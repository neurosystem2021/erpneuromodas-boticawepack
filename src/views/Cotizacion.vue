<template>
  <b-container fluid>
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
                  readonly/>
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
                    <template slot="cantidad" slot-scope="data">
                      <input :id="`cantidad${data.index}`" class="form-control" type="number" v-model="data.item.cantidad">
                    </template>
                    <template slot="precio" slot-scope="data">
                      <input class="form-control" type="number" v-model="data.item.precio">
                    </template>
                    <template slot="descuento" slot-scope="data">
                      <input class="form-control" type="number" v-model="data.item.descuento" 
                        :class="
                        data.item.cantidad * data.item.precio < data.item.descuento ? 'clsError' : ''
                        "
                      >
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
                          parseFloat(
                            (data.item.cantidad ? data.item.cantidad : 0) *
                            (data.item.precio ? data.item.precio : 0)
                          ).toFixed(2)
                        }}

                    </template>
                    <template slot="total" slot-scope="data">
                      {{
                          parseFloat(
                            (data.item.cantidad ? data.item.cantidad : 0) *
                            (data.item.precio ? data.item.precio : 0) - 
                            (data.item.descuento)
                          ).toFixed(2)
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
                <b-button variant="success" class="btnAction" @click="guardarCotizacion">Guardar Cotización</b-button>
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
  </b-container>
</template>

<script>
import config from "@/store/config.js";
import ClientesForm from "@/components/ClientesForm";
import ProductosCarrito from "@/components/ProductosCarrito";
export default {
  components: {
    ClientesForm,
    ProductosCarrito
  },
  data() {
    return {
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
        { key: "PrecioCosto" },
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

        { key: "cantidad" },
        { key: "precio", label: "Precio" },
        {
          key: "subtotal",
          label: "SubTotal",
          variant: "success",
          class: "text-center"
        },
        { key: "descuento", label: "Desc." },
        {
          key: "total",
          label: "Total",
          variant: "danger",
          class: "text-center"
        },
        { key: "Anaquel", label: "Anaquel" },
        { key: "observacion" },
        { key: "cantidadSucursal" },
        { key: "diasEntrega" },
        { key: "cantidadImportacion" },
        { key: "diasEntregaImportacion" },
        // { key: "Anaquel", label: "Anaquel" },

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
      vehiculoFlag: false
    };
  },
  mounted() {
    this.getVendedor();
    this.obtenerTipoCambio();
  },
  methods: {
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
      if (obj.Moneda == "PEN") {
        this.TipoCambio = 1;
      } else {
        this.TipoCambio = this.TipoCambioUSD;
      }
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
      obj.precio = item.PrecioCosto;
      obj.descuento = 0;
      obj.cantidadSucursal = 0;
      obj.diasEntrega = 0;
      obj.cantidadImportacion = 0;
      obj.diasEntregaImportacion = 0;
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
                <td style="width: 60px" class="${styleStock}"><b>${data.stock}</b></td>
                <td style="width: 60px"><b>${data.EsPadre == 1 ? '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />': data.CodigoFrabricante}</b></td>
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
</style>
