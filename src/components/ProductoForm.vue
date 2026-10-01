<template>
  <div>
    <b-btn v-if="showButton" type="button" variant="primary" @click="show = true">Nuevo</b-btn>
    <!-- Modal Component -->
    <div @keydown.enter.prevent>
    <b-modal :no-close-on-esc="true" :no-close-on-backdrop="true" :id="modalId" size="xl"
      ref="productoForm" v-model="show" modal-class="product-form-modal"
      dialog-class="product-form-dialog" content-class="product-form-content"
      body-class="product-form-body" footer-class="product-form-footer">
      <template slot="modal-title">
        <span class="product-form-title-icon"><i class="fas fa-box-open"></i></span>
        <span class="product-form-title-copy">
          <small>LLANTAS Y BATERÍAS · INVENTARIO</small>
          <strong>{{ title }}</strong>
        </span>
      </template>
      <b-row class="product-form-grid">
        <b-col md="12" class="product-form-toolbar">
            <div>
              <span class="product-form-state"><i class="fas fa-pen"></i> Ficha del producto</span>
              <small>Actualiza la información comercial y de inventario</small>
            </div>
            <b-btn v-if="showNew" class="product-form-new" size="md" @click="eliminaId">
              <i class="fas fa-plus"></i><b>Nuevo producto</b>
            </b-btn>
        </b-col>

        <b-col md="6" class="product-form-column product-form-column-primary">
        <b-form-group>
          <label>Código de producto / barras:</label>
            <b-input-group class="product-barcode-input">
              <b-form-input 
                ref="inputCodigoBarra"
                v-model="producto.CodigoBarra"
                @input="actualizarCodigoBarraManual"
                type="text"
                class="form-control upper"
                placeholder="ICE1, ICE2... o código del fabricante"
                ></b-form-input>
              <b-input-group-append>
                <b-btn class="product-code-generate" type="button" :disabled="generandoCodigo" @click="generarCodigoBarra(true)" title="Generar el siguiente código ICE">
                  <i class="fas fa-hashtag"></i><span>Generar ICE</span>
                </b-btn>
                <b-btn class="product-barcode-scan" type="button" @click="scanProductBarcode" title="Escanear código de barras" aria-label="Escanear código de barras">
                  <i class="fas fa-barcode"></i><span>Escanear</span>
                </b-btn>
              </b-input-group-append>
            </b-input-group>
            <small class="product-field-help" v-if="codigoBarraAutogenerado">Código ICE secuencial y editable. También puede ingresar un código del fabricante.</small>
          </b-form-group>
          <label>Código asociado al modelo:</label>
            <b-form-group>
            <b-form-input 
              ref="inputCodigoModelo"
              v-model="producto.CodeBarModelo"
              @input="(val) => producto.CodeBarModelo = val && val.toUpperCase()"
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
            <label>Producto:</label>
            <b-form-input v-model="producto.Producto"
              required="required"
              type="text"
              @input="(val) => producto.Producto = val && val.toUpperCase()"
              :value="producto.Producto"
              ></b-form-input>
          </b-form-group>
          <b-form-group class="product-form-optional-hidden">
            <label>Descripción corta:</label>
            <b-form-input v-model="producto.ProductoDescCorto" maxlength="255"
              placeholder="Resumen visible en el listado y punto de venta"></b-form-input>
          </b-form-group>
          <b-form-group class="product-form-optional-hidden">
            <label>Descripción detallada:</label>
            <b-form-textarea v-model="producto.ProductoDesc" :rows="3" :max-rows="5"
              placeholder="Material, características, uso, contenido u otros detalles"></b-form-textarea>
          </b-form-group>
          
          <b-form-group>
            <label>Ubicación (anaquel):</label>
            <autocomplete
                :url="API + 'productos/anaquel'"
                ref="inputPresentacion"
                anchor="Anaquel"
                label="writer"
                :placeholder.sync="producto.Anaquel"
                :on-select="val => producto.Anaquel = val.Anaquel && val.Anaquel.toUpperCase()"
                :onInput="val => producto.Anaquel = val && val.toUpperCase()"
                :onFocus="focusAutocomplete.bind(null, 'inputPresentacion')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>
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
          <b-form-group class="product-form-optional-hidden">
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
        </b-col>

        <b-col md="6" class="product-form-column product-form-column-secondary">
          <b-form-group>
            <label>Unidad de venta:</label>
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
            <label>Medida / especificación:</label>
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
          <b-form-group class="product-form-optional-hidden">
            <label>Genero:</label>            
            <b-form-select required="required" v-model="producto.Genero" :options="generos" />
          </b-form-group>
          <b-form-group class="product-form-optional-hidden">
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
            <!-- <b-form-input v-model="producto.Botapie"
              type="text"
              @input="(val) => producto.Botapie = val && val.toUpperCase()"              
              ></b-form-input> -->
          </b-form-group>
          <b-form-group class="product-form-optional-hidden">
            <label>Porcentaje Utilidad:</label>
            <input 
              v-model="producto.PorcentajeUtilidad"
              @input="actualizarPrecioContado"
              type="number"
              step="0.0001"
              class="form-control"
              />
          </b-form-group>
          <b-form-group>
            <label>Stock mínimo / reposición:</label>
            <input   
              required="required"
              v-model="producto.StockMinimo"
              type="number"
              step="0.001"
              class="form-control"
              />
          </b-form-group>

          <b-form-group class="product-scrap-toggle">
            <b-form-checkbox v-model="producto.EsChatarra" :value="1" :unchecked-value="0">
              <span>
                <strong>Es chatarra</strong>
                <small>Permite comprar este producto desde Caja/Banco y aumentar su stock.</small>
              </span>
            </b-form-checkbox>
          </b-form-group>

          <b-form-group class="product-form-image-field">
            <label>Galería del producto <small>(hasta 3 imágenes)</small>:</label>
            <div class="product-form-image-gallery">
              <div v-for="slot in 3" :key="slot" class="product-form-image-slot">
                <div class="product-form-image-preview">
                  <b-img v-if="imagePreview(slot)" :src="imagePreview(slot)" :alt="'Imagen ' + slot"></b-img>
                  <span v-else><i class="fas fa-camera"></i></span>
                  <b>{{ slot }}</b>
                </div>
                <label :for="'product-image-' + slot" class="product-form-image-select">
                  <i class="fas fa-upload"></i> {{ imagePreview(slot) ? 'Cambiar' : 'Subir' }}
                </label>
                <input :id="'product-image-' + slot" type="file" accept="image/jpeg,image/png,image/gif,image/webp"
                  @change="handleFileUpload($event, slot)" />
              </div>
            </div>
            <small class="product-form-image-help">La imagen 1 será la portada. Las tres se podrán ampliar en los puntos de venta.</small>
          </b-form-group>
          
        </b-col>
      </b-row>

      <hr/>
      <b-row>
        <b-col md="6">
          <b-form-group>
            <label>Precio de compra (costo):</label>
            <input
              v-model="producto.PrecioCosto"
              @input="actualizarPorcentajeUtilidad"
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
      </b-row>
      <b-row class="product-liquidation-section">
        <b-col md="6">

        </b-col>
        <b-col md="6">
            
            <b-row>
              <b-col>
                <label>Porcentaje de descuento:</label>
                <input v-model="producto.PorcentajeLiquidacion"
                type="number"
                class="form-control"
                step="0.01"
                min="0"
                max="100"
                @input="actualizarLiquidacion"
                />
              </b-col>
              <b-col>
                <label>Precio con descuento:</label>
                <br>
                <div style="display:flex; justify-content: start; align-items: center; margin-top: 6px;">
                  <b>{{ precioLiquidacion }}</b>
                </div>
              </b-col>
            </b-row>
        </b-col>
      </b-row>
       <hr />
      <section class="product-presentations-section">
        <div class="product-presentations-heading">
          <div class="product-presentations-heading__copy">
            <span class="product-presentations-eyebrow"><i class="fas fa-tags"></i> PUNTO DE VENTA</span>
            <h3>Presentaciones y precios</h3>
            <p>Configure precios por unidad, paquete o cantidad. Las opciones activas aparecerán al tocar el botón de lista en ventas.</p>
          </div>
          <div class="product-presentations-toggle-shell">
            <b-form-checkbox v-model="TienePresentacion" switch class="product-presentations-toggle">
              <span>Habilitar presentaciones</span>
              <small>{{ TienePresentacion ? 'Opciones activas' : 'Precio único' }}</small>
            </b-form-checkbox>
          </div>
        </div>
        <div v-if="!TienePresentacion" class="product-presentations-hint">
          <i class="fas fa-info-circle"></i> El producto se venderá con su precio normal. Active la casilla para agregar otros formatos y precios.
        </div>
        <b-row v-if="TienePresentacion" class="product-presentations-grid">
          <b-col cols="12" sm="6" xl="3" v-for="(precio, index) in producto.precios" :key="'presentacion-' + index">
            <article class="product-presentation-card">
              <header class="product-presentation-card__header">
                <div class="product-presentation-card__identity">
                  <span class="product-presentation-card__number">{{ index + 1 }}</span>
                  <span class="product-presentation-card__title">
                    <strong>Opción {{ index + 1 }}</strong>
                    <small>{{ precio.nombre && Number(precio.cantidad) > 0 ? 'Configurada' : 'Sin configurar' }}</small>
                  </span>
                </div>
                <span class="product-presentation-card__factor" v-if="Number(precio.cantidad) > 0">x{{ precio.cantidad }}</span>
              </header>
              <b-form-group class="product-presentation-field product-presentation-field--name">
                <template slot="label"><i class="fas fa-tag"></i> Nombre de presentación</template>
                <b-form-input
                  v-model="precio.nombre"
                  :disabled="(idPerfil == 1) ? false : true"
                  maxlength="40"
                  placeholder="Ej.: PAQUETE X4, OFERTA"
                  @input="(val) => precio.nombre = val && val.toUpperCase()"
                ></b-form-input>
              </b-form-group>
              <b-form-group class="product-presentation-field">
                <template slot="label"><i class="fas fa-layer-group"></i> Unidades por presentación</template>
                <b-input-group append="unid.">
                  <b-form-input v-model="precio.cantidad" :disabled="(idPerfil == 1) ? false : true" type="number" min="0" step="1" placeholder="Ej.: 4"></b-form-input>
                </b-input-group>
              </b-form-group>
              <b-form-group class="product-presentation-field">
                <template slot="label"><i class="fas fa-coins"></i> Costo del paquete</template>
                <b-input-group prepend="S/">
                  <b-form-input v-model="precio.precioc" :disabled="(idPerfil == 1) ? false : true" type="number" min="0" step="0.01" placeholder="0.00"></b-form-input>
                </b-input-group>
              </b-form-group>
              <b-form-group class="product-presentation-field">
                <template slot="label"><i class="fas fa-cash-register"></i> Precio de venta</template>
                <b-input-group prepend="S/">
                  <b-form-input v-model="precio.precio" :disabled="(idPerfil == 1) ? false : true" type="number" min="0" step="0.01" placeholder="0.00"></b-form-input>
                </b-input-group>
              </b-form-group>
              <footer class="product-presentation-card__total">
                <span><strong>Importe total</strong><small>Precio × unidades</small></span>
                <strong>S/ {{ (Number(precio.precio) * Number(precio.cantidad)) | redondear }}</strong>
              </footer>
            </article>
          </b-col>
        </b-row>
      </section>

      <hr class="product-form-optional-hidden" />
      <b-row>
        <b-col>
          <producto-form-det :id-producto="producto.IdProducto" v-if="showProductoDet && show" ref="productoDet"></producto-form-det>
        </b-col>
      </b-row>

      <div slot="modal-footer" class="product-form-footer-layout">
        <div class="product-form-barcode-control" v-if="producto.IdProducto">
          <span><i class="fas fa-barcode"></i> Etiquetas</span>
          <b-input-group class="product-form-barcode-group">
            <b-form-input
              v-model="cantidadCodigoBarra"
              type="number"
              min="1"
              step="1"
            ></b-form-input>
            <b-input-group-append>
              <b-btn class="product-form-barcode-button" target="_blank" :href="'/views/codigoBarra.php?idProducto=' + producto.IdProducto + '&cantidad=' + cantidadCodigoBarra "><i class="fas fa-print"></i> Imprimir códigos</b-btn>
            </b-input-group-append>
          </b-input-group>
        </div>
        <div class="product-form-footer-actions">
          <b-btn variant="danger" @click="eliminarProducto(producto)" v-if="producto.IdProducto">
            <i class="fas fa-ban"></i> Anular
          </b-btn>
          <b-btn variant="warning" @click="clearForm" v-if="!producto.IdProducto">
            <i class="fas fa-eraser"></i> Limpiar
          </b-btn>
          <b-btn variant="primary" type="button" :disabled="guardandoProducto || generandoCodigo" @click="guardarProducto">
            <i class="fas fa-save"></i> {{this.mensajeBtn}}
          </b-btn>
          <b-btn variant="secondary" @click="show=false">
            <i class="fas fa-times"></i> Cerrar
          </b-btn>
        </div>
       </div>
    </b-modal>
    </div>
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
      mensajeBtn: "Crear",
      showNew: false,
      showProductoDet: false,
      cantidadCodigoBarra: 1,
      title: "Nuevo Producto",
      API: config.API,
      show: false,
      generos: ["VARON", "DAMA", "NIÑO", "NIÑA", "UNISEX", "JUNIOR"],
      producto: {},
      productosDet: [],
      precioLiquidacion: 0,
      idPerfil: sessionStorage.getItem("IdPerfil"),
      files: [null, null, null],
      imagePreviews: [null, null, null],
      manterproducto: {
        default: false,
        type: Boolean
      },
      TienePresentacion: false,
      guardandoProducto: false,
      generandoCodigo: false,
      codigoBarraAutogenerado: false,
      siguienteCodigoICE: 0,
      productosCache: null
    };
  },
  beforeMount() {
    this.initForm();
  },
  props: ["showButton", "productoUpdate", "cbGuardar"],
  computed: {
    modalId() {
      return `modalProducto-${this._uid}`;
    }
  },
  watch: {
    async show(val) {
      this.preproducto = { ...(this.productoUpdate || {}) };
      if (val) {
      } else {
        this.producto.IdProducto;
      }
      this.$nextTick(() => {
        if (this.manterproducto && this.manterproducto.default && this.preproducto.Producto) {
          this.producto.Producto = this.limpiarNombreProducto(
            this.preproducto.Producto,
            this.preproducto.CodigoBarra
          );
          this.producto.PrecioCosto = this.preproducto.PrecioCosto;

          this.$forceUpdate();
        }
      });
    },
    productoUpdate(newVal) {
      if (newVal.IdProducto) {
        this.mensajeBtn = "Guardar";
        this.showNew = true;
        this.title = "Editar Producto << " + newVal.CodigoBarra + " >> ";
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
        this.$refs.inputPresentacion.setValue(newVal.Anaquel);
        this.$refs.inputTalla.setValue(newVal.ProductoTalla);

        this.$refs.inputColor.setValue(newVal.Color);
        this.$refs.inputBotapie.setValue(newVal.Botapie)

        /* this.producto.modelo = {
          IdProductoModelo: newVal.IdProductoModelo,
          ProductoModelo: newVal.ProductoModelo
        } */
        this.producto.talla = {
          IdProductoTalla: newVal.IdProductoTalla,
          ProductoTalla: newVal.ProductoTalla
        };
        this.producto.Producto = this.limpiarNombreProducto(
          newVal.Producto,
          newVal.CodigoBarra
        );
        this.producto.ProductoDesc = newVal.ProductoDesc || "";
        this.producto.ProductoDescCorto = newVal.ProductoDescCorto || "";
        this.producto.IdProducto = newVal.IdProducto;
        this.producto.Genero = newVal.Genero;
        this.producto.Botapie = newVal.Botapie;
        this.producto.Talla = newVal.Talla;
        this.producto.Color = newVal.Color;
        this.producto.PorcentajeUtilidad = newVal.PorcentajeUtilidad;
        this.producto.StockMinimo = newVal.StockMinimo;
        this.producto.CodigoBarra = newVal.CodigoBarra;
        this.producto.CodeBarModelo = newVal.CodeBarModelo;
        this.producto.ProductoModelo = newVal.ProductoModelo;
        this.producto.PrecioCosto = newVal.PrecioCosto;
        this.producto.EsChatarra = this.valorBooleano(newVal.EsChatarra) ? 1 : 0;
        this.producto.Anaquel = newVal.Anaquel;
        this.producto.PrecioContado = newVal.PrecioContado;
        const preciosProducto = this.normalizarPreciosPresentacion(
          newVal.PreciosPorProducto || newVal.precios
        );
        const tienePreciosActivos = preciosProducto.some(
          precio => precio.cantidad > 0 && precio.precio > 0
        );
        this.TienePresentacion = newVal.TienePresentacion == null
          ? tienePreciosActivos
          : this.valorBooleano(newVal.TienePresentacion) && tienePreciosActivos;
        this.producto.Imagen = newVal.Imagen;
        this.producto.Imagen2 = newVal.Imagen2;
        this.producto.Imagen3 = newVal.Imagen3;
        this.files = [null, null, null];
        this.imagePreviews = [null, null, null];
        this.producto.PorcentajeLiquidacion = newVal.PorcentajeLiquidacion;
        this.actualizarLiquidacion();
        this.producto.precios = preciosProducto;
        this.codigoBarraAutogenerado = false;
      } else {
        this.precioLiquidacion = 0;
        this.title = "Nuevo Producto";
        this.showNew = false;
        this.mensajeBtn = "Crear";
        this.clearForm();
      }
    }
  },
  methods: {
    scanProductBarcode() {
      const input = this.$refs.inputCodigoBarra && this.$refs.inputCodigoBarra.$el;
      if (window.parent && typeof window.parent.openNewProductBarcodeScanner === "function") {
        window.parent.openNewProductBarcodeScanner(input);
        return;
      }
      this.$toasted.error("Abra este formulario desde Neuro App para usar la cámara.");
    },
    normalizarClave(valor) {
      return String(valor == null ? "" : valor)
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim()
        .replace(/\s+/g, " ")
        .toUpperCase();
    },
    limpiarNombreProducto(nombre, codigoBarra) {
      const nombreOriginal = String(nombre || "").trim();
      const codigo = String(codigoBarra || "").trim();
      if (!nombreOriginal || !codigo) return nombreOriginal;

      const sufijo = "-" + this.normalizarClave(codigo);
      if (this.normalizarClave(nombreOriginal).endsWith(sufijo)) {
        return nombreOriginal.slice(0, nombreOriginal.length - codigo.length - 1).trim();
      }
      return nombreOriginal;
    },
    valorBooleano(valor) {
      if (valor === true || valor === 1) return true;
      return ["1", "true", "si", "sí"].includes(String(valor || "").trim().toLowerCase());
    },
    normalizarPreciosPresentacion(valor) {
      let precios = valor;
      if (typeof precios === "string") {
        try {
          precios = JSON.parse(precios);
        } catch (error) {
          precios = [];
        }
      }
      precios = Array.isArray(precios) ? precios : [];
      return Array.from({ length: 4 }, (_, index) => {
        const precio = precios[index] || {};
        return {
          nombre: String(precio.nombre || precio.Nombre || precio.Presentacion || "").toUpperCase(),
          cantidad: Number(precio.cantidad || precio.Cantidad || precio.CantidadPresentacion || 0) || 0,
          precio: Number(precio.precio || precio.Precio || precio.PrecioVenta || 0) || 0,
          precioc: Number(precio.precioc || precio.PrecioCosto || precio.Costo || 0) || 0
        };
      });
    },
    async cargarProductosCatalogo(recargar = false) {
      if (this.productosCache && !recargar) return this.productosCache;
      const conteo = await this.axios.get(this.API + "productos/count");
      const total = Number(conteo.data && (conteo.data.total || conteo.data.Total)) || 0;
      const paginas = Math.max(1, Math.ceil(total / 100));
      const respuestas = await Promise.all(
        Array.from({ length: paginas }, (_, index) => this.axios.get(this.API + "productos", {
          params: {
            limit: 100,
            page: index + 1,
            filter: "",
            sortBy: "IdProducto",
            sortDesc: 1
          }
        }))
      );
      this.productosCache = respuestas.reduce((productos, respuesta) => {
        return productos.concat(Array.isArray(respuesta.data) ? respuesta.data : []);
      }, []);
      return this.productosCache;
    },
    async codigoBarraExiste(codigoBarra) {
      const productos = await this.cargarProductosCatalogo();
      return productos.some(producto =>
        this.normalizarClave(producto.CodigoBarra) === this.normalizarClave(codigoBarra) &&
        String(producto.IdProducto) !== String(this.producto.IdProducto || "")
      );
    },
    async nombreProductoExiste(nombre) {
      const productos = await this.cargarProductosCatalogo();
      const nombreClave = this.normalizarClave(nombre);
      return productos.some(producto =>
        String(producto.IdProducto) !== String(this.producto.IdProducto || "") &&
        this.normalizarClave(
          this.limpiarNombreProducto(producto.Producto, producto.CodigoBarra)
        ) === nombreClave
      );
    },
    async obtenerSiguienteCodigoICE() {
      const inicio = Math.max(Number(this.siguienteCodigoICE) || 1, 1);
      const respuesta = await this.axios.get(
        this.API + "productos/codigo-siguiente/" + encodeURIComponent("ICE" + inicio)
      );
      const codigo = String(respuesta.data && respuesta.data.codigo || "").trim().toUpperCase();
      const coincidencia = codigo.match(/^ICE(\d+)$/);
      if (!coincidencia) throw new Error("La API no devolvió un código ICE válido.");
      this.siguienteCodigoICE = Number(coincidencia[1]) + 1;
      return codigo;
    },
    async prepararReferenciaProducto(refName, endpoint, idField, nameField, productoField, label, setter) {
      const autocomplete = this.$refs[refName];
      const nombre = String(autocomplete && autocomplete.type || "").trim();
      const seleccionActual = this.producto[productoField] || {};
      const nombreActual = String(seleccionActual[nameField] || "").trim();
      if (
        nombre && seleccionActual[idField] &&
        this.normalizarClave(nombre) === this.normalizarClave(nombreActual)
      ) {
        return seleccionActual;
      }
      if (!nombre) throw new Error("Ingrese " + label + " del producto.");

      const listado = await this.axios.get(this.API + endpoint, {
        params: { q: nombre, limit: 100 }
      });
      const opciones = Array.isArray(listado.data) ? listado.data : [];
      const existente = opciones.find(opcion =>
        this.normalizarClave(opcion[nameField]) === this.normalizarClave(nombre)
      );
      if (existente) {
        setter.call(this, existente);
        return existente;
      }

      const valorNuevo = nombre.toUpperCase();
      const creado = await this.axios.post(this.API + endpoint, { [nameField]: valorNuevo });
      const registro = {
        [idField]: creado.data && creado.data.insertId,
        [nameField]: creado.data && creado.data[nameField] || valorNuevo
      };
      if (!registro[idField]) throw new Error("No se pudo registrar " + label + ".");
      setter.call(this, registro);
      return registro;
    },
    async generarCodigoBarra(forzar = false, notificar = true) {
      if (this.generandoCodigo || (!forzar && this.producto.CodigoBarra)) return;
      this.generandoCodigo = true;
      try {
        const codigo = await this.obtenerSiguienteCodigoICE();
        if (forzar || !this.producto.CodigoBarra) {
          this.producto.CodigoBarra = codigo;
          this.codigoBarraAutogenerado = true;
          if (notificar) this.$toasted.success("Código asignado: " + codigo);
        }
      } catch (error) {
        if (notificar) {
          this.$toasted.error("No se pudo consultar el siguiente código ICE. Puede ingresarlo manualmente.");
        }
      } finally {
        this.generandoCodigo = false;
      }
    },
    actualizarCodigoBarraManual(valor) {
      this.producto.CodigoBarra = valor && valor.toUpperCase();
      this.codigoBarraAutogenerado = false;
    },
    actualizarPorcentajeUtilidad() {
      const costo = parseFloat(this.producto.PrecioCosto) || 0;
      const venta = parseFloat(this.producto.PrecioContado) || 0;

      if (venta > 0) {
        this.producto.PorcentajeUtilidad = Number(((venta - costo) / costo * 100).toFixed(2));
      } else {
        this.producto.PorcentajeUtilidad = 0;
      }
    },
    activarPresentacion(e) {
      // this.producto.TienePresentacion = Number(e);
      console.log(this.producto.TienePresentacion);
      console.log(typeof this.producto.TienePresentacion);
      console.log("---------");
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
    imagePreview(slot) {
      const preview = this.imagePreviews[slot - 1];
      if (preview) return preview;
      const field = slot === 1 ? "Imagen" : `Imagen${slot}`;
      return this.producto[field]
        ? `/resources/images/productos/${encodeURIComponent(this.producto[field])}`
        : "";
    },
    handleFileUpload(event, slot) {
      const file = event.target.files && event.target.files[0];
      if (!file) return;
      if (!/^image\/(jpeg|png|gif|webp)$/i.test(file.type)) {
        this.$toasted.show("Seleccione una imagen JPG, PNG, GIF o WEBP.");
        event.target.value = "";
        return;
      }
      if (file.size > 8 * 1024 * 1024) {
        this.$toasted.show("Cada imagen debe pesar menos de 8 MB.");
        event.target.value = "";
        return;
      }
      this.$set(this.files, slot - 1, file);
      this.$set(this.imagePreviews, slot - 1, URL.createObjectURL(file));
    },
    uploadProductImages(idProducto) {
      const uploads = this.files.map((file, index) => {
        if (!file) return Promise.resolve();
        const formData = new FormData();
        formData.append("imagen", file);
        return this.axios({
          method: "post",
          url: `${this.API}producto/imagen/${idProducto}/${index + 1}`,
          data: formData,
          headers: { "Content-Type": "multipart/form-data" }
        });
      });
      return Promise.all(uploads);
    },
    eliminaId() {
      delete this.producto.IdProducto;
      this.mensajeBtn = "Crear";
      this.showNew = false;
      this.$refs.inputCodigoBarra.setValue("");
      //console.log(this.producto,"asdasd");
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
      var precioCosto = this.producto.PrecioCosto;
      var utilidad = this.producto.PorcentajeUtilidad / 100;
      var nuevoPrecioContado =
        parseFloat(precioCosto) + parseFloat(precioCosto * utilidad);

      console.log(nuevoPrecioContado);
      this.producto.precioContadoNormal = nuevoPrecioContado;
      this.producto.PrecioContado = this.$options.filters.redondeoMayor(
        nuevoPrecioContado
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
    setTalla(obj) {
      this.producto.talla = obj;
      this.$refs.inputTalla.setValue(obj.ProductoTalla);
    },
    clearForm() {
      this.$refs.inputCategoria.setValue("");
      this.$refs.inputMarca.setValue("");
      this.$refs.inputMedicion.setValue("UNIDAD");
      this.$refs.inputTalla.setValue("");
      this.$refs.inputModelo.setValue("");
      this.$refs.inputColor.setValue("");
      //this.$refs.inputBotapie.setValue('')

      this.initForm();
    },
    initForm() {
      this.files = [null, null, null];
      this.imagePreviews = [null, null, null];
      this.producto = {
        Anulado: 0,
        IdProductoMarca: null,
        IdProductoMedicion: null,
        IdProductoCategoria: null,
        // IdProductoModelo: null,
        IdProductoBotapie: null,
        IdProductoTalla: "",
        Producto: "",
        ProductoDesc: "",
        ProductoDescCorto: "",
        CodigoBarra: "",
        CodeBarModelo: "",
        Color: "",
        PorcentajeUtilidad: "",
        StockMinimo: 0,
        PrecioContado: "",
        Botapie: "",
        Genero: "",
        PrecioContado: 0,
        PorcentajeLiquidacion: 0,
        PrecioCosto: 0,
        EsChatarra: 0,
        categoria: {},
        marca: {},
        Imagen: null,
        Imagen2: null,
        Imagen3: null,
        precios: [
          { nombre: "PRECIO 1", cantidad: 0, precio: 0, precioc: 0 },
          { nombre: "PRECIO 2", cantidad: 0, precio: 0, precioc: 0 },
          { nombre: "PRECIO 3", cantidad: 0, precio: 0, precioc: 0 },
          { nombre: "PRECIO 4", cantidad: 0, precio: 0, precioc: 0 },
          { nombre: "OFERTA", cantidad: 0, precio: 0, precioc: 0 }
        ],
        // modelo: {},
        talla: {},
        medicion: {
          IdProductoMedicion: 6,
          ProductoMedicion: "UNIDAD"
        }
      };
    },
    async guardarProducto(e) {
      if (e && typeof e.preventDefault === "function") {
        e.preventDefault();
      }
      if (this.guardandoProducto) return;

      const nombreProducto = this.limpiarNombreProducto(
        this.producto.Producto,
        this.producto.CodigoBarra
      ).trim();
      if (!nombreProducto) {
        this.$toasted.show("Ingrese el nombre del producto.");
        return;
      }

      this.guardandoProducto = true;
      try {
        await this.prepararReferenciaProducto(
          "inputCategoria", "categorias", "IdProductoCategoria", "ProductoCategoria",
          "categoria", "la categoría", this.setCategoria
        );
        await this.prepararReferenciaProducto(
          "inputMarca", "marcas", "IdProductoMarca", "ProductoMarca",
          "marca", "la marca", this.setMarca
        );
        await this.prepararReferenciaProducto(
          "inputTalla", "tallas", "IdProductoTalla", "ProductoTalla",
          "talla", "la medida o especificación", this.setTalla
        );
        this.producto.Producto = nombreProducto.toUpperCase();
        if (!String(this.producto.CodigoBarra || "").trim()) {
          this.producto.CodigoBarra = await this.obtenerSiguienteCodigoICE();
          this.codigoBarraAutogenerado = true;
        }
        this.producto.CodigoBarra = String(this.producto.CodigoBarra || "").trim().toUpperCase();
        this.producto.CodeBarModelo = String(this.producto.CodeBarModelo || "").trim().toUpperCase();

        const editando = Boolean(this.producto.IdProducto);
        const nombreOriginal = this.limpiarNombreProducto(
          this.productoUpdate && this.productoUpdate.Producto,
          this.productoUpdate && this.productoUpdate.CodigoBarra
        );
        const codigoOriginal = String(
          this.productoUpdate && this.productoUpdate.CodigoBarra || ""
        ).trim();
        const nombreCambio = !editando ||
          this.normalizarClave(this.producto.Producto) !== this.normalizarClave(nombreOriginal);
        const codigoCambio = !editando ||
          this.normalizarClave(this.producto.CodigoBarra) !== this.normalizarClave(codigoOriginal);

        if (nombreCambio || codigoCambio) {
          await this.cargarProductosCatalogo(true);
        }
        if (nombreCambio && await this.nombreProductoExiste(this.producto.Producto)) {
          this.$toasted.error("Ya existe un producto con ese nombre. Use un nombre distinto.");
          return;
        }
        if (codigoCambio && await this.codigoBarraExiste(this.producto.CodigoBarra)) {
          this.productosCache = null;
          this.$toasted.error("Ese código ya está asignado a otro producto. Edítelo o genere el siguiente ICE.");
          return;
        }

        let preciosPresentacion = [];
        if (this.TienePresentacion) {
          const filasConfiguradas = (this.producto.precios || [])
            .map((precio, index) => ({ precio, index }))
            .filter(fila => {
              const precio = fila.precio;
              return Number(precio.cantidad) > 0 ||
                Number(precio.precio) > 0 || Number(precio.precioc) > 0;
            });
          const nombresPresentacion = new Set();
          for (const fila of filasConfiguradas) {
            const precio = fila.precio;
            let nombre = String(precio.nombre || "").trim().toUpperCase();
            const cantidad = Number(precio.cantidad);
            const costo = Number(precio.precioc);
            const precioVenta = Number(precio.precio);
            if (!Number.isFinite(cantidad) || cantidad <= 0 || !Number.isFinite(precioVenta) || precioVenta <= 0 || !Number.isFinite(costo) || costo < 0) {
              this.$toasted.error("Complete nombre, unidades y precio de venta en cada presentación iniciada.");
              return;
            }
            if (!nombre) nombre = `PRECIO ${fila.index + 1}`;
            let nombreClave = this.normalizarClave(nombre);
            if (nombresPresentacion.has(nombreClave)) {
              if (/^PRECIO(?:\s*\d+)?$/.test(nombreClave)) {
                nombre = `PRECIO ${fila.index + 1}`;
                nombreClave = this.normalizarClave(nombre);
              } else {
                this.$toasted.error("No repita el nombre de una presentación.");
                return;
              }
            }
            nombresPresentacion.add(nombreClave);
            precio.nombre = nombre;
            preciosPresentacion.push({ nombre, cantidad, precio: precioVenta, precioc: costo });
          }
          if (!preciosPresentacion.length) {
            this.$toasted.error("Agregue al menos una presentación con su cantidad y precio de venta.");
            return;
          }
        }

        this.producto.PorcentajeLiquidacion = this.producto.PorcentajeLiquidacion > 0
          ? this.producto.PorcentajeLiquidacion
          : 0;
        // MySQL en modo estricto rechaza cadenas vacías en columnas numéricas.
        const camposNumericos = [
          "PorcentajeUtilidad",
          "StockMinimo",
          "PrecioCosto",
          "PrecioContado",
          "PorcentajeLiquidacion"
        ];
        camposNumericos.forEach(campo => {
          const valor = Number(this.producto[campo]);
          this.producto[campo] = Number.isFinite(valor) ? valor : 0;
        });
        this.producto.TienePresentacion = this.TienePresentacion ? 1 : 0;
        this.producto.EsChatarra = this.valorBooleano(this.producto.EsChatarra) ? 1 : 0;
        this.producto.precios = preciosPresentacion;

        if (this.showProductoDet) {
          this.producto.productosDet = this.$refs.productoDet.productos;
        }

        const response = await this.axios.post(this.API + "productos", {
          ...this.producto,
          PreciosPorProducto: preciosPresentacion
        });
        if (response.data.error) {
          this.$toasted.show(response.data.error);
          return;
        }
        const idProducto = response.data.insertId || this.producto.IdProducto;
        await this.uploadProductImages(idProducto);
        this.productosCache = null;
        this.$emit("productoactualizado", idProducto);
        if (this.cbGuardar) this.cbGuardar(idProducto);
        this.$toasted.success("Producto guardado correctamente");
        this.$refs.productoForm.hide();
      } catch (error) {
        console.log(error);
        const respuesta = error.response && error.response.data;
        const mensaje =
          (respuesta && (respuesta.error || respuesta.message)) ||
          error.message || "No se pudo verificar o guardar el producto. Revise los datos e intente nuevamente.";
        this.$toasted.show(mensaje);
      } finally {
        this.guardandoProducto = false;
      }
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
          .post(this.API + "categorias", categoria)
          .then(response => {
            this.$toasted.success("Categoría guardada correctamente", { duration: 3500 });
            this.setCategoria({
              IdProductoCategoria: response.data.insertId,
              ProductoCategoria: response.data.ProductoCategoria
            });
          })
          .catch(error => {
            console.log(error);
            const respuesta = error.response && error.response.data;
            this.$toasted.error(
              (respuesta && (respuesta.error || respuesta.message)) ||
                "No se pudo guardar la categoría."
            );
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
            this.$toasted.success("Marca guardada correctamente", { duration: 3500 });
            this.setMarca({
              IdProductoMarca: response.data.insertId,
              ProductoMarca: response.data.ProductoMarca
            });
          })
          .catch(error => {
            console.log(error);
            const respuesta = error.response && error.response.data;
            this.$toasted.error(
              (respuesta && (respuesta.error || respuesta.message)) ||
                "No se pudo guardar la marca."
            );
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
            this.$toasted.success("Talla guardada correctamente", { duration: 3500 });
            this.setTalla({
              IdProductoTalla: response.data.insertId,
              ProductoTalla: response.data.ProductoTalla
            });
          })
          .catch(error => {
            console.log(error);
            const respuesta = error.response && error.response.data;
            this.$toasted.error(
              (respuesta && (respuesta.error || respuesta.message)) ||
                "No se pudo guardar la talla."
            );
          });
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
<style>
.product-scrap-toggle{margin:12px 0 18px;padding:14px 16px;border:1px solid #a9d8cd;border-radius:14px;background:#eaf7f3}
.product-scrap-toggle .custom-control-label{color:#083f36;cursor:pointer}
.product-scrap-toggle .custom-control-label span,.product-scrap-toggle .custom-control-label small{display:block}
.product-scrap-toggle .custom-control-label strong{font-size:.98rem;font-weight:900}
.product-scrap-toggle .custom-control-label small{margin-top:2px;color:#47726b;font-weight:700}
.product-form-optional-hidden{display:none!important}
.product-liquidation-section{margin:18px 0 8px;padding:16px 18px;border:1px solid #d7e7ef;border-radius:16px;background:#f8fbfa;box-shadow:0 5px 16px rgba(25,67,94,.05)}
.product-liquidation-section>.col-md-6:first-child{display:none}
.product-liquidation-section>.col-md-6:last-child{flex:0 0 100%;max-width:100%}
.product-liquidation-section .row{align-items:end}
.product-liquidation-section label{display:block;margin-bottom:6px;color:#31556f;font-weight:800}
.product-liquidation-section .form-control{border-color:#c9dce8;border-radius:9px;font-weight:700}
.product-liquidation-section .input-group-text{border-color:#c9dce8;background:#eaf5fb;color:#087fa5;font-weight:800}
.product-liquidation-section .row>div:last-child>div{min-height:38px;padding:9px 12px;border:1px solid #dce7ee;border-radius:9px;background:#fff;color:#087fa5;font-weight:800}
.product-barcode-scan{min-width:112px!important;border:0!important;color:#fff!important;background:#a61b3b!important;font-weight:800!important}
.product-barcode-scan span{margin-left:7px}
.product-code-generate{min-width:112px!important;border:0!important;color:#fff!important;background:#087fa5!important;font-weight:800!important}
.product-code-generate span{margin-left:6px}
.product-field-help{display:block;margin-top:6px;color:#567086;font-size:.78rem}
.product-presentations-section{margin:20px 0 12px;overflow:hidden;border:1px solid #cbded9;border-radius:18px;background:#f2f7f6;box-shadow:0 10px 28px rgba(5,72,63,.08)}
.product-presentations-heading{display:flex;align-items:center;justify-content:space-between;gap:24px;margin:0;padding:18px 20px;background:#07584e}
.product-presentations-heading__copy{min-width:0}
.product-presentations-heading h3{margin:4px 0 5px;color:#fff;font-size:1.3rem;font-weight:900}
.product-presentations-heading p{margin:0;color:#ccebe5;font-size:.86rem}
.product-presentations-eyebrow{color:#7fe2d2;font-size:.67rem;font-weight:900;letter-spacing:.14em}
.product-presentations-eyebrow i{margin-right:6px}
.product-presentations-toggle-shell{flex:0 0 auto;padding:10px 15px;border:1px solid #89c8bb;border-radius:12px;background:#fff}
.product-presentations-toggle{margin:0;color:#123e39;font-weight:900}
.product-presentations-toggle .custom-control-label{cursor:pointer}
.product-presentations-toggle .custom-control-label span,.product-presentations-toggle .custom-control-label small{display:block}
.product-presentations-toggle .custom-control-label small{margin-top:1px;color:#678079;font-size:.68rem;font-weight:700}
.product-presentations-hint{margin:18px;padding:14px 16px;border:1px solid #b8ddd5;border-radius:11px;background:#e7f5f1;color:#305f56;font-size:.9rem}
.product-presentations-hint i{margin-right:7px;color:#087565}
.product-presentations-grid{margin:0;padding:12px}
.product-presentations-grid>[class*="col-"]{padding:8px;--presentation-accent:#0f766e}
.product-presentations-grid>[class*="col-"]:nth-child(2){--presentation-accent:#4056d8}
.product-presentations-grid>[class*="col-"]:nth-child(3){--presentation-accent:#d68a00}
.product-presentations-grid>[class*="col-"]:nth-child(4){--presentation-accent:#a62e73}
.product-presentation-card{height:100%;overflow:hidden;padding:0;border:1px solid #d1dfdc;border-top:4px solid var(--presentation-accent);border-radius:14px;background:#fff;box-shadow:0 7px 18px rgba(11,65,59,.08)}
.product-presentation-card__header{display:flex;min-height:66px;align-items:center;justify-content:space-between;margin:0;padding:12px 14px;border-bottom:1px solid #e2ebe9;background:#f9fbfb;color:#264b47}
.product-presentation-card__identity{display:flex;align-items:center;gap:10px}
.product-presentation-card__number{display:grid;width:34px;height:34px;flex:0 0 34px;place-items:center;border-radius:10px;background:var(--presentation-accent);color:#fff;font-weight:900}
.product-presentation-card__title{display:flex;flex-direction:column}
.product-presentation-card__title strong{font-size:.9rem;font-weight:900}
.product-presentation-card__title small{margin-top:2px;color:#73847f;font-size:.67rem;font-weight:700}
.product-presentation-card__factor{padding:5px 9px;border:1px solid #c7d7d3;border-radius:999px;background:#eef4f2;color:var(--presentation-accent);font-size:.76rem;font-weight:900}
.product-presentation-field{margin:0!important;padding:10px 14px 0}
.product-presentation-field--name{padding-top:14px}
.product-presentation-field label{margin-bottom:5px!important;color:#526d67!important;font-size:.7rem!important;font-weight:850!important;text-transform:uppercase;letter-spacing:.04em}
.product-presentation-field label i{width:17px;color:var(--presentation-accent);text-align:center}
#app .product-form-modal .product-presentation-field .form-control{height:42px;min-height:42px!important;border-color:#bfd0cc!important;border-radius:9px!important;background:#fff!important;font-size:.86rem!important;font-weight:750!important}
.product-presentation-field .input-group-text{min-width:46px;justify-content:center;border-color:#bfd0cc;background:#edf4f2;color:#325c54;font-size:.76rem;font-weight:900}
.product-presentation-field .input-group-prepend .input-group-text{border-radius:9px 0 0 9px}
.product-presentation-field .input-group-append .input-group-text{border-radius:0 9px 9px 0}
.product-presentation-card__total{display:flex;min-height:62px;align-items:center;justify-content:space-between;margin:14px 14px 0;padding:12px 0 14px;border-top:1px solid #dde8e5;color:#647c76;font-size:.77rem}
.product-presentation-card__total>span{display:flex;flex-direction:column}
.product-presentation-card__total>span strong{color:#345b54;font-size:.78rem}
.product-presentation-card__total>span small{margin-top:2px;color:#82938f;font-size:.65rem}
.product-presentation-card__total>strong{color:var(--presentation-accent);font-size:1rem;font-weight:900}
.product-form-footer-layout{display:flex;width:100%;align-items:center;justify-content:space-between;gap:18px}
.product-form-barcode-control{display:flex;align-items:center;gap:10px}
.product-form-barcode-control>span{color:#45645e;font-size:.73rem;font-weight:900;text-transform:uppercase;letter-spacing:.04em}
.product-form-barcode-control>span i{margin-right:5px;color:#087565}
.product-form-barcode-group{width:245px}
#app .product-form-modal .product-form-barcode-group .form-control{width:64px;min-height:42px!important;text-align:center}
#app .product-form-modal .product-form-barcode-button{min-width:160px!important;background:#087565!important;background-image:none!important}
.product-form-footer-actions{display:flex;align-items:center;gap:8px}
#app .product-form-modal .product-form-footer-actions .btn{min-width:112px;height:42px;border:0;border-radius:11px;color:#fff;font-weight:900;box-shadow:0 5px 12px rgba(31,54,50,.16)}
#app .product-form-modal .product-form-footer-actions .btn i{margin-right:6px}
#app .product-form-modal .product-form-footer-actions .btn-danger{background:#d62839!important;background-image:none!important}
#app .product-form-modal .product-form-footer-actions .btn-warning{background:#d68a00!important;background-image:none!important;color:#fff!important}
#app .product-form-modal .product-form-footer-actions .btn-primary{background:#087fa5!important;background-image:none!important}
#app .product-form-modal .product-form-footer-actions .btn-secondary{background:#475569!important;background-image:none!important}
#app .product-form-modal .modal-header{background:#064c43!important;background-image:none!important}
#app .product-form-modal .product-form-body{background:#f1f6f5!important;background-image:none!important}
#app .product-form-modal .product-form-new.btn{background:#12a36f!important;background-image:none!important}
#app .product-form-modal .input-group-append .btn{background-image:none!important}
@media(max-width:900px){.product-form-footer-layout{align-items:stretch;flex-direction:column}.product-form-barcode-control{justify-content:space-between}.product-form-footer-actions{width:100%;justify-content:flex-end}}
@media(max-width:700px){.product-barcode-scan,.product-code-generate{min-width:52px!important}.product-barcode-scan span,.product-code-generate span{display:none}.product-presentations-heading{align-items:flex-start;flex-direction:column}.product-presentations-toggle-shell{width:100%}.product-presentations-grid{padding:7px}.product-form-barcode-control{align-items:flex-start;flex-direction:column}.product-form-barcode-group{width:100%}.product-form-footer-actions{display:grid;grid-template-columns:1fr 1fr}.product-form-footer-actions .btn{width:100%}}
</style>
