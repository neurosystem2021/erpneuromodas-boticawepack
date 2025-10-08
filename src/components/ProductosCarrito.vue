<template>
  <b-row>
    <b-col md="12">
      <b-form-group class="ocultarMovil">
        <label>Agregar Productos:</label>
        <b-input-group>
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
          <input v-model="loadCode"
                  :disabled="bloquearInput?true:false"
                  @keydown.enter.prevent="loadBarcode"
                  type="text"
                  class="form-control"
                  placeholder="Codigo de Barra" />
          <b-input-group-append>
            <b-button variant="success" @click="abrirCodigoBarra"><i class="fas fa-barcode"></i></b-button>
            <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
            <!--<proveedores-tabla></proveedores-tabla>-->
            <producto-form :showButton="showButton" :cbGuardar="(id) => agregarObj(id)" ref="productoFormEdit" :producto-update="productoSelect"></producto-form>
            <!--<producto-form :showButton="showButton" :cbGuardar="(id,pxp,obj) =>{agregarGuardar(id,pxp,obj)}"  :producto-update="productoSelect" ></producto-form>-->
          </b-input-group-append>
        </b-input-group>
      </b-form-group>

      <div class="mostrarMovil">
      <b-form-group>
      <b-row>
        <b-col md="6">
          <b-input-group>
          <autocomplete
            ref="autocompleteProducto2"
            :url="APIProductos"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder="Agregar Producto"
            :on-select="setProducto"
            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto2')"
            :classes="{input: 'form-control'}">
          </autocomplete>
        </b-input-group>
        </b-col><br/>
        </b-row>
        <b-row>
        <b-col md="6">
          <b-input-group>
          <input v-model="loadCode"
          :disabled="bloquearInput?true:false"
                  @keydown.enter.prevent="loadBarcode"
                  type="text"
                  class="form-control"
                  placeholder="Codigo de Barra" />
          <b-input-group-append>
            <b-button variant="success" @click="abrirCodigoBarra"><i class="fas fa-barcode"></i></b-button>
            <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
            <!--<proveedores-tabla></proveedores-tabla>-->
            <producto-form :showButton="showButton" :cbGuardar="(id) => agregarObj(id)" ref="productoFormEditAnt" :producto-update="productoSelect"></producto-form>
            <!--<producto-form :showButton="showButton" :cbGuardar="(id,pxp,obj) =>{agregarGuardar(id,pxp,obj)}"  :producto-update="productoSelect" ></producto-form>-->
          </b-input-group-append>
        </b-input-group>
        </b-col>
      </b-row>
      </b-form-group>
      </div>


      <b-modal id="modalImagen" size="lg" ref="modalImagen" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration: underline;">{{productoNombre}}</h3>
        </div>
        <b-row style="display:flex; justify-content:center; align-items:center">
          <b-img :src="productoUrl" class="imagenGrande"></b-img>
        </b-row>
      </b-modal>

      <b-modal id="modalCodigoBarra" size="lg" ref="modalCodigoBarra" hide-footer hide-header>
        <div class="d-block text-center">
          ESCANEE EL CODIGO DE BARRA
          <br />
          <br />
          <div id="codigoBarraStyle">
            <v-quagga
              :onDetected="onBarcodeScanned"
              :readerSize="readerSize"
              :readerTypes="detecteds"
              :patchSize="'medium'"
              class="cameraBarra"
            ></v-quagga>
          </div>
        </div>
      </b-modal>

      <b-table small responsive  striped hover :items="productos" :fields="fields" ref="tablaCarrito">
        <template slot="eventPres" slot-scope="data">
          <!--<b-button size="sm" v-if="productos[data.index].estadoPxP == false" :disabled="bloquearInput?true:false" variant="info" @click="showPresentacion(productos[data.index])"><i class="fas fa-list"></i></b-button>-->
          <b-button @click="abrirImagen(productos[data.index].Producto,productos[data.index].Imagen == null || productos[data.index].Imagen==''?'static/no-photo.png': '/resources/images/productos/' + productos[data.index].Imagen)" style="margin-top:2px;" :id="'popover-imagen' + productos[data.index].IdProducto" variant="success" size="sm"><i class="fas fa-image"></i></b-button>	
          <b-popover  placement="right" :target="'popover-imagen' + productos[data.index].IdProducto" :title="productos[data.index].Producto" triggers="hover">
            <div style="text-align:center">
              <b-img style="border-radius: 10px; width:200px" fluid :src="productos[data.index].Imagen == null || productos[data.index].Imagen==''?'static/no-photo.png': '/resources/images/productos/' + productos[data.index].Imagen" alt="" @error="setAltImg" ></b-img>	
            </div>
          </b-popover>
        </template>

        <template slot="cantidad" slot-scope="data">
          <input
            v-model="productos[data.index].cantidad"
            :disabled="bloquearInput?true:false"
            type="number"
            style="width: 80px"
            min="0"
            @input="(val) => {$emit('onInputCantidad', val.target.value); actualizaDesc(data.index);
              if(setPrecio){
                productos[data.index].precio = (productos[data.index].StockPorMayor > 0 && val.target.value >= productos[data.index].StockPorMayor) ? productos[data.index].PrecioPorMayor : productos[data.index].PrecioContado
              }
            } "
            placeholder="Cantidad"
            class="form-control"/>
        </template>
        <template slot="precio" slot-scope="data">
          <input
            v-model="productos[data.index].precio"
            :disabled="bloquearInput?true:false"
            type="number"
            style="width: 80px"
            min="0"
            step="0.01"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)"
            class="form-control"/>
        </template>
        <template slot="subtotal" slot-scope="data">
          {{ getSubtotal(data.index) }}
        </template>
        <template slot="igv" slot-scope="data">
          <b-form-checkbox
            v-model="productos[data.index].TieneIgv"
            :disabled="bloquearInput?true:false"
            :value="1"
            @input="setNuevoPrecioContado(data.index)"
            :unchecked-value="0">
            <div>{{ getIgv(data.index) }}</div>
          </b-form-checkbox>
        </template>
        <!--<template slot="flete" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].FLETE"
            type="number"
            min="0"
            placeholder="Flete"></b-form-input>
        </template>-->
        
        <!--<template slot="lote" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].IdLote"
            type="text"
            min="0"
            placeholder="Lote"></b-form-input>
        </template>-->
        <template slot="precioVenta" slot-scope="data">
          <input
            style="width:80px"
            v-model="productos[data.index].nuevoPrecioContado"
            :disabled="bloquearInput?true:false"
            type="number"
            min="0"
            step="0.01"
            placeholder="Precio Venta"
            class="form-control"/>
            <b-form-text>
              <strike> {{ productos[data.index].normalPrecioContado }} </strike>
            </b-form-text>
        </template>

        <template slot="descuento" slot-scope="data">
          <input
            v-model="productos[data.index].descuento"
            :disabled="bloquearInput?true:false"
            type="number"
            min="0"
            step="0.01"
            value="0"
            class="form-control"/>
            <b-form-text style="padding: 0 !important; margin-bottom: -15px; margin-top: 0;">
              <p style="color: red; text-align: center;">Liquidación</p>
            </b-form-text>
        </template>

        <template slot="total" slot-scope="data">
          {{ getTotal(data.index) }}
        </template>
        <template slot="acciones" slot-scope="data">
          <b-button variant="danger" size="sm" :disabled="bloquearInput?true:false" @click="quitarProducto(data.index)">X</b-button>
          <b-button style="margin-top:5px" :disabled="bloquearInput?true:false" variant="primary" size="sm" @click="editarProducto(productos[data.index])"><i class="fas fa-pencil-alt fa-sm"></i></b-button>
        </template>

        <template slot="show_details" slot-scope="row">
           <b-button size="sm" variant="info" :disabled="bloquearInput?true:false" @click.stop="row.toggleDetails">></b-button>
        </template>
        <template slot="row-details" slot-scope="row">

        </template>
        <template slot="stock" slot-scope="data">
          <img v-if="data.item.EsPadre" src="static/box.png" style="width: 24px" />
          <div v-if="!data.item.EsPadre">{{ data.item.stock }}</div>
        </template>

      </b-table>
    </b-col>
    <b-col md="3">
      <slot name="operaciones"></slot>
    </b-col>
    <b-col md="4">
      <slot name="metodopago"></slot>
    </b-col>
    <b-col md="3" offset-md="2" v-if="!noSubtotal">
          <table class="table b-table border table-footer">
            <tbody>
              <tr v-if="sumatoriaDescuento > 0">
                <td class="table-success">SubTotal</td>
                <td class="table-success">{{ parseFloat(sumatoriaSubTotal) + parseFloat(sumatoriaDescuento) | redondear }}</td>
              </tr>
              <tr v-if="sumatoriaDescuento > 0">
                <td class="table-danger">Descuento</td>
                <td class="table-danger">- {{ sumatoriaDescuento  }}</td>
              </tr>
              <tr>
                <td><b>SubTotal</b></td>
                <td class="table-danger">{{ sumatoriaSubTotal  }}</td>
              </tr>
              <tr v-if="sumatoriaIgv > 0">
                <td><b>IGV</b></td>
                <td class="table-danger">{{ sumatoriaIgv }}</td>
              </tr>
              <tr>
                <td><b>TOTAL</b></td>
                <td class="table-danger">{{ sumatoriaTotal }}</td>
              </tr>
              <tr>
                <td  colspan="2" class="ocularMovil">
                  <label>pagó con:</label>
                  <b-form-input
                    type="text"
                    placeholder="Pagó con"
                    v-model="pagocon"
                    :disabled="bloquearInput?true:false"
                    @input="asignarVuelto"></b-form-input><br>
                  <label>Vuelto:</label>
                  <b>S/  {{ vuelto | redondear }}</b>
                </td>
              </tr>
            </tbody>
          </table>
    </b-col>
    <br><br>
  </b-row>
</template>

<script>
import { mapState, mapActions } from "vuex";
import ProductoForm from "@/components/ProductoForm";
import config from "@/store/config.js";
import sound from "../../static/beep.mp3";
import Vue from "vue";
import VueQuagga from "vue-quaggajs";

Vue.use(VueQuagga);

export default {
  mounted() {
    // this.alterarBuscadorProductos()
  },
  data() {
    return {
      readerSize: {
        width: 640,
        height: 480
      },
      detecteds: [
        "code_128_reader",
        "ean_reader",
        "ean_8_reader",
        "code_39_reader",
        "code_39_vin_reader",
        "codabar_reader",
        "upc_reader",
        "upc_e_reader",
        "i2of5_reader",
        "2of5_reader",
        "code_93_reader"
      ],
      result: "",
      format: "",
      API: config.API,
      loadCode: "",
      pasteCode: false,
      producto: {},
      items: [],
      handleKeyDown: false,
      vuelto: 0,
      pagocon: 0,
      productoSelect: {},
      productoNombre: "",
      productoUrl: "",
      modalAbiertCode: false
    };
  },
  created() {
    // Add barcode scan listener and pass the callback function
    this.$barcodeScanner.init(this.onBarcodeScanned);
  },
  destroyed() {
    // Remove listener when component is destroyed
    this.$barcodeScanner.destroy();
  },
  watch: {
    sumatoriaTotal(sum) {
      this.asignarVuelto();
    }
  },
  props: {
    tipo: String,
    setPrecio: Boolean,
    idAlmacen: Number,
    isVentas: Boolean,
    bloquearInput: Boolean,
    igvTotal: Boolean,
    onSetProducto: {
      type: Function,
      default(obj) {
        return obj;
      }
    },
    showButton: {
      default: true,
      type: Boolean
    },
    fields: {
      default() {
        return [
          { key: "ProductoCategoria", label: "Categoria" },
          { key: "Producto", label: "Artículo" },
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoModelo", label: "Modelo" },
          { key: "Color", label: "Color" },
          { key: "ProductoTalla", label: "Talla" },
          { key: "Genero" },
          { key: "Botapie" },
          { key: "cantidad" },
          { key: "precio", label: "P/U" },
          { key: "subtotal", label: "SubTotal", variant: "success" },
          { key: "igv", label: "IGV" },
          // { key: 'flete', label: 'FLETE' },
          // { key: 'lote', label: 'Lote' },
          { key: "precioVenta", label: "Precio venta" },
          { key: "total", label: "Total", variant: "danger" },
          { key: "acciones", label: "" }
        ];
      },
      type: Array
    },
    noSubtotal: Boolean
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      filtrados: state => state.cart.productosFiltrados
    }),
    APIProductos() {
      if (this.idAlmacen) {
        return this.API + "productos/puntoventa/stock/" + this.idAlmacen;
      } else {
        return this.API + "productos";
      }
    },
    sumatoriaSubTotal() {
      let sumatoria = 0;
      if (this.igvTotal) {
        sumatoria = this.sumatoriaTotal - this.sumatoriaIgv;
      } else {
        this.productos.map(value => {
          sumatoria += parseFloat(value.subtotal);
          if (value.descuento) {
            sumatoria -= value.descuento;
          }
        });
      }
      return this.$options.filters.redondear(sumatoria);
    },
    sumatoriaTotal() {
      let sumatoria = 0;
      this.productos.map(value => {
        sumatoria += parseFloat(value.total);
      });

      this.actualizarTotal(sumatoria);
      return this.$options.filters.redondear(sumatoria);
    },
    sumatoriaIgv() {
      let sumatoria = 0;
      if (this.igvTotal) {
        sumatoria = this.sumatoriaTotal;
        // sumatoria = sumatoria * 0.18
        sumatoria = sumatoria - sumatoria / 1.18;
      } else {
        this.productos.map(value => {
          sumatoria += parseFloat(value.igv);
        });
      }
      return this.$options.filters.redondear(sumatoria);
    },
    sumatoriaDescuento() {
      let sumatoria = 0;
      this.productos.map(value => {
        sumatoria += parseFloat(value.descuento || 0);
      });
      return this.$options.filters.redondear(sumatoria);
    }
  },
  methods: {
    ...mapActions({
      add: "agregarProducto",
      buscar: "buscarProductos",
      eliminar: "eliminarProducto",
      actualizarTotal: "actualizarTotalCart"
    }),
    abrirImagen(producto, url) {
      this.productoNombre = producto;
      this.productoUrl = url;
      this.$refs["modalImagen"].show();
    },
    abrirCodigoBarra() {
      this.$refs["modalCodigoBarra"].show();
      this.modalAbiertCode = true;
      this.result = "";
    },
    onBarcodeScanned(barcode) {
      if (this.modalAbiertCode) {
        this.result = barcode.codeResult.code;
        this.format = barcode.codeResult.format;
        const audio = new Audio(sound);
        audio.play();
        if (this.result.length > 0) {
          this.loadCode = this.result;
          this.loadBarcode();
          this.$refs["modalCodigoBarra"].hide();
          this.modalAbiertCode = false;
        }
      }

      // do something...
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    actualizaDesc(index) {
      let descGeneral = this.$options.filters.redondear(
        (Math.round(
          (((this.productos[index].PorcentajeLiquidacion > 0
            ? this.productos[index].PorcentajeLiquidacion
            : 0) *
            (this.productos[index].PrecioContado > 0
              ? this.productos[index].PrecioContado
              : 0)) /
            100) *
            10
        ) /
          10) *
          parseFloat(this.productos[index].cantidad)
      );
      Vue.set(this.productos[index], "descuento", descGeneral);
    },
    asignarVuelto() {
      this.vuelto = this.pagocon - this.sumatoriaTotal;
    },
    agregarObj(id) {
      if (id) {
        this.add([
          id,
          obj => {
            this.$emit("onAddProducto");
          },
          { setPrecio: this.setPrecio }
        ]);
      }
    },
    loadBarcode(e) {
      if (e) {
        e.preventDefault();
      }
      let idProducto = this.loadCode;

      if (idProducto) {
        this.add([
          idProducto,
          obj => {
            if (obj) {
              this.$emit("onAddProducto");
              this.$toasted.show("Producto añadido correctamente");
            } else {
              this.$toasted.show("No se encontró ningun producto");
            }
            this.loadCode = "";
          },
          { setPrecio: this.setPrecio, setSuma: true }
        ]);
      }
    },
    /*alterarBuscadorProductos() {
      this.handleKeyDown = this.$refs.autocompleteProducto.handleKeyDown

      this.$refs.autocompleteProducto.$refs.input.onpaste = (e) => {
        console.log('paste')
        this.pasteCode = (e.originalEvent || e).clipboardData.getData('text/plain')
      }

      this.$refs.autocompleteProducto.handleKeyDown = (e) =>{
        let key = e.keyCode;

        if (key == 13) {
          if(this.pasteCode) {
            e.preventDefault()
            let idProducto = this.pasteCode.substring(2, this.pasteCode.length -2);
            this.add([idProducto, () => {
              this.$emit('onAddProducto')
            }])
          } else {
            e.preventDefault()
            this.handleKeyDown(e) 
          }   
        } else {
          this.handleKeyDown(e)
        }
        this.pasteCode = false
      }
    },*/
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    editarProducto(datos) {
      this.productoSelect = datos;
      this.$refs.productoFormEdit.show = true;
      this.$refs.productoFormEditAnt.show = true;
    },
    setProducto(obj) {
      this.$refs.autocompleteProducto.setValue("");
      this.$refs.autocompleteProducto2.setValue("");
      //obj.FLETE = 0
      obj.precio = 0;
      obj.cantidad = 0;
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      if (this.tipo == "mov") {
        obj.descuento = 0;
      } else {
        obj.descuento =
          Math.round(
            (((obj.PorcentajeLiquidacion > 0 ? obj.PorcentajeLiquidacion : 0) *
              (obj.PrecioContado > 0 ? obj.PrecioContado : 0)) /
              100) *
              10
          ) / 10;
      }
      obj.nuevoPrecioContado = 0;
      obj = this.onSetProducto(obj);
      this.add([
        obj,
        () => {
          this.$emit("onAddProducto", obj);
        },
        { setPrecioCosto: this.tipo == "mov" ? true : false }
      ]);

      // Vue.set(this.productos, obj.IdProducto, obj)
    },
    renderChild(data) {
      // if (this.idAlmacen) {
      let styleStock = "";
      if (data.stock <= 0) {
        styleStock = "table-danger";
      } else {
        styleStock = "table-success";
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
        </style>
        <div>
          <table id="table-render" style="width: 1200px; text-align: center  ">
            <tbody>
              <tr>
                <td  style="width: 60px" class="${styleStock}"><b>${
        data.EsPadre == 1
          ? '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />'
          : data.stock
            ? Number.isInteger(data.stock)
              ? data.stock
              : data.stock.toFixed(2)
            : data.stock
      }</b></td>
                <td style="width: 400px" ><b>${data.Producto} - ${
        data.CodigoBarra
      }</b></td>
                <td style="width: 100px" >${data.ProductoCategoria}</td>
                <td style="width: 80px">${
                  data.ProductoModelo ? data.ProductoModelo : ""
                }</td>
                <td style="width: 120px">${
                  data.ProductoMarca ? data.ProductoMarca : ""
                }</td>
                <td style="width: 80px">${
                  data.ProductoTalla ? data.ProductoTalla : ""
                }</td>
                <td style="width: 80px">${data.Color ? data.Color : ""}</td>
                <td style="width: 80px">${data.Genero ? data.Genero : ""}</td>
                <td style="width: 80px">${data.Botapie ? data.Botapie : ""}</td>
                <td style="width: 80px">${data.Anaquel ? data.Anaquel : ""}</td>
                </td>
                <td class="table-primary"  style="width: 65px"><b>${
                  data.PrecioContado
                }</b></td>
                <td style="border: 1px solid #eaeaea;width: 100px">
                ${
                  data.Imagen
                    ? `<img class="imgProducto" style="border-radius: 10px;" src="/resources/images/productos/${
                        data.Imagen
                      }" alt="Imagen del producto" />`
                    : `<img class="imgProducto" style="border-radius: 10px; width:50px" src="/static/no-photo.png" alt="Sin imagen disponible" />`
                }
              </td>
              </tr>
            </tbody>
          </table>   
        </div>
        `;
      // }
      // return `
      //   <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${
      //   data.ProductoMarca
      // } / ${data.ProductoModelo}
      // `;
    },
    setNuevoPrecioContado(index) {
      // Añadir IGV si check IGV
      let precio = parseFloat(this.productos[index].precio);
      if (this.productos[index].TieneIgv) {
        precio += precio * 0.18;
      }

      precio += precio * (this.productos[index].PorcentajeUtilidad / 100);
      // let precio = parseFloat(this.productos[index].precio) + (this.productos[index].precio * (this.productos[index].PorcentajeUtilidad/100))

      let normalPrecio = this.$options.filters.redondear(precio);
      let nuevoPrecio = this.$options.filters.redondeoMayor(precio);

      Vue.set(this.productos[index], "normalPrecioContado", normalPrecio);
      Vue.set(this.productos[index], "nuevoPrecioContado", nuevoPrecio);
      // this.productos[index].nuevoPrecioContado = nuevoPrecio
      // return nuevoPrecio
    },
    getSubtotal(index) {
      if (this.tipo == "mov") {
        this.productos[index].descuento = 0;
      }
      let subtotal = this.$options.filters.redondear(
        this.productos[index].cantidad * this.productos[index].precio
      );
      Vue.set(this.productos[index], "subtotal", subtotal);
      // this.productos[index].subtotal = subtotal
      return subtotal;
    },
    getIgv(index) {
      let igv = 0;
      if (this.productos[index].TieneIgv) {
        igv = this.$options.filters.redondear(
          this.productos[index].subtotal * 0.18
        );
      }

      Vue.set(this.productos[index], "igv", igv);
      // this.productos[index].igv = igv
      return igv;
    },
    getTotal(index) {
      let total = parseFloat(this.productos[index].subtotal);

      if (this.productos[index].FLETE) {
        total += parseFloat(this.productos[index].FLETE);
      }
      if (this.productos[index].TieneIgv) {
        total += parseFloat(this.productos[index].igv);
      }
      if (this.productos[index].descuento) {
        total -= parseFloat(this.productos[index].descuento);
      }

      total = this.$options.filters.redondear(total);
      Vue.set(this.productos[index], "total", total);
      // this.productos[index].total = total
      return total;
    },
    getData(value) {
      this.buscar(value);
    },
    quitarProducto(index) {
      this.eliminar(index);
      this.$emit("onRemoveProducto", index);
      this.$toasted.show("Producto quitado del listado");
    }
  },
  components: {
    ProductoForm
  }
};
</script>

<style>
#modalImagen .modal-dialog {
  max-width: 75% !important;
}
.imagenGrande {
  width: 100%;
}
.imgProducto {
  transition: transform 0.3s ease;
  cursor: zoom-in;
  display: inline-block;
}
.imgProducto:hover {
  transform: scale(3);
  z-index: 100;
  position: relative;
}
#modalCodigoBarra .modal-dialog {
  max-width: 25% !important;
  margin: 5% auto;
}
#codigoBarraStyle video,
canvas {
  width: 100%;
  height: 100%;
}
.cameraBarra {
  height: 20em;
  width: auto;
  margin: 0 auto;
}
.mostrarMovil {
  display: none;
}
@media only screen and (max-width: 1440px) {
  #modalCodigoBarra .modal-dialog {
    max-width: 50% !important;
    margin: 5% auto;
  }
  .cameraBarra {
    height: 15em;
    width: auto;
    margin: 0 auto;
  }
}
@media only screen and (max-width: 750px) {
  #modalCodigoBarra .modal-dialog {
    max-width: 100% !important;
    margin: 5%;
  }
  .cameraBarra {
    height: 15em;
    width: auto;
    margin: 0 auto;
  }
  .ocultarMovil {
    display: none;
  }
  .mostrarMovil {
    display: block;
  }
}
@media only screen and (max-width: 375px) {
  #modalCodigoBarra .modal-dialog {
    max-width: 100% !important;
    margin: 5%;
  }
  .cameraBarra {
    height: 15em;
    width: auto;
    margin: 0 auto;
  }
}
@media only screen and (max-width: 700px) {
  /* For desktop: */
  .ocularMovil {
    display: none;
  }
}
</style>
