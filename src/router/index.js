import Vue from "vue";
import Router from "vue-router";
// import HelloWorld from '@/components/HelloWorld'
import Movimiento from "@/views/Movimiento.vue";
import Productos from "@/views/Productos.vue";
import GestionAlmacen from "@/views/GestionAlmacen.vue";
import Almacen from "@/views/Almacen.vue";
import Ventas from "@/views/Ventas.vue";
import VentasFacturacion from "@/views/VentasFacturacion.vue";
import VentaReimpresion from "@/views/VentaReimpresion.vue";
import Remision from "@/views/Remision.vue";
import RemisionLista from "@/views/RemisionLista.vue";
import Impresoras from "@/views/Impresoras.vue";

import OrdenCompra from "@/views/OrdenCompra.vue";

// Reportes
import Ordenes from "@/views/reportes/Ordenes.vue";

import Estadisticas from "@/views/reportes/Estadisticas.vue";
import ReporteVentas from "@/views/reportes/Ventas.vue";
import StockMinimo from "@/views/reportes/StockMinimo.vue";
import DeudaClientes from "@/views/reportes/DeudaClientes.vue";
import DeudaProveedores from "@/views/reportes/DeudaProveedores.vue";
import RankingClientes from "@/views/reportes/RankingClientes.vue";
import CierreCaja from "@/views/reportes/CierreCaja.vue";
import Proforma from "@/views/reportes/Proforma.vue";
import Kardex from "@/views/reportes/Kardex.vue";
import Articulosmovimientos from "@/views/reportes/Articulosmovimientos.vue";
import ReporteUtilidadBrutaPorUsuario from "@/views/reportes/ReporteUtilidadBrutaPorUsuario.vue";
import ReporteMoviminetoPorProveedor from "@/views/reportes/ReporteMoviminetoPorProveedor.vue";
import ReporteRankingCliente from "@/views/reportes/ReporteRankingCliente.vue";
import Formato from "@/views/reportes/Formato.vue";
import Usuario from "@/views/Usuario.vue";
import ReporteUtilidadBruta from "@/views/reportes/ReporteUtilidadBruta.vue";
import ReporteRegistroVentas from "@/views/reportes/ReporteRegistroVentas.vue";
import CajaBancos from "@/views/CajaBancos.vue";
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
      path: "/gestion/almacenes",
      name: "GestionAlmacen",
      component: GestionAlmacen
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
      path: "/usuario",
      name: "Usuario",
      component: Usuario
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
      path: "/reportes/proforma/:id",
      name: "Proforma",
      component: Proforma
    },
    {
      path: "/reportes/kardex",
      name: "Kardex",
      component: Kardex
    },
    {
      path: "/reportes/articulosmovimientos",
      name: "Articulosmovimientos",
      component: Articulosmovimientos
    },
    {
      path: "/reportes/formato",
      name: "formato",
      component: Formato
    },
    {
      path: "/facturacion",
      name: "VentasFacturacion",
      component: VentasFacturacion
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
      path: "/impresoras",
      name: "Impresoras",
      component: Impresoras
    },
    {
      path: "/reportes/estadisticas",
      name: "Estadisticas",
      component: Estadisticas
    },
    {
      path: "/reportes/reporte_utilidad_bruta",
      name: "ReporteUtilidadBrutaPorUsuario",
      component: ReporteUtilidadBrutaPorUsuario
    },
    {
      path: "/reportes/reporte_movimineto_proveedor",
      name: "ReporteMoviminetoPorProveedor",
      component: ReporteMoviminetoPorProveedor
    },
    {
      path: "/reportes/reporte_ranking_cliente",
      name: "ReporteRankingCliente",
      component: ReporteRankingCliente
    },
    {
      path: "/OrdenCompra",
      name: "OrdenCompra",
      component: OrdenCompra
    },

    {
      path: "/reportes/Ordenes",
      name: "Ordenes",
      component: Ordenes
    },
    {
      path: "/reporte/reporteutilidadbruta",
      name: "ReporteUtilidadBruta",
      component: ReporteUtilidadBruta
    },
    {
      path: "/reportes/ReporteRegistroVentas",
      name: "ReporteRegistroVentas",
      component: ReporteRegistroVentas
    },
    {
      path: "/cajabancos",
      name: "CajaBancos",
      component: CajaBancos
    }
  ]
});
