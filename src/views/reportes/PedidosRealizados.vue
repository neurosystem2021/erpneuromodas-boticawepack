<template>
  <b-container fluid>
    <b-row>
        <b-col cols="4" class="my-2">
            <b-input-group prepend="Fecha. Reg." class="mt-2">
                
                <input type="date" class="form-control" v-model="FechaPedido"/>
            </b-input-group>
        </b-col>
        <b-col cols="4" class="my-2">
            <b-input-group prepend="Fecha. Entrega." class="mt-2">
                
                <input type="date" class="form-control" v-model="FechaEntrega"/>
            </b-input-group>
        </b-col>
        <b-col cols="4" class="my-2">
            <b-button variant="info" @click="obtenerListado()"> Generar</b-button>
            <!-- <b-button variant="danger" @click="exportarExcelDesdeTabla(1)"> Exportar PDF</b-button> -->
            <b-button variant="success" v-if="PedidosFormateados.length>0" @click="exportarExcelDesdeTabla(2)"> Exportar EXCEL</b-button>
        </b-col>
        <b-col cols="12">
            <div class="table-container">
                <b-table striped hover id="miTabla" ref="miTabla" :items="PedidosFormateados" bordered :fields="Headers" class="fixed-column-table"></b-table>
            </div>
        </b-col>

    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      Clientes: [],
      Pedidos: [],
      Headers: [],
      PedidosFormateados: [],
      FechaPedido: "",
      FechaEntrega: "",
      API: config.API
    };
  },
  mounted() {
    // this.reCalcularHeader();
    // this.formatearProductos();
  },
  methods: {
    async obtenerListado() {
      this.Clientes = [];
      this.Headers = [];
      this.PedidosFormateados = [];
      await this.axios(
        `${this.API}reporte/pedidos?FechaPedido=${
          this.FechaPedido
        }&FechaEntrega=${this.FechaEntrega}`
      ).then(res => {
        if (res.data) {
          this.Pedidos = res.data;
        }
      });
      this.generarClientes();
      this.reCalcularHeader();
      this.formatearProductos();
    },
    generarClientes() {
      this.Pedidos.forEach(element => {
        if (!this.Clientes.some(v => v.IdCliente == element.IdCliente)) {
          this.Clientes.push(element);
        }
      });
    },
    reCalcularHeader() {
      if (this.Pedidos.length > 0) {
        let headers = [
          {
            key: "Cliente",
            label: "Nombre Cliente",
            class: "centrado"
          },
          {
            key: "Repartidor"
          },
          {
            key: "FechaEntrega"
          },
          {
            key: "HoraEntrega"
          }
        ];
        this.Pedidos.forEach(pedido => {
          let key = `Producto${pedido.IdProducto}`;
          if (!headers.some(v => v.key == key)) {
            headers.push({
              key: key,
              label: pedido.Producto,
              class: "headerCentrado"
              // class: "vertical-text"
            });
          }
        });

        this.Headers = headers;
      }
    },
    formatearProductos() {
      let PedidoFormateados = [];
      let Totales = {
        Cliente: "TOTALES"
      }; // Aquí guardaremos los totales

      this.Clientes.forEach(cliente => {
        let PedidoFormateado = { ...cliente };

        this.Pedidos.filter(v => v.IdCliente == cliente.IdCliente).forEach(
          pedido => {
            let key = `Producto${pedido.IdProducto}`;

            PedidoFormateado[key] = pedido.Cantidad;

            if (!Totales[key]) {
              Totales[key] = 0;
            }
            Totales[key] += pedido.Cantidad;
          }
        );

        PedidoFormateados.push(PedidoFormateado);
      });

      this.PedidosFormateados = PedidoFormateados;
      this.PedidosFormateados.push(Totales);
    },
    async exportarExcelDesdeTabla(tipo = 0) {
      let table = this.$refs.miTabla; // Asegúrate de agregar ref="miTabla" en <b-table>
      let data = [];

      // Recorrer las filas del cuerpo de la tabla
      let rows = table.$el.querySelectorAll("tbody tr");
      rows.forEach(row => {
        let rowData = {};
        let cells = row.querySelectorAll("td");

        // Recorrer las celdas y asignarlas a los headers
        this.Headers.forEach((header, index) => {
          rowData[header.key] = cells[index]
            ? cells[index].innerText.trim()
            : "";
        });

        data.push(rowData);
      });
      let body = {
        headers: [...this.Headers],
        data: data
      };
      this.axios
        .post(this.API + "exportar/reporte/pedidos", {
          FechaPedido: this.FechaPedido,
          FechaEntrega: this.FechaEntrega,
          headers: this.Headers,
          data: this.PedidosFormateados
        })
        .then(response => {
          if (response.data.success) {
            window.open(response.data.url, "_blank"); // Abrir en nueva pestaña
          } else {
            console.error(
              "Error al generar el reporte:",
              response.data.message
            );
          }
        })
        .catch(error => console.error("Error en la exportación:", error));
    }
  }
};
</script>

<style scope>
.vertical-text {
  writing-mode: vertical-rl; /* Vertical de derecha a izquierda */
  text-orientation: upright; /* Mantiene los caracteres en posición normal */
}
.centrado {
  align-content: center;
}
.headerCentrado {
  align-content: center;
  text-align: center;
}
.table-responsive {
  overflow-x: auto; /* Activa el scroll horizontal */
  max-width: 100%; /* Limita el ancho al 100% del contenedor */
  white-space: nowrap; /* Evita que el texto se rompa en varias líneas */
}

.fixed-column-table th:first-child,
.fixed-column-table td:first-child {
  position: sticky;
  left: 0;
  background-color: white; /* Asegura que el fondo no sea transparente */
  z-index: 2; /* Evita que se solape con otras celdas */
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); /* Opcional: agrega un efecto de sombra */
}
</style>
