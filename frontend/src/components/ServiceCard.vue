```vue
<script setup>
defineProps({
  servicio: {
    type: Object,
    required: true
  }
})

const emit = defineEmits([
  'seleccionar'
])
</script>

<template>
  <article class="service-card">
    <div class="card-icon" aria-hidden="true">
      <svg
        viewBox="0 0 24 24"
        width="28"
        height="28"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
      >
        <rect x="3" y="4" width="18" height="13" rx="2" />
        <path d="M8 21h8M12 17v4M7 9h10M7 12h6" />
      </svg>
    </div>

    <div class="card-content">
      <h3>{{ servicio.nombre }}</h3>

      <p class="description">
        {{ servicio.descripcion }}
      </p>
    </div>

    <div class="card-footer">
      <div class="price-container">
        <span class="price-label">Precio</span>
        <p class="price">
          ${{ Number(servicio.precio).toLocaleString('es-MX') }}
          <span class="currency">MXN</span>
        </p>
      </div>

      <button
        type="button"
        class="consult-button"
        :aria-label="`Consultar ${servicio.nombre}`"
        @click="emit('seleccionar', servicio)"
      >
        Consultar
        <span aria-hidden="true">→</span>
      </button>
    </div>
  </article>
</template>

<style scoped>
.service-card {
  display: flex;
  flex-direction: column;
  min-width: 0;
  min-height: 270px;
  padding: 24px;
  overflow-wrap: anywhere;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  box-shadow: 0 4px 12px rgb(15 23 42 / 4%);
  transition:
    transform 0.2s ease,
    box-shadow 0.2s ease,
    border-color 0.2s ease;
}

.service-card:hover {
  transform: translateY(-4px);
  border-color: #bfdbfe;
  box-shadow: 0 12px 28px rgb(15 23 42 / 9%);
}

.card-icon {
  display: grid;
  width: 54px;
  height: 54px;
  margin-bottom: 20px;
  place-items: center;
  color: #2563eb;
  background: #eff6ff;
  border-radius: 14px;
}

.card-content h3 {
  margin: 0 0 10px;
  color: #0f172a;
  font-size: 20px;
  font-weight: 750;
}

.description {
  margin: 0;
  color: #64748b;
  font-size: 14px;
  line-height: 1.7;
}

.card-footer {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 14px;
  margin-top: auto;
  padding-top: 24px;
}

.price-label {
  color: #64748b;
  font-size: 12px;
}

.price {
  margin: 4px 0 0;
  color: #0f172a;
  font-size: 25px;
  font-weight: 800;
}

.currency {
  color: #64748b;
  font-size: 11px;
  font-weight: 600;
}

.consult-button {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  padding: 11px 15px;
  border: 0;
  border-radius: 10px;
  background: #2563eb;
  color: #ffffff;
  font: inherit;
  font-size: 14px;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.2s ease;
}

.consult-button:hover {
  background: #1d4ed8;
}

.consult-button:focus-visible {
  outline: 3px solid #93c5fd;
  outline-offset: 3px;
}

@media (max-width: 400px) {
  .service-card {
    padding: 18px;
  }

  .card-footer {
    align-items: flex-start;
    flex-direction: column;
  }

  .consult-button {
    width: 100%;
  }
}

@media (prefers-reduced-motion: reduce) {
  .service-card,
  .consult-button {
    transition: none;
  }

  .service-card:hover {
    transform: none;
  }
}
</style>
```