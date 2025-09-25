<template>
  <div :class="this.empresa">
    <b-tabs v-model="tab" pills fill justified>
      <b-tab :title="`Venta ${index +1}`" v-for="(venta,index) in ventas" :value="index" :key="index">
        <b-row style="margin-bottom: -10px">
          <b-col md="7">
            <b-row>
              <b-col md="4">
                <b-form-group>
                  <b-input-group prepend="Doc." class="mt-2">
                    <autocomplete
                      required="required"
                      :ref="`inputTipoVenta${index}`"
                      :url="API + 'ventas/tipos'"
                      anchor="TipoDoc"
                      initValue="NOTA DE VENTA"
                      label="writer"
                      placeholder=""
                      :on-select="setTipoVenta"
                      :customParams="{ id_empresa: empresa }"
                      :onFocus="focusAutocomplete.bind(null, `inputTipoVenta${index}`)"
                      :classes="{ input: 'form-control' }"
                    >
                    </autocomplete>
                  </b-input-group>
                </b-form-group>
                <b-form-group v-if="isNota">
                  <label>Doc. Referencia:</label>
                  <autocomplete
                    required="required"
                    :ref="`inputDocRef${index}`"
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
                    :onFocus="focusAutocomplete.bind(null, `inputDocRef${index}`)"
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
                      :ref="`inputAlmacen${index}`"
                      :url="API + 'almacenes'"
                      anchor="Almacen"
                      initValue="PRINCIPAL"
                      label="writer"
                      placeholder=""
                      :on-select="setAlmacen"
                      :customParams="{ id_empresa: empresa }"
                      :onFocus="focusAutocomplete.bind(null, `inputAlmacen${index}`)"
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
                      :ref="`inputPuntoVenta${index}`"
                      :url="API + 'ventas/puntos'"
                      anchor="PuntoVenta"
                      initValue="PRINCIPAL"
                      label="writer"
                      placeholder=""
                      :on-select="setPuntoVenta" 
                      :customParams="{ id_empresa: empresa }"
                      :onFocus="focusAutocomplete.bind(null, `inputPuntoVenta${index}`)"
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
                      :ref="`inputCliente${index}`"
                      :url="API + 'clientes'"
                      anchor="ClienteDniRuc"
                      label="writer"
                      placeholder=""
                      :onInput="(val) => (newDniRuc = val)"
                      :on-select="setCliente"
                      :customParams="{ id_empresa: empresa }"
                      :onFocus="focusAutocomplete.bind(null, `inputCliente${index}`)"
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
                      <clientes-form
                        :ruc="newDniRuc"
                        @onAddCliente="setCliente"
                      ></clientes-form>
                    </b-input-group-append>
                  </b-input-group>
                  <b-form-select
                    v-if="ventas[index].cliente.IdCliente"
                    v-model="ventas[index].CampoDireccion"
                    class="mb-3"
                  >
                    <option :value="venta.cliente.Direccion">
                      {{ ventas[index].cliente.Direccion }}
                    </option>
                    <option :value="venta.cliente.Direccion2">
                      {{ ventas[index].cliente.Direccion2 }}
                    </option>
                    <option :value="venta.cliente.Direccion3">
                      {{ ventas[index].cliente.Direccion3 }}
                    </option>
                  </b-form-select>
                  <b-input-group v-if="ventas[index].cliente.DniRuc == '00000000'">
                    <b-form-checkbox
                      :disabled="bloquearInput ? true : false"
                      v-model="ventas[index].EsOrganizacion"
                      :value="1"
                      :unchecked-value="0"
                    >
                      Es Organización
                    </b-form-checkbox>
                    <input
                      v-model="ventas[index].NombreOrganizacion"
                      type="text"
                      style="text-transform: uppercase"
                      class="form-control"
                      required
                      placeholder="Nombre de la Organización"
                      v-if="ventas[index].EsOrganizacion"
                    />
                  </b-input-group>
                  <!-- <b-row v-if="this.$route.query.idPedido">
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
                  </b-row> -->
                </b-form-group>
              </b-col>
              
            </b-row>
          </b-col>
           <b-col md="3" class="ocularMovil">
            <b-form-group class="text-center" v-if="ventas[index].cliente.Cliente">
              <div class="puntos">
                {{ (ventas[index].cliente.Puntos || 0) | redondeoMenor }}
              </div>
              Puntos del cliente
            </b-form-group>
            <b-input-group
              prepend="Puntos"
              :append="descuentoPuntos.descuento | redondear"
              v-if="ventas[index].cliente.Puntos && descuentoGeneral == 0"
            >
              <b-form-input
                v-model="puntosAplicados"
                min="0"
                :max="ventas[index].cliente.Puntos"
                @input="aplicarPuntos"
                type="number"
              ></b-form-input>
            </b-input-group>
            <b-form-text v-if="descuentoPuntos.ValorPunto">
              1 Punto equivale a: S/
              {{ descuentoPuntos.ValorPunto | redondear }}
            </b-form-text>
            <b-form-text v-if="ventas[index].cliente.EsVIP">
              Cliente VIP con Precio Convenio
            </b-form-text>
          </b-col>
            <b-col md="2">
            <b-form-group class="text-center">
              <div class="big-text">
                {{ ventas[index].Serie }} - <b>{{ ventas[index].Numero }}</b>
              </div>
              {{ ventas[index].FechaDoc }}
            </b-form-group>
            <b-form-group class="ocularMovil">
              <label>Vendedor:</label>
              <autocomplete
                required="required"
                :ref="`inputVendedor${index}`"
                :url="API + 'usuarios'"
                :customParams="{ idPerfil: idPerfil ,id_empresa : empresa}"
                anchor="NombreUsuario"
                label="writer"
                :on-select="setVendedorManual"
                :onFocus="focusAutocomplete.bind(null, `inputVendedor${index}`)"
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
        <!-- --------------------------- -->
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
              <b-form-group>
                <b-input-group>
                  <b-button @click="abrirModaProductos" variant="info" class="mx-2 btnResponsive" :disabled="lstProductos.length==0">Agregar Productos
                  </b-button>
                  <autocomplete
                    :ref="`autocompleteProducto${index}`"
                    :url="APIProductos"
                    :customParams="{
                      sortBy: 'stock',
                      sortDesc: 1,
                      limit: '25',
                      id_empresa: empresa 
                    }"
                    :onShouldRenderChild="renderChild"
                    anchor="Producto"
                    label="writer"
                    placeholder="Agregar Producto"
                    :on-select="anadirarProductosSeleccionado"
                    :onFocus="focusAutocomplete.bind(null,`autocompleteProducto${index}`)"
                    :classes="{ input: 'form-control' }"
                  >
                  </autocomplete>
                  <input
                    v-model="loadCode"
                    @keydown.enter.prevent="loadBarcode"
                    type="text"
                    class="form-control"
                    placeholder="Codigo de Barra"
                  />
                  <b-input-group-append>
                  
                    <b-button variant="primary" @click="loadBarcode"
                      ><i class="fas fa-search"></i
                    ></b-button>
                    <producto-form
                      :showButton="showButton"
                      :cbGuardar="
                        (id, pxp, obj) => {
                          agregarGuardar(id, pxp, obj);
                        }
                      "
                      ref="productoFormEdit"
                      :producto-update="productoSelect"
                    ></producto-form>
                  </b-input-group-append>
                </b-input-group>
                <span class="ml">Búsqueda Avanzanda (Producto,Categoría,Marca)</span>
              </b-form-group>
            
            <b-table
              :fields="carrito.fields" 
              :items="venta.productos"
              :id="`TablaCarrito${index}`"
              :ref="`TablaCarrito${index}`"
            >
              <template slot="Series" slot-scope="data">
                <b-select 
                    v-if="generarSerie(data.item.productoSerie).length>0"
                    :options="generarSerie(data.item.productoSerie)" 
                    v-model="data.item.IdProductoSerie"
                    text-field="Serie"
                    value-field="IdProductoSerie"
                />  
              </template>
              <template slot="eventPres" slot-scope="data">
                <b-button
                  size="sm"
                  v-if="data.item.estadoPxP == false"
                  :disabled="bloquearInput ? true : false"
                  variant="info"
                  @click="showPresentacion(data.item,data.index)"
                  >
                  <i class="fas fa-list"></i>
                  </b-button>
                <b-button
                  @click="
                    abrirImagen(
                      data.item.Producto,
                      data.item.Imagen == null ||
                        data.item.Imagen == ''
                        ? 'static/no-photo.png'
                        : '/resources/images/productos/' +
                            data.item.Imagen
                    )
                  "
                  style="margin-top: 2px"
                  :id="'popover-imagen' + data.item.IdProducto"
                  variant="success"
                  size="sm"
                  ><i class="fas fa-image"></i
                ></b-button>
                <b-popover
                  placement="right"
                  :target="'popover-imagen' + data.item.IdProducto"
                  :title="data.item.Producto"
                  triggers="hover"
                >
                  <div style="text-align: center">
                    <b-img
                      style="border-radius: 10px; width: 200px"
                      fluid
                      :src="
                        data.item.Imagen == null ||
                        data.item.Imagen == ''
                          ? 'static/no-photo.png'
                          : '/resources/images/productos/' +
                            data.item.Imagen
                      "
                      alt=""
                      @error="setAltImg"
                    ></b-img>
                  </div>
                </b-popover>
              </template>

        
        <template
          slot="cantxPrecio"
          slot-scope="data"
          v-if="data.item.tipo == 'PRESENTACION'"
        >
          <input
            v-model="data.item.cantxPrecio"
            type="number"
            style="width: 80px"
            step="0.001"
            min="0"
            :disabled="bloquearInput ? true : false"
            placeholder="Cantidad"
            class="form-control"
          />
          <span>x{{ data.item.cantidadPres }}</span>
        </template>

         <template slot="cantidad" slot-scope="data">
          <input
            v-model="data.item.cantidad"
            :disabled="data.item.tipo == 'PRESENTACION'"
            type="number"
            style="width: 80px"
            class="form-control"
            step="0.001"
            min="0"
            placeholder="Cantidad"
          />
        </template>


        <template slot="precio" slot-scope="data">
          <input
            v-model="data.item.precio"
            type="number"
            style="width: 95px"
            min="0"
            step="0.00001"
            class="form-control"
            placeholder="Precio"
            />
        </template>

        <template slot="subtotal" slot-scope="data">
          {{ getSubtotal(data.index,data.item) }}
        </template>

        <template slot="total" slot-scope="data">
              {{ getTotal(data.index,data.item) }}
              <br />
              <h6><i>Prof.</i></h6>
              <input
                class="form-check-input"
                type="checkbox"
                :disabled="bloquearInput ? true : false"
                @change="
                  (e) => {
                    $emit('onCheckProf', e);
                    asignarDisabledProf(data.index, e);
                  }
                "
              />
            </template>
        

        <template slot="descuento" slot-scope="data">
          <input
            :disabled="bloquearInput ? true : false"
            v-model="data.item.descuento"
            style="width: 90px"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            value="0"
          />
          <b-form-text> </b-form-text>
        </template>

       

        <template slot="Descripcion" slot-scope="data">
          <textarea
            :disabled="bloquearInput ? true : false"
            class="form-control"
            placeholder="Descripción"
            cols="9"
            rows="1"
            style="width: 150px"
            v-model="data.item.Descripcion"
          ></textarea>
        
        </template>

            

            <template slot="acciones" slot-scope="data">
              <b-button
                :disabled="bloquearInput ? true : false"
                variant="danger"
                size="sm"
                @click="quitarProducto(data.index)"
                ><i class="fas fa-times fa-lg"></i
              ></b-button>
            
            </template>

           
            <template slot="stock" slot-scope="data">
              <div v-if="data.item.CodigoBarra == 'COSTODELIVERY'">
                <img src="static/moto-delivery.png" style="width: 24px" />
              </div>
              <div v-else>
                <div>{{ data.item.stock }}</div>
                <img
                  v-if="data.item.EsPadre"
                  src="static/box.png"
                  style="width: 24px"
                />
              </div>
            </template>


            </b-table>
          </b-col>
          <!-- <b-col cols="4">
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
          </b-col> -->
         
          <!-- <b-col cols="4" style="text-align: center;">
            <b-button
              type="button"
              @click="anularPagoDelivery"
              variant="danger"
              v-if="this.$route.query.idPedido"
              >Anular Pago Delivery</b-button
            >
            <b-button
              type="button"
              v-if="!this.$route.query.idPedido"
              :disabled="inProgress"
              variant="primary"
              size="lg"
              @click="guardarProforma"
              class="btnAction"
              >Crear Proforma</b-button
            >
            <b-button
              type="submit"
              :disabled="inProgress"
              style="background: #004D40;"
              size="lg"
              class="btnAction"
              v-if="idPerfil == 1 || idPerfil == 14"
              >Efectuar Venta</b-button
              >
            <b-button
              @click="anadirVentana"
              :disabled="inProgress"
              style="background: #FFEA00; color:#000000"
              size="lg"
              class="btnAction"
              >Añadir nueva ventana</b-button
              >
            
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
            
              <proformas
                :idAlmacen="venta.almacen.IdAlmacen"
                :empresa="empresa"
                @onSelectProforma="setCliente"
              />
          
             <config-venta
                @onUpdateConfig="eliminarCliente"
                @onUpdateConfigComision="eliminarComisionista"
              ></config-venta>
          </b-col> -->

          <!-- <b-col cols="4">
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
                              @input="venta.pago.efectivo.monto = $options.filters.redondear(getTotal() - venta.pago.mastercard.monto - $event.target.value)"
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
                            @input="venta.pago.efectivo.monto = $options.filters.redondear(getTotal() - venta.pago.visa.monto - $event.target.value)" 
                          />
                         
                        </td>
                      </tr>
                      <tr>
                        <td>Transferencia</td>
                        <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(4)"><i class="fas fa-check"></i></b-button></td>
                        <td>
                          <b-form-input v-model="venta.pago.delivery.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                        </td>
                        <td>
                          <input
                            class="form-control" v-model="venta.pago.delivery.monto" min="0" step="0.01" type="number" placeholder="S/."
                             @input="venta.pago.efectivo.monto = $options.filters.redondear(getTotal() - venta.pago.visa.monto - $event.target.value)" 
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
                            v-model="EsCredito"
                            :value="1"
                            :unchecked-value="0">
                            Venta a credito
                          </b-form-checkbox>
                          <b-form-input 
                            v-if="EsCredito"
                            v-model="FechaCredito"
                            type="date">
                          </b-form-input>
                                                
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
              <b>S/ </b>
            </td>
          </tr>
        </tbody>
      </table>
          </b-col> -->

          
        </b-row>
      </b-tab>
    </b-tabs>
    <!--<b-alert variant="warning" show class="text-center" v-if="this.$route.query.idPreOrden">Se está pagando la habitación HH</b-alert>-->
     <b-modal id="modal-1" :title="`${itemProducto.Producto}`"
     hide-footer v-model="modalPresentaciones"
      v-if="modalPresentaciones" >
      <b-row class="px-4">
        <b-col cols="6">
          <b-card  @click="guardarItemPorUnidad()" class="cardPresentacion">
              <b-row>
                <b-col cols="6"> UNIDAD </b-col>
                <b-col cols="6" class="borderIzquierdo">
                  <b>
                    Precio: <br>
                    {{itemProducto.PrecioContado}}
                  </b>

                  </b-col>
              </b-row>
          </b-card>
          
          <b-card v-for="(item ,index) in itemProducto.PreciosPorProducto.filter(v=>v.precio!=0)" :key="index" class="my-2 cardPresentacion" @click="guardarItem(item)">
              <b-row>
                <b-col cols="6">{{item.nombre}}</b-col>
                <b-col cols="6" class="borderIzquierdo">
                  <b>
                    Precio: <br>
                    {{item.precio}}
                  </b>

                  </b-col>
              </b-row>
          </b-card>

        </b-col>
        <b-col cols="6" class="borderIzquierdoPunteado pr-4 pl-5" >
          <b-row>
            <b-col cols="12" class="px-0 montoPresenacion">
              <b-form-input class="my-2 " type="number"
              ref="montoPresentacion"
               id="montoPresentacion" v-model="monto" placeholder="Ingrese Cantidad" 
              @input="monto == 0 ? errorMontoPresentacion='Ingrese una cantidad' : errorMontoPresentacion =''"/>
                  <span class="text-red">{{errorMontoPresentacion}}</span>

            </b-col>
            <b-col cols="4" class="p-0 mt-1 px-1" v-for="(numeros,index) in numerosCalculadora" :key="index">
                  <b-button @click="aumentarMontoPresentacion(numeros)" block variant="outline-dark" v-if="numeros.tipo=='backspace'">
                    <i :class="numeros.valor"></i>
                  </b-button>
                 <b-button @click="aumentarMontoPresentacion(numeros)" v-else block  variant="outline-dark">{{numeros.valor}}</b-button>
            </b-col>
          </b-row>
             

        </b-col>
      </b-row>
    </b-modal>

    <b-modal id="modal-1" :title="`${itemProducto.Producto}`" hide-footer v-model="modalPresentacionesEditar" v-if="modalPresentacionesEditar">
      <b-row class="px-4">
        <b-col cols="6">
          <b-card  @click="guardarItemPorUnidadEditar()" class="cardPresentacion">
              <b-row>
                <b-col cols="6"> UNIDAD </b-col>
                <b-col cols="6" class="borderIzquierdo">
                  <b>
                    Precio: <br>
                    {{itemProducto.PrecioContado}}
                  </b>

                  </b-col>
              </b-row>
          </b-card>
          <b-card v-for="(item ,index) in itemProducto.PreciosPorProducto.filter(v=>v.precio!=0)" :key="index" class="my-2 cardPresentacion" @click="guardarItemEditar(item)">
              <b-row>
                <b-col cols="6">{{item.nombre}}</b-col>
                <b-col cols="6" class="borderIzquierdo">
                  <b>
                    Precio: <br>
                    {{item.precio}}
                  </b>

                  </b-col>
              </b-row>
          </b-card>

        </b-col>
        <b-col cols="6" class="borderIzquierdoPunteado pr-4 pl-5" >
          <b-row>
            <b-col cols="12" class="px-0 montoPresenacion">
              <b-form-input class="my-2 " type="number" id="montoPresentacion" v-model="monto" placeholder="Ingrese Cantidad" 
              @input="monto == 0 ? errorMontoPresentacion='Ingrese una cantidad' : errorMontoPresentacion =''"/>
                  <span class="text-red">{{errorMontoPresentacion}}</span>

            </b-col>
            <b-col cols="4" class="p-0 mt-1 px-1" v-for="(numeros,index) in numerosCalculadora" :key="index">
                  <b-button @click="aumentarMontoPresentacion(numeros)" block variant="outline-dark" v-if="numeros.tipo=='backspace'">
                    <i :class="numeros.valor"></i>
                  </b-button>
                 <b-button @click="aumentarMontoPresentacion(numeros)" v-else block  variant="outline-dark">{{numeros.valor}}</b-button>
            </b-col>
          </b-row>
             

        </b-col>
      </b-row>
      
    </b-modal>

     <b-modal v-model="anadirSerieFlag" hide-footer size="md" title="Agregar Series">
      <h5 class="mt-2 mb-4">Producto: {{ objProd.Producto }}</h5>
      <b-row>
        <b-col cols="12">
          <b-input-group prepend="Serie">
            <b-input-group-append>
                <input type="text" class="form-control" id="txtSerie" v-model="Serie">
            </b-input-group-append>
            <b-button variant="info" size="sm" @click="anadirSerie()">AÑADIR</b-button>
          </b-input-group>
        </b-col>
        <b-col cols="6">
          
        </b-col>
     
         <b-col cols="12">   
          <table width="100%" class="mt-3">
              <thead>
                <tr>

                  <th>#</th>
                  <th>Serie</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(serie,index) in Series" :key="index">
                  <td>{{ index+1 }}</td>
                  <td>
                    <input
                    type="text"
                    class="form-control"
                    :value="serie.Serie"
                    @input="updateSerie(index, $event.target.value)"
                  >
                  </td>
                  <td>
                    <b-button variant="danger" @click="eliminarSerieModal(index)">
                      <i class="fa fa-trash" aria-hidden="true"></i>
                    </b-button>
                  </td>
                </tr>
              </tbody>
          </table>
       </b-col>
       <b-col cols="12" style="text-align: right;" class="mt-3">
          <b-button @click="guardarSerie" variant="success">Guardar</b-button>
          <b-button @click="anadirSerieFlag=!anadirSerieFlag" variant="danger">Cancelar</b-button>
       </b-col>
      </b-row>
       
    </b-modal>

     <b-modal
      size="xl"
      v-model="modalNuevoProductos"
      title="Selección Productos"
      class="modalSelectProduct modal-xl"
    >
      <b-row>
        <b-col cols="12">
          <b-input-group>
            <b-form-input
              v-model="searchProducto"
              placeholder="Buscar..."
              class="form-control inputResponsive"
            ></b-form-input>
            <b-input-group-append>
              <b-button @click="startRecognition()">
                <i class="fas fa-microphone"></i>
              </b-button>
            </b-input-group-append>
            <input
              v-model="IdProducto"
              @input="buscarProductosCodigoBarrasModal"
              type="text"
              class="form-control ml-2  inputResponsive"
              placeholder="Codigo de Barra"
               v-on:keyup.enter="anadirProductos"
            />
            <b-input-group-append>
              <b-button
                variant="primary"
                @click="buscarProductosCodigoBarrasModal"
                ><i class="fas fa-search"></i
              ></b-button>
            </b-input-group-append>
          </b-input-group>
          <p>Búsqueda Avanzanda (Producto,Categoría,Marca)</p>
        </b-col>
        <b-col cols="12" class="mt-3">
          <table class="table table-hover" width="100%">
              <thead>
                <tr>
                  <th>Empresa</th>
                  <th>Categoria</th>
                  <th>Marca</th>
                  <th>Producto</th>
                  <th>Stock</th>
                  <th>Stock</th>
                  <th>Precio</th>
                  <th>Imagen</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prod,index) in listadoProductos()" :key="index"  @dblclick="anadirarProductosSeleccionado(prod,true)">
                  <td>{{ prod.NombreEmpresa }}</td>
                  <td>{{ prod.ProductoCategoria }}</td>
                  <td>{{ prod.ProductoMarca }}</td>
                  <td>{{ prod.Producto }}</td>
                  <td>{{ prod.stock }}</td>
                  <td>{{ prod.StockMinimo }}</td>
                  <td>{{ prod.PrecioContado }}</td>
                  <td>
                    <img
                    :src="
                       prod.Imagen == null ||  prod.Imagen == ''
                        ? 'static/no-photo.png'
                        : `/resources/images/productos/${ prod.Imagen}`
                    "
                    class="imagen"
                  />
                  </td>
                </tr>
              </tbody>
          </table>

          <div class="pagination mt-3">
            <b-button size="sm" variant="outline-dark"  @click="prevPage" :disabled="currentPage === 1">Anterior</b-button>
            <span class="mx-2 my-3">Página {{ currentPage }} de {{ totalPages }}</span>
            <b-button size="sm" variant="outline-dark"  @click="nextPage" :disabled="isLastPage">Siguiente</b-button>
          </div>

          <b-row>
            <b-col cols="8">
           
          </b-col>
            <b-col cols="4">
              <b-row>
                <b-col cols="6">  
                  Nro De Filas:
                </b-col>
                <b-col cols="6">
                  <b-form-select
                    v-model="perPageModal"  
                    :options="perPageModalList"
                    size="small"
                    class="form-control"
                  ></b-form-select>
                </b-col>
              </b-row>
            
            
            </b-col>
          </b-row>
           
           
        </b-col>
      </b-row>
      
    </b-modal>


    <!-- PRE -->
    <b-container fluid class="ancho" id="conten">
      <!-- <div>
        <div>
          <b-button-group size="sm">
            <b-input-group prepend="Empresa:" class="mt-2">
              <b-form-select
              v-model="empresa"
              :options="$store.state.empresas"
              size="lg"
              class="form-control"
              @change="cambiarEmpresa"
              ></b-form-select>
            </b-input-group>
           
          </b-button-group>
        </div>  
      </div> -->
     
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
     <b-row  v-if="ventas[tab]">
        <b-col cols="4">
              <table class="table b-table border table-footer table-sm">
                    <tbody>
                      <tr style="background: #1A237E; color: #ffffff;">
                        <td colspan="2" class="text-center" style="text-transform: uppercase;font-size: 1.2rem; font-weight: bold;">Total por Empresa</td>
                      </tr>
                        <tr v-for="(empresa,index) in lstTotalPorEmpresa()" :key="index">
                          <th>{{empresa.nombre}}</th>
                          <td>{{empresa.total}}</td>
                        </tr>
                    </tbody>
                  </table>
        </b-col>
        <b-col cols="4" style="text-align: center;">
              <b-button
                type="button"
                @click="anularPagoDelivery"
                variant="danger"
                v-if="this.$route.query.idPedido"
                >Anular Pago Delivery</b-button
              >
              <b-button
                type="button"
                v-if="!this.$route.query.idPedido"
                :disabled="inProgress"
                variant="primary"
                size="lg"
                @click="guardarProforma"
                class="btnAction"
                >Crear Proforma</b-button
              >
              <b-button
                type="submit"
                :disabled="inProgress"
                style="background: #004D40;"
                size="lg"
                class="btnAction"
                v-if="idPerfil == 1 || idPerfil == 14"
                >Efectuar Venta</b-button
                >
              <b-button
                @click="anadirVentana"
                :disabled="inProgress"
                style="background: #FFEA00; color:#000000"
                size="lg"
                class="btnAction"
                >Añadir nueva ventana</b-button
                >
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
                <proformas
                  :idAlmacen="ventas[tab].almacen.IdAlmacen"
                  :empresa="empresa"
                  @onSelectProforma="anadirProforma"
                />
            
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
                            <b-form-input v-model="ventas[tab].pago.efectivo.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                          </td>
                          <td>
                            <input v-model="ventas[tab].pago.efectivo.monto" min="0" step="0.01" type="number" placeholder="S/." class="form-control"/>
                          </td>
                        </tr>
                        <tr>
                          <td>Tarjeta</td>
                          <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(2)"><i class="fas fa-check"></i></b-button></td>
                          <td>
                            <b-form-input v-model="ventas[tab].pago.visa.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                          </td>
                          <td>
                            <input class="form-control" v-model="ventas[tab].pago.visa.monto" min="0" step="0.01" type="number" placeholder="S/." 
                                @input="ventas[tab].pago.efectivo.monto = $options.filters.redondear(getTotal() - ventas[tab].pago.mastercard.monto - $event.target.value)"
                              />
                          </td>
                        </tr>
                        <tr>
                          <td>Yape/Plin</td>
                          <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(3)"><i class="fas fa-check"></i></b-button></td>
                          <td>
                            <b-form-input v-model="ventas[tab].pago.mastercard.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                          </td>
                          <td>
                            <input class="form-control" v-model="ventas[tab].pago.mastercard.monto" min="0" step="0.01" type="number" placeholder="S/." 
                              @input="ventas[tab].pago.efectivo.monto = $options.filters.redondear(getTotal() - ventas[tab].pago.visa.monto - $event.target.value)" 
                            />
                          
                          </td>
                        </tr>
                        <tr>
                          <td>Transferencia</td>
                          <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(4)"><i class="fas fa-check"></i></b-button></td>
                          <td>
                            <b-form-input v-model="ventas[tab].pago.delivery.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                          </td>
                          <td>
                            <input
                              class="form-control" v-model="ventas[tab].pago.delivery.monto" min="0" step="0.01" type="number" placeholder="S/."
                              @input="ventas[tab].pago.efectivo.monto = $options.filters.redondear(getTotal() - ventas[tab].pago.visa.monto - $event.target.value)" 
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
                              v-model="EsCredito"
                              :value="1"
                              :unchecked-value="0">
                              Venta a credito
                            </b-form-checkbox>
                            <b-form-input 
                              v-if="EsCredito"
                              v-model="FechaCredito"
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
                <b>S/ </b>
              </td>
            </tr>
          </tbody>
        </table>
        </b-col>
      </b-row>
     
    </b-container>
  </div>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import ProductosCarrito from "@/components/ProductosCarritoMultVenta";
import ClientesForm from "@/components/ClientesForm";
import ConfigVenta from "@/components/ConfigVenta";
import PagoDeuda from "@/components/PagoDeuda";
import PreOrdenLista from "@/components/PreOrdenLista.vue";
import Proformas from "@/components/ProformaLista.vue";
import DeliveryModal from "@/components/DeliveryModal.vue";
import Swal from "sweetalert2";
import ProductoForm from "./../components/ProductoForm.vue";
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
        url.indexOf(location.protocol + "//") !== -1
          ? url
          : location.protocol + "//" + location.host + url;
      newUrl = new URL(newUrl);

      newUrl.searchParams.append(
        "empresa",
        sessionStorage.getItem("empresa") || "modasm"
      );

      url = newUrl.toString();
      arguments[1] = url;

      return oldXHROpen.apply(this, arguments);
    };
    this.initForm();
    this.setScreen();
  },
  async mounted() {
    this.$store.state.MostrarBotonMultipleEmpresa = true;
    if (sessionStorage.getItem("empresa")) {
      this.empresa = sessionStorage.getItem("empresa");
    }
    //setInterval(this.updateTime, 1000)
    setTimeout(async () => {
      Promise.all([
        this.getVendedor(),
        this.loadClienteVarios(),
        this.escucharPedidos(),
        this.configStock()
        // this.recalcularTotalesPorEmpresa()
      ]);
    }, 500);
    let vm = this;
    vm.lstProductos = [];
    for (const empresa of vm.$store.state.empresas) {
      let url = `${
        vm.API
      }productos/puntoventa/modal/stock/1?q&sortBy=stock&sortDesc=1&id_empresa=${
        empresa.value
      }&limit=`;

      let res = await vm.axios(url); // Espera la respuesta antes de continuar

      res.data.forEach(element => {
        vm.lstProductos.push({
          ...element,
          Empresa: empresa.value,
          Selected: false,
          NombreEmpresa: empresa.text.substring(0, 20) + "...",
          _rowVariant:
            element.stock <= 0
              ? "danger"
              : element.stock < element.StockMinimo
                ? "warning"
                : "success"
        });
      });
    }
  },
  data() {
    return {
      monto: 0,
      primerMonto: true,
      perPageModal: 100,
      currentPageModal: 1,
      IdProducto: "",
      errorMontoPresentacion: "",
      numerosCalculadora: [
        { valor: "7", tipo: "number" },
        { valor: "8", tipo: "number" },
        { valor: "9", tipo: "number" },
        { valor: "4", tipo: "number" },
        { valor: "5", tipo: "number" },
        { valor: "6", tipo: "number" },
        { valor: "1", tipo: "number" },
        { valor: "2", tipo: "number" },
        { valor: "3", tipo: "number" },
        { valor: "0", tipo: "number" },
        { valor: "fas fa-backspace", tipo: "backspace" },
        { valor: "CE", tipo: "limpiar" }
      ],
      Serie: "",
      Series: [],
      loadCode: "",
      lstProductos: [],
      productoSelect: [],
      modalNuevoProductos: false,
      modalPresentaciones: false,
      modalPresentacionesEditar: false,
      anadirSerieFlag: false,
      objProd: {},
      itemProducto: {},
      showButton: false,
      searchProducto: "",
      ventas: [],
      tab: 0,
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
      ventaInit: {},
      mostrar: false,
      mostrarOp: false,
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
          //{ key: 'manoobra', label: 'Mano de obra', class:'text-center' },
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center"
          },
          { key: "Producto" },
          { key: "Series" },
          // { key: "AddSeries" },
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoCategoria", label: "Categoria" },
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
      empresa: "modasm",
      ventaSinStock: 0,
      idPerfil: sessionStorage.getItem("IdPerfil") || 0,
      EsCredito: 0,
      EsCreditoProforma: 0,
      perPageModalList: [
        { value: 5, text: "5" },
        { value: 10, text: "10" },
        { value: 20, text: "20" },
        { value: 50, text: "50" },
        { value: 100, text: "100" },
        { value: null, text: "All" }
      ],
      nextPage() {
        if (this.currentPage < this.totalPages) this.currentPage++;
      },
      prevPage() {
        if (this.currentPage > 1) this.currentPage--;
      },
      currentPage: 1
    };
  },

  methods: {
    // PRODUCTO CARRITO

    quitarProducto(index) {
      this.ventas[this.tab].productos.splice(index, 1);
      this.$toasted.show("Producto quitado del listado");
    },
    getSubtotal(index, item) {
      let subtotal =
        (item.tipo == "PRESENTACION" ? item.cantxPrecio : item.cantidad) *
        item.precio;

      Vue.set(
        this.ventas[this.tab].productos[index],
        "subtotal",
        parseFloat(subtotal).toFixed(2)
      );

      return parseFloat(subtotal).toFixed(2);
    },
    getTotal(index, item) {
      let total = parseFloat(item.subtotal);

      if (item.FLETE) {
        total += parseFloat(item.FLETE);
      }
      if (item.TieneIgv) {
        total += parseFloat(item.igv);
      }
      if (item.descuento) {
        total -= parseFloat(item.descuento);
      }

      total = this.$options.filters.redondear(total);
      Vue.set(this.ventas[this.tab].productos[index], "total", total);

      return total;
    },
    aumentarMontoPresentacion(item) {
      if (this.primerMonto) {
        this.monto = item.valor;
        this.primerMonto = false;
      } else {
        switch (item.tipo) {
          case "number":
            this.monto = parseFloat(this.monto.toString() + item.valor);
            break;
          case "limpiar":
            this.monto = 0;
            break;
          case "backspace":
            if (this.monto.toString().length > 1) {
              this.monto = parseFloat(
                this.monto.toString().slice(0, this.monto.toString().length - 1)
              );
            } else {
              this.monto = 0;
            }
            break;

          default:
            break;
        }
      }
    },
    listadoProductos() {
      let lst = [];
      let productosFiltrados = this.lstProductos.filter(
        listadoProducto =>
          !this.ventas[this.tab].productos.some(
            producto =>
              producto.IdProducto == listadoProducto.IdProducto &&
              producto.Empresa == listadoProducto.Empresa
          )
      );

      if (this.searchProducto) {
        const term = this.searchProducto.toLowerCase();

        lst = productosFiltrados.filter(p => {
          const prod = (p.Producto || "").toLowerCase();
          const cat = (p.ProductoCategoria || "").toLowerCase();
          const marca = (p.ProductoMarca || "").toLowerCase();
          const empresa = (p.Empresa || "").toLowerCase();

          // si aparece en cualquiera, entra
          return (
            prod.includes(term) ||
            cat.includes(term) ||
            marca.includes(term) ||
            empresa.includes(term)
          );
        });
      } else {
        lst = productosFiltrados;
      }

      if (this.IdProducto) {
        lst = lst.filter(v => v.CodigoBarra == this.IdProducto);
      }

      const perPage = parseInt(this.perPageModal) || lst.length;
      const start = (this.currentPage - 1) * perPage;
      const end = start + perPage;
      return lst.slice(start, end);
    },
    buscarProductosCodigoBarrasModal(e) {
      e.preventDefault();
      this.IdProducto = this.loadCode;
    },

    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    generarSerie(serie) {
      if (serie) {
        return serie;
      }
      return [];
    },
    showPresentacion(obj, index) {
      this.itemProducto = { ...obj };
      this.monto = this.itemProducto.cantidad;
      this.index = index;
      this.modalPresentacionesEditar = true;
    },
    guardarItemEditar(presentacion) {
      this.errorMontoPresentacion = "";
      if (this.monto == 0) {
        this.errorMontoPresentacion = "Ingrese una Cantidad";
        return false;
      }
      let prod = this.itemProducto;
      prod.tipo = "PRESENTACION";
      prod.estadoPxP = true;
      prod.cantxPrecio = this.monto;
      prod.cantidad = presentacion.cantidad;

      if (this.itemProducto.PreciosPorProducto.length > 0) {
        prod.total = this.monto * presentacion.precio;
        prod.precio = presentacion.precio;
        prod.cantidadPres = presentacion.cantidad;
        prod.estadoPxP = false;
      }
      Vue.set(this.ventas[this.tab].productos, this.index, prod);

      this.modalPresentacionesEditar = false;
    },
    guardarItemPorUnidadEditar() {
      this.errorMontoPresentacion = "";
      if (this.monto == 0) {
        this.errorMontoPresentacion = "Ingrese una Cantidad";
        return false;
      }
      let prod = this.itemProducto;
      prod.tipo = "UNIDAD";
      prod.precio = prod.PrecioContado;

      prod.cantxPrecio = this.monto;
      prod.cantidad = 1;
      if (this.itemProducto.PreciosPorProducto.length > 0) {
        prod.subtotal = this.monto * prod.PrecioContado;
        prod.total = this.monto * prod.PrecioContado;
      }

      Vue.set(this.ventas[this.tab].productos, this.index, prod);
      this.modalPresentacionesEditar = false;
    },
    guardarItem(presentacion) {
      let empresa = sessionStorage.getItem("empresa")
        ? sessionStorage.getItem("empresa")
        : "modasm";
      this.errorMontoPresentacion = "";

      if (this.monto == 0) {
        this.errorMontoPresentacion = "Ingrese una Cantidad";
        return false;
      }

      let prod = this.itemProducto;
      this.itemProducto.tipo = "PRESENTACION";
      prod.estadoPxP = true;
      prod.cantxPrecio = this.monto;
      prod.cantidad = presentacion.cantidad * this.monto;
      prod.descuento = 0;
      prod.disabled = true;
      prod.Presentacion = JSON.stringify(presentacion);
      if (!prod.Empresa || typeof prod.Empresa == "undefined") {
        let datoEmpresa = this.$store.state.empresas.filter(
          v => v.value == empresa
        );
        prod.Empresa = datoEmpresa[0].value;
        prod.NombreEmpresa = datoEmpresa[0].text.substring(0, 20) + "...";
      }

      if (this.itemProducto.PreciosPorProducto.length > 0) {
        prod.total = this.monto * presentacion.precio;
        prod.precio = presentacion.precio;
        prod.cantidadPres = presentacion.cantidad;
        prod.estadoPxP = false;
      }
      delete prod._rowVariant;
      prod = {
        ...prod,
        subtotal: 0,
        total: 0,
        precio: prod.PrecioContado,
        bloquearInput: false
      };
      this.ventas[this.tab].productos.unshift(prod);
      this.modalPresentaciones = false;
      this.modalNuevoProductos = this.abrirModal;
    },
    guardarItemPorUnidad() {
      let empresa = sessionStorage.getItem("empresa")
        ? sessionStorage.getItem("empresa")
        : "modasm";
      this.errorMontoPresentacion = "";
      if (this.monto == 0) {
        this.errorMontoPresentacion = "Ingrese una Cantidad";
        return false;
      }
      let prod = this.itemProducto;
      prod.tipo = "UNIDAD";
      prod.estadoPxP = true;
      prod.cantxPrecio = 0;
      prod.cantidad = 1;
      prod.descuento = 0;
      prod.disabled = false;
      prod.Presentacion = null;
      if (!prod.Empresa || typeof prod.Empresa == "undefined") {
        let datoEmpresa = this.$store.state.empresas.filter(
          v => v.value == empresa
        );
        prod.Empresa = datoEmpresa[0].value;
        prod.NombreEmpresa = datoEmpresa[0].text.substring(0, 20) + "...";
      }
      prod.subtotal = 0;
      prod.total = 0;
      prod.precio = prod.PrecioContado;
      prod.cantidadPres = 0;
      prod.bloquearInput = false;
      if (this.itemProducto.PreciosPorProducto.length > 0) {
        prod.estadoPxP = false;
      }
      delete prod._rowVariant;
      prod = {
        ...prod,
        subtotal: 0,
        total: 0,
        precio: prod.PrecioContado,
        cantidadPres: 0,
        bloquearInput: false
      };
      this.ventas[this.tab].productos.unshift(prod);
      this.modalPresentaciones = false;
      this.modalNuevoProductos = this.abrirModal;
    },

    guardarSerie() {
      this.objProd.Serie = this.Series;
      Vue.set(this.ventas[this.tab].productos, this.index, this.objProd);
      this.anadirSerieFlag = false;
    },
    async obtenerSeriePorProducto(id, Empresa) {
      let productoSerie = [];
      await this.axios
        .get(this.API + "productos/serie", {
          params: { IdProducto: id, Todos: false, empresa: Empresa }
        })
        .then(res => {
          if (res) {
            productoSerie = res.data;
          }
        });
      return productoSerie;
    },
    async anadirarProductosSeleccionado(item, abrirModal = false) {
      if (
        this.ventas[this.tab].productos.some(
          v => v.IdProducto == item.IdProducto
        )
      ) {
      } else {
        let PreciosPorProducto = JSON.parse(item.PreciosPorProducto);
        let productoSerie = await this.obtenerSeriePorProducto(
          item.IdProducto,
          item.Empresa
        );
        item.productoSerie = productoSerie;
        this.itemProducto = { ...item };
        this.itemProducto.tipo = "PRESENTACION";
        this.itemProducto.PreciosPorProducto = PreciosPorProducto;
        if (PreciosPorProducto.filter(v => v.cantidad > 0).length > 0) {
          this.monto = 1;
          this.modalPresentaciones = true;
          this.primerMonto = true;
        } else {
          if (!this.itemProducto.Empresa) {
            let empresa = this.$store.state.empresas.filter(
              v => v.value == this.empresa
            )[0];
            this.itemProducto.Empresa = empresa.value;
            this.itemProducto.NombreEmpresa =
              empresa.text.substring(0, 20) + "...";
          }
          this.itemProducto.tipo = "UNIDAD";
          this.itemProducto.estadoPxP = true;
          this.itemProducto.precio = this.itemProducto.PrecioContado;
          this.itemProducto.total = 0;
          this.itemProducto.cantxPrecio = 0;
          this.itemProducto.cantidad = 1;
          this.itemProducto.descuento = 0;
          delete this.itemProducto._rowVariant;
          this.ventas[this.tab].productos.unshift(this.itemProducto);
        }
      }
    },
    loadBarcode(e) {
      e.preventDefault();
      let idProducto = this.loadCode;
      let id_empresa = this.empresa;
      if (idProducto) {
        let params = { codigoBarra: idProducto, id_empresa: id_empresa };
        this.axios
          .get(this.API + "productos/id/" + idProducto, { params: params })
          .then(response => {
            if (response.data.IdProducto) {
              this.anadirarProductosSeleccionado(response.data);
            } else {
              this.loadCode = "";
              this.$toasted.show("No se encontró ningun producto");
            }
          });
      }
    },
    async abrirModaProductos() {
      let vm = this;
      vm.searchProducto = "";
      vm.modalNuevoProductos = !vm.modalNuevoProductos;
      vm.$store.state.consideraAppendEmpresa = false;
    },
    renderChild(data) {
      let styleStock = "";
      if (data.stock <= 0) {
        styleStock = "table-danger";
      } else {
        styleStock = "table-success";
      }
      if (data.EsPadre == 1) {
        styleStock = "";
      }

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
          <table id="table-render" style="width: 830px; text-align: center;padding:0; margin:0">
            <tbody>
              <tr style="padding:0; margin:0">
                <td style="width: 60px" class="${styleStock}"><b>${
        data.EsPadre == 1
          ? '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />'
          : data.stock
            ? Number.isInteger(parseFloat(data.stock))
              ? parseInt(data.stock)
              : parseFloat(data.stock).toFixed(2)
            : data.stock
      }</b></td>
                <td style="width: 200px"><b>${data.Producto} - ${
        data.CodigoBarra
      }</b></td>
                  <td style="width: 100px">${data.ProductoCategoria}</td>
                  <td style="width: 100px">${data.ProductoMarca}</td>
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
    },
    anadirVentana() {
      // PRODUCTO CARRITO
      // this.ventas.push(JSON.parse(JSON.stringify(this.ventaInit)));

      this.ventas.push({ ...this.ventaInit, productos: [] });
      setTimeout(async () => {
        this.tab = this.ventas.length - 1;
      }, 100);
      setTimeout(async () => {
        this.tab = this.ventas.length - 1;
        Promise.all([
          this.getVendedor(),
          this.loadClienteVarios(),
          this.escucharPedidos(),
          this.configStock()
        ]);
      }, 150);
    },
    anadirProforma(value) {
      this.ventas.push({
        ...this.ventaInit,
        cliente: value,
        productos: value.productos
      });
      setTimeout(async () => {
        this.tab = this.ventas.length - 1;
      }, 100);
      setTimeout(async () => {
        Promise.all([
          this.getVendedor(),
          this.setCliente({
            Anio: value.Anio,
            Cliente: value.Cliente,
            Direccion: value.Direccion,
            DniRuc: value.DniRuc,
            FechaReg: value.FechaReg,
            IdCliente: value.IdCliente,
            Numero: value.Numero,
            Total: value.Total
          }),
          this.escucharPedidos(),
          this.configStock()
        ]);
      }, 100);
    },
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
      window.parent.document.getElementById("NavegadorParent").style.display =
        "block";
    },

    asignarVuelto() {
      this.vuelto = parseFloat(this.pagocon - this.sumatoriaTotal).toFixed(2);
    },

    metodoPagoCambio(opc) {
      switch (opc) {
        case 1:
          this.ventas[this.tab].pago.visa.monto = 0;
          this.ventas[this.tab].pago.mastercard.monto = 0;
          this.ventas[this.tab].pago.transferencia.monto = 0;
          this.ventas[this.tab].pago.efectivo.monto = this.totalCart;
          break;
        case 2:
          this.ventas[this.tab].pago.mastercard.monto = 0;
          this.ventas[this.tab].pago.efectivo.monto = 0;
          this.ventas[this.tab].pago.transferencia.monto = 0;
          this.ventas[this.tab].pago.visa.monto = this.totalCart;
          break;
        case 3:
          this.ventas[this.tab].pago.efectivo.monto = 0;
          this.ventas[this.tab].pago.visa.monto = 0;
          this.ventas[this.tab].pago.transferencia.monto = 0;
          this.ventas[this.tab].pago.mastercard.monto = this.totalCart;
          break;
        case 4:
          this.ventas[this.tab].pago.efectivo.monto = 0;
          this.ventas[this.tab].pago.visa.monto = 0;
          this.ventas[this.tab].pago.mastercard.monto = 0;
          this.ventas[this.tab].pago.transferencia.monto = this.totalCart;
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
      this.ventas[this.tab].IdPreOrden.push(orden);

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
          this.ventas[this.tab].almacen = res.data;
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
      // this.$nextTick(() => {
      this.$refs[ref][0].handleDoubleClick();
      // });
    },
    initForm() {
      this.ventaInit = {
        EsCredito: 0,
        Serie: "",
        Numero: "",
        cliente: {},
        comisionista: {},
        tipoVenta: {
          IdTipoDoc: 3,
          TipoDoc: "NOTA DE VENTA",
          LimitItems: 0,
          CodSunat: "12"
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
        FechaCredito: this.$moment().format("YYYY-MM-DD"),
        FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
        pago: {
          efectivo: { descripcion: "", IdMetodoPago: 1, monto: 0 },
          visa: { descripcion: "", IdMetodoPago: 2, monto: 0 },
          mastercard: { descripcion: "", IdMetodoPago: 3, monto: 0 },
          transferencia: { descripcion: "", IdMetodoPago: 4, monto: 0 },
          delivery: { descripcion: "", IdMetodoPago: 4, monto: 0 }
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
        anioNumeroProforma: "",
        productos: []
      };

      this.isNota = false;
      this.bloquearInput = false;
      this.adelantosPreOrden = [];
      this.adelanto = 0;
      this.infoDelivery = {};
      this.descuentoGeneral = 0;
      this.puntosAplicados = 0;

      setTimeout(() => {
        this.ventas.push({ ...this.ventaInit });
      }, 200);
    },
    setTipoVenta(obj) {
      this.isNota = false;
      (this.bloquearInput = false),
        (this.ventas[this.tab].NroComprobanteModifica = "");
      this.ventas[this.tab].CodSunatModifica = "";
      this.ventas[this.tab].NotaIdMotivo = "";
      this.ventas[this.tab].NotaDescMotivo = "";
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
      this.ventas[this.tab].tipoVenta = obj;
      this.getNumeroVenta();
    },
    setDocRef(obj) {
      this.ventas[this.tabs].CodSunatModifica = obj.CodSunat;
      this.ventas[this.tabs].NroComprobanteModifica = obj.NroComprobante;

      if (
        this.ventas[this.tabs].tipoVenta.CodSunat == "07" ||
        this.ventas[this.tabs].tipoVenta.CodSunat == "08"
      ) {
        this.ventas[this.tabs].Serie = this.ventas[this.tabs].Serie.replace(
          /^./,
          obj.NroComprobante[0]
        );
      }
      this.axios
        .get(this.API + "ventas/solonumero", {
          params: {
            serie: this.ventas[this.tab].Serie,
            idTipoDoc: this.ventas[this.tab].tipoVenta.IdTipoDoc,
            id_empresa: this.empresa
          }
        })
        .then(response => {
          this.ventas[this.tab].Numero = response.data.numero;
        })
        .catch(error => {
          console.error(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
      //Cargar producto del documento afectado nota credito
      this.axios
        .get(this.API + "venta/detalle/comprobante", {
          params: {
            NroComprobante: obj.NroComprobante,
            idDocVenta: obj.IdDocVenta,
            IdDocVentaPuntoVenta: this.ventas[this.tab].puntoVenta
              .IdDocVentaPuntoVenta,
            id_empresa: this.empresa
          }
        })
        .then(response => {
          this.axios
            .get(this.API + "cliente", {
              params: {
                idCliente: response.data[0].IdCliente,
                id_empresa: this.empresa
              }
            })
            .then(response => {
              if (response.data) {
                let ref = `inputCliente${this.tab}`;
                const comp = this.$refs[ref][0];

                if (comp) {
                  comp.setValue(response.data.NombreUsuario);
                  comp.$refs.input.disabled = true;
                }
                this.ventas[this.tab].cliente = response.data;
                this.ventas[this.tab].CampoDireccion = this.ventas[
                  this.tab
                ].cliente.Direccion;
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.error(error);
            });
          this.limpiarCart();
          let empresa = this.$store.state.empresas.filter(
            v => v.value == this.empresa
          )[0];

          if (
            this.ventas[this.tab].tipoVenta.CodSunat == "07" ||
            this.ventas[this.tab].tipoVenta.CodSunat == "08"
          ) {
            response.data.map(item => {
              item.Empresa = empresa.value;
              item.NombreEmpresa = empresa.text;
              // item.IdEm
            });
          }
          response.data.map(item => {
            item.estadoPxP = false;
            let preciosProd = [];
            item.precioCont = item.PrecioContado;
            preciosProd = JSON.parse(item.PreciosPorProducto || "[]");
            let fPP = preciosProd.filter(p => p.cantidad === 0);

            if (!item.PreciosPorProducto || fPP.length === 4) {
              item.estadoPxP = true;
            }
            this.add([item, () => {}, false]);
          });
          this.ventas[this.tab].NotaIdMotivo = null;
          this.bloquearInput = true;
          this.obtenerStok({
            IdAlmacen: this.ventas[this.tab].almacen.IdAlmacen
          });
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
      this.ventas[this.tab].almacen = obj;
      let ref = `inputAlmacen${this.tab}`;
      this.obtenerStok({ IdAlmacen: this.ventas[this.tab].almacen.IdAlmacen });
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
      this.ventas[this.tab].puntoVenta = obj;

      this.getNumeroVenta();
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
          let ref = "";

          ref = `inputAlmacen${this.tab}`;
          this.$refs[ref][0].$refs.input.disabled = true;

          ref = `inputPuntoVenta${this.tab}`;
          this.$refs[ref][0].$refs.input.disabled = true;
          // -----------------------------
          ref = `inputVendedor${this.tab}`;
          const comp = this.$refs[ref][0];

          if (comp) {
            comp.setValue(response.data.NombreUsuario);
            comp.$refs.input.disabled = true;
          }

          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
          // bloquear ambos inputs

          this.getTipoDoc(response.data.IdTipoDoc);
        })
        .catch(error => {
          console.error(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    setVendedor(obj) {
      if (obj.IdTipoDoc) this.getTipoDoc(obj.IdTipoDoc);

      this.ventas[this.tab].vendedor = obj;
    },
    setVendedorManual(obj) {
      this.ventas[this.tab].vendedor = obj;
      this.getVendedor(obj.Usuario);
    },
    setComisionista(obj) {
      if (typeof obj === "object") {
        let inputComisionista = `inputComisionista${this.tabs}`;
        this.$refs[inputComisionista].setValue(obj.Cliente);
        this.ventas[this.tab].comisionista = obj;
      } else {
        this.ventas[this.tab].comisionista = {};
      }
    },
    setCliente(obj, dni = false) {
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
        if (obj.IdProforma > 0) {
          this.ventas[this.tab].idProforma = obj.IdProforma;
          this.ventas[this.tab].anioNumeroProforma =
            obj.Anio + "-" + obj.Numero;
          this.getVendedor(obj.UsuarioReg);
          delete obj.productos;
          this.axios
            .post(
              this.API + "verificar/crear/cliente?id_empresa=" + this.empresa,
              { data: obj }
            )
            .then(response => {
              let ref = `inputCliente${this.tab}`;
              const comp = this.$refs[ref][0];

              if (comp) {
                comp.setValue(response.data.NombreUsuario);
                comp.$refs.input.disabled = true;
              }

              this.ventas[this.tab].cliente = response.data;
              this.ventas[this.tab].CampoDireccion = this.ventas[
                this.tab
              ].cliente.Direccion;
            })
            .catch(error => {
              this.$toasted.show("Ha ocurrido un error, en la emision a sunat");
            });
        } else {
          let ref = `inputCliente${this.tab}`;
          const comp = this.$refs[ref][0];

          if (comp) {
            comp.setValue(obj.Cliente);
          }

          this.ventas[this.tab].cliente = obj;
          this.ventas[this.tab].CampoDireccion = this.ventas[
            this.tab
          ].cliente.Direccion;
          if (this.ventas[this.tab].cliente.DniRuc == "00000000") {
            this.ventas[this.tab].EsOrganizacion = 0;
            this.ventas[this.tab].NombreOrganizacion = "";
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
              let ref = `inputCliente${this.tab}`;
              const comp = this.$refs[ref][0];

              if (comp) {
                comp.setValue(response.data.NombreUsuario);
                comp.$refs.input.disabled = true;
              }
              this.ventas[this.tab].cliente = response.data;
              this.ventas[this.tab].CampoDireccion = this.ventas[
                this.tab
              ].cliente.Direccion;
              if (this.ventas[this.tab].cliente.DniRuc == "00000000") {
                this.ventas[this.tab].EsOrganizacion = 0;
                this.ventas[this.tab].NombreOrganizacion = "";
              }
            } else {
              this.$toasted.show("No se encontro al cliente");
            }
          })
          .catch(error => {
            console.error(error);
          });
      }

      //this.actualizarPrecioConvenio(obj)
      // Actualizar DisabledTotal
    },
    limpiarProforma() {
      this.ventas[this.tab].idProforma = 0;
      this.ventas[this.tab].anioNumeroProforma = "";
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
      this.ventas[this.tab].pago.visa.monto = 0;
      this.ventas[this.tab].pago.mastercard.monto = 0;
      this.ventas[this.tab].pago.transferencia.monto = 0;
      this.actualizarDescuento();
      this.obtenerStok({
        IdAlmacen: this.ventas[this.tab].almacen.IdAlmacen
      });
    },
    actualizarDescuento() {
      this.ventas[this.tab].pago.visa.monto = 0;
      this.ventas[this.tab].pago.mastercard.monto = 0;
      this.ventas[this.tab].pago.transferencia.monto = 0;
      if (this.puntosAplicados > 0) {
        this.aplicarPuntos(this.puntosAplicados);
      }

      if (this.descuentoGeneral > 0) {
        this.aplicarDescuentoGeneral(this.descuentoGeneral);
      }
    },
    aplicarPuntos(val) {
      if (parseFloat(val) > this.ventas[this.tab].cliente.Puntos) {
        this.puntosAplicados = this.ventas[this.tab].cliente.Puntos;
      }

      this.axios
        .get(this.API + "ventas/descuento/" + this.puntosAplicados)
        .then(response => {
          this.descuentoPuntos = response.data;
          this.aplicarDescuento(response.data.descuento);
        })
        .catch(error => {
          console.error(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    aplicarDescuentoGeneral(val) {
      this.puntosAplicados = 0;
      this.aplicarDescuento(val);
    },
    getNumeroVenta() {
      this.axios
        .get(this.API + "ventas/numero", {
          params: {
            idPuntoVenta: this.ventas[this.tab].puntoVenta.IdDocVentaPuntoVenta,
            idTipoDoc: this.ventas[this.tab].tipoVenta.IdTipoDoc,
            id_empresa: this.empresa
          }
        })
        .then(response => {
          this.ventas[this.tab].Serie = response.data.serie;
          this.ventas[this.tab].Numero = response.data.numero;
        })
        .catch(error => {
          console.error(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    updateTime() {
      this.ventas[this.tab].FechaDoc = this.$moment().format(
        "YYYY-MM-DDTH:mm:ss"
      );
    },
    //limpiarDespuesVenta () {
    //    this.inProgress = false
    //    this.tieneIgv = false
    //    this.initForm()
    //    this.limpiarCart()
    //    this.getVendedor()
    //    this.$refs.inputCliente.setValue('')
    // this.getNumeroVenta()
    // this.$refs.inputTipoVenta.setValue('TICKET')
    // this.$refs.inputAlmacen.setValue('VENTA')
    // this.$refs.inputPuntoVenta.setValue('CAJA1')
    // this.$refs.inputVendedor.setValue('')
    //},
    guardarProforma() {
      this.inProgress = true;

      if (!this.ventas[this.tab].productos.length) {
        this.$toasted.show("No hay productos para crear la proforma");
        this.inProgress = false;
        return;
      }

      if (this.diasValidosProf == 0 || this.diasValidosProf == "") {
        this.$toasted.show("Debe ingresar los días de validez de la proforma");
        this.inProgress = false;
        return;
      }

      if (!this.ventas[this.tab].cliente.IdCliente) {
        this.$toasted.show("Debe seleccionar un cliente");
        this.inProgress = false;
        return;
      }

      if (!this.ventas[this.tab].vendedor.Usuario) {
        this.$toasted.show("Debe seleccionar un vendedor");
        this.inProgress = false;
        return;
      }

      this.ventas[this.tab].diasValidosProf = this.diasValidosProf;
      this.axios
        .post(
          this.API + "proformas?id_empresa=" + this.empresa,
          this.ventas[this.tab]
        )
        .then(async response => {
          this.$toasted.info("Proforma Guardada con exito");
          //this.limpiarDespuesVenta()
          this.loadClienteVarios();
          this.diasValidosProf = 1;
          this.inProgress = false;
          this.tieneIgv = false;
          //this.ultimaVenta = response.data.insertId
          this.getVendedor();
          window.open(
            "/nuevo/index.html#/reportes/proforma/" +
              response.data.insertId +
              "?id_empresa=" +
              this.empresa,
            "_blank"
          );
        })
        .catch(error => {
          console.error(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });

      if (this.ventas.length > 1) {
        this.eliminarVentana();
      } else {
        setTimeout(() => {
          window.location.reload();
        }, 500);
      }
    },
    validarProforma() {
      let profoma = { ...this.$store.state.proforma };
      let lstProductos = [...this.$store.state.lstProductosProforma];

      if (Object.keys(profoma).length > 0) {
        const empresas = [...new Set(lstProductos.map(d => d.Empresa))];

        for (const Empresa of empresas) {
          let lstProductosLength = lstProductos.filter(
            v => v.Empresa == Empresa
          ).length;
          let productosLength = this.ventas[this.tab].productos.filter(
            v => v.Empresa == Empresa
          ).length;

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
      let totalPagos = 0;
      totalPagos += isNaN(parseFloat(this.ventas[this.tab].pago.efectivo.monto))
        ? 0
        : parseFloat(this.ventas[this.tab].pago.efectivo.monto);
      totalPagos += isNaN(parseFloat(this.ventas[this.tab].pago.visa.monto))
        ? 0
        : parseFloat(this.ventas[this.tab].pago.visa.monto);
      totalPagos += isNaN(
        parseFloat(this.ventas[this.tab].pago.mastercard.monto)
      )
        ? 0
        : parseFloat(this.ventas[this.tab].pago.mastercard.monto);
      totalPagos += isNaN(
        parseFloat(this.ventas[this.tab].pago.transferencia.monto)
      )
        ? 0
        : parseFloat(this.ventas[this.tab].pago.transferencia.monto);
      if (
        (isNaN(parseFloat(this.ventas[this.tab].pago.efectivo.monto))
          ? 0
          : parseFloat(this.ventas[this.tab].pago.efectivo.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de pago efectivo no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.ventas[this.tab].pago.visa.monto))
          ? 0
          : parseFloat(this.ventas[this.tab].pago.visa.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de pago Tarjeta no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.ventas[this.tab].pago.mastercard.monto))
          ? 0
          : parseFloat(this.ventas[this.tab].pago.mastercard.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.ventas[this.tab].pago.transferencia.monto))
          ? 0
          : parseFloat(this.ventas[this.tab].pago.transferencia.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de Transferencia no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (!this.ventas[this.tab].EsCredito) {
        if (
          parseFloat(this.totalCart).toFixed(2) !=
          parseFloat(totalPagos).toFixed(2)
        ) {
          this.$toasted.show("Los pagos no coinciden con la venta");
          this.inProgress = false;
          return;
        }
      }

      if (!this.ventas[this.tab].cliente.DniRuc) {
        this.$toasted.show("Debe seleccionar un cliente.");
        this.inProgress = false;
        return;
      }

      var esNumerico = Number.isInteger(
        parseInt(this.ventas[this.tab].cliente.DniRuc)
      );
      if (!esNumerico) {
        this.$toasted.show("El RUC debe ser numérico, editar cliente.");
        this.inProgress = false;
        return;
      }

      var esEmpresa =
        this.ventas[this.tab].cliente.DniRuc.length > 9 ? true : false;
      if (!esEmpresa && this.ventas[this.tab].tipoVenta.CodSunat == "01") {
        this.$toasted.show(
          "Una factura debe ser emitida para una empresa, no un usuario común."
        );
        this.inProgress = false;
        return;
      }

      //Nota de Creadito
      if (
        this.ventas[this.tab].tipoVenta.CodSunat == "07" ||
        this.ventas[this.tab].tipoVenta.CodSunat == "08"
      ) {
        if (
          !this.ventas[this.tab].CodSunatModifica ||
          !this.ventas[this.tab].NroComprobanteModifica
        ) {
          this.$toasted.show("Debe seleccionar un Documento de Referencia...");
          this.inProgress = false;
          return;
        }
        if (!this.ventas[this.tab].NotaIdMotivo) {
          this.$toasted.show("Debe seleccionar un Motivo");
          this.inProgress = false;
          return;
        }
        this.ventas[this.tab].NotaDescMotivo = this.itemsMotivoNota.filter(
          item => item.value == this.ventas[this.tab].NotaIdMotivo
        )[0].text;
      }

      if (!this.ventas[this.tab].productos.length) {
        this.$toasted.show("No hay productos para vender");
        this.inProgress = false;
        return;
      }

      // NO PERMITE CANTIDAD Y PRECIOS 0
      let sinStock = 0;
      let totalCero = 0;
      this.ventas[this.tab].productos.forEach(pro => {
        if (
          pro.CodigoBarra !== "COSTODELIVERY" &&
          pro.CodigoBarra !== "MANODEOBRA" &&
          (pro.stock <= 0 ||
            parseFloat(pro.cantidad) > pro.stock ||
            pro.stock == undefined)
        ) {
          sinStock += 1;
        }

        if (parseFloat(pro.total) <= 0) {
          totalCero += 1;
        }
      });

      if (
        sinStock >= 1 &&
        this.ventas[this.tab].tipoVenta.CodSunat != "07" &&
        this.ventaSinStock != 1
      ) {
        this.$toasted.error("No se puede vender un producto sin stock");
        this.inProgress = false;
        return;
      }

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

      let count = 0;
      for (const k in this.ventas[this.tab].pago) {
        if (this.ventas[this.tab].pago[k].monto > 0) {
          count = count + 1;
        }
      }

      if (count > 1) {
        this.$toasted.show("Solo se permite un tipo de pago.");
        this.inProgress = false;
        return;
      }

      if (!this.validarProforma()) {
        this.inProgress = false;
        this.$toasted.show(
          "La Proforma no cumple con los requisitos necesarios. Verifique"
        );
        return;
      }
      this.inProgress = false;

      this.ventas[this.tab].puntosAplicados = this.puntosAplicados || 0;
      this.ventas[this.tab].totalCart = this.totalCart || 0;
      this.ventas[this.tab].PagoCon = parseFloat(
        this.$refs.carrito.pagocon
      ).toFixed(2);
      this.ventas[this.tab].NombreOrganizacion = (
        "" + this.ventas[this.tab].NombreOrganizacion
      )
        .trim()
        .toUpperCase();
      this.ventas[this.tab].adelantosPreOrden = this.adelantosPreOrden;
      this.ventas[this.tab].IdPedido = this.$route.query.idPedido || null;
      this.ventas[this.tab].nombreLlevar = this.ventas[
        this.tab
      ].nombreLlevar.toUpperCase();
      this.ventas[this.tab].DireccionEntrega = this.ventas[this.tab].EsDelivery
        ? this.infoDelivery.Direccion || ""
        : "";
      // ------------------------- aqui paga
      this.loading = true;
      await this.$store.state.empresas.forEach(async element => {
        let data = { ...this.ventas[this.tab] };
        let serie = await this.obtenerSerie(element.value);
        let almacen = await this.obtenerAlmacen(element.value);
        let empresa = await this.obtenerDatosEmpresa(element.value);
        let impresora = await this.obtenerDatosImpresora(element.value);
        let total = data.productos
          .filter(v => v.Empresa == element.value)
          .reduce((total, prod) => {
            return total + parseFloat(prod.total);
          }, 0);
        let tipoPago = "";
        for (const k in this.ventas[this.tab].pago) {
          if (this.ventas[this.tab].pago[k].monto > 0) {
            tipoPago = k;
          }
        }
        let dataImprimir = {
          NombreOrganizacion: this.ventas[this.tab].NombreOrganizacion,
          vendedor: this.ventas[this.tab].vendedor.NombreUsuario,
          tipoPago: tipoPago,
          numero: serie.numero,
          serie: serie.serie,
          CodSunat: this.ventas[this.tab].tipoVenta.CodSunat,
          IdTipoDoc: this.ventas[this.tab].tipoVenta.IdTipoDoc,
          LimitItems: this.ventas[this.tab].tipoVenta.LimitItems,
          TipoDoc: this.ventas[this.tab].tipoVenta.TipoDoc,
          empresa: element.value,
          nombre_comercial: empresa.razoncial,
          documento: empresa.ruc,
          direccion: empresa.direccion,
          cliente: data.cliente.Cliente,
          documento_client: data.cliente.DniRuc,
          direccion_cliente: data.cliente.Direccion,
          total: total,
          impresora: impresora,
          productos: data.productos.filter(v => v.Empresa == element.value)
        };
        data.almacen = almacen;
        data.Numero = serie.numero;
        data.Serie = serie.serie;
        this.inProgress = true;
        if (data.productos.some(v => v.Empresa == element.value)) {
          let IdProformaDets = [];
          if (element.value == this.empresa) {
            IdProformaDets = this.ventas[this.tab].productos.map(v => {
              return v.IdProformaDet;
            });
          }
          let productos = data.productos.filter(
            v => v.Empresa == element.value
          );
          let totalCarr = productos.reduce((total, pr) => {
            return parseFloat(total) + parseFloat(pr.total);
          }, 0);

          data.productos = productos;
          data.totalCart = totalCarr;
          data.IdProformaDets = IdProformaDets;
          if (data.pago.efectivo.monto != 0) {
            data.pago.efectivo.monto = totalCarr;
          } else if (data.pago.mastercard.monto != 0) {
            data.pago.mastercard.monto = totalCarr;
          } else if (data.pago.transferencia.monto != 0) {
            data.pago.transferencia.monto = totalCarr;
          } else if (data.pago.visa.monto != 0) {
            data.pago.visa.monto = totalCarr;
          }

          let comandaJson = JSON.stringify(dataImprimir);

          await this.axios
            .post(this.API + "ventas?id_empresa=" + element.value, data)
            .then(async response => {
              /** --------RECUERDA ESTA PARTE  */
              this.emitirauto(response.data.insertId, element.value);
              // -------------------------
              await fetch("http://localhost:5000/imprimir/getcmdped.php", {
                method: "POST",
                headers: {
                  "Content-Type": "application/json"
                },
                mode: "cors",
                cache: "default",
                body: comandaJson
              }).catch(error => console.error("Error:", error));
            })
            .catch(error => {
              console.error(error);
              this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
              this.inProgress = false;
            });

          // await this.axios
          //   .post("http://localhost:5000/imprimir/getcmdped.php", comandaJson, {
          //     baseURL: ""
          //   })
          //   .catch(err => {
          //     console.error(err);
          //   });
          this.$toasted.success("Venta realizada con exito");
          if (this.ventas.length > 1) {
            this.eliminarVentana();
          } else {
            setTimeout(() => {
              window.location.reload();
            }, 500);
          }
        }
      });
      this.inProgress = true;
      // this.$toasted.success("Venta realizada con exito");

      setTimeout(() => {
        this.inProgress = false;
        window.location.reload();
      }, 1000 * 2 * this.$store.state.empresas.length);
    },
    eliminarVentana() {
      this.ventas.splice(this.tab, 1);

      this.$nextTick(() => {
        if (this.tab != 0) {
          this.tab = this.tab - 1;
        } else {
          this.tab = 0;
        }
      });
      let header = document.getElementById("header");
      header.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
      this.$toasted.info("Opción de venta cerrada.");
    },
    emitirauto(idDocVenta, id_empresa) {
      this.axios
        .post(this.API + "emitirauto?id_empresa=" + this.empresa, {
          IdDocVenta: idDocVenta
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
      this.ventas[this.tab].cliente = {};
    },
    eliminarComisionista() {
      this.$refs.inputComisionista.setValue("");
      this.ventas[this.tab].comisionista = {};
    },
    onPagarDelivery(obj) {
      this.$route.query.idPedido = obj.IdPedido;
      if (this.$route.query.idPedido) {
        this.infoDelivery = obj;
        this.ventas[this.tab].EsDelivery = true;
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
                this.ventas[this.tab].nombreLlevar =
                  obj.Cliente || "SIN NOMBRE";
                this.ventas[this.tab].DireccionEntrega = obj.Direccion;
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
          this.ventas[this.tab].nombreLlevar = obj.Cliente || "SIN NOMBRE";
          this.ventas[this.tab].DireccionEntrega = obj.Direccion;
        }
      }
    },
    anularPagoDelivery() {
      this.limpiarCart();
      this.ventas[this.tab].EsDelivery = false;
      this.infoDelivery = {};
      this.$refs.carrito.pagocon = "";
      this.loadClienteVarios();
      this.ventas[this.tab].nombreLlevar = "";
      this.ventas[this.tab].selectOpcionVenta = 0;
      this.ventas[this.tab].nombreLlevar = "";
      this.ventas[this.tab].DireccionEntrega = "";
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
      let res = await this.axios(
        `${this.API}/ventas/numero?idPuntoVenta=${
          this.ventas[this.tab].puntoVenta.IdDocVentaPuntoVenta
        }&idTipoDoc=${
          this.ventas[this.tab].tipoVenta.IdTipoDoc
        }&id_empresa=${empresa}`
      );
      return res.data;
    },
    async obtenerAlmacen(empresa) {
      let res = await this.axios(
        `${this.API}almacenes/id/1?&id_empresa=${empresa}`
      );
      return res.data;
    },
    async obtenerDatosEmpresa(empresa) {
      let res = await this.axios(
        `${this.API}obtener_datos_empresa?id_empresa=${empresa}`
      );
      return res.data;
    },
    async obtenerDatosImpresora(empresa) {
      let res = await this.axios(
        `${this.API}obtener_datos_impresora?id_empresa=${empresa}`
      );
      return res.data;
    },
    lstTotalPorEmpresa() {
      let totalEmpresa = [];

      this.$store.state.empresas.forEach(empresa => {
        let productos = this.ventas[this.tab]
          ? this.ventas[this.tab].productos
          : [];

        let productosEmpresa = productos.filter(
          p => p.Empresa == empresa.value
        );

        if (productosEmpresa.length > 0) {
          let total = productosEmpresa.reduce((sum, prod) => {
            return parseFloat(sum) + parseFloat(prod.total || 0);
          }, 0);

          totalEmpresa.push({
            nombre: empresa.text,
            total: parseFloat(total).toFixed(2)
          });
        } else {
          totalEmpresa.push({
            nombre: empresa.text,
            total: 0
          });
        }
      });

      return totalEmpresa;
    }
  },
  watch: {
    totalCart(newVal) {
      this.ventas[this.tab].pago.efectivo.monto = parseFloat(
        newVal -
          (parseFloat(this.ventas[this.tab].pago.visa.monto) +
            parseFloat(this.ventas[this.tab].pago.mastercard.monto) +
            parseFloat(this.ventas[this.tab].pago.transferencia.monto))
      ).toFixed(2);
    },

    recalcularTotalPorEmpresaFlag() {
      this.$nextTick(() => {
        setTimeout(() => {
          // this.recalcularTotalesPorEmpresa();
          // this.metodoPagoCambio(1);
        }, 100);
      });
    }
  },
  computed: {
    totalPages() {
      let productosFiltrados = this.lstProductos.filter(
        listadoProducto =>
          !this.ventas[this.tab].productos.some(
            producto =>
              producto.IdProducto == listadoProducto.IdProducto &&
              producto.Empresa == listadoProducto.Empresa
          )
      );

      if (this.searchProducto) {
        productosFiltrados = productosFiltrados.filter(
          p =>
            p.Producto.toLowerCase().includes(
              this.searchProducto.toLowerCase()
            ) || !!p.Selected
        );
      }

      if (this.IdProducto) {
        productosFiltrados = productosFiltrados.filter(
          v => v.CodigoBarra == this.IdProducto
        );
      }

      const perPage = parseInt(this.perPageModal) || productosFiltrados.length;
      return Math.ceil(productosFiltrados.length / perPage);
    },
    isLastPage() {
      return this.currentPage >= this.totalPages;
    },
    APIProductos() {
      return this.API + "productos/puntoventa/stock/" + 1;
    },
    ...mapState({
      productos: state => state.cart.productos,
      recalcularTotalPorEmpresaFlag: state =>
        state.recalcularTotalPorEmpresaFlag,

      totalCart: state => state.cart.totalCart
    }),

    manoDeObraBruta() {
      let pManoObra = this.ventas[this.tab].productos.find(
        p => p.CodigoBarra === "MANODEOBRA"
      );
      let precio = pManoObra ? pManoObra.precioBruta : 0;

      return precio;
    },
    manoDeObraNeta() {
      let pManoObra = this.ventas[this.tab].productos.find(
        p => p.CodigoBarra === "MANODEOBRA"
      );
      let precio = pManoObra ? pManoObra.precio : 0;

      return precio;
    },
    sumatoriaTotal() {
      let sumatoria = this.ventas[this.tab].productos.reduce(
        (sum, producto) => {
          return (
            sum +
            parseFloat(
              producto.subtotal - (producto.descuento ? producto.descuento : 0)
            )
          );
        },
        0
      );
      // this.actualizarTotal(sumatoria);

      return this.$options.filters.redondear(sumatoria);
    },
    subTotalG() {
      let sumatoria = this.ventas[this.tab].productos.reduce(
        (sum, producto) => {
          return sum + parseFloat(producto.subtotal);
        },
        0
      );
      // this.actualizarTotal(sumatoria);

      return this.$options.filters.redondear(sumatoria);
    },
    descuentoG() {
      let sumatoria = this.ventas[this.tab].productos.reduce(
        (sum, producto) => {
          return sum + parseFloat(producto.descuento ? producto.descuento : 0);
        },
        0
      );
      // this.actualizarTotal(sumatoria);

      return this.$options.filters.redondear(sumatoria);
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
    ProductoForm
  }
};
</script>
<style scoped>
.text-red {
  color: red !important;
  margin: 50px 0px 50px 0 !important;
}
.modalSelectProduct.modal-xl .modal-dialog {
  max-width: 60%;
}
#modalImagen .modal-dialog {
  max-width: 75% !important;
}
.modalSelectProduct.modal-xl .modal-dialog {
  max-width: 60%;
}
.imagen {
  width: 40px !important;
  height: 40px !important;
}
.imagen:hover {
  transform: scale(2.5);
}
.cardPresentacion {
  cursor: pointer;
}
.cardPresentacion:hover {
  cursor: pointer;
  background: #bdbdbd;
}
.contentCard {
  cursor: pointer;
}
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
  background: #eff9ff;
}
.modasgio {
  background: #c1e7ff;
}
.modasjes {
  background: #cdcdcd;
}
.modasjun {
  background: #c2ffc0;
}
.modaspau {
  background: #ffdaf0;
}
</style>
