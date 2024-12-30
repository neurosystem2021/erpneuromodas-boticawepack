<template>
  <b-container>
    <b-row>
        <b-col cols="12" class="right">
            <b-button class="my-3" variant="success" @click="abrirNuevo()"> Nueva</b-button>
        </b-col>
        <b-col cols="12"> 
            <b-table :items="lstImpresoras" :fields="fields">
                <template slot="action" slot-scope="data">
                    <b-button  variant="primary" @click="abrirEditar(data.item)">
                        <i class="fas fa-pencil-alt fa-sm"></i>
                    </b-button>
                    <!-- <b-button  variant="danger" @click="eliminar(data.item)">
                        <i class="fa fa-trash" aria-hidden="true"></i>
                    </b-button> -->
                </template>
            </b-table>
        </b-col>
    </b-row>
    <b-modal v-model="modalNuevaImpresora" hide-footer title="Nueva Impresora">
        Nombre 
        <input type="text" class="form-control my-2" v-model="impresora.NombreImpresora">
        Ip
        <input type="text" class="form-control my-2" v-model="impresora.Ip">
        Puerto
        <input type="text" class="form-control my-2" v-model="impresora.Puerto">
        LongitudLinea
        <input type="text" class="form-control my-2" v-model="impresora.LongitudLinea">
        <div class="right">
            <b-button variant="success" @click="registrar()"> Guardar</b-button>
            <b-button variant="danger" @click="modalNuevaImpresora=!modalNuevaImpresora"> Cancelar</b-button>
        </div>
        </b-modal>
    <b-modal v-model="modalEditarImpresora" hide-footer title="Editar Impresora">
        Nombre 
        <input type="text" class="form-control my-2" v-model="impresora.NombreImpresora">
        Ip
        <input type="text" class="form-control my-2" v-model="impresora.Ip">
        Puerto
        <input type="text" class="form-control my-2" v-model="impresora.Puerto">
        LongitudLinea
        <input type="text" class="form-control my-2" v-model="impresora.LongitudLinea">
        <div class="right">
            <b-button variant="success" @click="editar()"> Guardar</b-button>
            <b-button variant="danger" @click="modalEditarImpresora=!modalEditarImpresora"> Cancelar</b-button>
        </div>
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
import Swal from "sweetalert2";
export default {
  data() {
    return {
      modalNuevaImpresora: false,
      modalEditarImpresora: false,
      lstImpresoras: [],
      fields: [
        // { key: "IdImpresora" },
        { key: "NombreImpresora" },
        { key: "Ip" },
        { key: "Puerto" },
        // { key: "EsCocina" },
        { key: "LongitudLinea" },
        { key: "action", label: "" }
      ],
      API: config.API,
      impresora: {
        IdImpresora: "",
        NombreImpresora: "",
        Ip: "",
        Puerto: "",
        LongitudLinea: ""
      }
    };
  },
  mounted() {
    this.obtenerListado();
  },
  methods: {
    async obtenerListado() {
      await this.axios(`${this.API}impresora`).then(res => {
        this.lstImpresoras = res.data;
      });
    },
    abrirNuevo() {
      this.impresora = {
        IdImpresora: "",
        NombreImpresora: "",
        Ip: "",
        Puerto: "",
        LongitudLinea: ""
      };
      this.modalNuevaImpresora = true;
    },
    abrirEditar(impresora) {
      this.impresora = impresora;
      this.modalEditarImpresora = true;
    },
    async registrar() {
      await this.axios
        .post(`${this.API}impresora/nuevo`, this.impresora)
        .then(res => {});
      this.obtenerListado();
      this.modalNuevaImpresora = false;
    },
    async editar() {
      await this.axios
        .put(`${this.API}impresora/editar`, this.impresora)
        .then(res => {});
      this.obtenerListado();
      this.modalEditarImpresora = false;
    },
    eliminar(item) {
      Swal({
        icon: "question"
      });
    }
  }
};
</script>

<style>
.right {
  text-align: right !important;
  /* align-items: right; */
  /* align-content: right; */
}
</style>
