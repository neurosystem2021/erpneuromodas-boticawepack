<template>
  <div class="stock-report-page">
      <b-container fluid class="stock-report-shell">
        <header class="stock-report-hero"><div class="stock-report-hero__icon"><i class="fas fa-boxes"></i></div><div><small>CONTROL DE INVENTARIO</small><h1>Stock y valorización</h1><p>Consulta existencias, costos y valores comerciales por almacén.</p></div><span><i class="fas fa-shield-alt"></i> Información protegida</span></header>
        <b-row class="stock-summary-panel">
          <b-col md="12"  class="my-1">
            <b-row>
              <b-col md="2">
                <div class="suma-stock stock-value-card text-center" @click="solicitarAccesoValorizado">
                  <b>Stock Valorizado <br />Precio Costo</b>
                  <div v-if="valorizadoAutorizado"> S/. {{ sumaValorizado | moneda }} </div>
                  <div v-else class="stock-locked" title="Ver stock valorizado"><i class="fas fa-eye"></i></div>
                </div>
              </b-col>
              <b-col md="2">
                <div class="suma-stock stock-value-card text-center" @click="solicitarAccesoValorizado">
                  <b>Stock Valorizado <br />Precio Venta</b>
                  <div v-if="valorizadoAutorizado"> S/. {{ sumaValorizadoSin | moneda }} </div>
                  <div v-else class="stock-locked" title="Ver stock valorizado"><i class="fas fa-eye"></i></div>
                </div>
              </b-col>  
              <b-col md="2">
                <div class="suma-stock stock-general-card text-center">
                  <b>Stock General</b>
                  <div> {{ sumaStock }} </div>
                </div>
              </b-col>
              <b-col md="1">
                <b-form-group>
                  <label>Almacen:</label>
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
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Tipo de Muestra">
                  <b-form-radio-group button-variant="primary" buttons v-model="tipoStock" 
                    name="butons1" 
                    :options="[
                      {text: 'Stock > 0', value: 'mayor'},
                      {text: 'Stock <= 0', value: 'menor'}
                    ]"
                    @change="val => generateFilter('stock', val)"></b-form-radio-group>
                </b-form-group>
              </b-col>
              <b-col md="1">
                <b-form-group label="Limpiar ">
                  <b-button :disabled="!filter" @click="limpiarFiltros">Limpiar</b-button>
                </b-form-group>
                <br />
              </b-col>
              <!--<b-col md="1">
                <b-form-group label="Stock Mínimo">
                  <b-form-checkbox id="stock"
                     @change="val => generateFilter('minimo', val)"
                     :value="1"
                     :unchecked-value="0">

                  </b-form-checkbox>
                </b-form-group>
              </b-col>-->
              <b-col md="1">
                <b-form-group label="Mostrar">
                  <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
                </b-form-group>
              </b-col>
            </b-row>
          </b-col>
        </b-row>
        <b-row class="stock-search-card">
          <b-col md="3">
                <b-form-group>
                  <label>Código de barra:</label>
                  <input ref="inputCodigoBarra" class="form-control" @keyup="(val) => generateFilter('codigo', val.target.value, true)"/>
                </b-form-group>
          </b-col>
        </b-row>
        <b-row class="stock-filters-card">
          <br>
          <b-col md="6">
            <b-row>
              <b-col md="3">
                <b-form-group>
                  <label>Categoría:</label>
                  <autocomplete
                    :url="API + 'categorias'"
                    ref="inputCategoria"
                    anchor="ProductoCategoria"
                    label="writer"
                    :on-select="val => generateFilter('categoria', val.ProductoCategoria)"
                    :onInput="(val) => !val ? generateFilter('categoria', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-form-group>
                  <label>Artículo:</label>
                  <autocomplete
                    :url="API + 'productos'"
                    ref="inputProducto"
                    anchor="Producto"
                    label="writer"
                    :on-select="val => generateFilter('producto', val.Producto)"                    
                    :onInput="val => generateFilter('producto', val)"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-form-group>
                  <label>Marca:</label>
                  <autocomplete
                    :url="API + 'marcas'"
                    ref="inputMarca"
                    anchor="ProductoMarca"
                    label="writer"
                    :on-select="val => generateFilter('marca', val.ProductoMarca)"
                    :onInput="(val) => !val ? generateFilter('marca', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>  
              <b-col md="3">
                <b-form-group>
                  <label>Modelo:</label>
                  <autocomplete
                    :url="API + 'productos/modelos'"
                    ref="inputModelo"
                    anchor="ProductoModelo"
                    label="writer"
                    :on-select="val => generateFilter('modelo', val.ProductoModelo)"
                    :onInput="(val) => !val ? generateFilter('modelo', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>
            </b-row>
          </b-col>
          <b-col md="6">
            <b-row>
              <b-col md="3">
                <b-form-group>
                  <label>Color:</label>
                  <autocomplete
                    :url="API + 'productos/colores'"
                    ref="inputColor"
                    anchor="Color"
                    label="writer"
                    :on-select="val => generateFilter('color', val.Color)"
                    :onInput="(val) => !val ? generateFilter('color', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>  
              <b-col md="3">
                <b-form-group>
                  <label>Talla:</label>
                  <autocomplete
                    :url="API + 'tallas'"
                    ref="inputTalla"
                    anchor="ProductoTalla"
                    label="writer"
                    :on-select="val => generateFilter('talla', val.ProductoTalla)"
                    :onInput="(val) => !val ? generateFilter('talla', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col> 
              <b-col md="3">
                <b-form-group>
                  <label>Genero:</label>
                  <autocomplete
                    :url="API + 'productos/generos'"
                    ref="inputGenero"
                    anchor="Genero"
                    label="writer"
                    :on-select="val => generateFilter('genero', val.Genero)"
                    :onInput="(val) => !val ? generateFilter('genero', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-form-group>
                  <label>Botapie:</label>
                  <autocomplete
                    :url="API + 'productos/botapies'"
                    ref="inputBotapie"
                    anchor="Botapie"
                    label="writer"
                    :on-select="val => generateFilter('botapie', val.Botapie)"
                    :onInput="(val) => !val ? generateFilter('botapie', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>
            </b-row>
          </b-col>
        </b-row>
        <b-row class="stock-results-card">
          <b-col md="12" class="my-1">
            <table class="table border b-table table-footer table-sm table-detalle">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th class="text-center">Precio de compra</th>
                  <th class="text-center">Precio de venta</th>
                  <th class="text-center">Stock</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>{{ productoSelect.Producto }}</td>
                  <td class="text-center">{{ productoSelect.PrecioCosto }}</td>
                  <td class="text-center">{{ productoSelect.PrecioContado }}</td>
                  <td class="text-center table-info">{{ getStock(productoSelect.IdProducto) }}</td>
                </tr>
              </tbody>
            </table>
          </b-col>

          <b-modal id="modalImagen" size="lg" ref="modalImagen" hide-footer hide-header>
            <div class="d-block text-center">
              <h3 style="text-decoration: underline;">{{productoNombre}}</h3>
            </div>
            <b-row style="display:flex; justify-content:center; align-items:center">
              <b-img :src="productoUrl" class="imagenGrande"></b-img>
            </b-row>
          </b-modal>
          
          <b-col md="12">
            <b-table small
              ref="tablaProductos"
              :filter="filter"
              :sort-by.sync="sortBy"
              :sort-desc.sync="sortDesc"
              striped
              hover
              @row-clicked="rowClick"
              @row-dblclicked="rowDblClick"
              id="tabla-productos" 
              :busy.sync="isBusy" 
              :items="myProvider" 
              :fields="fields"
              :per-page="perPage"
              :current-page="currentPage">

                <template slot="stock" slot-scope="data">
                  <div :class="getStock(data.item.IdProducto) > 0 ? 'table-success' : 'table-danger'" @click.stop="() => false">
                    {{ getStock(data.item.IdProducto) }}
                  </div>
                </template>
                <template slot="Producto" slot-scope="data">
                  <div>{{data.item.Producto}}</div>
                </template>

                <template slot="ImagenProducto" slot-scope="data">
                  <b-button @click="abrirImagen(data.item.Producto, data.item.Imagen == null || data.item.Imagen==''?'static/no-photo.png': '/resources/images/productos/' + data.item.Imagen)" style="margin-top:2px;" :id="'popover-imagen' + data.item.IdProducto" variant="success" size="sm"><i class="fas fa-image"></i></b-button>	
                  <b-popover placement="top" :target="'popover-imagen' + data.item.IdProducto" :title="data.item.Producto" triggers="hover">
                    <div style="text-align:center">
                      <b-img style="border-radius: 10px; width:200px" fluid :src="data.item.Imagen == null || data.item.Imagen==''?'static/no-photo.png': '/resources/images/productos/' + data.item.Imagen" alt="" @error="setAltImg" ></b-img>	
                    </div>
                  </b-popover>
                </template>
                
              </b-table>
          </b-col>
          <b-row>
              <b-col md="6" class="my-1">
                  <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
              </b-col>
          </b-row>
        </b-row>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button class="stock-export-button" variant="success" size="lg" @click="exportarStock"><i class="fas fa-file-excel"></i> Exportar reporte</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>

      <b-modal v-model="showExportOptions" modal-class="stock-export-modal" title="Opciones de exportación" hide-footer centered>
        <div class="stock-export-option">
          <div class="stock-export-option__icon is-current"><i class="fas fa-calendar-day"></i></div>
          <div><small>REPORTE INMEDIATO</small><h3>Stock hasta la fecha actual</h3><p>Genera las existencias y valorización disponibles al día de hoy.</p></div>
          <b-button @click="exportarStockActual"><i class="fas fa-download"></i> Exportar actual</b-button>
        </div>
        <div class="stock-export-divider"><span>O por periodo</span></div>
        <div class="stock-export-option is-range">
          <div class="stock-export-option__icon"><i class="fas fa-calendar-alt"></i></div>
          <div class="stock-export-range-content"><small>REPORTE HISTÓRICO</small><h3>Stock por rango de fechas</h3><b-row><b-col md="6"><label>Fecha inicio</label><b-form-input v-model="exportar.fechaDesde" type="date"></b-form-input></b-col><b-col md="6"><label>Fecha fin</label><b-form-input v-model="exportar.fechaHasta" type="date"></b-form-input></b-col></b-row></div>
          <b-button @click="exportarStockFechas"><i class="fas fa-file-excel"></i> Exportar por fechas</b-button>
        </div>
      </b-modal>

      <b-modal id="kardex" size="lg" modal-class="modal-xl stock-kardex-modal" v-model="showKardex" title="Kardex de producto" hide-footer>
      
      <div class="stock-kardex-product"><i class="fas fa-box"></i><div><small>PRODUCTO SELECCIONADO</small><strong>{{ titleKardex }}</strong></div></div>
      <b-row class="stock-kardex-filters">
        <b-col md="5">
          <b-form-group>
            <label>Fecha Inicio:</label>
            <b-form-input
              v-model="kardex.fechaDesde"
              type="date"
              step="1"
              placeholder="Fecha Inicio">
            </b-form-input>
          </b-form-group>
        </b-col>
        <b-col md="5">
          <b-form-group>
            <label>Fecha Fin:</label>
            <b-form-input
              v-model="kardex.fechaHasta"
              type="date"
              step="1"
              placeholder="Fecha Inicio">
            </b-form-input>
          </b-form-group>
        </b-col>
        <b-col md="2" class="text-center">
          <b-form-group>
            <br>
            <b-button class="stock-kardex-generate" @click="kardexFechaRango" variant="primary" size="lg"><i class="fas fa-search"></i> Generar</b-button>
          </b-form-group>
        </b-col>
      </b-row>
      <producto-kardex :producto="productoSelect" :kardex="kardexSelect"></producto-kardex>
      <b-row>
        <b-col class="text-center">
            <b-button class="stock-kardex-export" variant="success" :href="hrefXlsProducto" target="_blank"><i class="fas fa-file-excel"></i> Exportar Excel</b-button>
        </b-col>
      </b-row>
      <br>
    </b-modal>

  </div>
</template>

<script>
import config from "@/store/config.js";
import ProductoKardex from "@/components/ProductoKardex";

export default {
  mounted() {
    this.axios.get(this.API + "productos/count").then(response => {
      this.totalRows = response.data.total;
    });

    this.hrefExcel =
      "/api/index.php/reporte/stock?idAlmacen=" + this.almacen.IdAlmacen;
  },
  data() {
    return {
      API: config.API,
      hrefExcel: "",
      sumaStock: "...",
      sumaValorizado: "...",
      sumaValorizadoSin: "...",
      valorizadoAutorizado: false,
      showExportOptions: false,
      APISTOCK: "productos",
      tipoStock: "",
      showKardex: false,
      almacen: {
        IdAlmacen: 1,
        Almacen: "ALMACEN 1"
      },
      pageOptions: [5, 10, 15, 50, 100],
      filters: {},
      filter: "",
      sortBy: "IdProducto",
      sortDesc: false,
      productoSelect: {},
      kardexSelect: [],
      items: [],
      itemsStock: [],
      isBusy: false,
      fields: [
        { key: "CodigoBarra", label: "Código", sortable: true },
        { key: "ProductoCategoria", label: "Categoría", sortable: true },
        { key: "ImagenProducto", label: "Img." },
        { key: "Producto", label: "Artículo", sortable: true },
        { key: "ProductoMarca", label: "Marca", sortable: true },
        { key: "ProductoModelo", label: "Modelo", sortable: true },
        { key: "Color", sortable: true },
        { key: "ProductoTalla", label: "Talla", sortable: true },
        { key: "Genero", sortable: true },
        { key: "Botapie", sortable: true },
        { key: "stock", sortable: false, variant: "", class: "text-center" }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      exportar: {
        fechaDesde: this.$moment().format("YYYY-01-01"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      },
      kardex: {
        fechaDesde: this.$moment().format("YYYY-MM-01"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      },
      productoNombre: "",
      productoUrl: ""
    };
  },
  watch: {
    items(items) {
      //Vue.set(this.$refs.tablaProductos, 'localItems', [])
      //console.log(this.$refs.tablaProductos.localItems)
      //this.myProvider(false)

      this.axios
        .get(this.API + "productos/stock", {
          params: {
            idProductos: items.map(item => item.IdProducto),
            idAlmacen: this.almacen.IdAlmacen
          }
        })
        .then(response => {
          this.itemsStock = response.data;
        })
        .catch(error => {
          console.log(error);
        });

      // Cargar la sumatoria
      this.getSumaStock();
      this.getSumaValorizado();

      /*console.log(items[0].hasOwnProperty('stock'))
      if (!items[0].hasOwnProperty('stock')) {
      //console.log(items)
        // ejecutar solo cuando los productos no tengan stock
        this.axios.get(this.API + 'productos/stock', {
          params: {
            idProductos: items.map((item) => item.IdProducto),
            idAlmacen: this.almacen.IdAlmacen
          }
        }).then((response) => {
         // console.log(response.data)
             this.items = []
        })
        .catch((error) => {
          console.log(error)
        })
      }*/
    }
  },
  computed: {
    titleKardex() {
      return (
        "KARDEX - / " +
        this.productoSelect.CodigoBarra +
        " / " +
        this.productoSelect.Producto +
        " / " +
        this.productoSelect.ProductoMarca +
        " / " +
        this.productoSelect.ProductoModelo +
        " / " +
        this.productoSelect.Color +
        " / " +
        this.productoSelect.ProductoTalla
      );
    },
    hrefXlsFechas() {
      return (
        "/api/index.php/reporte/stock?controlaStock=1&idAlmacen=" +
        this.almacen.IdAlmacen +
        "&fechaDesde=" +
        this.exportar.fechaDesde +
        "&fechaHasta=" +
        this.exportar.fechaHasta +
        "&sortBy=" +
        this.sortBy +
        "&sortDesc=" +
        (this.sortDesc ? 1 : 0) +
        this.filter
      ).replace("#", "");
    },
    hrefXlsProducto() {
      return (
        "/api/index.php/reporte/producto/kardex?idAlmacen=" +
        this.almacen.IdAlmacen +
        "&idProducto=" +
        this.productoSelect.IdProducto +
        "&productoMarca=" +
        this.productoSelect.ProductoMarca +
        "&producto=" +
        this.productoSelect.Producto +
        "&codigoBarra=" +
        this.productoSelect.CodigoBarra +
        "&fechaDesde=" +
        this.kardex.fechaDesde +
        "&fechaHasta=" +
        this.kardex.fechaHasta
      ).replace("#", "");
    }
  },
  methods: {
    async solicitarAccesoValorizado(forzarValidacion = false) {
      if (this.valorizadoAutorizado && !forzarValidacion) return true;
      const result = await this.$swal.fire({
        icon: "info",
        title: "Acceso restringido",
        width: 440,
        html: '<p class="stock-access-description">Ingresa las credenciales de un administrador para consultar la valorización.</p><label class="stock-access-label" for="stock-admin-user"><i class="fas fa-user"></i> Usuario</label><input id="stock-admin-user" class="swal2-input stock-access-input" autocomplete="username" placeholder="Ingresa el usuario"><label class="stock-access-label" for="stock-admin-password"><i class="fas fa-key"></i> Contraseña</label><input id="stock-admin-password" type="password" class="swal2-input stock-access-input" autocomplete="current-password" placeholder="Ingresa la contraseña">',
        showCancelButton: true,
        confirmButtonText: "Desbloquear",
        cancelButtonText: "Cancelar",
        buttonsStyling: false,
        customClass: {
          popup: "stock-access-popup",
          icon: "stock-access-icon",
          title: "stock-access-title",
          htmlContainer: "stock-access-content",
          actions: "stock-access-actions",
          confirmButton: "stock-access-confirm",
          cancelButton: "stock-access-cancel"
        },
        preConfirm: () => {
          const user = document.getElementById("stock-admin-user").value;
          const password = document.getElementById("stock-admin-password").value;
          if (!user || !password) {
            this.$swal.showValidationMessage("Ingrese usuario y contraseña");
            return false;
          }
          return { user, password };
        }
      });
      if (!result.value) return false;
      try {
        const response = await this.axios.post(this.API + "login", result.value);
        if (response.data && Number(response.data.IdUsuarioPerfil) === 1) {
          this.valorizadoAutorizado = true;
          this.getSumaValorizado();
          this.$toasted.success("Stock valorizado desbloqueado");
          return true;
        }
        this.mostrarAccesoDenegado("Solo un administrador puede visualizar y generar este reporte.");
      } catch (error) {
        this.mostrarAccesoDenegado("Las credenciales ingresadas no son correctas.");
      }
      return false;
    },
    mostrarAccesoDenegado(mensaje) {
      return this.$swal.fire({
        icon: "error",
        title: "Acceso denegado",
        text: mensaje,
        confirmButtonText: "Entendido",
        buttonsStyling: false,
        customClass: {
          popup: "stock-denied-popup",
          icon: "stock-denied-icon",
          title: "stock-denied-title",
          htmlContainer: "stock-denied-content",
          confirmButton: "stock-denied-confirm"
        }
      });
    },
    async exportarStock() {
      this.showExportOptions = true;
    },
    exportarStockActual() {
      window.open(this.hrefExcel, "_blank");
      this.showExportOptions = false;
    },
    exportarStockFechas() {
      if (!this.exportar.fechaDesde || !this.exportar.fechaHasta) {
        this.$toasted.show("Seleccione el rango de fechas");
        return;
      }
      window.open(this.hrefXlsFechas, "_blank");
      this.showExportOptions = false;
    },
    abrirImagen(producto, url) {
      this.productoNombre = producto;
      this.productoUrl = url;
      this.$refs["modalImagen"].show();
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    getSumaValorizado() {
      if (!this.valorizadoAutorizado) return;
      const urlBase = this.API + "productos/stock?idAlmacen=" + this.almacen.IdAlmacen + "&filter=" + (this.filter || "");

      // Precio Costo → usa sumaValorizadoSin=1
      this.axios.get(urlBase + "&sumaValorizadoSin=1")
        .then(response => {
          this.sumaValorizado = response.data[0].monto;
        });

      // Precio Venta → usa sumaValorizado=1
      this.axios.get(urlBase + "&sumaValorizado=1")
        .then(response => {
          this.sumaValorizadoSin = response.data[0].monto;
        });
    },
    getSumaStock() {
      this.axios
        .get(
          this.API + "productos/stock" + "?idAlmacen=" + this.almacen.IdAlmacen 
              + "&sumaStock=1" + (this.filter ? this.filter : "")
        )
        .then(response => {
          this.sumaStock = response.data[0].sumaStock;
        });
    },
    getStock(idProducto) {
      let index = this.itemsStock.findIndex(
        item => item.IdProducto == idProducto
      );
      return this.itemsStock[index] && this.itemsStock[index].stock;
    },
    focusAutocomplete(ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    limpiarClassRows() {
      let rows = this.$refs.tablaProductos.$el.rows;
      Object.keys(rows).forEach(function(key) {
        rows[key].classList.remove("table-info");
      });
    },
    setAlmacen(obj) {
      this.almacen = obj;
      this.limpiarFiltros();
      console.log(this.filter);
      Vue.nextTick(() => this.$refs.tablaProductos.refresh());
    },
    rowClick(item, index, e) {
      this.limpiarClassRows();
      e.target.parentNode.classList.add("table-info");

      this.productoSelect = item;
    },
    rowDblClick(item) {
      this.kardexFechaRango();
      this.productoSelect = item;
      this.showKardex = true;
      this.kardex.fechaDesde = this.$moment().format("YYYY-MM-01");
      this.kardex.fechaHasta = this.$moment().format("YYYY-MM-DD");

      this.axios
        .get(
          this.API +
            "productos/kardex/" +
            this.productoSelect.IdProducto +
            "?limit=0",
          {
            params: {
              idAlmacen: this.almacen.IdAlmacen,
              fechaHasta: this.kardex.fechaHasta,
              fechaDesde: this.kardex.fechaDesde
            }
          }
        )
        .then(response => {
          this.kardexSelect = response.data;
        });
    },
    kardexFechaRango() {
      if (this.kardex.fechaDesde != "" && this.kardex.fechaHasta != "") {
        this.axios
          .get(
            this.API +
              "productos/kardex/" +
              this.productoSelect.IdProducto +
              "?limit=0",
            {
              params: {
                idAlmacen: this.almacen.IdAlmacen,
                fechaHasta: this.kardex.fechaHasta,
                fechaDesde: this.kardex.fechaDesde
              }
            }
          )
          .then(response => {
            this.kardexSelect = response.data;
          });
      }
    },
    actualizarTabla() {
      this.$refs.tablaProductos.refresh();
    },
    myProvider(ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let url =
        this.API +
        this.APISTOCK +
        "?idAlmacen=" +
        this.almacen.IdAlmacen +
        "&limit=" +
        ctx.perPage +
        "&page=" +
        ctx.currentPage +
        "&filter=" +
        (ctx.filter || "") +
        "&sortBy=" +
        ctx.sortBy;
      url += "&sortDesc=" + (ctx.sortDesc ? 1 : 0);
      let promise = this.axios.get(url);

      this.hrefExcel =
        "/api/index.php/reporte/stock?idAlmacen=" +
        this.almacen.IdAlmacen +
        "&filter=" +
        (ctx.filter || "");
      console.log(this.hrefExcel);

      return promise
        .then(result => {
          this.items = result.data;
          //this.totalRows = this.items.length
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          return this.items;
        })
        .catch(error => {
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          // Returning an empty array, allows table to correctly handle busy state in case of error
          return [];
        });
    },
    limpiarFiltros() {
      this.APISTOCK = "productos";
      this.tipoStock = "";
      this.filters = {};
      this.filter = "";
      this.$refs.inputCodigoBarra.value = "";
      this.$refs.inputCategoria.setValue("");
      this.$refs.inputProducto.setValue("");
      this.$refs.inputMarca.setValue("");
      this.$refs.inputModelo.setValue("");
      this.$refs.inputColor.setValue("");
      this.$refs.inputTalla.setValue("");
      this.$refs.inputGenero.setValue("");
      this.$refs.inputBotapie.setValue("");

      this.productoSelect = {};
      this.limpiarClassRows();
      console.log(this.filter);
    },
    generateFilter(key, value) {
      this.filters[key] = value;

      let filter = "";
      for (var key in this.filters) {
        filter += "&filter[" + key + "]=" + this.filters[key];

        if (key == "stock" || key == "minimo") {
          // si la consulta se hace al stock jalar de otro endpoint
          this.APISTOCK = "productos/stock";
        }
      }

      this.filter = filter;
    },
    exportarProductos() {}
  },
  components: {
    ProductoKardex
  }
};
</script>

<style scope>
.stock-report-page{min-height:100vh;padding:12px 20px 28px;color:#112b49;background:linear-gradient(145deg,#f4f9fd,#eaf3fa 55%,#f9fcfe)}.stock-report-shell{max-width:1850px;padding:0!important}.stock-report-hero{display:flex;align-items:center;gap:13px;min-height:76px;padding:11px 20px;margin-bottom:12px;color:#fff;border-radius:20px;background:linear-gradient(118deg,#082b58,#0b568e 63%,#08a0c4);box-shadow:0 15px 34px rgba(9,52,94,.18)}.stock-report-hero__icon{display:grid;place-items:center;width:48px;height:48px;flex:0 0 48px;color:#79edff;font-size:21px;border:1px solid rgba(255,255,255,.25);border-radius:14px;background:rgba(255,255,255,.1)}.stock-report-hero small{color:#84ecff;font-size:10px;font-weight:900;letter-spacing:1.5px}.stock-report-hero h1{margin:0;font-size:25px;font-weight:900}.stock-report-hero p{margin:1px 0 0;color:rgba(239,249,255,.83);font-size:12px}.stock-report-hero>span{margin-left:auto;padding:8px 13px;border:1px solid rgba(255,255,255,.2);border-radius:999px;background:rgba(2,31,64,.25);font-size:11px;font-weight:800}.stock-report-hero>span i{margin-right:6px;color:#43e5a3}
.stock-summary-panel,.stock-search-card,.stock-filters-card,.stock-results-card{margin:0 0 12px;padding:14px 12px;border:1px solid #d9e7f2;border-radius:20px;background:rgba(255,255,255,.97);box-shadow:0 12px 30px rgba(31,69,108,.08)}.stock-value-card,.stock-general-card{display:flex;min-height:105px;align-items:center;justify-content:center;flex-direction:column;padding:13px;border-radius:17px!important;border:1px solid #d9e7f2;background:linear-gradient(145deg,#fff,#eef8fd)!important;color:#254b68!important;box-shadow:0 8px 20px rgba(31,69,108,.08)}.stock-value-card{cursor:pointer}.stock-value-card:hover{border-color:#45aada;transform:translateY(-1px)}.stock-value-card b,.stock-general-card b{font-size:12px}.stock-value-card div,.stock-general-card div{margin-top:6px;color:#0878b2;font-size:22px!important}.stock-locked{display:flex;align-items:center;gap:7px;color:#9a6370!important;font-size:14px!important;padding:7px 11px;border-radius:999px;background:#fbecef}.stock-general-card div{color:#008b58!important}.stock-report-page label{margin-bottom:5px;color:#29445f;font-size:12px;font-weight:850}.stock-report-page .form-control,.stock-report-page .custom-select{min-height:44px;color:#18304e;font-weight:700;border:1px solid #cfdeea;border-radius:11px;background:#fbfdff;box-shadow:none}.stock-report-page .form-control:focus{border-color:#1599df;box-shadow:0 0 0 4px rgba(21,153,223,.13)}
.stock-results-card{overflow:hidden;padding:0}.stock-results-card .table-detalle{margin:0;border:0!important}.stock-results-card .table-detalle th,.stock-results-card #tabla-productos thead th{padding:11px;color:#fff;background:linear-gradient(105deg,#0a376d,#087fae);border:0}.stock-results-card #tabla-productos{margin:0}.stock-results-card #tabla-productos td{padding:9px 10px;vertical-align:middle;border-color:#e3edf4}.stock-results-card #tabla-productos tbody tr:hover{background:#eef8ff}.stock-results-card .table-success{padding:5px;border-radius:8px;color:#007d49;background:#bff0d5!important;font-weight:900}.stock-results-card .table-danger{padding:5px;border-radius:8px;color:#ba1837;background:#ffcbd3!important;font-weight:900}.stock-export-button{min-height:48px;padding:0 20px!important;border:0!important;border-radius:12px!important;background:linear-gradient(135deg,#09955b,#21c178)!important;font-size:13px!important;font-weight:900!important;box-shadow:0 9px 20px rgba(9,149,91,.2)}.stock-export-button i{margin-right:7px}
.stock-report-hero{background:linear-gradient(110deg,#0a365f,#0b507f)}.stock-report-hero__icon{color:#b9e4ff}.stock-report-hero small{color:#b9e4ff}.stock-report-hero>span i{color:#8bd2ff}.stock-value-card,.stock-general-card{background:linear-gradient(145deg,#fff,#eef5fa)!important;border-color:#cfdeea}.stock-value-card:hover{border-color:#2f7eae}.stock-value-card div{color:#0b5b8c}.stock-locked{display:grid!important;place-items:center;width:48px;height:48px;padding:0!important;color:#fff!important;font-size:19px!important;border:0;border-radius:50%!important;background:linear-gradient(145deg,#0d5e91,#0a416d)!important;box-shadow:0 7px 16px rgba(10,65,109,.22)}.stock-locked:hover{transform:scale(1.05);filter:brightness(1.08)}.stock-results-card .table-detalle th,.stock-results-card #tabla-productos thead th{background:#0b5787}.stock-report-page .btn-group-toggle .btn{border-color:#0b5787!important;color:#0b5787!important;background:#fff!important}.stock-report-page .btn-group-toggle .btn.active{color:#fff!important;background:#0b5787!important}.stock-report-page .btn-secondary{border-color:#8295a5;background:#8295a5}.stock-export-button{background:linear-gradient(135deg,#0b5d8e,#0877a6)!important;box-shadow:0 9px 20px rgba(11,93,142,.2)}
.stock-kardex-modal .modal-content{overflow:hidden;border:0;border-radius:20px;box-shadow:0 24px 60px rgba(7,38,70,.28)}.stock-kardex-modal .modal-header{padding:16px 20px;color:#fff;border:0;background:linear-gradient(110deg,#0a365f,#0b5787)}.stock-kardex-modal .modal-title{font-size:20px;font-weight:900}.stock-kardex-modal .close{color:#fff;text-shadow:none;opacity:.85}.stock-kardex-modal .modal-body{padding:18px 20px 22px;background:#f7fafc}.stock-kardex-product{display:flex;align-items:center;gap:12px;margin-bottom:14px;padding:12px 14px;color:#214560;border:1px solid #d7e6f1;border-radius:14px;background:#fff}.stock-kardex-product>i{display:grid;place-items:center;width:38px;height:38px;color:#fff;border-radius:11px;background:#0b5787}.stock-kardex-product div{display:flex;min-width:0;flex-direction:column}.stock-kardex-product small{color:#1686bd;font-size:9px;font-weight:900;letter-spacing:1.2px}.stock-kardex-product strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px}.stock-kardex-filters{align-items:end;margin:0 0 14px;padding:13px 5px;border:1px solid #d9e6f0;border-radius:14px;background:#fff}.stock-kardex-generate,.stock-kardex-export{min-height:44px;border:0!important;border-radius:11px!important;font-size:12px!important;font-weight:900!important}.stock-kardex-generate{background:#0b5787!important}.stock-kardex-export{margin-top:15px;padding:0 17px!important;background:linear-gradient(135deg,#078b58,#18b774)!important}.stock-kardex-generate i,.stock-kardex-export i{margin-right:6px}
.stock-access-popup{overflow:hidden!important;padding:0 0 22px!important;border-radius:20px!important;background:#f8fbfd!important;box-shadow:0 26px 65px rgba(5,35,64,.3)!important}.stock-access-popup::before{display:block;width:100%;height:7px;background:linear-gradient(90deg,#0a365f,#0b6d9e);content:""}.stock-access-icon{width:62px!important;height:62px!important;margin:24px auto 12px!important;color:#0b5787!important;border-color:#8bc7e6!important}.stock-access-icon .swal2-icon-content{font-size:39px!important}.stock-access-title{padding:0 25px!important;color:#173b59!important;font-size:24px!important;font-weight:900!important}.stock-access-content{margin:10px 30px 0!important;color:#587086!important}.stock-access-description{margin:0 0 18px;font-size:13px;line-height:1.5}.stock-access-label{display:block;margin:10px 0 5px!important;color:#294b67!important;text-align:left;font-size:11px!important;font-weight:900!important}.stock-access-label i{width:17px;color:#0b6b9c}.stock-access-input{width:100%!important;height:48px!important;margin:0!important;padding:0 14px!important;color:#173b59!important;border:1px solid #ccdeea!important;border-radius:11px!important;background:#fff!important;font-size:14px!important;box-shadow:none!important}.stock-access-input:focus{border-color:#0b6b9c!important;box-shadow:0 0 0 4px rgba(11,107,156,.12)!important}.stock-access-actions{display:flex!important;width:calc(100% - 60px)!important;margin:20px 30px 0!important;gap:9px!important}.stock-access-confirm,.stock-access-cancel{min-height:44px;padding:0 17px;border:0;border-radius:11px;font-size:12px;font-weight:900;cursor:pointer}.stock-access-confirm{flex:1;color:#fff;background:linear-gradient(135deg,#0a4b79,#0b76a6);box-shadow:0 8px 18px rgba(11,87,135,.22)}.stock-access-cancel{color:#506779;background:#e7eef3}.stock-access-confirm:hover,.stock-access-cancel:hover{filter:brightness(1.05)}
.stock-denied-popup{overflow:hidden!important;width:410px!important;padding:0 0 23px!important;border-radius:20px!important;background:#f9fbfd!important;box-shadow:0 26px 65px rgba(5,35,64,.3)!important}.stock-denied-popup::before{display:block;width:100%;height:7px;background:linear-gradient(90deg,#8e2940,#cf4962);content:""}.stock-denied-icon{width:62px!important;height:62px!important;margin:24px auto 12px!important}.stock-denied-title{padding:0 24px!important;color:#713043!important;font-size:23px!important;font-weight:900!important}.stock-denied-content{margin:10px 30px 0!important;color:#677887!important;font-size:13px!important;line-height:1.5!important}.stock-denied-confirm{min-width:130px;min-height:44px;margin-top:8px;padding:0 18px;color:#fff;border:0;border-radius:11px;background:linear-gradient(135deg,#0a4b79,#0b76a6);font-size:12px;font-weight:900;box-shadow:0 8px 18px rgba(11,87,135,.22);cursor:pointer}.stock-denied-confirm:hover{filter:brightness(1.06)}
.stock-export-modal .modal-content{overflow:hidden;border:0;border-radius:20px;box-shadow:0 26px 70px rgba(7,39,70,.3)}.stock-export-modal .modal-header{padding:17px 21px;color:#fff;border:0;background:linear-gradient(115deg,#0a365f,#087fae)}.stock-export-modal .modal-title{font-size:19px;font-weight:900}.stock-export-modal .close{color:#fff;text-shadow:none;opacity:.9}.stock-export-modal .modal-body{padding:18px;background:#f5f9fc}.stock-export-option{display:grid;grid-template-columns:48px minmax(0,1fr) 170px;align-items:center;gap:14px;padding:17px;border:1px solid #d8e6f0;border-radius:16px;background:#fff;box-shadow:0 7px 20px rgba(25,70,105,.07)}.stock-export-option__icon{display:grid;width:46px;height:46px;place-items:center;color:#fff;border-radius:13px;background:linear-gradient(145deg,#0a5f91,#0b86b4);box-shadow:0 7px 16px rgba(11,104,150,.2)}.stock-export-option__icon.is-current{background:linear-gradient(145deg,#07875a,#17ae75)}.stock-export-option small{color:#1681b5;font-size:9px;font-weight:900;letter-spacing:1.2px}.stock-export-option h3{margin:2px 0 3px;color:#173b59;font-size:15px;font-weight:900}.stock-export-option p{margin:0;color:#718596;font-size:11px;line-height:1.4}.stock-export-option .btn{min-height:44px;border:0;border-radius:11px;background:linear-gradient(135deg,#07875a,#18b875);font-size:11px;font-weight:900;box-shadow:0 8px 18px rgba(8,139,91,.18)}.stock-export-option.is-range .btn{background:linear-gradient(135deg,#0a5688,#0b82b1)}.stock-export-option .btn i{margin-right:6px}.stock-export-divider{display:flex;align-items:center;gap:10px;margin:13px 3px;color:#8195a5;font-size:9px;font-weight:900;letter-spacing:1px;text-transform:uppercase}.stock-export-divider::before,.stock-export-divider::after{height:1px;flex:1;background:#d9e5ed;content:""}.stock-export-range-content label{margin:7px 0 4px;font-size:10px}.stock-export-range-content .form-control{min-height:39px;font-size:11px}
.stock-summary-panel{position:relative;align-items:stretch;padding:18px 16px;background:linear-gradient(120deg,#f9fcff,#f2f8fc)}.stock-summary-panel>[class*=col]{display:flex}.stock-summary-panel>[class*=col]>.suma-stock,.stock-summary-panel>[class*=col]>.form-group{width:100%}.stock-summary-panel>.col-md-2:nth-child(1) .stock-value-card{border-color:#bad7ef;background:linear-gradient(145deg,#fafdff,#e4f1fb)!important;box-shadow:0 10px 24px rgba(33,112,167,.12)}.stock-summary-panel>.col-md-2:nth-child(2) .stock-value-card{border-color:#b9e2dc;background:linear-gradient(145deg,#fbfffe,#e3f5f2)!important;box-shadow:0 10px 24px rgba(22,135,117,.11)}.stock-summary-panel>.col-md-2:nth-child(3) .stock-general-card{border-color:#b9e5ce;background:linear-gradient(145deg,#fcfffd,#e4f7eb)!important;box-shadow:0 10px 24px rgba(20,145,82,.11)}.stock-summary-panel>.col-md-2:nth-child(1) .stock-value-card::before,.stock-summary-panel>.col-md-2:nth-child(2) .stock-value-card::before,.stock-summary-panel>.col-md-2:nth-child(3) .stock-general-card::before{position:absolute;top:0;right:18px;left:18px;height:4px;border-radius:0 0 5px 5px;background:#267eb4;content:""}.stock-summary-panel>.col-md-2:nth-child(2) .stock-value-card::before{background:#168c79}.stock-summary-panel>.col-md-2:nth-child(3) .stock-general-card::before{background:#149052}.stock-value-card,.stock-general-card{position:relative;overflow:hidden}.stock-summary-panel>.col-md-2:nth-child(2) .stock-locked{background:linear-gradient(145deg,#138d7a,#086557)!important;box-shadow:0 7px 16px rgba(19,141,122,.22)}.stock-general-card div{display:inline-flex;align-items:center;justify-content:center;min-width:92px;padding:5px 13px;border-radius:999px;background:#d8f4e4;color:#087b46!important}.stock-summary-panel legend,.stock-summary-panel .col-form-label{height:auto!important;margin:0 0 6px!important;padding:0!important;color:#38536b!important;border:0!important;background:transparent!important;font-size:11px!important;font-weight:900!important}.stock-summary-panel .form-group{display:flex;justify-content:center;flex-direction:column;margin-bottom:0}.stock-summary-panel .form-control,.stock-summary-panel .custom-select{background:#fff}.stock-summary-panel .btn-secondary{min-height:44px;border:0!important;border-radius:10px!important;background:#71889a!important;font-size:12px;font-weight:800}.stock-summary-panel .btn-group-toggle{display:flex;width:100%}.stock-summary-panel .btn-group-toggle .btn{display:grid;min-height:42px;place-items:center;flex:1;padding:0 10px;border-color:#b7cedf!important;color:#315b78!important}.stock-summary-panel .btn-group-toggle .btn.active{border-color:#0b6697!important;background:linear-gradient(135deg,#0a4b79,#0b76a6)!important;color:#fff!important;box-shadow:0 5px 13px rgba(11,87,135,.18)}.stock-report-hero>span{background:rgba(255,255,255,.1);box-shadow:inset 0 0 0 1px rgba(255,255,255,.05)}
.stock-summary-panel{align-items:initial;padding:14px 12px;background:rgba(255,255,255,.97)}.stock-summary-panel>[class*=col]{display:block}.stock-summary-panel>[class*=col]>.suma-stock,.stock-summary-panel>[class*=col]>.form-group{width:auto}.stock-summary-panel .form-group{display:block;margin-bottom:1rem}.stock-summary-panel .btn-group-toggle{display:inline-flex;width:auto}.stock-summary-panel .btn-group-toggle .btn{min-width:102px;flex:0 0 auto}.stock-locked{width:38px!important;height:38px!important;font-size:14px!important;box-shadow:0 5px 12px rgba(10,65,109,.18)!important}.stock-summary-panel>.col-md-2:nth-child(2) .stock-locked{box-shadow:0 5px 12px rgba(19,141,122,.18)!important}.stock-value-card:hover .stock-locked{transform:none}.stock-value-card,.stock-general-card{min-height:105px}
#tabla-productos tr {
  cursor: pointer;
}
.table-detalle {
  height: 80px;
}
.table-detalle th {
  color: #fff;
  background-color: #6c757d;
}
.table-detalle tbody td {
  font-size: 18px;
  font-weight: bold;
  color: #2980b9;
}
.table-detalle thead th:first-child {
  max-width: 300px;
}
.suma-stock {
  background: #a6ccd2;
  color: #fff;
}
.suma-stock div {
  font-size: 26px;
  font-weight: bold;
}
@media (min-width: 992px) {
  .modal-xl .modal-dialog {
    max-width: 1200px;
  }
}
@media(max-width:991px){.stock-report-hero>span{display:none}.stock-summary-panel>[class*=col]{max-width:50%;flex:0 0 50%;margin-bottom:8px}}
@media(max-width:575px){.stock-report-page{padding:8px}.stock-report-hero{padding:10px 13px;border-radius:16px}.stock-report-hero p{display:none}.stock-report-hero h1{font-size:20px}.stock-summary-panel>[class*=col]{max-width:100%;flex:0 0 100%}}
@media(max-width:767px){.stock-export-option{grid-template-columns:44px 1fr}.stock-export-option .btn{grid-column:1/-1}.stock-export-modal .modal-body{padding:12px}}
</style>
