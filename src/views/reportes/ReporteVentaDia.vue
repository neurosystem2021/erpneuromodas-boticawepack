<template>
    <div>
      <br>
        <b-container class="bv-example-row">
          <b-row class="no-print">
            <b-col md="2" class="my-1">
              <b-form-group>
                <label>Almacen:</label>
                <autocomplete
                    required="required"
                    ref="inputAlmacen"
                    :url="API + 'almacenes'"
                    anchor="Almacen"
                    label="writer"
                    placeholder=""
                    :on-select="setAlmacen" 
                    :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                    :classes="{input: 'form-control'}">
                </autocomplete>
              </b-form-group>
            </b-col>
            <b-col md="2" class="my-1">
              <b-form-group>
                  <label>Punto de Venta:</label>
                  <autocomplete
                      required="required"
                      ref="inputPuntoVenta"
                      :url="API + 'ventas/puntos'"
                      anchor="PuntoVenta"
                      initValue="PRINCIPAL"
                      label="writer"
                      placeholder=""
                      :on-select="setPuntoVenta" 
                      :onFocus="focusAutocomplete.bind(null, 'inputPuntoVenta')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
              </b-form-group>
            </b-col>
            <b-col md="4" class="my-1">
              <b-form-group>
                <label>Vendedor:</label>
                <autocomplete
                    required="required"
                    ref="inputVendedor"
                    :url="API + 'usuarios'"
                    anchor="NombreUsuario"
                    label="writer"
                    :on-select="getVendedor" 
                    :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                    :classes="{input: 'form-control'}">
                </autocomplete>
              </b-form-group>
            </b-col>
            <b-col md="4" class="my-1">
              <b-button variant="success" size="sm" :href="hrefXls" target="_blank">Exportar</b-button>
              <b-button variant="success" size="sm" :href="hrefpdf" target="_blank">PDF</b-button>
              <hr>
              <b-button variant="primary" size="sm" @click="imprimir">Imprimir</b-button>
            </b-col>
          </b-row>

          <b-row>
            <b-col class="text-center">
              <br>
              <h2>REPORTE DE VENTAS REALIZADAS</h2>
              <br>
            </b-col>
          </b-row>
          <b-row class="si-print" style="display:none">
            <b-col class="text-left">
              <h5 class="font-weight-normal">Punto de Venta: <span class="font-weight-bold">{{puntoVenta.PuntoVenta}}</span></h5>
              <h5 class="font-weight-normal">Almacen: <span class="font-weight-bold">{{almacen.Almacen}}</span></h5>
              <h5 class="font-weight-normal">Vendedor: <span class="font-weight-bold">{{vendedor.Usuario}}</span></h5>
              <h5 class="font-weight-normal">Fecha y Hora del Reporte: <span class="font-weight-bold">{{fechaHoraImpreso}}</span></h5>
            </b-col>
          </b-row>
          <br>
          <b-row>
            <table class="table b-table table-striped table-hover table-sm">
              <tr>
                  <td class="text-center font-weight-bold h5">IMPORTE TOTAL VENTAS DEL DÍA </td>
                  <td class="text-center font-weight-bold h5">S/ {{parseFloat(total).toFixed(2) }}</td>
                </tr>
            </table>
          </b-row>
          <b-row>
            <table class="table b-table table-striped table-hover table-sm">
              <thead>
                <tr>
                  <th class="text-center">Código</th>
                  <th>Descripcion</th>
                  <th class="text-center">Stock</th>
                  <th class="text-center">Queda</th>
                  <th class="text-center">Ventas</th>
                  <th class="text-center">Prom. Precio.</th>
                  <th class="text-center">Monto Total</th>
                </tr>
              </thead>
              <tbody v-for="(cat, i) in categorias" :key="i">
                <tr>
                  <td class="text-right"><b>{{cat.IdProductoCategoria}}</b></td>
                  <td colspan="6"><b>{{cat.ProductoCategoria}}</b></td>
                </tr>
                <tr v-for="(prod, k) in cat.Productos" :key="k">
                  <td class="text-center">{{ prod.CodigoBarra }}</td>
                  <td>{{ prod.Producto}}</td>
                  <td class="text-center">{{ prod.stockIniVenta }}</td>
                  <td class="text-center">{{ prod.stock}}</td>
                  <td class="text-center">{{ prod.CantidadVentaDia }}</td>
                  <td class="text-center">{{parseFloat(prod.PromedioPrecioVenta).toFixed(2) }}</td>
                  <td class="text-center">S/ {{ parseFloat(prod.MontoVentas).toFixed(2) }}</td>
                </tr>
                <tr>
                  <td class="text-right" colspan="4"><b>*** TOTAL CATEGORIA</b></td>
                  <td class="text-center"><b>{{cat.TotalCantidad}}</b></td>
                  <td class="text-center">S/ <b>{{cat.TotalPromedio | moneda}}</b></td>
                  <td class="text-center">S/ <b>{{cat.TotalMonto | moneda}}</b></td>
                </tr>
                <tr><td colspan="7"></td></tr>
              </tbody>
              <tfoot>
                <tr>
                  <td></td>
                  <td></td>
                  <td colspan="4" class="text-center font-weight-bold h5">SUMATORIA</td>
                  <td class="text-center font-weight-bold h5">S/ {{parseFloat(total).toFixed(2) }}</td>
                </tr>
              </tfoot>
            </table>
          </b-row>
        </b-container>
    </div>
</template>
<script>
import config from "@/store/config.js";
export default {
  mounted() {
    let usuario = sessionStorage.getItem("User");
    let idPerfil = sessionStorage.getItem("IdPerfil");
    if (idPerfil != 1 && idPerfil != 2) {
      this.$refs.inputVendedor.$refs.input.disabled = true;
    }
    this.getVendedor({ Usuario: usuario });
  },
  data() {
    return {
      API: config.API,
      almacen: {},
      vendedor: {},
      productos: [],
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      puntoVenta: {},
      fechaHoraImpreso: "",
      total: 0,
      categorias: []
    };
  },
  computed: {
    hrefXls() {
      return (
        "/api/index.php/reporte/venta/turno?idAlmacen=" +
        this.almacen.IdAlmacen +
        "&fechaHasta=" +
        this.fechaHasta +
        "&user=" +
        this.vendedor.Usuario
      );
    },
    hrefpdf() {
      return (
        "/imprimir/reporteventapdf.php?idAlmacen=" +
        this.almacen.IdAlmacen +
        "&fechaHasta=" +
        this.fechaHasta +
        "&user=" +
        this.vendedor.Usuario +
        "&punto=" +
        this.puntoVenta.PuntoVenta +
        "&almacen=" +
        this.almacen.Almacen
      );
    }
  },
  methods: {
    getVendedor(usuario) {
      //let usuario = sessionStorage.getItem('User')
      this.axios
        .get(this.API + "usuarios/info/" + usuario.Usuario || usuario)
        .then(response => {
          let user = {
            Usuario: response.data.Usuario,
            NombreUsuario: response.data.NombreUsuario,
            IdAlmacen: response.data.IdAlmacen,
            IdDocVentaPuntoVenta: response.data.IdDocVentaPuntoVenta
          };

          let almacen = {
            IdAlmacen: response.data.IdAlmacen,
            Almacen: response.data.Almacen
          };

          let puntoVenta = {
            IdDocVentaPuntoVenta: response.data.IdDocVentaPuntoVenta,
            PuntoVenta: response.data.PuntoVenta
          };

          this.setVendedor(user);
          this.setAlmacen(almacen);
          this.setPuntoVenta(puntoVenta);
          this.$refs.inputVendedor.setValue(user.NombreUsuario);
          this.$refs.inputAlmacen.setValue(almacen.Almacen);
          this.$refs.inputPuntoVenta.setValue(puntoVenta.PuntoVenta);
          this.$refs.inputAlmacen.$refs.input.disabled = true;
          this.$refs.inputPuntoVenta.$refs.input.disabled = true;
          this.verArticulos();
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    setVendedor(obj) {
      this.vendedor = obj;
    },
    setAlmacen(obj) {
      this.almacen = obj;
    },
    setPuntoVenta(obj) {
      this.puntoVenta = obj;
    },
    focusAutocomplete(ref) {
      //this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    verArticulos() {
      this.total = 0;
      this.axios
        .get(this.API + "productos/ventas/dia/cat?limit=0", {
          params: {
            idAlmacen: this.almacen.IdAlmacen,
            user: this.vendedor.Usuario,
            fechaHasta: this.fechaHasta
          }
        })
        .then(response => {
          this.productos = response.data.data;
          let categorias = response.data.categorias;
          categorias.map(cat => {
            cat.Productos = [];
            cat.TotalCantidad = 0;
            cat.TotalMonto = 0;
            cat.TotalPromedio = 0;
            response.data.data.map(pro => {
              if (cat.IdProductoCategoria == pro.IdProductoCategoria) {
                cat.Productos.push(pro);
                cat.TotalCantidad += parseFloat(pro.CantidadVentaDia);
                cat.TotalMonto += parseFloat(pro.MontoVentas);
                cat.TotalPromedio += parseFloat(pro.PromedioPrecioVenta);
              }
            });
          });
          this.categorias = categorias;
          this.productos.map(prod => {
            this.total += prod.MontoVentas || 0;
          });
        });
    },
    imprimir() {
      this.fechaHoraImpreso = this.$moment().format("YYYY-MM-DD HH:mm:ss");
      setTimeout(() => {
        window.print();
      }, 200);
    }
  }
};
</script>
<style>
@media print {
  .no-print {
    display: none !important;
  }
  .si-print {
    display: block !important;
  }
}
</style>
