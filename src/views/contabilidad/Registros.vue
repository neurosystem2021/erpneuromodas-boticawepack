<template>
  <div>
      <b-container fluid class="bv-example-row" id="conten">
        <div class="contenido" v-if="libroIdSelec!=null">
           <compras v-if="libroIdSelec==8" :libroIdSelec="libroIdSelec" @cambioLibro="cargarLibros" ></compras>
        </div>

        <b-modal id="modalRegistros" size="md" ref="modalRegistros" hide-footer hide-header :no-close-on-esc="true" no-close-on-backdrop>
            <div class="text-center"><h4><b style="text-decoration: underline;">LIBROS O REGISTROS CONTABLES</b></h4></div>
            <hr>
            <div class="d-block">
                <table class="table table-striped">
                    <tbody>
                        <tr v-for="(libro, index) in libros" :key="index" @click="abrirLibro(libro)" v-bind:class="[libro.Habilitado==1? 'filaLibro' : '']">
                            <td><label style="font-size:0.9rem; font-weight:bold">{{libro.Libro}}</label></td>
                            <td><label style="font-size:0.9rem; font-weight:bold">{{libro.IdLibroContable}}</label></td>
                            <td>
                                <b-badge v-if="libro.Habilitado==1" variant="success">HABILITADO</b-badge>
                                <b-badge v-else variant="danger">DESABILITADO</b-badge>
                            </td>
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
import Compras from '@/components/contabilidad/Compras'

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
      libroIdSelec: null,
      libroNombreSelec: null,
    }
  },
  methods: {
      abrirLibro(data){
        if(data.Habilitado != 1){
            return
        }        
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
            this.libroIdSelec = data.IdLibroContable
            this.libroNombreSelec = data.Libro
            this.$refs['modalRegistros'].hide()
        }, 1000);
      },
      cargarLibros(){
        this.libros = []
        this.libroIdSelec = null
        this.libroNombreSelec = null
        this.axios.get(this.API + 'contabilidad/libros')
        .then((r) => {
            this.libros = r.data
            this.$refs['modalRegistros'].show()
        })
        .catch((error)=>{

        })
      },
  },
  components: {
    Compras
  }
}
</script>

<style>
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
.infoFilas td{
    font-size: 0.8rem;
}
.cabeceraFila th{
    font-size: 0.8rem;
}
</style>
