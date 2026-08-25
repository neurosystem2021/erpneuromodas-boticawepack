<template>
  <div class="pos-jobi-inline">
    <!-- Modal Component -->
    <b-form @submit="guardarCliente">    
    <b-modal id="modal1" size="lg" ref="clienteForm" v-model="show" modal-class="pos-jobi-modal" hide-header-close>
      <template slot="modal-header">
        <div class="pos-modal-header">
          <div class="pos-modal-header-icon"><i class="fas fa-user-edit"></i></div>
          <div>
            <div class="pos-modal-header-title">Editar Cliente</div>
            <div class="pos-modal-header-sub">Actualiza los datos del cliente</div>
          </div>
          <button type="button" class="pos-modal-close" @click="show=false"><i class="fas fa-times"></i></button>
        </div>
      </template>

      <b-row>
        <b-col md="12">
          <b-form-group class="pos-field">
            <label><i class="fas fa-id-card pos-label-icon"></i> DNI / RUC</label>
            <b-input-group>
              <b-form-input 
                required="required"
                v-model="cliente.DniRuc"></b-form-input>
              <b-input-group-append>
                <b-btn class="pos-btn-buscar" @click="consultarDNIRUC"><i class="fas fa-search"></i> Buscar</b-btn>
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
          <b-form-group class="pos-field">
            <label><i class="fas fa-user pos-label-icon"></i> Nombre del Cliente</label>
            <b-form-input 
                required="required"
                v-model="cliente.Cliente"
                type="text"></b-form-input>
          </b-form-group>
          <b-form-group class="pos-field">
            <label><i class="fas fa-store pos-label-icon"></i> Nombre Comercial</label>
            <b-form-input 
                required="required"
                v-model="cliente.NombreComercial"
                type="text"></b-form-input>
          </b-form-group>
          <b-form-group class="pos-field">
            <label><i class="fas fa-map-marker-alt pos-label-icon"></i> Dirección</label>
            <b-form-input 
              v-model="cliente.Direccion"
              type="text"></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>
      <b-row>
          <b-col md="6">
            <b-form-group class="pos-field">
                <label><i class="fas fa-phone pos-label-icon"></i> Teléfono</label>
                <b-form-input 
                v-model="cliente.Telefono"
                type="text"></b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="6">
            <b-form-group class="pos-field">
                <label><i class="fas fa-envelope pos-label-icon"></i> Correo Electrónico</label>
                <b-form-input 
                v-model="cliente.Email"
                type="email"></b-form-input>
            </b-form-group>
          </b-col>
      </b-row>
      <b-row>
        <b-col md="6">
          <div class="pos-mini-row-check">
            <b-form-checkbox id="checkbox1"
                       v-model="cliente.EsVIP"
                       :value="1"
                       :unchecked-value="0">
              <i class="fas fa-star pos-label-icon"></i> Marcar si es un cliente VIP
            </b-form-checkbox>
          </div>
        </b-col>
        <b-col md="6">
          <div class="pos-mini-row-check">
            <b-form-checkbox id="checkbox2"
                       v-model="cliente.esComisionista"
                       :value="1"
                       :unchecked-value="0">
              <i class="fas fa-handshake pos-label-icon"></i> Marcar si es un comisionista
            </b-form-checkbox>
          </div>
        </b-col>
      </b-row>
      <div slot="modal-footer" class="pos-modal-actions">
        <b-btn class="pos-btn-guardar" type="submit">
           <i class="fas fa-save"></i> Guardar
         </b-btn>
         <b-btn class="pos-btn-cerrar-modal" @click="show=false">
           <i class="fas fa-times"></i> Cerrar
         </b-btn>
       </div>
    </b-modal>
    </b-form>
  </div>
</template>
<script>
import config from '@/store/config.js'
export default {
  data() {
    return {
      API: config.API,
      show: false,
      cliente: {}
    }
  },
  props: ['clienteSelect'],
  watch: {
    clienteSelect (cl) {
      this.cliente = Object.assign({}, cl)
    }
  },
  methods: {
    consultarDNIRUC () {
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
<style scoped>
/* =========================================================
   ClientesFormEdit.vue - mismo lenguaje visual JoBi que el
   resto de los módulos (modal navy, campos con icono).
   ========================================================= */
.pos-jobi-inline {
    --jobi-navy: #0B2A4A;
    --jobi-navy-2: #123A63;
    --jobi-teal: #0F9B8E;
    --jobi-blue: #2F6FED;
    --jobi-green: #17A554;
    --jobi-red: #E5484D;
    --jobi-orange: #F5A623;
}

/* -------- Cabecera del modal -------- */
.pos-modal-header {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    background: linear-gradient(135deg, var(--jobi-navy) 0%, var(--jobi-navy-2) 100%);
    color: #fff;
    padding: 14px 18px;
    margin: -1px -1px 0 -1px;
    border-radius: 6px 6px 0 0;
    position: relative;
}
.pos-modal-header-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255,255,255,.14);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex: 0 0 auto;
}
.pos-modal-header-title {
    font-weight: 900;
    font-size: 1.05rem;
}
.pos-modal-header-sub {
    font-size: .74rem;
    color: rgba(255,255,255,.65);
}
.pos-modal-close {
    margin-left: auto;
    background: rgba(255,255,255,.14);
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.pos-modal-close:hover { background: rgba(255,255,255,.24); }

/* -------- Campos -------- */
.pos-field label {
    color: var(--jobi-navy);
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.pos-label-icon {
    font-size: .82em;
    color: var(--jobi-teal);
}
.pos-field >>> input,
.pos-field >>> .form-control {
    border-radius: 10px !important;
    border: 1.5px solid #dbe6f1 !important;
    height: 42px;
    font-weight: 600;
    color: var(--jobi-navy);
}
.pos-field >>> input:focus,
.pos-field >>> .form-control:focus {
    border-color: var(--jobi-blue) !important;
    box-shadow: 0 0 0 3px rgba(47,111,237,.14) !important;
}

.pos-btn-buscar {
    border-radius: 0 10px 10px 0 !important;
    background: var(--jobi-blue) !important;
    border: none !important;
    color: #fff !important;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.pos-mini-row-check {
    background: #eef3f8;
    border: 1px solid #dbe6f1;
    border-radius: 10px;
    padding: 10px 12px;
    margin-bottom: 8px;
}

/* -------- Acciones del modal -------- */
.pos-modal-actions {
    display: flex;
    gap: 10px;
}
.pos-btn-guardar,
.pos-btn-cerrar-modal {
    border-radius: 999px !important;
    border: none !important;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .03em;
    font-size: .78rem;
    padding: 9px 20px !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.pos-btn-guardar {
    background: var(--jobi-green) !important;
    color: #fff !important;
    box-shadow: 0 6px 14px rgba(23,165,84,.30);
}
.pos-btn-cerrar-modal {
    background: #eef3f8 !important;
    color: var(--jobi-navy) !important;
}
.pos-btn-guardar:hover,
.pos-btn-cerrar-modal:hover { transform: translateY(-1px); }
</style>