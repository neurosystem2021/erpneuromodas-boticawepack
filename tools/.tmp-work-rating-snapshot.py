from pathlib import Path
p=Path('integration/helpdesk/support-service.php');s=p.read_text(encoding='utf-8')
s=s.replace('support_satisfaction_surveys(tenant_id,ticket_id,respondent_id,cycle,solved,speed,treatment,solution,recommend,comment,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)', 'support_satisfaction_surveys(tenant_id,ticket_id,respondent_id,cycle,solved,speed,treatment,solution,recommend,comment,created_at,technician_id) VALUES(?,?,?,?,?,?,?,?,?,?,?,?)')
s=s.replace("supportText($p['comment']??'',3000,false),$now]);supportAudit", "supportText($p['comment']??'',3000,false),$now,$t['technician_id']]);supportAudit",1)
p.write_text(s,encoding='utf-8')
p=Path('integration/helpdesk/support-activity.php');s=p.read_text(encoding='utf-8')
s=s.replace("m.role='technician' AND a.technician_id=?", "m.role='technician' AND COALESCE(s.technician_id,a.technician_id)=?",1).replace("$scope.=' AND a.technician_id=?'", "$scope.=' AND COALESCE(s.technician_id,a.technician_id)=?'",1)
s=s.replace('LEFT JOIN support_principals p ON p.id=a.technician_id WHERE', 'LEFT JOIN support_principals p ON p.id=COALESCE(s.technician_id,a.technician_id) WHERE',1)
p.write_text(s,encoding='utf-8')
