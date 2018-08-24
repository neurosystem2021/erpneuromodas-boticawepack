<template>
    <div>
        <b-form @submit="guardarConfigVenta">    
            <b-modal id="modalConfigVenta" 
                title="Devolver Producto Prestado" 
                size="md" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">

                <b-row class="mb-1">
                    <b-col md="5">
                        <label>Serie:</label>
                        <b-input-group size="sm">
                            <b-form-input v-model="serie"
                                id="input-serie"
                                type="text"
                                placeholder=""></b-form-input>
                        </b-input-group>
                    </b-col>
                    <b-col md="5">
                        <label>Número:</label>
                        <b-input-group size="sm">
                            <b-form-input v-model="numero"
                                id="input-numero"
                                type="text"
                                placeholder=""></b-form-input>
                        </b-input-group>
                    </b-col>
                    <b-col md="2">
                        <label></label>
                        <b-input-group size="sm">
                            <b-btn size="sm" variant="success" type="button" @click="buscarPrestados">
                                Buscar
                            </b-btn>
                        </b-input-group>
                    </b-col>

                    <b-col md="12">
                        <table>
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </b-col>
                </b-row>
                <div slot="modal-footer" >
                    <b-btn size="sm" variant="success" type="submit">
                        Guardar
                    </b-btn>
                    <b-btn size="sm" variant="secondary" @click="show=false">
                        Cerrar
                    </b-btn>
                </div>
            
            </b-modal>
        </b-form>
    </div>
</template>
<script>
import config from '@/store/config.js'

    export default {
        mounted () {
            this.obtenerDescuento()
        },
        data () {
            return {
                API: config.API,
                show: false,
                prestados: [],
                serie: '',
                numero: ''
            }
        },
        methods: {
            buscarPrestados () {
                this.axios.get(this.API + 'productos/prestados/docventa', {
                    params: {
                        serie: this.serie,
                        numero: this.numero
                    }
                })
                .then((response) => {
                    this.prestados = response.data
                })
            },
            obtenerDescuento () {
                this.axios.get(this.API + 'ventas/descuento')
                .then((response) => {
                    this.porCada = response.data.PorCada,
                    this.puntos = response.data.Puntos,
                    this.valorPunto = response.data.ValorPunto
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            },
            guardarConfigVenta (evt) {
                evt.preventDefault()

                this.axios.post(this.API + 'ventas/descuento', {
                    porCada: this.porCada,
                    puntos: this.puntos,
                    valorPunto: this.valorPunto
                })
                .then((response) => {
                    this.$emit('onUpdateConfig', response.data)
                    this.obtenerDescuento()
                    this.$toasted.show('Se actualizó la configuración')
                    this.show = false
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
