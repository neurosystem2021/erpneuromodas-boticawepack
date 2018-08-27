<template>
    <div>
        <b-form @submit="guardarDevueltos">    
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
                        <br />
                        <table class="table table-sm">
                            <thead>
                                <tr class="text-center"><td>Código</td><td>Producto</td><td>Prestados</td><td>Devolver</td></tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in prestados" :key="index" class="text-center">
                                    <td>{{ item.CodigoBarra }}</td>
                                    <td>{{ item.Producto }}</td>
                                    <td>{{ item.Prestados }}</td>
                                    <td><input type="number" class="form-control" v-model="item.devueltos" /></td>
                                </tr>
                            </tbody>
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
            limpiar() {
                this.prestados = []
                this.serie = ''
                this.numero = ''
            },
            guardarDevueltos (evt) {
                evt.preventDefault()
                this.axios.post(this.API + 'productos/devueltos', { devueltos: this.prestados})
                .then((response) => {
                    this.show = false
                    this.$toasted.show('Productos actualizados correctamente')
                    this.$emit('update')
                })
            }
        }
    }
</script>
<style>

</style>
