<template>
  <div class="reprint-page">
      <b-container class="reprint-card" fluid>
        <header class="reprint-header">
          <div>
            <span class="reprint-eyebrow">VENTAS / DOCUMENTOS</span>
            <h1>Reimpresión de comprobantes</h1>
            <p>Consulta, descarga y comparte tus documentos emitidos.</p>
          </div>
          <div class="document-counter">
            <i class="fas fa-file-invoice"></i>
            <span><strong>{{ totalRows }}</strong> documentos</span>
          </div>
        </header>
        <b-row>
          <b-col md="8" class="filter-column">
            <b-form-group label="Buscar documento" class="mb-0 filter-control">
              <b-input-group>
                <b-input-group-prepend><span class="input-group-text"><i class="fas fa-search"></i></span></b-input-group-prepend>
                <b-form-input v-model="filter" placeholder="ID, correlativo, cliente o documento" @input="currentPage = 1" />
                <b-input-group-append>
                  <b-btn class="clear-button" :disabled="!filter" @click="filter = ''"><i class="fas fa-times"></i> Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
          </b-col>
          <b-col md="4" class="filter-column">
              <b-form-group label="Filas por página" class="mb-0 filter-control">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
          </b-col>

          <b-col cols="12" class="table-panel">
          <b-table
            class="documents-table"
            ref="tablaVentas"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            @row-clicked="rowClick"
            id="my-table" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage">
          
              <template slot="Reimprimir" slot-scope="data">
                <div class="document-actions" @click.stop>
                  <a class="btn action-button print-button" target="_blank" rel="noopener" :href="'/imprimir/index.php?IdDocVenta=' + data.item.idDocVenta + '&preview=1'">
                    <i class="fas fa-print"></i> {{data.item.TipoDoc}}
                  </a>
                  <a class="btn action-button pdf-button" :href="pdfUrl(data.item, true)" title="Descargar PDF">
                    <i class="fas fa-file-pdf"></i> PDF
                  </a>
                  <a class="btn action-button xml-button" :href="xmlUrl(data.item)" title="Descargar XML">
                    <i class="fas fa-file-code"></i> XML
                  </a>
                  <b-button class="action-button whatsapp-button" @click="enviarWhatsApp(data.item)">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                  </b-button>
                  <b-button class="action-button guide-button" @click="irGuiaRemision(data.item)">
                    <i class="fas fa-truck"></i> GUÍA REMISIÓN
                  </b-button>
                </div>
              </template>
          </b-table>
          </b-col>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <br />
        <br />
      </b-container>
  </div>
</template>

<script>
import config from "@/store/config.js";

export default {
  mounted() {
    this.axios.get(this.API + "ventas/count").then(response => {
      this.totalRows = response.data.total;
    });
  },
  data() {
    return {
      API: config.API,
      pageOptions: [5, 10, 15, 50, 100],
      filter: null,
      sortBy: "idDocVenta",
      sortDesc: true,
      productoSelect: {},
      items: [],
      isBusy: false,
      fields: [
        { key: "idDocVenta", label: "ID", sortable: true },
        { key: "Correlativo", sortable: true },
        { key: "FechaDoc", sortable: true },
        { key: "DniRuc", sortable: true },
        { key: "TipoDoc", sortable: true },
        { key: "Cliente", sortable: true },
        { key: "Total", sortable: true },
        { key: "Reimprimir" }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    };
  },
  methods: {
    pdfUrl(item, descargar) {
      return this.API + "imprimirpdf/" + item.idDocVenta + (descargar ? "?descargar=1" : "");
    },
    xmlUrl(item) {
      return this.API + "descargarxml/" + item.idDocVenta;
    },
    async enviarWhatsApp(item) {
      const resultado = await this.$swal({
        title: "Enviar comprobante por WhatsApp",
        text: "Ingresa el celular del cliente",
        input: "text",
        inputPlaceholder: "Ejemplo: 987654321",
        showCancelButton: true,
        confirmButtonText: "Enviar",
        cancelButtonText: "Cancelar",
        inputValidator: value => {
          const telefono = String(value || "").replace(/\D/g, "");
          if (!/^(?:51)?9\d{8}$/.test(telefono)) {
            return "Ingresa un celular peruano válido de 9 dígitos";
          }
        }
      });

      if (!resultado.value) return;

      let telefono = String(resultado.value).replace(/\D/g, "");
      if (telefono.length === 9) telefono = "51" + telefono;

      const enlacePdf = new URL(
        this.pdfUrl(item, false),
        window.location.origin
      ).href;
      const mensaje =
        "Hola, compartimos su comprobante " +
        item.Correlativo +
        ". Puede visualizarlo aquí: " +
        enlacePdf;
      window.open(
        "https://wa.me/" + telefono + "?text=" + encodeURIComponent(mensaje),
        "_blank"
      );
    },
    rowClick(item, index) {
      this.productoSelect = item;
      //this.$refs.productoForm.producto.IdProducto = 'a'
      //console.log(this.$refs.productoForm.$refs.productoForm)
    },
    actualizarTabla() {
      this.$refs.tablaVentas.refresh();
    },
    nuevoProducto() {
      this.productoSelect = {};
      this.$refs.productoForm.show = true;
    },
    myProvider(ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let url =
        this.API +
        "ventas?noFecha=1&limit=" +
        ctx.perPage +
        "&page=" +
        ctx.currentPage +
        "&filter=" +
        (ctx.filter || "") +
        "&sortBy=" +
        ctx.sortBy;
      url += "&sortDesc=" + (ctx.sortDesc ? 1 : 0);
      let promise = this.axios.get(url);

      return promise
        .then(result => {
          this.items = result.data;
          //this.totalRows = this.items.length
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          return this.items;
        })
        .catch(error => {
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          // Returning an empty array, allows table to correctly handle busy state in case of error
          return [];
        });
    },
    irGuiaRemision(item) {
      console.log(item);
      this.$router.push({
        name: "RemisionVer",
        params: {
          idDocVenta: item.idDocVenta,
          Serie: item.Serie,
          Numero: item.Numero
        }
      });
    }
  },
  components: {}
};
</script>

<style scoped>
.reprint-page {
  min-height: calc(100vh - 70px);
  padding: 24px 28px 42px;
  background: #f1f7fb;
}

.reprint-card {
  padding: 0 0 22px;
  overflow: hidden;
  background: #fff;
  border: 1px solid #d8e6ef;
  border-radius: 18px;
  box-shadow: 0 14px 34px rgba(15, 65, 101, .10);
}

.reprint-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  padding: 22px 26px;
  color: #fff;
  background: linear-gradient(110deg, #063b71 0%, #087aa0 58%, #0a9ab0 100%);
}

.reprint-eyebrow { font-size: 11px; font-weight: 800; letter-spacing: 1.3px; opacity: .78; }
.reprint-header h1 { margin: 3px 0 4px; font-size: 24px; font-weight: 800; }
.reprint-header p { margin: 0; color: rgba(255, 255, 255, .82); }
.document-counter { display: flex; align-items: center; gap: 11px; padding: 11px 16px; border: 1px solid rgba(255,255,255,.28); border-radius: 12px; background: rgba(255,255,255,.12); white-space: nowrap; }
.document-counter i { font-size: 22px; }
.document-counter strong { font-size: 18px; }

.filter-column { padding: 20px 26px 18px; }
.filter-column + .filter-column { padding-left: 0; }
.filter-control /deep/ label { margin-bottom: 7px; color: #173b55; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: .35px; }
.filter-control /deep/ .form-control,
.filter-control /deep/ .custom-select,
.filter-control /deep/ .input-group-text { height: 42px; border-color: #cedee9; background: #f8fbfd; }
.filter-control /deep/ .input-group-text { color: #0b7da3; border-right: 0; }
.filter-control /deep/ .form-control:focus,
.filter-control /deep/ .custom-select:focus { border-color: #1597bb; box-shadow: 0 0 0 3px rgba(21,151,187,.12); }
.clear-button { height: 42px; padding: 0 16px; border-color: #cad7df; background: #e9eff3; color: #365365; font-weight: 700; }

.table-panel { padding: 0 12px; overflow-x: auto; }
.documents-table { min-width: 1120px; margin-bottom: 0; color: #123b58; border-collapse: separate; border-spacing: 0; }
.documents-table /deep/ thead th { padding: 14px 12px; color: #fff; background: #086795; border: 0; font-size: 12px; text-transform: uppercase; letter-spacing: .25px; vertical-align: middle; }
.documents-table /deep/ thead th:first-child { border-radius: 11px 0 0 0; }
.documents-table /deep/ thead th:last-child { border-radius: 0 11px 0 0; }
.documents-table /deep/ tbody td { padding: 13px 12px; border-top: 0; border-bottom: 1px solid #dce9f1; vertical-align: middle; }
.documents-table /deep/ tbody tr:nth-child(odd) { background: #f5fafd; }
.documents-table /deep/ tbody tr:nth-child(even) { background: #e9f6fb; }
.documents-table /deep/ tbody tr:hover { background: #d9f0f8; }

.document-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  min-width: 560px;
}

.document-actions .action-button {
  margin: 0;
  min-height: 36px;
  padding: 8px 12px;
  border: 0;
  border-radius: 9px;
  white-space: nowrap;
  color: #fff;
  font-size: 12px;
  font-weight: 800;
  box-shadow: 0 5px 12px rgba(12, 74, 109, .13);
  transition: transform .15s ease, box-shadow .15s ease, filter .15s ease;
}
.document-actions .action-button:hover { color: #fff; filter: brightness(.96); transform: translateY(-1px); box-shadow: 0 7px 15px rgba(12,74,109,.20); }

.document-actions i { margin-right: 5px; }
.print-button { background: linear-gradient(135deg, #079b69, #10ba79); }
.pdf-button { background: linear-gradient(135deg, #d93649, #ef5562); }
.xml-button { background: linear-gradient(135deg, #dc8c08, #f3a91e); }
.whatsapp-button { background: linear-gradient(135deg, #1aa957, #25d366); }
.guide-button { background: linear-gradient(135deg, #0787b1, #13a7c5); }

@media (max-width: 767px) {
  .reprint-page { padding: 12px; }
  .reprint-header { align-items: flex-start; flex-direction: column; padding: 19px; }
  .reprint-header h1 { font-size: 20px; }
  .filter-column { padding: 15px 18px; }
  .filter-column + .filter-column { padding-top: 0; padding-left: 18px; }
}
</style>
