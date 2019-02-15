<template>
    <div>
        <b-button @click="cargarPreOrden" variant="outline-success">Cargar Pre Orden</b-button>
        
        <b-form >    
            <b-modal id="modalPreOrden" 
                title="Seleccionar Pre Orden"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">           
                    <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />  
                    <b-table 
                        id="my-table" 
                        striped hover 
                        ref="tablaPreOrden"
                        :busy.sync="isBusy" 
                        :filter="filter"
                        :items="myProvider" 
                        @row-clicked="selectPreOrden"
                        :fields="fields"
                        :per-page="perPage"
                        :current-page="currentPage"></b-table>
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
            this.axios.get(this.API + 'preorden/count').then((response) => {
                this.totalRows = response.data.total
            })

            if(this.$route.query.idPreOrden && this.$route.query.idCliente) {
                this.selectPreOrden({
                    IdPreOrden: this.$route.query.idPreOrden,
                    IdCliente: this.$route.query.idCliente
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
                fields: [ 'IdPreOrden', 'FechaReg', 'Cliente', 'DniRuc' ],
                totalRows: 50,
                perPage: 5,
                currentPage: 1
            }
        },
        props: ['idAlmacen'],
        methods: {
            ...mapActions({
                add: 'agregarProducto',
                obtenerStok: 'obtenerStock'
            }),
            cargarPreOrden () {
                this.show = true
                this.$refs.tablaPreOrden.refresh()
            },
            selectPreOrden (item) {
                this.axios.get(this.API + 'preorden/detalle', {
                    params: {
                        idPreOrden: item.IdPreOrden
                    }
                }).then((response) => {
                    response.data.map((item) => {
                        this.add([item.IdProducto, () => {
                            this.obtenerStok({ IdAlmacen: this.idAlmacen })
                        }, { setPrecio: true, cantidad: item.Cantidad } ])

                        this.filter = ''
                        this.show = false
                    })
                    this.$emit('onPreOrden', item)
                    this.$emit('onSelectPreOrden', item.IdCliente)
                })
                //this.add([item.IdPreOrden, false, { setPrecio: true, cantidad:  }])
            },
            myProvider (ctx) {
                // Here we don't set isBusy prop, so busy state will be handled by table itself
                // this.isBusy = true
                let promise = this.axios.get(this.API + 'preorden?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ctx.filter)

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
