<template>
  <b-container>
    <b-row class="clsReemplazo">
        <b-col cols="12">
          <h6>Reemplazos:</h6>  
        </b-col> 
        <b-col cols="12"> 
            <autocomplete
                ref="autocompleteProducto"
                :url="`${API}productos`"
                anchor="Producto"
                label="writer"
                placeholder="Agregar Producto"
                :on-select="setProducto"
                :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                :classes="{input: 'form-control'}"
                 :onShouldRenderChild="renderChild"
                >
            </autocomplete>
        </b-col>
        <b-col cols="12">
            <b-table small :items="ProductoReempleazo" :fields="fields">
                <template slot="Estado" slot-scope="data">
                     <b-form-checkbox 
                        v-model="data.item.Estado" 
                        :value="1"
                        :unchecked-value="0"
                        switch>
                    </b-form-checkbox>
                </template>
                <template slot="action" slot-scope="data">
                    <b-button @click="EliminarReemplazo(data.index)" variant="danger">
                          <i class="fa fa-trash" aria-hidden="true"></i>  
                    </b-button>
                </template>
            </b-table> 
        </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  props: ["ProductoReempleazo", "IdProducto"],
  data() {
    return {
      API: config.API,
      fields: [
        // { label: "Cod. Frabricante", key: "stock" },
        { label: "Cod. Frabricante", key: "CodigoFrabricante" },
        { label: "Cod. Interno", key: "CodigoInterno" },
        { key: "Producto" },
        { key: "ProductoCategoria",label:'Categoria' },
        { key: "ProductoMarca",label:'Marca' },
        { key: "Estado", label: "Inversa" },
        { key: "action", label: "" }
      ]
    };
  },
  methods: {
    setProducto(obj) {
      if (
        this.ProductoReempleazo.some(
          v => v.IdProductoReemplazo == obj.IdProducto
        ) ||
        obj.IdProducto == this.IdProducto
      ) {
        if (obj.IdProducto == this.IdProducto) {
          this.$toasted.error("No puede seleccionar el mismo producto");
        } else {
          this.$toasted.error("El producto ya se encuentra en la lista");
        }
        return;
      }
      obj.Estado = 1;
      obj.IdProductoReemplazo = obj.IdProducto;
      this.ProductoReempleazo.push(obj);
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    EliminarReemplazo(index) {
      this.$swal({
        icon: "question",
        title: "¿Eliminar Registro?",
        text: "El registro será eliminado.",
        showCancelButton: true,
        confirmButtonText: "Eliminar Regisrtro",
        confirmButtonColor: "#D50000",
        cancelButtonColor: "#1B5E20",
        allowOutsideClick: false,
        allowEscapeKey: false
      }).then(async res => {
        if (res.isConfirmed) {
          this.ProductoReempleazo.splice(index, 1);
        }
      });
    },
    renderChild(data) {
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
            <thead>
              <tr>
                <th>Stock.</th>
                <th>Cód. Fab.</th>
                <th>Cód. Int..</th>
                <th>Producto</th>
                <th>Categoria</th>
                <th>Marca</th>
                <th>Precio</th>
                <th>Img</th>
              </tr>  
            </thead>
            <tbody>
              <tr style="padding:0; margin:0">
                <td style="width: 60px" class="${styleStock}"><b>${data.stock}</b></td>
                <td style="width: 60px"><b>${data.EsPadre == 1? '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />': data.CodigoFrabricante}</b></td>
                <td style="width: 60px"><b>${data.CodigoInterno}</b></td>
                <td style="width: 200px"><b>${data.Producto}</b></td>
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
  }
};
</script>

<style>
.clsReemplazo {
  padding: 5% 0%;
  border: 1px solid #e0e0e0 !important;
  border-radius: 15px;
  background: #f5f5f5 !important;
}
</style>
