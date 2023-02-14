<template>
    <div>
        <b-button @click="show=true" variant="warning">Total Balones</b-button>
        
        <b-form>
        <b-modal id="modalBalones" 
          title="Ver Reporte de Balones"
          size="lg" v-model="show"
          header-bg-variant="dark"
          headerTextVariant="light"
          body-bg-variant="light"
          body-text-variant="dark">           

        <b-row>
          <b-col md="11" class="my-1" style="margin: 0px auto;">
            <b-row>
              <b-col style="text-align: center; padding-top:10px; text-align: center; border-radius: 10px 0px 0px 10px; border: 0.5px black solid; color: black;background: white;">
                <b>Total de Balones<br>
                {{cantidadBalones}}</b>
              </b-col>
              <b-col style="text-align: center; text-align: center; border: 0.5px black solid; color: black;background: white;">
                <b>Total de Envaces Vacios<br>
                {{stock}}</b>
              </b-col>
              <b-col style="text-align: center; text-align: center; border: 0.5px black solid; color: black;background: white;">
                <b>Cantidad de Balones Prestados<br>
                {{cantidadPrestado}}</b>
              </b-col>
              <b-col style="text-align: center; text-align: center; border-radius: 0px 10px 10px 0px; border: 0.5px black solid; color: black;background: white;">
                <b>Cantidad de Gases (contenido)<br>
                {{cantidadGases}}</b>
              </b-col>
            </b-row>
          </b-col>
        </b-row>

        <b-row align-h="center">
          <b-col md="5" class="my-3">
            <b-form-group>
              <autocomplete
                  ref="inputBalonEnvase"
                  :url="API + 'productos/balonesEnvase'"
                  anchor="Producto"
                  label="writer"
                  :on-select="setBalonesEnvase"
                  :onFocus="focusAutocomplete.bind(null, 'inputBalonEnvase')"
                  :classes="{input: 'form-control'}">
              </autocomplete>
            </b-form-group>
            <b-form-group style="text-align: center;">
              <b-btn variant="secondary" @click="getBalonesTotales">
                Sin Filtro
              </b-btn>
            </b-form-group>
          </b-col>
        </b-row>
          <div slot="modal-footer" >
            <b-button variant="success" size="sm" @click="hrefXlsProductoBalones">Exportar Reporte Total</b-button>
            <b-btn size="sm" variant="secondary" @click="show=false">
                Cerrar
            </b-btn>
          </div>
        </b-modal>
      </b-form>
    </div>
</template>
<script>
import config from '@/store/config.js'

    export default {
        mounted () {
    
        },
        props: ['idAlmacen'],
        watch: {
          show (val) {
            this.getBalonesTotales()
          }
        },
        data () {
            return {
                API: config.API,
                show: false,
                balonesTot: 0,
                cantidadBalones: 0,
                cantidadPrestado: 0,
                cantidadGases: 0,
                stock: 0,
                productos: {},
                prodPrestados:{},
                msgFirst: 'TODOS',
                url:''
            }
        },
        methods: {
            getBalonesTotales() {
                this.axios.get(this.API + 'balonesvacios' + '?idAlmacen='+this.idAlmacen)
                .then(res => {
                    this.$refs.inputBalonEnvase.setValue('TODOS')
                    this.cantidadBalones = res.data.cantidadEnvases;
                    this.stock = res.data.envasesVacios;
                    this.cantidadGases = res.data.cantidadGases;
                    this.productos.IdProducto = null;
                    this.getPrestados();
                })
            },
            hrefXlsProductoBalones() {
              this.axios.get(this.API + 'productos/prestados?idAlmacen='+this.idAlmacen+ '&reporte=1').then(res1 => {
                this.prodPrestados=res1.data;
                this.axios.get(this.API + 'reporte/balonesContenido',{
                  params: {
                    idProductos: this.prodPrestados.map(x => x.IdProducto),
                    idAlmacen: this.idAlmacen,
                    idProductoContenido: this.prodPrestados.map(x => x.IdProductoContenido)
                  }
                })
                .then(res => {
                  window.open(res.request.responseURL,'_blank');
                  this.getBalonesTotales();
                })
              })
            },
            getBalonesEnvase() {
                this.axios.get(this.API + 'balonesvacios',{
                  params: {
                    idProducto: this.productos.IdProducto,
                    idAlmacen: this.idAlmacen,
                    idProductoContenido: this.productos.IdProductoContenido
                  }
                })
                .then(res => {
                    this.cantidadBalones = res.data.cantidadEnvases;
                    this.stock = res.data.envasesVacios;
                    this.cantidadGases = res.data.cantidadGases;
                })
              this.getPrestados();
            },
            focusAutocomplete (ref) {
              // this.$refs[ref].type = ""
              this.$refs[ref].handleDoubleClick()
            },
            setBalonesEnvase(obj) {
              this.productos = obj;
              this.getBalonesEnvase();
            },
            getPrestados() {
              if (this.productos.IdProducto) {
                this.url = this.API + 'productos/prestados?idAlmacen='+this.idAlmacen+ '&idProducto='+this.productos.IdProducto + '&reporte=1'
              }
              else {
                this.url = this.API + 'productos/prestados?idAlmacen='+this.idAlmacen + '&reporte=1'
              }
              let promise = this.axios.get( this.url )

              return promise.then((result) => {
                var cont = 0;
                result.data.map(d => {
                  cont += parseInt(d['Prestados']);
                })
                this.cantidadPrestado = cont
              }).catch(error => {
                console.log(error);
              })
            }
        }
    }
</script>
<style>

</style>
