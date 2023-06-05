<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
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
            <b-col md="8">

              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
            <b-col md="4">
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Documentos</p>
            </b-col>
            </b-row>
          </b-col>

          <b-table small
            ref="tablaVentas"
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
          
              <template slot="Visualizar" slot-scope="data">
                  <a class="btn btn-success" target="_blank" :href="'/imprimir/index.php?IdDocVenta=' + data.item.idDocVenta + '&preview=1'">{{data.item.TipoDoc}}</a>
              </template>
              <template slot="Reimprimir" slot-scope="data">
                <b-button variant="primary" @click="reimprimir(data.item.idDocVenta)"><i class="fas fa-print"></i></b-button>
              </template>
          </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <br />
        <br />
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'

export default {
  mounted() {
    this.axios.get(this.API + 'ventas/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'idDocVenta',
      sortDesc: true,
      productoSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'idDocVenta', label: 'ID', sortable: true },
            { key: 'Correlativo', sortable: true },
            { key: 'FechaDoc', sortable: true },
            { key: 'DniRuc', sortable: true },
            { key: 'TipoDoc', sortable: true },
            { key: 'Cliente', sortable: true },
            { key: 'Total', sortable: true },
            { key: 'Visualizar' },
            { key: 'Reimprimir', label:'Reimprimir', class:'text-center' }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
  methods: {
      rowClick (item, index) {
        this.productoSelect = item
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      actualizarTabla() {
        this.$refs.tablaVentas.refresh()
      },
      nuevoProducto () {
          this.productoSelect = {}
          this.$refs.productoForm.show = true

      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'ventas?noFecha=1&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
      },
      reimprimir(idDocVenta){
          this.axios.get(this.API + 'ventas/reimpresion/rapida?idDocVenta=' + idDocVenta)
          .then((response) => {
            let venta = {
              CampoDireccion: response.data.venta.CampoDireccion,
              FechaCredito: response.data.venta.FechaCredito,
              EsCredito: response.data.venta.EsCredito,
              PagoCon: response.data.venta.PagoCon,
              dataInfoDocVenta:{
                Fecha: response.data.venta.FechaDoc,
                Numero: response.data.venta.Numero,
                Serie: response.data.venta.Serie,
              },
              cliente:{
                Cliente: response.data.venta.Cliente,
                Direccion: response.data.venta.Direccion,
                DniRuc: response.data.venta.DniRuc,
              },
              productos:response.data.productos,
              tipoVenta:{
                CodSunat: response.data.venta.CodSunat,
              },
              vendedor: {
                Usuario: response.data.venta.UsuarioReg,
              },
              reps: 1,
              abrirCaja: 0
            }
            let ventaJson= JSON.stringify(venta)
            let left = (screen.width/2)-(800/2)
                let top = (screen.height/2)-(800/2)
                let winName='Impresion';
                let winURL='http://localhost:5000/imprimir/getdata.php';
                let windowoption='resizable=yes,height=300,width=300,location=0,menubar=0,scrollbars=1,top='+top+', left='+left;     
                let form = document.createElement("form");
                form.setAttribute("method", "post");
                form.setAttribute("action", winURL);
                form.setAttribute("target",winName);  
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'venta';
                input.value = ventaJson;
                form.appendChild(input);
                document.body.appendChild(form);                       
                window.open('', winName,windowoption);
                form.target = winName;
                form.submit();                 
                document.body.removeChild(form);  
          })
          .catch((error) => {
              console.log(error)
              this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
          })
      },
  },
  components: {
  }
}
</script>

<style>

</style>
