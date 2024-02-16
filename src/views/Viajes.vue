<template>
    <div>
        <b-container class="bv-example-row">
            <b-row class="text-center">
                <b-col>
                    <h1>Registro de Viajes</h1>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col md="5">
                    <b-form-group>
                        <label>Fecha Inicio:</label>
                        <b-form-input v-model="fechaInicio" type="date" step="1" placeholder="Fecha Inicio">
                        </b-form-input>
                    </b-form-group>
                </b-col>
                <b-col md="5">
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
            </b-row>
            <hr>
            <b-row>
                <b-col>
                    <b-card v-for="(viaje, i) in viajes" :key="i">
                        <b-row class="m-2" style="font-weight:bold">
                            <b-col md="2" class="text-center">
                                <label>IMAGEN</label>
                            </b-col>
                            <b-col md="8" class="text-center">
                                <b-row>
                                    <label><b>ORIGEN: </b> {{ viaje.CiudadOrigen }}</label>   
                                </b-row>
                                <b-row>
                                    <label><b>DESTINO: </b> {{ viaje.CiudadDestino }}</label>   
                                </b-row>
                            </b-col>
                            <b-col md="2" class="text-center">
                                <label>DOCUMENTO QUE MODIFICA</label>
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
        }
    },
    methods: {
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
  
<style></style>