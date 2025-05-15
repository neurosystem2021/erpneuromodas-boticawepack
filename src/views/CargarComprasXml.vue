<template>
    <b-container fluid>
      <b-row>

      
      <b-col md="8">
        <b-row>
          <b-col md="4">
            <b-form-group>
              <label>Tipo de Movimiento:</label>
              <autocomplete
                required="required"
                ref="inputMovimiento"
                :url="API + 'movimientos/tipos'"
                anchor="TipoMovimiento"
                label="writer"
                placeholder=""
                :on-select="setTipoMovimiento"
                :onFocus="focusAutocomplete.bind(null, 'inputMovimiento')"
                :classes="{input: 'form-control'}">
              </autocomplete>
            </b-form-group>
          </b-col>
          <b-col md="4">
                    <b-form-group>
                      <label>Proveedor:</label>
                      <input type="text" :value="Proveedor" class="form-control" readonly>
                    </b-form-group>
            </b-col>

            <b-col md="4">
                    <b-form-group>
                      <label>Periodo Tributario:</label>
                      <b-form-input v-model="movimiento.FechaPeriodoTributario"
                        type="text"
                        readonly="readonly"
                        placeholder="Periodo Tributario"></b-form-input>
                    </b-form-group>
            </b-col>

            <b-col md="4" v-if="movimiento.movimientoTipo.Tipo == 1 || movimiento.movimientoTipo.Tipo == 2">
              <b-form-group>
                <label>Almacen Origen:</label>
                <autocomplete
                  ref="inputAlmacenOrigen"
                  required="required"
                  initValue=""
                  :url="API + 'almacenes'"
                  anchor="Almacen"
                  label="writer"
                  placeholder=""
                  :on-select="setAlmacenOrigen"
                  :onFocus="focusAutocomplete.bind(null, 'inputAlmacenOrigen')"
                  :classes="{input: 'form-control'}">
                </autocomplete>
              </b-form-group>
            </b-col>

            <b-col md="4" v-if="movimiento.movimientoTipo.Tipo == 0 || movimiento.movimientoTipo.Tipo == 2">
                    <b-form-group>
                      <label>Almacen Destino:</label>
                      <autocomplete
                        ref="inputAlmacenDestino"
                        required="required"
                        initValue=""
                        :url="API + 'almacenes'"
                        anchor="Almacen"
                        label="writer"
                        placeholder=""
                        :on-select="setAlmacenDestino"
                        :onFocus="focusAutocomplete.bind(null, 'inputAlmacenDestino')"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>

            <b-col cols="12" class="mb-3" v-if="activarCargarXML">
                Cargar XML
                <b-form-file ref="fileInput" @change="leerXML" accept=".xml" browse-text="Cargar Archivo xml"> </b-form-file>
            </b-col>
            
            
            
        </b-row>
      </b-col>
     <b-col md="4">
                <b-row>
                  <b-col md="4">
                    <b-form-group>
                      <label>Serie:</label>                    
                      <b-form-input v-model="movimiento.Serie"
                        required="required"
                        @input="cargarNumero"
                        type="text"
                        ref="txtcargarNumero"

                        placeholder=""></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Número:</label>                    
                      <b-form-input v-model="movimiento.Numero"
                        required="required"
                        type="text"
                        placeholder=""></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Moneda:</label>
                      <autocomplete
                        ref="inputMoneda"
                        :url="API + 'monedas'"
                        initValue="USD"
                        anchor="Moneda"
                        label="writer"
                        placeholder="Moneda"
                        :on-select="setMoneda"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                  <b-col md="8">
                    <b-form-group>
                      <label>Fecha Contable:</label>
                      <b-form-input
                        v-model="movimiento.MovimientoFecha"
                        type="datetime-local"
                        step="1"
                        placeholder="Fecha Contable"
                        ></b-form-input>

                    </b-form-group>
                    <b-form-group>
                      <label>Fecha Stock:</label>
                      <b-form-input
                        v-model="movimiento.FechaStock"
                        type="date"
                        placeholder="Fecha Stock"
                        ></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Tipo de cambio:</label>
                      <input v-model.number="movimiento.TipoCambio"
                        type="number"
                        class="form-control"
                        step="0.0001"
                        placeholder="Tipo de cambio"/>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-col>

              <b-col cols="12" v-if="mostrarAnadirProductos">
                <span>Recuerde solo añadir, en caso reconozca un producto y no lo haya podido encontrar el sistema</span>
                <b-input-group>

                
                  <autocomplete
                  ref="autocompleteProducto"
                  :url="APIProductos"
                  :customParams="{ 'sortBy':isVentas?'stock':'IdProducto', 'sortDesc': 1 ,limit: '25' }"
                  :onShouldRenderChild="renderChild"
                  anchor="Producto"
                  label="writer"
                  placeholder="Agregar Producto"
                  :on-select="setProducto"
                  :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                  id="txtAddProduct"
                  @keydown.native.prevent.tab="irtxtBarcode"
                  :classes="{input: 'form-control'}">
                </autocomplete>
                <input 
                  id="txtBarcode"
                  v-model="loadCode"
                  @keydown.prevent.tab="irtxtAddProduct"
                  @keydown.enter.prevent="loadBarcode"
                  type="text"
                  class="form-control"
                  placeholder="Codigo de Barra" />
                  <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
                </b-input-group>
              </b-col>

              <b-col cols="12" class="mt-2">
               <table width="100%" class="table">
                <thead>
                    <tr>
                        <th>Stock</th>
                        <th>Cod. Barra</th>
                        <th>Artículo</th>
                        <th>Marca</th>
                        <th></th>
                        <th>Presentación</th>
                        
                        <th style="text-align: center;">Cantidad x Pres</th>
                        <th style="text-align: center;">Cantidad</th>
                        <th style="text-align: center;">P/ U</th>
                       
                        <th style="text-align: center;">Precio Venta </th>
                        <th style="text-align: center;">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                  <tr v-for="(producto,index) in lstProductos" :key="index" :style="`background: ${producto.background};`">
                    <td> {{producto.stock }}</td>
                    <td class="px-2" > {{producto.CodigoBarra}} </td>
                    <td class="px-2" >{{ producto.Producto }}</td>
                    <td class="px-2" >
                      <span >
                        {{ producto.ProductoMarca }}
                      </span>

                      <!-- <autocomplete v-else
                        required="required"
                        :url="API + 'marcas'"
                        :ref="`inputMarca${index}`"
                        anchor="ProductoMarca"
                        label="writer"
                        :placeholder.sync="producto.ProductoMarca"
                        :on-select="(val) => setMarca(val, index)"
                        :onFocus="focusAutocomplete.bind(null, `inputMarca${index}`)"                
                        :classes="{input: 'form-control upper'}">
                      </autocomplete> -->

                    </td>
                    <td> 
                      <b-button variant="info" v-if="producto.TienePresentacion" @click="abrirModalPresentacion(producto,index)">
                        <i class="fas fa-list" ></i>
                      </b-button>
                      </td>
                      <td> 
                      <input type="text" v-model="producto.ProductoMedicion" class="form-control" style="width: 120px;" disabled>
                    </td>
                    <td class="px-2" style="text-align: center;"> <input type="number" v-if="producto.esPresentacion" v-model="producto.precantidad" class="form-control" style="width: 90px;"> </td>
                    <td class="px-2" style="text-align: center;"> <input type="number"  v-if="!producto.esPresentacion" v-model="producto.cantidad" class="form-control" style="width: 90px;"> </td>
                    <!-- <td class="px-2" style="text-align: center;"> PrecioCosto - {{ producto.PrecioCosto }}</td> -->
                    <td class="px-2" style="text-align: center;"> {{ producto.precioUnitario }}</td>
                    <td class="px-2" style="text-align: center;"> <input type="number" v-model="producto.PrecioContado" class="form-control" style="width: 90px;"> </td>
                    <td class="px-2" style="text-align: center;">{{ parseFloat(producto.PrecioCosto * producto.cantidad).toFixed(2) }}</td>
                    <td>
                      <b-button variant="warning" v-if="producto.IdProducto" @click="editarProducto(producto)"><i  class="fa fa-edit" aria-hidden="true"></i></b-button>
                      <b-button variant="success" v-else @click="nuevoProducto(producto)"> <i class="fa fa-plus" aria-hidden="true"></i></b-button>
                      <b-button variant="danger" @click="eliminar(index)"><i  class="fa fa-trash" aria-hidden="true"></i></b-button>
                    </td>
                  </tr>
                </tbody>
               </table>
            </b-col>

            <producto-form @productoactualizado="productoactualizado" :showButton="false" ref="productoFormEdit" :producto-update="productoSelect"></producto-form>
                      

            <b-col cols="12" md="4">
              <table width="100%" class="table"> 
                <tbody>
                  <tr>
                    <td> <b>Total Factura XML</b></td>
                    <td style="background: rgb(245, 198, 203);"> {{ parseFloat(totalFacturaXML).toFixed(1) }}</td>
                  </tr>
                  <tr>
                    <td> <b>Total Factura</b></td>
                    <td style="background: rgb(245, 198, 203);"> {{ totalFactura }}</td>
                  </tr>
                </tbody>
              </table>
            </b-col>
            <b-col cols="12" v-if="mostrarAdvertencia" class="mt-2">
              <b-alert show variant="warning">
                <li>
                  Si el Proveedor no se encuentra registrado, será incorporado al sistema mediante un registro automático.
                </li>
               <li>
                 Aquellos productos que no cuenten con un código de barras serán incorporados al sistema mediante un registro automático, durante el cual se les asignará un código.

               </li>
              </b-alert>
            </b-col>
            <b-col cols="12" style="text-align: center;" v-if="lstProductos.length>0">
                <b-button variant="success" @click="registrarIngreso"> Guardar </b-button>
            </b-col>
            </b-row>
            <b-modal v-model="modalPresentacion" size="lg" hide-footer hide-heade>
              <div class="d-block text-center">
                <h3 style="text-decoration: underline;">{{producto.Producto}}</h3>
                <h5>Presentaciones</h5>
              </div>
              <b-row>
                <b-col cols="6">
                   <b-card
                      align="center"
                      class="mb-3 contentCard"
                      @click="cambiarPresentacion(null,'UNIDAD')"
                   >
                   <b-row>
                      <b-col cols="6" class="nomCart">
                        UNIDAD
                      </b-col>
                      <b-col cols="6">
                        <b>Cant.</b> 1
                        <b>Precio.</b> {{ producto.PrecioContado }}
                      </b-col>
                    </b-row>
                  </b-card>
                </b-col>
                <b-col cols="6" v-for="(pres,index) in producto.presentacion" :key="index">
                   <b-card
                     align="center"
                      class="mb-3 contentCard"
                      @click="cambiarPresentacion(pres,'PRESENTACION')"
                   >
                   <b-row>
                      <b-col cols="6" class="nomCart">
                        {{pres.nombre}}
                      </b-col>
                      <b-col cols="6">
                        <b>Cant.</b> {{pres.cantidad}}
                        <b>Precio.</b> {{ pres.precio }}
                      </b-col>
                    </b-row>
                  </b-card>
                </b-col>
              </b-row>
            </b-modal>
    </b-container>
  </template>
  
  <script>
import config from "@/store/config.js";
import { mapState, mapActions } from "vuex";
import ProductoForm from "./../components/ProductoForm";
export default {
  components: {
    ProductoForm
  },
  data() {
    return {
      modalPresentacion: false,
      API: config.API,
      rucProveedor: "",
      nameProveedor: "",
      direccionProveedor: "",
      productos: [],
      lstProductos: [],
      lstPreProductos: [],
      movimiento: this.initMovimiento(),
      lstMarcas: [],
      isVentas: false,
      loadCode: "",
      producto: {},
      index: "",
      totalFacturaXML: 0,
      productoSelect: {},
      showButton: {
        default: false,
        type: Boolean
      }
    };
  },
  methods: {
    ...mapActions({
      buscarProveedores: "buscarProveedores",
      buscarAlmacenes: "buscarAlmacenes",
      limpiar: "limpiarCarrito",
      obtenerStok: "obtenerStock"
    }),
    async productoactualizado() {
      this.lstPreProductos = [];
      this.lstProductos = [];
      this.$swal({
        title: "Procesando...",
        text: "Por favor espera",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });
      this.$nextTick(async () => {
        setTimeout(async () => {
          await this.validarExisteProductos();
          await this.obtenerStock();
        }, 100);
      });
      this.$swal.close();
    },
    editarProducto(Pro) {
      this.productoSelect = Pro;
      this.$refs.productoFormEdit.show = true;
      this.$refs.productoFormEdit.nuevo = false;
      this.$refs.productoFormEdit.manterproducto = false;
    },
    nuevoProducto(Pro) {
      this.productoSelect = Pro;
      this.$refs.productoFormEdit.show = true;
      this.$refs.productoFormEdit.nuevo = true;
      this.$refs.productoFormEdit.manterproducto = true;
    },
    setMarca(val, index) {
      let prod = { ...this.lstProductos[index] };
      prod.IdProductoMarca = val.IdProductoMarca;
      Vue.set(this.lstProductos, index, prod);
    },
    leerXML(e) {
      const file = e.target.files[0];
      if (!file) return;

      const validMimeTypes = ["text/xml", "application/xml"];
      const validExtension = file.name.toLowerCase().endsWith(".xml");

      if (!validMimeTypes.includes(file.type) && !validExtension) {
        alert("Por favor selecciona un archivo XML válido.");
        return;
      }
      const reader = new FileReader();
      reader.onload = event => {
        const xml = event.target.result;
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(xml, "application/xml");

        this.totalFacturaXML = "";
        try {
          const accountingSupplier = xmlDoc.getElementsByTagName(
            "cac:LegalMonetaryTotal"
          )[0];
          const party = accountingSupplier.getElementsByTagName(
            "cbc:PayableAmount"
          )[0];
          // const partyId = party.getElementsByTagName(
          //   "cac:PayableAmount "
          // )[0];
          // const id = party.getElementsByTagName("cbc:ID")[0];
          this.totalFacturaXML = party.textContent;
          // this.movimiento.totalFacturaXML = id.textContent;
        } catch (error) {
          console.warn("No se pudo obtener total:", error);
        }

        this.rucProveedor = "";
        try {
          const accountingSupplier = xmlDoc.getElementsByTagName(
            "cac:AccountingSupplierParty"
          )[0];
          const party = accountingSupplier.getElementsByTagName("cac:Party")[0];
          const partyId = party.getElementsByTagName(
            "cac:PartyIdentification"
          )[0];
          const id = partyId.getElementsByTagName("cbc:ID")[0];
          this.rucProveedor = id.textContent;
          this.movimiento.rucProveedor = id.textContent;
        } catch (error) {
          console.warn("No se pudo obtener el RUC del proveedor:", error);
        }

        this.nameProveedor = "";
        try {
          const accountingSupplier = xmlDoc.getElementsByTagName(
            "cac:AccountingSupplierParty"
          )[0];
          const party = accountingSupplier.getElementsByTagName("cac:Party")[0];
          const partyId = party.getElementsByTagName("cac:PartyLegalEntity")[0];
          const id = partyId.getElementsByTagName("cbc:RegistrationName")[0];
          this.nameProveedor = id.textContent;
          this.movimiento.nameProveedor = id.textContent;
        } catch (error) {
          console.warn("No se pudo obtener el Nombre del proveedor:", error);
        }

        this.direccionProveedor = "";
        try {
          const accountingSupplier = xmlDoc.getElementsByTagName(
            "cac:AccountingSupplierParty"
          )[0];
          if (!accountingSupplier)
            throw new Error("No se encontró AccountingSupplierParty");

          const party = accountingSupplier.getElementsByTagName("cac:Party")[0];
          if (!party) throw new Error("No se encontró Party");

          const legalEntity = party.getElementsByTagName(
            "cac:PartyLegalEntity"
          )[0];
          if (!legalEntity) throw new Error("No se encontró PartyLegalEntity");

          const regAddress = legalEntity.getElementsByTagName(
            "cac:RegistrationAddress"
          )[0];
          if (!regAddress)
            throw new Error("No se encontró RegistrationAddress");

          // Extraer los campos por separado
          const line = regAddress.getElementsByTagName("cbc:Line")[0];
          const district = regAddress.getElementsByTagName("cbc:District")[0];
          const city = regAddress.getElementsByTagName("cbc:CityName")[0];
          const subentity = regAddress.getElementsByTagName(
            "cbc:CountrySubentity"
          )[0];

          // Concatenar la dirección
          const direccion =
            (line ? line.textContent.trim() + ", " : "") +
            (district ? district.textContent.trim() + ", " : "") +
            (city ? city.textContent.trim() + ", " : "") +
            (subentity ? subentity.textContent.trim() : "");

          this.direccionProveedor = direccion;
          this.movimiento.direccionProveedor = direccion;
        } catch (error) {
          console.warn(
            "No se pudo obtener la dirección del proveedor:",
            error.message
          );
        }

        const invoiceLines = xmlDoc.getElementsByTagName("cac:InvoiceLine");
        const productos = Array.from(invoiceLines).map(item => {
          const descripcionTags = item.getElementsByTagName("cbc:Description");
          const cantidadTags = item.getElementsByTagName(
            "cbc:InvoicedQuantity"
          );
          const pricingReferenceTags = item.getElementsByTagName(
            "cac:PricingReference"
          );
          let precioUnitario = "";

          if (pricingReferenceTags.length > 0) {
            const priceAmountTags = pricingReferenceTags[0].getElementsByTagName(
              "cbc:PriceAmount"
            );
            if (priceAmountTags.length > 0) {
              precioUnitario = priceAmountTags[0].textContent;
            }
          }

          return {
            descripcion:
              descripcionTags.length > 0 ? descripcionTags[0].textContent : "",
            cantidad:
              cantidadTags.length > 0 ? cantidadTags[0].textContent : "",
            precioUnitario: precioUnitario,
            PrecioCosto: precioUnitario
          };
        });
        this.productos = productos;

        this.generarDatos();
      };

      reader.readAsText(file);
    },
    async generarDatos() {
      this.lstPreProductos = [];
      this.lstProductos = [];
      this.$swal({
        title: "Procesando...",
        text: "Por favor espera",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });
      await this.validarExisteProductos();
      await this.obtenerStock();
      this.$swal.close();
    },
    async validarExisteProductos() {
      for (const element of this.productos) {
        element.IdProducto = "";
        element.CodigoBarra = "";
        element.Producto = element.descripcion;
        element.PrecioCosto = element.precioUnitario;

        try {
          const res = await this.axios.get(
            `${this.API}buscarproductos/nombrexml`,
            {
              params: {
                Producto: element.descripcion
              }
            }
          );

          if (res.data.length > 0) {
            let productos = res.data;
            let background = this.generarColorClaroHex();
            await productos.forEach(producto => {
              producto.descripcion = element.descripcion;
              producto.background = background;
              producto.cantidad = element.cantidad;
              producto.PrecioCosto = element.precioUnitario;
              this.lstPreProductos.push(producto);
            });
          } else {
            element.IdProducto = "";
            element.CodigoBarra = "";
            element.background = "#FFFFFF";
            this.lstPreProductos.push(element);
          }
        } catch (error) {
          console.error("Error al validar producto:", error);
          this.lstPreProductos.push(element);
        }
      }
      this.clearFileInput();
    },
    clearFileInput() {
      this.$refs.fileInput.reset();
    },
    async obtenerStock() {
      let productos = [];
      let IdAlmacen = "";

      if (this.isSalida) {
        IdAlmacen =
          this.movimiento.almacenOrigen.IdAlmacen > 0
            ? this.movimiento.almacenOrigen.IdAlmacen
            : 1;
      } else {
        IdAlmacen = !this.isTraslado
          ? this.movimiento.almacenDestino.IdAlmacen > 0
            ? this.movimiento.almacenDestino.IdAlmacen
            : 1
          : this.movimiento.almacenOrigen.IdAlmacen > 0
            ? this.movimiento.almacenOrigen.IdAlmacen
            : 1;
      }

      const promesas = this.lstPreProductos.map(async producto => {
        producto.precioUnitario = producto.PrecioCosto;
        producto.esPresentacion = false;
        producto.precantidad = producto.cantidad;
        producto.stock = 0;
        producto.existe = false;
        producto.TienePresentacion = false;
        producto.ProductoMedicion = "UNIDAD";
        let presentacion = [];
        if (producto.IdProducto) {
          const response = await this.axios(`${this.API}productos/stock`, {
            params: {
              idAlmacen: IdAlmacen,
              idProductos: [producto.IdProducto]
            }
          });
          const res = response.data[0];

          producto.TienePresentacion = false;
          if (producto.PreciosPorProducto) {
            presentacion = JSON.parse(producto.PreciosPorProducto);
            producto.TienePresentacion = presentacion.some(v => v.cantidad > 0);
          }

          producto.presentacion = presentacion.filter(v => v.cantidad > 0);
          producto.stock = res.stock;
          producto.existe = true;
          producto.ProductoMarca = res.ProductoMarca;
        }
        return producto;
      });

      productos = await Promise.all(promesas);

      this.lstProductos = productos.sort((a, b) => {
        if (a.background < b.background) return -1;
        if (a.background > b.background) return 1;
        return 0;
      });
    },
    eliminar(index) {
      this.$swal({
        icon: "question",
        title: "Eliminar",
        text: "¿Desea eliminar el registro?",
        showCancelButton: true,
        allowEscapeKey: false,
        allowOutsideClick: false
      }).then(response => {
        if (response.isConfirmed) {
          this.lstProductos.splice(index, 1);
        }
      });
    },
    initMovimiento() {
      let currentMonth = this.$moment().month() + 1;
      currentMonth = currentMonth < 10 ? "0" + currentMonth : currentMonth;
      return {
        EsCredito: 0,
        alterarProductos: 1,
        FechaVenCredito: "",
        Moneda: "USD",
        FechaPeriodoTributario: this.$moment().year() + "" + currentMonth,
        // MovimientoFecha: this.$moment().format("YYYY-MM-DDTH:mm"),
        MovimientoFecha:
          this.$browser.name == "chrome"
            ? this.$moment().format("YYYY-MM-DDThh:mm:ss")
            : this.$moment().format("YYYY-MM-DD hh:mm:ss"),
        FechaStock: this.$moment().format("YYYY-MM-DD"),
        TipoCambio: 1,
        proveedor: { IdProveedor: 0 },
        movimientoTipo: {},
        almacenOrigen: { IdAlmacen: -1 },
        almacenDestino: { IdAlmacen: -1 },
        user: sessionStorage.getItem("User")
      };
    },
    setMoneda(obj) {
      this.movimiento.Moneda = obj.Moneda;
      if (obj.Moneda === "USD") {
        this.movimiento.TipoCambio = this.tipocambio;
      } else {
        this.movimiento.TipoCambio = 1;
      }
    },
    cargarNumero() {
      this.axios
        .get(this.API + "movimientos/numero", {
          params: {
            serie: this.movimiento.Serie,
            idMovimientoTipo: this.movimiento.movimientoTipo.IdMovimientoTipo
          }
        })
        .then(response => {
          Vue.set(
            this.movimiento,
            "Numero",
            response.data.NuevoNumero || "0001"
          );
        })
        .catch(error => {
          console.log(error);
          // this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        });
    },
    setTipoMovimiento(obj) {
      if (obj.Tipo == 0) {
        this.movimiento.almacenOrigen = { IdAlmacen: -1 };
        this.movimiento.almacenDestino = { IdAlmacen: 1 };
        this.isTraslado = false;
        this.isSalida = false;
        if (this.$refs.inputAlmacenDestino) {
          this.$refs.inputAlmacenDestino.setValue("");
        }
      } else if (obj.Tipo == 1) {
        this.movimiento.almacenDestino = { IdAlmacen: -1 };
        this.movimiento.almacenOrigen = { IdAlmacen: 1 };
        this.isTraslado = false;
        this.isSalida = true;
        if (this.$refs.inputAlmacenOrigen) {
          this.$refs.inputAlmacenOrigen.setValue("");
        }
      } else {
        this.movimiento.almacenDestino = { IdAlmacen: 1 };
        this.movimiento.almacenOrigen = { IdAlmacen: 1 };
        this.isTraslado = true;
        this.isSalida = false;
        if (this.$refs.inputAlmacenDestino) {
          this.$refs.inputAlmacenDestino.setValue("");
        }
        if (this.$refs.inputAlmacenOrigen) {
          this.$refs.inputAlmacenOrigen.setValue("");
        }
      }
      this.movimiento.movimientoTipo = obj;

      this.cargarNumero();
    },
    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick();
    },
    setAlmacenOrigen(obj) {
      this.movimiento.almacenOrigen = obj;
      this.obtenerStock();
    },
    setAlmacenDestino(obj) {
      this.movimiento.almacenDestino = obj;
      this.obtenerStock();
    },
    generarColorClaroHex() {
      let hexColor = "#FFFFFF";
      const coloresUsados = this.lstPreProductos.map(p => p.background);

      const hslToHex = (h, s, l) => {
        s /= 100;
        l /= 100;
        let c = (1 - Math.abs(2 * l - 1)) * s,
          x = c * (1 - Math.abs(((h / 60) % 2) - 1)),
          m = l - c / 2,
          r = 0,
          g = 0,
          b = 0;

        if (0 <= h && h < 60) {
          r = c;
          g = x;
          b = 0;
        } else if (60 <= h && h < 120) {
          r = x;
          g = c;
          b = 0;
        } else if (120 <= h && h < 180) {
          r = 0;
          g = c;
          b = x;
        } else if (180 <= h && h < 240) {
          r = 0;
          g = x;
          b = c;
        } else if (240 <= h && h < 300) {
          r = x;
          g = 0;
          b = c;
        } else if (300 <= h && h < 360) {
          r = c;
          g = 0;
          b = x;
        }

        const toHex = c => {
          const hex = Math.round((c + m) * 255).toString(16);
          return hex.length === 1 ? "0" + hex : hex;
        };

        return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
      };

      // Generar color hasta que no esté usado ni sea blanco
      do {
        const hue = Math.floor(Math.random() * 360);
        const saturation = Math.floor(Math.random() * 50) + 50;
        const lightness = Math.floor(Math.random() * 30) + 70;
        hexColor = hslToHex(hue, saturation, lightness);
      } while (hexColor === "#FFFFFF" || coloresUsados.includes(hexColor));

      return hexColor;
    },
    async registrarIngreso(data) {

      
      if (!this.movimiento.Serie) {
        this.$toasted.error("Es importante la Serie");
        this.$refs.txtcargarNumero.focus();
        return false;
      }

      const colores = {};

      this.lstProductos.forEach((producto, index) => {
        const bg = producto.background.toUpperCase(); // normalizamos a mayúsculas
        if (bg !== "#FFFFFF") {
          if (!colores[bg]) {
            colores[bg] = [];
          }
          colores[bg].push(index);
        }
      });

      for (let color in colores) {
        if (colores[color].length > 1) {
          this.$toasted.error(
            "Existen productos similares, recuerde que solo asociar a uno. Verifique"
          );
          return false;
        }
      }
      if (this.lstProductos.some(v => !v.IdProducto)) {
        this.$toasted.error(
          "Existe un producto sin registrar. Por favor. Registar El producto."
        );
        return false;
      }

      if (this.lstProductos.some(v => !v.PrecioContado)) {
        this.$toasted.error(
          "Es importante que todos los productos tengan un precio de venta"
        );
        return false;
      }
      this.$swal({
        title: "Procesando...",
        text: "Por favor espera",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });
      this.movimiento.productos = this.lstProductos;
      await this.axios
        .post(`${this.API}/movimientoxml`, this.movimiento)
        .then(res => {
          if (res.data) {
            this.$swal({
              icon: "success",
              title: "Registro Correcto",
              allowOutsideClick: false,
              allowEscapeKey: false
            }).then(respuesta => {
              if (respuesta.isConfirmed) {
                window.location.reload();
              }
            });
          }
        });

      console.log("Registrando ingreso...", data);
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
            width: 250px;
            heigth: 250px;
          }
        </style>
        <div>
          <table id="table-render" style="width: 830px; text-align: center;padding:0; margin:0">
            <tbody>
              <tr>
                <td style="width: 60px" class="${styleStock}"><b>${
          data.stock
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
    setProducto(obj) {
      if (this.lstPreProductos.some(v => v.IdProducto == obj.IdProducto)) {
        this.$toasted.error("El producto ya se encuentra en el listado");
        return false;
      }
      obj.background = this.generarColorClaroHex();
      this.lstPreProductos.unshift(obj);
      this.lstProductos = [];
      this.obtenerStock();
    },
    loadBarcode(e) {
      if (e) {
        e.preventDefault();
      }
      let idProducto = this.loadCode;

      if (idProducto) {
        let params = { codigoBarra: idProducto };
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
    abrirModalPresentacion(obj, index) {
      this.producto = obj;
      this.index = index;
      this.modalPresentacion = true;
    },
    cambiarPresentacion(obj = {}, tipo = "UNIDAD") {
      let prod = { ...this.producto };
      if (tipo != "UNIDAD") {
        prod.esPresentacion = true;
        prod.cantidad = prod.precantidad * obj.cantidad;
        prod.PrecioCosto = parseFloat(
          prod.precioUnitario / obj.cantidad
        ).toFixed(4);
        prod.ProductoMedicion = obj.nombre;
      } else {
        prod.esPresentacion = false;
        prod.cantidad = prod.precantidad;
        prod.PrecioCosto = prod.precioUnitario;
        prod.ProductoMedicion = "UNIDAD";
      }
      Vue.set(this.lstProductos, this.index, prod);
      this.modalPresentacion = false;
      // prod.precioCosto = obj;
    }
  },
  computed: {
    APIProductos() {
      if (this.idAlmacen) {
        return this.API + "productos/stock/" + this.idAlmacen;
      } else {
        return this.API + "productos";
      }
    },
    mostrarAdvertencia() {
      let val = this.lstProductos.some(v => !v.CodigoBarra);
      return val;
    },
    Proveedor() {
      if (this.rucProveedor && this.nameProveedor) {
        return this.rucProveedor + " - " + this.nameProveedor;
      }
      return "";
    },
    activarCargarXML() {
      if (
        this.movimiento.almacenOrigen.IdAlmacen == -1 &&
        this.movimiento.almacenDestino.IdAlmacen == -1
      ) {
        return false;
      }
      return true;
    },
    totalFactura() {
      let total = 0;
      if (this.lstProductos.length > 0) {
        total = this.lstProductos.reduce((sum, producto) => {
          return sum + parseFloat(producto.PrecioCosto * producto.cantidad);
        }, 0);
      }
      return parseFloat(total).toFixed(1);
    },
    mostrarAnadirProductos() {
      // if (this.lstProductos.length > 0) {
      //   return true;
      // }
      return false;
    }
  },
  mounted() {}
};
</script>
<style scoped>
.contentCard {
  cursor: pointer;
}
.nomCart {
  display: flex;
  justify-content: center;
  align-content: center;
  align-items: center;
  border-right: 2px solid black;
}
</style>

