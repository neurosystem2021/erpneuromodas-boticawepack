<template>
    <div>
        <b-button @click="show=true" variant="outline-success" size="sm">Retiros FISE</b-button>
        
        <b-form @submit="guardarPago">    
            <b-modal id="" 
                title="Retiros FISE" 
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">
                
                <b-row class="mb-1">
                    <b-col>
                    <b>Ventas FISE</b><br /><br />
                         <table class="table b-table table-sm" >
                            <thead>
                                <tr>
                                    <th>Almacen</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="ventasFise.Importe>0" v-for="(ventasFise, index) in ventasFise" :key="index">
                                    <td>{{ventasFise.Almacen}}</td>
                                    <td>{{ventasFise.Importe}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
                <b-row class="mb-1">
                    <b-col>

                    <b>Compras FISE</b><br /><br />
                         <table class="table b-table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                    <td>{{comprasFise.Importe}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
                <b-row  style="background: rgb(235, 97, 44);color: #fff;padding: 10px;">
                    <b-col style="text-align: right">
                        <b>Total Acumulado FISE: </b><br>
                        <b>Gran TOTAL: </b>
                    </b-col>
                    <b-col>
                        <b>S/ {{totalVentaFise+totalCompraFise}}</b><br>
                        <b>S/ {{totalVentaFise+totalCompraFise}}</b>
                    </b-col>
                </b-row>
                
                <br>
                <b-row class="mb-1">
                    <!--<b-col>
                        <b-form-input
                            required
                            v-model="fechaRetiro"
                            type="date"
                            placeholder=""></b-form-input>
                    </b-col>-->
                    <b-col md="3">
                        <b-form-input
                            required
                            v-model="conceptoRetiro"
                            type="text"
                            placeholder="Concepto"
                            autocomplete="off"></b-form-input>
                    </b-col>
                    <b-col md="3">
                        <b-form-input
                            required
                            v-model="importeRetiro"
                            type="number"
                            placeholder="Importe"
                            @input="validaRetiro"
                            autocomplete="off"></b-form-input>
                    </b-col>
                    <b-col md="2" >
                        <b-form-group>
                        <autocomplete
                            required="required"
                            ref="inputAlmacen"
                            :url="API + 'almacenes'"
                            anchor="Almacen"
                            initValue=""
                            label="writer"
                            placeholder=""
                            :on-select="setAlmacen" 
                            :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                            :classes="{input: 'form-control'}">
                        </autocomplete>
                        </b-form-group>
                    </b-col>
                    <b-col>
                        <div class="floast-right">
                            <b-btn style="margin-left: 30px" size="md" variant="success" type="submit">
                                Retirar
                            </b-btn>
                            <b-btn style="margin-left: 20px" size="md" variant="secondary" @click="show=false">
                                Cerrar
                            </b-btn>
                        </div>
                    </b-col>
                </b-row>
                <hr>
                <br>
                <b-row class="mb-1">
                    <b-col>
                        <b-form-group>
                            <b-form-input 
                            v-model="fechaInicio"
                            type="date"
                            @input="val => generateFilter('fechaInicio', val) "
                            placeholder=""></b-form-input>
                        </b-form-group>
                    </b-col>
                        
                    <b-col>
                        <b-form-group>
                            <b-form-input
                            v-model="fechaFin"
                            type="date"
                            placeholder=""
                            @input="val => generateFilter('fechaFin', val) "></b-form-input>
                        </b-form-group> 
                    </b-col>

                    <b-col>
                        <div class="floast-right">
                            <b-btn size="sm" variant="info" :disabled="this.filter==''?true:false" style="margin-top: 4px;" @click="limpiarFiltros">
                                Limpiar Fecha
                            </b-btn>
                        </div>
                    </b-col>
                    <!--<b-col md="2" style="margin-top: -20px;">
                        Mostrar
                        <b-form-select :options="pageOptions"/>
                        <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
                    </b-col>-->
                </b-row>

                <b-row>
                    <b-col>
                    <table class="table b-table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha Retiro</th>
                                <th>Almacen</th>
                                <th>Concepto</th>
                                <th>Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(ret, index) in retiros" :key="index">
                                <td>{{ ret.IdFiseRetiro }}</td>
                                <td>{{ ret.FechaDoc }}</td>
                                <td>{{ ret.Almacen }}</td>
                                <td>{{ ret.Concepto }}</td>
                                <td>{{ ret.Importe }}</td>                            
                            </tr>
                            <tr style="font-weight: bold;">
                                <td colspan="4" style="text-align: center;"> Total retiros</td>
                                <td>{{ totalRetiros }}</td>
                            </tr>
                        </tbody>
                    </table>
                    </b-col>
                </b-row>
                <div slot="modal-footer" >
                    
                </div>
            
            </b-modal>
        </b-form>
    </div>
</template>
<script>
import config from '@/store/config.js'

    export default {
        mounted () {
            this.loadFirstAlmacen()
            this.getVentasFise()
            this.getComprasFise()
            this.getRetiros()
            this.limpiarFiltros()
        },
        data () {
            return {
                API: config.API,
                fechaInicio: this.$moment().format("YYYY-MM-01"),
                fechaFin: this.$moment().format("YYYY-MM-DD"),
                //fechaRetiro: this.$moment().format("YYYY-MM-DD"),
                conceptoRetiro: '',
                importeRetiro: '',
                ventasFise: [],
                comprasFise: [],
                totalVentaFise: 0,
                totalCompraFise: 0,
                capturaImp: 0,
                show: false,
                retiros: [],
                filters: {},
                filter: '',
                pageOptions: [ 5, 10, 15, 50, 100 ],
                almacen: {
                    IdAlmacen: 1
                }
            }
        },
        computed: {
            totalRetiros() {
                let total = 0
                this.retiros.forEach(r => {
                    total += r.Importe
                });
                return total
            },
        },
        methods: {
            loadFirstAlmacen () {
            this.axios.get(this.API + 'almacenes/primero')
                .then(res => {
                this.$refs.inputAlmacen.setValue(res.data.Almacen)
                this.setAlmacen(res.data)
                //this.almacen = res.data
                })
            },
            getVentasFise() {
                let total
                this.axios.get(this.API + 'ventas/fise')
                .then((response) => {
                    this.ventasFise = response.data
                    this.totalVentaFise = response.data.map( item => item.Importe).reduce((sumaTotal, actual) => sumaTotal + actual)
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            },
            generateFilter (key, value) {
                this.filters[key] = value

                let filter1 = ''
                for (var key in this.filters) {
                    filter1 += '&filter[' + key + ']=' + this.filters[key]
                }
                this.filter = filter1
                this.getRetiros();
            },
            limpiarFiltros() {
                this.fechaInicio = this.$moment().format("YYYY-MM-01");
                this.fechaFin = this.$moment().format("YYYY-MM-DD");
                this.filters = {}
                this.filter = ''
            },
            getComprasFise() {
                this.axios.get(this.API + 'compras/fise')
                .then((response) => {
                    if(response.data.Cantidad && response.data.Total ){
                        this.comprasFise = response.data
                        this.totalCompraFise = response.data.map( item => item.Importe).reduce((sumaTotal, actual) => sumaTotal + actual)
                    }
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            },
            validaRetiro(x){
                this.capturaImp = parseInt(x);
            },
            setAlmacen (obj) {
                this.almacen = obj;
            },
            focusAutocomplete (ref) {
                this.$refs[ref].handleDoubleClick()
            },
            getRetiros() {
                this.axios.get(this.API + 'retiros', {
                    params : {
                        fechaInicio: this.fechaInicio,
                        fechaFin: this.fechaFin
                    }
                })
                .then((response) => {
                   this.retiros = response.data
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            },
            guardarPago (evt) {
                evt.preventDefault()
                if (!this.almacen.IdAlmacen) {
                    this.$toasted.show('Debe seleccionar un almacen')
                    return
                }
                this.axios.post(this.API + 'retiro/fise', {
                    Concepto: this.conceptoRetiro,
                    Importe: this.importeRetiro,
                    IdAlmacen: this.almacen.IdAlmacen
                })
                .then((response) => {
                    if (response.data.success) {
                        this.$toasted.success(response.data.msg)
                        this.getRetiros()
                        this.conceptoRetiro = '';
                        this.importeRetiro = '';
                        this.loadFirstAlmacen();
                        this.getVentasFise();
                    }
                    else {
                        this.$toasted.show(response.data.msg);
                    }
                    
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            }
        }
    }
</script>
<style>

</style>
