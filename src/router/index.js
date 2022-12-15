import Vue from 'vue'
import Router from 'vue-router'
// import HelloWorld from '@/components/HelloWorld'
import Movimiento from '@/views/Movimiento.vue'
import Productos from '@/views/Productos.vue'
import Almacen from '@/views/Almacen.vue'
import Ventas from '@/views/Ventas.vue'
import VentaReimpresion from '@/views/VentaReimpresion.vue'
import VentasFacturacion from '@/views/VentasFacturacion.vue'
import Clientes from '@/views/Clientes.vue'
import Habitaciones from '@/views/Habitaciones.vue'
import Impresoras from '@/views/Impresoras.vue'
import Proveedores from '@/views/Proveedores.vue'
import Cobros from '@/views/Cobros.vue'
import MovimientoCantidad from '@/views/MovimientoCantidad.vue'
import OrdenCompra from '@/views/OrdenCompra.vue'
import CalculoPvp from '@/views/CalculoPvp.vue'
import OrdenesCompra from '@/views/OrdenesCompra.vue'

// Reportes
import ReporteVentas from '@/views/reportes/Ventas.vue'
import StockMinimo from '@/views/reportes/StockMinimo.vue'
import Comisionistas from '@/views/reportes/Comisionistas.vue'
import DeudaClientes from '@/views/reportes/DeudaClientes.vue'
import DeudaProveedores from '@/views/reportes/DeudaProveedores.vue'
import RankingClientes from '@/views/reportes/RankingClientes.vue'
import CierreCaja from '@/views/reportes/CierreCaja.vue'
import ReporteCierreCaja from '@/views/reportes/ReporteCierreCaja.vue'
import Proforma from '@/views/reportes/Proforma.vue'
import Kardex from '@/views/reportes/Kardex.vue'
import RegistroHuespedes from '@/views/reportes/RegistroHuespedes.vue'
import Articulosmovimientos from '@/views/reportes/Articulosmovimientos.vue'
import PorEntregar from '@/views/reportes/PorEntregar.vue'
import Remision from '@/views/Remision.vue'
import RemisionLista from '@/views/RemisionLista.vue'
import Estadisticas from '@/views/reportes/Estadisticas.vue'
import ArticulosResumen from '@/views/reportes/ArticulosResumen.vue'
import Ordenes from '@/views/reportes/Ordenes.vue'
import Confrontacion from '@/views/reportes/Confrontacion.vue'

Vue.use(Router)

export default new Router({
  // mode: 'history',
  routes: [
    {
      path: '/',
      name: 'Movimiento',
      component: Movimiento
    },
    {
      path: '/productos',
      name: 'Productos',
      component: Productos
    },
    {
      path: '/reimpresion',
      name: 'VentaReimpresion',
      component: VentaReimpresion
    },
    {
      path: '/clientes',
      name: 'Clientes',
      component: Clientes
    },
    {
      path: '/habitaciones',
      name: 'Habitaciones',
      component: Habitaciones
    },
    {
      path: '/almacen',
      name: 'Almacen',
      component: Almacen
    },
    {
      path: '/ventas',
      name: 'Ventas',
      component: Ventas
    },
    {
      path: '/reportes/ventas',
      name: 'ReporteVentas',
      component: ReporteVentas
    },
    {
      path: '/reportes/cierrecaja',
      name: 'CierreCaja',
      component: CierreCaja
    },
    {
      path: '/reportes/proforma/:id',
      name: 'Proforma',
      component: Proforma
    },
    {
      path: '/reportes/kardex',
      name: 'Kardex',
      component: Kardex
    },
    {
      path: '/reportes/cierrecaja/:id',
      name: 'CierreCajaId',
      component: CierreCaja
    },
    {
      path: '/reportes/reportecierrecaja',
      name: 'ReporteCierreCaja',
      component: ReporteCierreCaja
    },
    {
      path: '/reportes/stockminimo',
      name: 'StockMinimo',
      component: StockMinimo
    },
    {
      path: '/reportes/comisionistas',
      name: 'comisionistas',
      component: Comisionistas
    },
    {
      path: '/reportes/deudaclientes',
      name: 'DeudaClientes',
      component: DeudaClientes
    },    
    {
      path: '/reportes/deudaproveedores',
      name: 'DeudaProveedores',
      component: DeudaProveedores
    },
    {
      path: '/reportes/rankingclientes',
      name: 'RankingClientes',
      component: RankingClientes
    },
    {
      path: '/reportes/huespedes',
      name: 'RegistroHuespedes',
      component: RegistroHuespedes
    },
    {
      path: '/reportes/articulosmovimientos',
      name: 'Articulosmovimientos',
      component: Articulosmovimientos
    },
    {
      path: '/facturacion',
      name: 'VentasFacturacion',
      component: VentasFacturacion
    },
    {
      path: '/porentregar',
      name: 'PorEntregar',
      component: PorEntregar
    },
    {
      path: '/remision',
      name: 'Remision',
      component: Remision
    },
    {
      path: '/remision-lista',
      name: 'RemisionLista',
      component: RemisionLista
    },
    {
      path: '/impresoras',
      name: 'Impresoras',
      component: Impresoras
    },
    {
      path: '/proveedores',
      name: 'Proveedores',
      component: Proveedores
    },
    {
      path: '/reportes/estadisticas',
      name: 'Estadisticas',
      component: Estadisticas
    },
    {
      path: '/reportes/articulosresumen',
      name: 'ArticulosResumen',
      component: ArticulosResumen
    },
    {
      path: '/cobros',
      name: 'Cobros',
      component: Cobros
    },
    {
      path: '/movimiento/cantidad',
      name: 'MovimientoCantidad',
      component: MovimientoCantidad
    },
    {
      path: '/orden',
      name: 'OrdenCompra',
      component: OrdenCompra
    },
    {
      path: '/calculopvp',
      name: 'CalculoPvp',
      component: CalculoPvp
    },
    {
      path: '/ordenescompra',
      name: 'OrdenesCompra',
      component: OrdenesCompra
    },
    {
      path: '/reporte/ordenes',
      name: 'Ordenes',
      component: Ordenes
    },
    {
      path: '/reporte/confrontacion',
      name: 'Confrontacion',
      component: Confrontacion
    },
  ]
})
