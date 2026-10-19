import os,json,base64
from pathlib import Path
thread=os.environ['CODEX_THREAD_ID']
files=list((Path.home()/'.codex'/'sessions').rglob('*'+thread+'.jsonl'))
for file in files:
    found=False
    for line in file.open(encoding='utf-8'):
        if 'MANEJAR ESA RUBRICA' not in line:
            continue
        data=json.loads(line)
        payload=data.get('payload',{})
        if payload.get('role')!='user':
            continue
        found=True
        for block in payload.get('content',[]):
            if block.get('type') not in ['input_image','image']:
                continue
            value=block.get('image_url') or block.get('url') or block.get('image')
            if isinstance(value,str) and value.startswith('data:'):
                header,encoded=value.split(',',1)
                extension='.png' if 'png' in header else '.jpg'
                output=Path(__file__).resolve().parent.parent/'static'/'images'/('consultor-jhon-rubrica-original'+extension)
                output.write_bytes(base64.b64decode(encoded))
                print('Original attached image saved:',output,'bytes:',output.stat().st_size)
            else:
                print('Image attachment metadata:',{k:v for k,v in block.items() if isinstance(v,str) and len(v)<500})
    print('Matching user message found:',found)
