from pathlib import Path
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8');s+='''
@media(max-width:767.98px){.pos-page .pos-checkout-area,.pos-page .pos-checkout-form,.pos-page .pos-checkout-grid,.pos-page .pos-summary-panel{transform:none!important;filter:none!important;backdrop-filter:none!important;-webkit-backdrop-filter:none!important;contain:none!important}}
''';p.write_text(s,encoding='utf-8')
