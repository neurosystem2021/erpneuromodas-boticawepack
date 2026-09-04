<template>
  <b-row>
    <b-col md="12">
      <b-form-group>
        <b-input-group>
          <autocomplete
            ref="autocompleteProducto"
            :url="APIProductos"
            :customParams="{
              sortBy: isVentas ? 'stock' : 'IdProducto',
              sortDesc: 1,
              limit: '25',
              id_empresa: empresa ,
              IdAlmacen : idAlmacen
            }"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder="Agregar Producto"
            :on-select="anadirarProductosSeleccionado"
            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
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
              @producto-editado="actualizarProductoEnCarrito" 
            ></producto-form>
          </b-input-group-append>
        </b-input-group>
        <span class="ml">Búsqueda Avanzanda (Producto,Categoría,Marca)</span>
        <br>
        <br>

        <div v-if="isVentas" class="toolbarProductos d-flex align-items-center flex-wrap mb-2">
          <b-form-checkbox
            switch
            v-model="panelProductosActivo"
            class="switchMultiempresa mr-3"
          >
            <b>Búsqueda multiempresa</b>
          </b-form-checkbox>
          <small class="text-muted" v-if="panelProductosActivo">
            Haz clic en "Búsqueda Producto" para ver el listado desplegable
          </small>
        </div>
      </b-form-group>

      <!--
        BÚSQUEDA MULTIEMPRESA — estilo dropdown/autocomplete.
        Antes era un <b-modal>, luego un panel estático embebido.
        Ahora: la barra de búsqueda queda fija en el POS, y el listado
        de resultados se muestra como un panel flotante (dropdown) que
        aparece al enfocar/escribir en el input y se cierra al hacer
        clic fuera, con Esc, o con el botón de cerrar.
      -->
      <div
        v-if="panelProductosActivo"
        class="productosDropdownWrapper"
        ref="panelProductosWrapper"
        @keydown.esc="mostrarDropdownProductos = false"
      >
        <b-input-group class="quickSearchBar">
          <input
            ref="inputProducto"
            v-model="searchProducto"
            @input="onSearchProducto(); mostrarDropdownProductos = true;"
            @focus="onFocusBusquedaProductos"
            placeholder="Búsqueda Producto (multiempresa)"
            class="form-control inputResponsive"
            id="searchProducto"
          >
          <b-button v-if="searchProducto" @click="limpiarBuscar" variant="warning"> X </b-button>
          <b-input-group-append>
            <b-button @click="startRecognition()" variant="info">
              <i class="fas fa-microphone"></i>
            </b-button>
          </b-input-group-append>
          <input
            v-model="searchSoloBarras"
            @input="onSearchBarras(); mostrarDropdownProductos = true;"
            @focus="onFocusBusquedaProductos"
            placeholder="Buscar por Codigo de Barra"
            class="form-control"
          />
          <input
            v-model="CodigoBarra"
            @input="onInputCodigoBarra"
            @focus="onFocusBusquedaProductos"
            type="text"
            class="form-control ml-2 inputResponsive"
            placeholder="Busqueda rápida por Codigo de Barra"
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

        <transition name="fade-slide">
          <div v-show="mostrarDropdownProductos" class="productosDropdownPanel">
            <div class="productosDropdownHeader">
              <span><i class="fas fa-boxes"></i> Productos multiempresa ({{ lstProductosRapido.length }})</span>
              <b-button size="sm" variant="link" class="closeDropdownBtn" @click="mostrarDropdownProductos = false">
                <i class="fas fa-times"></i>
              </b-button>
            </div>
            <div class="tblModalInline">
              <table class="table table-hover table-sm mb-0" width="100%">
                <thead>
                  <tr>
                    <th class="py-1"></th>
                    <th class="py-1">Códpreferencial</th>
                    <th class="py-1">Producto</th>
                    <th class="py-1">Marca</th>
                    <th class="py-1">Categoria</th>
                    <th class="py-1">Empresa</th>
                    <th class="py-1">Precio</th>
                    <th class="py-1">Por Mayor</th>
                    <th class="py-1">Por Volumen</th>
                    <th class="py-1">Imagen</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(prod,index) in lstProductosRapido" :key="index"
                    :class="[
                      selectedProducto && selectedProducto.IdProducto === prod.IdProducto ? 'fila-activa' : ''
                    ]"
                    @click="seleccionarProducto(prod)"
                    @dblclick="agregarDirecto(prod)"
                  >
                    <td class="py-1">
                      <b-button size="sm" variant="success" class="btnAddProd" @click.stop="anadirarProductosSeleccionado(prod,true)">
                        <i class="fas fa-plus"></i>
                      </b-button>
                    </td>
                    <td class="py-1">{{ prod.CodigoBarra }}</td>
                    <td class="py-1">{{ prod.Producto }}</td>
                    <td class="py-1">{{ prod.ProductoMarca }}</td>
                    <td class="py-1">{{ prod.ProductoCategoria }}</td>
                    <td class="py-1"><span :class="`${getSyle(prod.Empresa)} empresaChip`">{{ prod.NombreEmpresa }}</span></td>
                    <td class="py-1 text-center font-weight-bold">{{ parseFloat(prod.PrecioContado?prod.PrecioContado : 0).toFixed(2) }}</td>
                    <td class="py-1 text-center">{{ parseFloat(prod.precioConvenio?prod.precioConvenio : 0).toFixed(2) }}</td>
                    <td class="py-1 text-center">{{ parseFloat(prod.precioBloque?prod.precioBloque : 0).toFixed(2) }}</td>
                    <td class="py-1">
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
                  <tr v-if="lstProductosRapido.length === 0">
                    <td colspan="10" class="text-center text-muted py-4">
                      Sin resultados. Escribe para buscar…
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="productosDropdownFooter">
              <span>Stock Total Por Empresa</span>
              <b>{{ lstProductoStock.reduce((sum,prod)=>{return sum + parseFloat(prod.stock)},0) }}</b>
            </div>
          </div>
        </transition>
      </div>

      <b-modal size="lg" ref="modalCant" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration: underline">{{ objProd.Producto }}</h3>
          <h5>Presentaciones</h5>
        </div>
        <hr />
        <b-row>
          <b-col cols="6">
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
                  <br />
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
              v-bind:class="[d.nombre == 'OFERTA' ? 'oferta' : '']"
              @click="
                () => {
                  if (parseFloat(d.cantidad)) {
                    setProductoCant(d);
                  }
                }
              "
            >
              <b-row>
                <b-col
                  md="6"
                  class="nomCart"
                  v-bind:class="[d.nombre == 'OFERTA' ? 'ofertanom' : '']"
                >
                  <h5 class="text-uppercase">{{ d.nombre }}</h5>
                </b-col>
                <b-col md="6" class="text-left">
                  <b>Cant: </b>{{ d.cantidad }}
                  <br />
                  <b>Precio: </b>S/ {{ d.precio }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>
        </b-row>
      </b-modal>

      <b-modal
        id="modalImagen"
        size="lg"
        ref="modalImagen"
        hide-footer
        hide-header
      >
        <div class="d-block text-center">
          <h3 style="text-decoration: underline">{{ productoNombre }}</h3>
        </div>
        <b-row
          style="display: flex; justify-content: center; align-items: center"
        >
          <b-img :src="productoUrl" class="imagenGrande"></b-img>
        </b-row>
      </b-modal>

       <b-table
          small
          responsive
          striped
          hover
          :items="productos"
          :fields="fields"
          ref="tablaCarrito"
          style="margin-top: -12px"
          v-if="mostrarTablaProductos"
          class="tblCarrrito"
        >
        <template slot="eventPres" slot-scope="data">
          <b-button
            size="sm"
            v-if="productos[data.index].estadoPxP == false"
            :disabled="bloquearInput ? true : false"
            variant="info"
            @click="showPresentacion(productos[data.index],data.index)"
            >
            <i class="fas fa-list"></i>
            </b-button>
          <b-button
            @click="
              abrirImagen(
                productos[data.index].Producto,
                productos[data.index].Imagen == null ||
                  productos[data.index].Imagen == ''
                  ? 'static/no-photo.png'
                  : '/resources/images/productos/' +
                      productos[data.index].Imagen
              )
            "
            style="margin-top: 2px"
            :id="'popover-imagen' + productos[data.index].IdProducto"
            variant="success"
            size="sm"
            ><i class="fas fa-image"></i
          ></b-button>
          <b-popover
            placement="right"
            :target="'popover-imagen' + productos[data.index].IdProducto"
            :title="productos[data.index].Producto"
            triggers="hover"
          >
            <div style="text-align: center">
              <b-img
                style="border-radius: 10px; width: 200px"
                fluid
                :src="
                  productos[data.index].Imagen == null ||
                  productos[data.index].Imagen == ''
                    ? 'static/no-photo.png'
                    : '/resources/images/productos/' +
                      productos[data.index].Imagen
                "
                alt=""
                @error="setAltImg"
              ></b-img>
            </div>
          </b-popover>
        </template>

        <template slot="obsequio" slot-scope="data">
          <div class="obsequio" v-if="tipoMov == 0">
            <b-form-checkbox
              button-variant="primary"
              v-model="productos[data.index].EsObsequio"
              :value="1"
              @input="setObsequio(data.index)"
              :unchecked-value="0"
            >
            </b-form-checkbox>
          </div>
        </template>

        <template
          slot="cantxPrecio"
          slot-scope="data"
          v-if="productos[data.index].tipo == 'PRESENTACION'"
        >
          <input
            v-model="productos[data.index].cantxPrecio"
            type="number"
            style="width: 80px"
            step="0.001"
            min="0"
            :disabled="bloquearInput ? true : false"
            @input="
              (val) => changeCant(val.target.value, productos[data.index])
            "
            placeholder="Cantidad"
            class="form-control"
          />
          <span>x{{ productos[data.index].cantidadPres }}</span>
        </template>

        <template slot="NombreEmpresa" slot-scope="data">
          <div :class="`${getSyle(data.item.Empresa)} vChip`">
            {{ data.item.NombreEmpresa }}
          </div> 
          </template>
        <template slot="Serie" slot-scope="data">
          <ul>
            <li v-for="(serie,index) in generarSerie(data.item.productoSerie)" :key="index">
              {{ serie.Serie }}
            </li>
          </ul>
        </template>
        <template slot="Series" slot-scope="data">
          <b-select 
              v-if="generarSerie(data.item.productoSerie).length>0"
              :options="generarSerie(data.item.productoSerie)" 
              v-model="data.item.IdProductoSerie"
              text-field="Serie"
              value-field="IdProductoSerie"
          />
          
        </template>
        <template slot="AddSeries" slot-scope="data">
          <b-button variant="info" size="sm" @click="abrirmodalNuevaSerie(data['index'],data.item)">
            <i class="fa fa-barcode" aria-hidden="true"></i>
          </b-button>
        </template>
        
        <template slot="cantidad" slot-scope="data">
          <input
            v-model="productos[data.index].cantidad"
            :disabled="productos[data.index].tipo == 'PRESENTACION'"
            type="number"
            style="width: 95px"
            class="form-control"
            step="1"            
            min="0"
            @input="
              (val) => {
                cambiarPrecioConvenioBloque(data.index,data.item)
                $emit('onInputCantidad', val.target.value);
               recalcularTotalPorItem(productos[data.index],data.index);
                if (productos[data.index].EsObsequio == 1) {
                  productos[data.index].precio = 0;
                  productos[data.index].nuevoPrecioContado = 0;
                }
              }
            "
            placeholder="Cantidad"
          />
        </template>
        <template slot="precio" slot-scope="data">
          <!-- disabled -->
          <input
          :disabled="
              $store.state.bloquearCarrito ||
              (bloquearInput ? true : false) ||
              (idPerfil == 1 ? false : true) ||
              esDelivery
          "
            v-model="productos[data.index].precio"
            type="number"
            style="width: 95px"
            min="0"
            step="0.000001"
            class="form-control"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)"
          />
        </template>
        <template slot="subtotal" slot-scope="data">
          {{ getSubtotal(data.index) }}
        </template>
        <template slot="subtotalE" slot-scope="data">
          <b-input v-model="productos[data.index].subtotal"
          type="number"
           @blur="recalcularPUxSubTotal(data.index)"
           />
          <!-- {{data.item.subtotal}} -->
          
          {{ getSubtotal(data.index) }}
        </template>
        <template slot="igv" slot-scope="data">
          <b-form-checkbox
            v-model="productos[data.index].TieneIgv"
            :value="1"
            :disabled="
              bloquearInput
                ? true
                : false ||
                  (productos[data.index].EsObsequio == 1 && tipoMov == 0
                    ? true
                    : false)
            "
            @input="setNuevoPrecioContado(data.index)"
            :unchecked-value="0"
          >
            <div>{{ getIgv(data.index) }}</div>
          </b-form-checkbox>
        </template>

        <template slot="manoobra" slot-scope="data">
          <input
            @change="(e) => addManoDeObra(data.index, e)"
            v-if="productos[data.index].CodigoBarra != 'MANODEOBRA'"
            :disabled="bloquearInput ? true : false"
            v-model="productos[data.index].EsManoDeObra"
            class="form-check-input"
            type="checkbox"
          />
        </template>

        <template slot="precioVenta" slot-scope="data">
          <input
            :disabled="bloquearInput ? true : false"
            style="width: 95px"
            v-model="productos[data.index].nuevoPrecioContado"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            placeholder="Precio Venta"
          />
          <b-form-text>
            <strike> {{ productos[data.index].normalPrecioContado }} </strike>
          </b-form-text>
        </template>

        <template slot="utilidad" slot-scope="data">
          <input
            :disabled="
              bloquearInput
                ? true
                : false || idPerfil == 1
                ? false
                : true || esDelivery
            "
            class="form-control"
            v-model="productos[data.index].PorcentajeUtilidad"
            style="width: 80px"
            type="number"
            step="0.01"
            @input="cambiarPrecio(data.index, productos[data.index])"
            @mouseup="cambiarPrecio(data.index, productos[data.index])"
          />
          <small
            >ganancia:
            {{
              (productos[data.index].precio - productos[data.index].PrecioCosto)
                | redondear
            }}</small
          >
        </template>

        <template slot="descuento" slot-scope="data">
          <input
            :disabled="
                $store.state.bloquearCarrito ||
                (bloquearInput ? true : false) ||
                esDelivery
            "
            v-model="productos[data.index].descuento"
            style="width: 90px"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            value="0"
            @input="actualizarDescu(data.index)"
          />
          <b-form-text> </b-form-text>
        </template>

        <template slot="FechaAlquiler" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].FechaAlquiler"
            type="date"
            placeholder="Fecha Fin"
            :value="formatFecha(data.item.FechaAlquiler)"
          ></b-form-input>
        </template>

        <template slot="FechaAlquilerFin" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].FechaAlquilerFin"
            type="date"
            placeholder="Fecha Fin"
            :value="formatFecha(data.item.FechaAlquilerFin)"
          ></b-form-input>
        </template>

        <template slot="Descripcion" slot-scope="data">
          <textarea
            :disabled="bloquearInput ? true : false"
            class="form-control"
            placeholder="Descripción"
            cols="9"
            rows="1"
            style="width: 150px"
            v-model="productos[data.index].Descripcion"
          ></textarea>
        </template>

        <template slot="total" slot-scope="data">
          {{ getTotal(data.index) }}
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
        <template slot="acciones" slot-scope="data">
          <b-button
            :disabled="bloquearInput ? true : false"
            variant="danger"
            size="sm"
            @click="quitarProducto(data.index)"
            ><i class="fas fa-times fa-lg"></i
          ></b-button>
          <b-button
            style="margin-top: 5px"
            :disabled="bloquearInput ? true : false"
            v-if="idPerfil == 1 ? !isVentas : false"
            variant="primary"
            size="sm"
            @click="editarProducto(productos[data.index])"
            ><i class="fas fa-pencil-alt fa-sm"></i
          ></b-button>
        </template>

        <template slot="show_details" slot-scope="row">
          <b-button
            :disabled="bloquearInput ? true : false"
            size="sm"
            variant="info"
            @click.stop="row.toggleDetails"
            >></b-button
          >
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
    <b-col md="3">
      <!-- <slot name="operaciones"></slot> -->
    </b-col>
    <b-col md="3">
      <!-- <slot name="metodopago"></slot> -->
    </b-col>
    <b-col md="3">
      <!-- <slot name="totalEmpresa"  v-if="mostrarTablaProductos"></slot> -->
    </b-col>
    <b-col md="3" >
      <table class="table b-table border table-footer " v-if="mostraTotales">
        <tbody>
          <tr v-if="sumatoriaDescuento > 0">
            <td class="table-success">SubTotal</td>
            <td class="table-success">
              {{
                (parseFloat(sumatoriaSubTotal) + parseFloat(sumatoriaDescuento))
                  | redondear
              }}
            </td>
          </tr>
          <tr v-if="sumatoriaDescuento > 0">
            <td class="table-danger">Descuento</td>
            <td class="table-danger">- {{ sumatoriaDescuento }}</td>
          </tr>
          <tr   v-if="mostrarTablaProductos">
            <td><b>SubTotal</b></td>
            <td class="table-danger">{{ sumatoriaSubTotal }}</td>
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
              <b>S/ {{ vuelto | redondear }}</b>
            </td>
          </tr>
        </tbody>
      </table>
    </b-col>
    <br /><br />

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
                    {{ isVentas ? itemProducto.PrecioContado : itemProducto.PrecioCosto }}
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
  </b-row>
</template>

<script>
import { mapState, mapActions } from "vuex";
import ProductoForm from "./ProductoForm";
import config from "@/store/config.js";

export default {
  async beforeMount() {
    let empresa = sessionStorage.getItem("empresa");
    if (!empresa) {
      this.empresa = "modasm";
      sessionStorage.setItem("empresa", this.empresa);
    } else {
      this.empresa = empresa;
    }
  },
  async mounted() {
    let vm = this;

    // Carga inicial de la lista de productos multiempresa (solo si el
    // interruptor "Búsqueda multiempresa" está activo), para que el
    // dropdown ya tenga datos listos apenas el cajero haga foco/clic.
    if (this.panelProductosActivo) {
      await this.cargarListadoProductos(4);
    }

    // Cierra el dropdown al hacer clic fuera del panel de búsqueda.
    document.addEventListener("mousedown", this.handleClickOutsideProductos);

    // vm.lstProductos = [];
    // for (const empresa of vm.$store.state.empresas) {
    //   let url = `${
    //     vm.API
    //   }productos/puntoventa/modal/stock/1?q&sortBy=stock&sortDesc=1&id_empresa=${
    //     empresa.value
    //   }&limit=`;

    //   let res = await vm.axios(url); // Espera la respuesta antes de continuar

    //   res.data.forEach(element => {
    //     vm.lstProductos.push({
    //       ...element,
    //       Empresa: empresa.value,
    //       Selected: false,
    //       NombreEmpresa: empresa.desc.substring(0, 15),
    //       _rowVariant:
    //         element.stock <= 0
    //           ? "danger"
    //           : element.stock < element.StockMinimo
    //             ? "warning"
    //             : "success"
    //     });
    //   });
    // }
  },
  beforeDestroy() {
    document.removeEventListener("mousedown", this.handleClickOutsideProductos);
  },
  data() {
    return {
      selectedProducto: null,
      lstProductosRapido: [],
      lstProductosDescripcion: [],
      lstProductoStock: [],
      Serie: "",
      Series: [],
      page: 0,
      anadirSerieFlag: false,
      abrirModal: false,
      mostrarTablaProductos: true,
      modalPresentaciones: false,
      modalPresentacionesEditar: false,
      index: false,
      empresa: "",
      IdProducto: "",
      CodigoBarra: "",
      errorMessage: "",
      // Interruptor: activa/desactiva por completo la búsqueda rápida
      // multiempresa. Se recuerda entre sesiones vía localStorage.
      panelProductosActivo: localStorage.getItem("pos_panelProductosActivo") !== "false",
      // Controla si el panel flotante (dropdown) de resultados está
      // visible en este momento (se abre al enfocar/escribir, se cierra
      // al hacer clic fuera, con Esc o con el botón de cerrar).
      mostrarDropdownProductos: false,
      lstProductos: [],
      busqueda: "",
      searchSoloBarras: "",
      // perPage: 7,
      currentPage: 1,
      searchProducto: "",
      searchProductoDesc: "",
      errorMontoPresentacion: "",
      searchProducto: "",
      searchSoloBarras: "",
      searchTimeout: null,
      searchTimeoutBarras: null,
      monto: 0,
      selected: 1,
      options: [
        { text: "Producto", value: 1 },
        { text: "Marca", value: 3 },
        { text: "Categoría", value: 2 }
        // { text: "This is the 4th radio", value: { fourth: 4 } }
      ],
      fieldsNuevoProductos: [
        { key: "Selected", label: "" },
        { key: "NombreEmpresa", label: "Empresa" },
        { key: "ProductoCategoria", label: "Categoria" },
        { key: "ProductoMarca", label: "Marca" },
        { key: "Producto", label: "Producto" },
        { key: "stock", label: "stock" },
        { key: "Stock Mínimo", label: "StockMinimo" },
        { key: "PrecioContado", label: "Precio" },
        { key: "Imagen", label: "Imagen" }
      ],
      headerModalProducts: [
        { label: "Empresa", key: "NombreEmpresa" },
        { label: "Categoria", key: "ProductoCategoria" },
        { label: "Marca", key: "ProductoMarca" },
        { label: "Producto", key: "Producto" },
        { label: "Stock", key: "stock" },
        { label: "Stock Min", key: "StockMinimo" },
        { label: "Precio", key: "PrecioContado" },
        { label: "Imagen", key: "img" },
        { label: "Add", key: "add" }
      ],
      perPageModalList: [
        { value: 5, text: "5" },
        { value: 10, text: "10" },
        { value: 20, text: "20" },
        { value: 50, text: "50" },
        { value: 100, text: "100" },
        { value: null, text: "All" }
      ],

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
      perPageModal: 20,
      currentPageModal: 0,
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
      productoNombre: "",
      productoUrl: "",
      itemProducto: {},
      primerMonto: true,
      currentPage: 1
      // perPageModal: 100
    };
  },
  watch: {
    totalCart() {
      this.pagocon = parseFloat(this.totalCart).toFixed(2);
    },
    sumatoriaTotal(sum) {
      this.asignarVuelto();
    },
    productos(prods) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");

      if (pi >= 0 && !this.productos[pi].precioBruta) {
        this.productos[pi].precioBruta = this.productos[pi].precio;
      }
    },
    panelProductosActivo(val) {
      localStorage.setItem("pos_panelProductosActivo", val);
      if (val) {
        this.cargarListadoProductos(4);
      } else {
        this.mostrarDropdownProductos = false;
      }
    }
  },
  props: {
    isVentas: Boolean,
    tipo: String,
    setPrecio: Boolean,
    tipoCambio: Number,
    igvTotal: Boolean,
    tipoMov: Number,
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
    mostraTotales: {
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
          { key: "ProductoPresentacion", label: "Anaquel" },
          // { key: 'ProductoMedicion', label: 'Medición' },
          { key: "obsequio", label: "Obsequio", class: "thcenter" },
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
    ...mapState({
      productos: state => state.cart.productos,
      filtrados: state => state.cart.productosFiltrados,
      totalCart: state => state.cart.totalCart
    }),
    isLastPage() {
      return this.currentPage >= this.totalPages;
    },
    APIProductos() {
      if (this.idAlmacen) {
        return this.API + "productos/puntoventa/stock/" + this.idAlmacen;
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
    onFocusBusquedaProductos() {
      this.mostrarDropdownProductos = true;
      if (this.lstProductosRapido.length === 0) {
        this.cargarListadoProductos(4);
      }
    },
    handleClickOutsideProductos(event) {
      const wrapper = this.$refs.panelProductosWrapper;
      if (wrapper && !wrapper.contains(event.target)) {
        this.mostrarDropdownProductos = false;
      }
    },
    async onInputCodigoBarra() {
      if (!this.CodigoBarra) return;

      await this.cargarListadoProductos(3);

    },
    actualizarProductoEnCarrito(productoEditado) {
      let index = this.productos.findIndex(
        p => p.IdProducto === productoEditado.IdProducto
      );

      if (index !== -1) {
        let prodActual = this.productos[index];

        prodActual.CodigoBarra = productoEditado.CodigoBarra;
        prodActual.Producto = productoEditado.Producto;

        // Marca
        prodActual.IdProductoMarca = productoEditado.IdProductoMarca;
        prodActual.ProductoMarca = productoEditado.ProductoMarca;

        if (productoEditado.marca) {
          prodActual.ProductoMarca = productoEditado.marca.ProductoMarca;
          prodActual.IdProductoMarca = productoEditado.marca.IdProductoMarca;
        }

        // COSTO → afecta P/U
        let nuevoCosto = parseFloat(productoEditado.PrecioCosto) || 0;
        prodActual.precio = nuevoCosto;        // ← ESTE ES EL P/U
        prodActual.PrecioCosto = nuevoCosto;

        // PRECIO VENTA → solo referencia
        let nuevoVenta = parseFloat(productoEditado.PrecioContado) || 0;
        prodActual.PrecioContado = nuevoVenta;
        prodActual.nuevoPrecioContado = nuevoVenta;

        // recalcular subtotal
        let cantidad = parseFloat(prodActual.cantidad) || 0;
        this.$set(prodActual, "subtotal", cantidad * nuevoCosto);
        this.$set(prodActual, "total", cantidad * nuevoCosto);

        this.$set(this.productos, index, prodActual);
      }
    },
    forzarFocusPersistente() {
      let intentos = 0;

      const intervalo = setInterval(() => {
        const input = this.$refs.inputProducto;

        if (input) {
          input.focus();
          input.select();
        }

        intentos++;

        // después de varios intentos lo detenemos
        if (intentos > 10) {
          clearInterval(intervalo);
          //console.log("FOCUS ASEGURADO");
        }
      }, 100);
    },
    seleccionarProducto(prod) {
    // guardar seleccionado
    this.selectedProducto = prod;

    // mostrar stock (lo que ya hacías)
    this.obtenerStockAlmacen(prod);
    },
    agregarDirecto(prod) {
    this.anadirarProductosSeleccionado(prod, true);
    },
    bgStock(item) {
      if (item.stock == 0) {
        return "bgSinStock";
      }
      if (item.stock < item.StockMinimo) {
        return "bgMinimo";
      }
      return "";
    },
    limpiarBuscar() {
      this.searchProducto = "";
      document.getElementById("searchProducto").focus();
    },
    anadirSerie() {
      if (!this.Serie) {
        return;
      }
      if (this.Series.some(v => v == this.Serie)) {
        this.$toasted.error(
          "La serie ya se encuentra en el listado. Modifique"
        );
        return;
      }
      this.Series.unshift({ IdProductoSerie: "", Serie: this.Serie });
      setTimeout(() => {
        this.Serie = "";
        let txtSerie = document.getElementById("txtSerie");
        txtSerie.focus();
      }, 200);
    },
    generarSerie(serie) {
      if (serie) {
        return serie;
      }
      return [];
    },
    nextPage() {
      if (this.currentPage < this.totalPages) this.currentPage++;
    },
    prevPage() {
      if (this.currentPage > 1) this.currentPage--;
    },
    recalcularPUxSubTotal(index) {
      let prod = { ...this.productos[index] };
      prod.precio = parseFloat(prod.subtotal / prod.cantidad);
      this.productos[index] = prod;
    },
    onSearchProducto() {
      clearTimeout(this.searchTimeout);
      this.searchTimeout = setTimeout(() => {
        this.cargarListadoProductos(1);
      }, 700);
    },

    onSearchBarras() {
      clearTimeout(this.searchTimeoutBarras);
      this.searchTimeoutBarras = setTimeout(() => {
        this.cargarListadoPorBarras();
      }, 700);
    },
    abrirmodalNuevaSerie(index, producto) {
      this.Serie = "";
      this.Series = [];
      this.objProd = { ...producto };

      if (this.objProd.productoSerie) {
        this.Series = this.objProd.productoSerie;
      }
      this.index = index;
      this.anadirSerieFlag = true;
      this.$nextTick(() => {
        setTimeout(() => {
          let txtSerie = document.getElementById("txtSerie");
          txtSerie.focus();
        }, 500);
      });
    },
    guardarSerie() {
      this.objProd.Serie = this.Series;
      Vue.set(this.productos, this.index, this.objProd);
      this.anadirSerieFlag = false;
    },
    eliminarSerieModal(index) {
      this.Series.splice(index, 1);
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
    ...mapActions({
      add: "agregarProducto",
      buscar: "buscarProductos",
      eliminar: "eliminarProducto",
      actualizarTotal: "actualizarTotalCart",
      actualizarProd: "actualizarProducto",
      buscarSeriesxProduto: "buscarSeriesxProduto"
      // obtenerStok: "obtenerStock",
    }),
    abrirImagen(producto, url) {
      this.productoNombre = producto;
      this.productoUrl = url;
      this.$refs["modalImagen"].show();
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    changeCant(val, prod) {
      if (val > 0) {
        prod.cantidad = prod.cantidadPres;
        prod.cantidad *= val;
      }
      let index = this.productos.findIndex(
        v => v.IdProducto == prod.IdProducto
      );
      this.recalcularTotalPorItem(prod, index);

      this.$nextTick(() => {
        let totalCart = this.productos.reduce((a, b) => {
          return a + parseFloat(b.total);
        }, 0);
        this.$store.state.cart.totalCart = parseFloat(totalCart).toFixed(2);
      });
    },
    cambiarPrecioConvenioBloque(index, item) {
      // si no es venta, no modificar precio
      if (!this.isVentas) {
        return;
      }

      if (item.precioBloque > 0 && item.cantidad >= item.PrecioPorBloque) {
        this.productos[index].precio = item.precioBloque;
      } else if (
        item.PrecioPorMayor > 0 &&
        item.cantidad >= item.PrecioPorMayor
      ) {
        this.productos[index].precio = item.precioConvenio;
      } else {
        this.productos[index].precio = item.PrecioContado;
      }
    },
    cambiarPrecio(index, prod) {
      var utilidad =
        this.productos[index].PrecioCosto *
        (this.productos[index].PorcentajeUtilidad / 100);
      this.productos[index].precio = Math.ceil(
        this.productos[index].PrecioCosto + utilidad
      );
    },
    anadirProductos() {
      let productos = [...this.listadoProductos()];
      if (productos.length == 1) {
        this.IdProducto = "";
        this.anadirarProductosSeleccionado(productos[0], true);
      }
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
      this.vuelto = parseFloat(this.pagocon - this.sumatoriaTotal).toFixed(2);
    },

    loadBarcode(e) {
      e.preventDefault();
      let idProducto = this.loadCode;
      let id_empresa = this.empresa;
      if (idProducto) {
        let params = {
          codigoBarra: idProducto,
          id_empresa: id_empresa,
          idAlmacen: this.idAlmacen
        };
        this.axios
          .get(this.API + "productos/id/" + idProducto, { params: params })
          .then(response => {
            if (response.data.IdProducto) {
              this.anadirarProductosSeleccionado(response.data);
            } else {
              this.loadCode = "";
              this.$toasted.show("No se encontró ningun producto");
            }
            this.loadCode = "";
          });
      }
    },

    buscarProductosCodigoBarrasModal(e) {
      e.preventDefault();
      this.IdProducto = this.loadCode;
    },

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

        // AGREGAR DIRECTAMENTE AL CARRITO
        obj.IdProducto = id;
        obj.cantidad = 1;
        obj.precio = obj.PrecioCosto || 0;
        obj.nuevoPrecioContado = obj.PrecioContado || 0;
        obj.stock = 0;
        //Datos del producto
        obj.CodigoBarra = obj.CodigoBarra || "";
        obj.ProductoMarca = obj.ProductoMarca || (obj.marca? obj.marca.ProductoMarca: "SIN MARCA");

        obj.estadoPxP = !this.isVentas;
        this.$store.commit("AGREGAR_PRODUCTO", obj);
      setTimeout(() => {
        this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
      }, 200);

      if (this.isVentas) {
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
        }
      }
    },
    async setProducto(obj) {
      if (!obj.stockMax) {
        obj.stockMax = obj.stock;
      }

      let empresa = sessionStorage.getItem("empresa")
        ? sessionStorage.getItem("empresa")
        : "1";
      this.precioCont = obj.PrecioContado;
      obj.estadoPxP = false;
      this.loadCode = "";
      this.preciosProd = [];
      this.objProd = {};
      obj.EsObsequio = 0;

      let productoSerie = await this.obtenerSeriePorProducto(obj.IdProducto);
      obj.productoSerie = productoSerie;
      obj.EsVenta = this.isVentas;

      if (!this.isVentas) {
        obj.PreciosPorProducto = "";
      }

      this.preciosProd = JSON.parse(obj.PreciosPorProducto || "[]");
      let fPP = this.preciosProd.filter(
        p => p.cantidad === 0 || p.cantidad === "0"
      );
      console.log(obj);
      if (!obj.PreciosPorProducto || fPP.length === 5) {
        console.log("llegó a");
        obj.estadoPxP = true;
      }
      this.setProductoUni(obj);
      console.log("llegó b");

      this.$refs["modalCant"].show();
      // this.preciosProd = JSON.parse(obj.PreciosPorProducto)
      this.objProd = obj;
    },
    setProductoUni(obj) {
      let empresa = sessionStorage.getItem("empresa")
        ? sessionStorage.getItem("empresa")
        : "modasm";

      if (!this.isVentas) {
        obj.PreciosPorProducto = "";
      }
      this.objProd.cantxPrecio = 0;
      this.$refs.autocompleteProducto.setValue("");
      //obj.FLETE = 0
      obj.precio = 0;
      obj.cantidad = 1;
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0;
      obj.nuevoPrecioContado = 0;
      obj = this.onSetProducto(obj);

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
      this.objProd.cantxPrecio = 1;
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
    recalcularTotalPorItem(item, index) {
      let produc = { ...item };
      let cantidad = parseFloat(produc.cantidad) || 0;
      let precio = parseFloat(produc.precio) || 0;

      item.subtotal = cantidad * precio;
      item.total = cantidad * precio;

      this.actualizarDescu(index);
      setTimeout(() => {
        this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
          .recalcularTotalPorEmpresaFlag;
      }, 100);
    },

    renderChild(data) {
      if (this.idAlmacen) {
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
          <table id="table-render" style="width: 800px; text-align: center;padding:0; margin:0">
            <tbody>
              <tr style="padding:0; margin:0">
                <td style="width: 60px" class="${styleStock}"><b>${
          data.EsPadre == 1
            ? '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60800/l+r+SqbUNOsK9pfd4Fk7Ov9r8mo+2Jf5vXVv8vAJAyPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCund53CIH6ptbaxbXV3ycTv0F9C8dv0F1AhI+DKcAAAAABJRU5ErkJggg==" style="width: 24px" />'
            : data.stock
              ? Number.isInteger(parseFloat(data.stock))
                ? parseInt(data.stock)
                : parseFloat(data.stock).toFixed(0)
              : data.stock
        }</b></td>
                <td style="width: 30px"><b> ${data.CodigoBarra}</b></td>
                <td style="text-align:left; pading: 0 0 0 5px ;width: 400px"><b>${
                  data.Producto
                }</b></td>
                <td style="width: 100px">${data.ProductoMarca}</td>
                <td style="width: 100px">${data.ProductoCategoria}</td>
                <td style="width: 65px" class="table-primary"><b>${
                  data.PrecioContado ? data.PrecioContado : ""
                }</b></td>
                <td style="width: 5%">
                  <img src="${
                    data.Imagen == null || data.Imagen == ""
                      ? "static/no-photo.png"
                      : "/resources/images/productos/" + data.Imagen
                  }" class="imagen">
                </td>
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

    setNuevoPrecioContado(index) {
      let precio = parseFloat(this.productos[index].precio);
      if (this.productos[index].TieneIgv) {
        precio += precio * 0.18;
      }
      let precioFinal = 0;

      precioFinal =
        (precio * this.productos[index].PorcentajeUtilidad) / 100 + precio;

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
      this.mostrarTablaProductos = !false;
      this.actualizarDescu(index);
      setTimeout(() => {
        this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
          .recalcularTotalPorEmpresaFlag;
      }, 100);
    },
    actualizarDescu(index) {
      let obj = this.productos[index];

      this.actualizarProd(obj);
      this.$nextTick(() => {
        this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
          .recalcularTotalPorEmpresaFlag;
      });
    },
    getSubtotal(index) {
      let subtotal = this.$options.filters.redondear(
        this.productos[index].cantidad * this.productos[index].precio
      );
      Vue.set(this.productos[index], "subtotal", subtotal);
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
      return total;
    },
    getData(value) {
      this.buscar(value);
    },
    quitarProducto(index) {
      this.eliminar(index);
      this.$emit("onRemoveProducto", index);
      this.$toasted.show("Producto quitado del listado");
    },

    formatFecha(fecha) {
      if (fecha) {
        return this.$moment(fecha, "YYYY-MM-DD hh:mm:ss").format("YYYY-MM-DD");
      } else {
        return "";
      }
    },
    getSyle(IDEMPRESA) {
      switch (IDEMPRESA) {
        case 1:
          return "modasm";
          break;
        case 2:
          return "modasgio";
          break;
        case 3:
          return "modasjes";
          break;
        default:
          return "";
          break;
      }
    },
    async cargarListadoPorBarras() {
      let IdProductos = this.productos.map(v => v.IdProducto);
      
      await this.axios(
        `${this.API}productos/puntoventa/barcode/stock/${this.idAlmacen}`,
        {
          params: {
            search: this.searchSoloBarras,
            limit: this.perPageModal,
            page: 0,
            IdProductos: IdProductos.join(",")
          }
        }
      ).then(res => {
        this.lstProductosRapido = res.data;
        this.lstProductosDescripcion = res.data;
      }).catch(err => {
        console.error("Error en búsqueda por barras:", err);
      });
    },

    async cargarListadoProductos(tipo) {
      let IdProductos = this.productos.map(v => {
        return v.IdProducto;
      });
      console.log("IdProductos", IdProductos.join(","));
      await this.axios(
        `${this.API}productos/puntoventa/modal/stock/${this.idAlmacen}`,
        {
          params: {
            sortBy: null,
            sortDesc: 1,
            limit: null,
            search:
              tipo == 1
                ? this.searchProducto
                : tipo == 2
                  ? this.searchProductoDesc
                  : "",
            // tipo: this.selected,
            limit: this.perPageModal,
            page: 0,
            CodigoBarra: this.CodigoBarra,
            tipo: tipo,
            IdProductos: IdProductos.join(",")
          }
        }
      ).then(res => {
        switch (tipo) {
          case 1:
            this.lstProductosRapido = res.data;

            break;
          case 2:
            this.lstProductosDescripcion = res.data;
            break;
          case 3:
            this.lstProductosRapido = res.data;
            this.lstProductosDescripcion = res.data;
            break;
          case 4:
            this.lstProductosRapido = res.data;
            this.lstProductosDescripcion = res.data;
            break;
          default:
            break;
        }


        // AUTO AGREGAR SI ES CODIGO DE BARRA
        if (tipo === 3 && this.CodigoBarra && res.data.length === 1) {
            this.anadirarProductosSeleccionado(res.data[0], true);

            // LIMPIAR DESPUÉS DEL RENDER
            this.$nextTick(() => {
              this.CodigoBarra = "";

               this.cargarListadoProductos(4);

              // DOBLE REFUERZO DE FOCUS
              setTimeout(() => {
                this.forzarFocusPersistente();
              }, 50);
            });
          }

      });
    },

    listadoProductosOrdenados() {
      let lst = [...this.lstProductos].filter(
        listadoProducto =>
          !this.$store.state.cart.productos.some(
            producto =>
              producto.IdProducto == listadoProducto.IdProducto &&
              producto.Empresa == listadoProducto.Empresa
          )
      );

      if (this.searchProducto) {
        lst.sort((a, b) =>
          (a.CodigoBarra || "").localeCompare(b.CodigoBarra || "", "es", {
            sensitivity: "base"
          })
        );
      }

      return lst;
    },
    listadoProductos() {
      let lst = [];
      let productosFiltrados = [...this.lstProductos].filter(
        listadoProducto =>
          !this.$store.state.cart.productos.some(
            producto =>
              producto.IdProducto == listadoProducto.IdProducto &&
              producto.Empresa == listadoProducto.Empresa
          )
      );
      return productosFiltrados;
    },
    async obtenerSeriePorProducto(id) {
      let productoSerie = [];
      let params = {
        IdProducto: id,
        Todos: false,
        empresa: sessionStorage.getItem("empresa"),
        IdAlmacen: this.idAlmacen
      };
      productoSerie = await this.buscarSeriesxProduto(params);

      return productoSerie;
    },
    async anadirarProductosSeleccionado(item, abrirModal = false) {
      this.mostrarDropdownProductos = false;
      item.stockMax = item.stock;

      if (this.productos.some(v => v.IdProducto == item.IdProducto)) {
        let index = this.productos.findIndex(
          v => v.IdProducto == item.IdProducto
        );
        let prod = {
          ...this.productos.filter(v => v.IdProducto == item.IdProducto)[0]
        };

        if (prod.tipo == "UNIDAD") {
          prod.cantidad++;
        }
        if (prod.tipo == "PRESENTACION") {
          prod.cantxPrecio++;
        }
        prod.stockMax = prod.stock;
        Vue.set(this.productos, index, prod);
        this.$nextTick(() => {
          this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
            .recalcularTotalPorEmpresaFlag;
        });
        this.$toasted.success("Producto añadido correctamente");
        this.loadCode = "";
        return;
      }
      this.abrirModal = abrirModal;
      let PreciosPorProducto = JSON.parse(item.PreciosPorProducto);
      let productoSerie = await this.obtenerSeriePorProducto(
        item.IdProducto,
        item.Empresa
      );
      delete item._rowVariant;
      item.productoSerie = productoSerie;

      this.itemProducto = { ...item };
      this.itemProducto.tipo = "PRESENTACION";
      this.itemProducto.PreciosPorProducto = PreciosPorProducto;
      if (PreciosPorProducto.filter(v => v.cantidad > 0).length > 0) {
        this.monto = 1;
        setTimeout(() => {
          document.getElementById("montoPresentacion").focus();
          document.getElementById("montoPresentacion").select();
        }, 100);
        this.modalPresentaciones = true;
        this.primerMonto = true;
      } else {
        this.itemProducto.tipo = "UNIDAD";
        this.itemProducto.estadoPxP = true;
        if (this.isVentas) {
          // venta
          this.itemProducto.precio = this.itemProducto.PrecioContado;
          this.itemProducto.nuevoPrecioContado = this.itemProducto.PrecioContado;
        } else {
          // compra
          this.itemProducto.precio = this.itemProducto.PrecioCosto;
          this.itemProducto.nuevoPrecioContado = this.itemProducto.PrecioContado;
        }
        this.itemProducto.total = 0;
        this.itemProducto.cantxPrecio = 0;
        this.itemProducto.cantidad = 1;
        this.itemProducto.descuento = 0;

        this.$store.state.cart.productos.unshift(this.itemProducto);
      }
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

      if (this.itemProducto.PreciosPorProducto.length > 0) {
        if (this.isVentas) {
          prod.total = this.monto * presentacion.precio;
          prod.precio = presentacion.precio;
        } else {
          prod.precio = prod.PrecioCosto;
          prod.total = prod.cantidad * prod.PrecioCosto;
        }
        prod.cantidadPres = presentacion.cantidad;
        prod.estadoPxP = false;
      }
      prod.nuevoPrecioContado = prod.PrecioContado;
      prod.normalPrecioContado = prod.PrecioContado;
      delete prod._rowVariant;
      this.$store.state.cart.productos.unshift(prod);
      this.modalPresentaciones = false;
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
      prod.cantidad = this.monto;
      prod.descuento = 0;
      prod.disabled = false;
      prod.Presentacion = null;
      prod.total = 0;
      prod.precio = this.isVentas ? prod.PrecioContado : prod.PrecioCosto;
       prod.nuevoPrecioContado = prod.PrecioContado;
        prod.normalPrecioContado = prod.PrecioContado;
      prod.cantidadPres = 0;
      prod.bloquearInput = false;
      if (this.itemProducto.PreciosPorProducto.length > 0) {
        prod.estadoPxP = false;
      }
      delete prod._rowVariant;
      this.$store.state.cart.productos.unshift(prod);
      this.modalPresentaciones = false;
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
      prod.cantidad = this.monto;

      if (this.itemProducto.PreciosPorProducto.length > 0) {
        if (this.isVentas) {
            prod.total = this.monto * presentacion.precio;
            prod.precio = presentacion.precio;
          } else {
            prod.precio = prod.PrecioCosto;
            prod.total = prod.cantidad * prod.PrecioCosto;
          }
        prod.cantidadPres = presentacion.cantidad;
        prod.estadoPxP = false;
      }

      prod.nuevoPrecioContado = prod.PrecioContado;
      prod.normalPrecioContado = prod.PrecioContado;

      this.$store.state.cart.productos[this.index] = prod;
      setTimeout(() => {
        this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
          .recalcularTotalPorEmpresaFlag;
        this.modalPresentacionesEditar = false;
      }, 100);
      let total = this.$store.state.cart.productos.reduce((total, item) => {
        return parseFloat(total) + parseFloat(item.total);
      }, 0);
    },
    guardarItemPorUnidadEditar() {
      this.errorMontoPresentacion = "";
      if (this.monto == 0) {
        this.errorMontoPresentacion = "Ingrese una Cantidad";
        return false;
      }
      let prod = this.itemProducto;
      prod.tipo = "UNIDAD";
      prod.precio = this.isVentas ? prod.PrecioContado : prod.PrecioCosto;
      prod.cantxPrecio = this.monto;
      prod.cantidad = this.monto;
      if (this.itemProducto.PreciosPorProducto.length > 0) {
         prod.precio = this.isVentas ? prod.PrecioContado : prod.PrecioCosto;
          prod.total = this.monto * prod.precio;
      }

      this.$store.state.cart.productos[this.index] = prod;
      setTimeout(() => {
        this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
          .recalcularTotalPorEmpresaFlag;
        this.modalPresentacionesEditar = false;
      }, 100);
      let total = this.$store.state.cart.productos.reduce((total, item) => {
        return parseFloat(total) + parseFloat(item.total);
      }, 0);
    },
    async anadirarProductos() {
      await this.lstProductos
        .filter(v => !!v.Selected)
        .forEach(async element => {
          let item = { ...element };

          delete item._rowVariant;
          if (
            !this.productos.some(
              v =>
                v.IdProducto == element.IdProducto &&
                v.empresa == element.empresa
            )
          ) {
            await this.guardaProductosEnLaTablaFinal(item);
          }
        });
    },
    async guardaProductosEnLaTablaFinal(item) {
      this.objProd.cantxPrecio = 1;

      item.subtotal = item.PrecioContado;
      item.cantxPrecio = 1;
      item.estadoPxP = false;

      item.cantidad = 1;
      // itemobj.IdLote = 0
      item.TieneIgv = 0;
      item.descuento = 0;
      item.total = item.PrecioContado;
      this.preciosProd = JSON.parse(item.PreciosPorProducto || "[]");
      let fPP = this.preciosProd.filter(
        p => p.cantidad === 0 || p.cantidad === "0"
      );

      if (!item.PreciosPorProducto || fPP.length === 5) {
        item.estadoPxP = true;
        this.setProductoUni(item);
        return;
      }

      //obj.nuevoPrecioContado = 0;
      let obj = await this.onSetProducto(item);

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

      obj.cantidadPres = obj.cantidad;
      //this.actualizarProd(obj)

      this.$store.state.recalcularTotalPorEmpresaFlag = !this.$store.state
        .recalcularTotalPorEmpresaFlag;
      this.sumatoriaSubTotal();
    },
    startRecognition() {
      // Verifica si el navegador soporta la Web Speech API
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }

      this.recognition = new webkitSpeechRecognition();
      this.recognition.lang = "es-ES"; // Establece el idioma, en este caso español (España)
      this.recognition.continuous = false; // El reconocimiento no es continuo
      this.recognition.interimResults = false; // No muestra resultados intermedios

      // Maneja los eventos del reconocimiento
      this.recognition.onresult = event => {
        if (event.results.length > 0) {
          this.searchProducto = event.results[0][0].transcript;
        }
      };

      this.recognition.onerror = event => {
        console.error("Speech Recognition Error:", event);
        this.handleError(event.error);
        alert(this.errorMessage);
      };

      this.recognition.onend = () => {
        console.log("Speech Recognition Ended");
      };

      // Inicia el reconocimiento de voz
      this.recognition.start();
    },

    handleError(error) {
      this.errorMessage = "";
      if (error === "no-speech") {
        this.errorMessage =
          "No se detectó ningún discurso. Por favor, inténtalo de nuevo.";
      } else if (error === "audio-capture") {
        this.errorMessage =
          "No se pudo acceder al micrófono. Asegúrate de que esté correctamente conectado y de que el navegador tenga permisos para usarlo.";
      } else if (error === "not-allowed") {
        this.errorMessage =
          "Permiso para usar el micrófono denegado. Por favor, habilita el acceso en la configuración de tu navegador.";
      } else {
        this.errorMessage =
          "Ocurrió un error desconocido. Por favor, inténtalo de nuevo.";
      }
    },
    buscarProductosCodigoBarrasModal(e) {
      e.preventDefault();
      // this.IdProducto = this.IdProducto;
    },
    variantPorEmpresa(empresa) {
      return null;
    },
    async obtenerStockAlmacen(Producto) {
      await this.axios(`${this.API}/productos/almacen/stock`, {
        params: { IdProducto: Producto.IdProducto }
      }).then(res => {
        this.lstProductoStock = res.data;
      });
      let stockMax = this.lstProductoStock.reduce((sum, prod) => {
        return sum + parseFloat(prod.stock);
      }, 0);
      Producto.stockMax = stockMax;
      this.producto = Producto;
    },
    async anadirProductoSeleccionado() {
      await this.setProducto(this.producto);
      await this.cargarListadoProductos(4);
      this.lstProductoStock = [];
      this.producto = {};
      this.searchProducto = "";
      this.searchProductoDesc = "";
    }
  },
  components: {
    ProductoForm
  }
};
</script>

<style scoped>
.modasm {
  background: #eff9ff !important;
}
.modasgio {
  background: #c1e7ff !important;
}
.modasjes {
  background: #cdcdcd !important;
}
.modasjun {
  background: #c2ffc0 !important;
}
.modaspau {
  background: #ffdaf0 !important;
}
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
.tblCarrrito {
  width: 100% !important;
  height: auto !important;
  min-height: auto !important;
  max-height: none !important;
  overflow: visible !important;
  border: 1px solid #bdbdbd !important;
  background: #ffffff !important;
}
</style>
<style>
.bgSinStock {
  background: #d50000;
  color: #ffffff;
}

.fila-activa {
  background-color: #d2f5d2 !important;
  border-left: 4px solid #28a745;
}

.stock-box {
  text-align: center;
}

.stock-title {
  font-weight: bold;
  margin-bottom: 5px;
}

.stock-input {
  background-color: #1a237e !important;
  color: #ffffff !important;
  font-weight: bold;
  text-align: center;
  font-size: 18px;
  border: none;
}

.bgMinimo {
  background: #ff3d00;
  color: #ffffff;
}

/* ===== Toolbar (interruptor de búsqueda multiempresa) ===== */
.toolbarProductos {
  gap: 8px;
}
.switchMultiempresa {
  background: #e0e0e0 !important;
  border: 1px solid #bdbdbd !important;
  border-radius: 20px !important;
  padding: 8px 16px 8px 46px !important;
  display: inline-flex !important;
  align-items: center !important;
  font-size: 14px !important;
  line-height: 1.4 !important;
  overflow: hidden !important;
  transition: background 0.2s ease, border-color 0.2s ease;
}
.switchMultiempresa .custom-control-label {
  font-weight: 600;
  cursor: pointer;
  color: #0b6bca;
  margin-bottom: 0 !important;
}
.switchMultiempresa .custom-control-input:checked ~ .custom-control-label {
  color: #1a237e;
}
.switchMultiempresa:has(.custom-control-input:checked) {
  background: #92dffb !important;
  border-color: #4fc3f7 !important;
}

/* ===== Búsqueda multiempresa estilo dropdown ===== */
.productosDropdownWrapper {
  position: relative;
  margin-bottom: 16px;
}
.quickSearchBar {
  margin-bottom: 0;
}
.productosDropdownPanel {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 1050;
  background: #ffffff;
  border-radius: 10px;
  box-shadow: 0 14px 34px rgba(0, 0, 0, 0.18), 0 2px 8px rgba(0, 0, 0, 0.08);
  border: 1px solid #e3e6f0;
  overflow: hidden;
  margin-top: 4px;
}
.productosDropdownHeader {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 14px;
  background: linear-gradient(90deg, #1a237e, #283593);
  color: #ffffff;
  font-size: 0.85rem;
  font-weight: 600;
}
.productosDropdownHeader i {
  margin-right: 6px;
}
.closeDropdownBtn {
  color: #ffffff !important;
  padding: 0 4px;
  line-height: 1;
}
.closeDropdownBtn:hover {
  color: #ffcdd2 !important;
}
.productosDropdownFooter {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 14px;
  background: #f4f6fb;
  border-top: 1px solid #e3e6f0;
  font-size: 0.85rem;
}
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}
.fade-slide-enter,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}

.empresaChip {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.72rem;
  font-weight: 600;
}
.btnAddProd {
  border-radius: 50%;
  width: 28px;
  height: 28px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.tblModal {
  height: 70vh;
  overflow-y: auto;
  overflow-x: auto;
  display: block;
}
.tblModal table {
  border-collapse: collapse;
  width: 100%;
}
.tblModalInline {
  height: 42vh;
  overflow-y: auto;
  overflow-x: auto;
}
.tblModalInline table {
  border-collapse: collapse;
  width: 100%;
}
.tblModalInline table thead th {
  position: sticky;
  top: 0;
  background: #f4f6fb;
  color: #1a237e;
  font-size: 0.78rem;
  z-index: 2;
  border-bottom: 2px solid #d8dcee;
}
.tblModalInline table td {
  font-size: 0.82rem;
  vertical-align: middle;
}
.tblModalInline tbody tr:hover {
  background: #f0f4ff;
  cursor: pointer;
}

.modasm {
  background: #eff9ff;
  font-size: 0.9rem;
}
.modasgio {
  background: #c1e7ff;
  font-size: 0.9rem;
}
.modasjes {
  background: #cdcdcd;
  font-size: 0.9rem;
}
.modasjun {
  background: #c2ffc0;
  font-size: 0.9rem;
}
.modaspau {
  background: #ffdaf0;
  font-size: 0.9rem;
}
.vChip {
  font-weight: bold;
  padding: 10px 5px;
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
.imagen {
  width: 36px !important;
  height: 36px !important;
  border-radius: 6px;
  object-fit: cover;
}
.imagen:hover {
  transform: scale(2.5);
}

.table.tblProductosModal {
  width: 100%;
  border-collapse: collapse;
}

.tblProductosModal thead {
  background-color: #f2f2f2;
  position: sticky;
  top: 0;
  z-index: 1;
}

.tblProductosModal tbody {
  display: block;
  max-height: 800px;
  overflow-y: auto;
  width: 100%;
}

.tblProductosModal thead tr,
.tblProductosModal tbody tr {
  display: table;
  width: 100%;
  table-layout: fixed;
}

.optionSelect {
  background: #e8f5e9 !important;
}
.derecha {
  align-items: right;
  text-align: right !important;
  align-content: right;
}
.w-100 {
  width: 100% !important;
}
.borderIzquierdo {
  border-left: 1px solid #000 !important;
}
.borderIzquierdoPunteado {
  border-left: 1px dashed #000 !important;
}
.montoPresentacion {
  text-align: right;
  padding-right: 10px;
  line-height: 2.5em;
}
.cardPresentacion {
  cursor: pointer;
}
.cardPresentacion:hover {
  cursor: pointer;
  background: #bdbdbd;
}
.text-red {
  color: red !important;
  margin: 50px 0px 50px 0 !important;
}
.inputResponsive {
  border: 1px solid #6a1b9a !important;
}
@media only screen and (max-width: 700px) {
  .ocularMovil {
    display: none;
  }
  .productosDropdownPanel {
    font-size: 0.6rem;
  }
  .btnResponsive {
    max-width: 25%;
    white-space: pre-line;
    padding: 0 !important;
    margin: 0 !important;
  }
  .inputResponsive {
    width: 82% !important;
    margin: 5px 0;
    border: 1px solid #6a1b9a !important;
  }
}
</style>