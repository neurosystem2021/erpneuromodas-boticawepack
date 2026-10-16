from pathlib import Path
p=Path('src/utils/cashClosingTicket.js');s=p.read_text(encoding='utf-8').replace('({a', '({a');s=s.replace('({ ...item,','Object.assign({},item,');s=s.replace('({...item, price:item.quantity ? item.gross/item.quantity : item.price})','Object.assign({},item,{price:item.quantity ? item.gross/item.quantity : item.price})');p.write_text(s,encoding='utf-8')
