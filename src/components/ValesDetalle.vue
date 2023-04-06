<template>
    <div> 
        <b-row class="mb-1" v-if="AnuladoConsumo==1">
             <b-col class="alert-danger text-center">
                 <h6 class="p-2">VENTA POR CONSUMO ANULADA POR NOTA DE CREDITO</h6>
             </b-col>
        </b-row>
        <hr>
        <b-row class="mb-1">
            <b-col>
                <h6 style="font-weight:normal"> DNI/RUC: <span style="font-weight:bold">{{dniRuc}}</span></h6>
                <h6 style="font-weight:normal"> Cliente: <span style="font-weight:bold">{{Cliente}}</span></h6> 
                <h6 style="font-weight:normal"> Monto Total: <b-badge style="font-size:13.5px" variant="primary">{{ SubtotalIni }} </b-badge></h6>
                <h6 style="font-weight:normal"> Monto Consumido: <b-badge style="font-size:13.5px" variant="danger">{{ SubTotalConsumo }} </b-badge></h6>
                <h6 style="font-weight:normal"> Monto Disponible: <b-badge style="font-size:13.5px" variant="success">{{ MontoDisponible }} </b-badge></h6>
            </b-col>
            <b-col>
                <h6 style="font-weight:normal"> Fecha y Hora: <span style="font-weight:bold">{{FechaDoc}}</span></h6>
                <h6 style="font-weight:normal"> ID Documento: <span style="font-weight:bold">{{IdDoc}}</span></h6>
                <h6 style="font-weight:normal"> Correlativo: <span style="font-weight:bold">{{Serie}} - {{Numero}}</span></h6>   
            </b-col>
        </b-row>
        <br>
        <b-row class="mb-1">
             <b-col>
                 <h5>Vales:</h5>
             </b-col>
        </b-row>
        <b-row class="mb-1">
            <b-col>
                <table class="table b-table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th class="text-center">Id</th>
                            <th class="text-center">FechaDoc</th>
                            <th class="text-center">Serie</th>
                            <th class="text-center">Numero</th>
                            <th class="text-center">Vendedor</th>
                            <th class="text-center">Placa</th>
                            <th class="text-center">Monto</th>
                            <th class="text-center">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="vales.length==0"><td colspan="6"  class="text-center" style="font-weight:bold">No existe vales para esta deuda.</td></tr>
                        <tr v-for="(item, index) in vales" :key="index">
                            <td class="text-center">{{ item.idDocVenta }}</td>
                            <td class="text-center">{{ item.FechaDoc }}</td>

                            <td class="text-center">{{ item.Serie }}</td>
                            <td class="text-center">{{ item.Numero }}</td>
                            <td class="text-center">{{ item.UsuarioReg }}</td>
                            <td class="text-center">{{ item.Placa }}</td>
                            <td class="text-center">{{ item.Monto }}</td>
                            <td class="text-center"><b-button :href="'/imprimir/index.php?IdDocVenta=' + item.idDocVenta" variant="success" target="_blank" size="sm">Documento</b-button></td>
                        </tr>
                    </tbody>
                </table>
            </b-col>
        </b-row>
    </div>
</template>
<script>
import config from '@/store/config.js'
    export default {
        mounted () {
        },
        data () {
            return {
                API: config.API,
                vales: [],
                IdDoc:"",
                SubtotalIni:0,
                Cliente:"",
                Serie:"",
                Numero: 0,
                dniRuc:"",
                SubTotalConsumo:0,
                MontoDisponible:0,
                FechaDoc: '',
                AnuladoConsumo:0,

            }
        },
        computed:{
        },
        methods: {
            cargarVales(IdDocVenta,SubtotalIni,DniRuc,Cliente,Serie,Numero,SubTotalConsumo,MontoDisponible,FechaDoc,AnuladoConsumo){
            this.IdDoc = IdDocVenta;
            this.SubtotalIni = SubtotalIni;  
            this.dniRuc = DniRuc;
            this.Cliente = Cliente; 
            this.Serie = Serie; 
            this.Numero = Numero;
            this.SubTotalConsumo = SubTotalConsumo;  
            this.MontoDisponible = MontoDisponible;
            this.FechaDoc = FechaDoc;
            this.AnuladoConsumo = AnuladoConsumo
            this.axios.get(this.API + 'vales/consumidos?IdDocVenta=' +IdDocVenta) 
                .then((response) => {
                   this.vales = response.data;
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                });
            }
        }
    }
</script>
<style>
</style>