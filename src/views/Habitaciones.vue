<template>
    <div>
      <b-container class="bv-example-row">
        <b-row>
					<b-col class="habitacion" md="3" v-for="(hab, index) in habitaciones" :key="index">
						<b-card :bg-variant="getVariant(hab)"
										text-variant="white"
										class="text-center">
										
								<h1>{{ hab.Producto }}</h1>
								<ul class="detalle" v-if="hab.EstadoHabitacion == 2">
									<li><b>{{ hab.Cliente }}</b> </li>
									<li><b>FECHA: {{ formatFecha(hab.FechaReserva) }}</b></li>
								</ul>
								<div slot="footer">
									<b-btn @click="liberarHab(hab)" variant="success" size="sm">Liberar</b-btn>	
				
									<b-btn :id="'popover-reservar' + index" variant="warning" size="sm">Reservar</b-btn>	
									<b-popover :target="'popover-reservar' + index" title="Reservar Habitación" ref="popover">
										<label>Seleccione un cliente:</label>
										<autocomplete
												required="required"
												:ref="'inputCliente' + index"
												:url="API + 'clientes'"
												anchor="ClienteDniRuc"
												label="writer"
												placeholder=""
												:on-select="setCliente" 
												:onFocus="focusAutocomplete.bind(null, 'inputCliente' + index)"
												:classes="{input: 'form-control'}">
										</autocomplete>
										<label for="inputLive">Fecha Reserva:</label>
										<b-form-input v-model="fechaReserva"
											type="date"></b-form-input>
										<b-button variant="success" size="sm" @click="reservarHab(hab)">Reservar Habitación</b-button>	
									</b-popover>

									<b-btn :id="'popover-alquilar' + index" variant="danger" size="sm">Alquilar</b-btn>	
									<b-popover :target="'popover-alquilar' + index" title="Alquilar Habitación" ref="popover">
										<label>Seleccione un cliente:</label>
										<autocomplete
												required="required"
												:ref="'inputCliente2' + index"
												:url="API + 'clientes'"
												anchor="ClienteDniRuc"
												label="writer"
												placeholder=""
												:on-select="setCliente" 
												:onFocus="focusAutocomplete.bind(null, 'inputCliente2' + index)"
												:classes="{input: 'form-control'}">
										</autocomplete>
										<b-button variant="success" size="sm" @click="alquilarHab(hab)">Alquilar Habitación</b-button>	
									</b-popover>

									<b-button v-if="hab.IdPreOrden" @click="openCarrito(hab)" variant="primary" size="sm">+</b-button>
								</div>
						</b-card>
					</b-col>
				</b-row>
      </b-container>

		<b-form @submit.prevent="guardarProductos" @keydown.enter.prevent="()=>{}" >
			<b-modal v-model="show" id="carrito" :title="'Cesta de compras - Habitación ' + selectProducto.Producto " ref="modalCarrito">
				<b>{{ selectProducto.DniRuc }} - {{ selectProducto.Cliente }}</b>
				<br>
				<br>
				<pre-orden-form-det v-if="show"
					:id-preorden="selectProducto.IdPreOrden"
					:id-producto="selectProducto.IdProducto"
					ref="preOrdenProductos"
					></pre-orden-form-det>
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

export default {
  	mounted () {
		this.getHabitaciones()
	},
	data () {
		return {
			API: config.API,
			show: false,
			habitaciones: [],
			selectCliente: {},
			selectProducto: {},
			fechaReserva: this.$moment().format("YYYY-MM-DD")
		}
	},
	methods: {
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
		},
		getHabitaciones() {
			this.axios.get(this.API + 'habitaciones').then(res => {
				this.habitaciones = res.data
			})
		},
		getVariant(hab) {
			if (hab.EstadoHabitacion == 2) return 'warning'
			if (hab.EstadoHabitacion == 3) return 'danger'
			return 'success'
		},
		reservarHab(hab) {
			this.axios.post(this.API + 'habitaciones/reservar', {
				IdProducto: hab.IdProducto,
				IdCliente: this.selectCliente.IdCliente,
				FechaReserva: this.fechaReserva
			}).then(res => {
				this.getHabitaciones()
				this.limpiarPopover()
				this.$toasted.success('La Habitación: ' + hab.Producto + ' ha sido reservada');				
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
				})
			}
		},
		alquilarHab(hab) {
			let r = confirm('¿Está seguro que desea alquilar la habitación?')

			if (r) {
				this.axios.post(this.API + 'habitaciones/alquilar', {
					IdProducto: hab.IdProducto,
					IdCliente: this.selectCliente.IdCliente
				}).then(res => {
					this.getHabitaciones()
					this.limpiarPopover()
					this.$toasted.success('La Habitación: ' + hab.Producto + ' ha sido alquilada')
				})
			}
		},
		setCliente(obj) {
			this.selectCliente = obj
		},
		focusAutocomplete (ref) {
			this.$refs[ref][0].handleDoubleClick()
		},
		limpiarPopover() {
			//this.cliente = {}
			//this.$refs.inputCliente.setValue('')
		},
		formatFecha(fecha) {
			return this.$moment(fecha, 'YYYY-MM-DD hh:mm:ss').format('YYYY-MM-DD')
		}
	},
	components: {
		PreOrdenFormDet
	}
}
</script>
<style scoped>
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
}
</style>

