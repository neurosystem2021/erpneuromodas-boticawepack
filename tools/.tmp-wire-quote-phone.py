from pathlib import Path
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8')
needle='<proforma-presentacion v-model="quotePresentation"'
assert needle in s;s=s.replace(needle,'''<cliente-telefono-rapido v-if="quotePresentationOpen &amp;&amp; quotePresentation.tipo==='sistemas'" :client-id="ventas[index] &amp;&amp; ventas[index].cliente ? ventas[index].cliente.IdCliente : 0" required :disabled="inProgress" @status="quotePhoneReady=$event" @loaded="quotePhoneUpdated" @saved="quotePhoneUpdated"/><proforma-presentacion v-model="quotePresentation"''',1)
s=s.replace("(quotePresentation.tipo==='sistemas' &amp;&amp; !quotePresentation.alcance.trim())", "(quotePresentation.tipo==='sistemas' &amp;&amp; (!quotePresentation.alcance.trim() || !quotePhoneReady))",1)
s=s.replace('import ProformaPresentacion from "@/components/ProformaPresentacion.vue";', 'import ProformaPresentacion from "@/components/ProformaPresentacion.vue";\nimport ClienteTelefonoRapido from "@/components/ClienteTelefonoRapido.vue";')
s=s.replace('quotePresentationOpen:false,','quotePhoneReady:false,quotePresentationOpen:false,',1)
s=s.replace('    async guardarProforma() {', "    quotePhoneUpdated(client){const sale=this.ventas[this.index];if(sale&&sale.cliente&&Number(sale.cliente.IdCliente)===Number(client.IdCliente))this.$set(sale.cliente,'Telefono',client.Telefono);},\n    async guardarProforma() {\n      if(this.quotePresentation.tipo==='sistemas'&&!this.quotePhoneReady){this.$toasted.error('Guarda un teléfono válido del cliente antes de crear la proforma de sistemas.');return;}",1)
s=s.replace('    ProformaPresentacion,','    ProformaPresentacion,\n    ClienteTelefonoRapido,',1);p.write_text(s,encoding='utf-8')
p=Path('src/views/ProformaEditor.vue');s=p.read_text(encoding='utf-8')
s=s.replace('  <b-form-group label="Buscar productos', '''  <cliente-telefono-rapido v-if="quotePresentation.tipo==='sistemas'" :client-id="quote.IdCliente" required :disabled="busy" @status="phoneReady=$event" @loaded="phoneUpdated" @saved="phoneUpdated"/>
  <b-form-group label="Buscar productos''',1)
s=s.replace('import ProformaPresentacion from "@/components/ProformaPresentacion.vue";', 'import ProformaPresentacion from "@/components/ProformaPresentacion.vue";\nimport ClienteTelefonoRapido from "@/components/ClienteTelefonoRapido.vue";')
s=s.replace('components:{ProformaPresentacion}', 'components:{ProformaPresentacion,ClienteTelefonoRapido}').replace('return {quote:{}', 'return {phoneReady:false,quote:{}',1)
s=s.replace(' methods:{', " methods:{\n  phoneUpdated(client){this.$set(this.quote,'ClienteTelefono',client.Telefono);},",1)
s=s.replace("async save(){if(this.busy)return;", "async save(){if(this.busy)return;if(this.quotePresentation.tipo==='sistemas'&&!this.phoneReady){this.error='Guarda un teléfono válido del cliente antes de guardar la proforma de sistemas.';return;}",1)
p.write_text(s,encoding='utf-8')
