<template>
  <b-container fluid>
    <center>
        <h1>Reporte de Orden de Producción</h1>
    </center>
    <b-card no-body>
      <b-tabs v-model="tabIndex" card>
        <b-tab title="Reporte Por Categoria">
          <b-table :fields="headerRepCliente" :items="lstRepCliente" :per-page="perPage"
          :current-page="currentPage" small>
            <template slot="detalle" slot-scope="data">
                <table class="table small">
                  <tr>
                    <th width="40%">CLIENTE</th>
                    <th width="20%">PRODUCTO</th>
                    <th width="10%">DESCRIPCIÓN</th>
                    <th width="10%">FECHA ENTREGA</th>
                    <th width="15%">REPARTIDOR</th>
                    <th width="5%" style="text-align: center;">CANT.</th>
                  </tr>
                  <tr v-for="(det,index) in data.item.detalle" :key="index" >
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''">{{ det.Cliente }}</td>
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''">{{ det.Producto }}</td>
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''">{{ det.Descripción }}</td>
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''">{{ det.FechaEntrega }} {{ det.HoraEntrega }}</td>
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''">{{ det.Repartidor }}</td>
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''" style="text-align: center;">{{ det.Cantidad }}</td>

                  </tr>
                </table>
            </template>
          </b-table>
          

            <b-pagination
            v-model="currentPage"
            :total-rows="lstRepCliente.length"
            :per-page="perPage"
           
            size="sm"
            >
            
          </b-pagination>
       


        </b-tab>



        <b-tab title="Reporte por Productos">
          <b-table :fields="headerRepPro" :items="lstRepProductos">

          </b-table>
           
        </b-tab>
        
      </b-tabs>
    </b-card>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      tabIndex: 0,
      perPage: 5,
      currentPage: 1,
      headerRepCliente: [
        { key: "ProductoCategoria", with: "10%" },
        // { key: "Repartidor" },
        // { key: "FechaEntrega" },
        // { key: "HoraEntrega" },
        { key: "detalle" }
      ],
      lstRepCliente: [],
      headerRepPro: [
        { key: "ProductoCategoria" },
        { key: "Producto" },
        { key: "Cantidad" }
      ],
      lstRepProductos: [],
      API: config.API
    };
  },
  mounted() {
    this.obtenerReportePersona();
    this.obtenerReporteProducto();
  },
  methods: {
    async obtenerReportePersona() {
      await this.axios(`${this.API}reporte/ordencompra_persona`).then(res => {
        if (res) {
          this.formatearDetalleRepCliente(res.data);
        }
      });
    },
    async obtenerReporteProducto() {
      await this.axios(`${this.API}reporte/ordencompra_productos`).then(res => {
        if (res) {
          this.lstRepProductos = res.data;
        }
      });
    },
    // formatearDetalleRepCliente(data) {
    //   data.forEach(categoria => {
    //     const productosMap = {};

    //     // Agrupar productos por IdProducto y calcular totales
    //     categoria.detalle.forEach(producto => {
    //       if (!productosMap[producto.IdProducto]) {
    //         productosMap[producto.IdProducto] = {
    //           Cantidad: 0,
    //           Precio: 0
    //         };
    //       }
    //       productosMap[producto.IdProducto].Cantidad += producto.Cantidad;
    //     });

    //     // Insertar los totales en la lista de detalles
    //     const detalleConTotales = [];
    //     categoria.detalle.forEach(producto => {
    //       detalleConTotales.push(producto);
    //       if (productosMap[producto.IdProducto]) {
    //         detalleConTotales.push({
    //           Cliente: "TOTAL",
    //           IdProducto: "",
    //           Producto: "",
    //           descripcion: "",
    //           FechaEntrega: "",
    //           HoraEntrega: "",
    //           Repartidor: "",
    //           IdProformaDet: null,
    //           IdProforma: null,
    //           DisabledTotal: 0,
    //           Descripcion: "",
    //           Cantidad: productosMap[producto.IdProducto].Cantidad,
    //           Precio: 0,
    //           Descuento: 0
    //         });
    //         delete productosMap[producto.IdProducto]; // Asegurar que solo agreguemos el total una vez
    //       }
    //     });
    //     categoria.detalle = detalleConTotales;
    //   });
    //   this.lstRepCliente = data;
    // }
    formatearDetalleRepCliente(data) {
      data.forEach(categoria => {
        const productosMap = {};

        categoria.detalle.forEach(producto => {
          if (!productosMap[producto.IdProducto]) {
            productosMap[producto.IdProducto] = {
              Cantidad: 0,
              Precio: 0,
              Productos: []
            };
          }
          productosMap[producto.IdProducto].Cantidad += producto.Cantidad;
          productosMap[producto.IdProducto].Productos.push(producto);
        });

        const detalleConTotales = [];
        Object.keys(productosMap).forEach(idProducto => {
          const grupo = productosMap[idProducto];
          grupo.Productos.forEach(producto => {
            detalleConTotales.push(producto);
          });
          // Producto:
          //   grupo.Productos.length > 0 ? grupo.Productos[0].Producto : "",

          detalleConTotales.push({
            Cliente: "TOTAL",
            IdProducto: "",
            Producto: "",
            descripcion: "",
            FechaEntrega: "",
            HoraEntrega: "",
            Repartidor: "",
            IdProformaDet: null,
            IdProforma: null,
            DisabledTotal: 0,
            Descripcion: "",
            Cantidad: grupo.Cantidad,
            Precio: 0,
            Descuento: 0
          });
        });

        // Reemplazar la lista original con la lista formateada
        categoria.detalle = detalleConTotales;
      });

      this.lstRepCliente = data;
    }
  }
};
</script>

<style>
.bg-total {
  background: #e8eaf6;
  font-weight: bold;
}
.align-center {
  min-width: 100%;
  align-content: center !important;
  align-items: center !important;
  text-align: center !important;
}
</style>
