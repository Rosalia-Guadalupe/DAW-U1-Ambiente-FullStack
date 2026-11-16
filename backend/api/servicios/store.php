<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/response.php';
require_once __DIR__ . '/../../helpers/request.php';
require_once __DIR__ . '/../../helpers/auth.php';
/*
|--------------------------------------------------------------------------
| 1. Verificar permisos
|--------------------------------------------------------------------------
| Solamente:
|
| ADMIN_SISTEMA
| ADMIN_SERVICIOS
|
| pueden registrar nuevos servicios.
*/
$currentUser = requireRole(['ADMIN_SISTEMA','ADMIN_SERVICIOS']);
/*
|--------------------------------------------------------------------------
| 2. Verificar método HTTP
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
 jsonResponse(
 405,
 [
 'ok' => false,
 'message' => 'Método no permitido'
 ]
 );
}
/*
|--------------------------------------------------------------------------
| 3. Leer JSON recibido
|--------------------------------------------------------------------------
|
| Ejemplo:
|
| {
| "nombre": "Mantenimiento preventivo",
| "descripcion": "Servicio de mantenimiento...",
| "precio": 1500.00,
| "duracion_minutos": 90,
| "activo": 1
| }
*/
$data = readJson();
/*
|--------------------------------------------------------------------------
| 4. Recuperar valores
|--------------------------------------------------------------------------
*/
$nombre =
 trim(
 (string)(
 $data['nombre'] ?? ''
 )
 );
$descripcion =
 trim(
 (string)(
 $data['descripcion'] ?? ''
 )
 );
$precioRaw = $data['precio'] ?? null;
$duracionRaw = $data['duracion_minutos'] ?? null;
$activoRaw = $data['activo'] ?? 1;
/*
|--------------------------------------------------------------------------
| 5. Validaciones
|--------------------------------------------------------------------------
*/
$errors = [];
/*
|--------------------------------------------------------------------------
| Nombre
|--------------------------------------------------------------------------
*/
if ($nombre === '') {
 $errors['nombre'] = 'El nombre del servicio es obligatorio';
}
elseif (mb_strlen($nombre) > 120) {
 $errors['nombre'] = 'El nombre no puede superar 120 caracteres';
}
/*
|--------------------------------------------------------------------------
| Descripción
|--------------------------------------------------------------------------
*/
if ($descripcion === '') {
 $errors['descripcion'] = 'La descripción es obligatoria';
}
/*
|--------------------------------------------------------------------------
| Precio
|--------------------------------------------------------------------------
*/
if ( $precioRaw === null || $precioRaw === '') {
 $errors['precio'] = 'El precio es obligatorio';
}
elseif (!is_numeric($precioRaw)) {
 $errors['precio'] = 'El precio debe ser un valor numérico';
}
elseif ((float)$precioRaw < 0) {
 $errors['precio'] = 'El precio no puede ser negativo';
}
/*
|--------------------------------------------------------------------------
| Duración
|--------------------------------------------------------------------------
*/
if ( $duracionRaw === null || $duracionRaw === '') {
 $errors['duracion_minutos'] = 'La duración es obligatoria';
}
elseif (filter_var($duracionRaw, FILTER_VALIDATE_INT ) === false) {
 $errors['duracion_minutos'] = 'La duración debe expresarse en minutos enteros';
}
elseif ((int)$duracionRaw <= 0) {
 $errors['duracion_minutos'] = 'La duración debe ser mayor que cero';
}
/*
|--------------------------------------------------------------------------
| Estado
|--------------------------------------------------------------------------
|
| Aceptamos:
|
| 1
| 0
| "1"
| "0"
| true
| false
*/
if (is_bool($activoRaw)) {
 $activo = $activoRaw ? 1 : 0;
}
elseif (
 in_array(
 $activoRaw,
 [0, 1, '0', '1'],
 true
 )
) {
 $activo = (int)$activoRaw;
}
else {
 $activo = -1;
 $errors['activo'] = 'El estado debe ser activo o inactivo';
}
/*
|--------------------------------------------------------------------------
| 6. Si existen errores, detener proceso
|--------------------------------------------------------------------------
*/
if (!empty($errors)) {
 jsonResponse(
 422,
 [
 'ok' => false,
 'message' => 'Existen datos incorrectos',
 'errors' => $errors
 ]
 );
}
/*
|--------------------------------------------------------------------------
| 7. Normalizar valores
|--------------------------------------------------------------------------
*/
$precio = round((float)$precioRaw, 2);
$duracionMinutos = (int)$duracionRaw;
/*
|--------------------------------------------------------------------------
| 8. Operaciones con base de datos
|--------------------------------------------------------------------------
*/
try {
 $pdo = getPDO();
 /*
 |--------------------------------------------------------------------------
 | 9. Comprobar si existe un servicio con el mismo nombre
 |--------------------------------------------------------------------------
 |
 | Esta validación es opcional desde el punto de vista técnico,
 | pero es conveniente para evitar duplicados lógicos.
 */
 $stmtExiste = $pdo->prepare(' SELECT id FROM servicios WHERE nombre = :nombre
LIMIT 1 ');
 $stmtExiste->execute([ ':nombre' => $nombre ]);
 if ($stmtExiste->fetch()) {
 jsonResponse(
 409,
 [
 'ok' => false,
 'message' => 'Ya existe un servicio con ese nombre'
 ]
 );
 }
 /*
 |--------------------------------------------------------------------------
 | 10. Preparar INSERT
 |--------------------------------------------------------------------------
 |
 | Todos los valores enviados por el usuario se manejan
 | mediante parámetros PDO.
 */
 $stmt = $pdo->prepare('INSERT INTO servicios
 (
 nombre,
 descripcion,
 precio,
 duracion_minutos,
 activo
 )
 VALUES
 (
 :nombre,
 :descripcion,
 :precio,
 :duracion_minutos,
 :activo
 )' );
 /*
 |--------------------------------------------------------------------------
 | 11. Ejecutar INSERT
 |--------------------------------------------------------------------------
 */
 $stmt->execute([
 ':nombre' => $nombre,
 ':descripcion' => $descripcion,
 ':precio' => $precio,
 ':duracion_minutos' => $duracionMinutos,
 ':activo' => $activo
 ]);
 /*
 |--------------------------------------------------------------------------
 | 12. Obtener ID generado
 |--------------------------------------------------------------------------
 */
 $nuevoId = (int)$pdo->lastInsertId();
 /*
 |--------------------------------------------------------------------------
 | 13. Recuperar servicio registrado
 |--------------------------------------------------------------------------
 |
 | Esto permite regresar al frontend el objeto completo.
 */
 $stmtServicio = $pdo->prepare('SELECT id, nombre, descripcion, precio,
duracion_minutos, activo, created_at, updated_at
 FROM servicios WHERE id = :id LIMIT 1' );
 $stmtServicio->execute([ ':id' => $nuevoId]);
 $servicio = $stmtServicio->fetch();
 /*
 |--------------------------------------------------------------------------
 | 14. Respuesta JSON
 |--------------------------------------------------------------------------
 */
 jsonResponse(
 201,
 [
 'ok' => true,
 'message' => 'Servicio registrado correctamente',
 'data' => $servicio
 ]
 );
} catch (PDOException $e) {
 /*
 |--------------------------------------------------------------------------
 | 15. Error interno
 |--------------------------------------------------------------------------
 |
 | No enviar:
 |
 | $e->getMessage()
 |
 | al frontend.
 */
 jsonResponse(
 500,
 [
 'ok' => false,
 'message' => 'Error interno al registrar el servicio'
 ]
 );
}
