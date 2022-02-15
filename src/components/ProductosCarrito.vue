<template>
  <b-row>
    <b-col md="12">
      <b-form-group>
        <label>Agregar Productos:</label>
        <b-input-group>
          <autocomplete
            ref="autocompleteProducto"
            :url="APIProductos"
            :customParams="{'sortBy':isVentas?'stock':'IdProducto', 'sortDesc': 1 , 'limit':15}"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder="Agregar Producto"
            :on-select="setProducto"
            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
            :classes="{input: 'form-control'}">
          </autocomplete>
          <input v-model="loadCode"
                  :disabled="bloquearInput?true:false || isNota"
                  @keydown.enter.prevent="loadBarcode"
                  type="text"
                  class="form-control"
                  placeholder="Codigo de Barra" />
          <b-input-group-append>
            <!--<proveedores-tabla></proveedores-tabla>-->
            <producto-form  v-if="false" :showButton="showButton" :cbGuardar="(id) => add([id])"></producto-form>
          </b-input-group-append>
        </b-input-group>
          <b-badge variant="danger">Productos Vencidos</b-badge>
          <b-badge variant="warning">proximos a vencer 4 meses</b-badge>
      </b-form-group>
      <b-table small class="tabla-carrito" responsive  striped hover :items="productos" :fields="fields" ref="tablaCarrito">
        <template slot="stock" slot-scope="data">
          <div v-if="!data.item.ControlaStock && productosCaja[data.item.IdProducto]" style="font-size:17px; font-weight:bold">
            {{productosCaja[data.item.IdProducto].stock}}
          </div>
          <div v-if="data.item.ControlaStock" style="font-size:17px; font-weight:bold">
            {{ data.item.stock }}
          </div>
          
        </template>
        
        <template slot="cantidad" slot-scope="data">
          <input
            v-model="productos[data.index].cantidad"
            :disabled="bloquearInput?true:false"
            type="number"
            style="width: 85px"
            min="0"
            @input="(val) => {
              $emit('onInputCantidad', val.target.value)
              if(isVentas){
                productos[data.index].precio = (val.target.value >= productos[data.index].StockPorMayor && productos[data.index].StockPorMayor !== 0) ? productos[data.index].PrecioPorMayor : productos[data.index].PrecioContado
              }   
            }"
            placeholder="Cantidad"
            class="form-control"/>
        </template>
        <template slot="precio" slot-scope="data">
          <input
            v-model="productos[data.index].precio"
            :disabled="bloquearInput?true:false"
            type="number"
            style="width: 110px"
            min="0"
            step="0.00001"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)"
            class="form-control"/>
        </template>
        <template slot="subtotal" slot-scope="data">
          <div style="font-size:17px">{{ getSubtotal(data.index) }}</div>
        </template>
        <template slot="igv" slot-scope="data">
          <b-form-checkbox
            v-model="productos[data.index].TieneIgv"
            :disabled="bloquearInput?true:false"
            :value="1"
            @input="setNuevoPrecioContado(data.index)"
            :unchecked-value="0">
            <div style="font-size:15px">{{ getIgv(data.index) }}</div>
          </b-form-checkbox>
        </template>
        <!--<template slot="flete" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].FLETE"
            type="number"
            min="0"
            placeholder="Flete"></b-form-input>
        </template>-->
        
        <!--<template slot="lote" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].IdLote"
            type="text"
            min="0"
            placeholder="Lote"></b-form-input>
        </template>-->

        <template slot="movimiento" slot-scope="data" v-if="(movTipo!=0 && movTipo!=undefined ) || isVentas">
          <div v-if="isNota" style="width: 135px; text-align:center">
            <div>
              {{productos[data.index].hashMovimiento}}
            </div>
            <small>Doc: <span style="font-weight:bold;"> {{productos[data.index].Serie}}-{{productos[data.index].Numero}}</span></small><br />

            <div>
              {{productos[data.index].FechaVen}}
            </div>
          </div>
          <b-form-select v-if="!isNota"
            style="width: 135px;"
            :disabled="bloquearInput?true:false"
            @change="(val) => changeMovimiento(val, data.item.IdProducto, data.index)"
            v-model="productos[data.index].hashMovimiento" :options="movimientoLotes[data.item.IdProducto]"></b-form-select>
          <br>
          <div style="text-align:center;" v-if="!isNota">
                <small style="font-size:14px;">S: <span style="font-weight:bold;">{{ productos[data.index].stockMovimiento }}</span> -  D: <span style="font-weight:bold; text-transform:uppercase">{{ productos[data.index].SerieNumero }}</span></small><br />
          </div>
          <div style="font-size:19px" v-if="!isNota" v-html="productos[data.index].fechaVenMovimiento"></div>
        </template>

        <template slot="Lote" slot-scope="data" >
          <b-form-input
            required="required"
            v-model="productos[data.index].Lote"
            type="text"
            ></b-form-input>
        </template>

        <template slot="fechavencimiento" slot-scope="data" >
          <b-form-input
            required="required"
            v-model="productos[data.index].FechaVen"
            type="date"
            ></b-form-input>
        </template>

        <template slot="precioVenta" slot-scope="data">
          <input
            style="width:80px"
            v-model="productos[data.index].nuevoPrecioContado"
            :disabled="bloquearInput?true:false"
            type="number"
            min="0"
            step="0.01"
            placeholder="Precio Venta"
            class="form-control"/>
            <b-form-text style="font-size:16px;">
              <strike> {{ productos[data.index].normalPrecioContado }} </strike>
            </b-form-text>
        </template>

        <template slot="descuento" slot-scope="data">
          <input
            v-model="productos[data.index].descuento"
            :disabled="bloquearInput?true:false"
            type="number"
            min="0"
            step="0.01"
            value="0"
            class="form-control"/>
            <b-form-text>
            </b-form-text>
        </template>

        <template slot="total" slot-scope="data">
          <div style="font-size:17px">{{ getTotal(data.index) }}</div>
        </template>
        <template slot="acciones" slot-scope="data">
          <b-button variant="danger" size="sm" :disabled="bloquearInput?true:false" @click="quitarProducto(data.index)">X</b-button>
        </template>

        <template slot="show_details" slot-scope="row">
           <b-button size="sm" variant="info" :disabled="bloquearInput?true:false" @click.stop="row.toggleDetails">></b-button>
        </template>
        <template slot="row-details" slot-scope="row">

        </template>

      </b-table>
    </b-col>
    <b-col md="3">
      <slot name="operaciones"></slot>
    </b-col>
    <b-col md="4">
      <slot name="metodopago"></slot>
    </b-col>
    <b-col md="3" offset-md="2">
          <table class="table b-table border table-footer">
            <tbody>
              <tr v-if="sumatoriaDescuento > 0">
                <td class="table-success">SubTotal</td>
                <td class="table-success">{{ parseFloat(sumatoriaSubTotal) + parseFloat(sumatoriaDescuento) | redondear }}</td>
              </tr>
              <tr v-if="sumatoriaDescuento > 0">
                <td class="table-danger">Descuento</td>
                <td class="table-danger">- {{ sumatoriaDescuento }}</td>
              </tr>
              <tr>
                <td><b>SubTotal</b></td>
                <td class="table-danger">{{ sumatoriaSubTotal }}</td>
              </tr>
              <tr v-if="sumatoriaIgv > 0">
                <td><b>IGV</b></td>
                <td class="table-danger">{{ sumatoriaIgv }}</td>
              </tr>
              <tr>
                <td><b>TOTAL</b></td>
                <td class="table-danger">{{ sumatoriaTotal }}</td>
              </tr>
              <tr>
                <td  colspan="2">
                  <label>pagó con:</label>
                  <b-form-input
                    type="text"
                    placeholder="Pagó con"
                    v-model="pagocon"
                    :disabled="bloquearInput?true:false"
                    @input="asignarVuelto"></b-form-input><br>
                  <label>Vuelto:</label>
                  <b>S/  {{ vuelto | redondear }}</b>
                </td>
              </tr>
            </tbody>
          </table>
    </b-col>
    <br><br>
  </b-row>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import ProductoForm from './ProductoForm'
import config from '@/store/config.js'
import axios from 'axios'

export default {
  mounted() {
    // this.alterarBuscadorProductos()
    if(this.$route.query.idProducto) {
        this.add([this.$route.query.idProducto, (obj) => {}, { setPrecio: this.setPrecio,  setSuma: true }])
    }
  },
  data () {
    return {
      API: config.API,
      loadCode: '',
      pasteCode: false,
      producto: {},
      productosCaja: {},
      items: [],
      handleKeyDown: false,
      vuelto: 0,
      pagocon: 0,
      idPerfil: sessionStorage.getItem('IdPerfil'),
      movimientoLotes: {},
    }
  },
  watch: {
    sumatoriaTotal (sum) {
      this.asignarVuelto()
    }
  },
  props: {
    setPrecio: Boolean,
    idAlmacen: Number,
    bloquearInput:Boolean,
    igvTotal: Boolean,
    isVentas: Boolean,
    isNota:Boolean,
    movTipo: Number,
    almacenDestino:Number,
    almacenOrigen:Number,
    onSetProducto: {
      type: Function,
      default (obj) {
        return obj
      }
    },
    showButton: {
      default: true,
      type: Boolean
    },
    fields: {
      default () {
        return [
          { key: 'stock', label: 'Stock', variant: 'info', class:'text-center stocksize' },
          { key: 'movimiento', label: 'Lote', variant: 'primary', class:'text-center stocksize'  },
          //{ key: 'ProductoCategoria', label: 'Categoria'},
          { key: 'Producto', label: 'Artículo', class:'stocksize' }, 
          { key: 'ProductoMarca', label: 'Marca', class:'stocksize' },
          //{ key: 'ProductoModelo', label: 'Modelo' }, 
          //{ key: 'Color', label: 'Color' }, 
          //{ key: 'ProductoTalla', label: 'Talla' },
          //{ key: 'Genero' },
          //{ key: 'Botapie' },
          { key: 'cantidad', class:'stocksize' },
          { key: 'precio', label: 'P/U', class:'stocksize' },
          { key: 'subtotal', label: 'SubTotal', variant: 'success', class:'stocksize' },
          { key: 'igv', label: 'IGV', class:'stocksize' },
          // { key: 'flete', label: 'FLETE' },
          // { key: 'lote', label: 'Lote' },
          { key: 'precioVenta', label: 'Precio venta', class:'stocksize' },
          { key: 'total', label: 'Total', variant: 'danger', class:'stocksize' },
          { key: 'Lote', label: 'C. Lote', class:'stocksize' },
          { key: 'fechavencimiento', label: 'Vencimiento', class:'stocksize' },
          { key: 'acciones', label: '', class:'stocksize'}
        ]
      },
      type: Array
    }
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      filtrados: state => state.cart.productosFiltrados
    }),
    APIProductos () {
      if (this.idAlmacen) {
        return this.API + 'productos/stock/' + this.idAlmacen
      } else {
        return this.API + 'productos'
      }
    },
    sumatoriaSubTotal () {
      let sumatoria = 0
      if (this.igvTotal) {
        sumatoria = this.sumatoriaTotal - this.sumatoriaIgv
      } else {
        this.productos.map((value) => {
          sumatoria += parseFloat(value.subtotal)
          if (value.descuento) {
            sumatoria -= value.descuento
          }
        })
      }
      return this.$options.filters.redondear(sumatoria)
    },
    sumatoriaTotal () {
      let sumatoria = 0
      this.productos.map((value) => {
        sumatoria += parseFloat(value.total)
      })
      
      this.actualizarTotal(sumatoria)
      return this.$options.filters.redondear(sumatoria)
    },
    sumatoriaIgv () {
      let sumatoria = 0
      if (this.igvTotal) {
        sumatoria = this.sumatoriaTotal
        // sumatoria = sumatoria * 0.18
        sumatoria = sumatoria - (sumatoria/1.18)
      } else {
        this.productos.map((value) => {
          sumatoria += parseFloat(value.igv)
        })
      }
      return this.$options.filters.redondear(sumatoria)
    },
    sumatoriaDescuento () {
      let sumatoria = 0
      this.productos.map((value) => {
        sumatoria += parseFloat(value.descuento || 0)
      })
      return this.$options.filters.redondear(sumatoria)
    }
  },
  methods: {
    ...mapActions({
      add: 'agregarProducto',
      buscar: 'buscarProductos',
      eliminar: 'eliminarProducto',
      actualizarTotal: 'actualizarTotalCart'
    }),
    changeMovimiento(hash, idProducto, index) {
      if  (hash) {
        // Stock
        let stock = this.movimientoLotes[idProducto].find(m => m.value === hash).Stock
        let serieNumero = this.movimientoLotes[idProducto].find(m => m.value === hash).SerieNumero
        let IdLote = this.movimientoLotes[idProducto].find(m => m.value === hash).IdLote
        let Lote = this.movimientoLotes[idProducto].find(m => m.value === hash).Lote
        if (stock) {
          // this.productos[index].stockMovimiento = stock
          this.$nextTick(() => {
            Vue.set(this.productos[index], 'stockMovimiento', stock)
            Vue.set(this.productos[index], 'SerieNumero', serieNumero)
          })
        } else {
          this.$nextTick(() => {
            Vue.set(this.productos[index], 'stockMovimiento', 0)
            Vue.set(this.productos[index], 'SerieNumero', '----')
          })
        }
        // Fecha de vencimiento
        var fechaVen = this.movimientoLotes[idProducto].find(m => m.value === hash).FechaVen
        let hoy      = this.$moment().unix();
        let fechaVenUnix = this.$moment(fechaVen).unix();
        let en4meses  = this.$moment().add(4,'months').unix();
        let style = 'bg-success'
        if(fechaVenUnix && fechaVenUnix < hoy) {
          style = 'bg-danger text-white'
        }
        if (fechaVenUnix >= hoy && fechaVenUnix <= en4meses) {
          style = 'bg-warning'
        }
        if (fechaVen) {
          this.$nextTick(() => {
            if(!this.isVentas){
              if(this.movTipo!=0 &&  this.movTipo!=undefined ){
               this.productos[index].FechaVen = this.$moment(fechaVen).format("YYYY-MM-DD")
               this.productos[index].IdLote = IdLote 
               this.productos[index].Lote = Lote 
                //console.log(this.$moment(fechaVen).format("YYYY-MM-DD"));
                //Vue.set(this.productos[index], 'fechaVen', this.$moment(fechaVen).format("YYYY-MM-DD"))
              }
            }else{
                 this.productos[index].Lote = Lote 
              }
             Vue.set(this.productos[index], 'fechaVenMovimiento', `<small style="font-weight:bold" class='venci ${style}'>${ this.$moment(fechaVen).format("DD-MM-YYYY") }</small>`)
          })
        } else {
          this.$nextTick(() => {
            Vue.set(this.productos[index], 'fechaVenMovimiento', '')
          })
        }
          // return `<small class='venci ${style}'>Venci: ${fechaVen}</small>`
      }
    },
    loadMovimientoLotes ($idProducto) {
      this.axios.get(this.API + '/movimientolotes/' + $idProducto+'/'+(this.isVentas?this.idAlmacen:(this.movTipo==0?this.almacenDestino:this.almacenOrigen)))
        .then((response) => {
          let indexProducto = this.productos.findIndex(p => p.IdProducto === $idProducto);
          // Cargar por defecto lote próximo a vencer.
          let primeroVencer = response.data.reduce((a,b) => {
            return (a.FechaVen && (new Date(a.FechaVen) < new Date(b.FechaVen))) ? a : b;
          })
          
          this.$nextTick(() => {
            Vue.set(this.productos[indexProducto], 'hashMovimiento', primeroVencer.hashMovimiento);
            this.changeMovimiento(primeroVencer.hashMovimiento, $idProducto, indexProducto)
          })
          
          this.movimientoLotes[$idProducto] = response.data.map(l => ({
            value: l.hashMovimiento,
            text : l.Lote,
            Stock : l.Stock,
            IdLote: l.IdLote,
            Lote: l.Lote,
            FechaVen: l.FechaVen,
            SerieNumero: l.Serie + '-' + l.Numero
          }))
        })
        .catch((error) => {
          console.log(error)
        })
    },
    asignarVuelto () {
      this.vuelto = this.pagocon - this.sumatoriaTotal
    },
    loadBarcode (e) {
      e.preventDefault()
      let idProducto = this.loadCode;

      if (idProducto) {
        this.add([idProducto, (obj) => {
          if (obj) {
            this.$emit('onAddProducto')
            this.loadMovimientoLotes(obj.IdProducto)
            this.$toasted.show('Producto añadido correctamente')
          } else {
            this.$toasted.show('No se encontró ningun producto')
          }
          this.loadCode = ''
        }, { setPrecioCosto: (this.isVentas ? false : true),  setSuma: true }, true])
      }
    },
    /*alterarBuscadorProductos() {
      this.handleKeyDown = this.$refs.autocompleteProducto.handleKeyDown

      this.$refs.autocompleteProducto.$refs.input.onpaste = (e) => {
        console.log('paste')
        this.pasteCode = (e.originalEvent || e).clipboardData.getData('text/plain')
      }

      this.$refs.autocompleteProducto.handleKeyDown = (e) =>{
        let key = e.keyCode;

        if (key == 13) {
          if(this.pasteCode) {
            e.preventDefault()
            let idProducto = this.pasteCode.substring(2, this.pasteCode.length -2);
            this.add([idProducto, () => {
              this.$emit('onAddProducto')
            }])
          } else {
            e.preventDefault()
            this.handleKeyDown(e) 
          }   
        } else {
          this.handleKeyDown(e)
        }
        this.pasteCode = false
      }
    },*/
    obtenerStockCaja(prod) {
     axios.get(this.API + 'productos/stock/caja', {
        params: {
          idProducto: prod.IdProducto,
          idAlmacen: this.idAlmacen
        }
      }).then((response) => {
        Vue.set(this.productosCaja, prod.IdProducto, {stock: response.data.stock})
        //console.log(response.data.stock)
          //return response.data.stock
      })
      .catch((error) => {
        console.log(error)
      })
    },
    focusAutocomplete (ref) {
      this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },  
    setProducto (obj) {
 
      //this.obtenerStockCaja(obj)

      if(!this.isVentas){
        if(this.movTipo==undefined){
          this.$toasted.show('Primero seleccione un tipo de movimiento')
          return
        }
      }

      this.$refs.autocompleteProducto.setValue('')
      //obj.FLETE = 0
      obj.precio = 0
      obj.cantidad = 0
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0
      obj.nuevoPrecioContado = 0
      obj = this.onSetProducto(obj)
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        this.loadMovimientoLotes(obj.IdProducto)
      },{ setPrecioCosto: (this.isVentas ? false : true)}])

      // Vue.set(this.productos, obj.IdProducto, obj)
    },
    renderChild(data) {
      if (this.idAlmacen) {
        let style = 'ss'

        // cambiar color los vencidos
        var dt = new Date()
        var anio = dt.getFullYear()
        var mes =  dt.getMonth() + 1
        var dia = dt.getDate()
        var fechaVen = new Date(data.FechaVen).getTime()
        
        var hoy = anio + "-" + mes + "-" + dia
        hoy = new Date(hoy).getTime()
        var en4meses = anio + '-' + (mes + 4) + '-' + dia
        en4meses = new Date(en4meses).getTime()

        // if(fechaVen < hoy && data.stock > 0) {
        if(fechaVen && fechaVen < hoy) {
          style = 'danger'
        }

        // cambiar color los que faltan 4 meses por vencer
        // if (fechaVen >= hoy && fechaVen <= en4meses && data.stock > 0) {
        if (fechaVen >= hoy && fechaVen <= en4meses) {
          style = 'ambar'
        }

        let styleStock = ''

        if(data.stock>0){
          styleStock = 'table-success'
        }else{
          styleStock = 'table-danger'
        }

        // if (!data.ControlaStock) {
        //   data.stock = '---'
        // }

//<b class="bg-success" style="color: #fff; padding: 0 6px">${data.stock}</b> | 
        return `
        <style>
          #table-render {
            font-size: 13px;
            margin-bottom: 5px;
            margin-top: 5px;
          }
          #table-render td, #table-render th {
            border: 1px solid #eaeaea;
          }
          #table-render td, #table-render th {
            color: #000000 !important;
          }
        </style>

        <div class="${style}">
          <table id="table-render" style="width: 830px; text-align: center  ">
            <thead>
              <tr style>
                <th class="table-info" style="width: 60px">Stock</th>
                <th>Lab.</th>
                <th>Forma.</th>
                <th>Medición.</th>
                <th style="width: 200px">Producto.</th>
                <th style="width: 200px">Compuestos</th>
                <th class="table-success" style="width: 65px">Precio.</th>
                <th>V.E.</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="${styleStock}"><b>${data.stock}</b></td>
                <td>${data.ProductoMarca}</td>
                <td>${data.ProductoFormaFarmaceutica}</td>
                <td>${data.ProductoMedicion}</td>
                <td><b>${data.Producto}</b></td>
                <td style="font-size:10px">${data.CompuestosProducto}</td>
                <td class="table-success"><b>${data.PrecioContado}</b></td>
                <td><b>${data.VentaEstrategica==1?'<i class="fas fa-star text-warning"></i>':''}</b></td>
              </tr>
            </tbody>
          </table>   
        </div>
        `
      }
      return `
        <b>${data.CodigoBarra}</b> / <b>${data.Producto}</b> / ${data.ProductoFormaFarmaceutica} / ${data.ProductoMarca} 
      `
    },
    setNuevoPrecioContado (index) {

      // Añadir IGV si check IGV
      let precio = parseFloat(this.productos[index].precio)
      if(this.productos[index].TieneIgv) {
        precio += (precio * 0.18)
      }

      precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      // let precio = parseFloat(this.productos[index].precio) + (this.productos[index].precio * (this.productos[index].PorcentajeUtilidad/100))


      let normalPrecio = this.$options.filters.redondear(precio)
      let nuevoPrecio = this.$options.filters.redondeoMayor(precio)
      
      Vue.set(this.productos[index], 'normalPrecioContado', normalPrecio)
      Vue.set(this.productos[index], 'nuevoPrecioContado', nuevoPrecio)
      // this.productos[index].nuevoPrecioContado = nuevoPrecio
      // return nuevoPrecio 
    },
    getSubtotal(index) {
      
      let subtotal = this.$options.filters.redondear(this.productos[index].cantidad * this.productos[index].precio)
      Vue.set(this.productos[index], 'subtotal', subtotal)
      // this.productos[index].subtotal = subtotal
      return subtotal
    },
    getIgv (index) {
      let igv = 0
      if (this.productos[index].TieneIgv) {
        igv = this.$options.filters.redondear(this.productos[index].subtotal * 0.18)
      }

      Vue.set(this.productos[index], 'igv', igv)
      // this.productos[index].igv = igv
      return igv
    },
    getTotal (index) {
      let total = parseFloat(this.productos[index].subtotal)

      if (this.productos[index].FLETE) {
        total += parseFloat(this.productos[index].FLETE)
      }
      if (this.productos[index].TieneIgv) {
        total += parseFloat(this.productos[index].igv)
      }
      if (this.productos[index].descuento) {
        total -= parseFloat(this.productos[index].descuento)
      }

      if (total <= 0) {
        Vue.set(this.productos[index], 'descuento', 0)
        return
      }

      total =  this.$options.filters.redondear(total)
      Vue.set(this.productos[index], 'total', total)
      // this.productos[index].total = total
      return total
    },
    getData (value) {
      this.buscar(value)
    },
    quitarProducto (index) {
      this.eliminar(index)
      this.$emit('onRemoveProducto', index)
      this.$toasted.show('Producto quitado del listado')
    }
  },
  components: {
    ProductoForm
  }
}
</script>

<style>
.danger {
  background-color:#ff7675;
  color: #ffffff;
}
.ambar {
  background-color: #feca57;
  color: #ffffff;
}
.tabla-carrito .table-sm td  {
  font-size: .8rem!important;
}

td.stocksize{
  text-align: center !important; 
  vertical-align: middle !important;
}
</style>

