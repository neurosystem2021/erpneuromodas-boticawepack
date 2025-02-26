<template>
    <b-col md="12">
        <b-modal ref="modalCant" size="lg" id="modalCantidad" hide-footer hide-header>
                <div class="d-block text-center">
                    <h3 style="text-decoration:underline">{{ objProd.Producto}}</h3>
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
                        <div>
                            <b-row class="nomCart2">
                                <h6 class="text-uppercase">{{ objProd.ProductoMedicion }}</h6>
                            </b-row>
                            <b-row class="nomCart2">
                            <h6 style="margin-right:5px">Stock:</h6><h5><b-badge v-if="objProd.stock<=0" variant="danger">{{ objProd.stock | redondear(2) }}</b-badge>
                            <b-badge v-else variant="success">{{ objProd.stock | redondear(2) }}</b-badge></h5>
                            </b-row>
                        </div>
                        </b-col>
                        <b-col md="6" class="text-left">
                        <h5>x1</h5>
                        <span><b >COSTO: </b>S/ {{ objProd.PrecioCosto | redondear(2) }}</span><br>
                        <b>VENTA: </b>S/ {{ objProd.PrecioContado | redondear(2) }}
                        </b-col>
                    </b-row>
                    </b-card>
                </b-col>

                <b-col cols="6" v-for="d in preciosProd" :key="d.id">
                    <b-card
                    v-if="parseFloat(d.cantidad) > 0"
                    align="center"
                    class="mb-3 contentCard"
                    :class="{'disabledMouse': !parseFloat(d.cantidad)}"
                    @click="() => {
                        if ( parseFloat(d.cantidad) ) {
                        setProductoCant(d)
                        }
                    }"
                    >
                    <b-row>
                        <b-col md="6" class="nomCart" >
                        <div>
                            <b-row class="nomCart2">
                            <h6 class="text-uppercase">{{ d.nombre }}</h6>
                            </b-row>
                            <b-row class="nomCart2">
                            <h6 style="margin-right:5px">Stock:</h6><h5><b-badge v-if="(objProd.stock/d.cantidad)<=0" variant="danger">{{ (objProd.stock/d.cantidad) | redondear(2)}}</b-badge>
                            <b-badge v-else variant="success">{{ (objProd.stock/d.cantidad) | redondear(2)}}</b-badge></h5>
                            </b-row>
                        </div>
                        </b-col>
                        <b-col md="6" class="text-left">
                        <h5>x{{ d.cantidad }}</h5>
                        <span><b>COSTO: </b>S/ {{ d.precioc | redondear(2) }}</span><br>
                        <b>VENTA: </b>S/ {{ d.precio | redondear(2) }}
                        </b-col>
                    </b-row>
                    </b-card>
                </b-col>
                </b-row>
            </b-modal>
        <b-modal ref="modalIndi" size="xl" id="modalIndicacion" hide-footer hide-header>
          <div class="d-block text-center">
            <h5 class="font-weight-normal">Indicaciones para:</h5>
            <h4>{{productoSelect.Producto}}</h4>
          </div>
          <hr>
          <b-row>
             <b-col md="12">
                <textarea 
                class="form-control"
                style="text-transform: uppercase;"
                cols="9" rows="3"
                v-model="productoSelect.indicacion">
                </textarea>
            </b-col>
          </b-row>
          <br>
          <b-row>
            <b-col md="12">
              <b-form-group>
              <label>Indicaciones Comunes:</label>
                <autocomplete
                  ref="autocompleteIndicacion"
                  :url="apiInidicaciones"
                  :customParams="{ limit: '25' }"
                  anchor="Indicaciones"
                  :onShouldRenderChild="renderChild"
                  label="writer"
                  placeholder="Agregar indicación"
                  :on-select="setIndicacion"
                  :onFocus="focusAutocomplete.bind(null, 'autocompleteIndicacion')"
                  :classes="{input: 'form-control'}">
                </autocomplete>
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col class="text-center">
              <b-button variant="info" size="md" @click="()=>{$refs['modalIndi'].hide()}">Cerrar</b-button>
            </b-col>
          </b-row>
        </b-modal>

        <!-- MODALS TOUCH -->

        <b-modal ref="modalIngCant" id="ingCant" size="sm" hide-footer class="ingCant" title="Cantidad" hide-header-close>
          <label><b style="text-decoration: underline;">{{objProd.Producto}}</b></label>
          <input type="text" inputmode="none" @keypress="(e)=>{ verificar(e)}" v-model="cantidadInput" style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control"/>
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
                      <td class="botones" @click="addPoint('.');">.</td>
                      <td class="botones" @click="deleteNumber();"><i class="fas fa-backspace"></i></td>
                  </tr>
              </tbody>
            </table>
            <br>
            <div style="text-align: center; width: 100%; height: 100%;">
              <b-button style="width: 49%;" size="md" variant="success" @click="ingresaNum(capturaIndex)">Ingresar</b-button>
              <b-button style="width: 49%;" size="md" variant="danger" @click="cerrrarNum()">Cerrar</b-button>
            </div>
        </b-modal>

        <b-modal ref="modalIngPre" id="ingPre" size="sm" hide-footer class="ingPre" title="Precio" hide-header-close>
          <label><b style="text-decoration: underline;">{{objProd.Producto}}</b></label>
          <input type="text" inputmode="none" @keypress="(e)=>{ verificar(e)}" v-model="cantidadInput" style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control"/>
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
                      <td class="botones" @click="addPoint('.');">.</td>
                      <td class="botones" @click="deleteNumber();"><i class="fas fa-backspace"></i></td>
                  </tr>
              </tbody>
            </table>
            <br>
            <div style="text-align: center; width: 100%; height: 100%;">
              <b-button style="width: 49%;" size="md" variant="success" @click="ingresaNum(capturaIndex)">Ingresar</b-button>
              <b-button style="width: 49%;" size="md" variant="danger" @click="cerrrarNum()">Cerrar</b-button>
            </div>
        </b-modal>

        <b-modal ref="modalIngDesc" id="ingDesc" size="sm" hide-footer class="ingDesc" title="Descuento" hide-header-close>
          <label><b style="text-decoration: underline;">{{objProd.Producto}}</b></label>
          <input type="text" inputmode="none" @keypress="(e)=>{ verificar(e)}" v-model="cantidadInput" style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control"/>
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
                      <td class="botones" @click="addPoint('.');">.</td>
                      <td class="botones" @click="deleteNumber();"><i class="fas fa-backspace"></i></td>
                  </tr>
              </tbody>
            </table>
            <br>
            <div style="text-align: center; width: 100%; height: 100%;">
              <b-button style="width: 49%;" size="md" variant="success" @click="ingresaNum(capturaIndex)">Ingresar</b-button>
              <b-button style="width: 49%;" size="md" variant="danger" @click="cerrrarNum()">Cerrar</b-button>
            </div>
        </b-modal>

        <!-- MODALS TOUCH -->

        <b-row style="height:50vh; overflow-y: scroll; background: #FFFFFF;">
            <b-col md="12" style="padding-right: 5px !important;">
                <b-table id="carrito" small responsive  striped hover :items="productos" :fields="fields" ref="tablaCarrito">
                <template slot="Indicaciones" slot-scope="data">
                  <textarea 
                    :disabled="bloquearInput?true:false || esDelivery"
                    class="form-control"
                    style="width: 120px; font-size:0.8rem; text-transform: uppercase;"
                    cols="9" rows="3"
                    v-model="productos[data.index].indicacion"></textarea>
                </template>

                <!--<template slot="presentaciones" slot-scope="data">
                  <b-button size="sm" variant="info" @click="showPresentacion(data.item)"><i class="fas fa-list"></i></b-button>
                </template>-->

                <!--<template slot="eventPres" slot-scope="data">
                  <b-button :disabled="bloquearInput?true:false || esDelivery" size="sm" variant="info" @click="showIndicaciones(productos[data.index])"><i class="fas fa-pencil-alt"></i></b-button>
                </template>-->

                <template slot="eventPresent" slot-scope="data">
                  <b-button style="font-size: 0.7rem;" size="sm" v-if="productos[data.index].PreciosPorProducto  !='' " :disabled="bloquearInput?true:false" variant="info" @click="showPresentacion(productos[data.index],data.index)"><i class="fas fa-list"></i></b-button>
                  <b-form-text style="padding: 0 !important; margin-bottom: -15px; margin-top: 0; width: 50px; font-size: 0.7rem;">
                    <p style="color: #007bff; text-align: center; font-weight: bold;">{{ productos[data.index].cantidad * productos[data.index].CantidadPresentacion }} uni</p>
                  </b-form-text>
                </template>

                <template slot="cantidad" slot-scope="data">
                <div>
                  <input
                    v-model="productos[data.index].cantidad"
                    type="number"
                    style="width: 90px; font-weight:bold"
                    step="0.01"
                    min="0"
                    autocomplete="off"
                    @focus="abrirPop1(data.index)"
                    v-bind:class="{ new: data.item.IdProducto==productoChange }"
                    @input="changeCantidad(data.index)"
                    class="form-control"
                    placeholder="Cantidad"
                  />
                  </div>
                </template>
                <template slot="Presentacion" slot-scope="data" >
                  <b-form-input
                    v-model="productos[data.index].Presentacion"
                    type="text"
                    style="text-align:center; font-weight:bold; width: 95px; font-size: 0.85rem;"
                    autocomplete="false"
                    readonly
                    ></b-form-input>
                </template>
                <template slot="precio" slot-scope="data">
                <input
                    v-model="productos[data.index].precio"
                    type="number"
                    style="width: 90px"
                    min="0"
                    step="0.001"
                    @focus="abrirPop2(data.index)"
                    placeholder="Precio"
                    autocomplete="off"
                    @input="setNuevaCantidad(data.item,data.index)"
                    class="form-control"
                    />
                </template>
                <template slot="subtotal" slot-scope="data">
                    <input                      
                      type="number" 
                      style="width: 90px;" 
                      class="form-control my-2"
                      v-model="data.item.subtotal"
                      @input="recalcularKilake(data.index)"
                    >
                </template>
                <template slot="igv" slot-scope="data">
                <b-form-checkbox
                    v-model="productos[data.index].TieneIgv"
                    :value="1"
                    @input="setNuevoPrecioContado(data.index)"
                    :unchecked-value="0">
                    <div>{{ getIgv(data.index) }}</div>
                </b-form-checkbox>
                </template>

                <template slot="descuento" slot-scope="data">
                <input
                    :disabled="bloquearInput?true:false || esDelivery || (precioHabilitado==false?false:true)"
                    v-model="productos[data.index].descuento"
                    type="number"
                    min="0"
                    step="0.01"
                    @focus="abrirPop3(data.index)"
                    style="width: 80px"
                    @input="(val)=>{ actualizarProd({Producto:productos[data.index],Pos:data.index})}" 
                    value="0"
                    class="form-control"/>
                </template>

                <template slot="total" slot-scope="data">
                {{ getTotal(data.index) }}
                <br>
                <!--<h6 v-if="isVentas"><i>Prof.</i></h6>
                <input v-if="isVentas"
                    class="form-check-input" 
                    type="checkbox" 
                    :disabled="bloquearInput?true:false"
                    @change="(e) => {
                    $emit('onCheckProf', e)
                    asignarDisabledProf(data.index, e)
                    }"
                />-->
                </template>
                <template slot="acciones" slot-scope="data">
                <b-button :disabled="bloquearInput?true:false" variant="danger" size="sm" @click="quitarProducto(data.index)"><i class="fas fa-times fa-lg"></i></b-button>
                </template>

                <template slot="show_details" slot-scope="row">
                <b-button size="sm" variant="info" @click.stop="row.toggleDetails">></b-button>
                </template>
                <template slot="row-details" slot-scope="row">

                </template>

                <template slot="stock" slot-scope="data">
                <div v-if="data.item.CodigoBarra=='COSTODELIVERY'">
                    <img src="static/moto-delivery.png" style="width: 24px" />
                </div>
                <div v-else>
                    <img v-if="data.item.EsPadre" src="static/box.png" style="width: 24px" />
                    <div v-if="!data.item.EsPadre">{{data.item.stock? Number.isInteger(data.item.stock)?data.item.stock:data.item.stock.toFixed(2):data.item.stock }}</div>
                </div>
                </template>
            </b-table>
            </b-col>
        </b-row>
        <b-row style="border-top: 3px solid #C0C0C0">
            <b-col md="3">
              <b-row>
                <slot name="botones"></slot>
              </b-row>
            </b-col>
            <b-col cols="4" class="text-center mt-1">
               <slot name="infoLlevar"></slot>
            </b-col>
            <b-col md="5" class="text-center mt-1">
              <table class="table b-table border table-footer" style="font-size:0.85rem">
                <tbody>
                  <tr>
                    <td>
                      <b-button class="p-1 ml-1 mt-1" id="popover-metodopago" variant="success"><i class="fas fa-credit-card" ></i></b-button>
                      <br>
                      <b-badge v-if="idMetodoPago==1" variant="success" style="font-size: 0.70rem;">EFECTIVO</b-badge>
                      <b-badge v-if="idMetodoPago==2" variant="info" style="font-size: 0.70rem;">TARJETA</b-badge>
                      <b-badge v-if="idMetodoPago==3" variant="info" style="font-size: 0.70rem;">YAPE/PLIN</b-badge>
                      <b-badge v-if="idMetodoPago==4" variant="info" style="font-size: 0.70rem;">TRANSF.</b-badge>
                      <b-popover target="popover-metodopago" title="Metodo de pago" placement="bottomleft" triggers="focus">
                      <slot name="metodopago"></slot>
                      </b-popover>
                    </td>
                    <td class="table-success" style="vertical-align: middle !important;">SUBTOTAL</td>
                    <td class="table-success font-weight-bold" style="vertical-align: middle !important;">{{ parseFloat(sumatoriaSubTotal) + parseFloat(sumatoriaDescuento) | redondear }}</td>
                  </tr>
                  <tr>
                    <td>
                      <b-button class="p-1 ml-1 mt-1" id="popover-opciones" variant="warning"><i class="fas fa-tags" ></i></b-button>
                      <b-popover target="popover-opciones" title="Opciones" triggers="focus" >
                        <slot name="operaciones"></slot>
                      </b-popover>  
                    </td>
                    <td class="table-danger" style="vertical-align: middle !important;">DESCUENTO</td>
                    <td class="table-danger  font-weight-bold" style="vertical-align: middle !important;">{{ sumatoriaDescuento > 0?'-'+sumatoriaDescuento:sumatoriaDescuento  }}</td>
                  </tr>
                  <tr v-if="sumatoriaIgv > 0">
                    <td></td>
                    <td style="vertical-align: middle !important;"><b>IGV</b></td>
                    <td class="table-danger">{{ sumatoriaIgv }}</td>
                  </tr>
                  <tr>
                    <td>
                      <b-button class="p-1 ml-1 mt-1" id="popover-num" variant="primary"><i class="fas fa-calculator" ></i></b-button>
                      <b-popover target="popover-num" triggers="focus" placement='bottom'>
                        <table class="calc__numbers">
                          <tbody>
                              <tr>
                                  <td class="botones" @click="foldNumbers('7');">7</td>
                                  <td class="botones" @click="foldNumbers('8');">8</td>
                                  <td class="botones" @click="foldNumbers('9');">9</td>
                                  <td class="botones" @click="accionBoton=1" v-bind:class="{ accionActiva: accionBoton==1 }"><span>Cant.</span></td>
                              </tr>
                              <tr>
                                  <td class="botones" @click="foldNumbers('4');">4</td>
                                  <td class="botones" @click="foldNumbers('5');">5</td>
                                  <td class="botones" @click="foldNumbers('6');">6</td>
                                  <td class="botones" @click="accionBoton=2" v-bind:class="{ 'accionActiva': accionBoton==2 }">Desc.</td>
                              </tr>
                              <tr>
                                  <td class="botones" @click="foldNumbers('1');">1</td>
                                  <td class="botones" @click="foldNumbers('2');">2</td>
                                  <td class="botones" @click="foldNumbers('3');">3</td>
                                  <td class="botones" @click="accionBoton=3" v-bind:class="{ 'accionActiva': accionBoton==3 }">Precio</td>
                              </tr>
                              <tr>
                                  <td class="botones" @click="foldNumbers('0');">0</td>
                                  <td class="botones" @click="pushNumbers('-');">-</td>
                                  <td class="botones" @click="pushNumbers('+');">+</td>
                                  <td class="botones" @click="result();"><i class="fas fa-backspace"></i></td>
                              </tr>
                          </tbody>
                        </table>
                      </b-popover>
                    </td>
                    <td style="vertical-align: middle !important;"><b>TOTAL</b></td>
                    <td class="table-danger  font-weight-bold" style="vertical-align: middle !important;">{{ sumatoriaTotal }}</td>
                  </tr>
                  <tr>
                    <td  colspan="3">
                      <b-row>
                        <b-col md="8">
                          <b-input-group prepend="Pagó con" class="pagocon mt-2 mb-2" >
                            <b-form-input
                              type="text"
                              autocomplete="off"
                              v-model="pagocon"
                              @input="asignarVuelto">
                            </b-form-input>
                          </b-input-group>
                        </b-col>
                        <b-col  md="4">
                            <label>Vuelto:</label>
                            <br>
                            <b>S/  {{ vuelto | redondear }}</b>
                        </b-col>
                      </b-row>
                    </td>
                  </tr>
                </tbody>
              </table>
            </b-col>
        </b-row>
    </b-col>
</template>

<script>
import { mapState, mapActions } from "vuex";
import ProductoForm from "./ProductoForm";
import config from "@/store/config.js";
export default {
  mounted() {
    // this.alterarBuscadorProductos()
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
      pagocon: "",
      cliente: {},
      preciosProd: [],
      objProd: {},
      PresentacionesProd: [],
      medicionPro: "UNIDAD",
      cant: 0,
      precioCont: 0,
      productoSelect: {},
      idPerfil: sessionStorage.getItem("IdPerfil"),
      accionBoton: 1,
      productoChange: null,
      cantidadInput: "",
      capturaIndex: 0,
      indexPos: null
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
    descuentoActivo: Number,
    precioHabilitado: Boolean,
    isVentas: Boolean,
    tipo: String,
    setPrecio: Boolean,
    tipoCambio: Number,
    igvTotal: Boolean,
    idAlmacen: Number,
    bloquearInput: Boolean,
    idMetodoPago: Number,
    esDelivery: Boolean,
    touchHab: Boolean,
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
          //{ key: 'presentaciones', label: '', sortable: false, class:'text-center stocksize'},
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center stocksize"
          },
          { key: "Producto", class: "fontsize thsize" },
          //{ key: 'Indicaciones', label: 'Indicaciones' , class:'thsize'},
          //{ key: 'eventPres', label: '', class:'thsize' },
          // { key: 'cantxPrecio', variant: 'warning' },
          // { key: 'FechaAlquiler', label: 'Fecha Inicio' },
          // { key: 'FechaAlquilerFin', label: 'Fecha Fin'},
          /*{ key: 'ProductoMedicion', label: 'Medición' },*/
          //{ key: 'cantxPrecio', variant: 'warning', class:'text-center thsize' },
          { key: "eventPresent", label: "", class: "fontsize thsize" },
          {
            key: "Presentacion",
            label: "Presentación",
            class: "text-center stocksize"
          },
          { key: "cantidad", class: "text-center thsize" },
          { key: "precio", label: "Precio", class: "thsize" },
          //{ key: 'utilidad', label: 'Porcentaje Utilidad %' },
          {
            key: "subtotal",
            label: "SubTotal",
            variant: "success",
            class: "text-center thsize"
          },
          { key: "descuento", label: "Descuento", class: "thsize" },
          {
            key: "total",
            label: "Total",
            variant: "danger",
            class: "text-center thsize"
          },
          { key: "acciones", label: "", class: "thsize" }
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
    apiInidicaciones() {
      return this.API + "indicaciones";
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
      obtenerStok: "obtenerStock",
      limpiarCart: "limpiarCarrito"
    }),
    actualizaDesc(index, prod) {
      //Vue.delete(this.productos[index], 'descuento');
      Vue.set(this.productos[index], "descuento", parseFloat(prod.descuento));
      this.actualizarProd({ Producto: prod, Pos: index });
    },
    changeCant(val, prod, index) {
      if (val) {
        prod.cantidad = val;
        //prod.cantidad = Number(val)
      }
      this.$nextTick(() => {
        this.actualizarProd({ Producto: prod, Pos: index });
      });
    },
    abrirPop1(index) {
      if (!this.touchHab) {
        return;
      }
      this.capturaIndex = index;
      this.objProd = this.productos[index];
      this.$refs["modalIngCant"].show();
      this.$refs["modalIngPre"].hide();
      this.$refs["modalIngDesc"].hide();
    },
    abrirPop2(index) {
      if (!this.touchHab) {
        return;
      }
      this.capturaIndex = index;
      this.objProd = this.productos[index];
      this.$refs["modalIngPre"].show();
      this.$refs["modalIngCant"].hide();
      this.$refs["modalIngDesc"].hide();
    },
    abrirPop3(index) {
      if (!this.touchHab) {
        return;
      }
      this.capturaIndex = index;
      this.objProd = this.productos[index];
      this.$refs["modalIngDesc"].show();
      this.$refs["modalIngCant"].hide();
      this.$refs["modalIngPre"].hide();
    },
    addNumber(number) {
      this.cantidadInput = this.cantidadInput + number;
    },
    addPoint() {
      if (!("" + this.cantidadInput).includes(".")) {
        this.$nextTick(() => {
          this.cantidadInput = this.cantidadInput + ".";
        });
      }
    },
    deleteNumber() {
      this.cantidadInput = this.cantidadInput.toString().slice(0, -1);
    },
    verificar(e) {
      if (
        (e.key == "." && ("" + this.cantidadInput).includes(".")) ||
        e.key == " "
      ) {
        e.preventDefault();
      }
    },
    ingresaNum(index) {
      index = this.capturaIndex;
      this.cantidadInput == "" ? (this.cantidadInput = 1) : this.cantidadInput;

      if (this.$refs["modalIngCant"].is_show) {
        this.productos[index].cantidad = this.cantidadInput;
        this.actualizaDesc(index, this.productos[index]);
      }
      if (this.$refs["modalIngPre"].is_show) {
        this.productos[index].precio = this.cantidadInput;
        this.setNuevoPrecioContado(index);
      }
      if (this.$refs["modalIngDesc"].is_show) {
        this.productos[index].descuento = this.cantidadInput;
        this.ActualizarDesc(index, this.cantidadInput);
      }

      this.cantidadInput = "";
      this.$refs["modalIngCant"].hide();
      this.$refs["modalIngPre"].hide();
      this.$refs["modalIngDesc"].hide();
    },
    cerrrarNum() {
      this.cantidadInput = "";
      this.$refs["modalIngCant"].hide();
      this.$refs["modalIngPre"].hide();
      this.$refs["modalIngDesc"].hide();
    },
    showPresentacion(obj, index = null) {
      if (obj.PreciosPorProducto !== "" && obj.PreciosPorProducto !== null) {
        this.preciosProd = obj.PreciosPorProducto
          ? JSON.parse(obj.PreciosPorProducto)
          : [];
        this.objProd = obj;
        this.$refs["modalCant"].show();
        this.indexPos = index;
        return;
      }
    },
    setIndicacion(obj) {
      this.$nextTick(() => {
        if (
          this.productoSelect.indicacion == null ||
          this.productoSelect.indicacion == undefined
        ) {
          this.productoSelect.indicacion = "";
        }
        this.productoSelect.indicacion =
          this.productoSelect.indicacion + " -" + obj.Indicacion;
        //this.actualizarProd(this.productoSelect)
      });
    },
    limpiar() {
      this.limpiarCart();
    },
    cambiarPrecio(index, prod) {
      var utilidad = 1 - this.productos[index].PorcentajeUtilidad / 100;
      this.productos[index].precio =
        Math.round((this.productos[index].PrecioCosto / utilidad) * 10) / 10;
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
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    editarProducto(Pro) {
      this.indexPos = index;
      this.productoSelect = Pro;
      this.$refs.productoFormEdit.show = true;
      this.$refs.productoFormEdit.nuevo = false;
    },
    agregarGuardar(id, obj) {
      this.add([id, false, false, false, false, true, this.isVentas]);
      setTimeout(() => {
        //this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 }) ;
      }, 200);

      if (this.isVentas) {
        obj.estadoPxP = false;
        obj.ProductoMedicion = obj.medicion.ProductoMedicion;
        obj.IdProductoMedicion = obj.medicion.IdProductoMedicion;
        obj.IdProductoMarca = obj.marca.IdProductoMarca;
        obj.ProductoMarca = obj.marca.ProductoMarca;
        obj.IdProductoCategoria = obj.categoria.IdProductoCategoria;
        obj.ProductoCategoria = obj.categoria.ProductoCategoria;
        this.productoSelect = obj;
        this.showPresentacion(this.productoSelect);
      }
    },
    setProducto(obj) {
      this.precioCont = obj.PrecioContado;
      this.medicionPro = obj.ProductoMedicion;
      this.preciosProd = [];
      this.objProd = {};
      this.preciosProd = JSON.parse(obj.PreciosPorProducto || "[]");

      let fpp = this.preciosProd.filter(
        p => p.cantidad === 0 || p.cantidad === "0" || p.cantidad === ""
      );

      if (fpp.length == 4) {
        this.setProductoUni(obj);
        return;
      }

      this.$refs["modalCant"].show();
      this.objProd = obj;
    },
    setProductoUni(obj) {
      obj.factor = obj.PrecioContado;

      obj.precio = obj.PrecioContado;
      obj.cantidad = 1;
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj.indicacion = "";
      obj.Presentacion = obj.ProductoMedicion;
      obj.CantidadPresentacion = 1;
      obj.EsPresentacion = 0;
      obj.EsVentaNew = false;
      obj.precioc = obj.PrecioCosto;
      obj = this.onSetProducto(obj);
      if (this.indexPos != null) {
        this.actualizarProd({ Producto: obj, Pos: this.indexPos });
        this.indexPos = null;
        this.$refs["modalCant"].hide();
        return;
      }
      this.add([
        obj,
        () => {
          this.$emit("onAddProducto", obj);
          this.$toasted.success("Producto añadido correctamente");
          this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
        },
        { setSuma: false, setPrecioCosto: this.tipo == "mov" ? true : false },
        false,
        this.cliente.EsVIP
      ]);
      this.$refs["modalCant"].hide();
    },
    setProductoCant(producto) {
      let prod = { ...producto };
      let obj = { ...this.objProd };

      obj.factor = prod.precio;

      obj.Presentacion = prod.nombre;
      obj.CantidadPresentacion = prod.cantidad;
      obj.precioc = prod.precioc;
      obj.precio = prod.precio;
      obj.cantidad = 1;
      obj.EsPresentacion = 1;
      obj.subt = "";
      obj.Descripcion = prod.nombre;
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj.EsVentaNew = false;
      obj = this.onSetProducto(obj, prod.precio, prod.precioc);
      if (this.indexPos != null) {
        this.actualizarProd({ Producto: obj, Pos: this.indexPos });
        this.indexPos = null;
        this.$refs["modalCant"].hide();
        return;
      }
      this.add([
        obj,
        () => {
          this.$emit("onAddProducto", obj);
          this.$toasted.success("Producto añadido correctamente");
          this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
        },
        { setSuma: false, setPrecioCosto: this.tipo == "mov" ? true : false },
        false,
        this.cliente.EsVIP
      ]);
      this.$refs["modalCant"].hide();
    },
    showIndicaciones(obj) {
      this.productoSelect = obj;
      this.$refs["modalIndi"].show();
    },
    renderChild(data) {
      return `
          <div>
            <b>${data.Indicacion}</b>
          </div>  
          `;
    },
    // setCantidad (index, val) {
    //   this.$emit('onInputCantidad', val)
    // },
    setNuevaCantidad(prod, index) {
      let producto = { ...prod };

      // producto.cantidad = producto.precio / producto.factor;
      producto.subtotal = producto.cantidad * producto.precio;
      Vue.set(this.productos, index, producto);
    },
    setNuevoPrecioContado(index) {
      // Añadir IGV si check IGV
      let precio = parseFloat(this.productos[index].precio);
      if (this.productos[index].TieneIgv) {
        precio += precio * 0.18;
      }
      if (this.isVentas) {
        precio += precio * (this.productos[index].PorcentajeUtilidad / 100);
      } else {
        precio = precio / (1 - this.productos[index].PorcentajeUtilidad / 100);
      }
      let normalPrecio = this.$options.filters.redondear(precio);
      //let nuevoPrecio = this.$options.filters.redondeoMayor(precio)
      let nuevoPrecio = this.$options.filters.redondear(precio, 1);

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
      //this.updateManoDeObraNeta(index)
    },
    changeCantidad(index) {
      let producto = { ...this.productos[index] };
      producto.subtotal = producto.cantidad * producto.precio;
      Vue.set(this.productos, index, producto);
      // $emit('onInputCantidad', val.target.value);
      // actualizaDesc(data.index, productos[data.index]);
    },
    recalcularKilake(index) {
      let producto = { ...this.productos[index] };
      producto.cantidad = parseFloat(
        producto.subtotal / producto.precio
      ).toFixed(2);
      Vue.set(this.productos, index, producto);
    },
    ActualizarDesc(index, val) {
      Vue.delete(this.productos[index], "descuento");
      Vue.set(this.productos[index], "descuento", parseFloat(val));
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
.nomCart {
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 2px solid black;
}
.nomCart2 {
  display: flex;
  align-items: center;
  justify-content: center;
}
.form-check-input {
  margin-left: -0.2rem !important;
}

.calc__numbers {
  width: 15vw;
  height: 15vw;
}
.calc__numbers2 {
  width: 100%;
  height: 20vh;
}
.calc__numbers td {
  min-width: 40px;
  min-height: 40px;
  border: 0.5px solid #a3a3a3;
  cursor: pointer;
  text-align: center;
  vertical-align: middle;
  padding: 1.8% 1.8%;
  font-size: 0.8rem;
  font-weight: bold;
  font-family: Arial;
  color: black;
  background-color: #fff;
}
.calc__numbers td:last-child {
  background-color: #eaeaea;
}
.calc__numbers2 td:last-child {
  background-color: white;
}
.calc__numbers .botones:hover {
  color: #fff;
  background: #65ba9b;
}
.calc__numbers .accionActiva {
  color: #fff !important;
  background: #65ba9b !important;
}
.pagocon .input-group-text {
  font-size: 0.8rem;
  font-weight: bold;
}
.fontsize {
  font-size: 50px !important;
}
.new {
  animation: blink 0.3s ease;
}
@keyframes blink {
  0% {
    background-color: yellow;
  }
  100% {
    background-color: white;
  }
}
</style>

<style>
.ingCant .modal-backdrop {
  background: transparent;
}
.ingPre .modal-backdrop {
  background: transparent;
}
.ingDesc .modal-backdrop {
  background: transparent;
}
#ingCant {
  height: unset !important;
  right: unset !important;
  bottom: unset !important;
  margin-left: 25vw;
}
#ingPre {
  height: unset !important;
  right: unset !important;
  bottom: unset !important;
  margin-left: 10vw;
}
#ingDesc {
  height: unset !important;
  right: unset !important;
  bottom: unset !important;
  margin-left: 20vw;
}
td.fontsize {
  font-size: 0.75rem !important;
  font-weight: bold;
  text-align: center;
  vertical-align: middle !important;
}
td.stocksize {
  text-align: center !important;
  vertical-align: middle !important;
}
th.stocksize {
  font-size: 0.8rem !important;
  text-align: center;
}
th.thsize {
  font-size: 0.85rem !important;
  text-align: center;
}
td.thsize {
  vertical-align: middle !important;
}
.popover-body {
  background-color: #c1c1c1;
}
.bs-popover-bottom {
  max-width: 370px !important;
}

#carrito td {
  margin: 0 0.3rem !important;
  padding: 0 0.3rem !important;
}
</style>
