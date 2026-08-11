<template>
  <div class="ub-picker" ref="picker">
    <div class="input-group input-group-sm">
      <input type="text" class="form-control" :value="value" readonly
        :placeholder="placeholder" @click="abrir">
      <div class="input-group-append">
        <b-button size="sm" variant="outline-secondary" @click="abrir">Buscar</b-button>
      </div>
    </div>
    <div v-if="descripcion" class="ub-descripcion">{{ descripcion }}</div>

    <div v-if="abierto" class="ub-panel">
      <input type="text" class="form-control form-control-sm ub-search" v-model="busqueda"
        @input="buscar" placeholder="Buscar departamento, provincia o distrito" autofocus>

      <div class="ub-lista">
        <template v-if="busqueda.trim().length >= 3">
          <div v-if="buscando" class="ub-empty">Buscando…</div>
          <div v-else-if="!resultados.length" class="ub-empty">Sin resultados</div>
          <div v-else v-for="r in resultados" :key="r.id" class="ub-resultado" @click="seleccionarBusqueda(r)">
            <strong>{{ r.nombre }}</strong>
            <small>{{ r.provincia }} — {{ r.departamento }} ({{ r.id }})</small>
          </div>
        </template>
        <template v-else>
          <div v-if="cargandoRaiz" class="ub-empty">Cargando…</div>
          <ubigeo-nodo v-for="dep in departamentos" :key="dep.id" :nodo="dep" @seleccionar="onNodo"></ubigeo-nodo>
        </template>
      </div>
    </div>
  </div>
</template>

<script>
import UbigeoNodo from '@/components/UbigeoNodo'
import config from '@/store/config.js'

export default {
  name: 'UbigeoPicker',
  components: { UbigeoNodo },
  props: {
    value: { type: String, default: '' },
    placeholder: { type: String, default: 'Ej. 100101' }
  },
  data () {
    return {
      API: config.API,
      abierto: false,
      departamentos: [],
      cargandoRaiz: false,
      busqueda: '',
      resultados: [],
      buscando: false,
      timerBusqueda: null,
      descripcion: ''
    }
  },
  mounted () {
    document.addEventListener('click', this.clickFuera)
  },
  beforeDestroy () {
    document.removeEventListener('click', this.clickFuera)
  },
  methods: {
    clickFuera (evento) {
      if (!this.abierto) return
      if (this.$refs.picker && !this.$refs.picker.contains(evento.target)) {
        this.abierto = false
        this.colapsarTodo()
      }
    },
    abrir () {
      this.abierto = true
      if (!this.departamentos.length) this.cargarRaiz()
    },
    colapsarTodo () {
      const colapsarNodo = (nodo) => {
        nodo.expandido = false
        if (nodo.hijos) {
          nodo.hijos.forEach(colapsarNodo)
        }
      }
      this.departamentos.forEach(colapsarNodo)
    },
    cargarRaiz () {
      this.cargandoRaiz = true
      this.axios.get(this.API + 'ubigeo/hijos')
        .then((response) => {
          this.departamentos = response.data.map(n => ({
            ...n, expandido: false, hijos: null, cargando: false, ruta: [n.nombre]
          }))
        })
        .catch(() => { this.$toasted.show('No se pudo cargar el ubigeo') })
        .finally(() => { this.cargandoRaiz = false })
    },
    onNodo (evento) {
      if (evento.toggle) {
        const nodo = evento.nodo
        if (nodo.expandido) {
          nodo.expandido = false
          return
        }
        if (!nodo.hijos) {
          nodo.cargando = true
          this.axios.get(this.API + 'ubigeo/hijos', { params: { padre: nodo.id } })
            .then((response) => {
              nodo.hijos = response.data.map(n => ({
                ...n, expandido: false, hijos: null, cargando: false, ruta: [...nodo.ruta, n.nombre]
              }))
              nodo.expandido = true
            })
            .catch(() => { this.$toasted.show('No se pudo cargar el ubigeo') })
            .finally(() => { nodo.cargando = false })
        } else {
          nodo.expandido = true
        }
      } else {
        this.confirmarSeleccion(evento.id, evento.ruta.join(' / '), evento)
      }
    },
    seleccionarBusqueda (item) {
      const ruta = [item.departamento, item.provincia, item.nombre].join(' / ')
      this.confirmarSeleccion(item.id, ruta, item)
    },
    confirmarSeleccion (id, descripcion, item) {
      this.descripcion = descripcion
      this.$emit('input', id)
      this.$emit('select', item)
      this.abierto = false
      this.busqueda = ''
      this.resultados = []
      this.colapsarTodo()
    },
    buscar () {
      clearTimeout(this.timerBusqueda)
      if (this.busqueda.trim().length < 3) {
        this.resultados = []
        return
      }
      this.timerBusqueda = setTimeout(() => {
        this.buscando = true
        this.axios.get(this.API + 'ubigeo/buscar', { params: { q: this.busqueda.trim() } })
          .then((response) => { this.resultados = response.data })
          .catch(() => { this.$toasted.show('No se pudo buscar el ubigeo') })
          .finally(() => { this.buscando = false })
      }, 300)
    }
  }
}
</script>

<style scoped>
.ub-picker { position: relative; }
.ub-descripcion {
  font-size: 11.5px;
  font-weight: 600;
  color: #010067;
  background: #eef0fb;
  padding: 4px 9px;
  border-radius: 6px;
  margin-top: 5px;
  display: inline-block;
  white-space: normal;
  line-height: 1.4;
}
.ub-panel {
  position: absolute;
  z-index: 50;
  top: 100%;
  left: 0;
  width: 380px;
  max-width: 90vw;
  background: #fff;
  border: 1px solid #e2e5ee;
  border-radius: 8px;
  box-shadow: 0 6px 20px rgba(1,0,103,0.15);
  padding: 8px;
  margin-top: 4px;
}
.ub-search { margin-bottom: 6px; }
.ub-lista { max-height: 320px; overflow-y: auto; }
.ub-empty { padding: 10px; font-size: 12.5px; color: #6b7290; text-align: center; }
.ub-resultado {
  padding: 6px 8px;
  cursor: pointer;
  border-radius: 5px;
  display: flex;
  flex-direction: column;
}
.ub-resultado:hover { background: #eef0fb; }
.ub-resultado small { color: #6b7290; }
</style>