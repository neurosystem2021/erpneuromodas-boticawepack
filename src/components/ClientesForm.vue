<template>
  <div>
    <b-btn v-b-modal="idModal || 'modal1'" variant="primary">{{ txtButton || 'Nuevo' }}</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarCliente" ref="form">
      <b-modal :id="idModal || 'modal1'" title="Nuevo Cliente" size="lg" ref="clienteForm" v-model="show"
        @shown="shownClienteForm">
        <b-row>
          <b-col md="12">
            <b-form-group>
              <label>DNI / RUC:</label>
              <b-input-group>
                <b-form-input required="required" v-model="cliente.DniRuc" type="number"></b-form-input>
                <b-input-group-append>
                  <b-btn variant="info" @click="consultarDNIRUC">Buscar </b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
            <b-form-group>
              <label>Nombre del Cliente:</label>
              <b-form-input required="required" v-model="cliente.Cliente" type="text"></b-form-input>
            </b-form-group>
            <b-form-group>
              <label>Dirección 1:</label>
              <b-form-input v-model="cliente.Direccion" type="text"></b-form-input>
            </b-form-group>
            <b-form-group>
              <label>Dirección 2:</label>
              <b-form-input v-model="cliente.Direccion2" type="text"></b-form-input>
            </b-form-group>
            <b-form-group>
              <label>Dirección 3:</label>
              <b-form-input v-model="cliente.Direccion3" type="text"></b-form-input>
            </b-form-group>
          </b-col>
        </b-row>

        <b-row>
          <b-col md="4">
            <b-form-group label="Departamento:">
              <b-form-select 
                required="required"
                v-model="ubigeoSeleccionado.departamento" 
                @change="cambioDepartamento">
                <option :value="null">-- Seleccione --</option>
                <option 
                  v-for="dep in $store.state.listUbigeo" 
                  :key="dep.id" 
                  :value="dep">
                  {{ dep.name }}
                </option>
              </b-form-select>
            </b-form-group>
          </b-col>
          <b-col md="4">
            <b-form-group label="Provincia:">
              <b-form-select 
                required="required"
                v-model="ubigeoSeleccionado.provincia" 
                :disabled="!ubigeoSeleccionado.departamento"
                @change="cambioProvincia">
                <option :value="null">-- Seleccione --</option>
                <option 
                  v-for="prov in provinciasDisponibles" 
                  :key="prov.id" 
                  :value="prov">
                  {{ prov.name }}
                </option>
              </b-form-select>
            </b-form-group>
          </b-col>
          <b-col md="4">
            <b-form-group label="Distrito:">
              <b-form-select 
                required="required"
                v-model="ubigeoSeleccionado.distrito" 
                :disabled="!ubigeoSeleccionado.provincia"
                @change="cambioDistrito">
                <option :value="null">-- Seleccione --</option>
                <option 
                  v-for="dist in distritosDisponibles" 
                  :key="dist.id" 
                  :value="dist">
                  {{ dist.name }}
                </option>
              </b-form-select>
            </b-form-group>
          </b-col>
        </b-row>

        <b-row>
          <b-col>
            <b-form-group>
              <label>Teléfono:</label>
              <b-form-input v-model="cliente.Telefono" type="text"></b-form-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group>
              <label>Correo Electrónico:</label>
              <b-form-input v-model="cliente.Email" type="email"></b-form-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group>
              <label>Nacionalidad:</label>
              <b-form-input v-model="cliente.Nacionalidad" type="text"></b-form-input>
            </b-form-group>
          </b-col>
        </b-row>
        <b-row>
          <b-col>
            <b-form-group>
              <label>Sexo:</label>
              <b-form-select v-model="cliente.Sexo" :options="['M', 'F']" class="mb-3" />
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group>
              <label>Ocupación:</label>
              <b-form-input v-model="cliente.Ocupacion" type="text"></b-form-input>
            </b-form-group>
          </b-col>
        </b-row>
        <b-row>
          <b-col>
            <b-form-checkbox v-model="cliente.EsVIP" :value="1" :unchecked-value="0">
              Marcar si es un cliente VIP
            </b-form-checkbox>
          </b-col>
          <b-col>
            <b-form-group>
              <label>Fecha Nacimiento:</label>
              <b-form-input v-model="cliente.FechaNacimiento" type="date"></b-form-input>
            </b-form-group>
          </b-col>
        </b-row>
        <div slot="modal-footer">
          <b-btn variant="primary" type="submit">
            Guardar
          </b-btn>
          <b-btn variant="secondary" @click="show = false">
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
  mounted() {
    if (this.$store.state.listUbigeo.length === 0) {
      this.$store.dispatch("GetUbigeo");
    }
  },
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
        Nacionalidad: "",
        IdUbigeo: null,
        EsVIP: 0
      },
      ubigeoSeleccionado: {
        departamento: null,
        provincia: null,
        distrito: null
      },
      provinciasDisponibles: [],
      distritosDisponibles: []
    };
  },
  props: ["idModal", "newDniRuc", "editCliente", "txtButton"],
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
      this.ubigeoSeleccionado = { departamento: null, provincia: null, distrito: null };
      this.provinciasDisponibles = [];
      this.distritosDisponibles = [];

      if (this.editCliente && this.editCliente.IdCliente) {
        this.cliente = Object.assign({}, this.editCliente);
        
        // Mapear ubigeo si el cliente ya cuenta con uno guardado (ej: 150101)
        if (this.cliente.IdUbigeo && this.cliente.IdUbigeo.length === 6) {
          this.reconstruirUbigeo(this.cliente.IdUbigeo);
        }
      } else {
        this.cliente = {
          Cliente: "",
          DniRuc: this.newDniRuc || "",
          Direccion: "",
          Direccion2: "",
          Direccion3: "",
          Telefono: "",
          Email: "",
          Sexo: "",
          Ocupacion: "",
          FechaNacimiento: "",
          Nacionalidad: "",
          IdUbigeo: null,
          EsVIP: 0
        };
      }

      this.$nextTick(() => {
        if (this.cliente.DniRuc) {
          this.consultarDNIRUC();
        }
      });
    },

    // MÉTODOS DEL MANEJO DE UBIGEO CASCADA
    cambioDepartamento(dep) {
      this.ubigeoSeleccionado.provincia = null;
      this.ubigeoSeleccionado.distrito = null;
      this.distritosDisponibles = [];
      this.provinciasDisponibles = dep ? dep.children : [];
      this.cliente.IdUbigeo = null; 
    },
    cambioProvincia(prov) {
      this.ubigeoSeleccionado.distrito = null;
      this.distritosDisponibles = prov ? prov.children : [];
      this.cliente.IdUbigeo = null;
    },
    cambioDistrito(dist) {
      // Al elegir distrito se actualiza el objeto cliente con su código definitivo
      this.cliente.IdUbigeo = dist ? dist.id : null;
    },

    // Reconstruye la cascada de objetos de ubigeo usando Vuex al momento de editar
    reconstruirUbigeo(codUbigeo) {
      const depId = codUbigeo.substring(0, 2);
      const provId = codUbigeo.substring(0, 4);

      const dep = this.$store.state.listUbigeo.find(d => d.id === depId);
      if (dep) {
        this.ubigeoSeleccionado.departamento = dep;
        this.provinciasDisponibles = dep.children || [];

        const prov = this.provinciasDisponibles.find(p => p.id === provId);
        if (prov) {
          this.ubigeoSeleccionado.provincia = prov;
          this.distritosDisponibles = prov.children || [];

          const dist = this.distritosDisponibles.find(di => di.id === codUbigeo);
          if (dist) {
            this.ubigeoSeleccionado.distrito = dist;
          }
        }
      }
    },

    consultarDNIRUC() {
      var esNumerico = Number.isInteger(parseInt(this.cliente.DniRuc));
      if (!esNumerico) {
        this.$toasted.show("El DNI/RUC debe ser numérico.");
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
              
              // Si la consulta RUC te devuelve una clave de ubigeo asignable:
              if (data.ubigeo && data.ubigeo.length === 6) {
                this.cliente.IdUbigeo = data.ubigeo;
                this.reconstruirUbigeo(data.ubigeo);
              }
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
          this.$emit("onAddCliente", response.data);
          this.$toasted.show("Cliente guardado correctamente");
          this.$refs.clienteForm.hide();
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