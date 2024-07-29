<template>
<b-container fluid>
  <b-row class="mt-3 revisionProducto">
    <b-col cols="4">
        <b-row>
            <b-col cols="12">
                <b-form-input v-model="codigoBarra" placeholder="Código de Barra"  class="form-control"/>
            </b-col>
            <b-col cols="12">
               <b-card
                            
                            :img-src="producto.Imagen == null ||producto.Imagen == ''? 'static/no-photo.png': '/resources/images/productos/' +producto.Imagen"
                            img-alt="Image"
                            img-top
                            tag="article"
                            
                            class="mt-2"
                        >
                           
                </b-card>
            </b-col>
        </b-row>
    </b-col>
    <b-col cols="8" class="detalle">
        <h2> <b>DATOS DEL PRODUCTO: </b></h2>
        <b-row>
            <b-col cols="12"> <hr class="division"></b-col>
            <b-col cols="3" ><b>Categoría:</b></b-col>
            <b-col cols="9" class="producto">{{producto.ProductoCategoria}}</b-col>
            <b-col cols="12"> <hr class="division"></b-col>
            <b-col cols="3" ><b>Nombre:</b></b-col>
            <b-col cols="9" class="producto">{{producto.Producto}}</b-col>
            <b-col cols="12"> <hr class="division"></b-col>
            <b-col cols="12" class="m-2">
                <b>Presentación(es)</b>
                <b-row class="my-3">
                    <b-col cols="4">
                        <b-card title="" sub-title="" class="mt-3 presentacion">
                             <b-row>
                                <b-col cols="6"> UNIDAD </b-col>
                                <b-col cols="6" class="borderIzquierdo precio">
                                <b>
                                    Precio: <br>
                                    {{producto.PrecioContado}}
                                </b>

                                </b-col>
                            </b-row>
                        </b-card>
                    </b-col>
                    <b-col cols="4" v-for="(pr , index) in producto.PreciosPorProducto.filter((v)=>v.cantidad>0)" :key="index" >
                        <b-card title="" sub-title="" class="mt-3 presentacion">
                             <b-row>
                                <b-col cols="6">{{pr.nombre}}</b-col>
                                <b-col cols="6" class="borderIzquierdo precio">
                                <b>
                                    Precio: <br>
                                    {{parseFloat(pr.precio).toFixed(2)}}
                                </b>

                                </b-col>
                            </b-row>
                        </b-card>
                    </b-col>
                </b-row>
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
      codigoBarra: "",
      empresa: "",
      API: config.API,
      producto: { PrecioContado: "", PreciosPorProducto: [] }
    };
  },
  async beforeMount() {
    let empresa = sessionStorage.getItem("empresa");
    if (!empresa) {
      this.empresa = "modasm";
      sessionStorage.setItem("empresa", this.empresa);
    } else {
      this.empresa = empresa;
    }
    this.setScreen();
  },
  methods: {
    loadBarcode() {
      if (this.codigoBarra) {
        let params = {
          codigoBarra: this.codigoBarra,
          id_empresa: this.empresa
        };
        this.axios
          .get(this.API + "productos/id/" + this.codigoBarra, {
            params: params
          })
          .then(response => {
            if (response.data.IdProducto) {
              let PreciosPorProducto = JSON.parse(
                response.data.PreciosPorProducto
              );
              this.producto = response.data;
              this.producto.PreciosPorProducto = PreciosPorProducto;
              this.codigoBarra = "";
            } else {
              this.loadCode = "";
              this.$toasted.show("No se encontró ningun producto");
            }
          });
        setTimeout(() => {
          window.location.reload();
        }, 30 * 1000);
      }
    },
    setScreen() {
      window.parent.document.getElementById("NavegadorParent").style.display =
        "none";
    }
  },
  watch: {
    codigoBarra() {
      this.loadBarcode();
    }
  }
};
</script>

<style scope>
.borderIzquierdo {
  border-left: 1px solid #000 !important;
}
.revisionProducto {
  /* z-index: 1; */
  max-width: 95%;
  align-content: center;
  /* align-items: center; */
  /* min-width: 100wh; */
  /* min-height: 100vh; */
}
.detalle {
  font-size: 2rem;
}
.division {
  border: 1px dashed #000 !important;
}
.precio {
  /* color: blue; */
  font-weight: bold;
  font-size: 2.7rem;
}
.producto {
  color: #1a237e;
  font-weight: bold;
}
.presentacion {
  background: #e1f5fe;
}
</style>
