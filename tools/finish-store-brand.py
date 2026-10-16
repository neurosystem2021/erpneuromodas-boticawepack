from pathlib import Path
import shutil
stage=Path('deploy-src/neurosystem-store')
p=stage/'resources/css/neurosystem-store.css'
with p.open('a',encoding='utf-8') as f:
    f.write('\n.promo-carousel{height:auto!important;aspect-ratio:3/1;min-height:0!important}.promo-carousel:before{background:#d51c30!important}.promo-slide:after{display:none!important}.promo-image{filter:none!important}.store-nav-option{background:#fff5f6!important;color:#6a303e!important;border-color:#eed7dd!important}.store-nav-close{background:#d51c30!important;color:white!important}\n')
p=stage/'api/tienda/index.php'
p.parent.mkdir(parents=True,exist_ok=True)
shutil.copy2(Path('C:/xampp/htdocs/boticav1/api/tienda/index.php'),p)
s=p.read_text(encoding='utf-8-sig')
old="foreach (array('imagen','imagen2','imagen3') as $field) $row[$field]=$row[$field] ? '/resources/images/productos/'.rawurlencode($row[$field]) : '';"
new="""foreach (array('imagen','imagen2','imagen3') as $field) {
            $image = trim(str_replace('\\\\', '/', (string)$row[$field]));
            $row[$field] = '';
            if ($image === '') continue;
            $relative = strpos(ltrim($image, '/'), 'resources/images/') === 0 ? ltrim($image, '/') : 'resources/images/productos/' . basename($image);
            $root = realpath(dirname(__DIR__, 2) . '/resources/images');
            $actual = realpath(dirname(__DIR__, 2) . '/' . $relative);
            if ($root && $actual && is_file($actual) && strpos(str_replace('\\\\', '/', $actual), str_replace('\\\\', '/', $root) . '/') === 0) {
                $row[$field] = '/' . implode('/', array_map('rawurlencode', explode('/', $relative)));
            }
        }"""
assert old in s
p.write_text(s.replace(old,new),encoding='utf-8')
print('Image resolver and banner proportions ready')
