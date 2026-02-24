<template>
    <div> 
        <b-row class="mb-1">
            <b-col>
                <h6 style="font-weight:normal"> DNI/RUC: <span style="font-weight:bold">{{dniRuc}}</span></h6>
                <h6 style="font-weight:normal"> Cliente: <span style="font-weight:bold">{{Cliente}}</span></h6> 
            </b-col>
            <b-col>
                <h6 style="font-weight:normal"> Serie: <span style="font-weight:bold">{{Serie}}</span></h6>   
                <h6 style="font-weight:normal"> ID Documento: <span style="font-weight:bold">{{IdDoc}}</span></h6>
                <h6 style="font-weight:normal"> Monto total: <span style="font-weight:bold">S/. {{Total.toFixed(2)}}</span></h6>
            </b-col>
        </b-row>

        <br>
        <b-row class="mb-1">
             <b-col>
                 <h6>Letras:</h6>
             </b-col>
        </b-row>
        <b-row class="mb-1">
            <b-col>
                <table class="table b-table table-striped table-hover table-sm">
                    <thead>
                        <tr>
                            <th class="text-center">N° Letra</th>
                            <th class="text-center">Codigo</th>
                            <th class="text-center">Fecha de Vencimiento</th>
                            <th class="text-center">Pago Realizado</th>
                            <th class="text-center">Importe</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="Letras.length==0"><td colspan="6"  class="text-center" style="font-weight:bold">No existe letras para esta deuda.</td></tr>
                        <tr v-for="(item, index) in Letras" :key="index">
                            <td class="text-center">{{ item.NumeroLetra }}</td>
                            <td class="text-center">{{ item.IdDocIdLetra }}</td>

                            <td class="text-center">{{ (item.FechaPago).substring(0,10) }}</td>
                            <td class="text-center">{{ item.FechaDoc }}</td>
                            <td class="text-center">{{ item.Importe }}</td>
                            <td v-if="item.Cancelado" class="text-center">
                                <b-badge variant="success">CANCELADO</b-badge>
                            </td>
                            <td v-else class="text-center">
                                <b-badge variant="warning">PENDIENTE</b-badge>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="6"  class="text-center">

                            </td>
                        </tr>
                    </tbody>
                </table>
            </b-col>
        </b-row>
        <b-row  class="text-center">
            <b-col>
              <b-btn size="sm" variant="success" :href="'/imprimir/reporteletrascliente.php?idDoc='+IdDoc+'&dniruc='+dniRuc+'&cliente='+Cliente+'&serie='+Serie+'&total='+Total" target="_blank">Exportar reporte PDF</b-btn>
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
                Letras: [],
                IdDoc:"",
                Total:0,
                Cliente:"",
                Serie:"",
                dniRuc:""
            }
        },
        computed:{
        },
        methods: {
            cargarDeudasCliente(idDocDet, total, dniruc, cliente, serie){
            this.IdDoc = idDocDet;
            this.Total = total;  
            this.dniRuc = dniruc;
            this.Cliente = cliente; 
            this.Serie = serie; 
            this.axios.get(this.API + 'letras?idDocDet=' +idDocDet) 
                .then((response) => {
                   this.Letras = response.data;
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