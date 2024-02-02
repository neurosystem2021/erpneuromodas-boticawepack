<template>
    <div>
        <b-container class="bv-example-row">
            <b-row class="text-center">
                <b-col>
                    <h1>Registro de Conductores</h1>
                </b-col>
            </b-row>
            <hr>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-form-group horizontal label="Buscar" class="mb-0">
                        <b-input-group>
                            <b-form-input v-model="filter" autocomplete="off" placeholder="Buscar"
                                @input="currentPage = 1" />
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
                                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1" />
                            </b-form-group>
                        </b-col>
                        <b-col md="4">
                            <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Conductores</p>
                        </b-col>
                    </b-row>
                </b-col>

                <b-table small ref="tablaConductores" :filter="filter" :sort-by.sync="sortBy" :sort-desc.sync="sortDesc"
                    striped hover @row-clicked="rowClick" id="my-table" :busy.sync="isBusy" :items="myProvider"
                    :fields="fields" :per-page="perPage" :current-page="currentPage">

                    <template slot="Anulado" slot-scope="data">
                        <b-badge pill :variant="data.item.Anulado ? 'danger' : 'success'">{{
                            data.item.Anulado ? 'Anulado' : 'Activo' }}</b-badge>
                    </template>
                </b-table>
                <b-row>
                    <b-col md="6" class="my-1">
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                    </b-col>
                </b-row>
            </b-row>
            <conductor-form ref="conductorForm" :conductorSelect="conductorSelect" :cbGuardar="actualizarTabla"></conductor-form>
            <br />
            <b-row align-h="center" class="text-center">
                <b-col cols="4">
                    <b-button variant="success" size="lg" @click="nuevoConductor">Nuevo Conductor</b-button>
                </b-col>
            </b-row>
            <br /><br />
        </b-container>
    </div>
</template>
  
<script>
import config from '@/store/config.js'
import ConductorForm from '@/components/ConductorForm.vue'

export default {
    mounted() {
    },
    data() {
        return {
            API: config.API,
            pageOptions: [5, 10, 15, 50, 100],
            filter: null,
            sortBy: 'IdConductor',
            sortDesc: false,
            conductorSelect: {},
            items: [],
            isBusy: false,
            fields: [
                { key: 'IdConductor', label: 'ID', sortable: true },
                { key: 'Dni', label: 'DNI', sortable: true },
                { key: 'Nombres', sortable: true },
                { key: 'Apellidos', sortable: true },
                { key: 'Direccion', label: 'Domicilio', sortable: true },
                { key: 'Licencia', },
                { key: 'FechaReg', label: 'Fecha de Registro' },
                { key: 'Anulado', label: 'Estado', sortable: true },
            ],
            totalRows: 50,
            perPage: 10,
            currentPage: 1
        }
    },
    methods: {
        rowClick(item, index) {
            this.conductorSelect = item
            this.$refs.conductorForm.show = true;
        },
        actualizarTabla() {
            this.$refs.tablaConductores.refresh()
        },
        nuevoConductor() {
            this.conductorSelect = {}
            this.$refs.conductorForm.show = true

        },
        myProvider(ctx) {
            // Here we don't set isBusy prop, so busy state will be handled by table itself
            // this.isBusy = true
            let url = this.API + 'transporte/conductor?limit=' + ctx.perPage + '&page=' + ctx.currentPage + '&q=' + (ctx.filter || '') + '&sortBy=' + ctx.sortBy
            url += '&sortDesc=' + (ctx.sortDesc ? 1 : 0)
            let promise = this.axios.get(url)

            return promise.then((result) => {
                this.items = result.data.data
                this.totalRows = result.data.count
                this.items.map(r => {
                    if (r.Anulado == 1)
                        r._rowVariant = 'danger'
                })
                return (this.items)
            }).catch(error => {
                // Here we could override the busy state, setting isBusy to false
                // this.isBusy = false
                // Returning an empty array, allows table to correctly handle busy state in case of error
                return []
            })
        }
    },
    components: {
        ConductorForm
    }
}
</script>
  
<style></style>