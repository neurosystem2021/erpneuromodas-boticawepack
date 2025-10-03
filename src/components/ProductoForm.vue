<template>
  <div>
    <b-btn v-if="showButton" @click="()=>nuevo=true" v-b-modal.modalProducto variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarProducto" @keydown.enter="keyDownInput" >
    <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalProducto" :title="title" ref="productoForm" v-model="show">
      <b-row>
        <b-col md="9" class="px-5">
          <b-row>
            <b-col md="6">
              <b-form-group>
                <label>Tipo:</label>
                <b-input-group>
                    <autocomplete
                      required="required"
                      :url="API + 'producto/tipos'"
                      ref="inputTipos"
                      anchor="ProductoTipo"
                      label="writer"
                      :placeholder.sync="producto.ProductoTipo.ProductoTipo"
                      :on-select="setTipo"
                      :onFocus="focusAutocomplete.bind(null, 'inputTipos')"
                      :classes="{input: 'form-control upper'}">
                    </autocomplete>
                    <b-input-group-append>
                      <b-btn variant="primary" @click="crearTipo"><b>Guardar</b></b-btn>                
                      <!--<marca-form :showButton="true" :cbGuardar="setMarca"></marca-form>-->
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


              <b-form-group>
                <label>Código Interno:</label>
                <b-form-input 
                  ref="inputCodigoBarra"
                  v-model="producto.CodigoInterno"
                  @input="(val) => producto.CodigoInterno = val && val.toUpperCase()"
                  type="text"
                  class="form-control upper"
                  ></b-form-input>
              </b-form-group>

              <!-- <b-form-group>
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
              </b-form-group> -->
              
              <b-form-group>
                <label>Se compra en:</label>
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

                <!-- --------------------------------- -->

              <!-- <b-form-group>
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
                </b-input-group>
              </b-form-group> -->
<!--               
              <b-form-group v-show="false">
                <label>Anaquel:</label>
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
              </b-form-group> -->

              

              <b-form-group>
                <label>Observación:</label>
                <b-form-textarea 
                  ref="inputProductoDesc"
                  class="txtComentario"
                  v-model="producto._ProductoDesc"
                  @input="(val) => producto.ProductoDesc = val"
                  />
              </b-form-group>

              <!-- <b-form-group v-if="false">
                <label>3ra Descripción:</label>
                <b-form-textarea 
                  ref="inputProductoDesc3"
                  rows="6"
                  v-model="producto._ProductoDesc3"
                  @input="(val) => producto.ProductoDesc3 = val"
                  />
              </b-form-group> -->

            </b-col>
            <b-col md="6">
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
                <label>Código Sunat:</label>
                <b-form-input 
                  ref="inputCodigoSunat"
                  v-model="producto.CodigoSunat"
                  @input="(val) => producto.CodigoSunat = val && val.toUpperCase()"
                  type="text"
                  class="form-control upper"
                  ></b-form-input>
              </b-form-group> -->

              <b-form-group>
                <label>Código Fabricante:</label>
                <b-form-input 
                  ref="inputCodigoFrabricante"
                  v-model="producto.CodigoFrabricante"
                  @input="(val) => producto.CodigoFrabricante = val && val.toUpperCase()"
                  type="text"
                  class="form-control upper"
                  ></b-form-input>
              </b-form-group>


                
              <b-form-group>
                <label>Nombre:</label>
                <b-form-input 
                  v-model="producto.Producto"
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
                    ref="inputAnaquel"
                    :url="`${API}Anaquel`"
                    anchor="Anaquel"
                    label="writer"
                    :placeholder="producto.Anaquel"
                    :on-select="setAnaquel"
                    :onFocus="focusAutocomplete.bind(null, 'inputAnaquel')"
                    :classes="{input: 'form-control upper'}">
                </autocomplete>
                  <b-input-group-append>
                  <b-btn variant="primary" @click="crearAnaquel"><b>Guardar</b></b-btn>                
                  <!--<marca-form :showButton="true" :cbGuardar="setMarca"></marca-form>-->
                  </b-input-group-append>
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
               <!-- --------------------------------- -->
              
              <!-- <b-form-group>
                <b-form-checkbox
                      v-model="producto.VentaEstrategica"
                      :value="1"
                      :unchecked-value="0">
                  Producto con Venta Estrategica
                </b-form-checkbox>
              </b-form-group> -->
             
              <b-form-group>
                <b-form-checkbox
                      v-model="producto.ControlaStock"
                      :value="1"
                      :unchecked-value="0">
                  Controla Stock
                </b-form-checkbox>
              </b-form-group>
              <b-form-group v-if="this.activaSubProducto">
                <label>Activar SubProductos:</label>
                <b-form-checkbox
                        v-model="producto.EsPadre"
                        :disabled="!this.hideQuitarX"
                        :value="1"
                        :unchecked-value="0"
                        @input="checkIsHijo">
                  Seleccione esta opción si el producto contiene mas productos
                </b-form-checkbox>
              </b-form-group>
              
              <!-- <b-form-group>
                <b-form-checkbox
                      v-model="producto.TienePresentacion"
                      :value="1"
                      :unchecked-value="0">
                  Tiene Presentacion
                </b-form-checkbox>
              </b-form-group> -->
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

            <!-- <b-col cols="12">
              Equivalencias
            </b-col> -->
            <b-col cols="12">
              <hr>
            </b-col>
            <b-col cols="7">
              <ProductoFormReemplazo :ProductoReempleazo="ProductoReempleazo" :IdProducto="producto.IdProducto "/>
            </b-col>
             <!-- <b-col cols="5">
              <ProductoAnaquel :ProductoAnaquel="producto.anaqueles"/>
             </b-col> -->

          </b-row>
        </b-col>
        <b-col md="3" class="px-5 borderLef">
              <b-form-group>
                  <label>Imagen:</label>
                  <b-img :src="'/resources/images/productos/' + producto.Imagen" fluid alt=""></b-img>
                  <input type="file" id="file" ref="file" @change="handleFileUpload()"/>
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
                  <label>Precio X Mayor:</label>
                  <input 
                    v-model="producto.PrecioPorMayor"
                    type="number"
                    min="0"
                    step="0.0001"
                    class="form-control"
                    />
              </b-form-group>

              <b-form-group>
                  <label>Precio de Costo:</label>
                  <input
                    v-model="producto.PrecioCosto"
                    :disabled="(idPerfil == 1 || idPerfil == 23 || idPerfil == 31) ? false : true"
                    step="0.0001"
                    type="number"
                     @input="actualizarPrecioDolar(1)"
                    class="form-control"
                    />
              </b-form-group>
              <b-form-group>
                  <label>Tipo Cambio:</label>
                  <input
                    v-model="producto.TipoCambio"
                    step="0.0001"
                    type="number"
                    @input="actualizarPrecioDolar(2)"
                    class="form-control"
                    />
              </b-form-group>
              <b-form-group>
                  <label>Precio de Costo Dolar:</label>
                  <input
                    v-model="producto.PrecioCostoDolar"
                    step="0.0001"
                    type="number"
                    @input="actualizarPrecioDolar(3)"

                    class="form-control"
                    />
              </b-form-group>

              <b-form-group>
                <label>Precio de Venta:</label>
                <input v-model="producto.PrecioContado"
                type="number"
                step="0.00001"
                @input="actualizarPrecioDolar(4)"
                class="form-control"
                />
                <b-form-text v-if="producto.precioContadoNormal">
                  Original - {{ producto.precioContadoNormal | redondear }}
                </b-form-text>
              </b-form-group>
              
              <b-form-group>
                <label>Precio de Venta Dolar:</label>
                <input v-model="producto.PrecioContadoDolar"
                type="number"
                step="0.00001"
                @input="actualizarPrecioDolar(5)"
                class="form-control"
                />
                <b-form-text v-if="producto.precioContadoNormal">
                  Original - {{ producto.precioContadoNormal | redondear }}
                </b-form-text>
              </b-form-group>

              <b-form-group>
                <label>Precio Distribuidor:</label>
                <input v-model="producto.precioConvenio"
                  type="number"
                  step="0.0001"
                  class="form-control"
                  />
              </b-form-group>

            <b-form-group>
              <label>Precio Especial:</label>
              <input v-model="producto.PrecioEspecial"
                type="number"
                class="form-control"
                step="0.001"
                />
            </b-form-group>
          </b-col>
      </b-row>
      
      <!-- <label>Anaquel - Almacen</label>
      <b-row>
          <b-col md="6" v-for="(an, index) in producto.anaqueles" :key="index" class="mb-4" v-show="(index+1)<almacenes.length">
            <b-form-select v-model="an.id" :options="almacenes" size="md"/>
            <autocomplete
                :url="API + 'productos/presentacionesdet'"
                :ref="'inputPresentaciondet'+index"
                anchor="Anaquel"
                label="writer"
                :placeholder.sync="an.nombre"
                :on-select="val => an.nombre = val.Anaquel && val.Anaquel.toUpperCase()"
                :onInput="val => an.nombre = val && val.toUpperCase()"
                :classes="{input: 'form-control upper'}">
            </autocomplete>
            </b-col>
      </b-row> -->
      <!-- <hr/> -->
    
      <b-form-group v-if="producto.TienePresentacion">
      <hr>
      <b-row>
        <b-col md="3" v-for="i in 4" :key="i.id">
          <b-card :border-variant="producto.precios[i-1].nombre=='OFERTA'?'danger':'light'" :header="'Precio '+i" class="text-center"  v-bind:class="[producto.precios[i-1].nombre=='OFERTA' ? 'oferta' : '']">
            <b-form-group>
              <b-form-input
                v-model="producto.precios[i-1].nombre"
                :disabled="(idPerfil == 1) ? false : true"
                type="text"
                style="text-transform:uppercase"
                @input="(val) => producto.precios[i-1].nombre = val && val.toUpperCase()"
                ></b-form-input>
            </b-form-group>

            <b-form-group>
              <label>Cantidad:</label>
              <input
                v-model="producto.precios[i-1].cantidad"
                :disabled="(idPerfil == 1) ? false : true"
                type="number"
                class="form-control"
                step="0.01"
                />
            </b-form-group>
            
            <b-form-group>
              <label>Precio:</label>
              <input 
                v-model="producto.precios[i-1].precio"
                :disabled="(idPerfil == 1) ? false : true"
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
    </b-form-group>

      <hr />
      <b-row>
        <b-col>
          <producto-form-det 
            @onUpdateProductos="calcularPreciosPaquete"
            :id-producto="producto.IdProducto" 
            :hideQuitar="hideQuitarX"
            v-if="showProductoDet && show" 
            ref="productoDet"
            ></producto-form-det>
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
import config from "@/store/config.js";
import CategoriaForm from "@/components/CategoriaForm";
import MarcaForm from "@/components/MarcaForm";
import ProductoFormDet from "@/components/ProductoFormDet";
import ProductoFormReemplazo from "@/components/ProductoFormReemplazo";
import ProductoAnaquel from "@/components/ProductoAnaquel";
// import ModeloForm from '@/components/ModeloForm'

export default {
  data() {
    return {
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
      activaSubProducto: true,
      hideQuitarX: true,
      almacenes: [{ value: null, text: "-- SELECCIONE ALMACEN --" }],
      ProductoReempleazo: []
    };
  },
  beforeMount() {
    this.initForm();
  },
  props: ["showButton", "productoUpdate", "cbGuardar", "hideQuitar"],
  watch: {
    async show(val) {
      if (val) {
        this.activaSubProducto = true;
        this.hideQuitarX = true;
        if (this.productoUpdate.IdProducto) {
          if (this.productoUpdate.EsPadre == 0) {
            this.activaSubProducto = false;
          } else {
            this.hideQuitarX = false;
          }
        }
        //this.cargarCatOferta()
      }

      let newVal = this.productoUpdate || {};
      if (newVal.IdProducto && !this.nuevo) {
        await this.axios
          .get(this.API + "productos/id/" + newVal.IdProducto)
          .then(response => {
            newVal = response.data.producto;
            this.ProductoReempleazo = response.data.ProductoReempleazo;
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

            this.producto.ProductoTipo = {
              IdProductoTipo: newVal.IdProductoTipo,
              ProductoTipo: newVal.ProductoTipo
            };

            // this.$refs.inputCategoria.setValue('')
            this.$refs.inputCategoria.setValue(newVal.ProductoCategoria);

            // this.$refs.inputMarca.setValue('')
            this.$refs.inputMarca.setValue(newVal.ProductoMarca);
            this.$refs.inputTipos.setValue(newVal.ProductoTipo);

            // this.$refs.inputMedicion.setValue('')
            this.$refs.inputMedicion.setValue(newVal.ProductoMedicion);

            // this.$refs.inputModelo.setValue('')
            // this.$refs.inputModelo.setValue(newVal.ProductoModelo);
            // this.$refs.inputPresentacion.setValue(newVal.ProductoPresentacion);

            this.$refs.inputProductoDesc.$el.value = newVal.ProductoDesc;
            //this.$refs.inputAnaquel.$el.value = newVal.Anaquel;
            this.$refs.inputAnaquel.setValue(newVal.Anaquel || "");
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
            this.producto.Anaquel = newVal.Anaquel;
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
            this.producto.PrecioCostoDolar = newVal.PrecioCostoDolar;
            this.producto.TipoCambio = newVal.TipoCambio;
            this.producto.PrecioContado = newVal.PrecioContado;
            this.producto.PrecioContadoDolar = newVal.PrecioContadoDolar;
            this.producto.precioConvenio = newVal.precioConvenio;
            this.producto.PrecioEspecial = newVal.PrecioEspecial;
            this.producto.EsHabitacion = newVal.EsHabitacion;
            this.producto.Piso = newVal.Piso;
            this.producto.ControlaStock = newVal.ControlaStock;
            this.producto.Imagen = newVal.Imagen;
            this.producto.IdOfertaCategoria = newVal.IdOfertaCategoria;
            this.producto.VentaEstrategica = newVal.VentaEstrategica;
            this.producto.TienePresentacion = newVal.TienePresentacion;
            this.producto.CodigoInterno = newVal.CodigoInterno;
            this.producto.CodigoFrabricante = newVal.CodigoFrabricante;
            this.producto.CodigoSunat = newVal.CodigoSunat;
            if (newVal.PreciosPorProducto) {
              this.producto.precios = JSON.parse(newVal.PreciosPorProducto);
            }
            if (
              newVal.Anaqueles &&
              newVal.Anaqueles != null &&
              newVal.Anaqueles != "null"
            ) {
              this.producto.anaqueles = JSON.parse(newVal.Anaqueles);
            }
            this.$refs.file.value = null;
            this.file = "";
          })
          .catch(error => {
            this.$refs.file.value = null;
            this.file = "";
            console.log(error);
            this.initForm();
            this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          });
      } else {
        this.title = "Nuevo Producto";
        this.clearForm();
        this.$refs.file.value = null;
        this.file = "";
        //this.$refs.inputCodigoBarra.$el.disabled = false
      }
      this.cargarAlmacenes();
    }
  },
  methods: {
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
    actualizarPrecioDolar(tipo = 1) {
      switch (tipo) {
        case 1:
          this.producto.PrecioCostoDolar = parseFloat(
            this.producto.PrecioCosto * this.producto.TipoCambio
          ).toFixed(2);
          break;
        case 2:
          this.producto.PrecioCostoDolar = parseFloat(
            this.producto.PrecioCosto * this.producto.TipoCambio
          ).toFixed(2);
          this.producto.PrecioContadoDolar = parseFloat(
            this.producto.PrecioContado * this.producto.TipoCambio
          ).toFixed(2);
          this.$forceUpdate();
          break;
        case 3:
          this.producto.PrecioCosto = parseFloat(
            this.producto.PrecioCostoDolar / this.producto.TipoCambio
          ).toFixed(2);
          break;
        case 4:
          this.producto.PrecioContadoDolar = parseFloat(
            this.producto.PrecioContado * this.producto.TipoCambio
          ).toFixed(2);
          break;
        case 5:
          this.producto.PrecioContado = parseFloat(
            this.producto.PrecioContadoDolar / this.producto.TipoCambio
          ).toFixed(2);
          break;
      }
      this.actualizarPorcentajeUtilidad();
    },
    cargarAlmacenes() {
      this.axios
        .get(this.API + "almacenes")
        .then(response => {
          this.almacenes = [{ value: null, text: "-- SELECCIONE ALMACEN --" }];
          response.data.map(c => {
            this.almacenes.push({ value: c.IdAlmacen, text: c.Almacen });
          });
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    handleFileUpload() {
      this.file = this.$refs.file.files[0];
    },
    calcularPreciosPaquete(prodDetalles) {
      let sumatoriaCosto = 0;
      let sumatoriaContado = 0;
      prodDetalles.forEach(prod => {
        if (prod.Cantidad) {
          sumatoriaCosto += (prod.PrecioDet || 0) * prod.Cantidad;
          sumatoriaContado += (prod.PrecioDet || 0) * prod.Cantidad;
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
          .post(this.API + "productos/delete", {
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
      var nuevoPrecioContado =
        precioCosto / ((100 - parseFloat(utilidad)) / 100);

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
    setTipo(obj) {
      console.log(obj);
      this.producto.ProductoTipo = obj;
      this.$refs.inputTipos.setValue(obj.ProductoTipo);
    },
    setMedicion(obj) {
      this.producto.medicion = obj;
    },
    setMarca(obj) {
      this.producto.marca = obj;
      this.$refs.inputMarca.setValue(obj.ProductoMarca);
    },
    setAnaquel(obj) {
      const valor = obj && obj.Anaquel ? obj.Anaquel : obj || "";
      this.producto.Anaquel = valor.trim().toUpperCase();
      this.$refs.inputAnaquel.setValue(valor);
      this.$forceUpdate();
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
      this.$nextTick(() => {
        this.$refs.inputCategoria.setValue("");
        this.$refs.inputMarca.setValue("");
        this.$refs.inputMedicion.setValue("UNIDAD");
        this.$refs.inputTipos.setValue("");
        this.$refs.inputProductoDesc.setValue("");
        this.$refs.inputAnaquel.setValue("");
        this.producto.Anaquel = "";
        // this.$refs.inputTalla.setValue('')
        // this.$refs.inputModelo.setValue("");
        // this.$refs.inputPresentacion.setValue("");
        // this.$refs.inputColor.setValue('')
        // this.$refs.inputBotapie.setValue('')

        this.initForm();
      });
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
        TienePresentacion: 0,
        PrecioCosto: 0,
        PrecioContado: 0,
        PrecioPorMayor: 0,
        precioConvenio: 0,
        PrecioEspecial: 0,
        CodigoFrabricante: "",
        CodigoSunat: "",
        CodigoInterno: "",
        // Botapie: '',
        // Genero: '',
        categoria: {},
        marca: {},
        ProductoTipo: {},
        IdOfertaCategoria: null,
        VentaEstrategica: 0,
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
          { nombre: "OFERTA", cantidad: 0, precio: 0 }
        ],
        anaqueles: [
          { id: null, nombre: "" },
          { id: null, nombre: "" },
          { id: null, nombre: "" },
          { id: null, nombre: "" },
          { id: null, nombre: "" },
          { id: null, nombre: "" }
        ],
        Anaquel: ""
      };
    },
    keyDownInput(e) {
      if (e.target.tagName != "TEXTAREA") {
        e.preventDefault();
      }
    },
    guardarProducto(e) {
      e.preventDefault();

      if (
        this.$refs.productoDet.productos.length <= 0 &&
        this.producto.EsPadre == 1
      ) {
        this.$toasted.show("Tiene que ingresar Subproductos");
        return;
      }

      this.producto.anaqueles.map(d => {
        if (d.id == null) {
          d.nombre = "";
        }
        d.nombre = ("" + d.nombre).toUpperCase().trim();
      });

      for (let index = 0; index < this.almacenes.length - 1; index++) {
        if (index >= this.almacenes.length - 1) {
          this.producto.anaqueles[index].id = null;
        }
      }

      let igualAlm = 0;
      this.producto.anaqueles.map(r => {
        let fil = this.producto.anaqueles.filter(f => f.id == r.id);
        if (fil.length >= 2 && r.id != null) {
          igualAlm++;
        }
      });

      if (igualAlm > 1) {
        this.$toasted.show(
          "No puede seleccionar 2 almacenes iguales en Anaquel - Almacen"
        );
        return;
      }

      // Agregar producto DET
      if (this.showProductoDet) {
        this.producto.productosDet = this.$refs.productoDet.productos;
      }

      // Si no hay un Anaquel seleccionado, tomar el texto manual del input
      if (!this.producto.Anaquel && this.$refs.inputAnaquel) {
        const valor = this.$refs.inputAnaquel.getValue
          ? this.$refs.inputAnaquel.getValue()
          : this.$refs.inputAnaquel.type || "";

        this.producto.Anaquel = valor.trim().toUpperCase();
      }
      console.log("Valor final Anaquel =>", this.producto.Anaquel);
      // console.log(JSON.stringify(this.producto))
      this.producto.CodigoBarra = ("" + this.producto.CodigoBarra).trim();
      this.axios
        .post(this.API + "productos", {
          ...this.producto,
          PreciosPorProducto: this.producto.precios,
          // Anaqueles: this.producto.anaqueles,
          Anaquel: this.producto.Anaquel,
          ProductoReempleazo: this.ProductoReempleazo
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
    focusAutocompletedet(ref) {
      // this.$refs[ref].type = ""

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
    crearAnaquel() {
      if (this.$refs.inputAnaquel.type) {
        let anaquel = {};
        anaquel.Anaquel = this.$refs.inputAnaquel.type.toUpperCase();

        this.axios
          .post(this.API + "Anaquel", anaquel)
          .then(response => {
            this.$toasted.show("Anaquel guardada correctamente");
            this.setAnaquel({
              IdAnaquel: response.data.insertId,
              Anaquel: response.data.Anaquel
            });
          })
          .catch(error => {
            console.log(error);
            this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          });
      }
    },
    crearTipo() {
      if (this.$refs.inputTipos.type) {
        let Tipo = {};
        Tipo.Tipo = this.$refs.inputTipos.type.toUpperCase();
        this.axios
          .post(this.API + "tipos", Tipo)
          .then(response => {
            this.$toasted.show("Tipo guardado correctamente");
            console.log(response.data);
            this.setTipo({
              IdProductoTipo: response.data.insertId,
              ProductoTipo: response.data.ProductoTipo
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
    checkIsHijo(val) {
      if (val && this.producto.IdProducto) {
        this.axios
          .get(this.API + "productos/eshijo", {
            params: {
              idProducto: this.producto.IdProducto
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
    ProductoFormDet,
    ProductoFormReemplazo,
    ProductoAnaquel
  }
};
</script>
<style>
.txtComentario {
  field-sizing: content;
}
.oferta .card-header {
  background: #dc3545;
  font-weight: bold;
}
.borderLef {
  border-left: 1px solid #e0e0e0 !important;
}

#modalProducto .modal-dialog {
  min-width: 80% !important;
}

/* .modal-dialog {
  min-width: 80% !important;
} */
</style>
