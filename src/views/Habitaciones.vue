<template>
    <div>
      <b-container class="bv-example-row">
		  <div class="piso" v-for="(pi, i) in pisos" :key="i">
			<div v-if="getHabitacionesPiso(pi).length">
			  <br>
			  	<b-alert show class="text-center"><b>PROYECTOS CADEMEZA</b></b-alert>
			  <br>
			</div>
        	<b-row>
				<b-col class="habitacion" md="3" v-for="(hab, index) in getHabitacionesPiso(pi)" :key="index">
						<b-card :bg-variant="getVariant(hab)"  :header-bg-variant="hab.EstaSucio==1?'secondary':'none'" text-variant="white"
										class="text-center">
								<div slot="header">
									<b-row v-if="hab.EstaSucio==1">
										<b-col md="2">
											<b-button variant="info" size="sm" @click="limpiarHabitacion(hab.IdProducto,hab.Producto)"><i class="fas fa-broom"></i></b-button>
										</b-col>
										<b-col md="10">
											<label>{{ hab.Producto }}</label>
										</b-col>
									</b-row>
									<b-row v-else>
										<b-col md="12">
											<label>{{ hab.Producto }}</label>
										</b-col>
									</b-row>
								</div>			
								
								<h5>S/. {{ (hab.granTotalCalculado || 0) | moneda }}</h5>
								<ul class="detalle" v-if="hab.EstadoHabitacion == 2">
									<li><b style="color:white">{{ hab.Cliente }}</b></li>
									<li><b>FECHA: {{ formatFecha(hab.FechaReserva) }}</b></li>
									<li v-if="hab.vuelo"><br>Vuelo: {{ hab.Vuelo }}</li>
								</ul>
								<ul class="detalle" v-if="hab.EstadoHabitacion == 3">
									<li><b style="color:white; font-size:15px">{{ hab.Cliente }}</b></li>
									<li><b style="color:white">INICIO: {{ hab.FechaAlquiler }}</b></li>
									<li><b style="color:white">FIN: {{ hab.FechaAlquilerFin }}</b></li>
									<b-popover  v-if="showPopover" :target="i + 'popover-agregar' + index" title="Agregar Huesped a la Habitación" triggers="click">
										<label>Seleccione un cliente:</label>
										<b-input-group>
											<autocomplete
													required="required"
													:ref="'inputCliente3' + index"
													:url="API + 'clientes'"
													anchor="ClienteDniRuc"
													label="writer"
													placeholder=""
													 :onInput="(val) => newDniRuc2 = val"
													:on-select="setHuesped" 
													:classes="{input: 'form-control'}">
											</autocomplete>
											<b-input-group-append>
												<!--<proveedores-tabla></proveedores-tabla>-->
												<clientes-form :ruc="newDniRuc2" idModal="modal2" @onAddCliente="(obj) => { setHuesped(obj, index) }"></clientes-form>
											</b-input-group-append>
										</b-input-group>
										<br>
										<b-button variant="success" size="sm" @click="agregarHuesped(hab,index)">Agregar Huesped</b-button>	
									</b-popover>
								</ul>
								<div slot="footer">
									<b-btn @click="liberarHab(hab)" variant="success" size="sm">Liberar</b-btn>	
									<b-btn :id="i + 'popover-reservar' + index" variant="warning" size="sm">Reservar</b-btn>	
									<b-popover v-if="showPopover"  :target="i + 'popover-reservar' + index" title="Reservar Habitación">
										<label>Seleccione un cliente:</label>
										<autocomplete
												required="required"
												:ref="'inputCliente1' + index"
												:url="API + 'clientes'"
												anchor="ClienteDniRuc"
												label="writer"
												placeholder=""
												:on-select="setCliente" 
												:classes="{input: 'form-control'}">
										</autocomplete>
										<label for="inputLive">Fecha Reserva:</label>
										<b-form-input v-model="fechaReserva"
											type="date"></b-form-input>
		
										<label for="inputLive">Vuelo:</label>
										<b-form-input v-model="vuelo"
											type="text"></b-form-input>
										<br />
										<b-button variant="success" size="sm" @click="reservarHab(hab)">Reservar Habitación</b-button>	
									</b-popover>
									<b-btn :id="i + 'popover-alquilar' + index" variant="danger" size="sm">Vender</b-btn>	
									<b-popover  v-if="showPopover" :target="i + 'popover-alquilar' + index" title="Crear Proyecto" triggers="click">
										<label>Seleccione un cliente <b>Titular</b>:</label>
										<b-input-group>
											<autocomplete
													required="required"
													:ref="'inputCliente2' + index"
													:url="API + 'clientes'"
													anchor="ClienteDniRuc"
													label="writer"
													placeholder=""
													 :onInput="(val) => newDniRuc = val"
													:on-select="setCliente" 
													:classes="{input: 'form-control'}">
											</autocomplete>
											<b-input-group-append>
												<!--<proveedores-tabla></proveedores-tabla>-->
												<clientes-form :ruc="newDniRuc" @onAddCliente="(obj) => { setCliente(obj, index) }"></clientes-form>
											</b-input-group-append>
										</b-input-group>
										<br />
										<label>Lugar Procedencia:</label>
										<b-input-group>
											<b-form-input v-model="selectCliente.LugarProcedencia"
												type="text"
												placeholder="Lugar Procedencia"></b-form-input>
										</b-input-group>
										<br>
										<label>Medio de transporte:</label>
										<b-input-group>
											<b-form-input v-model="selectCliente.MedioTransporte"
												type="text"
												placeholder="Medio de transporte"></b-form-input>
										</b-input-group>
										<br>
										<label>Próximo destino:</label>
										<b-input-group>
											<b-form-input v-model="selectCliente.ProximoDestino"
												type="text"
												placeholder="Próximo destino"></b-form-input>
										</b-input-group>
										<br>
										<label>Fecha Inicio:</label>
										<b-input-group>
											<b-form-input v-model="selectCliente.FechaAlquilerInicio"
												type="datetime-local"
												placeholder="Fecha Inicio"></b-form-input>
										</b-input-group>
										<br>
										<label>Fecha Fin:</label>
										<b-input-group>
											<b-form-input v-model="selectCliente.FechaAlquilerFin"
												type="datetime-local"
												placeholder="Fecha Fin"></b-form-input>
										</b-input-group>
										<br>

										<b-button variant="success" size="sm" @click="alquilarHab(hab,index)">Crear Proyecto</b-button>	
									</b-popover>
									<b-button v-if="hab.IdPreOrden && hab.EstadoHabitacion == 3" @click="openCarrito(hab)" variant="primary" size="sm">+</b-button>
									<div style="margin-top:10px">
										<b-btn v-if="!hab.DocVenta && hab.EstadoHabitacion == 3" variant="dark" size="sm" @click="liberarForzado(hab)">Liberar Forzado</b-btn>
										<span v-if="hab.EstadoHabitacion == 3">
											<b-btn :id="i + 'popover-cambiar' + index" variant="light" size="sm" @click="selectDefectoCambio" >Cambiar Proy.</b-btn>
											<b-popover :target="i + 'popover-cambiar' + index"  v-if="showPopover" title="Cambiar Habitación" triggers="click">
												<label>Proyecto Disponibles:</label>
												<b-form-text text-variant="info" style="margin-top:-10px">(Los proyecto ocupados y reservadas no se mostrarán.)</b-form-text>
												<b-input-group style="margin-top:-10px">
													<b-form-select style="font-weight:bold; font-size:12px" v-model="selectHabCambio" :options="options" size="sm" class="mt-3"></b-form-select>
												</b-input-group>
												<br>
												<b-button variant="success" size="sm" @click="cambiarHab(hab,index)">Cambiar Proyecto</b-button>	
											</b-popover>
										</span>

									</div>
									<div style="margin-top:10px" v-if="hab.DocVenta && hab.EstadoHabitacion == 3">
										<b-btn :id="i + 'popover-renovar' + index" variant="info" size="sm">Renovar</b-btn>	
										<b-popover  v-if="showPopover" :target="i + 'popover-renovar' + index" title="Renovar Alquiler Habitación" triggers="click">
											<label>Nueva Fecha Inicio:</label>
											<b-input-group>
												<b-form-input v-model="selectCliente.FechaAlquilerInicioRe"
													type="datetime-local"
													placeholder="Fecha Inicio"></b-form-input>
											</b-input-group>
											<br>
											<label>Nueva Fecha Fin:</label>
											<b-input-group>
												<b-form-input v-model="selectCliente.FechaAlquilerFinRe"
													type="datetime-local"
													placeholder="Fecha Fin"></b-form-input>
											</b-input-group>
											<br>
											<b-button variant="success" size="sm" @click="renovarHabitacion(hab,index)">Renovar Alquiler</b-button>	
										</b-popover>
									</div>
								</div>
						</b-card>
					</b-col>
			</b-row>
		  </div>
      </b-container>

		<b-form @submit.prevent="guardarProductos" @keydown.enter.prevent="()=>{}" >
			<b-modal v-model="show" size="lg" id="carrito"  no-close-on-backdrop :title="'Cesta de compras - Proyecto ' + selectProducto.Producto " ref="modalCarrito">

				<div id="contentModal" >
				  	<div class="row">
						<div id="headerCamiones" class="col-md-12">
							<div class="text-right horaFechaImpreso"> Fecha y Hora del impreso: <span id="fechaHoraImp" style="font-weight:bold"></span></div>
							<div class="text-left" style="margin-bottom: 20px;">Fecha del Proyecto: <b> Inicio: {{ selectProducto.FechaAlquiler }} - Fin: {{ selectProducto.FechaAlquilerFin }}</b></div>
							<div class="text-center" style="margin-bottom: 20px;"><b>{{ selectProducto.DniRuc }} - {{ selectProducto.Cliente }}</b></div>
							<div class="text-left" v-if="proformaTotal">
								<b style="padding: 4px; background: #ebf0ff;">
									<b>PROFORMA:</b> {{ codProforma }} <br> S/ {{ proformaTotal }}
								</b>
							</div>
							<br>
							
							<b-row>
								<b-col md="8" id="productosProduccion">
									<pre-orden-form-det v-if="show"
									:id-preorden="selectProducto.IdPreOrden"
									:id-producto="selectProducto.IdProducto"
									ref="preOrdenProductos"
									 @onUpdateProductos="calcularManoObra"
									></pre-orden-form-det>

									<!-- <b-row>
										<b-col md="6">
											<table class="table b-table border table-footer table-sm text-center" >
											<tbody>
												<tr><td>Mano de obra bruta</td></tr>
												<tr><td><h3>{{ totalManoObraBruta | redondear }}</h3></td></tr>
												<tr><td>Mano de obra - Materiales</td></tr>
												<tr><td><h3 :class="{'text-danger': totalManoObraMenoMat <= 0}">{{ totalManoObraMenoMat | redondear }}</h3></td></tr>
											</tbody>
											</table>
										</b-col>
										<b-col md="6">
											<table class="table b-table border table-footer table-sm text-center" >
											<tbody>
												<tr><td>Total de Gastos</td></tr>
												<tr><td><h3>{{ totalGastos | redondear }}</h3></td></tr>
												<tr><td>Mano de obra neta</td></tr>
												<tr><td><h3 :class="{'text-danger': totalManoObraNeta <= 0}">{{ totalManoObraNeta | redondear }}</h3></td></tr>
											</tbody>
											</table>
										</b-col>
									</b-row> -->

								</b-col> 
								<b-col md="4">

									<hr />

									<label><b>Adelantos</b></label>
									<b-form-select v-model="idMetodoPago" :options="metodosPago" size="md"/>
									<input v-model="selectProducto.Adelanto"
											size="sm"
											type="number"
											class="form-control"
											step="0.01"
											placeholder="Adelanto S/."/>
									<b-input-group>
										<b-form-input v-model="selectProducto.AdelantoDesc"
											size="sm"
											type="text"
											placeholder="Descripción"></b-form-input>
										<b-button @click="insertarAdelanto" variant="primary" size="sm">+</b-button>
										<b-form-text id="inputLiveHelp">Adelanto se reflejará en Caja y Banco.</b-form-text>
									</b-input-group>

									<br />
									<table class="table b-table table-sm">
										<thead>
											<tr>
											<th>Fecha</th>
											<th>Concepto</th>
											<th>Cantidad</th>
											</tr>
										</thead>
										<tbody>
											<tr v-for="(ade, index) in adelantos" :key="index">
												<td>{{ ade.FechaDoc }}</td>
												<td>{{ ade.Concepto.split(" - ").pop() }}</td>
												<td>{{ ade.Tipo ? '-' + ade.Importe : ade.Importe }}</td>
											</tr>
											<tr class="bg-info text-white">
												<td colspan="2">TOTAL</td>
												<td>{{ totalAdelantos }}</td>
											</tr>
										</tbody>
									</table>
								</b-col>
							</b-row>
							
							<div class="text-center">
								<h2>Gran total: {{ totalSumaProductos }}</h2> 
							</div>
						
						</div>
				  	</div>
				</div>

				<div slot="modal-footer" >
					<div style="left: 1rem;position: absolute;">
						<b-btn  variant="info" type="submit" target="_blank" :href="'/views/V_VentaForm.php?idPreOrden=' + selectProducto.IdPreOrden + '&idCliente=' + selectProducto.IdClienteReserva">
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
import PreOrdenFormDet from '@/components/PreOrdenFormDet'
import ClientesForm from '@/components/ClientesForm'
import Proformas from '@/components/ProformaLista.vue'

export default {
  	mounted () {
		this.getHabitaciones()
	},
	data () {
		return {
			API: config.API,
			show: false,
			showPopover: true,
			habitaciones: [],
			selectCliente: {},
			selectProducto: {},
			vuelo: '',
			fechaReserva: this.$moment().format("YYYY-MM-DD"),
			pisos: [1, 2, 3, 4, 5],
			adelantos: [],
			gastos: [],
			montoTotal: 0,
			IdProforma: null,
			proformaTotal: null,
			codProforma: null,
			profTotHab: 0,
			metodosPago:[{ value: null, text: '-- SELECCIONE --' }, { value: 1, text: 'EFECTIVO' }, { value: 2, text: 'DEPOSITO' }],
			// proformaTotalCard: null,
			// habitacionesPiso: []

			//nuevas funciones
			idMetodoPago: null,
			newDniRuc: '',
			newDniRuc2: '',
			totalManoObraBruta:0,
			totalManoObraMenoMat:0,
			totalManoObraNeta:0,
			totalSumaProductos:0,
			totalGastos:0,
			totalAdelantos: 0,
			options: [{ value: null, text: 'SELECCIONE LA NUEVA HABITACIÓN' }],
			selectHabCambio:null
			}
	},
	computed: {
		...mapState({
            productos: state => state.cart.productos
        }),
/* 		totalAdelantos() {
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
		}, */
	},
	methods: {
		mountedProforma(modal) {
			document.body.appendChild(modal)
		},
		selectProforma(proforma) {
			this.show = true
			this.$nextTick(() => {
				proforma.productos.map(p => {
					this.$refs.preOrdenProductos.productosACargar.push(p)
				})

				this.IdProforma = proforma.IdProforma
			})
		},
		cargarAdelantos() {
			this.axios.get(this.API + 'preorden/cajaybanco', { params: {
				IdPreOrden		: this.selectProducto.IdPreOrden,
				TipoCajaBanco 	: 0
			} }).then(res => {
				this.adelantos = res.data
				this.calculoGastosAdelantos()
			})
		},
		cargarGastos() {
			this.axios.get(this.API + 'preorden/cajaybanco', { params: {
				IdPreOrden		: this.selectProducto.IdPreOrden,
				TipoCajaBanco 	: 1
			} }).then(res => {
				this.gastos = res.data
				this.calculoGastosAdelantos()
			})
		},
		insertarAdelanto() {
			this.axios.get(this.API + 'preorden/venta', { params: {
				IdPreOrden: this.selectProducto.IdPreOrden
			} }).then(res => {
				if(res.data){
					if(res.data.IdDocVenta == null){
						let usuario = sessionStorage.getItem('User')

						if(this.selectProducto.Adelanto <= 0 || this.selectProducto.Adelanto == undefined) {
							this.$toasted.show('Debe insertar un monto')
							return;
						}

						if(this.idMetodoPago==null) {
							this.$toasted.show('Seleccione un metodo de pago')
							return;
						}

						let r = confirm("¿Desea Insertar un adelanto?")

						if (r) {
							this.axios.post(this.API + 'preorden/cajaybanco', {
								producto: this.selectProducto,
								concepto: 'Adelanto Habitación ' + this.selectProducto.Producto + ' - ' + (this.selectProducto.AdelantoDesc || ''),
								IdPreOrden: this.selectProducto.IdPreOrden,
								IdHabitacion: this.selectProducto.IdProducto,
								Adelanto: this.selectProducto.Adelanto,
								Usuario: usuario,
								MetodoPago: this.idMetodoPago
							}).then(res => {
								// this.adelantos.push(res.data)
								this.cargarAdelantos();
								this.selectProducto.Adelanto = 0
								this.selectProducto.AdelantoDesc = ''
								this.idMetodoPago = null
							})
						}
					}else{
						this.$toasted.show("No puede ingresar adelantos, el proyecto ya fue pagada, puede renovarla si se trata del mismo cliente")
						this.getHabitaciones()
					}
				}
			}).catch(err=>{
				this.$toasted.show("Ha ocurrido un error")
			})
		},

		insertarGasto() {
			let usuario = sessionStorage.getItem('User')

			if(this.selectProducto.Gasto <= 0) {
				return;
			}

			let r = confirm("¿Desea Insertar un gasto?")

			if (r) {
				this.axios.post(this.API + 'preorden/cajaybanco/gasto', {
					producto: this.selectProducto,
                    concepto: 'Gasto Proyecto ' + this.selectProducto.Producto + ' - ' + this.selectProducto.GastoDesc,
					IdPreOrden: this.selectProducto.IdPreOrden,
					IdHabitacion: this.selectProducto.IdProducto,
					Gasto: this.selectProducto.Gasto,
					Usuario: usuario
				}).then(res => {
					// this.adelantos.push(res.data)
					this.cargarGastos();
					this.selectProducto.Gasto 		= '' 
					this.selectProducto.Producto 	= '' 
					this.selectProducto.GastoDesc 	= ''
				})
			}
		},

		getHabitacionesPiso (piso) {
			return this.habitaciones.filter(hab => {
				return hab.Piso === piso
			})
		},
		guardarProductos() {
			this.axios.post(this.API + 'preorden/detalle', {
				IdPreOrden: this.selectProducto.IdPreOrden,
				IdHabitacion: this.selectProducto.IdProducto,
				productos: this.$refs.preOrdenProductos.productos,
				IdProforma: this.IdProforma
			}).then(res => {
				this.$toasted.success('Guardado correctamente')
				this.show = false
				this.getHabitaciones()
			})

			
		},
		openCarrito(hab) {
			this.proformaTotal = null
			this.selectProducto = hab
			this.show = true; // Asegúrate de que 'show' sea true para que el v-if del componente hijo se active
			
			this.$refs.modalCarrito.show()
			this.cargarAdelantos()
			this.cargarGastos()
			this.cargarProformaTotal(this.selectProducto.IdProforma)

			// ESTO ES LO NUEVO:
			// Esperamos a que el componente hijo (preOrdenProductos) cargue
			this.$nextTick(() => {
				// Forzamos el cálculo inicial apenas se abre el modal
				this.calcularManoObra();
			})
		},
		cargarProformaTotal(idProforma) {
			if (idProforma) {
				this.axios.get(this.API + 'preorden/proforma', {params: {idProforma: idProforma}}).then(res => {
					this.proformaTotal = res.data.Total
					this.codProforma = res.data.IdProforma
					this.codProforma = res.data.Anio + ' - ' + res.data.Numero
				})
			}
		},
		getHabitaciones() {
			this.axios.get(this.API + 'habitaciones?sortBy=Gen_Producto.Producto').then(res => {
				this.arregloInfo = []
				this.LibresTotal = 0
				this.ReservadasTotal = 0
				this.OcupadasTotal = 0
				this.PagadasTotal = 0
				this.SuciasTotal = 0
				this.habitaciones = res.data
				this.options = []
				this.options.push({ value: null, text: 'SELECCIONE EL NUEVO PROYECTO' })
				this.habitaciones.map(hab=>{
					if(hab.EstadoHabitacion == 1 && hab.EstaSucio == 0){
						this.options.push({ value: hab.IdProducto, text: hab.Producto })
					}
				})
				this.pisos.map(p=>{
					let HabitacionesPiso = this.habitaciones.filter(hab => {
						return hab.Piso === p
					})
					if(HabitacionesPiso.length != 0){
						let libres = 0
						let ocupadas = 0
						let reservadas = 0
						let sucias = 0
						let pagadas = 0

						HabitacionesPiso.map(hab=>{
							if(hab.EstadoHabitacion == 1 && hab.EstaSucio==0 ){
								libres++
							}else if(hab.EstadoHabitacion==2){
								reservadas++
							}
							
							if(hab.EstadoHabitacion==3 && hab.DocVenta != null){
								pagadas++
							}
							
							if(hab.EstadoHabitacion==3){
								ocupadas++
							}

							if(hab.EstadoHabitacion==3 && hab.EstaSucio==1){
								sucias++
							}
							
							if(hab.EstadoHabitacion==1 && hab.EstaSucio==1){
								sucias++
							}
						})
						this.arregloInfo.push({Piso:p,Libres:libres,Reservadas:reservadas,Pagadas:pagadas,Ocupadas:ocupadas,Sucias:sucias})
					}
				})

				this.arregloInfo.map((arr)=>{
					this.LibresTotal += arr.Libres
					this.ReservadasTotal += arr.Reservadas
					this.PagadasTotal += arr.Pagadas
					this.OcupadasTotal += arr.Ocupadas
					this.SuciasTotal += arr.Sucias
				})
	
			}).catch(err=>{
				this.habitaciones = []
				this.arregloInfo = []
				this.$toasted.show("Ocurrio un error")
			})
		},
		liberarForzado(hab){
			this.axios.get(this.API + 'preorden/venta', { params: {
				IdPreOrden: hab.IdPreOrden
			} }).then(res => {
				if(res.data){
					if(res.data.IdDocVenta != null){
						this.$toasted.show("El proyecto  ya tiene venta asociada, puede usar el boton verde para liberar")
						this.getHabitaciones()
					}else{
						this.$swal({
						target: document.getElementById('conten'),
						title: 'Liberación forzada',
						html: 'Se liberará la habitación: <strong> '+hab.Producto+'</strong> ¿Está seguro? <br>'+
						' <div style="text-align:left"><strong>Solo use esta opción en los siguientes casos: </strong> <br> '+
						' <ul><li>Confusión de habitación.</li><li>Cliente erroneo</li><li>Fechas inicio / fin ingresadas erroneas.</li><li>No debe tener adelantos, y si los tiene eliminarlos desde <strong>caja y banco</strong> antes de proceder.</li></ul> '+
						' <strong>Adicional: </strong> <ul><li>Esta opción solo se mostrará cuando no haya ventas asociadas a la habitación.</li> '+
						' <li>Por favor libere el proyecto (botón verde) solo <strong>cuando el cliente abandone el establecimiento y este deacuerdo con su comprobante.</strong></li> </ul> </div>',
						icon: 'warning',
						showCancelButton: true,
						confirmButtonColor: '#3085d6',
						cancelButtonColor: '#d33',
						confirmButtonText: 'Sí, liberar forzadamente',
						cancelButtonText: 'No, atrás'
						}).then((result) => {
							if (result.isConfirmed) {
								this.axios.post(this.API + 'habitaciones/liberar', {
									IdProducto: hab.IdProducto,
									EstaSucio: 0
								}).then(res => {
									this.getHabitaciones()
									this.limpiarPopover()
									this.$toasted.success('La Habitación: ' + hab.Producto + ' está ahora disponible');
									this.showPopover = false
									this.$nextTick(() => {
										this.showPopover = true
									})
								})
							}
						}) 
					}
				}
			}).catch(err=>{
				this.$toasted.show("Ah ocurrido un error")
			})
		},
		getVariant(hab) {
			if (hab.EstadoHabitacion == 2) return 'warning'
			if (hab.EstadoHabitacion == 3) return 'danger'
            if (hab.EstadoHabitacion == 1 && hab.EstaSucio == 1) return 'secondary'
			return 'success'
		},
		reservarHab(hab) {
			if (!this.selectCliente.Cliente) {
				this.$toasted.error('Seleccione un cliente.');
			}
			
			this.axios.post(this.API + 'habitaciones/reservar', {
				IdProducto: hab.IdProducto,
				IdCliente: this.selectCliente.IdCliente,
				FechaReserva: this.fechaReserva,
				Vuelo: this.vuelo
			}).then(res => {
				this.getHabitaciones()
				this.limpiarPopover()
				this.limpiarDespues()
				this.$toasted.success('La Habitación: ' + hab.Producto + ' ha sido reservada');
				this.showPopover = false
				this.$nextTick(() => {
					this.showPopover = true
				})		
			})
		},
		liberarHab(hab) {
			let r = confirm('¿Está seguro que desea liberar el proyecto? Asegurese de haber realizado la venta')

			if (r) {
				this.axios.post(this.API + 'habitaciones/liberar', {
					IdProducto: hab.IdProducto
				}).then(res => {
					this.getHabitaciones()
					this.limpiarPopover()
					this.$toasted.success('El proyecto: ' + hab.Producto + ' está ahora disponible');
					this.showPopover = false
					this.$nextTick(() => {
						this.showPopover = true
					})
				})
			}
		},
		alquilarHab(hab) {
			if (!this.selectCliente.Cliente) {
				this.$toasted.error('Seleccione un cliente.');
			}

			let r = confirm('¿Está seguro que desea empezar el proyecto?')

			if (r) {
				this.axios.post(this.API + 'habitaciones/alquilar', {
					IdProducto: hab.IdProducto,
					IdCliente: this.selectCliente.IdCliente,
					cliente: this.selectCliente
				}).then(res => {
					this.getHabitaciones()
					this.limpiarPopover()
					this.limpiarDespues()
					this.$toasted.success('El proyecto: ' + hab.Producto + ' ha sido iniciado.')
					this.showPopover = false
					this.$nextTick(() => {
						this.showPopover = true
					})
				})
			}
		},
		setCliente(obj, index) {
			if (index >= 0) {
				console.log(this.$refs)
				console.log(index)
				console.log('inputCliente2' + index)
				this.$refs['inputCliente2' + index][0].setValue(obj.Cliente)
				this.newDniRuc=''
			}
			this.selectCliente = obj
		},
		/*focusAutocomplete (ref) {
			this.$refs[ref][0].handleDoubleClick()
		},*/
		limpiarPopover() {
			//this.cliente = {}
			//this.$refs.inputCliente.setValue('')
		},
		limpiarDespues() {
			this.selectCliente = {}
			this.selectProducto = {}
		},
		formatFecha(fecha) {
			return this.$moment(fecha, 'YYYY-MM-DD hh:mm:ss').format('YYYY-MM-DD')
		},
		imprimir() {
			document.getElementById("fechaHoraImp").innerHTML = this.$moment().format("YYYY-MM-DD HH:mm:ss");
			var elem = "contentModal";
			var cssHead = '<head><link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"></head>';

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
		sumProductos() {
			let total = 0
			if (this.$refs.preOrdenProductos) {
				total = this.$refs.preOrdenProductos.sumProductos + this.totalGastos - this.totalAdelantos
			}
			return Math.abs(total).toFixed(2)
		},
/* 		totManoObraBruta() {
			let total = 0
			if (this.$refs.preOrdenProductos) {
				total = this.$refs.preOrdenProductos.manoDeObraBruta
			}
			return total?total.toFixed(2):0
		}, */
/* 		totManoObraMenoMat() {
			let total = 0
			if (this.$refs.preOrdenProductos) {
				total = this.$refs.preOrdenProductos.manoDeObraMenosMat
			}
			return total?total.toFixed(2):0
		}, */
/* 		totManoObraNeta() {
			let total = 0
			if (this.$refs.preOrdenProductos) {
				total = this.$refs.preOrdenProductos.manoDeObraMenosMat - this.totalGastos
			}
			return total?total.toFixed(2):0
		}, */
		setHuesped(obj,index){
			if (index >= 0) {
				console.log('inputCliente3' + index)
				this.$refs['inputCliente3' + index][0].setValue(obj.Cliente)
				this.newDniRuc2=''
			}
			this.selectHuesped = obj
		},
		selectDefectoCambio(){
			this.selectHabCambio = null
		},
				cambiarHab(hab){
			console.log(hab);
			let habCambio = this.selectHabCambio;
			if(habCambio == null){
				this.$toasted.show('Seleccione la habitacion nueva a cambiar')
				return
			}

			this.showPopover = false
			this.$nextTick(() => {
				this.showPopover = true
			})

			this.axios.get(this.API + 'preorden/estadohab', { params: {
				IdProducto: hab.IdProducto,
			} }).then(res => {
				if(res.data){
					if(res.data.EstadoHabitacion == 3 && res.data.IdPreOrden != null){
						this.$swal({
						target: document.getElementById('conten'),
						title: '<div style="text-align:left; font-size:18.5px; text-align:center">El Cliente <strong>'+ hab.Cliente+'</strong> se cambiará a: <br> <strong>'+ this.options.find((opc)=>opc.value == habCambio).text+'</strong>, está seguro?</div>',
						html: ' <div style="text-align:left"><strong>Información: </strong> <br> '+
						' <ul><li>Sí tiene una venta, se cambiará el detalle <strong>(Nombre de la habitación)</strong>. El <strong>precio</strong> se mantendrá.</li><li>Sí el precio de la nueva habitación es mayor a la que estaba y tiene una venta se tiene que <strong>anular la venta</strong>. Posteriormente agregar la diferencia y realizar una nueva venta.</li><li>Toda la información pasará a la nueva habitación.</li></ul></div>',
						icon: 'question',
						showCancelButton: true,
						confirmButtonColor: '#28a745',
						cancelButtonColor: '#d33',
						confirmButtonText: 'Sí, cambiar habitación',
						cancelButtonText: 'No, atrás'
						}).then((result) => {
							if (result.isConfirmed) {
								this.axios.post(this.API + 'habitaciones/cambiar', {
									IdCliente:hab.IdCliente,
									IdPreOrden:hab.IdPreOrden,
									IdProductoAnterior:hab.IdProducto,
									IdProductoNuevo:habCambio,
									FechaAlquiler:hab.FechaAlquiler,
									FechaAlquilerFin:hab.FechaAlquilerFin
								}).then(res => {
									if(res.data.updated==true){
										this.getHabitaciones()
										this.limpiarPopover()
										this.$toasted.info('El cliente '+hab.Cliente+' se cambió a: ' + res.data.Producto);
										this.showPopover = false
										this.$nextTick(() => {
											this.showPopover = true
										})
									}else{
										if(res.data.msg){
											this.$toasted.show(res.data.msg)
											this.getHabitaciones()
											this.limpiarPopover()
											this.showPopover = false
											this.$nextTick(() => {
												this.showPopover = true
											})
										}
									}
								})
							}
						})
					}else{
						this.$toasted.info("La habitacion ya no está disponible se actualizará la lista")
						this.getHabitaciones()
					}
				}
			}).catch(err=>{
				this.$toasted.show("Ah ocurrido un error")
			})
		},
		calcularManoObra(e){
			let pManoObra = e.find(p => p.CodigoBarra === 'MANODEOBRA');
      		let precio = pManoObra ? pManoObra.Precio : 0;
			let cantidad = pManoObra ? (pManoObra.Cantidad==undefined?0:pManoObra.Cantidad) :0;
			this.totalManoObraBruta = Number(precio*cantidad)
			this.totalManoObraMenoMat = Number(precio*cantidad)
			e.map((preorden=>{
				if(preorden.EsManoDeObra==1){
					this.totalManoObraMenoMat = this.totalManoObraMenoMat - (preorden.Precio*preorden.Cantidad)
				}
			}))
			this.calculoGastosAdelantos()
		},
		calculoGastosAdelantos(){
			let sumatoriaGas = 0
			this.gastos.map(ad => {
				sumatoriaGas += parseFloat(ad.Importe)
			})
			this.totalGastos = sumatoriaGas
			this.totalManoObraNeta = this.totalManoObraMenoMat - this.totalGastos
			

			let sumatoriaAde = 0
			this.adelantos.map(ad => {
				if (ad.Tipo) {
					sumatoriaAde -= parseFloat(ad.Importe)
				} else {
					sumatoriaAde += parseFloat(ad.Importe)
				}
			})
			this.totalAdelantos = sumatoriaAde

			if (this.$refs.preOrdenProductos) {
			  this.totalSumaProductos =Math.abs(this.$refs.preOrdenProductos.sumProductos + this.totalGastos - this.totalAdelantos).toFixed(2)  
			}
			
		},
	},
	components: {
		PreOrdenFormDet,
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
	.horaFechaImpreso{
 		display: block;
	}
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
.horaFechaImpreso{
 	display: none;
}
</style>

