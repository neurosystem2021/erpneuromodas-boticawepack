from pathlib import Path
root=Path(__file__).resolve().parents[1]
test=root/'tools/test-ali-api.php'
source=test.read_text(encoding='utf-8')
old="checkAli($code===200&&!$data['superadmin']&&!isset($data['key']),'safe config');[$code]=callAli('config',$cookie,[], $data['csrf']);checkAli($code===403,'role');"
new="checkAli($code===403,'unknown ERP user denied');[$code]=callAli('config',$cookie,[]);checkAli($code===403,'role');"
if old in source:test.write_text(source.replace(old,new),encoding='utf-8')
for name in ['phase2','responsive','voice-variety']:
    source=(root/f'.tmp-order-workbook/test-ali-{name}.mjs').read_text(encoding='utf-8')
    source=source.replace("import {chromium} from 'playwright';", "import {createRequire} from 'node:module';\nconst require=createRequire(new URL('../.tmp-order-workbook/package.json',import.meta.url));\nconst {chromium}=require('playwright');")
    (root/f'tools/test-ali-{name}-browser.mjs').write_text(source,encoding='utf-8')
print('Regression auth expectation updated; reproducible browser test sources copied into tools/. Playwright is resolved from the existing local test runtime.')
