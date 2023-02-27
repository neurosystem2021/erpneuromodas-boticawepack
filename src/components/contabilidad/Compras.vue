<template>
  <div>
    <b-row>
        <b-col md="4" class="text-center">
            
        </b-col>
        <b-col md="4" class="text-center">
            <h4><b style="text-decoration: underline;">Registro de Compras</b></h4>
        </b-col>
        <b-col md="4" class="text-right">
            <b-button variant="info" @click="cambiarLibro" size="sm"><i class="fas fa-sync fa-sm"></i> Cambiar Libro</b-button>
        </b-col>
    </b-row>
    <hr>
    <b-row>
        <b-col md="2">
            <b-row>
                <b-col>
                    <b-button block size="sm" variant="success" @click="nuevaCompra"><i class="fas fa-plus fa-sm "></i> Nuevo</b-button>
                    <b-button block size="sm" variant="primary"><i class="fas fa-pencil-alt fa-sm"></i> Editar</b-button>
                </b-col>
                <b-col>
                    <b-button block size="sm" variant="warning"><i class="fas fa-check fa-sm"></i> Validar</b-button>
                    <b-button block size="sm" variant="danger"><i class="fas fa-trash fa-sm"></i> Eliminar</b-button>
                </b-col>
            </b-row>
        </b-col>
        <b-col md="4">
            <b-row>
                <b-col md="8">
                    <b-row>
                        <label>Impresion - Varios Formatos</label>
                    </b-row>
                    <b-row>

                    </b-row>
                </b-col>
                <b-col md="4">
                    <b-form-radio-group
                    v-model="selected"
                    :options="options"
                    class="mb-3"
                    value-field="item"
                    text-field="name"
                    disabled-field="notEnabled"
                    ></b-form-radio-group>
                </b-col>
            </b-row>
        </b-col>
        <b-col md="3">
            <b-row>
                <b-col class="text-center">
                    <label>Generar Asientos</label>
                    <b-button block size="sm">Asientos para balance</b-button>
                    <b-button block size="sm">Asientos para PLE</b-button>
                </b-col>
                <b-col>
                    <b-form-group>
                        <b-form-select v-model="mesSelect" :options="itemsMes" v-on:change="setMes($event)" class="mb-3" size="md" required/>
                    </b-form-group>
                </b-col>
            </b-row>
        </b-col>
    </b-row>
    <br>
    <!--<b-row>
        <b-col md="6" class="my-1">
            <b-form-group horizontal label="Buscar" class="mb-0">
            <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                <b-input-group-append>
                <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                </b-input-group-append>
            </b-input-group>
            </b-form-group>
            <br />
        </b-col>
        <b-col md="6" class="my-1">
            <b-row>
            <b-col md="4">
            <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Artículos</p>
            </b-col>
            </b-row>
        </b-col>
    </b-row>-->
    <b-row style="overflow-x: auto;">
        <b-col md="12">
            <table class="table table-striped" style="width:100%; table-layout: auto;">
                <thead class="cabeceraFila">
                    <tr>
                        <th style="min-width: 100px">F. Emi.</th>
                        <th style="min-width: 20px">Tipo</th>
                        <th style="min-width: 50px">Serie</th>
                        <th style="min-width: 50px">Numero</th>
                        <th style="min-width: 200px">Razón Social</th>
                        <th style="min-width: 70px">Base Imp</th>
                        <th style="min-width: 70px">IGV</th>
                        <th style="min-width: 70px">Base Imp</th>
                        <th style="min-width: 70px">IGV</th>
                        <th style="min-width: 70px">Base Imp</th>
                        <th style="min-width: 70px">IGV</th>
                        <th style="min-width: 70px">No Grav.</th>
                        <th style="min-width: 70px">Otros</th>
                        <th style="min-width: 70px">Total</th>
                        <th>Ord</th>
                        <th>Mon</th>
                        <th>Cc</th>
                        <th>EB</th>
                        <th>TipoCam</th>
                        <th>Reg</th>
                        <th>Total</th>
                        <th>IGV</th>
                        <th>Base Imp</th>
                        <th>No Grav</th>
                        <th style="min-width: 100px">F. Ven.</th>
                        <th>Periodo</th>
                        <th>Caja</th>
                        <th style="min-width: 100px">F. Cancel</th>
                        <th>Cta. Caja</th>
                        <th>Caja Cancel</th>
                        <th>Asto</th>
                        <th>Letra</th>
                    </tr>
                </thead>
                <tbody class="infoFilas">
                    <!--<tr>
                        <td>06-03-2019</td>
                        <td>01</td>
                        <td>F001</td>
                        <td>58</td>
                        <td>NAVARRO NARDINI S.A.C. </td>
                        <td>100.00</td>
                        <td>18.00</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>-->
                </tbody>
                <tfoot class="infoFilas">
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                        <td>0.00</td>
                    </tr>
                </tfoot>
            </table>
        </b-col>
    </b-row>
        <b-modal id="modalCompras" size="lg" ref="modalCompras" hide-footer hide-header :no-close-on-esc="true" no-close-on-backdrop>
            <b-form @submit="guardarCompra">  
            <div class="d-block">
                <b-row>
                    <b-col md="5">
                        <b-row>
                            
                        </b-row>
                        <b-row class="filaOperacion">
                            <b-col>
                                <b-input-group prepend="Tipo de Operación" class="mb-1">
                                    <autocomplete
                                        required="required"
                                        ref="inputTipoOperacion"
                                        :url="API + 'contabilidad/operaciones'"
                                        :customParams="{'IdLibro': libroIdSelec }"
                                        :onShouldRenderChild="renderChildOpe"
                                        anchor="TipoOperacion"
                                        initValue=""
                                        label="writer"
                                        placeholder=""
                                        :on-select="setOperacion"
                                        :onFocus="focusAutocomplete.bind(null, 'inputTipoOperacion')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-input-group>
                                <b-input-group prepend="Detalle de Operación">
                                    <autocomplete
                                        required="required"
                                        ref="inputTipoOperacionDet"
                                        :url="API + 'contabilidad/operaciones/detalle'"
                                        :customParams="{'IdTipoOpe': operacion.IdTipoOperacion }"
                                        :onShouldRenderChild="renderChildOpeDet"
                                        anchor="TipoOperacionDet"
                                        initValue=""
                                        label="writer"
                                        placeholder=""
                                        :on-select="setOperacionDet"
                                        :onFocus="focusAutocomplete.bind(null, 'inputTipoOperacionDet')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                    <b-button variant="info" ref="buttonConfig" size="sm" class="ml-1" :disabled="operacionDet.IdTipoOperacionDet==0"><i class="fas fa-cog fa-sm"></i></b-button>
                                </b-input-group>
                            </b-col>
                        </b-row>
                        <hr>
                        <b-row class="filaDocumento">
                            <b-col>
                                <h6>Documento: </h6>
                                <b-row>
                                    <b-col md="6">
                                        <b-input-group prepend="F. Emisión" class="mb-1">
                                            <b-form-input ref="inputFechaEmision" v-model="FechaDoc" type="date" size="md" required/>
                                        </b-input-group>
                                    </b-col>
                                </b-row>
                                <b-input-group prepend="Tipo Doc." class="mb-1">
                                    <autocomplete
                                        required="required"
                                        ref="inputTipoComprob"
                                        :url="API + 'contabilidad/comprobantes'"
                                        :customParams="{'IdTipoOpe': operacion.IdTipoOperacion }"
                                        :onShouldRenderChild="renderChildTipoComprob"
                                        anchor="Comprobante"
                                        initValue=""
                                        label="writer"
                                        placeholder=""
                                        :on-select="setTipoComprob"
                                        :onFocus="focusAutocomplete.bind(null, 'inputTipoComprob')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-input-group>
                                <b-row>
                                    <b-col md="4">
                                        <b-input-group prepend="Serie" class="mb-1">
                                            <b-form-input ref="inputSerie" type="text" v-model="Comprobante.Serie" style="text-transform: uppercase" maxlength="4" size="md" autocomplete="off" required/>
                                        </b-input-group>
                                    </b-col>
                                    <label> - </label>
                                    <b-col md="5">
                                        <b-input-group prepend="Número" class="mb-1">
                                            <b-form-input type="number" v-model="Comprobante.Numero" size="md" min="0" step="1" autocomplete="off" required/>
                                        </b-input-group>
                                    </b-col>
                                </b-row>
                            </b-col>
                        </b-row>
                        <hr>
                        <b-row class="filaDatos">
                            <b-col>
                                <h6>Razón Social: </h6>
                                <b-row>
                                    <b-col md="8">
                                        <b-input-group prepend="RUC" class="mb-1">
                                            <b-form-input type="text" maxlength="11" size="md"  v-model="proveedor.Ruc" @change="validarTipoDocRuc" required autocomplete="off"/>
                                            <b-button size="sm" @click="validarTipoDocRuc" class="ml-1" variant="primary"><i class="fas fa-search fa-sm"></i></b-button>
                                        </b-input-group>
                                    </b-col>
                                    <b-col md="4">
                                        <b-input-group prepend="Tipo Doc" class="mb-1">
                                            <b-form-input type="number" disabled size="md" v-model="TipoDocRuc" autocomplete="off"/>
                                        </b-input-group>
                                    </b-col>
                                </b-row>

                                <b-input-group prepend="Nom / Razón S." class="mb-1">
                                    <b-form-input type="text" size="md" readonly v-model="proveedor.Proveedor" autocomplete="off"/>
                                </b-input-group>
                                <b-input-group prepend="Dirección" class="mb-1">
                                    <b-form-input type="text" size="md" style="text-transform: uppercase" v-model="proveedor.Direccion" autocomplete="off"/>
                                </b-input-group>
                            </b-col>
                        </b-row>
                        <hr>
                        <b-row class="filaMoneda">
                            <b-col>
                                <h6>Tipo Cambio: </h6>
                                <b-row>
                                    <b-col md="5">
                                        <b-input-group prepend="Moneda" class="mb-1">
                                            <autocomplete
                                                required="required"
                                                ref="inputTipoMoneda"
                                                :url="API + 'contabilidad/moneda'"
                                                anchor="Moneda"
                                                initValue="NUEVOS SOLES"
                                                label="writer"
                                                placeholder=""
                                                :on-select="setTipoMoneda"
                                                :onFocus="focusAutocomplete.bind(null, 'inputTipoMoneda')"
                                                :classes="{input: 'form-control'}">
                                            </autocomplete>
                                        </b-input-group>
                                    </b-col>
                                    <b-col md="4">
                                        <b-input-group prepend="Tipo C." class="mb-1">
                                            <input class="form-control" type="number" step="0.0001" v-model="Moneda.TipoCambio" autocomplete="off"/>
                                        </b-input-group>
                                    </b-col>
                                    <b-col md="3">
                                        <b-input-group prepend="IGV" class="mb-1">
                                            <input class="form-control" type="number" step="0.1" v-model="IgvPorcentaje" @input="calcularMontos" autocomplete="off"/>
                                        </b-input-group>
                                    </b-col>
                                </b-row>

                            </b-col>
                        </b-row>
                    </b-col>
                    <b-col md="7">
                        <b-row>
                            <b-col md="5">
                                <table class="tablaMontos">
                                    <tbody>
                                        <tr>
                                            <td style="width: 5%;">Total</td>
                                            <td style="width: 50%;"><input class="form-control" type="number" v-model="Total" @input="calcularMontos" step="0.00001"   autocomplete="off"/></td>
                                            <td style="width: 30%;"><input class="form-control" type="text" v-model="CuentasCalculo.Total"  pattern="[0-9]+"  autocomplete="off"/></td>
                                            <td style="width: 15%;"><input class="form-control" type="text" v-model="CuentasCalculo.TotalOpc" style="font-weight:bold; text-transform:uppercase" maxlength="1"  autocomplete="off"/></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 5%;">I.G.V.</td>
                                            <td style="width: 50%;"><input class="form-control" type="number" v-model="Igv" step="0.00001" autocomplete="off"/></td>
                                            <td style="width: 30%;"><input class="form-control" type="text" v-model="CuentasCalculo.Igv" pattern="[0-9]+"  autocomplete="off"/></td>
                                            <td style="width: 15%;"><input class="form-control" type="text" v-model="CuentasCalculo.IgvOpc" style="font-weight:bold; text-transform:uppercase" maxlength="1"  autocomplete="off"/></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 5%;">Otros</td>
                                            <td style="width: 50%;"><input class="form-control" type="number" v-model="Otros" step="0.00001" autocomplete="off"/></td>
                                            <td style="width: 30%;"><input class="form-control" type="text" value="40189" pattern="[0-9]+"  autocomplete="off"/></td>
                                            <td style="width: 15%;"><input class="form-control" type="text" value="D" style="font-weight:bold; text-transform:uppercase" maxlength="1"  autocomplete="off"/></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 5%;">B. Imp</td>
                                            <td style="width: 50%;"><input class="form-control" type="number" v-model="BImp" step="0.00001" autocomplete="off"/></td>
                                            <td style="width: 30%;"><input class="form-control" type="text" v-model="CuentasCalculo.BImp" pattern="[0-9]+"  autocomplete="off"/></td>
                                            <td style="width: 15%;"><input class="form-control" type="text" v-model="CuentasCalculo.BImpOpc" style="font-weight:bold; text-transform:uppercase" maxlength="1"  autocomplete="off"/></td>
                                        </tr>
                                        <tr>
                                            <td style="width: 5%;">No Grav.</td>
                                            <td style="width: 50%;"><input class="form-control" type="number" v-model="NoGrav" step="0.00001" autocomplete="off"/></td>
                                            <td style="width: 30%;"><input class="form-control" type="text" v-model="CuentasCalculo.Exo" pattern="[0-9]+"  autocomplete="off"/></td>
                                            <td style="width: 15%;"><input class="form-control" type="text" v-model="CuentasCalculo.ExoOpc" style="font-weight:bold; text-transform:uppercase" maxlength="1"  autocomplete="off"/></td>
                                        </tr>
                                        <tr style="border-top: 1px solid black">
                                            <td style="width: 5%;">I.S.C.</td>
                                            <td style="width: 50%;"><input class="form-control" type="number" v-model="Isc" step="0.00001" autocomplete="off"/></td>
                                            <td style="width: 30%;"><input class="form-control" type="text" v-model="CuentasCalculo.Isc" pattern="[0-9]+"  autocomplete="off"/></td>
                                            <td style="width: 15%;"><input class="form-control" type="text" v-model="CuentasCalculo.IscOpc" style="font-weight:bold; text-transform:uppercase" maxlength="1"  autocomplete="off"/></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </b-col>
                            <b-col md="7">
                                <h6>Tipo de Costo: {{ TipoCosto[0].NombreSub  }} </h6>
                                <table class="tablaCosteo">
                                    <tbody>
                                        <tr>
                                            <td colspan="5" align="center"><h6>Asiento - Destino Almacén</h6></td>
                                        </tr>
                                        <tr>
                                            <td width="20%" class="noArrow"><input class="form-control" v-on:keyup="validarTecla" type="number" autocomplete="off"/></td>
                                            <td width="30%"><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td width="20%" class="noArrow"><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td width="30%"><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <br>
                                <table class="tablaCosteo">
                                    <tbody>
                                        <tr>
                                            <td colspan="5" align="center"><h6>Asiento - Destino Costo Flexible</h6></td>
                                        </tr>
                                        <tr>
                                            <td width="15%"><b-form-select size="md" class="selectCosteo" :options="CentroCosto"/></td>
                                            <td class="noArrow"><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td align="right"><b-button size="sm" variant="primary">Modif.</b-button></td>
                                        </tr>
                                        <tr>
                                            <td><b-form-select size="md" class="selectCosteo" :options="CentroCosto"/></td>
                                            <td class="noArrow"><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td><b-form-select size="md" class="selectCosteo" :options="CentroCosto"/></td>
                                            <td class="noArrow"><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td><b-form-select size="md" class="selectCosteo" :options="CentroCosto" v-on:change="setMotivo($event)"/></td>
                                            <td class="noArrow"><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td></td>
                                        </tr>
                                        <tr style="border-top: 1px solid black">
                                            <td></td>
                                            <td><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td></td>
                                            <td><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                            <td><input class="form-control" type="number" step="0.00001" autocomplete="off"/></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </b-col>
                        </b-row>
                        <hr>
                        <b-row class="filaFormaPago">
                            <b-col md="4">
                                <b-input-group prepend="Forma de Pago" class="mb-1">
                                    <autocomplete
                                        required="required"
                                        ref="inputFormaPago"
                                        :url="API + 'contabilidad/forma'"
                                        anchor="FormaPago"
                                        initValue="CONTADO"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setFormaPago"
                                        :onFocus="focusAutocomplete.bind(null, 'inputFormaPago')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-input-group>
                            </b-col> 
                           <b-col md="4">
                                <b-input-group prepend="F. Venc" class="mb-1">
                                    <b-form-input ref="inputFechaVenc" v-model="FechaVen" type="date" size="md" required/>
                                </b-input-group>
                           </b-col>
                        </b-row>
                        <b-row class="filaFormaPago" v-if="FormaPago.IdFormaPago!=2 && FormaPago.IdFormaPago!=3">
                            <b-col md="8">
                                <b-input-group prepend="Modo de Pago" class="mb-1">
                                    <autocomplete
                                        required="required"
                                        ref="inputModoPago"
                                        :url="API + 'contabilidad/modopago'"
                                        anchor="ModoPago"
                                        initValue="EFECTIVO - MOV. EFECTIVO"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setModoPago"
                                        :onFocus="focusAutocomplete.bind(null, 'inputModoPago')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-input-group>
                            </b-col>
                        </b-row>
                    </b-col>
                </b-row>
                <hr>
                <b-row class="text-center">
                    <b-col align-h="center" md="12" class="text-center">
                        <b-button variant="success" size="md" type="submit"><i class="fas fa-save fa-sm"></i> Guardar</b-button>
                        <b-button variant="danger" size="md" @click="cerrarModal"><i class="fas fa-times fa-sm"></i> Salir</b-button>
                    </b-col>
                </b-row>
            </div>
            </b-form>
        </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'

export default {
    mounted() {
        //this.cargarDatos();
        this.obtenerTipoCosteo()
    },
    data () {
        return {
            API: config.API,
            itemsMes: [
                { value: 1, text: 'ENERO' },
                { value: 2, text: 'FEBRERO' },
                { value: 3, text: 'MARZO' },
                { value: 4, text: 'ABRIL' },
                { value: 5, text: 'MAYO' },
                { value: 6, text: 'JUNIO' },
                { value: 7, text: 'JULIO' },
                { value: 8, text: 'AGOSTO' },
                { value: 9, text: 'SEPTIEMBRE' },
                { value: 10, text: 'OCTUBRE' },
                { value: 11, text: 'NOVIEMBRE' },
                { value: 12, text: 'DICIEMBRE' }
                    ],
            mesSelect: 1,
            selected: null,
            options: [
                { item: 1, name: 'x Fecha' },
                { item: 2, name: 'x Tipo' },
                { item: 3, name: 'x Orden' },
            ],
            operaciones: [],
            operacionesDet: [],
            operacion: {
                IdTipoOperacion: 0,
                TipoOperacion: ''
            },
            operacionDet: {
                IdTipoOperacionDet: 0,
                TipoOperacionDet: ''
            },
            TipoDocRuc: 0,
            proveedor: {
                IdProveedor: null,
                Proveedor:'',
                Ruc:'',
                Direccion:''
            },
            Moneda: {
                Moneda: 'NUEVOS SOLES',
                TipoCambio: 1
            },
            Comprobante: {
                IdComprobante:null,
                Comprobante: '',
                CodSunat: '',
                Serie: '',
                Numero: null,
            },
            CuentasCalculo: {
                BImp:'',
                BImpOpc:'',
                CalculaIgv: 0,
                Exo:'',
                ExoOpc:'',
                GravadosConIgv:0,
                IdOperacionDetCuenta:null,
                IdTipoOperacionDet:null,
                Igv:'',
                IgvOpc:'',
                Isc:'',
                IscOpc:'',
                Total:'',
                TotalOpc:'',
            },
            FormaPago: {
                IdFormaPago:1,
                FormaPago:'CONTADO',
                EsCredito:0
            },
            FechaVen: this.$moment().format("YYYY-MM-DD"),
            FechaDoc: this.$moment().format("YYYY-MM-01"),
            ModoPago: {
                IdModoPago: 1,
                ModoPago: 'EFECTIVO - MOV. EFECTIVO'
            },
            IgvPorcentaje: 18.00,
            Total:0,
            Igv:0,
            Otros:0,
            BImp:0,
            NoGrav:0,
            Isc:0,
            TipoCosto:[
                {
                    NombreSub: ''
                }
            ],
            CentroCosto:[]
        }
    },
    watch: {

    },
    props: {
        libroNombreSelec: String,
        libroIdSelec: Number
    },
    computed: {

    },
    methods: {
        validarTecla(e){
            if(e.key=='ArrowDown' || e.key=='ArrowUp'){
                e.preventDefault()
                e.stopPropagation()
                return false
            }
            console.log(e);
        },
        cambiarLibro(){
            this.$emit('cambioLibro')
        },
        setMes(mes){

        },
        focusAutocomplete (ref) {
            // this.$refs[ref].type = ""
            this.$refs[ref].handleDoubleClick()
        },
        cerrarModal(){
            this.$refs['modalCompras'].hide()
        },
        obtenerTipoCosteo(){
            this.axios.get(this.API + 'contabilidad/config/costo')
            .then((r) => {
                if(r.data.success){
                    this.TipoCosto = r.data.tipo
                    this.CentroCosto = r.data.centro
                }
            })
            .catch((error)=>{

            })    
        },
        initModal(){
            this.operacion = {
                IdTipoOperacion: 0,
                TipoOperacion: ''
            }
            this.operacionDet= {
                IdTipoOperacionDet: 0,
                TipoOperacionDet: ''
            }
            this.TipoDocRuc= 0,
            this.proveedor ={
                IdProveedor: null,
                Proveedor:'',
                Ruc:'',
                Direccion:''
            }
            this.Moneda= {
                Moneda: 'NUEVOS SOLES',
                TipoCambio: 1
            }
            this.Comprobante = {
                IdComprobante:null,
                Comprobante: '',
                CodSunat: '',
                Serie: '',
                Numero: null,
            }
            this.CuentasCalculo = {
                BImp:'',
                BImpOpc:'',
                CalculaIgv: 0,
                Exo:'',
                ExoOpc:'',
                GravadosConIgv:0,
                IdOperacionDetCuenta:null,
                IdTipoOperacionDet:null,
                Igv:'',
                IgvOpc:'',
                Isc:'',
                IscOpc:'',
                Total:'',
                TotalOpc:'',
            }
            this.FormaPago = {
                IdFormaPago:1,
                FormaPago:'CONTADO',
                EsCredito:0
            }
            this.ModoPago = {
                IdModoPago: 1,
                ModoPago: 'EFECTIVO - MOV. EFECTIVO'
            }

            this.$refs.inputTipoOperacionDet.setValue('')
            this.$refs.inputTipoOperacion.setValue('')
            this.$refs.inputTipoComprob.setValue('')
            this.$refs.inputTipoMoneda.setValue('NUEVOS SOLES')
            this.$refs.inputFormaPago.setValue('CONTADO')
            this.$refs.inputModoPago.setValue('EFECTIVO - MOV. EFECTIVO')
        },
        renderChildOpe(data) {
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
                <table id="table-render" style="width: 300px; text-align: left;  padding:0; margin:0"">
                    <tbody>
                    <tr style="padding:0; margin:0">
                        <td><b style="font-size: 0.75rem">${data.TipoOperacion}</b></td>
                    </tr>
                    </tbody>
                </table>   
                </div>
                `
        },
        renderChildOpeDet(data) {
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
                <table id="table-render" style="width: 500px; text-align: left;  padding:0; margin:0"">
                    <tbody>
                    <tr style="padding:0; margin:0">
                        <td><b style="font-size: 0.75rem">${data.TipoOperacionDet}</b></td>
                    </tr>
                    </tbody>
                </table>   
                </div>
                `
        },
        renderChildTipoComprob(data) {
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
                <table id="table-render" style="width: 500px; text-align: left;  padding:0; margin:0"">
                    <tbody>
                    <tr style="padding:0; margin:0">
                        <td style="width: 470px;"><b style="font-size: 0.75rem">${data.Comprobante}</b></td>
                        <td style="width: 30px;"><b style="font-size: 0.75rem">${data.CodSunat}</b></td>
                    </tr>
                    </tbody>
                </table>   
                </div>
                `
        },
        cargarDatos(){
            this.axios.get(this.API + 'contabilidad/operaciones',{
                params: { 
                    IdLibro: this.libroIdSelec,
                }
            })
            .then((r) => {
                console.log(r);
            })
            .catch((error)=>{

            })
        },
        setOperacion(obj){
            this.operacion = obj
            this.operacionDet = {
                IdTipoOperacionDet: 0,
                TipoOperacionDet: ''
            }
            this.$refs.inputTipoOperacionDet.setValue('')
            this.$refs.buttonConfig.focus()
        },
        setOperacionDet(obj){
            this.operacionDet = obj
            this.$refs.inputFechaEmision.$refs.input.focus()
            this.consultaCuentasDet(obj.IdTipoOperacionDet)
        },
        consultaCuentasDet(id){

            this.CuentasCalculo = {
                BImp:'',
                BImpOpc:'',
                CalculaIgv: 0,
                Exo:'',
                ExoOpc:'',
                GravadosConIgv:0,
                IdOperacionDetCuenta:null,
                IdTipoOperacionDet:null,
                Igv:'',
                IgvOpc:'',
                Isc:'',
                IscOpc:'',
                Total:'',
                TotalOpc:'',
            }

            this.axios.get(this.API + 'contabilidad/operaciondet/cuentas', {
                params: {
                    IdTipoOperacionDet: id
                }
            }).then((response) => {
                if(response.data.success){
                    this.CuentasCalculo = response.data.data
                    this.calcularMontos()
                }
            })
            .catch((error) => {
                this.$toasted.show('ERROR: No se encontró el RUC')        
                console.log(error)
            })     
        },
        validarTipoDocRuc(){
            let docFinal = (''+this.proveedor.Ruc).trim();
            this.TipoDocRuc = 0;

            if(docFinal.length==8){
                this.TipoDocRuc = 1;
            }

            if(docFinal.length==11){
                this.TipoDocRuc = 6
                this.axios.get(this.API + 'contabilidad/proveedor', {
                    params: {
                        type: 'RUC',
                        Ruc: docFinal
                    }
                }).then((response) => {
                    var data = response.data
                    if(data.success==false){
                        this.$toasted.error('ERROR: No se encontró el RUC')   
                        this.proveedor.IdProveedor = null
                        this.proveedor.Proveedor = ''
                        this.proveedor.Direccion = ''
                    }else if(data.base){
                        this.$toasted.info('Proveedor encontrado en BASE DE DATOS, se llenarán sus datos', {duration:3000})
                        this.proveedor.IdProveedor = data.data.IdProveedor
                        this.proveedor.Proveedor = data.data.Proveedor
                        this.proveedor.Direccion = data.data.Direccion
                    }else if(data.razonSocial){
                        this.$toasted.info('Proveedor encontrado en SUNAT, se llenarán sus datos', {duration:3000})
                        this.proveedor.IdProveedor = null
                        this.proveedor.Proveedor = data.razonSocial
                        this.proveedor.Direccion = data.direccion
                    }else{
                        this.$toasted.error('ERROR: Complete el RUC correctamente')
                        this.proveedor.IdProveedor = null
                        this.proveedor.Proveedor = ''
                        this.proveedor.Direccion = ''
                    }
                })
                .catch((error) => {
                    this.$toasted.show('ERROR: No se encontró el RUC')        
                    console.log(error)
                })
            }

            return;
        },
        setTipoMoneda(obj){
            this.Moneda = obj
        },
        setTipoComprob(obj){
            this.Comprobante.IdComprobante = obj.IdComprobante
            this.Comprobante.Comprobante = obj.Comprobante
            this.Comprobante.CodSunat = obj.CodSunat
            this.$refs.inputSerie.$refs.input.focus()
        },
        setFormaPago(obj){
            this.FormaPago = obj
            this.$refs.inputFechaVenc.$refs.input.focus()
        },
        setModoPago(obj){
            this.ModoPago = obj
        },
        nuevaCompra(){
            this.initModal()
            this.$refs['modalCompras'].show()
        },
        calcularMontos(){
            if(this.operacionDet.IdTipoOperacionDet>0 && this.CuentasCalculo.IdOperacionDetCuenta!=null){

                if(this.CuentasCalculo.CalculaIgv==1){
                    this.BImp = Math.round((this.Total / (1+this.IgvPorcentaje/100))* 100) / 100
                    this.Igv = Math.round((this.Total - this.BImp)* 100) / 100
                    this.NoGrav = 0  
                }else{
                    this.Igv = 0
                    this.NoGrav = this.Total  
                    this.BImp = 0
                }
            }
        },
        editarCompra(){

        },
        guardarCompra(){
            let data = {
                FechaDoc:this.FechaDoc,
                Anio: 2023,
                Mes: this.mesSelect,
                IdComprobante: this.Comprobante.IdComprobante,
                IdTipoOperacion: this.operacion.IdTipoOperacion,
                IdTipoOperacionDet: this.operacionDet.IdTipoOperacionDet,
                IdProveedor: this.proveedor.IdProveedor,
                Proveedor: this.proveedor.Proveedor,
                Ruc: this.proveedor.Ruc,
                Direccion: this.proveedor.Direccion,
                IdFormaPago: this.FormaPago.IdFormaPago,
                IdModoPago: this.ModoPago.IdModoPago,
                FechaVen: this.FechaVen,
                Total: this.Total,
                Igv: this.Igv,
                Otros: this.Otros,
                BImp: this.BImp,
                NoGrav: this.NoGrav,
                Isc: this.Isc,
                IdLibro: this.libroIdSelec,
                Serie: this.Comprobante.Serie,
                Numero: this.Comprobante.Numero,
                Moneda: this.Moneda.Moneda,
                TipoCambio: this.Moneda.TipoCambio,
                IgvPorcentaje: this.IgvPorcentaje

            }

            this.axios.post(this.API + 'contabilidad/registro', {
                data,
            }).then(r => {
                if (r.data) {
                    this.$toasted.show('Registro Exitoso')
                    this.$refs['modalCompras'].hide()    
                }
            }).catch((error) => {
               
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            })
        }
    },
    components: {
    
    }
}
</script>

<style>
#modalCompras .modal-dialog{
    max-width: 90% !important;
}
.filaOperacion .input-group-text{
    font-weight: bold;
    font-size: 0.75rem;
}
.filaOperacion input {
    font-weight: bold;
    font-size: 0.75rem;
}
.filaOperacion .autocomplete-anchor-text {
    font-size: 0.75rem
}

.filaDocumento .input-group-text{
    font-weight: bold;
    font-size: 0.75rem;
}
.filaDocumento input {
    font-weight: bold;
    font-size: 0.75rem;
}
.filaDocumento .autocomplete-anchor-text {
    font-size: 0.75rem
}

.filaDatos .input-group-text{
    font-weight: bold;
    font-size: 0.75rem;
}
.filaDatos input {
    font-weight: bold;
    font-size: 0.75rem;
}
.filaDatos .autocomplete-anchor-text {
    font-size: 0.75rem
}

.filaMoneda .input-group-text{
    font-weight: bold;
    font-size: 0.75rem;
}
.filaMoneda input {
    font-weight: bold;
    font-size: 0.75rem;
}
.filaMoneda .autocomplete-anchor-text {
    font-size: 0.75rem
}

.tablaMontos * {
    font-size: 0.8rem;
}
.tablaMontos input {
    border-radius: 0% !important;
}

.tablaCosteo * {
    font-size: 0.8rem;
}
.tablaCosteo input {
    border-radius: 0% !important;
}

.filaFormaPago .input-group-text{
    font-weight: bold;
    font-size: 0.75rem;
}
.filaFormaPago input {
    font-weight: bold;
    font-size: 0.75rem;
}
.filaFormaPago .autocomplete-anchor-text {
    font-size: 0.75rem
}

.noArrow input::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
.noArrow input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Firefox */
.noArrow input[type=number] {
  -moz-appearance: textfield;
}

.selectCosteo{
    height: auto !important;
    font-weight: bold !important;
}
.selectCosteo option{
    font-weight: bold;
}
</style>
