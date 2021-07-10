<template>
    <div>
        <b-container fluid style="width: calc(100% - 50px)" class="bv-example-row">
            <div class="ordenes">
                <b-row style=" background: #EEEEEE;">
                    <b-col size="6" style="position: relative; float: left; height:100vh; overflow-y: scroll; background: #EEEEEE;">
                        <b-row>
                            <b-col md="12" class="text-center">
                            <h1>Lista de Ordenes</h1>
                            </b-col>
                        </b-row>
                        <b-row style="justify-content:space-evenly">
							<b-col cols="auto" style="width:190px;" v-for="(orden,i) in ordenes" :key="i" >
								<b-card :header="orden.Mesa" align="center" class="m-1" 
									:header-bg-variant="orden.IdCliente==null?'primary':'danger'"
									:border-variant="orden.IdCliente==null?'primary':'danger'"
									header-text-variant="white" header-text-weight="bold"
									footer-tag="footer">
                                  
								<img  src="static/mesa.png" height="80"/>
								<template #footer>
									<b-button-group>
									<b-button size="sm" @click="liberarMesa(i)" v-if="orden.IdCliente==null" variant="primary" block>Liberar</b-button>
									<b-button size="sm" @click="openOrden(i)" variant="info"><i class="fas fa-eye"></i></b-button>
									</b-button-group>
								</template>
								</b-card>
							</b-col>   
                        </b-row>
                    </b-col>

                    <b-col size="6">
                        <b-row class="text-right mt-1">
                            <b-col size="12">
                                <b-button class="m-1" @click="getOrdenes" variant="primary">Actualizar Ordenes</b-button>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row v-if="idOrdenSelect!=null" style="border: 1px solid black; border-radius:10px; margin:10px; background: white !important;">
                            <div id="headerCamiones" class="col-md-12">
                                <b-row class="mt-1">
                                    <b-col md="11"  class="text-left" >
                                        <h4><b>Información de Mesa: {{ ordenSelect.Mesa }}</b></h4>
                                    </b-col>
                                    <b-col md="1"  class="text-right" >
                                        <b-button @click="cerrarInfo" variant="danger">x</b-button>
                                    </b-col>
									<b-col md="12">
										<label style="font-size:1.1rem">Cliente: <b>{{ordenSelect.IdCliente==null?'Sin asignar':ordenSelect.Cliente}}</b></label>
									</b-col>
                                    <b-col md="12">
										<label style="font-size:1.1rem">Atendido por: <b>{{ordenSelect.UsuarioReg}}</b></label>
									</b-col>
                                    <b-col  md="12">
                                    <div class="text-center">
                                        <label style="font-size:1.4rem">Consumo total: <b>S/ {{ montoTotalConsumo.toFixed(2) }}</b></label>
                                        <br>
                                        <b-button :disabled="ordenSelect.IdCliente==null" variant="success" type="submit" @click="pagarMesa">
                                        Pagar Ahora
                                        </b-button>
                                    </div>
                                    <div class="m-1" v-if="productosEnEspera>0">
                                        <b-badge variant="danger">Nota: Tiene {{productosEnEspera}} producto(s) en espera de envio, use el aplicativo para imprimir comanda.</b-badge>                 
                                    </div>
                                    </b-col>
                                </b-row>
                                <b-row>
                                    <b-col md="12" id="productosProduccion">
                                    <div>
                                    <b-form-group>
                                    <label>Agrega Productos:</label>
                                    <b-input-group>
                                        <autocomplete
                                        :url="API + 'productos'"
                                        ref="inputProducto"
                                        anchor="Producto"
                                        :onShouldRenderChild="renderChild"
                                        label="writer"
                                        placeholder="Seleccionar Producto"
                                        :on-select="addProductoModal"
                                        :onFocus="focusAutocomplete.bind(null, 'inputProducto')"
                                        :classes="{input: 'form-control upper'}">
                                        </autocomplete>
                                    </b-input-group>
                                    </b-form-group>
                                    <table class="table b-table table-sm">
                                    <thead>
                                        <tr>
                                        <th>Id</th>
                                        <th>Producto</th>
                                        <th>Categoria</th>
                                        <th class="text-center">Acción</th>
                                        <th class="text-center">Cantidad</th>
                                        <th>Precio</th>
                                        <th>Enviado</th>
                                        <th class="text-center">Quitar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(prod, index) in productos" :key="index" :class="{'bg-secondary text-white': prod.isDisabled}">
                                        <td>{{ prod.IdProducto }}</td>
                                        <td>{{ prod.Producto }}</td>
                                        <td>{{ prod.ProductoCategoria }}</td>
                                        <td class="text-center" style="width:70px"><b-button size="sm" class="btn-danger" @click="rmCantidadDetalleProducto(index)">-</b-button><b-button size="sm" class="btn-info ml-1" @click="addCantidadDetalleProducto(index)">+</b-button></td>
                                        <td class="text-center">
                                            <input style="width:70px" required type="number" class="form-control" readonly v-model="prod.Cantidad"/>
                                        </td>
                                        <td>
                                            <input readonly step="0.01" required type="number" class="form-control" v-model="prod.Precio" />
                                        </td>
                                        <td class="text-center"><img :src="prod.Enviado==1?'static/success.png':'static/wait.png'" height="32"/></td>
                                        <td class="text-center"><b-button variant="danger" size="sm" @click="eliminarDetalleProducto(index)"><i class="fa fa-trash"></i></b-button></td>
                                        </tr>
                                    </tbody>
                                    </table>
                                    </div>
                                    </b-col> 
                                </b-row>                          
                                </div>
                        </b-row>
                    </b-col>
                </b-row>
            </div>
      </b-container>
    <b-modal size="md" ref="modalProductoOpc" hide-footer title="Agregar Producto" no-close-on-esc no-close-on-backdrop>
		<b-container>
			<b-row>
				<b-col class="text-center">
					<h4>{{productoSelect.Producto}}</h4>
					<h4>S/ {{productoSelect.PrecioContado}}</h4>
				</b-col>
			</b-row>
			<b-row>
				<b-col md="12">
					<b-col md="3">
						<b-form-group id="input-group-1" label="Cantidad:" label-for="input-1">
							<b-form-input id="input-1" min="1" v-model="productoSelect.Cantidad" type="number" required>
							</b-form-input>
						</b-form-group>
					</b-col>
					<b-col md="12">
						<b-form-group id="input-group-1" label="Indicaciones:" label-for="textarea-rows">
							<textarea class="form-control" id="textarea-rows m-1" placeholder="EJEMPLO: SIN PIMIENTA, SIN ENSALADA" rows="5" v-model="productoSelect.Indicacion">
							</textarea>
						</b-form-group>
					</b-col>
				</b-col>
			</b-row>
		</b-container>
		<b-btn class="mt-3" variant="outline-success" block @click="addDetalleProducto">Agregar</b-btn>
     	<b-btn class="mt-3" variant="outline-danger" block @click="()=>{$refs.modalProductoOpc.hide()}">Cancelar</b-btn>
    </b-modal>
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
		this.getOrdenes()
	},
	data () {
		return {
			API: config.API,
            ordenes:[],
            idOrdenSelect:null,
			ordenSelect:{},
            productos:[],
            processAdd:false,
            processRm:false,
            productosEnEspera:0,
			productoSelect:{},

			fechaReserva: this.$moment().format("YYYY-MM-DD"),
		}
	},
	computed: {
		...mapState({
           // productos: state => state.cart.productos
        }),
		montoTotalConsumo() {
			let sumatoria = 0
			this.productos.map(pro => {
                sumatoria+=(pro.Cantidad *pro.Precio)
			})
			return sumatoria;
		},
	},
	methods: {
		//Metodos restaurante
		getOrdenes() {
            //habitaciones?sortBy=Gen_Producto.Producto'
			this.idOrdenSelect=null    
			this.axios.get(this.API + 'ordenes/pendientes').then(res => {
				//this.habitaciones = res.data
                this.ordenes = res.data
                console.log(res.data);
				/* this.pisos.forEach(piso => {
					this.habitacionesPiso[piso] = this.getHabitacionesPiso(piso)
				}) */
			})
		},
		openOrden(index){
			this.ordenSelect = this.ordenes[index];
            this.getProductos(this.ordenSelect.IdPreOrden)
		},
        getProductos (IdPreOrden) {
            this.idOrdenSelect=null	
            if (IdPreOrden) {
                this.axios.get(this.API + 'preorden/detalle', {
                params: {idPreOrden: IdPreOrden}
                }).then(res => {
                this.idOrdenSelect=IdPreOrden
                this.productos = res.data
                this.productosEnEspera=0
                this.productos.map(pro=> {
                    if(pro.Enviado==0){this.productosEnEspera++}
                })
                }).catch(err => {
                this.idOrdenSelect=null
                console.log(err)
                })
            }
        },
        renderChild(data) {
        return `
            <b class='alert-secondary'>${data.IdProducto} - ${data.Producto} - ${data.CodigoBarra}</b>
        `
        },
        eliminarDetalleProducto(index){
        let result = confirm("¿Esta seguro quitar el producto: "+this.productos[index].Producto+"? No se podrá revertir la acción." );
            if (result == true) {
            this.axios.post(this.API + 'preordendetalle/delete', {
            idPreOrdenDet: this.productos[index].IdPreOrdenDet
                }).then(res => {
                    if(res.data){
                    this.$toasted.show('¡Producto eliminado!')
                    this.productos.splice(index, 1)
                    this.productosEnEspera=0
                    this.productos.map(pro=> {
                        if(pro.Enviado==0){this.productosEnEspera++}
                    })
                    }
                    console.log(res);
                }) 
            }
        },
        addCantidadDetalleProducto(index){
        this.processAdd=true;
        this.axios.post(this.API + 'preordendetalle/addcantidad', {
                        IdPreOrdenDet: this.productos[index].IdPreOrdenDet
                    }).then(res => {
            if(res.data!=null){
                this.productos[index].Cantidad=res.data.Cantidad;
            }
            this.processAdd=false;
                }).catch((error)=>{
            this.processAdd=false;
            this.$toasted.show('Se produjo un error, hay conexion')
            })
        },
        rmCantidadDetalleProducto(index){
        if(this.productos[index].Cantidad>1){
            this.processRm=true;
            this.axios.post(this.API + 'preordendetalle/rmcantidad', {
                        IdPreOrdenDet: this.productos[index].IdPreOrdenDet
                    }).then(res => {
            if(res.data!=null){
                this.productos[index].Cantidad=res.data.Cantidad
            }
            this.processRm=false
                }).catch((error)=>{
            this.processRm=false
              this.$toasted.show('Se produjo un error, hay conexion')
            }) 
        }    

        },
        addProductoModal(data){
			let index = this.productos.findIndex(el => el.IdProducto == data.IdProducto)

			if (index >= 0) {
				this.$toasted.show('El producto ya está agregado')
				return
			}
			this.productoSelect = data
			this.productoSelect.Cantidad = 1
			this.productoSelect.Indicacion = ''
			this.$refs.modalProductoOpc.show()
        },
		addDetalleProducto(){
			this.axios.post(this.API + 'preordendetalle/agregar', {
            IdPreOrden: this.idOrdenSelect,
            IdProducto:this.productoSelect.IdProducto,
            Cantidad:this.productoSelect.Cantidad,
            Precio:this.productoSelect.PrecioContado,
            Indicacion:this.productoSelect.Indicacion.toUpperCase()
            }).then(res => {
                this.getProductosOrden(this.idOrdenSelect)
			    this.$refs.modalProductoOpc.hide()
        	}).catch((error)=>{
        	}) 
		},
        getProductosOrden (IdPreOrden) {
            if (IdPreOrden) {
                this.axios.get(this.API + 'preorden/detalle', {
                params: {idPreOrden: IdPreOrden}
                }).then(res => {
                this.$toasted.success('¡Producto agregado!')
                this.idOrdenSelect=IdPreOrden
                this.productos = res.data
                this.productosEnEspera=0
                this.productos.map(pro=> {
                    if(pro.Enviado==0){this.productosEnEspera++}
                })
                }).catch(err => {
                this.idOrdenSelect=null
                console.log(err)
                })
            }
        },
        focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
            this.$refs[ref].handleDoubleClick()
        },
        cerrarInfo(){
            this.idOrdenSelect=null
            this.productos=[]
            this.getOrdenes()
        },
        liberarMesa(index){
            let result = confirm("¿Esta seguro liberar mesa: "+this.ordenes[index].Mesa+"? No se podrá revertir la acción." );
                if (result == true) {
                    this.axios.post(this.API + 'preorden/liberar', {
                    IdPreOrden: this.ordenes[index].IdPreOrden
                    }).then(res => {
                        if(res.data){
                            this.$toasted.success('Mesa '+this.ordenes[index].Mesa+' liberada, se actualizaron las ordenes!')
                            this.getOrdenes()
                        }else{
                            this.$toasted.show('No se puede liberar la mesa '+this.ordenes[index].Mesa+' tiene un cliente!')
                            this.getOrdenes()
                        }
                    }).catch((error)=>{
                    })
                }
        },
        pagarMesa(){
            this.axios.get(this.API + 'preorden/estado', {
            params: {idPreOrden: this.ordenSelect.IdPreOrden}
            }).then(res => {
                //target="_blank" :href="'/views/V_VentaForm.php?idPreOrden=' + idOrdenSelect + '&idCliente=' + ordenSelect.IdCliente"
                if(res.data.IdPreOrden==this.ordenSelect.IdPreOrden){
                    if(res.data.Estado==0){
                        window.open('/views/V_VentaForm.php?idPreOrden=' +  this.ordenSelect.IdPreOrden+'&idCliente='+ this.ordenSelect.IdCliente,'_blank');
                    }else if(res.data.Estado==1){
                        this.$toasted.show('Esta orden ya fue pagada, se actualizan las ordenes en breve...')
                        this.getOrdenes()
                    }
                }
                console.log(res.data);
            }).catch(err => {
            console.log(err)
            this.$toasted.show('Se produjo un error, hay conexion')
            })
        },
		//Fin de metodos restaurante
		sumProductos() {
			let total = 0
			if (this.$refs.preOrdenProductos) {
				total = this.$refs.preOrdenProductos.sumProductos + this.totalGastos - this.totalAdelantos
			}
			return Math.abs(total).toFixed(2)
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
}
</style>
<style scoped>
h5 {
	font-size: 1.2rem;
	color: #ffffff;
}
.card-header .header{
    background: red;
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