<template>
  <div>
      <b-container fluid class="ancho bv-example-row" style="width: calc(100% - 200px)">
        <b-row>
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
            <b-col md="8">

              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
            <b-col md="4">
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Artículos</p>
            </b-col>
            </b-row>
          </b-col>

          <b-table small
            ref="tablaProductos"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            @row-clicked="rowClick"
            id="my-table" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage">
              <template slot="PrecioCosto" slot-scope="data" >
                {{ data.item.PrecioCosto | redondear(2) }}
              </template>
              <template slot="PrecioContado" slot-scope="data" >
                {{ data.item.PrecioContado | redondear(2) }}
              </template>
              <template slot="Colores" slot-scope="data" >
                <div v-if="data.item.EsVehiculo==1">
                  <b-button variant="primary" @click.stop="showColores(data.item)"><i class="fas fa-palette"></i> Colores</b-button>
                </div>
              </template>
            </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <producto-form ref="productoForm" :producto-update="productoSelect" :cbGuardar="actualizarTabla"></producto-form>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" @click="nuevoProducto">Nuevo</b-button>
            </b-col>
        </b-row>
        <br /><br />
        <b-modal id="modalVehiculoColor" size="lg" ref="modalVehiculoColor" hide-header>
          <div class="d-block text-center">
            <h3 style="text-decoration: underline;">{{ productoSelect.Producto }} - {{ productoSelect.ProductoModelo }}</h3>
            <h4>COLORES</h4>
          </div>
          <hr>
          <b-row>
            <b-col md="3">
              <b-form-select id="selectColor" :disabled="!nuevoColor" v-model="idColor" :options="colores" v-on:change="setColor($event)" class="mb-3" size="md" required/>
            </b-col>
            <b-col md="6">
              <input class="mt-1" type="file" id="file" ref="file" :disabled="!nuevoColor" @change="handleFileUpload()"/>
              <b-form-text id="input-formatter-help">
                <b>*La imagen debe ser en formato PNG y 500 x 500 pixeles</b>
              </b-form-text>
            </b-col>
            <b-col md="3" class="text-center">
              <b-button @click="accionColor()" :variant="nuevoColor?'success':'primary'">{{ nuevoColor?'Guardar':'Nuevo' }}</b-button>
              <b-button v-if="nuevoColor" @click="limpiarImagenProducto()" variant="danger">Cancelar</b-button>
            </b-col>
          </b-row>
          <b-row>
            <b-col md="12">
              <table class="table b-table table-sm" id="tablaImagenColores">
                <thead>
                  <tr>
                    <th class="text-center" width="5%"></th>
                    <th class="text-center">Imagen</th>
                    <th class="text-center">Color</th>
                    <th class="text-center">Fecha Registro</th>
                    <th class="text-center">Acción</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(prod, index) in coloresProducto" :key="index">
                    <td><b-button @click="cambiarZoom(prod)" :variant="prod.Mostrar==0?'primary':'danger'" size="sm"><i :class="prod.Mostrar==0?'fas fa-search-plus':'fas fa-search-minus'"></i></b-button></td>
                    <td class="text-center"><b-img :src="'/resources/images/colores/' + prod.Imagen" fluid alt="" width="80px" :class="{'imagenColorMax': prod.Mostrar == 1}"></b-img></td>
                    <td class="text-center"><b>{{ prod.ProductoColor }}</b></td>
                    <td class="text-center"><b>{{ prod.FechaReg }}</b></td>
                    <td class="text-center"><b-button :disabled="nuevoColor" variant="danger" size="sm" @click="eliminarImagenColor(prod)"><i class="fas fa-trash"></i></b-button></td>
                  </tr>
                </tbody>
              </table>
            </b-col>
          </b-row>
          <template #modal-footer>
            <div class="w-100">
              <p class="float-left"></p>
              <b-button
                variant="secondary"
                size="md"
                class="float-right"
                @click="$refs['modalVehiculoColor'].hide()"
              >
                Cerrar
              </b-button>
            </div>
          </template>
        </b-modal>
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProductoForm from '@/components/ProductoForm'

export default {
  mounted() {
    this.axios.get(this.API + 'productos/count').then((response) => {
      this.totalRows = response.data.total
    })
    this.cargarColores()
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'IdProducto',
      sortDesc: false,
      productoSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'CodigoBarra', label: 'Codigo', sortable: true },
            { key: 'Producto', sortable: true },
            { key: 'ProductoCategoria', label: 'Categoría', sortable: true},
            { key: 'ProductoMarca', label: 'Marca', sortable: true },
            { key: 'ProductoMedicion', label: 'Medición', sortable: true },
            { key: 'PorcentajeUtilidad', label: 'Porcentaje Utilidad', sortable: true },
            { key: 'PrecioCosto', label: 'Precio Compra', sortable: true },
            { key: 'PrecioContado', label: 'Precio Venta', sortable: true },
            { key: 'Colores', label: 'Extra', sortable: false },
            //{ key: 'ProductoPresentacion', label: 'Anaquel', sortable: true },
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      coloresProducto: [],
      colores: [],
      idColor: null,
      nuevoColor: false,
      file: null,
      processImg: false,
    }
  },
  methods: {
      limpiarImagenProducto(){
        this.nuevoColor = false
        this.idColor = this.colores.length>0?1:null
        this.$refs.file.value = null
      },
      async accionColor(){
        if(this.nuevoColor){
          this.processImg = true
          if(this.idColor==null || this.idColor==1){
            this.$toasted.error('Debe seleccionar un color')
            this.processImg = false
            return
          }

          if(this.file == null){
            this.$toasted.error('Debe seleccionar un archivo de imagen 500 x 500 en formato PNG')
            this.processImg = false
            return
          }

          try {
            let formData = new FormData();
            formData.append('imagen', this.file);
            const response = await this.axios({
              method:'post',
              url:`${this.API}vehiculo/colores/nuevo/${this.productoSelect.IdProducto}/${this.idColor}`,
              data: formData,
              headers   : { 'Content-Type': 'multipart/form-data' }
              }
            );
            if(response.data.success){
              this.nuevoColor = false
              this.idColor = this.colores.length>0?1:null
              this.$refs.file.value = null
              this.coloresProducto = response.data.data
              this.$toasted.success(response.data.msg)
            }else{
              this.$toasted.show(response.data.msg)
            }
            this.processImg = false
          } catch (error) {
            this.$toasted.error('Ha ocurrido un error')
          }

        }else {
          this.nuevoColor = true
        }
      },
      handleFileUpload() {
        const file = this.$refs.file.files[0]
        if (!file) {
          this.$refs.file.value=null
          this.$toasted.show('Sin Imagen')
          return;
        }
        
        if (file.type != 'image/png') {
          this.$toasted.show('La imagen debe ser en formato PNG de 500 x 500 pixeles.')
          this.$refs.file.value = null
          return;
        }

        try {
          this.getBase64(file).then((base64) => {
            const img = new Image();
            img.src = base64.toString();
            img.onload = () => {
              if(img.width == 500 && img.height == 500){
                this.file = file
              }else{
                this.$refs.file.value = null
                this.$toasted.show("La imagen debe ser en formato PNG de 500 x 500 pixeles.")
              }
            
            };     
          });    
        } catch (error) {
          this.$refs.file.value = null
          this.$toasted.show("Ha ocurrido un error")
        }

      },
      getBase64(file) {
        return new Promise((resolve, reject) => {
          const reader = new FileReader();
          reader.readAsDataURL(file);
          reader.onload = () => resolve(reader.result);
          reader.onerror = error => reject(error);
        });
      },
      setColor(e){
        this.idColor = e
      },
      cargarColores(){
        this.axios.get(this.API + 'colores')
        .then((response) => {
          if(response.data.length>0){
            this.colores = []
            this.idColor = 1
            response.data.map((dat)=>{
              this.colores.push({value: dat.IdProductoColor, text: dat.ProductoColor, color: dat.HexColor})
            })
          }
        })
      },
      rowClick (item, index) {
        this.productoSelect = item
        this.$refs.productoForm.show = true;
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      async eliminarImagenColor(item){
        let r = confirm('¿Está seguro de eliminar el color '+item.ProductoColor+' de '+this.productoSelect.Producto+'?')
        if (r) {
          try {
            const response = await this.axios.post(`${this.API}vehiculo/colores/eliminar`, 
              {
                IdEnlaceColor: item.IdEnlaceColor,
                IdProducto: item.IdProducto
              }
            );

            if(response.data.success){
              this.$toasted.info('Imagen eliminada')
              this.coloresProducto = response.data.data
            }
          } catch (error) {
            console.log(error);
            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
          }
        }
      },
      cambiarZoom(prod){
        prod.Mostrar = prod.Mostrar==1?0:1
      },
      async showColores(item){
        this.coloresProducto = []
        this.nuevoColor = false
        this.idColor = this.colores.length>0?1:null
        this.$refs.file.value=null
        try {
          const response = await this.axios.get(`${this.API}vehiculo/colores`, { params:
            {
              IdProducto: item.IdProducto
            }
          });
          this.productoSelect = item
          this.coloresProducto = response.data
          this.$refs['modalVehiculoColor'].show()
        } catch (error) {
          console.log(error);
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        }
      },
      actualizarTabla() {
        this.$refs.tablaProductos.refresh()
      },
      nuevoProducto () {
          this.productoSelect = {}
          this.$refs.productoForm.show = true

      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'productos?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
        url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
        let promise = this.axios.get( url )

        return promise.then((result) => {
          this.items = result.data
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
      }
  },
  components: {
      ProductoForm
  }
}
</script>

<style>
  @media only screen and (min-width: 777px) and (max-width: 1800px) {
  /* For desktop: */
    .ancho{
        width: calc(100% - 300px)
    }
  }
  #tablaImagenColores tbody td{
    vertical-align: middle !important;
  }
  .imagenColorMax{
    width: 250px !important;
    height: 250px !important;
  }
  #modalVehiculoColor .modal-dialog{
    max-width: 65% !important;
  }
</style>
