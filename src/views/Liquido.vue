<template>
  <b-container fluid>
    <b-row>
    <b-col cols="12">
        <center><h1>Listado Registro Líquido</h1></center>
    </b-col>
    
       <b-col cols="4">  <b-input-group class="mt-2" prepend="Desde:" > 
          <input v-model="fechaDesde" class="form-control" type="date" /> </b-input-group> 
        </b-col>
       <b-col cols="4">  <b-input-group class="mt-2" prepend="Hasta:" > 
          <input v-model="fechaHasta" class="form-control" type="date" /> </b-input-group> 
        </b-col>
       <b-col cols="4">
          <b-button variant="success" @click="obtenerListadoHistorial"> Generar</b-button>
            <b-button class="my-2" variant="success" @click="abrirModal()">Nuevo Registro</b-button>
        </b-col>
        
    <b-col cols="12">
        <center><h1>Listado Registro Líquido</h1></center>
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
                <template slot="IdSurtidor" slot-scope="data">
                 Isla   {{ data.item.IdSurtidor }}
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
  <b-modal v-model="modal" hide-footer size="lg" no-close-on-backdrop no-close-on-esc no-enforce-focus :title="`Registrar información del cronómetro`">
    <b-row>
      <b-col cols="6" class="my-2">
          Fecha Actual:
          <input type="date" class="form-control" v-model="fecha" disabled>
      </b-col>

      <b-col cols="6" class="my-2">
        Vend.
          <b-input-group>
            <autocomplete
              required="required"
              ref="inputVendedor"
              :url="API + 'usuarios'"
              anchor="NombreUsuario"
              label="writer"
              :on-select="setVendedor" 
              :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
              :classes="{input: 'form-control'}">
            </autocomplete>
                              </b-input-group>
      </b-col>

      <b-col cols="12" class="my-2">
            Isla
            <b-form-select v-model="IdSurtidor" :options="Surtidor" class="mb-3"  
               value-field="IdIsla"
               text-field="Isla"
               @change="obtenerProductoPorTipo($event)"
               />
        </b-col>

        <b-col cols="12" class="my-2" v-if="IdSurtidor">
            Producto:
           <b-form-select 
            v-model="IdProduco" value-field="IdProducto" text-field="Producto"
            :options="Productos" class="mb-3" @change="obtenerProducto()"/>

        </b-col>
        
        <b-col cols="6" class="my-2" v-if="IdProduco">
            <div class="cardCaraA">
                Cronómetro Lado A
                <input type="number" class="form-control" v-model="MontoCaraA" :disabled="producto.TieneCaraA ==0">

            </div>
        </b-col>
        <b-col cols="6" class="my-2" v-if="IdProduco">
            <div class="cardCaraB">
                Cronómetro Lado B
                <input type="number" class="form-control" v-model="MontoCaraB" :disabled="producto.TieneCaraB ==0">

            </div>
        </b-col>
        
        <b-col cols="12" class="my-2" v-if="IdProduco">
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
        // { key: "IdHistorico" },
        { key: "Isla", label: "Isla" },
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
      Productos: [],
      fecha: null,
      Surtidor: [],
      IdSurtidor: null,
      IdProduco: null,
      IdTipoProducto: 1,
      MontoCaraA: 0,
      MontoCaraB: 0,
      Usuario: "",
      IdSurtidor: "",
      vendedor: {},
      fechaDesde: "",
      fechaHasta: ""
    };
  },
  mounted() {
    this.Usuario = sessionStorage.getItem("User");
    this.IdSurtidor = sessionStorage.getItem("IdSurtidor");
    this.fechaDesde = this.$moment().format("YYYY-MM-DD");
    this.fechaHasta = this.$moment().format("YYYY-MM-DD");

    this.obtenerListadoHistorial();
    this.ObtenerIsla();
    // this.obtenerProductoPorTipo();
    // this.obtenerProducto();
    this.fecha = this.$moment().format("YYYY-MM-DD");
  },
  methods: {
    async ObtenerIsla() {
      await this.axios(`${this.API}isla`).then(res => {
        if (res.data) {
          this.Surtidor = res.data;
        }
      });
    },
    async obtenerProductoPorTipo(IdSurtidor) {
      console.log(IdSurtidor);
      let params = {
        IdTipoProducto: this.IdTipoProducto,
        IdIsla: IdSurtidor
      };
      await this.axios(`${this.API}producto/tipoproducto`, {
        params: params
      }).then(res => {
        if (res.data) {
          this.Productos = res.data;
        }
      });
    },
    async obtenerListadoHistorial() {
      await this.axios(`${this.API}historialoferta`, {
        params: {
          IdTipoProducto: this.IdTipoProducto,
          Usuario: this.Usuario,
          IdSurtidor: this.IdSurtidor,
          fechaDesde: this.fechaDesde,
          fechaHasta: this.fechaHasta
        }
      }).then(res => {
        if (res.data) {
          this.lstHistorial = res.data;
        }
      });
    },
    async obtenerProducto() {
      this.$nextTick(() => {
        this.axios(`${this.API}/productos/id/${this.IdProduco}`).then(res => {
          if (res.data) {
            this.producto = res.data;
          }
        });
      });
    },
    setVendedor(obj) {
      this.vendedor = obj;
    },
    focusAutocomplete(ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
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
      this.$nextTick(() => {
        this.vendedor = {
          Usuario: this.Usuario
        };
        this.$refs.inputVendedor.setValue(this.Usuario);
      });
    },
    async guardarHistorial() {
      if (Object.keys(this.vendedor).length == 0) {
        this.$toasted.error("Seleccione un Vendedor");
        return;
      }
      let data = {
        MontoCaraA: Number(this.MontoCaraA),
        MontoCaraB: Number(this.MontoCaraB),
        IdProducto: this.IdProduco,
        IdSurtidor: this.IdSurtidor,
        User: this.vendedor.Usuario
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
