<template>
<section ref="cboUbigeo">
  <input v-if="cboClose" type="text" class="form-control form-control-sm"
   id="direccionDestino" placeholder="Dirección de Llegada"
    v-model="Ubigeo" @focus="cboClose=false">
  <div v-else class="listado">
      <input 
        type="text" 
        class="form-control form-control-sm my-1"
        placeholder="Buscar Departamento" 
        v-model="search" 
      />
      <ul class="classDepartamento" v-for="departamento in retornarDataUbigeo()" :key="departamento.id">
        <li @click="obtenerSelect(departamento)">
          <span>
           <i class="fa fa-chevron-right" v-if="!departamento.selected" aria-hidden="true"></i> 
           <i class="fa fa-chevron-down" v-else aria-hidden="true"></i> 
           {{departamento.name}}
          </span>
          <ul class="classProvincia" v-for="provincia in departamento.children" :key="provincia.id">
            <li @click="obtenerSelect(provincia); $event.stopPropagation()" v-if="departamento.selected">
              <span>
                <i class="fa fa-chevron-right" v-if="!provincia.selected" aria-hidden="true"></i> 
                <i class="fa fa-chevron-down" v-else aria-hidden="true"></i> 
                {{provincia.name}}
              </span>
              <ul class="classDistrito" v-for="distrito in provincia.children" :key="distrito.id">
                <li @click="guardarIdUbigeo(distrito.id, distrito.name, provincia.name, departamento.name); $event.stopPropagation()" v-if="provincia.selected">
                  <span>
                    {{distrito.name}}
                  </span>
                </li>
              </ul>
            </li>
          </ul>
        </li>
      </ul>
  </div>
</section>
</template>

<script>
export default {
  props: ["datos"],
  data() {
    return {
      cboClose: true,
      Ubigeo: "",
      search: ""
    };
  },
  mounted() {
    document.addEventListener("click", this.handleClickOutside);
  },
  beforeDestroy() {
    document.removeEventListener("click", this.handleClickOutside);
  },
  methods: {
    handleClickOutside(event) {
      if (!this.$refs.cboUbigeo.contains(event.target)) {
        this.cboClose = true;
      }
    },
    obtenerSelect(item) {
      item.selected = !item.selected;
      this.$forceUpdate();
    },
    guardarIdUbigeo(id, distrito, provincia, departamento) {
      let nameDepartamento = departamento.replace(/^DEPARTAMENTO\s*/, "");
      this.Ubigeo = `${nameDepartamento} > ${provincia} > ${distrito}`;
      this.$emit("idUbigeo", id);
      this.cboClose = true;
    },
    retornarDataUbigeo() {
      if (this.search) {
        return this.datos.filter(v => v.name.includes(this.search));
      }
      return this.datos;
    }
  },
  watch: {
    cboClose() {
      if (this.cboClose) {
        this.search = "";
      }
      this.datos.map(item => {
        item.selected = false;
      });
    }
  }
};
</script>

<style scoped>
section {
  padding: 5px 5px;
  margin: 0 0px;
  width: 100%;
}
.listado {
  position: absolute;
  z-index: 99;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 1);
  background: #fff;
  width: 96%;
}
ul {
  list-style: none;
  cursor: pointer;
  margin: 0;
}
li {
  width: 100%;
}
.classDepartamento {
  padding: 0px;
}

.classProvincia .classDistrito {
  padding: 0 0 0 50px;
}
span {
  display: block;
  border: 1px solid #ced4da;
  padding: 4px 8px;
  color: #495057;
  width: 100%;
  max-height: 31px;
  border-radius: 5px;
  box-sizing: border-box;
  background: white;
}
</style>
