from pathlib import Path
import fitz
from PIL import Image
root=Path(__file__).resolve().parent.parent
image=Image.open(root/'static/images/consultor-jhon-rubrica-limpia.png')
bounds=image.getchannel('A').getbbox()
relative_centre=(bounds[0]+bounds[2])/2/image.width
for name in ['contrato-sistemas-rubrica-limpia','proforma-18-rubrica-limpia']:
    doc=fitz.open(root/'output/pdf'/f'{name}.pdf')
    page=next(p for p in doc if p.search_for('Firma / rúbrica'))
    signature=next(i for i in page.get_image_info() if i['width']==1810 and i['height']==869)
    rect=fitz.Rect(signature['bbox'])
    ink_centre=rect.x0+rect.width*relative_centre
    name_rect=page.search_for('Ing. Jhon H. Medrano Martinez')[-1]
    name_centre=(name_rect.x0+name_rect.x1)/2
    error=abs(ink_centre-name_centre)
    print(name,'visible ink/name difference:',round(error,3),'pt')
    assert error<1.2,'Signature is not centred above the consultant name'
    page.get_pixmap(matrix=fitz.Matrix(1.3,1.3)).save(str(root/'output/pdf'/f'{name}-centrada.png'))
