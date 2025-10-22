<template>
  <b-container fluid>
    <b-modal id="modalImagen" size="lg" ref="modalImagen" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration: underline;">{{productoNombre}}</h3>
        </div>
        <b-row style="display:flex; justify-content:center; align-items:center">
          <b-img :src="productoUrl" class="imagenGrande"></b-img>
        </b-row>
      </b-modal>

     <b-breadcrumb :items="items"></b-breadcrumb>
    <b-tabs pills card>
        <b-tab title="Por Producto" active class="px-5">
          <b-row>
            <b-col cols="4">
            <b-input-group  class="my-2" prepend="Cliente:">
                       <clientes-form txtButton="Editar" v-if="cliente.IdCliente && cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="cliente"></clientes-form>
                          <autocomplete
                              style="width:40%"
                              required="required"
                              ref="inputCliente"
                              :url="API + 'clientes'"
                              anchor="ClienteDniRuc"
                              label="writer"
                              placeholder=""
                              :onInput="(val) => newDniRuc = val"
                              :on-select="setCliente" 
                              :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                              :classes="{input: 'form-control'}">
                          </autocomplete>
                          <b-input-group-append>
                              <clientes-form :ruc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                          </b-input-group-append>
                          <b-input-group-append  v-if="cliente.IdCliente && cliente.DniRuc != '00000000'">
                              <b-button variant="info" @click="verVehiculos"><i class="fa fa-car" aria-hidden="true"></i></b-button>
                          </b-input-group-append>
            </b-input-group>
                       <b-form-select v-if="valDireccion" v-model="CampoDireccion" class="mb-3">
                          <option v-if="cliente.Direccion" :value="cliente.Direccion">{{ cliente.Direccion }}</option>
                          <option v-if="cliente.Direccion2" :value="cliente.Direccion2">{{ cliente.Direccion2 }}</option>
                          <option v-if="cliente.Direccion3" :value="cliente.Direccion3">{{ cliente.Direccion3 }}</option>
                      </b-form-select>
                
            </b-col>
            <b-col cols="2">
             <b-input-group  class="my-2" prepend="Vendedor:">
                <autocomplete
                    required="required"
                    ref="inputVendedor"
                    :url="API + 'usuarios'"
                    anchor="NombreUsuario"
                    label="writer"
                    :on-select="setVendedor" 
                    :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                    :classes="{input: 'form-control'}">
                </autocomplete>
              </b-input-group>
            </b-col>
            <b-col cols="2">
             <b-input-group  class="my-2" prepend="Almacén:">
               
                <autocomplete
                    required="required"
                    ref="inputAlmacen"
                    :url="API + 'almacenes'"
                    anchor="Almacen"
                    initValue="PRINCIPAL"
                    label="writer"
                    placeholder=""
                    :on-select="setAlmacen" 
                    :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                    :classes="{input: 'form-control'}">
                </autocomplete>
             </b-input-group> 
            </b-col>
            <b-col cols="2">
              <b-input-group  class="my-2" prepend="Punto Venta:">
                <autocomplete
                    required="required"
                    ref="inputPuntoVenta"
                    :url="API + 'ventas/puntos'"
                    anchor="PuntoVenta"
                    initValue="PRINCIPAL"
                    label="writer"
                    placeholder=""
                    :on-select="setPuntoVenta" 
                    :onFocus="focusAutocomplete.bind(null, 'inputPuntoVenta')"
                    :classes="{input: 'form-control'}">
                </autocomplete>
              </b-input-group>
            </b-col>
            <b-col cols="2">
              <b-form-checkbox
                  v-model="igv"
                  :value="1"
                  :unchecked-value="0"
                >
                  IGV
              </b-form-checkbox>
             <b-input-group  class="my-2" prepend="Moneda:">
                <autocomplete
                  :url="API + 'monedas'"
                  initValue="PEN"
                  anchor="Moneda"
                  ref="inputMoneda"
                  label="writer"
                  placeholder="Moneda"
                  :on-select="setMoneda"
                  :onFocus="focusAutocomplete.bind(null, 'inputMoneda')"
                  :classes="{input: 'form-control'}">
                </autocomplete>
            </b-input-group>
              <b-input-group  class="my-2" prepend="Tipo Cambio:">
                <input v-model.number="TipoCambio"
                  type="number"
                  class="form-control"
                  step="0.0001"
                  placeholder="Tipo de cambio"
                  />
              </b-input-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col cols="12">
              <b-row>
                <b-col cols="4"> 
                  <b-form-group label="Buscar Por:">
                    <b-form-radio-group
                      v-model="tipoBusqueda"
                      :options="options"
                    ></b-form-radio-group>
                  </b-form-group>
                </b-col>
                <b-col cols="8"> 
                  Buscar Producto:
                  <autocomplete
                    required="required"
                    ref="inputProductos"
                    :url="API + 'busqueda/productos'"
                    anchor="Producto"
                    initValue="Producto"
                    label="writer"
                    placeholder=""
                    :on-select="setProducto" 
                    :onFocus="focusAutocomplete.bind(null, 'inputProductos')"
                     :customParams="{ tipoBusqueda: tipoBusqueda,idAlmacen:almacen.IdAlmacen }"
                    :classes="{input: 'form-control'}"
                    :onShouldRenderChild="renderChild"
                    >
                </autocomplete>
                </b-col>
              </b-row>
            </b-col>
            <b-col cols="12" class="mt-2">
              <div class="clsProductos">
                  <b-table small responsive  striped hover :items="productos" :fields="fields" ref="tablaCarrito">
                    <template slot="img" slot-scope="data">
                      <b-button @click="abrirImagen(data.item.Producto,data.item.Imagen == null || data.item.Imagen==''?'static/no-photo.png': '/resources/images/productos/' + data.item.Imagen)" style="margin-top:2px;" :id="'popover-imagen' + data.item.IdProducto" variant="success" size="sm"><i class="fas fa-image"></i></b-button>	
                       <b-popover  placement="right" :target="'popover-imagen' + data.item.IdProducto" :title="data.item.Producto" triggers="hover">
                          <div style="text-align:center">
                            <b-img style="border-radius: 10px; width:200px" fluid :src="data.item.Imagen == null || data.item.Imagen==''?'static/no-photo.png': '/resources/images/productos/' + data.item.Imagen" alt="" @error="setAltImg" ></b-img>	
                          </div>
                        </b-popover>
                        
                    </template>
                    <template slot="cantidad" slot-scope="data">
                      <input :id="`cantidad${data.index}`" :ref="`cantidad${data.index}`" class="form-control" type="number" v-model="data.item.cantidad">
                    </template>
                    <template slot="precio" slot-scope="data">
                      <input class="form-control" type="number" v-model="data.item.precio" disabled>
                    </template>
                    <template slot="desc" slot-scope="data">
                       <b-input-group  append="%">
                         <input class="form-control" type="number" v-model="data.item.desc" 
                         :class="
                        data.item.cantidad * data.item.precio < data.item.precio * data.item.desc/100  ? 'clsError' : ''
                        "
                      >
                    </b-input-group>
                    </template>
                    <template slot="cantidadSucursal" slot-scope="data">
                      <input class="form-control" type="number" v-model="data.item.cantidadSucursal">
                    </template>
                    <template slot="diasEntrega" slot-scope="data">
                      <input class="form-control" type="number" v-model="data.item.diasEntrega">
                    </template>
                    <template slot="cantidadImportacion" slot-scope="data">
                      <input class="form-control" type="number" v-model="data.item.cantidadImportacion">
                    </template>
                    <template slot="diasEntregaImportacion" slot-scope="data">
                      <input class="form-control" type="number" v-model="data.item.diasEntregaImportacion">
                    </template>
                    <template slot="observacion" slot-scope="data">
                      <b-form-textarea v-model="data.item.observacion" class="txtComentario" /> 
                    </template>
                    <template slot="subtotal" slot-scope="data">
                        {{
                          getSubTotalDet(data.item)
                        }}

                    </template>
                    <template slot="total" slot-scope="data">
                       {{
                          getTotalDet(data.item)
                        }}
                    </template>
                     <template slot="acciones" slot-scope="data">
                       <b-button variant="danger" @click="eliminarProducto(data.index)"><i class="fas fa-trash-alt"></i></b-button>
                     </template>
                    <!-- <template slot="observacion" slot-scope="data"></template> -->
                  </b-table>
               </div>
            </b-col>
            <b-col cols="4" style="text-align: center;">
              <b-input-group  class="my-2" prepend="Garantía:">
                <b-form-textarea v-model="garantia" class="txtComentario" /> 
              </b-input-group>
              <b-input-group  class="my-2" prepend="Observacion:">
                <b-form-textarea v-model="observacion" class="txtComentario" /> 
              </b-input-group>
              <b-input-group  class="my-2" prepend="Peso:">
                <input class="form-control" v-model="peso" type="number" /> 
              </b-input-group>
            </b-col>
            <b-col cols="4" style="text-align: center; align-content: center;align-items: center;">
                <b-button-group class="ocularMovil">
                  <b-button variant="success" class="btnAction" @click="guardarCotizacion">Guardar Cotización</b-button>
                </b-button-group>

                <b-button-group size="sm">
                  <proformas :idAlmacen="1" class="btnAction"  @onSelectProforma="cargarProforma"></proformas>
                </b-button-group>
            </b-col>
            <b-col cols="4" style="text-align: center; align-content: center;align-items: center;">
               <table class="table b-table border table-footer" style="font-size: 0.8rem !important;">
                <tr>
                  <td class="table-info" style="text-align: left; font-weight:bold">Total Cantidad</td>
                  <td>
                   {{ moneda.Moneda }}  {{ getTotalCantidad() }}
                  </td>
                </tr>
                <tr>
                  <td class="table-warning" style="text-align: left; font-weight:bold">Total Sucursal</td>
                  <td>
                   {{ moneda.Moneda }}  {{ getTotalSucursal() }}
                  </td>
                </tr>
                <tr>
                  <td class="table-danger" style="text-align: left; font-weight:bold">Total Importacion</td>
                  <td>
                   {{ moneda.Moneda }}  {{ getTotalImportacion() }}
                  </td>
                </tr>
                <tr>
                  <td class="" style="background: #FFF59D; text-align: left; font-weight:bold">SubTotal </td>
                  <td style="font-weight: bold;">
                   {{ moneda.Moneda }}  {{ getSubTotal() }}
                  </td>
                </tr>
                
                <tr>
                  <td  style="text-align: left; font-weight:bold">Descuentos </td>
                  <td style="">
                     -{{ moneda.Moneda }}  {{ getTotalDescuentos() }}
                  </td>
                </tr>
                <tr>
                  <td class="" style="background: #E1BEE7; text-align: left; font-weight:bold">IGV </td>
                  <td style="font-weight: bold;">
                   {{ moneda.Moneda }}  {{ getTotalIGV() }}
                  </td>
                </tr>
                <tr>
                  <td class="table-success" style="text-align: left; font-weight:bold">Total </td>
                  <td style="font-weight: bold;">
                   {{ moneda.Moneda }}  {{ getTotal() }}
                  </td>
                </tr>
              </table>
            </b-col>
          </b-row>
        </b-tab>
    </b-tabs>
    
    <b-modal v-model="modalSeleccionarProducto" id="modalProducto" size="lg" hide-footer title="Seleccionar Producto" no-close-on-backdrop no-close-on-esc>
        <b-table :items="lstProducto" :fields="fieldsProd">
            <template slot="action" slot-scope="data">
                <b-button variant="success" @click="anadirCarrito(data.item)"> <i class="fa fa-plus-circle" aria-hidden="true"></i> </b-button>
            </template>
        </b-table>
    </b-modal>
    <b-modal v-model="vehiculoFlag" size="lg" class="modalVehiculo" id="modalProducto">
      <div style="text-align: right;" class="my-2">
        <b-button variant="success" @click="abrirModalNuevoRegistro"> Nuevo</b-button>
      </div>
      <b-table :items="lstVehiculos" :fields="fieldsVehiculo">

                <template slot="comentario" slot-scope="data">
                    <b-button variant="info" @click="data.item.mostrarComentario=!data.item.mostrarComentario" v-if="!data.item.mostrarComentario"> <i class="fa fa-eye" aria-hidden="true"></i> </b-button>
                    <b-button variant="secondary" @click="data.item.mostrarComentario=!data.item.mostrarComentario" v-else> <i class="fa fa-eye-slash" aria-hidden="true"></i> </b-button>
                    <p v-if="data.item.mostrarComentario">
                       {{ data.item.comentario }} 
                    </p>
                    
                </template>
                <!-- <template slot="action" slot-scope="data">
                    <b-button variant="warning" @click="abrirModalActualizar(data.item)">
                        <i class="fas fa-pencil-alt"></i>
                    </b-button>
                    <b-button variant="danger" @click="eliminarVehiculo(data.item)">
                        <i class="fas fa-trash-alt"></i>
                    </b-button>
                </template> -->

            </b-table>
    </b-modal>

        <b-modal v-model="nuevoRegistroFlag" size="lg" hide-footer title="Vehículo" no-close-on-backdrop no-close-on-esc>
        <b-row>
            <b-col cols="12" md="6">
                <b-input-group prepend="Nombre Máquina" class="my-2">
                    <b-form-input :class="nombreError ? 'clsRequerido' :''" v-model="formVehiculo.nombre"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Marca Máquina" class="my-2">
                    <b-form-input :class="marcaError ? 'clsRequerido' :''" v-model="formVehiculo.marca"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Modelo Máquina" class="my-2">
                    <b-form-input :class="modeloError ? 'clsRequerido' :''" v-model="formVehiculo.modelo"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Serie Máquina" class="my-2">
                    <b-form-input :class="serieError ? 'clsRequerido' :''" v-model="formVehiculo.serie"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Horas" class="my-2">
                    <b-form-input :class="horasError ? 'clsRequerido' :''" v-model="formVehiculo.horas" type="number"></b-form-input>
                </b-input-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <hr>
            </b-col>
        </b-row>
        <b-row>
            <b-col cols="12" md="6">
                <b-input-group prepend="Motor del Vehículo" class="my-2">
                    <b-form-input :class="motorError ? 'clsRequerido' :''" v-model="formVehiculo.motor"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Serie del Motor" class="my-2">
                    <b-form-input :class="serieMotorError ? 'clsRequerido' :''" v-model="formVehiculo.serieMotor"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Año de Fab. Motor" class="my-2">
                    <b-form-input :class="aniomotorError ? 'clsRequerido' :''"  type="number" min="1800" max="2100" v-model="formVehiculo.aniomotor"></b-form-input>
                </b-input-group>
            </b-col>
            <!-- <b-col cols="12" md="6">
                <b-input-group prepend="Carga Bruta del Motor " class="my-2">
                    <b-form-input v-model="formVehiculo.cargaBruta" type="number"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Carga Neta del Motor " class="my-2">
                    <b-form-input v-model="formVehiculo.cargaNeta" type="number"></b-form-input>
                </b-input-group>
            </b-col> -->
            <b-col cols="12">
                <b-input-group prepend="Comentario " class="my-2">
                     <b-form-textarea :class="comentarioError ? 'clsRequerido' :''" v-model="formVehiculo.comentario" class="txtComentario"/>
                </b-input-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col cols="12" style="text-align: right !important; align-items: right !important; align-content: right !important;">
                    <b-button variant="success" v-if="nuevoFlag" @click="guardarVehiculo">Guardar</b-button>
                    <b-button variant="success" v-if="actualizarFlag" @click="actualizarVehiculo">Actualizar</b-button>
                    <b-button variant="danger" @click="nuevoRegistroFlag=!nuevoRegistroFlag">Cancelar</b-button>              
            </b-col>
        </b-row>
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
import ClientesForm from "@/components/ClientesForm";
import ProductosCarrito from "@/components/ProductosCarrito";
import Proformas from "@/components/ProformaLista.vue";
export default {
  components: {
    ClientesForm,
    ProductosCarrito,
    Proformas
  },
  data() {
    return {
      IdProforma: null,
      igv: 1,
      nuevoFlag: false,
      actualizarFlag: false,
      nombreError: false,
      marcaError: false,
      modeloError: false,
      serieError: false,
      horasError: false,
      motorError: false,
      serieMotorError: false,
      aniomotorError: false,
      comentarioError: false,
      modalSeleccionarProducto: false,
      tipoBusqueda: 1,
      items: [
        {
          text: "Cotización",
          active: true
        }
      ],
      options: [
        { text: "Código Fabricante", value: 1 },
        { text: "Descripción", value: 2 },
        { text: "Código Interno", value: 3 }
        // { text: "This is the 4th radio", value: { fourth: 4 } }
      ],
      fieldsProd: [
        { key: "CodigoFrabricante" },
        { key: "CodigoInterno" },
        { key: "Producto" },
        { key: "PrecioContado", label: "Precio" },
        { key: "action", label: "" }
      ],
      fields: [
        //{ key: 'manoobra', label: 'Mano de obra', class:'text-center' },
        {
          key: "stock",
          label: "Stock",
          variant: "info",
          class: "text-center"
        },
        { key: "CodigoFrabricante" },
        { key: "Producto" },
        // { key: "ProductoModelo", label: "Modelo" },
        { key: "ProductoMarca", label: "Marca" },
        { key: "ProductoMedicion", label: "U. M." },

        { key: "img", label: "" },
        { key: "cantidad" },
        { key: "precio", label: "Precio" },
        { key: "Anaquel", label: "Anaquel" },
        { key: "observacion" },
        { key: "cantidadSucursal" },
        { key: "diasEntrega" },
        { key: "cantidadImportacion" },
        { key: "diasEntregaImportacion" },
        { key: "desc", label: "Desc (%)" },
        // { key: "Anaquel", label: "Anaquel" },
        {
          key: "subtotal",
          label: "SubTotal",
          variant: "success",
          class: "text-center"
        },
        {
          key: "total",
          label: "Total",
          variant: "danger",
          class: "text-center"
        },

        //{ key: 'utilidad', label: 'Porcentaje Utilidad %' },

        // { key: "descMax" },

        { key: "acciones", label: "" }
      ],
      fieldsVehiculo: [
        { key: "nombre", label: "Nombre Máquina" },
        { key: "marca", label: "Marca Vehículo" },
        { key: "modelo", label: "Modelo Máquina" },
        { key: "serie", label: "Serie Máquina" },
        { key: "horas" },
        { key: "motor", label: "Modelo Vehículo" },
        { key: "seriemotor", label: "Serie Motor" },
        { key: "aniomotor", label: "Año Fabricación" },
        // { key: "cargabruta", label: "Carga Bruta" },
        // { key: "carganeta", label: "Carga Neta" },
        { key: "comentario", label: "Comentarios" }
        // { key: "action", label: "" }
      ],
      lstProducto: [],
      API: config.API,
      dni: "",
      newDniRuc: "",
      CampoDireccion: "",
      cliente: {},
      vendedor: {},
      almacen: {},
      puntoVenta: {},
      moneda: {
        IdMoneda: 1,
        Moneda: "PEN"
      },
      productos: [],
      TipoCambio: 1,
      garantia: "De Fabrica",
      observacion: "",
      peso: 0,
      TipoCambioUSD: 1,
      lstVehiculos: [],
      vehiculoFlag: false,
      formVehiculo: {
        nombre: "",
        marca: "",
        modelo: "",
        serie: "",
        horas: "",
        motor: "",
        serieMotor: "",
        cargaBruta: "",
        cargaNeta: "",
        comentario: "",
        aniomotor: ""
      },
      nuevoRegistroFlag: false,
      productoNombre: "",
      productoUrl: ""
    };
  },
  mounted() {
    this.getVendedor();
    this.obtenerTipoCambio();
  },
  methods: {
    getSubTotalDet(item) {
      let subTotal =
        (parseFloat(item.cantidad ? item.cantidad : 0) +
          parseFloat(item.cantidadSucursal ? item.cantidadSucursal : 0) +
          parseFloat(item.cantidadImportacion ? item.cantidadImportacion : 0)) *
        (item.precio ? item.precio : 0);
      return parseFloat(subTotal).toFixed(2);
    },
    getTotalDet(item) {
      let subTotal =
        (parseFloat(item.cantidad ? item.cantidad : 0) +
          parseFloat(item.cantidadSucursal ? item.cantidadSucursal : 0) +
          parseFloat(item.cantidadImportacion ? item.cantidadImportacion : 0)) *
        (item.precio ? item.precio : 0);
      let descuento = (subTotal * (item.desc ? item.desc : 0)) / 100;
      let index = this.productos.findIndex(
        v => v.IdProducto == item.IdProducto
      );

      Vue.set(this.productos[index], "descuento", descuento);
      return parseFloat(
        subTotal - (subTotal * (item.desc ? item.desc : 0)) / 100
      ).toFixed(2);
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    cargarProforma(event) {
      this.IdProforma = event.IdProforma;
      this.igv = event.IGV;
      console.log("cargarProforma", event);
      this.setCliente({
        IdCliente: event.IdCliente,
        Cliente: event.Cliente
      });
      this.moneda = {
        Moneda: event.IdMoneda,
        Moneda: event.IdMoneda
      };

      this.$refs.inputCliente.setValue(event.Cliente);
      this.$refs.inputMoneda.setValue(event.IdMoneda);
      this.TipoCambio = event.TipoCambio;
      this.productos = [];
      event.productos.forEach(async prod => {
        let p = { ...prod };
        let stock = await this.axios(
          `${this.API}/productos/puntoventa/stock/${this.almacen.IdAlmacen}`,
          {
            params: {
              idProductos: [prod.IdProducto]
            }
          }
        ).then(response => {
          if (response.data) {
            return response.data[0].stock;
          }
          return 0;
        });

        prod.stock = stock;
        prod.cantidadImportacion = p.CantidadImportacion;
        prod.cantidadSucursal = p.CantidadSucursal;
        prod.diasEntrega = p.DiasEntrega;
        prod.cantidadImportacion = p.CantidadImportacion;
        prod.diasEntregaImportacion = p.DiasEntregaImportacion;
        this.productos.unshift(prod);
        this.$nextTick(() => {
          setTimeout(() => {
            let cantidad = document.getElementById("cantidad0");
            if (cantidad) {
              cantidad.focus();
              cantidad.select();
            }
          }, 100);
        });
      });
      // this.productos = event.productos;
    },
    abrirImagen(producto, url) {
      this.productoNombre = producto;
      this.productoUrl = url;
      this.$refs["modalImagen"].show();
    },
    abrirModalNuevoRegistro() {
      this.nuevoFlag = true;
      this.actualizarFlag = false;
      this.formVehiculo = {
        nombre: "",
        marca: "",
        modelo: "",
        serie: "",
        horas: "",
        motor: "",
        serieMotor: "",
        cargaBruta: "",
        cargaNeta: "",
        aniomotor: "",
        comentario: ""
      };
      this.nombreError = false;
      this.marcaError = false;
      this.modeloError = false;
      this.serieError = false;
      this.horasError = false;
      this.motorError = false;
      this.serieMotorError = false;
      this.comentarioError = false;
      this.aniomotorError = false;
      this.nuevoRegistroFlag = true;
    },
    validarFormulario() {
      this.nombreError = false;
      this.marcaError = false;
      this.modeloError = false;
      this.serieError = false;
      this.horasError = false;
      this.motorError = false;
      this.serieMotorError = false;
      this.aniomotorError = false;
      this.comentarioError = false;
      let val = false;
      if (!this.formVehiculo.marca) {
        this.nombreError = true;
        val = true;
      }
      if (!this.formVehiculo.marca) {
        this.marcaError = true;
        val = true;
      }
      if (!this.formVehiculo.modelo) {
        this.modeloError = true;
        val = true;
      }
      if (!this.formVehiculo.serie) {
        this.serieError = true;
        val = true;
      }
      if (!this.formVehiculo.horas) {
        this.horasError = true;
        val = true;
      }
      if (!this.formVehiculo.motor) {
        this.motorError = true;
        val = true;
      }
      if (!this.formVehiculo.serieMotor) {
        this.serieMotorError = true;
        val = true;
      }
      if (!this.formVehiculo.aniomotor) {
        this.aniomotorError = true;
        val = true;
      }
      if (!this.formVehiculo.comentario) {
        this.comentarioError = true;
        val = true;
      }
      return val;
    },
    async guardarVehiculo() {
      let res = this.validarFormulario();
      if (res) {
        return;
      }
      let data = { ...this.cliente, ...this.formVehiculo };
      await this.axios.post(`${this.API}vehiculo`, data);
      this.verVehiculos();
      this.nuevoRegistroFlag = false;
    },
    async verVehiculos() {
      this.lstVehiculos = [];
      await this.axios(`${this.API}cliente/vehiculo`, {
        params: this.cliente
      }).then(res => {
        if (res.data) {
          let data = res.data;
          data = data.map(item => {
            return { ...item, mostrarComentario: false };
          });
          this.lstVehiculos = data;
          this.vehiculoFlag = true;
        }
      });
    },
    async obtenerTipoCambio() {
      let tipoCambio = await this.axios(
        "https://deperu.com/api/rest/cotizaciondolar.json"
      );
      this.TipoCambioUSD = tipoCambio.data.Cotizacion[0].Compra;
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    setCliente(obj, dni = false) {
      this.cliente = obj;
    },
    setVendedor(obj) {
      // console.log(obj)
      if (obj.IdTipoDoc) this.getTipoDoc(obj.IdTipoDoc);

      this.vendedor = obj;
      this.loadAlmacen(obj.IdAlmacen);
      this.loadPuntoVenta(obj.IdDocVentaPuntoVenta);
    },
    setAlmacen(obj) {
      this.almacen = obj;
      this.$refs.inputAlmacen.setValue(obj.Almacen);
      // this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen });
    },
    setMoneda(obj) {
      this.TipoCambio = obj.TipoCambio;
      this.moneda = obj;
      this.$refs.inputMoneda.setValue(obj.Moneda);
    },
    // ---------------------------------
    getVendedor() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    loadAlmacen(id) {
      this.axios.get(this.API + "almacenes/id/" + id).then(res => {
        this.setAlmacen(res.data);
      });
    },
    loadPuntoVenta(id) {
      this.axios.get(this.API + "puntoventa/id/" + id).then(res => {
        this.setPuntoVenta(res.data);
        // this.getNumeroVenta();
      });
    },
    setPuntoVenta(obj) {
      this.puntoVenta = obj;
      this.$refs.inputPuntoVenta.setValue(obj.PuntoVenta);
      // this.getNumeroVenta();
    },
    async setProducto(obj) {
      let res = await this.axios(`${this.API}/busqueda/productos/id`, {
        params: obj
      });
      console.log((this.lstProducto = res.data));
      this.modalSeleccionarProducto = true;
    },
    async anadirCarrito(item) {
      if (this.productos.some(v => v.IdProducto == item.IdProducto)) {
        return;
      }
      let obj = { ...item };
      console.log(this.almacen);
      let res = await this.axios(
        `${this.API}productos/puntoventa/stock/${this.almacen.IdAlmacen}`,
        {
          params: {
            idProductos: [item.IdProducto]
          }
        }
      );
      obj.stock = res.data[0].stock;
      obj.cantidad = 1;
      obj.precio =
        this.TipoCambio == 1 || this.TipoCambio == 0
          ? parseFloat(item.PrecioContado).toFixed(2)
          : parseFloat(item.PrecioContado / this.TipoCambio).toFixed(2);
      obj.desc = 0;
      obj.descuento = 0;
      obj.cantidadSucursal = 0;
      obj.diasEntrega = 0;
      obj.cantidadImportacion = 0;
      obj.diasEntregaImportacion = 0;
      obj.precio = parseFloat(
        obj.precio * (1 + (this.igv == 1 ? 0.18 : 0))
      ).toFixed(2);
      this.productos.unshift(obj);
      this.$nextTick(() => {
        setTimeout(() => {
          document.getElementById("cantidad0").focus();
          document.getElementById("cantidad0").select();
        }, 100);
      });
    },
    eliminarProducto(index) {
      this.productos.splice(index, 1);
    },
    async guardarCotizacion() {
      if (!(Object.keys(this.cliente).length > 0)) {
        this.$toasted.error("Seleccione un Cliente. Verifique.", {
          duration: 2000
        });
        return;
      }
      if (!(Object.keys(this.vendedor).length > 0)) {
        this.$toasted.error("Seleccione un Vendedor. Verifique.", {
          duration: 2000
        });
        return;
      }
      if (!(Object.keys(this.almacen).length > 0)) {
        this.$toasted.error("Seleccione un Almacén. Verifique.", {
          duration: 2000
        });
        return;
      }
      if (!(Object.keys(this.puntoVenta).length > 0)) {
        this.$toasted.error("Seleccione un Punto de Venta. Verifique.", {
          duration: 2000
        });
        return;
      }
      if (!(Object.keys(this.moneda).length > 0)) {
        this.$toasted.error("Seleccione una Moneda. Verifique.", {
          duration: 2000
        });
        return;
      }
      if (this.productos.length == 0) {
        this.$toasted.error(
          "No existen Productos para la cotización. Verifique.",
          {
            duration: 2000
          }
        );
        return;
      }

      if (this.productos.some(v => v.cantidad < 0)) {
        this.$toasted.error(
          "No puede vender un producto con 0 o menos, cantidad. Verifique.",
          {
            duration: 2000
          }
        );
        return;
      }
      if (
        this.productos.some(
          v =>
            (v.stock < 0 && !(v.cantidadSucursal || v.cantidadImportacion)) ||
            (v.stock < v.cantidad &&
              !(v.cantidadSucursal || v.cantidadImportacion))
        )
      ) {
        this.$toasted.error(
          "No puede vender un producto sin stock, sin que seleccione Cantidad en otra sucursal o Importación , cantidad. Verifique.",
          {
            duration: 2000
          }
        );
        return;
      }

      let data = {
        IdProforma: this.IdProforma,
        IdCliente: this.cliente.IdCliente,
        IdVendedor: this.vendedor.Usuario,
        IdAlmacen: this.almacen.IdAlmacen,
        IdPuntoVenta: this.puntoVenta.IdDocVentaPuntoVenta,
        IdMoneda: this.moneda.Moneda,
        TipoCambio: this.TipoCambio,
        Garantia: this.garantia,
        Observacion: this.observacion,
        Direccion: this.CampoDireccion,
        peso: this.peso,
        igv: this.igv,
        detalle: this.productos
      };
      await this.axios.post(`${this.API}cotizacion`, data).then(res => {
        if (res.data) {
          window.open(
            "/nuevo/index.html#/reportes/proforma/" + res.data,
            "_blank"
          );
          this.$swal({
            icon: "success",
            title: "Registro Correcto",
            allowOutsideClick: false,
            allowEscapeKey: false
          }).then(res => {
            if (res.isConfirmed) {
              window.location.reload();
            }
          });
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
                <th>Stock</th>
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
                <td style="width: 60px" class="${styleStock}"><b>${
        data.stock
      }</b></td>
                <td style="width: 60px"><b>${
                  data.EsPadre == 1
                    ? '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />'
                    : data.CodigoFrabricante
                }</b></td>
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
    },
    getTotalCantidad() {
      let total = this.productos.reduce((sum, prod) => {
        return sum + parseFloat(prod.precio * prod.cantidad);
      }, 0);
      return parseFloat(total).toFixed(2);
    },
    getTotalSucursal() {
      let total = this.productos.reduce((sum, prod) => {
        return sum + parseFloat(prod.precio * prod.cantidadSucursal);
      }, 0);
      return parseFloat(total).toFixed(2);
    },
    getTotalImportacion() {
      let total = this.productos.reduce((sum, prod) => {
        return sum + parseFloat(prod.precio * prod.cantidadImportacion);
      }, 0);
      return parseFloat(total).toFixed(2);
    },
    getTotalIGV() {
      let total = this.productos.reduce((sum, prod) => {
        return (
          sum +
          (parseFloat(prod.cantidad) +
            parseFloat(prod.cantidadSucursal) +
            parseFloat(prod.cantidadImportacion)) *
            prod.precio -
          parseFloat(prod.descuento)
        );
      }, 0);
      return parseFloat(total * (this.igv == 1 ? 0.18 : 0)).toFixed(2);
    },
    getSubTotal() {
      let total = this.productos.reduce((sum, prod) => {
        return (
          sum +
          parseFloat(prod.precio * prod.cantidad) +
          parseFloat(prod.precio * prod.cantidadSucursal) +
          parseFloat(prod.precio * prod.cantidadImportacion)
        );
      }, 0);
      return parseFloat(total).toFixed(2);
    },
    getTotalDescuentos() {
      let total = this.productos.reduce((sum, prod) => {
        return sum + parseFloat(prod.descuento ? prod.descuento : 0);
      }, 0);
      return parseFloat(total).toFixed(2);
    },
    getTotal() {
      let total = this.productos.reduce((sum, prod) => {
        return (
          sum +
          (parseFloat(prod.cantidad) +
            parseFloat(prod.cantidadSucursal) +
            parseFloat(prod.cantidadImportacion)) *
            prod.precio -
          parseFloat(prod.descuento)
        );
      }, 0);
      total = total;
      return parseFloat(total).toFixed(2);
    }
  },
  computed: {
    valDireccion() {
      let val = false;
      if (Object.keys(this.cliente).length > 0) {
        if (
          this.cliente.Direccion ||
          this.cliente.Direccion2 ||
          this.cliente.Direccion3
        ) {
          return true;
        }
      }
      return val;
    }
  },
  watch: {
    TipoCambio() {
      if (this.productos.length > 0) {
        this.productos.forEach((prod, index) => {
          const nuevoPrecio =
            this.TipoCambio == 1 || this.TipoCambio == 0
              ? Number(parseFloat(prod.PrecioContado).toFixed(2))
              : Number((prod.PrecioContado / this.TipoCambio).toFixed(2));

          this.$set(this.productos[index], "precio", nuevoPrecio);
        });
      }
    },
    igv() {
      this.productos.forEach((prod, index) => {
        const nuevoPrecio =
          prod.PrecioContado * (1 + (this.igv == 1 ? 0.18 : 0));
        this.$set(
          this.productos[index],
          "precio",
          parseFloat(nuevoPrecio).toFixed(2)
        );
      });
    }
  }
};
</script>

<style>
#modalProducto .modal-dialog {
  /* min-width: 80% !important; */
}
.txtComentario {
  field-sizing: content;
}
.clsError {
  border: 1px solid #d50000;
}

.clsProductos {
  padding: 0 1%;
  border-radius: 25px;
  border: 1px solid #e0e0e0;
  min-height: 320px;
  max-height: 320px;
  overflow-y: scroll;
  margin: 0 0 2%;
}
#modalProducto .modal-dialog {
  min-width: 80% !important;
}
.tblTotal {
  border: 1px solid #;
}
</style>
