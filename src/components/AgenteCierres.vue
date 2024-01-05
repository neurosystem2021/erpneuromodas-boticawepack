<template>
    <div>
        <b-button @click="show=true" variant="secondary">Ver Cierres</b-button>
        
        <b-form >    
            <b-modal id="modalCierresAgente" 
                title="Cierres de Caja Agente"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">           
                    <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />  
                    <b-table 
                        v-if="show"
                        id="my-table" 
                        striped hover 
                        :busy.sync="isBusy" 
                        :filter="filter"
                        :items="myProvider" 
                        @row-clicked="selectCierre"
                        :fields="fields"
                        :per-page="perPage"
                        :current-page="currentPage">
                        
                      <template slot="ver" slot-scope="data">
                        <b-button :href="'/views/Ag_CierreCaja.php?idCierre=' + data.item.IdCierreCaja" variant="primary" target="_blank">Ver</b-button>
                      </template>

                      <template slot="Estado" slot-scope="data">
                        <b-badge style="font-size:0.8rem" :variant="data.item.Estado==1?'primary':'success'">{{ data.item.Estado==1?'ABIERTO':'CERRADO' }}</b-badge>
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
          }
        },
        data () {
            return {
                API: config.API,
                show: false,
                filter: '',
                items: [],
                isBusy: false,
                fields: [ 'IdCierreCaja','FechaApertura','FechaCierre', 'IdTurno', 'UsuarioReg', {key:'Estado', label:'Estado'},'ver' ],
                totalRows: 50,
                perPage: 5,
                currentPage: 1
            }
        },
        methods: {
            selectCierre (item) {
               // console.log(item)
                
            },
            myProvider (ctx) {
                // Here we don't set isBusy prop, so busy state will be handled by table itself
                // this.isBusy = true
                let promise = this.axios.get(this.API + 'agente/cierrecaja?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ctx.filter + '&sortBy=IdCierreCaja&sortDesc=1')

                return promise.then((result) => {
                this.items = result.data.data
                this.totalRows = result.data.count.total
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
    #modalCierresAgente .modal-dialog{
        max-width: 80%;
    }
</style>
