<template>
  <div>
    <b-btn class="client-form-trigger" variant="primary" @click="abrirClienteForm">
      <i :class="txtButton ? 'fas fa-user-edit' : 'fas fa-user-plus'"></i>
      <span>{{ txtButton || 'Nuevo' }}</span>
    </b-btn>
    <!-- Modal Component -->
    <b-modal
      :id="idModal || 'modal1'"
      size="lg"
      centered
      ref="clienteForm"
      v-model="show"
      modal-class="client-modal"
      dialog-class="client-modal-dialog"
      content-class="client-modal-content"
      @shown="shownClienteForm"
    >
      <template slot="modal-title">
        <span class="client-modal-title-icon"><i class="fas fa-user-plus"></i></span>
        <span class="client-modal-title-copy">
          <strong>{{ editCliente && editCliente.IdCliente ? 'Editar cliente' : 'Nuevo cliente' }}</strong>
          <small>Información comercial y de contacto</small>
        </span>
      </template>
      <div class="client-modal-intro">
        <span><i class="fas fa-shield-alt"></i></span>
        <div>
          <strong>Ficha del cliente</strong>
          <small>Completa los datos necesarios para identificarlo en la venta.</small>
        </div>
      </div>
      <b-row class="client-form-grid">
        <b-col md="12">
          <b-form-group class="client-field client-field-document">
            <label><i class="fas fa-id-card"></i> DNI / RUC</label>
            <b-input-group class="client-document-control">
              <b-form-input 
                required="required"
                placeholder="Ingresa 8 u 11 dígitos"
                v-model="cliente.DniRuc" type="text" inputmode="numeric" maxlength="11"
                @input="normalizarDocumento"></b-form-input>
              <b-input-group-append>
                <b-btn variant="info" :disabled="consultandoDocumento" @click="consultarDNIRUC">
                  <i :class="consultandoDocumento ? 'fas fa-spinner fa-spin' : 'fas fa-search'"></i><span>{{ consultandoDocumento ? 'Consultando...' : 'Buscar' }}</span>
                </b-btn>
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
          <b-form-group class="client-field">
            <label><i class="fas fa-user"></i> Nombre del cliente</label>
            <b-form-input 
                required="required"
                placeholder="Nombres o razón social"
                v-model="cliente.Cliente"
                type="text"></b-form-input>
          </b-form-group>
          <b-form-group class="client-field">
            <label><i class="fas fa-map-marker-alt"></i> Dirección principal</label>
            <b-form-input 
              placeholder="Dirección fiscal o principal"
              v-model="cliente.Direccion"
              type="text"></b-form-input>
          </b-form-group>
          <b-form-group class="client-field">
            <label><i class="fas fa-map-pin"></i> Dirección secundaria</label>
            <b-form-input 
              placeholder="Dirección adicional (opcional)"
              v-model="cliente.Direccion2"
              type="text"></b-form-input>
          </b-form-group>
          <b-form-group class="client-field">
            <label><i class="fas fa-map-pin"></i> Dirección adicional</label>
            <b-form-input 
              placeholder="Otra dirección (opcional)"
              v-model="cliente.Direccion3"
              type="text"></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>
      <b-row class="client-contact-row">
          <b-col>
            <b-form-group class="client-field">
                <label><i class="fas fa-phone-alt"></i> Teléfono</label>
                <b-form-input 
                placeholder="Número de contacto"
                v-model="cliente.Telefono"
                type="text"></b-form-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group class="client-field">
                <label><i class="fas fa-envelope"></i> Correo electrónico</label>
                <b-form-input 
                placeholder="correo@ejemplo.com"
                v-model="cliente.Email"
                type="email"></b-form-input>
            </b-form-group>
          </b-col>
      </b-row>
      <b-row class="client-options-row">
        <b-col>
          <b-form-checkbox id="checkbox1" class="client-vip-check"
            v-model="cliente.EsVIP"
            :value="1"
            :unchecked-value="0">
            <i class="fas fa-crown"></i> Cliente VIP
          </b-form-checkbox>
        </b-col>
        <b-col>
          <b-form-group class="client-field mb-0">
            <label><i class="fas fa-birthday-cake"></i> Fecha de nacimiento</label>
            <b-form-input 
              v-model="cliente.FechaNacimiento"
              type="date"></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>
      <div slot="modal-footer" class="client-modal-actions">
        <b-btn variant="secondary" @click="show=false">
          <i class="fas fa-times"></i> Cancelar
        </b-btn>
        <b-btn variant="primary" @click="guardarCliente">
          <i class="fas fa-save"></i> Guardar cliente
        </b-btn>
       </div>
    </b-modal>
  </div>
</template>
<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      show: false,
      consultandoDocumento: false,
      cliente: {
        Cliente: "",
        DniRuc: "",
        Direccion: "",
        Direccion2: "",
        Direccion3: "",
        Telefono: "",
        Email: "",
        FechaNacimiento: ""
      }
    };
  },
  props: ["idModal", "newDniRuc", "editCliente", "txtButton"],
  mounted() {
    /*
     * bootstrap-vue rc.11 renders b-modal at the declaration site instead of
     * portalling it to <body>. This component is used inside an input-group,
     * so the modal was being clipped by the client header. Move only the
     * modal host outside the form controls while keeping Vue ownership.
     */
    this.$nextTick(() => {
      const modalHost = this.$refs.clienteForm && this.$refs.clienteForm.$el;
      if (modalHost && modalHost.parentNode !== document.body) {
        document.body.appendChild(modalHost);
      }
    });
  },
  beforeDestroy() {
    const modalHost = this.$refs.clienteForm && this.$refs.clienteForm.$el;
    if (modalHost && modalHost.parentNode === document.body && this.$el) {
      this.$el.appendChild(modalHost);
    }
  },
  // watch: {
  //   show(val) {
  //     this.$nextTick(() => {
  //       setTimeout(() => {
  //         this.cliente.DniRuc = this.newDniRuc;
  //         this.consultarDNIRUC();
  //       }, 100);
  //     });
  //   }
  // },
  methods: {
    normalizarDocumento(valor) {
      this.cliente.DniRuc = String(valor || "").replace(/\D/g, "").slice(0, 11);
    },
    abrirClienteForm() {
      this.show = true;
    },
    shownClienteForm() {
      /*
      this.cliente.Direccion = ''
      this.cliente.Cliente = ''
      this.cliente.Telefono = ''
      this.cliente.Email = ''
*/

      if (this.editCliente && this.editCliente.IdCliente) {
        this.cliente = this.editCliente;
        console.log('a')
      } else {
        console.log('b')
        this.cliente.DniRuc = this.newDniRuc || "";
        if (this.cliente.DniRuc) {
          this.consultarDNIRUC();
        }
      }
    },
    consultarDNIRUC() {
      this.normalizarDocumento(this.cliente.DniRuc);
      if (![8, 11].includes(this.cliente.DniRuc.length)) {
        this.$toasted.error("Ingrese un DNI de 8 dígitos o un RUC de 11 dígitos.");
        return;
      }

      let tipo = this.cliente.DniRuc.length === 11 ? "RUC" : "DNI";

      this.consultandoDocumento = true;
      this.axios
        .get(this.API + "consultarRUC", {
          params: {
            type: tipo,
            numero: this.cliente.DniRuc
          }
        })
        .then(response => {
          var data = response.data;
          if (!data || data.success === false) {
            throw new Error((data && data.message) || "No se encontraron datos para este documento.");
          }

          if (tipo == "DNI") {
            const nombreCompleto = [data.nombres, data.apellidoPaterno, data.apellidoMaterno]
              .filter(Boolean)
              .join(" ")
              .trim();
            if (!nombreCompleto) throw new Error("El servicio no devolvió el nombre del DNI.");
            this.cliente.Cliente = nombreCompleto;
            this.cliente.Direccion = "";
          } else {
            if (!data.razonSocial) throw new Error("El servicio no devolvió la razón social del RUC.");
            this.cliente.Cliente = data.razonSocial;
            this.cliente.Direccion = data.direccion || "";
          }
          this.$toasted.success(`${tipo} encontrado. Se completaron los datos.`);
        })
        .catch(error => {
          const data = error.response && error.response.data;
          this.$toasted.error((data && data.message) || error.message || "No se pudo consultar el documento.");
          console.log(error);
        })
        .then(() => {
          this.consultandoDocumento = false;
        });
    },
    guardarCliente(evt) {
      if (evt) evt.preventDefault();

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
