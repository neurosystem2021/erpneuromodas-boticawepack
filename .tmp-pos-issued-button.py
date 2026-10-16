from pathlib import Path
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8');needle='<b-container class="body pos-page" fluid>'
s=s.replace(needle,needle+'''
  <div class="pos-issued-documents">
    <button type="button" class="pos-issued-button" title="Comprobantes emitidos" aria-label="Abrir comprobantes emitidos" @click="showIssuedDocuments = true"><i class="fas fa-file-invoice" aria-hidden="true"></i><span>Comprobantes emitidos</span></button>
    <b-modal v-model="showIssuedDocuments" title="Comprobantes emitidos" size="xl" hide-footer modal-class="nst-issued-modal">
      <venta-reimpresion v-if="showIssuedDocuments" />
    </b-modal>
  </div>''',1)
s=s.replace('import ProductosCarrito from', 'import VentaReimpresion from "@/views/VentaReimpresion.vue";\nimport ProductosCarrito from',1)
s=s.replace('pedidosWebPendientes: 0', 'showIssuedDocuments: false,\n      pedidosWebPendientes: 0',1)
assert 'showIssuedDocuments: false' in s
s=s.replace('    ProductosCarrito,', '    VentaReimpresion,\n    ProductosCarrito,',1)
p.write_text(s,encoding='utf-8')
p=Path('src/views/VentaReimpresion.vue');s=p.read_text(encoding='utf-8');s=s.replace('<b-container class="bv-example-row">', '''<b-container class="bv-example-row">
      <header class="issued-receipts-hero"><div><small>VENTAS / DOCUMENTOS</small><h2>Reimpresión de comprobantes</h2><p>Consulta e imprime tus documentos emitidos.</p></div><span><i class="fas fa-file-alt" aria-hidden="true"></i> {{ totalRows }} documentos</span></header>''',1)
s=s.replace('placeholder="Buscar"','placeholder="ID, correlativo, cliente o documento"',1)
s=s.replace("+ '&preview=1'", "")
s=s.replace('<a class="btn btn-success" target="_blank"', '<a class="btn btn-success" target="_blank" rel="noopener"',1)
p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8');s+='''
.pos-page>.pos-issued-documents{position:relative;z-index:1100!important}
.pos-issued-button{position:fixed;right:20px;bottom:24px;z-index:950;display:flex;align-items:center;gap:9px;min-width:46px;min-height:46px;border:1px solid #ffffff66;border-radius:50px;padding:12px 16px;background:linear-gradient(135deg,#079b72,#11b886);color:#fff;font-size:12px;font-weight:800;box-shadow:0 6px 20px #087d7040;cursor:pointer}
.pos-issued-button i{font-size:18px}.pos-issued-button:hover{background:#078b66}.pos-issued-button:focus-visible{outline:3px solid #a6e9d3;outline-offset:3px}
.nst-issued-modal.modal{z-index:1110!important}.nst-issued-modal .modal-dialog{max-width:1450px;width:calc(100% - 36px);margin:24px auto}.nst-issued-modal .modal-content{max-height:calc(100dvh - 48px);border:0;border-radius:20px;overflow:hidden;box-shadow:0 18px 60px #12384950}.nst-issued-modal .modal-header{padding:17px 22px;background:#073e35;color:white;border:0}.nst-issued-modal .modal-title{font-size:20px;font-weight:800}.nst-issued-modal .close{color:white;opacity:1}.nst-issued-modal .modal-body{overflow-y:auto;background:#f0f7f8;padding:18px}.nst-issued-modal .nst-screen--ventareimpresion{padding:0!important}.nst-issued-modal .nst-screen--ventareimpresion>.container{max-width:100%;padding:0}.nst-issued-modal .table{font-size:12px}.nst-issued-modal .table td{vertical-align:middle}.nst-issued-modal .table .btn-success{white-space:nowrap;background:#06a777!important;border:0;border-radius:9px;font-size:12px;font-weight:800;padding:9px 12px}.nst-issued-modal .table th{background:#0d5b50!important;color:white}.nst-issued-modal .form-control,.nst-issued-modal .custom-select{min-height:43px;border-radius:11px;background:#f8fbfe;border-color:#d5e5f0}
.issued-receipts-hero{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:17px;padding:22px 25px;background:#073e35;color:white;border-radius:16px}.issued-receipts-hero small{font-weight:800;font-size:10px;letter-spacing:1px;color:#c9e6df}.issued-receipts-hero h2{margin:7px 0;color:#fff!important;font-size:23px;font-weight:800}.issued-receipts-hero p{margin:0;font-size:13px;color:#e0f5ef}.issued-receipts-hero>span{padding:12px 15px;border:1px solid #ffffff44;border-radius:12px;white-space:nowrap;background:#ffffff15;font-size:13px}
@media(max-width:767.98px){.pos-issued-button{right:12px;bottom:calc(132px + env(safe-area-inset-bottom));width:44px;height:44px;min-height:44px;padding:0;justify-content:center}.pos-issued-button span{display:none}.nst-issued-modal .modal-dialog{width:calc(100% - 12px);margin:6px auto}.nst-issued-modal .modal-content{max-height:calc(100dvh - 12px);border-radius:14px}.nst-issued-modal .modal-body{padding:10px}.issued-receipts-hero{padding:16px;flex-wrap:wrap}.issued-receipts-hero h2{font-size:19px}.issued-receipts-hero>span{padding:6px 9px;font-size:11px}}
@media print{.pos-issued-button{display:none!important}}
''';p.write_text(s,encoding='utf-8')
