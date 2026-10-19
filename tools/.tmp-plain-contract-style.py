from pathlib import Path
root=Path(__file__).resolve().parent.parent
f=root/'integration/proforma-edit/proforma-contract-template.php';s=f.read_text(encoding='utf-8')
old=' </style></head><body>'
new=''' .contract-plain{background:#fff}.contract-plain .document-header,.contract-plain .reference,.contract-plain .reference td,.contract-plain .parties,.contract-plain .parties td,.contract-plain .clause h2,.contract-plain .annex h2,.contract-plain .payment,.contract-plain .payment td,.contract-plain .note,.contract-plain .signature-name,.contract-plain .annex-head,.contract-plain .solution,.contract-plain .module,.contract-plain .annual,.contract-plain .services td,.contract-plain .footer,.contract-plain .accounts,.contract-plain .context-block,.contract-plain .context-intro{background:transparent!important;border:0!important;border-radius:0!important;box-shadow:none!important}
 </style></head><body class="contract-plain">'''
assert s.count(old)==1
s=s.replace(old,new)
old="qcSignaturesHtml($f,$data['consultantSignature']??null)"
assert old in s
s=s.replace(old,'qcSignaturesHtml($f,qcDisplaySignature($data))')
s+='''
function qcDisplaySignature($data){
 $saved=$data['consultantSignature']??null;if(!$saved)return null;
 // Presentation-only cleanup explicitly requested for the supplied original image.
 // The stored agreement and the original image snapshot remain unchanged.
 $original=dirname(__DIR__).'/nuevo/static/images/consultor-jhon-rubrica-original.png';if(!is_file($original))$original=dirname(__DIR__,2).'/static/images/consultor-jhon-rubrica-original.png';
 if(is_file($original)&&is_string($saved['sha256']??null)&&hash_equals(hash_file('sha256',$original),$saved['sha256'])){
  $clean=qpConsultantRubric($data['form']['consultor']??'');if($clean)return $clean;
 }
 return $saved;
}
'''
f.write_text(s,encoding='utf-8',newline='\n')
print('Contract decorative backgrounds and borders removed; known old rubric cleaned only when rendering, with stored agreement preserved.')
