<template>
  <div>
      <b-container fluid class="bv-example-row" id="conten">
        <div class="contenido" v-if="libroSelec!=null">
            <b-row>
                <b-col md="12" class="text-center">
                    <h4><b style="text-decoration: underline;">REGISTRO CONTABLE</b></h4>
                    <h5>REGISTRO DE COMPRAS</h5>
                </b-col>
            </b-row>
            <br>
            <!--<b-row>
                <b-col md="6" class="my-1">
                    <b-form-group horizontal label="Buscar" class="mb-0">
                    <b-input-group>
                        <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                        <b-input-group-append>
                        <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                        </b-input-group-append>
                    </b-input-group>
                    </b-form-group>
                    <br />
                </b-col>
                <b-col md="6" class="my-1">
                    <b-row>
                    <b-col md="4">
                    <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Artículos</p>
                    </b-col>
                    </b-row>
                </b-col>
            </b-row>-->
            <b-row>
                <table class="table table-striped" style="overflow-x: auto; width:100%">
                    <thead>
                        <tr>
                            <th style="width: 12%">F. Emi.</th>
                            <th>Tipo</th>
                            <th>Serie</th>
                            <th>Numero</th>
                            <th>Razón Social</th>
                            <th>Base Imp</th>
                            <th>IGV</th>
                            <th>Base Imp</th>
                            <th>IGV</th>
                            <th>No Grav.</th>
                            <th>Otros</th>
                            <th>Total</th>
                            <th>Ord</th>
                            <th>Mon</th>
                            <th>Cc</th>
                            <th>EB</th>
                            <th>TipoCam</th>
                            <th>Reg</th>
                            <th>Total</th>
                            <th>IGV</th>
                            <th>Base Imp</th>
                            <th>No Grav</th>
                            <th>F. Ven.</th>
                            <th>Periodo</th>
                            <th>Caja</th>
                            <th>F. Cancel</th>
                            <th>Cta. Caja</th>
                            <th>Caja Cancel</th>
                            <th>Asto</th>
                            <th>Letra</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <!--<td>06-03-2019</td>
                            <td>01</td>
                            <td>F001</td>
                            <td>58</td>
                            <td>NAVARRO NARDINI S.A.C. </td>
                            <td>100.00</td>
                            <td>18.00</td>-->
                        </tr>
                    </tbody>
                </table>
            </b-row>
        </div>

        <b-modal id="modalRegistros" size="md" ref="modalRegistros" hide-footer hide-header :no-close-on-esc="true" no-close-on-backdrop>
            <div class="text-center"><h4><b style="text-decoration: underline;">LIBROS O REGISTROS CONTABLES</b></h4></div>
            <hr>
            <div class="d-block">
                <table class="table table-striped">
                    <tbody>
                        <tr class="filaLibro" v-for="(libro, index) in libros" :key="index" @click="abrirLibro(libro)">
                            <td class="linea">{{libro.Libro}}</td>
                            <td class="linea">{{libro.IdLibroContable}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </b-modal>
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'

export default {
  mounted() {
    /*this.axios.get(this.API + 'contabilidad/cuentas/count').then((response) => {
      this.totalRows = response.data.total
    })*/
    this.cargarLibros()
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 30, 50, 100 ],
      filter: null,
      sortBy: 'IdCuentaContable',
      sortDesc: false,
      cuentaSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'IdCuentaContable', label: 'Id', sortable: true },
            { key: 'Cuenta', sortable: true },
            { key: 'Nombre', label: 'Nombre', sortable: true},
            { key: 'Cargo1', label: 'Cargo1', sortable: true },
            { key: 'Cargo2', label: 'Cargo2', sortable: true },
            { key: 'Abono', label: 'Abono', sortable: true },
      ],
      totalRows: 50,
      perPage: 30,
      currentPage: 1,
      libros: [],
      libroSelec: null
    }
  },
  methods: {
      abrirLibro(data){
        console.log(data);
        
        this.$swal({
            title: 'Cargando registros... ',
            allowEscapeKey: false,
            allowOutsideClick: false,
            timerProgressBar: true,
            didOpen: () => {
                this.$swal.showLoading()
            }
        })

        setTimeout(() => {
            this.$swal.close()
            this.libroSelec = data.IdLibroContable
            this.$refs['modalRegistros'].hide()
        }, 1000);
      },
      cargarLibros(){
        this.axios.get(this.API + 'contabilidad/libros')
        .then((r) => {
            console.log(r);
            this.libros = r.data
            this.$refs['modalRegistros'].show()
        })
        .catch((error)=>{

        })
      },
      rowClick (item, index) {
        this.$refs['modalCuenta'].show()
        this.cuentaSelect = item
        console.log(item);
      },
      actualizarTabla() {
        this.$refs.tablaCuenta.refresh()
      },
      nuevaCuenta () {
          this.cuentaSelect = {}
          this.$refs.cuentaForm.show = true

      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'contabilidad/cuentas?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
.linea{
    font-weight: bold;
}
.filaLibro{
    cursor: pointer;
}
.filaLibro:hover{
    color: white;
    background-color: #0275D8 !important;
}
</style>
