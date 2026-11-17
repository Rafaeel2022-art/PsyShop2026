<template>
  <div class="reports-page">

    <div class="page-intro">
      <div>
        <span class="eyebrow">ANÁLISIS DEL NEGOCIO</span>
        <h2>Centro de reportes</h2>
        <p>Consulta y descarga los informes administrativos de PsyShop.</p>
      </div>
    </div>

    <div class="reports-grid">

      <!-- REPORTE DE VENTAS -->
      <article class="report-card">
        <div class="report-icon sales-icon">💰</div>
        <span class="report-category">FINANZAS</span>
        <h3>Reporte de ventas</h3>
        <p>Consulta los ingresos confirmados y las ventas realizadas.</p>

        <div class="report-details">
          <span>• Ventas completadas</span>
          <span>• Ingresos totales</span>
          <span>• Unidades vendidas</span>
        </div>

        <button @click="descargarReporte('sales')"
                :disabled="loading !== ''">
          {{ loading === 'sales' ? 'Generando PDF...' : 'Descargar reporte' }}
        </button>
      </article>

      <!-- REPORTE DE INVENTARIO -->
      <article class="report-card">
        <div class="report-icon inventory-icon">📦</div>
        <span class="report-category">INVENTARIO</span>
        <h3>Reporte de inventario</h3>
        <p>Revisa las existencias actuales de videojuegos.</p>

        <div class="report-details">
          <span>• Videojuegos registrados</span>
          <span>• Unidades disponibles</span>
          <span>• Stock bajo y agotado</span>
        </div>

        <button @click="descargarReporte('inventory')"
                :disabled="loading !== ''">
          {{ loading === 'inventory' ? 'Generando PDF...' : 'Descargar reporte' }}
        </button>
      </article>

      <!-- REPORTE DE PEDIDOS -->
      <article class="report-card">
        <div class="report-icon orders-icon">🧾</div>
        <span class="report-category">PEDIDOS</span>
        <h3>Reporte de pedidos</h3>
        <p>Consulta los pedidos registrados y sus estados.</p>

        <div class="report-details">
          <span>• Pedidos completados</span>
          <span>• Pedidos pendientes</span>
          <span>• Pedidos cancelados</span>
        </div>

        <button @click="descargarReporte('orders')"
                :disabled="loading !== ''">
          {{ loading === 'orders' ? 'Generando PDF...' : 'Descargar reporte' }}
        </button>
      </article>

      <!-- REPORTE GENERAL -->
      <article class="report-card featured">
        <div class="report-icon general-icon">📊</div>
        <span class="report-category">RESUMEN EJECUTIVO</span>
        <h3>Reporte general</h3>
        <p>Obtén una visión completa del estado del negocio.</p>

        <div class="report-details">
          <span>• Resumen de ventas</span>
          <span>• Estado de pedidos</span>
          <span>• Indicadores de inventario</span>
        </div>

        <button @click="descargarReporte('general')"
                :disabled="loading !== ''">
          {{ loading === 'general' ? 'Generando PDF...' : 'Descargar reporte' }}
        </button>
      </article>

    </div>

    <p v-if="mensaje" class="message" :class="{ error: esError }">
      {{ mensaje }}
    </p>

  </div>
</template>

<script>
import api from '../api';

export default {
  name: 'ReportsView',

  data() {
    return {
      loading: '',
      mensaje: '',
      esError: false
    };
  },

  methods: {
    async descargarReporte(tipo) {
      this.loading = tipo;
      this.mensaje = '';
      this.esError = false;

      try {
        const response = await api.get(`/admin/reports/${tipo}`, {
          responseType: 'blob'
        });

        const blob = new Blob([response.data], {
          type: 'application/pdf'
        });

        const url = window.URL.createObjectURL(blob);
        const enlace = document.createElement('a');

        enlace.href = url;
        enlace.download = `psyshop-reporte-${tipo}.pdf`;

        document.body.appendChild(enlace);
        enlace.click();
        enlace.remove();

        window.URL.revokeObjectURL(url);

        this.mensaje = 'Reporte descargado correctamente.';
      } catch (error) {
        console.error('Error al descargar el reporte:', error);

        this.esError = true;
        this.mensaje = 'No se pudo generar el reporte. Verifica tu sesión de administrador.';
      } finally {
        this.loading = '';
      }
    }
  }
};
</script>

<style scoped>
.reports-page {
  color: #f5f3fa;
}

.page-intro {
  margin-bottom: 28px;
}

.page-intro h2 {
  margin: 8px 0;
  font-size: 25px;
}

.page-intro p {
  color: #a6a0b5;
  font-size: 14px;
}

.reports-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 20px;
}

.report-card {
  padding: 25px;
  background: #191722;
  border: 1px solid #302a40;
  border-radius: 16px;
  transition: transform 0.2s, border-color 0.2s;
}

.report-card:hover {
  transform: translateY(-4px);
  border-color: #8b5cf6;
}

.report-card.featured {
  background: linear-gradient(145deg, #211936, #191722);
  border-color: #6843a3;
}

.report-icon {
  display: grid;
  place-items: center;
  width: 48px;
  height: 48px;
  margin-bottom: 18px;
  border-radius: 13px;
  font-size: 22px;
}

.sales-icon {
  background: #352b20;
}

.inventory-icon {
  background: #20352f;
}

.orders-icon {
  background: #292b46;
}

.general-icon {
  background: #382354;
}

.report-category {
  color: #a78bfa;
  font-size: 10px;
  font-weight: bold;
  letter-spacing: 1.5px;
}

.report-card h3 {
  margin: 10px 0;
  font-size: 20px;
}

.report-card p {
  color: #a6a0b5;
  font-size: 13px;
  line-height: 1.6;
}

.report-details {
  display: flex;
  flex-direction: column;
  gap: 9px;
  margin: 20px 0;
  color: #d0cadb;
  font-size: 12px;
}

.report-card button {
  width: 100%;
  padding: 13px;
  border: none;
  border-radius: 9px;
  background: #8b5cf6;
  color: white;
  font-weight: bold;
  cursor: pointer;
  transition: background 0.2s;
}

.report-card button:hover {
  background: #7544dc;
}

.report-card button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.message {
  margin-top: 22px;
  color: #4ade80;
  font-size: 14px;
}

.message.error {
  color: #fb7185;
}

@media (max-width: 850px) {
  .reports-grid {
    grid-template-columns: 1fr;
  }
}
</style>