<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="6" class="my-1">
            <b-form-group label="Buscar" class="mb-0">
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" />
                <b-input-group-append>
                  <b-btn :disabled="!filter" @click="limpiarBuscador">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
          </b-col>

          <b-col md="4" class="my-1">
            <b-form-group label="Mostrar" class="mb-0">
              <b-form-select :options="pageOptions" v-model="perPage" @input="generarReporte"/>
            </b-form-group>
          </b-col>

          <b-col md="2" class="my-1">
            <p style="margin-top: 32px;">de <b class="text-success">{{ this.totalRows }}</b> Clientes</p>
          </b-col>

          <b-col md="3" class="my-1">
            <b-form-group label="Departamento" class="mb-0">
              <b-form-select 
                v-model="ubigeoSeleccionado.departamento" 
                @change="cambioDepartamento">
                <option :value="null">-- Todos --</option>
                <option 
                  v-for="dep in $store.state.listUbigeo" 
                  :key="dep.id" 
                  :value="dep">
                  {{ dep.name }}
                </option>
              </b-form-select>
            </b-form-group>
          </b-col>

          <b-col md="3" class="my-1">
            <b-form-group label="Provincia" class="mb-0">
              <b-form-select 
                v-model="ubigeoSeleccionado.provincia" 
                :disabled="!ubigeoSeleccionado.departamento"
                @change="cambioProvincia">
                <option :value="null">-- Todas --</option>
                <option 
                  v-for="prov in provinciasDisponibles" 
                  :key="prov.id" 
                  :value="prov">
                  {{ prov.name }}
                </option>
              </b-form-select>
            </b-form-group>
          </b-col>

          <b-col md="3" class="my-1">
            <b-form-group label="Distrito" class="mb-0">
              <b-form-select 
                v-model="ubigeoSeleccionado.distrito" 
                :disabled="!ubigeoSeleccionado.provincia">
                <option :value="null">-- Todos --</option>
                <option 
                  v-for="dist in distritosDisponibles" 
                  :key="dist.id" 
                  :value="dist">
                  {{ dist.name }}
                </option>
              </b-form-select>
            </b-form-group>
          </b-col>

          <b-col md="3" class="my-1">
            <b-form-group label="&nbsp;" class="mb-0">
              <b-button variant="primary" block @click="generarReporte()">Generar <i class="fa fa-search"></i></b-button>
            </b-form-group>
          </b-col>

          <b-table small
            ref="tablaClientes"
            :filter="filterQuery"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            @row-clicked="rowClick"
            id="my-table" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage"></b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <cliente-form ref="clienteForm" :clienteSelect="clienteSelect" @onAddCliente="actualizarTabla"></cliente-form>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" @click="nuevoCliente">Nuevo</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
  </div>
</template>

<script>
import config from "@/store/config.js";
import ClienteForm from "@/components/ClientesFormEdit";

export default {
  mounted() {
    if (this.$store.state.listUbigeo.length === 0) {
      this.$store.dispatch("GetUbigeo");
    }

    this.axios.get(this.API + "clientes/count").then(response => {
      this.totalRows = response.data.total;
    });
  },
  data() {
    return {
      API: config.API,
      pageOptions: [5, 10, 15, 50, 100],
      filter: null,
      filterQuery: "", // El estado controlado que el Provider escuchará
      sortBy: "IdCliente",
      sortDesc: false,
      clienteSelect: {},
      items: [],
      isBusy: false,
      fields: [
        { key: "Cliente", sortable: true },
        { key: "DniRuc", sortable: true },
        { key: "Direccion", sortable: true },
        { key: "Telefono", sortable: false },
        { key: "Email", sortable: false },
        { key: "Puntos", sortable: true },
        { key: "EsVIP", sortable: true },
        // { key: "Departamento", sortable: true },
        { key: "Provincia", sortable: true },
        { key: "Distrito", sortable: true }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,

      ubigeoSeleccionado: {
        departamento: null,
        provincia: null,
        distrito: null
      },
      provinciasDisponibles: [],
      distritosDisponibles: []
    };
  },
  methods: {
    rowClick(item, index) {
      this.clienteSelect = item;
      this.$refs.clienteForm.show = true;
      //this.$refs.productoForm.producto.IdProducto = 'a'
      //console.log(this.$refs.productoForm.$refs.productoForm)
    },
    actualizarTabla() {
      this.$refs.tablaClientes.refresh();
    },
    nuevoCliente() {
      this.clienteSelect = {};
      this.$refs.clienteForm.show = true;
    },
    cambioDepartamento(dep) {
      this.ubigeoSeleccionado.provincia = null;
      this.ubigeoSeleccionado.distrito = null;
      this.distritosDisponibles = [];
      this.provinciasDisponibles = dep ? dep.children : [];
    },
    cambioProvincia(prov) {
      this.ubigeoSeleccionado.distrito = null;
      this.distritosDisponibles = prov ? prov.children : [];
    },
    
    // Al presionar generar, traspasamos el 'filter' a 'filterQuery' y refrescamos la tabla
    generarReporte() {
      this.currentPage = 1;
      this.filterQuery = this.filter || "";
      this.$nextTick(() => {
        if (this.$refs.tablaClientes) {
          this.$refs.tablaClientes.refresh();
        }
      });
    },
    limpiarBuscador() {
      this.filter = '';
      this.generarReporte();
    },

    myProvider(ctx) {
      let codUbigeo = "";
      if (this.ubigeoSeleccionado.distrito) codUbigeo = this.ubigeoSeleccionado.distrito.id;
      else if (this.ubigeoSeleccionado.provincia) codUbigeo = this.ubigeoSeleccionado.provincia.id;
      else if (this.ubigeoSeleccionado.departamento) codUbigeo = this.ubigeoSeleccionado.departamento.id;

      // Usamos ctx.filter (que equivale a filterQuery internamente)
      let url =
        this.API +
        "clientes?limit=" +
        ctx.perPage +
        "&page=" +
        ctx.currentPage +
        "&q=" +
        (ctx.filter || "") +
        "&sortBy=" +
        ctx.sortBy +
        "&ubigeo=" + codUbigeo; 
        
      url += "&sortDesc=" + (ctx.sortDesc ? 1 : 0);

      return this.axios.get(url)
        .then(result => {
          this.items = result.data;
          console.log(result.data);
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
    }
  },
  components: {
    ClienteForm
  }
};
</script>

<style scoped>
/* Cuando la pantalla sea grande (computadoras), dale un ancho máximo más holgado */
@media (min-width: 1200px) {
  .container {
    max-width: 1450px !important; /* Ajusta este número (ej. 1350px o 1500px) a tu gusto */
  }
}

/* Opcional: Hace que los textos de la cabecera no se amontonen */
#my-table th {
  white-space: nowrap;
}
</style>
