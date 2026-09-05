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
        <b-col cols="1">
            Hora Inicio
            <input type="time" class="form-control" v-model="horaInicio">
        </b-col>

        <b-col cols="2">
            Fecha Fin
            <input type="date" class="form-control" v-model="fechaFin">
        </b-col>
        <b-col cols="1">
            Hora Fin
            <input type="time" class="form-control" v-model="horaFin">
        </b-col>
        <b-col cols="3">
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
        <b-col cols="2" class="pt-4">
            <b-button variant="info" @click="currentPage = 1; generarReporte()"><i class="fa fa-search" aria-hidden="true"></i> Generar</b-button>
            <b-button variant="success" @click="exportarReporte()"><i class="fa fa-file-excel" aria-hidden="true"></i> Exportar</b-button>
        </b-col>

        <!-- Búsqueda rápida por Serie / Número -->
        <b-col cols="2" class="mt-2">
            Serie
            <input
              type="text"
              class="form-control"
              v-model="serieBusqueda"
              placeholder="Ej: B157"
              @keyup.enter="currentPage = 1; generarReporte()"
            >
        </b-col>
        <b-col cols="2" class="mt-2">
            Número
            <input
              type="text"
              class="form-control"
              v-model="numeroBusqueda"
              placeholder="Ej: 23091"
              @keyup.enter="currentPage = 1; generarReporte()"
            >
        </b-col>
        <b-col cols="2" class="mt-2 pt-4">
            <b-button variant="secondary" @click="limpiarBusqueda">
              <i class="fa fa-times" aria-hidden="true"></i> Limpiar búsqueda
            </b-button>
        </b-col>

        <b-col cols="12" class="mt-4 table-container">

          <div class="sticky-paginacion">
          <b-row class="mt-3">

              <b-col md="6">

                <b-pagination
                  v-model="currentPage"
                  :total-rows="totalRows"
                  :per-page="perPage"
                  @input="generarReporte"
                ></b-pagination>

              </b-col>

              <b-col md="3">

                <b-form-select
                  v-model="perPage"
                  :options="[50,100,200]"
                  @change="changePerPage"
                ></b-form-select>

              </b-col>

              <b-col md="3" class="text-right pt-2">

                Total registros: {{ totalRows }}

              </b-col>

            </b-row>
            </div>
            <table class="table table-hover table-striped ">
                <thead>
                    <tr>
                      <th>Id</th>
                      <th>Vendedor</th>
                      <th>Tipo Doc</th>
                      <th>Nro. Documento</th>
                      <th>FECHA EMIS.</th>
                      <th>FECHA VENCE</th>
                      <th>RUC DNI</th>
                      <th>RUC</th>
                      <th>CLIENTE</th>
                      <th>VTA GRAB</th>
                      <th>IMPUESTO</th>
                      <th>VTA NOGRAB</th>
                      <th>TOTAL VTA</th>
                      <th>COND PAGO</th>
                      <th>FORMA PAGO</th>
                      <th>BANCO</th>
                      <th>NRO OPE</th>
                      <th>DIAS CREDITO</th>
                      <th>DOC REF</th>
                      <th>SERIE REF</th>
                      <th>NRO REF</th>
                      <th>ESTADO</th>
                      <th></th>

                    </tr>
                </thead>
                <tbody class="scrollable-tbody">
                    <tr v-for="(reporte,index) in lstReporte" :key="index" @click="abrirModalDetalle(reporte)" :class="{'clsAnulado': reporte.Anulado == 1}">
                        <td style="text-align: center;">{{reporte.idDocVenta}}</td>
                        <td>{{reporte.UsuarioReg}}</td>
                        <td style="text-align: center;">{{reporte.TipoDoc}}</td>
                        <td style="text-align: center;">{{reporte.Serie}} - {{ reporte.Numero }}</td>
                        <td style="text-align: center;">{{reporte.FechaDoc}}</td>
                        <td style="text-align: center;">{{reporte.EsCredito ? reporte.FechaCredito : ''}}</td>
                        <td style="text-align: center;">{{reporte.DniRuc}}</td>
                        <td style="text-align: center;"></td>
                        <td style="text-align: center;">{{ reporte.Cliente }}</td>
                        <td style="text-align: center;">{{reporte.Total}}</td>
                        <td style="text-align: center;">{{reporte.Igv}}</td>
                        <td style="text-align: center;">{{reporte.Total}}</td>
                        <td style="text-align: center;">{{reporte.Total}}</td>
                        <td style="text-align: center;">{{reporte.EsCredito ==1  ? 'Crédito' : 'Contado'}}</td>
                        <td style="text-align: center;">
                          
                            {{  
                                Number(reporte.Efectivo) >0 ? 'Efectivo' : 
                                Number(reporte.Visa) >0 ? reporte.VisaDesc : 
                                Number(reporte.Mastercard) >0 ? reporte.MastercardDesc : 
                                Number(reporte.Deposito) >0 ? reporte.DepositoDesc : 
                                Number(reporte.Transferencia) >0 ? reporte.TransferenciaDesc : ''
                            }}</td>
                         <td style="text-align: center;"></td>
                         <td style="text-align: center;"></td>
                         <td style="text-align: center;">{{ reporte.DiasCredito }}</td>
                         <td style="text-align: center;"></td>
                         <td style="text-align: center;"></td>
                         <td style="text-align: center;"></td>


                        
                        <td style="text-align: center;">{{reporte.Anulado ==1 ? 'Anulado' :'Activo' }}</td>
                        <td> <i v-if="reporte.Anulado ==0" class="fa fa-trash" style="color:red; cursor: pointer;" aria-hidden="true" @click.stop="eliminarReg(reporte)"></i> </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: #006064; color: #ffffff; font-weight: bold;">
                        <td colspan="12">Total</td>
                        <td style="text-align: center;">{{footerTotal}}</td>
                        <td colspan="1"></td>
                        <td colspan="9"></td>
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
      horaInicio: "00:00",
      horaFin: "23:59",
      almacen: 1,
      API: config.API,
      lstReporte: [],
      declarado: 1,
      // NUEVO: filtros de búsqueda rápida
      serieBusqueda: "",
      numeroBusqueda: "",
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
      detalle: [],
      totalRows: 0,
      perPage: 50,
      currentPage: 1,
    };
  },
  mounted() {

    // SOLO HOY
    this.fechaInicio = this.$moment()
      .format("YYYY-MM-DD");

    this.fechaFin = this.$moment()
      .format("YYYY-MM-DD");
    this.obtenerAlmance();
    this.generarReporte();
    // NO GENERAR AUTOMÁTICAMENTE
  },
  methods: {
    // NUEVO: limpia la búsqueda por Serie/Número y vuelve a generar con el rango de fechas normal
    limpiarBusqueda() {
      this.serieBusqueda = "";
      this.numeroBusqueda = "";
      this.currentPage = 1;
      this.generarReporte();
    },
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
            .put(`${this.API}/reporte/ListarRegVenta/anular`, data)
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
      this.$swal({
        title: "Generando Excel",
        text: "Un momento por favor...",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });
      let params = {
          fechaIni: this.fechaInicio,
          horaIni: this.horaInicio,
          fechaFin: this.fechaFin,
          horaFin: this.horaFin,
          declarado: this.declarado,
          almacen: this.almacen,
          empresa: sessionStorage.getItem("empresa"),
          // NUEVO: filtros de búsqueda
          serie: this.serieBusqueda,
          numero: this.numeroBusqueda
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
        title: "Generando reporte...",
        text: "Un momento por favor",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });
      this.lstReporte = [];
      let params = {
        fechaIni: this.fechaInicio,
        horaIni: this.horaInicio,
        fechaFin: this.fechaFin,
        horaFin: this.horaFin,
        declarado: this.declarado,
        almacen: this.almacen,
        empresa: sessionStorage.getItem("empresa"),
        page: this.currentPage,
        limit: this.perPage,
        // NUEVO: filtros de búsqueda por Serie / Número
        serie: this.serieBusqueda,
        numero: this.numeroBusqueda
      };
      await this.axios(`${this.API}reporte/ListarRegVenta`, {
        params: params
      }).then(res => {
        if (res.data) {
          let data = res.data.data;
          this.lstReporte = data;
          this.totalRows = res.data.total;
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
  top: 50px;
  background: #f8f9fa;
  z-index: 998;
}

tfoot td {
  bottom: 0;
  background: #006064;
  color: white;
  font-weight: bold;
}

.sticky-paginacion{
  position: sticky;

  top: 0px;

  z-index: 999;

  background: white;

  padding: 0px 0;

  border-bottom: 1px solid #ddd;
  width: 100.7%;
  left: 0;
  margin: 0;
}
</style>