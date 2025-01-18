<template>
  <b-container fluid>
    <b-row>
      <b-col cols="12"> 
        <h1>Ofertas</h1>
      </b-col>
      <b-col cols="12" style="text-align: end;"> 
        <b-button variant="success" class="my-2" @click="abrirModalRegistro()"> Nueva Oferta </b-button>
      </b-col>
      <b-col cols="12"> 
        <b-table :fields="headers" :items="lstOferta">
          <template slot="estado" slot-scope="data">
            {{ data.item.Estado ==1 ? "Activo" : "Inactivo" }}
          </template>
          <template slot="action" slot-scope="data">
            <b-button variant="info" v-if="data.item.Estado ==1" @click="abrirEditar(data.item)"><i class="fas fa-edit"></i></b-button>
            <b-button variant="danger" v-if="data.item.Estado ==1" @click="eliminar(data.item)"><i class="fa fa-trash" aria-hidden="true"></i></b-button>
            <!-- {{ data.item.Estado ==1 ? "Activo" : "Inactivo" }} -->
          </template>
          <template slot="ProductoFlag" slot-scope="data">
              <i v-if="data.item.ProductoFlag==1" class="fa fa-check" aria-hidden="true"></i>
              <i v-else class="fa fa-times" aria-hidden="true"></i>

          </template>
          <template slot="MontoFlag" slot-scope="data">
              <i v-if="data.item.MontoFlag==1" class="fa fa-check" aria-hidden="true"></i>
              <i v-else class="fa fa-times" aria-hidden="true"></i>

          </template>
          <template slot="CantidadFlag" slot-scope="data">
              <i v-if="data.item.CantidadFlag==1" class="fa fa-check" aria-hidden="true"></i>
              <i v-else class="fa fa-times" aria-hidden="true"></i>

          </template>
        </b-table>
      </b-col>

    </b-row>
    <b-modal v-model="nuevaOfertaFlag" size="lg" title="Nueva Oferta" hide-footer>
      <b-row>
        <b-col cols="12">
              <b-form-checkbox v-model="formulario.ProductoFlag" unchecked-value="0" value="1"> Producto </b-form-checkbox>
              <b-form-checkbox v-model="formulario.MontoFlag" unchecked-value="0" value="1"> Monto </b-form-checkbox>
              <b-form-checkbox v-model="formulario.CantidadFlag" unchecked-value="0" value="1"> Cantidad </b-form-checkbox>
        </b-col>
        <b-col cols="12" v-if="mostrarSiguientePaso()">
          <b-form @submit="guardar">
          <b-row>
            <b-col cols="12" v-if="formulario.ProductoFlag" class="mt-3"> 
              Producto
              <autocomplete
              ref="autocompleteProducto"
              :url="APIProductos"
              :onShouldRenderChild="renderChild"
              anchor="Producto"
              label="writer"
              placeholder="Agregar Producto"
              :on-select="setProducto"
              :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
              :classes="{input: 'form-control'}">
            </autocomplete>
            </b-col>
            <b-col cols="12" v-if="formulario.MontoFlag"> Monto
              <input type="number" v-model="formulario.Monto" class="form-control">

            </b-col>
            <b-col cols="12" v-if="formulario.CantidadFlag"> Cantidad Mínima
              <input type="number" v-model="formulario.Cantidad" class="form-control">

            </b-col>
          
            <b-col cols="12">
              Descripcion
              <textarea rows="1" v-model="formulario.Descripcion" class="form-control"  style="overflow:hidden; resize:none;"></textarea>  
            </b-col>
            <b-col cols="12">
              Porcentaje
              <input type="number" v-model="formulario.Porcentaje" class="form-control">  
            </b-col>
            <b-col cols="6">
              Desde
              <input type="date" v-model="formulario.FechaDesde" required class="form-control"> 

            </b-col>
            <b-col cols="6">
              Hasta
              <input type="date" v-model="formulario.FechaHasta" required class="form-control"> 

            </b-col>
            <b-col cols="12">
              Stockmax
              <input type="number" v-model="formulario.StockMax" class="form-control">    
            </b-col>
            <b-col cols="12" style="text-align: end;" class="mt-5"> 
              <b-button type="submit" variant="success" >Guardar</b-button>
              <b-button variant="danger" @click="nuevaOfertaFlag=!nuevaOfertaFlag">Cancelar</b-button>
            </b-col>
          </b-row>
        </b-form>
        </b-col>
        
      </b-row>
    </b-modal>
    <b-modal v-model="actualizarOfertaFlag" size="lg" title="Nueva Oferta" hide-footer>
      <b-row>
        <b-col cols="12">
              <b-form-checkbox v-model="formulario.ProductoFlag" unchecked-value="0" value="1"> Producto </b-form-checkbox>
              <b-form-checkbox v-model="formulario.MontoFlag" unchecked-value="0" value="1"> Monto </b-form-checkbox>
              <b-form-checkbox v-model="formulario.CantidadFlag" unchecked-value="0" value="1"> Cantidad </b-form-checkbox>
        </b-col>

        <b-col cols="12" v-if="mostrarSiguientePaso()">
          <b-form @submit="actualizar">
          <b-row>
            <b-col cols="12" v-if="formulario.ProductoFlag" class="mt-3"> 
              Producto
              <input type="text" readonly v-model="formulario.Producto" class="form-control">
            </b-col>
            <b-col cols="12" v-if="formulario.MontoFlag"> Monto
              <input type="number" v-model="formulario.Monto" class="form-control">

            </b-col>
            <b-col cols="12" v-if="formulario.CantidadFlag"> Cantidad Mínima
              <input type="number" v-model="formulario.Cantidad" class="form-control">

            </b-col>
          
            <b-col cols="12">
              Descripcion
              <textarea rows="1" v-model="formulario.Descripcion" class="form-control"  style="overflow:hidden; resize:none;"></textarea>  
            </b-col>
            <b-col cols="12">
              Porcentaje
              <input type="number" v-model="formulario.Porcentaje" class="form-control">  
            </b-col>
            <b-col cols="6">
              Desde
              <input type="date" v-model="formulario.FechaDesde" required class="form-control"> 

            </b-col>
            <b-col cols="6">
              Hasta
              <input type="date" v-model="formulario.FechaHasta" required class="form-control"> 

            </b-col>
            <b-col cols="12">
              Stockmax
              <input type="number" v-model="formulario.StockMax" class="form-control">    
            </b-col>
            <b-col cols="12" style="text-align: end;" class="mt-5"> 
              <b-button type="submit" variant="success" >Guardar</b-button>
              <b-button variant="danger" @click="actualizarOfertaFlag=!actualizarOfertaFlag">Cancelar</b-button>
            </b-col>
          </b-row>
        </b-form>
        </b-col>
        
      </b-row>
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      nuevaOfertaFlag: false,
      actualizarOfertaFlag: false,
      formulario: {
        ProductoFlag: 0,
        MontoFlag: 0,
        CantidadFlag: 0,
        IdProducto: "",
        Monto: "",
        Cantidad: "",
        Descripcion: "",
        Porcentaje: "",
        FechaDesde: "",
        FechaHasta: "",
        StockMax: ""
      },
      headers: [
        // { key: "tipo" },
        { key: "Hash",label:"Código" },
        { key: "ProductoFlag", label: "Por Prodcuto" },
        { key: "MontoFlag", label: "Por Monto" },
        { key: "CantidadFlag", label: "Por Cantidad Mínima" },
        { key: "Producto" },
        { key: "Monto" },
        { key: "Cantidad" },
        { key: "Descripcion" },
        { key: "Porcentaje" },
        { key: "FechaDesde" },
        { key: "FechaHasta" },
        { key: "StockMax" },
        { key: "estado" },
        { key: "action", label: "" }
      ],
      lstOferta: []
    };
  },
  mounted() {
    this.obtenerListado();
    this.formulario.desde = this.$moment().format("YYYY-MM-DD");
    this.formulario.hasta = this.$moment()
      .add(15, "days")
      .format("YYYY-MM-DD");
  },
  methods: {
    async obtenerListado() {
      await this.axios(`${this.API}ofertas`)
        .then(res => {
          if (res) {
            this.lstOferta = res.data;
          }
        })
        .catch(error => {
          console.log(error);
        });
    },
    abrirModalRegistro() {
      this.formulario = {
        ProductoFlag: 0,
        MontoFlag: 0,
        CantidadFlag: 0,
        IdProducto: "",
        Monto: "",
        Cantidad: "",
        Descripcion: "",
        Porcentaje: "",
        FechaDesde: this.$moment().format("YYYY-MM-DD"),
        FechaHasta: this.$moment()
          .add(15, "days")
          .format("YYYY-MM-DD"),
        StockMax: ""
      };
      this.nuevaOfertaFlag = true;
    },
    abrirEditar(item) {
      this.formulario = item;
      this.actualizarOfertaFlag = true;
    },
    mostrarSiguientePaso() {
      let val =
        this.formulario.ProductoFlag ||
        this.formulario.MontoFlag ||
        this.formulario.CantidadFlag;
      return val;
    },

    renderChild(data) {
      if (this.idAlmacen) {
        let style = "ss";

        // cambiar color los vencidos
        var dt = new Date();
        var anio = dt.getFullYear();
        var mes = dt.getMonth() + 1;
        var dia = dt.getDate();
        var fechaVen = new Date(data.FechaVen).getTime();

        var hoy = anio + "-" + mes + "-" + dia;
        hoy = new Date(hoy).getTime();
        var en4meses = anio + "-" + (mes + 4) + "-" + dia;
        en4meses = new Date(en4meses).getTime();

        if (fechaVen < hoy && data.stock > 0) {
          style = "danger";
        }

        // cambiar color los que faltan 4 meses por vencer
        if (fechaVen >= hoy && fechaVen <= en4meses && data.stock > 0) {
          style = "ambar";
        }

        return `
        <div class="${style}">
          <b class="bg-success" style="color: #fff; padding: 0 6px">${
            data.stock
          }</b> | <b>${data.CodigoBarra}</b> /
          <b>${data.Producto}</b> / ${data.ProductoMarca}
        </div>
        `;
      }
      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${
        data.ProductoMarca
      } / ${data.ProductoMedicion}
      `;
    },
    setProducto(obj) {
      this.formulario.IdProducto = obj.IdProducto;
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    async guardar(e) {
      e.preventDefault();
      if (this.formulario.ProductoFlag == 1 && !this.formulario.IdProducto) {
        this.$toasted.error("Es Necesario un Producto ");
        return false;
      }
      if (this.formulario.MontoFlag == 1 && !this.formulario.Monto) {
        this.$toasted.error("Es Necesario un Monto ");
        return false;
      }
      if (this.formulario.CantidadFlag == 1 && !this.formulario.Cantidad) {
        this.$toasted.error("Es Necesario una Cantidad ");
        return false;
      }

      await this.axios
        .post(`${this.API}ofertas/guardar`, this.formulario)
        .then(async res => {
          if (res) {
            await this.obtenerListado();
            this.nuevaOfertaFlag = false;
          }
        })
        .catch(error => {
          console.log(error);
        });
    },
    async actualizar(e) {
      e.preventDefault();
      if (this.formulario.ProductoFlag == 1 && !this.formulario.IdProducto) {
        this.$toasted.error("Es Necesario un Producto ");
        return false;
      }
      if (this.formulario.MontoFlag == 1 && !this.formulario.Monto) {
        this.$toasted.error("Es Necesario un Monto ");
        return false;
      }
      if (this.formulario.CantidadFlag == 1 && !this.formulario.Cantidad) {
        this.$toasted.error("Es Necesario una Cantidad ");
        return false;
      }
      await this.axios
        .put(`${this.API}ofertas/actualizar`, this.formulario)
        .then(async res => {
          if (res) {
            await this.obtenerListado();
            this.actualizarOfertaFlag = false;
          }
        })
        .catch(error => {
          console.log(error);
        });
    },
    eliminar(item) {
      this.$swal({
        icon: "question",
        text: "¿Desea Eliminar el registro?",
        showCancelButton: true,
        allowEscapeKey: false,
        allowEnterKey: false,
        allowOutsideClick: false
      }).then(async res => {
        if (res.isConfirmed) {
          await this.axios
            .put(`${this.API}ofertas/eliminar`, item)
            .then(async res => {
              if (res) {
                await this.obtenerListado();
                // this.actualizar = false;
              }
            })
            .catch(error => {
              console.log(error);
            });
        }
      });
    }
  },
  computed: {
    APIProductos() {
      if (this.idAlmacen) {
        return this.API + "productos/stock/" + this.idAlmacen;
      }
      return this.API + "productos";
    }
  }
};
</script>

<style>
</style>
