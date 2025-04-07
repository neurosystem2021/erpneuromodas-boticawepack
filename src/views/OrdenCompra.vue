<template>
  <b-container fluid>
    <b-row>
        <b-col cols="4">
                Almacén
                <b-select 
                    :options="lstAlmacen" 
                    value-field="IdAlmacen"
                    text-field="Almacen"
                    v-model="IdAlmacen"
                />
        </b-col>
        <b-col cols="4">
            Proveedor
                <autocomplete
                ref="autocompleteProveedor"
                :url="`${API}proveedores`"
                anchor="Proveedor"
                label="writer"
                placeholder="Proveedor"
                :on-select="setProveedor"
                :onFocus="focusAutocomplete.bind(null, 'autocompleteProveedor')"
                :classes="{input: 'form-control'}">
              </autocomplete>
        </b-col>
        <b-col cols="4" class="my-auto">
            <b-button variant="info" @click="obtenerListado()"><i class="fa fa-search" aria-hidden="true" ></i> Buscar</b-button>
            <b-button variant="success"  @click="abrirModal()"><i class="fa fa-plus" aria-hidden="true"></i> Nuevo</b-button>
            <b-button variant="secondary"  @click="limpiar()"><i class="fa fa-filter" aria-hidden="true"></i> Limpiar</b-button>
        </b-col>

        <b-col cols="12">
            <b-table :items="lstOrdenCompra" :fields="hearder">
                <template slot="detalle" slot-scope="data">
                    <thead  style="padding: 0px;margin: 0px; font-size: 0.75rem;">
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody style="padding: 0px;margin: 0px; font-size: 0.75rem;">
                        <tr v-for="(detalle,index) in data.item.Detalle" :key="index">
                            <td>{{ detalle.Producto }}</td>
                            <td>{{ detalle.Cantidad }}</td>
                            <td>{{ detalle.Precio }}</td>
                            <td>{{ detalle.Cantidad*detalle.Precio }}</td>
                        </tr>
                        <tr>
                            <th>Total:</th>
                            <th>
                                {{ data.item.Detalle.reduce((sum,item)=>{
                                    return sum + parseFloat(item.Cantidad)
                                },0) }}
                            </th>
                            <th>
                                {{ data.item.Detalle.reduce((sum,item)=>{
                                    return sum + parseFloat(item.Precio)
                                },0) }}
                            </th>
                            <th>
                                {{ data.item.Detalle.reduce((sum,item)=>{
                                    return sum + parseFloat(item.Cantidad*item.Precio)
                                },2) }}
                            </th>
                        </tr>
                    </tbody>
                </template>
                
            </b-table>
        </b-col>
    </b-row>
    <b-modal v-model="nuevaOrdenCompraFlag" title="Registro Nueva Orden" size="lg" hide-footer>
        <b-row>
        <b-col cols="4">
                Almacén
                <b-select 
                    :options="lstAlmacen" 
                    value-field="IdAlmacen"
                    text-field="Almacen"
                    v-model="frmNuevo.IdAlmacen"
                />
        </b-col>
        <b-col cols="4">
            Proveedor
                <autocomplete
                ref="autocompleteProductonuevo"
                :url="`${API}proveedores`"
                anchor="Proveedor"
                label="writer"
                placeholder="Proveedor"
                :on-select="setProveedorNuevo"
                :onFocus="focusAutocomplete.bind(null, 'autocompleteProductonuevo')"
                :classes="{input: 'form-control'}">
              </autocomplete>
        </b-col>
        <b-col cols="4">
            Fecha 
            <input type="date" v-model="frmNuevo.fecha" class="form-control">
        </b-col>
        <b-col cols="12" class="my-1" v-if="mostrarProductosNuevo">
            Añadir Producto:
            <autocomplete
                ref="autocompleteProductoNuevo"
                :url="APIProductos"
                :customParams="{ 'sortBy':isVentas?'stock':'IdProducto', 'sortDesc': 1 ,limit: '25' }"
                :onShouldRenderChild="renderChild"
                anchor="Producto"
                label="writer"
                placeholder="Agregar Producto"
                :on-select="setProducto"
                :onFocus="focusAutocomplete.bind(null, 'autocompleteProductoNuevo')"
                @keydown.native.prevent.tab="irtxtBarcode"
                id="autocompleteProductoNuevo"
                :classes="{input: 'form-control'}">
              </autocomplete>
        </b-col>
        <b-col cols="12" class="my-1">
            <b-table :items="frmNuevo.lstProductos" :fields="hearderNuevo">
                <template slot="Cantidad" slot-scope="data">
                    <input  :id="`Cantidad${data.index}`" style="width: 80px" type="number" v-model="data.item.Cantidad" class="form-control">
                </template>
                <template slot="Precio" slot-scope="data">
                    <input  style="width: 80px" type="number" v-model="data.item.precio" class="form-control">
                </template>
                <template slot="total" slot-scope="data">
                            {{ data.item.Cantidad * data.item.precio }}
                </template>
                <template slot="action" slot-scope="data">
                    <i class="fa fa-trash" aria-hidden="true" style="color:red" @click="eliminarRegistro(data.index)"></i>

                </template>

            </b-table>
           
        </b-col>
        <b-col cols="3">
            <b-input-group size="md" prepend="Total:">
                <input type="number" class="form-control" disabled v-model="totalOrdenCompraNuevo">
            </b-input-group>
        </b-col>
        <b-col cols="9" style="text-align: right;">
            <b-button variant="success" :disabled="activarBTNGuardar" @click="guardarOrdenCompra()">Guardar</b-button>
            <b-button variant="danger">Cancelar</b-button>
        </b-col>
        </b-row>
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      isVentas: false,
      nuevaOrdenCompraFlag: false,
      lstOrdenCompra: [],
      lstAlmacen: [],
      IdAlmacen: "",
      proveedor: {},
      hearder: [
        { key: "Almacen" },
        { key: "Proveedor" },
        { key: "FechaOrdenCompra", label: "Fecha" },
        { key: "Total" },
        { key: "Anio", label: "Año" },
        { key: "Numero", label: "Número" },
        { key: "detalle", label: "Detalle" }
        // { key: "action", label: "" }
      ],
      frmNuevo: {
        IdAlmacen: "",
        proveedor: {},
        Producto: [],
        lstProductos: []
      },
      hearderNuevo: [
        { key: "Producto" },
        { key: "Cantidad" },
        { key: "Precio" },
        { key: "total" },
        { key: "action" }
      ]
    };
  },
  async mounted() {
    this.IdAlmacen = sessionStorage.getItem("IdAlmacen");
    await this.obtenerAlmacen();
    await this.obtenerListado();
  },
  methods: {
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    setProveedor(proveedor) {
      this.proveedor = proveedor;
    },
    setProveedorNuevo(proveedor) {
      this.frmNuevo.proveedor = proveedor;
    },
    async obtenerAlmacen() {
      await this.axios(`${this.API}almacenes`).then(res => {
        if (res.data) {
          this.lstAlmacen = res.data;
          this.IdAlmacen = this.IdAlmacen
            ? this.IdAlmacen
            : res.data[0].IdAlmacen;
        }
      });
    },
    async obtenerListado() {
      await this.axios(`${this.API}ordenCompra`, {
        params: {
          IdAlmacen: this.IdAlmacen,
          IdProveedor: this.proveedor ? this.proveedor.IdProveedor : null
        }
      }).then(res => {
        if (res.data) {
          this.lstOrdenCompra = res.data;
        }
      });
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
    setProducto(producto) {
      if (
        this.frmNuevo.lstProductos.some(
          v => v.IdProducto == producto.IdProducto
        )
      ) {
        this.$toasted.error("El Producto ya se encuentra en la lista.");
        return false;
      }
      producto.Cantidad = 1;
      producto.precio = producto.PrecioCosto;
      this.frmNuevo.lstProductos.unshift(producto);
      this.$nextTick(() => {
        let cantidad = document.getElementById("Cantidad0");
        cantidad.focus();
        cantidad.select();
      });
    },
    eliminarRegistro(index) {
      this.frmNuevo.lstProductos.splice(index, 1);
    },
    abrirModal() {
      this.frmNuevo = {
        IdAlmacen: "",
        proveedor: {},
        Producto: [],
        lstProductos: []
      };
      this.nuevaOrdenCompraFlag = true;
    },
    guardarOrdenCompra() {
      this.frmNuevo.total = this.totalOrdenCompraNuevo;
      this.axios
        .post(`${this.API}guardarordencompra`, this.frmNuevo)
        .then(res => {
          if (res.data) {
            this.obtenerListado();
            this.nuevaOrdenCompraFlag = false;
          }
        });
    },
    limpiar() {
      this.proveedor = {};
      this.obtenerListado();
      this.focusAutocomplete("autocompleteProveedor");
    },
    toggleDetalle(item) {
      console.log(item);
      console.log("Detalle abierto:", isShown, row.item);
    }
  },
  computed: {
    APIProductos() {
      if (this.IdAlmacen) {
        return this.API + "productos/stock/" + this.IdAlmacen;
      } else {
        return this.API + "productos";
      }
    },
    mostrarProductosNuevo() {
      if (!this.frmNuevo.fecha) {
        return false;
      }
      if (!this.frmNuevo.IdAlmacen) {
        return false;
      }
      if (!Object.keys(this.frmNuevo.proveedor).length > 0) {
        return false;
      }
      return true;
    },
    activarBTNGuardar() {
      if (this.frmNuevo.lstProductos.length > 0) {
        return false;
      }
      return true;
    },
    totalOrdenCompraNuevo() {
      let total = this.frmNuevo.lstProductos.reduce((sum, producto) => {
        return sum + parseFloat(producto.Cantidad * producto.precio);
      }, 0);
      return parseFloat(total).toFixed(2);
    }
  }
};
</script>

<style>
</style>
