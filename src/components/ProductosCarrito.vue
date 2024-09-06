<template>
  <b-row>
    <b-col md="12">
      <b-form-group>
        <b-input-group>
          <b-button @click="abrirModaProductos" variant="info" class="mx-2 btnResponsive" v-if="$store.state.MostrarBotonMultipleEmpresa">Agregar Productos
          </b-button>
          <autocomplete
            ref="autocompleteProducto"
            :url="APIProductos"
            :customParams="{
              sortBy: isVentas ? 'stock' : 'IdProducto',
              sortDesc: 1,
              limit: '25',
              id_empresa: empresa 
            }"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder="Agregar Producto"
            :on-select="setProducto"
            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
            :classes="{ input: 'form-control' }"
          >
          </autocomplete>
          <input
            v-model="loadCode"
            @keydown.enter.prevent="loadBarcode"
            type="text"
            class="form-control"
            placeholder="Codigo de Barra"
          />
          <b-input-group-append>
           
            <b-button variant="primary" @click="loadBarcode"
              ><i class="fas fa-search"></i
            ></b-button>
            <producto-form
              :showButton="showButton"
              :cbGuardar="
                (id, pxp, obj) => {
                  agregarGuardar(id, pxp, obj);
                }
              "
              ref="productoFormEdit"
              :producto-update="productoSelect"
            ></producto-form>
          </b-input-group-append>
        </b-input-group>
      </b-form-group>

      <b-modal size="lg" ref="modalCant" hide-footer hide-header>
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
              @click="setProductoUni(objProd,index)"
            >
              <b-row>
                <b-col md="6" class="nomCart">
                  <h5 class="text-uppercase">UNIDAD</h5>
                </b-col>
                <b-col md="6" class="text-left">
                  <b>Cant: </b>1
                  <br />
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
              v-bind:class="[d.nombre == 'OFERTA' ? 'oferta' : '']"
              @click="
                () => {
                  if (parseFloat(d.cantidad)) {
                    setProductoCant(d,index);
                  }
                }
              "
            >
              <b-row>
                <b-col
                  md="6"
                  class="nomCart"
                  v-bind:class="[d.nombre == 'OFERTA' ? 'ofertanom' : '']"
                >
                  <h5 class="text-uppercase">{{ d.nombre }}</h5>
                </b-col>
                <b-col md="6" class="text-left">
                  <b>Cant: </b>{{ d.cantidad }}
                  <br />
                  <b>Precio: </b>S/ {{ d.precio }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>
        </b-row>
      </b-modal>

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

      <b-table
        small
        responsive
        striped
        hover
        :items="productos"
        :fields="fields"
        ref="tablaCarrito"
        style="margin-top: -12px"
        v-if="mostrarTablaProductos"
      >
        <template slot="eventPres" slot-scope="data">
          <b-button
            size="sm"
            v-if="productos[data.index].estadoPxP == false"
            :disabled="bloquearInput ? true : false"
            variant="info"
            @click="showPresentacion(productos[data.index],data.index)"
            >
            <i class="fas fa-list"></i>
            </b-button>
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

        <template slot="obsequio" slot-scope="data">
          <div class="obsequio" v-if="tipoMov == 0">
            <b-form-checkbox
              button-variant="primary"
              v-model="productos[data.index].EsObsequio"
              :value="1"
              @input="setObsequio(data.index)"
              :unchecked-value="0"
            >
            </b-form-checkbox>
          </div>
        </template>

        <template
          slot="cantxPrecio"
          slot-scope="data"
          v-if="productos[data.index].estadoPxP == false"
        >
          <input
            v-model="productos[data.index].cantxPrecio"
            type="number"
            style="width: 80px"
            step="0.001"
            min="0"
            :disabled="bloquearInput ? true : false"
            @input="
              (val) => changeCant(val.target.value, productos[data.index])
            "
            placeholder="Cantidad"
            class="form-control"
          />
          <span>x{{ productos[data.index].cantidadPres }}</span>
        </template>

        <template slot="cantidad" slot-scope="data">
          <input
            v-model="productos[data.index].cantidad"
            :disabled="
              productos[data.index].estadoPxP == false || bloquearInput
                ? true
                : false || esDelivery
            "
            type="number"
            style="width: 95px"
            class="form-control"
            step="0.001"            
            min="0"
            @input="
              (val) => {
                $emit('onInputCantidad', val.target.value);
               recalcularTotalPorItem(productos[data.index],data.index);
                if (productos[data.index].EsObsequio == 1) {
                  productos[data.index].precio = 0;
                  productos[data.index].nuevoPrecioContado = 0;
                }
              }
            "
            placeholder="Cantidad"
          />
        </template>
        <template slot="precio" slot-scope="data">
          <input
            v-model="productos[data.index].precio"
            :disabled="
              productos[data.index].disabled || bloquearInput
                ? true
                : false ||
                  esDelivery ||
                  (productos[data.index].EsObsequio == 1 && tipoMov == 0
                    ? true
                    : false)
            "
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
            :disabled="
              bloquearInput
                ? true
                : false ||
                  (productos[data.index].EsObsequio == 1 && tipoMov == 0
                    ? true
                    : false)
            "
            @input="setNuevoPrecioContado(data.index)"
            :unchecked-value="0"
          >
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
            v-if="productos[data.index].CodigoBarra != 'MANODEOBRA'"
            :disabled="bloquearInput ? true : false"
            v-model="productos[data.index].EsManoDeObra"
            class="form-check-input"
            type="checkbox"
          />
        </template>

        <template slot="precioVenta" slot-scope="data">
          <input
            :disabled="bloquearInput ? true : false"
            style="width: 95px"
            v-model="productos[data.index].nuevoPrecioContado"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            placeholder="Precio Venta"
          />
          <b-form-text>
            <strike> {{ productos[data.index].normalPrecioContado }} </strike>
          </b-form-text>
        </template>

        <template slot="utilidad" slot-scope="data">
          <input
            :disabled="
              bloquearInput
                ? true
                : false || idPerfil == 1
                ? false
                : true || esDelivery
            "
            class="form-control"
            v-model="productos[data.index].PorcentajeUtilidad"
            style="width: 80px"
            type="number"
            step="0.01"
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
            :disabled="bloquearInput ? true : false || esDelivery"
            v-model="productos[data.index].descuento"
            style="width: 90px"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            value="0"
            @input="actualizarDescu(data.index)"
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

        <template slot="Descripcion" slot-scope="data">
          <textarea
            :disabled="bloquearInput ? true : false"
            class="form-control"
            placeholder="Descripción"
            cols="9"
            rows="1"
            style="width: 150px"
            v-model="productos[data.index].Descripcion"
          ></textarea>
          <!--<b-form-input
            v-model="productos[data.index].Descripcion"
            type="text"
            placeholder="Descripción"></b-form-input>-->
        </template>

        <template slot="total" slot-scope="data">
          {{ getTotal(data.index) }}
          <br />
          <h6><i>Prof.</i></h6>
          <input
            class="form-check-input"
            type="checkbox"
            :disabled="bloquearInput ? true : false"
            @change="
              (e) => {
                $emit('onCheckProf', e);
                asignarDisabledProf(data.index, e);
              }
            "
          />
        </template>
        <template slot="acciones" slot-scope="data">
          <b-button
            :disabled="bloquearInput ? true : false"
            variant="danger"
            size="sm"
            @click="quitarProducto(data.index)"
            ><i class="fas fa-times fa-lg"></i
          ></b-button>
          <!-- <b-button
            style="margin-top: 5px"
            :disabled="bloquearInput ? true : false"
            v-if="idPerfil == 1 ? true : false"
            variant="primary"
            size="sm"
            @click="editarProducto(productos[data.index])"
            ><i class="fas fa-pencil-alt fa-sm"></i
          ></b-button> -->
        </template>

        <template slot="show_details" slot-scope="row">
          <b-button
            :disabled="bloquearInput ? true : false"
            size="sm"
            variant="info"
            @click.stop="row.toggleDetails"
            >></b-button
          >
        </template>
        <!-- <template slot="row-details" slot-scope="row"> </template> -->

        <template slot="stock" slot-scope="data">
          <div v-if="data.item.CodigoBarra == 'COSTODELIVERY'">
            <img src="static/moto-delivery.png" style="width: 24px" />
          </div>
          <div v-else>
            <div>{{ data.item.stock }}</div>
            <img
              v-if="data.item.EsPadre"
              src="static/box.png"
              style="width: 24px"
            />
          </div>
        </template>
      </b-table>
    </b-col>
    <b-col md="3">
      <slot name="operaciones"></slot>
    </b-col>
    <b-col md="3">
      <slot name="metodopago"></slot>
    </b-col>
    <b-col md="3">
      <slot name="totalEmpresa"  v-if="mostrarTablaProductos"></slot>
    </b-col>
    <!-- <b-col md="2">
      <slot name="adicional"></slot>
    </b-col> -->
    <b-col md="3" >
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
          <tr   v-if="mostrarTablaProductos">
            <td><b>SubTotal</b></td>
            <td class="table-danger">{{ sumatoriaSubTotal }}</td>
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
            <td colspan="2">
              <label>pagó con:</label>
              <b-form-input
                type="text"
                :disabled="bloquearInput ? true : false"
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
    <br /><br />
    <b-modal
      size="lg"
      v-model="modalNuevoProductos"
      title="Selección Productos"
      class="modalSelectProduct modal-xl"
    >
      <b-row>
        <b-col cols="12">
          <b-input-group>
            <b-form-input
              v-model="searchProducto"
              placeholder="Buscar..."
              class="form-control inputResponsive"
            ></b-form-input>
            <b-input-group-append>
              <b-button @click="startRecognition()">
                <i class="fas fa-microphone"></i>
              </b-button>
            </b-input-group-append>
            <input
              v-model="IdProducto"
              @keydown.enter.prevent="buscarProductosCodigoBarrasModal"
              type="text"
              class="form-control ml-2  inputResponsive"
              placeholder="Codigo de Barra"
            />
            <b-input-group-append>
              <!--<proveedores-tabla></proveedores-tabla>-->
              <b-button
                variant="primary"
                @click="buscarProductosCodigoBarrasModal"
                ><i class="fas fa-search"></i
              ></b-button>
            </b-input-group-append>
          </b-input-group>
        </b-col>
        <b-col cols="12" slot="modal-footer">
          <div class="w-100 px-2 my-2 derecha">
              <b-button
                variant="primary"
                size="sm"
                class="float-right mx-1"
                @click="anadirarProductos()"
              >
                Aceptar
              </b-button>
              <b-button
                variant="danger"
                size="sm"
                class="float-right mx-1"
                @click="modalNuevoProductos = false"
              >
                Cancelar
              </b-button>
          </div>
        </b-col>
        <b-col cols="12" class="mt-3">
          <table class="table tblProductosModal">
            <thead>
              <tr>
                <!-- <th width="3%"></th> -->
                <th width="15%">Empresa</th>
                <th width="15%">Categoria</th>
                <th width="20%">Marca</th>
                <th width="28%">Producto</th>
                <th width="5%">Stock</th>
                <th width="5%">Stock Mínimo</th>
                <th width="5%">Precio</th>
                <th width="7%">Imagen</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(producto, index) in listadoProductos()" :key="index"
               :class="producto.Selected?'optionSelect' :''" 
                @click="producto.Selected=!producto.Selected" 
                @dblclick="anadirarProductosSeleccionado(producto)">
                <!-- <td width="3%">
                  <b-form-checkbox
                    size="lg"
                    v-model="producto.Selected"
                    class="ml-2"
                  />
                </td> -->
                <td width="15%">{{ producto.NombreEmpresa }}</td>
                <td width="15%">{{ producto.ProductoCategoria }}</td>
                <td width="20%">{{ producto.ProductoMarca }}</td>
                <td width="28%">{{ producto.Producto }}</td>
                <td width="5%">{{ producto.stock }}</td>
                <td width="5%">{{ producto.StockMinimo }}</td>
                <td width="5%">{{ producto.PrecioContado }}</td>
                <!-- <td>{{ producto.Imagen }} -->
                <td width="7%">
                  <img
                    :src="
                      producto.Imagen == null || producto.Imagen == ''
                        ? 'static/no-photo.png'
                        : `/resources/images/productos/${producto.Imagen}`
                    "
                    class="imagen"
                  />
                </td>
              </tr>
            </tbody>
          </table>
          <!-- <b-table
            hover
            :items="listadoProductos()"
            :fields="fieldsNuevoProductos"
            :per-page="perPage"
            :current-page="currentPage"
            small
          >
            <template slot="Selected" slot-scope="data">
              <b-form-checkbox
                size="lg"
                v-model="data.item.Selected"
                class="ml-2"
              />
            </template>
            <template slot="Imagen" slot-scope="data">
              <img
                :src="
                  data.Imagen == null || data.Imagen == ''
                    ? 'static/no-photo.png'
                    : `/resources/images/productos/${data.Imagen}`
                "
                class="imagen"
              />
            </template>
           
          </b-table> 
          <b-pagination
            v-model="currentPage"
            :total-rows="listadoProductos().length"
            :per-page="perPage"
          ></b-pagination>-->
        </b-col>
      </b-row>
      
    </b-modal>
  </b-row>
</template>

<script>
import { mapState, mapActions } from "vuex";
import ProductoForm from "./ProductoForm";
import config from "@/store/config.js";

export default {
  async beforeMount() {
    let empresa = sessionStorage.getItem("empresa");
    if (!empresa) {
      this.empresa = "imperiop2";
      sessionStorage.setItem("empresa", this.empresa);
    } else {
      this.empresa = empresa;
    }
  },
  async mounted() {
    let vm = this;
    vm.lstProductos = [];
    await vm.$store.state.empresas.forEach(async empresa => {
      let url = `${
        vm.API
      }productos/puntoventa/modal/stock/1?q&sortBy=stock&sortDesc=1&id_empresa=${
        empresa.value
      }&limit=`;
      // url = new URL(url);
      let res = await vm.axios(url);
      await res.data.forEach(element => {
        vm.lstProductos.push({
          ...element,
          Empresa: empresa.value,
          Selected: false,
          NombreEmpresa: empresa.text,
          _rowVariant:
            element.stock <= 0
              ? "danger"
              : element.stock < element.StockMinimo
                ? "warning"
                : "success"
        });
      });
    });
  },
  data() {
    return {
      index: null,
      mostrarTablaProductos: true,
      empresa: "",
      IdProducto: "",
      errorMessage: "",
      modalNuevoProductos: false,
      lstProductos: [],
      busqueda: "",
      perPage: 7,
      currentPage: 1,
      searchProducto: "",
      fieldsNuevoProductos: [
        { key: "Selected", label: "" },
        { key: "NombreEmpresa", label: "Empresa" },
        { key: "ProductoCategoria", label: "Categoria" },
        { key: "ProductoMarca", label: "Marca" },
        { key: "Producto", label: "Producto" },
        { key: "stock", label: "stock" },
        { key: "Stock Mínimo", label: "StockMinimo" },
        { key: "PrecioContado", label: "Precio" },
        { key: "Imagen", label: "Imagen" }
      ],
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
      productoUrl: ""
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
    },
    mostrarTablaProductos() {
      
      this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
        .recalcularTotalPorEmpresaFlag;
    }
  },
  props: {
    isVentas: Boolean,
    tipo: String,
    setPrecio: Boolean,
    tipoCambio: Number,
    igvTotal: Boolean,
    tipoMov: Number,
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
          { key: "ProductoPresentacion", label: "Anaquel" },
          // { key: 'ProductoMedicion', label: 'Medición' },
          { key: "obsequio", label: "Obsequio", class: "thcenter" },
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
        sumatoria += parseFloat(value.subtotal) - parseFloat(value.descuento);
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
      actualizarProd: "actualizarProducto"
      // obtenerStok: "obtenerStock",
    }),
    abrirImagen(producto, url) {
      this.productoNombre = producto;
      this.productoUrl = url;
      this.$refs["modalImagen"].show();
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

    loadBarcode(e) {
      e.preventDefault();
      let idProducto = this.loadCode;
      let id_empresa = this.empresa;
      if (idProducto) {
        let params = { codigoBarra: idProducto, id_empresa: id_empresa };
        this.axios
          .get(this.API + "productos/id/" + idProducto, { params: params })
          .then(response => {
            if (response.data.IdProducto) {
              this.setProducto(response.data);
            } else {
              this.loadCode = "";
              this.$toasted.show("No se encontró ningun producto");
            }
          });
      }
    },

    buscarProductosCodigoBarrasModal(e) {
      e.preventDefault();
      this.IdProducto = this.loadCode;
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
    setProducto(obj) {
      let empresa = sessionStorage.getItem("empresa")
        ? sessionStorage.getItem("empresa")
        : "imperiop2";
      this.precioCont = obj.PrecioContado;
      obj.estadoPxP = false;
      this.loadCode = "";
      this.preciosProd = [];
      this.objProd = {};
      obj.EsObsequio = 0;
      obj.EsVenta = this.isVentas;
      if (!obj.Empresa) {
        let datoEmpresa = this.$store.state.empresas.filter(
          v => v.value == empresa
        );
        obj.Empresa = datoEmpresa.value;
        obj.NombreEmpresa = datoEmpresa.text;
      }
      if (!this.isVentas) {
        obj.PreciosPorProducto = "";
      }

      this.preciosProd = JSON.parse(obj.PreciosPorProducto || "[]");
      let fPP = this.preciosProd.filter(
        p => p.cantidad === 0 || p.cantidad === "0"
      );

      if (!obj.PreciosPorProducto || fPP.length === 5) {
        obj.estadoPxP = true;
        this.setProductoUni(obj);
        return;
      }

      this.$refs["modalCant"].show();
      // this.preciosProd = JSON.parse(obj.PreciosPorProducto)
      this.objProd = obj;
    },
    setProductoUni(obj, index = null) {
      let empresa = sessionStorage.getItem("empresa")
        ? sessionStorage.getItem("empresa")
        : "imperiop2";
      if (!obj.Empresa) {
        let datoEmpresa = this.$store.state.empresas.filter(
          v => v.value == empresa
        );
        obj.Empresa = datoEmpresa[0].value;
        obj.NombreEmpresa = datoEmpresa[0].text;
      }

      if (!this.isVentas) {
        obj.PreciosPorProducto = "";
      }
      this.objProd.cantxPrecio = 1;
      this.$refs.autocompleteProducto.setValue("");
      obj.precio = 0;
      obj.cantidad = 0;
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj = this.onSetProducto(obj);

      if (index !== null && index !== undefined) {
        this.$store.state.cart.productos[index] = obj;
        this.$refs["modalCant"].hide();
        this.mostrarTablaProductos = !this.mostrarTablaProductos;
        this.mostrarTablaProductos = !this.mostrarTablaProductos;
      } else {
        this.add([
          obj,
          () => {
            this.$emit("onAddProducto", obj);
            this.$toasted.success("Producto añadido correctamente");
          },
          { setPrecioCosto: this.tipo == "mov" ? true : false },
          false,
          this.cliente.EsVIP
        ]);
        this.$refs["modalCant"].hide();
        obj.cantidadPres = obj.cantidad;
        this.actualizarProd(obj);
      }

      this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
        .recalcularTotalPorEmpresaFlag;
      this.index = null;
    },
    setProductoCant(prod, index = null) {
      this.objProd.cantxPrecio = 1;
      this.cant = prod.cantidad;
      let obj = this.objProd;
      let empresa = sessionStorage.getItem("empresa")
        ? sessionStorage.getItem("empresa")
        : "imperiop2";

      if (!obj.Empresa) {
        let datoEmpresa = this.$store.state.empresas.filter(
          v => v.value == empresa
        );
        obj.Empresa = datoEmpresa[0].value;
        obj.NombreEmpresa = datoEmpresa[0].text;
      }

      this.$refs.autocompleteProducto.setValue("");
      obj.precio = 0;
      obj.cantidad = 0;
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj = this.onSetProducto(obj, prod.cantidad, prod.precio);

      if (index !== null && index !== undefined) {
        
        this.$store.state.cart.productos[index] = obj;
        this.$refs["modalCant"].hide();
        this.mostrarTablaProductos = !this.mostrarTablaProductos;
        this.mostrarTablaProductos = !this.mostrarTablaProductos;
      } else {
        
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
        this.$refs["modalCant"].hide();
        obj.cantidadPres = this.cant;
        this.actualizarProd(obj);
      }

      this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
        .recalcularTotalPorEmpresaFlag;
        this.index = null;
    },
    recalcularTotalPorItem(item, index) {
      let produc = { ...item };
      item.subtotal = produc.cantidad * produc.precio;
      item.total = produc.cantidad * produc.precio;
      this.mostrarTablaProductos = false;

      this.mostrarTablaProductos = !false;
      this.actualizarDescu(index);
      this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
        .recalcularTotalPorEmpresaFlag;

      // this.sumatoriaTotal();
    },
    showPresentacion(obj, index) {
      if (!obj.Empresa) {
        let empresa = this.$store.state.empresa.filter(
          v =>
            v.value ==
            (sessionStorage.getItem("empresa")
              ? sessionStorage.getItem("empresa")
              : "imperiop2")
        );
        obj.Empresa = empresa[0].value;
        obj.NombreEmpresa = empresa[0].text;
      }
      this.index = index;
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
      this.mostrarTablaProductos = !false;
      this.actualizarDescu(index);
      this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
        .recalcularTotalPorEmpresaFlag;
      // this.sumatoriaTotal();
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
    },
    // ZURITA
    async abrirModaProductos() {
      let vm = this;
      vm.searchProducto = "";
      vm.modalNuevoProductos = !vm.modalNuevoProductos;
      vm.$store.state.consideraAppendEmpresa = false;
    },

    listadoProductos() {
      let lst = [];
      let productosFiltrados = this.lstProductos.filter(
        listadoProducto =>
          !this.$store.state.cart.productos.some(
            producto =>
              producto.IdProducto == listadoProducto.IdProducto &&
              producto.Empresa == listadoProducto.Empresa
          )
      );

      if (this.searchProducto) {
        lst = productosFiltrados.filter(
          p =>
            p.Producto.toLowerCase().includes(
              this.searchProducto.toLowerCase()
            ) || !!p.Selected
        );
      } else {
        lst = productosFiltrados;
      }
      //  "PISO BIENVENIDO CAUCHO CHICO"

      if (this.IdProducto) {
        return lst.filter(v => v.CodigoBarra == this.IdProducto);
      } else {
        return lst;
      }
    },
    async anadirarProductosSeleccionado(item) {
      await this.lstProductos.forEach(element => {
        element.Selected = false;
      });

      item.Selected = true;
      delete item._rowVariant;
      await this.guardaProductosEnLaTablaFinal(item);
    },
    async anadirarProductos() {
      await this.lstProductos
        .filter(v => !!v.Selected)
        .forEach(async element => {
          let item = { ...element };

          delete item._rowVariant;
          if (
            !this.productos.some(
              v =>
                v.IdProducto == element.IdProducto &&
                v.empresa == element.empresa
            )
          ) {
            await this.guardaProductosEnLaTablaFinal(item);
          }
        });

      this.modalNuevoProductos = false;
      //
      //this.productos =
    },
    async guardaProductosEnLaTablaFinal(item) {
      this.objProd.cantxPrecio = 1;

      item.subtotal = item.PrecioContado;
      item.cantxPrecio = 1;
      item.estadoPxP = false;

      item.cantidad = 1;
      // itemobj.IdLote = 0
      item.TieneIgv = 0;
      item.descuento = 0;
      item.total = item.PrecioContado;
      this.preciosProd = JSON.parse(item.PreciosPorProducto || "[]");
      let fPP = this.preciosProd.filter(
        p => p.cantidad === 0 || p.cantidad === "0"
      );

      if (!item.PreciosPorProducto || fPP.length === 5) {
        item.estadoPxP = true;
        this.setProductoUni(item);
        return;
      }

      //obj.nuevoPrecioContado = 0;
      let obj = await this.onSetProducto(item);

      this.add([
        obj,
        () => {
          this.$emit("onAddProducto", obj);

          this.$toasted.success("Producto añadido correctamente");
        },
        { setPrecioCosto: this.tipo == "mov" ? true : false },
        false,
        this.cliente.EsVIP
      ]);
      // Vue.set(this.productos, obj.IdProducto, obj)

      obj.cantidadPres = obj.cantidad;
      //this.actualizarProd(obj)

      this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
        .recalcularTotalPorEmpresaFlag;
      this.sumatoriaSubTotal();
    },
    startRecognition() {
      // Verifica si el navegador soporta la Web Speech API
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }

      this.recognition = new webkitSpeechRecognition();
      this.recognition.lang = "es-ES"; // Establece el idioma, en este caso español (España)
      this.recognition.continuous = false; // El reconocimiento no es continuo
      this.recognition.interimResults = false; // No muestra resultados intermedios

      // Maneja los eventos del reconocimiento
      this.recognition.onresult = event => {
        if (event.results.length > 0) {
          this.searchProducto = event.results[0][0].transcript;
        }
      };

      this.recognition.onerror = event => {
        console.error("Speech Recognition Error:", event);
        this.handleError(event.error);
        alert(this.errorMessage);
      };

      this.recognition.onend = () => {
        console.log("Speech Recognition Ended");
      };

      // Inicia el reconocimiento de voz
      this.recognition.start();
    },

    handleError(error) {
      this.errorMessage = "";
      if (error === "no-speech") {
        this.errorMessage =
          "No se detectó ningún discurso. Por favor, inténtalo de nuevo.";
      } else if (error === "audio-capture") {
        this.errorMessage =
          "No se pudo acceder al micrófono. Asegúrate de que esté correctamente conectado y de que el navegador tenga permisos para usarlo.";
      } else if (error === "not-allowed") {
        this.errorMessage =
          "Permiso para usar el micrófono denegado. Por favor, habilita el acceso en la configuración de tu navegador.";
      } else {
        this.errorMessage =
          "Ocurrió un error desconocido. Por favor, inténtalo de nuevo.";
      }
    },
    buscarProductosCodigoBarrasModal(e) {
      e.preventDefault();
      // this.IdProducto = this.IdProducto;
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
.imagen {
  width: 40px !important;
  height: 40px !important;
}
.imagen:hover {
  width: 120px !important;
  height: 120px !important;
}

.table.tblProductosModal {
  width: 100%;
  border-collapse: collapse;
}

.tblProductosModal thead {
  background-color: #f2f2f2;
  position: sticky;
  top: 0;
  z-index: 1; /* Para asegurarse de que el encabezado esté por encima de las filas */
}

.tblProductosModal tbody {
  display: block;
  max-height: 800px; /* Establece la altura máxima para el tbody */
  overflow-y: auto; /* Permite el desplazamiento vertical */
  width: 100%;
}

.tblProductosModal thead tr,
.tblProductosModal tbody tr {
  display: table;
  width: 100%;
  table-layout: fixed;
}
.modalSelectProduct.modal-xl .modal-dialog {
  max-width: 60%;
}

.optionSelect {
  background: #e8f5e9 !important;
}
.derecha {
  align-items: right;
  text-align: right !important;
  align-content: right;
}
.w-100 {
  width: 100% !important;
}
@media only screen and (max-width: 700px) {
  /* For desktop: */
  .ocularMovil {
    display: none;
  }
  .modalSelectProduct.modal-xl .modal-dialog {
    background: red !important;
    max-width: 100%;
    font-size: 0.6rem;
  }
  .btnResponsive {
    max-width: 25%;
    white-space: pre-line;
    padding: 0 !important;
    margin: 0 !important;
  }
  .inputResponsive {
    width: 82% !important;
    margin: 5px 0;
  }
}
</style>
