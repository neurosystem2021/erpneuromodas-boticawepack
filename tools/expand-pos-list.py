from pathlib import Path
p=Path('static/css/pos-layout-fix.css')
s=p.read_text(encoding='utf-8')
s+='''
/* POS: grow the basket with products instead of clipping it to two rows. */
.pos-page .pos-cart-component .divTBl .table-responsive{height:auto!important;max-height:none!important;overflow-y:visible!important;overflow-x:auto!important}
.pos-page .pos-cart-component .divTBl.has-products{height:auto!important;max-height:none!important;min-height:0!important}
.pos-page .pos-cart-component .pos-desktop-cart tbody td{padding:6px 7px!important;height:auto!important;vertical-align:middle!important}
.pos-page .pos-cart-component .pos-desktop-cart tbody input{min-height:34px!important;height:34px!important;padding:5px 7px!important}
.pos-page .pos-cart-component .pos-desktop-cart tbody textarea{min-height:34px!important;height:34px!important;padding:5px 7px!important}
.pos-page .pos-cart-component .pos-desktop-cart tbody button{min-height:34px!important;max-height:38px!important;padding:6px 10px!important}
.pos-page .pos-cart-pay-row{padding:7px!important}
@media(min-width:768px){
.pos-page .pos-sale-grid>.pos-customer-panel,.pos-page .pos-sale-grid>.pos-loyalty-panel,.pos-page .pos-sale-grid>.pos-document-panel{min-height:0!important;height:auto!important;padding-top:6px!important;padding-bottom:6px!important;margin-bottom:5px!important}
.pos-page .pos-customer-panel .pos-client-block.is-client-collapsed{min-height:0!important;padding-bottom:0!important;margin-bottom:0!important}
.pos-page .pos-meta-inline-card{min-height:40px!important;height:40px!important;padding:5px 10px!important}
.pos-page .pos-document-panel{grid-template-rows:auto auto auto!important;row-gap:2px!important}
.pos-page .pos-document-panel .form-group{margin-bottom:2px!important}
.pos-page .pos-loyalty-panel .puntos{font-size:24px!important}
.pos-page .pos-tabs>.card-header{padding-top:4px!important;padding-bottom:4px!important}
.pos-page .pos-cart-component .divTBl .table-responsive{max-height:none!important}
}
'''
p.write_text(s,encoding='utf-8')
