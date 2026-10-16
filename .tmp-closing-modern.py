from pathlib import Path
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8');s+='''
@media screen{
 .nst-screen--cierrecaja{padding:22px!important;background:linear-gradient(145deg,#f2f7fc,#f8fafc 55%,#f0f4ff);color:#1b3c51}
 .nst-screen--cierrecaja>.nst-panel{padding:0!important;background:transparent!important;border:0!important;box-shadow:none!important}
 .nst-screen--cierrecaja .card{border:1px solid #dce8ef!important;border-radius:20px!important;overflow:hidden;box-shadow:0 8px 26px #1c405510!important;background:white}
 .nst-screen--cierrecaja .card>center{padding:20px 22px;background:linear-gradient(110deg,#10384e,#17657a);text-align:left!important}
 .nst-screen--cierrecaja .card>center h4{margin:0!important;color:#fff!important;font-size:20px;font-weight:800;letter-spacing:.4px}
 .nst-screen--cierrecaja .card-header{background:#f7fafc!important;padding:12px!important;border-bottom:1px solid #e4edf3!important}
 .nst-screen--cierrecaja .nav-pills{gap:7px;background:transparent!important;border:0!important;padding:0!important}
 .nst-screen--cierrecaja .nav-pills .nav-link{border-radius:11px!important;padding:11px 16px!important;font-weight:700;background:white;color:#557286;border:1px solid #e3edf4}
 .nst-screen--cierrecaja .nav-pills .nav-link.active{background:linear-gradient(110deg,#067c96,#137bce)!important;color:#fff!important;box-shadow:0 4px 12px #097e9622}
 .nst-screen--cierrecaja .tab-content{padding:5px 2px 12px}
 .nst-screen--cierrecaja .tab-pane h5{font-size:17px;font-weight:800;margin:20px 0 8px;padding:11px 14px;background:#eef5fa;border-left:4px solid #1486a1;border-radius:8px;color:#244e65}
 .nst-screen--cierrecaja .tab-pane h5+br{display:none}
 .nst-screen--cierrecaja .nst-table-scroll{border:1px solid #dce8ef;border-radius:13px;margin-bottom:16px;box-shadow:0 3px 12px #20425608}
 .nst-screen--cierrecaja .table{margin-bottom:0!important;font-size:12px}
 .nst-screen--cierrecaja .table thead th{background:#173f54!important;color:white;padding:12px 10px!important;font-weight:700}
 .nst-screen--cierrecaja .table td{padding:12px 10px!important;vertical-align:middle;border-color:#e5edf3!important}
 .nst-screen--cierrecaja .table tbody tr:nth-child(even)>td{background:#f8fbfd}
 .nst-screen--cierrecaja .table tfoot .total{background:#e4f6ef!important;color:#08775d!important;font-weight:800}
 .nst-screen--cierrecaja>.nst-panel>.row>.col-xl-3{align-self:flex-start;padding:18px!important;background:#fff;border:1px solid #dce8ef;border-radius:20px;box-shadow:0 8px 28px #173c5312}
 .nst-screen--cierrecaja h2{font-size:25px;font-weight:800;color:#153e55;margin:0 0 18px}
 .nst-screen--cierrecaja .btn{border-radius:10px!important;font-size:12px!important;font-weight:700!important;padding:10px 12px!important;box-shadow:0 3px 10px #183a5010}
 .nst-screen--cierrecaja .form-control,.nst-screen--cierrecaja .custom-select{border:1px solid #d5e4ef;border-radius:10px;min-height:44px;background:#f8fbfd;color:#244d64;font-weight:600}
 .nst-screen--cierrecaja tr[class*="closing-summary-"]>td{background:var(--closing-bg)!important;color:var(--closing-color)!important}
 .nst-screen--cierrecaja .table:has(tr.closing-summary-grand){border-radius:12px;overflow:hidden;margin-top:14px}
}
@media screen and (max-width:767.98px){.nst-screen--cierrecaja{padding:10px!important}.nst-screen--cierrecaja h2{font-size:23px}.nst-screen--cierrecaja .card>center{padding:16px}.nst-screen--cierrecaja .card>center h4{font-size:17px}.nst-screen--cierrecaja .nav-pills .nav-link{padding:9px!important;font-size:12px}.nst-screen--cierrecaja .nst-table-scroll{overflow-x:auto}}
''';p.write_text(s,encoding='utf-8')
