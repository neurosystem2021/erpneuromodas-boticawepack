<template>
    <div>
        <b-modal :no-close-on-esc="true" id="modalDeudaCliente" size="lg" modal-class="modal-xl" ref="deudaClienteForm" v-model="show" title="Comprobantes por pagar">
            <b-row class="mb-1">
                <b-col>
                    <b>SR(ES):</b>
                    {{ clienteSelect.Cliente }}
                </b-col>
                <hr>
            </b-row>
            <b-row class="mb-1">
            <b-col>
                <table class="table b-table table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha Emisión</th>
                            <th>Fecha Vencimiento</th>
                            <th>Correlativo</th>
                            <th>Total</th>
                            <th>Aplicado</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in deudas" :key="index">
                            <td>{{ item.IdDocVenta }}</td>
                            <td>{{ item.FechaDoc }}</td>
                            <td>{{ item.FechaCredito }}</td>
                            <td>{{ item.Correlativo }}</td>
                            <td>{{ item.Total }}</td>
                            <td class="text-center">{{ item.Aplicado }}</td>
                            <td>{{ item.Saldo }}</td>
                        </tr>
                    </tbody>
                </table>
            </b-col>
            </b-row>
        </b-modal>
    </div>
</template>
<script>
import config from '@/store/config.js'

    export default {
        data () {
            return {
                API: config.API,
                deudas: [],
                cliente: {},
                show: false,
            }
        },
        beforeMount () {
            this.initForm()
        },
        props: ['clienteSelect'],
        watch:{
            show(val){
                if (val)
                this.cargarDeuda()
            }
        },
        methods: {
            initForm () {
                this.cliente = {}

            },
            focusAutocomplete (ref) {
                this.$refs[ref].handleDoubleClick()
            },
            cargarDeuda () {
                this.cliente = this.clienteSelect
                this.axios.get(this.API + 'cliente/deudas?idCliente=' + this.cliente.IdCliente)
                .then((response) => {
                   this.deudas = response.data
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            }
        }
    }
</script>
<style>

</style>
