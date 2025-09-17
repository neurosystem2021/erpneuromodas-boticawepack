<template>
   <b-container fluid>
    <b-tabs pills card  v-model="opcion">
      <b-tab :title="`Venta ${index+1}`" v-for="(venta,index) in ventas" :key="index">
       
        <b-row>
          <b-col cols="7">
            <b-row>
              <b-col md="4">
                <b-form-group  class="my-0">
                  <span class="ocularMovil">Tipo de Venta</span>
                    <label><b style="display:none" class="mostrarSerie">{{ venta.Serie }} - {{ venta.Numero }}</b></label>
                    <b-input-group>
                    <autocomplete
                        required="required"
                        :id="`inputTipoVenta${index}`"
                        :ref="`inputTipoVenta${index}`"
                        :url="API + 'ventas/tipos'"
                        anchor="TipoDoc"
                        initValue="BOLETA ELEC."
                        label="writer"
                        placeholder=""
                        :on-select="setTipoVenta" 
                        :onFocus="focusAutocomplete.bind(null, `inputTipoVenta${index}`)"
                        :classes="{input: 'form-control'}">
                    </autocomplete>
                    </b-input-group>
                </b-form-group>
                <b-form-group v-if="isNota" >
                   <label>Doc. Referencia:</label>
                   <autocomplete
                       required="required"
                       :id="`inputDocRef${index}`"
                       :url="API + 'ventas/lista/'+venta.puntoVenta.IdDocVentaPuntoVenta"
                       anchor="NroComprobante"
                       initValue=""
                       label="writer"
                       placeholder=""
                       :on-select="setDocRef"
                       :onFocus="focusAutocomplete.bind(null, `inputDocRef${index}`)"
                       :classes="{input: 'form-control'}">
                   </autocomplete>
                </b-form-group>                  
              </b-col>
              <b-col md="4" class="ocularMovil2 my-0">
                  <b-form-group>
                      <label class="my-0">Almacen:</label>
                      <autocomplete
                          required="required"
                          :id="`inputAlmacen${index}`"
                          :ref="`inputAlmacen${index}`"
                          :url="API + 'almacenes'"
                          anchor="Almacen"
                          initValue="HUANUCO"
                          label="writer"
                          placeholder=""
                          :on-select="setAlmacen" 
                          :onFocus="focusAutocomplete.bind(null, `inputAlmacen${index}`)"
                          :classes="{input: 'form-control'}">
                      </autocomplete>
                  </b-form-group>
                      <b-form-group v-if="isNota">
                      <label>Motivo :</label>
                      <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                </b-form-group>
              </b-col>  
              <b-col md="4" class="ocularMovil2">
                  <b-form-group>
                    <label class="my-0">Punto de Venta:</label>
                    <autocomplete
                      required="required"
                      :id="`inputPuntoVenta${index}`"
                      :url="API + 'ventas/puntos'"
                      anchor="PuntoVenta"
                      initValue="HUANUCO"
                      label="writer"
                      placeholder=""
                      :on-select="setPuntoVenta" 
                      :onFocus="focusAutocomplete.bind(null, `inputPuntoVenta${index}`)"
                      :classes="{input: 'form-control'}">
                    </autocomplete>
                  </b-form-group>
              </b-col>
              <b-col md="12" class="my-0">
                <b-form-group  class="my-0">
                  <label class="ocularMovil">Cliente</label>
                  <b-input-group>
                    <clientes-form txtButton="Editar" v-if="venta.cliente.IdCliente && venta.cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="venta.cliente"></clientes-form>
                    <autocomplete
                      required="required"
                      :ref="`inputCliente${index}`"
                      :id="`inputCliente${index}`"
                      :url="API + 'clientes'"
                      anchor="ClienteDniRuc"
                      label="writer"
                      placeholder=""
                      :onInput="(val) => newDniRuc = val"
                      :on-select="setCliente" 
                      :onFocus="focusAutocomplete.bind(null, `inputCliente${index}`)"
                      :classes="{input: 'form-control'}">
                    </autocomplete>
                    <input v-model="dni"
                      @keydown.enter.prevent="loadClienteBarcode"
                      type="text"
                      class="form-control ocularMovil2"
                      placeholder="Codigo de Barra" />
                    <b-input-group-append>
                      <!--<proveedores-tabla></proveedores-tabla>-->
                      <clientes-form :ruc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                    </b-input-group-append>
                  </b-input-group>
                  <b-form-select v-if="ventas[index].cliente.IdCliente" v-model="ventas[index].CampoDireccion" class="mb-0">
                    <option :value="ventas[index].cliente.Direccion">{{ ventas[index].cliente.Direccion }}</option>
                    <option :value="ventas[index].cliente.Direccion2">{{ ventas[index].cliente.Direccion2 }}</option>
                    <option :value="venta.cliente.Direccion3">{{ ventas[index].cliente.Direccion3 }}</option>
                  </b-form-select>
                  <b-row v-if="$route.query.idPedido">
                    <b-col>
                      <b-form-group>
                        <label style="font-weight:bold">Cliente (Delivery):</label>
                        <b-form-input required style="text-transform:uppercase; font-size:0.9rem" placeholder="Ingrese cliente" autocomplete="off" v-model="venta.nombreLlevar" ref="inputNombreCliente" type="text" name="NombreLlevar" id="NombreLlevar" readonly></b-form-input>
                      </b-form-group>
                    </b-col>
                    <b-col>
                      <b-form-group>
                        <label style="font-weight:bold">Direccion Entrega (Delivery):</label>
                        <b-form-textarea b-form-textarea required style="text-transform:uppercase; font-size:0.9rem" placeholder="Ingrese direccion" rows="3" autocomplete="off" v-model="venta.DireccionEntrega" ref="inputDireccionEntrega" type="text" name="DireccionEntrega" id="DireccionEntrega" readonly></b-form-textarea>
                      </b-form-group>
                    </b-col>
                  </b-row>
                </b-form-group>
              </b-col>
            </b-row>
          </b-col>
          <b-col md="3" class="ocularMovil" >
                        <b-form-group class="text-center" v-if="ventas[index].cliente.Cliente">
                            <div class="puntos">
                                {{ (venta.cliente.Puntos || 0) | redondeoMenor }}
                            </div>
                            Puntos del cliente
                        </b-form-group>
                        <b-input-group prepend="Puntos" :append="descuentoPuntos.descuento | redondear" v-if="venta.cliente.Puntos && descuentoGeneral == 0">
                            <b-form-input
                                v-model="puntosAplicados"
                                min="0"
                                :max="venta.cliente.Puntos"
                                @input="aplicarPuntos"
                                type="number"
                            ></b-form-input>
                        </b-input-group>
                        <b-form-text v-if="descuentoPuntos.ValorPunto">
                           1 Punto equivale a: S/ {{ descuentoPuntos.ValorPunto | redondear }}
                        </b-form-text>
                        <b-form-text v-if="venta.cliente.EsVIP">
                           Cliente VIP con Precio Convenio
                        </b-form-text>
                    </b-col>
                    <b-col md="2" class="ocularMovil2">
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
                                :id="`inputVendedor${index}`"
                                :ref="`inputVendedor${index}`"
                                :url="API + 'usuarios'"
                                anchor="NombreUsuario"
                                label="writer"
                                :on-select="setVendedor" 
                                :onFocus="focusAutocomplete.bind(null, `inputVendedor${index}`)"
                                :classes="{input: 'form-control'}">
                            </autocomplete>
                        </b-form-group>
                        <b-form-group class="ocularMovil">
                            <label>Comisionista</label>
                            <autocomplete
                                :ref="`inputComisionista${index}`"
                                :id="`inputComisionista${index}`"
                                required="required"
                                :url="API + 'comisionistas'"
                                anchor="ClienteDniRuc"
                                label="writer"
                                placeholder="Comisionista"
                                :onInput="(val) => newDniRuc = val"
                                :on-select="setComisionista" 
                                :onFocus="focusAutocomplete.bind(null, `inputComisionista${index}`)"
                                :classes="{input: 'form-control'}">
                            </autocomplete>
                        </b-form-group>
                        <b-badge variant="info" v-if="adelanto">Adelanto de: S/ {{ adelanto }} </b-badge>
                        <b-badge variant="success" v-if="adelanto">Total a pagar: S/ {{ $options.filters.redondear(totalCart - adelanto) }} </b-badge>
                    </b-col>
        </b-row>
        <b-row>
            <b-col cols="12"> 
             <!-- @onInputCantidad="actualizarDescuento"
             
              @onRemoveProducto="actualizarDescuento" -->
              <productos-carrito 
                :igvTotal="tieneIgv"
                :setPrecio="true"
                ref="carrito" 
                :showButton="false" 
                :bloquearInput="bloquearInput"
                :fields="carrito.fields"
                :isVentas="true"
                :idAlmacen="venta.almacen.IdAlmacen"
                :onSetProducto="onSetProducto"
                :esDelivery="venta.EsDelivery"
                :productos="ventas[index].productos"
                :index="index"
                 @onAddProducto="onAddProducto"
                >
              </productos-carrito>
            </b-col>
          </b-row>
      </b-tab>
      
    </b-tabs>

    <b-row>
      <b-col cols="12"  md="4" lg="3" class="text-center" align-h="center">
        <b-button class="btnAction" type="button" @click="anularPagoDelivery" variant="danger" v-if="this.$route.query.idPedido">Anular Pago Delivery</b-button>
        <b-button class="btnAction" type="button" v-if="!this.$route.query.idPedido"  :disabled="inProgress" variant="primary" @click="guardarProforma">Crear Proforma</b-button>
        <b-button class="btnAction" @click="guardarVenta" :disabled="inProgress" variant="success" >Efectuar Venta</b-button>
        <b-button class="btnAction" variant="warning" @click="anadirVentana()"> Añadir Ventana</b-button>
        <b-button class="btnAction" variant="danger" @click="eliminarVenta()" v-if="opcion>0"> Eliminar Ventana</b-button>
        <b-button class="btnAction" v-if="ultimaVenta" target="_blank" variant="info"  :href="'/imprimir/index.php?IdDocVenta=' + ultimaVenta">Imprimir última Venta</b-button>
                        <!--  -->
                    </b-col>
       <!-- <b-col cols="12" md="3" lg="3" v-if="!ventas[opcion].EsCredito" > -->
       <b-col cols="12" md="3" lg="3" >
          <table class="table b-table border table-footer table-sm text-center "  >
              <tbody>
                  <tr>
                    <td>Descripción:</td>
                    <td>
                      <b-form-textarea
                        placeholder="Descripción"
                        rows="3"
                        max-rows="6"
                        v-model="ventas[opcion].descripcion"
                      ></b-form-textarea>
                    </td>
                  </tr>
                  <tr class="ocularMovil"><td colspan="2">Mano de obra bruta</td></tr>
                  <tr class="ocularMovil"><td colspan="2"><h3>{{ manoDeObraBruta | redondear }}</h3></td></tr>
                  <tr class="ocularMovil"><td colspan="2">Mano de obra neta</td></tr>
                  <tr class="ocularMovil"><td colspan="2"><h3 :class="{'text-danger': manoDeObraNeta <= 0}">{{ manoDeObraNeta | redondear }}</h3></td></tr>
              </tbody>
          </table>
        </b-col>

          <b-col cols="12" md="4" lg="3" >
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
                       <b-button class="p-1 ml-1 mt-1" id="popover-metodopago" variant="success"><i class="fas fa-credit-card" ></i></b-button>
                       <br>
                       <b-badge v-if="idMetodoPago==1" variant="success" style="font-size: 0.70rem;">EFECTIVO</b-badge>
                       <b-badge v-if="idMetodoPago==2" variant="info" style="font-size: 0.70rem;">TARJETA</b-badge>
                       <b-badge v-if="idMetodoPago==3" variant="info" style="font-size: 0.70rem;">YAPE/PLIN</b-badge>
                       <b-badge v-if="idMetodoPago==4" variant="info" style="font-size: 0.70rem;">TRANSF.</b-badge>
                       <b-popover 
                           target="popover-metodopago" 
                           placement="bottom" 
                           triggers="click" 
                           custom-class="custom-popover"
                           auto-hide
                         >
                           <div class="table-responsive p-2">
                             <table class="table table-bordered table-sm">
                               <tbody>
                                 <tr>
                                   <td colspan="3" class="text-center font-weight-bold">Método de Pago</td>
                                 </tr>
                                 <tr>
                                   <td>Efectivo</td>
                                   <td>
                                     <b-button @click="cargarMonto('efectivo')" size="sm" variant="primary">
                                       <i class="fa fa-check" aria-hidden="true"></i>
                                     </b-button>
                                   </td>
                                   <td>
                                     <b-form-input
                                       v-model="ventas[opcion].pago.efectivo.descripcion"
                                       type="text"
                                       :disabled="bloquearInput"
                                       placeholder="Descripcion"
                                     ></b-form-input>
                                   </td>
                                   <td>
                                     <input
                                       v-model="ventas[opcion].pago.efectivo.monto"
                                       min="0"
                                       :disabled="bloquearInput"
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
                                     <b-button size="sm" @click="cargarMonto('visa')" variant="primary">
                                       <i class="fa fa-check" aria-hidden="true"></i>
                                     </b-button>
                                   </td>
                                   <td>
                                     <b-form-input
                                       v-model="ventas[opcion].pago.visa.descripcion"
                                       type="text"
                                       :disabled="bloquearInput"
                                       placeholder="Descripcion"
                                     ></b-form-input>
                                   </td>
                                   <td>
                                     <input
                                       class="form-control"
                                       :disabled="bloquearInput"
                                       v-model="ventas[opcion].pago.visa.monto"
                                       min="0"
                                       step="0.01"
                                       type="number"
                                       placeholder="S/."
                                       @input="ventas[opcion].pago.efectivo.monto = $options.filters.redondear(totalCart - ventas[opcion].pago.mastercard.monto - ventas[opcion].pago.yape.monto - $event.target.value)"
                                     />
                                   </td>
                                 </tr>
                                 <tr>
                                   <td>Transferencia</td>
                                   <td>
                                     <b-button size="sm" @click="cargarMonto('mastercard')" variant="primary">
                                       <i class="fa fa-check" aria-hidden="true"></i>
                                     </b-button>
                                   </td>
                                   <td>
                                     <b-form-input
                                       v-model="ventas[opcion].pago.mastercard.descripcion"
                                       :disabled="bloquearInput"
                                       type="text"
                                       placeholder="Descripcion"
                                     ></b-form-input>
                                   </td>
                                   <td>
                                     <input
                                       class="form-control"
                                       v-model="ventas[opcion].pago.mastercard.monto"
                                       :disabled="bloquearInput"
                                       min="0"
                                       step="0.01"
                                       type="number"
                                       placeholder="S/."
                                       @input="ventas[opcion].pago.efectivo.monto = $options.filters.redondear(totalCart - ventas[opcion].pago.visa.monto - ventas[opcion].pago.yape.monto - $event.target.value)"
                                     />
                                   </td>
                                 </tr>
                                 <tr>
                                   <td>YAPE / PLIN</td>
                                   <td>
                                     <b-button size="sm" @click="cargarMonto('yape')" variant="primary">
                                       <i class="fa fa-check" aria-hidden="true"></i>
                                     </b-button>
                                   </td>
                                   <td>
                                     <b-form-input
                                       v-model="ventas[opcion].pago.yape.descripcion"
                                       type="text"
                                       placeholder="Descripcion"
                                     ></b-form-input>
                                   </td>
                                   <td>
                                     <input
                                       class="form-control"
                                       v-model="ventas[opcion].pago.yape.monto"
                                       min="0"
                                       step="0.01"
                                       type="number"
                                       placeholder="S/."
                                       @input="ventas[opcion].pago.efectivo.monto = $options.filters.redondear(totalCart - ventas[opcion].pago.visa.monto - ventas[opcion].pago.mastercard.monto - $event.target.value)"
                                     />
                                   </td>
                                 </tr>
                               </tbody>
                             </table>
                           </div>
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
                       title="Opciones" triggers="click" auto-hide>
                     <table class="table b-table border table-footer">
                       <tbody>
                         <tr>
                           <td class="text-center ocularMovil">Opciones</td>
                         </tr>
                         <tr v-if="puntosAplicados == 0" class="ocularMovil">
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
                               Descuento general a toda la ventas[opcion].
                             </b-form-text>
                           </td>
                         </tr>
                         <tr>
                           <td>
                             <b-form-checkbox
                               :disabled="bloquearInput ? true : false || isNota || ventas[opcion].EsDelivery"
                               v-model="ventas[opcion].EsCredito"
                               :value="1"
                               :unchecked-value="0">
                               Venta a crédito
                             </b-form-checkbox>
                             <b-form-input 
                               :disabled="bloquearInput ? true : false || isNota || ventas[opcion].EsDelivery"
                               v-if="ventas[opcion].EsCredito"
                               v-model="ventas[opcion].FechaCredito"
                               type="date">
                             </b-form-input>
                             <b-form-checkbox 
                               :disabled="bloquearInput ? true : false || ventas[opcion].EsDelivery"
                               v-model="ventas[opcion].PorEntregar"
                               :value="1"
                               :unchecked-value="0">
                               Por entregar
                             </b-form-checkbox>
                             <b-form-checkbox 
                               :disabled="bloquearInput ? true : false || ventas[opcion].EsDelivery"
                               v-model="ventas[opcion].ProformaSinPrecio"
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
                   </b-popover>  
                 </td>
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
               <tr>
                 <td>G. G</td>
                 <td>
                   {{ 
                     
                    parseFloat(
                     productos.reduce((sum,prod)=>{
                       return sum + (prod.precio - prod.PrecioCosto) * prod.cantidad 
                     },0)
                    ).toFixed(2)
                     
                     
                     }}
                 </td>
               </tr>
             </tbody>
           </table>
          </b-col>

          <b-col cols="12"  md="4" lg="3" class="text-center" align-h="center">          
            <delivery-modal ref="deliveryModal" @onPagarDelivery="onPagarDelivery"></delivery-modal>

              <movimiento></movimiento>

              <pago-deuda></pago-deuda>
              <!-- <proformas :idAlmacen="ventas[opcion].almacen.IdAlmacen" @onSelectProforma="setCliente"  @guardarDatosProforma="guardarDatosProforma"></proformas> -->
              <proformas :idAlmacen="ventas[opcion].almacen.IdAlmacen" @onSelectProforma="guardarDatosProforma"></proformas>

              <pre-orden-lista            
                  v-show="!this.$route.query.idPreOrden"
                  :idAlmacen="ventas[opcion].almacen.IdAlmacen" @onSelectPreOrden="setCliente"
                  @onPreOrden="selectPreOrden"

                  :EsVenta="true"
                  ></pre-orden-lista>

              <!--<config-venta @onUpdateConfig="eliminarCliente"></config-venta>-->
              <config-venta @onUpdateConfig="eliminarCliente" @onUpdateConfigComision="eliminarComisionista"></config-venta>
                       
          </b-col>
    </b-row>
   </b-container>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import ProductosCarrito from "@/components/ProductosCarritoNuevo";
import ClientesForm from "@/components/ClientesForm";
import ConfigVenta from "@/components/ConfigVenta";
import PagoDeuda from "@/components/PagoDeuda";
import PreOrdenLista from "@/components/PreOrdenLista.vue";
import Proformas from "@/components/ProformaLista.vue";
import DeliveryModal from "@/components/DeliveryModal.vue";
import Movimiento from "@/components/Movimiento";
import Swal from "sweetalert2";
export default {
  beforeMount() {
    this.initForm();
  },
  mounted() {
    // this.getNumeroVenta()
    this.getVendedor();
    //setInterval(this.updateTime, 1000)
    this.loadClienteVarios();
    this.loadComisionista();
    this.escucharPedidos();
    this.getEstadoSistema();
    this.ObtenerLstOfertas();
  },
  data() {
    return {
      ventas: [],
      opcion: 0,
      vuelto: 0,
      pagocon: 0,
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
      ventaObj: { IdProforma: null, descripcion: "" },
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
            class: "text-center ocularMovil"
          },
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center"
          },
          { key: "Producto", class: "Producto", class: "clsProducto" },
          { key: "Descripcion", label: "Descripción", class: "ocularMovil" },
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
            class: "text-center"
          },
          { key: "descuento", label: "Desc", class: "ocularMovil" },
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
      fechaUltPago: ""
    };
  },
  watch: {
    totalCart(newVal) {
      this.ventas[this.opcion].pago.efectivo.monto = parseFloat(
        newVal -
          (parseFloat(this.ventas[this.opcion].pago.visa.monto) +
            parseFloat(this.ventas[this.opcion].pago.mastercard.monto) +
            parseFloat(this.ventas[this.opcion].pago.yape.monto))
      ).toFixed(2);
    }
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      totalCart: state => state.cart.totalCart
    }),
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
    ...mapActions({
      aplicarDescuento: "aplicarDescuentoGeneral",
      limpiarCart: "limpiarCarrito",
      obtenerStok: "obtenerStock",
      actualizarPrecioConvenio: "actualizarPrecioConvenio",
      add: "agregarProducto",
      actualizarTotal: "actualizarTotalCart"
    }),
    anadirVentana() {
      let newVenta = { ...this.ventaObj };
      newVenta.productos = [];
      // console.log(newVenta);
      this.ventas.push({ ...this.ventaObj, productos: [] });
      this.$nextTick(() => {
        this.opcion = this.ventas.length - 1;
        setTimeout(() => {
          this.getVendedor();
          this.getNumeroVenta();
          this.loadClienteVarios();
          this.loadComisionista();
        }, 100);
      });
    },
    eliminarVenta() {
      console.log(this.opcion);
      this.ventas.splice(this.opcion, 1);
      this.$nextTick(() => {
        this.opcion = this.opcion - 1;
      });
    },
    asignarVuelto() {
      this.vuelto = this.pagocon - this.sumatoriaTotal;
    },
    cargarMonto(tipo) {
      if (
        this.ventas[this.opcion].pago &&
        this.ventas[this.opcion].pago.hasOwnProperty(tipo)
      ) {
        Object.keys(this.ventas[this.opcion].pago).forEach((key, index) => {
          this.ventas[this.opcion].pago[key].monto =
            key === tipo ? this.totalCart : 0;
          if (key === tipo) {
            this.idMetodoPago = index + 1;
          }
        });

        // console.log("Montos actualizados:", this.ventas[this.opcion].pago);
      } else {
        console.error(
          `El tipo de pago "${tipo}" no existe en this.ventas[this.opcion].pago`
        );
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
      if (this.ventas[this.opcion].tipoVenta.CodSunat == "99") {
        let TotalMontoOfertas = this.DocRefOfertas.reduce((sum, oferta) => {
          return sum + parseFloat(oferta.Total);
        }, 0);

        if (this.DocRefOfertas.length == 0) {
          console.log("a");
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
          console.log("ab");
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
            console.log("ac");
            return false;
          }
        }
        return true;
      }
      return true;
    },

    mostrarSeccionOferta() {
      if (this.ventas[this.opcion].tipoVenta.CodSunat == "99") {
        return true;
      }
      return false;
    },
    async obtenerProductoOferta() {
      this.$store.state.cart.productos = [];
      this.$nextTick(async () => {
        let query = this.lstOfertas.filter(v => v.IdOferta == this.IdOferta)[0];
        console.log("query", query);
        let stock = await this.axios(
          `${this.API}productos/stock?idProductos[]=${
            query.IdProductoAsociado
          }&idAlmacen=${this.ventas[this.opcion].almacen.IdAlmacen}`
        ).then(res => {
          return res.data;
        });
        console.log("stock", stock);

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
    loadCliente(id) {
      this.axios
        .get(this.API + "clientes", {
          params: {
            idCliente: id
          }
        })
        .then(res => {
          if (res.data.IdCliente) {
            let ref = `inputCliente${this.opcion}`;
            this.$nextTick(() => {
              let autocompleteComponent = this.$refs[ref];
              if (Array.isArray(autocompleteComponent)) {
                autocompleteComponent = autocompleteComponent[0];
              }
              if (
                autocompleteComponent &&
                typeof autocompleteComponent.setValue === "function"
              ) {
                autocompleteComponent.setValue(res.data.Cliente);
              }
              this.setAlmacen(res.data);
            });
            this.setCliente(res.data);
          }
        });
    },
    loadClienteVarios() {
      this.axios.get(this.API + "clientes/00000000").then(res => {
        if (res.data.IdCliente) {
          let ref = `inputCliente${this.opcion}`;
          this.$nextTick(() => {
            let autocompleteComponent = this.$refs[ref];
            if (Array.isArray(autocompleteComponent)) {
              autocompleteComponent = autocompleteComponent[0];
            }
            if (
              autocompleteComponent &&
              typeof autocompleteComponent.setValue === "function"
            ) {
              autocompleteComponent.setValue(res.data.Cliente);
            }
            this.setAlmacen(res.data);
          });
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
      console.log(orden);
      // this.ventas[this.opcion].IdPreOrden = this.ventas[this.opcion].IdPreOrden || []
      // if (Array.isArray(this.ventas[this.opcion].IdPreOrden) ) {
      this.ventas[this.opcion].IdPreOrden.push(orden);
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
      this.axios.get(this.API + "almacenes/primero").then(res => {
        // this.$refs.inputAlmacen.setValue(res.data.Almacen);
        this.ventas[this.opcion].almacen = res.data;
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
      // this.$refs[ref].handleDoubleClick();
      let element = document.getElementById(ref);
      if (element) {
        element.value = "";
        element.dispatchEvent(new Event("input", { bubbles: true }));
      }

      const event = new MouseEvent("dblclick");
      element.dispatchEvent(event);
    },
    initForm() {
      this.ventaObj = {
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
        ProformaSinPrecio: 0,
        productos: []
      };
      this.ventas.push({ ...this.ventaObj });
      this.isNota = false;
      this.bloquearInput = false;
      this.adelantosPreOrden = [];
      this.adelanto = 0;
      this.infoDelivery = {};
    },
    setTipoVenta(obj) {
      this.isNota = false;
      this.bloquearInput = false;
      this.ventas[this.opcion].NroComprobanteModifica = "";
      this.ventas[this.opcion].CodSunatModifica = "";
      this.ventas[this.opcion].NotaIdMotivo = "";
      this.ventas[this.opcion].NotaDescMotivo = "";

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
      // this.$refs[`inputTipoVenta${this.opcion}`].setValue(obj.TipoDoc);

      this.ventas[this.opcion].tipoVenta = obj;
      this.getNumeroVenta();
    },
    setDocRefOferta(obj) {
      console.log(obj);
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
      this.ventas[this.opcion].CodSunatModifica = obj.CodSunat;
      this.ventas[this.opcion].NroComprobanteModifica = obj.NroComprobante;
      if (
        this.ventas[this.opcion].tipoVenta.CodSunat == "07" ||
        this.ventas[this.opcion].tipoVenta.CodSunat == "08"
      ) {
        this.ventas[this.opcion].Serie = this.ventas[this.opcion].Serie.replace(
          /^./,
          obj.NroComprobante[0]
        );
      }
      this.axios
        .get(this.API + "ventas/solonumero", {
          params: {
            serie: this.ventas[this.opcion].Serie,
            idTipoDoc: this.ventas[this.opcion].tipoVenta.IdTipoDoc
          }
        })
        .then(response => {
          this.ventas[this.opcion].Numero = response.data.numero;
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
            IdDocVentaPuntoVenta: this.ventas[this.opcion].puntoVenta
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
                this.$refs.inputCliente.setValue(response.data.Cliente);
                this.ventas[this.opcion].cliente = response.data;
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });
          this.limpiarCart();
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
          this.ventas[this.opcion].NotaIdMotivo = null;
          this.bloquearInput = true;
          this.obtenerStok({
            IdAlmacen: this.ventas[this.opcion].almacen.IdAlmacen
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
        let ref = `inputAlmacen${this.opcion}`;
        this.$nextTick(() => {
          let autocompleteComponent = this.$refs[ref];
          if (Array.isArray(autocompleteComponent)) {
            autocompleteComponent = autocompleteComponent[0];
          }
          if (
            autocompleteComponent &&
            typeof autocompleteComponent.setValue === "function"
          ) {
            autocompleteComponent.setValue(res.data.Almacen);
          }
          this.setAlmacen(res.data);
        });
      });
    },
    setAlmacen(obj) {
      // this.$refs.this.ventas[this.opcion].almacen = obj;
      this.obtenerStok({
        IdAlmacen: this.ventas[this.opcion].almacen.IdAlmacen
      });
    },
    loadPuntoVenta(id) {
      this.axios.get(this.API + "puntoventa/id/" + id).then(res => {
        let ref = `inputPuntoVenta${this.opcion}`;
        this.$nextTick(() => {
          let autocompleteComponent = this.$refs[ref];
          if (Array.isArray(autocompleteComponent)) {
            autocompleteComponent = autocompleteComponent[0];
          }
          if (
            autocompleteComponent &&
            typeof autocompleteComponent.setValue === "function"
          ) {
            autocompleteComponent.setValue(res.data.PuntoVenta);
          }
          this.setAlmacen(res.data);
        });
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
      this.ventas[this.opcion].puntoVenta = obj;
      // this.$refs.inputPuntoVenta.setValue(obj.PuntoVenta);
      this.getNumeroVenta();
    },
    getVendedor() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
          let e = `inputAlmacen${this.opcion}`;
          document.getElementById(e).disabled = true;
          let p = `inputPuntoVenta${this.opcion}`;
          document.getElementById(p).disabled = true;
          let u = `inputVendedor${this.opcion}`;
          document.getElementById(u).disabled = true;

          let ref = `inputVendedor${this.opcion}`;
          this.$nextTick(() => {
            let autocompleteComponent = this.$refs[ref];
            if (Array.isArray(autocompleteComponent)) {
              autocompleteComponent = autocompleteComponent[0];
            }
            if (
              autocompleteComponent &&
              typeof autocompleteComponent.setValue === "function"
            ) {
              autocompleteComponent.setValue(response.data.NombreUsuario);
            }
            // this.setAlmacen(res.data);
          });
          // this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          // this.$refs.inputVendedor.$refs.input.disabled = true;
          // this.loadAlmacen(response.data.IdAlmacen);
          // this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
          // // bloquear ambos inputs
          // this.$refs.inputAlmacen.$refs.input.disabled = true;
          // this.$refs.inputPuntoVenta.$refs.input.disabled = true;
          // //this.getNumeroVenta()
          // this.getTipoDoc(response.data.IdTipoDoc);
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    setVendedor(obj) {
      // console.log(obj)
      if (obj.IdTipoDoc) this.getTipoDoc(obj.IdTipoDoc);

      this.ventas[this.opcion].vendedor = obj;
    },
    setComisionista(obj) {
      if (typeof obj === "object") {
        // this.$refs.inputComisionista.setValue(obj.Cliente);
        this.ventas[this.opcion].comisionista = obj;
      } else {
        this.ventas[this.opcion].comisionista = {};
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
        this.ventas[this.opcion].cliente = obj;
        this.ventas[this.opcion].CampoDireccion = this.ventas[
          this.opcion
        ].cliente.Direccion;
      } else {
        this.axios
          .get(this.API + "cliente", {
            params: params
          })
          .then(response => {
            if (response.data) {
              this.ventas[this.opcion].cliente = response.data;
              this.ventas[this.opcion].CampoDireccion = this.ventas[
                this.opcion
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
    },
    onAddProducto(obj) {
      // this.actualizarDescuento();

      this.ventas[this.opcion].productos.push(obj);

      // this.obtenerStok({
      //   IdAlmacen: this.ventas[this.opcion].almacen.IdAlmacen
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
      if (parseFloat(val) > this.ventas[this.opcion].cliente.Puntos) {
        this.puntosAplicados = this.ventas[this.opcion].cliente.Puntos;
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
      this.axios
        .get(this.API + "ventas/numero", {
          params: {
            idPuntoVenta: this.ventas[this.opcion].puntoVenta
              .IdDocVentaPuntoVenta,
            idTipoDoc: this.ventas[this.opcion].tipoVenta.IdTipoDoc
          }
        })
        .then(response => {
          this.ventas[this.opcion].Serie = response.data.serie;
          this.ventas[this.opcion].Numero = response.data.numero;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    updateTime() {
      this.ventas[this.opcion].FechaDoc = this.$moment().format(
        "YYYY-MM-DDTH:mm:ss"
      );
    },
    limpiarDespuesVenta() {
      this.inProgress = false;
      this.tieneIgv = false;
      this.initForm();
      this.limpiarCart();
      this.getVendedor();
      this.$refs.inputCliente.setValue("");
      // this.getNumeroVenta()
      // this.$refs.inputTipoVenta.setValue('TICKET')
      // this.$refs.inputAlmacen.setValue('VENTA')
      // this.$refs.inputPuntoVenta.setValue('CAJA1')
      // this.$refs.inputVendedor.setValue('')
    },
    guardarDatosProforma(proforma) {
      console.log("proforma", proforma.productos);
      let newVenta = { ...this.ventaObj };
      this.ventas.push({ ...newVenta, productos: [] });
      this.$nextTick(() => {
        this.opcion = this.ventas.length - 1;
        setTimeout(() => {
          this.getVendedor();
          this.getNumeroVenta();
          this.loadCliente({
            IdCliente: proforma.IdCliente,
            Cliente: proforma.Cliente
          });
          this.loadComisionista();
        }, 1000);
      });
      // this.ventas[this.opcion].IdProforma = IdProforma;
      // console.log("IdProforma", IdProforma);
    },
    async guardarProforma() {
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

      if (!this.ventas[this.opcion].cliente.IdCliente) {
        this.$toasted.show("Debe seleccionar un cliente");
        this.inProgress = false;
        return;
      }

      if (!this.ventas[this.opcion].vendedor.Usuario) {
        this.$toasted.show("Debe seleccionar un vendedor");
        this.inProgress = false;
        return;
      }
      if (this.ventas[this.opcion].ProformaSinPrecio == 1) {
        this.productos.map(p => {
          p.disabledProf = 1;
        });
      }

      this.ventas[this.opcion].productos = this.productos;
      this.ventas[this.opcion].diasValidosProf = this.diasValidosProf;
      await this.axios
        .post(this.API + "proformas", this.ventas[this.opcion])
        .then(response => {
          this.$toasted.success("Proforma Guardada con exito");
          if (this.ventas.length == 1) {
            console.log("recargar");
            // window.location.reload();
          } else {
            this.ventas.splice(this.opcion, 1);
            this.$nextTick(() => {
              this.opcion = 0;
            });
          }

          window.open(
            "/nuevo/index.html#/reportes/proforma/" + response.data.insertId,
            "_blank"
          );
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
    },
    async guardarVenta(evt) {
      evt.preventDefault();

      this.inProgress = true;
      let totalPagos = 0;
      totalPagos += isNaN(
        parseFloat(this.ventas[this.opcion].pago.efectivo.monto)
      )
        ? 0
        : parseFloat(this.ventas[this.opcion].pago.efectivo.monto);
      totalPagos += isNaN(parseFloat(this.ventas[this.opcion].pago.visa.monto))
        ? 0
        : parseFloat(this.ventas[this.opcion].pago.visa.monto);
      totalPagos += isNaN(
        parseFloat(this.ventas[this.opcion].pago.mastercard.monto)
      )
        ? 0
        : parseFloat(this.ventas[this.opcion].pago.mastercard.monto);
      totalPagos += isNaN(parseFloat(this.ventas[this.opcion].pago.yape.monto))
        ? 0
        : parseFloat(this.ventas[this.opcion].pago.yape.monto);

      if (
        (isNaN(parseFloat(this.ventas[this.opcion].pago.efectivo.monto))
          ? 0
          : parseFloat(this.ventas[this.opcion].pago.efectivo.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de pago efectivo no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.ventas[this.opcion].pago.visa.monto))
          ? 0
          : parseFloat(this.ventas[this.opcion].pago.visa.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de pago Tarjeta no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.ventas[this.opcion].pago.mastercard.monto))
          ? 0
          : parseFloat(this.ventas[this.opcion].pago.mastercard.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show(
          "El metodo de pago Transferencia no puede ser negativo"
        );
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.ventas[this.opcion].pago.yape.monto))
          ? 0
          : parseFloat(this.ventas[this.opcion].pago.yape.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show(
          "El metodo de pago YAPE / PLIN no puede ser negativo"
        );
        this.inProgress = false;
        return;
      }

      if (!this.ventas[this.opcion].EsCredito) {
        if (this.totalCart != totalPagos) {
          this.$toasted.show("Los pagos no coinciden con la venta");
          this.inProgress = false;
          return;
        }
      }

      if (!this.ventas[this.opcion].cliente.DniRuc) {
        this.$toasted.show("Debe seleccionar un cliente.");
        this.inProgress = false;
        return;
      }

      var esNumerico = Number.isInteger(
        parseInt(this.ventas[this.opcion].cliente.DniRuc)
      );
      if (!esNumerico) {
        this.$toasted.show("El RUC debe ser numérico, editar cliente.");
        this.inProgress = false;
        return;
      }

      var esEmpresa =
        this.ventas[this.opcion].cliente.DniRuc.length > 9 ? true : false;
      if (!esEmpresa && this.ventas[this.opcion].tipoVenta.CodSunat == "01") {
        this.$toasted.show(
          "Una factura debe ser emitida para una empresa, no un usuario común."
        );
        this.inProgress = false;
        return;
      }

      //Nota de Creadito
      if (
        this.ventas[this.opcion].tipoVenta.CodSunat == "07" ||
        this.ventas[this.opcion].tipoVenta.CodSunat == "08"
      ) {
        if (
          !this.ventas[this.opcion].CodSunatModifica ||
          !this.ventas[this.opcion].NroComprobanteModifica
        ) {
          this.$toasted.show("Debe seleccionar un Documento de Referencia...");
          this.inProgress = false;
          return;
        }
        if (!this.ventas[this.opcion].NotaIdMotivo) {
          this.$toasted.show("Debe seleccionar un Motivo");
          this.inProgress = false;
          return;
        }
        this.ventas[this.opcion].NotaDescMotivo = this.itemsMotivoNota.filter(
          item => item.value == this.ventas[this.opcion].NotaIdMotivo
        )[0].text;
      }

      if (!this.productos.length) {
        this.$toasted.show("No hay productos para vender");
        this.inProgress = false;
        return;
      }

      // NO PERMITE CANTIDAD Y PRECIOS 0
      let sinStock = 0;
      let totalCero = 0;
      let menosUtilidad = 0;
      this.productos.forEach(pro => {
        if (
          pro.CodigoBarra !== "COSTODELIVERY" &&
          pro.CodigoBarra !== "MANODEOBRA" &&
          (pro.stock <= 0 || parseFloat(pro.cantidad) > pro.stock) &&
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
          pro.CodigoBarra !== "MANODEOBRA"
        ) {
          menosUtilidad += 1;
        }
      });

      if (this.ventas[this.opcion].tipoVenta.CodSunat != "99") {
        if (
          menosUtilidad >= 1 &&
          this.ventas[this.opcion].tipoVenta.CodSunat != "07"
        ) {
          this.$toasted.error(
            "No se puede vender un producto con ganancia negativa"
          );
          this.inProgress = false;
          return;
        }
      }
      if (
        sinStock >= 1 &&
        this.ventas[this.opcion].tipoVenta.CodSunat != "07"
      ) {
        this.$toasted.error("No se puede vender un producto sin stock");
        this.inProgress = false;
        return;
      }
      if (this.ventas[this.opcion].tipoVenta.CodSunat != "99") {
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

      // this.ventas[this.opcion].productos = this.productos;
      this.ventas[this.opcion].puntosAplicados = this.puntosAplicados || 0;
      this.ventas[this.opcion].totalCart = this.totalCart || 0;
      this.ventas[this.opcion].PagoCon = this.$refs.carrito.pagocon;
      this.ventas[this.opcion].adelantosPreOrden = this.adelantosPreOrden;
      this.ventas[this.opcion].IdPedido = this.$route.query.idPedido || null;
      this.ventas[this.opcion].nombreLlevar = this.ventas[
        this.opcion
      ].nombreLlevar.toUpperCase();
      this.ventas[this.opcion].IdOferta = this.IdOferta;
      this.ventas[this.opcion].yapeMonto = this.ventas[this.opcion].pago.yape
        .monto
        ? this.ventas[this.opcion].pago.yape.monto
        : 0;
      this.ventas[this.opcion].yapeDescripcion = this.ventas[this.opcion].pago
        .yape.descripcion
        ? this.ventas[this.opcion].pago.yape.descripcion
        : "";
      this.ventas[this.opcion].visaMonto = this.ventas[this.opcion].pago.visa
        .monto
        ? this.ventas[this.opcion].pago.visa.monto
        : 0;
      this.ventas[this.opcion].visaDescripcion = this.ventas[this.opcion].pago
        .visa.descripcion
        ? this.ventas[this.opcion].pago.visa.descripcion
        : "";
      this.ventas[this.opcion].mastercardMonto = this.ventas[this.opcion].pago
        .mastercard.monto
        ? this.ventas[this.opcion].pago.mastercard.monto
        : 0;
      this.ventas[this.opcion].mastercardDescripcion = this.ventas[this.opcion]
        .pago.mastercard.descripcion
        ? this.ventas[this.opcion].pago.mastercard.descripcion
        : "";
      this.ventas[this.opcion].DireccionEntrega = this.ventas[this.opcion]
        .EsDelivery
        ? this.infoDelivery.Direccion || ""
        : "";

      this.ventas[this.opcion].IdDocVentas = "";
      if (this.ventas[this.opcion].tipoVenta.CodSunat == "99") {
        let IdDocVentas = this.DocRefOfertas.map(v => v.idDocVenta);
        this.ventas[this.opcion].IdDocVentas = IdDocVentas.join(",");
      }
      await this.axios
        .post(this.API + "ventas", this.ventas[this.opcion])
        .then(response => {
          // limpiar preorden seleccionado
          // this.$router.replace("/ventas");
          this.$toasted.success("Venta realizada con exito");
          this.inProgress = false;
          this.tieneIgv = false;
          this.ultimaVenta = response.data.insertId;
          // this.initForm();
          // this.limpiarCart();
          // this.getVendedor();
          // this.getNumeroVenta();
          // this.loadClienteVarios();
          // this.loadComisionista();
          // this.$refs.inputTipoVenta.setValue("BOLETA ELEC.");
          // this.$refs.inputAlmacen.setValue("VENTA");
          // this.$refs.inputPuntoVenta.setValue("CAJA1");
          // this.$refs.inputCliente.setValue("");
          // this.$refs.inputVendedor.setValue("");
          // this.$refs.inputComisionista.setValue("");
          this.$refs.carrito.pagocon = 0;
          let IdOfertasString = IdOfertas.join(",");
          window.open(
            "/imprimir/index.php?IdDocVenta=" +
              response.data.insertId +
              "&IdOfertas=" +
              IdOfertasString,
            "_blank"
          );
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
      console.log("length", this.ventas.length);
      if (this.ventas.length == 1) {
        console.log("recargar");
        window.location.reload();
      } else {
        this.ventas.splice(this.opcion, 1);
        this.$nextTick(() => {
          this.opcion = 0;
        });
      }
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
      this.$refs.inputCliente.setValue("");
      this.ventas[this.opcion].cliente = {};
    },
    eliminarComisionista() {
      this.$refs.inputComisionista.setValue("");
      this.ventas[this.opcion].comisionista = {};
    },
    onPagarDelivery(obj) {
      this.$route.query.idPedido = obj.IdPedido;
      if (this.$route.query.idPedido) {
        this.infoDelivery = obj;
        this.ventas[this.opcion].EsDelivery = true;
        this.$refs.carrito.pagocon = parseFloat(obj.Cambio).toFixed(2);
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
                this.ventas[this.opcion].nombreLlevar =
                  obj.Cliente || "SIN NOMBRE";
                this.ventas[this.opcion].DireccionEntrega = obj.Direccion;
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
          this.ventas[this.opcion].nombreLlevar = obj.Cliente || "SIN NOMBRE";
          this.ventas[this.opcion].DireccionEntrega = obj.Direccion;
        }
      }
    },
    anularPagoDelivery() {
      this.limpiarCart();
      this.ventas[this.opcion].EsDelivery = false;
      this.infoDelivery = {};
      this.$refs.carrito.pagocon = "";
      this.loadClienteVarios();
      this.loadComisionista();
      this.ventas[this.opcion].nombreLlevar = "";
      this.ventas[this.opcion].selectOpcionVenta = 0;
      this.ventas[this.opcion].nombreLlevar = "";
      this.ventas[this.opcion].DireccionEntrega = "";
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
    }
    //Fin Sockets
  },
  components: {
    ProductosCarrito,
    ClientesForm,
    ConfigVenta,
    PagoDeuda,
    PreOrdenLista,
    Proformas,
    Movimiento,
    DeliveryModal
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

@keyframes slide {
  0% {
    transform: translatex(100vw);
  }

  100% {
    transform: translatex(-120vw);
  }
}

@media only screen and (max-width: 920px) {
  /* For desktop: */
  .ocularMovil {
    display: none !important;
  }
}
@media only screen and (min-width: 777px) and (max-width: 1800px) {
  /* For desktop: */
  .ancho {
    width: calc(100% - 300px);
  }
}
@media only screen and (max-width: 750px) {
  /* For desktop: */
  .ocularMovil2 {
    display: none !important;
  }
  .mostrarSerie {
    display: inline !important;
  }
}
/* @media (max-width: 850px) {
  .ocularMovil {
    display: none !important;
  }
} */
.clsProducto {
  min-width: 200px !important;
  white-space: normal;
  word-wrap: break-word;
}
</style>
