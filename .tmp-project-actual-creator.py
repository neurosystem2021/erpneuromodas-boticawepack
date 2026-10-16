from pathlib import Path
p=Path(r'C:\xampp\htdocs\boticav1\api\index.php');s=p.read_text(encoding='utf-8');a=s.index("$app->post('/producto/registro_proyecto'");b=s.index('// SELECT SUBSTRING',a);part=s[a:b]
start=part.index("        $vendedor = 'xx';");end=part.index('        $sql =',start)
part=part[:start]+'''        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $usuarioReg=$_SESSION['user'] ?? null;
        if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
        if (!is_string($usuarioReg) || trim($usuarioReg)==='') return $response->withJson(array('error'=>'Inicie sesión nuevamente.'),401);

'''+part[end:];s=s[:a]+part+s[b:]
a=s.index("$app->get('/reporte/proyectos'");b=s.index("$app->delete('/proforma/anular/",a);part=s[a:b];needle='SELECT Ve_PreOrden.*,';assert needle in part;part=part.replace(needle,"""SELECT Ve_PreOrden.*,
    CASE WHEN Ve_PreOrden.UsuarioReg IS NULL OR TRIM(Ve_PreOrden.UsuarioReg)='' OR LOWER(TRIM(Ve_PreOrden.UsuarioReg))='xx' THEN NULL
    ELSE COALESCE((SELECT NULLIF(u.NombreUsuario,'') FROM Seg_Usuario u WHERE u.Usuario=Ve_PreOrden.UsuarioReg LIMIT 1),Ve_PreOrden.UsuarioReg) END AS UsuarioCreador,""",1);s=s[:a]+part+s[b:];p=Path('integration/project-real-creator/api/index.php');p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
p=Path('src/views/reportes/ReporteProyectos.vue');s=p.read_text(encoding='utf-8').replace('slot="UsuarioReg"','slot="UsuarioCreador"',1).replace("data.item.UsuarioReg || 'Sin registro'","data.item.UsuarioCreador || 'Sin registro histórico'",1).replace('key: "UsuarioReg", label: "Creado por"','key: "UsuarioCreador", label: "Creado por"',1).replace('productoSelect.UsuarioReg || "Sin registro"','productoSelect.UsuarioCreador || "Sin registro histórico"',1);p.write_text(s,encoding='utf-8')
