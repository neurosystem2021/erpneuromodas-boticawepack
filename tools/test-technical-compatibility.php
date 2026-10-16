<?php
require __DIR__.'/../integration/ali-product-knowledge.php';
require __DIR__.'/../integration/ali-product-guide.php';
require __DIR__.'/../integration/ali-technical-expert.php';
$products=aliTechnicalProducts([['name'=>'RAM DDR4','model'=>'TEST','specs'=>'16GB'],['name'=>'SSD','model'=>'TEST-SSD','specs'=>'NVMe']]);
$rows=aliTechnicalCompatibility([['key'=>'0','status'=>'incompatible','reason'=>'DDR4 frente a DDR5','models'=>'No confirmado'],['key'=>'999','status'=>'compatible']],$products);
if(count($rows)!==2||$rows[0]['status']!=='incompatible'||$rows[1]['status']!=='pending')throw new Exception('Invalid result matching');
$draft=aliTechnicalDraft(['quoteProducts'=>$products,'compatibility'=>$rows]);
if(count($draft['quoteProducts'])!==2||count($draft['compatibility'])!==2)throw new Exception('Persistence failed');
echo "PASS per-product results mapping, missing results pending and case persistence normalization.\n";

$parts=aliTechnicalProducts([['name'=>'PLACA MADRE MSI B650'],['name'=>'MEMORIA RAM DDR4 8GB']]);$fixed=aliTechnicalCompatibility([],$parts);if($fixed[0]['status']!=='incompatible'||$fixed[1]['status']!=='incompatible'||strpos($fixed[0]['memory'],'DDR5')===false)throw new Exception('Memory generation safeguard failed');if(aliTechnicalCompatibility($fixed,$parts)!==$fixed)throw new Exception('Memory normalization not idempotent');echo "PASS verified MSI B650 DDR5 rule, reciprocal DDR4 conflict and idempotence.\n";

$separate=aliTechnicalDraft(['equipment'=>'Impresora de prueba','request'=>'No imprime','validator'=>['assembly'=>true,'quoteProducts'=>$parts,'compatibility'=>$fixed,'analysis'=>'Validacion de piezas']]);if($separate['equipment']!=='Impresora de prueba'||$separate['quoteProducts']!==[]||count($separate['validator']['quoteProducts'])!==2)throw new Exception('Independent diagnosis/validator persistence failed');echo "PASS independent diagnosis and validator case data.\n";

try{aliTechnicalRequireCoverage([['key'=>'0','status'=>'compatible','reason'=>'Standard confirmed']],$products);throw new Exception('Missing row accepted');}catch(RuntimeException $e){}
aliTechnicalRequireCoverage([['key'=>'0','status'=>'compatible','reason'=>'Interface confirmed'],['key'=>'1','status'=>'pending','reason'=>'Need interface','requirements'=>'Exact connector']],$products);echo "PASS missing product results rejected; compatible rows and specific pending requirements accepted.\n";
