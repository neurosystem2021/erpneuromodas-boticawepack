<template>
  <div>
    <b-form-group>
      <label>Asignar Series:</label>
      <b-input-group>
        <autocomplete
          required="required"
          ref="inputTipoDoc"
          :url="API + 'ventas/tipos'"
          anchor="TipoDoc"
          initValue="BOLETA ELEC."
          label="writer"
          placeholder="Seleccionar tipo documento"
          :on-select="addTipoDoc"
          :onFocus="focusAutocomplete.bind(null, 'inputTipoDoc')"
          :classes="{input: 'form-control upper'}">
        </autocomplete>
      </b-input-group>
    </b-form-group>
    <table class="table b-table table-sm">
      <thead>
        <tr>
          <th>ID</th>
          <th>Tipo Documento</th>
          <th>Serie</th>
          <th class="text-center">Quitar</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(tipodoc, index) in tipodocumentos" :key="index">
          <td>{{ tipodoc.IdTipoDoc }}</td>
          <td>{{ tipodoc.TipoDoc }}</td>
          <td><input type="text" class="form-control" v-model="tipodoc.Serie" required @input="emitUpdate" /></td>
          <td class="text-center"><b-button variant="danger" size="sm" @click="quitarTipoDocumento(tipodoc)">X</b-button></td>
        </tr>
      </tbody>
    </table>
  </div>    
</template>
<script>
import config from '@/store/config.js'

export default {
  mounted () {
    this.getAlmacenesDet()
  },
  data () {
    return {
      show: false,
      API: config.API,
      tipodocumentos: []
    }
  },
  props: ['IdAlmacen'],
  methods: {
    emitUpdate () {
      this.$emit('onUpdateAlmacenes', this.tipodocumentos)
    },
    getAlmacenesDet () {
      this.axios.get(this.API + "gestion/almacenes/series?IdDocVentaPuntoVenta=", {
          params: {
              IdDocVentaPuntoVenta: this.IdAlmacen
          }
      }).then((response) => {
          this.tipodocumentos = response.data
      })

    },
    addTipoDoc (data) {
      let index = this.tipodocumentos.findIndex(el => el.IdTipoDocDet == data.IdTipoDoc)
      if (index >= 0) {
        this.$toasted.show('El Documento ya está agregado')
        return
      }
      data.IdTipoDocDet = data.IdTipoDoc
      this.tipodocumentos.push(data)
      this.emitUpdate()
    },
    quitarTipoDocumento (tipodoc) {
      let index = this.tipodocumentos.findIndex(el => el.IdTipoDocDet == tipodoc.IdTipoDocDet)
      this.tipodocumentos.splice(index, 1)
      this.emitUpdate()
    },
    focusAutocomplete (ref) {
      this.$refs[ref].handleDoubleClick()
    },
  }
}
</script>
<style>

</style>
