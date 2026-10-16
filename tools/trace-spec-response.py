from pathlib import Path
p=Path('integration/ali-characteristics.php');s=p.read_text(encoding='utf-8').replace('$response=$next($q,$r);',"$response=$next($q,$r)->withHeader('X-Product-Specs','items-v2');").replace('catch(Throwable $e){return $response;}',"catch(Throwable $e){error_log('[PRODUCT SPECS] '.get_class($e));return $response;}");p.write_text(s,encoding='utf-8')
