<template>
    <div>
        <b-button @click="show=true" style="background: #D50000 ; color: #ffffff;" class="btnAction">Ver Proformas</b-button>
        
        <b-form ref="formProformaModal" >    
            <b-modal id="modalProformas" 
                title="Buscar Proforma"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">           
                    <b-button-group size="sm">
                        <b-form-select
                        v-model="empresa"
                        :options="$store.state.empresas"
                        size="lg"
                        class="form-control my-2"
                        @change="cambiarEmpresa"
                        ></b-form-select>
                    </b-button-group>
                    <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />  
                    <b-table 
                        v-if="show"
                        id="table-proformas"
                        ref="tableProformas"
                        striped hover 
                        :busy.sync="isBusy" 
                        :filter="filter"
                        :items="myProvider" 
                        :fields="fields"
                        :per-page="perPage"
                        :current-page="currentPage">

                      <template slot="update" slot-scope="data">
                        <b-button v-if="!cargandoProforma" @click="selectProformas(data.item)" variant="success"><i class="fas fa-hand-pointer"></i></b-button>
                        <div class="d-flex justify-content-center" v-else>
                          <div class="spinner" style="
                            width: 3rem;
                            height: 3rem;
                            border: 0.4em solid rgba(0, 0, 0, 0.1);
                            border-top-color: #007bff;
                            border-radius: 50%;
                            animation: spin 0.75s linear infinite;
                          "></div>
                        </div>
                      </template>

                      <template slot="ver" slot-scope="data">
                        <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma+'?empresa=' + empresaselec" variant="primary" target="_blank">Ver</b-button>
                      </template>

                      <template slot="verTicket" slot-scope="data">
                        <b-button :href="'/nuevo/index.html#/reportes/proformaticket/' + data.item.IdProforma + '&empresa=' + empresaselec" variant="primary" target="_blank">Ver Ticket</b-button>
                      </template>
                      
                      <template slot="Eliminar" slot-scope="data">
                        <b-button @click="eliminarProf(data.item)" variant="danger"><i class="fas fa-trash"></i></b-button>
                      </template>

                    </b-table>
                    <b-row>
                    <b-col md="6" class="my-1">
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                    </b-col>
                    </b-row>
                <div slot="modal-footer" >
                    <!--<b-btn size="sm" variant="success" type="submit">
                        Guardar
                    </b-btn>-->
                    <b-btn size="sm" variant="secondary" @click="show=false">
                        Cerrar
                    </b-btn>
                </div>
            
            </b-modal>
        </b-form>
    </div>
</template>
<script>
import config from "@/store/config.js";
import { mapActions } from "vuex";
import ProductosCarrito from "@/components/ProductosCarrito";

export default {
  mounted() {
    this.$emit("mounted", this.$refs.formProformaModal);
  },
  watch: {
    items() {
      this.axios
        .get(this.API + "proformas/count?prof=1&emp=" + this.empresaselec)
        .then(response => {
          this.totalRows = response.data.total;
        });
    }
  },
  data() {
    return {
      cargandoProforma: false,
      API: config.API,
      show: false,
      filter: "",
      items: [],
      isBusy: false,
      fields: [
        { key: "IdProforma", label: "Id" },
        { key: "Anio", label: "Año" },
        "Numero",
        "Cliente",
        { key: "DniRuc", label: "Documento" },
        "FechaReg",
        { key: "UsuarioReg", label: "Empleado" },
        // { key: "Total" },
        "update",
        "ver",
        "verTicket",
        "Eliminar"
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      empresaselec: sessionStorage.getItem("empresa") || "modasm"
    };
  },
  props: ["idAlmacen", "empresa"],
  methods: {
    ...mapActions({
      add: "agregarProducto",
      obtenerStok: "obtenerStock",
      limpiarCarrito: "limpiarCarrito"
    }),
    cambiarEmpresa() {
      setTimeout(() => {
        sessionStorage.setItem("empresa", this.empresa);
        window.location.reload();
        return false;
      }, 50);
    },
    async selectProformas(proforma) {
      try {
        this.cargandoProforma = true;
        this.limpiarCarrito();

        // 1. Obtener detalles de la proforma
        const { data: detalles } = await this.axios.get(
          `${this.API}proformas/detalle/nuevo`,
          {
            params: {
              idProforma: proforma.IdProforma,
              prof: 1,
              emp: this.empresaselec
            }
          }
        );

        // 2. Obtener empresas únicas
        const empresas = [...new Set(detalles.map(d => d.Empresa))];
        let productos = [];

        // 3. Consultar en paralelo el stock de cada empresa
        await Promise.all(
          empresas.map(async empresa => {
            const productosPorEmpresa = detalles.filter(
              d => d.Empresa === empresa
            );
            const idProductos = productosPorEmpresa.map(d => d.IdProducto);

            const { data: stock } = await this.axios.get(
              `${this.API}productos/stock/1`,
              {
                params: { id_empresa: empresa, idProductos }
              }
            );

            // 4. Fusionar stock con productos
            const productosConStock = productosPorEmpresa.map(det => {
              const encontrado = stock.find(
                s => s.IdProducto === det.IdProducto
              );
              console.log(det,'det')
              return {
                ...det,
                Producto: encontrado ? encontrado.Producto : det.Producto,
                stock: encontrado ? encontrado.stock : 0,
                Series: encontrado
                  ? encontrado["Series"]
                  : null,
                ProductoModelo: encontrado
                  ? encontrado["ProductoModelo"]
                  : null,
                ProductoDesc3: encontrado
                  ? encontrado["ProductoDesc3"]
                  : null,
                ProductoCategoria: encontrado
                  ? encontrado["ProductoCategoria"]
                  : null,
                ProductoMarca: encontrado
                  ? encontrado["ProductoMarca"]
                  : null,
                ProductoMedicion: encontrado
                  ? encontrado["ProductoMedicion"]
                  : null
              };
            });

            // 5. Normalizar productos e ir agregando al store
            productosConStock.forEach(item => {
              const empresaInfo = this.$store.state.empresas.find(
                e => e.value == item.Empresa
              );
              console.log("item", item);
              const normalizado = {
                ...item,
                Empresa: empresaInfo.value,
                NombreEmpresa: empresaInfo.text.substring(0, 20) + "...",
                estadoPxP: !!item.estadoPxP
              };

              this.add([
                normalizado,
                () => {},
                { setPreOr: item.Precio, cantidad: item.Cantidad }
              ]);

              productos.push(normalizado);
            });
          })
        );

        // 6. Finalizar
        this.filter = "";
        this.show = false;
        proforma.productos = productos;
        this.$store.state.lstProductosProforma = [...productos];
        this.$store.state.proforma = proforma;
        this.$emit("onSelectProforma", proforma);
      } catch (error) {
        console.error("Error en selectProformas:", error);
      } finally {
        this.cargandoProforma = false;
      }
    },
    // let empresa = this.$store.state.empresas.filter(
    //   v => v.value == this.empresa
    // )[0];
    // await this.axios
    //   .get(this.API + "proformas/detalle/nuevo", {
    //     params: {
    //       idProforma: proforma.IdProforma,
    //       prof: 1,
    //       emp: this.empresaselec
    //     }
    //   })
    //   .then(async response => {
    //     this.limpiarCarrito();
    //     let productos = [...response.data];
    //     productos.map(item => {
    //       let Empresa = this.$store.state.empresas.find(
    //         v => v.value == item.Empresa
    //       );
    //       item.Empresa = Empresa.value;
    //       item.NombreEmpresa = Empresa.text.substring(0, 20) + "...";
    //       //  empresa.text.substring(0, 20) + "..."
    //     });
    //     productos.map(item => {
    //       item.estadoPxP = !!item.estadoPxP;
    //       this.add([
    //         item,
    //         () => {},
    //         { setPreOr: item.Precio, cantidad: item.Cantidad }
    //       ]);
    //       this.filter = "";
    //       this.show = false;
    //     });
    //     await productos.forEach(async p => {
    //       await this.axios
    //         .get(this.API + `productos/stock/1`, {
    //           params: {
    //             id_empresa: p.Empresa,
    //             idProductos: [p.IdProducto]
    //           }
    //         })
    //         .then(res => {
    //           let producto = res.data[0];
    //           p.stock = producto.stock;
    //           this.$store.state.cart.ProductosCarrito.push(p);
    //         });
    //     });
    //     // proforma.productos = productos;
    //     // console.log(proforma);
    //     // this.$emit('onSelectProforma', proforma.IdCliente, proforma)
    //     // this.$emit("onSelectProforma", proforma);
    //   });
    myProvider(ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let promise = this.axios.get(
        this.API +
          "proformas?prof=1&emp=" +
          this.empresaselec +
          "&limit=" +
          ctx.perPage +
          "&IdAlmacen=" +
          this.idAlmacen +
          "&page=" +
          ctx.currentPage +
          "&filter=" +
          ctx.filter
      );

      return promise
        .then(result => {
          this.items = result.data;
          //this.totalRows = this.items.length
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          return this.items;
        })
        .catch(error => {
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          // Returning an empty array, allows table to correctly handle busy state in case of error
          return [];
        });
    },
    eliminarProf(data) {
      this.$swal({
        target: document.getElementById("conten"),
        title:
          "Se eliminará la proforma: <strong>" +
          data.Anio +
          " - " +
          data.Numero +
          "</strong> del  cliente <strong>" +
          data.Cliente +
          "</strong>",
        text: "¿Está seguro?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "No, atrás"
      }).then(result => {
        if (result.isConfirmed) {
          this.axios
            .delete(
              this.API +
                "proforma/eliminar/" +
                data.IdProforma +
                "?prof=1&emp=" +
                this.empresaselec
            )
            .then(res => {
              if (res.data) {
                this.$toasted.info("¡PROFORMA ELIMINADA!");
                this.$refs.tableProformas.refresh();
              }
            })
            .catch(err => {
              console.log(err);
              this.$toasted.error("Ha ocurrido un error");
            });
        }
      });
    }
  }
};
</script>
<style>
#modalProformas .modal-dialog {
  max-width: 65% !important;
}
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
