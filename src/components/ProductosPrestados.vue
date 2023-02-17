<template>
<div class="table-responsive">
    <table class="table b-table table-sm">
        <thead>
            <tr class="text-center">
                <th>FECHA</th>
                <th>PRESTADOR</th>
                <th>TIPO DOC</th>
                <th>SERIE</th>
                <th>NUMERO</th>
                <th>CLIENTE</th>
                <th>DIRECCIÓN</th>
                <th class="table-danger">CANTIDAD</th>
            </tr>
        </thead>
        <tbody>
            <tr class="text-center" v-for="(item, i) in productosPrestados" :key="i">
                <td>{{ item.FechaReg }}</td>
                <td>{{ item.Repartidor==undefined?item.NombreUsuario:item.Repartidor }}</td>
                <td>{{ item.TipoDoc }}</td>
                <td>{{ item.Serie }}</td>
                <td>{{ item.Numero }}</td>
                <td>{{ item.Cliente }}</td>
                <td>{{ item.DireccionTemp==""?'---':item.DireccionTemp }}</td>
                <td >{{ item.Prestados }}</td>
            </tr>
        </tbody>
    </table>
</div>
</template>
<script>
import config from '@/store/config.js'

export default {
    mounted () {
        //this.cargarPrestados() 
    },
    data () {
        return {
            API: config.API,
            prestados: []
        }
    },
    props: ['producto'],
    watch: {
        producto (value) {
            if (value.IdProducto) {
                this.cargarPrestados()
            }
        }
    },
    computed: {
        productosPrestados () {
            return this.prestados.filter(item => item.Prestados > 0)
        }
    },
    methods: {
        cargarPrestados () {
           this.axios.get(this.API + 'productos/prestados/clientes', {
               params: {
                   idProducto: this.producto.IdProducto
               }
           })
           .then(response => {
               this.prestados = response.data
           })
        }
    }
}
</script>
<style scoped>
table {
    font-size: .8em;
}
table thead th {
    vertical-align: middle;
}
.table-danger {
    text-align: center;
}

</style>
