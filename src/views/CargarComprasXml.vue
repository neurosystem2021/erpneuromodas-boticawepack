<template>
    <b-container>
        <b-row>
            <b-col cols="12">
                <b-form-file @change="leerXML" accept=".xml" > </b-form-file>
            </b-col>
            <b-col cols="4">
                Proveedor:
            </b-col>
            <b-col cols="4">
                {{ rucProveedor }} - {{ nameProveedor }}
            </b-col>
            <b-col cols="12" width="100%">
               <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Existe</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Total</th>
                    </tr>
                </thead>
               </table>
            </b-col>
        </b-row>
        
      <!-- <input type="file" /> -->
    </b-container>
  </template>
  
  <script>
export default {
  data() {
    return {
      rucProveedor: "",
      nameProveedor: "",
      productos: []
    };
  },
  methods: {
    leerXML(e) {
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
        this.productos = productos
        console.log("RUC Proveedor:", this.rucProveedor);
        console.log("Nombre Proveedor:", this.nombreProveedor);
        console.log("Productos:", productos);

        // Enviar al backend si es necesario
        // this.registrarIngreso({ rucProveedor, productos });
      };

      reader.readAsText(file);
    },
    registrarIngreso(data) {
      // Usa axios o fetch para enviar al backend Laravel
      // axios.post('/api/ingresos', data)
      console.log("Registrando ingreso...", data);
    }
  }
};
</script>
