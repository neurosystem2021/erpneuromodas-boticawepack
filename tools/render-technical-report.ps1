$ErrorActionPreference='Stop'
$fixtureDoc=[IO.Path]::GetFullPath((Join-Path $PSScriptRoot '..\output\technical-report-fixture.docx'))
$fixturePdf=[IO.Path]::ChangeExtension($fixtureDoc,'.pdf')
$renderWord=$null;$renderDocument=$null
try{$renderWord=New-Object -ComObject Word.Application;$renderWord.Visible=$false;$renderWord.DisplayAlerts=0;$renderWord.AutomationSecurity=3;$renderDocument=$renderWord.Documents.Open($fixtureDoc,$false,$true);$renderDocument.ExportAsFixedFormat($fixturePdf,17);Write-Output 'Technical report fixture rendered to PDF with Word.'}finally{if($renderDocument){$renderDocument.Close(0);[void][Runtime.InteropServices.Marshal]::ReleaseComObject($renderDocument)};if($renderWord){try{$renderWord.Quit()}catch{if($_.Exception.Message -notmatch 'RPC'){throw}};[void][Runtime.InteropServices.Marshal]::ReleaseComObject($renderWord)}}
