import Vue from "vue";
import Router from "vue-router";
// import HelloWorld from '@/components/HelloWorld'
import Movimiento from "@/views/Movimiento.vue";
import Productos from "@/views/Productos.vue";
import Almacen from "@/views/Almacen.vue";
import Usuario from "@/views/Usuario.vue";
import Ventas from "@/views/Ventas.vue";
import VentaReimpresion from "@/views/VentaReimpresion.vue";
import VentasFacturacion from "@/views/VentasFacturacion.vue";
import Clientes from "@/views/Clientes.vue";
import Remision from "@/views/Remision.vue";
import ConfigurarOferta from "@/views/ConfigurarOferta.vue";
import HistorialGalones from "@/views/HistorialGalones.vue";
import Liquido from "@/views/Liquido.vue";
import GLP from "@/views/GLP.vue";
import Alta from "@/views/Alta.vue";
import CajaBancos from "@/views/CajaBancos.vue";
import Dashboard from "@/views/reportes/Dashboard.vue";
import KardexValorizado from "@/views/KardexValorizado.vue";
import ReporteUtilidadBruta from "@/views/reportes/ReporteUtilidadBruta.vue";
import ReporteRegMovimiento from "@/views/reportes/ReporteRegMovimiento.vue";
import ReporteRegistroVentas from "@/views/reportes/ReporteRegistroVentas.vue";
// Reportes
import ReporteVentas from "@/views/reportes/Ventas.vue";
import StockMinimo from "@/views/reportes/StockMinimo.vue";
import DeudaClientes from "@/views/reportes/DeudaClientes.vue";
import DeudaProveedores from "@/views/reportes/DeudaProveedores.vue";
import RankingClientes from "@/views/reportes/RankingClientes.vue";
import CierreCaja from "@/views/reportes/CierreCaja.vue";
import Proforma from "@/views/reportes/Proforma.vue";
import Vales from "@/views/reportes/Vales.vue";
import RemisionLista from "@/views/RemisionLista.vue";
import ValesFacturados from "@/views/reportes/ValesFacturados.vue";
import ReporteCantidad from "@/views/reportes/ReporteCantidad.vue";
import Estadisticas from "@/views/reportes/Estadisticas.vue";
import ReporteGalones from "@/views/reportes/ReporteGalones.vue";

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
      path: "/usuario",
      name: "Usuario",
      component: Usuario
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
      path: "/KardexValorizado",
      name: "KardexValorizado",
      component: KardexValorizado
    },
    {
      path: "/cajabancos",
      name: "CajaBancos",
      component: CajaBancos
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
      path: "/reportes/stockminimo",
      name: "StockMinimo",
      component: StockMinimo
    },
    {
      path: "/reportes/deudaclientes",
      name: "DeudaClientes",
      component: DeudaClientes
    },
    {
      path: "/reportes/deudaproveedores",
      name: "DeudaProveedores",
      component: DeudaProveedores
    },
    {
      path: "/reportes/rankingclientes",
      name: "RankingClientes",
      component: RankingClientes
    },
    {
      path: "/facturacion",
      name: "VentasFacturacion",
      component: VentasFacturacion
    },
    {
      path: "/vales",
      name: "Vales",
      component: Vales
    },
    {
      path: "/remision",
      name: "Remision",
      component: Remision
    },
    {
      path: "/remision-lista",
      name: "RemisionLista",
      component: RemisionLista
    },
    {
      path: "/vales-facturados",
      name: "ValesFacturados",
      component: ValesFacturados
    },
    {
      path: "/historial-galones",
      name: "HistorialGalones",
      component: HistorialGalones
    },
    {
      path: "/reportes/cantidades",
      name: "ReporteCantidad",
      component: ReporteCantidad
    },
    {
      path: "/reportes/estadisticas",
      name: "Estadisticas",
      component: Estadisticas
    },
    {
      path: "/oferta",
      name: "ConfigurarOferta",
      component: ConfigurarOferta
    },
    {
      path: "/liquidos",
      name: "Liquido",
      component: Liquido
    },
    {
      path: "/glp",
      name: "GLP",
      component: GLP
    },
    {
      path: "/alta",
      name: "Alta",
      component: Alta
    },
    {
      path: "/reportes/galones",
      name: "ReporteGalones",
      component: ReporteGalones
    },
    {
      path: "/reporte/dashboard",
      name: "Dashboard",
      component: Dashboard
    },
    {
      path: "/reporte/reporteutilidadbruta",
      name: "ReporteUtilidadBruta",
      component: ReporteUtilidadBruta
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
    }
  ]
});
