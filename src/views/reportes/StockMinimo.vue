<template>

    <div class="control-stock">

        <!-- ===================================================== -->
        <!-- CABECERA -->
        <!-- ===================================================== -->

        <div class="page-header">

            <div class="header-title">

                <div class="header-icon">
                    <i class="fa fa-cubes"></i>
                </div>

                <div>
                    <h1>Control de Stock</h1>

                    <p>
                        Monitorea el stock actual y el estado de tus productos
                    </p>
                </div>

            </div>

            <div class="breadcrumb">

                <span>Inicio</span>
                <span>›</span>
                <span>Inventario</span>
                <span>›</span>
                <strong>Control de Stock</strong>

            </div>

        </div>


        <!-- ===================================================== -->
        <!-- FILTROS -->
        <!-- ===================================================== -->

        <div class="filters-card">

            <div class="filter-group">

                <label>Almacén</label>

                <select
                    v-model="filtros.idAlmacen"
                    class="form-control"
                >

                    <option value="1">
                        PRINCIPAL
                    </option>

                </select>

            </div>


            <div class="filter-group">
                <label>Categoría</label>

                <select
                    v-model="filtros.categoria"
                    class="form-control"
                    @change="buscar"
                >
                    <option value="">
                        Todas las categorías
                    </option>

                    <option
                        v-for="categoria in categorias"
                        :key="categoria.IdProductoCategoria"
                        :value="categoria.IdProductoCategoria"
                    >
                        {{ categoria.ProductoCategoria }}
                    </option>
                </select>
            </div>


            <div class="filter-group search-group">

                <label>Buscar producto</label>

                <div class="search-input">

                    <i class="fa fa-search"></i>

                    <input
                        v-model="filtros.q"
                        type="text"
                        class="form-control"
                        placeholder="Código, nombre o marca..."
                        @keyup.enter="buscar"
                    >

                </div>

            </div>


            <div class="filter-group">

                <label>Estado</label>

                <select
                    v-model="filtros.estado"
                    class="form-control"
                >

                    <option value="TODOS">
                        Todos
                    </option>

                    <option value="SIN_STOCK">
                        Sin stock
                    </option>

                    <option value="BAJO_MINIMO">
                        Bajo mínimo
                    </option>

                    <option value="OK">
                        Stock OK
                    </option>

                </select>

            </div>


            <div class="filter-buttons">

                <button
                    class="btn btn-primary"
                    @click="buscar"
                    :disabled="cargando"
                >

                    <i class="fa fa-search"></i>

                    Buscar

                </button>


                <button
                    class="btn btn-light"
                    @click="limpiar"
                    :disabled="cargando"
                >

                    <i class="fa fa-filter"></i>

                    Limpiar

                </button>

            </div>

        </div>


        <!-- ===================================================== -->
        <!-- TARJETAS -->
        <!-- ===================================================== -->

        <div class="summary-grid">


            <!-- TOTAL -->

            <div
                class="summary-card total-card"
                @click="seleccionarEstado('TODOS')"
            >

                <div class="summary-icon">
                    <i class="fa fa-cubes"></i>
                </div>

                <div class="summary-content">

                    <div class="summary-title">
                        Total productos
                    </div>

                    <div class="summary-number">
                        {{ resumen.total }}
                    </div>

                    <div class="summary-description">
                        Registrados
                    </div>

                </div>

                <div class="summary-arrow">
                    ›
                </div>

            </div>


            <!-- SIN STOCK -->

            <div
                class="summary-card danger-card"
                @click="seleccionarEstado('SIN_STOCK')"
            >

                <div class="summary-icon">
                    <i class="fa fa-times-circle"></i>
                </div>

                <div class="summary-content">

                    <div class="summary-title">
                        Sin stock
                    </div>

                    <div class="summary-number">
                        {{ resumen.sinStock }}
                    </div>

                    <div class="summary-description">
                        Productos sin existencia
                    </div>

                </div>

                <div class="summary-arrow">
                    ›
                </div>

            </div>


            <!-- BAJO MINIMO -->

            <div
                class="summary-card warning-card"
                @click="seleccionarEstado('BAJO_MINIMO')"
            >

                <div class="summary-icon">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>

                <div class="summary-content">

                    <div class="summary-title">
                        Bajo mínimo
                    </div>

                    <div class="summary-number">
                        {{ resumen.bajoMinimo }}
                    </div>

                    <div class="summary-description">
                        Requieren atención
                    </div>

                </div>

                <div class="summary-arrow">
                    ›
                </div>

            </div>


            <!-- OK -->

            <div
                class="summary-card success-card"
                @click="seleccionarEstado('OK')"
            >

                <div class="summary-icon">
                    <i class="fa fa-check-circle"></i>
                </div>

                <div class="summary-content">

                    <div class="summary-title">
                        Stock OK
                    </div>

                    <div class="summary-number">
                        {{ resumen.ok }}
                    </div>

                    <div class="summary-description">
                        Stock normal
                    </div>

                </div>

                <div class="summary-arrow">
                    ›
                </div>

            </div>

        </div>


        <!-- ===================================================== -->
        <!-- TABLA -->
        <!-- ===================================================== -->

        <div class="table-card">

            <div class="table-header">

                <div>

                    <div class="table-title">

                        <i class="fa fa-list"></i>

                        Listado de productos

                    </div>

                    <div class="table-subtitle">

                        {{ paginacion.total }} productos encontrados

                    </div>

                </div>


                <div class="table-actions">

                    <button
                        class="btn btn-outline-success"
                        @click="exportar"
                    >

                        <i class="fa fa-file-excel-o"></i>

                        Exportar Excel

                    </button>


                    <select
                        v-model.number="filtros.limit"
                        class="form-control limit-select"
                        @change="buscar"
                    >

                        <option :value="10">
                            10
                        </option>

                        <option :value="25">
                            25
                        </option>

                        <option :value="50">
                            50
                        </option>

                        <option :value="100">
                            100
                        </option>

                    </select>

                </div>

            </div>


            <!-- LOADING -->

            <div
                v-if="cargando"
                class="loading"
            >

                <i class="fa fa-spinner fa-spin"></i>

                Cargando stock...

            </div>


            <!-- ERROR -->

            <div
                v-else-if="error"
                class="error-message"
            >

                <i class="fa fa-exclamation-circle"></i>

                {{ error }}

                <button
                    class="btn btn-sm btn-primary"
                    @click="buscar"
                >
                    Reintentar
                </button>

            </div>


            <!-- TABLA -->

            <div
                v-else
                class="table-responsive"
            >

                <table class="stock-table">

                    <thead>

                        <tr>

                            <th>Código</th>

                            <th>Producto</th>

                            <th>Categoría</th>

                            <th>Marca</th>

                            <th>Medición</th>

                            <th class="text-right">
                                Stock actual
                            </th>

                            <th class="text-right">
                                Stock mínimo
                            </th>

                            <th class="text-center">
                                Estado
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <tr
                            v-for="producto in productos"
                            :key="producto.IdProducto"
                        >

                            <td>

                                {{ producto.CodigoBarra || '-' }}

                            </td>


                            <td>

                                <div class="product-name">

                                    <div class="product-avatar">

                                        {{ obtenerInicial(producto.Producto) }}

                                    </div>

                                    <div>

                                        <strong>
                                            {{ producto.Producto }}
                                        </strong>

                                        <small>
                                            ID: {{ producto.IdProducto }}
                                        </small>

                                    </div>

                                </div>

                            </td>


                            <td>
                                {{ producto.ProductoCategoria }}
                            </td>


                            <td>
                                {{ producto.ProductoMarca || 'S/M' }}
                            </td>


                            <td>
                                {{ producto.ProductoMedicion }}
                            </td>


                            <td
                                class="text-right stock-value"
                                :class="claseStock(producto)"
                            >

                                {{ formatearStock(producto.stock) }}

                            </td>


                            <td class="text-right">

                                {{ formatearStock(producto.StockMinimo) }}

                            </td>


                            <td class="text-center">

                                <span
                                    class="status-badge"
                                    :class="claseEstado(producto.estado)"
                                >

                                    {{ textoEstado(producto.estado) }}

                                </span>

                            </td>

                        </tr>


                        <tr
                            v-if="productos.length === 0"
                        >

                            <td
                                colspan="8"
                                class="empty"
                            >

                                <i class="fa fa-inbox"></i>

                                <div>
                                    No se encontraron productos
                                </div>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>


            <!-- ================================================= -->
            <!-- PAGINACION -->
            <!-- ================================================= -->

            <div
                v-if="!cargando && paginacion.total > 0"
                class="pagination-container"
            >

                <div class="pagination-info">

                    Mostrando

                    {{ inicioPagina }}

                    a

                    {{ finPagina }}

                    de

                    {{ paginacion.total }}

                    productos

                </div>


                <div class="pagination">

                    <button
                        class="page-button"
                        :disabled="paginacion.page <= 1"
                        @click="cambiarPagina(paginacion.page - 1)"
                    >

                        ‹

                    </button>


                    <button
                        v-for="pagina in paginas"
                        :key="pagina"
                        class="page-button"
                        :class="{ active: pagina === paginacion.page }"
                        @click="cambiarPagina(pagina)"
                    >

                        {{ pagina }}

                    </button>


                    <button
                        class="page-button"
                        :disabled="paginacion.page >= paginacion.totalPaginas"
                        @click="cambiarPagina(paginacion.page + 1)"
                    >

                        ›

                    </button>

                </div>

            </div>

        </div>


    </div>

</template>


<script>

import axios from 'axios'

export default {

    name: 'ControlStock',

    data () {

        return {

            API: 'http://localhost/api/index.php/',

            cargando: false,

            error: null,

            productos: [],


            /*
             * ============================================
             * DATOS DEL RESUMEN
             * ============================================
             */

            resumen: {

                total: 0,

                sinStock: 0,

                bajoMinimo: 0,

                ok: 0

            },


            /*
             * ============================================
             * FILTROS
             * ============================================
             */

            filtros: {

                idAlmacen: 1,

                categoria: '',

                q: '',

                estado: 'TODOS',

                page: 1,

                limit: 10

            },


            /*
             * ============================================
             * PAGINACION
             * ============================================
             */

            paginacion: {

                page: 1,

                limit: 10,

                total: 0,

                totalPaginas: 0

            },


            /*
             * ============================================
             * CATEGORIAS TEMPORALES
             * ============================================
             */

            categorias: []

        }

    },


    computed: {

        inicioPagina () {

            if (!this.paginacion.total) {

                return 0

            }

            return (
                (this.paginacion.page - 1) *
                this.paginacion.limit
            ) + 1

        },

        finPagina () {

            return Math.min(
                this.paginacion.page *
                this.paginacion.limit,
                this.paginacion.total

            )

        },

        paginas () {
            const total = this.paginacion.totalPaginas
            const actual = this.paginacion.page
            if (total <= 7) {

                return Array.from(
                    { length: total },
                    (_, i) => i + 1
                )

            }

            let inicio = Math.max(
                1,
                actual - 3
            )

            let fin = Math.min(
                total,
                inicio + 6
            )

            if (fin - inicio < 6) {

                inicio = Math.max(
                    1,
                    fin - 6
                )

            }

            const paginas = []

            for (
                let i = inicio;
                i <= fin;
                i++
            ) {

                paginas.push(i)

            }

            return paginas

        }

    },

    mounted () {
        this.buscar(),
        this.obtenerCategorias()
    },

    methods: {

        buscar () {
            this.filtros.page = 1
            this.cargarStock()
        },

        obtenerCategorias () {
            axios.get(this.API + 'categorias', {
                params: {
                    q: '',
                    limit: 1000
                }
            })
            .then(response => {

                this.categorias = (response.data || [])
                    .filter(categoria => Number(categoria.Anulado || 0) === 0)
                    .sort((a, b) =>
                        String(a.ProductoCategoria)
                            .localeCompare(String(b.ProductoCategoria))
                    )

            })
            .catch(error => {

                console.error('Error al cargar categorías:', error)

                this.categorias = []

            })
        },
        cargarStock () {

            this.cargando = true
            this.error = null

            const params = {
                idAlmacen: this.filtros.idAlmacen,
                idCategoria: this.filtros.categoria,
                q: this.filtros.q,
                estado: this.filtros.estado,
                page: this.filtros.page,
                limit: this.filtros.limit
            }

            axios.get(
                this.API + 'productos/controlstock',
                {
                    params: params
                }
            )

            .then(response => {

                const data = response.data
                this.resumen = data.resumen || {

                    total: 0,

                    sinStock: 0,

                    bajoMinimo: 0,

                    ok: 0

                }

                this.productos = data.items || []

                this.paginacion = data.paginacion || {

                    page: 1,

                    limit: this.filtros.limit,

                    total: 0,

                    totalPaginas: 0

                }

            })

            .catch(error => {

                console.error(
                    'Error cargando control de stock:',
                    error
                )

                this.error =
                    'No se pudo cargar el stock. Verifique la conexión con el servidor.'

            })

            .finally(() => {

                this.cargando = false

            })

        },

        cambiarPagina (pagina) {

            if (pagina < 1) {

                return

            }

            if (
                pagina >
                this.paginacion.totalPaginas
            ) {

                return

            }

            this.filtros.page = pagina

            this.cargarStock()

        },


        /*
         * ==================================================
         * LIMPIAR
         * ==================================================
         */

        limpiar () {

            this.filtros.q = ''

            this.filtros.categoria = ''

            this.filtros.estado = 'TODOS'

            this.filtros.page = 1

            this.cargarStock()

        },


        /*
         * ==================================================
         * TARJETAS
         * ==================================================
         */

        seleccionarEstado (estado) {

            this.filtros.estado = estado

            this.filtros.page = 1

            this.cargarStock()

        },


        /*
         * ==================================================
         * FORMATO STOCK
         * ==================================================
         */

        formatearStock (valor) {

            const numero = Number(valor || 0)

            if (
                Number.isInteger(numero)
            ) {

                return numero

            }

            return numero.toFixed(2)

        },


        /*
         * ==================================================
         * INICIAL
         * ==================================================
         */

        obtenerInicial (producto) {

            const nombre = String(producto || '').trim()

            if (!nombre) {

                return '?'

            }

            return nombre
                .charAt(0)
                .toUpperCase()

        },


        /*
         * ==================================================
         * CLASE STOCK
         * ==================================================
         */

        claseStock (producto) {

            if (
                producto.estado === 'SIN_STOCK'
            ) {

                return 'stock-danger'

            }

            if (
                producto.estado === 'BAJO_MINIMO'
            ) {

                return 'stock-warning'

            }

            return 'stock-ok'

        },


        /*
         * ==================================================
         * CLASE ESTADO
         * ==================================================
         */

        claseEstado (estado) {

            if (
                estado === 'SIN_STOCK'
            ) {

                return 'status-danger'

            }

            if (
                estado === 'BAJO_MINIMO'
            ) {

                return 'status-warning'

            }

            return 'status-success'

        },


        /*
         * ==================================================
         * TEXTO ESTADO
         * ==================================================
         */

        textoEstado (estado) {

            if (
                estado === 'SIN_STOCK'
            ) {

                return 'SIN STOCK'

            }

            if (
                estado === 'BAJO_MINIMO'
            ) {

                return 'BAJO MÍNIMO'

            }

            return 'OK'

        },

        exportar () {

            const params = new URLSearchParams({
                idAlmacen: this.filtros.idAlmacen,
                idCategoria: this.filtros.categoria || '',
                q: this.filtros.q || '',
                estado: this.filtros.estado
            })

            window.open(
                this.API + 'productos/controlstock/excel?' + params.toString(),
                '_blank'
            )

        }

    }

}

</script>


<style scoped>

.control-stock {

    padding: 30px 38px;

    background: #f6f8fb;

    min-height: 100vh;

    color: #142b45;

}


/* ========================================================= */
/* HEADER */
/* ========================================================= */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.header-title {

    display: flex;

    align-items: center;

}


.header-icon {

    width: 58px;

    height: 58px;

    border-radius: 14px;

    background: #e9f2ff;

    color: #1674d1;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 25px;

    margin-right: 15px;

}


.header-title h1 {

    margin: 0;

    font-size: 28px;

    font-weight: 700;

}


.header-title p {

    margin: 5px 0 0;

    color: #718096;

}


.breadcrumb {

    display: flex;

    gap: 9px;

    color: #718096;

    font-size: 13px;

}


/* ========================================================= */
/* FILTROS */
/* ========================================================= */

.filters-card {

    background: #fff;

    border: 1px solid #e1e7ef;

    border-radius: 12px;

    padding: 20px;

    display: grid;

    grid-template-columns:
        1fr
        1fr
        2fr
        1fr
        auto;

    gap: 14px;

    align-items: end;

    margin-bottom: 20px;

}


.filter-group label {

    display: block;

    font-size: 13px;

    font-weight: 600;

    margin-bottom: 7px;

}


.form-control {

    height: 38px;

    border: 1px solid #d5dde8;

    border-radius: 6px;

    padding: 0 12px;

    width: 100%;

    background: #fff;

    color: #26384d;

}


.search-input {

    position: relative;

}


.search-input i {

    position: absolute;

    left: 12px;

    top: 12px;

    color: #8a98a9;

    z-index: 2;

}


.search-input input {

    padding-left: 35px;

}


.filter-buttons {

    display: flex;

    gap: 8px;

}


.btn {

    height: 38px;

    padding: 0 16px;

    border-radius: 6px;

    border: none;

    cursor: pointer;

    font-weight: 600;

}


.btn-primary {

    background: #1674d1;

    color: #fff;

}


.btn-light {

    background: #fff;

    border: 1px solid #d5dde8;

    color: #34495e;

}


/* ========================================================= */
/* SUMMARY */
/* ========================================================= */

.summary-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 14px;

    margin-bottom: 20px;

}


.summary-card {

    background: #fff;

    border: 1px solid #e1e7ef;

    border-radius: 12px;

    min-height: 110px;

    padding: 17px;

    display: flex;

    align-items: center;

    position: relative;

    cursor: pointer;

    transition: .2s;

}


.summary-card:hover {

    transform: translateY(-2px);

    box-shadow:
        0 5px 15px rgba(0,0,0,.07);

}


.summary-icon {

    width: 48px;

    height: 48px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 20px;

    margin-right: 13px;

}


.total-card .summary-icon {

    background: #e8f2ff;

    color: #1674d1;

}


.danger-card .summary-icon {

    background: #ffe7e7;

    color: #dc3545;

}


.warning-card .summary-icon {

    background: #fff1cc;

    color: #c58900;

}


.success-card .summary-icon {

    background: #e1f5e8;

    color: #23934a;

}


.summary-content {

    flex: 1;

}


.summary-title {

    font-size: 13px;

    font-weight: 700;

}


.summary-number {

    font-size: 26px;

    font-weight: 800;

    line-height: 32px;

}


.summary-description {

    color: #8793a1;

    font-size: 11px;

}


.summary-arrow {

    color: #8996a5;

    font-size: 25px;

}


/* ========================================================= */
/* TABLA */
/* ========================================================= */

.table-card {

    background: #fff;

    border: 1px solid #e1e7ef;

    border-radius: 12px;

    overflow: hidden;

}


.table-header {

    padding: 17px 18px;

    border-bottom: 1px solid #e5eaf0;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.table-title {

    font-size: 16px;

    font-weight: 700;

}


.table-title i {

    margin-right: 8px;

}


.table-subtitle {

    margin-top: 4px;

    color: #8995a3;

    font-size: 12px;

}


.table-actions {

    display: flex;

    gap: 8px;

}


.btn-outline-success {

    background: #fff;

    color: #168447;

    border: 1px solid #168447;

}


.limit-select {

    width: 75px;

}


.table-responsive {

    overflow-x: auto;

}


.stock-table {

    width: 100%;

    border-collapse: collapse;

}


.stock-table th {

    background: #f6f8fb;

    color: #4a596a;

    font-size: 12px;

    font-weight: 700;

    padding: 13px 10px;

    border-bottom: 1px solid #e1e7ef;

    text-align: left;

}


.stock-table td {

    padding: 12px 10px;

    border-bottom: 1px solid #edf0f4;

    font-size: 12px;

}


.stock-table tbody tr:hover {

    background: #fafcff;

}


.text-right {

    text-align: right !important;

}


.text-center {

    text-align: center !important;

}


/* ========================================================= */
/* PRODUCTO */
/* ========================================================= */

.product-name {

    display: flex;

    align-items: center;

    gap: 9px;

}


.product-avatar {

    width: 30px;

    height: 30px;

    background: #edf2f7;

    color: #2371b9;

    border-radius: 7px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-weight: 700;

}


.product-name strong {

    display: block;

    color: #19314c;

}


.product-name small {

    display: block;

    color: #9aa5b1;

    margin-top: 2px;

}


/* ========================================================= */
/* STOCK */
/* ========================================================= */

.stock-value {

    font-weight: 800;

}


.stock-ok {

    color: #168447;

}


.stock-warning {

    color: #d08a00;

}


.stock-danger {

    color: #dc3545;

}


/* ========================================================= */
/* ESTADOS */
/* ========================================================= */

.status-badge {

    display: inline-block;

    min-width: 90px;

    padding: 5px 9px;

    border-radius: 6px;

    font-size: 10px;

    font-weight: 800;

}


.status-success {

    background: #dff2e6;

    color: #23834a;

}


.status-warning {

    background: #fff0ca;

    color: #9b6c00;

}


.status-danger {

    background: #ffe0e0;

    color: #c72d2d;

}


/* ========================================================= */
/* PAGINACION */
/* ========================================================= */

.pagination-container {

    padding: 14px 18px;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.pagination-info {

    color: #7b8794;

    font-size: 12px;

}


.pagination {

    display: flex;

    gap: 4px;

}


.page-button {

    min-width: 32px;

    height: 32px;

    border: 1px solid #dce2e9;

    background: #fff;

    border-radius: 5px;

    cursor: pointer;

}


.page-button:hover {

    background: #f1f5f9;

}


.page-button.active {

    background: #1674d1;

    border-color: #1674d1;

    color: #fff;

}


.page-button:disabled {

    opacity: .45;

    cursor: not-allowed;

}


/* ========================================================= */
/* LOADING */
/* ========================================================= */

.loading {

    padding: 60px;

    text-align: center;

    color: #687789;

}


.loading i {

    margin-right: 8px;

}


/* ========================================================= */
/* ERROR */
/* ========================================================= */

.error-message {

    padding: 40px;

    text-align: center;

    color: #c0392b;

}


.error-message i {

    margin-right: 8px;

}


/* ========================================================= */
/* EMPTY */
/* ========================================================= */

.empty {

    text-align: center;

    padding: 60px !important;

    color: #8995a3;

}


.empty i {

    font-size: 35px;

    display: block;

    margin-bottom: 10px;

}


/* ========================================================= */
/* RESPONSIVE */
/* ========================================================= */

@media (max-width: 1100px) {

    .filters-card {

        grid-template-columns:
            repeat(2, 1fr);

    }

    .search-group {

        grid-column: span 2;

    }

    .filter-buttons {

        grid-column: span 2;

    }

    .summary-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (max-width: 700px) {

    .control-stock {

        padding: 15px;

    }

    .page-header {

        display: block;

    }

    .breadcrumb {

        margin-top: 15px;

    }

    .filters-card {

        grid-template-columns: 1fr;

    }

    .search-group {

        grid-column: auto;

    }

    .filter-buttons {

        grid-column: auto;

    }

    .summary-grid {

        grid-template-columns: 1fr;

    }

    .table-header {

        display: block;

    }

    .table-actions {

        margin-top: 12px;

    }

    .pagination-container {

        display: block;

    }

    .pagination {

        margin-top: 10px;

    }

}

</style>