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
      let reporteAgrupado = [];
      nombresUnicos.forEach(nombre => {
        let Cantidad = listado
          .filter(v => v.Nombre == nombre)
          .reduce((sum, prod) => {
            return sum + parseFloat(prod.Cantidad ? prod.Cantidad : 0);
          }, 0);
        let PrecioCosto = listado.reduce((sum, prod) => {
          return sum + parseFloat(prod.PrecioCosto ? prod.PrecioCosto : 0);
        }, 0);
        let PrecioVenta = listado.reduce((sum, prod) => {
          return sum + parseFloat(prod.PrecioVenta ? prod.PrecioVenta : 0);
        }, 0);
        // let PrecioVenta = listado.reduce((sum, prod) => {
        //   return sum + parseFloat(prod.PrecioVenta ? prod.PrecioVenta : 0);
        // }, 0);
        this.reporteAgrupado.push({
          Nombre: nombre,
          Cantidad: parseFloat(Cantidad).toFixed(2),
          PrecioCosto: parseFloat(PrecioCosto).toFixed(2),
          TotalCosto: parseFloat(PrecioCosto * Cantidad).toFixed(2),
          PrecioVenta: parseFloat(PrecioVenta).toFixed(2),
          TotalVenta: parseFloat(PrecioVenta * Cantidad).toFixed(2),
          UtilidadBruta: this.getUtilidadBruta({
            Cantidad: Cantidad,
            PrecioCosto: PrecioCosto,
            TotalCosto: PrecioCosto * Cantidad,
            PrecioVenta: PrecioVenta,
            TotalVenta: PrecioVenta * Cantidad
          }),
          UtilidadBrutaPorcentaje: this.getUtilidadBrutaPorcentaje({
            Cantidad: Cantidad,
            PrecioCosto: PrecioCosto,
            TotalCosto: PrecioCosto * Cantidad,
            PrecioVenta: PrecioVenta,
            TotalVenta: PrecioVenta * Cantidad
          })
        });
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
      // this.axios(`${this.API}reporte/utilidabruta/presentacion/exportar`, {
      //   params: {
      //     fechaIni: this.fechaIni,
      //     fechaFin: this.fechaFin,
      //     UsuarioReg: this.UsuarioReg
      //   }
      // })
      //   .then(res => {
      //     if (res.data) {
      //       window.open(res.data, "_blank");
      //     }
      //   })
      //   .catch(e => {
      //     console.log(e);
      //   });
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
