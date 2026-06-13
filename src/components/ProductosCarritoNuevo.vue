<template>
  <b-row style="background: #ffffff;">
    <b-col md="12">
      <b-row>
        <b-col cols="12" lg="6">
          <b-form-group>
            <b-input-group>
              <autocomplete
                ref="autocompleteProducto"
                :url="APIProductos"
                :debounce="500"
                :customParams="{ 'sortBy':isVentas?'stock':'IdProducto', 'sortDesc': 1 ,limit: '25' }"
                :onShouldRenderChild="renderChild"
                anchor="Producto"
                label="writer"
                placeholder="Agregar Producto"
                :on-select="setProducto"
                :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                id="txtAddProduct"
                @keydown.native.prevent.tab="irtxtBarcode"
                :classes="{input: 'form-control txtProducto'}"
                
                >
              </autocomplete>
              <b-button @click="startRecognition" size="lg" :class="microActivo?'alerta':''" style="background: #1A237E; color: #ffffff;"><i class="fas fa-microphone"></i> </b-button>
              </b-input-group>
              </b-form-group>
              <span class="ocultarMovil">Busqueda avanzada (Producto, Modelo, Categoria)</span>
        </b-col>
        <b-col cols="12" lg="6">
          <b-form-group>
        <b-input-group>
          
          <input 
                  id="txtBarcode"
                  v-model="loadCode"
                  @keydown.prevent.tab="irtxtAddProduct"
                  @keydown.enter.prevent="loadBarcode"
                  type="number"
                  class="form-control"
                  placeholder="Codigo de Barra" />
          <b-input-group-append>
            <b-button  size="lg"  @click="abrirModal()"><i class="fa fa-hand-pointer" aria-hidden="true"></i></b-button>
            
            <b-button  size="lg"  @click="startRecognitionCodeBar" :class="microActivoBarCode?'alerta':''"  style="background: #1A237E; color: #ffffff;"><i class="fas fa-microphone"></i> </b-button>
            <!--<proveedores-tabla></proveedores-tabla>-->
            <b-button variant="success" @click="abrirCodigoBarra"><i class="fas fa-barcode"></i></b-button>
            <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
            <producto-form :showButton="showButton" :cbGuardar="(id,pxp,obj) =>{agregarGuardar(id,pxp,obj)}" ref="productoFormEdit" :producto-update="productoSelect"></producto-form>
          </b-input-group-append>
        </b-input-group>
      </b-form-group>
        </b-col>
      </b-row>
      <b-modal v-model="touchModal" size="sm" hide-footer class="ingDesc" title="Codigo Barra" hide-header-close>
        <input readonly type="text" inputmode="none" @keypress="(e)=>{ verificar(e)}" v-model="cantidadInput" style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control"/>
        <table class="calc__numbers calc__numbers2">
              <tbody>
                  <tr>
                      <td class="botones" @click="addNumber('7');">7</td>
                      <td class="botones" @click="addNumber('8');">8</td>
                      <td class="botones" @click="addNumber('9');">9</td>
                  </tr>
                  <tr>
                      <td class="botones" @click="addNumber('4');">4</td>
                      <td class="botones" @click="addNumber('5');">5</td>
                      <td class="botones" @click="addNumber('6');">6</td>
                  </tr>
                  <tr>
                      <td class="botones" @click="addNumber('1');">1</td>
                      <td class="botones" @click="addNumber('2');">2</td>
                      <td class="botones" @click="addNumber('3');">3</td>
                  </tr>
                  <tr>
                      <td class="botones" @click="addNumber(0);">0</td>
                      <!-- <td class="botones" @click="addPoint('.');">.</td> -->
                      <td class="botones" @click="deleteNumber();"><i class="fas fa-backspace"></i></td>
                  </tr>
                  <tr>
                    <td colspan="3" style="text-align: center;">
                        <b-button variant="success" @click="buscarCodigoBarra()">Buscar</b-button>
                        <b-button variant="danger" @click="touchModal=!touchModal">Cerrar</b-button>
                    </td>
                  </tr>
              </tbody>
            </table>
      </b-modal>

      <b-modal size="lg" ref="modalCant" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration: underline;">{{objProd.Producto}}</h3>
          <h5>Presentaciones</h5>
        </div>
        <hr>
        <b-row>
          <b-col cols="6">
            <b-card
              align="center"
              class="mb-3 contentCard"
              @click="setProductoUni(objProd)"
            >
              <b-row>
                <b-col md="6" class="nomCart">
                  <h5 class="text-uppercase">UNIDAD</h5>
                </b-col>
                <b-col md="6" class="text-left">
                  <b>Cant: </b>1
                  <br>
                  <b>Precio: </b>S/ {{ precioCont }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>

          <b-col cols="6" v-for="d in preciosProd" :key="d.id">
            <b-card
              v-if="d.cantidad != 0"
              align="center"
              class="mb-3 contentCard"
              v-bind:class="[d.nombre=='OFERTA' ? 'oferta' : '']"
              @click="() => {
                if ( parseFloat(d.cantidad) ) {
                  setProductoCant(d)
                }
              }"
            >
              <b-row>
                <b-col md="6" class="nomCart" v-bind:class="[d.nombre=='OFERTA' ? 'ofertanom' : '']">
                  <h5 class="text-uppercase">{{ d.nombre }}</h5>
                </b-col>
                <b-col md="6" class="text-left">
                  <b>Cant: </b>{{ d.cantidad }}
                  <br>
                  <b>Precio: </b>S/ {{ d.precio }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>
        </b-row>
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
      <div class="divTBl">

     
      <b-table 
            small 
            responsive 
            id="tblProductos"
            striped hover 
            :items="productos" 
            :fields="fields" 
          ref="tablaCarrito">
        <template slot="eventPres" slot-scope="data">
          <b-button size="sm" :disabled="bloquearInput?true:false" variant="info" @click="showPresentacion(productos[data.index])"><i class="fas fa-list"></i></b-button>
          <br>
          <b-button class="OcultarTable" @click="abrirImagen(productos[data.index].Producto,productos[data.index].Imagen == null || productos[data.index].Imagen==''?'static/no-photo.png': '/resources/images/productos/' + productos[data.index].Imagen)" style="margin-top:2px;" :id="'popover-imagen' + productos[data.index].IdProducto" variant="success" size="sm"><i class="fas fa-image"></i></b-button>	
            <b-popover  placement="right" :target="'popover-imagen' + productos[data.index].IdProducto" :title="productos[data.index].Producto" triggers="hover">
              <div style="text-align:center">
                <b-img style="border-radius: 10px; width:200px" fluid :src="productos[data.index].Imagen == null || productos[data.index].Imagen==''?'static/no-photo.png': '/resources/images/productos/' + productos[data.index].Imagen" alt="" @error="setAltImg" ></b-img>	
              </div>
            </b-popover>
          <b-button class="OcultarPC" @click="abrirImagen(productos[data.index].Producto,productos[data.index].Imagen == null || productos[data.index].Imagen==''?'static/no-photo.png': '/resources/images/productos/' + productos[data.index].Imagen)" style="margin-top:2px;" variant="success" size="sm"><i class="fas fa-image"></i></b-button>	
            
        </template>

        <template slot="cantxPrecio" slot-scope="data" v-if="productos[data.index].estadoPxP == false">
          <input
            :id="`txtCantidadxPrecio${data.index}`"
            v-model="productos[data.index].cantxPrecio"
            type="number"
            style="width: 80px"
            step="0.001"
            min="0"
            :disabled="bloquearInput?true:false"
            @input="(val) => changeCant(val.target.value, productos[data.index])"
            placeholder="Cantidad"
            @keydown.prevent.enter="irtxtAddProduct"
            class="form-control" />
          <span>x{{productos[data.index].cantidadPres}}</span>
        </template>

        <template slot="cantidad" slot-scope="data">
          <input
            :id="`txtCantidad${data.index}`"
            v-model="productos[data.index].cantidad"
            :disabled="productos[data.index].estadoPxP == false || bloquearInput?true:false || esDelivery"
            type="number"
            style="width: 80px"
            class="form-control"
            step="0.001"
            @keydown.prevent.tab="irtxtBarcode"
            min="0"
            @keydown.prevent.enter="irtxtBarcode"
            @input="(val) => {
              $emit('onInputCantidad', val.target.value)
              if(this.ventas){
                if(!productos[data.index].PreciosPorProducto) {
                productos[data.index].precio = (productos[data.index].StockPorMayor && val.target.value >= productos[data.index].StockPorMayor) ? productos[data.index].PrecioPorMayor : (tipo == 'mov' ? productos[data.index].PrecioCosto : productos[data.index].PrecioContado)
                return
                }

                let reg = JSON.parse(productos[data.index].PreciosPorProducto).find( e => e.cantidad === productos[data.index].cantidadPres )
                if(reg && reg.cantidad){
                  productos[data.index].precio = reg.precio
                  return
                }
                
                productos[data.index].precio = productos[data.index].PrecioContado 
              }else{
                cambiarPrecioVenta(data.index)
              }

            }"
            placeholder="Cantidad"/>
            <!-- <p><span class="mx-2">Prof.</span>  
          <input 
            class="form-check-input" 
            type="checkbox" 
            :disabled="bloquearInput?true:false"
            @change="(e) => {
              $emit('onCheckProf', e)
              asignarDisabledProf(data.index, e)
            }"
          />
        </p> -->
        </template>
        <template slot="precio" slot-scope="data">
          <input
            v-model="productos[data.index].precio"
            :disabled="bloquearPrecio(productos[data.index])"
            type="number"
            style="width: 90px"
            min="0"
            step="0.00001"
            class="form-control"
            placeholder="Precio"
            @input="() => { setNuevoPrecioContado(data.index);}"
            />
        </template>
        <template slot="subtotal" slot-scope="data">
          <div v-if="isVentas">
            {{ getSubtotal(data.index) }}
          </div>
          <div v-else>
            <input
            type="number"
            style="width: 105px"
            min="0"
            step="0.00001"
            placeholder="Subtotal"
            class="form-control"
            v-model="productos[data.index].subt"
            @input="val => setSubtotal(data.index, val.target.value, productos[data.index])"/>
            <b>{{ getSubtotal(data.index) }}</b>
          </div>
        </template>
        <template slot="igv" slot-scope="data">
          <b-form-checkbox
            v-model="productos[data.index].TieneIgv"
            :value="1"
            :disabled="bloquearInput?true:false"
            @input="setNuevoPrecioContado(data.index)"
            :unchecked-value="0">
            <div>{{ getIgv(data.index) }}</div>
          </b-form-checkbox>
        </template>
        <template slot="flete" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].FLETE"
            type="number"
            min="0"
            placeholder="Flete"
            @input="cambiarPrecioVenta(data.index)"
            ></b-form-input>
           <b v-if="productos[data.index].FLETE && productos[data.index].cantidad!=0 "> 
            {{  
              parseFloat(productos[data.index].FLETE / productos[data.index].cantidad).toFixed(2)
            }}
          </b>
        </template>
        
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
            style="width: 98px"
            type="number" 
            step="0.01"
            @input="cambiarPrecio(data.index, productos[data.index])" 
            @mouseup="cambiarPrecio(data.index, productos[data.index])" 
            />
            
        </template>

        <template slot="descuento" slot-scope="data">
          <input
            :disabled="bloquearInput?true:false  || esDelivery"
            v-model="productos[data.index].descuento"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            value="0"
            style="max-width: 100px;"
            @input="actualizarDescu(data.index)"/>
            <b-form-text>
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
          
          <b-textarea 
            :disabled="bloquearInput?true:false"
            class="form-control"
            placeholder="Descripción"
            cols="9" rows="3"
            style="width: 150px;field-sizing: content;"
            v-model="productos[data.index].Descripcion"></b-textarea>
          <!--<b-form-input
            v-model="productos[data.index].Descripcion"
            type="text"
            placeholder="Descripción"></b-form-input>-->
        </template>        

        <template slot="total" slot-scope="data">
          {{ getTotal(data.index) }}
          <br>
          
        </template>

        <template slot="acciones" slot-scope="data">
          <b-button :disabled="bloquearInput?true:false" variant="danger" size="sm" @click="$emit('eliminarProducto',data.index)"><i class="fas fa-times fa-lg"></i></b-button>
          <b-button  style="margin-top:5px" :disabled="bloquearInput?true:false" v-if="(idPerfil == 1 && index==0) ? true : false"  variant="primary" size="sm" @click="editarProducto(productos[data.index])"><i class="fas fa-pencil-alt fa-sm"></i></b-button>
        <br>
            <small>G: {{ (data.item.precio - data.item.PrecioCosto) * data.item.cantidad | redondear }}</small></template>

        <template slot="show_details" slot-scope="row">
           <b-button :disabled="bloquearInput?true:false" size="sm" variant="info" @click.stop="row.toggleDetails">></b-button>
        </template>
        <!-- <template slot="row-details" slot-scope="row">

          
        </template> -->

         <template slot="Series" slot-scope="data">
          <b-select 
              :options="data.item.Series" 
              v-model="data.item.IdProductoSerie"
              text-field="Serie"
              value-field="IdProductoSerie"
          />
          </template>

        <template slot="stock" slot-scope="data">
          <div v-if="data.item.CodigoBarra=='COSTODELIVERY'">
              <img src="static/moto-delivery.png" style="width: 24px" />
          </div>
          <div v-else>
            <img v-if="data.item.EsPadre" src="static/box.png" style="width: 24px" />
            <div v-if="!data.item.EsPadre">{{ data.item.stock }}</div>
          </div>
        </template>
      </b-table>
       </div>
    </b-col>
    <b-col md="3">
      <slot name="operaciones"></slot>
    </b-col>
    <b-col md="4">
      <slot name="metodopago"></slot>
    </b-col>
    <b-col md="2">
      <slot name="adicional"></slot>
    </b-col>
    <b-col md="3" v-if="!isVentas">
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
              <tr v-if="productos.some(v=>v.FLETE >0)">
                <td><b>Total Flete</b></td>
                <td class="table-danger">{{ productos.reduce((sum,producto)=>{
                  return sum + parseFloat(producto.FLETE? producto.FLETE: 0)
                },0)  }}</td>
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
                <td  colspan="2">
                  <label>pagó con:</label>
                  <b-form-input
                    type="text"
                    :disabled="bloquearInput?true:false"
                    placeholder="Pagó con"
                    v-model="pagocon"
                    @input="asignarVuelto"></b-form-input><br>
                  <label>Vuelto:</label>
                  <b>S/  {{ vuelto | redondear }}</b>
                </td>
              </tr>
            </tbody>
          </table>
    </b-col>
    <br><br>
       <b-modal  size="lg" v-model="modalImagen" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration: underline;">{{productoNombre}}</h3>
        </div>
        <b-row style="display:flex; justify-content:center; align-items:center">
          <b-img :src="productoUrl" class="imagenGrande"></b-img>
        </b-row>
      </b-modal>

  </b-row>
</template>

<script>
import { mapState, mapActions } from "vuex";
import ProductoForm from "./ProductoForm";
import config from "@/store/config.js";
import sound from "../../static/beep.mp3";
import Vue from "vue";
import VueQuagga from "vue-quaggajs";
import debounce from 'lodash';

Vue.use(VueQuagga);

export default {
  mounted() {
    // document.getElementById("txtBarcode").focus();
    this.$nextTick(() => {
      setTimeout(() => {
        this.recalcularManoObra();
        let barCode = document.getElementById("txtBarcode");
        barCode.focus();
      }, 1000);
    });
    // this.alterarBuscadorProductos()
  },

  data() {
    return {
      modalImagen: false,
      productoNombre: "",
      productoUrl: "",
      cantidadInput: "",
      touchModal: false,
      microActivo: false,
      microActivoBarCode: false,
      vuelto: 0,
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
      cliente: {},
      preciosProd: [],
      objProd: {},
      cant: 0,
      precioCont: 0,
      medicionPro: "UNIDAD",
      productoSelect: {},
      idPerfil: sessionStorage.getItem("IdPerfil"),
      modalAbiertCode: false,
      queryCodeBar: "",
      query: ""
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
    gravadaflag() {
      console.log("gravadaflag", this.gravadaflag);
    },
    igvflag() {
      this.productos.forEach((element, index) => {
        Vue.set(this.productos[index], "TieneIgv", this.igvflag);
        // this.productos[index] = element;
        this.setNuevoPrecioContado(index);
        // console.log("igvflag", this.igvflag);
      });
    },
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
    index: { typeof: Number, default: 0 },
    productos: Array,
    gravadaflag: Number,
    igvflag: Number,
    isVentas: Boolean,
    tipo: String,
    setPrecio: Boolean,
    tipoCambio: Number,
    igvTotal: Boolean,
    idAlmacen: Number,
    bloquearInput: Boolean,
    esDelivery: Boolean,
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
          { key: "ProductoModelo", label: "Modelo" },
          // { key: 'ProductoPresentacion', label: 'Present.' },
          // { key: 'ProductoMedicion', label: 'Medición' },
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
      // productos: state => state.cart.productos,
      filtrados: state => state.cart.productosFiltrados
    }),
    APIProductos() {
      if (this.idAlmacen) {
        return this.API + "productos/stock/" + this.idAlmacen;
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
      if (this.igvTotal || this.gravadaflag) {
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
    abrirImagen(producto, url) {
      this.productoNombre = producto;
      this.productoUrl = url;
      this.modalImagen = true;
    },
    bloquearPrecio(data) {
      if (data.disabled) {
        return true;
      }
      if (this.bloquearInput) {
        return true;
      }
      if (this.esDelivery) {
        return true;
      }

      if (this.isVentas && data.EsPadre) {
        return true;
      }
      return false;
    },
    abrirModal() {
      this.touchModal = true;
    },
    cambiarPrecioVenta(index) {
      let producto = { ...this.productos[index] };
      let flete = producto.FLETE
        ? parseFloat(
            producto.FLETE / (producto.cantidad ? producto.cantidad : 1)
          )
        : 0;
      let precio = parseFloat(producto.precio);

      if (producto.TieneIgv) {
        precio += precio * 0.18;
      }
      let precioFinal =
        (producto.precio * producto.PorcentajeUtilidad) / 100 + precio + flete;

      precioFinal = precioFinal * this.tipoCambio;
      Vue.set(
        this.productos[index],
        "nuevoPrecioContado",
        parseFloat(precioFinal).toFixed(2)
      );
      Vue.set(
        this.productos[index],
        "normalPrecioContado",
        parseFloat(precioFinal).toFixed(2)
      );
    },
    startRecognition() {
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }
      this.microActivo = true;
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
        this.microActivo = false;
        console.error("Speech Recognition Error:", event);
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = async () => {
        this.microActivo = false;
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
    startRecognitionCodeBar() {
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }
      this.microActivoBarCode = true;
      this.recognition = new webkitSpeechRecognition();
      this.recognition.lang = "es-ES";
      this.recognition.continuous = false;
      this.recognition.interimResults = false;

      this.recognition.onstart = () => {
        console.log("Speech recognition service has started");
      };

      this.recognition.onresult = event => {
        this.microActivoBarCode = false;
        if (event.results.length > 0) {
          this.queryCodeBar = event.results[0][0].transcript;
          this.searchCodeBar();
        }
      };

      this.recognition.onerror = event => {
        this.microActivoBarCode = false;
        console.error("Speech Recognition Error:", event);
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = async () => {
        this.microActivoBarCode = false;
        if (!this.queryCodeBar) {
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
      this.microActivo = false;
      // console.log("Búsqueda:", this.query);
      const inputElement = this.$refs["autocompleteProducto"].$el.querySelector(
        "input"
      );
      inputElement.focus();
      setTimeout(() => {
        inputElement.value = this.query;
        inputElement.dispatchEvent(new Event("input", { bubbles: true }));
      }, 1000);
    },
    searchCodeBar() {
      // console.log("Búsqueda:", this.query);
      // const inputElement = this.$refs["autocompleteProducto"].$el.querySelector(
      //   "input"
      // );
      let inputElement = document.getElementById("txtBarcode");
      inputElement.focus();

      inputElement.value = this.queryCodeBar;
      inputElement.dispatchEvent(new Event("input", { bubbles: true }));
      setTimeout(() => {
        this.loadBarcode();
      }, 1000);
    },
    irtxtBarcode(event) {
      if (event) {
        event.preventDefault();
      }
      document.getElementById("txtBarcode").focus();
    },
    irtxtAddProduct(event) {
      if (event) {
        event.preventDefault();
      }
      document.getElementById("txtAddProduct").focus();
    },
    setSubtotal(index, val, prod) {
      let precio = this.$options.filters.redondear(
        val / this.productos[index].cantidad,
        4
      );
      Vue.set(this.productos[index], "precio", precio);
    },
    actualizarPorcentajeUtilidad(index) {
      this.productos[
        index
      ].PorcentajeUtilidad = this.$options.filters.redondear(
        ((this.productos[index].precio - this.productos[index].PrecioCosto) /
          this.productos[index].PrecioCosto) *
          100,
        2
      );
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    changeCant(val, prod) {
      if (val > 0) {
        prod.cantidad = prod.cantidadPres;
        prod.cantidad *= val;
      }
      this.$nextTick(() => {
        this.actualizarProd(prod);
      });
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

    cambiarPrecio(index, prod) {
      var utilidad =
        this.productos[index].PrecioCosto *
        (this.productos[index].PorcentajeUtilidad / 100);
      this.productos[index].precio = this.$options.filters.redondear(
        this.productos[index].PrecioCosto + utilidad,
        2
      );
    },
    recalcularManoObra() {
      this.productos.forEach((producto, index) => {
        if (producto.EsManoDeObra) {
          this.addManoDeObra(index, { target: {} });
        }
      });
    },
    addManoDeObra(index, e) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");

      if (pi < 0) {
        this.$toasted.error("Primero seleccione el producto MANODEOBRA");
        this.$set(this.productos, index, {
          ...this.productos[index],
          EsManoDeObra: 0
        });
        return;
      }

      let productoMO = this.productos[pi];
      let productoSeleccionado = this.productos[index];

      if (!productoMO.precioOriginal) {
        productoMO.precioOriginal = productoMO.precio;
      }

      let monto =
        (productoSeleccionado.precio * productoSeleccionado.cantidad) /
        productoMO.cantidad;

      if (productoSeleccionado.EsManoDeObra) {
        this.$set(
          productoMO,
          "precio",
          parseFloat((productoMO.precio - monto).toFixed(2))
        );
        this.$set(productoSeleccionado, "disabled", true);
      } else {
        this.$set(
          productoMO,
          "precio",
          parseFloat((productoMO.precio + monto).toFixed(2))
        );

        let hayDescuentos = this.productos.some(p => p.EsManoDeObra);
        if (!hayDescuentos) {
          this.$set(productoMO, "precio", productoMO.precioOriginal);
        }

        this.$set(productoSeleccionado, "disabled", false);
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
      if (e) {
        e.preventDefault();
      }
      let idProducto = this.loadCode;

      if (idProducto) {
        let params = { codigoBarra: idProducto };
        let prod = {};
        await this.axios
          .get(this.API + "productos/id/" + idProducto, { params: params })
          .then(response => {
            if (response.data.IdProducto) {
              prod = response.data;
            } else {
              this.loadCode = "";
              this.$toasted.show("No se encontró ningun producto");
            }
          });
        const existe = this.productos.find(p => p.IdProducto == prod.IdProducto);
        if (existe) {
          existe.cantidad = parseFloat(existe.cantidad) + 1;
          this.loadCode = "";
          let barCodeInput = document.getElementById("txtBarcode");
          if (barCodeInput) barCodeInput.focus();
          return;
        }
        prod.Series = await this.obtenerSeriePorProducto(idProducto);
        let p = await this.axios(
          `${this.API}/productos/stock/${this.idAlmacen}`,
          {
            params: {
              idProductos: [idProducto]
            }
          }
        ).then(res => {
          return res.data[0];
        });

        this.$emit("setProducto", { ...prod, stock: p.stock });
        setTimeout(() => {
          this.loadCode = "";
          // document.getElementById("txtBarcode").focus();
          let barCodeInput = document.getElementById("txtBarcode");
          if (barCodeInput) {
            barCodeInput.focus();
          }
        }, 100);
      }

      // this.setProducto();
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
    editarProducto(Pro) {
      this.productoSelect = Pro;
      this.$refs.productoFormEdit.show = true;
      this.$refs.productoFormEdit.nuevo = false;
    },
    agregarGuardar(id, pxp, obj) {
      // this.add([id, false, false, false, false, true, this.isVentas]);
      // setTimeout(() => {
      //   this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
      // }, 200);

      // if (this.isVentas) {
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
        return;
        // }
      }
      this.setProducto(obj);
    },

    async setProducto(obj) {
      this.precioCont = obj.PrecioContado;
      obj.estadoPxP = false;
      this.loadCode = "";
      this.preciosProd = [];
      this.objProd = {};
      obj.PrecioContado = parseFloat(obj.PrecioContado).toFixed(1);
      let productoSerie = await this.obtenerSeriePorProducto(obj.IdProducto);
      obj.Series = productoSerie;

      // if (!this.isVentas) {
      //   obj.PreciosPorProducto = "";
      // }
      obj.Descripcion = null;

      this.preciosProd = JSON.parse(obj.PreciosPorProducto || "[]");
      let fPP = this.preciosProd.filter(p => p.cantidad === 0);

      if (obj.TienePresentacion == 0) {
        console.log("ssssssssssss");
        obj.estadoPxP = true;
        this.setProductoUni(obj);
        return;
      }

      this.$refs["modalCant"].show();
      // this.preciosProd = JSON.parse(obj.PreciosPorProducto)
      this.objProd = obj;
    },
    async obtenerSeriePorProducto(id) {
      let productoSerie = [];
      let params = {
        IdProducto: id,
        Todos: false,
        empresa: sessionStorage.getItem("empresa"),
        IdAlmacen: this.idAlmacen
      };
      productoSerie = await this.axios(`${this.API}/productos/serie`, {
        params: {
          IdProducto: id,
          IdAlmacen: this.idAlmacen
        }
      }).then(res => {
        return res.data;
      });

      return productoSerie;
    },
    setProductoUni(obj) {
      obj.Descripcion = null;
      this.objProd.cantxPrecio = 1;
      this.$refs.autocompleteProducto.setValue("");
      obj.precio = parseFloat(obj.PrecioContado).toFixed(1);
      obj.cantidad = 1;
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj.cantidadPres = 1;
      this.$emit("setProducto", obj);
      this.$refs["modalCant"].hide();

      this.actualizarProd(obj);
      this.$nextTick(() => {
        setTimeout(() => {
          let barCode = document.getElementById("txtBarcode");
          if (barCode) {
            barCode.focus();
            barCode.select(); 
          }
        }, 150);
      });
    },
    setProductoCant(prod) {
      prod.Descripcion = null;
      this.objProd.cantxPrecio = 1;
      this.cant = prod.cantidad;
      let obj = this.objProd;
      this.$refs.autocompleteProducto.setValue("");
      //obj.FLETE = 0
      // this.objProd.precio = 0
      // this.objProd.cantidad = 0
      obj.precio = parseFloat(obj.precio).toFixed(1);
      obj.cantidad = prod.cantidad;
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj = {
        ...obj,
        cantidad: prod.cantidad,
        precio: prod.precio
      };
      // obj = this.onSetProducto(obj, prod.cantidad, prod.precio);
      this.$emit("setProducto", obj);
      // this.add([
      //   obj,
      //   () => {
      //     this.$emit("onAddProducto", obj);
      //     this.$toasted.success("Producto añadido correctamente");
      //   },
      //   false,
      //   false,
      //   this.cliente.EsVIP
      // ]);
      this.$refs["modalCant"].hide();
      obj.cantidadPres = this.cant;
      // this.actualizarProd(obj);
      this.$nextTick(() => {
          setTimeout(() => {
            let barCode = document.getElementById("txtBarcode");
            if (barCode) {
              barCode.focus();
              barCode.select();
            }
          }, 150);
        });
      },
    showPresentacion(obj) {
      if (!obj.PreciosPorProducto !== "") {
        this.precioCont = obj.PrecioContado;
        obj.estadoPxP = false;
        this.preciosProd = obj.PreciosPorProducto
          ? JSON.parse(obj.PreciosPorProducto)
          : [];
        this.objProd = obj;
        this.$refs["modalCant"].show();
        return;
      }
    },
    renderChild(data) {
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
          .imagen{
            width: 100%;
            heigth: 100%;
          }
          .imagen:hover{
            width: 100%;
            heigth: 250px;
          }
          
        </style>
        <div class="dvdProductos">
          <table id="table-render" class="tblProductos" style="padding:0; margin:0">
            <tbody>
              <tr>
                <td style="text-align: center;width: 60px" class="${styleStock}"><b>${
          data.stock
            ? Number.isInteger(data.stock)
              ? data.stock
              : data.stock.toFixed(2)
            : data.stock
        }</b></td>
                <td style="width: 200px;padding:0 2%"><b>${data.Producto} - ${
          data.CodigoBarra
        }</b></td>
                <td class="ocultarMovil" style="padding:0 2% ;width: 100px">${
                  data.ProductoCategoria
                }</td>
                <td class="ocultarMovil" style="padding:0 2% ;width: 100px">${
                  data.ProductoMarca
                }</td>
                <td  style="text-align: center;width: 65px" class="table-primary"><b>${
                  data.PrecioContado
                }</b></td>
                <td style="text-align: center;width: 5%">
                  <img src="${
                    data.Imagen == null || data.Imagen == ""
                      ? "static/no-photo.png"
                      : "/resources/images/productos/" + data.Imagen
                  }" class="imagen">
                </td>
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
    // setCantidad (index, val) {
    //   this.$emit('onInputCantidad', val)
    // },
    setNuevoPrecioContado(index) {
      let producto = { ...this.productos[index] };
      // Añadir IGV si check IGV
      let precio = parseFloat(producto.precio);
      let flete = producto.FLETE
        ? parseFloat(
            producto.FLETE / (producto.cantidad ? producto.cantidad : 1)
          )
        : 0;

      if (producto.TieneIgv) {
        precio += precio * 0.18;
      }
      let precioFinal = 0;

      precioFinal =
        (precio * producto.PorcentajeUtilidad) / 100 + precio + flete;
      // +parseFloat(flete).toFixed(2);
      // + parseFloat(producto.PorcentajeUtilidad.FLETE /
      //   (producto.PorcentajeUtilidad.cantidad
      //     ? producto.PorcentajeUtilidad.cantidad
      //     : 1));

      let normalPrecio = this.$options.filters.redondear(precioFinal);

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
      /*
      if (this.productos[index].FLETE) {
        total += parseFloat(this.productos[index].FLETE);
      }*/
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
    addNumber(number) {
      this.cantidadInput = this.cantidadInput + number;
    },
    deleteNumber() {
      this.cantidadInput = this.cantidadInput.toString().slice(0, -1);
    },
    buscarCodigoBarra() {
      this.loadCode = this.cantidadInput;
      this.loadBarcode();
      this.$nextTick(() => {
        this.cantidadInput = "";
      });
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
    ProductoForm
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
.calc__numbers {
  width: 15vw;
  height: 15vw;
}
.calc__numbers2 {
  width: 100%;
  height: 20vh;
}
.calc__numbers .botones {
  /* border: 1px solid #000000; */
  cursor: pointer;
  text-align: center;
}
.calc__numbers .botones:hover {
  color: #fff;
  background: #65ba9b;
  border: 1px;
}
.cameraBarra {
  height: 50vh;
  width: auto;
  margin: 0 auto;
}
.tblProductos {
  width: 830px;
  /* font-size: 1rem !important; */
}
.ocultarMovil {
  /* display: inline; */
}
.divTBl {
  font-size: 1rem;
  overflow-x: auto;
  white-space: nowrap;
}
.OcultarPC {
  visibility: hidden;
}

.OcultarTable {
  visibility: visible;
}

@media only screen and (max-width: 1440px) {
  .cameraBarra {
    height: 50vh;
    width: auto;
  }
  .OcultarPC {
    visibility: visible;
  }

  .OcultarTable {
    visibility: hidden;
  }
  #modalCodigoBarra .modal-dialog {
    max-width: 50vw !important;
    max-height: 50vh !important;
  }
  .tblProductos {
    width: 720px !important;
    font-size: 1rem !important;
  }
}
@media only screen and (max-width: 750px) {
  #modalCodigoBarra .modal-dialog {
    max-width: 100vw !important;
    max-height: 80vh !important;
  }
  .tblProductos {
    width: 400px !important;
    font-size: 1rem !important;
  }
  .cameraBarra {
    height: 80vh;
    width: auto;
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
  .tblProductos {
    width: 400px !important;
    font-size: 1rem !important;
  }

  .table-responsive {
    font-size: 0.7rem !important;
  }
  .divTBl {
    font-size: 0.7rem;
    overflow-x: auto;
    white-space: nowrap;
  }
}
.txtProducto {
  height: 50px !important;
}

#modalCodigoBarra {
  padding: 0 !important;
}
@keyframes parpadeo-color {
  0%,
  100% {
    /* Color normal o "apagado" */
    background: #9fa8da;
    color: black;
  }
  50% {
    /* Color "encendido" */
    background: #1a237e;
    color: #ffffff;
  }
}

.alerta {
  animation: parpadeo-color 1.5s ease-in-out infinite;
}
</style>
