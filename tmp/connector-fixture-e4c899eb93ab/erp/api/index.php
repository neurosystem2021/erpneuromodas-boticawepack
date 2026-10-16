<?php
$registerSupportConnector=require __DIR__.'/support-connector.php';$registerSupportConnector($app);

$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/fixture', function(){});
