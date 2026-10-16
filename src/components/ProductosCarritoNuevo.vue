<template>
<b-row class="pos-cart-component">
  <b-col md="12">
    <b-row class="pos-search-toolbar">
      <b-col cols="12" lg="6" class="pos-search-column">
        <b-form-group class="pos-search-group">
          <label class="pos-product-search-label" for="txtAddProduct">Buscar producto por nombre, modelo o categoría</label>
          <div class="pos-product-search-control pos-product-name-search">
            <autocomplete
              ref="autocompleteProducto"
              :url="APIProductos"
              :debounce="200"
              :customParams="{ 'sortBy':isVentas?'stock':'IdProducto', 'sortDesc': 1 ,limit: '25' }"
              :onShouldRenderChild="renderChild"
              anchor="Producto"
              label="writer"
              placeholder="Agregar Producto"
              :on-select="setProducto"
              :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
              id="txtAddProduct"
              @keydown.native.prevent.tab="irtxtBarcode"
              :classes="{input: 'form-control txtProducto'}"
            >
            </autocomplete>
            <b-button
              type="button"
              class="pos-search-action pos-search-voice"
              :class="{ alerta: microActivo, 'pos-voice-listening': microActivo }"
              variant="info"
              @click="startRecognition"
              :title="microActivo ? 'Detener micrófono' : 'Buscar producto por voz'"
              :aria-label="microActivo ? 'Detener micrófono' : 'Buscar producto por voz'"
              :aria-pressed="microActivo"
            >
              <i class="fas fa-microphone" aria-hidden="true"></i>
            </b-button>
          </div>
        </b-form-group>
        <div class="pos-search-hint ocultarMovil">
          <i class="fas fa-search" aria-hidden="true"></i>
          Búsqueda inteligente: producto, modelo o categoría
        </div>
      </b-col>
      <b-col cols="12" lg="6" class="pos-search-column">
        <b-form-group class="pos-search-group">
          <label class="pos-product-search-label" for="txtBarcode">Buscar producto por código de barras</label>
          <b-input-group class="pos-product-search-control pos-barcode-search-control">
            <input
              id="txtBarcode"
              aria-label="Buscar producto por código de barras"
              ref="barcodeInput"
              v-model="loadCode"
              @focus="clearProductSearch"
              @keydown.prevent.tab="irtxtAddProduct"
              @keydown.enter.prevent="loadBarcode"
              type="text"
              inputmode="search"
              autocomplete="off"
              class="form-control"
              placeholder="Escanee o escriba el código de barras"
            />
            <b-input-group-append>
              <b-button
                class="pos-search-action pos-search-barcode"
                variant="success"
                @click="abrirCodigoBarra"
                title="Abrir escáner de código de barras"
                aria-label="Abrir escáner de código de barras"
              >
                <span class="pos-barcode-trigger-icon" aria-hidden="true">
                  <i class="fas fa-barcode"></i>
                </span>
              </b-button>
              <b-button
                class="pos-search-action pos-search-submit"
                variant="primary"
                @click="loadBarcode"
                title="Buscar código de barras"
                aria-label="Buscar código de barras"
              >
                <i class="fas fa-search" aria-hidden="true"></i>
              </b-button>
            </b-input-group-append>
          </b-input-group>
        </b-form-group>
      </b-col>
    </b-row>
    <producto-form
      :showButton="showButton"
      :cbGuardar="(id,pxp,obj) =>{agregarGuardar(id,pxp,obj)}"
      ref="productoFormEdit"
      :producto-update="productoSelect">
    </producto-form>
    <b-modal v-model="touchModal" size="sm" hide-footer class="ingDesc" title="Codigo Barra" hide-header-close modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
      <input readonly type="text" inputmode="none" @keypress="(e)=>{ verificar(e)}" v-model="cantidadInput" style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control"/>
      <table class="calc__numbers calc__numbers2">
            <tbody>
                <tr>
                    <td class="botones" @click="addNumber('7');">7</td>
                    <td class="botones" @click="addNumber('8');">8</td>
                    <td class="botones" @click="addNumber('9');">9</td>
                </tr>
                <tr>
                    <td class="botones" @click="addNumber('4');">4</td>
                    <td class="botones" @click="addNumber('5');">5</td>
                    <td class="botones" @click="addNumber('6');">6</td>
                </tr>
                <tr>
                    <td class="botones" @click="addNumber('1');">1</td>
                    <td class="botones" @click="addNumber('2');">2</td>
                    <td class="botones" @click="addNumber('3');">3</td>
                </tr>
                <tr>
                    <td class="botones" @click="addNumber(0);">0</td>
                    <!-- <td class="botones" @click="addPoint('.');">.</td> -->
                    <td class="botones" @click="deleteNumber();"><i class="fas fa-backspace"></i></td>
                </tr>
                <tr>
                  <td colspan="3" style="text-align: center;">
                      <b-button variant="success" @click="buscarCodigoBarra()">Buscar</b-button>
                      <b-button variant="danger" @click="touchModal=!touchModal">Cerrar</b-button>
                  </td>
                </tr>
            </tbody>
          </table>
    </b-modal>

    <b-modal size="lg" ref="modalCant" hide-footer modal-class="nst-dialog presentation-picker" body-class="nst-dialog-body" centered>
      <template slot="modal-title"><div class="presentation-product"><span class="presentation-product-icon"><i class="fas fa-box-open"></i></span><div><small>PRESENTACIONES DISPONIBLES</small><h3>{{ objProd.Producto }}</h3></div></div></template>
      <div class="presentation-hint"><span><i class="fas fa-tags"></i> ELIGE UNA PRESENTACIÓN</span><small>{{ 1 + preciosProd.filter(d => parseFloat(d.cantidad)).length }} OPCIONES</small></div>
      <div class="presentation-options">
        <button type="button" class="presentation-option" @click="setProductoUni(objProd)">
          <span class="presentation-card-top"><span><i class="fas fa-cube"></i> UNIDAD</span><small>×1</small></span>
          <span class="presentation-option-info"><strong>UNIDAD</strong></span>
          <span class="presentation-option-price"><small>PRECIO FINAL</small><strong>S/ {{ precioCont | redondear }}</strong></span>
          <span class="presentation-select">SELECCIONAR <i class="fas fa-arrow-right"></i></span>
        </button>
        <template v-for="d in preciosProd">
          <button v-if="parseFloat(d.cantidad)" :key="d.id" type="button" :class="['presentation-option', 'presentation-pack', { 'presentation-option-offer': d.nombre === 'OFERTA' }]" @click="setProductoCant(d)">
            <span class="presentation-card-top"><span><i class="fas fa-box-open"></i> PRESENTACIÓN</span><small>×{{ d.cantidad }}</small></span>
            <span class="presentation-option-info"><strong>{{ d.nombre }}</strong></span>
            <span class="presentation-option-price"><small>PRECIO FINAL</small><strong>S/ {{ d.precio | redondear }}</strong></span>
            <span class="presentation-select">SELECCIONAR <i class="fas fa-arrow-right"></i></span>
          </button>
        </template>
      </div>
    </b-modal>

    <b-modal
      id="modalCodigoBarra"
      size="lg"
      ref="modalCodigoBarra"
      hide-footer
      centered
      modal-class="pos-imceden-scanner-modal nst-dialog"
      body-class="pos-imceden-scanner-body nst-dialog-body"
      header-class="pos-imceden-scanner-header"
      @hidden="cerrarCodigoBarra" footer-class="nst-dialog-footer">
      <template slot="modal-title">
        <div class="pos-scanner-title">
          <span class="pos-scanner-title-icon" aria-hidden="true"><i class="fas fa-barcode"></i></span>
          <span>
            <small>Lector inteligente</small>
            <strong>Escanee el c&oacute;digo de barra</strong>
          </span>
        </div>
      </template>
      <div class="pos-scanner-shell">
        <div id="codigoBarraStyle" class="pos-scanner-viewport">
          <v-quagga
            v-if="modalAbiertCode"
            :onDetected="onBarcodeScanned"
            :readerSize="readerSize"
            :readerTypes="detecteds"
            class="cameraBarra"
          ></v-quagga>
          <div class="pos-scanner-guide" aria-hidden="true">
            <span class="pos-scanner-line"></span>
          </div>
          <div class="pos-scanner-instructions">
            <i class="fas fa-crosshairs" aria-hidden="true"></i>
            Acerque la etiqueta, centre todas las barras y evite reflejos.
          </div>
        </div>
        <div class="pos-scanner-manual">
          <span class="pos-scanner-manual-icon" aria-hidden="true"><i class="fas fa-keyboard"></i></span>
          <input
            v-model.trim="loadCode"
            type="text"
            inputmode="search"
            autocomplete="off"
            class="form-control"
            placeholder="Tambi&eacute;n puede escribir el c&oacute;digo"
            @keydown.enter.prevent="buscarCodigoEscaneado" />
          <b-button type="button" class="pos-scanner-search" @click="buscarCodigoEscaneado" aria-label="Buscar c&oacute;digo">
            <i class="fas fa-search" aria-hidden="true"></i>
          </b-button>
        </div>
      </div>
    </b-modal>
    <div
      class="divTBl pos-cart"
      :class="{ 'has-products': productos.length > 0 }"
      :style="{ '--cart-height': (56 + (72 * (productos.length > 5 ? 5 : productos.length))) + 'px' }">

    <div v-if="esVistaMovil" class="pos-mobile-cart" aria-label="Productos agregados">
      <div v-if="productos.length === 0" class="pos-mobile-cart-empty">
        <i class="fas fa-shopping-basket" aria-hidden="true"></i>
        <span>Agregue un producto para iniciar la venta</span>
      </div>
      <article
        v-for="(producto, productIndex) in productos"
        :key="`mobile-product-${productIndex}`"
        class="pos-mobile-product-card">
        <header class="pos-mobile-product-header">
          <div class="pos-mobile-product-main">
            <div class="pos-mobile-product-badges">
              <span class="pos-mobile-product-code"><i class="fas fa-barcode"></i> {{ producto.CodigoBarra || 'S/C' }}</span>
              <span class="pos-mobile-product-stock"><i class="fas fa-box"></i> Stock {{ producto.stock || 0 }}</span>
            </div>
            <strong class="pos-mobile-product-name">{{ producto.Producto }}</strong>
          </div>
          <b-button
            type="button"
            class="pos-mobile-product-toggle"
            :class="{ 'is-open': detalleProductoMovilVisible(producto, productIndex) }"
            :title="detalleProductoMovilVisible(producto, productIndex) ? 'Ocultar detalle del producto' : 'Mostrar detalle del producto'"
            :aria-label="detalleProductoMovilVisible(producto, productIndex) ? 'Ocultar detalle del producto' : 'Mostrar detalle del producto'"
            :aria-expanded="detalleProductoMovilVisible(producto, productIndex) ? 'true' : 'false'"
            @click="toggleDetalleProductoMovil(producto, productIndex)">
            <i :class="detalleProductoMovilVisible(producto, productIndex) ? 'fas fa-eye-slash' : 'fas fa-eye'" aria-hidden="true"></i>
          </b-button>
          <div class="pos-mobile-product-total">
            <small>Total</small>
            <strong>S/ {{ getTotal(productIndex) }}</strong>
            <button type="button" class="pos-mobile-profit" :class="{ 'is-loss': utilidadProducto(producto) < 0 }" :aria-expanded="String(!!producto.MostrarGanancia)" :aria-label="producto.MostrarGanancia ? 'Ocultar utilidad' : 'Mostrar utilidad'" :title="producto.MostrarGanancia ? 'Ocultar utilidad' : 'Mostrar utilidad'" @click="$set(producto, 'MostrarGanancia', !producto.MostrarGanancia)">
              <i :class="producto.MostrarGanancia ? 'fas fa-eye-slash' : 'fas fa-eye'" aria-hidden="true"></i>
              <span v-if="producto.MostrarGanancia">Utilidad S/ {{ utilidadProducto(producto) | redondear }}</span>
            </button>
          </div>
        </header>

        <transition name="pos-mobile-product-reveal">
        <div
          v-show="detalleProductoMovilVisible(producto, productIndex)"
          class="pos-mobile-product-details">
        <div class="pos-mobile-product-fields">
          <label class="pos-mobile-field">
            <span>Cantidad</span>
            <input
              v-model="producto.cantidad"
              :disabled="producto.estadoPxP == false || bloquearInput || esDelivery"
              type="number"
              min="0"
              step="0.001"
              class="form-control"
              @input="actualizarCantidadProducto(productIndex, $event)" />
          </label>
          <label class="pos-mobile-field">
            <span>Precio unitario</span>
            <input
              v-model="producto.precio"
              :disabled="bloquearPrecio(producto)"
              type="number"
              min="0"
              step="0.00001"
              class="form-control"
              @input="setNuevoPrecioContado(productIndex)" />
          </label>
          <label class="pos-mobile-field">
            <span>Descuento</span>
            <input
              v-model="producto.descuento"
              :disabled="bloquearInput || esDelivery"
              type="number"
              min="0"
              step="0.01"
              class="form-control"
              @input="actualizarDescu(productIndex)" />
            <small>{{ porcentajeDescuentoProducto(producto) }}% aplicado</small>
          </label>
        </div>

        <div v-if="producto.estadoPxP == false" class="pos-mobile-secondary-field">
          <label :for="`mobileCantxPrecio${productIndex}`">Cantidad por presentaci&oacute;n</label>
          <div>
            <input
              :id="`mobileCantxPrecio${productIndex}`"
              v-model="producto.cantxPrecio"
              :disabled="bloquearInput"
              type="number"
              min="0"
              step="0.001"
              class="form-control"
              @input="changeCant($event.target.value, producto)" />
            <span>x{{ producto.cantidadPres }}</span>
          </div>
        </div>

        <div
          class="pos-mobile-series-field"
          :class="{ 'is-empty': !producto.Series || producto.Series.length === 0 }">
          <label :for="`mobileSerie${productIndex}`"><i class="fas fa-fingerprint"></i> Serie del producto</label>
          <div class="pos-mobile-series-control">
            <b-form-select
              :id="`mobileSerie${productIndex}`"
              v-model="producto.IdProductoSerie"
              :options="producto.Series || []"
              :disabled="bloquearInput || producto._seriesLoading || !producto.Series || producto.Series.length === 0"
              text-field="Serie"
              value-field="IdProductoSerie">
              <option :value="null" disabled>
                {{ producto._seriesLoading ? 'Cargando series...' : ((producto.Series && producto.Series.length) ? 'Seleccione una serie' : 'Sin series disponibles') }}
              </option>
            </b-form-select>
            <b-button
              type="button"
              class="pos-mobile-series-refresh"
              :disabled="bloquearInput || producto._seriesLoading"
              title="Actualizar series"
              aria-label="Actualizar series"
              @click="recargarSeriesProducto(producto)">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': producto._seriesLoading }" aria-hidden="true"></i>
            </b-button>
          </div>
        </div>

        <div class="pos-mobile-product-footer">
          <label class="pos-mobile-description">
            <i class="fas fa-align-left" aria-hidden="true"></i>
            <span class="sr-only">Descripci&oacute;n</span>
            <b-textarea
              v-model="producto.Descripcion"
              :disabled="bloquearInput"
              rows="1"
              placeholder="Descripci&oacute;n"></b-textarea>
          </label>
          <div class="pos-mobile-product-actions">
            <b-button
              v-if="tieneVariasPresentaciones(producto)"
              class="pos-cart-tool-button is-list"
              :disabled="bloquearInput"
              title="Ver presentaciones"
              @click="showPresentacion(producto)"><i class="fas fa-list"></i></b-button>
            <b-button
              class="pos-cart-tool-button is-image"
              :disabled="bloquearInput"
              title="Ampliar imagen"
              @click="abrirImagen(producto, productoImagenUrl(producto.Imagen))"><i class="fas fa-image"></i></b-button>
            <b-button v-if="isVentas" class="pos-cart-tool-button pos-cart-info-ai is-ai" :disabled="bloquearInput" title="Información IA del producto" aria-label="Información IA del producto" v-b-tooltip.hover @click.stop="$root.$emit('ali:product-info', producto)">IA</b-button>
            <b-button
              v-if="(idPerfil == 1 && index == 0)"
              class="pos-cart-row-button is-edit"
              :disabled="bloquearInput"
              title="Editar producto"
              @click="editarProducto(producto)"><i class="fas fa-pencil-alt"></i></b-button>
            <b-button
              class="pos-cart-row-button is-delete"
              :disabled="bloquearInput"
              title="Quitar producto"
              @click="$emit('eliminarProducto', productIndex)"><i class="fas fa-trash-alt"></i></b-button>
          </div>
        </div>
        </div>
        </transition>
      </article>
    </div>

   
    <div v-else class="pos-desktop-cart">
    <b-table 
          small 
          responsive 
          id="tblProductos"
          class="pos-product-table pos-cart-table pos-desktop-cart-table"
          striped hover 
          :items="productos" 
          :fields="fields" 
        ref="tablaCarrito">
      <template slot="Producto" slot-scope="data"><span>{{data.item.Producto}}</span></template>
      <template slot="eventPres" slot-scope="data">
        <div class="pos-cart-tool-stack">
          <b-button
            v-if="tieneVariasPresentaciones(data.item)"
            class="pos-cart-tool-button is-list"
            size="sm"
            :disabled="bloquearInput?true:false"
            variant="info"
            title="Ver presentaciones"
            @click="showPresentacion(data.item)">
            <i class="fas fa-list"></i>
          </b-button>
          <b-button
            class="pos-cart-tool-button is-image"
            size="sm"
            :disabled="bloquearInput?true:false"
            variant="success"
            title="Ampliar imagen"
            @click="abrirImagen(productos[data.index],productoImagenUrl(productos[data.index].Imagen))">
            <i class="fas fa-image"></i>
          </b-button>
          <b-button v-if="isVentas" class="pos-cart-tool-button pos-cart-info-ai is-ai" :disabled="bloquearInput" title="Información IA del producto" aria-label="Información IA del producto" v-b-tooltip.hover @click.stop="$root.$emit('ali:product-info', data.item)">IA</b-button>
        </div>
      </template>

      <template slot="cantxPrecio" slot-scope="data" v-if="productos[data.index].estadoPxP == false">
        <input
          :id="`txtCantidadxPrecio${data.index}`"
          v-model="productos[data.index].cantxPrecio"
          type="number"
          style="width: 80px"
          step="0.001"
          min="0"
          :disabled="bloquearInput?true:false"
          @input="(val) => changeCant(val.target.value, productos[data.index])"
          placeholder="Cantidad"
          @keydown.prevent.enter="irtxtAddProduct"
          class="form-control" />
        <span>x{{productos[data.index].cantidadPres}}</span>
      </template>

      <template slot="cantidad" slot-scope="data">
        <input
          :id="`txtCantidad${data.index}`"
          v-model="productos[data.index].cantidad"
          :disabled="productos[data.index].estadoPxP == false || bloquearInput?true:false || esDelivery"
          type="number"
          style="width: 80px"
          class="form-control"
          step="0.001"
          @keydown.prevent.tab="irtxtBarcode"
          min="0"
          @keydown.prevent.enter="irtxtBarcode"
          @input="actualizarCantidadProducto(data.index, $event)"
          placeholder="Cantidad"/>
          <!-- <p><span class="mx-2">Prof.</span>  
        <input 
          class="form-check-input" 
          type="checkbox" 
          :disabled="bloquearInput?true:false"
          @change="(e) => {
            $emit('onCheckProf', e)
            asignarDisabledProf(data.index, e)
          }"
        />
      </p> -->
      </template>
      <template slot="precio" slot-scope="data">
        <input
          v-model="productos[data.index].precio"
          :disabled="bloquearPrecio(productos[data.index])"
          type="number"
          style="width: 90px"
          min="0"
          step="0.00001"
          class="form-control"
          placeholder="Precio"
          @input="() => { setNuevoPrecioContado(data.index);}"
          />
      </template>
      <template slot="subtotal" slot-scope="data">
        <div v-if="isVentas">
          {{ getSubtotal(data.index) }}
        </div>
        <div v-else>
          <input
          type="number"
          style="width: 105px"
          min="0"
          step="0.00001"
          placeholder="Subtotal"
          class="form-control"
          v-model="productos[data.index].subt"
          @input="val => setSubtotal(data.index, val.target.value, productos[data.index])"/>
          <b>{{ getSubtotal(data.index) }}</b>
        </div>
      </template>
      <template slot="igv" slot-scope="data">
        <b-form-checkbox
          v-model="productos[data.index].TieneIgv"
          :value="1"
          :disabled="bloquearInput?true:false"
          @input="setNuevoPrecioContado(data.index)"
          :unchecked-value="0">
          <div>{{ getIgv(data.index) }}</div>
        </b-form-checkbox>
      </template>
      <template slot="flete" slot-scope="data">
        <b-form-input
          v-model="productos[data.index].FLETE"
          type="number"
          min="0"
          placeholder="Flete"
          @input="cambiarPrecioVenta(data.index)"
          ></b-form-input>
         <b v-if="productos[data.index].FLETE && productos[data.index].cantidad!=0 "> 
          {{  
            parseFloat(productos[data.index].FLETE / productos[data.index].cantidad).toFixed(2)
          }}
        </b>
      </template>
      
      <!--<template slot="lote" slot-scope="data">
        <b-form-input
          v-model="productos[data.index].IdLote"
          type="text"
          min="0"
          placeholder="Lote"></b-form-input>
      </template>-->
      
      <template slot="manoobra" slot-scope="data">
        <input 
          @change="(e) => addManoDeObra(data.index, e)"
          v-if="!isLaborService(productos[data.index]) " 
          :disabled="bloquearInput?true:false"
          v-model="productos[data.index].EsManoDeObra"
          class="form-check-input" type="checkbox" />
      </template>

      <template slot="precioVenta" slot-scope="data">
        <input
          :disabled="bloquearInput?true:false"
          style="width:95px"
          v-model="productos[data.index].nuevoPrecioContado"
          type="number"
          min="0"
          step="0.01"
          class="form-control"
          placeholder="Precio Venta"/>
          <b-form-text>
            <strike> {{ productos[data.index].normalPrecioContado }} </strike>
          </b-form-text>
      </template>

      <template slot="utilidad" slot-scope="data">
        <input 
          :disabled="bloquearInput?true:false || (idPerfil == 1) ? false : true || esDelivery"
          class="form-control"
          v-model="productos[data.index].PorcentajeUtilidad"
          style="width: 98px"
          type="number" 
          step="0.01"
          @input="cambiarPrecio(data.index, productos[data.index])" 
          @mouseup="cambiarPrecio(data.index, productos[data.index])" 
          />
          
      </template>

      <template slot="descuento" slot-scope="data">
        <input
          :disabled="bloquearInput?true:false  || esDelivery"
          v-model="productos[data.index].descuento"
          type="number"
          min="0"
          step="0.01"
          class="form-control"
          value="0"
          style="max-width: 100px;"
          @input="actualizarDescu(data.index)"/>
          <b-form-text>
          </b-form-text>
      </template>

      <template slot="FechaAlquiler" slot-scope="data">
        <b-form-input
          v-model="productos[data.index].FechaAlquiler"
          type="date"
          placeholder="Fecha Fin"
          :value="formatFecha(data.item.FechaAlquiler)"></b-form-input>
      </template>

      <template slot="FechaAlquilerFin" slot-scope="data">
        <b-form-input
          v-model="productos[data.index].FechaAlquilerFin"
          type="date"
          placeholder="Fecha Fin"
          :value="formatFecha(data.item.FechaAlquilerFin)"></b-form-input>
      </template>

      <template slot="Descripcion" slot-scope="data">
        
        <b-textarea 
          :disabled="bloquearInput?true:false"
          class="form-control"
          placeholder="Descripción"
          cols="9" rows="3"
          style="width: 150px;field-sizing: content;"
          v-model="productos[data.index].Descripcion"></b-textarea>
        <!--<b-form-input
          v-model="productos[data.index].Descripcion"
          type="text"
          placeholder="Descripción"></b-form-input>-->
      </template>        

      <template slot="total" slot-scope="data">
        {{ getTotal(data.index) }}
        <br>
        
      </template>

      <template slot="acciones" slot-scope="data">
        <div class="pos-cart-row-actions">
          <div class="pos-cart-row-action-buttons">
            <b-button
              class="pos-cart-row-button is-delete"
              :disabled="bloquearInput?true:false"
              variant="danger"
              size="sm"
              title="Quitar producto"
              @click="$emit('eliminarProducto',data.index)">
              <i class="fas fa-times"></i>
            </b-button>
            <b-button
              v-if="(idPerfil == 1 && index==0) ? true : false"
              class="pos-cart-row-button is-edit"
              :disabled="bloquearInput?true:false"
              variant="primary"
              size="sm"
              title="Editar producto"
              @click="editarProducto(productos[data.index])">
              <i class="fas fa-pencil-alt"></i>
            </b-button>
          </div>
          <button type="button" class="pos-cart-gain" :class="{ 'is-loss': data.item.MostrarGanancia &amp;&amp; (data.item.precio - data.item.PrecioCosto) * data.item.cantidad &lt; 0 }" :aria-expanded="String(!!data.item.MostrarGanancia)" :aria-label="data.item.MostrarGanancia ? 'OCULTAR GANANCIA' : 'MOSTRAR GANANCIA'" :title="data.item.MostrarGanancia ? 'OCULTAR GANANCIA BRUTA' : 'MOSTRAR GANANCIA BRUTA'" @click="$set(data.item, 'MostrarGanancia', !data.item.MostrarGanancia)"><span class="pos-gain-label"><i :class="data.item.MostrarGanancia ? 'fas fa-eye-slash' : 'fas fa-eye'"></i></span><strong v-if="data.item.MostrarGanancia">{{ (data.item.precio - data.item.PrecioCosto) * data.item.cantidad | redondear }}</strong></button>
        </div>
      </template>

      <template slot="show_details" slot-scope="row">
         <b-button :disabled="bloquearInput?true:false" size="sm" variant="info" @click.stop="row.toggleDetails">></b-button>
      </template>
      <!-- <template slot="row-details" slot-scope="row">

        
      </template> -->

       <template slot="Series" slot-scope="data">
        <b-select
            class="pos-series-select"
            :options="data.item.Series" 
            v-model="data.item.IdProductoSerie"
            :disabled="bloquearInput"
            text-field="Serie"
            value-field="IdProductoSerie"
        >
          <option :value="null" disabled>Seleccione una serie</option>
        </b-select>
        </template>

      <template slot="stock" slot-scope="data">
        <div v-if="data.item.CodigoBarra=='COSTODELIVERY'">
            <img src="static/moto-delivery.png" style="width: 24px" />
        </div>
        <div v-else>
          <img v-if="data.item.EsPadre" src="static/box.png" style="width: 24px" />
          <div v-if="!data.item.EsPadre">{{ data.item.stock }}</div>
        </div>
      </template>
    </b-table>
    </div>
     </div>
  </b-col>
  <b-col md="3" v-if="!isVentas">
    <slot name="operaciones"></slot>
  </b-col>
  <b-col md="4" v-if="!isVentas">
    <slot name="metodopago"></slot>
  </b-col>
  <b-col md="2" v-if="!isVentas">
    <slot name="adicional"></slot>
  </b-col>
  <b-col md="3" v-if="!isVentas">
        <table class="table b-table border table-footer">
          <tbody>
            <tr v-if="sumatoriaDescuento > 0">
              
              <td class="table-success">SubTotal</td>
              <td class="table-success">{{ parseFloat(sumatoriaSubTotal) + parseFloat(sumatoriaDescuento) | redondear }}</td>
            </tr>
            <tr v-if="sumatoriaDescuento > 0">
              <td class="table-danger">Descuento</td>
              <td class="table-danger">- {{ sumatoriaDescuento  }}</td>
            </tr>
            <tr>
              <td><b>SubTotal</b></td>
              <td class="table-danger">{{ sumatoriaSubTotal  }}</td>
            </tr>
            <tr v-if="productos.some(v=>v.FLETE >0)">
              <td><b>Total Flete</b></td>
              <td class="table-danger">{{ productos.reduce((sum,producto)=>{
                return sum + parseFloat(producto.FLETE? producto.FLETE: 0)
              },0)  }}</td>
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
                  :disabled="bloquearInput?true:false"
                  placeholder="Pagó con"
                  v-model="pagocon"
                  @input="asignarVuelto"></b-form-input><br>
                <label>Vuelto:</label>
                <b>S/  {{ vuelto | redondear }}</b>
              </td>
            </tr>
          </tbody>
        </table>
  </b-col>
  <template v-if="!isVentas"><br><br></template>
     <b-modal
       size="lg"
       v-model="modalImagen"
       hide-footer
       centered
       :title="productoNombre"
       modal-class="pos-product-image-modal nst-dialog"
       body-class="pos-product-image-modal__body nst-dialog-body" footer-class="nst-dialog-footer">
      <div class="pos-product-detail">
        <div class="pos-product-gallery">
          <div class="pos-product-image-stage">
            <b-img :src="productoGaleria[productoVista] || productoUrl" :alt="productoNombre" class="imagenGrande" @error="setAltImg"></b-img>
            <button v-if="productoGaleria.length > 1" class="pos-gallery-prev" aria-label="Imagen anterior" @click="productoVista = (productoVista + productoGaleria.length - 1) % productoGaleria.length">&#8249;</button>
            <button v-if="productoGaleria.length > 1" class="pos-gallery-next" aria-label="Imagen siguiente" @click="productoVista = (productoVista + 1) % productoGaleria.length">&#8250;</button>
          </div>
          <div class="pos-gallery-thumbs"><button v-for="(foto,i) in productoGaleria" :key="foto" :class="{selected:i===productoVista}" :aria-label="'Ver imagen '+(i+1)" @click="productoVista=i"><img :src="foto" @error="setAltImg" alt="Vista del producto"></button></div>
        </div>
        <div class="pos-product-information">
          <small>{{ productoDetalle.categoria || productoDetalle.ProductoCategoria }} · {{ productoDetalle.marca || productoDetalle.ProductoMarca }}</small>
          <h2>{{ productoNombre }}</h2>
          <strong class="pos-detail-price">S/ {{ Number(productoDetalle.PrecioContado || 0).toFixed(2) }}</strong>
          <p class="pos-detail-description">{{ productoDetalle.descripcion_detallada || productoDetalle.ProductoDesc || productoDetalle.descripcion || productoDetalle.ProductoDescCorto || 'Sin descripción adicional.' }}</p>
          <div class="pos-detail-attributes"><div><small>CÓDIGO</small><b>{{ productoDetalle.CodigoBarra || productoDetalle.codigo || productoDetalle.IdProducto }}</b></div><div><small>MODELO</small><b>{{ productoDetalle.modelo || productoDetalle.ProductoModelo || '—' }}</b></div></div>
          <button type="button" class="pos-ali-learn" @click="modalImagen=false; $root.$emit('ali:product-info', productoDetalle)">Aprender con ALI · Información y videos</button>
          <p class="pos-detail-stock">Stock en tienda: {{ productoDetalle.stock != null ? productoDetalle.stock : 'Consultar' }}</p>
        </div>
      </div>
    </b-modal>

</b-row>
</template>

<script>
import {isLaborService,laborSummary} from '@/utils/laborProducts';
import { mapState, mapActions } from "vuex";
import ProductoForm from "./ProductoForm";
import config from "@/store/config.js";
import sound from "../../static/beep.mp3";
import Vue from "vue";
import VueQuagga from "vue-quaggajs";
import debounce from 'lodash';

Vue.use(VueQuagga);

export default {
  mounted() {
    window.addEventListener("resize", this.actualizarVistaResponsiva, { passive: true });
    this.$nextTick(() => {
      setTimeout(() => {
        this.recalcularManoObra();
        this.focusBarcode();
      }, 1000);
    });
    // this.alterarBuscadorProductos()
  },

  data() {
    return {
      modalImagen: false,
      productoDetalle: {},
      productoGaleria: [],
      productoVista: 0,
      productoNombre: "",
      productoUrl: "",
      cantidadInput: "",
      touchModal: false,
      microActivo: false,
      microActivoBarCode: false,
      vuelto: 0,
      readerSize: {
        width: 1280,
        height: 720
      },
      detecteds: [
        "ean_reader",
        "ean_8_reader",
        "code_128_reader",
        "upc_reader",
        "upc_e_reader",
        "code_39_reader",
        "codabar_reader"
      ],
      result: "",
      format: "",
      API: config.API,
      loadCode: "",
      pasteCode: false,
      producto: {},
      items: [],
      handleKeyDown: false,
      vuelto: 0,
      pagocon: 0,
      cliente: {},
      preciosProd: [],
      objProd: {},
      cant: 0,
      precioCont: 0,
      medicionPro: "UNIDAD",
      productoSelect: {},
      idPerfil: sessionStorage.getItem("IdPerfil"),
      modalAbiertCode: false,
      scanProcessing: false,
      esVistaMovil: typeof window !== "undefined" && window.innerWidth <= 767,
      resizeFrame: null,
      colaCodigosBarra: [],
      procesandoCodigoBarra: false,
      ultimaEntradaCodigo: {
        codigo: "",
        origen: "",
        tiempo: 0
      },
      detallesProductosMovil: {},
      queryCodeBar: "",
      query: ""
    };
  },
  created() {
    // Acepta lectores USB/Bluetooth con Enter, Tab o sin tecla final.
    this.$barcodeScanner.init(this.onBarcodeScanned, {
      sensitivity: 120,
      callbackAfterTimeout: true
    });
  },
  destroyed() {
    if (this._productRecognition) {
      const recognition = this._productRecognition;
      this._productRecognition = null;
      recognition.onresult = recognition.onend = recognition.onerror = null;
      try { recognition.abort(); } catch (error) {}
    }
    // Remove listener when component is destroyed
    this.$barcodeScanner.destroy();
    window.removeEventListener("resize", this.actualizarVistaResponsiva);
    if (this.resizeFrame) window.cancelAnimationFrame(this.resizeFrame);
  },
  watch: {
    gravadaflag() {
      console.log("gravadaflag", this.gravadaflag);
    },
    igvflag() {
      this.productos.forEach((element, index) => {
        Vue.set(this.productos[index], "TieneIgv", this.igvflag);
        // this.productos[index] = element;
        this.setNuevoPrecioContado(index);
        // console.log("igvflag", this.igvflag);
      });
    },
    sumatoriaTotal(sum) {
      this.asignarVuelto();
    },
    productos(){this.recalcularManoObra();},
    laborRecalcKey(){this.recalcularManoObra();}
  },
  props: {
    index: { typeof: Number, default: 0 },
    productos: Array,
    gravadaflag: Number,
    igvflag: Number,
    isVentas: Boolean,
    tipo: String,
    setPrecio: Boolean,
    tipoCambio: Number,
    igvTotal: Boolean,
    idAlmacen: Number,
    bloquearInput: Boolean,
    esDelivery: Boolean,
    onSetProducto: {
      type: Function,
      default(obj) {
        return obj;
      }
    },
    showButton: {
      default: true,
      type: Boolean
    },
    fields: {
      default() {
        return [
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center"
          },
          { key: "CodigoBarra", label: "Código" },
          // { key: 'ProductoCategoria', label: 'Categoria'},
          { key: "Producto", label: "Artículo" },
          { key: "Descripcion", label: "Descripción" },
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoModelo", label: "Modelo" },
          // { key: 'ProductoPresentacion', label: 'Present.' },
          // { key: 'ProductoMedicion', label: 'Medición' },
          { key: "cantidad" },
          { key: "precio", label: "P/U" },
          { key: "subtotal", label: "SubTotal", variant: "success" },
          { key: "igv", label: "IGV" },
          // { key: 'flete', label: 'FLETE' },
          // { key: 'lote', label: 'Lote' },
          { key: "precioVenta", label: "Precio venta (S/)" },
          { key: "total", label: "Total", variant: "danger" },
          { key: "acciones", label: "" }
        ];
      },
      type: Array
    }
  },
  computed: {
    laborRecalcKey(){return JSON.stringify(this.productos.map(p=>[p.IdProducto,p.EsServicioManoObra,p.CodigoBarra,p.EsManoDeObra,p.cantidad,p.precioBruta,isLaborService(p)?null:p.precio]))},
    ...mapState({
      // productos: state => state.cart.productos,
      filtrados: state => state.cart.productosFiltrados
    }),
    APIProductos() {
      if (this.idAlmacen) {
        return this.API + "productos/stock/" + this.idAlmacen;
      } else {
        return this.API + "productos";
      }
    },
    sumatoriaSubTotal() {
      let sumatoria = 0;
      if (this.igvTotal) {
        // sumatoria = this.sumatoriaTotal - this.sumatoriaIgv
        sumatoria = this.sumatoriaTotal / 1.18;
      } else {
        this.productos.map(value => {
          sumatoria += parseFloat(value.subtotal);
          if (value.descuento) {
            sumatoria -= value.descuento;
          }
        });
      }
      return this.$options.filters.redondear(sumatoria);
    },
    sumatoriaTotal() {
      let sumatoria = 0;
      this.productos.map(value => {
        sumatoria += parseFloat(value.total);
      });

      this.actualizarTotal(sumatoria);
      return this.$options.filters.redondear(sumatoria);
    },
    sumatoriaIgv() {
      let sumatoria = 0;
      if (this.igvTotal || this.gravadaflag) {
        sumatoria = this.sumatoriaTotal;
        //sumatoria = sumatoria * 0.18
        sumatoria = sumatoria - sumatoria / 1.18;
      } else {
        this.productos.map(value => {
          sumatoria += parseFloat(value.igv);
        });
      }
      return this.$options.filters.redondear(sumatoria);
    },
    sumatoriaDescuento() {
      let sumatoria = 0;
      this.productos.map(value => {
        sumatoria += parseFloat(value.descuento || 0);
      });
      return this.$options.filters.redondear(sumatoria);
    }
  },
  methods: {
    isLaborService,
    ...mapActions({
      add: "agregarProducto",
      buscar: "buscarProductos",
      eliminar: "eliminarProducto",
      actualizarTotal: "actualizarTotalCart",
      actualizarProd: "actualizarProducto",
      obtenerStok: "obtenerStock"
    }),
    actualizarVistaResponsiva() {
      if (this.resizeFrame) return;
      this.resizeFrame = window.requestAnimationFrame(() => {
        this.esVistaMovil = window.innerWidth <= 767;
        this.resizeFrame = null;
      });
    },
    clearProductSearch() {
      this.query = "";
      const autocomplete = this.$refs.autocompleteProducto;
      if (!autocomplete) return;
      clearTimeout(autocomplete.debounceTask);
      autocomplete.setValue("");
      autocomplete.showList = false;
      autocomplete.json = [];
    },
    focusBarcode(selectContent = false) {
      this.clearProductSearch();
      this.$nextTick(() => {
        const input = this.$refs.barcodeInput;
        if (!input || input.disabled || input.offsetParent === null) return;
        input.focus();
        if (selectContent && typeof input.select === "function") {
          input.select();
        }
      });
    },
    abrirImagen(producto, url) {
      const item = typeof producto === 'object' ? producto : {Producto: producto};
      this.productoNombre = item.Producto;
      this.productoDetalle = Object.assign({}, item);
      this.productoUrl = url;
      this.productoVista = 0;
      this.productoGaleria = [url];
      this.modalImagen = true;
      const id = item.IdProducto;
      if (id) fetch('/api/tienda/index.php?id=' + encodeURIComponent(id)).then(r=>r.json()).then(rows=>{
        if (!this.modalImagen || this.productoDetalle.IdProducto !== id) return;
        const detail = Array.isArray(rows) && rows.find(row=>String(row.id)===String(id));
        if (!detail) return;
        this.productoDetalle = Object.assign({}, detail, item);
        const photos = [detail.imagen, detail.imagen2, detail.imagen3].filter(Boolean);
        this.productoGaleria = photos.length ? photos : [url];
      }).catch(()=>{});
    },
    bloquearPrecio(data) {
      if (data.disabled) {
        return true;
      }
      if (this.bloquearInput) {
        return true;
      }
      if (this.esDelivery) {
        return true;
      }

      if (this.isVentas && data.EsPadre) {
        return true;
      }
      return false;
    },
    actualizarCantidadProducto(index, eventOrValue) {
      const value = eventOrValue && eventOrValue.target
        ? eventOrValue.target.value
        : eventOrValue;
      const producto = this.productos[index];
      if(producto&&isLaborService(producto)){this.$nextTick(()=>this.recalcularManoObra());return;}

      this.$emit("onInputCantidad", value);
      if (!producto) return;

      if (this.isVentas) {
        if (!producto.PreciosPorProducto) {
          producto.precio = producto.StockPorMayor && value >= producto.StockPorMayor
            ? producto.PrecioPorMayor
            : (this.tipo === "mov" ? producto.PrecioCosto : producto.PrecioContado);
          return;
        }

        let precios = [];
        try {
          precios = JSON.parse(producto.PreciosPorProducto || "[]");
        } catch (error) {
          precios = [];
        }
        const registro = precios.find(item => item.cantidad === producto.cantidadPres);
        producto.precio = registro && registro.cantidad
          ? registro.precio
          : producto.PrecioContado;
        return;
      }

      this.cambiarPrecioVenta(index);
    },
    porcentajeDescuentoProducto(producto) {
      const base = parseFloat(producto.subtotal) ||
        (parseFloat(producto.precio) || 0) * (parseFloat(producto.cantidad) || 0);
      const descuento = parseFloat(producto.descuento) || 0;
      return base > 0 ? ((descuento / base) * 100).toFixed(2) : "0.00";
    },
    claveDetalleProductoMovil(producto, index) {
      return String(producto.IdProducto || producto.CodigoBarra || index);
    },
    detalleProductoMovilVisible(producto, index) {
      const clave = this.claveDetalleProductoMovil(producto, index);
      return this.detallesProductosMovil[clave] === true;
    },
    toggleDetalleProductoMovil(producto, index) {
      const clave = this.claveDetalleProductoMovil(producto, index);
      Vue.set(
        this.detallesProductosMovil,
        clave,
        !this.detalleProductoMovilVisible(producto, index)
      );
    },
    abrirModal() {
      this.touchModal = true;
    },
    cambiarPrecioVenta(index) {
      let producto = { ...this.productos[index] };
      let flete = producto.FLETE
        ? parseFloat(
            producto.FLETE / (producto.cantidad ? producto.cantidad : 1)
          )
        : 0;
      let precio = parseFloat(producto.precio);

      if (producto.TieneIgv) {
        precio += precio * 0.18;
      }
      let precioFinal =
        (producto.precio * producto.PorcentajeUtilidad) / 100 + precio + flete;

      precioFinal = precioFinal * this.tipoCambio;
      Vue.set(
        this.productos[index],
        "nuevoPrecioContado",
        parseFloat(precioFinal).toFixed(2)
      );
      Vue.set(
        this.productos[index],
        "normalPrecioContado",
        parseFloat(precioFinal).toFixed(2)
      );
    },
    startRecognition() {
      if (this._productRecognition) { this._productRecognition.stop(); return; }
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!SpeechRecognition) { this.$toasted.error("Tu navegador no soporta la búsqueda por voz."); return; }
      const recognition = new SpeechRecognition();
      this._productRecognition = recognition;
      recognition.lang = "es-PE";
      recognition.continuous = false;
      recognition.interimResults = true;
      let latest = "", searched = "";
      const reset = () => {
        if (this._productRecognition === recognition) {
          this._productRecognition = null;
          this.microActivo = false;
        }
      };
      const searchLatest = () => {
        if (latest && latest !== searched) { searched = latest; this.query = latest; this.search(); }
      };
      recognition.onstart = () => {
        if (this._productRecognition === recognition) this.microActivo = true;
      };
      recognition.onresult = event => {
        if (this._productRecognition !== recognition) return;
        const parts = [];
        for (let i = 0; i < event.results.length; i++) {
          if (event.results[i][0]) parts.push(event.results[i][0].transcript);
        }
        const text = parts.join(" ").replace(/\s+/g, " ").trim();
        if (!text) return;
        latest = text; this.query = text;
        const autocomplete = this.$refs.autocompleteProducto;
        if (autocomplete) autocomplete.setValue(text);
        const last = event.results[event.results.length - 1];
        if (last.isFinal !== false) searchLatest();
      };
      recognition.onerror = event => {
        reset();
        if (event.error !== "aborted" && event.error !== "no-speech") this.$toasted.error("No se pudo usar el micrófono. Revisa sus permisos.");
      };
      recognition.onend = () => {
        if (this._productRecognition !== recognition) return;
        searchLatest(); reset();
      };
      try { recognition.start(); } catch (error) { reset(); this.$toasted.error("No se pudo iniciar el micrófono. Intenta nuevamente."); }
    },
    startRecognitionCodeBar() {
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }
      this.microActivoBarCode = true;
      this.recognition = new webkitSpeechRecognition();
      this.recognition.lang = "es-ES";
      this.recognition.continuous = false;
      this.recognition.interimResults = false;

      this.recognition.onstart = () => {
        console.log("Speech recognition service has started");
      };

      this.recognition.onresult = event => {
        this.microActivoBarCode = false;
        if (event.results.length > 0) {
          this.queryCodeBar = event.results[0][0].transcript;
          this.searchCodeBar();
        }
      };

      this.recognition.onerror = event => {
        this.microActivoBarCode = false;
        console.error("Speech Recognition Error:", event);
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = async () => {
        this.microActivoBarCode = false;
        if (!this.queryCodeBar) {
          alert("No se pudo captar ninguna palabra");
        }
      };

      try {
        this.recognition.start();
      } catch (error) {
        console.error("Error starting recognition:", error);
        alert("No se pudo iniciar el reconocimiento de voz.");
      }
    },
    search(rows) {
      const autocomplete = this.$refs.autocompleteProducto;
      if (!autocomplete) return;
      const text = this.query;
      const suppliedRows = Array.isArray(rows);
      if (suppliedRows) {
        clearTimeout(autocomplete.debounceTask);
        autocomplete.json = rows;
        autocomplete.showList = true;
        autocomplete.focusList = 0;
      }
      autocomplete.setValue(text);
      this.$nextTick(() => {
        const input = autocomplete.$el.querySelector("input");
        if (!input) return;
        this._voiceSearchFocus = true;
        try { input.focus(); } finally { this._voiceSearchFocus = false; }
        // Focus callbacks must not reset the autocomplete's reactive model.
        autocomplete.setValue(text);
        input.value = text;
        if (!suppliedRows) autocomplete.handleInput({ target: input });
      });
    },
    searchCodeBar() {
      // console.log("Búsqueda:", this.query);
      // const inputElement = this.$refs["autocompleteProducto"].$el.querySelector(
      //   "input"
      // );
      let inputElement = this.$refs.barcodeInput;
      if (!inputElement) return;
      inputElement.focus();

      inputElement.value = this.queryCodeBar;
      inputElement.dispatchEvent(new Event("input", { bubbles: true }));
      this.$nextTick(() => this.loadBarcode());
    },
    irtxtBarcode(event) {
      if (event) {
        event.preventDefault();
      }
      this.focusBarcode(true);
    },
    irtxtAddProduct(event) {
      if (event) {
        event.preventDefault();
      }
      document.getElementById("txtAddProduct").focus();
    },
    setSubtotal(index, val, prod) {
      let precio = this.$options.filters.redondear(
        val / this.productos[index].cantidad,
        4
      );
      Vue.set(this.productos[index], "precio", precio);
    },
    actualizarPorcentajeUtilidad(index) {
      this.productos[
        index
      ].PorcentajeUtilidad = this.$options.filters.redondear(
        ((this.productos[index].precio - this.productos[index].PrecioCosto) /
          this.productos[index].PrecioCosto) *
          100,
        2
      );
    },
    productoImagenUrl(value) {
      const image = String(value || '').trim().replace(/\\/g, '/');
      if (!image) return '/static/no-photo.png';
      if (/^https?:\/\//i.test(image)) return image;
      const relative = image.replace(/^\/+/, '');
      const path = relative.indexOf('resources/images/') === 0 ? relative : 'resources/images/productos/' + relative;
      return '/' + path.split('/').map(part=>encodeURIComponent(decodeURIComponent(part))).join('/');
    },
    setAltImg(event) {
      if (!event.target.src.endsWith('/static/no-photo.png')) event.target.src = '/static/no-photo.png';
    },
    changeCant(val, prod) {
      if (val > 0) {
        prod.cantidad = prod.cantidadPres;
        prod.cantidad *= val;
      }
      this.$nextTick(() => {
        this.actualizarProd(prod);
      });
    },

    abrirCodigoBarra() {
      this.modalAbiertCode = true;
      this.scanProcessing = false;
      this.result = "";
      this.format = "";
      this.$refs["modalCodigoBarra"].show();
    },
    onBarcodeScanned(barcode) {
      const codeResult = barcode && barcode.codeResult ? barcode.codeResult : null;
      const scannedCode = typeof barcode === "string"
        ? barcode.trim()
        : (codeResult && codeResult.code ? String(codeResult.code).trim() : "");

      if (!scannedCode) return;

      // Los lectores físicos funcionan en toda la pantalla de venta, sin abrir
      // la cámara. La cola evita perder lecturas aunque el servidor esté ocupado.
      if (!this.modalAbiertCode) {
        this.encolarCodigoBarra(scannedCode, "lector");
        return;
      }

      if (this.scanProcessing) return;

      this.scanProcessing = true;
      this.result = scannedCode;
      this.format = codeResult && codeResult.format ? codeResult.format : "scanner";

      const audio = new Audio(sound);
      const playback = audio.play();
      if (playback && typeof playback.catch === "function") playback.catch(() => {});

      this.$refs["modalCodigoBarra"].hide();
      this.encolarCodigoBarra(scannedCode, "camara");
    },
    buscarCodigoEscaneado() {
      const code = String(this.loadCode || "").trim();
      if (!code || this.scanProcessing) return;
      this.scanProcessing = true;
      this.$refs["modalCodigoBarra"].hide();
      this.encolarCodigoBarra(code, "camara-manual");
    },
    cerrarCodigoBarra() {
      this.modalAbiertCode = false;
      this.scanProcessing = false;
    },

    cambiarPrecio(index, prod) {
      var utilidad =
        this.productos[index].PrecioCosto *
        (this.productos[index].PorcentajeUtilidad / 100);
      this.productos[index].precio = this.$options.filters.redondear(
        this.productos[index].PrecioCosto + utilidad,
        2
      );
    },
    recalcularManoObra(){
      if(!this.productos.some(isLaborService)){this.productos.forEach(p=>{if(p.EsManoDeObra){this.$set(p,'EsManoDeObra',0);this.$set(p,'disabled',false)}});return;}
      this.productos.forEach(p=>{if(isLaborService(p)){if(p.precioBruta===undefined||p.precioBruta===null)this.$set(p,'precioBruta',Number(p.precio)||0);if(p.EsManoDeObra)this.$set(p,'EsManoDeObra',0);}else this.$set(p,'disabled',p.EsManoDeObra===true||Number(p.EsManoDeObra)===1)});
      const summary=laborSummary(this.productos);summary.rows.forEach(row=>{const p=this.productos[row.index];if(Number(p.precio)!==row.unitNet)this.$set(p,'precio',row.unitNet)});
    },
    addManoDeObra(index){if(!this.productos.some(isLaborService)){this.$set(this.productos[index],'EsManoDeObra',0);this.$toasted.error('Primero selecciona un servicio configurado como mano de obra.');return;}this.recalcularManoObra();if(laborSummary(this.productos).overBudget)this.$toasted.error('Los componentes superan la mano de obra bruta. Revisa cantidades e importes.');},
    updateManoDeObraNeta(index){if(isLaborService(this.productos[index]))this.$set(this.productos[index],'precioBruta',Number(this.productos[index].precio)||0);this.recalcularManoObra();},

    asignarDisabledProf(index, e) {
      if (e.target.checked === true) {
        this.productos[index].disabledProf = 1;
        return;
      }
      this.productos[index].disabledProf = 0;
    },

    asignarVuelto() {
      this.vuelto = this.pagocon - this.sumatoriaTotal;
    },
    // loadBarcode (e) {
    //   e.preventDefault()
    //   let idProducto = this.loadCode;

    //   if (idProducto) {
    //     this.add([idProducto, (obj) => {
    //       if (obj) {
    //         this.$emit('onAddProducto')
    //         this.$toasted.show('Producto añadido correctamente')
    //       } else {
    //         this.$toasted.show('No se encontró ningun producto')
    //       }
    //       this.loadCode = ''
    //     }, { setPrecio: this.setPrecio,  setSuma: true }, true])
    //   }
    // },

    loadBarcode(e) {
      if (e) {
        e.preventDefault();
      }
      const codigo = String(this.loadCode || "").trim();
      this.loadCode = "";
      this.focusBarcode();
      if (codigo) this.encolarCodigoBarra(codigo, "campo");
    },
    encolarCodigoBarra(codigo, origen = "campo") {
      const codigoNormalizado = String(codigo || "").trim();
      if (!codigoNormalizado) return;

      const ahora = Date.now();
      const ultima = this.ultimaEntradaCodigo;
      const esEventoDuplicado = ultima.codigo === codigoNormalizado &&
        ultima.origen !== origen && ahora - ultima.tiempo < 300;

      // Un lector con Enter dispara tanto el campo como el listener global.
      // Se descarta sólo ese evento gemelo; dos escaneos reales sí se suman.
      if (esEventoDuplicado) return;

      this.ultimaEntradaCodigo = {
        codigo: codigoNormalizado,
        origen,
        tiempo: ahora
      };
      this.colaCodigosBarra.push(codigoNormalizado);
      this.procesarColaCodigosBarra();
    },
    async procesarColaCodigosBarra() {
      if (this.procesandoCodigoBarra) return;
      this.procesandoCodigoBarra = true;

      try {
        while (this.colaCodigosBarra.length) {
          const codigo = this.colaCodigosBarra.shift();
          try {
            await this.procesarCodigoBarra(codigo);
          } catch (error) {
            this.$toasted.error("No se pudo consultar el producto");
          }
        }
      } finally {
        this.procesandoCodigoBarra = false;
        this.focusBarcode();
      }
    },
    async procesarCodigoBarra(codigo) {
      this.clearProductSearch();
      const codigoNormalizado = String(codigo).trim();
      let indice = this.productos.findIndex(producto => {
        return String(producto.CodigoBarra || "").trim() === codigoNormalizado ||
          String(producto.IdProducto || "").trim() === codigoNormalizado;
      });

      // La lectura repetida se resuelve completamente en memoria.
      if (indice >= 0) {
        const producto = this.productos[indice];
        const nuevaCantidad = (parseFloat(producto.cantidad) || 0) + 1;
        Vue.set(producto, "cantidad", nuevaCantidad);
        this.actualizarCantidadProducto(indice, nuevaCantidad);
        this.actualizarProd(producto);

        if (!producto.Series || !producto.Series.length) {
          this.recargarSeriesProducto(producto).catch(() => {});
        }
        return true;
      }

      const response = await this.axios.get(
        this.API + "productos/id/" + encodeURIComponent(codigoNormalizado),
        { params: { codigoBarra: codigoNormalizado } }
      );
      const prod = response && response.data ? response.data : {};
      if (!prod.IdProducto) {
        this.$toasted.show("No se encontró ningún producto");
        return false;
      }

      // Una segunda lectura pudo entrar mientras respondía la primera.
      indice = this.productos.findIndex(p => p.IdProducto == prod.IdProducto);
      if (indice >= 0) {
        const producto = this.productos[indice];
        const nuevaCantidad = (parseFloat(producto.cantidad) || 0) + 1;
        Vue.set(producto, "cantidad", nuevaCantidad);
        this.actualizarCantidadProducto(indice, nuevaCantidad);
        this.actualizarProd(producto);
        return true;
      }

      const [series, stockProducto] = await Promise.all([
        this.obtenerSeriePorProducto(prod.IdProducto).catch(() => []),
        this.obtenerStockProducto(prod.IdProducto)
      ]);

      prod.Series = series;
      if (typeof prod.IdProductoSerie === "undefined" || prod.IdProductoSerie === "") {
        prod.IdProductoSerie = null;
      }
      this.$emit("setProducto", {
        ...prod,
        stock: stockProducto && typeof stockProducto.stock !== "undefined"
          ? stockProducto.stock
          : (prod.stock || 0)
      });
      return true;
    },
    async obtenerStockProducto(idProducto) {
      if (!this.idAlmacen) return {};
      try {
        const response = await this.axios(
          `${this.API}/productos/stock/${this.idAlmacen}`,
          { params: { idProductos: [idProducto] } }
        );
        return Array.isArray(response.data) && response.data[0]
          ? response.data[0]
          : {};
      } catch (error) {
        return {};
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
    focusAutocomplete(ref) {
      const autocomplete = this.$refs[ref];
      if (!autocomplete || this._voiceSearchFocus) return;
      const text = String(autocomplete.type || "").trim();
      if (text) autocomplete.handleInput({ target: { value: text } });
      else autocomplete.handleDoubleClick();
    },
    editarProducto(Pro) {
      this.productoSelect = Pro;
      this.$refs.productoFormEdit.show = true;
      this.$refs.productoFormEdit.nuevo = false;
    },
    agregarGuardar(id, pxp, obj) {
      // this.add([id, false, false, false, false, true, this.isVentas]);
      // setTimeout(() => {
      //   this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
      // }, 200);

      // if (this.isVentas) {
      let fPP = pxp.filter(p => p.cantidad === 0 || p.cantidad === "0");
      if (fPP.length != 4) {
        obj.ProductoMedicion = obj.medicion.ProductoMedicion;
        obj.IdProductoMedicion = obj.medicion.IdProductoMedicion;
        obj.IdProductoMarca = obj.marca.IdProductoMarca;
        obj.ProductoMarca = obj.marca.ProductoMarca;
        obj.IdProductoCategoria = obj.categoria.IdProductoCategoria;
        obj.ProductoCategoria = obj.categoria.ProductoCategoria;
        this.productoSelect = obj;
        this.productoSelect.PreciosPorProducto = JSON.stringify(pxp);
        this.showPresentacion(this.productoSelect);
        return;
        // }
      }
      this.setProducto(obj);
    },

    async setProducto(obj) {
      this.precioCont = obj.PrecioContado;
      obj.estadoPxP = false;
      this.loadCode = "";
      this.preciosProd = [];
      this.objProd = {};
      obj.PrecioContado = parseFloat(obj.PrecioContado).toFixed(1);
      let productoSerie = await this.obtenerSeriePorProducto(obj.IdProducto);
      obj.Series = productoSerie;
      if (typeof obj.IdProductoSerie === "undefined" || obj.IdProductoSerie === "") {
        obj.IdProductoSerie = null;
      }

      // if (!this.isVentas) {
      //   obj.PreciosPorProducto = "";
      // }
      obj.Descripcion = null;

      this.preciosProd = JSON.parse(obj.PreciosPorProducto || "[]");
      let fPP = this.preciosProd.filter(p => p.cantidad === 0);

      if (obj.TienePresentacion == 0) {
        console.log("ssssssssssss");
        obj.estadoPxP = true;
        this.setProductoUni(obj);
        return;
      }

      this.$refs["modalCant"].show();
      // this.preciosProd = JSON.parse(obj.PreciosPorProducto)
      this.objProd = obj;
    },
    async obtenerSeriePorProducto(id) {
      const params = {
        IdProducto: id,
        Todos: false,
        empresa: sessionStorage.getItem("empresa")
      };
      const obtenerSeries = requestParams => this.axios(
        `${this.API}/productos/serie`,
        { params: requestParams }
      ).then(res => Array.isArray(res.data) ? res.data : []).catch(() => []);

      if (!this.idAlmacen) return obtenerSeries(params);

      // Ambas consultas viajan a la vez. Esto evita una segunda espera cuando
      // la serie nueva todavía no tiene movimiento de almacén asociado.
      const [seriesAlmacen, seriesGenerales] = await Promise.all([
        obtenerSeries({ ...params, IdAlmacen: this.idAlmacen }),
        obtenerSeries(params)
      ]);

      if (seriesAlmacen.length) return seriesAlmacen;

      /*
       * Las series recién registradas todavía pueden no tener IdMovimiento.
       * La API las excluye al filtrar por almacén; recuperamos únicamente esas
       * series sin movimiento, evitando ofrecer series de otro almacén.
       */
      return seriesGenerales.filter(serie => !serie.IdMovimiento);
    },
    async recargarSeriesProducto(producto) {
      if (!producto || !producto.IdProducto || producto._seriesLoading) return;

      Vue.set(producto, "_seriesLoading", true);
      try {
        const series = await this.obtenerSeriePorProducto(producto.IdProducto);
        Vue.set(producto, "Series", series);
        if (typeof producto.IdProductoSerie === "undefined" || producto.IdProductoSerie === "") {
          Vue.set(producto, "IdProductoSerie", null);
        }
        if (!series.length) {
          this.$toasted.show("Este producto no tiene series disponibles");
        }
      } catch (error) {
        this.$toasted.error("No se pudieron actualizar las series");
      } finally {
        Vue.set(producto, "_seriesLoading", false);
      }
    },
    setProductoUni(obj) {
      obj.Descripcion = null;
      this.objProd.cantxPrecio = 1;
      this.$refs.autocompleteProducto.setValue("");
      obj.precio = parseFloat(obj.PrecioContado).toFixed(1);
      obj.cantidad = 1;
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj.cantidadPres = 1;
      this.$emit("setProducto", obj);
      this.$refs["modalCant"].hide();

      this.actualizarProd(obj);
      this.$nextTick(() => {
        setTimeout(() => {
          this.focusBarcode(true);
        }, 150);
      });
    },
    setProductoCant(prod) {
      prod.Descripcion = null;
      this.objProd.cantxPrecio = 1;
      this.cant = prod.cantidad;
      let obj = this.objProd;
      this.$refs.autocompleteProducto.setValue("");
      //obj.FLETE = 0
      // this.objProd.precio = 0
      // this.objProd.cantidad = 0
      obj.precio = parseFloat(obj.precio).toFixed(1);
      obj.cantidad = prod.cantidad;
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj = {
        ...obj,
        cantidad: prod.cantidad,
        precio: prod.precio
      };
      // obj = this.onSetProducto(obj, prod.cantidad, prod.precio);
      this.$emit("setProducto", obj);
      // this.add([
      //   obj,
      //   () => {
      //     this.$emit("onAddProducto", obj);
      //     this.$toasted.success("Producto añadido correctamente");
      //   },
      //   false,
      //   false,
      //   this.cliente.EsVIP
      // ]);
      this.$refs["modalCant"].hide();
      obj.cantidadPres = this.cant;
      // this.actualizarProd(obj);
      this.$nextTick(() => {
          setTimeout(() => {
            this.focusBarcode(true);
          }, 150);
        });
      },
    tieneVariasPresentaciones(obj) {
      try {
        const opciones = typeof obj.PreciosPorProducto === 'string'
          ? JSON.parse(obj.PreciosPorProducto || '[]') : obj.PreciosPorProducto;
        return Array.isArray(opciones) && opciones.some(opcion => !!parseFloat(opcion.cantidad));
      } catch (e) { return false; }
    },
    showPresentacion(obj) {
      if (!obj.PreciosPorProducto !== "") {
        this.precioCont = obj.PrecioContado;
        obj.estadoPxP = false;
        this.preciosProd = obj.PreciosPorProducto
          ? JSON.parse(obj.PreciosPorProducto)
          : [];
        this.objProd = obj;
        this.$refs["modalCant"].show();
        return;
      }
    },
    renderChild(data) {
      const original = data;
      const list = this.$refs.autocompleteProducto && this.$refs.autocompleteProducto.json || [];
      const resultIndex = list.indexOf(original);
      const ordinal = resultIndex >= 0 ? (resultIndex + 1) + '. ' : '';
      const escape = value => String(value == null ? '' : value).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
      data = Object.assign({}, data);
      ['Producto','CodigoBarra','ProductoCategoria','ProductoModelo','ProductoMarca','PrecioContado'].forEach(key => { data[key] = escape(data[key]); });
      if (this.idAlmacen) {
        const imagen = this.productoImagenUrl(data.Imagen).replace(/&/g, '&amp;').replace(/"/g, '&quot;');
        const stock = parseFloat(data.stock);
        const stockTexto = Number.isFinite(stock)
          ? Number.isInteger(stock)
            ? stock
            : stock.toFixed(2)
          : "0";
        const precioTexto =
          data.PrecioContado === null || data.PrecioContado === undefined
            ? "0"
            : data.PrecioContado;
        const stockEstado = stock > 0 ? "has-stock" : "no-stock";
        return `
        <div class="imceden-product-row">
          <div class="imceden-product-stock ${stockEstado}" title="Stock disponible">
            <strong>${stockTexto}</strong>
          </div>
          <div class="imceden-product-name" title="Producto y código de barras">
            <strong>${ordinal}<span class="pos-option-barcode">${data.CodigoBarra || "S/C"}</span> ${data.Producto || "Producto sin nombre"}</strong>
          </div>
          <div class="imceden-product-detail imceden-product-category" title="Categoría">
            ${data.ProductoCategoria || ""}
          </div>
          <div class="imceden-product-detail imceden-product-model" title="Modelo">
            ${data.ProductoModelo || ""}
          </div>
          <div class="imceden-product-detail imceden-product-brand" title="Marca">
            ${data.ProductoMarca || ""}
          </div>
          <div class="imceden-product-price" title="Precio de venta">
            <strong>${precioTexto}</strong>
          </div>
          <div class="imceden-product-image-cell">
            <span class="imceden-product-image-frame" title="Ampliar imagen">
              <img class="imceden-product-image" src="${imagen}" onerror="this.onerror=null;this.src='/static/no-photo.png'" alt="Imagen del producto">
            </span>
          </div>
        </div>
        `;
      }
      return `
        <b><span class="pos-option-barcode">${data.CodigoBarra || "S/C"}</span> ${data.Producto}</b> / ${data.ProductoCategoria} / ${
        data.ProductoMarca
      } / ${data.ProductoModelo}
      `;
    },
    // setCantidad (index, val) {
    //   this.$emit('onInputCantidad', val)
    // },
    setNuevoPrecioContado(index) {
      let producto = { ...this.productos[index] };
      // Añadir IGV si check IGV
      let precio = parseFloat(producto.precio);
      let flete = producto.FLETE
        ? parseFloat(
            producto.FLETE / (producto.cantidad ? producto.cantidad : 1)
          )
        : 0;

      if (producto.TieneIgv) {
        precio += precio * 0.18;
      }
      let precioFinal = 0;

      precioFinal =
        (precio * producto.PorcentajeUtilidad) / 100 + precio + flete;
      // +parseFloat(flete).toFixed(2);
      // + parseFloat(producto.PorcentajeUtilidad.FLETE /
      //   (producto.PorcentajeUtilidad.cantidad
      //     ? producto.PorcentajeUtilidad.cantidad
      //     : 1));

      let normalPrecio = this.$options.filters.redondear(precioFinal);

      let nuevoPrecio = this.$options.filters.redondear(precioFinal, 1);

      if (this.tipoCambio > 0) {
        normalPrecio *= this.tipoCambio;
        nuevoPrecio *= this.tipoCambio;
      }

      Vue.set(
        this.productos[index],
        "normalPrecioContado",
        parseFloat(normalPrecio).toFixed(2)
      );
      Vue.set(
        this.productos[index],
        "nuevoPrecioContado",
        Math.ceil(nuevoPrecio)
      );
      this.getSubtotal(index);

      this.updateManoDeObraNeta(index);
    },
    actualizarDescu(index) {
      let obj = this.productos[index];
      this.actualizarProd(obj);
    },
    utilidadProducto(producto) {
      return ((Number(producto.precio) || 0) - (Number(producto.PrecioCosto) || 0)) * (Number(producto.cantidad) || 0) - (Number(producto.descuento) || 0);
    },
    getSubtotal(index) {
      let subtotal = this.$options.filters.redondear(
        this.productos[index].cantidad * this.productos[index].precio
      );
      Vue.set(this.productos[index], "subtotal", subtotal);
      // this.productos[index].subtotal = subtotal
      return subtotal;
    },
    getIgv(index) {
      let igv = 0;
      if (this.productos[index].TieneIgv) {
        igv = this.$options.filters.redondear(
          this.productos[index].subtotal * 0.18
        );
      }

      Vue.set(this.productos[index], "igv", igv);
      // this.productos[index].igv = igv
      return igv;
    },
    getTotal(index) {
      let total = parseFloat(this.getSubtotal(index));
      const igv = parseFloat(this.getIgv(index)) || 0;
      /*
      if (this.productos[index].FLETE) {
        total += parseFloat(this.productos[index].FLETE);
      }*/
      if (this.productos[index].TieneIgv) {
        total += igv;
      }
      if (this.productos[index].descuento) {
        total -= parseFloat(this.productos[index].descuento);
      }

      total = this.$options.filters.redondear(total);
      Vue.set(this.productos[index], "total", total);
      // this.productos[index].total = total
      return total;
    },
    getData(value) {
      this.buscar(value);
    },
    quitarProducto(index) {
      this.eliminar(index);
      this.$emit("onRemoveProducto", index);
      this.$toasted.show("Producto quitado del listado");
      this.focusBarcode();
    },
    addNumber(number) {
      this.cantidadInput = this.cantidadInput + number;
    },
    deleteNumber() {
      this.cantidadInput = this.cantidadInput.toString().slice(0, -1);
    },
    buscarCodigoBarra() {
      this.loadCode = this.cantidadInput;
      this.loadBarcode();
      this.$nextTick(() => {
        this.cantidadInput = "";
      });
    },
    formatFecha(fecha) {
      if (fecha) {
        return this.$moment(fecha, "YYYY-MM-DD hh:mm:ss").format("YYYY-MM-DD");
      } else {
        return "";
      }
    }
  },
  components: {
    ProductoForm
  }
};
</script>

<style scoped>
.contentCard {
  cursor: pointer;
}
.disabledMouse {
  background: #cecece;
  cursor: inherit;
}
.form-check-input {
  margin-left: -0.2rem !important;
}
.oferta {
  border: 4px solid #dc3545;
}
.ofertanom {
  color: #dc3545;
  border-right: 2px solid #dc3545;
}
.pos-option-barcode{display:inline-block;margin-right:8px;padding:2px 7px;border-radius:6px;background:#e4f3fa;color:#075b83;font-variant-numeric:tabular-nums;font-weight:800}
</style>
<style>
.pos-cart-component {
  position: relative;
  background: #fff;
}
.pos-cart-component .pos-search-toolbar,
.pos-cart-component .pos-search-column,
.pos-cart-component .pos-search-group,
.pos-cart-component .pos-product-name-search {
  overflow: visible !important;
}
.pos-cart-component .pos-search-column:first-child {
  position: relative;
  z-index: 110;
}

/* Tabla del carrito: filas claras, controles separados y acciones accesibles. */
.pos-cart-component .divTBl {
  position: relative;
  height: 60px;
  min-height: 60px;
  max-height: 60px;
  padding: 3px 3px 12px;
  overflow: hidden;
  border: 1px solid rgba(203, 219, 233, .8);
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 10px 26px rgba(15, 35, 65, .06);
  white-space: normal;
  transition: height 280ms cubic-bezier(.2, .8, .2, 1), box-shadow 200ms ease;
}

.pos-cart-component .divTBl.has-products {
  height: clamp(128px, var(--cart-height), 416px);
  min-height: 128px;
  max-height: 416px;
}

.pos-cart-component .divTBl.has-products .table-responsive,
.pos-cart-component .divTBl .table-responsive {
  height: 100%;
  max-height: 100%;
  overflow: auto;
  scrollbar-color: #2aa9c4 #e9f3f7;
  scrollbar-width: thin;
}

.pos-cart-component .table-responsive {
  border-radius: 16px;
}

.pos-cart-component .divTBl .table-responsive::-webkit-scrollbar {
  width: 9px;
  height: 9px;
}

.pos-cart-component .divTBl .table-responsive::-webkit-scrollbar-track {
  border-radius: 999px;
  background: #e9f3f7;
}

.pos-cart-component .divTBl .table-responsive::-webkit-scrollbar-thumb {
  border: 2px solid #e9f3f7;
  border-radius: 999px;
  background: linear-gradient(180deg, #21bcd2, #287be0);
}

.pos-cart-component .pos-cart-table {
  width: 100%;
  min-width: 1080px;
  margin: 0;
  border: 0 !important;
  border-collapse: separate !important;
  border-spacing: 0 7px !important;
  color: #14314c;
  background: transparent !important;
  font-family: Roboto, Arial, sans-serif;
  font-size: 13px;
}

.pos-cart-component .pos-cart-table thead th {
  position: sticky;
  z-index: 6;
  top: 0;
  padding: 10px 8px !important;
  border: 0 !important;
  color: #eafaff !important;
  background: linear-gradient(135deg, #082c43, #075e6b) !important;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: .04em;
  text-align: center;
  text-transform: uppercase;
  vertical-align: middle;
}

.pos-cart-component .pos-cart-table thead th:first-child {
  border-radius: 12px 0 0 12px;
}

.pos-cart-component .pos-cart-table thead th:last-child {
  border-radius: 0 12px 12px 0;
}

.pos-cart-component .pos-cart-table tbody tr,
.pos-cart-component .pos-cart-table.table-striped tbody tr:nth-of-type(odd) {
  background: #fff !important;
  box-shadow: 0 6px 18px rgba(18, 60, 91, .08);
  transition: transform 160ms ease, box-shadow 160ms ease;
}

.pos-cart-component .pos-cart-table tbody tr:hover {
  background: #f8fcff !important;
  box-shadow: 0 10px 24px rgba(23, 104, 156, .14);
  transform: translateY(-1px);
}

.pos-cart-component .pos-cart-table tbody td {
  height: 58px;
  padding: 7px 6px !important;
  border-top: 1px solid #e2ebf3 !important;
  border-bottom: 1px solid #e2ebf3 !important;
  background: #fff;
  vertical-align: middle !important;
}

.pos-cart-component .pos-cart-table tbody td:first-child {
  border-left: 1px solid #e2ebf3 !important;
  border-radius: 12px 0 0 12px;
}

.pos-cart-component .pos-cart-table tbody td:last-child {
  border-right: 1px solid #e2ebf3 !important;
  border-radius: 0 12px 12px 0;
}

.pos-cart-component .pos-cart-table tbody td.table-info {
  color: #07556b !important;
  background: #def6fb !important;
}

.pos-cart-component .pos-cart-table tbody td.table-success {
  color: #08663e !important;
  background: #ddf5e8 !important;
}

.pos-cart-component .pos-cart-table tbody td.table-warning {
  color: #735407 !important;
  background: #fff2c7 !important;
}

.pos-cart-component .pos-cart-table tbody td.table-danger {
  color: #8d243b !important;
  background: #fbe2e7 !important;
}

.pos-cart-component .pos-cart-table .pos-cart-barcode {
  min-width: 118px;
  color: #31506b;
  font-family: Consolas, "Courier New", monospace;
  font-size: 12px;
  font-weight: 800;
  letter-spacing: .02em;
  white-space: normal;
  overflow-wrap: anywhere;
}

.pos-cart-component .pos-cart-table .form-control,
.pos-cart-component .pos-cart-table .custom-select {
  min-height: 38px;
  border: 1px solid #cddfec;
  border-radius: 10px;
  color: #17344f;
  background: #f8fbfd;
  box-shadow: inset 0 1px 2px rgba(24, 61, 91, .04);
}

.pos-cart-component .pos-cart-table textarea.form-control {
  height: 44px !important;
  min-height: 44px !important;
  resize: vertical;
}

.pos-cart-component .pos-cart-table .form-control:focus,
.pos-cart-component .pos-cart-table .custom-select:focus {
  border-color: #27b9d6;
  background: #fff;
  box-shadow: 0 0 0 3px rgba(39, 185, 214, .14);
}

.pos-cart-tool-stack,
.pos-cart-row-action-buttons {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
}

.pos-cart-component .pos-cart-tool-button.btn,
.pos-cart-component .pos-cart-row-button.btn {
  display: inline-grid;
  width: 36px !important;
  min-width: 36px !important;
  height: 36px !important;
  min-height: 36px !important;
  margin: 0 !important;
  padding: 0 !important;
  place-items: center;
  border: 1px solid rgba(255, 255, 255, .68) !important;
  border-radius: 10px !important;
  color: #fff !important;
  box-shadow: 0 5px 12px rgba(10, 54, 86, .18) !important;
}

.pos-cart-component .pos-cart-tool-button.is-list {
  background: linear-gradient(135deg, #08a9c4, #157fbb) !important;
}

.pos-cart-component .pos-cart-tool-button.is-image {
  background: linear-gradient(135deg, #0fa765, #28c66c) !important;
}
.pos-cart-component .pos-cart-tool-button.is-ai {
  background: linear-gradient(135deg, #6653d9, #4533a8) !important;
  font: 750 14px "Segoe UI", Arial, sans-serif !important;
  letter-spacing: .3px;
}

.pos-cart-component .pos-cart-row-button.is-delete {
  background: linear-gradient(135deg, #e73c50, #c91f38) !important;
}

.pos-cart-component .pos-cart-row-button.is-edit {
  background: linear-gradient(135deg, #1689ed, #3159e8) !important;
}

.pos-cart-row-actions {
  display: flex;
  min-width: 86px;
  flex-direction: column;
  align-items: center;
  gap: 5px;
}

.pos-cart-gain {
  display: inline-flex;
  padding: 2px 7px;
  border-radius: 999px;
  color: #31506b;
  background: #edf4f8;
  font-size: 10px;
  font-weight: 800;
  white-space: nowrap;
}

/* Imagen fuera del recuadro del carrito y adaptada al alto de la pantalla. */
.pos-product-image-modal,
.pos-product-editor-modal {
  z-index: 10550 !important;
}

.pos-product-image-modal .modal-dialog {
  width: min(92vw, 1060px);
  max-width: min(92vw, 1060px) !important;
  margin: 1.5rem auto;
}

.pos-product-image-modal .modal-content,
.pos-product-editor-modal .modal-content {
  max-height: calc(100vh - 3rem);
  overflow: hidden;
  border: 1px solid rgba(76, 185, 219, .52);
  border-radius: 19px;
  box-shadow: 0 26px 70px rgba(4, 29, 52, .38);
}

.pos-product-image-modal .modal-header,
.pos-product-editor-modal .modal-header {
  align-items: center;
  padding: 13px 18px;
  border-bottom: 1px solid rgba(80, 194, 220, .24);
  color: #fff;
  background: linear-gradient(115deg, #072943, #086575 70%, #264984);
}

.pos-product-image-modal .modal-title,
.pos-product-editor-modal .modal-title {
  overflow: hidden;
  font-size: 1.05rem;
  font-weight: 850;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.pos-product-image-modal .modal-header .close,
.pos-product-editor-modal .modal-header .close {
  color: #fff;
  opacity: .9;
  text-shadow: none;
}

.pos-product-image-modal__body {
  display: flex;
  min-height: 360px;
  max-height: calc(100vh - 8rem);
  padding: 14px !important;
  overflow: auto;
  background: #f3f8fb;
}

.pos-product-image-stage {
  display: flex;
  width: 100%;
  min-width: 0;
  min-height: 330px;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border: 1px solid #dbe7ef;
  border-radius: 14px;
  background: #fff;
}

.pos-product-image-stage .imagenGrande {
  display: block;
  width: auto !important;
  max-width: 100% !important;
  height: auto !important;
  max-height: calc(100vh - 11rem) !important;
  padding: 8px;
  object-fit: contain;
}

.pos-product-editor-modal .modal-dialog {
  width: auto !important;
  max-width: 800px !important;
  margin: 1.75rem auto !important;
}

.pos-product-editor-modal__body {
  max-height: calc(100vh - 11rem);
  overflow-y: auto;
  background: #f7fafc;
}

@media only screen and (max-width: 750px) {
  .pos-cart-component .divTBl {
    height: 60px;
    min-height: 60px;
    max-height: 60px;
  }

  .pos-cart-component .divTBl.has-products {
    height: clamp(128px, var(--cart-height), 340px);
    min-height: 128px;
    max-height: 340px;
  }

  .pos-product-image-modal .modal-dialog,
  .pos-product-editor-modal .modal-dialog {
    width: calc(100vw - 16px);
    max-width: calc(100vw - 16px) !important;
    margin: 8px auto;
  }

  .pos-product-image-modal .modal-content,
  .pos-product-editor-modal .modal-content {
    max-height: calc(100vh - 16px);
    border-radius: 14px;
  }

  .pos-product-image-modal__body {
    min-height: 260px;
  }
}
.pos-product-name-search .autocomplete {
  position: relative !important;
  width: 100% !important;
  overflow: visible !important;
}
.pos-page .pos-product-name-search .autocomplete.autocomplete-list {
  top: calc(100% + 13px) !important;
  right: auto !important;
  left: 0 !important;
  width: calc(170% + 10px) !important;
  min-width: calc(170% + 10px) !important;
  max-width: calc(170% + 10px) !important;
  overflow: visible !important;
  border: 0 !important;
  background: transparent !important;
  box-shadow: none !important;
}
.pos-page .pos-product-name-search .autocomplete-list ul {
  position: static !important;
  z-index: 120 !important;
  width: 100% !important;
  min-width: 100% !important;
  max-width: 100% !important;
  max-height: none !important;
  margin: 0 !important;
  padding: 8px 10px !important;
  overflow: visible !important;
  border: 1px solid #b9e4ef !important;
  border-radius: 17px !important;
  background: #f4fbfe !important;
  box-shadow: 0 15px 34px rgba(8, 53, 79, 0.2), 0 0 0 3px rgba(19, 173, 204, 0.08) !important;
}
.pos-page .pos-product-name-search .autocomplete-list ul li a::before {
  display: none !important;
}
.pos-page .pos-product-name-search .autocomplete-list ul li a > div {
  display: block !important;
  width: 100% !important;
  min-width: 0 !important;
  overflow: visible !important;
}
.pos-product-name-search .autocomplete ul li,
.pos-product-name-search .autocomplete ul li a {
  min-height: 0 !important;
  overflow: visible !important;
}
.pos-product-name-search .autocomplete-list li {
  min-height: 0 !important;
  margin: 4px 0 !important;
  padding: 0 !important;
}
.pos-page .pos-product-name-search .autocomplete-list ul li a,
.pos-page .pos-product-name-search .autocomplete-list ul li a:hover,
.pos-page .pos-product-name-search .autocomplete-list ul li.focus-list a {
  display: block !important;
  width: 100% !important;
  min-height: 0 !important;
  padding: 0 !important;
  overflow: visible !important;
  border: 0 !important;
  border-radius: 12px !important;
  color: #102c52 !important;
  background: transparent !important;
  box-shadow: none !important;
  transform: none !important;
}
.imceden-product-row {
  display: grid;
  grid-template-columns: 8% minmax(280px, 40%) 10.5% 10.5% 16% 9% 6%;
  align-items: center;
  width: 100%;
  min-width: 0;
  min-height: 58px;
  padding: 0;
  overflow: visible;
  border: 1px solid #e1e9f1;
  border-radius: 12px;
  color: #102c52 !important;
  background: #fff;
  box-shadow: 0 3px 9px rgba(35, 62, 91, 0.05);
  font-family: Roboto, Arial, sans-serif;
  font-size: 13px;
  line-height: 1.18;
  transition: border-color 130ms ease, background-color 130ms ease, box-shadow 130ms ease;
}
.pos-product-name-search .autocomplete-list li:hover .imceden-product-row,
.pos-product-name-search .autocomplete-list li.focus-list .imceden-product-row {
  border-color: #b9d9fa !important;
  background: #deedfc !important;
  box-shadow: inset 7px 0 0 #087ce4, 0 4px 10px rgba(25, 91, 151, 0.09) !important;
}
.imceden-product-stock,
.imceden-product-name,
.imceden-product-detail,
.imceden-product-price,
.imceden-product-image-cell {
  display: flex;
  align-self: stretch;
  align-items: center;
  justify-content: center;
  min-width: 0;
  padding: 6px 12px;
  overflow: hidden;
  border-right: 1px solid #e1e9f1;
  color: #102c52 !important;
  text-align: center;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.imceden-product-stock {
  border-radius: 11px 0 0 11px;
  color: #087a46 !important;
  background: #dff5e8;
  font-size: 15px;
  font-weight: 800;
}
.imceden-product-stock.no-stock {
  color: #a4223c !important;
  background: #f9dfe5;
}
.imceden-product-name {
  justify-content: center;
  padding-right: 16px;
  padding-left: 16px;
  font-size: 13px;
  font-weight: 800;
}
.imceden-product-name strong {
  width: 100%;
  overflow: hidden;
  color: #102c52 !important;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.imceden-product-detail {
  font-size: 11px;
  font-weight: 700;
}
.imceden-product-price {
  color: #0064aa !important;
  background: #e5f3ff;
  font-size: 15px;
  font-weight: 800;
}
.imceden-product-image-cell {
  position: relative;
  overflow: visible;
  border-right: 0;
  padding: 5px 8px;
}
.imceden-product-image-frame {
  position: relative;
  z-index: 4;
  display: grid;
  width: 44px;
  height: 44px;
  place-items: center;
  overflow: visible;
  border: 1px solid #d7e2ec;
  border-radius: 10px;
  background: #fff;
  box-shadow: 0 2px 6px rgba(23, 54, 84, 0.08);
  cursor: zoom-in;
}
.imceden-product-image {
  display: block;
  width: 36px;
  height: 36px;
  padding: 2px;
  border-radius: 8px;
  background: #fff;
  object-fit: contain;
  transition: width 140ms ease, height 140ms ease, box-shadow 140ms ease;
}
.imceden-product-image-frame:hover {
  z-index: 9999;
}
.imceden-product-image-frame:hover .imceden-product-image {
  position: absolute;
  z-index: 9999;
  top: 50%;
  right: calc(100% + 10px);
  width: 340px;
  height: 340px;
  padding: 28px;
  border: 2px solid #079dc5;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 18px 40px rgba(8, 50, 78, 0.28);
  object-fit: contain;
  transform: translateY(-50%);
}
@media only screen and (max-width: 1199px) {
  .pos-page .pos-product-name-search .autocomplete.autocomplete-list {
    width: calc(185% + 10px) !important;
    min-width: calc(185% + 10px) !important;
    max-width: calc(185% + 10px) !important;
  }
  .imceden-product-row {
    grid-template-columns: 8% minmax(230px, 40%) 11% 10% 15% 9% 7%;
  }
}
@media only screen and (max-width: 750px) {
  .pos-page .pos-product-name-search .autocomplete.autocomplete-list {
    width: 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
  }
  .imceden-product-row {
    grid-template-columns: 58px minmax(0, 1fr) 70px 48px;
    min-height: 50px;
  }
  .imceden-product-category,
  .imceden-product-model,
  .imceden-product-brand {
    display: none;
  }
  .imceden-product-name {
    justify-content: flex-start;
    padding: 5px 8px;
    text-align: left;
  }
  .imceden-product-image-frame:hover .imceden-product-image {
    right: 0;
    width: min(280px, 78vw);
    height: min(280px, 78vw);
  }
}
.popover {
  max-width: 420px !important;
}
.nomCart {
  display: flex;
  justify-content: center;
  align-content: center;
  align-items: center;
  border-right: 2px solid black;
}
#modalCodigoBarra {
  padding: 0 !important;
}
#modalCodigoBarra .modal-dialog {
  max-width: 50vw !important;
  max-height: 50vh !important;
}
#modalCodigoBarra .modal-body {
  padding-bottom: 1px !important;
  margin-bottom: 1px !important;
}
#codigoBarraStyle video,
#codigoBarraStyle canvas {
  width: 100%;
  height: 100%;
}
.calc__numbers {
  width: 15vw;
  height: 15vw;
}
.calc__numbers2 {
  width: 100%;
  height: 20vh;
}
.calc__numbers .botones {
  /* border: 1px solid #000000; */
  cursor: pointer;
  text-align: center;
}
.calc__numbers .botones:hover {
  color: #fff;
  background: #65ba9b;
  border: 1px;
}
.cameraBarra {
  height: 50vh;
  width: auto;
  margin: 0 auto;
}
.tblProductos {
  width: 830px;
  /* font-size: 1rem !important; */
}
.ocultarMovil {
  /* display: inline; */
}
.divTBl {
  font-size: 1rem;
  overflow-x: auto;
  white-space: nowrap;
}
.OcultarPC {
  visibility: hidden;
}

.OcultarTable {
  visibility: visible;
}

@media only screen and (max-width: 1440px) {
  .cameraBarra {
    height: 50vh;
    width: auto;
  }
  .OcultarPC {
    visibility: visible;
  }

  .OcultarTable {
    visibility: hidden;
  }
  #modalCodigoBarra .modal-dialog {
    max-width: 50vw !important;
    max-height: 50vh !important;
  }
  .tblProductos {
    width: 720px !important;
    font-size: 1rem !important;
  }
}
@media only screen and (max-width: 750px) {
  #modalCodigoBarra .modal-dialog {
    max-width: 100vw !important;
    max-height: 80vh !important;
  }
  .tblProductos {
    width: 400px !important;
    font-size: 1rem !important;
  }
  .cameraBarra {
    height: 80vh;
    width: auto;
  }
}
@media only screen and (max-width: 375px) {
  #modalCodigoBarra .modal-dialog {
    max-width: 100vw !important;
    max-height: 80vh !important;
  }
  .cameraBarra {
    height: 80vh;
    width: auto;
  }
  .tblProductos {
    width: 400px !important;
    font-size: 1rem !important;
  }

  .table-responsive {
    font-size: 0.7rem !important;
  }
  .divTBl {
    font-size: 0.7rem;
    overflow-x: auto;
    white-space: nowrap;
  }
}
.txtProducto {
  height: 50px !important;
}

#modalCodigoBarra {
  padding: 0 !important;
}
@keyframes parpadeo-color {
  0%,
  100% {
    /* Color normal o "apagado" */
    background: #9fa8da;
    color: black;
  }
  50% {
    /* Color "encendido" */
    background: #1a237e;
    color: #ffffff;
  }
}

.alerta {
  animation: parpadeo-color 1.5s ease-in-out infinite;
}

/* Tarjetas responsivas del carrito. En escritorio se conserva la tabla completa. */
.pos-mobile-profit{display:inline-flex;align-items:center;justify-content:center;gap:5px;margin-top:5px;padding:4px 6px;border:0;border-radius:6px;background:transparent;color:#68808b;font-size:11px;font-weight:700;cursor:pointer}.pos-mobile-profit:hover{background:#edf4f6}.pos-mobile-profit.is-loss[aria-expanded="true"]{color:#c3223e}.pos-mobile-profit:focus-visible{outline:2px solid #68808b;outline-offset:2px}
.pos-mobile-cart {
  display: none;
}

.pos-barcode-trigger-icon {
  display: inline-grid;
  width: 30px;
  height: 30px;
  place-items: center;
  border: 1px solid rgba(255, 255, 255, .42);
  border-radius: 9px;
  background: rgba(255, 255, 255, .13);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, .25);
}

/* Escáner inspirado en IMCEDEN: cámara amplia, guía clara y cierre seguro. */
.pos-imceden-scanner-modal {
  z-index: 10650 !important;
  padding: 0 !important;
}

.pos-imceden-scanner-modal .modal-dialog,
#modalCodigoBarra .modal-dialog {
  width: min(760px, calc(100vw - 28px)) !important;
  max-width: min(760px, calc(100vw - 28px)) !important;
  max-height: none !important;
  margin: 14px auto !important;
  transform: none !important;
}

.pos-imceden-scanner-modal .modal-content {
  overflow: hidden;
  border: 1px solid rgba(61, 197, 205, .52);
  border-radius: 22px;
  background: #071e32;
  box-shadow: 0 28px 75px rgba(1, 16, 30, .55), 0 0 0 6px rgba(29, 192, 190, .1);
}

.pos-imceden-scanner-modal .pos-imceden-scanner-header {
  min-height: 70px;
  align-items: center;
  padding: 11px 16px;
  border: 0;
  color: #fff;
  background: linear-gradient(118deg, #063b3c, #075866 60%, #163a67);
}

.pos-imceden-scanner-modal .modal-title {
  width: calc(100% - 38px);
}

.pos-imceden-scanner-modal .modal-header .close {
  width: 38px;
  height: 38px;
  margin: 0;
  padding: 0;
  border: 1px solid rgba(255, 255, 255, .22);
  border-radius: 11px;
  color: #fff;
  background: rgba(255, 255, 255, .1);
  font-size: 1.4rem;
  line-height: 36px;
  opacity: 1;
  text-shadow: none;
}

.pos-scanner-title {
  display: flex;
  min-width: 0;
  align-items: center;
  gap: 11px;
}

.pos-scanner-title-icon {
  display: inline-grid;
  width: 43px;
  min-width: 43px;
  height: 43px;
  place-items: center;
  border: 1px solid rgba(116, 241, 223, .5);
  border-radius: 12px;
  color: #78f2db;
  background: rgba(12, 213, 175, .13);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, .16);
  font-size: 1.22rem;
}

.pos-scanner-title small,
.pos-scanner-title strong {
  display: block;
}

.pos-scanner-title small {
  margin-bottom: 1px;
  color: #6eead5;
  font-size: .64rem;
  font-weight: 800;
  letter-spacing: .1em;
  text-transform: uppercase;
}

.pos-scanner-title strong {
  overflow: hidden;
  font-size: 1rem;
  font-weight: 900;
  letter-spacing: .025em;
  text-overflow: ellipsis;
  text-transform: uppercase;
  white-space: nowrap;
}

.pos-imceden-scanner-modal .pos-imceden-scanner-body,
#modalCodigoBarra .modal-body {
  padding: 10px !important;
  margin: 0 !important;
  background: linear-gradient(180deg, #08233a, #07192b);
}

.pos-scanner-shell {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.pos-scanner-viewport {
  position: relative;
  width: 100%;
  height: min(66vh, 560px);
  min-height: 360px;
  overflow: hidden;
  border: 1px solid rgba(91, 178, 208, .32);
  border-radius: 17px;
  background: radial-gradient(circle at 50% 45%, #173b55, #071725 72%);
  box-shadow: inset 0 0 45px rgba(0, 0, 0, .42);
}

.pos-scanner-viewport .cameraBarra {
  position: absolute !important;
  inset: 0;
  width: 100% !important;
  height: 100% !important;
  margin: 0 !important;
  overflow: hidden;
}

#codigoBarraStyle .cameraBarra video,
#codigoBarraStyle .cameraBarra canvas {
  position: absolute !important;
  inset: 0;
  width: 100% !important;
  height: 100% !important;
  object-fit: cover;
}

#codigoBarraStyle .cameraBarra canvas {
  z-index: 2;
}

.pos-scanner-guide {
  position: absolute;
  z-index: 5;
  top: 50%;
  left: 50%;
  width: min(82%, 520px);
  height: 31%;
  min-height: 125px;
  border: 2px solid #e8b650;
  border-radius: 18px;
  box-shadow: 0 0 0 999px rgba(4, 18, 31, .2), inset 0 0 24px rgba(232, 182, 80, .08);
  transform: translate(-50%, -54%);
  pointer-events: none;
}

.pos-scanner-guide::before,
.pos-scanner-guide::after {
  position: absolute;
  width: 24px;
  height: 24px;
  border-color: #fff1b3;
  content: "";
}

.pos-scanner-guide::before {
  top: -3px;
  left: -3px;
  border-top: 4px solid;
  border-left: 4px solid;
  border-radius: 16px 0 0;
}

.pos-scanner-guide::after {
  right: -3px;
  bottom: -3px;
  border-right: 4px solid;
  border-bottom: 4px solid;
  border-radius: 0 0 16px;
}

.pos-scanner-line {
  position: absolute;
  top: 50%;
  right: 5%;
  left: 5%;
  height: 2px;
  border-radius: 999px;
  background: #ff304f;
  box-shadow: 0 0 5px #ff304f, 0 0 15px rgba(255, 48, 79, .95);
  animation: posScannerLine 2.2s ease-in-out infinite;
}

@keyframes posScannerLine {
  0%, 100% { transform: translateY(-44px); opacity: .72; }
  50% { transform: translateY(44px); opacity: 1; }
}

.pos-scanner-instructions {
  position: absolute;
  z-index: 6;
  right: 18px;
  bottom: 16px;
  left: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 9px 12px;
  border: 1px solid rgba(255, 255, 255, .14);
  border-radius: 12px;
  color: #fff;
  background: rgba(3, 18, 31, .68);
  box-shadow: 0 8px 24px rgba(0, 0, 0, .22);
  font-size: .78rem;
  font-weight: 700;
  line-height: 1.25;
  text-align: center;
  text-shadow: 0 1px 3px #000;
}

.pos-scanner-instructions i {
  color: #e8b650;
}

.pos-scanner-manual {
  display: grid;
  align-items: stretch;
  grid-template-columns: 44px minmax(0, 1fr) 48px;
  overflow: hidden;
  border: 1px solid rgba(96, 197, 220, .42);
  border-radius: 13px;
  background: #fff;
}

.pos-scanner-manual-icon,
.pos-scanner-search.btn {
  display: grid;
  place-items: center;
  border: 0 !important;
  border-radius: 0 !important;
}

.pos-scanner-manual-icon {
  color: #5ee2d1;
  background: #093a51;
}

.pos-scanner-manual .form-control {
  height: 46px;
  border: 0;
  border-radius: 0;
  color: #12324a;
  box-shadow: none;
  font-weight: 650;
}

.pos-scanner-search.btn {
  color: #fff !important;
  background: linear-gradient(135deg, #0baf77, #12c68a) !important;
}

@media (max-width: 767.98px) {
  .pos-desktop-cart {
    display: none !important;
  }

  .pos-cart-component .divTBl,
  .pos-cart-component .divTBl.has-products {
    height: auto !important;
    min-height: 68px !important;
    max-height: none !important;
    padding: 4px 4px 32px;
    overflow: hidden;
    border-radius: 15px;
  }

  .pos-mobile-cart {
    display: block;
    width: 100%;
    max-height: min(56vh, 520px);
    padding: 2px 2px 5px;
    overflow-x: hidden;
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-color: #16afbd #e5f2f5;
    scrollbar-width: thin;
  }

  .pos-mobile-cart::-webkit-scrollbar {
    width: 5px;
  }

  .pos-mobile-cart::-webkit-scrollbar-thumb {
    border-radius: 999px;
    background: linear-gradient(#18b9c4, #2a76dc);
  }

  .pos-mobile-cart-empty {
    display: flex;
    min-height: 58px;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: #6c8294;
    font-size: .75rem;
    font-weight: 700;
  }

  .pos-mobile-cart-empty i {
    color: #12aa85;
  }

  .pos-mobile-product-card {
    overflow: hidden;
    margin: 0 0 8px;
    border: 1px solid #cbdde8;
    border-top: 4px solid #075e68;
    border-radius: 15px;
    background: #fff;
    box-shadow: 0 8px 22px rgba(17, 61, 91, .12);
  }

  .pos-mobile-product-header {
    display: grid;
    min-height: 72px;
    align-items: center;
    grid-template-columns: minmax(0, 1fr) 36px auto;
    gap: 6px;
    color: #12344e;
    background: linear-gradient(118deg, #ffffff, #f5faff 68%, #edf5fb);
  }

  .pos-mobile-product-main {
    min-width: 0;
    padding: 8px 9px 7px;
  }

  .pos-mobile-product-badges {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 5px;
    margin-bottom: 6px;
  }

  .pos-mobile-product-code,
  .pos-mobile-product-stock {
    display: inline-flex;
    min-width: 0;
    align-items: center;
    gap: 4px;
    padding: 3px 6px;
    overflow: hidden;
    border: 1px solid #c9dfe6;
    border-radius: 999px;
    color: #075e68;
    background: #e9f7f7;
    font-size: .57rem;
    font-weight: 850;
    letter-spacing: .02em;
    line-height: 1;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .pos-mobile-product-name {
    display: -webkit-box;
    overflow: hidden;
    color: #12344e;
    font-size: .76rem;
    font-weight: 900;
    line-height: 1.24;
    text-transform: uppercase;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
  }

  .pos-mobile-product-total {
    display: flex;
    min-width: 88px;
    padding: 8px 9px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    border-left: 1px solid #d8e5ed;
    color: #12344e;
    background: rgba(255, 255, 255, .7);
  }

  .pos-mobile-product-total small {
    margin-bottom: 3px;
    color: #a27200;
    font-size: .56rem;
    font-weight: 900;
    letter-spacing: .08em;
    text-transform: uppercase;
  }

  .pos-mobile-product-total strong {
    font-size: .86rem;
    font-weight: 900;
    white-space: nowrap;
  }

  .pos-mobile-product-toggle.btn {
    display: grid;
    width: 36px;
    min-width: 36px;
    height: 36px;
    min-height: 36px;
    margin: 0;
    padding: 0 !important;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .62) !important;
    border-radius: 11px !important;
    color: #fff !important;
    background: linear-gradient(135deg, #297ee8, #6448d5) !important;
    box-shadow: 0 5px 12px rgba(57, 83, 196, .24) !important;
    font-size: .8rem;
  }

  .pos-mobile-product-toggle.btn.is-open {
    background: linear-gradient(135deg, #6548d5, #385fcb) !important;
  }

  .pos-mobile-product-details {
    overflow: hidden;
    transform-origin: top;
  }

  .pos-mobile-product-reveal-enter-active,
  .pos-mobile-product-reveal-leave-active {
    transition: opacity 180ms ease, transform 180ms ease;
  }

  .pos-mobile-product-reveal-enter,
  .pos-mobile-product-reveal-leave-to {
    opacity: 0;
    transform: translateY(-5px);
  }

  .pos-mobile-product-fields {
    display: grid;
    padding: 8px;
    gap: 6px;
    grid-template-columns: .72fr 1.15fr .95fr;
    background: #f8fbfd;
  }

  .pos-mobile-field {
    min-width: 0;
    margin: 0;
  }

  .pos-mobile-field > span {
    display: block;
    min-height: 18px;
    margin-bottom: 3px;
    color: #315268;
    font-size: .52rem;
    font-weight: 900;
    line-height: 1.05;
    text-transform: uppercase;
  }

  .pos-mobile-field .form-control {
    width: 100% !important;
    height: 39px;
    min-height: 39px;
    padding: 5px 7px;
    border: 1px solid #c8dce8;
    border-radius: 9px;
    color: #17344f;
    background: #fff;
    font-size: .82rem;
    font-weight: 800;
    text-align: center;
    box-shadow: inset 0 1px 2px rgba(18, 57, 82, .04);
  }

  .pos-mobile-field:first-child .form-control {
    border-color: #a4dbd1;
    background: #effaf7;
  }

  .pos-mobile-field:last-child .form-control {
    border-color: #f0cfd6;
    background: #fff7f8;
  }

  .pos-mobile-field small {
    display: block;
    margin-top: 2px;
    color: #248169;
    font-size: .48rem;
    font-weight: 800;
    text-align: center;
    white-space: nowrap;
  }

  .pos-mobile-secondary-field,
  .pos-mobile-series-field {
    display: grid;
    align-items: center;
    padding: 7px 9px;
    gap: 8px;
    grid-template-columns: minmax(105px, .8fr) minmax(0, 1.2fr);
    border-top: 1px solid #e0ebf1;
    color: #26485f;
    background: #f2f8fb;
  }

  .pos-mobile-secondary-field label,
  .pos-mobile-series-field label {
    margin: 0;
    font-size: .67rem;
    font-weight: 850;
    line-height: 1.1;
  }

  .pos-mobile-series-field label i {
    margin-right: 4px;
    color: #7a5ae0;
  }

  .pos-mobile-series-field.is-empty {
    background: #f7f9fb;
  }

  .pos-mobile-series-control {
    display: grid;
    min-width: 0;
    align-items: stretch;
    gap: 5px;
    grid-template-columns: minmax(0, 1fr) 36px;
  }

  .pos-mobile-secondary-field > div {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .pos-mobile-secondary-field .form-control,
  .pos-mobile-series-field .custom-select {
    height: 36px;
    min-height: 36px;
    border: 1px solid #c8dce8;
    border-radius: 9px;
    color: #17344f;
    background-color: #fff;
    font-size: .73rem;
    font-weight: 750;
  }

  .pos-mobile-series-field.is-empty .custom-select {
    color: #6f8190;
    background-color: #eef3f6;
  }

  .pos-mobile-series-refresh.btn {
    display: grid;
    width: 36px;
    min-width: 36px;
    height: 36px;
    min-height: 36px;
    padding: 0 !important;
    place-items: center;
    border: 1px solid rgba(255, 255, 255, .52) !important;
    border-radius: 9px !important;
    color: #fff !important;
    background: linear-gradient(135deg, #6956d9, #397ae4) !important;
    box-shadow: 0 4px 10px rgba(60, 80, 180, .2) !important;
    font-size: .72rem;
  }

  .pos-mobile-product-footer {
    display: grid;
    align-items: center;
    padding: 7px 8px 8px;
    gap: 7px;
    grid-template-columns: minmax(0, 1fr) auto;
    border-top: 1px solid #e4edf3;
    background: #fff;
  }

  .pos-mobile-description {
    display: grid;
    min-width: 0;
    margin: 0;
    align-items: center;
    gap: 6px;
    grid-template-columns: 20px minmax(0, 1fr);
    color: #198391;
  }

  .pos-mobile-description textarea.form-control {
    width: 100%;
    height: 36px !important;
    min-height: 36px !important;
    padding: 7px 8px;
    resize: none;
    border: 1px solid #d3e2eb;
    border-radius: 9px;
    font-size: .7rem;
  }

  .pos-mobile-product-actions {
    display: flex;
    gap: 5px;
  }

  .pos-cart-component .pos-mobile-product-actions .btn {
    width: 34px !important;
    min-width: 34px !important;
    height: 34px !important;
    min-height: 34px !important;
    border-radius: 10px !important;
    font-size: .74rem;
  }

  .pos-imceden-scanner-modal .modal-dialog,
  #modalCodigoBarra .modal-dialog {
    width: calc(100vw - 12px) !important;
    max-width: calc(100vw - 12px) !important;
    margin: 6px auto !important;
  }

  .pos-imceden-scanner-modal .modal-content {
    height: calc(100dvh - 12px);
    max-height: calc(100vh - 12px);
    border-radius: 17px;
  }

  .pos-imceden-scanner-modal .pos-imceden-scanner-body {
    display: flex;
    min-height: 0;
    flex: 1;
  }

  .pos-scanner-shell {
    width: 100%;
    min-height: 0;
  }

  .pos-scanner-viewport {
    height: auto;
    min-height: 0;
    flex: 1;
    border-radius: 15px;
  }

  .pos-scanner-guide {
    width: 86%;
    height: 27%;
    min-height: 118px;
  }

  .pos-scanner-instructions {
    right: 10px;
    bottom: 11px;
    left: 10px;
    padding: 8px 9px;
    font-size: .66rem;
  }

  .pos-scanner-title-icon {
    width: 39px;
    min-width: 39px;
    height: 39px;
  }

  .pos-scanner-title strong {
    font-size: .82rem;
  }
}

@media (max-width: 390px) {
  .pos-mobile-product-main {
    padding-right: 6px;
    padding-left: 7px;
  }

  .pos-mobile-product-total {
    min-width: 82px;
    padding-right: 6px;
    padding-left: 6px;
  }

  .pos-mobile-product-fields {
    padding: 7px 6px;
    gap: 4px;
  }

  .pos-mobile-product-footer {
    padding-right: 6px;
    padding-left: 6px;
    gap: 5px;
  }

  .pos-cart-component .pos-mobile-product-actions .btn {
    width: 32px !important;
    min-width: 32px !important;
    height: 32px !important;
    min-height: 32px !important;
  }
}
.pos-option-barcode{display:inline-block;margin-right:8px;padding:2px 7px;border-radius:6px;background:#e4f3fa;color:#075b83;font-variant-numeric:tabular-nums;font-weight:800}
</style>

<style>
.pos-product-detail{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(0,1fr);gap:28px}.pos-product-gallery{min-width:0}.pos-product-detail .pos-product-image-stage{position:relative;height:440px;min-height:0;padding:12px}.pos-product-detail .imagenGrande{width:100%;height:100%;max-height:100%;object-fit:contain}.pos-gallery-prev,.pos-gallery-next{position:absolute;top:46%;border:1px solid #dbe5ec;border-radius:50%;background:white;width:42px;height:42px;color:#079b72;font-size:30px}.pos-gallery-prev{left:8px}.pos-gallery-next{right:8px}.pos-gallery-thumbs{display:flex;gap:10px;margin-top:12px;overflow-x:auto}.pos-gallery-thumbs button{padding:4px;background:white;border:2px solid #e5edf1;border-radius:12px;flex:0 0 80px;height:80px}.pos-gallery-thumbs button.selected{border-color:#00a77b}.pos-gallery-thumbs img{width:100%;height:100%;object-fit:contain}.pos-product-information>small{color:#098b75;font-weight:700}.pos-product-information h2{font-size:25px;line-height:1.3;margin:12px 0}.pos-detail-price{display:block;font-size:30px;color:#039878}.pos-detail-description{white-space:pre-line;overflow-wrap:anywhere;margin:22px 0;line-height:1.65}.pos-detail-attributes{display:grid;grid-template-columns:1fr 1fr;gap:10px}.pos-detail-attributes>div{background:#f0f7fa;border:1px solid #deebf0;border-radius:12px;padding:12px;overflow-wrap:anywhere}.pos-detail-attributes small,.pos-detail-attributes b{display:block}.pos-detail-stock{margin-top:20px;color:#07826c;font-weight:700}@media(max-width:767px){.pos-product-detail{grid-template-columns:1fr;gap:20px}.pos-product-detail .pos-product-image-stage{height:280px}.pos-product-information h2{font-size:21px}.pos-gallery-thumbs button{flex-basis:64px;height:64px}.pos-product-image-modal .modal-body{padding:12px!important}}
.pos-option-barcode{display:inline-block;margin-right:8px;padding:2px 7px;border-radius:6px;background:#e4f3fa;color:#075b83;font-variant-numeric:tabular-nums;font-weight:800}
</style>
