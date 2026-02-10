<template>
  <b-container fluid class="px-5 mb-5">
    <b-row>
        <b-col cols="12">
         <b-breadcrumb :items="items"></b-breadcrumb>
        </b-col>
        <b-col cols="2">
            Fecha Inicio
            <input type="date" class="form-control" v-model="fechaInicio">
        </b-col>
        <b-col cols="2">
            Fecha Fin
            <input type="date" class="form-control" v-model="fechaFin">
        </b-col>
        <b-col cols="2">
            Almacén
            <b-form-select v-model="almacen" 
                 value-field="IdAlmacen"
                 text-field="Almacen"
                :options="lstAlmances"></b-form-select>
        </b-col>
        <b-col cols="2">
            Tipo de Pago
            <b-form-select v-model="tipoPago" :options="opcionesPago"></b-form-select>
        </b-col>
        <b-col cols="1"  class="py-4">
          <b-form-checkbox
            id="checkbox-1"
            v-model="declarado"
            name="checkbox-1"
            value="1"
            unchecked-value="0"
          >
            Declarado
          </b-form-checkbox>
        </b-col>
        <b-col cols="3" class="pt-4">
            <b-button variant="info" @click="generarReporte()"><i class="fa fa-search" aria-hidden="true"></i> Generar</b-button>
            <b-button variant="success" @click="exportarReporte()"><i class="fa fa-file-excel" aria-hidden="true"></i> Exportar</b-button>
        </b-col>

        <b-col cols="12" class="mt-4 table-container">
            <table class="table table-hover table-striped ">
                <thead>
                    <tr>
                      <th width="5%" style="text-align: center;">Id</th>
                      <th width="10%" style="text-align: center;">Vendedor</th>
                      <th width="10%" style="text-align: center;">Fecha Doc</th>
                      <th width="15%" style="text-align: center;">Cliente</th>
                      <th width="5%" style="text-align: center;">Crédito</th>
                      <th width="5%" style="text-align: center;">Tipo Pago</th>
                      <th width="7%" style="text-align: center;">Cod. Sunat</th>
                      <th width="7%" style="text-align: center;">Tipo Doc.</th>
                      <th width="7%" style="text-align: center;">Anulado</th>
                      <th width="3%" style="text-align: center;">Serie</th>
                      <th width="3%" style="text-align: center;">Número</th>
                      <th width="6%" style="text-align: center;">SubTotal</th>
                      <th width="5%" style="text-align: center;">IGV</th>
                      <th width="10%" style="text-align: center;">Total</th>
                      <th width="7%"></th>
                    </tr>
                </thead>
                <tbody class="scrollable-tbody">
                    <tr v-for="(reporte,index) in lstReporte" :key="index" @click="abrirModalDetalle(reporte)" :class="{'clsAnulado': reporte.Anulado == 1}">
                        <td style="text-align: center;">{{reporte.idDocVenta}}</td>
                        <td style="text-align: center;">{{reporte.UsuarioReg}}</td>
                        <td style="text-align: center;">{{reporte.FechaDoc}}</td>
                        <td style="text-align: center;">{{reporte.Cliente}}</td>
                        <td style="text-align: center;">{{reporte.EsCredito == 1 ? 'SI' : 'NO'}}</td>
                         <td style="text-align: center;">{{reporte.TipoPago }}</td>
                        <td style="text-align: center;">{{reporte.CodSunat}}</td>
                        <td style="text-align: center;">{{reporte.TipoDoc}}</td>
                        <td style="text-align: center;">{{reporte.Anulado ==1 ? 'Anulado' :'Activo' }}</td>
                        <td style="text-align: center;">{{reporte.Serie}}</td>
                        <td style="text-align: center;">{{reporte.Numero}}</td>
                        <td style="text-align: center;">{{reporte.SubTotal}}</td>
                        <td style="text-align: center;">{{reporte.Igv}}</td>
                        <td style="text-align: center;">{{reporte.Total}}</td>
                        <td> <i v-if="reporte.Anulado ==0" class="fa fa-trash" style="color:red; cursor: pointer;" aria-hidden="true" @click.stop="eliminarReg(reporte)"></i> </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: #006064; color: #ffffff; font-weight: bold;">
                        <td colspan="12">Total</td>
                        <td style="text-align: center;">{{footerTotal}}</td>
                        <td colspan="1"></td>
                    </tr>
                </tfoot>
            </table>
        </b-col>
    </b-row>

    <b-modal v-model="dialogDetalle" size="lg" modal-class="modalRegVentaDetalle" :title="title">
          <table class="table table-hover table-striped">
            <thead>
              <th>IdDocVenta</th>
              <th>Cod. Barra</th>
              <th>Producto</th>
              <th>Marca</th>
              <th>Medición</th>
              <th>Cantidad</th>
              <th>Precio</th>
              <th>Descuento</th>
              <th>Total</th>
            </thead>
            <tbody>
              <tr v-for="(det,index) in detalle" :key="index" >
                <td>{{ det.IdDocVenta }}</td>
                <td>{{ det.CodigoBarra }}</td>
                <td>{{ det.Producto }}</td>
                <td>{{ det.ProductoMarca }}</td>
                <td>{{ det.ProductoMedicion }}</td>
                <td>{{ det.Cantidad }}</td>
                <td>{{ det.Precio }}</td>
                <td>{{ det.Descuento }}</td>
                <td>{{ obtenerTotal(det) }}</td>
              </tr>
            </tbody>
          </table>
    </b-modal>

  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      dialogDetalle: false,
      fechaInicio: "",
      fechaFin: "",
      almacen: 1,
      API: config.API,
      lstReporte: [],
      declarado: 1,
      title: "Detalle Reg. Ventas:",
      items: [
        { text: "VENTAS", href: "#" },
        { text: "REPORTE", href: "#" },
        { text: "REGISTRO VENTAS", active: true }
      ],
      lstAlmances: [],
      detalle: [],
      tipoPago: 0,
      // Opciones para el select de tipo de pago
      opcionesPago: [
        { value: 0, text: 'TODOS' },
        { value: 1, text: 'EFECTIVO' },
        { value: 2, text: 'TARJETA' },
        { value: 3, text: 'YAPE/PLIN' },
        { value: 4, text: 'TRANSF. BANC' },
        { value: 5, text: 'CONTADO' },
        { value: 6, text: 'CRÉDITO' }
      ]
    };
  },
  mounted() {
    this.fechaInicio = this.$moment().startOf("month").format("YYYY-MM-DD");
    this.fechaFin = this.$moment().endOf("month").format("YYYY-MM-DD");
    this.obtenerAlmance();
    this.generarReporte();
  },
  methods: {
    async abrirModalDetalle(item) {
      this.title = `Detalle Reg. Ventas: ${item.Serie}-${item.Numero}`;
      await this.axios(`${this.API}reporte/ListarRegVenta/detalle`, {
        params: { idDocVenta: item.idDocVenta }
      }).then(res => {
        if (res.data) {
          this.detalle = res.data;
        }
      });
      this.dialogDetalle = true;
    },
    async obtenerAlmance() {
      await this.axios(`${this.API}almacenes`).then(res => {
        if (res.data) {
          this.lstAlmances = res.data;
          this.lstAlmances.unshift({ Almacen: "TODOS", IdAlmacen: 0 });
        }
      });
    },
    obtenerTotal(item) {
      let total = item.Cantidad * item.Precio - (item.Descuento ? item.Descuento : 0);
      return parseFloat(total).toFixed(2);
    },
    async generarReporte() {
        this.lstReporte = [];
        let params = {
            fechaIni: this.fechaInicio,
            fechaFin: this.fechaFin,
            declarado: this.declarado,
            almacen: this.almacen,
            tipoPago: this.tipoPago
        };

        try {
            const res = await this.axios(`${this.API}reporte/ListarRegVenta`, { params });
            if (res.data) {
                this.lstReporte = res.data;
            }
        } catch (error) {
            console.error("Error al generar reporte", error);
        }
    },
    async exportarReporte() {
      let params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen,
        tipoPago: this.tipoPago
      };
      await this.axios(`${this.API}reporte/ListarRegVenta/excel`, {
        params: params
      }).then(res => {
        let url = res.data;
        url = url.replace(/\\/g, "");
        if (!url.startsWith("http")) url = "http://" + url;
        window.open(url, "_blank");
      });
    },
    eliminarReg(item) {
      if(item.IdTipoDoc == 9){
        this.$swal({ icon: "error", title: "Error", text: `No se puede anular nota de credito` });
        return;
      }
      this.$swal({
        icon: "question",
        title: "Anular registro",
        text: `Desea anular el registro: ${item.Serie}-${item.Numero}`,
        showCancelButton: true,
        confirmButtonText: "Si, Eliminar",
        cancelButtonText: "Cancelar"
      }).then(async response => {
        if (response.isConfirmed) {
          await this.axios.put(`${this.API}reporte/ListarRegVenta/anular`, { idDocVenta: item.idDocVenta })
            .then(() => { this.generarReporte(); });
        }
      });
    }
  },
  computed: {
    footerTotal() {
      let total = 0;
      if (this.lstReporte.length > 0) {
        total = this.lstReporte
          .filter(v => v.Anulado == 0)
          .reduce((sum, reporte) => sum + parseFloat(reporte.Total), 0);
      }
      return parseFloat(total).toFixed(2);
    }
  }
};
</script>

<style scoped>
/* Estilos se mantienen igual */
.modalRegVentaDetalle .modal-dialog { max-width: 90% !important; }
.clsAnulado { background: #ffcdd2 !important; }
.table-container { max-height: 680px; overflow-y: auto; position: relative; }
thead th, tfoot td { position: sticky; z-index: 1; }
thead th { top: 0; background: #f8f9fa; z-index: 2; }
tfoot td { bottom: 0; background: #006064; color: white; font-weight: bold; }
</style>