from pathlib import Path
p=Path('integration/proforma-edit/proforma-pdf-render.php');s=p.read_text(encoding='utf-8');s=s.replace('.heading small{font-size:12px;color:#b51f32}', '.heading small{font-size:12px;color:#b51f32;white-space:nowrap;display:block;text-align:right;margin-top:4px}')
s=s.replace('.context-items span{color:', '.context-single td{width:100%}.context-items span{color:');p.write_text(s,encoding='utf-8')
