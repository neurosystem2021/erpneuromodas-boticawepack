export function readPrintSettings() {
 let saved = {};
 try { saved = JSON.parse(localStorage.getItem('posPrintSettings') || '{}'); } catch (e) { /* defaults */ }
 const printer = typeof saved.printer === 'string' && /^[^\x00-\x1f\x7f]{1,100}$/.test(saved.printer) ? saved.printer.trim() : 'bar';
 return { printer: printer || 'bar', mode: saved.mode === 'browser' ? 'browser' : 'windows', automatic: saved.automatic !== false };
}
