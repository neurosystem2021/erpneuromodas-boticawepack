<template>
  <div>
    <!-- Modal Component -->
    <b-form @submit="guardarCliente">    
    <b-modal id="modal1" title="Editar Cliente" size="lg" ref="clienteForm" v-model="show">
      <b-row>
        <b-col md="12">
          <b-form-group>
            <label>DNI / RUC:</label>
            <b-input-group>
              <b-form-input 
                required="required"
                v-model="cliente.DniRuc"></b-form-input>
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
          <b-form-group>
            <label>Nombre Comercial:</label>
            <b-form-input 
                v-model="cliente.NombreComercial"
                type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Dirección:</label>
            <b-form-input 
              v-model="cliente.Direccion"
              type="text"></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>

      <b-row>
        <b-col md="4">
          <b-form-group>
            <label>Departamento:</label>
            <b-form-select 
              v-model="ubigeoSeleccionado.departamento" 
              @change="cambioDepartamento"
              required>
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
          <b-form-group>
            <label>Provincia:</label>
            <b-form-select 
              v-model="ubigeoSeleccionado.provincia" 
              :disabled="!ubigeoSeleccionado.departamento"
              @change="cambioProvincia"
              required>
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
          <b-form-group>
            <label>Distrito:</label>
            <b-form-select 
              v-model="ubigeoSeleccionado.distrito" 
              :disabled="!ubigeoSeleccionado.provincia"
              @change="cambioDistrito"
              required>
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
      </b-row>
      <b-row>
        <b-col>
          <b-form-checkbox id="checkbox1"
                     v-model="cliente.EsVIP"
                     :value="1"
                     :unchecked-value="0">
            Marcar si es un cliente VIP
          </b-form-checkbox>
        </b-col>
      </b-row>
      <b-row>
        <b-col>
          <b-form-checkbox id="checkbox2"
                     v-model="cliente.esComisionista"
                     :value="1"
                     :unchecked-value="0">
            Marcar si es un comisionista
          </b-form-checkbox>
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
import config from '@/store/config.js'
import Vue from 'vue'
export default {
  data() {
    return {
      API: config.API,
      show: false,
      cliente: {},
      ubigeoSeleccionado: {
        departamento: null,
        provincia: null,
        distrito: null
      },
      provinciasDisponibles: [],
      distritosDisponibles: []
    }
  },
  props: ['clienteSelect'],
  watch: {
    clienteSelect(cl) {
      this.cliente = Object.assign({}, cl);
      
      // Si el árbol de Ubigeos está vacío, lo cargamos desde Vuex
      if (this.$store.state.listUbigeo.length === 0) {
        this.$store.dispatch("GetUbigeo");
      }

      // LÓGICA PARA AUTO-SELECCIONAR EL UBIGEO SI EL CLIENTE YA TIENE UNO GUARDADO
      if (this.cliente && this.cliente.IdUbigeo) {
        this.mapearUbigeoCliente(this.cliente.IdUbigeo);
      } else {
        this.resetUbigeo();
      }
    }
  },
  methods: {

    mapearUbigeoCliente(idUbigeo) {
      const codDep = idUbigeo.substring(0, 2);
      const codProv = idUbigeo.substring(0, 4);

      const dep = this.$store.state.listUbigeo.find(d => d.id === codDep);
      if (dep) {
        this.ubigeoSeleccionado.departamento = dep;
        this.provinciasDisponibles = dep.children || [];
        
        const prov = this.provinciasDisponibles.find(p => p.id === codProv);
        if (prov) {
          this.ubigeoSeleccionado.provincia = prov;
          this.distritosDisponibles = prov.children || [];
          
          const dist = this.distritosDisponibles.find(d => d.id === idUbigeo);
          if (dist) {
            this.ubigeoSeleccionado.distrito = dist;
          }
        }
      }
    },
    resetUbigeo() {
      this.ubigeoSeleccionado.departamento = null;
      this.ubigeoSeleccionado.provincia = null;
      this.ubigeoSeleccionado.distrito = null;
      this.provinciasDisponibles = [];
      this.distritosDisponibles = [];
    },
    cambioDepartamento(dep) {
      this.ubigeoSeleccionado.provincia = null;
      this.ubigeoSeleccionado.distrito = null;
      this.distritosDisponibles = [];
      this.provinciasDisponibles = dep ? dep.children : [];
      Vue.set(this.cliente, 'IdUbigeo', null); // Limpia el valor en el payload
    },
    cambioProvincia(prov) {
      this.ubigeoSeleccionado.distrito = null;
      this.distritosDisponibles = prov ? prov.children : [];
      Vue.set(this.cliente, 'IdUbigeo', null);
    },
    cambioDistrito(dist) {
      if (dist) {
        // Asignamos el ID de 6 dígitos del distrito seleccionado al payload del cliente
        Vue.set(this.cliente, 'IdUbigeo', dist.id);
      } else {
        Vue.set(this.cliente, 'IdUbigeo', null);
      }
    },
    consultarDNIRUC() {
        let tipo = this.cliente.DniRuc.length > 8 ? 'RUC' : 'DNI'

        this.axios.get(this.API + 'consultarRUC', {
            params: {
                type: tipo,
                numero: this.cliente.DniRuc
            }
        }).then((response) => {
            if (response.data) {
                this.$toasted.show('Cliente encontrado, se llenarán sus datos')                  
                var data = response.data
                if (tipo == 'DNI') {
                  //  this.cliente.Cliente = data.nombres + ' ' + data.apellidoPaterno + ' ' + data.apellidoMaterno
                  Vue.set(this.cliente, 'Cliente', data.nombres + ' ' + data.apellidoPaterno + ' ' + data.apellidoMaterno)
                } else {
                  Vue.set(this.cliente, 'Cliente', data.razonSocial);
                  Vue.set(this.cliente, 'Direccion', data.direccion);

                  // this.cliente.Cliente = data.razonSocial
                  // this.cliente.Direccion = data.direccion
                  // this.cliente.Telefono = data.Telefono
                }

            } else {
                this.$toasted.show('No se encontró el Usuario')                
            }
            
        })
        .catch((error) => {
            this.$toasted.show('No se encontró el Usuario')        
            console.log(error)
        })
    },
    guardarCliente(evt) {
        evt.preventDefault();
        
        // Validación previa de seguridad
        if (!this.cliente.IdUbigeo) {
          this.$toasted.show('Debe seleccionar el Distrito (Ubigeo) del cliente');
          return;
        }

      this.axios.post(this.API + 'clientes', this.cliente)
      .then((response) => {
        this.$emit('onAddCliente', response.data)
        this.$toasted.show('Cliente guardado correctamente')
        this.$refs.clienteForm.hide()
      })
      .catch((error) => {
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    }
  }
}
</script>
<style>

</style>
