<template>
  <b-container fluid class="px-5 mb-5">
    <b-row>
       <b-col cols="12">
        <b-breadcrumb :items="items"></b-breadcrumb>
       </b-col>
        <b-col cols="2">
            Fecha
            <input type="date" class="form-control" v-model="fechaInicio">
        </b-col>
        <b-col cols="2">
            Fecha
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
        <b-col cols="4" class="pt-4">
            <b-button variant="info" @click="generarReporte()"><i class="fa fa-search" aria-hidden="true"></i> Generar</b-button>
            <b-button variant="secondary" @click="descargarTxt()"> <i class="fa fa-file" aria-hidden="true"></i> Text </b-button>
            <b-button variant="success" @click="exportarReporte()"><i class="fa fa-file-excel" aria-hidden="true"></i> Exportar</b-button>
        </b-col>
        <b-col cols="12" class="mt-4 table-container">
            <table id="miTabla" class="table table-hover table-striped ">
                <thead>
                    <tr>
                      <th>Empresa</th>
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
                    <tr v-for="(reporte,index) in lstReporte" :key="index" @click="abrirModalDetalle(reporte)" :class="{'clsAnulado': reporte.Anulado == 1}">
                        <td style="text-align: center;">{{reporte.empresa}}</td>
                        <td style="text-align: center;">{{reporte.idDocVenta}}</td>
                        <td>{{reporte.UsuarioReg}}</td>
                        <td style="text-align: center;">{{reporte.FechaDoc}}</td>
                        <td style="text-align: center;">{{reporte.CodSunat}}</td>
                        <td style="text-align: center;">{{reporte.TipoDoc}}</td>
                        <td style="text-align: center;">{{reporte.Anulado ==1 ? 'Anulado' :'Activo' }}</td>
                        <td style="text-align: center;">{{reporte.Serie}}</td>
                        <td style="text-align: center;">{{reporte.Numero}}</td>
                        <td style="text-align: center;">{{reporte.SubTotal}}</td>
                        <td style="text-align: center;">{{reporte.Igv}}</td>
                        <td style="text-align: center;">{{reporte.Total}}</td>
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
        
          <table class="table table-hover table-striped" width="100%">
            <thead>
              <th>Id</th>
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


    <b-modal v-model="tipoExcel" size="lg">
	
				<div class="row">
					<table class="table table-bordered table-striped">
						<tbody>
							<tr>
								<td>Exportar todos los tipos</td>
								<td>
									Se exporta <b>TODOS</b> los tipos de venta.
								</td>
								<td>
									<button class="btn btn-success" @click="exportarPorTipoReporte({todos:1})" style="width: -webkit-fill-available;">Exportar Todos</button>
								</td>
							</tr>
							<tr>
								<td>Exportar VALES DE CREDITO Y CANJES</td>
								<td>
									Se exporta solo <b>VALES DE CREDITO Y CANJES</b>.
								</td>
								<td>
									<button class="btn btn-success" @click="exportarPorTipoReporte({canjes:1})" style="width: -webkit-fill-available;">Expotar Vales y Canjes</button>
								</td>
							</tr>
							<tr>
								<td>Exportar solo tipos electronicos <b>(PARA EL CONTADOR)</b></td>
								<td>
									Se exporta solo documentos electronicos <b>(BOLETAS ELEC., FACTURAS ELEC. Y NOTAS DE CRÉDITO ELEC.).</b>
								</td>
								<td>
									<button class="btn btn-success" @click="exportarPorTipoReporte({electronicos:1})" style="width: -webkit-fill-available;">Exportar Electronicos Fox</button>
								</td>
							</tr>
						</tbody>
					</table>
					<div style="margin:4px">
						<label style="color:red">IMPORTANTE:</label><br>
						<ul>
							<li style="margin: 3px">
							El sistema solo emite documentos electronicos a SUNAT <strong>(BOLETAS ELEC., FACTURAS ELEC. Y NOTAS DE CRÉDITO ELEC.).</strong>
							</li>
							<li style="margin: 3px">
							Las <strong>NOTAS DE VENTA, VALES DE CREDITO Y CANJES</strong> son para control interno estás no se declaran, por favor informar a su contador.
							</li>
						</ul>
					</div>
				</div>
		
	</div>
</b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      tipoExcel: false,
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
      .startOf("month")
      .format("YYYY-MM-DD");
    this.fechaFin = this.$moment()
      .endOf("month")
      .format("YYYY-MM-DD");
    this.obtenerAlmance();
    this.generarReporte();
  },
  methods: {
    descargarTxt() {
      const tabla = document.getElementById("miTabla");
      let txt = "";

      for (let i = 1; i < tabla.rows.length; i++) {
        const celdas = tabla.rows[i].cells;
        const fila = [];

        for (let j = 0; j < celdas.length; j++) {
          fila.push(celdas[j].innerText.trim());
        }

        txt += fila.join("|") + "|\n";
      }

      const blob = new Blob([txt], { type: "text/plain;charset=utf-8" });
      const url = URL.createObjectURL(blob);

      const a = document.createElement("a");
      a.href = url;
      a.download = "reporte.txt";
      a.click();

      URL.revokeObjectURL(url);
    },
    async abrirModalDetalle(item) {
      this.$swal({
        title: "Procesando...",
        text: "Por favor espera",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });
      this.title = "Detalle Reg. Ventas:";
      this.title = `${this.title} ${item.empresa} :  ${item.Serie}-${
        item.Numero
      }`;
      await this.axios(`${this.API}reporte/ListarRegVenta/detalle`, {
        params: {
          idDocVenta: item.idDocVenta,
          empresa: item.tipoEmpresa
        }
      }).then(res => {
        if (res.data) {
          this.detalle = res.data;
        }
      });
      this.$swal.close();
      this.dialogDetalle = true;
    },
    eliminarReg(item) {
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
    async exportarReporte() {
      this.tipoExcel = true;
    },
    async exportarPorTipoReporte({ todos = 0, electronicos = 0, canjes = 0 }) {
      this.$swal({
        title: "Procesando...",
        text: "Por favor espera",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });
      let params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen,
        todos: todos,
        electronicos: electronicos,
        canjes: canjes
      };
      await this.axios(`${this.API}reporte/ListarRegVenta/excel`, {
        params: params
      }).then(res => {
        let url = res.data;
        url = url.replace(/\\/g, "");
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
          url = "http://" + url; // O "https://" si es necesario
        }
        window.open(url, "_blank");
      });
      this.$swal.close();
    },
    async generarReporte() {
      this.$swal({
        title: "Procesando...",
        text: "Por favor espera",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });

      this.lstReporte = [];
      let params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen
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
      this.$swal.close();
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

<style scoped>
.modalRegVentaDetalle .modal-dialog {
  max-width: 90% !important;
  width: 90% !important;
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
</style>
