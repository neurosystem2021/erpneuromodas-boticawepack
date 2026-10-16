import {readPrintSettings} from '@/utils/printSettings';
// IMCEDEN transport: Windows spooler -> configured BAR queue (USB or TCP/IP).
export default {
 methods: {
  async enviarImpresionVenta(id, ofertas = '') {
   if (!/^\d+$/.test(String(id)) || Number(id) <= 0) return false;
   const settings = readPrintSettings();
   if (!settings.automatic) return false;
   const key = 'pos-direct-print:' + id;
   this._salePrintPending = this._salePrintPending || {};
   if (this._salePrintPending[key]) return false;
   try { if (sessionStorage.getItem(key)) return false; } catch (e) { /* storage can be disabled */ }
   this._salePrintPending[key] = true;
   try {
    if (settings.mode === 'browser') { this.imprimirComprobanteRespaldo(id, ofertas); return true; }
    const response = await this.axios.post('/imprimir/usb.php', { IdDocVenta: Number(id), PrinterName: settings.printer }, { timeout: 20000, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!response.data || response.data.ok !== true) throw new Error('Print not acknowledged');
    try { sessionStorage.setItem(key, 'sent'); } catch (e) { /* in-memory guard remains */ }
    this.$toasted.success('Comprobante enviado a ' + settings.printer, { duration: 2500 });
    return true;
   } catch (e) {
    // Registration succeeded; never repeat payment or blindly retry a timed-out print.
    try { sessionStorage.setItem(key, 'review'); } catch (ignored) { /* memory guard remains */ }
    this.$toasted.show('Venta realizada. Revisa la impresión; se abrirá el comprobante de respaldo.', { duration: 4000 });
    this.imprimirComprobanteRespaldo(id, ofertas);
    return false;
   }
  },
  imprimirComprobanteRespaldo(id, ofertas = '') {
   const prior = document.getElementById('pos-ticket-print-frame');
   if (prior) prior.remove();
   const frame = document.createElement('iframe');
   frame.id = 'pos-ticket-print-frame';
   frame.title = 'Comprobante de venta';
   Object.assign(frame.style, { position: 'fixed', width: '1px', height: '1px', border: '0', opacity: '0' });
   frame.src = '/imprimir/index.php?IdDocVenta=' + encodeURIComponent(id) + '&IdOfertas=' + encodeURIComponent(ofertas) + '&popup=1';
   frame.onload = () => { try { frame.contentWindow.addEventListener('afterprint', () => frame.remove(), { once: true }); } catch (e) { /* manual reprint available */ } };
   document.body.appendChild(frame);
  }
 }
};
