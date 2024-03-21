<template>
    <div>
      <h2 align-h="center" class="text-center">Reporte de vencimiento</h2>
        <b-container fluid style="width: calc(97%);" class="bv-example-row">
          <b-row>
            <b-col md="4" class="my-1" v-show="false">
              <b-form-group>
                    <label>Buscar:</label>
                
                <b-input-group>
                  <b-form-input v-model="filter" autocomplete="off" placeholder="Buscar" @input="currentPage = 1" />
                  <b-input-group-append>
                    <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
              <br />
            </b-col>
            <b-col md="8" class="my-1">
              <b-row>
                <b-col md="4">
                  <b-form-group>
                    <label>Fecha Inicio:</label>
                    <b-form-input
                      v-model="vencimiento.fechaInicio"
                      type="date"
                      step="1"
                      placeholder="Fecha Inicio">
                    </b-form-input>
                  </b-form-group>
                </b-col>
                <b-col md="4">
                  <b-form-group>
                    <label>Fecha Fin:</label>
                    <b-form-input
                      v-model="vencimiento.fechaFin"
                      type="date"
                      step="1"
                      placeholder="Fecha Inicio">
                    </b-form-input>
                  </b-form-group>
                </b-col>
                <b-col md="4">
                  <br>
                  <b-button :disabled="isProcess" class="mt-2" @click="generarFechas()" variant="primary" size="md">Generar</b-button>  
                </b-col>
              </b-row>
            </b-col>
          </b-row>
          <b-row>
            <b-col md="12">
                <table class="table table-hover table-bordered b-table table-sm" id="tablaVencidos">
                    <thead>
                        <tr>
                            <th>Hash</th>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Laboratorio</th>
                            <th>Lote</th>
                            <th class="table-info text-center">Fecha Vencimiento</th>
                            <th>Registro</th>
                            <th>Serie</th>
                            <th>Numero</th>
                            <th class="table-success text-center">Stock</th>
                            <th>RUC</th>
                            <th>Proveedor</th>
                            <th class="table-primary text-center">P.Costo</th>
                            <th class="table-primary text-center">P.Venta</th>
                            <th class="table-success text-center">Total</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody v-for="(item, index) in productos" :key="index">
                        <tr>
                            <td class="text-center">{{ item.hashMovimiento }}</td>
                            <td>{{ item.IdProducto }}</td>
                            <td>{{ item.Producto }}</td>
                            <td>{{ item.ProductoMarca }}</td>
                            <td>{{ item.Lote }}</td>
                            <td class="table-info text-center">{{ item.FechaVen }}</td>
                            <td>{{ item.Registro }}</td>
                            <td>{{ item.Serie }}</td>
                            <td>{{ item.Numero }}</td>
                            <td class="table-success text-center">{{ item.Stock | redondear(2) }}</td>
                            <td>{{ item.Ruc }}</td>
                            <td>{{ item.Proveedor }}</td>
                            <td class="table-primary text-center">{{ item.PrecioCosto.toFixed(2) }}</td>
                            <td class="table-primary text-center">{{ item.PrecioContado.toFixed(2) }}</td>
                            <td class="table-success text-center">{{ ((item.Stock * item.PrecioContado) - (item.Stock * item.PrecioCosto)).toFixed(2) }}</td>
                            <td class="text-center"><span v-if="item.Vencer==0"><b-button @click="cambiarVencer(item.IdProducto, index)" variant="info">CAMBIAR A VENCIDO</b-button></span><span v-else><b-badge style="font-size: 1.2rem;" variant="danger">VENCIDO</b-badge></span></td>
                          </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="11"></td> <!-- Espacio vacío para las primeras columnas -->
                            <td class="table-success text-center "><strong>Total</strong></td>
                            <td class="table-primary text-center">{{ sumarPreciosCosto(productos).toFixed(2) }}</td> <!-- Suma de PrecioCosto -->
                            <td class="table-primary text-center">{{ sumarPreciosContado(productos).toFixed(2) }}</td> <!-- Suma de PrecioContado -->
                            <td></td> <!-- Espacio vacío para la última columna -->
                        </tr>
                    </tfoot>
                </table>
            </b-col>
          </b-row>
          <!--<b-row align-h="center" class="text-center">
              <b-col cols="4">
                <b-button variant="success" size="lg" :href="'/api/index.php/reporte/deudaclientes2?idalmacen=' + almacen.IdAlmacen" target="_blank">Exportar</b-button>
              </b-col>
          </b-row>-->
          <br /><br />
        </b-container>
    </div>
  </template>
  
  <script>
  import config from '@/store/config.js'
  // import ProductoForm from '@/components/ProductoForm'
  export default {
    mounted() {
    },
    data() {
      return {
        API: config.API,
        pageOptions: [ 5, 10, 15, 50, 100 ],
        filter: '',
        sortBy: 'IdDocVenta',
        sortDesc: false,
        items: [],
        isBusy: false,
        totalRows: 50,
        perPage: 10,
        currentPage: 1,
        almacen: {
          IdAlmacen: 1
        },
        productos: [],
        vencimiento: {
          fechaInicio: this.$moment().format("YYYY-MM-01"),
          fechaFin: this.$moment().format("YYYY-MM-DD")
        },
        isProcess: false,
      }
    },
    methods: {
      
      sumarPreciosCosto(productos) {
        return productos.reduce((total, item) => total + item.PrecioCosto, 0);
      },
      sumarPreciosContado(productos) {
        return productos.reduce((total, item) => total + item.PrecioContado, 0);
      },
      async generarFechas(){
        if(this.vencimiento.fechaInicio !='' && this.vencimiento.fechaFin != ''){
          this.isProcess = true
          try {
            const response = await this.axios.get(`${this.API}vencimiento`, { params:
              {
                IdAlmacen: 1,
                FechaInicio: this.vencimiento.fechaInicio,
                FechaFin: this.vencimiento.fechaFin,
              }
            });
            this.isProcess = false
            this.productos = response.data
          } catch (error) {
            this.isProcess = false
            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
          }
        }else{
          this.$toasted.show('Debe seleccionar una fecha inicial y final')
        }
      },
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      setAlmacen (obj) {
        this.almacen = obj
        Vue.nextTick(() => this.$refs.tablaProductos.refresh())
      },
      async cambiarVencer(id, index){
        let r = confirm('¿Está seguro de cambiar a vencido?')
        if (r) {
          try {
            const response = await this.axios.post(`${this.API}cambiar/vencido`, {
              IdProducto: id
            });
            if(response.data.success){
              this.productos[index].Vencer = 1
              this.$toasted.success('Cambiado a vencido correctamente')
            }else{
              this.$toasted.show('No cambiado...')
            }
          } catch (error) {
            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
          }
        }
      }
    },
    components: {
        // ProductoForm
    }
  }
  </script>
  
  <style>
  #tablaVencidos td{
    vertical-align: middle;
  }
  </style>