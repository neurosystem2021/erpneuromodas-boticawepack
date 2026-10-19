from pathlib import Path
import fitz
from PIL import Image

root = Path(__file__).resolve().parent.parent
pdf_path = root / 'output/pdf/contrato-200-2026-firma-corregida.pdf'
doc = fitz.open(pdf_path)
assert len(doc) == 6
found = []
previews = []
for index, page in enumerate(doc):
    images = page.get_image_info(xrefs=True)
    assert not any(i['width'] == 223 and i['height'] == 107 for i in images), 'Old image still drawn'
    for info in images:
        if (info['width'], info['height']) == (1810, 869):
            found.append((index, info))
    pix = page.get_pixmap(matrix=fitz.Matrix(1.25, 1.25), alpha=False)
    path = root / f'output/pdf/contrato-firma-corregida-pagina-{index+1}.png'
    pix.save(path)
    thumb = Image.open(path).convert('RGB')
    thumb.thumbnail((400, 566))
    previews.append(thumb)
assert len(found) == 1
index, info = found[0]
x0, y0, x1, y1 = info['bbox']
ink_centre = x0 + (x1-x0)*927/1810
name = doc[index].search_for('Ing. Jhon H. Medrano Martinez')
assert len(name) == 1
name_centre = (name[0].x0 + name[0].x1)/2
assert abs(ink_centre-name_centre) < 1.5, (ink_centre, name_centre)
xref = info['xref']
assert '/SMask' in doc.xref_object(xref), 'Signature transparency missing'
assert any(d['type'] in ('s', 'fs') for p in doc for d in p.get_drawings()), 'Original decorative lines absent'
sheet = Image.new('RGB', (1200, 1132), '#e7e7e7')
for i, preview in enumerate(previews):
    sheet.paste(preview, ((i%3)*400, (i//3)*566))
sheet.save(root / 'output/pdf/contrato-firma-corregida-revision.png')
print(f'PASS {len(doc)} pages; transparent clean signature on page {index+1}, centered within {abs(ink_centre-name_centre):.2f} points; no original gray image; corporate design restored.')
