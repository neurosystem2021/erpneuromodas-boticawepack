<template>
  <b-container fluid class="py-4">
    <h3 class="mb-4"><i class="fas fa-gift mr-2"></i>Promociones de Productos</h3>

    <b-row>
      <b-col md="6">
        <b-form-group>
          <label>Buscar Producto:</label>
          <autocomplete
            ref="inputProducto"
            :url="API + 'productos'"
            anchor="Producto"
            label="writer"
            placeholder="Buscar producto por nombre o código..."
            :on-select="setProducto"
            :onFocus="focusAutocomplete.bind(null, 'inputProducto')"
            :classes="{ input: 'form-control' }">
          </autocomplete>
        </b-form-group>
      </b-col>
    </b-row>

    <div v-if="productoSeleccionado.IdProducto">
      <b-card class="mb-4 card-producto">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <div class="text-muted small">Producto seleccionado</div>
            <h5 class="mb-0">{{ productoSeleccionado.Producto }}</h5>
          </div>
          <b-button variant="outline-secondary" size="sm" @click="limpiarSeleccion">
            Cambiar producto
          </b-button>
        </div>
      </b-card>

      <b-row>
        <b-col md="7">
          <b-card>
            <h5 class="mb-3">{{ promo.IdPromocion ? 'Editar Promoción' : 'Nueva Promoción' }}</h5>

            <b-form @submit.prevent="guardar">
              <b-row>
                <b-col md="4">
                  <b-form-group label="Presentacion:">
                    <b-form-select
                      v-model="promo.Unidad"
                      :options="presentacionesProducto"
                      @change="actualizarCantidadCompra">
                    </b-form-select>
                  </b-form-group>
                </b-col>
                <b-col md="4">
                  <b-form-group label="Cantidad Compra:">
                    <b-form-input type="number" min="1" v-model.number="promo.CantidadCompra" required></b-form-input>
                    <b-form-text>Se autocompleta según la presentación elegida, pero puede ajustarla.</b-form-text>
                  </b-form-group>
                </b-col>

                <b-col md="6">
                  <b-form-group label="Vigencia Desde:">
                    <b-form-input type="date" v-model="promo.FechaInicio" required></b-form-input>
                  </b-form-group>
                </b-col>
                <b-col md="6">
                  <b-form-group label="Vigencia Hasta:">
                    <b-form-input type="date" v-model="promo.FechaFin" required></b-form-input>
                  </b-form-group>
                </b-col>
              </b-row>

              <fieldset class="promo-fieldset">
                <legend>Promoción (lo que se entrega gratis)</legend>
                <b-row>
                  <b-col md="12">
                    <b-form-group label="Producto Regalo:">
                      <autocomplete
                        ref="inputProductoRegalo"
                        :url="API + 'productos'"
                        anchor="Producto"
                        label="writer"
                        placeholder="Buscar producto de regalo... (déjelo igual al de arriba si es el mismo)"
                        :on-select="setProductoRegalo"
                        :onFocus="focusAutocomplete.bind(null, 'inputProductoRegalo')"
                        :classes="{ input: 'form-control' }">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                  <b-col md="6">
                    <b-form-group label="Cantidad Regalo:">
                      <b-form-input type="number" min="1" v-model.number="promo.CantidadRegalo" required></b-form-input>
                    </b-form-group>
                  </b-col>
                </b-row>
              </fieldset>

              <b-form-checkbox v-model="promo.Habilitado" :value="1" :unchecked-value="0" class="mt-2 mb-3">
                Habilitado
              </b-form-checkbox>

              <div class="text-right">
                <b-button variant="secondary" @click="nuevaPromo" class="mr-2" v-if="promo.IdPromocion">
                  Cancelar edición
                </b-button>
                <b-button variant="primary" type="submit">
                  {{ promo.IdPromocion ? 'Actualizar' : 'Guardar' }}
                </b-button>
              </div>
            </b-form>
          </b-card>
        </b-col>

        <b-col md="5">
          <b-card>
            <h5 class="mb-3">Promociones de este producto</h5>
            <b-table
              small
              striped
              hover
              :items="historial"
              :fields="camposHistorial"
              show-empty
              empty-text="Este producto no tiene promociones registradas.">

              <template slot="Habilitado" slot-scope="data">
                <b-badge :variant="data.item.Habilitado ? 'success' : 'secondary'">
                  {{ data.item.Habilitado ? 'Activa' : 'Inactiva' }}
                </b-badge>
              </template>
              <template slot="acciones" slot-scope="data">
                <b-button size="sm" variant="outline-primary" @click="editar(data.item)">
                  <i class="fas fa-pen"></i>
                </b-button>
                <b-button size="sm" variant="outline-danger" @click="eliminar(data.item)" v-if="data.item.Habilitado">
                  <i class="fas fa-ban"></i>
                </b-button>
              </template>
            </b-table>
          </b-card>
        </b-col>
      </b-row>
    </div>

    <b-card v-else class="text-center text-muted py-5">
      <i class="fas fa-search fa-2x mb-2"></i>
      <div>Busque un producto arriba para ver o crear sus promociones.</div>
    </b-card>
  </b-container>
</template>

<script>
import config from '@/store/config.js'

export default {
  data () {
    return {
      API: config.API,
      productoSeleccionado: {},
      historial: [],
      presentacionesProducto: [{ value: null, text: 'Seleccione un producto primero' }],
      camposHistorial: [
        { key: 'CantidadCompra', label: 'Compra' },
        { key: 'ProductoRegaloNombre', label: 'Regalo' },
        { key: 'CantidadRegalo', label: 'Cant.' },
        { key: 'FechaInicio', label: 'Desde' },
        { key: 'FechaFin', label: 'Hasta' },
        { key: 'Habilitado', label: 'Estado' },
        { key: 'acciones', label: '' }
      ],
      promo: this.promoVacia()
    }
  },
  methods: {
    promoVacia () {
      return {
        IdPromocion: null,
        IdProducto: null,
        TipoPrecio: 'Precio3',
        Unidad: 'UND',
        CantidadCompra: 12,
        FechaInicio: this.$moment().format('YYYY-MM-DD'),
        FechaFin: this.$moment().add(1, 'month').format('YYYY-MM-DD'),
        IdProductoRegalo: null,
        UnidadRegalo: 'UND',
        CantidadRegalo: 1,
        Habilitado: 1
      }
    },
    focusAutocomplete (ref) {
      this.$refs[ref].handleDoubleClick()
    },
    // Convierte la columna JSON "Presentaciones" de Gen_Producto en opciones de <select>
    // Ej: [{"nombre":"X 12 UNIDADES","cantidad":"12",...}] -> [{value:'X 12 UNIDADES', text:'X 12 UNIDADES (x12)'}]
    parsePresentaciones (producto) {
      let lista = [{
        value: producto.ProductoMedicion || 'UNIDAD',
        text: (producto.ProductoMedicion || 'UNIDAD') + ' (unidad base)',
        cantidad: 1
      }]

      try {
        const raw = producto.Presentaciones
        const arr = raw ? (typeof raw === 'string' ? JSON.parse(raw) : raw) : []
        arr.forEach(p => {
          if (p && p.nombre) {
            lista.push({
              value: p.nombre,
              text: `${p.nombre} (x${p.cantidad})`,
              cantidad: parseFloat(p.cantidad) || 1
            })
          }
        })
      } catch (e) {
        console.log('Presentaciones inválidas para ' + producto.Producto, e)
      }

      return lista
    },
    // Al cambiar la presentación, la Cantidad Compra se ajusta a la cantidad real de esa presentación
    // Ej: si eligen "CAJA (x100)", Cantidad Compra pasa a ser 100 automáticamente
    // IMPORTANTE: @change de b-form-select dispara este handler ANTES de que v-model
    // termine de actualizar promo.Unidad, así que usamos el valor que entrega el propio
    // evento (nuevaUnidad) en vez de confiar en this.promo.Unidad en este punto.
    actualizarCantidadCompra (nuevaUnidad) {
      const unidad = nuevaUnidad !== undefined ? nuevaUnidad : this.promo.Unidad
      const opt = this.presentacionesProducto.find(p => p.value === unidad)
      if (opt) {
        this.promo.CantidadCompra = opt.cantidad
      }
    },
    setProducto (obj) {
      this.productoSeleccionado = obj
      this.nuevaPromo()
      this.promo.IdProducto = obj.IdProducto
      this.presentacionesProducto = this.parsePresentaciones(obj)
      this.promo.Unidad = this.presentacionesProducto[0].value
      this.promo.CantidadCompra = this.presentacionesProducto[0].cantidad
      this.cargarHistorial()
    },
    setProductoRegalo (obj) {
      this.promo.IdProductoRegalo = obj.IdProducto
      this.$refs.inputProductoRegalo.setValue(obj.Producto)
    },
    limpiarSeleccion () {
      this.productoSeleccionado = {}
      this.historial = []
      this.presentacionesProducto = [{ value: null, text: 'Seleccione un producto primero' }]
      this.$refs.inputProducto.setValue('')
      this.nuevaPromo()
    },
    nuevaPromo () {
      this.promo = this.promoVacia()
      this.promo.IdProducto = this.productoSeleccionado.IdProducto || null
      if (this.$refs.inputProductoRegalo) {
        this.$refs.inputProductoRegalo.setValue('')
      }
    },
    cargarHistorial () {
      this.axios.get(this.API + 'productos/promocion/lista/' + this.productoSeleccionado.IdProducto)
        .then(res => {
          this.historial = res.data
        })
    },
    editar (item) {
      this.promo = { ...item }

      // Reutilizamos las presentaciones del producto ya cargado; si la guardada
      // ya no coincide con ninguna presentación actual, la agregamos igual para no perder el dato
      this.presentacionesProducto = this.parsePresentaciones(this.productoSeleccionado)
      if (!this.presentacionesProducto.find(p => p.value === item.Unidad)) {
        this.presentacionesProducto.push({ value: item.Unidad, text: item.Unidad })
      }

      this.$nextTick(() => {
        this.$refs.inputProductoRegalo.setValue(item.ProductoRegaloNombre)
      })
    },
    guardar () {
      if (!this.promo.IdProductoRegalo) {
        this.$toasted.show('Debe seleccionar el producto de regalo')
        return
      }

      this.axios.post(this.API + 'productos/promocion', this.promo)
        .then(() => {
          this.$toasted.success('Promoción guardada con éxito')
          this.nuevaPromo()
          this.cargarHistorial()
        })
        .catch((error) => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
    },
    eliminar (item) {
      let r = confirm('¿Desactivar esta promoción?')
      if (r) {
        this.axios.post(this.API + 'productos/promocion/eliminar', { IdPromocion: item.IdPromocion })
          .then(() => {
            this.$toasted.show('Promoción desactivada')
            this.cargarHistorial()
          })
      }
    }
  }
}
</script>

<style scoped>
.card-producto {
  border-left: 4px solid #0d9c93;
}
.promo-fieldset {
  border: 1px solid #dee2e6;
  border-radius: 6px;
  padding: 12px 16px;
  margin-bottom: 16px;
}
.promo-fieldset legend {
  width: auto;
  font-size: 13px;
  font-weight: 600;
  padding: 0 8px;
  color: #6c757d;
}
</style>