<template>
    <div>
        <b-button block @click="show=true" variant="outline-primary">Configurar</b-button>
        
        <b-form @submit="guardarConfigVenta">    
            <b-modal id="modalConfigVenta" 
                title="Configurar Venta" 
                size="md" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">

                <b-row class="mb-1" v-if="false">
                    <b-col><label for="input-porcada">Por la compra de:</label></b-col>
                    <b-col>
                        <b-input-group size="sm" prepend="S/. ">
                        <b-form-input v-model="porCada"
                            id="input-porcada"
                            type="number"
                            min="0"
                            step="0.1"
                            placeholder=""></b-form-input>
                        </b-input-group>
                    </b-col>
                </b-row>
                <b-row class="mb-1" v-if="false">
                    <b-col><label for="input-porcada">El cliente VIP gana:</label></b-col>
                    <b-col>
                        <b-input-group size="sm" append="punto(s)">
                        <b-form-input v-model="puntos"
                            id="input-porcada"
                            type="number"
                            min="0"
                            step="1"
                            placeholder=""></b-form-input>
                        </b-input-group>
                    </b-col>
                </b-row>
                <b-row class="mb-1" v-if="false">
                    <b-col><label for="input-porcada">Cada punto vale:</label></b-col>
                    <b-col>
                        <b-input-group size="sm" prepend="S/. ">
                        <b-form-input v-model="valorPunto"
                            id="input-porcada"
                            type="number"
                            min="0"
                            step="0.1"
                            placeholder=""></b-form-input>
                        </b-input-group>
                    </b-col>
                </b-row>
                <b-row v-if="tienePuntos != null">
                    <b-col md="6"><label for="input-porcada">Puntos al final del comprobante:</label></b-col>
                    <b-col md="3">
                    <b-btn v-if="tienePuntos==0" class="mt-1" size="sm" variant="success"  @click="accionPuntos(1)">
                        Habilitar
                    </b-btn> 
                    <b-btn v-if="tienePuntos==1" class="mt-1" size="sm" variant="danger"  @click="accionPuntos(0)">
                        Deshabilitar
                    </b-btn> 
                    </b-col>
                    <b-col md="3">
                        <b-form-text v-if="tienePuntos==1" text-variant="success">Los puntos están habilitados</b-form-text>
                        <b-form-text v-if="tienePuntos==0" text-variant="danger">Los puntos están deshabilitados</b-form-text>
                    </b-col>
                </b-row>
                <hr>
                <b-row class="mb-1">
                    <b-col><label for="input-porcada">Texto promocion del comprobante:</label></b-col>
                    <b-col>
                     <textarea 
                    class="form-control"
                    placeholder="Descripción"
                    cols="9" rows="4"
                    v-model="textoPromo">
                    </textarea>
                    <b-btn class="mt-1" size="sm" variant="success"  @click="guardarConfigTexto">
                        Guardar Texto
                    </b-btn>  
                    </b-col>
                </b-row>
                <div slot="modal-footer" >
                    <b-btn size="sm" variant="success" type="submit" v-if="false">
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
        watch:{
            show(){
                if(this.show){
                 //this.obtenerDescuento()
                 //this.obtenerComision() 
                 this.getTextoPromo()
                }
            }
        },
        data () {
            return {
                API: config.API,
                show: false,
                porCada: 20,
                puntos: 1,
                valorPunto: 1,
                textoPromo: '',
                tienePuntos: null
            }
        },
        methods: {
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
                    this.$toasted.success('Se actualizó la configuración')
                    this.show = false
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            },
            getTextoPromo(){
                this.axios.get(this.API + 'ventas/promo')
                    .then((response) => {
                        this.textoPromo = response.data.TEXTO_PROMO
                        this.tienePuntos = response.data.TIENE_PUNTOS
                })
                    .catch((error) => {
                        console.log(error)
                        this.tienePuntos = null
                        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                 })
            
            },
            guardarConfigTexto() {
                // Comision
                this.axios.post(this.API + 'ventas/promo', {
                    TextoPromo: this.textoPromo
                }).then((response) => {
                    //this.$emit('onUpdateConfigComision', response.data)
                    //this.obtenerComision()
                    this.textoPromo = response.data.textoPromo
                    this.$toasted.success('Se actualizó el texto promoción del comprobante')
                    this.show = false
                })
                .catch((error) => {
                    console.log(error)
                    this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                })
            },
            accionPuntos(opcion){
                this.axios.post(this.API + 'ventas/promo/puntos', {
                    Habilitar: opcion
                }).then((response) => {
                    //this.$emit('onUpdateConfigComision', response.data)
                    //this.obtenerComision()
                    this.tienePuntos = response.data.tienePuntos
                    if(this.tienePuntos==1){
                        this.$toasted.success('Se habilito los puntos del comprobante')
                    }else if(this.tienePuntos==0){
                        this.$toasted.error('Se desahabilito los puntos del comprobante')
                    }
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
