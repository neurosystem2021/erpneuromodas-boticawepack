<template>
    <div>
      <br>
        <b-container fluid style="width: calc(100% - 200px)" class="bv-example-row">
          <b-row class="text-center">
            <b-col>
              <h3>Registro de Ordenes de Compra</h3>
            </b-col>
          </b-row>
          <hr>
          <b-row>
            <b-col md="3" class="my-1">
              <b-form-group>
                <label>Fecha Inicio:</label>
                <b-form-input
                  v-model="fechaDesde"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="3" class="my-1">
              <b-form-group>
                <label>Fecha Fin:</label>
                <b-form-input
                  v-model="fechaHasta"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="4" class="my-1">
              <b-button variant="success" size="md" @click="verArticulos">Generar</b-button>
              <hr>
              <b-button variant="success" size="md" :href="hrefXls" target="_blank"><i class="fas fa-file-excel"></i>  Exportar Ordenes</b-button>
              <b-button class="btnPdf" size="md" @click="imprimirOrden()"><i class="fa fa-file-pdf"></i>  Exportar Ordenes</b-button>
            </b-col>
          </b-row>

          <b-row>
            <table class="table b-table table-striped table-hover table-sm">
              <thead>
                <tr>
                  <th style="width: 5% !important">Id</th>
                  <th>Correlativo</th>
                  <th>Fecha</th>
                  <th>Ruc</th>
                  <th>Proveedor</th>
                  <th>Total</th>
                  <th>Ver</th>
                  <th>Reporte</th>
                  <th>Accion</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prod, i) in productos" :key="i">
                  <td :class="{'table-danger': prod.AnuladoOrden == 1}" style="width: 5% !important">{{ prod.IdOrdenCompra }}</td>
                  <td :class="{'table-danger': prod.AnuladoOrden == 1}">{{ prod.Correlativo }}</td>
                  <td :class="{'table-danger': prod.AnuladoOrden == 1}">{{ prod.FechaOrdenCompra }}</td>
                  <td :class="{'table-danger': prod.AnuladoOrden == 1}">{{ prod.Ruc }}</td>
                  <td :class="{'table-danger': prod.AnuladoOrden == 1}">{{ prod.Proveedor }}</td>
                  <td :class="{'table-danger': prod.AnuladoOrden == 1}">S/ {{ prod.Total | moneda }}</td>
                  <td :class="{'table-danger': prod.AnuladoOrden == 1}"><b-button variant="primary" @click="mostrarDetalles(prod)"><i class="fas fa-eye"></i> Ver</b-button></td>
                  <td :class="{'table-danger': prod.AnuladoOrden == 1}"><a class="btn btn-success" target="_blank" :href="'/api/index.php/reporte/ordencompra?IdOrdenCompra=' + prod.IdOrdenCompra"><i class="fas fa-file-excel"></i> Excel</a></td>
                  <td :class="{'table-danger': prod.AnuladoOrden == 1}" align="center"><b-button variant="danger" v-if="prod.AnuladoOrden==0" @click="anularOrden(prod)"><i class="fas fa-times"></i> Anular</b-button><span v-else>----</span></td>
                </tr>
              </tbody>
            </table>
          </b-row>
          <b-modal size="lg" id="modalDetalle" ref="modalDetalle" :title="'Orden de Compra: '+infoOrden.Correlativo" ok-only ok-title="Cerrar" hide-footer>
            <b-row>
              <b-col md="6">
                <table>
                  <tbody>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">RUC</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Ruc }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Proveedor</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Proveedor }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Dirección</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Direccion }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Forma de Pago</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.EsCredito==1?'CRÉDITO':'CONTADO'}}</td>
                    </tr>
                    <tr v-if="infoOrden.EsCredito==1">
                      <td class="celda" style="font-weight:bold; width:35% !important;">Fecha de Crédito</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.FechaCredito }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Metodo de Pago</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.MovimientoMetodoPago}}</td>
                    </tr>
                  </tbody>
                </table>
              </b-col>

              <b-col md="6">
                <table>
                  <tbody>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Fecha</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.FechaOrdenCompra }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Fecha a Recoger</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.FechaRecojo }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Registrado Por</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.UsuarioRegOrden }}</td>
                    </tr>
                  </tbody>
                </table>
              </b-col>
            </b-row>
            <br>
            <b-row>
              <b-col md="12">
                <table class="table b-table table-sm table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">Codigo</th>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Precio</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(prod, i) in detalle" :key="i">
                            <td class="text-center">{{ prod.CodigoBarra }}</td>
                            <td class="text-center">{{ prod.Producto }}</td>
                            <td class="text-center">{{ prod.Cantidad }}</td>
                            <td class="text-center">{{ prod.Precio | moneda }}</td>
                            <td class="text-center">{{ (prod.Precio *  prod.Cantidad) | moneda }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                      <td colspan="3"></td>
                      <td class="text-center"><b>TOTAL</b></td>
                      <td class="table-success text-center" style="vertical-align: middle !important;">S/ {{infoOrden.Total | moneda}} </td>
                    </tr>
                  </tfoot>
                </table>
              </b-col>
            </b-row>
            <template #modal-footer>
              <div class="w-100">
                  
                  <b-button
                    size="sm"
                    class="float-right btnPdf"
                    @click="imprimirDetalle()"
                  >
                    Imprimir <i class="fa fa-file-pdf"></i>
                  </b-button>
                </div>
            </template>
          </b-modal>
        </b-container>
    </div>
</template>
<script>
import config from "@/store/config.js";
export default {
  mounted() {},
  data() {
    return {
      API: config.API,
      productos: [],
      fechaDesde: this.$moment().format("YYYY-01-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      detalle: {},
      infoOrden: {
        Anio: null,
        Correlativo: null,
        Direccion: null,
        EsCredito: null,
        FechaCredito: null,
        FechaOrdenCompra: null,
        FechaRecojo: null,
        IdOrdenCompra: null,
        IdProveedor: null,
        NumCuenta: null,
        Numero: null,
        Proveedor: null,
        Ruc: null,
        Telefono: null,
        UsuarioReg: null
      },
      urlImg: "http://boticav1.local/resources/images/logo-ticket.png"
    };
  },
  computed: {
    hrefXls() {
      return (
        "/api/index.php/reporte/ordenescompra?fechaDesde=" +
        this.fechaDesde +
        "&fechaHasta=" +
        this.fechaHasta
      );
    }
  },
  methods: {
    imprimirDetalle() {
      let html = `
                <table class>
                  <tbody>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">RUC</td>
                      <td class="celda" style="text-transform: uppercase">: ${
                        this.infoOrden.Ruc
                      }</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Proveedor</td>
                      <td class="celda" style="text-transform: uppercase">: ${
                        this.infoOrden.Proveedor
                      }</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Dirección</td>
                      <td class="celda" style="text-transform: uppercase">: ${
                        this.infoOrden.Direccion
                      }</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Forma de Pago</td>
                      <td class="celda" style="text-transform: uppercase">: ${
                        this.infoOrden.EsCredito == 1 ? "CRÉDITO" : "CONTADO"
                      }</td>
                    </tr>
                    <tr v-if="infoOrden.EsCredito==1">
                      <td class="celda" style="font-weight:bold; width:35% !important;">Fecha de Crédito</td>
                      <td class="celda" style="text-transform: uppercase">: ${
                        this.infoOrden.FechaCredito
                      }</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Metodo de Pago</td>
                      <td class="celda" style="text-transform: uppercase">: ${
                        this.infoOrden.MovimientoMetodoPago
                      }</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Fecha</td>
                      <td class="celda" style="text-transform: uppercase">: ${
                        this.infoOrden.FechaOrdenCompra
                      }</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Fecha a Recoger</td>
                      <td class="celda" style="text-transform: uppercase">: ${
                        this.infoOrden.FechaRecojo
                      }</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Registrado Por</td>
                      <td class="celda" style="text-transform: uppercase">: ${
                        this.infoOrden.UsuarioRegOrden
                      }</td>
                    </tr>
                  </tbody>
                </table>
                    
                <table class="mt-2" width="100%">
                  <thead>
                    <tr>
                      <th class="text-center">Codigo</th>
                      <th class="text-center">Producto</th>
                      <th class="text-center">Cantidad</th>
                      <th class="text-center">Precio</th>
                      <th class="text-center">Total</th>
                      </tr>
                  </thead>
                 <tbody>`;

      this.detalle.forEach(prod => {
        html += `
          <tr>
            <td class="text-center">${prod.CodigoBarra}</td>
            <td class="text-center">${prod.Producto}</td>
            <td class="text-center">${prod.Cantidad}</td>
            <td class="text-center">${prod.Precio | this.moneda}</td>
            <td class="text-center">${(prod.Precio * prod.Cantidad) |
              this.moneda}</td>
          </tr>
        `;
      });
      html += `</tbody>
               <tfoot class="mt-2">
                <tr >
                  <td colspan="3"></td>
                  <td class="text-center"><b>TOTAL</b></td>
                  <td class="table-success text-center" style="vertical-align: middle !important;">S/ ${this
                    .infoOrden.Total | this.moneda} </td>
                </tr>
                </tfoot>
          </table>`;
      const printWindow = window.open("", "_blank");
      if (!printWindow) {
        alert(
          "No se pudo abrir la ventana de impresión. Por favor, habilita las ventanas emergentes."
        );
        return;
      }

      printWindow.document.write(`
        <html>
          <head>
            <style>
              body { 
                font-family: Arial, sans-serif; 
                padding: 20px 20px;
              }
              .table-success {
                background: #c3e6cb !important;
              }
              .text-center {
                text-align: center;
              }
              .imgPrint{
                max-height: 100px;
                margin: 10px 10px;
              }
              .mt-2{
                margin: 4% 0 !important;
              }
            </style>
          </head>
          <body>
          
            <img id="printImage" src="${
              this.urlImg
            }" onload="window.print(); window.close();" class="imgPrint">
            <h1>Registro de Ordenes de Compra</h1>
            ${html}
          </body>
        </html>
      `);
      // printWindow.document.close();
      printWindow.focus();
    },
    imprimirOrden() {
      let html = `
        <table class="table b-table table-striped table-hover table-sm" width="100%" >
          <thead>
            <tr>
              
              <th>Correlativo</th>
              <th>Usuario</th>
              <th>Fecha</th>
              <th>Proveedor</th>
              <th>Total</th>             
            </tr>
          </thead>
          <tbody>`;
      this.productos.forEach(prod => {
        html += `
            <tr v-for="(prod, i) in productos" :key="i">
              <td class="${prod.AnuladoOrden == 1 ? "table-danger" : ""}">${
          prod.Correlativo
        }</td><td>${prod.UsuarioRegOrden}</td><td class="${
          prod.AnuladoOrden == 1 ? "table-danger" : ""
        }">${prod.FechaOrdenCompra}</td>
              <td class="${prod.AnuladoOrden == 1 ? "table-danger" : ""}">${
          prod.Proveedor
        }</td>
              <td class="${
                prod.AnuladoOrden == 1 ? "table-danger" : ""
              }">S/ ${prod.Total | this.moneda}</td>
            </tr>
        
        `;
      });
      html += `
        </tbody>
        </table>
      `;

      const printWindow = window.open("", "_blank");
      if (!printWindow) {
        alert(
          "No se pudo abrir la ventana de impresión. Por favor, habilita las ventanas emergentes."
        );
        return;
      }
      /*
  @page {
                  size: landscape;
                }
*/
      printWindow.document.write(`
          <html>
            <head>
              <style>
              
                @media print {
                  body { 
                    font-family: Arial, sans-serif; 
                    padding: 10px 20px;
                  }
                  .table-success {
                    background: #c3e6cb !important;
                  }
                  .text-center {
                    text-align: center;
                  }
                  .imgPrint {
                    max-height: 100px;
                    margin: 10px 10px;
                  }
                  .mt-2 {
                    margin: 4% 0 !important;
                  }
                  .table-danger {
                    background: red !important;
                    color: white !important;
                  }
                }
              </style>
            </head>
            <body>
              <img id="printImage" src="${
                this.urlImg
              }" onload="window.print(); window.close();" class="imgPrint">

              <h1>Registro de Ordenes de Compra</h1>

              ${html}
            </body>
          </html>
        `);

      printWindow.document.close();
      printWindow.focus();
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    mostrarDetalles(data) {
      this.axios
        .get(this.API + "ordencompra/detalle", {
          params: {
            IdOrdenCompra: data.IdOrdenCompra
          }
        })
        .then(response => {
          this.detalle = response.data;
          this.infoOrden = data;
          this.$refs["modalDetalle"].show();
        })
        .catch(error => {
          console.log(error);
        });
    },
    verArticulos() {
      this.productos = [];
      this.axios
        .get(this.API + "ordencompra", {
          params: {
            fechaDesde: this.fechaDesde,
            fechaHasta: this.fechaHasta
          }
        })
        .then(response => {
          this.productos = response.data;
        });
    },
    anularOrden(orden) {
      this.$swal({
        target: document.getElementById("conten"),
        title:
          "Se anulará la orden de compra <strong>" +
          orden.Correlativo +
          "</strong> del proveedor <strong>" +
          orden.Proveedor +
          "</strong>",
        text: "¿Está seguro?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        cancelButtonColor: "#dc3545",
        confirmButtonText: "Sí, anular",
        cancelButtonText: "No, atrás"
      }).then(result => {
        if (result.isConfirmed) {
          this.inProgress = true;
          this.axios
            .post(this.API + "ordencompra/anular", {
              IdOrdenCompra: orden.IdOrdenCompra
            })
            .then(response => {
              if (response.data) {
                this.$swal({
                  position: "center",
                  icon: "success",
                  title: "¡La orden de compra fue anulada correctamente!",
                  showConfirmButton: true,
                  confirmButtonText: "Ok",
                  target: document.getElementById("conten"),
                  confirmButtonColor: "#1D9828",
                  allowOutsideClick: false,
                  allowEscapeKey: false
                });

                this.verArticulos();
              }
            })
            .catch(error => {
              this.$toasted.error("Ha ocurrido un error, vuelva a intentarlo");
              this.inProgress = false;
            });
        }
      });
    }
  }
};
</script>
<style scope>
.btnPdf {
  background: #b71c1c !important;
  color: #fff !important;
}
</style>
