import cv2, numpy as np, struct
from pathlib import Path
base=Path('output');expected=(base/'qr-clarity-payload.txt').read_text()
raw=(base/'qr-clarity-ticket.bin').read_bytes();pos=2;chunks=[]
while raw[pos:pos+4]==b'\x1d\x76\x30\x00':
 w,h=struct.unpack_from('<HH',raw,pos+4);pos+=8
 data=np.frombuffer(raw[pos:pos+w*h],dtype=np.uint8).reshape(h,w);pos+=w*h
 chunks.append(255-np.unpackbits(data,axis=1)*255)
thermal=np.vstack(chunks);cv2.imwrite(str(base/'qr-clarity-thermal.png'),thermal)
for name,img in [('ticket PNG',cv2.imread(str(base/'qr-clarity-ticket.png'))),('exact ESC/POS dots',thermal)]:
 decoded,points,_=cv2.QRCodeDetector().detectAndDecode(img)
 assert decoded==expected,(name,'QR failed')
 print('PASS QR decoded from',name)
