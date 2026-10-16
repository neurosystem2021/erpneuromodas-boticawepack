<template>
<div class="pos-product-form-host">
  <b-btn v-if="showButton" @click="()=>nuevo=true" v-b-modal.modalProducto variant="primary">Nuevo</b-btn>
  <!-- Modal Component -->
  <b-form class="pos-product-editor-form" @submit="guardarProducto" @keydown.enter="keyDownInput" >
  <b-modal
    :no-close-on-esc="true"
    no-close-on-backdrop
    id="modalProducto"
    :title="title"
    size="lg"
    centered
    modal-class="pos-product-editor-modal nst-dialog"
    body-class="pos-product-editor-modal__body nst-dialog-body"
    footer-class="pos-product-editor-modal__footer nst-dialog-footer"
    ref="productoForm"
    v-model="show">
    <b-row class="pos-product-editor-grid">
      <b-col md="6" class="pos-product-editor-column">
        <b-form-group class="pos-product-barcode-field">
          <label>Código barra:</label>
          <div class="pos-editor-barcode-shell">
            <span class="pos-editor-barcode-icon" aria-hidden="true"><i class="fas fa-barcode"></i></span>
            <b-form-input
              ref="inputCodigoBarra"
              v-model="producto.CodigoBarra"
              @input="(val) => producto.CodigoBarra = val && val.toUpperCase()"
              type="text"
              autocomplete="off"
              class="form-control upper pos-editor-barcode-input"
              ></b-form-input>
            <span class="pos-editor-barcode-ready"><i class="fas fa-circle"></i> Lector listo</span>
          </div>
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

        <b-form-group>
          <label>Código Producto Sunat:</label>
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

        <producto-imagenes ref="productImages" :key="String(show)" :product-id="producto.IdProducto" :primary="producto.Imagen" />

        <b-form-group>
          <label>1ra Descripción:</label>
          <b-form-textarea 
            ref="inputProductoDesc"
            rows="6"
            v-model="producto.ProductoDesc"
            @input="(val) => producto.ProductoDesc = val"
            />
        </b-form-group>

        <b-form-group v-if="false">
          <label>3ra Descripción:</label>
          <b-form-textarea 
            ref="inputProductoDesc3"
            rows="5"
            v-model="producto._ProductoDesc3"
            @input="(val) => producto.ProductoDesc3 = val"
            />
        </b-form-group>

        <b-form-group>
          <b-form-checkbox
                   v-model="producto.EsMercaderia"
                   :value="1"
                   :unchecked-value="0"
                   >
                   Es Mercadería
          </b-form-checkbox>
        </b-form-group>

        <b-form-group>
          <b-form-checkbox
                   v-model="producto.EsServicio"
                   :value="1"
                   :unchecked-value="0"
                   >
                   Es Servicio
          </b-form-checkbox>
        </b-form-group>
        <b-form-group><b-form-checkbox v-model="producto.EsServicioManoObra" :value="1" :unchecked-value="0" @change="configureLaborService">Tratar como mano de obra</b-form-checkbox><small>Servicio que participa en mano de obra bruta y neta. Al activarlo se marca como servicio y se desactiva el control de stock.</small></b-form-group>
      </b-col>
      <b-col md="6" class="pos-product-editor-column">
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
            :disabled="(idPerfil == 1) ? false : true"
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
          <label>Activar SubProductos:</label>
          <b-form-checkbox
                   v-model="producto.EsPadre"
                   :value="1"
                   :unchecked-value="0"
                   @input="checkIsHijo">
            Seleccione esta opción si el producto contiene mas productos
          </b-form-checkbox>
        </b-form-group>
        <b-form-group>
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

        <b-form-group>
          <b-form-checkbox
                 v-model="producto.TienePresentacion"
                 :value="1"
                 :unchecked-value="0">
            Tiene Presentacion
          </b-form-checkbox>
        </b-form-group>

        <b-form-group v-if="producto.IdProducto" class="pos-product-app-panel">
          <label>Pedi2.pe:</label> <img src="static/logo.png" style="width: 32px" /><br>
          <b-form-checkbox
                   v-model="producto.MostrarApp"
                   :value="1"
                   :unchecked-value="0"
                   >
            Mostrar en Pedi2.pe
          </b-form-checkbox>
          <hr>
          <label>Categoria oferta en APP:</label>
          <b-form-select v-model="producto.IdOfertaCategoria" :options="categoriaItems" v-on:change="setOferta($event)" class="mb-3" size="md"/>
          <label v-if="producto.IdOfertaCategoria!=null">Orden a mostrar:</label>
          <b-form-select v-if="producto.IdOfertaCategoria!=null" v-model="producto.PosOferta" :options="itemsPosOferta" class="mb-3" size="md" required/>
          <div v-if="producto.IdOfertaCategoria!=null" class="pos-product-offer-schedule">
            <div class="pos-product-offer-date">
              <label for="offer-start-date"><i class="fas fa-calendar-plus" aria-hidden="true"></i> Inicia</label>
              <b-form-input id="offer-start-date" v-model="producto.FechaOfertaInicio" type="date"></b-form-input>
            </div>
            <div class="pos-product-offer-date">
              <label for="offer-end-date"><i class="fas fa-calendar-times" aria-hidden="true"></i> Finaliza</label>
              <b-form-input id="offer-end-date" v-model="producto.FechaOfertaFin" type="date" :min="producto.FechaOfertaInicio || null"></b-form-input>
            </div>
            <small><i class="fas fa-magic" aria-hidden="true"></i> Al vencer, la oferta se desactiva y las posiciones se ordenan automáticamente.</small>
          </div>
        </b-form-group>

         <b-form-group>
            <label>Precio APP: </label>
            <input type="number" v-model="producto.PrecioApp" class="form-control">
        </b-form-group>

        <b-form-group>
          <b-form-checkbox v-model="producto.OfertaWeb" :value="1" :unchecked-value="0">Oferta tienda virtual NEUROSYSTEM</b-form-checkbox>
          <small>Mostrar en ofertas debajo del flyer de la tienda.</small>
        </b-form-group>
        <b-form-group label="Precio web:">
          <input type="number" min="0" step="0.01" v-model.number="producto.PrecioWeb" class="form-control">
          <small>Si se deja vacío, se usa el precio de venta.</small>
        </b-form-group>
        <b-form-group label="Precio anterior (tachado en la web):">
          <input type="number" min="0" step="0.01" v-model.number="producto.PrecioAnterior" class="form-control">
          <small>Se muestra tachado cuando es mayor que el precio web.</small>
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

    <hr v-if="producto.IdProducto || producto.EsPadre || manterproducto" />
    <b-row class="pos-product-pricing-section" v-if="producto.IdProducto || producto.EsPadre || manterproducto">
      <b-col md="6">
        <b-form-group>
          <label>Precio de Costo:</label>
          <input
            v-model="producto.PrecioCosto"
             :disabled="(idPerfil != 1 || producto.EsPadre ==1) ? true : false"
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
            :disabled="(idPerfil != 1 || producto.EsPadre ==1) ? true : false"
            type="number"
            step="0.0001"
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
          <label>Precio Convenio:</label>
          <input v-model="producto.precioConvenio"
             :disabled="(idPerfil != 1 || producto.EsPadre ==1) ? true : false"
            type="number"
            step="0.0001"
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
            :disabled="producto.EsPadre ==1"
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

    <hr  v-if="producto.TienePresentacion==1">
    <b-row class="pos-product-presentation-section" v-if="producto.TienePresentacion==1">
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
              step="0.001"
              />
          </b-form-group>
        </b-card>
      </b-col>
    </b-row>

    <hr  v-if="showProductoDet && show" />  
    <b-row>
      <b-col>
        <producto-form-det 
          @onUpdateProductos="calcularPreciosPaquete"
          :id-producto="producto.IdProducto" 
          v-if="showProductoDet && show" 
          :padre="producto"
          ref="productoDet"
          ></producto-form-det>
      </b-col>
    </b-row>

     <b-row class="pos-product-series-section">
      <b-col cols="12">
        <b-button  size="sm" class="my-2" variant="success" v-if="!nuevaSerie" @click="activarInactivarAgregarNuevaSerie">Añadir Nueva Serie</b-button>
        
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
              v-if="!nuevaSerie"

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

      

      </b-col> 
    </b-row>



    <div slot="modal-footer" class="pos-product-editor-footer-content">
      <div class="pos-product-barcode-print" v-if="producto.IdProducto">
        <b-input-group class="pos-product-barcode-print-control">
          <b-form-input
            v-model="cantidadCodigoBarra"
            type="number"
            min="1"
            aria-label="Cantidad de etiquetas de código de barras"
          ></b-form-input>
          <b-input-group-append>
            <b-btn class="pos-product-barcode-print-button" variant="info" target="_blank" :href="'/views/codigoBarra.php?idProducto=' + producto.IdProducto + '&cantidad=' + cantidadCodigoBarra "><i class="fas fa-barcode" aria-hidden="true"></i> Código de barra</b-btn>
          </b-input-group-append>
        </b-input-group>
      </div>
     <!-- <b-btn  variant="danger" @click="eliminarProducto(producto)" v-if="producto.IdProducto">
        Anular
      </b-btn> -->
      <div class="pos-product-editor-actions">
        <b-btn class="pos-product-clear-button" variant="warning" @click="clearForm" v-if="!producto.IdProducto">
          <i class="fas fa-eraser" aria-hidden="true"></i> Limpiar
        </b-btn>
        <b-btn class="pos-product-save-button" variant="primary" type="submit" :disabled="savingProduct">
           <i class="fas fa-save" aria-hidden="true"></i> Guardar
         </b-btn>
         <b-btn class="pos-product-close-button" variant="secondary" @click="show=false">
           <i class="fas fa-times" aria-hidden="true"></i> Cerrar
         </b-btn>
      </div>
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
import ProductoImagenes from '@/components/ProductoImagenes';
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
      preproducto: {},
      preproducto: {},
      productosDet: [],
      file: "",
      savingProduct: false,
      idPerfil: sessionStorage.getItem("IdPerfil"),
      categoriaItems: [],
      itemsPosOferta: [],
      precioLiquidacion: 0,
      manterproducto: {
        default: false,
        type: Boolean
      },
      Serie: "",
      nuevaSerie: false,
      headerSerie: [
        { label: "Serie", key: "Serie" },
        { label: "Estado", key: "Estado" },
        { label: "Doc Venta", key: "CorrelativoVenta" },
        { label: "", key: "action" }
      ],
      productoSerie: []
    };
  },
  beforeMount() {
    this.initForm();
  },
  props: ["showButton", "productoUpdate", "cbGuardar"],
  watch: {
    async show(val) {
      this.preproducto = { ...this.productoUpdate };
      if (val) {
        this.cargarCatOferta();
        this.itemsPosOferta = [];
        this.itemsPosOferta.push({
          value: 10000,
          text: "== SELECCIONE POSICION OFERTA =="
        });
        for (let index = 1; index <= 100; index++) {
          this.itemsPosOferta.push({ value: index, text: index });
        }

        let newVal = this.productoUpdate || {};
        if (newVal.IdProducto && !this.nuevo) {
          await this.axios;
          await this.axios
            .get(this.API + "productos/id/" + newVal.IdProducto)
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
              this.$refs.inputPresentacion.setValue(
                newVal.ProductoPresentacion
              );

              this.$refs.inputProductoDesc.$el.value = newVal.ProductoDesc;
              //this.$refs.inputProductoDesc2.$el.value = newVal.ProductoDesc2
              //this.$refs.inputProductoDesc3.$el.value = newVal.ProductoDesc3

              // this.$refs.inputTalla.setValue(newVal.ProductoTalla)
              // this.$refs.inputProducto.$el.disabled = true
              this.$refs.inputCodigoBarra.$el.disabled = true;

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
              this.producto.PrecioContado = newVal.PrecioContado;
              this.producto.precioConvenio = newVal.precioConvenio;
              this.producto.EsHabitacion = newVal.EsHabitacion;
              this.producto.OpcContable = newVal.OpcContable;
              this.producto.Piso = newVal.Piso;
              this.producto.ControlaStock = newVal.ControlaStock;
              this.producto.EsServicioManoObra=Number(newVal.EsServicioManoObra)||0;
              this.producto.Imagen = newVal.Imagen;
              this.producto.IdOfertaCategoria = newVal.IdOfertaCategoria;
              this.producto.MostrarApp = newVal.MostrarApp;
              this.producto.PosOferta = newVal.PosOferta;
              this.producto.PrecioApp = newVal.PrecioApp;
              this.producto.OfertaWeb = Number(newVal.OfertaWeb || 0);
              this.producto.PrecioWeb = newVal.PrecioWeb;
              this.producto.PrecioAnterior = newVal.PrecioAnterior;
              this.producto.FechaOfertaInicio = newVal.FechaOfertaInicio || null;
              this.producto.FechaOfertaFin = newVal.FechaOfertaFin || null;
              this.producto.TienePresentacion = newVal.TienePresentacion;
              this.producto.PorcentajeLiquidacion =
                newVal.PorcentajeLiquidacion;

              switch (this.producto.OpcContable) {
                case 1:
                  this.producto.EsMercaderia = 1;
                  break;
                case 2:
                  this.producto.EsServicio = 1;
                  break;

                default:
                  break;
              }

              if (newVal.PreciosPorProducto) {
                this.producto.precios = JSON.parse(newVal.PreciosPorProducto);
              }
              this.$refs.file.value = null;
              this.file = "";
              this.actualizarLiquidacion();
            })
            .catch(error => {
              this.$refs.file.value = null;
              this.file = "";
              console.log(error);
              this.initForm();
              this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
            });
          if (val) {
            this.obtenerSerie(newVal.IdProducto);
          }
        } else {
          this.title = "Nuevo Producto";
          this.clearForm();
          this.$refs.file.value = null;
          this.file = "";
          this.$refs.inputCodigoBarra.$el.disabled = false;
        }
        this.$nextTick(() => {
          if (this.manterproducto) {
            this.producto.Producto = this.preproducto.Producto;
            this.producto.PrecioCosto = this.preproducto.PrecioCosto;

            this.$forceUpdate();
          }
        });
      }
    }
  },
  methods: {
    configureLaborService(value){if(Number(value)===1){this.producto.EsServicio=1;this.producto.EsMercaderia=0;this.producto.ControlaStock=0;}},
    // SERIE
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
    // SERIE

    setOferta(e) {
      if (e == null) {
        this.$toasted.info("Se quitó el orden de la oferta");
        this.producto.PosOferta = 10000;
        this.producto.FechaOfertaInicio = null;
        this.producto.FechaOfertaFin = null;
      } else {
        if (!this.producto.FechaOfertaInicio) {
          this.producto.FechaOfertaInicio = this.fechaOfertaISO(0);
        }
        if (!this.producto.FechaOfertaFin) {
          this.producto.FechaOfertaFin = this.fechaOfertaISO(7);
        }
        this.$toasted.info("Seleccione el orden; si está ocupado, las demás ofertas se reordenarán solas");
      }
    },
    fechaOfertaISO(dias) {
      const fecha = new Date();
      fecha.setDate(fecha.getDate() + dias);
      const year = fecha.getFullYear();
      const month = String(fecha.getMonth() + 1).padStart(2, "0");
      const day = String(fecha.getDate()).padStart(2, "0");
      return year + "-" + month + "-" + day;
    },
    cargarCatOferta() {
      this.categoriaItems = [];
      this.axios
        .get(this.API + "categorias/oferta")
        .then(response => {
          if (response.data) {
            this.categoriaItems.push({
              value: null,
              text: "=== SIN OFERTA ==="
            });
            response.data.map(cat => {
              this.categoriaItems.push({
                value: cat.IdProductoCategoria,
                text: cat.ProductoCategoria
              });
            });
          } else {
            this.categoriaItems.push({
              value: null,
              text: "=== SIN OFERTA ==="
            });
          }
        })
        .catch(error => {});
    },
    handleFileUpload() {
      this.file = this.$refs.file.files[0];
    },
    calcularPreciosPaquete(prodDetalles) {
      let sumatoriaCosto = 0;
      let sumatoriaContado = 0;
      prodDetalles.forEach(prod => {
        if (prod.Cantidad) {
          sumatoriaCosto +=
            (prod.PrecioCosto ? prod.PrecioCosto : 0) *
            (prod.Cantidad ? prod.Cantidad : 0);
          sumatoriaContado +=
            (prod.Precio ? prod.Precio : 0) *
            (prod.Cantidad ? prod.Cantidad : 0);
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
    actualizarLiquidacion() {
      this.precioLiquidacion =
        this.producto.PrecioContado -
        Math.round(
          (((this.producto.PorcentajeLiquidacion > 0
            ? this.producto.PorcentajeLiquidacion
            : 0) *
            (this.producto.PrecioContado > 0
              ? this.producto.PrecioContado
              : 0)) /
            100) *
            10
        ) /
          10;
      this.actualizarPorcentajeUtilidad();
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
        ControlaStockMinimo: 0,
        ControlaStock: 1,
        PrecioApp: 0,
        OfertaWeb: 0,
        PrecioWeb: null,
        PrecioAnterior: null,
        PosOferta: 10000,
        FechaOfertaInicio: null,
        FechaOfertaFin: null,
        MostrarApp: 0,
        StockMinimo: 0,
        PrecioContado: 0,
        PrecioPorMayor: 0,
        precioConvenio: 0,
        // Botapie: '',
        // Genero: '',
        categoria: {},
        marca: {},
        IdOfertaCategoria: null,
        PorcentajeLiquidacion: 0,
        TienePresentacion: 0,
        // modelo: {},
        CodigoProductoSunat: '00000000',
        codigoSunat: {
          codigo: '00000000',
          busqueda_label: '00000000 - NO REGISTRADO / COMODÍN GENÉRICO'
        },
        //talla: {},
        EsServicio: 0,
        EsServicioManoObra: 0,
        EsMercaderia: 0,
        OpcContable: 0,
        medicion: {
          IdProductoMedicion: 6,
          ProductoMedicion: "UNIDAD"
        },
        Imagen: null,
        precios: [
          { nombre: "P.HUANUCO", cantidad: 0, precio: 0 },
          { nombre: "P.PUCALLPA", cantidad: 0, precio: 0 },
          { nombre: "P.ESPECIAL", cantidad: 0, precio: 0 },
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
      if (this.savingProduct) return;

      if (this.producto.EsMercaderia == 1 && this.producto.EsServicio == 1) {
        this.$toasted.show(
          "El producto solo puede ser MERCADERIA o SERVICIO, no ambos..."
        );
        return;
      }

      if (this.producto.EsMercaderia == 0 && this.producto.EsServicio == 0) {
        this.$toasted.show(
          "Debe seleccionar si el producto es MERCADERIA o SERVICIO"
        );
        return;
      }
      if (this.producto.EsMercaderia == 1) {
        this.producto.OpcContable = 1;
      } else {
        this.producto.OpcContable = 2;
      }

      if (
        this.producto.IdOfertaCategoria != null &&
        this.producto.PosOferta == 10000
      ) {
        this.$toasted.error("Seleccione un orden si el producto es oferta.", {
          duration: 3000
        });
        return;
      }

      if (this.producto.IdOfertaCategoria != null) {
        const inicio = this.producto.FechaOfertaInicio;
        const fin = this.producto.FechaOfertaFin;
        if ((inicio && !fin) || (!inicio && fin)) {
          this.$toasted.error("Seleccione la fecha de inicio y la fecha de fin de la oferta.", { duration: 3500 });
          return;
        }
        if (inicio && fin && fin < inicio) {
          this.$toasted.error("La fecha final de la oferta no puede ser menor que la fecha inicial.", { duration: 3500 });
          return;
        }
      }

      // Agregar producto DET
      if (this.showProductoDet) {
        this.producto.productosDet = this.$refs.productoDet.productos;
      }
      // console.log(JSON.stringify(this.producto))
      this.producto.PorcentajeLiquidacion =
        this.producto.PorcentajeLiquidacion > 0
          ? this.producto.PorcentajeLiquidacion
          : 0;

      if (this.producto.IdOfertaCategoria == null) {
        this.producto.PosOferta = 10000;
      }

      if (this.producto.EsPadre == 1) {
        let productosDet = this.producto.productosDet;
        let val = this.producto.productosDet.some(
          v => v.PrecioCosto > v.Precio
        );
        if (val) {
          this.$toasted.error("No puede tener un precio menor que su costo.", {
            duration: 3000
          });
          return;
        }
      }

      this.savingProduct = true;
      this.axios
        .post(this.API + "productos", {
          ...this.producto,
          PreciosPorProducto: this.producto.precios,
          productoSerie: this.productoSerie
        })
        .then(async response => {
          if (response.data.error) {
            this.$toasted.show(
              response.data.error + " - " + response.data.codigoBarra
            );
            return;
          }

          // this.initForm()
          // this.producto.talla = {}
          // this.$refs.inputTalla.setValue('')

          this.producto.IdProducto = response.data.insertId;
          try { await this.$refs.productImages.upload(response.data.insertId); }
          catch (error) { this.$toasted.error('El producto se guardó, pero falló la carga de imágenes. Reintenta guardar.'); return; }
          this.$toasted.success("Producto e imágenes guardados correctamente");
          this.$emit("productoactualizado", response.data.insertId);
          if (this.cbGuardar) this.cbGuardar(response.data.insertId, this.producto.precios, this.producto);

          this.$refs.productoForm.hide();
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        }).then(() => { this.savingProduct = false; });
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
    ProductoImagenes,
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

/* Editor de producto premium: paleta alineada con el entorno Neurosoft. */
.pos-product-editor-modal {
  --editor-navy: #082c43;
  --editor-deep: #0a4158;
  --editor-cyan: #20c7dc;
  --editor-blue: #2878e8;
  --editor-violet: #6558dc;
  --editor-green: #10b878;
  --editor-ink: #15324b;
  --editor-muted: #62798d;
  --editor-border: #cfe0ea;
  z-index: 10550 !important;
  padding-right: 0 !important;
}

body .pos-product-editor-modal .modal-dialog {
  width: calc(100% - 32px) !important;
  max-width: 900px !important;
  margin: 16px auto !important;
}

body .pos-product-editor-modal .modal-content {
  max-height: calc(100vh - 32px) !important;
  overflow: hidden;
  border: 1px solid rgba(49, 193, 217, .52) !important;
  border-radius: 20px !important;
  background: #f7fbfd;
  box-shadow: 0 30px 80px rgba(4, 28, 49, .42), 0 0 0 1px rgba(255,255,255,.58) inset !important;
}

body .pos-product-editor-modal .modal-header {
  min-height: 62px;
  align-items: center;
  padding: 12px 18px !important;
  border: 0 !important;
  color: #fff;
  background:
    radial-gradient(circle at 82% -80%, rgba(93, 97, 235, .78), transparent 47%),
    linear-gradient(112deg, #072a42 0%, #086175 62%, #304b91 100%) !important;
  box-shadow: 0 9px 24px rgba(6, 47, 75, .24);
}

body .pos-product-editor-modal .modal-title {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #fff;
  font-size: 1.08rem;
  font-weight: 900;
  letter-spacing: .025em;
}

body .pos-product-editor-modal .modal-title::before {
  display: inline-grid;
  width: 36px;
  height: 36px;
  place-items: center;
  border: 1px solid rgba(255,255,255,.25);
  border-radius: 11px;
  content: "\f044";
  background: linear-gradient(145deg, rgba(35,215,226,.32), rgba(103,87,229,.4));
  box-shadow: inset 0 1px rgba(255,255,255,.2), 0 7px 16px rgba(0,0,0,.16);
  font-family: "Font Awesome 5 Free", FontAwesome, sans-serif;
  font-size: .9rem;
  font-weight: 900;
}

body .pos-product-editor-modal .modal-header .close {
  display: inline-grid;
  width: 36px;
  height: 36px;
  margin: 0 !important;
  padding: 0 !important;
  place-items: center;
  border: 1px solid rgba(255,255,255,.22);
  border-radius: 50%;
  color: #fff;
  background: rgba(255,255,255,.1);
  opacity: .96;
  text-shadow: none;
  transition: background 160ms ease, transform 160ms ease;
}

body .pos-product-editor-modal .modal-header .close:hover {
  background: rgba(239, 60, 89, .9);
  transform: rotate(6deg);
}

body .pos-product-editor-modal .pos-product-editor-modal__body {
  max-height: calc(100vh - 162px) !important;
  padding: 16px !important;
  overflow-x: hidden;
  overflow-y: auto;
  background:
    radial-gradient(circle at 0 0, rgba(32,199,220,.09), transparent 25rem),
    radial-gradient(circle at 100% 100%, rgba(101,88,220,.07), transparent 26rem),
    linear-gradient(135deg, #f6fbfd, #f7f8fc 58%, #f8f6fc) !important;
  scrollbar-color: #36a9c2 #e7f0f4;
  scrollbar-width: thin;
}

body .pos-product-editor-modal .pos-product-editor-modal__body::-webkit-scrollbar {
  width: 9px;
}

body .pos-product-editor-modal .pos-product-editor-modal__body::-webkit-scrollbar-track {
  border-radius: 999px;
  background: #e7f0f4;
}

body .pos-product-editor-modal .pos-product-editor-modal__body::-webkit-scrollbar-thumb {
  border: 2px solid #e7f0f4;
  border-radius: 999px;
  background: linear-gradient(180deg, var(--editor-cyan), var(--editor-violet));
}

.pos-product-editor-grid {
  margin: 0 -4px 10px !important;
}

.pos-product-editor-grid > .pos-product-editor-column {
  flex: 0 0 calc(50% - 8px);
  max-width: calc(50% - 8px);
  margin: 0 4px;
  padding: 14px !important;
  border: 1px solid rgba(119, 164, 193, .22);
  border-radius: 16px;
  background: rgba(255,255,255,.88);
  box-shadow: 0 10px 25px rgba(18, 57, 87, .07), inset 0 1px #fff;
}

.pos-product-editor-grid > .pos-product-editor-column::before {
  display: flex;
  min-height: 31px;
  margin: -4px -2px 12px;
  padding: 7px 10px;
  align-items: center;
  border-bottom: 1px solid rgba(43, 177, 207, .16);
  color: #0c6379;
  content: "INFORMACIÓN DEL PRODUCTO";
  font-size: .69rem;
  font-weight: 900;
  letter-spacing: .08em;
}

.pos-product-editor-grid > .pos-product-editor-column:nth-child(2)::before {
  color: #514aa4;
  content: "CONTROL, STOCK Y PRECIOS";
}

body .pos-product-editor-modal .form-group {
  margin-bottom: 11px;
}

body .pos-product-editor-modal .form-group > label,
body .pos-product-editor-modal .pos-product-pricing-section label,
body .pos-product-editor-modal .pos-product-presentation-section label {
  margin-bottom: 5px;
  color: #31506a;
  font-size: .69rem;
  font-weight: 900;
  letter-spacing: .045em;
  text-transform: uppercase;
}

body .pos-product-editor-modal .form-control,
body .pos-product-editor-modal .custom-select,
body .pos-product-editor-modal .autocomplete-input {
  min-height: 42px;
  border: 1px solid var(--editor-border) !important;
  border-radius: 10px;
  color: var(--editor-ink);
  background: #fbfdff;
  box-shadow: inset 0 1px 2px rgba(18, 52, 78, .035);
  font-size: .84rem;
  transition: border-color 150ms ease, box-shadow 150ms ease, background 150ms ease;
}

body .pos-product-editor-modal textarea.form-control {
  min-height: 92px;
  line-height: 1.35;
}

body .pos-product-editor-modal .form-control:focus,
body .pos-product-editor-modal .custom-select:focus,
body .pos-product-editor-modal .autocomplete-input:focus {
  border-color: #28bad1 !important;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(32, 199, 220, .13) !important;
}

body .pos-product-editor-modal .form-control:disabled,
body .pos-product-editor-modal .form-control[readonly] {
  color: #5d7080;
  background: #edf3f6;
}

body .pos-product-editor-modal .input-group-append .btn,
body .pos-product-editor-modal .modal-body .btn {
  min-height: 42px;
  margin: 0 !important;
  border: 0 !important;
  border-radius: 10px !important;
  box-shadow: 0 6px 14px rgba(26, 74, 113, .16) !important;
  font-size: .76rem;
  font-weight: 850;
}

body .pos-product-editor-modal .input-group-append .btn {
  border-radius: 0 10px 10px 0 !important;
}

body .pos-product-editor-modal .btn-primary {
  color: #fff !important;
  background: linear-gradient(135deg, var(--editor-blue), var(--editor-violet)) !important;
}

body .pos-product-editor-modal .btn-success {
  color: #fff !important;
  background: linear-gradient(135deg, #079e6d, #19c67f) !important;
}

body .pos-product-editor-modal .btn-info {
  color: #fff !important;
  background: linear-gradient(135deg, #08a9c4, #2479d7) !important;
}

/* Lector con lenguaje visual IMCEDEN. */
.pos-editor-barcode-shell {
  position: relative;
  display: grid;
  grid-template-columns: 46px minmax(0, 1fr) auto;
  min-height: 48px;
  align-items: stretch;
  overflow: hidden;
  border: 1px solid rgba(30, 183, 208, .58);
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 8px 18px rgba(20, 116, 153, .11), inset 0 1px #fff;
  transition: border-color 160ms ease, box-shadow 160ms ease, transform 160ms ease;
}

.pos-editor-barcode-shell:focus-within {
  border-color: #21d0df;
  box-shadow: 0 9px 22px rgba(19, 158, 190, .19), 0 0 0 4px rgba(32,199,220,.12);
  transform: translateY(-1px);
}

.pos-editor-barcode-icon {
  display: grid;
  place-items: center;
  color: #fff;
  background: linear-gradient(145deg, #07576c, #128e9e);
  font-size: 1rem;
  text-shadow: 0 1px 3px rgba(0,0,0,.22);
}

body .pos-product-editor-modal .pos-editor-barcode-input.form-control {
  min-height: 46px;
  border: 0 !important;
  border-radius: 0 !important;
  background: #fff;
  box-shadow: none !important;
  font-family: Consolas, "Courier New", monospace;
  font-size: .92rem;
  font-weight: 800;
  letter-spacing: .045em;
}

.pos-editor-barcode-ready {
  display: inline-flex;
  min-width: 103px;
  padding: 0 11px;
  align-items: center;
  justify-content: center;
  gap: 6px;
  color: #087052;
  border-left: 1px solid #d5e8eb;
  background: linear-gradient(135deg, #e8faf4, #eaf8fc);
  font-size: .61rem;
  font-weight: 900;
  letter-spacing: .055em;
  text-transform: uppercase;
  white-space: nowrap;
}

.pos-editor-barcode-ready i {
  color: #19c792;
  font-size: .48rem;
  filter: drop-shadow(0 0 4px rgba(25,199,146,.65));
}

body .pos-product-editor-modal .custom-checkbox {
  min-height: 40px;
  padding: 10px 10px 9px 38px;
  border: 1px solid #dce8ee;
  border-radius: 10px;
  background: linear-gradient(135deg, #f9fcfd, #f6f7fc);
}

body .pos-product-editor-modal .custom-control-label {
  color: #38546b;
  font-size: .76rem;
  font-weight: 750;
  line-height: 1.25;
  text-transform: none;
}

body .pos-product-editor-modal .custom-control-input:checked ~ .custom-control-label::before {
  border-color: #17ae86;
  background: linear-gradient(135deg, #0ba779, #25c78e);
}

.pos-product-app-panel {
  padding: 13px !important;
  border: 1px solid rgba(68, 105, 222, .3) !important;
  border-radius: 13px;
  background: linear-gradient(135deg, rgba(235,246,255,.9), rgba(243,239,255,.9));
  box-shadow: inset 0 1px #fff, 0 7px 18px rgba(44,75,142,.07);
}

.pos-product-offer-schedule {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 10px;
  margin-top: 2px;
  padding: 11px;
  border: 1px solid rgba(11, 126, 148, .2);
  border-radius: 12px;
  background: rgba(255, 255, 255, .72);
}

.pos-product-offer-date label {
  display: flex !important;
  align-items: center;
  gap: 6px;
  margin-bottom: 5px !important;
  color: #24536b !important;
}

.pos-product-offer-date label i {
  color: #0b9db6;
}

.pos-product-offer-schedule small {
  grid-column: 1 / -1;
  color: #58758a;
  font-size: .69rem;
  font-weight: 700;
  line-height: 1.35;
}

.pos-product-offer-schedule small i {
  margin-right: 5px;
  color: #7561db;
}

body .pos-product-editor-modal .img-fluid {
  display: block;
  width: auto;
  max-width: 100%;
  max-height: 140px;
  margin: 0 0 8px;
  padding: 7px;
  border: 1px solid #d8e5ec;
  border-radius: 12px;
  background: #fff;
  object-fit: contain;
  box-shadow: 0 7px 18px rgba(22, 62, 90, .08);
}

body .pos-product-editor-modal input[type="file"] {
  width: 100%;
  padding: 7px;
  border: 1px dashed #a9c9d7;
  border-radius: 10px;
  color: #557083;
  background: #f7fbfd;
  font-size: .72rem;
}

.pos-product-pricing-section,
.pos-product-presentation-section,
.pos-product-series-section {
  margin: 10px 0 0 !important;
  padding: 13px 7px 4px;
  border: 1px solid rgba(105, 153, 183, .2);
  border-radius: 15px;
  background: rgba(255,255,255,.82);
  box-shadow: 0 9px 22px rgba(16, 52, 82, .06);
}

.pos-product-pricing-section::before,
.pos-product-presentation-section::before,
.pos-product-series-section::before {
  width: 100%;
  margin: -3px 8px 11px;
  color: #0c6379;
  content: "PRECIOS Y RENTABILIDAD";
  font-size: .69rem;
  font-weight: 900;
  letter-spacing: .08em;
}

.pos-product-presentation-section::before {
  content: "PRESENTACIONES Y ESCALAS DE PRECIO";
}

.pos-product-series-section::before {
  content: "SERIES DEL PRODUCTO";
}

body .pos-product-editor-modal hr {
  margin: 14px 0;
  border: 0;
  border-top: 1px solid rgba(52, 167, 192, .17);
}

body .pos-product-editor-modal .table {
  overflow: hidden;
  border: 1px solid #dce8ee;
  border-radius: 11px;
  color: var(--editor-ink);
  background: #fff;
  font-size: .75rem;
}

body .pos-product-editor-modal .table thead th {
  border: 0;
  color: #fff;
  background: linear-gradient(135deg, #093249, #086a77);
  font-size: .65rem;
  letter-spacing: .045em;
  text-transform: uppercase;
}

body .pos-product-editor-modal .pos-product-editor-modal__footer {
  min-height: 72px;
  padding: 11px 16px !important;
  border-top: 1px solid rgba(54, 167, 195, .18) !important;
  background: linear-gradient(135deg, #f6fbfd, #f4f5fb) !important;
  box-shadow: 0 -8px 22px rgba(20, 56, 84, .06);
}

.pos-product-editor-footer-content {
  display: flex;
  width: 100%;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.pos-product-barcode-print {
  flex: 0 1 330px;
}

.pos-product-barcode-print-control .form-control {
  width: 74px;
  flex: 0 0 74px;
  border-radius: 10px 0 0 10px !important;
  text-align: center;
  font-weight: 900;
}

body .pos-product-editor-modal .pos-product-barcode-print-button.btn {
  min-height: 42px;
  padding: 0 15px;
  border-radius: 0 10px 10px 0 !important;
  letter-spacing: .015em;
  white-space: nowrap;
}

.pos-product-barcode-print-button i,
.pos-product-editor-actions .btn i {
  margin-right: 6px;
}

.pos-product-editor-actions {
  display: flex;
  margin-left: auto;
  align-items: center;
  justify-content: flex-end;
  gap: 8px;
}

body .pos-product-editor-modal .pos-product-editor-actions .btn {
  min-width: 112px;
  min-height: 43px;
  margin: 0 !important;
  border: 0 !important;
  border-radius: 11px !important;
  color: #fff !important;
  font-size: .76rem;
  font-weight: 900;
  letter-spacing: .035em;
  text-transform: uppercase;
  box-shadow: 0 8px 17px rgba(18, 55, 84, .17) !important;
}

body .pos-product-editor-modal .pos-product-save-button.btn {
  background: linear-gradient(135deg, #2479e8, #6557dd) !important;
}

body .pos-product-editor-modal .pos-product-close-button.btn {
  background: linear-gradient(135deg, #516575, #293c4f) !important;
}

body .pos-product-editor-modal .pos-product-clear-button.btn {
  color: #5b4300 !important;
  background: linear-gradient(135deg, #ffd45c, #ffac35) !important;
}

body .pos-product-editor-modal .btn:hover,
body .pos-product-editor-modal .btn:focus {
  filter: brightness(1.07) saturate(1.05);
  transform: translateY(-1px);
}

@media (max-width: 767.98px) {
  .pos-product-offer-schedule {
    grid-template-columns: 1fr;
  }

  .pos-product-offer-schedule small {
    grid-column: 1;
  }

  body .pos-product-editor-modal .modal-dialog {
    width: calc(100% - 14px) !important;
    margin: 7px auto !important;
  }

  body .pos-product-editor-modal .modal-content {
    max-height: calc(100vh - 14px) !important;
    border-radius: 15px !important;
  }

  body .pos-product-editor-modal .pos-product-editor-modal__body {
    max-height: calc(100vh - 148px) !important;
    padding: 9px !important;
  }

  .pos-product-editor-grid > .pos-product-editor-column {
    flex: 0 0 100%;
    max-width: 100%;
    margin: 0 0 8px;
    padding: 11px !important;
  }

  .pos-editor-barcode-shell {
    grid-template-columns: 42px minmax(0, 1fr);
  }

  .pos-editor-barcode-ready {
    display: none;
  }

  .pos-product-editor-footer-content {
    flex-wrap: wrap;
  }

  .pos-product-barcode-print {
    flex: 1 1 100%;
  }

  .pos-product-editor-actions {
    width: 100%;
  }

  body .pos-product-editor-modal .pos-product-editor-actions .btn {
    min-width: 0;
    flex: 1 1 0;
  }
}
</style>
