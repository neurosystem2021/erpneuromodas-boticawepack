<?php
if(($argv[1]??'')!=='--install')throw new RuntimeException('Use --install');require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$db->exec(file_get_contents(__DIR__.'/../integration/technical-expert.sql'));echo "Technical case table installed. Existing catalog, sales and tickets unchanged.\n";
