<template>
    <div>
      <b-container class="bv-example-row" id="conten">
          <br>
          <b-row>
				<b-col md="4" class="my-1">
              	<b-form-group horizontal label="Buscar" class="mb-0">
					<b-input-group>
					<b-form-input v-model="filter" autocomplete="off" @input="(val)=>{ getMantenimientos(val)}" placeholder="Buscar"/>
					<b-input-group-append>
						<b-btn @click="()=>{filter=''; getMantenimientos();}">Limpiar</b-btn>
					</b-input-group-append>
					</b-input-group>
              	</b-form-group>
			  </b-col>
              <b-col md="3" class="text-center">
                  <b-button id="popover-proyecto" :disabled="disabledNuevo" variant="primary">Nuevo Mantenimiento</b-button>
                  <b-popover  v-if="showPopover" target="popover-proyecto" placement="bottom" title="Empezar Nuevo Mantenimiento">
                        <b-form @submit="crearProyecto" @keydown.enter="prevent">
                            <label>Seleccione un cliente:</label>
                            <b-input-group>
                                <autocomplete
                                        required="required"
                                        :ref="'inputCliente'"
                                        :url="API + 'clientes'"
                                        anchor="ClienteDniRuc"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setCliente" 
										:onInput="(val) => newDniRuc = val"
										:onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                                        :classes="{input: 'form-control'}">
                                </autocomplete>
                                <b-input-group-append>
                                    <clientes-form :ruc="newDniRuc" @onAddCliente="(obj) => { setCliente(obj) }"></clientes-form>
                                </b-input-group-append>
                            </b-input-group>
                            <br>
							<label>Seleccione un Mecanico:</label>
                            <b-input-group>
                                <autocomplete
                                        required="required"
                                        :ref="'inputMecanico'"
                                        :url="API + 'mecanicos'"
                                        anchor="Mecanico"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setMecanico" 
										:onFocus="focusAutocomplete.bind(null, 'inputMecanico')"
                                        :classes="{input: 'form-control'}">
                                </autocomplete>
                            </b-input-group>
							<br>
                            <label>Fecha Inicio:</label>
                            <b-input-group>
                                <b-form-input v-model="selectCliente.FechaInicio"
                                    type="date"
                                    required
                                    placeholder="Fecha Inicio"></b-form-input>
                            </b-input-group>
                            <br>
                            <label>Fecha Fin:</label>
                            <b-input-group>
                                <b-form-input v-model="selectCliente.FechaFin"
                                    type="date"
                                    required
                                    placeholder="Fecha Fin"></b-form-input>
                            </b-input-group>
                            <br>
							<label>Descripcion del Mantenimiento:</label>
							<textarea 
								class="form-control"
								placeholder="Descripción"
								cols="12" rows="4"
								style="width: 100%; text-transform:uppercase"
								v-model="selectCliente.Descripcion">
							</textarea>
							<br>
                            <b-button type="submit" variant="success" size="sm">Crear Mantenimiento</b-button>
                        </b-form>	
                    </b-popover>
              </b-col>
			  <b-col md="3">
				<b-form-group size="sm" >
					<autocomplete
						required="required"
						ref="inputVendedor"
						:url="API + 'usuarios'"
						anchor="NombreUsuario"
						label="writer"
						:on-select="setVendedor" 
						:onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
						:classes="{input: 'form-control'}">
					</autocomplete>
				</b-form-group>
			  </b-col>
              <b-col md="2">
				<b-form-group size="sm">
                    <autocomplete
                      required="required"
                      ref="inputAlmacen"
                      :url="API + 'almacenes'"
                      anchor="Almacen"
                      label="writer"
                      :on-select="setAlmacen" 
                      :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                      :classes="{input: 'form-control'}">
                    </autocomplete>
                </b-form-group>
			  </b-col>
          </b-row>
		  <b-row v-show="false">
            <b-col>
              <b-form-group horizontal label="Inicio">
                <b-form-input
                  v-model="fechaIni"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio">
				</b-form-input>
              </b-form-group>
            </b-col>
            <b-col>
              <b-form-group horizontal label="Fin">
                <b-form-input
                  v-model="fechaFin"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col>
              <b-button variant="success" size="md" @click="getMantenimientos()" :disabled="fechaFin=='' || fechaIni==''"><i class="fas fa-search"></i> Buscar por Fecha de Registro</b-button>
			  <b-button variant="info" size="md" @click="limpiarBusquador()">Limpiar</b-button>
            </b-col>
		  </b-row>
		  <hr>
		  <b-row class="text-center">
			  <b-col>
				  <b-badge variant="success" style="font-size:0.9rem">Mantenimiento Antes de Fecha Fin</b-badge>
				  <b-badge variant="warning" style="font-size:0.9rem">Mantenimiento en Fecha Exacta</b-badge>
				  <b-badge variant="danger" style="font-size:0.9rem">Mantenimiento Pasados de Fecha Fin</b-badge>
			  </b-col>
		  </b-row>
          <hr>
		  <b-row>
			<b-col class="habitacion" md="3" v-for="(proyec, index) in proyectos" :key="index">
				<b-card :bg-variant="fechaVencimiento(proyec.FechaFin)" class="text-center">
								
						<h5>MANTENIMIENTO M001-{{ proyec.IdProyecto }}</h5>
						<h6 style="color: white">{{ proyec.Cliente }}</h6>
						<h5>S/ {{ proyec.TotalDetalle | moneda }}</h5>
						<i><h6 v-if="proyec.IdProforma" style="color:#ffc107"><b-badge variant="warning">Prof. Valor: S/ {{ proyec.TotalProforma | moneda }}</b-badge></h6>
						<h6 v-else><b-badge variant="warning">Sin Proforma asociada</b-badge></h6></i>
						<ul class="detalle text-center">
							<li class="text-dark font-weight-bold mb-2"><b-badge variant="light" style="font-size:0.9rem">FECHA INICIO: {{ proyec.FechaInicio }} </b-badge></li>
							<li class="text-dark font-weight-bold mb-2"><b-badge variant="light" style="font-size:0.9rem">FECHA FIN: {{ proyec.FechaFin }}</b-badge></li>
							<li class="text-dark font-weight-bold mb-2"><b-badge variant="light" style="font-size:0.9rem">FECHA REG: {{ proyec.FechaReg }}</b-badge></li>
						</ul>
						<b-row style="margin-top:-10px;">
							<b-col md="12" class="text-center">
 								<b-badge style="font-size: 0.88rem;" variant="primary">Adelantos: S/ {{proyec.Adelantos | moneda}}</b-badge>
							</b-col>
							<b-col md="12" class="text-center">
								<b-badge style="font-size: 0.88rem;" variant="info">Debe: S/ {{proyec.granTotalCalculado | moneda}}</b-badge>
							</b-col>
						</b-row>
						
						<div slot="footer">
							<b-button variant="dark" size="sm" @click="liberarProyecto(proyec.IdProyecto,proyec.Cliente)">LIBERAR</b-button>
							<b-button variant="primary" size="sm" @click="abrirCarrito(proyec)">+</b-button>
						</div>
				</b-card>
			</b-col>
		  </b-row>
      </b-container>
	  <b-form @submit.prevent="guardarProductos" @keydown.enter.prevent="()=>{}" >
			<b-modal v-model="show" size="lg" class="carritoForm" id="carrito" :title="'MANTENIMIENTO ' + 'M001-' +selectProyecto.IdProyecto " ref="modalCarrito">

				<div id="contentModal" >
				  	<div class="row">
						<div id="headerCamiones" class="col-md-12">

							<div class="text-center" style="margin-bottom: 20px;"><b>{{ selectProyecto.Cliente }}</b></div>
							<div style="margin-bottom: 20px;">
							<b-row>
								<b-col md="7">
									<label>Descripción del Mantenimiento:</label>
									<textarea
									:disabled="!editarDescripcion"
									class="form-control"
									placeholder="Descripción"
									cols="12" rows="3"
									style="width: 100%; text-transform:uppercase"
									v-model="selectProyecto.Descripcion">
									</textarea>
									<b-button v-if="!editarDescripcion" style="margin-top:5px" variant="primary" size="sm" @click="editarDescripcionFun()"><i class="fas fa-pencil-alt fa-sm"></i> Editar Descripcion</b-button>
									<b-button v-if="editarDescripcion" style="margin-top:5px" variant="success" size="sm" @click="guardarDescripcion()"><i class="fas fa-save fa-sm"></i> Guardar Descripcion </b-button>
								</b-col>
								<b-col md="5">
									<label>Imagen:</label>
									<b-form-group>
										<b-img :src="'/resources/images/pedidos/' + selectProyecto.Imagen" fluid alt="" style="width: 150px; height: 150px;" @error="setAltImg" @click="mostrarImagen(selectProyecto.Imagen)"></b-img>
										<input type="file" id="file" ref="file" @change="handleFileUpload()"/>
										<small>Nota: La imagen debe ser en formato .png y de 300x300</small>
										<b-button :disabled="!file" style="margin-top:5px" variant="success" size="sm" @click="subirImagen()">Subir Imagen</b-button>
									</b-form-group>
								</b-col>
							</b-row>	
							</div>
							<div class="text-left" v-if="IdProforma">
								<b style="padding: 4px; background: #ebf0ff; color:red">
									<b>SE GUARDARÁ PROFORMA: {{ codProfAnio }} - {{ codProfNumero }} <br>TOTAL PROFORMA: S/ {{ proformaTotal }}</b> 
								</b>
							</div>
							<div class="text-left" v-if="selectProyecto.IdProforma">
								<b style="padding: 4px; background: #ebf0ff;">
									<b>PROFORMA ACTUAL: {{ selectProyecto.ProformaAnio }} - {{ selectProyecto.ProformaNumero }} <br>TOTAL PROFORMA: S/ {{ selectProyecto.TotalProforma }}</b>
								</b>
							</div>
							<div class="text-left" v-if="selectProyecto.IdProforma==null && !IdProforma">
								<b style="padding: 4px; background: #ebf0ff; color:#ffc107">
									<b>SIN PROFORMA ASOCIADA</b>
								</b>
							</div>
							<br>

							<b-row>
								<b-col md="8" id="productosProduccion">
									<mantenimiento-form-det v-if="show"
									:id-proyecto="selectProyecto.IdProyecto"
									ref="proyectoproductos"
									@onChangeProductos="cambioProductos"
									></mantenimiento-form-det>
								</b-col>
								<b-col md="4">
									<label v-if="false"><b>Gastos</b></label>
									<input v-model="selectProyecto.Gasto" v-if="false"
											size="sm"
											type="number"
											min="0"
											autocomplete="off"
											class="form-control"
											placeholder="Monto Gasto S/."/>
									<b-input-group v-if="false">
										<b-form-input v-model="selectProyecto.GastoDesc"
											size="sm"
											type="text"
											placeholder="Descripción"></b-form-input>
										<b-button @click="insertarGasto" variant="primary" size="sm" :disabled="almacen.IdAlmacen != vendedor.IdAlmacen">+</b-button>
										<b-form-text id="inputLiveHelp">Los gastos se reflejará en Caja y Banco.</b-form-text>
									</b-input-group>
									<br v-if="false">
									<table class="table b-table table-sm" v-if="false">
										<thead>
											<tr>
											<th>Fecha</th>
											<th>Concepto</th>
											<th>Monto</th>
											</tr>
										</thead>
										<tbody>
											<tr v-for="(ade, index) in gastos" :key="index">
												<td>{{ ade.FechaDoc }}</td>
												<td>{{ ade.Concepto.split(" - ").pop() }}</td>
												<td>{{ ade.Tipo ? '-' + ade.Importe : ade.Importe }}</td>
											</tr>
											<tr class="bg-info text-white">
												<td colspan="2">TOTAL</td>
												<td>S/ {{ totalGastos }}</td>
											</tr>
										</tbody>
									</table>

									<hr v-if="false" />

									<label><b>Adelantos</b></label>
									<input v-model="selectProyecto.Adelanto"
											size="sm"
											type="number"
											min="0"
											autocomplete="off"
											class="form-control"
											placeholder="Monto Adelanto S/."/>
									<b-input-group>
										<b-form-textarea v-model="selectProyecto.AdelantoDesc"
											autocomplete="off"
											size="sm"
											rows="3"
											type="text"
											@input="(val) => selectProyecto.AdelantoDesc = val"
											placeholder="Descripción"/>
										<b-button @click="insertarAdelanto" style="margin-left: 5px" variant="primary" size="sm" v-show="!(almacen.IdAlmacen != vendedor.IdAlmacen)">+</b-button>
									</b-input-group>
									<b-form-text id="inputLiveHelp">Adelanto se reflejará en Caja y Banco.</b-form-text>
									<br />
									<table class="table b-table table-sm">
										<thead>
											<tr>
											<th>Fecha</th>
											<th>Concepto</th>
											<th>Monto</th>
											<th>Doc</th>
											</tr>
										</thead>
										<tbody>
											<tr v-for="(ade, index) in adelantos" :key="index">
												<td>{{ ade.FechaDoc }}</td>
												<td>{{ ade.Concepto.split(" - ").pop() }}</td>
												<td>{{ ade.Tipo ? '-' + ade.Importe : ade.Importe }}</td>
												<td><b-btn size="sm" variant="success" :href="'/imprimir/pagocredito.php?idCajaBanco=' + ade.IdCajaBanco" target="_blank"><i class="fas fa-print"></i></b-btn></td>
											</tr>
											<tr class="bg-info text-white">
												<td colspan="2">TOTAL</td>
												<td>S/ {{ totalAdelantos }}</td>
											</tr>
										</tbody>
									</table>
								</b-col>
							</b-row>

							<div class="text-center">
								<h4>Gran total: {{ granTotal  }}</h4> 
							</div>
						
						</div>
				  	</div>
				</div>

				<div slot="modal-footer" >
					<div style="left: 1rem;position: absolute;">
						<b-btn  variant="info" type="submit" target="_blank" :href="'/views/V_VentaForm.php?idProyecto=' + selectProyecto.IdProyecto + '&idCliente=' + selectProyecto.IdCliente">
						Pagar Ahora
						</b-btn>
						<b-btn  variant="" @click="imprimir" class="ocultarBtn">
							Imprimir
						</b-btn>
						<div style="float: right; margin-left: 10px;" class="ocultarBtn">
							<proformas @onSelectProforma="selectProforma" @mounted="mountedProforma"></proformas>
						</div>
					</div>
					<b-btn  variant="success" type="submit">
					Guardar
					</b-btn>
					<b-btn  variant="secondary" @click="show=false">
					Cerrar
					</b-btn>
				</div>
			</b-modal>
		</b-form>
    </div>
</template>
<script>
import { mapActions, mapState } from 'vuex'
import config from '@/store/config.js'
import MantenimientoFormDet from '@/components/MantenimientoFormDet'
import ClientesForm from '@/components/ClientesForm'
import Proformas from '@/components/ProformaLista.vue'

export default {
  	mounted () {
		this.getVendedor()
	},
	data () {
		return {
			API: config.API,
			show: false,
			showPopover: true,
			habitaciones: [],
			selectProducto: {},
			vuelo: '',
			fechaHoy: this.$moment().format("YYYY-MM-DD"),
			pisos: [1, 2, 3, 4, 5],
			montoTotal: 0,
			codProforma: null,
			profTotHab: 0,
			// proformaTotalCard: null,
			// habitacionesPiso: []
            //Nuevo datos
            selectCliente: {},
			proyectos: [],
			selectProyecto:{},
			adelantos: [],
			gastos: [],
			cajaBanco:[],
			IdProforma: null,
			proformaTotal: null,
			codProfAnio: null,
			codProfNumero: null,
			totalProductosCarrito:0,
			disabledNuevo:false,
			editarDescripcion:false,
			almacen: {},
			vendedor: {},
			filter: '',
			file: '',
			fechaIni: '',
			fechaFin: '',
			newDniRuc: '',
		}
	},
	computed: {
		...mapState({
            productos: state => state.cart.productos
        }),
		totalAdelantos() {
			let sumatoria = 0
			this.adelantos.map(ad => {
				if (ad.Tipo) {
					sumatoria -= parseFloat(ad.Importe)
				} else {
					sumatoria += parseFloat(ad.Importe)
				}
			})

			return sumatoria;
		},
		totalGastos() {
			let sumatoria = 0
			this.gastos.map(ad => {
				sumatoria += parseFloat(ad.Importe)
			})

			return sumatoria;
		},
		granTotal(){
			return  Math.abs(this.totalProductosCarrito + this.totalGastos - this.totalAdelantos).toFixed(2)
		}
	},
	watch:{
		show (val) {
        	if(val==true){
				this.IdProforma = null
				this.proformaTotal = null
				this.codProfAnio = null
				this.codProfNumero = null
				this.cargarCajaBanco()
        	}
    	}
	},
	methods: {
		setMecanico(data){
			this.selectCliente.IdMecanico = data.IdMecanico
		},
		focusAutocomplete (ref) {
            // this.$refs[ref].type = ""
            this.$refs[ref].handleDoubleClick()
        },
		limpiarBusquador(){
			this.fechaFin = ''
			this.fechaIni = ''
			this.getMantenimientos()
		},
		mostrarImagen(imagen){
			let hostUrl = sessionStorage.getItem('HostUrl')
			if(imagen && hostUrl){
				this.$swal({
				imageUrl: ''+hostUrl+'/resources/images/pedidos/'+imagen,
				imageWidth: 500,
				imageHeight: 500,
				confirmButtonText: 'Cerrar'
				})
			}
		},
		setAltImg(event) { 
            event.target.src = "static/no-photo.png" 
        },
		handleFileUpload() {
			this.file = this.$refs.file.files[0]
		},
		subirImagen(){
			if(this.file){
				let formData = new FormData();
				formData.append('imagen', this.file);

				this.axios({
				method    : 'post',
				url       : this.API + 'producto/pedidos/' + this.selectProyecto.IdProyecto,
				data      : formData,
				headers   : { 'Content-Type': 'multipart/form-data' }
				})
				.then((res) => {
					this.$toasted.info('Imagen guardada correctamente')
					this.$refs.file.value=null
					this.selectProyecto.Imagen = res.data.imagen
				})
			}else{
				this.$refs.file.value=null
				this.$toasted.error('No selecciono ningun archivo')
			}
		},
        //inicio de metodos de personalizado
		focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        	this.$refs[ref].handleDoubleClick()
    	},
		getVendedor () {
			let usuario = sessionStorage.getItem('User')
			this.axios.get(this.API + 'usuarios/' + usuario)
			.then((response) => {
				this.setVendedor(response.data)
				this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
				this.$refs.inputVendedor.$refs.input.disabled = true
				if(response.data.IdUsuarioPerfil!=1 && response.data.IdUsuarioPerfil!=23){	
					this.$refs.inputAlmacen.$refs.input.disabled = true
				}
				this.almacen.IdAlmacen = response.data.IdAlmacen
				this.almacen.Almacen = response.data.Almacen
			})
			.catch((error) => {
				console.log(error)
				this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
			})
		},
		setVendedor (obj) {
			this.vendedor = obj
			this.axios.get(this.API + 'almacenes/id/' + obj.IdAlmacen)
			.then(res => {
				this.setAlmacen(res.data)
				this.$refs.inputAlmacen.setValue(res.data.Almacen)	
			}).catch((err)=>{
			this.$toasted.show('Ha ocurrido un error, actualice la pagina y vuelva a intentarlo')
			})
		},
		setAlmacen (obj) {
      		this.almacen = obj;
			this.getMantenimientos()
    	},
		cambioProductos(total){
			this.totalProductosCarrito = Math.abs(total || 0).toFixed(2)
		},
		fechaVencimiento(fecha){
			let fechaValAntes = this.$moment(fecha).isAfter(this.$moment().format('YYYY-MM-DD'), 'day');
			let fechaValIgual = this.$moment(fecha).isSame(this.$moment().format('YYYY-MM-DD'), 'day');
			let fechaValDepues = this.$moment(fecha).isBefore(this.$moment().format('YYYY-MM-DD'), 'day');
			if(fechaValAntes==true){
				return 'success';
			}else if(fechaValIgual==true){
				return 'warning';
			}else if(fechaValDepues==true){
				return 'danger';
			}
		},
		liberarProyecto(idProyecto,cliente){
			let r = confirm('¿Está seguro de liberar el mantenimiento de '+cliente+'?')
			if (r) {
                this.$swal({
                title: 'Autorización de Administrador',
                html: `<form onsubmit="event.preventDefault();"><label>Habilitar el cambio de precios</label>
                <input type="password" id="password" class="swal2-input" placeholder="Ingrese su contraseña"><form>`,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Autorizar',
                cancelButtonText: 'Cancelar',
                target: document.getElementById('conten'),
                focusConfirm: false,
                    preConfirm: () => {
                    const password = this.$swal.getPopup().querySelector('#password').value
                    if (!password) {
                    this.$swal.showValidationMessage('Ingrese la contraseña de administrador')
                    }
                    return {password: password }
                }
                }).then((result) => {
                    if (result.value.password) {
                        this.axios.post(this.API + 'login', {
                        user: 'autorizacion',
                        password: result.value.password
                        }).then(r => {
                            if (r.data) {
								this.axios.get(this.API + 'proyecto/estado', {
								params: {
									IdProyecto: idProyecto
								}
								}).then((response) => {
									if(response.data.IdProyecto){
										this.axios.post(this.API + 'proyecto/liberar', {
											IdProyecto: idProyecto,
											Usuario:sessionStorage.getItem('User')
										}).then(res => {
											this.$toasted.info("Mantenimiento liberado!")
											this.getMantenimientos();
										}).catch(error=>{

										})
									}else{
										this.$toasted.show('Ya se pago este mantenimiento, se actualizará')
										this.getMantenimientos()
									}
								}).catch(error=>{
									this.$toasted.show('Hubo un error, no hay conexion')
								})
                            }else{
                                this.$toasted.error("¡Error de contraseña, No autorizado para liberar!")
                            }
                        })
                    }else{
                        this.$toasted.error("¡No autorizado!")
                    }
                })
            }
		},
		getMantenimientos(filter = ''){
			this.axios.get(this.API + 'proyectos',{
				params: {
					IdAlmacen: this.almacen.IdAlmacen,
					filter: filter || '',
					FechaIni: this.fechaIni,
					FechaFin: this.fechaFin,
				}
			}).then(res => {
				this.proyectos = res.data
			}).catch(err=>{
				this.proyectos = []
			})
		},
        crearProyecto(evt){
            evt.preventDefault();
			if(!(this.almacen.IdAlmacen>0)){
				this.$toasted.error("No se obtuvo el almacen, recargue la página")
				return
			}

			if(!(this.selectCliente.IdMecanico>0)){
				this.$toasted.error("Debe seleccionar un Mecanico para el mantenimiento")
				return
			}

            if(Object.keys(this.selectCliente).length !==0 && this.selectCliente.IdCliente!=undefined){
				this.disabledNuevo = true
				if(!this.selectCliente.Descripcion){
					this.selectCliente.Descripcion = ''
				}
				this.selectCliente.Descripcion = (""+this.selectCliente.Descripcion).trim().toUpperCase()
                this.axios.post(this.API + 'nuevo/proyecto', {
					proyecto: this.selectCliente,
					Usuario: sessionStorage.getItem('User'),
					IdAlmacen: this.almacen.IdAlmacen
				}).then(res => {
					this.newDniRuc = ''
					this.$refs.inputCliente.setValue('')
                    this.selectCliente = {};
                    this.$root.$emit('bv::hide::popover');
                    this.$toasted.info("Mantenimiento Creado!")
					this.disabledNuevo = false
					this.getMantenimientos();
				}).catch(error=>{
					 this.$toasted.error("Se ha producido un error")
					this.disabledNuevo = false
                })
            }
        },
		abrirCarrito(pers){
			this.$refs.file.value=null
			this.selectProyecto.Adelanto = null
			this.selectProyecto.AdelantoDesc = ''
			this.axios.get(this.API + 'proyecto/estado', {
            params: {
                IdProyecto: pers.IdProyecto
            }
            }).then((response) => {
                if(response.data.IdProyecto){
					this.selectProyecto = pers
					this.$refs.modalCarrito.show()
                }else{
					this.$toasted.show('Ya se pago este mantenimiento, se actualizará')
					this.getMantenimientos()
                }
            }).catch(error=>{
                this.$toasted.show('Hubo un error, no hay conexion')
            })
		},
		getVariant(proyec) {
		 if (proyec.Estado == 0) return 'info'
		 if (proyec.Estado == 1) return 'danger'
		 return 'success'
		},
		cargarAdelantos() {
			this.axios.get(this.API + 'proyecto/cajaybanco', { params: {
				IdProyecto		: this.selectProyecto.IdProyecto,
				TipoCajaBanco 	: 0
			} }).then(res => {
				if(res.data){
					this.adelantos = res.data
					let index = this.proyectos.findIndex(pe=>pe.IdProyecto==this.selectProyecto.IdProyecto)
					if(index!==-1){
						this.$nextTick(() => {
							this.proyectos[index].Adelantos = this.totalAdelantos;
						})	
					}
				}
			})
		},
		cargarGastos() {
			this.axios.get(this.API + 'proyecto/cajaybanco', { params: {
				IdProyecto		: this.selectProyecto.IdProyecto,
				TipoCajaBanco 	: 1
			} }).then(res => {
				if(res.data){
					this.gastos = res.data
					let index = this.selectProyecto.findIndex(pe=>pe.IdProyecto==this.selectProyecto.IdProyecto)
					if(index!==-1){
						this.$nextTick(() => {
							this.proyectos[index].Gastos = this.totalGastos;
						})	
					}
				}
				
			})
		},
		cargarCajaBanco(){
			this.gastos=[];
			this.adelantos=[];
			this.axios.get(this.API + 'proyecto/cajaybanco/todo', { params: {
				IdProyecto		: this.selectProyecto.IdProyecto,
			} }).then(res => {
				this.cajaBanco = res.data
				this.cajaBanco.map(cb=>{
					if(cb.Tipo==0){
						this.adelantos.push(cb)
					}
					if(cb.Tipo==1){
						this.gastos.push(cb)
					}
				})
				let index = this.proyectos.findIndex(pe=>pe.IdProyecto==this.proyectos.IdProyecto)
				if(index!==-1){
					this.$nextTick(() => {
						this.proyectos[index].Adelantos = this.totalAdelantos;
						this.proyectos[index].Gastos = this.totalGastos;
					})	
				}
			})
		},
		insertarAdelanto() {
			let usuario = sessionStorage.getItem('User')

			if(!usuario){
				this.$toasted.error('El usuario no ha sido identificado, actualice la pagina')
				return;
			}

			if(!(this.selectProyecto.Adelanto>0)) {
				this.$toasted.error('Ingrese un monto del adelanto')
				return;
			}

			if(this.selectProyecto.AdelantoDesc==undefined){
				this.selectProyecto.AdelantoDesc=''
			}
			
			if((''+this.selectProyecto.AdelantoDesc).trim()=='') {
				this.$toasted.error('Ingrese una descripcion del adelanto')
				return;
			}

			this.selectProyecto.AdelantoDesc = (''+this.selectProyecto.AdelantoDesc).toUpperCase()
			let r = confirm("¿Desea Insertar un adelanto?")

			if (r) {
				this.axios.post(this.API + 'proyecto/cajaybanco/adelanto', {
					IdProyecto: this.selectProyecto.IdProyecto,
					IdCliente: this.selectProyecto.IdCliente,
                    Concepto: 'Adelanto Mantenimiento M001-' + this.selectProyecto.IdProyecto + ' - ' + (this.selectProyecto.AdelantoDesc || ''),
					Adelanto: this.selectProyecto.Adelanto,
					Usuario: usuario
				}).then(res => {
					this.cargarAdelantos();
					this.selectProyecto.Adelanto 		= '' 
					this.selectProyecto.Producto 		= '' 
					this.selectProyecto.AdelantoDesc	= ''
				})
			}
		},
		insertarGasto() {
			let usuario = sessionStorage.getItem('User')

			if(this.selectProducto.Gasto <= 0) {
				return;
			}

			let r = confirm("¿Desea Insertar un gasto?")

			if (r) {
				this.axios.post(this.API + 'proyecto/cajaybanco/gasto', {
					IdProyecto: this.selectProyecto.IdProyecto,
					IdCliente: this.selectProyecto.IdCliente,
                    Concepto: 'Gasto Proyecto ' + this.selectProyecto.IdProyecto + ' - ' + this.selectProyecto.GastoDesc,
					Gasto: this.selectProyecto.Gasto,
					Usuario: usuario
				}).then(res => {
					this.cargarGastos();
					this.selectProyecto.Gasto 		= '' 
					this.selectProyecto.Producto 	= '' 
					this.selectProyecto.GastoDesc 	= ''
				})
			}
		},
		guardarProductos() {
			this.axios.post(this.API + 'proyecto/detalle', {
				IdProyecto: this.selectProyecto.IdProyecto,
				productos: this.$refs.proyectoproductos.productos,
				IdProforma: this.IdProforma
			}).then(res => {
				if(this.IdProforma != null){
					let index = this.proyectos.findIndex(pe=>pe.IdProyecto==this.selectProyecto.IdProyecto)
					if(index!==-1){
						this.$nextTick(() => {
							this.proyectos[index].IdProforma = this.IdProforma
							this.proyectos[index].ProformaAnio = this.codProfAnio
							this.proyectos[index].ProformaNumero = this.codProfNumero
							this.proyectos[index].TotalProforma = this.proformaTotal
						})	
					}
				}
				this.$toasted.success('Guardado correctamente')
				this.show = false
				this.getMantenimientos();
			})	
		},
		sumProductos() {
			let total = 0
			this.$nextTick(()=>{
				
				if (this.$refs.proyectoproductos) {
					console.log(this.$refs.proyectoproductos.sumProductos);
					total = this.$refs.proyectoproductos.totalProductos + this.totalGastos - this.totalAdelantos
				}
				
			})
			return Math.abs(total).toFixed(2)
		},
        //Fin de metodos de personalizado
		mountedProforma(modal) {
			document.body.appendChild(modal)
		},
		selectProforma(proforma) {
			console.log(proforma);
			this.show = true
			let totalProf = 0  
			this.$nextTick(() => {
				proforma.productos.map(p => {
					let producto = this.$refs.proyectoproductos.productos.find(pr => pr.IdProducto === p.IdProducto)
					if (!producto) {
						totalProf += (p.Precio * p.Cantidad)
						this.$refs.proyectoproductos.productos.push(p)
					}
				})
				this.proformaTotal = totalProf
				this.IdProforma = proforma.IdProforma
				this.codProfAnio = proforma.Anio
				this.codProfNumero = proforma.Numero
			})
		},
		setCliente(obj) {
			//if (index >= 0) {
			//	this.$refs['inputCliente2' + index][0].setValue(obj.Cliente)
			//}
            if (typeof obj === 'object') {
                this.$refs.inputCliente.setValue(obj.Cliente)
            }
			this.selectCliente = obj
            console.log(obj);

		},
		limpiarDespues() {
			this.selectCliente = {}
			this.selectProducto = {}
		},
		formatFecha(fecha) {
			return this.$moment(fecha, 'YYYY-MM-DD hh:mm:ss').format('YYYY-MM-DD')
		},
		imprimir() {
			var elem = "contentModal";
			var cssHead = '<head><link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"></head>'

			var mywindow = window.open('', 'PRINT', 'height=600,width=1000');
			mywindow.document.write(cssHead);
			mywindow.document.write('<h5 style="text-align:center">' + document.querySelector('#carrito .modal-title').textContent  + '</h5> <hr>');
			mywindow.document.write(document.getElementById(elem).innerHTML);
			
			for (var i = 0; i < mywindow.document.querySelectorAll('input, button, a').length; ++i) {
				mywindow.document.querySelectorAll('input, button, a')[i].style.display = "none";
			}

			mywindow.document.getElementById('headerCamiones').className = "col-md-12";
			mywindow.document.getElementById('productosProduccion').className = "col-md-8";

			mywindow.document.close(); // necessary for IE >= 10
			mywindow.focus(); // necessary for IE >= 10*/

			mywindow.onload = function() {
				setTimeout(function(){
					mywindow.print();
					// mywindow.close();
				}, 500);
			}

			return true;
		},
		editarDescripcionFun(){
			this.editarDescripcion = !this.editarDescripcion
		},
		guardarDescripcion(){
			this.axios.post(this.API + 'proyecto/descripcion', {
				IdProyecto: this.selectProyecto.IdProyecto,
				Descripcion: (""+this.selectProyecto.Descripcion).trim().toUpperCase()
			}).then(res => {
				this.editarDescripcion = false
				this.$toasted.info('Descripcion guardada correctamente')
			}).catch(error=>{
				this.$toasted.show('No se guardo la descripcion, hubo un error')
			})
		}
	},
	components: {
		MantenimientoFormDet,
		ClientesForm,
		Proformas,
	}
}
</script>
<style>
@media print {
	body .printable, body .modal {
		display:block;
	}
	#pisos {
		visibility: hidden;
	}
	.modal {
		/**Remove scrollbar for printing.**/
		overflow: visible !important;
		position: absolute;
	}
	.modal-dialog {
		visibility: visible !important;
		/**Remove scrollbar for printing.**/
		overflow: visible !important;
	}
	.modal-footer {
		visibility: hidden;
	}
}
.popover{
    min-width: 520px !important;
}
/*.carritoForm .modal-dialog {
    max-width: 60% !important;
}*/
</style>
<style scoped>
h5 {
	font-size: 1.2rem;
	color: #ffffff;
}
h6 {
	font-size: 1rem;
	color: #DBDBDB;
}
.card-footer {
	padding-left: 0;
	padding-right: 0;
}
.habitacion {
	margin-bottom: 10px;
}
.detalle {
	list-style: none;
    padding: 0;
	text-align: left;
	font-size: 0.8rem;
	color: #363636;
}
</style>
<style>
#carrito .modal-dialog{
    max-width: 65%;
}
@media only screen and (min-width: 0px) and (max-width: 800px) {
  /* For desktop: */
    #carrito .modal-dialog{
        max-width: 100% !important;
		margin: 12px;
    }

	.ocultarBtn{
		display: none !important;
	}

}
</style>
