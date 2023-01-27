<template>
  <div>
    <Menu v-if="false"></Menu>
    <br />
    <b-form @submit="guardarRemision" @reset="onReset" @keyup="afe" validated id="remision-form">
        <b-container fluid class="bv-example-row">

        <b-row>
          <b-col md="6">
            <fieldset class="scheduler-border">
              <legend class="scheduler-border">
                <h5>Resumen</h5>
              </legend>
              <h5>Guía de remisión</h5>
              <div class="container">
                <div class="row">
                  <label for="fechaTraslado" class="col-sm-4">Fecha de Traslado</label>
                  <input type="date" class="form-control form-control-sm col-sm-8" id="fechaTraslado" placeholder="Fecha de Traslado" v-model="remision.fechaTraslado" required>
                </div>
              </div>
              <div class="container">
                <div class="row">
                  <label for="observacion" class="col-sm-4">Observación</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" id="observacion" placeholder="Observación" v-model="remision.observacion">
                </div>
              </div>
              <div class="container">
                <div class="row">
                  <label for="codUndPesoBruto" class="col-sm-4">Peso Total</label>
                  <select class="form-control form-control-sm col-sm-2" id="codUndPesoBruto" v-model="remision.codUndPesoBruto" required>
                    <option value="KGM">KILOGRAMO</option>
                  </select>
                  <input type="text" class="form-control form-control-sm col-sm-3" id="pesoBruto" placeholder="Peso Total" v-model="remision.pesoBruto" required>
                  <input type="text" class="form-control form-control-sm col-sm-3" id="totalBultos" placeholder="Total Bultos" v-model="remision.totalBultos">
                </div>
              </div>

              <h5>Motivo de Traslado</h5>

              <div class="container">
                <div class="row">
                  <label for="codModalidadTraslado" class="col-sm-4">Modalidad Traslado</label>
                  <select class="form-control form-control-sm col-sm-8" id="codModalidadTraslado" v-model="remision.codModalidadTraslado" required v-on:change="cambioModalidad($event)">
                    <option value="01">Transporte público</option>
                    <option value="02">Transporte privado</option>
                  </select>
                </div>
                <div class="row">
                  <div class="col-12">
                    <b-form-text style="color:red !important; font-size:0.8rem;">*La opción público requiere información de un transportista tercero registrado en el MTC, hace referencia a una empresa de transportes encargada en trasladar los bienes.*</b-form-text>
                    <b-form-text style="color:red !important; font-size:0.8rem;">*La opción privado solo requiere información del conductor y del vehiculo, ya que es su empresa o el cliente el encargado en trasladar los bienes.*</b-form-text>
                  </div>
                </div>
              </div>
              <div class="container">
                <div class="row">
                  <label for="codMotivoTraslado" class="col-sm-4">Motivos Traslado</label>
                  <select @change="motivoTrasladoChange" class="form-control form-control-sm col-sm-8" id="codMotivoTraslado" v-model="remision.codMotivoTraslado" required>
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
              </div>
            </fieldset>


            <fieldset class="scheduler-border">
              <legend class="scheduler-border">
                <h5>Remitente</h5>
              </legend>
              <div class="container">
                <div class="row">
                  <label for="codUbigeoOrigen" class="col-sm-4">Ubigeo de Partida</label>
                  <div class="input-group input-group-sm col-sm-8">
                    <input id="codUbigeoOrigen" type="text" class="form-control" placeholder="Ubigeo de Partida" aria-label="Recipient's username" aria-describedby="basic-addon2" v-model="remision.codUbigeoOrigen" required>
                    <div class="input-group-append">
                      <a class="btn btn-outline-secondary" target="_blank" href="http://account.geodir.co/recursos/ubigeo-inei-peru.html">Buscar</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="container">
                <div class="row">
                  <label for="direccionOrigen" class="col-sm-4">Dirección de Partida</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" id="direccionOrigen" placeholder="Dirección de Partida" v-model="remision.direccionOrigen" required>
                </div>
              </div>
            </fieldset>


            <fieldset class="scheduler-border">
              <legend class="scheduler-border">
                <h5>Documento Relacionado</h5>
              </legend>
              <div class="container">
                <div class="row">
                  <div class="col-sm-6">
                    <label>Importar Factura:</label>
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
                  <div class="col-sm-6">
                    <label>Importar Movimiento:</label>
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
              </div>
            </fieldset>
          </b-col>


          <b-col md="6">
            <fieldset class="scheduler-border">
              <legend class="scheduler-border">
                <h5>Destinatario</h5>
              </legend>
              <h5>Datos del destinatario</h5>
              <div class="container">
                <div class="row">
                  <label for="nroDocumentoCliente" class="col-sm-4">RUC</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" id="nroDocumentoCliente" placeholder="Ruc" v-model="remision.nroDocumentoCliente" required>
                </div>
              </div>
              <div class="container">
                <div class="row">
                  <label for="razonSocialCliente" class="col-sm-4">Razón Social</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" id="razonSocialCliente" placeholder="Razón Social" v-model="remision.razonSocialCliente" required>
                </div>
              </div>
              <div class="container">
                <div class="row">
                  <label for="direccionCliente" class="col-sm-4">Dirección</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" id="direccionCliente" placeholder="Dirección" v-model="remision.direccionCliente">
                </div>
              </div>
              <!-- <div class="container">
                <div class="row">
                  <label for="observacion" class="col-sm-4">Dirección</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" id="observacion" placeholder="Dirección" v-mdoel="remision.">
                </div>
              </div> -->

              <h5>Punto de llegada</h5>

              <div class="container">
                <div class="row">
                  <label for="codUbigeoDestino" class="col-sm-4">Ubigeo de Llegada</label>
                  <div class="input-group input-group-sm col-sm-8">
                    <input id="codUbigeoDestino" type="text" class="form-control" placeholder="Ubigeo de Llegada" aria-label="Recipient's username" aria-describedby="basic-addon2" v-model="remision.codUbigeoDestino" required>
                    <div class="input-group-append">
                      <a class="btn btn-outline-secondary" target="_blank" href="http://account.geodir.co/recursos/ubigeo-inei-peru.html">Buscar</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="container">
                <div class="row">
                  <label for="direccionDestino" class="col-sm-4">Dirección de Llegada</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" id="direccionDestino" placeholder="Dirección de Llegada" v-model="remision.direccionDestino" required>
                </div>
              </div>
            </fieldset> 

            <fieldset class="scheduler-border">
              <legend class="scheduler-border">
                <h5>Transportista</h5>
              </legend>
              <h5>Datos de la empresa de transportes</h5>
              <div class="container">
                <div class="row">
                    <label for="nroDocumentoTransportista" class="col-sm-4">Número Documento</label>
                    <div class="input-group input-group-sm col-sm-8">
                      <input type="text" style="max-width: none;" class="form-control form-control-sm col-sm-8" id="nroDocumentoTransportista" ref="inputnroDocumentoTransportista" placeholder="Número Documento" v-model="remision.nroDocumentoTransportista">
                      <div class="input-group-append">
                          <b-button size="sm" variant="outline-secondary" @click="consultarDNIRUC()">Buscar</b-button>
                      </div>
                    </div>
                </div>
              </div>
              <div class="container">
                <div class="row">
                  <label for="razonSocialTransportista" class="col-sm-4">Razón Social</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" id="razonSocialTransportista" ref="inputrazonSocialTransportista" placeholder="Razón Social" v-model="remision.razonSocialTransportista">
                </div>
              </div>

              <h5>Unidad de transporte</h5>

              <!--<div class="container">
                <div class="row">
                  <label for="placaCarreta" class="col-sm-4">N° de Placa Carreta</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" id="placaCarreta" placeholder="N° de Placa" v-model="remision.placaCarreta">
                </div>
              </div>-->

              <div class="container mt-2">
                <div class="row">
                    <label for="nroDocChofer" class="col-sm-4">Doc. del Chofer (DNI)</label>
                    <div class="input-group input-group-sm col-sm-8">
                       <input type="text"  style="max-width: none;" class="form-control form-control-sm col-sm-8" id="nroDocChofer" ref="inputnroDocChofer" placeholder="Documento del Chofer" v-model="remision.nroDocChofer">
                      <div class="input-group-append">
                          <b-button size="sm" variant="outline-secondary" @click="consultaDocConductor()">Buscar</b-button>
                      </div>
                    </div>
                </div>
              </div>

              <div class="container mt-2">
                <div class="row">
                  <label for="apellidosChofer" class="col-sm-4">Apellidos del Chofer</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" id="apellidosChofer" ref="inputapellidosChofer" placeholder="Apellidos del Chofer" v-model="remision.apellidosChofer">
                </div>
              </div>

              <div class="container mt-2">
                <div class="row">
                  <label for="nombresChofer" class="col-sm-4">Nombres del Chofer</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" id="nombresChofer" ref="inputnombresChofer" placeholder="Nombres del Chofer" v-model="remision.nombresChofer">
                </div>
              </div>

              <div class="container mt-2">
                <div class="row">
                  <label for="licenciaChofer" class="col-sm-4">Licencia del Chofer</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" id="licenciaChofer" ref="inputlicenciaChofer" placeholder="Ejemplo: Y74746010" v-model="remision.licenciaChofer">
                </div>
              </div>

              <div class="container">
                <div class="row">
                  <label for="placaVehiculo" class="col-sm-4">N° de Placa Vehículo</label>
                  <input type="text" class="form-control form-control-sm col-sm-8" style="text-transform:uppercase" id="placaVehiculo" ref="inputplacaVehiculo" placeholder="Ejemplo: XYZ123" v-model="remision.placaVehiculo">
                </div>
              </div>


            </fieldset>
          </b-col>
        </b-row>

          <b-row>
            <b-col>
              <productos-carrito ref="carrito" tipo="mov" :fields="carrito.fields" :noSubtotal="true"></productos-carrito>
            </b-col>
          </b-row>
          <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" type="submit" :disabled="inProgress">Guardar</b-button>
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
export default {
  mounted () {
    this.limpiarCamposRefs();
  },
  data () {
    return {
      inProgress: false,
      API: config.API,
      remision: {
        codUndPesoBruto: 'KGM',
        codModalidadTraslado: '01',
        codMotivoTraslado: '01',
        descripcionMotivoTraslado:'VENTA',
        fechaTraslado: this.$moment().format("YYYY-MM-DD"),
        placaCarreta: '',
        nombresChofer: '',
        apellidosChofer: '',
        licenciaChofer: '',
        nroDocumentoTransportista: '',
        razonSocialTransportista: '',
        placaVehiculo: '',
        nroDocChofer: '',
        direccionCliente: ''
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
    })
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
    guardarRemision (evt) {
      evt.preventDefault();
      this.inProgress = true

      if((''+this.remision.codUbigeoOrigen).trim().length!=6 || (''+this.remision.codUbigeoDestino).trim().length!=6){
          this.$toasted.show('Verificar los codigos de ubigeo origen y destino; debe tener 6 digitos',{duration:3000})
          this.inProgress = false            
          return
      }

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
      this.remision.placaVehiculo = (''+this.remision.placaVehiculo).toUpperCase();
      this.axios.post(this.API + 'remision', {
        remision: this.remision,
        productos: this.productos
      })
      .then((response) => {
        console.log(response);
        
        if (response.data.error) {
          this.$toasted.show(response.data.error,{duration:3000})
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
          this.remision.descripcionMotivoTraslado = 'VENTA';
          this.remision.totalBultos = '';
          this.$refs.inputDocRef.setValue('');
          this.$refs.inputMovRef.setValue('');
          this.limpiarCamposRefs();
          this.$toasted.success('Guia de remisión guardada correctamente ')          
        }
        this.inProgress = false
      })
      .catch((error) => {
        this.$toasted.error('Ha ocurrido un error, vuelva a intentarlo')
        this.inProgress = false
      })
    },
    focusAutocomplete (ref) {
      //this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    limpiarCamposRefs(){
      this.$refs.inputplacaVehiculo.disabled = true
      this.$refs.inputnroDocChofer.disabled = true
      this.$refs.inputapellidosChofer.disabled = true
      this.$refs.inputnombresChofer.disabled = true
      this.$refs.inputlicenciaChofer.disabled = true
      this.$refs.inputplacaVehiculo.required = false
      this.$refs.inputnroDocChofer.required = false
      this.$refs.inputapellidosChofer.required = false
      this.$refs.inputnombresChofer.required = false
      this.$refs.inputlicenciaChofer.required = false
      this.$refs.inputnroDocumentoTransportista.disabled = false
      this.$refs.inputrazonSocialTransportista.disabled = false
      this.$refs.inputnroDocumentoTransportista.required = true
      this.$refs.inputrazonSocialTransportista.required = true
    },
    cambioModalidad(val){
      if(val.target.value=="01"){
        this.limpiarCamposRefs();
        this.remision.placaVehiculo = '';
        this.remision.nroDocChofer = '';
        this.remision.apellidosChofer = '';
        this.remision.nombresChofer = '';
        this.remision.licenciaChofer = '';
      }else{
        this.$refs.inputplacaVehiculo.disabled = false
        this.$refs.inputnroDocChofer.disabled = false
        this.$refs.inputapellidosChofer.disabled = false
        this.$refs.inputnombresChofer.disabled = false
        this.$refs.inputlicenciaChofer.disabled = false
        this.$refs.inputplacaVehiculo.required = true
        this.$refs.inputnroDocChofer.required = true
        this.$refs.inputapellidosChofer.required = true
        this.$refs.inputnombresChofer.required = true
        this.$refs.inputlicenciaChofer.required = true
        this.$refs.inputnroDocumentoTransportista.disabled = true
        this.$refs.inputrazonSocialTransportista.disabled = true
        this.$refs.inputnroDocumentoTransportista.required = false
        this.$refs.inputrazonSocialTransportista.required = false
        this.remision.nroDocumentoTransportista = '';
        this.remision.razonSocialTransportista = '';
      }
    },
    consultaDocConductor () {
      if ((''+this.remision.nroDocChofer).trim()=='' || this.remision.nroDocChofer == undefined) {
          this.$toasted.show('Debe ingresar Número Documento del Conductor')
          return
      }
      let esNumerico = Number.isInteger(parseInt(this.remision.nroDocChofer))
      if (!esNumerico) {
          this.$toasted.show('El DNI/RUC debe ser numérico, editar Número Documento Conductor')
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
              var data = response.data
              this.remision.nombresChofer = data.nombres
              this.remision.apellidosChofer = data.apellidoPaterno + ' ' + data.apellidoMaterno
              document.getElementById("nombresChofer").value = data.nombres
              document.getElementById("apellidosChofer").value = data.apellidoPaterno + ' ' + data.apellidoMaterno
          } else {
              this.$toasted.show('No se encontró el Conductor')                
          }
          
      })
      .catch((error) => {
          this.$toasted.show('No se encontró el Conductor')        
          console.log(error)
      })
    },
    consultarDNIRUC () {
      if ((''+this.remision.nroDocumentoTransportista).trim()=='' || this.remision.nroDocumentoTransportista == undefined) {
          this.$toasted.show('Debe ingresar Número Documento Transportista')
          return
      }
      let esNumerico = Number.isInteger(parseInt(this.remision.nroDocumentoTransportista))
      if (!esNumerico) {
          this.$toasted.show('El RUC debe ser numérico, editar Número Documento Transportista')
          return
      }
      
      this.axios.get(this.API + 'consultarRUC', {
          params: {
              type: 'RUC',
              numero: this.remision.nroDocumentoTransportista
          }
      }).then((response) => {
          if (response.data) {
              if(response.data.success==false){
                this.$toasted.show('No se encontro al Transportista');
                return
              }
              this.$toasted.info('Transportista encontrado, se llenarán sus datos')                  
              let data = response.data
              this.remision.razonSocialTransportista = data.razonSocial
              document.getElementById("razonSocialTransportista").value = data.razonSocial
          } else {
              this.$toasted.show('No se encontró el Transportista')                
          }
      })
      .catch((error) => {
          this.$toasted.show('No se encontró el Transportista')        
          console.log(error)
      })
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
            this.axios.get(this.API + 'proveedor', {
              params: {
                  idProveedor: response.data[0].IdProveedor
              }})
              .then((response) => {
                if (response.data) {
                  this.$set(this.remision, 'nroDocumentoCliente', response.data.Ruc);
                  this.$set(this.remision, 'razonSocialCliente', response.data.Proveedor);
                  this.$set(this.remision, 'direccionCliente', response.data.Direccion);
                  this.cliente = response.data;
                } else {
                  this.$toasted.show('No se encontro al proveedor');
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
      this.axios.get(this.API + 'venta/detalle/comprobante/guia', {
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
    ProductosCarrito
  }
}
</script>
<style>

  #remision-form fieldset .row {
    margin-bottom: 5pX;
  }
  #remision-form fieldset {
    border: 1px solid #cacaca;
    padding: 10px;
    margin-bottom: 14px;
  }
  #remision-form fieldset legend {
    padding-left: 10px;
    border: 1px solid #cacaca;
  }
  #remision-form fieldset legend h5 {
    margin: 4px;
  }
  #remision-form fieldset > h5 {
    border-bottom: 1px solid #cacaca;
    padding-bottom: 4px;
  }
</style> 