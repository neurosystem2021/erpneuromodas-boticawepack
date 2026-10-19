from pathlib import Path
root=Path(__file__).resolve().parent.parent
p=root/'integration/proforma-edit/proforma-pdf-render.php';raw=p.read_bytes()
def replace(old,new):
 global raw
 old=old.encode('utf-8');new=new.encode('utf-8');assert raw.count(old)==1,raw.count(old);raw=raw.replace(old,new)
replace("require_once __DIR__.'/proforma-scope.php';","require_once __DIR__.'/proforma-scope.php';\nrequire_once __DIR__.'/proforma-featured.php';")
replace("$systems=($quote['Presentacion']??'productos')==='sistemas';$systemTitle=","$systems=($quote['Presentacion']??'productos')==='sistemas';$featured=!$systems&&($quote['FormatoProducto']??'catalogo')==='destacado';$featuredProducts='';$systemTitle=")
replace('if($systems&&strpos(', 'if(($systems||$featured)&&strpos(')
replace("$hidden=!empty($p['DisabledTotal']);", "$hidden=!empty($p['DisabledTotal']);if($featured)$featuredProducts.=qpFeaturedProductHtml($p,$i,$extras,$line,$hidden);")
replace(':$commercialTable;', ':($featured?$featuredProducts:$commercialTable);')
replace("$planCss.$contextCss.","$planCss.$contextCss.qpFeaturedCss().(!$systems?'.products b{font-size:10px;font-weight:bold;color:#32242b}.products .detail{font-size:9px;font-weight:bold;line-height:1.6;color:#46353e}':'').")
p.write_bytes(raw)
print('Featured image/description and bold commercial text added to server PDF only; systems branch unchanged.')
