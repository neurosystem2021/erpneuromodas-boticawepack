<template>
  <b-row>
    <b-col md="12" v-if="!isVentas">
      <b-form-checkbox v-model="gravadaFlag" value="1" unchecked-value="0">
        GRAVADA
      </b-form-checkbox>

      <b-form-checkbox v-model="igvFlag" value="1" unchecked-value="0">
        IGV
      </b-form-checkbox>
    </b-col>
    <b-col md="12">
      <b-form-group>
        <b-input-group>
          <autocomplete
            ref="autocompleteProducto"
            id="autocompleteProducto"
            :url="APIProductos"
            :customParams="{ limit: '25','sortBy':'stock' ,'sortDesc':'DESC'}"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder="Agregar Producto"
            :on-select="setProducto"
            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
            :classes="{ input: 'form-control' }"
          >
          </autocomplete>
           <b-button @click="abrirMicrofono()">
            <i class="fas fa-microphone"></i>
          </b-button>
          <input
            v-model="loadCode"
            :disabled="isNota"
            @keydown.enter.prevent="loadBarcode"
            ref="barcodeInput"
            type="text"
            class="form-control"
            placeholder="Codigo de Barra"
          />
          <b-input-group-append>
            <b-button variant="success" @click="abrirCodigoBarra"
              ><i class="fas fa-barcode"></i
            ></b-button> 
            <b-button variant="primary" @click="loadBarcode"
              ><i class="fas fa-search"></i
            ></b-button>
            <producto-form
              :showButton="showButton"
              :cbGuardar="(id, pxp) => agregarObj(id, pxp)"
              ref="productoFormEdit"
              :producto-update="productoSelect"
            ></producto-form>
          </b-input-group-append>
        </b-input-group>
        <b-badge v-if="isVentas" variant="danger">Productos sin stock</b-badge>
      </b-form-group>

      <b-modal
        id="modalImagen"
        size="lg"
        ref="modalImagen"
        hide-footer
        hide-header
      >
        <div class="d-block text-center">
          <h3 style="text-decoration: underline">{{ productoNombre }}</h3>
        </div>
        <b-row
          style="display: flex; justify-content: center; align-items: center"
        >
          <b-img :src="productoUrl" class="imagenGrande"></b-img>
        </b-row>
      </b-modal>

      <b-modal
        ref="modalCant"
        size="lg"
        id="modalCantidad"
        hide-footer
        hide-header
      >
        <div class="d-block text-center">
          <h3 style="text-decoration: underline">{{ objProd.Producto }}</h3>
          <h5>Presentaciones</h5>
        </div>
        <hr />
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
                      <h6 class="text-uppercase">
                        {{ objProd.ProductoMedicion }}
                      </h6>
                    </b-row>
                  </div>
                </b-col>
                <b-col md="6" class="text-left">
                  <h5>x1</h5>
                  <span
                    ><b>Precio C: </b>S/
                    {{ objProd.PrecioCosto | redondear(2) }}</span
                  ><br />
                  <b>Precio V: </b>S/ {{ objProd.PrecioContado | redondear(2) }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>

          <b-col cols="6" v-for="d in preciosProd" :key="d.id">
            <b-card
              v-if="parseFloat(d.cantidad) > 0"
              align="center"
              class="mb-3 contentCard"
              :class="{ disabledMouse: !parseFloat(d.cantidad) }"
              @click="
                () => {
                  if (parseFloat(d.cantidad)) {
                    setProductoCant(d);
                  }
                }
              "
            >
              <b-row>
                <b-col md="6" class="nomCart">
                  <div>
                    <b-row class="nomCart2">
                      <h6 class="text-uppercase">{{ d.nombre }}</h6>
                    </b-row>
                  </div>
                </b-col>
                <b-col md="6" class="text-left">
                  <h5>x{{ d.cantidad }}</h5>
                  <span><b>Precio C: </b>S/ {{ d.precioc | redondear(2) }}</span
                  ><br />
                  <b>Precio V: </b>S/ {{ d.precio | redondear(2) }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>
        </b-row>
      </b-modal>

      <b-table
        small
        responsive
        striped
        hover
        :items="productos"
        :fields="fields"
        ref="tablaCarrito"
      >
        <template slot="eventPresImagen" slot-scope="data">
          <!--<b-button size="sm" v-if="productos[data.index].estadoPxP == false" :disabled="bloquearInput?true:false" variant="info" @click="showPresentacion(productos[data.index])"><i class="fas fa-list"></i></b-button>-->
          <b-button
            @click="
              abrirImagen(
                productos[data.index].Producto,
                productos[data.index].Imagen == null ||
                  productos[data.index].Imagen == ''
                  ? 'static/no-photo.png'
                  : '/resources/images/productos/' +
                      productos[data.index].Imagen
              )
            "
            style="margin-top: 2px"
            :id="'popover-imagen' + productos[data.index].IdProducto"
            variant="success"
            size="sm"
            ><i class="fas fa-image"></i
          ></b-button>
          <b-popover
            placement="right"
            :target="'popover-imagen' + productos[data.index].IdProducto"
            :title="productos[data.index].Producto"
            triggers="hover"
          >
            <div style="text-align: center">
              <b-img
                style="border-radius: 10px; width: 200px"
                fluid
                :src="
                  productos[data.index].Imagen == null ||
                  productos[data.index].Imagen == ''
                    ? 'static/no-photo.png'
                    : '/resources/images/productos/' +
                      productos[data.index].Imagen
                "
                alt=""
                @error="setAltImg"
              ></b-img>
            </div>
          </b-popover>
        </template>

        <template
          slot="eventPres"
          slot-scope="data"
          v-if="productos[data.index].PreciosPorProducto"
        >
          <b-button
            :disabled="bloquearInput ? true : false"
            size="sm"
            variant="info"
            @click="showPresentacion(productos[data.index], data.index)"
            style="margin-top: 2px"
            ><i class="fas fa-list"></i
          ></b-button>
        </template>

        <template slot="Presentacion" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].Presentacion"
            type="text"
            style="text-align: center; font-weight: bold; width: 120px"
            autocomplete="false"
            readonly
          ></b-form-input>
        </template>

        <template slot="obsequio" slot-scope="data">
          <div class="obsequio" v-if="tipoMov == 0">
            <b-form-checkbox
              button-variant="primary"
              v-model="productos[data.index].EsObsequio"
              :value="1"
              @change="setObsequio(data.index)"
              :unchecked-value="0"
            >
            </b-form-checkbox>
          </div>
        </template>

        <template slot="cantidad" slot-scope="data">
          <input
            v-model="productos[data.index].cantidad"
            type="number"
            style="width: 90px"
            min="0"
            step="0.01"
            @input="recalcular(data.index)"
            placeholder="Cantidad"
            class="form-control"
          />
          <b-form-text
            style="padding: 0 !important; margin-bottom: -15px; margin-top: 0"
          >
            <p style="color: #007bff; text-align: center; font-weight: bold">
              {{
                productos[data.index].cantidad *
                (productos[data.index].CantidadPresentacion
                  ? productos[data.index].CantidadPresentacion
                  : 1)
              }}
              unid.
            </p>
          </b-form-text>
        </template>
        <template slot="precio" slot-scope="data">
          <input
            v-model="productos[data.index].precio"
            :disabled="
              productos[data.index].disabled || bloquearInput
                ? true
                : false ||
                  (productos[data.index].EsObsequio == 1 && tipoMov == 0
                    ? true
                    : false)
            "
            type="number"
            style="width: 105px"
            min="0"
            step="0.00001"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)"
            class="form-control"
          />
        </template>

        <template slot="flete" slot-scope="data">
          <input
            v-model="productos[data.index].flete"
            type="number"
            style="width: 105px"
            min="0"
            step="0.00001"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)"
            class="form-control"
          />
        </template>
        <template slot="subtotal" slot-scope="data">
          <div v-if="isVentas">
            {{ getSubtotal(data.index) }}
          </div>
          <div v-else>
            <input
              type="number"
              style="width: 95px"
              min="0"
              step="0.00001"
              placeholder="Subtotal"
              class="form-control"
              v-model="productos[data.index].subtotal"
              @input="
                (val) =>
                  setSubtotal(
                    data.index,
                    val.target.value,
                    productos[data.index]
                  )
              "
            />
            <!-- <b>{{ getSubtotal(data.index) }}</b> -->
          </div>
        </template>
        <template slot="igv" slot-scope="data">
          <b-form-checkbox
            v-model="productos[data.index].TieneIgv"
            :disabled="
              productos[data.index].EsObsequio == 1 && tipoMov == 0
                ? true
                : false
            "
            :value="1"
            @input="setNuevoPrecioContado(data.index)"
            :unchecked-value="0"
          >
            <div>{{ data.item.igv }}</div>
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

        <template slot="manoobra" slot-scope="data" v-if="false">
          <input
            :disabled="bloquearInput ? true : false"
            @change="(e) => addManoDeObra(data.index, e)"
            v-if="productos[data.index].CodigoBarra != 'MANODEOBRA'"
            v-model="productos[data.index].EsManoDeObra"
            class="form-check-input"
            type="checkbox"
          />
        </template>

        <template slot="precioVenta" slot-scope="data">
          <input
            style="width: 95px"
            v-model="productos[data.index].nuevoPrecioContado"
            type="number"
            min="0"
            step="0.001"
            placeholder="Precio Venta"
            class="form-control"
          />
          <b-form-text>
            <strike> {{ productos[data.index].normalPrecioContado }} </strike>
          </b-form-text>
        </template>

        <template slot="utilidad" slot-scope="data" v-if="false">
          <input
            class="form-control"
            v-model="productos[data.index].PorcentajeUtilidad"
            style="width: 80px"
            type="number"
            step="0.01"
            :disabled="bloquearInput ? true : false"
            @input="cambiarPrecio(data.index, productos[data.index])"
            @mouseup="cambiarPrecio(data.index, productos[data.index])"
          />
          <small
            >ganancia:
            {{
              (productos[data.index].precio - productos[data.index].PrecioCosto)
                | redondear
            }}</small
          >
        </template>

        <template slot="descuento" slot-scope="data">
          <input
            :disabled="bloquearInput ? true : false"
            v-model="productos[data.index].descuento"
            type="number"
            min="0"
            style="width: 100px"
            step="0.01"
            @input="(val) => ActualizarDesc(data.index, val.target.value)"
            value="0"
            class="form-control"
          />
          <b-form-text> </b-form-text>
        </template>

        <template slot="FechaAlquiler" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].FechaAlquiler"
            type="date"
            placeholder="Fecha Fin"
            :value="formatFecha(data.item.FechaAlquiler)"
          ></b-form-input>
        </template>

        <template slot="FechaAlquilerFin" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].FechaAlquilerFin"
            type="date"
            placeholder="Fecha Fin"
            :value="formatFecha(data.item.FechaAlquilerFin)"
          ></b-form-input>
        </template>

        <!--<template slot="Descripcion" slot-scope="data">
          <textarea 
            class="form-control"
            placeholder="Descripción"
            cols="9" rows="3"
            v-model="productos[data.index].Descripcion"></textarea>
          <b-form-input
            v-model="productos[data.index].Descripcion"
            type="text"
            placeholder="Descripción"></b-form-input>
        </template>-->

        <template
          slot="movimiento"
          slot-scope="data"
          v-if="(movTipo != 0 && movTipo != undefined) || isVentas"
        >
          <div v-if="isNota" style="width: 135px; text-align: center">
            <div>
              {{ productos[data.index].hashMovimiento }}
            </div>
            <small
              >Doc:
              <span style="font-weight: bold">
                {{ productos[data.index].Serie }}-{{
                  productos[data.index].Numero
                }}</span
              ></small
            ><br />

            <div>
              {{ productos[data.index].FechaVen }}
            </div>
          </div>
          <!--<b-form-select v-if="!isNota"
            style="width: 135px;"
            :disabled="bloquearInput?true:false"
            @change="(val) => changeMovimiento(val, data.item.IdProducto, data.index)"
            v-model="productos[data.index].hashMovimiento" :options="movimientoLotes[data.item.IdProducto]"></b-form-select>
          <br>
          <div style="text-align:center" v-if="!isNota">
          <small>Doc: <span style="font-weight:bold;">{{ productos[data.index].SerieNumero }}</span></small><br />
          <small>Stock: <span style="font-weight:bold;">{{ productos[data.index].stockMovimiento }}</span></small><br />
          </div>

          <div v-if="!isNota" v-html="productos[data.index].fechaVenMovimiento"></div>-->

          <div
            style="
              display: flex;
              justify-content: space-evenly;
              align-items: center;
            "
          >
            <b-form-select
              v-if="!isNota"
              style="width: 135px"
              :disabled="bloquearInput ? true : false"
              @change="
                (val) => changeMovimiento(val, data.item.IdProducto, data.index)
              "
              v-model="productos[data.index].hashMovimiento"
              :options="movimientoLotes[data.item.IdProducto]"
            ></b-form-select>
            <div style="width: 135px">
              <div v-if="!isNota">
                <small
                  >Doc:
                  <span style="font-weight: bold">{{
                    productos[data.index].SerieNumero
                  }}</span></small
                >
                <small
                  >Stock:
                  <span style="font-weight: bold">{{
                    productos[data.index].stockMovimiento
                  }}</span></small
                >
              </div>

              <div
                v-if="!isNota"
                v-html="productos[data.index].fechaVenMovimiento"
              ></div>
            </div>
          </div>
        </template>

        <template slot="fechavencimiento" slot-scope="data">
          <b-form-input
            required="required"
            v-model="productos[data.index].FechaVen"
            type="date"
          ></b-form-input>
        </template>

        <template slot="total" slot-scope="data">
          <div style="display: flex; justify-content: space-evenly">
            {{ getTotal(data.index) }}
            <!--<h6 v-if="isVentas" style="padding-top: 2px;"><i>Prof.</i></h6>-->
          </div>
          <!--<div style="margin-top: -5px;">
            <input v-if="isVentas"
              class="form-check-input" 
              type="checkbox" 
              :disabled="bloquearInput?true:false"
              @change="(e) => {
                $emit('onCheckProf', e)
                asignarDisabledProf(data.index, e)
              }"
            />
          </div>-->
        </template>
        <template slot="acciones" slot-scope="data">
          <b-button
            :disabled="bloquearInput ? true : false"
            variant="danger"
            size="sm"
            @click="quitarProducto(data.index)"
            ><i class="fas fa-times fa-lg"></i
          ></b-button>
          <b-button
            style="margin-top: 5px"
            :disabled="bloquearInput ? true : false"
            v-if="idPerfil == 1 ? true : false"
            variant="primary"
            size="sm"
            @click="editarProducto(productos[data.index], data.index)"
            ><i class="fas fa-pencil-alt fa-sm"></i
          ></b-button>
        </template>

        <template slot="show_details" slot-scope="row">
          <b-button size="sm" variant="info" @click.stop="row.toggleDetails"
            >></b-button
          >
        </template>

        <template slot="stock" slot-scope="data">
          <img
            v-if="data.item.EsPadre"
            src="static/box.png"
            style="width: 24px"
          />
          <div v-if="!data.item.EsPadre">
            {{
              data.item.stock
                ? Number.isInteger(data.item.stock)
                  ? data.item.stock
                  : parseFloat(data.item.stock).toFixed(2)
                : data.item.stock
            }}
          </div>
        </template>
      </b-table>
    </b-col>
    <b-col md="3">
      <slot name="operaciones" v-if="!noSubtotal"></slot>
    </b-col>
    <b-col md="4">
      <slot name="metodopago"></slot>
    </b-col>
    <b-col md="2">
      <slot name="adicional"></slot>
    </b-col>
    <b-col md="3">
      <table class="table b-table border table-footer">
        <tbody>
          <tr v-if="sumatoriaDescuento > 0">
            <td class="table-success">SubTotal</td>
            <td class="table-success">
              {{
                (parseFloat(sumatoriaSubTotal) + parseFloat(sumatoriaDescuento))
                  | redondear
              }}
            </td>
          </tr>
          <tr v-if="sumatoriaDescuento > 0">
            <td class="table-danger">Descuento</td>
            <td class="table-danger">- {{ sumatoriaDescuento }}</td>
          </tr>
          <tr>
            <td><b>SubTotal</b></td>
            <td class="table-danger">{{ sumatoriaSubTotal() }}</td>
          </tr>
          <tr v-if="sumatoriaIgv > 0">
            <td><b>IGV</b></td>
            <td class="table-danger">{{ sumatoriaIgv }}</td>
          </tr>
          <tr>
            <td><b>TOTAL</b></td>
            <td class="table-danger">{{ sumatoriaTotal() }}</td>
          </tr>
          <tr v-if="isVentas">
            <td colspan="2">
              <label>pagó con:</label>
              <b-form-input
                :disabled="bloquearInput ? true : false"
                type="text"
                placeholder="Pagó con"
                v-model="pagocon"
                @input="asignarVuelto"
              ></b-form-input
              ><br />
              <label>Vuelto:</label>
              <b>S/ {{ vuelto | redondear }}</b>
            </td>
          </tr>
        </tbody>
      </table>
    </b-col>
    
    <b-modal
      id="modalCodigoBarra"
      size="lg"
      ref="modalCodigoBarra"
      hide-footer
      hide-header
    >
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
  </b-row>
</template>

<script>
import { mapState, mapActions } from "vuex";
import ProductoForm from "./ProductoForm";
import config from "@/store/config.js";
import VueQuagga from "vue-quaggajs";
import sound from "../../static/beep.mp3";
Vue.use(VueQuagga);

export default {
  mounted() {
    // this.alterarBuscadorProductos()
  },
  created() {
    // Add barcode scan listener and pass the callback function
    this.$barcodeScanner.init(this.onBarcodeScanned);
  },
  destroyed() {
    // Remove listener when component is destroyed
    this.$barcodeScanner.destroy();
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
      igvFlag: 0,
      gravadaFlag: 0,
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
      movimientoLotes: {},
      productoSelect: {},
      indexProdSelect: null,
      idPerfil: sessionStorage.getItem("IdPerfil"),
      productoSelect: {},
      productoNombre: "",
      productoUrl: "",
      indexPos: null
    };
  },
  watch: {
    productos(prods) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");

      if (pi >= 0 && !this.productos[pi].precioBruta) {
        this.productos[pi].precioBruta = this.productos[pi].precio;
      }
    },
    igvFlag() {
      this.recalcularIGV();
      console.log(this.igvFlag);
    },
    gravadaFlag() {
      console.log(this.gravadaFlag);
    }
  },
  props: {
    isVentas: Boolean,
    isNota: Boolean,
    tipo: String,
    padre: String,
    setPrecio: Boolean,
    tipoCambio: Number,
    igvTotal: Boolean,
    idAlmacen: Number,
    movTipo: Number,
    tipoMov: Number,
    almacenDestino: Number,
    almacenOrigen: Number,
    bloquearInput: Boolean,
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
          {
            key: "movimiento",
            label: "Lote",
            variant: "info",
            class: "text-center"
          },
          //{ key: 'CodigoBarra', label: 'Código'},
          //{ key: 'ProductoCategoria', label: 'Categoria'},
          { key: "Producto", label: "Artículo" },
          //{ key: 'Descripcion', label: 'Descripción'},
          { key: "ProductoMarca", label: "Marca" },
          //{ key: 'ProductoModelo', label: 'Modelo/Color'},
          //{ key: 'ProductoMedicion', label: 'Medición' },
          { key: "eventPres", label: "" },
          //{ key: 'cantxPrecio', variant: 'warning' },
          {
            key: "Presentacion",
            label: "Presentación",
            class: "text-center centrarTd"
          },
          { key: "cantidad" },
          { key: "obsequio", label: "Obsequio", class: "thcenter" },
          { key: "precio", label: "P/U" },
          { key: "subtotal", label: "SubTotal", variant: "success" },
          { key: "igv", label: "IGV" },
          // { key: 'flete', label: 'FLETE' },
          // { key: 'lote', label: 'Lote' },
          { key: "precioVenta", label: "Precio venta" },
          { key: "total", label: "Total", variant: "danger" },
          { key: "fechavencimiento", label: "Vencimiento" },
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
    sumatoriaSubTotal() {
      let subTotal = this.productos.reduce((total, prod) => {
        return total + parseFloat(parseFloat(prod.cantidad * prod.precio));
      }, 0);
      return this.$options.filters.redondear(subTotal);
    },
    sumatoriaTotal() {
      let subTotal = this.productos.reduce((total, prod) => {
        let monto =
          prod.cantidad * prod.precio +
          prod.cantidad * prod.precio * (prod.TieneIgv == 1 ? 0.18 : 0);
        return total + parseFloat(monto);
      }, 0);
      return this.$options.filters.redondear(subTotal);
    },
    recalcularIGV() {
      this.productos.forEach((prod, index) => {
        prod.TieneIgv = this.igvFlag;
        let igv = 0;
        if (this.igvFlag == 1) {
          igv = prod.subtotal * 0.18;
        }
        console.log("igv", igv);
        Vue.set(this.productos[index], "igv", igv);
      });
      this.$forceUpdate();
    },
    recalcular(index, val) {
      let prod = this.productos[index];
      prod.subtotal =
        this.productos[index].cantidad * this.productos[index].precio;
      this.$forceUpdate();
    },
    abrirImagen(producto, url) {
      this.productoNombre = producto;
      this.productoUrl = url;
      this.$refs["modalImagen"].show();
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    setObsequio(index) {
      this.productos[index].precio = 0;
      //this.productos[index].nuevoPrecioContado = 0
    },
    setSubtotal(index, val, prod) {
      let precio = this.$options.filters.redondear(
        val / this.productos[index].cantidad,
        5
      );
      Vue.set(this.productos[index], "precio", parseFloat(precio).toFixed(2));
      this.actualizarProd({ Producto: prod, Pos: index });
      this.setNuevoPrecioContado(index);
    },
    changeCant(val, prod, index) {
      if (val) {
        prod.cantidad = prod.cantidadPres;
        prod.cantidad *= val;
      }
      prod.subt = "";
      this.$nextTick(() => {
        this.actualizarProd({ Producto: prod, Pos: index });
      });
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
    changeMovimiento(hash, idProducto, index) {
      if (hash) {
        // Stock
        let stock = this.movimientoLotes[idProducto].find(m => m.value === hash)
          .Stock;
        let serieNumero = this.movimientoLotes[idProducto].find(
          m => m.value === hash
        ).SerieNumero;
        if (stock) {
          // this.productos[index].stockMovimiento = stock
          this.$nextTick(() => {
            Vue.set(this.productos[index], "stockMovimiento", stock);
            Vue.set(this.productos[index], "SerieNumero", serieNumero);
          });
        } else {
          this.$nextTick(() => {
            Vue.set(this.productos[index], "stockMovimiento", 0);
            Vue.set(this.productos[index], "SerieNumero", "----");
          });
        }
        // Fecha de vencimiento
        var fechaVen = this.movimientoLotes[idProducto].find(
          m => m.value === hash
        ).FechaVen;
        let hoy = this.$moment().unix();
        let fechaVenUnix = this.$moment(fechaVen).unix();
        let en5dias = this.$moment()
          .add(5, "days")
          .unix();
        let style = "bg-success";
        if (fechaVenUnix && fechaVenUnix < hoy) {
          style = "bg-danger";
        }
        if (fechaVenUnix >= hoy && fechaVenUnix <= en5dias) {
          style = "bg-warning";
        }
        if (fechaVen) {
          this.$nextTick(() => {
            if (!this.isVentas) {
              if (this.movTipo != 0 && this.movTipo != undefined) {
                this.productos[index].FechaVen = this.$moment(fechaVen).format(
                  "YYYY-MM-DD"
                );
                //console.log(this.$moment(fechaVen).format("YYYY-MM-DD"));
                //Vue.set(this.productos[index], 'fechaVen', this.$moment(fechaVen).format("YYYY-MM-DD"))
                this.actualizarProd({
                  Producto: this.productos[index],
                  Pos: index
                });
              }
            }

            Vue.set(
              this.productos[index],
              "fechaVenMovimiento",
              `<small style="font-weight:bold" class='venci ${style}'>F. Venci: ${this.$moment(
                fechaVen
              ).format("DD-MM-YYYY")}</small>`
            );
          });
        } else {
          this.$nextTick(() => {
            Vue.set(this.productos[index], "fechaVenMovimiento", "");
          });
        }
        // return `<small class='venci ${style}'>Venci: ${fechaVen}</small>`
      }
    },
    loadMovimientoLotes($idProducto) {
      this.movimientoLotes[$idProducto] = [];
      this.axios
        .get(
          this.API +
            "/movimientolotes/" +
            $idProducto +
            "/" +
            (this.isVentas
              ? this.idAlmacen
              : this.movTipo == 0
                ? this.almacenDestino
                : this.almacenOrigen)
        )
        .then(response => {
          let indexProducto = this.productos.findIndex(
            p => p.IdProducto === $idProducto
          );
          // Cargar por defecto lote próximo a vencer.
          let primeroVencer = response.data.reduce((a, b) => {
            return a.FechaVen && new Date(a.FechaVen) < new Date(b.FechaVen)
              ? a
              : b;
          });

          this.$nextTick(() => {
            Vue.set(
              this.productos[indexProducto],
              "hashMovimiento",
              primeroVencer.hashMovimiento
            );
            this.changeMovimiento(
              primeroVencer.hashMovimiento,
              $idProducto,
              indexProducto
            );
          });
          //
          this.movimientoLotes[$idProducto] = response.data.map(l => ({
            value: l.hashMovimiento,
            text: l.hashMovimiento,
            Stock: l.Stock,
            FechaVen: l.FechaVen,
            SerieNumero: l.Serie + "-" + l.Numero
          }));
        })
        .catch(error => {
          console.log(error);
        });
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
          { setPrecio: this.setPrecio, setSuma: true },
          { isBarCode: true }
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
    editarProducto(Pro, index) {
      this.indexPos = index;
      this.productoSelect = Pro;
      this.$refs.productoFormEdit.show = true;
      this.$refs.productoFormEdit.nuevo = false;
    },
    agregarGuardar(id, pxp, obj) {
      /* this.add([id,false,false,false,false,true,this.isVentas]);
      setTimeout(() => {
              this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 }) ;
      }, 200); 
      */
    },
    agregarObj(obj) {
      if (obj) {
        if (typeof obj === "object") {
          obj.ProductoMedicion = obj.medicion
            ? obj.medicion.ProductoMedicion || "UNIDAD"
            : "UNIDAD";
          obj.Presentacion = obj.medicion
            ? obj.medicion.ProductoMedicion || "UNIDAD"
            : "UNIDAD";
          obj.ProductoMarca = obj.marca
            ? obj.marca.ProductoMarca || "S/M"
            : "S/M";
          obj.CantidadPresentacion = 1;
          obj.EsPresentacion = 0;
          obj.PreciosPorProducto = JSON.stringify(obj.precios);
          obj = this.onSetProducto(obj);
          if (this.indexPos != null) {
            this.actualizarProd({ Producto: obj, Pos: this.indexPos });
            this.indexPos = null;
            this.$emit("onAddProducto");
            return;
          }
        }
        this.add([
          obj,
          obj => {
            this.$emit("onAddProducto");
          },
          { setPrecio: this.setPrecio, esId: true }
        ]);
      }
    },
    setProducto(obj) {
      let index = this.productos.findIndex(v => v.IdProducto == obj.IdProducto);
      console.log(index);
      if (index >= 0) {
        this.productos[index].cantidad += 1;
        this.productos[index].subtotal =
          this.productos[index].cantidad * this.productos[index].precio;
        this.$forceUpdate();
        return;
      }

      this.precioCont = obj.PrecioContado;
      this.medicionPro = obj.ProductoMedicion;
      this.loadCode = "";
      this.preciosProd = [];
      this.objProd = {};
      this.preciosProd = JSON.parse(obj.PreciosPorProducto || "[]");
      obj.EsObsequio = 0;
      obj.igv = 0;
      obj.flete = 0;
      let fpp = this.preciosProd.filter(
        p => p.cantidad === 0 || p.cantidad === "0" || p.cantidad === ""
      );
      if (!this.isVentas && this.padre == "compra") {
        if (this.movTipo == undefined) {
          this.$toasted.show("Primero seleccione un tipo de movimiento");
          return;
        }
      }

      if (fpp.length == 4) {
        this.setProductoUni(obj);
        return;
      }
      this.indexProdSelect = null;
      this.$refs["modalCant"].show();
      this.objProd = obj;
    },
    setProductoUni(obj) {
      this.$refs.autocompleteProducto.setValue("");
      obj.subt = "";
      obj.precio = obj.PrecioCosto;
      obj.cantidad = 0;
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj.Presentacion = obj.ProductoMedicion;
      obj.CantidadPresentacion = 1;
      obj.EsPresentacion = 0;
      obj.subtotal = obj.PrecioCosto * 1;
      obj = this.onSetProducto(obj);

      let index = this.productos.findIndex(v => v.IdProducto == obj.IdProducto);

      if (index == -1) {
        console.log("aaa");
        this.add([
          obj,
          () => {
            this.$emit("onAddProducto", obj);
            //this.actualizarProd(obj)
          },
          { setSuma: false, setPrecioCosto: this.tipo == "mov" ? true : false }
        ]);
        this.$refs["modalCant"].hide();
        this.$refs.barcodeInput.focus();
        return;
      }

      Vue.set(this.productos, index, obj);
      this.$refs["modalCant"].hide();
      this.$refs.barcodeInput.focus();
    },
    setProductoCant(prod) {
      let obj = { ...this.objProd };
      obj.Presentacion = prod.nombre;
      obj.CantidadPresentacion = prod.cantidad;
      obj.precio = 0;
      obj.cantidad = 1;
      obj.EsPresentacion = 1;
      obj.subt = "";
      this.$refs.autocompleteProducto.setValue("");
      obj.Descripcion = prod.nombre;
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj.subtotal = prod.precioc * 1;
      obj = this.onSetProducto(obj, prod.precio, prod.precioc);
      let index = this.productos.findIndex(v => v.IdProducto == obj.IdProducto);

      if (index == -1) {
        console.log("aaa");
        this.add([
          obj,
          () => {
            this.$emit("onAddProducto", obj);
            //this.actualizarProd(obj)
          },
          { setSuma: false, setPrecioCosto: this.tipo == "mov" ? true : false }
        ]);
        this.$refs["modalCant"].hide();
        this.$refs.barcodeInput.focus();
        return;
      }

      Vue.set(this.productos, index, obj);
      this.$refs["modalCant"].hide();
      this.$refs.barcodeInput.focus();
    },
    showPresentacion(obj, index = null) {
      if (obj.PreciosPorProducto !== "" && obj.PreciosPorProducto !== null) {
        this.preciosProd = obj.PreciosPorProducto
          ? JSON.parse(obj.PreciosPorProducto)
          : [];
        this.objProd = obj;
        this.$refs["modalCant"].show();
        return;
      }
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
          <table id="table-render" style="width: 830px;padding:0; margin:0">
            <tbody>
              <tr style="padding:0; margin:0">
                <td style=" text-align: center;
  align-content: center;
  align-items: center;width: 60px" class="${styleStock}"><b>${
        data.stock
      }</b> </td>
                <td style="text-align: center;
  align-content: center;
  align-items: center;width: 60px"><b>${
    data.EsPadre == 1
      ? '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />'
      : data.CodigoBarra
  }</b></td>
                <td style="padding: 0 0 0 15px; width: 200px"><b>${
                  data.Producto
                }</b></td>
                <td style="padding: 0 0 0 15px; width: 100px">${
                  data.ProductoCategoria
                }</td>
                <td style="padding: 0 0 0 15px; width: 100px">${
                  data.ProductoMarca
                }</td>
                <td style=" text-align: center;
  align-content: center;
  align-items: center;padding: 0 15px; width: 65px" class="table-primary"><b>${
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
    },

    // setCantidad (index, val) {
    //   this.$emit('onInputCantidad', val)
    // },
    setNuevoPrecioContado(index) {
      let prod = this.productos[index];

      let nuevoPrecioContado =
        prod.precio / ((100 - prod.PorcentajeUtilidad) / 100) +
        parseFloat(prod.flete);
      prod.subtotal = prod.cantidad * prod.precio;
      prod.igv = prod.TieneIgv == 1 ? prod.precio * prod.cantidad * 0.18 : 0;
      prod.nuevoPrecioContado = parseFloat(nuevoPrecioContado).toFixed(2);
      console.log(prod);
      this.$forceUpdate();
    },
    actualizaDesc(index, prod) {
      //Vue.delete(this.productos[index], 'descuento');
      Vue.set(this.productos[index], "descuento", parseFloat(prod.descuento));
      this.actualizarProd({ Producto: prod, Pos: index });
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

      Vue.set(this.productos[index], "igv", parseFloat(igv).toFixed(2));
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
    },
    abrirMicrofono() {
      this.busqueda = "";
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }
      this.recognition = new webkitSpeechRecognition();
      this.recognition.lang = "es-ES";
      this.recognition.continuous = false;
      this.recognition.interimResults = false;
      this.recognition.onresult = event => {
        if (event.results.length > 0) {
          this.busqueda = event.results[0][0].transcript;
          document.getElementById("autocompleteProducto").focus();
        }
      };

      this.recognition.onerror = event => {
        console.error("Speech Recognition Error:", event);
        this.handleError(event.error);
      };

      this.recognition.onend = async () => {
        const inputEvent = new Event("input", {
          bubbles: true,
          cancelable: true
        });
        let autocompleteInput = document.getElementById("autocompleteProducto");
        setTimeout(() => {
          document.getElementById("autocompleteProducto").value = this.busqueda;
          autocompleteInput.dispatchEvent(inputEvent);
        }, 200);
      };
      this.recognition.start();
    },
    handleError(error) {
      let errorMessage = "";
      if (error === "no-speech") {
        errorMessage =
          "No se detectó ningún discurso. Por favor, inténtalo de nuevo.";
      } else if (error === "audio-capture") {
        errorMessage =
          "No se pudo acceder al micrófono. Asegúrate de que esté correctamente conectado y de que el navegador tenga permisos para usarlo.";
      } else if (error === "not-allowed") {
        errorMessage =
          "Permiso para usar el micrófono denegado. Por favor, habilita el acceso en la configuración de tu navegador.";
      } else {
        errorMessage =
          "Ocurrió un error desconocido. Por favor, inténtalo de nuevo.";
      }
      alert(errorMessage);
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
.form-check-input {
  margin-left: -0.2rem !important;
}
</style>
<style>
#modalImagen .modal-dialog {
  max-width: 75% !important;
}
.imagenGrande {
  width: 100%;
}
#modalCantidad .modal-dialog {
  max-width: 55% !important;
}
.obsequio {
  display: flex;
  justify-content: center;
  justify-items: center;
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
.cameraBarra {
  height: 20em;
  width: auto;
  margin: 0 auto;
}
.cboAgregar {
  border: 1px solid blue;
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
  .ocularMovil {
    display: none;
  }
}
</style>
