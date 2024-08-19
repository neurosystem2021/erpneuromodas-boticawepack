<template>
  <input v-if="cboClose" type="text" class="form-control form-control-sm" id="direccionDestino" placeholder="Dirección de Llegada" v-model="Ubigeo" @blur="cboClose=false">
  <section v-else>

      <ul class="classDepartamento" v-for="departamento in datos" :key="departamento.id" 
          >
        <li @click="obtenerSelect(departamento)" > 
          <span>
           <i class="fa fa-chevron-right" v-if="!departamento.selected" aria-hidden="true"></i> 
           <i class="fa fa-chevron-down" v-else aria-hidden="true"></i> 
           {{departamento.name}}
          </span>
          <ul class="classProvincia" v-for="provincia in departamento.children" :key="provincia.id" >

            <li @click="obtenerSelect(provincia); $event.stopPropagation()"  v-if="departamento.selected">
              <span>
             <i class="fa fa-chevron-right" v-if="!provincia.selected" aria-hidden="true"></i> 
             <i class="fa fa-chevron-down" v-else aria-hidden="true"></i> 
             {{provincia.name}}

              </span>
                <ul class="classDistrito" v-for="distrito in provincia.children" :key="distrito.id" >
                  <li @click="guardarIdUbigeo(distrito.id,distrito.name,provincia.name,departamento.name); $event.stopPropagation()" v-if="provincia.selected">
                    <span>
                    {{distrito.name}}

                    </span>
                  </li>
                </ul>
              
            </li>

          </ul>
        </li>

      </ul>
  </section>

</template>

<script>
import { mapState } from "vuex";
export default {
  props: ["datos"],
  data() {
    return {
      cboClose: false,
      Ubigeo: "",
      departamentos: [],
      provincias: [],
      distritos: []
    };
  },
  mounted() {
    // this.getListadoUbigeo();
  },
  watch: {
    // listUbigeo() {
    //   this.getListadoUbigeo();
    // }
  },
  methods: {
    obtenerSelect(item) {
      item.selected = !item.selected;
      this.$forceUpdate();
      console.log(item);
    },
    guardarIdUbigeo(id, distrito, provincia, departamento) {
      let nameDepartamento = departamento.replace(/^DEPARTAMENTO\s*/, "");
      this.Ubigeo = `${nameDepartamento} > ${provincia} > ${distrito}`;
      this.$emit("idUbigeo", id);
      this.cboClose = true;
    }
  },
  computed: {
    ...mapState(["listUbigeo"])
  }
};
</script>

<style scoped>
section {
  padding: 5px 0 px;
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
  border: 1px solid black;
  padding: 10px 10px;
  width: 100%;
  border-radius: 5px;
  box-sizing: border-box;
}
</style>
