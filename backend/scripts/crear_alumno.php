<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getPDO();

    $usuario = 'alumno';
    $nombre = 'Alumno de prueba';
    $password = 'alumno2026';
    $rol = 'Usuario';

    $passwordHash = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $sql = $pdo->prepare(
        'INSERT INTO usuarios
        (usuario, nombre, password_hash, rol, activo)
        VALUES
        (:usuario, :nombre, :password_hash, :rol, 1)
        ON DUPLICATE KEY UPDATE
        nombre = VALUES(nombre),
        password_hash = VALUES(password_hash),
        rol = VALUES(rol),
        activo = 1'
    );

    $sql->execute([
        ':usuario' => $usuario,
        ':nombre' => $nombre,
        ':password_hash' => $passwordHash,
        ':rol' => $rol
    ]);

    echo "Alumno creado correctamente.\n";

} catch (Throwable $e) {
    echo "Error al crear el alumno.\n";
}
