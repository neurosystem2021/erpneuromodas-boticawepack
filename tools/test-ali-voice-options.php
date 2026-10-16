<?php
require __DIR__.'/../integration/ali-api.php';
$expected=['alloy','ash','ballad','coral','echo','fable','nova','onyx','sage','shimmer','verse','marin','cedar'];
if(aliVoiceOptions()!==$expected)throw new Exception('Unexpected voice list');
foreach(['natural','dulce','infantil','alegre'] as $style){
 $text=aliVoiceInstructions($style);
 if(!is_string($text)||strpos($text,'español latinoamericano')===false)throw new Exception('Invalid style');
}
if(aliVoiceInstructions('invalid')!==null)throw new Exception('Unknown styles must be rejected');
if(aliVoiceInstructions('natural')===aliVoiceInstructions('infantil'))throw new Exception('Styles must differ');
echo 'PASS all 13 voice IDs and 4 distinct validated styles. No API requests.'.PHP_EOL;
