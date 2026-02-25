<template>
  <b-container fluid class="px-5 mb-5">
    <b-row>
       <b-col cols="2">
            Fecha Desde:
            <input type="date" class="form-control" v-model="fechaInicio">
        </b-col>
        <b-col cols="2">
            Fecha Hasta:
            <input type="date" class="form-control" v-model="fechaFin">
        </b-col>
        <b-col cols="2">
            Almacén
            <b-form-select v-model="almacen" 
                 value-field="IdAlmacen"
                 text-field="Almacen"
                :options="lstAlmances"></b-form-select>
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
        <b-col cols="5" class="pt-4">
            <b-button variant="info" @click="generarReporte()"><i class="fa fa-refresh" aria-hidden="true"></i> RECARGAR </b-button>
            <!-- <b-button variant="success" @click="exportar()"><i class="fa fa-file-excel" aria-hidden="true"></i> Exportar</b-button> -->
            <!-- <b-button variant="warning" @click="exportarDetallado()"><i class="fa fa-file-excel" aria-hidden="true"></i> Exportar Detallado</b-button> -->
            <!-- <b-button variant="default" @click="exportarSunat(1)"><i class="fa fa-file" aria-hidden="true"></i> txt</b-button> -->
            <button class="btn bnt-sire"  @click="exportarSunat(2)">
                <img  src="static/sire.png" style="width: 120px;cursor: pointer;"/>
            </button>
        </b-col>
        <b-col cols="12" class="mt-4 table-container">
            <table class="table table-hover table-striped ">
                <thead>
                    <tr>
                      <th>Id</th>
                      <th>Vendedor</th>
                      <th>Fecha Doc</th>
                      <th>Cod. Sunat</th>
                      <th>Tipo Doc.</th>
                      <th>Anulado</th>
                      <th>Serie</th>
                      <th>Número</th>
                      <th>SubTotal</th>
                      <th>IGV</th>
                      <th>Total</th>
                      <th></th>
                    </tr>
                </thead>
                <tbody class="scrollable-tbody">
                    <tr 
                        v-for="(reporte,index) in lstReporte" 
                        :key="index" 
                        @click="abrirModalDetalle(reporte)" 
                        :class="{'clsAnulado': reporte.Anulado == 1, 'clsVale': (reporte.IdTipoDoc == 11 || reporte.IdTipoDoc == 12)}"
                    >
                        <td style="text-align: center;">{{reporte.idDocVenta}}</td>
                        <td>{{reporte.UsuarioReg}}</td>
                        <td style="text-align: center;">{{reporte.FechaDoc}}</td>
                        <td style="text-align: center;">{{reporte.CodSunat}}</td>
                        <td style="text-align: center;">{{reporte.TipoDoc}} 
                            <span v-if="reporte.IdTipoDoc == 11 || reporte.IdTipoDoc == 12"> 
                              <span style="font-weight: bold;" v-if="reporte.ValeProcesado==0"> (NO SUMA) </span> 
                              <span style="font-weight: bold;" v-if="reporte.ValeProcesado==0"> (PENDIENTE) </span> 
                              <span style="font-weight: bold;" v-else> ({{ reporte.ComprobanteProcesado }})</span> 
                            </span> 
                        </td>
                        <td style="text-align: center;">{{reporte.Anulado ==1 ? 'Anulado' :'Activo' }} {{ reporte.IdTipoDoc }}</td>
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
                        <td colspan="10">Total</td>
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
    <b-modal v-model="seleccionarTipoFlag" title="Modos de Exportación" size="lg">
      <table class="table">
        <tr>
          <td>Exportar Todos Los tipos</td>
          <td>Se exportará <b>TODOS</b> los tipos de venta</td>
          <td><b-button @click="exportarReporte(0)" variant="success" block>EXPORTAR TODOS</b-button></td>
        </tr>
        <tr>
          <td>Exportar VALES DE CRÉDITO Y CANAJES</td>
          <td>Se exportará solo <b>VALES DE CRÉDITO Y CANJAES</b></td>
          <td><b-button @click="exportarReporte(1)" variant="success" block>EXPORTAR VALES Y CANJES</b-button></td>
        </tr>
        <tr>
          <td>Exportar solo tipos electrónicos <b>(PARA EL CONTADOR)</b></td>
          <td>Se exportará solo <b>(BOLETAS ELEC,FACT ELEC. Y NOTAS DE CRÉDITO ELEC.)</b></td>
          <td><b-button @click="exportarReporte(2)" variant="success" block>EXPORTAR ELECTRONICOS FOX</b-button></td>
        </tr>
      </table>
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      seleccionarTipoFlag: false,
      dialogDetalle: false,
      fechaInicio: "",
      fechaFin: "",
      almacen: 1,
      API: config.API,
      lstReporte: [],
      declarado: 1,
      title: "Detalle Reg. Ventas:",
      items: [
        {
          text: "VENTAS",
          href: "#"
        },
        {
          text: "REPORTE",
          href: "#"
        },
        {
          text: "REGISTRO VENTAS",
          active: true
        }
      ],

      lstAlmances: [],
      footer: {
        Producto: "TOTAL",
        Cantidad: 0,
        PrecioCosto: 0,
        PrecioVenta: 0,
        TotalVenta: 0,
        TotalCosto: 0,
        UtilidadBruta: 0,
        PorcentajeUti: 0
      },
      detalle: []
    };
  },
  mounted() {
    this.fechaInicio = this.$moment()
      .subtract(1, "month")
      .startOf("month")
      .format("YYYY-MM-DD");

    this.fechaFin = this.$moment()
      .subtract(1, "month")
      .endOf("month")
      .format("YYYY-MM-DD");
    this.obtenerAlmance();
    this.generarReporte();
  },
  methods: {
    async abrirModalDetalle(item) {
      this.title = `${this.title} ${item.Serie}-${item.Numero}`;
      await this.axios(`${this.API}reporte/ListarRegVenta/detalle`, {
        params: { idDocVenta: item.idDocVenta }
      }).then(res => {
        if (res.data) {
          this.detalle = res.data;
        }
      });
      this.dialogDetalle = true;
    },
    eliminarReg(item) {
      console.log(item.idDocVenta);
      this.$swal({
        icon: "question",
        title: "",
        text: `Desea anular el registro: ${item.Serie}-${item.Numero}`,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showCancelButton: true,
        cancelButtonColor: "#d33",
        confirmButtonText: "Si,Eliminar",
        cancelButtonText: "Cancelar"
      }).then(async response => {
        if (response.isConfirmed) {
          let data = {
            idDocVenta: item.idDocVenta
          };
          await this.axios
            .put(`${this.API}reporte/ListarRegVenta/anular`, data)
            .then(res => {
              console.log(res);
              this.generarReporte();
            });
        }
      });
    },
    async obtenerAlmance() {
      await this.axios(`${this.API}almacenes`).then(res => {
        if (res.data) {
          this.lstAlmances = res.data;
          this.lstAlmances.unshift({
            Almacen: "TODOS",
            IdAlmacen: 0
          });
        }
      });
    },
    obtenerTotal(item) {
      let total =
        item.Cantidad * item.Precio - (item.Descuento ? item.Descuento : 0);
      console.log(total);
      return parseFloat(total).toFixed(2);
    },
    exportar() {
      this.seleccionarTipoFlag = true;
    },
    async exportarDetallado() {
      let params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen
      };
      await this.axios(`${this.API}reporte/ListarRegVenta/exceldetallado`, {
        params: params
      }).then(res => {
        let url = res.data;
        url = url.replace(/\\/g, "");
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
          url = "https://" + url; // O "https://" si es necesario
        }
        window.open(url, "_blank");
      });
    },
    async exportarSunat(tipo) {
      const params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen
      };

      try {
        const res = await this.axios.get(`${this.API}exportar/ventas/zip`, {
          params
        });
        console.log(res.data);

        let url = tipo == 1 ? res.data.txt : res.data.zip;

        if (!url) return alert("No se generó el archivo correctamente.");

        // limpiar barras invertidas
        url = url.replace(/\\/g, "");

        // asegurar protocolo HTTPS
        if (!/^https?:\/\//i.test(url)) {
          url = "https://" + url;
        }

        // descargar (abrir en nueva pestaña)
        window.open(url, "_blank");
      } catch (err) {
        console.error("Error exportando a SUNAT:", err);
        alert("Error al exportar. Revisa la consola para más detalles.");
      }
    },
    async exportarReporte(tipo) {
      let params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen,
        tipo: tipo
      };
      await this.axios(`${this.API}reporte/ListarRegVenta/excel`, {
        params: params
      }).then(res => {
        let url = res.data;
        url = url.replace(/\\/g, "");
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
          url = "https://" + url; // O "https://" si es necesario
        }
        window.open(url, "_blank");
      });
    },
    async generarReporte() {
      this.lstReporte = [];
      let params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen,
        tipo: 0
      };
      await this.axios(`${this.API}reporte/ListarRegVenta`, {
        params: params
      }).then(res => {
        if (res.data) {
          let data = res.data;
          this.lstReporte = data;
          let Cantidad = 0;
          let PrecioCosto = 0;
          let PrecioVenta = 0;
          let TotalVenta = 0;
          let TotalCosto = 0;
          let UtilidadBruta = 0;
          let PorcentajeUti = 0;
          data.forEach(element => {
            Cantidad += element.Cantidad;
            PrecioCosto += element.PrecioCosto;
            PrecioVenta += element.PrecioVenta;
            TotalVenta += element.TotalVenta;
            TotalCosto += element.TotalCosto;
            UtilidadBruta += element.UtilidadBruta;
            PorcentajeUti += element.PorcentajeUti;
          });
          this.footer = {
            Producto: "TOTAL",
            Cantidad: parseFloat(Cantidad).toFixed(2),
            PrecioCosto: parseFloat(PrecioCosto).toFixed(2),
            PrecioVenta: parseFloat(PrecioVenta).toFixed(2),
            TotalVenta: parseFloat(TotalVenta).toFixed(2),
            TotalCosto: parseFloat(TotalCosto).toFixed(2),
            UtilidadBruta: parseFloat(UtilidadBruta).toFixed(2),
            PorcentajeUti: parseFloat(PorcentajeUti).toFixed(2)
          };
        }
      });
    }
  },
  computed: {
    footerTotal() {
      let total = 0;
      if (this.lstReporte.length > 0) {
        total = this.lstReporte.reduce((sum, reporte) => {
          return sum + reporte.Total;
        }, 0);
      }
      return parseFloat(total).toFixed(2);
    }
  }
};
</script>

<style>
.modalRegVentaDetalle .modal-dialog {
  max-width: 90% !important;
  width: 90% !important;
}

tr.clsVale {
  background-color: #d9edf7 !important;
}
.clsAnulado {
  /* background: red !important; */
  background: #ffcdd2 !important;
}

.table-container {
  max-height: 680px; /* ajusta la altura según necesidad */
  overflow-y: auto;
  position: relative;
}

/* Hace que el thead y tfoot se mantengan fijos */
thead th,
tfoot td {
  position: sticky;
  background: white;
  z-index: 1;
}

thead th {
  top: 0;
  background: #f8f9fa; /* color del encabezado */
  z-index: 2;
}

tfoot td {
  bottom: 0;
  background: #006064;
  color: white;
  font-weight: bold;
}

.bnt-sire {
  border: 1px solid #b5bd00;
  background: #ffffff;
}
.bnt-sire:hover {
  border: 1px solid #b5bd00;
  background: #dadf7d;
}
</style>
