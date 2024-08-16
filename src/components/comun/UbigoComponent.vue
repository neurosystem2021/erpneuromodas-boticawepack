<template>
  <section v-if="departamentos.length && provincias.length && distritos.length">
    <ul class="departamento" v-for="departamento in departamentos" :key="departamento.id">
        <li> 
            <details>
                <summary> {{ departamento.nombre }}  </summary>

        <ul class="provincia" v-for="provincia in provincias.filter(v => v.id_padre == departamento.id)" :key="provincia.id">
            <li> {{ provincia.nombre }} </li>
            <ul class="distrito" v-for="distrito in distritos.filter(v => v.id_padre == provincia.id)" :key="distrito.id">
                {{ distrito.nombre }}
            </ul>
        </ul>
            </details>
        </li>
    </ul>    
  </section>
  <p v-else>Cargando datos...</p>
</template>

<script>
import { mapState } from "vuex";
export default {
  data() {
    return {
      departamentos: [],
      provincias: [],
      distritos: []
    };
  },
  mounted() {
    this.getListadoUbigeo();
  },
  watch: {
    listUbigeo() {
      this.getListadoUbigeo();
    }
  },
  methods: {
    async getListadoUbigeo() {
      this.departamentos = this.listUbigeo.filter(v => v.nivel == 1);
      this.provincias = this.listUbigeo.filter(v => v.nivel == 2);
      this.distritos = this.listUbigeo.filter(v => v.nivel == 3);
    },
    toggleProvincia(provincia) {
      provincia.expanded = !provincia.expanded;
    }
  },
  computed: {
    ...mapState(["listUbigeo"])
  }
};
</script>

<style scoped>
ul {
  list-style: none;
}
.tree summary {
  cursor: pointer;
}
.tree summary ::marker {
  display: none;
}
.tree summary::-webkit-details-marker {
  display: none;
}
</style>
