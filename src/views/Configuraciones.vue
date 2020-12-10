<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
           <b-col md="12" class="my-1">
                <table class="table">
                    <thead>

                    </thead>
                    <tbody>
                        <tr>
                            <td> Regenerar Códigos de productos.</td>
                            <td> <button class="btn btn-success" @click="regenerarCodigos">Regenerar</button> </td>
                        </tr>
                          <td></td>
                          <td></td>
                        <tr></tr>
                    </tbody>
                </table>
           </b-col>
        </b-row>
        <br />
        <br />
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
      API: config.API,
    }
  },
  methods: {
    regenerarCodigos () {
      let r = confirm('¿Está seguro que desea regenerar los Códigos?')
      if (r) {
        this.axios.post(this.API + 'generarcodigos').then(r => {
          if (r.data.updated) {
            this.$toasted.success('Códigos generados correctamente')
          }

        })
      }
    }
  },
  components: {
  }
}
</script>

<style>

</style>
