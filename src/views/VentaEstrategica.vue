<template>
    <div>
        <br>
        <b-container class="bv-example-row">
          <b-row>
            <b-col md="2" class="my-1">
              <b-form-group>
                <label>Almacen:</label>
                <autocomplete
                    required="required"
                    ref="inputAlmacen"
                    :url="API + 'almacenes'"
                    anchor="Almacen"
                    label="writer"
                    placeholder=""
                    :on-select="setAlmacen" 
                    :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                    :classes="{input: 'form-control'}">
                </autocomplete>
              </b-form-group>
            </b-col>
            <b-col md="2">
              <b-form-group>
                <label>Vendedor:</label>
                <autocomplete
                    required="required"
                    ref="inputVendedor"
                    :url="API + 'usuarios'"
                    anchor="NombreUsuario"
                    label="writer"
                    :on-select="setVendedor" 
                    :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                    :classes="{input: 'form-control'}">
                </autocomplete>
              </b-form-group>
            </b-col>
            <b-col md="3" class="my-1">
              <b-form-group>
                <label>Fecha Inicio:</label>
                <b-form-input
                  v-model="fechaDesde"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="3" class="my-1">
              <b-form-group>
                <label>Fecha Fin:</label>
                <b-form-input
                  v-model="fechaHasta"
                  type="date"
                  step="1"
                  placeholder="Fecha Fin"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="2" class="my-1">
                <br>
              <b-button variant="success" size="sm" @click="verVentasEstrategicas">Generar</b-button>
            </b-col>
          </b-row>
            <br><br>
          <b-row class="text-center">
            <b-alert show variant="primary">
             <h4> Hola {{ vendedor.NombreUsuario }}.</h4>
              El total por venta estratégica es de: <br><br>
              <h4>S/. {{ total | moneda}}</h4>
            </b-alert>
          </b-row>
        </b-container>
    </div>
</template>
<script>
import config from '@/store/config.js'

export default {
  mounted () {
    this.getUsuario();
  },
  data () {
    return {
      API: config.API,
      almacen: {},
      fechaDesde: this.$moment().format("YYYY-MM-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      vendedor: {},
      total: 0
    }
  },
  methods: {
    loadAlmacen(id) {
      this.axios.get(this.API + 'almacenes/id/' + id)
      .then(res => {
          this.setAlmacen(res.data)
      })
    },
    setAlmacen (obj) {
      this.almacen = obj
      this.$refs.inputAlmacen.setValue(obj.Almacen)
      this.verVentasEstrategicas()
    },
    getUsuario () {
        let usuario = sessionStorage.getItem('User')
        this.axios.get(this.API + 'usuarios/' + usuario)
        .then((response) => {
            if (response.data.IdUsuarioPerfil != 1) {
              this.$refs.inputVendedor.$refs.input.disabled = true
            }
            this.setVendedor(response.data)
            this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
            this.loadAlmacen(response.data.IdAlmacen)
            this.$refs.inputAlmacen.$refs.input.disabled = true
        })
        .catch((error) => {
            console.log(error)
            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    setVendedor (obj) {
        this.vendedor = obj
    },
    verVentasEstrategicas () {
      this.axios.get(this.API + 'ventasestrategicas', {
        params: {
          Usuario: this.vendedor.Usuario,
          IdAlmacen: this.almacen.IdAlmacen,
          FechaIni: this.fechaDesde,
          FechaFin: this.fechaHasta
        }
      })
      .then((response) => {
          this.total = response.data.total
      })
      .catch((error) => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    }
  }
}
</script>
<style scoped>
.alert {
  margin: 0 auto;
}
</style>
