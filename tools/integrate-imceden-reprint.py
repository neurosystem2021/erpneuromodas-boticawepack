from pathlib import Path
import subprocess
p=Path('src/views/VentaReimpresion.vue');s=subprocess.check_output(['git','-c','safe.directory=C:/xampp/htdocs/boticawebpack','show','imceden:src/views/VentaReimpresion.vue']).decode('utf-8')
s=s.replace(':current-page="currentPage">',':current-page="currentPage" responsive show-empty empty-text="No hay comprobantes para los filtros seleccionados.">')
s=s.replace('<a class="btn action-button xml-button" :href="xmlUrl(data.item)" title="Descargar XML">','<button type="button" class="btn action-button xml-button" @click="descargarXml(data.item)" title="Descargar XML">').replace('<i class="fas fa-file-code"></i> XML\n                  </a>','<i class="fas fa-file-code"></i> XML\n                  </button>')
s=s.replace('  methods: {','''  methods: {
    async descargarXml(item) {
      try {
        const response=await this.axios.get(this.xmlUrl(item),{responseType:'blob'});
        const url=URL.createObjectURL(response.data);const link=document.createElement('a');
        link.href=url;link.download=(item.Correlativo || 'Comprobante')+'.xml';document.body.appendChild(link);link.click();link.remove();setTimeout(()=>URL.revokeObjectURL(url),30000);
      } catch(error) {
        let message='El XML de este comprobante no está disponible.';
        if(error.response && error.response.data && error.response.data.text){try{message=JSON.parse(await error.response.data.text()).error || message;}catch(e){}}
        this.$toasted.error(message);
      }
    },''',1)
s=s.replace('(ctx.filter || "")','encodeURIComponent(ctx.filter || "")')
css='''
.reprint-page .document-actions .whatsapp-button{background:#25d366!important;color:#073d22!important;border:1px solid #19ba57!important;box-shadow:0 4px 12px rgba(37,211,102,.3)!important}
.reprint-page .document-actions .whatsapp-button:hover,.reprint-page .document-actions .whatsapp-button:focus{background:#1fc45d!important;color:#052d18!important}
.reprint-page /deep/ .pagination{display:flex!important;flex-direction:row!important;flex-wrap:wrap;gap:3px;margin:16px 26px!important}
.reprint-page /deep/ .table-responsive{overflow-x:auto}
.reprint-header{padding:16px 24px}
.reprint-page{padding:14px;min-height:0}
'''
s=s.replace('</style>',css+'</style>');p.write_text(s,encoding='utf-8')
p=Path('integration/reprint-index.php');s=Path('C:/xampp/htdocs/boticav1/api/index.php').read_text(encoding='utf-8');needle='$registerPayroll($app);';s=s.replace(needle,needle+"\n$registerReceiptDownloads=require __DIR__.'/receipt-downloads.php';$registerReceiptDownloads($app);")
start=s.index("$app->get('/imprimirpdf/{id}'");end=s.index("$app->post('/moveraemitidos'",start);block=s[start:end];needle="return $response->write($content);";assert needle in block;block=block.replace(needle,"if($request->getParam('descargar'))$response=$response->withHeader('Content-Disposition','attachment; filename=\"Comprobante_'.(int)$id.'.pdf\"');\n    return $response->write($content);")
s=s[:start]+block+s[end:];p.write_text(s,encoding='utf-8')
