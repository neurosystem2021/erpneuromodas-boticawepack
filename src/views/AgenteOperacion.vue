<template>
    <div>
      <b-container fluid class="bv-example-row" id="conten">
        <b-row>
            <b-col md="7" style="border-right: 2px solid #E6E6E6; padding: 20px;">
                <b-row>
                    <b-col md="12">
                        <b-row v-if="!inCharge" style="display: contents !important;">
                            <div v-if="entidades.length > 0" style="overflow-x: scroll; display: inline-flex;width: -webkit-fill-available;">
                                <b-col class="text-center" v-for="(entidad, index) in entidades" :key="index" @click="setEntidad(entidad)" :class="{'seleccionEntidad': idEntidadSelect == entidad.IdEntidad }" style="cursor:pointer">
                                    <b-img style="border-radius: 10px;" fluid :src="'/resources/images/agente/'+entidad.Imagen" @error="setAltImg" alt="" width="80%" ></b-img><br>
                                    <label style="font-size: 0.85rem "><b>{{ entidad.Nombre }}</b></label>
                                </b-col>
                            </div>
                            <div v-else>
                                <h5 class="text-info">NO TIENE ENTIDADES AGREGADAS</h5>
                            </div>
                        </b-row>
                        <b-row v-else>
                            <vue-simple-spinner size="huge"></vue-simple-spinner>
                        </b-row>
                    </b-col>
                </b-row>
                <hr>
                <b-row v-if="idEntidadSelect!=null">
                    <b-col md="2" style="border-right: 2px solid #E6E6E6;">
                        <b-button block variant="success" size="sm" @click="setOperacionesEntidad(1)" :class="{'seleccionEntidadOpcion': opcionEntidad == 1 }">OPERACIONES</b-button>
                        <b-button block variant="primary" size="sm" @click="setServiciosEntidad(2)" :class="{'seleccionEntidadOpcion': opcionEntidad == 2 }">SERVICIOS</b-button>
                    </b-col>
                    
                    <b-col md="10" class="centrarOperacion" style="border-bottom: 2px solid #E6E6E6;">
                        <b-row v-if="opcionEntidad==2" style="display: contents !important;">
                            <b-col md="12">
                                <b-row v-if="!inChargeServicios">
                                    <b-col md="7">
                                        <b-form-group>
                                            <b-input-group>
                                                <b-form-input ref="inputSearchEmpresa" v-model="busquedaEmpresa" placeholder="Buscar empresa" @input="(val)=>{debounceInput(val); inChargeEmpresas = true; empresas = [];}" autocomplete="off" />
                                                <b-input-group-append>
                                                <b-btn ref="btnLimpiarEmpresa" :disabled="!busquedaEmpresa" @click="()=>{  busquedaEmpresa = ''; getEmpresas(); } ">Limpiar</b-btn>
                                                </b-input-group-append>
                                            </b-input-group>
                                        </b-form-group>
                                    </b-col>
                                    <b-col md="5">
                                        <b-input-group prepend="Categorias" class="inputLine">
                                            <b-form-select v-model="idCategoriaEmpresaSelect" :options="itemsCategoriaEmpresa" v-on:change="setCategoriaEmpresa($event)" size="md"/>
                                        </b-input-group>
                                    </b-col>
                                </b-row>
                                <b-row v-else class="centrarOperacion">
                                    <vue-simple-spinner size="large"></vue-simple-spinner>
                                </b-row>
                            </b-col>
                        </b-row>
                    </b-col>            
                </b-row>
                <hr>
                <b-row style="height: 46vh;">
                    <b-col md="12">
                        <b-row v-if="opcionEntidad == 2" style="height: 46vh;">
                            <b-col md="12">
                                <b-row v-if="!inChargeEmpresas" style="overflow-y: scroll; height: 46vh;">
                                    <b-col md="12">
                                        <b-row v-if="empresas.length>0" >
                                            <b-col cols="3" class="text-center" v-for="(empresa, index) in empresas" :key="index" @click="setEmpresaEntidad(empresa)" style="cursor:pointer; padding: 2px;" :class="{'seleccionEmpresaEntidad': idEmpresaEntidadSelect == empresa.IdEmpresaEntidad }">
                                                <div style="cursor:pointer;">
                                                    <b-img style="border-radius: 10px;" fluid :src="'/resources/images/agente/'+empresa.Imagen" @error="setAltImg" alt="" width="70%" ></b-img>
                                                    <div>
                                                        <label style="font-size: 1vw "><b>{{ empresa.Empresa }}</b></label>
                                                    </div>
                                                    <div style="margin-top: -10px !important;">
                                                        <label style="font-size: 0.8vw;" class="text-primary"><b>{{ empresa.CategoriaEmpresa }}</b></label>
                                                    </div>
                                                </div>
                                            </b-col>
                                        </b-row>
                                        <b-row v-else class="centrarOperacion">
                                            <h5 class="text-info">NO SE ENCONTRARON RESULTADOS</h5> 
                                        </b-row>
                                    </b-col>
                                </b-row>
                                <b-row v-else class="centrarOperacion" style="height: 46vh;">
                                    <vue-simple-spinner size="huge"></vue-simple-spinner>
                                </b-row>
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>

            </b-col>
            
            <b-col md="5">
                <b-form @submit="guardarOperacion" @keydown.enter="prevent">
                    <b-row>
                        <b-col md="12">
                            <b-row>
                                <b-col md="6">
                                    <b-input-group prepend="Usuario " class="mt-2 inputLine">
                                        <input
                                            v-model="vendedor.NombreUsuario"
                                            type="text"
                                            style="width: 80px"
                                            :disabled="true"
                                            class="form-control" />
                                    </b-input-group>
                                </b-col>
                                <b-col md="5" style="display: flex; justify-content: center;align-items: center;">
                                    <b-row>
                                        <b-col md="12" v-if="idCajaAbierta > 0">
                                           <label class="text-success" style="font-weight: bold; margin: 0 !important;">CAJA APERTURADA <br>{{ cajaFechaApertura }}</label>  
                                        </b-col>
                                        <b-col md="12" v-else>
                                            <h6 class="text-danger" v-show="cargaCorrecta">NO EXISTE CAJA ABIERTA</h6>
                                        </b-col>
                                    </b-row>
                                </b-col>
                                <b-col md="1" style="display: flex; justify-content: center;align-items: center;">
                                    <b-button size="sm" @click="actualizarVendedor" variant="outline-primary"><i class="fas fa-redo fa-lg"></i></b-button>
                                </b-col>
                            </b-row>
                            <hr>
                            <b-row>
                                <b-col md="12">
                                    <b-form-group style="margin: 0 !important;">
                                        <b-input-group prepend="Cliente" class="mt-2 inputLine">
                                            <clientes-form txtButton="Editar" v-if="operacion.cliente.IdCliente && operacion.cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="operacion.cliente"></clientes-form>
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
                            </b-row>
                            <b-row>
                                <b-col md="7">
                                    <b-input-group prepend="Telefono " class="mt-2 inputLine">
                                        <input
                                            v-model="operacion.telefono"
                                            type="text"
                                            style="width: 80px"
                                            required
                                            placeholder="Número de contacto del cliente"
                                            class="form-control" />
                                    </b-input-group>
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                    <hr>
                    <b-row v-if="idEmpresaEntidadSelect==null && opcionEntidad!=1">
                        <b-col md="12" class="text-center">
                            <br>
                            <br>
                            <br>
                            <h4 class="text-danger">SELECCIONE UN SERVICIO / OPERACION</h4>
                        </b-col>  
                    </b-row>
                    <b-row v-if="opcionEntidad==1 || idEmpresaEntidadSelect!=null">
                        <b-col md="12">
                                <label style="font-size: 1.05rem"><b>NUEVA OPERACION: <span class="text-info">{{ nombreEntidadSelect }}</span></b></label>
                                <b-row>
                                    <b-col>
                                        <b-input-group prepend="Operación" class="mt-2 inputLineOperacion">
                                                <autocomplete
                                                    required="required"
                                                    ref="inputTipoOperacion"
                                                    :url="API + (opcionEntidad==2?'agente/entidad/empresa/servicio':'agente/entidad/operacion')"
                                                    :customParams="{IdEmpresaEntidad: idEmpresaEntidadSelect, SoloData: 1, IdEntidad: idEntidadSelect}"
                                                    :anchor="(opcionEntidad==2?'Servicio':'Operacion')"
                                                    initValue=""
                                                    class="font-weight-bold"
                                                    label="writer"
                                                    placeholder=""
                                                    :on-select="setTipoOperacion" 
                                                    :onFocus="focusAutocompleteOperacion.bind(null, 'inputTipoOperacion')"
                                                    :classes="{input: 'form-control'}">
                                                </autocomplete>
                                        </b-input-group>
                                    </b-col>
                                </b-row>
                                <b-row>
                                    <b-col>
                                        <b-input-group prepend="Monto " class="mt-2 inputLineNumber">
                                            <input
                                                v-model="operacion.monto"
                                                type="number"
                                                style="width: 80px"
                                                step="0.01"
                                                min="0"
                                                required
                                                @input="(val) => changeMonto(val.target.value,1)"
                                                placeholder="Monto de la operación (S/)"
                                                class="form-control" />
                                        </b-input-group>
                                    </b-col>
                                </b-row>
                                <b-row v-if="operacion.tieneComision == 1">
                                    <b-col md="6">
                                        <b-input-group prepend="Comision " class="mt-2 inputLineNumber">
                                            <input
                                                v-model="operacion.comision"
                                                :disabled = "operacion.calculo == 1"
                                                type="number"
                                                style="width: 75px !important"
                                                step="0.01"
                                                min="0"
                                                @input="(val) => changeMonto(val.target.value,2)"
                                                placeholder="Comision de la operación (S/)"
                                                class="form-control" />
                                        </b-input-group>
                                    </b-col>
                                    <b-col v-if="operacion.tieneComision == 1 && operacion.calculo == 1" md="6" class="mt-3" style="display: flex; align-items: center; justify-content: start;">
                                        <h6 v-if="operacion.comisionFija == 0" style="margin-left: 20px;">Por cada S/ {{ operacion.montoMax | moneda }}, será S/ {{ operacion.montoComision | moneda }} <i v-if="operacion.ganancia==1" class="fas fa-arrow-up fa-lg text-success ml-1"></i></h6>
                                        <h6 v-else style="margin-left: 20px;">Tiene una comisión fija de S/ {{ operacion.montoComision | moneda }} </h6>
                                    </b-col>
                                </b-row>
                                <hr>
                                <b-row v-if="operacion.requiereCodigo">
                                    <b-col>
                                        <b-form-group>
                                            <b-input-group :prepend="getCapitalize(operacion.nombreCodigo)" class="mt-2 inputLineRequerido">
                                                <input
                                                    v-model="operacion.codigo"
                                                    type="text"
                                                    style="width: 80px"
                                                    required
                                                    :placeholder="'Ingrese ' + operacion.nombreCodigo"
                                                    class="form-control" />
                                            </b-input-group>
                                        </b-form-group>
                                    </b-col>
                                </b-row>
                                <b-row>
                                    <b-col>
                                        <b-input-group prepend="Observación " class="mt-2 inputLineText">
                                            <textarea
                                                v-model="operacion.observacion"
                                                placeholder="Observación de la operación"
                                                rows="2"
                                                style="text-transform: uppercase;"
                                                class="form-control">
                                            </textarea>
                                        </b-input-group>
                                    </b-col>
                                </b-row>
                                <hr v-if="operacion.total > 0">
                                <b-row v-if="operacion.total > 0">
                                    <b-col class="text-center">
                                        <h4>TOTAL A RECIBIR: S/ {{ operacion.total | moneda }}</h4>
                                    </b-col>
                                </b-row>

                                <b-row align-h="center" class="text-center">
                                <b-col cols="12">
                                    <br />
                                    <b-button type="submit" :disabled="inProgress" variant="success" size="lg">Guardar Operación</b-button>
                                    <br />
                                    <br />
                                </b-col>
                            </b-row>
                        </b-col>
                    </b-row>
                </b-form>
            </b-col>
        </b-row>
      </b-container>
    </div>
</template>
<script>
import { mapActions, mapState } from 'vuex'
import config from '@/store/config.js'
import ClientesForm from '@/components/ClientesForm'
import Spinner from 'vue-simple-spinner'
import debounce  from 'lodash'

export default {
    beforeMount () {
        this.initForm()
    },
  	mounted () {
        this.loadClienteVarios()
        this.getVendedor()
        this.getEntidades()
	},
	data () {
		return {
			API: config.API,
			fechaOperacion: this.$moment().format("YYYY-MM-DD"),
            operacion: {},
            newDniRuc: '',
            inProgress: false,
            inCharge: false,
            vendedor: {},
            //nuevas variables
            opcionEntidad: null,
            busquedaEmpresa: '',
            idCategoriaEmpresaSelect: null,
            inChargeServicios: false,
            itemsCategoriaEmpresa: [{ value: null, text: 'TODAS' }],
            empresas:[],
            idEmpresaEntidadSelect: null,
            inChargeEmpresas: false,
            nombreEntidadSelect: '',
            entidades: [],
            idEntidadSelect: null,
            idCajaAbierta: null,
            cajaFechaApertura: '',
            cajaIdTurno: null,
            cargaCorrecta: false,
		}
	},
	computed: {
		...mapState({
            productos: state => state.cart.productos
        }),
	},
	methods: {
        /* FIN METODOS DE FINALES */
        actualizarVendedor(){
            this.$toasted.info('Se cargo el vendedor nuevamente')
            this.getVendedor()
        },
        debounceInput: _.debounce(function (e) {
            this.busquedaEmpresa = e
            this.getEmpresas()
        }, 100),
        setOperacionesEntidad(val){
            this.opcionEntidad = val
            
            let index = this.entidades.findIndex((e)=>e.IdEntidad == this.idEntidadSelect)
            if(index !=-1){
                this.nombreEntidadSelect = this.entidades[index].Nombre +" - "+this.entidades[index].Nombre +" - OPERACION"
            }else{
                this.nombreEntidadSelect = ''
            }

            this.busquedaEmpresa = ''
            this.idCategoriaEmpresaSelect = null
            setTimeout(() => {
                this.operacion.idServicio = null
                this.operacion.operacion = ''
                this.operacion.tieneComision = 0
                this.operacion.comisionFija = 0
                this.operacion.requiereCodigo = 0
                this.operacion.nombreCodigo = ''
                this.operacion.codigo = ''
                this.operacion.ganancia = 0
                this.operacion.montoMax = 0
                this.operacion.montoComision = 0
                this.operacion.comision = null
                this.operacion.monto = null
                this.operacion.observacion = ''
                if(this.$refs.inputTipoOperacion){
                    this.$refs.inputTipoOperacion.setValue('')
                }
            }, 100);
        },
        setEmpresaEntidad(val){
            this.idEmpresaEntidadSelect = val.IdEmpresaEntidad
            this.nombreEntidadSelect = val.Nombre+" - "+val.Empresa+" - "+val.CategoriaEmpresa
            setTimeout(() => {
                if(this.$refs.inputTipoOperacion){
                    this.$refs.inputTipoOperacion.setValue('')
                    this.operacion.idServicio = null
                    this.operacion.operacion = ''
                    this.operacion.tieneComision = 0
                    this.operacion.comisionFija = 0
                    this.operacion.requiereCodigo = 0
                    this.operacion.nombreCodigo = ''
                    this.operacion.codigo = ''
                    this.operacion.ganancia = 0
                    this.operacion.montoMax = 0
                    this.operacion.montoComision = 0
                    this.operacion.comision = null
                    this.getComision()
                }
            }, 150);
        },
        async getEmpresas(){
            this.empresas = []
            this.idEmpresaEntidadSelect = null
            this.nombreEntidadSelect = ''
            this.inChargeEmpresas = true
            try {
            const response = await this.axios.get(`${this.API}agente/entidad/empresa`, { params:
                {
                    q: this.busquedaEmpresa,
                    IdCategoriaEmpresa: this.idCategoriaEmpresaSelect,
                    IdEntidad: this.idEntidadSelect,
                    noLimit:1
                }
            });
            this.empresas = response.data.data

            if(this.$refs.btnLimpiarEmpresa){
                this.$refs.btnLimpiarEmpresa.focus()
            }
            if(this.$refs.inputSearchEmpresa){
                this.$refs.inputSearchEmpresa.focus()
            }

            this.inChargeEmpresas = false
            } catch (error) {
                console.log(error);
                this.inChargeEmpresas = false
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            }
        },
        setCategoriaEmpresa(val){
            this.idCategoriaEmpresaSelect = val
            this.getEmpresas()
        },
        async getCategoriaEmpresa(){
            this.itemsCategoriaEmpresa = []
            this.inChargeServicios = true 
            try {
            const response = await this.axios.get(`${this.API}agente/entidad/empresa/categoria`, { params:
                {
                    q: '',
                    IdEntidad: this.idEntidadSelect
                }
            });
            this.itemsCategoriaEmpresa = [{ value: null, text: 'TODAS' }]
            response.data.data.map(c=>{
                this.itemsCategoriaEmpresa.push({value:c.IdCategoriaEmpresa,text:c.CategoriaEmpresa})
            })

            this.inChargeServicios = false
            } catch (error) {
                console.log(error);
                this.inChargeServicios = false
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            }
        },
        setServiciosEntidad(val){
            this.opcionEntidad = val
            this.busquedaEmpresa = ''
            this.nombreEntidadSelect = ''
            this.idCategoriaEmpresaSelect = null
            this.getCategoriaEmpresa()
            setTimeout(() => {
                this.getEmpresas()
                this.operacion.idServicio = null
                this.operacion.operacion = ''
                this.operacion.tieneComision = 0
                this.operacion.comisionFija = 0
                this.operacion.requiereCodigo = 0
                this.operacion.nombreCodigo = ''
                this.operacion.codigo = ''
                this.operacion.ganancia = 0
                this.operacion.montoMax = 0
                this.operacion.montoComision = 0
                this.operacion.comision = null
                this.operacion.monto = null
                this.operacion.observacion = ''
            }, 100);
        },
        setEntidad(entidad){
            this.idEntidadSelect = entidad.IdEntidad
            this.operacion.idEntidad = entidad.IdEntidad
            this.opcionEntidad = null
            this.empresas = []
            this.idEmpresaEntidadSelect = null
            this.nombreEntidadSelect = ''
            setTimeout(() => {
                this.operacion.idServicio = null
                this.operacion.operacion = ''
                this.operacion.tieneComision = 0
                this.operacion.comisionFija = 0
                this.operacion.requiereCodigo = 0
                this.operacion.nombreCodigo = ''
                this.operacion.codigo = ''
                this.operacion.ganancia = 0
                this.operacion.montoMax = 0
                this.operacion.montoComision = 0
                this.operacion.comision = null
                this.operacion.monto = null
                this.operacion.observacion = ''
            }, 100);
        },
        async getEntidades(){
            this.entidades = []
            this.inCharge = true 
            try {
            const response = await this.axios.get(`${this.API}agente/entidad`, { params:
                {
                    q: ''
                }
            });
            this.entidades = response.data.data
            this.inCharge = false
            } catch (error) {
                console.log(error);
                this.inCharge = false
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            }
        },
        setAltImg(event) { 
            event.target.src = "static/no-photo.png" 
        },
        /* METODOS DE FINALES OPERACION */
        initForm () {
            this.operacion = {
                idEntidad: null,
                idServicio: null,
                cliente: {},
                calculo: 0,
                montoMax: 0,
                montoComision: 0,
                tieneComision: 0,
                comisionFija: 0,
                requiereCodigo: 0,
                codigo: '',
                nombreCodigo: '',
                ganancia: 0,
                operacion: '',
                monto: null,
                comision: null,
                observacion: '',
                telefono: '',
                total: 0,
                usuario: sessionStorage.getItem('User') || null
            }
            this.newDniRuc = ''
            this.idEntidadSelect = null
            this.nombreEntidadSelect = ''
        },
        setCliente (obj, dni = false) {
            let params = {}
            if (dni) {
                params = {
                    dni: dni
                }
            }else {
                params = {
                    idCliente: obj.IdCliente || obj
                }
            }

            if (typeof obj === 'object') {
                this.$refs.inputCliente.setValue(obj.Cliente)
                this.operacion.cliente = obj
            } else {
                this.axios.get(this.API + 'cliente', {
                    params: params
                })
                .then((response) => {
                    if (response.data) {
                        this.$refs.inputCliente.setValue(response.data.Cliente)
                        this.operacion.cliente = response.data
                    } else {
                        this.$toasted.show('No se encontro al cliente')
                    }
                })
                .catch((error) => {
                    console.log(error)
                })
            }
        },
        guardarOperacion(evt) {
            evt.preventDefault();
            this.inProgress = true

            if(!this.vendedor.Usuario){
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'No se cargo su usuario, vuelva a inciar sesión',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return
            }

            if((''+this.operacion.telefono).trim() ==''){
                this.operacion.telefono = (''+this.operacion.telefono).trim()
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'Ingrese el número del cliente',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return
            }

            if(!(this.operacion.cliente.IdCliente > 0)){
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'Debe seleccionar un cliente',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return
            }

            if(!(this.operacion.idServicio > 0)){
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'Debe seleccionar '+(this.opcionEntidad==2?'un servicio':'una operación'),
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return
            }

            if(!(this.operacion.monto > 0)){
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'Debe ingresar un monto de la operación',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return
            }

            if(this.operacion.requiereCodigo==1 && (''+this.operacion.codigo).trim() ==''){
                this.operacion.codigo = (''+this.operacion.codigo).trim()
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'Es requerido el <br> <b style="color: #dc3545;">'+ (''+this.operacion.nombreCodigo).toUpperCase()+'</b> <br> para guardar la operación',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return
            }

            if(this.operacion.tieneComision && !(this.operacion.comision > 0)){
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'La operación está configurada con comisión, debe tener un valor',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return    
            }

            this.operacion.telefono = (''+this.operacion.telefono).trim().toUpperCase()
            this.operacion.observacion = (''+this.operacion.observacion).trim().toUpperCase()
            this.operacion.codigo = (''+this.operacion.codigo).trim().toUpperCase()
            this.operacion.usuario = this.vendedor.Usuario
            this.operacion.operacionTipo = this.opcionEntidad

            this.$swal({
            target: document.getElementById('conten'),
            title: 'Se guardará la siguiente operación ¿Está seguro? ',
            icon: 'question',
            html: `<div style="text-align:left"><strong>Información de Operacion: </strong><br><hr>
                <strong>CLIENTE: </strong> ${this.operacion.cliente.Cliente}<br>
                <strong>TELEFONO: </strong> ${this.operacion.telefono}<br>
                <strong>ENTIDAD: </strong> ${this.nombreEntidadSelect}<br>
                <strong>OPERACION: </strong> ${this.operacion.operacion}<br>
                ${this.operacion.requiereCodigo?' <strong style="color: #dc3545;">'+(''+this.operacion.nombreCodigo).toUpperCase()+': </strong><span style="color: #dc3545;">'+this.operacion.codigo+'</span><br>':''}
                <strong>MONTO: </strong> S/ ${(this.operacion.monto > 0 ?  Number.parseFloat(this.operacion.monto).toFixed(2) : 0 )}<br>
                <strong>COMISION: </strong> S/ ${(this.operacion.comision > 0 ?  Number.parseFloat(this.operacion.comision).toFixed(2) : 0)}<br><hr>
                <strong>TOTAL A RECIBIR:  S/ ${(this.operacion.total > 0 ?  Number.parseFloat(this.operacion.total).toFixed(2) : 0)}</strong><br><hr>
                <strong>OBSERVACION: </strong> ${this.operacion.observacion}<br>`,
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'No, atrás'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    this.$swal({
                        title: 'Guardando Operación',
                        allowEscapeKey: false,
                        allowOutsideClick: false,
                        didOpen: () => {
                            this.$swal.showLoading();
                        }
                    })
                    try {
                        let respuesta = await this.axios.post(`${this.API}agente/comprobante`, this.operacion);
                        this.$swal.close()
                        if(respuesta.data.insertId){
                            this.limpiarDespues()
                            this.$swal({
                                position: 'center',
                                icon: 'success',
                                title: '¡Operación Realizada!',
                                showConfirmButton: true,
                                confirmButtonText: 'Aceptar',
                                target: document.getElementById('conten'),
                                confirmButtonColor:'#1D9828',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                            })
                        }else{
                            this.$swal({
                                position: 'center',
                                icon: 'warning',
                                title: respuesta.data.msg,
                                showConfirmButton: true,
                                confirmButtonText: 'Entiendo',
                                target: document.getElementById('conten'),
                                confirmButtonColor:'#3085d6',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                            })
                            if(respuesta.data.noexistecaja==1){
                                this.idCajaAbierta = null
                                this.cajaFechaApertura = ''
                                this.cajaIdTurno = null
                            }
                        }
                        this.inProgress = false
                    } catch (error) {
                        this.$swal.close()
                        this.$toasted.show('Ha ocurrido un error')
                        this.inProgress = false   
                    }
                }else{
                    this.inProgress = false
                }
            }) 

        },
        setTipoOperacion(obj){
            this.operacion.idServicio = (this.opcionEntidad==2?obj.IdServicio:obj.IdOperacion) 
            this.operacion.operacion = (this.opcionEntidad==2?obj.Servicio:obj.Operacion) 
            this.operacion.tieneComision = obj.TieneComision
            this.operacion.comisionFija = obj.ComisionFija
            this.operacion.requiereCodigo = obj.RequiereCodigo
            this.operacion.nombreCodigo = (''+obj.NombreCodigo).trim().toLowerCase()
            this.operacion.ganancia = obj.Ganancia
            this.operacion.montoMax = obj.MontoMax
            this.operacion.montoComision = obj.MontoComision
            this.operacion.calculo = obj.Calculo
            this.operacion.codigo = ''
            this.getComision()
        },
        focusAutocompleteOperacion (ref) {
            this.$refs[ref].type = ""
            this.$refs[ref].handleDoubleClick()
            this.operacion.idServicio = null
            this.operacion.operacion = ''
            this.operacion.tieneComision = 0
            this.operacion.comisionFija = 0
            this.operacion.requiereCodigo = 0
            this.operacion.nombreCodigo = ''
            this.operacion.codigo = ''
            this.operacion.ganancia = 0
            this.operacion.montoMax = 0
            this.operacion.montoComision = 0
            this.operacion.comision = null
        },
        focusAutocomplete (ref) {
            // this.$refs[ref].type = ""
            this.$refs[ref].handleDoubleClick()
        },
        setVendedor (obj) {
            this.vendedor = obj
        },
        getVendedor () {
            this.vendedor = {}
            this.idCajaAbierta = null
            this.cajaFechaApertura = ''
            this.cajaIdTurno = null
            this.cargaCorrecta = false
            let usuario = sessionStorage.getItem('User')
            if(usuario==null){
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'No se cargo su usuario, vuelva a inciar sesión',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.cargaCorrecta = false
                return
            }
            this.axios.get(this.API + 'agente/usuarios/' + usuario)
            .then((response) => {
                if(response.data){
                    this.idCajaAbierta = response.data.CajaAbierta
                    this.cajaFechaApertura = response.data.FechaApertura
                    this.cajaIdTurno = response.data.IdTurno
                    this.setVendedor(response.data)
                    this.cargaCorrecta = true
                }else{
                    this.$toasted.show('No existe data del vendedor')
                    this.cargaCorrecta = false
                }
            })
            .catch((error) => {
                console.log(error)
                this.cargaCorrecta = false
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            })
        },
        limpiarDespues(){
            this.initForm()
            this.getVendedor()
            this.loadClienteVarios()
            this.opcionEntidad = null
            this.empresas = []
            this.idEmpresaEntidadSelect = null
            this.nombreEntidadSelect = ''
        },
        getCapitalize(val){
            let valor = (''+val).trim().toLowerCase()
            if(valor==''){
                return ''
            }
            //return valor.charAt(0).toUpperCase() + valor.slice(1);
            return valor.replace(/(^\w{1})|(\s+\w{1})/g, letter => letter.toUpperCase());
        },
        changeMonto(val, opc){
            if(opc == 1){
                this.getComision(val)
            }

            if(opc==2){
                this.getComision()
            }
        },
        getComision(val=0){
            if(this.operacion.calculo == 1){
                if(this.operacion.tieneComision == 1){

                    if(this.operacion.comisionFija == 1){
                        this.operacion.comision = this.operacion.montoComision
                    } else {
                        let valor = val>0?val:Number(this.operacion.monto)
                        let valorEntero = Math.trunc(valor / Number(this.operacion.montoMax))
                        let valorDecimal = Math.round(valor / Number(this.operacion.montoMax) * 10) / 10
                        let excedeDecimal = valorDecimal - valorEntero
                        let valorMultiplicador = excedeDecimal>0 && this.operacion.ganancia==1? valorEntero + 1 : valorEntero
                        this.operacion.comision =  (valorMultiplicador || 0 ) * Number(this.operacion.montoComision)
                    }

                }else{
                    this.operacion.comision = 0
                }
            }else{
                //this.operacion.comision = null    
            }
            this.operacion.total = Number(this.operacion.monto || 0 ) + Number(this.operacion.comision  || 0 )
        },
        loadClienteVarios () {
            this.axios.get(this.API + 'clientes/00000000')
                .then(res => {
                    if (res.data.IdCliente) {
                        this.setCliente(res.data)
                    }
                })
        },
	},
	components: {
        ClientesForm,
        'vue-simple-spinner':Spinner,
	}
}
</script>
<style>
    .seleccionProducto {
        border: 4.5px solid #dc3545 !important;
    }

    .centrarOperacion {
        display: flex;
        justify-content: center;
        align-content: center;
        align-items: center;
    }

    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    /* Firefox */
    input[type=number] {
        -moz-appearance: textfield;
    }

    .inputLine .input-group-text {
        font-weight: bold;
        font-size: 0.9rem;
    }

    .inputLineRequerido .input-group-text {
        font-weight: bold;
        font-size: 0.9rem;
        color: #dc3545;
    }

    .inputLineOperacion .input-group-text {
        font-weight: bold;
        font-size: 0.9rem;
    }

    .inputLineOperacion {
        width: 300px !important;     
    }

    .inputLineNumber .input-group-text {
        font-weight: bold;
        font-size: 0.9rem;
    }

    .inputLineNumber {
        width: 330px !important;
    }

    .inputLineText .input-group-text {
        font-weight: bold;
        font-size: 0.9rem;
    }

    .seleccionEntidad {
        border: 3px solid #dc3545  !important;
    }

    .seleccionEmpresaEntidad {
        border: 3px solid #0d6efd  !important;
    }

    .seleccionEntidadOpcion {
        border: 2px solid #ffc107  !important;
        box-shadow: 0 0 0 0.15rem #ffc107 !important;   
    }
</style>