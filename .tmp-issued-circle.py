from pathlib import Path
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8');s+='''
/* Acceso discreto a comprobantes emitidos. */
.pos-page .pos-issued-button{left:18px!important;right:auto!important;bottom:22px;width:46px!important;height:46px!important;min-width:46px!important;min-height:46px!important;padding:0!important;border-radius:50%!important;justify-content:center!important;gap:0!important}
.pos-page .pos-issued-button span{display:none!important}
@media(max-width:767.98px){.pos-page .pos-issued-button{left:12px!important;bottom:calc(132px + env(safe-area-inset-bottom))!important}}
''';p.write_text(s,encoding='utf-8')
