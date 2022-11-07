<template>
    <div>
        <b-button @click="show=true" variant="outline-success">Ver Cierres</b-button>
        
        <b-form >    
            <b-modal id="modalCierres" 
                title="Seleccionar Cierre de Caja"
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
                        <b-button :href="'/views/ve_cierreform.php?idCierre=' + data.item.IdCierreCaja + '&empresa=' + empresa" variant="primary" target="_blank">Ver</b-button>
                        <b-button :href="'/api/index.php/cierrecaja/pdfresumen/' + data.item.IdCierreCaja" variant="success" >PDF Resumen</b-button>
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
            this.axios.get(this.API + 'cierrecaja/count').then((response) => {
                this.totalRows = response.data.total
            })
          }
        },
        props: ['empresa'],
        data () {
            return {
                API: config.API,
                show: false,
                filter: '',
                items: [],
                isBusy: false,
                fields: [ 'IdCierreCaja', 'FechaCierre', 'IdTurno', 'UsuarioReg', 'ver' ],
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
                let promise = this.axios.get(this.API + 'cierrecaja?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ctx.filter + '&sortBy=IdCierreCaja&sortDesc=1&idPerfil='+(sessionStorage.getItem('IdPerfil') || 3)+'&usuario='+sessionStorage.getItem('User'))

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
