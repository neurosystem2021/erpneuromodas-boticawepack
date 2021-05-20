<template>
    <div>
        <b-button @click="show=true" variant="outline-primary">Pago Deuda</b-button>
        
        <b-form @submit="guardarPago">    
            <b-modal id="modalConfigVenta" 
                title="Cobrar deuda cliente" 
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">
                
                <b-row class="mb-1">
                    <b-col><label for="input-cliente">Cliente:</label>
                        <b-input-group>
                            <autocomplete
                                required="required"
                                ref="inputCliente"
                                :url="API + 'clientes'"
                                anchor="ClienteDniRuc"
                                label="writer"
                                placeholder=""
                                :onInput="(val) => newDniRuc = val"
                                :on-select="cargarDeuda" 
                                :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                                :classes="{input: 'form-control'}">
                            </autocomplete>
                        </b-input-group>
                    </b-col>
                </b-row>
                <b-row class="mb-1">
                    <b-col><label for="input-fecha">Fecha:</label>
                        <b-form-input v-model="cajaBanco.FechaDoc"
                            required
                            id="input-fecha"
                            type="date"
                            placeholder=""></b-form-input>
                    </b-col>
                    <b-col>
                        <b-form-group>
                            <label>Vendedor:</label>
                            <autocomplete
                                required
                                ref="inputVendedor"
                                :url="API + 'usuarios'"
                                anchor="NombreUsuario"
                                label="writer"
                                :on-select="setVendedor" 
                                :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                                :classes="{input: 'form-control'}">
                            </autocomplete>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row class="mb-1">
                    <b-col><label for="input-concepto">Concepto:</label>
                        <b-form-input v-model="cajaBanco.Concepto"
                            id="input-concepto"
                            required
                            type="text"
                            placeholder=""></b-form-input>
                    </b-col>
                </b-row>
                <br>
                <b-row class="mb-1">
                    <b-col>
                        <table class="table b-table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>FechaVen</th>
                                    <th>Tipo</th>
                                    <th>Correlativo</th>
                                    <th>Total</th>
                                    <th>Aplicado</th>
                                    <th>Apli. Nota</th>
                                    <th>Saldo</th>
                                    <th>Aplicar</th>
                                </tr>
                            </thead>
                            <tbody v-for="(item, index) in documentos" :key="index">
                                <tr class="table-info">
                                    <td class="text-center">{{ item.IdDocVenta }}</td>
                                    <td>{{ item.FechaDoc }}</td>
                                    <td>{{ item.FechaCredito }}</td>
                                    <td v-if="item.IdTipoDoc==6">FACTURA ELEC.</td>
                                    <td v-else-if="item.IdTipoDoc==7">BOLETA ELEC.</td>
                                    <td v-else>OTRO</td>
                                    <td>{{ item.Correlativo }}</td>
                                    <td>{{ (item.Total).toFixed(2) }}</td>
                                    <td  class="text-center">{{ (item.Aplicado).toFixed(2) }}</td>
                                    <td>{{(item.NotaCreApli).toFixed(2)}}</td>
                                    <td>{{ (item.Saldo).toFixed(2) }}</td>
                                    <td>
                                        <b-form-input v-model="item.aplicar"
                                            type="number"
                                            step="0.01"
                                            min="0"

                                            placeholder="0"></b-form-input>
                                    </td>   
                                </tr>
                                <tr v-for="(item2, index2) in item.NotasCredito" :key="index2" class="h6 small" >
                                    <td class="text-right">{{ item2.IdDocVenta }}</td>
                                    <td>{{ item2.FechaDoc }}</td>
                                    <td></td>
                                    <td>NOTA DE CREDITO ELEC.</td>
                                    <td>{{ item2.Serie+'-'+item2.Numero }}</td>
                                    <td>{{ (item2.Total).toFixed(2) }}</td>
                                </tr>   
                                 <br> 
                            </tbody>
                        </table>
                        <br>
                        <div class="float-right">
                            <b-btn size="sm" variant="success" type="submit">
                                Guardar
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
                                    <th>Fecha Pago</th>
                                    <th>Concepto</th>
                                    <th>Importe</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(pago, index) in pagos" :key="index">
                                    <td>{{ pago.IdCajaBanco }}</td>
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
                this.$refs.inputCliente.setValue('')
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
                    response.data.map(data=>data.NotasCredito=JSON.parse(data.NotasCredito) )
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
                    this.isLoading = false;
                    return;
                }

                for (let index = 0; index < this.documentos.length; index++) {
                    if (this.documentos[index].aplicar>this.documentos[index].Saldo){
                        this.isLoading = false;
                        this.$toasted.show('Error: No puede ingresar un monto mayor al Saldo');
                        return;
                    }
                    
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
