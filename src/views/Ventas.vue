<template>
  <b-container fluid class="ancho px-0 py-0" id="conten">
    <b-modal id="modalImagen" size="lg" ref="modalImagen" hide-footer hide-header>
      <div class="d-block text-center">
        <h3 style="text-decoration: underline;">{{ productoNombre }}</h3>
      </div>
      <b-row style="display:flex; justify-content:center; align-items:center">
        <b-img :src="productoUrl" class="imagenGrande"></b-img>
      </b-row>
    </b-modal>
    <b-row id="header">
      <b-col cols="12" class="py-0 mt-0">
        <b-card no-body class="mt-0" style="background-color: #E3F2FD;">
          <b-tabs v-model="ventaActiva" content-class="mt-0" pills card class="mt-0">
            <b-tab v-for="(venta, index) in ventas" :key="index" :title="`Venta ${index + 1}`" title-link-class="titleTabs"
              class="mt-0 pt-0">
              <b-container fluid>
                <b-row>
                  <b-col lg="6" xl="6" cols="12">
                    <b-row>
                      <b-col lg="4" xl="4" cols="12" class="mostrarMovil">
                        Tipo de Venta: {{ ventas[ventaActiva].Serie }} - {{ ventas[ventaActiva].Numero }}
                      </b-col>
                      <b-col lg="4" xl="4" cols="12" class="ocultarMovil">
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
                        <b-input-group class="mt-2">
                          <autocomplete required="required" :id="`inputTipoVenta${index}`" :url="API + 'ventas/tipos'"
                            anchor="TipoDoc" initValue="BOLETA ELEC." class="font-weight-bold" label="writer"
                            placeholder="" :on-select="setTipoVenta"
                            :onFocus="focusAutocomplete.bind(null, `inputTipoVenta${index}`)"
                            :classes="{ input: 'form-control' }">
                          </autocomplete>
                        </b-input-group>
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
                      <b-col md="4" class="ocultarMovil">
                        <b-input-group prepend="Alm." class="mt-2">
                          <autocomplete required="required" :id="`inputAlmacen${index}`" :url="API + 'almacenes'"
                            anchor="Almacen" class="font-weight-bold" initValue="PRINCIPAL" label="writer"
                            placeholder="" :on-select="setAlmacen" :value="ventas[ventaActiva].almacen"
                            :onFocus="focusAutocomplete.bind(null, `inputAlmacen${index}`)"
                            :classes="{ input: 'form-control' }">
                          </autocomplete>
                        </b-input-group>
                      </b-col>
                      <b-col md="4" class="ocultarMovil">
                        <b-input-group prepend="P.V. " class="mt-2">
                          <autocomplete required="required" :id="`inputPuntoVenta${index}`" :url="API + 'ventas/puntos'"
                            anchor="PuntoVenta" class="font-weight-bold" initValue="PRINCIPAL" label="writer"
                            placeholder="" :on-select="setPuntoVenta"
                            :onFocus="focusAutocomplete.bind(null, `inputPuntoVenta${index}`)"
                            :classes="{ input: 'form-control' }">
                          </autocomplete>
                        </b-input-group>
                      </b-col>
                      <b-col cols="12" class="mt-1">

                        <b-form-group>
                          <b-input-group class="d-flex w-100 w-lg-auto">
                            <b-input-group-prepend>
                              <!-- @dniRuc="setNewDniRuc" -->
                              <clientes-form txtButton="Editar"
                                v-if="venta.cliente.IdCliente && venta.cliente.DniRuc != '00000000'"
                                idModal="editCliente" @onAddCliente="setCliente"
                                :editCliente="venta.cliente"></clientes-form>
                              <AutoComplete :value="ventas[index].cliente.Cliente" @guardarCliente="setCliente"
                                @dniRuc="newDniRuc=$event" />
                            </b-input-group-prepend>
                            <b-input-group-append>
                              <clientes-form :newDniRuc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                            </b-input-group-append>
                          </b-input-group>

                          <b-form-select v-if="ventas[ventaActiva].cliente.IdCliente" v-model="venta.CampoDireccion"
                            class="mb-3">
                            <option :value="ventas[ventaActiva].cliente.Direccion">{{ venta.cliente.Direccion }}
                            </option>
                            <option :value="ventas[ventaActiva].cliente.Direccion2">{{ venta.cliente.Direccion2 }}
                            </option>
                            <option :value="ventas[ventaActiva].cliente.Direccion3">{{ venta.cliente.Direccion3 }}
                            </option>
                          </b-form-select>
                        </b-form-group>


                      </b-col>
                      <b-col lg="6" xl="6" cols="12">
                        <b-form-checkbox :id="`checkbox-${index}`" v-model="venta.EsOrganizacion" :value="true"
                          :unchecked-value="false">
                          Es Organización
                        </b-form-checkbox>
                      </b-col>
                      <b-col lg="6" xl="6" cols="12" class="pt-0">
                        <b-form-input v-model="venta.nombreOrganizacion" v-if="venta.EsOrganizacion"
                          :id="`txtNombreOrganizacion-${index}`" placeholder="NOMBRE DE LA ORGANIZACIÓN"></b-form-input>
                      </b-col>
                    </b-row>
                  </b-col>
                  <b-col lg="6" xl="6" cols="12" class="ocultarMovil">
                    <div style="display: flex; align-items: center; justify-content: space-around; gap: 8px; padding: 4px 0;">
                      <b-col md="4">
                        <b-form-group class="text-center" v-if="venta.cliente.Cliente && ventas[ventaActiva].tipoVenta.CodSunat != '07' && ventas[ventaActiva].tipoVenta.CodSunat != '08'">
                            <div class="puntos">
                                {{ (venta.cliente.Puntos || 0) | redondeoMenor }}
                            </div>
                            Puntos del cliente
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
                      <div style="text-align: center; flex: 1;">
                        <h1 style="margin-bottom: 0;">
                          {{ ventas[ventaActiva].Serie }} - {{ ventas[ventaActiva].Numero }}
                        </h1>
                        <p style="padding: 2px 3px; border-radius: 25px; background: yellow; 
                                  font-size: 1rem; display: inline-block; margin-bottom: 0;">
                          Valor Referencial
                        </p>
                      </div>

                      <div style="flex: 1; display: flex; align-items: center; justify-content: center;">
                        <b-input-group prepend="Vendedor:" class="mt-2">
                          <input type="text" class="form-control"
                                v-model="ventas[ventaActiva].vendedor.Usuario"
                                disabled style="max-width: 150px;">
                        </b-input-group>
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
                  <b-col cols="12">
                    <b-form-group class="formResponsive">
                      <b-input-group>
                        <b-input-group-append>
                          <autocomplete :ref="`autocompleteProducto${index}`" :id="`autocompleteProducto${index}`"
                            :url="APIProductos"
                            :customParams="{ 'sortBy': isVentas ? 'stock' : 'IdProducto', 'sortDesc': 1, limit: '25' }"
                            :onShouldRenderChild="renderChild" anchor="Producto" label="writer"
                            placeholder="Agregar Producto" :on-select="setProducto"
                            :onFocus="focusAutocomplete.bind(null, `autocompleteProducto${index}`)"
                            :classes="{ input: 'form-control', root: 'formResponsive' }"
                            @keydown.native.tab.prevent="irCodeBar" class="txtAutoComplete">
                          </autocomplete>
                          <b-button variant="primary" @click="startRecognition">
                            <i class="fas fa-microphone"></i>
                          </b-button>
                        </b-input-group-append>
                        <input v-model="loadCode" :id="`txtCodeBar${index}`" @keydown.enter.prevent="loadBarcode"
                          type="text" class="form-control" placeholder="Codigo de Barra" />
                        <b-input-group-append>
                          <!--<proveedores-tabla></proveedores-tabla>-->
                          <b-button variant="success" @click="abrirCodigoBarra"><i class="fas fa-barcode"></i></b-button>
                          <b-button variant="primary" class="ocultarMovil" @click="loadBarcode"><i
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
                <div class="divCarrito">
                  <b-row class="mt-2 clsCarrito">
                    <b-table striped hover :fields="headerTable" :items="venta.productos" class="tblCarrito">
                      <template slot="pres" slot-scope="data">
                        <b-button v-if="data.item.tienePresentacion" size="sm" variant="info"
                          @click="showPresentacion(data.item, data.index)" style="margin-top:2px;">
                          <i class="fas fa-list"></i>
                        </b-button>
                        <b-button
                          @click="abrirImagen(data.item.Producto, data.item.Imagen == null || data.item.Imagen == '' ? 'static/no-photo.png' : '/resources/images/productos/' + data.item.Imagen)"
                          style="margin-top:2px;" :id="'popover-imagen' + data.index.IdProducto" variant="success"
                          size="sm"><i class="fas fa-image"></i></b-button>
                        <b-popover placement="right" :target="'popover-imagen' + data.index.IdProducto"
                          :title="data.index.Producto" triggers="hover">
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
                      <template slot="cantidad" slot-scope="data">
                        <input v-model="data.item.cantidad" type="number"
                          style="text-align:center; font-weight:bold; width: 90px;" autocomplete="false"
                          :id="`input${data.index}`" :disabled="data.item.esPresentacion" @input="metodoPagoCambio(1)"
                          @focus="abrirModalTouch(data.index, 1)" class="form-control"
                          @keydown.enter="irBusquedaProducto" />
                        <p style="color: rgb(0, 123, 255); text-align: center; font-weight: bold;padding:0;margin:0;"
                          v-if="data.item.esPresentacion">

                          {{ data.item.CantidadPresentacion * data.item.cantidad }} unid.
                        </p>
                      </template>
                      <template slot="precio" slot-scope="data">
                        <input :disabled="data.item.bloquearInputPrecio" v-model="data.item.precio" min="0"
                          step="0.00001" type="text" style="text-align:center; font-weight:bold; width: 105px;"
                          autocomplete="false" @input="metodoPagoCambio(1)" @focus="abrirModalTouch(data.index, 2)"
                          class="form-control">

                      </template>
                      <template slot="descuento" slot-scope="data">
                        <input v-model="data.item.descuento" type="text"
                          style="text-align:center; font-weight:bold; width: 100px;" autocomplete="false"
                          @input="metodoPagoCambio(1)" @focus="abrirModalTouch(data.index, 3)" class="form-control" />

                      </template>
                      <template slot="subtotal" slot-scope="data">
                        {{ getSubTotalDetalle(data.item) }}

                      </template>
                      <template slot="total" slot-scope="data">
                        {{ getTotalDetalle(data.item) }}

                      </template>
                      <template slot="acciones" slot-scope="data">
                        <b-button variant="danger" size="sm" @click="quitarProducto(data.index)">
                          <i class="fas fa-times fa-lg"></i>
                        </b-button>

                        <b-button class="mx-auto mt-1" variant="info" size="sm" v-if="mostrarEditarProducto()"
                          @click="editarProducto(data.item, data.index)">
                          <i class="fas fa-pencil-alt fa-sm"></i>
                        </b-button>

                      </template>
                      <template slot="cantxPrecio" slot-scope="data">
                        <b-form-input v-if="data.item.esPresentacion" v-model="data.item.CantidadPresentacion"
                          type="text" style="text-align:center; font-weight:bold; width: 100px;" autocomplete="false"
                          @input="metodoPagoCambio(1)"></b-form-input>
                        <!-- {{data.item.esPresentacion}} -->
                        <!-- esPresentacion -->

                      </template>

                    </b-table>
                  </b-row>
                </div>
                <b-row class="justify-content-center">
                  <b-button block class="mx-1 btnAction" type="submit" style="max-width: 300px; width: 100%;font-size: 1.5rem; margin-top: 10px;" size="lg" variant="success" @click="guardarVenta()">
                    <i class="fas fa-shopping-cart"></i> PAGAR
                  </b-button>
                </b-row>
                <b-row class="mt-4" style="border-top: 3px solid #C0C0C0">
                  <b-col lg="3" xl="3" cols="12">
                    <b-row>
                      <b-col cols="12" class="mt-1">
                        <!-- <b-button-group size="sm" class="ocularMovil">
                          <delivery-modal ref="deliveryModal" @onPagarDelivery="onPagarDelivery"></delivery-modal>
                      </b-button-group>  -->
                        <b-button-group size="sm" class="ocularMovil">
                          <pago-deuda></pago-deuda>
                        </b-button-group>
                        <b-button-group size="sm">
                          <movimiento></movimiento>
                        </b-button-group>
                        <b-button-group size="sm">
                          <proformas :idAlmacen="venta.almacen.IdAlmacen" @onSelectProforma="cargarProforma"
                            @cargarProforma="cargarProforma"></proformas>
                        </b-button-group>
                        <b-button-group size="sm" class="ocularMovil" v-if="false">
                          <pre-orden-lista v-show="!this.$route.query.idPreOrden" :idAlmacen="venta.almacen.IdAlmacen"
                            @onSelectPreOrden="setCliente" @onPreOrden="selectPreOrden"></pre-orden-lista>
                        </b-button-group>
                        <b-button-group size="sm" class="ocularMovil">

                          <config-venta @onUpdateConfig="eliminarCliente"
                            @onUpdateConfigComision="eliminarComisionista"></config-venta>
                        </b-button-group>
                      </b-col>
                      <b-col cols="12" class="mt-1">
                        <table class="table b-table border table-footer">
                          <tbody>
                            <tr>
                              <td class="text-center">Opciones</td>
                            </tr>
                            <tr v-if="ventas[ventaActiva].puntosAplicados === 0">
                                            <td>
                                                <b-input-group prepend="S/" append="Descuento">
                                                    <input
                                                        v-model="ventas[ventaActiva].descuentoGeneral"
                                                        :disabled="bloquearInput ? true : false"
                                                        min="0"
                                                        value="0"
                                                        @input="(val) => { aplicarDescuentoGeneral(val.target.value) }"
                                                        type="number"
                                                        class="form-control"
                                                        step="0.01"
                                                    />
                                                </b-input-group>
                                                <b-form-text>
                                                    Descuento general a toda la venta.
                                                </b-form-text>
                                            </td>
                                        </tr>
                            <!-- <tr v-if="puntosAplicados == 0"> -->

                            <tr>
                              <td>
                                <b-form-checkbox v-model="ventas[ventaActiva].EsCredito" :value="1"
                                  :unchecked-value="0">
                                  Venta a credito
                                </b-form-checkbox>
                                <b-form-input v-if="ventas[ventaActiva].EsCredito" v-model="venta.FechaCredito"
                                  type="date">
                                </b-form-input>
                                <b-form-checkbox v-model="ventas[ventaActiva].PorEntregar" :value="1"
                                  :unchecked-value="0">
                                  Por entregar
                                </b-form-checkbox>
                              </td>
                            </tr>
                            <tr>
                              <td class="text-center">Proforma</td>
                            </tr>
                            <tr>
                              <td>
                                <b-input-group prepend="Proforma válido por:" append="días">
                                  <b-form-input :disabled="bloquearInput ? true : false"
                                    v-model="ventas[ventaActiva].diasValidosProf" min="0" value="0" type="number"></b-form-input>
                                </b-input-group>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <b-input-group>
                                  <b-form-checkbox :disabled="bloquearInput ? true : false"
                                    v-model="venta.EsCreditoProforma" :value="1" :unchecked-value="0">
                                    Proforma a Credito
                                  </b-form-checkbox>
                                  <div v-if="venta.EsCreditoProforma">
                                    <div class="mb-3">
                                      <input v-model="venta.FechaCreditoProforma" type="date" class="form-control"
                                        required placeholder="Fecha Crédito" />
                                    </div>
                                  </div>
                                </b-input-group>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <b-input-group>
                                  <b-form-checkbox :disabled="bloquearInput ? true : false" v-model="venta.EsDescripcion"
                                    :value="1" :unchecked-value="0">
                                    Nota
                                  </b-form-checkbox>
                                  <div v-if="venta.EsDescripcion">
                                    <div class="mb-3">
                                      <input v-model="venta.DescripcionProforma" type="text" class="form-control"
                                        required placeholder="Plazo de Entrega" />
                                    </div>
                                    <div class="mb-3">
                                      <input v-model="venta.DescripcionProforma2" type="text" class="form-control"
                                        required placeholder="Validez de la oferta" />
                                    </div>
                                    <div class="mb-3">
                                      <input v-model="venta.DescripcionProforma3" type="text" class="form-control"
                                        required placeholder="Lugar de Entrega" />
                                    </div>
                                    <div class="mb-3">
                                      <input v-model="venta.DescripcionProforma4" type="text" class="form-control"
                                        required placeholder="Garantia" />
                                    </div>
                                  </div>
                                </b-input-group>
                              </td>
                            </tr>

                            <!-- <tr>
                                            <td>
                                                <b-input-group prepend="Prof. válida por:" append="días">
                                                <b-form-input
                                                    :disabled="bloquearInput?true:false"
                                                    v-model="diasValidosProf"
                                                    min="0"
                                                    value="0"
                                                    type="number"
                                                ></b-form-input>
                                            </b-input-group>
                                            </td>
                                        </tr> -->
                          </tbody>
                        </table>
                      </b-col>
                      <!-- <b-col md="12" class="mt-1">
                     
                        <b-button type="button" block variant="primary"><i class="fas fa-redo"></i> Actualizar</b-button>
                      </b-col> -->


                    </b-row>
                  </b-col>
                  <b-col lg="3" xl="3" cols="12">


                    <b-col cols="12" class="text-left">
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
                    </b-col>
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
                  
                  <b-col lg="3" xl="3" cols="12">
                    <b-row class="centrado d-flex justify-content-center my-2">
                      <b-button block variant="warning" class="btnAction" @click="anadirVentana()"> Añadir
                        Venta</b-button>
                      <!-- <b-button block class="mx-1 btnAction" variant="success" @click="guardarVenta()"> <i
                          class="fas fa-shopping-cart"></i> PAGAR</b-button> -->
                      <b-button block class="mx-1 btnAction" variant="primary" @click="guardarProforma()"> Crear
                        Proforma</b-button>
                      <busqueda-modelo
                        :idAlmacen="venta.almacen.IdAlmacen"
                        @onAgregarProductos="cargarProductosDeModelo">
                      </busqueda-modelo>
                      <b-button v-if="touchHab" variant="danger" class="btnAction" block @click="activarModalTouch()"><i
                          class="fas fa-desktop"></i> Deshabilitar Touch</b-button>
                      <b-button v-else variant="success" class="btnAction" block @click="activarModalTouch()"><i
                          class="fas fa-keyboard"></i> Habilitar Touch</b-button>
                      <b-button block v-if="ventas.length > 1" variant="danger" @click="eliminarVentana()" class="btnAction"> Cancelar
                        Venta</b-button>
                      <b-button class="btnAction" block style="background-color: #6F42C1;" @click="testImpresora()">Probar
                        Impresora</b-button>
                      <!-- <delivery-modal style="min-width: 100%; margin: 1% 0;" ref="deliveryModal"
                        @onPagarDelivery="onPagarDelivery"></delivery-modal> -->
                      <b-button :variant="mostrarNav ? 'danger' : 'info'" class="btnAction" block @click="ocultarNav()">
                        <i class="fas fa-bars"></i>
                        <span v-if="mostrarNav"> Ocultar Nav</span>
                        <span v-else> Mostrar Nav</span>
                      </b-button>

                    </b-row>
                  </b-col>
                  <b-col lg="3" xl="3" cols="12">
                    <table class="table b-table border table-footer" style="font-size:0.85rem">
                      <tbody>
                        <tr>
                          <!-- <td>
                            <b-button class="p-1 ml-1 mt-1" id="popover-metodopago" variant="success"><i class="fas fa-credit-card" ></i></b-button>
                            <br>
                            <b-badge v-if="ventas[ventaActiva].idMetodoPago==1" variant="success" style="font-size: 0.70rem;">EFECTIVO</b-badge>
                            <b-badge v-if="ventas[ventaActiva].idMetodoPago==2" variant="info" style="font-size: 0.70rem;">TARJETA</b-badge>
                            <b-badge v-if="ventas[ventaActiva].idMetodoPago==3" variant="info" style="font-size: 0.70rem;">YAPE/PLIN</b-badge>
                            <b-badge v-if="ventas[ventaActiva].idMetodoPago==4" variant="info" style="font-size: 0.70rem;">TRANSF.</b-badge>
                            <b-popover target="popover-metodopago" title="Metodo de pago" placement="bottomleft" triggers="focus" :style="{ minWidth: '600px' }">
                            <slot name="metodopago" style="width: 1200px;" >
                              <table class="table b-table border table-footer table-sm">
                                <tbody>
                                  <tr>
                                    <td>Efectivo</td>
                                    <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(1)"><i class="fas fa-check"></i></b-button></td>
                                    <td>
                                      <b-form-input v-model="ventas[ventaActiva].pago.efectivo.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                                    </td>
                                    <td>
                                      <input v-model="ventas[ventaActiva].pago.efectivo.monto" min="0" step="0.01" type="number" placeholder="S/." class="form-control"/>
                                    </td>
                                   </tr>
                                  <tr>
                                    <td>Tarjeta</td>
                                    <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(2)"><i class="fas fa-check"></i></b-button></td>
                                    <td>
                                      <b-form-input v-model="ventas[ventaActiva].pago.visa.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                                    </td>
                                    <td>
                                      <input class="form-control" v-model="ventas[ventaActiva].pago.visa.monto" min="0" step="0.01" type="number" placeholder="S/." @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.mastercard.monto - $event.target.value)"/>
                                    </td>
                                    </tr>
                                    <tr>
                                      <td>Yape/Plin</td>
                                      <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(3)"><i class="fas fa-check"></i></b-button></td>
                                      <td>
                                        <b-form-input v-model="ventas[ventaActiva].pago.mastercard.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                                      </td>
                                      <td>
                                        <input class="form-control" v-model="ventas[ventaActiva].pago.mastercard.monto" min="0" step="0.01" type="number" placeholder="S/." @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - $event.target.value)"/>
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>Transferencia</td>
                                      <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(4)"><i class="fas fa-check"></i></b-button></td>
                                      <td>
                                        <b-form-input v-model="ventas[ventaActiva].pago.delivery.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                                      </td>
                                      <td>
                                        <input
                                          class="form-control" v-model="ventas[ventaActiva].pago.delivery.monto" min="0" step="0.01" type="number" placeholder="S/." @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - $event.target.value)"/>
                                      </td>
                                    </tr>
                                  </tbody>
                              </table>
                            </slot>
                            </b-popover>
                          </td> -->
                          <td class="table-success">SubTotal</td>
                          <td class="table-success">{{ getSubTotal(ventaActiva) }}</td>
                        </tr>
                        <tr>
                          <td class="table-danger">Descuento</td>
                          <td class="table-danger">
                            {{ getDescuento(ventaActiva) }}
                          </td>
                        </tr>

                        <tr>
                          <td><b>TOTAL</b></td>
                          <td class="table-danger">
                            {{ getTotal(ventaActiva) }}
                          </td>
                        </tr>
                        <tr>
                          <td colspan="1">
                            <label>pagó con:</label>
                            <b-form-input type="text" placeholder="Pagó con" v-model="ventas[ventaActiva].pagocon">
                            </b-form-input><br>
                          </td>
                          <td>
                            <label>Vuelto:</label>
                            <b>S/
                              {{ parseFloat(ventas[ventaActiva].pagocon - getTotal(ventaActiva)).toFixed(2) }}

                            </b>
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
    <b-modal v-model="modalPresentacion" size="lg" hide-footer>
      <center>
        <h3 style="text-decoration: underline;">{{ producto.Producto }}</h3>
      </center>
      <b-row>
        <b-col cols="6">
          <b-card align="center" class="mb-3 contentCard" @click="cambiarPresentacion(
            producto,
            false
          )">
            <b-row>
              <b-col md="6" class="nomCart">
                <div>
                  <b-row class="nomCart2">
                    <center>
                      <h6 class="text-uppercase"> {{ producto.ProductoPresentacion }} </h6>
                    </center>
                  </b-row>
                  Stock <span style="padding: 5px 10px; border-radius: 5px ;background-color:#28a745;color:#fff"> {{
                    producto.stock }} </span>
                </div>
              </b-col>
              <b-col md="6" class="text-left">
                <h5>x1</h5>
                <span><b>Precio C: </b>S/ {{ producto.PrecioCosto | redondear(2) }}</span><br>
                <b>Precio V: </b>S/ {{ producto.PrecioContado | redondear(2) }}
              </b-col>
            </b-row>
          </b-card>
        </b-col>


        <b-col cols="6" v-for="(pres, index) in producto.PreciosPorProducto" :key="index">
          <b-card align="center" class="mb-3 contentCard" @click="cambiarPresentacion(
            pres,
            true
          )">
            <b-row>
              <b-col md="6" class="nomCart">
                <div>
                  <b-row class="nomCart2">
                    <center>
                      <h6 class="text-uppercase"> {{ pres.nombre }} </h6>
                    </center>
                  </b-row>
                  Stock <span style="padding: 5px 10px; border-radius: 5px ;background-color:#28a745;color:#fff">
                    {{ parseFloat(producto.stock / pres.cantidad).toFixed(2) }}
                  </span>
                </div>
              </b-col>
              <b-col md="6" class="text-left">
                <h5>x{{ pres.cantidad }}</h5>
                <span><b>Precio C: </b>S/ {{ pres.precioc | redondear(2) }}</span><br>
                <b>Precio V: </b>S/ {{ pres.precio | redondear(2) }}
              </b-col>
            </b-row>
          </b-card>
        </b-col>

        <!-- <b-col md="6" v-for="(pres,index) in producto.PreciosPorProducto" :key="index" class="nomCart">
                
                <div>
                  <b-row class="nomCart2">
                    <center> <h6 class="text-uppercase"> {{pres.nombre}} </h6> </center>
                  </b-row>
                    Stock <span style="padding: 5px 10px; border-radius: 5px ;background-color:#28a745;color:#fff"> {{ 
                    parseFloat(producto.stock/pres.cantidad).toFixed(2)
                    }} </span>
                </div>
              </b-col>
              <b-col md="6" class="text-left">
              <h5>x1</h5>
              <span><b >Precio C: </b>S/ {{ producto.PrecioCosto | redondear(2) }}</span><br>
              <b>Precio V: </b>S/ {{ producto.PrecioContado | redondear(2) }}
          </b-col> -->
      </b-row>
    </b-modal>
    <!-- MODALS TOUCH -->

    <b-modal ref="modalIngCant" id="ingCant" size="sm" hide-footer class="ingCant" title="Cantidad" hide-header-close>
      <label><b style="text-decoration: underline;">{{ objProd.Producto }}</b></label>
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

    <b-modal ref="modalIngPre" id="ingPre" size="sm" hide-footer class="ingPre" title="Precio" hide-header-close>
      <label><b style="text-decoration: underline;">{{ objProd.Producto }}</b></label>
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

    <b-modal ref="modalIngDesc" id="ingDesc" size="sm" hide-footer class="ingDesc" title="Descuento" hide-header-close>
      <label><b style="text-decoration: underline;">{{ objProd.Producto }}</b></label>
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

    <!-- MODALS TOUCH -->
    <producto-form :cbGuardar="(id, pxp) => agregarObj(id, pxp)" ref="productoFormEdit" :producto-update="productoSelect"
      @actualizarProducto="actualizarRegistro($event)"></producto-form>

    <b-modal id="modalCodigoBarra" size="lg" ref="modalCodigoBarra" hide-footer title="ESCANEE EL CODIGO DE BARRA">
        <div class="d-block text-center">
          <div id="codigoBarraStyle">
            <v-quagga
              :onDetected="onBarcodeScanned"
              :readerSize="readerSize"
              :readerTypes="detecteds"
              :patchSize="'medium'"
              class="cameraBarra"
            ></v-quagga>
          </div>
        </div>
      </b-modal>

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
import Vue from "vue";
import VueQuagga from "vue-quaggajs";
Vue.use(VueQuagga);

export default {
  components: {
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
        width: 1600,
        height: 1080
      },
      detecteds: [
        "code_128_reader",
        "ean_reader",
        "ean_8_reader",
        "code_39_reader",
        "code_39_vin_reader",
        "codabar_reader",
        "upc_reader",
        "upc_e_reader",
        "i2of5_reader",
        "2of5_reader",
        "code_93_reader"
      ],
      inProgress: false,
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
        { key: "Producto" },
        { key: "ProductoModelo", label: "Modelo" },
        { key: "ProductoMarca", label: "Marca" },
        { key: "ProductoTalla", label: "Talla" },
        { key: "Color" },
        { key: "Genero" },
        { key: "Botapie", label: "Botapie/Manga" },
        { key: "Anaquel" },
        { key: "pres", label: "" },
        { key: "presentacion", label: "Presentacion" },
        { key: "cantxPrecio", label: "	Cant X Precio" },
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
      loadCode: "",
      tabActive: 0,
      ventaActiva: 0,
      venta: {
        pagocon: 0,
        cliente: {},
        EsCredito: 0,
        Serie: "",
        Numero: "",
        Observacion: "",
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
      producto: {},
      index: 0,
      productoSelect: {},
      idPerfil: sessionStorage.getItem("IdPerfil"),
      objProd: {},
      cantidadInput: ""
    };
  },
  mounted() {
    console.log("componente montado");
    this.getVendedorInfo();
    this.getNumeroVenta();
    this.loadClienteVarios();
    this.$nextTick(() => {
      setTimeout(() => {
        let inputElement = `autocompleteProducto${this.ventaActiva}`;
        let autocompleteInput = document.getElementById(inputElement);
        autocompleteInput.focus();
      }, 500);
    });
  },
  methods: {
  ...mapActions({
    aplicarDescuento: 'aplicarDescuentoGeneral',
    limpiarCart: 'limpiarCarrito',
    obtenerStok: 'obtenerStock',
    add:'agregarProducto'
  }),
    abrirCodigoBarra() {
      this.$refs["modalCodigoBarra"].show();
      this.modalAbiertCode = true;
      this.result = "";
    },
    onBarcodeScanned(barcode) {
      if (this.modalAbiertCode) {
        this.result = barcode.codeResult.code;
        this.format = barcode.codeResult.format;
        const audio = new Audio(sound);
        audio.play();
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
      let producto = { ...event };
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

      let index = this.ventas[this.ventaActiva].productos.findIndex(
        v => v.IdProducto == producto.IdProducto
      );
      console.log(index);
      Vue.set(this.ventas[this.ventaActiva].productos, index, prod);
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
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }

      this.recognition = new webkitSpeechRecognition();
      this.recognition.lang = "es-ES";
      this.recognition.continuous = false;
      this.recognition.interimResults = false;

      this.recognition.onstart = () => {
        console.log("Speech recognition service has started");
      };

      this.recognition.onresult = event => {
        if (event.results.length > 0) {
          this.query = event.results[0][0].transcript;
          this.search();
        }
      };

      this.recognition.onerror = event => {
        console.error("Speech Recognition Error:", event);
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = async () => {
        if (!this.query) {
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
    abrirModalTouch(index, tipo) {
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
      }
      if (this.$refs["modalIngPre"].is_show) {
        this.objProd.precio = this.cantidadInput;
      }
      if (this.$refs["modalIngDesc"].is_show) {
        this.objProd.descuento = this.cantidadInput;
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
            width: 100%;
            heigth: 100%;
          }
          .imagen:hover{
            width: 200px;
            heigth: 200px;
          }
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
                <td style="width: 80px">${
                  data.ProductoTalla ? data.ProductoTalla : ""
                }</td>
                <td style="width: 80px">${data.Color ? data.Color : ""}</td>
                <td style="width: 80px">${data.Genero ? data.Genero : ""}</td>
                <td style="width: 80px">${data.Botapie ? data.Botapie : ""}</td>
                <td style="width: 65px" class="table-primary"><b>${
                  data.PrecioContado
                }</b></td>
                <td style="width: 5%">
                  <img src="${
                    data.Imagen == null || data.Imagen == ""
                      ? "static/no-photo.png"
                      : "/resources/images/productos/" + data.Imagen
                  }" class="imagen">
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
          let inputElement = `autocompleteProducto${this.ventaActiva}`;
          let autocompleteInput = document.getElementById(inputElement);
          autocompleteInput.focus();
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

      if (obj.TieneIgv) {
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
      const descuento = parseFloat(val) || 0;
      this.ventas[this.ventaActiva].descuentoGeneral = descuento;
      this.aplicarDescuentoAProductos(descuento);
    },

    reaplicarDescuentoVigente(ventaIndex = null) {
      const idx = ventaIndex !== null ? ventaIndex : this.ventaActiva;
      const venta = this.ventas[idx];
      if (venta.puntosAplicados > 0 && venta.descuentoPuntos && venta.descuentoPuntos.descuento) {
        this.aplicarDescuentoAProductos(parseFloat(venta.descuentoPuntos.descuento), idx);
      } else if (parseFloat(venta.descuentoGeneral) > 0) {
        this.aplicarDescuentoAProductos(parseFloat(venta.descuentoGeneral), idx);
      }
    },
    aplicarDescuentoAProductos(descuentoTotal, ventaIndex = null) {
      const idx = ventaIndex !== null ? ventaIndex : this.ventaActiva;
      const productos = this.ventas[idx].productos;
      if (!productos.length) return;

      const subtotal = parseFloat(this.getSubTotal(idx));
      if (subtotal <= 0) return;

      productos.forEach((prod, index) => {
        const subtotalProd = parseFloat(prod.cantidad * prod.precio);
        const proporcion = subtotalProd / subtotal;
        const descuentoProd = parseFloat((descuentoTotal * proporcion).toFixed(2));
        this.$set(this.ventas[idx].productos, index, { ...prod, descuento: descuentoProd });
      });

      const totalDistribuido = productos.reduce((sum, p) => sum + parseFloat(p.descuento), 0);
      const diferencia = parseFloat((descuentoTotal - totalDistribuido).toFixed(2));
      if (diferencia !== 0 && productos.length > 0) {
        const ultimo = productos[productos.length - 1];
        this.$set(this.ventas[idx].productos, productos.length - 1, {
          ...ultimo,
          descuento: parseFloat((parseFloat(ultimo.descuento) + diferencia).toFixed(2))
        });
      }
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
        productos.filter(
          v => v.IdProducto == producto.IdProducto
        )[0].cantidad += parseFloat(1);
        this.$nextTick(() => {
          this.metodoPagoCambio(1);
          this.reaplicarDescuentoVigente();
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
      productos.unshift(prod);
      // if (esNota) {
      //   if (producto.Producto == "TAPAJUNTA") {
      //     console.log(prod);
      //   }
      //   prod.cantidad = producto.cantidad;
      //   prod.precio = producto.PrecioContado;
      // }
      this.$nextTick(() => {
        let input = document.getElementById("txtCodeBar0");
        input.focus();
        input.select();
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
          this.$set(venta.productos, index, { ...prod, descuento: 0 });
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
        if (prod.esPresentacion) {
          return total + parseFloat(prod.CantidadPresentacion * prod.cantidad * prod.precio).toFixed(2);
        } else {
          return parseFloat(total + parseFloat(prod.cantidad * prod.precio));
        }
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
      venta.Observacion = (venta.Observacion + "").toUpperCase();
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

      await this.axios.post(this.API + "ventas", venta).then(response => {
        if (response.data.insertId) {
          window.open(
            "/imprimir/index.php?IdDocVenta=" +
              response.data.insertId +
              "&preview=1",
            "_blank"
          );

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
      venta.Observacion = (venta.Observacion + "").toUpperCase();
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
          prod.ProductoPresentacion = producto.Descripcion;
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
    showPresentacion(prod, index) {
      this.producto = { ...prod };
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
    abrirImagen(producto, url) {
      this.productoNombre = producto;
      this.productoUrl = url;
      this.$refs["modalImagen"].show();
    }
  },
  computed: {
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
