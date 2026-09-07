<template>
  <div class="pos-jobi-inline">
    <b-btn v-if="showButton" class="pos-btn-nuevo" @click="()=>nuevo=true" v-b-modal.modalProducto variant="primary"><i class="fas fa-plus-circle"></i> Nuevo</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarProducto" @keydown.enter="keyDownInput" >
    <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalProducto" size="lg" ref="productoForm" v-model="show" modal-class="pos-jobi-modal" hide-header-close>
      <template slot="modal-header">
        <div class="pos-modal-header">
          <div class="pos-modal-header-icon"><i class="fas fa-box-open"></i></div>
          <div>
            <div class="pos-modal-header-title">{{ title }}</div>
            <div class="pos-modal-header-sub">Completa los datos del producto</div>
          </div>
          <button type="button" class="pos-modal-close" @click="show=false"><i class="fas fa-times"></i></button>
        </div>
      </template>

      <b-row>
        <b-col md="6">
          <b-form-group class="ocularMovil pos-field">
            <label><i class="fas fa-barcode pos-label-icon"></i> Código barra</label>
            <b-form-input 
              ref="inputCodigoBarra"
              v-model="producto.CodigoBarra"
              @input="(val) => producto.CodigoBarra = val && val.toUpperCase()"
              type="text"
              class="form-control upper"
              ></b-form-input>
          </b-form-group>
          <b-form-group class="pos-field">
            <label><i class="fas fa-tags pos-label-icon"></i> Categoría</label>
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
                <b-btn class="pos-btn-mini" @click="crearCategoria"><i class="fas fa-save"></i></b-btn>
                <!--<categoria-form :showButton="true" :cbGuardar="setCategoria"></categoria-form>-->
              </b-input-group-append>
            </b-input-group>
          </b-form-group>

           <b-form-group class="pos-field">
            <label><i class="fas fa-copyright pos-label-icon"></i> Marca</label>
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
                <b-btn class="pos-btn-mini" @click="crearMarca"><i class="fas fa-save"></i></b-btn>
                <!--<marca-form :showButton="true" :cbGuardar="setMarca"></marca-form>-->
              </b-input-group-append>
            </b-input-group>
          </b-form-group>

          <b-form-group class="ocularMovil pos-field">
            <label><i class="fas fa-hashtag pos-label-icon"></i> Modelo</label>
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
          <b-form-group class="ocularMovil pos-field">
            <label><i class="fas fa-hashtag pos-label-icon"></i> Código Producto Sunat:</label>
            <b-input-group>
              <autocomplete
                :url="API + 'codigoproductosunat'"
                ref="inputCodigoSunat"
                anchor="busqueda_label"
                label="writer"
                :placeholder.sync="producto.codigoSunat.busqueda_label"
                :on-select="setCodigoSunat"
                :onFocus="focusAutocomplete.bind(null, 'inputCodigoSunat')"
                :classes="{input: 'form-control upper'}">
              </autocomplete>
            </b-input-group>
          </b-form-group>
          
          <b-form-group class="pos-field">
            <label><i class="fas fa-cube pos-label-icon"></i> Artículo</label>
            <b-form-input v-model="producto.Producto"
              ref="inputProducto"
              required="required"
              type="text"
              @input="(val) => producto.Producto = val && val.toUpperCase()"
              :value="producto.Producto"
              ></b-form-input>
          </b-form-group>

         
          <b-form-group class="ocularMovil pos-field">
            <label><i class="fas fa-box pos-label-icon"></i> Presentación</label>
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

          <b-form-group v-if="false">
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
          <b-form-group class="pos-field">
            <label><i class="fas fa-ruler pos-label-icon"></i> Medición</label>
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
          <b-form-group class="pos-field">
            <label><i class="fas fa-percentage pos-label-icon"></i> Porcentaje Utilidad</label>
            <input
              v-model="producto.PorcentajeUtilidad"
              @input="actualizarPrecioContado"
              type="number"
              min="0"
              class="form-control"
              step="0.0001"
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
              step="0.00001"
              />
          </b-form-group>
          <b-form-group v-if="!producto.EsPadre" class="pos-field">
            <label><i class="fas fa-boxes pos-label-icon"></i> Stock Minimo</label>
            <input   
              v-model="producto.StockMinimo"
              type="number"
              step="0.0001"
              class="form-control"
              />
          </b-form-group>
          <b-form-group>
            <div class="pos-mini-row-check">
              <b-form-checkbox
                     v-model="producto.ControlaStock"
                     :value="1"
                     :unchecked-value="0">
                <i class="fas fa-warehouse pos-label-icon"></i> Controla Stock
              </b-form-checkbox>
            </div>
          </b-form-group>
          <b-form-group>
            <label class="pos-check-title"><i class="fas fa-layer-group pos-label-icon"></i> Activar SubProductos</label>
            <div class="pos-mini-row-check">
              <b-form-checkbox
                       v-model="producto.EsPadre"
                       :value="1"
                       :unchecked-value="0"
                       @input="checkIsHijo">
                Seleccione esta opción si el producto contiene mas productos
              </b-form-checkbox>
            </div>
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
          <b-form-group v-if="producto.EsHabitacion" class="pos-field">
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

      <div class="pos-section-title pos-section-title-navy"><i class="fas fa-coins"></i> Precios</div>
      <b-row >
        <b-col md="6">
          <b-form-group class="pos-field">
            <label><i class="fas fa-money-bill-wave pos-label-icon"></i> Precio de Costo</label>
            <input
              v-model="producto.PrecioCosto"
              step="0.01"
              type="number"
              class="form-control"
              />
          </b-form-group>
        </b-col>
        <b-col md="6" v-if="false">
          <b-form-group>
            <label>Precio Especial:</label>
            <input
              v-model="producto.PrecioEspecial"
              type="number"
              step="0.01"
              class="form-control"
              />
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group class="pos-field">
            <label><i class="fas fa-tag pos-label-icon"></i> Precio Público</label>
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
        <b-col md="6" v-if="false">
          <b-form-group>
            <label>Precio Distribuidor:</label>
            <input v-model="producto.precioConvenio"
              type="number"
              class="form-control"
              step="0.001"
              />
          </b-form-group>
        </b-col>
      </b-row>

      <div class="pos-section-title pos-section-title-teal"><i class="fas fa-layer-group"></i> Presentaciones por cantidad</div>
      <b-row>
        <b-col md="3" v-for="i in 4" :key="i.id">
          <b-card border-variant="light" class="text-center pos-precio-card">
            <div class="pos-precio-card-title">{{ producto.precios[i-1].nombre }}</div>
            <b-form-group class="pos-field">
              <label>Cantidad</label>
              <input
                v-model="producto.precios[i-1].cantidad"
                type="number"
                step="0.001"
                class="form-control"
                />
            </b-form-group>
            
            <b-form-group class="pos-field mb-2">
              <label>Precio</label>
              <input 
                v-model="producto.precios[i-1].precio"
                type="number"
                step="0.001"
                class="form-control"
                />
            </b-form-group>
            <div class="pos-precio-card-total">Total: S/ {{(producto.precios[i-1].precio*producto.precios[i-1].cantidad) | redondear}}</div>
          </b-card>
        </b-col>
      </b-row>

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

      <div slot="modal-footer" class="pos-modal-footer">
        <div class="pos-cod-barra" v-if="producto.IdProducto">
          <b-input-group>
            <b-form-input
              v-model="cantidadCodigoBarra"
              type="number"
              style="width:80px;"
            ></b-form-input>
            <b-input-group-append>
              <b-btn class="pos-btn-cod-barra" target="_blank" :href="'/views/codigoBarra.php?idProducto=' + producto.IdProducto + '&cantidad=' + cantidadCodigoBarra "><i class="fas fa-barcode"></i> Código de barra</b-btn>
            </b-input-group-append>
          </b-input-group>
        </div>
        <div class="pos-modal-actions">
          <b-btn class="pos-btn-anular" @click="eliminarProducto(producto)" v-if="producto.IdProducto">
            <i class="fas fa-ban"></i> Anular
          </b-btn>
          <b-btn class="pos-btn-limpiar-modal" @click="clearForm" v-if="!producto.IdProducto">
            <i class="fas fa-eraser"></i> Limpiar
          </b-btn>
          <b-btn class="pos-btn-guardar" type="submit">
            <i class="fas fa-save"></i> Guardar
          </b-btn>
          <b-btn class="pos-btn-cerrar-modal" @click="show=false">
            <i class="fas fa-times"></i> Cerrar
          </b-btn>
        </div>
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
      title: 'Nuevo Producto',
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
          this.$refs.inputModelo.setValue(newVal.ProductoModelo)
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
          Vue.nextTick(() => {
            if (this.$refs.productoDet) this.$refs.productoDet.show = parseInt(newVal.EsPadre)
          })
          // this.producto.Genero = newVal.Genero
          // this.producto.Botapie = newVal.Botapie
          // this.producto.Color = newVal.Color
          this.producto.CodigoProductoSunat = newVal.CodigoSunat || '00000000'
          this.producto.codigoSunat = {
            codigo: newVal.CodigoSunat || '00000000',
            busqueda_label: newVal.CodigoSunatDescripcion
              ? (newVal.CodigoSunat + ' - ' + newVal.CodigoSunatDescripcion)
              : '00000000 - NO REGISTRADO / COMODÍN GENÉRICO'
          }
          
          if (this.$refs.inputCodigoSunat) {
            this.$refs.inputCodigoSunat.setValue(this.producto.codigoSunat.busqueda_label)
          }
          this.producto.PorcentajeUtilidad    = newVal.PorcentajeUtilidad
          this.producto.PrecioPorMayor        = newVal.PrecioPorMayor
          this.producto.StockMinimo           = newVal.StockMinimo
          this.producto.StockPorMayor         = newVal.StockPorMayor
          this.producto.CodigoBarra           = newVal.CodigoBarra
          this.producto.ProductoModelo        = newVal.ProductoModelo
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
          this.producto.Imagen                = newVal.Imagen

          if (newVal.PreciosPorProducto) {
            this.producto.precios = JSON.parse(newVal.PreciosPorProducto)
          }

          }).catch((error) => {
            console.log(error)
            this.initForm()
            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
          })


      } else {

        this.title = 'Nuevo Producto'
        this.clearForm()

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
    eliminarProducto (producto) {
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
      this.$refs.inputModelo.setValue('')
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
        PrecioCosto: 0,
        // Color: '',
        PorcentajeUtilidad: 0,
        StockMinimo: 0,
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
        CodigoProductoSunat: '00000000',
        codigoSunat: {
          codigo: '00000000',
          busqueda_label: '00000000 - NO REGISTRADO / COMODÍN GENÉRICO'
        },
        //talla: {},
        medicion: {
          IdProductoMedicion:1,
          ProductoMedicion:"UNIDAD"
        },
        Imagen: null,
        precios: [
          {nombre: 'Precio 1', cantidad: 0, precio: 0},
          {nombre: 'Precio 2', cantidad: 0, precio: 0},
          {nombre: 'Precio 3', cantidad: 0, precio: 0},
          {nombre: 'Precio 4', cantidad: 0, precio: 0}
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

      this.axios.post(this.API + 'productos', {
        ...this.producto, 
        PreciosPorProducto: this.producto.precios 
      })
      .then((response) => {
        if (this.cbGuardar) {
          this.cbGuardar(response.data.insertId,this.producto.precios, this.producto)
        }
        if (response.data.error) {
          this.$toasted.show(response.data.error + ' - ' + response.data.codigoBarra)
          return
        } else {
          this.$toasted.show('Producto guardado correctamente')
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
    focusAutocomplete(refName) {
      this.$nextTick(() => {
        const component = this.$refs[refName]
        if (!component) return

        // 1. Seleccionar todo el texto del input
        const inputEl = component.$el ? component.$el.querySelector('input') : null
        if (inputEl) {
          inputEl.select()
        }

        // 2. Restaurar el comportamiento original: forzar apertura de la lista
        if (typeof component.handleDoubleClick === 'function') {
          component.handleDoubleClick()
        }
      })
    },

    setCodigoSunat(obj) {
      this.producto.codigoSunat = obj;
      this.producto.CodigoProductoSunat = obj.CodigoSunat;
      if (this.$refs.inputCodigoSunat) {
        this.$refs.inputCodigoSunat.setValue(obj.busqueda_label);
      }
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
<style scoped>
/* =========================================================
   ProductoForm.vue - mismo lenguaje visual JoBi que el resto
   de los módulos (modal navy, campos con icono, tarjetas de
   precios, acciones en píldora).
   ========================================================= */
.pos-jobi-inline {
    --jobi-navy: #00666A;
    --jobi-navy-2: #00666A;
    --jobi-teal: #0F9B8E;
    --jobi-blue: #2F6FED;
    --jobi-purple: #7C3AED;
    --jobi-green: #17A554;
    --jobi-red: #E5484D;
    --jobi-orange: #F5A623;
}

.pos-btn-nuevo {
    border-radius: 999px !important;
    border: none !important;
    background: var(--jobi-green) !important;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .03em;
    font-size: .78rem;
    padding: 8px 18px !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 6px 14px rgba(23,165,84,.30);
}
.pos-btn-nuevo:hover { transform: translateY(-1px); }

/* -------- Cabecera del modal -------- */
.pos-modal-header {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    background: linear-gradient(135deg, var(--jobi-navy) 0%, var(--jobi-navy-2) 100%);
    color: #fff;
    padding: 14px 18px;
    margin: -1px -1px 0 -1px;
    border-radius: 6px 6px 0 0;
    position: relative;
}
.pos-modal-header-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255,255,255,.14);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    flex: 0 0 auto;
}
.pos-modal-header-title {
    font-weight: 900;
    font-size: 1.05rem;
}
.pos-modal-header-sub {
    font-size: .74rem;
    color: rgba(255,255,255,.65);
}
.pos-modal-close {
    margin-left: auto;
    background: rgba(255,255,255,.14);
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
}
.pos-modal-close:hover { background: rgba(255,255,255,.24); }

/* -------- Campos -------- */
.pos-field label {
    color: var(--jobi-navy);
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.pos-check-title {
    color: var(--jobi-navy);
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    display: flex;
    align-items: center;
    gap: 5px;
}
.pos-label-icon {
    font-size: .82em;
    color: var(--jobi-teal);
}
.pos-field >>> input,
.pos-field >>> .form-control,
.pos-field input.form-control {
    border-radius: 10px !important;
    border: 1.5px solid #dbe6f1 !important;
    height: 40px;
    font-weight: 600;
    color: var(--jobi-navy);
}
.pos-field >>> input:focus,
.pos-field >>> .form-control:focus {
    border-color: var(--jobi-blue) !important;
    box-shadow: 0 0 0 3px rgba(47,111,237,.14) !important;
}

.pos-btn-mini {
    border-radius: 10px !important;
    background: var(--jobi-purple) !important;
    border: none !important;
    color: #fff !important;
    height: 40px;
    box-shadow: 0 4px 10px rgba(124,58,237,.30);
}

.pos-mini-row-check {
    background: #eef3f8;
    border: 1px solid #dbe6f1;
    border-radius: 10px;
    padding: 8px 12px;
}

/* -------- Títulos de sección -------- */
.pos-section-title {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    font-weight: 800;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: 6px 14px;
    border-radius: 999px;
    margin: 14px 0 10px;
}
.pos-section-title-navy { background: var(--jobi-navy); }
.pos-section-title-teal { background: var(--jobi-teal); }

/* -------- Tarjetas de precios -------- */
.pos-precio-card {
    border-radius: 12px !important;
    border: 1.5px solid #e7edf5 !important;
    box-shadow: 0 4px 12px rgba(11,42,74,.06);
    margin-bottom: 12px;
}
.pos-precio-card-title {
    font-weight: 800;
    color: var(--jobi-navy);
    text-transform: uppercase;
    font-size: .78rem;
    letter-spacing: .03em;
    margin-bottom: 8px;
}
.pos-precio-card-total {
    font-weight: 800;
    color: var(--jobi-green);
    font-size: .88rem;
}

/* -------- Pie del modal -------- */
.pos-modal-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    flex-wrap: wrap;
    gap: 10px;
}
.pos-cod-barra { flex: 1 1 auto; }
.pos-btn-cod-barra {
    border-radius: 0 10px 10px 0 !important;
    background: var(--jobi-blue) !important;
    border: none !important;
    color: #fff !important;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.pos-modal-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-left: auto;
}
.pos-btn-anular,
.pos-btn-limpiar-modal,
.pos-btn-guardar,
.pos-btn-cerrar-modal {
    border-radius: 999px !important;
    border: none !important;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .03em;
    font-size: .76rem;
    padding: 9px 18px !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.pos-btn-anular { background: var(--jobi-red) !important; color: #fff !important; box-shadow: 0 6px 14px rgba(229,72,77,.30); }
.pos-btn-limpiar-modal { background: var(--jobi-orange) !important; color: #fff !important; box-shadow: 0 6px 14px rgba(245,166,35,.30); }
.pos-btn-guardar { background: var(--jobi-green) !important; color: #fff !important; box-shadow: 0 6px 14px rgba(23,165,84,.30); }
.pos-btn-cerrar-modal { background: #eef3f8 !important; color: var(--jobi-navy) !important; }
.pos-btn-anular:hover,
.pos-btn-limpiar-modal:hover,
.pos-btn-guardar:hover,
.pos-btn-cerrar-modal:hover { transform: translateY(-1px); }

@media only screen and (max-width: 700px) {
  /* For desktop: */
    .ocularMovil{
        display: none;
    }
  }
</style>