<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
            <h1>res</h1>
        </b-row>

      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'


export default {
  mounted() {

  },
  data() {
    return {
      API: config.API
    }
  },
  methods: {

  },
  components: {
      
  }
}
</script>

<style>

</style>