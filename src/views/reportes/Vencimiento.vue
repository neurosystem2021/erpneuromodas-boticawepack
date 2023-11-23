<template>
    <div>
      <h2 align-h="center" class="text-center">Reporte de vencimiento</h2>
        <b-container fluid style="width: calc(90%);" class="bv-example-row">
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
                <table class="table table-hover b-table table-sm" id="tablaVencidos">
                    <thead>
                        <tr>
                            <th>Hash</th>
                            <th>ID</th>
                            <th>Producto</th>
                            <th>Laboratorio</th>
                            <th class="text-center">Lote</th>
                            <th class="table-info text-center">Fecha Vencimiento</th>
                            <th>Registro</th>
                            <th class="text-center">Serie</th>
                            <th class="text-center">Numero</th>
                            <th>Stock</th>
                            <th class="text-center">Accion</th>
                        </tr>
                    </thead>
                    <tbody v-for="(item, index) in productos" :key="index">
                        <tr>
                            <td class="text-center">{{ item.hashMovimiento }}</td>
                            <td>{{ item.IdProducto }}</td>
                            <td>{{ item.Producto }}</td>
                            <td>{{ item.ProductoMarca }}</td>
                            <td class="text-center">{{ item.Lote }}</td>
                            <td class="table-info text-center">{{ item.FechaVen }}</td>
                            <td>{{ item.Registro }}</td>
                            <td class="text-center">{{ item.Serie }}</td>
                            <td class="text-center">{{ item.Numero }}</td>
                            <td>{{ item.Stock | redondear(2) }}</td>
                            <td class="text-center"><span v-if="item.Vencer==0"><b-button @click="cambiarVencer(item.IdProducto, index)" variant="info">CAMBIAR A VENCIDO</b-button></span><span v-else><b-badge style="font-size: 1.2rem;" variant="danger">VENCIDO</b-badge></span></td>
                        </tr>
                    </tbody>
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
        fields: [
              { key: 'DniRuc', label: 'DNI/RUC', sortable: true },
              { key: 'Cliente', label: 'Cliente', sortable: true },
              { key: 'Correlativo', label: 'Serie', sortable: true },
              { key: 'Total', label: 'Total', sortable: true},
              { key: 'Aplicado', label: 'Aplicado', sortable: true},
              { key: 'NotaCreApli', label: 'Apli. Nota Cred.', sortable: true},
              { key: 'Saldo', label: 'Saldo', sortable: true },
              { key: 'FechaDoc', label: 'Emision', sortable: true },
              { key: 'FechaCredito', label: 'Vencimiento', sortable: true },
        ],
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