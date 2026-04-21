<template>
    <div>
      <!-- Modal Component -->
      <b-form @submit="guardarCategoria" @keydown.enter="keyDownInput" autocomplete="off" >
      <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalProducto" :title="title" size="lg" ref="productoForm" v-model="show">
          <b-col md="6">
            <b-form-group>
              <label>Categoria:</label>
              <b-form-input
                v-model="categoria.ProductoCategoria"
                @input="(val) => categoria.ProductoCategoria = val && val.toUpperCase()"
                type="text"
                required="required"
                ></b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="6">
            <b-form-checkbox
              v-model="categoria.MostrarPuntoVenta"
              :value="1"
              :unchecked-value="0">
              Mostrar en PuntoVenta
            </b-form-checkbox>
          </b-col>
          <b-col md="6" style="margin-top:10px">
            <b-form-group>
              <label>Orden:</label>
              <b-form-input
                v-model="categoria.Orden"
                type="number"
                required="required"
                ></b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="6">
            <b-form-group>
              <label><b>Porcentaje Adicional Nocturno:</b></label>
              <b-form-input
                v-model="categoria.PrecioNocturno"
                type="number"
                step="0.1"
                placeholder="0.0"
              ></b-form-input>
              <b-form-text>Ingrese el porcentaje(%) para habilitar la tarifa nocturna.</b-form-text>
            </b-form-group>
          </b-col>
          <div slot="modal-footer">
          <b-btn variant="primary" type="submit">
             Guardar
           </b-btn>
           <b-btn  variant="secondary" @click="show=false">
             Cerrar
           </b-btn>
         </div>
      </b-modal>
      </b-form>
    </div>
  </template>
  <script>
  import config from '@/store/config.js'
  // import ModeloForm from '@/components/ModeloForm'
  export default {
    data () {
      return {
        title: 'Nueva Categoria',
        API: config.API,
        show: false, 
        // generos: ['VARON', 'DAMA', 'NIÑO', 'NIÑA', 'UNISEX', 'JUNIOR'],
        categoria: {},
        nuevo: false
      }
    },
    props: ['categoriaUpdate', 'cbGuardar'],
    watch: {
      show (val) {
        let newVal = this.categoriaUpdate  || {}
        if (newVal.IdProductoCategoria && !this.nuevo) {
          this.initForm()
          this.title='Editar Categoria << '+newVal.IdProductoCategoria+' >>'
          //this.categoria.IdImpresora = newVal.IdImpresora
          this.categoria.ProductoCategoria=newVal.ProductoCategoria
          this.categoria.IdProductoCategoria = newVal.IdProductoCategoria
          //this.categoria.MostrarDelivery = newVal.MostrarDelivery
          this.categoria.MostrarPuntoVenta = newVal.MostrarPuntoVenta
          this.categoria.PrecioNocturno = newVal.PrecioNocturno
          this.categoria.Orden = newVal.Orden
        } else {
          this.title='Nueva Categoria'
          this.initForm();
        }
      }
    },
    methods: {
      initForm () {
        this.categoria = {
            ProductoCategoria: '',
            //IdImpresora: null,
            //MostrarDelivery: 0,
            MostrarPuntoVenta:0,
            PrecioNocturno:0,
            Orden:0,
        }
      },
      checkPrecioNocturno() {
        if (parseFloat(this.categoria.MontoNocturno) > 0) {
          this.categoria.PrecioNocturno = 1;
        } else {
          this.categoria.PrecioNocturno = 0;
        }
      },
      guardarCategoria(e) {
        e.preventDefault();
        this.axios.post(this.API + 'categorias/nuevo', {
          ...this.categoria, 
        })
        .then((response) => {
          if (this.cbGuardar) {
            this.cbGuardar(response.data.insertId)
          }
          if (response.data.error) {
            this.$toasted.show(response.data.error + ' - ' + response.data.codigoBarra)
            return
          } else {
            this.$toasted.info('Categoria guardada correctamente')
          }
          this.$refs.productoForm.hide()
        })
        .catch((error) => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error o la categoria ya existe, vuelva a intentarlo')
        })
      },
    },
    computed : {
    },
    components: {
    }
  }
  </script>
  <style>
  </style>