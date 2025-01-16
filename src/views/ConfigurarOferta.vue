<template>
  <b-container fluid>
    <b-row>
      <b-col cols="12"> Ofertas</b-col>
      <b-col cols="12" style="text-align: end;"> 
        <b-button variant="success" class="my-2"> Nueva Oferta </b-button>
      </b-col>
      <b-col cols="12"> 
        <b-table :fields="headers" :items="lstOferta"></b-table>
      </b-col>

    </b-row>
    <b-modal v-model="nuevaOfertaFlag" size="lg" title="Nueva Oferta" hide-footer>
      <b-row>
        <b-col cols="12">
              <b-form-checkbox v-model="formulario.producto" unchecked-value="0" value="1"> Producto </b-form-checkbox>
              <b-form-checkbox v-model="formulario.monto" unchecked-value="0" value="1"> Monto </b-form-checkbox>
              <b-form-checkbox v-model="formulario.cantidad" unchecked-value="0" value="1"> Cantidad </b-form-checkbox>
        </b-col>
        <b-col cols="12" v-if="mostrarSiguientePaso()">
          <b-row>
            <b-col cols="12" v-if="formulario.producto"> 
              <autocomplete
              ref="autocompleteProducto"
              :url="APIProductos"
              :onShouldRenderChild="renderChild"
              anchor="Producto"
              label="writer"
              placeholder="Agregar Producto"
              :on-select="setProducto"
              :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
              :classes="{input: 'form-control'}">
            </autocomplete>
            </b-col>
            <b-col cols="12" v-if="formulario.monto"> Monto
              <input type="number" v-model="formulario.Monto" class="form-control">

            </b-col>
            <b-col cols="12" v-if="formulario.cantidad"> Cantidad
              <input type="number" v-model="formulario.Cantidad" class="form-control">

            </b-col>
          
            <b-col cols="12">
              Descripcion
              <input type="text" v-model="formulario.descripcion" class="form-control">  
            </b-col>
            <b-col cols="12">
              Porcentaje
              <input type="number" v-model="formulario.porcentaje" class="form-control">  
            </b-col>
            <b-col cols="6">
              Desde
              <input type="date" v-model="formulario.desde" class="form-control"> 

            </b-col>
            <b-col cols="6">
              Hasta
              <input type="date" v-model="formulario.hasta" class="form-control"> 

            </b-col>
            <b-col cols="12">
              Stockmax
              <input type="number" v-model="formulario.stockmax" class="form-control">    
            </b-col>
            
          </b-row>
        </b-col>
      </b-row>
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      nuevaOfertaFlag: true,
      formulario: {
        producto: 0,
        monto: 0,
        cantidad: 0
      },
      headers: [
        { key: "tipo" },
        { key: "Producto" },
        { key: "Monto" },
        { key: "cantidad" },
        { key: "descripcion" },
        { key: "porcentaje" },
        { key: "desde" },
        { key: "hasta" },
        { key: "stockmax" },
        { key: "estado" }
      ],
      lstOferta: []
    };
  },
  methods: {
    mostrarSiguientePaso() {
      let val =
        this.formulario.producto ||
        this.formulario.monto ||
        this.formulario.cantidad;
      return val;
    },
    APIProductos() {
      if (this.idAlmacen) {
        return this.API + "productos/stock/" + this.idAlmacen;
      }
      return this.API + "productos";
    },
    renderChild(data) {
      console.log(data);
      if (this.idAlmacen) {
        let style = "ss";

        // cambiar color los vencidos
        var dt = new Date();
        var anio = dt.getFullYear();
        var mes = dt.getMonth() + 1;
        var dia = dt.getDate();
        var fechaVen = new Date(data.FechaVen).getTime();

        var hoy = anio + "-" + mes + "-" + dia;
        hoy = new Date(hoy).getTime();
        var en4meses = anio + "-" + (mes + 4) + "-" + dia;
        en4meses = new Date(en4meses).getTime();

        if (fechaVen < hoy && data.stock > 0) {
          style = "danger";
        }

        // cambiar color los que faltan 4 meses por vencer
        if (fechaVen >= hoy && fechaVen <= en4meses && data.stock > 0) {
          style = "ambar";
        }

        return `
        <div class="${style}">
          <b class="bg-success" style="color: #fff; padding: 0 6px">${
            data.stock
          }</b> | <b>${data.CodigoBarra}</b> / 
          <b>${data.Producto}</b> / ${data.ProductoMarca} 
        </div>
        `;
      }
      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${
        data.ProductoMarca
      } / ${data.ProductoMedicion}
      `;
    },
    setProducto(obj) {
      console.log(obj);
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    }
  }
};
</script>

<style>
</style>
