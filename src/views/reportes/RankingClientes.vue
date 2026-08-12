<template>
  <div class="fondo">
    <h2 align-h="center" class="text-center">Reporte Ranking de Clientes</h2>
    <b-container class="bv-example-row">
      <b-row>
        <b-col md="3" class="my-1">
          <b-form-group>
            <label>Buscar (DNI/RUC/Cliente):</label>
            <b-input-group>
              <b-form-input v-model="filtro" placeholder="Buscar" @keyup.enter="generarReporte" />
              <b-input-group-append>
                <b-btn :disabled="!filtro" @click="limpiarFiltro">Limpiar</b-btn>
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
        </b-col>

        <b-col md="2" class="my-1">
          <b-form-group>
            <label>Desde:</label>
            <b-form-input type="date" v-model="fechaInicio" />
          </b-form-group>
        </b-col>

        <b-col md="2" class="my-1">
          <b-form-group>
            <label>Hasta:</label>
            <b-form-input type="date" v-model="fechaFin" />
          </b-form-group>
        </b-col>

        <b-col md="3">
          <b-form-group>
            <label>Almacen:</label>
            <autocomplete
                v-if="almacenInicialCargado"
                required="required"
                ref="inputAlmacen"
                :url="API + 'almacenes'"
                anchor="Almacen"
                :initValue="almacen.writer || ''"
                label="writer"
                placeholder=""
                :on-select="setAlmacen"
                :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                :classes="{input: 'form-control'}">
            </autocomplete>
          </b-form-group>
        </b-col>

        <b-col md="2" class="my-1 d-flex align-items-end">
          <b-form-group class="w-100">
            <label>Mostrar:</label>
            <b-form-select :options="pageOptions" v-model="perPage" @input="onPerPageChange" />
          </b-form-group>
        </b-col>
      </b-row>

      <b-row align-h="center" class="text-center my-2">
        <b-col cols="3">
          <b-button variant="primary" size="lg" block @click="generarReporte" :disabled="isBusy || !almacenInicialCargado">
            <b-spinner small v-if="isBusy"></b-spinner>
            {{ isBusy ? 'Generando...' : 'Generar' }}
          </b-button>
        </b-col>
      </b-row>

      <b-row>
        <b-table small
          ref="tablaProductos"
          :sort-by.sync="sortBy"
          :sort-desc.sync="sortDesc"
          striped
          hover
          id="my-table"
          :busy.sync="isBusy"
          :items="myProvider"
          :fields="fields"
          :per-page="perPage"
          :current-page="currentPage">
          <template #table-busy>
            <div class="text-center text-primary my-2">
              <b-spinner class="align-middle"></b-spinner>
              <strong> Cargando...</strong>
            </div>
          </template>
          <template #empty>
            <div class="text-center text-muted my-3">
              {{ buscado ? 'No se encontraron resultados.' : 'Presiona "Generar" para ver el reporte.' }}
            </div>
          </template>
        </b-table>
        <b-row>
          <b-col md="6" class="my-1">
            <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" @input="refrescar" />
          </b-col>
        </b-row>
      </b-row>

      <b-row align-h="center" class="text-center">
        <b-col cols="4">
          <b-button variant="success" size="lg" :disabled="!buscado" :href="urlExportar" target="_blank">Exportar</b-button>
        </b-col>
      </b-row>
      <br /><br />
    </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'

export default {
  mounted() {
    this.inicializarFechasPorDefecto()
    this.cargarAlmacenPorDefecto()
  },
  data() {
    return {
      API: config.API,
      pageOptions: [5, 10, 15, 50, 100],
      filtro: '',
      fechaInicio: '',
      fechaFin: '',
      // valores realmente aplicados a la consulta (solo cambian al presionar "Generar")
      filtroAplicado: '',
      fechaInicioAplicada: '',
      fechaFinAplicada: '',
      // controla si ya se generó al menos un reporte, evita que la tabla
      // cargue datos automáticamente al entrar al módulo
      buscado: false,
      // evita mostrar/usar el autocomplete y el botón Generar hasta tener
      // el almacén por defecto cargado
      almacenInicialCargado: false,
      sortBy: 'Total',
      sortDesc: false,
      items: [],
      isBusy: false,
      fields: [
        { key: 'DniRuc', label: 'DNI / RUC', sortable: true },
        { key: 'Cliente', label: 'Cliente', sortable: true },
        { key: 'Direccion', label: 'Direccion', sortable: true },
        { key: 'CantidadCompras', label: 'Compras', sortable: true },
        { key: 'PromedioCompra', label: 'Promedio', sortable: true },
        { key: 'UltimaCompra', label: 'Última Compra', sortable: true },
        { key: 'Puntos', label: 'Puntos', sortable: true },
        { key: 'Total', label: 'Total', sortable: true },
      ],
      totalRows: 0,
      perPage: 10,
      currentPage: 1,
      almacen: {
        IdAlmacen: '',
        writer: ''
      }
    }
  },
  computed: {
    urlExportar() {
      let url = this.API + 'reporte/rankingclientes?idalmacen=' + this.almacen.IdAlmacen
      url += '&q=' + encodeURIComponent(this.filtroAplicado || '')
      if (this.fechaInicioAplicada) url += '&fechaInicio=' + this.fechaInicioAplicada
      if (this.fechaFinAplicada) url += '&fechaFin=' + this.fechaFinAplicada
      return url
    }
  },
  methods: {
    inicializarFechasPorDefecto() {
      const hoy = new Date()
      const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1)

      this.fechaFin = this.formatearFecha(hoy)
      this.fechaInicio = this.formatearFecha(primerDiaMes)
    },
    formatearFecha(fecha) {
      const year = fecha.getFullYear()
      const month = String(fecha.getMonth() + 1).padStart(2, '0')
      const day = String(fecha.getDate()).padStart(2, '0')
      return `${year}-${month}-${day}`
    },
    cargarAlmacenPorDefecto() {
      this.axios.get(this.API + 'almacenes').then((response) => {
        const almacenes = response.data
        if (almacenes && almacenes.length > 0) {
          this.almacen = almacenes[0]
        }
        this.almacenInicialCargado = true
      }).catch(() => {
        // si falla la carga, igual habilitamos el módulo para que el
        // usuario pueda seleccionar manualmente desde el autocomplete
        this.almacenInicialCargado = true
      })
    },
    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick()
    },
    setAlmacen(obj) {
      this.almacen = obj
      // no auto-refresca: espera a que el usuario presione "Generar"
    },
    limpiarFiltro() {
      this.filtro = ''
    },
    generarReporte() {
      // aquí "congelamos" los valores de los inputs hacia la consulta real
      this.filtroAplicado = this.filtro
      this.fechaInicioAplicada = this.fechaInicio
      this.fechaFinAplicada = this.fechaFin
      this.buscado = true
      this.currentPage = 1
      this.actualizarConteo()
      this.refrescar()
    },
    onPerPageChange() {
      this.currentPage = 1
      this.refrescar()
    },
    refrescar() {
      if (this.$refs.tablaProductos) {
        this.$refs.tablaProductos.refresh()
      }
    },
    actualizarConteo() {
      let url = this.API + 'ranking/clientes/count?idalmacen=' + this.almacen.IdAlmacen
      url += '&q=' + encodeURIComponent(this.filtroAplicado || '')
      if (this.fechaInicioAplicada) url += '&fechaInicio=' + this.fechaInicioAplicada
      if (this.fechaFinAplicada) url += '&fechaFin=' + this.fechaFinAplicada

      this.axios.get(url).then((response) => {
        this.totalRows = response.data.total
      })
    },
    myProvider(ctx) {
      // Bloquea la carga automática al montar el componente o al mover
      // per-page/paginación antes de que el usuario presione "Generar"
      if (!this.buscado) {
        return []
      }

      let url = this.API + 'ranking/clientes?idalmacen=' + this.almacen.IdAlmacen
      url += '&q=' + encodeURIComponent(this.filtroAplicado || '')
      url += '&limit=' + ctx.perPage
      url += '&page=' + ctx.currentPage

      if (this.fechaInicioAplicada) url += '&fechaInicio=' + this.fechaInicioAplicada
      if (this.fechaFinAplicada) url += '&fechaFin=' + this.fechaFinAplicada

      let promise = this.axios.get(url)

      return promise.then((response) => {
        this.items = response.data
        return this.items
      }).catch(error => {
        return []
      })
    }
  },
  components: {}
}
</script>

<style>
.fondo {
  background: #EFF9FF;
}
</style>