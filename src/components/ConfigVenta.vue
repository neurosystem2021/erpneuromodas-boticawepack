<template>
    <div>
        <b-button @click="show=true" variant="info" class="btnAction">Configurar</b-button>
        
        <b-form @submit="guardarConfigVenta">    
            <b-modal id="modalConfigVenta" 
                title="Configurar Venta" 
                size="md" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">

                <b-row class="mb-1">
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
                <b-row class="mb-1">
                    <b-col><label for="input-porcada">El cliente gana:</label></b-col>
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
                <b-row class="mb-1">
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
                <b-row class="mb-1">
                    <b-col><label for="input-porcada">% Pago de comision:</label></b-col>
                    <b-col>
                        <b-input-group size="sm" prepend="% ">
                        <input v-model="valorComision"
                            id="input-porcada"
                            type="number"
                            min="0"
                            step="0.01"
                            class="form-control"
                            placeholder=""/>
                            <b-button size="sm" text="Button" variant="success" @click="guardarConfigComision">Ok</b-button>
                        </b-input-group>
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
import config from "@/store/config.js";

export default {
  mounted() {
    this.obtenerDescuento();
    this.obtenerComision();
  },
  data() {
    return {
      API: config.API,
      show: false,
      porCada: 20,
      puntos: 1,
      valorPunto: 1,
      valorComision: 1
    };
  },
  methods: {
    obtenerDescuento() {
      let empresa = sessionStorage.getItem("empresa");
      this.axios
        .get(this.API + "ventas/descuento", { id_empresa: empresa })
        .then(response => {
          this.porCada = response.data.PorCada;
          this.puntos = response.data.Puntos;
          this.valorPunto = response.data.ValorPunto;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    obtenerComision() {
      // Para obtener el ValorComision
      let empresa = sessionStorage.getItem("empresa");
      this.axios
        .get(this.API + "ventas/comision", { id_empresa: empresa })
        .then(response => {
          this.valorComision = response.data.valorComision;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    guardarConfigVenta(evt) {
      evt.preventDefault();
      let empresa = sessionStorage.getItem("empresa");
      this.axios
        .post(this.API + "ventas/descuento", {
          porCada: this.porCada,
          puntos: this.puntos,
          valorPunto: this.valorPunto,
          id_empresa: empresa
        })
        .then(response => {
          this.$emit("onUpdateConfig", response.data);
          this.obtenerDescuento();
          this.$toasted.show("Se actualizó la configuración");
          this.show = false;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    guardarConfigComision() {
      // Comision
      let empresa = sessionStorage.getItem("empresa");
      this.axios
        .post(this.API + "ventas/comision", {
          valorComision: this.valorComision,
          id_empresa: empresa
        })
        .then(response => {
          this.$emit("onUpdateConfigComision", response.data);
          this.obtenerComision();
          this.$toasted.show("Se actualizó la configuración");
          this.show = false;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    }
  }
};
</script>
<style>
</style>
