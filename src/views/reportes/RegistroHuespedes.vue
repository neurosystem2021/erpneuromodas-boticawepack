<template>
<div class="nst-screen nst-screen--registrohuespedes">
    <b-container class="container-huespedes">
      <section class="nst-panel nst-panel--filters"><b-row class="no-print">
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

      </b-row></section>
      <div class="registro">
        <h1 class="text-center" >Registro de Proyectos</h1>
      <section class="nst-panel nst-panel--results"><b-row>
        <div class="nst-table-scroll" tabindex="0" role="region" aria-label="Tabla de resultados"><table class="table b-table table-striped table-hover">
          <thead>
            <tr>
              <th>Proyecto</th>
              <th>Apellidos y Nombres</th>
              <th>Sexo</th>
              <th>Ocupacion</th>
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

            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in huespedes" :key="index">
              <td>{{ item.Producto }}</td>
              <td>{{ item.Cliente }}</td>
              <td>{{ item.Sexo }}</td>
              <td>{{ item.Ocupacion }}</td>
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
            </tr>
          </tbody>
        </table></div>
      </b-row></section>
      </div>
      <br /><br />
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
			}
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