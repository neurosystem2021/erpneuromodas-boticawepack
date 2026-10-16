from pathlib import Path
import json,re,subprocess
root=Path.cwd();s=json.loads((root/'integration/imceden/api-source.json').read_text(encoding='utf-8-sig'));c=json.loads((root/'integration/imceden/api-current.json').read_text(encoding='utf-8-sig'))
print('extracted',len(s['routes']),len(s['functions']))
want=['/productos/puntoventa/stock','/productos/codigo-siguiente/','/reporte/movimientos/dashboard','/venta/proforma/generar_pdf','/ventas-app/modulos','/cierrecaja/resumen-pdf','/correo/']
routes={k:v for k,v in s['routes'].items() if k not in c['routes'] and any(x in k for x in want)}
# Copy missing helpers referenced by the selected routes and KPI, recursively.
needed='\n'.join(routes.values())+(root/'integration/imceden/backend/api/kpi.php').read_text(encoding='utf-8-sig');funcs={}
while True:
 new={k:v for k,v in s['functions'].items() if k not in c['functions'] and k not in funcs and re.search(r'\b'+re.escape(k)+r'\s*\(',needed)}
 if not new:break
 funcs.update(new);needed+='\n'+'\n'.join(new.values())
output='<?php\nuse \\Psr\\Http\\Message\\ServerRequestInterface as Request;\nuse \\Psr\\Http\\Message\\ResponseInterface as Response;\nuse PhpOffice\\PhpSpreadsheet\\Spreadsheet;\nuse PhpOffice\\PhpSpreadsheet\\Writer\\Xlsx;\n'
for k,v in funcs.items():output+="\nif (!function_exists('"+k+"')) {\n"+v+'\n}\n'
output+='\n'+'\n\n'.join(routes.values())+"\nrequire_once __DIR__.'/kpi.php';\n"
(root/'integration/imceden/backend/api/imceden-modules.php').write_text(output,encoding='utf-8')
print('routes',list(routes));print('helpers',list(funcs))
