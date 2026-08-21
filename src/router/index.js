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
import Remision from "@/views/Remision.vue";
import RemisionLista from "@/views/RemisionLista.vue";
import Venta from "@/views/Venta.vue";
import Venta2 from "@/views/Venta2.vue";
import CategoriasNuevo from "@/views/CategoriasNuevo.vue";
import Proveedores from "@/views/Proveedores.vue";
import CajaBancos from "@/views/CajaBancos.vue";
import Usuario from "@/views/Usuario.vue";
import OrdenCompra from "@/views/OrdenCompra.vue";
import VentaListado from "@/views/VentaListado.vue";
import Confrontacion from "@/views/reportes/Confrontacion.vue";
import BusquedaPorModelo from "@/views/BusquedaPorModelo.vue";
import ConfrontacionRegistros from "@/views/ConfrontacionRegistros.vue";
import GestorDescargasPdf from "@/views/GestorDescargasPdf.vue";
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
import Estadisticas from "@/views/reportes/Estadisticas.vue";
import Dashboard from "@/views/reportes/Dashboard.vue";
import ReporteRegMovimiento from "@/views/reportes/ReporteRegMovimiento.vue";
import ReporteUtilidadBruta from "@/views/reportes/ReporteUtilidadBruta.vue";
import ReporteRegistroVentas from "@/views/reportes/ReporteRegistroVentas.vue";
import KardexValorizado from "@/views/KardexValorizado.vue";
import PreOrden from "@/views/PreOrden.vue";
import CargarComprasXml from "@/views/CargarComprasXml.vue";
import RemisionVer from "@/views/RemisionVer.vue";
import BuscadorCajaBanco from "@/views/BuscadorCajaBanco.vue";
import ReporteMoviminetoPorProveedor from "@/views/reportes/ReporteMoviminetoPorProveedor.vue";
import RemisionListado from '@/views/reportes/RemisionListado.vue'
import ReporteUtilidadBrutaPorUsuario from '@/views/reportes/ReporteUtilidadBrutaPorUsuario.vue'
import ReporteSireMovimiento from '@/views/reportes/ReporteSireMovimiento.vue'
import ReporteSireVentas from '@/views/reportes/ReporteSireVentas.vue'

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
      path: "/usuario",
      name: "Usuario",
      component: Usuario
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
      path: "/cajabancos",
      name: "CajaBancos",
      component: CajaBancos
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
      path: "/reportes/estadisticas",
      name: "Estadisticas",
      component: Estadisticas
    },
    {
      path: "/venta",
      name: "Venta",
      component: Venta
    },
    {
      path: "/VentaListado",
      name: "VentaListado",
      component: VentaListado
    },
    {
      path: "/venta2",
      name: "Venta2",
      component: Venta2
    },
    {
      path: "/categoriasnuevo",
      name: "CategoriasNuevo",
      component: CategoriasNuevo
    },
    {
      path: "/proveedores",
      name: "Proveedores",
      component: Proveedores
    },
    {
      path: "/reporte/dashboard",
      name: "Dashboard",
      component: Dashboard
    },
    {
      path: "/reporte/reporteregmovimiento",
      name: "ReporteRegMovimiento",
      component: ReporteRegMovimiento
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
      path: "/KardexValorizado",
      name: "KardexValorizado",
      component: KardexValorizado
    },
    {
      path: "/PreOrden",
      name: "PreOrden",
      component: PreOrden
    },
    {
      path: "/reporte/confrontacion",
      name: "Confrontacion",
      component: Confrontacion
    },
    {
      path: "/CargarComprasXml",
      name: "CargarComprasXml",
      component: CargarComprasXml
    },
    {
      path: "/BuscadorCajaBanco",
      name: "BuscadorCajaBanco",
      component: BuscadorCajaBanco
    },
    {
      path: "/reportes/reporte_movimineto_proveedor",
      name: "ReporteMoviminetoPorProveedor",
      component: ReporteMoviminetoPorProveedor
    },
    {
      path: "/remision/ver/:idDocVenta/:Serie/:Numero",
      name: "RemisionVer",
      component: RemisionVer
    },
    {
      path: "/remision-lista",
      name: "RemisionLista",
      component: RemisionLista
    },
    {
      path: "/reportes/reporte_utilidad_bruta",
      name: "ReporteUtilidadBrutaPorUsuario",
      component: ReporteUtilidadBrutaPorUsuario
    },
    {
      path: "/BusquedaPorModelo",
      name: "BusquedaPorModelo",
      component: BusquedaPorModelo
    },
    {
      path: "/ConfrontacionRegistros",
      name: "ConfrontacionRegistros",
      component: ConfrontacionRegistros
    },
    {
      path: "/GestorDescargasPdf",
      name: "GestorDescargasPdf",
      component: GestorDescargasPdf
    },
    {
      path: "/reportes/ReporteSireMovimiento",
      name: "ReporteSireMovimiento",
      component: ReporteSireMovimiento
    },
    {
      path: "/reportes/ReporteSireVentas",
      name: "ReporteSireVentas",
      component: ReporteSireVentas
    },
  ]
});
