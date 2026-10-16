from pathlib import Path
p=Path('integration/payroll/api/index-export.php');s=Path('C:/xampp/htdocs/boticav1/api/index.php').read_text(encoding='utf-8');needle='$registerPayroll($app);';assert needle in s;s=s.replace(needle,needle+"\n$registerPayrollExport=require __DIR__.'/payroll-export.php';$registerPayrollExport($app);");p.write_text(s,encoding='utf-8')
p=Path('src/views/Planilla.vue');s=p.read_text(encoding='utf-8');needle='<nav class="payroll-tabs">';s=s.replace(needle,needle+'''<a class="btn btn-success" :class="{'disabled': loading || filtersDirty}" :href="config.API+'planilla/excel?periodo='+encodeURIComponent(period)+'&amp;idAlmacen='+encodeURIComponent(warehouse)+'&amp;buscar='+encodeURIComponent(filter)" target="_blank" rel="noopener">Exportar Excel</a>''');
# config is imported but the component exposes API through its existing getter; inspect and use that same property.
s=s.replace(':href="config.API+',':href="API+')
p.write_text(s,encoding='utf-8')
