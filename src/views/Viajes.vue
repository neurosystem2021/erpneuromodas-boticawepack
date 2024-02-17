<template>
    <div>
        <b-container fluid style="width: calc(90%)" class="bv-example-row">
            <b-row class="text-center">
                <b-col>
                    <h1>Registro de Viajes</h1>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col md="4">
                    <b-form-group>
                        <label>Fecha Inicio:</label>
                        <b-form-input v-model="fechaInicio" type="date" step="1" placeholder="Fecha Inicio">
                        </b-form-input>
                    </b-form-group>
                </b-col>
                <b-col md="4">
                    <b-form-group>
                        <label>Fecha Fin:</label>
                        <b-form-input v-model="fechaFin" type="date" step="1" placeholder="Fecha Inicio">
                        </b-form-input>
                    </b-form-group>
                </b-col>
                <b-col md="2" class="text-center">
                    <b-form-group>
                        <br>
                        <b-button @click="buscarViajes" variant="primary" size="lg">Buscar Viajes</b-button>
                    </b-form-group>
                </b-col>
                <b-col md="2" class="text-center">
                    <b-form-group>
                        <br>
                        <b-button @click="buscarViajes" variant="success" size="lg">Nuevo Viaje</b-button>
                    </b-form-group>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col>
                    <b-card v-for="(viaje, index) in viajes" :key="index" class="cardViaje">
                        <b-row>
                            <b-col></b-col>
                        </b-row>
                        <b-row class="m-1" style="font-weight:bold">
                            <b-col md="1" class="text-center">
                                <label style="margin-bottom: 0;"><b>N° VIAJE</b></label><br>
                                <label style="font-size: 1.4rem;"><b-badge>{{ viaje.IdViaje }}</b-badge></label>
                            </b-col>
                            <b-col md="2" class="text-center">
                                <label style="margin-bottom: 0;"><b>FECHA Y HORA</b></label><br>
                                <label style="font-size: 1.4rem;"><b-badge variant="primary">{{ viaje.FechaViaje }} </b-badge> <b-badge variant="primary">{{ viaje.HoraViaje }}</b-badge></label>
                            </b-col>
                            <b-col md="2" class="text-center">
                                <b-row>
                                    <label><b>ORIGEN: </b> {{ viaje.CiudadOrigen }}</label>   
                                </b-row>
                                <b-row>
                                    <label><b>DESTINO: </b> {{ viaje.CiudadDestino }}</label>   
                                </b-row>
                            </b-col>
                            <b-col md="3" class="text-center alignCenter">
                                <label>{{ viaje.Apellidos }} {{ viaje.Nombres }}</label><br>
                                <label>{{ viaje.Vehiculo }} ({{ viaje.Asientos }})</label>     
                            </b-col>
                            <b-col md="2" class="text-center">
                                <label style="margin-bottom: 0;"><b>ASIENTOS</b></label><br>
                                <label style="font-size: 1.4rem;"><b-badge variant="success">{{ 5 }}</b-badge> / <b-badge variant="primary">{{ 10 }}</b-badge></label>
                            </b-col>
                            <b-col md="2" class="text-center alignCenter">
                                <span class="btn btn-sm text-dark ml-2" style="font-size: 1rem;" size="sm" @click="estadoViaje(viaje, index)"><i class="fas fa-sync fa-lg"></i></span>
                                <span class="btn btn-sm text-primary ml-2" style="font-size: 1rem;" size="sm" @click="editarViaje(viaje, index)"><i class="fas fa-pencil-alt fa-lg"></i></span>
                                <span class="btn btn-sm text-warning ml-2" style="font-size: 1rem;" size="sm" @click="mostrarViaje(viaje, index)"><i class="fas fa-bus fa-lg"></i></span>
                                <span class="btn btn-sm text-danger ml-2" style="font-size: 1rem;" size="sm" @click="anularViaje(viaje, index)"><i class="fas fa-ban fa-lg"></i></span>
                            </b-col>
                        </b-row>
                        <b-row class="p-1" style="margin-bottom: -13px;">
                            <b-col md="12" class="text-center" style="border-top: 2px solid rgba(0, 0, 0, 0.125);" :class="'bg-'+getColorEstado(viaje.Estado)">
                                <label style="font-size: 1.4rem; margin-bottom: 0; font-weight: bold; color:white">{{ getEstado(viaje.Estado) }}</label>
                            </b-col>
                        </b-row>
                    </b-card>
                </b-col>
            </b-row>
            <!--<b-row>
                <b-col>
                    <table id="tableConfig" class="table b-table table-hover table-striped table-sm">
                        <thead>
                            <tr class="table-success">
                                <th class="text-left">ID</th>
                                <th class="text-left">Nombre</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(operacion, i) in viajes" :key="i" :class="{'table-danger': operacion.Anulado}">
                                <td  style="width: 5% !important">{{ operacion.IdOperacion }}</td>
                                <td>{{ operacion.Operacion }}</td>
                                <td>{{ operacion.FechaReg }}</td>
                                <td class="text-center">
                                    <b-badge :variant="operacion.Ganancia?'success':'danger'" style="font-size: 1rem;">{{ operacion.Ganancia?'SI':'NO' }}</b-badge>
                                </td>
                                <td class="text-center">
                                    <b-button variant="primary" size="sm" @click="editarOperacion(operacion)"><i class="fas fa-pencil-alt fa-lg"></i></b-button>
                                </td>
                                <td class="text-center">
                                    <b-button v-if="operacion.Anulado" variant="success" size="sm" @click="anularServicio(operacion,0)"><i class="fas fa-check fa-lg"></i></b-button>
                                    <b-button v-else variant="danger" size="sm" @click="anularServicio(operacion,1)"><i class="fas fa-times fa-lg"></i></b-button>
                                </td>
                            </tr>
                            <tr v-if="operaciones.length == 0">
                                <td colspan="10" class="text-center" style="font-weight: bold">NO TIENE OPERACIONES</td>
                            </tr>
                        </tbody>
                    </table>
                </b-col>
            </b-row>-->
            <b-modal ref="modalEstado" size="sm" id="modalEstado" hide-footer hide-header>
                <b-row>
                    <b-col md="12" class="text-center">
                        <h5>CAMBIO DE ESTADO</h5>
                        <h4><b>VIAJE N° {{ viajeSelect.IdViaje }}</b></h4>
                    </b-col>
                </b-row>
                <hr>
                <b-row>
                    <b-col md="12">
                        <b-button block v-for="(estado, index) in itemsEstado" :key="index" @click="cambioEstado(estado)" :variant="estado.variant" :disabled="estado.value <= viajeSelect.Estado"><b>{{ estado.text }}</b></b-button>
                    </b-col>
                </b-row>
            </b-modal>
        </b-container>
    </div>
</template>
  
<script>
import config from '@/store/config.js'

export default {
    mounted() {
    },
    data() {
        return {
            API: config.API,
            fechaInicio: this.$moment().format("YYYY-MM-01"),
            fechaFin: this.$moment().format("YYYY-MM-01"),
            viajes: [],
            itemsEstado:[
                {value: 1, text: 'EN VENTA', variant: 'primary'},
                {value: 2, text: 'EN VIAJE', variant: 'warning'},
                {value: 3, text: 'COMPLETADO', variant:'success'},
            ],
            viajeSelect: {},
        }
    },
    methods: {
        async cambioEstado(estado){
            try {
                let respuesta = await this.axios.post(this.API + 'transporte/viajes/estado', {
                    IdViaje: this.viajeSelect.IdViaje,
                    EstadoActual: this.viajeSelect.Estado,
                    EstadoNuevo: estado.value
                })
                if(respuesta.data.success){
                    this.$toasted.success(respuesta.data.msg)
                    this.$refs['modalEstado'].hide()
                    this.buscarViajes()
                }else{
                    this.$toasted.error(respuesta.data.msg)
                }
            } catch (error) {
                this.$toasted.error('Ha ocurrido un error')
            }
        },
        editarViaje(viaje, index){

        },
        async mostrarViaje(viaje, index){
            try {
            let respuesta = await this.axios.get(this.API + 'transporte/viajes', {
                params:{
                    fechaInicio: this.fechaInicio,
                    fechaFin: this.fechaFin,
                }
            })
            this.viajes = respuesta.data
          } catch (error) {
            this.$toasted.error('Ha ocurrido un error')
          }
        },
        estadoViaje(viaje, index){
            this.viajeSelect = viaje
            this.$refs['modalEstado'].show()
        },
        anularViaje(viaje, index){

        },
        getEstado(opc){
            let estado = this.itemsEstado.find((i)=>opc==i.value)
            if(estado){
                return estado.text
            }
            return 'SIN DATOS'
        },
        getColorEstado(opc){
            let estado = this.itemsEstado.find((i)=>opc==i.value)
            if(estado){
                return estado.variant
            }
            return 'secondary'
        },
        async buscarViajes(){
            try {
            let respuesta = await this.axios.get(this.API + 'transporte/viajes', {
                params:{
                    fechaInicio: this.fechaInicio,
                    fechaFin: this.fechaFin,
                }
            })
            this.viajes = respuesta.data
          } catch (error) {
            this.$toasted.error('Ha ocurrido un error')
          }
        },
        /*rowClick(item, index) {
            this.vehiculoSelect = item
            this.$refs.vehiculoForm.show = true;
        },*/
    },
    components: {
    }
}
</script>
  
<style>
.cardViaje .card-body {
    padding: 0.85rem;
}
.cardViaje {
    margin-bottom: 10px;
    border: 2px solid rgba(0, 0, 0, 0.125) !important;
}
.alignCenter{
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
}
#modalEstado{
    padding-right: 0 !important;
}
</style>