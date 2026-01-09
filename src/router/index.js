import Vue from 'vue'
import Router from 'vue-router'
// import HelloWorld from '@/components/HelloWorld'
import Movimiento from '@/views/Movimiento.vue'
import Productos from '@/views/Productos.vue'
import Almacen from '@/views/Almacen.vue'
import Ventas from '@/views/Ventas.vue'
import VentasFacturacion from '@/views/VentasFacturacion.vue'

// Reportes
import ReporteVentas from '@/views/reportes/Ventas.vue'
import StockMinimo from '@/views/reportes/StockMinimo.vue'
import CierreCaja from '@/views/reportes/CierreCaja.vue'
import Proforma from '@/views/reportes/Proforma.vue'
import Kardex from '@/views/reportes/Kardex.vue'
import Remision from '@/views/Remision.vue'
import RemisionLista from '@/views/RemisionLista.vue'
import BusquedaPorModelo from '@/views/BusquedaPorModelo.vue'

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
      path: '/reportes/cierrecaja/:id',
      name: 'CierreCajaId',
      component: CierreCaja
    },
    {
      path: '/reportes/stockminimo',
      name: 'StockMinimo',
      component: StockMinimo
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
      path: '/facturacion',
      name: 'VentasFacturacion',
      component: VentasFacturacion
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
      path: '/BusquedaPorModelo',
      name: 'BusquedaPorModelo',
      component: BusquedaPorModelo
    },
  ]
})
