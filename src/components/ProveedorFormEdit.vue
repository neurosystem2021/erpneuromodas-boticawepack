<template>
  <div>
    <b-modal id="modal1" :title="titulo" size="lg" ref="proveedorForm" v-model="show">
      <b-row>
        <b-col md="12">
          <b-form-group>
            <label>RUC:</label>
            <b-input-group>
              <b-form-input 
                v-model="proveedor.Ruc"></b-form-input>
              <b-input-group-append>
                <b-btn variant="info" @click="consultarRUC">Buscar RUC</b-btn>
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
          <b-form-group>
            <label>Nombre del Proveedor:</label>
            <b-form-input 
              v-model="proveedor.Proveedor"
              type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Dirección:</label>
            <b-form-input 
              v-model="proveedor.Direccion"
              type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Teléfono:</label>
            <b-form-input 
              v-model="proveedor.Telefono"
              type="text"></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>
      <div slot="modal-footer" >
        <b-btn  variant="primary" @click="guardarProveedor">
           Guardar
         </b-btn>
         <b-btn  variant="secondary" @click="show=false">
           Close
         </b-btn>
       </div>
    </b-modal>
  </div>
</template>
<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      show: false,
      proveedor: {
        Proveedor: "",
        Ruc: "",
        Direccion: "",
        Telefono: "",
        Observacion: "",
        FechaReg: null
      },
      titulo: "",
      nuevo: true
    };
  },
  props: ["proveedorSelect"],
  watch: {
    show(pr) {
      let newVal = this.proveedorSelect || {};
      if (this.proveedorSelect.IdProveedor) {
        this.titulo = "Editar Proveedor " + this.proveedorSelect.IdProveedor;
        this.proveedor = Object.assign({}, this.proveedorSelect);
      } else {
        this.titulo = "Nuevo Proveedor";
        this.proveedor = {
          Proveedor: "",
          Ruc: "",
          Direccion: "",
          Telefono: "",
          Observacion: "",
          FechaReg: null
        };
      }
    }
  },
  methods: {
    consultarRUC() {
      this.axios
        .get(this.API + "consultarRUC", {
          params: {
            type: "RUC",
            numero: this.proveedor.Ruc,
            id_empresa: this.empresa
          }
        })
        .then(response => {
          var data = response.data;
          if (data.razonSocial) {
            this.$toasted.info("Proveedor encontrado, se llenarán sus datos");
            this.proveedor.Proveedor = data.razonSocial;
            this.proveedor.Direccion = data.direccion || "";
          } else {
            this.$toasted.show("Proveedor no encontrado");
          }
        })
        .catch(error => {
          this.$toasted.show("No se encontró el RUC");
          console.log(error);
        });
    },
    guardarProveedor() {
      this.$nextTick(() => {
        this.proveedor.Ruc = ("" + this.proveedor.Ruc).toUpperCase().trim();
        this.proveedor.Proveedor = ("" + this.proveedor.Proveedor)
          .toUpperCase()
          .trim();
        this.proveedor.Direccion = ("" + this.proveedor.Direccion)
          .toUpperCase()
          .trim();
        this.proveedor.Telefono = ("" + this.proveedor.Telefono)
          .toUpperCase()
          .trim();
      });
      console.log(this.proveedor);

      if (this.proveedor.Ruc == "") {
        this.$toasted.show("Ingrese el RUC del proveedor");
        return;
      }
      if (this.proveedor.Proveedor == "") {
        this.$toasted.show("Ingrese el nombre del proveedor");
        return;
      }
      let empresa = sessionStorage.getItem("empresa");
      this.axios
        .post(this.API + "proveedores?id_empresa=" + empresa, this.proveedor)
        .then(response => {
          if (response.data.updated) {
            this.$toasted.success("Proveedor editado correctamente");
          } else {
            this.$toasted.success("Proveedor guardado correctamente");
          }
          this.$emit("onAddProveedor", response.data);
          this.$refs.proveedorForm.hide();
        })
        .catch(error => {
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    }
  }
};
</script>
<style>
</style>
