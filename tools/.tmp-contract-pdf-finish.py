from pathlib import Path
p=Path('integration/proforma-edit/proforma-contract-core.php');s=p.read_text(encoding='utf-8')
s=s.replace('margin:17px 0 7px;page-break-after:avoid','margin:12px 0 6px;page-break-after:avoid').replace('.signatures{margin-top:38px;', '.signatures{margin-top:24px;').replace('.signatures td{width:50%;padding:15px;', '.signatures td{width:50%;padding:10px;').replace('.services td{width:50%;padding:6px;', '.services td{width:50%;padding:4px;line-height:1.4;').replace('.modules td{padding:6px;', '.modules tr{page-break-inside:avoid}.modules ul{margin:3px 0;padding-left:13px}.modules li{margin:0 0 2px}.modules td{padding:6px;')
needle="qpEscape($m['description']).'</td></tr>';"
replacement="qpEscape($m['description']).(!empty($m['features'])?'<ul><li>'.implode('</li><li>',array_map('qpEscape',$m['features'])).'</li></ul>':'').'</td></tr>';"
assert needle in s;s=s.replace(needle,replacement,1);p.write_text(s,encoding='utf-8')
