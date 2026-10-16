<template>
  <b-row>
    <b-col md="12">
      <b-form-group>
        <b-input-group class="confront-searchbar">
          <div class="confront-product-search">
            <autocomplete
              ref="autocompleteProducto"
              :url="APIProductos"
              :customParams="{ 'sortBy':isVentas?'stock':'IdProducto', 'sortDesc': 1 ,limit: '25' }"
              :onShouldRenderChild="renderChild"
              anchor="Producto"
              label="writer"
              placeholder="Agregar Producto"
              :on-select="abrirCalcularM2"
              :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
              :classes="{input: 'form-control'}">
            </autocomplete>
            <button
              type="button"
              class="confront-voice-button"
              :class="{ 'is-listening': isListening }"
              :aria-pressed="isListening ? 'true' : 'false'"
              :title="isListening ? 'Detener busqueda por voz' : 'Buscar producto por voz'"
              @click="toggleVoiceSearch">
              <i :class="isListening ? 'fas fa-stop' : 'fas fa-microphone'"></i>
              <span>{{ isListening ? 'Escuchando' : 'Voz' }}</span>
            </button>
          </div>
          <input v-model="loadCode"
                  @keydown.enter.prevent="loadBarcode"
                  type="text"
                  class="form-control"
                  placeholder="Codigo de Barra" />
          <b-input-group-append class="confront-code-actions">
             <b-button
               class="confront-camera-button"
               variant="success"
               :disabled="!idAlmacen"
               title="Escanear con la camara"
               @click="abrirEscaner">
               <i class="fas fa-barcode"></i><span> Escanear</span>
             </b-button>
             <b-button class="confront-code-search-button" variant="primary" title="Buscar código" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
            <producto-form v-if="tipo!='mov'" :showButton="showButton" :cbGuardar="(id,pxp,obj) =>{agregarGuardar(id,pxp,obj)}" ref="productoFormEdit" :producto-update="productoSelect"></producto-form>
          </b-input-group-append>
        </b-input-group>
      </b-form-group>

      <b-modal
        v-model="mostrarEscaner"
        dialog-class="confront-scanner-dialog"
        content-class="confront-scanner-modal"
        title="Escanear codigo de barra"
        hide-footer
        centered>
        <div class="confront-scanner-stage">
          <barcode-camera-scanner
            v-if="mostrarEscaner"
            :onDetected="onBarcodeScanned"
            :readerSize="readerSize"
            :readerTypes="readerTypes">
          </barcode-camera-scanner>
          <div class="confront-scanner-guide" aria-hidden="true"><span></span></div>
        </div>
        <p class="confront-scanner-help">Centre todas las barras dentro del recuadro y mantenga la camara estable.</p>
      </b-modal>

      <b-modal id="modalImagen" size="lg" ref="modalImagen" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration: underline;">{{productoNombre}}</h3>
        </div>
        <b-row style="display:flex; justify-content:center; align-items:center">
          <b-img :src="productoUrl" class="imagenGrande"></b-img>
        </b-row>
      </b-modal>


      <b-table class="confront-desktop-table" small responsive striped hover :items="productos" :fields="fields" ref="tablaCarrito" style="margin-top:10px">
        <template slot="eventPres" slot-scope="data">
          <!--<b-button size="sm" v-if="productos[data.index].estadoPxP == false" :disabled="bloquearInput?true:false" variant="info" @click="showPresentacion(productos[data.index])"><i class="fas fa-list"></i></b-button>-->
          <b-button @click="abrirImagen(productos[data.index].Producto,productos[data.index].Imagen == null || productos[data.index].Imagen==''?'static/no-photo.png': '/resources/images/productos/' + productos[data.index].Imagen)" style="margin-top:2px;" :id="'popover-imagen' + productos[data.index].IdProducto" variant="success" size="sm"><i class="fas fa-image"></i></b-button>	
          <b-popover placement="right" :target="'popover-imagen' + productos[data.index].IdProducto" :title="productos[data.index].Producto" triggers="hover">
            <div style="text-align:center">
              <b-img style="border-radius: 10px; width:200px" fluid :src="productos[data.index].Imagen == null || productos[data.index].Imagen==''?'static/no-photo.png': '/resources/images/productos/' + productos[data.index].Imagen" alt="" @error="setAltImg" ></b-img>	
            </div>
          </b-popover>
        </template>

        <template slot="obsequio" slot-scope="data">
          <div class="obsequio" v-if="tipoMov==0">
            <b-form-checkbox
              button-variant="primary"
              v-model="productos[data.index].EsObsequio"
              :value="1"
              @input="setObsequio(data.index)"
              :unchecked-value="0">
            </b-form-checkbox>
          </div>
        </template>

        <template slot="cantxPrecio" slot-scope="data" v-if="productos[data.index].estadoPxP == false">
          <input
            v-model="productos[data.index].cantxPrecio"
            type="number"
            style="width: 80px"
            step="0.001"
            min="0"
            :disabled="bloquearInput?true:false"
            @input="(val) => changeCant(val.target.value, productos[data.index])"
            placeholder="Cantidad"
            class="form-control" />
          <span>x{{productos[data.index].cantidadPres}}</span>
        </template>

        <template slot="cantidad" slot-scope="data">
          <div class="confront-qty-control">
            <button type="button" aria-label="Restar una unidad" :disabled="bloquearInput || esDelivery" @click="ajustarCantidadFisica(data.index, -1)">−</button>
            <input
              v-model.number="productos[data.index].cantidad"
              :disabled="bloquearInput || esDelivery"
              type="number"
              inputmode="numeric"
              class="form-control"
              step="1"
              min="0"
              @input="actualizarCantidadFisica(data.index)"
              placeholder="0"/>
            <button type="button" aria-label="Sumar una unidad" :disabled="bloquearInput || esDelivery" @click="ajustarCantidadFisica(data.index, 1)">+</button>
          </div>
        </template>
        <template slot="precio" slot-scope="data">
          <input
            v-model="productos[data.index].precio"
            :disabled="productos[data.index].disabled || bloquearInput?true:false || esDelivery || (productos[data.index].EsObsequio==1 && tipoMov==0 ?true:false) || (isVentas && idPerfil!=1)"
            type="number"
            style="width: 95px"
            min="0"
            step="0.00001"
            class="form-control"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)"
            />
        </template>
        <template slot="subtotal" slot-scope="data">
          {{ getSubtotal(data.index) }}
        </template>
        <template slot="igv" slot-scope="data">
          <b-form-checkbox
            v-model="productos[data.index].TieneIgv"
            :value="1"
            :disabled="bloquearInput?true:false || (productos[data.index].EsObsequio==1 && tipoMov==0 ?true:false)"
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
        
        <template slot="manoobra" slot-scope="data">
          <input 
            @change="(e) => addManoDeObra(data.index, e)"
            v-if="productos[data.index].CodigoBarra != 'MANODEOBRA' " 
            :disabled="bloquearInput?true:false"
            v-model="productos[data.index].EsManoDeObra"
            class="form-check-input" type="checkbox" />
        </template>

        <template slot="precioVenta" slot-scope="data">
          <input
            :disabled="bloquearInput?true:false"
            style="width:95px"
            v-model="productos[data.index].nuevoPrecioContado"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            placeholder="Precio Venta"/>
            <b-form-text>
              <strike> {{ productos[data.index].normalPrecioContado }} </strike>
            </b-form-text>
        </template>

        <template slot="utilidad" slot-scope="data">
          <input 
            :disabled="bloquearInput?true:false || (idPerfil == 1) ? false : true || esDelivery"
            class="form-control"
            v-model="productos[data.index].PorcentajeUtilidad"
            style="width: 80px"
            type="number" 
            step="0.01"
            @input="cambiarPrecio(data.index, productos[data.index])" 
            @mouseup="cambiarPrecio(data.index, productos[data.index])" 
            />
            <small>ganancia: {{ (productos[data.index].precio - productos[data.index].PrecioCosto) | redondear }}</small>
        </template>

        <template slot="descuento" slot-scope="data">
          <input
            :disabled="bloquearInput?true:false  || esDelivery"
            v-model="productos[data.index].PorcentajeDesc"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            value="0"
            :max="tipoVenta==2?productos[data.index].PorcentajeDescMax:8"
            @input="val =>{productos[data.index].descuento = Math.round(getSubtotal(data.index) * val.target.value/100*10)/10; actualizarDescu(data.index); } "/>
            <b-form-text class="text-center">
                <b style="color:#dc3545">% Max: {{tipoVenta==2?productos[data.index].PorcentajeDescMax:8}}</b>
                <br>
                <b>Desc: S/{{productos[data.index].descuento}}</b>      
            </b-form-text>
        </template>

        <template slot="FechaAlquiler" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].FechaAlquiler"
            type="date"
            placeholder="Fecha Fin"
            :value="formatFecha(data.item.FechaAlquiler)"></b-form-input>
        </template>

        <template slot="FechaAlquilerFin" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].FechaAlquilerFin"
            type="date"
            placeholder="Fecha Fin"
            :value="formatFecha(data.item.FechaAlquilerFin)"></b-form-input>
        </template>

        <template slot="Descripcion" slot-scope="data">
          <textarea 
            :disabled="bloquearInput?true:false"
            class="form-control"
            placeholder="Descripción"
            cols="9" rows="1"
            style="width: 170px"
            v-model="productos[data.index].Descripcion"></textarea>
          <!--<b-form-input
            v-model="productos[data.index].Descripcion"
            type="text"
            placeholder="Descripción"></b-form-input>-->
        </template>        

        <template slot="total" slot-scope="data">
          {{ getTotal(data.index) }}
          <br>
          <h6 v-if="false"><i>Prof.</i></h6>
          <input 
             v-if="false"
            class="form-check-input" 
            type="checkbox" 
            :disabled="bloquearInput?true:false"
            @change="(e) => {
              $emit('onCheckProf', e)
              asignarDisabledProf(data.index, e)
            }"
          />
        </template>
        <template slot="acciones" slot-scope="data">
          <b-button :disabled="bloquearInput?true:false" variant="danger" size="sm" @click="quitarProducto(data.index)"><i class="fas fa-times fa-lg"></i></b-button>
          <!--<b-button  style="margin-top:5px" :disabled="bloquearInput?true:false" v-if="(idPerfil == 1) ? true : false"  variant="primary" size="sm" @click="editarProducto(productos[data.index])"><i class="fas fa-pencil-alt fa-sm"></i></b-button>-->
        </template>

        <template slot="show_details" slot-scope="row">
           <b-button :disabled="bloquearInput?true:false" size="sm" variant="info" @click.stop="row.toggleDetails">></b-button>
        </template>
        <template slot="row-details" slot-scope="row">

        </template>

        <template slot="stock" slot-scope="data">
          <div v-if="data.item.CodigoBarra=='COSTODELIVERY'">
              <img src="static/moto-delivery.png" style="width: 24px" />
          </div>
          <div v-else>
            <div>{{ data.item.stock }}</div>
            <img v-if="data.item.EsPadre" src="static/box.png" style="width: 24px" />
          </div>
        </template>
      </b-table>

      <div class="confront-mobile-list" aria-label="Productos por confrontar">
        <article v-for="(producto, index) in productos" :key="producto.IdProducto || index" class="confront-mobile-card">
          <div class="confront-mobile-card__head">
            <div class="confront-mobile-card__product">
              <small>{{ producto.CodigoBarra || 'SIN CODIGO' }}</small>
              <strong>{{ producto.Producto }}</strong>
            </div>
            <div class="confront-qty-control confront-qty-control--mobile">
              <button type="button" aria-label="Restar una unidad" :disabled="bloquearInput" @click="ajustarCantidadFisica(index, -1)">−</button>
              <input v-model.number="producto.cantidad" aria-label="Cantidad fisica" type="number" min="0" step="1" inputmode="numeric" @input="actualizarCantidadFisica(index)">
              <button type="button" aria-label="Sumar una unidad" :disabled="bloquearInput" @click="ajustarCantidadFisica(index, 1)"><i class="fas fa-plus"></i></button>
            </div>
            <button type="button" class="confront-mobile-card__remove" aria-label="Quitar producto" :disabled="bloquearInput" @click="quitarProducto(index)"><i class="fas fa-times"></i></button>
          </div>
        </article>
      </div>
    </b-col>

    <br><br>
  </b-row>
</template>

<script>
import { mapState, mapActions } from "vuex";
import ProductoForm from "./ProductoForm";
import BarcodeCameraScanner from "./BarcodeCameraScanner.vue";
import config from "@/store/config.js";
import sound from "../../static/beep.mp3";

export default {
  mounted() {
    // this.alterarBuscadorProductos()
  },
  beforeDestroy() {
    if (this.recognition && this.isListening) {
      this.voiceSearchCancelled = true;
      this.recognition.stop();
    }
  },
  data() {
    return {
      API: config.API,
      loadCode: "",
      pasteCode: false,
      producto: {},
      items: [],
      handleKeyDown: false,
      vuelto: 0,
      pagocon: 0,
      cliente: {},
      preciosProd: [],
      objProd: {},
      cant: 0,
      precioCont: 0,
      medicionPro: "UNIDAD",
      productoSelect: {},
      idPerfil: sessionStorage.getItem("IdPerfil"),
      productoNombre: "",
      productoUrl: "",
      mensajeM2: "",
      M2: "",
      mostrarEscaner: false,
      isListening: false,
      recognition: null,
      voiceQuery: "",
      voiceSearchCancelled: false,
      voiceSearchFailed: false,
      readerSize: { width: 1280, height: 720 },
      readerTypes: [
        "code_128_reader",
        "ean_reader",
        "ean_8_reader",
        "upc_reader",
        "upc_e_reader"
      ]
    };
  },
  watch: {
    sumatoriaTotal(sum) {
      this.asignarVuelto();
    },
    productos(prods) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");

      if (pi >= 0 && !this.productos[pi].precioBruta) {
        this.productos[pi].precioBruta = this.productos[pi].precio;
      }
    }
  },
  props: {
    isVentas: Boolean,
    tipoVenta: Number,
    tipo: String,
    setPrecio: Boolean,
    tipoCambio: Number,
    igvTotal: Boolean,
    tipoMov: Number,
    idAlmacen: Number,
    bloquearInput: Boolean,
    esDelivery: Boolean,
    ordenNormal: Boolean,
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
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center"
          },
          { key: "CodigoBarra", label: "Código" },
          // { key: 'ProductoCategoria', label: 'Categoria'},
          { key: "Producto", label: "Artículo" },
          { key: "Descripcion", label: "Descripción" },
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoPresentacion", label: "Rack" },
          // { key: 'ProductoMedicion', label: 'Medición' },
          //{ key: 'obsequio', label: 'Obsequio', class:'thcenter' },
          { key: "cantidad" },
          { key: "precio", label: "P/U" },
          { key: "subtotal", label: "SubTotal", variant: "success" },
          { key: "igv", label: "IGV" },
          // { key: 'flete', label: 'FLETE' },
          // { key: 'lote', label: 'Lote' },
          { key: "precioVenta", label: "Precio venta (S/)" },
          { key: "total", label: "Total", variant: "danger" },
          { key: "acciones", label: "" }
        ];
      },
      type: Array
    }
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
        // sumatoria = this.sumatoriaTotal - this.sumatoriaIgv
        sumatoria = this.sumatoriaTotal / 1.18;
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
        //sumatoria = sumatoria * 0.18
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
      actualizarTotal: "actualizarTotalCart",
      actualizarProd: "actualizarProducto",
      obtenerStok: "obtenerStock"
    }),
    abrirCalcularM2(obj) {
      this.setProducto(obj, false);
    },
    toggleVoiceSearch() {
      if (this.isListening && this.recognition) {
        this.voiceSearchCancelled = true;
        this.recognition.stop();
        this.isListening = false;
        return;
      }

      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SpeechRecognition) {
        this.$toasted.show("La busqueda por voz no esta disponible en este navegador");
        return;
      }

      this.voiceQuery = "";
      this.voiceSearchCancelled = false;
      this.voiceSearchFailed = false;
      this.recognition = new SpeechRecognition();
      this.recognition.lang = "es-PE";
      this.recognition.continuous = false;
      this.recognition.interimResults = false;
      this.recognition.maxAlternatives = 1;

      this.recognition.onstart = () => {
        this.isListening = true;
      };
      this.recognition.onresult = event => {
        const resultado = event.results[event.results.length - 1];
        const consulta = resultado && resultado[0]
          ? String(resultado[0].transcript || "").trim()
          : "";
        this.voiceQuery = consulta;
        if (consulta) this.buscarProductoPorVoz(consulta);
      };
      this.recognition.onerror = event => {
        this.voiceSearchFailed = true;
        if (event.error !== "aborted") {
          this.$toasted.show("No se pudo reconocer la voz. Intente nuevamente");
        }
      };
      this.recognition.onend = () => {
        this.isListening = false;
        if (!this.voiceQuery && !this.voiceSearchFailed && !this.voiceSearchCancelled) {
          this.$toasted.show("No se detectaron palabras");
        }
      };

      try {
        this.recognition.start();
      } catch (error) {
        this.isListening = false;
        this.$toasted.show("No se pudo iniciar la busqueda por voz");
      }
    },
    buscarProductoPorVoz(consulta) {
      const autocomplete = this.$refs.autocompleteProducto;
      if (!autocomplete) return;

      autocomplete.type = consulta;
      autocomplete.showList = true;
      this.$nextTick(() => {
        if (autocomplete.$refs && autocomplete.$refs.input) {
          autocomplete.$refs.input.focus();
        }
        if (typeof autocomplete.getData === "function") {
          autocomplete.getData(consulta);
        }
      });
    },
    abrirEscaner() {
      if (!this.idAlmacen) {
        this.$toasted.show("Seleccione primero el almacen a confrontar");
        return;
      }
      this.mostrarEscaner = true;
    },
    onBarcodeScanned(result) {
      if (!this.mostrarEscaner || !result || !result.codeResult) return;
      const codigo = String(result.codeResult.code || "").trim();
      if (!codigo) return;
      const audio = new Audio(sound);
      audio.volume = 0.85;
      audio.play().catch(() => {});
      if (window.navigator && typeof window.navigator.vibrate === "function") {
        window.navigator.vibrate(70);
      }
      this.loadCode = codigo;
      this.mostrarEscaner = false;
      this.loadBarcode();
    },
    actualizarCantidadFisica(index) {
      const producto = this.productos[index];
      const cantidad = Math.max(0, Number(producto.cantidad) || 0);
      this.$set(producto, "cantidad", cantidad);
      this.changeCant(cantidad, producto, index);
      this.$emit("onInputCantidad", cantidad);
    },
    ajustarCantidadFisica(index, diferencia) {
      const producto = this.productos[index];
      const cantidad = Math.max(0, (Number(producto.cantidad) || 0) + diferencia);
      this.$set(producto, "cantidad", cantidad);
      this.changeCant(cantidad, producto, index);
      this.$emit("onInputCantidad", cantidad);
    },
    abrirImagen(producto, url) {
      this.productoNombre = producto;
      this.productoUrl = url;
      this.$refs["modalImagen"].show();
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    changeCant(val, prod, index) {
      // this.$nextTick(() => {
      //   if(prod.PorcentajeDesc>0){
      //     this.productos[index].descuento = Math.round(this.getSubtotal(index) * prod.PorcentajeDesc/100*10)/10;
      //   }
      //   this.actualizarProd(prod)
      //   this.getSubtotal(index)
      // })
    },

    cambiarPrecio(index, prod) {
      var utilidad =
        this.productos[index].PrecioCosto *
        (this.productos[index].PorcentajeUtilidad / 100);
      this.productos[index].precio = Math.ceil(
        this.productos[index].PrecioCosto + utilidad
      );
    },

    addManoDeObra(index, e) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");
      if (pi < 0) {
        this.$toasted.error("Primero seleccione el producto MANODEOBRA");

        this.productos[index].EsManoDeObra = 0;
        return;
      }

      if (this.productos[index].EsManoDeObra) {
        e.target.parentNode.parentNode.style.backgroundColor = "#e7c4ff";
        this.productos[pi].precio =
          parseFloat(this.productos[pi].precio) -
          parseFloat(this.productos[index].precio) *
            parseFloat(this.productos[index].cantidad);
        this.productos[index].disabled = true;
      } else {
        e.target.parentNode.parentNode.removeAttribute("style");
        this.productos[pi].precio = 0;
        this.productos[pi].precio =
          parseFloat(this.productos[pi].precio) +
          parseFloat(this.productos[index].precio) *
            parseFloat(this.productos[index].cantidad);
        this.productos[index].disabled = false;
      }
    },

    updateManoDeObraNeta(index) {
      let pManoObra = this.productos.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.precioBruta : 0;

      this.productos.filter(p => p.EsManoDeObra).map(p => {
        precio -= parseFloat(p.precio) * parseFloat(p.cantidad);
      });

      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");

      if (pi >= 0 && index !== pi) {
        this.productos[pi].precio = precio;
      }
    },

    asignarDisabledProf(index, e) {
      if (e.target.checked === true) {
        this.productos[index].disabledProf = 1;
        return;
      }
      this.productos[index].disabledProf = 0;
    },

    asignarVuelto() {
      this.vuelto = this.pagocon - this.sumatoriaTotal;
    },
    // loadBarcode (e) {
    //   e.preventDefault()
    //   let idProducto = this.loadCode;

    //   if (idProducto) {
    //     this.add([idProducto, (obj) => {
    //       if (obj) {
    //         this.$emit('onAddProducto')
    //         this.$toasted.show('Producto añadido correctamente')
    //       } else {
    //         this.$toasted.show('No se encontró ningun producto')
    //       }
    //       this.loadCode = ''
    //     }, { setPrecio: this.setPrecio,  setSuma: true }, true])
    //   }
    // },

    async loadBarcode(e) {
      if (e && typeof e.preventDefault === "function") e.preventDefault();
      let idProducto = this.loadCode;

      if (idProducto) {
        let params = { codigoBarra: idProducto };
        await this.axios
          .get(this.API + "productos/id/" + idProducto, { params: params })
          .then(response => {
            if (response.data.IdProducto) {
              this.setProducto(response.data, true);
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
    setObsequio(index) {
      this.productos[index].precio = 0;
      this.productos[index].nuevoPrecioContado = 0;
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    editarProducto(Pro) {
      this.productoSelect = Pro;
      this.$refs.productoFormEdit.show = true;
      this.$refs.productoFormEdit.nuevo = false;
    },
    agregarGuardar(id, pxp, obj) {
      this.add([id, false, false, false, false, true, this.isVentas]);
      setTimeout(() => {
        this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
      }, 200);

      if (this.isVentas) {
        let fPP = pxp.filter(p => p.cantidad === 0 || p.cantidad === "0");
        if (fPP.length != 4) {
          obj.ProductoMedicion = obj.medicion.ProductoMedicion;
          obj.IdProductoMedicion = obj.medicion.IdProductoMedicion;
          obj.IdProductoMarca = obj.marca.IdProductoMarca;
          obj.ProductoMarca = obj.marca.ProductoMarca;
          obj.IdProductoCategoria = obj.categoria.IdProductoCategoria;
          obj.ProductoCategoria = obj.categoria.ProductoCategoria;
          this.productoSelect = obj;
          this.productoSelect.PreciosPorProducto = JSON.stringify(pxp);
          this.showPresentacion(this.productoSelect);
        }
      }
    },
    setProducto(obj, incrementarCantidad) {
      const existente = this.productos.findIndex(producto => Number(producto.IdProducto) === Number(obj.IdProducto));
      if (existente >= 0) {
        if (incrementarCantidad) this.ajustarCantidadFisica(existente, 1);
        this.$toasted.show(incrementarCantidad ? "Cantidad fisica incrementada" : "El producto ya esta en la lista");
        return;
      }
      obj.cantidad = incrementarCantidad ? 1 : 0;
      this.$store.state.cart.productos.unshift(obj);
      this.$nextTick(() => this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 }));
    },
    setProductoUni(obj, precio) {
      this.objProd.cantxPrecio = 1;
      this.$refs.autocompleteProducto.setValue("");
      //obj.FLETE = 0
      obj.precio = 0;
      obj.cantidad = 0;
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj = this.onSetProducto(obj, false, precio);
      this.add([
        obj,
        () => {
          this.$emit("onAddProducto", obj);
          this.$toasted.success("Producto añadido correctamente");
        },
        {
          setPrecioCosto: this.tipo == "mov" ? true : false,
          setOrden: this.ordenNormal
        },
        false,
        this.cliente.EsVIP
      ]);
      // Vue.set(this.productos, obj.IdProducto, obj)
      //this.$refs['modalCant'].hide()
      obj.cantidadPres = obj.cantidad;
      this.actualizarProd(obj);
    },
    setProductoCant(prod) {
      this.objProd.cantxPrecio = 1;
      this.cant = prod.cantidad;
      let obj = this.objProd;
      this.$refs.autocompleteProducto.setValue("");
      //obj.FLETE = 0
      // this.objProd.precio = 0
      // this.objProd.cantidad = 0
      obj.precio = 0;
      obj.cantidad = 0;
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj = this.onSetProducto(obj, prod.cantidad, prod.precio);
      this.add([
        obj,
        () => {
          this.$emit("onAddProducto", obj);
          this.$toasted.success("Producto añadido correctamente");
        },
        false,
        false,
        this.cliente.EsVIP
      ]);
      //this.$refs['modalCant'].hide()
      obj.cantidadPres = this.cant;
      this.actualizarProd(obj);
    },
    showPresentacion(obj) {
      if (this.isVentas) {
        this.preciosProd = [];
        this.preciosProd.push({
          nombre: "P. Pub.",
          cantidad: 1,
          precio: obj.PrecioContado
        });
        if (obj.precioConvenio) {
          this.preciosProd.push({
            nombre: "P. Dis.",
            cantidad: 1,
            precio: obj.precioConvenio
          });
        }
        if (obj.PrecioEspecial) {
          this.preciosProd.push({
            nombre: "P. Esp.",
            cantidad: 1,
            precio: obj.PrecioEspecial
          });
        }
        this.objProd = obj;
        this.$refs["modalCant"].show();
        return;
      }
    },
    renderChild(data) {
      if (this.tipo === "mov") {
        return `
          <div class="confront-search-result">
            <small>${data.CodigoBarra || "SIN CODIGO"}</small>
            <b>${data.Producto || "Producto sin nombre"}</b>
          </div>
        `;
      }
      if (this.idAlmacen) {
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
          <table id="table-render" style="width: 830px; text-align: center  ">
            <thead>
              <tr style>
                <th class="table-info" style="width: 60px">Stock</th>
                <th style="width: 200px">Producto</th>
                <th style="width: 100px">Categoría</th>
                <th style="width: 100px">Marca</th>
                <th style="width: 120px">Modelo</th>
                <th class="table-primary" style="width: 65px">Precio</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="${styleStock}"><b>${
          data.EsPadre == 1
            ? '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />'
            : data.stock
              ? Number.isInteger(data.stock)
                ? data.stock
                : data.stock.toFixed(2)
              : data.stock
        }</b></td>
                <td><b>${data.Producto} - ${data.CodigoBarra}</b></td>
                <td>${data.ProductoCategoria}</td>
                <td>${data.ProductoMarca}</td>
                <td>${data.ProductoModelo || ""}</td>
                <td class="table-primary"><b>${data.PrecioContado}</b></td>
              </tr>
            </tbody>
          </table>   
        </div>
        `;
      }
      return `
        <b>${data.CodigoBarra}</b> | ${data.Producto} |  | ${
        data.ProductoCategoria
      } | ${data.ProductoMarca} | ${data.ProductoModelo || ""}
      `;
    },
    // setCantidad (index, val) {
    //   this.$emit('onInputCantidad', val)
    // },
    setNuevoPrecioContado(index) {
      // Añadir IGV si check IGV
      let precio = parseFloat(this.productos[index].precio);
      if (this.productos[index].TieneIgv) {
        precio += precio * 0.18;
      }
      let precioFinal = 0;
      //if(this.isVentas){
      //precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      // let precioFinal =
      // }else{
      precioFinal =
        (precio * this.productos[index].PorcentajeUtilidad) / 100 + precio;
      // precio = (precio / (1-this.productos[index].PorcentajeUtilidad/100))
      //}

      //precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      //precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      // let precio = parseFloat(this.productos[index].precio) + (this.productos[index].precio * (this.productos[index].PorcentajeUtilidad/100))

      let normalPrecio = this.$options.filters.redondear(precioFinal);
      //let nuevoPrecio = this.$options.filters.redondeoMayor(precio)
      let nuevoPrecio = this.$options.filters.redondear(precioFinal, 1);

      if (this.tipoCambio > 0) {
        normalPrecio *= this.tipoCambio;
        nuevoPrecio *= this.tipoCambio;
      }

      Vue.set(
        this.productos[index],
        "normalPrecioContado",
        parseFloat(normalPrecio).toFixed(2)
      );
      Vue.set(
        this.productos[index],
        "nuevoPrecioContado",
        Math.ceil(nuevoPrecio)
      );
      this.getSubtotal(index);

      // this.productos[index].nuevoPrecioContado = nuevoPrecio
      // return nuevoPrecio
      this.updateManoDeObraNeta(index);
    },
    actualizarDescu(index) {
      let obj = this.productos[index];
      this.actualizarProd(obj);
    },
    getSubtotal(index) {
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
    },

    formatFecha(fecha) {
      if (fecha) {
        return this.$moment(fecha, "YYYY-MM-DD hh:mm:ss").format("YYYY-MM-DD");
      } else {
        return "";
      }
    }
  },
  components: {
    ProductoForm,
    BarcodeCameraScanner
  }
};
</script>

<style scoped>
.contentCard {
  cursor: pointer;
}
.disabledMouse {
  background: #cecece;
  cursor: inherit;
}
.form-check-input {
  margin-left: -0.2rem !important;
}
.oferta {
  border: 4px solid #dc3545;
}
.ofertanom {
  color: #dc3545;
  border-right: 2px solid #dc3545;
}
.confront-mobile-list {
  display: none;
}
.confront-product-search {
  display: flex;
  flex: 1 1 auto;
  min-width: 0;
  align-items: stretch;
}
.confront-product-search > *:first-child {
  flex: 1 1 auto;
  min-width: 0;
}
.confront-voice-button {
  display: inline-flex;
  flex: 0 0 auto;
  min-width: 74px;
  padding: 0 14px;
  border: 1px solid #075247;
  border-radius: 0 11px 11px 0;
  align-items: center;
  justify-content: center;
  gap: 7px;
  color: #fff;
  background: #075247;
  font-weight: 800;
  cursor: pointer;
}
.confront-voice-button.is-listening {
  border-color: #d82737;
  background: #d82737;
  box-shadow: 0 0 0 4px rgba(216, 39, 55, .15);
}
.confront-qty-control {
  display: inline-flex;
  min-width: 142px;
  align-items: stretch;
  overflow: hidden;
  border: 1px solid #9fcfc5;
  border-radius: 12px;
  background: #fff;
}
.confront-qty-control button {
  width: 40px;
  border: 0;
  color: #fff;
  background: #075247;
  font-size: 22px;
  font-weight: 900;
}
.confront-qty-control input {
  width: 60px !important;
  min-width: 0;
  border: 0 !important;
  border-radius: 0 !important;
  text-align: center;
  font-weight: 900;
  box-shadow: none !important;
}
.confront-camera-button {
  background: #087363 !important;
  border-color: #087363 !important;
}
.confront-scanner-stage {
  position: relative;
  height: 420px;
  overflow: hidden;
  border-radius: 18px;
  background: #020b16;
}
.confront-scanner-guide {
  position: absolute;
  inset: 25% 8%;
  pointer-events: none;
  border: 3px solid #e1b859;
  border-radius: 18px;
}
.confront-scanner-guide span {
  position: absolute;
  top: 50%;
  right: 4%;
  left: 4%;
  height: 2px;
  background: #ff315c;
  box-shadow: 0 0 9px #ff315c;
}
.confront-scanner-help {
  margin: 13px 4px 0;
  color: #075247;
  text-align: center;
  font-weight: 800;
}
@media (max-width: 767px) {
  .confront-searchbar {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) 92px;
    gap: 7px 0;
    align-items: stretch;
  }
  .confront-searchbar > .input-group-prepend,
  .confront-searchbar > div:first-child {
    grid-column: 1 / -1;
    width: 100%;
    min-width: 0;
  }
  .confront-product-search {
    min-height: 54px;
  }
  .confront-voice-button {
    min-width: 54px;
    width: 54px;
    padding: 0;
    border-radius: 0 12px 12px 0;
    font-size: 18px;
  }
  .confront-voice-button span {
    display: none;
  }
  .confront-searchbar > input {
    grid-column: 1;
    width: 100%;
    min-width: 0;
    min-height: 46px;
    border-radius: 11px 0 0 11px !important;
  }
  .confront-searchbar > .confront-code-actions {
    display: grid;
    grid-column: 2;
    grid-template-columns: repeat(2, 46px);
    width: 92px;
    min-width: 92px;
    margin-left: 0;
    align-items: stretch;
  }
  .confront-code-actions .btn {
    display: inline-flex;
    width: 46px;
    min-width: 46px;
    min-height: 46px;
    padding: 0;
    align-items: center;
    justify-content: center;
    border-radius: 0 !important;
    font-size: 1rem;
  }
  .confront-code-actions .confront-code-search-button {
    border-radius: 0 11px 11px 0 !important;
  }
  .confront-camera-button span {
    display: none;
  }
  .confront-desktop-table {
    display: none !important;
  }
  .confront-mobile-list {
    display: grid;
    gap: 12px;
    margin-top: 10px;
  }
  .confront-mobile-card {
    padding: 11px;
    overflow: hidden;
    border: 1px solid #9fcfc5;
    border-radius: 18px;
    background: #fff;
    box-shadow: 0 10px 24px rgba(7, 82, 71, .11);
  }
  .confront-mobile-card__quantity {
    display: flex;
    padding: 12px 14px;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    color: #fff;
    background: linear-gradient(135deg, #043b35, #087363);
  }
  .confront-mobile-card__quantity > span {
    font-size: 11px;
    font-weight: 900;
    letter-spacing: .7px;
  }
  .confront-qty-control--mobile {
    min-width: 112px;
    height: 44px;
    border-color: rgba(255,255,255,.5);
  }
  .confront-qty-control--mobile button {
    width: 34px;
    color: #075247;
    background: #d8fff5;
    font-size: 14px;
  }
  .confront-qty-control--mobile input {
    width: 44px !important;
    padding: 0 3px;
  }
  .confront-mobile-card__head {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 112px 40px;
    padding: 0;
    align-items: center;
    gap: 8px;
  }
  .confront-mobile-card__product {
    min-width: 0;
  }
  .confront-mobile-card__head small,
  .confront-mobile-card__head strong {
    display: block;
  }
  .confront-mobile-card__head small {
    color: #087363;
    font-weight: 900;
  }
  .confront-mobile-card__head strong {
    margin-top: 3px;
    color: #18334a;
    font-size: 14px;
    line-height: 1.2;
    overflow-wrap: anywhere;
  }
  .confront-mobile-card__remove {
    width: 40px;
    height: 44px;
    border: 0;
    border-radius: 12px;
    color: #fff;
    background: #dc2535;
  }
  .confront-scanner-stage {
    height: 340px;
  }
}
</style>
<style>
.popover {
  max-width: 420px !important;
}
.nomCart {
  display: flex;
  justify-content: center;
  align-content: center;
  align-items: center;
  border-right: 2px solid black;
}
#modalImagen .modal-dialog {
  max-width: 75% !important;
}
.imagenGrande {
  width: 100%;
}
.obsequio {
  display: flex;
  justify-content: center;
  justify-items: center;
}
.confront-product-search .autocomplete-wrapper input {
  min-height: 46px;
  border-radius: 11px 0 0 11px !important;
}
.confront-search-result {
  display: grid;
  gap: 2px;
  padding: 7px 9px;
  color: #17364a;
}
.confront-search-result small {
  color: #087363;
  font-weight: 900;
}
.confront-search-result b {
  white-space: normal;
  overflow-wrap: anywhere;
}
@media (max-width: 767px) {
  .confront-product-search .autocomplete-wrapper input {
    min-height: 54px;
  }
}
</style>
