<template>
  <b-container>
    <b-row>
        <b-col cols="12">
            <b-button class="my-2" variant="warning" @click="cerrarReg()">Cerrar Registro</b-button>
            <b-button class="my-2" variant="success" @click="abrirModal()">Nuevo Registro</b-button>
        </b-col>
        <b-col cols="12">
            <b-table 
              :fields="fields" 
              :items="lstHistorial"
              :per-page="perPage"
              :current-page="currentPage"  
            >
                <template slot="Turno" slot-scope="data">
                    {{ obtenerTurno(data.item) }}
                </template>

                <template slot="Estado" slot-scope="data">
                    {{ data.item.Estado == 1 ? 'Activo'  : 'Cerrado' }}
                </template>
            </b-table>

            <b-pagination
              v-model="currentPage"
              :total-rows="lstHistorial.length"
              :per-page="perPage"
              aria-controls="my-table"
            ></b-pagination>
        </b-col>
    </b-row>
    <b-modal v-model="modal" hide-footer title="Registro Galones Turno" size="lg">
        <b-container>
            <b-form @submit="guardar" ref="form">
            <b-row>
                <b-col cols="12">
                    <b-table :fields="fieldsForm" :items="lstFormHistorial">
                        <template slot="Galones" slot-scope="data">
                            <input type="number" v-model="data.item.Galones" class="form-control" step="0.0001" min="0">
                        </template>
                    </b-table>
                </b-col>
                <b-col>
                    <b-button variant="success" type="submit">Guardar</b-button>
                    <b-button variant="danger" @click="modal=!modal">Cancelar</b-button>
                </b-col>
            </b-row>
            </b-form>
        </b-container>
        <!-- {{ lstFormHistorial }} -->
          
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      perPage: 10,
      currentPage: 1,
      modal: false,
      API: config.API,
      lstProducto: [],
      IdTurno: null,
      turnos: [
        { value: null, text: "Seleccione un Turno" },
        { value: 1, text: "Mañana" },
        { value: 2, text: "Tarde" },
        { value: 3, text: "Noche" }
      ],
      caras: [{ value: 1, text: "Cara A" }, { value: 2, text: "Cara B" }],
      lstFormHistorial: [],
      lstHistorial: [],
      fields: [
        { key: "IdHistorico" },
        { key: "FechaReg" },
        // { key: "Turno", label: "Turno" },
        { key: "Estado" },
        { key: "Producto" },
        { key: "Cara" },
        { key: "Galones" },
        { key: "UsuarioReg" }
      ],
      fieldsForm: [
        // { key: "IdProducto" },
        { key: "Producto" },
        // { key: "Cara" },
        { key: "CaraDescripcion", label: "Cara" },
        { key: "Galones" }
      ]
    };
  },
  mounted() {
    this.obtenerListadoHistorial();
  },
  methods: {
    async obtenerListadoHistorial() {
      await this.axios(`${this.API}historialoferta`).then(res => {
        if (res.data) {
          this.lstHistorial = res.data;
        }
      });
    },
    async obtenerListadoProductos() {
      this.IdTurno = null;
      this.lstFormHistorial = [];
      await this.axios(`${this.API}productos/lineal`).then(res => {
        if (res.data) {
          res.data.forEach(productos => {
            this.caras.forEach(caras => {
              let historial = {
                Cara: caras.value,
                CaraDescripcion: caras.text,
                IdProducto: productos.IdProducto,
                Producto: productos.Producto,
                Galones: 0,
                IdSurtidor: this.IdSurtidor
              };
              this.lstFormHistorial.push(historial);
            });
          });
          //   this.lstProducto = res.data;
        }
      });
    },
    abrirModal() {
      if (this.lstHistorial.some(v => v.Estado)) {
        this.$toasted.error(
          "Por favor cierre el historial, para poder ingresar un nuevo registro"
        );
        return false;
      }
      this.obtenerListadoProductos();
      this.modal = true;
    },
    cerrarReg() {
      this.$swal({
        icon: "question",
        title: "Cerrar Historial",
        text: "¿Está seguro de registrar ",
        allowEscapeKey: false,
        allowEnterKey: false,
        allowOutsideClick: false,
        showCancelButton: true
      }).then(async respuesta => {
        if (respuesta.isConfirmed) {
          await this.axios
            .post(`${this.API}historial/cierre`, data)
            .then(res => {
              console.log(res.data);
              this.obtenerListadoHistorial();
            });
        }
      });
    },
    obtenerTurno(item) {
      let nameTurno = this.turnos.filter(v => v.value == item.IdTurno)[0];
      return nameTurno.text;
    },
    async guardar() {
      let data = {
        IdTurno: this.IdTurno,
        Historial: this.lstFormHistorial,
        Usuario: "admin"
      };
      await this.axios
        .post(`${this.API}historialoferta/guardar`, data)
        .then(res => {
          console.log(res.data);
          this.obtenerListadoHistorial();
        });
      this.modal = false;
    }
  }
};
</script>

<style>
</style>
