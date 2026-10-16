from pathlib import Path
p=Path(__file__).resolve().parents[1]/'src/views/Ventas.vue';s=p.read_text(encoding='utf-8');needle='.ali-checkout-actions button{flex:1;font-size:12px}}';s=s.replace(needle,'.ali-checkout-actions button{flex:1;font-size:12px}.ali-checkout-panel>div:first-child{padding-right:95px}.ali-checkout-total{flex-basis:100%}}');p.write_text(s,encoding='utf-8')
print('Reserved space for ALI fish so the confirmation total remains visible on desktop and mobile.')
