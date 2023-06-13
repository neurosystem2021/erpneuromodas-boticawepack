<template>
    <div>
        <b-container class="bv-example-row">
          <b-row>
            <b-col class="text-center">
              <h3>Registro de Reservas</h3>
            </b-col>
          </b-row>
          <hr>
          <b-row>
            <b-col md="3" class="my-1">
              <b-form-group horizontal label="Almacén" class="mb-0">
                <b-form-select v-model="idAlmacen" :options="almacenes" v-on:change="setAlmacen($event)" class="mb-3" size="md"/>
              </b-form-group>
            </b-col>
            <b-col md="5" class="my-1">
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
            <b-col md="4" class="my-1">
              <b-row>
              <b-col md="8">
  
                <b-form-group horizontal label="Mostrar" class="mb-0">
                  <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
                </b-form-group>
              </b-col>
              <b-col md="4">
                <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Reservas</p>
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
                <template slot="IdProyecto" slot-scope="data">
                 <label>R001-{{ data.item.IdProyecto }}</label>
                </template>
                <template slot="Estado" slot-scope="data">
                    <b-badge variant="primary" v-if="data.item.Estado==0">PENDIENTE</b-badge>
                    <b-badge variant="success" v-if="data.item.Estado==1">FACTURADO</b-badge>
                    <b-badge variant="danger" v-if="data.item.Estado==2">LIBERADO</b-badge>
                </template>
                <template slot="Visualizar" slot-scope="data">
                  <b-button variant="primary" @click="verDetalle(data.item.IdProyecto,data.item)"><i class="fas fa-eye"></i></b-button>
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
          <b-modal size="lg" id="modalDetalle" ref="modalDetalle" :title="'Reserva: '+productoSelect.IdProyecto" ok-only ok-title="Cerrar">
            <b-row>
              <b-col md="6">
                <table>
                  <tbody>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:40% !important;">RUC</td>
                      <td class="celda" style="text-transform: uppercase">: {{ productoSelect.DniRuc }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:40% !important;">Cliente</td>
                      <td class="celda" style="text-transform: uppercase">: {{ productoSelect.Cliente }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:40% !important;">Fecha Inicio</td>
                      <td class="celda" style="text-transform: uppercase">: {{ productoSelect.FechaInicio }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:40% !important;">Estado</td>
                      <td class="celda" style="text-transform: uppercase">:
                        <b-badge variant="primary" v-if="productoSelect.Estado==0">PENDIENTE</b-badge>
                        <b-badge variant="success" v-if="productoSelect.Estado==1">FACTURADO</b-badge>
                        <b-badge variant="danger" v-if="productoSelect.Estado==2">LIBERADO</b-badge>
                      </td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:40% !important;">Comprobante</td>
                      <td class="celda" style="text-transform: uppercase">: {{ productoSelect.Comprobante }}</td>
                    </tr>
                  </tbody>
                </table>
              </b-col>
              <b-col md="6">
                <table>
                  <tbody>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:40% !important;">Almacen</td>
                      <td class="celda" style="text-transform: uppercase">: {{ productoSelect.Almacen }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:40% !important;">Usuario</td>
                      <td class="celda" style="text-transform: uppercase">: {{ productoSelect.UsuarioReg }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:40% !important;">Fecha Fin</td>
                      <td class="celda" style="text-transform: uppercase">: {{ productoSelect.FechaFin }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:40% !important;">Usuario Cierre</td>
                      <td class="celda" style="text-transform: uppercase">: {{ productoSelect.UsuarioCierre }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:40% !important;">Fecha Cierre</td>
                      <td class="celda" style="text-transform: uppercase">: {{ productoSelect.FechaCierre }}</td>
                    </tr>
                  </tbody>
                </table>
              </b-col>
            </b-row>
            <hr>
            <b-row>
              <b-col md="12">
                <table>
                  <tbody>
                    <tr>
                      <td class="celda" style="font-weight:bold;">Descripción: <span style="font-weight: normal;">{{ productoSelect.Descripcion }}</span></td>
                    </tr>
                  </tbody>
                </table>
              </b-col>
            </b-row>
            <br>
            <h6>Adelantos:</h6>
            <b-row>
              <b-col md="12">
                <table class="table b-table table-sm table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">Id Caja</th>
                            <th class="text-center">Fecha</th>
                            <th class="text-center">Concepto</th>
                            <th class="text-center">Importe</th>
                            <th class="text-center">Doc</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(ade, i) in adelantos" :key="i">
                            <td class="text-center">{{ ade.IdCajaBanco }}</td>
                            <td class="text-center">{{ ade.FechaDoc }}</td>
                            <td class="text-center">{{ ade.Concepto }}</td>
                            <td class="text-center">{{ ade.Importe | moneda }}</td>
                            <td><b-btn size="sm" variant="success" :href="'/imprimir/pagocredito.php?idCajaBanco=' + ade.IdCajaBanco" target="_blank"><i class="fas fa-print"></i></b-btn></td>
                        </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                      <td colspan="2"></td>
                      <td class="text-center"><b>TOTAL ADELANTOS</b></td>
                      <td class="table-success text-center" style="vertical-align: middle !important;">S/ {{productoSelect.Adelantos | moneda}} </td>
                    </tr>
                  </tfoot>
                </table>
              </b-col>
            </b-row>
            <hr>
            <h6>Productos:</h6>
            <b-row>
              <b-col md="12">
                <table class="table b-table table-sm table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">Codigo</th>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Precio</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(prod, i) in productos" :key="i">
                            <td class="text-center">{{ prod.CodigoBarra }}</td>
                            <td class="text-center">{{ prod.Producto }}</td>
                            <td class="text-center">{{ prod.Cantidad }}</td>
                            <td class="text-center">{{ prod.Precio | moneda }}</td>
                            <td class="text-center">{{ (prod.Precio *  prod.Cantidad) | moneda }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                      <td colspan="3"></td>
                      <td class="text-center"><b>TOTAL PRODUCTOS</b></td>
                      <td class="table-success text-center" style="vertical-align: middle !important;">S/ {{productoSelect.TotalDetalle | moneda}} </td>
                    </tr>
                  </tfoot>
                </table>
              </b-col>
            </b-row>
            <hr>
            <h6>Pago Restante: {{(productoSelect.TotalDetalle - productoSelect.Adelantos)  | moneda}}</h6>
          </b-modal>
        </b-container>
    </div>
  </template>
  
  <script>
  import config from '@/store/config.js'
  
  export default {
    mounted() {
      this.axios.get(this.API + 'proyectos/count').then((response) => {
        this.totalRows = response.data.total
      })
      this.getAlmacenes();
    },
    data() {
      return {
        API: config.API,
        pageOptions: [ 5, 10, 15, 50, 100 ],
        filter: null,
        sortBy: 'IdProyecto',
        sortDesc: true,
        productoSelect: {},
        items: [],
        isBusy: false,
        gastos: [],
        adelantos: [],
        productos: [],
        fields: [
              { key: 'IdProyecto', label: 'ID Reserva', sortable: true },
              { key: 'Cliente', sortable: true },
              { key: 'DniRuc', sortable: true },
              { key: 'FechaReg', label: 'Fecha Registro', sortable: true },
              { key: 'FechaInicio', label: 'Fecha Inicio', sortable: true },
              { key: 'FechaFin', label: 'Fecha Fin', sortable: true },
              { key: 'Cliente', sortable: true },
              { key: 'Adelantos',label: 'Adelantos', sortable: true },
              { key: 'TotalDetalle',label: 'Total', sortable: true },
              { key: 'Estado',label: 'Estado', sortable: true, class:'text-center' },
              { key: 'Visualizar', class:'text-center' },
        ],
        totalRows: 50,
        perPage: 10,
        currentPage: 1,
        idAlmacen: (sessionStorage.getItem('IdPerfil')==1?0:sessionStorage.getItem('IdAlmacen')) || 0,
        almacenes: [
          { value: 0, text: 'TODOS' },
        ],
      }
    },
    methods: {
        rowClick (item, index) {
          this.productoSelect = item
        },
        verDetalle(id, item){
          this.productoSelect = item
          this.cargarCajaBanco(id)
          this.getProductos(id)
          this.$refs['modalDetalle'].show()
        },
        getProductos (id) {
          this.productos=[]
          this.axios.get(this.API + 'proyecto/detalle', {
            params: {IdProyecto: id}
          }).then(res => {
            res.data.map(pc => {
                this.productos.push(pc)
            })

          }).catch(err => {
            console.log(err)
          })
        },
        cargarCajaBanco(id){
          this.gastos=[];
          this.adelantos=[];
          this.axios.get(this.API + 'proyecto/cajaybanco/todo', { params: {
            IdProyecto: id
          } }).then(res => {
            this.cajaBanco = res.data
            this.cajaBanco.map(cb=>{
              if(cb.Tipo==0){
                this.adelantos.push(cb)
              }
              if(cb.Tipo==1){
                this.gastos.push(cb)
              }
            })
          })
        },
        actualizarTabla() {
          setTimeout(() => {
            this.$refs.tablaVentas.refresh()
          }, 300);
          
        },
        setAlmacen(){
          this.actualizarTabla()
        },
        myProvider (ctx) {
          // Here we don't set isBusy prop, so busy state will be handled by table itself
          // this.isBusy = true
          let url = this.API + 'reporte/proyectos?&idAlmacen='+this.idAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
        getAlmacenes(){
          this.almacenes = []
          this.almacenes.push({ value: 0, text: 'TODOS' })
          this.axios.get(this.API + '/almacenes').then(r => {
            if (r.data) {
              r.data.map(al=>{
                this.almacenes.push({ value: al.IdAlmacen, text: al.Almacen })
              })
            }
          })
        },
    },
    components: {
    }
    }
  </script>
  
  <style>
    #modalDetalle .modal-dialog{
      max-width: 55% !important;
    }
  </style>
  