import Vue from 'vue'
import Router from 'vue-router'
// import HelloWorld from '@/components/HelloWorld'
import Movimiento from '@/views/Movimiento.vue'
import Productos from '@/views/Productos.vue'
import Almacen from '@/views/Almacen.vue'
import Ventas from '@/views/Ventas.vue'
import VentasFacturacion from '@/views/VentasFacturacion.vue'
import Clientes from '@/views/Clientes.vue'
import VentaEstrategica from '@/views/VentaEstrategica.vue'
import VentaReimpresion from '@/views/VentaReimpresion.vue'
import ProductosSimilares from '@/views/ProductosSimilares.vue'
import Configuraciones from '@/views/Configuraciones.vue'
import ProductosVencer from '@/views/ProductosVencer.vue'

// Reportes
import ReporteVentas from '@/views/reportes/Ventas.vue'
import CierreCaja from '@/views/reportes/CierreCaja.vue'
import Proforma from '@/views/reportes/Proforma.vue'
import Kardex from '@/views/reportes/Kardex.vue'
import ClientesVip from '@/views/reportes/ClientesVip.vue'

import Remision from '@/views/Remision.vue'
import RemisionLista from '@/views/RemisionLista.vue'

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
      path: '/consultaventaestrategica',
      name: 'VentaEstrategica',
      component: VentaEstrategica
    },
    {
      path: '/productossimilares',
      name: 'ProductosSimilares',
      component: ProductosSimilares
    },
    {
      path: '/configuraciones',
      name: 'Configuraciones',
      component: Configuraciones
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
      path: '/reportes/clientesvip',
      name: 'ClientesVip',
      component: ClientesVip
    },
    {
      path: '/productosvencer',
      name: 'ProductosVencer',
      component: ProductosVencer
    },
  ]
})
