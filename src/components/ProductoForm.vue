<template>
  <div>
    <b-btn v-if="showButton" v-b-modal.modalProducto variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarProducto" @keydown.enter.prevent="()=>{}" >
    <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalProducto" :title="title" size="lg" ref="productoForm" v-model="show">
      <b-row>
        <b-col md="12" style="margin-top: -10px; margin-bottom: -10px;">
            <b-btn v-if="showNew" variant="success" size="md" @click="eliminaId"><b>Nuevo</b></b-btn>    
            <hr style="margin-top: 5px;"/> 
        </b-col>

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
            <label>Artículo:</label>
            <b-form-input v-model="producto.Producto"
              required="required"
              type="text"
              @input="(val) => producto.Producto = val && val.toUpperCase()"
              :value="producto.Producto"
              ></b-form-input>
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
          <b-form-group>
            <label>Modelo:</label>
            <b-input-group>
              <autocomplete
                required="required"
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
            <label>Colores:</label>
            <autocomplete
                required="required"
                :url="API + 'productos/colores'"
                ref="inputColor"
                anchor="Color"
                label="writer"
                :placeholder.sync="producto.Color"
                :on-select="val => producto.Color = val.Color && val.Color.toUpperCase()"
                :onInput="val => producto.Color = val && val.toUpperCase()"
                :onFocus="focusAutocomplete.bind(null, 'inputColor')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>
            <!--<b-form-input v-model="producto.Color"
              type="text"
              @input="(val) => producto.Color = val && val.toUpperCase()"              
              ></b-form-input>-->
          </b-form-group>
          <b-form-group>
            <label>Institucion:</label>
            <b-input-group>
              <autocomplete
                required="required"
                :url="API + 'productos/instituciones'"
                ref="inputInstitucion"
                anchor="Institucion"
                label="writer"
                :placeholder.sync="producto.Institucion"
                :on-select="val => producto.Institucion = val.Institucion && val.Institucion.toUpperCase()"
                :onInput="val => producto.Institucion = val && val.toUpperCase()"
                :onFocus="focusAutocomplete.bind(null, 'inputInstitucion')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>
            </b-input-group>
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
              :classes="{input: 'form-control'}">
            </autocomplete>
          </b-form-group>
          
          <b-form-group>
            <label>Talla:</label>
            <b-input-group>
              <autocomplete
                required="required"
                :url="API + 'tallas'"
                ref="inputTalla"
                anchor="ProductoTalla"
                label="writer"
                :placeholder.sync="producto.talla.ProductoTalla"
                :on-select="setTalla"
                :onFocus="focusAutocomplete.bind(null, 'inputTalla')"                    
                :classes="{input: 'form-control upper'}">
              </autocomplete>
              <b-input-group-append>
                <b-btn variant="primary" @click="crearTalla"><b>Guardar</b></b-btn>                
                <!--<marca-form :showButton="true" :cbGuardar="setMarca"></marca-form>-->
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
          <b-form-group>
            <label>Genero:</label>            
            <b-form-select required="required" v-model="producto.Genero" :options="generos" />
          </b-form-group>
          <!--<b-form-group>
            <label>Botapie/Manga:</label>
            <autocomplete
                required="required"
                :url="API + 'productos/botapies'"
                ref="inputBotapie"
                anchor="Botapie"
                label="writer"
                :placeholder.sync="producto.Botapie"
                :on-select="val => producto.Botapie = val.Botapie && val.Botapie.toUpperCase()"
                :onInput="val => producto.Botapie = val && val.toUpperCase()"
                :onFocus="focusAutocomplete.bind(null, 'inputBotapie')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>-->
            <!--<b-form-input v-model="producto.Botapie"
              type="text"
              @input="(val) => producto.Botapie = val && val.toUpperCase()"              
              ></b-form-input>-->
          <!--</b-form-group>-->
          <b-form-group>
            <label>Porcentaje Utilidad:</label>
            <input 
              required="required"
              v-model="producto.PorcentajeUtilidad"
              @input="actualizarPrecioContado"
              type="number"
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
              step="0.0001"
              class="form-control"
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
          <b-form-group>
            <label>Stock Minimo:</label>
            <input   
              required="required"
              v-model="producto.StockMinimo"
              type="number"
              step="0.001"
              class="form-control"
              />
          </b-form-group>
          <b-form-group>
            <b-form-checkbox
                   v-model="producto.TienePresentacion"
                   :value="1"
                   :unchecked-value="0">
              Tiene Presentacion
            </b-form-checkbox>
          </b-form-group>

          <b-form-group v-if="true">
            <label>Imagen:</label>
            <input type="file" id="file" ref="file" @change="handleFileUpload()"/>
            <div class="text-center p-1">
              <b-img :src="'/resources/images/productos/' + producto.Imagen" width="180px" fluid alt=""></b-img>
            </div>
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
              type="number"
              class="form-control"
              step="0.0001"
              />
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Precio de Venta:</label>
            <input v-model="producto.PrecioContado"
              type="number"
              class="form-control"
              step="0.001"
              @input="actualizarLiquidacion"
              />
              <b-form-text v-if="producto.precioContadoNormal">
                Original - {{ producto.precioContadoNormal | redondear }}
              </b-form-text>
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Precio Convenio:</label>
            <input v-model="producto.PrecioConvenio"
              type="number"
              step="0.001"
              class="form-control"
              />
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-row>
            <b-col>
              <label>% de Liquidación:</label>
              <input v-model="producto.PorcentajeLiquidacion"
              type="number"
              class="form-control"
              step="1"
              min="0"
              max="100"
              @input="(val)=>actualizarLiquidacion(val.target.value)"
              />
            </b-col>
            <b-col>
              <label>Precio Final:</label>
              <br>
              <div style="display:flex; justify-content: start; align-items: center; margin-top: 6px;">
                <b>{{ precioLiquidacion }}</b>
              </div>
            </b-col>
          </b-row>
        </b-col>
      </b-row>


      <b-form-group v-if="producto.TienePresentacion==1">
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
          <producto-form-det :id-producto="producto.IdProducto" v-if="showProductoDet && show" ref="productoDet"></producto-form-det>
        </b-col>
      </b-row>

      <div slot="modal-footer" >
        <div style="left: 1rem;position: absolute;" v-if="producto.IdProducto">
          <b-input-group>
            <b-form-input
              v-model="cantidadCodigoBarra"
              type="number"
              style="width:80px;"
            ></b-form-input>
            <b-input-group-append>
              <b-btn variant="info" target="_blank" :href="'/views/codigoBarra.php?idProducto=' + producto.IdProducto + '&cantidad=' + cantidadCodigoBarra ">Código de barra</b-btn>
            </b-input-group-append>
          </b-input-group>
        </div>
        <b-btn  variant="danger" @click="eliminarProducto(producto)" v-if="producto.IdProducto">
          Anular
        </b-btn>
        <b-btn  variant="warning" @click="clearForm" v-if="!producto.IdProducto">
          Limpiar
        </b-btn>
        <b-btn  variant="primary" type="submit">
           {{this.mensajeBtn}}
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
      mensajeBtn: 'Crear',
      showNew:false,
      showProductoDet: false,
      cantidadCodigoBarra: 1,
      title: 'Nuevo Producto',
      API: config.API,
      show: false, 
      generos: ['VARON', 'DAMA', 'NIÑO', 'NIÑA', 'UNISEX', 'JUNIOR'],
      producto: {},
      productosDet: [],
      precioLiquidacion: 0,
      file: ''
    }
  },
  beforeMount () {
    this.initForm()
  },
  props: ['showButton', 'productoUpdate', 'cbGuardar'],
  watch: {
    show (val) {
      if (val) {
        
      }
      else {
        this.producto.IdProducto;
      }
    },
    productoUpdate (newVal) {
      if (newVal.IdProducto) {
        this.mensajeBtn='Guardar'
        this.showNew = true;
        this.title = 'Editar Producto << ' + newVal.CodigoBarra + ' >> '
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

        this.$refs.inputInstitucion.setValue(newVal.Institucion)

        this.$refs.inputTalla.setValue(newVal.ProductoTalla)
        
        this.$refs.inputColor.setValue(newVal.Color)
        //this.$refs.inputBotapie.setValue(newVal.Botapie)
        
        /* this.producto.modelo = {
          IdProductoModelo: newVal.IdProductoModelo,
          ProductoModelo: newVal.ProductoModelo
        } */
        this.producto.talla = {
          IdProductoTalla: newVal.IdProductoTalla,
          ProductoTalla: newVal.ProductoTalla
        }
        this.producto.Producto = newVal.Producto.split('-').slice(0, -1).join()
        this.producto.IdProducto = newVal.IdProducto
        this.producto.Genero = newVal.Genero
        //this.producto.Botapie = newVal.Botapie
        this.producto.Color = newVal.Color
        this.producto.PorcentajeUtilidad = newVal.PorcentajeUtilidad
        this.producto.PrecioPorMayor = newVal.PrecioPorMayor
        this.producto.StockPorMayor = newVal.StockPorMayor
        this.producto.StockMinimo = newVal.StockMinimo
        this.producto.CodigoBarra = newVal.CodigoBarra
        this.producto.ProductoModelo = newVal.ProductoModelo
        this.producto.PrecioCosto = newVal.PrecioCosto
        this.producto.PrecioContado = newVal.PrecioContado
        this.producto.PrecioConvenio = newVal.PrecioConvenio
        this.producto.ControlaStockMinimo   = newVal.ControlaStockMinimo
        this.producto.Imagen = newVal.Imagen
        this.producto.TienePresentacion     = newVal.TienePresentacion
        this.producto.PorcentajeLiquidacion = newVal.PorcentajeLiquidacion
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
        this.actualizarLiquidacion()

      } else {
        this.precioLiquidacion = 0
        this.title = 'Nuevo Producto'
        this.showNew = false;
        this.mensajeBtn = 'Crear';
        this.clearForm()
        this.$refs.file.value=null
      }
    }
  },
  methods: {
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
    handleFileUpload() {
      this.file = this.$refs.file.files[0]
    },
    eliminaId(){
      delete this.producto.IdProducto;
      this.mensajeBtn = 'Crear';
      this.showNew = false;
      this.$refs.inputCodigoBarra.setValue('');
      //console.log(this.producto,"asdasd");
    },
    actualizarLiquidacion(){
      this.precioLiquidacion = this.producto.PrecioContado -  Math.round(((this.producto.PorcentajeLiquidacion>0?this.producto.PorcentajeLiquidacion:0)*(this.producto.PrecioContado>0?this.producto.PrecioContado:0)/100)* 10) / 10;
      this.actualizarPorcentajeUtilidad()
    },
    actualizarPrecioContado () {
      var precioCosto = Number(this.producto.PrecioCosto)
      var utilidad = Number(this.producto.PorcentajeUtilidad)
      // var nuevoPrecioContado = precioCosto + (precioCosto * (utilidad / 100))
      //var nuevoPrecioContado = precioCosto / (1 - (utilidad / 100))
      var nuevoPrecioContado = precioCosto *(utilidad / 100) + precioCosto
      this.producto.precioContadoNormal = nuevoPrecioContado
      //this.producto.PrecioContado = this.$options.filters.redondeoMayor(nuevoPrecioContado)
      this.producto.PrecioContado = this.$options.filters.redondear(nuevoPrecioContado, 1)

    },
    actualizarPorcentajeUtilidad () {
      var precioContado = Number(this.producto.PrecioContado)
      var precioCosto = Number(this.producto.PrecioCosto)
      this.producto.PorcentajeUtilidad = this.$options.filters.redondear(((precioContado - precioCosto)/precioCosto) * 100)
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
    setTalla (obj) {
      this.producto.talla = obj
      this.$refs.inputTalla.setValue(obj.ProductoTalla)
    },
    clearForm () {
      this.$refs.inputCategoria.setValue('')
      this.$refs.inputMarca.setValue('')
      this.$refs.inputMedicion.setValue('UNIDAD')
      this.$refs.inputTalla.setValue('')
      this.$refs.inputModelo.setValue('')
      this.$refs.inputInstitucion.setValue('')
      this.$refs.inputColor.setValue('')
      //this.$refs.inputBotapie.setValue('')

      this.initForm()
    },
    initForm () {
      this.producto = {
        Anulado: 0,
        IdProductoMarca: null,
        IdProductoMedicion: null,
        IdProductoCategoria: null,
        // IdProductoModelo: null,
        IdProductoBotapie: null,
        IdProductoTalla: '',
        Producto: '',
        CodigoBarra: '',
        Color: '',
        PorcentajeUtilidad: '',
        StockMinimo: 0,
        PrecioContado: '',
        PrecioConvenio: 0,
        PrecioPorMayor: 0,
        StockPorMayor: 0,
        Botapie: '',
        Genero: '',
        PrecioContado: 0,
        PorcentajeLiquidacion: 0,
        PrecioCosto: 0,
        ControlaStockMinimo:0,
        TienePresentacion: 0,
        categoria: {},
        marca: {},
        Imagen: null,
        // modelo: {},
        talla: {},
        medicion: {
          IdProductoMedicion:6,
          ProductoMedicion:"UNIDAD"
        },
        precios: [
        {nombre: 'NOMBRE 1', cantidad: 0, precioc: 0, precio: 0},
        {nombre: 'NOMBRE 2', cantidad: 0, precioc: 0, precio: 0},
        {nombre: 'NOMBRE 3', cantidad: 0, precioc: 0, precio: 0},
        {nombre: 'NOMBRE 4', cantidad: 0, precioc: 0, precio: 0},
        ]
      }
    },
    guardarProducto(e) {
      e.preventDefault();

      // filtro si esta ingresado correctamente
      if (this.$refs.inputCategoria.$refs.input.value.toLowerCase() != this.producto.categoria.ProductoCategoria.toLowerCase()) {
        this.$toasted.show('Falta seleccionar o guardar la Categoría.')
        return;
      }

      if (this.$refs.inputMarca.$refs.input.value.toLowerCase() != this.producto.marca.ProductoMarca.toLowerCase()) {
        this.$toasted.show('Falta seleccionar o guardar la Marca.')
        return;
      }

      if (this.$refs.inputTalla.$refs.input.value.toLowerCase() != this.producto.talla.ProductoTalla.toLowerCase()) {
        this.$toasted.show('Falta seleccionar o guardar la Talla.')
        return;
      }

      this.producto.PorcentajeLiquidacion = (this.producto.PorcentajeLiquidacion>0?this.producto.PorcentajeLiquidacion:0);
      this.producto.CodigoBarra = (''+this.producto.CodigoBarra).trim();

      // Agregar producto DET
      if (this.showProductoDet) {
        this.producto.productosDet = this.$refs.productoDet.productos
      }

      this.axios.post(this.API + 'productos', {
        ...this.producto, 
        PreciosPorProducto: this.producto.precios 
      })
      .then((response) => {
        if (this.cbGuardar) {
          this.cbGuardar(this.producto.IdProducto?this.producto:response.data.insertId)
        }
        if (response.data.error) {
          this.$toasted.show(response.data.error)
        } else {
          this.$toasted.success('Producto guardado correctamente')
          // this.initForm()
          //this.producto.talla = {}
          //this.$refs.inputTalla.setValue('')
          this.$refs.productoForm.hide()
        }

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
