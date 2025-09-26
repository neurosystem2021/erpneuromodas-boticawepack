<template>
  <b-container>
    <b-row>
      <b-col cols="12">
        <b-button variant="success" @click="abrirModalRegistro">Nueva Isla</b-button>
    </b-col>
    <b-col cols="12">
        <b-table :fields="fields" :items="lstIsla">
          <template slot="Estado" scope="data">
            {{ data.item.Estado == '1' ? 'Activo' : 'Inactivo' }}
          </template>
          <template slot="action" scope="data">
            <b-button variant="danger" v-if="data.item.Estado==1" @click="cambiarEstado(data.item)"><i class="fa fa-trash" aria-hidden="true"></i></b-button>
            <b-button variant="warning" v-else @click="cambiarEstado(data.item)"><i class="fa fa-power-off" aria-hidden="true"></i></b-button>
            
          </template>
        </b-table>
      </b-col>
    </b-row>
    <b-modal v-model="modal" title="Nueva Isla" hide-footer size="sm">
      <input v-model="Nombre" class="form-control">
      <b-button class="mt-2" variant="success" @click="guardarIsla">Guardar</b-button>
      <b-button class="mt-2" variant="danger" @click="modal=false">Cancelar</b-button>
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      Nombre: "",
      modal: false,
      lstIsla: [],
      fields: [
        { key: "Isla" },
        { key: "Estado" },
        { key: "action", label: "" }
      ],
      API: config.API
    };
  },
  mounted() {
    this.cargarIsla();
  },
  methods: {
    cargarIsla() {
      this.axios(`${this.API}/isla`).then(res => {
        if (res.data) {
          this.lstIsla = res.data;
        }
      });
    },
    cambiarEstado(item) {
      this.$swal({
        icon: "question",
        title: item.Estado == 1 ? "ANULAR" : "Activar",
        text: `Desea ${item.Estado == 1 ? "ANULAR" : "Activar"}: ${item.Isla}`,
        allowEscapeKey: false,
        allowEnterKey: false,
        allowOutsideClick: false,
        showCancelButton: true
      }).then(async res => {
        if (res.isConfirmed) {
          await this.axios.put(`${this.API}/isla/update`, item).then(res => {
            if (res.data) {
              this.cargarIsla();
            }
          });
        }
      });
    },
    abrirModalRegistro() {
      this.Nombre = "";
      this.modal = true;
    },
    async guardarIsla() {
      if (!this.Nombre) {
        return;
      }
      await this.axios
        .post(`${this.API}/isla/insertar`, { Isla: this.Nombre })
        .then(res => {
          this.cargarIsla();
          this.modal = false;
        });
    }
  }
};
</script>

<style>
</style>
