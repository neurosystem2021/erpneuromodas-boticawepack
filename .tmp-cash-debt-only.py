from pathlib import Path
import re
p=Path('src/views/CajaBancos.vue');s=p.read_text(encoding='utf-8')
s,n=re.subn(r'\{\{\s*parseFloat\(\s*lstDeudas\.reduce\([\s\S]*?\)\.toFixed\(2\)\s*\}\}', '{{ totalDocumentosPendientes }}',s);assert n==1,n
s,n=re.subn(r'\{\{\s*parseFloat\(\s*lstAplicado\.reduce\(\(sum,deuda\)=>\{\s*return sum \+ parseFloat\(deuda\.Aplicado\)[\s\S]*?\)\.toFixed\(2\)\s*\}\}', '{{ totalDocumentosAplicados }}',s);assert n==1,n
s,n=re.subn(r'\{\{\s*parseFloat\(\s*lstAplicado\.reduce\(\(sum,deuda\)=>\{\s*return sum \+ parseFloat\(deuda\.Saldo\)[\s\S]*?\)\.toFixed\(2\)\s*\}\}', '{{ totalSaldoDocumentos }}',s);assert n==1,n
s=s.replace('<p v-if="(Number(IdTipoCajaBanco) === 1 || Number(IdTipoCajaBanco) === 2) && !lstAplicado.length" class="text-muted small">Sin documentos aplicados: se registrará como movimiento a cuenta.</p>', '<p v-if="(Number(IdTipoCajaBanco) === 1 || Number(IdTipoCajaBanco) === 2) && !lstAplicado.length" class="text-muted small">Seleccione una deuda pendiente y aplique el importe para habilitar Guardar.</p>')
s=s.replace('if ([1, 2].includes(Number(operation.IdTipoCajaBanco))) {', 'if ([1, 2].includes(Number(operation.IdTipoCajaBanco))) {\n        if (!this.lstAplicado.length) return true;',1)
s=s.replace('  computed: {', '''  computed: {
    totalDocumentosPendientes() { return (this.lstDeudas || []).reduce((sum, item) => sum + (Number(item.Total) || 0), 0).toFixed(2); },
    totalDocumentosAplicados() { return (this.lstAplicado || []).reduce((sum, item) => sum + (Number(item.Aplicado) || 0), 0).toFixed(2); },
    totalSaldoDocumentos() { return (this.lstAplicado || []).reduce((sum, item) => sum + (Number(item.Saldo) || 0), 0).toFixed(2); },''',1)
for name in ['setProveedor','setCliente']:
 s=s.replace('    '+name+'(obj) {', '    '+name+'(obj) {\n      this.lstDeudas = [];\n      this.lstAplicado = [];\n      this.bloquearAplicar = false;\n      this.errorMovimiento = "";',1)
p.write_text(s,encoding='utf-8')
p=Path('integration/cash-save/api/cash-movement-save.php');s=p.read_text(encoding='utf-8');s=s.replace('    $db = $this->db;', '    if (session_status() !== PHP_SESSION_ACTIVE) session_start();\n    $db = $this->db;',1)
s=s.replace('    $appliedTotal=0;', "    if(in_array($idTipo,array(1,2),true)&&!count($aplicados))return $response->withJson(array('error'=>'Debe aplicar el pago a una deuda pendiente antes de guardar.'),422);\n    $appliedTotal=0;",1)
s=s.replace('    try {\n        $db->beginTransaction();', "    if(in_array($idTipo,array(1,2),true)&&abs(round($appliedTotal,2)-round($importe,2))>0.001)return $response->withJson(array('error'=>'El importe debe coincidir con el total aplicado a los documentos.'),422);\n    try {\n        $db->beginTransaction();",1)
p.write_text(s,encoding='utf-8')
