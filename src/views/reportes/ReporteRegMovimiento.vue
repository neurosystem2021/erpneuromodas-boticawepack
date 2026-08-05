<template>
  <b-container fluid class="movement-report-page">
    <header class="movement-report-hero"><div class="movement-report-hero__icon"><i class="fas fa-exchange-alt"></i></div><div><small>CONTROL DE INVENTARIO</small><h1>Registro de movimientos</h1><p>Consulta ingresos y salidas, revisa importes y genera reportes del periodo.</p></div><span><i class="fas fa-chart-line"></i> Reporte operativo</span></header>
    <b-row class="movement-filter-card">
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
          <b-form-checkbox
            class="mt-3"
            id="checkbox-1"
            v-model="declarado"
            name="checkbox-1"
            value="true"
            unchecked-value="false"
          >
            Declarado
          </b-form-checkbox>

        </b-col>
        <b-col cols="3">
          Buscar detalle del movimiento (buscar serie)
            <input type="text" class="form-control" v-model="descripcion">
        </b-col>
        <b-col cols="3" class="pt-4">
            <b-button variant="info" @click="generarReporte()"><i class="fa fa-search" aria-hidden="true"></i> Generar</b-button>
            <b-button variant="success" :disabled="activarBotones" @click="exportarReporte()"><i class="fa fa-file-excel" aria-hidden="true"></i> Excel</b-button>
            <b-button variant="secondary" :disabled="activarBotones" @click="exportarXls()"><i class="fa fa-file" aria-hidden="true"></i> Text</b-button>
        </b-col>
        <b-col cols="12" class="movement-table-card">
          <div class="movement-section-title"><div><small>RESULTADOS</small><h2>Movimientos encontrados</h2></div><span>{{ lstReporte.length }} registros</span></div>
          <div class="movement-table-scroll">
            <table ref="miTabla" class="table table-hover table-striped ">
                <thead>
                    <tr>
                        <th># Movimiento</th>
                        <th>Vendedor</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>FechaMov</th>
                        <th># Tipo</th>
                        <th>Tipo</th>
                        <th>Serie</th>
                        <th>Numero</th>
                        <th>Proveedor</th>
                        <th style="text-align: center;">SubTotal</th>	
                        <th style="text-align: center;">ISC</th>	
                        <th style="text-align: center;">IGV</th>	
                        <th style="text-align: center;">FLETE</th>	
                        <th style="text-align: center;">PERCEPCION</th>	
                        <th style="text-align: center;">MONEDA</th>	
                        <th style="text-align: center;">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(reporte,index) in lstReporte" :key="index" @click="abrirDedetalle(reporte)">
                        <td style="text-align: center;">{{reporte.IdMovimiento}}</td>
                        <td>{{reporte.UsuarioReg}}</td>
                        <td>{{reporte.AlmacenOrigen}}</td>
                        <td>{{reporte.AlmacenDestino}}</td>
                        <td>{{reporte.MovimientoFecha}}</td>
                        <td>{{reporte.IdMovimientoTipo}}</td>
                        <td>{{reporte.TipoMovimiento}}</td>
                        <td>{{reporte.Serie}}</td>
                        <td>{{reporte.Numero}}</td>
                        <td>{{reporte.Proveedor}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.SUBTOTAL).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.ISC).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.IGV).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.FLETE).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.Percepcion).toFixed(2)}}</td>
                        <td style="text-align: center;">{{reporte.Moneda}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.TOTAL).toFixed(2)}}</td>
                        <td>
                          <b-button size="sm" class="mx-1 my-1" @click="elimimnarRegistro(reporte)" variant="danger"><i class="fa fa-trash" aria-hidden="true"></i></b-button>
                          <b-button size="sm" class="mx-1 my-1" @click="imprimirMovimiento(reporte)" variant="info"><i class="fa fa-print" aria-hidden="true"></i></b-button>
                        </td>
                    </tr>
                </tbody>
            </table>
          </div>
        </b-col>
        <b-col cols="12" class="movement-totals-card">
            <b-row class="justify-content-end">
              <b-col cols="6"> <b>Sub Total(PEN)</b> <input type="number" class="form-control" v-model="SubTotalPEN" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>Sub Total(USD)</b> <input type="number" class="form-control" v-model="SubTotalUSD" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>IGV(PEN)</b> <input type="number" class="form-control" v-model="IgvlPEN" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>IGV(USD)</b> <input type="number" class="form-control" v-model="IgvlUSD" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>Total(PEN)</b> <input type="number" class="form-control" v-model="TotalPEN" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>Total(USD)</b> <input type="number" class="form-control" v-model="TotalUSD" readonly name="" id=""></b-col>
            </b-row>
        </b-col>
    </b-row>
    <b-modal v-model="detalleMovimiento" id="modalxl" modal-class="movement-detail-modal" size="lg" no-close-on-backdrop no-close-on-esc hide-footer title="Detalle del movimiento">
        <b-row>
          <b-col cols="6">
            <b-input-group>
              <input type="text" class="form-control" v-model="FechaPeriodoTributario">
              <b-button variant="warning" @click="actualizarPeriodo()">Actualizar Periodo</b-button>
            </b-input-group>
          </b-col>
          <b-col cols="6">
            Periodo Tributario:
            El valor debe ser AAAAMM
          </b-col>
          

          <b-col cols="12" class="mt-2">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Cod. Barra</th>
                  <th>Marca</th>
                  <th>Producto</th>
                  <th>Descripcion</th>
                  <th>Modelo</th>
                  <th>Marca</th>
                  <th>Talla</th>
                  <th>Color</th>
                  <th>Genero</th>
                  <th>Botapie/ Manga</th>
                  <th>Medición</th>
                  <th>Cantidad</th>
                  <th>Precio</th>
                  <th>IGV</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(detalle,index) in lstDetalle" :key="index" >
                  <td>{{ detalle.hashMovimiento }}</td>
                  <td>{{ detalle.CodigoBarra }}</td>
                  <td>{{ detalle.ProductoMarca }}</td>
                  <td>{{ detalle.Producto }}</td>
                  <td>{{ detalle.Descripcion }}</td>
                  <td>{{ detalle.ProductoModelo }}</td>
                  <td>{{ detalle.ProductoMarca }}</td>
                  <td>{{ detalle.ProductoTalla }}</td>
                  <td>{{ detalle.Color }}</td>
                  <td>{{ detalle.Genero }}</td>
                  <td>{{ detalle.Botapie }}</td>
                  <td>{{ detalle.ProductoMedicion }}</td>
                  <td>{{ detalle.Cantidad }}</td>
                  <td>{{ detalle.Precio }}</td>
                  <td>{{ montoIGV(detalle)}}</td>
                  <td>{{ montoTotal(detalle)}}</td>
                </tr>
              </tbody>
            </table>
          </b-col>
          <b-col cols="12" style="text-align: end;">
            <b-button variant="warning" @click="generarCodigoBarra()">Código de Barra</b-button>
            <b-button variant="danger" @click="detalleMovimiento=!detalleMovimiento">Cerrar</b-button>
          </b-col>
        </b-row>
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      detalleMovimiento: false,
      fechaInicio: "",
      fechaFin: "",
      descripcion: "",
      declarado: true,
      API: config.API,
      lstReporte: [],
      lstDetalle: [],
      items: [
        {
          text: "MOVIMIENTO",
          href: "#"
        },
        {
          text: "REPORTE",
          href: "#"
        },
        {
          text: "REGISTRO DE MOVIMIENTO",
          active: true
        }
      ],
      header: [
        { key: "IdProducto" },
        { key: "Producto" },
        { key: "Cantidad" },
        { key: "PrecioCosto" },
        { key: "PrecioVenta" },
        { key: "TotalVenta" },
        { key: "TotalCosto" },
        { key: "UtilidadBruta" },
        { key: "PorcentajeUti" }
      ],
      lstAlmances: [],
      SubTotalPEN: 0,
      SubTotalUSD: 0,
      IgvlPEN: 0,
      IgvlUSD: 0,
      TotalPEN: 0,
      TotalUSD: 0,
      FechaPeriodoTributario: "",
      IdMovimiento: ""
    };
  },
  mounted() {
    this.fechaInicio = this.$moment()
      .startOf("month")
      .format("YYYY-MM-DD");
    this.fechaFin = this.$moment()
      .endOf("month")
      .format("YYYY-MM-DD");
    this.generarReporte();
    // this.obtenerAlmance();
  },
  methods: {
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
    imprimirMovimiento(reporte) {
      window.open(
        `/imprimir/index.php?hashMovimiento=${reporte.IdMovimiento}`,
        "_blank"
      );
    },
    generarCodigoBarra(reporte) {
      window.open(`/views/codigoBarra.php?hash=${this.IdMovimiento}`, "_blank");
    },
    async exportarXls() {
      const table = this.$refs.miTabla; // Obtener la referencia de la tabla
      let txt = "";

      // Recorrer las filas del tbody
      table.querySelectorAll("tbody tr").forEach(tr => {
        let rowData = [];
        tr.querySelectorAll("td").forEach(td => {
          rowData.push(td.textContent.trim()); // Obtener el texto y limpiar espacios
        });
        txt += rowData.join("|") + "\r\n"; // Separar con | y agregar salto de línea
      });

      // Crear un Blob con los datos en formato TXT
      const blob = new Blob([txt], { type: "text/plain" });

      // Crear un enlace para descargar el archivo
      const a = document.createElement("a");
      a.href = URL.createObjectURL(blob);
      a.download = "tabla_exportada.txt";
      a.click();

      // Liberar el objeto URL
      URL.revokeObjectURL(a.href);
    },
    async exportarReporte() {
      await await this.axios(
        `${this.API}reporte/movimientos?fechaInicio=${this.fechaInicio}&fechaFin=${
          this.fechaFin
        }&descripcion=${this.descripcion}&declarado=${this.declarado}`
      ).then(res => {
        window.open(res.data.link, "_blank");
      });

      // window.open(
      //   `/views/ReporteExcel6.php?&fechaIni=${this.fechaInicio}&fechaFin=${
      //     this.fechaFin
      //   }&descripcion=${this.descripcion}&declarado=${this.declarado}`,
      //   "_blank"
      // );
    },

    elimimnarRegistro(item) {
      this.$swal({
        icon: "question",
        title: "Eliminar",
        text: `Desea eliminar el registro ${item.Serie}-${item.Numero} `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showCancelButton: true,
        cancelButtonColor: "#d33",
        confirmButtonText: "Si,Eliminar",
        cancelButtonText: "Cancelar"
      }).then(async respuesta => {
        if (respuesta.isConfirmed) {
          await this.axios.post(`${this.API}movimiento/eliminar`, {
            idMov: item.IdMovimiento
          });
          this.generarReporte();
        }
      });
      // console.log(item);
    },
    async generarReporte() {
      let params = {
        fechaInicio: this.fechaInicio,
        fechaFin: this.fechaFin,
        descripcion: this.descripcion,
        declarado: this.declarado
      };
      await this.axios(`${this.API}reporte/listarregmov`, {
        params: params
      }).then(res => {
        if (res.data) {
          let data = res.data;
          this.lstReporte = data;
          let SubTotalPEN = 0;
          let SubTotalUSD = 0;
          let IgvlPEN = 0;
          let IgvlUSD = 0;
          let TotalPEN = 0;
          let TotalUSD = 0;
          data.forEach(element => {
            if (element.Moneda == "PEN") {
              SubTotalPEN += element.SUBTOTAL;
              IgvlPEN += element.IGV;
              TotalPEN += element.TOTAL;
            }
            if (element.Moneda == "USD") {
              SubTotalUSD = element.SUBTOTAL;
              IgvlUSD = element.IGV;
              TotalUSD = element.TOTAL;
            }
          });
          this.SubTotalPEN = parseFloat(SubTotalPEN).toFixed(2);
          this.SubTotalUSD = parseFloat(SubTotalUSD).toFixed(2);
          this.IgvlPEN = parseFloat(IgvlPEN).toFixed(2);
          this.IgvlUSD = parseFloat(IgvlUSD).toFixed(2);
          this.TotalPEN = parseFloat(TotalPEN).toFixed(2);
          this.TotalUSD = parseFloat(TotalUSD).toFixed(2);
        }
      });
    },
    async abrirDedetalle(reporte) {
      console.log(reporte);
      this.FechaPeriodoTributario = reporte.FechaPeriodoTributario;
      this.IdMovimiento = reporte.IdMovimiento;
      this.lstDetalle = [];
      let params = {
        IdMovimiento: reporte.IdMovimiento
      };
      await this.axios(`${this.API}movmiento/detalles`, {
        params: params
      }).then(res => {
        if (res.data) {
          this.lstDetalle = res.data;
        }
      });
      this.detalleMovimiento = true;
    },
    async actualizarPeriodo() {
      const regex = /^(20[0-9]{2})(0[1-9]|1[0-2])$/;
      const match = this.FechaPeriodoTributario.match(regex);

      if (!match) {
        this.$toasted.error(
          "El Periodo Tributario debe ser un año entre 2000 y 2099 con un mes válido (Ejemplo: 202403)."
        );
        return false;
      }
      let params = {
        FechaPeriodoTributario: this.FechaPeriodoTributario,
        IdMovimiento: this.IdMovimiento
      };
      await this.axios
        .post(`${this.API}movmiento/actualizarperiodo`, params)
        .then(res => {
          if (res.data) {
            if (res.data.updated) {
              this.detalleMovimiento = false;
              this.$swal({
                icon: "success",
                title: "Actualización Correcta"
              });
            }
          }
        });
      // this.detalleMovimiento = false;
    },
    montoIGV(item) {
      let monto = 0;
      if (item.TieneIgv == 1) {
        monto = item.Cantidad * item.Precio * 0.18;
      }
      return parseFloat(monto).toFixed(2);
    },
    montoTotal(item) {
      let igv = 0;
      let monto = 0;
      if (item.TieneIgv == 1) {
        igv = item.Cantidad * item.Precio * 0.18;
      }
      monto = item.Cantidad * item.Precio + igv;
      return parseFloat(monto).toFixed(2);
    }
  },
  computed: {
    activarBotones() {
      if (this.lstReporte.length == 0) {
        return true;
      }
      return false;
    }
  }
};
</script>

<style>
.movement-report-page{min-height:100vh;padding:14px 24px 34px!important;color:#173451;background:linear-gradient(145deg,#f3f8fc,#eaf3f9 60%,#f8fbfd)}
.movement-report-hero{display:flex;align-items:center;gap:14px;min-height:92px;margin-bottom:14px;padding:16px 22px;color:#fff;border-radius:20px;background:linear-gradient(112deg,#083562,#0b5787 65%,#078bac);box-shadow:0 14px 32px rgba(11,68,111,.18)}.movement-report-hero__icon{display:grid;width:52px;height:52px;place-items:center;flex:0 0 52px;color:#9fe9ff;border:1px solid rgba(255,255,255,.22);border-radius:15px;background:rgba(255,255,255,.1);font-size:20px}.movement-report-hero small{color:#a9eaff;font-size:10px;font-weight:900;letter-spacing:1.4px}.movement-report-hero h1{margin:0;font-size:27px;font-weight:900}.movement-report-hero p{margin:2px 0 0;color:#d8edf8;font-size:12px}.movement-report-hero>span{margin-left:auto;padding:9px 14px;border:1px solid rgba(255,255,255,.2);border-radius:999px;background:rgba(0,35,67,.2);font-size:11px;font-weight:800}.movement-report-hero>span i{margin-right:6px;color:#67e0ab}
.movement-filter-card{margin:0;padding:16px 12px 0;border:1px solid #d7e5ef;border-radius:19px;background:#fff;box-shadow:0 10px 28px rgba(28,70,105,.08)}.movement-filter-card>.col-12:first-child{display:none}.movement-filter-card>.col-2,.movement-filter-card>.col-3{margin-bottom:16px;color:#29465f;font-size:12px;font-weight:850}.movement-filter-card .form-control{min-height:46px;margin-top:5px;color:#173451;border:1px solid #cedeea;border-radius:11px;background:#fbfdff;font-weight:700;box-shadow:none}.movement-filter-card .form-control:focus{border-color:#168fc7;box-shadow:0 0 0 4px rgba(22,143,199,.12)}.movement-filter-card .custom-checkbox{padding-top:12px;font-size:13px}.movement-filter-card>.col-3.pt-4{display:flex;align-items:end;gap:7px;padding-top:23px!important}.movement-filter-card>.col-3.pt-4 .btn{min-height:46px;margin:0;border:0;border-radius:11px;font-size:12px;font-weight:900;box-shadow:0 7px 16px rgba(20,80,118,.14)}.movement-filter-card>.col-3.pt-4 .btn-info{background:linear-gradient(135deg,#0a5b8c,#0889b2)}.movement-filter-card>.col-3.pt-4 .btn-success{background:linear-gradient(135deg,#078653,#18b46f)}
.movement-table-card{overflow:hidden;margin-top:2px;padding:0!important;border-top:1px solid #dfebf2}.movement-section-title{display:flex;align-items:center;padding:15px 15px 12px}.movement-section-title small{color:#0785bd;font-size:9px;font-weight:900;letter-spacing:1.3px}.movement-section-title h2{margin:0;color:#153b5b;font-size:19px;font-weight:900}.movement-section-title>span{margin-left:auto;padding:7px 12px;color:#0b638e;border-radius:999px;background:#e7f5fb;font-size:10px;font-weight:900}.movement-table-scroll{overflow:auto}.movement-table-card table{min-width:1550px;margin:0!important}.movement-table-card thead th{position:sticky;top:0;padding:12px 10px;color:#fff;border:0;background:#0b5787;white-space:nowrap;font-size:11px}.movement-table-card tbody td{padding:11px 10px;vertical-align:middle;border-color:#e2ebf1;font-size:11px}.movement-table-card tbody tr{cursor:pointer}.movement-table-card tbody tr:hover{background:#eaf7fc!important}.movement-table-card tbody .btn{width:34px;height:34px;padding:0;border:0;border-radius:9px}.movement-totals-card{padding:16px 14px 18px;border-top:1px solid #deebf2;background:#f7fafc}.movement-totals-card .col-6{max-width:16.666%;flex:0 0 16.666%;color:#36536b;font-size:10px}.movement-totals-card .form-control{min-height:42px;color:#0a5c86;background:#fff;font-size:13px;font-weight:900}.movement-detail-modal .modal-dialog{max-width:90%!important;width:90%!important}.movement-detail-modal .modal-content{overflow:hidden;border:0;border-radius:20px;box-shadow:0 25px 65px rgba(7,39,70,.3)}.movement-detail-modal .modal-header{color:#fff;border:0;background:linear-gradient(110deg,#083562,#0b6d9b)}.movement-detail-modal .modal-title{font-weight:900}.movement-detail-modal .modal-body{padding:18px;background:#f6f9fc}.movement-detail-modal table thead th{color:#fff;background:#0b5787;white-space:nowrap}.movement-detail-modal .modal-body>.row>.col-12{overflow:auto}
@media(max-width:991px){.movement-report-page{padding:10px!important}.movement-report-hero>span{display:none}.movement-filter-card>.col-2,.movement-filter-card>.col-3{max-width:50%;flex:0 0 50%}.movement-totals-card .col-6{max-width:33.333%;flex:0 0 33.333%}}
@media(max-width:575px){.movement-report-hero p{display:none}.movement-report-hero h1{font-size:21px}.movement-filter-card>.col-2,.movement-filter-card>.col-3{max-width:100%;flex:0 0 100%}.movement-totals-card .col-6{max-width:50%;flex:0 0 50%}}
</style>
