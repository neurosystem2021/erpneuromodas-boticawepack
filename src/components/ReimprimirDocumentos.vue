<template>
    <div class="jobi-reimpresion">
    <b-button block variant="outline-primary" @click="show = true">
        <i class="fas fa-print"></i> Reimprimir Documentos
    </b-button>

        <b-modal
            id="modalReimpresion"
            v-model="show"
            title="Reimprimir Documentos"
            size="xl"
            header-bg-variant="dark"
            headerTextVariant="light"
            body-bg-variant="light"
            body-text-variant="dark"
            dialog-class="jobi-modal-dialog"
            hide-footer
            @shown="onModalShown"
        >
            <div class="jobi-filters">
                <b-form-input
                    v-model="filter"
                    placeholder="Buscar por comprobante, cliente o documento"
                    class="jobi-input jobi-input-search"
                    @input="onFilterChange"
                />
                <b-form-input
                    type="date"
                    v-model="fechaInicio"
                    class="jobi-input"
                    @change="onFilterChange"
                />
                <b-form-input
                    type="date"
                    v-model="fechaFin"
                    class="jobi-input"
                    @change="onFilterChange"
                />
                <b-button variant="outline-secondary" class="jobi-btn-clear" @click="limpiarFiltros">
                    <i class="fas fa-times"></i>
                </b-button>
            </div>

            <div class="jobi-table-wrap">
            <b-table
                ref="tablaReimpresion"
                v-if="show"
                striped hover
                :busy.sync="isBusy"
                :items="myProvider"
                :fields="fields"
                :per-page="perPage"
                :current-page="currentPage"
                class="jobi-table"
                responsive
            >
                    <template slot="NroComprobante" slot-scope="data">
                        <span class="jobi-badge">{{ data.item.NroComprobante }}</span>
                    </template>

                    <template slot="Cliente" slot-scope="data">
                        <span class="jobi-cliente" :title="data.item.Cliente">{{ data.item.Cliente }}</span>
                    </template>

                    <template slot="Total" slot-scope="data">
                        <span class="jobi-total">S/ {{ Number(data.item.Total).toFixed(2) }}</span>
                    </template>

                    <template slot="acciones" slot-scope="data">
                        <div class="jobi-acciones">
                            <a
                                class="jobi-pill-btn jobi-btn-ticket"
                                href="#"
                                @click.prevent="reimprimir(data.item.IdDocVenta)"
                                title="Reimprimir Ticket"
                            >
                                <i class="fas fa-print"></i> TICKET
                            </a>
                            <a
                                size="sm"
                                class="jobi-pill-btn jobi-btn-pdf"
                                style="color: white;"
                                @click="regenerarPDF(data.item)">
                                <i class="fas fa-file-pdf"></i> PDF
                            </a>
                            <a
                                class="jobi-pill-btn jobi-btn-comprobante"
                                target="_blank"
                                :href="'/imprimir/index.php?IdDocVenta=' + data.item.IdDocVenta + '&preview=1'"
                                title="Ver Comprobante PDF"
                            >
                                <i class="fas fa-file"></i> Comprobante
                            </a>
                        </div>
                    </template>

                    <div slot="table-busy" class="text-center my-3">
                        <b-spinner small variant="primary" /> Cargando...
                    </div>

                    <div slot="empty" class="text-center text-muted my-3">
                        No se encontraron documentos
                    </div>
                </b-table>
            </div>

            <div class="jobi-pagination-wrap">
                <b-pagination
                    :total-rows="totalRows"
                    :per-page="perPage"
                    v-model="currentPage"
                    @change="() => $refs.tablaReimpresion.refresh()"
                    align="center"
                    class="jobi-pagination"
                />
            </div>
        </b-modal>
    </div>
</template>

<script>
import config from '@/store/config.js'

export default {
    data () {
        return {
            API: config.API,
            show: false,
            filter: '',
            fechaInicio: '',
            fechaFin: '',
            isBusy: false,
            totalRows: 0,
            perPage: 10,
            currentPage: 1,
            tableKey: 0,
            filterTimeout: null,
            fields: [
                { key: 'NroComprobante', label: 'Comprobante' },
                { key: 'TipoDoc', label: 'Tipo' },
                { key: 'Cliente', label: 'Cliente' },
                { key: 'DniRuc', label: 'Documento' },
                { key: 'FechaReg', label: 'Fecha' },
                { key: 'Total', label: 'Total' },
                { key: 'acciones', label: 'Acciones' }
            ]
        }
    },
    methods: {
        async regenerarPDF(doc) {
            this.loading = true;
            try {
                const res = await this.axios.get(
                    this.API + "imprimirpdf/" + doc.IdDocVenta,
                    { responseType: 'blob' }
                );

                const url = window.URL.createObjectURL(
                    new Blob([res.data], { type: 'application/pdf' })
                );
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `${doc.NroComprobante}.pdf`);
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);

                this.$toasted.success("El PDF fue generado nuevamente", { duration: 7000 });
            } catch (err) {
                console.error(err);
                this.$toasted.error("No se pudo generar el PDF", { duration: 7000 });
            } finally {
                this.loading = false;
            }
        },
        onModalShown () {
            // Respaldo a prueba de fallos: fuerza el ancho aunque otro CSS
            // del proyecto se cargue después y pise nuestras reglas.
            this.$nextTick(() => {
                const dialog = document.querySelector('#modalReimpresion .modal-dialog')
                if (dialog) {
                    dialog.style.setProperty('max-width', '96vw', 'important')
                    dialog.style.setProperty('width', '96vw', 'important')
                    dialog.style.setProperty('margin', '1.75rem auto', 'important')
                }
            })
        },
        onFilterChange () {
            clearTimeout(this.filterTimeout)
            this.filterTimeout = setTimeout(() => {
                this.currentPage = 1
                this.$refs.tablaReimpresion.refresh()
            }, 400)
        },
        limpiarFiltros () {
            this.filter = ''
            this.fechaInicio = ''
            this.fechaFin = ''
            this.onFilterChange()
        },
        myProvider (ctx) {
    let params = new URLSearchParams({
        perPage: ctx.perPage,
        page: ctx.currentPage,
        filter: this.filter,
        fechaInicio: this.fechaInicio,
        fechaFin: this.fechaFin,
        idPuntoVenta: this.$store.state.idPuntoVenta || 0
    })

    return this.axios.get(this.API + 'ventas/reimpresion?' + params.toString())
        .then((result) => {
            this.totalRows = result.data.total
            return result.data.data
        })
        .catch((error) => {
    console.error('Error en reimpresion:', (error.response && error.response.data) || error)
    this.totalRows = 0
    return []
})
},
        reimprimir (idDocVenta) {
            const url = '/imprimir/index.php?IdDocVenta=' + idDocVenta + '&preview=1'
            const iframe = document.createElement('iframe')
            iframe.style.position = 'absolute'
            iframe.style.width = '0px'
            iframe.style.height = '0px'
            iframe.style.border = 'none'
            iframe.src = url
            iframe.onload = function () {
                iframe.contentWindow.print()
                setTimeout(() => document.body.removeChild(iframe), 1000)
            }
            document.body.appendChild(iframe)
        }
    }
}
</script>

<style>
.jobi-reimpresion {
    --jobi-navy: #1e2a4a;
    --jobi-teal: #16a3a3;
    --jobi-blue: #2563eb;
    --jobi-purple: #7c3aed;
    --jobi-green: #16a34a;
    --jobi-orange: #ea580c;
}

.jobi-btn-trigger {
    background: linear-gradient(135deg, var(--jobi-navy), var(--jobi-blue));
    border: none;
    color: #fff;
    border-radius: 999px;
    padding: 10px 16px;
    font-weight: 600;
    transition: box-shadow 0.2s ease;
}
.jobi-btn-trigger:hover {
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
}

/* --- MODAL WIDTH FIX --- */
#modalReimpresion.modal .modal-dialog.jobi-modal-dialog {
    max-width: 96vw !important;
    width: 96vw !important;
    margin: 1.75rem auto !important;
}
#modalReimpresion .modal-body {
    padding: 20px 24px;
}

/* --- FILTROS --- */
#modalReimpresion .jobi-filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
#modalReimpresion .jobi-input {
    border-radius: 10px;
    border: 1px solid #d7dbe3;
    padding: 8px 12px;
    box-shadow: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
#modalReimpresion .jobi-input:focus {
    border-color: var(--jobi-blue);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}
#modalReimpresion .jobi-input-search {
    flex: 2 1 260px;
    min-width: 200px;
}
#modalReimpresion .jobi-input[type="date"] {
    flex: 1 1 150px;
    min-width: 140px;
}
#modalReimpresion .jobi-btn-clear {
    border-radius: 10px;
    flex: 0 0 auto;
}

/* --- TABLA --- */
#modalReimpresion .jobi-table-wrap {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}
#modalReimpresion .jobi-table {
    margin-bottom: 0;
    font-size: 0.88rem;
}
#modalReimpresion .jobi-table thead th {
    background: var(--jobi-navy);
    color: #fff;
    border: none;
    font-weight: 600;
    white-space: nowrap;
    padding: 10px 12px;
}
#modalReimpresion .jobi-table tbody td {
    vertical-align: middle;
    padding: 10px 12px;
    white-space: nowrap;
}
#modalReimpresion .jobi-table tbody tr:hover {
    background: rgba(37, 99, 235, 0.06);
}

.jobi-badge {
    background: var(--jobi-navy);
    color: #fff;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: 0.78rem;
    display: inline-block;
    white-space: nowrap;
}

.jobi-cliente {
    display: inline-block;
    max-width: 180px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    vertical-align: middle;
}

.jobi-total {
    font-weight: 700;
    color: var(--jobi-green);
    white-space: nowrap;
}

.jobi-acciones {
    display: flex;
    gap: 6px;
    white-space: nowrap;
}
.jobi-pill-btn {
    border: none;
    border-radius: 999px;
    color: #fff;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0 14px;
    font-size: 0.78rem;
    font-weight: 600;
    text-decoration: none;
    transition: box-shadow 0.2s ease;
}
.jobi-pill-btn:hover {
    box-shadow: 0 3px 10px rgba(0,0,0,0.25);
    color: #fff;
    text-decoration: none;
}
.jobi-btn-pdf { background: var(--jobi-orange); }
.jobi-btn-comprobante { background: var(--jobi-green); }
.jobi-btn-ticket { background: var(--jobi-teal); }

/* --- PAGINACION --- */
#modalReimpresion .jobi-pagination-wrap {
    display: flex;
    justify-content: center;
    margin-top: 16px;
}
#modalReimpresion .jobi-pagination .page-link {
    border: none;
    color: var(--jobi-navy);
    border-radius: 8px;
    margin: 0 2px;
}
#modalReimpresion .jobi-pagination .page-item.active .page-link {
    background: var(--jobi-blue);
    color: #fff;
}
</style>