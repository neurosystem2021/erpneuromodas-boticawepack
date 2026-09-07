<template>
    <div class="proforma-jobi-wrap">
        <b-button ref="triggerBtn" @click="openModal" variant="info" class="jobi-btn-action">Ver Proformas</b-button>

        <b-form ref="formProformaModal">
            <b-modal id="modalListaProformas"
                container="body"
                append-to-body
                title="Buscar Proforma"
                size="xl" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark"
                hide-header-close
                content-class="pj-modal-content"
                auto-focus="false"
                @hidden="resetFocus">

                <template slot="modal-header">
                    <div class="pj-modal-header">
                        <div class="pj-modal-header-icon"><i class="fas fa-file-invoice"></i></div>
                        <div class="pj-modal-header-text">
                            <div class="pj-modal-header-title">Buscar Proforma</div>
                            <div class="pj-modal-header-sub">{{ totalRows }} proforma(s) registrada(s)</div>
                        </div>
                        <button type="button" class="pj-modal-close" @click="show=false" aria-label="Cerrar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </template>

                <div class="pj-search-bar">
                    <span class="pj-search-icon"><i class="fas fa-search"></i></span>
                    <b-form-input
                        v-model="filter"
                        placeholder="Buscar por cliente, número o documento..."
                        @input="currentPage = 1"
                        class="pj-search-input" />
                </div>

                <div class="pj-table-shell">
                    <b-table
                        v-if="show"
                        id="table-proformas"
                        hover
                        :busy.sync="isBusy"
                        :filter="filter"
                        :items="myProvider"
                        :fields="fields"
                        :per-page="perPage"
                        :current-page="currentPage"
                        show-empty
                        empty-text="No se encontraron proformas"
                        empty-filtered-text="No hay resultados para tu búsqueda"
                        class="pj-table">

                        <template slot="table-busy">
                            <div class="pj-busy">
                                <b-spinner small class="mr-2"></b-spinner>
                                Cargando proformas...
                            </div>
                        </template>

                        <template slot="IdProforma" slot-scope="data">
                            <span class="pj-id-pill">#{{ data.item.IdProforma }}</span>
                        </template>

                        <template slot="Numero" slot-scope="data">
                            <span class="pj-numero">{{ data.item.Anio }}-{{ data.item.Numero }}</span>
                        </template>

                        <template slot="Cliente" slot-scope="data">
                            <div class="pj-cliente-cell">
                                <span class="pj-cliente-avatar"><i class="fas fa-user"></i></span>
                                <span class="pj-cliente-name">{{ data.item.Cliente }}</span>
                            </div>
                        </template>

                        <template slot="DniRuc" slot-scope="data">
                            <span class="pj-doc-badge">{{ data.item.DniRuc }}</span>
                        </template>

                        <template slot="FechaReg" slot-scope="data">
                            <span class="pj-fecha"><i class="far fa-calendar-alt"></i> {{ data.item.FechaReg }}</span>
                        </template>

                        <template slot="UsuarioReg" slot-scope="data">
                            <span class="pj-usuario">{{ data.item.UsuarioReg }}</span>
                        </template>

                        <template slot="verTicket" slot-scope="data">
                            <b-button
                                :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma"
                                target="_blank"
                                class="pj-btn-ticket">
                                <i class="fas fa-receipt"></i>
                                <span>Ver Ticket</span>
                            </b-button>
                        </template>

                    </b-table>
                </div>

                <div class="pj-pagination-row">
                    <div class="pj-pagination-info">
                        Mostrando página {{ currentPage }} de {{ Math.max(1, Math.ceil(totalRows / perPage)) }}
                    </div>
                    <b-pagination
                        :total-rows="totalRows"
                        :per-page="perPage"
                        v-model="currentPage"
                        class="pj-pagination"
                        align="right" />
                </div>

                <div slot="modal-footer" class="pj-modal-footer">
                    <b-btn size="sm" class="pj-btn-cerrar" @click="closeModal">
                        <i class="fas fa-times"></i> Cerrar
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
            this.$nextTick(() => {
                try {
                    const el = document.getElementById('modalListaProformas')
                    if (el && el.parentNode !== document.body) document.body.appendChild(el)
                } catch (e) {
                }
            })
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
                fields: [
                    { key: 'IdProforma', label: 'Id' },
                    { key: 'Anio', label: 'Año', class: 'text-center' },
                    { key: 'Numero', label: 'N°' },
                    { key: 'Cliente', label: 'Cliente' },
                    { key: 'DniRuc', label: 'Documento' },
                    { key: 'FechaReg', label: 'Fecha' },
                    { key: 'UsuarioReg', label: 'Empleado' },
                    { key: 'verTicket', label: '' }
                ],
                totalRows: 50,
                perPage: 10,
                currentPage: 1
            }
        },
        props: ['idAlmacen'],
        methods: {
            ...mapActions({
                add         : 'agregarProducto',
                obtenerStok : 'obtenerStock',
                limpiarCarrito: 'limpiarCarrito'
            }),
            focusTrigger () {
                this.$nextTick(() => {
                    const trigger = this.$el && this.$el.querySelector('.jobi-btn-action, button')
                    if (trigger) {
                        trigger.focus()
                    }
                })
            },
            openModal () {
                this.filter = ''
                this.currentPage = 1
                this.show = true
            },
            closeModal () {
                const trigger = this.$el && this.$el.querySelector('.jobi-btn-action, button')
                if (trigger) {
                    trigger.focus()
                }
                this.show = false
            },
            resetFocus () {
                this.focusTrigger()
            },
            selectProformas (proforma) {
                this.axios.get(this.API + 'proformas/detalle', {
                    params: {
                        idProforma: proforma.IdProforma
                    }
                }).then((response) => {
                    response.data.map((item) => {
                        this.add([item.IdProducto, () => {
                            this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 })
                        }, { precio: item.Precio, cantidad: item.Cantidad, proforma:true } ])

                        this.filter = ''
                        this.show = false
                    })
                    proforma.productos = response.data

                    this.$emit('onSelectProforma', proforma)
                    this.limpiarCarrito()
                })
            },
            myProvider (ctx) {
                let promise = this.axios.get(this.API + 'proformas?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ctx.filter)

                return promise.then((result) => {
                this.items = result.data
                return(this.items)
                }).catch(error => {
                return []
                })
            }
        }
    }
</script>
<style>
/* =========================================================
   JoBi-style redesign — ProformaLista
   Reutiliza la paleta del módulo POS (navy / teal / blue /
   purple / green / orange). El modal de BootstrapVue se monta
   fuera del árbol de .pos-jobi (vía teleport a <body>), por eso
   las variables CSS se redefinen aquí sobre #modalProformas.
   =========================================================*/

#modalListaProformas {
    --jobi-navy: #00666A;
    --jobi-navy-2: #00666A;
    --jobi-teal: #0F9B8E;
    --jobi-blue: #2F6FED;
    --jobi-purple: #7C3AED;
    --jobi-green: #17A554;
    --jobi-orange: #F5A623;
    padding-right: 0px !important;
}

#modalListaProformas .modal-dialog {
    max-width: 1280px !important;
    width: 95vw !important;
    margin: 2.5vh auto !important;
}

#modalListaProformas .modal-content {
    min-height: 82vh;
}

#modalListaProformas .pj-modal-content {
    border-radius: 18px !important;
    border: none !important;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(11,42,74,.35);
}

#modalProformas .modal-header {
    background: linear-gradient(135deg, var(--jobi-navy) 0%, var(--jobi-navy-2) 100%) !important;
    border: none !important;
    padding: 0 !important;
}

#modalProformas .modal-body {
    overflow-x: auto;
    background: #EEF3F8;
    padding: 24px 28px !important;
    display: flex;
    flex-direction: column;
    flex: 1 1 auto;
}

#modalProformas .modal-footer {
    background: #EEF3F8;
    border-top: 1px solid #dbe6f1 !important;
    padding: 12px 20px !important;
}

/* -------- Header custom -------- */
.pj-modal-header {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 16px 18px;
}
.pj-modal-header-icon {
    flex: 0 0 auto;
    width: 42px;
    height: 42px;
    border-radius: 12px;
    background: rgba(255,255,255,.14);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}
.pj-modal-header-text {
    flex: 1 1 auto;
    min-width: 0;
}
.pj-modal-header-title {
    color: #fff;
    font-weight: 800;
    font-size: 1.05rem;
    letter-spacing: .01em;
}
.pj-modal-header-sub {
    color: rgba(255,255,255,.65);
    font-size: .76rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.pj-modal-close {
    flex: 0 0 auto;
    width: 34px;
    height: 34px;
    border-radius: 999px;
    border: none;
    background: rgba(255,255,255,.14);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color .15s ease;
}
.pj-modal-close:hover {
    background: rgba(255,255,255,.28);
}

/* -------- Buscador pill -------- */
.pj-search-bar {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    border-radius: 999px;
    box-shadow: 0 4px 12px rgba(11,42,74,.10);
    padding: 4px 10px 4px 22px;
    margin-bottom: 20px;
}
.pj-search-icon {
    color: var(--jobi-navy);
    opacity: .55;
    font-size: 1.1rem;
}
.pj-search-bar .pj-search-input,
.pj-search-bar input {
    border: none !important;
    box-shadow: none !important;
    background: transparent !important;
    height: 50px;
    font-size: 1rem;
    font-weight: 600;
    color: var(--jobi-navy);
}
.pj-search-bar .pj-search-input:focus {
    outline: none;
}

/* -------- Tabla -------- */
.pj-table-shell {
    background: #fff;
    border-radius: 14px;
    padding: 6px;
    box-shadow: 0 6px 18px rgba(11,42,74,.08);
}

.pj-table thead th {
    background: var(--jobi-navy) !important;
    color: #fff !important;
    border: none !important;
    text-transform: uppercase;
    font-size: .70rem;
    letter-spacing: .04em;
    font-weight: 800;
    vertical-align: middle;
}
.pj-table thead th:first-child { border-radius: 10px 0 0 10px; }
.pj-table thead th:last-child  { border-radius: 0 10px 10px 0; }

.pj-table tbody tr {
    transition: background-color .15s ease;
}
.pj-table tbody tr:hover {
    background: #f5f9ff !important;
}
.pj-table tbody td {
    vertical-align: middle;
    border-color: #eef2f7 !important;
}

.pj-busy {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 0;
    color: var(--jobi-navy);
    font-weight: 700;
}

.pj-id-pill {
    display: inline-block;
    background: #edf2f7;
    color: #526b86;
    font-weight: 800;
    font-size: .74rem;
    padding: 3px 10px;
    border-radius: 999px;
}

.pj-numero {
    font-weight: 800;
    color: var(--jobi-navy);
}

.pj-cliente-cell {
    display: flex;
    align-items: center;
    gap: 8px;
}
.pj-cliente-avatar {
    flex: 0 0 auto;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: var(--jobi-purple);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .72rem;
}
.pj-cliente-name {
    font-weight: 700;
    color: var(--jobi-navy);
}

.pj-doc-badge {
    display: inline-block;
    background: #eaf3fc;
    color: var(--jobi-blue);
    font-weight: 700;
    font-size: .78rem;
    padding: 3px 10px;
    border-radius: 8px;
}

.pj-fecha {
    color: #4d6a8a;
    font-weight: 600;
    font-size: .82rem;
}
.pj-fecha i {
    margin-right: 4px;
    opacity: .7;
}

.pj-usuario {
    color: #4d6a8a;
    font-weight: 600;
}

.pj-btn-ticket {
    background: var(--jobi-teal) !important;
    border: none !important;
    color: #fff !important;
    border-radius: 999px !important;
    font-weight: 800;
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: 7px 14px !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 5px 12px rgba(15,155,142,.30);
    transition: box-shadow .15s ease, background-color .15s ease;
}
.pj-btn-ticket:hover {
    background: #0c8a7e !important;
    color: #fff !important;
    box-shadow: 0 7px 16px rgba(15,155,142,.40);
}

/* -------- Paginación -------- */
.pj-pagination-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 14px;
}
.pj-pagination-info {
    font-size: .78rem;
    color: #6b85a3;
    font-weight: 600;
}
.pj-pagination .page-link {
    border: none !important;
    color: var(--jobi-navy) !important;
    font-weight: 700;
    border-radius: 8px !important;
    margin: 0 2px;
}
.pj-pagination .page-item.active .page-link {
    background: var(--jobi-blue) !important;
    color: #fff !important;
    box-shadow: 0 4px 10px rgba(47,111,237,.30);
}

/* -------- Footer -------- */
.pj-modal-footer {
    display: flex;
    justify-content: flex-end;
    width: 100%;
}
.pj-btn-cerrar {
    background: #edf2f7 !important;
    border: none !important;
    color: #526b86 !important;
    border-radius: 999px !important;
    font-weight: 800;
    text-transform: uppercase;
    font-size: .74rem;
    letter-spacing: .03em;
    padding: 8px 18px !important;
}
.pj-btn-cerrar:hover {
    background: #dfe8f2 !important;
    color: var(--jobi-navy) !important;
}
</style>