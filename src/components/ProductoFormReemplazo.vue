<template>
  <b-container>
    <b-row class="clsReemplazo">
        <b-col cols="12">
          <h6>Reemplazos:</h6>  
        </b-col> 
        <b-col cols="12"> 
            <autocomplete
                ref="autocompleteProducto"
                :url="`${API}productos`"
                anchor="Producto"
                label="writer"
                placeholder="Agregar Producto"
                :on-select="setProducto"
                :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                :classes="{input: 'form-control'}">
            </autocomplete>
        </b-col>
        <b-col cols="12">
            <b-table small :items="ProductoReempleazo" :fields="fields">
                <template slot="Estado" slot-scope="data">
                     <b-form-checkbox 
                        v-model="data.item.Estado" 
                        :value="1"
                        :unchecked-value="0"
                        switch>
                    </b-form-checkbox>
                </template>
                <template slot="action" slot-scope="data">
                    <b-button @click="EliminarReemplazo(data.index)" variant="danger">
                          <i class="fa fa-trash" aria-hidden="true"></i>  
                    </b-button>
                </template>
            </b-table> 
        </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  props: ["ProductoReempleazo"],
  data() {
    return {
      API: config.API,
      fields: [
        { label: "Cod. Frabricante", key: "CodigoFrabricante" },
        { key: "Producto" },
        { key: "Estado", label: "Inversa" },
        { key: "action", label: "" }
      ]
    };
  },
  methods: {
    setProducto(obj) {
      if (
        this.ProductoReempleazo.some(
          v => v.IdProductoReemplazo == obj.IdProducto
        )
      ) {
        return;
      }
      obj.Estado = 1;
      obj.IdProductoReemplazo = obj.IdProducto;
      this.ProductoReempleazo.push(obj);
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    EliminarReemplazo(index) {
      this.$swal({
        icon: "question",
        title: "¿Eliminar Registro?",
        text: "El registro será eliminado.",
        showCancelButton: true,
        confirmButtonText: "Eliminar Regisrtro",
        confirmButtonColor: "#D50000",
        cancelButtonColor: "#1B5E20",
        allowOutsideClick: false,
        allowEscapeKey: false
      }).then(async res => {
        if (res.isConfirmed) {
          this.ProductoReempleazo.splice(index, 1);
        }
      });
    }
  }
};
</script>

<style>
.clsReemplazo {
  padding: 5% 0%;
  border: 1px solid #e0e0e0 !important;
  border-radius: 15px;
  background: #f5f5f5 !important;
}
</style>
