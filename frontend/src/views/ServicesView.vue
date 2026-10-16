```vue
<script setup>
import { computed, onMounted, ref } from 'vue'
import ServiceCard from '../components/ServiceCard.vue'
import { obtenerServicios } from '../services/api'

const servicios = ref([])
const busqueda = ref('')
const cargando = ref(true)
const error = ref('')

const serviciosFiltrados = computed(() => {
  const texto = busqueda.value.trim().toLowerCase()

  return servicios.value.filter(servicio =>
    servicio.nombre.toLowerCase().includes(texto)
  )
})

onMounted(async () => {
  try {
    servicios.value = await obtenerServicios()
  } catch (e) {
    error.value = e.message
  } finally {
    cargando.value = false
  }
})
</script>

<template>
  <section class="services-page">
    <header class="services-heading">
      <div class="services-title">
        <span class="services-tag">CATÁLOGO</span>
        <h2>Servicios disponibles</h2>
        <p>
          Explora nuestros servicios y encuentra la opción que necesitas.
        </p>
      </div>

      <label class="services-search">
        <span>Buscar servicio</span>
        <input
          v-model="busqueda"
          type="search"
          placeholder="Ej. Desarrollo Web"
        >
      </label>
    </header>

    <div class="services-summary">
      <span>Catálogo de servicios</span>
      <span v-if="!cargando && !error">
        {{ serviciosFiltrados.length }}
        {{ serviciosFiltrados.length === 1 ? 'servicio' : 'servicios' }}
      </span>
    </div>

    <div
      v-if="cargando"
      class="services-message"
      aria-live="polite"
    >
      <p>Cargando información...</p>
    </div>

    <div
      v-else-if="error"
      class="services-message services-error"
      role="alert"
    >
      <h3>No se pudieron cargar los servicios</h3>
      <p>{{ error }}</p>
    </div>

    <div
      v-else-if="serviciosFiltrados.length === 0"
      class="services-message"
    >
      <h3>No se encontraron servicios</h3>
      <p>Prueba con otro nombre en el buscador.</p>
    </div>

    <div
      v-else
      class="services-grid"
    >
      <ServiceCard
        v-for="servicio in serviciosFiltrados"
        :key="servicio.id"
        :servicio="servicio"
      />
    </div>
  </section>
</template>

<style scoped>
.services-page {
  width: 100%;
  max-width: 1200px;
  margin: 0 auto;
  padding: 32px 24px;
}

.services-heading {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 24px;
  margin-bottom: 32px;
}

.services-tag {
  display: inline-block;
  margin-bottom: 10px;
  color: #2563eb;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 1.5px;
}

.services-title h2 {
  margin: 0 0 10px;
  font-size: clamp(26px, 4vw, 36px);
  line-height: 1.2;
}

.services-title p {
  margin: 0;
  color: #64748b;
  line-height: 1.6;
}

.services-search {
  display: flex;
  flex-direction: column;
  gap: 8px;
  width: min(100%, 300px);
  font-size: 14px;
  font-weight: 600;
}

.services-search input {
  width: 100%;
  padding: 12px 14px;
  border: 1px solid #cbd5e1;
  border-radius: 10px;
  background: #ffffff;
  color: #0f172a;
  font: inherit;
  outline: none;
}

.services-search input:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 3px rgb(37 99 235 / 15%);
}

.services-summary {
  display: flex;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 18px;
  padding-bottom: 14px;
  border-bottom: 1px solid #e2e8f0;
  color: #64748b;
  font-size: 14px;
}

.services-summary span:last-child {
  font-weight: 700;
  color: #334155;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(100%, 260px), 1fr));
  gap: 22px;
  align-items: stretch;
}

.services-message {
  padding: 32px 20px;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  background: #f8fafc;
  text-align: center;
}

.services-message h3 {
  margin: 0 0 8px;
}

.services-message p {
  margin: 0;
  color: #64748b;
  overflow-wrap: anywhere;
}

.services-error {
  border-color: #fecaca;
  background: #fef2f2;
  color: #991b1b;
}

.services-error p {
  color: #991b1b;
}

@media (max-width: 650px) {
  .services-page {
    padding: 24px 16px;
  }

  .services-heading {
    align-items: stretch;
    flex-direction: column;
    gap: 20px;
  }

  .services-search {
    width: 100%;
  }

  .services-grid {
    grid-template-columns: 1fr;
  }
}
</style>
```