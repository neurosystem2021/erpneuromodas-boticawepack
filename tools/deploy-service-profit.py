from pathlib import Path
import re, shutil, datetime, sys
root=Path(__file__).resolve().parent.parent
route="""$app->get('/reporte/utlidadbruta/usuario', function (Request $request, Response $response, array $args) {
    require_once __DIR__ . '/service-profit-report.php';
    try {
        return $response->withJson(serviceProfitReport($this->db, $request->getQueryParams()));
    } catch (InvalidArgumentException $error) {
        return $response->withJson(array('error' => $error->getMessage()), 422);
    }
});"""
pattern=r"\$app->get\('/reporte/utlidadbruta/usuario',.*?\n\}\);"
source=root/'integration/imceden/backend/api/index.php'
s=source.read_text(encoding='utf-8-sig');s,n=re.subn(pattern,lambda m:route,s,count=1,flags=re.S)
if n!=1:raise RuntimeError('Report route not found')
source.write_text(s,encoding='utf-8')
shutil.copy2(root/'integration/service-profit/report.php',source.parent/'service-profit-report.php')
if '--deploy' in sys.argv:
    live=Path('C:/xampp/htdocs/boticav1');backup=live/('.service-profit-backup-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'));backup.mkdir()
    api=live/'api/index.php';s=api.read_text(encoding='utf-8-sig');s,n=re.subn(pattern,lambda m:route,s,count=1,flags=re.S)
    if n!=1:raise RuntimeError('Live report route not found')
    shutil.copy2(api,backup/'api-index.php');api.write_text(s,encoding='utf-8')
    shutil.copy2(root/'integration/service-profit/report.php',live/'api/service-profit-report.php')
    header=live/'views/header.php';s=header.read_text(encoding='utf-8-sig');shutil.copy2(header,backup/'header.php')
    s=s.replace('REPORTE U. BRUTA POR USUARIO','UTILIDAD BRUTA SERVICIOS / USUARIO');header.write_text(s,encoding='utf-8')
    print('Report deployed. Backup:',backup)
