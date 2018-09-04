<template>
    <div>
        <b-button @click="show=true" variant="outline-primary" size="sm">Ver Pendientes de Pago</b-button>
        
        <b-form >    
            <b-modal id="modalPendientes" 
                title="Buscar Ventas Pendientes de Pago"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">           
                    <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />  
                    <b-table 
                        v-if="show"
                        id="table-pendientes" 
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
                        <b-button variant="primary" target="_blank">Pagar</b-button>
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
                fields: [ 
                    { key: 'idDocVenta', label: 'Id' }, 
                    { key: 'FechaReg', label: 'Fecha' }, 
                    { key: 'Serie', label: 'Serie' }, 
                    { key: 'Numero', label: 'Numero' },
                    { key: 'Cliente', label: 'Cliente' },
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
            myProvider (ctx) {
                // Here we don't set isBusy prop, so busy state will be handled by table itself
                // this.isBusy = true
                let promise = this.axios.get(this.API + 'ventas/pendientes?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ctx.filter)

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

</style>
