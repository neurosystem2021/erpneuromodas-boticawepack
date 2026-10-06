<template>
  <b-modal
    v-model="visible"
    no-close-on-backdrop
    no-close-on-esc
    hide-footer
    dialog-class="modal-editar-mov"
    :title="`Editar movimiento ${tituloMov}`"
  >
    <b-row>
      <!-- PROVEEDOR -->
      <b-col cols="4" class="position-relative">
        <label class="mb-1">Proveedor</label>
        <input
          type="text"
          class="form-control"
          v-model="textoProveedor"
          placeholder="Buscar por RUC o razón social..."
          autocomplete="off"
          @focus="mostrarListaProv = true"
          @input="onInputProveedor()"
          @blur="cerrarListaProv()"
          @keydown.esc="cerrarListaProv()"
        >
        <div v-if="mostrarListaProv" class="lista-prov list-group">
          <button
            type="button"
            class="list-group-item list-group-item-action py-1"
            v-for="p in proveedoresFiltrados"
            :key="p.IdProveedor"
            @mousedown.prevent="seleccionarProveedor(p)"
          >{{ p.nombre }}</button>
          <div v-if="!proveedoresFiltrados.length" class="list-group-item text-muted py-1">Sin resultados</div>
        </div>
      </b-col>
      <b-col cols="2">
        <label class="mb-1">Serie</label>
        <input
          type="text"
          class="form-control"
          v-model="editar.Serie"
          maxlength="10"
          @input="editar.Serie = (editar.Serie || '').toUpperCase()"
        >
      </b-col>
      <b-col cols="2">
        <label class="mb-1">Número</label>
        <input type="text" class="form-control" v-model="editar.Numero" maxlength="20">
      </b-col>
      <b-col cols="2">
        <label class="mb-1">Fecha</label>
        <input type="date" class="form-control" v-model="editar.Fecha">
      </b-col>
      <b-col cols="2">
        <label class="mb-1">Forma de pago</label>
        <b-form-checkbox v-model="editar.EsCredito" switch class="mt-1">
          {{ editar.EsCredito ? "Crédito" : "Contado" }}
        </b-form-checkbox>
      </b-col>
    </b-row>
    <b-row class="mt-2" v-if="editar.EsCredito">
    <b-col cols="2" offset="10">
        <label class="mb-1">Vence el</label>
        <input type="date" class="form-control" v-model="editar.FechaVenCredito">
    </b-col>
    </b-row>
    <hr>

    <!-- BUSCADOR DE PRODUCTOS -->
    <b-row>
      <b-col cols="12" class="position-relative">
        <label class="mb-1">Agregar producto</label>
        <b-input-group>
          <input
            ref="inputBusqueda"
            type="text"
            class="form-control"
            v-model="busquedaProducto"
            placeholder="Agregar Producto"
            autocomplete="off"
            @focus="mostrarListaProd = true"
            @blur="cerrarListaProd()"
            @keydown.down.prevent="moverSeleccion(1)"
            @keydown.up.prevent="moverSeleccion(-1)"
            @keydown.enter.prevent="enterBusqueda()"
            @keydown.esc="cerrarListaProd()"
          >
          <b-input-group-append>
            <b-button variant="info" :disabled="buscando" @click="buscarProductos()">
              <i class="fa" :class="buscando ? 'fa-spinner fa-spin' : 'fa-search'" aria-hidden="true"></i> Buscar
            </b-button>
          </b-input-group-append>
        </b-input-group>

        <div
          v-if="mostrarListaProd && (resultadosProductos.length || buscando || busquedaRealizada)"
          class="lista-prod"
        >
          <!-- Paso 2: elegir presentación -->
          <template v-if="prodElegido">
            <div class="item-prod-titulo">
              <b>{{ prodElegido.Producto }}</b> — elija la presentación
            </div>
            <div
              v-for="(opt, i) in opcionesPresentacion(prodElegido)"
              :key="'pr' + i"
              class="item-prod"
              :class="{ activo: i === indiceActivo }"
              @mousedown.prevent="elegirPresentacion(opt)"
              @mouseenter="indiceActivo = i"
            >{{ opt.text }}</div>
          </template>

          <!-- Paso 1: lista de productos -->
          <template v-else>
            <div
              v-for="(prod, i) in resultadosProductos"
              :key="prod.IdProducto"
              class="item-prod"
              :class="{ activo: i === indiceActivo }"
              @mousedown.prevent="elegirProducto(prod)"
              @mouseenter="indiceActivo = i"
            >
              <b>{{ prod.CodigoBarra || 0 }}</b> /
              <b>{{ prod.Producto }}</b> /
              {{ prod.ProductoFormaFarmaceutica }} /
              {{ prod.ProductoMarca }}
            </div>
            <div v-if="!resultadosProductos.length" class="item-prod-vacio">
              {{ buscando ? "Buscando..." : "Sin resultados para la búsqueda." }}
            </div>
          </template>
        </div>
      </b-col>
    </b-row>

    <!-- CARRITO -->
    <b-row class="mt-3">
      <b-col cols="12">
        <table class="table table-sm table-striped">
          <thead>
            <tr>
                <th>Cod. Barra</th>
                <th>Producto</th>
                <th>Marca</th>
                <th>Presentación</th>
                <th style="width: 120px;">Lote</th>
                <th style="width: 140px;">Vence</th>
                <th style="width: 120px;">Reg. San.</th>
                <th style="width: 110px;">Cantidad</th>
                <th style="width: 120px;">Precio</th>
                <th style="text-align: center;">Unid. a stock</th>
                <th style="text-align: center;">IGV</th>
                <th style="text-align: end;">Total</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <tr v-if="carrito.length == 0">
                <td colspan="13" class="text-center text-muted">El carrito está vacío. Busque y añada productos.</td>
            </tr>
            <tr v-for="(linea, i) in carrito" :key="linea.IdProducto + '|' + (linea.IdPresentacion || 'U')">
                <td>{{ linea.CodigoBarra }}</td>
                <td>{{ linea.Producto }}</td>
                <td>{{ linea.ProductoMarca }}</td>
                <td>
                <select
                    v-if="(linea.presentaciones || []).length"
                    class="form-control form-control-sm"
                    :value="linea.IdPresentacion || ''"
                    @change="cambiarPresentacion(linea, $event.target.value)"
                >
                    <option value="">Sin presentación (unidad)</option>
                    <option v-for="p in linea.presentaciones" :key="p.IdPresentacion" :value="p.IdPresentacion">
                    {{ p.Presentacion }} (x{{ p.CantidadPresentacion }})
                    </option>
                </select>
                <span v-else>Sin presentación</span>
                </td>
                <td><input type="text" class="form-control form-control-sm" disabled v-model="linea.Lote" maxlength="50"></td>
                <td><input type="date" class="form-control form-control-sm" disabled v-model="linea.FechaVen"></td>
                <td><input type="text" class="form-control form-control-sm" disabled v-model="linea.Registro" maxlength="50"></td>
                <td><input type="number" min="0" step="any" class="form-control form-control-sm" v-model.number="linea.Cantidad"></td>
                <td><input type="number" min="0" step="any" class="form-control form-control-sm" v-model.number="linea.Precio"></td>
                <td style="text-align: center;">{{ unidadesStock(linea) }}</td>
                <td style="text-align: center;">{{ linea.TieneIgv == 1 ? "Sí" : "No" }}</td>
                <td style="text-align: end;">{{ totalLinea(linea) }}</td>
                <td style="text-align: end;">
                <b-button size="sm" variant="danger" @click="quitarLinea(i)" title="Quitar">
                    <i class="fa fa-trash" aria-hidden="true"></i>
                </b-button>
                </td>
               </tr>
            </tbody>
        </table>
      </b-col>
    </b-row>

    <b-row>
      <b-col cols="2" offset="6">
        <b>Sub Total</b>
        <input type="text" class="form-control" :value="totalesEditar.subtotal.toFixed(2)" readonly>
      </b-col>
      <b-col cols="2">
        <b>IGV</b>
        <input type="text" class="form-control" :value="totalesEditar.igv.toFixed(2)" readonly>
      </b-col>
      <b-col cols="2">
        <b>Total</b>
        <input type="text" class="form-control" :value="totalesEditar.total.toFixed(2)" readonly>
      </b-col>
    </b-row>

    <b-row class="mt-3">
      <b-col cols="12" style="text-align: end;">
        <b-button variant="success" :disabled="guardando" @click="guardarEdicion()">
          <i class="fa fa-save" aria-hidden="true"></i> {{ guardando ? "Guardando..." : "Guardar cambios" }}
        </b-button>
        <b-button variant="danger" :disabled="guardando" @click="cancelar()">Cancelar</b-button>
      </b-col>
    </b-row>
  </b-modal>
</template>

<script>
import config from "@/store/config.js";
export default {
  name: "MovimientoEditForm",
  data() {
    return {
      API: config.API,
      visible: false,
      guardando: false,
      tituloMov: "",
      editar: {
        IdMovimiento: "",
        IdProveedor: null,
        Serie: "",
        Numero: "",
        Fecha: "",
        EsCredito: false,
        FechaVenCredito: ""
      },
      carrito: [],
      // proveedor
      lstProveedores: [],
      textoProveedor: "",
      mostrarListaProv: false,
      // productos
      idAlmacen: 0,
      busquedaProducto: "",
      resultadosProductos: [],
      buscando: false,
      busquedaRealizada: false,
      busquedaSeq: 0,
      timerBusqueda: null,
      mostrarListaProd: false,
      prodElegido: null,
      indiceActivo: 0
    };
  },
  watch: {
        busquedaProducto() {
            this.buscarProductosDebounce();
        },
        "editar.EsCredito"(val) {
            if (val && !this.editar.FechaVenCredito) {
            this.editar.FechaVenCredito = this.editar.Fecha;
            }
        }
    },
  beforeDestroy() {
    clearTimeout(this.timerBusqueda);
  },
  methods: {
    // Se llama desde el padre: this.$refs.movimientoEdit.abrir(IdMovimiento)
    async abrir(idMovimiento) {
      try {
        await this.cargarProveedores();
        const res = await this.axios(`${this.API}movimiento/obtener`, {
          params: { IdMovimiento: idMovimiento }
        });
        if (!res.data || !res.data.cabecera) {
          this.$toasted.error("No se pudo cargar el movimiento.");
          return;
        }
        const cab = res.data.cabecera;
        const det = res.data.detalle || [];
        this.editar = {
        IdMovimiento: idMovimiento,
        IdProveedor: cab.IdProveedor || null,
        Serie: String(cab.Serie || ""),
        Numero: String(cab.Numero || ""),
        Fecha: (cab.MovimientoFecha || "").substring(0, 10),
        EsCredito: Number(cab.EsCredito) == 1,
        FechaVenCredito: (cab.FechaVenCredito || "").substring(0, 10)
        };
        this.tituloMov = `${cab.Serie || ""}-${cab.Numero || ""}`;
        this.carrito = det.map(d => this.normalizarLinea(d));

        const prov = this.lstProveedores.find(p => p.IdProveedor == this.editar.IdProveedor);
        this.textoProveedor = prov ? prov.nombre : "";
        this.mostrarListaProv = false;
        this.idAlmacen = cab.IdAlmacenDestino || 0;

        this.resetBuscador();
        this.visible = true;
      } catch (err) {
        console.error(err);
        this.$toasted.error("Error al cargar el movimiento para editar.");
      }
    },
    cancelar() {
      this.resetBuscador();
      this.visible = false;
    },
    resetBuscador() {
      clearTimeout(this.timerBusqueda);
      this.busquedaSeq++; // descarta peticiones en curso
      this.buscando = false;
      this.busquedaProducto = "";
      this.resultadosProductos = [];
      this.busquedaRealizada = false;
      this.mostrarListaProd = false;
      this.prodElegido = null;
      this.indiceActivo = 0;
    },

    // ----- Proveedores -----
    async cargarProveedores() {
      if (this.lstProveedores.length) return;
      const res = await this.axios(`${this.API}proveedores`);
      if (res.data && Array.isArray(res.data)) {
        this.lstProveedores = res.data.map(p => ({
          IdProveedor: p.IdProveedor,
          nombre:
            (p.Ruc ? p.Ruc + " - " : "") +
            (p.Proveedor || p.RazonSocial || p.Nombre || "")
        }));
      }
    },
    onInputProveedor() {
      this.editar.IdProveedor = null; // hasta que elija uno de la lista
      this.mostrarListaProv = true;
    },
    seleccionarProveedor(p) {
      this.editar.IdProveedor = p.IdProveedor;
      this.textoProveedor = p.nombre;
      this.mostrarListaProv = false;
    },
    cerrarListaProv() {
      this.mostrarListaProv = false;
      const sel = this.lstProveedores.find(p => p.IdProveedor == this.editar.IdProveedor);
      if (sel) this.textoProveedor = sel.nombre;
    },

      normalizarLinea(d) {
          const presentaciones = this.parsearPresentaciones(d.Presentaciones);
          const actual = d.Presentacion && String(d.Presentacion).toUpperCase() !== "UNIDAD" ? d.Presentacion : null;
          if (actual && !presentaciones.some(p => p.IdPresentacion == actual)) {
              presentaciones.push({
              IdPresentacion: actual,
              Presentacion: actual,
              CantidadPresentacion: parseFloat(d.CantidadPresentacion) || 1,
              PrecioCosto: null
              });
          }
            return {
                IdProducto: d.IdProducto,
                CodigoBarra: d.CodigoBarra || "",
                Producto: d.Producto || "",
                ProductoMarca: d.ProductoMarca || "",
                ProductoMedicion: d.ProductoMedicion || "",
                IdPresentacion: d.IdPresentacion ? d.IdPresentacion : null,
                Presentacion: d.Presentacion && String(d.Presentacion).toUpperCase() !== "UNIDAD" ? d.Presentacion : "",
                CantidadPresentacion: parseFloat(d.CantidadPresentacion) || 1,
                Cantidad: parseFloat(d.Cantidad) || 0,
                Precio: parseFloat(d.Precio) || 0,
                TieneIgv: Number(d.TieneIgv) == 1 ? 1 : 0,
                // lote
                Lote: d.Lote || "",
                FechaVen: (d.FechaVen || "").substring(0, 10),
                Registro: d.Registro || "",
                // control de edición
                esExistente: true,
                PresentacionOrig: d.Presentacion || "UNIDAD",
                presentaciones
            };
    },

    // ----- Buscador de productos -----
    itemsLista() {
      return this.prodElegido
        ? this.opcionesPresentacion(this.prodElegido)
        : this.resultadosProductos;
    },
    moverSeleccion(d) {
      const n = this.itemsLista().length;
      if (!n) return;
      this.mostrarListaProd = true;
      this.indiceActivo = (this.indiceActivo + d + n) % n;
      this.$nextTick(() => {
        const el = this.$el.querySelector(".lista-prod .activo");
        if (el) el.scrollIntoView({ block: "nearest" });
      });
    },
    enterBusqueda() {
      const items = this.itemsLista();
      if (this.mostrarListaProd && items.length && !this.buscando) {
        const it = items[this.indiceActivo];
        if (this.prodElegido) this.elegirPresentacion(it);
        else this.elegirProducto(it);
      } else {
        this.buscarProductos();
      }
    },
    elegirProducto(prod) {
      if ((prod.presentaciones || []).length) {
        this.prodElegido = prod; // tiene presentaciones: pedir cuál
        this.indiceActivo = 0;
        return;
      }
      prod._pres = null;
      this.agregarProducto(prod);
      this.limpiarBusqueda();
    },
    elegirPresentacion(opt) {
      const prod = this.prodElegido;
      prod._pres = opt.value;
      this.agregarProducto(prod);
      this.limpiarBusqueda();
    },
    limpiarBusqueda() {
      clearTimeout(this.timerBusqueda);
      this.busquedaSeq++;
      this.busquedaProducto = "";
      this.resultadosProductos = [];
      this.busquedaRealizada = false;
      this.prodElegido = null;
      this.indiceActivo = 0;
      this.$nextTick(() => this.$refs.inputBusqueda && this.$refs.inputBusqueda.focus());
    },
    cerrarListaProd() {
      this.mostrarListaProd = false;
      this.prodElegido = null;
    },
    buscarProductosDebounce() {
      clearTimeout(this.timerBusqueda);
      const texto = (this.busquedaProducto || "").trim();
      if (texto.length < 2) {
        this.busquedaSeq++; // invalida cualquier petición en curso
        this.buscando = false;
        this.resultadosProductos = [];
        this.busquedaRealizada = false;
        this.prodElegido = null;
        return;
      }
      this.timerBusqueda = setTimeout(() => this.buscarProductos(), 300);
    },
    async buscarProductos() {
      clearTimeout(this.timerBusqueda); // evita doble búsqueda si presionan Enter
      const texto = (this.busquedaProducto || "").trim();
      if (texto.length < 1) return;

      const seq = ++this.busquedaSeq;
      this.buscando = true;
      try {
        const res = await this.axios(`${this.API}productos/puntoventa/stock`, {
          params: { q: texto, idAlmacen: this.idAlmacen }
        });
        if (seq !== this.busquedaSeq) return; // respuesta vieja: se ignora
        const data = Array.isArray(res.data) ? res.data : [];
        this.resultadosProductos = data.map(p => ({
          ...p,
          presentaciones: this.parsearPresentaciones(p.Presentaciones),
          _pres: null
        }));
        this.indiceActivo = 0;
        this.prodElegido = null;
        this.mostrarListaProd = true;
        this.busquedaRealizada = true;
      } catch (err) {
        if (seq !== this.busquedaSeq) return;
        this.resultadosProductos = [];
        this.busquedaRealizada = true;
        this.indiceActivo = 0;
        this.prodElegido = null;
        this.mostrarListaProd = true;
        this.$toasted.error("No se pudo buscar productos.");
      } finally {
        if (seq === this.busquedaSeq) this.buscando = false;
      }
    },
    parsearPresentaciones(raw) {
      let lista = [];
      try {
        lista = typeof raw === "string" ? JSON.parse(raw || "[]") : raw || [];
      } catch (e) {
        lista = [];
      }
      if (!Array.isArray(lista)) return [];
      const pick = (o, keys) => {
        for (let i = 0; i < keys.length; i++) {
          if (o[keys[i]] !== undefined && o[keys[i]] !== null && o[keys[i]] !== "") return o[keys[i]];
        }
        return null;
      };
      return lista.map(pr => ({
        IdPresentacion: pr.nombre,
        Presentacion: pr.nombre,
        // AJUSTA las claves si tu JSON usa otro nombre para la cantidad
        CantidadPresentacion:
          parseFloat(pick(pr, ["cantidad", "cantidadPress", "CantidadPresentacion", "unidades"])) || 1,
        PrecioCosto: pick(pr, ["preciocosto", "PrecioCosto"])
      }));
    },
    opcionesPresentacion(prod) {
      const opts = [{ value: null, text: "Sin presentación (unidad)" }];
      (prod.presentaciones || []).forEach(p => {
        opts.push({
          value: p.IdPresentacion,
          text: `${p.Presentacion} (x${p.CantidadPresentacion})`
        });
      });
      return opts;
    },

    // ----- Carrito -----
    agregarProducto(prod) {
      const pres = prod._pres
        ? (prod.presentaciones || []).find(p => p.IdPresentacion == prod._pres)
        : null;
      const idPres = pres ? pres.IdPresentacion : null;

      // Mismo producto + misma presentación: suma 1 a la cantidad
      const existente = this.carrito.find(
        l => l.IdProducto == prod.IdProducto && (l.IdPresentacion || null) == idPres
      );
      if (existente) {
        existente.Cantidad = (parseFloat(existente.Cantidad) || 0) + 1;
        return;
      }

      const factor = pres ? parseFloat(pres.CantidadPresentacion) || 1 : 1;
      // Precio de la línea = precio por presentación (costo unitario x factor)
      // si la presentación no trae su propio PrecioCosto.
      let precio = parseFloat(prod.PrecioCosto) || 0;
      if (pres) {
        precio =
          pres.PrecioCosto !== undefined && pres.PrecioCosto !== null
            ? parseFloat(pres.PrecioCosto) || 0
            : precio * factor;
      }

        this.carrito.push({
            IdProducto: prod.IdProducto,
            CodigoBarra: prod.CodigoBarra || "",
            Producto: prod.Producto || "",
            ProductoMarca: prod.ProductoMarca || "",
            ProductoMedicion: prod.ProductoMedicion || "",
            IdPresentacion: idPres,
            Presentacion: pres ? pres.Presentacion : "",
            CantidadPresentacion: factor,
            Cantidad: 1,
            Precio: parseFloat(precio.toFixed(4)),
            TieneIgv: Number(prod.TieneIgv) == 1 ? 1 : 0,
            Lote: "",
            FechaVen: "",
            Registro: "",
            esExistente: false,
            PresentacionOrig: null,
            presentaciones: prod.presentaciones || []
        });
    },
    cambiarPresentacion(linea, valor) {
        const pres = valor ? linea.presentaciones.find(p => p.IdPresentacion == valor) : null;
        const idPres = pres ? pres.IdPresentacion : null;

        const repetida = this.carrito.some(
            l => l !== linea && l.IdProducto == linea.IdProducto && (l.IdPresentacion || null) == idPres
        );
        if (repetida) {
            this.$toasted.error("Ese producto ya está en el carrito con esa presentación.");
            this.$forceUpdate(); // devuelve el select al valor anterior
            return;
        }

        // Se mantiene el costo por unidad y se recalcula el precio de la presentación
        const factorViejo = parseFloat(linea.CantidadPresentacion) || 1;
        const factorNuevo = pres ? parseFloat(pres.CantidadPresentacion) || 1 : 1;
        const costoUnidad = (parseFloat(linea.Precio) || 0) / factorViejo;

        linea.IdPresentacion = idPres;
        linea.Presentacion = pres ? pres.Presentacion : "";
        linea.CantidadPresentacion = factorNuevo;
        linea.Precio = parseFloat((costoUnidad * factorNuevo).toFixed(4));
    },
        
    quitarLinea(index) {
        const l = this.carrito[index];
        if (l.esExistente && l.Lote) {
            const ok = window.confirm(
            `"${l.Producto}" tiene el lote ${l.Lote}. Si lo quita y lo vuelve a agregar, el lote se pierde.\n\n` +
            `Para cambiar solo la presentación use el selector de la fila.\n\n¿Quitar de todas formas?`
            );
            if (!ok) return;
        }
        this.carrito.splice(index, 1);
    },
    unidadesStock(linea) {
      const u =
        (parseFloat(linea.Cantidad) || 0) *
        (parseFloat(linea.CantidadPresentacion) || 1);
      return parseFloat(u.toFixed(4));
    },
    totalLinea(linea) {
      const base = (parseFloat(linea.Cantidad) || 0) * (parseFloat(linea.Precio) || 0);
      const igv = linea.TieneIgv == 1 ? base * 0.18 : 0;
      return (base + igv).toFixed(2);
    },

    // ----- Guardado -----
    validarEdicion() {
      const e = this.editar;
      if (!e.IdProveedor) return "Seleccione un proveedor de la lista.";
      if (!String(e.Serie || "").trim()) return "Ingrese la serie.";
      if (!String(e.Numero || "").trim()) return "Ingrese el número.";
        if (!e.Fecha) return "Ingrese la fecha.";
      if (e.EsCredito) {
    if (!e.FechaVenCredito) return "Ingrese la fecha de vencimiento del crédito.";
    if (e.FechaVenCredito < e.Fecha) return "El vencimiento no puede ser anterior a la fecha del movimiento.";
    }
      if (this.carrito.length == 0) return "El carrito no puede quedar vacío.";
      for (let i = 0; i < this.carrito.length; i++) {
        const l = this.carrito[i];
        if (!(parseFloat(l.Cantidad) > 0)) {
          return `La cantidad de "${l.Producto}" debe ser mayor a 0.`;
        }
        if (!(parseFloat(l.Precio) >= 0) || l.Precio === "") {
          return `El precio de "${l.Producto}" no es válido.`;
          }
        if (!l.esExistente) {
        if (!String(l.Lote || "").trim()) return `Ingrese el lote de "${l.Producto}".`;
        if (!l.FechaVen) return `Ingrese la fecha de vencimiento de "${l.Producto}".`;
        }
      }
      return null;
    },
    async guardarEdicion() {
    if (this.guardando) return;

    const error = this.validarEdicion();
    if (error) {
        this.$toasted.error(error);
        return;
    }

    const confirmado = window.confirm(
        "Esto recalcula stock, kardex y costos del movimiento. Si el periodo ya fue declarado, revise que el cambio sea correcto.\n\n¿Desea guardar los cambios?"
    );
    if (!confirmado) return;

    const payload = {
        IdMovimiento: this.editar.IdMovimiento,
        Usuario: sessionStorage.getItem("User"),
        IdProveedor: this.editar.IdProveedor,
        Serie: String(this.editar.Serie || "").trim(),
        Numero: String(this.editar.Numero || "").trim(),
        MovimientoFecha: this.editar.Fecha,
        EsCredito: this.editar.EsCredito ? 1 : 0,
        FechaVenCredito: this.editar.EsCredito ? this.editar.FechaVenCredito : "",
        detalle: this.carrito.map(l => ({
            IdProducto: l.IdProducto,
            IdPresentacion: l.IdPresentacion,
            PresentacionOrig: l.PresentacionOrig,
            CantidadPresentacion: parseFloat(l.CantidadPresentacion) || 1,
            Cantidad: parseFloat(l.Cantidad),
            Precio: parseFloat(l.Precio),
            TieneIgv: l.TieneIgv,
            Lote: (l.Lote || "").trim(),
            FechaVen: l.FechaVen || "",
            Registro: (l.Registro || "").trim()
        }))
    };

    this.guardando = true;
    try {
        const res = await this.axios.post(`${this.API}movimiento/editar`, payload);
        if (res.data && res.data.updated) {
        this.resetBuscador();
        this.visible = false;
        this.$emit("guardado"); // el padre recarga el reporte
        this.$toasted.success("Movimiento actualizado");
        } else {
        this.$toasted.error(
            (res.data && res.data.message) || "No se pudo actualizar el movimiento."
        );
        }
    } catch (err) {
        console.error(err);
        const msg = err.response && err.response.data && err.response.data.message;
        this.$toasted.error(msg || "Error al guardar el movimiento.");
    } finally {
        this.guardando = false;
    }
    }
  },
  computed: {
    proveedoresFiltrados() {
      // si ya hay uno elegido, muestra todos; si no, filtra por lo escrito
      const f = this.editar.IdProveedor ? "" : (this.textoProveedor || "").trim().toLowerCase();
      return this.lstProveedores
        .filter(p => !f || p.nombre.toLowerCase().includes(f))
        .slice(0, 50);
    },
    totalesEditar() {
      let subtotal = 0;
      let igv = 0;
      this.carrito.forEach(l => {
        const base = (parseFloat(l.Cantidad) || 0) * (parseFloat(l.Precio) || 0);
        subtotal += base;
        if (l.TieneIgv == 1) igv += base * 0.18;
      });
      return { subtotal, igv, total: subtotal + igv };
    }
  }
};
</script>

<style>
/* Más especificidad que la regla global .modal-dialog { width: 60% !important } de la vista */
.modal-dialog.modal-editar-mov {
  max-width: 95% !important;
  width: 95% !important;
}

.lista-prov {
  position: absolute;
  z-index: 2000;
  left: 15px;
  right: 15px;
  max-height: 220px;
  overflow-y: auto;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

.lista-prod {
  position: absolute;
  z-index: 2000;
  left: 15px;
  right: 15px;
  max-height: 320px;
  overflow-y: auto;
  background: #fff;
  border: 1px solid #ced4da;
  border-radius: 4px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}
.lista-prod .item-prod {
  padding: 6px 10px;
  cursor: pointer;
  font-size: 0.9rem;
}
.lista-prod .item-prod.activo {
  background: #8c8cc4;
  color: #fff;
}
.lista-prod .item-prod-titulo {
  padding: 6px 10px;
  background: #f1f1f1;
  border-bottom: 1px solid #ddd;
}
.lista-prod .item-prod-vacio {
  padding: 6px 10px;
  color: #6c757d;
}
</style>