<template>
  <b-container fluid>
    <b-row>
        <b-col cols="12" md="2">
           <b-input-group prepend="Código Barra">
             <b-form-input
             v-model="CodigoBarra"
             />
            </b-input-group>
        </b-col>
        <b-col cols="12" md="2">
           <b-input-group prepend="Producto">
           <autocomplete
            ref="autocompleteProducto"
            :url="APIProductos"
            :customParams="{ 'sortBy':false?'stock':'IdProducto', 'sortDesc': 1 ,limit: '25' }"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder=""
            :on-select="setProducto"
            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
            :classes="{input: 'form-control'}">
          </autocomplete>
          </b-input-group>
        </b-col>
        <b-col cols="12" md="2">
           <b-input-group prepend="Desde:"> 
             <input type="date" v-model="desde" class="form-control">
            </b-input-group>
        </b-col>
        <b-col cols="12" md="2">
           <b-input-group prepend="Hasta:">            
             <input type="date" v-model="hasta" class="form-control">
            </b-input-group>
        </b-col>
          <b-col cols="12" md="2">
            <!-- 1 -> Ventas||  2 -> Disponible -->
             <b-input-group prepend="Tipo Reporte:">         
            <select class="form-control" v-model="tipoReporte">
              <option :value="0" selected disabled>---Seleccionar ---</option>
              <option :value="1">Series Vendidas</option>
              <option :value="2">Series Disponibles</option>
            </select>
            </b-input-group>
          </b-col>
         <b-col cols="12" md="2">
            <b-button variant="info" @click="ObtenerListadoSeriesVendida()">Generar</b-button>
            <b-button variant="success"  
              :href="`/api/index.php/productos/ventas/serie/exportar?fechaDesde=${desde}&fechaHasta=${hasta}`"
              target="_blank"
            >Exportar</b-button>
         </b-col>
        <b-col cols="12" class="my-3">
            <b-table 
            :items="filteredSeries" 
            :fields="headerReporte"
            bordered
            hover
            small
            :per-page="perPage"
            :current-page="currentPage"
            >
            </b-table>

            <b-pagination
            v-model="currentPage"
            :total-rows="filteredSeries.length"
            :per-page="perPage"
            aria-controls="my-table"
            ></b-pagination>
        </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
import axios from "axios";
export default {
  data() {
    return {
      currentPage: 1,
      perPage: 20,
      searchQuery: "",
      headerReporte: [
        { label: "Fecha", key: "FechaMod" },
        { label: "Serie", key: "ventaSerie" },
        { label: "Numero", key: "ventaNumero" },
        { label: "Producto", key: "Producto" },
        { label: "Producto Serie", key: "Serie" },
        { label: "Cantidad", key: "Cantidad" },
        { label: "Precio", key: "Precio" },
        { label: "subTotal", key: "subTotal" },
        { label: "Descuento", key: "Descuento" },
        { label: "Total", key: "Total" }
      ],
      lstSeriesVendida: [],
      API: config.API,
      desde: "",
      hasta: "",
      IdProducto: "",
      tipoReporte: 1,
      CodigoBarra: ""
    };
  },
  mounted() {
    const fechaActualMoment = this.$moment();
    this.desde = fechaActualMoment
      .clone()
      .startOf("month")
      .format("YYYY-MM-DD");
    this.hasta = fechaActualMoment
      .clone()
      .endOf("month")
      .format("YYYY-MM-DD");
    this.ObtenerListadoSeriesVendida();
  },
  methods: {
    async ObtenerListadoSeriesVendida() {
      if (this.tipoReporte == 1) {
        this.headerReporte = [
          { label: "Fecha", key: "FechaMod" },
          { label: "Serie", key: "ventaSerie" },
          { label: "Numero", key: "ventaNumero" },
          { label: "Producto", key: "Producto" },
          { label: "Producto Serie", key: "Serie" },
          { label: "Cantidad", key: "Cantidad" },
          { label: "Precio", key: "Precio" },
          { label: "subTotal", key: "subTotal" },
          { label: "Descuento", key: "Descuento" },
          { label: "Total", key: "Total" }
        ];
      }
      if (this.tipoReporte == 2) {
        this.headerReporte = [
          { label: "Fecha", key: "FechaMod" },
          // { label: "Serie", key: "ventaSerie" },
          // { label: "Numero", key: "ventaNumero" },
          { label: "Producto", key: "Producto" },
          { label: "Producto Serie", key: "Serie" },
          // { label: "Cantidad", key: "Cantidad" },
          // { label: "Precio", key: "Precio" },
          // { label: "subTotal", key: "subTotal" },
          // { label: "Descuento", key: "Descuento" },
          // { label: "Total", key: "Total" }
        ];
      }

      let params = {
        fechaDesde: this.desde,
        fechaHasta: this.hasta,
        IdProducto: this.IdProducto,
        tipoReporte: this.tipoReporte,
        CodigoBarra: this.CodigoBarra
      };
      await this.axios(`${this.API}productos/ventas/serie`, {
        params: params
      }).then(res => {
        if (res) {
          this.lstSeriesVendida = res.data;
        }
      });
    },
    async ExportarListadoSeriesVendida() {
      let params = {
        fechaDesde: this.desde,
        fechaHasta: this.hasta,
        IdProducto: this.IdProducto,
        tipoReporte: this.tipoReporte,
        CodigoBarra: this.CodigoBarra
      };
      await this.axios(`${this.API}productos/ventas/serie/exportar`, {
        params: params
      }).then(res => {
        if (res) {
          console.log(res.data);
        }
      });
    },
    renderChild(data) {
      if (this.idAlmacen) {
        let styleStock = "";
        if (data.stock <= 0) {
          styleStock = "table-danger";
        } else {
          styleStock = "table-success";
        }

        if (data.EsPadre == 1) {
          styleStock = "";
        }

        return `
        <style>
          #table-render {
            font-size: 13px;
            margin-bottom: 5px;
            margin-top: 5px;
          }
          #table-render td, #table-render th {
            border: 1px solid #eaeaea;
          }
          #table-render td, #table-render th {
            color: #000000 !important;
          }
          .imagen{
            width: 100%;
            heigth: 100%;
          }
          .imagen:hover{
            width: 200px;
            heigth: 200px;
          }
        </style>
        <div>
          <table id="table-render" style="width: 830px; text-align: center;padding:0; margin:0">
            <tbody>
              <tr style="padding:0; margin:0">
                <td style="width: 60px" class="${styleStock}"><b>${
          data.EsPadre == 1
            ? '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />'
            : data.stock
              ? Number.isInteger(data.stock)
                ? data.stock
                : data.stock.toFixed(2)
              : data.stock
        }</b></td>
                <td style="width: 200px"><b>${data.Producto} - ${
          data.CodigoBarra
        }</b></td>
                <td style="width: 100px">${data.ProductoCategoria}</td>
                <td style="width: 100px">${data.ProductoMarca}</td>
                <td style="width: 65px" class="table-primary"><b>${
                  data.PrecioContado
                }</b></td>
                <td style="width: 5%">
                  <img src="${
                    data.Imagen == null || data.Imagen == ""
                      ? "static/no-photo.png"
                      : "/resources/images/productos/" + data.Imagen
                  }" class="imagen">
                </td>
                <!--<td style="width: 20px" class="text-center"><b>${
                  data.VentaEstrategica == 1
                    ? '<i class="fas fa-star" style="color:yellow !important" ></i>'
                    : ""
                }</b></td>-->
              </tr>
            </tbody>
          </table>   
        </div>
        `;
      }
      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${
        data.ProductoMarca
      } / ${data.ProductoModelo}
      `;
    },
    setProducto(obj) {
      this.IdProducto = obj.IdProducto;
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    }
  },
  computed: {
    filteredSeries() {
      if (!this.searchQuery) return this.lstSeriesVendida;

      const query = this.searchQuery.toLowerCase();
      return this.lstSeriesVendida.filter(item =>
        this.headerReporte.some(col => {
          const val = item[col.key];
          return (
            val != null &&
            String(val)
              .toLowerCase()
              .includes(query)
          );
        })
      );
    },
    APIProductos() {
      // if (this.idAlmacen) {
      //   return this.API + "productos/puntoventa/stock/" + this.idAlmacen;
      // } else {
      return this.API + "productos";
      // }
    }
  }
};
</script>

<style>
</style>
