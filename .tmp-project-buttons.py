from pathlib import Path
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8').replace('ref="modalCarrito" modal-class="nst-dialog"','ref="modalCarrito" modal-class="nst-dialog project-workspace-modal"',1)
s=s.replace('<div style="margin-bottom: 20px">\n                <label>Descripción de Proyecto:</label>','<div class="project-description-section">\n                <label>Descripción del proyecto</label>',1)
s=s.replace('<b-col md="3">\n                    <b-button','<b-col md="3" class="project-description-actions">\n                    <b-button',1)
s=s.replace('<b-col md="3" class="mt-2">','<b-col cols="12" class="project-barcode-actions mt-2">\n                    <label>Etiquetas del proyecto</label>',1)
s=s.replace('<div slot="modal-footer">','<div slot="modal-footer" class="project-workspace-actions">',1).replace('<div style="left: 1rem; position: absolute">','<div class="project-secondary-actions">',1).replace('<div style="float: right; margin-left: 10px">','<div class="project-proforma-action">',1)
s=s.replace('> Imprimir </b-btn>','> Imprimir detalle </b-btn>',1).replace('Imprimir Recibo\n                      Servicios','Recibo de servicio',1)
p.write_text(s,encoding='utf-8')
p=Path('src/components/PreOrdenFormDet.vue');s=p.read_text(encoding='utf-8').replace('<table class="table b-table table-sm">','<div class="preorder-table-scroll"><table class="table b-table table-sm">',1).replace('</table>','</table></div>',1);p.write_text(s,encoding='utf-8')
