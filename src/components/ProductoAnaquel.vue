<template>
  <b-container>
    <b-row>
        <b-col cols="12">Anaquel</b-col>
        <b-col cols="12">
             <autocomplete
                ref="autocompleteAnquel"
                :url="`${API}Anaquel`"
                anchor="Anaquel"
                label="writer"
                placeholder="Agregar Anaquel"
                :on-select="setAnquel"
                :onFocus="focusAutocomplete.bind(null, 'autocompleteAnquel')"
                :classes="{input: 'form-control'}">
            </autocomplete>
        </b-col>
        <b-col cols="12">
            <b-table :items="ProductoAnaquel">

            </b-table>
        </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  props: ["ProductoAnaquel"],
  data() {
    return {
      API: config.API
    };
  },
  methods: {
    setAnquel(obj) {
      console.log(obj);
      if (this.ProductoAnaquel.some(v => v.IdAnaquel == obj.IdAnaquel)) {
        return;
      }
      console.log(obj);
      this.ProductoAnaquel.push(obj);
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    }
  }
};
</script>

<style>
</style>
