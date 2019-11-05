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

                    <b>VENTAS FISE</b><br /><br />
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
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
                <b-row class="mb-1">
                    <b-col>

                    <b>COMPRAS FISE</b><br /><br />
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
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
                
                <br>
                <b-row class="mb-1">
                    <b-col>
                        <br>
                        <b-form-input
                            required
                            id="input-fecha"
                            type="date"
                            placeholder=""></b-form-input>
                        <div class="floast-right">
                            <b-btn size="sm" variant="success" type="submit">
                                Retirar
                            </b-btn>
                            <b-btn size="sm" variant="secondary" @click="show=false">
                                Cerrar
                            </b-btn>
                        </div>
                        <hr>
                        <br><br>
                        <table class="table b-table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha Retiro</th>
                                    <th>Concepto</th>
                                    <th>Importe</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(pago, index) in pagos" :key="index">
                                    <td>{{ pago.IdFiseRetiro }}</td>
                                    <td>{{ pago.FechaDoc }}</td>
                                    <td>{{ pago.Concepto }}</td>
                                    <td>{{ pago.Importe }}</td>
                                    <td><b-btn size="sm" variant="success" :href="'/imprimir/pagocredito.php?idCajaBanco=' + pago.IdCajaBanco" target="_blank">Imprimir</b-btn></td>
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
            this.initForm()
            this.getVendedor()
        },
        data () {
            return {
                API: config.API,
                documentos: [],
                pagos: [],
                cajaBanco: {},
                vendedor: {},
                show: false
            }
        },
        methods: {
            initForm () {
                this.vendedor = {}
                this.documentos = []
                this.pagos = []
                this.cajaBanco = {
                    FechaDoc: this.$moment().format("YYYY-MM-DD")
                }
                // this.$refs.inputCliente.setValue('')
            },
            getVendedor () {
                let usuario = sessionStorage.getItem('User')
                this.axios.get(this.API + 'usuarios/' + usuario)
                .then((response) => {
                    this.setVendedor(response.data)
                    this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
                    this.$refs.inputVendedor.$refs.input.disabled = true
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            },
            setVendedor (obj) {
                this.vendedor = obj
            },
            focusAutocomplete (ref) {
                this.$refs[ref].handleDoubleClick()
            },
            cargarDeuda (cliente) {
                this.cajaBanco.cliente = cliente

                this.axios.get(this.API + 'cliente/deudas?idCliente=' + cliente.IdCliente)
                .then((response) => {
                   this.documentos = response.data
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })

                this.cargarPagos(cliente)
            },
            cargarPagos (cliente) {
                let usuario = sessionStorage.getItem('User')
                this.axios.get(this.API + 'cliente/pagos?idCliente=' + cliente.IdCliente)
                .then((response) => {
                   this.pagos = response.data
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            },
            guardarPago (evt) {
                evt.preventDefault()
                let importe = 0
                this.documentos.map(doc => {
                    if (doc.aplicar > 0) {
                        importe += doc.aplicar
                    }
                })

                if(importe === 0) {
                    this.$toasted.show('Error: no ha ingresado ningun importe');
                }

                this.axios.post(this.API + 'cliente/deudas/pagar', {
                    cajaBanco: this.cajaBanco,
                    documentos: this.documentos,
                    vendedor: this.vendedor
                })
                .then((response) => {
                    console.log(response.data)
                    this.$toasted.show('Se pagó la deuda del cliente.')
                    this.show = false
                    this.initForm()
                    window.open('/imprimir/pagocredito.php?idCajaBanco=' + response.data.idCajaBanco)
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
