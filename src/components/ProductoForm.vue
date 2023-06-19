<template>
  <div>
    <b-btn v-if="showButton" @click="()=>nuevo=true" v-b-modal.modalProducto variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarProducto" @keydown.enter="keyDownInput" >
    <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalProducto" :title="title" size="lg" ref="productoForm" v-model="show">
      <b-row>
        <b-col md="6">
          <b-form-group>
            <label>Código barra:</label>
            <b-form-input 
              ref="inputCodigoBarra"
              v-model="producto.CodigoBarra"
              @input="(val) => producto.CodigoBarra = val && val.toUpperCase()"
              type="text"
              class="form-control upper"
              ></b-form-input>
              <b-form-text>Dejar en blanco para generar el codigo de barra</b-form-text>
          </b-form-group>
          <b-form-group>
            <label>Categoría:</label>
            <b-input-group>
              <autocomplete
                required="required"
                :url="API + 'categorias'"
                ref="inputCategoria"
                anchor="ProductoCategoria"
                label="writer"
                :placeholder="producto.categoria.ProductoCategoria"
                :on-select="setCategoria"
                :onFocus="focusAutocomplete.bind(null, 'inputCategoria')"
                :classes="{input: 'form-control upper'}">
              </autocomplete>
              <b-input-group-append>
                <b-btn variant="primary" @click="crearCategoria"><b>Guardar</b></b-btn>
                <!--<categoria-form :showButton="true" :cbGuardar="setCategoria"></categoria-form>-->
              </b-input-group-append>
            </b-input-group>
          </b-form-group>

           <b-form-group>
            <label>Marca:</label>
            <b-input-group>
              <autocomplete
                required="required"
                :url="API + 'marcas'"
                ref="inputMarca"
                anchor="ProductoMarca"
                label="writer"
                :placeholder.sync="producto.marca.ProductoMarca"
                :on-select="setMarca"
                :onFocus="focusAutocomplete.bind(null, 'inputMarca')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>
              <b-input-group-append>
                <b-btn variant="primary" @click="crearMarca"><b>Guardar</b></b-btn>                
                <!--<marca-form :showButton="true" :cbGuardar="setMarca"></marca-form>-->
              </b-input-group-append>
            </b-input-group>
          </b-form-group>

          <b-form-group v-if="false">
            <label>Modelo/Color:</label>
            <b-input-group>
              <autocomplete
                :url="API + 'productos/modelos'"
                ref="inputModelo"
                anchor="ProductoModelo"
                label="writer"
                :placeholder.sync="producto.ProductoModelo"
                :on-select="val => producto.ProductoModelo = val.ProductoModelo && val.ProductoModelo.toUpperCase()"
                :onInput="val => producto.ProductoModelo = val && val.toUpperCase()"
                :onFocus="focusAutocomplete.bind(null, 'inputModelo')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>
            </b-input-group>
            <!--<b-form-input v-model="producto.ProductoModelo"
              required="required"
              class="upper"
              type="text"
              @input="(val) => producto.ProductoModelo = val && val.toUpperCase()"              
              ></b-form-input>-->
            <!-- <b-input-group>
              <autocomplete
                :url="API + 'modelos'"
                ref="inputModelo"
                anchor="ProductoModelo"
                label="writer"
                :placeholder.sync="producto.modelo.ProductoModelo"
                :on-select="setModelo"
                :onFocus="focusAutocomplete.bind(null, 'inputModelo')"                
                :classes="{input: 'form-control'}">
              </autocomplete>
              <b-input-group-append>
                <modelo-form :showButton="true" :cbGuardar="setModelo"></modelo-form>
              </b-input-group-append>
            </b-input-group> -->
          </b-form-group>
          
          <b-form-group>
            <label>Artículo:</label>
            <b-form-input v-model="producto.Producto"
              ref="inputProducto"
              required="required"
              type="text"
              @input="(val) => producto.Producto = val && val.toUpperCase()"
              :value="producto.Producto"
              ></b-form-input>
          </b-form-group>

         
          <b-form-group>
            <label>Presentación:</label>
            <autocomplete
                :url="API + 'productos/presentaciones'"
                ref="inputPresentacion"
                anchor="ProductoPresentacion"
                label="writer"
                :placeholder.sync="producto.ProductoPresentacion"
                :on-select="val => producto.ProductoPresentacion = val.ProductoPresentacion && val.ProductoPresentacion.toUpperCase()"
                :onInput="val => producto.ProductoPresentacion = val && val.toUpperCase()"
                :onFocus="focusAutocomplete.bind(null, 'inputPresentacion')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>
          </b-form-group>

          <b-form-group v-if="true">
            <label>Imagen:</label>
            <b-img :src="'/resources/images/productos/' + producto.Imagen" fluid alt=""></b-img>
            <input type="file" id="file" ref="file" @change="handleFileUpload()"/>
          </b-form-group>

          <b-form-group v-if="false">
            <label>1ra Descripción:</label>
            <b-form-textarea 
              ref="inputProductoDesc"
              rows="6"
              v-model="producto._ProductoDesc"
              @input="(val) => producto.ProductoDesc = val"
              />
          </b-form-group>

          <b-form-group v-if="false">
            <label>3ra Descripción:</label>
            <b-form-textarea 
              ref="inputProductoDesc3"
              rows="6"
              v-model="producto._ProductoDesc3"
              @input="(val) => producto.ProductoDesc3 = val"
              />
          </b-form-group>

        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Medición:</label>
            <autocomplete
              required="required"
              :url="API + 'mediciones'"
              ref="inputMedicion"
              initValue="UNIDAD"
              anchor="ProductoMedicion"
              label="writer"
              :placeholder.sync="producto.medicion.ProductoMedicion"
              :on-select="setMedicion"
              :onFocus="focusAutocomplete.bind(null, 'inputMedicion')" 
              :classes="{input: 'form-control'}">
            </autocomplete>
          </b-form-group>
          <b-form-group>
            <label>Porcentaje Utilidad:</label>
            <input 
              required="required"
              v-model="producto.PorcentajeUtilidad"
              @input="actualizarPrecioContado"
              type="number"
              min="0"
              step="0.0001"
              class="form-control"
              />
          </b-form-group>
          <b-form-group v-if="false">
            <label>Precio X Mayor:</label>
            <input 
              v-model="producto.PrecioPorMayor"
              type="number"
              min="0"
              step="0.0001"
              class="form-control"
              />
          </b-form-group>
          <b-form-group v-if="false">
            <label>Stock X Mayor:</label>
            <input 
              v-model="producto.StockPorMayor"
              type="number"
              min="0"
              class="form-control"
              step="0.001"
              />
          </b-form-group>
          <b-form-group>
            <b-form-checkbox
                     v-model="producto.ControlaStockMinimo"
                     :value="1"
                     :unchecked-value="0"
                     >
              Activar stock minimo
            </b-form-checkbox>
          </b-form-group>
          <b-form-group v-if="!producto.EsPadre">
            <label>Stock Minimo:</label>
            <input   
              required="required"
              v-model="producto.StockMinimo"
              type="number"
              class="form-control"
              step="0.001"
              />
          </b-form-group>
          <b-form-group>
            <b-form-checkbox
                   v-model="producto.MasMovido"
                   :value="1"
                   :unchecked-value="0">
              Producto con más movimiento
            </b-form-checkbox>
          </b-form-group>
          <b-form-group>
            <b-form-checkbox
                   v-model="producto.ControlaStock"
                   :value="1"
                   :unchecked-value="0">
              Controla Stock
            </b-form-checkbox>
          </b-form-group>
          <b-form-group>
            <label>Activar SubProductos:</label>
            <b-form-checkbox
                     v-model="producto.EsPadre"
                     :value="1"
                     :unchecked-value="0"
                     @input="checkIsHijo">
              Seleccione esta opción si el producto contiene mas productos
            </b-form-checkbox>
          </b-form-group>
          <hr>
          <b-form-group>
            <b-form-checkbox
                   v-model="producto.TienePresentacion"
                   :value="1"
                   :unchecked-value="0">
              Tiene Presentacion
            </b-form-checkbox>
          </b-form-group>
          <b-form-group  v-if="false">
            <label>Activar Proyecto:</label>
            <b-form-checkbox
                     v-model="producto.EsHabitacion"
                     :value="1"
                     :unchecked-value="0"
                     >
              Seleccione esta opción si es un proyecto
            </b-form-checkbox>
          </b-form-group>
          <b-form-group v-if="producto.EsHabitacion">
            <label>Lugar:</label>
            <b-form-select required="required" v-model="producto.Piso" :options="[{value: 1, text: 'Huánuco'}, {value:2, text: 'Pucallpa'}]" class="mb-3" />
          </b-form-group>

          <b-form-group v-if="false">
            <label>2da Descripción:</label>
            <b-form-textarea 
              ref="inputProductoDesc2"
              rows="6"
              v-model="producto._ProductoDesc2"
              @input="(val) => producto.ProductoDesc2 = val"
              />
          </b-form-group>
          
        </b-col>
      </b-row>

      <hr/>
      <b-row>
        <b-col md="6">
          <b-form-group>
            <label>Precio de Costo:</label>
            <input
              v-model="producto.PrecioCosto"
              step="0.00001"
              type="number"
              class="form-control"
              />
          </b-form-group>
        </b-col>
        <b-col md="6"  v-if="false">
          <b-form-group>
            <label>Precio Especial:</label>
            <input
              v-model="producto.PrecioEspecial"
              type="number"
              class="form-control"
              step="0.001"
              />
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Precio Público:</label>
            <input v-model="producto.PrecioContado"
              type="number"
              step="0.01"
              class="form-control"
              @input="actualizarPorcentajeUtilidad"
              />
              <b-form-text v-if="producto.precioContadoNormal">
                Original - {{ producto.precioContadoNormal | redondear }}
              </b-form-text>
          </b-form-group>
        </b-col>
        <b-col md="6"  v-if="false">
          <b-form-group>
            <label>Precio Distribuidor:</label>
            <input v-model="producto.precioConvenio"
              type="number"
              step="0.001"
              class="form-control"
              />
          </b-form-group>
        </b-col>
      </b-row>

      <b-form-group v-if="producto.TienePresentacion">
      <hr>
      <b-row>
        <b-col md="3" v-for="i in 4" :key="i.id">
          <b-card :header="'Presentacion '+i" class="text-center">
            <b-form-group>
              <b-form-input
                v-model="producto.precios[i-1].nombre"
                type="text"
                style="text-transform:uppercase; font-weight: bold;"
                @input="(val) => producto.precios[i-1].nombre = val && val.toUpperCase()"
                ></b-form-input>
            </b-form-group>

            <b-form-group class="text-left">
              <label>Cantidad:</label>
              <input
                v-model="producto.precios[i-1].cantidad"
                type="number"
                class="form-control"
                step="0.01"
                />
            </b-form-group>

            <b-form-group class="text-left">
              <label>Precio Costo:</label>
              <input 
                v-model="producto.precios[i-1].precioc"
                type="number"
                class="form-control"
                step="0.00001"
                />
            </b-form-group>

            <b-form-group class="text-left">
              <label>Precio Venta:</label>
              <input 
                v-model="producto.precios[i-1].precio"
                type="number"
                class="form-control"
                step="0.00001"
                />
            </b-form-group>
          </b-card>
        </b-col>
      </b-row>
      </b-form-group>

      <hr />
      <b-row>
        <b-col>
          <producto-form-det 
            @onUpdateProductos="calcularPreciosPaquete"
            :id-producto="producto.IdProducto" 
            v-if="showProductoDet && show" 
            ref="productoDet"
            ></producto-form-det>
        </b-col>
      </b-row>
      <hr />
      <b-row v-if="producto.IdProducto">
        <b-col md="12">
          <label>Impresión de Codigo Barras</label>
        </b-col>
        <b-col md="3">
          <b-form-group>
              <label>Almacen:</label>
              <autocomplete
                  required="required"
                  ref="inputAlmacen"
                  :url="API + 'almacenes'"
                  anchor="Almacen"
                  initValue="HUANUCO"
                  label="writer"
                  placeholder=""
                  :on-select="setAlmacen" 
                  :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                  :classes="{input: 'form-control'}">
              </autocomplete>
          </b-form-group>
        </b-col>
        <b-col md="2">
            <!--<b-form-group>
              <label>Lote: <span style="font-weight:bold">{{serieNumero}}</span></label>
              <br>
              <b-form-select
              style="width: 130px;"
              @change="(val) => changeMovimiento(val)"
              v-model="hashMovimiento" :options="movimientoLotes">
              </b-form-select>
            </b-form-group>-->
        </b-col>
        <b-col md="3">
            <!--<b-form-group style="text-align: center;">
              <label>Fecha Vencimiento:</label>
              <br>
              <div style=" margin-top:7px" v-html="fechaVenMovimiento"></div>
            </b-form-group>-->
        </b-col>
        <b-col md="4">
          <label>Cantidad:</label>
        <b-input-group>
          <b-form-input v-model="cantidadCodigoBarra"
                  type="number"
                  style="width:80px;"></b-form-input>
          <b-input-group-append>
             <b-btn variant="info" target="_blank" :href="'/views/codigoBarra.php?idProducto=' + producto.IdProducto + '&cantidad=' + cantidadCodigoBarra +'&hash='+lote+'&serieNumero='+serieNumero" >Código de barra</b-btn>
          </b-input-group-append>
        </b-input-group>

        </b-col>
      </b-row>
      <div slot="modal-footer" >
        <!--<b-btn  variant="danger" @click="eliminarProducto(producto)" v-if="producto.IdProducto">
          Anular
        </b-btn>-->
        <b-btn  variant="warning" @click="clearForm" v-if="!producto.IdProducto">
          Limpiar
        </b-btn>
        <b-btn  variant="primary" type="submit">
           Guardar
         </b-btn>
         <b-btn  variant="secondary" @click="show=false">
           Close
         </b-btn>
       </div>
    </b-modal>
    </b-form>
  </div>
</template>
<script>
import config from '@/store/config.js'
import CategoriaForm from '@/components/CategoriaForm'
import MarcaForm from '@/components/MarcaForm'
import ProductoFormDet from '@/components/ProductoFormDet'
// import ModeloForm from '@/components/ModeloForm'

export default {
  data () {
    return {
      showProductoDet: true,
      cantidadCodigoBarra: 1,
      hashMovimiento:'',
      movimientoLotes: [],
      lote:'',
      fechaVenMovimiento:'',
      serieNumero:'',
      title: 'Nuevo Producto',
      almacen: {
        IdAlmacen: 1,
        Almacen: 'PRINCIPAL'
      },
      API: config.API,
      nuevo:false,
      show: false, 
      // generos: ['VARON', 'DAMA', 'NIÑO', 'NIÑA', 'UNISEX', 'JUNIOR'],
      producto: {},
      productosDet: [],
      file: '',
    }
  },
  beforeMount () {
    this.initForm()
  },
  props: ['showButton', 'productoUpdate', 'cbGuardar'],
  watch: {
    show (val) {
      let newVal = this.productoUpdate  || {}
      if (newVal.IdProducto && !this.nuevo) {
        this.axios.get(this.API + 'productos/id/'+newVal.IdProducto
        ).then((response) => {
          newVal = response.data
          this.hashMovimiento=''
          this.movimientoLotes=[]
          this.lote=''
          this.fechaVenMovimiento=''
          this.serieNumero = ''
          this.initForm()
          this.title = 'Editar Producto << ' + newVal.CodigoBarra + ' >>'
          this.producto.categoria = {
            IdProductoCategoria: newVal.IdProductoCategoria,
            ProductoCategoria: newVal.ProductoCategoria
          }
          this.producto.marca = {
            IdProductoMarca: newVal.IdProductoMarca,
            ProductoMarca: newVal.ProductoMarca
          }
          this.producto.medicion = {
            IdProductoMedicion: newVal.IdProductoMedicion,
            ProductoMedicion: newVal.ProductoMedicion
          }
        
        // this.$refs.inputCategoria.setValue('')
          this.$refs.inputCategoria.setValue(newVal.ProductoCategoria)
        
        // this.$refs.inputMarca.setValue('')
          this.$refs.inputMarca.setValue(newVal.ProductoMarca)
        // this.$refs.inputMedicion.setValue('')
          this.$refs.inputMedicion.setValue(newVal.ProductoMedicion)
          
          // this.$refs.inputModelo.setValue('')
          //this.$refs.inputModelo.setValue(newVal.ProductoModelo)
          this.$refs.inputPresentacion.setValue(newVal.ProductoPresentacion)
          //this.$refs.inputProductoDesc.$el.value = newVal.ProductoDesc
          //this.$refs.inputProductoDesc2.$el.value = newVal.ProductoDesc2
          //this.$refs.inputProductoDesc3.$el.value = newVal.ProductoDesc3
          // this.$refs.inputTalla.setValue(newVal.ProductoTalla)
          // this.$refs.inputProducto.$el.disabled = true
          //this.$refs.inputCodigoBarra.$el.disabled = true
          // this.$refs.inputColor.setValue(newVal.Color)
          // this.$refs.inputBotapie.setValue(newVal.Botapie)
          
          /* this.producto.modelo = {
            IdProductoModelo: newVal.IdProductoModelo,
            ProductoModelo: newVal.ProductoModelo
          } */
          /*this.producto.talla = {
            IdProductoTalla: newVal.IdProductoTalla,
            ProductoTalla: newVal.ProductoTalla
          }*/
          //this.producto.Producto = newVal.Producto.split('-').slice(0, -1).join()
          this.producto.Producto = newVal.Producto
          this.producto.IdProducto = newVal.IdProducto
          this.producto.EsPadre = newVal.EsPadre
          this.loadAlmacen(1);
          Vue.nextTick(() => {
            if (this.$refs.productoDet) this.$refs.productoDet.show = parseInt(newVal.EsPadre)
          })
          // this.producto.Genero = newVal.Genero
          // this.producto.Botapie = newVal.Botapie
          // this.producto.Color = newVal.Color
          this.producto.PorcentajeUtilidad    = newVal.PorcentajeUtilidad
          this.producto.PrecioPorMayor        = newVal.PrecioPorMayor
          this.producto.ControlaStockMinimo   = newVal.ControlaStockMinimo
          this.producto.StockMinimo           = newVal.StockMinimo
          this.producto.StockPorMayor         = newVal.StockPorMayor
          this.producto.CodigoBarra           = newVal.CodigoBarra
          //this.producto.ProductoModelo        = newVal.ProductoModelo
          this.producto.ProductoDesc          = newVal.ProductoDesc
          this.producto.ProductoDesc2         = newVal.ProductoDesc2
          this.producto.ProductoDesc3         = newVal.ProductoDesc3
          this.producto.ProductoPresentacion  = newVal.ProductoPresentacion
          this.producto.PrecioCosto           = newVal.PrecioCosto
          this.producto.PrecioContado         = newVal.PrecioContado
          this.producto.precioConvenio        = newVal.precioConvenio
          this.producto.PrecioEspecial        = newVal.PrecioEspecial
          this.producto.EsHabitacion          = newVal.EsHabitacion
          this.producto.Piso                  = newVal.Piso
          this.producto.ControlaStock         = newVal.ControlaStock
          this.producto.MasMovido             = newVal.MasMovido
          this.producto.TienePresentacion     = newVal.TienePresentacion
          this.producto.Imagen                = newVal.Imagen
          if (newVal.PreciosPorProducto) {
            this.producto.precios = JSON.parse(newVal.PreciosPorProducto)
          }else{
            this.producto.precios = [
              {nombre: 'NOMBRE 1', cantidad: 0, precioc: 0, precio: 0},
              {nombre: 'NOMBRE 2', cantidad: 0, precioc: 0, precio: 0},
              {nombre: 'NOMBRE 3', cantidad: 0, precioc: 0, precio: 0},
              {nombre: 'NOMBRE 4', cantidad: 0, precioc: 0, precio: 0},
            ]
          }
          this.$refs.file.value=null
          }).catch((error) => {
            console.log(error)
            this.$refs.file.value=null
            this.initForm()
            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
          })

      } else {

        this.title = 'Nuevo Producto'
        this.clearForm()
        this.$refs.file.value=null
        this.$refs.inputCodigoBarra.$el.disabled = false
      }
    }
  },
  methods: {
    handleFileUpload() {
      this.file = this.$refs.file.files[0]
    },
    calcularPreciosPaquete (prodDetalles) {
      let sumatoriaCosto = 0
      let sumatoriaContado = 0
      prodDetalles.forEach(prod => {
        if (prod.Cantidad) {
          sumatoriaCosto += prod.PrecioCosto * prod.Cantidad
          sumatoriaContado += prod.PrecioContado * prod.Cantidad
        }
      });
      sumatoriaCosto = this.$options.filters.redondear(sumatoriaCosto)
      sumatoriaContado = this.$options.filters.redondear(sumatoriaContado)
      
      Vue.set(this.producto, 'PrecioCosto', sumatoriaCosto)
      Vue.set(this.producto, 'PrecioContado', sumatoriaContado)
      this.actualizarPorcentajeUtilidad()
    },
    /*eliminarProducto (producto) {
      // NO eliminar porque no distinque movimientos ni ventas
      let r = confirm('¿Esta seguro de Anular este producto?')
      if (r) {
        this.axios.post(this.API + 'productos/delete', {
          id: producto.IdProducto
        }).then(res => {
          if (res.data.updated) {
            this.$toasted.success('El producto con código ' + res.data.IdProducto + ' ha sido anulado')
            if (this.cbGuardar) {
              this.cbGuardar()
            }
            this.$refs.productoForm.hide()
          }
        })
      }
    },*/
    loadAlmacen(id) {
    this.axios.get(this.API + 'almacenes/id/' + id)
    .then(res => {
        this.setAlmacen(res.data)
    })
    },
    setAlmacen (obj) {
        this.almacen = obj
        this.$refs.inputAlmacen.setValue(obj.Almacen)
        this.loadMovimientoLotes(this.producto.IdProducto)
    },
    changeMovimiento(hash) {
      if  (hash) {
        // Fecha de vencimiento
        var fechaVen = this.movimientoLotes.find(m => m.value === hash).FechaVen
        this.lote = this.movimientoLotes.find(m => m.value === hash).text
        this.serieNumero = this.movimientoLotes.find(m => m.value === hash).SerieNumero
        let hoy      = this.$moment().unix();
        let fechaVenUnix = this.$moment(fechaVen).unix();
        let en5dias  = this.$moment().add(5, 'days').unix();
        let style = 'bg-success'
        if(fechaVenUnix && fechaVenUnix < hoy) {
          style = 'bg-danger'
        }
        if (fechaVenUnix >= hoy && fechaVenUnix <= en5dias) {
          style = 'bg-warning'
        }
        if (fechaVen) {
            this.fechaVenMovimiento= `<label style="font-weight:bold" class='venci ${style}'>${this.$moment(fechaVen).format("DD-MM-YYYY")}</label>`;
        } else {

        }
          // return `<small class='venci ${style}'>Venci: ${fechaVen}</small>`
      }
    },
    loadMovimientoLotes ($idProducto) {
      this.axios.get(this.API + '/movimientolotesbarra/' + $idProducto+'/'+(this.almacen.IdAlmacen))
        .then((response) => {
       
          // Cargar por defecto lote próximo a vencer.
          let primeroVencer = response.data.reduce((a,b) => {
            return (a.FechaVen && (new Date(a.FechaVen) < new Date(b.FechaVen))) ? a : b;
          })
          
          this.$nextTick(() => {
            this.hashMovimiento = primeroVencer.hashMovimiento
            this.changeMovimiento(primeroVencer.hashMovimiento)
          })
          
          this.movimientoLotes = response.data.map(l => ({
            value: l.hashMovimiento,
            text : l.hashMovimiento,
            Stock : l.Stock,
            FechaVen: l.FechaVen,
            SerieNumero: l.Serie + '-' + l.Numero
          }))
        })
        .catch((error) => {
          console.log(error)
        })
    },
    actualizarPrecioContado () {
      var precioCosto = this.producto.PrecioCosto
      var utilidad = this.producto.PorcentajeUtilidad
      // var nuevoPrecioContado = precioCosto + (precioCosto * (utilidad / 100))
      var nuevoPrecioContado = precioCosto / (1 - (utilidad / 100))

      this.producto.precioContadoNormal = nuevoPrecioContado
      //this.producto.PrecioContado = this.$options.filters.redondeoMayor(nuevoPrecioContado)
      this.producto.PrecioContado = this.$options.filters.redondear(nuevoPrecioContado, 1)

    },
    actualizarPorcentajeUtilidad () {
      var precioContado = this.producto.PrecioContado
      var precioCosto = this.producto.PrecioCosto

      this.producto.PorcentajeUtilidad = this.$options.filters.redondear((1 - (precioCosto / precioContado)) * 100)
    },
    setCategoria (obj) {
      this.producto.categoria = obj
      this.$refs.inputCategoria.setValue(obj.ProductoCategoria)
    },
    setMedicion (obj) {
      this.producto.medicion = obj
    },
    setMarca (obj) {
      this.producto.marca = obj
      this.$refs.inputMarca.setValue(obj.ProductoMarca)
    },
    /* setModelo (obj) {
      this.producto.modelo = obj
      this.$refs.inputModelo.setValue(obj.ProductoModelo)
    }, */
    /*setTalla (obj) {
      this.producto.talla = obj
      this.$refs.inputTalla.setValue(obj.ProductoTalla)
    },*/
    clearForm () {
      this.$refs.inputCategoria.setValue('')
      this.$refs.inputMarca.setValue('')
      this.$refs.inputMedicion.setValue('UNIDAD')
      // this.$refs.inputTalla.setValue('')
      //this.$refs.inputModelo.setValue('')
      this.$refs.inputPresentacion.setValue('')
      // this.$refs.inputColor.setValue('')
      // this.$refs.inputBotapie.setValue('')

      this.initForm()
    },
    initForm () {
      this.producto = {
        EsPadre: 0,
        Anulado: 0,
        EsHabitacion: 0,
        Piso: 1,
        IdProductoMarca: null,
        IdProductoMedicion: null,
        IdProductoCategoria: null,
        // IdProductoModelo: null,
        // IdProductoBotapie: null,
        // IdProductoTalla: '',
        Producto: '',
        ProductoDesc: '',
        ProductoDesc2: '',
        ProductoDesc3: '',
        CodigoBarra: '',
        // Color: '',
        PorcentajeUtilidad: 0,
        ControlaStockMinimo:0,
        MasMovido:0,
        StockMinimo: 0,
        TienePresentacion: 0,
        StockPorMayor: 0,
        PrecioContado: 0,
        PrecioPorMayor: 0,
        precioConvenio: 0,
        PrecioEspecial: 0,
        // Botapie: '',
        // Genero: '',
        categoria: {},
        marca: {},
        // modelo: {},
        //talla: {},
        medicion: {
          IdProductoMedicion:1,
          ProductoMedicion:"UNIDAD"
        },
        Imagen: null,
        precios: [
          {nombre: 'NOMBRE 1', cantidad: 0, precioc: 0, precio: 0},
          {nombre: 'NOMBRE 2', cantidad: 0, precioc: 0, precio: 0},
          {nombre: 'NOMBRE 3', cantidad: 0, precioc: 0, precio: 0},
          {nombre: 'NOMBRE 4', cantidad: 0, precioc: 0, precio: 0}
        ]
      }
    },
    keyDownInput(e) {
      if (e.target.tagName != 'TEXTAREA') {  
        e.preventDefault();
      }
    },
    guardarProducto(e) {
      e.preventDefault();

      // Agregar producto DET
      if (this.showProductoDet) {
        this.producto.productosDet = this.$refs.productoDet.productos
      }
      // console.log(JSON.stringify(this.producto))
      this.producto.precios.map((pre)=>{
        if(pre.cantidad==''){
          pre.cantidad=0
        }
        if(pre.precio==''){
          pre.precio=0
        }
      })
      this.producto.CodigoBarra=(''+this.producto.CodigoBarra).trim();
      this.axios.post(this.API + 'productos', {
        ...this.producto, 
        PreciosPorProducto: this.producto.precios 
      })
      .then((response) => {
        if (this.cbGuardar) {
          this.cbGuardar(this.producto.IdProducto?this.producto:response.data.insertId,this.producto.precios )
        }
        if (response.data.error) {
          this.$toasted.show(response.data.error + ' - ' + response.data.codigoBarra)
          return
        } else {
          this.$toasted.info('Producto guardado correctamente')
        }

        // this.initForm()
        // this.producto.talla = {}
        // this.$refs.inputTalla.setValue('')


        //INSERTANDO IMAGEN
        let formData = new FormData();
        formData.append('imagen', this.file);

        this.axios({
            method    : 'post',
            url       : this.API + 'producto/imagen/' + response.data.insertId,
            data      : formData,
            headers   : { 'Content-Type': 'multipart/form-data' }
          })
          .then((res) => {
            // console.log(res);
          })

        this.$refs.productoForm.hide()
      })
      .catch((error) => {
        console.log(error)
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    crearCategoria () {
      if (this.$refs.inputCategoria.type) {
        let categoria = {}
        categoria.ProductoCategoria = this.$refs.inputCategoria.type.toUpperCase()

        this.axios.post(this.API + 'categorias', categoria)
        .then((response) => {
          this.$toasted.show('Categoría guardada correctamente')
          this.setCategoria({
            IdProductoCategoria: response.data.insertId,
            ProductoCategoria: response.data.ProductoCategoria
          })
        })
        .catch((error) => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
      }
      
    },
    crearMarca () {
      if (this.$refs.inputMarca.type) {
        let marca = {}
        marca.ProductoMarca = this.$refs.inputMarca.type.toUpperCase()
  
        this.axios.post(this.API + 'marcas', marca)
        .then((response) => {
          this.$toasted.show('Marca guardada correctamente')
          this.setMarca({
            IdProductoMarca: response.data.insertId,
            ProductoMarca: response.data.ProductoMarca
          })
        })
        .catch((error) => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
      }
    },
    crearTalla () {
      if (this.$refs.inputTalla.type) {
        let talla = {}
        talla.ProductoTalla = this.$refs.inputTalla.type.toUpperCase()
  
        this.axios.post(this.API + 'tallas', talla)
        .then((response) => {
          this.$toasted.show('Talla guardada correctamente')
          this.setTalla({
            IdProductoTalla: response.data.insertId,
            ProductoTalla: response.data.ProductoTalla
          })
        })
        .catch((error) => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
      }
    },
    checkIsHijo (val) {
      if (val && this.producto.IdProducto) {
        this.axios.get(this.API + 'productos/eshijo', {params: {
          idProducto: this.producto.IdProducto
        }})
        .then(res => {
          let esHijo = res.data.esHijo
          if(esHijo) {
            this.$toasted.show('Este producto no puede contener productos ya que es un producto hijo')
            this.producto.EsPadre = false      
          }else {
            this.$refs.productoDet.show = parseInt(this.producto.EsPadre)
          }
        })
      } else {
        if (this.$refs.productoDet) this.$refs.productoDet.show = parseInt(this.producto.EsPadre)
      }
    }
  },
  computed : {
  },
  components: {
    CategoriaForm, MarcaForm, ProductoFormDet
  }
}
</script>
<style>

</style>
