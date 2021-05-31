<template>
    <div>
        <b-button @click="show=true" variant="outline-primary" size="sm">Ver Proformas</b-button>
        
        <b-form ref="formProformaModal" >    
            <b-modal id="modalProformas" 
                title="Buscar Proforma"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">           
                    <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />  
                    <b-table 
                        v-if="show"
                        id="table-proformas" 
                        striped hover 
                        :busy.sync="isBusy" 
                        :filter="filter"
                        :items="myProvider" 
                        :fields="fields"
                        :per-page="perPage"
                        :current-page="currentPage">

                      <template slot="update" slot-scope="data">
                        <b-button @click="selectProformas(data.item)" variant="success"><i class="fas fa-pencil-alt"></i></b-button>
                      </template>

                      <template slot="ver" slot-scope="data">
                        <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma" variant="primary" target="_blank">Ver</b-button>
                      </template>

                       <template slot="verTicket" slot-scope="data">
                        <b-button :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma" variant="primary" target="_blank">Ver Ticket</b-button>
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
                fields: [ 
                    { key: 'IdProforma', label: 'Id' }, 
                    { key: 'Anio', label: 'Año' } , 'Numero', 'Cliente', 
                    { key: 'DniRuc', label: 'Documento' }, 'FechaReg', 
                    { key: 'UsuarioReg', label: 'Empleado' }, 'update', 'ver' ,'verTicket'],
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
                limpiarCarrito: 'limpiarCarrito'
            }),
            selectProformas (proforma) {
                this.axios.get(this.API + 'proformas/detalle', {
                    params: {
                        idProforma: proforma.IdProforma
                    }
                }).then((response) => {
                    response.data.map((item) => {
                        this.add([item.IdProducto, () => {
                            this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 })
                        }, { setPrecio: true, cantidad: item.Cantidad } ])

                        this.filter = ''
                        this.show = false
                    })
                    proforma.productos = response.data
                    // console.log(proforma);

                    // this.$emit('onSelectProforma', proforma.IdCliente, proforma)
                    this.$emit('onSelectProforma', proforma)
                    this.limpiarCarrito()
                })
            },
            myProvider (ctx) {
                // Here we don't set isBusy prop, so busy state will be handled by table itself
                // this.isBusy = true
                let promise = this.axios.get(this.API + 'proformas?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ctx.filter)

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
        }
    }
</script>
<style>
#modalProformas .modal-dialog {
    max-width: 65% !important;
   }
</style>
