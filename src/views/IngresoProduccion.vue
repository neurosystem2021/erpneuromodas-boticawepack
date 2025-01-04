<template>
  <b-container fluid>
    <b-row>
        <b-col cols="12">
          <h2>INGRESO DE PRODUCCIÓN</h2>
        </b-col>
        <b-col cols="2">
            <b-form-group>
             <label>Tipo de Movimiento:</label>
             <b-select :options="lstTipoMovimiento"
                text-field="TipoMovimiento"
                value-field="IdMovimientoTipo"
                v-model="data.IdMovimientoTipo"
                ></b-select>
                
              </b-form-group>
            </b-col>
            <b-col cols="4">
              <b-form-group>
                <label>Proveedor:</label>
                <b-input-group>
                  <b-select 
                    :options="lstProveedor"
                    text-field="Proveedor"
                    value-field="IdProveedor"
                    v-model="data.IdProveedor"
                    readonly
                  ></b-select>
             </b-input-group>
         </b-form-group>
        </b-col>
        <b-col cols="3">
            <b-form-group>
                <label>Serie:</label>                    
                <b-form-input v-model="data.Serie"
                required="required"
                type="text"
                placeholder=""
                readonly></b-form-input>
            </b-form-group>
        </b-col>
        <b-col cols="3">
            <b-form-group>
                <label>Número:</label>                    
                <b-form-input v-model="data.Numero"
                required="required"
                type="text"
                placeholder=""
                readonly>
            </b-form-input>
            </b-form-group>
        </b-col>
    </b-row>
    
    <b-row>
        <b-col cols="12">
            <b-table :fields="headerRepPro" :items="lstRepProductos">
                <template slot="cantidad" slot-scope="data">
                    <input type="number" v-model="data.item.cantidad" class="form-control" style="max-width: 120px;">
                </template>
                <template slot="diff" slot-scope="data">
                    <span :class="(data.item.cantidad?data.item.cantidad :0) -data.item.CantidadRestante<0?'bg-red':'bg-green'">
                      {{ (data.item.cantidad?data.item.cantidad :0) -data.item.CantidadRestante }}
                    </span>
                   
                </template>
            </b-table>
        </b-col>
    </b-row>
    <b-row>
        <b-col cols="12" class="align-center">
            <b-button variant="success" @click="guardar()"> Guardar</b-button>
        </b-col>
    </b-row>
  </b-container>
</template>


<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      lstTipoMovimiento: [],
      lstProveedor: [],
      empresa: "",
      API: config.API,
      headerRepPro: [
        { key: "ProductoCategoria" },
        { key: "CodigoBarra" },
        { key: "Producto" },
        {
          key: "CantidadRestante",
          label: "Cantidad Pedida",
          variant: "warning"
        },
        { key: "cantidad", label: "Cantidad Realizada" },
        { key: "diff", label: "R/EXC" }
      ],
      lstRepProductos: [],
      data: {
        IdMovimientoTipo: 13,
        IdProveedor: "",
        Serie: "PROD",
        Numero: "",
        FechaPeriodoTributario: ""
      }
    };
  },
  mounted() {
    let currentMonth = this.$moment().month() + 1;
    currentMonth = currentMonth < 10 ? "0" + currentMonth : currentMonth;

    this.obtenerListTipoMovimiento();
    this.obtenerReporteProducto();
    this.obtenerListProveedor();
    this.cargarNumero();
    this.$store.state.MostrarBotonMultipleEmpresa = false;
    this.data.FechaPeriodoTributario =
      this.$moment().year() + "" + currentMonth;
    console.log(this.data);
  },
  methods: {
    async obtenerReporteProducto() {
      await this.axios(`${this.API}reporte/ordencompra_productos`).then(res => {
        if (res) {
          this.lstRepProductos = res.data;
        }
      });
    },
    async obtenerListTipoMovimiento() {
      let IdMovimientoTipo = [13, 15];
      await this.axios(`${this.API}movimientos/tipos`, {
        params: {
          id_empresa: this.empresa
        }
      }).then(res => {
        if (res) {
          this.lstTipoMovimiento = res.data.filter(v =>
            IdMovimientoTipo.includes(v.IdMovimientoTipo)
          );
        }
      });
    },
    async obtenerListProveedor() {
      let Ruc = ["20613130862", "20613130862"];
      await this.axios(`${this.API}proveedores`, {
        params: {
          id_empresa: this.empresa,
          q: "20613130862"
        }
      }).then(res => {
        if (res) {
          let d = res.data.filter(v => Ruc.includes(String(v.Ruc).trim()));
          this.data.IdProveedor = d[0] ? d[0].IdProveedor : "";
          this.lstProveedor = d;
        }
      });
    },
    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick();
    },
    setTipoMovimiento(obj) {},
    cargarNumero() {
      this.axios
        .get(this.API + "movimientos/numero", {
          params: {
            serie: this.data.Serie,
            idMovimientoTipo: this.data.IdMovimientoTipo,
            id_empresa: this.empresa
          }
        })
        .then(response => {
          this.data.Numero = response.data.NuevoNumero || "0001";
        })
        .catch(error => {
          console.log(error);
          // this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        });
    },
    async guardar() {
      if (!this.lstRepProductos.some(v => !!v.cantidad && v.cantidad > 0)) {
        this.$toasted.show(
          "Es necesario al menos un producto con cantidad mayor que 0"
        );
        return false;
      }
      this.data.productos = this.lstRepProductos.filter(
        v => !!v.cantidad && v.cantidad > 0
      );
      await this.axios
        .post(this.API + "registro/produccion", this.data)
        .then(response => {
          this.$swal
            .fire({
              icon: "success",
              title: "Confirmación",
              text: "Registro Correcto",
              allowOutsideClick: false,
              allowEscapeKey: false
            })
            .then(res => {
              if (res.isConfirmed) {
                window.location.reload();
              }
            });
        })
        .catch(error => {
          console.log(error);
        });
    }
  }
};
</script>

<style>
.bg-red {
  background: red;
  color: white;
  font-weight: bold;
  padding: 5px 20px;
}
.bg-green {
  background: green;
  color: white;
  font-weight: bold;
  padding: 5px 20px;
}
</style>
