from pathlib import Path
import re,json
from decimal import Decimal
sql=Path(r'C:/Users/USER/Downloads/fact_nst_COMPLETA.sql').read_text(encoding='utf-8-sig')
schema=json.loads(Path('output/migration-schema-comparison.json').read_text(encoding='utf-8'))['source_tables']
# Parse only INSERT VALUES literals offline. Never execute dump SQL.
def tuples(text):
 i=0;n=len(text)
 while i<n:
  while i<n and text[i] in ' \r\n\t,;':i+=1
  if i==n:return
  if text[i]!='(':raise ValueError('Unsupported VALUES syntax')
  i+=1;row=[]
  while True:
   while i<n and text[i].isspace():i+=1
   if text[i]=="'":
    i+=1;v=''
    while i<n:
     c=text[i];i+=1
     if c=='\\':
      if i>=n:raise ValueError('Incomplete escape')
      c=text[i];i+=1;v+= {'n':'\n','r':'\r','t':'\t','0':'\0'}.get(c,c)
     elif c=="'":
      if i<n and text[i]=="'":v+="'";i+=1
      else:break
     else:v+=c
   else:
    start=i
    while i<n and text[i] not in ',)':i+=1
    v=text[start:i].strip();v=None if v.upper()=='NULL' else v
   if isinstance(v,str) and re.fullmatch(r"b'[01]+'",v):v=str(int(v[2:-1],2))
   row.append(v)
   while i<n and text[i].isspace():i+=1
   if text[i]==')':i+=1;break
   if text[i]!=',':raise ValueError('Invalid separator')
   i+=1
  yield row
# Statement scanner respects semicolons in quoted data.
def statements(text):
 start=0;quote=False;i=0
 while i<len(text):
  c=text[i]
  if quote and c=='\\':i+=2;continue
  if c=="'":
   if quote and i+1<len(text) and text[i+1]=="'":i+=2;continue
   quote=not quote
  if c==';' and not quote:yield text[start:i+1];start=i+1
  i+=1
rows={};errors=[]
for st in statements(sql):
 m=re.search(r'INSERT INTO\s+`([^`]+)`\s*(?:\((.*?)\))?\s*VALUES\s*(.*);\s*$',st,re.S|re.I)
 if not m:continue
 name,cols,values=m.groups()
 if name not in schema:errors.append('Unknown table '+name);continue
 fields=re.findall(r'`([^`]+)`',cols) if cols else list(schema[name]['columns'])
 for r in tuples(values):
  if len(r)!=len(fields):raise ValueError('Column count mismatch '+name)
  rows.setdefault(name,[]).append(dict(zip(fields,r)))
counts={n:len(rows.get(n,[])) for n in schema}
ids=lambda t,c:{r[c] for r in rows.get(t,[])}
sales=ids('Ve_DocVenta','idDocVenta');products=ids('Gen_Producto','IdProducto');clients=ids('Ve_DocVentaCliente','IdCliente')
out={'counts':counts,'parse_errors':errors,'orphans':{},'totals':{}}
for table,key,valid in [('Ve_DocVentaDet','IdDocVenta',sales),('Ve_DocVentaMetodoPagoDet','IdDocVenta',sales),('Ve_DocVentaNotaCredito','IdDocVenta',sales),('Ve_DocVentaDet','IdProducto',products),('Ve_DocVenta','IdCliente',clients)]:out['orphans'][table+'.'+key]=sum(next((v for k,v in r.items() if k.lower()==key.lower()),None) not in valid for r in rows.get(table,[]))
for table,fields in [('Ve_DocVenta',('IdTipoDoc','IdDocVentaPuntoVenta','Serie','Numero')),('Gen_Producto',('CodigoBarra',))]:
 seen={}
 for r in rows.get(table,[]):
  k=tuple(r.get(f) for f in fields)
  if all(v is None or not str(v).strip() for v in k):continue
  seen[k]=seen.get(k,0)+1
 out[table+'_duplicate_groups']=sum(v>1 for v in seen.values())
for table,formula in [('Ve_DocVentaDet',lambda r:Decimal(r.get('Cantidad') or '0')*Decimal(r.get('Precio') or '0')-Decimal(r.get('Descuento') or '0')),('Lo_OrdenCompra',lambda r:Decimal(r.get('Total') or '0')),('Ve_DocVentaMetodoPagoDet',lambda r:Decimal(r.get('Importe') or '0'))]:out['totals'][table]=str(sum((formula(r) for r in rows.get(table,[])),Decimal(0)))
Path('output/migration-source-validation.json').write_text(json.dumps(out,indent=2,ensure_ascii=False),encoding='utf-8')
print(json.dumps({k:v for k,v in out.items() if k!='counts'},ensure_ascii=False))
print(json.dumps({k:v for k,v in counts.items() if k.startswith(('Ve_DocVenta','Lo_Orden','Lo_Movimiento')) or k in ['Gen_Producto','Lo_Proveedor','prodstock']},ensure_ascii=False))
# Additional purchase checks: preserve document-type meaning and original currencies.
from collections import defaultdict
movements=ids('Lo_Movimiento','Hash');vendors=ids('Lo_Proveedor','IdProveedor');orders=ids('Lo_OrdenCompra','IdOrdenCompra')
for table,key,valid in [('Lo_MovimientoDetalle','hashMovimiento',movements),('Lo_OrdenCompraDet','IdOrdenCompra',orders),('Lo_MovimientoDetalle','IdProducto',products)]:out['orphans'][table+'.'+key]=sum(r.get(key) not in valid for r in rows.get(table,[]))
by_type=defaultdict(int)
for r in rows.get('Lo_Movimiento',[]):by_type[(r.get('IdMovimientoTipo'),r.get('Moneda'),r.get('Anulado'))]+=1
out['movement_groups']=[{'type_id':k[0],'currency':k[1],'cancelled':k[2],'count':v} for k,v in by_type.items()]
out['movement_type_definitions']=[{k:r.get(k) for k in ['IdMovimientoTipo','TipoMovimiento','Tipo','VaRegCompra','CodSunat','Anulado']} for r in rows.get('Lo_MovimientoTipo',[])]
Path('output/migration-source-validation.json').write_text(json.dumps(out,indent=2,ensure_ascii=False),encoding='utf-8')
print('Offline analysis complete. No SQL executed.')
# Reconcile each document without exposing customer data or document numbers.
line_totals=defaultdict(Decimal);payment_totals=defaultdict(Decimal)
for r in rows.get('Ve_DocVentaDet',[]):line_totals[r['IdDocVenta']]+=Decimal(r.get('Cantidad') or '0')*Decimal(r.get('Precio') or '0')-Decimal(r.get('Descuento') or '0')
for r in rows.get('Ve_DocVentaMetodoPagoDet',[]):payment_totals[r['IdDocVenta']]+=Decimal(r.get('Importe') or '0')
differences=[];groups={}
for r in rows.get('Ve_DocVenta',[]):
 sid=r['idDocVenta'];delta=line_totals[sid]-payment_totals[sid]
 if delta: differences.append(delta)
 key=(r.get('IdTipoDoc'),r.get('Anulado'),r.get('Estado'),r.get('EsCredito'))
 g=groups.setdefault(key,{'count':0,'detail_total':Decimal(0),'payments':Decimal(0)});g['count']+=1;g['detail_total']+=line_totals[sid];g['payments']+=payment_totals[sid]
out['sales_reconciliation']={'documents_with_any_difference':len(differences),'documents_difference_over_one_cent':sum(abs(d)>Decimal('.01') for d in differences),'max_absolute_difference':str(max(map(abs,differences),default=Decimal(0))),'missing_detail_documents':sum(r['idDocVenta'] not in line_totals for r in rows.get('Ve_DocVenta',[])),'missing_payment_documents':sum(r['idDocVenta'] not in payment_totals for r in rows.get('Ve_DocVenta',[])),'currency':'Not declared on Ve_DocVenta; do not infer PEN without verifying application semantics.'}
out['sales_groups']=[{'type_id':k[0],'cancelled':k[1],'state':k[2],'credit':k[3],**{a:str(b) if isinstance(b,Decimal) else b for a,b in v.items()}} for k,v in groups.items()]
Path('output/migration-source-validation.json').write_text(json.dumps(out,indent=2,ensure_ascii=False),encoding='utf-8')
print(json.dumps(out['sales_reconciliation']))
