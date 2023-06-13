<template>
    <div>
      <b-form-group>
        <label>Agrega Productos:</label>
        <b-input-group>
          <autocomplete
            :url="API + 'productos'"
            ref="inputProducto"
            anchor="Producto"
            :onShouldRenderChild="renderChild"
            label="writer"
            placeholder="Seleccionar Producto"
            :on-select="addProducto"
            :onFocus="focusAutocomplete.bind(null, 'inputProducto')"
            :classes="{input: 'form-control upper'}">
          </autocomplete>
        </b-input-group>
      </b-form-group>
      <table class="table b-table table-sm">
        <thead>
          <tr>
            <th>Id</th>
            <th>Producto</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th class="text-center">Quitar</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(prod, index) in productos" v-show="!prod.isDisabled" :key="index">
            <td>{{ prod.IdProducto }}</td>
            <td>{{ prod.Producto }}</td>
            <td>
              <span class="hideInModal">{{ prod.Precio }}</span>
              <input :disabled="prod.isDisabled" step="0.01" required type="number" class="form-control" v-model="prod.Precio" @input="emitUpdate(prod.IdProducto,prod)" />
            </td>
            <td>
              <span class="hideInModal">{{ prod.Cantidad }}</span>
              <input :disabled="prod.isDisabled" required type="number" class="form-control" v-model="prod.Cantidad" @input="emitUpdate(prod.IdProducto,prod)" />
            </td>
            <td class="text-center"><b-button :disabled="prod.isDisabled" variant="danger" size="sm" @click="quitarProducto(prod)">X</b-button></td>
          </tr>
        </tbody>
      </table>
    </div>    
  </template>
  <script>
  import config from '@/store/config.js'
  
  export default {
    mounted () {
      
      this.getProductos()
    },
    data () {
      return {
        API: config.API,
        productosACargar: [],
        productos: [],
        totalProductos:0
      }
    },
    props: ['idProyecto'],
    computed: {
      sumProductos() {
        return this.totalProductos;
      },
    },
    watch: {
      productos (prods) {
        let sum = 0;
        this.productos.map(prod => {
          sum += (prod.Cantidad * prod.Precio) || 0
        })
        this.totalProductos=sum
        this.$emit('onChangeProductos', this.totalProductos) 
      }
    },
    methods: {
      emitUpdate (idProducto,prod) {
       let index = this.productos.findIndex( p => p.IdProducto === idProducto )
       let prod2 = Object.assign({}, prod);
       this.productos.splice(index, 1, prod2)
      },
      getProductos () {
        if (this.idProyecto) {
          this.axios.get(this.API + 'proyecto/detalle', {
            params: {IdProyecto: this.idProyecto}
          }).then(res => {
  
            res.data.map(pc => {
                this.productos.push(pc)
            })
  
          }).catch(err => {
            console.log(err)
          })
        }
      },
      renderChild(data) {
        return `
          <b>${data.IdProducto} - ${data.Producto} - ${data.CodigoBarra}</b>
        `
      },
      addProducto (data) {
        let index = this.productos.findIndex(el => el.IdProducto == data.IdProducto)
  
        if (index >= 0) {
          this.$toasted.show('El producto ya está agregado')
          return
        }
  
        data.Precio = data.PrecioContado
        
        this.productos.push(data)
        this.$refs.inputProducto.setValue('')
        this.$toasted.success("Producto agregado")
  
      },
      quitarProducto (prod) {
        let index = this.productos.findIndex(el => el.IdProducto == prod.IdProducto)
        this.productos.splice(index, 1)
        //this.emitUpdate()
        this.$toasted.error("Producto quitado del listado")
      },
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
    }
  }
  </script>
  <style scoped>
    .hideInModal {
      display: none;
    }
    .form-check-input {
      margin-left: 0.75rem !important;
    }
  </style>