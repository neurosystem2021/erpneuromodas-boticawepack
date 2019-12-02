<template>
  <div>
      <b-container class="bv-example-row">
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
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Registros</p>
            </b-col>
            </b-row>
          </b-col>
        </b-row>

        <b-row>         
          <b-col md="12">
            <b-table small
              ref="tablaDeudaCliente"
              :filter="filter"
              :sort-by.sync="sortBy"
              :sort-desc.sync="sortDesc"
              striped
              hover
              @row-clicked="rowClick"
              id="tabla-deuda" 
              :busy.sync="isBusy" 
              :items="myProvider" 
              :fields="fields"
              :per-page="perPage"
              :current-page="currentPage">
              </b-table>
          </b-col>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
      </b-container>
      
      <deuda-cliente ref="deudaClienteForm" :clienteSelect="clienteSelect"></deuda-cliente>
      
  </div>
</template>

<script>
import config from '@/store/config.js'
import DeudaCliente from '@/components/DeudaCliente'

export default {
  mounted() {
    this.axios.get(this.API + 'reportes/deudacliente/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'DniRuc',
      sortDesc: false,
      clienteSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'DniRuc', label: 'DNI/RUC', sortable: true },
            { key: 'Cliente', label: 'Cliente', sortable: true },
            { key: 'Deuda Acumulada', label: 'Deuda Acumulada', sortable: true },
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
  methods: {
    focusAutocomplete (ref) {
      this.$refs[ref].handleDoubleClick()
    },
    rowClick (item, index, e) {
      this.clienteSelect = item
      this.$refs.deudaClienteForm.show = true
    },
    rowDblClick (item) {
    },
    actualizarTabla() {
      this.$refs.tablaDeudaCliente.refresh()
    },
    myProvider (ctx) {
      let url = this.API + 'reportes/deudacliente?' + '&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')
      let promise = this.axios.get( url )
      return promise.then((result) => {
        this.items = result.data
        return(this.items)
      }).catch(error => {
        return []
      })
    },
  },
  components: {
    DeudaCliente
  }
}
</script>

<style scope>
#tabla-productos tr {
  cursor: pointer;
}
.table-detalle {
  height: 80px;
}
.table-detalle th {
  color: #fff;
  background-color: #6c757d; 
}
.table-detalle tbody td {
  font-size: 18px;
  font-weight: bold;
  color: #2980b9;
}
.table-detalle thead th:first-child {
  max-width: 300px;
}
.suma-stock {
  background: #a6ccd2;
    color: #fff;
}
.suma-stock div {
  font-size: 32px;
  font-weight: bold;
}
@media (min-width: 992px) {
    .modal-xl .modal-dialog {
        max-width: 1200px;
    }
}
</style>
