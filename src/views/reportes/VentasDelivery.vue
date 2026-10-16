<template>
<b-container fluid class="nst-screen nst-screen--ventasdelivery">
  <section class="nst-panel nst-panel--filters"><b-row>
      <b-col cols="4" class="my-3">
          <b-input-group prepend="Desde:">
              <b-form-input v-model="desde" type="date"></b-form-input>
          </b-input-group>
      </b-col>
      <b-col cols="4" class="my-3">
          <b-input-group prepend="Hasta:">
              <b-form-input v-model="hasta" type="date"></b-form-input>
          </b-input-group>
      </b-col>
      <b-col cols="4" class="my-3">
          <b-button variant="success" @click="cargarVentasDelivery">Generar</b-button>
      </b-col>
      <!-- <b-col cols="2"></b-col>
      <b-col cols="2"></b-col> -->
      <b-col cols="12">
          <b-table 
              :items="ventas" 
              :fields="fields"
              :per-page="perPage"
              :current-page="currentPage" responsive=""
           show-empty empty-text="No hay registros para los filtros seleccionados." empty-filtered-text="No se encontraron coincidencias." >
              <template slot="action" slot-scope="data">
                  <b-button @click="cargarVentasDetalle(data.item)" variant="info"> <i class="fa fa-eye" aria-hidden="true"></i> Detalle</b-button>
              </template>
          </b-table>

          <b-pagination
              v-model="currentPage"
              :total-rows="ventas.length"
              :per-page="perPage"
              aria-controls="my-table"
          ></b-pagination>
      </b-col>
  </b-row></section>
  <b-modal v-model="dlgDetalle" size="lg" :title="`${venta.Serie}-${venta.Numero}`" modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
      <b-table :items="detalle" :fields="fieldsDet"></b-table>
  </b-modal>
</b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      dlgDetalle: false,
      venta: {},
      detalle: [],
      perPage: 10,
      currentPage: 1,
      desde: this.$moment().format("YYYY-MM-01"),
      hasta: this.$moment().format("YYYY-MM-DD"),
      API: config.API,
      ventas: [],
      fields: [
        { key: "TipoDoc" },
        { key: "Serie" },
        { key: "Numero" },
        { key: "Cliente" },
        { key: "NombreLlevar", label: "Nombre" },
        { key: "DireccionEntrega" },
        { key: "FechaReg" },
        { key: "action", label: "" }
      ],
      fieldsDet: [
        { key: "Producto" },
        { key: "Cantidad" },
        { key: "Precio" },
        { key: "Subtotal" },
        { key: "Descuento" },
        { key: "Total" }
      ]
    };
  },
  mounted() {
    this.cargarVentasDelivery();
  },
  methods: {
    async cargarVentasDelivery() {
      this.venta = [];
      await this.axios(`${this.API}ventas/delivery`, {
        params: {
          desde: this.desde,
          hasta: this.hasta
        }
      }).then(res => {
        this.ventas = res.data;
      });
    },
    async cargarVentasDetalle(obj) {
      this.venta = obj;
      this.detalle = [];
      await this.axios(`${this.API}ventas/detalle`, {
        params: { idDocVentas: [obj.idDocVenta] }
      }).then(res => {
        this.detalle = res.data;
        this.dlgDetalle = true;
      });
    }
  }
};
</script>

<style>
</style>
