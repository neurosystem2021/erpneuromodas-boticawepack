<template>
  <div class="ub-nodo">
    <div class="ub-fila" :class="'ub-nivel-'+nodo.nivel" @click="clic">
      <span class="ub-caret" v-if="nodo.nivel < 3">
        <svg v-if="nodo.expandido" viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M7 10l5 5 5-5z"/></svg>
        <svg v-else viewBox="0 0 24 24" width="12" height="12" fill="currentColor"><path d="M10 17l5-5-5-5z"/></svg>
      </span>
      <span class="ub-caret" v-else></span>
      <span class="ub-nombre">{{ nodo.nombre }}</span>
      <span v-if="nodo.cargando" class="ub-cargando">…</span>
    </div>
    <div v-if="nodo.expandido && nodo.hijos" class="ub-hijos">
      <ubigeo-nodo v-for="h in nodo.hijos" :key="h.id" :nodo="h" @seleccionar="$emit('seleccionar', $event)"></ubigeo-nodo>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UbigeoNodo',
  props: { nodo: { type: Object, required: true } },
  methods: {
    clic () {
      if (this.nodo.nivel === 3) {
        this.$emit('seleccionar', this.nodo)
      } else {
        this.$emit('seleccionar', { toggle: true, nodo: this.nodo })
      }
    }
  }
}
</script>

<style scoped>
.ub-fila {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 5px 8px;
  cursor: pointer;
  font-size: 12.5px;
  border-radius: 5px;
}
.ub-fila:hover { background: #f4f5f9; }
.ub-nivel-1 .ub-nombre { font-weight: 700; }
.ub-nivel-2 .ub-nombre { font-weight: 600; color: #444; }
.ub-nivel-3 .ub-nombre { color: #1c2340; }
.ub-hijos { padding-left: 16px; }
.ub-caret { width: 12px; display: flex; align-items: center; flex-shrink: 0; color: #6b7290; }
.ub-cargando { font-size: 11px; color: #6b7290; }
</style>