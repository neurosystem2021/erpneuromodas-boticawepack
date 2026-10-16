<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$name='helpdesk_test_'.bin2hex(random_bytes(6));if(!preg_match('/^helpdesk_test_[a-f0-9]{12}$/',$name))throw new RuntimeException('Unsafe test schema');
$db->exec('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
function mustReject($db,$sql){try{$db->exec($sql);}catch(PDOException $e){return;}throw new RuntimeException('Expected rejection: '.$sql);}
try{$db->exec('USE `'.$name.'`');foreach(glob(dirname(__DIR__).'/integration/helpdesk/migrations/*.up.sql') as $file)$db->exec(file_get_contents($file));
$db->exec("INSERT INTO support_installations(installation_uuid,origin_https,created_at) VALUES('00000000-0000-0000-0000-000000000001','https://tenant-a.invalid',NOW()),('00000000-0000-0000-0000-000000000002','https://tenant-b.invalid',NOW())");
$db->exec("INSERT INTO support_tenants(installation_id,external_company_id,created_at) VALUES(1,'1',NOW()),(2,'1',NOW())");
$db->exec("INSERT INTO support_principals(installation_id,external_user_id) VALUES(1,'admin'),(2,'admin')");
$db->exec("INSERT INTO support_categories(name) VALUES('Test')");
$db->exec("INSERT INTO support_tickets(tenant_id,requester_id,category_id,code,subject,description,affected_module,created_at,updated_at) VALUES(1,1,1,'SOP-2026-000001','Test','Fixture','POS',NOW(),NOW())");
mustReject($db,"INSERT INTO support_ticket_messages(tenant_id,ticket_id,author_id,visibility,body,operation_uuid,created_at) VALUES(2,1,2,'public','Cross tenant','a',NOW())");
$db->exec("INSERT INTO support_ticket_messages(tenant_id,ticket_id,author_id,visibility,body,operation_uuid,created_at) VALUES(1,1,1,'public','Fixture','a',NOW())");
mustReject($db,"INSERT INTO support_ticket_messages(tenant_id,ticket_id,author_id,visibility,body,operation_uuid,created_at) VALUES(1,1,1,'public','Duplicate','a',NOW())");
mustReject($db,"INSERT INTO support_tickets(tenant_id,requester_id,category_id,code,subject,description,affected_module,created_at,updated_at) VALUES(2,2,1,'SOP-2026-000001','Duplicate','Fixture','POS',NOW(),NOW())");
mustReject($db,"INSERT INTO support_satisfaction_surveys(tenant_id,ticket_id,respondent_id,solved,speed,treatment,solution,recommend,comment,created_at) VALUES(1,1,1,1,0,6,2,1,'Invalid',NOW())");
$files=glob(dirname(__DIR__).'/integration/helpdesk/migrations/*.down.sql');rsort($files);foreach($files as $file)$db->exec(file_get_contents($file));
if($db->query('SHOW TABLES')->fetchAll())throw new RuntimeException('Rollback incomplete');
echo "PASS four migrations and reverse rollback; isolated company IDs/users; ticket uniqueness; cross-tenant message foreign key; deduplication; survey bounds. No production data changed.\n";
}finally{$db->exec('DROP DATABASE `'.$name.'`');}
