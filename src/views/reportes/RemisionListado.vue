<template>
  <b-container fluid class="bv-example-row">
    <b-row>
        <b-col cols="12">
            <h2>Listado De Guía de Remisión</h2>
        </b-col>
        <b-col cols="4">
            <b-form-group
                label="Doc. Referencia"
                label-for="input-1"
                valid-feedback="Thank you!"
            
            
            >
            <b-form-input id="input-1" v-model="search"   style="text-transform: uppercase;" trim></b-form-input>
        </b-form-group>
        </b-col>
        <b-col cols="12">
            <b-table
            id="my-table"
            :items="datos()"
            :fields="headerTable"
            :per-page="perPage"
            :current-page="currentPage"
            small
            >
            <template slot="url_archivo" slot-scope="data">
                <b-button variant="danger" @click="abrirPF(data.item)">
                    <i class="fa fa-file-pdf" aria-hidden="true"></i>
                </b-button>
                
            </template>
            </b-table>

             <b-pagination
            v-model="currentPage"
            :total-rows="datos().length"
            :per-page="perPage"
            aria-controls="my-table"
            ></b-pagination>

        </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      search: "",
      perPage: 10,
      API: config.API,
      currentPage: 1,
      lstRemnision: [],
      headerTable: [
        { key: "DocumentoReferencia", text: "DocumentoReferencia" },
        { key: "Serie", text: "Serie" },
        { key: "Numero", text: "Numero" },
        { key: "NombreArchivo", text: "NombreArchivo" },
        { key: "url_archivo", text: "PDF" }
      ]
    };
  },
  mounted() {
    this.obtenerListado();
  },
  methods: {
    async obtenerListado() {
      await this.axios
        .get(this.API + "ventas/remision/listado")
        .then(response => {
          if (response.data) {
            this.lstRemnision = response.data;
          }
        });
    },
    datos() {
      if (!!this.search) {
        return this.lstRemnision.filter(v =>
          v.DocumentoReferencia.includes(this.search.toUpperCase())
        );
      }
      return this.lstRemnision;
    },
    abrirPF(item) {
      window.open(item.URL, "_blank");
    }
  }
};
</script>

<style>
</style>
