from pathlib import Path
p=Path('src/views/reportes/CierreCaja.vue')
s=p.read_text(encoding='utf-8')
s=s.replace('import { isMainCashMovement', 'import { yapePaymentTicket } from "@/utils/yapePaymentTicket";\nimport { isMainCashMovement',1)
s=s.replace('<h5>Detalle de Ventas Pagadas con Yape/Plin</h5>','<h5>Detalle de Ventas Pagadas con Yape/Plin</h5>\n                <b-button variant="info" class="mb-3" @click="imprimirYapeTicket"><i class="fas fa-print" aria-hidden="true"></i> Ticket Yape/Plin 80 mm</b-button>',1)
a=s.index('<h5>Detalle de Ventas Pagadas con Yape/Plin</h5>');b=s.index('</b-tab>',a)
part=s[a:b].replace('<th>Estado</th>','<th>Estado</th><th>Operación</th>').replace('<td>{{ estadoVenta(item) }}</td>','<td>{{ estadoVenta(item) }}</td><td>{{ item.YapeDesc || "Sin operación" }}</td>').replace('colspan="5"','colspan="6"')
s=s[:a]+part+s[b:]
method='''    async imprimirYapeTicket() {
      const popup = window.open('', '_blank', 'width=440,height=720');
      if (!popup) { this.$toasted.error('Permita las ventanas emergentes para imprimir el ticket.'); return; }
      popup.document.write('<p>Preparando reporte Yape/Plin...</p>');
      try {
        const response = await this.axios.get(this.API + 'empresa/id/' + (sessionStorage.getItem('empresa') || 1));
        const html = yapePaymentTicket({company:response.data || {},sales:this.ventas,seller:this.vendedor.NombreUsuario || this.vendedor.Usuario || this.IdVendedor,date:this.cierre.FechaReg || this.cierre.Fecha || new Date().toLocaleString('es-PE')});
        popup.document.open(); popup.document.write(html); popup.document.close();
        popup.focus(); setTimeout(() => { if (!popup.closed) popup.print(); },250);
      } catch (error) { popup.close(); this.$toasted.error('No se pudo preparar el reporte Yape/Plin.'); }
    },
'''
s=s.replace('    async imprimirTicket(productDetail = false) {',method+'    async imprimirTicket(productDetail = false) {',1)
p.write_text(s,encoding='utf-8')
