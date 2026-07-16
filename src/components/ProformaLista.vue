<template>
    <div>
        <b-button block @click="show=true" variant="primary" style="font-weight: bold;" class="btn-pos btn-accion"><i class="fas fa-file-alt"></i>Orden de Pedido</b-button>
               
        <b-form ref="formProformaModal" >    
        
            <b-modal id="modalProformas" 
                title="Buscar Orden"
                class="modalOrdenCompra"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">     
                <b-tabs pills card>
                  <b-tab title="Proformas" active>
                  <b-row class="align-items-center mb-2">
                    <b-col cols="8">
                      <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                    </b-col>
                    <b-col cols="4">
                      <b-input-group prepend="Mostrar">
                        <b-form-select
                          v-model="perPage"
                          :options="opcionesPorPagina"
                          @change="currentPage = 1"
                        />
                      </b-input-group>
                    </b-col>
                  </b-row>

                  <div class="table-responsive table-proforma-scroll">
                    <b-table 
                        v-if="show"
                        id="table-proformas" 
                        ref="tableProformasPendientes"
                        striped hover 
                        :busy.sync="isBusy" 
                        :filter="filter"
                        :items="myProvider" 
                        :fields="fields"
                        :per-page="perPage"
                        :current-page="currentPage">

                      <template slot="EsFavoritoFlag" slot-scope="data">
                        <i @click="cambiarFavorito(data.item,0)" class="fa fa-star" style="color: #F57F17; cursor: pointer;" aria-hidden="true" v-if="data.item.EsFavoritoFlag"></i>
                        <i @click="cambiarFavorito(data.item,1)" class="far fa-star" style="cursor: pointer;" aria-hidden="true" v-else></i>
                      </template>
                      <template slot="update" slot-scope="data">
                        <b-button @click="selectProformas(data.item)" variant="success"><i class="fas fa-pencil-alt"></i></b-button>
                      </template>

                      <template slot="ver" slot-scope="data">
                        <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma" variant="primary" target="_blank">Ver</b-button>
                      </template>

                      <template slot="Total" slot-scope="data">
                        <b>{{ data.item.Total | moneda }}</b>
                      </template>

                      <template slot="Editar" slot-scope="data">
                        <b-button @click="abrirEditar(data.item)" variant="warning" size="sm">
                          <i class="fas fa-edit"></i>
                        </b-button>
                      </template>

                      <template slot="Anular" slot-scope="data">
                        <b-button @click="eliminarProf(data.item)" variant="danger"><i class="fas fa-times"></i></b-button>
                      </template>

                      <template slot="verTicket" slot-scope="data">
                        <b-button :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma" variant="primary" target="_blank">Ver Ticket</b-button>
                      </template>

                    </b-table>
                  </div>
                    <b-row>
                    <b-col md="6" class="my-1">
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                    </b-col>
                    </b-row>
                <div slot="modal-footer" >
                    <!--<b-btn size="sm" variant="success" type="submit">
                        Guardar
                    </b-btn>-->
                    <b-btn size="sm" variant="secondary" @click="show=false">
                        Cerrar
                    </b-btn>
                </div>
                  </b-tab>

                  <b-tab title="Ordenes Facturadas">
                    <b-row class="align-items-center mb-2">
                      <b-col cols="8">
                        <b-form-input v-model="filterFav" placeholder="Buscar" @input="currentPageFav = 1" />
                      </b-col>
                      <b-col cols="4">
                        <b-input-group prepend="Mostrar">
                          <b-form-select
                            v-model="perPageFav"
                            :options="opcionesPorPagina"
                            @change="currentPageFav = 1"
                          />
                        </b-input-group>
                      </b-col>
                    </b-row>

                    <div class="tablaScroll">
                      <b-table
                        v-if="show"
                        id="table-proformas-facturadas"
                        ref="tableProformasFacturadas"
                        striped hover
                        :busy.sync="isBusyFav"
                        :filter="filterFav"
                        :items="myProviderFacturadas"
                        :fields="fieldsFavoritas"
                        :per-page="perPageFav"
                        :current-page="currentPageFav">

                        <template slot="ver" slot-scope="data">
                          <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma" variant="primary" target="_blank">Ver</b-button>
                        </template>

                        <template slot="Total" slot-scope="data">
                          <b>{{ data.item.Total | moneda }}</b>
                        </template>

                        <template slot="verTicket" slot-scope="data">
                          <b-button :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma" variant="primary" target="_blank">Ver Ticket</b-button>
                        </template>
                        <template slot="Estado" slot-scope="data">
                          <b-button variant="danger" target="_blank">Facturado</b-button>
                        </template>
                        <template slot="Facturado" slot-scope="data">
                          <b-button
                            v-if="data.item.IdDocVenta"
                            :href="'/imprimir/index.php?IdDocVenta=' + data.item.IdDocVenta"
                            variant="info"
                            target="_blank"
                          >
                            <i class="fas fa-file-invoice"></i> Ver Comprobante
                          </b-button>
                          <span v-else class="text-muted">Sin comprobante</span>
                        </template>
                      </b-table>
                    </div>

                    <b-pagination
                      v-model="currentPageFav"
                      :total-rows="totalRowsFav"
                      :per-page="perPageFav"
                    ></b-pagination>
                  </b-tab>
                </b-tabs>      
                    
            
            </b-modal>

            <b-modal
              id="modalEditarProforma"
              title="Editar Proforma"
              size="lg"
              v-model="showEditar"
              header-bg-variant="dark"
              headerTextVariant="light"
              body-bg-variant="light"
              body-text-variant="dark"
            >
              <div v-if="proformaEditar">
                <h5>Productos</h5>

                <!-- 🆕 Buscador para añadir producto -->
                <b-row class="mb-2">
                  <b-col cols="12">
                    <autocomplete
                      ref="autocompleteProductoEditar"
                      id="autocompleteProductoEditar"
                      :url="APIProductosEditar"
                      :customParams="{ sortBy: 'stock', sortDesc: 1, limit: '25' }"
                      anchor="Producto"
                      label="writer"
                      placeholder="Buscar producto para agregar..."
                      :on-select="agregarProductoEditar"
                      :onFocus="focusAutocompleteEditar"
                      :classes="{ input: 'form-control' }"
                    ></autocomplete>
                  </b-col>
                </b-row>

                <b-table :items="productosEditar" :fields="camposProductosEditar" small>
                  <template slot="Cantidad" slot-scope="row">
                    <b-form-input
                      type="number" min="0"
                      v-model.number="row.item.Cantidad"
                      @input="recalcularSubtotal(row.item)"
                    />
                  </template>
                  <template slot="Precio" slot-scope="row">
                    <b-form-input
                      type="number" step="0.01" min="0"
                      v-model.number="row.item.Precio"
                      @input="recalcularSubtotal(row.item)"
                    />
                  </template>
                  <template slot="presentacion" slot-scope="row">
                    <b-form-select
                      v-if="row.item.tienePresentacion"
                      v-model="row.item.PresentacionSeleccionada"
                      :options="opcionesPresentacion(row.item)"
                      @change="cambiarPresentacionEditar(row.item, $event)"
                    />
                    <span v-else>{{ row.item.presentacion }}</span>
                  </template>
                  <template slot="Descuento" slot-scope="row">
                    <b-form-input
                      type="number" step="0.01" min="0"
                      v-model.number="row.item.Descuento"
                      @input="recalcularSubtotal(row.item)"
                    />
                  </template>
                  <template slot="Subtotal" slot-scope="row">
                    {{ row.item.Subtotal | moneda }}
                  </template>
                  <template slot="quitar" slot-scope="row">
                    <b-button size="sm" variant="danger" @click="quitarProductoEditar(row.index)">
                      <i class="fas fa-trash"></i>
                    </b-button>
                  </template>
                </b-table>

                <div class="text-right">
                  <strong>Total: {{ totalEditar | moneda }}</strong>
                </div>
              </div>

              <div slot="modal-footer">
                <b-btn variant="secondary" @click="showEditar = false">Cancelar</b-btn>
                <b-btn variant="success" @click="guardarEdicion">Guardar Cambios</b-btn>
              </div>
            </b-modal>
        </b-form>
    </div>
</template>
<script>
import config from "@/store/config.js";
import { mapActions } from "vuex";

export default {
  mounted() {
    this.$emit("mounted", this.$refs.formProformaModal);
  },
  watch: {
    items() {
      this.axios.get(this.API + "proformas/count").then(response => {
        this.totalRows = response.data.total;
      });
      this.axios.get(this.API + "proformas/facturadas/count").then(response => {
        this.totalRowsFav = response.data.total;
      });
    }
  },
  data() {
    return {
      API: config.API,
      show: false,
      search: "",
      filter: "",
      filterFav: "",
      items: [],
      isBusy: false,
      showEditar: false,
      proformaEditar: null,
      productosEditar: [],
      camposProductosEditar: [
        { key: "Descripcion", label: "Producto" },
        { key: "presentacion", label: "Presentación" },
        { key: "Cantidad", label: "Cant." },
        { key: "Precio", label: "Precio" },
        { key: "Descuento", label: "Desc." },
        { key: "Subtotal", label: "Subtotal" },
        { key: "quitar", label: "" }
      ],
      isBusyFav: false,
      opcionesPorPagina: [
        { value: 5, text: "5" },
        { value: 10, text: "10" },
        { value: 25, text: "25" },
        { value: 50, text: "50" },
        { value: 100, text: "100" }
      ],
      fields: [
        { key: "ver",           label: "Ver",       thStyle: { width: "50px" } },
        { key: "verTicket",     label: "Ticket",    thStyle: { width: "80px" } },
        { key: "update",        label: "Update",    thStyle: { width: "60px" } },
        { key: "Editar",        label: "Editar",    thStyle: { width: "50px" } },
        { key: "Total",         label: "Total",     thStyle: { width: "70px" } },
        { key: "Cliente",       label: "Cliente",   thStyle: { width: "180px" } },
        // { key: "EsFavoritoFlag",label: "Favorito",  thStyle: { width: "80px" } },
        { key: "IdProforma",    label: "Id",        thStyle: { width: "60px" } },
        { key: "Anio",          label: "Año",       thStyle: { width: "50px" } },
        { key: "Numero",        label: "Número",    thStyle: { width: "60px" } },
        { key: "DniRuc",        label: "Documento", thStyle: { width: "80px" } },
        { key: "FechaReg",      label: "Fecha",     thStyle: { width: "120px" } },
        { key: "UsuarioReg",    label: "Empleado",  thStyle: { width: "80px" } },
        { key: "Anular",        label: "Anular",    thStyle: { width: "50px" } }
      ],
      fieldsFavoritas: [
        // { key: "EsFavoritoFlag", label: "Favorito" },
        "ver",
        "verTicket",
        { key: "Facturado",label: "Comprobante"},
        { key: "Estado",label: "Estado"},
        { key: "Total", label: "Total" },
        "Cliente",
        { key: "IdProforma", label: "Id" },
        // { key: "Descripcion" },
        { key: "Anio", label: "Año" },
        "Numero",
        { key: "DniRuc", label: "Documento" },
        "FechaReg",
        { key: "UsuarioReg", label: "Empleado" }
      ],
      currentPageFav: 1,
      totalRows: 50,
      totalRowsFav: 50,
      perPage: 50,
      perPageFav: 50,
      currentPage: 1,

      // 🆕 Buscador de productos dentro de Editar Proforma
      buscarProductoTexto: "",
      resultadosProductoEditar: [],
      buscandoProductoEditar: false,
      _debounceBuscarProdEditar: null
    };
  },
  computed: {
    totalEditar() {
    return this.productosEditar.reduce((acc, p) => acc + (p.Subtotal || 0), 0);
    },
    APIProductosEditar() {
      const IdAlmacen = this.idAlmacen;
      return this.API + "productos/puntoventa/stock/" + IdAlmacen;
    }
  },
  props: ["idAlmacen"],
  methods: {
    ...mapActions({
      add: "agregarProducto",
      obtenerStok: "obtenerStock",
      limpiarCarrito: "limpiarCarrito"
    }),
    cargarFavoritas() {
      this.lstProformasFavoritas = [];
      this.axios(`${this.API}proformas/favoritas`).then(response => {
        if (response.data) {
          this.lstProformasFavoritas = response.data;
        }
      });
    },
    async cambiarFavorito(item, EsFavoritoFlag) {
      item.EsFavoritoFlag = EsFavoritoFlag;
      await this.axios.put(`${this.API}proforma/cambiarfavorito`, item);
      this.cargarFavoritas();
    },
    selectProformas(proforma) {
      this.$emit("guardarDatosProforma", proforma.IdProforma);
      this.axios
        .get(this.API + "proformas/detalle", {
          params: {
            idProforma: proforma.IdProforma,
            IdAlmacen: sessionStorage.getItem("IdAlmacen")
          }
        })
        .then(response => {
          response.data.map(item => {
            this.add([
              item.IdProducto,
              () => {
                this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
              },
              {
                setPrecio: true,
                setPreOr: item.Precio,
                cantidad: item.Cantidad,
                descuento: item.Descuento
              }
            ]);
          });
          proforma.productos = response.data;
          this.filter = "";
          this.show = false;

          this.$emit("onSelectProforma", proforma);
          this.limpiarCarrito();
        });
    },
    abrirEditar(item) {
      this.axios
        .get(this.API + "proformas/detalle", {
          params: {
            idProforma: item.IdProforma,
            IdAlmacen: sessionStorage.getItem("IdAlmacen")
          }
        })
        .then(async response => {
          this.proformaEditar = { ...item };

          // 👇 por cada producto, traemos su catálogo de presentaciones
          this.productosEditar = await Promise.all(
            response.data.map(async p => {
              let pres = [];
              try {
                const stockRes = await this.axios(
                  this.API + "productos/puntoventa/stock",
                  {
                    params: {
                      idProductos: [p.IdProducto],
                      idAlmacen: this.idAlmacen || sessionStorage.getItem("IdAlmacen") || 1
                    }
                  }
                );
                const prodStock = stockRes.data[0];
                if (prodStock && prodStock.PreciosPorProducto) {
                  pres = JSON.parse(prodStock.PreciosPorProducto).filter(
                    v => v.cantidad > 0
                  );
                }
              } catch (e) {
                pres = [];
              }

              // 🆕 Parsear la presentación guardada en BD (viene como JSON string o null)
              let presentacionActual = null;
              try {
                presentacionActual = p.Presentacion ? JSON.parse(p.Presentacion) : null;
              } catch (e) {
                presentacionActual = null;
              }

              // 🆕 Buscar dentro de las opciones (`pres`) la que coincide con lo guardado
              let presInicial = null;
              try {
                if (presentacionActual && pres.length) {
                  presInicial = pres.find(x =>
                    String(x.cantidad) === String(presentacionActual.cantidad) &&
                    String(x.nombre || "").trim() === String(presentacionActual.nombre || "").trim()
                  ) || null;
                }
              } catch (e) {
                presInicial = null;
              }

              return {
                ...p,
                Descripcion: p.Producto || p.NombreProducto || p.Descripcion || "",
                presentacion: presentacionActual ? String(presentacionActual.nombre || "").trim() : "UNIDAD",
                esPresentacion: !!presentacionActual,
                PreciosPorProducto: pres,
                tienePresentacion: pres.length > 0,
                PrecioUnitario: p.Precio, // 🆕 precio base actual, usado al volver a "UNIDAD"
                PresentacionSeleccionada: presInicial, // 🆕 objeto de presentación o null
                Subtotal: this.calcularSubtotalItem(p)
              };
            })
          );

          this.buscarProductoTexto = "";
          this.resultadosProductoEditar = [];
          this.showEditar = true;
        })
        .catch(err => {
          console.log(err); // 🆕 para ver el error real en consola si vuelve a fallar
          this.$toasted.error("No se pudo cargar el detalle de la proforma");
        });
    },

    // 🆕 Busca productos con stock por texto (nombre, categoría, marca, forma farmacéutica)
    buscarProductosEditar() {
      clearTimeout(this._debounceBuscarProdEditar);

      if (!this.buscarProductoTexto || this.buscarProductoTexto.length < 2) {
        this.resultadosProductoEditar = [];
        return;
      }

      this._debounceBuscarProdEditar = setTimeout(() => {
        this.buscandoProductoEditar = true;
        const IdAlmacen = this.idAlmacen; // 👈 cambio aquí también

        this.axios
          .get(this.API + `productos/puntoventa/stock/${IdAlmacen}`, {
            params: { q: this.buscarProductoTexto }
          })
          .then(response => {
            // el endpoint devuelve false si no hay resultados
            this.resultadosProductoEditar = response.data ? response.data : [];
          })
          .catch(() => {
            this.resultadosProductoEditar = [];
          })
          .finally(() => {
            this.buscandoProductoEditar = false;
          });
      }, 350);
    },

  focusAutocompleteEditar() {
    const el = document.getElementById("autocompleteProductoEditar");
    if (!el) return;

    const dblClickEvent = new MouseEvent("dblclick");

    if (el.value !== "") {
      // Si tenía texto, lo limpiamos y notificamos el cambio
      el.value = "";
      el.dispatchEvent(new Event("input", { bubbles: true }));
      // Esperamos un tick para no disparar la búsqueda dos veces
      this.$nextTick(() => {
        el.dispatchEvent(dblClickEvent);
      });
    } else {
      // Ya está vacío, con el dblclick alcanza para abrir/buscar
      el.dispatchEvent(dblClickEvent);
    }
  },

    // 🆕 Agrega el producto seleccionado del buscador a la tabla de edición
    agregarProductoEditar(prod) {
      if (!prod || !prod.IdProducto) return;

      if (prod.stock <= 0) {
        this.$toasted.error("Producto sin stock disponible");
        return;
      }

      const existente = this.productosEditar.find(p => p.IdProducto === prod.IdProducto);

      if (existente) {
        if (existente.Cantidad + 1 > prod.stock) {
          this.$toasted.error("No hay suficiente stock para agregar más unidades");
          return;
        }
        existente.Cantidad += 1;
        this.recalcularSubtotal(existente);
      } else {
        const nuevo = {
          IdProducto: prod.IdProducto,
          Descripcion: prod.Producto,
          Cantidad: 1,
          Precio: prod.PrecioContado,
          Descuento: 0,
          Subtotal: 0,
          stockDisponible: prod.stock
        };
        this.recalcularSubtotal(nuevo);
        this.productosEditar.push(nuevo);
      }

      // 🆕 limpia el input del autocomplete, igual patrón que focusAutocomplete()
      this.$nextTick(() => {
        const el = document.getElementById("autocompleteProductoEditar");
        if (el) {
          el.value = "";
          el.dispatchEvent(new Event("input", { bubbles: true }));
        }
      });
    },

    calcularSubtotalItem(item) {
      return (item.Cantidad * item.Precio) - (item.Descuento || 0);
    },
    recalcularSubtotal(item) {
      if (item.stockDisponible !== undefined && item.Cantidad > item.stockDisponible) {
        this.$toasted.error(`Solo hay ${item.stockDisponible} unidades disponibles`);
        item.Cantidad = item.stockDisponible;
      }
      item.Subtotal = item.Cantidad * item.Precio - (item.Descuento || 0);
    },

    opcionesPresentacion(item) {
      return [
        { value: null, text: "UNIDAD" },
        ...item.PreciosPorProducto.map(pres => ({
          value: pres,
          text: `${String(pres.nombre || "").trim()} (${pres.cantidad} und - S/ ${pres.precio})`
        }))
      ];
    },

    cambiarPresentacionEditar(item, seleccion) {
      if (!seleccion) {
        item.presentacion = "UNIDAD";
        item.esPresentacion = false;
        item.PresentacionSeleccionada = null;
        item.Precio = item.PrecioUnitario;
      } else {
        item.presentacion = String(seleccion.nombre || "").trim();
        item.esPresentacion = true;
        item.PresentacionSeleccionada = seleccion;
        item.Precio = parseFloat(seleccion.precio);
      }
      this.recalcularSubtotal(item);
    },
    quitarProductoEditar(index) {
      const producto = this.productosEditar[index];

      this.$swal({
        target: document.getElementById("conten"),
        title: "¿Quitar producto?",
        html: `Se quitará <strong>${producto.Descripcion}</strong> de la proforma`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Sí, quitar",
        cancelButtonText: "Cancelar"
      }).then(result => {
        if (result.isConfirmed) {
          this.productosEditar.splice(index, 1);
          this.$toasted.info("Producto quitado");
        }
      });
    },

    guardarEdicion() {
      const payload = {
        productos: this.productosEditar.map(p => ({
          IdProducto: p.IdProducto,
          Cantidad: p.Cantidad,
          Precio: p.Precio,
          Descuento: p.Descuento,
          Presentacion: p.PresentacionSeleccionada || null
        }))
      };

      this.axios
        .put(`${this.API}proforma/actualizar/${this.proformaEditar.IdProforma}`, payload)
        .then(res => {
          if (res.data) {
            this.$toasted.info("¡PROFORMA ACTUALIZADA!");
            this.showEditar = false;
            // 👇 solo refresca si la tabla existe
            if (this.$refs.tableProformasPendientes) {
              this.$refs.tableProformasPendientes.refresh();
            }
          }
        })
        .catch(err => {
          console.log(err);
          this.$toasted.error("Ha ocurrido un error al actualizar");
        });
    },
    myProviderFacturadas(ctx) {
      let promise = this.axios.get(
        this.API +
          "proformas/facturadas?limit=" +
          ctx.perPage +
          "&page=" +
          ctx.currentPage +
          "&filter=" +
          (ctx.filter || "")
      );

      return promise
        .then(result => {
          return result.data;
        })
        .catch(() => []);
    },
    myProvider(ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let IdAlmacen = sessionStorage.getItem("IdAlmacen");
      let promise = this.axios.get(
        this.API +
          "proformas?limit=" +
          ctx.perPage +
          "&page=" +
          ctx.currentPage +
          "&IdAlmacen="+IdAlmacen +
          "&filter=" +
          ctx.filter
      );

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
    eliminarProf(data) {
      this.$swal({
        target: document.getElementById("conten"),
        title:
          "Se anulará la proforma: <strong>" +
          data.Anio +
          " - " +
          data.Numero +
          "</strong> del  cliente <strong>" +
          data.Cliente +
          "</strong>",
        text: "¿Está seguro?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, anular",
        cancelButtonText: "No, atrás"
      }).then(result => {
        if (result.isConfirmed) {
          this.axios
            .delete(this.API + "proforma/anular/" + data.IdProforma)
            .then(res => {
              if (res.data) {
                this.$toasted.info("¡PROFORMA ANULADA!");
                this.$refs.tableProformasPendientes.refresh();
              }
            })
            .catch(err => {
              console.log(err);
              this.$toasted.error("Ha ocurrido un error");
            });
        }
      });
    }
  }
};
</script>
<style>
#modalProformas .modal-dialog {
  max-width: 85vw !important;
  width: 98vw !important;
  margin: 1vh auto !important;
}

#modalProformas .modal-content {
  height: 96vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

#modalProformas .modal-header {
  flex-shrink: 0;
}

#modalProformas .modal-footer {
  flex-shrink: 0;
}

#modalProformas .modal-body {
  flex: 1;
  overflow: hidden;
  padding: 0.5rem;
  display: flex;
  flex-direction: column;
}

/* ── Tabs ── */
#modalProformas .tabs {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

#modalProformas .tab-content {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

#modalProformas .tab-pane.active {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Bootstrap-Vue genera .card y .card-body dentro de b-tabs con card */
#modalProformas .card {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  border: none;
}

#modalProformas .card-body {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  padding: 0.5rem;
}

/* ── Buscador: no crece ── */
#modalProformas .form-control,
#modalProformas .input-group {
  flex-shrink: 0;
  margin-bottom: 0.5rem;
}

/* ── Paginación: no crece ── */
#modalProformas .pagination,
#modalProformas nav {
  flex-shrink: 0;
  margin-top: 0.5rem;
}

/* ── Contenedor tabla con ambos scrolls ── */
.table-proforma-scroll {
  flex: 1;
  overflow-x: auto !important;
  overflow-y: auto !important;
  -webkit-overflow-scrolling: touch;
  min-height: 0;
}

/* ── Tabla con layout fijo para respetar anchos de cabeceras ── */
.table-proforma-scroll > table,
.table-proforma-scroll .table {
  min-width: 1100px !important;
  width: 100% !important;
  table-layout: fixed !important;
}

/* ── Todas las celdas sin salto por defecto ── */
.table-proforma-scroll td,
.table-proforma-scroll th {
  white-space: normal !important;
  word-break: break-word;
  overflow: visible;
}

/* ── Descripción permite salto de línea ── */
.td-descripcion {
  white-space: normal !important;
  word-break: break-word !important;
  overflow: visible !important;
  text-overflow: unset !important;
}


.tablaScroll {
  max-height: 50vh;
  overflow-y: auto;
  position: relative;
}

@media (max-width: 768px) {
  #modalProformas .modal-dialog {
    width: 100vw !important;
    max-width: 100vw !important;
    margin: 0 !important;
  }

  #modalProformas .modal-content {
    height: 100vh;
    border-radius: 0;
  }
}
</style>