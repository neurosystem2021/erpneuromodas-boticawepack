<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="12" class="text-center">
            <h4><b style="text-decoration: underline;">PLAN CONTABLE GENERAL PARA EMPRESAS</b></h4>
            <h5>REGISTRO DE PLAN DE CUENTAS</h5>
          </b-col>
        </b-row>
        <hr>
        <b-row>
          <b-col md="6" class="my-1">
            <b-form-group horizontal label="Buscar" class="mb-0">
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                <b-input-group-append>
                  <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
            <br />
          </b-col>
          <b-col md="6" class="my-1">
            <b-row>
            <b-col md="8">

              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
            <b-col md="4">
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Artículos</p>
            </b-col>
            </b-row>
          </b-col>

          <b-table small
            ref="tablaCuenta"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            @row-clicked="rowClick"
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
        </b-row>
        <br />
        <br />
        
        <b-modal id="modalCuenta" size="md" ref="modalCuenta" hide-footer hide-header>
          <div class="d-block text-left">
            <h5>Información:</h5>
            <b-row>
              <b-col md="6">
                <b-form-group>
                  <label>Cuenta:</label>
                  <input
                    v-model="cuentaSelect.Cuenta"
                    type="text"
                    readonly
                    class="form-control"
                    />
                </b-form-group>
              </b-col>
              <b-col md="12">
                <b-form-group>
                  <label>Nombre:</label>
                  <textarea
                    v-model="cuentaSelect.Nombre"
                    type="text"
                    readonly
                    class="form-control"
                    rows="4"
                  ></textarea>
                </b-form-group>
              </b-col>
            </b-row>
            <hr>
            <h5>Estructura:</h5>
            <b-row>
              <b-col md="6">
                <b-input-group prepend="Cuenta"  class="mt-2 mb-2">
                    <input v-model="cuentaSelect.NumCta"
                        autocomplete="off"
                        ref="barcodeInput"
                        type="text"
                        disabled
                        class="form-control"
                    />
                </b-input-group> 
              </b-col>
            </b-row>
            <b-row>
              <b-col md="6">
                <b-input-group prepend="Sub Cuenta"  class="mt-2 mb-2">
                    <input v-model="cuentaSelect.NumSubCta"
                        autocomplete="off"
                        ref="barcodeInput"
                        type="text"
                        disabled
                        class="form-control"
                    />
                </b-input-group> 
              </b-col>
            </b-row>
            <b-row>
              <b-col md="6">
                <b-input-group prepend="Divisionaria"  class="mt-2 mb-2">
                    <input v-model="cuentaSelect.NumDivi"
                        autocomplete="off"
                        ref="barcodeInput"
                        type="text"
                        disabled
                        class="form-control"
                    />
                </b-input-group> 
              </b-col>
            </b-row>
            <b-row>
              <b-col md="6">
                <b-input-group prepend="Sub Divisionaria"  class="mt-2 mb-2">
                    <input v-model="cuentaSelect.NumSubDivi"
                        autocomplete="off"
                        ref="barcodeInput"
                        type="text"
                        disabled
                        class="form-control"
                    />
                </b-input-group> 
              </b-col>
            </b-row>
          </div>
        </b-modal>
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'

export default {
  mounted() {
    this.axios.get(this.API + 'contabilidad/cuentas/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 30, 50, 100 ],
      filter: null,
      sortBy: 'IdCuentaContable',
      sortDesc: false,
      cuentaSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'IdCuentaContable', label: 'Id', sortable: true },
            { key: 'Cuenta', sortable: true },
            { key: 'Nombre', label: 'Nombre', sortable: true},
            { key: 'Cargo1', label: 'Cargo1', sortable: true },
            { key: 'Cargo2', label: 'Cargo2', sortable: true },
            { key: 'Abono', label: 'Abono', sortable: true },
      ],
      totalRows: 50,
      perPage: 30,
      currentPage: 1
    }
  },
  methods: {
      rowClick (item, index) {
        this.$refs['modalCuenta'].show()
        this.cuentaSelect = item
        console.log(item);
      },
      actualizarTabla() {
        this.$refs.tablaCuenta.refresh()
      },
      nuevaCuenta () {
          this.cuentaSelect = {}
          this.$refs.cuentaForm.show = true

      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'contabilidad/cuentas?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
        url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
        let promise = this.axios.get( url )

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
  },
  components: {
  }
}
</script>

<style>
.input-group-text{
    font-weight: bold;
    font-size: 0.95rem;
}
</style>
