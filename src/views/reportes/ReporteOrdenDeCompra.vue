<template>
  <b-container fluid>
    <center>
        <h1>Reporte de Orden de Producción</h1>
    </center>
    <b-card no-body>
      <b-tabs v-model="tabIndex" card>
        <b-tab title="Reporte Por Categoria">
          <b-button size="sm" variant="danger" @click="ExportarobtenerReportePersona()">Exporta PDF</b-button>
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
                    <th width="5%" style="text-align: center;">CANT. PED</th>
                    <th width="5%" style="text-align: center;">CANT. ENT.</th>
                    <th width="5%" style="text-align: center;">CANT. RES</th>
                  </tr>
                  <tr v-for="(det,index) in data.item.detalle" :key="index" >
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''">{{ det.Cliente }}</td>
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''">{{ det.Producto }}</td>
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''">{{ det.Descripcion }}</td>
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''">{{ det.FechaEntrega }} {{ det.HoraEntrega }}</td>
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''">{{ det.Repartidor }}</td>
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''" style="text-align: center;">{{ det.Cantidad }}</td>
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''" style="text-align: center;">{{ det.CantEntregada }}</td>
                    <td :class="det.Cliente=='TOTAL' ? 'bg-total':''" style="text-align: center;">{{ det.Cantidad - det.CantEntregada }}</td>
                    
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
          <b-button size="sm" variant="danger" @click="ExportarobtenerReporteProductos()">Exporta PDF</b-button>
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
        { key: "Cantidad", label: "Cantidad Pedida" },
        { key: "CantidadRealizada" },
        { key: "CantidadRestante" }
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
    async ExportarobtenerReportePersona() {
      await this.axios(`${this.API}reporte/ordencompra_persona/exportar`).then(
        res => {
          if (res) {
            let url = res.data;
            url = url.replace(/\\/g, "");
            if (!url.startsWith("http://") && !url.startsWith("https://")) {
              url = "http://" + url; // O "https://" si es necesario
            }
            window.open(url, "_blank");
          }
        }
      );
    },
    async obtenerReporteProducto() {
      await this.axios(`${this.API}reporte/ordencompra_productos`).then(res => {
        if (res) {
          this.lstRepProductos = res.data;
        }
      });
    },
    async ExportarobtenerReporteProductos() {
      await this.axios(
        `${this.API}reporte/ordencompra_productos/exportar`
      ).then(res => {
        if (res) {
          url = url.replace(/\\/g, "");
          if (!url.startsWith("http://") && !url.startsWith("https://")) {
            url = "http://" + url; // O "https://" si es necesario
          }
          window.open(url, "_blank");
        }
      });
    },
    formatearDetalleRepCliente(data) {
      data.forEach(categoria => {
        const productosMap = {};

        categoria.detalle.forEach(producto => {
          if (!productosMap[producto.IdProducto]) {
            productosMap[producto.IdProducto] = {
              Cantidad: 0,
              Precio: 0,
              CantEntregada: 0,
              Productos: []
            };
          }
          productosMap[producto.IdProducto].Cantidad += producto.Cantidad;
          productosMap[producto.IdProducto].CantEntregada +=
            producto.CantEntregada;
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
            CantEntregada: grupo.CantEntregada,
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
