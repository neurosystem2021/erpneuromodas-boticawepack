<template>
  <div>
      <b-container class="container-huespedes">
        <b-row class="no-print">
          <b-col md="3" class="my-1">
            <b-form-group>
                  <label>Fecha Registro Inicio:</label>
							
              <b-input-group>
                <b-form-input type="date" v-model="fechaRegistroInicio" />
              </b-input-group>
            </b-form-group>
            
          </b-col>
          <b-col md="3" >
            <b-form-group>
                  <label>Fecha Registro Fin:</label>
							
              <b-input-group>
                <b-form-input type="date" v-model="fechaRegistroFin" />
              </b-input-group>
            </b-form-group>
          </b-col>

          <b-col md="3" >
            <b-form-group>
              <label>Buscar:</label>
              <b-input-group>
                <b-btn variant="success" @click="getRegistroHuespedes">Buscar</b-btn>
              </b-input-group>
            </b-form-group>
          </b-col>

        </b-row>
        <div class="registro">
          <h1 class="text-center" >Registro de Huéspedes</h1>
        <b-row>
          <table class="table b-table table-striped table-hover">
            <thead>
              <tr>
                <th>Habitación</th>
                <th>Apellidos y Nombres <b>(Titular)</b></th>
                <th>Sexo</th>
                <!--<th>Ocupacion</th>-->
                <th>F. Nacimiento</th>
                <th>Nacionalidad</th>
                <th>Documento de Identidad</th>
                <th>Direccion</th>
                <th>Fecha de Ingreso</th>
                <th>Lugar de Procedencia</th>
                <th>Medio de Transporte</th>
                <th>Próximo Destino</th>
                <th>Fecha de salida</th>
                <th>Tipo</th>
                <th>Tarifa</th>
                <th>Huespedes</th>

              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in huespedes" :key="index">
                <td>{{ item.Producto }}</td>
                <td>{{ item.Cliente }}</td>
                <td>{{ item.Sexo }}</td>
                <!--<td>{{ item.Ocupacion }}</td>-->
                <td>{{ item.FechaNacimiento }}</td>
                <td>{{ item.Nacionalidad }}</td>
                <td>{{ item.DniRuc }}</td>
                <td>{{ item.Direccion }}</td>
                <td>{{ item.FechaAlquilerInicio }}</td>
                <td>{{ item.LugarProcedencia }}</td>
                <td>{{ item.MedioTransporte }}</td>
                <td>{{ item.ProximoDestino }}</td>
                <td>{{ item.FechaAlquilerFin }}</td>
                <td>{{ item.ProductoMarca }}</td>
                <td>{{ item.Tarifa }}</td>
                <td><b-button @click="abrirHuespedes(item)" size="sm" variant="info"><i class="fas fa-eye"></i></b-button></td>
              </tr>
            </tbody>
          </table>
        </b-row>
        </div>
        <br /><br />
      <b-modal size="md" ref="modalHuespedes" hide-footer :title="'HUESPEDES DE: '+itemSelect.Producto" no-close-on-esc no-close-on-backdrop>
        <div class="d-block ">
          <b-row>
            <b-col md="12">
              <b>TITULAR: </b>{{ itemSelect.Cliente }}
            </b-col>
            <b-col md="12">
              <b>HUESPED 1: </b>{{ itemSelect.IdClienteH1==null?'----':itemSelect.Huesped1 }}
            </b-col>
            <b-col md="12">
              <b>HUESPED 2: </b>{{ itemSelect.IdClienteH2==null?'----':itemSelect.Huesped2 }}
            </b-col>
          </b-row>
        </div>
        <b-btn class="mt-3" variant="outline-danger" block @click="()=>{$refs.modalHuespedes.hide()}">Cerrar</b-btn>
      </b-modal>
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'

export default {
  mounted() {
    this.getRegistroHuespedes()
  },
  data() {
    return {
      API: config.API,
      huespedes: [],
      fechaRegistroInicio: this.$moment().format("YYYY-MM-DD"),
      fechaRegistroFin: this.$moment().format("YYYY-MM-DD"),
			almacen: {
				IdAlmacen: 1
			},
      itemSelect:{}
    }
  },
  methods: {
		focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
		},
		setAlmacen (obj) {
			this.almacen = obj
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    getRegistroHuespedes () {
      this.axios.get(this.API + 'reporte/habitaciones?idAlmacen='+this.almacen.IdAlmacen+'&fechaInicio='+this.fechaRegistroInicio+'&fechaFin='+this.fechaRegistroFin).then((response) => {
        this.huespedes = response.data
      })
    },
    abrirHuespedes(item){
      this.itemSelect = item
      this.$refs.modalHuespedes.show()
    }
  },
  components: {
  }
}
</script>

<style scoped>
  .container-huespedes {
    max-width: 100%;
    padding:  0 2rem;
  }
  .registro {
    font-size: .8rem;
  }
  @media print
  {    
    .no-print, .no-print *
    {
      display: none !important;
    }
  }
</style>