<?php
ini_set('display_errors','0');header('Cache-Control: no-store, private');header('X-Content-Type-Options: nosniff');
require_once __DIR__.'/concretera/Bootstrap.php';
function cxResponse($status,$data) { http_response_code($status);header('Content-Type: application/json; charset=utf-8');echo cxJson($data);exit; }
try {
    list($service,$csrf)=cxBootstrap();$method=$_SERVER['REQUEST_METHOD'];
    $p=$method==='GET'?$_GET:json_decode(file_get_contents('php://input'),true);if(!is_array($p))throw new CxError('Solicitud JSON inválida.');
    $action=cxText($p['action']??'context',40,true);
    if($action==='context'&&$method==='GET')cxResponse(200,array_merge($service->context(),['csrf'=>$csrf]));
    $company=cxId($p['companyId']??0);
    if($method==='GET') {
        if($action==='mix-control')cxResponse(200,$service->mixControl($company,$p));
        if($action==='mix-control-document'){
            $format=cxChoice($p['formato']??'',['cargas-csv','insumos-csv','preview','pdf'],'el formato del reporte');$report=$service->mixControl($company,$p);
            require_once __DIR__.'/concretera/MixControlDocument.php';
            if(in_array($format,['cargas-csv','insumos-csv'],true)){$section=$format==='cargas-csv'?'cargas':'insumos';header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="control-'.$section.'-'.$report['desde'].'-'.$report['hasta'].'.csv"');echo cxMixCsv($report,$section);exit;}
            if($format==='pdf'&&count($report['cargas'])>300)throw new CxError('El PDF admite hasta 300 cargas por documento. Reduzca el período o descargue CSV.');
            $html=cxMixControlHtml($report);if($format==='preview'){header('Content-Type: text/html; charset=utf-8');header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; img-src data:");echo $html;exit;}
            header('Content-Type: application/pdf');header('Content-Disposition: inline; filename="control-mezclas-'.$report['desde'].'-'.$report['hasta'].'.pdf"');echo cxRenderPdf($html,'landscape');exit;
        }
        if($action==='recipe-history'){$service->permission($company);require_once __DIR__.'/concretera/RecipeHistory.php';cxResponse(200,cxRecipeHistory($p['volumen']??'1'));}
        if($action==='schedule-defaults')cxResponse(200,$service->scheduleDefaults($company,$p));
        if($action==='recipe-source'){$service->permission($company);cxResponse(200,json_decode(file_get_contents(__DIR__.'/../data/guia-recetas-revision.json'),true));}
        if($action==='operations-context')cxResponse(200,$service->operationsContext($company));
        if($action==='materials')cxResponse(200,$service->materials($company,$p['idAlmacen']??null));
        if($action==='stock-alerts')cxResponse(200,$service->stockAlerts($company,$p['idAlmacen']??0,$p['hasta']??''));
        if($action==='suppliers')cxResponse(200,$service->findSuppliers($company,$p['q']??''));
        if($action==='receipts')cxResponse(200,$service->receipts($company));
        if($action==='designs')cxResponse(200,$service->designs($company));
        if($action==='design')cxResponse(200,$service->design($company,$p['id']??0));
        if($action==='schedules')cxResponse(200,$service->schedules($company,$p['dia']??''));
        if($action==='schedule')cxResponse(200,$service->schedule($company,$p['id']??0));
        if($action==='catalog')cxResponse(200,$service->catalog($company,cxText($p['kind']??'',30,true),$p['q']??''));
        if($action==='catalog-candidates')cxResponse(200,$service->catalogCandidates($company,cxText($p['kind']??'',30,true),$p['q']??''));
        if($action==='list')cxResponse(200,$service->listing($company,cxText($p['kind']??'',30,true)));
        if($action==='proposal')cxResponse(200,$service->proposal($company,$p['id']??0));
        if($action==='order')cxResponse(200,$service->order($company,$p['id']??0));
        if($action==='fiscal-settings')cxResponse(200,$service->fiscalSettings($company,$p['idEmpresaEmisora']??0));
        if($action==='billing-context')cxResponse(200,$service->billingContext($company,$p['kind']??'',$p['id']??0));
        if($action==='pdf'||$action==='preview') {
            $kind=cxChoice($p['kind']??'',['proposal','order'],'el documento');$id=cxId($p['id']??0);
            $doc=$kind==='proposal'?$service->proposal($company,$id):$service->order($company,$id);
            require_once __DIR__.'/concretera/Pdf.php';$html=cxDocumentHtml($doc,$kind);
            if($action==='preview'){header('Content-Type: text/html; charset=utf-8');header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; img-src data:");echo $html;exit;}
            $pdf=cxRenderPdf($html);header('Content-Type: application/pdf');header('Content-Disposition: inline; filename="concretera-'.$kind.'-'.$id.'.pdf"');echo $pdf;exit;
        }
    } else {
        if($action==='area')cxResponse(200,$service->saveArea($company,$p));
    if($action==='plant-operation')cxResponse(200,$service->savePlant($company,$p));
    if($action==='equipment')cxResponse(200,$service->saveEquipment($company,$p));
    if($action==='driver')cxResponse(200,$service->saveDriver($company,$p));
    if($action==='equipment-block')cxResponse(200,$service->equipmentBlock($company,$p));
    if($action==='design')cxResponse(200,$service->saveDesign($company,$p));
    if($action==='approve-design')cxResponse(200,$service->approveDesign($company,$p));
    if($action==='authorize-agenda')cxResponse(200,$service->authorizeAgenda($company,$p));
    if($action==='material')cxResponse(200,$service->saveMaterial($company,$p));
    if($action==='stock-policy')cxResponse(200,$service->saveStockPolicy($company,$p));
    if($action==='retry-receipt')cxResponse(200,$service->retryReceipt($company,$p));
    if($action==='receive-materials')cxResponse(200,$service->receiveMaterials($company,$p));
    if($action==='preview-schedule')cxResponse(200,$service->previewSchedule($company,$p));
    if($action==='save-schedule')cxResponse(200,$service->saveSchedule($company,$p));
    if($action==='confirm-schedule')cxResponse(200,$service->confirmSchedule($company,$p));
    if($action==='cancel-schedule')cxResponse(200,$service->cancelSchedule($company,$p));
    if($action==='configure')cxResponse(200,$service->configure($company,$p));
    if($action==='fiscal-settings')cxResponse(200,$service->saveFiscalSettings($company,$p));
    if($action==='issuer-profile')cxResponse(200,$service->issuerProfile($company,$p));
        if($action==='synchronize')cxResponse(200,$service->synchronize($company));
        if($action==='reference')cxResponse(200,$service->assignReference($company,$p));
        if($action==='member')cxResponse(200,$service->member($company,$p));
        if($action==='work')cxResponse(201,$service->createWork($company,$p));
        if($action==='specification')cxResponse(201,$service->createSpecification($company,$p));
        if($action==='proposal')cxResponse(201,$service->createProposal($company,$p));
        if($action==='proposal-from-pos')cxResponse(201,$service->createPosProposal($company,$p));
        if($action==='issue')cxResponse(200,$service->issueProposal($company,$p));
        if($action==='accept')cxResponse(200,$service->acceptProposal($company,$p));
    }
    throw new CxError('Acción no disponible en esta etapa.',404);
} catch(CxError $e) {cxResponse($e->status,['error'=>$e->getMessage()]);}
catch(Throwable $e) {
    $reference=substr(hash('sha256',uniqid('',true)),0,12);error_log('[Concretera '.$reference.'] '.$e->getMessage());
    $code=$e instanceof PDOException?(int)($e->errorInfo[1]??0):0;
    if($code===1062)cxResponse(409,['error'=>'Ya existe un código o registro con esos datos. No se duplicó la operación.','reference'=>$reference]);
    if(in_array($code,[1205,1213],true))cxResponse(409,['error'=>'Otro usuario está confirmando datos. Conserve el formulario y vuelva a consultar antes de reintentar.','reference'=>$reference]);
    cxResponse(500,['error'=>'No se pudo completar la operación. Los datos del formulario se conservan.','reference'=>$reference]);
}
