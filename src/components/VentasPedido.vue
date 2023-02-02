<template>
    <div>
        <b-button @click="show=true" variant="outline-success" size="md">Ventas a Pedido</b-button>
        
        <b-form ref="formProformaModal" >    
            <b-modal id="modalVentaPedido" 
                title="Buscar Venta a Pedido"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">           
                    <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />  
                    <b-table 
                        v-if="show"
                        id="table-proformas"
                        ref="tableVentaPedido"
                        striped hover 
                        :busy.sync="isBusy" 
                        :sort-by.sync="sortBy"
                        :sort-desc.sync="sortDesc"
                        :filter="filter"
                        :items="myProvider" 
                        :fields="fields"
                        :per-page="perPage"
                        :current-page="currentPage">

                      <template slot="facturar" slot-scope="data">
                        <div v-if="data.item.PedidoFacturado!=null">
                            <label>----</label>
                        </div>
                        <div v-else>
                            <b-button v-if="data.item.Anulado==0" @click="selectVentaPedido(data.item)" variant="success"><i class="fas fa-hand-pointer"></i></b-button>
                            <label v-else>----</label>
                        </div>

                      </template>

                      <template slot="total" slot-scope="data">
                        <b>{{(data.item.Total).toFixed(2)}}</b>
                      </template>

                      <template slot="aplicado" slot-scope="data">
                        <b>{{(data.item.Aplicado).toFixed(2)}}</b>
                      </template>

                      <template slot="pendiente" slot-scope="data">
                        <div v-if="data.item.PedidoFacturado!=null">
                            <label>----</label>
                        </div>
                        <div v-else>
                            <b>{{((data.item.Total || 0) - (data.item.Aplicado || 0)).toFixed(2)}}</b>
                        </div>
                      </template>

                      <template slot="verTicket" slot-scope="data">
                        <b-button size="sm" :href="'/imprimir/index.php?IdDocVenta=' + data.item.idDocVenta" variant="primary" target="_blank">Ver Doc</b-button>
                      </template>
                      
                      <template slot="Anular" slot-scope="data">
                        <div v-if="data.item.PedidoFacturado!=null">
                            <b-badge variant="success" style="font-size:1rem">{{data.item.PedidoFacturado}}</b-badge>
                        </div>
                        <div v-else>
                            <b-button v-if="data.item.Anulado==0" @click="anularVentaPedido(data.item)" variant="danger"><i class="fas fa-ban"></i></b-button>
                            <h5 v-else><b-badge  variant="danger">Anulado</b-badge></h5>
                        </div>
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
import { mapActions } from 'vuex'

    export default {
        mounted () {
            this.$emit('mounted', this.$refs.formProformaModal)
        },
        watch: {
          items () {
            this.axios.get(this.API + 'ventapedido/count').then((response) => {
                this.totalRows = response.data.total
            })
          }
        },
        data () {
            return {
                API: config.API,
                show: false,
                filter: '',
                sortBy: 'idDocVenta',
                sortDesc: true,
                items: [],
                isBusy: false,
                fields: [ 
                    { key: 'idDocVenta', label: 'Id' }, 'Almacen',
                    'Serie', 'Numero', 'Cliente', 
                    { key: 'DniRuc', label: 'Documento' }, 'FechaDoc', 
                    { key: 'UsuarioReg', label: 'Empleado' }, { key: 'total', label: 'Total' }, { key: 'aplicado', label: 'Adelantos' },{ key: 'pendiente', label: 'Pendiente' }, 'facturar', 'verTicket', { key: 'Anular', label: 'Estado' } ],
                totalRows: 50,
                perPage: 5,
                currentPage: 1
            }
        },
        props: ['idAlmacen'],
        methods: {
            ...mapActions({
                add         : 'agregarProducto',
                obtenerStok : 'obtenerStock',
                limpiarCarrito: 'limpiarCarrito',
            }),
            selectVentaPedido (proforma) {
                this.axios.get(this.API + 'venta/pedido/detalle', {
                    params: {
                        idDocVenta: proforma.idDocVenta
                    }
                }).then((response) => {
                    this.limpiarCarrito()
                    response.data.map((item) => {
                        this.add([item, () => {
                        }, {setPreOr: item.Precio, cantidad: item.Cantidad } ])
                        this.filter = ''
                        this.show = false
                    })
                    setTimeout(() => {
                        this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 })        
                    }, 300);
                    proforma.productos = response.data
                    // console.log(proforma);

                    // this.$emit('onSelectProforma', proforma.IdCliente, proforma)
                    this.$emit('onSelectProforma', proforma)
                    this.$emit('onFacturar', proforma)
                })
            },
            myProvider (ctx) {
                // Here we don't set isBusy prop, so busy state will be handled by table itself
                // this.isBusy = true
                let promise = this.axios.get(this.API + 'ventas/pedido?idAlmacen='+this.idAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ctx.filter+'&sortBy='+ctx.sortBy+'&sortDesc='+(ctx.sortDesc ? 1 : 0 ))

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
            anularVentaPedido(data){
                this.$swal({
                target: document.getElementById('conten'),
                title: 'Se anulara la venta a pedido: <strong>'+data.Serie+' - '+data.Numero+'</strong> del  cliente <strong>'+data.Cliente+'</strong>',
                text:'¿Está seguro?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, anular',
                cancelButtonText: 'No, atrás'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.axios.put(this.API + 'venta/pedido/anular/'+data.idDocVenta).then(res => {
                            if(res.data){
                               this.$toasted.info('¡VENTA A PEDIDO ANULADA!')
                                this.$refs.tableVentaPedido.refresh()
                            }
                        }).catch(err => {
                            console.log(err)
                        this.$toasted.error('Ha ocurrido un error')
                        })        
                    }
                })
            }
        }
    }
</script>
<style>
#modalVentaPedido .modal-dialog {
    max-width: 95% !important;
}
</style>
