import Vue from 'vue'
import Router from 'vue-router'
// import HelloWorld from '@/components/HelloWorld'
import Movimiento from '@/views/Movimiento.vue'
import Productos from '@/views/Productos.vue'
import GestionAlmacen from '@/views/GestionAlmacen.vue'
import Almacen from '@/views/Almacen.vue'
import Ventas from '@/views/Ventas.vue'
import VentaReimpresion from '@/views/VentaReimpresion.vue'
import VentasFacturacion from '@/views/VentasFacturacion.vue'
import Traslado from '@/views/Traslado.vue'

// Reportes
import ReporteVentas from '@/views/reportes/Ventas.vue'
import StockMinimo from '@/views/reportes/StockMinimo.vue'
import DeudaClientes from '@/views/reportes/DeudaClientes.vue'
import DeudaProveedores from '@/views/reportes/DeudaProveedores.vue'
import RankingClientes from '@/views/reportes/RankingClientes.vue'
import CierreCaja from '@/views/reportes/CierreCaja.vue'
import Proforma from '@/views/reportes/Proforma.vue'
import Kardex from '@/views/reportes/Kardex.vue'

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
      path: '/gestion/almacenes',
      name: 'GestionAlmacen',
      component: GestionAlmacen
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
      path: '/traslado',
      name: 'Traslado',
      component: Traslado
     }
  ]
})
