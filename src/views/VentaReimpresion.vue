<template>
  <div>
      <b-container fluid class="bv-example-row">
        <b-row>
          <b-col md="6" class="my-1">
            <b-form-group horizontal label="Buscar" class="mb-0">
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="onFilterInput" />
                <b-input-group-append>
                  <b-btn :disabled="!filter" @click="limpiarFiltro">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
            <br />
          </b-col>
          <b-col md="6" class="my-1">
            <b-row>
            <b-col md="8">

              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
            <b-col md="4">
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Documentos</p>
            </b-col>
            </b-row>
          </b-col>

          <b-table small
            ref="tablaVentas"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            @row-clicked="rowClick"
            id="my-table" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage">
          
              <!-- <template slot="Visualizar" slot-scope="data">
                  <a class="btn btn-success" target="_blank" :href="'/imprimir/index.php?IdDocVenta=' + data.item.idDocVenta + '&preview=1'">{{data.item.TipoDoc}}</a>
              </template> -->
              <template slot="Reimprimir" slot-scope="data">
                <a class="btn btn-success" target="_blank" :href="'/imprimir/index.php?IdDocVenta=' + data.item.idDocVenta + '&preview=1'" >{{ data.item.TipoDoc }}</a>
                <a class="fas fa-print btn btn-primary" href="#" @click.prevent="reimprimir(data.item.idDocVenta)"><i class="fas fa-print"></i></a>
                <b-button variant="warning" @click="descargarXML(data.item)">XML</b-button>
              </template>
          </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <br />
        <br />
      </b-container>
  </div>
</template>

<script>
import config from "@/store/config.js";

export default {
  mounted() {
    this.axios.get(this.API + "ventas/count").then(response => {
      this.totalRows = response.data.total;
    });
  },
  data() {
    return {
      API: config.API,
      pageOptions: [5, 10, 15, 50, 100],
      filter: null,
      sortBy: "idDocVenta",
      sortDesc: true,
      productoSelect: {},
      items: [],
      isBusy: false,
      fields: [
        { key: "idDocVenta", label: "ID", sortable: true },
        { key: "Correlativo", sortable: true },
        { key: "FechaDoc", sortable: true },
        { key: "DniRuc", sortable: true },
        { key: "TipoDoc", sortable: true },
        { key: "Cliente", sortable: true },
        { key: "Total", sortable: true },
        // { key: "Visualizar" },
        { key: "Reimprimir", label: "Reimprimir", class: "text-center" }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      lastCountKey: null,
    };
  },
  methods: {

    descargarXML: function (doc) {
    if (!doc || !(doc.idDocVenta || doc.IdDocVenta)) {
      this.$toasted.error("Id de venta no disponible");
      return;
    }

    this.axios.post(this.API + 'emitirelectronico', {
      generarXml: true,
      docVenta: doc
    })
    .then(function (response) {
      if (response.data.success) {
        this.$toasted.success(response.data.msj_sunat || "XML generado correctamente");

        var id = doc.idDocVenta || doc.IdDocVenta;
        this.axios.get(this.API + 'imprimirxml/' + id)
          .then(function (res) {
            if (res.data.success && res.data.xmlBase64) {
              var blob = new Blob(
                [Uint8Array.from(atob(res.data.xmlBase64), c => c.charCodeAt(0))],
                { type: 'application/xml' }
              );
              var link = document.createElement('a');
              link.href = window.URL.createObjectURL(blob);
              link.download = res.data.nombreArchivo; // ← nombre exacto del backend
              link.click();
            } else {
              this.$toasted.error("No se pudo obtener el XML");
            }
          }.bind(this));
      } else {
        this.$toasted.error(response.data.msj_sunat || "Error generando XML");
      }
    }.bind(this))
    .catch(function (error) {
      console.error(error);
      this.$toasted.error("Error en la petición");
    }.bind(this));
  },
    rowClick(item, index) {
      this.productoSelect = item;
      //this.$refs.productoForm.producto.IdProducto = 'a'
      //console.log(this.$refs.productoForm.$refs.productoForm)
    },
    actualizarTabla() {
      this.$refs.tablaVentas.refresh();
    },
    nuevoProducto() {
      this.productoSelect = {};
      this.$refs.productoForm.show = true;
    },
    irGuiaRemision(item) {
      console.log(item);
      this.$router.push({
        name: "RemisionVer",
        params: {
          idDocVenta: item.idDocVenta,
          Serie: item.Serie,
          Numero: item.Numero
        }
      });
    },
    myProvider(ctx) {
      let queryParams =
        "noFecha=1" +
        "&limit=" + ctx.perPage +
        "&page=" + ctx.currentPage +
        "&q=" + encodeURIComponent(this.filter || "") +
        "&sortBy=" + ctx.sortBy +
        "&sortDesc=" + (ctx.sortDesc ? 1 : 0);

      let url = this.API + "ventas?" + queryParams;

      const countKey = this.filter || "";
      if (this.lastCountKey !== countKey) {
        this.lastCountKey = countKey;
        this.axios
          .get(this.API + "ventas/count?noFecha=1&q=" + encodeURIComponent(countKey))
          .then(res => { this.totalRows = res.data.total; })
          .catch(() => {});
      }

      return this.axios.get(url)
        .then(result => { this.items = result.data; return this.items; })
        .catch(error => []);
    },
    reimprimir(idDocVenta) {
      const url = "/imprimir/index.php?IdDocVenta=" + idDocVenta + "&preview=1";
      const iframe = document.createElement("iframe");
      iframe.style.position = "absolute";
      iframe.style.width = "0px";
      iframe.style.height = "0px";
      iframe.style.border = "none";
      iframe.src = url;
      iframe.onload = function() {
        iframe.contentWindow.print();
        setTimeout(() => document.body.removeChild(iframe), 1000);
      };
      document.body.appendChild(iframe);
    },
    onFilterInput() {
      this.currentPage = 1;
      this.actualizarTabla();
    },
    limpiarFiltro() {
      this.filter = "";
      this.currentPage = 1;
      this.actualizarTabla();
    },
    /*reimprimir(idDocVenta){
          this.axios.get(this.API + 'ventas/reimpresion/rapida?idDocVenta=' + idDocVenta)
          .then((response) => {
            let venta = {
              CampoDireccion: response.data.venta.CampoDireccion,
              FechaCredito: response.data.venta.FechaCredito,
              EsCredito: response.data.venta.EsCredito,
              PagoCon: response.data.venta.PagoCon,
              dataInfoDocVenta:{
                Fecha: response.data.venta.FechaDoc,
                Numero: response.data.venta.Numero,
                Serie: response.data.venta.Serie,
              },
              cliente:{
                Cliente: response.data.venta.Cliente,
                Direccion: response.data.venta.Direccion,
                DniRuc: response.data.venta.DniRuc,
              },
              productos:response.data.productos,
              tipoVenta:{
                CodSunat: response.data.venta.CodSunat,
              },
              vendedor: {
                Usuario: response.data.venta.UsuarioReg,
              },
              reps: 1,
              abrirCaja: 0
            }
            let ventaJson= JSON.stringify(venta)
            let left = (screen.width/2)-(800/2)
                let top = (screen.height/2)-(800/2)
                let winName='Impresion';
                let winURL='http://localhost:5000/imprimir/getdata.php';
                let windowoption='resizable=yes,height=300,width=300,location=0,menubar=0,scrollbars=1,top='+top+', left='+left;     
                let form = document.createElement("form");
                form.setAttribute("method", "post");
                form.setAttribute("action", winURL);
                form.setAttribute("target",winName);  
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'venta';
                input.value = ventaJson;
                form.appendChild(input);
                document.body.appendChild(form);                       
                window.open('', winName,windowoption);
                form.target = winName;
                form.submit();                 
                document.body.removeChild(form);  
          })
          .catch((error) => {
              console.log(error)
              this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
          })
      },*/
  },
  components: {
  }
}
</script>

<style>

</style>