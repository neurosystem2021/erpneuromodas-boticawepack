<template>
<div>
  <b-btn
    v-b-modal="idModal || 'modal1'"
    variant="primary"
    :class="buttonClass"
    :title="buttonTitle || txtButton || 'Nuevo cliente'"
    :aria-label="buttonTitle || txtButton || 'Nuevo cliente'">
    <i v-if="buttonIcon" :class="buttonIcon" aria-hidden="true"></i>
    <span v-if="txtButton">{{ txtButton }}</span>
    <span v-else-if="!buttonIcon">Nuevo</span>
  </b-btn>
  <!-- Modal Component -->
  <b-form @submit="guardarCliente" ref="form">    
  <b-modal :id="idModal || 'modal1'" :title="editCliente && editCliente.IdCliente ? 'Editar cliente' : 'Nuevo cliente'" size="lg" ref="clienteForm" v-model="show" @shown="shownClienteForm" :modal-class="projectMode ? 'nst-dialog nst-client-dialog project-client-dialog' : 'nst-dialog nst-client-dialog'" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
    <b-row>
      <b-col md="12" class="nst-editor-section nst-editor-section--identity">
        <h3><i class="fas fa-address-card" aria-hidden="true"></i> Identificación y direcciones del cliente</h3>
        <b-form-group>
          <label>DNI / RUC:</label>
          <b-input-group>
            <b-form-input 
              required="required"
              v-model="cliente.DniRuc" type="number"></b-form-input>
            <b-input-group-append>
              <b-btn variant="info" @click="consultarDNIRUC">Buscar </b-btn>
            </b-input-group-append>
          </b-input-group>
        </b-form-group>
        <b-form-group>
          <label>Nombre del Cliente:</label>
          <b-form-input 
              required="required"
              v-model="cliente.Cliente"
              type="text"></b-form-input>
        </b-form-group>
        <b-form-group v-if="projectMode">
          <label>Teléfono:</label><b-form-input v-model="cliente.Telefono" type="tel" autocomplete="tel"></b-form-input>
        </b-form-group>
        <b-form-group>
          <label>{{ projectMode ? "Dirección:" : "Dirección 1:" }}</label>
          <b-form-input 
            v-model="cliente.Direccion"
            type="text"></b-form-input>
        </b-form-group>
        <b-form-group v-if="!projectMode">
          <label>Dirección 2:</label>
          <b-form-input 
            v-model="cliente.Direccion2"
            type="text"></b-form-input>
        </b-form-group>
        <b-form-group v-if="!projectMode">
          <label>Dirección 3:</label>
          <b-form-input 
            v-model="cliente.Direccion3"
            type="text"></b-form-input>
        </b-form-group>
      </b-col>
    </b-row>
    <b-row>
        <b-col v-if="!projectMode">
          <b-form-group>
              <label>Teléfono:</label>
              <b-form-input 
              v-model="cliente.Telefono"
              type="text"></b-form-input>
          </b-form-group>
        </b-col>
        <b-col>
          <b-form-group>
              <label>Correo Electrónico:</label>
              <b-form-input 
              v-model="cliente.Email"
              type="email"></b-form-input>
          </b-form-group>
        </b-col>
        <b-col v-if="!projectMode">
          <b-form-group>
              <label>Nacionalidad:</label>
              <b-form-input 
              v-model="cliente.Nacionalidad"
              type="text"></b-form-input>
          </b-form-group>
        </b-col>
    </b-row>
    <b-row v-if="!projectMode">
        <b-col>
          <b-form-group>
              <label>Sexo:</label>
              <b-form-select v-model="cliente.Sexo" :options="['M', 'F']" class="mb-3" />
          </b-form-group>
        </b-col>
        <b-col>
          <b-form-group>
              <label>Ocupación:</label>
              <b-form-input 
              v-model="cliente.Ocupacion"
              type="text"></b-form-input>
          </b-form-group>
        </b-col>
    </b-row>
    <b-row>
      <b-col>
        <b-form-checkbox 
                   v-model="cliente.EsVIP"
                   :value="1"
                   :unchecked-value="0">
          Marcar si es un cliente VIP
        </b-form-checkbox>
      </b-col>
      <b-col>
        <b-form-group>
          <label>Fecha Nacimiento:</label>
          <b-form-input 
            v-model="cliente.FechaNacimiento"
            type="date"></b-form-input>
        </b-form-group>
      </b-col>
    </b-row>
    <div slot="modal-footer" >
      <b-btn  variant="primary" type="submit">
         Guardar
       </b-btn>
       <b-btn  variant="secondary" @click="show=false">
         Close
       </b-btn>
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
      show: false,
      cliente: {
        Cliente: "",
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
  props: ["idModal", "newDniRuc", "editCliente", "txtButton", "buttonIcon", "buttonClass", "buttonTitle", "projectMode"],
  /*watch: {
    show (val) {
      console.log(this.editCliente)
      if (this.editCliente.IdCliente) {
        this.cliente = this.editCliente
      }
    }
  },*/
  methods: {
    shownClienteForm() {
      /*
      this.cliente.Direccion = ''
      this.cliente.Cliente = ''
      this.cliente.Telefono = ''
      this.cliente.Email = ''
*/
      console.log(this.newDniRuc);
      if (this.editCliente && this.editCliente.IdCliente) {
        this.cliente = Object.assign({}, this.editCliente);
      } else {
        this.cliente.DniRuc = this.newDniRuc;
      }
    },
    consultarDNIRUC() {
      var esNumerico = Number.isInteger(parseInt(this.cliente.DniRuc));
      if (!esNumerico) {
        this.$toasted.show("El DNI/RUC debe ser numérico, editar cliente.");
        return;
      }

      let tipo = this.cliente.DniRuc.length > 8 ? "RUC" : "DNI";

      this.axios
        .get(this.API + "consultarRUC", {
          params: {
            type: tipo,
            numero: this.cliente.DniRuc
          }
        })
        .then(response => {
          if (response.data) {
            this.$toasted.show("Cliente encontrado, se llenarán sus datos");
            var data = response.data;
            if (tipo == "DNI") {
              this.cliente.Cliente =
                data.nombres +
                " " +
                data.apellidoPaterno +
                " " +
                data.apellidoMaterno;
              this.cliente.Direccion = "";
            } else {
              this.cliente.Cliente = data.razonSocial;
              this.cliente.Direccion = data.direccion;
              // this.cliente.Telefono = data.Telefono
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
    guardarCliente(evt) {
      evt.preventDefault();

      var esNumerico = Number.isInteger(parseInt(this.cliente.DniRuc));
      if (!esNumerico) {
        this.$toasted.show("El DNI/RUC debe ser numérico, editar cliente.");
        return;
      }

      var esDocumento =
        this.cliente.DniRuc.length > 7 && this.cliente.DniRuc.length <= 11
          ? true
          : false;
      if (!esDocumento) {
        this.$toasted.show("El Número de Documento es de 8 ó 11 digitos");
        return;
      }

      this.axios
        .post(this.API + "clientes", this.cliente)
        .then(response => {
          // console.log(response);
          this.$emit("onAddCliente", response.data);
          this.$toasted.show("Cliente guardado correctamente");
          this.$refs.clienteForm.hide();
          this.cliente = {};
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
