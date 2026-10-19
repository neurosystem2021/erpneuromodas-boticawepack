from pathlib import Path
p=Path('src/views/reportes/Proforma.vue');s=p.read_text(encoding='utf8').replace('proforma-header-complete.png','proforma-header-wide-v3.png')
s=s.replace('grid-template-columns:190px minmax(0,1fr);gap:7px 20px','grid-template-columns:170px minmax(0,1fr);gap:7px 12px').replace('height:110px;object-fit:contain','height:130px;object-fit:contain').replace('width:190px;max-height:80px','width:170px;max-height:75px')
s=s.replace('.quote-sheet{width:100%;max-width:none;padding:0!important;box-shadow:none;border-radius:0}', '.quote-sheet{width:100%;max-width:none;padding:10mm!important;box-shadow:none;border-radius:0}')
s=s.replace('grid-template-columns:43mm minmax(0,1fr);gap:2mm 4mm','grid-template-columns:38mm minmax(0,1fr);gap:2mm 3mm').replace('.quote-art{height:25mm}', '.quote-art{height:30mm}').replace('.quote-logo{width:43mm;max-height:20mm}', '.quote-logo{width:38mm;max-height:19mm}')
s=s.replace('@page{size:A4;margin:12mm!important}', '@page{size:A4;margin:2mm!important}')
p.write_text(s,encoding='utf8')
