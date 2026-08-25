<template>
  <div class="jobi-cliente-wrap">
    <b-btn v-b-modal="idModal || 'modal1'" variant="primary" class="jobi-btn-nuevo">
      <i class="fas fa-user-plus"></i>
      <span>{{ txtButton || 'Nuevo' }}</span>
    </b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarCliente" ref="form">
    <b-modal :id="idModal || 'modal1'" size="lg" ref="clienteForm" v-model="show" @shown="shownClienteForm"
             hide-header-close content-class="jobi-cliente-modal" header-class="jobi-cliente-header" footer-class="jobi-cliente-footer">

      <template slot="modal-header">
        <div class="jobi-header-inner">
          <span class="jobi-header-icon"><i class="fas fa-user-circle"></i></span>
          <div>
            <div class="jobi-header-title">Nuevo Cliente</div>
            <div class="jobi-header-sub">Registro / edición de datos del cliente</div>
          </div>
        </div>
        <button type="button" class="jobi-header-close" @click="show=false" aria-label="Cerrar">
          <i class="fas fa-times"></i>
        </button>
      </template>

      <b-row>
        <b-col md="12">
          <b-form-group class="jobi-field">
            <label><i class="fas fa-id-card jobi-label-icon"></i> DNI / RUC:</label>
            <b-input-group>
              <b-form-input
                required="required"
                v-model="cliente.DniRuc" type="number"></b-form-input>
              <b-input-group-append>
                <b-btn class="jobi-btn-buscar" @click="consultarDNIRUC"><i class="fas fa-search"></i> Buscar</b-btn>
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
          <b-form-group class="jobi-field">
            <label><i class="fas fa-user jobi-label-icon"></i> Nombre del Cliente:</label>
            <b-form-input
                required="required"
                v-model="cliente.Cliente"
                type="text"></b-form-input>
          </b-form-group>
          <b-form-group class="jobi-field">
            <label><i class="fas fa-map-marker-alt jobi-label-icon"></i> Dirección 1:</label>
            <b-form-input
              v-model="cliente.Direccion"
              type="text"></b-form-input>
          </b-form-group>
          <b-form-group class="jobi-field">
            <label><i class="fas fa-map-marker-alt jobi-label-icon"></i> Dirección 2:</label>
            <b-form-input
              v-model="cliente.Direccion2"
              type="text"></b-form-input>
          </b-form-group>
          <b-form-group class="jobi-field">
            <label><i class="fas fa-map-marker-alt jobi-label-icon"></i> Dirección 3:</label>
            <b-form-input
              v-model="cliente.Direccion3"
              type="text"></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>
      <b-row>
          <b-col>
            <b-form-group class="jobi-field">
                <label><i class="fas fa-phone jobi-label-icon"></i> Teléfono:</label>
                <b-form-input
                v-model="cliente.Telefono"
                type="text"></b-form-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group class="jobi-field">
                <label><i class="fas fa-envelope jobi-label-icon"></i> Correo Electrónico:</label>
                <b-form-input
                v-model="cliente.Email"
                type="email"></b-form-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group class="jobi-field">
                <label><i class="fas fa-flag jobi-label-icon"></i> Nacionalidad:</label>
                <b-form-input
                v-model="cliente.Nacionalidad"
                type="text"></b-form-input>
            </b-form-group>
          </b-col>
      </b-row>
      <b-row>
          <b-col>
            <b-form-group class="jobi-field">
                <label><i class="fas fa-venus-mars jobi-label-icon"></i> Sexo:</label>
                <b-form-select v-model="cliente.Sexo" :options="['Masculino', 'Femenino']" class="mb-3" />
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group class="jobi-field">
                <label><i class="fas fa-briefcase jobi-label-icon"></i> Ocupación:</label>
                <b-form-input
                v-model="cliente.Ocupacion"
                type="text"></b-form-input>
            </b-form-group>
          </b-col>
      </b-row>
      <b-row>
        <b-col>
          <div class="jobi-vip-box" style="color:black" :class="{ 'is-active': cliente.EsVIP == 1 }">
            <b-form-checkbox
                       v-model="cliente.EsVIP"
                       :value="1"
                       :unchecked-value="0">
              <i class="fas fa-star jobi-label-icon"></i> Marcar si es un cliente VIP
            </b-form-checkbox>
          </div>
        </b-col>
        <b-col>
          <b-form-group class="jobi-field">
            <label><i class="fas fa-birthday-cake jobi-label-icon"></i> Fecha Nacimiento:</label>
            <b-form-input
              v-model="cliente.FechaNacimiento"
              type="date"></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>
      <div slot="modal-footer" class="jobi-footer-actions">
        <b-btn class="jobi-btn-cancelar" @click="show=false">
           <i class="fas fa-times"></i> Cerrar
         </b-btn>
        <b-btn class="jobi-btn-guardar" type="submit">
           <i class="fas fa-save"></i> Guardar
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
      cliente: {
        Cliente: '',
        DniRuc: '',
        Direccion: '',
        Direccion2: '',
        Direccion3: '',
        Telefono: '',
        Email: '',
        Sexo: '',
        Ocupacion:'',
        FechaNacimiento: '',
        Nacionalidad: '',
      }
    }
  },
  props: ['idModal', 'newDniRuc', 'editCliente', 'txtButton'],
  /*watch: {
    show (val) {
      console.log(this.editCliente)
      if (this.editCliente.IdCliente) {
        this.cliente = this.editCliente
      }
    }
  },*/
  methods: {
    shownClienteForm () {
      /*
      this.cliente.Direccion = ''
      this.cliente.Cliente = ''
      this.cliente.Telefono = ''
      this.cliente.Email = ''
*/
      if (this.editCliente && this.editCliente.IdCliente) {
        this.cliente = this.editCliente
      } else {
        this.cliente.DniRuc = this.$parent.$parent.newDniRuc
      }
    },
    consultarDNIRUC () {
      var esNumerico = Number.isInteger(parseInt(this.cliente.DniRuc))
      if (!esNumerico) {
          this.$toasted.show('El DNI/RUC debe ser numérico, editar cliente.')
          return
      }
      
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
                  this.cliente.Cliente = data.nombres + ' ' + data.apellidoPaterno + ' ' + data.apellidoMaterno
                  this.cliente.Direccion = ''
              } else {
                  this.cliente.Cliente = data.razonSocial
                  this.cliente.Direccion = data.direccion
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

      var esNumerico = Number.isInteger(parseInt(this.cliente.DniRuc))
      if (!esNumerico) {
          this.$toasted.show('El DNI/RUC debe ser numérico, editar cliente.')
          return
      }

      var esDocumento = this.cliente.DniRuc.length > 7 && this.cliente.DniRuc.length <= 11? true : false
      if (!esDocumento) {
          this.$toasted.show('El Número de Documento es de 8 ó 11 digitos')
          return
      }

      this.axios.post(this.API + 'clientes', this.cliente)
      .then((response) => {
        // console.log(response);
        this.$emit('onAddCliente', response.data)
        this.$toasted.show('Cliente guardado correctamente')
        this.$refs.clienteForm.hide()
        this.cliente = {}
      })
      .catch((error) => {
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    }
  }
}
</script>
<style scoped>
  input[type=number]::-webkit-inner-spin-button,
  input[type=number]::-webkit-outer-spin-button {
    -webkit-appearance: none;
    margin: 0;
  }

  /* =========================================================
     JoBi-style — Modal Cliente
     Mismo lenguaje visual que VentaPOS.vue: header navy con
     degradado, iconos FontAwesome en labels, inputs redondeados
     con sombra suave, botones tipo pill.
     ========================================================= */
  .jobi-cliente-wrap {
    --jobi-navy: #0B2A4A;
    --jobi-navy-2: #123A63;
    --jobi-teal: #0F9B8E;
    --jobi-blue: #2F6FED;
    --jobi-purple: #7C3AED;
    --jobi-green: #17A554;
    --jobi-orange: #F5A623;
    display: inline-block;
  }

  /* Botón "Nuevo" que abre el modal */
  .jobi-btn-nuevo {
    border-radius: 999px !important;
    background: var(--jobi-purple) !important;
    border: none !important;
    font-weight: 800;
    letter-spacing: .02em;
    padding: 8px 18px !important;
    box-shadow: 0 6px 14px rgba(124,58,237,.30);
    display: inline-flex;
    align-items: center;
    gap: 7px;
    transition: box-shadow .18s ease, transform .18s ease;
  }
  .jobi-btn-nuevo:hover {
    transform: translateY(-1px);
    box-shadow: 0 9px 18px rgba(124,58,237,.38);
  }

  /* Contenedor del modal */
  .jobi-cliente-modal >>> .modal-content {
    border-radius: 16px !important;
    overflow: hidden;
    border: none !important;
    box-shadow: 0 20px 45px rgba(11,42,74,.25);
  }

  /* Header navy con degradado, igual al pos-panel-header */
  .jobi-cliente-wrap >>> .jobi-cliente-header {
    background: linear-gradient(135deg, var(--jobi-navy) 0%, var(--jobi-navy-2) 100%) !important;
    border: none !important;
    padding: 16px 18px !important;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .jobi-cliente-wrap >>> .jobi-header-close {
    background: rgba(255,255,255,.12);
    border: none;
    color: #fff;
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color .18s ease;
  }
  .jobi-cliente-wrap >>> .jobi-header-close:hover {
    background: rgba(255,255,255,.24);
  }

  .jobi-cliente-wrap >>> .jobi-cliente-footer {
    background: #F7FAFD !important;
    border-top: 1px solid #e3ebf4 !important;
    padding: 14px 22px !important;
  }

  .jobi-cliente-wrap >>> .jobi-footer-actions {
    display: flex;
    gap: 10px;
    width: 100%;
    justify-content: flex-end;
  }
  .jobi-header-inner {
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .jobi-header-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255,255,255,.14);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex: 0 0 auto;
  }
  .jobi-header-title {
    color: #fff;
    font-weight: 800;
    font-size: 1.05rem;
    letter-spacing: .01em;
  }
  .jobi-header-sub {
    color: rgba(255,255,255,.65);
    font-size: .74rem;
    font-weight: 600;
  }
  .jobi-header-close:hover {
    background: rgba(255,255,255,.24);
  }

  /* Body */
  .jobi-cliente-modal >>> .modal-body {
    padding: 22px 22px 8px !important;
    background: #F7FAFD;
  }

  /* Campos */
  .jobi-field label {
    color: var(--jobi-navy);
    font-size: .70rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
  }
  .jobi-label-icon {
    font-size: .85em;
    color: var(--jobi-teal);
    width: 14px;
    text-align: center;
  }
  .jobi-field >>> input,
  .jobi-field >>> select,
  .jobi-field >>> .form-control {
    border-radius: 10px !important;
    border: 1.5px solid #dbe6f1 !important;
    background: #fff !important;
    color: var(--jobi-navy);
    font-weight: 600;
    height: 42px;
    box-shadow: 0 3px 8px rgba(11,42,74,.05);
    transition: border-color .18s ease, box-shadow .18s ease;
  }
  .jobi-field >>> input:focus,
  .jobi-field >>> select:focus,
  .jobi-field >>> .form-control:focus {
    border-color: var(--jobi-blue) !important;
    box-shadow: 0 0 0 3px rgba(47,111,237,.15) !important;
  }

  /* Botón Buscar (junto a DNI/RUC) */
  .jobi-btn-buscar {
    border-radius: 0 10px 10px 0 !important;
    background: var(--jobi-blue) !important;
    border: none !important;
    font-weight: 800;
    height: 42px;
    padding: 0 16px !important;
    box-shadow: 0 3px 8px rgba(47,111,237,.25);
  }
  .jobi-btn-buscar:hover {
    background: #2560d4 !important;
  }

  /* Caja del checkbox VIP */
.jobi-vip-box {
    background: #fff !important;
    border: 1.5px solid #dbe6f1;
    border-radius: 10px;
    padding: 10px 14px;
    margin-top: 26px;
    transition: border-color .18s ease, background-color .18s ease;
  }
  .jobi-vip-box.is-active {
    border-color: var(--jobi-orange);
    background: #FFF8EC !important;
  }

  /* FORZADO: el texto del checkbox no se veía porque algo con más
     especificidad le pintaba el color blanco/transparente. Se fuerza
     el color en TODOS los selectores posibles que bootstrap-vue puede
     generar para el label del custom-checkbox. */
  .jobi-vip-box >>> .custom-control,
  .jobi-vip-box >>> .custom-checkbox {
    color: var(--jobi-navy) !important;
  }
  .jobi-vip-box >>> .custom-control-label,
  .jobi-vip-box >>> label.custom-control-label,
  .jobi-vip-box >>> .custom-control-label * {
    color: var(--jobi-navy) !important;
    opacity: 1 !important;
    visibility: visible !important;
    font-weight: 700 !important;
    font-size: .92rem !important;
    display: inline-block !important;
  }
  .jobi-vip-box .jobi-label-icon {
    color: var(--jobi-orange) !important;
    opacity: 1 !important;
    margin-right: 4px;
  }
  .jobi-vip-box >>> .jobi-vip-text {
    color: var(--jobi-navy) !important;
    font-weight: 700 !important;
  }

  /* Footer */
  .jobi-btn-guardar,
  .jobi-btn-cancelar {
    border-radius: 999px !important;
    border: none !important;
    font-weight: 800;
    padding: 10px 22px !important;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    text-transform: uppercase;
    font-size: .78rem;
    letter-spacing: .03em;
    transition: box-shadow .18s ease, transform .18s ease;
  }
  .jobi-btn-guardar {
    background: var(--jobi-green) !important;
    color: #fff !important;
    box-shadow: 0 8px 18px rgba(23,165,84,.30);
  }
  .jobi-btn-guardar:hover {
    transform: translateY(-1px);
    box-shadow: 0 10px 22px rgba(23,165,84,.38);
  }
  .jobi-btn-cancelar:hover {
    background: #dfe8f2 !important;
  }
</style>