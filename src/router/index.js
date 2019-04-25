import Vue from 'vue'
import Router from 'vue-router'
// import HelloWorld from '@/components/HelloWorld'
import Movimiento from '@/views/Movimiento.vue'
import Productos from '@/views/Productos.vue'
import Almacen from '@/views/Almacen.vue'
import Ventas from '@/views/Ventas.vue'
import VentasFacturacion from '@/views/VentasFacturacion.vue'
import Clientes from '@/views/Clientes.vue'
import Traslado from '@/views/Traslado.vue'

// Reportes
import ReporteVentas from '@/views/reportes/Ventas.vue'
import StockMinimo from '@/views/reportes/StockMinimo.vue'
import CierreCaja from '@/views/reportes/CierreCaja.vue'
import Proforma from '@/views/reportes/Proforma.vue'

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
      path: '/clientes',
      name: 'Clientes',
      component: Clientes
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
      path: '/reportes/proforma/:id',
      name: 'Proforma',
      component: Proforma
    },
    {
      path: '/reportes/stockminimo',
      name: 'StockMinimo',
      component: StockMinimo
    },
    {
      path: '/facturacion',
      name: 'VentasFacturacion',
      component: VentasFacturacion
    },
    {
      path: '/traslado',
      name: 'Traslado',
      component: Traslado
    }
  ]
})
