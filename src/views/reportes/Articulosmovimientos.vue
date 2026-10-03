<template>
  <div class="reporte-movimientos">

    <!-- ENCABEZADO -->
    <b-container fluid class="px-4 py-3">

      <div class="page-header mb-4">
        <div>
          <h3 class="page-title">
            <span class="title-icon">
              <i class="fa fa-chart-line"></i>
            </span>
            Artículos con más movimientos
          </h3>

          <p class="page-subtitle">
            Consulta las ventas y compras de productos por período
          </p>
        </div>

        <b-button
          variant="outline-success"
          size="sm"
          :href="hrefXls"
          target="_blank"
          class="btn-exportar"
        >
          <i class="fa fa-file-excel-o mr-1"></i>
          Exportar Excel
        </b-button>
      </div>


      <!-- FILTROS -->
      <b-card class="filter-card mb-4" no-body>

        <div class="filter-header">
          <div>
            <i class="fa fa-filter mr-2"></i>
            Filtros de consulta
          </div>
        </div>

        <div class="p-3">

          <b-row>

            <!-- ALMACEN -->
            <b-col md="4" lg="3" class="mb-3 mb-md-0">

              <label class="filter-label">
                Almacén
              </label>

              <autocomplete
                required="required"
                ref="inputAlmacen"
                :url="API + 'almacenes'"
                anchor="Almacen"
                label="writer"
                placeholder="Seleccione almacén"
                :on-select="setAlmacen"
                :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                :classes="{input: 'form-control'}"
              >
              </autocomplete>

            </b-col>


            <!-- FECHA INICIO -->
            <b-col md="3" lg="2" class="mb-3 mb-md-0">

              <label class="filter-label">
                Fecha inicio
              </label>

              <b-form-input
                v-model="fechaDesde"
                type="date"
              >
              </b-form-input>

            </b-col>


            <!-- FECHA FIN -->
            <b-col md="3" lg="2" class="mb-3 mb-md-0">

              <label class="filter-label">
                Fecha fin
              </label>

              <b-form-input
                v-model="fechaHasta"
                type="date"
              >
              </b-form-input>

            </b-col>


            <!-- BOTON -->
            <b-col md="2" lg="2" class="d-flex align-items-end">

              <b-button
                variant="primary"
                block
                @click="verArticulos"
                :disabled="cargando"
              >

                <i
                  v-if="!cargando"
                  class="fa fa-search mr-1"
                ></i>

                <i
                  v-else
                  class="fa fa-spinner fa-spin mr-1"
                ></i>

                {{ cargando ? 'Consultando...' : 'Consultar' }}

              </b-button>

            </b-col>

          </b-row>

        </div>

      </b-card>


      <!-- TARJETAS RESUMEN -->
      <b-row v-if="productos.length" class="mb-4">

        <!-- VENTAS -->
        <b-col md="6" lg="3" class="mb-3">

          <div class="summary-card sales-card">

            <div class="summary-icon">
              <i class="fas fa-money-bill-wave"></i>
            </div>

            <div class="summary-content">

              <span class="summary-label">
                Ventas
              </span>

              <strong>
                S/ {{ formatoNumero(resumen.montoVentas) }}
              </strong>

              <small>
                {{ formatoNumero(resumen.cantidadVentas) }} unidades vendidas
              </small>

            </div>

          </div>

        </b-col>


        <!-- TRANSACCIONES -->
        <b-col md="6" lg="3" class="mb-3">

          <div class="summary-card transactions-card">

            <div class="summary-icon">
              <i class="fa fa-shopping-cart"></i>
            </div>

            <div class="summary-content">

              <span class="summary-label">
                Operaciones de venta
              </span>

              <strong>
                {{ formatoNumero(resumen.totalVentas) }}
              </strong>

              <small>
                operaciones registradas
              </small>

            </div>

          </div>

        </b-col>


        <!-- COMPRAS -->
        <b-col md="6" lg="3" class="mb-3">

          <div class="summary-card purchases-card">

            <div class="summary-icon">
              <i class="fa fa-shopping-basket"></i>
            </div>

            <div class="summary-content">

              <span class="summary-label">
                Compras
              </span>

              <strong>
                S/ {{ formatoNumero(resumen.montoCompras) }}
              </strong>

              <small>
                {{ formatoNumero(resumen.cantidadCompras) }} unidades compradas
              </small>

            </div>

          </div>

        </b-col>


        <!-- DIFERENCIA -->
        <b-col md="6" lg="3" class="mb-3">

          <div class="summary-card balance-card">

            <div class="summary-icon">
              <i class="fas fa-balance-scale"></i>
            </div>

            <div class="summary-content">

              <span class="summary-label">
                Diferencia ventas - compras
              </span>

              <strong>
                S/ {{ formatoNumero(resumen.montoVentas - resumen.montoCompras) }}
              </strong>

              <small>
                período seleccionado
              </small>

            </div>

          </div>

        </b-col>

      </b-row>


      <!-- TOP PRODUCTOS -->
      <b-card
        v-if="topProductos.length"
        class="top-card mb-4"
        no-body
      >

        <div class="section-header">

          <div>
            <h5>
              <i class="fa fa-trophy mr-2"></i>
              Productos más vendidos
            </h5>

            <span>
              Top 5 según cantidad de unidades vendidas
            </span>
          </div>

        </div>


        <div class="top-products">

          <div
            v-for="(prod, index) in topProductos"
            :key="prod.IdProducto || index"
            class="top-product"
          >

            <!-- POSICION -->
            <div
              class="ranking"
              :class="'ranking-' + (index + 1)"
            >
              {{ index + 1 }}
            </div>


            <!-- PRODUCTO -->
            <div class="top-product-info">

              <strong>
                {{ prod.Producto }}
              </strong>

              <span>
                {{ prod.ProductoCategoria }}
                <span v-if="prod.ProductoMarca">
                  · {{ prod.ProductoMarca }}
                </span>
              </span>

            </div>


            <!-- CANTIDAD -->
            <div class="top-product-value">

              <strong>
                {{ formatoNumero(prod.CantidadVentas) }}
              </strong>

              <span>
                unidades
              </span>

            </div>


            <!-- MONTO -->
            <div class="top-product-money">

              S/ {{ formatoNumero(prod.MontoVentas) }}

            </div>

          </div>

        </div>

      </b-card>


      <!-- TABLA -->
      <b-card class="table-card" no-body>

        <div class="table-header">

          <div>
            <h5>
              <i class="fa fa-list mr-2"></i>
              Detalle de movimientos
            </h5>

            <span>
              {{ productos.length }} productos encontrados
            </span>
          </div>

        </div>


        <div class="table-responsive">

          <table class="table table-hover table-sm movement-table mb-0">

            <thead>

              <tr>

                <th>Código</th>

                <th>Producto</th>

                <th>Categoría</th>

                <th>U. Medida</th>

                <th class="text-right">
                  Ventas
                </th>

                <th class="text-right">
                  Cantidad vendida
                </th>

                <th class="text-right">
                  Monto ventas
                </th>

                <th class="text-right">
                  Compras
                </th>

                <th class="text-right">
                  Cantidad comprada
                </th>

                <th class="text-right">
                  Monto compras
                </th>

              </tr>

            </thead>


            <tbody>

              <tr
                v-for="(prod, i) in productos"
                :key="prod.IdProducto || i"
              >

                <td>

                  <span class="codigo">
                    {{ prod.CodigoBarra }}
                  </span>

                </td>


                <td>

                  <div class="producto-cell">

                    <strong>
                      {{ prod.Producto }}
                    </strong>

                    <small v-if="prod.ProductoMarca">
                      {{ prod.ProductoMarca }}
                    </small>

                  </div>

                </td>


                <td>

                  <span class="category-badge">
                    {{ prod.ProductoCategoria }}
                  </span>

                </td>


                <td>
                  {{ prod.ProductoMedicion }}
                </td>


                <td class="text-right">

                  {{ formatoNumero(prod.TotalVentas) }}

                </td>


                <td class="text-right">

                  <strong class="ventas-cantidad">
                    {{ formatoNumero(prod.CantidadVentas) }}
                  </strong>

                </td>


                <td class="text-right">

                  <strong class="ventas-monto">
                    S/ {{ formatoNumero(prod.MontoVentas) }}
                  </strong>

                </td>


                <td class="text-right">

                  {{ formatoNumero(prod.TotalCompras) }}

                </td>


                <td class="text-right">

                  {{ formatoNumero(prod.CantidadCompras) }}

                </td>


                <td class="text-right">

                  <strong class="compras-monto">
                    S/ {{ formatoNumero(prod.MontoCompras) }}
                  </strong>

                </td>

              </tr>


              <!-- SIN DATOS -->
              <tr v-if="!productos.length && !cargando">

                <td
                  colspan="10"
                  class="empty-state"
                >

                  <i class="fa fa-bar-chart"></i>

                  <strong>
                    No hay información para mostrar
                  </strong>

                  <span>
                    Selecciona el almacén y período de consulta.
                  </span>

                </td>

              </tr>


              <!-- CARGANDO -->
              <tr v-if="cargando">

                <td
                  colspan="10"
                  class="empty-state"
                >

                  <i class="fa fa-spinner fa-spin"></i>

                  <strong>
                    Consultando información...
                  </strong>

                </td>

              </tr>

            </tbody>

          </table>

        </div>

      </b-card>

    </b-container>

  </div>
</template>


<script>

import config from '@/store/config.js'

export default {

  mounted () {

    this.loadFirstAlmacen()

  },


  data () {

    return {

      API: config.API,

      almacen: {},

      productos: [],

      cargando: false,

      fechaDesde: this.$moment().startOf('month').format("YYYY-MM-DD"),

      fechaHasta: this.$moment().format("YYYY-MM-DD")

    }

  },


  computed: {

    hrefXls () {

      return '/api/index.php/reporte/articulosmovimientos?idAlmacen=' +
        this.almacen.IdAlmacen +
        '&fechaDesde=' +
        this.fechaDesde +
        '&fechaHasta=' +
        this.fechaHasta

    },


    /*
     * ============================================================
     * RESUMEN GENERAL
     * ============================================================
     */

    resumen () {

      let totalVentas = 0
      let cantidadVentas = 0
      let montoVentas = 0

      let totalCompras = 0
      let cantidadCompras = 0
      let montoCompras = 0


      this.productos.forEach(prod => {

        totalVentas += Number(prod.TotalVentas || 0)

        cantidadVentas += Number(prod.CantidadVentas || 0)

        montoVentas += Number(prod.MontoVentas || 0)

        totalCompras += Number(prod.TotalCompras || 0)

        cantidadCompras += Number(prod.CantidadCompras || 0)

        montoCompras += Number(prod.MontoCompras || 0)

      })


      return {

        totalVentas,

        cantidadVentas,

        montoVentas,

        totalCompras,

        cantidadCompras,

        montoCompras

      }

    },


    /*
     * ============================================================
     * TOP 5 MÁS VENDIDOS
     * ============================================================
     */

    topProductos () {

      return this.productos

        .filter(prod => Number(prod.CantidadVentas || 0) > 0)

        .slice()

        .sort((a, b) => {

          return Number(b.CantidadVentas || 0) -
            Number(a.CantidadVentas || 0)

        })

        .slice(0, 5)

    }

  },


  methods: {


    /*
     * ============================================================
     * CARGAR ALMACÉN
     * ============================================================
     */

    loadFirstAlmacen () {

      this.axios
        .get(this.API + 'almacenes/primero')
        .then(res => {

          this.$refs.inputAlmacen.setValue(res.data.Almacen)

          this.almacen = res.data

        })

    },


    /*
     * ============================================================
     * CAMBIAR ALMACÉN
     * ============================================================
     */

    setAlmacen (obj) {

      this.almacen = obj

    },


    /*
     * ============================================================
     * FOCUS AUTOCOMPLETE
     * ============================================================
     */

    focusAutocomplete (ref) {

      this.$refs[ref].type = ""

      this.$refs[ref].handleDoubleClick()

    },


    /*
     * ============================================================
     * CONSULTAR
     * ============================================================
     */

    verArticulos () {

      if (!this.almacen || !this.almacen.IdAlmacen) {

        this.$toasted.show(
          'Seleccione un almacén',
          {
            type: 'error'
          }
        )

        return

      }


      if (this.fechaDesde > this.fechaHasta) {

        this.$toasted.show(
          'La fecha de inicio no puede ser mayor que la fecha final',
          {
            type: 'error'
          }
        )

        return

      }


      this.cargando = true


      this.axios.get(
        this.API + 'productos/masrotacion?limit=0',
        {

          params: {

            idAlmacen: this.almacen.IdAlmacen,

            fechaDesde: this.fechaDesde,

            fechaHasta: this.fechaHasta

          }

        }

      )

      .then(response => {

        this.productos = response.data || []

      })

      .catch(error => {

        console.error(
          'Error consultando movimientos:',
          error
        )

        this.productos = []

        this.$toasted.show(
          'Ocurrió un error al consultar los movimientos',
          {
            type: 'error'
          }
        )

      })

      .finally(() => {

        this.cargando = false

      })

    },


    /*
     * ============================================================
     * FORMATO DE NÚMEROS
     * ============================================================
     */

    formatoNumero (valor) {

      return Number(valor || 0)
        .toLocaleString(
          'es-PE',
          {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          }
        )

    }

  }

}

</script>


<style scoped>

/* ============================================================
   GENERAL
   ============================================================ */

.reporte-movimientos {
  background: #f5f7fa;
  min-height: 100vh;
}


/* ============================================================
   HEADER
   ============================================================ */

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

.page-title {
  margin: 0;
  font-size: 24px;
  font-weight: 700;
  color: #17324d;
}

.page-subtitle {
  margin: 5px 0 0;
  color: #7a8a9a;
  font-size: 13px;
}

.title-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 42px;
  height: 42px;
  margin-right: 10px;
  border-radius: 10px;
  background: #e8f1ff;
  color: #1976d2;
}

.btn-exportar {
  white-space: nowrap;
}


/* ============================================================
   FILTROS
   ============================================================ */

.filter-card {
  border: 1px solid #e4e9ef;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(30, 55, 80, 0.05);
}

.filter-header {
  padding: 12px 16px;
  border-bottom: 1px solid #e8edf2;
  color: #17324d;
  font-size: 14px;
  font-weight: 600;
}

.filter-label {
  display: block;
  margin-bottom: 6px;
  color: #526477;
  font-size: 12px;
  font-weight: 600;
}


/* ============================================================
   TARJETAS RESUMEN
   ============================================================ */

.summary-card {
  display: flex;
  align-items: center;
  min-height: 105px;
  padding: 18px;
  background: #fff;
  border: 1px solid #e5eaf0;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(30, 55, 80, 0.05);
}

.summary-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 48px;
  height: 48px;
  margin-right: 14px;
  border-radius: 12px;
  font-size: 20px;
}

.summary-content {
  min-width: 0;
}

.summary-label {
  display: block;
  margin-bottom: 2px;
  color: #718096;
  font-size: 12px;
}

.summary-content strong {
  display: block;
  color: #17324d;
  font-size: 20px;
  line-height: 1.2;
}

.summary-content small {
  display: block;
  margin-top: 3px;
  color: #8b98a7;
  font-size: 11px;
}

.sales-card .summary-icon {
  background: #e6f7ee;
  color: #159447;
}

.transactions-card .summary-icon {
  background: #e8f1ff;
  color: #1976d2;
}

.purchases-card .summary-icon {
  background: #fff4df;
  color: #d98a00;
}

.balance-card .summary-icon {
  background: #eee9ff;
  color: #7251c7;
}


/* ============================================================
   TOP PRODUCTOS
   ============================================================ */

.top-card {
  border: 1px solid #e4e9ef;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(30, 55, 80, 0.05);
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 18px;
  border-bottom: 1px solid #e8edf2;
}

.section-header h5 {
  margin: 0;
  color: #17324d;
  font-size: 15px;
  font-weight: 700;
}

.section-header h5 i {
  color: #e5a100;
}

.section-header span {
  display: block;
  margin-top: 3px;
  color: #8996a4;
  font-size: 11px;
}

.top-products {
  padding: 5px 18px;
}

.top-product {
  display: flex;
  align-items: center;
  padding: 13px 0;
  border-bottom: 1px solid #edf0f3;
}

.top-product:last-child {
  border-bottom: 0;
}

.ranking {
  display: flex;
  align-items: center;
  justify-content: center;
  min-width: 34px;
  height: 34px;
  margin-right: 12px;
  border-radius: 50%;
  background: #eef2f6;
  color: #596a7b;
  font-size: 13px;
  font-weight: 700;
}

.ranking-1 {
  background: #fff0bf;
  color: #9a7100;
}

.ranking-2 {
  background: #edf0f3;
  color: #65727e;
}

.ranking-3 {
  background: #f6e7d8;
  color: #95643d;
}

.top-product-info {
  flex: 1;
  min-width: 0;
}

.top-product-info strong {
  display: block;
  overflow: hidden;
  color: #26394d;
  font-size: 13px;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.top-product-info span {
  color: #8996a4;
  font-size: 11px;
}

.top-product-value {
  min-width: 110px;
  text-align: right;
}

.top-product-value strong {
  display: block;
  color: #16834a;
  font-size: 15px;
}

.top-product-value span {
  color: #8996a4;
  font-size: 10px;
}

.top-product-money {
  min-width: 130px;
  margin-left: 20px;
  color: #26394d;
  font-size: 13px;
  font-weight: 600;
  text-align: right;
}


/* ============================================================
   TABLA
   ============================================================ */

.table-card {
  overflow: hidden;
  border: 1px solid #e4e9ef;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(30, 55, 80, 0.05);
}

.table-header {
  padding: 16px 18px;
  background: #fff;
  border-bottom: 1px solid #e8edf2;
}

.table-header h5 {
  margin: 0;
  color: #17324d;
  font-size: 15px;
  font-weight: 700;
}

.table-header h5 i {
  color: #1976d2;
}

.table-header span {
  display: block;
  margin-top: 4px;
  color: #8996a4;
  font-size: 11px;
}

.movement-table {
  background: #fff;
}

.movement-table thead th {
  padding: 11px 10px;
  background: #f7f9fb;
  border-bottom: 1px solid #dfe5eb;
  color: #526477;
  font-size: 11px;
  font-weight: 700;
  white-space: nowrap;
}

.movement-table tbody td {
  padding: 10px;
  vertical-align: middle;
  border-color: #edf0f3;
  color: #526477;
  font-size: 12px;
}

.movement-table tbody tr:hover {
  background: #f8fbff;
}

.codigo {
  color: #5c6f82;
  font-family: monospace;
  font-size: 11px;
}

.producto-cell strong {
  display: block;
  color: #26394d;
  font-size: 12px;
}

.producto-cell small {
  display: block;
  margin-top: 2px;
  color: #9aa5b1;
  font-size: 10px;
}

.category-badge {
  display: inline-block;
  padding: 4px 8px;
  border-radius: 5px;
  background: #eef4fb;
  color: #47719a;
  font-size: 10px;
  font-weight: 600;
}

.ventas-cantidad {
  color: #16834a;
}

.ventas-monto {
  color: #1976d2;
}

.compras-monto {
  color: #b27600;
}


/* ============================================================
   SIN DATOS
   ============================================================ */

.empty-state {
  padding: 50px 20px !important;
  text-align: center;
}

.empty-state i {
  display: block;
  margin-bottom: 12px;
  color: #aab5c0;
  font-size: 32px;
}

.empty-state strong {
  display: block;
  color: #627386;
  font-size: 14px;
}

.empty-state span {
  display: block;
  margin-top: 4px;
  color: #9aa6b2;
  font-size: 11px;
}


/* ============================================================
   RESPONSIVE
   ============================================================ */

@media (max-width: 768px) {

  .page-header {
    display: block;
  }

  .btn-exportar {
    margin-top: 15px;
  }

  .top-product-money {
    display: none;
  }

  .top-product-value {
    min-width: 90px;
  }

}

</style>