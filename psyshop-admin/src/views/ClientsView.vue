
<script setup>
import { ref, onMounted } from 'vue'
import api from '../api'

const clientes = ref([])
const pagina = ref(1)
const ultimaPagina = ref(1)
const cargando = ref(false)
const error = ref('')

const clienteSeleccionado = ref(null)
const pedidos = ref([])
const paginaPedidos = ref(1)
const ultimaPaginaPedidos = ref(1)
const cargandoPedidos = ref(false)

const cargarClientes = async (numeroPagina = 1) => {
  cargando.value = true
  error.value = ''

  try {
    const respuesta = await api.get('/admin/clients', {
      params: { page: numeroPagina }
    })

    clientes.value = respuesta.data.data
    pagina.value = respuesta.data.current_page
    ultimaPagina.value = respuesta.data.last_page
  } catch (err) {
    error.value = 'No fue posible cargar los clientes.'
    console.error(err)
  } finally {
    cargando.value = false
  }
}

const verPedidos = async (cliente, numeroPagina = 1) => {
  clienteSeleccionado.value = cliente
  cargandoPedidos.value = true

  try {
    const respuesta = await api.get(`/admin/clients/${cliente.id}`, {
      params: { page: numeroPagina }
    })

    pedidos.value = respuesta.data.orders.data
    paginaPedidos.value = respuesta.data.orders.current_page
    ultimaPaginaPedidos.value = respuesta.data.orders.last_page
  } catch (err) {
    console.error(err)
    alert('No fue posible cargar los pedidos del cliente.')
  } finally {
    cargandoPedidos.value = false
  }
}

const cerrarDetalle = () => {
  clienteSeleccionado.value = null
  pedidos.value = []
}

const formatoFecha = (fecha) => {
  if (!fecha) return 'No disponible'

  return new Date(fecha).toLocaleDateString('es-SV', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatoDinero = (cantidad) => {
  return Number(cantidad || 0).toLocaleString('en-US', {
    style: 'currency',
    currency: 'USD'
  })
}

const traducirEstado = (estado) => {
  const estados = {
    pending: 'Pendiente',
    confirmed: 'Confirmado',
    preparing: 'En preparación',
    shipped: 'Enviado',
    completed: 'Completado',
    cancelled: 'Cancelado'
  }

  return estados[estado] || estado
}

onMounted(() => {
  cargarClientes()
})
</script>

<template>
  <div class="clients-page">
    <div class="page-heading">
      <div>
        <span class="eyebrow">PSYSHOP GAMING STORE</span>
        <h1>Clientes</h1>
        <p>Consulta los clientes registrados y su historial de compras.</p>
      </div>

      <button class="refresh-button" @click="cargarClientes(pagina)">
        Actualizar
      </button>
    </div>

    <div v-if="error" class="error-message">
      {{ error }}
    </div>

    <section class="clients-card">
      <div class="section-heading">
        <div>
          <h2>Clientes registrados</h2>
          <p>Listado de cuentas de clientes de PsyShop.</p>
        </div>

        <span class="counter">{{ clientes.length }} en esta página</span>
      </div>

      <div v-if="cargando" class="empty-state">
        Cargando clientes...
      </div>

      <div v-else-if="clientes.length === 0" class="empty-state">
        Todavía no hay clientes registrados.
      </div>

      <div v-else class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Cliente</th>
              <th>Correo electrónico</th>
              <th>Fecha de registro</th>
              <th>Compras</th>
              <th>Acciones</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="cliente in clientes" :key="cliente.id">
              <td>
                <div class="client-name">
                  <div class="avatar">
                    {{ cliente.name?.charAt(0)?.toUpperCase() || 'C' }}
                  </div>
                  <strong>{{ cliente.name }}</strong>
                </div>
              </td>

              <td>{{ cliente.email }}</td>
              <td>{{ formatoFecha(cliente.created_at) }}</td>
              <td>
                <span class="purchase-count">
                  {{ cliente.orders_count }}
                </span>
              </td>

              <td>
                <button
                  class="details-button"
                  @click="verPedidos(cliente)"
                >
                  Ver compras
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="ultimaPagina > 1" class="pagination">
        <button
          :disabled="pagina <= 1 || cargando"
          @click="cargarClientes(pagina - 1)"
        >
          Anterior
        </button>

        <span>Página {{ pagina }} de {{ ultimaPagina }}</span>

        <button
          :disabled="pagina >= ultimaPagina || cargando"
          @click="cargarClientes(pagina + 1)"
        >
          Siguiente
        </button>
      </div>
    </section>

    <section v-if="clienteSeleccionado" class="detail-card">
      <div class="section-heading detail-heading">
        <div>
          <span class="eyebrow">PERFIL DEL CLIENTE</span>
          <h2>{{ clienteSeleccionado.name }}</h2>
          <p>{{ clienteSeleccionado.email }}</p>
        </div>

        <button class="close-button" @click="cerrarDetalle">
          Cerrar
        </button>
      </div>

      <div class="client-summary">
        <div class="summary-item">
          <span>Correo electrónico</span>
          <strong>{{ clienteSeleccionado.email }}</strong>
        </div>

        <div class="summary-item">
          <span>Fecha de registro</span>
          <strong>{{ formatoFecha(clienteSeleccionado.created_at) }}</strong>
        </div>

        <div class="summary-item">
          <span>Total de compras</span>
          <strong>{{ clienteSeleccionado.orders_count }}</strong>
        </div>
      </div>

      <div class="orders-heading">
        <h3>Historial de compras</h3>
        <span>{{ clienteSeleccionado.orders_count }} compras registradas</span>
      </div>

      <div v-if="cargandoPedidos" class="empty-state">
        Cargando historial de compras...
      </div>

      <div v-else-if="pedidos.length === 0" class="empty-state">
        Este cliente todavía no tiene compras.
      </div>

      <div v-else class="orders-list">
        <article
          v-for="pedido in pedidos"
          :key="pedido.id"
          class="order-card"
        >
          <div class="order-top">
            <div>
              <strong>Pedido #{{ pedido.id }}</strong>
              <span>{{ formatoFecha(pedido.created_at) }}</span>
            </div>

            <span class="status">
              {{ traducirEstado(pedido.status) }}
            </span>
          </div>

          <div class="order-information">
            <p>
              <strong>Teléfono:</strong>
              {{ pedido.customer_phone || 'No disponible' }}
            </p>

            <p>
              <strong>Dirección:</strong>
              {{ pedido.delivery_address || 'No disponible' }}
            </p>
          </div>

          <div class="order-games">
            <strong>Videojuegos:</strong>

            <ul>
              <li v-for="item in pedido.items" :key="item.id">
                {{ item.game?.title || 'Videojuego no disponible' }}
                × {{ item.quantity }}
              </li>
            </ul>
          </div>

          <div class="order-total">
            Total: {{ formatoDinero(pedido.total) }}
          </div>
        </article>
      </div>

      <div v-if="ultimaPaginaPedidos > 1" class="pagination">
        <button
          :disabled="paginaPedidos <= 1 || cargandoPedidos"
          @click="verPedidos(clienteSeleccionado, paginaPedidos - 1)"
        >
          Anterior
        </button>

        <span>
          Página {{ paginaPedidos }} de {{ ultimaPaginaPedidos }}
        </span>

        <button
          :disabled="paginaPedidos >= ultimaPaginaPedidos || cargandoPedidos"
          @click="verPedidos(clienteSeleccionado, paginaPedidos + 1)"
        >
          Siguiente
        </button>
      </div>
    </section>
  </div>
</template>

<style scoped>
.clients-page {
  color: #f5f3ff;
  padding: 25px;
}

.page-heading,
.section-heading,
.detail-heading,
.order-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 15px;
}

.page-heading {
  margin-bottom: 25px;
}

.eyebrow {
  color: #a78bfa;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 2px;
}

h1 {
  font-size: 28px;
  margin: 8px 0;
}

h2, h3, p {
  margin-top: 0;
}

.page-heading p,
.section-heading p,
.detail-heading p {
  color: #aaa5bd;
  margin-bottom: 0;
}

.clients-card,
.detail-card {
  background: #151522;
  border: 1px solid #29263a;
  border-radius: 14px;
  padding: 22px;
  margin-bottom: 25px;
}

.section-heading {
  margin-bottom: 22px;
}

.section-heading h2 {
  margin-bottom: 7px;
  font-size: 19px;
}

.counter,
.purchase-count {
  background: #292044;
  color: #c4b5fd;
  border-radius: 20px;
  padding: 7px 12px;
  font-size: 12px;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 700px;
}

th {
  color: #aaa5bd;
  font-size: 12px;
  text-align: left;
  padding: 14px 12px;
  border-bottom: 1px solid #302b43;
}

td {
  padding: 16px 12px;
  border-bottom: 1px solid #29263a;
  font-size: 13px;
}

.client-name {
  display: flex;
  align-items: center;
  gap: 10px;
}

.avatar {
  width: 34px;
  height: 34px;
  display: grid;
  place-items: center;
  border-radius: 50%;
  background: linear-gradient(135deg, #8b5cf6, #6d28d9);
  font-weight: 700;
}

button {
  cursor: pointer;
  border: none;
  border-radius: 8px;
  padding: 9px 14px;
  font-weight: 600;
}

.refresh-button,
.details-button {
  background: #7c3aed;
  color: white;
}

.refresh-button:hover,
.details-button:hover {
  background: #6d28d9;
}

.close-button {
  background: #302b43;
  color: #ddd6fe;
}

.error-message {
  padding: 13px;
  margin-bottom: 18px;
  background: #451f2a;
  color: #fecaca;
  border-radius: 8px;
}

.empty-state {
  text-align: center;
  color: #aaa5bd;
  padding: 30px 10px;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 18px;
  margin-top: 22px;
  color: #aaa5bd;
  font-size: 13px;
}

.pagination button {
  background: #302b43;
  color: white;
}

.pagination button:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.client-summary {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 15px;
  margin: 25px 0;
}

.summary-item {
  background: #0f0e19;
  border: 1px solid #29263a;
  border-radius: 10px;
  padding: 15px;
  min-width: 0;
}

.summary-item span,
.summary-item strong {
  display: block;
  overflow-wrap: anywhere;
}

.summary-item span {
  color: #aaa5bd;
  font-size: 12px;
  margin-bottom: 8px;
}

.summary-item strong {
  font-size: 13px;
}

.orders-heading {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin: 25px 0 15px;
}

.orders-heading span {
  color: #aaa5bd;
  font-size: 12px;
}

.order-card {
  background: #0f0e19;
  border: 1px solid #29263a;
  border-radius: 10px;
  padding: 18px;
  margin-bottom: 13px;
}

.order-top {
  padding-bottom: 14px;
  border-bottom: 1px solid #29263a;
}

.order-top strong,
.order-top span {
  display: block;
}

.order-top span {
  color: #aaa5bd;
  font-size: 12px;
  margin-top: 5px;
}

.status {
  background: #292044;
  color: #c4b5fd !important;
  padding: 7px 10px;
  border-radius: 20px;
}

.order-information {
  margin-top: 15px;
}

.order-information p {
  color: #c5c0d3;
  font-size: 13px;
  margin-bottom: 8px;
  overflow-wrap: anywhere;
}

.order-games {
  font-size: 13px;
  margin-top: 15px;
}

.order-games ul {
  color: #c5c0d3;
  padding-left: 20px;
  margin-bottom: 0;
}

.order-games li {
  margin-top: 7px;
}

.order-total {
  text-align: right;
  color: #c4b5fd;
  font-weight: 700;
  margin-top: 18px;
}

@media (max-width: 700px) {
  .clients-page {
    padding: 15px;
  }

  .client-summary {
    grid-template-columns: 1fr;
  }

  .page-heading,
  .section-heading,
  .orders-heading {
    align-items: flex-start;
    flex-direction: column;
  }
}
</style>
