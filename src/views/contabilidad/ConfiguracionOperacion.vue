<template>
  <div>
      <b-container class="bv-example-row" id="conten">
        <div class="contenido" v-if="libroIdSelec!=null">
          <b-row>
            <b-col md="2" class="text-center">
            </b-col>
            <b-col md="8" class="text-center">
              <h3><b style="color:red;">CONFIGURACIÓN DE OPERACIONES</b></h3>
              <h4><b style="text-decoration: underline;">{{ this.libroNombreSelec }}</b></h4>
            </b-col>
            <b-col md="2" class="text-center">
              <b-button variant="info" @click="cambiarLibro" size="sm"><i class="fas fa-sync fa-sm"></i> Cambiar Libro</b-button>
            </b-col>
          </b-row>
          <hr>
          <b-row>
            <hr>
            <b-col md="6">
              <b-input-group prepend="Tipo de Operación">
                  <autocomplete
                      required="required"
                      ref="inputTipoOperacion"
                      :url="API + 'contabilidad/operaciones'"
                      :customParams="{'IdLibro': libroIdSelec }"
                      :onShouldRenderChild="renderChildOpe"
                      anchor="TipoOperacion"
                      initValue=""
                      label="writer"
                      placeholder=""
                      :on-select="setOperacion"
                      :onFocus="focusAutocomplete.bind(null, 'inputTipoOperacion')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                  <b-button variant="success" size="sm">Nuevo</b-button>
              </b-input-group>
            </b-col>
            <b-col md="4">
              <b-form-group>
                <b-input-group>
                  <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                  <b-input-group-append>
                    <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
              <br />
            </b-col>
            <b-col md="2">
              <b-form-group horizontal label="Ver" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
          </b-row>
          
          <b-row>

            <!--<b-col md="6" class="my-1">
              <b-row>
              <b-col md="8">

                <b-form-group horizontal label="Mostrar" class="mb-0">
                  <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
                </b-form-group>
              </b-col>
              <b-col md="4">
                <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Artículos</p>
              </b-col>
              </b-row>
            </b-col>-->

            <b-table small
              ref="tablaOperacion"
              :filter="filter"
              :sort-by.sync="sortBy"
              :sort-desc.sync="sortDesc"
              striped
              hover
              @row-clicked="rowClick"
              id="my-table" 
              :busy.sync="isBusy" 
              :items="myProvider" 
              :fields="fields"
              :per-page="perPage"
              :current-page="currentPage">

              <template slot="Total" slot-scope="data">
                  {{ data.item.Total }} - {{ data.item.TotalOpc }}
              </template>
              <template slot="Igv" slot-scope="data">
                  {{ data.item.Igv }} - {{ data.item.IgvOpc }}
              </template>
              <template slot="BImp" slot-scope="data">
                  {{ data.item.BImp }} - {{ data.item.BImpOpc }}
              </template>
              <template slot="Exo" slot-scope="data">
                  {{ data.item.Exo }} - {{ data.item.ExoOpc }}
              </template>
              <template slot="Isc" slot-scope="data">
                  {{ data.item.Isc }} - {{ data.item.IscOpc }}
              </template>
            </b-table>
              <b-row>
                  <b-col md="6" class="my-1">
                      <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                  </b-col>
              </b-row>
          </b-row>
          <br />
          <b-row>
            <b-col align-h="center" md="12" class="text-center">
              <b-button variant="success" size="lg" v-if="operacionSelect!=null"><i class="fas fa-plus fa-sm"></i> Nuevo</b-button>
            </b-col>  
          </b-row>
        </div>
        
        <b-modal id="modalRegistros" size="md" ref="modalRegistros" hide-footer hide-header :no-close-on-esc="true" no-close-on-backdrop>
            <div class="text-center">
              <h3><b style="color:red;">SELECCIONE PARA CONFIGURAR</b></h3>
              <h5><b style="text-decoration: underline;">LIBROS O REGISTROS CONTABLES</b></h5>
            </div>
            <hr>
            <div class="d-block">
                <table class="table table-striped">
                    <tbody>
                        <tr v-for="(libro, index) in libros" :key="index" @click="abrirLibro(libro)" class="filaLibro">
                            <td><label style="font-size:0.9rem; font-weight:bold">{{libro.Libro}}</label></td>
                            <td><label style="font-size:0.9rem; font-weight:bold">{{libro.IdLibroContable}}</label></td>
                            <td>
                                <b-badge variant="primary">DISPONIBLE</b-badge>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </b-modal>

        <b-modal id="modalCuenta" size="md" ref="modalCuenta" title="Editar Cuentas" hide-footer :no-close-on-esc="true" no-close-on-backdrop>
            <div class="text-center">
              <h5><b>{{ CuentasCalculo.TipoOperacionDet }}</b></h5>
            </div>
            <hr>
            <div class="d-block">
              <table class="tablaMontos">
                <tbody>
                    <tr>
                        <td style="width: 5%;">Total</td>
                        <td style="width: 30%;"><input class="form-control" type="text" v-model="CuentasCalculo.Total"  pattern="[0-9]+"  autocomplete="off"/></td>
                        <td style="width: 15%;"><input class="form-control" type="text" v-model="CuentasCalculo.TotalOpc" style="font-weight:bold; text-transform:uppercase" maxlength="1"  autocomplete="off"/></td>
                    </tr>
                    <tr>
                        <td style="width: 5%;">I.G.V.</td>
                        <td style="width: 30%;"><input class="form-control" type="text" v-model="CuentasCalculo.Igv" pattern="[0-9]+"  autocomplete="off"/></td>
                        <td style="width: 15%;"><input class="form-control" type="text" v-model="CuentasCalculo.IgvOpc" style="font-weight:bold; text-transform:uppercase" maxlength="1"  autocomplete="off"/></td>
                    </tr>
                    <tr>
                        <td style="width: 5%;">B. Imp</td>
                        <td style="width: 30%;"><input class="form-control" type="text" v-model="CuentasCalculo.BImp" pattern="[0-9]+"  autocomplete="off"/></td>
                        <td style="width: 15%;"><input class="form-control" type="text" v-model="CuentasCalculo.BImpOpc" style="font-weight:bold; text-transform:uppercase" maxlength="1"  autocomplete="off"/></td>
                    </tr>
                    <tr>
                        <td style="width: 5%;">No Grav.</td>
                        <td style="width: 30%;"><input class="form-control" type="text" v-model="CuentasCalculo.Exo" pattern="[0-9]+"  autocomplete="off"/></td>
                        <td style="width: 15%;"><input class="form-control" type="text" v-model="CuentasCalculo.ExoOpc" style="font-weight:bold; text-transform:uppercase" maxlength="1"  autocomplete="off"/></td>
                    </tr>
                    <tr style="border-top: 1px solid black">
                        <td style="width: 5%;">I.S.C.</td>
                        <td style="width: 30%;"><input class="form-control" type="text" v-model="CuentasCalculo.Isc" pattern="[0-9]+"  autocomplete="off"/></td>
                        <td style="width: 15%;"><input class="form-control" type="text" v-model="CuentasCalculo.IscOpc" style="font-weight:bold; text-transform:uppercase" maxlength="1"  autocomplete="off"/></td>
                    </tr>
                </tbody>
              </table>
              <br>
              <b-row>
                <b-col align-h="center" md="12" class="text-center">
                  <b-button variant="success" size="md">Guardar</b-button>
                </b-col>
              </b-row>
            </div>
        </b-modal>
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'

export default {
  mounted() {
    this.cargarLibros()
    /*this.axios.get(this.API + 'contabilidad/cuentas/count').then((response) => {
      this.totalRows = response.data.total
    })*/
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 30, 50, 100 ],
      filter: null,
      sortBy: 'IdTipoOperacionDet',
      sortDesc: false,
      cuentaSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'IdTipoOperacionDet', label: 'Id', sortable: true },
            { key: 'TipoOperacionDet', label:'Detalle de Operación' , sortable: true },
            { key: 'Total', label: 'Total', sortable: true},
            { key: 'Igv', label: 'Igv', sortable: true },
            { key: 'BImp', label: 'BImp', sortable: true },
            { key: 'Exo', label: 'Exo', sortable: true },
            { key: 'Isc', label: 'Isc', sortable: true },
      ],
      totalRows: 200,
      perPage: 30,
      currentPage: 1,
      operacionSelect: null,
      libros: [],
      libroIdSelec: null,
      libroNombreSelec: null,
      CuentasCalculo: {
        BImp:'',
        BImpOpc:'',
        CalculaIgv: 0,
        Exo:'',
        ExoOpc:'',
        GravadosConIgv:0,
        IdOperacionDetCuenta:null,
        TipoOperacionDet: '',
        IdTipoOperacionDet:null,
        Igv:'',
        IgvOpc:'',
        Isc:'',
        IscOpc:'',
        Total:'',
        TotalOpc:'',
      },
    }
  },
  methods: {
      cambiarLibro(){
        this.cargarLibros()
      },
      abrirLibro(data){
        this.$swal({
            title: 'Cargando registro... ',
            allowEscapeKey: false,
            allowOutsideClick: false,
            timerProgressBar: true,
            didOpen: () => {
                this.$swal.showLoading()
            }
        })

        setTimeout(() => {
            this.$swal.close()
            this.libroIdSelec = data.IdLibroContable
            this.libroNombreSelec = data.Libro
            this.$refs['modalRegistros'].hide()
            if(this.$refs.tablaOperacion){
              this.$refs.tablaOperacion.refresh()
            }
        }, 500);
      },
      cargarLibros(){
        this.libros = []
        this.libroIdSelec = null
        this.libroNombreSelec = null
        this.operacionSelect = null
        this.axios.get(this.API + 'contabilidad/libros')
        .then((r) => {
            this.libros = r.data
            this.$refs['modalRegistros'].show()
        })
        .catch((error)=>{

        })
      },
      rowClick (item, index) {
        this.CuentasCalculo = item
        this.$refs['modalCuenta'].show()
        console.log(item);
      },
      actualizarTabla() {
        this.$refs.tablaOperacion.refresh()
      },
      nuevaCuenta () {
        this.cuentaSelect = {}
        this.$refs.cuentaForm.show = true
      },
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      setOperacion(obj){
        this.operacionSelect = obj.IdTipoOperacion
        this.$refs.tablaOperacion.refresh()
      },
      renderChildOpe(data) {
        return `
            <style>
            #table-render {
                font-size: 13px;
                margin-bottom: 5px;
                margin-top: 5px;
            }
            #table-render td, #table-render th {
                border: 1px solid #eaeaea;
            }
            #table-render td, #table-render th {
                color: #000000 !important;
            }
            </style>
            <div>
            <table id="table-render" style="width: 300px; text-align: left;  padding:0; margin:0"">
                <tbody>
                <tr style="padding:0; margin:0">
                    <td><b style="font-size: 0.75rem">${data.TipoOperacion}</b></td>
                </tr>
                </tbody>
            </table>   
            </div>
            `
      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'contabilidad/operaciones/cuenta/detalle?IdTipoOpe='+this.operacionSelect+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&q='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
        url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
        let promise = this.axios.get( url )

        return promise.then((result) => {
          this.items = result.data
          //this.totalRows = this.items.length
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          return(this.items)
        }).catch(error => {
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          // Returning an empty array, allows table to correctly handle busy state in case of error
          return []
        })
      }
  },
  components: {
  }
}
</script>

<style>
.input-group-text{
    font-weight: bold;
    font-size: 0.95rem;
}
#modalRegistros .modal-dialog{
    max-width: 45% !important;
}
.filaLibro *{
    cursor: pointer;
}
.filaLibro:hover{
    color: white;
    background-color: #0275D8 !important;
}
.contenido {
    box-sizing: unset;
}
.tablaMontos * {
    font-size: 0.8rem;
}
.tablaMontos input {
    border-radius: 0% !important;
}
</style>
