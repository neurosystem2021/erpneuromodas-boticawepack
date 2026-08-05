<template>
  <b-container class="kardex-page" fluid>
    <header class="kardex-hero">
      <div class="kardex-hero__icon"><i class="fas fa-chart-line"></i></div>
      <div><small>REPORTES DE INVENTARIO</small><h1>Kardex valorizado</h1><p>Consulta movimientos, costos y saldos de un producto por periodo.</p></div>
      <span><i class="fas fa-shield-alt"></i> Información valorizada</span>
    </header>
    <b-row class="kardex-filter-card">
        <b-col cols="12">
            <div class="kardex-filter-title"><div><small>PARÁMETROS DEL REPORTE</small><h2>Configura tu consulta</h2></div><i class="fas fa-sliders-h"></i></div>
            <b-row class="align-items-end">
                <b-col cols="1">
                    Año
                    <input type="number" class="form-control" v-model="anno">
                </b-col>
                <b-col cols="2">
                    Producto
                    <autocomplete
                        ref="autocompleteProducto"
                        :url="APIProductos"
                        :onShouldRenderChild="renderChild"
                        anchor="Producto"
                        label="writer"
                        placeholder="Agregar Producto"
                        :on-select="setProducto"
                        :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                        :classes="{input: 'form-control'}">
                    </autocomplete>
                </b-col>
                <b-col cols="1">
                    Stock Inicial
                     <input type="number" class="form-control" v-model="stock">
                </b-col>
                <b-col cols="1">
                    Costo
                     <input type="number" class="form-control" v-model="precio">
                </b-col>
                <b-col cols="2">
                    Mes:
                    <select class="form-control" v-model="Mes">
                        <option value="" selected disabled> -- Seleccione un mes -- </option>
                        <option :value="mes.value" v-for="(mes,index) in mounth" :key="index">
                            {{ mes.text }}
                        </option>
                    </select>
                    <!-- {{ mounth }} -->
                </b-col>
                <b-col cols="3" class="kardex-actions">
                    <b-button class="btn-generate" @click="obtenerListado()" variant="info">
                      <i class="fa fa-search" aria-hidden="true"></i> Generar
                    </b-button> 
                    
                    <b-button class="btn-pdf" @click="exportPDF()" variant="danger" :disabled="disableEXCEL">
                      <i class="fa fa-file-pdf" aria-hidden="true"></i> Generar PDF
                    </b-button> 

                    <b-button class="btn-excel" @click="exportEXCEL()" variant="success" :disabled="disableEXCEL">
                      <i class="fa fa-file-excel" aria-hidden="true"></i> Generar EXCEL
                    </b-button> 
                </b-col>
            </b-row>
           
        </b-col>
        <b-col v-if="lstReporte.length" cols="12" class="kardex-report-card">
            <div class="kardex-report-head"><div><small>RESULTADOS</small><h2>Detalle del kardex</h2></div><span><i class="fas fa-box"></i> {{ producto.Producto }}</span></div>
            <div class="kardex-table-scroll"><table width="100%" class="table table-hover kardex-table">
                <tbody>
                    <tr v-for="(lst,index) in lstReporte.filter(v=> v.d1!=' ')" :key="index" :class="getClass(lst)"> 
                        <td>{{ lst.d1 }}</td>
                        <td>{{ retornarDecimal(lst.d2) }}</td>
                        <td>{{ retornarDecimal(lst.d3) }}</td>
                        <td>{{ retornarDecimal(lst.d4) }}</td>
                        <td>{{ retornarDecimal(lst.d5) }}</td>
                        <td>{{ retornarDecimal(lst.d6) }}</td>
                        <td>{{ retornarDecimal(lst.d7) }}</td>
                        <td>{{ retornarDecimal(lst.d8) }}</td>
                        <td>{{ retornarDecimal(lst.d9) }}</td>
                        <td>{{ retornarDecimal(lst.d10) }}</td>
                        <td>{{ retornarDecimal(lst.d11) }}</td>
                    </tr>
                </tbody>
            </table></div>
            <!-- {{ lstReporte }} -->
        </b-col>
        <b-col v-else cols="12" class="kardex-empty"><div><i class="fas fa-chart-bar"></i></div><h3>Tu reporte aparecerá aquí</h3><p>Selecciona un producto, ajusta los parámetros y pulsa “Generar”.</p></b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      Tipo: 0,
      anno: "",
      Mes: "",
      mounth: [
        { value: "01", text: "ENERO" },
        { value: "02", text: "FEBREO" },
        { value: "03", text: "MARZO" },
        { value: "04", text: "ABRIL" },
        { value: "05", text: "MAYO" },
        { value: "06", text: "JUNIO" },
        { value: "07", text: "JULIO" },
        { value: "08", text: "AGOSTO" },
        { value: "09", text: "SEPTIEMBRE" },
        { value: "10", text: "OCTUBRE" },
        { value: "11", text: "NOVIEMBRE" },
        { value: "12", text: "DICIEMBRE" }
      ],
      stock: 0,
      precio: 0,
      idAlmacen: 1,
      producto: {},
      lstReporte: []
    };
  },
  mounted() {
    this.anno = this.$moment().format("YYYY");
  },
  methods: {
    renderChild(data) {
      return `
          <b>${data.Producto}</b>
        `;
    },
    setProducto(obj) {
      this.producto = obj;
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    async obtenerListado() {
      if (!Object.keys(this.producto).length > 0) {
        this.$toasted.show("Seleccione un producto.");
        return;
      }
      await this.axios(`${this.API}producto/listarstockvalorizado`, {
        params: {
          producto: this.producto.Producto,
          stock: this.stock,
          precio: this.precio,
          anno: this.anno,
          Tipo: this.Tipo
        }
      }).then(response => {
        this.generarListado(response.data);
      });
    },
    generarListado(datos) {
      const excluirValores = [
        "KARDEX VALORIZADO",
        "PRODUCTO",
        "AÑO",
        "DESDE",
        "HASTA"
      ];

      const datosFiltrados = datos.filter(item => {
        const valor = (item.d1 || "").trim();
        return valor !== "" && !excluirValores.includes(valor);
      });

      let header = datosFiltrados[0];

      datosFiltrados.shift();

      let datosFinales = datosFiltrados;
      console.log("this.Mes", this.Mes);
      if (this.Mes) {
        datosFinales = [];
        datosFiltrados.forEach(element => {
          if (this.$moment(element.d1).format("MM") == this.Mes) {
            datosFinales.push(element);
          }
        });
      }
      datosFinales.unshift(header);
      this.lstReporte = datosFinales;
    },
    retornarDecimal(valor) {
      if (typeof valor === "number") {
        return parseFloat(valor.toFixed(2));
      }

      if (typeof valor === "string" && /^-?\d+(\.\d+)?$/.test(valor.trim())) {
        const numero = parseFloat(valor);
        return parseFloat(numero.toFixed(2));
      }

      return valor;
    },
    getClass(item) {
      let clasName = "";
      let value = item.d1.trim();
      switch (value) {
        case "KARDEX VALORIZADO":
          clasName = "clsTitle";
          break;
        case "PRODUCTO":
          clasName = "clsProducto";
          break;
        case "AÑO":
          clasName = "clsAnno";
          break;
        case "DESDE":
          console.log(item.d1);
          clasName = "clsDesde";
          break;
        case "HASTA":
          console.log(item.d1);
          clasName = "clsHasta";
          break;
        case "FECHA":
          clasName = "clsFecha";
          break;

        default:
          clasName = "";
          break;
      }
      return clasName;
    },
    async exportEXCEL() {
      let data = {
        data: this.lstReporte
      };
      await this.axios
        .post(`${this.API}producto/listarstockvalorizado/excel`, data)
        .then(res => {
          let url = res.data;
          url = url.replace(/\\/g, "");
          if (!url.startsWith("http://") && !url.startsWith("https://")) {
            url = "http://" + url; // O "https://" si es necesario
          }
          window.open(url, "_blank");
        });
    },
    async exportPDF() {
      await this.axios(`${this.API}reporte/kardexvalorizadopdf`, {
        params: {
          producto: this.producto.Producto,
          stock: this.stock,
          precio: this.precio,
          anno: this.anno,
          Tipo: this.Tipo
        }
      }).then(res => {
        if (res.data) {
          let url = res.data.url;
          url = url.replace(/\\/g, "");
          if (!url.startsWith("http://") && !url.startsWith("https://")) {
            url = "http://" + url; // O "https://" si es necesario
          }
          console.log(url);
          window.open(url, "_blank");
        }
      });
    }
  },

  computed: {
    APIProductos() {
      return this.API + "producto/inv";
    },
    disableEXCEL() {
      if (this.lstReporte.length == 0) {
        return true;
      }
      return false;
    }
  }
};
</script>

<style scoped>
.kardex-page { min-height: 100vh; padding: 12px 20px 28px; color: #112b49; background: linear-gradient(145deg,#f4f9fd,#eaf3fa 55%,#f9fcfe); }
.kardex-hero { display:flex; align-items:center; gap:13px; min-height:76px; padding:11px 20px; margin-bottom:12px; color:#fff; border-radius:20px; background:linear-gradient(118deg,#082b58,#0b568e 63%,#08a0c4); box-shadow:0 15px 34px rgba(9,52,94,.18); }
.kardex-hero__icon { display:grid; place-items:center; width:48px; height:48px; flex:0 0 48px; color:#79edff; font-size:21px; border:1px solid rgba(255,255,255,.25); border-radius:14px; background:rgba(255,255,255,.1); }
.kardex-hero small { color:#84ecff; font-size:10px; font-weight:900; letter-spacing:1.5px; }
.kardex-hero h1 { margin:0; font-size:25px; font-weight:900; }
.kardex-hero p { margin:1px 0 0; color:rgba(239,249,255,.83); font-size:12px; }
.kardex-hero > span { margin-left:auto; padding:8px 13px; white-space:nowrap; border:1px solid rgba(255,255,255,.2); border-radius:999px; background:rgba(2,31,64,.25); font-size:11px; font-weight:800; }
.kardex-hero > span i { margin-right:6px; color:#43e5a3; }
.kardex-filter-card { margin:0 0 12px; padding:16px 17px 18px; border:1px solid #d9e7f2; border-radius:20px; background:rgba(255,255,255,.97); box-shadow:0 12px 30px rgba(31,69,108,.08); }
.kardex-filter-title { display:flex; align-items:center; justify-content:space-between; width:100%; margin-bottom:13px; padding-bottom:10px; border-bottom:1px solid #e5edf5; }
.kardex-filter-title small,.kardex-report-head small { color:#1388c8; font-size:9px; font-weight:900; letter-spacing:1.3px; }
.kardex-filter-title h2,.kardex-report-head h2 { margin:1px 0 0; font-size:18px; font-weight:900; }
.kardex-filter-title > i { color:#168fd1; font-size:18px; }
.kardex-page label { display:block; margin-bottom:5px; color:#29445f; font-size:12px; font-weight:850; }
.kardex-page .form-control { min-height:46px; color:#18304e; font-weight:700; border:1px solid #cfdeea; border-radius:11px; background:#fbfdff; box-shadow:none; }
.kardex-page .form-control:focus { border-color:#1599df; box-shadow:0 0 0 4px rgba(21,153,223,.13); }
.kardex-actions { display:flex; flex-wrap:wrap; gap:7px; padding-top:0 !important; }
.kardex-actions .btn { min-height:46px; padding:0 14px; border:0; border-radius:11px; font-size:12px; font-weight:900; box-shadow:0 7px 15px rgba(24,69,111,.13); }
.btn-generate { background:linear-gradient(135deg,#0874b6,#09a3c5) !important; }
.btn-pdf { background:linear-gradient(135deg,#df3653,#f06a78) !important; }
.btn-excel { background:linear-gradient(135deg,#09955b,#21c178) !important; }
.kardex-actions .btn:disabled { filter:grayscale(.35); opacity:.45; box-shadow:none; }
.kardex-report-card { margin-top:2px; padding:0 !important; overflow:hidden; border:1px solid #d8e6f1; border-radius:20px; background:#fff; box-shadow:0 13px 32px rgba(31,69,108,.09); }
.kardex-report-head { display:flex; align-items:center; justify-content:space-between; padding:14px 17px; border-bottom:1px solid #e3edf5; }
.kardex-report-head > span { max-width:50%; overflow:hidden; padding:7px 11px; color:#315571; text-overflow:ellipsis; white-space:nowrap; border-radius:999px; background:#edf7fd; font-size:11px; font-weight:800; }
.kardex-table-scroll { overflow-x:auto; }
.kardex-table { min-width:1050px; margin:0; }
.kardex-table td { padding:10px 12px; vertical-align:middle; border-color:#e5edf4; font-size:12px; }
.kardex-table tr:not(.clsFecha):hover { background:#eef8ff; }
.kardex-empty { min-height:330px; display:flex; align-items:center; justify-content:center; flex-direction:column; color:#71869a; text-align:center; border:1px dashed #c7d9e8; border-radius:20px; background:rgba(255,255,255,.62); }
.kardex-empty > div { display:grid; place-items:center; width:64px; height:64px; margin-bottom:12px; color:#168fca; font-size:25px; border-radius:20px; background:#e1f3fc; }
.kardex-empty h3 { margin:0; color:#244560; font-size:18px; font-weight:900; }
.kardex-empty p { margin:5px 0 0; font-size:12px; }
.clsTitle {
  background: linear-gradient(105deg,#092f61,#087da9);
  font-weight: bold;
  color: white;
}
.clsProducto {
  background: #dff3fc;
  color:#174b6b;
  font-weight:800;
}
.clsAnno {
  background: #eaf4fb;
}
.clsDesde {
  background: #f0f7fc;
}
.clsHasta {
  background: #f0f7fc;
}
.clsFecha {
  background: linear-gradient(105deg,#0a376d,#087fae);
  font-weight: bold;
  color: white;
}
@media(max-width:991px){.kardex-hero>span{display:none}.kardex-filter-card .col-1,.kardex-filter-card .col-2,.kardex-filter-card .col-3{max-width:50%;flex:0 0 50%;margin-bottom:10px}.kardex-actions{max-width:100%!important;flex:0 0 100%!important}.kardex-empty{min-height:250px}}
@media(max-width:575px){.kardex-page{padding:8px}.kardex-hero{padding:10px 13px;border-radius:16px}.kardex-hero p{display:none}.kardex-hero h1{font-size:20px}.kardex-filter-card .col-1,.kardex-filter-card .col-2,.kardex-filter-card .col-3{max-width:100%;flex:0 0 100%}.kardex-actions .btn{flex:1 1 auto}}
</style>
