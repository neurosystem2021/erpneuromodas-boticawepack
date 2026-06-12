<template>
  <div id="invoice-container">
    <div class="accent-bar"></div>

    <!-- ENCABEZADO -->
    <header class="inv-header clearfix">
      <div class="inv-header__brand">
        <img src="/resources/images/banner-proforma.jpg" class="inv-header__logo" />
      </div>
      <div class="inv-header__badge">
        <div class="inv-header__badge-type">Cotización</div>
        <div class="inv-header__badge-num">{{ proforma.Anio }}-{{ proforma.Numero }}</div>
      </div>
    </header>

    <main>
      <!-- INFO CLIENTE / DOCUMENTO -->
      <div id="details" class="inv-details clearfix">
        <div class="inv-details__col">
          <div class="inv-details__col-title">Datos del cliente</div>
          <table class="inv-info-table">
            <tbody>
              <tr>
                <td class="inv-info-table__lbl">Cliente</td>
                <td class="inv-info-table__val">{{ proforma.Cliente }}</td>
              </tr>
              <tr>
                <td class="inv-info-table__lbl">Documento</td>
                <td class="inv-info-table__val">{{ proforma.DniRuc }}</td>
              </tr>
              <tr>
                <td class="inv-info-table__lbl">Dirección</td>
                <td class="inv-info-table__val">{{ proforma.ClienteDireccion }}</td>
              </tr>
              <tr>
                <td class="inv-info-table__lbl">Atendido por</td>
                <td class="inv-info-table__val">{{ proforma.UsuarioReg }}</td>
              </tr>
              <tr>
                <td class="inv-info-table__lbl">T. Pago</td>
                <td class="inv-info-table__val">
                  <span :class="proforma.EsCredito == 1 ? 'badge badge--green' : 'badge badge--blue'">
                    {{ proforma.EsCredito == 1 ? 'CRÉDITO' : 'CONTADO' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="inv-details__col">
          <div class="inv-details__col-title">Detalles del documento</div>
          <table class="inv-info-table">
            <tbody>
              <tr>
                <td class="inv-info-table__lbl">Fecha de emisión</td>
                <td class="inv-info-table__val">{{ proforma.FechaReg ? proforma.FechaReg.substring(0, 10) : '' }}</td>
              </tr>
              <tr>
                <td class="inv-info-table__lbl">Validez</td>
                <td class="inv-info-table__val">{{ proforma.DiasValidos }} días</td>
              </tr>
              <tr>
                <td class="inv-info-table__lbl">Entrega</td>
                <td class="inv-info-table__val">
                  <span :class="proforma.PorEntregar == 1 ? 'badge badge--orange' : 'badge badge--green'">
                    {{ proforma.PorEntregar == 1 ? proforma.PorEntregarDias + ' días' : 'INMEDIATO' }}
                  </span>
                </td>
              </tr>
              <tr v-if="proforma.Observacion">
                <td class="inv-info-table__lbl">Observación</td>
                <td class="inv-info-table__val">{{ proforma.Observacion }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TABLA DE PRODUCTOS -->
      <div class="inv-table-wrap">
        <table class="inv-table">
          <thead>
            <tr>
              <th class="center">#</th>
              <th class="center">Cant.</th>
              <th colspan="2" class="left">Descripción</th>
              <th class="center">U.M.</th>
              <th class="center">Modelo</th>
              <th class="center">Marca</th>
              <th class="center">Talla</th>
              <th class="center">Color</th>
              <th class="center">Género</th>
              <th class="center">Botapie</th>
              <th class="right">Precio U.</th>
              <th class="right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(prod, index) in proforma.productos" :key="index">
              <td class="center inv-table__num">{{ index + 1 }}</td>
              <td class="center">
                <span class="qty-pill">{{ prod.Cantidad }}</span>
              </td>
              <td colspan="2" class="left">
                <div class="prod-name">
                  {{ prod.Producto }} - {{ prod.CodigoBarra }}
                  <span v-if="presentacionProducto(prod)" class="prod-pres">({{ presentacionProducto(prod) }})</span>
                </div>
                <div v-if="prod.ProductoDesc" class="prod-desc">{{ prod.ProductoDesc }}</div>
                <div v-if="prod.ProductoDesc2" class="prod-desc">{{ prod.ProductoDesc2 }}</div>
                <div v-if="prod.ProductoDesc3" class="prod-desc">{{ prod.ProductoDesc3 }}</div>
              </td>
              <td class="center">{{ getPresentacion(prod) }}</td>
              <td class="center">{{ prod.ProductoModelo }}</td>
              <td class="center">{{ prod.ProductoMarca }}</td>
              <td class="center">{{ prod.ProductoTalla }}</td>
              <td class="center">{{ prod.Color }}</td>
              <td class="center">{{ prod.Genero }}</td>
              <td class="center">{{ prod.Botapie }}</td>
              <td class="right precio">S/. {{ prod.Precio | moneda }}</td>
              <td class="right precio">
                S/. {{ (prod.Precio * prod.Cantidad) - prod.Descuento | redondear }}
                <div v-if="prod.Descuento" class="dsct-label">Dsct: {{ prod.Descuento | moneda('S/. ') }}</div>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="inv-table__subtotal">
              <td colspan="10"></td>
              <td colspan="2" class="right">Subtotal</td>
              <td class="right">S/. {{ subTotal | redondear }}</td>
            </tr>
            <tr class="inv-table__total">
              <td colspan="10"></td>
              <td colspan="2" class="right">TOTAL</td>
              <td class="right">S/. {{ subTotal | redondear }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <!-- CUENTAS BANCARIAS -->
      <div class="inv-accounts">
        <div class="cuentas-titulo">Cuentas bancarias para depósito / transferencia</div>
        <div class="cuentas-grid">
          <div class="banco-card">
            <div class="banco-header bcp-header">
              <span class="banco-nombre">›BCP‹</span>
            </div>
            <table class="banco-table">
              <tr>
                <td class="banco-lbl banco-lbl--red">Cta. Corriente Soles</td>
                <td class="banco-val">-</td>
              </tr>
              <tr>
                <td class="banco-lbl banco-lbl--gray">Cta. Interbancaria</td>
                <td class="banco-val">-</td>
              </tr>
            </table>
          </div>
          <div class="banco-card">
            <div class="banco-header bbva-header">
              <span class="banco-nombre">BBVA Continental</span>
            </div>
            <table class="banco-table">
              <tr>
                <td class="banco-lbl banco-lbl--red">Cta. Corriente Soles</td>
                <td class="banco-val">-</td>
              </tr>
              <tr>
                <td class="banco-lbl banco-lbl--gray">Cta. Interbancaria</td>
                <td class="banco-val">-</td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <!-- NOTAS Y FIRMA -->
      <div class="inv-footer-row">
        <div class="inv-notes">
          <div class="inv-notes__title">Nota:</div>
          <div class="inv-notes__line">Los productos serán entregados en el local comercial.</div>

          <template v-if="proforma.EsDescripcion == 1">
            <div class="inv-notes__line" v-if="proforma.DescripcionProforma">
              <strong>Plazo de Entrega:</strong> {{ proforma.DescripcionProforma }}
            </div>
            <div class="inv-notes__line" v-if="proforma.DescripcionProforma2">
              <strong>Validez de la oferta:</strong> {{ proforma.DescripcionProforma2 }}
            </div>
            <div class="inv-notes__line" v-if="proforma.DescripcionProforma3">
              <strong>Lugar de Entrega:</strong> {{ proforma.DescripcionProforma3 }}
            </div>
            <div class="inv-notes__line" v-if="proforma.DescripcionProforma4">
              <strong>Garantía:</strong> {{ proforma.DescripcionProforma4 }}
            </div>
            <div class="inv-notes__line" v-if="proforma.DescripcionProforma5">
              {{ proforma.DescripcionProforma5 }}
            </div>
          </template>

          <div class="inv-notes__line">
            Esta cotización tiene validez de <strong>{{ proforma.DiasValidos }}</strong> días.
          </div>
        </div>

        <div class="inv-firma-wrap">
          <!-- <img src="/resources/images/firma.png" class="inv-firma-img" /> -->
          <div class="inv-firma-line">
            <span class="inv-firma-label">Firma / Sello de autorización</span>
          </div>
        </div>
      </div>

      <!-- BOTÓN IMPRIMIR -->
      <div class="inv-print-row">
        <button class="btn-imprimir" id="printBtn" @click="imprimirPantalla">
          Imprimir
        </button>
        <button class="btn-imprimir" id="printBtn" @click="imprimir">
          Imprimir cotización
        </button>
        <button class="btn btn-success" id="printBtn" @click="enviarWsp()">
          Enviar por WhatsApp
        </button>
      </div>
    </main>
  </div>
</template>

<script>
import { API } from "@/store/config.js";
import moment from "moment";
export default {
  mounted() {
    this.getProforma();
    this.getEmpresa();
  },
  data() {
    return {
      API: API,
      idProforma: this.$route.params.id,
      proforma: { productos: [] },
      empresa: {},
      fechaHasta: "",
      DiasCredito: 0
    };
  },
  computed: {
    subTotal() {
      let sumatoria = 0;
      this.proforma.productos.forEach(prod => {
        sumatoria += prod.Cantidad * prod.Precio - prod.Descuento;
      });
      return sumatoria;
    }
  },
  methods: {
    getProforma() {
      this.axios.get(API + "proformas/id/" + this.idProforma).then(res => {
        this.proforma = res.data;
        this.fechaHasta = this.calcularFechaHasta(
          this.proforma.FechaReg.substring(0, 10),
          this.proforma.DiasValidos
        );
        this.DiasCredito = this.calcularDiasCredito(
          this.proforma.FechaReg,
          this.proforma.FechaCredito
        );
      });
    },
    presentacionProducto(item) {
      if (item.presentacion) {
        let presentacion = JSON.parse(item.presentacion);
        return `${presentacion.nombre} de ${presentacion.cantidad} uds`;
      }

      return "";
    },
    calcularDiasCredito(FechaReg, FechaCredito) {
      let FechaRegFormat = moment(FechaReg, "YYYY-MM-DD");
      let FechaCreditoFormat = moment(FechaCredito, "YYYY-MM-DD");
      return FechaCreditoFormat.diff(FechaRegFormat, "days");
    },
    calcularFechaHasta(actual, dias) {
      var new_date = moment(actual).add(dias, "days");
      return new_date.format("YYYY-MM-DD");
    },
    getPresentacion(prod) {
      if (prod.presentacion) {
        let presentacion = JSON.parse(prod.presentacion);
        return presentacion.nombre;
      } else {
        return prod.ProductoMedicion;
      }
    },
    getEmpresa() {
      this.axios.get(API + "empresa/id/" + 1).then(res => {
        this.empresa = res.data;
      });
    },
    imprimir() {
      this.axios(`${API}venta/proforma/generar_pdf?id=${this.idProforma}`).then(res => {
        if (res.data.url) {
          this.urlPdfGenerado = res.data.url; // Guardamos la URL para habilitar el botón de descarga
          window.open(res.data.url, "_blank");
        }
      });
    },

    async enviarWsp() {
      const loadingSwal = this.$swal({
        title: "Generando PDF...",
        text: "Por favor espera...",
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });

      try {
        // Genera el PDF usando proforma.php del dompdf (igual que el botón "Imprimir cotización")
        const res = await this.axios.get(`${API}venta/proforma/generar_pdf?id=${this.idProforma}`);
        
        this.$swal.close();

        if (res.data.url) {
          const urlPdf = res.data.url;
          await this.solicitarTelefono(urlPdf);
        } else {
          this.$swal({ title: "Error", text: "No se pudo generar el PDF.", icon: "error" });
        }
      } catch (e) {
        this.$swal.close();
        this.$swal({ title: "Error", text: "Ocurrió un error al generar el PDF.", icon: "error" });
      }
    },

    async solicitarTelefono(data) {
      console.log(data);
      const { value: telefono } = await this.$swal({
        title: "Ingresa tu número de teléfono",
        input: "text",
        inputPlaceholder: "Ejemplo: 987654321",
        showCancelButton: true,
        inputValidator: value => {
          const regexTelefono = /^9\d{8}$/; // Valida que comience con 9 y tenga 9 dígitos
          if (!regexTelefono.test(value)) {
            return "Ingresa un número válido (9 dígitos, comienza con 9)";
          }
        }
      });

      if (telefono) {
        window.open(
          `https://wa.me/51${telefono}?text=Para poder visualizar la proforma Ingrese al siguiente enlace ${data}`
        );
      }
    },

    imprimirPantalla() {
      window.print();
    }
  }
};
function imprimirPantalla() {
  // Llama al asistente de impresión nativo del navegador (Equivalente exacto a pulsar Ctrl + P)
  window.print();
}
</script>

<style scoped>
/* ─── RESET ─────────────────────────────────────────── */
* {
  box-sizing: border-box;
}

/* ─── BARRA ACENTO SUPERIOR ──────────────────────────── */
/* (ver .accent-bar más abajo) */

/* ─── CONTENEDOR PRINCIPAL ───────────────────────────── */
#invoice-container {
  max-width: 960px;
  margin: 1.5rem auto;
  background: #ffffff;
  border: 1px solid #e0e0e0;
  border-radius: 12px;
  overflow: hidden;
  font-family: 'Segoe UI', Arial, sans-serif;
  font-size: 13px;
  color: #222;
}

.inv-header__brand {
  background: #fff;
  border-radius: 8px;
  padding: 6px 12px;
  display: inline-flex;
  align-items: center;
}

.accent-bar {
  height: 5px;
  background: linear-gradient(to right, #010067 65%, #fd8100 100%);
}

/* ─── ENCABEZADO ─────────────────────────────────────── */
.inv-header {
  background: #010067;
  padding: 1.25rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.inv-header__logo {
  max-height: 90px;
  max-width: 280px;
  object-fit: contain;
}

.inv-header__badge {
  background: #fd8100;
  border-radius: 8px;
  padding: 10px 22px;
  text-align: center;
  min-width: 140px;
}

.inv-header__badge-type {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: rgba(255, 255, 255, 0.85);
}

.inv-header__badge-num {
  font-size: 22px;
  font-weight: 600;
  color: #fff;
  line-height: 1.2;
}

/* ─── SECCIÓN INFO (CLIENTE / DOCUMENTO) ─────────────── */
.inv-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  border-bottom: 1px solid #ebebeb;
}

.inv-details__col {
  padding: 1.25rem 2rem;
}

.inv-details__col:first-child {
  border-right: 1px solid #ebebeb;
}

.inv-details__col-title {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #888;
  font-weight: 600;
  margin-bottom: 10px;
}

.inv-info-table {
  width: 100%;
  border-collapse: collapse;
}

.inv-info-table td {
  padding: 4px 0;
  vertical-align: top;
  border: none;
  background: transparent;
}

.inv-info-table__lbl {
  color: #888;
  font-weight: 600;
  width: 38%;
  font-size: 12px;
}

.inv-info-table__val {
  color: #222;
  text-transform: uppercase;
  font-size: 12px;
}

/* ─── BADGES ─────────────────────────────────────────── */
.badge {
  display: inline-block;
  font-size: 10px;
  font-weight: 600;
  padding: 2px 10px;
  border-radius: 20px;
  letter-spacing: 0.3px;
}

.badge--green {
  background: #e8f5e9;
  color: #2e7d32;
}

.badge--blue {
  background: #e3f2fd;
  color: #1565c0;
}

.badge--orange {
  background: #fff3e0;
  color: #bf360c;
}

/* ─── TABLA DE PRODUCTOS ─────────────────────────────── */
.inv-table-wrap {
  overflow-x: auto;
}

.inv-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 820px;
}

.inv-table thead tr {
  background: #010067;
}

.inv-table thead th {
  color: #fff;
  font-weight: 600;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  padding: 10px 8px;
  white-space: nowrap;
}

.inv-table thead th.center {
  text-align: center;
}

.inv-table thead th.left {
  text-align: left;
}

.inv-table thead th.right {
  text-align: right;
}

.inv-table tbody tr {
  border-bottom: 1px solid #f0f0f0;
}

.inv-table tbody tr:nth-child(even) {
  background: #fafafa;
}

.inv-table tbody td {
  padding: 10px 8px;
  vertical-align: middle;
  border: none;
  background: transparent;
}

.inv-table tbody td.center {
  text-align: center;
}

.inv-table tbody td.left {
  text-align: left;
}

.inv-table tbody td.right {
  text-align: right;
}

.inv-table__num {
  color: #aaa;
  font-size: 11px;
}

/* ─── PRODUCTO EN TABLA ──────────────────────────────── */
.prod-name {
  font-weight: 600;
  font-size: 13px;
  color: #222;
}

.prod-pres {
  font-weight: 400;
  font-size: 11px;
  color: #888;
}

.prod-desc {
  font-size: 11px;
  color: #666;
  margin-top: 2px;
}

.qty-pill {
  display: inline-block;
  background: #eef0f5;
  color: #333;
  border-radius: 20px;
  padding: 3px 12px;
  font-weight: 600;
  font-size: 13px;
}

.precio {
  color: #010067;
  font-weight: 600;
}

.dsct-label {
  font-size: 10px;
  color: #e67e22;
  font-weight: 400;
  margin-top: 2px;
}

/* ─── TFOOT ──────────────────────────────────────────── */
.inv-table__subtotal td {
  padding: 10px 8px;
  font-size: 13px;
  color: #555;
  border-top: 1px solid #e0e0e0;
  border-bottom: none;
  background: transparent;
}

.inv-table__total td {
  padding: 12px 8px;
  font-size: 15px;
  font-weight: 700;
  background: #010067 !important;
  color: #fff !important;
  border: none;
}

/* ─── CUENTAS BANCARIAS ──────────────────────────────── */
.inv-accounts {
  padding: 1rem 2rem;
  border-top: 1px solid #ebebeb;
}

.cuentas-titulo {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 1px;
  color: #888;
  font-weight: 600;
  margin-bottom: 10px;
}

.cuentas-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.banco-card {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  overflow: hidden;
}

.banco-header {
  padding: 8px 14px;
  display: flex;
  align-items: center;
}

.bcp-header {
  background: #010067;
}

.bbva-header {
  background: #004a97;
}

.banco-nombre {
  color: #fff;
  font-weight: 700;
  font-size: 15px;
  letter-spacing: 0.5px;
}

.banco-table {
  width: 100%;
  border-collapse: collapse;
}

.banco-table tr {
  border-bottom: 1px solid #f0f0f0;
}

.banco-table tr:last-child {
  border-bottom: none;
}

.banco-lbl {
  padding: 7px 12px;
  font-size: 11px;
  font-weight: 600;
  width: 45%;
  color: #fff;
}

.banco-lbl--red {
  background: #cc0000;
}

.banco-lbl--gray {
  background: #888;
}

.banco-val {
  padding: 7px 12px;
  font-size: 12px;
  font-weight: 700;
  color: #222;
  background: #f9f9f9;
  letter-spacing: 0.3px;
}

/* ─── PIE: NOTAS Y FIRMA ─────────────────────────────── */
.inv-footer-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 2rem;
  padding: 1.25rem 2rem;
  border-top: 1px solid #ebebeb;
  background: #fafafa;
}

.inv-notes__title {
  font-weight: 700;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
  color: #010067;
}

.inv-notes__line {
  font-size: 12px;
  color: #555;
  line-height: 1.8;
}

.inv-firma-wrap {
  text-align: center;
  flex-shrink: 0;
  min-width: 180px;
}

.inv-firma-img {
  max-width: 140px;
  display: block;
  margin: 0 auto 8px;
}

.inv-firma-line {
  border-top: 2px solid #010067;
  padding-top: 6px;
}

.inv-firma-label {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #555;
}

/* ─── BOTÓN IMPRIMIR ─────────────────────────────────── */
.inv-print-row {
  text-align: center;
  padding: 1.25rem 2rem;
  border-top: 1px solid #ebebeb;
}

.btn-imprimir {
  background: #010067;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 32px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  letter-spacing: 0.3px;
  transition: background 0.2s;
}

.btn-imprimir:hover {
  background: #fd8100;
}

/* ─── PRINT ──────────────────────────────────────────── */
@media print {

  #printBtn,
  .inv-print-row {
    display: none !important;
  }

  #invoice-container {
    border: none;
    border-radius: 0;
    margin: 0;
    max-width: 100%;
  }

  .inv-table-wrap {
    overflow-x: visible;
  }
}
</style>