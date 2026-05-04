<template>
    <div>
        <b-button @click="show=true" variant="outline-success">Ver Cierres</b-button>
        
        <b-form >    
            <b-modal id="modalCierres" 
                title="Seleccionar Cierre de Caja"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">           
                <div class="d-flex flex-column align-items-center mb-4">
                    <b-button-group size="sm" class="w-100">
                        <b-form-select
                            v-model="id_empresa_local"
                            :options="$store.state.empresas"
                            size="lg"
                            value-field="IDEMPRESA"
                            text-field="RAZONSOCIAL"
                            @change="cambiarEmpresa" 
                        ></b-form-select>
                    </b-button-group>
                </div>
                    <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" class="mb-2" />  
                    <b-table 
                        ref="tableCierres"
                        v-if="show"
                        id="my-table" 
                        striped hover 
                        :busy.sync="isBusy" 
                        :filter="filter"
                        :items="myProvider" 
                        @row-clicked="selectCierre"
                        :fields="fields"
                        :per-page="perPage"
                        :current-page="currentPage">
                        
                      <template slot="ver" slot-scope="data">
                        <b-button :href="'/views/ve_cierreform.php?idCierre=' + data.item.IdCierreCaja + '&empresa=' + id_empresa_local" variant="primary" target="_blank">Ver</b-button>
                        <b-button :href="'/api/index.php/cierrecaja/pdfresumen/' + data.item.IdCierreCaja" target="_blank" variant="success" >PDF Resumen</b-button>
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

export default {
  mounted() {
    const guardada = sessionStorage.getItem("empresa");
    if (guardada) {
            this.id_empresa_local = guardada;
        } else if (this.empresa) {
            this.id_empresa_local = this.empresa;
        }
      },
  watch: {
    items() {
      let empresa = sessionStorage.getItem("empresa");
      this.axios
        .get(this.API + "cierrecaja/count", {
          id_empresa: empresa
        })
        .then(response => {
          this.totalRows = response.data.total;
        });
    }
  },
  props: ["empresa"],
  data() {
    return {
      id_empresa_local: this.empresa,
      API: config.API,
      show: false,
      filter: "",
      items: [],
      isBusy: false,
      fields: ["IdCierreCaja", "FechaCierre", "IdTurno", "UsuarioReg", "ver"],
      totalRows: 50,
      perPage: 5,
      currentPage: 1
    };
  },
  computed: {
    nombreEmpresaActual() {
      const lista = this.$store.state.empresas;
      if (lista && this.id_empresa_local) {
        const encontrada = lista.find(e => String(e.IDEMPRESA) === String(this.id_empresa_local));
        return encontrada ? encontrada.RAZONSOCIAL : "";
      }
      return "Seleccione una Empresa";
    }
  },
  methods: {
    cambiarEmpresa(nuevoId) {
        // 1. Guardamos en el storage por si el usuario refresca manualmente luego
        sessionStorage.setItem("empresa", nuevoId);
        
        // 2. Actualizamos el total de filas para la nueva empresa
        this.actualizarCount(nuevoId);

        // 3. ¡ESTA ES LA MAGIA! Refresca solo la tabla dentro del modal
        this.$nextTick(() => {
            if (this.$refs.tableCierres) {
                this.$refs.tableCierres.refresh();
            }
        });
    },

    actualizarCount(idEmpresa) {
        this.axios.get(this.API + "cierrecaja/count", {
            params: { id_empresa: idEmpresa }
        })
        .then(response => {
            this.totalRows = response.data.total;
        });
    },
    selectCierre(item) {
      // console.log(item)
    },
    myProvider(ctx) {
      let empresa = sessionStorage.getItem("empresa");
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let promise = this.axios.get(
        this.API +
          "cierrecaja?limit=" +
          ctx.perPage +
          "&page=" +
          ctx.currentPage +
          "&filter=" +
          ctx.filter +
          "&sortBy=IdCierreCaja&sortDesc=1" +
          "&id_empresa=" +
          empresa
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
    }
  }
};
</script>
<style>
</style>
