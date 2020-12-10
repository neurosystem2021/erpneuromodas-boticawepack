<template>
    <div>
      <b-container class="bv-example-row">
		  <div class="piso" v-for="(pi, i) in pisos" :key="i">
			<div v-if="getHabitacionesPiso(pi).length">
			  <br>
			  	<b-alert show class="text-center"><b>{{ pi === 1 ? 'Huánuco' : 'Pucallpa' }}</b></b-alert>
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
									<li v-if="hab.vuelo"><br>Vuelo: {{ hab.Vuelo }}</li>
								</ul>
								<ul class="detalle" v-if="hab.EstadoHabitacion == 3">
									<li><b>FECHA: {{ hab.FechaAlquiler }} - {{ hab.FechaAlquilerFin }}</b></li>
								</ul>
								<div slot="footer">
									<b-btn @click="liberarHab(hab)" variant="success" size="sm">Liberar</b-btn>	
									<!--<b-btn :id="i + 'popover-reservar' + index" variant="warning" size="sm">Reservar</b-btn>-->
									<!--<b-popover v-if="showPopover"  :target="i + 'popover-reservar' + index" title="Reservar Habitación">
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
									</b-popover>-->
									<b-btn :id="i + 'popover-alquilar' + index" variant="danger" size="sm">Empezar</b-btn>	
									<b-popover  v-if="showPopover" :target="i + 'popover-alquilar' + index" title="Empezar Proyecto">
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
										<!--<br />
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
										</b-input-group>-->
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

										<b-button variant="success" size="sm" @click="alquilarHab(hab)">Empezar Proyecto</b-button>	
									</b-popover>
									<b-button v-if="hab.IdPreOrden && hab.EstadoHabitacion == 3" @click="openCarrito(hab)" variant="primary" size="sm">+</b-button>
								</div>
						</b-card>
					</b-col>

		</b-row>
		  </div>
      </b-container>

		<b-form @submit.prevent="guardarProductos" @keydown.enter.prevent="()=>{}" >
			<b-modal v-model="show" size="lg" id="carrito" :title="'Cesta de compras - Proyecto ' + selectProducto.Producto " ref="modalCarrito">

				<div id="contentModal" >
				  	<div class="row">
						<div id="headerCamiones" class="col-md-12">

							<div class="text-center"><b>{{ selectProducto.DniRuc }} - {{ selectProducto.Cliente }}</b></div>
							<br>
							<br>
							
							<b-row>
								<b-col md="8" id="productosProduccion">
									<pre-orden-form-det v-if="show"
									:id-preorden="selectProducto.IdPreOrden"
									:id-producto="selectProducto.IdProducto"
									ref="preOrdenProductos"
									></pre-orden-form-det>
								</b-col> 
								<b-col md="4">

									<label><b>Gastos</b></label>
									<b-form-input v-model="selectProducto.Gasto"
											size="sm"
											type="number"
											placeholder="Gasto S/."></b-form-input>
									<b-input-group>
										<b-form-input v-model="selectProducto.GastoDesc"
											size="sm"
											type="text"
											placeholder="Descripción"></b-form-input>
										<b-button @click="insertarGasto" variant="primary" size="sm">+</b-button>
										<b-form-text id="inputLiveHelp">Los gastos se reflejará en Caja y Banco.</b-form-text>
									</b-input-group>

									<hr />

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
			gastos: []
			// habitacionesPiso: []
		}
	},
	computed: {
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
		}
	},
	methods: {
		mountedProforma(modal) {
			document.body.appendChild(modal)
		},
		selectProforma(idCliente, proforma) {
			this.show = true
			this.$nextTick(() => {
				proforma.productos.map(p => {
					
					this.$refs.preOrdenProductos.productosACargar.push(p)
				})
				console.log(this.$refs.preOrdenProductos.productosACargar)
			})
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
                    concepto: 'Adelanto Proyecto ' + this.selectProducto.Producto + ' - ' + this.selectProducto.AdelantoDesc,
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
		}
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
}
</style>
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

