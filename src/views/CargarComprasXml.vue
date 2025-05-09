<template>
    <b-container>
        <b-row>
            <b-col cols="12" class="mb-3">
                <b-form-file @change="leerXML" accept=".xml" > </b-form-file>
            </b-col>
            <b-col cols="2">
                Proveedor:
            </b-col>
            <b-col cols="5">
              {{ rucProveedor }} - {{ nameProveedor }}
            </b-col>
            <b-col cols="5">
              <b-alert show variant="info">
                Si el proveedor no se encuentra registrado, se incorporará al sistema.
              </b-alert>
            </b-col>

            <b-col cols="12" width="100%">
               <table width="100%">
                <thead>
                    <tr>
                        <th>Cod. Barra</th>
                        <th>Producto</th>
                        <th style="text-align: center;">Cantidad</th>
                        <th style="text-align: center;">Precio</th>
                        <th style="text-align: center;">Total</th>
                    </tr>
                </thead>
                <tbody>
                  <tr v-for="(producto,index) in lstProductos" :key="index">
                    <td class="px-2" width="20%" > <input type="text" class="form-control" v-model="producto.CodigoBarra"> </td>
                    <td class="px-2" width="50%" >{{ producto.Producto }}</td>
                    <td class="px-2" width="10%" style="text-align: center;">{{ producto.cantidad }}</td>
                    <td class="px-2" width="10%" style="text-align: center;">{{ producto.PrecioCosto }}</td>
                    <td class="px-2" width="10%" style="text-align: center;">{{ parseFloat(producto.PrecioCosto * producto.PrecioCosto).toFixed(2) }}</td>
                  </tr>
                </tbody>
               </table>
            </b-col>
            <b-col cols="12" v-if="mostrarAdvertencia" class="mt-2">
              <b-alert show variant="warning">
                Aquellos productos que no cuenten con un código de barras serán incorporados al sistema mediante un registro automático, durante el cual se les asignará un código.
              </b-alert>
            </b-col>
        </b-row>
        
      <!-- <input type="file" /> -->
    </b-container>
  </template>
  
  <script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      rucProveedor: "",
      nameProveedor: "",
      productos: [],
      lstProductos: []
    };
  },
  methods: {
    leerXML(e) {
      this.lstProductos = [];
      const file = e.target.files[0];
      if (!file) return;

      const validMimeTypes = ["text/xml", "application/xml"];
      const validExtension = file.name.toLowerCase().endsWith(".xml");

      if (!validMimeTypes.includes(file.type) && !validExtension) {
        alert("Por favor selecciona un archivo XML válido.");
        return;
      }
      const reader = new FileReader();
      reader.onload = event => {
        const xml = event.target.result;
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(xml, "application/xml");

        this.rucProveedor = "";
        try {
          const accountingSupplier = xmlDoc.getElementsByTagName(
            "cac:AccountingSupplierParty"
          )[0];
          const party = accountingSupplier.getElementsByTagName("cac:Party")[0];
          const partyId = party.getElementsByTagName(
            "cac:PartyIdentification"
          )[0];
          const id = partyId.getElementsByTagName("cbc:ID")[0];
          this.rucProveedor = id.textContent;
        } catch (error) {
          console.warn("No se pudo obtener el RUC del proveedor:", error);
        }

        this.nameProveedor = "";
        try {
          const accountingSupplier = xmlDoc.getElementsByTagName(
            "cac:AccountingSupplierParty"
          )[0];
          const party = accountingSupplier.getElementsByTagName("cac:Party")[0];
          const partyId = party.getElementsByTagName("cac:PartyLegalEntity")[0];
          const id = partyId.getElementsByTagName("cbc:RegistrationName")[0];
          this.nameProveedor = id.textContent;
        } catch (error) {
          console.warn("No se pudo obtener el Nombre del proveedor:", error);
        }

        const invoiceLines = xmlDoc.getElementsByTagName("cac:InvoiceLine");
        const productos = Array.from(invoiceLines).map(item => {
          const descripcionTags = item.getElementsByTagName("cbc:Description");
          const cantidadTags = item.getElementsByTagName(
            "cbc:InvoicedQuantity"
          );
          const pricingReferenceTags = item.getElementsByTagName(
            "cac:PricingReference"
          );
          let precioUnitario = "";

          if (pricingReferenceTags.length > 0) {
            const priceAmountTags = pricingReferenceTags[0].getElementsByTagName(
              "cbc:PriceAmount"
            );
            if (priceAmountTags.length > 0) {
              precioUnitario = priceAmountTags[0].textContent;
            }
          }

          return {
            descripcion:
              descripcionTags.length > 0 ? descripcionTags[0].textContent : "",
            cantidad:
              cantidadTags.length > 0 ? cantidadTags[0].textContent : "",
            precioUnitario: precioUnitario
          };
        });
        this.productos = productos;
        console.log("Productos:", productos);
        this.validarExisteProductos();
        // Enviar al backend si es necesario
        // this.registrarIngreso({ rucProveedor, productos });
      };

      reader.readAsText(file);
    },
    validarExisteProductos() {
      this.productos.forEach(async element => {
        console.log(element.descripcion);
        element.IdProducto = "";
        element.CodigoBarra = "";
        element.Producto = element.descripcion;
        element.PrecioCosto = element.precioUnitario;
        let res = await this.axios
          .get(`${this.API}buscarproductos/nombrexml`, {
            params: {
              Producto: element.descripcion
            }
          })
          .then(res => {
            return res.data;
          });
        if (res) {
          element.IdProducto = res.IdProducto;
          element.CodigoBarra = res.CodigoBarra;
        }
        this.lstProductos.push(element);
      });
    },
    registrarIngreso(data) {
      // Usa axios o fetch para enviar al backend Laravel
      // axios.post('/api/ingresos', data)
      console.log("Registrando ingreso...", data);
    }
  },
  computed: {
    mostrarAdvertencia() {
      let val = this.lstProductos.some(v => !v.CodigoBarra);
      return val;
    }
  }
};
</script>
