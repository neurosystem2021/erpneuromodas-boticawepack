<template>
<div class="table-responsive">
    <table class="table b-table table-sm">
        <thead>
            <tr>
                <th rowspan="2">FECHA</th>
                <th rowspan="2">USUARIO</th>
                <th rowspan="2">COMPROBANTE</th>
                <th rowspan="2">PROVEEDOR/CLIENTE</th>
                <th colspan="2">INGRESOS</th>
                <th colspan="2">SALIDAS</th>
                <th rowspan="2" class="table-danger">SALDO</th>
            </tr>
            <tr>
                <th>Cant.</th>
                <th>Precio.</th>
                <th>Cant.</th>
                <th>Precio.</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(prod, i) in kardexSaldo" :key="i">
                <td>{{ prod.Fecha }}</td>
                <td>{{ prod.UsuarioReg }}</td>
                <td>{{ prod.Detalle }} - {{ prod.Serie }} - {{ prod.Numero }}</td>
                <td>{{ prod.Nombres }}</td>
                <td :class="{'table-warning': prod.IngresoCantidad > 0}">{{ prod.IngresoCantidad }}</td>
                <td :class="{'table-warning': prod.IngresoPrecio > 0}"><b>{{ prod.IngresoPrecio > 0 ? prod.Moneda : '' }}</b> {{ prod.IngresoPrecio | redondear }}</td>
                <td :class="{'table-warning': prod.SalidaCantidad > 0}">{{ prod.SalidaCantidad }}</td>
                <td :class="{'table-warning': prod.SalidaPrecio > 0}"><b>{{ prod.SalidaPrecio > 0 ? prod.Moneda : '' }}</b> {{ prod.SalidaPrecio | redondear }}</td>
                <td class="table-danger">{{ prod.saldo }}</td>
            </tr>
        </tbody>
    </table>
</div>
</template>
<script>
export default {
    mounted () {
    },
    data () {
        return {
            saldo: 0
        }
    },
    props: ['producto', 'kardex'],
    methods: {
        getSaldo(prod) {
                let saldo = 0
            return ((prod) => {
                saldo += 10
                return saldo

            })(prod)
           /* let newSaldo = saldo + (prod.IngresoCantidad - prod.SalidaCantidad)
            this.saldo = newSaldo       
            return newSaldo*/
        }
    },
    computed: {
        kardexSaldo () {
            let saldo = 0
            return this.kardex.map(item => {
                saldo += (item.IngresoCantidad - item.SalidaCantidad)
                item.saldo = saldo
                return item
            })
        }
    }
}
</script>
<style scoped>
table thead th {
    vertical-align: middle;
}
.table-danger {
    text-align: center;
}

</style>
