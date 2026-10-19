<template>
  <b-container fluid class="ancho px-0 py-0 pos-modern" id="conten">
    <b-modal id="modalImagen" size="lg" ref="modalImagen" hide-footer hide-header modal-class="pos-image-gallery-modal">
      <div class="pos-image-gallery-title"><i class="fas fa-images"></i><h3>{{ productoNombre }}</h3></div>
      <div class="pos-image-gallery-stage">
        <button v-if="productoUrls.length > 1" type="button" @click="cambiarImagen(-1)" aria-label="Imagen anterior"><i class="fas fa-chevron-left"></i></button>
        <b-img :src="productoUrl" class="imagenGrande"></b-img>
        <button v-if="productoUrls.length > 1" type="button" @click="cambiarImagen(1)" aria-label="Imagen siguiente"><i class="fas fa-chevron-right"></i></button>
      </div>
      <div class="pos-image-gallery-thumbs" v-if="productoUrls.length > 1">
        <button v-for="(url, imageIndex) in productoUrls" :key="url" type="button"
          :class="{ active: imageIndex === productoImagenActiva }" @click="seleccionarImagen(imageIndex)">
          <b-img :src="url"></b-img>
        </button>
      </div>
    </b-modal>
    <b-row id="header">
      <b-col cols="12" class="py-0 mt-0">
        <b-card no-body class="mt-0 pos-shell">
          <b-tabs v-model="ventaActiva" content-class="mt-0" pills card class="mt-0">
            <b-tab v-for="(venta, index) in ventas" :key="index" title-link-class="titleTabs"
              class="mt-0 pt-0">
              <template slot="title">
                <span class="pos-tab-icon">
                  <i class="fas fa-shopping-cart"></i>
                  <b>{{ index + 1 }}</b>
                </span>
                <span>Venta {{ index + 1 }}</span>
              </template>
              <b-container fluid>
                <button type="button" class="pos-mobile-document-toggle"
                  :aria-expanded="mostrarDatosBoletaMovil ? 'true' : 'false'"
                  @click="mostrarDatosBoletaMovil = !mostrarDatosBoletaMovil">
                  <span class="pos-mobile-document-toggle-icon">
                    <i :class="mostrarDatosBoletaMovil ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                  </span>
                  <span>{{ mostrarDatosBoletaMovil ? 'Ocultar datos de boleta' : 'Mostrar datos de boleta' }}</span>
                  <i class="fas fa-chevron-up pos-mobile-document-toggle-chevron"
                    :class="{ 'is-collapsed': !mostrarDatosBoletaMovil }"></i>
                </button>
                <b-row class="pos-command-header"
                  :class="{ 'pos-document-data-collapsed': !mostrarDatosBoletaMovil }">
                  <b-col lg="6" xl="6" cols="12" class="pos-sale-header">
                    <b-row>
                      <b-col lg="4" xl="4" cols="12" class="mostrarMovil">
                        Tipo de Venta: {{ ventas[ventaActiva].Serie }} - {{ ventas[ventaActiva].Numero }}
                      </b-col>
                      <b-col lg="4" xl="4" cols="12" class="ocultarMovil pos-doc-field">
                        <b-input-group prepend="Doc. Venta" class="mt-2">
                          <autocomplete required="required" :id="`inputTipoVenta${index}`" :url="API + 'ventas/tipos'"
                            anchor="TipoDoc" initValue="BOLETA ELEC." class="font-weight-bold" label="writer"
                            placeholder="" :on-select="setTipoVenta"
                            :onFocus="focusAutocomplete.bind(null, `inputTipoVenta${index}`)"
                            :classes="{ input: 'form-control' }">
                          </autocomplete>
                        </b-input-group>
                      </b-col>
                      <b-col lg="4" xl="4" cols="12" class="mostrarMovil">
                        <b-form-group label="Documento de venta" class="mt-2 pos-mobile-document-type">
                          <b-form-select
                            :value="ventas[index].tipoVenta.IdTipoDoc"
                            @change="seleccionarTipoVentaMovil"
                            required>
                            <option v-for="tipo in tiposVentaMovil" :key="tipo.IdTipoDoc" :value="tipo.IdTipoDoc">
                              {{ tipo.TipoDoc }}
                            </option>
                          </b-form-select>
                        </b-form-group>
                      </b-col>
                      <b-col md="4" class="docInfo" v-if="isNota[index]">
                        <b-input-group prepend="Doc. Referenc" class="mt-2">
                          <autocomplete required="required" :id="`inputDocRef${index}`" :ref="`inputDocRef${index}`"
                            :url="API + 'ventas/lista/' + ventas[index].puntoVenta.IdDocVentaPuntoVenta"
                            anchor="NroComprobante" initValue="" label="writer" placeholder="" :on-select="setDocRef"
                            :onFocus="focusAutocomplete.bind(null, `inputDocRef${index}`)"
                            :classes="{ input: 'form-control' }">
                          </autocomplete>
                        </b-input-group>
                      </b-col>
                      <b-col md="3" class="docInfo" v-if="isNota[index]">
                        <b-input-group class="mt-2" style="margin-bottom:-20px">
                          <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota"
                            v-on:change="setMotivo($event)" class="mb-3" size="md" required />
                        </b-input-group>
                      </b-col>
                      <b-col md="4" class="ocultarMovil pos-header-field">
                        <b-input-group prepend="Alm." class="mt-2">
                          <autocomplete required="required" :id="`inputAlmacen${index}`" :url="API + 'almacenes'"
                            anchor="Almacen" class="font-weight-bold" initValue="PRINCIPAL" label="writer"
                            placeholder="" :on-select="setAlmacen" :value="ventas[ventaActiva].almacen"
                            :onFocus="focusAutocomplete.bind(null, `inputAlmacen${index}`)"
                            :classes="{ input: 'form-control' }">
                          </autocomplete>
                        </b-input-group>
                      </b-col>
                      <b-col md="4" class="ocultarMovil pos-header-field">
                        <b-input-group prepend="P.V. " class="mt-2">
                          <autocomplete required="required" :id="`inputPuntoVenta${index}`" :url="API + 'ventas/puntos'"
                            anchor="PuntoVenta" class="font-weight-bold" initValue="PRINCIPAL" label="writer"
                            placeholder="" :on-select="setPuntoVenta"
                            :onFocus="focusAutocomplete.bind(null, `inputPuntoVenta${index}`)"
                            :classes="{ input: 'form-control' }">
                          </autocomplete>
                        </b-input-group>
                      </b-col>
                      <b-col cols="12" class="mt-1 pos-client-field">

                        <b-form-group>
                          <b-input-group class="d-flex w-100 w-lg-auto">
                            <b-input-group-prepend>
                              <!-- @dniRuc="setNewDniRuc" -->
                              <clientes-form txtButton="Editar"
                                v-if="venta.cliente.IdCliente && venta.cliente.DniRuc != '00000000'"
                                idModal="editCliente" @onAddCliente="setCliente"
                                :editCliente="venta.cliente"></clientes-form>
                              <AutoComplete :ref="`inputClienteTouch${index}`"
                                :value="ventas[index].cliente.Cliente"
                                :touch-enabled="touchHab"
                                @touch-focus="abrirTecladoTouch('cliente', $event)"
                                @guardarCliente="setCliente"
                                @dniRuc="newDniRuc=$event" />
                            </b-input-group-prepend>
                            <b-input-group-append>
                              <clientes-form :newDniRuc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                            </b-input-group-append>
                          </b-input-group>

                        </b-form-group>


                      </b-col>
                      <b-col cols="12" class="pos-client-meta-row">
                        <div class="pos-organization-toggle">
                          <b-form-checkbox :id="`checkbox-${index}`" v-model="venta.EsOrganizacion" :value="true"
                            :unchecked-value="false">
                            Es Organización
                          </b-form-checkbox>
                        </div>
                        <div class="pos-address-control" v-if="ventas[ventaActiva].cliente.IdCliente">
                          <i class="fas fa-map-marker-alt"></i>
                          <b-form-select v-model="venta.CampoDireccion">
                            <option :value="ventas[ventaActiva].cliente.Direccion">{{ venta.cliente.Direccion }}</option>
                            <option :value="ventas[ventaActiva].cliente.Direccion2">{{ venta.cliente.Direccion2 }}</option>
                            <option :value="ventas[ventaActiva].cliente.Direccion3">{{ venta.cliente.Direccion3 }}</option>
                          </b-form-select>
                        </div>
                        <div class="pos-organization-name" v-if="venta.EsOrganizacion">
                          <b-form-input v-model="venta.nombreOrganizacion"
                            :id="`txtNombreOrganizacion-${index}`" placeholder="NOMBRE DE LA ORGANIZACIÓN"></b-form-input>
                        </div>
                      </b-col>
                    </b-row>
                  </b-col>
                  <b-col lg="6" xl="6" cols="12" class="ocultarMovil pos-document-summary">
                    <div class="pos-document-layout" style="display: flex; align-items: center; justify-content: space-around; gap: 8px; padding: 4px 0;">
                      <b-col md="4">
                        <b-form-group class="text-center pos-points-card">
                            <span class="pos-metric-label"><i class="fas fa-star"></i> Puntos del cliente</span>
                            <div class="puntos">
                                {{ (venta.cliente.Puntos || 0) | redondeoMenor }}
                            </div>
                        </b-form-group>
                        <b-input-group prepend="Puntos" 
                          :append="ventas[ventaActiva].descuentoPuntos.descuento | redondear"
                          v-if="ventas[ventaActiva].cliente.Puntos && ventas[ventaActiva].descuentoGeneral == 0 && ventas[ventaActiva].tipoVenta.CodSunat != '07' && ventas[ventaActiva].tipoVenta.CodSunat != '08'">
                          <b-form-input
                            v-model="ventas[ventaActiva].puntosAplicados"
                            min="0"
                            :max="ventas[ventaActiva].cliente.Puntos"
                            @input="aplicarPuntos"  
                            type="number"
                          ></b-form-input>
                        </b-input-group>
                        <b-form-text v-if="ventas[ventaActiva].descuentoPuntos.ValorPunto">
                          1 Punto equivale a: S/ {{ ventas[ventaActiva].descuentoPuntos.ValorPunto | redondear }}
                        </b-form-text>
                    </b-col>
                      <!-- Serie / Número -->
                      <div class="pos-document-identity" style="text-align: center; flex: 1;">
                        <span class="pos-document-icon"><i class="fas fa-receipt"></i></span>
                        <span class="pos-summary-kicker">Comprobante actual</span>
                        <h1 style="margin-bottom: 0;">
                          {{ ventas[ventaActiva].Serie }} - {{ ventas[ventaActiva].Numero }}
                        </h1>
                        <p style="padding: 2px 3px; border-radius: 25px; background: yellow; 
                                  font-size: 1rem; display: inline-block; margin-bottom: 0;">
                          Valor Referencial
                        </p>
                      </div>

                      <div class="pos-seller-card" style="flex: 1; display: flex; align-items: center; justify-content: center;">
                        <span class="pos-seller-icon"><i class="fas fa-user-tie"></i></span>
                        <div class="pos-seller-copy">
                          <span class="pos-summary-kicker">Vendedor asignado</span>
                          <strong>{{ ventas[ventaActiva].vendedor.Usuario || 'Sin asignar' }}</strong>
                          <small><i class="fas fa-circle"></i> Sesión activa</small>
                        </div>
                      </div>

                    </div>
                  </b-col>
                  <!-- <b-col cols="3">
                    <b-form-checkbox
                      :id="`checkbox-${index}`"
                      v-model="venta.EsOrganizacion"
                      :value="true"
                      :unchecked-value="false"
                    >
                      Es Organización
                    </b-form-checkbox>  
                  </b-col>
                  <b-col cols="8" >
                    <b-form-input 
                    v-model="venta.nombreOrganizacion" 
                    v-if="venta.EsOrganizacion"
                    :id="`txtNombreOrganizacion-${index}`"
                    placeholder="NOMBRE DE LA ORGANIZACIÓN"></b-form-input>
                  </b-col> -->
                  <b-col cols="12" class="pos-search-strip">
                    <b-form-group class="formResponsive">
                      <b-input-group class="pos-product-search">
                        <b-input-group-append class="pos-product-lookup">
                          <autocomplete :ref="`autocompleteProducto${index}`" :id="`autocompleteProducto${index}`"
                            :url="APIProductos"
                            :customParams="{ 'sortBy': isVentas ? 'stock' : 'IdProducto', 'sortDesc': 1, limit: '30' }"
                            :onShouldRenderChild="renderChild" anchor="Producto" label="writer"
                            placeholder="Agregar Producto" :on-select="setProducto"
                            :onFocus="focusAutocomplete.bind(null, `autocompleteProducto${index}`)"
                            :classes="{ input: 'form-control', root: 'formResponsive' }"
                            @keydown.native.tab.prevent="irCodeBar" class="txtAutoComplete pos-product-autocomplete">
                          </autocomplete>
                          <b-button
                            class="pos-voice-button"
                            :class="{ 'is-listening': isListening }"
                            :aria-pressed="isListening ? 'true' : 'false'"
                            variant="primary"
                            @click="startRecognition"
                            :title="isListening ? 'Escuchando… presione para detener' : 'Buscar por voz'">
                            <i class="fas fa-microphone"></i>
                          </b-button>
                        </b-input-group-append>
                        <input v-model="loadCode" :id="`txtCodeBar${index}`" @keydown.enter.prevent="loadBarcode"
                          type="text" class="form-control pos-barcode-input" placeholder="Codigo de Barra" />
                        <b-input-group-append class="pos-search-actions">
                          <!--<proveedores-tabla></proveedores-tabla>-->
                          <b-button variant="success" class="pos-barcode-camera-button" @click="abrirCodigoBarra" title="Escanear código"><i class="fas fa-barcode"></i></b-button>
                          <b-button variant="primary" class="pos-search-button" @click="loadBarcode" title="Buscar código"><i
                              class="fas fa-search"></i></b-button>
                        </b-input-group-append>
                      </b-input-group>
                    </b-form-group>
                  </b-col>
                </b-row>

                <!-- <b-row>
                  <b-col cols="12">
                    <b-form-group>
                      <input
                        v-model="ventas[ventaActiva].Observacion"
                        type="text"
                        class="form-control"
                        placeholder="Observación"
                      />
                    </b-form-group>
                  </b-col>
                </b-row> -->
                <div class="divCarrito pos-cart"
                  :class="{ 'has-products': venta.productos.length > 0 }"
                  :style="{ '--cart-height': (105 + Math.min(venta.productos.length, 8) * 68) + 'px' }">
                  <b-row class="mt-2 clsCarrito">
                    <b-table striped hover responsive show-empty
                      empty-text="Aún no hay productos. Busca por nombre o escanea un código de barras."
                      :fields="headerTable" :items="venta.productos" class="tblCarrito">
                      <template slot="empty">
                        <div class="pos-empty-cart">
                          <div class="pos-empty-visual">
                            <span class="pos-empty-orbit"></span>
                            <i class="fas fa-shopping-basket"></i>
                            <span class="pos-empty-scan"></span>
                          </div>
                          <div class="pos-empty-copy">
                            <span class="pos-empty-kicker">Carrito inteligente</span>
                            <h3>Listo para una nueva venta</h3>
                            <p>Busca un producto por nombre, dicta con el micrófono o escanea su código de barras.</p>
                            <div class="pos-empty-methods">
                              <span><i class="fas fa-keyboard"></i> Nombre</span>
                              <span><i class="fas fa-microphone"></i> Voz</span>
                              <span><i class="fas fa-barcode"></i> Código</span>
                            </div>
                          </div>
                        </div>
                      </template>
                      <template slot="pres" slot-scope="data">
                        <div class="pos-product-media-actions">
                          <b-button v-if="tienePresentaciones(data.item)" size="sm" variant="info"
                            class="pos-presentation-button"
                            @click="showPresentacion(data.item, data.index)" title="Ver presentaciones y precios" aria-label="Ver presentaciones y precios">
                            <i class="fas fa-list-ul"></i>
                          </b-button>
                          <b-button
                            @click="abrirImagen(data.item)"
                            class="pos-product-image-button" :id="'popover-imagen-' + data.index" variant="success"
                            size="sm"><i class="fas fa-image"></i></b-button>
                        </div>
                        <b-popover placement="right" :target="'popover-imagen-' + data.index"
                          :title="data.item.Producto" triggers="hover">
                          <div style="text-align:center">
                            <b-img style="border-radius: 10px; width:200px" fluid
                              :src="data.item.Imagen == null || data.item.Imagen == '' ? 'static/no-photo.png' : '/resources/images/productos/' + data.item.Imagen"
                              alt="" @error="setAltImg"></b-img>
                          </div>
                        </b-popover>


                      </template>
                      <template slot="presentacion" slot-scope="data">
                        <b-form-input v-model="data.item.presentacion" type="text"
                          style="text-align:center; font-weight:bold; width: 120px;" autocomplete="false"
                          disabled></b-form-input>


                      </template>
                      <template slot="Descripcion" slot-scope="data">
                        <textarea v-model="data.item.Descripcion"
                          class="form-control pos-item-description"
                          placeholder="Descripción"
                          maxlength="180"
                          rows="1"
                          aria-label="Descripción del producto"></textarea>
                      </template>
                      <template slot="cantidad" slot-scope="data">
                        <input v-model="data.item.cantidad" type="number"
                          style="text-align:center; font-weight:bold; width: 90px;" autocomplete="false"
                          :id="`input${data.index}`" :disabled="data.item.esPresentacion" @input="actualizarDetalleConDescuento(data.item)"
                          @focus="abrirModalTouch(data.index, 1)" class="form-control"
                          @keydown.enter="irBusquedaProducto" />
                        <p style="color: rgb(0, 123, 255); text-align: center; font-weight: bold;padding:0;margin:0;"
                          v-if="data.item.esPresentacion">

                          {{ data.item.CantidadPresentacion * data.item.cantidad }} unid.
                        </p>
                      </template>
                      <template slot="precio" slot-scope="data">
                        <input :disabled="precioProductoBloqueado(data.item)" v-model="data.item.precio" min="0"
                          step="0.00001" type="text" style="text-align:center; font-weight:bold; width: 105px;"
                          autocomplete="false" @input="actualizarDetalleConDescuento(data.item)"
                          @focus="abrirModalTouch(data.index, 2)"
                          :title="precioProductoBloqueado(data.item) ? 'Precio unitario bloqueado para este perfil' : 'Editar precio unitario'"
                          :aria-label="precioProductoBloqueado(data.item) ? 'Precio unitario bloqueado' : 'Precio unitario editable'"
                          :class="['form-control', { 'pos-manual-price-locked': precioProductoBloqueado(data.item) }]">

                      </template>
                      <template slot="descuento" slot-scope="data">
                        <div class="pos-item-discount">
                          <input v-model.number="data.item.descuento" type="number"
                            style="text-align:center; font-weight:bold; width: 100px;" autocomplete="false"
                            min="0" :max="getSubTotalDetalle(data.item)" step="0.01"
                            :disabled="!puedeAplicarDescuentos"
                            @input="actualizarDescuentoProductoManual(data.item, $event.target.value)"
                            @focus="puedeAplicarDescuentos && abrirModalTouch(data.index, 3)"
                            :title="puedeAplicarDescuentos ? 'Editar descuento del producto' : 'Solo el administrador puede aplicar descuentos'"
                            :aria-label="puedeAplicarDescuentos ? 'Descuento editable' : 'Descuento bloqueado para este perfil'"
                            :class="['form-control', { 'pos-manual-discount-locked': !puedeAplicarDescuentos }]" />
                          <small v-if="Number(getSubTotalDetalle(data.item)) > 0" class="pos-item-discount-percent">
                            {{ getPorcentajeDescuentoAplicado(data.item).toFixed(2) }}% aplicado
                          </small>
                        </div>

                      </template>
                      <template slot="subtotal" slot-scope="data">
                        {{ getSubTotalDetalle(data.item) }}

                      </template>
                      <template slot="total" slot-scope="data">
                        {{ getTotalDetalle(data.item) }}

                      </template>
                      <template slot="acciones" slot-scope="data">
                        <div class="pos-row-actions">
                          <b-button class="pos-row-delete" variant="danger" size="sm"
                            @click="quitarProducto(data.index)" title="Quitar producto">
                            <i class="fas fa-trash-alt"></i>
                          </b-button>

                          <b-button class="pos-row-edit" variant="info" size="sm" v-if="mostrarEditarProducto()"
                            @click="editarProducto(data.item, data.index)" title="Editar producto">
                            <i class="fas fa-pencil-alt"></i>
                          </b-button>
                        </div>

                      </template>
                      <template slot="cantxPrecio" slot-scope="data">
                        <b-form-input v-if="data.item.esPresentacion" v-model="data.item.CantidadPresentacion"
                          type="text" style="text-align:center; font-weight:bold; width: 100px;" autocomplete="false"
                          @input="actualizarDetalleConDescuento(data.item)"></b-form-input>
                        <!-- {{data.item.esPresentacion}} -->
                        <!-- esPresentacion -->

                      </template>

                    </b-table>
                    <div v-if="!venta.productos.length" class="pos-mobile-empty-cart">
                      <span class="pos-mobile-empty-cart-icon"><i class="fas fa-shopping-basket"></i></span>
                      <div>
                        <strong>Lista para agregar productos</strong>
                        <small>Busca por nombre o escanea el codigo de barras.</small>
                      </div>
                    </div>
                    <div v-if="venta.productos.length" class="pos-mobile-cart-list" aria-label="Productos agregados">
                      <article v-for="(producto, productoIndex) in venta.productos"
                        :key="'producto-movil-' + (producto.IdProducto || producto.CodigoBarra || productoIndex) + '-' + productoIndex"
                        class="pos-mobile-product-card">
                        <header class="pos-mobile-product-head">
                          <div class="pos-mobile-product-title">
                            <div class="pos-mobile-product-badges">
                              <span class="pos-mobile-product-code">
                                <i class="fas fa-barcode"></i>
                                {{ producto.CodigoBarra || producto.Codigo || 'SIN CODIGO' }}
                              </span>
                              <span class="pos-mobile-stock-badge"
                                :class="{ 'is-empty': Number(producto.stock || 0) <= 0 }">
                                <i class="fas fa-box"></i>
                                Stock {{ formatearStockMovil(producto.stock) }}
                              </span>
                            </div>
                            <h3>{{ producto.Producto || 'Producto sin nombre' }}</h3>
                          </div>
                          <div class="pos-mobile-product-total">
                            <small>Total</small>
                            <strong>S/ {{ getTotalDetalle(producto) }}</strong>
                          </div>
                        </header>

                        <div class="pos-mobile-product-controls">
                          <label class="pos-mobile-quantity-field">
                            <span>Cantidad</span>
                            <div class="pos-mobile-quantity-stepper">
                              <button type="button" :disabled="producto.esPresentacion || Number(producto.cantidad) <= 1"
                                @click="cambiarCantidadProductoMovil(producto, -1)"
                                aria-label="Disminuir cantidad" title="Disminuir cantidad">
                                <i class="fas fa-minus"></i>
                              </button>
                              <input v-model="producto.cantidad" type="number" inputmode="decimal"
                                :disabled="producto.esPresentacion"
                                @input="actualizarDetalleConDescuento(producto)"
                                @focus="abrirModalTouch(productoIndex, 1)"
                                @keydown.enter="irBusquedaProducto"
                                class="form-control" />
                              <button type="button" :disabled="producto.esPresentacion"
                                @click="cambiarCantidadProductoMovil(producto, 1)"
                                aria-label="Aumentar cantidad" title="Aumentar cantidad">
                                <i class="fas fa-plus"></i>
                              </button>
                            </div>
                            <small v-if="producto.esPresentacion">
                              {{ producto.CantidadPresentacion * producto.cantidad }} unid.
                            </small>
                          </label>
                          <label :class="{ 'pos-mobile-price-locked': precioProductoBloqueado(producto) }">
                            <span>Precio unitario <i v-if="precioProductoBloqueado(producto)" class="fas fa-lock" aria-hidden="true"></i></span>
                            <input :disabled="precioProductoBloqueado(producto)" v-model="producto.precio"
                              min="0" step="0.00001" type="text" inputmode="decimal"
                              @input="actualizarDetalleConDescuento(producto)"
                              @focus="abrirModalTouch(productoIndex, 2)"
                              :title="precioProductoBloqueado(producto) ? 'Precio unitario bloqueado para este perfil' : 'Editar precio unitario'"
                              :aria-label="precioProductoBloqueado(producto) ? 'Precio unitario bloqueado' : 'Precio unitario editable'"
                              :class="['form-control', { 'pos-manual-price-locked': precioProductoBloqueado(producto) }]" />
                          </label>
                          <label :class="{ 'pos-mobile-discount-locked': !puedeAplicarDescuentos }">
                            <span>Descuento <i v-if="!puedeAplicarDescuentos" class="fas fa-lock" aria-hidden="true"></i></span>
                            <input v-model.number="producto.descuento" type="number" inputmode="decimal"
                              min="0" :max="getSubTotalDetalle(producto)" step="0.01"
                              :disabled="!puedeAplicarDescuentos"
                              @input="actualizarDescuentoProductoManual(producto, $event.target.value)"
                              @focus="puedeAplicarDescuentos && abrirModalTouch(productoIndex, 3)"
                              :title="puedeAplicarDescuentos ? 'Editar descuento del producto' : 'Solo el administrador puede aplicar descuentos'"
                              :aria-label="puedeAplicarDescuentos ? 'Descuento editable' : 'Descuento bloqueado para este perfil'"
                              :class="['form-control', { 'pos-manual-discount-locked': !puedeAplicarDescuentos }]" />
                            <small v-if="Number(getSubTotalDetalle(producto)) > 0">
                              {{ getPorcentajeDescuentoAplicado(producto).toFixed(2) }}% aplicado
                            </small>
                          </label>
                        </div>

                        <label v-if="producto._mostrarDescripcionMovil" class="pos-mobile-description-field">
                          <span>Descripcion del producto</span>
                          <textarea v-model="producto.Descripcion"
                            class="form-control pos-item-description"
                            placeholder="Escriba una descripcion solo si es necesaria"
                            maxlength="180"
                            rows="2"
                            aria-label="Descripcion del producto"></textarea>
                        </label>

                        <footer class="pos-mobile-product-actions">
                          <b-button size="sm" variant="outline-info"
                            class="pos-mobile-description-toggle"
                            @click="toggleDescripcionProductoMovil(producto)"
                            :title="producto._mostrarDescripcionMovil ? 'Ocultar descripcion' : 'Agregar descripcion'">
                            <i class="fas fa-align-left"></i>
                            <span>{{ producto._mostrarDescripcionMovil ? 'Ocultar descripcion' : 'Descripcion' }}</span>
                          </b-button>
                          <b-button v-if="tienePresentaciones(producto)" size="sm" variant="info"
                            @click="showPresentacion(producto, productoIndex)" title="Ver presentaciones y precios">
                            <i class="fas fa-list-ul"></i><span>Formatos</span>
                          </b-button>
                          <b-button size="sm" variant="success" @click="abrirImagen(producto)" title="Ver imagen">
                            <i class="fas fa-image"></i><span>Imagen</span>
                          </b-button>
                          <b-button v-if="mostrarEditarProducto()" size="sm" variant="info"
                            @click="editarProducto(producto, productoIndex)" title="Editar producto">
                            <i class="fas fa-pencil-alt"></i><span>Editar</span>
                          </b-button>
                          <b-button size="sm" variant="danger" @click="quitarProducto(productoIndex)" title="Quitar producto">
                            <i class="fas fa-trash-alt"></i><span>Quitar</span>
                          </b-button>
                        </footer>
                      </article>
                    </div>
                  </b-row>
                </div>
                <b-row class="justify-content-center pos-pay-row">
                  <b-button block class="mx-1 btnAction pos-pay-button" type="submit" size="lg" variant="success" @click="guardarVenta()">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="pos-pay-label">Pagar</span>
                    <span v-if="getTotal(ventaActiva) > 0" class="pos-pay-amount">S/ {{ getTotal(ventaActiva) | moneda }}</span>
                    <span v-else class="pos-pay-spacer" aria-hidden="true"></span>
                  </b-button>
                </b-row>

                <section class="pos-mobile-payment-section">
                  <button type="button" class="pos-mobile-payment-toggle"
                    :aria-expanded="mostrarMetodosMovil ? 'true' : 'false'"
                    aria-controls="pos-mobile-payment-methods"
                    @click.stop="mostrarMetodosMovil = !mostrarMetodosMovil">
                    <span class="pos-mobile-payment-toggle-icon"><i class="fas fa-wallet"></i></span>
                    <span class="pos-mobile-payment-toggle-copy">
                      <strong>Metodos de pago</strong>
                      <small>Seleccione o distribuya el importe</small>
                    </span>
                    <i class="fas fa-chevron-down pos-mobile-payment-chevron"
                      :class="{ 'is-open': mostrarMetodosMovil }"></i>
                  </button>

                  <div v-if="mostrarMetodosMovil" id="pos-mobile-payment-methods"
                    class="pos-mobile-payment-methods" @focusin="seleccionarContenidoPago">
                    <div class="pos-mobile-payment-method payment-cash">
                      <button type="button" @click="seleccionarMetodoPago(1, index)" title="Seleccionar efectivo">
                        <i class="fas fa-money-bill-wave"></i>
                      </button>
                      <strong>Efectivo</strong>
                      <b-form-input v-model="ventas[index].pago.efectivo.descripcion"
                        class="pos-mobile-payment-reference" type="text" placeholder="Referencia"></b-form-input>
                      <input v-model="ventas[index].pago.efectivo.monto" min="0" step="0.01"
                        type="number" inputmode="decimal" placeholder="S/."
                        class="form-control pos-mobile-payment-amount" />
                    </div>
                    <div class="pos-mobile-payment-method payment-wallet">
                      <button type="button" @click="seleccionarMetodoPago(3, index)" title="Seleccionar Yape o Plin">
                        <i class="fas fa-mobile-alt"></i>
                      </button>
                      <strong>Yape/Plin</strong>
                      <b-form-input v-model="ventas[index].pago.mastercard.descripcion"
                        class="pos-mobile-payment-reference" type="text" placeholder="Operacion"></b-form-input>
                      <input v-model="ventas[index].pago.mastercard.monto" min="0" step="0.01"
                        type="number" inputmode="decimal" placeholder="S/."
                        class="form-control pos-mobile-payment-amount"
                        @input="ventas[index].pago.efectivo.monto = $options.filters.redondear(getTotal(index) - ventas[index].pago.visa.monto - ventas[index].pago.delivery.monto - $event.target.value)" />
                    </div>
                    <div class="pos-mobile-payment-method payment-card">
                      <button type="button" @click="seleccionarMetodoPago(2, index)" title="Seleccionar tarjeta">
                        <i class="fas fa-credit-card"></i>
                      </button>
                      <strong>Tarjeta</strong>
                      <b-form-input v-model="ventas[index].pago.visa.descripcion"
                        class="pos-mobile-payment-reference" type="text" placeholder="Operacion"></b-form-input>
                      <input v-model="ventas[index].pago.visa.monto" min="0" step="0.01"
                        type="number" inputmode="decimal" placeholder="S/."
                        class="form-control pos-mobile-payment-amount"
                        @input="ventas[index].pago.efectivo.monto = $options.filters.redondear(getTotal(index) - ventas[index].pago.mastercard.monto - ventas[index].pago.delivery.monto - $event.target.value)" />
                    </div>
                    <div class="pos-mobile-payment-method payment-transfer">
                      <button type="button" @click="seleccionarMetodoPago(4, index)" title="Seleccionar transferencia">
                        <i class="fas fa-university"></i>
                      </button>
                      <strong>Transferencia</strong>
                      <b-form-input v-model="ventas[index].pago.delivery.descripcion"
                        class="pos-mobile-payment-reference" type="text" placeholder="Operacion"></b-form-input>
                      <input v-model="ventas[index].pago.delivery.monto" min="0" step="0.01"
                        type="number" inputmode="decimal" placeholder="S/."
                        class="form-control pos-mobile-payment-amount"
                        @input="ventas[index].pago.efectivo.monto = $options.filters.redondear(getTotal(index) - ventas[index].pago.visa.monto - ventas[index].pago.mastercard.monto - $event.target.value)" />
                    </div>
                  </div>
                </section>

                <div class="pos-mobile-primary-actions">
                  <b-button type="button" block class="btnAction pos-mobile-action-proforma"
                    variant="primary" @click="guardarProforma()">
                    <i class="fas fa-file-invoice"></i><span>Crear Proforma</span>
                  </b-button>
                  <b-button type="button" block class="btnAction pos-mobile-action-add"
                    variant="warning" @click="anadirVentana()">
                    <i class="fas fa-plus-circle"></i><span>Añadir Ventana</span>
                  </b-button>
                  <b-button type="button" block class="btnAction pos-mobile-action-view"
                    variant="danger" @click="abrirProformasMovil">
                    <i class="fas fa-file-alt"></i><span>Ver Proformas</span>
                  </b-button>
                </div>
                <b-row class="mt-4 pos-checkout">
                 <b-col lg="3" xl="4" cols="12" class="pos-options-column">
                    <b-row>
                      <!-- BOTONES DE ACCIONES RÁPIDAS -->
                      <b-col cols="12" class="mt-2">
                        <div class="d-flex flex-wrap gap-2 pos-quick-actions" style="gap: 8px;">
                          <b-button-group size="sm" class="ocularMovil pos-debt-trigger">
                            <pago-deuda></pago-deuda>
                          </b-button-group>
                          <b-button-group size="sm">
                            <movimiento></movimiento>
                          </b-button-group>
                          <b-button-group size="sm" class="pos-proformas-trigger">
                            <proformas ref="proformasLista" :idAlmacen="venta.almacen.IdAlmacen" @onSelectProforma="cargarProforma"
                              @cargarProforma="cargarProforma">
                            </proformas>
                          </b-button-group>
                          <b-button-group size="sm" class="ocularMovil">
                            <config-venta @onUpdateConfig="eliminarCliente"
                              @onUpdateConfigComision="eliminarComisionista">
                            </config-venta>
                          </b-button-group>
                        </div>
                      </b-col>

                      <!-- PANEL DE OPCIONES MEJORADO -->
                      <b-col cols="12" class="mt-2">
                        <div class="panel-opciones pos-document-options">

                          <!-- Proforma a Crédito -->
                          <div class="opcion-item pos-document-option pos-document-option-credit"
                            :class="{ 'is-open': !!venta.EsCreditoProforma }">
                            <div class="opcion-header">
                              <b-form-checkbox :disabled="bloquearInput ? true : false"
                                v-model="venta.EsCreditoProforma" :value="1" :unchecked-value="0"
                                class="opcion-checkbox">
                                <span class="opcion-label">
                                  <span class="pos-option-icon pos-option-icon-credit">
                                    <i class="fas fa-calendar-check"></i>
                                  </span>
                                  <span class="pos-option-copy">
                                    <strong>Proforma a Crédito</strong>
                                    <small>Programa una fecha de vencimiento</small>
                                  </span>
                                </span>
                              </b-form-checkbox>
                            </div>
                            <b-collapse :visible="!!venta.EsCreditoProforma">
                              <div class="opcion-body">
                                <label class="pos-option-field-label">
                                  <i class="far fa-calendar-alt"></i> Fecha de vencimiento
                                </label>
                                <div class="pos-option-input-wrap">
                                  <input v-model="venta.FechaCreditoProforma" type="date"
                                    class="form-control form-control-sm" placeholder="Fecha Crédito" />
                                </div>
                              </div>
                            </b-collapse>
                          </div>

                          <!-- Nota / Descripción -->
                          <div class="opcion-item pos-document-option pos-document-option-note"
                            :class="{ 'is-open': !!venta.EsDescripcion }">
                            <div class="opcion-header">
                              <b-form-checkbox :disabled="bloquearInput ? true : false" v-model="venta.EsDescripcion"
                                :value="1" :unchecked-value="0" class="opcion-checkbox">
                                <span class="opcion-label">
                                  <span class="pos-option-icon pos-option-icon-note">
                                    <i class="fas fa-file-alt"></i>
                                  </span>
                                  <span class="pos-option-copy">
                                    <strong>Nota / Descripción</strong>
                                    <small>Agrega condiciones comerciales</small>
                                  </span>
                                </span>
                              </b-form-checkbox>
                            </div>
                            <b-collapse :visible="!!venta.EsDescripcion">
                              <div class="opcion-body">
                                <div class="pos-option-fields">
                                  <div class="pos-option-input-wrap">
                                    <i class="fas fa-truck-loading"></i>
                                    <input v-model="venta.DescripcionProforma" type="text"
                                      class="form-control form-control-sm" placeholder="Plazo de entrega" />
                                  </div>
                                  <div class="pos-option-input-wrap">
                                    <i class="fas fa-hourglass-half"></i>
                                    <input v-model="venta.DescripcionProforma2" type="text"
                                      class="form-control form-control-sm" placeholder="Validez de la oferta" />
                                  </div>
                                  <div class="pos-option-input-wrap">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <input v-model="venta.DescripcionProforma3" type="text"
                                      class="form-control form-control-sm" placeholder="Lugar de entrega" />
                                  </div>
                                  <div class="pos-option-input-wrap">
                                    <i class="fas fa-shield-alt"></i>
                                    <input v-model="venta.DescripcionProforma4" type="text"
                                      class="form-control form-control-sm" placeholder="Garantía" />
                                  </div>
                                </div>
                              </div>
                            </b-collapse>
                          </div>

                        </div>
                      </b-col>
                    </b-row>
                  </b-col>
                  <b-col lg="3" xl="1" cols="12" class="pos-delivery-details"
                    v-if="ventas[ventaActiva].selectOpcionVenta > 0">


                    <!-- <b-col cols="12" class="text-left">
                      <table class="table b-table border table-footer table-sm">
                        <tbody>
                          <tr>
                            <td>Efectivo</td>
                            <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(1)"><i
                                  class="fas fa-check"></i></b-button></td>
                            <td>
                              <b-form-input v-model="ventas[ventaActiva].pago.efectivo.descripcion" type="text"
                                placeholder="Descripcion"></b-form-input>
                            </td>
                            <td>
                              <input v-model="ventas[ventaActiva].pago.efectivo.monto" min="0" step="0.01" type="number"
                                placeholder="S/." class="form-control" />
                            </td>
                          </tr>
                          <tr>
                            <td>Tarjeta</td>
                            <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(2)"><i
                                  class="fas fa-check"></i></b-button></td>
                            <td>
                              <b-form-input v-model="ventas[ventaActiva].pago.visa.descripcion" type="text"
                                placeholder="Descripcion"></b-form-input>
                            </td>
                            <td>
                              <input class="form-control" v-model="ventas[ventaActiva].pago.visa.monto" min="0"
                                step="0.01" type="number" placeholder="S/."
                                @input="ventas[ventaActiva].pago.efectivo.monto = $options.filters.redondear(getTotal(ventaActiva) - ventas[ventaActiva].pago.mastercard.monto - ventas[ventaActiva].pago.delivery.monto - $event.target.value)" />
                            </td>
                          </tr>
                          <tr>
                            <td>Yape/Plin</td>
                            <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(3)"><i
                                  class="fas fa-check"></i></b-button></td>
                            <td>
                              <b-form-input v-model="ventas[ventaActiva].pago.mastercard.descripcion" type="text"
                                placeholder="Descripcion"></b-form-input>
                            </td>
                            <td>
                              <input class="form-control" v-model="ventas[ventaActiva].pago.mastercard.monto" min="0"
                                step="0.01" type="number" placeholder="S/."
                                @input="ventas[ventaActiva].pago.efectivo.monto = $options.filters.redondear(getTotal(ventaActiva) - ventas[ventaActiva].pago.visa.monto - ventas[ventaActiva].pago.delivery.monto - $event.target.value)" />
                            </td>
                          </tr>
                          <tr>
                            <td>Transferencia</td>
                            <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(4)"><i
                                  class="fas fa-check"></i></b-button></td>
                            <td>
                              <b-form-input v-model="ventas[ventaActiva].pago.delivery.descripcion" type="text"
                                placeholder="Descripcion"></b-form-input>
                            </td>
                            <td>
                              <input class="form-control" v-model="ventas[ventaActiva].pago.delivery.monto" min="0"
                                step="0.01" type="number" placeholder="S/."
                                @input="ventas[ventaActiva].pago.efectivo.monto = $options.filters.redondear(getTotal(ventaActiva) - ventas[ventaActiva].pago.visa.monto - ventas[ventaActiva].pago.mastercard.monto - $event.target.value)" />
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </b-col> -->
                    <b-col md="12" v-if="ventas[ventaActiva].selectOpcionVenta > 0">
                      <b-form-group>
                        <label style="font-weight:bold">Nombre:</label>
                        <b-form-input required style="text-transform:uppercase; 
                          font-size:0.9rem" placeholder="Ingrese cliente" autocomplete="off"
                          v-model="ventas[ventaActiva].nombreLlevar" ref="inputNombreCliente" type="text"
                          name="NombreLlevar" id="NombreLlevar">
                        </b-form-input>
                      </b-form-group>
                    </b-col>
                    <b-col md="12" v-if="ventas[ventaActiva].selectOpcionVenta == 2">
                      <b-form-group>
                        <label style="font-weight:bold">Direccion Entrega:</label>
                        <b-form-textarea required style="text-transform:uppercase; font-size:0.9rem"
                          placeholder="Ingrese direccion" rows="3" autocomplete="off" v-model="venta.DireccionEntrega"
                          ref="inputDireccionEntrega" type="text" name="DireccionEntrega"
                          id="DireccionEntrega"></b-form-textarea>
                      </b-form-group>
                    </b-col>
                    <b-col md="12" class="text-left">

                    </b-col>
                  </b-col>
                  
                  <b-col lg="3" xl="3" cols="12" class="pos-actions">
                    <b-row class="centrado d-flex justify-content-center my-2">
                      <b-button block class="mx-1 btnAction pos-action-proforma" variant="primary" @click="guardarProforma()">
                        <i class="fas fa-file-invoice mr-1"></i> Crear Proforma</b-button>
                      <b-button block variant="warning" class="btnAction pos-action-add" @click="anadirVentana()">
                        <i class="fas fa-plus-circle mr-1"></i> Añadir Ventana</b-button>
                      <!-- <b-button block class="mx-1 btnAction" variant="success" @click="guardarVenta()"> <i
                          class="fas fa-shopping-cart"></i> PAGAR</b-button> -->
                      <busqueda-modelo class="pos-action-model"
                        :idAlmacen="venta.almacen.IdAlmacen"
                        @onAgregarProductos="cargarProductosDeModelo">
                      </busqueda-modelo>
                      <div class="pos-touch-controls">
                        <b-button v-if="touchHab" variant="danger" class="btnAction pos-action-touch" block
                          @click="activarModalTouch()"><i class="fas fa-desktop"></i> Deshabilitar Touch</b-button>
                        <b-button v-else variant="success" class="btnAction pos-action-touch" block
                          @click="activarModalTouch()"><i class="fas fa-keyboard"></i> Habilitar Touch</b-button>
                        <b-button v-if="touchHab" class="pos-touch-keyboard-launch"
                          title="Abrir teclado táctil" aria-label="Abrir teclado táctil"
                          @click="abrirTecladoAlfanumerico">
                          <i class="fas fa-keyboard"></i>
                        </b-button>
                      </div>
                      <b-button block v-if="ventas.length > 1" variant="danger" @click="eliminarVentana()" class="btnAction pos-action-cancel"> Cancelar
                        Venta</b-button>
                      <b-button class="btnAction pos-print-button" block @click="testImpresora()">
                        <i class="fas fa-print mr-1"></i> Probar Impresora</b-button>
                      <!-- <delivery-modal style="min-width: 100%; margin: 1% 0;" ref="deliveryModal"
                        @onPagarDelivery="onPagarDelivery"></delivery-modal> -->
                      <b-button :variant="mostrarNav ? 'danger' : 'info'" class="btnAction pos-action-nav" block @click="ocultarNav()">
                        <i class="fas fa-bars"></i>
                        <span v-if="mostrarNav"> Ocultar Nav</span>
                        <span v-else> Mostrar Nav</span>
                      </b-button>

                    </b-row>
                  </b-col>
                  <b-col lg="3" xl="4" cols="12" class="pos-summary">
                   <table class="table b-table border table-footer resumen-venta" style="font-size:0.85rem">
                      <tbody>
                        <tr>
                          <td>
                            <!-- CAMBIO: index en lugar de ventaActiva -->
                            <b-button class="p-1 ml-1 mt-1 pos-payment-trigger" :id="`popover-metodopago-${index}`" variant="success"
                              @click.stop="togglePaymentPopover(index)">
                              <i class="fas fa-credit-card"></i>
                            </b-button>
                            <br>
                            <b-badge v-if="ventas[index].idMetodoPago == 1" variant="success"
                              style="font-size: 0.70rem;">EFECTIVO</b-badge>
                            <b-badge v-if="ventas[index].idMetodoPago == 2" variant="info"
                              style="font-size: 0.70rem;">TARJETA</b-badge>
                            <b-badge v-if="ventas[index].idMetodoPago == 3" variant="info"
                              style="font-size: 0.70rem;">YAPE/PLIN</b-badge>
                            <b-badge v-if="ventas[index].idMetodoPago == 4" variant="info"
                              style="font-size: 0.70rem;">TRANSF.</b-badge>

                            <!-- CAMBIO: target usa index -->
                            <b-popover :target="`popover-metodopago-${index}`" placement="bottomleft"
                              triggers="manual" :show="paymentPopoverIndex === index"
                              custom-class="custom-popover payment-method-popover">
                              <div class="payment-method-panel" @focusin="seleccionarContenidoPago">
                                <div class="payment-method-heading">
                                  <span><i class="fas fa-wallet"></i></span>
                                  <div>
                                    <strong>Formas de pago</strong>
                                    <small>Distribuye el importe de la venta</small>
                                  </div>
                                </div>
                                <div class="table-responsive">
                                <table class="table b-table table-footer table-sm payment-method-table">
                                  <tbody>
                                    <tr class="payment-cash">
                                      <td class="payment-name"><span><i class="fas fa-money-bill-wave"></i></span><b>Efectivo</b></td>
                                      <td><b-button variant="info" size="sm" class="mt-1"
                                          @click="seleccionarMetodoPago(1, index)"><i class="fas fa-check"></i></b-button></td>
                                      <td>
                                        <b-form-input v-model="ventas[index].pago.efectivo.descripcion"
                                          type="text" placeholder="Referencia"></b-form-input>
                                      </td>
                                      <td>
                                        <input v-model="ventas[index].pago.efectivo.monto" min="0" step="0.01"
                                          type="number" placeholder="S/." class="form-control" />
                                      </td>
                                    </tr>
                                    <tr class="payment-wallet">
                                      <td class="payment-name"><span><i class="fas fa-mobile-alt"></i></span><b>Yape/Plin</b></td>
                                      <td><b-button variant="info" size="sm" class="mt-1"
                                          @click="seleccionarMetodoPago(3, index)"><i class="fas fa-check"></i></b-button></td>
                                      <td>
                                        <b-form-input v-model="ventas[index].pago.mastercard.descripcion"
                                          type="text" placeholder="Operación"></b-form-input>
                                      </td>
                                      <td>
                                        <input class="form-control" v-model="ventas[index].pago.mastercard.monto"
                                          min="0" step="0.01" type="number" placeholder="S/."
                                          @input="ventas[index].pago.efectivo.monto = $options.filters.redondear(getTotal(index) - ventas[index].pago.visa.monto - ventas[index].pago.delivery.monto - $event.target.value)" />
                                      </td>
                                    </tr>
                                    <tr class="payment-card">
                                      <td class="payment-name"><span><i class="fas fa-credit-card"></i></span><b>Tarjeta</b></td>
                                      <td><b-button variant="info" size="sm" class="mt-1"
                                          @click="seleccionarMetodoPago(2, index)"><i class="fas fa-check"></i></b-button></td>
                                      <td>
                                        <b-form-input v-model="ventas[index].pago.visa.descripcion" type="text"
                                          placeholder="Operación"></b-form-input>
                                      </td>
                                      <td>
                                        <input class="form-control" v-model="ventas[index].pago.visa.monto"
                                          min="0" step="0.01" type="number" placeholder="S/."
                                          @input="ventas[index].pago.efectivo.monto = $options.filters.redondear(getTotal(index) - ventas[index].pago.mastercard.monto - ventas[index].pago.delivery.monto - $event.target.value)" />
                                      </td>
                                    </tr>
                                    <tr class="payment-transfer">
                                      <td class="payment-name"><span><i class="fas fa-university"></i></span><b>Transferencia</b></td>
                                      <td><b-button variant="info" size="sm" class="mt-1"
                                          @click="seleccionarMetodoPago(4, index)"><i class="fas fa-check"></i></b-button></td>
                                      <td>
                                        <b-form-input v-model="ventas[index].pago.delivery.descripcion"
                                          type="text" placeholder="Operación"></b-form-input>
                                      </td>
                                      <td>
                                        <input class="form-control" v-model="ventas[index].pago.delivery.monto"
                                          min="0" step="0.01" type="number" placeholder="S/."
                                          @input="ventas[index].pago.efectivo.monto = $options.filters.redondear(getTotal(index) - ventas[index].pago.visa.monto - ventas[index].pago.mastercard.monto - $event.target.value)" />
                                      </td>
                                    </tr>
                                  </tbody>
                                </table>
                                </div>
                              </div>
                            </b-popover>
                          </td>
                          <td class="table-success resumen-subtotal">Subtotal</td>
                          <td class="table-success  resumen-subtotal">{{ getSubTotal(ventaActiva) }}</td>
                        </tr>

                        <tr>
                          <td>
                            <!-- CAMBIO: index en lugar de ventaActiva -->
                            <b-button class="p-1 ml-1 mt-1 pos-options-trigger" :id="`popover-opciones-${index}`"
                              variant="warning" @click.stop="toggleSaleOptionsPopover(index)">
                              <i class="fas fa-tags"></i>
                            </b-button>

                            <!-- CAMBIO: target usa index -->
                            <b-popover :target="`popover-opciones-${index}`" placement="bottomleft"
                              custom-class="custom-popover sale-options-popover" triggers="manual"
                              :show="saleOptionsPopoverIndex === index">
                              <div class="sale-options-panel">
                                <div class="sale-options-heading">
                                  <span><i class="fas fa-sliders-h"></i></span>
                                  <div>
                                    <strong>Opciones de la venta</strong>
                                    <small>Configura condiciones adicionales</small>
                                  </div>
                                </div>
                                <div class="sale-options-body">
                                  <section class="sale-option-section sale-option-discount"
                                    v-if="ventas[ventaActiva].puntosAplicados === 0">
                                    <div class="sale-option-label">
                                      <span><i class="fas fa-percentage"></i></span>
                                      <div><b>Descuento general</b><small>Se prorratea segun el importe de cada producto</small></div>
                                    </div>
                                      <b-input-group prepend="S/" append="Descuento" class="sale-option-input">
                                        <input v-model.number="ventas[ventaActiva].descuentoGeneral"
                                          min="0" :max="getSubTotal(ventaActiva)" value="0"
                                          :disabled="!puedeAplicarDescuentos || bloquearInput"
                                          @input="aplicarDescuentoGeneral($event.target.value)"
                                          :title="puedeAplicarDescuentos ? 'Aplicar y prorratear descuento general' : 'Solo el administrador puede aplicar descuentos'"
                                          :aria-label="puedeAplicarDescuentos ? 'Descuento general editable' : 'Descuento general bloqueado para este perfil'"
                                          type="number"
                                          :class="['form-control', { 'pos-manual-discount-locked': !puedeAplicarDescuentos }]" step="0.01" />
                                      </b-input-group>
                                  </section>
                                  <section class="sale-option-section sale-option-toggles">
                                    <div class="sale-option-toggle">
                                      <span class="sale-option-toggle-icon credit"><i class="fas fa-calendar-check"></i></span>
                                      <b-form-checkbox v-model="ventas[ventaActiva].EsCredito" :value="1"
                                        :unchecked-value="0">
                                        <b>Venta a crédito</b>
                                        <small>Define una fecha de pago</small>
                                      </b-form-checkbox>
                                      <b-form-input v-if="ventas[ventaActiva].EsCredito" v-model="venta.FechaCredito"
                                        type="date" class="sale-credit-date">
                                      </b-form-input>
                                    </div>
                                    <div class="sale-option-toggle">
                                      <span class="sale-option-toggle-icon delivery"><i class="fas fa-truck"></i></span>
                                      <b-form-checkbox v-model="ventas[ventaActiva].PorEntregar" :value="1"
                                        :unchecked-value="0">
                                        <b>Por entregar</b>
                                        <small>Marca la venta como pendiente</small>
                                      </b-form-checkbox>
                                    </div>
                                  </section>
                                  <section class="sale-option-section sale-document-fields">
                                    <button type="button" class="sale-document-field-button"
                                      :class="{ 'has-value': String(ventas[ventaActiva].Observacion || '').trim() !== '' }"
                                      @click.stop="abrirDatoVenta('observacion')">
                                      <span class="sale-document-field-icon observation"><i class="fas fa-align-left"></i></span>
                                      <span class="sale-document-field-copy">
                                        <b>Observaciones</b>
                                        <small>{{ resumenDatoVenta(ventas[ventaActiva].Observacion, 'Agregar una indicación') }}</small>
                                      </span>
                                      <i class="fas fa-chevron-right sale-document-field-arrow"></i>
                                    </button>
                                    <button type="button" class="sale-document-field-button"
                                      :class="{ 'has-value': String(ventas[ventaActiva].Placa || '').trim() !== '' }"
                                      @click.stop="abrirDatoVenta('placa')">
                                      <span class="sale-document-field-icon plate"><i class="fas fa-car"></i></span>
                                      <span class="sale-document-field-copy">
                                        <b>Placa del vehículo</b>
                                        <small>{{ resumenDatoVenta(ventas[ventaActiva].Placa, 'Agregar placa') }}</small>
                                      </span>
                                      <i class="fas fa-chevron-right sale-document-field-arrow"></i>
                                    </button>
                                  </section>
                                  <section class="sale-option-section sale-option-validity">
                                    <div class="sale-option-label">
                                      <span><i class="fas fa-hourglass-half"></i></span>
                                      <div><b>Vigencia de proforma</b><small>Tiempo de validez del documento</small></div>
                                    </div>
                                      <b-input-group prepend="Válida por" append="días" class="sale-option-input">
                                        <b-form-input :disabled="bloquearInput ? true : false"
                                          v-model="ventas[ventaActiva].diasValidosProf" min="0" value="0" type="number">
                                        </b-form-input>
                                      </b-input-group>
                                  </section>
                                </div>
                              </div>
                            </b-popover>
                          </td>
                          <td class="table-danger resumen-subtotal">Descuento</td>
                          <td class="table-danger  resumen-subtotal">{{ getDescuento(ventaActiva) }}</td>
                        </tr>

                        <tr v-if="ventaConIgv(ventaActiva)" class="pos-tax-base">
                          <td></td>
                          <td class="resumen-subtotal">Op. gravada</td>
                          <td class="resumen-subtotal">{{ getBaseGravada(ventaActiva) }}</td>
                        </tr>
                        <tr v-if="ventaConIgv(ventaActiva)" class="pos-tax-igv">
                          <td></td>
                          <td class="resumen-subtotal">IGV (18%)</td>
                          <td class="resumen-subtotal">{{ getIgvVenta(ventaActiva) }}</td>
                        </tr>

                        <tr>
                          <td>
                            <b-button class="pos-calculator-trigger" :id="`popover-calculadora-${index}`"
                              title="Abrir calculadora" @click.stop="toggleCalculadora($event)">
                              <i class="fas fa-calculator"></i>
                            </b-button>
                            <div v-if="calculatorOpen" class="pos-calculator-floating"
                              :style="{ left: calculatorPosition.x + 'px', top: calculatorPosition.y + 'px' }"
                              @click.stop>
                              <div class="pos-calculator">
                                <div class="pos-calculator-heading" @pointerdown="iniciarArrastreCalculadora">
                                  <span><i class="fas fa-calculator"></i></span>
                                  <div><strong>Calculadora rápida</strong><small>Operaciones para la venta</small></div>
                                  <button type="button" class="pos-calculator-close"
                                    title="Cerrar calculadora" @pointerdown.stop @click.stop="cerrarCalculadora">
                                    <i class="fas fa-times"></i>
                                  </button>
                                </div>
                                <div class="pos-calculator-display">
                                  <small>RESULTADO</small>
                                  <input v-model="calculatorExpression" readonly placeholder="0">
                                </div>
                                <div class="pos-calculator-grid">
                                  <button type="button" class="calc-clear" @click="calculatorClear">C</button>
                                  <button type="button" class="calc-operation" @click="calculatorInput('(')">(</button>
                                  <button type="button" class="calc-operation" @click="calculatorInput(')')">)</button>
                                  <button type="button" class="calc-operation" @click="calculatorInput('/')">÷</button>
                                  <button type="button" @click="calculatorInput('7')">7</button>
                                  <button type="button" @click="calculatorInput('8')">8</button>
                                  <button type="button" @click="calculatorInput('9')">9</button>
                                  <button type="button" class="calc-operation" @click="calculatorInput('*')">×</button>
                                  <button type="button" @click="calculatorInput('4')">4</button>
                                  <button type="button" @click="calculatorInput('5')">5</button>
                                  <button type="button" @click="calculatorInput('6')">6</button>
                                  <button type="button" class="calc-operation" @click="calculatorInput('-')">−</button>
                                  <button type="button" @click="calculatorInput('1')">1</button>
                                  <button type="button" @click="calculatorInput('2')">2</button>
                                  <button type="button" @click="calculatorInput('3')">3</button>
                                  <button type="button" class="calc-operation" @click="calculatorInput('+')">+</button>
                                  <button type="button" class="calc-backspace" @click="calculatorBackspace"><i class="fas fa-backspace"></i></button>
                                  <button type="button" @click="calculatorInput('0')">0</button>
                                  <button type="button" @click="calculatorInput('.')">.</button>
                                  <button type="button" class="calc-equals" @click="calculatorResult">=</button>
                                </div>
                              </div>
                            </div>
                          </td>
                          <td class="resumen-total-label total-final-label"><b>TOTAL</b></td>
                          <td class="resumen-total-monto table-danger">{{ getTotal(ventaActiva) }}</td>
                        </tr>
                        <tr>
                          <td colspan="3" class="pos-payment-change">
                            <label><i class="fas fa-hand-holding-usd"></i> Pagó con</label>
                            <b-form-input type="number" min="0" step="0.01" placeholder="0.00"
                              :inputmode="touchHab ? 'none' : 'decimal'"
                              @focus="seleccionarPagoCon"
                              @click="$event.target.select()"
                              v-model="ventas[ventaActiva].pagocon">
                            </b-form-input>
                            <div class="vuelto-contenedor"
                              :class="{ 'is-negative': parseFloat(ventas[ventaActiva].pagocon - getTotal(ventaActiva)) < 0 }">
                              <label><i class="fas fa-coins"></i> Vuelto</label>
                              <div class="resumen-vuelto">
                                <b>S/ {{ parseFloat(ventas[ventaActiva].pagocon - getTotal(ventaActiva)).toFixed(2)
                                  }}</b>
                              </div>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>

                  </b-col>
                </b-row>
              </b-container>
            </b-tab>

          </b-tabs>
        </b-card>
      </b-col>

    </b-row>
    <b-modal ref="modalObservacionVenta" centered hide-footer
      modal-class="sale-document-modal" content-class="sale-document-modal-content">
      <template slot="modal-title">
        <span class="sale-document-modal-title-icon observation"><i class="fas fa-align-left"></i></span>
        <span><small>Dato para el comprobante</small><strong>Observaciones de la venta</strong></span>
      </template>
      <label class="sale-document-modal-label" for="venta-observacion">Observaciones</label>
      <b-form-textarea id="venta-observacion" v-model="observacionTemporal" maxlength="500" rows="5"
        placeholder="Ejemplo: Entregar por la tarde o indicar una referencia"></b-form-textarea>
      <div class="sale-document-modal-help">
        <span>Se imprimirá en la boleta o factura.</span>
        <span>{{ String(observacionTemporal || '').length }}/500</span>
      </div>
      <div class="sale-document-modal-actions">
        <b-button variant="outline-secondary" @click="$refs.modalObservacionVenta.hide()">Cancelar</b-button>
        <b-button variant="primary" @click="guardarObservacionVenta"><i class="fas fa-check mr-1"></i> Guardar</b-button>
      </div>
    </b-modal>

    <b-modal ref="modalPlacaVenta" centered hide-footer
      modal-class="sale-document-modal" content-class="sale-document-modal-content">
      <template slot="modal-title">
        <span class="sale-document-modal-title-icon plate"><i class="fas fa-car"></i></span>
        <span><small>Dato para el comprobante</small><strong>Placa del vehículo</strong></span>
      </template>
      <label class="sale-document-modal-label" for="venta-placa">Placa</label>
      <b-form-input id="venta-placa" v-model="placaTemporal" maxlength="20" autocomplete="off"
        placeholder="Ejemplo: ABC-123" @input="normalizarPlacaTemporal"></b-form-input>
      <div class="sale-document-modal-help">
        <span>Se imprimirá en la boleta o factura.</span>
        <span>{{ String(placaTemporal || '').length }}/20</span>
      </div>
      <div class="sale-document-modal-actions">
        <b-button variant="outline-secondary" @click="$refs.modalPlacaVenta.hide()">Cancelar</b-button>
        <b-button variant="primary" @click="guardarPlacaVenta"><i class="fas fa-check mr-1"></i> Guardar</b-button>
      </div>
    </b-modal>

    <b-modal v-model="modalPresentacion" size="lg" centered hide-footer
      modal-class="presentation-modal" dialog-class="presentation-modal-dialog"
      content-class="presentation-modal-content">
      <template slot="modal-title">
        <span class="presentation-title-icon"><i class="fas fa-box-open"></i></span>
        <span class="presentation-title-copy">
          <small>Presentaciones disponibles</small>
          <strong>{{ producto.Producto }}</strong>
        </span>
      </template>
      <div class="presentation-intro">
        <div>
          <span class="presentation-intro-icon"><i class="fas fa-tags"></i></span>
          <span>
            <strong>Elige una presentación</strong>
          </span>
        </div>
        <span class="presentation-count">
          {{ 1 + contarPresentaciones(producto) }} opciones
        </span>
      </div>
      <div class="presentation-grid" role="list">
        <button type="button" class="presentation-option presentation-option-unit"
          @click="cambiarPresentacion(producto, false)">
          <span class="presentation-card-head">
            <span class="presentation-card-kind"><i class="fas fa-cube"></i> UNIDAD</span>
            <span class="presentation-card-badge">x1</span>
          </span>
          <span class="presentation-card-name">{{ producto.ProductoPresentacion || 'UNIDAD' }}</span>
          <span class="presentation-card-price">
            <small>Precio final</small>
            <strong>S/ {{ producto.PrecioContado | redondear(2) }}</strong>
          </span>
          <span class="presentation-card-stock">
            <i class="fas fa-boxes"></i>
            {{ Math.max(0, Number(producto.stock || 0)) }} disponibles
          </span>
          <span class="presentation-card-action">Seleccionar <i class="fas fa-arrow-right"></i></span>
        </button>

        <button v-for="(pres, index) in producto.PreciosPorProducto" :key="'pres-' + index"
          type="button" class="presentation-option presentation-option-pack"
          @click="cambiarPresentacion(pres, true)">
          <span class="presentation-card-head">
            <span class="presentation-card-kind"><i class="fas fa-box-open"></i> PRESENTACIÓN</span>
            <span class="presentation-card-badge">x{{ pres.cantidad }}</span>
          </span>
          <span class="presentation-card-name">{{ pres.nombre }}</span>
          <span class="presentation-card-price">
            <small>Precio final</small>
            <strong>S/ {{ (pres.precio * pres.cantidad) | redondear(2) }}</strong>
          </span>
          <span class="presentation-card-stock">
            <i class="fas fa-boxes"></i>
            {{ Math.max(0, Math.floor(Number(producto.stock || 0) / Number(pres.cantidad || 1))) }} disponibles
          </span>
          <span class="presentation-card-action">Seleccionar <i class="fas fa-arrow-right"></i></span>
        </button>
      </div>    </b-modal>
    <!-- MODALS TOUCH -->

    <b-modal ref="modalIngCant" id="ingCant" size="sm" hide-footer class="ingCant" modal-class="pos-calculator-modal" title="Cantidad" hide-header-close>
      <label class="pos-calculator-product"><i class="fas fa-box-open"></i><span>Producto</span><b>{{ objProd.Producto }}</b></label>
      <input type="text" inputmode="none" @keypress="(e) => { verificar(e) }" v-model="cantidadInput"
        style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control" />
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
            <td class="botones" @click="addPoint('.');">.</td>
            <td class="botones" @click="deleteNumber();"><i class="fas fa-backspace"></i></td>
          </tr>
        </tbody>
      </table>
      <br>
      <div style="text-align: center; width: 100%; height: 100%;">
        <b-button style="width: 49%;" size="md" variant="success" @click="ingresaNum(capturaIndex)">Ingresar</b-button>
        <b-button style="width: 49%;" size="md" variant="danger" @click="cerrrarNum()">Cerrar</b-button>
      </div>
    </b-modal>

    <b-modal ref="modalIngPre" id="ingPre" size="sm" hide-footer class="ingPre" modal-class="pos-calculator-modal" title="Precio" hide-header-close>
      <label class="pos-calculator-product"><i class="fas fa-tag"></i><span>Producto</span><b>{{ objProd.Producto }}</b></label>
      <input type="text" inputmode="none" @keypress="(e) => { verificar(e) }" v-model="cantidadInput"
        style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control" />
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
            <td class="botones" @click="addPoint('.');">.</td>
            <td class="botones" @click="deleteNumber();"><i class="fas fa-backspace"></i></td>
          </tr>
        </tbody>
      </table>
      <br>
      <div style="text-align: center; width: 100%; height: 100%;">
        <b-button style="width: 49%;" size="md" variant="success" @click="ingresaNum(capturaIndex)">Ingresar</b-button>
        <b-button style="width: 49%;" size="md" variant="danger" @click="cerrrarNum()">Cerrar</b-button>
      </div>
    </b-modal>

    <b-modal ref="modalIngDesc" id="ingDesc" size="sm" hide-footer class="ingDesc" modal-class="pos-calculator-modal" title="Descuento" hide-header-close>
      <label class="pos-calculator-product"><i class="fas fa-percentage"></i><span>Producto</span><b>{{ objProd.Producto }}</b></label>
      <input type="text" inputmode="none" @keypress="(e) => { verificar(e) }" v-model="cantidadInput"
        style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control" />
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
            <td class="botones" @click="addPoint('.');">.</td>
            <td class="botones" @click="deleteNumber();"><i class="fas fa-backspace"></i></td>
          </tr>
        </tbody>
      </table>
      <br>
      <div style="text-align: center; width: 100%; height: 100%;">
        <b-button style="width: 49%;" size="md" variant="success" @click="ingresaNum(capturaIndex)">Ingresar</b-button>
        <b-button style="width: 49%;" size="md" variant="danger" @click="cerrrarNum()">Cerrar</b-button>
      </div>
    </b-modal>

    <b-modal ref="modalTouchNumeric" id="touchNumeric" size="sm" hide-footer
      modal-class="pos-calculator-modal pos-touch-input-modal"
      :title="touchInputTitle" hide-header-close>
      <label class="pos-calculator-product">
        <i :class="touchInputTarget === 'cliente' ? 'fas fa-id-card' : 'fas fa-hand-holding-usd'"></i>
        <span>{{ touchInputTarget === 'cliente' ? 'Documento del cliente' : 'Importe recibido' }}</span>
        <b>{{ touchInputTarget === 'cliente' ? 'Ingrese DNI o RUC' : 'Ingrese el monto pagado' }}</b>
      </label>
      <input type="text" inputmode="none" v-model="cantidadInput"
        class="form-control" readonly />
      <div v-if="touchInputTarget === 'pagocon'" class="pos-touch-change-preview">
        <div>
          <span>Total a pagar</span>
          <strong>S/ {{ parseFloat(getTotal(ventaActiva) || 0).toFixed(2) }}</strong>
        </div>
        <div :class="{ 'has-change': parseFloat(cantidadInput || 0) >= parseFloat(getTotal(ventaActiva) || 0) }">
          <span>{{ parseFloat(cantidadInput || 0) >= parseFloat(getTotal(ventaActiva) || 0) ? 'Vuelto' : 'Falta' }}</span>
          <strong>
            S/ {{ Math.abs(parseFloat(cantidadInput || 0) - parseFloat(getTotal(ventaActiva) || 0)).toFixed(2) }}
          </strong>
        </div>
        <button type="button" @click="cantidadInput = parseFloat(getTotal(ventaActiva) || 0).toFixed(2)">
          <i class="fas fa-bullseye"></i> Monto exacto
        </button>
      </div>
      <table class="calc__numbers calc__numbers2">
        <tbody>
          <tr>
            <td class="botones" @click="addNumber('7')">7</td>
            <td class="botones" @click="addNumber('8')">8</td>
            <td class="botones" @click="addNumber('9')">9</td>
          </tr>
          <tr>
            <td class="botones" @click="addNumber('4')">4</td>
            <td class="botones" @click="addNumber('5')">5</td>
            <td class="botones" @click="addNumber('6')">6</td>
          </tr>
          <tr>
            <td class="botones" @click="addNumber('1')">1</td>
            <td class="botones" @click="addNumber('2')">2</td>
            <td class="botones" @click="addNumber('3')">3</td>
          </tr>
          <tr>
            <td class="botones" @click="addNumber('0')">0</td>
            <td class="botones" :class="{ 'is-disabled': touchInputTarget === 'cliente' }"
              @click="touchInputTarget !== 'cliente' && addPoint('.')">.</td>
            <td class="botones" @click="deleteNumber()"><i class="fas fa-backspace"></i></td>
          </tr>
        </tbody>
      </table>
      <div class="pos-touch-keyboard-actions">
        <b-button variant="success" @click="confirmarTecladoTouch">
          <i class="fas fa-check mr-1"></i> Confirmar
        </b-button>
        <b-button variant="danger" @click="cerrarTecladoTouch">
          <i class="fas fa-times mr-1"></i> Cerrar
        </b-button>
      </div>
    </b-modal>

    <b-modal ref="modalTouchAlpha" id="touchAlpha" size="lg" hide-footer hide-header-close
      modal-class="pos-alpha-keyboard-modal" title="Teclado táctil">
      <div class="pos-alpha-heading">
        <span class="pos-alpha-heading-icon"><i class="fas fa-keyboard"></i></span>
        <div>
          <strong>Escribir nombre o número</strong>
          <small>{{ touchAlphaTargetLabel }}</small>
        </div>
      </div>
      <div class="pos-alpha-display">
        <input type="text" inputmode="none" autocomplete="off" v-model="touchAlphaValue"
          placeholder="Escriba con el teclado táctil" readonly>
        <button type="button" title="Borrar último carácter" @click="borrarTeclaAlpha">
          <i class="fas fa-backspace"></i>
        </button>
      </div>
      <div class="pos-alpha-keyboard">
        <div v-for="(row, rowIndex) in touchAlphaRows" :key="'alpha-row-' + rowIndex"
          class="pos-alpha-row"
          :style="{ gridTemplateColumns: 'repeat(' + row.length + ', minmax(0, 1fr))' }">
          <button v-for="key in row" :key="key" type="button" @click="presionarTeclaAlpha(key)">
            {{ touchAlphaUppercase ? key : key.toLowerCase() }}
          </button>
        </div>
        <div class="pos-alpha-tools">
          <button type="button" :class="{ active: touchAlphaUppercase }"
            @click="touchAlphaUppercase = !touchAlphaUppercase"><i class="fas fa-arrow-up"></i> Mayús</button>
          <button type="button" class="pos-alpha-space" @click="presionarTeclaAlpha(' ')">
            <i class="fas fa-grip-lines"></i> Espacio
          </button>
          <button type="button" class="pos-alpha-clear" @click="touchAlphaValue = ''">
            <i class="fas fa-eraser"></i> Limpiar
          </button>
        </div>
      </div>
      <div class="pos-alpha-actions">
        <b-button variant="outline-secondary" @click="$refs.modalTouchAlpha.hide()">
          <i class="fas fa-times mr-1"></i> Cerrar
        </b-button>
        <b-button variant="primary" @click="confirmarTecladoAlfanumerico">
          <i class="fas fa-check mr-1"></i> Aplicar texto
        </b-button>
      </div>
    </b-modal>

    <!-- MODALS TOUCH -->
    <producto-form ref="productoFormEdit" :producto-update="productoSelect"
      @productoactualizado="actualizarRegistro"></producto-form>

    <b-modal id="modalCodigoBarra" size="lg" ref="modalCodigoBarra" hide-footer
      title="ESCANEE EL CODIGO DE BARRA" @shown="iniciarCodigoBarra" @hidden="cerrarCodigoBarra">
        <div class="d-block text-center">
          <div id="codigoBarraStyle">
            <barcode-camera-scanner
              v-if="modalAbiertCode"
              :onDetected="onBarcodeScanned"
              :readerSize="readerSize"
              :readerTypes="detecteds"
              class="cameraBarra"
            ></barcode-camera-scanner>
            <div class="pos-camera-guide" aria-hidden="true"><span></span></div>
            <p class="pos-camera-help">Acerque la etiqueta, centre todas las barras y evite reflejos.</p>
          </div>
        </div>
      </b-modal>

    <b-modal ref="modalDocumentosVenta" id="modalDocumentosVenta" size="xl" hide-footer centered
      title="Comprobantes emitidos" modal-class="pos-documents-modal" dialog-class="pos-documents-dialog">
      <iframe class="pos-documents-frame"
        src="/nuevo/index.html?v=20260912-pos-documentos-2#/reimpresion"
        title="Listado de comprobantes emitidos" frameborder="0"></iframe>
    </b-modal>

    <div class="pos-floating-documents">
    <button type="button" class="pos-documents-button" title="Ver comprobantes y reimpresiones"
      aria-label="Abrir reimpresiÃ³n y envÃ­o de comprobantes" @click="$refs.modalDocumentosVenta.show()">
      <i class="fas fa-file-invoice"></i>
    </button>
    <button
      v-if="ultimaVentaId"
      type="button"
      class="pos-last-ticket"
      title="Reimprimir la última venta"
      aria-label="Reimprimir la última venta"
      @click="reimprimirUltimaVenta"
    >
      <i class="fas fa-receipt"></i>
      <span>Último ticket</span>
    </button>
    </div>

  </b-container>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import ClientesForm from "@/components/ClientesForm";
import Proformas from "@/components/ProformaLista.vue";
import BusquedaModelo from "@/components/BusquedaModelo.vue";
import PagoDeuda from "@/components/PagoDeuda";
import PreOrdenLista from "@/components/PreOrdenLista.vue";
// import DeliveryModal from "@/components/DeliveryModal.vue";
import ConfigVenta from "@/components/ConfigVenta";
import ProductoForm from "@/components/ProductoForm";
import AutoComplete from "@/components/AutoComplete.vue";
import Movimiento from "@/components/Movimiento";
import BarcodeCameraScanner from "@/components/BarcodeCameraScanner.vue";
import sound from "../../static/beep.mp3";
import Vue from "vue";

export default {
  components: {
    BarcodeCameraScanner,
    ClientesForm,
    Proformas,
    PagoDeuda,
    Movimiento,
    PreOrdenLista,
    BusquedaModelo,
    // DeliveryModal,
    ConfigVenta,
    ConfigVenta,
    ProductoForm,
    AutoComplete
  },
  created() {
    // Add barcode scan listener and pass the callback function
    this.$barcodeScanner.init(this.onBarcodeScanned);
  },
  data() {
    return {
      readerSize: {
        width: 1280,
        height: 720
      },
      detecteds: [
        "code_128_reader",
        "ean_reader",
        "ean_8_reader",
        "upc_reader",
        "upc_e_reader"
      ],
      tiposVentaMovil: [],
      inProgress: false,
      ultimaVentaId: window.localStorage.getItem("ultimaVentaId") || null,
      mostrarNav: true,
      isNota: [],
      itemsMotivoNota: [
        { value: null, text: "-- SELECCIONE EL MOTIVO --" },
        { value: "01", text: "ANULACION DE LA OPERACION" },
        { value: "02", text: "ANULACION POR ERROR EN EL RUC" },
        { value: "03", text: "CORRECION POR ERROR EN LA DESCRIPCION" },
        { value: "04", text: "DESCUENTO GLOBAL" },
        { value: "05", text: "DESCUENTO POR ITEM" },
        { value: "06", text: "DEVOLUCION TOTAL" },
        { value: "07", text: "DEVOLUCION POR ITEM" },
        { value: "08", text: "BONIFICACION" },
        { value: "09", text: "DISMINUCION EN EL VALOR" },
        { value: "10", text: "OTROS CONCEPTOS" }
      ],
      productoNombre: "",
      productoUrl: "",
      productoUrls: [],
      productoImagenActiva: 0,
      isVentas: true,
      initValueCliente: "00000000 CLIENTE VARIOS",
      touchHab: true,
      newDniRuc: "",
      API: config.API,
      categorias: [],
      productosCategoria: [],
      isNota: [],
      tieneIgv: [],
      headerTable: [
        { key: "stock", label: "Stock", variant: "info", class: "text-center" },
        { key: "CodigoBarra" },
        { key: "ProductoCategoria", label: "Categoría" },
        { key: "Producto" },
        { key: "Descripcion", label: "Descripción" },
        { key: "ProductoModelo", label: "Modelo" },
        { key: "ProductoMarca", label: "Marca" },
        { key: "pres", label: "Formatos", class: "text-center" },
        { key: "cantidad" },
        { key: "precio", label: "Precio" },
        {
          key: "subtotal",
          label: "SubTotal",
          variant: "success",
          class: "text-center"
        },
        { key: "descuento", label: "Descuento" },
        {
          key: "total",
          label: "Total",
          variant: "danger",
          class: "text-center"
        },
        { key: "acciones", label: "" }
      ],
      busqueda: "",
      query: "",
      isListening: false,
      recognition: null,
      loadCode: "",
      result: "",
      format: "",
      modalAbiertCode: false,
      tabActive: 0,
      ventaActiva: 0,
      paymentPopoverIndex: null,
      saleOptionsPopoverIndex: null,
      mostrarMetodosMovil: false,
      mostrarDatosBoletaMovil: true,
      venta: {
        pagocon: 0,
        cliente: {},
        EsCredito: 0,
        Serie: "",
        Numero: "",
        Observacion: "",
        Placa: "",
        comisionista: {},
        puntosAplicados: 0,
        descuentoPuntos: {},
        descuentoGeneral: 0,
        tipoVenta: {
          IdTipoDoc: 7,
          TipoDoc: "BOLETA ELEC.",
          LimitItems: 0,
          CodSunat: "03"
        },
        almacen: {
          IdAlmacen: 1,
          Almacen: "PRINCIPAL",
          DescuentoActivo: 0
        },
        puntoVenta: {
          IdDocVentaPuntoVenta: 1,
          PuntoVenta: "PRINCIPAL"
        },
        vendedor: {},
        FechaCredito: this.$moment().format("YYYY-MM-DD"),
        FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
        pago: {
          efectivo: { IdMetodoPago: 1, monto: 0 },
          visa: { IdMetodoPago: 2, monto: 0 },
          mastercard: { IdMetodoPago: 3, monto: 0 },
          delivery: { IdMetodoPago: 4, monto: 0 }
        },
        idProductos: [],
        NroComprobanteModifica: "",
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: "",
        EsCreditoProforma: 0,
        FechaCreditoProforma: this.$moment().format("YYYY-MM-DD"),
        EsDescripcion: 0,
        DescripcionProforma: "",
        DescripcionProforma2: "",
        DescripcionProforma3: "",
        DescripcionProforma4: "",
        DescripcionProforma5: "",
        IdPreOrden: null,
        CampoDireccion: "",
        EsDelivery: false,
        EsParaLlevar: 0,
        nombreLlevar: "",
        selectOpcionVenta: 0,
        DireccionEntrega: "",
        IdMetodoPago: 1,
        EsOrganizacion: 0,
        NombreOrganizacion: "",
        productos: [],
        accionBoton: 1
      },
      ventas: [
        {
          pagocon: 0,
          cliente: {},
          EsCredito: 0,
          puntosAplicados: 0,
          descuentoPuntos: {},
          descuentoGeneral: 0,
          Serie: "",
          Numero: "",
          Observacion: "",
          Placa: "",
          cliente: {},
          comisionista: {},
          tipoVenta: {
            IdTipoDoc: 7,
            TipoDoc: "BOLETA ELEC.",
            LimitItems: 0,
            CodSunat: "03"
          },
          almacen: {
            IdAlmacen: 1,
            Almacen: "PRINCIPAL",
            DescuentoActivo: 0
          },
          puntoVenta: {
            IdDocVentaPuntoVenta: 1,
            PuntoVenta: "PRINCIPAL"
          },
          vendedor: {},
          FechaCredito: this.$moment().format("YYYY-MM-DD"),
          FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
          pago: {
            efectivo: { IdMetodoPago: 1, monto: 0 },
            visa: { IdMetodoPago: 2, monto: 0 },
            mastercard: { IdMetodoPago: 3, monto: 0 },
            delivery: { IdMetodoPago: 4, monto: 0 }
          },
          idProductos: [],
          NroComprobanteModifica: "",
          CodSunatModifica: "",
          NotaIdMotivo: "",
          NotaDescMotivo: "",
          IdPreOrden: null,
          CampoDireccion: "",
          EsDelivery: false,
          EsParaLlevar: 0,
          nombreLlevar: "",
          selectOpcionVenta: 0,
          DireccionEntrega: "",
          IdMetodoPago: 1,
          EsOrganizacion: 0,
          NombreOrganizacion: "",
          productos: [],
          accionBoton: 1
        }
      ],
      bloquearInput: false,
      precioHabilitado: false,
      touchHab: false,
      opcionesVenta: [
        { text: "Normal", value: 0 },
        { text: "Delivery", value: 2 }
      ],
      modalPresentacion: false,
      observacionTemporal: "",
      placaTemporal: "",
      calculatorExpression: "",
      calculatorOpen: false,
      calculatorPosition: { x: 0, y: 0 },
      calculatorDrag: null,
      producto: {},
      index: 0,
      productoSelect: {},
      idPerfil: sessionStorage.getItem("IdPerfil"),
      objProd: {},
      cantidadInput: "",
      touchInputTarget: "",
      touchInputTitle: "Teclado numérico",
      suppressTouchKeyboard: false,
      touchAlphaValue: "",
      touchAlphaUppercase: true,
      touchAlphaTargetLabel: "Seleccione primero el campo donde desea escribir",
      touchAlphaRows: [
        ["1", "2", "3", "4", "5", "6", "7", "8", "9", "0"],
        ["Q", "W", "E", "R", "T", "Y", "U", "I", "O", "P"],
        ["A", "S", "D", "F", "G", "H", "J", "K", "L", "Ñ"],
        ["Z", "X", "C", "V", "B", "N", "M"]
      ]
    };
  },
mounted() {
    console.log("componente montado");
    this.cargarTiposVentaMovil();
    this.getVendedorInfo();
    this.getNumeroVenta();
    this.loadClienteVarios();
    this._hideProductImagePreview = () => {
      const preview = document.getElementById("pos-image-hover-preview");
      if (preview) preview.remove();
    };
    this._showProductImagePreview = image => {
      this._hideProductImagePreview();
      const preview = document.createElement("img");
      const size = 300;
      const gap = 14;
      const rect = image.getBoundingClientRect();
      let left = rect.right + gap;
      let top = rect.top + (rect.height - size) / 2;

      preview.id = "pos-image-hover-preview";
      preview.src = image.currentSrc || image.src;
      preview.alt = image.alt || "Vista ampliada del producto";
      if (left + size > window.innerWidth - 12) left = rect.left - size - gap;
      left = Math.max(12, Math.min(left, window.innerWidth - size - 12));
      top = Math.max(12, Math.min(top, window.innerHeight - size - 12));
      preview.style.left = `${left}px`;
      preview.style.top = `${top}px`;
      document.body.appendChild(preview);
    };
window.showPosProductImagePreview = this._showProductImagePreview;
window.hidePosProductImagePreview = this._hideProductImagePreview;
this._productImageMouseOverHandler = event => {
  const target = event.target && typeof event.target.closest === "function"
    ? event.target.closest(".pos-product-hover-image")
    : null;
  if (target) this._showProductImagePreview(target);
};
this._productImageMouseOutHandler = event => {
  const target = event.target && typeof event.target.closest === "function"
    ? event.target.closest(".pos-product-hover-image")
    : null;
  if (target) this._hideProductImagePreview();
};
document.addEventListener("mouseover", this._productImageMouseOverHandler, true);
document.addEventListener("mouseout", this._productImageMouseOutHandler, true);
    this._touchFocusHandler = event => {
      const element = event.target;
      if (!element || typeof element.matches !== "function") return;
      const isEditable = element.matches(
        'input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([readonly]), textarea:not([readonly])'
      );
      if (isEditable && !element.closest(".pos-alpha-keyboard-modal") &&
        !element.closest(".pos-touch-input-modal")) {
        this._lastTouchInput = element;
      }
    };
    document.addEventListener("focusin", this._touchFocusHandler);
    this._paymentOutsideHandler = event => {
      const target = event.target;
      if (!target || typeof target.closest !== "function") return;

      const dentroDelPanel = target.closest(".payment-method-popover") ||
        target.closest(".payment-method-panel");
      const botonDelPanel = target.closest(".pos-payment-trigger");
      if (!dentroDelPanel && !botonDelPanel) {
        this.paymentPopoverIndex = null;
      }

      const dentroDeOpciones = target.closest(".sale-options-popover") ||
        target.closest(".sale-options-panel");
      const botonDeOpciones = target.closest(".pos-options-trigger");
      if (!dentroDeOpciones && !botonDeOpciones) {
        this.saleOptionsPopoverIndex = null;
      }
    };
    document.addEventListener("pointerdown", this._paymentOutsideHandler, true);
    this._barcodeWindowFocusHandler = () => this.enfocarCodigoBarra(true);
    window.addEventListener("focus", this._barcodeWindowFocusHandler);
    this._barcodeModalHiddenHandler = () => {
      window.setTimeout(() => this.enfocarCodigoBarra(true), 180);
    };
    this.$root.$on("bv::modal::hidden", this._barcodeModalHiddenHandler);
    this._barcodeFocusTimer = window.setInterval(() => {
      this.enfocarCodigoBarra(false);
    }, 1200);
    this.$nextTick(() => this.enfocarCodigoBarra(true));
    // this.$nextTick(() => {
    //   setTimeout(() => {
    //     let inputElement = `autocompleteProducto${this.ventaActiva}`;
    //     let autocompleteInput = document.getElementById(inputElement);
    //     autocompleteInput.focus();
    //   }, 500);
    // });
  },
  watch: {
    ventaActiva() {
      this.$nextTick(() => {
        window.setTimeout(() => this.enfocarCodigoBarra(true), 120);
      });
    }
  },
beforeDestroy() {
    if (this._hideProductImagePreview) this._hideProductImagePreview();
    document.removeEventListener("mouseover", this._productImageMouseOverHandler, true);
    document.removeEventListener("mouseout", this._productImageMouseOutHandler, true);
    if (window.showPosProductImagePreview === this._showProductImagePreview) {
      delete window.showPosProductImagePreview;
    }
    if (window.hidePosProductImagePreview === this._hideProductImagePreview) {
      delete window.hidePosProductImagePreview;
    }
    document.removeEventListener("focusin", this._touchFocusHandler);
    document.removeEventListener("pointerdown", this._paymentOutsideHandler, true);
    window.removeEventListener("focus", this._barcodeWindowFocusHandler);
    window.removeEventListener("pointermove", this.moverCalculadora);
    window.removeEventListener("pointerup", this.terminarArrastreCalculadora);
    this.$root.$off("bv::modal::hidden", this._barcodeModalHiddenHandler);
    window.clearInterval(this._barcodeFocusTimer);
  },
  methods: {
    ...mapActions({
    aplicarDescuento: 'aplicarDescuentoGeneral',
    limpiarCart: 'limpiarCarrito',
    obtenerStok: 'obtenerStock',
    add:'agregarProducto'
    }),
    toggleDescripcionProductoMovil(producto) {
      this.$set(
        producto,
        "_mostrarDescripcionMovil",
        !producto._mostrarDescripcionMovil
      );
    },
    cambiarCantidadProductoMovil(producto, variacion) {
      if (!producto || producto.esPresentacion) return;
      const cantidadActual = Number(producto.cantidad);
      const cantidadBase = Number.isFinite(cantidadActual) ? cantidadActual : 0;
      const nuevaCantidad = Math.max(1, Math.round((cantidadBase + variacion) * 1000) / 1000);
      this.$set(producto, "cantidad", nuevaCantidad);
      this.actualizarDetalleConDescuento(producto);
    },
    formatearStockMovil(stock) {
      const cantidad = Number(stock);
      if (!Number.isFinite(cantidad)) return "0";
      return Number.isInteger(cantidad) ? String(cantidad) : cantidad.toFixed(2);
    },
    abrirPagoDeudaMovil() {
      this.mostrarMetodosMovil = false;
      this.$nextTick(() => {
        const botonPagoDeuda = this.$el.querySelector(".pos-debt-trigger .btn-pos");
        if (botonPagoDeuda) botonPagoDeuda.click();
      });
    },
    crearProformaMovil() {
      this.mostrarMetodosMovil = false;
      this.guardarProforma();
    },
    precioProductoBloqueado(producto) {
      return !this.puedeEditarPrecioVenta || Boolean(producto && producto.bloquearInputPrecio);
    },
    abrirProformasMovil() {
      const referencias = this.$refs.proformasLista;
      const listaActiva = Array.isArray(referencias)
        ? (referencias[this.ventaActiva] || referencias[0])
        : referencias;

      if (listaActiva && typeof listaActiva.abrir === "function") {
        listaActiva.abrir();
        return;
      }

      const botonProformas = this.$el.querySelector(".pos-proformas-trigger .btn-pos");
      if (botonProformas) botonProformas.click();
      else this.$toasted.error("No se pudo abrir el listado de proformas");
    },
    abrirTicket(idVenta) {
      if (!idVenta) return;
      const ticketWidth = 460;
      const ticketHeight = Math.min(760, window.screen.availHeight - 80);
      const ticketLeft = Math.max(0, window.screenX + (window.outerWidth - ticketWidth) / 2);
      const ticketTop = Math.max(0, window.screenY + (window.outerHeight - ticketHeight) / 2);
      const ticketWindow = window.open(
        "/imprimir/index.php?IdDocVenta=" + encodeURIComponent(idVenta) + "&preview=1&popup=1",
        "ticket_impresion",
        "popup=yes,width=" + ticketWidth + ",height=" + ticketHeight + ",left=" + Math.round(ticketLeft) + ",top=" + Math.round(ticketTop) + ",resizable=yes,scrollbars=yes,status=no,location=no,toolbar=no,menubar=no"
      );
      if (ticketWindow) ticketWindow.focus();
      else this.$toasted.error("Permita las ventanas emergentes para imprimir el ticket");
    },
    imprimirTicketEnNavegador(idVenta) {
      if (!idVenta) return Promise.resolve();

      const iframeAnterior = document.getElementById("pos-ticket-print-frame");
      if (iframeAnterior) iframeAnterior.remove();

      const iframe = document.createElement("iframe");
      iframe.id = "pos-ticket-print-frame";
      iframe.title = "Impresion de respaldo del ticket";
      iframe.setAttribute("aria-hidden", "true");
      iframe.style.position = "fixed";
      iframe.style.right = "0";
      iframe.style.bottom = "0";
      iframe.style.width = "1px";
      iframe.style.height = "1px";
      iframe.style.border = "0";
      iframe.style.opacity = "0";
      iframe.src =
        "/imprimir/index.php?IdDocVenta=" +
        encodeURIComponent(idVenta) +
        "&popup=1";
      return new Promise(resolve => {
        let terminado = false;
        const finalizar = () => {
          if (terminado) return;
          terminado = true;
          resolve();
        };

        iframe.addEventListener("load", () => {
          window.setTimeout(finalizar, 300);
        });
        document.body.appendChild(iframe);

        window.setTimeout(finalizar, 10000);
        window.setTimeout(() => {
          if (iframe.parentNode) iframe.parentNode.removeChild(iframe);
        }, 60000);
      });
    },
    async imprimirVentaDirecta(idVenta) {
      if (!idVenta) return false;

      try {
        const respuesta = await this.axios.post(
          "/imprimir/usb.php",
          {
            IdDocVenta: idVenta,
            PrinterName: "bar"
          },
          { timeout: 20000 }
        );

        if (!respuesta.data || respuesta.data.ok !== true) {
          throw new Error(
            (respuesta.data && respuesta.data.mensaje) ||
              "La impresora BAR no confirmo la impresion."
          );
        }

        this.$toasted.success("Ticket enviado a la impresora BAR");
        return true;
      } catch (error) {
        const mensaje =
          error && error.response && error.response.data
            ? error.response.data.mensaje
            : error && error.message;
        console.error("No se pudo imprimir directamente en BAR:", mensaje || error);
        this.$toasted.error(
          "No se pudo imprimir en BAR. Se abrira la impresion de respaldo."
        );
        await this.imprimirTicketEnNavegador(idVenta);
        return false;
      }
    },
    reimprimirUltimaVenta() {
      this.abrirTicket(this.ultimaVentaId);
    },
    togglePaymentPopover(index) {
      this.ventaActiva = index;
      this.paymentPopoverIndex = this.paymentPopoverIndex === index ? null : index;
    },
    toggleSaleOptionsPopover(index) {
      this.ventaActiva = index;
      this.saleOptionsPopoverIndex = this.saleOptionsPopoverIndex === index ? null : index;
    },
    resumenDatoVenta(valor, textoVacio) {
      const texto = String(valor || "").trim();
      if (!texto) return textoVacio;
      return texto.length > 42 ? texto.slice(0, 39) + "..." : texto;
    },
    abrirDatoVenta(tipo) {
      const venta = this.ventas[this.ventaActiva];
      if (!venta) return;
      this.saleOptionsPopoverIndex = null;

      if (tipo === "placa") {
        this.placaTemporal = String(venta.Placa || "");
        this.$nextTick(() => this.$refs.modalPlacaVenta.show());
        return;
      }

      this.observacionTemporal = String(venta.Observacion || "");
      this.$nextTick(() => this.$refs.modalObservacionVenta.show());
    },
    normalizarPlacaTemporal(valor) {
      const placa = String(valor || "")
        .replace(/[^A-Za-z0-9-]/g, "")
        .slice(0, 20);
      if (placa !== this.placaTemporal) this.placaTemporal = placa;
    },
    guardarObservacionVenta() {
      const venta = this.ventas[this.ventaActiva];
      if (!venta) return;
      const observacion = String(this.observacionTemporal || "")
        .trim()
        .slice(0, 500);
      this.$set(venta, "Observacion", observacion);
      this.$refs.modalObservacionVenta.hide();
      this.$toasted.success(observacion ? "Observaciones guardadas" : "Observaciones eliminadas");
    },
    guardarPlacaVenta() {
      const venta = this.ventas[this.ventaActiva];
      if (!venta) return;
      const placa = String(this.placaTemporal || "")
        .trim()
        .replace(/[^A-Za-z0-9-]/g, "")
        .slice(0, 20);
      this.$set(venta, "Placa", placa);
      this.placaTemporal = placa;
      this.$refs.modalPlacaVenta.hide();
      this.$toasted.success(placa ? "Placa guardada" : "Placa eliminada");
    },
    seleccionarContenidoPago(event) {
      const input = event && event.target;
      if (input && input.tagName === "INPUT" && typeof input.select === "function") {
        this.$nextTick(() => input.select());
      }
    },
    toggleCalculadora(event) {
      if (this.calculatorOpen) {
        return;
      }
      const rect = event.currentTarget.getBoundingClientRect();
      const width = window.innerWidth <= 575 ? 288 : 320;
      const height = 390;
      this.calculatorPosition = {
        x: Math.max(12, Math.min(window.innerWidth - width - 12, rect.left - width - 16)),
        y: Math.max(12, Math.min(window.innerHeight - height - 12, rect.top - 120))
      };
      this.calculatorOpen = true;
    },
    cerrarCalculadora() {
      this.calculatorOpen = false;
      this.terminarArrastreCalculadora();
    },
    iniciarArrastreCalculadora(event) {
      if (event.button !== undefined && event.button !== 0) return;
      this.calculatorDrag = {
        offsetX: event.clientX - this.calculatorPosition.x,
        offsetY: event.clientY - this.calculatorPosition.y
      };
      window.addEventListener("pointermove", this.moverCalculadora);
      window.addEventListener("pointerup", this.terminarArrastreCalculadora);
      event.preventDefault();
    },
    moverCalculadora(event) {
      if (!this.calculatorDrag) return;
      const width = window.innerWidth <= 575 ? 288 : 320;
      const height = 390;
      this.calculatorPosition = {
        x: Math.max(8, Math.min(window.innerWidth - width - 8, event.clientX - this.calculatorDrag.offsetX)),
        y: Math.max(8, Math.min(window.innerHeight - height - 8, event.clientY - this.calculatorDrag.offsetY))
      };
    },
    terminarArrastreCalculadora() {
      this.calculatorDrag = null;
      window.removeEventListener("pointermove", this.moverCalculadora);
      window.removeEventListener("pointerup", this.terminarArrastreCalculadora);
    },
    calculatorInput(value) {
      if (this.calculatorExpression === "Error") this.calculatorExpression = "";
      this.calculatorExpression += value;
    },
    calculatorClear() {
      this.calculatorExpression = "";
    },
    calculatorBackspace() {
      if (this.calculatorExpression === "Error") {
        this.calculatorExpression = "";
        return;
      }
      this.calculatorExpression = this.calculatorExpression.slice(0, -1);
    },
    enfocarCodigoBarra(forzar = false) {
      if (document.querySelector(".modal.show")) return;

      const active = document.activeElement;
      const usuarioEscribiendo = active && (
        active.matches("input, textarea, select") ||
        active.isContentEditable
      );
      if (!forzar && usuarioEscribiendo) return;

      const input = document.getElementById(`txtCodeBar${this.ventaActiva}`);
      if (!input || input.disabled || input.offsetParent === null) return;
      input.focus({ preventScroll: true });
      if (typeof input.select === "function") input.select();
    },
    calculatorResult() {
      const expression = String(this.calculatorExpression || "").trim();
      if (!expression || !/^[0-9+\-*/().\s]+$/.test(expression)) {
        this.calculatorExpression = "Error";
        return;
      }
      try {
        const result = Function('"use strict"; return (' + expression + ')')();
        this.calculatorExpression = Number.isFinite(result)
          ? String(Math.round((result + Number.EPSILON) * 100) / 100)
          : "Error";
      } catch (error) {
        this.calculatorExpression = "Error";
      }
    },
    abrirCodigoBarra() {
      this.prepararSonidoEscaneo();
      // El video necesita un contenedor visible y con dimensiones reales.
      // Bootstrap termina de mostrar el modal antes de montar el escaner.
      this.modalAbiertCode = false;
      this.result = "";
      this.$nextTick(() => this.$refs["modalCodigoBarra"].show());
    },
    iniciarCodigoBarra() {
      this.$nextTick(() => {
        this.modalAbiertCode = true;
      });
    },
    prepararSonidoEscaneo() {
      if (!this._scanAudio) {
        this._scanAudio = new Audio(sound);
        this._scanAudio.preload = "auto";
      }
      const audio = this._scanAudio;
      audio.volume = 0;
      const desbloqueo = audio.play();
      if (desbloqueo && typeof desbloqueo.then === "function") {
        desbloqueo.then(() => {
          audio.pause();
          audio.currentTime = 0;
          audio.volume = 0.85;
        }).catch(() => { audio.volume = 0.85; });
      }
    },
    cerrarCodigoBarra() {
      this.modalAbiertCode = false;
      window.setTimeout(() => this.enfocarCodigoBarra(true), 120);
    },
    onBarcodeScanned(barcode) {
      if (this.modalAbiertCode) {
        const codeResult = barcode && barcode.codeResult ? barcode.codeResult : null;
        this.result = String(codeResult ? codeResult.code : barcode || "").trim();
        this.format = codeResult ? codeResult.format : "scanner";
        const audio = this._scanAudio || new Audio(sound);
        audio.pause();
        audio.currentTime = 0;
        audio.volume = 0.85;
        audio.play().catch(() => {});
        if (window.navigator && typeof window.navigator.vibrate === "function") {
          window.navigator.vibrate(70);
        }
        if (this.result.length > 0) {
          this.loadCode = this.result;
          this.loadBarcode();
          this.$refs["modalCodigoBarra"].hide();
          this.modalAbiertCode = false;
        }
      }
    },
    testImpresora() {
      var left = screen.width / 2 - 800 / 2;
      var top = screen.height / 2 - 800 / 2;
      let params =
        "scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=300,height=300,top=" +
        top +
        ", left=" +
        left;
      window.open(
        "http://localhost:5000/imprimir/test.php",
        "Impresión",
        params
      );
    },
    async actualizarRegistro(event) {
      const idProducto = event && typeof event === "object"
        ? event.IdProducto
        : event;
      if (!idProducto) return;

      const productos = this.ventas[this.ventaActiva].productos;
      const index = productos.findIndex(v => v.IdProducto == idProducto);
      if (index < 0) return;

      const productoActual = productos[index];
      let prod = await this.obtenerStockBuscador(idProducto);
      if (!prod || !prod.IdProducto) {
        this.$toasted.show("No se pudo recargar el producto actualizado");
        return;
      }
      let pres = [];
      if (prod.PreciosPorProducto) {
        try {
          let newPres = JSON.parse(prod.PreciosPorProducto);
          pres = newPres.filter(v => v.cantidad > 0);
        } catch (error) {
          pres = [];
        }
      }
      prod.PreciosPorProducto = pres;
      prod.presentacion = prod.ProductoPresentacion
        ? prod.ProductoPresentacion
        : "UNIDAD";
      prod.ProductoPresentacion = prod.ProductoPresentacion
        ? prod.ProductoPresentacion
        : "UNIDAD";
      prod.cantidad = Number(productoActual.cantidad) || 1;
      const precioActualizado = Number(prod.PrecioContado);
      prod.precio = Number.isFinite(precioActualizado)
        ? precioActualizado
        : (Number(productoActual.precio) || 0);
      const porcentajeAnterior = Number(productoActual.PorcentajeLiquidacion) || 0;
      const porcentajeActualizado = Number(prod.PorcentajeLiquidacion) || 0;
      const cambioPorcentajeDescuento = porcentajeAnterior !== porcentajeActualizado;
      prod.descuento = cambioPorcentajeDescuento
        ? 0
        : (Number(productoActual.descuento) || 0);
      prod.tienePresentacion = prod.PreciosPorProducto.some(
        v => v.cantidad > 0
      );
      prod.bloquearInputPrecio = false;
      prod.CantidadPresentacion = 1;
      prod.esPresentacion = false;
      prod.pres = "";
      if (cambioPorcentajeDescuento && porcentajeActualizado > 0) {
        this.aplicarDescuentoProducto(prod);
      }

      Vue.set(productos, index, prod);
    },
    ocultarNav() {
      this.mostrarNav = !this.mostrarNav;
      if (!this.mostrarNav) {
        window.parent.document.getElementById("navBar").style.display = "none";
      } else {
        window.parent.document.getElementById("navBar").style.display = "block";
      }
    },
    agregarObj(obj) {
      this.productoSelect.precio = obj.PrecioContado;
      const productos = [...this.ventas[0].productos];
      productos[this.indexPos] = this.productoSelect;
      this.ventas[0].productos = productos;
      this.$nextTick(() => {
        this.$forceUpdate();
      });
    },
    irCodeBar() {
      let txtCodeBar = `txtCodeBar${this.ventaActiva}`;
      document.getElementById(txtCodeBar).focus();
    },
    irBusquedaProducto() {
      let txtCodeBar = `autocompleteProducto${this.ventaActiva}`;
      document.getElementById(txtCodeBar).focus();
      document.getElementById(txtCodeBar).select();
    },
    setDocRef(obj) {
    this.ventas[this.ventaActiva].CodSunatModifica = obj.CodSunat;
    this.ventas[this.ventaActiva].NroComprobanteModifica = obj.NroComprobante;

    if (
        this.ventas[this.ventaActiva].tipoVenta.CodSunat == "07" ||
        this.ventas[this.ventaActiva].tipoVenta.CodSunat == "08"
    ) {
        this.ventas[this.ventaActiva].Serie = this.ventas[
            this.ventaActiva
        ].Serie.replace(/^./, obj.NroComprobante[0]);
    }

    this.axios
        .get(this.API + "ventas/solonumero", {
            params: {
                serie: this.ventas[this.ventaActiva].Serie,
                idTipoDoc: this.ventas[this.ventaActiva].tipoVenta.IdTipoDoc
            }
        })
        .then(response => {
            this.ventas[this.ventaActiva].Numero = response.data.numero;
        })
        .catch(error => {
            console.log(error);
            this.$toasted.show("Ha ocurrido un error al obtener el número de venta");
        });

    this.axios
        .get(this.API + "venta/detalle/comprobante", {
            params: {
                NroComprobante: obj.NroComprobante,
                idDocVenta: obj.IdDocVenta,
                IdDocVentaPuntoVenta: this.ventas[this.ventaActiva].puntoVenta.IdDocVentaPuntoVenta
            }
        })
        .then(async response => {
            const productosDelComprobante = response.data;

            if (!productosDelComprobante || productosDelComprobante.length === 0) {
                this.$toasted.show("No se encontraron productos en el comprobante");
                return;
            }

            // ✅ Cargar cliente directo sin pasar por setCliente() para no limpiar descuentos
            try {
                const idCliente = productosDelComprobante[0].IdCliente;
                const resCliente = await this.axios.get(this.API + "cliente", {
                    params: { idCliente: idCliente }
                });
                if (resCliente.data) {
                    this.ventas[this.ventaActiva].cliente = resCliente.data;
                    this.ventas[this.ventaActiva].CampoDireccion = resCliente.data.Direccion;
                    this.initValueCliente = resCliente.data.Cliente;
                } else {
                    this.$toasted.show("No se encontró al cliente");
                }
            } catch (error) {
                console.log("Error cargando cliente:", error);
                this.$toasted.show("Error al cargar el cliente del comprobante");
            }

            // ✅ Limpiar productos actuales
            this.ventas[this.ventaActiva].productos = [];

            // ✅ Cargar cada producto con su precio y descuento original
            for (const element of productosDelComprobante) {
                let prod = {};

                // Obtener stock del producto
                try {
                    await this.axios(this.API + "productos/puntoventa/stock", {
                        params: {
                            idProductos: [element.IdProducto],
                            idAlmacen: this.ventas[this.ventaActiva].almacen.IdAlmacen || 1
                        }
                    }).then(res => {
                        prod = res.data[0];
                    });
                } catch (error) {
                    console.log("Error cargando stock:", error);
                    this.$toasted.show(`Error cargando stock del producto ${element.IdProducto}`);
                }

                // Si no se obtuvo el producto, saltar
                if (!prod || !prod.IdProducto) continue;

                // Preparar presentaciones
                let pres = [];
                if (prod.PreciosPorProducto) {
                    try {
                        pres = JSON.parse(prod.PreciosPorProducto).filter(v => v.cantidad > 0);
                    } catch (e) {
                        pres = [];
                    }
                }

                prod.PreciosPorProducto = pres;

                // ✅ Respetar presentación original del comprobante
                prod.presentacion = element.Presentacion || prod.ProductoPresentacion || "UNIDAD";
                prod.ProductoPresentacion = element.Descripcion || prod.ProductoPresentacion || "UNIDAD";

                prod.tienePresentacion = pres.length > 0;
                prod.bloquearInputPrecio = false;
                prod.CantidadPresentacion = parseFloat(element.CantidadxPresentacion) || 1;
                prod.esPresentacion = false;
                prod.pres = "";

                // ✅ Asignar valores exactos del comprobante original
                prod.cantidad  = parseFloat(element.cantidad) || 1;
                prod.precio    = parseFloat(element.precio)   || prod.PrecioContado;
                prod.descuento = parseFloat(element.descuento) || 0;

                // ✅ Insertar con $set para garantizar reactividad
                this.$set(
                    this.ventas[this.ventaActiva].productos,
                    this.ventas[this.ventaActiva].productos.length,
                    prod
                );
            }

            // ✅ Resetear motivo y recalcular total con descuentos cargados
            this.ventas[this.ventaActiva].NotaIdMotivo = null;
            this.$nextTick(() => {
                this.metodoPagoCambio(1);
            });
            this.$forceUpdate();
        })
        .catch(error => {
            console.log("Error cargando detalle del comprobante:", error);
            this.$toasted.show("Error al cargar el detalle del comprobante");
        });
},
    setMotivo(ev) {
      if (ev == "01" || ev == "02" || ev == "06" || ev == null) {
        this.bloquearInput = true;
      } else {
        this.bloquearInput = false;
      }
    },
    activarModalTouch() {
      this.touchHab = !this.touchHab;
    },
    startRecognition() {
      if (this.isListening && this.recognition) {
        this.recognition.stop();
        return;
      }

      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }

      this.query = "";
      this.recognition = new webkitSpeechRecognition();
      this.recognition.lang = "es-ES";
      this.recognition.continuous = false;
      this.recognition.interimResults = false;

      this.recognition.onstart = () => {
        this.isListening = true;
        console.log("Speech recognition service has started");
      };

      this.recognition.onresult = event => {
        if (event.results.length > 0) {
          this.query = event.results[0][0].transcript;
          this.search();
        }
      };

      this.recognition.onerror = event => {
        this.isListening = false;
        console.error("Speech Recognition Error:", event);
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = async () => {
        this.isListening = false;
        if (!this.query) {
          alert("No se pudo captar ninguna palabra");
        }
      };

      try {
        this.recognition.start();
      } catch (error) {
        this.isListening = false;
        console.error("Error starting recognition:", error);
        alert("No se pudo iniciar el reconocimiento de voz.");
      }
    },
    // search() {
    //   let text = `autocompleteProducto${this.ventaActiva}`;
    //   console.log(text);
    //   let autoComplete = document.getElementById(text);
    //   // let buscarProducto = document.getElementById(text);
    //   // let autoComplete = this.$refs[text];

    //   if (!autoComplete || !autoComplete.$el) {
    //     console.error(
    //       "No se encontró el componente autocomplete o su elemento DOM."
    //     );
    //     return;
    //   }

    //   let inputElement = autoComplete.$el.querySelector("input");
    //   if (inputElement) {
    //     // Establece el valor

    //     // Dispara el evento 'input' para notificar el cambio
    //     let event = new Event("input", { bubbles: true });
    //     inputElement.value = this.query;
    //     inputElement.dispatchEvent(event);
    //   }
    // },
    search() {
      const inputEvent = new Event("input", {
        bubbles: true,
        cancelable: true
      });
      let inputElement = `autocompleteProducto${this.ventaActiva}`;
      let autocompleteInput = document.getElementById(inputElement);
      setTimeout(() => {
        document.getElementById(inputElement).value = this.query;
        autocompleteInput.dispatchEvent(inputEvent);
      }, 200);
    },
    mostrarEditarProducto() {
      if (this.idPerfil != 1) {
        return false;
      }
      if (this.ventas.length > 1) {
        return false;
      }
      return true;
    },
    seleccionarPagoCon(event) {
      if (event && event.target && typeof event.target.select === "function") {
        event.target.select();
      }
      this.abrirTecladoTouch("pagocon", this.ventas[this.ventaActiva].pagocon);
    },
    abrirTecladoTouch(target, currentValue) {
      if (!this.touchHab || this.suppressTouchKeyboard) {
        return;
      }

      this.touchInputTarget = target;
      this.touchInputTitle = target === "cliente"
        ? "Buscar cliente"
        : "Pagó con";

      const cleanValue = String(currentValue == null ? "" : currentValue)
        .replace(/[^\d.]/g, "");
      this.cantidadInput = target === "cliente"
        ? cleanValue.replace(/\./g, "")
        : (parseFloat(cleanValue) > 0 ? cleanValue : "");

      this.$nextTick(() => {
        this.$refs.modalTouchNumeric.show();
      });
    },
    confirmarTecladoTouch() {
      const value = String(this.cantidadInput || "");

      if (this.touchInputTarget === "cliente") {
        this.newDniRuc = value;
        const refName = `inputClienteTouch${this.ventaActiva}`;
        let clientInput = this.$refs[refName];
        if (Array.isArray(clientInput)) {
          clientInput = clientInput[0];
        }
        if (clientInput && typeof clientInput.setTextAndSearch === "function") {
          clientInput.setTextAndSearch(value);
        }
      } else if (this.touchInputTarget === "pagocon") {
        this.ventas[this.ventaActiva].pagocon = value === ""
          ? 0
          : parseFloat(value);
      }

      this.cerrarTecladoTouch();
    },
    cerrarTecladoTouch() {
      this.suppressTouchKeyboard = true;
      this.$refs.modalTouchNumeric.hide();
      this.cantidadInput = "";
      this.touchInputTarget = "";
      window.setTimeout(() => {
        this.suppressTouchKeyboard = false;
      }, 450);
    },
    abrirTecladoAlfanumerico() {
      const activeElement = document.activeElement;
      const activeIsEditable = activeElement && typeof activeElement.matches === "function" &&
        activeElement.matches('input:not([type="checkbox"]):not([type="radio"]):not([type="file"]), textarea');
      const target = activeIsEditable ? activeElement : this._lastTouchInput;

      this._touchAlphaTarget = target || null;
      this.touchAlphaValue = target && typeof target.value !== "undefined"
        ? String(target.value || "")
        : "";
      this.touchAlphaUppercase = true;
      this.touchAlphaTargetLabel = target
        ? (target.getAttribute("placeholder") || target.getAttribute("aria-label") ||
          target.name || "Campo seleccionado")
        : "Seleccione primero el campo donde desea escribir";
      this.$nextTick(() => this.$refs.modalTouchAlpha.show());
    },
    presionarTeclaAlpha(key) {
      this.touchAlphaValue += this.touchAlphaUppercase
        ? String(key).toUpperCase()
        : String(key).toLowerCase();
    },
    borrarTeclaAlpha() {
      this.touchAlphaValue = this.touchAlphaValue.slice(0, -1);
    },
    confirmarTecladoAlfanumerico() {
      const target = this._touchAlphaTarget;
      if (!target) {
        this.$toasted.show("Seleccione primero el campo donde desea escribir", {
          type: "info",
          duration: 2600
        });
        return;
      }

      const prototype = target.tagName === "TEXTAREA"
        ? window.HTMLTextAreaElement.prototype
        : window.HTMLInputElement.prototype;
      const descriptor = Object.getOwnPropertyDescriptor(prototype, "value");
      if (descriptor && descriptor.set) {
        descriptor.set.call(target, this.touchAlphaValue);
      } else {
        target.value = this.touchAlphaValue;
      }
      target.dispatchEvent(new Event("input", { bubbles: true }));
      target.dispatchEvent(new Event("change", { bubbles: true }));
      this.$refs.modalTouchAlpha.hide();
      this.$nextTick(() => this.enfocarCodigoBarra(true));
    },
    abrirModalTouch(index, tipo) {
      if (tipo == 3 && !this.puedeAplicarDescuentos) return;
      this.capturaIndex = index;
      this.cantidadInput = "";
      this.objProd = this.ventas[this.ventaActiva].productos[index];
      if (!this.touchHab) {
        return;
      }

      this.$refs["modalIngDesc"].hide();
      this.$refs["modalIngCant"].hide();
      this.$refs["modalIngPre"].hide();

      if (tipo == 1) {
        this.$refs["modalIngCant"].show();
      }
      if (tipo == 2) {
        this.$refs["modalIngPre"].show();
      }
      if (tipo == 3) {
        this.$refs["modalIngDesc"].show();
      }
    },
    ingresaNum() {
      if (this.$refs["modalIngCant"].is_show) {
        this.objProd.cantidad = this.cantidadInput;
        this.actualizarDetalleConDescuento(this.objProd);
      }
      if (this.$refs["modalIngPre"].is_show) {
        this.objProd.precio = this.cantidadInput;
        this.actualizarDetalleConDescuento(this.objProd);
      }
      if (this.$refs["modalIngDesc"].is_show) {
        this.actualizarDescuentoProductoManual(this.objProd, this.cantidadInput);
      }
      // Vue.set(this.ventas[this.ventaActiva].productos, this.index, this.objProd);
      // console.log("this.objProd");
      this.cantidadInput = "";
      this.$refs["modalIngCant"].hide();
      this.$refs["modalIngPre"].hide();
      this.$refs["modalIngDesc"].hide();
    },
    deleteNumber() {
      this.cantidadInput = this.cantidadInput.toString().slice(0, -1);
    },
    addNumber(number) {
      this.cantidadInput = this.cantidadInput + number;
    },
    addPoint(number) {
      const regex = /\./;
      if (!regex.test(this.cantidadInput) && this.cantidadInput.length > 0) {
        this.cantidadInput = this.cantidadInput + ".";
      }
    },
    cerrrarNum() {
      this.cantidadInput = "";
      this.$refs["modalIngCant"].hide();
      this.$refs["modalIngPre"].hide();
      this.$refs["modalIngDesc"].hide();
    },
    fechaActual() {
      return this.$moment().format("YYYY-MM-DD H:mm:ss");
    },
    async loadClienteVarios() {
      await this.axios.get(this.API + "clientes/00000000").then(res => {
        if (res.data.IdCliente) {
          this.setCliente(res.data);
        }
      });
    },
    editarProducto(Pro, index) {
      this.indexPos = index;
      this.productoSelect = Pro;
      this.$refs.productoFormEdit.show = true;
      this.$refs.productoFormEdit.nuevo = false;
    },
    getVendedorInfo() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "/usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          let almacen = {
            IdAlmacen: response.data.IdAlmacen,
            Almacen: response.data.Almacen,
            DescuentoActivo: response.data.DescuentoActivo
          };
          let puntoVenta = {
            IdDocVentaPuntoVenta: response.data.IdDocVentaPuntoVenta,
            PuntoVenta: response.data.PuntoVenta
          };
          this.APPTOKEN = response.data.APPTOKEN;
          this.setAlmacen(almacen);
          this.setPuntoVenta(puntoVenta);

          let e = `inputAlmacen${this.ventaActiva}`;
          document.getElementById(e).disabled = true;
          let p = `inputPuntoVenta${this.ventaActiva}`;
          document.getElementById(p).disabled = true;
          // this.$refs.inputVendedor.$refs.input.disabled = true;
        })
        .catch(error => {
          console.log("error", error);
          this.$toasted.show(
            "Ha ocurrido un error, vuelva a intentarlo # vendedor"
          );
        });
    },
    focusAutocomplete(ref) {
      // console.log(ref);
      let prod = "autocompleteProducto";

      let element = document.getElementById(ref);
      if (ref.includes(prod)) {
        element.value = "";
        element.dispatchEvent(new Event("input", { bubbles: true }));
      }
      const event = new MouseEvent("dblclick");
      element.dispatchEvent(event);
    },
    async cargarTiposVentaMovil() {
      try {
        const response = await this.axios.get(this.API + "ventas/tipos", {
          params: { q: "" }
        });
        const payload = response && response.data;
        this.tiposVentaMovil = Array.isArray(payload)
          ? payload
          : payload && Array.isArray(payload.data)
            ? payload.data
            : [];
      } catch (error) {
        console.error("No se pudieron cargar los documentos de venta", error);
        this.tiposVentaMovil = [this.ventas[this.ventaActiva].tipoVenta];
      }
    },
    seleccionarTipoVentaMovil(idTipoDoc) {
      const tipo = this.tiposVentaMovil.find(
        item => String(item.IdTipoDoc) === String(idTipoDoc)
      );
      if (tipo) this.setTipoVenta(tipo);
    },
    setVendedor(obj) {
      // if (obj.IdTipoDoc) this.getTipoDoc(obj.IdTipoDoc);
      this.venta.vendedor = obj;
      this.ventas[0].vendedor = obj;
    },
    setAlmacen(obj) {
      this.ventas[this.ventaActiva].almacen = obj;

      if (this.$route.query.idPreOrden && this.$route.query.idCliente) {
        this.cargarPreOrden();
        this.setCliente(this.$route.query.idCliente);
      }
    },
    renderChild(data) {
      if (this.ventas[this.ventaActiva].almacen.IdAlmacen) {
        let styleStock = "";
        if (data.stock <= 0) {
          styleStock = "table-danger";
        } else {
          styleStock = "table-success";
        }

        if (data.EsPadre == 1) {
          styleStock = "";
        }
        // console.log(data);
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
          .imagen{
            width: 42px;
            height: 42px;
            display: block;
            object-fit: contain;
            position: relative;
            z-index: 1;
            cursor: zoom-in;
            border-radius: 8px;
            background: #ffffff;
            transition: transform .18s ease, box-shadow .18s ease;
            transform-origin: right center;
            will-change: transform;
          }
          .imagen:hover{
            transform: scale(7.142857);
            z-index: 9999;
            box-shadow: 0 8px 24px rgba(8, 47, 73, .28);
          }
          #table-render,
          #table-render tbody,
          #table-render tr,
          #table-render td { overflow: visible !important; }
        </style>
        <div>
          <table id="table-render" style="width: 1300px; text-align: center;padding:0; margin:0">
            <tbody>
              <tr style="padding:0; margin:0">
                <td style="width: 60px" class="${styleStock}"><b>${
          data.EsPadre == 1
            ? '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />'
            : data.stock
              ? Number.isInteger(data.stock)
                ? data.stock
                : parseFloat(data.stock).toFixed(2)
              : data.stock
        }</b></td>
                <td style="width: 300px"><b>${data.Producto} - ${
          data.CodigoBarra
        }</b></td>
                <td style="width: 80px">${
                  data.ProductoCategoria ? data.ProductoCategoria : ""
                }</td>
                <td style="width: 80px">${
                  data.ProductoModelo ? data.ProductoModelo : ""
                }</td>
                <td style="width: 120px">${
                  data.ProductoMarca ? data.ProductoMarca : ""
                }</td>
                <td style="width: 65px" class="table-primary"><b>${
                  data.PrecioContado
                }</b></td>
                <td style="width: 5%">
                  <img src="${
                    data.Imagen == null || data.Imagen == ""
                      ? "static/no-photo.png"
                      : "/resources/images/productos/" + data.Imagen
  }" class="imagen pos-product-hover-image">
                </td>
                <!--<td style="width: 20px" class="text-center"><b>${
                  data.VentaEstrategica == 1
                    ? '<i class="fas fa-star" style="color:yellow !important" ></i>'
                    : ""
                }</b></td>-->
              </tr>
            </tbody>
          </table>   
        </div>
        `;
      }
      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${
        data.ProductoMarca
      } / ${data.ProductoModelo}
      `;
    },
    loadBarcode(e) {
      if (e && e.preventDefault) e.preventDefault();
      let idProducto = this.loadCode;

      if (idProducto) {
        let params = { codigoBarra: idProducto };
        this.axios
          .get(this.API + "productos/id/" + idProducto, { params: params })
          .then(response => {
            if (response.data.IdProducto) {
              this.setProducto(response.data);
            } else {
              this.loadCode = "";
              this.$toasted.show("No se encontró ningun producto");
            }
          });
      }
      this.loadCode = "";
    },
    setPuntoVenta(obj) {
      this.ventas[this.ventaActiva].puntoVenta = obj;
      this.getNumeroVenta();
    },

    limpiarFiltro() {
      this.busqueda = "";
      this.tabActive = 0;
    },
    anadirVentana() {
      this.initValueCliente = "00000000 CLIENTE VARIOS";

      let venta = { ...this.venta };
      venta.productos = [];
      this.ventas.push(venta);
      this.$nextTick(() => {
        this.ventaActiva = this.ventas.length - 1;
        this.getNumeroVenta();
        this.getVendedorInfo();
        this.loadClienteVarios();
      });
      let header = document.getElementById("header");
      header.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
      this.$toasted.success("Se añadió una opción más de venta");
      let p = `inputPuntoVenta${this.ventaActiva}`;
      document.getElementById(p).disabled = true;
      let e = `inputAlmacen${this.ventaActiva}`;
      document.getElementById(e).disabled = true;

      setTimeout(() => {
        this.$nextTick(() => {
          this.enfocarCodigoBarra(true);
        });
      }, 100);
    },
    eliminarCliente() {
      // this.$refs.inputCliente.setValue("");
      // this.venta.cliente = {};
    },
    eliminarComisionista() {
      // this.$refs.inputComisionista.setValue('')
      // this.venta.comisionista = {}
    },
    eliminarVentana() {
      this.ventas.splice(this.ventaActiva, 1);

      this.$nextTick(() => {
        if (this.ventaActiva != 0) {
          this.ventaActiva = this.ventaActiva - 1;
        } else {
          this.ventaActiva = 0;
        }
      });
      let header = document.getElementById("header");
      header.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
      this.$toasted.info("Opción de venta cerrada.");
    },
    setTipoVenta(obj) {
      this.isNota[this.ventaActiva] = false;
      this.bloquearInput = false;
      this.ventas[this.ventaActiva].NroComprobanteModifica = "";
      this.ventas[this.ventaActiva].CodSunatModifica = "";
      this.ventas[this.ventaActiva].NotaIdMotivo = "";
      this.ventas[this.ventaActiva].NotaDescMotivo = "";
      // this.$refs.inputDocRef && this.$refs.inputDocRef.setValue("");
      if (obj.CodSunat == "07" || obj.CodSunat == "08") {
        // 07 - nota de credito, 08 - Nota de debito
        this.isNota[this.ventaActiva] = true;
        this.ventas[this.ventaActiva].puntosAplicados = 0;
        this.ventas[this.ventaActiva].descuentoPuntos = {};
        this.ventas[this.ventaActiva].descuentoGeneral = 0;
        // Limpiar descuentos de productos
        this.ventas[this.ventaActiva].productos.forEach((prod, index) => {
          this.$set(this.ventas[this.ventaActiva].productos, index, { ...prod, descuento: 0 });
        });
        // this.limpiarCart();
        if (this.ventas[this.ventaActiva].EsDelivery) {
          // this.anularPagoDelivery();
        } else if (
          this.$route.query.idPreOrden &&
          this.$route.query.idCliente
        ) {
          // this.anularPagoOrden();
        }
      }

      if (obj.CodSunat == "01" || obj.CodSunat == "07") {
        this.ventas[this.ventaActiva].EsOrganizacion = 0;
        this.ventas[this.ventaActiva].NombreOrganizacion = "";
        // this.$refs.inputCliente.$refs.input.disabled = false;
      }

      if (Number(obj.TieneIgv) === 1) {
        this.tieneIgv[this.ventaActiva] = true;
      } else {
        this.tieneIgv[this.ventaActiva] = false;
      }
      //this.$refs.inputTipoVenta.setValue(obj.TipoDoc)

      this.ventas[this.ventaActiva].tipoVenta = obj;
      this.getNumeroVenta();
    },
    aplicarPuntos(val) {
      const venta = this.ventas[this.ventaActiva];
      let puntos = parseFloat(val) || 0;
      if (puntos > venta.cliente.Puntos) {
        puntos = venta.cliente.Puntos;
        venta.puntosAplicados = puntos;
      }
      if (puntos <= 0) {
        venta.puntosAplicados = 0;
        venta.descuentoPuntos = {};
        // Limpiar descuento de todos los productos
        venta.productos.forEach((prod, index) => {
          this.$set(venta.productos, index, { ...prod, descuento: 0 });
        });
        // Actualizar el pago
        this.$nextTick(() => {
          venta.pago.efectivo.monto = this.getTotal();
        });
        return;
      }

      this.axios.get(this.API + 'ventas/descuento/' + puntos)
        .then((response) => {
          venta.descuentoPuntos = response.data;
          const descuento = parseFloat(response.data.descuento) || 0;
          this.aplicarDescuentoAProductos(descuento);
          this.$nextTick(() => {
            venta.pago.efectivo.monto = this.getTotal();
          });
        });
    },

    aplicarDescuentoGeneral(val) {
      if (!this.puedeAplicarDescuentos || this.bloquearInput) return;

      const venta = this.ventas[this.ventaActiva];
      const subtotal = Number(this.getSubTotal(this.ventaActiva)) || 0;
      const descuentoSolicitado = Number.parseFloat(val);
      const descuento = Math.min(
        subtotal,
        Math.max(0, Number.isFinite(descuentoSolicitado) ? descuentoSolicitado : 0)
      );
      const descuentoNormalizado = Math.round(descuento * 100) / 100;

      venta.puntosAplicados = 0;
      venta.descuentoPuntos = {};
      if (descuentoNormalizado === 0) {
        venta.descuentoGeneral = 0;
        this.restaurarDescuentosAutomaticos(this.ventaActiva);
      } else {
        venta.descuentoGeneral = this.aplicarDescuentoAProductos(
          descuentoNormalizado,
          this.ventaActiva
        );
      }
      this.$nextTick(() => this.metodoPagoCambio(1, this.ventaActiva));
    },

    reaplicarDescuentoVigente(ventaIndex = null) {
      const idx = ventaIndex !== null ? ventaIndex : this.ventaActiva;
      const venta = this.ventas[idx];
      if (venta.puntosAplicados > 0 && venta.descuentoPuntos && venta.descuentoPuntos.descuento) {
        this.aplicarDescuentoAProductos(parseFloat(venta.descuentoPuntos.descuento), idx);
      } else if (parseFloat(venta.descuentoGeneral) > 0) {
        venta.descuentoGeneral = this.aplicarDescuentoAProductos(
          parseFloat(venta.descuentoGeneral),
          idx
        );
      }
      this.$nextTick(() => this.metodoPagoCambio(1, idx));
    },
    aplicarDescuentoAProductos(descuentoTotal, ventaIndex = null) {
      const idx = ventaIndex !== null ? ventaIndex : this.ventaActiva;
      const productos = this.ventas[idx].productos;
      if (!productos.length) return 0;

      const importesCentavos = productos.map(prod =>
        Math.max(0, Math.round((Number(this.getSubTotalDetalle(prod)) || 0) * 100))
      );
      const subtotalCentavos = importesCentavos.reduce((total, importe) => total + importe, 0);
      if (subtotalCentavos <= 0) return 0;

      const solicitado = Number.parseFloat(descuentoTotal);
      const descuentoCentavos = Math.min(
        subtotalCentavos,
        Math.max(0, Math.round((Number.isFinite(solicitado) ? solicitado : 0) * 100))
      );

      // Se distribuye en centavos mediante restos mayores para que la suma sea exacta.
      const distribucion = importesCentavos.map((importe, index) => {
        const exacto = descuentoCentavos * importe / subtotalCentavos;
        const base = Math.floor(exacto);
        return { index, importe, centavos: base, resto: exacto - base };
      });
      let pendientes = descuentoCentavos - distribucion.reduce(
        (total, item) => total + item.centavos,
        0
      );
      const orden = [...distribucion].sort((a, b) =>
        b.resto - a.resto || b.importe - a.importe || a.index - b.index
      );
      for (let i = 0; pendientes > 0 && orden.length; i = (i + 1) % orden.length) {
        const item = orden[i];
        if (item.centavos < item.importe) {
          item.centavos += 1;
          pendientes -= 1;
        }
      }

      distribucion.forEach(item => {
        const prod = productos[item.index];
        this.$set(productos, item.index, {
          ...prod,
          descuento: item.centavos / 100,
          _descuentoManual: false
        });
      });
      return descuentoCentavos / 100;
    },
    async cargarProductosDeModelo(productos) {
      for (const item of productos) {
        const existe = this.ventas[this.ventaActiva].productos
          .some(v => v.IdProducto == item.IdProducto);

        if (existe) {
          this.ventas[this.ventaActiva].productos
            .find(v => v.IdProducto == item.IdProducto).cantidad += parseFloat(item.cantidad);
          continue;
        }

        let prod = await this.obtenerStockBuscador(item.IdProducto);
        let pres = [];
        if (prod.PreciosPorProducto) {
          pres = JSON.parse(prod.PreciosPorProducto).filter(v => v.cantidad > 0);
        }
        prod.PreciosPorProducto = pres;
        prod.presentacion      = prod.ProductoPresentacion || 'UNIDAD';
        prod.ProductoPresentacion = prod.ProductoPresentacion || 'UNIDAD';
        prod.cantidad          = parseFloat(item.cantidad) || 1;
        prod.precio            = parseFloat(item.precio)   || prod.PrecioContado;
        prod.descuento         = parseFloat(item.descuento) || 0;
        prod.tienePresentacion = pres.some(v => v.cantidad > 0);
        prod.bloquearInputPrecio = false;
        prod.CantidadPresentacion = 1;
        prod.esPresentacion    = false;
        prod.pres              = '';
        this.ventas[this.ventaActiva].productos.unshift(prod);
      }
      this.metodoPagoCambio(1);
      this.reaplicarDescuentoVigente();
    },
    async getNumeroVenta() {
      // await setTimeout(() => {
      this.$nextTick(async () => {
        await this.axios
          .get(this.API + "ventas/numero", {
            params: {
              idPuntoVenta: this.ventas[this.ventaActiva].puntoVenta
                .IdDocVentaPuntoVenta
                ? this.ventas[this.ventaActiva].puntoVenta.IdDocVentaPuntoVenta
                : 1,
              idTipoDoc: this.ventas[this.ventaActiva].tipoVenta.IdTipoDoc
            }
          })
          .then(response => {
            this.ventas[this.ventaActiva].Serie = response.data.serie;
            this.ventas[this.ventaActiva].Numero =
              response.data.numero + this.ventaActiva;
          })
          .catch(error => {
            console.log(error);
            this.$toasted.show(
              "Ha ocurrido un error, vuelva a intentarlo # Venta"
            );
          });
      });
      // }, 1000);
    },
    async setProducto(producto, esNota = false) {
      let productos = this.ventas[this.ventaActiva].productos;
      if (productos.some(v => v.IdProducto == producto.IdProducto)) {
        const productoExistente = productos.filter(
          v => v.IdProducto == producto.IdProducto
        )[0];
        productoExistente.cantidad += parseFloat(1);
        this.aplicarDescuentoProducto(productoExistente);
        this.$nextTick(() => {
          this.metodoPagoCambio(1);
          this.reaplicarDescuentoVigente();
          this.enfocarCodigoBarra(true);
        });

        return false;
      }

      // console.log("producto", producto);
      let prod = await this.obtenerStockBuscador(producto.IdProducto);
      // console.log("prod", prod.PreciosPorProducto);
      let pres = [];
      if (prod.PreciosPorProducto) {
        let newPres = JSON.parse(prod.PreciosPorProducto);
        pres = newPres.filter(v => v.cantidad > 0);
      }
      prod.PreciosPorProducto = pres;
      prod.presentacion = prod.ProductoPresentacion
        ? prod.ProductoPresentacion
        : "UNIDAD";
      prod.ProductoPresentacion = prod.ProductoPresentacion
        ? prod.ProductoPresentacion
        : "UNIDAD";
      prod.Descripcion = String(prod.Descripcion || "").trim();
      prod.cantidad = 1;
      prod.precio = prod.PrecioContado;
      prod.descuento = 0;
      prod.tienePresentacion = prod.PreciosPorProducto.some(
        v => v.cantidad > 0
      );
      prod.bloquearInputPrecio = false;
      prod.CantidadPresentacion = 1;
      prod.esPresentacion = false;
      prod.pres = "";
      this.aplicarDescuentoProducto(prod);
      productos.unshift(prod);
      // if (esNota) {
      //   if (producto.Producto == "TAPAJUNTA") {
      //     console.log(prod);
      //   }
      //   prod.cantidad = producto.cantidad;
      //   prod.precio = producto.PrecioContado;
      // }
      this.$nextTick(() => {
        this.enfocarCodigoBarra(true);
        this.reaplicarDescuentoVigente();
      });
      this.metodoPagoCambio(1);
    },
    quitarProducto(index) {
      this.ventas[this.ventaActiva].productos.splice(index, 1);
      this.$nextTick(() => {
        this.reaplicarDescuentoVigente();
        // ✅ Recalcular el pago después de quitar el producto
        this.$nextTick(() => {
          const metodos = ["efectivo", "visa", "mastercard", "delivery"];
          const ventaActual = this.ventas[this.ventaActiva].pago;
          // Detectar qué método tiene monto > 0 y recalcular solo ese
          metodos.forEach((metodo, i) => {
            if (ventaActual[metodo].monto > 0) {
              metodos.forEach(m => {
                ventaActual[m].descripcion = null;
                ventaActual[m].monto = 0;
              });
              ventaActual[metodo].monto = this.getTotal();
            }
          });
          // Si ninguno tenía monto, asignar efectivo por defecto
          const hayPago = metodos.some(m => ventaActual[m].monto > 0);
          if (!hayPago) {
            ventaActual.efectivo.monto = this.getTotal();
          }
        });
      });
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    setCliente(obj, dni = false) {
      let params = {};
      if (dni) {
        params = { dni };
      } else {
        params = { idCliente: obj.IdCliente || obj };
      }

      const limpiarDescuentos = () => {
        const venta = this.ventas[this.ventaActiva];
        venta.puntosAplicados = 0;
        venta.descuentoPuntos = {};
        venta.descuentoGeneral = 0;
        venta.productos.forEach((prod, index) => {
          const productoActualizado = { ...prod, descuento: 0 };
          this.aplicarDescuentoProducto(productoActualizado);
          this.$set(venta.productos, index, productoActualizado);
        });
      };
      let ref = `inputCliente${this.ventaActiva}`;
      let inputCliente = this.$refs[ref];
      if (typeof obj === "object") {
        if (inputCliente && typeof inputCliente.setValue === "function") {
          // inputCliente.setValue(obj.Cliente);
        }
        this.ventas[this.ventaActiva].cliente = obj;
        this.ventas[this.ventaActiva].CampoDireccion = obj.Direccion;
        limpiarDescuentos();
        if (obj.DniRuc !== "00000000") {
          // inputCliente.$refs.input.disabled = false;
          this.ventas[this.ventaActiva].EsOrganizacion = 0;
          this.ventas[this.ventaActiva].NombreOrganizacion = "";
        }
      } else {
        this.axios
          .get(this.API + "cliente", { params })
          .then(response => {
            if (response.data) {
              if (inputCliente && typeof inputCliente.setValue === "function") {
                // inputCliente.setValue(response.data.Cliente);
              }
              this.ventas[this.ventaActiva].cliente = response.data;
              this.ventas[this.ventaActiva].CampoDireccion =
                response.data.Direccion;
              limpiarDescuentos();
            } else {
              this.$toasted.show("No se encontró al cliente");
            }
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
    async obtenerStockBuscador(IdProducto) {
      let res = {};
      await this.axios(this.API + "productos/puntoventa/stock", {
        params: {
          idProductos: [IdProducto],
          idAlmacen: this.ventas[this.ventaActiva].almacen.IdAlmacen
            ? this.ventas[this.ventaActiva].almacen.IdAlmacen
            : 1
        }
      }).then(response => {
        res = response.data[0];
        // return response.data;
      });
      return res;
    },
    getSubTotalDetalle(prod) {
      let subtotal = 0;

      if (prod.esPresentacion) {
        subtotal = prod.CantidadPresentacion * prod.cantidad * prod.precio;
      } else {
        subtotal = prod.cantidad * prod.precio;
        // console.log(prod);
      }
      return parseFloat(subtotal).toFixed(2);
    },
    getPorcentajeDescuentoProducto(prod) {
      const porcentaje = parseFloat(prod && prod.PorcentajeLiquidacion);
      return Number.isFinite(porcentaje) && porcentaje > 0 ? porcentaje : 0;
    },
    getPorcentajeDescuentoAplicado(prod) {
      const subtotal = Number(this.getSubTotalDetalle(prod));
      const descuento = Number(prod && prod.descuento);
      if (!Number.isFinite(subtotal) || subtotal <= 0 || !Number.isFinite(descuento)) return 0;
      return Number((Math.max(0, descuento) / subtotal * 100).toFixed(2));
    },
    aplicarDescuentoProducto(prod) {
      const porcentaje = this.getPorcentajeDescuentoProducto(prod);
      if (!porcentaje) {
        if (!prod._descuentoManual) this.$set(prod, "descuento", 0);
        return;
      }

      const cantidad = parseFloat(prod.cantidad) || 0;
      const precio = parseFloat(prod.precio) || 0;
      const factorPresentacion = prod.esPresentacion
        ? (parseFloat(prod.CantidadPresentacion) || 1)
        : 1;
      const subtotal = factorPresentacion * cantidad * precio;
      this.$set(prod, "descuento", parseFloat((subtotal * porcentaje / 100).toFixed(2)));
    },
    restaurarDescuentosAutomaticos(ventaIndex = null) {
      const idx = ventaIndex !== null ? ventaIndex : this.ventaActiva;
      const venta = this.ventas[idx];
      if (!venta) return;

      venta.productos.forEach((prod, index) => {
        const productoActualizado = {
          ...prod,
          descuento: 0,
          _descuentoManual: false
        };
        this.aplicarDescuentoProducto(productoActualizado);
        this.$set(venta.productos, index, productoActualizado);
      });
    },
    actualizarDescuentoProductoManual(prod, valorIngresado) {
      if (!this.puedeAplicarDescuentos || !prod) return;

      const venta = this.ventas[this.ventaActiva];
      const productoIndex = venta.productos.indexOf(prod);
      if (productoIndex < 0) return;

      if (Number(venta.descuentoGeneral) > 0 || Number(venta.puntosAplicados) > 0) {
        venta.descuentoGeneral = 0;
        venta.puntosAplicados = 0;
        venta.descuentoPuntos = {};
        this.restaurarDescuentosAutomaticos(this.ventaActiva);
      }

      const productoActual = venta.productos[productoIndex];
      const subtotal = Number(this.getSubTotalDetalle(productoActual)) || 0;
      const valor = Number.parseFloat(valorIngresado);
      const descuento = Math.min(
        subtotal,
        Math.max(0, Number.isFinite(valor) ? valor : 0)
      );
      this.$set(venta.productos, productoIndex, {
        ...productoActual,
        descuento: Math.round(descuento * 100) / 100,
        _descuentoManual: true
      });
      this.$nextTick(() => this.metodoPagoCambio(1, this.ventaActiva));
    },
    actualizarDetalleConDescuento(prod) {
      const venta = this.ventas[this.ventaActiva];
      if (Number(venta.puntosAplicados) > 0 || Number(venta.descuentoGeneral) > 0) {
        this.reaplicarDescuentoVigente(this.ventaActiva);
      } else if (prod._descuentoManual) {
        const subtotal = Number(this.getSubTotalDetalle(prod)) || 0;
        const descuento = Math.min(subtotal, Math.max(0, Number(prod.descuento) || 0));
        this.$set(prod, "descuento", Math.round(descuento * 100) / 100);
      } else {
        this.aplicarDescuentoProducto(prod);
      }
      this.$nextTick(() => this.metodoPagoCambio(1, this.ventaActiva));
    },
    getTotalDetalle(prod) {
      let total = 0;
      if (prod.esPresentacion) {
        total =
          prod.CantidadPresentacion * prod.cantidad * prod.precio -
          prod.descuento;
      } else {
        total = prod.cantidad * prod.precio - prod.descuento;
      }

      return parseFloat(total).toFixed(2);
    },
    getSubTotal(ventaIndex = null) {
      const idx = ventaIndex !== null ? ventaIndex : this.ventaActiva;
      let prod = [...this.ventas[idx].productos];
      if (prod.length == 0) return 0;
      let subtotal = prod.reduce((total, prod) => {
        return total + (Number(this.getSubTotalDetalle(prod)) || 0);
      }, 0);
      return parseFloat(subtotal).toFixed(2);
    },
    getDescuento(ventaIndex = null) {
      const idx = ventaIndex !== null ? ventaIndex : this.ventaActiva;
      let prod = [...this.ventas[idx].productos];
      if (prod.length == 0) return 0;
      let subtotal = prod.reduce((total, prod) => {
        return total + (parseFloat(prod.descuento) || 0);
      }, 0);
      return parseFloat(subtotal).toFixed(2);
    },
    ventaConIgv(ventaIndex = null) {
      const idx = ventaIndex !== null ? ventaIndex : this.ventaActiva;
      return Number((this.ventas[idx].tipoVenta || {}).TieneIgv) === 1;
    },
    getBaseGravada(ventaIndex = null) {
      // Igual que nstarapaca: el precio de venta ya incluye el IGV.
      return (Number(this.getTotal(ventaIndex)) / 1.18).toFixed(2);
    },
    getIgvVenta(ventaIndex = null) {
      const total = Number(this.getTotal(ventaIndex));
      return (total - total / 1.18).toFixed(2);
    },
    getTotal(ventaIndex = null) {
      const idx = ventaIndex !== null ? ventaIndex : this.ventaActiva;
      let prod = [...this.ventas[idx].productos];
      if (prod.length == 0) return 0;
      let subtotal = prod.reduce((total, prod) => {
        if (prod.esPresentacion) {
          return total + parseFloat(prod.CantidadPresentacion * prod.cantidad * prod.precio - prod.descuento);
        } else {
          return total + parseFloat(prod.cantidad * prod.precio - prod.descuento);
        }
      }, 0);
      return parseFloat(subtotal).toFixed(2);
    },
    setOpcionVenta(opc) {
      this.ventas[this.ventaActiva].nombreLlevar = "";
      this.ventas[this.ventaActiva].DireccionEntrega = "";
    },
    setComisionista(obj) {
      if (typeof obj === "object") {
        //this.$refs.inputComisionista.setValue(obj.Cliente)
        this.venta.comisionista = obj;
        this.ventas[this.ventaActiva].comisionista = obj;
      } else {
        this.venta.comisionista = {};
        this.ventas[this.ventaActiva].comisionista = {};
      }
    },
    metodoPagoCambio(tipo = null, ventaIndex = null) {
      const indexUsado = ventaIndex !== null ? ventaIndex : this.ventaActiva;
      
      const metodos = ["efectivo", "visa", "mastercard", "delivery"];
      const ventaActual = this.ventas[indexUsado].pago;
      
      metodos.forEach(metodo => {
        ventaActual[metodo].descripcion = null;
        ventaActual[metodo].monto = 0;
      });
      
      if (tipo && metodos[tipo - 1]) {
        ventaActual[metodos[tipo - 1]].monto = this.getTotal(indexUsado);
        this.$set(this.ventas[indexUsado], "idMetodoPago", tipo);
      }
    },

    seleccionarMetodoPago(tipo, ventaIndex) {
      const nombres = {
        1: "Efectivo",
        2: "Tarjeta",
        3: "Yape/Plin",
        4: "Transferencia"
      };

      this.ventaActiva = ventaIndex;
      this.metodoPagoCambio(tipo, ventaIndex);
      this.$bvToast.toast(`Forma de pago seleccionada: ${nombres[tipo]}`, {
        title: "Pago seleccionado",
        variant: tipo === 1 ? "success" : "info",
        solid: true,
        autoHideDelay: 2200
      });
    },

    async validarStockDisponibleVenta(venta) {
      if (String(venta.tipoVenta && venta.tipoVenta.CodSunat) === "07") return true;

      const productosControlados = (venta.productos || []).filter(prod => {
        const codigo = String(prod.CodigoBarra || "").trim().toUpperCase();
        const noControlaStock = prod.ControlaStock === 0 || prod.ControlaStock === "0" || prod.ControlaStock === false;
        return !noControlaStock && codigo !== "COSTODELIVERY" && codigo !== "MANODEOBRA";
      });
      if (!productosControlados.length) return true;

      const requeridos = {};
      productosControlados.forEach(prod => {
        const idProducto = String(prod.IdProducto);
        const cantidad = Number(prod.cantidad);
        const factorPresentacion = prod.esPresentacion
          ? (Number(prod.CantidadPresentacion) || 1)
          : 1;
        if (!requeridos[idProducto]) {
          requeridos[idProducto] = {
            IdProducto: prod.IdProducto,
            Producto: prod.Producto || prod.CodigoBarra || "Producto",
            Cantidad: 0
          };
        }
        requeridos[idProducto].Cantidad += Number.isFinite(cantidad)
          ? cantidad * factorPresentacion
          : 0;
      });

      try {
        const idAlmacen = venta.almacen && venta.almacen.IdAlmacen
          ? venta.almacen.IdAlmacen
          : 1;
        const response = await this.axios.get(this.API + "productos/puntoventa/stock/" + idAlmacen, {
          params: {
            idProductos: Object.keys(requeridos),
            noLimit: 1
          }
        });
        const stockActual = Array.isArray(response.data) ? response.data : [];
        const stockPorProducto = {};
        stockActual.forEach(prod => {
          stockPorProducto[String(prod.IdProducto)] = Number(prod.stock) || 0;
        });

        productosControlados.forEach(prod => {
          this.$set(prod, "stock", stockPorProducto[String(prod.IdProducto)] || 0);
        });

        const faltantes = Object.keys(requeridos).map(idProducto => {
          const requerido = requeridos[idProducto];
          const disponible = stockPorProducto[idProducto] || 0;
          return {
            ...requerido,
            Disponible: disponible
          };
        }).filter(item => !(item.Cantidad > 0) || item.Cantidad > item.Disponible);

        if (!faltantes.length) return true;

        const escaparHtml = valor => String(valor == null ? "" : valor).replace(/[&<>"']/g, caracter => ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#039;"
        })[caracter]);
        const formatearCantidad = valor => {
          const numero = Math.max(0, Number(valor) || 0);
          return Number.isInteger(numero) ? numero : numero.toFixed(3);
        };
        const detalle = faltantes.map(item => `
          <div class="stock-alert-item">
            <div class="stock-alert-product">
              <span class="stock-alert-product__icon"><i class="fas fa-box"></i></span>
              <span><strong>${escaparHtml(item.Producto)}</strong><small>Existencia insuficiente</small></span>
            </div>
            <div class="stock-alert-values">
              <span><small>Solicita</small><strong>${formatearCantidad(item.Cantidad)}</strong></span>
              <span class="stock-alert-values__available"><small>Disponible</small><strong>${formatearCantidad(item.Disponible)}</strong></span>
            </div>
          </div>
        `).join("");

        const confirmacion = await this.$swal.fire({
          icon: "warning",
          title: "Stock insuficiente",
          html: `
            <div class="stock-alert-copy">Puede continuar con la venta bajo confirmación. Revise los productos sin existencia suficiente.</div>
            <div class="stock-alert-list">${detalle}</div>
          `,
          showCancelButton: true,
          confirmButtonText: "Continuar con la venta",
          cancelButtonText: "Revisar productos",
          buttonsStyling: false,
          customClass: {
            popup: "stock-alert-popup",
            icon: "stock-alert-icon",
            title: "stock-alert-title",
            htmlContainer: "stock-alert-content",
            actions: "stock-alert-actions",
            confirmButton: "stock-alert-confirm",
            cancelButton: "stock-alert-cancel"
          },
          allowOutsideClick: false,
          target: document.getElementById("conten")
        });
        return Boolean(confirmacion && (confirmacion.isConfirmed || confirmacion.value));
      } catch (error) {
        await this.$swal.fire({
          icon: "error",
          title: "No se pudo verificar el stock",
          text: "La venta fue detenida para evitar registrar productos sin existencias.",
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten")
        });
        return false;
      }
    },

    async guardarVenta() {
      this.inProgress = true;
      const ventaIndex = this.ventaActiva;
      let venta = this.ventas[this.ventaActiva];
      if (venta.cliente && venta.cliente.IdCliente) {
        try {
          const resCliente = await this.axios.get(this.API + "cliente", {
            params: { idCliente: venta.cliente.IdCliente }
          });
          if (resCliente.data) {
            // Solo actualizar los puntos, no reemplazar todo el cliente
            venta.cliente.Puntos = resCliente.data.Puntos;
          }
        } catch (e) {
          console.log("Error recargando cliente:", e);
        }
      }
      let productos = venta.productos;
      let totalPagos = 0;

      totalPagos +=
        this.obtenerMonto(venta.pago.efectivo.monto) +
        this.obtenerMonto(venta.pago.visa.monto) +
        this.obtenerMonto(venta.pago.mastercard.monto) +
        this.obtenerMonto(venta.pago.delivery.monto);
      if (this.obtenerMonto(venta.pago.efectivo.monto) < 0) {
        this.$toasted.show("El metodo de pago efectivo no puede ser negativo");
        this.inProgress = true;
        return;
      }
      if (this.obtenerMonto(venta.pago.visa.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago tarjeta/transferencia no puede ser negativo"
        );
        this.inProgress = true;
        return;
      }
      if (this.obtenerMonto(venta.pago.mastercard.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        this.inProgress = true;
        return;
      }
      if (this.obtenerMonto(venta.pago.delivery.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        this.inProgress = true;
        return;
      }
      if (totalPagos <= 0) {
        this.$toasted.show("Debe seleccionar un método de pago");
        this.inProgress = true;
        return;
      }

      if (!venta.cliente.DniRuc) {
        //this.$toasted.show('Debe seleccionar un cliente.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "Debe seleccionar un cliente",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        this.inProgress = true;
        return;
      }
      var esNumerico = Number.isInteger(parseInt(venta.cliente.DniRuc));
      if (!esNumerico) {
        //this.$toasted.show('El RUC debe ser numérico, editar cliente.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "El RUC debe ser numérico, editar cliente.",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });
        this.inProgress = true;
        return;
      }

      var esEmpresa = venta.cliente.DniRuc.length > 9 ? true : false;
      if (!esEmpresa && venta.tipoVenta.CodSunat == "01") {
        //this.$toasted.show('Una factura debe ser emitida para una empresa, no un usuario común.')
        this.$swal({
          position: "center",
          icon: "info",
          title:
            "Una factura debe ser emitida para una empresa, no a un usuario común",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });
        this.inProgress = true;
        return;
      }
      if (esEmpresa && venta.tipoVenta.CodSunat == "03") {
        //this.$toasted.show('Una boleta no debe ser emitida para una empresa.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "Una boleta no debe ser emitida para una empresa",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        this.inProgress = true;
        return;
      }
      if (
        venta.tipoVenta.CodSunat == "07" ||
        venta.tipoVenta.CodSunat == "08"
      ) {
        if (!venta.CodSunatModifica || !venta.NroComprobanteModifica) {
          //this.$toasted.show('Debe seleccionar un Documento de Referencia...')
          this.$swal({
            position: "center",
            icon: "info",
            title: "Debe seleccionar un Documento de Referencia...",
            showConfirmButton: true,
            confirmButtonText: "Entiendo",
            target: document.getElementById("conten"),
            confirmButtonColor: "#0275d8"
          });

          this.inProgress = true;
          return;
        }
        if (!venta.NotaIdMotivo) {
          //this.$toasted.show('Debe seleccionar un Motivo')
          this.$swal({
            position: "center",
            icon: "info",
            title: "Debe seleccionar un Motivo",
            showConfirmButton: true,
            confirmButtonText: "Entiendo",
            target: document.getElementById("conten"),
            confirmButtonColor: "#0275d8"
          });

          this.inProgress = true;
          return;
        }
        if (venta.NroComprobanteModifica == "") {
          this.$toasted.show("Debe seleccionar un Documento de Referencia");

          this.$refs.inputDocRef.focus();
          this.inProgress = true;
          return;
        }
        venta.NotaDescMotivo = this.itemsMotivoNota.filter(
          item => item.value == venta.NotaIdMotivo
        )[0].text;
      }
      if (!productos.length) {
        this.$swal({
          position: "center",
          icon: "info",
          title: "No hay productos para vender",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        this.inProgress = true;
        return;
      }

      const stockDisponible = await this.validarStockDisponibleVenta(venta);
      if (!stockDisponible) {
        this.inProgress = false;
        return;
      }

      venta.nombreLlevar = venta.nombreLlevar.toUpperCase();
      // venta.productos = this.productos;
      if (venta.tipoVenta.CodSunat == "07" || venta.tipoVenta.CodSunat == "08") {
        venta.puntosAplicados = 0;
      } else {
        venta.puntosAplicados = venta.puntosAplicados || 0;
      }
      // venta.totalCart = this.totalCart || 0;
      venta.IdPreOrden = this.$route.query.idPreOrden || null;
      venta.IdPedido = this.$route.query.idPedido || null;
      venta.IdProyecto = this.$route.query.idProyecto || null;
      venta.adelantosPreOrden = [];
      if (venta.selectOpcionVenta != 2) {
        venta.DireccionEntrega = venta.EsDelivery
          ? this.infoDelivery.Direccion || ""
          : "";
      }
      venta.PagoCon = venta.pagocon;
      venta.totalCart = this.getTotal();
      if (
        parseFloat(this.getTotal()).toFixed(2) !=
        parseFloat(totalPagos).toFixed(2)
      ) {
        this.$toasted.show("Los pagos no coinciden con la venta");
        this.inProgress = false;
        this.inProgress = true;
        return;
      }
      venta.Observacion = String(venta.Observacion || "").trim().slice(0, 500);
      venta.Placa = String(venta.Placa || "").trim().replace(/[^A-Za-z0-9-]/g, "").slice(0, 20);
      venta.idProductos = productos.map(prod => prod.IdProducto);
      if (venta.cliente.DniRuc != "00000000") {
        venta.EsOrganizacion = 0;
        venta.NombreOrganizacion = "";
      }
      if (venta.EsOrganizacion) {
        venta.cliente.Cliente = venta.NombreOrganizacion;
      }
      venta.NombreOrganizacion = ("" + venta.NombreOrganizacion)
        .toUpperCase()
        .trim();

      await this.axios.post(this.API + "ventas", venta).then(async response => {
        if (response.data.insertId) {
    this.ultimaVentaId = response.data.insertId;
    window.localStorage.setItem("ultimaVentaId", String(response.data.insertId));
    await this.imprimirVentaDirecta(response.data.insertId);

          this.$toasted.success("Venta realizada con exito");
          venta.productos.map(pro => {
            delete pro.nuevoPrecioContado;
            delete pro.Descripcion;
            delete pro.disabledProf;
            delete pro.normalPrecioContado;
            delete pro.Anulado;
            delete pro.Botapie;
            delete pro.Codigo;
            delete pro.CodigoBarra;
            delete pro.Color;
            delete pro.ControlaStock;
            delete pro.Dosis;
            delete pro.EsPadre;
            delete pro.FechaMod;
            delete pro.FechaReg;
            delete pro.Genero;
            delete pro.Hash;
            delete pro.IdBloque;
            delete pro.IdProducto;
            delete pro.IdProductoCategoria;
            delete pro.IdProductoFormaFarmaceutica;
            delete pro.IdProductoMarca;
            delete pro.IdProductoMedicion;
            delete pro.IdProductoTalla;
            delete pro.PorcentajeUtilidad;
            delete pro.PrecioContado;
            delete pro.PrecioCosto;
            delete pro.PrecioPorMayor;
            delete pro.ProductoCategoria;
            delete pro.ProductoDesc;
            delete pro.ProductoDescCorto;
            delete pro.ProductoMarca;
            delete pro.ProductoMedicion;
            delete pro.ProductoModelo;
            delete pro.ProductoTalla;
            delete pro.StockMinimo;
            delete pro.StockPorMayor;
            delete pro.UsuarioMod;
            delete pro.UsuarioReg;
            delete pro.VentaEstrategica;
            delete pro.stock;
          });
          delete venta.cliente.Anulado;
          delete venta.cliente.FechaReg;
          delete venta.cliente.UsuarioReg;
          delete venta.cliente.FechaMod;
          delete venta.cliente.UsuarioMod;
          delete venta.cliente.FechaNacimiento;
          delete venta.cliente.Nacionalidad;
          delete venta.cliente.Direccion3;
          delete venta.cliente.Telefono;
          delete venta.cliente.Email;
          delete venta.cliente.Puntos;
          delete venta.cliente.EsVIP;
          delete venta.cliente.Direccion2;
          delete venta.cliente.Sexo;
          delete venta.cliente.Ocupacion;
          delete venta.cliente.NombreComercial;
          delete venta.cliente.esComisionista;
          delete venta.idProductos;
          delete venta.vendedor.APPTOKEN;
          delete venta.vendedor.APPURL;
          delete venta.vendedor.Anulado;
          delete venta.vendedor.FechaReg;
          delete venta.vendedor.UsuarioReg;
          delete venta.vendedor.FechaMod;
          delete venta.vendedor.UsuarioMod;
          delete venta.vendedor.Password;
          delete venta.puntoVenta;
          delete venta.almacen;
          let dataInfoDocVenta = {
            Serie: response.data.serie,
            Numero: response.data.numero,
            Fecha: response.data.fecha
          };
          venta.dataInfoDocVenta = dataInfoDocVenta;
          let ventaJson = JSON.stringify(venta);
          var left = screen.width / 2 - 800 / 2;
          var top = screen.height / 2 - 800 / 2;
          // let params =
          //   "scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=300,height=300,top=" +
          //   top +
          //   ", left=" +
          //   window.open(
          //     "/imprimir/index.php?IdDocVenta=" + response.data.insertId,
          //     "_blank"
          //   );
          /*window.open(
          window.open(
            "http://localhost:5000/imprimir/getdata.php?rep=2&venta=" +
              encodeURIComponent(ventaJson),
            "Impresión",
            params
          );*/
        }
        this.inProgress = true;
        if (this.ventas.length == 1) {
          window.location.reload();
          this.inProgress = false;
        } else {
          this.eliminarVentana();
          this.getVendedorInfo();
          this.inProgress = false;
        }

        // if (response.data.actualizar) {
        //   this.$toasted.show(response.data.msg);
        //   return;
        // }
      });
    },
    async guardarProforma() {
      let venta = this.ventas[this.ventaActiva];
      let productos = venta.productos;
      let totalPagos = 0;

      totalPagos +=
        this.obtenerMonto(venta.pago.efectivo.monto) +
        this.obtenerMonto(venta.pago.visa.monto) +
        this.obtenerMonto(venta.pago.mastercard.monto) +
        this.obtenerMonto(venta.pago.delivery.monto);
      if (this.obtenerMonto(venta.pago.efectivo.monto) < 0) {
        this.$toasted.show("El metodo de pago efectivo no puede ser negativo");
        return;
      }
      if (this.obtenerMonto(venta.pago.visa.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago tarjeta/transferencia no puede ser negativo"
        );
        return;
      }
      if (this.obtenerMonto(venta.pago.mastercard.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        return;
      }
      if (this.obtenerMonto(venta.pago.delivery.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        return;
      }

      if (!venta.cliente.DniRuc) {
        //this.$toasted.show('Debe seleccionar un cliente.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "Debe seleccionar un cliente",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        return;
      }
      var esNumerico = Number.isInteger(parseInt(venta.cliente.DniRuc));
      if (!esNumerico) {
        //this.$toasted.show('El RUC debe ser numérico, editar cliente.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "El RUC debe ser numérico, editar cliente.",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });
        return;
      }

      var esEmpresa = venta.cliente.DniRuc.length > 9 ? true : false;
      if (!esEmpresa && venta.tipoVenta.CodSunat == "01") {
        //this.$toasted.show('Una factura debe ser emitida para una empresa, no un usuario común.')
        this.$swal({
          position: "center",
          icon: "info",
          title:
            "Una factura debe ser emitida para una empresa, no a un usuario común",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });
        return;
      }
      if (esEmpresa && venta.tipoVenta.CodSunat == "03") {
        //this.$toasted.show('Una boleta no debe ser emitida para una empresa.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "Una boleta no debe ser emitida para una empresa",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        return;
      }
      if (
        venta.tipoVenta.CodSunat == "07" ||
        venta.tipoVenta.CodSunat == "08"
      ) {
        if (!venta.CodSunatModifica || !venta.NroComprobanteModifica) {
          //this.$toasted.show('Debe seleccionar un Documento de Referencia...')
          this.$swal({
            position: "center",
            icon: "info",
            title: "Debe seleccionar un Documento de Referencia...",
            showConfirmButton: true,
            confirmButtonText: "Entiendo",
            target: document.getElementById("conten"),
            confirmButtonColor: "#0275d8"
          });

          return;
        }
        if (!venta.NotaIdMotivo) {
          //this.$toasted.show('Debe seleccionar un Motivo')
          this.$swal({
            position: "center",
            icon: "info",
            title: "Debe seleccionar un Motivo",
            showConfirmButton: true,
            confirmButtonText: "Entiendo",
            target: document.getElementById("conten"),
            confirmButtonColor: "#0275d8"
          });

          return;
        }
        if (venta.NroComprobanteModifica == "") {
          this.$toasted.show("Debe seleccionar un Documento de Referencia");

          this.$refs.inputDocRef.focus();
          return;
        }
        venta.NotaDescMotivo = this.itemsMotivoNota.filter(
          item => item.value == venta.NotaIdMotivo
        )[0].text;
      }
      if (!productos.length) {
        this.$swal({
          position: "center",
          icon: "info",
          title: "No hay productos para vender",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        return;
      }

      //   if (venta.diasValidosProf == 0 || venta.diasValidosProf == "") {
      //   this.$toasted.show("Debe ingresar los días de validez de la proforma");
      //   this.inProgress = false;
      //   return;
      // }

      venta.nombreLlevar = venta.nombreLlevar.toUpperCase();

      venta.productos.forEach((prod, index) => {
        this.$set(venta.productos, index, {
          ...prod,
          descuento: 0
        });
      });

      venta.puntosAplicados = 0;
      venta.descuentoPuntos = {};
      // venta.totalCart = this.totalCart || 0;
      venta.IdPreOrden = this.$route.query.idPreOrden || null;
      venta.IdPedido = this.$route.query.idPedido || null;
      venta.IdProyecto = this.$route.query.idProyecto || null;
      venta.adelantosPreOrden = [];
      if (venta.selectOpcionVenta != 2) {
        venta.DireccionEntrega = venta.EsDelivery
          ? this.infoDelivery.Direccion || ""
          : "";
      }
      venta.PagoCon = venta.pagocon;
      venta.totalCart = this.getTotal();
      venta.Observacion = String(venta.Observacion || "").trim().slice(0, 500);
      venta.Placa = String(venta.Placa || "").trim().replace(/[^A-Za-z0-9-]/g, "").slice(0, 20);
      venta.idProductos = productos.map(prod => prod.IdProducto);
      if (venta.cliente.DniRuc != "00000000") {
        venta.EsOrganizacion = 0;
        venta.NombreOrganizacion = "";
      }
      if (venta.EsOrganizacion) {
        venta.cliente.Cliente = venta.NombreOrganizacion;
      }
      venta.NombreOrganizacion = ("" + venta.NombreOrganizacion)
        .toUpperCase()
        .trim();
      await this.axios.post(this.API + "proformas", venta).then(response => {
        if (response.data.insertId) {
          this.$toasted.success("Proforma realizada con exito");
          window.open(
            "/nuevo/index.html#/reportes/proforma/" + response.data.insertId,
            "_blank"
          );
        }
      });
      if (this.ventas.length == 1) {
        window.location.reload();
      } else {
        this.eliminarVentana();
        this.getVendedorInfo();
      }
    },
    obtenerMonto(valor) {
      return isNaN(parseFloat(valor)) ? 0 : parseFloat(valor);
    },

    async cargarProforma(proforma) {
      try {
        this.initValueCliente = `${proforma.DniRuc} ${proforma.Cliente}`;

        // Capturar el índice que tendrá esta nueva venta
        const nuevoIndex = this.ventas.length;

        // Clonar estructura base de venta con pago fresco
        let venta = {
          ...JSON.parse(JSON.stringify(this.venta)), // deep clone para evitar referencias compartidas
          cliente: null,
          vendedor: null,
          productos: [],
          pago: {
            efectivo: { IdMetodoPago: 1, monto: 0, descripcion: null },
            visa:     { IdMetodoPago: 2, monto: 0, descripcion: null },
            mastercard: { IdMetodoPago: 3, monto: 0, descripcion: null },
            delivery: { IdMetodoPago: 4, monto: 0, descripcion: null }
          },
          puntosAplicados: 0,
          descuentoPuntos: {},
          descuentoGeneral: 0
        };

        // Obtener vendedor
        const respUsuario = await this.axios.get(
          `${this.API}/usuarios/${proforma.UsuarioReg}`
        );
        if (respUsuario.data) {
          venta.UsuarioProforma = respUsuario.data.Usuario;
        }

        // Asignar vendedor desde la venta 0 (ya cargado)
        venta.vendedor = { ...this.ventas[0].vendedor };

        // Cargar cliente
        const respCliente = await this.axios.get(this.API + "cliente", {
          params: { idCliente: proforma.IdCliente }
        });

        if (respCliente.data && respCliente.data.IdCliente) {
          venta.cliente = respCliente.data;
        } else {
          venta.cliente = { ...proforma };
        }

        // Cargar productos usando el almacen de ventas[0] directamente
        const idAlmacen = this.ventas[0].almacen.IdAlmacen || 1;
        const productos = [];

        for (const producto of proforma.productos) {
          let prod = {};
          // Obtener stock usando el almacen correcto, sin depender de ventaActiva
          await this.axios(this.API + "productos/puntoventa/stock", {
            params: {
              idProductos: [producto.IdProducto],
              idAlmacen: idAlmacen
            }
          }).then(response => {
            prod = response.data[0];
          });

          // Presentaciones
          let pres = [];
          if (prod.PreciosPorProducto) {
            try {
              const parsed = JSON.parse(prod.PreciosPorProducto);
              pres = parsed.filter(v => v.cantidad > 0);
            } catch (e) {
              pres = [];
            }
          }

          prod.PreciosPorProducto = pres;
          prod.presentacion = producto.Presentacion || "UNIDAD";
          prod.Descripcion = producto.Descripcion == null ? "" : String(producto.Descripcion);
          prod.cantidad = producto.Cantidad;
          prod.CantidadPresentacion = producto.CantidadxPresentacion || 1;
          prod.precio = producto.Precio;
          prod.descuento = producto.Descuento || 0;
          prod.bloquearInputPrecio = false;
          prod.tienePresentacion = pres.length > 0;
          prod.esPresentacion = pres.some(v => v.nombre === producto.Presentacion);
          prod.pres = prod.esPresentacion
            ? JSON.stringify(pres.find(v => v.nombre === producto.Presentacion))
            : null;

          productos.push(prod);
        }

        venta.productos = productos;
        venta.almacen = { ...this.ventas[0].almacen };
        venta.puntoVenta = { ...this.ventas[0].puntoVenta };

        // Calcular el total directamente sobre los productos sin depender de getTotal()
        const totalVenta = productos.reduce((total, prod) => {
          if (prod.esPresentacion) {
            return total + parseFloat(prod.CantidadPresentacion * prod.cantidad * prod.precio - prod.descuento);
          } else {
            return total + parseFloat(prod.cantidad * prod.precio - prod.descuento);
          }
        }, 0);

        // Asignar el pago directamente sobre el objeto venta ANTES de hacer push
        venta.pago.efectivo.monto = parseFloat(totalVenta).toFixed(2);

        // Insertar venta YA COMPLETA con pago ya calculado
        this.ventas.push(venta);

        // Solo cambiar ventaActiva y número, sin llamar setCliente ni metodoPagoCambio
        this.$nextTick(() => {
          this.ventaActiva = nuevoIndex;
          // Obtener número de venta para este índice
          this.axios.get(this.API + "ventas/numero", {
            params: {
              idPuntoVenta: venta.puntoVenta.IdDocVentaPuntoVenta || 1,
              idTipoDoc: venta.tipoVenta.IdTipoDoc
            }
          }).then(response => {
            this.ventas[nuevoIndex].Serie = response.data.serie;
            this.ventas[nuevoIndex].Numero = response.data.numero + nuevoIndex;
          });
        });

      } catch (error) {
        console.error("Error al cargar proforma:", error);
      }
    },
    obtenerPresentacionesVenta(prod) {
      if (!prod) return [];
      let presentaciones = prod.PreciosPorProducto;
      if (typeof presentaciones === "string") {
        try {
          presentaciones = JSON.parse(presentaciones);
        } catch (error) {
          presentaciones = [];
        }
      }
      if (!Array.isArray(presentaciones)) return [];
      return presentaciones.map(item => ({
        ...item,
        nombre: String(item.nombre || item.Nombre || item.Presentacion || "PRESENTACIÓN").trim(),
        cantidad: parseFloat(item.cantidad || item.Cantidad || item.CantidadPresentacion || 0),
        precioc: parseFloat(item.precioc || item.PrecioCosto || item.Costo || 0),
        precio: parseFloat(item.precio || item.Precio || item.PrecioVenta || 0)
      })).filter(item => item.nombre && item.cantidad > 0 && item.precio > 0);
    },
    contarPresentaciones(prod) {
      return this.obtenerPresentacionesVenta(prod).length;
    },
    tienePresentaciones(prod) {
      return this.obtenerPresentacionesVenta(prod).length > 0;
    },    showPresentacion(prod, index) {
      const presentaciones = this.obtenerPresentacionesVenta(prod);
      if (!this.tienePresentaciones(prod)) return;
      this.producto = { ...prod, PreciosPorProducto: presentaciones };
      this.index = index;
      this.modalPresentacion = true;
      this.metodoPagoCambio(1);
    },
    cambiarPresentacion(obj, esPresentacion) {
      let producto = { ...this.producto };
      if (esPresentacion) {
        producto.presentacion = obj.nombre;
        producto.pres = JSON.stringify(obj);
        producto.CantidadPresentacion = 1;
        producto.cantidad = obj.cantidad;
        producto.bloquearInputPrecio = true;
        producto.precio = obj.precio;
        producto.esPresentacion = esPresentacion;
      } else {
        producto.presentacion = obj.ProductoPresentacion;
        producto.CantidadPresentacion = 1;
        producto.cantidad = 1;
        producto.pres = "";
        producto.bloquearInputPrecio = false;
        producto.precio = producto.PrecioContado;
        producto.esPresentacion = esPresentacion;
      }

      Vue.set(this.ventas[this.ventaActiva].productos, this.index, producto);
      this.modalPresentacion = false;
      this.metodoPagoCambio(1);
    },
    probarImpresora() {
      if (
        this.venta.vendedor != false &&
        Object.keys(this.venta.vendedor).length > 0
      ) {
        let data = {
          Ip: this.venta.vendedor.Ip,
          NombreImpresora: this.venta.vendedor.NombreImpresora,
          NombreRed: this.venta.vendedor.NombreRed,
          Puerto: this.venta.vendedor.Puerto,
          Tipo: this.venta.vendedor.Tipo
        };
        let impresoraJson = JSON.stringify(data);
        var left = screen.width / 2 - 800 / 2;
        var top = screen.height / 2 - 800 / 2;
        let params =
          "scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=300,height=300,top=" +
          top +
          ", left=" +
          left;
        window.open(
          "http://localhost:5000/imprimir/test.php?impresora=" +
            encodeURIComponent(impresoraJson),
          "Impresión",
          params
        );
      }
    },
    onPagarDelivery(obj) {
      this.$route.query.idPedido = obj.IdPedido;
      let venta = { ...this.venta };
      if (this.$route.query.idPedido) {
        this.infoDelivery = obj;
        this.venta.EsDelivery = true;
        // this.$refs.carrito.pagocon = parseFloat(obj.Cambio).toFixed(2);
        // //cargar costo de delivery
        // let costoDelivery = parseFloat(obj.PrecioDelivery || 0).toFixed(2);
        // if (costoDelivery > 0) {
        //   this.axios
        //     .get(this.API + "costo/delivery")
        //     .then(response => {
        //       if (response.data) {
        //         let costoDeliverObj = response.data;
        //         costoDeliverObj.precio = costoDelivery;
        //         costoDeliverObj.cantidad = 1;
        //         costoDeliverObj.EsCostoDelivery = 1;
        //         costoDeliverObj.IdImpresora = 0;
        //         costoDeliverObj.descuento = 0;
        //         costoDeliverObj.indicacion = "";
        //         this.add([
        //           response.data,
        //           () => {
        //             //this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 })
        //           },
        //           { setPrecioCosto: false },
        //           false,
        //           false
        //         ]);
        //         //Cargar Productos Delivery
        //         let productos = JSON.parse(obj.Productos);
        //         console.log(productos);
        //         productos.map(item => {
        //           this.add([
        //             item.IdProducto,
        //             () => {
        //               this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
        //             },
        //             {
        //               cantidad: item.Cantidad,
        //               precio: item.Precio,
        //               indicacion: item.Indicacion
        //             },
        //             false
        //           ]);
        //         });
        //         this.venta.nombreLlevar = obj.Cliente || "SIN NOMBRE";
        //         this.venta.DireccionEntrega = obj.Direccion;
        //         this.$toasted.success("PRODUCTOS DEL DELIVERY AGREGADOS");
        //       } else {
        //         this.$toasted.show(
        //           "PRIMERO CREE EL PRODUCTO COSTO DE DELIVERY CON EL CODIGO DE BARRAS 'COSTODELIVERY'"
        //         );
        //         this.anularPagoDelivery();
        //       }
        //     })
        //     .catch(error => {
        //       this.$toasted.show("HUBO UN ERROR");
        //     });
      } else {
        //Cargar Productos Delivery
        let productos = JSON.parse(obj.Productos);
        // productos.map(item => {
        //   this.add([
        //     item.IdProducto,
        //     () => {
        //       this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
        //     },
        //     {
        //       cantidad: item.Cantidad,
        //       precio: item.Precio,
        //       indicacion: item.Indicacion,
        //       isDelivery: true
        //     },
        //     false
        //   ]);
        // });
        // this.venta.nombreLlevar = obj.Cliente || "SIN NOMBRE";
        // this.venta.DireccionEntrega = obj.Direccion;
      }
    },
    imagenesProducto(producto) {
      return [producto.Imagen, producto.Imagen2, producto.Imagen3]
        .filter(Boolean)
        .map(imagen => "/resources/images/productos/" + encodeURIComponent(imagen));
    },
    seleccionarImagen(index) {
      if (!this.productoUrls.length) return;
      this.productoImagenActiva = (index + this.productoUrls.length) % this.productoUrls.length;
      this.productoUrl = this.productoUrls[this.productoImagenActiva];
    },
    cambiarImagen(delta) {
      this.seleccionarImagen(this.productoImagenActiva + delta);
    },
    abrirImagen(producto) {
      this.productoNombre = producto.Producto;
      this.productoUrls = this.imagenesProducto(producto);
      if (!this.productoUrls.length) this.productoUrls = ["static/no-photo.png"];
      this.seleccionarImagen(0);
      this.$refs["modalImagen"].show();
    }
  },
  computed: {
    puedeAplicarDescuentos() {
      return Number(this.idPerfil) === 1;
    },
    puedeEditarPrecioVenta() {
      const perfil = Number(this.idPerfil);
      return perfil === 1 || perfil === 31;
    },
    APIProductos() {
      // console.log(this.ventas[this.ventaActiva].almacen.IdAlmacen);
      if (this.ventas[this.ventaActiva].almacen.IdAlmacen) {
        return (
          this.API +
          "productos/puntoventa/stock/" +
          this.ventas[this.ventaActiva].almacen.IdAlmacen
        );
      } else {
        return this.API + "productos";
      }
    }
  }
};
</script>
  <style scoped>
.alert {
  margin-bottom: 0;
}
.puntos {
  font-size: 52px;
}
.big-text {
  font-size: 30px;
}
.nomCart {
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 2px solid black;
}
.contentCard {
  cursor: pointer;
}
.centrado {
  width: 100%;
  align-items: center !important;
  align-content: center !important;
  text-align: center !important;
}
.calc__numbers {
  width: 15vw;
  height: 15vw;
}
.calc__numbers2 {
  width: 100%;
  height: 20vh;
}
.calc__numbers td {
  min-width: 40px;
  min-height: 40px;
  border: 0.5px solid #a3a3a3;
  cursor: pointer;
  text-align: center;
  vertical-align: middle;
  padding: 1.8% 1.8%;
  font-size: 0.8rem;
  font-weight: bold;
  font-family: Arial;
  color: black;
  background-color: #fff;
}
.calc__numbers td:last-child {
  background-color: #eaeaea;
}
.calc__numbers2 td:last-child {
  background-color: white;
}
.calc__numbers .botones:hover {
  color: #fff;
  background: #65ba9b;
}
.calc__numbers .accionActiva {
  color: #fff !important;
  background: #65ba9b !important;
}

.pagocon .input-group-text {
  font-size: 0.8rem;
  font-weight: bold;
}
</style>

<style scoped>
.pos-floating-documents {
  position: fixed;
  left: 18px;
  bottom: 18px;
  z-index: 1040;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.pos-documents-button,
.pos-last-ticket {
  display: inline-flex;
  width: 42px;
  height: 42px;
  align-items: center;
  justify-content: center;
  padding: 0;
  border: 1px solid rgba(255, 255, 255, 0.32);
  border-radius: 50%;
  background: linear-gradient(135deg, #0b3d6e, #0796bd);
  color: #fff;
  box-shadow: 0 10px 24px rgba(4, 52, 91, 0.26);
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.01em;
  cursor: pointer;
  transition: transform 0.18s ease, box-shadow 0.18s ease, filter 0.18s ease;
}

.pos-documents-button {
  background: linear-gradient(135deg, #087f5b, #13b981);
  box-shadow: 0 10px 24px rgba(5, 125, 86, 0.28);
}

.pos-last-ticket {
  border: 2px solid #fff;
  background: linear-gradient(135deg, #1688f0, #075dcc) !important;
  box-shadow: 0 8px 20px rgba(7, 93, 204, 0.42), 0 0 0 3px rgba(22, 136, 240, 0.16);
}

.pos-last-ticket i {
  color: #fff !important;
  opacity: 1 !important;
}

.pos-documents-button span,
.pos-last-ticket span { display: none; }

.pos-documents-button:hover,
.pos-last-ticket:hover {
  transform: translateY(-2px);
  box-shadow: 0 13px 28px rgba(4, 52, 91, 0.34);
  filter: brightness(1.06);
}

.pos-documents-button:focus,
.pos-last-ticket:focus {
  outline: 3px solid rgba(28, 176, 220, 0.28);
  outline-offset: 2px;
}

.pos-documents-button i,
.pos-last-ticket i {
  font-size: 14px;
}

@media (max-width: 767px) {
  .pos-floating-documents {
    left: 10px;
    bottom: 10px;
  }
}

@media print {
  .pos-floating-documents,
  .pos-documents-button,
  .pos-last-ticket {
    display: none !important;
  }
}
</style>
<style>
.pos-documents-dialog { width:calc(100% - 36px);max-width:1510px!important;margin:18px auto; }
.pos-documents-modal .modal-content { overflow:hidden;border:0;border-radius:20px; }
.pos-documents-modal .modal-body { padding:0;background:#eef5fa; }
.pos-documents-heading { display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f5faff;color:#173b60; }
.pos-documents-heading__icon { display:grid;width:36px;height:36px;place-items:center;border-radius:10px;color:#078b62;background:#dff7ec; }
.pos-documents-heading strong,.pos-documents-heading small { display:block; }
.pos-documents-heading small { color:#60758b;font-size:12px; }
.pos-documents-frame { display:block;width:100%;height:calc(100vh - 125px);min-height:620px;border:0; }
</style>
<style>
/* Header sizing correction: prevent Bootstrap columns from collapsing inside CSS Grid. */
.pos-modern .pos-document-layout {
  min-height: 128px;
  height: auto;
  max-height: none;
  grid-template-columns: minmax(190px,1fr) minmax(250px,1.35fr) minmax(190px,1fr);
}

.pos-modern .pos-document-layout > .col-md-4 {
  width: auto !important;
  max-width: none !important;
  min-width: 0 !important;
  flex: none !important;
}

.pos-modern .pos-points-card {
  width: 100%;
  min-width: 0;
}

.pos-modern .pos-points-card .pos-metric-label {
  white-space: nowrap;
}

.pos-modern .pos-points-card .puntos {
  white-space: nowrap;
  word-break: normal;
}

.pos-modern .pos-document-layout > .col-md-4 > .input-group {
  width: 100%;
  flex-wrap: nowrap;
}

.pos-modern .pos-document-identity,
.pos-modern .pos-seller-card {
  width: auto;
  max-width: none;
  min-height: 96px;
}

@media (max-width: 1350px) {
  .pos-modern .pos-document-layout {
    grid-template-columns: minmax(155px,.85fr) minmax(210px,1.25fr) minmax(160px,.9fr);
  }
}

/* El selector de documento debe flotar inmediatamente bajo su propio campo. */
.pos-modern.pos-modern .pos-sale-header .pos-doc-field,
.pos-modern.pos-modern .pos-sale-header .pos-doc-field .input-group {
  position: relative !important;
  overflow: visible !important;
  z-index: 200 !important;
}

.pos-modern.pos-modern .pos-sale-header .pos-doc-field .autocomplete {
  position: relative !important;
  height: 40px !important;
  min-height: 40px !important;
  max-height: 40px !important;
  align-self: flex-start !important;
  overflow: visible !important;
  z-index: 201 !important;
}

.pos-modern.pos-modern .pos-sale-header .pos-doc-field .autocomplete ul {
  position: absolute !important;
  top: 47px !important;
  left: 0 !important;
  z-index: 25000 !important;
  margin: 0 !important;
}
</style>
<style>
/* Executive sales header */
.pos-modern .pos-sale-header,
.pos-modern .pos-document-summary {
  padding: 10px;
}

.pos-modern .pos-sale-header > .row,
.pos-modern .pos-document-layout {
  position: relative;
  min-height: 128px;
  overflow: visible;
  border: 1px solid #dbe5f0;
  border-radius: 18px;
  background:
    radial-gradient(circle at 0 0,rgba(37,99,235,.06),transparent 34%),
    linear-gradient(145deg,#fff,#f7faff);
  box-shadow: 0 14px 32px rgba(15,23,42,.08);
}

.pos-modern .pos-sale-header > .row::before,
.pos-modern .pos-document-layout::before {
  position: absolute;
  z-index: 1;
  top: -1px;
  right: 22px;
  left: 22px;
  height: 4px;
  content: "";
  border-radius: 0 0 8px 8px;
  background: linear-gradient(90deg,#0b234a,#2563eb,#06b6d4);
  box-shadow: 0 3px 10px rgba(37,99,235,.2);
}

.pos-modern .pos-sale-header > .row {
  margin: 0;
  padding: 10px 10px 8px;
}

.pos-modern .pos-doc-field,
.pos-modern .pos-header-field {
  padding-right: 6px;
  padding-left: 6px;
}

.pos-modern .pos-doc-field .input-group,
.pos-modern .pos-header-field .input-group {
  margin-top: 5px !important;
  border-radius: 12px;
  box-shadow: 0 6px 14px rgba(15,23,42,.06);
}

.pos-modern .pos-doc-field .input-group-text,
.pos-modern .pos-header-field .input-group-text {
  min-height: 40px;
  padding: 0 13px;
  border-color: #d3e0ec;
  color: #475569;
  background: #f4f7fb;
  font-size: .72rem;
  font-weight: 850;
  letter-spacing: .02em;
  text-transform: uppercase;
}

.pos-modern .pos-doc-field .form-control,
.pos-modern .pos-header-field .form-control {
  min-height: 40px;
  border-color: #d3e0ec;
  color: #0f213d;
  font-size: .8rem;
  font-weight: 800;
}

.pos-modern .pos-client-field {
  margin-top: 4px !important;
  padding-right: 6px;
  padding-left: 6px;
}

.pos-modern .pos-client-field .input-group {
  min-height: 46px;
  border: 1px solid #bcd8ef;
  border-radius: 13px;
  box-shadow: 0 9px 20px rgba(11,35,74,.1);
}

.pos-modern .pos-client-field .input-group::before {
  display: inline-flex;
  width: 42px;
  min-width: 42px;
  align-items: center;
  justify-content: center;
  content: "\f007";
  border-right: 1px solid #d7e4ef;
  border-radius: 12px 0 0 12px;
  color: #2563eb;
  background: #eef6ff;
  font-family: "Font Awesome 5 Free";
  font-size: .9rem;
  font-weight: 900;
}

.pos-modern .pos-client-field .form-control {
  min-height: 44px;
  padding-left: 12px;
  font-size: .88rem;
  font-weight: 750;
}

.pos-modern .pos-client-field .input-group-append .btn {
  min-width: 110px;
  min-height: 44px;
  border-radius: 0 12px 12px 0;
  background: linear-gradient(135deg,#0b234a,#075da9);
  box-shadow: 6px 7px 15px rgba(11,35,74,.2);
  font-size: .84rem;
}

.pos-modern .pos-document-layout {
  display: grid !important;
  min-height: 128px;
  padding: 12px 14px !important;
  align-items: stretch !important;
  grid-template-columns: minmax(165px,.9fr) minmax(230px,1.15fr) minmax(170px,.85fr);
  gap: 10px !important;
}

.pos-modern .pos-document-layout > .col-md-4,
.pos-modern .pos-document-identity,
.pos-modern .pos-seller-card {
  display: flex;
  min-width: 0;
  margin: 0;
  padding: 10px 12px;
  align-items: center;
  justify-content: center;
  border: 1px solid #dce6f0;
  border-radius: 14px;
  background: rgba(255,255,255,.82);
}

.pos-modern .pos-points-card {
  margin: 0;
}

.pos-modern .pos-metric-label,
.pos-modern .pos-summary-kicker {
  display: block;
  margin-bottom: 4px;
  color: #64748b;
  font-size: .62rem;
  font-weight: 850;
  letter-spacing: .075em;
  text-transform: uppercase;
}

.pos-modern .pos-metric-label i {
  color: #f59e0b;
}

.pos-modern .pos-points-card .puntos {
  color: #2563eb;
  font-size: 2.2rem;
  line-height: 1;
}

.pos-modern .pos-document-identity {
  flex-direction: column;
  border-color: #bfdbfe;
  background: linear-gradient(145deg,#fafdff,#eef7ff);
}

.pos-modern .pos-document-identity h1 {
  color: #0b234a;
  font-size: 1.55rem;
}

.pos-modern .pos-document-identity p {
  padding: 4px 9px !important;
  color: #1d4ed8;
  background: #dbeafe !important;
  font-size: .6rem !important;
  font-weight: 850;
  letter-spacing: .035em;
  text-transform: uppercase;
}

.pos-modern .pos-seller-card .input-group {
  width: 100%;
  margin: 0 !important;
}

.pos-modern .pos-seller-card .input-group-text {
  min-height: 40px;
  color: #475569;
  background: #f1f5f9;
  font-size: .7rem;
  font-weight: 850;
  text-transform: uppercase;
}

.pos-modern .pos-seller-card .form-control {
  min-height: 40px;
  color: #0f213d;
  background: #fff;
  font-size: .78rem;
  font-weight: 800;
}

@media (max-width: 1250px) {
  .pos-modern .pos-document-layout {
    grid-template-columns: .8fr 1.15fr .9fr;
  }
  .pos-modern .pos-document-identity {
    min-width: 0;
  }
}
</style>
<style>
.pos-product-media-actions {
  display: inline-flex;
  flex-flow: row nowrap;
  align-items: center;
  justify-content: center;
  gap: 5px;
  white-space: nowrap;
}

.pos-product-media-actions .btn {
  width: 41px !important;
  min-width: 41px !important;
  height: 41px !important;
  min-height: 41px !important;
  margin: 0 !important;
  padding: 0 !important;
}

.pos-product-media-actions .btn i {
  margin: 0 !important;
}

/* Animated icon finish inspired by the supplied reference */
.pos-modern .btn::before {
  z-index: 3;
  mix-blend-mode: screen;
}

.pos-modern .btn > i,
.pos-modern .btn > span,
.pos-modern .btn > strong {
  position: relative;
  z-index: 2;
}

.pos-modern .btn i {
  display: inline-flex;
  width: 25px;
  height: 25px;
  margin-right: 5px;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(255,255,255,.16);
  box-shadow: inset 0 1px 0 rgba(255,255,255,.24);
  transform-origin: center;
}

.pos-modern .btn-warning i {
  background: rgba(255,255,255,.34);
}

.pos-modern .pos-pay-button i {
  width: 33px;
  height: 33px;
  background: rgba(255,255,255,.2);
  font-size: 1rem;
}

.pos-modern .pos-print-button {
  background: linear-gradient(135deg, #64748b, #334155) !important;
}

.pos-modern .btn:hover .fa-shopping-cart {
  animation: posCartDrive .62s cubic-bezier(.2,.8,.2,1);
}

.pos-modern .btn:hover .fa-plus-circle {
  animation: posAddSpin .55s cubic-bezier(.2,.8,.2,1);
}

.pos-modern .btn:hover .fa-file-invoice {
  animation: posDocumentFlip .65s ease;
}

.pos-modern .btn:hover .fa-search {
  animation: posSearchPulse .7s ease;
}

.pos-modern .btn:hover .fa-print {
  animation: posPrinterShake .55s ease;
}

.pos-modern .btn:hover .fa-keyboard,
.pos-modern .btn:hover .fa-desktop {
  animation: posDeviceFloat .7s ease;
}

.pos-modern .btn:hover .fa-bars {
  animation: posMenuWave .62s ease;
}

.pos-modern .btn:hover .fa-tags {
  animation: posTagSwing .7s ease;
}

.pos-modern .btn:hover .fa-credit-card {
  animation: posCardFlip .7s ease;
}

.pos-modern .btn:hover .fa-barcode {
  animation: posScanner .7s ease;
}

.pos-modern .pos-pay-button .fa-shopping-cart {
  animation: posCartIdle 2.5s ease-in-out infinite;
}

.pos-modern .pos-pay-button:hover .fa-shopping-cart {
  animation: posCartDrive .62s cubic-bezier(.2,.8,.2,1);
}

.pos-modern .pos-actions .btnAction {
  display: flex;
  width: 100%;
  height: 46px;
  min-height: 46px;
  margin: 0 !important;
  padding: 0 48px;
  align-items: center;
  justify-content: center;
  line-height: 1.1;
  text-align: center;
  white-space: nowrap;
}

.pos-modern .pos-actions .btnAction i {
  position: absolute;
  left: 12px;
  width: 28px;
  height: 28px;
  margin: 0;
}

.pos-modern .pos-actions .btnAction::after {
  position: absolute;
  z-index: 1;
  right: 14px;
  width: 6px;
  height: 6px;
  content: "";
  border: 2px solid rgba(255,255,255,.72);
  border-bottom: 0;
  border-left: 0;
  opacity: 0;
  transform: translateX(-7px) rotate(45deg);
  transition: opacity .2s ease, transform .2s ease;
}

.pos-modern .pos-actions .btnAction:hover::after {
  opacity: 1;
  transform: translateX(0) rotate(45deg);
}

.pos-modern .pos-actions .centrado {
  display: grid !important;
  width: 100%;
  margin: 0 !important;
  padding: 14px 12px;
  grid-template-columns: minmax(0, 1fr);
  gap: 10px;
}

.pos-modern .pos-actions .centrado > * {
  width: 100%;
  min-width: 0;
  margin: 0 !important;
}

.pos-modern .pos-actions .centrado > div,
.pos-modern .pos-actions .centrado > span {
  display: block;
}

.pos-modern .pos-actions .centrado > div .btn,
.pos-modern .pos-actions .centrado > span .btn {
  width: 100%;
  height: 46px;
  margin: 0 !important;
}

.pos-modern .pos-quick-actions {
  display: grid !important;
  width: 100%;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px !important;
}

.pos-modern .pos-quick-actions .btn-group,
.pos-modern .pos-quick-actions > * {
  display: flex;
  width: 100%;
  min-width: 0;
  margin: 0 !important;
}

.pos-modern .pos-quick-actions .btn {
  display: flex;
  width: 100%;
  min-width: 0;
  min-height: 40px;
  margin: 0 !important;
  padding: 7px 12px;
  align-items: center;
  justify-content: center;
  border-radius: 10px !important;
  font-size: .78rem;
  line-height: 1.15;
  text-align: center;
  white-space: normal;
}

.pos-modern .pos-quick-actions .btn i {
  position: static;
  flex: 0 0 24px;
  width: 24px;
  height: 24px;
  margin-right: 6px;
}

@media (max-width: 420px) {
  .pos-modern .pos-quick-actions {
    grid-template-columns: minmax(0, 1fr);
  }
}

/* Compact, uniform button alignment */
.pos-modern .pos-pay-row {
  display: flex;
  width: 100%;
  margin-right: 0;
  margin-left: 0;
  align-items: center;
  justify-content: center;
}

.pos-modern .pos-pay-row .pos-pay-button.btn {
  width: 300px !important;
  max-width: calc(100% - 24px);
  min-height: 50px;
  margin-right: auto !important;
  margin-left: auto !important;
  padding: 0 52px;
  justify-content: center;
  border-radius: 999px !important;
  background: linear-gradient(180deg, #2fb151 0%, #25a447 100%);
  box-shadow:
    0 7px 3px rgba(15,23,42,.46),
    0 13px 22px rgba(15,23,42,.17),
    inset 0 1px 0 rgba(255,255,255,.24);
  font-size: 1.12rem !important;
  letter-spacing: .035em;
  animation: none;
}

.pos-modern .pos-pay-row .pos-pay-button.btn i {
  position: absolute;
  left: 47px;
  width: 30px;
  height: 30px;
  margin: 0;
  background: transparent;
  box-shadow: none;
  font-size: 1.15rem;
}

.pos-modern .pos-pay-row .pos-pay-button.btn strong {
  display: none;
}

.pos-modern .pos-actions .centrado {
  justify-items: start;
}

.pos-modern .pos-actions .centrado > *,
.pos-modern .pos-actions .centrado > div .btn,
.pos-modern .pos-actions .centrado > span .btn,
.pos-modern .pos-actions .btnAction {
  width: 100%;
  max-width: 300px;
}

.pos-modern .pos-actions .btnAction,
.pos-modern .pos-actions .centrado > div .btn,
.pos-modern .pos-actions .centrado > span .btn {
  height: 43px;
  min-height: 43px;
  padding: 0 44px;
  border-radius: 999px !important;
  box-shadow:
    0 6px 3px rgba(15,23,42,.42),
    0 11px 18px rgba(15,23,42,.14),
    inset 0 1px 0 rgba(255,255,255,.25);
  font-size: .96rem;
  font-weight: 800;
}

.pos-modern .pos-actions .btnAction i {
  left: 10px;
  width: 27px;
  height: 27px;
}

.pos-modern .pos-quick-actions .btn {
  min-height: 40px;
  box-shadow:
    0 5px 2px rgba(15,23,42,.34),
    0 9px 15px rgba(15,23,42,.12),
    inset 0 1px 0 rgba(255,255,255,.24);
  font-size: .84rem;
  font-weight: 750;
}

.pos-modern .pos-actions .btnAction:hover,
.pos-modern .pos-quick-actions .btn:hover {
  transform: translateY(-2px);
  box-shadow:
    0 8px 3px rgba(15,23,42,.4),
    0 14px 21px rgba(15,23,42,.16),
    inset 0 1px 0 rgba(255,255,255,.34);
}

.pos-modern .pos-actions .btnAction:active,
.pos-modern .pos-quick-actions .btn:active,
.pos-modern .pos-pay-row .pos-pay-button.btn:active {
  transform: translateY(4px);
  box-shadow:
    0 2px 1px rgba(15,23,42,.42),
    0 5px 9px rgba(15,23,42,.12),
    inset 0 1px 0 rgba(255,255,255,.2);
}

.pos-modern .pos-summary .btn i,
.pos-modern .formResponsive .btn i {
  width: auto;
  height: auto;
  margin: 0;
  background: transparent;
  box-shadow: none;
}

@keyframes posCartIdle {
  0%, 100% { transform: translateX(0) rotate(0); }
  50% { transform: translateX(2px) rotate(-3deg); }
}

@keyframes posCartDrive {
  0% { transform: translateX(-2px) rotate(0); }
  35% { transform: translateX(5px) rotate(-7deg); }
  62% { transform: translateX(-2px) rotate(5deg); }
  100% { transform: translateX(0) rotate(0); }
}

@keyframes posAddSpin {
  0% { transform: scale(.8) rotate(0); }
  55% { transform: scale(1.2) rotate(190deg); }
  100% { transform: scale(1) rotate(360deg); }
}

@keyframes posDocumentFlip {
  0%, 100% { transform: perspective(80px) rotateY(0); }
  45% { transform: perspective(80px) rotateY(65deg) translateY(-2px); }
}

@keyframes posSearchPulse {
  0%, 100% { transform: scale(1) rotate(0); }
  45% { transform: scale(1.22) rotate(-12deg); }
}

@keyframes posPrinterShake {
  0%, 100% { transform: translate(0,0); }
  20% { transform: translate(-2px,1px); }
  40% { transform: translate(2px,-1px); }
  60% { transform: translate(-1px,-1px); }
  80% { transform: translate(1px,1px); }
}

@keyframes posDeviceFloat {
  0%, 100% { transform: translateY(0); }
  45% { transform: translateY(-4px) scale(1.06); }
}

@keyframes posMenuWave {
  0%, 100% { transform: scaleX(1); }
  35% { transform: scaleX(.68); }
  65% { transform: scaleX(1.16); }
}

@keyframes posTagSwing {
  0%, 100% { transform: rotate(0); }
  30% { transform: rotate(-15deg); }
  65% { transform: rotate(12deg); }
}

@keyframes posCardFlip {
  0%, 100% { transform: perspective(80px) rotateY(0); }
  50% { transform: perspective(80px) rotateY(180deg); }
}

@keyframes posScanner {
  0%, 100% { filter: brightness(1); transform: scaleY(1); }
  50% { filter: brightness(1.8); transform: scaleY(.72); }
}
</style>
<style>
/* Modern POS visual system */
.pos-modern {
  --pos-primary: #2563eb;
  --pos-primary-dark: #1d4ed8;
  --pos-success: #16a34a;
  --pos-success-dark: #15803d;
  --pos-ink: #172033;
  --pos-muted: #64748b;
  --pos-line: #e2e8f0;
  --pos-surface: #ffffff;
  --pos-canvas: #f4f7fb;
  background:
    radial-gradient(circle at 95% 0%, rgba(37, 99, 235, .08), transparent 28rem),
    var(--pos-canvas);
  color: var(--pos-ink);
  min-height: 100vh;
  padding: 18px !important;
}

.pos-modern .pos-shell.card {
  background: transparent !important;
  border: 0;
  border-radius: 20px;
  box-shadow: none;
}

.pos-modern .card-header {
  padding: 0 0 12px;
  background: transparent;
  border: 0;
}

.pos-modern .card-header-pills {
  margin: 0;
}

.pos-modern .nav-pills {
  gap: 8px;
}

.pos-modern .nav-pills .nav-link {
  min-width: 112px;
  padding: 10px 18px;
  border: 1px solid var(--pos-line);
  border-radius: 12px;
  background: rgba(255,255,255,.82);
  color: var(--pos-muted);
  font-size: .9rem !important;
  font-weight: 700;
  transition: transform .18s ease, box-shadow .18s ease, color .18s ease, background .18s ease;
}

.pos-modern .nav-pills .nav-link:hover {
  color: var(--pos-primary);
  transform: translateY(-2px);
  box-shadow: 0 8px 20px rgba(30, 64, 175, .1);
}

.pos-modern .nav-pills .nav-link.active {
  border-color: var(--pos-primary);
  background: linear-gradient(135deg, var(--pos-primary), #3b82f6);
  color: #fff;
  box-shadow: 0 10px 22px rgba(37, 99, 235, .25);
}

.pos-modern .tab-content {
  overflow: visible;
}

.pos-modern .tab-pane > .container-fluid {
  padding: 18px 20px 22px;
  border: 1px solid rgba(226, 232, 240, .9);
  border-radius: 18px;
  background: rgba(255, 255, 255, .92);
  box-shadow: 0 20px 55px rgba(15, 23, 42, .08);
  backdrop-filter: blur(8px);
}

.pos-modern .form-control,
.pos-modern .custom-select,
.pos-modern .input-group-text {
  min-height: 40px;
  border-color: var(--pos-line);
  font-size: .86rem;
}

.pos-modern .form-control,
.pos-modern .custom-select {
  color: var(--pos-ink);
  background-color: #fff;
  transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
}

.pos-modern .form-control:focus,
.pos-modern .custom-select:focus {
  border-color: #60a5fa;
  box-shadow: 0 0 0 4px rgba(37, 99, 235, .11);
}

.pos-modern .input-group-text {
  border-color: var(--pos-line);
  background: #f8fafc;
  color: #475569;
  font-weight: 700;
}

.pos-modern h1 {
  color: var(--pos-ink);
  font-size: 2rem;
  font-weight: 800;
  letter-spacing: -.04em;
}

.pos-modern .puntos {
  color: var(--pos-primary);
  font-size: 2.65rem;
  font-weight: 800;
  letter-spacing: -.05em;
  line-height: 1;
}

.pos-modern .formResponsive {
  margin: 15px 0 10px;
  padding: 10px;
  border: 1px solid #bfdbfe;
  border-radius: 14px;
  background: #eff6ff;
  box-shadow: 0 8px 22px rgba(37, 99, 235, .08);
}

.pos-modern .formResponsive .form-control {
  min-height: 46px;
  border-color: transparent;
  font-size: .95rem;
  font-weight: 500;
}

.pos-modern .formResponsive .btn {
  min-width: 48px;
  border: 0;
  border-radius: 10px;
}

.pos-modern .pos-cart {
  height: 35vh;
  min-height: 280px;
  overflow: hidden;
  border: 1px solid var(--pos-line);
  border-radius: 15px;
  background: #fff;
}

.pos-modern .clsCarrito {
  max-height: 35vh;
  min-height: 280px;
  margin: 0 !important;
  overflow: auto;
}

.pos-modern .tblCarrito {
  margin: 0;
  font-size: .79rem;
}

.pos-modern .tblCarrito thead th {
  position: sticky;
  top: 0;
  z-index: 2;
  padding: 13px 10px;
  border: 0;
  border-bottom: 1px solid var(--pos-line);
  background: #f8fafc;
  color: #475569;
  font-size: .72rem;
  font-weight: 800;
  letter-spacing: .035em;
  text-transform: uppercase;
  white-space: nowrap;
}

.pos-modern .tblCarrito tbody td {
  padding: 10px;
  border-color: #edf2f7;
  vertical-align: middle;
}

.pos-modern .tblCarrito.table-striped tbody tr:nth-of-type(odd) {
  background: #fbfdff;
}

.pos-modern .tblCarrito tbody tr {
  transition: background .16s ease, transform .16s ease;
}

.pos-modern .tblCarrito tbody tr:hover {
  background: #eff6ff;
}

.pos-modern .tblCarrito .b-table-empty-row td {
  padding: 82px 20px;
  color: var(--pos-muted);
  font-size: .95rem;
  font-weight: 600;
  text-align: center;
}

.pos-modern .pos-pay-row {
  position: relative;
  z-index: 3;
  margin-top: -30px;
  pointer-events: none;
}

.pos-modern .pos-pay-button.btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 11px;
  width: min(440px, calc(100% - 30px)) !important;
  max-width: 440px;
  min-height: 58px;
  margin-top: 0 !important;
  border: 0;
  border-radius: 16px !important;
  background: linear-gradient(135deg, var(--pos-success), #22c55e);
  box-shadow: 0 14px 28px rgba(22, 163, 74, .28);
  font-size: 1.05rem !important;
  font-weight: 800;
  letter-spacing: 0;
  pointer-events: auto;
  transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
}

.pos-modern .pos-pay-button strong {
  padding-left: 12px;
  border-left: 1px solid rgba(255,255,255,.4);
  font-size: 1.08rem;
}

.pos-modern .pos-pay-button:hover {
  transform: translateY(-3px) scale(1.01);
  box-shadow: 0 18px 34px rgba(22, 163, 74, .34);
  filter: saturate(1.08);
}

.pos-modern .pos-pay-button:active {
  transform: translateY(0) scale(.985);
}

.pos-modern .pos-checkout {
  margin-top: 18px !important;
  padding-top: 18px;
  border-top: 1px solid var(--pos-line);
}

.pos-modern .btnAction {
  min-height: 42px;
  margin: 4px 0;
  border: 0;
  border-radius: 11px !important;
  box-shadow: 0 6px 14px rgba(15, 23, 42, .11);
  font-size: .86rem;
  font-weight: 700;
  transition: transform .17s ease, box-shadow .17s ease, filter .17s ease;
}

.pos-modern .btnAction:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 20px rgba(15, 23, 42, .16);
  filter: brightness(1.04);
}

.pos-modern .btnAction:active {
  transform: scale(.98);
}

.pos-modern .pos-actions .centrado {
  padding: 12px;
  border: 1px solid var(--pos-line);
  border-radius: 14px;
  background: #f8fafc;
}

.pos-modern .panel-opciones,
.pos-modern .resumen-venta {
  border: 1px solid var(--pos-line) !important;
  border-radius: 14px;
  box-shadow: 0 8px 22px rgba(15, 23, 42, .06);
}

.pos-modern .opcion-header {
  background: #f8fafc;
}

.pos-modern .pos-summary .table-footer {
  overflow: hidden;
  background: #fff;
}

.pos-modern .pos-summary .table-footer td {
  border-color: #edf2f7;
}

.pos-modern .resumen-total-monto,
.pos-modern .total-final,
.pos-modern .total-final-label {
  background: #dcfce7 !important;
  color: #166534 !important;
}

.pos-modern .subtotal-final-label {
  background: #fff1f2 !important;
  color: #9f1239 !important;
}

.pos-modern .btn:not(:disabled) {
  cursor: pointer;
}

.pos-modern .btn:focus-visible,
.pos-modern input:focus-visible,
.pos-modern select:focus-visible {
  outline: 3px solid rgba(37, 99, 235, .3);
  outline-offset: 2px;
}

@media (max-width: 1199px) {
  .pos-modern {
    padding: 10px !important;
  }
  .pos-modern .tab-pane > .container-fluid {
    padding: 14px;
  }
  .pos-modern .pos-summary,
  .pos-modern .pos-actions {
    margin-top: 14px;
  }
}

@media (max-width: 767px) {
  .pos-modern {
    padding: 6px !important;
  }
  .pos-modern .card-header {
    overflow-x: auto;
  }
  .pos-modern .nav-pills {
    flex-wrap: nowrap;
  }
  .pos-modern .pos-cart,
  .pos-modern .clsCarrito {
    height: 48vh;
    max-height: 48vh;
    min-height: 300px;
  }
  .pos-modern .pos-pay-row {
    margin-top: 10px;
  }
  .pos-modern .pos-pay-button.btn {
    width: 100% !important;
    max-width: none;
  }
}

@media (prefers-reduced-motion: reduce) {
  .pos-modern *,
  .pos-modern *::before,
  .pos-modern *::after {
    scroll-behavior: auto !important;
    transition-duration: .01ms !important;
    animation-duration: .01ms !important;
    animation-iteration-count: 1 !important;
  }
}

/* Premium buttons and luminous hover */
.pos-modern .btn {
  position: relative;
  isolation: isolate;
  overflow: hidden;
  border-width: 0;
  font-weight: 750;
  letter-spacing: .01em;
  text-shadow: 0 1px 1px rgba(0, 0, 0, .12);
  -webkit-tap-highlight-color: transparent;
}

.pos-modern .btn::before {
  position: absolute;
  z-index: -1;
  top: -60%;
  left: -75%;
  width: 46%;
  height: 220%;
  content: "";
  background: linear-gradient(100deg, transparent 0%, rgba(255,255,255,.12) 28%, rgba(255,255,255,.72) 50%, rgba(255,255,255,.12) 72%, transparent 100%);
  filter: blur(1px);
  opacity: 0;
  pointer-events: none;
  transform: rotate(16deg);
}

.pos-modern .btn:not(:disabled):hover::before {
  left: 135%;
  opacity: 1;
  transition: left .72s cubic-bezier(.2,.75,.25,1), opacity .15s ease;
}

.pos-modern .btn i {
  display: inline-block;
  transition: transform .2s ease;
}

.pos-modern .btn:not(:disabled):hover i {
  transform: translateY(-1px) scale(1.12);
}

.pos-modern .btn-primary {
  background: linear-gradient(135deg, #2563eb, #0284c7);
  box-shadow: 0 8px 18px rgba(37,99,235,.25);
}

.pos-modern .btn-success {
  background: linear-gradient(135deg, #16a34a, #22c55e);
  box-shadow: 0 8px 18px rgba(22,163,74,.24);
}

.pos-modern .btn-warning {
  background: linear-gradient(135deg, #f59e0b, #facc15);
  color: #172033;
  text-shadow: none;
  box-shadow: 0 8px 18px rgba(245,158,11,.25);
}

.pos-modern .btn-info {
  background: linear-gradient(135deg, #0891b2, #06b6d4);
  box-shadow: 0 8px 18px rgba(8,145,178,.23);
}

.pos-modern .btn-danger {
  background: linear-gradient(135deg, #dc2626, #f43f5e);
  box-shadow: 0 8px 18px rgba(220,38,38,.24);
}

.pos-modern .btn-secondary {
  background: linear-gradient(135deg, #475569, #64748b);
  box-shadow: 0 8px 18px rgba(51,65,85,.22);
}

.pos-modern .btnAction {
  min-height: 46px;
  border: 1px solid rgba(255,255,255,.24);
  border-radius: 999px !important;
  box-shadow: 0 9px 18px rgba(15,23,42,.18), inset 0 1px 0 rgba(255,255,255,.28);
  font-size: .9rem;
  text-transform: uppercase;
}

.pos-modern .btnAction:not(:disabled):hover {
  transform: translateY(-3px);
  box-shadow: 0 14px 25px rgba(15,23,42,.23), 0 0 0 4px rgba(59,130,246,.08), inset 0 1px 0 rgba(255,255,255,.38);
}

.pos-modern .pos-pay-button.btn {
  min-height: 62px;
  border: 1px solid rgba(255,255,255,.35);
  border-radius: 999px !important;
  background: linear-gradient(135deg, #16a34a 0%, #22c55e 55%, #16a34a 100%);
  background-size: 180% 180%;
  box-shadow: 0 16px 30px rgba(22,163,74,.28), 0 0 28px rgba(34,197,94,.12), inset 0 1px 0 rgba(255,255,255,.36);
  font-size: 1.08rem !important;
  text-transform: uppercase;
  animation: posPayBreath 3.2s ease-in-out infinite;
}

.pos-modern .pos-pay-button.btn:hover {
  background-position: 100% 50%;
  box-shadow: 0 19px 36px rgba(22,163,74,.34), 0 0 34px rgba(34,197,94,.2), inset 0 1px 0 rgba(255,255,255,.45);
}

.pos-modern .nav-pills .nav-link {
  position: relative;
  overflow: hidden;
  border-radius: 999px;
}

.pos-modern .nav-pills .nav-link.active::after {
  position: absolute;
  top: 0;
  left: -90%;
  width: 48%;
  height: 100%;
  content: "";
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.45), transparent);
  transform: skewX(-20deg);
  animation: posTabShine 3.4s ease-in-out infinite;
}

.pos-modern .pos-actions .centrado {
  padding: 15px 13px;
  border: 1px solid rgba(203,213,225,.8);
  border-radius: 18px;
  background: linear-gradient(145deg, rgba(255,255,255,.98), rgba(248,250,252,.9));
  box-shadow: 0 14px 35px rgba(15,23,42,.08), inset 0 1px 0 #fff;
}

.pos-modern .resumen-venta {
  border-radius: 18px;
  box-shadow: 0 16px 35px rgba(15,23,42,.1), inset 0 1px 0 #fff;
}

.pos-modern .resumen-venta td {
  vertical-align: middle;
  font-weight: 700;
}

.pos-modern .pos-summary .table-footer tr td:first-child {
  width: 64px;
  text-align: center;
}

.pos-modern .pos-summary .table-footer tr td:first-child .btn {
  width: 40px;
  height: 40px;
  margin: 0 !important;
  border-radius: 50%;
  box-shadow: 0 9px 17px rgba(15,23,42,.18);
}

.pos-modern .resumen-total-monto,
.pos-modern .total-final {
  font-size: 1.75rem !important;
  font-weight: 850 !important;
  letter-spacing: -.04em;
}

@keyframes posPayBreath {
  0%, 100% { transform: translateY(0); filter: saturate(1); }
  50% { transform: translateY(-2px); filter: saturate(1.12); }
}

@keyframes posTabShine {
  0%, 55% { left: -90%; }
  78%, 100% { left: 145%; }
}

/* Keep the animated icon layer above the gradient and the light sweep visible. */
.pos-modern .btn::before {
  z-index: 3;
  mix-blend-mode: screen;
}

.pos-modern .btn > i {
  display: inline-flex;
}
</style>
  <style>
/* .nav-tabs .nav-link.active,
  .nav-tabs .nav-item.show .nav-link {
    background-color: #e6e6e6;
    border-bottom: none;
  } */
.titleTabs {
  font-size: 1.2rem !important;
  font-weight: bold;
}

.popover {
  min-width: 400px !important;
  /* Estilo global para todos los popovers */
}
.headerInfo .input-group-text {
  font-weight: bold;
  font-size: 0.8rem;
}
.headerInfo input {
  font-size: 0.8rem;
}
.buscador .input-group-text {
  font-weight: bold;
  font-size: 0.8rem;
}
.buscador input {
  font-size: 0.8rem;
}
.docInfo .input-group-text {
  font-weight: bold;
  font-size: 0.9rem;
}
.docInfo input {
  font-size: 0.9rem;
  font-weight: bold;
}
.docInfo select {
  font-size: 0.9rem;
  height: auto !important;
}
.clienteInfo * {
  font-size: 0.8rem;
  height: auto !important;
}
#productoPunto:hover {
  transform: scale(1.02);
  transition: 0.5s all;
  box-shadow: 0 20px 80px rgba(0, 0, 0, 0.25);
}
#modalCantidad .modal-dialog {
  max-width: 55% !important;
}
.btnAction {
  margin: 2px 0;
  border-radius: 25px !important;
  -webkit-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
  -moz-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
  box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
  font-size: 1.2rem;
  font-weight: bold;
}

.ocultarMovil {
  display: inline;
}

.mostrarMovil {
  display: none;
}

.b-input-group {
  display: flex;
  align-items: center;
}

.txtAutoComplete {
  min-width: 960px;
}

.divCarrito {
  height: 40vh;
}

.clsCarrito {
  position: relative;
  max-height: 40vh;
  overflow-y: scroll;
}

.contenedor-carrito{
    min-height:200px;
}

.resumen-venta{
    box-shadow:0 4px 12px rgba(0,0,0,.08);
    border-radius:12px;
    overflow:hidden;
    background:#fff;
}

.resumen-venta td{
    padding:12px;
}

.resumen-subtotal{
    font-size:18px;
    font-weight:700;
}

.resumen-total-label{
    font-size:22px;
    font-weight:900;
    color:#222;
}

.resumen-total-monto{
   background:#c8f0d0 !important;
    font-size:27px;
    font-weight:900;
    text-align:left;
}

.total-final-label{
    background:#c8f0d0 !important;
    color:#0f5132 !important;
    font-size:22px;
    font-weight:900;
}

.subtotal-final-label{
    background:#ffc7c0 !important;
    color:#424242 !important;
    font-size:22px;
    font-weight:900;
}
.total-final{
    background:#c8f0d0 !important;
    color:#0f5132 !important;
    font-size:28px;
    font-weight:900;
}

.resumen-venta tr:last-child td{
    padding-top:18px;
    padding-bottom:18px;
}

.resumen-vuelto{
    font-size:24px;
    font-weight:900;
    color:#28a745;
    margin-top:5px;
}

.vuelto-contenedor{
    display:flex;
    align-items:center;
    gap:10px;
    margin-top:-20px;
}

.vuelto-contenedor label{
    margin:0;
    font-weight:700;
    font-size:16px;
}
.panel-opciones {
  border: 1px solid #dee2e6;
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
}

.opcion-item {
  border-bottom: 1px solid #f0f0f0;
}

.opcion-item:last-child {
  border-bottom: none;
}

.opcion-header {
  padding: 10px 14px;
  background: #f8f9fa;
  transition: background 0.2s;
  cursor: pointer;
}

.opcion-header:hover {
  background: #e9ecef;
}

.opcion-checkbox {
  margin: 0;
  width: 100%;
}

.opcion-label {
  font-size: 0.88rem;
  font-weight: 600;
  color: #444;
}

.opcion-body {
  padding: 10px 14px;
  background: #fff;
  border-top: 1px solid #f0f0f0;
}
@media (max-width: 1024px) {
  .txtAutoComplete { min-width: 60% !important; }
  .divCarrito      { height: 60vh; }
  .clsCarrito      { position: relative; max-height: 60vh; overflow-y: scroll; }
  .formResponsive  { max-width: 100%; }
  .autocomplete-input { max-width: 100% !important; }
  .tblCarrito      { font-size: 0.75rem; }
  /* ocultarMovil y mostrarMovil NO se tocan aquí — tablet ve todo */
}

@media (max-width: 767px) {
  .ocultarMovil { display: none !important; }
  .mostrarMovil { display: block !important; }
  .txtAutoComplete { min-width: 100% !important; }
  .divCarrito      { height: 70vh; }
  .clsCarrito      { position: relative; max-height: 70vh; overflow-y: scroll; }
  .tblCarrito      { font-size: 0.7rem; }
}
</style>
<style>
#modalImagen .modal-dialog {
  max-width: 75% !important;
}
.imagenGrande {
  width: 100%;
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
canvas {
  width: 100%;
  height: 100%;
}
.cameraBarra {
  height: 50vh;
  width: auto;
  margin: 0 auto;
}
.mostrarMovil {
  display: none;
}
@media only screen and (max-width: 1440px) {
  #modalCodigoBarra .modal-dialog {
    max-width: 50vw !important;
    max-height: 50vh !important;
  }
  .cameraBarra {
    height: 50vh;
    width: auto;
  }
}
@media only screen and (max-width: 750px) {
  #modalCodigoBarra .modal-dialog {
    max-width: 100vw !important;
    max-height: 80vh !important;
  }
  .cameraBarra {
    height: 80vh;
    width: auto;
  }
  .ocultarMovil {
    display: none;
  }
  .mostrarMovil {
    display: block;
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
}
@media only screen and (max-width: 700px) {
  /* For desktop: */
  .ocularMovil {
    display: none;
  }
}
</style>
<style>
/* Header adapted from the CRM reference, scoped to the jcuzquito POS. */
.pos-modern .pos-sale-header,
.pos-modern .pos-document-summary {
  padding-top: 8px;
  padding-bottom: 8px;
}

.pos-modern .pos-sale-header > .row {
  margin: 0;
  padding: 7px 8px 9px;
  border-radius: 16px;
  background: #fff;
}

.pos-modern .pos-doc-field .input-group {
  min-height: 44px;
  border-radius: 14px;
  box-shadow: 0 7px 17px rgba(11,35,74,.08);
}

.pos-modern .pos-doc-field .input-group-text {
  min-height: 44px;
  padding: 0 15px;
  border-color: #b8d9f5;
  border-radius: 14px 0 0 14px;
  color: #0b234a;
  background: linear-gradient(135deg,#edf8ff,#dff2ff);
  font-size: .88rem;
  font-weight: 900;
}

.pos-modern .pos-doc-field .form-control {
  min-height: 44px;
  border-color: #b8d9f5;
  color: #0a1d3c;
  background: #fff;
  font-size: .9rem;
  font-weight: 900;
}

.pos-modern .pos-client-field {
  margin-top: 7px !important;
}

.pos-modern .pos-client-field .form-group {
  margin-bottom: 4px;
}

.pos-modern .pos-client-field .input-group {
  min-height: 48px;
  border: 2px solid #bfe7ff;
  border-radius: 8px 15px 15px 8px;
  background: #fff;
  box-shadow: 0 9px 20px rgba(11,35,74,.12);
}

.pos-modern .pos-client-field .form-control {
  min-height: 46px;
  border: 0;
  color: #27354a;
  background: #fff;
  font-size: .94rem;
  font-weight: 800;
}

.pos-modern .pos-client-field .input-group-append .btn {
  min-width: 118px;
  min-height: 46px;
  padding: 0 18px;
  border-radius: 0 13px 13px 0;
  color: #fff;
  background: linear-gradient(135deg,#0b2d5d,#075da9 58%,#098ed5);
  box-shadow: 8px 8px 18px rgba(11,35,74,.28);
  font-size: .95rem;
  font-weight: 900;
}

.pos-modern .pos-client-field .input-group-append .btn:hover {
  background: linear-gradient(135deg,#081f43,#0a4f91,#087fc3);
  box-shadow: 10px 11px 22px rgba(11,35,74,.36);
}

.pos-modern .pos-client-field .autocomplete ul {
  margin-top: 7px;
  padding: 4px;
  border: 2px solid #bfe7ff;
  border-radius: 11px;
  background: #fff;
  box-shadow: 0 16px 28px rgba(11,35,74,.22);
}

.pos-modern .pos-client-field .autocomplete ul li a {
  min-height: 44px;
  padding: 11px 13px;
  border-radius: 8px;
  color: #fff;
  background: linear-gradient(135deg,#0b2d5d,#0877bd);
  font-size: .8rem;
  font-weight: 900;
}

.pos-modern .pos-document-layout {
  min-height: 118px;
  padding: 8px 10px !important;
  border: 1px solid #dbe6f1;
  border-radius: 16px;
  background: linear-gradient(145deg,#fff,#f5faff);
  box-shadow: 0 9px 22px rgba(15,23,42,.07);
}

.pos-modern .pos-document-identity {
  min-width: 230px;
  padding: 14px 18px 11px;
  border: 1px solid #c9e3f7;
  border-radius: 17px;
  background: linear-gradient(145deg,#fff,#eff8ff);
}

.pos-modern .pos-document-identity h1 {
  color: #061a38;
  font-size: 1.72rem;
  font-weight: 900;
  letter-spacing: -.035em;
}

.pos-modern .pos-document-identity p {
  color: #0b234a;
  background: #dff3ff !important;
  font-size: .76rem !important;
  font-weight: 800;
}

@media (max-width: 1100px) {
  .pos-modern .pos-document-identity {
    min-width: 180px;
  }
}
</style>
<style>
/* Final header composition — intentionally last in the cascade. */
.pos-modern .pos-sale-header > .row,
.pos-modern .pos-document-layout {
  position: relative;
  min-height: 128px;
  border: 1px solid #dbe5f0;
  border-radius: 18px;
  background: radial-gradient(circle at 0 0,rgba(37,99,235,.06),transparent 34%),linear-gradient(145deg,#fff,#f7faff);
  box-shadow: 0 14px 32px rgba(15,23,42,.08);
}
.pos-modern .pos-sale-header > .row::before,
.pos-modern .pos-document-layout::before {
  position:absolute;z-index:1;top:-1px;right:22px;left:22px;height:4px;content:"";
  border-radius:0 0 8px 8px;background:linear-gradient(90deg,#0b234a,#2563eb,#06b6d4);
}
.pos-modern .pos-sale-header > .row{margin:0;padding:10px 10px 8px}
.pos-modern .pos-doc-field,.pos-modern .pos-header-field{padding-right:6px;padding-left:6px}
.pos-modern .pos-doc-field .input-group,.pos-modern .pos-header-field .input-group{min-height:40px;margin-top:5px!important;border-radius:12px;box-shadow:0 6px 14px rgba(15,23,42,.06)}
.pos-modern .pos-doc-field .input-group-text,.pos-modern .pos-header-field .input-group-text{min-height:40px;padding:0 13px;border-color:#d3e0ec;border-radius:12px 0 0 12px!important;color:#475569;background:#f4f7fb;font-size:.72rem;font-weight:850;text-transform:uppercase}
.pos-modern .pos-doc-field .form-control,.pos-modern .pos-header-field .form-control{min-height:40px;border-color:#d3e0ec;color:#0f213d;font-size:.8rem;font-weight:800}
.pos-modern .pos-client-field{margin-top:4px!important;padding-right:6px;padding-left:6px}
.pos-modern .pos-client-field .input-group{min-height:46px;border:1px solid #bcd8ef;border-radius:13px;background:#fff;box-shadow:0 9px 20px rgba(11,35,74,.1)}
.pos-modern .pos-client-field .input-group::before{display:inline-flex;width:42px;min-width:42px;align-items:center;justify-content:center;content:"\f007";border-right:1px solid #d7e4ef;border-radius:12px 0 0 12px;color:#2563eb;background:#eef6ff;font-family:"Font Awesome 5 Free";font-weight:900}
.pos-modern .pos-client-field .form-control{min-height:44px;padding-left:12px;font-size:.88rem;font-weight:750}
.pos-modern .pos-client-field .input-group-append .btn{min-width:110px;min-height:44px;border-radius:0 12px 12px 0;background:linear-gradient(135deg,#0b234a,#075da9);box-shadow:6px 7px 15px rgba(11,35,74,.2);font-size:.84rem}
.pos-modern .pos-document-layout{display:grid!important;padding:12px 14px!important;align-items:stretch!important;grid-template-columns:minmax(155px,.9fr) minmax(210px,1.15fr) minmax(160px,.85fr);gap:10px!important}
.pos-modern .pos-document-layout>.col-md-4,.pos-modern .pos-document-identity,.pos-modern .pos-seller-card{display:flex;min-width:0;margin:0;padding:10px 12px;align-items:center;justify-content:center;border:1px solid #dce6f0;border-radius:14px;background:rgba(255,255,255,.84)}
.pos-modern .pos-points-card{margin:0}
.pos-modern .pos-metric-label,.pos-modern .pos-summary-kicker{display:block;margin-bottom:4px;color:#64748b;font-size:.62rem;font-weight:850;letter-spacing:.075em;text-transform:uppercase}
.pos-modern .pos-metric-label i{color:#f59e0b}
.pos-modern .pos-points-card .puntos{color:#2563eb;font-size:2.2rem;line-height:1}
.pos-modern .pos-document-identity{min-width:0;flex-direction:column;border-color:#bfdbfe;background:linear-gradient(145deg,#fafdff,#eef7ff)}
.pos-modern .pos-document-identity h1{color:#0b234a;font-size:1.55rem}
.pos-modern .pos-document-identity p{padding:4px 9px!important;color:#1d4ed8;background:#dbeafe!important;font-size:.6rem!important;font-weight:850;letter-spacing:.035em;text-transform:uppercase}
.pos-modern .pos-seller-card .input-group{width:100%;margin:0!important}
.pos-modern .pos-seller-card .input-group-text,.pos-modern .pos-seller-card .form-control{min-height:40px;font-size:.7rem;font-weight:850}
@media(max-width:1250px){.pos-modern .pos-document-layout{grid-template-columns:.8fr 1.15fr .9fr}}
</style>
<style>
/* Grid fix kept last to override Bootstrap's .col-md-4 constraints. */
.pos-modern .pos-document-layout{height:auto;min-height:128px;max-height:none;grid-template-columns:minmax(190px,1fr) minmax(250px,1.35fr) minmax(190px,1fr)}
.pos-modern .pos-document-layout>.col-md-4{width:auto!important;max-width:none!important;min-width:0!important;flex:none!important}
.pos-modern .pos-points-card{width:100%;min-width:0}
.pos-modern .pos-points-card .pos-metric-label,.pos-modern .pos-points-card .puntos{white-space:nowrap;word-break:normal}
.pos-modern .pos-document-layout>.col-md-4>.input-group{width:100%;flex-wrap:nowrap}
.pos-modern .pos-document-identity,.pos-modern .pos-seller-card{width:auto;max-width:none;min-height:96px}
@media(max-width:1350px){.pos-modern .pos-document-layout{grid-template-columns:minmax(155px,.85fr) minmax(210px,1.25fr) minmax(160px,.9fr)}}
</style>
<style>
/* Softer visual finish: fewer nested boxes, more whitespace and flowing surfaces. */
.pos-modern .pos-sale-header > .row,
.pos-modern .pos-document-layout {
  border: 0;
  border-radius: 22px;
  background: linear-gradient(145deg,rgba(255,255,255,.98),rgba(244,249,255,.92));
  box-shadow: 0 16px 36px rgba(15,35,65,.09);
}

.pos-modern .pos-sale-header > .row::before,
.pos-modern .pos-document-layout::before {
  right: 34px;
  left: 34px;
  height: 3px;
  opacity: .78;
}

.pos-modern .pos-doc-field .input-group,
.pos-modern .pos-header-field .input-group {
  box-shadow: none;
}

.pos-modern .pos-doc-field .input-group-text,
.pos-modern .pos-header-field .input-group-text {
  border-color: #dce7f1;
  color: #52647a;
  background: rgba(238,245,251,.78);
}

.pos-modern .pos-doc-field .form-control,
.pos-modern .pos-header-field .form-control {
  border-color: #dce7f1;
  background: rgba(255,255,255,.72);
}

.pos-modern .pos-client-field .input-group {
  border: 0;
  border-radius: 15px;
  background: #fff;
  box-shadow: 0 10px 24px rgba(15,45,80,.1),inset 0 0 0 1px rgba(188,216,239,.72);
}

.pos-modern .pos-client-field .input-group::before {
  border: 0;
  border-radius: 14px 0 0 14px;
  background: linear-gradient(145deg,#eff7ff,#e3f1ff);
}

.pos-modern .pos-client-field .input-group-append .btn {
  border-radius: 13px !important;
  box-shadow: 0 8px 18px rgba(11,35,74,.22);
}

.pos-modern .pos-document-layout {
  padding: 14px 18px !important;
  background:
    radial-gradient(circle at 50% -40%,rgba(37,99,235,.1),transparent 58%),
    linear-gradient(145deg,#fbfdff,#f1f7fd);
  grid-template-columns: minmax(180px,.9fr) minmax(245px,1.25fr) minmax(185px,.9fr);
  gap: 0 !important;
}

.pos-modern .pos-document-layout > .col-md-4,
.pos-modern .pos-document-identity,
.pos-modern .pos-seller-card {
  min-height: 92px;
  padding: 8px 18px;
  border: 0;
  border-radius: 0;
  background: transparent;
}

.pos-modern .pos-document-layout > .col-md-4,
.pos-modern .pos-document-identity {
  border-right: 1px solid rgba(148,163,184,.22);
}

.pos-modern .pos-document-identity {
  background: transparent;
}

.pos-modern .pos-points-card .puntos {
  text-shadow: 0 7px 18px rgba(37,99,235,.16);
}

.pos-modern .pos-document-identity h1 {
  font-size: 1.62rem;
  text-shadow: 0 6px 16px rgba(11,35,74,.1);
}

.pos-modern .pos-document-identity p {
  border-radius: 999px !important;
  background: linear-gradient(135deg,#e6f0ff,#d9eaff) !important;
  box-shadow: inset 0 0 0 1px rgba(37,99,235,.06);
}

.pos-modern .pos-seller-card .input-group {
  overflow: hidden;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 7px 18px rgba(15,35,65,.07);
}

.pos-modern .pos-seller-card .input-group-text,
.pos-modern .pos-seller-card .form-control {
  border: 0;
  background: transparent;
}

.pos-modern .pos-seller-card .input-group-text {
  color: #64748b;
  background: #f1f5f9;
}

@media(max-width:1350px) {
  .pos-modern .pos-document-layout {
    grid-template-columns: minmax(155px,.8fr) minmax(210px,1.2fr) minmax(165px,.85fr);
  }
}
</style>
<style>
/* Navy sale tab, icon trigger and polished autocomplete menus. */
.pos-modern .nav-pills .nav-link,
.pos-modern .nav-pills .nav-link.active {
  display: inline-flex;
  min-width: 126px;
  min-height: 43px;
  padding: 0 18px;
  align-items: center;
  justify-content: center;
  gap: 9px;
  border: 1px solid rgba(255,255,255,.18);
  border-radius: 999px;
  color: #fff;
  background: linear-gradient(135deg,#0b234a,#123d78);
  box-shadow: 0 8px 18px rgba(11,35,74,.28),inset 0 1px 0 rgba(255,255,255,.2);
}

.pos-modern .nav-pills .nav-link:hover,
.pos-modern .nav-pills .nav-link.active:hover {
  color: #fff;
  background: linear-gradient(135deg,#071a38,#075da9);
}

.pos-modern .pos-tab-icon {
  display: inline-flex;
  width: 25px;
  height: 25px;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: rgba(255,255,255,.14);
  font-size: .72rem;
}

.pos-modern .pos-client-field .client-form-trigger {
  display: inline-flex;
  min-width: 116px;
  min-height: 44px;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.pos-modern .pos-client-field .client-form-trigger i {
  position: static;
  display: inline-block;
  width: auto;
  height: auto;
  margin: 0;
  background: transparent;
  box-shadow: none;
  font-size: .88rem;
}

.pos-modern .pos-doc-field .autocomplete ul {
  min-width: 100%;
  max-height: 310px;
  margin-top: 7px;
  padding: 7px;
  overflow-y: auto;
  border: 1px solid #cbddec;
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 18px 38px rgba(11,35,74,.2);
}

.pos-modern .pos-doc-field .autocomplete ul li a {
  display: flex;
  min-height: 48px;
  margin: 2px 0;
  padding: 0 15px;
  align-items: center;
  border-radius: 10px;
  color: #172033;
  background: transparent;
  font-size: .94rem;
  font-weight: 850;
  transition: background .16s ease,color .16s ease,transform .16s ease,box-shadow .16s ease;
}

.pos-modern .pos-doc-field .autocomplete ul li a:hover,
.pos-modern .pos-doc-field .autocomplete ul li.focus-list a {
  color: #fff;
  background: linear-gradient(135deg,#0b3c78,#087fc3);
  box-shadow: 0 8px 17px rgba(7,93,169,.24);
  transform: translateX(2px);
}

.pos-modern .pos-client-field .txtText {
  width: 100%;
  min-width: 0;
  max-width: none;
}

.pos-modern .pos-client-field .txtBuscar {
  width: 100%;
  min-width: 0 !important;
}

.pos-modern .pos-client-field .txtText > div {
  top: calc(100% + 7px);
  width: 100%;
  max-height: 310px;
  padding: 7px;
  overflow-y: auto;
  border: 1px solid #cbddec;
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 18px 38px rgba(11,35,74,.2);
  z-index: 5000;
}

.pos-modern .pos-client-field .trDetails {
  min-height: 48px;
  margin: 2px 0;
  padding: 13px 15px;
  border-radius: 10px;
  color: #172033;
  background: transparent;
  font-size: .9rem;
  font-weight: 850;
  transition: background .16s ease,color .16s ease,transform .16s ease,box-shadow .16s ease;
}

.pos-modern .pos-client-field .trDetails:hover {
  color: #fff;
  background: linear-gradient(135deg,#0b3c78,#087fc3);
  box-shadow: 0 8px 17px rgba(7,93,169,.24);
  transform: translateX(2px);
}
</style>
<style>
/* Tab animation and exact client-field alignment. */
.pos-modern .pos-tab-icon {
  position: relative;
  flex: 0 0 28px;
  width: 28px;
  height: 28px;
  overflow: visible;
}

.pos-modern .pos-tab-icon i {
  font-size: .72rem;
  transform-origin: center bottom;
}

.pos-modern .pos-tab-icon b {
  position: absolute;
  top: -5px;
  right: -6px;
  display: flex;
  width: 16px;
  height: 16px;
  align-items: center;
  justify-content: center;
  border: 2px solid #123d78;
  border-radius: 50%;
  color: #0b234a;
  background: #fff;
  font-size: .55rem;
  font-weight: 950;
  line-height: 1;
  box-shadow: 0 3px 7px rgba(0,0,0,.2);
}

.pos-modern .nav-pills .nav-link:hover .pos-tab-icon i,
.pos-modern .nav-pills .nav-link.active .pos-tab-icon i {
  animation: posTabCart 1.5s ease-in-out infinite;
}

.pos-modern .pos-client-field .input-group {
  display: flex;
  width: 100%;
  overflow: visible;
  flex-wrap: nowrap;
  align-items: stretch;
}

.pos-modern .pos-client-field .input-group-prepend {
  display: flex;
  min-width: 0;
  flex: 1 1 auto;
  align-items: stretch;
}

.pos-modern .pos-client-field .input-group-prepend > div:not(.txtText) {
  flex: 0 0 auto;
}

.pos-modern .pos-client-field .input-group-prepend .txtText {
  width: auto;
  min-width: 0;
  max-width: none;
  flex: 1 1 auto;
}

.pos-modern .pos-client-field .input-group-prepend .txtBuscar {
  width: 100% !important;
  min-width: 0 !important;
  max-width: none !important;
  border-radius: 0 !important;
}

.pos-modern .pos-client-field .input-group-append {
  position: relative;
  z-index: 2;
  display: flex;
  margin-left: auto;
  flex: 0 0 auto;
  align-items: stretch;
}

.pos-modern .pos-client-field .input-group-append > div {
  display: flex;
}

.pos-modern .pos-client-field .input-group-append .client-form-trigger {
  height: 100%;
  min-width: 122px;
  margin: 0 !important;
  border-radius: 0 13px 13px 0 !important;
}

.pos-modern .pos-client-field .txtText > div:empty {
  display: none;
}

@keyframes posTabCart {
  0%,100% { transform: translateX(0) rotate(0); }
  35% { transform: translateX(3px) rotate(-7deg); }
  60% { transform: translateX(-1px) rotate(4deg); }
}
</style>
<style>
/* Clear selected-sale state. */
.pos-modern .nav-pills .nav-link {
  border-color: rgba(255,255,255,.12);
  color: rgba(255,255,255,.86);
  background: linear-gradient(135deg,#0b234a,#173965);
  box-shadow: 0 6px 13px rgba(11,35,74,.2),inset 0 1px 0 rgba(255,255,255,.12);
  opacity: .86;
  transform: scale(.97);
}

.pos-modern .nav-pills .nav-link.active {
  z-index: 2;
  border-color: rgba(255,255,255,.42);
  color: #fff;
  background: linear-gradient(135deg,#2563eb,#078ddd 62%,#06b6d4);
  box-shadow: 0 10px 22px rgba(37,99,235,.34),0 0 0 4px rgba(6,182,212,.1),inset 0 1px 0 rgba(255,255,255,.32);
  opacity: 1;
  transform: translateY(-1px) scale(1.02);
}

.pos-modern .nav-pills .nav-link.active .pos-tab-icon {
  background: rgba(255,255,255,.23);
  box-shadow: 0 0 0 3px rgba(255,255,255,.1);
}

.pos-modern .nav-pills .nav-link.active .pos-tab-icon b {
  border-color: #078ddd;
  color: #075da9;
}

.pos-modern .nav-pills .nav-link:not(.active):hover {
  color: #fff;
  background: linear-gradient(135deg,#12345f,#075da9);
  opacity: 1;
  transform: translateY(-1px) scale(1);
}

/* Opciones documentarias: proforma a crédito y condiciones comerciales. */
.pos-modern .pos-document-options {
  overflow: hidden;
  border: 1px solid rgba(117, 151, 187, .24) !important;
  border-radius: 18px !important;
  background: rgba(250, 253, 255, .92);
  box-shadow: 0 14px 30px rgba(11, 42, 75, .09) !important;
}

.pos-modern .pos-document-option {
  position: relative;
  border: 0;
  border-bottom: 1px solid rgba(117, 151, 187, .16);
  background: transparent;
  transition: background .22s ease, box-shadow .22s ease;
}

.pos-modern .pos-document-option:last-child {
  border-bottom: 0;
}

.pos-modern .pos-document-option.is-open {
  background: linear-gradient(145deg, rgba(239, 248, 255, .96), rgba(255, 255, 255, .98));
  box-shadow: inset 3px 0 0 #168ed5;
}

.pos-modern .pos-document-option .opcion-header {
  padding: 12px 14px !important;
  background: transparent !important;
}

.pos-modern .pos-document-option .custom-control {
  min-height: 40px;
  padding-left: 0;
}

.pos-modern .pos-document-option .custom-control-label {
  display: flex;
  min-height: 40px;
  align-items: center;
  padding-left: 48px;
  cursor: pointer;
}

.pos-modern .pos-document-option .custom-control-label::before,
.pos-modern .pos-document-option .custom-control-label::after {
  top: 10px;
  left: 6px;
  width: 20px;
  height: 20px;
  border-radius: 7px;
}

.pos-modern .pos-document-option .custom-control-input:checked ~ .custom-control-label::before {
  border-color: #087fc5;
  background: linear-gradient(135deg, #2563eb, #06a8ce);
  box-shadow: 0 4px 10px rgba(14, 126, 196, .27);
}

.pos-modern .pos-document-option .opcion-label {
  display: flex;
  width: 100%;
  align-items: center;
  gap: 11px;
  color: #132b4a;
}

.pos-modern .pos-option-icon {
  display: inline-flex;
  width: 38px;
  height: 38px;
  flex: 0 0 38px;
  align-items: center;
  justify-content: center;
  border-radius: 12px;
  color: #fff;
  box-shadow: 0 7px 15px rgba(22, 89, 145, .2);
  transition: transform .22s ease;
}

.pos-modern .opcion-header:hover .pos-option-icon {
  transform: translateY(-2px) rotate(-3deg);
}

.pos-modern .pos-option-icon-credit {
  background: linear-gradient(135deg, #f59e0b, #facc15);
}

.pos-modern .pos-option-icon-note {
  background: linear-gradient(135deg, #2563eb, #06b6d4);
}

.pos-modern .pos-option-copy {
  display: flex;
  min-width: 0;
  flex-direction: column;
  line-height: 1.2;
}

.pos-modern .pos-option-copy strong {
  color: #142b49;
  font-size: .9rem;
  font-weight: 900;
  letter-spacing: .01em;
}

.pos-modern .pos-option-copy small {
  margin-top: 3px;
  color: #71859d;
  font-size: .7rem;
  font-weight: 650;
}

.pos-modern .pos-document-option .opcion-body {
  padding: 4px 16px 15px !important;
  border: 0 !important;
  background: transparent !important;
}

.pos-modern .pos-option-field-label {
  display: block;
  margin: 0 0 7px;
  color: #536b85;
  font-size: .72rem;
  font-weight: 850;
  letter-spacing: .035em;
}

.pos-modern .pos-option-field-label i {
  margin-right: 5px;
  color: #168ed5;
}

.pos-modern .pos-option-fields {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 8px;
}

.pos-modern .pos-option-input-wrap {
  position: relative;
  min-width: 0;
}

.pos-modern .pos-option-input-wrap > i {
  position: absolute;
  z-index: 2;
  top: 50%;
  left: 13px;
  color: #168ed5;
  font-size: .8rem;
  transform: translateY(-50%);
  pointer-events: none;
}

.pos-modern .pos-option-input-wrap > i + input {
  padding-left: 37px !important;
}

.pos-modern .pos-option-input-wrap .form-control {
  width: 100%;
  min-height: 42px;
  margin: 0 !important;
  border: 1px solid #d4e2ef;
  border-radius: 12px;
  color: #17304e;
  background: rgba(255, 255, 255, .96);
  font-size: .78rem;
  font-weight: 650;
  box-shadow: 0 4px 11px rgba(24, 64, 104, .035);
  transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}

.pos-modern .pos-option-input-wrap .form-control:focus {
  border-color: #299bd8;
  box-shadow: 0 0 0 4px rgba(41, 155, 216, .12), 0 7px 16px rgba(24, 79, 128, .08);
  transform: translateY(-1px);
}

@media (max-width: 767px) {
  .pos-modern .pos-option-fields {
    grid-template-columns: 1fr;
  }

  .pos-modern .pos-document-option .custom-control-label {
    padding-left: 43px;
  }

  .pos-modern .pos-option-copy small {
    white-space: normal;
  }
}

/* Teclado táctil para cantidad, precio y descuento. */
.pos-calculator-modal .modal-dialog {
  width: min(94vw, 620px) !important;
  max-width: 620px !important;
  margin: 5vh auto !important;
}

.pos-calculator-modal .modal-content {
  overflow: hidden;
  border: 1px solid rgba(91, 180, 231, .45);
  border-radius: 22px;
  background: linear-gradient(155deg, #ffffff 0%, #f4f9fd 100%);
  box-shadow: 0 28px 70px rgba(4, 25, 54, .34), 0 0 0 5px rgba(56, 189, 248, .08);
}

.pos-calculator-modal .modal-header {
  min-height: 72px;
  align-items: center;
  padding: 16px 22px;
  border: 0;
  color: #fff;
  background:
    radial-gradient(circle at 88% 10%, rgba(34, 211, 238, .32), transparent 28%),
    linear-gradient(135deg, #092754, #075c9b 65%, #079bc1);
}

.pos-calculator-modal .modal-title {
  display: flex;
  align-items: center;
  gap: 11px;
  font-size: 1.2rem;
  font-weight: 900;
  letter-spacing: .025em;
}

.pos-calculator-modal .modal-title::before {
  display: inline-flex;
  width: 38px;
  height: 38px;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255, 255, 255, .25);
  border-radius: 12px;
  background: rgba(255, 255, 255, .14);
  content: "\f3d1";
  font-family: "Font Awesome 5 Free";
  font-size: 1rem;
  font-weight: 900;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, .25);
}

.pos-calculator-modal .modal-body {
  padding: 20px 22px 22px;
}

.pos-calculator-modal .pos-calculator-product {
  display: grid;
  grid-template-columns: 38px 1fr;
  grid-template-rows: auto auto;
  column-gap: 11px;
  width: 100%;
  align-items: center;
  margin: 0 0 13px;
  padding: 10px 13px;
  border: 1px solid #d5e7f4;
  border-radius: 13px;
  color: #132b49;
  background: rgba(255, 255, 255, .9);
  box-shadow: 0 6px 15px rgba(21, 63, 100, .06);
}

.pos-calculator-modal .pos-calculator-product i {
  grid-row: 1 / span 2;
  display: inline-flex;
  width: 36px;
  height: 36px;
  align-items: center;
  justify-content: center;
  border-radius: 11px;
  color: #fff;
  background: linear-gradient(135deg, #2563eb, #06a9cf);
  box-shadow: 0 6px 13px rgba(37, 99, 235, .22);
}

.pos-calculator-modal .pos-calculator-product span {
  color: #75889f;
  font-size: .65rem;
  font-weight: 850;
  letter-spacing: .1em;
  text-transform: uppercase;
}

.pos-calculator-modal .pos-calculator-product b {
  overflow: hidden;
  font-size: .9rem;
  font-weight: 900;
  text-decoration: none !important;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.pos-calculator-modal .modal-body > input.form-control {
  height: 60px;
  margin-bottom: 14px;
  padding: 8px 18px !important;
  border: 2px solid #9bd8f3;
  border-radius: 15px;
  color: #075da9 !important;
  background: #fff;
  font-size: 1.65rem !important;
  font-weight: 900 !important;
  text-align: right !important;
  letter-spacing: .035em;
  box-shadow: inset 0 2px 5px rgba(15, 53, 89, .04), 0 0 0 4px rgba(56, 189, 248, .08);
}

.pos-calculator-modal .modal-body > input.form-control:focus {
  border-color: #168ed5;
  box-shadow: 0 0 0 5px rgba(22, 142, 213, .14), 0 8px 20px rgba(9, 78, 134, .1);
}

.pos-calculator-modal .calc__numbers {
  width: 100%;
  border-collapse: separate;
  border-spacing: 8px;
  table-layout: fixed;
}

.pos-calculator-modal .calc__numbers .botones {
  height: 58px;
  padding: 0;
  border: 1px solid #d5e4f0 !important;
  border-radius: 14px;
  color: #18324f;
  background: linear-gradient(145deg, #fff, #edf5fb);
  font-size: 1.05rem;
  font-weight: 900;
  text-align: center;
  box-shadow: 0 6px 12px rgba(12, 50, 84, .08), inset 0 1px 0 #fff;
  cursor: pointer;
  user-select: none;
  transition: transform .12s ease, box-shadow .12s ease, color .12s ease, background .12s ease;
}

.pos-calculator-modal .calc__numbers .botones:hover {
  border-color: #63bee9 !important;
  color: #fff;
  background: linear-gradient(135deg, #1769ba, #06a7ce);
  box-shadow: 0 9px 18px rgba(10, 112, 178, .22);
  transform: translateY(-2px);
}

.pos-calculator-modal .calc__numbers .botones:active {
  box-shadow: 0 3px 7px rgba(10, 77, 125, .18);
  transform: translateY(1px) scale(.98);
}

.pos-calculator-modal .calc__numbers tr:last-child td:nth-child(2) {
  color: #075da9;
  background: linear-gradient(145deg, #e7f7ff, #d7f1fc);
}

.pos-calculator-modal .calc__numbers tr:last-child td:last-child {
  color: #fff;
  background: linear-gradient(135deg, #f43f5e, #e11d48);
  box-shadow: 0 7px 14px rgba(225, 29, 72, .2);
}

.pos-calculator-modal .calc__numbers .botones.is-disabled {
  color: #a5b4c3;
  background: #edf2f6;
  box-shadow: none;
  cursor: not-allowed;
}

.pos-touch-input-modal .modal-title::before {
  content: "\f11c";
}

.pos-touch-input-modal .pos-calculator-product b {
  color: #075da9;
}

.pos-touch-change-preview {
  display: grid;
  grid-template-columns: 1fr 1fr auto;
  gap: 8px;
  margin: 0 0 8px;
}

.pos-touch-change-preview > div {
  display: flex;
  min-height: 54px;
  flex-direction: column;
  justify-content: center;
  padding: 8px 12px;
  border: 1px solid #d6e5f1;
  border-radius: 12px;
  background: #f3f8fc;
}

.pos-touch-change-preview > div:nth-child(2) {
  border-color: #fed7aa;
  background: #fff7ed;
}

.pos-touch-change-preview > div:nth-child(2).has-change {
  border-color: #a7f3d0;
  background: #ecfdf5;
}

.pos-touch-change-preview span {
  color: #6f8298;
  font-size: .65rem;
  font-weight: 850;
  letter-spacing: .07em;
  text-transform: uppercase;
}

.pos-touch-change-preview strong {
  margin-top: 2px;
  color: #102c4d;
  font-size: .95rem;
  font-weight: 900;
}

.pos-touch-change-preview > div:nth-child(2) strong {
  color: #c2410c;
}

.pos-touch-change-preview > div:nth-child(2).has-change strong {
  color: #047857;
}

.pos-touch-change-preview button {
  min-width: 92px;
  border: 0;
  border-radius: 12px;
  color: #fff;
  background: linear-gradient(135deg, #2563eb, #06a9cf);
  font-size: .7rem;
  font-weight: 900;
  box-shadow: 0 7px 15px rgba(25, 107, 178, .2);
  cursor: pointer;
  transition: transform .17s ease, box-shadow .17s ease;
}

.pos-touch-change-preview button:hover {
  box-shadow: 0 10px 20px rgba(25, 107, 178, .27);
  transform: translateY(-2px);
}

.pos-calculator-modal .modal-body > br {
  display: none;
}

.pos-calculator-modal .modal-body > div:last-child {
  display: grid;
  height: auto !important;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 11px;
}

.pos-calculator-modal .modal-body > div:last-child .btn {
  width: 100% !important;
  min-height: 50px;
  margin: 0;
  border: 1px solid rgba(255, 255, 255, .27);
  border-radius: 13px;
  font-size: .88rem;
  font-weight: 900;
  letter-spacing: .035em;
  box-shadow: 0 8px 16px rgba(15, 45, 74, .15);
  transition: transform .17s ease, box-shadow .17s ease;
}

.pos-calculator-modal .modal-body > div:last-child .btn-success {
  background: linear-gradient(135deg, #059669, #22c55e);
}

.pos-calculator-modal .modal-body > div:last-child .btn-danger {
  background: linear-gradient(135deg, #334155, #64748b);
}

.pos-calculator-modal .modal-body > div:last-child .btn:hover {
  box-shadow: 0 11px 21px rgba(15, 45, 74, .22);
  transform: translateY(-2px);
}

.pos-touch-controls {
  display: grid;
  width: 100%;
  grid-template-columns: minmax(0, 1fr) 48px;
  align-items: center;
  gap: 9px;
  margin-top: 8px;
}

.pos-touch-controls .pos-action-touch {
  min-width: 0;
  margin: 0 !important;
}

.pos-touch-controls:not(:has(.pos-touch-keyboard-launch)) {
  grid-template-columns: 1fr;
}

.pos-touch-keyboard-launch {
  position: relative;
  width: 48px !important;
  min-width: 48px;
  height: 48px;
  padding: 0 !important;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, .38) !important;
  border-radius: 15px !important;
  color: #fff !important;
  background: linear-gradient(145deg, #174a8c, #078bc1) !important;
  font-size: 1.05rem;
  box-shadow: 0 7px 15px rgba(7, 64, 118, .25), inset 0 1px 0 rgba(255, 255, 255, .25);
  transition: transform .18s ease, box-shadow .18s ease;
}

.pos-touch-keyboard-launch::after {
  position: absolute;
  top: -50%;
  left: -90%;
  width: 42%;
  height: 200%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .55), transparent);
  content: "";
  transform: rotate(22deg);
  transition: left .5s ease;
}

.pos-touch-keyboard-launch:hover {
  box-shadow: 0 10px 22px rgba(7, 103, 166, .35), 0 0 0 4px rgba(40, 183, 224, .1);
  transform: translateY(-2px);
}

.pos-touch-keyboard-launch:hover::after {
  left: 140%;
}

.pos-alpha-keyboard-modal .modal-dialog {
  width: min(900px, calc(100vw - 28px));
  max-width: 900px;
}

.pos-alpha-keyboard-modal .modal-content {
  overflow: hidden;
  border: 1px solid rgba(101, 189, 231, .65);
  border-radius: 22px;
  background: linear-gradient(145deg, rgba(255, 255, 255, .98), rgba(235, 246, 253, .98));
  box-shadow: 0 28px 70px rgba(3, 31, 62, .38);
}

.pos-alpha-keyboard-modal .modal-header {
  min-height: 66px;
  padding: 17px 24px;
  border: 0;
  color: #fff;
  background: linear-gradient(110deg, #082c5c, #075e9f 62%, #06a6c9);
}

.pos-alpha-keyboard-modal .modal-title {
  font-size: 1.05rem;
  font-weight: 900;
  letter-spacing: .03em;
}

.pos-alpha-keyboard-modal .modal-body {
  padding: 20px 22px 22px;
}

.pos-alpha-heading {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 13px;
}

.pos-alpha-heading-icon {
  display: grid;
  width: 42px;
  height: 42px;
  flex: 0 0 42px;
  place-items: center;
  border-radius: 13px;
  color: #fff;
  background: linear-gradient(135deg, #2563eb, #06a9cf);
  box-shadow: 0 8px 16px rgba(13, 108, 177, .2);
}

.pos-alpha-heading strong,
.pos-alpha-heading small {
  display: block;
}

.pos-alpha-heading strong {
  color: #102c4d;
  font-size: 1rem;
  font-weight: 900;
}

.pos-alpha-heading small {
  margin-top: 2px;
  color: #6b7f94;
  font-size: .73rem;
  font-weight: 700;
}

.pos-alpha-display {
  display: grid;
  grid-template-columns: 1fr 58px;
  margin-bottom: 14px;
  overflow: hidden;
  border: 2px solid #7bc9ed;
  border-radius: 15px;
  background: #fff;
  box-shadow: 0 0 0 4px rgba(56, 189, 248, .08);
}

.pos-alpha-display input {
  min-width: 0;
  height: 58px;
  padding: 0 18px;
  border: 0;
  outline: 0;
  color: #102c4d;
  background: transparent;
  font-size: 1.25rem;
  font-weight: 850;
}

.pos-alpha-display button {
  border: 0;
  color: #fff;
  background: linear-gradient(135deg, #ef4444, #f43f5e);
  font-size: 1.08rem;
}

.pos-alpha-keyboard {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.pos-alpha-row {
  display: grid;
  gap: 7px;
}

.pos-alpha-row button,
.pos-alpha-tools button {
  min-width: 0;
  height: 48px;
  border: 1px solid #d2e3ef;
  border-radius: 11px;
  color: #173453;
  background: linear-gradient(145deg, #fff, #eaf3f9);
  font-size: .92rem;
  font-weight: 900;
  box-shadow: 0 4px 9px rgba(13, 57, 91, .08), inset 0 1px 0 #fff;
  transition: transform .12s ease, color .12s ease, background .12s ease;
}

.pos-alpha-row button:hover,
.pos-alpha-row button:active {
  color: #fff;
  background: linear-gradient(135deg, #1769ba, #06a7ce);
  transform: translateY(-1px);
}

.pos-alpha-tools {
  display: grid;
  grid-template-columns: 1fr 2.4fr 1fr;
  gap: 8px;
}

.pos-alpha-tools button.active {
  color: #fff;
  background: linear-gradient(135deg, #3557de, #178fd1);
}

.pos-alpha-tools .pos-alpha-clear {
  color: #b4233e;
  background: #fff1f3;
}

.pos-alpha-actions {
  display: flex;
  justify-content: flex-end;
  gap: 9px;
  margin-top: 17px;
}

.pos-alpha-actions .btn {
  min-width: 150px;
  min-height: 46px;
  border-radius: 12px;
  font-weight: 900;
}

@media (max-width: 575px) {
  .pos-calculator-modal .modal-dialog {
    width: calc(100vw - 20px) !important;
    margin: 10px auto !important;
  }

  .pos-calculator-modal .modal-header {
    min-height: 62px;
    padding: 12px 16px;
  }

  .pos-calculator-modal .modal-body {
    padding: 14px;
  }

  .pos-calculator-modal .calc__numbers .botones {
    height: 54px;
  }

  .pos-touch-change-preview {
    grid-template-columns: 1fr 1fr;
  }

  .pos-touch-change-preview button {
    min-height: 42px;
    grid-column: 1 / -1;
  }

  .pos-alpha-keyboard-modal .modal-body {
    padding: 13px 10px 16px;
  }

  .pos-alpha-row {
    gap: 3px;
  }

  .pos-alpha-row button,
  .pos-alpha-tools button {
    height: 42px;
    padding: 0 3px;
    border-radius: 8px;
    font-size: .72rem;
  }

  .pos-alpha-actions .btn {
    min-width: 0;
    flex: 1;
  }
}

.pos-image-gallery-title { display:flex;align-items:center;gap:10px;padding:4px 4px 12px;color:#12375f; }
.pos-image-gallery-title i { display:grid;width:38px;height:38px;place-items:center;border-radius:12px;color:#fff;background:linear-gradient(135deg,#6d3de1,#12a8d2); }
.pos-image-gallery-title h3 { margin:0;font-size:1.1rem;font-weight:900;text-transform:uppercase; }
.pos-image-gallery-stage { display:grid;grid-template-columns:46px minmax(0,1fr) 46px;align-items:center;gap:8px;min-height:360px;padding:10px;border-radius:18px;background:radial-gradient(circle,#fff,#edf6fc); }
.pos-image-gallery-stage > button { display:grid;width:42px;height:42px;padding:0;place-items:center;border:0;border-radius:50%;color:#fff;background:linear-gradient(135deg,#123c70,#0d94c5);box-shadow:0 7px 16px rgba(8,62,111,.24); }
.pos-image-gallery-stage .imagenGrande { max-width:100%;max-height:62vh;margin:auto;object-fit:contain; }
.pos-image-gallery-thumbs { display:flex;padding:12px 0 2px;justify-content:center;gap:9px; }
.pos-image-gallery-thumbs button { width:62px;height:62px;padding:3px;overflow:hidden;border:2px solid transparent;border-radius:12px;background:#eaf3f9; }
.pos-image-gallery-thumbs button.active { border-color:#7c3aed;box-shadow:0 0 0 3px rgba(124,58,237,.14); }
.pos-image-gallery-thumbs img { width:100%;height:100%;border-radius:8px;object-fit:cover; }
</style>
