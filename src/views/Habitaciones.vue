<template>
    <div>
      <b-container class="bv-example-row">
		  <div class="piso" v-for="(pi, i) in pisos" :key="i">
			<div v-if="getHabitacionesPiso(pi).length">
			  <br>
			  	<b-alert show class="text-center"><b>PISO {{ pi }}</b></b-alert>
			  <br>
			</div>
        	<b-row>

					<b-col class="habitacion" md="3" v-for="(hab, index) in getHabitacionesPiso(pi)" :key="index">
						<b-card :bg-variant="getVariant(hab)"
										class="text-center">
										
								<h5>{{ hab.Producto }}</h5>
								<h5>S/. {{ hab.PrecioContado | moneda }}</h5>
								<ul class="detalle" v-if="hab.EstadoHabitacion == 2">
									<li><b>{{ hab.Cliente }}</b> </li>
									<li><b>FECHA: {{ formatFecha(hab.FechaReserva) }}</b></li>
									<li><br>Vuelo: {{ hab.Vuelo }}</li>
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
									<b-popover  v-if="showPopover" :target="i + 'popover-alquilar' + index" title="Alquilar Habitación">
										<label>Seleccione un cliente:</label>
										<b-input-group>
											<autocomplete
													required="required"
													:ref="'inputCliente2' + index"
													:url="API + 'clientes'"
													anchor="ClienteDniRuc"
													label="writer"
													placeholder=""
													:on-select="setCliente" 
													:classes="{input: 'form-control'}">
											</autocomplete>
											<b-input-group-append>
												<!--<proveedores-tabla></proveedores-tabla>-->
												<clientes-form @onAddCliente="(obj) => { setCliente(obj, index) }"></clientes-form>
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
												type="date"
												placeholder="Fecha Inicio"></b-form-input>
										</b-input-group>
										<br>
										<label>Fecha Fin:</label>
										<b-input-group>
											<b-form-input v-model="selectCliente.FechaAlquilerFin"
												type="date"
												placeholder="Fecha Fin"></b-form-input>
										</b-input-group>
										<br>

										<b-button variant="success" size="sm" @click="alquilarHab(hab)">Alquilar Habitación</b-button>	
									</b-popover>
									<b-button v-if="hab.IdPreOrden" @click="openCarrito(hab)" variant="primary" size="sm">+</b-button>
								</div>
						</b-card>
					</b-col>

		</b-row>
		  </div>
      </b-container>

		<b-form @submit.prevent="guardarProductos" @keydown.enter.prevent="()=>{}" >
			<b-modal v-model="show" size="lg" id="carrito" :title="'Cesta de compras - Habitación ' + selectProducto.Producto " ref="modalCarrito">
				<div class="text-center"><b>{{ selectProducto.DniRuc }} - {{ selectProducto.Cliente }}</b></div>
				<br>
				<br>
				
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
      					<b-input-group>
							<b-form-input v-model="selectProducto.Adelanto"
								size="sm"
								type="number"
								placeholder="Adelanto S/."></b-form-input>
							<b-button @click="insertarAdelanto" variant="primary" size="sm">+</b-button>
							<b-form-text id="inputLiveHelp">Adelanto se reflejará en Caja y Banco.</b-form-text>
      					</b-input-group>

						<br />
						<table class="table b-table table-sm">
					      <thead>
					        <tr>
					          <th>Fecha</th>
					          <th>Cantidad</th>
					        </tr>
					      </thead>
					      <tbody>
					      	<tr v-for="(ade, index) in adelantos" :key="index">
					      		<td>{{ ade.FechaDoc }}</td>
					      		<td>{{ ade.Importe }}</td>
					      	</tr>
					      	<tr class="bg-info text-white">
					      		<td>TOTAL</td>
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
			adelantos: []
			// habitacionesPiso: []
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
		cargarAdelantos() {
			this.axios.get(this.API + 'preorden/cajaybanco', { params: {
				IdPreOrden: this.selectProducto.IdPreOrden
			} }).then(res => {
				this.adelantos = res.data
			})

		},
		insertarAdelanto() {
			if(this.selectProducto.Adelanto <= 0) {
				return;
			}

			let r = confirm("¿Desea Insertar un adelanto?")

			if (r) {
				this.axios.post(this.API + 'preorden/cajaybanco', {
					producto: this.selectProducto,
					IdPreOrden: this.selectProducto.IdPreOrden,
					IdHabitacion: this.selectProducto.IdProducto,
					Adelanto: this.selectProducto.Adelanto
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
			this.selectProducto = hab
			this.$refs.modalCarrito.show()
			this.cargarAdelantos()
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
		alquilarHab(hab) {
			if (!this.selectCliente.Cliente) {
				this.$toasted.error('Seleccione un cliente.');
			}

			let r = confirm('¿Está seguro que desea alquilar la habitación?')

			if (r) {
				this.axios.post(this.API + 'habitaciones/alquilar', {
					IdProducto: hab.IdProducto,
					IdCliente: this.selectCliente.IdCliente,
					cliente: this.selectCliente
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
		}
	},
	components: {
		PreOrdenFormDet,
		ClientesForm
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

