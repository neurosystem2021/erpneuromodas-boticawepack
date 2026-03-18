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
              <b-form-text id="input-formatter-help">
                Dejar en blanco para autogenerar
              </b-form-text>
          </b-form-group>
          <b-form-group>
            <label>Categoría:</label>
            <b-input-group>
              <autocomplete
                required="required"
                :url="API + 'categorias'"
                :customParams="{ id_empresa: empresa }"
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
                :on-select="setMarca" :customParams="{ id_empresa: empresa }"
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
                :url="API + 'productos/modelos'"
                ref="inputModelo"
                anchor="ProductoModelo"
                label="writer"
                :placeholder.sync="producto.ProductoModelo"
                :on-select="val => producto.ProductoModelo = val.ProductoModelo && val.ProductoModelo.toUpperCase()"
                :onInput="val => producto.ProductoModelo = val && val.toUpperCase()"
                :onFocus="focusAutocomplete.bind(null, 'inputModelo')"     :customParams="{ id_empresa: empresa }"           
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
            <label>Anaquel:</label>
            <autocomplete
                :url="API + 'productos/presentaciones'"
                ref="inputPresentacion"
                anchor="ProductoPresentacion"
                label="writer"
                :customParams="{ id_empresa: empresa }"
                :placeholder.sync="producto.ProductoPresentacion"
                :on-select="val => producto.ProductoPresentacion = val.ProductoPresentacion && val.ProductoPresentacion.toUpperCase()"
                :onInput="val => producto.ProductoPresentacion = val && val.toUpperCase()"
                :onFocus="focusAutocomplete.bind(null, 'inputPresentacion')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>
          </b-form-group>

          <b-form-group>
            <label>Imagen:</label>
            <b-img :src="'/resources/images/productos/' + producto.Imagen" fluid alt=""></b-img>
            <input type="file" id="file" ref="file" @change="handleFileUpload()"/>
          </b-form-group>

          <b-form-group>
            <label>1ra Descripción:</label>
            <b-form-textarea 
              ref="inputProductoDesc"
              rows="2"
              v-model="producto._ProductoDesc"
              @input="(val) => producto.ProductoDesc = val"
              style="field-sizing: content;"
              />
          </b-form-group>

          <b-form-group>
            <label>Procedencia:</label>
            <b-form-textarea 
              ref="inputProductoDesc3"
              rows="6"
              v-model="producto._ProductoDesc3"
              @input="(val) => producto.ProductoDesc3 = val"
              style="field-sizing: content;"
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
              initValue="UNIDADES"
              anchor="ProductoMedicion"
              label="writer" :customParams="{ id_empresa: empresa }"
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
              step="0.0001"
              class="form-control"
              />
          </b-form-group>
          <b-form-group>
            <label>Cantidad X Mayor:</label>
            <input 
              v-model="producto.PrecioPorMayor"
              type="number"
              min="0"
              step="0.0001"
              class="form-control"
              />
          </b-form-group>
          <b-form-group>
            <label>Cantidad X Bloque:</label>
            <input 
              v-model="producto.PrecioPorBloque"
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
          <b-form-group v-if="!producto.EsPadre">
            <label>Stock Minimo:</label>
            <input   
              v-model="producto.StockMinimo"
              type="number"
              class="form-control"
              step="0.0001"
              />
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
            <label>Activar Presentación(es):</label>
            <b-form-checkbox
                     v-model="tienePresentacionFlag"
                     :value="1"
                     :unchecked-value="0"
                     >
                    Seleccione esta opción si el producto contiene presentaciones.
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
          <b-form-group v-show="false">
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
            <label>Categoria Oferta en APP:</label>
            <b-form-select v-model="producto.IdOfertaCategoria" :options="categoriaItems" class="mb-3" size="md"/>
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
              step="0.0001"
              type="number"
              class="form-control"
              />
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Precio de Venta:</label>
            <input v-model="producto.PrecioContado"
              type="number"
              step="0.00001"
              @input="actualizarPorcentajeUtilidad"
              class="form-control"
              />
              <b-form-text v-if="producto.precioContadoNormal">
                Original - {{ producto.precioContadoNormal | redondear }}
              </b-form-text>
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Precio Por Mayor:</label>
            <input v-model="producto.precioConvenio"
              type="number"
              step="0.0001"
              class="form-control"
              />
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Precio Por Bloque:</label>
            <input v-model="producto.precioBloque"
              type="number"
              step="0.0001"
              class="form-control"
              />
          </b-form-group>
        </b-col>
      </b-row>

      <hr>
      <b-row v-if="tienePresentacionFlag ==1">
        
        <b-col md="3" v-for="i in 4" :key="i.id">
          <b-card :border-variant="producto.precios[i-1].nombre=='OFERTA'?'danger':'light'" :header="'Precio '+i" class="text-center"  v-bind:class="[producto.precios[i-1].nombre=='OFERTA' ? 'oferta' : '']">
            <b-form-group>
              <b-form-input
                v-model="producto.precios[i-1].nombre"
                
                type="text"
                style="text-transform:uppercase"
                @input="(val) => producto.precios[i-1].nombre = val && val.toUpperCase()"
                ></b-form-input>
            </b-form-group>
            <!-- :disabled="(idPerfil == 1) ? false : true" -->
            <b-form-group>
              <label>Cantidad:</label>
              <input
                v-model="producto.precios[i-1].cantidad"
                type="number"
                class="form-control"
                step="0.01"
                />
            </b-form-group>
            
            <b-form-group>
              <label>Precio:</label>
              <input 
                v-model="producto.precios[i-1].precio"
                type="number"
                class="form-control"
                step="0.00001"
                />
            </b-form-group>
            <b-form-group>
              <label>Total: {{(producto.precios[i-1].precio*producto.precios[i-1].cantidad) | redondear }}</label>
            </b-form-group>
          </b-card>
        </b-col>
      </b-row>

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

       <b-row>
        <b-col cols="12">
          <b-button  size="sm" class="my-2" variant="success" v-if="!nuevaSerie" @click="activarInactivarAgregarNuevaSerie">Nueva Serie</b-button>
          
        </b-col>
        <b-col cols="12" v-if="nuevaSerie">
          <b-row>
            <b-col cols="3">Serie:</b-col>
            <b-col cols="9"><input type="text" class="form-control" required v-model="Serie"></b-col>
             <b-col style="text-align: right;" class="my-2">
               <b-button  size="sm" variant="success" @click="anadirSerie">Guardar</b-button>
               <b-button  size="sm" variant="danger" @click="activarInactivarAgregarNuevaSerie">Cancelar</b-button>
              </b-col>   
           </b-row>   
        </b-col>         
        <b-col cols="12">         
           <b-table 
                striped 
                hover 
                :items="productoSerie" 
                :fields="headerSerie" 
                small
                :per-page="perPage"
                :current-page="currentPage"
            >
              <template slot="Estado" slot-scope="data">
                {{ data.item.Estado==1 ? 'Activo' : data.item.Estado==3? 'Vendido' :'Anulado'  }}
              </template>
               <template slot="action" slot-scope="data">
                  <b-button  size="sm" v-if="data.item.Estado==1" variant="danger" 
                    @click="eliminarRegistroSerie(data.item,data['index'])">
                    <i  class="fa fa-trash" aria-hidden="true"></i>
                  </b-button>
               </template>
           </b-table>

           <b-pagination
            v-model="currentPage"
            :total-rows="productoSerie.length"
            :per-page="perPage"
            aria-controls="my-table"
          ></b-pagination>

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
import ProductoFormDet from "@/components/ProductoFormDet";
// import ModeloForm from '@/components/ModeloForm'

export default {
  data() {
    return {
      tienePresentacionFlag: 0,
      showProductoDet: true,
      cantidadCodigoBarra: 1,
      title: "Nuevo Producto",
      API: config.API,
      show: false,
      // generos: ['VARON', 'DAMA', 'NIÑO', 'NIÑA', 'UNISEX', 'JUNIOR'],
      producto: {},
      productosDet: [],
      file: "",
      idPerfil: sessionStorage.getItem("IdPerfil"),
      categoriaItems: [],
      productoSerie: [],
      nuevaSerie: false,
      headerSerie: [
        { label: "Serie", key: "Serie" },
        { label: "Estado", key: "Estado" },
        { label: "Doc Venta", key: "CorrelativoVenta" },
        { label: "", key: "action" }
      ],
      currentPage: 1,
      perPage: 5
    };
  },
  async beforeMount() {
    console.log('prdoct? ')
    this.empresa = sessionStorage.getItem("empresa") || "modasm";

    await this.initForm();

    setTimeout(async () => {
      let res = await this.axios(
        `${this.API}mediciones?id_empresa=${this.empresa}`
      );
      if (res.data.length > 0) {
        this.producto.medicion = res.data.filter(
          v => v.ProductoMedicion == "UNIDAD"
        )[0];
      }
    }, 100);
  },
  props: ["showButton", "productoUpdate", "cbGuardar"],
  watch: {
    show(val) {
      if (val) {
        //this.cargarCatOferta()
      }
      this.nuevaSerie = false;
      let newVal = this.productoUpdate || {};
      if (newVal.IdProducto && !this.nuevo) {
        this.axios
          .get(
            this.API +
              `ver_productos?id_empresa=${this.empresa}&id=${newVal.IdProducto}`
          )
          .then(response => {
            newVal = response.data;
            this.initForm();

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

            // this.$refs.inputCategoria.setValue('')
            this.$refs.inputCategoria.setValue(newVal.ProductoCategoria);

            // this.$refs.inputMarca.setValue('')
            this.$refs.inputMarca.setValue(newVal.ProductoMarca);

            // this.$refs.inputMedicion.setValue('')
            this.$refs.inputMedicion.setValue(newVal.ProductoMedicion);

            // this.$refs.inputModelo.setValue('')
            this.$refs.inputModelo.setValue(newVal.ProductoModelo);
            this.$refs.inputPresentacion.setValue(newVal.ProductoPresentacion);

            this.$refs.inputProductoDesc.$el.value = newVal.ProductoDesc;
            //this.$refs.inputProductoDesc2.$el.value = newVal.ProductoDesc2
            this.$refs.inputProductoDesc3.$el.value = newVal.ProductoDesc3;

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
            this.producto.Producto = newVal.Producto;
            this.producto.IdProducto = newVal.IdProducto;

            this.producto.EsPadre = newVal.EsPadre;
            Vue.nextTick(() => {
              if (this.$refs.productoDet) {
                this.$refs.productoDet.show = parseInt(newVal.EsPadre);
                setTimeout(() => {
                  this.$refs.productoDet.getProductosDet();
                }, 100);
              }
            });
            // this.producto.Genero = newVal.Genero
            // this.producto.Botapie = newVal.Botapie
            // this.producto.Color = newVal.Color
            this.producto.PorcentajeUtilidad = newVal.PorcentajeUtilidad;
            this.producto.PrecioPorMayor = newVal.PrecioPorMayor;
            this.producto.ControlaStockMinimo = newVal.ControlaStockMinimo;
            this.producto.StockMinimo = newVal.StockMinimo;
            this.producto.CodigoBarra = newVal.CodigoBarra;
            this.producto.ProductoModelo = newVal.ProductoModelo;
            this.producto.ProductoDesc = newVal.ProductoDesc;
            this.producto.ProductoDesc2 = newVal.ProductoDesc2;
            this.producto.ProductoDesc3 = newVal.ProductoDesc3;
            this.producto.ProductoPresentacion = newVal.ProductoPresentacion;
            this.producto.PrecioCosto = newVal.PrecioCosto;
            this.producto.PrecioPorBloque = newVal.PrecioPorBloque;
            this.producto.precioBloque = newVal.precioBloque;
            this.producto.PrecioContado = newVal.PrecioContado;
            this.producto.precioConvenio = newVal.precioConvenio;
            this.producto.EsHabitacion = newVal.EsHabitacion;
            this.producto.Piso = newVal.Piso;
            this.producto.ControlaStock = newVal.ControlaStock;
            this.producto.Imagen = newVal.Imagen;
            this.producto.IdOfertaCategoria = newVal.IdOfertaCategoria;
            if (newVal.PreciosPorProducto) {
              this.producto.precios = JSON.parse(newVal.PreciosPorProducto);
            }
          })
          .catch(error => {
            console.log(error);
            this.initForm();
            setTimeout(async () => {
              let res = await this.axios(
                `${this.API}mediciones?id_empresa=${this.empresa}`
              );
              if (res.data.length > 0) {
                this.producto.medicion = res.data.filter(
                  v => v.ProductoMedicion == "UNIDAD"
                )[0];
              }
            }, 100);
            this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          });
        if (val) {
          this.obtenerSerie(newVal.IdProducto);
        }
      } else {
        this.title = "Nuevo Producto";
        this.clearForm();

        //this.$refs.inputCodigoBarra.$el.disabled = false
      }
    }
  },
  methods: {
    eliminarRegistroSerie(item, index) {
      this.productoSerie[index].Estado = 0;
    },
    obtenerSerie(id) {
      this.axios
        .get(this.API + "productos/serie", {
          params: { IdProducto: id, Todos: true }
        })
        .then(res => {
          if (res) {
            this.productoSerie = res.data;
          }
        });
    },
    activarInactivarAgregarNuevaSerie() {
      this.Serie = "";
      this.nuevaSerie = !this.nuevaSerie;
    },
    anadirSerie() {
      if (this.Serie) {
        this.productoSerie.unshift({
          IdProductoSerie: 0,
          Serie: this.Serie,
          Estado: 1
        });
        this.nuevaSerie = false;
      }
    },
    /*cargarCatOferta(){
      this.categoriaItems = []
      this.axios.get(this.API + 'categorias/oferta')
      .then((response) => {
        if(response.data){
            response.data.map((cat)=>{
                this.categoriaItems.push({value:null,text:'=== SIN OFERTA ==='})
                this.categoriaItems.push({value:cat.IdProductoCategoria,text:cat.ProductoCategoria})
            })
        }else{
          this.categoriaItems.push({value:null,text:'=== SIN OFERTA ==='})
        }
      }).catch((error)=>{
        
      })
    },*/
    handleFileUpload() {
      this.file = this.$refs.file.files[0];
    },
    calcularPreciosPaquete(prodDetalles) {
      let sumatoriaCosto = 0;
      let sumatoriaContado = 0;
      prodDetalles.forEach(prod => {
        if (prod.Cantidad) {
          sumatoriaCosto += prod.PrecioCosto * prod.Cantidad;
          sumatoriaContado += prod.PrecioContado * prod.Cantidad;
        }
      });
      sumatoriaCosto = this.$options.filters.redondear(sumatoriaCosto);
      sumatoriaContado = this.$options.filters.redondear(sumatoriaContado);

      Vue.set(this.producto, "PrecioCosto", sumatoriaCosto);
      Vue.set(this.producto, "PrecioContado", sumatoriaContado);
      this.actualizarPorcentajeUtilidad();
    },
    eliminarProducto(producto) {
      // NO eliminar porque no distinque movimientos ni ventas
      let r = confirm("¿Esta seguro de Anular este producto?");
      if (r) {
        this.axios
          .post(this.API + `productos/delete?id_empresa=${this.empresa}"`, {
            id: producto.IdProducto
          })
          .then(res => {
            if (res.data.updated) {
              this.$toasted.success(
                "El producto con código " +
                  res.data.IdProducto +
                  " ha sido anulado"
              );
              if (this.cbGuardar) {
                this.cbGuardar();
              }
              this.$refs.productoForm.hide();
            }
          });
      }
    },
    actualizarPrecioContado() {
      var precioCosto = Number(this.producto.PrecioCosto);
      var utilidad = Number(this.producto.PorcentajeUtilidad);
      // var nuevoPrecioContado = precioCosto + (precioCosto * (utilidad / 100))
      //var nuevoPrecioContado = precioCosto / (1 - (utilidad / 100))

      var nuevoPrecioContado = precioCosto * (utilidad / 100) + precioCosto;

      this.producto.precioContadoNormal = nuevoPrecioContado;
      //this.producto.PrecioContado = this.$options.filters.redondeoMayor(nuevoPrecioContado)
      this.producto.PrecioContado = this.$options.filters.redondear(
        nuevoPrecioContado,
        1
      );
    },
    actualizarPorcentajeUtilidad() {
      var precioContado = Number(this.producto.PrecioContado);
      var precioCosto = Number(this.producto.PrecioCosto);

      this.producto.PorcentajeUtilidad = this.$options.filters.redondear(
        ((precioContado - precioCosto) / precioCosto) * 100
      );
    },
    setCategoria(obj) {
      this.producto.categoria = obj;
      this.$refs.inputCategoria.setValue(obj.ProductoCategoria);
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
    /*setTalla (obj) {
      this.producto.talla = obj
      this.$refs.inputTalla.setValue(obj.ProductoTalla)
    },*/
    clearForm() {
      this.$refs.inputCategoria.setValue("");
      this.$refs.inputMarca.setValue("");
      this.$refs.inputMedicion.setValue("UNIDAD");
      // this.$refs.inputTalla.setValue('')
      this.$refs.inputModelo.setValue("");
      this.$refs.inputPresentacion.setValue("");
      // this.$refs.inputColor.setValue('')
      // this.$refs.inputBotapie.setValue('')
      this.$refs.inputProductoDesc.$el.value = "";
      this.initForm();
      setTimeout(async () => {
        let res = await this.axios(
          `${this.API}mediciones?id_empresa=${this.empresa}`
        );
        if (res.data.length > 0) {
          this.producto.medicion = res.data.filter(
            v => v.ProductoMedicion == "UNIDAD"
          )[0];
        }
      }, 100);
    },
    initForm() {
      this.producto = {
        EsPadre: 0,
        Anulado: 0,
        EsHabitacion: 0,
        Piso: 1,
        IdProductoMarca: null,
        IdProductoMedicion: null,
        IdProductoCategoria: null,
        ProductoModelo: "",
        // IdProductoModelo: null,
        // IdProductoBotapie: null,
        // IdProductoTalla: '',
        Producto: "",
        ProductoDesc: "",
        ProductoDesc2: "",
        ProductoDesc3: "",
        _ProductoDesc: "",
        _ProductoDesc2: "",
        _ProductoDesc3: "",
        CodigoBarra: "",
        // Color: '',
        PorcentajeUtilidad: 0,
        ControlaStock: 1,
        ControlaStockMinimo: 0,
        StockMinimo: 0,
        PrecioCosto: 0,
        PrecioContado: 0,
        PrecioPorMayor: 0,
        precioConvenio: 0,
        // Botapie: '',
        // Genero: '',
        categoria: {},
        marca: {},
        IdOfertaCategoria: null,
        // modelo: {},
        //talla: {},
        medicion: {
          IdProductoMedicion: 6,
          ProductoMedicion: "UNIDAD"
        },
        Imagen: null,
        precios: [
          { nombre: "PRECIO 1", cantidad: 0, precio: 0 },
          { nombre: "PRECIO 2", cantidad: 0, precio: 0 },
          { nombre: "PRECIO 3", cantidad: 0, precio: 0 },
          { nombre: "PRECIO 4", cantidad: 0, precio: 0 },
          { nombre: "OFERTA", cantidad: 0, precio: 0 }
        ]
      };
    },
    keyDownInput(e) {
      if (e.target.tagName != "TEXTAREA") {
        e.preventDefault();
      }
    },
    guardarProducto(e) {
      e.preventDefault();

      // Agregar producto DET
      if (this.showProductoDet) {
        this.producto.productosDet = this.$refs.productoDet.productos;
      }
      // console.log(JSON.stringify(this.producto))
      this.producto.Producto = ("" + this.producto.Producto).trim();
      this.producto.CodigoBarra = ("" + this.producto.CodigoBarra).trim();

      this.axios
        .post(this.API + `productos?id_empresa=${this.empresa}`, {
          ...this.producto,
          PreciosPorProducto: this.producto.precios,
          id_empresa: this.empresa,
          productoSerie: this.productoSerie
        })
        .then(response => {
          if (this.cbGuardar) {
            this.cbGuardar(
              response.data.insertId,
              this.producto.precios,
              this.producto
            );
          }
          if (response.data.error) {
            this.$toasted.show(
              response.data.error + " - " + response.data.codigoBarra
            );
            return;
          } else {
            this.$toasted.success("Producto guardado correctamente");
          }

          // this.initForm()
          // this.producto.talla = {}
          // this.$refs.inputTalla.setValue('')

          //INSERTANDO IMAGEN
          let formData = new FormData();
          formData.append("imagen", this.file);
          formData.append("empresa", this.empresa);

          this.axios({
            method: "post",
            url: this.API + "producto/imagen/" + response.data.insertId,
            data: formData,
            headers: { "Content-Type": "multipart/form-data" }
          }).then(res => {
            // console.log(res);
          });

          this.$refs.productoForm.hide();
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    focusAutocomplete(ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    crearCategoria() {
      if (this.$refs.inputCategoria.type) {
        let categoria = {};
        categoria.ProductoCategoria = this.$refs.inputCategoria.type.toUpperCase();

        this.axios
          .post(this.API + `categorias?id_empresa=${this.empresa}`, categoria)
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
          .post(this.API + `marcas?id_empresa=${this.empresa}`, marca)
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
          .post(this.API + `tallas&id_empresa=${this.empresa}`, talla)
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
    checkIsHijo(val) {
      if (val && this.producto.IdProducto) {
        this.axios
          .get(this.API + "productos/eshijo", {
            params: {
              idProducto: this.producto.IdProducto,
              id_empresa: this.empresa
            }
          })
          .then(res => {
            let esHijo = res.data.esHijo;
            if (esHijo) {
              this.$toasted.show(
                "Este producto no puede contener productos ya que es un producto hijo"
              );
              this.producto.EsPadre = false;
            } else {
              this.$refs.productoDet.show = parseInt(this.producto.EsPadre);
            }
          });
      } else {
        if (this.$refs.productoDet)
          this.$refs.productoDet.show = parseInt(this.producto.EsPadre);
      }
    }
  },
  computed: {},
  components: {
    CategoriaForm,
    MarcaForm,
    ProductoFormDet
  }
};
</script>
<style>
.oferta .card-header {
  background: #dc3545;
  font-weight: bold;
}
</style>
