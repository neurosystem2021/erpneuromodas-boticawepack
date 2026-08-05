<template>
    <div>
        <b-button @click="show=true" variant="primary" class="btn-pos btn-accion"><i class="fas fa-money-bill-wave mr-2"></i>Pago Deuda</b-button>
        
        <b-form @submit="guardarPago">    
            <b-modal id="modalConfigVenta"
                size="lg" centered v-model="show"
                modal-class="debt-payment-modal"
                dialog-class="debt-payment-dialog"
                content-class="debt-payment-content">
                <template slot="modal-title">
                    <span class="debt-title-icon"><i class="fas fa-hand-holding-usd"></i></span>
                    <span class="debt-title-copy">
                        <small>GESTIÓN DE CUENTAS POR COBRAR</small>
                        <strong>Cobrar deuda del cliente</strong>
                    </span>
                </template>

                <div class="debt-modal-intro">
                    <div>
                        <span><i class="fas fa-shield-alt"></i></span>
                        <div>
                            <strong>Registro seguro de cobranza</strong>
                            <small>Selecciona un cliente y distribuye el importe entre sus documentos pendientes.</small>
                        </div>
                    </div>
                    <div class="debt-stats">
                        <span><b>{{ documentos.length }}</b><small>Documentos</small></span>
                        <span><b>{{ pagos.length }}</b><small>Pagos</small></span>
                    </div>
                </div>

                <div class="debt-form-card">
                <b-row class="mb-1">
                    <b-col><label for="input-cliente"><i class="fas fa-user"></i> Cliente</label>
                        <b-input-group>
                            <autocomplete
                                required="required"
                                ref="inputCliente"
                                :url="API + 'clientes'"
                                anchor="ClienteDniRuc"
                                label="writer"
                                placeholder="Buscar por nombre o documento"
                                :onInput="(val) => newDniRuc = val"
                                :on-select="cargarDeuda" 
                                :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                                :classes="{input: 'form-control'}">
                            </autocomplete>
                        </b-input-group>
                    </b-col>
                </b-row>
                <b-row class="mb-1">
                    <b-col cols="12" md="6"><label for="input-fecha"><i class="fas fa-calendar-alt"></i> Fecha</label>
                        <b-form-input v-model="cajaBanco.FechaDoc"
                            required
                            id="input-fecha"
                            type="date"
                            placeholder=""></b-form-input>
                    </b-col>
                    <b-col cols="12" md="6">
                        <b-form-group>
                            <label><i class="fas fa-user-tie"></i> Vendedor</label>
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
                    <b-col><label for="input-concepto"><i class="fas fa-file-signature"></i> Concepto</label>
                        <b-form-input v-model="cajaBanco.Concepto"
                            id="input-concepto"
                            required
                            type="text"
                            placeholder="Detalle o referencia del pago"></b-form-input>
                    </b-col>
                </b-row>
                </div>

                <b-row class="mb-1 debt-tables">
                    <b-col>
                        <div class="debt-section-heading">
                            <span><i class="fas fa-file-invoice-dollar"></i></span>
                            <div><strong>Documentos pendientes</strong><small>Ingresa cuánto aplicar a cada deuda</small></div>
                        </div>
                        <div class="table-responsive debt-table-wrap">
                        <table class="table b-table table-sm debt-table debt-documents-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>Vencimiento</th>
                                    <th>Correlativo</th>
                                    <th>Total</th>
                                    <th>Aplicado</th>
                                    <th>Saldo</th>
                                    <th>Aplicar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in documentos" :key="index">
                                    <td>{{ item.IdDocVenta }}</td>
                                    <td>{{ item.FechaDoc }}</td>
                                    <td>{{ item.FechaCredito }}</td>
                                    <td>{{ item.Correlativo }}</td>
                                    <td>{{ item.Total }}</td>
                                    <td class="text-center">{{ item.Aplicado }}</td>
                                    <td>{{ item.Saldo }}</td>
                                    <td>
                                        <input v-model="item.aplicar"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            class="form-control"
                                            placeholder="0"/>
                                     </td>
                                 </tr>
                                <tr v-if="!documentos.length" class="debt-empty-row">
                                    <td colspan="8">
                                        <i class="fas fa-search-dollar"></i>
                                        <b>Selecciona un cliente</b>
                                        <small>Sus documentos pendientes aparecerán aquí.</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </div>

                        <div class="debt-section-heading debt-history-heading">
                            <span><i class="fas fa-history"></i></span>
                            <div><strong>Historial de pagos</strong><small>Movimientos registrados para el cliente</small></div>
                        </div>
                        <div class="table-responsive debt-table-wrap">
                        <table class="table b-table table-sm debt-table debt-history-table">
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
                                    <td><b-btn size="sm" variant="success" class="debt-print-button" :href="'/imprimir/pagocredito.php?idCajaBanco=' + pago.IdCajaBanco" target="_blank"><i class="fas fa-print"></i> Imprimir</b-btn></td>
                                </tr>
                                <tr v-if="!pagos.length" class="debt-empty-row debt-empty-compact">
                                    <td colspan="5"><i class="fas fa-receipt"></i><b>Sin pagos registrados</b></td>
                                </tr>
                            </tbody>
                        </table>
                        </div>
                    </b-col>
                </b-row>
                <div slot="modal-footer" class="debt-modal-actions">
                    <b-btn variant="secondary" @click="show=false">
                        <i class="fas fa-times"></i> Cerrar
                    </b-btn>
                    <b-btn variant="success" type="submit">
                        <i class="fas fa-save"></i> Guardar pago
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
                    // this.$refs.inputVendedor.$refs.input.disabled = true
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
                    // window.open('/imprimir/pagocredito.php?idCajaBanco=' + response.data.idCajaBanco)
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

.btn-accion{
    background:#12B8D0 !important;
    border:1px solid #12B8D0 !important;
    color:#fff !important;
    font-weight: bold;
}

.btn-accion:hover{
    background:#0DA6BC !important;
    border-color:#0DA6BC !important;
}
</style>
