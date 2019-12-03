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
                         <table class="table b-table table-sm">
                            <thead>
                                <tr>
                                    <th>Almacen</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(ventasFise, index) in ventasFise" :key="index">
                                    <td>{{ventasFise.Almacen}}</td>
                                    <td>{{ventasFise.Cantidad}}</td>
                                    <td>{{ventasFise.Precio}}</td>
                                    <td>{{ventasFise.Total}}</td>
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
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td></td>
                                    <td>{{comprasFise.Cantidad}}</td>
                                    <td>{{comprasFise.Precio}}</td>
                                    <td>{{comprasFise.Total}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
                <b-row  style="background: rgb(235, 97, 44);color: #fff;padding: 10px;">
                    <b-col style="text-align: right">
                        <b>Total Acumulado FISE: </b><br>
                        <b>Total Retiros: </b><br>
                        <b>Gran TOTAL: </b>
                    </b-col>
                    <b-col>
                        <b>S/ {{totalVentaFise+totalCompraFise}}</b><br>
                        <b>S/ {{totalRetiros}}</b><br>
                        <b>S/ {{totalVentaFise+totalCompraFise-totalRetiros}}</b>
                    </b-col>
                </b-row>
                
                <br>
                <b-row class="mb-1">
                    <b-col>
                        <b-form-input
                            required
                            v-model="fechaRetiro"
                            type="date"
                            placeholder=""></b-form-input>
                    </b-col>
                    <b-col>
                        <b-form-input
                            required
                            v-model="conceptoRetiro"
                            type="text"
                            placeholder="Concepto"></b-form-input>
                    </b-col>
                    <b-col>
                        <b-form-input
                            required
                            v-model="importeRetiro"
                            type="number"
                            placeholder="Importe"></b-form-input>
                    </b-col>
                    <b-col>
                        <div class="floast-right">
                            <b-btn size="sm" variant="success" type="submit">
                                Retirar
                            </b-btn>
                            <b-btn size="sm" variant="secondary" @click="show=false">
                                Cerrar
                            </b-btn>
                        </div>
                    </b-col>
                </b-row>
                <hr>
                <b-row>
                    <b-col>
                    <br><br>
                    <table class="table b-table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha Retiro</th>
                                <th>Concepto</th>
                                <th>Importe</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(ret, index) in retiros" :key="index">
                                <td>{{ ret.IdFiseRetiro }}</td>
                                <td>{{ ret.FechaDoc }}</td>
                                <td>{{ ret.Concepto }}</td>
                                <td>{{ ret.Importe }}</td>                            
                            </tr>
                            <tr style="font-weight: bold;">
                                <td colspan="3" style="text-align: center;"> Total retiros</td>
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
            this.getVentasFise()
            this.getComprasFise()
            this.getRetiros()
        },
        data () {
            return {
                API: config.API,
                fechaRetiro: this.$moment().format("YYYY-MM-DD"),
                conceptoRetiro: '',
                importeRetiro: '',
                ventasFise: [],
                comprasFise: [],
                totalVentaFise: 0,
                totalCompraFise: 0,
                show: false,
                retiros: []
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
            getVentasFise() {
                let total
                this.axios.get(this.API + 'ventas/fise')
                .then((response) => {
                    this.ventasFise = response.data
                    this.totalVentaFise = response.data.map( item => item.Total).reduce((sumaTotal, actual) => sumaTotal + actual)
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            },
            getComprasFise() {
                this.axios.get(this.API + 'compras/fise')
                .then((response) => {
                    if(response.data.Cantidad && response.data.Total ){
                        this.comprasFise = response.data
                        this.totalCompraFise = response.data.map( item => item.Total).reduce((sumaTotal, actual) => sumaTotal + actual)
                    }
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            },
            focusAutocomplete (ref) {
                this.$refs[ref].handleDoubleClick()
            },
            getRetiros() {
                this.axios.get(this.API + 'retiros')
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

                this.axios.post(this.API + 'retiro/fise', {
                    Concepto: this.conceptoRetiro,
                    Importe: this.importeRetiro
                })
                .then((response) => {
                    this.$toasted.success('Retiro registrado correctamente.')
                    this.getRetiros()
                    this.conceptoRetiro = '';
                    this.importeRetiro = '';
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
