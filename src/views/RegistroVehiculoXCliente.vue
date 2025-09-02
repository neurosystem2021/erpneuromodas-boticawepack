<template>
  <b-container fluid>
    <b-row class="px-5">
        <b-col cols="6">
           
             <b-form-group>
                <label>Cliente:</label>
                <b-input-group>
                    <clientes-form txtButton="Editar" v-if="cliente.IdCliente && cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="cliente"></clientes-form>
                    <autocomplete
                        required="required"
                        ref="inputCliente"
                        :url="API + 'clientes'"
                        anchor="ClienteDniRuc"
                        label="writer"
                        placeholder=""
                        :onInput="(val) => newDniRuc = val"
                        :on-select="setCliente" 
                        :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                        :classes="{input: 'form-control'}">
                    </autocomplete>
                    <b-input-group-append>
                        <clientes-form :ruc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                    </b-input-group-append>
                </b-input-group>
            </b-form-group>
        </b-col>
        <b-col cols="6" style="padding: 30px !important; align-content: end !important; text-align: end !important;">
            <b-button variant="info"  @click="abrirModalNuevoRegistro" :disabled="!exiteCliente">Nuevo Vehículo</b-button>
        </b-col>
        <b-col cols="12">
            <b-table :items="lstVehiculos" :fields="fields">

                <template slot="comentario" slot-scope="data">
                    <b-button variant="info" @click="data.item.mostrarComentario=!data.item.mostrarComentario" v-if="!data.item.mostrarComentario"> <i class="fa fa-eye" aria-hidden="true"></i> </b-button>
                    <b-button variant="secondary" @click="data.item.mostrarComentario=!data.item.mostrarComentario" v-else> <i class="fa fa-eye-slash" aria-hidden="true"></i> </b-button>
                    <p v-if="data.item.mostrarComentario">
                       {{ data.item.comentario }} 
                    </p>
                    
                </template>
                <template slot="action" slot-scope="data">
                    <b-button variant="warning" @click="abrirModalActualizar(data.item)">
                        <i class="fas fa-pencil-alt"></i>
                    </b-button>
                    <b-button variant="danger" @click="eliminarVehiculo(data.item)">
                        <i class="fas fa-trash-alt"></i>
                    </b-button>
                </template>

            </b-table>
        </b-col>
    </b-row>
    <b-modal v-model="nuevoRegistroFlag" size="lg" hide-footer title="Vehículo" no-close-on-backdrop no-close-on-esc>
        <b-row>
            <b-col cols="12" md="6">
                <b-input-group prepend="Nombre Máquina" class="my-2">
                    <b-form-input :class="nombreError ? 'clsRequerido' :''" v-model="formVehiculo.nombre"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Marca Máquina" class="my-2">
                    <b-form-input :class="marcaError ? 'clsRequerido' :''" v-model="formVehiculo.marca"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Modelo Máquina" class="my-2">
                    <b-form-input :class="modeloError ? 'clsRequerido' :''" v-model="formVehiculo.modelo"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Serie Máquina" class="my-2">
                    <b-form-input :class="serieError ? 'clsRequerido' :''" v-model="formVehiculo.serie"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Horas" class="my-2">
                    <b-form-input :class="horasError ? 'clsRequerido' :''" v-model="formVehiculo.horas" type="number"></b-form-input>
                </b-input-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col>
                <hr>
            </b-col>
        </b-row>
        <b-row>
            <b-col cols="12" md="6">
                <b-input-group prepend="Motor del Vehículo" class="my-2">
                    <b-form-input :class="motorError ? 'clsRequerido' :''" v-model="formVehiculo.motor"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Serie del Motor" class="my-2">
                    <b-form-input :class="serieMotorError ? 'clsRequerido' :''" v-model="formVehiculo.serieMotor"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Año de Fab. Motor" class="my-2">
                    <b-form-input :class="aniomotorError ? 'clsRequerido' :''"  type="number" min="1800" max="2100" v-model="formVehiculo.aniomotor"></b-form-input>
                </b-input-group>
            </b-col>
            <!-- <b-col cols="12" md="6">
                <b-input-group prepend="Carga Bruta del Motor " class="my-2">
                    <b-form-input v-model="formVehiculo.cargaBruta" type="number"></b-form-input>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6">
                <b-input-group prepend="Carga Neta del Motor " class="my-2">
                    <b-form-input v-model="formVehiculo.cargaNeta" type="number"></b-form-input>
                </b-input-group>
            </b-col> -->
            <b-col cols="12">
                <b-input-group prepend="Comentario " class="my-2">
                     <b-form-textarea :class="comentarioError ? 'clsRequerido' :''" v-model="formVehiculo.comentario" class="txtComentario"/>
                </b-input-group>
            </b-col>
        </b-row>
        <b-row>
            <b-col cols="12" style="text-align: right !important; align-items: right !important; align-content: right !important;">
                    <b-button variant="success" v-if="nuevoFlag" @click="guardarVehiculo">Guardar</b-button>
                    <b-button variant="success" v-if="actualizarFlag" @click="actualizarVehiculo">Actualizar</b-button>
                    <b-button variant="danger" @click="nuevoRegistroFlag=!nuevoRegistroFlag">Cancelar</b-button>              
            </b-col>
        </b-row>
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
import ClientesForm from "@/components/ClientesForm";
export default {
  components: {
    ClientesForm
  },
  data() {
    return {
      nuevoRegistroFlag: false,
      nuevoFlag: true,
      actualizarFlag: true,
      formVehiculo: {
        nombre: "",
        marca: "",
        modelo: "",
        serie: "",
        horas: "",
        motor: "",
        serieMotor: "",
        cargaBruta: "",
        cargaNeta: "",
        comentario: "",
        aniomotor: ""
      },
      cliente: {},
      newDniRuc: "",
      API: config.API,
      lstVehiculos: [],
      fields: [
        { key: "nombre", label: "Nombre Máquina" },
        { key: "marca", label: "Marca Vehículo" },
        { key: "modelo", label: "Modelo Máquina" },
        { key: "serie", label: "Serie Máquina" },
        { key: "horas" },
        { key: "motor", label: "Modelo Vehículo" },
        { key: "seriemotor", label: "Serie Motor" },
        { key: "aniomotor", label: "Año Fabricación" },
        // { key: "cargabruta", label: "Carga Bruta" },
        // { key: "carganeta", label: "Carga Neta" },
        { key: "comentario", label: "Comentarios" },
        { key: "action", label: "" }
      ],
      nombreError: false,
      marcaError: false,
      modeloError: false,
      serieError: false,
      horasError: false,
      motorError: false,
      serieMotorError: false,
      comentarioError: false,
      aniomotorError: false
    };
  },
  methods: {
    cusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    setCliente(val) {
      this.cliente = val;
      this.obtenerVehiculos();
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    abrirModalNuevoRegistro() {
      this.nuevoFlag = true;
      this.actualizarFlag = false;
      this.formVehiculo = {
        nombre: "",
        marca: "",
        modelo: "",
        serie: "",
        horas: "",
        motor: "",
        serieMotor: "",
        cargaBruta: "",
        cargaNeta: "",
        aniomotor: "",
        comentario: ""
      };
      this.nombreError = false;
      this.marcaError = false;
      this.modeloError = false;
      this.serieError = false;
      this.horasError = false;
      this.motorError = false;
      this.serieMotorError = false;
      this.comentarioError = false;
      this.aniomotorError = false;
      this.nuevoRegistroFlag = true;
    },
    validarFormulario() {
      this.nombreError = false;
      this.marcaError = false;
      this.modeloError = false;
      this.serieError = false;
      this.horasError = false;
      this.motorError = false;
      this.serieMotorError = false;
      this.aniomotorError = false;
      this.comentarioError = false;
      let val = false;
      if (!this.formVehiculo.marca) {
        this.nombreError = true;
        val = true;
      }
      if (!this.formVehiculo.marca) {
        this.marcaError = true;
        val = true;
      }
      if (!this.formVehiculo.modelo) {
        this.modeloError = true;
        val = true;
      }
      if (!this.formVehiculo.serie) {
        this.serieError = true;
        val = true;
      }
      if (!this.formVehiculo.horas) {
        this.horasError = true;
        val = true;
      }
      if (!this.formVehiculo.motor) {
        this.motorError = true;
        val = true;
      }
      if (!this.formVehiculo.serieMotor) {
        this.serieMotorError = true;
        val = true;
      }
      if (!this.formVehiculo.aniomotor) {
        this.aniomotorError = true;
        val = true;
      }
      if (!this.formVehiculo.comentario) {
        this.comentarioError = true;
        val = true;
      }
      return val;
    },
    abrirModalActualizar(item) {
      this.nombreError = false;
      this.marcaError = false;
      this.modeloError = false;
      this.serieError = false;
      this.horasError = false;
      this.motorError = false;
      this.serieMotorError = false;
      this.comentarioError = false;
      this.aniomotorError = false;
      let vehiculo = { ...item };
      vehiculo.serieMotor = item.seriemotor;
      this.nuevoFlag = false;
      this.actualizarFlag = true;
      this.nuevoRegistroFlag = true;
      this.formVehiculo = vehiculo;
    },
    async obtenerVehiculos() {
      this.lstVehiculos = [];
      await this.axios(`${this.API}cliente/vehiculo`, {
        params: this.cliente
      }).then(res => {
        if (res.data) {
          let data = res.data;
          data = data.map(item => {
            return { ...item, mostrarComentario: false };
          });
          this.lstVehiculos = data;
        }
      });
    },
    async guardarVehiculo() {
      let res = this.validarFormulario();
      if (res) {
        return;
      }
      let data = { ...this.cliente, ...this.formVehiculo };
        await this.axios.post(`${this.API}vehiculo`, data);
        this.obtenerVehiculos();
        this.nuevoRegistroFlag = false;
    },
    async actualizarVehiculo() {
      let res = this.validarFormulario();
      if (res) {
        return;
      }
      let data = { ...this.cliente, ...this.formVehiculo };
      console.log(data);
      await this.axios.put(`${this.API}vehiculo/actualizar`, data);
      this.obtenerVehiculos();
      this.nuevoRegistroFlag = false;
    },
    eliminarVehiculo(item) {
      this.$swal({
        icon: "question",
        title: "¿Eliminar Registro?",
        text: "El registro será eliminado.",
        showCancelButton: true,
        confirmButtonText: "Eliminar Regisrtro",
        confirmButtonColor: "#D50000",
        cancelButtonColor: "#1B5E20",
        allowOutsideClick: false,
        allowEscapeKey: false
      }).then(async res => {
        if (res.isConfirmed) {
          let data = { ...this.cliente, ...item };
          await this.axios.put(`${this.API}vehiculo/eliminar`, data);

          this.obtenerVehiculos();
        }
      });

      //   this.nuevoRegistroFlag = false;
    }
  },
  watch: {
    "formVehiculo.nombre"() {
      if (this.formVehiculo.nombre) {
        this.nombreError = false;
      }
    },
    "formVehiculo.marca"() {
      if (this.formVehiculo.marca) {
        this.marcaError = false;
      }
    },
    "formVehiculo.modelo"() {
      if (this.formVehiculo.modelo) {
        this.modeloError = false;
      }
    },
    "formVehiculo.serie"() {
      if (this.formVehiculo.serie) {
        this.serieError = false;
      }
    },
    "formVehiculo.horas"() {
      if (this.formVehiculo.horas) {
        this.horasError = false;
      }
    },
    "formVehiculo.motor"() {
      if (this.formVehiculo.motor) {
        this.motorError = false;
      }
    },
    "formVehiculo.serieMotor"() {
      if (this.formVehiculo.serieMotor) {
        this.serieMotorError = false;
      }
    },

    "formVehiculo.comentario"() {
      if (this.formVehiculo.comentario) {
        this.comentarioError = false;
      }
    },
    "formVehiculo.aniomotor"() {
      if (this.formVehiculo.aniomotor) {
        this.aniomotorError = false;
      }
    }
  },
  computed: {
    exiteCliente() {
      return Object.keys(this.cliente).length > 0;
    }
  }
};
</script>


<style scoped>
.txtComentario {
  field-sizing: content;
}
.clsRequerido {
  border: 1px solid #d50000;
}
</style>
