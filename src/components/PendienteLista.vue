<template>
    <div>
        <b-button @click="show=true" variant="outline-primary" size="sm">Repartidor Pendientes de Pago</b-button>
        
        <b-form >    
            <b-modal id="modalPendientes" 
                title="Buscar Ventas Pendientes de Pago"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">
                
                <b-row>
                    <b-col md="6" class="my-1">
                        <b-form-group>
                            <label>Buscar:</label>
                            <b-form-input placeholder="Busqueda..." v-model="filter" @input="currentPage = 1" />
                        </b-form-group>
                    </b-col>
                    <b-col md="6" class="my-1">
                        <b-form-group>
						    <label>Almacen:</label>
						    <autocomplete
                                required="required"
                                ref="inputAlmacen"
                                :url="API + 'almacenes'"
                                anchor="Almacen"
                                initValue="TODOS"
                                label="writer"
                                placeholder=""
                                :on-select="setAlmacen" 
                                :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                                :classes="{input: 'form-control'}">
						    </autocomplete>
					    </b-form-group>
                    </b-col>
                    <b-col md="12">
                        <b-form-group>
                            <b-btn @click="limpiarFiltros">Limpiar</b-btn>
                        </b-form-group>
                    </b-col>
                </b-row>
                    
                    <b-table 
                        v-if="show"
                        id="table-pendientes" 
                        ref="tablaPendientes"
                        striped hover 
                        :busy.sync="isBusy" 
                        :filter="filter"
                        :items="myProvider" 
                        :fields="fields"
                        :per-page="perPage"
                        :current-page="currentPage">
                        
											<template slot="PagoCon" slot-scope="data">
												{{ data.item.PagoCon > 0 ? data.item.PagoCon : data.item.total }}
											</template>

                      <template slot="vuelto" slot-scope="data">
												<div v-if="data.item.PagoCon > 0">
                        	{{ data.item.PagoCon - data.item.total }}
												</div>
												<div v-else>
													--
												</div>
                      </template>

                      <template slot="pagar" slot-scope="data">
                        <b-button variant="primary" target="_blank" @click="pagarVentaPendiente(data.item.idDocVenta)">Pagar</b-button>
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
            //this.limpiarFiltros();
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
                almacen: {
                    IdAlmacen: ''
                },
                isBusy: false,
                fields: [ 
                    { key: 'idDocVenta', label: 'Id' }, 
                    { key: 'FechaReg', label: 'Fecha Venta' }, 
                    { key: 'Serie', label: 'Serie' }, 
                    { key: 'Numero', label: 'Numero' },
                    { key: 'Cliente', label: 'Cliente' },
                    { key: 'Almacen', label: 'Almacen' },
                    { key: 'Repartidor', label: 'Repartidor' },
                    { key: 'total', label: 'Total' },
                    { key: 'PagoCon', label: 'Paga con', variant: 'danger' },
                    { key: 'vuelto', label: 'Vuelto'},
                    'pagar' 
                ],
                totalRows: 50,
                perPage: 5,
                currentPage: 1
            }
        },
        methods: {
            pagarVentaPendiente(idDocVenta) {
                let r = confirm('Esta seguro de pagar la venta pendiente?')
                if (r == true) {
                    this.axios.post(this.API + 'ventas/pendientes/pago', {
                        idDocVenta: idDocVenta
                    }).then(response => {
                        this.$toasted.show('Venta pagada correctamente!')
                        this.$refs.tablaPendientes.refresh()
                    }).catch((error) => {
                        console.log(error)
                    })
                }
            },
            setAlmacen (obj) {
                this.almacen = obj || {}
                this.$refs.tablaPendientes.refresh()
            },
            focusAutocomplete (ref) {
            // this.$refs[ref].type = ""
            this.$refs[ref].handleDoubleClick()
            },
            limpiarFiltros() {
                this.filter = ''
                this.almacen.IdAlmacen = ''
                this.$refs.inputAlmacen.setValue('TODOS')
                this.$refs.tablaPendientes.refresh()
            },
            myProvider (ctx) {
                // Here we don't set isBusy prop, so busy state will be handled by table itself
                // this.isBusy = true
                let promise = this.axios.get(this.API + 'ventas/pendientes?limit='+ctx.perPage+'&idAlmacen='+this.almacen.IdAlmacen+'&page='+ctx.currentPage+'&filter='+ctx.filter)

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
<style scoped>
#table-pendientes {
    font-size: .8em;
}
</style>
