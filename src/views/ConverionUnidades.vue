<template>
  <b-container>
    <b-row>
        <b-col cols="12">
            <center><h1>CONVERSIÓN DE UNIDADES</h1></center>
        </b-col>
        <b-col cols="12">
            <b-row>
                <b-col cols="6">
                    <label>Almacén Origien:</label>
                    <autocomplete
                        ref="inputAlmacenOrigen"
                        required="required"
                        initValue=""
                        :url="API + `almacenes`"
                        anchor="Almacen"
                        label="writer"
                        placeholder=""
                        :on-select="setAlmacenOrigen"
                      :onFocus="focusAutocomplete.bind(null, 'inputAlmacenOrigen')"
                      :classes="{input: 'form-control'}">
                    </autocomplete>
                </b-col>
                <b-col cols="6">
                    <label>Almacén Destino:</label>
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
             
            
              <b-col cols="6" v-if="idAlmacenOrigen">
                  <label>Buscar Presentación:</label>
                  <!-- sortBy: isVentas ? 'stock' : 'IdProducto', -->
                  <autocomplete 
                    ref="autocompleteProducto" 
                    :url="APIProductos" 
                    :customParams="{
                      sortDesc: 1,
                      limit: '25',
                      idAlmacen: idAlmacenOrigen
                    }" 
                    :onShouldRenderChild="renderChild"
                    anchor="Producto"
                    label="writer" placeholder="Agregar Producto"
                    :on-select="setProducto" 
                    :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                    :classes="{ input: 'form-control' }"
                    
                    >
                  </autocomplete>

              </b-col>
              <b-col cols="6" v-if="idAlmacenOrigen">
                  <label>Cantidad:</label>
                  <input
                    class="form-control"
                    type="number"
                    ref="txtCantidad"
                    id="txtCantidad"
                    :disabled="lstProducto.length==0"
                    v-model="cantidad"
                    @input="validarCantidad"
                    @focusout="validarCantidad"
                    step="0.01"
                    ></input>
              </b-col>
              <b-col cols="12" class="mt-2">
                <b-table
                :fields="fields"
                :items="lstProducto"
                >
                  <template slot="correcto" slot-scope="data">
                    <b-alert v-if="data['item'].correcto && cantidad" show variant="success">Correcto</b-alert>
                    
                    <b-alert v-if="!data['item'].correcto && cantidad>0"  show variant="danger">
                      Cantidad Mayor al Stock
                    </b-alert>
                    
                  </template>
                </b-table>
                
              </b-col>
              <b-col cols="12" v-if="!lstProducto.length==0">
                <b-button @click="guardarConversion()" variant="success" :disabled="cantidad==0">Guardar</b-button>
              </b-col>
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
      API: config.API,
      idAlmacenOrigen: "",
      idAlmacenDestino: "",
      lstProducto: [],
      fields: [
        { key: "stock" },
        { key: "Producto" },
        { key: "ProductoCategoria", label: "Categoria" },
        { key: "ProductoMedicion", label: "U/M" },
        { key: "factor", label: "Factor" },
        { key: "cantidad", label: "Cantidad" },
        { key: "correcto", label: "" }
      ],
      cantidad: 0,
      producto: {}
    };
  },
  computed: {
    APIProductos() {
      return this.API + "cargar_producto_presentacion";
    }
  },
  methods: {
    setAlmacenOrigen(obj) {
      this.idAlmacenOrigen = obj.IdAlmacen;
      this.cantidad = 0;
      this.lstProducto = [];
      this.$nextTick(() => {
        let txtCantidad = document.getElementById("txtCantidad");
        txtCantidad.focus();
        txtCantidad.select();
      });
    },
    setAlmacenDestino(obj) {
      this.idAlmacenDestino = obj.IdAlmacen;
    },
    setProducto(obj) {
      this.producto = obj;
      obj.detalle.map(item => {
        return (item.cantidad = 0);
      });
      this.lstProducto = obj.detalle;
      this.cantidad = 0;
      this.$nextTick(() => {
        const inputRef = document.getElementById("txtCantidad"); //this.$refs[`txtCantidad`].$el;
        inputRef.focus();
        inputRef.select();
      });
    },
    focusAutocomplete(ref) {
      //this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    renderChild(data) {
      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${
        data.ProductoMarca
      } / ${data.ProductoModelo}
      `;
    },
    validarCantidad(obj) {
      this.lstProducto.forEach(val => {
        val.correcto = val.stock >= val.factor * this.cantidad;
      });
      this.generarOpciones();
    },
    generarOpciones() {
      this.lstProducto.forEach(element => {
        element.cantidad = parseFloat(element.factor * this.cantidad).toFixed(
          2
        );
      });
      this.$forceUpdate();
    },
    async guardarConversion() {
      if (this.lstProducto.some(v => !v.correcto)) {
        this.$swal({
          icon: "warning",
          title: "Aviso",
          text:
            "Existen productos sin stock para realizar la operación. Verifique."
        });
        return false;
      }
      if (!this.idAlmacenOrigen) {
        this.$swal({
          icon: "warning",
          title: "Aviso",
          html:
            "Es necesario un <strong> Almacén de Origen </strong>. Verifique."
        });
        return false;
      }
      if (!this.idAlmacenDestino) {
        this.$swal({
          icon: "warning",
          title: "Aviso",
          html:
            "Es necesario un <strong> Almacén de Destino </strong>. Verifique."
        });
        return false;
      }

      this.producto.cantidad = this.cantidad;
      let data = {
        producto: this.producto,
        detalle: this.lstProducto,
        idAlmacenOrigen: this.idAlmacenOrigen,
        idAlmacenDestino: this.idAlmacenDestino
        // Observacion:
      };
      await this.axios
        .post(this.API + "/movimientos/guardar_producto_presentacion", data)
        .then(res => {
          console.log(res);
        })
        .catch();
      this.$swal({
        icon: "success",
        title: "Registro Correcto",
        allowallowOutsideClick: false,
        allowEscapeKey: false,
        confirmButtonText: "Aceptar"
      }).then(res => {
        if (res.isConfirmed) {
          window.location.reload();
        }
      });
    }
  }
};
</script>

<style>
</style>
