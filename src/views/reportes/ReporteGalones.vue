<template>
  <b-container fluid>
    <b-row class="mt-2">
        <b-col cols="8">
            <b-row>
                <b-col cols="12">
                    <b-row>
                        <b-col cols="6">
                          <b-input-group prepend="Fecha Desde:" class="my-2">
                            <input type="date" class="form-control" v-model="fechaInicio"/>
                          </b-input-group>
                        </b-col>
                        <b-col cols="6" class="my-1">

                        </b-col>
                        <b-col cols="6" class="my-1">
                          <b-input-group prepend="Fecha Hasta:" class="my-2">
                              <input type="date" class="form-control" v-model="fechaFin"/>
                          </b-input-group>
                        </b-col>
                    </b-row>
                    
                </b-col>
                <b-col cols="6">
                  <b-input-group prepend="Productos" class="my-2">
                  <b-select 
                    :options="lstProductos" 
                    value-field="IdProducto"
                    text-field="Producto"  
                    v-model="IdProducto"
                  />
                  </b-input-group>
                </b-col>
            </b-row>
        </b-col>
        <b-col cols="2">
            <b-row>
                <b-col cols="12">
                    <b-button variant="success" class="my-2" @click="obtenerListado()"><i class="fas fa-search"  ></i> Generar</b-button>
                </b-col>
                <b-col cols="12">
                    <b-button variant="info" class="my-2" @click="exportarListado()"><i class="fas fa-file-excel"  ></i> Exportar</b-button>
                </b-col>
            </b-row>
            
            
            
        </b-col>
        <b-col cols="2" class="px-5 py-3" >
            <table class="table">
                <tr style="background: #bee5eb">
                    <td>Total Soles:</td>
                    <td>{{TotalSoles}}</td>
                </tr>
                <tr style="background: #c3e6cb;">
                    <td>Total Galones</td>
                    <td>{{TotalGalones}}</td>
                </tr>
            </table>
        </b-col>
 
    </b-row>

    <b-row class="my-2">
      <b-col cols="12">
        <b-table 
          :fields="fields" 
          :items="lstReporte"
          striped
          hover 
          :per-page="perPage"
          :current-page="currentPage"
          small
        >
          <template slot="Estado" slot-scope="data">
            {{ data.item.Estado ==1 ? 'Activo' : 'Anulado'  }}
          </template>
        </b-table>
      </b-col>
      <b-col cols="12" class="mt-5">
        <b-row>
          <b-col cols="6">
            
            <b-pagination
            v-model="currentPage"
            :total-rows="lstReporte.length"
            :per-page="perPage"
            aria-controls="my-table"
            ></b-pagination>
          </b-col>
          
            <b-col cols="6">
              <b-input-group prepend=" Mostrar:">
               
                <b-select 
                  :options="nroPaginacion" 
                  v-model="perPage"
                  value-field="key" 
                  text-field="text"
                />
              </b-input-group>
          </b-col>
        </b-row>
        </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      perPage: 10,
      currentPage: 1,
      fields: [
        { key: "FechaReg" },
        { key: "Estado" },
        { key: "Serie" },
        { key: "Numero" },
        { key: "DniRuc" },
        { key: "Cliente" },
        { key: "SubTotal" },
        { key: "Descuento" },
        { key: "Total", variant: "info" },
        { key: "Producto", label: "Combustible" },
        { key: "Precio" },
        { key: "Cantidad", variant: "success" }
      ],
      lstReporte: [],
      lstProductos: [],
      fechaInicio: "",
      IdProducto: "",
      fechaFin: "",
      TotalSoles: 0,
      TotalGalones: 0,
      nroPaginacion: [
        { key: 5, text: "5" },
        { key: 10, text: "10" },
        { key: 20, text: "20" },
        { key: 50, text: "50" },
        { key: "", text: "Todos" }
      ]
    };
  },
  mounted() {
    this.fechaInicio = this.$moment().format("YYYY-MM-DD");
    this.fechaFin = this.$moment().format("YYYY-MM-DD");
    this.obtenerProductos();
    this.obtenerListado();
  },
  methods: {
    async obtenerProductos() {
      await this.axios(`${this.API}productos`).then(res => {
        if (res.data) {
          let productos = res.data;
          productos.map(producto => {
            return (producto.Producto =
              producto.Isla + " - " + producto.Producto);
          });
          this.lstProductos = productos;
          let pre = {
            IdProducto: "",
            Producto: "Seleccione un Producto"
          };
          this.lstProductos.unshift(pre);
        }
      });
    },

    async obtenerListado() {
      let params = {
        fechaInicio: this.fechaInicio,
        fechaFin: this.fechaFin,
        IdProducto: this.IdProducto
      };
      await this.axios(`${this.API}reporte/galones`, { params: params }).then(
        res => {
          if (res.data) {
            let listado = res.data;
            listado.map(element => {
              if (element.Estado == 0) {
                element._rowVariant = "danger";
              }
            });
            this.lstReporte = listado;
          }
        }
      );
      this.recalcularTotal();
    },
    async exportarListado() {
      console.log("exportarListado");
      let params = {
        fechaInicio: this.fechaInicio,
        fechaFin: this.fechaFin,
        IdProducto: this.IdProducto
      };

      try {
        const res = await this.axios.get(
          `${this.API}reporte/galones/exportar`,
          { params }
        );

        if (res.data.url) {
          window.open(res.data.url, "_blank");
        } else {
          console.error("No se recibió una URL válida:", res.data);
        }
      } catch (error) {
        console.error("Error al exportar listado:", error);
      }
    },
    recalcularTotal() {
      this.TotalSoles = 0;
      this.TotalGalones = 0;

      this.TotalSoles = parseFloat(
        this.lstReporte.reduce((total, element) => {
          return total + element.Total;
        }, 0)
      ).toFixed(2);
      this.TotalGalones = parseFloat(
        this.lstReporte.reduce((total, element) => {
          return total + element.Cantidad;
        }, 0)
      ).toFixed(2);
    }
  }
};
</script>

<style>
</style>
