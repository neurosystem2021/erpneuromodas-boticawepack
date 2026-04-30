<template>
  <div :class="empresaClass">
    <!--<b-alert variant="warning" show class="text-center" v-if="this.$route.query.idPreOrden">Se está pagando la habitación HH</b-alert>-->

    <Menu v-if="false"></Menu>
<!-- {{ $store.state.proforma }} proforma
{{ $store.state.lstProductosProforma }} lstProductosProforma -->
    <b-container fluid class="ancho" id="conten">
      <div>
        <div>
          <b-button-group size="sm">
            <b-input-group prepend="Empresa:" class="mt-2">
              <b-form-select
              v-model="empresa"
              :options="$store.state.empresas"
              size="lg"
              class="form-control"
              value-field="IDEMPRESA"
              text-field="RAZONSOCIAL"
              @change="cambiarEmpresa"
              ></b-form-select>
            </b-input-group>
           
          </b-button-group>
        </div>  
      </div>
     
      <b-row>
        <b-col md="12" v-if="this.$route.query.idPedido">
          <div class="form-inline">
            <h4>
              Pago de delivery código:
              <b-badge variant="info">{{ this.$route.query.idPedido }}</b-badge>
            </h4>
          </div>
        </b-col>
      </b-row>
      <b-form @submit="guardarVenta" @keydown.enter="prevent">
        <b-row style="margin-bottom: -10px">
          <b-col md="7">
            <b-row>
              <b-col md="4">
                <b-form-group>
                  <b-input-group prepend="Doc." class="mt-2">
                    <autocomplete
                      required="required"
                      ref="inputTipoVenta"
                      :url="API + 'ventas/tipos'"
                      anchor="TipoDoc"
                      :initValue="TipoDocumento"
                      label="writer"
                      placeholder=""
                      :on-select="setTipoVenta"
                      :customParams="{ id_empresa: empresa }"
                      :onFocus="focusAutocomplete.bind(null, 'inputTipoVenta')"
                      :classes="{ input: 'form-control' }"
                    >
                    </autocomplete>
                  </b-input-group>
                </b-form-group>
                <b-form-group v-if="isNota">
                  <label>Doc. Referencia:</label>
                  <autocomplete
                    required="required"
                    ref="inputDocRef"
                    :url="
                      API +
                      'ventas/lista/' +
                      venta.puntoVenta.IdDocVentaPuntoVenta
                    "
                    :customParams="{ id_empresa: empresa }"
                    anchor="NroComprobante"
                    initValue=""
                    label="writer"
                    placeholder=""
                    :on-select="setDocRef"
                    :onFocus="focusAutocomplete.bind(null, 'inputDocRef')"
                    :classes="{ input: 'form-control' }"
                  >
                  </autocomplete>
                </b-form-group>
              </b-col>
              <b-col md="4" class="ocularMovil">
                <b-form-group>
                  <b-input-group prepend="Alm." class="mt-2">
                    <autocomplete
                      required="required"
                      ref="inputAlmacen"
                      :url="API + 'almacenes'"
                      anchor="Almacen"
                      initValue="PRINCIPAL"
                      label="writer"
                      placeholder=""
                      :on-select="setAlmacen"
                      :customParams="{ id_empresa: empresa }"
                      :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                      :classes="{ input: 'form-control' }"
                    >
                    </autocomplete>
                  </b-input-group>
                </b-form-group>
                <b-form-group v-if="isNota">
                  <label>Motivo :</label>
                  <b-form-select
                    v-model="venta.NotaIdMotivo"
                    :options="itemsMotivoNota"
                    v-on:change="setMotivo($event)"
                    class="mb-3"
                    size="md"
                    required
                  />
                </b-form-group>
              </b-col>
              <b-col md="4" class="ocularMovil">
                <b-form-group>
                  <b-input-group prepend="P.V." class="mt-2">
                    <autocomplete
                      required="required"
                      ref="inputPuntoVenta"
                      :url="API + 'ventas/puntos'"
                      anchor="PuntoVenta"
                      initValue="PRINCIPAL"
                      label="writer"
                      placeholder=""
                      :on-select="setPuntoVenta" 
                      :customParams="{ id_empresa: empresa }"
                      :onFocus="focusAutocomplete.bind(null, 'inputPuntoVenta')"
                      :classes="{ input: 'form-control' }"
                    >
                    </autocomplete>
                  </b-input-group>
                </b-form-group>
              </b-col>
              <b-col md="12" style="margin-top: -10px">
                <b-form-group>
                  <b-input-group prepend="Cliente ">
                    <clientes-form
                      txtButton="Editar"
                      v-if="
                        venta.cliente.IdCliente &&
                        venta.cliente.DniRuc != '00000000'
                      "
                      idModal="editCliente"
                      @onAddCliente="setCliente"
                      :editCliente="venta.cliente"
                    ></clientes-form>
                    <autocomplete
                      style="width: 40%"
                      required="required"
                      ref="inputCliente"
                      :url="API + 'clientes'"
                      anchor="ClienteDniRuc"
                      label="writer"
                      placeholder=""
                      :onInput="(val) => (newDniRuc = val)"
                      :on-select="setCliente"
                      :customParams="{ id_empresa: empresa }"
                      :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                      :classes="{ input: 'form-control' }"
                    >
                    </autocomplete>
                    <input
                      v-model="dni"
                      @keydown.enter.prevent="loadClienteBarcode"
                      type="text"
                      class="form-control"
                      placeholder="Cod. Barra"
                    />
                    <b-input-group-append>
                      <!--<proveedores-tabla></proveedores-tabla>-->
                      <clientes-form
                        :ruc="newDniRuc"
                        @onAddCliente="setCliente"
                      ></clientes-form>
                    </b-input-group-append>
                  </b-input-group>
                  <b-form-select
                    v-if="venta.cliente.IdCliente"
                    v-model="venta.CampoDireccion"
                    class="mb-3"
                  >
                    <option :value="venta.cliente.Direccion">
                      {{ venta.cliente.Direccion }}
                    </option>
                    <option :value="venta.cliente.Direccion2">
                      {{ venta.cliente.Direccion2 }}
                    </option>
                    <option :value="venta.cliente.Direccion3">
                      {{ venta.cliente.Direccion3 }}
                    </option>
                  </b-form-select>
                  <b-input-group v-if="venta.cliente.DniRuc == '00000000'">
                    <b-form-checkbox
                      :disabled="bloquearInput ? true : false"
                      v-model="venta.EsOrganizacion"
                      :value="1"
                      :unchecked-value="0"
                    >
                      Es Organización
                    </b-form-checkbox>
                    <input
                      v-model="venta.NombreOrganizacion"
                      type="text"
                      style="text-transform: uppercase"
                      class="form-control"
                      required
                      placeholder="Nombre de la Organización"
                      v-if="venta.EsOrganizacion"
                    />
                  </b-input-group>
                  <b-row v-if="this.$route.query.idPedido">
                    <b-col>
                      <b-form-group>
                        <label style="font-weight: bold"
                          >Cliente (Delivery):</label
                        >
                        <b-form-input
                          required
                          style="text-transform: uppercase; font-size: 0.9rem"
                          placeholder="Ingrese cliente"
                          autocomplete="off"
                          v-model="venta.nombreLlevar"
                          ref="inputNombreCliente"
                          type="text"
                          name="NombreLlevar"
                          id="NombreLlevar"
                          readonly
                        ></b-form-input>
                      </b-form-group>
                    </b-col>
                    <b-col>
                      <b-form-group>
                        <label style="font-weight: bold"
                          >Direccion Entrega (Delivery):</label
                        >
                        <b-form-textarea
                          required
                          style="text-transform: uppercase; font-size: 0.9rem"
                          placeholder="Ingrese direccion"
                          rows="3"
                          autocomplete="off"
                          v-model="venta.DireccionEntrega"
                          ref="inputDireccionEntrega"
                          type="text"
                          name="DireccionEntrega"
                          id="DireccionEntrega"
                          readonly
                        ></b-form-textarea>
                      </b-form-group>
                    </b-col>
                  </b-row>
                </b-form-group>
              </b-col>
            </b-row>
          </b-col>
          <b-col md="3" class="ocularMovil">
            <b-form-group class="text-center" v-if="venta.cliente.Cliente">
              <div class="puntos">
                {{ (venta.cliente.Puntos || 0) | redondeoMenor }}
              </div>
              Puntos del cliente
            </b-form-group>
            <b-input-group
              prepend="Puntos"
              :append="descuentoPuntos.descuento | redondear"
              v-if="venta.cliente.Puntos && descuentoGeneral == 0"
            >
              <b-form-input
                v-model="puntosAplicados"
                min="0"
                :max="venta.cliente.Puntos"
                @input="aplicarPuntos"
                type="number"
              ></b-form-input>
            </b-input-group>
            <b-form-text v-if="descuentoPuntos.ValorPunto">
              1 Punto equivale a: S/
              {{ descuentoPuntos.ValorPunto | redondear }}
            </b-form-text>
            <b-form-text v-if="venta.cliente.EsVIP">
              Cliente VIP con Precio Convenio
            </b-form-text>
          </b-col>
          <b-col md="2">
            <b-form-group class="text-center">
              <div class="big-text">
                {{ venta.Serie }} - <b>{{ venta.Numero }}</b>
              </div>
              {{ venta.FechaDoc }}
            </b-form-group>
            <b-form-group class="ocularMovil">
              <label>Vendedor:</label>
              <autocomplete
                required="required"
                ref="inputVendedor"
                :url="API + 'usuarios'"
                :customParams="{ idPerfil: idPerfil ,id_empresa : empresa}"
                anchor="NombreUsuario"
                label="writer"
                :on-select="setVendedorManual"
                :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                :classes="{ input: 'form-control' }"
              >
              </autocomplete>
            </b-form-group>
            <b-form-group v-if="false">
              <label>Comisionista</label>
              <autocomplete
                ref="inputComisionista"
                required="required"
                :url="API + 'comisionistas'"
                anchor="ClienteDniRuc"
                label="writer"
                placeholder="Comisionista"
                :onInput="(val) => (newDniRuc = val)"
                :on-select="setComisionista"
                :onFocus="focusAutocomplete.bind(null, 'inputComisionista')"
                :classes="{ input: 'form-control' }"
                :customParams="{ id_empresa: empresa }"
              >
              </autocomplete>
            </b-form-group>
            <b-badge variant="info" v-if="adelanto"
              >Adelanto de: S/ {{ adelanto }}
            </b-badge>
            <b-badge variant="success" v-if="adelanto"
              >Total a pagar: S/
              {{ $options.filters.redondear(totalCart - adelanto) }}
            </b-badge>
          </b-col>
        </b-row>
        <b-row v-if="venta.idProforma > 0">
          <b-col md="6" class="ml-3">
            <b-row>
              <h5 class="mt-1">
                Proforma Seleccionada:
                <b-badge variant="info" style="font-size: 1.2rem">{{
                  venta.anioNumeroProforma
                }}</b-badge>
              </h5>
              <b-button
                class="ml-1 mb-1"
                size="sm"
                variant="warning"
                @click="limpiarProforma"
                ><b>Quitar</b></b-button
              >
            </b-row>
          </b-col>
        </b-row>
        <b-row>
          <b-col cols="12">
            <productos-carrito
              @onInputCantidad="actualizarDescuento"
              @onAddProducto="onAddProducto"
              @onRemoveProducto="actualizarDescuento"
              :igvTotal="tieneIgv"
              :setPrecio="true"
              ref="carrito"
              :showButton="false"
              :bloquearInput="bloquearInput"
              :fields="carrito.fields"
              :isVentas="true"
              :idAlmacen="venta.almacen ? venta.almacen.IdAlmacen : 0" 
              :onSetProducto="onSetProducto"
              :esDelivery="venta.EsDelivery"
              :mostraTotales="false"
            >
              <template slot="operaciones">
                <table class="table b-table border table-footer">
                  <tbody>
                    <tr>
                      <td class="text-center">Opciones</td>
                    </tr>
                    <tr v-if="puntosAplicados == 0">
                      <td>
                        <b-input-group prepend="S/" append="Descuento">
                          <input
                            v-model="descuentoGeneral"
                            min="0"
                            value="0"
                            :disabled="bloquearInput ? true : false"
                            @input="
                              (val) => {
                                aplicarDescuentoGeneral(val.target.value);
                              }
                            "
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
                          :disabled="
                            bloquearInput
                              ? true
                              : false || isNota || venta.EsDelivery
                          "
                          v-model="venta.EsCredito"
                          :value="1"
                          :unchecked-value="0"
                        >
                          Venta a credito
                        </b-form-checkbox>
                        <b-form-input
                          :disabled="
                            bloquearInput
                              ? true
                              : false || isNota || venta.EsDelivery
                          "
                          v-if="venta.EsCredito"
                          v-model="venta.FechaCredito"
                          type="date"
                        >
                        </b-form-input>
                        <b-form-checkbox
                          :disabled="
                            bloquearInput ? true : false || venta.EsDelivery
                          "
                          v-model="venta.PorEntregar"
                          :value="1"
                          :unchecked-value="0"
                        >
                          Por entregar
                        </b-form-checkbox>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <b-input-group
                          prepend="Prof. válida por:"
                          append="días"
                        >
                          <b-form-input
                            :disabled="bloquearInput ? true : false"
                            v-model="diasValidosProf"
                            min="0"
                            value="0"
                            type="number"
                          ></b-form-input>
                        </b-input-group>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </template>
              <template slot="metodopago" v-if="!venta.EsCredito">
                <table class="table b-table border table-footer table-sm">
                  <tbody>
                    <tr>
                      <td colspan="3" class="text-center">Metodo de Pago</td>
                    </tr>
                    <tr>
                      <td>Efectivo</td>
                      <td>
                        <b-button
                          variant="info"
                          size="sm"
                          class="mt-1"
                          @click="metodoPagoCambio(1)"
                          ><i class="fas fa-check"></i
                        ></b-button>
                      </td>
                      <td>
                        <b-form-input
                          v-model="venta.pago.efectivo.descripcion"
                          type="text"
                          :disabled="bloquearInput ? true : false"
                          placeholder="Descripcion"
                        ></b-form-input>
                      </td>
                      <td>
                        <input
                          v-model="venta.pago.efectivo.monto"
                          min="0"
                          :disabled="bloquearInput ? true : false"
                          step="0.01"
                          type="number"
                          class="form-control"
                          placeholder="S/."
                        />
                      </td>
                    </tr>
                    <tr>
                      <td>Tarjeta</td>
                      <td>
                        <b-button
                          variant="info"
                          size="sm"
                          class="mt-1"
                          @click="metodoPagoCambio(2)"
                          ><i class="fas fa-check"></i
                        ></b-button>
                      </td>
                      <td>
                        <b-form-input
                          v-model="venta.pago.visa.descripcion"
                          type="text"
                          :disabled="bloquearInput ? true : false"
                          placeholder="Descripcion"
                        ></b-form-input>
                      </td>
                      <td>
                        <input
                          class="form-control"
                          :disabled="bloquearInput ? true : false"
                          v-model="venta.pago.visa.monto"
                          min="0"
                          step="0.01"
                          type="number"
                          placeholder="S/."
                          @input="
                            venta.pago.efectivo.monto =
                              $options.filters.redondear(
                                totalCart -
                                  venta.pago.mastercard.monto -
                                  $event.target.value
                              )
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td>Yape/Plin</td>
                      <td>
                        <b-button
                          variant="info"
                          size="sm"
                          class="mt-1"
                          @click="metodoPagoCambio(3)"
                          ><i class="fas fa-check"></i
                        ></b-button>
                      </td>
                      <td>
                        <b-form-input
                          v-model="venta.pago.mastercard.descripcion"
                          :disabled="bloquearInput ? true : false"
                          type="text"
                          placeholder="Descripcion"
                        ></b-form-input>
                      </td>
                      <td>
                        <input
                          class="form-control"
                          v-model="venta.pago.mastercard.monto"
                          :disabled="bloquearInput ? true : false"
                          min="0"
                          step="0.01"
                          type="number"
                          placeholder="S/."
                          @input="
                            venta.pago.efectivo.monto =
                              $options.filters.redondear(
                                totalCart -
                                  venta.pago.visa.monto -
                                  $event.target.value
                              )
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td>Transferencia</td>
                      <td>
                        <b-button
                          variant="info"
                          size="sm"
                          class="mt-1"
                          @click="metodoPagoCambio(4)"
                          ><i class="fas fa-check"></i
                        ></b-button>
                      </td>
                      <td>
                        <b-form-input
                          v-model="venta.pago.transferencia.descripcion"
                          :disabled="bloquearInput ? true : false"
                          type="text"
                          placeholder="Descripcion"
                        ></b-form-input>
                      </td>
                      <td>
                        <input
                          class="form-control"
                          v-model="venta.pago.transferencia.monto"
                          :disabled="bloquearInput ? true : false"
                          min="0"
                          step="0.01"
                          type="number"
                          placeholder="S/."
                          @input="
                            venta.pago.efectivo.monto =
                              $options.filters.redondear(
                                totalCart -
                                  venta.pago.transferencia.monto -
                                  $event.target.value
                              )
                          "
                        />
                      </td>
                    </tr>
                    <tr>
                      <td>Depósito</td>
                      <td>
                        <b-button
                          variant="info"
                          size="sm"
                          class="mt-1"
                          @click="metodoPagoCambio(5)"
                          ><i class="fas fa-check"></i
                        ></b-button>
                      </td>
                      <td>
                        <b-form-input
                          v-model="venta.pago.deposito.descripcion"
                          :disabled="bloquearInput ? true : false"
                          type="text"
                          placeholder="Descripcion"
                        ></b-form-input>
                      </td>
                      <td>
                        <input
                          class="form-control"
                          v-model="venta.pago.deposito.monto"
                          :disabled="bloquearInput ? true : false"
                          min="0"
                          step="0.01"
                          type="number"
                          placeholder="S/."
                          @input="
                            venta.pago.efectivo.monto =
                              $options.filters.redondear(
                                totalCart -
                                  venta.pago.deposito.monto -
                                  $event.target.value
                              )
                          "
                        />
                      </td>
                    </tr>
                  </tbody>
                </table>
              </template>
              <template slot="totalEmpresa" >
                
              </template>
              <template slot="adicional" v-if="!venta.EsCredito">
                <table
                  class="table b-table border table-footer table-sm text-center"
                  v-if="false"
                >
                  <tbody>
                    <tr>
                      <td>Mano de obra bruta</td>
                    </tr>
                    <tr>
                      <td>
                        <h3>{{ manoDeObraBruta | redondear }}</h3>
                      </td>
                    </tr>
                    <tr>
                      <td>Mano de obra neta</td>
                    </tr>
                    <tr>
                      <td>
                        <h3 :class="{ 'text-danger': manoDeObraNeta <= 0 }">
                          {{ manoDeObraNeta | redondear }}
                        </h3>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </template>
              
            </productos-carrito>
            
          </b-col>
          <b-col cols="4">
            <table class="table b-table border table-footer table-sm">
                  <tbody>
                    <tr style="background: #1A237E; color: #ffffff;">
                      <td colspan="2" class="text-center" style="text-transform: uppercase;font-size: 1.2rem; font-weight: bold;">Total por Empresa</td>
                    </tr>
                      <tr v-for="(empresa,index) in empresasConTotal" :key="index">
                        <th>{{empresa.nombre}}</th>
                        <td>{{empresa.total}}</td>
                      </tr>
                  </tbody>
                </table>
          </b-col>
          <b-col cols="4" style="text-align: center;">
            <b-button
              type="button"  @click="guardarVenta"
              :disabled="inProgress"
              style="background: #28A745;"
              size="lg"
              class="btnAction"
              
              > 
                <i class="fa fa-shopping-cart" aria-hidden="true"></i> Pagar 
            </b-button>
            <b-button
              type="button"
              v-if="!this.$route.query.idPedido"
              :disabled="inProgress"
              variant="primary"
              size="lg"
              @click="guardarProforma"
              class="btnAction"
              >
                Crear Proforma
              </b-button>

              <proformas
                :idAlmacen="venta.almacen.IdAlmacen"
                :empresa="empresa"
                @onSelectProforma="setCliente"
              />

            
            <b-button
              type="button"
              @click="anularPagoDelivery"
              variant="danger"
              v-if="this.$route.query.idPedido"
              >Anular Pago Delivery</b-button
            >
            <Movimiento :empresa="empresa" v-if="idPerfil == 1 || idPerfil == 14"/>
              <!-- style="background: #004D40;" -->
            
              <!-- -->
              <b-button
              class="btnAction"
              v-if="ultimaVenta"
              target="_blank"
              variant="info"
              size="lg"
              :href="
                '/imprimir/index.php?IdDocVenta=' +
                ultimaVenta +
                '&empresa=' +
                empresa
              "
              >Imprimir última Venta</b-button
            >

           
              <delivery-modal
                class="ocularMovil"
                ref="deliveryModal"
                @onPagarDelivery="onPagarDelivery"
              ></delivery-modal>
           
            
              <pago-deuda />
            
          
          
            <!-- <b-button-group class="ocularMovil" v-if="false">
              <pre-orden-lista
                v-show="!this.$route.query.idPreOrden"
                :idAlmacen="venta.almacen.IdAlmacen"
                @onSelectPreOrden="setCliente"
                @onPreOrden="selectPreOrden"
              ></pre-orden-lista>
            </b-button-group> -->
            <!-- <b-button-group size="sm" class="ocularMovil"> -->
              <!--<config-venta @onUpdateConfig="eliminarCliente"></config-venta>-->
              <config-venta
                @onUpdateConfig="eliminarCliente"
                @onUpdateConfigComision="eliminarComisionista"
              ></config-venta>
            <!-- </b-button-group> -->
          </b-col>
          <b-col cols="4">
             <table class="table b-table border table-footer">
        <tbody>
           <tr>
            <td>
              <b-button 
                class="p-1 ml-1 mt-1" 
                :id="`popover-metodopago`" 
                tabindex="0" 
                variant="success" 
                @click="togglePopover">
                <i class="fas fa-credit-card"></i>
              </b-button>
              
               <b-popover 
                  :target="`popover-metodopago`" 
                  title="Método de Pago" 
                  placement="top" 
                  triggers="manual" 
                  :show.sync="mostrar" 
                  :style="{ minWidth: '600px' }"
                >
                <slot :name="`metodopago`" style="width: 1200px;">
                  <table class="table b-table border table-footer table-sm">
                    <tbody>
                      <tr>
                        <td>Efectivo</td>
                        <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(1)"><i class="fas fa-check"></i></b-button></td>
                        <td>
                          <b-form-input v-model="venta.pago.efectivo.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                        </td>
                        <td>
                          <input v-model="venta.pago.efectivo.monto" min="0" step="0.01" type="number" placeholder="S/." class="form-control"/>
                        </td>
                      </tr>
                      <tr>
                        <td>Tarjeta</td>
                        <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(2)"><i class="fas fa-check"></i></b-button></td>
                        <td>
                          <b-form-input v-model="venta.pago.visa.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                        </td>
                        <td>
                          <input class="form-control" v-model="venta.pago.visa.monto" min="0" step="0.01" type="number" placeholder="S/." 
                              @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.mastercard.monto - venta.pago.transferencia.monto - venta.pago.deposito.monto - $event.target.value)"
                            />
                        </td>
                      </tr>
                      <tr>
                        <td>Yape/Plin</td>
                        <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(3)"><i class="fas fa-check"></i></b-button></td>
                        <td>
                          <b-form-input v-model="venta.pago.mastercard.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                        </td>
                        <td>
                          <input class="form-control" v-model="venta.pago.mastercard.monto" min="0" step="0.01" type="number" placeholder="S/." 
                            @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - venta.pago.transferencia.monto - venta.pago.deposito.monto - $event.target.value)" 
                          />
                         
                        </td>
                      </tr>
                      <tr>
                        <td>Transferencia</td>
                        <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(4)"><i class="fas fa-check"></i></b-button></td>
                        <td>
                          <b-form-input v-model="venta.pago.transferencia.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                        </td>
                        <td>
                          <input
                            class="form-control" v-model="venta.pago.transferencia.monto" min="0" step="0.01" type="number" placeholder="S/."
                             @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - venta.pago.mastercard.monto - venta.pago.deposito.monto - $event.target.value)" 
                            />
                        </td>
                      </tr>
                      <tr>
                        <td>Depósito</td>
                        <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(5)"><i class="fas fa-check"></i></b-button></td>
                        <td>
                          <b-form-input v-model="venta.pago.deposito.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                        </td>
                        <td>
                          <input
                            class="form-control" v-model="venta.pago.deposito.monto" min="0" step="0.01" type="number" placeholder="S/."
                             @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - venta.pago.mastercard.monto - venta.pago.transferencia.monto - $event.target.value)" 
                            />
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </slot>
              </b-popover>
            </td>
            <td class="table-success"><b>SubTotal</b></td>
            <td class="table-success">
               {{ subTotalG }}
            </td>
          </tr>
          <tr v-if="descuentoG >0">
           <td></td>
            <td class="table-danger">Descuento</td>
            <td class="table-danger">- {{ descuentoG }}</td>
          </tr>
         
          <tr>
            <td>
              <b-button 
                class="p-1 ml-1 mt-1" 
                :id="`popover-opciones`" 
                tabindex="0" 
                @click="togglePopoverOpcion" 
                variant="warning">
                <i class="fas fa-tags"></i>
              </b-button>
              <b-popover 
                :target="`popover-opciones`" 
                title="Opciones" 
                triggers="manual" 
                placement="top" 
                :show.sync="mostrarOp" 
                :style="{ minWidth: '600px' }"
              >
                <slot :name="`opciones`">
                  <table class="table b-table border table-footer">
                    <tbody>
                      <tr v-if="puntosAplicados == 0">
                        <td>
                          <b-input-group prepend="S/" append="Descuento">
                              <input
                                v-model="descuentoGeneral"
                                min="0"
                                value="0"
                                @input="(val)=>{aplicarDescuentoGeneral(val.target.value)}"
                                class="form-control"
                              />
                          </b-input-group>
                          <b-form-text style="color:black !important; font-weight:bold">
                            Descuento general a toda la venta.
                          </b-form-text>
                        </td>
                      </tr>
                      <tr>
                        <td>
                          <b-form-checkbox
                            v-model="venta.EsCredito"
                            :value="1"
                            :unchecked-value="0">
                            Venta a credito
                          </b-form-checkbox>
                          <b-form-input 
                            v-if="venta.EsCredito"
                            v-model="venta.FechaCredito"
                            type="date">
                          </b-form-input>
                                                <!-- <b-form-checkbox v-if="false"
                                                    v-model="venta.PorEntregar"
                                                    :value="1"
                                                    :unchecked-value="0">
                                                    Por entregar
                                                </b-form-checkbox> -->
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <b-input-group prepend="Proforma válidez: " append="días">
                            <b-form-input
                              :disabled="bloquearInput?true:false"
                              v-model="diasValidosProf"
                              min="0"
                              value="0"
                              type="number"
                            ></b-form-input>
                            </b-input-group>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <b-input-group>
                              <b-form-checkbox 
                                :disabled="bloquearInput?true:false"
                                v-model="EsCreditoProforma"
                                :value="1"
                                :unchecked-value="0">
                                Proforma a Credito
                              </b-form-checkbox>
                              <div v-if="EsCreditoProforma">
                                <div class="mb-3">
                                  <input v-model="FechaCreditoProforma"
                                    type="date"
                                    class="form-control"
                                    required
                                    placeholder="Fecha Crédito"/>
                                </div>
                              </div>
                            </b-input-group>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </slot>
                </b-popover>  
              </td>
            <td class="table-info"><b>TOTAL</b></td>
            <td class="table-info">
              {{ sumatoriaTotal }}
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <label>pagó con:</label>
              <b-form-input
                type="text"
                :disabled="bloquearInput ? true : false"
                placeholder="Pagó con"
                v-model="pagocon"
                @input="asignarVuelto"
              ></b-form-input
              ><br />
              <label>Vuelto:</label>
             <input type="text" class="form-control" v-model="vuelto" readonly>
            </td>
          </tr>
        </tbody>
      </table>
          </b-col>

          
        </b-row>
        <b-row align-h="center" class="text-center">
          <b-col cols="8">
            <br />
            
            <br />
            <br />
          </b-col>
        </b-row>
      </b-form>
    </b-container>
  </div>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import ProductosCarrito from "@/components/ProductosCarrito";
import ClientesForm from "@/components/ClientesForm";
import ConfigVenta from "@/components/ConfigVenta";
import PagoDeuda from "@/components/PagoDeuda";
import PreOrdenLista from "@/components/PreOrdenLista.vue";
import Proformas from "@/components/ProformaLista.vue";
import DeliveryModal from "@/components/DeliveryModal.vue";
import Movimiento from "@/components/Movimiento";
import Swal from "sweetalert2";
import axios from "axios";
export default {
  beforeMount() {
    let vm = this;

    // Interceptos XMLHttpRequest
    let oldXHROpen = window.XMLHttpRequest.prototype.open;
    window.XMLHttpRequest.prototype.open = function(
      method,
      url,
      async,
      user,
      password
    ) {
      // do something with the method, url and etc.

      var newUrl =
         url.startsWith("http")
          ? url
          : location.protocol + "//" + location.host + url;
      newUrl = new URL(newUrl);

      newUrl.searchParams.append(
        "empresa",
        sessionStorage.getItem("empresa") || "modasgio"
      );

      url = newUrl.toString();
      arguments[1] = url;

      return oldXHROpen.apply(this, arguments);
    };
  },
  created() {},
  async mounted() {
    this.$store.state.bloquearCarrito  = true;
    // 1. Inicializa objetos básicos para evitar el error de 'IdCliente'
    this.venta = { cliente: {}, pago: { efectivo: {}, visa: {}, mastercard: {}, deposito: {}, transferencia: {} } };

    // 2. Espera a que el DOM sea real
    this.$nextTick(async () => {
      this.initForm();
      
      // Un pequeño delay extra para setScreen ayuda si hay v-if complejos
      setTimeout(() => { this.setScreen(); }, 50);

      this.$store.state.MostrarBotonMultipleEmpresa = true;
      await this.getVendedor();

      await Promise.all([ 
        this.loadClienteVarios(),
        this.escucharPedidos(),
        this.configStock()
      ]);

      this.recalcularTotalesPorEmpresa();
    });
  },

  data() {
    return {
      vuelto: 0,
      // TipoDocumento:
      //   this.empresa == "modasjes" ? "NOTA DE VENTA" : "BOLEC. ELECTR",
      TipoDocumento: "BOLETA ELEC.",
      pagocon: 0,
      isNota: false,
      dni: "",
      adelanto: 0,
      gasto: 0,
      newDniRuc: "",
      ultimaVenta: "",
      inProgress: false,
      API: config.API,
      puntosAplicados: 0,
      descuentoPuntos: {},
      descuentoGeneral: 0,
      tieneIgv: false,
      venta: {
        cliente: {}, // <--- Esto evita el error de IdCliente
        almacen: {}, // <--- Esto evita el error de IdAlmacen
        puntoVenta: {},
        tipoVenta: {},
        pago: {
          efectivo: { monto: 0 },
          visa: { monto: 0 },
          mastercard: { monto: 0 },
          deposito: { monto: 0 },
          transferencia: { monto: 0 }
        }
      },
      mostrar: false,
      mostrarOp: false,
      bloquearInput: false,
      diasValidosProf: 1,
      adelantosPreOrden: [],
      lstTotalPorEmpresa: [],
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
          //{ key: 'manoobra', label: 'Mano de obra', class:'text-center' },
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center"
          },
          { key: "CodigoBarra", label: "Código" },
          { key: "Producto" },
          { key: "ProductoModelo", label: "Modelo" },
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoDesc3", label: "Procedencia" },
          { key: "ProductoCategoria", label: "Categoria" },
          // { key: "Producto" },
          { key: "Series" },
          // { key: "AddSeries" },
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoCategoria", label: "Categoria" },
          { key: "ProductoMedicion", label: "U.M" },
          { key: "NombreEmpresa", label: "Empresa" },
          { key: "ProductoPresentacion", label: "Anaquel" },
          { key: "Descripcion", label: "Descripción" },
          { key: "eventPres", label: "" },
          { key: "cantxPrecio", variant: "warning" },
          // { key: 'FechaAlquiler', label: 'Fecha Inicio' },
          // { key: 'FechaAlquilerFin', label: 'Fecha Fin'},
          { key: "cantidad" },
          { key: "precio", label: "Precio" },
          //{ key: 'utilidad', label: 'Porcentaje Utilidad %' },
          {
            key: "subtotal",
            label: "SubTotal",
            variant: "success",
            class: "text-center"
          },
          { key: "descuento", label: "Desc." },
          {
            key: "total",
            label: "Total",
            variant: "danger",
            class: "text-center"
          },
          { key: "acciones", label: "" }
        ]
      },
      empresa: sessionStorage.getItem("empresa"),
      ventaSinStock: 0,
      idPerfil: sessionStorage.getItem("IdPerfil") || 0,
      EsCredito: 0,
      EsCreditoProforma: 0
    };
  },
  watch: {
    totalCart(newVal) {
      this.venta.pago.efectivo.monto = parseFloat(
        newVal -
          (parseFloat(this.venta.pago.visa.monto) +
            parseFloat(this.venta.pago.mastercard.monto) +
            parseFloat(this.venta.pago.deposito.monto) +
            parseFloat(this.venta.pago.transferencia.monto))
      ).toFixed(2);
      this.asignarVuelto();
    },
    productos() {
      this.recalcularTotalesPorEmpresa();
    },
    recalcularTotalPorEmpresaFlag() {
      this.$nextTick(() => {
        setTimeout(() => {
          this.recalcularTotalesPorEmpresa();
          this.metodoPagoCambio(1);
          // this.metodoPagoCambio(1);
        }, 100);
      });
    }
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      recalcularTotalPorEmpresaFlag: state =>
        state.recalcularTotalPorEmpresaFlag,

      totalCart: state => state.cart.totalCart
    }),
    empresaClass() {
      const emp = this.$store.state.empresas.find(
        e => e.IDEMPRESA == this.empresa
      );
      return emp ? emp.ACRONYM.toLowerCase() : "";
    },
    empresasConTotal() {
      return this.lstTotalPorEmpresa.filter(e => e.total > 0);
    },
    manoDeObraBruta() {
      let pManoObra = this.productos.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.precioBruta : 0;

      return precio;
    },
    manoDeObraNeta() {
      let pManoObra = this.productos.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.precio : 0;

      return precio;
    },
    sumatoriaTotal() {
      let sumatoria = this.productos.reduce((sum, producto) => {
        return (
          sum +
          parseFloat(
            producto.subtotal - (producto.descuento ? producto.descuento : 0)
          )
        );
      }, 0);
      // this.actualizarTotal(sumatoria);

      return this.$options.filters.redondear(sumatoria);
    },
    subTotalG() {
      let sumatoria = this.productos.reduce((sum, producto) => {
        return sum + parseFloat(producto.subtotal);
      }, 0);
      // this.actualizarTotal(sumatoria);

      return this.$options.filters.redondear(sumatoria);
    },
    descuentoG() {
      let sumatoria = this.productos.reduce((sum, producto) => {
        return sum + parseFloat(producto.descuento ? producto.descuento : 0);
      }, 0);
      // this.actualizarTotal(sumatoria);

      return this.$options.filters.redondear(sumatoria);
    }
  },
  methods: {
    ...mapActions({
      aplicarDescuento: "aplicarDescuentoGeneral",
      limpiarCart: "limpiarCarrito",
      obtenerStok: "obtenerStock",
      actualizarPrecioConvenio: "actualizarPrecioConvenio",
      add: "agregarProducto"
    }),
    togglePopover() {
      if (this.mostrar) {
        this.$root.$emit("bv::hide::popover", `popover-metodopago`);
      } else {
        this.mostrar = true;
        this.$nextTick(() => {
          this.$root.$emit("bv::show::popover", `popover-metodopago`);
        });
      }
    },
    togglePopoverOpcion() {
      if (this.mostrarOp) {
        this.$root.$emit("bv::hide::popover", `popover-opciones`);
      } else {
        this.mostrarOp = true;
        this.$nextTick(() => {
          this.$root.$emit("bv::show::popover", `popover-opciones`);
        });
      }
    },
    setScreen() {
        let el = document.getElementById('EL_ID_QUE_BUSCAS'); // Revisa qué ID tienes en la línea 1311
        if (el) {
            el.style.whatever = "..."; 
        } else {
            console.warn("Esperando al DOM para setScreen...");
        }
    },
    async recalcularTotalesPorEmpresa() {
      this.lstTotalPorEmpresa = this.$store.state.empresas.map(empresa => {
        const total = this.productos
          .filter(p => p.Empresa == empresa.IDEMPRESA)
          .reduce((sum, prod) => sum + (parseFloat(prod.total) || 0), 0);

        return {
          nombre: empresa.RAZONSOCIAL,
          total: total.toFixed(2)
        };
      });
      const totalCart = this.lstTotalPorEmpresa.reduce((sum, emp) => {
        return sum + parseFloat(emp.total);
      }, 0);
      this.$store.state.cart.totalCart = totalCart;
      this.$nextTick(() => {
        this.metodoPagoCambio(1);
      });
    },
    asignarVuelto() {
      this.vuelto = parseFloat(this.pagocon - this.sumatoriaTotal).toFixed(2);
    },

    metodoPagoCambio(opc) {
      switch (opc) {
        case 1:
          this.venta.pago.visa.monto = 0;
          this.venta.pago.mastercard.monto = 0;
          this.venta.pago.transferencia.monto = 0;
          this.venta.pago.deposito.monto = 0;
          this.venta.pago.efectivo.monto = this.totalCart;
          break;
        case 2:
          this.venta.pago.mastercard.monto = 0;
          this.venta.pago.efectivo.monto = 0;
          this.venta.pago.transferencia.monto = 0;
          this.venta.pago.deposito.monto = 0;
          this.venta.pago.visa.monto = this.totalCart;
          break;
        case 3:
          this.venta.pago.efectivo.monto = 0;
          this.venta.pago.visa.monto = 0;
          this.venta.pago.transferencia.monto = 0;
          this.venta.pago.deposito.monto = 0;
          this.venta.pago.mastercard.monto = this.totalCart;
          break;
        case 4:
          this.venta.pago.efectivo.monto = 0;
          this.venta.pago.visa.monto = 0;
          this.venta.pago.mastercard.monto = 0;
          this.venta.pago.deposito.monto = 0;
          this.venta.pago.transferencia.monto = this.totalCart;
          break;
        case 5:
          this.venta.pago.efectivo.monto = 0;
          this.venta.pago.visa.monto = 0;
          this.venta.pago.transferencia.monto = 0;
          this.venta.pago.mastercard.monto = 0;
          this.venta.pago.deposito.monto = this.totalCart;
          break;
        default:
          break;
      }
    },
    configStock() {
      this.axios
        .get(this.API + "config/stock?id_emprea=" + this.empresa)
        .then(res => {
          this.ventaSinStock = res.data;
        })
        .catch(err => {
          this.ventaSinStock = 0;
        });
    },
    cambiarEmpresa() {
      setTimeout(() => {
        sessionStorage.setItem("empresa", this.empresa);
        window.location.reload();
        return false;
      }, 50);
    },
    loadClienteVarios() {
      this.axios.get(this.API + "clientes/00000000").then(res => {
        console.log("res", res.data);
        if (res.data.IdCliente) {
          this.setCliente(res.data);
        }
      });
    },
    prevent(evt) {
      if (evt.target.tagName !== "TEXTAREA") {
        evt.preventDefault();
      }
    },
    selectPreOrden(orden) {
      // console.log(orden)
      // this.venta.IdPreOrden = this.venta.IdPreOrden || []
      // if (Array.isArray(this.venta.IdPreOrden) ) {
      this.venta.IdPreOrden.push(orden);
      //}

      // obtener total adelantos
      this.axios
        .get(this.API + "preorden/cajaybanco", {
          params: {
            IdPreOrden: orden.IdPreOrden,
            TipoCajaBanco: 0,
            id_empresa: this.empresa
          }
        })
        .then(res => {
          this.adelantosPreOrden = res.data;
          let suma = 0;
          res.data.map(ad => {
            if (ad.Tipo == 0) {
              suma += ad.Importe;
            }
          });
          this.adelanto = suma;
        });
    },
    loadFirstAlmacen() {
      return this.axios
        .get(this.API + "almacenes/primero?id_empresa" + this.empresa)
        .then(res => {
          this.$refs.inputAlmacen.setValue(res.data.Almacen);
          this.venta.almacen = res.data;
        });
    },
    loadFirstPuntoVenta() {
      return this.axios
        .get(this.API + "puntoventa/primero?id_empresa=" + this.empresa)
        .then(res => {
          this.$refs.inputPuntoVenta.setValue(res.data.PuntoVenta);
          this.setPuntoVenta(res.data);
        });
    },
    loadClienteBarcode(e) {
      e.preventDefault();
      let dni = this.dni;

      if (dni) {
        this.setCliente(false, dni);
      }
    },
    focusAutocomplete(ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    initForm() {
      this.venta = {
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
          //IdAlmacen: 1,
          //Almacen: 'PRINCIPAL'
        },
        puntoVenta: {
          //IdDocVentaPuntoVenta: 1,
          //PuntoVenta: 'PRINCIPAL'
        },
        vendedor: {},
        adelantosPreOrden: [],
        FechaCredito: this.$moment().add(1, "days").format("YYYY-MM-DD"),
        FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
        pago: {
          efectivo: { descripcion: "", IdMetodoPago: 1, monto: 0 },
          visa: { descripcion: "", IdMetodoPago: 2, monto: 0 },
          mastercard: { descripcion: "", IdMetodoPago: 3, monto: 0 },
          transferencia: { descripcion: "", IdMetodoPago: 4, monto: 0 },
          delivery: { descripcion: "", IdMetodoPago: 4, monto: 0 },
          deposito: { descripcion: "", IdMetodoPago: 5, monto: 0 }
        },
        IdPreOrden: [],
        CampoDireccion: "Direccion",
        EsOrganizacion: 0,
        NombreOrganizacion: "",
        NroComprobanteModifica: "",
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: "",
        EsDelivery: false,
        nombreLlevar: "",
        DireccionEntrega: "",
        diasValidosProf: 1,
        idProforma: 0,
        anioNumeroProforma: ""
      };
      this.isNota = false;
      this.bloquearInput = false;
      this.adelantosPreOrden = [];
      this.adelanto = 0;
      this.infoDelivery = {};
      this.descuentoGeneral = 0;
      this.puntosAplicados = 0;
    },
    setTipoVenta(obj) {
      this.isNota = false;
      (this.bloquearInput = false), (this.venta.NroComprobanteModifica = "");
      this.venta.CodSunatModifica = "";
      this.venta.NotaIdMotivo = "";
      this.venta.NotaDescMotivo = "";
      this.$refs.inputDocRef && this.$refs.inputDocRef.setValue("");
      if (obj.CodSunat == "07" || obj.CodSunat == "08") {
        // 07 - nota de credito, 08 - Nota de debito
        this.isNota = true;
      }

      if (obj.TieneIgv) {
        this.tieneIgv = true;
      } else {
        this.tieneIgv = false;
      }
      this.$refs.inputTipoVenta.setValue(obj.TipoDoc);
      this.venta.tipoVenta = obj;
      this.getNumeroVenta();
    },

    async setDocRef(obj) {
      try {
        // 1. Set documento base
        this.venta.CodSunatModifica = obj.CodSunat;
        this.venta.NroComprobanteModifica = obj.NroComprobante;

        // 2. Ajustar serie (nota crédito / débito)
        if (
          this.venta.tipoVenta.CodSunat == "07" ||
          this.venta.tipoVenta.CodSunat == "08"
        ) {
          this.venta.Serie = this.venta.Serie.replace(
            /^./,
            obj.NroComprobante[0]
          );
        }

        // 3. Obtener correlativo (empresa actual)
        const resNumero = await this.axios.get(
          this.API + "ventas/solonumero",
          {
            params: {
              serie: this.venta.Serie,
              idTipoDoc: this.venta.tipoVenta.IdTipoDoc,
              id_empresa: this.empresa
            }
          }
        );

        this.venta.Numero = resNumero.data.numero;

        // 4. Obtener detalle (SIN FILTRAR EMPRESA)
        const resDetalle = await this.axios.get(
          this.API + "venta/detalle/comprobante",
          {
            params: {
              NroComprobante: obj.NroComprobante,
              idDocVenta: obj.IdDocVenta,
              IdDocVentaPuntoVenta:
                this.venta.puntoVenta.IdDocVentaPuntoVenta
            }
          }
        );

        const data = resDetalle.data;

        if (!data || data.length === 0) {
          this.$toasted.show("No se encontró el comprobante");
          return;
        }

        // 5. Cliente (SIN FILTRAR EMPRESA)
        const resCliente = await this.axios.get(this.API + "cliente", {
          params: {
            idCliente: data[0].IdCliente
          }
        });

        if (resCliente.data) {
          this.$refs.inputCliente.setValue(resCliente.data.Cliente);
          this.venta.cliente = resCliente.data;
          this.venta.CampoDireccion = resCliente.data.Direccion;
        } else {
          this.$toasted.show("No se encontró el cliente");
        }

        // 6. Limpiar carrito
        this.limpiarCart();

        // 7. Detectar empresas (IMPORTANTE)
        const empresasSet = new Set();

        data.forEach(item => {
          empresasSet.add(item.IdEmpresa);
        });

        if (empresasSet.size > 1) {
          this.$toasted.show(
            "Venta multempresa detectada. Se generarán múltiples notas de crédito."
          );
        }

        // 8. Procesar productos (SIN alterar empresa)
        data.forEach(item => {
          item.estadoPxP = false;

          let preciosProd = JSON.parse(
            item.PreciosPorProducto || "[]"
          );

          item.precioCont = item.PrecioContado;

          let fPP = preciosProd.filter(p => p.cantidad === 0);

          if (!item.PreciosPorProducto || fPP.length === 4) {
            item.estadoPxP = true;
          }

          // IMPORTANTE: NO TOCAR EMPRESA
          // item.Empresa =  NO HACER 
          item.Empresa = item.IdEmpresa;
          item.NombreEmpresa = item.NombreEmpresa || item.NombreEmpresa;
          this.add([item, () => {}, false]);
        });

        // 9. Configuración final
        this.venta.NotaIdMotivo = null;
        this.bloquearInput = true;

        // 10. Stock (empresa actual)
        this.obtenerStok({
          IdAlmacen: this.venta.almacen.IdAlmacen
        });

      } catch (error) {
        console.log(error);
        this.$toasted.show("Error al cargar la nota de crédito");
      }
    },

     obtenerStok(data) {
        this.axios
          .get(this.API + "productos/puntoventa/stock", {
            params: {
              idAlmacen: data.IdAlmacen,
              idProductos: this.productos.map(p => p.IdProducto),
              id_empresa: this.empresa
            }
          })
          .then(res => {

            res.data.forEach(stockItem => {

              let prod = this.productos.find(
                p => p.IdProducto == stockItem.IdProducto
              )

              if (prod) {
                prod.stock = stockItem.stock
              }

            })

            this.$forceUpdate()

          })
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
      this.venta.almacen = obj;
      this.$refs.inputAlmacen.setValue(obj.Almacen);
      this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen });
    },
    loadPuntoVenta(id) {
      this.axios.get(this.API + "puntoventa/id/" + id).then(res => {
        this.setPuntoVenta(res.data);
        this.getNumeroVenta();
      });
    },
    getTipoDoc(id) {
      this.axios.get(this.API + "tipodoc/" + id).then(response => {
        if (response.data) this.setTipoVenta(response.data);
      });
    },
    setPuntoVenta(obj) {
      this.venta.puntoVenta = obj;
      this.$refs.inputPuntoVenta.setValue(obj.PuntoVenta);
      //this.getNumeroVenta();
    },
    getVendedor(user) {
      let usuario = sessionStorage.getItem("User");
      if (user) {
        usuario = user;
      }
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          //this.$refs.inputVendedor.$refs.input.disabled = true
          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
          // bloquear ambos inputs
          this.$refs.inputAlmacen.$refs.input.disabled = true;
          this.$refs.inputPuntoVenta.$refs.input.disabled = true;
          //this.getNumeroVenta()
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

      this.venta.vendedor = obj;
    },
    setVendedorManual(obj) {
      this.venta.vendedor = obj;
      this.getVendedor(obj.Usuario);
    },
    setComisionista(obj) {
      if (typeof obj === "object") {
        this.$refs.inputComisionista.setValue(obj.Cliente);
        this.venta.comisionista = obj;
      } else {
        this.venta.comisionista = {};
      }
    },
    setCliente(obj, dni = false) {
      let params = {};
      if (dni) {
        params = {
          dni: dni
        };
      } else {
        this.venta.cliente = obj;
        params = {
          idCliente: obj.IdCliente || obj
        };
      }

      if (typeof obj === "object") {
        if (obj.IdProforma > 0) {
          this.venta.idProforma = obj.IdProforma;
          this.venta.anioNumeroProforma = obj.Anio + "-" + obj.Numero;
          // this.getVendedor(obj.UsuarioReg);
          delete obj.productos;
          this.axios
            .post(
              this.API + "verificar/crear/cliente?id_empresa=" + this.empresa,
              { data: obj }
            )
            .then(response => {
              //console.log(response.data);
              this.$refs.inputCliente.setValue(response.data.Cliente);
              this.venta.cliente = response.data;
              this.venta.CampoDireccion = this.venta.cliente.Direccion;
            })
            .catch(error => {
              this.$toasted.show("Ha ocurrido un error, en la emision a sunat");
            });
        } else {
          this.$refs.inputCliente.setValue(obj.Cliente);
          this.venta.cliente = obj;
          this.venta.CampoDireccion = this.venta.cliente.Direccion;
          if (this.venta.cliente.DniRuc == "00000000") {
            this.venta.EsOrganizacion = 0;
            this.venta.NombreOrganizacion = "";
          }
        }
      } else {
        this.axios
          .get(this.API + "cliente", {
            params: params,
            id_empresa: this.empresa
          })
          .then(response => {
            if (response.data) {
              this.$refs.inputCliente.setValue(response.data.Cliente);
              this.venta.cliente = response.data;
              this.venta.CampoDireccion = this.venta.cliente.Direccion;
              if (this.venta.cliente.DniRuc == "00000000") {
                this.venta.EsOrganizacion = 0;
                this.venta.NombreOrganizacion = "";
              }
            } else {
              this.$toasted.show("No se encontro al cliente");
            }
          })
          .catch(error => {
            console.log(error);
          });
      }
      this.recalcularTotalesPorEmpresa();
      //this.actualizarPrecioConvenio(obj)
      // Actualizar DisabledTotal
    },
    limpiarProforma() {
      this.venta.idProforma = 0;
      this.venta.anioNumeroProforma = "";
      //this.limpiarCart()
      //this.loadClienteVarios()
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
    },
    onAddProducto(obj) {
      this.venta.pago.visa.monto = 0;
      this.venta.pago.mastercard.monto = 0;
      this.venta.pago.transferencia.monto = 0;
      this.venta.pago.deposito.monto = 0;
      this.actualizarDescuento();
      this.obtenerStok({
        IdAlmacen: this.venta.almacen.IdAlmacen
      });
    },
    actualizarDescuento() {
      this.venta.pago.visa.monto = 0;
      this.venta.pago.mastercard.monto = 0;
      this.venta.pago.transferencia.monto = 0;
      this.venta.pago.deposito.monto = 0;
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
      this.usuario = sessionStorage.getItem("User");

      if (!this.usuario) {
        console.error("Usuario no definido");
        return;
      }

      this.axios.get(this.API + "ventas/numero", {
        params: {
          usuario: this.usuario,
          idTipoDoc: this.venta.tipoVenta.IdTipoDoc,
          id_empresa: this.empresa
        }
      })
      .then(response => {
        this.venta.Serie = response.data.serie;
        this.venta.Numero = response.data.numero;
        this.setPuntoVenta(response.data.puntoVenta);
      })
      .catch(error => {
        console.log(error);
      });
    },
    updateTime() {
      this.venta.FechaDoc = this.$moment().format("YYYY-MM-DDTH:mm:ss");
    },

    guardarProforma() {
      this.inProgress = true;

      if (!this.productos.length) {
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

      this.venta.productos = this.productos;
      this.venta.diasValidosProf = this.diasValidosProf;
      this.venta.NombreOrganizacion = ("" + this.venta.NombreOrganizacion)
        .trim()
        .toUpperCase();
      this.axios
        .post(this.API + "proformas?id_empresa=" + this.empresa, this.venta)
        .then(async response => {
          this.$toasted.info("Proforma Guardada con exito");
          //this.limpiarDespuesVenta()
          this.loadClienteVarios();
          this.diasValidosProf = 1;
          this.inProgress = false;
          this.tieneIgv = false;
          //this.ultimaVenta = response.data.insertId
          this.initForm();
          this.limpiarCart();
          this.$refs.inputCliente.setValue("");
          this.$refs.inputVendedor.setValue("");
          this.getVendedor();
          // window.open(
          //   "/imprimir/proforma.php?IdProforma=" +
          //     response.data.insertId +
          //     "&empresa=" +
          //     this.empresa,
          //   "_blank"
          // );
          window.open(
            "/nuevo/index.html#/reportes/proforma/" +
              response.data.insertId +
              "?id_empresa=" +
              this.empresa,
            "_blank"
          );
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
    },
    validarProforma() {
      let profoma = { ...this.$store.state.proforma };
      let lstProductos = [...this.$store.state.lstProductosProforma];

      if (Object.keys(profoma).length > 0) {
        const empresas = [...new Set(lstProductos.map(d => d.Empresa))];

        // if (lstProductos.length == this.productos.length) {
        //   console.log("ddddd");
        //   return true;
        // }
        for (const Empresa of empresas) {
          let lstProductosLength = lstProductos.filter(
            v => v.Empresa == Empresa
          ).length;
          let productosLength = this.productos.filter(v => v.Empresa == Empresa)
            .length;

          if (productosLength > 0 && lstProductosLength != productosLength) {
            return false;
          }
        }
      }
      return true;
    },
    async guardarVenta(evt) {
    evt.preventDefault();
    this.inProgress = true;
    this.loading = true;

    // 1. Cálculo de montos de pago de forma limpia
    const pagos = this.venta.pago;
    const obtenerMonto = (monto) => isNaN(parseFloat(monto)) ? 0 : parseFloat(monto);
    
    let totalPagos = obtenerMonto(pagos.efectivo.monto) +
                     obtenerMonto(pagos.visa.monto) +
                     obtenerMonto(pagos.mastercard.monto) +
                     obtenerMonto(pagos.transferencia.monto) +
                     obtenerMonto(pagos.deposito.monto);

    // VALIDAR TOTAL VENTA
    const totalVenta = parseFloat(this.total) || 0;

    for (let producto of this.productos) {
        const precio = parseFloat(producto.precio) || 0;

        if (precio <= 0) {
            this.$toasted.show(`El producto "${producto.Producto}" tiene precio inválido (0 o negativo)`);
            this.inProgress = false;
            this.loading = false;
            return;
        }
    }

    //RESTRICCION DE STOCK (VENTAS EN 0 O NEGATIVO)

    // for (let producto of this.productos) {
    //     const stock = parseFloat(producto.Stock) || 0;
    //     const cantidad = parseFloat(producto.cantidad) || 0;

    //     if (stock <= 0) {
    //         this.$toasted.show(`El producto "${producto.Producto}" no tiene stock disponible`);
    //         this.inProgress = false;
    //         this.loading = false;
    //         return;
    //     }

    //     if (cantidad > stock) {
    //         this.$toasted.show(`Stock insuficiente para "${producto.Producto}". Disponible: ${stock}`);
    //         this.inProgress = false;
    //         this.loading = false;
    //         return;
    //     }
    // }

    // 2. Validaciones iniciales
    if (obtenerMonto(pagos.efectivo.monto) < 0 || obtenerMonto(pagos.visa.monto) < 0 || obtenerMonto(pagos.mastercard.monto) < 0) {
        this.$toasted.show("Los montos de pago no pueden ser negativos");
        this.inProgress = false; this.loading = false;
        return;
    }

    if (!this.venta.EsCredito && parseFloat(this.totalCart).toFixed(2) != parseFloat(totalPagos).toFixed(2)) {
        this.$toasted.show("Los pagos no coinciden con la venta");
        this.inProgress = false; this.loading = false;
        return;
    }

    if (!this.venta.cliente.DniRuc) {
        this.$toasted.show("Debe seleccionar un cliente.");
        this.inProgress = false; this.loading = false;
        return;
    }

    // Validación RUC/Factura
    const dniRuc = this.venta.cliente.DniRuc;
    if (this.venta.tipoVenta.CodSunat == "01" && dniRuc.length <= 9) {
        this.$toasted.show("Una factura requiere un RUC de empresa.");
        this.inProgress = false; this.loading = false;
        return;
      }
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

    if (!this.productos.length) {
        this.$toasted.show("No hay productos para vender");
        this.inProgress = false; this.loading = false;
        return;
    }

    // 3. Preparar datos globales
    this.venta.productos = this.productos;
    this.venta.totalCart = this.totalCart;
    this.venta.IdPedido = this.$route.query.idPedido || null;

    // 4. Actualizar estado de proformas (Una sola vez para todas)
    const IdProformaDets = this.productos.map(v => v.IdProformaDet).filter(id => id);
    if (IdProformaDets.length > 0) {
        await this.axios.post(`${this.API}/proformas/cambiarPagado?id_empresa=${this.empresa}`, {
            IdProformaDets: IdProformaDets
        });
    }

    // 5. PROCESO MULTIEMPRESA
    let ventasGeneradas = [];
    let errores = [];

    try {
        for (const element of this.$store.state.empresas) {
            try {
                // Filtramos productos de esta empresa
                const productosEmpresa = this.productos.filter(v => v.Empresa == element.IDEMPRESA);

                if (productosEmpresa.length > 0) {
                    // Obtener configuraciones en paralelo
                    const [serie, almacen] = await Promise.all([
                        this.obtenerSerie(element.IDEMPRESA),
                        this.obtenerAlmacen(element.IDEMPRESA)
                    ]);

                    // CLONAMOS el objeto venta para cada empresa
                    let objetoVenta = JSON.parse(JSON.stringify(this.venta));

                    // LIMPIEZA DE PRODUCTOS
                    objetoVenta.productos = productosEmpresa.map(pro => {
                        let p = { ...pro };

                        delete p.nuevoPrecioContado; delete p.Descripcion; delete p.disabledProf;
                        delete p.normalPrecioContado; delete p.Anulado; delete p.Botapie;
                        delete p.Codigo; delete p.CodigoBarra; delete p.Color;
                        delete p.ControlaStock; delete p.Dosis; delete p.EsPadre;
                        delete p.FechaMod; delete p.FechaReg; delete p.Genero;
                        delete p.Hash; delete p.IdBloque;
                        delete p.IdProductoCategoria; delete p.IdProductoFormaFarmaceutica;
                        delete p.IdProductoMarca; delete p.IdProductoMedicion;
                        delete p.IdProductoTalla; delete p.PorcentajeUtilidad;
                        delete p.PrecioContado; delete p.PrecioCosto; delete p.PrecioPorMayor;
                        delete p.ProductoCategoria; delete p.ProductoDesc;
                        delete p.ProductoDescCorto; delete p.ProductoMarca;
                        delete p.ProductoMedicion; delete p.ProductoModelo;
                        delete p.ProductoTalla; delete p.StockMinimo;
                        delete p.StockPorMayor; delete p.UsuarioMod; delete p.UsuarioReg;
                        delete p.VentaEstrategica; delete p.stock;

                        return p;
                    });

                    // Limpieza del Cliente
                    if (objetoVenta.cliente) {
                        delete objetoVenta.cliente.Anulado; delete objetoVenta.cliente.FechaReg;
                        delete objetoVenta.cliente.UsuarioReg; delete objetoVenta.cliente.FechaMod;
                        delete objetoVenta.cliente.UsuarioMod; delete objetoVenta.cliente.FechaNacimiento;
                        delete objetoVenta.cliente.Nacionalidad; delete objetoVenta.cliente.Direccion3;
                        delete objetoVenta.cliente.Telefono; delete objetoVenta.cliente.Email;
                        delete objetoVenta.cliente.Puntos; delete objetoVenta.cliente.EsVIP;
                        delete objetoVenta.cliente.Direccion2; delete objetoVenta.cliente.Sexo;
                        delete objetoVenta.cliente.Ocupacion; delete objetoVenta.cliente.NombreComercial;
                        delete objetoVenta.cliente.esComisionista;
                    }

                    // Limpieza Vendedor y otros
                    if (objetoVenta.vendedor) {
                        delete objetoVenta.vendedor.APPTOKEN; delete objetoVenta.vendedor.APPURL;
                        delete objetoVenta.vendedor.Anulado; delete objetoVenta.vendedor.Password;
                    }

                    delete objetoVenta.idProductos;
                    delete objetoVenta.puntoVenta;

                    // DATOS ESPECÍFICOS DE LA EMPRESA
                    objetoVenta.almacen = almacen;
                    objetoVenta.totalCart = productosEmpresa.reduce((sum, p) => sum + parseFloat(p.total || 0), 0);

                    // EMPRESA
                    objetoVenta.empresa_id = element.IDEMPRESA;
                    objetoVenta.empresa_nombre = element.RAZONSOCIAL || element.RazonSocial || element.NOMBRE || element.Empresa || '';
                    objetoVenta.empresa_ruc = element.RUC || element.Documento || element.NRO_DOCUMENTO || '';
                    objetoVenta.empresa_direccion = element.Direccion || element.DIRECCION || '';
                    objetoVenta.empresa_departamento = element.Departamento || element.DEPARTAMENTO || '';
                    objetoVenta.empresa_provincia = element.Provincia || element.PROVINCIA || '';
                    objetoVenta.empresa_distrito = element.Distrito || element.DISTRITO || '';
                    objetoVenta.empresa_telefonos = element.Telefonos || element.TELEFONOS || '';
                    objetoVenta.empresa_web_consulta = element.WebConsulta || 'http://emision.factura.vip';

                    // DOCUMENTO / FECHAS
                    objetoVenta.FechaDoc = objetoVenta.FechaDoc || new Date().toISOString().slice(0, 19).replace("T", " ");
                    objetoVenta.FechaCredito = objetoVenta.FechaCredito || '';

                    // PAGO / TOTALES
                    objetoVenta.EsCredito = objetoVenta.EsCredito || 0;
                    objetoVenta.PagoCon = this.venta.PagoCon || objetoVenta.PagoCon || 0;
                    objetoVenta.Efectivo = this.venta.Efectivo || 0;
                    objetoVenta.Visa = this.venta.Visa || 0;
                    objetoVenta.Mastercard = this.venta.Mastercard || 0;
                    objetoVenta.Transferencia = this.venta.Transferencia || 0;
                    objetoVenta.Deposito = this.venta.Deposito || 0;
                    objetoVenta.TieneIgv = this.venta.TieneIgv || objetoVenta.TieneIgv || 0;

                    const cantidadEmpresasConVenta = this.lstTotalPorEmpresa.filter(emp => parseFloat(emp.total) > 0).length;
                    objetoVenta.TotalGeneral = cantidadEmpresasConVenta > 1 ? this.sumatoriaTotal : null;

                    // EXTRA
                    objetoVenta.Puntos = this.venta.Puntos || 0;
                    objetoVenta.UsuarioReg = (this.venta.vendedor && this.venta.vendedor.Usuario)
                        ? this.venta.vendedor.Usuario
                        : 'ADMIN';

                    // Guardar en DB
                    const response = await this.axios.post(
                        this.API + "ventas?id_empresa=" + element.IDEMPRESA,
                        objetoVenta
                    );

                    if (response.data && response.data.insertId) {
                        objetoVenta.IdDocVenta = response.data.insertId;

                        objetoVenta.Serie = (
                            response.data.serie ||
                            response.data.Serie ||
                            ''
                        ).toString().trim();

                        objetoVenta.Numero = (
                            response.data.numero ||
                            response.data.Numero ||
                            ''
                        ).toString().trim();

                        // IMPORTANTE: guardar directo la venta
                        ventasGeneradas.push(objetoVenta);

                        this.$toasted.success(
                            `Empresa ${element.IDEMPRESA}: Venta ${objetoVenta.Serie}-${objetoVenta.Numero}`
                        );
                    } else {
                        errores.push(`Empresa ${element.IDEMPRESA}: no devolvió insertId`);
                    }
                }

            } catch (errEmpresa) {
                console.error("Error empresa:", element.IDEMPRESA, errEmpresa);
                errores.push(`Empresa ${element.IDEMPRESA}`);
            }
        }


        // SI HAY ERRORES NO IMPRIMIR

        if (errores.length > 0) {
            console.error("Errores detectados:", errores);
            this.$toasted.error(`Fallaron ${errores.length} empresas. No se imprimió.`);
            this.inProgress = false;
            this.loading = false;
            return;
        }

        // DISPARAR IMPRESIÓN
        if (ventasGeneradas.length > 0) {
             this.limpiarCart();
            let left = (screen.width / 2) - (400 / 2);
            let top = (screen.height / 2) - (400 / 2);
            let params = `scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=350,height=350,top=${top},left=${left}`;

            const basePrintUrl = "http://localhost:5000";

            const respImpresion = await axios.post(
                `${basePrintUrl}/imprimir/guardar_impresion.php`,
                {
                    ventas: ventasGeneradas
                },
                {
                    headers: {
                        "Content-Type": "application/json"
                    },
                    timeout: 15000
                }
            );

            if (!respImpresion.data || !respImpresion.data.archivo) {
                this.$toasted.error("No se pudo preparar la impresión");
                this.inProgress = false;
                this.loading = false;
                return;
            }

            const archivo = respImpresion.data.archivo;

            const ventanaImpresion = window.open(
                `${basePrintUrl}/imprimir/getdata.php?archivo=${encodeURIComponent(archivo)}`,
                "ImpresionDirecta",
                params
            );

            if (!ventanaImpresion) {
                this.$toasted.show("El navegador bloqueó la ventana de impresión");
                this.inProgress = false;
                this.loading = false;
                return;
            }

            

            setTimeout(() => {
                window.location.reload();
            }, 2500);
        }

    } catch (error) {
        console.error("Error Multiempresa:", error);
        this.$toasted.error("Error al procesar una de las ventas");
    } finally {
        this.inProgress = false;
        this.loading = false;
    }

    if (errores.length > 0) {
    this.$toasted.error(`Fallaron ${errores.length} empresas. No se imprimió.`);
    return;
    }
  },
    emitirauto(idDocVenta, id_empresa) {
      this.axios
        .post(this.API + "emitirauto?id_empresa=" + id_empresa, {
          IdDocVenta: idDocVenta,
          IdEmpresa: id_empresa
        })
        .then(response => {
          if (response.data.success) {
            if (response.data.Estado == 1) {
              this.$toasted.error("SUNAT: " + response.data.msj_sunat);
            } else {
              this.$toasted.info("SUNAT: " + response.data.msj_sunat);
            }
          }
        })
        .catch(error => {
          this.$toasted.show("Ha ocurrido un error, en la emision a sunat");
        });
    },
    eliminarCliente() {
      this.$refs.inputCliente.setValue("");
      this.venta.cliente = {};
    },
    eliminarComisionista() {
      this.$refs.inputComisionista.setValue("");
      this.venta.comisionista = {};
    },
    onPagarDelivery(obj) {
      this.$route.query.idPedido = obj.IdPedido;
      if (this.$route.query.idPedido) {
        this.infoDelivery = obj;
        this.venta.EsDelivery = true;
        this.$refs.carrito.pagocon = parseFloat(
          parseFloat(obj.Cambio).toFixed(2)
        ).toFixed(2);
        //cargar costo de delivery
        let costoDelivery = parseFloat(obj.PrecioDelivery || 0).toFixed(2);
        if (costoDelivery > 0) {
          this.axios
            .get(this.API + "costo/delivery?id_empresa" + this.empresa)
            .then(response => {
              if (response.data) {
                let costoDeliverObj = response.data;
                costoDeliverObj.precio = costoDelivery;
                costoDeliverObj.cantidad = 1;
                costoDeliverObj.EsCostoDelivery = 1;
                costoDeliverObj.IdImpresora = 0;
                costoDeliverObj.descuento = 0;
                costoDeliverObj.indicacion = "";
                this.add([
                  response.data,
                  () => {
                    //this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 })
                  },
                  { setPrecioCosto: false },
                  false,
                  false
                ]);
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
                      indicacion: item.Indicacion
                    },
                    false
                  ]);
                });
                this.venta.nombreLlevar = obj.Cliente || "SIN NOMBRE";
                this.venta.DireccionEntrega = obj.Direccion;
                this.$toasted.success("PRODUCTOS DEL DELIVERY AGREGADOS");
              } else {
                this.$toasted.show(
                  "PRIMERO CREE EL PRODUCTO COSTO DE DELIVERY CON EL CODIGO DE BARRAS 'COSTODELIVERY'"
                );
                this.anularPagoDelivery();
              }
            })
            .catch(error => {
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
          this.venta.nombreLlevar = obj.Cliente || "SIN NOMBRE";
          this.venta.DireccionEntrega = obj.Direccion;
        }
      }
    },
    anularPagoDelivery() {
      this.limpiarCart();
      this.venta.EsDelivery = false;
      this.infoDelivery = {};
      this.$refs.carrito.pagocon = "";
      this.loadClienteVarios();
      this.venta.nombreLlevar = "";
      this.venta.selectOpcionVenta = 0;
      this.venta.nombreLlevar = "";
      this.venta.DireccionEntrega = "";
      this.$router.replace("/ventas");
    },
    //Sockets
    escucharPedidos() {
      this.sockets.subscribe("nuevo-pedido", data => {
        if (this.$refs.deliveryModal.show) {
          this.$toasted.success(
            "Nuevo Pedido de" + "<strong>" + data.nombre + "</strong>"
          );
          this.$refs.deliveryModal.getPedidos();
        } else {
          this.$refs.deliveryModal.getPedidos();
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
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
              this.$refs.deliveryModal.show = true;
            } else if (result.isDenied) {
              console.log("Denegado");
            }
          });
        }
      });
    },
    //Fin Sockets
    async obtenerSerie(empresa) {
      this.usuario = sessionStorage.getItem("User");

      let res = await this.axios.get(`${this.API}/ventas/numero`, {
        params: {
          usuario: this.usuario,
          idTipoDoc: this.venta.tipoVenta.IdTipoDoc,
          id_empresa: empresa
        }
      });

      return res.data;
    },
    async obtenerAlmacen(empresa) {
      let res = await this.axios(
        `${this.API}almacenes/id/1?&id_empresa=${empresa}`
      );
      return res.data;
    },
    async obtenerDatosEmpresa(idEmpresa) {
        // Asegúrate de que la ruta coincida con tu backend
        const res = await this.axios.get(`${this.API}obtener_datos_empresa?id_empresa=${idEmpresa}`);
        return res.data;
    },
    async obtenerDatosImpresora(empresa) {
      let res = await this.axios(
        `${this.API}obtener_datos_impresora?id_empresa=${empresa}`
      );
      return res.data;
    }
  },

  components: {
    ProductosCarrito,
    ClientesForm,
    ConfigVenta,
    PagoDeuda,
    PreOrdenLista,
    Proformas,
    DeliveryModal,
    Movimiento
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
@media only screen and (max-width: 700px) {
  /* For desktop: */
  .ocularMovil {
    display: none;
  }
}

@media only screen and (min-width: 777px) and (max-width: 1800px) {
  /* For desktop: */
  .ancho {
    width: calc(100% - 300px);
  }
}

.modasm {
  background: #F8FAFB;
}
.modasgio {
  background: #c1e7ff;
}
.modasjes {
  background: #D7F3F4;
}
.modasjun {
  background: #c2ffc0;
}
.modaspau {
  background: #F3F3DC;
}

</style>
