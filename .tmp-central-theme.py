from pathlib import Path
root=Path('integration/central-config');p=root/'resources/css/central-config.css';p.parent.mkdir(parents=True,exist_ok=True);p.write_text('''/* Presentation shared by the five central configuration modules. */
.ec-hero,.vg-hero,.tag-hero,.pm-hero{background:#073e35!important;color:#fff!important}
.ec-hero h1,.vg-hero h1,.tag-hero h1,.pm-hero h1{color:#fff!important;font-weight:900!important}
.ec-hero p,.vg-hero p,.tag-hero p,.pm-hero p{color:#e0f5ef!important}
.ec-page,.vg-page,.tag-page,.pm-page{padding-bottom:25px}
.ec-page input,.vg-page input,.tag-page input,.pm-page select{font-size:13px}
@media(max-width:600px){.ec-hero,.vg-hero,.tag-hero,.pm-hero{border-radius:14px}.ec-hero h1,.vg-hero h1,.tag-hero h1,.pm-hero h1{font-size:21px!important}}
''',encoding='utf-8')
for name in ['EmpresaConfig.php','Seg_ComunicadosVigencia.php','Seg_ConfigurarEtiquetas.php','Seg_PlanesMenu.php','Seg_CorreoConfig.php']:
 p=root/'views'/name;s=p.read_text(encoding='utf-8');s=s.replace('<style>','<link rel="stylesheet" href="/resources/css/central-config.css?v=20261012-1">\n<style>',1);p.write_text(s,encoding='utf-8')
