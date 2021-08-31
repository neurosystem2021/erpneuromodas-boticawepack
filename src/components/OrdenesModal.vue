<template>
    <div>
        <b-button @click="show=true" variant="outline-danger" size="md">Ordenes</b-button>
           
        <b-modal id="modalOrdenes" 
            title="ORDENES ACTIVAS"
            size="lg" v-model="show"
            header-bg-variant="danger"
            headerTextVariant="light"
            body-bg-variant="light"
            body-text-variant="dark"
            centered 
            ok-only no-close-on-backdrop
            no-close-on-esc ok-title="Cerrar">           
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
									<b-button size="sm" @click="liberarMesa(orden.IdPreOrden)" v-if="orden.IdCliente==null" variant="primary" block>Liberar</b-button>
									<b-button size="sm" @click="openOrden(orden.IdPreOrden)" variant="info"><i class="fas fa-eye"></i></b-button>
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
                                    <br>
                                    <div>
                                    <table class="table b-table table-sm">
                                    <thead>
                                        <tr>
                                        <th>Id</th>
                                        <th>Producto</th>
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
                                <div class="text-center m-1">
                                    <b-button v-if="ordenSelect.IdCliente!=null" variant="danger" type="submit" @click="eliminarOrden">
                                    Eliminar Orden
                                    </b-button>
                                </div>  
                                <br>                    
                            </div>
                        </b-row>
                    </b-col>
                </b-row>
            </div>
            </b-container>
        </b-modal>
    </div>
</template>
<script>
import config from '@/store/config.js'
import { mapActions } from 'vuex'

    export default {
        mounted () {
            this.$emit('mounted', this.$refs.formProformaModal)
        },
        computed:{
            montoTotalConsumo() {
			let sumatoria = 0
			this.productos.map(pro => {
                sumatoria+=(pro.Cantidad *pro.Precio)
			})
			return sumatoria;  
            }
		},
        watch: {
        show (val) {
            console.log(val);
            if(val==true){
                this.getOrdenes()
            }
        }
        },
        data () {
            return {
                API: config.API,
                show: false,
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
        props: ['idAlmacen'],
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
		openOrden(idPreOrden){
            this.axios.get(this.API + 'orden/info', {
            params: {IdPreOrden: idPreOrden}
            }).then(res => {
                if(res.data.length>0){
                    if(res.data[0].IdCliente!=null){
                        this.ordenes[this.ordenes.findIndex(p => p.IdPreOrden === idPreOrden )].IdCliente=res.data[0].IdCliente;
                    }
                    this.ordenSelect = res.data[0];
                    this.getProductos(this.ordenSelect.IdPreOrden)
                }else{
                    this.getOrdenes()
                }
            }).catch(err => {
            this.idOrdenSelect=null
            console.log(err)
            })


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
		//	let index = this.productos.findIndex(el => el.IdProducto == data.IdProducto)
		//	if (index >= 0) {
		//		this.$toasted.show('El producto ya está agregado')
		//		return
		//	}
		//	this.productoSelect = data
		//	this.productoSelect.Cantidad = 1
		//	this.productoSelect.Indicacion = ''
		//	this.$refs.modalProductoOpc.show()
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
        liberarMesa(idPreOrden){
            let orden = this.ordenes[this.ordenes.findIndex(p => p.IdPreOrden === idPreOrden )];
            if(orden == undefined){
                this.getOrdenes()
                return;
            }
            let result = confirm("¿Esta seguro liberar mesa: "+orden.Mesa+"? No se podrá revertir la acción." );
                if (result == true) {
                    this.axios.post(this.API + 'preorden/liberar', {
                    IdPreOrden: orden.IdPreOrden
                    }).then(res => {
                        if(res.data){
                            this.$toasted.success('Mesa '+orden.Mesa+' liberada, se actualizaron las ordenes!')
                            this.getOrdenes()
                        }else{
                            this.$toasted.show('No se puede liberar la mesa '+orden.Mesa+' tiene un cliente!')
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
                        this.$emit('onPagarOrden', {IdPreOrden:this.ordenSelect.IdPreOrden,IdCliente:this.ordenSelect.IdCliente})
                        this.show=false
                        //window.open('/views/V_VentaForm.php?idPreOrden=' +  this.ordenSelect.IdPreOrden+'&idCliente='+ this.ordenSelect.IdCliente,'_blank');
                    }else if(res.data.Estado==1){
                        this.$toasted.show('Esta orden ya fue pagada, se actualizan las ordenes en breve...')
                        this.getOrdenes()
                    }
                }
            }).catch(err => {
            console.log(err)
            this.$toasted.show('Se produjo un error, hay conexion')
            })
        },
        eliminarOrden(){
            let result = confirm("¿Esta seguro de eliminar la orden activa de la mesa : "+this.ordenSelect.Mesa+"? No se podrá revertir la acción." );
                if (result == true) {
                    this.axios.post(this.API + 'preorden/eliminar', {
                    IdPreOrden:  this.ordenSelect.IdPreOrden
                    }).then(res => {
                        if(res.data){
                            this.$toasted.success('Orden de la mesa  '+this.ordenSelect.Mesa+' eliminada, se actualizaron las ordenes!')
                            this.getOrdenes()
                        }else{
                            this.$toasted.show('No se puede elimnar la orden de la '+this.ordenSelect.Mesa+' no tiene cliente!')
                            this.getOrdenes()
                        }
                    }).catch((error)=>{
                    })
                }
        },
		//Fin de metodos restaurante
		sumProductos() {
			let total = 0
			if (this.$refs.preOrdenProductos) {
				total = this.$refs.preOrdenProductos.sumProductos + this.totalGastos - this.totalAdelantos
			}
			return Math.abs(total).toFixed(2)
		},

        }
    }
</script>
<style>
#modalOrdenes .modal-dialog {
    max-width: 95% !important;
   }
</style>