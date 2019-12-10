<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="9" class="my-1">
            <b-row>
              <b-col md="6">
                <b-form-group>
                  <label>Vendedor:</label>
                  <autocomplete
                      required="required"
                      ref="inputVendedor"
                      :url="API + 'usuarios'"
                      anchor="NombreUsuario"
                      label="writer"
                      :on-select="setVendedor" 
                      :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-form-group>
							</b-col>

              <b-col md="3">
                <b-form-group>
                  <label>Fecha Inicio:</label>
                  <b-form-input v-model="fechaInicio"
                    type="date"
                    placeholder=""
                    @input="val => generateFilter('fechaInicio', val) "></b-form-input>
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-row>
                  <b-col md="12">
                    <b-form-group>
                      <label>Fecha Fin:</label>
                      <b-form-input v-model="fechaFin"
                        type="date"
                        placeholder=""
                        @input="val => generateFilter('fechaFin', val) "></b-form-input>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-col>
            </b-row>
          </b-col>
          <b-col md="3" class="my-1">
            <b-row>
              <b-col md="5">
                <b-form-group label="Limpiar Filtros">
                  <b-button :disabled="!filter" @click="limpiarFiltros">Limpiar</b-button>
                </b-form-group>
              </b-col>
							<b-col md="7">
                <b-form-group label="Mostrar">
                  <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
                </b-form-group>
                <br />
              </b-col>
            </b-row>
          </b-col>
        </b-row>
        <b-row>
          
          <b-col md="12">
            <b-table small
              ref="tablaVentas"
              :filter="filter"
              :sort-by.sync="sortBy"
              :sort-desc.sync="sortDesc"
              striped
              hover
              @row-clicked="rowClick"
              id="tabla-ventas" 
              :busy.sync="isBusy" 
              :items="myProvider" 
              :fields="fields"
              :per-page="perPage"
              :current-page="currentPage">
              </b-table>
          </b-col>
          <b-row>
              <b-col md="6" class="my-1">
                  <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
              </b-col>
          </b-row>
        </b-row>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
             <!-- <b-button variant="success" size="lg" :href="'/api/index.php/reporte/stock?idAlmacen=' + almacen.IdAlmacen" target="_blank">Exportar</b-button> -->
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
    this.axios.get(this.API + 'reportes/ventavendedor/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      vendedor: '',
      fechaInicio: this.$moment().format("YYYY-MM-DD"),
      fechaFin: this.$moment().format("YYYY-MM-DD"),
      filters: {},
      filter: '',
      sortBy: '',
      sortDesc: true,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      items: [],
      showVenta: false,
      isBusy: false,
      fields: [
            { key: 'UsuarioReg', label: 'Vendedor', sortable: true },
            { key: 'Cant. Balones', label: 'V. Balones.', sortable: true},
            { key: 'Efectivo', label: 'V. Efectivo', sortable: true },
            { key: 'Fise', label: 'V. Fise', sortable: true },
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
  computed: {
  },
  methods: {
		// diferenciaFecha (a, b) {
		// 	var ms = this.$moment(b, "YYYY-MM-DD HH:mm:ss").diff(this.$moment(a,"YYYY-MM-DD HH:mm:ss"));
		// 	var d = this.$moment.duration(ms);

		// 	return d.years() + '/' + d.months() + '/' + d.days() + ' ' + d.hours() + ':' + d.minutes() + ':' + d.seconds()
		// },
		loadFirstAlmacen () {
        let usuario = sessionStorage.getItem('User')
          if (user) {
            usuario = user
          }

				this.axios.get(this.API + 'almacenes/primero')
						.then(res => {
								this.$refs.inputAlmacen.setValue(res.data.Almacen)
								this.almacen = res.data
						})
		},
    setTipoVenta (obj) {
        this.tipoDoc = obj || {}
        this.getVentasAcumuladas()
        this.$refs.tablaVentas.refresh()
    },
    setVendedor (obj) {
      this.currentPage = 1
      this.vendedor = obj
      this.generateFilter('vendedor', obj.Usuario)
  
    },
    focusAutocomplete (ref) {
      this.$refs[ref].handleDoubleClick()
    },
    rowClick (item) {
    },
    actualizarTabla() {
      this.$refs.tablaVentas.refresh()
    },
    myProvider (ctx) {
      console.log(ctx)
      let url = this.API + 'reportes/ventavendedor?' + '&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
      url += '&sortDesc='+(ctx.sortDesc ? 1 : 0)
      console.log(url)
      let promise = this.axios.get( url )

      return promise.then((result) => {
        this.items = result.data

        return(this.items)
      }).catch(error => {
        return []
      })
    },
    limpiarFiltros () {
      this.filters = {}
      this.filter = ''
      this.vendedor = ''

    },
    generateFilter (key, value) {
      this.filters[key] = value

      let filter = ''
      for (var key in this.filters) {
        filter += '&filter[' + key + ']=' + this.filters[key]
      }
      this.filter = filter
    },
    getVendedor (user) {
        let usuario = sessionStorage.getItem('User')
        if (user) {
            usuario = user
        }

        this.axios.get(this.API + 'usuarios/' + usuario)
        .then((response) => {
            this.setVendedor(response.data)
            this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
        })
        .catch((error) => {
            console.log(error)
            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
    }

  },
  components: {
  }
}
</script>

<style scope>
.total-ventas {
  font-weight: bold;
  text-align: center;
  color: #ffffff;
  background-color: #ff7675;
  padding: 10px;
  font-size: 40px;
}
#tabla-productos tr {
  cursor: pointer;
}
.table-detalle {
  height: 80px;
}
.table-detalle th {
  color: #fff;
  background-color: #6c757d; 
}
.table-detalle tbody td {
  font-size: 18px;
  font-weight: bold;
  color: #2980b9;
}
.table-detalle thead th:first-child {
  max-width: 300px;
}
@media (min-width: 992px) {
    .modal-xl .modal-dialog {
        max-width: 1200px;
    }
}
</style>
