<template>
  <div>
    <b-table
      :items="items"
      :fields="fields"
      :busy="loading"
      striped
      hover
      small
      responsive
      show-empty
      empty-text="No hay datos para los filtros seleccionados"
    >
      <template #table-busy>
        <div class="text-center my-3">
          <b-spinner small /> Cargando...
        </div>
      </template>

      <template #bottom-row v-if="items && items.length">
        <td v-for="(f, idx) in fields" :key="f.key">
          <b v-if="idx === 0">Total General</b>
          <b v-else-if="esNumerica(f.key)">{{ totalColumna(f.key) }}</b>
        </td>
      </template>
    </b-table>
  </div>
</template>

<script>
export default {
  name: 'TablaReporte',
  props: {
    items: { type: Array, default: () => [] },
    fields: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false }
  },
  methods: {
    esNumerica (key) {
      if (!this.items || !this.items.length) return false
      const v = this.items[0][key]
      return typeof v === 'number' || (!isNaN(parseFloat(v)) && isFinite(v))
    },
    totalColumna (key) {
      const total = this.items.reduce((acc, row) => acc + (parseFloat(row[key]) || 0), 0)
      return total.toFixed(2)
    }
  }
}
</script>