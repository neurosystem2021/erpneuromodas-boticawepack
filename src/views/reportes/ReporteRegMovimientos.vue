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
        <b-col cols="12" class="mt-4">
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
                    <tr
                      v-for="(reporte,index) in lstReporte"
                      :key="index"
                      @click="abrirDedetalle(reporte)"
                      :class="{ 'fila-anulada': reporte.Anulado == 1 }"
                    >
                        <td style="text-align: center;">
                          {{reporte.IdMovimiento}}
                          <span v-if="reporte.Anulado == 1" class="badge badge-danger ml-1">ANULADO</span>
                        </td>
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
                          <b-button
                            size="sm"
                            class="mx-1 my-1"
                            @click.stop="anularRegistro(reporte)"
                            variant="danger"
                            :disabled="reporte.Anulado == 1"
                            :title="reporte.Anulado == 1 ? 'Movimiento ya anulado' : 'Anular movimiento'"
                          >
                            <i class="fa fa-ban" aria-hidden="true"></i>
                          </b-button>
                          <b-button size="sm" class="mx-1 my-1" @click.stop="imprimirMovimiento(reporte)" variant="info">
                            <i class="fa fa-print" aria-hidden="true"></i>
                          </b-button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </b-col>
        <b-col cols="3" offset="9">
            <b-row>
              <b-col cols="6"> <b>Sub Total(PEN)</b> <input type="number" class="form-control" v-model="SubTotalPEN" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>Sub Total(USD)</b> <input type="number" class="form-control" v-model="SubTotalUSD" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>IGV(PEN)</b> <input type="number" class="form-control" v-model="IgvlPEN" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>IGV(USD)</b> <input type="number" class="form-control" v-model="IgvlUSD" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>Total(PEN)</b> <input type="number" class="form-control" v-model="TotalPEN" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>Total(USD)</b> <input type="number" class="form-control" v-model="TotalUSD" readonly name="" id=""></b-col>
            </b-row>
        </b-col>
    </b-row>
    <b-modal v-model="detalleMovimiento" no-close-on-backdrop no-close-on-esc hide-footer>
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
                  <th>Medida</th>
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
    this.fechaInicio = this.$moment().format("YYYY-MM-DD");
    this.fechaFin = this.$moment().format("YYYY-MM-DD");
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
      window.open(
        `/views/ReporteExcel6.php?&fechaIni=${this.fechaInicio}&fechaFin=${
          this.fechaFin
        }&descripcion=${this.descripcion}&declarado=${this.declarado}`,
        "_blank"
      );
    },

    anularRegistro(item) {
      if (item.Anulado == 1) {
        this.$toasted.error("Este movimiento ya se encuentra anulado.");
        return;
      }
      this.$swal({
        icon: "warning",
        title: "Anular movimiento",
        text: `¿Desea anular el movimiento ${item.Serie}-${item.Numero}? Esta acción revertirá el stock pero conservará el registro.`,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showCancelButton: true,
        cancelButtonColor: "#d33",
        confirmButtonText: "Si, Anular",
        cancelButtonText: "Cancelar"
      }).then(async (respuesta) => {
        if (respuesta.isConfirmed) {
          try {
            await this.axios.post(`${this.API}movimiento/anular`, {
              idMov: item.IdMovimiento
            });

            this.$swal({
              icon: "success",
              title: "¡Anulado!",
              text: "El movimiento fue anulado correctamente",
              timer: 2500,
              showConfirmButton: false
            });

            // Refrescamos la tabla; el registro ahora aparece pintado, no eliminado
            this.generarReporte();

          } catch (error) {
            console.error(error);
            this.$swal({
              icon: "error",
              title: "Error",
              text: error.response && error.response.data && error.response.data.error
                ? error.response.data.error
                : "No se pudo anular el registro. Intente de nuevo."
            });
          }
        }
      });
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
.modal-dialog {
  max-width: 60% !important;
  width: 60% !important;
}

.fila-anulada {
  background-color: rgba(220, 53, 69, 0.08) !important;
  border-left: 5px solid #dc3545;
}

.fila-anulada td {
  color: #6c757d;
}

.fila-anulada:hover {
  background-color: rgba(220, 53, 69, 0.15) !important;
}
</style>
