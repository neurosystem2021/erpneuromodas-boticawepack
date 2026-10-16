<template>
<div>
    <b-btn v-b-modal.modal1>click</b-btn>
    <!-- Modal Component -->
    <b-modal id="modal1" title="Tabla" size="lg" modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
      <b-table 
        id="my-table" 
        :busy.sync="isBusy" 
        :items="myProvider" 
        :fields="fields"
        :per-page="perPage"
        :current-page="currentPage"></b-table>
      <b-row>
        <b-col md="6" class="my-1">
          <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
        </b-col>
      </b-row>
    </b-modal>
</div>
</template>

<script>
import config from '@/store/config.js'

export default {
  mounted() {
    this.axios.get(API + 'proveedores/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      items: [],
      isBusy: false,
      fields: [ 'Proveedor', 'Direccion' ],
      totalRows: 50,
      perPage: 5,
      currentPage: 1
    }
  },
  methods: {
      myProvider (ctx) {
        console.log(ctx)
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let promise = this.axios.get(API + 'proveedores?limit='+ctx.perPage+'&page='+ctx.currentPage)

        return promise.then((result) => {
          this.items = result.data
          //this.totalRows = this.items.length
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          return(this.items)
        }).catch(error => {
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          // Returning an empty array, allows table to correctly handle busy state in case of error
          return []
        })
      }
  }
}
</script>

<style>

</style>
