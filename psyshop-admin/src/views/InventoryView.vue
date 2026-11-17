<template>
  <div class="inventory-page">
    <div class="page-header">
      <div>
        <h1>Inventario</h1>
        <p>Control de existencias de videojuegos de PsyShop.</p>
      </div>

      <button @click="cargarInventario" :disabled="cargando">
        {{ cargando ? 'Cargando...' : 'Actualizar inventario' }}
      </button>
    </div>

    <div class="stats-grid">
      <div class="stat-card">
        <span>Total de videojuegos</span>
        <strong>{{ inventario.length }}</strong>
      </div>

      <div class="stat-card">
        <span>Unidades disponibles</span>
        <strong>{{ totalUnidades }}</strong>
      </div>

      <div class="stat-card warning-card">
        <span>Stock bajo</span>
        <strong>{{ productosStockBajo }}</strong>
      </div>

      <div class="stat-card">
        <span>Sin existencias</span>
        <strong>{{ productosAgotados }}</strong>
      </div>
    </div>

    <div class="filters">
      <input
        v-model="busqueda"
        type="search"
        placeholder="Buscar videojuego..."
      />

      <select v-model="filtroStock">
        <option value="todos">Todos los productos</option>
        <option value="bajo">Stock bajo (1 a 5)</option>
        <option value="agotado">Agotados</option>
        <option value="disponible">Stock normal</option>
      </select>
    </div>

    <p v-if="error" class="error">{{ error }}</p>
    <p v-if="cargando" class="info">Cargando inventario...</p>

    <div v-if="!cargando && !error" class="table-container">
      <table>
        <thead>
          <tr>
            <th>Videojuego</th>
            <th>Plataforma</th>
            <th>Precio</th>
            <th>Existencias</th>
            <th>Estado del stock</th>
            <th>Acciones</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="juego in inventarioFiltrado"
            :key="juego.id"
          >
            <td>
              <div class="game-name">{{ juego.title }}</div>
              <small>ID: {{ juego.id }}</small>
            </td>

            <td>{{ juego.platform }}</td>

            <td>{{ formatearPrecio(juego.price) }}</td>

            <td>
              <strong>{{ juego.stock }}</strong>
              <span class="unit-label"> unidades</span>
            </td>

            <td>
              <span
                class="stock-badge"
                :class="claseStock(juego.stock)"
              >
                {{ estadoStock(juego.stock) }}
              </span>
            </td>
            <td>
  <button
    class="edit-stock-button"
    @click="editarStock(juego)"
  >
    Editar stock
  </button>
</td>
          </tr>

          <tr v-if="inventarioFiltrado.length === 0">
            <td colspan="5" class="empty">
              No se encontraron videojuegos para este filtro.
            </td>
          </tr>
        </tbody>
      </table>
    </div>

   
  </div>
</template>

<script>
import api from '../api'

export default {
  name: 'InventoryView',

  data() {
    return {
      inventario: [],
      cargando: false,
      error: '',
      busqueda: '',
      filtroStock: 'todos',
    }
  },

  computed: {
    totalUnidades() {
      return this.inventario.reduce(
        (total, juego) => total + Number(juego.stock || 0),
        0
      )
    },

    productosStockBajo() {
      return this.inventario.filter(
        juego => Number(juego.stock) >= 1 &&
                 Number(juego.stock) <= 5
      ).length
    },

    productosAgotados() {
      return this.inventario.filter(
        juego => Number(juego.stock) === 0
      ).length
    },

    inventarioFiltrado() {
      let resultado = this.inventario

      const texto = this.busqueda.trim().toLowerCase()

      if (texto) {
        resultado = resultado.filter(juego =>
          String(juego.title || '').toLowerCase().includes(texto)
        )
      }

      if (this.filtroStock === 'bajo') {
        resultado = resultado.filter(
          juego => Number(juego.stock) >= 1 &&
                   Number(juego.stock) <= 5
        )
      } else if (this.filtroStock === 'agotado') {
        resultado = resultado.filter(
          juego => Number(juego.stock) === 0
        )
      } else if (this.filtroStock === 'disponible') {
        resultado = resultado.filter(
          juego => Number(juego.stock) > 5
        )
      }

      return resultado
    },
  },

  mounted() {
    this.cargarInventario()
  },
  

  methods: {
    
async editarStock(juego) {
  const nuevaCantidad = prompt(
    `Ingresa las nuevas existencias de ${juego.title}:`,
    juego.stock
  )

  if (nuevaCantidad === null || nuevaCantidad.trim() === '') {
    return
  }

  const cantidad = Number(nuevaCantidad)

  if (!Number.isInteger(cantidad) || cantidad < 0) {
    alert('Ingresa un número entero igual o mayor que cero.')
    return
  }

  try {
    await api.put(`/games/${juego.id}`, {
      stock: cantidad,
    })

    await this.cargarInventario()

    alert('¡Existencias actualizadas correctamente!')
  } catch (e) {
    alert(
      e.response?.data?.message ||
      'No se pudieron actualizar las existencias.'
    )
  }
},

    async cargarInventario() {
      this.cargando = true
      this.error = ''

      try {
        const respuesta = await api.get('/games')
        const datos = respuesta.data

        this.inventario = Array.isArray(datos)
          ? datos
          : datos.data || []
      } catch (e) {
        this.error =
          e.response?.data?.message ||
          'No se pudo cargar el inventario. Verifica que Laravel esté funcionando.'
      } finally {
        this.cargando = false
      }
    },

    formatearPrecio(precio) {
      return new Intl.NumberFormat('es-SV', {
        style: 'currency',
        currency: 'USD',
      }).format(Number(precio || 0))
    },

    estadoStock(stock) {
      const cantidad = Number(stock)

      if (cantidad === 0) return 'Agotado'
      if (cantidad <= 5) return 'Stock bajo'

      return 'Disponible'
    },

    claseStock(stock) {
      const cantidad = Number(stock)

      if (cantidad === 0) return 'stock-empty'
      if (cantidad <= 5) return 'stock-low'

      return 'stock-ok'
    },
  },
}
</script>

<style scoped>
.inventory-page {
  padding: 28px;
  color: #e5e7eb;
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 16px;
  margin-bottom: 24px;
}

h1 {
  margin: 0 0 8px;
  color: #fff;
  font-size: 28px;
}

.page-header p {
  margin: 0;
  color: #9ca3af;
}

button {
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  background: #2563eb;
  color: #fff;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.stat-card {
  padding: 20px;
  border: 1px solid #374151;
  border-radius: 12px;
  background: #111827;
}

.stat-card span {
  display: block;
  margin-bottom: 12px;
  color: #9ca3af;
  font-size: 13px;
}

.stat-card strong {
  color: #fff;
  font-size: 28px;
}

.warning-card strong {
  color: #fbbf24;
}

.filters {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
}

.filters input,
.filters select {
  min-width: 0;
  padding: 11px 12px;
  border: 1px solid #374151;
  border-radius: 8px;
  background: #111827;
  color: #f9fafb;
}

.filters input {
  flex: 1;
}

.filters select {
  width: 220px;
}

.table-container {
  overflow-x: auto;
  border: 1px solid #374151;
  border-radius: 10px;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: #111827;
}

th,
td {
  padding: 15px;
  text-align: left;
  border-bottom: 1px solid #374151;
}

th {
  background: #1f2937;
  color: #d1d5db;
  white-space: nowrap;
}

td small {
  display: block;
  margin-top: 5px;
  color: #9ca3af;
}

.game-name {
  color: #fff;
  font-weight: 600;
}

.unit-label {
  color: #9ca3af;
  font-size: 12px;
}

.stock-badge {
  display: inline-block;
  padding: 6px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
}

.stock-ok {
  background: #14532d;
  color: #bbf7d0;
}

.stock-low {
  background: #713f12;
  color: #fde68a;
}

.stock-empty {
  background: #7f1d1d;
  color: #fecaca;
}

.error {
  padding: 12px;
  border-radius: 8px;
  background: #7f1d1d;
  color: #fecaca;
}

.info,
.empty {
  color: #9ca3af;
}

.footer-note {
  margin-top: 18px;
  color: #9ca3af;
  font-size: 13px;
}

@media (max-width: 1000px) {
  .stats-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
}

@media (max-width: 600px) {
  .inventory-page {
    padding: 16px;
  }

  .page-header,
  .filters {
    align-items: stretch;
    flex-direction: column;
  }

  .filters select {
    width: 100%;
  }
}
</style>
