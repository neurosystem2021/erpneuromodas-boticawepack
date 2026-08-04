  <template>
    <div class="gr-page">
      <Menu v-if="false"></Menu>

      <b-form @submit="guardarRemision" @reset="onReset" @keyup="afe" validated>
        <b-container fluid class="gr-container">

          <!-- ===================== HEADER ===================== -->
          <div class="gr-header">
            <div class="gr-header-icon">
              <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M3 7l9-4 9 4-9 4-9-4z"/>
                <path d="M3 7v10l9 4 9-4V7"/>
                <path d="M12 11v10"/>
              </svg>
            </div>
            <div>
              <h4 class="gr-title">Guía de Remisión</h4>
              <span class="gr-subtitle">Remitente · SUNAT</span>
            </div>
          </div>

          <b-row>
            <!-- ===================== COLUMNA IZQUIERDA ===================== -->
            <b-col md="6">

              <!-- RESUMEN -->
              <section class="gr-card">
                <header class="gr-card-header">
                  <span class="gr-card-icon gr-icon-navy">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                  </span>
                  <h5>Resumen</h5>
                </header>

                <div class="gr-field">
                  <label for="fechaTraslado">Fecha de traslado <span class="gr-req">*</span></label>
                  <input type="date" class="form-control form-control-sm" id="fechaTraslado" v-model="remision.fechaTraslado" required>
                </div>

                <div class="gr-field">
                  <label for="observacion">
                    Observación
                    <span class="gr-req" v-if="observacionRequerida">*</span>
                    <span class="gr-hint" v-if="observacionRequerida">— obligatoria para motivo "OTROS"</span>
                  </label>
                  <input
                    type="text"
                    class="form-control form-control-sm"
                    :class="{ 'is-invalid': observacionRequerida && !remision.observacion }"
                    id="observacion"
                    placeholder="Describa el motivo de traslado"
                    v-model="remision.observacion"
                    :required="observacionRequerida">
                </div>

                <div class="gr-row-3">
                  <div class="gr-field">
                    <label for="codUndPesoBruto">Unidad</label>
                    <select class="form-control form-control-sm" id="codUndPesoBruto" v-model="remision.codUndPesoBruto" required>
                      <option value="KGM">KILOGRAMO</option>
                    </select>
                  </div>
                  <div class="gr-field">
                    <label for="pesoBruto">Peso total <span class="gr-req">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="pesoBruto" placeholder="0.00" v-model="remision.pesoBruto" required>
                  </div>
                  <div class="gr-field">
                    <label for="totalBultos">Total bultos</label>
                    <input type="text" class="form-control form-control-sm" id="totalBultos" placeholder="0" v-model="remision.totalBultos">
                  </div>
                </div>

                <div class="gr-divider"></div>
                <h6 class="gr-subhead">Motivo de traslado</h6>

                <div class="gr-field">
                  <label for="codModalidadTraslado">Modalidad de traslado <span class="gr-req">*</span></label>
                  <select class="form-control form-control-sm" id="codModalidadTraslado" v-model="remision.codModalidadTraslado" required>
                    <option value="01">Transporte público</option>
                    <option value="02">Transporte privado</option>
                  </select>
                  <span class="gr-hint" v-if="esTransportePublico">La empresa transportista completará los datos de vehículo y chofer.</span>
                  <span class="gr-hint" v-if="esTransportePrivado">Como transportista privado, debe indicar vehículo y chofer.</span>
                </div>

                <div class="gr-field">
                  <label for="codMotivoTraslado">Motivo de traslado <span class="gr-req">*</span></label>
                  <select @change="motivoTrasladoChange" class="form-control form-control-sm" id="codMotivoTraslado" v-model="remision.codMotivoTraslado" required>
                    <option value="01">VENTA</option>
                    <option value="14">VENTA SUJETA A CONFIRMACION DEL COMPRADOR</option>
                    <option value="02">COMPRA</option>
                    <option value="04">TRASLADO ENTRE ESTABLECIMIENTOS DE LA MISMA EMPRESA</option>
                    <option value="18">TRASLADO EMISOR ITINERANTE CP</option>
                    <option value="08">IMPORTACION</option>
                    <option value="09">EXPORTACION</option>
                    <option value="19">TRASLADO A ZONA PRIMARIA</option>
                    <option value="13">OTROS</option>
                  </select>
                </div>
              </section>

              <!-- REMITENTE -->
              <section class="gr-card">
                <header class="gr-card-header">
                  <span class="gr-card-icon gr-icon-navy">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-6h6v6"/></svg>
                  </span>
                  <h5>Remitente</h5>
                </header>

                <div class="gr-field">
                  <label for="codUbigeoOrigen">Ubigeo de partida <span class="gr-req">*</span></label>
                  <ubigeo-picker v-model="remision.codUbigeoOrigen" @select="onUbigeoOrigen"></ubigeo-picker>
                </div>

                <div class="gr-field">
                  <label for="direccionOrigen">Dirección de partida <span class="gr-req">*</span></label>
                  <input type="text" class="form-control form-control-sm" id="direccionOrigen" placeholder="Dirección de partida" v-model="remision.direccionOrigen" required>
                </div>
              </section>

              <!-- DOCUMENTO RELACIONADO -->
              <section class="gr-card">
                <header class="gr-card-header">
                  <span class="gr-card-icon gr-icon-orange">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3v5h5"/><path d="M6 3h8l6 6v12H6z"/><path d="M9 13h6M9 17h6"/></svg>
                  </span>
                  <h5>Documento relacionado</h5>
                </header>

                <div class="gr-row-2">
                  <div class="gr-field">
                    <label>Importar factura</label>
                    <autocomplete
                        ref="inputDocRef"
                        :url="API + 'ventas/busqueda'"
                        anchor="NroComprobante"
                        initValue=""
                        label="writer"
                        placeholder=""
                        :on-select="setDocRef"
                        :onFocus="focusAutocomplete.bind(null, 'inputDocRef')"
                        :classes="{input: 'form-control form-control-sm '}">
                    </autocomplete>
                  </div>
                  <div class="gr-field">
                    <label>Importar movimiento</label>
                    <autocomplete
                        ref="inputMovRef"
                        :url="API + 'movimientos/lista'"
                        anchor="NroComprobante"
                        initValue=""
                        label="writer"
                        placeholder=""
                        :on-select="setMovRef"
                        :onFocus="focusAutocomplete.bind(null, 'inputMovRef')"
                        :classes="{input: 'form-control form-control-sm '}">
                    </autocomplete>
                  </div>
                </div>
              </section>
            </b-col>

            <!-- ===================== COLUMNA DERECHA ===================== -->
            <b-col md="6">

              <!-- DESTINATARIO -->
              <section class="gr-card">
                <header class="gr-card-header">
                  <span class="gr-card-icon gr-icon-navy">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                  </span>
                  <h5>Destinatario</h5>
                </header>
                <h6 class="gr-subhead">Datos del destinatario</h6>

                <div class="gr-field">
                <label for="nroDocumentoCliente">RUC <span class="gr-req">*</span></label>
                <div class="input-group input-group-sm">
                  <input type="text" class="form-control" id="nroDocumentoCliente" placeholder="RUC"
                    v-model="remision.nroDocumentoCliente" required>
                  <div class="input-group-append">
                    <b-button size="sm" variant="outline-secondary" @click="consultarRUCCliente()">Buscar</b-button>
                  </div>
                </div>
              </div>

                <div class="gr-field">
                  <label for="razonSocialCliente">Razón social <span class="gr-req">*</span></label>
                  <input type="text" class="form-control form-control-sm" id="razonSocialCliente" placeholder="Razón social" v-model="remision.razonSocialCliente" required>
                </div>

                <div class="gr-field">
                  <label for="direccionCliente">Dirección</label>
                  <input type="text" class="form-control form-control-sm" id="direccionCliente" placeholder="Dirección" v-model="remision.direccionCliente">
                </div>

                <div class="gr-divider"></div>
                <h6 class="gr-subhead">Punto de llegada</h6>

                <div class="gr-field">
                  <label for="codUbigeoDestino">Ubigeo de llegada <span class="gr-req">*</span></label>
                  <ubigeo-picker v-model="remision.codUbigeoDestino" @select="onUbigeoDestino"></ubigeo-picker>
                </div>

                <div class="gr-field">
                  <label for="direccionDestino">Dirección de llegada <span class="gr-req">*</span></label>
                  <input type="text" class="form-control form-control-sm" id="direccionDestino" placeholder="Dirección de llegada" v-model="remision.direccionDestino" required>
                </div>
              </section>

              <!-- TRANSPORTISTA -->
              <section class="gr-card">
                <header class="gr-card-header">
                  <span class="gr-card-icon gr-icon-orange">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="7" width="15" height="10" rx="1"/><path d="M16 10h4l3 3v4h-7z"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg>
                  </span>
                  <h5>Transportista</h5>
                </header>

                <div class="gr-mode-banner" :class="esTransportePublico ? 'gr-mode-active' : 'gr-mode-disabled'">
                  Transporte público — datos de la empresa transportista
                  <span v-if="!esTransportePublico" class="gr-mode-tag">bloqueado (modalidad privada)</span>
                </div>

                <div class="gr-field">
                  <label for="nroDocumentoTransportista">Número de documento <span class="gr-req" v-if="esTransportePublico">*</span></label>
                  <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="nroDocumentoTransportista" placeholder="RUC transportista"
                      v-model="remision.nroDocumentoTransportista" :disabled="!esTransportePublico" :required="esTransportePublico">
                    <div class="input-group-append">
                      <b-button size="sm" variant="outline-secondary" :disabled="!esTransportePublico" @click="consultarDNIRUC()">Buscar</b-button>
                    </div>
                  </div>
                </div>

                <div class="gr-field">
                  <label for="razonSocialTransportista">Razón social <span class="gr-req" v-if="esTransportePublico">*</span></label>
                  <input type="text" class="form-control form-control-sm" id="razonSocialTransportista" placeholder="Razón social transportista"
                    v-model="remision.razonSocialTransportista" :disabled="!esTransportePublico" :required="esTransportePublico">
                </div>

                <div class="gr-divider"></div>

                <div class="gr-mode-banner" :class="esTransportePrivado ? 'gr-mode-active' : 'gr-mode-disabled'">
                  Transporte privado — unidad y conductor propios
                  <span v-if="!esTransportePrivado" class="gr-mode-tag">bloqueado (modalidad pública)</span>
                </div>

                <div class="gr-row-4">
                  <div class="gr-field">
                    <label for="placaVehiculo">Placa vehículo <span class="gr-req" v-if="esTransportePrivado">*</span></label>
                    <input type="text" class="form-control form-control-sm" style="text-transform:uppercase" id="placaVehiculo" placeholder="Ej. XYZ123"
                      v-model="remision.placaVehiculo" :disabled="!esTransportePrivado" :required="esTransportePrivado">
                  </div>
                  <!--<div class="gr-field">
                    <label for="placaCarreta">Placa carreta</label>
                    <input type="text" class="form-control form-control-sm" style="text-transform:uppercase" id="placaCarreta" placeholder="N° de placa"
                      v-model="remision.placaCarreta" :disabled="!esTransportePrivado">
                  </div>-->
                </div>

                <div class="gr-field">
                  <label for="nroDocChofer">Documento del chofer (DNI) <span class="gr-req" v-if="esTransportePrivado">*</span></label>
                  <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="nroDocChofer" placeholder="RUC / DNI"
                      v-model="remision.nroDocChofer" :disabled="!esTransportePrivado" :required="esTransportePrivado">
                    <div class="input-group-append">
                      <b-button size="sm" variant="outline-secondary" :disabled="!esTransportePrivado" @click="consultaDocConductor()">Buscar</b-button>
                    </div>
                  </div>
                </div>

                <div class="gr-row-2">
                  <div class="gr-field">
                    <label for="apellidosChofer">Apellidos del chofer <span class="gr-req" v-if="esTransportePrivado">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="apellidosChofer" placeholder="Apellidos del chofer"
                      v-model="remision.apellidosChofer" :disabled="!esTransportePrivado" :required="esTransportePrivado">
                  </div>
                  <div class="gr-field">
                    <label for="nombresChofer">Nombres del chofer <span class="gr-req" v-if="esTransportePrivado">*</span></label>
                    <input type="text" class="form-control form-control-sm" id="nombresChofer" placeholder="Nombres del chofer"
                      v-model="remision.nombresChofer" :disabled="!esTransportePrivado" :required="esTransportePrivado">
                  </div>
                </div>

                <div class="gr-field">
                  <label for="licenciaChofer">Número de licencia <span class="gr-req" v-if="esTransportePrivado">*</span></label>
                  <input type="text" class="form-control form-control-sm" id="licenciaChofer" placeholder="Ejemplo: Y74746010"
                    v-model="remision.licenciaChofer" :disabled="!esTransportePrivado" :required="esTransportePrivado">
                </div>
              </section>
            </b-col>
          </b-row>

          <b-row>
            <b-col>
              <section class="gr-card gr-card-flush">
                <productos-carrito ref="carrito" tipo="mov" :fields="carrito.fields" :noSubtotal="true" :idAlmacen="idAlmacen"></productos-carrito>
              </section>
            </b-col>
          </b-row>

          <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button class="gr-submit-btn" size="lg" type="submit" :disabled="inProgress">
                <span v-if="!inProgress">Guardar</span>
                <span v-else>Guardando…</span>
              </b-button>
            </b-col>
          </b-row>
        </b-container>
        <br><br>
      </b-form>
    </div>
  </template>

  <script>
  import Menu from '@/components/Menu'
  import ProductosCarrito from '@/components/ProductosCarrito'
  import { mapState, mapActions } from 'vuex'
  import config from '@/store/config.js'
  import UbigeoPicker from '@/components/UbigeoPicker'

  export default {
    data () {
      return {
        inProgress: false,
        API: config.API,
        idAlmacen: parseInt(sessionStorage.getItem("IdAlmacen")) || 1,
        remision: {
          codUndPesoBruto: 'KGM',
          codModalidadTraslado: '01',
          codMotivoTraslado: '01',
          fechaTraslado: this.$moment().format("YYYY-MM-DD"),
          placaCarreta: '',
          nombresChofer: '',
          apellidosChofer: '',
          licenciaChofer: '',
          nroDocumentoTransportista: '',
          razonSocialTransportista: '',
          placaVehiculo: '',
          nroDocChofer: ''
        },
        cliente: {},
        carrito: {
          fields: [
            { key: 'CodigoBarra', label: 'Código' }, 
            { key: 'Producto' }, 
            { key: 'cantidad' },
            { key: 'acciones', label: ''}
          ]
        }
      }
    },
    computed: {
      ...mapState({
        productos: state => state.cart.productos
      }),
      // SUNAT: modalidad 01 = transporte público -> los datos de la empresa transportista son obligatorios
      esTransportePublico () {
        return this.remision.codModalidadTraslado === '01'
      },
      // SUNAT: modalidad 02 = transporte privado -> vehículo y chofer son obligatorios
      esTransportePrivado () {
        return this.remision.codModalidadTraslado === '02'
      },
      // SUNAT: motivo 13 = OTROS -> la descripción/observación del motivo es obligatoria
      observacionRequerida () {
        return this.remision.codMotivoTraslado === '13'
      }
    },
    watch: {
      // Al cambiar de modalidad, limpiamos los campos que quedan bloqueados
      // para no arrastrar datos que ya no aplican y no deben enviarse a SUNAT
      'remision.codModalidadTraslado' (nuevo) {
        if (nuevo === '01') {
          // Transporte público: se limpian datos de vehículo/chofer propios
          this.remision.placaVehiculo = ''
          this.remision.placaCarreta = ''
          this.remision.nroDocChofer = ''
          this.remision.apellidosChofer = ''
          this.remision.nombresChofer = ''
          this.remision.licenciaChofer = ''
        } else if (nuevo === '02') {
          // Transporte privado: se limpian datos de la empresa transportista
          this.remision.nroDocumentoTransportista = ''
          this.remision.razonSocialTransportista = ''
        }
      }
    },
    methods: {
      ...mapActions({
        limpiarCart: 'limpiarCarrito',
        add:'agregarProducto'
      }),
      afe (e) {
        return false
      },
      motivoTrasladoChange (e) {
        let descripcionMotivoTraslado = e.target.options[e.target.selectedIndex].text;
        this.remision.descripcionMotivoTraslado = descripcionMotivoTraslado
      },
      onReset (evt) {
        evt.preventDefault();
      },
      // Busca la empresa transportista por RUC y autocompleta la razón social
      consultarDNIRUC () {
        if ((''+this.remision.nroDocumentoTransportista).trim() === '' || this.remision.nroDocumentoTransportista === undefined) {
          this.$toasted.show('Debe ingresar el número de documento del transportista')
          return
        }
        let esNumerico = Number.isInteger(parseInt(this.remision.nroDocumentoTransportista))
        if (!esNumerico) {
          this.$toasted.show('El RUC debe ser numérico, edite el número de documento del transportista')
          return
        }
        this.axios.get(this.API + 'consultarRUC', {
          params: {
            type: 'RUC',
            numero: this.remision.nroDocumentoTransportista
          }
        }).then((response) => {
          if (response.data && response.data.success !== false) {
            this.$toasted.info('Transportista encontrado, se llenarán sus datos')
            this.$set(this.remision, 'razonSocialTransportista', response.data.razonSocial)
          } else {
            this.$toasted.show('No se encontró el transportista')
          }
        })
        .catch((error) => {
          this.$toasted.show('No se encontró el transportista')
          console.log(error)
        })
      },
      // Busca al chofer por DNI y autocompleta nombres y apellidos
      consultaDocConductor () {
        if ((''+this.remision.nroDocChofer).trim() === '' || this.remision.nroDocChofer === undefined) {
          this.$toasted.show('Debe ingresar el número de documento del conductor')
          return
        }
        let esNumerico = Number.isInteger(parseInt(this.remision.nroDocChofer))
        if (!esNumerico) {
          this.$toasted.show('El DNI/RUC debe ser numérico, edite el número de documento del conductor')
          return
        }
        this.axios.get(this.API + 'consultarRUC', {
          params: {
            type: 'DNI',
            numero: this.remision.nroDocChofer
          }
        }).then((response) => {
          if (response.data) {
            this.$toasted.info('Conductor encontrado, se llenarán sus datos')
            this.$set(this.remision, 'nombresChofer', response.data.nombres)
            this.$set(this.remision, 'apellidosChofer', response.data.apellidoPaterno + ' ' + response.data.apellidoMaterno)
          } else {
            this.$toasted.show('No se encontró el conductor')
          }
        })
        .catch((error) => {
          this.$toasted.show('No se encontró el conductor')
          console.log(error)
        })
      },

      consultarRUCCliente () {
        if ((''+this.remision.nroDocumentoCliente).trim() === '' || this.remision.nroDocumentoCliente === undefined) {
          this.$toasted.show('Debe ingresar el número de RUC del destinatario')
          return
        }
        let esNumerico = Number.isInteger(parseInt(this.remision.nroDocumentoCliente))
        if (!esNumerico) {
          this.$toasted.show('El RUC debe ser numérico, edite el número de documento del destinatario')
          return
        }
        this.axios.get(this.API + 'consultarRUC', {
          params: {
            type: 'RUC',
            numero: this.remision.nroDocumentoCliente
          }
        }).then((response) => {
          if (response.data && response.data.success !== false) {
            this.$toasted.info('Destinatario encontrado, se llenarán sus datos')
            this.$set(this.remision, 'razonSocialCliente', response.data.razonSocial)
            if (response.data.direccion) {
              this.$set(this.remision, 'direccionCliente', response.data.direccion)
            }
          } else {
            this.$toasted.show('No se encontró el destinatario')
          }
        })
        .catch((error) => {
          this.$toasted.show('No se encontró el destinatario')
          console.log(error)
        })
      },
      guardarRemision (evt) {
        evt.preventDefault();
        this.inProgress = true
        if(!this.productos.length) {
            this.$toasted.show('No ha seleccionado ningun producto')
            this.inProgress = false            
            return
        }
        let faltaLlenar = false
        this.productos.forEach((prod, index) => {
          if (prod.cantidad <= 0) {
            faltaLlenar = true
          }
        })
        if (faltaLlenar) {
          this.$toasted.show('Falta llenar cantidad')
          this.inProgress = false
          return;
        }
        if (!this.remision.codUbigeoOrigen) {
          this.$toasted.show('Debe seleccionar el ubigeo de partida')
          this.inProgress = false
          return;
        }
        if (!this.remision.codUbigeoDestino) {
          this.$toasted.show('Debe seleccionar el ubigeo de llegada')
          this.inProgress = false
          return;
        }
        if (this.observacionRequerida && !this.remision.observacion) {
          this.$toasted.show('Debe indicar la observación cuando el motivo es OTROS')
          this.inProgress = false
          return;
        }
        if (this.esTransportePublico && (!this.remision.nroDocumentoTransportista || !this.remision.razonSocialTransportista)) {
          this.$toasted.show('Debe indicar los datos de la empresa transportista')
          this.inProgress = false
          return;
        }
        if (this.esTransportePrivado && (!this.remision.placaVehiculo || !this.remision.nroDocChofer || !this.remision.apellidosChofer || !this.remision.nombresChofer || !this.remision.licenciaChofer)) {
          this.$toasted.show('Debe indicar la placa del vehículo y los datos completos del chofer')
          this.inProgress = false
          return;
        }
        this.remision.placaVehiculo = (''+this.remision.placaVehiculo).toUpperCase();
        this.remision.placaCarreta = (''+this.remision.placaCarreta).toUpperCase();
        this.axios.post(this.API + 'remision', {
          remision: this.remision,
          productos: this.productos
        })
        .then((response) => {
          console.log(response);
          
          if (response.data.error) {
            this.$toasted.show(response.data.error)
          } else {
            this.limpiarCart()
            this.remision.fechaTraslado = this.$moment().format("YYYY-MM-DD");
            this.remision.observacion = '';
            this.remision.pesoBruto = '';
            this.remision.codModalidadTraslado = '01';
            this.remision.codMotivoTraslado = '01';
            this.remision.codUbigeoOrigen = '';
            this.remision.direccionOrigen = '';
            this.remision.nroDocumentoCliente = '';
            this.remision.razonSocialCliente = '';
            this.remision.direccionCliente = '';
            this.remision.codUbigeoDestino = '';
            this.remision.direccionDestino = '';
            this.remision.nroDocumentoTransportista = '';
            this.remision.razonSocialTransportista = '';
            this.remision.placaVehiculo = '';
            this.remision.placaCarreta = '';
            this.remision.nroDocChofer = '';
            this.remision.apellidosChofer = '';
            this.remision.nombresChofer = '';
            this.remision.licenciaChofer = '';
            this.remision.descripcionMotivoTraslado = '';
            this.remision.totalBultos = '';
            this.$refs.inputDocRef.setValue('');
            this.$refs.inputMovRef.setValue('');
            this.$toasted.success('Movimiento guardado correctamente ')          
          }
          this.inProgress = false
        })
        .catch((error) => {
          this.$toasted.error('Ha ocurrido un error, vuelva a intentarlo')
          this.inProgress = false
        })
      },
      onUbigeoOrigen (item) {
        // item = { id, nombre, provincia, departamento }
        this.$toasted.info('Ubigeo de partida: ' + item.nombre)
      },
      onUbigeoDestino (item) {
        this.$toasted.info('Ubigeo de llegada: ' + item.nombre)
      },
      focusAutocomplete (ref) {
        //this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      setMovRef (obj) {
        this.$set(this.remision, 'nroDocumentoCliente', '');
        this.$set(this.remision, 'razonSocialCliente', '');
        this.$set(this.remision, 'direccionCliente', '');
        this.$refs.inputDocRef.setValue('');
        this.remision.documentoReferencia = '';
        this.limpiarCart();
        
        // Cargar producto del movimiento
        this.axios.get(this.API + 'movimiento/detalle/comprobante', {
              params: {
                hashMovimiento: obj.Hash
              }
          }).then((response) => {
            
            if (response.data[0]) {
              this.axios.get(this.API + 'cliente', {
                params: {
                    idCliente: response.data[0].IdProveedor
                }})
                .then((response) => {
                  if (response.data) {
                    this.$set(this.remision, 'nroDocumentoCliente', response.data.DniRuc);
                    this.$set(this.remision, 'razonSocialCliente', response.data.Cliente);
                    this.$set(this.remision, 'direccionCliente', response.data.Direccion);
                    this.cliente = response.data;
                  } else {
                    this.$toasted.show('No se encontro al cliente');
                  }
                })
                .catch((error) => {
                    console.log(error)
                })
                // this.limpiarCart();
                
                response.data.map((item) => {
                      item.estadoPxP = false
                      let preciosProd = []
                      item.precioCont = item.PrecioContado
                      preciosProd = JSON.parse(item.PreciosPorProducto || '[]')
                      let fPP = preciosProd.filter(p => p.cantidad === 0)
                      
                      if (!item.PreciosPorProducto || fPP.length === 4) {
                          item.estadoPxP = true
                      }
                  this.add([item, () => {}, false ])
                })             
            }
        })
      },
      setDocRef (obj) {
        this.$set(this.remision, 'nroDocumentoCliente', '');
        this.$set(this.remision, 'razonSocialCliente', '');
        this.$set(this.remision, 'direccionCliente', '');
        this.$refs.inputMovRef.setValue('');
        this.limpiarCart();
        this.remision.documentoReferencia = obj.NroComprobante;
        //Cargar producto del documento afectado
        this.axios.get(this.API + 'venta/detalle/comprobante', {
              params: {
                  NroComprobante: obj.NroComprobante,
                  idDocVenta: obj.IdDocVenta
              }
          }).then((response) => {
            this.axios.get(this.API + 'cliente', {
              params: {
                  idCliente: response.data[0].IdCliente
              }})
              .then((response) => {
                if (response.data) {
                  this.$set(this.remision, 'nroDocumentoCliente', response.data.DniRuc);
                  this.$set(this.remision, 'razonSocialCliente', response.data.Cliente);
                  this.$set(this.remision, 'direccionCliente', response.data.Direccion);
                  
                  this.cliente = response.data;
                } else {
                  this.$toasted.show('No se encontro al cliente');
                }
              })
              .catch((error) => {
                  console.log(error)
              })
              // this.limpiarCart();
              
              response.data.map((item) => {
                    item.estadoPxP = false
                    let preciosProd = []
                    item.precioCont = item.PrecioContado
                    preciosProd = JSON.parse(item.PreciosPorProducto || '[]')
                    let fPP = preciosProd.filter(p => p.cantidad === 0)
                    
                    if (!item.PreciosPorProducto || fPP.length === 4) {
                        item.estadoPxP = true
                    }
                this.add([item, () => {
                }, false ])
              })             
        })
      },
    },
    components: {
      Menu,
      ProductosCarrito,
      UbigeoPicker
    }
  }
  </script>

  <style scoped>
  .gr-page {
    --gr-navy: #010067;
    --gr-navy-soft: #eef0fb;
    --gr-orange: #fd8100;
    --gr-orange-soft: #fff2e2;
    --gr-border: #e2e5ee;
    --gr-text: #1c2340;
    --gr-muted: #6b7290;
    color: var(--gr-text);
  }

  .gr-container { padding-top: 4px; }

  .gr-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
    padding-bottom: 14px;
    border-bottom: 2px solid var(--gr-navy);
  }
  .gr-header-icon {
    width: 44px; height: 44px;
    border-radius: 10px;
    background: var(--gr-navy);
    color: #fff;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .gr-title { margin: 0; font-weight: 700; color: var(--gr-navy); letter-spacing: .2px; }
  .gr-subtitle { font-size: 12px; color: var(--gr-orange); font-weight: 600; text-transform: uppercase; letter-spacing: .6px; }

  .gr-card {
    background: #fff;
    border: 1px solid var(--gr-border);
    border-radius: 10px;
    padding: 16px 18px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(1, 0, 103, 0.05);
  }
  .gr-card-flush { padding: 10px; }

  .gr-card-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--gr-border);
  }
  .gr-card-header h5 { margin: 0; font-size: 15px; font-weight: 700; color: var(--gr-navy); }

  .gr-card-icon {
    width: 28px; height: 28px;
    border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
  }
  .gr-icon-navy { background: var(--gr-navy-soft); color: var(--gr-navy); }
  .gr-icon-orange { background: var(--gr-orange-soft); color: var(--gr-orange); }

  .gr-subhead {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: var(--gr-muted);
    margin: 4px 0 10px;
  }

  .gr-field { margin-bottom: 12px; }
  .gr-field label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--gr-text);
    margin-bottom: 4px;
  }
  .gr-req { color: var(--gr-orange); font-weight: 700; }
  .gr-hint {
    display: block;
    font-size: 11px;
    color: var(--gr-muted);
    margin-top: 3px;
  }

  .gr-row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .gr-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
  @media (max-width: 576px) {
    .gr-row-2, .gr-row-3 { grid-template-columns: 1fr; }
  }

  .gr-divider { height: 1px; background: var(--gr-border); margin: 14px 0; }

  .form-control:focus {
    border-color: var(--gr-navy);
    box-shadow: 0 0 0 2px rgba(1, 0, 103, 0.12);
  }
  .form-control:disabled {
    background-color: #f4f5f9;
    color: #a8adc2;
  }
  .form-control.is-invalid { border-color: var(--gr-orange); }

  .gr-mode-banner {
    font-size: 12px;
    font-weight: 600;
    padding: 7px 10px;
    border-radius: 7px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
  }
  .gr-mode-active {
    background: var(--gr-navy-soft);
    color: var(--gr-navy);
  }
  .gr-mode-disabled {
    background: #f4f5f9;
    color: var(--gr-muted);
  }
  .gr-mode-tag {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .3px;
    color: var(--gr-muted);
  }

  .gr-submit-btn {
    background: var(--gr-orange);
    border-color: var(--gr-orange);
    font-weight: 700;
    letter-spacing: .3px;
    border-radius: 8px;
    min-width: 160px;
  }
  .gr-submit-btn:hover,
  .gr-submit-btn:focus {
    background: #e57400;
    border-color: #e57400;
  }
  </style>