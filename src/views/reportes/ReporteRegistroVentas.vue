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
        <b-col cols="2">
            Usuario
            <b-form-select v-model="usuarioSel"
                value-field="Usuario"
                text-field="Usuario"
                :options="lstUsuarios"
                :disabled="idPerfil == 3"></b-form-select>
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
            <b-button variant="warning" @click="exportarDetallado()"><i class="fa fa-file-excel" aria-hidden="true"></i> Contable</b-button>
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
                    <tr v-for="(reporte,index) in lstReporte" :key="index" @click="abrirModalDetalle(reporte)" :class="{'clsAnulado': reporte.Anulado == 1}">
                        <td style="text-align: center;">{{reporte.idDocVenta}}</td>
                        <td>{{reporte.UsuarioReg}}</td>
                        <td style="text-align: center;">{{reporte.FechaDoc}}</td>
                        <td style="text-align: center;">{{reporte.CodSunat}}</td>
                        <td style="text-align: center;">{{reporte.TipoDoc}}</td>
                        <td style="text-align: center;">{{reporte.Anulado ==1 ? 'Anulado' :'Activo' }}</td>
                        <td style="text-align: center;">{{reporte.Serie}}</td>
                        <td style="text-align: center;">{{reporte.Numero}}</td>
                        <td style="text-align: center;">{{reporte.SubTotal}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.Igv).toFixed(2)}}</td>
                        <td style="text-align: center;">{{reporte.Total}}</td>
                        <td> <i v-if="reporte.Anulado ==0 && idPerfil == 1" class="fa fa-trash" style="color:red; cursor: pointer;" aria-hidden="true" @click.stop="eliminarReg(reporte)"></i> </td>
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

    <!-- MODAL DETALLE -->
    <b-modal
      v-model="dialogDetalle"
      size="xl"
      scrollable
      hide-footer
      modal-class="modalRegVentaDetalle"
    >
      <template #modal-header="{ close }">
        <div class="detalle-header">
          <div>
            <div class="detalle-subtitulo">Detalle de registro de ventas</div>
            <h5 class="detalle-titulo">
              <i class="fa fa-file-invoice"></i> {{ tituloDetalle }}
            </h5>
          </div>
          <button type="button" class="detalle-cerrar" @click="close()">&times;</button>
        </div>
      </template>

      <div class="detalle-wrapper">
        <table class="detalle-table">
          <thead>
            <tr>
              <th class="text-center">Id</th>
              <th>Cod. Barra</th>
              <th>Producto</th>
              <th>Marca</th>
              <th>Medición</th>
              <th>Presentación</th>
              <th class="text-right">Cantidad</th>
              <th class="text-right">Unidades</th>
              <th class="text-right">Precio</th>
              <th class="text-right">Descuento</th>
              <th class="text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(det, index) in detalle" :key="index">
              <td class="text-center text-muted">{{ det.IdDocVenta }}</td>
              <td class="text-mono">{{ det.CodigoBarra }}</td>
              <td class="fw-600">{{ det.Producto }}</td>
              <td>{{ det.ProductoMarca }}</td>
              <td>{{ det.ProductoMedicion }}</td>
              <td>
                <span class="badge-pres">
                  {{ det.Presentacion }}
                  <span v-if="det.CantidadPresentacion > 1">(x{{ det.CantidadPresentacion }})</span>
                </span>
              </td>
              <td class="text-right">{{ det.Cantidad }}</td>
              <td class="text-right">{{ obtenerUnidades(det) }}</td>
              <td class="text-right">{{ Number(det.Precio).toFixed(2) }}</td>
              <td class="text-right">{{ Number(det.Descuento || 0).toFixed(2) }}</td>
              <td class="text-right fw-600">{{ obtenerTotal(det) }}</td>
            </tr>
            <tr v-if="!detalle.length">
              <td colspan="11" class="text-center text-muted py-4">Sin registros</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="10" class="text-right">TOTAL</td>
              <td class="text-right">{{ detalleTotal }}</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="detalle-acciones">
        <b-button variant="secondary" @click="dialogDetalle = false">
          <i class="fa fa-times"></i> Cerrar
        </b-button>
      </div>
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
      tituloDetalle: "",
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
      idPerfil: sessionStorage.getItem("IdPerfil"),
      usuarioReg: sessionStorage.getItem("User"),
      usuarioSel: sessionStorage.getItem("User"),
      lstUsuarios: []
    };
  },
  mounted() {
    this.fechaInicio = this.$moment().startOf("month").format("YYYY-MM-DD");
    this.fechaFin = this.$moment().endOf("month").format("YYYY-MM-DD");
    this.obtenerAlmance();
    this.obtenerUsuarios();
    this.generarReporte();
  },
  methods: {
    async abrirModalDetalle(item) {
      this.tituloDetalle = `${item.TipoDoc} ${item.Serie}-${item.Numero}`;
      this.detalle = [];
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
      if (item.ExisteNota == 1) {
        this.$swal({
          icon: "error",
          title: "Restricción de eliminación",
          text: "Existe una Nota de Crédito vinculada a este registro. "
        });
        return;
      }

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
      return parseFloat(total).toFixed(2);
    },
    async obtenerUsuarios() {
      if (this.idPerfil == 3) {
        this.lstUsuarios = [{ Usuario: this.usuarioReg }];
        this.usuarioSel = this.usuarioReg;
        return;
      }

      await this.axios(`${this.API}usuarios`, { params: { idPerfil: 3 } }).then(res => {
        if (res.data) {
          this.lstUsuarios = res.data;
          this.lstUsuarios.unshift({ Usuario: "---TODOS---" });
          this.usuarioSel = "---TODOS---";
        }
      });
    },
    async exportarReporte() {
      let params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen,
        idPerfil: this.idPerfil,
        usuarioReg: this.idPerfil == 3 ? this.usuarioReg : (this.usuarioSel === "---TODOS---" ? "" : this.usuarioSel)
      };
      await this.axios(`${this.API}reporte/ListarRegVenta/excel`, { params: params }).then(res => {
        let url = res.data;
        url = url.replace(/\\/g, "");
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
          url = "http://" + url; // O "https://" si es necesario
        }
        window.open(url, "_blank");
      });
    },
    async exportarDetallado() {
      let params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen,
        idPerfil: this.idPerfil,
        usuarioReg: this.idPerfil == 3 ? this.usuarioReg : (this.usuarioSel === "---TODOS---" ? "" : this.usuarioSel)
      };
      await this.axios(`${this.API}reporte/ListarRegVentaActivos/excel`, {
        params: params
      }).then(res => {
        let url = res.data;
        url = url.replace(/\\/g, "");
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
          url = "http://" + url; // O "https://" si es necesario
        }
        window.open(url, "_blank");
      });
    },
    obtenerUnidades(item) {
      const cant = parseFloat(item.Cantidad) || 0;
      const pres = parseFloat(item.CantidadPresentacion) || 1;
      return cant * pres;
    },
    async generarReporte() {
      this.lstReporte = [];
      let params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen,
        idPerfil: this.idPerfil,
        usuarioReg: this.idPerfil == 3 ? this.usuarioReg : (this.usuarioSel === "---TODOS---" ? "" : this.usuarioSel)
      };
      await this.axios(`${this.API}reporte/ListarRegVenta`, { params: params }).then(res => {
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
          if (reporte.Anulado == 1 || reporte.CodSunat == '07') {
            return sum;
          }
          return sum + reporte.Total;
        }, 0);
      }
      return parseFloat(total).toFixed(2);
    },
    detalleTotal() {
      const total = this.detalle.reduce((s, d) => {
        return s + (d.Cantidad * d.Precio - (d.Descuento ? d.Descuento : 0));
      }, 0);
      return total.toFixed(2);
    }
  }
};
</script>

<!-- Estilos de la pantalla principal (scoped) -->
<style scoped>
.clsAnulado {
  background: #ffcdd2 !important;
}

.table-container {
  max-height: 680px; /* ajusta la altura según necesidad */
  overflow-y: auto;
  position: relative;
}

/* Hace que el thead y tfoot de la tabla principal se mantengan fijos */
.table-container thead th,
.table-container tfoot td {
  position: sticky;
  background: white;
  z-index: 1;
}

.table-container thead th {
  top: 0;
  background: #1a237e; /* color del encabezado */
  color: #ffffff;
  z-index: 2;
}

.table-container tfoot td {
  bottom: 0;
  background: #006064;
  color: white;
  font-weight: bold;
}
</style>

<!-- Estilos del modal (SIN scoped, porque el modal se renderiza fuera del componente) -->
<style>
/* Tamaño del modal: ancho ~1070px, alto ~625px (arriba de la pantalla) */
.modalRegVentaDetalle .modal-dialog {
  max-width: 1070px !important;
  width: 92% !important;
  margin: 15px auto !important;
}
.modalRegVentaDetalle .modal-content {
  min-height: 625px;
  max-height: calc(100vh - 30px);
}
.modalRegVentaDetalle .modal-body {
  flex: 1 1 auto;
}
.modalRegVentaDetalle .modal-content {
  border: none;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.35);
}
.modalRegVentaDetalle .modal-header {
  padding: 0;
  border: none;
  display: block;
}
.modalRegVentaDetalle .modal-body {
  padding: 20px 24px 8px;
  background: #f5f6fa;
}

/* Cabecera */
.detalle-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 18px 24px;
  background: linear-gradient(135deg, #1a237e, #283593 60%, #006064);
  color: #fff;
}
.detalle-subtitulo {
  font-size: 11px;
  letter-spacing: 1px;
  text-transform: uppercase;
  opacity: 0.75;
}
.detalle-titulo {
  margin: 2px 0 0;
  font-weight: 700;
}
.detalle-titulo i {
  margin-right: 6px;
  opacity: 0.85;
}
.detalle-cerrar {
  background: rgba(255, 255, 255, 0.15);
  border: none;
  color: #fff;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  font-size: 22px;
  line-height: 1;
  cursor: pointer;
  transition: background 0.2s;
}
.detalle-cerrar:hover {
  background: rgba(255, 255, 255, 0.3);
}

/* Tabla */
.detalle-wrapper {
  background: #fff;
  border-radius: 10px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
  overflow-x: auto;
}
.detalle-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  font-size: 13.5px;
}
.detalle-table thead th {
  position: static;
  background: #eef0fa;
  color: #1a237e;
  font-size: 11.5px;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  padding: 12px 14px;
  border-bottom: 2px solid #c5cae9;
  white-space: nowrap;
}
.detalle-table tbody td {
  padding: 12px 14px;
  border-bottom: 1px solid #eceff1;
  vertical-align: middle;
}
.detalle-table tbody tr:hover {
  background: #f3f5ff;
}
.detalle-table tbody tr:last-child td {
  border-bottom: none;
}
.detalle-table tfoot td {
  background: #006064;
  color: #fff;
  font-weight: 700;
  padding: 12px 14px;
  font-size: 14px;
}

/* Utilidades */
.detalle-table .text-right {
  text-align: right;
}
.detalle-table .text-center {
  text-align: center;
}
.text-mono {
  font-family: Consolas, monospace;
  color: #546e7a;
}
.fw-600 {
  font-weight: 600;
}
.badge-pres {
  display: inline-block;
  background: #e0f2f1;
  color: #00695c;
  padding: 2px 10px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.detalle-acciones {
  display: flex;
  justify-content: flex-end;
  padding: 16px 0 12px;
}
</style>