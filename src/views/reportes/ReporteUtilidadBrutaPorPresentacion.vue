<template>
  <b-container>
    <b-row>
      <b-col md="3">
          <b-input-group prepend="Desde" class="mt-2">
            <input type="date" v-model="fechaIni" class="form-control">
          </b-input-group>
      </b-col>
      <b-col md="3">
            <b-input-group prepend="Hasta" class="mt-2">
              <input type="date" v-model="fechaFin" class="form-control">
            </b-input-group>
      </b-col>
      <b-col md="3">
        <b-input-group prepend="Vendedor:" class="mt-2">
              <autocomplete
                  required="required"
                  ref="inputVendedor"
                  :url="API + 'usuarios'"
                  anchor="NombreUsuario"
                  label="writer"
                  initValue="Todos"
                   :customParams="{idPerfil: idPerfil}"
                  :on-select="setVendedorManual" 
                  :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                  :classes="{input: 'form-control'}">
              </autocomplete>
         </b-input-group>
      </b-col>
      <b-col md="3">
        <b-button variant="info" class="my-2 mx-1" @click="obtenerDatos">Generar</b-button>
        <b-button variant="success" class="my-2 mx-1" @click="exportarReporte">Exportar Excel</b-button>
      </b-col>

      <div class="mt-5">
        <b-tabs content-class="mt-3" justified pills card>
          <b-tab title="Reporte General" active>
             <b-col md="12" class="mt-3">
                <b-table :items="lstReporte" :fields="header">
                  <template slot="TotalCosto" slot-scope="data">
                    {{ parseFloat(data.item.PrecioCosto * data.item.Cantidad).toFixed(2) }}
                    
                  </template>
                <template slot="TotalVenta" slot-scope="data">
                  {{ parseFloat(data.item.PrecioVenta * data.item.Cantidad) .toFixed(2)}} 
                  
                </template>
                <template slot="UtilidadBruta" slot-scope="data">
                  {{ getUtilidadBruta(data.item) }}
                  
                </template>
                <template slot="PorcentajeUtilidadBruta" slot-scope="data">
                  {{ getUtilidadBrutaPorcentaje(data.item) }}%
                  
                </template>
              </b-table>
            </b-col>
          </b-tab>
          <b-tab title="Reporte Por Presentación" active>
              <b-table :items="reporteAgrupado"></b-table>
          </b-tab>
        </b-tabs>
      </div>

     
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      lstReporte: [],
      header: [
        { key: "UsuarioReg", label: "Vendedor" },
        { key: "Producto" },
        { key: "Nombre", label: "Presentación" },
        { key: "Cantidad" },
        { key: "PrecioCosto" },
        { key: "TotalCosto" },
        { key: "PrecioVenta" },
        { key: "TotalVenta" },
        { key: "UtilidadBruta", label: "Ut. Bruta" },
        { key: "PorcentajeUtilidadBruta", label: "% Ut. Bruta" }
      ],
      fechaIni: "",
      fechaFin: "",
      UsuarioReg: "",
      idPerfil: sessionStorage.getItem("IdPerfil") || 0,
      reporteAgrupado: []
    };
  },
  mounted() {
    this.fechaIni = this.$moment()
      .startOf("month")
      .format("YYYY-MM-DD");
    this.fechaFin = this.$moment()
      .endOf("month")
      .format("YYYY-MM-DD");
    this.obtenerDatos();
  },
  methods: {
    obtenerDatos() {
      this.axios(`${this.API}reporte/utilidabruta/presentacion`, {
        params: {
          fechaIni: this.fechaIni,
          fechaFin: this.fechaFin,
          UsuarioReg: this.UsuarioReg
        }
      })
        .then(res => {
          if (res.data) {
            this.lstReporte = res.data;
            this.generarReporte(res.data);
          }
        })
        .catch(e => {
          console.log(e);
        });
    },
    generarReporte(listado) {
      this.reporteAgrupado = [];
      let nombresUnicos = [...new Set(listado.map(v => v.Nombre))];

      nombresUnicos.forEach(nombre => {
        let productos = listado.filter(v => v.Nombre == nombre);

        // Totales
        let Cantidad = productos.reduce(
          (sum, prod) => sum + parseFloat(prod.Cantidad || 0),
          0
        );

        let TotalCosto = productos.reduce((sum, prod) => {
          let cant = parseFloat(prod.Cantidad || 0);
          let pc = parseFloat(prod.PrecioCosto || 0); // 👈 si viene null → 0
          return sum + cant * pc;
        }, 0);

        let TotalVenta = productos.reduce((sum, prod) => {
          let cant = parseFloat(prod.Cantidad || 0);
          let pv = parseFloat(prod.PrecioVenta || 0); // 👈 si viene null → 0
          return sum + cant * pv;
        }, 0);

        // Precios unitarios (promedio ponderado)
        let PrecioCosto = Cantidad > 0 ? TotalCosto / Cantidad : 0;
        let PrecioVenta = Cantidad > 0 ? TotalVenta / Cantidad : 0;

        // Utilidad
        let UtilidadBruta = TotalVenta - TotalCosto;
        let UtilidadBrutaPorcentaje =
          TotalVenta > 0 ? (UtilidadBruta / TotalVenta) * 100 : 0;

        this.reporteAgrupado.push({
          Nombre: nombre,
          Cantidad: Cantidad.toFixed(2),
          PrecioCosto: PrecioCosto.toFixed(2),
          TotalCosto: TotalCosto.toFixed(2),
          PrecioVenta: PrecioVenta.toFixed(2),
          TotalVenta: TotalVenta.toFixed(2),
          UtilidadBruta: UtilidadBruta.toFixed(2),
          UtilidadBrutaPorcentaje: UtilidadBrutaPorcentaje.toFixed(2)
        });
      });

      // Totales generales
      let totales = this.reporteAgrupado.reduce(
        (acc, item) => {
          acc.Cantidad += parseFloat(item.Cantidad);
          acc.TotalCosto += parseFloat(item.TotalCosto);
          acc.TotalVenta += parseFloat(item.TotalVenta);
          return acc;
        },
        { Cantidad: 0, TotalCosto: 0, TotalVenta: 0 }
      );

      let PrecioCostoTotal =
        totales.Cantidad > 0 ? totales.TotalCosto / totales.Cantidad : 0;
      let PrecioVentaTotal =
        totales.Cantidad > 0 ? totales.TotalVenta / totales.Cantidad : 0;
      let UtilidadBrutaTotal = totales.TotalVenta - totales.TotalCosto;
      let UtilidadBrutaPctTotal =
        totales.TotalVenta > 0
          ? (UtilidadBrutaTotal / totales.TotalVenta) * 100
          : 0;

      this.reporteAgrupado.push({
        _rowVariant: "info",
        Nombre: "TOTALES",
        Cantidad: totales.Cantidad.toFixed(2),
        PrecioCosto: PrecioCostoTotal.toFixed(2),
        TotalCosto: totales.TotalCosto.toFixed(2),
        PrecioVenta: PrecioVentaTotal.toFixed(2),
        TotalVenta: totales.TotalVenta.toFixed(2),
        UtilidadBruta: UtilidadBrutaTotal.toFixed(2),
        UtilidadBrutaPorcentaje: UtilidadBrutaPctTotal.toFixed(2)
      });
    },
    exportarReporte() {
      window.open(
        `${this.API}reporte/utilidabruta/presentacion/exportar?fechaIni=${
          this.fechaIni
        }&fechaFin=${this.fechaFin}&UsuarioReg=${this.UsuarioReg}
      `,
        "_blank"
      );
      
    },
    getUtilidadBruta(item) {
      return parseFloat(
        parseFloat(item.PrecioVenta * item.Cantidad) -
          parseFloat(item.PrecioCosto * item.Cantidad)
      ).toFixed(2);
    },
    getUtilidadBrutaPorcentaje(item) {
      return parseFloat(
        ((item.PrecioVenta * item.Cantidad - item.PrecioCosto * item.Cantidad) /
          (item.Cantidad * item.PrecioVenta)) *
          100
      ).toFixed(2);
    },
    setVendedorManual(val) {
      this.UsuarioReg = val.Usuario;
    },
    focusAutocomplete(ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    }
  }
};
</script>

<style>
</style>
