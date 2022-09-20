<template>
    <div>
        <b-button @click="show=true" variant="outline-primary">Ver Proformas</b-button>
        
        <b-form ref="formProformaModal" >    
            <b-modal id="modalProformas" 
                title="Buscar Proforma"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">           
                    <b-button-group size="sm">
                        <select class="form-control" v-model="empresaselec" @change="cambiarEmpresa" style="font-weight: bold; width: 300px">
                            <option value="rosmart">EMPRESA ROSMART</option>
                            <option value="dismart">EMPRESA DISMART</option>
                        </select>
                    </b-button-group>
                    <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />  
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
                        <b-button @click="selectProformas(data.item)" variant="success"><i class="fas fa-hand-pointer"></i></b-button>
                      </template>

                      <template slot="ver" slot-scope="data">
                        <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma+'?empresa=' + empresaselec" variant="primary" target="_blank">Ver</b-button>
                      </template>

                      <template slot="verTicket" slot-scope="data">
                        <b-button :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma + '&empresa=' + empresaselec" variant="primary" target="_blank">Ver Ticket</b-button>
                      </template>
                      
                      <template slot="Eliminar" slot-scope="data">
                        <b-button @click="eliminarProf(data.item)" variant="danger"><i class="fas fa-trash"></i></b-button>
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
            this.axios.get(this.API + 'proformas/count?prof=1&emp='+this.empresaselec).then((response) => {
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
                    { key: 'UsuarioReg', label: 'Empleado' }, 'update', 'ver', 'verTicket','Eliminar' ],
                totalRows: 50,
                perPage: 5,
                currentPage: 1,
                empresaselec: sessionStorage.getItem('empresa') || 'dismart'
            }
        },
        props: ['idAlmacen','empresa'],
        methods: {
            ...mapActions({
                add         : 'agregarProducto',
                obtenerStok : 'obtenerStock',
                limpiarCarrito: 'limpiarCarrito',
            }),
            cambiarEmpresa(){
                this.$refs.tableProformas.refresh()
            },
            selectProformas (proforma) {
                this.axios.get(this.API + 'proformas/detalle/nuevo', {
                    params: {
                        idProforma: proforma.IdProforma,
                        prof: 1,
                        emp: this.empresaselec
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
                let promise = this.axios.get(this.API + 'proformas?prof=1&emp='+this.empresaselec+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ctx.filter)

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
    max-width: 65% !important;
}
</style>
