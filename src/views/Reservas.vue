<template>
    <div>
      <b-container class="bv-example-row">
          <br>
          <b-row>
              <b-col></b-col>
              <b-col class="text-center">
                  <b-button id="popover-proyecto" :disabled="disabledNuevo" variant="primary">Nueva Reserva</b-button>
                  <b-popover  v-if="showPopover" target="popover-proyecto" placement="bottom" title="Empezar Nueva Reserva">
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
										:onInput="(val) => newDniRuc = val"
                                        :on-select="setCliente" 
                                        :classes="{input: 'form-control'}">
                                </autocomplete>
                                <b-input-group-append>
                                    <clientes-form :ruc="newDniRuc" @onAddCliente="(obj) => { setCliente(obj) }"></clientes-form>
                                </b-input-group-append>
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
							<label>Descripcion:</label>
							<textarea 
								class="form-control"
								placeholder="Descripción"
								cols="12" rows="4"
								style="width: 100%; text-transform:uppercase"
								v-model="selectCliente.Descripcion">
							</textarea>
							<br>
                            <b-button type="submit" variant="success" size="sm">Crear Reserva</b-button>
                        </b-form>	
                    </b-popover>
              </b-col>
			  <b-col>
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
              <b-col>
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
		  <hr>
		  <b-row class="text-center">
			  <b-col>
				  <b-badge variant="success" style="font-size:0.9rem">Reservas Antes de Fecha Fin</b-badge>
				  <b-badge variant="warning" style="font-size:0.9rem">Reservas en Fecha Exacta</b-badge>
				  <b-badge variant="danger" style="font-size:0.9rem">Reservas Pasados de Fecha Fin</b-badge>
			  </b-col>
		  </b-row>
          <hr>
		  <b-row>
			<b-col class="habitacion" md="3" v-for="(proyec, index) in proyectos" :key="index">
				<b-card :bg-variant="fechaVencimiento(proyec.FechaFin)" class="text-center">
								
						<h5>RESERVA - {{ proyec.IdProyecto }}</h5>
						<h6 style="color: white">{{ proyec.Cliente }}</h6>
						<h5>S/ {{ proyec.granTotalCalculado | moneda }}</h5>
						<i><h6 v-if="proyec.IdProforma" style="color:#ffc107"><b-badge variant="warning">Prof. Valor: S/ {{ proyec.TotalProforma | moneda }}</b-badge></h6>
						<h6 v-else><b-badge variant="warning">Sin Proforma asociada</b-badge></h6></i>
						<ul class="detalle text-center">
							<li class="text-dark font-weight-bold"><b-badge variant="light" style="font-size:0.9rem">FECHA INICIO: {{ proyec.FechaInicio }} </b-badge></li>
							<br>
							<li class="text-dark font-weight-bold"><b-badge variant="light" style="font-size:0.9rem">FECHA FIN: {{ proyec.FechaFin }}</b-badge></li>
						</ul>
						<b-row>
							<b-col md="12" class="text-center">
 								<b-badge variant="primary">Adelantos: S/ {{proyec.Adelantos | moneda}}</b-badge>
							</b-col>
							<b-col md="6" v-if="false">
								<b-badge variant="danger">Gastos: S/ {{proyec.Gastos | moneda}}</b-badge>
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
			<b-modal v-model="show" size="lg" class="carritoForm" id="carrito" :title="'RESERVA ' + selectProyecto.IdProyecto " ref="modalCarrito">

				<div id="contentModal" >
				  	<div class="row">
						<div id="headerCamiones" class="col-md-12">

							<div class="text-center" style="margin-bottom: 20px;"><b>{{ selectProyecto.Cliente }}</b></div>
							<div style="margin-bottom: 20px;">
							<label>Descripción de Reserva:</label>
							<b-row>
								<b-col md="9">
									<textarea
									:disabled="!editarDescripcion"
									class="form-control"
									placeholder="Descripción"
									cols="12" rows="3"
									style="width: 100%; text-transform:uppercase"
									v-model="selectProyecto.Descripcion">
								</textarea>
								</b-col>
								<b-col md="3">
									<b-button v-if="!editarDescripcion" style="margin-top:5px" variant="primary" size="sm" @click="editarDescripcionFun()"><i class="fas fa-pencil-alt fa-sm"></i> Editar Descripcion</b-button>
									<b-button v-if="editarDescripcion" style="margin-top:5px" variant="success" size="sm" @click="guardarDescripcion()"><i class="fas fa-save fa-sm"></i> Guardar Descripcion </b-button>
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
									<proyecto-form-det v-if="show"
									:id-proyecto="selectProyecto.IdProyecto"
									ref="proyectoproductos"
									@onChangeProductos="cambioProductos"
									></proyecto-form-det>
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
										<b-form-input v-model="selectProyecto.AdelantoDesc"
											autocomplete="off"
											size="sm"
											type="text"
											placeholder="Descripción"></b-form-input>
										<b-button @click="insertarAdelanto" variant="primary" size="sm" :disabled="almacen.IdAlmacen != vendedor.IdAlmacen">+</b-button>
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
						<b-btn  variant="" @click="imprimir">
							Imprimir
						</b-btn>
						<div style="float: right; margin-left: 10px;">
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
import ProyectoFormDet from '@/components/ProyectoFormDet'
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
				if(response.data.IdUsuarioPerfil!=1){	
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
			this.getProyectos()
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
			let r = confirm('¿Está seguro de liberar la reserva de '+cliente+'?')
			if (r) {
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
							this.$toasted.info("Reserva liberada!")
							this.getProyectos();
						}).catch(error=>{

						})
					}else{
						this.$toasted.show('Ya se pago esta reserva, se actualizará')
						this.getProyectos()
					}
				}).catch(error=>{
					this.$toasted.show('Hubo un error, no hay conexion')
				})
			}
		},
		getProyectos(){
			this.axios.get(this.API + 'proyectos',{
				params: {
					IdAlmacen: this.almacen.IdAlmacen
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
				this.$toasted.error("No se obtuvo el almacen, recague la página")
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
					this.$refs.inputCliente.setValue('')
                    this.selectCliente = {};
                    this.$root.$emit('bv::hide::popover');
                    this.$toasted.info("Reserva creada!")
					this.disabledNuevo = false
					this.getProyectos();
				}).catch(error=>{
					 this.$toasted.error("Se ha producido un error")
					this.disabledNuevo = false
                })
            }
        },
		abrirCarrito(pers){
			this.axios.get(this.API + 'proyecto/estado', {
            params: {
                IdProyecto: pers.IdProyecto
            }
            }).then((response) => {
                if(response.data.IdProyecto){
					this.selectProyecto = pers
					this.$refs.modalCarrito.show()
                }else{
					this.$toasted.show('Ya se pago esta reserva, se actualizará')
					this.getProyectos()
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
                    Concepto: 'Adelanto Reserva' + this.selectProyecto.IdProyecto + ' - ' + (this.selectProyecto.AdelantoDesc || ''),
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
				this.getProyectos();
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
		ProyectoFormDet,
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
    max-width: 420px !important;
}
.carritoForm .modal-dialog {
    max-width: 60% !important;
}
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