<template>
<div>
  <b-btn v-b-modal="idModal || 'modal2'" variant="primary">{{
    txtButton || "Nuevo"
  }}</b-btn>
  <!-- Modal Component -->
  <b-form @submit="guardarEmpleado" ref="form">
    <b-modal
      :id="idModal || 'modal2'"
      title="Nuevo Empleado"
      size="lg"
      ref="empleadoForm"
      v-model="show2"
      @shown="shownEmpleadoForm" modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered=""
    >
      <b-row>
        <b-col md="12" class="nst-editor-section">
          <b-form-group>
            <label>DNI / RUC:</label>
            <b-input-group>
              <b-form-input
                required="required"
                v-model="empleado.DniRuc"
                type="number"
              ></b-form-input>
              <b-input-group-append>
                <b-btn variant="info" @click="consultarDNIRUC">Buscar </b-btn>
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
          <b-form-group>
            <label>Nombre del Empleado:</label>
            <b-form-input
              required="required"
              v-model="empleado.Empleado"
              type="text"
            ></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Dirección 1:</label>
            <b-form-input
              v-model="empleado.Direccion"
              type="text"
            ></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>
      <b-row>
        <b-col>
          <b-form-group>
            <label>Teléfono:</label>
            <b-form-input
              v-model="empleado.Telefono"
              type="text"
            ></b-form-input>
          </b-form-group>
        </b-col>
        
      </b-row>
      <b-row>
        <b-col>
          <b-form-group>
            <label>Correo Electrónico:</label>
            <b-form-input
              v-model="empleado.Email"
              type="email"
            ></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>

      <div slot="modal-footer">
        <b-btn variant="primary" type="submit"> Guardar </b-btn>
        <b-btn variant="secondary" @click="show2 = false"> Close </b-btn>
      </div>
    </b-modal>
  </b-form>
</div>
</template>
<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      show2: false,
      empleado: {
        Empleado: "",
        DniRuc: "",
        Direccion: "",
        Direccion2: "",
        Direccion3: "",
        Telefono: "",
        Email: "",
        Sexo: "",
        Ocupacion: "",
        FechaNacimiento: "",
        Nacionalidad: ""
      }
    };
  },
  props: ["idModal", "newDniRuc", "editEmpleado", "txtButton"],
  /*watch: {
    show2 (val) {
      console.log(this.editEmpleado)
      if (this.editEmpleado.IdEmpleado) {
        this.empleado = this.editEmpleado
      }
    }
  },*/
  methods: {
    shownEmpleadoForm() {
      /*
      this.empleado.Direccion = ''
      this.empleado.Empleado = ''
      this.empleado.Telefono = ''
      this.empleado.Email = ''
*/
      console.log(this.newDniRuc);
      if (this.editEmpleado && this.editEmpleado.IdEmpleado) {
        this.empleado = this.editEmpleado;
      } else {
        this.empleado.DniRuc = this.newDniRuc;
        this.$nextTick(() => {
          this.consultarDNIRUC();
        });
      }
    },
    consultarDNIRUC() {
      var esNumerico = Number.isInteger(parseInt(this.empleado.DniRuc));
      if (!esNumerico) {
        this.$toasted.show("El DNI/RUC debe ser numérico, editar empleado.");
        return;
      }

      let tipo = this.empleado.DniRuc.length > 8 ? "RUC" : "DNI";

      this.axios
        .get(this.API + "consultarRUC", {
          params: {
            type: tipo,
            numero: this.empleado.DniRuc
          }
        })
        .then(response => {
          if (response.data) {
            this.$toasted.show("Empleado encontrado, se llenarán sus datos");
            var data = response.data;
            if (tipo == "DNI") {
              this.empleado.Empleado =
                data.nombres +
                " " +
                data.apellidoPaterno +
                " " +
                data.apellidoMaterno;
              this.empleado.Direccion = "";
            } else {
              this.empleado.Empleado = data.razonSocial;
              this.empleado.Direccion = data.direccion;
              // this.empleado.Telefono = data.Telefono
            }
          } else {
            this.$toasted.show("No se encontró el Usuario");
          }
        })
        .catch(error => {
          this.$toasted.show("No se encontró el Usuario");
          console.log(error);
        });
    },
    guardarEmpleado(evt) {
      evt.preventDefault();

      var esNumerico = Number.isInteger(parseInt(this.empleado.DniRuc));
      if (!esNumerico) {
        this.$toasted.show("El DNI/RUC debe ser numérico, editar empleado.");
        return;
      }

      var esDocumento =
        this.empleado.DniRuc.length > 7 && this.empleado.DniRuc.length <= 11
          ? true
          : false;
      if (!esDocumento) {
        this.$toasted.show("El Número de Documento es de 8 ó 11 digitos");
        return;
      }

      this.axios
        .post(this.API + "empleados", this.empleado)
        .then(response => {
          // console.log(response);
          this.$emit("onAddEmpleado", response.data);
          this.$toasted.show("Empleado guardado correctamente");
          this.$refs.empleadoForm.hide();
          this.show2 = false;
          this.empleado = {};
        })
        .catch(error => {
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    }
  }
};
</script>
<style scoped>
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>
