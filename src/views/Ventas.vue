<template>
<b-container class="body pos-page" fluid>
  <buscador-productos-ia v-model="productExplorerOpen" />
  <div class="quote-create-host"><b-modal v-model="quotePresentationOpen" title="Preparar proforma" hide-footer size="lg" modal-class="quote-create-modal"><cliente-telefono-rapido v-if="quotePresentationOpen &amp;&amp; quotePresentation.tipo==='sistemas'" :client-id="ventas[index] &amp;&amp; ventas[index].cliente ? ventas[index].cliente.IdCliente : 0" required :disabled="inProgress" @status="quotePhoneReady=$event" @loaded="quotePhoneUpdated" @saved="quotePhoneUpdated"/><proforma-presentacion v-model="quotePresentation" :disabled="inProgress" :active="quotePresentationOpen" :products="ventas[index] ? ventas[index].productos : []"/><div class="text-right"><b-button @click="quotePresentationOpen=false" :disabled="inProgress">Cancelar</b-button> <b-button variant="danger" @click="guardarProforma" :disabled="inProgress || (quotePresentation.tipo==='sistemas' &amp;&amp; (!quotePresentation.alcance.trim() || !quotePhoneReady))">{{inProgress?'Guardando...':'Crear proforma'}}</b-button></div></b-modal></div>
  <div v-if="aliCheckoutError && !aliCheckoutReview" class="ali-checkout-error" role="alert"><strong>ALIPAGO:</strong> {{ aliCheckoutError }} <button type="button" @click="aliCheckoutError=''">Cerrar</button></div>
  <section v-if="aliCheckoutReview" class="ali-checkout-panel" role="region" aria-label="Confirmación de ALIPAGO">
    <div><strong>ALIPAGO · Revisa antes de cobrar</strong><p>{{ aliCheckoutReview.summary.client }} · {{ aliCheckoutReview.summary.document }} · Serie {{ aliCheckoutReview.summary.series }}</p></div>
    <strong class="ali-checkout-total">S/ {{ Number(aliCheckoutReview.summary.total).toFixed(2) }}</strong>
    <p><span v-for="(payment,i) in aliCheckoutReview.summary.payments" :key="i">{{ payment.label }}: S/ {{ Number(payment.amount).toFixed(2) }} · </span>Descuento: S/ {{ Number(aliCheckoutReview.summary.discount).toFixed(2) }}</p>
    <details><summary>Ver productos ({{ aliCheckoutReview.summary.items.length }})</summary><ul><li v-for="(item,i) in aliCheckoutReview.summary.items" :key="i">{{ item.quantity }} × {{ item.name }} · S/ {{ Number(item.total).toFixed(2) }}</li></ul></details>
    <p>Verifica que recibiste el dinero. Di <strong>«Confirmar pago»</strong> para registrar la venta o <strong>«Cancelar»</strong>. {{ aliCheckoutReview.summary.fiscal }}</p>
    <p v-if="aliCheckoutError" role="alert">{{ aliCheckoutError }}</p>
    <div class="ali-checkout-actions"><button type="button" :disabled="inProgress || aliCheckoutReview.uncertain" @click="aliCheckoutCommand('confirmar pago')">{{ inProgress ? 'Registrando…' : 'Verifiqué el pago · Confirmar' }}</button><button type="button" :disabled="inProgress" @click="aliCheckoutCommand('cancelar')">Cancelar</button><button type="button" :disabled="inProgress" @click="aliRecoverCheckout">Consultar estado</button></div>
  </section>
  <div class="pos-issued-documents">
    <button type="button" class="pos-issued-button" title="Comprobantes emitidos" aria-label="Abrir comprobantes emitidos" @click="showIssuedDocuments = true"><i class="fas fa-file-invoice" aria-hidden="true"></i><span>Comprobantes emitidos</span></button>
    <b-modal v-model="showIssuedDocuments" title="Comprobantes emitidos" size="xl" hide-footer modal-class="nst-issued-modal">
      <venta-reimpresion v-if="showIssuedDocuments" />
    </b-modal>
  </div>
  <div class="pos-mobile-methods-host"><b-modal id="pos-mobile-payment-methods" title="Métodos de pago" hide-footer modal-class="pos-mobile-methods-modal"><template v-if="ventas[index]">                                              <section class="pos-payment-picker" aria-label="Formas de pago">
                                                <header class="pos-payment-picker-head"><span><i class="fas fa-wallet" aria-hidden="true"></i></span><div><strong>Formas de pago</strong><small>Distribuye el importe de la venta</small></div></header>
                                                <div v-for="method in [{key:'efectivo',label:'Efectivo',icon:'fas fa-money-bill-alt'},{key:'yape',label:'Yape/Plin',icon:'fas fa-mobile-alt'},{key:'visa',label:'Tarjeta',icon:'fas fa-credit-card'},{key:'mastercard',label:'Transferencia',icon:'fas fa-university'}]" :key="method.key" :class="['pos-payment-option','payment-'+method.key]">
                                                  <span class="pos-payment-option-icon"><i :class="method.icon" aria-hidden="true"></i></span>
                                                  <strong>{{ method.label }}</strong>
                                                  <button type="button" class="pos-payment-assign" :disabled="bloquearInput" :aria-label="'Asignar total a '+method.label" :title="'Asignar total a '+method.label" @click="cargarMonto(method.key)"><i class="fas fa-check" aria-hidden="true"></i></button>
                                                  <input v-model="ventas[index].pago[method.key].descripcion" type="text" class="form-control pos-payment-reference" :disabled="bloquearInput" :placeholder="method.key==='efectivo'?'Referencia':'Operación'" :aria-label="'Referencia de '+method.label">
                                                  <input v-model="ventas[index].pago[method.key].monto" type="number" inputmode="decimal" min="0" step="0.01" class="form-control pos-payment-amount" :disabled="bloquearInput" :aria-label="'Monto de '+method.label" @input="distribuirPago(method.key,$event.target.value)">
                                                </div>
                                              </section></template></b-modal></div>
  <div v-if="ventas.length" class="pos-mobile-payment-bar">
    <button type="button" class="pos-mobile-methods" v-b-modal.pos-mobile-payment-methods><i class="fas fa-credit-card" aria-hidden="true"></i><span>Métodos de pago</span></button>
    <button type="button" class="pos-mobile-more" @click="mostrarAccionesMovil"><i class="fas fa-th-large" aria-hidden="true"></i><span>Más opciones</span></button>
    <button type="button" class="pos-mobile-pay" :disabled="inProgress" @click="guardarVenta"><i class="fa fa-shopping-cart" aria-hidden="true"></i><span>{{ inProgress ? 'Procesando...' : 'Pagar' }}</span><strong>S/ {{ $options.filters.redondear((ventas[index] && ventas[index].totalCart) || 0) }}</strong></button>
  </div>
   <pedidos-web ref="pedidosWeb" :on-load="cargarPedidoWeb" :hide-button="true" @pending="pedidosWebPendientes = $event" />
   <b-card no-body v-if="ventas.length>0" class="pos-shell">
    <b-tabs card pills class="pos-tabs" v-model="index" justified>
      <template slot="tabs"><li role="presentation" class="nav-item pos-product-ai-launcher"><button type="button" class="pos-product-ai-search" title="Buscar productos con IA" aria-label="Buscar productos con IA" @click.stop="productExplorerOpen=true" @keydown.stop><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5" stroke-linecap="round"/></svg><span>IA</span></button></li></template>
      <b-tab :title="`Venta ${i+1}`" v-for="(tab,i) in ventas" :key="i" class="pos-workspace">
          <b-row class="pos-sale-grid">
             <b-col md="7" class="pos-customer-panel">
                <b-row class="pos-customer-fields-row">
                 <b-col md="4" class="pos-meta-field pos-meta-document">
                      <div class="pos-meta-inline-card pos-meta-inline-type">
                        <span class="pos-meta-inline-icon pos-document-type-icon" aria-hidden="true">
                          <i :class="iconoTipoDocumento(tab.tipoVenta)"></i>
                        </span>
                        <span class="sr-only">Tipo de venta</span>
                        <autocomplete
                            required="required"
                            :ref="`inputTipoVenta${i}`"
                            class="pos-type-autocomplete"
                            :url="API + 'ventas/tipos'"
                            anchor="TipoDoc"
                            initValue="BOLETA ELEC."
                            label="writer"
                            placeholder="Tipo de venta"
                            :on-select="setTipoVenta"
                            :onFocus="focusAutocomplete.bind(null, `inputTipoVenta${i}`)"
                            :classes="{input: 'form-control'}">
                        </autocomplete>
                        <span class="pos-meta-inline-chevron" aria-hidden="true"><i class="fa fa-chevron-down"></i></span>
                      </div>
                      <label class="lblSerie"><b style="display:none" class="mostrarSerie">{{ tab.Serie }} - {{ tab.Numero }}</b></label>
                      <b-form-group class="ocultarWeb">
                          <label>Vendedor:</label>
                          <autocomplete
                                  required="required"
                                  :ref="`inputVendedor${i}`"
                                  class="pos-seller-autocomplete"
                                  :url="API + 'lstusuarios'"
                                  anchor="NombreUsuario"
                                  label="writer"
                                  placeholder="Seleccione vendedor"
                                  :on-select="setVendedor"
                                  :onFocus="focusAutocomplete.bind(null, `inputVendedor${i}`)"
                                  :classes="{input: 'form-control'}">
                              </autocomplete>
                      </b-form-group>
                      <b-form-group v-if="tab.isNota" > 
                          <label>Doc. Referencia:</label>
                          <autocomplete
                              required="required"
                              :ref="`inputDocRef${i}`"
                              :url="API + 'ventas/lista/'+venta.puntoVenta.IdDocVentaPuntoVenta"
                              anchor="NroComprobante"
                              initValue=""
                              label="writer"
                              placeholder=""
                              :on-select="setDocRef"
                              :onFocus="focusAutocomplete.bind(null, `inputDocRef${i}`)"
                              :classes="{input: 'form-control'}">
                          </autocomplete>
                      </b-form-group>
                 </b-col>
                  <b-col md="4" class="ocularMovil2 my-0 pos-meta-field pos-meta-store">
                    <div class="pos-meta-inline-card">
                      <span class="pos-meta-inline-icon" aria-hidden="true"><i class="fa fa-cubes"></i></span>
                      <span class="pos-meta-inline-value">{{ tab.almacen.Almacen || 'Cargando...' }}</span>
                    </div>
                    <b-form-group v-if="tab.isNota" class="my-0">
                      <label >Motivo :</label>
                      <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                    
                    </b-form-group>
                  </b-col>
                  <b-col md="4" class="ocularMovil2 pos-meta-field pos-meta-terminal">
                     <div class="pos-meta-inline-card">
                       <span class="pos-meta-inline-icon" aria-hidden="true"><i class="fa fa-map-marker"></i></span>
                       <span class="pos-meta-inline-value">{{ tab.puntoVenta.PuntoVenta || 'Cargando...' }}</span>
                     </div>
                  </b-col>
                  <b-col md="12" class="my-0 pos-client-block" :class="{ 'is-client-collapsed': tab.MostrarClienteDatos === false }">
                      <b-form-group  class="my-0">
                        <div class="pos-client-toolbar">
                          <label class="pos-field-label">
                            <i :class="tab.MostrarClienteDatos === false ? 'fas fa-user-shield' : 'fa fa-user-circle-o'" aria-hidden="true"></i>
                            {{ tab.MostrarClienteDatos === false ? 'Datos del cliente ocultos' : 'Cliente' }}
                          </label>
                          <div class="pos-client-toolbar-actions">
                            <clientes-form
                              v-if="tab.MostrarClienteDatos !== false && tab.cliente.IdCliente && tab.cliente.DniRuc != '00000000'"
                              class="pos-client-edit"
                              buttonIcon="fas fa-user-edit"
                              buttonClass="pos-client-icon-button pos-client-edit-button"
                              buttonTitle="Editar cliente"
                              idModal="editCliente"
                              @onAddCliente="setCliente"
                              :editCliente="tab.cliente">
                            </clientes-form>
                            <b-button
                              type="button"
                              class="pos-client-icon-button pos-client-privacy-button"
                              :class="{ 'is-private': tab.MostrarClienteDatos === false }"
                              :title="tab.MostrarClienteDatos === false ? 'Mostrar datos del cliente' : 'Ocultar datos del cliente'"
                              :aria-label="tab.MostrarClienteDatos === false ? 'Mostrar datos del cliente' : 'Ocultar datos del cliente'"
                              @click="toggleClienteDatos(tab)">
                              <i :class="tab.MostrarClienteDatos === false ? 'fas fa-eye' : 'fas fa-eye-slash'" aria-hidden="true"></i>
                            </b-button>
                          </div>
                        </div>
                          <b-input-group v-show="tab.MostrarClienteDatos !== false">
                              <autocomplete
                                  required="required"
                                  :ref="`inputCliente${i}`"
                                  :url="API + 'clientes'"
                                  anchor="ClienteDniRuc"
                                  label="writer"
                                  placeholder=""
                                  :onInput="(val) => onAliClientInput(val, i)"
                                  :on-select="setCliente" 
                                  :onFocus="focusAutocomplete.bind(null, `inputCliente${i}`)"
                                  :classes="{input: 'form-control'}">
                              </autocomplete>
                              <b-button @click="abrirModal()"><i class="fa fa-hand-pointer" aria-hidden="true"></i></b-button>
                              <input v-model="dni"
                                  @keydown.enter.prevent="loadClienteBarcode"
                                  type="text"
                                  class="form-control ocularMovil2"
                                  placeholder="Codigo de Barra" />
                              <b-input-group-append>
                                  <!--<proveedores-tabla></proveedores-tabla>-->
                                  <clientes-form :id="`cltNuevo${i}`" :ruc="newDniRuc" :newDniRuc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                                  
                              </b-input-group-append>
                          </b-input-group>
                          <div v-show="tab.MostrarClienteDatos !== false" class="pos-guarantee-control">
                            <b-button
                              type="button"
                              class="pos-guarantee-toggle"
                              :class="{ 'is-active': tab.MostrarGarantia }"
                              :disabled="bloquearInput"
                              @click="toggleGarantia(tab)">
                              <i class="fa fa-shield" aria-hidden="true"></i>
                              Garantía
                              <span>{{ tab.MostrarGarantia ? 'Activa' : 'Opcional' }}</span>
                            </b-button>
                            <transition name="pos-guarantee-reveal">
                              <b-form-radio-group
                                v-if="tab.MostrarGarantia"
                                class="pos-guarantee-options"
                                v-model="tab.Garantia"
                                :options="[
                                  { text: '3 Meses', value: 1 },
                                  { text: '6 Meses', value: 2 },
                                  { text: '12 Meses', value: 3 }
                                ]"
                                :disabled="bloquearInput">
                              </b-form-radio-group>
                            </transition>
                          </div>

                          <b-row v-if="$route.query.idPedido" v-show="tab.MostrarClienteDatos !== false">
                              <b-col>
                                  <b-form-group>
                                      <label style="font-weight:bold">Cliente (Delivery):</label>
                                      <b-form-input 
                                        required style="text-transform:uppercase; font-size:0.9rem"
                                        placeholder="Ingrese cliente" autocomplete="off" 
                                        v-model="tab.nombreLlevar" ref="inputNombreCliente" type="text" name="NombreLlevar" id="NombreLlevar" readonly></b-form-input>
                                  </b-form-group>
                              </b-col>
                              <b-col>
                                  <b-form-group>
                                      <label style="font-weight:bold">Direccion Entrega (Delivery):</label>
                                      <b-form-textarea 
                                        required style="text-transform:uppercase; font-size:0.9rem"
                                        placeholder="Ingrese direccion" rows="3" 
                                        autocomplete="off" 
                                        v-model="tab.DireccionEntrega" ref="inputDireccionEntrega" type="text" name="DireccionEntrega" id="DireccionEntrega" readonly></b-form-textarea>
                                  </b-form-group>
                              </b-col>
                          </b-row>
                      </b-form-group>
                  </b-col>
                  <b-col cols="6" v-if="mostrarSeccionOferta() && IdOferta">
                    <b-form-group >
                          <label>Doc. Referencias Oferta:</label>
                         
                        </b-form-group>
                  </b-col>
                  <b-col v-show="tab.MostrarClienteDatos !== false" cols="12" class="pos-customer-options-line">
                    <b-row no-gutters align-v="center">
                      <b-col cols="12" md="3" class="pos-organization-toggle">
                        <b-button
                          type="button"
                          class="pos-organization-button"
                          :class="{ 'is-active': tab.EsOrganizacion }"
                          :disabled="bloquearInput"
                          :aria-pressed="tab.EsOrganizacion ? 'true' : 'false'"
                          @click="toggleOrganizacion(tab)">
                          <span class="pos-organization-switch" aria-hidden="true">
                            <span class="pos-organization-knob"></span>
                          </span>
                          <span class="pos-organization-copy">
                            <strong><i class="fas fa-building" aria-hidden="true"></i> Organización</strong>
                            <small>{{ tab.EsOrganizacion ? 'Habilitada' : 'Deshabilitada' }}</small>
                          </span>
                        </b-button>
                      </b-col>
                      <b-col cols="12" :md="tab.EsOrganizacion ? 5 : 9" class="pos-address-column">
                        <div class="pos-address-control">
                          <span class="pos-address-icon"><i class="fa fa-map-marker" aria-hidden="true"></i></span>
                          <b-form-select v-if="tab.cliente.IdCliente" v-model="tab.CampoDireccion" class="pos-address-select">
                            <option :value="tab.cliente.Direccion">{{ tab.cliente.Direccion }}</option>
                            <option :value="tab.cliente.Direccion2">{{ tab.cliente.Direccion2 }}</option>
                            <option :value="tab.cliente.Direccion3">{{ tab.cliente.Direccion3 }}</option>
                          </b-form-select>
                          <div v-else class="pos-address-placeholder">Seleccione un cliente para cargar su dirección</div>
                        </div>
                      </b-col>
                      <b-col cols="12" md="4" class="pos-organization-name" v-if="tab.EsOrganizacion">
                        <b-form-input
                          v-model="tab.nombreOrganizacion"
                          placeholder="NOMBRE DE LA ORGANIZACIÓN"></b-form-input>
                      </b-col>
                    </b-row>
                  </b-col>
                 </b-row>
            </b-col>
            <b-col md="3" class="ocultarMovil pos-loyalty-panel" >
                <div class="pos-panel-kicker"><i class="fa fa-star-o" aria-hidden="true"></i> Fidelización</div>
                <b-form-group class="text-center" v-if="tab.cliente.Cliente">
                    <div class="puntos">
                        {{ (tab.cliente.Puntos || 0) | redondeoMenor }}
                    </div>
                    Puntos del cliente
                </b-form-group>
                <b-input-group prepend="Puntos" :append="descuentoPuntos.descuento | redondear" v-if="venta.cliente.Puntos && descuentoGeneral == 0">
                    <b-form-input
                        v-model="puntosAplicados"
                        min="0"
                        :max="tab.cliente.Puntos"
                        @input="aplicarPuntos"
                        type="number"
                    ></b-form-input>
                </b-input-group>
                <b-form-text v-if="descuentoPuntos.ValorPunto">
                   1 Punto equivale a: S/ {{ descuentoPuntos.ValorPunto | redondear }}
                </b-form-text>
                <b-form-text v-if="tab.cliente.EsVIP">
                   Cliente VIP con Precio Convenio
                </b-form-text>
                <b-form-group class="ocultarMovil mt-4" >
                    <label class="pos-field-label"><i class="fa fa-handshake-o" aria-hidden="true"></i> Comisionista</label>
                    <autocomplete
                        :ref="`inputComisionista${i}`"
                        required="required"
                        :url="API + 'comisionistas'"
                        anchor="ClienteDniRuc"
                        label="writer"
                        placeholder="Comisionista"
                        :onInput="(val) => onAliClientInput(val, i)"
                        :on-select="setComisionista" 
                        :onFocus="focusAutocomplete.bind(null, `inputComisionista${i}`)"
                        :classes="{input: 'form-control'}">
                    </autocomplete>
                </b-form-group>
            </b-col>
             <b-col md="2" class="ocularMovil2 pos-document-panel">
                <div class="pos-panel-kicker pos-panel-kicker-light"><i class="fa fa-shield" aria-hidden="true"></i> Documento</div>
                <b-form-group class="text-center">
                    <div class="big-text">
                        {{ tab.Serie }} - <b>{{ tab.Numero }}</b>
                    </div>
                    {{ tab.FechaDoc }}
                </b-form-group>
                <b-form-group class="ocultarMovil pos-seller-control">
                  <center>
                    <span class="pos-reference-badge"><i class="fa fa-bolt" aria-hidden="true"></i> Valor Referencial</span>
                  </center>
                </b-form-group>
                <b-form-group class="ocultarMovil">
                    <label class="pos-field-label pos-field-label-light">
                      <i class="fa fa-user" aria-hidden="true"></i>
                      Vendedor asignado
                      <span class="pos-seller-session" v-if="tab.vendedor && tab.vendedor.NombreUsuario">
                        <i class="fa fa-circle" aria-hidden="true"></i> Sesión
                      </span>
                    </label>
                    <autocomplete
                            required="required"
                            :ref="`inputVendedor${i}`"
                            class="pos-seller-autocomplete"
                            :url="API + 'lstusuarios'"
                            anchor="NombreUsuario"
                            label="writer"
                            placeholder="Seleccione vendedor"
                            :on-select="setVendedor" 
                            :onFocus="focusAutocomplete.bind(null, `inputVendedor${i}`)"
                            :classes="{input: 'form-control'}">
                        </autocomplete>
                    </b-form-group>
                <b-badge variant="info" v-if="ventas[index].adelanto">Adelanto de: S/ {{ ventas[index].adelanto }} </b-badge>
                <b-badge variant="success" v-if="ventas[index].adelanto">Total a pagar: S/ {{ $options.filters.redondear(ventas[index].totalCart -  ventas[index].adelanto) }} </b-badge>
            </b-col>
            <b-col cols="12" class="pos-cart-panel">
                    <productos-carrito 
                        @onInputCantidad="actualizarDescuento"
                        @onAddProducto="onAddProducto"
                        @onRemoveProducto="actualizarDescuento"
                        @setProducto="setProducto"
                        :igvTotal="tieneIgv"
                        :setPrecio="true"
                        :ref="`carrito${i}`" 
                        :showButton="false" 
                        :bloquearInput="bloquearInput"
                        :fields="carrito.fields"
                        :isVentas="true"
                        :idAlmacen="tab.almacen.IdAlmacen"
                        :onSetProducto="onSetProducto"
                        :esDelivery="tab.EsDelivery"
                        :productos="tab.productos"
                        :id="`prodiuctos${i}`"
                        @eliminarProducto="eliminarProducto"
                        :index="i"
                        >
                    </productos-carrito>
                    <div class="pos-cart-pay-row pos-pay-row">
                      <b-button
                        type="button"
                        class="btnAction imceden-pay-button pos-pay-button"
                        size="lg"
                        @click="guardarVenta"
                        :disabled="inProgress"
                        variant="success">
                        <i class="fa fa-shopping-cart" aria-hidden="true"></i>
                        <span class="imceden-pay-label pos-pay-label">{{ inProgress ? 'Procesando...' : 'Pagar' }}</span>
                        <strong class="imceden-pay-amount">
                          S/ {{ $options.filters.redondear((tab && tab.totalCart) || 0) }}
                        </strong>
                      </b-button>
                    </div>
                </b-col>

          </b-row>
      </b-tab>
      
    </b-tabs>
  </b-card>

    <b-row v-if="pagosPendientes>0">
        <b-col class="holder">
            <h4 class="news" height="100" width="200" bgcolor="white">Tiene <b>{{pagosPendientes}} pago(s) pendientes</b> del servicio de facturación, porfavor regularizar para mantener la validez de los comprobantes emitidos.  <b>{{fechaUltPago!=''?' Fecha de Vencimiento: '+fechaUltPago:''}}</b></h4>
        </b-col>
    </b-row>
    <b-container class="bv-example-row pos-checkout-area" id="conten" fluid>
        <b-row>
            <b-col md="12" v-if="this.$route.query.idPedido">
                <div class="form-inline">
                    <h4>Pago de delivery código: <b-badge variant="info">{{this.$route.query.idPedido}}</b-badge></h4>
                </div>
            </b-col>
        </b-row>
        <b-form class="pos-checkout-form" @submit="guardarVenta" @keydown.enter="prevent">
            
            <b-row v-if="mostrarSeccionOferta()">
              <b-col cols="4">

                <b-table :fields="fieldsDocRef" :items="DocRefOfertas">
                  <template slot="action" slot-scope="data">
                    <b-button variant="danger" @click="eliminarDocRef(data.index)">
                      <i class="fa fa-trash" aria-hidden="true"></i>
                    </b-button>
                  </template>
                  </b-table>
              </b-col>
            </b-row>
            <b-row class="pos-checkout-grid">
                <b-col cols="12" md="4" lg="3" class="text-center pos-action-panel" align-h="center">
                    <b-button class="btnAction" type="button" @click="anularPagoDelivery" variant="danger" v-if="this.$route.query.idPedido">Anular Pago Delivery</b-button>
                    <b-button class="btnAction pos-btn-proforma" type="button" v-if="!this.$route.query.idPedido" :disabled="inProgress" @click="quotePhoneReady=false;quotePresentation=Object.assign({tipo:'productos',alcance:'',entregables:'',condiciones:''},ventas[index].presentacionProforma || {});quotePresentationOpen=true">Crear Proforma</b-button>
                    <!-- <b-button class="btnAction" @click="guardarVenta" :disabled="inProgress" variant="success" >Efectuar Venta</b-button> -->
                    <b-button class="btnAction pos-btn-window" @click="anadirVenta">Añadir Ventana</b-button>
                    <b-button class="btnAction pos-btn-remove-window" @click="eliminarVentana" v-if="index!=0">Eliminar Ventana</b-button>
                    <b-button class="btnAction pos-btn-print" v-if="ultimaVenta" target="_blank" :href="'/imprimir/index.php?IdDocVenta=' + ultimaVenta">Imprimir última Venta</b-button>
                    <b-button class="btnAction pos-btn-web-orders" :class="{ 'has-pending': pedidosWebPendientes > 0 }" variant="danger" @click="$refs.pedidosWeb.abrir()"><i class="fas fa-shopping-bag" aria-hidden="true"></i> Pedidos tienda virtual <span v-if="pedidosWebPendientes" class="web-pending-badge">{{ pedidosWebPendientes }}</span></b-button>
                    <proformas :idAlmacen="ventas[index].almacen.IdAlmacen" @onSelectProforma="setProforma"  @guardarDatosProforma="guardarDatosProforma"></proformas>
                    <delivery-modal ref="deliveryModal" @onPagarDelivery="onPagarDelivery"></delivery-modal>

                     
                    <!--  -->
                  
                </b-col>

                <b-col cols="12" md="3" lg="3" v-if="!venta.EsCredito" class="ocultarMovil pos-metrics-panel">
                      <table class="table b-table border table-footer table-sm text-center "  >
                          <tbody>
                              <tr class="ocultarMovil"><td colspan="2">Mano de obra bruta</td></tr>
                              <tr class="ocultarMovil"><td colspan="2"><h3>{{ manoDeObraBruta | redondear }}</h3></td></tr>
                              <tr class="ocultarMovil"><td colspan="2">Mano de obra neta</td></tr>
                              <tr class="ocultarMovil"><td colspan="2"><h3 :class="{'text-danger': manoDeObraNeta <= 0}">{{ manoDeObraNeta | redondear }}</h3></td></tr>
                              
                          </tbody>
                      </table>
                    </b-col>
                          <b-col cols="12" md="3" lg="3" v-if="!venta.EsCredito" class="ocultarWeb">
                            <table class="table b-table border table-footer table-sm text-center "  >
                                <tbody>
                                    <!-- <tr style="background: #ffffff !important; ">
                                      <td width="50%">
                                        <b-form-checkbox
                                          v-model="mostrarDescripcion"
                                          :value="1"
                                          :unchecked-value="0"
                                        >
                                          Descripción
                                        </b-form-checkbox>
                                      </td>
                                      <td width="50%">
                                        <b-form-textarea
                                          placeholder="Descripción"
                                          v-model="venta.descripcion"
                                          style="field-sizing: content;"
                                          v-if="mostrarDescripcion==1"
                                        ></b-form-textarea>
                                      </td>
                                    </tr> -->
                                    <tr class="ocultarMovil"><td colspan="2">Mano de obra bruta</td></tr>
                                    <tr class="ocultarMovil"><td colspan="2"><h3>{{ manoDeObraBruta | redondear }}</h3></td></tr>
                                    <tr class="ocultarMovil"><td colspan="2">Mano de obra neta</td></tr>
                                    <tr class="ocultarMovil"><td colspan="2"><h3 :class="{'text-danger': manoDeObraNeta <= 0}">{{ manoDeObraNeta | redondear }}</h3></td></tr>
                                </tbody>
                            </table>   
                            
                            
                          </b-col>
                          <b-col cols="12" md="4" lg="3" class="pos-summary-panel">
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
                                    <td>
                                          <b-button class="p-1 ml-1 mt-1 pos-payment-method-trigger" :id="`popover-metodopago-${index}`" variant="success"><i class="fas fa-credit-card" ></i></b-button>
                                          <br>
                                          <b-badge v-if="idMetodoPago==1" variant="success" style="font-size: 0.70rem;">EFECTIVO</b-badge>
                                          <b-badge v-if="idMetodoPago==2" variant="info" style="font-size: 0.70rem;">TARJETA</b-badge>
                                          <b-badge v-if="idMetodoPago==3" variant="info" style="font-size: 0.70rem;">TRANSF.</b-badge>
                                          <b-badge v-if="idMetodoPago==4" variant="info" style="font-size: 0.70rem;">YAPE/PLIN</b-badge>
                                          <b-popover 
                                              :target="`popover-metodopago-${index}`"
                                              placement="bottom" 
                                              triggers="click"
                                              custom-class="custom-popover pos-payment-popover"
                                              auto-hide
                                            >
                                              <section class="pos-payment-picker" aria-label="Formas de pago">
                                                <header class="pos-payment-picker-head"><span><i class="fas fa-wallet" aria-hidden="true"></i></span><div><strong>Formas de pago</strong><small>Distribuye el importe de la venta</small></div></header>
                                                <div v-for="method in [{key:'efectivo',label:'Efectivo',icon:'fas fa-money-bill-alt'},{key:'yape',label:'Yape/Plin',icon:'fas fa-mobile-alt'},{key:'visa',label:'Tarjeta',icon:'fas fa-credit-card'},{key:'mastercard',label:'Transferencia',icon:'fas fa-university'}]" :key="method.key" :class="['pos-payment-option','payment-'+method.key]">
                                                  <span class="pos-payment-option-icon"><i :class="method.icon" aria-hidden="true"></i></span>
                                                  <strong>{{ method.label }}</strong>
                                                  <button type="button" class="pos-payment-assign" :disabled="bloquearInput" :aria-label="'Asignar total a '+method.label" :title="'Asignar total a '+method.label" @click="cargarMonto(method.key)"><i class="fas fa-check" aria-hidden="true"></i></button>
                                                  <input v-model="ventas[index].pago[method.key].descripcion" type="text" class="form-control pos-payment-reference" :disabled="bloquearInput" :placeholder="method.key==='efectivo'?'Referencia':'Operación'" :aria-label="'Referencia de '+method.label">
                                                  <input v-model="ventas[index].pago[method.key].monto" type="number" inputmode="decimal" min="0" step="0.01" class="form-control pos-payment-amount" :disabled="bloquearInput" :aria-label="'Monto de '+method.label" @input="distribuirPago(method.key,$event.target.value)">
                                                </div>
                                              </section>
                                            </b-popover>
                                    </td>
                                    <td><b>SubTotal</b></td>
                                    <td class="table-danger">{{ sumatoriaSubTotal  }}</td>
                                  </tr>
                                  <tr v-if="productos.some(v=>v.FLETE >0)">
                                    <td><b>Total Flete</b></td>
                                    <td class="table-danger">{{ productos.reduce((sum,producto)=>{
                                      return sum + parseFloat(producto.FLETE)
                                    },0)  }}</td>
                                  </tr>
                                  <tr v-if="sumatoriaIgv > 0">
                                    <td><b>IGV</b></td>
                                    <td class="table-danger">{{ sumatoriaIgv }}</td>
                                  </tr>
                                  <tr>
                                    <td>
                                      <b-button class="p-1 ml-1 mt-1" id="popover-opciones" variant="warning"><i class="fas fa-tags" ></i></b-button>
                                      <b-popover
                                          target="popover-opciones"
                                          placement="bottom" 
                                          custom-class="custom-popover"
                                          title="Opciones" triggers="click blur focus" auto-hide>
                                        <table class="table b-table border table-footer">
                                          <tbody>
                                            <tr>
                                              <td class="text-center ocultarMovil">Opciones</td>
                                            </tr>
                                            <tr v-if="puntosAplicados == 0" class="ocultarMovil">
                                              <td>
                                                <b-input-group prepend="S/">
                                                  <input
                                                    v-model="descuentoGeneral"
                                                    min="0"
                                                    value="0"
                                                    :disabled="bloquearInput ? true : false"
                                                    @input="(val) => { aplicarDescuentoGeneral(val.target.value) }"
                                                    type="number"
                                                    class="form-control"
                                                  />
                                                </b-input-group>
                                                <b-form-text>
                                                  Descuento general a toda la venta.
                                                </b-form-text>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td>
                                                <b-form-checkbox
                                                  :disabled="bloquearInput ? true : false || isNota || ventas[index].EsDelivery"
                                                  v-model="ventas[index].EsCredito"
                                                  :value="1"
                                                  :unchecked-value="0">
                                                  Venta a crédito
                                                </b-form-checkbox>
                                                <b-form-input 
                                                  :disabled="bloquearInput ? true : false || isNota || ventas[index].EsDelivery"
                                                  v-if="ventas[index].EsCredito==1"
                                                  v-model="ventas[index].FechaCredito"
                                                  type="date">
                                                </b-form-input>
                                                <b-form-checkbox 
                                                  :disabled="bloquearInput ? true : false || ventas[index].EsDelivery"
                                                  v-model="ventas[index].PorEntregar"
                                                  :value="1"
                                                  :unchecked-value="0">
                                                  Por entregar
                                                </b-form-checkbox>
                                                <b-form-checkbox 
                                                  :disabled="bloquearInput ? true : false || ventas[index].EsDelivery"
                                                  v-model="ventas[index].ProformaSinPrecio"
                                                  :value="1"
                                                  :unchecked-value="0">
                                                  Sin precios en proforma
                                                </b-form-checkbox>
                                              </td>
                                            </tr>
                                            <tr>
                                              <td>
                                                <b-input-group prepend="Proforma válido por:" append="días">
                                                  <b-form-input
                                                    :disabled="bloquearInput ? true : false"
                                                    v-model="ventas[index].diasValidosProf"
                                                    min="0"
                                                    value="0"
                                                    type="number"
                                                  ></b-form-input>
                                                </b-input-group>
                                              </td>
                                            </tr>
                                          </tbody>
                                        </table>
                                      </b-popover>  
                                    </td>
                                    <td><b>TOTAL</b></td>
                                    <td class="table-danger">{{ sumatoriaTotal }}</td>
                                  </tr>
                                  <tr  class="ocultarweb"><td colspan="3" v-if="ventas[index].productos.length>0" style="background: #ffffff !important; ">
                                    <b-form-checkbox
                                      id="checkbox-1"
                                      v-model="ventas[index].asignarDisabledProf"
                                      name="checkbox-1"
                                      :value="1"
                                      :unchecked-value="0"
                                    >
                                      Ocultar Proforma 
                                    </b-form-checkbox>
                                  
                                  </td>
                                </tr>
                                  <tr style="background: #ffffff !important; " >
                                      <td colspan="3">
                                        <b-form-checkbox
                                          v-model="mostrarDescripcion"
                                          :value="1"
                                          :unchecked-value="0"
                                        >
                                          Descripción
                                        </b-form-checkbox>
                                        <b-form-textarea
                                          placeholder="Descripción"
                                          v-model="ventas[index].descripcion"
                                          style="field-sizing: content;"
                                          v-if="mostrarDescripcion==1"
                                        ></b-form-textarea>
                                      </td>
                                    </tr>
                                  <tr>
                                    <td  colspan="3">
                                      <label>pagó con:</label>
                                      <b-form-input
                                        type="text"
                                        :disabled="bloquearInput?true:false"
                                        placeholder="Pagó con"
                                        v-model="ventas[index].pagocon"
                                        @input="asignarVuelto"
                                        
                                        ></b-form-input><br>
                                      <label>Vuelto:</label>
                                      <b>S/  {{ ventas[index].vuelto | redondear }}</b>
                                    </td>
                                  </tr>
                                  <tr>
                                     <td colspan="2"><button type="button" class="pos-total-gain-toggle" :aria-expanded="String(!!ventas[index].MostrarGananciaTotal)" :aria-label="ventas[index].MostrarGananciaTotal ? 'OCULTAR GANANCIA TOTAL' : 'MOSTRAR GANANCIA TOTAL'" title="GANANCIA TOTAL" @click="$set(ventas[index], 'MostrarGananciaTotal', !ventas[index].MostrarGananciaTotal)"><i :class="ventas[index].MostrarGananciaTotal ? 'fas fa-eye-slash' : 'fas fa-eye'"></i></button></td>
                                    <td><span v-if="ventas[index].MostrarGananciaTotal">{{ gananciaTotal(ventas[index].productos) }}</span></td>
                                  </tr>
                                </tbody>
                              </table>
                        </b-col>

                        <b-col cols="12" md="4" lg="3" class="text-center pos-tools-panel" align-h="center">
                    <!--  -->
                   
                      
                        <movimiento></movimiento>
                   
                        <pago-deuda></pago-deuda>
                        <pre-orden-lista 
                            v-show="!this.$route.query.idPreOrden"
                            :idAlmacen="ventas[index].almacen.IdAlmacen" @onSelectPreOrden="setCliente"
                            @onPreOrden="selectPreOrden"
                            @cargarDetalleProforma="cargarDetalleProforma"
                            :EsVenta="true"
                            ></pre-orden-lista>
                  
                        <!--<config-venta @onUpdateConfig="eliminarCliente"></config-venta>-->
                        <config-venta @onUpdateConfig="eliminarCliente" @onUpdateConfigComision="eliminarComisionista"></config-venta>
                        <b-button target="_blank" href="/views/proyectos.php?id=1" class="btnAction pos-btn-project">CREAR PROYECTOS</b-button>
                        <b-button target="_blank" href="/views/reg_proyectos.php" class="btnAction pos-btn-project-list">VER PROYECTOS</b-button>
  <ali-assistant class="ali-inline" ref="aliAssistant" :execute="aliExecute" :warehouse="Number((ventas[index] && ventas[index].almacen || {}).IdAlmacen) || 0" />
                </b-col>
               
            </b-row>
            
            <div>
            <div class="float-right">
                
            </div>
        </div>
        </b-form>
    </b-container>

    <b-modal v-model="cargando" hide-header hide-footer modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" >
      <div class="spinner"></div>
      <p><b>Obteniendo los datos, un momento por favor....</b></p>
    </b-modal>

     <b-modal v-model="touchModal" id="mdlTouch" hide-footer class="ingDesc" title="Codigo Barra" hide-header-close modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" >
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
  <b-modal v-model="modalQR" hide-footer hide-header modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" >
    <div class="text-center">
  <img v-if="qrUrl" :src="qrUrl" width="260" />
  <p class="mt-2">Monto: S/ {{ totalCart }}</p>
</div>
  </b-modal>
  <diario-recordatorios class="pos-inline-voice" />
</b-container>
</template>
<script>
import ProformaPresentacion from "@/components/ProformaPresentacion.vue";
import BuscadorProductosIA from "@/components/BuscadorProductosIA.vue";
import ClienteTelefonoRapido from "@/components/ClienteTelefonoRapido.vue";
import {isLaborService,laborSummary} from '@/utils/laborProducts';
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import VentaReimpresion from "@/views/VentaReimpresion.vue";
import {aliNormalizeProductQuery} from '@/utils/aliCommands';
import AliAssistant from "@/components/AliAssistant";
import DiarioRecordatorios from "@/components/DiarioRecordatorios.vue";
import aliCheckout from '@/utils/aliCheckout';
import salePrintDelivery from '@/utils/salePrintDelivery';
import ProductosCarrito from "@/components/ProductosCarritoNuevo";
import ClientesForm from "@/components/ClientesForm";
import ConfigVenta from "@/components/ConfigVenta";
import PagoDeuda from "@/components/PagoDeuda";
import PreOrdenLista from "@/components/PreOrdenLista.vue";
import Proformas from "@/components/ProformaLista.vue";
import PedidosWeb from "@/components/PedidosWeb.vue";
import DeliveryModal from "@/components/DeliveryModal.vue";
import Movimiento from "@/components/Movimiento";
import QRpagoYape from "@/components/QRpagoYape";
import Swal from "sweetalert2";
export default {
  mixins: [aliCheckout, salePrintDelivery],
  beforeMount() {
    this.initForm();
  },
  mounted() {
    // this.getNumeroVenta()
    this.getVendedor();
    //setInterval(this.updateTime, 1000)

    this.loadComisionista();
    // this.escucharPedidos();
    this.getEstadoSistema();
    this.ObtenerLstOfertas();
    this.conectarSocket();
    this.setupSocketListeners();
    this.getEstadoSistema();
    if (this.$route.query.idCliente) {
      this.$nextTick(() => {
        this.setCliente(this.$route.query.idCliente);
      });
    } else {
      this.loadClienteVarios();
    }
  },
  data() {
    return {
      productExplorerOpen:false,quotePhoneReady:false,quotePresentationOpen:false,quotePresentation:{tipo:"productos",alcance:"",entregables:"",condiciones:""},
      showIssuedDocuments: false,
      pedidosWebPendientes: 0,
      modalQR: false,
      cantidadInput: "",
      touchModal: false,
      cargando: false,
      ventas: [],
      index: 0,
      preVenta: {},
      vuelto: 0,
      pagocon: 0,
      mostrarDescripcion: 0,
      fieldsDocRef: [
        { key: "idDocVenta" },
        { key: "IdProductos" },
        { key: "NroDocRef", label: "Nro Doc. Referencia" },
        { key: "Total", label: "Monto" },
        { key: "action", label: "" }
      ],
      DocRefOfertas: [],
      idMetodoPago: 0,
      IdOferta: "",
      lstOfertas: [],
      isNota: false,
      dni: "",
      adelanto: 0,
      gasto: 0,
      newDniRuc: "",
      ultimaVenta: "",
      inProgress: false,
      API: config.API,
      APIADMIN: "http://204.48.21.196:5000",
      puntosAplicados: 0,
      descuentoPuntos: {},
      descuentoGeneral: 0,
      tieneIgv: false,
      venta: { IdProforma: null, descripcion: "" },
      bloquearInput: false,
      diasValidosProf: 1,
      adelantosPreOrden: [],
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
      carrito: {
        fields: [
          {
            key: "manoobra",
            label: "M.O.",
            class: "text-center ocultarMovil"
          },
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center"
          },
          {
            key: "CodigoBarra",
            label: "C. Barra",
            class: "text-center pos-cart-barcode"
          },
          { key: "Producto", class: "Producto", class: "clsProducto" },
          { key: "Descripcion", label: "Descripción" },
          { key: "Series", label: "Serie" },
          { key: "eventPres", label: "" },
          { key: "cantxPrecio", label: "CantxP", variant: "warning" },
          // { key: 'FechaAlquiler', label: 'Fecha Inicio' },
          // { key: 'FechaAlquilerFin', label: 'Fecha Fin'},
          /*{ key: 'ProductoMedicion', label: 'Medición' },*/
          { key: "cantidad", label: "Cant" },
          { key: "precio", label: "PU" },
          // { key: "utilidad", label: "Porcentaje Utilidad %" },
          {
            key: "subtotal",
            label: "SubTotal",
            variant: "success",
            class: "text-center ocultarMovil"
          },
          { key: "descuento", label: "Desc", class: "ocultarMovil" },
          {
            key: "total",
            label: "Total",
            variant: "danger",
            class: "text-center"
          },
          { key: "acciones", label: "" }
        ]
      },
      pagosPendientes: 0,
      fechaUltPago: "",
      qrUrl: "",
      listeners: {
        nuevoPedido: null,
        connect: null,
        disconnect: null
      }
    };
  },
  watch: {
    index() {
      this.$nextTick(() => {
        setTimeout(() => {
          let carrito = this.$refs[`carrito${this.index}`];
          if (Array.isArray(carrito)) {
            carrito = carrito[0];
          }
          if (carrito && typeof carrito.focusBarcode === "function") {
            carrito.focusBarcode();
          }
        }, 80);
      });
    },
    totalCart(newVal) {
      this.ventas[this.index].totalCart = this.totalCart;
      this.ventas[this.index].pago.efectivo.monto = parseFloat(
        newVal -
          (parseFloat(this.ventas[this.index].pago.visa.monto) +
            parseFloat(this.ventas[this.index].pago.mastercard.monto) +
            parseFloat(this.ventas[this.index].pago.yape.monto))
      ).toFixed(2);
      this.asignarVuelto();
    },
    asignarDisabledProfComputed(newVal) {
      let prods = [...this.ventas[this.index].productos];
      prods = prods.map(v => {
        return { ...v, disabledProf: newVal };
      });
      this.ventas[this.index].productos = [];
      this.ventas[this.index].productos = prods;
    }
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      totalCart: state => state.cart.totalCart
    }),
    asignarDisabledProfComputed() {
      if (
        this.ventas &&
        this.ventas[this.index] &&
        typeof this.ventas[this.index].asignarDisabledProf !== "undefined"
      ) {
        return this.ventas[this.index].asignarDisabledProf;
      }
      return null;
    },
    manoDeObraBruta(){return laborSummary(this.ventas[this.index]?this.ventas[this.index].productos:this.productos).gross;},
    manoDeObraNeta(){return laborSummary(this.ventas[this.index]?this.ventas[this.index].productos:this.productos).net;},
    sumatoriaSubTotal() {
      let sumatoria = 0;
      if (this.igvTotal) {
        // sumatoria = this.sumatoriaTotal - this.sumatoriaIgv
        sumatoria = this.sumatoriaTotal / 1.18;
      } else {
        this.ventas[this.index].productos.map(value => {
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
      this.ventas[this.index].productos.map(value => {
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
  beforeDestroy() {
    this.removeSocketListeners();
    this.$socket.disconnect();
  },
  methods: {
    aliSnapshot() {
      const sale=this.ventas[this.index];
      return JSON.stringify({index:this.index,sale});
    },
    async aliExecute(action) {
      if(action.type==='cancelSearch'){this._aliSearchSeq=(this._aliSearchSeq||0)+1;if(this._aliSearchCancel)this._aliSearchCancel('superseded');return {silent:true,message:''};}
      const sale=this.ventas[this.index];
      if(!sale) throw new Error('Abre una venta primero.');
      const ref=this.$refs['carrito'+this.index], cart=Array.isArray(ref)?ref[0]:ref;
      const total=Number(sale.totalCart)||0;
      if(action.type==='greet')return {message:'Buen día, ¿en qué te puedo ayudar?'};
      if(action.type==='help')return {message:'Di ALI ventas para codigo de barras, dicta el codigo y di buscar. Tipo de venta boleta elec, factura elec o nota de venta. ALI clientes, DNI o RUC y buscar. Metodo de pago Yape, efectivo, tarjeta o transferencia. ALIPAGO prepara el resumen; confirmado registra despues de revisarlo.'};
      if(action.type==='cancel'&&action.reset){if(this.inProgress||this.aliCheckoutSubmitting||this.aliCheckoutPreparing)throw Error('Operacion en curso.');await this.aliCancelCheckout(action.csrf);const activeIndex=this.index;const warehouse=sale.almacen,point=sale.puntoVenta;this.initForm();const fresh=this.ventas.pop();fresh.almacen=warehouse;fresh.puntoVenta=point;fresh.totalCart=0;fresh.vendedor=sale.vendedor;this.$set(this.ventas,activeIndex,fresh);this.index=activeIndex;this.venta=fresh;this.newDniRuc='';this.dni='';this._aliDrafting=false;this.limpiarCart();this.actualizarTotal(0);await this.$nextTick();const defaults=await Promise.all([this.axios.get(this.API+'clientes/00000000'),this.axios.get(this.API+'comisionista/0000')].map(request=>request.then(value=>({status:'fulfilled',value})).catch(()=>({status:'rejected'}))));if(this.ventas[this.index]!==fresh)throw Error('La ventana de venta cambio.');if(defaults[0].status==='fulfilled'&&defaults[0].value.data.IdCliente)await this.setCliente(defaults[0].value.data);if(defaults[1].status==='fulfilled'&&defaults[1].value.data.IdCliente)this.setComisionista(defaults[1].value.data);await this.getNumeroVenta();await this.$nextTick();const result=await this.aliExecute({type:'focus',field:'barcode'});return Object.assign({},result,{silent:false,fastVoice:true,message:'Venta limpia.',results:[],pending:''});}
      if(action.type==='cancel'){await this.aliCancelCheckout(action.csrf);return {pending:'',message:'Operación cancelada. La venta sigue abierta.'};}
      if(action.type==='total')return {message:'El total es S/ '+total.toFixed(2)};
      if(action.type==='methods')return {field:'payment',buffer:'',message:'Efectivo, Yape, tarjeta o transferencia.',fastVoice:true};
      if(action.type==='method'){
        if(this.inProgress||this.bloquearInput)throw new Error('La venta está bloqueada.');
        this.venta=sale;this.cargarMonto(action.key);this._aliPayment=null;
        return {field:'payment',buffer:'',pending:'',message:action.label+' seleccionado.'};
      }
      if(['selectCode','add','accept'].includes(action.type)){
        if(this.inProgress||this.bloquearInput)throw new Error('La venta está bloqueada.');
        const autocomplete=cart&&cart.$refs.autocompleteProducto;
        const visible=autocomplete&&autocomplete.showList&&Array.isArray(autocomplete.json)?autocomplete.json:[];
        let product=action.product;
        if(action.type==='selectCode'){
          if(!visible.length)throw new Error('Abre la lista en Agregar producto para seleccionar por terminación de código.');
          const exact=visible.filter(p=>String(p.CodigoBarra||'')===action.suffix);
          const matches=exact.length?exact:visible.filter(p=>String(p.CodigoBarra||'').endsWith(action.suffix));
          if(matches.length>1)throw new Error('Hay '+matches.length+' productos con esa terminación. Dime el código completo.');
          if(!matches.length)throw new Error('No hay un producto terminado en '+action.suffix+' en la lista visible.');
          product=matches[0];
        }else if(visible.length)product=visible[action.type==='accept'?(Number(autocomplete.focusList)||0):action.index-1];
        if(!product)throw new Error('Selecciona un producto de la lista actual.');
        if(!cart)throw new Error('No se encontró el carrito.');
        if(autocomplete){autocomplete.showList=false;autocomplete.setValue(product.Producto)}
        await cart.setProducto(Object.assign({},product));
        return {field:'search',buffer:'',pending:'',message:'Seleccioné '+product.Producto+', código '+product.CodigoBarra+'. Revisa la cantidad.'};
      }
      if(['barcode','barcodeDraft','customer','documentType','focus'].includes(action.type)){
        if(this.inProgress||this.bloquearInput)throw new Error('La venta está bloqueada.');
        if(action.type==='focus'){
          if(action.field==='documentType'){const refs=this.$refs['inputTipoVenta'+this.index],component=Array.isArray(refs)?refs[0]:refs;const field=component&&component.$el.querySelector('input');if(field){field.scrollIntoView({block:'center'});field.focus()}return {field:'documentType',buffer:'',message:'Tipo de venta.',silent:true};}
          if(action.field==='customer'){this.$set(sale,'MostrarClienteDatos',true);await this.$nextTick();const refs=this.$refs['inputCliente'+this.index],component=Array.isArray(refs)?refs[0]:refs;const field=component&&component.$el.querySelector('input');if(!field)throw Error('No se encontro el buscador de clientes.');field.dataset.aliCustomer='true';field.inputMode='numeric';field.setAttribute('autocomplete','off');const documentNumber=action.number!==undefined?action.number:'';component.setValue(documentNumber);component.json=[];component.showList=false;this.newDniRuc=documentNumber;this.dni=documentNumber;field.scrollIntoView({block:'center'});field.focus();return {field:'customer',buffer:documentNumber,message:action.number!==undefined?'Documento escrito.':'Clientes.',silent:true};}
          const input=cart&&(action.field==='barcode'?cart.$refs.barcodeInput:cart.$refs.autocompleteProducto.$el.querySelector('input'));
          if(action.field==='barcode'){cart.loadCode='';cart.clearProductSearch();}
          if(input){input.scrollIntoView({block:'center'});input.focus()}
          return {field:action.field,buffer:'',message:action.field==='barcode'?'Codigo de barras.':'Productos.',silent:true};
        }
        if(action.type==='barcodeDraft'){
          if(!cart||!/^\d{0,40}$/.test(action.number))throw new Error('Código inválido.');
          cart.loadCode=action.number;cart.focusBarcode();await this.$nextTick();
          this._aliDrafting=true;
          return {field:'barcode',buffer:action.number,silent:true,message:action.number?'Codigo escrito.':'Codigo vacio.'};
        }
        if(action.type==='barcode'){
          if(!/^\d{1,40}$/.test(action.number))throw new Error('Dime el código con números o dígito por dígito.');
          if(!cart)throw new Error('No se encontró el carrito.');
          cart.loadCode=action.number;cart.focusBarcode();
          let found;try{found=await cart.procesarCodigoBarra(action.number);}finally{cart.loadCode='';}
          if(found===false)return {field:'barcode',buffer:'',message:'Codigo no encontrado.',fastVoice:true};
          this._aliDrafting=false;
          return {field:'barcode',buffer:'',message:'Producto agregado.'};
        }
        if(action.type==='documentType'){
          const response=await this.axios.get(this.API+'ventas/tipos',{params:{q:action.label}});
          const types=Array.isArray(response.data)?response.data:[];const type=action.code==='note'?types.find(t=>/^nota(?: de)?(?: venta)?(?: electronica)?\.?$/i.test(String(t.TipoDoc||'').trim())):types.find(t=>String(t.CodSunat)===action.code);
          if(!type)throw new Error('Ese tipo de comprobante no está disponible.');
          await this.setTipoVenta(type);
          const refs=this.$refs['inputTipoVenta'+this.index],input=Array.isArray(refs)?refs[0]:refs;
          if(input)input.setValue(type.TipoDoc);
          return {message:type.TipoDoc+'.',fastVoice:true};
        }
        if(action.kind==='auto'){const refs=this.$refs['inputCliente'+this.index],component=Array.isArray(refs)?refs[0]:refs;const field=component&&component.$el.querySelector('input');action.number=String(field&&field.value||'').replace(/\s/g,'');action.kind=action.number.length===11?'ruc':'dni';}
        const expected=action.kind==='ruc'?11:8;
        if(!new RegExp('^\\d{'+expected+'}$').test(action.number))throw new Error(action.kind.toUpperCase()+' debe tener '+expected+' dígitos.');
        this.$set(sale,'MostrarClienteDatos',true);await this.$nextTick();
        const refs=this.$refs['inputCliente'+this.index],input=Array.isArray(refs)?refs[0]:refs;
        if(input){const field=input.$el.querySelector('input');if(field){field.scrollIntoView({block:'center'});field.focus()}input.setValue(action.number)}
        this.newDniRuc=action.number;this.dni=action.number;
        const response=await this.axios.get(this.API+'cliente',{params:{dni:action.number}});
        let client=response.data;
        if(!client||!client.IdCliente)throw new Error('El cliente no está registrado. Revisa el formulario de clientes del POS antes de guardarlo.');
        const latest=input&&input.$el.querySelector('input');if(this.ventas[this.index]!==sale||latest&&latest.value.trim()!==action.number)return {superseded:true,silent:true};
        await this.setCliente(client);
        return {field:'customer',buffer:'',message:'Cliente seleccionado.',fastVoice:true};
      }
      if(action.type==='search'||action.type==='learn'){
        const warehouse=sale.almacen&&sale.almacen.IdAlmacen;
        if(!warehouse)throw new Error('Selecciona la tienda antes de buscar.');
        const query=aliNormalizeProductQuery(action.query);
        if(!query)throw new Error('Indica qué producto buscas.');
        const seq=this._aliSearchSeq=(this._aliSearchSeq||0)+1;if(this._aliSearchCancel)this._aliSearchCancel('superseded');
        const source=this.axios.CancelToken.source();this._aliSearchCancel=source.cancel;
        let response;try{response=await this.axios(this.API+'productos/stock/'+warehouse,{params:{q:query,limit:25,sortBy:'stock',sortDesc:1},cancelToken:source.token});}catch(error){if(this.axios.isCancel(error)||seq!==this._aliSearchSeq)return {superseded:true,silent:true};throw error;}
        if(seq!==this._aliSearchSeq)return {superseded:true,silent:true};this._aliSearchCancel=null;
        const rows=Array.isArray(response.data)?response.data:(response.data.data||[]);
        const results=rows.slice(0,25);
        if(action.type==='search'&&cart){cart.query=query;cart.search(rows);}
        return {field:'search',buffer:'',results,product:action.type==='learn'&&results.length===1?results[0]:null,message:results.length?'Encontré '+results.length+' productos.':'No encontré productos con ese nombre.'};
      }
      if(action.type==='pay')return this.aliPrepareCheckout(action.csrf);
      if(action.type==='confirm')return this.aliConfirmCheckout(action.csrf);
      throw new Error('Comando no disponible.');
    },
    distribuirPago(tipo, monto) {
      if (tipo === 'efectivo') return;
      const venta = this.ventas[this.index];
      const otros = ['visa', 'mastercard', 'yape'].reduce((sum, key) => sum + (key === tipo ? Number(monto) || 0 : Number(venta.pago[key].monto) || 0), 0);
      venta.pago.efectivo.monto = this.$options.filters.redondear(Math.max(0, (Number(venta.totalCart) || 0) - otros));
    },
    mostrarAccionesMovil() {
      const panel = this.$el.querySelector('.pos-checkout-area');
      if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },
    async cargarPedidoWeb(data) {
      if (!this.ventas[this.index].almacen || !this.ventas[this.index].almacen.IdAlmacen) throw new Error('Seleccione un almacén.');
      try {
        await this.setProforma(data);
        await this.$nextTick();
        this.$set(this.ventas[this.index], 'IdProforma', data.IdProforma);
        this.venta.IdProforma = data.IdProforma;
      } finally { this.cargando = false; }
    },
    gananciaTotal(productos) {
      return (productos || []).reduce((sum, prod) => sum + ((Number(prod.precio) || 0) - (Number(prod.PrecioCosto) || 0)) * (Number(prod.cantidad) || 0), 0).toFixed(2);
    },

    ...mapActions({
      aplicarDescuento: "aplicarDescuentoGeneral",
      limpiarCart: "limpiarCarrito",
      // obtenerStok: "obtenerStock",
      actualizarPrecioConvenio: "actualizarPrecioConvenio",
      add: "agregarProducto",
      actualizarTotal: "actualizarTotalCart"
    }),
    iconoTipoDocumento(tipoVenta) {
      const codigo = tipoVenta && String(tipoVenta.CodSunat || "");
      if (codigo === "01") return "fas fa-file-invoice";
      if (codigo === "07") return "fas fa-undo-alt";
      if (codigo === "08") return "fas fa-plus-circle";
      if (codigo === "99") return "fas fa-shopping-bag";
      return "fas fa-receipt";
    },
    toggleClienteDatos(tab) {
      this.$set(tab, "MostrarClienteDatos", tab.MostrarClienteDatos === false);
    },
    toggleGarantia(tab) {
      const mostrar = !Boolean(tab.MostrarGarantia);
      this.$set(tab, "MostrarGarantia", mostrar);
      if (!mostrar) {
        this.$set(tab, "Garantia", 0);
      }
    },
    toggleOrganizacion(tab) {
      const habilitada = !Boolean(tab.EsOrganizacion);
      this.$set(tab, "EsOrganizacion", habilitada);
      if (!habilitada) {
        this.$set(tab, "nombreOrganizacion", "");
      }
    },
    async generarQR() {
      this.venta = {};
      this.venta = this.ventas[this.index];
      // evt.preventDefault();

      this.inProgress = true;
      let totalPagos = 0;
      totalPagos += isNaN(parseFloat(this.venta.pago.efectivo.monto))
        ? 0
        : parseFloat(this.venta.pago.efectivo.monto);
      totalPagos += isNaN(parseFloat(this.venta.pago.visa.monto))
        ? 0
        : parseFloat(this.venta.pago.visa.monto);
      totalPagos += isNaN(parseFloat(this.venta.pago.mastercard.monto))
        ? 0
        : parseFloat(this.venta.pago.mastercard.monto);
      totalPagos += isNaN(parseFloat(this.venta.pago.yape.monto))
        ? 0
        : parseFloat(this.venta.pago.yape.monto);

      if (
        (isNaN(parseFloat(this.venta.pago.efectivo.monto))
          ? 0
          : parseFloat(this.venta.pago.efectivo.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de pago efectivo no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.venta.pago.visa.monto))
          ? 0
          : parseFloat(this.venta.pago.visa.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de pago Tarjeta no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.venta.pago.mastercard.monto))
          ? 0
          : parseFloat(this.venta.pago.mastercard.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show(
          "El metodo de pago Transferencia no puede ser negativo"
        );
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.venta.pago.yape.monto))
          ? 0
          : parseFloat(this.venta.pago.yape.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show(
          "El metodo de pago YAPE / PLIN no puede ser negativo"
        );
        this.inProgress = false;
        return;
      }

      if (!this.venta.EsCredito) {
        if (
          parseFloat(this.venta.totalCart).toFixed(2) !=
          parseFloat(totalPagos).toFixed(2)
        ) {
          this.$toasted.show("Los pagos no coinciden con la venta");
          this.inProgress = false;
          return;
        }
      }

      if (!this.venta.cliente.DniRuc) {
        this.$toasted.show("Debe seleccionar un cliente.");
        this.inProgress = false;
        return;
      }

      var esNumerico = Number.isInteger(parseInt(this.venta.cliente.DniRuc));
      if (!esNumerico) {
        this.$toasted.show("El RUC debe ser numérico, editar cliente.");
        this.inProgress = false;
        return;
      }

      var esEmpresa = this.venta.cliente.DniRuc.length > 9 ? true : false;
      if (!esEmpresa && this.venta.tipoVenta.CodSunat == "01") {
        this.$toasted.show(
          "Una factura debe ser emitida para una empresa, no un usuario común."
        );
        this.inProgress = false;
        return;
      }
      // validar serie
      let prods = this.venta.productos;
      // let val = !prods.some(v => v.Series.length > 0 && !!v.IdProductoSerie);

      // if (val) {
      //   this.$toasted.show(
      //     "Debe Seleccionar la serie del produto. Verifique."
      //   );
      //   this.inProgress = false;
      //   return;
      // }
      //Nota de Creadito
      if (
        this.venta.tipoVenta.CodSunat == "07" ||
        this.venta.tipoVenta.CodSunat == "08"
      ) {
        if (
          !this.venta.CodSunatModifica ||
          !this.venta.NroComprobanteModifica
        ) {
          this.$toasted.show("Debe seleccionar un Documento de Referencia...");
          this.inProgress = false;
          return;
        }
        if (!this.venta.NotaIdMotivo) {
          this.$toasted.show("Debe seleccionar un Motivo");
          this.inProgress = false;
          return;
        }
        this.venta.NotaDescMotivo = this.itemsMotivoNota.filter(
          item => item.value == this.venta.NotaIdMotivo
        )[0].text;
      }

      if (!this.venta.productos.length) {
        this.$toasted.show("No hay productos para vender");
        this.inProgress = false;
        return;
      }

      // NO PERMITE CANTIDAD Y PRECIOS 0
      if(laborSummary(this.venta.productos).overBudget){this.$toasted.error('Los componentes superan la mano de obra bruta. Revisa cantidades e importes.');this.inProgress=false;return;}
      let sinStock = 0;
      let totalCero = 0;
      let menosUtilidad = 0;
      this.venta.productos.forEach(pro => {
        if (
          pro.CodigoBarra !== "COSTODELIVERY" &&
          !isLaborService(pro) &&
          Number(pro.ControlaStock)!==0 && (pro.stock <= 0 || parseFloat(pro.cantidad) > pro.stock) &&
          pro.EsPadre == 0
        ) {
          sinStock += 1;
        }

        if (parseFloat(pro.total) <= 0) {
          totalCero += 1;
        }

        /*if ((!(1/(pro.PorcentajeUtilidad*0)===1/0) || (pro.PorcentajeUtilidad=='')) && (pro.CodigoBarra !== 'COSTODELIVERY') && (pro.CodigoBarra !== 'MANODEOBRA')) {
                    menosUtilidad += 1
                }*/

        if (
          parseFloat(pro.precio) - pro.PrecioCosto < 0 &&
          pro.CodigoBarra !== "COSTODELIVERY" &&
          !isLaborService(pro)
        ) {
          menosUtilidad += 1;
        }
      });

      if (this.venta.tipoVenta.CodSunat != "99") {
        if (menosUtilidad >= 1 && this.venta.tipoVenta.CodSunat != "07") {
          this.$toasted.error(
            "No se puede vender un producto con ganancia negativa"
          );
          this.inProgress = false;
          return;
        }
      }
      if (sinStock >= 1 && this.venta.tipoVenta.CodSunat != "07") {
        this.$toasted.error("No se puede vender un producto sin stock");
        this.inProgress = false;
        return;
      }
      if (this.venta.tipoVenta.CodSunat != "99") {
        if (totalCero >= 1) {
          this.$toasted.error("No se puede enviar un producto en S/. 0.00");
          this.inProgress = false;
          return;
        }

        if (this.totalCart <= 0) {
          this.$toasted.show("La venta en S/. 0.00 no está permitido");
          this.inProgress = false;
          return;
        }
      }

      if (!this.validarDocRefOfetar()) {
        this.inProgress = false;
        return;
      }

      let IdOfertas = this.generarOfertas();
      this.modalQR = true;
      this.qrUrl = null;
      const res = await this.axios.post(this.API + "/openpay/qr", {
        monto: this.ventas[this.index].totalCart,
        pedido:
          this.ventas[this.index].Serie + "-" + this.ventas[this.index].Numero
      });

      // this.qrUrl = res.data.payment_method.barcode_url;
    },
    abrirModal() {
      this.touchModal = true;
    },
    addNumber(number) {
      this.cantidadInput = this.cantidadInput + number;
    },
    deleteNumber() {
      this.cantidadInput = this.cantidadInput.toString().slice(0, -1);
    },
    buscarCodigoBarra() {
      let ref = `inputCliente${this.index}`;
      const comp = this.$refs[ref][0];
      comp.setValue(this.cantidadInput);
      this.$nextTick(() => {
        const inputElement = comp.$el.querySelector("input");
        if (inputElement) {
          inputElement.dispatchEvent(new Event("input", { bubbles: true }));
        }
      });
      this.touchModal = false;
    },
    async cargarDetalleProforma(det) {
      console.log(this.ventas[this.index].almacen.IdAlmacen);
      this.$nextTick(async () => {
        let detalle = det.map(item => ({
          ...item,
          setPrecio: true,
          cantidad: item.Cantidad,
          precio: item.Precio,
          EsManoDeObra: item.EsManoDeObra
        }));
        let IdAlmacen = this.ventas[this.index].almacen.IdAlmacen;
        for (let i = 0; i < detalle.length; i++) {
          const res = await this.axios(
            `${this.API}/productos/stock/${IdAlmacen}`,
            {
              params: { idProductos: [detalle[i].IdProducto] }
            }
          );

          detalle[i].stock = res.data[0].stock ? res.data[0].stock : 0;
        }

        this.ventas[this.index].productos = detalle;
        this.cargarMonto("efectivo");
      });
    },
    eliminarVentana() {
      if (this.ventas.length === 1) {
        window.location.reload();
        return;
      }

      this.ventas.splice(this.index, 1);
      this.index = this.index - 1;
      return;
    },
    eliminarProducto(index) {
      this.ventas[this.index].productos.splice(index, 1);
      // console.log("index", index);
    },
    setProducto(evt) {
      let prods = this.ventas[this.index].productos;
      if (prods.some(v => v.IdProducto == evt.IdProducto)) {
        let i = this.ventas[this.index].productos.findIndex(
          v => v.IdProducto == evt.IdProducto
        );
        let producto = this.ventas[this.index].productos[i];
        producto.precio = evt.PrecioContado;
        if (producto.estadoPxP === evt.estadoPxP) {
          if (producto.estadoPxP) {
            producto.cantidad = parseFloat(producto.cantidad) + 1;
          } else {
            producto.cantxPrecio = parseFloat(producto.cantxPrecio) + 1;
          }
        }
        Vue.set(prods, this.index, evt);
        // console.log(index);
      } else {
        this.ventas[this.index].productos.unshift({
          ...evt,
          precio: evt.PrecioContado,
          cantidad: 1,
          cantxPrecio: 1
        });
      }
    },
    async setProforma(e) {
      console.log("aaaa");
      let productos = e.productos.map(v => {
        return { ...v, cantidadPres: 1 };
      });
      this.cargando = true;
      for (let index = 0; index < productos.length; index++) {
        const producto = productos[index];
        let series = await this.obtenerSeriePorProducto(producto.IdProducto);
        let p = await this.axios(
          `${this.API}/productos/stock/${
            this.ventas[this.index].almacen.IdAlmacen
          }`,
          {
            params: {
              idProductos: [producto.IdProducto]
            }
          }
        ).then(res => {
          return res.data[0];
        });
        if (!p) throw new Error("Producto no disponible en el almac?n seleccionado.");
        producto.stock = p.stock;
        producto.Series = series;
        producto.PrecioCosto = p.PrecioCosto;
      }
      let ven = { ...this.preVenta, productos: productos, IdProforma: e.IdProforma,
        descripcion: e.Descripcion || '', diasValidosProf: Number(e.DiasValidos || 7) };
      this.diasValidosProf = ven.diasValidosProf;
      this.ventas.push(ven);
      this.$nextTick(() => {
        this.cargando = false;
        this.index = this.ventas.length - 1;
        this.getVendedor();
        let v = this.ventas[this.index];

        Promise.all([
          this.cargarMonto("efectivo"),
          this.loadAlmacen(v.almacen.IdAlmacen),
          this.loadPuntoVenta(v.puntoVenta.IdDocVentaPuntoVenta),
          this.loadComisionista(),
          this.setCliente(e),
          this.actualizarTotalCart()
        ]);
      });
    },
    actualizarTotalCart() {
      let productos = this.ventas[this.index].productos;

      this.ventas[this.index].totalCart = productos.reduce((sum, producto) => {
        return sum + parseFloat(producto.cantidad * producto.precio);
      }, 0);
    },
    asignarVuelto() {
      this.ventas[this.index].vuelto =
        this.ventas[this.index].pagocon - this.ventas[this.index].totalCart;
    },
    cargarMonto(tipo) {
      if (this.venta.pago && this.venta.pago.hasOwnProperty(tipo)) {
        Object.keys(this.venta.pago).forEach((key, index) => {
          this.venta.pago[key].monto = key === tipo ? this.totalCart : 0;
          if (key === tipo) {
            this.idMetodoPago = index + 1;
          }
        });

        // console.log("Montos actualizados:", this.venta.pago);
      } else {
        console.error(`El tipo de pago "${tipo}" no existe en this.venta.pago`);
      }
    },
    async ObtenerLstOfertas() {
      await this.axios(`${this.API}ofertas/listadoimpresion`).then(res => {
        if (res) {
          this.lstOfertas = res.data;
        }
      });
    },
    eliminarDocRef(index) {
      this.DocRefOfertas.splice(index, 1);
    },
    validarDocRefOfetar() {
      if (this.venta.tipoVenta.CodSunat == "99") {
        let TotalMontoOfertas = this.DocRefOfertas.reduce((sum, oferta) => {
          return sum + parseFloat(oferta.Total);
        }, 0);

        if (this.DocRefOfertas.length == 0) {
          return false;
        }

        let oferta = this.lstOfertas.filter(
          v => v.IdOferta == this.IdOferta
        )[0];
        if (oferta.ProductoFlag == 1) {
          let IdProducto = oferta.IdProducto;
          let IdProductos = this.DocRefOfertas.map(v => {
            return v.IdProductos;
          });

          this.$toasted.show(
            "Los documentos de referencia no cumple con los requisitos. Producto no presente en el comprobante"
          );
          return IdProductos.includes(IdProducto);
        }

        if (oferta.MontoFlag == 1) {
          if (oferta.Monto > TotalMontoOfertas) {
            this.$toasted.show(
              "Los documentos de referencia no cumple con los requisitos. La suma de los montos es menor a la promoción"
            );

            return false;
          }
        }
        return true;
      }
      return true;
    },

    mostrarSeccionOferta() {
      if (this.venta.tipoVenta.CodSunat == "99") {
        return true;
      }
      return false;
    },
    async obtenerProductoOferta() {
      this.$store.state.cart.productos = [];
      this.$nextTick(async () => {
        let query = this.lstOfertas.filter(v => v.IdOferta == this.IdOferta)[0];

        let stock = await this.axios(
          `${this.API}productos/stock?idProductos[]=${
            query.IdProductoAsociado
          }&idAlmacen=${this.venta.almacen.IdAlmacen}`
        ).then(res => {
          return res.data;
        });

        this.axios(
          `${this.API}productos/stock/2?q=${
            query.Producto
          }&sortBy=stock&sortDesc=1&limit=25`
        ).then(res => {
          if (res.data) {
            let producto = res.data[0];
            producto.cantidad = 1;
            producto.precio = 0;
            producto.disabled = true;
            producto.stock = stock[0].stock;
            this.productos.push(producto);
          }
        });
      });

      setTimeout(() => {
        const inputElement = this.$refs["inputDocRefOrden"].$el.querySelector(
          "input"
        );
        inputElement.focus();
        this.focusAutocomplete("inputDocRefOrden");
      }, 1000);
    },
    getEstadoSistema() {
      let hostUrl = sessionStorage.getItem("HostUrl");
      if (hostUrl != null) {
        this.axios
          .get(this.APIADMIN + "/api/sistema", {
            params: { Url: hostUrl }
          })
          .then(res => {
            if (res.data.success == true) {
              let info = res.data.data;
              this.pagosPendientes = info.length;
              this.fechaUltPago =
                info.slice(-1)[0].FechaVencimientoFormat || "";
            } else {
              this.pagosPendientes = 0;
            }
          })
          .catch(err => {
            this.pagosPendientes = 0;
            // console.log("Error de conexión al admin");
          });
      } else {
        this.pagosPendientes = 0;
      }
    },
    loadClienteVarios() {
      this.axios.get(this.API + "clientes/00000000").then(res => {
        if (res.data.IdCliente) {
          this.setCliente(res.data);
        }
      });
    },
    loadComisionista() {
      this.axios.get(this.API + "comisionista/0000").then(res => {
        if (res.data.IdCliente) {
          this.setComisionista(res.data);
        }
      });
    },
    prevent(evt) {
      if (evt.target.tagName !== "TEXTAREA") {
        evt.preventDefault();
      }
    },
    selectPreOrden(orden) {
      // console.log(orden);
      // this.venta.IdPreOrden = this.venta.IdPreOrden || []
      // if (Array.isArray(this.venta.IdPreOrden) ) {
      this.ventas[this.index].IdPreOrden.push(orden);
      //}

      // obtener total adelantos
      this.axios
        .get(this.API + "preorden/cajaybanco", {
          params: {
            IdPreOrden: orden.IdPreOrden,
            TipoCajaBanco: 0
          }
        })
        .then(res => {
          this.ventas[this.index].adelantosPreOrden = res.data;
          let suma = 0;
          res.data.map(ad => {
            if (ad.Tipo == 0) {
              suma += ad.Importe;
            }
          });
          this.ventas[this.index].adelanto = suma;
        });
    },
    loadFirstAlmacen() {
      this.axios.get(this.API + "almacenes/primero").then(res => {
        this.$refs.inputAlmacen.setValue(res.data.Almacen);
        this.ventas[this.index].almacen = res.data;
      });
    },
    loadClienteBarcode(e) {
      e.preventDefault();
      let dni = this.dni;

      if (dni) {
        this.setCliente(false, dni);
      }
    },
    onAliClientInput(value,index){this.newDniRuc=value;const refs=this.$refs['inputCliente'+index],component=Array.isArray(refs)?refs[0]:refs;const field=component&&component.$el.querySelector('input');if(field&&field.dataset.aliCustomer==='true'&&/^\d*$/.test(value))component.showList=false;},
    focusAutocomplete(ref, event) {
      const refs = this.$refs[ref] || [];
      const componentes = Array.isArray(refs) ? refs : [refs];
      const componenteActivo = componentes.find(componente => {
        return event && componente && componente.$refs && componente.$refs.input === event.target;
      });
      const componenteVisible = componentes.find(componente => {
        return componente && componente.$el && componente.$el.offsetParent !== null;
      });
      const componente = componenteActivo || componenteVisible || componentes[0];
      if (!componente) return;
      const input = componente.$refs && componente.$refs.input
        ? componente.$refs.input
        : componente.$el.querySelector("input");
      if (input) {
        input.focus();
        input.select();
      }
      if(input&&input.dataset.aliCustomer==='true'){componente.showList=false;return;}
      componente.handleDoubleClick();
    },
    anadirVenta() {
      this.ventas.push({ ...this.preVenta, productos: [] });
      this.$nextTick(() => {
        this.index = this.ventas.length - 1;
        this.getVendedor();
        this.loadClienteVarios();
        this.loadComisionista();
      });
    },
    initForm() {
      this.venta = {
        asignarDisabledProf: 0,
        descripcion: "",
        IdProforma: null,
        EsCredito: 0,
        Serie: "",
        Numero: "",
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
          Almacen: "HUANUCO"
        },
        puntoVenta: {
          IdDocVentaPuntoVenta: 1,
          PuntoVenta: "HUANUCO"
        },
        vendedor: {},
        adelantosPreOrden: [],
        FechaCredito: this.$moment().format("YYYY-MM-DD"),
        FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
        pago: {
          efectivo: { IdMetodoPago: 1, monto: 0 },
          visa: { IdMetodoPago: 2, monto: 0 },
          mastercard: { IdMetodoPago: 3, monto: 0 },
          yape: { IdMetodoPago: 4, monto: 0 }
        },
        IdPreOrden: [],
        CampoDireccion: "Direccion",
        NroComprobanteModifica: "",
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: "",
        EsDelivery: false,
        nombreLlevar: "",
        DireccionEntrega: "",
        diasValidosProf: 1,
        Garantia: 0,
        MostrarGarantia: false,
        MostrarClienteDatos: false,
        ProformaSinPrecio: 0
      };
      this.bloquearInput = false;
      this.adelantosPreOrden = [];
      this.adelanto = 0;
      this.infoDelivery = {};
      this.preVenta = { ...this.venta, isNota: false };
      this.ventas.push({ ...this.venta, isNota: false, productos: [] });
    },
    setTipoVenta(obj) {
      this.ventas[this.index].isNota = false;
      this.bloquearInput = false;
      this.ventas[this.index].NroComprobanteModifica = "";
      this.ventas[this.index].CodSunatModifica = "";
      this.ventas[this.index].NotaIdMotivo = "";
      this.ventas[this.index].NotaDescMotivo = "";
      // this.$refs.inputDocRef && this.$refs.inputDocRef.setValue("");
      if (obj.CodSunat == "07" || obj.CodSunat == "08") {
        // 07 - nota de credito, 08 - Nota de debito
        this.ventas[this.index].isNota = true;
      }

      if (obj.TieneIgv) {
        this.tieneIgv = true;
      } else {
        this.tieneIgv = false;
      }
      // this.$refs.inputTipoVenta.setValue(obj.TipoDoc);
      this.ventas[this.index].tipoVenta = obj;
      return this.getNumeroVenta();
    },
    setDocRefOferta(obj) {
      if (!this.DocRefOfertas.some(v => v.idDocVenta == obj.idDocVenta)) {
        this.DocRefOfertas.push(obj);
      } else {
        this.$swal.fire({
          icon: "warning",
          title: "Aviso",
          text: "El Doc. ya encuentra en la lista."
        });
      }
      // console.log(obj);
    },
    setDocRef(obj) {
      this.ventas[this.index].productos = [];
      this.ventas[this.index].CodSunatModifica = obj.CodSunat;
      this.ventas[this.index].NroComprobanteModifica = obj.NroComprobante;
      if (
        this.ventas[this.index].tipoVenta.CodSunat == "07" ||
        this.ventas[this.index].tipoVenta.CodSunat == "08"
      ) {
        this.ventas[this.index].Serie = this.ventas[this.index].Serie.replace(
          /^./,
          obj.NroComprobante[0]
        );
      }
      this.axios
        .get(this.API + "ventas/solonumero", {
          params: {
            serie: this.ventas[this.index].Serie,
            idTipoDoc: this.ventas[this.index].tipoVenta.IdTipoDoc
          }
        })
        .then(response => {
          this.ventas[this.index].Numero = response.data.numero;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
      //Cargar producto del documento afectado nota credito
      this.axios
        .get(this.API + "venta/detalle/comprobante", {
          params: {
            NroComprobante: obj.NroComprobante,
            idDocVenta: obj.IdDocVenta,
            IdDocVentaPuntoVenta: this.venta.puntoVenta.IdDocVentaPuntoVenta
          }
        })
        .then(response => {
          this.axios
            .get(this.API + "cliente", {
              params: {
                idCliente: response.data[0].IdCliente
              }
            })
            .then(response => {
              if (response.data) {
                this.setCliente(response.data);
                // this.$refs.inputCliente.setValue(response.data.Cliente);
                // this.venta.cliente = response.data;
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });
          // this.limpiarCart();
          response.data.map(async item => {
            item.estadoPxP = false;
            let preciosProd = [];
            item.precioCont = item.PrecioContado;
            preciosProd = JSON.parse(item.PreciosPorProducto || "[]");
            let fPP = preciosProd.filter(p => p.cantidad === 0);

            if (!item.PreciosPorProducto || fPP.length === 4) {
              item.estadoPxP = true;
            }
            let stock = await this.axios(
              `${this.API}productos/stock?idProductos[]=${
                item.IdProducto
              }&idAlmacen=${this.venta.almacen.IdAlmacen}`
            ).then(res => {
              return res.data;
            });
            this.ventas[this.index].productos.push({
              ...item,
              stock: stock[0].stock
            });
            // this.add([item, () => {}, false]);
          });
          this.ventas[this.index].NotaIdMotivo = null;
          this.bloquearInput = true;
          // this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen });
        });
    },
    setMotivo(ev) {
      if (ev == "01" || ev == "02" || ev == "06" || ev == null) {
        this.bloquearInput = true;
      } else {
        this.bloquearInput = false;
      }
    },
    loadAlmacen(id) {
      this.axios.get(this.API + "almacenes/id/" + id).then(res => {
        this.setAlmacen(res.data);
      });
    },
    setAlmacen(obj) {
      console.log(obj);
      this.ventas[this.index].almacen = obj;
      // this.$refs.inputAlmacen.setValue(obj.Almacen);
      // this.obtenerStok({
      //   IdAlmacen: this.ventas[this.index].almacen.IdAlmacen
      // });
    },
    loadPuntoVenta(id) {
      this.axios.get(this.API + "puntoventa/id/" + id).then(res => {
        this.setPuntoVenta(res.data);
        this.getNumeroVenta();
      });
    },
    getTipoDoc(id) {
      this.axios.get(this.API + "tipodoc/" + id).then(response => {
        if (response.data) {
          this.setTipoVenta(response.data);
          this.$nextTick(() => {
            const ref = `inputTipoVenta${this.index}`;
            const refs = this.$refs[ref] || [];
            const componentes = Array.isArray(refs) ? refs : [refs];
            componentes.forEach(componente => {
              if (componente && componente.setValue) {
                componente.setValue(response.data.TipoDoc);
              }
            });
          });
        }
      });
    },
    setPuntoVenta(obj) {
      this.ventas[this.index].puntoVenta = obj;
      this.getNumeroVenta();
    },
    getVendedor() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          let ref = `inputVendedor${this.index}`;
          const vendedorRefs = this.$refs[ref] || [];
          const componentes = Array.isArray(vendedorRefs)
            ? vendedorRefs
            : [vendedorRefs];
          componentes.forEach(componente => {
            if (componente && componente.setValue) {
              componente.setValue(response.data.NombreUsuario);
            }
          });
          this.setVendedor(response.data);
          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
          this.getTipoDoc(response.data.IdTipoDoc);
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    setVendedor(obj) {
      // console.log(obj)
      if (obj.IdTipoDoc) this.getTipoDoc(obj.IdTipoDoc);
      this.ventas[this.index].vendedor = obj;
    },
    setComisionista(obj) {
      if (typeof obj === "object") {
        let ref = `inputComisionista${this.index}`;
        this.$refs[ref][0].setValue(obj.Cliente);
        this.ventas[this.index].comisionista = obj;
      } else {
        this.ventas[this.index].comisionista = {};
      }
    },
    async setCliente(obj, dni = false) {
      let ref = `inputCliente${this.index}`;
      let params = {};
      if (dni) {
        params = {
          dni: dni
        };
      } else {
        params = {
          idCliente: obj.IdCliente || obj
        };
      }

      if (typeof obj === "object") {
        this.$refs[ref][0].setValue(obj.Cliente);
        this.ventas[this.index].cliente = obj;
        this.ventas[this.index].CampoDireccion = this.ventas[
          this.index
        ].cliente.Direccion;
      } else {
        let prof = { ...this.preVenta };
        await this.axios
          .get(this.API + "cliente", {
            params: params
          })
          .then(response => {
            if (response.data) {
              this.$refs[ref][0].setValue(response.data.Cliente);
              this.ventas[this.index].cliente = response.data;
              this.ventas[this.index].CampoDireccion = this.ventas[
                this.index
              ].cliente.Direccion;
            } else {
              this.$toasted.show("No se encontro al cliente");
            }
          })
          .catch(error => {
            console.log(error);
          });
      }

      //this.actualizarPrecioConvenio(obj)
      // Actualizar DisabledTotal
    },
    onSetProducto(obj, cant, precio) {
      if (cant && precio) {
        obj.cantidad = cant;
        let preciosPorProducto = JSON.parse(obj.PreciosPorProducto).find(
          e => e.cantidad === cant && e.precio === precio
        );
        obj.precio = preciosPorProducto.precio || 0;
        return obj;
      }
      obj.cantidad = 1;
      obj.precio = obj.PrecioContado || 0;
      return obj;
      this.loadClienteBarcode();
    },
    onAddProducto(obj) {
      this.actualizarDescuento();
      // this.obtenerStok({
      //   IdAlmacen: this.venta.almacen.IdAlmacen
      // });
    },
    actualizarDescuento() {
      if (this.puntosAplicados > 0) {
        this.aplicarPuntos(this.puntosAplicados);
      }

      if (this.descuentoGeneral > 0) {
        this.aplicarDescuentoGeneral(this.descuentoGeneral);
      }
    },
    aplicarPuntos(val) {
      if (parseFloat(val) > this.venta.cliente.Puntos) {
        this.puntosAplicados = this.venta.cliente.Puntos;
      }

      this.axios
        .get(this.API + "ventas/descuento/" + this.puntosAplicados)
        .then(response => {
          this.descuentoPuntos = response.data;
          this.aplicarDescuento(response.data.descuento);
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    aplicarDescuentoGeneral(val) {
      this.puntosAplicados = 0;
      this.aplicarDescuento(val);
    },
    getNumeroVenta() {
      return this.axios
        .get(this.API + "ventas/numero", {
          params: {
            idPuntoVenta: this.ventas[this.index].puntoVenta
              .IdDocVentaPuntoVenta,
            idTipoDoc: this.ventas[this.index].tipoVenta.IdTipoDoc
          }
        })
        .then(response => {
          this.ventas[this.index].Serie = response.data.serie;
          this.ventas[this.index].Numero = response.data.numero;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    updateTime() {
      this.venta.FechaDoc = this.$moment().format("YYYY-MM-DDTH:mm:ss");
    },
    limpiarDespuesVenta() {
      this.inProgress = false;
      this.tieneIgv = false;
      this.initForm();
      this.limpiarCart();
      this.getVendedor();
      // this.$refs.inputCliente.setValue("");
      // this.getNumeroVenta()
      // this.$refs.inputTipoVenta.setValue('TICKET')
      // this.$refs.inputAlmacen.setValue('VENTA')
      // this.$refs.inputPuntoVenta.setValue('CAJA1')
      // this.$refs.inputVendedor.setValue('')
    },
    guardarDatosProforma(IdProforma) {
      this.venta.IdProforma = IdProforma;
      // console.log("IdProforma", IdProforma);
    },
    quotePhoneUpdated(client){const sale=this.ventas[this.index];if(sale&&sale.cliente&&Number(sale.cliente.IdCliente)===Number(client.IdCliente))this.$set(sale.cliente,'Telefono',client.Telefono);},
    async guardarProforma() {
      if(this.quotePresentation.tipo==='sistemas'&&!this.quotePhoneReady){this.$toasted.error('Guarda un teléfono válido del cliente antes de crear la proforma de sistemas.');return;}
      this.venta = {};
      this.venta = { ...this.ventas[this.index] };
      this.inProgress = true;

      if (!this.venta.productos.length) {
        this.$toasted.show("No hay productos para crear la proforma");
        this.inProgress = false;
        return;
      }

      if (this.diasValidosProf == 0 || this.diasValidosProf == "") {
        this.$toasted.show("Debe ingresar los días de validez de la proforma");
        this.inProgress = false;
        return;
      }

      if (!this.venta.cliente.IdCliente) {
        this.$toasted.show("Debe seleccionar un cliente");
        this.inProgress = false;
        return;
      }

      if (!this.venta.vendedor.Usuario) {
        this.$toasted.show("Debe seleccionar un vendedor");
        this.inProgress = false;
        return;
      }
      if (this.venta.ProformaSinPrecio == 1) {
        this.productos.map(p => {
          p.disabledProf = 1;
        });
      }

      // this.venta.productos = this.productos;
      this.venta.diasValidosProf = this.diasValidosProf;
      this.venta.presentacionProforma=Object.assign({},this.quotePresentation);
      const saveRequest = this.axios.post(this.API + 'proformas', this.venta);
      let saved = false;
      await saveRequest
        .then(response => {
          saved = true;this.quotePresentationOpen=false;
          this.$toasted.success("Proforma guardada");
          window.open(
            "/nuevo/index.html#/reportes/proforma/" + response.data.insertId,
            "_blank"
          );
        })
        .catch(error => {
          console.log(error);
          this.$toasted.error((error.response && error.response.data && error.response.data.error) || 'No se pudo guardar. Tus cambios siguen en pantalla.');
          this.inProgress = false;
        });
      this.inProgress = false;
      if (saved) this.eliminarVentana();
    },
    async guardarVenta(evt, aliOptions = null) {
      if (this.inProgress || (!aliOptions && (this.aliCheckoutPreparing || this.aliCheckoutSubmitting || (this.aliCheckoutReview && this.aliCheckoutReview.sale === this.ventas[this.index])))) {
        this.$toasted.show('Hay un resumen ALIPAGO pendiente. Confírmalo o cancélalo antes de usar Pagar.');
        return;
      }
      this.venta = {};
      this.venta = this.ventas[this.index];
      // evt.preventDefault();

      this.inProgress = true;
      let totalPagos = 0;
      totalPagos += isNaN(parseFloat(this.venta.pago.efectivo.monto))
        ? 0
        : parseFloat(this.venta.pago.efectivo.monto);
      totalPagos += isNaN(parseFloat(this.venta.pago.visa.monto))
        ? 0
        : parseFloat(this.venta.pago.visa.monto);
      totalPagos += isNaN(parseFloat(this.venta.pago.mastercard.monto))
        ? 0
        : parseFloat(this.venta.pago.mastercard.monto);
      totalPagos += isNaN(parseFloat(this.venta.pago.yape.monto))
        ? 0
        : parseFloat(this.venta.pago.yape.monto);

      if (
        (isNaN(parseFloat(this.venta.pago.efectivo.monto))
          ? 0
          : parseFloat(this.venta.pago.efectivo.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de pago efectivo no puede ser negativo");
          if (aliOptions && aliOptions.prepare) throw new Error("El metodo de pago efectivo no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.venta.pago.visa.monto))
          ? 0
          : parseFloat(this.venta.pago.visa.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de pago Tarjeta no puede ser negativo");
          if (aliOptions && aliOptions.prepare) throw new Error("El metodo de pago Tarjeta no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.venta.pago.mastercard.monto))
          ? 0
          : parseFloat(this.venta.pago.mastercard.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show(
          "El metodo de pago Transferencia no puede ser negativo"
        );
          if (aliOptions && aliOptions.prepare) throw new Error("El metodo de pago Transferencia no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.venta.pago.yape.monto))
          ? 0
          : parseFloat(this.venta.pago.yape.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show(
          "El metodo de pago YAPE / PLIN no puede ser negativo"
        );
          if (aliOptions && aliOptions.prepare) throw new Error("El metodo de pago YAPE / PLIN no puede ser negativo");
        this.inProgress = false;
        return;
      }

      if (!this.venta.EsCredito) {
        if (
          parseFloat(this.venta.totalCart).toFixed(2) !=
          parseFloat(totalPagos).toFixed(2)
        ) {
          this.$toasted.show("Los pagos no coinciden con la venta");
          if (aliOptions && aliOptions.prepare) throw new Error("Los pagos no coinciden con la venta");
          this.inProgress = false;
          return;
        }
      }

      if (!this.venta.cliente.DniRuc) {
        this.$toasted.show("Debe seleccionar un cliente.");
          if (aliOptions && aliOptions.prepare) throw new Error("Debe seleccionar un cliente.");
        this.inProgress = false;
        return;
      }

      var esNumerico = Number.isInteger(parseInt(this.venta.cliente.DniRuc));
      if (!esNumerico) {
        this.$toasted.show("El RUC debe ser numérico, editar cliente.");
          if (aliOptions && aliOptions.prepare) throw new Error("El RUC debe ser numérico, editar cliente.");
        this.inProgress = false;
        return;
      }

      var esEmpresa = this.venta.cliente.DniRuc.length > 9 ? true : false;
      if (!esEmpresa && this.venta.tipoVenta.CodSunat == "01") {
        this.$toasted.show(
          "Una factura debe ser emitida para una empresa, no un usuario común."
        );
          if (aliOptions && aliOptions.prepare) throw new Error("Una factura debe ser emitida para una empresa, no un usuario común.");
        this.inProgress = false;
        return;
      }
      // validar serie
      let prods = this.venta.productos;
      // let val = !prods.some(v => v.Series.length > 0 && !!v.IdProductoSerie);

      // if (val) {
      //   this.$toasted.show(
      //     "Debe Seleccionar la serie del produto. Verifique."
      //   );
      //   this.inProgress = false;
      //   return;
      // }
      //Nota de Creadito
      if (
        this.venta.tipoVenta.CodSunat == "07" ||
        this.venta.tipoVenta.CodSunat == "08"
      ) {
        if (
          !this.venta.CodSunatModifica ||
          !this.venta.NroComprobanteModifica
        ) {
          this.$toasted.show("Debe seleccionar un Documento de Referencia...");
          if (aliOptions && aliOptions.prepare) throw new Error("Debe seleccionar un Documento de Referencia...");
          this.inProgress = false;
          return;
        }
        if (!this.venta.NotaIdMotivo) {
          this.$toasted.show("Debe seleccionar un Motivo");
          if (aliOptions && aliOptions.prepare) throw new Error("Debe seleccionar un Motivo");
          this.inProgress = false;
          return;
        }
        this.venta.NotaDescMotivo = this.itemsMotivoNota.filter(
          item => item.value == this.venta.NotaIdMotivo
        )[0].text;
      }

      if (!this.venta.productos.length) {
        this.$toasted.show("No hay productos para vender");
          if (aliOptions && aliOptions.prepare) throw new Error("No hay productos para vender");
        this.inProgress = false;
        return;
      }

      // NO PERMITE CANTIDAD Y PRECIOS 0
      if(laborSummary(this.venta.productos).overBudget){this.$toasted.error('Los componentes superan la mano de obra bruta. Revisa cantidades e importes.');this.inProgress=false;return;}
      let sinStock = 0;
      let totalCero = 0;
      let menosUtilidad = 0;
      this.venta.productos.forEach(pro => {
        if (
          pro.CodigoBarra !== "COSTODELIVERY" &&
          !isLaborService(pro) &&
          Number(pro.ControlaStock)!==0 && (pro.stock <= 0 || parseFloat(pro.cantidad) > pro.stock) &&
          pro.EsPadre == 0
        ) {
          sinStock += 1;
        }

        if (parseFloat(pro.total) <= 0) {
          totalCero += 1;
        }

        /*if ((!(1/(pro.PorcentajeUtilidad*0)===1/0) || (pro.PorcentajeUtilidad=='')) && (pro.CodigoBarra !== 'COSTODELIVERY') && (pro.CodigoBarra !== 'MANODEOBRA')) {
                    menosUtilidad += 1
                }*/

        if (
          parseFloat(pro.precio) - pro.PrecioCosto < 0 &&
          pro.CodigoBarra !== "COSTODELIVERY" &&
          !isLaborService(pro)
        ) {
          menosUtilidad += 1;
        }
      });

      if (this.venta.tipoVenta.CodSunat != "99") {
        if (menosUtilidad >= 1 && this.venta.tipoVenta.CodSunat != "07") {
          this.$toasted.error(
            "No se puede vender un producto con ganancia negativa"
          );
          if (aliOptions && aliOptions.prepare) throw new Error("No se puede vender un producto con ganancia negativa");
          this.inProgress = false;
          return;
        }
      }
      if (sinStock >= 1 && this.venta.tipoVenta.CodSunat != "07") {
        this.$toasted.error("No se puede vender un producto sin stock");
          if (aliOptions && aliOptions.prepare) throw new Error("No se puede vender un producto sin stock");
        this.inProgress = false;
        return;
      }
      if (this.venta.tipoVenta.CodSunat != "99") {
        if (totalCero >= 1) {
          this.$toasted.error("No se puede enviar un producto en S/. 0.00");
          if (aliOptions && aliOptions.prepare) throw new Error("No se puede enviar un producto en S/. 0.00");
          this.inProgress = false;
          return;
        }

        if (this.totalCart <= 0) {
          this.$toasted.show("La venta en S/. 0.00 no está permitido");
          if (aliOptions && aliOptions.prepare) throw new Error("La venta en S/. 0.00 no está permitido");
          this.inProgress = false;
          return;
        }
      }

      if (!this.validarDocRefOfetar()) {
        this.inProgress = false;
        return;
      }

      let IdOfertas = this.generarOfertas();

      // this.venta.productos = this.productos;
      // this.venta.puntosAplicados = this.puntosAplicados || 0;
      // this.venta.totalCart = this.venta.totalCart || 0;
      // this.venta.PagoCon = this.$refs.carrito.pagocon;
      // this.venta.adelantosPreOrden = this.adelantosPreOrden;
      this.venta.IdPedido = this.$route.query.idPedido || null;
      this.venta.nombreLlevar = this.venta.nombreLlevar.toUpperCase();
      // this.venta.IdOferta =  this.venta.IdOferta;
      this.venta.yapeMonto = this.venta.pago.yape.monto
        ? this.venta.pago.yape.monto
        : 0;
      this.venta.yapeDescripcion = this.venta.pago.yape.descripcion
        ? this.venta.pago.yape.descripcion
        : "";
      this.venta.visaMonto = this.venta.pago.visa.monto
        ? this.venta.pago.visa.monto
        : 0;
      this.venta.visaDescripcion = this.venta.pago.visa.descripcion
        ? this.venta.pago.visa.descripcion
        : "";
      this.venta.mastercardMonto = this.venta.pago.mastercard.monto
        ? this.venta.pago.mastercard.monto
        : 0;
      this.venta.mastercardDescripcion = this.venta.pago.mastercard.descripcion
        ? this.venta.pago.mastercard.descripcion
        : "";
      this.venta.DireccionEntrega = this.venta.EsDelivery
        ? this.infoDelivery.Direccion || ""
        : "";

      this.venta.IdDocVentas = "";
      if (this.venta.tipoVenta.CodSunat == "99") {
        let IdDocVentas = this.DocRefOfertas.map(v => v.idDocVenta);
        this.venta.IdDocVentas = IdDocVentas.join(",");
      }
      if (aliOptions && aliOptions.prepare) { this.inProgress = false; return JSON.parse(JSON.stringify(this.venta)); }
      await this.axios
        .post(this.API + "ventas", this.venta)
        .then(response => {
          // limpiar preorden seleccionado
          // this.$router.replace("/ventas");
          this.$toasted.success("Venta realizada");
          this.inProgress = false;
          this.tieneIgv = false;
          this.ultimaVenta = response.data.insertId;
          let IdOfertasString = IdOfertas.join(",");
          // this.limpiarDespuesVenta();
          this.enviarImpresionVenta(response.data.insertId, IdOfertasString);
          setTimeout(() => {
            this.inProgress = false;
            this.eliminarVentana();
          }, 1000);
        })

        .catch(error => {
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
          console.error("Error del servidor:", error.response.data);
        });
      // window.location.reload();
    },
    async obtenerSeriePorProducto(id) {
      let productoSerie = [];
      let params = {
        IdProducto: id,
        Todos: false,
        empresa: sessionStorage.getItem("empresa"),
        IdAlmacen: this.idAlmacen
      };
      productoSerie = await this.axios(`${this.API}/productos/serie`, {
        params: {
          IdProducto: id,
          IdAlmacen: this.IdAlmacen
        }
      }).then(res => {
        return res.data;
      });

      return productoSerie;
    },
    generarOfertas() {
      let IdOferta = [];

      this.lstOfertas.forEach(oferta => {
        if (oferta.ProductoFlag == 1) {
          let producto = this.productos.filter(
            v => v.IdProducto == oferta.IdProducto
          )[0];

          if (producto) {
            if (oferta.MontoFlag == 1) {
              if (oferta.Monto <= producto.cantidad * producto.precio) {
                IdOferta.push(oferta.IdOferta);
              }
            } else if (oferta.CantidadFlag == 1) {
              if (oferta.Cantidad <= producto.cantidad) {
                IdOferta.push(oferta.IdOferta);
              }
            } else {
              IdOferta.push(oferta.IdOferta);
            }
          }
        } else if (oferta.MontoFlag == 1) {
          IdOferta.push(oferta.IdOferta);
        }
        // else if (oferta.CantidadFlag == 1) {
        //   console.log("Imprimir ofertas relacionadas con CantidadFlag");
        // }
      });
      return IdOferta;
    },
    eliminarCliente() {
      // this.$refs.inputCliente.setValue("");
      this.venta.cliente = {};
    },
    eliminarComisionista() {
      // this.$refs.inputComisionista.setValue("");
      this.venta.comisionista = {};
    },
    onPagarDelivery(obj) {
      this.$route.query.idPedido = obj.IdPedido;
      let venta = { ...this.preVenta };
      if (this.$route.query.idPedido) {
        this.infoDelivery = obj;
        venta.EsDelivery = true;
        // this.$refs.carrito.pagocon = parseFloat(obj.Cambio).toFixed(2);
        //cargar costo de delivery
        let costoDelivery = parseFloat(obj.PrecioDelivery || 0).toFixed(2);
        if (costoDelivery > 0) {
          this.axios
            .get(this.API + "costo/delivery")
            .then(response => {
              if (response.data) {
                let costoDeliverObj = response.data;
                costoDeliverObj.precio = costoDelivery;
                costoDeliverObj.cantidad = 1;
                costoDeliverObj.EsCostoDelivery = 1;
                costoDeliverObj.IdImpresora = 0;
                costoDeliverObj.descuento = 0;
                costoDeliverObj.indicacion = "";

                //Cargar Productos Delivery
                let productos = obj.Productos;
                productos = productos.map(item => {
                  return {
                    ...item,
                    cantidad: item.Cantidad,
                    precio: item.Precio,
                    indicacion: item.Indicacion
                  };
                });
                venta.nombreLlevar = obj.Cliente || "SIN NOMBRE";
                venta.DireccionEntrega = obj.Direccion;
                this.$toasted.success("PRODUCTOS DEL DELIVERY AGREGADOS");
                this.ventas.push({ ...venta, productos: productos });
                this.$nextTick(() => {
                  this.index = this.ventas.length - 1;
                  this.getVendedor();
                  let v = this.ventas[this.index];

                  Promise.all([
                    this.cargarMonto("efectivo"),
                    this.loadAlmacen(v.almacen.IdAlmacen),
                    this.loadPuntoVenta(v.puntoVenta.IdDocVentaPuntoVenta),
                    this.loadClienteVarios(),
                    this.loadComisionista(),
                    // this.setCliente(costoDeliverObj),
                    this.actualizarTotalCart()
                  ]);
                });
              } else {
                this.$toasted.show(
                  "PRIMERO CREE EL PRODUCTO COSTO DE DELIVERY CON EL CODIGO DE BARRAS 'COSTODELIVERY'"
                );
                this.anularPagoDelivery();
              }
            })
            .catch(error => {
              console.error("Error delivery", error);
              this.$toasted.show("HUBO UN ERROR");
            });
        } else {
          //Cargar Productos Delivery
          let productos = JSON.parse(obj.Productos);
          productos.map(item => {
            this.add([
              item.IdProducto,
              () => {
                this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
              },
              {
                cantidad: item.Cantidad,
                precio: item.Precio,
                indicacion: item.Indicacion,
                isDelivery: true
              },
              false
            ]);
          });
          this.venta[this.venta].nombreLlevar = obj.Cliente || "SIN NOMBRE";
          this.venta[this.venta].DireccionEntrega = obj.Direccion;
        }
      }
    },
    anularPagoDelivery() {
      this.limpiarCart();
      this.venta.EsDelivery = false;
      this.infoDelivery = {};
      // this.$refs.carrito.pagocon = "";
      this.loadClienteVarios();
      this.loadComisionista();
      this.venta.nombreLlevar = "";
      this.venta.selectOpcionVenta = 0;
      this.venta.nombreLlevar = "";
      this.venta.DireccionEntrega = "";
      this.$router.replace("/ventas");
    },

    conectarSocket() {
      if (!this.$socket.connected) {
        // console.log("🔗 Conectando socket...");
        this.$socket.connect();
      }
    },

    /**
     * ✅ NUEVO: Configurar todos los listeners
     */
    setupSocketListeners() {
      // Evento: Conectado
      this.listeners.connect = () => {
        // console.log("✅ Socket conectado en VentasForm");
      };
      this.$socket.on("connect", this.listeners.connect);

      // Evento: Desconectado
      this.listeners.disconnect = () => {
        console.log("❌ Socket desconectado en VentasForm");
      };
      this.$socket.on("disconnect", this.listeners.disconnect);

      // Evento: Nuevo pedido
      this.listeners.nuevoPedido = data => {
        if (this.$refs.deliveryModal && this.$refs.deliveryModal.show) {
          this.$toasted.success(
            "Nuevo Pedido de " + "<strong>" + data.nombre + "</strong>"
          );
          this.$refs.deliveryModal.getPedidos();
        } else {
          if (this.$refs.deliveryModal) {
            this.$refs.deliveryModal.getPedidos();
          }
          this.$swal({
            position: "top-end",
            icon: "info",
            target: document.getElementById("conten"),
            title:
              "Pedido de Delivery de: <br> " +
              "<strong>" +
              data.nombre +
              "</strong>",
            showConfirmButton: true,
            confirmButtonText: "Mostrar Pedidos",
            confirmButtonColor: "#3880ff"
          }).then(result => {
            if (result.isConfirmed) {
              if (this.$refs.deliveryModal) {
                this.$refs.deliveryModal.show = true;
              }
            } else if (result.isDenied) {
              console.log("Denegado");
            }
          });
        }
      };
      this.$socket.on("nuevo-pedido", this.listeners.nuevoPedido);
    },

    /**
     * ✅ NUEVO: Remover todos los listeners
     */
    removeSocketListeners() {
      this.$socket.off("connect", this.listeners.connect);
      this.$socket.off("disconnect", this.listeners.disconnect);
      this.$socket.off("nuevo-pedido", this.listeners.nuevoPedido);
    }
  },
  components: {
    'buscador-productos-ia':BuscadorProductosIA,
    ProformaPresentacion,
    ClienteTelefonoRapido,
    DiarioRecordatorios,
    AliAssistant,
    PedidosWeb,
    VentaReimpresion,
    ProductosCarrito,
    ClientesForm,
    ConfigVenta,
    PagoDeuda,
    PreOrdenLista,
    Proformas,
    Movimiento,
    DeliveryModal,
    QRpagoYape
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
/* .body {
  background: #e3f2fd;
} */
</style>
<style>
.holder {
  color: #721c24;
  background-color: #f8d7da;
  border-color: #f5c6cb;
  clear: both;
  display: inline-block;
  overflow: hidden;
}
.news {
  animation: slide 20s linear infinite;
  white-space: pre;
}

.botones {
  /* border: 1px solid red; */
  /* border-radius: 25px; */
  padding: 5%;
  font-weight: bold;
  font-size: 1.5em;
}

#mdlTouch .modal-dialog {
  width: 15% !important;
}

@keyframes slide {
  0% {
    transform: translatex(100vw);
  }

  100% {
    transform: translatex(-120vw);
  }
}

@media only screen and (max-width: 920px) {
  #mdlTouch .modal-dialog {
    width: 30% !important;
  }
  /* For desktop: */
  .ocultarMovil {
    display: none !important;
  }
}
.ocultarWeb {
  display: none;
}
@media only screen and (min-width: 777px) and (max-width: 1800px) {
  /* For desktop: */
  .ancho {
    width: calc(100% - 300px);
  }
}
@media only screen and (max-width: 750px) {
  #mdlTouch .modal-dialog {
    width: 100% !important;
  }
  /* For desktop: */
  .ocultarWeb {
    display: inline;
  }
  .ocularMovil2 {
    display: none !important;
  }
  .mostrarSerie {
    display: inline !important;
    font-size: 1.5rem;
  }
  .lblSerie {
    text-align: center !important;
    padding: auto;
  }
}
/* @media (max-width: 850px) {
  .ocultarMovil {
    display: none !important;
  }
} */
.clsProducto {
  min-width: 200px !important;
  white-space: normal;
  word-wrap: break-word;
}

.body {
  background: #e3f2fd !important;
  min-height: 100vh;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 6px solid #ddd;
  border-top-color: #3498db; /* Color del borde superior */
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 20px auto; /* Centrado */
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* POS TECHNOLOGY UI: capa visual aislada de los procesos de venta. */
.body.pos-page {
  --pos-ink: #082338;
  --pos-muted: #668198;
  --pos-blue: #0878f9;
  --pos-cyan: #00c7e8;
  --pos-green: #16c784;
  --pos-purple: #7c3aed;
  --pos-border: rgba(37, 99, 235, 0.14);
  --pos-shadow: 0 18px 50px rgba(10, 41, 77, 0.12);
  position: relative;
  min-height: 100vh;
  padding: 10px 12px 24px !important;
  overflow-x: hidden;
  color: var(--pos-ink);
  font-family: "Segoe UI", Inter, Roboto, Arial, sans-serif;
  background:
    radial-gradient(circle at 2% 2%, rgba(0, 199, 232, 0.16), transparent 25rem),
    radial-gradient(circle at 98% 8%, rgba(124, 58, 237, 0.13), transparent 28rem),
    linear-gradient(145deg, #f7fbff 0%, #edf5ff 48%, #f8fbff 100%) !important;
}

.pos-page::before {
  position: fixed;
  z-index: 0;
  inset: 0;
  content: "";
  pointer-events: none;
  opacity: 0.32;
  background-image:
    linear-gradient(rgba(8, 120, 249, 0.045) 1px, transparent 1px),
    linear-gradient(90deg, rgba(8, 120, 249, 0.045) 1px, transparent 1px);
  background-size: 34px 34px;
  mask-image: linear-gradient(to bottom, #000, transparent 82%);
}

.pos-page > * {
  position: relative;
  z-index: 1;
}

.pos-page .pos-shell.card {
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.82);
  border-radius: 22px;
  background: rgba(255, 255, 255, 0.82);
  box-shadow: var(--pos-shadow);
  backdrop-filter: blur(16px);
  animation: posFadeUp 0.55s cubic-bezier(0.2, 0.8, 0.2, 1) both;
}

/* El buscador de productos debe desplegarse sobre el resto de la venta. */
.pos-page .pos-shell.card:focus-within,
.pos-page .pos-tabs:focus-within,
.pos-page .pos-tabs .tab-content:focus-within,
.pos-page .pos-workspace:focus-within,
.pos-page .pos-cart-panel:focus-within {
  overflow: visible !important;
}

/* Mantiene el autocompletado por encima del formulario de pago, que es un
   hermano posterior del bloque de venta. */
.pos-page > .pos-shell.card:focus-within {
  z-index: 7000 !important;
}

.pos-page .pos-tabs > .card-header {
  padding: 7px 10px;
  border: 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  background:
    linear-gradient(110deg, rgba(0, 199, 232, 0.13), transparent 38%),
    linear-gradient(135deg, #061b30, #0a3152);
}

.pos-page .pos-tabs .nav-pills {
  gap: 9px;
}

.pos-page .pos-tabs .nav-pills .nav-item {
  flex: 0 1 auto;
}

.pos-page .pos-tabs .nav-pills .nav-link {
  position: relative;
  min-width: 104px;
  padding: 8px 15px;
  overflow: hidden;
  border: 1px solid rgba(255, 255, 255, 0.16);
  border-radius: 12px;
  color: rgba(255, 255, 255, 0.78);
  background: rgba(255, 255, 255, 0.07);
  font-weight: 800;
  letter-spacing: 0.02em;
  transition: transform 0.22s ease, background 0.22s ease, box-shadow 0.22s ease;
}

.pos-page .pos-tabs .nav-pills .nav-link:hover {
  color: #fff;
  background: rgba(255, 255, 255, 0.14);
  transform: translateY(-2px);
}

.pos-page .pos-tabs .nav-pills .nav-link.active {
  color: #fff;
  border-color: transparent;
  background: linear-gradient(135deg, var(--pos-blue), var(--pos-cyan));
  box-shadow: 0 9px 22px rgba(0, 163, 224, 0.35);
  animation: posTabGlow 3s ease-in-out infinite;
}

.pos-page .pos-tabs .card-body {
  padding: 0;
}

.pos-page .pos-workspace {
  padding: 10px !important;
  background: transparent;
}

.pos-tech-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 18px;
  margin-bottom: 14px;
  padding: 16px 20px;
  overflow: hidden;
  border: 1px solid rgba(0, 199, 232, 0.22);
  border-radius: 18px;
  color: #fff;
  background:
    radial-gradient(circle at 82% 0%, rgba(0, 199, 232, 0.3), transparent 17rem),
    linear-gradient(120deg, #071d33 0%, #0b3d61 62%, #064e5c 100%);
  box-shadow: 0 15px 35px rgba(5, 32, 57, 0.2);
}

.pos-tech-heading {
  display: flex;
  align-items: center;
  gap: 14px;
}

.pos-tech-icon {
  display: grid;
  flex: 0 0 50px;
  width: 50px;
  height: 50px;
  place-items: center;
  border: 1px solid rgba(255, 255, 255, 0.24);
  border-radius: 15px;
  color: #fff;
  background: linear-gradient(145deg, rgba(0, 199, 232, 0.95), rgba(8, 120, 249, 0.9));
  box-shadow: 0 0 25px rgba(0, 199, 232, 0.34);
  font-size: 21px;
}

.pos-tech-header h1 {
  margin: 1px 0 2px;
  color: #fff;
  font-size: clamp(1.25rem, 2vw, 1.75rem);
  font-weight: 850;
  line-height: 1.05;
  letter-spacing: -0.025em;
}

.pos-tech-header p {
  margin: 0;
  color: rgba(222, 244, 255, 0.72);
  font-size: 0.82rem;
}

.pos-eyebrow {
  color: #6de9ff;
  font-size: 0.68rem;
  font-weight: 900;
  letter-spacing: 0.16em;
}

.pos-live-status {
  display: inline-flex;
  flex: 0 0 auto;
  align-items: center;
  gap: 8px;
  padding: 9px 13px;
  border: 1px solid rgba(73, 255, 188, 0.24);
  border-radius: 999px;
  color: #c8ffe9;
  background: rgba(9, 38, 54, 0.48);
  font-size: 0.76rem;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.pos-live-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #34f5a6;
  box-shadow: 0 0 0 4px rgba(52, 245, 166, 0.14), 0 0 16px #34f5a6;
  animation: posPulse 1.8s ease-in-out infinite;
}

.pos-sale-grid {
  margin-right: -7px;
  margin-left: -7px;
}

.pos-sale-grid > [class*="col-"] {
  padding-right: 7px;
  padding-left: 7px;
}

.pos-customer-panel,
.pos-loyalty-panel,
.pos-document-panel {
  margin-bottom: 10px;
  padding-top: 10px;
  border: 1px solid var(--pos-border);
  border-radius: 17px;
  background: rgba(255, 255, 255, 0.92);
  box-shadow: 0 9px 24px rgba(21, 57, 91, 0.08);
}

.pos-customer-panel label,
.pos-customer-panel span,
.pos-loyalty-panel label,
.pos-document-panel label {
  color: #27445c;
  font-size: 0.76rem;
  font-weight: 800;
}

.pos-page .form-control,
.pos-page .custom-select,
.pos-page .input-group-text {
  min-height: 40px;
  border: 1px solid #cfdeeb;
  border-radius: 10px;
  color: var(--pos-ink);
  background-color: #fafdff;
  transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.pos-page .form-control:focus,
.pos-page .custom-select:focus {
  border-color: var(--pos-cyan);
  background: #fff;
  box-shadow: 0 0 0 4px rgba(0, 199, 232, 0.12);
}

.pos-page .form-control:disabled,
.pos-page .form-control[readonly] {
  color: #415d73;
  background: #eef4f8;
}

.pos-page .input-group > .form-control:not(:last-child),
.pos-page .input-group > .custom-select:not(:last-child) {
  border-top-right-radius: 10px;
  border-bottom-right-radius: 10px;
}

.pos-page .input-group .btn {
  min-height: 40px;
  border: 0;
  box-shadow: none;
}

.pos-guarantee-strip {
  align-items: center;
  margin-top: 8px;
  padding: 8px 12px;
  border: 1px dashed rgba(8, 120, 249, 0.22);
  border-radius: 11px;
  background: linear-gradient(90deg, rgba(8, 120, 249, 0.055), rgba(0, 199, 232, 0.04));
}

.pos-guarantee-strip label {
  margin: 0 12px 0 0 !important;
}

.pos-loyalty-panel {
  position: relative;
  overflow: hidden;
  background: linear-gradient(145deg, #ffffff, #f2f9ff);
}

.pos-loyalty-panel::after {
  position: absolute;
  top: -50px;
  right: -50px;
  width: 130px;
  height: 130px;
  border-radius: 50%;
  content: "";
  background: radial-gradient(circle, rgba(124, 58, 237, 0.16), transparent 68%);
  pointer-events: none;
}

.pos-page .puntos {
  color: transparent;
  background: linear-gradient(135deg, var(--pos-purple), var(--pos-blue), var(--pos-cyan));
  -webkit-background-clip: text;
  background-clip: text;
  font-size: clamp(2.4rem, 4vw, 3.5rem);
  font-weight: 850;
  line-height: 1;
}

.pos-document-panel {
  background: linear-gradient(155deg, #071d33 0%, #0b3d61 100%);
  color: #dff7ff;
}

.pos-document-panel .big-text,
.pos-document-panel label,
.pos-document-panel .form-group {
  color: #fff;
}

.pos-document-panel .big-text {
  font-size: clamp(1.35rem, 2vw, 1.85rem);
  font-weight: 400;
  letter-spacing: 0.035em;
}

.pos-cart-panel {
  min-width: 0;
  padding: 12px !important;
  border: 1px solid rgba(8, 120, 249, 0.13);
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.94);
  box-shadow: 0 12px 32px rgba(21, 57, 91, 0.08);
}

.pos-page .pos-search-toolbar {
  align-items: flex-end;
  margin-right: -6px;
  margin-left: -6px;
}

.pos-page .pos-search-column {
  padding-right: 6px;
  padding-left: 6px;
}

.pos-page .pos-search-group {
  margin-bottom: 5px;
}

.pos-page .pos-search-group .form-control {
  min-height: 48px;
  border-width: 1px;
  background: #f8fbfe;
  font-size: 0.96rem;
}

.pos-page .pos-search-group .btn {
  min-width: 48px;
  min-height: 48px;
  margin-left: 3px;
  border-radius: 10px !important;
  transition: transform 0.2s ease, filter 0.2s ease;
}

.pos-page .pos-search-group .btn:hover {
  filter: brightness(1.08);
  transform: translateY(-2px);
}

.pos-search-hint {
  margin: 0 0 6px 3px;
  color: var(--pos-muted);
  font-size: 0.72rem;
  font-weight: 650;
}

.pos-search-hint i {
  margin-right: 4px;
  color: var(--pos-purple);
}

.pos-page .pos-product-table {
  margin-top: 12px;
  margin-bottom: 0;
  border-collapse: separate;
  border-spacing: 0 7px;
  font-size: 0.82rem;
}

.pos-page .pos-product-table thead th {
  padding: 10px 8px;
  border: 0;
  color: #dff8ff;
  background: linear-gradient(135deg, #08283d, #075d69);
  font-size: 0.72rem;
  font-weight: 850;
  text-transform: uppercase;
  letter-spacing: 0.035em;
  vertical-align: middle;
}

.pos-page .pos-product-table thead th:first-child {
  border-radius: 11px 0 0 11px;
}

.pos-page .pos-product-table thead th:last-child {
  border-radius: 0 11px 11px 0;
}

.pos-page .pos-product-table tbody tr {
  background: #fff;
  box-shadow: 0 7px 19px rgba(15, 57, 88, 0.07);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.pos-page .pos-product-table tbody tr:hover {
  background: #f7fcff;
  box-shadow: 0 11px 25px rgba(8, 120, 249, 0.12);
  transform: translateY(-2px);
}

.pos-page .pos-product-table tbody td {
  padding: 9px 7px;
  border-top: 1px solid #e6eef5;
  border-bottom: 1px solid #e6eef5;
  vertical-align: middle;
}

.pos-page .pos-product-table tbody td:first-child {
  border-left: 1px solid #e6eef5;
  border-radius: 11px 0 0 11px;
}

.pos-page .pos-product-table tbody td:last-child {
  border-right: 1px solid #e6eef5;
  border-radius: 0 11px 11px 0;
}

.pos-page .pos-product-table .form-control {
  min-height: 36px;
  padding: 5px 7px;
  border-radius: 8px;
}

.pos-page .pos-product-table .btn-sm {
  width: 34px;
  min-width: 34px;
  height: 34px;
  min-height: 34px;
  padding: 0;
  border: 0;
  border-radius: 9px;
  box-shadow: 0 5px 12px rgba(8, 35, 56, 0.16);
  transition: transform 0.18s ease, box-shadow 0.18s ease;
}

.pos-page .pos-product-table .btn-sm:hover {
  box-shadow: 0 8px 16px rgba(8, 35, 56, 0.22);
  transform: translateY(-2px) scale(1.04);
}

.pos-checkout-area {
  margin-top: 9px;
  padding: 0 !important;
}

.pos-checkout-form {
  padding: 11px;
  border: 1px solid rgba(255, 255, 255, 0.9);
  border-radius: 22px;
  background: rgba(255, 255, 255, 0.78);
  box-shadow: var(--pos-shadow);
  backdrop-filter: blur(14px);
}

.pos-primary-action-row {
  margin-bottom: 9px;
}

.pos-page .btnAction {
  position: relative;
  width: calc(100% - 10px);
  min-height: 46px;
  margin: 5px;
  padding: 10px 14px;
  overflow: hidden;
  border: 0 !important;
  border-radius: 13px !important;
  color: #fff !important;
  box-shadow: 0 9px 18px rgba(8, 35, 56, 0.17);
  font-size: 0.83rem !important;
  font-weight: 850;
  text-transform: uppercase;
  letter-spacing: 0.035em;
  transition: transform 0.22s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.22s ease, filter 0.22s ease;
  isolation: isolate;
}

.pos-page .btnAction::before {
  position: absolute;
  z-index: -1;
  inset: 1px;
  border: 1px solid rgba(255, 255, 255, 0.28);
  border-radius: inherit;
  content: "";
  pointer-events: none;
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
  transition: box-shadow 0.25s ease, border-color 0.25s ease;
}

.pos-page .btnAction::after {
  position: absolute;
  top: -80%;
  left: -55%;
  width: 34%;
  height: 260%;
  content: "";
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.42), transparent);
  transform: rotate(22deg) translateX(-230%);
  transition: transform 0.65s ease;
}

.pos-page .btnAction:hover:not(:disabled) {
  color: #fff;
  filter: saturate(1.1) brightness(1.04);
  box-shadow: 0 14px 27px rgba(8, 35, 56, 0.24);
  transform: translateY(-3px);
}

.pos-page .btnAction:hover:not(:disabled)::before,
.pos-page .btnAction:focus-visible::before {
  border-color: rgba(255, 255, 255, 0.58);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.36), 0 0 20px rgba(83, 226, 255, 0.24);
}

.pos-page .btnAction:focus-visible {
  outline: 3px solid rgba(0, 199, 232, 0.22);
  outline-offset: 3px;
}

.pos-page .btnAction:hover::after {
  transform: rotate(22deg) translateX(520%);
}

.pos-page .btnAction:active:not(:disabled) {
  box-shadow: 0 6px 13px rgba(8, 35, 56, 0.17);
  transform: translateY(-1px) scale(0.99);
}

.pos-page .btnAction:disabled {
  cursor: not-allowed;
  filter: grayscale(0.35);
  opacity: 0.58;
}

.pos-page .pos-pay-button {
  min-height: 60px;
  margin: 2px 0 10px;
  border: 1px solid rgba(255, 255, 255, 0.46) !important;
  border-radius: 18px !important;
  background: linear-gradient(120deg, #09a96e, #16d785 52%, #00b8c9) !important;
  box-shadow: 0 13px 30px rgba(22, 199, 132, 0.32), 0 0 0 7px rgba(22, 199, 132, 0.08);
  font-size: 1.22rem !important;
  letter-spacing: 0.055em;
  background-size: 180% 180% !important;
  animation: posPayGlow 2.8s ease-in-out infinite, posGradientFlow 5s ease-in-out infinite;
}

.pos-page .pos-pay-button i {
  margin-right: 8px;
}

.pos-page .pos-btn-proforma {
  background: linear-gradient(135deg, #ff7a18, #ff3d54) !important;
  background-size: 180% 180% !important;
  animation: posGradientFlow 5.4s ease-in-out infinite;
}

.pos-page .pos-btn-window {
  background: linear-gradient(135deg, #667eea, #764ba2) !important;
  background-size: 180% 180% !important;
  animation: posGradientFlow 5.8s ease-in-out -1.3s infinite;
}

.pos-page .pos-btn-remove-window {
  color: #18324a !important;
  background: linear-gradient(135deg, #ffd54f, #ffb300) !important;
  background-size: 180% 180% !important;
  animation: posGradientFlow 5.2s ease-in-out -0.8s infinite;
}

.pos-page .pos-btn-print {
  background: linear-gradient(135deg, #00a6cb, #0878f9) !important;
  background-size: 180% 180% !important;
  animation: posGradientFlow 5.6s ease-in-out -2s infinite;
}

.pos-page .pos-btn-project {
  background: linear-gradient(135deg, #0d47a1, #0878f9) !important;
}

.pos-page .pos-btn-project-list {
  background: linear-gradient(135deg, #00695c, #00a878) !important;
}

.pos-action-panel,
.pos-metrics-panel,
.pos-summary-panel,
.pos-tools-panel {
  margin-top: 8px;
  padding-top: 13px;
  padding-bottom: 13px;
  border: 1px solid var(--pos-border);
  border-radius: 17px;
  background: rgba(255, 255, 255, 0.93);
  box-shadow: 0 9px 24px rgba(21, 57, 91, 0.08);
}

.pos-page .table-footer {
  overflow: hidden;
  margin-bottom: 0;
  border: 0 !important;
  border-radius: 13px;
  background: #fff;
  box-shadow: none;
}

.pos-page .table-footer td {
  padding: 10px;
  border-color: #e7eef5;
  vertical-align: middle;
}

.pos-page .pos-metrics-panel .table-footer tr:nth-child(odd) td {
  color: #dff8ff;
  background: linear-gradient(135deg, #0b3652, #0b5963);
  font-size: 0.75rem;
  font-weight: 750;
  text-transform: uppercase;
  letter-spacing: 0.045em;
}

.pos-page .pos-metrics-panel .table-footer h3 {
  margin: 0;
  color: var(--pos-blue);
  font-weight: 850;
}

.pos-page .pos-summary-panel .table-danger {
  color: #9e1832;
  background: #fff0f3;
}

.pos-page .pos-summary-panel .table-success {
  color: #08714c;
  background: #e9fff6;
}

.pos-page .custom-checkbox .custom-control-label::before,
.pos-page .custom-radio .custom-control-label::before {
  border-color: #acc2d3;
}

.pos-page .custom-control-input:checked ~ .custom-control-label::before {
  border-color: var(--pos-blue);
  background-color: var(--pos-blue);
  box-shadow: 0 0 0 3px rgba(8, 120, 249, 0.12);
}

.pos-page .badge {
  padding: 0.48em 0.72em;
  border-radius: 8px;
  letter-spacing: 0.025em;
}

.pos-page ::-webkit-scrollbar {
  width: 9px;
  height: 9px;
}

.pos-page ::-webkit-scrollbar-track {
  border-radius: 999px;
  background: #e8f0f7;
}

.pos-page ::-webkit-scrollbar-thumb {
  border: 2px solid #e8f0f7;
  border-radius: 999px;
  background: linear-gradient(var(--pos-blue), var(--pos-cyan));
}

@keyframes posFadeUp {
  from { opacity: 0; transform: translateY(12px); }
  to { opacity: 1; transform: translateY(0); }
}

@keyframes posPulse {
  0%, 100% { transform: scale(1); opacity: 1; }
  50% { transform: scale(0.72); opacity: 0.72; }
}

@keyframes posPayGlow {
  0%, 100% { box-shadow: 0 13px 30px rgba(22, 199, 132, 0.32), 0 0 0 7px rgba(22, 199, 132, 0.08); }
  50% { box-shadow: 0 15px 34px rgba(0, 184, 201, 0.38), 0 0 0 10px rgba(0, 184, 201, 0.06); }
}

@keyframes posGradientFlow {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}

@keyframes posTabGlow {
  0%, 100% { box-shadow: 0 9px 22px rgba(0, 163, 224, 0.28); }
  50% { box-shadow: 0 11px 28px rgba(0, 199, 232, 0.48); }
}

@media (max-width: 1199.98px) {
  .body.pos-page { padding: 8px !important; }
  .pos-page .pos-workspace { padding: 8px !important; }
  .pos-page .pos-product-table { min-width: 1060px; }
}

@media (min-width: 921px) and (max-width: 1199.98px) {
  .pos-page .pos-customer-panel {
    flex: 0 0 100%;
    max-width: 100%;
  }

  .pos-page .pos-loyalty-panel {
    flex: 0 0 60%;
    max-width: 60%;
  }

  .pos-page .pos-document-panel {
    flex: 0 0 40%;
    max-width: 40%;
  }
}

@media (min-width: 768px) and (max-width: 920px) {
  .pos-page .pos-customer-panel,
  .pos-page .pos-document-panel {
    flex: 0 0 100%;
    max-width: 100%;
  }

  .pos-page .pos-document-panel {
    display: grid;
    grid-template-columns: minmax(190px, 0.8fr) minmax(230px, 1.2fr);
    gap: 12px 20px;
    align-items: center;
  }
}

@media (max-width: 767.98px) {
  .body.pos-page { padding: 4px !important; }
  .pos-page .pos-shell.card,
  .pos-checkout-form { border-radius: 13px; }
  .pos-page .pos-tabs > .card-header { padding: 6px; }
  .pos-page .pos-tabs .nav-pills { flex-wrap: nowrap; overflow-x: auto; }
  .pos-page .pos-tabs .nav-pills .nav-link { min-width: 88px; padding: 8px 10px; }
  .pos-page .pos-workspace { padding: 5px !important; }
  .pos-sale-grid { margin-right: -4px; margin-left: -4px; }
  .pos-sale-grid > [class*="col-"] { padding-right: 4px; padding-left: 4px; }
  .pos-page .pos-customer-panel {
    order: 1;
    flex: 0 0 100%;
    max-width: 100%;
    padding: 8px 8px 0;
  }
  .pos-page .pos-loyalty-panel,
  .pos-page .pos-document-panel { display: none !important; }
  .pos-page .pos-cart-panel {
    order: 2;
    flex: 0 0 100%;
    max-width: 100%;
    padding: 7px !important;
    overflow: hidden;
  }
  .pos-guarantee-strip { flex-wrap: wrap; gap: 5px 9px; padding: 7px 9px; }
  .pos-guarantee-strip label { flex: 0 0 100%; }
  .pos-page .pos-product-table { min-width: 900px; font-size: 0.76rem; }
  .pos-page .pos-search-column + .pos-search-column { margin-top: 8px; }
  .pos-checkout-area { margin-top: 6px; }
  .pos-checkout-form { padding: 7px; }
  .pos-page .pos-pay-button { width: 100%; min-height: 56px; margin-right: 0; margin-left: 0; }
  .pos-page .btnAction { width: 100%; margin-right: 0; margin-left: 0; }
  .pos-action-panel,
  .pos-summary-panel,
  .pos-tools-panel { margin-top: 10px; }
}

@media (max-width: 575.98px) {
  .body.pos-page {
    padding: 2px !important;
    background: linear-gradient(160deg, #f8fcff, #edf6ff 58%, #f8f5ff) !important;
  }

  .pos-page .pos-shell.card,
  .pos-checkout-form {
    border-radius: 11px;
  }

  .pos-page .pos-tabs .nav-pills .nav-link {
    min-width: 82px;
    padding: 7px 9px;
    border-radius: 9px;
    font-size: 0.78rem;
  }

  .pos-page .pos-workspace { padding: 3px !important; }
  .pos-customer-panel { border-radius: 12px; }
  .pos-page .form-control,
  .pos-page .custom-select,
  .pos-page .input-group-text { min-height: 42px; }
  .pos-page .pos-search-group .form-control,
  .pos-page .pos-search-group .btn { min-height: 44px; }
  .pos-page .pos-product-table { min-width: 820px; }
  .pos-page .btnAction { min-height: 44px; font-size: 0.77rem !important; }
  .pos-page .pos-pay-button { min-height: 54px; font-size: 1rem !important; }

  .swal2-container {
    padding: 10px !important;
  }

  .swal2-popup,
  .swal2-modal {
    width: calc(100vw - 20px) !important;
    max-width: calc(100vw - 20px) !important;
    padding: 1.25rem 0.8rem 0 !important;
  }

  .swal2-title {
    width: 100% !important;
    max-width: 100% !important;
    margin-right: auto !important;
    margin-left: auto !important;
    padding: 0 0.4rem !important;
    font-size: 1.15rem !important;
    line-height: 1.2 !important;
    white-space: normal !important;
    overflow-wrap: anywhere;
  }

  .swal2-content {
    padding: 0 0.25rem !important;
    font-size: 0.94rem !important;
    overflow-wrap: anywhere;
  }
}

/* POS SIGNATURE COMPACT: mayor información útil con menor altura visual. */
.body.pos-page {
  --pos-blue: #2867f6;
  --pos-cyan: #08d6ec;
  --pos-green: #18c991;
  --pos-purple: #7957ff;
  --pos-accent: #f6386d;
  padding: 7px 10px 20px !important;
}

.pos-page .pos-shell.card {
  border-radius: 18px;
  border-color: rgba(111, 222, 244, 0.24);
  box-shadow: 0 16px 42px rgba(7, 35, 61, 0.13), 0 0 0 1px rgba(255,255,255,.72) inset;
}

.pos-page .pos-tabs > .card-header {
  min-height: 46px;
  padding: 4px 8px;
  background:
    radial-gradient(circle at 12% -80%, rgba(8,214,236,.36), transparent 38%),
    radial-gradient(circle at 92% 160%, rgba(246,56,109,.22), transparent 42%),
    linear-gradient(112deg, #061725 0%, #092d47 52%, #25132e 100%);
  box-shadow: inset 0 -1px 0 rgba(77,226,247,.18);
}

.pos-page .pos-tabs .nav-pills {
  align-items: center;
  gap: 6px;
}

.pos-page .pos-tabs .nav-pills .nav-link {
  min-width: 96px;
  min-height: 36px;
  padding: 6px 12px;
  border-color: rgba(93,225,246,.22);
  border-radius: 10px;
  color: rgba(232,250,255,.86);
  background: linear-gradient(145deg, rgba(11,36,53,.86), rgba(34,18,40,.82));
  box-shadow: inset 0 1px 0 rgba(255,255,255,.08), 0 5px 13px rgba(0,5,16,.20);
  font-size: .78rem;
  line-height: 1.15;
}

.pos-page .pos-tabs .nav-pills .nav-link::before {
  margin-right: 6px;
  content: "\f07a";
  color: #72efff;
  font-family: FontAwesome;
  font-size: .78rem;
  text-shadow: 0 0 9px rgba(8,214,236,.62);
}

.pos-page .pos-tabs .nav-pills .nav-link:hover {
  border-color: rgba(119,238,255,.62);
  background: linear-gradient(125deg, rgba(8,173,215,.70), rgba(93,69,231,.64));
  box-shadow: 0 8px 18px rgba(10,139,203,.24), inset 0 1px 0 rgba(255,255,255,.20);
  transform: translateY(-1px);
}

.pos-page .pos-tabs .nav-pills .nav-link.active {
  border-color: rgba(213,250,255,.68);
  background: linear-gradient(120deg, #08cce8 0%, #2867f6 52%, #7957ff 100%);
  background-size: 180% 100%;
  box-shadow: 0 8px 21px rgba(33,116,245,.33), 0 0 16px rgba(8,214,236,.18), inset 0 1px 0 rgba(255,255,255,.38);
  animation: posCompactTabGlow 3.2s ease-in-out infinite, posGradientFlow 5s ease-in-out infinite;
}

.pos-page .pos-tabs .nav-pills .nav-link.active::before {
  color: #fff;
  text-shadow: 0 0 11px rgba(255,255,255,.72);
}

.pos-page .pos-workspace {
  padding: 7px !important;
}

.pos-sale-grid {
  align-items: stretch;
  margin-right: -5px;
  margin-left: -5px;
}

.pos-sale-grid > [class*="col-"] {
  padding-right: 5px;
  padding-left: 5px;
}

.pos-customer-panel,
.pos-loyalty-panel,
.pos-document-panel {
  margin-bottom: 7px;
  padding-top: 7px;
  border-radius: 14px;
}

.pos-customer-panel {
  padding-right: 8px;
  padding-bottom: 4px;
  padding-left: 8px;
  border-color: rgba(48,146,224,.18);
  background:
    radial-gradient(circle at 2% 0%, rgba(8,214,236,.09), transparent 15rem),
    linear-gradient(145deg, rgba(255,255,255,.98), rgba(246,251,255,.95));
  box-shadow: 0 8px 22px rgba(16,53,86,.075), inset 0 1px 0 #fff;
}

.pos-customer-fields-row {
  margin-right: -5px;
  margin-left: -5px;
}

.pos-meta-field,
.pos-client-block {
  padding-right: 5px;
  padding-left: 5px;
}

.pos-meta-field {
  --field-accent: 40,103,246;
}

.pos-meta-store {
  --field-accent: 24,201,145;
}

.pos-meta-terminal {
  --field-accent: 121,87,255;
}

.pos-meta-field > .form-group {
  position: relative;
  min-height: 63px;
  padding: 6px 7px 7px 10px;
  overflow: hidden;
  border: 1px solid rgba(var(--field-accent),.17);
  border-radius: 11px;
  background: linear-gradient(145deg, rgba(255,255,255,.96), rgba(var(--field-accent),.045));
  box-shadow: inset 0 1px 0 #fff, 0 5px 12px rgba(7,45,75,.045);
}

.pos-meta-field > .form-group::before {
  position: absolute;
  top: 10px;
  bottom: 10px;
  left: 0;
  width: 3px;
  border-radius: 0 999px 999px 0;
  content: "";
  background: rgb(var(--field-accent));
  box-shadow: 0 0 10px rgba(var(--field-accent),.48);
}

.pos-field-label {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  margin-bottom: 3px !important;
  color: #29485e !important;
  font-size: .69rem !important;
  font-weight: 850 !important;
  letter-spacing: .025em;
}

.pos-field-label > i {
  color: var(--pos-blue);
  font-size: .72rem;
  text-shadow: 0 0 9px rgba(40,103,246,.18);
}

.pos-meta-store .pos-field-label > i { color: var(--pos-green); }
.pos-meta-terminal .pos-field-label > i { color: var(--pos-purple); }

.pos-page .pos-customer-panel .form-group {
  margin-bottom: 5px;
}

.pos-page .pos-customer-panel .form-control,
.pos-page .pos-customer-panel .custom-select,
.pos-page .pos-customer-panel .input-group .btn {
  min-height: 35px;
  height: 35px;
  border-radius: 9px;
  font-size: .83rem;
}

.pos-page .pos-customer-panel .input-group .btn {
  padding-top: 5px;
  padding-bottom: 5px;
}

.pos-client-block {
  margin-top: 3px;
}

.pos-client-block > .form-group {
  padding: 6px 7px 4px;
  border: 1px solid rgba(8,184,218,.14);
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(240,251,255,.76), rgba(248,246,255,.72));
}

.pos-guarantee-strip {
  min-height: 35px;
  margin-top: 5px;
  padding: 5px 9px;
  border-style: solid;
  border-color: rgba(40,103,246,.16);
  border-radius: 9px;
  background: linear-gradient(90deg, rgba(40,103,246,.05), rgba(8,214,236,.045), rgba(121,87,255,.04));
}

.pos-guarantee-strip label {
  margin-right: 10px !important;
  font-size: .68rem !important;
}

.pos-guarantee-strip .custom-control {
  margin-right: 10px;
  min-height: 20px;
}

.pos-guarantee-strip .custom-control-label {
  line-height: 1.35;
}

.pos-customer-panel .custom-checkbox {
  min-height: 24px;
  margin-top: -1px;
  font-size: .74rem;
}

.pos-panel-kicker {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  margin-bottom: 3px;
  color: #41637a;
  font-size: .62rem;
  font-weight: 900;
  text-transform: uppercase;
  letter-spacing: .10em;
}

.pos-panel-kicker i {
  color: var(--pos-purple);
}

.pos-loyalty-panel {
  padding: 8px 10px 7px;
  border-color: rgba(121,87,255,.18);
  background:
    radial-gradient(circle at 100% 0%, rgba(121,87,255,.16), transparent 10rem),
    linear-gradient(150deg, #fff, #f5f9ff);
  box-shadow: 0 8px 22px rgba(44,54,112,.075);
}

.pos-loyalty-panel .form-group {
  margin-bottom: 5px;
}

.pos-loyalty-panel .mt-4 {
  margin-top: 7px !important;
}

.pos-page .pos-loyalty-panel .puntos {
  font-size: clamp(2rem, 3vw, 2.65rem);
  line-height: .92;
}

.pos-page .pos-loyalty-panel .form-control {
  min-height: 36px;
  height: 36px;
  font-size: .83rem;
}

.pos-document-panel {
  padding: 8px 10px 7px;
  border-color: rgba(8,214,236,.22);
  background:
    radial-gradient(circle at 100% 0%, rgba(8,214,236,.19), transparent 10rem),
    linear-gradient(155deg, #061827 0%, #0a3551 64%, #122341 100%);
  box-shadow: 0 10px 24px rgba(5,31,56,.19), inset 0 1px 0 rgba(255,255,255,.08);
}

.pos-panel-kicker-light,
.pos-field-label-light {
  color: #cdefff !important;
}

.pos-panel-kicker-light i,
.pos-field-label-light > i {
  color: #5eeaff !important;
}

.pos-document-panel .form-group {
  margin-bottom: 7px;
}

.pos-document-panel .big-text {
  font-size: clamp(1.15rem, 1.75vw, 1.52rem);
  line-height: 1.05;
}

.pos-reference-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 6px 10px;
  border: 1px solid rgba(255,219,73,.28);
  border-radius: 999px;
  color: #fff5bd;
  background: linear-gradient(125deg, rgba(238,174,22,.88), rgba(255,91,49,.84));
  box-shadow: 0 7px 15px rgba(245,136,28,.19), inset 0 1px 0 rgba(255,255,255,.26);
  font-size: .65rem;
  font-weight: 850;
}

.pos-page .pos-document-panel .form-control {
  min-height: 36px;
  height: 36px;
  border-color: rgba(96,218,239,.24);
  color: #eafaff;
  background: rgba(255,255,255,.09);
}

.pos-cart-panel {
  padding: 8px !important;
  border-radius: 14px;
  box-shadow: 0 9px 25px rgba(21,57,91,.07);
}

@keyframes posCompactTabGlow {
  0%, 100% { box-shadow: 0 8px 21px rgba(33,116,245,.29), 0 0 13px rgba(8,214,236,.14), inset 0 1px 0 rgba(255,255,255,.34); }
  50% { box-shadow: 0 9px 24px rgba(91,75,241,.37), 0 0 18px rgba(8,214,236,.25), inset 0 1px 0 rgba(255,255,255,.42); }
}

@media (max-width: 920px) {
  .pos-meta-field > .form-group { min-height: 60px; }
  .pos-page .pos-tabs > .card-header { min-height: 44px; }
}

@media (max-width: 767.98px) {
  .body.pos-page { padding: 3px !important; }
  .pos-page .pos-tabs > .card-header { padding: 4px; }
  .pos-page .pos-tabs .nav-pills .nav-link {
    min-width: 84px;
    min-height: 34px;
    padding: 5px 8px;
    font-size: .73rem;
  }
  .pos-page .pos-workspace { padding: 4px !important; }
  .pos-customer-panel { padding: 6px 6px 2px; }
  .pos-meta-field > .form-group { min-height: 58px; padding: 5px 6px 6px 9px; }
  .pos-client-block > .form-group { padding: 5px 5px 3px; }
}

/* POS ULTRA COMPACT 2026: franja operativa premium, delgada y colorida. */
.pos-workspace {
  --pos-cyan: #23d7f4;
  --pos-blue: #3478ff;
  --pos-violet: #7557ff;
  --pos-mint: #27dda5;
  --pos-gold: #ffc85a;
  --pos-ink: #071d33;
  --pos-border: rgba(75, 136, 214, 0.22);
}

.pos-workspace .nav-tabs,
.pos-tabs-header {
  min-height: 40px !important;
  padding: 4px 7px !important;
  gap: 6px !important;
  border: 1px solid rgba(65, 202, 238, 0.23) !important;
  border-radius: 15px 15px 8px 8px !important;
  background:
    linear-gradient(105deg, rgba(5, 59, 82, 0.98), rgba(8, 30, 56, 0.98) 54%, rgba(58, 15, 54, 0.96)) !important;
  box-shadow: 0 8px 22px rgba(5, 33, 57, 0.14), inset 0 1px rgba(255, 255, 255, 0.08) !important;
  overflow: hidden;
}

.pos-workspace .nav-tabs::after,
.pos-tabs-header::after {
  content: "";
  position: absolute;
  inset: auto 2% 0;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--pos-cyan), var(--pos-violet), transparent);
  opacity: 0.8;
}

.pos-workspace .nav-tabs .nav-link,
.pos-tab {
  min-height: 30px !important;
  height: 30px !important;
  padding: 0 14px !important;
  border-radius: 10px !important;
  font-size: 13px !important;
  letter-spacing: 0.15px;
  border: 1px solid rgba(111, 213, 255, 0.28) !important;
  background: rgba(4, 30, 50, 0.58) !important;
  box-shadow: inset 0 1px rgba(255, 255, 255, 0.08) !important;
}

.pos-workspace .nav-tabs .nav-link.active,
.pos-tab.active {
  color: #fff !important;
  border-color: rgba(151, 231, 255, 0.82) !important;
  background: linear-gradient(110deg, #0aaee9 0%, #3478ff 50%, #7658ff 100%) !important;
  box-shadow: 0 5px 14px rgba(47, 115, 255, 0.28), inset 0 1px rgba(255, 255, 255, 0.38) !important;
}

.pos-workspace .tab-content,
.pos-workspace .tab-pane {
  margin-top: 5px !important;
}

.pos-customer-fields-row {
  gap: 6px !important;
  padding: 6px !important;
  border-radius: 14px !important;
  background:
    linear-gradient(112deg, rgba(238, 251, 255, 0.96), rgba(247, 247, 255, 0.96)) !important;
  border: 1px solid var(--pos-border) !important;
  box-shadow: 0 7px 20px rgba(13, 54, 88, 0.07) !important;
}

.pos-meta-field {
  min-height: 64px !important;
  padding: 7px 9px 8px 12px !important;
  border-radius: 12px !important;
  border: 1px solid rgba(77, 142, 216, 0.19) !important;
  box-shadow: none !important;
  overflow: hidden;
}

.pos-meta-field::before {
  width: 3px !important;
  inset: 8px auto 8px 0 !important;
  border-radius: 0 6px 6px 0 !important;
}

.pos-meta-document {
  background: linear-gradient(135deg, rgba(240, 249, 255, 0.98), rgba(244, 247, 255, 0.96)) !important;
}

.pos-meta-store {
  background: linear-gradient(135deg, rgba(238, 255, 249, 0.98), rgba(244, 252, 255, 0.96)) !important;
}

.pos-meta-terminal {
  background: linear-gradient(135deg, rgba(247, 244, 255, 0.98), rgba(242, 249, 255, 0.96)) !important;
}

.pos-field-label {
  display: flex !important;
  align-items: center;
  gap: 6px;
  margin: 0 0 4px !important;
  color: #24445f !important;
  font-size: 11px !important;
  line-height: 1 !important;
  letter-spacing: 0.12px;
}

.pos-field-label i {
  font-size: 12px;
  color: var(--pos-blue);
  filter: drop-shadow(0 2px 4px rgba(52, 120, 255, 0.22));
}

.pos-meta-store .pos-field-label i { color: #13b982; }
.pos-meta-terminal .pos-field-label i { color: #7658ff; }

.pos-meta-field .form-control,
.pos-meta-field select {
  min-height: 34px !important;
  height: 34px !important;
  padding: 5px 12px !important;
  border-radius: 10px !important;
  border-color: rgba(73, 132, 196, 0.22) !important;
  background: rgba(247, 251, 255, 0.88) !important;
  box-shadow: inset 0 1px 2px rgba(9, 45, 76, 0.025) !important;
  font-size: 13px !important;
}

.pos-client-block {
  margin-top: 5px !important;
  padding: 7px !important;
  border-radius: 13px !important;
  border: 1px solid rgba(46, 168, 222, 0.18) !important;
  background: linear-gradient(120deg, rgba(244, 252, 255, 0.98), rgba(247, 250, 255, 0.96)) !important;
  box-shadow: none !important;
}

.pos-client-block .form-group {
  margin-bottom: 5px !important;
}

.pos-client-block .form-control,
.pos-client-block .btn,
.pos-client-block .input-group-text {
  min-height: 34px !important;
  height: 34px !important;
  font-size: 13px !important;
}

.pos-client-block .btn-primary {
  border: 0 !important;
  background: linear-gradient(110deg, #0dbde2, #2f7bff 62%, #7058ff) !important;
  box-shadow: 0 5px 12px rgba(43, 119, 247, 0.2) !important;
}

.pos-client-block .btn-primary:hover {
  transform: translateY(-1px);
  filter: saturate(1.13) brightness(1.04);
}

.pos-client-block .input-group-text,
.pos-client-block .btn-secondary {
  background: linear-gradient(145deg, #173a52, #356578) !important;
  border-color: transparent !important;
}

.pos-client-block .garantia,
.pos-client-block [class*="garantia"] {
  min-height: 32px !important;
  padding: 5px 10px !important;
  margin-top: 4px !important;
  border-radius: 10px !important;
  background: linear-gradient(90deg, rgba(225, 248, 255, 0.68), rgba(241, 237, 255, 0.74)) !important;
}

.pos-loyalty-panel {
  min-height: 100% !important;
  padding: 10px 12px !important;
  border-radius: 14px !important;
  background:
    radial-gradient(circle at 88% 12%, rgba(117, 87, 255, 0.14), transparent 35%),
    linear-gradient(145deg, #fbfdff, #f3f6ff) !important;
  border: 1px solid rgba(112, 91, 255, 0.19) !important;
  box-shadow: 0 7px 20px rgba(44, 55, 118, 0.08) !important;
}

.pos-loyalty-panel .pos-panel-kicker {
  margin-bottom: 0 !important;
  color: #4d6680 !important;
  font-size: 10px !important;
}

.pos-loyalty-panel .pos-points-number,
.pos-loyalty-panel .puntos,
.pos-loyalty-panel h2 {
  margin: 0 !important;
  font-size: 34px !important;
  line-height: 0.98 !important;
  color: #2f74f7 !important;
  text-shadow: 0 7px 18px rgba(47, 116, 247, 0.2);
}

.pos-loyalty-panel .form-group {
  margin: 6px 0 0 !important;
}

.pos-loyalty-panel .form-control {
  min-height: 34px !important;
  height: 34px !important;
  border-radius: 10px !important;
  font-size: 13px !important;
}

.pos-document-panel {
  min-height: 100% !important;
  padding: 10px 12px !important;
  border-radius: 14px !important;
  background:
    radial-gradient(circle at 95% 0%, rgba(37, 215, 244, 0.18), transparent 42%),
    linear-gradient(155deg, #061e36, #0b3550 58%, #172754) !important;
  border: 1px solid rgba(67, 219, 245, 0.25) !important;
  box-shadow: 0 10px 24px rgba(5, 31, 55, 0.15), inset 0 1px rgba(255, 255, 255, 0.06) !important;
}

.pos-document-panel .pos-panel-kicker,
.pos-document-panel label {
  margin-bottom: 4px !important;
  color: #dff8ff !important;
  font-size: 10px !important;
}

.pos-document-panel h2,
.pos-document-panel h3 {
  margin: 2px 0 !important;
  font-size: 24px !important;
  line-height: 1 !important;
  color: #fff !important;
}

.pos-document-panel .form-control {
  min-height: 34px !important;
  height: 34px !important;
  border-radius: 10px !important;
  border-color: rgba(117, 218, 239, 0.25) !important;
  background: rgba(255, 255, 255, 0.1) !important;
  color: #fff !important;
}

.pos-reference-badge {
  min-height: 27px !important;
  padding: 5px 10px !important;
  border: 1px solid rgba(255, 221, 122, 0.46) !important;
  border-radius: 999px !important;
  background: linear-gradient(110deg, #ffb70b, #f27631 70%, #e64562) !important;
  box-shadow: 0 5px 14px rgba(238, 128, 34, 0.24) !important;
  font-size: 10px !important;
}

@media (max-width: 920px) {
  .pos-customer-fields-row { padding: 5px !important; }
  .pos-meta-field { min-height: 60px !important; }
  .pos-loyalty-panel,
  .pos-document-panel { min-height: 124px !important; }
}

/* CORRECCION ULTRA SLIM: conserva las 3 columnas en una sola linea y evita paneles altos. */
.pos-page .pos-tabs > .card-header {
  min-height: 34px !important;
  padding: 3px 6px !important;
  border-bottom: 1px solid rgba(71, 226, 245, 0.28) !important;
  background:
    linear-gradient(105deg, #063d52 0%, #09263f 48%, #35132f 100%) !important;
  box-shadow: inset 0 -1px rgba(78, 221, 245, 0.16), 0 5px 14px rgba(5, 31, 52, 0.1) !important;
}

.pos-page .pos-tabs .nav-pills {
  min-height: 28px !important;
  gap: 4px !important;
}

.pos-page .pos-tabs .nav-pills .nav-link {
  min-width: 84px !important;
  min-height: 28px !important;
  height: 28px !important;
  padding: 3px 10px !important;
  border-radius: 9px !important;
  font-size: 0.72rem !important;
  line-height: 20px !important;
}

.pos-page .pos-tabs .nav-pills .nav-link::before {
  margin-right: 5px !important;
  content: "\f07a" !important;
  font-family: "Font Awesome 5 Free", FontAwesome, sans-serif !important;
  font-size: 0.67rem !important;
  font-weight: 900 !important;
}

.pos-page .pos-workspace {
  padding: 3px !important;
}

.pos-sale-grid {
  margin-right: -3px !important;
  margin-left: -3px !important;
}

.pos-sale-grid > [class*="col-"] {
  padding-right: 3px !important;
  padding-left: 3px !important;
}

.pos-customer-panel,
.pos-loyalty-panel,
.pos-document-panel {
  min-height: 0 !important;
  margin-bottom: 3px !important;
  padding-top: 5px !important;
  border-radius: 11px !important;
}

.pos-customer-panel {
  padding-right: 4px !important;
  padding-bottom: 1px !important;
  padding-left: 4px !important;
  background:
    linear-gradient(135deg, rgba(247, 253, 255, 0.99), rgba(244, 247, 255, 0.97)) !important;
}

.pos-customer-fields-row {
  gap: 0 !important;
  margin-right: -3px !important;
  margin-left: -3px !important;
  padding: 0 !important;
  border: 0 !important;
  border-radius: 0 !important;
  background: transparent !important;
  box-shadow: none !important;
}

@media (min-width: 768px) {
  .pos-customer-fields-row {
    flex-wrap: wrap !important;
  }

  .pos-customer-fields-row > .pos-meta-field {
    flex: 0 0 33.333333% !important;
    max-width: 33.333333% !important;
  }

  .pos-customer-fields-row > .pos-client-block {
    flex: 0 0 100% !important;
    max-width: 100% !important;
  }
}

.pos-meta-field {
  min-height: 0 !important;
  padding: 0 3px !important;
  overflow: visible !important;
  border: 0 !important;
  border-radius: 0 !important;
  background: transparent !important;
  box-shadow: none !important;
}

.pos-meta-field > .form-group {
  min-height: 50px !important;
  margin-bottom: 3px !important;
  padding: 4px 6px 5px 9px !important;
  border-radius: 9px !important;
  box-shadow: inset 0 1px rgba(255, 255, 255, 0.86), 0 3px 9px rgba(15, 55, 88, 0.035) !important;
}

.pos-meta-document .lblSerie {
  display: none !important;
  width: 0 !important;
  height: 0 !important;
  margin: 0 !important;
  padding: 0 !important;
  line-height: 0 !important;
}

.pos-meta-document > .form-group > .input-group {
  margin-top: 0 !important;
}

.pos-meta-document > .form-group {
  border-color: rgba(30, 137, 247, 0.22) !important;
  background: linear-gradient(135deg, #f6fbff, #edf5ff) !important;
}

.pos-meta-store > .form-group {
  border-color: rgba(20, 190, 134, 0.23) !important;
  background: linear-gradient(135deg, #f3fffa, #eafbf7) !important;
}

.pos-meta-terminal > .form-group {
  border-color: rgba(118, 88, 255, 0.22) !important;
  background: linear-gradient(135deg, #faf8ff, #f0efff) !important;
}

.pos-meta-field > .form-group::before {
  top: 7px !important;
  bottom: 7px !important;
  width: 2px !important;
}

.pos-field-label {
  gap: 4px !important;
  margin: 0 0 3px !important;
  font-size: 0.64rem !important;
  line-height: 1 !important;
}

.pos-field-label i {
  font-size: 0.66rem !important;
}

.pos-page .pos-customer-panel .form-control,
.pos-page .pos-customer-panel .custom-select,
.pos-page .pos-customer-panel .input-group .btn,
.pos-meta-field .form-control,
.pos-meta-field select {
  min-height: 30px !important;
  height: 30px !important;
  padding-top: 3px !important;
  padding-bottom: 3px !important;
  border-radius: 8px !important;
  font-size: 0.78rem !important;
}

.pos-client-block {
  min-height: 0 !important;
  margin-top: 1px !important;
  padding: 0 3px !important;
  border: 0 !important;
  border-radius: 0 !important;
  background: transparent !important;
}

.pos-client-block > .form-group {
  margin-bottom: 2px !important;
  padding: 4px 5px 2px !important;
  border-radius: 9px !important;
  background: linear-gradient(120deg, rgba(239, 251, 255, 0.78), rgba(247, 244, 255, 0.75)) !important;
}

.pos-client-block .form-group {
  margin-bottom: 2px !important;
}

.pos-client-block .form-control,
.pos-client-block .btn,
.pos-client-block .input-group-text {
  min-height: 30px !important;
  height: 30px !important;
  font-size: 0.78rem !important;
}

.pos-guarantee-strip {
  min-height: 27px !important;
  margin-top: 3px !important;
  padding: 3px 7px !important;
  border-radius: 8px !important;
}

.pos-guarantee-strip label {
  margin-right: 7px !important;
  font-size: 0.63rem !important;
}

.pos-guarantee-strip .custom-control {
  min-height: 17px !important;
  margin-right: 7px !important;
  font-size: 0.68rem !important;
}

.pos-customer-panel .custom-checkbox {
  min-height: 20px !important;
  margin-top: -2px !important;
  font-size: 0.69rem !important;
}

.pos-loyalty-panel {
  padding: 6px 8px 5px !important;
  background:
    radial-gradient(circle at 96% 0%, rgba(117, 87, 255, 0.18), transparent 52%),
    linear-gradient(145deg, #fbfdff, #f3f2ff) !important;
}

.pos-loyalty-panel .pos-panel-kicker {
  font-size: 0.58rem !important;
}

.pos-loyalty-panel .form-group {
  margin: 2px 0 3px !important;
}

.pos-page .pos-loyalty-panel .puntos,
.pos-loyalty-panel .puntos,
.pos-loyalty-panel h2 {
  margin: 0 !important;
  font-size: 1.72rem !important;
  line-height: 0.92 !important;
}

.pos-loyalty-panel .mt-4 {
  margin-top: 4px !important;
}

.pos-loyalty-panel .form-control {
  min-height: 30px !important;
  height: 30px !important;
  font-size: 0.78rem !important;
}

.pos-document-panel {
  padding: 6px 8px 5px !important;
  background:
    radial-gradient(circle at 100% 0%, rgba(35, 215, 244, 0.22), transparent 48%),
    linear-gradient(150deg, #061c31, #083b54 58%, #17264c) !important;
}

.pos-document-panel .pos-panel-kicker,
.pos-document-panel label {
  margin-bottom: 2px !important;
  font-size: 0.58rem !important;
}

.pos-document-panel .form-group {
  margin-bottom: 4px !important;
}

.pos-document-panel .big-text,
.pos-document-panel h2,
.pos-document-panel h3 {
  margin: 1px 0 !important;
  font-size: 1.12rem !important;
  line-height: 1 !important;
}

.pos-reference-badge {
  min-height: 23px !important;
  padding: 3px 8px !important;
  font-size: 0.58rem !important;
}

.pos-document-panel .form-control {
  min-height: 30px !important;
  height: 30px !important;
  border-radius: 8px !important;
  font-size: 0.76rem !important;
}

@media (max-width: 920px) and (min-width: 768px) {
  .pos-meta-field,
  .pos-meta-field > .form-group,
  .pos-loyalty-panel,
  .pos-document-panel {
    min-height: 0 !important;
  }
}

@media (prefers-reduced-motion: reduce) {
  .pos-page *,
  .pos-page *::before,
  .pos-page *::after {
    scroll-behavior: auto !important;
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Controles de cliente visibles, compactos y orientados a privacidad. */
.pos-page .pos-client-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  min-height: 24px;
  gap: 8px;
  margin-bottom: 3px;
}

.pos-page .pos-client-toolbar .pos-field-label {
  margin: 0 !important;
  color: #16415e !important;
}

.pos-client-toolbar-actions {
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.pos-client-edit,
.pos-client-edit > div {
  display: inline-flex;
  margin: 0;
}

.pos-page .pos-client-icon-button.btn {
  display: inline-grid !important;
  width: 29px !important;
  min-width: 29px !important;
  height: 27px !important;
  min-height: 27px !important;
  place-items: center;
  padding: 0 !important;
  overflow: hidden;
  border: 1px solid rgba(66, 185, 226, 0.48) !important;
  border-radius: 9px !important;
  color: #fff !important;
  box-shadow: 0 5px 12px rgba(19, 73, 120, 0.18), inset 0 1px 0 rgba(255,255,255,.23) !important;
  transition: transform 180ms ease, filter 180ms ease, box-shadow 180ms ease !important;
}

.pos-page .pos-client-icon-button.btn:hover,
.pos-page .pos-client-icon-button.btn:focus {
  color: #fff !important;
  filter: brightness(1.12);
  transform: translateY(-1px) scale(1.04);
  box-shadow: 0 7px 16px rgba(31, 111, 196, 0.28), 0 0 0 3px rgba(61, 211, 241, 0.12) !important;
}

.pos-page .pos-client-edit-button.btn {
  background: linear-gradient(135deg, #0a9fbd 0%, #2877e8 58%, #6657e8 100%) !important;
}

.pos-page .pos-client-privacy-button.btn {
  background: linear-gradient(135deg, #1b5375 0%, #243e70 54%, #55336f 100%) !important;
}

.pos-page .pos-client-privacy-button.btn.is-private {
  border-color: rgba(248, 181, 64, 0.72) !important;
  background: linear-gradient(135deg, #d28a12 0%, #e76835 56%, #d93c67 100%) !important;
  box-shadow: 0 6px 15px rgba(221, 103, 44, 0.26), 0 0 0 3px rgba(255, 190, 71, 0.12) !important;
}

.pos-client-private-state {
  display: flex;
  align-items: center;
  min-height: 30px;
  gap: 9px;
  padding: 4px 11px;
  border: 1px solid rgba(95, 119, 200, 0.3);
  border-radius: 9px;
  color: #38516b;
  background: linear-gradient(110deg, #eef8fb 0%, #f2efff 70%, #fff5ea 100%);
  box-shadow: inset 0 1px 0 #fff;
}

.pos-client-private-state > i { color: #5c62d8; font-size: .9rem; }
.pos-client-private-state span,
.pos-client-private-state strong,
.pos-client-private-state small { display: block; }
.pos-client-private-state strong { font-size: .67rem; line-height: 1; }
.pos-client-private-state small { margin-top: 2px; color: #7890a3; font-size: .53rem; }

/* Icono del comprobante con presencia visual propia. */
.pos-page .pos-document-type-icon {
  border-color: rgba(65, 220, 246, 0.58);
  background: linear-gradient(140deg, #00bad7 0%, #3476ef 58%, #7159ed 100%);
  box-shadow: 0 6px 14px rgba(38, 116, 226, 0.3), inset 0 1px 0 rgba(255,255,255,.3);
  font-size: .82rem;
}

.pos-page .pos-meta-inline-card:hover .pos-document-type-icon {
  box-shadow: 0 7px 17px rgba(27, 164, 226, 0.38), 0 0 12px rgba(79, 209, 244, .28), inset 0 1px 0 rgba(255,255,255,.34);
  transform: rotate(-3deg) scale(1.05);
}

/* Organización activa con color vivo y lectura inmediata. */
.pos-page .pos-organization-button.btn.is-active {
  border-color: rgba(83, 245, 221, 0.88) !important;
  color: #fff !important;
  background: linear-gradient(110deg, #00a77d 0%, #00a9d4 48%, #5a54e8 100%) !important;
  box-shadow: 0 7px 18px rgba(0, 167, 190, 0.3), 0 0 0 3px rgba(52, 231, 211, 0.11), inset 0 1px 0 rgba(255,255,255,.32) !important;
}

.pos-page .pos-organization-button.btn.is-active .pos-organization-switch {
  border-color: rgba(255,255,255,.66);
  background: rgba(0, 54, 72, .48);
  box-shadow: inset 0 1px 4px rgba(0,0,0,.18), 0 0 10px rgba(76,255,220,.28);
}

.pos-page .pos-organization-button.btn.is-active .pos-organization-copy strong i {
  color: #8effe9;
  text-shadow: 0 0 8px rgba(108,255,231,.6);
}

/* El ojo pliega todo el bloque operativo del cliente y conserva un acceso mínimo. */
.pos-page .pos-client-block.is-client-collapsed > .form-group {
  min-height: 34px !important;
  padding: 4px 6px !important;
  border-color: rgba(91, 103, 225, 0.32) !important;
  background: linear-gradient(105deg, rgba(235, 248, 252, 0.96), rgba(241, 237, 255, 0.96)) !important;
}

.pos-page .pos-client-block.is-client-collapsed .pos-client-toolbar {
  min-height: 25px;
  margin: 0;
}

.pos-page .pos-client-block.is-client-collapsed .pos-field-label {
  color: #5860a9 !important;
  letter-spacing: 0.025em;
}

.pos-page .pos-client-block.is-client-collapsed .pos-field-label i {
  margin-right: 5px;
  color: #675de5;
}

/* Buscador de productos: una sola línea, controles consistentes y escáner destacado. */
.pos-page .pos-search-toolbar {
  align-items: flex-start !important;
  padding: 3px;
  border: 1px solid rgba(67, 196, 229, 0.2);
  border-radius: 14px;
  background: linear-gradient(105deg, rgba(244, 252, 255, 0.92), rgba(247, 245, 255, 0.92));
  box-shadow: inset 0 1px 0 #fff, 0 7px 18px rgba(22, 78, 119, 0.06);
}

.pos-page .pos-search-group {
  margin: 0 !important;
}

.pos-page .pos-product-search-control {
  display: flex !important;
  flex-wrap: nowrap !important;
  align-items: stretch;
  min-width: 0;
  overflow: visible;
  border: 1px solid rgba(56, 142, 219, 0.27);
  border-radius: 12px;
  background: #f9fcff;
  box-shadow: 0 5px 14px rgba(32, 78, 124, 0.07), inset 0 1px 0 #fff;
  transition: border-color 180ms ease, box-shadow 180ms ease, transform 180ms ease;
}

.pos-page .pos-product-search-control:focus-within {
  z-index: 20;
  border-color: rgba(27, 199, 228, 0.78);
  box-shadow: 0 7px 18px rgba(20, 164, 213, 0.17), 0 0 0 3px rgba(40, 210, 233, 0.11);
  transform: translateY(-1px);
}

.pos-page .pos-product-search-control > .autocomplete-wrapper {
  flex: 1 1 0 !important;
  width: auto !important;
  min-width: 0 !important;
}

/* El autocompletado heredado intenta ocupar el 100%; el micrófono se ancla dentro del campo. */
.pos-page .pos-product-name-search {
  position: relative !important;
  display: block !important;
  width: 100%;
  min-height: 48px;
}

.pos-page .pos-product-name-search > .autocomplete-wrapper {
  display: block !important;
  width: 100% !important;
  min-width: 100% !important;
}

.pos-page .pos-product-name-search .autocomplete-input {
  padding-right: 62px !important;
  border-radius: 11px !important;
}

.pos-page .pos-product-name-search > .pos-search-voice.btn {
  position: absolute !important;
  z-index: 12;
  top: 0;
  right: 0;
  bottom: 0;
  width: 54px;
  height: 48px;
  min-height: 48px;
  border-radius: 0 11px 11px 0 !important;
}

.pos-page .pos-product-search-control > .autocomplete-wrapper .autocomplete-input,
.pos-page .pos-product-search-control > input.form-control {
  width: 100% !important;
  min-width: 0 !important;
  min-height: 48px !important;
  border: 0 !important;
  border-radius: 11px 0 0 11px !important;
  background: transparent !important;
  box-shadow: none !important;
}

.pos-page .pos-product-search-control > .input-group-append {
  display: flex;
  flex: 0 0 auto;
  margin-left: 0;
}

.pos-page .pos-product-search-control .pos-search-action.btn {
  min-width: 50px;
  min-height: 48px;
  margin: 0 !important;
  border: 0 !important;
  border-left: 1px solid rgba(255,255,255,.2) !important;
  border-radius: 0 !important;
  color: #fff !important;
  box-shadow: none !important;
}

.pos-page .pos-product-search-control .pos-search-action.btn:last-of-type {
  border-radius: 0 11px 11px 0 !important;
}

.pos-page .pos-product-search-control .pos-search-voice {
  background: linear-gradient(145deg, #263b99, #5a42d6) !important;
}

.pos-page .pos-product-search-control .pos-search-barcode {
  background: linear-gradient(145deg, #04a878, #18cf8b) !important;
  text-shadow: 0 1px 3px rgba(0,0,0,.2);
}

.pos-page .pos-product-search-control .pos-search-submit {
  background: linear-gradient(145deg, #087fdc, #3859f0) !important;
}

.pos-page .pos-product-search-control .pos-search-action:hover,
.pos-page .pos-product-search-control .pos-search-action:focus {
  z-index: 2;
  filter: brightness(1.13) saturate(1.08);
  transform: none !important;
}

.pos-page .pos-barcode-search-control:focus-within::before {
  content: "LECTOR LISTO";
  position: absolute;
  top: -9px;
  right: 12px;
  z-index: 3;
  padding: 2px 7px;
  border-radius: 999px;
  color: #fff;
  background: linear-gradient(90deg, #00a77d, #2476e8);
  box-shadow: 0 3px 8px rgba(18, 135, 171, .22);
  font-size: .48rem;
  font-weight: 900;
  letter-spacing: .08em;
}

.pos-page .pos-search-hint {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  min-height: 20px;
  margin: 5px 0 0 2px !important;
  padding: 2px 8px;
  border-radius: 999px;
  color: #536a7f;
  background: rgba(230, 241, 249, .8);
  font-size: .61rem;
  font-weight: 750;
}

/* Los modales de imagen y edición deben ocupar la ventana completa, no quedar
   atrapados por el recuadro superior. */
.pos-page .pos-shell.card {
  overflow: visible !important;
  animation: none !important;
  transform: none !important;
  backdrop-filter: none !important;
}

.pos-page .pos-tabs,
.pos-page .pos-tabs > .card-body,
.pos-page .pos-tabs .tab-content,
.pos-page .pos-tabs .tab-pane,
.pos-page .pos-workspace,
.pos-page .pos-sale-grid,
.pos-page .pos-cart-panel,
.pos-page .pos-cart-component {
  overflow: visible !important;
}

.pos-page .pos-checkout-form {
  backdrop-filter: none !important;
}

.body.pos-page {
  padding-bottom: 20px !important;
}

/* Pago dentro del recuadro, inmediatamente después del listado como en IMCEDEN. */
.pos-page .pos-cart-panel {
  position: relative;
  margin-bottom: 6px;
  padding: 4px !important;
}

.pos-cart-pay-row {
  position: relative;
  z-index: 2;
  display: flex;
  justify-content: center;
  width: 100%;
  margin: 0;
  padding: 10px 7px 14px;
  transform: none;
  pointer-events: none;
}

.pos-page .imceden-pay-button.btn {
  position: relative !important;
  z-index: 2 !important;
  display: grid !important;
  align-items: center;
  gap: 8px;
  grid-template-columns: 44px minmax(125px, 1fr) auto;
  width: 430px !important;
  min-width: 430px !important;
  max-width: calc(100% - 24px) !important;
  min-height: 60px;
  margin: 0 auto !important;
  padding: 6px 10px !important;
  overflow: visible;
  border: 2px solid rgba(101, 237, 176, .9) !important;
  border-radius: 999px !important;
  color: #fff !important;
  background: linear-gradient(105deg, #00a36c, #05b878 54%, #24cf6f) !important;
  box-shadow: 0 8px 0 #087b5c, 0 15px 28px rgba(0, 163, 108, .34), 0 0 0 8px rgba(36, 207, 111, .14), inset 0 1px 0 rgba(255,255,255,.46) !important;
  font-size: 1.22rem !important;
  font-weight: 800 !important;
  letter-spacing: .02em;
  animation: none !important;
  transform: none !important;
  pointer-events: auto;
}

.pos-page .imceden-pay-label {
  display: block;
  padding-left: 4px;
  font-size: 1.2rem;
  font-weight: 900;
  letter-spacing: .08em;
  text-align: center;
  text-transform: uppercase;
  white-space: nowrap;
}

.pos-page .imceden-pay-button.btn > .fa {
  position: static !important;
  display: inline-flex;
  width: 44px;
  height: 44px;
  margin: 0 !important;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255, 255, 255, .24);
  border-radius: 50%;
  background: rgba(255, 255, 255, .12);
  font-size: 1.12rem;
}

.pos-page .imceden-pay-amount {
  min-width: 112px;
  padding: 10px 13px;
  border: 0;
  border-left: 1px solid rgba(255, 255, 255, .38);
  border-radius: 0;
  background: transparent;
  box-shadow: none;
  font-size: 1.08rem;
  font-weight: 900;
  line-height: 1;
  text-align: center;
  text-shadow: 0 1px 3px rgba(0, 40, 25, .28);
  white-space: nowrap;
}

.pos-page .imceden-pay-button.btn::before,
.pos-page .imceden-pay-button.btn::after {
  display: none !important;
}

.pos-page .imceden-pay-button.btn:hover:not(:disabled),
.pos-page .imceden-pay-button.btn:focus:not(:disabled) {
  color: #fff !important;
  background: linear-gradient(135deg, #079856 0%, #10ba70 100%) !important;
  box-shadow: 0 8px 0 #076d51, 0 18px 36px rgba(5, 79, 53, .38), 0 0 0 9px rgba(33, 213, 151, .16) !important;
  filter: brightness(1.04);
  transform: translateY(-2px) !important;
}

/* Perfil de rendimiento POS: conserva los colores premium, pero evita trabajo
   continuo de GPU en equipos modestos durante la facturación. */
.pos-page .pos-tabs .nav-pills .nav-link.active,
.pos-page .pos-btn-proforma,
.pos-page .pos-btn-window,
.pos-page .pos-btn-remove-window,
.pos-page .pos-btn-print,
.pos-page .pos-live-dot {
  animation: none !important;
}

.pos-page .pos-shell.card,
.pos-page .pos-checkout-form {
  -webkit-backdrop-filter: none !important;
  backdrop-filter: none !important;
}

.pos-page .btnAction::after {
  display: none !important;
}

.pos-page .btnAction,
.pos-page .pos-tabs .nav-pills .nav-link,
.pos-page .pos-product-search-control .pos-search-action.btn {
  transition-duration: .12s !important;
}

@media (max-width: 767.98px) {
  .pos-page .pos-search-toolbar {
    padding: 4px;
  }

  .pos-page .pos-search-column + .pos-search-column {
    margin-top: 7px;
  }

  .pos-page .pos-search-hint {
    display: none;
  }

  .pos-page .imceden-pay-button.btn {
    width: calc(100% - 16px) !important;
    min-width: 0 !important;
    min-height: 58px;
    padding: 7px 8px !important;
    border-radius: 999px !important;
    grid-template-columns: 36px minmax(0, 1fr) auto;
    gap: 7px;
    font-size: 1.08rem !important;
  }

  .pos-page .imceden-pay-button.btn > .fa {
    width: 34px;
    height: 34px;
  }

  .pos-cart-pay-row {
    transform: none;
  }

  .pos-page .imceden-pay-amount {
    min-width: 92px;
    padding: 8px 10px;
    font-size: .98rem;
  }
}

.ali-checkout-panel{margin:12px 16px;padding:14px 190px 14px 18px;border:2px solid #08a879;border-radius:14px;background:#effbf6;color:#173f4a;display:flex;flex-wrap:wrap;gap:8px 20px;align-items:center}
.ali-checkout-panel p{margin:4px 0;flex-basis:100%;font-size:13px}.ali-checkout-panel>div:first-child{flex:1;min-width:180px}.ali-checkout-total{font-size:26px;color:#067c60}.ali-checkout-panel details{flex-basis:100%;max-height:150px;overflow:auto}.ali-checkout-panel summary{cursor:pointer}.ali-checkout-actions{display:flex;gap:8px;flex-wrap:wrap}.ali-checkout-actions button{padding:9px 13px;border:0;border-radius:8px;background:#077c67;color:white;font-weight:700}.ali-checkout-actions button:disabled{opacity:.55}.ali-checkout-actions button:nth-child(2){background:#627783}@media(max-width:767px){.ali-checkout-panel{margin:8px;padding:12px}.ali-checkout-actions{width:100%}.ali-checkout-actions button{flex:1;font-size:12px}.ali-checkout-panel>div:first-child{padding-right:95px}.ali-checkout-total{flex-basis:100%}}
.ali-checkout-error{margin:10px 16px;padding:12px 16px;background:#fff2d9;border:1px solid #e4b866;border-radius:10px;color:#734600;font-size:14px}.ali-checkout-error button{margin-left:10px;border:0;border-radius:6px;background:#795c28;color:white;padding:5px 10px}
</style>

<style>
.pos-page>.quote-create-host{position:relative!important;z-index:15050!important}.quote-create-modal{z-index:15050!important}.quote-create-modal .modal-dialog{max-width:740px!important;width:calc(100vw - 30px)!important;min-width:0!important}.quote-create-modal .modal-content{border-radius:12px}.quote-create-modal .modal-body{max-height:78vh;overflow:auto}
</style>

<style>
.pos-page .pos-tabs .nav-pills .nav-item.pos-product-ai-launcher{margin-left:auto!important;flex:0 0 auto!important;display:flex;align-items:center;justify-content:flex-end;padding:0 3px}.pos-product-ai-search{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-width:64px;min-height:34px;padding:5px 11px;border:1px solid #64d3eb;border-radius:9px;background:linear-gradient(115deg,#087d98,#454598);color:#fff;font-size:12px;font-weight:800;line-height:1;box-shadow:0 2px 8px #00cbd522;cursor:pointer}.pos-product-ai-search:hover{background:#176a95;color:#fff}.pos-product-ai-search:focus-visible{outline:3px solid #8deaf2;outline-offset:2px}.pos-product-ai-search svg{flex-shrink:0}@media(max-width:600px){.pos-product-ai-search{min-width:56px;min-height:32px;padding:4px 8px;font-size:11px;gap:4px}}
</style>
