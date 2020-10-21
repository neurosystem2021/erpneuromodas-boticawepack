<template>
  <div>
      <b-container class="bv-example-row">
          

        <b-row>
          <b-col md="12" class="my-1">
            <b-row>
              <b-col md="2">
                <b-form-group>
                  <label>Almacen:</label>
                  <autocomplete
                      required="required"
                      ref="inputAlmacen"
                      :url="API + 'almacenes'"
                      anchor="Almacen"
                      initValue=""
                      label="writer"
                      placeholder=""
                      :on-select="setAlmacen" 
                      :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-form-group>
                
                
              </b-col>
              <b-col md="4">
                <!--<b-form-group label="Mostrar">
                  <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
                </b-form-group>-->
                

                <!--<b-form-group label="Limpiar Filtros">
                  <b-button :disabled="!filter" @click="limpiarFiltros">Limpiar</b-button>
                </b-form-group>-->
                
                <b-form-group v-show="isBoleta">
                  <label>Fecha Referencia:</label>
                  <b-form-input
                    v-model="fechaReferencia"
                    type="date"
                    placeholder=""
                    @input="setFechaReferencia"></b-form-input>
                </b-form-group>
                
                <b-form-group v-show="!isBoleta">
                  <label>Fecha Inicio:</label>
                  <b-form-input v-model="fechaInicio"
                    type="date"
                    placeholder=""
                    @input="val => generateFilter('fechaInicio', val) "></b-form-input>
                </b-form-group>

                <b-form-group v-show="!isBoleta">
                  <label>Fecha Fin:</label>
                  <b-form-input v-model="fechaFin"
                    type="date"
                    placeholder=""
                    @input="val => generateFilter('fechaFin', val) "></b-form-input>
                </b-form-group>
              </b-col>
              <b-col md="4">
                <!--<div class="total-ventas">
                  S/ {{ ventasAcumuladas || '0.00' }}
                </div>-->
                <!--<b-form-group label="Tipo Documento">
                <b-form-select v-model="tipoDoc.IdTipoDoc" :options="optionsTipoDoc" @input="selectTipoDoc" />
                </b-form-group>-->
                  <b-form-group>
                      <label>Tipo Comprobante:</label>
                      <autocomplete
                          required="required"
                          ref="inputTipoVenta"
                          :url="API + 'ventas/tipos/electronicos'"
                          initValue=""
                          anchor="TipoDoc"
                          label="writer"
                          placeholder=""
                          :on-select="setTipoDoc" 
                          :onFocus="focusAutocomplete.bind(null, 'inputTipoVenta')"
                          :classes="{input: 'form-control'}">
                      </autocomplete>
                  </b-form-group>

                
                
              </b-col>
              <b-col md="2">
                  <b-form-group label="Estado">
                  <b-form-select v-model="estadoVenta" :options="optionsEstado" @input="$refs.tablaVentas.refresh()" />
                </b-form-group>
                <br />
              </b-col>
            </b-row>
          </b-col>
        </b-row>
        <b-row>

          
          <b-col md="12">
            <b-table small
              ref="tablaVentas"
              :filter="filter"
              :sort-by.sync="sortBy"
              :sort-desc.sync="sortDesc"
              striped
              hover
              id="tabla-ventas" 
              :busy.sync="isBusy" 
              :items="myProvider" 
              :fields="fields"
              :per-page="perPage"
              :current-page="currentPage">

                <template slot="detalles" slot-scope="row">
                  <b-button size="sm" @click.stop="row.toggleDetails" class="mr-2">
                    {{ row.detailsShowing ? '-' : '+'}}
                  </b-button>
                </template>

                <template slot="row-details" slot-scope="row">

                  <b-card>
                    <b-row class="mb-2">
                  <table class="table b-table table-sm prod-detalle">
                    <thead>
                      <tr>
                        <th>Codigo</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Descuento</th>
                        <th v-if="row.item.TieneIgv">Subtotal</th>
                        <th v-if="row.item.TieneIgv">IGV</th>
                        <th>Total</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in getDetalles(row.item.idDocVenta)" :key="index">
                        <td>{{ item.CodigoBarra }}</td>
                        <td>{{ item.Producto }}</td>
                        <td>{{ item.Cantidad }}</td>
                        <td>{{ item.Precio }}</td>
                        <td>{{ item.Descuento }}</td>
                        <td v-if="row.item.TieneIgv">
                          {{ item.Subtotal - (item.Subtotal * 0.18) | redondear }}
                        </td>
                        <td v-if="row.item.TieneIgv">
                          {{ item.Subtotal * 0.18 | redondear}}
                        </td>
                        <td>{{ item.Subtotal | redondear }}</td>
                      </tr>
                    </tbody>
                  </table>

                  <b-button style="margin-left: 30px;" variant="primary" size="sm" @click="moverAEmitidos(row.item)">Mover a emitidos</b-button>
                  <b-button style="margin-left: 10px;" variant="primary" size="sm" @click="cambiarAAnonimo(row.item)">Cambiar a cliente anónimo</b-button>
                    </b-row>
                  </b-card>
                </template>

                <template slot="estado" slot-scope="data">
                  <div style="display:flex;">
                      <img v-b-tooltip.hover  :title="getStatus(data.item.idDocVenta).msj_sunat" placement="right" v-if="getStatus(data.item.idDocVenta).image && estadoVenta != 2" :src="'static/' + getStatus(data.item.idDocVenta).image" width="32" height="32"/>
                      <img v-b-tooltip.hover ref="imgSuccess"  :title="data.item.Msj_sunat" v-if="data.item.Estado == 2" src="static/success.png" width="32" height="32"/>
                      &nbsp;&nbsp;
                      <img v-b-tooltip.hover  :title="data.item.Msj_sunat + '\nDocumento Anulado'" v-if="estadoVenta == 0" src="static/cancel.png" height="32"/>
                      <b-button v-if="estadoVenta == 2" :disabled="loading" variant="danger" size="sm" @click="eliminarElectronico(data.item)">Anular</b-button>
                      &nbsp;&nbsp;
                      <b-button variant="warning" @click="regenerarXML(data.item)">XML</b-button>
                      <b-button v-if="estadoVenta == 2" @click="regenerarPDF(data.item)">PDF</b-button>
                      &nbsp;&nbsp;

                      <!--<b-button :id="'popover-nota' + data.index" v-if="estadoVenta == 2 && tipoDoc.CodSunat == '01'" :disabled="loading" variant="primary" size="sm" >Nota Credito</b-button>
                      <b-popover :target="'popover-nota' + data.index" v-if="estadoVenta == 2 && tipoDoc.CodSunat == '01'" title="Emitir Nota Credito" placement="top">
                        <form @submit.prevent="emitirNotaCredito(data.item)" >
                          <b-form-select v-model="notaIdMotivo" :options="itemsMotivoNota" class="mb-3" size="sm" required/>
                          <input type="text" v-model="notaCredito.serie" class="form-control" placeholder="Serie" required/>
                          <input type="text" v-model="notaCredito.numero" class="form-control" placeholder="Numero" required/>
                          <b-button type="submit" variant="primary" size="sm" :disabled="loading">Emitir</b-button>
                        </form>
                      </b-popover>-->
                  </div>
                </template>

                <template slot="consulta" slot-scope="data">
                  <div >
                    <div style="width: 150px;">
                      <img id="captcha" style="width: 80px; float:left;" v-if="imgCaptcha" src="http://www.sunat.gob.pe/ol-ti-itconsvalicpe/captcha?accion=image&nmagic=0" />
                      <input style="width: 60px;" class="form-control" type="text" v-model="captcha"/>
                    </div>
                    <b-button :disabled="loading" variant="warning" size="sm"
                      @click="consultaElectronico(data.item)">Consulta SUNAT</b-button>
                  </div>
                  
                </template>

              </b-table>
          </b-col>
          <!--<b-row>
              <b-col md="6" class="my-1">
                  <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
              </b-col>
          </b-row>-->
        </b-row>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="12">
              <!--<b-button v-if="estadoVenta == 1 && tipoDoc.CodSunat == '03'" :disabled="this.loading" variant="warning" size="lg" @click="emitirElectronicoResumen">Emitir Como Resumen Diario</b-button>-->
              <b-button v-if="estadoVenta == 1" :disabled="this.loading" variant="success" size="lg" @click="emitirElectronico">Emitir Documentos Electronicas</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>

      <b-modal id="kardex" size="lg" modal-class="modal-xl" v-model="showVenta" title="Kardex de producto">
        <b-alert show variant="warning">titulo</b-alert>
        <!--<producto-kardex :producto="productoSelect" :kardex="kardexSelect"></producto-kardex>-->
      </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProductoKardex from '@/components/ProductoKardex'

export default {
  mounted() {
    this.axios.get(this.API + 'ventas/count', {
      params: { idAlmacen: this.almacen.IdAlmacen }
    }).then((response) => {
      this.totalRows = response.data.total
    })

    /*this.axios.get(this.API + 'ventas/tiposelectronicos')
      .then((response) => {
        response.data.forEach(element => {
          this.optionsTipoDoc.push({ value: element.IdTipoDoc, text: element.TipoDoc })  
        })
      })*/

    this.getVentasAcumuladas()
    this.loadFirstAlmacen()
  },
  data() {
    return {
      API: config.API,
      imgCaptcha: true,
      captcha: '',
      loading: false,
      tipoDoc: {
        IdTipoDoc: 1,
        CodSunat: '01'
      },
      estadoVenta: 1,
      optionsEstado: [
        { value: '1', text: 'PENDIENTE'},
        { value: '2', text: 'EMITIDOS' },
        { value: '0', text: 'ANULADOS' }
      ],
      // optionsTipoDoc: [],
      vendedor: '',
      // fechaInicio: this.$moment().format("YYYY") + '-01-01',
      fechaInicio: this.$moment().format("YYYY-MM-DD"),
      fechaFin: this.$moment().format("YYYY-MM-DD"),
      fechaReferencia: this.$moment().format("YYYY-MM-DD"),
      filters: { },
      filter: '',
      sortBy: 'FechaDoc',
      sortDesc: false,
      ventasAcumuladas: 0,
      almacen: {
        IdAlmacen: 1
      },
      pageOptions: [ 5, 10, 15, 50, 100 ],
      ventaSelect: {},
      ventaDetalleSelect: [],
      items: [],
      itemsDetalle: [],
      showVenta: false,
      isBusy: false,
      fields: [
            { key: 'detalles', label: '' },
            { key: 'idDocVenta', label: 'ID', sortable: true },
            { key: 'FechaDoc', label: 'Fecha', sortable: true },
            { key: 'TipoDoc', label: 'Documento', sortable: true},
            { key: 'Serie', label: 'Serie', sortable: true },
            { key: 'Numero', sortable: true },
            { key: 'Cliente', sortable: true },
            { key: 'Anulado', label: 'Anulado', sortable: true },
            { key: 'UsuarioReg', label: 'Vendedor', sortable: true },
            { key: 'Total', sortable: false, variant: 'success', class:'text-center'},
            { key: 'estado', sortable: false, class: 'text-center' },
            { key: 'consulta', class: 'text-center'}
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      itemsStatus: {},
      isBoleta: false,
      itemsMotivoNota: [
        { value: null, text: '-- SELECCIONE EL MOTIVO --' },
        { value: '01', text: 'ANULACION DE LA OPERACION' },
        { value: '02', text: 'ANULACION POR ERROR EN EL RUC' },
        /*{ value: '03', text: 'CORRECION POR ERROR EN LA DESCRIPCION' },
        { value: '04', text: 'DESCUENTO GLOBAL' },
        { value: '05', text: 'DESCUENTO POR ITEM' },
        { value: '06', text: 'DEVOLUCION TOTAL' },
        { value: '07', text: 'DEVOLUCION POR ITEM' },
        { value: '08', text: 'BONIFICACION' },
        { value: '09', text: 'DISMINUCION EN EL VALOR' },*/
      ],
      notaIdMotivo: null,
      notaCredito: {}
    }
  },
  watch: {
    estadoVenta (id)  {
     // this.$refs.imgSuccess
     // this.$refs.tablaVentas.refresh()
    },
    items (items) {
      if (items.length){
        this.axios.get(this.API + 'ventas/detalle', {
           params: {
             idAlmacen: this.almacen.IdAlmacen,
             idDocVentas: items.map((item) => item.idDocVenta),
           }
         }).then((response) => {
              this.itemsDetalle = response.data
              this.items.map((item) => {
                item.productos = this.getDetalles(item.idDocVenta)
                return item
              })
         })
         .catch((error) => {
           console.log(error)
         })
      }
    }
  },
  computed: {
  },
  methods: {
    loadFirstAlmacen (cb) {
      this.axios.get(this.API + 'almacenes/primero')
        .then(res => {
          this.$refs.inputAlmacen.setValue(res.data.Almacen)
          this.setAlmacen(res.data)
        })
    },
    regenerarPDF (doc) {
      this.$refs.tablaVentas.refresh()
      this.loading = true
      this.axios.post(this.API + 'generarpdfelectronico', { 
        docVenta: doc 
      }).then(res => {
        console.log(res)
        this.$toasted.success('El PDF fue generado nuevamente', { duration: 7000 })
        this.loading = false
      })
    },
    regenerarXML (doc) {
      this.$refs.tablaVentas.refresh()
      this.loading = true

      this.emitirElectronico(doc, true);

    },
    setFechaReferencia (value) {
      //this.fechaReferencia = ''
      this.fechaInicio = value
      this.fechaFin = value
    },
    setTipoDoc (obj) {
      console.log(obj)
      this.tipoDoc = obj
      if (this.tipoDoc.CodSunat == 3) {
        this.isBoleta = true
        this.fechaInicio = this.fechaReferencia
        this.fechaFin = this.fechaReferencia
      } else {
        this.isBoleta = false
      }

      Vue.nextTick(() => {
        this.$refs.tablaVentas.refresh()
      })
    },
    getStatus (idDocVenta) {
      let obj = this.itemsStatus[idDocVenta] || {}

      if (obj.Estado) {
        obj.image = obj.Estado == 2 ? 'success.png' : 'error.png'
      }
      
      return obj
    },
    /*emitirNotaCredito (item) {
      let doc = Object.assign({}, item);
      this.loading = true
      doc.codSunatModifica = doc.CodSunat // almacenamos el codsunat original
      doc.nroComprobanteModifica = doc.Serie + '-' + doc.Numero
      doc.notaIdMotivo = this.notaIdMotivo
      doc.notaDescMotivo = this.itemsMotivoNota.filter(item => item.value == this.notaIdMotivo)[0].text

      doc.CodSunat = '07' // codsunat 07 es nota de credito  / 08 nota de debito
      doc.Serie = this.notaCredito.serie
      doc.Numero = this.notaCredito.numero
      //this.axios.post
      console.log(JSON.stringify(doc))
      
      this.axios.post(this.API + 'emitirelectronico', { 
        docVenta: doc 
      }).then(res => {
        console.log(res)
        this.$toasted.success(res.data.msj_sunat, { duration: 7000 })
        this.notaIdMotivo = null
        this.loading = false
      })


    },*/
    async emitirElectronico (doc, generarXml) {
      if(!this.items.length) {
        this.$toasted.show('No hay documentos para emitir');
        return
      }

      this.loading = true
      let arr = []

      if (this.tipoDoc.CodSunat == '01' || this.tipoDoc.CodSunat == '03' || this.tipoDoc.CodSunat == '07') { // Factura / boleta
        if (doc.idDocVenta && generarXml) {
          arr.push(await this.axios.post(this.API + 'emitirelectronico', {
            generarXml: true,
            docVenta: doc
          }).then((response) => {
            console.log(response)
            if(response.data.Estado == 1) {
              this.$toasted.error('')                  
            } else {
              this.$toasted.success('XML Generado')
              // ocultar el documento emitido
              // Vue.set(this, 'items', this.items.filter(el => el.idDocVenta !== item.idDocVenta) )
              // let serie = response.data.data.txtNRO_COMPROBANTE.split('-')[0]
              // let numero = response.data.data.txtNRO_COMPROBANTE.split('-')[1]
            }
            // Vue.set(this.itemsStatus, item.idDocVenta, response.data)

          }))
        } else {
        for (let item of this.items) {
          if (item.Estado == 1) { // estado 1 es pendiente / 0 anulado / 2 emitido
          console.log(JSON.stringify(item))
              arr.push(await this.axios.post(this.API + 'emitirelectronico', {
                docVenta: item
              }).then((response) => {
                console.log(response)
                if(response.data.Estado == 1) {
                  this.$toasted.error(response.data.msj_sunat)                  
                } else {
                  this.$toasted.success(response.data.msj_sunat)
                  // ocultar el documento emitido
                  Vue.set(this, 'items', this.items.filter(el => el.idDocVenta !== item.idDocVenta) )
                  let serie = response.data.data.txtNRO_COMPROBANTE.split('-')[0]
                  let numero = response.data.data.txtNRO_COMPROBANTE.split('-')[1]
                }
                Vue.set(this.itemsStatus, item.idDocVenta, response.data)

              }))
          }
        }
        }
        Promise.all(arr).then(values => {

          this.loading = false
        })
      }

      if (this.tipoDoc.CodSunat == '07') { // Nota de credito
        
      }

      /*if (this.tipoDoc.CodSunat == '030') { // Boleta emitir resumen diario
        this.axios.post(this.API + 'emitirelectronicoboleta', {
          boletas: this.items,
          fechaReferencia: this.fechaReferencia,
          codSunat: this.tipoDoc.CodSunat
        }).then( response => {
          console.log(response.data)
          this.items.forEach(item => {
            Vue.set(this.itemsStatus, item.idDocVenta, response.data)
          })
          this.loading = false
        })
      }*/
    },
    /*async emitirElectronicoResumen (doc) {
      if(!this.items.length) {
        this.$toasted.show('No hay documentos para emitir');
        return
      }

      this.loading = true

      if (this.tipoDoc.CodSunat == '03') { // Boleta emitir resumen diario
        this.axios.post(this.API + 'emitirelectronicoboleta', {
          boletas: this.items,
          fechaReferencia: this.fechaReferencia,
          codSunat: this.tipoDoc.CodSunat
        }).then( response => {
          console.log(response.data)
          this.items.forEach(item => {
            Vue.set(this.itemsStatus, item.idDocVenta, response.data)
          })
          this.loading = false
        })
      }
    },*/
    eliminarElectronico (venta) {
      let r = confirm('¿Desea Anular la venta?')
      if (r == true) {
        this.loading = true
        
        if (this.tipoDoc.CodSunat == '01') { // Factura
          let desc = prompt('Escriba el motivo por el cual desea dar de baja esta factura')
          if (desc) {  
            this.axios.post(this.API + 'bajaelectronico', {
              facturas: [venta],
              descripcion: desc,
              codSunat: this.tipoDoc.CodSunat
              // fechaReferencia: this.fechaReferencia
            }).then(res => {
              console.log(res)
              this.$toasted.show('Factura Anulada')
              this.$refs.tablaVentas.refresh()
              this.loading = false
              //mensaje success
            })
          }
        }

        if (this.tipoDoc.CodSunat == '03') { // Boleta
          this.axios.post(this.API + 'emitirelectronicoboleta', {
            boletas: [venta],
            statu: 3, // Statu 3 = dar de baja / 1 = emitir
            codSunat: this.tipoDoc.CodSunat,
            fechaReferencia: this.fechaReferencia
          }).then(res => {
            console.log(res)
            this.$toasted.show('Boleta Anulada')
            this.$refs.tablaVentas.refresh()
            this.loading = false
          })
        }
      }      

    },
    getDetalles (idDocVenta) {
      let detalles = this.itemsDetalle.filter(item => item.IdDocVenta == idDocVenta)
      //let index = this.items.findIndex((item) => item.idDocVenta == idDocVenta)
      //this.items[index].productos = detalles
      return detalles
    },
    getVentasAcumuladas() {
      if(this.filters.vendedor) {
        this.axios.get(this.API + 'ventas/usuario', {
         params: {
           usuario: this.vendedor.Usuario,
           fechaInicio: this.fechaInicio,
           fechaFin: this.fechaFin
         }
       }).then((response) => {
         this.ventasAcumuladas = response.data.total
       })
      } else {
        this.axios.get(this.API + 'ventas/total', {
          params: {
            fechaInicio: this.fechaInicio,
            fechaFin: this.fechaFin
          }
        }).then((response) => {
          this.ventasAcumuladas = response.data.total
        })
      }
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    setAlmacen (obj) {
      this.almacen = obj
      this.limpiarFiltros()
      Vue.nextTick(() => this.$refs.tablaVentas.refresh())
    },
    actualizarTabla() {
      this.$refs.tablaVentas.refresh()
    }, 
    myProvider (ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      
      let url = this.API + 'ventas?estado=' + this.estadoVenta + '&idTipoDoc[]=' + this.tipoDoc.IdTipoDoc + '&idAlmacen=' + this.almacen.IdAlmacen + '&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
      url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
console.log(url)
      let promise = this.axios.get( url )

      return promise.then((result) => {
        
        //this.items = result.data
        this.items = result.data.map(el => {
          if (el.Anulado === 1) {
            el._rowVariant = 'warning'
          }

          return el
        })

        //this.totalRows = this.items.length
        // Here we could override the busy state, setting isBusy to false
        // this.isBusy = false
        return(this.items)
      }).catch(error => {
        // Here we could override the busy state, setting isBusy to false
        // this.isBusy = false
        // Returning an empty array, allows table to correctly handle busy state in case of error
        return []
      })
    },
    limpiarFiltros () {
      this.filters = {}
      this.filter = ''
      this.fechaInicio = this.$moment().format("YYYY-MM-DD")
      this.fechaFin = this.$moment().format("YYYY-MM-DD")
      //this.$refs.inputVendedor.setValue('')
    },
    generateFilter (key, value) {
      this.filters[key] = value

      let filter = ''
      for (var key in this.filters) {
        filter += '&filter[' + key + ']=' + this.filters[key]
      }
      this.filter = filter
      this.getVentasAcumuladas()
    },
    exportarProductos () {

    },
    moverAEmitidos (doc) {
      this.loading = true
      this.axios.post(this.API + 'moveraemitidos', {
        idDocVenta: doc.idDocVenta
      }).then( response => {
        this.$refs.tablaVentas.refresh()
        this.loading = false
      })
    },
    cambiarAAnonimo (doc) {
      this.loading = true
      this.axios.post(this.API + 'cambiaraanonimo', {
        idDocVenta: doc.idDocVenta
      }).then( response => {
        this.$refs.tablaVentas.refresh()
        this.loading = false
      })
    },
    consultaElectronico (doc) {
      console.log(doc)
      let tipocomprobante = ''
      let cantidad=''
      let fecha = this.$moment(doc.FechaDoc, 'YYYY-MM-DD hh:mm:ss').format('DD/MM/YYYY')
      console.log(fecha)

      if (doc.CodSunat == '03') {
        tipocomprobante = '06'
      }
      if (doc.CodSunat == '01') {
        cantidad = doc.Total
        tipocomprobante = '03'
      }

      this.imgCaptcha = false
      window.open('http://www.sunat.gob.pe/ol-ti-itconsvalicpe/ConsValiCpe.htm?accion=CapturaCriterioValidez&num_ruc=20605678131&tipocomprobante=' + tipocomprobante + '&num_serie=' + doc.Serie + '&num_comprob=' + doc.Numero + '&fec_emision=' + encodeURI(fecha) + '&codigo=' + this.captcha + (cantidad ? '&cantidad=' + cantidad : ''))
      
      /*this.$nextTick(() => {
        this.imgCaptcha = true
        this.captcha = ''
      })*/
      setTimeout(() => {
        this.imgCaptcha = true
        this.captcha = ''
      }, 1000)
    }
  },
  components: {
    ProductoKardex
  }
}
</script>

<style scope>
.total-ventas {
  font-weight: bold;
  text-align: center;
  color: #ffffff;
  background-color: #ff7675;
  padding: 10px;
  font-size: 40px;
}
#tabla-productos tr {
  cursor: pointer;
}
.table-detalle {
  height: 80px;
}
.table-detalle th {
  color: #fff;
  background-color: #6c757d; 
}
.table-detalle tbody td {
  font-size: 18px;
  font-weight: bold;
  color: #2980b9;
}
.table-detalle thead th:first-child {
  max-width: 300px;
}
@media (min-width: 992px) {
    .modal-xl .modal-dialog {
        max-width: 1200px;
    }
}
.prod-detalle {
  margin: 0 30px;
}
.prod-detalle th, .prod-detalle td{
  background-color: #ffffff;
}
</style>