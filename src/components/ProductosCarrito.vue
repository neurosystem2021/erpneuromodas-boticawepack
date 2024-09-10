<template>
  <b-row>
    <b-col md="12">
      <b-form-group>
        <b-input-group>
           <!-- <b-button variant="success" class="ml-1 btn-anadir" @click="abrirModalListProducto()"> Añadir </b-button> -->
              <BusquedaProducto v-on:monto="cantProductos=$event" v-on:modal="abrirModal=$event"/>
              
               <!-- <b-input-group-text><strong class="text-danger">!</strong></b-input-group-text> -->
           
          <!-- <autocomplete
            ref="autocompleteProducto"
            :url="APIProductos"
            :customParams="{ 'sortBy':isVentas?'stock':'IdProducto', 'sortDesc': 1 ,limit: '25' }"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder="Agregar Producto"
            :on-select="abrirCalcularM2"
            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
            class="mx-1"
            :classes="{input: 'form-control'}">
          </autocomplete> -->
          <input v-model="loadCode"
                  @keydown.enter.prevent="loadBarcode"
                  type="text"
                  class="form-control txt-codigobarra ml-5"
                  placeholder="Codigo de Barra" />
                   <b-button>
                <i class="fa fa-barcode"></i>
              </b-button>
          <b-input-group-append>
            <!--<proveedores-tabla></proveedores-tabla>-->
             <b-button variant="primary" @click="loadBarcode" class="btn-anadir"><i class="fas fa-search"></i></b-button>
            <producto-form :showButton="showButton" :cbGuardar="(id,pxp,obj) =>{agregarGuardar(id,pxp,obj)}" ref="productoFormEdit" :producto-update="productoSelect"></producto-form>
          </b-input-group-append>
        </b-input-group>
      </b-form-group>

      <b-modal size="lg" ref="modalCant" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration: underline;">{{objProd.Producto}}</h3>
          <h5>Presentaciones</h5>
        </div>
        <hr>
        <b-row>
          <b-col cols="6"  v-if="false">
            <b-card
              align="center"
              class="mb-3 contentCard"
              @click="setProductoUni(objProd)"
            >
              <b-row>
                <b-col md="6" class="nomCart">
                  <h5 class="text-uppercase">UNIDAD</h5>
                </b-col>
                <b-col md="6" class="text-left">
                  <b>Cant: </b>1
                  <br>
                  <b>Precio: </b>S/ {{ precioCont }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>

          <b-col cols="6" v-for="d in preciosProd" :key="d.id">
            <b-card
              v-if="d.cantidad != 0"
              align="center"
              class="mb-3 contentCard"
              @click="() => {
                if ( parseFloat(d.cantidad) ) {
                  setProductoUni(objProd, d.precio)
                }
              }"
            >
              <b-row>
                <b-col md="6" class="nomCart" v-bind:class="[d.nombre=='OFERTA' ? 'ofertanom' : '']">
                  <h5 class="text-uppercase">{{ d.nombre }}</h5>
                </b-col>
                <b-col md="6" class="text-left">
                  <b>Cant: </b>{{ d.cantidad }}
                  <br>
                  <b>Precio: </b>S/ {{ d.precio }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>
        </b-row>
      </b-modal>

      <b-modal id="modalImagen" size="lg" ref="modalImagen" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration: underline;">{{productoNombre}}</h3>
        </div>
        <b-row style="display:flex; justify-content:center; align-items:center">
          <b-img :src="productoUrl" class="imagenGrande"></b-img>
        </b-row>
      </b-modal>

      <b-modal size="md" ref="modalCalcularM2" hide-footer title="Calcular M2">
      <div class="d-block text-center">
        <b>{{ productoSelect.Producto }}</b>
        <hr />
        <label>Metros Cuadrados (M²):</label>
        <input
            type="number"
            ref="inputM2"
            placeholder="M²"
            class="form-control"
            @input="val => calcularm2(val.target.value, productoSelect.Rendimiento, productoSelect.Ancho, productoSelect.Largo, productoSelect.ProductoDetCantidad )"/><br>
        <hr />
        <p v-html="mensajeM2"></p>
      </div>
      <b-btn class="mt-3" variant="outline-success" block @click="seleccionarCalcularM2">Agregar</b-btn>
    </b-modal>

      <b-table small responsive  striped hover :items="productos" :fields="fields" ref="tablaCarrito" style="margin-top:-12px" v-if="tableProductos">
        <template slot="eventPres" slot-scope="data">
          <b-button size="sm" v-if="productos[data.index].estadoPxP == false"
           :disabled="bloquearInput?true:false" variant="info"
            @click="cambiarPresentacion(productos[data.index],data.index)">
            <i class="fas fa-list"></i>
          </b-button>
          <b-button @click="abrirImagen(productos[data.index].Producto,productos[data.index].Imagen == null || productos[data.index].Imagen==''?'static/no-photo.png': '/resources/images/productos/' + productos[data.index].Imagen)" style="margin-top:2px;" :id="'popover-imagen' + productos[data.index].IdProducto" variant="success" size="sm"><i class="fas fa-image"></i></b-button>	
          <b-popover placement="right" :target="'popover-imagen' + productos[data.index].IdProducto" :title="productos[data.index].Producto" triggers="hover">
            <div style="text-align:center">
              <b-img style="border-radius: 10px; width:200px" fluid :src="productos[data.index].Imagen == null || productos[data.index].Imagen==''?'static/no-photo.png': '/resources/images/productos/' + productos[data.index].Imagen" alt="" @error="setAltImg" ></b-img>	
            </div>
          </b-popover>
        </template>

        <template slot="obsequio" slot-scope="data">
          <!-- <div class="obsequio" v-if="tipoMov==0"> -->
          <div class="obsequio">
            <b-form-checkbox
              button-variant="primary"
              v-model="productos[data.index].EsObsequio"
              :value="1"
              @input="esObsequio(data.index)"
              :unchecked-value="0">
            </b-form-checkbox>
          </div>
        </template>

        <template slot="cantxPrecio" slot-scope="data" v-if="data.item.tipo == 'PRESENTACION'">
          <input
            v-model="productos[data.index].cantxPrecio"
            type="number"
            style="width: 80px"
            step="1"
            min="0"
            :disabled="bloquearInput?true:false"
            @input="(val) => cambiarCantidadPresentacion(productos[data.index],data.index,'PRESENTACION')"
            placeholder="Cantidad"
            class="form-control" />
          <span>x{{productos[data.index].cantidadPres}}</span>
        </template>

        <template slot="cantidad" slot-scope="data">
          <!-- cambiarCantidadPresentacion(productos[data.index],data.index) -->
          <input
            v-model="productos[data.index].cantidad"
            :disabled="productos[data.index].disabled ||bloquearInput?true:false || esDelivery"
            type="number"
            style="width: 95px"
            class="form-control"
            step="0.01"
            min="0"
            
            @input="(val) => {
              recalcularTotal(data.index)
              $emit('onInputCantidad', val.target.value)
              /*if(this.ventas){
                if(!productos[data.index].PreciosPorProducto) {
                productos[data.index].precio = (productos[data.index].StockPorMayor && val.target.value >= productos[data.index].StockPorMayor) ? productos[data.index].PrecioPorMayor : (tipo == 'mov' ? productos[data.index].PrecioCosto : productos[data.index].PrecioContado)
                return
                }

                let reg = JSON.parse(productos[data.index].PreciosPorProducto).find( e => e.cantidad === productos[data.index].cantidadPres )
                if(reg && reg.cantidad){
                  productos[data.index].precio = reg.precio
                  return
                }
                
                productos[data.index].precio = productos[data.index].PrecioContado 
              }else{
                if(productos[data.index].EsObsequio==1){
                    productos[data.index].precio = 0
                    productos[data.index].nuevoPrecioContado = 0
                }else{
                    productos[data.index].precio = productos[data.index].PrecioCosto; 
                }
              }*/

            }"
            placeholder="Cantidad"/>
        </template>
        <template slot="precio" slot-scope="data">
            <!-- :disabled="productos[data.index].disabled || bloquearInput?true:false || esDelivery || (productos[data.index].EsObsequio==1 && tipoMov==0 ?true:false) || (isVentas && idPerfil!=1 && tipoDocSunat!='99')" -->
          <input
            v-model="productos[data.index].precio"
            type="number"
            style="width: 95px"
            min="0"
            step="0.01"
            class="form-control"
            placeholder="Precio"
            @input="recalcularTotal(data.index)"
            />
        </template>
        <template slot="precioc" slot-scope="data">
          <input
            v-model="productos[data.index].precioc"
            type="number"
            style="width: 95px"
            min="0"
            step="0.01"
            class="form-control"
            placeholder="Precio"
            @input="recalcularTotal(data.index)"
            />
        </template>
        <template slot="subtotal" slot-scope="data">
          <input
            v-model="productos[data.index].subtotal"
            type="number"
            style="width: 95px"
            min="0"
            step="0.01"
            class="form-control"
            placeholder="Subtotal"
            v-if="$store.state.esOrdenCompra==1"
            @input="recalcularPrecioCompra(data.index)"
            />
          {{ parseFloat(productos[data.index].subtotal).toFixed(2) }}
        </template>
        <template slot="igv" slot-scope="data">
          <b-form-checkbox
            v-model="productos[data.index].TieneIgv"
            :value="1"
            :disabled="bloquearInput?true:false || (productos[data.index].EsObsequio==1 && tipoMov==0 ?true:false)"
            @input="setNuevoPrecioContado(data.index)"
            :unchecked-value="0">
            <div>{{ getIgv(data.index) }}</div>
          </b-form-checkbox>
        </template>
        <!--<template slot="flete" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].FLETE"
            type="number"
            min="0"
            placeholder="Flete"></b-form-input>
        </template>-->
        
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
            v-if="productos[data.index].CodigoBarra != 'MANODEOBRA' " 
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
            style="width: 80px"
            type="number" 
            step="0.01"
            @input="cambiarPrecio(data.index, productos[data.index])" 
            @mouseup="cambiarPrecio(data.index, productos[data.index])" 
            />
            <small>ganancia: {{ (productos[data.index].precio - productos[data.index].PrecioCosto) | redondear }}</small>
        </template>

        <template slot="descuento" slot-scope="data">
          <input
            :disabled="bloquearInput?true:false  || esDelivery"
            v-model="productos[data.index].PorcentajeDesc"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            value="0"
            :max="tipoVenta==2?productos[data.index].PorcentajeDescMax:(tipoVenta==1?descCorporativoProp:(tipoVenta==3?descInstitucionalProp:0))"
            @input="val =>{productos[data.index].descuento = Math.round(getSubtotal(data.index) * val.target.value/100*10)/10; actualizarDescu(data.index); } "/>
            <b-form-text class="text-center">
                <b style="color:#dc3545">% Max: {{tipoVenta==2?productos[data.index].PorcentajeDescMax:(tipoVenta==1?descCorporativoProp:(tipoVenta==3?descInstitucionalProp:0))}}</b>
                <br>
                <b>Desc: S/{{productos[data.index].descuento}}</b>      
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
          <textarea 
            :disabled="bloquearInput?true:false"
            class="form-control"
            placeholder="Descripción"
            cols="9" rows="1"
            style="width: 170px"
            v-model="productos[data.index].Descripcion"></textarea>
          <!--<b-form-input
            v-model="productos[data.index].Descripcion"
            type="text"
            placeholder="Descripción"></b-form-input>-->
        </template>        

        <template slot="total" slot-scope="data">
          {{ parseFloat(productos[data.index].total).toFixed(2) }}
          <br>
          <h6 v-if="false"><i>Prof.</i></h6>
          <input 
             v-if="false"
            class="form-check-input" 
            type="checkbox" 
            :disabled="bloquearInput?true:false"
            @change="(e) => {
              $emit('onCheckProf', e)
              asignarDisabledProf(data.index, e)
            }"
          />
        </template>
        <template slot="acciones" slot-scope="data">
          <b-button :disabled="bloquearInput?true:false" variant="danger" size="sm" @click="quitarProducto(data.index)"><i class="fas fa-times fa-lg"></i></b-button>
          <b-button  style="margin-top:5px" :disabled="bloquearInput?true:false" v-if="(idPerfil == 1) ? true : false"  variant="primary" size="sm" @click="editarProducto(productos[data.index])"><i class="fas fa-pencil-alt fa-sm"></i></b-button>
        </template>

        <template slot="show_details" slot-scope="row">
           <b-button :disabled="bloquearInput?true:false" size="sm" variant="info" @click.stop="row.toggleDetails">></b-button>
        </template>
        

        <template slot="stock" slot-scope="data">
          <div v-if="data.item.CodigoBarra=='COSTODELIVERY'">
              <img src="static/moto-delivery.png" style="width: 24px" />
          </div>
          <div v-else>
            <div>{{ data.item.stock }}</div>
            <img v-if="data.item.EsPadre" src="static/box.png" style="width: 24px" />
          </div>
        </template>
      </b-table>
    </b-col>
    <b-col md="3">
      <slot name="operaciones"></slot>
    </b-col>
    <b-col md="4">
      <slot name="metodopago"></slot>
    </b-col>
    <b-col md="2">
      <slot name="adicional"></slot>
    </b-col>
    <b-col md="3" v-show="tipo == 'mov' || !opcionCarrito">
 <table class="table b-table border table-footer">
            <tbody>
              <tr v-if="sumatoriaDescuento > 0">
                <td class="table-success">SubTotal</td>
                <td class="table-success">{{ parseFloat(sumatoriaSubTotal()) + parseFloat(sumatoriaDescuento) | redondear }}</td>
              </tr>
              <tr v-if="sumatoriaDescuento > 0">
                <td class="table-danger">Descuento</td>
                <td class="table-danger">- {{ sumatoriaDescuento  }}</td>
              </tr>
              <tr>
                <td><b>SubTotal</b></td>
                <td class="table-danger">{{ sumatoriaSubTotal()  }}</td>
              </tr>
              <tr v-if="sumatoriaIgv > 0">
                <td><b>IGV</b></td>
                <td class="table-danger">{{ sumatoriaIgv }}</td>
              </tr>
              <tr>
                <td><b>TOTAL</b></td>
                <td class="table-danger">{{ sumatoriaTotal() }}</td>
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
                  <b>S/ {{ calcVuelto() }}</b>
                </td>
              </tr>
            </tbody>
          </table>
    </b-col>
    <!-- <b-col md="3" v-show="tipo != 'mov' || opcionCarrito">
          <table class="table b-table border table-footer">
            <tbody>
              <tr v-if="sumatoriaDescuento > 0">
                <td class="table-success">SubTotal</td>
                <td class="table-success">{{ parseFloat(sumatoriaSubTotal()) + parseFloat(sumatoriaDescuento) | redondear }}</td>
              </tr>
              <tr v-if="sumatoriaDescuento > 0">
                <td class="table-danger">Descuento</td>
                <td class="table-danger">- {{ sumatoriaDescuento  }}</td>
              </tr>
              <tr>
                <td><b>SubTotal</b></td>
                <td class="table-danger">{{ sumatoriaSubTotal()  }}</td>
              </tr>
              <tr v-if="sumatoriaIgv > 0">
                <td><b>IGV</b></td>
                <td class="table-danger">{{ sumatoriaIgv }}</td>
              </tr>
              <tr>
                <td><b>TOTAL</b></td>
                <td class="table-danger">{{ sumatoriaTotal() }}</td>
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
                  <b>S/ {{ calcVuelto() }}</b>
                </td>
              </tr>
            </tbody>
          </table>
    </b-col> -->
    <br><br>
    <!-- <b-modal v-model="$store.state.modalNuevoProducto" size="md" title="Añadir Producto" class="" hide-footer>
      <b-row>
        <b-col cols="6">
         <b-row class="presentacion">
            <b-col cols="12">
              <b-card  align="center" class="mb-3 contentCard"
               @click="anadirProductosListado({item:null})" 
              @keyup.enter="anadirProductosListado({item:null})" 
              @keyup.space="anadirProductosListado({item:null})" 
              tabindex="2">
                <b-row>
                <b-col md="6" class="nomCart">
                  <h5 class="text-uppercase">UNIDAD</h5>
                </b-col>
                <b-col md="6" class="text-left">
                  <b>Cant: </b>1
                    <br>
                    <b>Precio: </b> S/{{ esOrdenCompra ? $store.state.producto.PrecioCosto : $store.state.producto.PrecioContado}}
                  </b-col>
                </b-row>
              </b-card>
            </b-col>
            <div v-if=" $store.state.producto.PreciosPorProducto.length>0">
              <b-col cols="12" v-for="(precio,index) in $store.state.producto.PreciosPorProducto.filter((v)=>v.cantidad >0 )" :key="index" >
                <b-card  align="center" class="mb-3 contentCard" 
                @click="anadirProductosListado({item:precio, tipo:'PRESENTACION'})" 
                @keyup.enter="anadirProductosListado({item:precio, tipo:'PRESENTACION'})" 
                @keyup.space="anadirProductosListado({item:precio, tipo:'PRESENTACION'})" 
                :tabindex="index+3"
              
                >
                  <b-row>
                  <b-col md="6" class="nomCart">
                    <h5 class="text-uppercase">{{precio.nombre}}</h5>
                  </b-col>
                  <b-col md="6" class="text-left">
                    <b>Cant: </b>{{precio.cantidad}}
                      <br>
                      <b>Precio: </b> S/{{ esOrdenCompra ? (precio.precioc?precio.precioc : 0) :precio.precio}}
                    </b-col>
                  </b-row>
                </b-card>
              </b-col>
            </div>
          </b-row>
        <b-col class="calculadora">

        </b-col> 
        <b-col cols="6" class="calculadora">
              <b-row>
                <b-col cols="12">
                  <b-input placeholder="Cantidad" class="my-2" v-model="cantProductos" type="number" ref="frmCantidad" 
                     :state="validarCantidad" trim tabindex="1"
                     />
                  <b-form-invalid-feedback id="input-live-feedback">
                      {{ errorCantidad }}
                  </b-form-invalid-feedback>
                  
                </b-col>
                <b-col cols="4"  v-for="(num,index) in numerosCalculadora" :key="index">
                  <b-button class="mx-1 my-1 numCalc" pill variant="outline-secondary" @click="btnCalculadora(num)">
                    <i v-if="num.value=='back'" :class="num.text" />
                    <span v-else> {{num.text}}</span>
                    
                  
                  </b-button>
                  
                </b-col>
                <b-col cols="4" >
                  <b-button class="mx-1 my-1 numCalc" pill variant="outline-secondary" @click="cantProductos=''" block>CE
                  </b-button>
                  
                </b-col>
              </b-row>
             

        </b-col>
        
      </b-row>
    </b-modal> -->
    <b-modal v-model="modalEditarProducto" size="md" title="Añadir Producto" class="" hide-footer>
      <b-row>
        <b-col cols="6">
         <b-row class="presentacion">
            <b-col cols="12">
              <b-card  align="center" class="mb-3 contentCard" 
              @click="actualizarProductosListado({item:null})"
              @keyup.enter="actualizarProductosListado({item:null})"
              @keyup.space="actualizarProductosListado({item:null})"
              tabindex="2"
              >
                <b-row>
                <b-col md="6" class="nomCart">
                  <h5 class="text-uppercase">UNIDAD</h5>
                </b-col>
                <b-col md="6" class="text-left">
                  <b>Cant: </b>1
                    <br>
                    <b>Precio: </b> S/{{ esOrdenCompra ? $store.state.producto.PrecioCosto : $store.state.producto.PrecioContado}}
                  </b-col>
                </b-row>
              </b-card>
            </b-col>
            <div v-if=" $store.state.producto.PreciosPorProducto.length>0">
              <b-col cols="12" v-for="(precio,index) in $store.state.producto.PreciosPorProducto.filter((v)=>v.cantidad >0 )" :key="index">
                <b-card  align="center" class="mb-3 contentCard"
                 @click="actualizarProductosListado({item:precio, tipo:'PRESENTACION'})"
                 @keyup.enter="actualizarProductosListado({item:precio, tipo:'PRESENTACION'})"
                 @keyup.space="actualizarProductosListado({item:precio, tipo:'PRESENTACION'})"
                 :tabindex="index+3"
                 >
                  <b-row>
                  <b-col md="6" class="nomCart">
                    <h5 class="text-uppercase">{{precio.nombre}}</h5>
                  </b-col>
                  <b-col md="6" class="text-left">
                    <b>Cant: </b>{{precio.cantidad}}
                      <br>
                      <b>Precio: </b> S/{{ esOrdenCompra ? (precio.precioc?precio.precioc : 0) :precio.precio}}
                    </b-col>
                  </b-row>
                </b-card>
              </b-col>
            </div>
          </b-row>
        <!-- <b-col class="calculadora">-->

        </b-col> 
        <b-col cols="6" class="calculadora">
              <b-row>
                <b-col cols="12">
                  <b-input placeholder="Cantidad" class="my-2" v-model="cantProductos" type="number"
                     :state="validarCantidad" trim  tabindex="1"
                     />
                  <b-form-invalid-feedback id="input-live-feedback">
                      {{ errorCantidad }}
                  </b-form-invalid-feedback>
                  
                </b-col>
                <b-col cols="4"  v-for="(num,index) in numerosCalculadora" :key="index">
                  <b-button class="mx-1 my-1 numCalc" pill variant="outline-secondary" @click="btnCalculadora(num)">
                    <i v-if="num.value=='back'" :class="num.text" />
                    <span v-else> {{num.text}}</span>
                    
                  
                  </b-button>
                  
                </b-col>
                <b-col cols="4" >
                  <b-button class="mx-1 my-1 numCalc" pill variant="outline-secondary" @click="cantProductos=''" block>CE
                  </b-button>
                  
                </b-col>
              </b-row>
             

        </b-col>
        
      </b-row>
    </b-modal>
     <!-- <b-modal
          v-model="modalListadoProductos"
          title="Listado de Productos" hide-footer 
          striped
          hover
          class="modalLstProductos"
      >
         <b-row class="my-2">
          <b-col cols="12" class="">
             <b-input-group>
              <b-form-input  class="" v-model="search"  placeholder="Buscar en la tabla"></b-form-input>
              <b-button @click="startRecognition">
                <i class="fa fa-microphone"></i>
              </b-button>
                      
        
            <b-form-input
            v-model="codigoBarra"
            placeholder="Codigo de Barra"
            class="form-control"
          ></b-form-input>
          <b-input-group-text><strong class="text-danger">
            <i class="fa fa-barcode"></i>
            </strong></b-input-group-text>
           
           </b-input-group> 
          </b-col>
        </b-row>
         <b-table striped hover :items="filteredItems()" :fields="headerListProductos"
            :per-page="perPage"
            :current-page="currentPage"
          >
           <template slot="Imagen" slot-scope="data">
              
               <b-img style="border-radius: 10px; width:24px" fluid :src="data.item.Imagen == null || producto.Imagen==''?'static/no-photo.png': '/resources/images/productos/' + producto.Imagen" alt="" @error="setAltImg" ></b-img>	
           </template>
           <template slot="action" slot-scope="data">
              <b-button variant="success" @click="abrirModalSelect(data.item)">
                  <i class="fa fa-check"></i>
               </b-button>
            </template>
          
          
          </b-table>
          <b-input-group>

          <b-pagination
              v-model="currentPage"
              :total-rows="filteredItems().length"
              :per-page="perPage"
              first-number
              last-number
              class="mr-5"
            ></b-pagination>
            <span class="mt-1">
                Item por Page:
            </span>
            <b-input-group-text> </b-input-group-text> 
            <b-form-select v-model="perPage" :options="itemPerPage"></b-form-select>
          </b-input-group>
      </b-modal> -->
  </b-row>
</template>

<script>
import { mapState, mapActions } from "vuex";
import ProductoForm from "@/components/ProductoForm";
import BusquedaProducto from "./comun/BusquedaProducto";
import config from "@/store/config.js";

export default {
  mounted() {
    this.obtenerListadoProductos();

    // this.alterarBuscadorProductos()
  },
  data() {
    return {
      codigoBarra: "",
      abrirModal: false,
      search: "",
      rows: 100,
      perPage: 25,
      currentPage: 5,
      lstProductos: [],
      API: config.API,
      loadCode: "",
      pasteCode: false,
      modalListadoProductos: false,
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
      productoNombre: "",
      productoUrl: "",
      mensajeM2: "",
      M2: "",
      numerosCalculadora: [
        { text: "7", value: 7 },
        { text: "8", value: 8 },
        { text: "9", value: 9 },
        { text: "4", value: 4 },
        { text: "5", value: 5 },
        { text: "6", value: 6 },
        { text: "1", value: 1 },
        { text: "2", value: 2 },
        { text: "3", value: 3 },
        // { text: ".", value: "." },
        { text: "0", value: 0 },
        { text: "fa fa-arrow-left", value: "back" }
      ],
      headerListProductos: [
        {
          key: "stock",
          label: "Stock"
          // variant: "info",
          // class: "text-center"
        },
        { key: "Producto" },
        { key: "ProductoPresentacion", label: "Rack" },
        { key: "Descripcion", label: "Descripción" },
        { key: "Imagen", label: "Img" },
        { key: "action", label: "Acción" }
      ],
      cantProductos: "",
      validarCantidad: true,
      tableProductos: true,
      errorCantidad: "",
      modalEditarProducto: false,
      index: "",
      itemPerPage: [
        { value: "5", text: "5" },
        { value: "10", text: "10" },
        { value: "25", text: "25" },
        { value: "50", text: "50" },
        { value: "100", text: "100" },
        { value: "", text: "All" }
      ]
    };
  },
  watch: {
    modalNuevoProducto() {
      if (this.modalNuevoProducto) {
        this.modalCant = 0;

        setTimeout(() => {
          this.$refs.frmCantidad.focus();
        }, 500);

        //
      }
    },
    // sumatoriaTotal(sum) {
    //   this.asignarVuelto();
    // },
    productos(prods) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");

      if (pi >= 0 && !this.productos[pi].precioBruta) {
        this.productos[pi].precioBruta = this.productos[pi].precio;
      }
    }
  },
  props: {
    tipoDocSunat: String,
    isVentas: Boolean,
    opcionCarrito: Boolean,
    tipoVenta: Number,
    tipo: String,
    setPrecio: Boolean,
    tipoCambio: Number,
    igvTotal: Boolean,
    tipoMov: Number,
    idAlmacen: Number,
    bloquearInput: Boolean,
    esDelivery: Boolean,
    descCorporativoProp: Number,
    descInstitucionalProp: Number,
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
          { key: "ProductoPresentacion", label: "Rack" },
          // { key: 'ProductoMedicion', label: 'Medición' },
          //{ key: 'obsequio', label: 'Obsequio', class:'thcenter' },
          { key: "cantidad" },
          { key: "precio", label: "P/U" },
          { key: "subtotal", label: "SubTotal", variant: "success" },
          { key: "igv", label: "IGV" },
          // { key: 'flete', label: 'FLETE' },
          // { key: 'lote', label: 'Lote' },
          //{ key: 'precioVenta', label: 'Precio venta (S/)' },
          { key: "total", label: "Total", variant: "danger" },
          { key: "acciones", label: "" }
        ];
      },
      type: Array
    }
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      filtrados: state => state.cart.productosFiltrados,
      esOrdenCompra: state => state.esOrdenCompra,
      modalNuevoProducto: state => state.modalNuevoProducto
      // totalCart: state => state.cart.totalCart
    }),
    APIProductos() {
      if (this.idAlmacen) {
        return this.API + "productos/puntoventa/stock/" + this.idAlmacen;
      } else {
        return this.API + "productos";
      }
    },

    // sumatoriaTotal() {
    //   let sumatoria = 0;
    //   this.productos.map(value => {
    //     sumatoria += parseFloat(value.total);
    //   });

    //   this.actualizarTotal(sumatoria);
    //   return this.$options.filters.redondear(sumatoria);
    // },
    sumatoriaIgv() {
      let sumatoria = 0;
      if (this.igvTotal) {
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
      add: "agregarProducto",
      buscar: "buscarProductos",
      eliminar: "eliminarProducto",
      actualizarTotal: "actualizarTotalCart",
      actualizarProd: "actualizarProducto",
      obtenerStok: "obtenerStock"
    }),
    calcVuelto() {
      let vuelto = 0;
      if (this.pagocon) {
        vuelto = parseFloat(this.pagocon) - this.sumatoriaTotal();
      }
      return vuelto;
    },
    esObsequio(index) {
      let newProducto = { ...this.$store.state.cart.productos[index] };

      if (newProducto.EsObsequio == 1) {
        newProducto.precio = 0;
        newProducto.precioc = 0;
        newProducto.subtotal = 0;
        newProducto.total = 0;
      }
      if (newProducto.EsObsequio == 0) {
        if (newProducto.tipo == "PRESENTACION") {
          let presentacion = JSON.parse(newProducto.presentacion);

          newProducto.precio = presentacion.precio;
          newProducto.precioc = presentacion.precioc;

          newProducto.cantxPrecio = newProducto.cantxPrecio;
          newProducto.cantidad =
            newProducto.cantxPrecio * presentacion.cantidad;
          newProducto.subtotal =
            (this.$store.state.esOrdenCompra
              ? presentacion.precioc
              : presentacion.precio) * newProducto.cantxPrecio;
          newProducto.total =
            (this.$store.state.esOrdenCompra
              ? presentacion.precioc
              : presentacion.precio) * newProducto.cantxPrecio;
        }
        if (newProducto.tipo == "UNIDAD") {
          newProducto.cantxPrecio = 0;
          newProducto.precio = newProducto.PrecioContado;

          newProducto.precioc = newProducto.PrecioCosto;
          newProducto.subtotal =
            newProducto.cantidad * newProducto.PrecioContado;
          newProducto.total = newProducto.cantidad * newProducto.PrecioContado;
        }
      }

      this.$store.state.cart.productos[index] = newProducto;
      this.tableProductos = false;
      setTimeout(() => {
        this.tableProductos = true;
      }, 1);
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
          this.search = event.results[0][0].transcript;
          // this.search();
        }
      };

      this.recognition.onerror = event => {
        console.error("Speech Recognition Error:", event);
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = async () => {
        console.log("Speech recognition service disconnected");
        console.log("query", this.query);
        // if (this.query) {
        //   alert(`Dijiste: ${this.query}`);
        // } else {
        //   alert("No se pudo captar ninguna palabra");
        // }
      };

      try {
        this.recognition.start();
      } catch (error) {
        console.error("Error starting recognition:", error);
        // alert("No se pudo iniciar el reconocimiento de voz.");
      }
    },
    // async abrirModalListProducto() {
    //   await this.obtenerListadoProductos();
    //   this.modalListadoProductos = !this.modalListadoProductos;
    //   this.abrirModal = true;
    // },

    abrirModalSelect(item) {
      // this.lstProductos = [];
      this.cantProductos = "";
      let producto = { ...item };
      console.log(producto);
      if (
        this.$store.state.cart.productos.some(
          v => v.IdProducto == producto.IdProducto
        )
      ) {
        this.$toasted.error(
          `El producto ${producto.Producto} ya se encuentra en la lista`
        );
        return false;
      }

      if (producto.PreciosPorProducto) {
        producto.PreciosPorProducto = JSON.parse(producto.PreciosPorProducto);
        producto.estadoPxP = false;
        producto.tienepresentacionflag = true;
      } else {
        producto.estadoPxP = true;
        producto.PreciosPorProducto = [];
        producto.tienepresentacionflag = false;
      }

      producto.cantidad = 1;
      producto.cantxPrecio = 0;
      producto.subtotal =
        (this.$store.state.esOrdenCompra
          ? producto.PrecioCosto
          : producto.PrecioContado) * 1;
      producto.total =
        (this.$store.state.esOrdenCompra
          ? producto.PrecioCosto
          : producto.PrecioContado) * 1;
      producto.precio = producto.PrecioContado;
      producto.precioc = producto.PrecioCosto;
      producto.tipo = "UNIDAD";
      producto.descuento = 0;
      this.$store.commit("AGREGAR_PRODUCTO", producto);
      //this.$store.state.cart.productos.unshift(producto);
      this.search = "";
      // this.$store.state.modalNuevoProducto = true;
    },
    filteredItems() {
      if (this.codigoBarra) {
        return this.lstProductos.filter(v => v.CodigoBarra == this.codigoBarra);
      }
      return this.lstProductos.filter(item => {
        return Object.values(item).some(val =>
          String(val)
            .toLowerCase()
            .includes(this.search.toLowerCase())
        );
      });
    },
    async obtenerListadoProductos() {
      let config = {
        url: `${
          this.API
        }/productos/puntoventa/stock/1?q=&sortBy=stock&sortDesc=1&limit=&noLimit=true`,
        method: "get"
      };
      await this.axios(config).then(response => {
        let res = response;
        if (res.data === false) {
          this.lstProductos = [];
        } else {
          this.lstProductos = res.data;
        }
      });
    },
    btnCalculadora(item) {
      this.validarCantidad = true;
      this.errorCantidad = "";
      if (item.value != "back") {
        let decimal = ".";
        if (item.value === decimal) {
          if (this.cantProductos.includes(decimal)) {
            return;
          }
        }
        this.cantProductos += item.value;
      } else {
        this.cantProductos = this.cantProductos.slice(
          0,
          this.cantProductos.length - 1
        );
      }
    },

    // anadirProductosListado({ item = null, tipo = "UNIDAD" }) {
    //   this.validarCantidad = true;
    //   this.errorCantidad = "";
    //   if (!this.cantProductos || this.cantProductos <= 0) {
    //     this.validarCantidad = false;
    //     this.errorCantidad = "Seleccione una cantidad";
    //     this.$refs.frmCantidad.focus();
    //     return false;
    //   }
    //   let producto = this.$store.state.producto;
    //   if (tipo == "PRESENTACION") {
    //     let precio = this.$store.state.esOrdenCompra ? item.precioc : item.precio;

    //     producto.cantidad = this.cantProductos;
    //     producto.cantxPrecio = this.cantProductos;
    //     producto.cantidadPres = item.cantidad;
    //     producto.precio = precio ? precio : 0;
    //     producto.subtotal = (precio ? precio : 0) * this.cantProductos;
    //     producto.total = (precio ? precio : 0) * this.cantProductos;
    //     producto.disabled = true;
    //     producto.tipo = "PRESENTACION";
    //   }
    //   if (tipo == "UNIDAD") {
    //     let precio = this.$store.state.esOrdenCompra
    //       ? producto.PrecioCosto
    //       : producto.PrecioContado;
    //     producto.cantidad = this.cantProductos;
    //     producto.cantxPrecio = this.cantProductos;

    //     producto.subtotal = precio * this.cantProductos;
    //     producto.total = precio * this.cantProductos;
    //     producto.precio = precio;
    //     producto.tipo = "UNIDAD";
    //   }
    //   // actualizarProd
    //   // this.actualizarProd(producto);
    //   producto.descuento = 0;
    //   this.$store.state.cart.productos.push(producto);
    //   this.$store.state.modalNuevoProducto = false;
    //   this.modalListadoProductos = this.abrirModal;
    // },
    actualizarProductosListado({ item = null, tipo = "UNIDAD" }) {
      this.validarCantidad = true;
      this.errorCantidad = "";
      if (!this.cantProductos || this.cantProductos <= 0) {
        this.validarCantidad = false;
        this.errorCantidad = "Seleccione una cantidad";
        this.$refs.frmCantidad.focus();
        return false;
      }
      let producto = this.$store.state.producto;
      // console.log(producto);
      if (tipo == "PRESENTACION") {
        let presentacion = JSON.stringify(item);
        producto.presentacion = presentacion;
        producto.cantidad = this.cantProductos * item.cantidad;
        producto.cantxPrecio = this.cantProductos;
        producto.cantidadPres = item.cantidad;
        producto.precio = item.precio ? item.precio : 0;
        producto.precioc = item.precioc ? item.precioc : 0;
        producto.subtotal = this.$store.state.esOrdenCompra
          ? (item.precioc ? item.precioc : 0) * this.cantProductos
          : (item.precio ? item.precio : 0) * this.cantProductos;
        producto.total = this.$store.state.esOrdenCompra
          ? (item.precioc ? item.precioc : 0) * this.cantProductos
          : (item.precio ? item.precio : 0) * this.cantProductos;
        producto.disabled = true;
        producto.tipo = "PRESENTACION";
      }
      if (tipo == "UNIDAD") {
        producto.presentacion = "";

        producto.cantidad = this.cantProductos;
        producto.cantxPrecio = 0;
        producto.disabled = false;
        producto.subtotal =
          (this.$store.state.esOrdenCompra
            ? producto.precioc
            : producto.precio) * this.cantProductos;
        producto.total =
          (this.$store.state.esOrdenCompra
            ? producto.precioc
            : producto.precio) * this.cantProductos;
        producto.precio = producto.PrecioContado;
        producto.precioc = producto.PrecioCosto;
        producto.tipo = "UNIDAD";
      }
      // actualizarProd
      // this.actualizarProd(producto);
      this.$store.state.cart.productos[this.index] = producto;
      this.modalEditarProducto = false;
      this.tableProductos = false;
      this.tableProductos = true;
    },

    abrirCalcularM2(obj) {
      this.setProducto(obj);
      if (!this.isVentas) {
        return;
      }
      if (obj.Rendimiento && obj.Ancho && obj.Largo) {
        this.$refs.modalCalcularM2.show();
      }
      this.productoSelect = obj;
      this.productoSelect.cantidad = 1;
      this.mensajeM2 = "";
      this.$refs.inputM2.value = "";
    },
    calcularm2(
      m2,
      rendimiento = 0,
      ancho = 0,
      largo = 0,
      productoDetCantidad = 0
    ) {
      rendimiento = rendimiento == "0" ? false : rendimiento;
      ancho = ancho == "0" ? false : ancho;
      largo = largo == "0" ? false : largo;
      if ((!rendimiento && !ancho) || (!rendimiento && !largo)) {
        alert(
          "El producto no cuenta con un rendimiento en M2, un ancho o largo establecido"
        );
        return;
      }
      var cantidad = 0;
      var mensaje = 0;
      // console.log(rendimiento)
      if (rendimiento && rendimiento != "0") {
        cantidad = (m2 / rendimiento).toFixed(1);
        cantidad = Math.ceil(cantidad);
        mensaje = "Total de cajas requeridas: " + cantidad;
      } else {
        // console.log(ancho)
        // console.log(largo)
        cantidad = (parseFloat(m2) / ((ancho / 100) * (largo / 100))).toFixed(
          1
        );
        cantidad = Math.ceil(cantidad);
        mensaje = "Total de unidades requeridas: " + cantidad;
      }
      if (cantidad > 0) {
        var mensajeAdicional = "";
        if (productoDetCantidad) {
          if (cantidad > productoDetCantidad) {
            var cajas = Math.floor(cantidad / productoDetCantidad);
            var unidades = cantidad % productoDetCantidad;
            mensajeAdicional += "(Cajas: " + cajas;
            if (unidades > 0) {
              mensajeAdicional += ", Unidades: " + unidades + ")";
            } else {
              mensajeAdicional += ")";
            }
          }
        }
        this.mensajeM2 = mensaje;
        this.mensajeM2 += " <br /> " + mensajeAdicional;
        this.productoSelect.cantidad = cantidad;
      }

      if (!m2) {
        this.mensajeM2 = "";
        this.productoSelect.cantidad = 1;
      }
    },
    seleccionarCalcularM2() {
      this.$refs.modalCalcularM2.hide();

      this.productoSelect.cantidad = this.productoSelect.cantidad || 1;
      // this.setProducto(this.productoSelect)
    },
    abrirImagen(producto, url) {
      this.productoNombre = producto;
      this.productoUrl = url;
      this.$refs["modalImagen"].show();
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    cambiarCantidadPresentacion(producto, index, tipo = "UNIDAD") {
      let prod = { ...producto };
      if (tipo == "PRESENTACION") {
        let precio = prod.precio;
        let precioc = prod.precioc;
        let presentacion = JSON.parse(producto.presentacion);
        // console.log(presentacion);
        // newProducto.cantxPrecio * presentacion.cantidad
        prod.cantidad = prod.cantxPrecio * presentacion.cantidad;
        prod.subtotal =
          prod.cantxPrecio *
          (this.$store.state.esOrdenCompra
            ? presentacion.precioc
            : presentacion.precio);

        prod.total =
          prod.cantxPrecio *
          (this.$store.state.esOrdenCompra
            ? presentacion.precioc
            : presentacion.precio);
      }
      if (tipo == "UNIDAD") {
        prod.cantxPrecio = 0;
        // let precio = producto.PrecioContado;
        prod.subtotal =
          prod.cantidad *
          (this.$store.state.esOrdenCompra
            ? producto.PrecioCosto
            : producto.PrecioContado);

        prod.total =
          prod.cantidad *
          (this.$store.state.esOrdenCompra
            ? prod.PrecioCosto
            : prod.PrecioContado);
      }
      this.productos[index] = prod;

      setTimeout(() => {
        this.tableProductos = false;
        this.tableProductos = true;
      }, 100);
      // this.recalcularTotal();
    },
    cambiarPresentacion(prod, index) {
      this.$store.state.producto = prod;
      this.index = index;
      this.cantProductos = "1";
      this.modalEditarProducto = true;
    },
    sumatoriaSubTotal() {
      let sum = this.productos.reduce((sum, pro) => {
        return (
          sum +
          parseFloat(pro.subtotal) -
          parseFloat(
            (pro.subtotal * (pro.PorcentajeDesc ? pro.PorcentajeDesc : 0)) / 100
          )
        );
      }, 0);

      return parseFloat(sum).toFixed(2);
    },
    sumatoriaTotal() {
      let sum = this.productos.reduce((sum, pro) => {
        return (
          sum +
          parseFloat(pro.total) -
          parseFloat(
            (pro.total * (pro.PorcentajeDesc ? pro.PorcentajeDesc : 0)) / 100
          )
        );
      }, 0);
      // console.log(sum);
      this.$store.state.cart.totalCart = parseFloat(sum).toFixed(2);
      // console.log(this.totalCart);
      return parseFloat(sum).toFixed(2);
    },
    changeCant(val, prod, index) {
      this.$nextTick(() => {
        if (prod.PorcentajeDesc > 0) {
          this.productos[index].descuento =
            Math.round(
              ((this.getSubtotal(index) * prod.PorcentajeDesc) / 100) * 10
            ) / 10;
        }
        this.actualizarProd(prod);
        this.getSubtotal(index);
      });
    },

    cambiarPrecio(index, prod) {
      var utilidad =
        this.productos[index].PrecioCosto *
        (this.productos[index].PorcentajeUtilidad / 100);
      this.productos[index].precio = Math.ceil(
        this.productos[index].PrecioCosto + utilidad
      );
    },

    addManoDeObra(index, e) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");
      if (pi < 0) {
        this.$toasted.error("Primero seleccione el producto MANODEOBRA");

        this.productos[index].EsManoDeObra = 0;
        return;
      }

      if (this.productos[index].EsManoDeObra) {
        e.target.parentNode.parentNode.style.backgroundColor = "#e7c4ff";
        this.productos[pi].precio =
          parseFloat(this.productos[pi].precio) -
          parseFloat(this.productos[index].precio) *
            parseFloat(this.productos[index].cantidad);
        this.productos[index].disabled = true;
      } else {
        e.target.parentNode.parentNode.removeAttribute("style");
        this.productos[pi].precio = 0;
        this.productos[pi].precio =
          parseFloat(this.productos[pi].precio) +
          parseFloat(this.productos[index].precio) *
            parseFloat(this.productos[index].cantidad);
        this.productos[index].disabled = false;
      }
    },

    updateManoDeObraNeta(index) {
      let pManoObra = this.productos.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.precioBruta : 0;

      this.productos.filter(p => p.EsManoDeObra).map(p => {
        precio -= parseFloat(p.precio) * parseFloat(p.cantidad);
      });

      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");

      if (pi >= 0 && index !== pi) {
        this.productos[pi].precio = precio;
      }
    },

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

    async loadBarcode(e) {
      e.preventDefault();
      let idProducto = this.loadCode;

      if (idProducto) {
        let params = { codigoBarra: idProducto };
        await this.axios
          .get(
            `${this.API}/productos/puntoventa/stock/1?q=${
              this.loadCode
            }&sortBy=stock&sortDesc=1&limit=1`
          )
          .then(response => {
            const prod = response.data[0];
            // console.log(prod);
            if (prod.IdProducto) {
              if (this.productos.some(v => v.IdProducto == prod.IdProducto)) {
                console.log(prod);
                let prodNew = this.productos.filter(
                  v => v.IdProducto == prod.IdProducto
                )[0];
                if (prodNew.tipo == "UNIDAD") {
                  prodNew.cantxPrecio = 0;
                  prodNew.cantidad++;
                  prodNew.subtotal = prodNew.cantidad * prodNew.precio;
                  prodNew.total = prodNew.cantidad * prodNew.precio;
                  this.productos.filter(
                    v => v.IdProducto == prodNew.IdProducto
                  )[0] = prodNew;
                }
                if (prodNew.tipo == "PRESENTACION") {
                  prodNew.cantidad++;
                  prodNew.cantxPrecio++;
                  prodNew.subtotal = prodNew.cantxPrecio * prodNew.precio;
                  prodNew.total = prodNew.cantxPrecio * prodNew.precio;
                  this.productos.filter(
                    v => v.IdProducto == prodNew.IdProducto
                  )[0] = prodNew;
                }
                this.tableProductos = false;
                this.tableProductos = true;
              } else {
                this.abrirModalSelect(prod);
              }
            } else {
              this.loadCode = "";
              this.$toasted.show("No se encontró ningun producto");
            }
          });
        this.loadCode = "";
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
    setObsequio(index) {
      this.productos[index].precio = 0;
      this.productos[index].nuevoPrecioContado = 0;
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    editarProducto(Pro) {
      this.productoSelect = Pro;
      this.$refs.productoFormEdit.show = true;
      this.$refs.productoFormEdit.nuevo = false;
    },
    agregarGuardar(id, pxp, obj) {
      const index = this.$store.state.cart.productos
        .map((producto, index) => ({ producto, index }))
        .filter(v => v.producto.IdProducto == id)
        .map(v => v.index)[0];

      console.log(obj);

      let prod = this.$store.state.cart.productos[index];
      prod.tienepresentacionflag = !pxp.some(v => v.cantidad > 0);
      prod.estadoPxP = !pxp.some(v => v.cantidad > 0);
      prod.PrecioContado = obj.PrecioContado;
      prod.precio = obj.PrecioContado;
      prod.cantxPrecio = 0;
      prod.PrecioCosto = obj.PrecioCosto;
      prod.precioc = obj.PrecioCosto;
      prod.Producto = obj.Producto;
      prod.ProductoPresentacion = obj.ProductoPresentacion;
      prod.PreciosPorProducto = pxp;
      prod.ProductoMedicion = obj.medicion.ProductoMedicion;
      prod.IdProductoMedicion = obj.medicion.IdProductoMedicion;
      prod.IdProductoMarca = obj.marca.IdProductoMarca;
      prod.ProductoMarca = obj.marca.ProductoMarca;
      prod.IdProductoCategoria = obj.categoria.IdProductoCategoria;
      prod.ProductoCategoria = obj.categoria.ProductoCategoria;
      prod.disabled = false;
      prod.tipo = "UNIDAD";
      prod.subtotal =
        prod.cantidad *
        (this.$store.state.esOrdenCompra ? obj.PrecioCosto : obj.PrecioContado);
      prod.total =
        prod.cantidad *
        (this.$store.state.esOrdenCompra ? obj.PrecioCosto : obj.PrecioContado);
      this.$store.state.cart.productos[index] = prod;
      this.tableProductos = false;
      this.tableProductos = true;
      // this.add([id, false, false, false, false, true, this.isVentas]);
      // setTimeout(() => {
      //   this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
      // }, 200);

      // if (this.isVentas) {
      //   let fPP = pxp.filter(p => p.cantidad === 0 || p.cantidad === "0");
      //   if (fPP.length != 4) {
      //     obj.ProductoMedicion = obj.medicion.ProductoMedicion;
      //     obj.IdProductoMedicion = obj.medicion.IdProductoMedicion;
      //     obj.IdProductoMarca = obj.marca.IdProductoMarca;
      //     obj.ProductoMarca = obj.marca.ProductoMarca;
      //     obj.IdProductoCategoria = obj.categoria.IdProductoCategoria;
      //     obj.ProductoCategoria = obj.categoria.ProductoCategoria;
      //     this.productoSelect = obj;
      //     this.productoSelect.PreciosPorProducto = JSON.stringify(pxp);
      //     this.showPresentacion(this.productoSelect);
      //   }
      // }
    },
    setProducto(obj) {
      this.precioCont = obj.PrecioContado;
      obj.estadoPxP = false;
      this.loadCode = "";
      this.preciosProd = [];
      this.objProd = {};
      obj.EsObsequio = 0;
      obj.EsVenta = this.isVentas;

      if (!this.isVentas) {
        obj.estadoPxP = true;
        obj.PreciosPorProducto = "";
        this.setProductoUni(obj);
        return;
      }
      this.preciosProd.push({
        nombre: "P. Pub.",
        cantidad: 1,
        precio: obj.PrecioContado
      });
      if (obj.precioConvenio) {
        this.preciosProd.push({
          nombre: "P. Dis.",
          cantidad: 1,
          precio: obj.precioConvenio
        });
      }
      if (obj.PrecioEspecial) {
        this.preciosProd.push({
          nombre: "P. Esp",
          cantidad: 1,
          precio: obj.PrecioEspecial
        });
      }

      if (this.preciosProd.length == 1) {
        this.setProductoUni(obj, obj.PrecioContado);
        return;
      }

      this.objProd = {};

      // this.preciosProd = JSON.parse(obj.PreciosPorProducto || '[]')
      // let fPP = this.preciosProd.filter(p => p.cantidad === 0)

      this.$refs["modalCant"].show();
      // this.preciosProd = JSON.parse(obj.PreciosPorProducto)
      this.objProd = obj;
    },
    setProductoUni(obj, precio) {
      this.objProd.cantxPrecio = 0;
      this.$refs.autocompleteProducto.setValue("");
      //obj.FLETE = 0
      obj.precio = 0;
      obj.cantidad = 0;
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj = this.onSetProducto(obj, false, precio);
      this.add([
        obj,
        () => {
          this.$emit("onAddProducto", obj);
          this.$toasted.success("Producto añadido correctamente");
        },
        { setPrecioCosto: this.tipo == "mov" ? true : false },
        false,
        this.cliente.EsVIP
      ]);
      // Vue.set(this.productos, obj.IdProducto, obj)
      this.$refs["modalCant"].hide();
      obj.cantidadPres = obj.cantidad;
      this.actualizarProd(obj);
    },
    setProductoCant(prod) {
      this.objProd.cantxPrecio = 0;
      this.cant = prod.cantidad;
      let obj = this.objProd;
      this.$refs.autocompleteProducto.setValue("");
      //obj.FLETE = 0
      // this.objProd.precio = 0
      // this.objProd.cantidad = 0
      obj.precio = 0;
      obj.cantidad = 0;
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj = this.onSetProducto(obj, prod.cantidad, prod.precio);
      this.add([
        obj,
        () => {
          this.$emit("onAddProducto", obj);
          this.$toasted.success("Producto añadido correctamente");
        },
        false,
        false,
        this.cliente.EsVIP
      ]);
      this.$refs["modalCant"].hide();
      obj.cantidadPres = this.cant;
      this.actualizarProd(obj);
    },
    showPresentacion(obj) {
      if (this.isVentas) {
        this.preciosProd = [];
        this.preciosProd.push({
          nombre: "P. Pub.",
          cantidad: 1,
          precio: obj.PrecioContado
        });
        if (obj.precioConvenio) {
          this.preciosProd.push({
            nombre: "P. Dis.",
            cantidad: 1,
            precio: obj.precioConvenio
          });
        }
        if (obj.PrecioEspecial) {
          this.preciosProd.push({
            nombre: "P. Esp.",
            cantidad: 1,
            precio: obj.PrecioEspecial
          });
        }
        this.objProd = obj;
        this.$refs["modalCant"].show();
        return;
      }
    },
    renderChild(data) {
      if (this.idAlmacen) {
        let styleStock = "";
        if (data.stock <= 0) {
          styleStock = "table-danger";
        } else {
          styleStock = "table-success";
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
        </style>
        <div>
          <table id="table-render" style="width: 830px; text-align: center  ">
            <thead>
              <tr style>
                <th class="table-info" style="width: 60px">Stock</th>
                <th style="width: 200px">Producto</th>
                <th style="width: 100px">Categoría</th>
                <th style="width: 100px">Marca</th>
                <th class="table-primary" style="width: 65px">Precio</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="${styleStock}"><b>${
          data.EsPadre == 1
            ? '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />'
            : data.stock
              ? Number.isInteger(data.stock)
                ? data.stock
                : data.stock.toFixed(2)
              : data.stock
        }</b></td>
                <td><b>${data.Producto} - ${data.CodigoBarra}</b></td>
                <td>${data.ProductoCategoria}</td>
                <td>${data.ProductoMarca}</td>
                <td class="table-primary"><b>${data.PrecioContado}</b></td>
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
    // setCantidad (index, val) {
    //   this.$emit('onInputCantidad', val)
    // },
    recalcularPrecioCompra(index) {
      let prod = this.$store.state.cart.productos[index];
      if (prod.tipo == "UNIDAD") {
        prod.cantxPrecio = 0;
        prod.precioc = parseFloat(prod.subtotal / prod.cantidad).toFixed(2);
        prod.total = prod.subtotal;
      }
      if (prod.tipo == "PRESENTACION") {
        let presentacion = JSON.parse(prod.presentacion);
        prod.precioc = parseFloat(
          prod.subtotal / presentacion.cantidad
        ).toFixed(2);
        prod.total = prod.subtotal;
      }
      this.$store.state.cart.productos[index] = prod;
      this.tableProductos = false;
      this.tableProductos = true;
    },
    recalcularTotal(index) {
      let prod = this.$store.state.cart.productos[index];
      if (prod.tipo == "UNIDAD") {
        prod.cantxPrecio = 0;
        prod.subtotal =
          prod.cantidad *
          (this.$store.state.esOrdenCompra ? prod.precioc : prod.precio);
        prod.total =
          prod.cantidad *
          (this.$store.state.esOrdenCompra ? prod.precioc : prod.precio);
      }
      if (prod.tipo == "PRESENTACION") {
        let presentacion = JSON.parse(prod.presentacion);

        prod.subtotal =
          prod.cantxPrecio *
          (this.$store.state.esOrdenCompra ? prod.precioc : prod.precio);
        prod.total =
          prod.cantxPrecio *
          (this.$store.state.esOrdenCompra ? prod.precioc : prod.precio);
      }
      this.$store.state.cart.productos[index] = prod;
      this.tableProductos = false;
      this.tableProductos = true;
    },
    setNuevoPrecioContado(index) {
      // Añadir IGV si check IGV
      let precio = parseFloat(this.productos[index].precio);
      if (this.productos[index].TieneIgv) {
        precio += precio * 0.18;
      }
      let precioFinal = 0;
      //if(this.isVentas){
      //precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      // let precioFinal =
      // }else{
      precioFinal =
        (precio * this.productos[index].PorcentajeUtilidad) / 100 + precio;
      // precio = (precio / (1-this.productos[index].PorcentajeUtilidad/100))
      //}

      //precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      //precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      // let precio = parseFloat(this.productos[index].precio) + (this.productos[index].precio * (this.productos[index].PorcentajeUtilidad/100))

      let normalPrecio = this.$options.filters.redondear(precioFinal);
      //let nuevoPrecio = this.$options.filters.redondeoMayor(precio)
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

      // this.productos[index].nuevoPrecioContado = nuevoPrecio
      // return nuevoPrecio
      this.updateManoDeObraNeta(index);
    },
    actualizarDescu(index) {
      let obj = this.productos[index];
      this.actualizarProd(obj);
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
      let total = parseFloat(this.productos[index].subtotal);

      if (this.productos[index].FLETE) {
        total += parseFloat(this.productos[index].FLETE);
      }
      if (this.productos[index].TieneIgv) {
        total += parseFloat(this.productos[index].igv);
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
      this.$store.commit("ELIMINAR_PRODUCTO", index);
      // this.eliminar(index);
      // this.$emit("onRemoveProducto", index);
      this.$toasted.show("Producto quitado del listado");
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
    ProductoForm,
    BusquedaProducto
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
</style>
<style>
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
#modalImagen .modal-dialog {
  max-width: 75% !important;
}
.imagenGrande {
  width: 100%;
}
.obsequio {
  display: flex;
  justify-content: center;
  justify-items: center;
}
.btn-anadir {
  max-height: 38px;
}
.txt-codigobarra {
  max-height: 38px;
  border-radius: 5px !important;
}
.numCalc {
  width: 50px;
  height: 50px;
}
.calculadora {
  border-left: dashed 1px !important;
}
.modalLstProductos .modal-dialog {
  max-width: 70%;
}
</style>
