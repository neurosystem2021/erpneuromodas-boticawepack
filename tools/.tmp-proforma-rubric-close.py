from pathlib import Path
f=Path(__file__).resolve().parent.parent/'integration/proforma-edit/proforma-pdf-render.php';s=f.read_text(encoding='utf-8')
for old,new in [
 ('<div class="footer">Gracias por confiar', '<table class="quote-close"><tr><td width="55%"><div class="footer">Gracias por confiar'),
 ('</div><div class="signature consultant-signature">', '</div></td><td width="45%"><div class="signature consultant-signature">'),
 ('<small>Firma / rúbrica</small></div></body>', '<small>Firma / rúbrica</small></div></td></tr></table></body>'),
 ('</style></head><body>', '.quote-close{border-top:1px solid #ead8dd;margin-top:12px;page-break-inside:avoid}.quote-close td{vertical-align:top;padding-top:8px}.quote-close .footer{border:0;margin:0;padding:0}.quote-close .signature{margin:0;width:100%}.quote-close .rubric-space{height:66px}.quote-close .consultant-rubric-image{width:132px;max-height:64px}</style></head><body>')
]:
 assert s.count(old)==1,(old[:50],s.count(old))
 s=s.replace(old,new)
f.write_text(s,encoding='utf-8',newline='\n')
