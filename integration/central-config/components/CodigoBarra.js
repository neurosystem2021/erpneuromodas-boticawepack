export default {
    template: `
      <div class="barcode-box barcode-label-modern">
        <header class="barcode-brand-row">
            <div class="barcode-brand-logo">
                <img :src="config.logo" alt="Logo de la empresa" />
            </div>
        </header>

        <div class="barcode-product-name" :class="{ 'barcode-product-name--long': nombreProducto.length > 48 }">
            {{ nombreProducto }}
        </div>

        <div class="barcode-symbol" v-if="codigoProducto">
            <barcode
                :value="codigoProducto"
                tag="svg"
                :options="barcodeOptions">
            </barcode>
        </div>
        <div class="barcode-symbol barcode-symbol--empty" v-else>SIN CODIGO</div>

        <footer class="barcode-label-footer">
            <span class="barcode-footer-side">
                {{ config.mostrarPrecio ? 'S/ ' + precioProducto : '' }}
            </span>
            <strong class="barcode-code-text">{{ codigoProducto || 'SIN CODIGO' }}</strong>
            <span class="barcode-footer-side barcode-footer-side--right">
                {{ config.mostrarTalla ? 'T. ' + tallaProducto : '' }}
            </span>
        </footer>
      </div>
    `,
    data() {
      return {
        config: window.ETIQUETA_CONFIG || {
          logo: '/resources/images/eden-company-monochrome.png',
          empresa: 'NEUROSYSTEM',
          mostrarPrecio: false,
          mostrarTalla: false
        }
      }
    },
    computed: {
      nombreEmpresa() {
        return String(this.config.empresa || 'NEUROSYSTEM')
          .replace(/\s+/g, ' ')
          .trim()
          .toUpperCase();
      },
      nombreProducto() {
        return String(this.producto.Producto || 'PRODUCTO SIN NOMBRE')
          .replace(/\s+/g, ' ')
          .trim()
          .toUpperCase();
      },
      codigoProducto() {
        return String(this.producto.CodigoBarra || this.producto.Codigo || '')
          .trim()
          .toUpperCase();
      },
      barcodeOptions() {
        const longitud = this.codigoProducto.length;
        const anchoModulo = longitud <= 8 ? 3 : (longitud <= 14 ? 2 : 1.45);
        const zonaSilenciosa = Math.ceil(anchoModulo * 10);
        return {
          format: 'CODE128',
          width: anchoModulo,
          height: 110,
          displayValue: false,
          margin: 0,
          marginTop: 2,
          marginBottom: 2,
          marginLeft: zonaSilenciosa,
          marginRight: zonaSilenciosa,
          lineColor: '#000000',
          background: '#ffffff'
        };
      },
      precioProducto() {
        return Number(this.producto.PrecioContado || 0).toFixed(2);
      },
      tallaProducto() {
        return String(
          this.producto.ProductoTalla ||
          this.producto.Talla ||
          this.producto.talla ||
          '-'
        ).trim();
      }
    },
    props: ['producto']
}
