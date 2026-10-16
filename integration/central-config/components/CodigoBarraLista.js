import CodigoBarra from './CodigoBarra.js?v=20261006-10';


export default {
    template: `
      <div id="barcode">
        <span v-for="(producto, key) in productos">
            <codigo-barra v-for="n in producto.Cantidad" :producto="producto" :key="n + producto.IdProducto"></codigo-barra>
        </span>
      </div>
    `,
    mounted() {
        if (this.idProducto) {
            this.obtenerProducto()
        } else if(this.hashMovimiento) {
            this.obtenerProductos()
        }else if(this.arregloPro){
            this.obtenerMultiplePro()
        }
    },
    data() {
      return {
          productos: []
      }
    },
    props: ['hashMovimiento', 'idProducto', 'cantidad', 'arregloPro'],
    methods: {
        obtenerProducto () {
            axios.get('/api/index.php/productos/id/' + this.idProducto+"?esId=1")
                .then((response) => {
                    var producto = response.data
                    producto.Producto = producto.Producto
                    producto.Cantidad = parseInt(this.cantidad)

                    this.productos.push(producto)
                })
                .catch(function (error) {
                    console.log(error)
                })
        },
        obtenerProductos () {
            axios.get('/api/index.php/movimiento/productos', {
                params: {
                    hash: this.hashMovimiento
                }
            }).then((response) => {
                var productos = response.data.map(function(x) {
                    //x.Producto = x.Producto.split('-').slice(0, -1).join()
                    return x
                })
                
                this.productos = productos
              })
              .catch(function (error) {
                console.log(error)
              })
        },
        obtenerMultiplePro(){
            let arregloGet = this.arregloPro
            let arreglo = JSON.parse(arregloGet)
            if(arreglo.length>0){
                arreglo.map(arr=>{
                    axios.get('/api/index.php/productos/id/' + arr[0]+"?esId=1")
                    .then((response) => {
                        var producto = response.data
                        producto.Producto = producto.Producto
                        producto.Cantidad = parseInt(arr[1])
        
                        this.productos.push(producto)
                    })
                    .catch(function (error) {
                        console.log(error)
                    })
                })
            }
        }
    },
    components: {
        CodigoBarra
    }
  }
