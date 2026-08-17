<template>
  <b-row class="inventory-cart" :class="{ 'inventory-cart--movement': tipo === 'mov' }">
    <b-col md="12">
      <div class="inventory-list-meta" v-if="productos.length || tipo === 'mov'">
        <div>
          <span class="inventory-list-meta__icon"><i class="fas fa-box-open"></i></span>
          <span>
            <strong>{{ productos.length ? 'Productos agregados' : 'Detalle del movimiento' }}</strong>
            <small>{{ productos.length ? 'Revisa cantidades, costos e importes del movimiento' : 'Agrega productos para registrar el movimiento' }}</small>
          </span>
        </div>
        <div v-if="tipo === 'mov'" class="inventory-list-meta__actions">
          <slot name="movimientoAcciones"></slot>
          <div class="inventory-list-meta__total"><small>TOTAL</small><strong>S/. {{ sumatoriaTotal }}</strong></div>
        </div>
        <b-badge pill>{{ productos.length }} {{ productos.length === 1 ? 'producto' : 'productos' }}</b-badge>
      </div>
      <b-form-group class="ocultarMovil inventory-product-search">
        <label>Agregar productos</label>
        <b-input-group class="inventory-product-search__bar">
          <autocomplete
            ref="autocompleteProducto"
            :url="APIProductos"
            :customParams="{ sortBy: 'stock', sortDesc: 1, limit: '30' }"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder="Agregar Producto"
            :on-select="setProducto"
            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
            :classes="{input: 'form-control'}"
            >
          </autocomplete>
          <b-button variant="warning" @click="startRecognition"><i class="fas fa-microphone"></i></b-button>
          <input v-model="loadCode"
                  :disabled="bloquearInput?true:false"
                  @keydown.enter.prevent="loadBarcode"
                  @keydown.prevent.tab="irAlPrimerProducto"
                  type="text"
                  class="form-control"
                  id="loadBarcode"
                  placeholder="Codigo de Barra" 
                  
                  />
          <b-input-group-append>
            <b-button variant="success" @click="abrirCodigoBarra"><i class="fas fa-barcode"></i></b-button>
            <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
            <!--<proveedores-tabla></proveedores-tabla>-->
            <producto-form :showButton="showButton" :cbGuardar="(id) => agregarObj(id)" ref="productoFormEdit" :producto-update="productoSelect"></producto-form>
            <!--<producto-form :showButton="showButton" :cbGuardar="(id,pxp,obj) =>{agregarGuardar(id,pxp,obj)}"  :producto-update="productoSelect" ></producto-form>-->
          </b-input-group-append>
        </b-input-group>
      </b-form-group>
      <p class="inventory-search-help">
        <i class="fas fa-filter"></i>
        Búsqueda avanzada por producto, categoría, marca, modelo o color.
      </p>
      <div class="mostrarMovil">
      <b-form-group>
      <b-row>
        <b-col md="6">
          <b-input-group>
          <autocomplete
            ref="autocompleteProducto2"
            :url="API + 'productos'"
            :customParams="{ sortBy: 'stock', sortDesc: 1, limit: '30' }"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder="Agregar Producto"
            :on-select="setProducto"
            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto2')"
            :classes="{input: 'form-control'}">
          </autocomplete>
          <b-button variant="warning" @click="startRecognition"><i class="fas fa-microphone"></i></b-button>
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

      <b-modal id="modalImagenCarrito" size="lg" ref="modalImagen" hide-footer hide-header>
        <div class="pos-image-gallery-title"><i class="fas fa-images"></i><h3>{{productoNombre}}</h3></div>
        <div class="pos-image-gallery-stage">
          <button v-if="productoUrls.length > 1" type="button" @click="cambiarImagen(-1)"><i class="fas fa-chevron-left"></i></button>
          <b-img :src="productoUrl" class="imagenGrande"></b-img>
          <button v-if="productoUrls.length > 1" type="button" @click="cambiarImagen(1)"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="pos-image-gallery-thumbs" v-if="productoUrls.length > 1">
          <button v-for="(url, imageIndex) in productoUrls" :key="url" type="button" :class="{active:imageIndex===productoImagenActiva}" @click="seleccionarImagen(imageIndex)"><b-img :src="url"></b-img></button>
        </div>
      </b-modal>

      <b-modal id="modalCodigoBarra" size="lg" ref="modalCodigoBarra" hide-footer title="ESCANEE EL CODIGO DE BARRA">
        <div class="d-block text-center">
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

      <b-table class="inventory-cart-table" small responsive hover :items="productos" :fields="fields" ref="tablaCarrito">
        <template slot="eventPres" slot-scope="data">
          <!--<b-button size="sm" v-if="productos[data.index].estadoPxP == false" :disabled="bloquearInput?true:false" variant="info" @click="showPresentacion(productos[data.index])"><i class="fas fa-list"></i></b-button>-->
          <b-button @click="abrirImagen(productos[data.index])" style="margin-top:2px;" :id="'popover-imagen' + productos[data.index].IdProducto" variant="success" size="sm"><i class="fas fa-image"></i></b-button>	
          <b-popover  placement="right" :target="'popover-imagen' + productos[data.index].IdProducto" :title="productos[data.index].Producto" triggers="hover">
            <div style="text-align:center">
              <b-img style="border-radius: 10px; width:200px" fluid :src="productos[data.index].Imagen == null || productos[data.index].Imagen==''?'static/no-photo.png': '/resources/images/productos/' + productos[data.index].Imagen" alt="" @error="setAltImg" ></b-img>	
            </div>
          </b-popover>
        </template>

        <template slot="cantidad" slot-scope="data">
          <input
            :id="`cantidad${data.index}`"
            v-model="productos[data.index].cantidad"
            :disabled="bloquearInput?true:false"
            type="number"
            style="width: 80px"
            min="0"
            @input="(val) => {$emit('onInputCantidad', val.target.value); actualizaDesc(data.index);} "
            placeholder="Cantidad"
            @keydown.prevent.enter="iraBusquedaProducto"
            @keydown.prevent.tab="iraBarcode"
            class="form-control"/>
        </template>
        <template slot="precio" slot-scope="data">
          <input
            v-model="productos[data.index].precio"
            :disabled="bloquearInput?true:false"
            type="number"
            style="width: 90px"
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

        <template slot="Descripcion" slot-scope="data">
          <textarea 
            :disabled="bloquearInput?true:false"
            class="form-control"
            placeholder="Descripción"
            cols="9" rows="1"
            style="width: 210px"
            v-model="productos[data.index].Descripcion"></textarea>
          <!--<b-form-input
            v-model="productos[data.index].Descripcion"
            type="text"
            placeholder="Descripción"></b-form-input>-->
        </template>

        <template slot="total" slot-scope="data">
          {{ getTotal(data.index) }}
        </template>
        <template slot="acciones" slot-scope="data">
          <b-button class="inventory-row-action inventory-row-action--delete" variant="danger" size="sm" :disabled="bloquearInput?true:false" @click="quitarProducto(data.index)"><i class="fas fa-trash-alt"></i></b-button>
          <b-button class="inventory-row-action inventory-row-action--edit" :disabled="bloquearInput?true:false" variant="primary" size="sm" @click="editarProducto(productos[data.index])"><i class="fas fa-pencil-alt fa-sm"></i></b-button>
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
    <b-col md="3" v-if="$slots.operaciones">
      <slot name="operaciones"></slot>
    </b-col>
    <b-col md="4" v-if="$slots.metodopago">
      <slot name="metodopago"></slot>
    </b-col>
    <b-col :md="tipo === 'mov' ? 4 : 3" :offset-md="tipo === 'mov' ? 8 : 2" v-if="!noSubtotal">
          <table class="table b-table border table-footer inventory-movement-summary">
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
              <tr v-if="tipo !== 'mov'">
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
    this.$nextTick(() => {
      this.$refs["autocompleteProducto"].$el.querySelector("input").focus();
    });
  },
  data() {
    return {
      readerSize: {
        width: 1600,
        height: 1080
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
      productoUrls: [],
      productoImagenActiva: 0,
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
    idAlmacen: Number,
    setPrecio: Boolean,
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
          { key: "ProductoModelo", label: "Modelo" },
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoTalla", label: "Talla" },
          { key: "Color" },
          { key: "Genero" },
          { key: "Botapie", label: "Botapie/Manga" },
          { key: "Anaquel" },
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
      // if (this.idAlmacen) {
      return this.API + "productos/puntoventa/stock/" + this.idAlmacen;
      // } else {
      //   return this.API + "productos";
      // }
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
    startRecognition() {
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }

      this.recognition = new webkitSpeechRecognition();
      this.recognition.lang = "es-ES";
      this.recognition.continuous = false;
      this.recognition.interimResults = false;

      this.recognition.onstart = () => {
        console.log("Speech recognition service has started");
      };

      this.recognition.onresult = event => {
        if (event.results.length > 0) {
          this.query = event.results[0][0].transcript;
          this.search();
        }
      };

      this.recognition.onerror = event => {
        console.error("Speech Recognition Error:", event);
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = async () => {
        if (!this.query) {
          alert("No se pudo captar ninguna palabra");
        }
      };

      try {
        this.recognition.start();
      } catch (error) {
        console.error("Error starting recognition:", error);
        alert("No se pudo iniciar el reconocimiento de voz.");
      }
    },
    search() {
      const inputElement = this.$refs["autocompleteProducto"].$el.querySelector(
        "input"
      );
      inputElement.focus();
      setTimeout(() => {
        inputElement.value = this.query;
        const event = new Event("input", { bubbles: true });
        inputElement.dispatchEvent(event);
      }, 100);
    },
    seleccionarImagen(index) {
      this.productoImagenActiva = (index + this.productoUrls.length) % this.productoUrls.length;
      this.productoUrl = this.productoUrls[this.productoImagenActiva];
    },
    cambiarImagen(delta) {
      this.seleccionarImagen(this.productoImagenActiva + delta);
    },
    abrirImagen(producto) {
      this.productoNombre = producto.Producto;
      this.productoUrls = [producto.Imagen, producto.Imagen2, producto.Imagen3]
        .filter(Boolean)
        .map(imagen => "/resources/images/productos/" + encodeURIComponent(imagen));
      if (!this.productoUrls.length) this.productoUrls = ["static/no-photo.png"];
      this.seleccionarImagen(0);
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
      e.preventDefault();
      let idProducto = this.loadCode;

      if (idProducto) {
        let params = { codigoBarra: idProducto };
        this.axios
          .get(this.API + "productos/id/" + idProducto, { params: params })
          .then(response => {
            if (response.data.IdProducto) {
              this.setProducto(response.data);
              this.$toasted.show("Producto añadido correctamente");
            } else {
              this.loadCode = "";
              this.$toasted.show("No se encontró ningun producto");
            }
          });
      }
      this.loadCode = "";
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
      const formulario = window.matchMedia("(max-width: 767px)").matches
        ? this.$refs.productoFormEditAnt
        : this.$refs.productoFormEdit;
      if (formulario) formulario.show = true;
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

      this.$nextTick(() => {
        document.getElementById("cantidad0").focus();
        document.getElementById("cantidad0").select();
      });
      // Vue.set(this.productos, obj.IdProducto, obj)
    },
    iraBusquedaProducto() {
      this.$refs["autocompleteProducto"].$el.querySelector("input").focus();
    },
    iraBarcode() {
      document.getElementById("loadBarcode").focus();

      // this.$refs["autocompleteProducto"].$el.querySelector("input").focus();
    },
    irAlPrimerProducto() {
      document.getElementById("cantidad0").focus();
      document.getElementById("cantidad0").select();
      // this.$refs["autocompleteProducto"].$el.querySelector("input").focus();
    },
    // renderChild(data) {
    //   return `
    //     <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${
    //     data.ProductoMarca
    //   } / ${data.ProductoModelo} / ${data.ProductoTalla}
    //   `;
    // },
    renderSuggestion(data) {
      const escapeHtml = value =>
        String(value == null ? "" : value).replace(/[&<>"']/g, character => {
          const entities = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;"
          };
          return entities[character];
        });
      const stockNumber = Number(data.stock);
      const stockLabel = Number.isFinite(stockNumber)
        ? Number.isInteger(stockNumber)
          ? stockNumber
          : stockNumber.toFixed(2)
        : "0";
      const hasStock = stockNumber > 0;
      const productName = escapeHtml(data.Producto || "Producto sin nombre");
      const barcode = escapeHtml(data.CodigoBarra || "Sin código");
      const details = [
        data.ProductoCategoria,
        data.ProductoMarca,
        data.ProductoModelo,
        data.ProductoTalla ? `Talla ${data.ProductoTalla}` : ""
      ]
        .filter(Boolean)
        .map(escapeHtml)
        .join(" · ");
      const image = data.Imagen
        ? `/resources/images/productos/${encodeURIComponent(data.Imagen)}`
        : "/static/no-photo.png";
      const price = Number(data.PrecioContado);
      const priceLabel = Number.isFinite(price) ? price.toFixed(2) : "0.00";

      return `
        <article class="inventory-suggestion">
          <span class="inventory-suggestion__stock ${
            hasStock ? "is-available" : "is-empty"
          }">
            <strong>${
              data.EsPadre == 1
                ? '<i class="fas fa-layer-group" aria-hidden="true"></i>'
                : escapeHtml(stockLabel)
            }</strong>
          </span>
          <span class="inventory-suggestion__main">
            <strong>${productName}</strong>
            <small>${barcode}${details ? ` · ${details}` : ""}</small>
          </span>
          <span class="inventory-suggestion__tags">
            <em>${escapeHtml(data.Color || "Sin color")}</em>
            <em>${escapeHtml(data.Genero || "Sin género")}</em>
            <em>${escapeHtml(data.Botapie || "Sin detalle")}</em>
          </span>
          <span class="inventory-suggestion__location">
            <small>Anaquel</small>
            <strong>${escapeHtml(data.Anaquel || "—")}</strong>
          </span>
          <span class="inventory-suggestion__price">
            <small>Precio</small>
            <strong>S/ ${priceLabel}</strong>
          </span>
          <span class="inventory-suggestion__photo" title="Imagen del producto">
            <i class="fas fa-camera" aria-hidden="true"></i>
            <img
              src="${image}"
              alt="${productName}"
              onerror="this.style.display='none'"
            />
          </span>
        </article>
      `;
    },
    renderChild(data) {
      return this.renderSuggestion(data);

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
          <table id="table-render" style="width: 1300px; text-align: center; padding:0; margin:0">
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
                <td style="width: 300px" ><b>${data.Producto} - ${
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
                <td style="border: 1px solid #eaeaea;width: 65px">
                ${
                  data.Imagen
                    ? `<img class="imgProducto" style="border-radius: 10px;" src="/resources/images/productos/${
                        data.Imagen
                      }" alt="Imagen del producto" />`
                    : `<img class="imgProducto" style="border-radius: 10px;" src="/static/no-photo.png" alt="Sin imagen disponible" />`
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
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
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
fieldset.b-form-group,
fieldset {
  width: 100% !important;
  max-width: 100% !important;
  min-width: 0 !important;
  border: none !important;
  padding: 0 !important;
  margin: 0 !important;
}

.input-group {
  width: 100% !important;
  flex-wrap: nowrap !important;
}

.table-responsive {
  width: 100% !important;
}
.imagenGrande {
  width: 100%;
}
#modalImagenCarrito .modal-dialog {
  max-width: min(760px, calc(100vw - 32px)) !important;
}
#modalImagenCarrito .modal-content { overflow:hidden;border:0;border-radius:18px;box-shadow:0 24px 70px rgba(7,35,65,.32); }
#modalImagenCarrito .modal-body { padding:20px; }
#modalImagenCarrito .pos-image-gallery-title { display:flex;margin-bottom:14px;align-items:center;gap:12px;color:#12375f; }
#modalImagenCarrito .pos-image-gallery-title i { display:grid;width:42px;height:42px;place-items:center;border-radius:12px;color:#fff;background-color:#0879a8; }
#modalImagenCarrito .pos-image-gallery-title h3 { margin:0;font-size:20px;font-weight:900; }
#modalImagenCarrito .pos-image-gallery-stage { display:grid;min-height:390px;padding:18px;place-items:center;grid-template-columns:44px minmax(0,1fr) 44px;gap:10px;border-radius:16px;background-color:#eef6fb; }
#modalImagenCarrito .pos-image-gallery-stage > button { display:grid;width:40px;height:40px;padding:0;place-items:center;border:0;border-radius:50%;color:#fff;background-color:#0879a8; }
#modalImagenCarrito .imagenGrande {
  display:block;
  width:auto !important;
  max-width:100% !important;
  height:auto !important;
  max-height:58vh !important;
  margin:auto;
  object-fit:contain;
}
#modalImagenCarrito .pos-image-gallery-thumbs { display:flex;padding-top:12px;justify-content:center;gap:8px; }
#modalImagenCarrito .pos-image-gallery-thumbs button { width:58px;height:58px;padding:3px;overflow:hidden;border:2px solid transparent;border-radius:10px;background-color:#eaf3f9; }
#modalImagenCarrito .pos-image-gallery-thumbs button.active { border-color:#0879a8; }
#modalImagenCarrito .pos-image-gallery-thumbs img { width:100%;height:100%;object-fit:cover; }
#modalCodigoBarra {
  padding: 0 !important;
}
#modalCodigoBarra .modal-dialog {
  max-width: 50vw !important;
  max-height: 50vh !important;
}
#modalCodigoBarra .modal-body {
  padding-bottom: 1px !important;
  margin-bottom: 1px !important;
}
#codigoBarraStyle video,
canvas {
  width: 100%;
  height: 100%;
}
.cameraBarra {
  height: 50vh;
  width: auto;
  margin: 0 auto;
}
.mostrarMovil {
  display: none;
}
@media only screen and (max-width: 1440px) {
  #modalCodigoBarra .modal-dialog {
    max-width: 50vw !important;
    max-height: 50vh !important;
  }
  .cameraBarra {
    height: 50vh;
    width: auto;
  }
}
@media only screen and (max-width: 750px) {
  #modalCodigoBarra .modal-dialog {
    max-width: 100vw !important;
    max-height: 80vh !important;
  }
  .cameraBarra {
    height: 80vh;
    width: auto;
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
    max-width: 100vw !important;
    max-height: 80vh !important;
  }
  .cameraBarra {
    height: 80vh;
    width: auto;
  }
}
@media only screen and (max-width: 700px) {
  /* For desktop: */
  .ocularMovil {
    display: none;
  }
}
.imgProducto {
  display:block;
  width:42px;
  height:42px;
  margin:auto;
  object-fit:contain;
  border-radius:8px!important;
  background:#fff;
}
.imgProducto:hover {
  width:42px;
}

/* Acabado moderno para el detalle del movimiento de inventario */
.inventory-cart {
  margin: 0 !important;
}
.inventory-cart > .col-md-12 {
  padding: 0;
}
.inventory-product-search {
  margin-bottom: 0 !important;
}
.inventory-product-search > label {
  display: none;
}
.inventory-product-search__bar {
  display: flex;
  min-height: 56px;
  overflow: visible;
  flex-wrap: nowrap !important;
  border: 1px solid #d5e3f0;
  border-radius: 15px;
  background: #fff;
  box-shadow: 0 9px 24px rgba(25, 61, 103, .09);
}
.inventory-product-search__bar > div:first-child {
  flex: 1 1 43%;
  min-width: 250px;
}
.inventory-product-search__bar .autocomplete {
  position: relative;
  width: 100%;
}
.inventory-product-search__bar .autocomplete ul {
  left: 0;
  right: auto;
  z-index: 1085;
  width: min(1460px, calc(100vw - 90px));
  min-width: 760px;
  max-height: none;
  margin-top: 9px;
  padding: 10px;
  overflow-x: hidden;
  overflow-y: visible;
  border: 1px solid #cfe0ee;
  border-radius: 18px;
  background: rgba(247, 251, 255, .985);
  box-shadow: 0 22px 48px rgba(8, 39, 76, .22);
  backdrop-filter: blur(14px);
}
.inventory-cart--movement .inventory-product-search__bar .autocomplete ul {
  width: calc(100vw - 84px);
  max-width: 1780px;
  max-height: none;
  overflow-y: visible;
}
.inventory-product-search__bar .autocomplete ul::before {
  display: none;
}
.inventory-product-search__bar .autocomplete ul li {
  margin: 0 0 4px;
  padding: 0;
  border: 0;
}
.inventory-product-search__bar .autocomplete ul li:last-child {
  margin-bottom: 0;
}
.inventory-product-search__bar .autocomplete ul li a,
.inventory-product-search__bar .autocomplete ul li a:hover,
.inventory-product-search__bar .autocomplete ul li a:focus {
  display: block;
  padding: 0;
  border: 0;
  border-radius: 14px;
  color: inherit;
  background: transparent;
  text-decoration: none;
}
.inventory-product-search__bar .autocomplete ul li.focus-list a {
  color: inherit;
  background: transparent;
}
.inventory-suggestion {
  display: grid;
  grid-template-columns: 78px minmax(360px, 2.7fr) minmax(285px, 1.7fr) 92px 125px 58px;
  align-items: center;
  gap: 0;
  min-height: 62px;
  padding: 0 8px;
  border: 1px solid #dbe7f1;
  border-radius: 14px;
  color: #16314f;
  background: linear-gradient(135deg, #fff 0%, #f7fbff 72%, #eef8ff 100%);
  box-shadow: 0 5px 14px rgba(28, 72, 115, .07);
  transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.inventory-product-search__bar .autocomplete ul li a:hover .inventory-suggestion,
.inventory-product-search__bar .autocomplete ul li.focus-list .inventory-suggestion {
  border-color: #64b9e6;
  background: linear-gradient(135deg, #f5fbff 0%, #e7f7ff 100%);
  box-shadow: 0 10px 22px rgba(15, 113, 171, .15);
  transform: translateY(-1px);
}
.inventory-product-search__bar .autocomplete ul li a:hover .inventory-suggestion__main strong,
.inventory-product-search__bar .autocomplete ul li.focus-list .inventory-suggestion__main strong { color:#0b284c!important; }
.inventory-product-search__bar .autocomplete ul li a:hover .inventory-suggestion__main small,
.inventory-product-search__bar .autocomplete ul li.focus-list .inventory-suggestion__main small { color:#667c93!important; }
.inventory-product-search__bar .autocomplete ul li a:hover .inventory-suggestion__tags em,
.inventory-product-search__bar .autocomplete ul li.focus-list .inventory-suggestion__tags em { color:#45627f!important; }
.inventory-suggestion__stock,
.inventory-suggestion__photo {
  display: grid;
  place-items: center;
  align-self: stretch;
  border-radius: 11px;
}
.inventory-suggestion__stock {
  color: #006c39;
  border: 1px solid #35dc83;
  background: #aef4cd;
  box-shadow: inset 0 0 0 1px rgba(255,255,255,.35), 0 4px 12px rgba(16,185,105,.18);
}
.inventory-suggestion__stock.is-empty {
  color: #b50024;
  border-color: #ff5570;
  background: #ffc0ca;
  box-shadow: inset 0 0 0 1px rgba(255,255,255,.32), 0 4px 12px rgba(239,35,67,.2);
}
.inventory-suggestion__stock small,
.inventory-suggestion__location small,
.inventory-suggestion__price small {
  align-self: end;
  font-size: 9px;
  font-style: normal;
  font-weight: 900;
  letter-spacing: .45px;
  text-transform: uppercase;
  opacity: .72;
}
.inventory-suggestion__stock strong {
  align-self: center;
  font-size: 19px;
  font-weight: 900;
}
.inventory-suggestion__main,
.inventory-suggestion__location,
.inventory-suggestion__price {
  display: flex;
  min-width: 0;
  flex-direction: column;
}
.inventory-suggestion__main,
.inventory-suggestion__tags,
.inventory-suggestion__location,
.inventory-suggestion__price {
  min-height: 48px;
  padding: 6px 11px;
  justify-content: center;
  border-left: 1px solid #e3edf5;
}
.inventory-suggestion__main strong {
  overflow: hidden;
  color: #0b284c;
  font-size: 14px;
  font-weight: 900;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.inventory-suggestion__main small {
  margin-top: 4px;
  overflow: hidden;
  color: #667c93;
  font-size: 10px;
  font-weight: 700;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.inventory-suggestion__tags {
  display: flex;
  min-width: 0;
  flex-wrap: wrap;
  gap: 5px;
}
.inventory-suggestion__tags em {
  max-width: 100%;
  padding: 4px 7px;
  overflow: hidden;
  border: 1px solid #d4e4f1;
  border-radius: 999px;
  color: #45627f;
  background: #f0f6fb;
  font-size: 9px;
  font-style: normal;
  font-weight: 800;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.inventory-suggestion__location {
  align-items: center;
}
.inventory-suggestion__location strong {
  margin-top: 3px;
  font-size: 13px;
  font-weight: 900;
}
.inventory-suggestion__price {
  align-items: flex-end;
  padding-right: 4px;
}
.inventory-suggestion__price strong {
  margin-top: 3px;
  color: #08714b;
  font-size: 15px;
  font-weight: 900;
  white-space: nowrap;
}
.inventory-suggestion__photo {
  position: relative;
  order: 6;
  width: 42px;
  height: 42px;
  overflow: hidden;
  border: 1px solid #cfe1ef;
  color: #1684c5;
  background: linear-gradient(145deg, #eff8ff, #dff1fc);
  box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .72);
}
.inventory-suggestion__photo i {
  font-size: 18px;
}
.inventory-suggestion__photo img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  background: #edf6fc;
}
.inventory-product-search__bar > input.form-control {
  flex: 1 1 43%;
  min-width: 260px;
}
.inventory-product-search__bar .form-control {
  min-height: 54px;
  border: 0 !important;
  border-radius: 14px !important;
  padding: 0 17px;
  font-size: 15px;
  font-weight: 700;
  background: transparent;
}
.inventory-product-search__bar > .btn,
.inventory-product-search__bar .input-group-append .btn {
  min-width: 55px;
  min-height: 54px;
  border: 0;
  border-radius: 0;
  color: #fff;
  background: linear-gradient(145deg, #103968, #087aa9);
  transition: transform .18s ease, filter .18s ease, box-shadow .18s ease;
}
.inventory-product-search__bar > .btn-warning {
  background: linear-gradient(145deg, #123f72, #0a78aa);
}
.inventory-product-search__bar .btn-success {
  background: linear-gradient(145deg, #0cad68, #18c879);
}
.inventory-product-search__bar .btn-primary {
  border-radius: 0 14px 14px 0;
}
.inventory-product-search__bar .btn:hover {
  z-index: 3;
  filter: brightness(1.08);
  box-shadow: 0 7px 17px rgba(8, 66, 125, .24);
  transform: translateY(-1px);
}
.inventory-search-help {
  margin: 8px 4px 14px;
  color: #718299;
  font-size: 11px;
  font-weight: 700;
}
.inventory-search-help i {
  margin-right: 6px;
  color: #1586c7;
}
.inventory-list-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin: 2px 0 10px;
  padding: 11px 15px;
  border: 1px solid #dbe8f3;
  border-radius: 14px;
  color: #183653;
  background: linear-gradient(135deg, #f8fbff 0%, #eef7ff 100%);
}
.inventory-list-meta > div,
.inventory-list-meta > div > span:last-child {
  display: flex;
  align-items: center;
}
.inventory-list-meta > div > span:last-child {
  align-items: flex-start;
  flex-direction: column;
}
.inventory-list-meta__icon {
  display: grid;
  place-items: center;
  width: 38px;
  height: 38px;
  margin-right: 11px;
  border-radius: 11px;
  color: #fff;
  background: linear-gradient(145deg, #123f72, #0c8dbc);
  box-shadow: 0 7px 14px rgba(14, 91, 145, .18);
}
.inventory-list-meta strong {
  font-size: 13px;
  font-weight: 900;
}
.inventory-list-meta small {
  margin-top: 1px;
  color: #70849a;
  font-size: 10px;
  font-weight: 700;
}
.inventory-list-meta .badge {
  padding: 7px 12px;
  color: #08714d;
  background: #d9f7e8;
  font-size: 10px;
  font-weight: 900;
}
.inventory-cart-table {
  margin: 0;
  overflow: auto;
  border: 1px solid #dae6f1;
  border-radius: 16px;
  background: linear-gradient(180deg, #f8fbfe, #eef6fc);
  box-shadow: 0 12px 28px rgba(26, 60, 98, .08);
}
.inventory-cart-table .table {
  min-width: 1040px;
  margin: 0;
  color: #263d59;
  border-collapse: separate;
  border-spacing: 0 7px;
  background: transparent;
}
.inventory-cart-table .table thead th {
  position: sticky;
  top: 0;
  z-index: 2;
  padding: 12px 10px;
  border-top: 0;
  border-bottom: 0;
  color: #3b5875;
  background: linear-gradient(180deg, #edf5fb, #e3eef7);
  font-size: 11px;
  font-weight: 900;
  letter-spacing: .45px;
  text-transform: uppercase;
  white-space: nowrap;
}
.inventory-cart-table .table tbody td {
  height: 62px;
  padding: 9px 10px;
  vertical-align: middle;
  border-top: 1px solid #e2ebf3;
  border-bottom: 1px solid #e2ebf3;
  font-size: 12px;
  font-weight: 700;
  background: rgba(255, 255, 255, .96);
}
.inventory-cart-table .table tbody td:first-child {
  border-left: 1px solid #e2ebf3;
  border-radius: 12px 0 0 12px;
}
.inventory-cart-table .table tbody td:last-child {
  border-right: 1px solid #e2ebf3;
  border-radius: 0 12px 12px 0;
}
.inventory-cart-table .table-hover tbody tr:hover {
  background: transparent;
}
.inventory-cart-table .table-hover tbody tr:hover td {
  border-color: #bcd9ee;
  background: #f2f9ff;
  box-shadow: 0 7px 18px rgba(24, 83, 132, .06);
}
.inventory-cart-table .table tbody td.table-info {
  color: #075a75;
  background: #dff6fb;
}
.inventory-cart-table .table tbody td.table-success {
  color: #08663f;
  background: #dff7e9;
}
.inventory-cart-table .table tbody td.table-danger {
  color: #92243a;
  background: #ffe3e8;
}
.inventory-cart-table .form-control {
  min-height: 40px;
  border: 1px solid #cfdeec;
  border-radius: 10px;
  text-align: center;
  font-weight: 800;
  background: #f9fcff;
}
.inventory-cart-table .btn-success {
  border: 0;
  border-radius: 10px;
  background: linear-gradient(145deg, #08a85d, #20c977);
  box-shadow: 0 7px 15px rgba(16, 176, 103, .22);
}
.inventory-row-action {
  display: inline-grid;
  place-items: center;
  width: 38px;
  height: 38px;
  margin: 2px;
  padding: 0 !important;
  border: 0 !important;
  border-radius: 12px !important;
  transition: transform .18s ease, filter .18s ease !important;
}
.inventory-row-action--delete {
  background: linear-gradient(145deg, #ff405d, #e51f3d) !important;
  box-shadow: 0 7px 15px rgba(229, 31, 61, .22);
}
.inventory-row-action--edit {
  background: linear-gradient(145deg, #1979e9, #0b58bb) !important;
  box-shadow: 0 7px 15px rgba(25, 121, 233, .22);
}
.inventory-row-action:hover {
  transform: translateY(-2px);
  filter: brightness(1.08);
}
.inventory-movement-summary {
  width: 100%;
  margin-top: 18px;
  overflow: hidden;
  border: 1px solid #dce8f2 !important;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 14px 30px rgba(29, 65, 106, .11);
}
.inventory-summary-top {
  display: flex;
  justify-content: flex-end;
  margin: 4px 0 14px;
}
.inventory-summary-top .inventory-movement-summary {
  width: min(100%, 555px);
  margin-top: 0;
}
.inventory-movement-summary td {
  padding: 15px 17px !important;
  border: 0 !important;
  font-weight: 800;
}
.inventory-movement-summary tr:last-child td {
  color: #fff;
  background: linear-gradient(135deg, #0d356c, #078db8);
  font-size: 16px;
  letter-spacing: .2px;
}
.inventory-movement-summary tr:last-child td:last-child {
  text-align: right;
  font-size: 19px;
}
.inventory-movement-summary .table-success {
  color: #0b6746;
  background: #d9f7e8;
}
.inventory-list-meta__actions {
  flex: 1;
  justify-content: center;
  gap: 12px;
  padding: 0 18px;
}
.inventory-list-meta__actions .inventory-save-button--inline {
  min-width: 220px;
  min-height: 46px;
  margin: 0;
  border-radius: 13px !important;
}
.inventory-list-meta__total {
  display: flex;
  min-width: 125px;
  min-height: 46px;
  padding: 6px 15px;
  align-items: flex-start !important;
  justify-content: center;
  flex-direction: column;
  border-radius: 13px;
  color: #fff;
  background: linear-gradient(135deg, #0d356c, #078db8);
  box-shadow: 0 8px 18px rgba(13, 53, 108, .18);
}
.inventory-list-meta__total small { margin:0;color:#d9f4ff;font-size:9px;font-weight:900; }
.inventory-list-meta__total strong { color:#fff;font-size:17px;font-weight:900; }
.inventory-movement-summary .table-danger {
  color: #97253a;
  background: #ffe2e7;
}
@media (max-width: 767px) {
  .inventory-product-search__bar {
    display: none;
  }
  .inventory-search-help {
    display: none;
  }
  .inventory-cart-table {
    border-radius: 12px;
  }
  .inventory-cart > .col-md-3,
  .inventory-cart > .col-md-4 {
    padding: 8px 0;
  }
}
@media (max-width: 1100px) {
  .inventory-product-search__bar .autocomplete ul {
    width: calc(100vw - 70px);
    min-width: 0;
  }
  .inventory-suggestion {
    grid-template-columns: 58px minmax(210px, 1fr) minmax(150px, .8fr) 92px 44px;
  }
  .inventory-suggestion__location {
    display: none;
  }
}
@media (max-width: 720px) {
  .mostrarMovil .autocomplete {
    position: relative;
    width: 100%;
  }
  .mostrarMovil .autocomplete ul {
    left: 0;
    width: calc(100vw - 48px);
    max-height: 330px;
    padding: 8px;
    overflow-y: auto;
    border: 1px solid #cfe0ee;
    border-radius: 15px;
    background: #f7fbff;
    box-shadow: 0 18px 38px rgba(8, 39, 76, .22);
  }
  .inventory-suggestion {
    grid-template-columns: 54px minmax(0, 1fr) 86px 42px;
    gap: 9px;
    min-height: 64px;
    padding: 8px;
  }
  .inventory-suggestion__tags,
  .inventory-suggestion__location {
    display: none;
  }
  .inventory-suggestion__main strong {
    font-size: 12px;
  }
  .inventory-suggestion__main small {
    font-size: 9px;
  }
  .inventory-suggestion__price strong {
    font-size: 12px;
  }
}
</style>
