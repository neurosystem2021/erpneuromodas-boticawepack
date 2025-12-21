import Vue from "vue";
import Router from "vue-router";
// import HelloWorld from '@/components/HelloWorld'
import Movimiento from "@/views/Movimiento.vue";
import Productos from "@/views/Productos.vue";
import Almacen from "@/views/Almacen.vue";
import Ventas from "@/views/Ventas.vue";
import VentaReimpresion from "@/views/VentaReimpresion.vue";
import VentasFacturacion from "@/views/VentasFacturacion.vue";
import Clientes from "@/views/Clientes.vue";
import Habitaciones from "@/views/Habitaciones.vue";
import Impresoras from "@/views/Impresoras.vue";
import AgenteOperacion from "@/views/AgenteOperacion.vue";
import AgenteCierreCaja from "@/views/AgenteCierreCaja.vue";
import AgenteGeneral from "@/views/AgenteGeneral.vue";
import AgenteReimpresion from "@/views/AgenteReimpresion.vue";
import Traslado from "@/views/Traslado.vue";
import ConfigurarOferta from "@/views/ConfigurarOferta.vue";
import OrdenCompra from "@/views/OrdenCompra.vue";
import BuscadorCajaBanco from "@/views/BuscadorCajaBanco.vue";
import Usuario from "@/views/Usuario.vue";
import CajaBancos from "@/views/CajaBancos.vue";
import RemisionTransportista from "@/views/RemisionTransportista.vue";

// Reportes
import Ordenes from "@/views/reportes/Ordenes.vue";
import ReporteVentas from "@/views/reportes/Ventas.vue";
import StockMinimo from "@/views/reportes/StockMinimo.vue";
import Comisionistas from "@/views/reportes/Comisionistas.vue";
import DeudaClientes from "@/views/reportes/DeudaClientes.vue";
import DeudaProveedores from "@/views/reportes/DeudaProveedores.vue";
import RankingClientes from "@/views/reportes/RankingClientes.vue";
import CierreCaja from "@/views/reportes/CierreCaja.vue";
import ReporteCierreCaja from "@/views/reportes/ReporteCierreCaja.vue";
import Proforma from "@/views/reportes/Proforma.vue";
import Kardex from "@/views/reportes/Kardex.vue";
import RegistroHuespedes from "@/views/reportes/RegistroHuespedes.vue";
import Articulosmovimientos from "@/views/reportes/Articulosmovimientos.vue";
import PorEntregar from "@/views/reportes/PorEntregar.vue";
import Remision from "@/views/Remision.vue";
import RemisionLista from "@/views/RemisionLista.vue";
import Estadisticas from "@/views/reportes/Estadisticas.vue";
import ReporteProyectos from "@/views/reportes/ReporteProyectos.vue";
import ReporteUtilidadBruta from "@/views/reportes/ReporteUtilidadBruta.vue";
import Confrontacion from "@/views/reportes/Confrontacion.vue";
import HistorialVentaKit from "@/views/reportes/HistorialVentaKit.vue";
import ReporteUtilidadBrutaPorUsuario from "@/views/reportes/ReporteUtilidadBrutaPorUsuario.vue";
import CargarComprasXml from "@/views/CargarComprasXml.vue";
import ReporteRegMovimiento from "@/views/reportes/ReporteRegMovimiento.vue";
import ReporteRegistroVentas from "@/views/reportes/ReporteRegistroVentas.vue";
import Dashboard from "@/views/reportes/Dashboard.vue";
import VentasDelivery from "@/views/reportes/VentasDelivery.vue";
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
      path: "/habitaciones",
      name: "Habitaciones",
      component: Habitaciones
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
      path: "/OrdenCompra",
      name: "OrdenCompra",
      component: OrdenCompra
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
      path: "/reportes/Ordenes",
      name: "Ordenes",
      component: Ordenes
    },
    {
      path: "/reportes/cierrecaja",
      name: "CierreCaja",
      component: CierreCaja
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
      path: "/reportes/cierrecaja/:id",
      name: "CierreCajaId",
      component: CierreCaja
    },
    {
      path: "/reportes/reportecierrecaja",
      name: "ReporteCierreCaja",
      component: ReporteCierreCaja
    },
    {
      path: "/reportes/stockminimo",
      name: "StockMinimo",
      component: StockMinimo
    },
    {
      path: "/reportes/comisionistas",
      name: "comisionistas",
      component: Comisionistas
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
      path: "/reportes/huespedes",
      name: "RegistroHuespedes",
      component: RegistroHuespedes
    },
    {
      path: "/reportes/articulosmovimientos",
      name: "Articulosmovimientos",
      component: Articulosmovimientos
    },
    {
      path: "/facturacion",
      name: "VentasFacturacion",
      component: VentasFacturacion
    },
    {
      path: "/porentregar",
      name: "PorEntregar",
      component: PorEntregar
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
    //Nuevo modulos de agente
    {
      path: "/agente/operacion",
      name: "AgenteOperacion",
      component: AgenteOperacion
    },
    {
      path: "/agente/cierrecaja",
      name: "AgenteCierreCaja",
      component: AgenteCierreCaja
    },
    {
      path: "/agente/cierrecaja/:id",
      name: "AgenteCierreCajaId",
      component: AgenteCierreCaja
    },
    {
      path: "/agente/general",
      name: "AgenteGeneral",
      component: AgenteGeneral
    },
    {
      path: "/agente/reimpresion",
      name: "AgenteReimpresion",
      component: AgenteReimpresion
    },
    {
      path: "/reportes/proyectos",
      name: "ReporteProyectos",
      component: ReporteProyectos
    },
    {
      path: "/traslado",
      name: "Traslado",
      component: Traslado
    },
    {
      path: "/reporte_utlidad_bruta",
      name: "ReporteUtilidadBruta",
      component: ReporteUtilidadBruta
    },
    {
      path: "/HistorialVentaKit",
      name: "HistorialVentaKit",
      component: HistorialVentaKit
    },

    {
      path: "/oferta",
      name: "ConfigurarOferta",
      component: ConfigurarOferta
    },
    {
      path: "/CargarComprasXml",
      name: "CargarComprasXml",
      component: CargarComprasXml
    },
    {
      path: "/reportes/reporte_utilidad_bruta",
      name: "ReporteUtilidadBrutaPorUsuario",
      component: ReporteUtilidadBrutaPorUsuario
    },
    {
      path: "/reporte/confrontacion",
      name: "Confrontacion",
      component: Confrontacion
    },
    {
      path: "/reporte/BuscadorCajaBanco",
      name: "BuscadorCajaBanco",
      component: BuscadorCajaBanco
    },
    {
      path: "/reportes/ReporteRegMovimiento",
      name: "ReporteRegMovimiento",
      component: ReporteRegMovimiento
    },
    {
      path: "/reportes/ReporteRegistroVentas",
      name: "ReporteRegistroVentas",
      component: ReporteRegistroVentas
    },
    {
      path: "/RemisionTransportista",
      name: "CajaBancos",
      component: RemisionTransportista
    },
    {
      path: "/CajaBancos",
      name: "CajaBancos",
      component: CajaBancos
    },
    {
      path: "/reportes/Dashboard",
      name: "Dashboard",
      component: Dashboard
    },
    {
      path: "/reportes/VentasDelivery",
      name: "VentasDelivery",
      component: VentasDelivery
    },
  ]
});
