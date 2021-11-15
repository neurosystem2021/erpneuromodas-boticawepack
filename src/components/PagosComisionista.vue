
<template>
    <div>
    
        <b-table small
            ref="tablaPagos"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            id="my-table" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage"></b-table>
    </div>
</template>

<script>
import config from '@/store/config.js'

export default {
    watch: {
        cliente(val) {
            this.$refs.tablaPagos.refresh()
        }
    },
    data () {
        return {
            API: config.API,
            pageOptions: [ 5, 10, 15, 50, 100 ],
            filter: '',
            sortBy: 'IdCajaBanco',
            sortDesc: false,
            items: [],
            isBusy: false,
            fields: [
                { key: 'FechaDoc' },
                { key: 'TipoCajaBanco' },
                { key: 'Concepto' },
                { key: 'Importe' },
            ],
            totalRows: 50,
            perPage: 10,
            currentPage: 1,
        }
    },
    props: ['cliente'],
    methods: {
        myProvider (ctx) {
            // Here we don't set isBusy prop, so busy state will be handled by table itself
            // this.isBusy = true
            // console.log(ctx.filter)
            // let url = this.API + 'productos/stock?idAlmacen='+this.almacen.IdAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter[producto]='+ (ctx.filter||'')+'&filter[minimo]=1&sortBy='+ctx.sortBy
            // url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
            let promise = this.axios.get( this.API + 'cliente/comisionistas/pagos?idComisionista=' + this.cliente.IdCliente + '&q=' + ctx.filter + '&limit=' + ctx.perPage + '&page=' + ctx.currentPage )

            return promise.then((response) => {
                this.items = response.data
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

<style></style>