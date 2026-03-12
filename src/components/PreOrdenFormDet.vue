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
        <tr v-for="(prod, index) in productos" v-show="!prod.isDisabled" :key="index" :class="{'bg-secondary text-white': prod.isDisabled}">
          <td>{{ prod.IdProducto }}</td>
          <td>{{ prod.Producto }}</td>
          <td>
            <span class="hideInModal">{{ prod.Precio }}</span>
            <input :disabled="prod.isDisabled" step="0.01" required type="number" class="form-control" v-model="prod.Precio" @input="emitUpdate" />
          </td>
          <td>
            <span class="hideInModal">{{ prod.Cantidad }}</span>
            <input :disabled="prod.isDisabled" required type="number" class="form-control" v-model="prod.Cantidad" @input="emitUpdate" />
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
      productos: []
    }
  },
  props: ['idPreorden', 'idProducto'],
  computed: {
    sumProductos() {
      var sum = 0;
      this.productos.map(prod => {
        sum += (prod.Cantidad * prod.Precio) || 0
      })
      return sum;
    },
    manoDeObraBruta () {
      let pManoObra = this.productos.find(p => p.CodigoBarra === 'MANODEOBRA');
      let precio = pManoObra ? pManoObra.PrecioMO : 0;
      return precio
    },
    manoDeObraMenosMat () {
      let pManoObra = this.productos.find(p => p.CodigoBarra === 'MANODEOBRA');
      let precio = pManoObra ? pManoObra.PrecioMOmM : 0;
      return precio
    },
  },
  watch: {
    productos (prods) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === 'MANODEOBRA');

      if (pi >= 0 && !this.productos[pi].Precio) {
        this.productos[pi].Precio = this.productos[pi].Precio
      }
    }
  },
  methods: {
    emitUpdate () {
      this.$emit('onUpdateProductos', this.productos)
    },
    getProductos () {
      if (this.idPreorden) {
        this.axios.get(this.API + 'preorden/detalle', {
          params: {idPreOrden: this.idPreorden}
        }).then(res => {
          this.productos = res.data.map(item => {
            item.PrecioMO = item.Precio
            item.PrecioMOmM = item.Precio
            if (item.IdProducto === this.idProducto) {
              item.isDisabled = true
            }
            return item
          })

          this.productosACargar.map(pc => {
            let producto = this.productos.find(pr => pr.IdProducto === pc.IdProducto)
            if (!producto) {
              this.productos.push(pc)
            }
          })
          this.emitUpdate();
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

      if (data.EsHabitacion) {
        this.$toasted.show('No puede ingresar este producto')
        return
      }
      data.Cantidad = 1;
      data.Precio = data.PrecioContado
      
      this.productos.push(data)
      this.emitUpdate()

    },
    quitarProducto (prod) {
      let index = this.productos.findIndex(el => el.IdProducto == prod.IdProducto)
      this.productos.splice(index, 1)
      this.emitUpdate()
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    addManoDeObra (index, e) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === 'MANODEOBRA');
      if ( pi < 0 ) {
        this.$toasted.error('Primero seleccione el producto MANODEOBRA')
        
        this.productos[index].EsManoDeObra = 0
        return ;
      }

      if (this.productos[index].EsManoDeObra) {
        e.target.parentNode.parentNode.style.backgroundColor = "#e7c4ff"  
        this.productos[pi].PrecioMOmM -= (parseFloat(this.productos[index].Precio) * parseFloat(this.productos[index].Cantidad))
        this.productos[index].disabled = true
      } else {  
        e.target.parentNode.parentNode.removeAttribute("style")
        this.productos[pi].PrecioMOmM += (parseFloat(this.productos[index].Precio) * parseFloat(this.productos[index].Cantidad))
        this.productos[index].disabled = false
      }
      this.emitUpdate()
    },
    updateManoDeObraNeta (index) {
      let pManoObra = this.productos.find(p => p.CodigoBarra === 'MANODEOBRA');
      let precio = pManoObra ? pManoObra.precio : 0;

      this.productos.filter(p => p.EsManoDeObra).map(p => {
          precio -= (parseFloat(p.precio) * parseFloat(p.Cantidad))
      })
      
      let pi = this.productos.findIndex(p => p.CodigoBarra === 'MANODEOBRA');

      if (pi >= 0 && index !== pi) {
        this.productos[pi].precio = precio
      }
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
