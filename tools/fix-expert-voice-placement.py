from pathlib import Path
p=Path('src/App.vue');s=p.read_text(encoding='utf-8').replace('<diario-recordatorios v-if="$route.name !== \'Ventas\'" />', '<diario-recordatorios v-if="$route.name !== \'Ventas\'" :class="{\'pos-inline-voice\':$route.name===\'TecnicoExperto\'}" />');p.write_text(s,encoding='utf-8')
p=Path('tools/render-technical-report.ps1');s=p.read_text(encoding='utf-8').replace('$renderWord.Quit();', 'try{$renderWord.Quit()}catch{if($_.Exception.Message -notmatch \'RPC\'){throw}};');p.write_text(s,encoding='utf-8')
