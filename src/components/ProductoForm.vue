<template>
  <div>
    <b-btn v-if="showButton" v-b-modal.modalProducto variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarProducto" @keydown.enter.prevent="()=>{}" >
    <b-modal :no-close-on-esc="true"  no-close-on-backdrop id="modalProducto" :title="title" size="lg" ref="productoForm" v-model="show">
      <b-row>
        <b-col md="6">
          <b-form-group>
            <label>Laboratorio:</label>
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
            <label>Bloque:</label>
            <b-input-group>
              <autocomplete
                required="required"
                :url="API + 'bloque'"
                ref="inputBloque"
                anchor="Bloque"
                label="writer"
                :placeholder.sync="producto.bloque.Bloque"
                :on-select="setBloque"
                :onFocus="focusAutocomplete.bind(null, 'inputBloque')"                    
                :classes="{input: 'form-control upper'}">
              </autocomplete>
              <b-input-group-append>
                <b-btn variant="primary" @click="crearTalla"><b>Guardar</b></b-btn>                
                <!--<marca-form :showButton="true" :cbGuardar="setMarca"></marca-form>-->
              </b-input-group-append>
            </b-input-group>
          </b-form-group>

          <!-- <b-form-group>
            <label>Artículo:</label>
            <b-form-input v-model="producto.Producto"
              required="required"
              type="text"
              @input="(val) => producto.Producto = val && val.toUpperCase()"
              :value="producto.Producto"
              ></b-form-input>
          </b-form-group> -->

          
          <!-- <b-form-group>
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
            </b-input-group> -->
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
          <!-- </b-form-group> -->
          <!-- <b-form-group>
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
              </autocomplete> -->
            <!--<b-form-input v-model="producto.Color"
              type="text"
              @input="(val) => producto.Color = val && val.toUpperCase()"              
              ></b-form-input>-->
          <!-- </b-form-group> -->
          
          
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Forma Farmaceutica:</label>
            <b-input-group>
              <autocomplete
                required="required"
                :url="API + 'formafarmaceutica'"
                ref="inputFormaFarmaceutica"
                anchor="ProductoFormaFarmaceutica"
                label="writer"
                :placeholder="producto.formafarmaceutica.ProductoFormaFarmaceutica"
                :on-select="setFormaFarmaceutica"
                :onFocus="focusAutocomplete.bind(null, 'inputFormaFarmaceutica')"
                :classes="{input: 'form-control upper'}">
              </autocomplete>
              <b-input-group-append>
                <b-btn variant="primary" @click="crearProductoFormaFarmaceutica"><b>Guardar</b></b-btn>
                <!--<categoria-form :showButton="true" :cbGuardar="setCategoria"></categoria-form>-->
              </b-input-group-append>
            </b-input-group>
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
                  
          
          <!-- <b-form-group>
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
                
              </b-input-group-append>
            </b-input-group>
          </b-form-group> -->
          <!-- <b-form-group>
            <label>Genero:</label>            
            <b-form-select required="required" v-model="producto.Genero" :options="generos" />
          </b-form-group> -->
          <!-- <b-form-group>
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
              </autocomplete>
           
          </b-form-group> -->
          <!-- <b-form-group>
            <label>Porcentaje Utilidad:</label>
            <b-form-input 
              required="required"
              v-model="producto.PorcentajeUtilidad"
              @input.native="actualizarPrecioContado"
              type="number"
              ></b-form-input>
          </b-form-group> -->
        </b-col>
      </b-row>
      <b-row>

        <b-col cols="12">
          <b-form-group>
            <label>Producto:</label>
            <b-form-input v-model="producto.Producto"
            required="required"
            type="text"
            @input="(val) => producto.Producto = val && val.toUpperCase()"
            :value="producto.Producto"
            ></b-form-input>
          </b-form-group>  
        </b-col>

        <b-col cols="12">
          <b-form-group>
            <label>Descripción:</label>
            <b-form-textarea v-model="producto.ProductoDesc"/>
            
          </b-form-group>  
        </b-col>
        <b-col cols="12">
          <b-form-group>
            <label>Anaquel:</label>
            <b-form-input v-model="producto.ProductoDescCorto" />
            
          </b-form-group>  
        </b-col>
        <b-col cols="6">
            <b-form-group>
              <label>Codigo Barra:</label>
              <b-form-input v-model="producto.CodigoBarra" />
            </b-form-group>  
        </b-col>
        <b-col cols="6">
          <b-form-group>
            <label>Codigo:</label>
            <b-form-input v-model="producto.Codigo" />
            </b-form-group>
        </b-col>
        <b-col cols="12">
          <b-form-group>
            <label>Dosis:</label>
            <b-form-input v-model="producto.Dosis" />
            
          </b-form-group>  
        </b-col>
        <b-col cols="12">
          <b-form-group>
            <label>Precio Costo :</label>
            <b-form-input type="number"  v-model="producto.PrecioCosto" />
          </b-form-group>  
        </b-col>

        <b-col cols="12">
          <b-form-group>
            
            <b-form-checkbox 
              v-model="producto.VentaEstrategica"
              :value="1"
              :unchecked-value="0"
            >
              Venta Estrategica ⭐
            </b-form-checkbox>
          </b-form-group>  
        </b-col>
        <b-col cols="6">
          <b-form-group>
            <label>Porcentaje Utilidad :</label>
            <b-form-input type="number" step="0.01" v-model="producto.PorcentajeUtilidad" />
          </b-form-group>  
        </b-col>
        <b-col cols="6">
          <b-form-group>
            <label>Precio Venta :</label>
            <b-form-input type="number" step="0.01" v-model="producto.PrecioContado" />
          </b-form-group>  
        </b-col>
        <b-col cols="6">
          <b-form-group>
            <label>Precio X Mayor :</label>
            <b-form-input type="number" step="0.01" v-model="producto.PrecioPorMayor" />
          </b-form-group>  
        </b-col>
        <b-col cols="6">
          <b-form-group>
            <label>Stock X Mayor :</label>
            <b-form-input type="number" step="0.01" v-model="producto.StockPorMayor" />
          </b-form-group>  
        </b-col>
         <b-col cols="12">
           <div class="headerPanel">
            Compuestos 

          </div>
            
            <autocomplete
                ref="autocompleteCompuesto"
                :url="`${API}/compuestos/listado`"
                :debounce="600"
                anchor="ProductoCompuesto"
                label="writer"
                placeholder="Agregar Compuesto"
                :on-select="setCompuesto"
                :onFocus="focusAutocomplete.bind(null, 'autocompleteCompuesto')"
                :classes="{input: 'form-control'}">
            </autocomplete>

             <table class="table">
            <thead>
              <tr>
                <th>#</th>
                <th>Compuesto</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(compuesto,index) in producto.Compuestos" :key="index">
                  <td>{{ index+1 }}</td>
                  <td>{{ compuesto.ProductoCompuesto }}</td>
                  <td>
                    <b-button @click="elminarCompuesto(index)" size="sm" variant="danger"><i class="fa fa-trash" aria-hidden="true"></i></b-button>  
                  </td>
              </tr>
            </tbody>
         </table>


         </b-col>

         
         <b-col cols="12">
           <hr/>
          </b-col>
         <b-col cols="6">
          <b-form-group>
             <b-form-checkbox 
              v-model="producto.ControlaStock"
              :value="1"
              :unchecked-value="0"
            >
              Controla Stock
            </b-form-checkbox>
           
          </b-form-group>  
        </b-col>

        <b-col cols="6">
          <b-form-group v-if="producto.ControlaStock==1">
            <label>Stock Minimo :</label>
            <b-form-input type="number" step="0.01" v-model="producto.StockMinimo" />
          </b-form-group>  
        </b-col>
        <b-col cols="12">
           <hr/>
          </b-col>
         <b-col cols="12">
          <div class="headerPanel">
            Presentaciones <b-button @click="anadirPresentacion" size="sm" variant="success"><i class="fa fa-plus" aria-hidden="true"></i></b-button> 

          </div>
            
            
            <table class="table">
              <thead>
                <tr>
                  <th>Nombre</th>
                  <th>Cantidad</th>
                  <th>P. Costo</th>
                  <th>P. Venta</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(pres,index) in producto.Presentaciones" :key="index">
                  <td> <input :id="`txtNombrePres${index}`" type="text" class="form-control" v-model="pres.nombre"> </td>
                  <td> <input type="number" step="0.01" class="form-control" v-model="pres.cantidad"> </td>
                  <td> <input type="number" step="0.01" class="form-control" v-model="pres.preciocosto"> </td>
                  <td> <input type="number" step="0.01" class="form-control" v-model="pres.precioventa"> </td>
                  <td>
                      <b-button @click="elminarPresentacion(index)" size="sm" variant="danger"><i class="fa fa-trash" aria-hidden="true"></i></b-button>  
                  </td>
                </tr>
              </tbody>
            </table>
         </b-col>
         <b-col cols="12">
           <hr/>
          </b-col>
          <b-col cols="6">
          <b-form-group>
             <b-form-checkbox 
              v-model="producto.Oferta"
              :value="1"
              :unchecked-value="0"
            >
              Producto en Oferta 😀
            </b-form-checkbox>
           
          </b-form-group>  
        </b-col>
         <b-col cols="6">
          <b-form-group>
             <b-form-checkbox 
              v-model="producto.Vencer"
              :value="1"
              :unchecked-value="0"
            >
              Producto Próximo a vencer ⏱
            </b-form-checkbox>
           
          </b-form-group>  
        </b-col>
         <b-col cols="6">
          <b-form-group>
             <b-form-checkbox 
              v-model="producto.NotaVenta"
              :value="1"
              :unchecked-value="0"
            >
              Producto Nota Venta
            </b-form-checkbox>
           
          </b-form-group>  
        </b-col>
      </b-row>

      <hr />
      <b-row>
        <b-col>
          <producto-form-det 
              :id-producto="producto.IdProducto" 
              v-if="showProductoDet && show" 
              ref="productoDet">
          </producto-form-det>
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
        <!--<b-btn  variant="danger" @click="eliminarProducto(producto)" v-if="producto.IdProducto">
          Eliminar
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
import config from "@/store/config.js";
import CategoriaForm from "@/components/CategoriaForm";
import MarcaForm from "@/components/MarcaForm";
import ProductoFormDet from "@/components/ProductoFormDet"; // import ModeloForm from '@/components/ModeloForm'

export default {
  data() {
    return {
      showProductoDet: false,
      cantidadCodigoBarra: 1,
      title: "Nuevo Producto",
      API: config.API,
      show: false,
      generos: ["VARON", "DAMA", "NIÑO", "NIÑA", "UNISEX", "JUNIOR"],
      producto: {},
      productosDet: [],
      presentacion: {
        nombre: "",
        cantidad: 0,
        preciocosto: 0,
        precioventa: 0
      },
      idAlmacen: 1,
      isVentas: 1
    };
  },
  beforeMount() {
    this.initForm();
  },
  props: ["showButton", "productoUpdate", "cbGuardar"],
  watch: {
    async productoUpdate(newVal) {
      this.$refs["inputFormaFarmaceutica"].type = "";
      this.$refs["inputFormaFarmaceutica"].handleDoubleClick();
      if (newVal.IdProducto) {
        let Compuesto = await this.axios(
          `${this.API}/compuestos_por_producto`,
          {
            params: {
              IdProducto: newVal.IdProducto
            }
          }
        ).then(res => {
          if (res.data) {
            return res.data;
          } else {
            return [];
          }
        });
        console.log("Compuesto", Compuesto);
        this.title = "Editar Producto << " + newVal.CodigoBarra + " >>";
        this.producto.categoria = {
          IdProductoCategoria: newVal.IdProductoCategoria,
          ProductoCategoria: newVal.ProductoCategoria
        };
        this.producto.marca = {
          IdProductoMarca: newVal.IdProductoMarca,
          ProductoMarca: newVal.ProductoMarca
        };
        this.producto.medicion = {
          IdProductoMedicion: newVal.IdProductoMedicion,
          ProductoMedicion: newVal.ProductoMedicion
        };
        this.producto.formafarmaceutica = {
          IdProductoFormaFarmaceutica: newVal.IdProductoFormaFarmaceutica,
          ProductoFormaFarmaceutica: newVal.ProductoFormaFarmaceutica
        };

        this.producto.bloque = {
          IdBloque: newVal.IdBloque,
          Bloque: newVal.Bloque
        };

        // this.$refs.inputCategoria.setValue('')
        this.$refs.inputCategoria.setValue(newVal.ProductoCategoria);

        this.$refs.inputFormaFarmaceutica.setValue(
          newVal.ProductoFormaFarmaceutica
        );

        // this.$refs.inputMarca.setValue('')
        this.$refs.inputMarca.setValue(newVal.ProductoMarca);

        // this.$refs.inputMedicion.setValue('')
        this.$refs.inputMedicion.setValue(newVal.ProductoMedicion);

        this.$refs.inputBloque.setValue(newVal.Bloque);

        // this.$refs.inputModelo.setValue('')
        // this.$refs.inputModelo.setValue(newVal.ProductoModelo);

        // this.$refs.inputTalla.setValue(newVal.ProductoTalla);

        // this.$refs.inputColor.setValue(newVal.Color);
        // this.$refs.inputBotapie.setValue(newVal.Botapie);

        /* this.producto.modelo = {
          IdProductoModelo: newVal.IdProductoModelo,
          ProductoModelo: newVal.ProductoModelo
        } */
        this.producto.talla = {
          IdProductoTalla: newVal.IdProductoTalla,
          ProductoTalla: newVal.ProductoTalla
        };
        this.producto.Producto = newVal.Producto.split("-")
          .slice(0, -1)
          .join();
        this.producto.IdProducto = newVal.IdProducto;
        this.producto.Genero = newVal.Genero;
        this.producto.Botapie = newVal.Botapie;
        this.producto.Color = newVal.Color;
        this.producto.PorcentajeUtilidad = newVal.PorcentajeUtilidad;
        this.producto.CodigoBarra = newVal.CodigoBarra;
        this.producto.Codigo = newVal.Codigo;
        this.producto.ProductoModelo = newVal.ProductoModelo;
        this.producto.PrecioCosto = newVal.PrecioCosto;
        this.producto.Producto = newVal.Producto;
        this.producto.ProductoDesc = newVal.ProductoDesc;
        this.producto.Dosis = newVal.Dosis;
        this.producto.ProductoDescCorto = newVal.ProductoDescCorto;
        this.producto.VentaEstrategica = newVal.VentaEstrategica;
        this.producto.PrecioPorMayor = newVal.PrecioPorMayor;
        this.producto.StockPorMayor = newVal.StockPorMayor;
        this.producto.ControlaStock = newVal.ControlaStock;
        this.producto.StockMinimo = newVal.StockMinimo;
        this.producto.Presentaciones = JSON.parse(newVal.Presentaciones);
        this.producto.PrecioContado = newVal.PrecioContado;
        this.producto.Vencer = newVal.Vencer;
        this.producto.Oferta = newVal.Oferta;
        this.producto.NotaVenta = newVal.NotaVenta;
        this.producto.Compuestos = Compuesto;
      } else {
        this.title = "Nuevo Producto";
        this.clearForm();
      }
    }
  },
  methods: {
    elminarPresentacion(index) {
      this.producto.Presentaciones.splice(index, 1);
      this.$forceUpdate();
    },
    elminarCompuesto(index) {
      this.producto.Compuestos.splice(index, 1);
      this.$forceUpdate();
    },
    anadirPresentacion() {
      this.producto.Presentaciones.unshift(this.presentacion);
      this.$forceUpdate();
      this.$nextTick(() => {
        document.getElementById("txtNombrePres0").focus();
      });
    },
    setCompuesto(compuesto) {
      if (
        this.producto.Compuestos.some(
          v => v.IdProductoCompuesto == compuesto.IdProductoCompuesto
        )
      ) {
        this.$toasted.show("Componente ya en lista");
        return false;
      }
      this.producto.Compuestos.unshift(compuesto);
      this.$forceUpdate();
    },

    /*eliminarProducto (producto) {
      // NO eliminar porque no distinque movimientos ni ventas
      let r = confirm('¿Esta seguro de eliminar este producto?')
      if (r) {
        this.axios.post(this.API + 'productos/delete', {
          id: producto.IdProducto
        }).then(res => {
          console.log(res.data)
        })
      }
    },*/
    actualizarPrecioContado() {
      var precioCosto = this.producto.PrecioCosto;
      var utilidad = this.producto.PorcentajeUtilidad;
      var nuevoPrecioContado = precioCosto + precioCosto * (utilidad / 100);

      this.producto.precioContadoNormal = nuevoPrecioContado;
      this.producto.PrecioContado = this.$options.filters.redondeoMayor(
        nuevoPrecioContado
      );
    },
    setCategoria(obj) {
      this.producto.categoria = obj;
      this.$refs.inputCategoria.setValue(obj.ProductoCategoria);
    },
    setFormaFarmaceutica(obj) {
      this.producto.ProductoFormaFarmaceutica = obj;
      this.$refs.inputFormaFarmaceutica.setValue(obj.ProductoFormaFarmaceutica);
    },
    setMedicion(obj) {
      this.producto.medicion = obj;
    },
    setMarca(obj) {
      this.producto.marca = obj;
      this.$refs.inputMarca.setValue(obj.ProductoMarca);
    },
    /* setModelo (obj) {
      this.producto.modelo = obj
      this.$refs.inputModelo.setValue(obj.ProductoModelo)
    }, */
    setTalla(obj) {
      this.producto.talla = obj;
      this.$refs.inputTalla.setValue(obj.ProductoTalla);
    },
    setBloque(obj) {
      this.producto.producto = obj;
      this.$refs.inputTalla.setValue(obj.Bloque);
    },
    clearForm() {
      this.$refs.inputCategoria.setValue("");
      this.$refs.inputMarca.setValue("");
      this.$refs.inputMedicion.setValue("UNIDAD");
      this.$refs.inputTalla.setValue("");
      this.$refs.inputModelo.setValue("");
      this.$refs.inputColor.setValue("");
      this.$refs.inputBotapie.setValue("");

      this.initForm();
    },
    initForm() {
      this.producto = {
        Anulado: 0,
        IdProductoMarca: null,
        IdProductoMedicion: null,
        IdProductoCategoria: null,
        // IdProductoModelo: null,
        IdProductoBotapie: null,
        IdProductoTalla: "",
        Producto: "",
        CodigoBarra: "",
        Color: "",
        PorcentajeUtilidad: "",
        PrecioContado: "",
        Botapie: "",
        Genero: "",
        categoria: {},
        marca: {},
        formafarmaceutica: {},
        bloque: {},
        // modelo: {},
        talla: {},
        medicion: {
          IdProductoMedicion: 6,
          ProductoMedicion: "UNIDAD"
        }
      };
    },
    guardarProducto(e) {
      e.preventDefault();
      // Agregar producto DET
      if (this.showProductoDet) {
        this.producto.productosDet = this.$refs.productoDet.productos;
      }
      this.$refs.autocompleteCompuesto.clearInput();
      let prod = { ...this.producto };
      let Presesentacion = JSON.stringify(this.producto.Presentaciones);
      prod.Presesentacion = Presesentacion;
      this.axios
        .post(this.API + "productos", prod)
        .then(response => {
          if (this.cbGuardar) {
            this.cbGuardar(response.data.insertId);
          }
          if (response.data.error) {
            this.$toasted.show(
              "Producto insertado correctamente - " + response.data.codigoBarra
            );
          } else {
            this.$toasted.show("Producto guardado correctamente");
          }

          // this.initForm()
          this.producto.talla = {};
          this.$refs.inputTalla.setValue("");

          this.$refs.productoForm.hide();
        })
        .catch(error => {
          console.log(error);
          // this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    crearCategoria() {
      if (this.$refs.inputCategoria.type) {
        let categoria = {};
        categoria.ProductoCategoria = this.$refs.inputCategoria.type.toUpperCase();

        this.axios
          .post(this.API + "categorias", categoria)
          .then(response => {
            this.$toasted.show("Categoría guardada correctamente");
            this.setCategoria({
              IdProductoCategoria: response.data.insertId,
              ProductoCategoria: response.data.ProductoCategoria
            });
          })
          .catch(error => {
            console.log(error);
            this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          });
      }
    },
    crearMarca() {
      if (this.$refs.inputMarca.type) {
        let marca = {};
        marca.ProductoMarca = this.$refs.inputMarca.type.toUpperCase();

        this.axios
          .post(this.API + "marcas", marca)
          .then(response => {
            this.$toasted.show("Marca guardada correctamente");
            this.setMarca({
              IdProductoMarca: response.data.insertId,
              ProductoMarca: response.data.ProductoMarca
            });
          })
          .catch(error => {
            console.log(error);
            this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          });
      }
    },
    crearTalla() {
      if (this.$refs.inputTalla.type) {
        let talla = {};
        talla.ProductoTalla = this.$refs.inputTalla.type.toUpperCase();

        this.axios
          .post(this.API + "tallas", talla)
          .then(response => {
            this.$toasted.show("Talla guardada correctamente");
            this.setTalla({
              IdProductoTalla: response.data.insertId,
              ProductoTalla: response.data.ProductoTalla
            });
          })
          .catch(error => {
            console.log(error);
            this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          });
      }
    },
    crearProductoFormaFarmaceutica() {
      return;
      if (this.$refs.inputTalla.type) {
        let talla = {};
        talla.ProductoTalla = this.$refs.inputTalla.type.toUpperCase();

        this.axios
          .post(this.API + "tallas", talla)
          .then(response => {
            this.$toasted.show("Talla guardada correctamente");
            this.setTalla({
              IdProductoTalla: response.data.insertId,
              ProductoTalla: response.data.ProductoTalla
            });
          })
          .catch(error => {
            console.log(error);
            this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          });
      }
    },
    crearTalla() {
      return;
      if (this.$refs.inputTalla.type) {
        let talla = {};
        talla.ProductoTalla = this.$refs.inputTalla.type.toUpperCase();

        this.axios
          .post(this.API + "tallas", talla)
          .then(response => {
            this.$toasted.show("Talla guardada correctamente");
            this.setTalla({
              IdProductoTalla: response.data.insertId,
              ProductoTalla: response.data.ProductoTalla
            });
          })
          .catch(error => {
            console.log(error);
            this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          });
      }
    },
    renderChild(data) {
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

        // if(fechaVen < hoy && data.stock > 0) {
        /*if(fechaVen && fechaVen < hoy) {
          style = 'danger'
        }

        // cambiar color los que faltan 4 meses por vencer
        // if (fechaVen >= hoy && fechaVen <= en4meses && data.stock > 0) {
        if (fechaVen >= hoy && fechaVen <= en4meses) {
          style = 'ambar'
        }*/

        let styleStock = "";
        let styleCondition = "";
        let styleVencerText = "";

        if (data.stock > 0) {
          styleStock = "table-info";
        } else {
          styleStock = "table-danger";
        }

        if (data.VentaEstrategica == 1) {
          styleCondition = "background-color: #FFCE05 !important;";
        }

        if (data.Oferta == 1) {
          styleCondition =
            "background-color: #28AA45 !important; color:white !important;";
          styleVencerText = "color:white !important;";
        }

        if (data.Vencer == 1) {
          styleCondition =
            "background-color: #dc3545 !important; color:white !important;";
          styleVencerText = "color:white !important;";
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
            color: #000000;
          }
        </style>

        <div class="${style}" style="${styleCondition}">
          <table id="table-render" style="width: 1050px; table-layout: fixed; word-wrap:break-word;">
            <thead>
              <tr style>
                <th class="table-info text-center" style="width: 60px;">Stock</th>
                <th style="width: 250px; padding-left:10px !important; ${styleVencerText}">Producto</th>
                <th style="width: 150px; padding-left:10px !important; ${styleVencerText}">Forma</th>
                <th style="width: 100px; padding-left:10px !important; ${styleVencerText}">Medición</th>
                <th style="width: 150px; padding-left:10px !important; ${styleVencerText}">Lab</th>                
                <th style="width: 100px; padding-left:10px !important; ${styleVencerText}">Bloque</th>
                <th style="width: 200px; padding-left:10px !important; ${styleVencerText}">Compuestos</th>
                <th class="table-success text-center" style="width: 65px;">Precio.</th>
                <th class="text-center" style="width: 35px; ${styleVencerText}">V.E.</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="${styleStock} text-center"><b>${
          data.ControlaStock == 1
            ? data.stock
            : '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />'
        }</b></td>
                <td style="padding-left:10px !important; ${styleVencerText}"><b>${
          data.Producto
        }</b></td>
                <td style="padding-left:10px !important; ${styleVencerText}">${
          data.ProductoFormaFarmaceutica
        }</td>
                <td style="padding-left:10px !important; ${styleVencerText}">${
          data.ProductoMedicion
        }</td>
                <td style="padding-left:10px !important; ${styleVencerText}">${
          data.ProductoMarca
        }</td>
                <td style="font-size:10px; padding-left:10px !important; ${styleVencerText}"><b>${
          data.Bloque
        }</b></td>
                <td style="font-size:10px; padding-left:10px !important; ${styleVencerText}">${
          data.CompuestosProducto
        }</td>
                <td class="table-success text-center"><b>${
                  data.PrecioContado
                }</b></td>
                <td class="text-center"><b style="font-size: 1.1rem">${
                  data.Vencer == 1
                    ? '<i class="fas fa-clock" style="color:white;"></i>'
                    : data.Oferta == 1
                      ? '<i class="fas fa-smile" style="color:white;"></i>'
                      : data.VentaEstrategica == 1
                        ? '<i class="fas fa-star" style="color:white;"></i>'
                        : ""
                }</b></td>
              </tr>
            </tbody>
          </table>   
        </div>
        `;
      }
      return `
        <b>${data.CodigoBarra}</b> / <b>${data.Producto}</b> / ${
        data.ProductoFormaFarmaceutica
      } / ${data.ProductoMarca} 
      `;
    }
  },
  computed: {
    APIProductos() {
      if (this.idAlmacen) {
        return this.API + "productos/puntoventa/stock/" + this.idAlmacen;
      } else {
        return this.API + "productos";
      }
    }
  },
  components: {
    CategoriaForm,
    MarcaForm,
    ProductoFormDet
  }
};
</script>
<style scope>
.headerPanel {
  color: #3c763d;
  background-color: #dff0d8;
  border-color: #d6e9c6;
  padding: 1.5% 2%;
  border-radius: 15px 15px 0 0px;
}
</style>
