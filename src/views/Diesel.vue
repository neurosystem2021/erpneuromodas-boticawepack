<template>
  <b-container>
    <b-row>
    <b-col cols="12">
        <center><h1>Listado Registro Diesel</h1></center>
    </b-col>
    <b-col cols="12">
            <b-button class="my-2" variant="success" @click="abrirModal()">Nuevo Registro</b-button>
        </b-col>
    <b-col cols="12">
        <b-table 
              :fields="fields" 
              :items="lstHistorial"
              :per-page="perPage"
              :current-page="currentPage"  
            >
                <template slot="Cara" slot-scope="data">
                    {{ data.item.Cara ==1 ? 'Cara A' : 'Cara B'  }}
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
  <b-modal v-model="modal" hide-footer size="lg" :title="`Registrar información del cronómetro ${producto.Producto}`">
    <b-row>

        <b-col cols="12" class="my-2">
            Surtidor
                        <b-form-select v-model="IdSurtidor" :options="Surtidor" class="mb-3"/>
                      </b-col>
        <b-col cols="6" class="my-2">
            <div class="cardCaraA">
                Cronómetro Lado A
                <input type="number" class="form-control" v-model="MontoCaraA" :disabled="producto.TieneCaraA ==0">

            </div>
        </b-col>
        <b-col cols="6" class="my-2">
            <div class="cardCaraB">
                Cronómetro Lado B
                <input type="number" class="form-control" v-model="MontoCaraB" :disabled="producto.TieneCaraB ==0">

            </div>
        </b-col>
        
        <b-col cols="6" class="my-2">
            Fecha Actual:
            <input type="date" class="form-control" v-model="fecha" disabled>
        </b-col>
        <b-col cols="12" class="my-2">
            <b-button variant="success" @click="guardarHistorial()">Guardar</b-button>
            <b-button variant="danger" @click="modal=!modal">Cancelar</b-button>
        </b-col>
    </b-row>
    <!-- {{ producto }} -->
  </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      lstHistorial: [],
      perPage: 10,
      currentPage: 1,
      fields: [
        { key: "IdHistorico" },
        { key: "IdSurtidor", label: "Surtidor" },
        { key: "FechaReg" },
        // { key: "Turno", label: "Turno" },
        { key: "Estado" },
        { key: "Producto" },
        { key: "Cara" },
        { key: "Galones" },
        { key: "UsuarioReg" }
      ],
      modal: false,
      producto: {},
      fecha: null,
      Surtidor: [
        { value: 1, text: "Surtidor 1" },
        { value: 2, text: "Surtidor 2" }
      ],
      IdSurtidor: null,
      IdProduco: 1,
      MontoCaraA: 0,
      MontoCaraB: 0
    };
  },
  mounted() {
    this.obtenerListadoHistorial();
    this.obtenerProducto();
    this.fecha = this.$moment().format("YYYY-MM-DD");
  },
  methods: {
    async obtenerListadoHistorial() {
      await this.axios(
        `${this.API}historialoferta?idProducto=${this.IdProduco}`
      ).then(res => {
        if (res.data) {
          this.lstHistorial = res.data;
        }
      });
    },
    async obtenerProducto() {
      this.axios(`${this.API}/productos/id/${this.IdProduco}`).then(res => {
        if (res.data) {
          this.producto = res.data;
        }
      });
    },
    abrirModal() {
      this.IdSurtidor = "";
      this.MontoCaraA = 0;
      this.MontoCaraB = 0;
      this.$swal({
        icon: "question",
        title: "Nuevo Registro",
        text:
          "¿Desea realizar, un nuevo registro, se cerrarán los anteriores activos.?",
        allowEscapeKey: false,
        allowEnterKey: false,
        allowOutsideClick: false,
        showCancelButton: true
      }).then(res => {
        if (res.isConfirmed) {
          this.modal = true;
        }
      });
    },
    async guardarHistorial() {
      let data = {
        MontoCaraA: Number(this.MontoCaraA),
        MontoCaraB: Number(this.MontoCaraB),
        IdProducto: this.IdProduco,
        IdSurtidor: this.IdSurtidor,
        User: sessionStorage.getItem("User")
      };
      await this.axios.post(`${this.API}historial/guardar`, data).then(res => {
        console.log(res);
        this.obtenerListadoHistorial();
        this.obtenerProducto();
        this.modal = false;
      });
    }
  }
};
</script>

<style>
.cardCaraA {
  border: 1px solid #1976d2;
  border-radius: 15px;
  min-height: 100px;
  padding: 5%;
  background: #e3f2fd;
}
.cardCaraB {
  border: 1px solid #616161;
  border-radius: 15px;
  min-height: 100px;
  padding: 5%;
  background: #eeeeee;
}
</style>
