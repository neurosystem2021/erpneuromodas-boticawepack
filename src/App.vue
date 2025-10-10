<template>
  <div id="app">
    <div
      v-if="!$route.meta.muestraComboEmpresa"

    >
      <b-input-group class="my-2 ml-5" style="width: 25%;">
              <b-form-select
              v-model="empresa"
              :options="$store.state.empresas"
              size="lg"
              class="form-control"
              value-field="IDEMPRESA"
              text-field="RAZONSOCIAL"
              @change="cambiarEmpresa"
              disabled
              
              ></b-form-select>
            </b-input-group>
    </div>
    <router-view/>
  </div>
</template>

<script>
import { mapActions } from "vuex";

export default {
  name: "App",
  data() {
    return {
      empresa: sessionStorage.getItem("empresa")
    };
  },
  mounted() {
    console.log("empresa", this.empresa);
    this.obtenerEmpresa();
  },
  methods: {
    ...mapActions(["obtenerEmpresa"])
  }
};
</script>

<style>
.btnAction {
  margin: 5px 0;
  border-radius: 25px !important;
  -webkit-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
  -moz-box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
  box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
  font-size: 1.2rem;
  font-weight: bold;
  text-transform: uppercase;
  width: 80%;
}

.modal-xl {
  min-width: 90% !important;
}
</style>
