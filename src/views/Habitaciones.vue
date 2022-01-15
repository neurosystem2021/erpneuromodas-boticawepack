<template>
    <div>
      <b-container class="bv-example-row">
		  <br>
		  <div>
			<div class="float-left">
				<b-button-group>
					<b-button variant="primary" @click="comenzarLimpieza">Comenzar Limpieza</b-button>
				</b-button-group>
				<b-button-group>
					<b-button variant="success" @click="getHabitaciones">Actualizar Habitaciones</b-button>
				</b-button-group>
			</div>
			<div class="float-right">
				<b-button-group>
					<b-button variant="secondary" @click="imprimirHabitacionesSucias">Imprimir Habitaciones Sucias</b-button>
				</b-button-group>
			</div>
		  </div>
		  <br>
		  <div class="piso" v-for="(pi, i) in pisos" :key="i">
			<div v-if="getHabitacionesPiso(pi).length">
			  <br>
			  	<b-alert show class="text-center"><b>PISO {{ pi }}</b></b-alert>
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
								
								<h5>S/. {{ hab.PrecioContado | moneda }}</h5>
								<ul class="detalle" v-if="hab.EstadoHabitacion == 2">
									<li><b style="color:white">{{ hab.Cliente }}</b><li>
									<li><b>FECHA: {{ formatFecha(hab.FechaReserva) }}</b></li>
									<li v-if="hab.vuelo"><br>Vuelo: {{ hab.Vuelo }}</li>
								</ul>
								<ul class="detalle" v-if="hab.EstadoHabitacion == 3">
									<li><b style="color:white">{{ hab.Cliente }}</b><li>
									<li><b>Inicio: {{ hab.FechaAlquiler }}</b></li>
									<li><b>Fin: {{ hab.FechaAlquilerFin }}</b></li>
									<li style="margin-top:5px"><b style="font-size:15px">Total Personas:</b>  <b-badge variant="light" style="font-size:15px">{{ hab.Hospedados}}</b-badge> <b-button :id="i + 'popover-agregar' + index" size="sm" variant="light" style="margin-top:-5px"><i class="fas fa-user-plus"></i></b-button></li>
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
										<br />
										<label>Lugar Procedencia:</label>
										<b-input-group>
											<b-form-input v-model="selectHuesped.LugarProcedencia"
												type="text"
												placeholder="Lugar Procedencia"></b-form-input>
										</b-input-group>
										<br>
										<label>Medio de transporte:</label>
										<b-input-group>
											<b-form-input v-model="selectHuesped.MedioTransporte"
												type="text"
												placeholder="Medio de transporte"></b-form-input>
										</b-input-group>
										<br>
										<label>Próximo destino:</label>
										<b-input-group>
											<b-form-input v-model="selectHuesped.ProximoDestino"
												type="text"
												placeholder="Próximo destino"></b-form-input>
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
									<b-btn :id="i + 'popover-alquilar' + index" variant="danger" size="sm">Alquilar</b-btn>	
									<b-popover  v-if="showPopover" :target="i + 'popover-alquilar' + index" title="Alquilar Habitación" triggers="click">
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

										<b-button variant="success" size="sm" @click="alquilarHab(hab,index)">Alquilar Habitación</b-button>	
									</b-popover>
									<b-button v-if="hab.IdPreOrden && hab.EstadoHabitacion == 3" @click="openCarrito(hab)" variant="primary" size="sm">+</b-button>
								</div>
						</b-card>
					</b-col>

		</b-row>
		  </div>
      </b-container>

		<b-form @submit.prevent="guardarProductos" @keydown.enter.prevent="()=>{}" >
			<b-modal v-model="show" size="lg" id="carrito" :title="'Cesta de compras - Habitación ' + selectProducto.Producto " ref="modalCarrito">
				<div><b>Titular: {{ selectProducto.DniRuc }} - {{ selectProducto.Cliente }}</b></div>
				<div>Inicio: {{ selectProducto.FechaAlquiler }}</div>
				<div>Fin: {{ selectProducto.FechaAlquilerFin }} </div>
				<div class="text-center">
					Nueva Fecha Fin: 
					<input class="form-control" style="width: 210px; display: inline" type="datetime-local" v-model="nuevaFechaFin" />
					<button @click="guardarNuevaFechaFin" style="width: 100px" type="button" class="form-control btn btn-primary">Actualizar</button>
				</div>
				<hr>
				<b-row>
					<b-col md="12" style="display: flex; align-items: center;"><b style="margin-right:10px">Huespedes secundarios:</b></b-col>
					<b-col md="12">
						<table>
							<tbody>
								<tr v-for="(prod, index) in HuespedesSec" :key="index">
									<td><b>{{ prod.DniRuc }}</b></td>
									<td> - {{ prod.Cliente }} </td>
									<td><b-button variant="danger" size="sm" @click="quitarHuesped(prod.IdHuespedDet,prod.Cliente)"><i class="fas fa-times fa-lg"></i></b-button></td>
								</tr>
							</tbody>
						</table>
					</b-col>
				</b-row>
				<hr>
				<b-row>
					<b-col md="8">
						<pre-orden-form-det v-if="show"
						:id-preorden="selectProducto.IdPreOrden"
						:id-producto="selectProducto.IdProducto"
						ref="preOrdenProductos"
						></pre-orden-form-det>
					</b-col> 
					<b-col md="4">
						<label><b>Adelantos</b></label>
                        <b-form-input v-model="selectProducto.Adelanto"
                                size="sm"
                                type="number"
                                placeholder="Adelanto S/."></b-form-input>
      					<b-input-group>
							<b-form-input v-model="selectProducto.AdelantoDesc"
                                size="sm"
                                type="text"
                                placeholder="Descripción"></b-form-input>
							<b-button @click="insertarAdelanto" variant="primary" size="sm">+</b-button>
							<b-form-text id="inputLiveHelp">Adelanto se reflejará en Caja y Banco.</b-form-text>
      					</b-input-group>

						<br />
						<table class="table b-table table-sm table-hover" style="cursor: pointer;">
					      <thead>
					        <tr>
					          <th>Fecha</th>
                              <th>Concepto</th>
					          <th>Cantidad</th>
					        </tr>
					      </thead>
					      <tbody>
					      	<tr v-for="(ade, index) in adelantos" :key="index" @click="() => openImprimir(ade)">
					      		<td>{{ ade.FechaDoc }}</td>
                                <td>{{ ade.Concepto.split(" - ").pop() }}</td>
					      		<td>{{ ade.Importe }}</td>
					      	</tr>
					      	<tr class="bg-info text-white">
                                <td colspan="2">TOTAL</td>
					      		<td>{{ totalAdelantos }}</td>
					      	</tr>
					      </tbody>
					  	</table>
					</b-col>
				</b-row>

				<div slot="modal-footer" >
					<div style="left: 1rem;position: absolute;">
						<b-btn  variant="info" type="submit" target="_blank" :href="'/views/V_VentaForm.php?idPreOrden=' + selectProducto.IdPreOrden + '&idCliente=' + selectProducto.IdClienteReserva">
						Pagar Ahora
						</b-btn>
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
import config from '@/store/config.js'
import PreOrdenFormDet from '@/components/PreOrdenFormDet'
import ClientesForm from '@/components/ClientesForm'

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
			nuevaFechaFin: '',
			// habitacionesPiso: []
			newDniRuc: '',
			newDniRuc2: '',
			HuespedesSec:[],
			selectHuesped: {},
		}
	},
	computed: {
		totalAdelantos() {
			let sumatoria = 0
			this.adelantos.map(ad => {
				sumatoria += parseFloat(ad.Importe)
			})

			return sumatoria;
		}
	},
	methods: {
		comenzarLimpieza(){
			let r = confirm('¿Se iniciará la limpieza, está seguro?')
			if (r) {
				this.axios.post(this.API + 'habitaciones/iniciar/limpieza', {
				}).then(res => {
					if (res.data.updated) {
						this.getHabitaciones()
						this.$toasted.success('Se ha comenzado la limpieza')
					}
				})
			}
		},
		imprimirHabitacionesSucias(){
			var left = (screen.width/2)-(800/2);
			var top = (screen.height/2)-(800/2);
			var fechaHora = this.$moment().format("YYYY-MM-DD HH:mm:ss");
			var schema = "<div style='text-align:center; font-family: monospace;font-weight:  bold;' ><h1>Mi Hotel<h1><h3>Reporte de Habitaciones Sucias</h3><label style='font-weight:bold'>Fecha y Hora: "+fechaHora+"</label><hr><table><thead><tr><th>HABITACIÓN</th><th>PISO</th><th>OCUPADO</th><th>LIMPIEZA</th></tr></thead><tbody>";
			var habita = "";
			for (let key in [1,2,3,4,5,6,7,8,9,10,11]) {
				this.habitaciones.forEach(ha => {
				if(ha.EstaSucio==1 && ha.Piso==key){
					habita += "<tr style='font-size:0.8rem'><td style='border-top: black 1px dashed;'>"+ha.Producto+"</td><td style='text-align: center; vertical-align: middle; border-top: black 1px dashed;'>"+ha.Piso+"</td><td style='text-align: center; vertical-align: middle; font-weight:bold; border-top: black 1px dashed;'>"+(ha.EstadoHabitacion==3?'SI':'NO')+"</td><td style='border: black 1px dashed;'></td></tr>"; 
				}
				}) 
			}
			var schemaFinal = "</tbody></table></div>";
			var myWindow = window.open ('', 'IMPRIMIR COMANDA', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=800, height=800, top='+top+', left='+left);
			myWindow.document.write(schema+habita+schemaFinal);
			myWindow.document.close();
			myWindow.focus();
			myWindow.print();
			myWindow.close();
		},
		limpiarHabitacion(IdProducto,Producto){

			let r = confirm('¿Se limpió correctamente la: '+Producto+"?")
			if (r) {
				this.axios.post(this.API + 'habitaciones/limpiar', {
					IdProducto: IdProducto,
				}).then(res => {
					if (res.data.updated) {
						this.$toasted.success('¡Limpiado!')
						this.getHabitaciones()
					}
				})
			}
		},
		guardarNuevaFechaFin() {
			this.axios.post(this.API + 'habitaciones/fecha', {
				IdProducto: this.selectProducto.IdProducto,
				fechaAlquilerFin: this.nuevaFechaFin
			}).then(res => {
				if (res.data.updated) {
					this.selectProducto.FechaAlquilerFin = this.nuevaFechaFin.replace('T', ' ')
					this.$toasted.success('Fecha fin actualizad')
				}
			
			})
		},
		openImprimir(adelanto) {
			window.open('/imprimir/index.php?IdCajaBanco=' + adelanto.IdCajaBanco, '_blank');
		},
		cargarAdelantos() {
			this.axios.get(this.API + 'preorden/cajaybanco', { params: {
				IdPreOrden: this.selectProducto.IdPreOrden
			} }).then(res => {
				this.adelantos = res.data
			})

		},
		insertarAdelanto() {
			let usuario = sessionStorage.getItem('User')

			if(this.selectProducto.Adelanto <= 0) {
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
					Usuario: usuario
				}).then(res => {
					// this.adelantos.push(res.data)
					this.cargarAdelantos();
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
				productos: this.$refs.preOrdenProductos.productos
			}).then(res => {
				this.$toasted.success('Guardado correctamente')
				this.show = false
			})

			
		},
		openCarrito(hab) {
			this.HuespedesSec=[]
			this.axios.get(this.API + 'habitacion/huespedes/det', {
				params: {idPreOrden: hab.IdPreOrden}
			}).then(res => {
				this.selectProducto = hab
				this.HuespedesSec=res.data
				this.$refs.modalCarrito.show()
				this.cargarAdelantos()
			}).catch(err => {
				this.selectProducto = {}
				console.log(err)
			})
		},
		cargarHuespedes(){
			this.axios.get(this.API + 'habitacion/huespedes/det', {
				params: {idPreOrden: this.selectProducto.IdPreOrden}
			}).then(res => {
				this.HuespedesSec=res.data
			}).catch(err => {
				this.selectProducto = {}
				console.log(err)
			})
		},
		quitarHuesped(IdHuespedDet, Cliente){
			let r = confirm('¿Se quitará '+Cliente+' de la habitación, está seguro?')
      		if (r) {
			this.axios.post(this.API + 'habitacion/huespedes/delete', {
				IdHuespedDet: IdHuespedDet,
			}).then(res => {
				this.cargarHuespedes();
				this.$toasted.success('Huesped quitado correctamente')
			}) 
			}
		},
		agregarHuesped(hab,index){
			if(this.$refs['inputCliente3' + index][0].$refs.input.value == ''){
				this.selectHuesped.IdCliente = null
				this.$toasted.error('Seleccione un cliente')
				return
			}
			console.log(hab);
			this.selectHuesped.FechaAlquilerInicio = hab.FechaAlquiler
			this.selectHuesped.FechaAlquilerFin = hab.FechaAlquilerFin
			this.axios.post(this.API + 'habitacion/agregar/huesped', {
				IdPreOrden: hab.IdPreOrden,
				IdCliente: this.selectHuesped.IdCliente,
				cliente: this.selectHuesped,
			}).then(res => {
				if(res.data.insertId){
				this.getHabitaciones();
				this.limpiarDespues()
				this.showPopover = false
				this.$nextTick(() => {
					this.showPopover = true
				})	
				this.$toasted.success('Huesped agregado correctamente')
				}

			}) 
		},
		setHuesped(obj,index){
			if (index >= 0) {
				console.log('inputCliente3' + index)
				this.$refs['inputCliente3' + index][0].setValue(obj.Cliente)
				this.newDniRuc2=''
			}
			this.selectHuesped = obj
		},
		getHabitaciones() {
			this.axios.get(this.API + 'habitaciones?sortBy=Gen_Producto.Producto').then(res => {
				this.habitaciones = res.data
				/* this.pisos.forEach(piso => {
					this.habitacionesPiso[piso] = this.getHabitacionesPiso(piso)
				}) */
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
			let r = confirm('¿Está seguro que desea liberar la habitación? Asegurese de haber realizado la venta')

			if (r) {
				this.axios.post(this.API + 'habitaciones/liberar', {
					IdProducto: hab.IdProducto
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
		},
		alquilarHab(hab,index) {
			/*
			if(this.$refs['inputCliente3' + index][0].$refs.input.value == ''){
				this.IdHuesped1 = null
			}
			if(this.$refs['inputCliente4' + index][0].$refs.input.value == ''){
				this.IdHuesped2 = null
			}*/

			if (!this.selectCliente.Cliente) {
				this.$toasted.error('Seleccione un cliente.');
			}

			let r = confirm('¿Está seguro que desea alquilar la habitación?')

			if (r) {
				this.axios.post(this.API + 'habitaciones/alquilar', {
					IdProducto: hab.IdProducto,
					IdCliente: this.selectCliente.IdCliente,
					cliente: this.selectCliente,
				}).then(res => {
					this.getHabitaciones()
					this.limpiarPopover()
					this.limpiarDespues()
					this.$toasted.success('La Habitación: ' + hab.Producto + ' ha sido alquilada')
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
			this.selectHuesped = {}
		},
		formatFecha(fecha) {
			return this.$moment(fecha, 'YYYY-MM-DD hh:mm:ss').format('YYYY-MM-DD')
		}
	},
	components: {
		PreOrdenFormDet,
		ClientesForm,
	}
}
</script>
<style scoped>
h5 {
	font-size: 1.2rem;
	color: #ffffff;
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
.popover{
    max-width: 420px !important;
}
#modal1{
overflow-y: scroll;
}
#modal2{
overflow-y: scroll;
}
#modal3{
overflow-y: scroll;
}



</style>

