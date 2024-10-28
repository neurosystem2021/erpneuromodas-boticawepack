<template>
  <b-container>
    <b-row>
        <b-col cols="12">
            <center>CONVERSIÓN DE UNIDADES</center>
        </b-col>
        <b-col cols="6">
            <b-row>
                <b-col cols="6">
                    <label>Almacen Destino:</label>
                    <autocomplete
                        ref="inputAlmacenDestino"
                        required="required"
                        initValue=""
                        :url="API + `almacenes`"
                        anchor="Almacen"
                        label="writer"
                        placeholder=""
                        :on-select="setAlmacenDestino"
                        :onFocus="focusAutocomplete.bind(null, 'inputAlmacenDestino')"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                </b-col>
                <b-col cols="6">
                    <label>Cantidad:</label>
                    <b-form-input type="number" :disabled="true"></b-form-input>
                </b-col>
                <b-col cols="12">
                    <label>Observación:</label>
                    <b-form-textarea></b-form-textarea>
                </b-col>
                <b-col cols="12">
                    <label>Buscar Presentación:</label>
                    <autocomplete
                        ref="inputAlmacenDestino"
                        required="required"
                        initValue=""
                        :url="API + `almacenes`"
                        anchor="Almacen"
                        label="writer"
                        placeholder=""
                        :on-select="setAlmacenDestino"
                        :onFocus="focusAutocomplete.bind(null, 'inputAlmacenDestino')"
                        :classes="{input: 'form-control'}">
                      </autocomplete>

                </b-col>
                <b-col cols="12"></b-col>
            </b-row>
        </b-col>
        <b-col cols="6"></b-col>
        
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API
    };
  },
  methods: {
    setAlmacenDestino(obj) {
      console.log(obj);
      //   this.movimiento.almacenOrigen = obj;
      //   if (this.isSalida) {
      //     this.obtenerStok({
      //       IdAlmacen:
      //         this.movimiento.almacenOrigen.IdAlmacen > 0
      //           ? this.movimiento.almacenOrigen.IdAlmacen
      //           : 1
      //     });
      //   } else {
      //     this.obtenerStok({
      //       IdAlmacen: !this.isTraslado
      //         ? this.movimiento.almacenDestino.IdAlmacen > 0
      //           ? this.movimiento.almacenDestino.IdAlmacen
      //           : 1
      //         : this.movimiento.almacenOrigen.IdAlmacen > 0
      //           ? this.movimiento.almacenOrigen.IdAlmacen
      //           : 1
      //     });
      //   }
    },
    focusAutocomplete(ref) {
      //this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    }
  }
};
</script>

<style>
</style>
