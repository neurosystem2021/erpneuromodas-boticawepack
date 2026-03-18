<template>
    <div>
        <b-button @click="show=true" variant="outline-primary" size="sm">Ver Proformas</b-button>
        
        <b-form ref="formProformaModal">    
            <b-modal id="modalProformas" 
                title="Buscar Proforma"
                size="lg" v-model="show"
                header-bg-variant="dark"
                header-text-variant="light"
                body-bg-variant="light"
                body-text-variant="dark">           
                    <b-row class="mb-3">
                    <b-col>
                        <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />  
                    </b-col>
                    </b-row>
                    <div class="table-responsive">
                    <b-table 
                        v-if="show"
                        id="table-proformas"
                        ref="tableProformas"
                        striped hover 
                        :busy.sync="isBusy" 
                        :filter="filter"
                        :items="myProvider" 
                        :fields="fields"
                        :per-page="perPage"
                        :current-page="currentPage">

                        <template slot="update" slot-scope="data">
                        <b-button @click="selectProformas(data.item)" variant="success" size="sm">
                            <i class="fas fa-pencil-alt"></i>
                        </b-button>
                        </template>

                      <template slot="ver" slot-scope="data">
                        <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma" variant="primary" target="_blank" size="sm">Ver</b-button>
                      </template>

                        <template slot="verTicket" slot-scope="data">
                        <b-button :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma" variant="primary" target="_blank" size="sm">Ver Ticket</b-button>
                        </template>

                        <template slot="Total" slot-scope="data">
                        {{ data.item.Total | moneda }}
                        </template>

                        <template slot="Eliminar" slot-scope="data">
                        <b-button @click="eliminarProf(data.item)" variant="danger" size="sm">
                            <i class="fas fa-trash"></i>
                        </b-button>
                        </template>

                    </b-table>
                    </div>
                    <b-row class="mt-3">
                    <b-col md="6" class="my-1">
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                    </b-col>
                    </b-row>
                <template slot="modal-footer">
                    <!--<b-btn size="sm" variant="success" type="submit">
                        Guardar
                    </b-btn>-->
                    <b-btn size="sm" variant="secondary" @click="show=false">
                        Cerrar
                    </b-btn>
                </template>
            
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
                Usuario: sessionStorage.getItem('User'),
                IdUsuarioPerfil: parseInt(sessionStorage.getItem('IdPerfil')),
                fields: [ 
                    { key: 'IdProforma', label: 'Id' }, 
                    { key: 'Anio', label: 'Año' } , 'Numero', 'Cliente', 
                    { key: 'DniRuc', label: 'Documento' }, 'FechaReg', 
                    { key: 'UsuarioReg', label: 'Empleado' }, { key: 'Total', label: 'Total' }, 'update', 'ver', 'verTicket','Eliminar' ],
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
            selectProformas (proforma) {
                this.axios.get(this.API + 'proformas/detalle/nuevo', {
                    params: {
                        idProforma: proforma.IdProforma
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
            },
            eliminarProf(data){
                if (this.IdUsuarioPerfil !== 1 && this.IdUsuarioPerfil !== 7) {
                    this.$toasted.error("Solo el usuario admin puede eliminar esta proforma.");
                    return;
                }
                this.$swal({
                target: document.getElementById('conten'),
                title: 'Se eliminará la proforma: <strong>'+data.Anio+' - '+data.Numero+'</strong> del  cliente <strong>'+data.Cliente+'</strong>',
                text:'¿Está seguro?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'No, atrás'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.axios.delete(this.API + 'proforma/eliminar/'+data.IdProforma).then(res => {
                            if(res.data){
                               this.$toasted.info('¡PROFORMA ELIMINADA!')
                                this.$refs.tableProformas.refresh()
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
#modalProformas .modal-dialog {
    max-width: 88% !important;
}
</style>
