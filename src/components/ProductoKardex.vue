<template>
<div class="kardex-product-table-wrap">
    <table class="table b-table kardex-product-table">
        <thead>
            <tr>
                <th rowspan="2">FECHA</th>
                <th rowspan="2">USUARIO</th>
                <th rowspan="2">COMPROBANTE</th>
                <th rowspan="2">PROVEEDOR/CLIENTE</th>
                <th colspan="2" class="kardex-entry-head">INGRESOS</th>
                <th colspan="2" class="kardex-exit-head">SALIDAS</th>
                <th rowspan="2" class="kardex-balance-head">SALDO</th>
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
                <td :class="{'kardex-entry-value': prod.IngresoCantidad > 0}">{{ prod.IngresoCantidad }}</td>
                <td :class="{'kardex-entry-value': prod.IngresoPrecio > 0}">{{ prod.IngresoPrecio | redondear }}</td>
                <td :class="{'kardex-exit-value': prod.SalidaCantidad > 0}">{{ prod.SalidaCantidad }}</td>
                <td :class="{'kardex-exit-value': prod.SalidaPrecio > 0}">{{ prod.SalidaPrecio | redondear }}</td>
                <td class="kardex-balance-value">{{ prod.saldo }}</td>
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
.kardex-product-table-wrap{width:100%;overflow-x:auto;border:1px solid #d9e6f0;border-radius:15px}.kardex-product-table{min-width:980px;margin:0}.kardex-product-table thead{color:#fff;background:#0b5787}.kardex-product-table thead th{padding:11px 9px;vertical-align:middle;border-color:rgba(255,255,255,.15);font-size:11px}.kardex-product-table td{padding:10px 9px;vertical-align:middle;border-color:#e3edf4;font-size:12px}.kardex-product-table tbody tr:nth-child(even){background:#f4f8fb}.kardex-product-table tbody tr:hover{background:#eaf5fb}.kardex-entry-head{background:#167a67}.kardex-exit-head{background:#a84d60}.kardex-balance-head{background:#263f61;text-align:center}.kardex-entry-value{color:#007a4a;background:#d9f5e7;font-weight:900}.kardex-exit-value{color:#b51f3c;background:#ffe0e6;font-weight:900}.kardex-balance-value{text-align:center;color:#0b4f7f;background:#dcecf7;font-weight:900}

</style>
