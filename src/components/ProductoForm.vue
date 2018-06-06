<template>
  <div>
    <b-btn v-b-modal.modalProducto variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-modal id="modalProducto" title="Nuevo Producto" size="lg" ref="productoForm" v-model="show">
      <b-row>
        <b-col md="6">
          <b-form-group>
            <label>Categoría:</label>
            <autocomplete
              url="http://boticav1.local/api/index.php/categorias"
              anchor="ProductoCategoria"
              label="writer"
              placeholder="Categoría"
              :on-select="setCategoria"
              :classes="{input: 'form-control'}">
            </autocomplete>
          </b-form-group>
          <b-form-group>
            <label>Marca:</label>
            <autocomplete
              url="http://boticav1.local/api/index.php/marcas"
              anchor="ProductoMarca"
              label="writer"
              placeholder="Marca"
              :on-select="setMarca"
              :classes="{input: 'form-control'}">
            </autocomplete>
          </b-form-group>
          <b-form-group>
            <label>Producto:</label>
            <b-form-input v-model="producto.Producto"
              type="text"
              placeholder="Producto"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Genero:</label>            
            <b-form-select v-model="producto.Genero" :options="generos" />
          </b-form-group>
          <b-form-group>
            <label>Botapie:</label>
            <b-form-input v-model="producto.Botapie"
              type="text"
              placeholder="Botapie"></b-form-input>
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Medición:</label>
            <autocomplete
              url="http://boticav1.local/api/index.php/mediciones"
              initValue="UNIDAD"
              anchor="ProductoMedicion"
              label="writer"
              placeholder="Medición"
              :on-select="setMedicion"              
              :classes="{input: 'form-control'}">
            </autocomplete>
          </b-form-group>
          <b-form-group>
            <label>Modelo:</label>
            <autocomplete
              url="http://boticav1.local/api/index.php/modelos"
              anchor="ProductoModelo"
              label="writer"
              placeholder="Modelo"
              :on-select="setModelo"              
              :classes="{input: 'form-control'}">
            </autocomplete>
          </b-form-group>
          <b-form-group>
            <label>Colores:</label>
            <b-form-input v-model="producto.Color"
              type="text"
              placeholder="Colores"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Talla:</label>
            <autocomplete
              url="http://boticav1.local/api/index.php/tallas"
              anchor="ProductoTalla"
              label="writer"
              placeholder="Talla"
              :on-select="setTalla"              
              :classes="{input: 'form-control'}">
            </autocomplete>
          </b-form-group>
          <b-form-group>
            <label>Porcentaje Utilidad:</label>
            <b-form-input v-model="producto.PorcentajeUtilidad"
              type="number"
              placeholder="Porcentaje Utilidad"></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>
      <div slot="modal-footer" >
        <b-btn  variant="primary" @click="guardarProducto">
           Guardar
         </b-btn>
         <b-btn  variant="secondary" @click="show=false">
           Close
         </b-btn>
       </div>
    </b-modal>
  </div>
</template>
<script>
export default {
  data () {
    return {
      show: false, 
      generos: ['VARON', 'DAMA', 'NIÑO', 'NIÑA'],
      producto: {
        Anulado: 0,
        IdProductoMarca: null,
        IdProductoMedicion: null,
        IdProductoCategoria: null,
        IdProductoModelo: null,
        // IdProductoBotapie: null,
        IdProductoTalla: '',
        Producto: '',
        CodigoBarra: '',
        Color: '',
        PorcentajeUtilidad: '',
        Botapie: '',
        Genero: '',
        categoria: null,
        marca: null,
        modelo: null,
        talla: null,
        medicion: {
          IdProductoMedicion:6,
          ProductoMedicion:"UNIDAD"
        }
      }
    }
  },
  methods: {
    setCategoria (obj) {
      this.producto.categoria = obj
    },
    setMedicion (obj) {
      this.producto.medicion = obj
    },
    setMarca (obj) {
      this.producto.marca = obj
    },
    setModelo (obj) {
      this.producto.modelo = obj
    },
    setTalla (obj) {
      this.producto.talla = obj
    },
    guardarProducto() {
      this.axios.post('http://boticav1.local/api/index.php/productos', this.producto)
      .then((response) => {
        this.$toasted.show('Producto guardado correctamente')
        //this.$refs.productoForm.hide()
      })
      .catch((error) => {
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    }
  }
}
</script>
<style>

</style>
