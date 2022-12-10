<template>
  <div>
      <b-container class="bv-example-row" id="conten">
        <b-row class="text-left" style="margin-bottom: -15px">
          <b-col md="9"><h1>COBROS</h1></b-col>
          <b-col md="3">
            <b-input-group prepend="Usuario:" class="mt-2">
                    <autocomplete
                        required="required"
                        ref="inputVendedor"
                        :url="API + 'usuarios'"
                        anchor="NombreUsuario"
                        class="font-weight-bold"
                        label="writer"
                        :on-select="setVendedor" 
                        :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                        :classes="{input: 'form-control'}">
                    </autocomplete>
            </b-input-group>
          </b-col>
        </b-row>
        <hr>
        <b-row>
          <b-col md="6" class="my-1">
            <b-form-group class="mb-0">
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
            <b-col md="5">

              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1" style="margin-left: 5px"/>
              </b-form-group>
            </b-col>
            <b-col md="3">
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Docs</p>
            </b-col>
            <b-col md="4">
              <b-button variant="primary" @click="actualizar()"><i class="fas fa-sync-alt"></i> ACTUALIZAR</b-button>
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
          
              <template slot="Estado" slot-scope="data">
                  <div v-if="data.item.Anulado==1" class="text-center">
                    <b-badge variant="danger"><b>ANULADO</b></b-badge>
                  </div>
                  <div v-else class="text-center">
                      <span v-if="data.item.Pagado==0 && data.item.EsCredito==0">
                         <b-button variant="warning" size="sm" block @click="onCobrar(data.item.idDocVenta)"><b>COBRAR</b></b-button>
                      </span>
                      <span v-else>
                        <div>
                        <b-badge variant="success"><b v-if="data.item.EsCredito==0">COBRADO</b><b v-else>CREDITO</b></b-badge>
                        </div>
                      </span>
                  </div>
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
        
      <b-modal size="lg" ref="modalCobrar" hide-footer :title="'COBRAR VENTA: '+titulo">
        <b-row>
          <b-col md="6">
            <table>
              <tbody>
                <tr>
                  <td class="celda" style="font-weight:bold; width:35% !important;">Documento</td>
                  <td class="celda" style="text-transform: uppercase">: {{ productoSelect.DniRuc }}</td>
                </tr>
                <tr>
                  <td class="celda" style="font-weight:bold; width:35% !important;">Cliente</td>
                  <td class="celda" style="text-transform: uppercase">: {{ productoSelect.Cliente }}</td>
                </tr>
                <tr>
                  <td class="celda" style="font-weight:bold; width:35% !important;">Forma de Pago</td>
                  <td class="celda" style="text-transform: uppercase">: {{ productoSelect.EsCredito==1?'CREDITO':'CONTADO' }}</td>
                </tr>
                <tr v-if="productoSelect.EsCredito==0">
                  <td class="celda" style="font-weight:bold; width:35% !important;">Metodo de Pago</td>
                  <td class="celda" style="text-transform: uppercase">: {{ productoSelect.Efectivo>0?'EFECTIVO':''}} {{ productoSelect.Visa>0?'VISA':''}} {{ productoSelect.Mastercard>0?'MASTERCARD':''}}</td>
                </tr>
              </tbody>
            </table>
          </b-col>

          <b-col md="6">
            <table>
              <tbody>
                <tr>
                  <td class="celda" style="font-weight:bold; width:35% !important;">Fecha y Hora</td>
                  <td class="celda" style="text-transform: uppercase">: {{ productoSelect.FechaDoc }}</td>
                </tr>
                <tr>
                  <td class="celda" style="font-weight:bold; width:35% !important;">Por Entregar</td>
                  <td class="celda" style="text-transform: uppercase">: 
                    <span v-if="productoSelect.PorEntregar==1"><b-badge variant="success"><b>SI</b></b-badge></span>
                    <span v-else><b-badge variant="warning"><b>NO</b></b-badge></span>
                  </td>
                </tr>
                <tr>
                  <td class="celda" style="font-weight:bold; width:35% !important;">Atendido Por</td>
                  <td class="celda" style="text-transform: uppercase">: {{ productoSelect.UsuarioReg }}</td>
                </tr>
              </tbody>
            </table>
          </b-col>
        </b-row>
        <hr>
        <b-row v-if="productoSelect.PorEntregar==1">
          <b-col>
            <h5>OBS:</h5>
            <h6 v-if="productoSelect.PorEntregar==1"><i class="fas fa-plus text-danger"></i> La venta está marcada por entregar</h6>
          </b-col>
        </b-row>
        <hr>
        <table class="table table-striped" style="width:100%">
          <thead>
            <tr>
              <th class="text-center">#</th>
              <th>PRODUCTO</th>
              <th class="text-center">CANTIDAD</th>
              <th class="text-center">PRECIO UNI.</th>
              <th class="text-center">TOTAL</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(detalle, index) in productoSelect.Detalle" :key="index">
              <td class="text-center">{{ index+1 }}</td>
              <td>{{ detalle.Producto }}</td>
              <td class="text-center">{{ detalle.Cantidad }}</td>
              <td class="text-center">{{ detalle.Precio | moneda }}</td>
              <td class="text-center">{{ (detalle.Precio * detalle.Cantidad) | moneda }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3"></td>
              <td><b>SUBTOTAL</b></td>
              <td class="table-primary" style="vertical-align: middle !important;">S/ {{productoSelect.SubTotal | moneda}} </td>
            </tr>
            <tr v-if="productoSelect.DescuentoTotal>0">
              <td colspan="3"></td>
              <td><b>DESCUENTO</b></td>
              <td class="table-danger" style="vertical-align: middle !important;">- S/ {{productoSelect.DescuentoTotal | moneda}} </td>
            </tr>
            <tr>
              <td colspan="3"></td>
              <td><b>TOTAL</b></td>
              <td class="table-success" style="vertical-align: middle !important;">S/ {{productoSelect.Total | moneda}} </td>
            </tr>
          </tfoot>
        </table>
        <b-btn :disabled="inProgress" class="mt-3" variant="success" block @click="cobrarVenta(productoSelect.idDocVenta)">COBRAR</b-btn>
      </b-modal>
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
    this.getVendedor()
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
            { key: 'Total', sortable: true, class:'centrar' },
            { key: 'Estado', class:'centrar'}
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      titulo: '',
      inProgress: false,
      vendedor: {}
    }
  },
  methods: {
      getVendedor () {
          let usuario = sessionStorage.getItem('User')
          this.axios.get(this.API + 'usuarios/' + usuario)
          .then((response) => {
              this.setVendedor(response.data)
                this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
                this.$refs.inputVendedor.$refs.input.disabled = true
          })
          .catch((error) => {
              console.log(error)
              this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
          })
      },
      setVendedor (obj) {
          this.vendedor = obj
      },
      focusAutocomplete (ref) {
          // this.$refs[ref].type = ""
          this.$refs[ref].handleDoubleClick()
      },
      onCobrar(idDocVenta){
        let params = {IdDocVenta: idDocVenta}
        this.axios.get(this.API + 'venta/info', {params: params})
        .then((response) => {
          if(response.data){
            if(response.data.Pagado==0){
              this.productoSelect = response.data
              this.titulo = this.productoSelect.Serie + " - "+ this.productoSelect.Numero
              this.$refs.modalCobrar.show()
              let descuentoTotal = 0
              this.productoSelect.Detalle.map((det)=>{
                descuentoTotal += det.Descuento
              })
              this.productoSelect.DescuentoTotal = descuentoTotal
            }else{
              this.$toasted.show('La venta fue cobrada anteriormente')
              this.$refs.tablaVentas.refresh()
            }
          }
          
        }).catch((err)=>{
          console.log(err);
          this.$toasted.error('Error: Intente nuevamente') 
        })
        
      },
      actualizar(){
        this.$refs.tablaVentas.refresh()
        this.$toasted.info('¡Se actualizó las ventas!') 
      },
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
          this.items.map(r=>{
            if(r.Pagado==1){
                  r._rowVariant='success'
            }else{
              r._rowVariant='warning'
            }

            if(r.Anulado==1){
              r._rowVariant='danger'
            }
          })
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
      cobrarVenta(idDocVenta){
        this.$swal({
        target: document.getElementById('conten'),
        title: 'La venta: <strong>'+this.productoSelect.Serie+'-'+this.productoSelect.Numero+'</strong> pasará a estado <strong>COBRADO</strong>',
        text:'¿Está seguro?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#dc3545',
        confirmButtonText: 'Sí, cobrar',
        cancelButtonText: 'No, atrás'
        }).then((result) => {
            if (result.isConfirmed) {
              this.inProgress = true
              this.axios.post(this.API + 'venta/cobrar', {IdDocVenta: idDocVenta, Usuario: sessionStorage.getItem('User') || 'xx'})
              .then((response) => {
                if(response.data){
                  this.$swal({
                    position: 'center',
                    icon: 'success',
                    title: '¡La venta <strong>'+this.productoSelect.Serie+'-'+this.productoSelect.Numero+'</strong> fue cobrada correctamente!',
                    showConfirmButton: true,
                    confirmButtonText: 'Ok',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#1D9828',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                  })
                  if(this.productoSelect.PorEntregar!=1){
                    let ventaJson= JSON.stringify(null)
                    var left = (screen.width/2)-(800/2)
                    var top = (screen.height/2)-(800/2)
                    let params = 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=300,height=300,top='+top+', left='+left
                    window.open('http://localhost:5000/imprimir/getdata.php?venta='+encodeURIComponent(ventaJson), "Impresión", params)
                  }
                  window.open('/imprimir/index.php?IdDocVenta=' + idDocVenta,'_blank');
                }else{
                  this.$toasted.show('La venta fue cobrada anteriormente')
                }
                this.$refs.modalCobrar.hide()
                this.$refs.tablaVentas.refresh()
                this.inProgress = false
              })
              .catch((error) => {
                  this.$toasted.error('Ha ocurrido un error, vuelva a intentarlo')
                  this.inProgress = false
              })
            }
        })
      }

  },
  components: {
  }
}
</script>

<style>
.celda{
  text-align:left;
  border: none !important;
  background: white !important
}
.centrar{
vertical-align: middle !important;
}
</style>
