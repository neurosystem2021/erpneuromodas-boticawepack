<template>
<div id="app" class="nst-premium" :class="{ 'nst-document': $route.name === 'Proforma', 'nst-inventory-app': $route.name === 'Confrontacion' }">
  
  <main :class="{ 'nst-workspace': $route.name !== 'Proforma', 'nst-sales-workspace': $route.name === 'Ventas' }"><router-view/></main>
  <producto-asesor-ia />
  <diario-recordatorios v-if="$route.name !== 'Ventas' && $route.name !== 'Proforma'" class="pos-inline-voice" />
  <agenda-personal v-if="$route.name !== 'Proforma'" />
  <support-launcher v-if="$route.name !== 'Proforma'" />
</div>
</template>

<script>
import ProductoAsesorIA from '@/components/ProductoAsesorIA.vue';
import SupportLauncher from '@/components/SupportLauncher.vue';
import DiarioRecordatorios from '@/components/DiarioRecordatorios.vue';
import AgendaPersonal from '@/components/AgendaPersonal.vue';
export default {
  name: "App",
  components: {AgendaPersonal,DiarioRecordatorios,SupportLauncher,'producto-asesor-ia':ProductoAsesorIA},
  computed: {
    modulo() {
      const titles = { Movimiento: 'Inventario y compras', Traslado: 'Traslado entre almacenes', OrdenCompra: 'Órdenes de compra', CargarComprasXml: 'Importar compras XML', Almacen: 'Stock por almacén', Productos: 'Catálogo de productos', ProductosSinMovimiento: 'Productos sin movimiento', ConfigurarOferta: 'Ofertas y promociones', Ventas: 'Punto de venta', VentaReimpresion: 'Consulta de comprobantes', VentasFacturacion: 'Facturación electrónica', Usuario: 'Usuarios y accesos', CajaBancos: 'Caja y bancos', BuscadorCajaBanco: 'Consulta de operaciones', Remision: 'Guías de remisión', RemisionLista: 'Listado de guías', Kardex: 'Kardex de productos', Articulosmovimientos: 'Artículos con más movimientos', Dashboard: 'Panel de resultados' };
      const name = this.$route.name || '';
      Object.assign(titles, {
        Clientes: 'Clientes', Impresoras: 'Impresoras', Habitaciones: 'Habitaciones',
        ReporteVentas: 'Reporte de ventas', Ordenes: 'Órdenes registradas', CierreCaja: 'Cierre de caja', CierreCajaId: 'Detalle de cierre de caja', ReporteCierreCaja: 'Reporte de cierres de caja',
        StockMinimo: 'Productos con stock mínimo', comisionistas: 'Comisionistas', DeudaClientes: 'Cuentas por cobrar', DeudaProveedores: 'Cuentas por pagar', RankingClientes: 'Ranking de clientes', RegistroHuespedes: 'Registro de huéspedes', PorEntregar: 'Productos por entregar',
        Estadisticas: 'Estadísticas del negocio', AgenteOperacion: 'Operaciones de agentes', AgenteCierreCaja: 'Cierre de caja de agentes', AgenteCierreCajaId: 'Detalle de cierre de agente', AgenteGeneral: 'Resumen de agentes', AgenteReimpresion: 'Comprobantes de agentes', AgenteChat: 'Conversaciones',
        ReporteProyectos: 'Reporte de proyectos', ReporteUtilidadBruta: 'Utilidad bruta', HistorialVentaKit: 'Historial de venta de kits', ReporteUtilidadBrutaPorUsuario: 'Utilidad bruta por usuario', Confrontacion: 'Confrontación de operaciones', ReporteRegMovimiento: 'Registro de movimientos', ReporteRegistroVentas: 'Registro de ventas', VentasDelivery: 'Ventas por delivery', ReporteProyectosEmpleado: 'Proyectos por empleado'
      });
      const logistics = ['Movimiento', 'Traslado', 'OrdenCompra', 'CargarComprasXml', 'Almacen', 'Kardex', 'Articulosmovimientos'];
      const group = logistics.indexOf(name) >= 0 ? 'Logística' : name.indexOf('Agente') === 0 ? 'Multi agente' : name.indexOf('Venta') >= 0 || name === 'Clientes' ? 'Ventas' : name.indexOf('Producto') >= 0 || name === 'ConfigurarOferta' ? 'Producto' : name === 'Usuario' ? 'Seguridad' : name.indexOf('Caja') >= 0 ? 'Caja y bancos' : 'Gestión y reportes';
      return { title: this.$route.path === '/RemisionTransportista' ? 'Transportistas' : titles[name] || name.replace(/([a-z])([A-Z])/g, '$1 $2'), group, description: name === 'Ventas' ? 'Busca productos, prepara tu venta y completa el cobro.' : 'Consulta la información y gestiona tus operaciones.' };
    }
  }
};
</script>

<style>
@import './assets/css/imceden-sire.css';
@import './assets/css/premium.css';
@import './assets/css/modules-modern.css';
@import './assets/css/modules-compact.css';
.tooltip[role="tooltip"]{pointer-events:none!important}
.modal .modal-xl {
  min-width: 80%;
  width: 80%;
}
/* #modalProformas___BV_modal_body_ {
  min-width: 80%;
  width: 80%;
} */
html,
body {
  height: 100%;
}

body {
  background-color: #f5f7fa; /* tu color */
  margin: 0;
  font-family: Roboto, Arial, sans-serif;
}

.container,
.container-fluid {
  background-color: transparent; /* deja ver el fondo del body */
}
.btnAction {
  margin: 2px 0;
  border-radius: 25px !important;
  border: 0px !important;
  -webkit-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.751);
  -moz-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.751);
  box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.751);
  font-size: 1.2rem !important;
  font-weight: bold !important;
  display: block;
  width: 100%;
  margin: 1.5% 0px;
  text-transform: uppercase;
  /* margin: 20px 0 0; */
}

.btnAction2 {
  margin: 2px 0;
  border-radius: 25px !important;
  border: 0px !important;
  -webkit-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.751);
  -moz-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.751);
  box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.751);
  font-size: 1.2rem !important;
  font-weight: bold !important;
  margin: 1.5% 0px;
  text-transform: uppercase;
  /* margin: 20px 0 0; */
}
</style>
