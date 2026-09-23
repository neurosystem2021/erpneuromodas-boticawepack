import Vue from "vue";
import Router from "vue-router";
// import HelloWorld from '@/components/HelloWorld'
import Movimiento from "@/views/Movimiento.vue";
import Productos from "@/views/Productos.vue";
import Almacen from "@/views/Almacen.vue";
import Ventas from "@/views/Ventas.vue";
import VentasFacturacion from "@/views/VentasFacturacion.vue";
import Clientes from "@/views/Clientes.vue";
import VentaEstrategica from "@/views/VentaEstrategica.vue";
import VentaReimpresion from "@/views/VentaReimpresion.vue";
import ProductosSimilares from "@/views/ProductosSimilares.vue";
import Configuraciones from "@/views/Configuraciones.vue";
import Promociones from "@/views/Promociones";

// Reportes
import ReporteVentas from "@/views/reportes/Ventas.vue";
import CierreCaja from "@/views/reportes/CierreCaja.vue";
import Proforma from "@/views/reportes/Proforma.vue";
import Kardex from "@/views/reportes/Kardex.vue";
import DeudaClientes from "@/views/reportes/DeudaClientes.vue";
import StockMinimo from "@/views/reportes/StockMinimo.vue";
import Articulosmovimientos from "@/views/reportes/Articulosmovimientos.vue";
import Vencimiento from "@/views/reportes/Vencimiento.vue";
import Estadisticas from "@/views/reportes/Estadisticas.vue";
import Dashboard from "@/views/reportes/Dashboard.vue";
import ReporteRegMovimiento from "@/views/reportes/ReporteRegMovimiento.vue";
import ReporteRegistroVentas from "@/views/reportes/ReporteRegistroVentas.vue";
import ReporteUtilidadBrutaPorUsuario from "@/views/reportes/ReporteUtilidadBrutaPorUsuario.vue";
import ReporteUtilidadBruta from "@/views/reportes/ReporteUtilidadBruta.vue";
import CajaBancos from "@/views/CajaBancos.vue";
import BuscadorCajaBanco from "@/views/BuscadorCajaBanco.vue";
import ProductoVencido from "@/views/reportes/ProductoVencido";
import ReporteDetallado from "@/views/reportes/ReporteDetallado";
import ReporteProductosConsolidado from "@/views/reportes/ReporteProductosConsolidado";
import ReporteDeudaClientesUsuario from "@/views/reportes/ReporteDeudaClientesUsuario";
Vue.use(Router);

export default new Router({
  // mode: 'history',
  routes: [
    {
      path: "/",
      name: "Movimiento",
      component: Movimiento
    },
    {
      path: "/productos",
      name: "Productos",
      component: Productos
    },
    {
      path: "/reimpresion",
      name: "VentaReimpresion",
      component: VentaReimpresion
    },
    {
      path: "/clientes",
      name: "Clientes",
      component: Clientes
    },
    {
      path: "/almacen",
      name: "Almacen",
      component: Almacen
    },
    {
      path: "/ventas",
      name: "Ventas",
      component: Ventas
    },
    {
      path: "/reportes/ventas",
      name: "ReporteVentas",
      component: ReporteVentas
    },
    {
      path: "/reportes/cierrecaja",
      name: "CierreCaja",
      component: CierreCaja
    },
    {
      path: "/reportes/cierrecaja/:id",
      name: "CierreCajaId",
      component: CierreCaja
    },
    {
      path: "/reportes/proforma/:id",
      name: "Proforma",
      component: Proforma
    },
    {
      path: "/reportes/deudaclientes",
      name: "DeudaClientes",
      component: DeudaClientes
    },
    {
      path: "/reportes/kardex",
      name: "Kardex",
      component: Kardex
    },
    {
      path: "/facturacion",
      name: "VentasFacturacion",
      component: VentasFacturacion
    },
    {
      path: "/consultaventaestrategica",
      name: "VentaEstrategica",
      component: VentaEstrategica
    },
    {
      path: "/productossimilares",
      name: "ProductosSimilares",
      component: ProductosSimilares
    },
    {
      path: "/configuraciones",
      name: "Configuraciones",
      component: Configuraciones
    },
    {
      path: "/reportes/stockminimo",
      name: "StockMinimo",
      component: StockMinimo
    },
    {
      path: "/reportes/articulosmovimientos",
      name: "Articulosmovimientos",
      component: Articulosmovimientos
    },
    {
      path: "/reportes/vencimiento",
      name: "Vencimiento",
      component: Vencimiento
    },
    {
      path: "/reportes/estadisticas",
      name: "Estadisticas",
      component: Estadisticas
    },
    {
      path: "/reportes/dashboard",
      name: "Dashboard",
      component: Dashboard
    },
    {
      path: "/reporte/reporteregmovimiento",
      name: "ReporteRegMovimiento",
      component: ReporteRegMovimiento
    },
    {
      path: "/reportes/ReporteRegistroVentas",
      name: "ReporteRegistroVentas",
      component: ReporteRegistroVentas
    },
    {
      path: "/reportes/reporte_utilidad_bruta",
      name: "ReporteUtilidadBrutaPorUsuario",
      component: ReporteUtilidadBrutaPorUsuario
    },
    {
      path: "/reportes/ReporteUtilidadBruta",
      name: "ReporteUtilidadBruta",
      component: ReporteUtilidadBruta
    },
    {
      path: "/cajabancos",
      name: "CajaBancos",
      component: CajaBancos
    },

    {
      path: "/BuscadorCajaBanco",
      name: "BuscadorCajaBanco",
      component: BuscadorCajaBanco
    },
    {
      path: "/ProductoVencido",
      name: "ProductoVencido",
      component: ProductoVencido
    },
    {
      path: "/Promociones",
      name: "Promociones",
      component: Promociones
    },
    {
      path: "/reportes/ReporteDetallado",
      name: "ReporteDetallado",
      component: ReporteDetallado
    },
    {
      path: "/reportes/ReporteProductosConsolidado",
      name: "ReporteProductosConsolidado",
      component: ReporteProductosConsolidado
    },
    {
      path: "/reportes/ReporteDeudaClientesUsuario",
      name: "ReporteDeudaClientesUsuario",
      component: ReporteDeudaClientesUsuario
    }
  ]
});
