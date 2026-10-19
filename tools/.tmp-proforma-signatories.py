from pathlib import Path

root = Path(__file__).resolve().parent.parent
def edit(path, replacements):
    file = root / path
    text = file.read_text(encoding='utf-8')
    for old, new in replacements:
        if old == '</style>':
            assert old in text
            start, end = text.rsplit(old, 1)
            text = start + new + end
            continue
        assert text.count(old) == 1, (path, old[:100], text.count(old))
        text = text.replace(old, new)
    file.write_text(text, encoding='utf-8', newline='\n')

edit('src/views/reportes/Proforma.vue', [
    ('<span><b>Asesor</b> {{ proforma.UsuarioReg }}</span>', '<span><b>Consultor</b> {{ consultantName }}</span>'),
    ('<div class="quote-signature">Firma / sello de autorización</div>', '<div class="quote-signature"><div class="quote-rubric-space" aria-label="Espacio para la rúbrica del consultor"></div><strong>{{consultantName}}</strong><span>CONSULTOR</span><small>Firma / rúbrica</small></div>'),
    ("import {proformaAnnualPlan} from '@/utils/proformaServices';", "import {proformaAnnualPlan} from '@/utils/proformaServices';\nimport {DEFAULT_CONSULTANT} from '@/utils/proformaSignatories';"),
    ('      contractOpen:false,editOpen:false,', '      contractOpen:false,editOpen:false,consultantName:DEFAULT_CONSULTANT,'),
    ('</style>', '.quote-footer .quote-signature{border-top:0;padding-top:0;text-align:center;flex:0 0 270px;max-width:100%;break-inside:avoid;page-break-inside:avoid}.quote-rubric-space{height:42px;border-bottom:1px solid #b58a97;margin-bottom:8px}.quote-signature strong{display:block;font-size:12px;line-height:1.4;color:#49333b}.quote-signature span{display:block;font-size:9px;letter-spacing:1.5px;color:#b21f38;margin-top:3px}.quote-signature small{display:block;font-size:9px;color:#8d6b78;margin-top:4px}@media screen and (max-width:650px){.quote-footer .quote-signature{flex-basis:auto;width:270px;margin-top:12px}}\n</style>'),
])
edit('src/components/ProformaContrato.vue', [
    ('<div class="contract-grid"><label>Representante del cliente<input v-model.trim="form.representanteCliente" maxlength="180" :disabled="busy" placeholder="Cuando corresponda"></label><label>Representante del proveedor<input v-model.trim="form.representanteProveedor" maxlength="180" :disabled="busy" placeholder="Nombre de quien firma por la empresa"></label></div>', '<div class="contract-grid"><label>Consultor *<input v-model.trim="form.consultor" required maxlength="180" :disabled="busy"></label><label>Gerente general *<input v-model.trim="form.representanteProveedor" required maxlength="180" :disabled="busy"></label></div>\n    <label>Representante legal del cliente<input v-model.trim="form.representanteCliente" maxlength="180" :disabled="busy" placeholder="Cuando corresponda; de lo contrario firma el cliente"></label>\n    <div class="contract-signatories" aria-label="Orden de firmas del contrato"><div><span>IZQUIERDA · CONSULTOR</span><strong>{{form.consultor}}</strong></div><div><span>CENTRO · CLIENTE / REPRESENTANTE LEGAL</span><strong>{{form.representanteCliente || form.cliente || \'Nombre del cliente\'}}</strong></div><div><span>DERECHA · GERENTE GENERAL</span><strong>{{form.representanteProveedor}}</strong></div></div>'),
    ('<script>\n', "<script>\nimport {DEFAULT_CONSULTANT,DEFAULT_MANAGER} from '@/utils/proformaSignatories';\n"),
    ("representanteCliente:'',representanteProveedor:'',fecha:today()", "representanteCliente:'',representanteProveedor:DEFAULT_MANAGER,consultor:DEFAULT_CONSULTANT,fecha:today()"),
    ('this.form=Object.assign({},this.form,data.contract.form);', 'this.form=Object.assign({},this.form,data.contract.form);this.form.consultor=this.form.consultor||DEFAULT_CONSULTANT;this.form.representanteProveedor=this.form.representanteProveedor||DEFAULT_MANAGER;'),
    ('</style>', '.contract-signatories{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;padding:13px 0;margin-bottom:12px;border-top:1px solid #efdee4;border-bottom:1px solid #efdee4}.contract-signatories div{min-width:0;text-align:center}.contract-signatories span{display:block;font-size:8px;letter-spacing:.5px;line-height:1.5;color:#a12942;margin-bottom:5px}.contract-signatories strong{font-size:11px;line-height:1.5;overflow-wrap:anywhere}@media(max-width:650px){.contract-signatories{grid-template-columns:1fr;gap:14px}}\n</style>'),
])
edit('integration/proforma-edit/proforma-pdf-render.php', [
    ('function qpMoney($v)', "function qpConsultantName(){return 'Ing. Jhon H. Medrano Martinez';}\nfunction qpManagerName(){return 'Blanca Martinez Bravo';}\nfunction qpMoney($v)"),
    ("'<br>Asesor: '.qpEscape($quote['UsuarioReg'])", "'<br>Consultor: '.qpEscape(qpConsultantName())"),
    ('<div class="signature">_____________________________<br>Firma / sello de autorización</div>', '<div class="signature consultant-signature"><div class="rubric-space"></div><b>\'.qpEscape(qpConsultantName()).\'</b><span>CONSULTOR</span><small>Firma / rúbrica</small></div>'),
    ('</style></head><body>', '.consultant-signature{width:220px;text-align:center;margin-left:auto;page-break-inside:avoid}.rubric-space{height:35px;border-bottom:1px solid #b58a97;margin-bottom:7px}.consultant-signature b{display:block;font-size:9px;color:#49333b}.consultant-signature span{display:block;font-size:7px;letter-spacing:1px;color:#b21f38;margin-top:3px}.consultant-signature small{display:block;font-size:7px;margin-top:3px}</style></head><body>'),
])
edit('integration/proforma-edit/proforma-contract-core.php', [
    ("$out=[];\n foreach(['AlcanceSistema'", "$out=[];$body+=['consultor'=>qpConsultantName(),'representanteProveedor'=>qpManagerName()];\n foreach(['AlcanceSistema'"),
    ("'representanteProveedor'=>180,'vigenciaLicencia'", "'representanteProveedor'=>180,'consultor'=>180,'vigenciaLicencia'"),
    ("foreach(['cliente','documento','direccion','vigenciaLicencia'", "foreach(['cliente','documento','direccion','consultor','representanteProveedor','vigenciaLicencia'"),
    ("($f['representanteProveedor']!==''?'<br>Representante: '.qpEscape($f['representanteProveedor']):'')", "($f['representanteProveedor']!==''?'<br>'.(isset($f['consultor'])?'Gerente general: ':'Representante: ').qpEscape($f['representanteProveedor']):'').(isset($f['consultor'])?'<br>Consultor: '.qpEscape($f['consultor']):'')"),
    ("($f['representanteCliente']!==''?'<br>Representante: '", "($f['representanteCliente']!==''?'<br>Representante legal: '"),
    ('</style></head><body>', '.signatures-three{table-layout:fixed}.signatures-three td{width:33.333%;padding:0 9px;font-size:8px;vertical-align:top}.signature-space{height:50px}.signatures-three .signature-name{font-size:9px;line-height:1.45;color:#45343b}.signature-role{display:block;font-size:7px;letter-spacing:.6px;color:#ab233b;margin:5px 0 4px}.signature-note{display:block;font-size:7px;color:#8a6f7c}</style></head><body>'),
    ("$html.='<table class=\"signatures\"><tr><td><b>POR EL PROVEEDOR</b>'", "$html.=array_key_exists('consultor',$f)?qcSignaturesHtml($f):'<table class=\"signatures\"><tr><td><b>POR EL PROVEEDOR</b>'"),
    ("</td></tr></table><div class=\"footer\">Contrato basado en la proforma ", "</td></tr></table>';\n $html.='<div class=\"footer\">Contrato basado en la proforma "),
])
file = root / 'integration/proforma-edit/proforma-contract-core.php'
with file.open('a', encoding='utf-8', newline='\n') as stream:
    stream.write('''
function qcSignaturesHtml($form){
 $signers=[[$form['consultor'],'CONSULTOR','Firma / rúbrica'],[$form['representanteCliente']?:$form['cliente'],$form['representanteCliente']!==''?'REPRESENTANTE LEGAL DEL CLIENTE':'CLIENTE','Firma y documento'],[$form['representanteProveedor'],'GERENTE GENERAL','Firma / sello del proveedor']];
 $html='<table class="signatures signatures-three" aria-label="Consultor, cliente y gerente general"><tr>';
 foreach($signers as $signer)$html.='<td><div class="signature-space"></div><b class="signature-name">'.qpEscape($signer[0]).'</b><span class="signature-role">'.qpEscape($signer[1]).'</span><span class="signature-note">'.qpEscape($signer[2]).'</span></td>';
 return $html.'</tr></table>';
}
''')
print('Updated proposal consultant and three contract signatories; existing saved contract versions retain their original signatories.')
