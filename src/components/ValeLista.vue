<template>
    <div>
        <b-button block @click="show=true" variant="success">Ver Vales</b-button>
        
        <b-form >    
            <b-modal id="modalValesCliente" 
                title="Vales Realizados"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">           
                    <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />  
                    <b-table 
                        v-if="show"
                        id="table-vales" 
                        striped hover 
                        :busy.sync="isBusy" 
                        :filter="filter"
                        :items="myProvider" 
                        @row-clicked="selectCierre"
                        :fields="fields"
                        :per-page="perPage"
                        :current-page="currentPage">

                        <b-badge variant="light">4</b-badge>

                      <template slot="CantidadVales" slot-scope="data">
                        <b-badge variant="primary" style="font-size: 1.2rem;">{{ data.item.CantidadVales }}</b-badge>
                      </template>

                      <template slot="MontoVales" slot-scope="data">
                        S/. {{ data.item.MontoVales | redondear(2) }}
                      </template> 

                      <template slot="detalles" slot-scope="row">
                        <b-button size="sm" @click="cargarInfo(row.index,row.item.IdCliente, row)" class="mr-2">
                            {{ row.detailsShowing ? '-' : '+'}}
                        </b-button>
                      </template>
                                
                      <template slot="row-details" slot-scope="row">
                        <b-card>
                            <b-row class="mb-2">
                            <table class="table b-table table-sm prod-detalle">
                                <thead>
                                    <tr>
                                    <th>Seleccionar</th>
                                    <th>Id</th>
                                    <th>Fecha</th>
                                    <th>Vendedor</th>
                                    <th>Placa</th>
                                    <th>Tipo</th>
                                    <th>Serie</th>
                                    <th>Numero</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Total</th>
                                    <th>Ver</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in row.item.Detalle" :key="index">
                                        <td :class="{'table-s': item.Seleccion}">
                                            <b-form-checkbox
                                                v-model="item.Seleccion"
                                                :value="1"
                                                class="mt-1"
                                                @input="(opc) => actualizarMonto(row.index,row.item,item,opc)"
                                                :unchecked-value="0">
                                            </b-form-checkbox>
                                        </td>
                                        <td :class="{'table-s': item.Seleccion}">{{ item.idDocVenta }}</td>
                                        <td :class="{'table-s': item.Seleccion}">{{ item.FechaDoc }}</td>
                                        <td :class="{'table-s': item.Seleccion}">{{ item.UsuarioReg }}</td>
                                        <td :class="{'table-s': item.Seleccion}"><b>{{ item.Placa }}</b></td>
                                        <td :class="{'table-s': item.Seleccion}">{{ item.TipoDoc }}</td>
                                        <td :class="{'table-s': item.Seleccion}">{{ item.Serie }}</td>
                                        <td :class="{'table-s': item.Seleccion}">{{ item.Numero }}</td>
                                        <td :class="{'table-s': item.Seleccion}">{{ item.Producto }}</td>
                                        <td :class="{'table-s': item.Seleccion}">{{ item.Cantidad }}</td>
                                        <td :class="{'table-s': item.Seleccion}">{{ item.Precio }}</td>
                                        <td :class="{'table-s': item.Seleccion}">{{ item.Total | redondear(2) }}</td>
                                        <td :class="{'table-s': item.Seleccion}"><b-btn size="sm" variant="info" :href="'/imprimir/index.php?IdDocVenta=' + item.idDocVenta" target="_blank" rel="noopener"><i class="fas fa-print"></i></b-btn></td>
                                    </tr>
                                    <tr>
                                        <td colspan="13">
                                            <b-button class="mt-2" variant="success" size="md" @click="facturarVales(row.item.IdCliente, row.item.Cliente, row.item)">FACTURAR S/. {{ row.item.Pagar | redondear(2) }} DE VALES</b-button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </b-row>
                        </b-card>
                      </template> 

                      <template slot="Facturar" slot-scope="data">
                        
                      </template>

                    </b-table>
                    <b-row>
                    <b-col md="6" class="my-1">
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                    </b-col>
                    </b-row>
                <div slot="modal-footer" >
                    <!--<b-btn size="sm" variant="success" type="submit">
                        Guardar
                    </b-btn>-->
                    <b-btn size="sm" variant="secondary" @click="show=false">
                        Cerrar
                    </b-btn>
                </div>
            
            </b-modal>
        </b-form>
    </div>
</template>
<script>
import config from '@/store/config.js'

    export default {
        mounted () {
            
        },
        watch: {
          items () {
            this.axios.get(this.API + 'proformas/count').then((response) => {
                this.totalRows = response.data.total
            })
          }
        },
        data () {
            return {
                API: config.API,
                show: false,
                filter: '',
                items: [],
                isBusy: false,
                fields: [ 'detalles',
                    { key: 'DniRuc', label: 'Documento' } , 'Cliente',
                    { key: 'CantidadVales', label: 'Vales Realizados' },
                    { key: 'MontoVales', label: 'Monto Total' }, ],
                totalRows: 50,
                perPage: 5,
                currentPage: 1,
                detalleVales: []
            }
        },
        methods: {
            facturarVales(id, cliente, obj){               
                if(obj.IdDocVentas.length==0){
                    this.$toasted.show('Debe seleccionar vales para facturar')
                    return
                }
                let r = confirm('¿Se va facturar los vales seleccionados de '+cliente+', está seguro?')
                if (r) {
                    let data = {
                        id:id,
                        cliente: cliente,
                        idDocVentas: obj.IdDocVentas,
                        monto: obj.Pagar
                    }
                    this.$emit('facturar', data)
                    this.show = false
                }
            },
            selectCierre (item) {
               // console.log(item)
                
            },
            actualizarMonto(index, item, det, opc ){
                console.log(det);
                if(opc==1){
                    this.items[index].Pagar = (this.items[index].Pagar || 0) + det.Total
                    this.items[index].IdDocVentas.push(det.idDocVenta)
                }else{
                    this.items[index].Pagar = (this.items[index].Pagar || 0) - det.Total
                    let index2 = this.items[index].IdDocVentas.indexOf(det.idDocVenta);
                    if (index2 > -1) {
                        this.items[index].IdDocVentas.splice(index2, 1);
                    }

                }
                
            },
            myProvider (ctx) {
                // Here we don't set isBusy prop, so busy state will be handled by table itself
                // this.isBusy = true
                let promise = this.axios.get(this.API + 'vales/clientes?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ctx.filter)

                return promise.then((result) => {
                this.items = result.data
                this.items.map(d=>{
                    d.Detalle = []
                    d.IdDocVentas = []
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
            cargarInfo(index, id, row){
                console.log();
                if(!row.detailsShowing){
                    this.axios.get(this.API+'vales/clientes/detalle',{
                        params: {
                            IdCliente: id
                        }
                    })
                    .then((response) => {
                        this.items[index].Detalle = response.data
                        row.toggleDetails()
                    })
                    .catch((error) => {
                        this.$toasted.show('Error no se cargo el detalle')
                    }) 
                }else{
                    this.items[index].Detalle = []
                    this.items[index].Pagar = 0
                    this.items[index].IdDocVentas = []
                    row.toggleDetails()
                }
    
            }
        }
    }
</script>
<style>
 #modalValesCliente .modal-dialog {
    max-width: 94%;
 }

 #table-vales td {
    vertical-align: middle;
 }

 #table-vales .table-s{
    background-color: #c3e6cb !important;
 }
</style>
