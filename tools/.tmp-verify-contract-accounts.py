from pathlib import Path
import fitz
from PIL import Image
root=Path(__file__).resolve().parent.parent
doc=fitz.open(root/'output/pdf/contrato-200-2026-con-cuentas.pdf')
banks=[];signatures=[];previews=[]
for index,page in enumerate(doc):
    for info in page.get_image_info(xrefs=True):
        if (info['width'],info['height'])==(1180,229):banks.append((index,info))
        if (info['width'],info['height'])==(1810,869):signatures.append((index,info))
        assert (info['width'],info['height'])!=(223,107),'Old signature image returned'
    pix=page.get_pixmap(matrix=fitz.Matrix(1.3,1.3),alpha=False)
    path=root/f'output/pdf/contrato-cuentas-pagina-{index+1}.png';pix.save(path)
    thumb=Image.open(path).convert('RGB');thumb.thumbnail((400,566));previews.append(thumb)
assert len(doc)==6 and len(banks)==1 and len(signatures)==1
page,bank=banks[0];rect=fitz.Rect(bank['bbox'])
assert rect.x0>=40 and rect.x1<=doc[page].rect.width-40
assert abs(rect.width/rect.height-1180/229)<.02,'Image distorted'
assert len(doc[page].search_for('INFORMACIÓN DE PAGO'))==1
assert rect.y1<doc[page].rect.height-45,'Accounts clipped by footer'
sheet=Image.new('RGB',(1200,1132),'#e7e7e7')
for i,preview in enumerate(previews):sheet.paste(preview,((i%3)*400,(i//3)*566))
sheet.save(root/'output/pdf/contrato-cuentas-revision.png')
print(f'PASS {len(doc)} pages, bank image on page {page+1} with preserved proportions and margins; clean signature on page {signatures[0][0]+1}.')
