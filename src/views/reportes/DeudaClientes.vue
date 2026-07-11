<template>
  <div>
    <h2 align-h="center" class="text-center" >Reporte Deuda de Clientes</h2>
      <b-container fluid style="width: calc(100% - 50px)" class="bv-example-row">
        <b-row>
          <b-col md="4">
            <b-form-group>
                  <label>Buscar:</label>
              
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="onFilterInput" />
                <b-input-group-append>
                  <b-btn :disabled="!filter" @click="limpiarFiltro">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
            <br />
          </b-col>
          <b-col md="4">
            <b-form-group>
                  <label>Almacen:</label>
                  <autocomplete
                      required="required"
                      ref="inputAlmacen"
                      :url="API + 'almacenes'"
                      anchor="Almacen"
                      initValue=""
                      label="writer"
                      placeholder=""
                      :on-select="setAlmacen" 
                      :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-form-group>
          </b-col>
          <b-col md="4">
            <b-row>
            <b-col md="12">

              <b-form-group>
                  <label>Mostrar:</label>
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
            <b-col md="">
             </b-col>
            </b-row>
          </b-col>

          <b-col md="4">
            <b-form-group>
              <label>Departamento</label>
              <b-form-select v-model="ubigeoSeleccionado.departamento">
                <option :value="null">-- TODOS --</option>
                <option 
                  v-for="dep in $store.state.listUbigeo" 
                  :key="dep.id" 
                  :value="dep">
                  {{ dep.name }}
                </option>
              </b-form-select>
            </b-form-group>
          </b-col>

          <b-col md="4">
            <b-form-group>
              <label>Provincia</label>
              <b-form-select 
                v-model="ubigeoSeleccionado.provincia" 
                :disabled="!ubigeoSeleccionado.departamento">
                <option :value="null">-- TODOS --</option>
                <option 
                  v-for="prov in provinciasDisponibles" 
                  :key="prov.id" 
                  :value="prov">
                  {{ prov.name }}
                </option>
              </b-form-select>
            </b-form-group>
          </b-col>

          <b-col md="4">
            <b-form-group>
              <label>Distrito</label>
              <b-form-select 
                v-model="ubigeoSeleccionado.distrito" 
                :disabled="!ubigeoSeleccionado.provincia">
                <option :value="null">-- TODOS --</option>
                <option 
                  v-for="dist in distritosDisponibles" 
                  :key="dist.id" 
                  :value="dist">
                  {{ dist.name }}
                </option>
              </b-form-select>
            </b-form-group>
          </b-col>
          <b-col md="3">
            <b-form-group>
              <label>Fecha Emisión Inicio:</label>
              <b-form-input
                v-model="rango.fechaDesde"
                type="date"
                step="1"
                @input="generarReporte"
                placeholder="Fecha Inicio">
              </b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="3">
            <b-form-group>
              <label>Fecha Emisión Fin:</label>
              <b-form-input
                v-model="rango.fechaHasta"
                type="date"
                step="1"
                @input="generarReporte"
                placeholder="Fecha Inicio">
              </b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="1" class="d-flex align-items-center">
          <b-form-group class="mt-4">
            <b-form-checkbox
              v-model="soloCredito"
              switch
              @change="cambioSoloCredito">
              Es Crédito
            </b-form-checkbox>
          </b-form-group>
        </b-col>
        <b-col md="1" class="d-flex align-items-center">
          <b-form-group class="mt-4">
            <b-form-checkbox
              v-model="soloEfectivoParcial"
              switch
              @change="cambioSoloEfectivoParcial">
              Es Efectivo Parcial
            </b-form-checkbox>
          </b-form-group>
        </b-col>
          <b-col md="1">
            <b-form-group class="mt-4">
              <b-button variant="primary" block @click="generarReporte()">Generar <i class="fa fa-search"></i></b-button>
            </b-form-group>
          </b-col>
          <b-col md="3">
            <h4>
              <b-form-group class="mt-4">
              <span > Total: {{ calcularTotal() }} </span>
              <b-button variant="danger" @click="imprimir()" class="mx-5">Imprimir <i class="fa fa-file-pdf"></i> </b-button>
              </b-form-group>
            </h4>
          </b-col>
          <b-table small
            ref="tablaProductos"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            id="my-table" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage"
            @row-clicked="myRowClickHandler">
            <template slot="Saldo" slot-scope="data">
                {{ data.item.Saldo | redondear(2) }}
            </template>
            <template slot="TipoDeuda" slot-scope="data">
                <b-button
                  size="sm"
                  :variant="data.item.EfectivoParcial == 1 ? 'warning' : 'info'"
                  disabled
                  style="font-weight: bold;"
                  block>
                  {{ data.item.EfectivoParcial == 1 ? 'Efectivo Parcial' : 'Crédito' }}
                </b-button>
            </template>
          </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
          <b-button variant="success" size="lg" 
            :href="'/api/index.php/reporte/deudaclientes2?idalmacen=' + almacen.IdAlmacen+'&limit='+perPage+'&fechaDesde='+rango.fechaDesde+'&fechaHasta='+rango.fechaHasta + obtenerParamsUbigeo() + '&esCredito=' + (soloCredito ? 1 : 0) + '&esEfectivoParcial=' + (soloEfectivoParcial ? 1 : 0) + '&q=' + filter" 
            target="_blank">Exportar</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
      <b-modal id="modalDetalleDeuda" size="lg" modal-class="modal-xl" v-model="showLetras" ok-only ok-title="Cerrar" title="Detalle de Deuda">
           <detalle-deuda-cliente ref="detDeuCli"></detalle-deuda-cliente>
      </b-modal>
  </div>
</template>

<script>
import config from "@/store/config.js";
import DetalleDeudaCliente from "@/components/DetalleDeudaCliente.vue";
// import ProductoForm from '@/components/ProductoForm'

export default {
  mounted() {
      if (this.$store.state.listUbigeo.length === 0) {
        this.$store.dispatch("GetUbigeo");
      }
      this.actualizarTotal(); // esto ya cubre el conteo inicial con todos los filtros
  },
  data() {
    return {
      API: config.API,
      pageOptions: [50, 100, 150, 200, 300],
      filter: "",
      sortBy: "IdDocVenta",
      sortDesc: false,
      items: [],
      isBusy: false,
      soloCredito: false,
      soloEfectivoParcial: false,
      // temporizador interno para el debounce del buscador de texto
      _filterTimeout: null,
      fields: [
        { key: "DniRuc", label: "DNI/RUC", sortable: true },
        { key: "Cliente", label: "Cliente", sortable: true },
        { key: "Correlativo", label: "Serie", sortable: true },
        { key: "Total", label: "Total", sortable: true },
        { key: "Aplicado", label: "Aplicado", sortable: true },
        { key: "NotaCreApli", label: "Apli. Nota Cred.", sortable: true },
        { key: "Saldo", label: "Saldo", sortable: true },
        { key: "FechaDoc", label: "Emision", sortable: true },
        { key: "FechaCredito", label: "Vencimiento", sortable: true },
        { key: "Letras", label: "Letras", sortable: true },
        { key: "Provincia", label: "Provincia", sortable: true },
        { key: "Distrito", label: "Distrito", sortable: true },
        { key: "TipoDeuda", label: "Tipo", sortable: false }
      ],
      totalRows: 50,
      perPage: 50,
      currentPage: 1,
      almacen: {
        IdAlmacen: 1
      },
      rango: {
        fechaDesde: this.$moment().subtract(3, 'months').format("YYYY-MM-DD"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      },
      showLetras: false,
      urlImg: "/resources/images/logo.png",
      
      // RESTRUTURACIÓN DE UBIGEO DATA
      ubigeoSeleccionado: {
        departamento: null,
        provincia: null,
        distrito: null
      },
      provinciasDisponibles: [],
      distritosDisponibles: []
    };
  },
  watch: {
    // Usamos watch en vez de @change para evitar la condición de carrera
    // entre el listener del v-model y un handler manual sobre el mismo evento
    // "change": con watch, Vue garantiza que el callback corre DESPUÉS de que
    // el dato ya fue actualizado, así el request nunca sale con el valor viejo.
    'ubigeoSeleccionado.departamento'(dep) {
      this.ubigeoSeleccionado.provincia = null;
      this.ubigeoSeleccionado.distrito = null;
      this.distritosDisponibles = [];
      this.provinciasDisponibles = dep ? dep.children : [];
      this.generarReporte();
    },
    'ubigeoSeleccionado.provincia'(prov) {
      this.ubigeoSeleccionado.distrito = null;
      this.distritosDisponibles = prov ? prov.children : [];
      this.generarReporte();
    },
    'ubigeoSeleccionado.distrito'() {
      this.generarReporte();
    }
  },
methods: {
    // Devuelve el querystring del nivel de ubigeo más específico seleccionado.
    // Se manda solo uno (el más profundo), porque el backend ya filtra en cascada:
    // si seleccionas Distrito, eso ya implica su Provincia y Departamento.
    obtenerParamsUbigeo() {
      if (this.ubigeoSeleccionado.distrito) {
        return "&idDistrito=" + this.ubigeoSeleccionado.distrito.id;
      }
      if (this.ubigeoSeleccionado.provincia) {
        return "&idProvincia=" + this.ubigeoSeleccionado.provincia.id;
      }
      if (this.ubigeoSeleccionado.departamento) {
        return "&idDepartamento=" + this.ubigeoSeleccionado.departamento.id;
      }
      return "";
    },

    // recalcula el total de filas respetando TODOS los filtros activos
    actualizarTotal() {
      this.axios
        .get(
          this.API + "clientes/deuda/count?idalmacen=" + this.almacen.IdAlmacen +
          "&q=" + this.filter +
          "&fechaDesde=" + this.rango.fechaDesde +
          "&fechaHasta=" + this.rango.fechaHasta +
          this.obtenerParamsUbigeo() +
          "&esCredito=" + (this.soloCredito ? 1 : 0) +
          "&esEfectivoParcial=" + (this.soloEfectivoParcial ? 1 : 0)
        )
        .then(response => {
          this.totalRows = response.data.total;
        });
    },

    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick();
    },
    calcularTotal() {
      let sum = this.items.reduce((sum, item) => {
        return sum + parseFloat(item.Saldo);
      }, 0);
      return parseFloat(sum).toFixed(2);
    },
    async imprimir() {
      // http://localhost/api/index.php/cliente/imprimir/deudas_cliente?idalmacen=1&q=&limit=50&page=1&fechaDesde=2024-01-01&fechaHasta=2024-09-20
      let res = await this.axios(
        `${this.API}cliente/imprimir/deudas_cliente?idalmacen=${
          this.almacen.IdAlmacen
        }&limit=${this.perPage}&fechaDesde=${this.rango.fechaDesde}&fechaHasta=${
          this.rango.fechaHasta
        }&q=${this.filter}${this.obtenerParamsUbigeo()}&esCredito=${this.soloCredito ? 1 : 0}&esEfectivoParcial=${this.soloEfectivoParcial ? 1 : 0}`
      ).catch(e => console.log(e));
      if (res.data) {
        window.open(res.data.url, "_blank");
      }
    },
    setAlmacen(obj) {
      this.almacen = obj;
      this.generarReporte();
    },

    // Recarga la tabla Y el contador de total, siempre juntos.
    generarReporte() {
      this.currentPage = 1;
      this.actualizarTotal();
      if (this.$refs.tablaProductos) {
        this.$refs.tablaProductos.refresh();
      }
    },

    // Buscador de texto: debounce de 400ms para no disparar una petición por cada tecla
    onFilterInput() {
      if (this._filterTimeout) clearTimeout(this._filterTimeout);
      this._filterTimeout = setTimeout(() => {
        this.generarReporte();
      }, 400);
    },
    limpiarFiltro() {
      this.filter = "";
      this.generarReporte();
    },

    cambioSoloCredito(val) {
      if (val) this.soloEfectivoParcial = false;
      this.generarReporte();
    },
    cambioSoloEfectivoParcial(val) {
      if (val) this.soloCredito = false;
      this.generarReporte();
    },

    myProvider(ctx) {
      let promise = this.axios.get(
        this.API +
          "cliente/deudas?idalmacen=" +
          this.almacen.IdAlmacen +
          "&q=" +
          ctx.filter +
          "&limit=" +
          ctx.perPage +
          "&page=" +
          ctx.currentPage +
          "&fechaDesde=" +
          this.rango.fechaDesde +
          "&fechaHasta=" +
          this.rango.fechaHasta +
          this.obtenerParamsUbigeo() +
          "&sortBy=" + ctx.sortBy +
          "&sortDesc=" + (ctx.sortDesc ? 1 : 0) +
          "&esCredito=" + (this.soloCredito ? 1 : 0) +
          "&esEfectivoParcial=" + (this.soloEfectivoParcial ? 1 : 0)
      );

      return promise
        .then(response => {
          this.items = response.data;
          return this.items;
        })
        .catch(error => {
          return [];
        });
    },
    myRowClickHandler(record, index) {
      if (record.Letras != "Ninguna") {
        this.showLetras = true;
        this.$refs.detDeuCli.cargarDeudasCliente(
          record.IdDocVenta,
          record.Total,
          record.DniRuc,
          record.Cliente,
          record.Correlativo
        );
      }
    }
},
  components: {
    DetalleDeudaCliente
  }
};
</script>