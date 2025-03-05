<template>
  <b-container fluid class="px-5 mb-5">
    <b-row>
       <b-col cols="12">
        <b-breadcrumb :items="items"></b-breadcrumb>
       </b-col>
        <b-col cols="2">
            Fecha
            <input type="date" class="form-control" v-model="fechaInicio">
        </b-col>
        <b-col cols="2">
            Fecha
            <input type="date" class="form-control" v-model="fechaFin">
        </b-col>
        <b-col cols="2">
          <b-form-checkbox
            class="mt-3"
            id="checkbox-1"
            v-model="declarado"
            name="checkbox-1"
            value="true"
            unchecked-value="false"
          >
            Declarado
          </b-form-checkbox>

        </b-col>
        <b-col cols="3">
          Buscar detalle del movimiento (buscar serie)
            <input type="text" class="form-control" v-model="descripcion">
        </b-col>
        <b-col cols="3" class="pt-4">
            <b-button variant="info" @click="generarReporte()"><i class="fa fa-search" aria-hidden="true"></i> Generar</b-button>
            <b-button variant="success" @click="exportarReporte()"><i class="fa fa-file-excel" aria-hidden="true"></i> Exportar</b-button>
        </b-col>
        <b-col cols="12" class="mt-4">
            <table class="table table-hover table-striped ">
                <thead>
                    <tr>
                        <th># Movimiento</th>
                        <th>Vendedor</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>FechaMov</th>
                        <th># Tipo</th>
                        <th>Tipo</th>
                        <th>Serie</th>
                        <th>Numero</th>
                        <th>Proveedor</th>
                        <th style="text-align: center;">SubTotal</th>	
                        <th style="text-align: center;">ISC</th>	
                        <th style="text-align: center;">IGV</th>	
                        <th style="text-align: center;">FLETE</th>	
                        <th style="text-align: center;">PERCEPCION</th>	
                        <th style="text-align: center;">MONEDA</th>	
                        <th style="text-align: center;">Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(reporte,index) in lstReporte" :key="index">
                        <td style="text-align: center;">{{reporte.IdMovimiento}}</td>
                        <td>{{reporte.UsuarioReg}}</td>
                        <td>{{reporte.AlmacenOrigen}}</td>
                        <td>{{reporte.AlmacenDestino}}</td>
                        <td>{{reporte.MovimientoFecha}}</td>
                        <td>{{reporte.IdMovimientoTipo}}</td>
                        <td>{{reporte.TipoMovimiento}}</td>
                        <td>{{reporte.Serie}}</td>
                        <td>{{reporte.Numero}}</td>
                        <td>{{reporte.Proveedor}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.SUBTOTAL).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.ISC).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.IGV).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.FLETE).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.Percepcion).toFixed(2)}}</td>
                        <td style="text-align: center;">{{reporte.Moneda}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.TOTAL).toFixed(2)}}</td>
                        <td>
                          <b-button size="sm" class="mx-1 my-1" @click="elimimnarRegistro(reporte)" variant="danger"><i class="fa fa-trash" aria-hidden="true"></i></b-button>
                          <b-button size="sm" class="mx-1 my-1" variant="info"><i class="fa fa-print" aria-hidden="true"></i></b-button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </b-col>
        <b-col cols="3" offset="9">
            <b-row>
              <b-col cols="6"> <b>Sub Total(PEN)</b> <input type="number" class="form-control" v-model="SubTotalPEN" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>Sub Total(USD)</b> <input type="number" class="form-control" v-model="SubTotalUSD" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>IGV(PEN)</b> <input type="number" class="form-control" v-model="IgvlPEN" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>IGV(USD)</b> <input type="number" class="form-control" v-model="IgvlUSD" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>Total(PEN)</b> <input type="number" class="form-control" v-model="TotalPEN" readonly name="" id=""></b-col>
              <b-col cols="6"> <b>Total(USD)</b> <input type="number" class="form-control" v-model="TotalUSD" readonly name="" id=""></b-col>
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
      fechaInicio: "",
      fechaFin: "",
      descripcion: "",
      declarado: true,
      API: config.API,
      lstReporte: [],
      items: [
        {
          text: "MOVIMIENTO",
          href: "#"
        },
        {
          text: "REPORTE",
          href: "#"
        },
        {
          text: "REGISTRO DE MOVIMIENTO",
          active: true
        }
      ],
      header: [
        { key: "IdProducto" },
        { key: "Producto" },
        { key: "Cantidad" },
        { key: "PrecioCosto" },
        { key: "PrecioVenta" },
        { key: "TotalVenta" },
        { key: "TotalCosto" },
        { key: "UtilidadBruta" },
        { key: "PorcentajeUti" }
      ],
      lstAlmances: [],
      SubTotalPEN: 0,
      SubTotalUSD: 0,
      IgvlPEN: 0,
      IgvlUSD: 0,
      TotalPEN: 0,
      TotalUSD: 0
    };
  },
  mounted() {
    this.fechaInicio = this.$moment()
      .startOf("month")
      .format("YYYY-MM-DD");
    this.fechaFin = this.$moment()
      .endOf("month")
      .format("YYYY-MM-DD");
    this.generarReporte();
    // this.obtenerAlmance();
  },
  methods: {
    async obtenerAlmance() {
      await this.axios(`${this.API}almacenes`).then(res => {
        if (res.data) {
          this.lstAlmances = res.data;
          this.lstAlmances.unshift({
            Almacen: "TODOS",
            IdAlmacen: 0
          });
        }
      });
    },
    async exportarReporte() {
      // http://localhost/api/index.php/reporte/utilidadbruta/excel?fechaInicio=2024-01-01&fechaFin=2026-01-01&almacen=0

      let params = {
        fechaInicio: this.fechaInicio,
        fechaFin: this.fechaFin,
        almacen: this.almacen
      };
      await this.axios(`${this.API}reporte/utilidadbruta/excel`, {
        params: params
      }).then(res => {
        console.log(res.data);
        window.open(res.data, "_blank");
      });
    },
    elimimnarRegistro(item) {
      this.$swal({
        icon: "question",
        title: "Eliminar",
        text: `Desea eliminar el registro ${item.Serie}-${item.Numero} `,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showCancelButton: true,
        cancelButtonColor: "#d33",
        confirmButtonText: "Si,Eliminar",
        cancelButtonText: "Cancelar"
      }).then(async respuesta => {
        if (respuesta.isConfirmed) {
          await this.axios.post(`${this.API}movimiento/eliminar`, {
            idMov: item.IdMovimiento
          });
          this.generarReporte();
        }
      });
      // console.log(item);
    },
    async generarReporte() {
      let params = {
        fechaInicio: this.fechaInicio,
        fechaFin: this.fechaFin,
        descripcion: this.descripcion,
        declarado: this.declarado
      };
      await this.axios(`${this.API}reporte/listarregmov`, {
        params: params
      }).then(res => {
        if (res.data) {
          let data = res.data;
          this.lstReporte = data;
          let SubTotalPEN = 0;
          let SubTotalUSD = 0;
          let IgvlPEN = 0;
          let IgvlUSD = 0;
          let TotalPEN = 0;
          let TotalUSD = 0;
          data.forEach(element => {
            if (element.Moneda == "PEN") {
              SubTotalPEN += element.SUBTOTAL;
              IgvlPEN += element.IGV;
              TotalPEN += element.TOTAL;
            }
            if (element.Moneda == "USD") {
              SubTotalUSD = element.SUBTOTAL;
              IgvlUSD = element.IGV;
              TotalUSD = element.TOTAL;
            }
          });
          this.SubTotalPEN = parseFloat(SubTotalPEN).toFixed(2);
          this.SubTotalUSD = parseFloat(SubTotalUSD).toFixed(2);
          this.IgvlPEN = parseFloat(IgvlPEN).toFixed(2);
          this.IgvlUSD = parseFloat(IgvlUSD).toFixed(2);
          this.TotalPEN = parseFloat(TotalPEN).toFixed(2);
          this.TotalUSD = parseFloat(TotalUSD).toFixed(2);
        }
      });
    }
  }
};
</script>

<style>
</style>
