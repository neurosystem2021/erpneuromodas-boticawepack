<template>
    <div>
      <b-container class="bv-example-row">
        <b-row>
					<b-col md="3" v-for="(hab, index) in habitaciones" :key="index">
						<b-card :bg-variant="getVariant(hab)"
										text-variant="white"
										class="text-center">
								<h1>{{ hab.Producto }}</h1>
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

									<b-button v-b-modal.carrito variant="primary" size="sm">+</b-button>
								</div>
						</b-card>
					</b-col>
				</b-row>
      </b-container>

		<b-modal id="carrito" title="Cesta de compras">
			<pre-orden-form-det 
				:id-preorden="1" 
				ref="productoDet"
				></pre-orden-form-det>
		</b-modal>
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
			habitaciones: [],
			selectCliente: {},

			fechaReserva: this.$moment().format("YYYY-MM-DD")
		}
	},
	methods: {
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
			let r = confirm('¿Está seguro que desea liberar la habitación?')

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
</style>

