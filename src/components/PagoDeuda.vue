<template>
    <div class="pd-jobi">
        <div class="pd-trigger-wrap">
            <b-button ref="triggerBtn" @click="openModal" variant="primary" class="jobi-btn-action pd-trigger-btn">
                <span>Pago Deuda</span>
            </b-button>
        </div>

        <b-form @submit="guardarPago">
            <b-modal id="modalPagoDeuda"
                container="body"
                append-to-body
                title="Cobrar deuda cliente"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark"
                hide-footer
                content-class="pd-modal-content"
                auto-focus="false"
                @hidden="resetFocus">

                <!-- ================= CABECERA ================= -->
                <div class="pd-panel pd-panel-header">
                    <b-row class="align-items-stretch pd-header-row">
                        <!-- Columna de campos principales -->
                        <b-col md="7" class="pd-header-col">
                            <b-row>
                                <b-col md="12">
                                    <b-form-group class="pd-field pd-field-cliente mb-2">
                                        <label><i class="fas fa-user pd-label-icon"></i> Cliente</label>
                                        <b-input-group class="pd-cliente-group">
                                            <span class="pd-cliente-pin"><i class="fas fa-id-card"></i></span>
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
                                    </b-form-group>
                                </b-col>
                                <b-col md="6">
                                    <b-form-group class="pd-field mb-2">
                                        <label><i class="fas fa-calendar-day pd-label-icon"></i> Fecha</label>
                                        <b-form-input v-model="cajaBanco.FechaDoc"
                                            required
                                            id="input-fecha"
                                            type="date"
                                            placeholder=""></b-form-input>
                                    </b-form-group>
                                </b-col>
                                <b-col md="6">
                                    <b-form-group class="pd-field mb-2">
                                        <label><i class="fas fa-user-tie pd-label-icon"></i> Vendedor</label>
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
                                <b-col md="12">
                                    <b-form-group class="pd-field mb-0">
                                        <label><i class="fas fa-comment-alt pd-label-icon"></i> Concepto</label>
                                        <b-form-input v-model="cajaBanco.Concepto"
                                            id="input-concepto"
                                            required
                                            type="text"
                                            placeholder=""></b-form-input>
                                    </b-form-group>
                                </b-col>
                            </b-row>
                        </b-col>

                        <!-- Columna derecha: resumen -->
                        <b-col md="5" class="pd-header-col d-flex align-items-stretch">
                            <div class="pd-stat-card pd-stat-total w-100 my-2">
                                <div class="pd-stat-icon"><i class="fas fa-hand-holding-usd"></i></div>
                                <div class="pd-stat-body">
                                    <div class="pd-stat-label">Total a aplicar</div>
                                    <div class="big-text">S/ {{ totalAplicar.toFixed(2) }}</div>
                                    <div class="pd-stat-sub" v-if="cajaBanco.cliente">{{ cajaBanco.cliente.Cliente }}</div>
                                    <div class="pd-stat-sub" v-else>Seleccione un cliente</div>
                                </div>
                            </div>
                        </b-col>
                    </b-row>
                </div>

                <!-- ================= DOCUMENTOS PENDIENTES ================= -->
                <div class="pd-panel pd-table-panel">
                    <div class="pd-panel-title">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Documentos pendientes</span>
                    </div>
                    <div class="pd-table-scroll">
                        <table class="table b-table table-sm pd-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha</th>
                                    <th>FechaVen</th>
                                    <th>Tipo</th>
                                    <th>Correlativo</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">Aplicado</th>
                                    <th class="text-right">Apli. Nota</th>
                                    <th class="text-right">Saldo</th>
                                    <th class="text-center">Aplicar</th>
                                    <th class="text-center">Cuenta</th>
                                </tr>
                            </thead>

                            <tbody v-for="(item, index) in documentos" :key="index">
                                <tr class="pd-row-doc">
                                    <td class="text-center"><span class="pd-id-badge">{{ item.IdDocVenta }}</span></td>
                                    <td>{{ item.FechaDoc }}</td>
                                    <td>{{ item.FechaCredito }}</td>
                                    <td v-if="item.IdTipoDoc==6"><span class="pd-tipo-tag pd-tipo-factura">FACTURA ELEC.</span></td>
                                    <td v-else-if="item.IdTipoDoc==7"><span class="pd-tipo-tag pd-tipo-boleta">BOLETA ELEC.</span></td>
                                    <td v-else><span class="pd-tipo-tag pd-tipo-otro">OTRO</span></td>
                                    <td>{{ item.Correlativo }}</td>
                                    <td class="text-right">{{ (item.Total).toFixed(2) }}</td>
                                    <td class="text-right">{{ (item.Aplicado).toFixed(2) }}</td>
                                    <td class="text-right">{{ (item.NotaCreApli).toFixed(2) }}</td>
                                    <td class="text-right"><b class="pd-saldo">{{ (item.Saldo).toFixed(2) }}</b></td>
                                    <td>
                                        <b-input-group prepend="S/" class="pd-input-aplicar">
                                            <input v-model="item.aplicar"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                class="form-control"
                                                placeholder="0"/>
                                        </b-input-group>
                                    </td>
                                    <td>
                                        <b-form-select v-model="item.cuenta" :options="cuentas" class="pd-select-cuenta" size="sm"/>
                                    </td>
                                </tr>
                                <tr v-for="(item2, index2) in item.NotasCredito" :key="'nc'+index+'-'+index2" class="pd-row-nota">
                                    <td class="text-center">{{ item2.IdDocVenta }}</td>
                                    <td>{{ item2.FechaDoc }}</td>
                                    <td></td>
                                    <td><span class="pd-tipo-tag pd-tipo-nota"><i class="fas fa-minus-circle"></i> NOTA DE CREDITO</span></td>
                                    <td>{{ item2.Serie+'-'+item2.Numero }}</td>
                                    <td class="text-right">{{ (item2.Total).toFixed(2) }}</td>
                                    <td colspan="5"></td>
                                </tr>
                            </tbody>
                            <tbody v-if="!documentos.length">
                                <tr>
                                    <td colspan="11" class="text-center pd-empty">
                                        <i class="fas fa-inbox"></i> Sin documentos pendientes
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="pd-actions-row">
                        <b-btn class="pd-btn-guardar" type="button" @click="onGuardarClick">
                            <i class="fas fa-save"></i>
                            <span>Guardar</span>
                        </b-btn>
                        <b-btn class="pd-btn-cerrar" @click="closeModal">
                            <i class="fas fa-times"></i>
                            <span>Cerrar</span>
                        </b-btn>
                    </div>
                </div>

                <!-- ================= HISTORIAL DE PAGOS ================= -->
                <div class="pd-panel pd-table-panel">
                    <div class="pd-panel-title pd-panel-title-alt">
                        <i class="fas fa-history"></i>
                        <span>Historial de pagos</span>
                    </div>
                    <div class="pd-table-scroll">
                        <table class="table b-table table-sm pd-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Fecha Pago</th>
                                    <th>Cuenta</th>
                                    <th>Concepto</th>
                                    <th class="text-right">Importe</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(pago, index) in pagos" :key="index">
                                    <td><span class="pd-id-badge pd-id-badge-alt">{{ pago.IdCajaBanco }}</span></td>
                                    <td>{{ pago.FechaDoc }}</td>
                                    <td>{{ pago.Cuenta }}</td>
                                    <td>{{ pago.Concepto }}</td>
                                    <td class="text-right"><b>{{ pago.Importe.toFixed(2) }}</b></td>
                                    <td class="text-center">
                                        <b-btn size="sm" class="pd-btn-imprimir-mini" :href="'/imprimir/pagocredito.php?idCajaBanco=' + pago.IdCajaBanco" target="_blank">
                                            <i class="fas fa-print"></i> Imprimir
                                        </b-btn>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-if="!pagos.length">
                                <tr>
                                    <td colspan="6" class="text-center pd-empty">
                                        <i class="fas fa-receipt"></i> Sin pagos registrados
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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
            this.$nextTick(() => {
                try {
                    const el = document.getElementById('modalPagoDeuda')
                    if (el && el.parentNode !== document.body) document.body.appendChild(el)
                } catch (e) {
                    // ignore
                }
            })
        },
        data () {
            return {
                API: config.API,
                documentos: [],
                pagos: [],
                cajaBanco: {},
                vendedor: {},
                show: false,
                cuentas:[{ value: null, text: '-- SELECCIONE CUENTA --' }],
            }
        },
        computed: {
            totalAplicar () {
                let total = 0
                this.documentos.forEach(doc => {
                    if (doc.aplicar > 0) {
                        total += parseFloat(doc.aplicar)
                    }
                })
                return total
            }
        },
        watch: {
            show (val) {
                if (!val) {
                    this.enableQuickButtons()
                }
            }
        },
        methods: {
            focusTrigger () {
                this.$nextTick(() => {
                    const trigger = this.$el && this.$el.querySelector('.pd-trigger-btn, .jobi-btn-action, button')
                    if (trigger) {
                        trigger.focus()
                    }
                })
            },
            openModal () {
                this.show = true
                this.$nextTick(() => {
                    this.runModalDiagnostics()
                })
            },
            closeModal () {
                if (this.$el) {
                    const trigger = this.$el.querySelector('.pd-trigger-btn, .jobi-btn-action, button')
                    if (trigger) {
                        trigger.focus()
                    }
                }
                this.show = false
            },
            resetFocus () {
                this.focusTrigger()
            },

            initForm () {
                this.vendedor = {}
                this.documentos = []
                this.pagos = []
                this.cuentas=[{ value: null, text: '-- SELECCIONE CUENTA --' }]
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
                    response.data.map(data=>data.NotasCredito=JSON.parse(data.NotasCredito) )
                    this.documentos = response.data
                    this.documentos.map(d=>d.cuenta=null)
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })

                this.cargarPagos(cliente)
                this.cargarCuentas()
            },
            cargarCuentas(){
                 this.axios.get(this.API + 'cuentas')
                .then((response) => {
                   this.cuentas=[{ value: null, text: '-- SELECCIONE CUENTA --' }]
                   response.data.map(c=>{
                       this.cuentas.push({value:c.IdCuenta,text:c.Cuenta})
                       })
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
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
                console.log('guardarPago handler invoked')
                let importe = 0
                let cuentaSelec= true
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
                    if (parseFloat(this.documentos[index].aplicar)>parseFloat(this.documentos[index].Saldo)){
                        this.isLoading = false
                        this.$toasted.show('Error: No puede ingresar un monto mayor al Saldo')
                        return;
                    }
                    
                } 

                this.documentos.map(doc => {
                    if(parseFloat(doc.aplicar)>0 && doc.cuenta == null){
                        cuentaSelec = false
                    }
                });

                if(!cuentaSelec){
                        this.$toasted.show('Error: Seleccione la cuenta para completar el pago');
                        this.isLoading = false;
                        return
                }


                let cuentasId =[]
                this.cuentas.map(c=>{
                        if(c.value!==null){                        
                        cuentasId.push(c.value)
                        }
                })

                this.axios.post(this.API + 'cliente/deudas/pagar', {
                    cajaBanco: this.cajaBanco,
                    documentos: this.documentos,
                    vendedor: this.vendedor,
                    cuentas: cuentasId
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
            },

            async onGuardarClick (evt) {
                console.log('pd-btn-guardar clicked', evt)
                // Prevent confirmation if concept is empty
                if (!this.cajaBanco.Concepto || !this.cajaBanco.Concepto.toString().trim()) {
                    this.$toasted.show('Ingrese el concepto del pago')
                    this.$nextTick(() => { if (this.$refs.inputConcepto && this.$refs.inputConcepto.focus) this.$refs.inputConcepto.focus() })
                    return
                }
                try {
                    const result = await this.$swal.fire({
                        icon: 'question',
                        title: 'Confirmar pago',
                        text: '¿Está seguro que desea efectuar el pago de la deuda?',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, efectuar pago',
                        cancelButtonText: 'Cancelar'
                    })

                    if (result && result.isConfirmed) {
                        this.guardarPago({ preventDefault () {} })
                    }
                } catch (e) {
                    console.error('confirmar pago failed', e)
                }
            },

            runModalDiagnostics () {
                try {
                    const el = document.getElementById('modalPagoDeuda') || document.querySelector('#modalPagoDeuda')
                    console.log('modal element:', el)
                    if (!el) return

                    // Visual outline to see the modal bounds
                    el.style.outline = '3px solid lime'
                    setTimeout(() => { el.style.outline = '' }, 4000)

                    // Ensure pointer-events and z-index
                    el.style.pointerEvents = 'auto'
                    el.style.zIndex = 20050

                    const backdrop = document.querySelector('.modal-backdrop')
                    console.log('backdrop:', backdrop)
                    if (backdrop) {
                        backdrop.style.zIndex = 20040
                        backdrop.style.pointerEvents = 'auto'
                        backdrop.style.outline = '2px solid rgba(255,0,0,0.4)'
                        setTimeout(() => { backdrop.style.outline = '' }, 4000)
                    }

                    // Log computed styles of modal and ancestors that may block interactions
                    const cs = window.getComputedStyle(el)
                    console.log('modal computed style pointerEvents/zIndex/position/transform/overflow:', cs.pointerEvents, cs.zIndex, cs.position, cs.transform, cs.overflow)

                    let node = el.parentNode
                    while (node && node !== document.body) {
                        const css = window.getComputedStyle(node)
                        if (css.pointerEvents === 'none' || css.overflow === 'hidden' || css.transform !== 'none' || css.position === 'relative') {
                            console.warn('Ancestor may affect modal:', node, { pointerEvents: css.pointerEvents, overflow: css.overflow, transform: css.transform, position: css.position, zIndex: css.zIndex })
                        }
                        node = node.parentNode
                    }

                    // Check for any element covering the modal (highest z-index)
                    const all = Array.from(document.querySelectorAll('body *'))
                    const modalRect = el.getBoundingClientRect()
                    const covering = all.find(a => {
                        try {
                            const r = a.getBoundingClientRect()
                            const css = window.getComputedStyle(a)
                            if (css.pointerEvents === 'none' || css.visibility === 'hidden' || css.display === 'none') return false
                            // If element covers center point of modal
                            const cx = modalRect.left + modalRect.width/2
                            const cy = modalRect.top + modalRect.height/2
                            return r.left <= cx && r.right >= cx && r.top <= cy && r.bottom >= cy && a !== el && !el.contains(a)
                        } catch(e){ return false }
                    })
                    console.log('possible covering element:', covering)
                    // Disable quick-action buttons while modal is open to avoid accidental blocking
                    this.disableQuickButtons()
                } catch (e) {
                    console.error('diagnostics error', e)
                }
            },

            disableQuickButtons () {
                try {
                    this._quickPrev = []
                    document.querySelectorAll('.pos-quick-btn').forEach(el => {
                        this._quickPrev.push({ el, prev: el.style.pointerEvents })
                        el.style.pointerEvents = 'none'
                    })
                } catch (e) {}
            },

            enableQuickButtons () {
                try {
                    if (this._quickPrev && this._quickPrev.length) {
                        this._quickPrev.forEach(item => {
                            item.el.style.pointerEvents = item.prev || ''
                        })
                        this._quickPrev = []
                    }
                } catch (e) {}
            },
        }
    }
</script>
<style>
#modalPagoDeuda .modal-dialog{
    max-width: 78% !important;
}

#modalPagoDeuda .modal-content.pd-modal-content {
    border-radius: 18px;
    overflow: hidden;
    border: none;
}

#modalPagoDeuda .modal-header {
    background: linear-gradient(135deg, #0B2A4A 0%, #123A63 100%) !important;
    border-bottom: none;
}

#modalPagoDeuda .modal-body {
    background: #EEF3F8 !important;
    padding: 18px 22px 22px;
}

.pd-jobi {
    --jobi-navy: #0B2A4A;
    --jobi-navy-2: #123A63;
    --jobi-teal: #0F9B8E;
    --jobi-blue: #2F6FED;
    --jobi-purple: #7C3AED;
    --jobi-green: #17A554;
    --jobi-orange: #F5A623;
    --jobi-pink: #F43F7E;
    --jobi-maroon: #9F1D3F;
    font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    display: inline-block;
}

.pd-trigger-btn {
    border-radius: 999px !important;
    background: var(--jobi-teal) !important;
    border: none !important;
    font-weight: 800;
    padding: 10px 20px !important;
    box-shadow: 0 8px 18px rgba(15,155,142,.30);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.pd-trigger-icon {
    font-size: 1rem;
}

/* -------- Panel header (navy, igual que el POS) -------- */
.pd-panel-header {
    background: linear-gradient(135deg, var(--jobi-navy) 0%, var(--jobi-navy-2) 100%);
    border-radius: 14px;
    padding: 16px 18px 8px;
    margin-bottom: 14px;
    color: #fff;
}

.pd-header-row {
    row-gap: 10px;
}

.pd-header-col {
    display: flex;
    flex-direction: column;
}

@media (min-width: 992px) {
    .pd-header-col + .pd-header-col {
        border-left: 1px solid rgba(255,255,255,.12);
        padding-left: 20px;
    }
}

.pd-panel-header label {
    color: rgba(255,255,255,.72);
    font-size: .70rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.pd-label-icon {
    font-size: .82em;
    opacity: .85;
}

.pd-field input,
.pd-field select,
.pd-field .form-control {
    border-radius: 10px !important;
    background: #fff !important;
    color: var(--jobi-navy);
    font-weight: 700;
    height: 40px;
    border: none !important;
    box-shadow: 0 3px 8px rgba(0,0,0,.15);
}

.pd-cliente-group {
    align-items: center;
}
.pd-cliente-pin {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255,255,255,.12);
    color: #fff;
    margin-right: 6px;
    font-size: 1rem;
}

/* -------- Tarjeta de resumen (total a aplicar) -------- */
.pd-stat-card {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: rgba(255,255,255,.10);
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 12px;
    padding: 14px 16px;
    align-self: center;
}
.pd-stat-icon {
    flex: 0 0 auto;
    width: 38px;
    height: 38px;
    border-radius: 9px;
    background: #fff;
    color: var(--jobi-navy);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    box-shadow: 0 3px 8px rgba(0,0,0,.15);
}
.pd-stat-body {
    flex: 1 1 auto;
    min-width: 0;
}
.pd-stat-label {
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: rgba(255,255,255,.65);
    font-weight: 800;
    margin-bottom: 2px;
}
.pd-stat-total .big-text {
    font-size: 34px;
    line-height: 1.1;
    color: #fff;
    font-weight: 800;
}
.pd-stat-sub {
    font-size: .78rem;
    color: rgba(255,255,255,.60);
    margin-top: 2px;
}

/* -------- Paneles de tablas -------- */
.pd-table-panel {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 6px 16px rgba(11,42,74,.08);
    padding: 14px;
    margin-bottom: 16px;
}

.pd-panel-title {
    font-weight: 800;
    text-transform: uppercase;
    font-size: .74rem;
    letter-spacing: .04em;
    color: #fff;
    background: var(--jobi-navy);
    margin: -14px -14px 12px -14px;
    padding: 10px 14px;
    border-radius: 14px 14px 0 0;
    display: flex;
    align-items: center;
    gap: 6px;
}
.pd-panel-title-alt {
    background: var(--jobi-blue);
}

.pd-table-scroll {
    overflow-x: auto;
}

.pd-table thead th {
    background: #f1f5fa !important;
    color: var(--jobi-navy) !important;
    text-transform: uppercase;
    font-size: .68rem;
    letter-spacing: .04em;
    border-top: none !important;
    border-bottom: 2px solid #e1e9f2 !important;
    white-space: nowrap;
}

.pd-table td {
    vertical-align: middle;
    font-size: .84rem;
}

.pd-row-doc {
    background: #f7fbff;
}
.pd-row-doc:hover {
    background: #eef6ff;
}

.pd-row-nota {
    background: #fff7f0;
    font-size: .78rem;
    color: #8a5a1c;
}

.pd-id-badge {
    display: inline-block;
    min-width: 30px;
    padding: 3px 8px;
    border-radius: 999px;
    background: var(--jobi-navy);
    color: #fff;
    font-weight: 800;
    font-size: .72rem;
    text-align: center;
}
.pd-id-badge-alt {
    background: var(--jobi-blue);
}

.pd-tipo-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 9px;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .02em;
    white-space: nowrap;
}
.pd-tipo-factura { background: #e7f0ff; color: var(--jobi-blue); }
.pd-tipo-boleta  { background: #e6f8ee; color: var(--jobi-green); }
.pd-tipo-otro    { background: #f1f1f1; color: #666; }
.pd-tipo-nota    { background: #fdeee0; color: #b5620a; }

.pd-saldo {
    color: var(--jobi-maroon);
}

.pd-input-aplicar {
    min-width: 120px;
}
.pd-input-aplicar .input-group-text {
    background: var(--jobi-orange) !important;
    color: #fff !important;
    border: none !important;
    font-weight: 700;
    border-radius: 8px 0 0 8px;
    font-size: .74rem;
    padding: 4px 8px;
}
.pd-input-aplicar input {
    border-radius: 0 8px 8px 0 !important;
    border: 1.5px solid #f3d9ad !important;
    text-align: right;
    font-weight: 700;
}

.pd-select-cuenta {
    min-width: 50px;
    border-radius: 8px !important;
    border: 1.5px solid #d9e4f0 !important;
    font-size: .88rem;
}

.pd-empty {
    padding: 22px 10px !important;
    color: #93a7bd;
    font-weight: 700;
    font-size: .84rem;
}
.pd-empty i {
    margin-right: 6px;
}

/* -------- Acciones (Guardar / Cerrar) -------- */
.pd-actions-row {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 14px;
}

.pd-btn-guardar,
.pd-btn-cerrar {
    border-radius: 999px !important;
    border: none !important;
    padding: 9px 22px !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: .03em;
    font-size: .78rem;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    box-shadow: 0 8px 18px rgba(11,42,74,.14);
}

.pd-btn-guardar {
    background: var(--jobi-green) !important;
    color: #fff !important;
}
.pd-btn-guardar:hover {
    transform: translateY(-1px);
}

.pd-btn-cerrar {
    background: #e4ebf3 !important;
    color: #4d6a8a !important;
    box-shadow: none;
}
.pd-btn-cerrar:hover {
    background: #d9e4f0 !important;
}

/* Ensure SweetAlert2 dialogs appear above this modal */
.swal2-container {
    z-index: 31000 !important;
}
.swal2-popup {
    z-index: 31010 !important;
}

/* Ensure toast notifications appear above modals */
.toasted,
.toasted .content,
.vue-toasted,
.v-toasted,
.toast,
.toast-container,
.toastr,
.toasted-container {
    z-index: 31100 !important;
    pointer-events: auto !important;
}

.pd-btn-imprimir-mini {
    border-radius: 999px !important;
    background: var(--jobi-blue) !important;
    border: none !important;
    color: #fff !important;
    font-weight: 700;
    font-size: .72rem;
    padding: 5px 12px !important;
}

@media (max-width: 750px) {
    #modalPagoDeuda .modal-dialog {
        max-width: 96% !important;
    }
    .pd-actions-row {
        justify-content: stretch;
    }
    .pd-btn-guardar,
    .pd-btn-cerrar {
        flex: 1 1 auto;
        justify-content: center;
    }
}

/* ========= Fix: ensure modal and its controls are on top and clickable ========= */
#modalPagoDeuda,
#modalPagoDeuda.modal {
    z-index: 20050 !important;
    pointer-events: auto !important;
}

/* Backdrop may be sibling of modal; raise it below modal */
body > .modal-backdrop {
    z-index: 20040 !important;
}

/* Ensure all interactive controls inside modal receive pointer events */
#modalPagoDeuda .pd-modal-content,
#modalPagoDeuda .pd-modal-content * {
    pointer-events: auto !important;
}

.pd-btn-guardar,
.pd-btn-cerrar {
    pointer-events: auto !important;
}
</style>
