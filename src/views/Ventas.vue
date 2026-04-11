<template>
    <b-container fluid class="ancho px-0 py-0"  id="conten">
      <b-modal id="modalImagen" size="lg" ref="modalImagen" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration: underline;">{{productoNombre}}</h3>
        </div>
        <b-row style="display:flex; justify-content:center; align-items:center">
          <b-img :src="productoUrl" class="imagenGrande"></b-img>
        </b-row>
      </b-modal>
      <b-row id="header">
        <b-col cols="12"  class="py-0 mt-0">
          <b-card no-body class="mt-0">
          <b-tabs v-model="ventaActiva" content-class="mt-0" pills card class="mt-0" >
            <b-tab v-for="(venta,index) in ventas" :key="index" :title="`Venta ${index+1}`" title-link-class="titleTabs" class="mt-0 pt-0">
              <b-container fluid>
                <b-row>
                  <b-col lg="6" xl="6" cols="12" >
                    <b-row>
                  <b-col lg="4" xl="4"  cols="12" class="mostrarMovil">
                   Tipo de Venta: {{ventas[ventaActiva].Serie}}  - {{ventas[ventaActiva].Numero}}
                  </b-col>
                  <b-col lg="4" xl="4"  cols="12" class="ocultarMovil">
                    <b-input-group prepend="Doc. Venta" class="mt-2">
                      <autocomplete
                      required="required"
                      :id="`inputTipoVenta${index}`"
                      :url="API + 'ventas/tipos'"
                      anchor="TipoDoc"
                      initValue="BOLETA/IGV ELEC."
                      class="font-weight-bold"
                      label="writer"
                      placeholder=""
                      :on-select="setTipoVenta"
                      :onFocus="focusAutocomplete.bind(null, `inputTipoVenta${index}`)"
                      :classes="{input: 'form-control'}"
                      >
                    </autocomplete>
                    </b-input-group>
                  </b-col>
                  <b-col lg="4" xl="4"  cols="12" class="mostrarMovil">
                    <b-input-group class="mt-2">
                      <autocomplete
                      required="required"
                      :id="`inputTipoVenta${index}`"
                      :url="API + 'ventas/tipos'"
                      anchor="TipoDoc"
                      initValue="BOLETA ELEC."
                      class="font-weight-bold"
                      label="writer"
                      placeholder=""
                      :on-select="setTipoVenta"
                      :onFocus="focusAutocomplete.bind(null, `inputTipoVenta${index}`)"
                      :classes="{input: 'form-control'}"
                      >
                    </autocomplete>
                    </b-input-group>
                  </b-col>
                  <b-col md="4" class="docInfo" v-if="isNota[index]">
                <b-input-group prepend="Doc. Referenc" class="mt-2" >
                   <autocomplete
                       required="required"
                       :id="`inputDocRef${index}`"
                       :ref="`inputDocRef${index}`"
                       :url="API + 'ventas/lista/'+ventas[index].puntoVenta.IdDocVentaPuntoVenta"
                       anchor="NroComprobante"
                       initValue=""
                       label="writer"
                       placeholder=""
                       :on-select="setDocRef"
                       :onFocus="focusAutocomplete.bind(null, `inputDocRef${index}`)"
                       :classes="{input: 'form-control'}">
                   </autocomplete>
                </b-input-group>
                </b-col>
                <b-col md="3" class="docInfo" v-if="isNota[index]">
                            <b-input-group class="mt-2"  style="margin-bottom:-20px">
                                <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                            </b-input-group>
                </b-col>
                  <b-col md="4" class="ocultarMovil">
                    <b-input-group prepend="Alm." class="mt-2">
                      <input type="text" v-model="venta.almacen.Almacen" class="form-control" disabled>
                    </b-input-group>
                  </b-col>
                  <b-col md="4" class="ocultarMovil">
                    <input type="text" v-model="venta.puntoVenta.PuntoVenta" class="form-control" disabled>
                  </b-col>
                  <b-col cols="12" class="mt-1">
                        
                        <b-form-group >
                          <b-input-group class="py-0">
                          <b-input-group-prepend>
                            <!-- @dniRuc="setNewDniRuc" -->
                            <clientes-form txtButton="Editar" v-if="venta.cliente.IdCliente && venta.cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="venta.cliente"></clientes-form>
                            <AutoComplete  
                              :value="ventas[index].cliente.Cliente"
                              @guardarCliente="setCliente"
                              @dniRuc="dniRuc"
                            />
                          </b-input-group-prepend>
                          <b-input-group-append>
                            
                            <clientes-form :newDniRuc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                          </b-input-group-append>
                        </b-input-group>
  
                          <b-form-select v-if="ventas[ventaActiva].cliente.IdCliente" v-model="venta.CampoDireccion" class="mb-3">
                            <option :value="ventas[ventaActiva].cliente.Direccion">{{ venta.cliente.Direccion }}</option>
                            <option :value="ventas[ventaActiva].cliente.Direccion2">{{ venta.cliente.Direccion2 }}</option>
                            <option :value="ventas[ventaActiva].cliente.Direccion3">{{ venta.cliente.Direccion3 }}</option>
                          </b-form-select>
                        </b-form-group>
                     
                      
                  </b-col>
                  <b-col lg="6" xl="6"  cols="12">
                    <b-form-checkbox
                      :id="`checkbox-${index}`"
                      v-model="venta.EsOrganizacion"
                      :value="true"
                      :unchecked-value="false"
                    >
                      Es Organización
                    </b-form-checkbox>  
                  </b-col>
                  <b-col lg="6" xl="6"  cols="12" class="pt-0" >
                    <b-form-input 
                    v-model="venta.nombreOrganizacion" 
                    v-if="venta.EsOrganizacion"
                    :id="`txtNombreOrganizacion-${index}`"
                    placeholder="NOMBRE DE LA ORGANIZACIÓN"></b-form-input>
                  </b-col>
                </b-row>
                  </b-col>
                  <b-col lg="6" xl="6"  cols="12" class="ocultarMovil">
                    <b-row>
                        <b-col lg="6" xl="6"  cols="12">
                    <center>
  
                      <h1> 
                        {{ (ventas[ventaActiva].cliente.Puntos || 0) | redondeoMenor }}
                    </h1>
                        <p> Puntos del cliente</p>
                     
                    </center>
                    </b-col>
  
                  <b-col lg="6" xl="6"  cols="12">
                    <center>
  
                      <h1> 
                        {{ventas[ventaActiva].Serie}}  - {{ventas[ventaActiva].Numero}}
                        <p style="padding: 2px 3px; border-radius: 25px;width:30% ; background: yellow; font-size: 1rem;"> Valor Referencial</p>
                      </h1>
                      {{ fechaActual()}}
                    </center>
                    </b-col>
                        
                    </b-row>
                
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
                  <b-col lg="12" xl="12"  cols="12">
                    <b-form-group>
                        <b-input-group>
                          <b-input-group-append>
                            <autocomplete
                                :ref="`autocompleteProducto${index}`"
                                :id="`autocompleteProducto${index}`"
                                :url="APIProductos"
                                :customParams="{ 'sortBy':'stock', 'sortDesc': 1 ,limit: '25' }"
                                :onShouldRenderChild="renderChild"
                                anchor="Producto"
                                label="writer"
                                placeholder="Agregar Producto"
                                :on-select="setProducto"
                                :onFocus="focusAutocomplete.bind(null, `autocompleteProducto${index}`)"
                                :classes="{input: 'form-control'}"
                                @keydown.native.tab.prevent="irCodeBar"
                                class="txtAutoComplete"
                               >
                            </autocomplete>
                            <b-button variant="primary" @click="startRecognition">
                              <i class="fas fa-microphone"></i>
                            </b-button>
                          </b-input-group-append>
                        <input v-model="loadCode"
                                :id="`txtCodeBar${index}`"
                                @keydown.enter.prevent="loadBarcode"
                                type="text"
                                class="form-control"
                                placeholder="Codigo de Barra" 
                                 @keydown.tab.prevent="irABuscarProducto"
                                />
                        <b-input-group-append>
                            <!--<proveedores-tabla></proveedores-tabla>-->
                            <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
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
                  <b-table striped hover :fields="headerTable"  :items="venta.productos">
                    <template slot="stock" slot-scope="data">
                    <div v-if="data.item.CodigoBarra=='COSTODELIVERY'">
                        <img src="static/moto-delivery.png" style="width: 24px" />
                    </div>
                    <div v-else>
                      <img v-if="data.item.EsPadre" src="static/box.png" style="width: 24px" />
                      <div v-else>{{ data.item.stock }}</div>
                    </div>
                  </template>
                    <template slot="pres" slot-scope="data">
                      <b-button   
                        v-if="data.item.tienePresentacion"
                        size="sm"
                        variant="info"
                        @click="showPresentacion(data.item,data.index)" 
                        style="margin-top:2px;"
                        >
                        <i class="fas fa-list"></i>
                      </b-button>
                      <b-button @click="abrirImagen(data.item.Producto,data.item.Imagen == null ||data.item.Imagen==''?'static/no-photo.png': '/resources/images/productos/' + data.item.Imagen)" style="margin-top:2px;"
                       :id="'popover-imagen' +data.index.IdProducto" variant="success" size="sm"><i class="fas fa-image"></i></b-button>	
                        <b-popover placement="right" :target="'popover-imagen' + data.index.IdProducto" :title="data.index.Producto" triggers="hover">
                      <div style="text-align:center">
                        <b-img style="border-radius: 10px; width:200px" fluid :src="data.item.Imagen == null || data.item.Imagen==''?'static/no-photo.png': '/resources/images/productos/' + data.item.Imagen" alt="" @error="setAltImg" ></b-img>	
                      </div>
                    </b-popover>
                       
                       
                    </template>
                    <template slot="Presentacion" slot-scope="data">
                      <b-form-input
                        v-model="data.item.Presentacion"
                        type="text"
                        style="text-align:center; font-weight:bold; width: 120px;"
                        autocomplete="false"
                        disabled
                        ></b-form-input>
                       
                       
                    </template>
                    <template slot="cantidad" slot-scope="data">
                      <input
                        v-model="data.item.cantidad"
                        type="number"
                        style="text-align:center; font-weight:bold; width: 90px;"
                      autocomplete="false"
                      :id="`input${data.index}`"
                      :disabled="data.item.esPresentacion"
                      @input="metodoPagoCambio(1)"
                       @focus="abrirModalTouch(data.index, 1)"
                       class="form-control"
                       @keydown.enter="irBusquedaProducto"
                        />
                        <p style="color: rgb(0, 123, 255); text-align: center; font-weight: bold;padding:0;margin:0;" 
                        v-if="data.item.esPresentacion">
                          
                          {{ data.item.CantidadPresentacion*data.item.cantidad }} unid.
                        </p>
                    </template>
                    <template slot="precio" slot-scope="data">
                      <input
                        :disabled="data.item.bloquearInputPrecio"
                        v-model="data.item.precio"
                        min="0"
                        step="0.00001"
                        type="text"
                        style="text-align:center; font-weight:bold; width: 105px;"
                        autocomplete="false"
                        @input="metodoPagoCambio(1)"
                         @focus="abrirModalTouch(data.index, 2)"
                         class="form-control"
                        >
                       
                    </template>
                    <template slot="descuento" slot-scope="data">
                      <input
                        v-model="data.item.descuento"
                        type="text"
                        style="text-align:center; font-weight:bold; width: 100px;"
                        autocomplete="false" 
                        @input="metodoPagoCambio(1)"
                         @focus="abrirModalTouch(data.index, 3)"
                         class="form-control"
                        />
                       
                    </template>
                    <template slot="subtotal" slot-scope="data">
                    {{ getSubTotalDetalle(data.item) }}
                       
                    </template>
                    <template slot="total" slot-scope="data">
                    {{ getTotalDetalle(data.item) }}
                       
                    </template>
                    <template slot="acciones" slot-scope="data">
                      <b-button 
                        variant="danger"
                        size="sm" 
                        @click="quitarProducto(data.index)">
                       <i class="fas fa-times fa-lg"></i>
                      </b-button>
            
                      <b-button 
                    class="mx-auto mt-1"
                      variant="info"
                      size="sm"
                      v-if="mostrarEditarProducto()"
                      @click="editarProducto(data.item,data.index)"
                      >
                     <i class="fas fa-pencil-alt fa-sm"></i>
                    </b-button>
       
                    </template>
                    <template slot="cantxPrecio" slot-scope="data">
                      <b-form-input
                        v-if="data.item.esPresentacion"
                        v-model="data.item.CantidadPresentacion"
                        type="text"
                        style="text-align:center; font-weight:bold; width: 100px;"
                        autocomplete="false"
                        @input="metodoPagoCambio(1)"
                        ></b-form-input>
                      <!-- {{data.item.esPresentacion}} -->
            <!-- esPresentacion -->
                       
                    </template>
  
                  </b-table>
                </b-row>
                </div>
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
                          <proformas :idAlmacen="venta.almacen.IdAlmacen" @onSelectProforma="setCliente" @cargarProforma="cargarProforma"></proformas>
                      </b-button-group>
                      <b-button-group size="sm" class="ocularMovil" v-if="false">
                          <pre-orden-lista 
                              v-show="!this.$route.query.idPreOrden"
                              :idAlmacen="venta.almacen.IdAlmacen" @onSelectPreOrden="setCliente"
                              @onPreOrden="selectPreOrden"></pre-orden-lista>
                      </b-button-group>
                      <b-button-group size="sm" class="ocularMovil">
                          
                          <config-venta @onUpdateConfig="eliminarCliente" @onUpdateConfigComision="eliminarComisionista"></config-venta>
                      </b-button-group>
                    </b-col>
                      <b-col cols="12" class="mt-1">
                        <table class="table b-table border table-footer">
                                    <tbody>
                                        <tr>
                                            <td  class="text-center">Opciones</td>
                                        </tr>
                                        <!-- <tr v-if="puntosAplicados == 0"> -->
                                        
                                        <tr>
                                            <td>
                                                <b-form-checkbox
                                                    v-model="ventas[ventaActiva].EsCredito"
                                                    :value="1"
                                                    :unchecked-value="0">
                                                    Venta a credito
                                                </b-form-checkbox>
                                                <b-form-input 
                                                    v-if="ventas[ventaActiva].EsCredito"
                                                    v-model="venta.FechaCredito"
                                                    type="date">
                                                </b-form-input>
                                                <b-form-checkbox 
                                                    v-model="ventas[ventaActiva].PorEntregar"
                                                    :value="1"
                                                    :unchecked-value="0">
                                                    Por entregar
                                                </b-form-checkbox>
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
                  <b-col lg="3" xl="3"  cols="12">
                   
  
                    <b-col cols="12" class="text-left">
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
                                      <input class="form-control" v-model="ventas[ventaActiva].pago.visa.monto" min="0" step="0.01" type="number" placeholder="S/." @input="ventas[ventaActiva].pago.efectivo.monto = $options.filters.redondear(getTotal() - ventas[ventaActiva].pago.mastercard.monto - ventas[ventaActiva].pago.delivery.monto - $event.target.value)"/>
                                    </td>
                                    </tr>
                                    <tr>
                                      <td>Yape/Plin</td>
                                      <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(3)"><i class="fas fa-check"></i></b-button></td>
                                      <td>
                                        <b-form-input v-model="ventas[ventaActiva].pago.mastercard.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                                      </td>
                                      <td>
                                        <input class="form-control" v-model="ventas[ventaActiva].pago.mastercard.monto" min="0" step="0.01" type="number" placeholder="S/." @input="ventas[ventaActiva].pago.efectivo.monto = $options.filters.redondear(getTotal() - ventas[ventaActiva].pago.visa.monto - ventas[ventaActiva].pago.delivery.monto - $event.target.value)"/>
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
                                          class="form-control" v-model="ventas[ventaActiva].pago.delivery.monto" min="0" step="0.01" type="number" placeholder="S/." @input="ventas[ventaActiva].pago.efectivo.monto = $options.filters.redondear(getTotal() - ventas[ventaActiva].pago.visa.monto - ventas[ventaActiva].pago.mastercard.monto - $event.target.value)"/>
                                      </td>
                                    </tr>
                                  </tbody>
                              </table>
                    </b-col>
                    <b-col md="12" v-if="ventas[ventaActiva].selectOpcionVenta > 0">
                      <b-form-group>
                        <label style="font-weight:bold">Nombre:</label>
                        <b-form-input 
                          required
                          style="text-transform:uppercase; 
                          font-size:0.9rem"
                          placeholder="Ingrese cliente"
                          autocomplete="off"
                          v-model="ventas[ventaActiva].nombreLlevar"
                          ref="inputNombreCliente" 
                          type="text" 
                          name="NombreLlevar"
                          id="NombreLlevar">
                        </b-form-input>
                      </b-form-group>
                    </b-col>
                    <b-col md="12" v-if="ventas[ventaActiva].selectOpcionVenta == 2">
                    <b-form-group>
                      <label style="font-weight:bold">Direccion Entrega:</label>
                      <b-form-textarea required style="text-transform:uppercase; font-size:0.9rem" placeholder="Ingrese direccion" rows="3" autocomplete="off" v-model="venta.DireccionEntrega" ref="inputDireccionEntrega" type="text" name="DireccionEntrega" id="DireccionEntrega"></b-form-textarea>
                    </b-form-group>
                    </b-col>
                    <b-col md="12" class="text-left">
                      
                      </b-col>
                  </b-col>
                  <b-col lg="3" xl="3"  cols="12">
                    <b-row class="centrado d-flex justify-content-center my-2" >
                      <b-button :disabled="loading" block class="mx-1 btnAction" variant="primary" @click="guardarProforma()"> Crear Proforma</b-button>
                      <b-button :disabled="loading" block class="mx-1 btnAction" variant="success" @click="guardarVenta()"> <i class="fas fa-shopping-cart"></i> PAGAR</b-button>
                      <b-button :disabled="loading" block variant="warning" class="btnAction"  @click="anadirVentana()"> Nueva Ventana</b-button>
                      <delivery-modal style="min-width: 100%; margin: 1% 0;" ref="deliveryModal" @onPagarDelivery="onPagarDelivery"></delivery-modal>
                      <b-button v-if="touchHab" style="background: #B0BEC5; color: #ffffff;" class="btnAction" block @click="activarModalTouch()"><i class="fas fa-desktop"></i> Deshabilitar Touch</b-button>
                      <b-button v-else style="background: #A1887F; color:#ffffff" class="btnAction" block @click="activarModalTouch()"><i class="fas fa-keyboard"></i> Habilitar Touch</b-button>
                      <b-button block v-if="ventas.length>1" variant="danger" @click="eliminarVentana()"> Cancelar Venta</b-button> 
                      <b-button :variant="mostrarNav ? 'danger' : 'info'" class="btnAction" block @click="ocultarNav()">
                        <i class="fas fa-bars"></i>
                        <span v-if="mostrarNav"> Ocultar Nav</span>
                        <span v-else> Mostrar Nav</span>
                      </b-button>
                    </b-row>
                  </b-col>
                  <b-col lg="3" xl="3"  cols="12">
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
                          <td class="table-success">{{ getSubTotal() }}</td>
                        </tr>
                        <tr>
                          <td class="table-danger">Descuento</td>
                          <td class="table-danger">
                            {{ getDescuento() }}
                          </td>
                        </tr>
                    
                        <tr>
                          <td><b>TOTAL</b></td>
                          <td class="table-danger">
                            {{ getTotal() }}
                          </td>
                        </tr>
                        <tr>
                          <td  colspan="1">
                            <label>pagó con:</label>
                            <b-form-input
                              type="text"
                              placeholder="Pagó con"
                              v-model="ventas[ventaActiva].pagocon"
                              >
                            </b-form-input><br>
                            </td>
                            <td>
                            <label>Vuelto:</label>
                            <b>S/ 
                                {{ parseFloat(ventas[ventaActiva].pagocon - getTotal()).toFixed(2) }}
  
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
          <h3 style="text-decoration: underline;">{{producto.Producto}}</h3>
        </center>
        <b-row>
            <b-col cols="6">
                  <b-card
                  align="center"
                  class="mb-3 contentCard"
                  @click="cambiarPresentacion(
                    producto,
                    false
                  )"
                  >
                  <b-row>
                      <b-col md="6" class="nomCart">
                        <div>
                            <b-row class="nomCart2">
                              <center> <h6 class="text-uppercase"> {{producto.ProductoPresentacion}} </h6> </center>
                              </b-row>
                              Stock <span style="padding: 5px 10px; border-radius: 5px ;background-color:#28a745;color:#fff"> {{ producto.stock }} </span>
                        </div>
                        </b-col>
                        <b-col md="6" class="text-left">
                        <h5>x1</h5>
                        <span v-if="idPerfil==1"><b >Precio C: </b>S/ {{ producto.PrecioCosto | redondear(2) }}</span><br v-if="idPerfil==1">
                        <b>Precio V: </b>S/ {{ producto.PrecioContado | redondear(2) }}
                      </b-col>
                  </b-row>
                  </b-card>
              </b-col>
  
              
            <b-col cols="6"  v-for="(pres,index) in producto.PreciosPorProducto" :key="index">
                  <b-card
                  align="center"
                  class="mb-3 contentCard"
                  @click="cambiarPresentacion(
                    pres,
                    true
                  )"
                  >
                  <b-row>
                      <b-col md="6" class="nomCart">
                        <div>
                            <b-row class="nomCart2">
                              <center> <h6 class="text-uppercase"> {{pres.nombre}} </h6> </center>
                              </b-row>
                              Stock <span style="padding: 5px 10px; border-radius: 5px ;background-color:#28a745;color:#fff">
                              {{  parseFloat(producto.stock/pres.cantidad).toFixed(2) }}
                              </span>
                        </div>
                        </b-col>
                        <b-col md="6" class="text-left">
                        <h5>x{{ pres.cantidad }}</h5>
                        <span v-if="idPerfil==1"><b >Precio C: </b>S/ {{ pres.precioc | redondear(2) }}</span><br v-if="idPerfil==1">
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
          <label><b style="text-decoration: underline;">{{objProd.Producto}}</b></label>
          <input type="text" inputmode="none" @keypress="(e)=>{ verificar(e)}" v-model="cantidadInput" style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control"/>
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
          <label><b style="text-decoration: underline;">{{objProd.Producto}}</b></label>
          <input type="text" inputmode="none" @keypress="(e)=>{ verificar(e)}" v-model="cantidadInput" style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control"/>
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
          <label><b style="text-decoration: underline;">{{objProd.Producto}}</b></label>
          <input type="text" inputmode="none" @keypress="(e)=>{ verificar(e)}" v-model="cantidadInput" style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control"/>
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
        <producto-form 
        :cbGuardar="(id,pxp) => agregarObj(id,pxp)" ref="productoFormEdit"
         :producto-update="productoSelect"  @actualizarProducto="actualizarRegistro($event)"
         ></producto-form>
        
      </b-container>
  </template>
  <script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import momemnt from "moment";
import ClientesForm from "@/components/ClientesForm";

// import ConfigVenta from "@/components/ConfigVenta";
// import PagoDeuda from "@/components/PagoDeuda";
// import PreOrdenLista from "@/components/PreOrdenLista.vue";
import Proformas from "@/components/ProformaLista.vue";
import PagoDeuda from "@/components/PagoDeuda";
import PreOrdenLista from "@/components/PreOrdenLista.vue";
import DeliveryModal from "@/components/DeliveryModal.vue";
import ConfigVenta from "@/components/ConfigVenta";
import ProductoForm from "@/components/ProductoForm";
import AutoComplete from "@/components/AutoComplete.vue";
import Movimiento from "@/components/Movimiento";
import Swal from "sweetalert2";
import { Bar } from "vue-chartjs";
export default {
  components: {
    ClientesForm,
    Proformas,
    PagoDeuda,
    Movimiento,
    PreOrdenLista,
    DeliveryModal,
    ConfigVenta,
    ConfigVenta,
    ProductoForm,
    AutoComplete
  },
  data() {
    return {
      loading: false,
      listeners: {
        nuevoPedido: null,
        connect: null,
        disconnect: null
      },
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
        { key: "Anaquel" },
        { key: "pres", label: "" },
        { key: "Presentacion", label: "Presentacion" },
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
        tipoVenta: {
          IdTipoDoc: 7,
          TipoDoc: "BOLETA/IGV ELEC.",
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
        FechaCredito: this.$moment().add(1, 'days').format("YYYY-MM-DD"),
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
        accionBoton: 1,
        idPerfil: 1
      },
      ventas: [
        {
          pagocon: 0,
          cliente: {},
          EsCredito: 0,
          Serie: "",
          Numero: "",
          Observacion: "",
          cliente: {},
          comisionista: {},
          tipoVenta: {
            IdTipoDoc: 7,
            TipoDoc: "BOLETA/IGV ELEC.",
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
          FechaCredito: this.$moment().add(1, 'days').format("YYYY-MM-DD"),
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
  beforeMount() {},
  mounted() {
    this.idPerfil = sessionStorage.getItem("IdPerfil");
    this.conectarSocket();
    this.setupSocketListeners();
    this.getEstadoSistema();
    this.$nextTick(() => {
      // setTimeout(() => {
      this.getVendedorInfo();
      this.getNumeroVenta();
      this.loadClienteVarios();
      let inputElement = `autocompleteProducto${this.ventaActiva}`;
      let autocompleteInput = document.getElementById(inputElement);
      autocompleteInput.focus();
      // }, 10000);
    });
  },

  beforeDestroy() {
    this.removeSocketListeners();
    this.$socket.disconnect();
  },
  methods: {
    conectarSocket() {
      if (!this.$socket.connected) {
        console.log("🔗 Conectando socket...");
        this.$socket.connect();
      }
    },
    irABarCode() {
      let inputElement = `txtCodeBar${this.ventaActiva}`;
      let autocompleteInput = document.getElementById(inputElement);
      autocompleteInput.focus();
    },
    irABuscarProducto() {
      let inputElement = `autocompleteProducto${this.ventaActiva}`;
      let autocompleteInput = document.getElementById(inputElement);
      autocompleteInput.focus();
    },
    /**
     * ✅ NUEVO: Configurar todos los listeners
     */
    setupSocketListeners() {
      // Evento: Conectado
      this.listeners.connect = () => {
        // console.log("✅ Socket conectado en VentasForm");
      };
      // this.$socket.on("connect", this.listeners.connect);

      // Evento: Desconectado
      this.listeners.disconnect = () => {
        // console.log("❌ Socket desconectado en VentasForm");
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
    },
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
              console.log(res.data.msg);
            }
          })
          .catch(err => {
            this.pagosPendientes = 0;
            console.log("Error de conexión al admin");
          });
      } else {
        this.pagosPendientes = 0;
      }
    },
    dniRuc(val) {
      this.newDniRuc = val;
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
      prod.Presentacion = prod.ProductoPresentacion
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
      console.log("ss");
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
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
      //Cargar producto del documento afectado nota credito
      this.axios
        .get(this.API + "venta/detalle/comprobante", {
          params: {
            NroComprobante: obj.NroComprobante,
            idDocVenta: obj.IdDocVenta,
            IdDocVentaPuntoVenta: this.ventas[this.ventaActiva].puntoVenta
              .IdDocVentaPuntoVenta
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
                this.setCliente(response.data.IdCliente);

                this.ventas[this.ventaActiva].CampoDireccion =
                  response.data.Direccion;
                this.ventas[this.ventaActiva].cliente = response.data;

                // let txtCliente = `inputCliente${this.ventaActiva}`;
                this.ventas[this.ventaActiva].verCliente = false;
                this.initValueCliente = this.ventas[
                  this.ventaActiva
                ].cliente.Cliente;
                this.ventas[this.ventaActiva].verCliente = true;
                this.$forceUpdate();
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });

          this.ventas.productos = [];

          response.data.forEach(element => {
            this.setProducto(element, true);
          });
          this.ventas[this.ventaActiva].NotaIdMotivo = null;
          this.$forceUpdate();
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
      const dniVarios = "00000000"; 
      
      try {
        const res = await this.axios.get(`${this.API}clientes/${dniVarios}`);
        if (res.data && res.data.IdCliente) {
          this.$nextTick(() => {
            this.setCliente(res.data);
          });
        }
      } catch (e) {
        console.error("Error cargando cliente por defecto", e);
      }
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
          // this.ventas[this.ventaActiva].almacen = obj;
          // console.log(ref);
          // let e = `inputAlmacen${this.ventaActiva}`;
          // document.getElementById(e).disabled = true;
          // let p = `inputPuntoVenta${this.ventaActiva}`;
          // document.getElementById(p).disabled = true;
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
      let prod = "autocompleteProducto";
      let val = ref.includes(prod);

      let element = document.getElementById(ref);
      if (val) {
        element.value = "";
        element.dispatchEvent(new Event("input", { bubbles: true }));
      }

      const event = new MouseEvent("dblclick");
      element.dispatchEvent(event);
    },
    setVendedor(obj) {
      if (obj.IdTipoDoc) this.getTipoDoc(obj.IdTipoDoc);
      this.venta.vendedor = obj;
      this.ventas[0].vendedor = obj;
    },
    setAlmacen(obj) {
      this.ventas[this.ventaActiva].almacen = obj;
      // this.$refs[ref][0].setValue(obj.Almacen)
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
          <table id="table-render" style="width: 830px; text-align: center;padding:0; margin:0">
            <tbody>
              <tr style="padding:0; margin:0">
                <td style="width: 60px" class="${styleStock}"><b>${
          data.EsPadre == 1
            ? '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />'
            : data.stock
              ? Number.isInteger(data.stock)
                ? data.stock
                : data.stock.toFixed(2)
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
      }
      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${
        data.ProductoMarca
      } / ${data.ProductoModelo}
      `;
    },
    loadBarcode(e) {
      e.preventDefault();
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
      console.log("sssssssss");
      let productos = this.ventas[this.ventaActiva].productos;
      if (productos.some(v => v.IdProducto == producto.IdProducto)) {
        let prod = productos.filter(v => v.IdProducto == producto.IdProducto);
        if (prod[0].Presentacion == "UNIDAD") {
          console.log("a");
          prod[0].cantidad += parseFloat(1);
        } else {
          console.log("b");
          prod[0].CantidadPresentacion += parseFloat(1);
        }
        this.metodoPagoCambio(1);
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
      prod.Presentacion = prod.ProductoPresentacion
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
      if (esNota) {
        if (producto.Producto == "TAPAJUNTA") {
          console.log(prod);
        }
        prod.cantidad = producto.cantidad;
        prod.precio = producto.PrecioContado;
      }
      this.$nextTick(() => {
        let input = document.getElementById("input0");
        input.focus();
        input.select();
      });
      this.metodoPagoCambio(1);
    },
    quitarProducto(index) {
      this.ventas[this.ventaActiva].productos.splice(index, 1);
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

      let ref = `inputCliente${this.ventaActiva}`;
      let inputCliente = this.$refs[ref];
      if (typeof obj === "object") {
        if (inputCliente && typeof inputCliente.setValue === "function") {
          // inputCliente.setValue(obj.Cliente);
        }
        this.ventas[this.ventaActiva].cliente = obj;
        this.ventas[this.ventaActiva].CampoDireccion = obj.Direccion;
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
    getSubTotal() {
      let prod = [...this.ventas[this.ventaActiva].productos];
      if (prod.length == 0) {
        return 0;
      } else {
        let subtotal = prod.reduce((total, prod) => {
          if (prod.esPresentacion) {
            return (
              total +
              parseFloat(
                prod.CantidadPresentacion * prod.cantidad * prod.precio
              )
            );
          } else {
            return total + parseFloat(prod.cantidad * prod.precio);
          }
          // return (
          //   total +
          //   parseFloat(
          //     prod.cantidad * prod.CantidadPresentacion * prod.precio
          //   ) -
          //   parseFloat(prod.descuento)
          // );
        }, 0);
        return parseFloat(subtotal).toFixed(2);
      }
    },
    getDescuento() {
      let prod = [...this.ventas[this.ventaActiva].productos];
      if (prod.length == 0) {
        return 0;
      } else {
        let subtotal = prod.reduce((total, prod) => {
          return total + parseFloat(prod.descuento);
        }, 0);
        return parseFloat(subtotal).toFixed(2);
      }
    },
    getTotal() {
      let prod = [...this.ventas[this.ventaActiva].productos];
      if (prod.length == 0) {
        return 0;
      } else {
        let subtotal = prod.reduce((total, prod) => {
          if (prod.esPresentacion) {
            return (
              total +
              parseFloat(
                prod.CantidadPresentacion * prod.cantidad * prod.precio -
                  prod.descuento
              )
            );
          } else {
            return (
              total + parseFloat(prod.cantidad * prod.precio - prod.descuento)
            );
          }
          // return (
          //   total +
          //   parseFloat(
          //     prod.cantidad * prod.CantidadPresentacion * prod.precio
          //   ) -
          //   parseFloat(prod.descuento)
          // );
        }, 0);
        return parseFloat(subtotal).toFixed(2);
      }
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
    metodoPagoCambio(tipo = null) {
      this.$nextTick(() => {
        const metodos = ["efectivo", "visa", "mastercard", "delivery"];
        const ventaActual = this.ventas[this.ventaActiva].pago;
        metodos.forEach(metodo => {
          ventaActual[metodo].descripcion = null;
          ventaActual[metodo].monto = 0;
        });
        if (tipo && metodos[tipo - 1]) {
          ventaActual[metodos[tipo - 1]].monto = this.getTotal();
        }
      });
    },

    async guardarVenta() {
      let venta = this.ventas[this.ventaActiva];
      let productos = venta.productos;
      let totalPagos = 0;
      this.loading = true;
      totalPagos +=
        this.obtenerMonto(venta.pago.efectivo.monto) +
        this.obtenerMonto(venta.pago.visa.monto) +
        this.obtenerMonto(venta.pago.mastercard.monto) +
        this.obtenerMonto(venta.pago.delivery.monto);
      if (this.obtenerMonto(venta.pago.efectivo.monto) < 0) {
        this.$toasted.show("El metodo de pago efectivo no puede ser negativo");
        this.loading = false;
        return;
      }
      if (this.obtenerMonto(venta.pago.visa.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago tarjeta/transferencia no puede ser negativo"
        );
        this.loading = false;
        return;
      }
      if (this.obtenerMonto(venta.pago.mastercard.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        this.loading = false;
        return;
      }
      if (this.obtenerMonto(venta.pago.delivery.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        this.loading = false;
        return;
      }
      if (totalPagos <= 0) {
        this.$toasted.show("Debe seleccionar un método de pago");
        this.loading = false;
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

        this.loading = false;
        return;
      }
      // if (
      //   venta.tipoVenta.IdTipoDoc != 12 &&
      //   productos.some(v => v.stock < v.CantidadPresentacion * v.cantidad)) {
      //   this.$swal({
      //     position: "center",
      //     icon: "info",
      //     title: "No se puede vender un producto sin stock.",
      //     showConfirmButton: true,
      //     confirmButtonText: "Entiendo",
      //     target: document.getElementById("conten"),
      //     confirmButtonColor: "#0275d8"
      //   });
      //   this.loading = false;
      //   return;
      // }
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
        this.loading = false;
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
        this.loading = false;
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

        this.loading = false;
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

          this.loading = false;
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

          this.loading = false;
          return;
        }
        if (venta.NroComprobanteModifica == "") {
          this.$toasted.show("Debe seleccionar un Documento de Referencia");

          this.$refs.inputDocRef.focus();
          this.loading = false;
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

        this.loading = false;
        return;
      }

      venta.nombreLlevar = venta.nombreLlevar.toUpperCase();
      // venta.productos = this.productos;
      venta.puntosAplicados = this.puntosAplicados || 0;
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
      await this.axios.post(this.API + "ventas", venta).then(response => {
        if (response.data.insertId) {
          this.$toasted.success("Venta realizada con exito");
          window.open(
            "/imprimir/index.php?IdDocVenta=" + response.data.insertId,
            "_blank",
            "noopener"
          );
        }

        if (this.ventas.length == 1) {
          window.location.reload();
          this.loading = false;
        } else {
          this.eliminarVentana();
          this.getVendedorInfo();
          setTimeout(() => {
            this.metodoPagoCambio(1);
            this.loading = false;
          }, 100);
        }

        // if (response.data.actualizar) {
        //   this.$toasted.show(response.data.msg);
        //   this.loading = false;  return;
        // }
      });
    },
    async guardarProforma() {
      let venta = this.ventas[this.ventaActiva];
      let productos = venta.productos;
      let totalPagos = 0;
      this.loading = true;
      totalPagos +=
        this.obtenerMonto(venta.pago.efectivo.monto) +
        this.obtenerMonto(venta.pago.visa.monto) +
        this.obtenerMonto(venta.pago.mastercard.monto) +
        this.obtenerMonto(venta.pago.delivery.monto);
      if (this.obtenerMonto(venta.pago.efectivo.monto) < 0) {
        this.$toasted.show("El metodo de pago efectivo no puede ser negativo");
        this.loading = false;
        return;
      }
      if (this.obtenerMonto(venta.pago.visa.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago tarjeta/transferencia no puede ser negativo"
        );
        this.loading = false;
        return;
      }
      if (this.obtenerMonto(venta.pago.mastercard.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        this.loading = false;
        return;
      }
      if (this.obtenerMonto(venta.pago.delivery.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        this.loading = false;
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

        this.loading = false;
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
        this.loading = false;
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
        this.loading = false;
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

        this.loading = false;
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

          this.loading = false;
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

          this.loading = false;
          return;
        }
        if (venta.NroComprobanteModifica == "") {
          this.$toasted.show("Debe seleccionar un Documento de Referencia");

          this.$refs.inputDocRef.focus();
          this.loading = false;
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

        this.loading = false;
        return;
      }

      venta.nombreLlevar = venta.nombreLlevar.toUpperCase();
      // venta.productos = this.productos;
      venta.puntosAplicados = this.puntosAplicados || 0;
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
    cargarProforma(proforma) {
      this.initValueCliente = proforma.DniRuc + " " + proforma.Cliente;
      let venta = { ...this.venta };
      venta.cliente = proforma;
      let productos = [];

      proforma.productos.forEach(async producto => {
        let prod = await this.obtenerStockBuscador(producto.IdProducto);
        let pres = [];
        if (prod.PreciosPorProducto) {
          let newPres = JSON.parse(prod.PreciosPorProducto);
          pres = newPres.filter(v => v.cantidad > 0);
        }
        prod.PreciosPorProducto = pres;

        prod.Presentacion = producto.Presentacion
          ? producto.Presentacion
          : "UNIDAD";
        prod.ProductoPresentacion = producto.ProductoPresentacion
          ? prod.ProductoPresentacion
          : "UNIDAD";
        prod.cantidad = 1;
        prod.precio = producto.PrecioContado;
        prod.descuento = 0;
        prod.tienePresentacion = prod.PreciosPorProducto.some(
          v => v.cantidad > 0
        );
        prod.bloquearInputPrecio = false;
        prod.CantidadPresentacion = 1;
        prod.ProductoPresentacion = producto.Descripcion;
        // prod.Presentacion = producto.Descripcion;
        prod.cantidad = producto.Cantidad;
        prod.CantidadPresentacion = producto.CantidadxPresentacion
          ? producto.CantidadxPresentacion
          : 1;
        prod.precio = producto.Precio;
        prod.descuento = producto.Descuento ? producto.Descuento : 0;
        prod.stock = prod.stock;
        prod.esPresentacion = pres.some(
          v => (v.nombre = producto.Presentacion)
        );
        prod.pres = pres.some(v => (v.nombre = producto.Presentacion))
          ? JSON.stringify(
              pres.filter(v => (v.nombre = producto.Presentacion))[0]
            )
          : null;
        productos.push(prod);
      });
      venta.productos = productos;

      this.ventas.push(venta);
      this.$nextTick(() => {
        this.ventaActiva = this.ventas.length - 1;
        this.getNumeroVenta();
        this.getVendedorInfo();
      });
      this.ventas[this.ventaActiva].cliente = proforma;
      setTimeout(() => {
        this.$nextTick(() => {
          console.log("hoña");
          this.metodoPagoCambio(1);
        });
      }, 1000);
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
        producto.Presentacion = obj.nombre;
        producto.pres = JSON.stringify(obj);
        producto.CantidadPresentacion = 1;
        producto.cantidad = obj.cantidad;
        producto.bloquearInputPrecio = true;
        producto.precio = obj.precio;
        producto.esPresentacion = esPresentacion;
      } else {
        producto.Presentacion = obj.ProductoPresentacion;
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
  },
  watch: {
    ventaActiva() {
      this.metodoPagoCambio(1);
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
  min-width: 400px !important; /* Estilo global para todos los popovers */
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
  text-transform: uppercase;
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

@media (max-width: 1250px) {
  .divCarrito {
    height: 70vh;
  }

  .clsCarrito {
    position: relative;
    max-height: 70vh;
    overflow-y: scroll;
  }
  .ocultarMovil {
    display: none;
  }
  .mostrarMovil {
    display: inline;
  }
  .txtAutoComplete {
    min-width: 280px !important;
  }
}
</style>
