from pathlib import Path
p=Path('deploy-src/store-order-fix/api.php');s=Path('C:/xampp/htdocs/boticav1/api/tienda/index.php').read_text(encoding='utf-8');s=s.replace("'logo_principal','nombre_comercial','actividad_comercial'", "'logo_principal','nombre_comercial','actividad_comercial','whatsapp_ventas'");p.write_text(s,encoding='utf-8')
