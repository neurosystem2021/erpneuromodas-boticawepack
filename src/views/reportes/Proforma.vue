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
                <td class="inv-info-table__val">{{ proforma.Documento }}</td>
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
              <th class="left">Descripción</th>
              <th class="center">Cant.</th>
              <th class="right">Precio U.</th>
              <th class="right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(prod, index) in proforma.productos" :key="index">
              <td class="center inv-table__num">{{ index + 1 }}</td>
              <td class="left">
                <div class="prod-name">
                  {{ prod.Producto }} - {{ prod.CodigoBarra }}
                  <span v-if="presentacionProducto(prod)" class="prod-pres">({{ presentacionProducto(prod) }})</span>
                </div>
                <div v-if="prod.ProductoDesc" class="prod-desc">{{ prod.ProductoDesc }}</div>
                <div v-if="prod.ProductoDesc2" class="prod-desc">{{ prod.ProductoDesc2 }}</div>
                <div v-if="prod.ProductoDesc3" class="prod-desc">{{ prod.ProductoDesc3 }}</div>
              </td>
              <td class="center">
                <span class="qty-pill">{{ prod.Cantidad }}</span>
              </td>
              <td class="right precio">S/. {{ prod.Precio | moneda }}</td>
              <td class="right precio">
                S/. {{ (prod.Precio * prod.Cantidad) - prod.Descuento | redondear }}
                <div v-if="prod.Descuento" class="dsct-label">Dsct: {{ prod.Descuento | moneda('S/. ') }}</div>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="inv-table__subtotal">
              <td colspan="3"></td>
              <td class="right">Subtotal</td>
              <td class="right">S/. {{ subTotal | redondear }}</td>
            </tr>
            <tr class="inv-table__total">
              <td colspan="3"></td>
              <td class="right">TOTAL</td>
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
              <span class="banco-nombre">BCP</span>
            </div>
            <table class="banco-table">
              <tr>
                <td class="banco-lbl banco-lbl--red">Cta. Corriente Soles</td>
                <td class="banco-val">365-2055065-0-99</td>
              </tr>
              <tr>
                <td class="banco-lbl banco-lbl--gray">Cta. Interbancaria</td>
                <td class="banco-val">002-365-002055065099-56</td>
              </tr>
            </table>
          </div>
          <div class="banco-card">
            <div class="banco-header bbva-header">
              <span class="banco-nombre">BBVA Continental</span>
            </div>
            <table class="banco-table">
              <tr>
                <td class="banco-val banco-val--titular" colspan="2">TITULAR: STEFANY CAROL TENAZOA MALPARTIDA</td>
              </tr>
              <tr>
                <td class="banco-lbl banco-lbl--red">Cta. Corriente Soles</td>
                <td class="banco-val">011-0210-0200898127</td>
              </tr>
              <tr>
                <td class="banco-lbl banco-lbl--gray">Cta. Interbancaria</td>
                <td class="banco-val">011-210-000200898127-22</td>
              </tr>
            </table>
          </div>
          <div class="banco-card banco-card--full">
            <div class="banco-header bn-header">
              <span class="banco-nombre">Banco de la Nación</span>
            </div>
            <table class="banco-table">
              <tr>
                <td class="banco-val banco-val--titular" colspan="2">TITULAR: INVERSIONES ALTAS CUMBRES DEL ORIENTE S.C.R.L.</td>
              </tr>
              <tr>
                <td class="banco-lbl banco-lbl--red">Cta. Ahorro Soles</td>
                <td class="banco-val">04-489-064559</td>
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
        <button class="btn-imprimir btn-imprimir--outline" id="printBtn" @click="imprimirPantalla">
          Imprimir
        </button>
        <button class="btn-imprimir" id="printBtn" @click="imprimir">
          Imprimir cotización
        </button>
        <button class="btn-imprimir btn-imprimir--wsp" id="printBtn" @click="enviarWsp()">
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
</script>

<style scoped>
/* ─── RESET ─────────────────────────────────────────── */
* {
  box-sizing: border-box;
}

/* ─── CONTENEDOR PRINCIPAL ───────────────────────────── */
#invoice-container {
  max-width: 980px;
  margin: 1.5rem auto;
  background: #ffffff;
  border-radius: 14px;
  overflow: hidden;
  font-family: 'Segoe UI', Arial, sans-serif;
  font-size: 13px;
  color: #222;
  box-shadow: 0 10px 40px rgba(1, 0, 103, 0.12), 0 2px 8px rgba(0, 0, 0, 0.06);
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
  background: linear-gradient(135deg, #010067 0%, #030080 100%);
  padding: 1.4rem 2rem;
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
  border-radius: 10px;
  padding: 12px 26px;
  text-align: center;
  min-width: 150px;
  box-shadow: 0 4px 14px rgba(253, 129, 0, 0.35);
}

.inv-header__badge-type {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: rgba(255, 255, 255, 0.85);
}

.inv-header__badge-num {
  font-size: 23px;
  font-weight: 700;
  color: #fff;
  line-height: 1.25;
}

/* ─── SECCIÓN INFO (CLIENTE / DOCUMENTO) ─────────────── */
.inv-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  border-bottom: 1px solid #ebebeb;
  background: #fbfbfd;
}

.inv-details__col {
  padding: 1.4rem 2rem;
}

.inv-details__col:first-child {
  border-right: 1px solid #ebebeb;
}

.inv-details__col-title {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 1.2px;
  color: #010067;
  font-weight: 700;
  margin-bottom: 12px;
  padding-bottom: 6px;
  border-bottom: 2px solid #fd8100;
  display: inline-block;
}

.inv-info-table {
  width: 100%;
  border-collapse: collapse;
}

.inv-info-table td {
  padding: 5px 0;
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
  font-weight: 500;
}

/* ─── BADGES ─────────────────────────────────────────── */
.badge {
  display: inline-block;
  font-size: 10px;
  font-weight: 700;
  padding: 3px 12px;
  border-radius: 20px;
  letter-spacing: 0.4px;
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
  min-width: 560px;
}

.inv-table thead tr {
  background: #010067;
}

.inv-table thead th {
  color: #fff;
  font-weight: 600;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  padding: 11px 8px;
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
  transition: background 0.15s;
}

.inv-table tbody tr:nth-child(even) {
  background: #fafbfd;
}

.inv-table tbody tr:hover {
  background: #f0f2fb;
}

.inv-table tbody td {
  padding: 11px 8px;
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
  font-weight: 600;
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
  color: #010067;
  border-radius: 20px;
  padding: 4px 14px;
  font-weight: 700;
  font-size: 13px;
}

.precio {
  color: #010067;
  font-weight: 600;
}

.dsct-label {
  font-size: 10px;
  color: #e67e22;
  font-weight: 600;
  margin-top: 2px;
}

/* ─── TFOOT ──────────────────────────────────────────── */
.inv-table__subtotal td {
  padding: 12px 8px;
  font-size: 13px;
  color: #555;
  border-top: 1px solid #e0e0e0;
  border-bottom: none;
  background: transparent;
}

.inv-table__total td {
  padding: 16px 8px;
  font-size: 17px;
  font-weight: 700;
  background: linear-gradient(135deg, #010067, #1a1a9e) !important;
  color: #fff !important;
  border: none;
}

/* ─── CUENTAS BANCARIAS ──────────────────────────────── */
.inv-accounts {
  padding: 1.4rem 2rem;
  border-top: 1px solid #ebebeb;
  background: #fbfbfd;
}

.cuentas-titulo {
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 1.2px;
  color: #010067;
  font-weight: 700;
  margin-bottom: 12px;
  padding-bottom: 6px;
  border-bottom: 2px solid #fd8100;
  display: inline-block;
}

.cuentas-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.2rem;
}

.banco-card {
  border: 1px solid #e0e0e0;
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}


.banco-header {
  padding: 10px 14px;
  display: flex;
  align-items: center;
}

.bcp-header {
  background: #010067;
}

.bbva-header {
  background: #004a97;
}

.bn-header {
  background: #94030D;
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
  padding: 8px 12px;
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
  padding: 8px 12px;
  font-size: 12px;
  font-weight: 700;
  color: #222;
  background: #f9f9f9;
  letter-spacing: 0.3px;
}

.banco-val--titular {
  background: #f4f4f8;
  color: #010067;
  font-size: 11px;
  text-transform: uppercase;
}

/* ─── PIE: NOTAS Y FIRMA ─────────────────────────────── */
.inv-footer-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  gap: 2rem;
  padding: 1.4rem 2rem;
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

/* ─── BOTONES ─────────────────────────────────────────── */
.inv-print-row {
  text-align: center;
  padding: 1.4rem 2rem;
  border-top: 1px solid #ebebeb;
  display: flex;
  justify-content: center;
  gap: 12px;
  flex-wrap: wrap;
}

.btn-imprimir {
  background: #010067;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 28px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  letter-spacing: 0.3px;
  transition: transform 0.15s, box-shadow 0.15s, background 0.2s;
}

.btn-imprimir:hover {
  background: #fd8100;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(253, 129, 0, 0.35);
}

.btn-imprimir--outline {
  background: #fff;
  color: #010067;
  border: 1.5px solid #010067;
}

.btn-imprimir--outline:hover {
  background: #010067;
  color: #fff;
}

.btn-imprimir--wsp {
  background: #25d366;
}

.btn-imprimir--wsp:hover {
  background: #1ebc59;
  box-shadow: 0 4px 12px rgba(37, 211, 102, 0.4);
}

/* ─── RESPONSIVE ──────────────────────────────────────── */
@media (max-width: 640px) {
  .inv-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .inv-details,
  .cuentas-grid {
    grid-template-columns: 1fr;
  }

  .inv-details__col:first-child {
    border-right: none;
    border-bottom: 1px solid #ebebeb;
  }

  .inv-footer-row {
    flex-direction: column;
    align-items: flex-start;
  }
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
    box-shadow: none;
    margin: 0;
    max-width: 100%;
  }

  .inv-table-wrap {
    overflow-x: visible;
  }
}
</style>