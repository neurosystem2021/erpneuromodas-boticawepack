<template>
    <div class="contenedor-raiz" style="margin-top:5px; margin-bottom:5px;">
        <b-button block class="btnAction" style="background-color: #592E37;" @click="abrirModal">
            <i class="fas fa-search"></i> Buscar Modelo
        </b-button>

        <!-- MODAL PRINCIPAL -->
        <b-modal v-model="mostrarModal" id="modalBusquedaModelo" size="xl" hide-footer title="BÚSQUEDA POR MODELO" @hidden="resetear"
            :no-enforce-focus="true">
            <b-container fluid>
                <b-row>
                <b-col cols="12" class="my-1 d-flex align-items-center">
                    <autocomplete required="required" ref="inputCliente" :url="API + 'clientes'"
                        anchor="ClienteDniRuc" label="writer" placeholder="Buscar Cliente"
                        :onInput="(val) => newDniRuc = val"
                        :on-select="setCliente"
                        :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                        :classes="{ input: 'form-control', wrapper: 'flex-grow-1' }">
                    </autocomplete>
                    <clientes-form :newDniRuc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                </b-col>
                    <b-col cols="12" md="4" class="my-1">
                        <b-input-group>
                            <autocomplete :url="API + 'productos/modelos'" ref="inputModeloModal"
                                anchor="ProductoModelo" placeholder="Buscar Modelo" label="writer"
                                :customParams="{ limit: 10 }" :on-select="setModelo"
                                :classes="{ input: 'form-control' }">
                            </autocomplete>
                            <b-button variant="warning" :class="microActivado ? 'btn-parpadeante' : ''"
                                @click="startRecognition">
                                <i class="fas fa-microphone"></i>
                            </b-button>
                        </b-input-group>
                    </b-col>

                    <b-col cols="12" md="4" class="my-1">
                        <b-input-group>
                            <input v-model="codeBarModelo" type="text" class="form-control"
                                placeholder="Codigo de Barra Modelo" @keypress.enter="buscarPorCodeBarModelo" />
                            <b-button variant="primary" @click="buscarPorCodeBarModelo">
                                <i class="fas fa-search"></i>
                            </b-button>
                        </b-input-group>
                    </b-col>

                    <b-col cols="12" md="4" class="my-1">
                        <b-input-group>
                            <input v-model="loadCode" type="text" class="form-control" placeholder="Codigo de Barra"
                                @keypress.enter="buscarPorCodeBar" />
                            <b-button variant="success" @click="buscarPorCodeBar">
                                <i class="fas fa-barcode"></i>
                            </b-button>
                            <b-button variant="primary" @click="buscarPorCodeBar">
                                <i class="fas fa-search"></i>
                            </b-button>
                        </b-input-group>
                    </b-col>

                    <!-- CARRITO -->
                    <b-col cols="12" class="mt-3">
                        <div style="max-height:320px; overflow-y:auto;">
                            <b-table :items="carrito" :fields="fieldsCarrito" small striped hover>
                                <template slot="Imagen" slot-scope="data">
                                    <b-img style="border-radius:6px;width:40px;" fluid
                                        :src="data.item.Imagen == null || data.item.Imagen == '' ? 'static/no-photo.png' : '/resources/images/productos/' + data.item.Imagen"
                                        @error="setAltImg" />
                                </template>
                                <template slot="Producto" slot-scope="data">
                                    <strong>{{ data.item.Producto }}</strong>
                                    <span style="color:#888;font-size:0.8rem;"> - {{ data.item.CodigoBarra }}</span>
                                </template>
                                <template slot="cantidad" slot-scope="data">
                                    <input type="number" class="form-control" style="width:80px;"
                                        v-model="data.item.cantidad" min="1">
                                </template>
                                <template slot="precio" slot-scope="data">
                                    <input type="number" class="form-control" style="width:90px;"
                                        v-model="data.item.precio">
                                </template>
                                <template slot="descuento" slot-scope="data">
                                    <input type="number" class="form-control" style="width:80px;"
                                        v-model="data.item.descuento">
                                </template>
                                <template slot="subTotal" slot-scope="data">
                                    {{ ((data.item.cantidad || 0) * (data.item.precio || 0)).toFixed(2) }}
                                </template>
                                <template slot="Total" slot-scope="data">
                                    {{ ((data.item.cantidad || 0) * (data.item.precio || 0) - (data.item.descuento ||
                                        0)).toFixed(2) }}
                                </template>
                                <template slot="action" slot-scope="data">
                                    <b-button variant="danger" size="sm" @click="carrito.splice(data.index, 1)">
                                        <i class="fa fa-trash"></i>
                                    </b-button>
                                </template>
                            </b-table>
                        </div>
                    </b-col>

                    <!-- TOTALES -->
                    <b-col cols="12" md="6" class="mt-3">
                        <table class="table table-sm table-bordered">
                            <tr>
                                <td class="table-warning"><b>SubTotal</b></td>
                                <td>{{ subTotal }}</td>
                            </tr>
                            <tr>
                                <td class="table-danger"><b>Descuento</b></td>
                                <td>{{ descuento }}</td>
                            </tr>
                            <tr>
                                <td class="table-success"><b>TOTAL</b></td>
                                <td><b>{{ total }}</b></td>
                            </tr>
                        </table>
                    </b-col>
                    <b-col cols="12" md="6" class="mt-3 d-flex align-items-center justify-content-center">
                        <b-button variant="success" class="btnAction" style="min-width:220px;"
                            :disabled="!carrito.length" @click="agregarAlCarrito">
                            <i class="fas fa-cart-plus"></i> Agregar al Carrito
                        </b-button>
                    </b-col>
                </b-row>
            </b-container>
        </b-modal>

        <!-- SUB-MODAL FUERA DEL PRINCIPAL — igual que el módulo original que funciona -->
        <b-modal v-model="modalProductos" id="modalProductosModelo" size="xl" hide-footer :title="`MODELO: ${modelo.ProductoModelo || ''}`"
            :no-enforce-focus="true">

            <!-- DEBUG TEMPORAL -->
            <p>Total productos: {{ productosModelo.length }}</p>
            <b-table :items="productosModelo" :fields="fieldsModelo" small :per-page="perPage"
                :current-page="currentPage" id="tbl-modelo">
                <template slot="stock" slot-scope="data">
                    <b-badge :variant="data.item.stock > 0 ? 'success' : 'danger'" style="font-size:0.85rem;">
                        {{ data.item.stock }}
                    </b-badge>
                </template>
                <template slot="Imagen" slot-scope="data">
                    <b-img style="border-radius:6px;width:40px;" fluid
                        :src="data.item.Imagen == null || data.item.Imagen == '' ? 'static/no-photo.png' : '/resources/images/productos/' + data.item.Imagen"
                        @error="setAltImg" />
                </template>
                <template slot="Producto" slot-scope="data">
                    {{ data.item.Producto }} - {{ data.item.CodigoBarra }}
                </template>
                <template slot="action" slot-scope="data">
                    <b-button size="sm" variant="info" @click="agregarProducto(data.item)">
                        <i class="fa fa-cart-arrow-down"></i>
                    </b-button>
                </template>
            </b-table>
            <b-row>
                <b-col cols="10">
                    <b-pagination v-model="currentPage" :total-rows="productosModelo.length" :per-page="perPage"
                        aria-controls="tbl-modelo" />
                </b-col>
                <b-col cols="2">
                    <b-form-select v-model="perPage"
                        :options="[{ value: 10, text: '10' }, { value: 20, text: '20' }, { value: 50, text: '50' }]" />
                </b-col>
            </b-row>
        </b-modal>

    </div>
</template>

<script>
import config from "@/store/config.js";
import ClientesForm from "@/components/ClientesForm";

export default {
    props: {
        idAlmacen: { type: Number, default: 1 }
    },
    data() {
        return {
            API: config.API,
            modelo: {},
            productosModelo: [],
            carrito: [],
            modalProductos: false,
            microActivado: false,
            mostrarModal: false,
            loadCode: '',
            codeBarModelo: '',
            currentPage: 1,
            perPage: 10,
            cliente: {},
            newDniRuc: '',
            fieldsCarrito: [
                { key: 'stock', label: 'Stock' },
                { key: 'Producto' },
                { key: 'ProductoModelo', label: 'Modelo' },
                { key: 'ProductoMarca', label: 'Marca' },
                { key: 'ProductoTalla', label: 'Talla' },
                { key: 'Color' },
                { key: 'Genero' },
                { key: 'Botapie' },
                { key: 'Imagen' },
                { key: 'cantidad' },
                { key: 'precio' },
                { key: 'subTotal', label: 'SubTotal' },
                { key: 'descuento', label: 'Descuento' },
                { key: 'Total' },
                { key: 'action', label: '' }
            ],
            fieldsModelo: [
                { key: 'action', label: '' },
                { key: 'stock' },
                { key: 'Producto' },
                { key: 'ProductoTalla', label: 'Talla' },
                { key: 'ProductoMarca', label: 'Marca' },
                { key: 'Color' },
                { key: 'Genero' },
                { key: 'Botapie' },
                { key: 'Imagen' }
            ]
        };
    },
    computed: {
        subTotal() {
            return parseFloat(this.carrito.reduce((a, b) => a + (b.cantidad * b.precio), 0)).toFixed(2);
        },
        descuento() {
            return parseFloat(this.carrito.reduce((a, b) => a + parseFloat(b.descuento || 0), 0)).toFixed(2);
        },
        total() {
            return parseFloat(this.carrito.reduce((a, b) => a + (b.cantidad * b.precio - (b.descuento || 0)), 0)).toFixed(2);
        }
    },
    components: {
        ClientesForm   // ← AGREGAR ESTO
    },
    methods: {
        abrirModal() {
            this.mostrarModal = true;
            this.loadClienteVarios();
        },

        resetear() {
            if (this.modalProductos) return; // No resetear si sub-modal está abierto
            this.carrito = [];
            this.productosModelo = [];
            this.modelo = {};
            this.loadCode = '';
            this.codeBarModelo = '';
            this.cliente = {};
        },

        setAltImg(event) {
            event.target.src = 'static/no-photo.png';
        },
        onHideModalPrincipal(evt) {
            // Solo resetear si NO hay un sub-modal abierto
            if (this.modalProductos) {
                evt.preventDefault();
                return;
            }
            this.resetear();
        },

        loadClienteVarios() {
            this.axios.get(this.API + 'clientes/00000000').then(res => {
                if (res.data.IdCliente) {
                    this.setCliente(res.data);
                }
            });
        },
        setCliente(cliente) {
            this.cliente = cliente;
            const label = cliente.ClienteDniRuc
                ? cliente.ClienteDniRuc
                : `${cliente.DniRuc} ${cliente.Cliente}`.toUpperCase();
            this.$refs.inputCliente.setValue(label);
        },
        async setModelo(modelo) {
            const ProductoModelo = modelo.ProductoModelo;
            this.modelo = { ProductoModelo };
            this.currentPage = 1;
            this.productosModelo = [];

            try {
                const res = await this.axios.get(this.API + 'productos_modelo', {
                    params: { ProductoModelo: ProductoModelo, sortBy: 'stock' }
                });

                const data = res.data;

                if (!data || data.length === 0) {
                    this.$toasted.show('No se encontraron productos para este modelo');
                    return;
                }

                data.forEach((item, index) => {
                    this.$set(this.productosModelo, index, item);
                });

                // NO cerrar el principal, solo abrir el sub-modal
                this.modalProductos = true;

            } catch (e) {
                console.error('Error en setModelo:', e);
                this.$toasted.show('Error al buscar productos');
            }
        },

        agregarProducto(item) {
            if (!this.carrito.some(v => v.IdProducto == item.IdProducto)) {
                this.carrito.unshift({
                    ...item,
                    cantidad: 1,
                    precio: item.PrecioContado,
                    descuento: 0
                });
            }
            this.modalProductos = false;
            setTimeout(() => {
                this.mostrarModal = true;
            }, 300);
        },

        focusAutocomplete(ref) {   // ← AGREGAR ESTE MÉTODO
            this.$refs[ref].type = "";
            this.$refs[ref].handleDoubleClick();
        },
        
        async buscarPorCodeBar() {
            if (!this.loadCode) return;
            const res = await this.axios.get(`${this.API}productos/id/${this.loadCode}`, {
                params: { codigoBarra: this.loadCode }
            });
            if (res.data && res.data.IdProducto) {
                this.agregarProducto(res.data);
                this.$toasted.show('Producto añadido');
            } else {
                this.$toasted.show('No se encontró el producto');
            }
            this.loadCode = '';
        },

        async buscarPorCodeBarModelo() {
            if (!this.codeBarModelo) return;
            const res = await this.axios.get(`${this.API}productos/CodeBarModelo`, {
                params: { CodeBarModelo: this.codeBarModelo }
            });
            if (res.data && res.data.IdProducto) {
                this.agregarProducto(res.data);
                this.codeBarModelo = '';
                this.$toasted.show('Producto añadido');
            } else {
                this.$toasted.show('No se encontró el producto');
            }
        },

        agregarAlCarrito() {
            if (!this.carrito.length) return;
            this.$emit('onAgregarProductos', [...this.carrito]);
            this.mostrarModal = false;
            this.$toasted.success('Productos agregados al carrito');
        },

        startRecognition() {
            if (!('webkitSpeechRecognition' in window)) {
                alert('Tu navegador no soporta búsqueda por voz.');
                return;
            }
            this.microActivado = true;
            const recognition = new webkitSpeechRecognition();
            recognition.lang = 'es-ES';
            recognition.continuous = false;
            recognition.interimResults = false;
            recognition.onresult = event => {
                this.microActivado = false;
                const query = event.results[0][0].transcript;
                const input = this.$refs['inputModeloModal'].$el.querySelector('input');
                input.focus();
                setTimeout(() => {
                    input.value = query;
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }, 100);
            };
            recognition.onerror = () => { this.microActivado = false; };
            recognition.onend = () => { this.microActivado = false; };
            recognition.start();
        }
    }
};
</script>

<style scoped>
.contenedor-raiz {
    width: 100%;
}

.btnAction {
    margin: 2px 0;
    border-radius: 25px !important;
    box-shadow: 5px 5px 5px -1px rgba(0, 0, 0, 0.75);
    font-size: 1.2rem;
    font-weight: bold;
}

.btn-parpadeante {
    animation: parpadeo 1.5s infinite;
}

@keyframes parpadeo {
    0% {
        opacity: 1;
        box-shadow: 0 0 0 0 rgba(255, 71, 87, 0.7);
    }

    50% {
        opacity: 0.5;
        box-shadow: 0 0 20px 10px rgba(255, 71, 87, 0);
    }

    100% {
        opacity: 1;
        box-shadow: 0 0 0 0 rgba(255, 71, 87, 0);
    }
}
</style>
<style>
#modalBusquedaModelo .modal-dialog,
#modalProductosModelo .modal-dialog {
  max-width: 95vw !important;
  width: 95vw !important;
}
#modalBusquedaModelo .modal-body,
#modalProductosModelo .modal-body {
  max-height: 80vh;
  overflow-y: auto;
}
</style>