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
                          <b-button v-if="idPerfil != 3 && esEditable(reporte)" size="sm" class="mx-1 my-1" @click.stop="abrirEditar(reporte)" variant="warning" title="Editar"><i class="fa fa-edit" aria-hidden="true"></i></b-button>
                          <b-button v-if="idPerfil != 3" size="sm" class="mx-1 my-1" @click.stop="elimimnarRegistro(reporte)" variant="danger"><i class="fa fa-trash" aria-hidden="true"></i></b-button>
                          <b-button size="sm" class="mx-1 my-1" @click.stop="imprimirMovimiento(reporte)" variant="info"><i class="fa fa-print" aria-hidden="true"></i></b-button>
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
    <b-modal
      v-model="detalleMovimiento"
      no-close-on-backdrop
      no-close-on-esc
      hide-footer
      title-class="w-100"
    >
      <template #modal-title>
        <div class="audit-header">
          <h5 class="audit-titulo">
            <i class="fa fa-file-alt" aria-hidden="true"></i> Detalle del movimiento
          </h5>

          <div class="audit-cards">
            <!-- Registro -->
            <div class="audit-card audit-reg">
              <div class="audit-icon"><i class="fa fa-user-plus" aria-hidden="true"></i></div>
              <div class="audit-info">
                <div class="audit-label">Registrado por</div>
                <div class="audit-user">{{ auditoria.usuarioReg || "-" }}</div>
                <div class="audit-fecha">
                  <i class="far fa-clock" aria-hidden="true"></i> {{ auditoria.fechaReg || "-" }}
                </div>
              </div>
            </div>

            <!-- Edición -->
            <div class="audit-card" :class="auditoria.usuarioMod ? 'audit-mod' : 'audit-sin-mod'">
              <div class="audit-icon"><i class="fa fa-user-edit" aria-hidden="true"></i></div>
              <div class="audit-info">
                <div class="audit-label">Última edición</div>
                <template v-if="auditoria.usuarioMod">
                  <div class="audit-user">{{ auditoria.usuarioMod }}</div>
                  <div class="audit-fecha">
                    <i class="far fa-clock" aria-hidden="true"></i> {{ auditoria.fechaMod || "-" }}
                  </div>
                </template>
                <div v-else class="audit-user audit-vacio">Sin modificaciones</div>
              </div>
            </div>
          </div>
        </div>
      </template>
      <br>
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
    <movimiento-edit-form ref="movimientoEdit" @guardado="generarReporte"></movimiento-edit-form>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
import MovimientoEditForm from "@/components/MovimientoEditForm.vue";
export default {
  components: { MovimientoEditForm },
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
      auditoria: {
        usuarioReg: "",
        fechaReg: "",
        usuarioMod: "",
        fechaMod: ""
      },
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
      IdMovimiento: "",
      idPerfil: sessionStorage.getItem("IdPerfil")
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
      window.open(
        `/views/ReporteExcel6.php?&fechaIni=${this.fechaInicio}&fechaFin=${
          this.fechaFin
        }&descripcion=${this.descripcion}&declarado=${this.declarado}`,
        "_blank"
      );
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
            const sub = parseFloat(element.SUBTOTAL) || 0;
            const igv = parseFloat(element.IGV) || 0;
            const tot = parseFloat(element.TOTAL) || 0;
            if (element.Moneda == "PEN") {
              SubTotalPEN += sub;
              IgvlPEN += igv;
              TotalPEN += tot;
            }
            if (element.Moneda == "USD") {
              SubTotalUSD += sub;
              IgvlUSD += igv;
              TotalUSD += tot;
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
      this.FechaPeriodoTributario = reporte.FechaPeriodoTributario;
      this.IdMovimiento = reporte.IdMovimiento;
      this.lstDetalle = [];
      this.auditoria = { usuarioReg: "", fechaReg: "", usuarioMod: "", fechaMod: "" };

      await this.axios(`${this.API}movmiento/detalles`, {
        params: { IdMovimiento: reporte.IdMovimiento }
      }).then(res => {
        if (res.data) this.lstDetalle = res.data;
      });

      // Auditoría (usuario que registró / modificó)
      try {
        const r = await this.axios(`${this.API}movimiento/obtener`, {
          params: { IdMovimiento: reporte.IdMovimiento }
        });
        const cab = r.data && r.data.cabecera;
        if (cab) {
          this.auditoria = {
            usuarioReg: cab.UsuarioReg || "",
            fechaReg: cab.FechaReg || "",
            usuarioMod: cab.UsuarioMod || "",
            fechaMod: cab.FechaMod || ""
          };
        }
      } catch (e) {
        console.error(e);
      }

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
    },
    esEditable(reporte) {
      return !!reporte.Proveedor;
    },
    abrirEditar(reporte) {
      this.$refs.movimientoEdit.abrir(reporte.IdMovimiento);
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

.audit-header { width: 100%; }
.audit-titulo {
  font-weight: 600;
  margin: 0 0 10px 0;
  color: #343a40;
}
.audit-titulo i { color: #6c757d; margin-right: 6px; }

.audit-cards {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}
.audit-card {
  display: flex;
  align-items: center;
  gap: 12px;
  flex: 1 1 260px;
  padding: 10px 14px;
  border-radius: 8px;
  border-left: 4px solid;
  background: #f8f9fa;
}
.audit-icon {
  width: 38px;
  height: 38px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: #fff;
  flex-shrink: 0;
}
.audit-label {
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6c757d;
  line-height: 1.2;
}
.audit-user {
  font-size: 1rem;
  font-weight: 600;
  color: #212529;
  line-height: 1.3;
}
.audit-fecha {
  font-size: 0.8rem;
  color: #6c757d;
}
.audit-fecha i { margin-right: 3px; }

.audit-reg { border-color: #17a2b8; background: #e8f6f8; }
.audit-reg .audit-icon { background: #17a2b8; }

.audit-mod { border-color: #ffc107; background: #fff8e1; }
.audit-mod .audit-icon { background: #e0a800; }

.audit-sin-mod { border-color: #ced4da; background: #f1f3f5; }
.audit-sin-mod .audit-icon { background: #adb5bd; }
.audit-vacio { font-weight: 400; font-style: italic; color: #868e96; font-size: 0.9rem; }
</style>
