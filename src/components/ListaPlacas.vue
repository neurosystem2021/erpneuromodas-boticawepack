<template>
    <div>
        <b-button block @click="show=true" variant="primary"><i class="fas fa-id-card"></i></b-button>
        
        <b-form>    
            <b-modal id="modalListaPlacas" 
                title="Lista de placas" 
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">
                
                <b-row class="mb-1">
                    <b-col><label for="input-cliente">Cliente:</label>
                        <b-input-group>
                            <autocomplete
                                required="required"
                                ref="inputCliente"
                                :url="API + 'clientes'"
                                anchor="ClienteDniRuc"
                                label="writer"
                                placeholder=""
                                :onInput="(val) => newDniRuc = val"
                                :on-select="cargarPlacas" 
                                :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                                :classes="{input: 'form-control'}">
                            </autocomplete>
                        </b-input-group>
                    </b-col>
                </b-row>
                
                <br>
                <b-row class="mb-1">
                    <b-col>
                        <table class="table b-table table-sm ">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th style="width: 280px">QR</th>
                                    <th>Placa</th>
                                    <th>Cliente</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody v-for="(item, index) in placas" :key="index">
                                <tr>
                                    <td class="text-center">{{ item.IdPlacaCliente }}</td>
                                    <td>
                                        <div :id="'placa-' + item.Placa" style="float:left;"></div>
                                        <barcode :value="item.Placa" format="CODE128" width=1 height=60>
                                            El renderizado falló.
                                        </barcode>
                                    </td>
                                    <td>{{ item.Placa }}</td>
                                    <td style="width: 300px;">{{ item.Cliente }}</td>
                                    <td class="text-center"><b-button variant="primary" title="Imprimir" @click="imprimirQr(item.Placa)"><i class="fas fa-print"></i></b-button></td>
                                </tr>
                               
                                <br> 
                            </tbody>
                        </table>
                        <br>
                        <div class="float-right">
                            <b-btn size="sm" variant="success" type="submit">
                                Guardar
                            </b-btn>
                            <b-btn size="sm" variant="secondary" @click="show=false">
                                Cerrar
                            </b-btn>
                        </div>
                    </b-col>
                </b-row>
                <div slot="modal-footer" >
                    
                </div>
            
            </b-modal>
        </b-form>
    </div>
</template>
<script>
import config from '@/store/config.js'
var QRCode = require('qrcode')
import VueBarcode from 'vue-barcode';

    export default {
        
        mounted () {
            this.initForm()
        },
        data () {
            return {
                API: config.API,
                placas: [],
                pagos: [],
                cajaBanco: {},
                vendedor: {},
                show: false,
                cuentas:[{ value: null, text: '-- SELECCIONE CUENTA --' }],
            }
        },
        methods: {
            async imprimirQr (placa) {
                var prtContent = document.getElementById('placa-' + placa);
                var WinPrint = window.open('', '', 'left=0,top=0,width=800,height=900,toolbar=0,scrollbars=0,status=0');

                WinPrint.document.write('<div style="text-align: center"><img src="http://' + (document.location.host === 'localhost:8080' ? 'localhost' : document.location.host) + '/resources/images/logo-ticket.png" style="width: 200px;"/></div>');
                WinPrint.document.write('<br /><div style="text-align: center">' + prtContent.innerHTML + '</div>');
                WinPrint.document.close();
                WinPrint.focus();

                const delay = ms => new Promise(res => setTimeout(res, ms));

                await delay(1000);
                WinPrint.print();
              //  WinPrint.close();
            },
            showQr () {

                const matches = document.querySelectorAll('[id^="placa-"]');
                matches.forEach(element => {
                    let placa = element.id.replace('placa-','');
                    
                    QRCode.toDataURL(placa, function (err, url) {
                        var img = document.createElement("img");
                        img.setAttribute("src", url);
                        img.setAttribute("height", "100");
                        img.setAttribute("width", "100");

                        element.appendChild(img);            
                    })
                });
            
            },

            async initForm () {
                this.placas = []

                await this.axios.get(this.API + 'placa/clientes').then((response) => {
                    this.placas = response.data
                });

                this.showQr();
               // this.showBarCode();
            },
           
            focusAutocomplete (ref) {
                this.$refs[ref].handleDoubleClick()
            },
            cargarPlacas (cliente) {

                this.axios.get(this.API + 'placa/clientes?idCliente=' + cliente.IdCliente)
                .then((response) => {
                    this.placas = response.data
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            },
        },

        components: {
            'barcode': VueBarcode
        }
    }
</script>
<style>

</style>
