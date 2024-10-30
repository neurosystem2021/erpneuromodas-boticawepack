<template>
  <b-container>
    <b-row>
        <b-col cols="8">
            <strong>LISTADO PRESENTACIÓN </strong>
        </b-col>
        <b-col cols="4">
            <b-button variant="success" @click="abriDialogNuevaPresentacion">

                Nueva Presentación
            </b-button>
        </b-col>
        <b-col cols="12" class="mt-2">
          <table width="100%" class="lstProdConfig">
            <thead>
              <tr>
                <th style="padding: 5px">Código</th>
                <th style="padding: 5px">Categoría</th>
                <th style="padding: 5px">Producto</th>
                <th style="padding: 5px">Estado</th>
                <th style="padding: 5px">Ac</th>
              </tr>
            </thead>
            <tbody>
              <!-- <template> -->
              <tr
              :class="prod.Estado ==0 ? 'styleEliminado':''"
                            v-for="(prod,index) in $store.state.ConfProdPres.lstConfiguracionProductoPresentacion" :key="index">
                <td width="10%">{{prod.Code}}  </td>
                <td width="20%">{{prod.ProductoCategoria}} </td>
                <td width="50%">{{prod.Producto}}  </td>
                <td width="15%">{{prod.Estado ==1 ? 'Activo':'Inactivo'}}  </td>
                <td width="25%">  <b-button size="sm" variant="danger" v-if="prod.Estado==1"
                  @click="eliminarConfiguracionProductoPresentacion(prod)"
                  ><i class="fas fa-trash fa-sm"></i></b-button> </td>

                <!-- <template>
                 
                      <tr v-for="(det,index2) in prod.detalle" :key="index2">
                        <td>{{ det.Producto }}</td>
                        <td>{{ det.ProductoMedicion }}</td>
                        <td>{{ det.Cantidad }}</td>
                      </tr>
                 
                </template> -->
                
              </tr>
            <!-- </template> -->
            </tbody>
          </table>
          <!-- <b-table :items="$store.state.ConfProdPres.lstConfiguracionProductoPresentacion" :fields="fieldsListProducto">

          </b-table> -->
        </b-col>
        <b-modal v-model="mConversion" title="Configurar Presentación" size="lg" hide-footer>
            <b-row>
                <b-col cols="12" class="my-1">
                  <strong>Producto Presentación:</strong>
                    <autocomplete 
                        anchor="Producto"
                        ref="autocompleteProducto" 
                        :url="APIProductos" 
                        :customParams="{
                            sortBy: isVentas ? 'stock' : 'IdProducto',
                            sortDesc: 1,
                            limit: '25',
                            fechaHasta: getFecha()}"
                        label="writer" 
                        placeholder="Seleccionar Producto Presentación"
                        :on-select="setProducto" 
                        :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                        :classes="{ input: 'form-control' }"
                        :onShouldRenderChild="renderChild"
                        id="autocompleteProducto"
                        v-model="Producto"
                    >
                    </autocomplete>   
                    <span :style="ErrorProducto ? 'color:red' : ''">
                      {{ ErrorProducto }}

                    </span>
                </b-col>
                <b-col cols="12" class="my-2">
                  <strong>Producto Añadir:</strong>
                    <autocomplete 
                        anchor="Producto"
                        id="autocompleteProductoDetalle"
                        ref="autocompleteProductoDetalle" :url="APIProductos"
                        :customParams="{
                            sortBy: isVentas ? 'stock' : 'IdProducto',
                            sortDesc: 1,
                            limit: '25',
                            fechaHasta: getFecha()
                        }" 
                        label="writer" 
                        placeholder="Añadir Producto Detalle"
                        :on-select="setProductoDetalle" 
                        :onFocus="focusAutocomplete.bind(null, 'autocompleteProductoDetalle')"
                        :classes="{ input: 'form-control' }"
                        :onShouldRenderChild="renderChildDet">
                    </autocomplete>   
                </b-col>
                <b-col cols="12">
                    <b-table
                     striped 
                     hover
                     :items="lstProdutos" 
                    :fields="fields">
                        <template slot="cantidad" slot-scope="data">
                           <input
                            v-model="lstProdutos[data.index].cantidad" 
                           :ref="'cantidad-'+data.index"
                           :id="'cantidad-'+data.index"
                           type="number" 
                           style="width: 95px"
                           class="form-control"
                           />
                        </template>
                        <template slot="action" slot-scope="data">
                            <b-button
                             size="sm"
                             variant="danger"
                            @click="quitarProducto(data.index)"><i class="fas fa-times fa-lg"></i></b-button>
                        </template>

                    </b-table>
                    
                </b-col>
                <b-col cols="12">
                    <template #modal-footer="{ ok, cancel}">
                       
                        <b-button size="sm" variant="success" @click="guardarData()">
                            Guardar
                        </b-button>
                        <b-button size="sm" variant="danger" @click="cancel()">
                            Cancel
                        </b-button>

                    </template>
                </b-col>
            </b-row>
            
            
        </b-modal>
    </b-row>
  </b-container>

</template>

<script>
import config from "@/store/config.js";
import { mapActions, mapState } from "vuex";
export default {
  data() {
    return {
      ErrorProducto: "",
      mConversion: false,
      isVentas: true,
      API: config.API,
      APIProductos: "",
      Producto: {},
      lstProdutos: [],
      fieldsListProducto: [
        {
          key: "ProductoCategoria",
          label: "Categoría"
        },
        {
          key: "Code",
          label: "Código"
        },
        {
          key: "Producto"
        }
      ],
      fields: [
        {
          key: "ProductoCategoria",
          label: "Categoría"
        },
        {
          key: "Producto"
        },
        {
          key: "ProductoMedicion",
          label: "U. M."
        },
        {
          key: "cantidad",
          label: "Cantidad"
        },
        {
          key: "action",
          label: ""
        }
      ]
    };
  },
  async mounted() {
    this.APIProductos = this.API + "productos/puntoventa/stock/" + 1;
    await this.ObtenerListadoConfiguracion();
  },
  methods: {
    ...mapActions([
      "ObtenerListadoConfiguracion",
      "GuardarConfiguracionProductoPresentacion"
    ]),
    abriDialogNuevaPresentacion() {
      this.Producto = {};
      this.lstProdutos = [];
      this.mConversion = true;
    },
    getFecha() {
      return this.$moment().format("YYYY-MM-DD");
    },
    setProducto(obj) {
      if (this.validarProductoPresentacion(obj)) {
        this.Producto = obj;
      } else {
        this.Producto = {};
      }
    },

    async guardarData() {
      if (this.valRegistro()) {
        let data = {
          producto: this.Producto,
          productoDetalle: this.lstProdutos
        };
        await this.GuardarConfiguracionProductoPresentacion(data);
        this.mConversion = false;
      }
      await this.ObtenerListadoConfiguracion();
    },
    valRegistro() {
      if (!this.Producto) {
        this.$toasted.info("Es necesario una presentación");
        return false;
      }
      if (this.lstProdutos.length == 0) {
        this.$toasted.info(
          "Es necesario al menos un producto para la presentación."
        );
        return false;
      }
      return true;
    },
    setProductoDetalle(obj) {
      // console.log(this.validarProducto(obj));
      if (this.validarProducto(obj)) {
        obj.cantidad = 1;
        this.lstProdutos.unshift(obj);
        this.$nextTick(() => {
          setTimeout(() => {
            let inputRef = document.getElementById("cantidad-0");
            if (inputRef) {
              inputRef.focus();
              inputRef.select();
            } else {
              console.warn("Referencia cantidad-0 no encontrada");
            }
          }, 50);
        });
      }
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
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
                <td style="width: 100px">${data.PrecioContado}</td>
                
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
    renderChildDet(data) {
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
                <td style="width: 100px">${data.PrecioContado}</td>
                
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
    quitarProducto(index) {
      this.lstProdutos.splice(index, 1);
    },
    validarProductoPresentacion(prod) {
      this.ErrorProducto = "";
      if (
        this.$store.state.ConfProdPres.lstConfiguracionProductoPresentacion.some(
          v => v.IdProducto == prod.IdProducto
        )
      ) {
        this.ErrorProducto =
          "Producto ya se encuentra registrado. Seleccione otro.";
        this.$toasted.info("Producto es ya se encuentra registrado.");
        return false;
      }
      return true;
    },
    validarProducto(prod) {
      if (
        this.$store.state.ConfProdPres.lstConfiguracionProductoPresentacion.some(
          v => v.IdProducto == prod.IdProducto
        )
      ) {
        this.$toasted.info("Producto es ya se encuentra como presentación.");
        return false;
      }
      if (this.Producto.IdProducto == prod.IdProducto) {
        this.$toasted.info(
          "Producto ya se encuentra seleccinado como presentación."
        );
        return false;
      }
      if (this.lstProdutos.some(v => v.IdProducto == prod.IdProducto)) {
        this.$toasted.info("Producto ya se encuentra en el listado.");
        return false;
      }

      return true;
    },
    async eliminarConfiguracionProductoPresentacion(obj) {
      this.$swal({
        icon: "question",
        title: "Eliminar",
        html: `¿Desea Eliminar <strong> ${
          obj.Producto
        } </strong> Del listado de configuración?`,
        confirmButtonText: "Si, Eliminar",
        showDenyButton: true,
        showCancelButton: false,
        denyButtonText: "Cancelar",
        allowOutsideClick: false,
        allowEscapeKey: false
      }).then(async res => {
        if (res.isConfirmed) {
          let p = { ...obj };
          obj.Estado = obj.Estado == 1 ? 0 : 1;
          await this.axios
            .put(this.API + "/cambiar_estado_configuracion_producto", obj)
            .then(res => {});

          await this.ObtenerListadoConfiguracion();
        }
      });
    }
  },
  computed: {
    ...mapState(["lstConfiguracionProductoPresentacion"])
  }
};
</script>

<style scope>
.lstProdConfig {
  border-collapse: collapse;
  border: 1px solid black;
}

.lstProdConfig thead {
  padding: 5px !important;
}
.lstProdConfig tr td {
  /* border: 1px solid black; */
  padding: 5px;
}
.styleEliminado {
  color: red;
}
/* Style for child tables */
</style>
