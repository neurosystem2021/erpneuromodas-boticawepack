<?php
require_once dirname(__DIR__) . '/models/EmpresaConfig.php';
$storeConfig = empresaConfigObtener();
$storeFallbackLogo = 'resources/images/neurosoft-logo-transparent.png';
$storeLogo = '/' . ltrim(empresaConfigValor($storeConfig, 'logo_principal', $storeFallbackLogo), '/\\');
$storeLogo .= (strpos($storeLogo, '?') === false ? '?' : '&') . 'v=20261007-eden-1';
$storeName = empresaConfigValor($storeConfig, 'nombre_comercial', empresaConfigValor($storeConfig, 'RAZONSOCIAL', 'NEUROSOFT'));
?>
<?php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob: https:; connect-src 'self'; font-src 'self' data:; object-src 'none'; base-uri 'self'; frame-ancestors 'self'; form-action 'self'; upgrade-insecure-requests");
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Tienda virtual</title>
  <style>
    :root{--purple:#123f51;--purple-dark:#082f40;--purple-soft:#f4edf5;--ink:#201526;--muted:#746879;--green:#079e69;--line:#eadfe9}
    *{box-sizing:border-box}body{margin:0;font-family:Inter,"Segoe UI",sans-serif;background:#f7f3f7;color:var(--ink)}button,input{font:inherit}button{cursor:pointer}
    header{background:linear-gradient(120deg,var(--purple-dark),var(--purple) 58%,#78477f);color:#fff;padding:24px max(5vw,20px) 68px}.head{max-width:1280px;margin:auto;display:flex;justify-content:space-between;gap:20px;align-items:center}.brand{display:flex;align-items:center;gap:16px}.brand-logo{width:138px;max-height:70px;object-fit:contain;border-radius:9px;box-shadow:0 8px 24px #16091d55}.brand h1{margin:0;font-size:clamp(23px,3vw,36px)}.brand p{margin:6px 0 0;opacity:.86}.cart-btn{border:1px solid #ffffff55;background:#ffffff18;color:#fff;border-radius:14px;padding:13px 17px;font-weight:800}
    .tools{max-width:1180px;margin:-40px auto 24px;background:#fff;border-radius:20px;padding:16px;box-shadow:0 16px 42px #4217491c;display:grid;grid-template-columns:1fr auto auto auto;gap:10px}.search-wrap{position:relative}.search-wrap input{width:100%;height:50px;border:1px solid #d9ccd9;border-radius:13px;padding:0 46px 0 16px;font-size:16px;outline:0}.search-wrap input:focus{border-color:#87518f;box-shadow:0 0 0 4px #87518f18}.clear-search{position:absolute;right:8px;top:8px;width:34px;height:34px;border:0;border-radius:9px;background:#f3edf4;color:#6d5271}.search-button{border:0;border-radius:12px;padding:0 20px;background:var(--purple);color:#fff;font-weight:800}.voice-search{display:grid;width:50px;height:50px;padding:0;place-items:center;border:0;border-radius:12px;color:#fff;background:linear-gradient(145deg,#a8172a,#ee3d34);box-shadow:0 9px 20px #b7202d48;transition:background .2s,box-shadow .2s,transform .2s}.voice-search svg{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}.voice-search.listening{background:linear-gradient(145deg,#008b68,#19c58e);box-shadow:0 0 0 5px #12b88638,0 10px 25px #087c5c66;animation:voicePulse 1s ease-in-out infinite}.voice-search:disabled{cursor:not-allowed;opacity:.55}@keyframes voicePulse{50%{transform:scale(1.08);box-shadow:0 0 0 10px #12b88625,0 10px 25px #087c5c66}}.result-label{align-self:center;color:var(--muted);font-size:13px;white-space:nowrap}.cats-nav{display:grid;min-width:0;grid-column:1/-1;grid-template-columns:38px minmax(0,1fr) 38px;align-items:center;gap:7px}.cats-arrow{display:grid;width:38px;height:38px;padding:0;place-items:center;border:0;border-radius:50%;color:#fff;background:linear-gradient(135deg,#421549,#702a7a);box-shadow:0 6px 14px #55205f35;font-size:24px;line-height:1}.cats-arrow:hover{transform:scale(1.06)}.cats{display:flex;min-width:0;gap:8px;overflow-x:auto;overflow-y:hidden;padding:3px 0;scroll-behavior:smooth;scrollbar-width:none;-ms-overflow-style:none}.cats::-webkit-scrollbar{display:none;width:0;height:0}.cats button{border:0;white-space:nowrap;border-radius:99px;padding:9px 14px;background:#f4e9ec;color:#633042;font-weight:700}.cats button.on{background:var(--purple);color:#fff}
    .grid{max-width:1180px;margin:0 auto 80px;padding:0 12px;display:grid;grid-template-columns:repeat(auto-fill,minmax(245px,1fr));gap:18px}.card{background:#fff;border-radius:19px;overflow:hidden;box-shadow:0 8px 26px #33111b10;border:1px solid var(--line);transition:.25s}.card:hover{transform:translateY(-4px);box-shadow:0 16px 35px #33111b20}.photo{height:280px;background:linear-gradient(145deg,#fff,#f2e8ef);display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;padding:14px}.photo img{width:100%;height:100%;object-fit:contain;transition:transform .3s}.card:hover .photo img{transform:scale(1.035)}.zoom{position:absolute;right:12px;bottom:12px;border:0;border-radius:50%;background:#fff;color:var(--purple);width:42px;height:42px;box-shadow:0 6px 18px #0002;font-size:21px}.photo-count{position:absolute;left:12px;bottom:12px;background:#25182ad9;color:#fff;padding:6px 9px;border-radius:99px;font-size:11px;font-weight:800}.info{padding:17px;display:flex;flex-direction:column;min-height:235px}.meta{font-size:12px;color:#836a83;text-transform:uppercase;font-weight:800}.info h3{margin:7px 0 5px;font-size:18px}.summary{font-size:13px;color:#514655;line-height:1.45;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:38px}.details{font-size:12px;color:#796f7d;margin-top:8px}.bottom{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:auto;padding-top:14px}.price{font-size:22px;font-weight:900;color:var(--purple)}.add{border:0;border-radius:11px;background:var(--green);color:#fff;padding:11px 14px;font-weight:850;box-shadow:0 7px 16px #079e6925}.add:hover{background:#087e59}.empty{grid-column:1/-1;text-align:center;padding:70px;color:#718092}
    .drawer,.product-modal{position:fixed;inset:0;background:#211426bd;z-index:30;visibility:hidden;opacity:0;pointer-events:none;transition:.22s}.drawer.open,.product-modal.open{visibility:visible;opacity:1;pointer-events:auto}.drawer{display:flex;justify-content:flex-end}.panel{width:min(440px,100%);height:100%;background:#fff;padding:20px;display:flex;flex-direction:column;border-left:5px solid var(--purple);transform:translateX(102%);transition:.28s}.drawer.open .panel{transform:none}.panel-head{display:flex;justify-content:space-between;align-items:center}.panel-head h2{color:#44204d}.close{border:0;background:#f0eaf2;color:#4b2454;border-radius:10px;padding:9px 12px}.items{flex:1;overflow:auto}.item{display:grid;grid-template-columns:62px 1fr auto;gap:10px;align-items:center;padding:13px 0;border-bottom:1px solid #e7e9ec}.item img{width:62px;height:68px;object-fit:contain;border-radius:9px;background:#f5eff4}.item small{color:#6d8192}.qty button{border:0;border-radius:7px;padding:5px 9px}.checkout{border:0;border-radius:13px;padding:15px;background:linear-gradient(120deg,#472052,#704079);color:#fff;font-weight:900;font-size:16px}.total{font-size:24px;font-weight:900;display:flex;justify-content:space-between;padding:16px 0;color:#32183a}
    .floating-cart{position:fixed;right:22px;bottom:22px;z-index:18;border:1px solid #ffffff77;background:linear-gradient(135deg,#3a1844,#6a3674);color:#fff;border-radius:999px;padding:12px 17px;display:flex;align-items:center;gap:9px;font-weight:900;box-shadow:0 10px 30px #35133f66}.floating-cart b{min-width:24px;height:24px;padding:0 6px;border-radius:99px;background:#fff;color:#123f51;display:grid;place-items:center}.cart-has-items .floating-cart{animation:cartPulse 1.55s ease-in-out infinite}@keyframes cartPulse{50%{transform:scale(1.055);box-shadow:0 10px 34px #7d4a88aa,0 0 0 7px #ffffff26}}
    .cart-btn{position:relative;display:flex;align-items:center;gap:11px;min-width:174px;padding:9px 12px 9px 9px;cursor:pointer;background:linear-gradient(135deg,#ffffff22,#ffffff0d);box-shadow:0 9px 24px #250c3066,inset 0 1px #ffffff44;transition:.22s ease}.cart-btn:hover{transform:translateY(-2px);border-color:#fff;box-shadow:0 13px 30px #250c3080}.cart-icon,.floating-icon{width:38px;height:38px;flex:0 0 38px;border-radius:12px;display:grid;place-items:center;background:linear-gradient(145deg,#fff,#eadced);color:#5c2167;box-shadow:0 5px 14px #210a2d55}.cart-icon svg,.floating-icon svg,.add-icon svg{width:23px;height:23px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}.cart-label{display:flex;flex-direction:column;align-items:flex-start;line-height:1.05}.cart-label strong{font-size:14px}.cart-label small{font-size:10px;margin-top:4px;opacity:.9}.cart-badge{position:absolute;right:-8px;top:-9px;min-width:27px;height:27px;padding:0 7px;border:3px solid #74447d;border-radius:99px;background:#fff;color:#5a1d65;display:grid;place-items:center;font-size:12px;box-shadow:0 5px 13px #22082d66}.add{position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;gap:10px;min-height:53px;padding:8px 15px;background:linear-gradient(135deg,#087c5c,#08ae79);box-shadow:0 8px 18px #087a5540;transition:.2s ease}.add::before{content:'';position:absolute;inset:-80% auto -80% -45%;width:34%;transform:rotate(18deg);background:#ffffff35;transition:left .48s ease}.add:hover{transform:translateY(-2px);filter:saturate(1.08);box-shadow:0 12px 24px #087a5560}.add:hover::before{left:115%}.add:active{transform:scale(.96)}.add-icon{width:35px;height:35px;flex:0 0 35px;border-radius:11px;display:grid;place-items:center;background:#ffffff1f;border:1px solid #ffffff35}.add-icon svg{width:21px;height:21px}.add-copy{line-height:1.05;text-align:left}.add-copy strong{display:block;font-size:14px}.add-copy small{display:block;margin-top:4px;font-size:9px;opacity:.8}.add-count{display:grid;min-width:24px;height:24px;padding:0 6px;place-items:center;border-radius:99px;color:#087c5c;background:#fff;font-size:12px;font-weight:900}.floating-cart{min-width:185px;padding:8px 13px 8px 8px;gap:10px;cursor:pointer;transition:.22s ease}.floating-copy{display:flex;flex:1;flex-direction:column;align-items:flex-start;line-height:1}.floating-copy small{font-size:9px;opacity:.76;margin-bottom:4px}.floating-copy strong{font-size:13px}.cart-has-items .floating-cart{animation:none}.cart-has-items .cart-icon svg,.cart-has-items .floating-icon svg{animation:cartRoll 2.8s ease-in-out infinite}.cart-bump{animation:cartBump .55s ease!important}.just-added .add-icon{animation:iconHop .55s ease}.just-added{filter:brightness(1.12)}@keyframes cartRoll{0%,82%,100%{transform:translateX(0) rotate(0)}88%{transform:translateX(3px) rotate(4deg)}94%{transform:translateX(-2px) rotate(-3deg)}}@keyframes cartBump{40%{transform:translateY(-6px) rotate(-7deg) scale(1.12)}70%{transform:translateY(1px) rotate(3deg)}}@keyframes iconHop{45%{transform:translateY(-5px) scale(1.12)}}
    .product-modal.open{display:grid;place-items:center;padding:22px}.product-box{width:min(1080px,100%);max-height:94vh;background:#fff;border-radius:22px;overflow:auto;box-shadow:0 28px 80px #0008}.product-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--line)}.product-head strong{font-size:17px}.product-content{display:grid;grid-template-columns:minmax(0,1.08fr) minmax(330px,.92fr);gap:28px;padding:24px}.visual-main{height:530px;border-radius:17px;background:#f8f5f7;display:grid;place-items:center;position:relative;overflow:hidden;padding:20px}.visual-main img{width:100%;height:100%;object-fit:contain}.nav-arrow{position:absolute;top:50%;transform:translateY(-50%);width:43px;height:43px;border:0;border-radius:50%;background:#fff;color:var(--purple);font-size:25px;box-shadow:0 5px 20px #0003}.nav-arrow.prev{left:13px}.nav-arrow.next{right:13px}.thumbs{display:flex;gap:9px;padding-top:10px;overflow:auto}.thumbs button{flex:0 0 78px;height:78px;padding:4px;border:2px solid transparent;border-radius:11px;background:#f2edf2}.thumbs button.on{border-color:var(--purple)}.thumbs img{width:100%;height:100%;object-fit:contain}.product-detail{padding:8px 4px}.product-kicker{font-size:12px;color:#865f83;text-transform:uppercase;font-weight:850}.product-detail h2{font-size:30px;margin:9px 0}.product-price{font-size:31px;font-weight:900;color:var(--purple);padding-bottom:17px;border-bottom:1px solid var(--line)}.product-description{color:#5f5361;line-height:1.65;margin:18px 0;white-space:pre-line}.attributes{display:grid;grid-template-columns:repeat(2,1fr);gap:9px;margin:18px 0}.attribute{background:#f7f3f7;border:1px solid var(--line);border-radius:11px;padding:10px}.attribute small{display:block;color:#8a7b8c;font-size:10px;text-transform:uppercase;font-weight:800}.attribute b{font-size:13px}.stock{color:#087e59;font-weight:800;margin:13px 0}.purchase{display:grid;grid-template-columns:130px 1fr;gap:10px;margin-top:18px}.quantity{display:grid;grid-template-columns:38px 1fr 38px;border:1px solid #d9ccd9;border-radius:12px;overflow:hidden}.quantity button{border:0;background:#f2edf3;font-size:20px}.quantity span{display:grid;place-items:center;font-weight:900}.detail-add{border:0;border-radius:12px;background:var(--green);color:#fff;font-weight:900;font-size:15px}.detail-add:hover{background:#087e59}
    @media(max-width:700px){header{padding:17px 14px 58px}.brand{gap:10px}.brand-logo{width:90px}.brand h1{font-size:18px}.brand p{font-size:11px}.cart-btn{padding:9px;font-size:12px}.tools{margin-left:10px;margin-right:10px;grid-template-columns:1fr auto auto}.result-label{display:none}.search-button{padding:0 12px}.grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.photo{height:205px;padding:8px}.info{padding:11px;min-height:225px}.info h3{font-size:15px}.summary{font-size:11px}.details{font-size:10px}.bottom{display:block}.price{display:block;margin-bottom:8px}.add{width:100%;padding:9px;font-size:12px}.panel{border-left:0;border-top:5px solid var(--purple)}.product-modal.open{padding:0}.product-box{height:100%;max-height:none;border-radius:0}.product-content{grid-template-columns:1fr;padding:13px;gap:8px}.visual-main{height:330px}.product-detail h2{font-size:23px}.product-price{font-size:25px}.product-description{font-size:14px;margin:12px 0}.attributes{margin:12px 0}.purchase{position:sticky;bottom:0;background:#fff;padding:10px 0;z-index:2}.floating-cart{right:12px;bottom:14px}.photo-count{font-size:9px}}
    /* Acabado premium: conserva la estructura y mejora jerarquia, tacto y contraste. */
    :root{--purple:#55205f;--purple-dark:#24102c;--purple-mid:#74417d;--gold:#e7bd69;--gold-soft:#fff2cf;--green:#078b69;--green-dark:#056b53;--ink:#241b2a}
    body{background:radial-gradient(circle at 12% 8%,rgba(126,67,139,.10),transparent 28%),radial-gradient(circle at 90% 20%,rgba(231,189,105,.11),transparent 25%),linear-gradient(180deg,#fbf8fb 0%,#f5f0f6 100%)}
    header{position:relative;overflow:hidden;background:linear-gradient(118deg,#24102c 0%,#4b1755 48%,#7a4781 100%);box-shadow:0 18px 46px rgba(47,19,55,.22)}
    header::before{content:"";position:absolute;inset:-60% 35% -80% -10%;background:radial-gradient(circle,rgba(255,255,255,.13),transparent 62%);transform:rotate(-12deg);pointer-events:none}
    header::after{content:"";position:absolute;right:-90px;top:-100px;width:320px;height:320px;border:1px solid rgba(255,255,255,.11);border-radius:50%;box-shadow:0 0 0 42px rgba(255,255,255,.035),0 0 0 88px rgba(255,255,255,.025);pointer-events:none}
    .head{position:relative;z-index:1}
    .brand-logo{padding:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.13);border-radius:16px;box-shadow:0 12px 32px rgba(15,4,18,.2)}
    .tools{position:relative;border:1px solid rgba(85,32,95,.10);box-shadow:0 24px 56px rgba(64,35,69,.13);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px)}
    .tools::before{content:"";position:absolute;left:28px;right:28px;top:0;height:3px;border-radius:0 0 999px 999px;background:linear-gradient(90deg,transparent,var(--gold),var(--purple-mid),transparent)}
    .search-wrap{gap:12px}
    .search-wrap input{border:1px solid #ded3e0;box-shadow:inset 0 1px 0 #fff,0 6px 18px rgba(58,27,63,.05);transition:border-color .2s ease,box-shadow .2s ease,transform .2s ease}
    .search-wrap input:focus{border-color:#8c5a94;box-shadow:0 0 0 4px rgba(116,65,125,.13),0 10px 24px rgba(58,27,63,.08)}
    .search-button,.checkout,.detail-add{position:relative;overflow:hidden;display:inline-flex;align-items:center;justify-content:center;gap:9px;border:0;isolation:isolate}
    .search-button{background:linear-gradient(135deg,#401448,#692675);box-shadow:0 11px 22px rgba(71,26,80,.23)}
    .search-button svg,.checkout svg,.detail-add svg{width:20px;height:20px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
    .search-button::after,.checkout::after,.detail-add::after,.add::after{content:"";position:absolute;inset:-120% auto -120% -45%;width:34%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.38),transparent);transform:skewX(-18deg);transition:left .55s ease;pointer-events:none}
    .search-button:hover::after,.checkout:hover::after,.detail-add:hover::after,.add:hover::after{left:115%}
    .search-button:hover,.checkout:hover,.detail-add:hover,.add:hover{transform:translateY(-2px);filter:saturate(1.06)}
    .search-button:active,.checkout:active,.detail-add:active,.add:active{transform:translateY(0) scale(.97)}
    button:focus-visible,input:focus-visible{outline:3px solid rgba(231,189,105,.72);outline-offset:3px}
    .cats button{border:1px solid transparent;background:linear-gradient(#f8eef0,#f8eef0) padding-box,linear-gradient(135deg,rgba(85,32,95,.12),rgba(231,189,105,.3)) border-box;box-shadow:0 5px 13px rgba(76,39,80,.05);transition:transform .2s ease,box-shadow .2s ease,color .2s ease}
    .cats button:hover{transform:translateY(-2px);box-shadow:0 9px 19px rgba(76,39,80,.12);color:var(--purple)}
    .cats button.active{background:linear-gradient(135deg,#421549,#702a7a);border-color:transparent;box-shadow:0 9px 22px rgba(73,25,82,.24)}
    .card{position:relative;isolation:isolate;border:1px solid rgba(77,42,82,.10);box-shadow:0 12px 30px rgba(60,34,64,.09);transition:transform .28s cubic-bezier(.2,.8,.2,1),box-shadow .28s ease,border-color .28s ease}
    .card::before{content:"";position:absolute;z-index:2;top:0;left:22px;right:22px;height:3px;border-radius:0 0 999px 999px;background:linear-gradient(90deg,var(--gold),#f2d99d,var(--purple-mid));opacity:0;transform:scaleX(.4);transition:.28s ease}
    .card:hover{transform:translateY(-7px);border-color:rgba(93,39,102,.22);box-shadow:0 24px 46px rgba(56,25,63,.16)}
    .card:hover::before{opacity:1;transform:scaleX(1)}
    .photo{overflow:hidden;background:linear-gradient(145deg,#fdf7f9,#f4e9f1)}
    .photo>img{transition:transform .5s cubic-bezier(.2,.8,.2,1),filter .4s ease}
    .card:hover .photo>img{transform:scale(1.035);filter:saturate(1.04)}
    .zoom{display:grid;place-items:center;background:rgba(255,255,255,.94);color:var(--purple);border:1px solid rgba(84,32,94,.12);box-shadow:0 10px 24px rgba(48,22,54,.2);transition:transform .22s ease,background .22s ease,color .22s ease}
    .zoom:hover{transform:translateY(-3px) rotate(-5deg) scale(1.06);background:var(--purple);color:#fff}
    .zoom svg{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round}
    .add{position:relative;overflow:hidden;background:linear-gradient(135deg,var(--green-dark),#10a77e);box-shadow:0 10px 22px rgba(4,119,89,.2);transition:transform .2s ease,box-shadow .2s ease,filter .2s ease}
    .add:hover{box-shadow:0 15px 28px rgba(4,119,89,.28)}
    .cart-top,.floating-cart{box-shadow:0 13px 30px rgba(28,8,33,.24),inset 0 1px 0 rgba(255,255,255,.16);transition:transform .2s ease,box-shadow .2s ease}
    .cart-top:hover,.floating-cart:hover{transform:translateY(-3px) scale(1.02);box-shadow:0 18px 38px rgba(28,8,33,.3),0 0 0 4px rgba(231,189,105,.15)}
    .drawer,.product-modal{backdrop-filter:blur(7px);-webkit-backdrop-filter:blur(7px)}
    .panel{box-shadow:-26px 0 70px rgba(29,11,34,.24)}
    .item{padding:16px;border:1px solid #eee5ef;border-radius:18px;background:linear-gradient(145deg,#fff,#fcf9fc);box-shadow:0 8px 22px rgba(53,29,57,.06)}
    .qty button{border:1px solid #e7dfe9;background:#fff;color:var(--purple);box-shadow:0 5px 12px rgba(51,26,56,.08);transition:.18s ease}
    .qty button:hover{background:var(--purple);color:#fff;transform:translateY(-2px)}
    .checkout{background:linear-gradient(135deg,#087d60,#13aa7e);box-shadow:0 14px 30px rgba(3,126,91,.25)}
    .product-box{border:1px solid rgba(88,41,95,.10);box-shadow:0 34px 80px rgba(36,14,41,.24)}
    .modal-head{background:linear-gradient(135deg,#fff,#fbf7fc);border-bottom:1px solid #eee4f0}
    .nav-arrow{border:1px solid rgba(82,34,91,.12);color:var(--purple);box-shadow:0 10px 25px rgba(45,20,50,.16);transition:.2s ease}
    .nav-arrow:hover{background:var(--purple);color:#fff;transform:translateY(-50%) scale(1.07)}
    .thumb{transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}
    .thumb:hover{transform:translateY(-3px);box-shadow:0 9px 18px rgba(55,27,60,.12)}
    .detail-add{background:linear-gradient(135deg,#087d60,#10a67d);box-shadow:0 12px 25px rgba(4,118,87,.22)}
    @media(max-width:700px){
      header{border-radius:0 0 24px 24px}.head{padding-top:22px}.brand-logo{border-radius:13px}
      .tools{margin-top:-22px;border-radius:22px;padding:18px 14px 15px}.tools::before{left:18px;right:18px}
      .search-wrap{gap:8px}.search-button{min-width:48px;padding:0 14px}.search-button span{display:none}
      .card:hover{transform:none}.add{min-height:50px}.floating-cart{right:14px;bottom:18px}
      .panel{border-radius:24px 0 0 24px}.item{border-radius:15px}.product-box{border-radius:22px 22px 0 0}
    }
    .store-stock{display:inline-flex;align-items:center;gap:6px;margin-top:7px;padding:5px 9px;border-radius:999px;background:#e7f7f1;color:#126b52;font-size:12px;font-weight:800}
    .store-stock.out{background:#fff0f2;color:#b4233c}
    .item-stock{display:block;margin-top:5px;color:#177a5f;font-size:12px;font-weight:800}
    .item-stock.out{color:#b4233c}
    .item.invalid{border-color:#e7a2ad;background:#fff7f8}
    .add:disabled,.detail-add:disabled,.qty button:disabled,.checkout:disabled{opacity:.5;cursor:not-allowed;filter:grayscale(.2);transform:none!important;box-shadow:none!important}
    .store-toast{position:fixed;left:50%;bottom:28px;z-index:150;max-width:min(430px,calc(100% - 32px));padding:13px 18px;border-radius:14px;background:#30143a;color:#fff;font-weight:800;text-align:center;box-shadow:0 16px 40px #2e123b4d;opacity:0;pointer-events:none;transform:translate(-50%,18px);transition:.25s ease}
    .store-toast.show{opacity:1;transform:translate(-50%,0)}
    @media(prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
    .customer-tools{display:flex;align-items:center;gap:10px}.account-btn{min-height:54px;padding:9px 14px;border:1px solid #ffffff55;border-radius:14px;color:#fff;background:#ffffff18;font-weight:800}.account-btn small{display:block;margin-top:3px;font-size:9px;opacity:.8}.auth-modal{position:fixed;z-index:10020;inset:0;display:none;padding:18px;place-items:center;background:#211426c9}.auth-modal.open{display:grid}.auth-card{width:min(560px,100%);max-height:94vh;overflow:auto;padding:22px;border-radius:22px;background:#fff;box-shadow:0 25px 70px #16091d66}.auth-head{display:flex;align-items:center;justify-content:space-between}.auth-head h2{margin:0;color:#123f51}.auth-tabs{display:grid;margin:16px 0;grid-template-columns:1fr 1fr;gap:7px}.auth-tabs button{height:42px;border:0;border-radius:11px;color:#633042;background:#f4e9ec;font-weight:800}.auth-tabs button.on{color:#fff;background:#123f51}.auth-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.auth-field{display:flex;flex-direction:column;gap:5px}.auth-field.wide{grid-column:1/-1}.auth-field label{font-size:11px;font-weight:800;color:#624b68}.auth-field input,.auth-field select{height:44px;padding:0 12px;border:1px solid #d9ccd9;border-radius:11px}.document-row{display:grid;grid-template-columns:110px 1fr auto;gap:7px}.document-row button{border:0;border-radius:11px;color:#fff;background:#087c5c;font-weight:800}.password-wrap{position:relative}.password-wrap input{width:100%;padding-right:48px}.password-eye{position:absolute;top:4px;right:4px;display:grid;width:38px;height:36px;padding:0;place-items:center;border:0;border-radius:9px;color:#123f51;background:#f2eaf3}.password-eye svg{width:21px;height:21px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}.auth-submit{width:100%;height:48px;margin-top:14px;border:0;border-radius:12px;color:#fff;background:linear-gradient(135deg,#421549,#702a7a);font-weight:900}.auth-message{min-height:18px;margin:8px 0;color:#b41639;font-size:12px;font-weight:700}.auth-message.ok{color:#087c5c}.auth-register-only[hidden]{display:none}.customer-contact{margin:10px 0;padding:10px;border-radius:11px;color:#305c4c;background:#e9f8f1;font-size:12px}@media(max-width:700px){.customer-tools{gap:5px}.account-btn{max-width:105px;padding:7px 9px;font-size:10px}.auth-grid{grid-template-columns:1fr}.auth-field.wide{grid-column:auto}.document-row{grid-template-columns:82px 1fr}.document-row button{grid-column:1/-1;height:40px}}
    @media(min-width:1200px){.tools,.grid{max-width:1460px}.grid{grid-template-columns:repeat(5,minmax(0,1fr));gap:16px}.photo{height:250px}.info{padding:15px}.info h3{font-size:16px}.price{font-size:19px}.add{padding:10px 11px}}
  </style>
  <link rel="stylesheet" href="/resources/css/neurollantas-store.css?v=20261002-5">
<link rel="stylesheet" href="/resources/css/imceden-nst-theme.css?v=20261010">
<link rel="stylesheet" href="/resources/css/neurosystem-store.css?v=20261010-3">
</head>
<body>
<header><div class="head"><div class="brand"><img id="brandLogo" class="brand-logo" src="<?php echo htmlspecialchars($storeLogo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8'); ?>"><div><h1 id="brand"><?php echo htmlspecialchars($storeName, ENT_QUOTES, 'UTF-8'); ?></h1><p id="tagline">COMPUTADORAS · LAPTOPS · ACCESORIOS</p></div></div><div class="customer-tools"><button id="accountButton" type="button" class="account-btn" onclick="openAuth()">Ingresar<small>o registrarse</small></button><button type="button" class="cart-btn" onclick="toggleCart(true)" aria-label="Abrir mi carrito"><span class="cart-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l2.1 10.1a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 7H6"/><circle cx="9" cy="19" r="1.4"/><circle cx="18" cy="19" r="1.4"/></svg></span><span class="cart-label"><strong>Mi carrito</strong><small id="headerCartTotal">S/ 0.00</small></span><span class="cart-badge" id="count">0</span></button></div></div></header>
<section class="tools"><div class="search-wrap"><input id="search" autocomplete="off" placeholder="Buscar nombre, modelo, marca, descripción o código..."><button class="clear-search" id="clearSearch" aria-label="Limpiar">&times;</button></div><button class="search-button" onclick="loadProducts()"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6"></circle><path d="m16 16 4 4"></path></svg><span>Buscar</span></button><button id="voiceSearch" class="voice-search" type="button" onclick="toggleVoiceSearch()" title="Buscar por voz" aria-label="Buscar por voz" aria-pressed="false"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="3" width="6" height="11" rx="3"></rect><path d="M5.5 11a6.5 6.5 0 0 0 13 0M12 17.5V21M9 21h6"></path></svg></button><span class="result-label" id="resultLabel"></span><div class="cats-nav"><button class="cats-arrow" type="button" onclick="scrollCategories(-1)" aria-label="Categorías anteriores">‹</button><div class="cats" id="cats"></div><button class="cats-arrow" type="button" onclick="scrollCategories(1)" aria-label="Más categorías">›</button></div></section>
<main class="grid" id="grid"><div class="empty">Cargando catálogo...</div></main>
<div class="drawer" id="drawer" onclick="if(event.target===this)toggleCart(false)"><aside class="panel"><div class="panel-head"><h2>Mi carrito</h2><button class="close" onclick="toggleCart(false)">&times;</button></div><div class="items" id="items"></div><div class="total"><span>Total</span><span id="total">S/ 0.00</span></div><button class="checkout" id="checkoutBtn" onclick="checkout()"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11.5a8.4 8.4 0 0 1-9 8.3 9 9 0 0 1-3.8-1.2L3 20l1.4-4.1A8.4 8.4 0 1 1 20 11.5Z"></path><path d="M8.2 8.4c.4 3.2 2.1 4.9 5.3 5.4"></path></svg><span id="checkoutLabel">Enviar pedido</span></button></aside></div>
<div class="product-modal" id="productModal" onclick="if(event.target===this)closeProduct()"><section class="product-box"><div class="product-head"><strong>Detalle del producto</strong><button class="close" onclick="closeProduct()">&times;</button></div><div class="product-content"><div><div class="visual-main"><img id="detailImage" alt="Producto"><button class="nav-arrow prev" onclick="stepGallery(-1)">&#8249;</button><button class="nav-arrow next" onclick="stepGallery(1)">&#8250;</button></div><div class="thumbs" id="thumbs"></div></div><div class="product-detail"><div class="product-kicker" id="detailKicker"></div><h2 id="detailTitle"></h2><div class="product-price" id="detailPrice"></div><div class="product-description" id="detailDescription"></div><div class="attributes" id="detailAttributes"></div><div class="stock" id="detailStock"></div><div class="purchase"><div class="quantity"><button onclick="changeDetailQty(-1)">&minus;</button><span id="detailQty">1</span><button onclick="changeDetailQty(1)">+</button></div><button class="detail-add" onclick="addFromDetail()"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.2 10h9.6l2-7H6"></path><circle cx="9" cy="19" r="1"></circle><circle cx="17" cy="19" r="1"></circle></svg><span>Agregar al carrito</span></button></div></div></div></section></div>
<button type="button" class="floating-cart" onclick="toggleCart(true)" aria-label="Ver mi cesta"><span class="floating-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l2.1 10.1a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 7H6"/><circle cx="9" cy="19" r="1.4"/><circle cx="18" cy="19" r="1.4"/></svg></span><span class="floating-copy"><small>Tu compra</small><strong id="floatingCartTotal">S/ 0.00</strong></span><b id="floatingCount">0</b></button>
<div id="authModal" class="auth-modal" onclick="if(event.target===this)closeAuth()"><form class="auth-card" novalidate onsubmit="submitAuth(event)"><div class="auth-head"><h2 id="authTitle">Ingresar</h2><button class="close" type="button" onclick="closeAuth()">×</button></div><div class="auth-tabs"><button id="loginTab" class="on" type="button" onclick="setAuthMode('login')">Ingresar</button><button id="registerTab" type="button" onclick="setAuthMode('register')">Registrarse</button></div><div class="auth-grid"><div class="auth-field wide"><label>Documento</label><div class="document-row"><select id="authDocType"><option value="DNI">DNI</option><option value="CE">Extranjería</option><option value="RUC">RUC</option></select><input id="authDocument" maxlength="12" placeholder="Número de documento"><button id="lookupDocumentBtn" type="button" onclick="lookupDocument()">Consultar RENIEC / SUNAT</button></div></div><div class="auth-field auth-register-only wide"><label>Nombre completo o razón social</label><input id="authName"></div><div class="auth-field auth-register-only wide"><label>Dirección</label><input id="authAddress"></div><div class="auth-field auth-register-only"><label>Teléfono</label><input id="authPhone" type="tel"></div><div class="auth-field auth-register-only"><label>Correo</label><input id="authEmail" type="email"></div><div class="auth-field wide"><label>Contraseña</label><div class="password-wrap"><input id="authPassword" type="password" minlength="8" autocomplete="current-password"><button class="password-eye" type="button" onclick="togglePassword('authPassword',this)" aria-label="Ver contraseña">◉</button></div></div><div class="auth-field auth-register-only wide"><label>Confirmar contraseña</label><div class="password-wrap"><input id="authPasswordConfirm" type="password" minlength="8" autocomplete="new-password"><button class="password-eye" type="button" onclick="togglePassword('authPasswordConfirm',this)" aria-label="Ver confirmación de contraseña">◉</button></div></div></div><div id="authMessage" class="auth-message"></div><button id="authSubmit" class="auth-submit" type="submit">Ingresar</button></form></div>
  <div class="store-toast" id="storeToast" role="status" aria-live="polite"></div>
  <script>
const API='/api/tienda/index.php',FALLBACK='/resources/images/tienda/producto-sin-imagen.svg?v=20261010-tech';
// El carrito pertenece solamente a la visita actual. No se conserva en el
// navegador para evitar que otro cliente encuentre una compra anterior.
let products=[],catalog=new Map(),category=0,cart={},company={},galleryImages=[],galleryIndex=0,activeProduct=null,detailQuantity=1,searchTimer=0,requestNumber=0,currentCustomer=null,authMode='login',authCheckoutPending=false;
// Elimina compras persistidas por versiones anteriores de la tienda.
try{localStorage.removeItem('tienda_cart')}catch(e){}
const $=id=>document.getElementById(id),money=n=>'S/ '+Number(n||0).toFixed(2),esc=s=>String(s??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const placeholder=FALLBACK;
const productImages=p=>[p.imagen,p.imagen2,p.imagen3].filter(Boolean);function safeImage(img){if(img.complete && img.naturalWidth===0){img.onerror=null;img.src=FALLBACK}else{img.onerror=function(){this.onerror=null;this.src=FALLBACK}}}
function updateAccountButton(){const button=$('accountButton');if(!button)return;button.innerHTML=currentCustomer?`${esc((currentCustomer.nombre||'Cliente').split(' ')[0])}<small>${esc(currentCustomer.telefono||'Mi cuenta')}</small>`:'Ingresar<small>o registrarse</small>'}
async function loadCustomerSession(){try{const data=await fetch(API+'?action=auth-status',{credentials:'same-origin'}).then(r=>r.json());currentCustomer=data.authenticated?data.client:null;updateAccountButton()}catch(e){currentCustomer=null}}
function openAuth(fromCheckout=false){if(currentCustomer&&!fromCheckout){if(confirm(`Sesión: ${currentCustomer.nombre}\nTeléfono: ${currentCustomer.telefono}\n\n¿Desea cerrar sesión?`))logoutCustomer();return}authCheckoutPending=fromCheckout;$('authDocument').value='';setAuthMode('login');let guest=$('authGuest');if(!guest){guest=document.createElement('button');guest.id='authGuest';guest.type='button';guest.className='auth-submit';guest.style.cssText='margin-top:8px;background:linear-gradient(135deg,#087d60,#13aa7e)';guest.textContent='Continuar por WhatsApp sin registrarme';guest.onclick=sendGuestWhatsApp;$('authSubmit').insertAdjacentElement('afterend',guest)}guest.hidden=!fromCheckout;$('authModal').classList.add('open');setTimeout(()=>{$('authEmail').value='';$('authPassword').value='';$('authPasswordConfirm').value='';$('authDocument').focus()},250)}
function closeAuth(){$('authModal').classList.remove('open');$('authMessage').textContent=''}
function eyeIcon(visible=false){return visible?'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 4.2A10.8 10.8 0 0112 4c5.5 0 9 5 9 5a18 18 0 01-2.1 2.5M6.6 6.6C4.3 8.2 3 10 3 10s3.5 5 9 5c1 0 2-.2 2.8-.5"/></svg>':'<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>'}
function protectEmptyField(input){input.value='';input.readOnly=true;input.autocomplete='new-password';input.onfocus=()=>{input.readOnly=false}}
function setAuthMode(mode){authMode=mode;const register=mode==='register';$('authTitle').textContent=register?'Crear cuenta':'Ingresar';$('loginTab').classList.toggle('on',!register);$('registerTab').classList.toggle('on',register);document.querySelectorAll('.auth-register-only').forEach(el=>el.hidden=!register);$('lookupDocumentBtn').hidden=!register;$('authSubmit').textContent=register?'Registrarme':'Ingresar';[$('authEmail'),$('authPassword'),$('authPasswordConfirm')].forEach(protectEmptyField);document.querySelectorAll('.password-eye').forEach(button=>button.innerHTML=eyeIcon(false));$('authPassword').type='password';$('authPasswordConfirm').type='password';$('authMessage').className='auth-message';$('authMessage').textContent=''}
function togglePassword(inputId,button){const input=$(inputId),show=input.type==='password';input.readOnly=false;input.type=show?'text':'password';button.innerHTML=eyeIcon(show);button.setAttribute('aria-label',show?'Ocultar contraseña':'Ver contraseña');input.focus()}
function sendGuestWhatsApp(){const rows=Object.entries(cart).map(([id,q])=>[catalog.get(String(id)),Number(q)]).filter(x=>x[0]);if(!rows.length)return alert('Agrega un producto al carrito.');let phone=String(company.CELULAR||company.TELEFONO||company.whatsapp||'').replace(/\D/g,'');if(!phone)return alert('La empresa no tiene un número de WhatsApp configurado.');if(!phone.startsWith('51'))phone='51'+phone;const total=rows.reduce((sum,[p,q])=>sum+Number(p.precio||0)*q,0),message='Hola, deseo realizar este pedido:\n\n'+rows.map(([p,q])=>`• ${q} x ${p.nombre} (${p.codigo}) - ${money(Number(p.precio||0)*q)}`).join('\n')+'\n\nTotal: '+money(total);closeAuth();open('https://wa.me/'+phone+'?text='+encodeURIComponent(message),'_blank','noopener')}
async function lookupDocument(){const type=$('authDocType').value,documento=$('authDocument').value.replace(/\D/g,'');if(type==='CE'){setAuthMode('register');$('authMessage').textContent='Complete manualmente los datos del carné de extranjería.';$('authName').focus();return}if((type==='DNI'&&documento.length!==8)||(type==='RUC'&&documento.length!==11)){$('authMessage').textContent='Ingrese un DNI de 8 dígitos o RUC de 11 dígitos.';return}setAuthMode('register');const button=$('lookupDocumentBtn');button.disabled=true;button.textContent='Consultando...';try{const response=await fetch(`${API}?action=lookup&documento=${encodeURIComponent(documento)}`),data=await response.json();if(!response.ok)throw new Error(data.error);$('authName').value=data.nombre||'';$('authAddress').value=data.direccion||'';$('authMessage').textContent='Datos encontrados correctamente en '+(type==='RUC'?'SUNAT.':'RENIEC.');$('authPhone').focus()}catch(e){$('authMessage').textContent=e.message||'No se encontró el documento.'}finally{button.disabled=false;button.textContent='Consultar RENIEC / SUNAT'}}
async function submitAuth(event){event.preventDefault();const message=$('authMessage'),payload={tipo:$('authDocType').value,documento:$('authDocument').value.trim(),password:$('authPassword').value,nombre:$('authName').value.trim(),direccion:$('authAddress').value.trim(),telefono:$('authPhone').value.trim(),correo:$('authEmail').value.trim()},confirmation=$('authPasswordConfirm').value,button=$('authSubmit');message.className='auth-message';message.textContent='';if(!payload.documento){message.textContent='Ingrese su número de documento.';$('authDocument').focus();return}if(!payload.password){message.textContent='Ingrese su contraseña.';$('authPassword').focus();return}if(authMode==='register'&&payload.password.length<8){message.textContent='La contraseña debe tener al menos 8 caracteres.';$('authPassword').focus();return}if(authMode==='register'&&payload.password!==confirmation){message.textContent='Las contraseñas no coinciden.';$('authPasswordConfirm').focus();return}button.disabled=true;try{const response=await fetch(`${API}?action=${authMode}`,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}),data=await response.json();if(!response.ok)throw new Error(data.error||'No se pudo completar la operación.');const continueCheckout=authCheckoutPending;currentCustomer=data.client;updateAccountButton();closeAuth();voiceNotice(authMode==='register'?'Cuenta creada correctamente.':'Bienvenido nuevamente.');if(continueCheckout)setTimeout(checkout,150)}catch(e){message.textContent=e.message}finally{button.disabled=false}}
async function logoutCustomer(){await fetch(API+'?action=logout',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:'{}'});currentCustomer=null;updateAccountButton();voiceNotice('Sesión cerrada.')}
async function init(){try{await loadCustomerSession();company=await fetch(API+'?action=empresa').then(r=>r.json());const business=company.nombre_comercial||company.RAZONSOCIAL||'NEUROSOFT';$('brand').textContent='NEUROSYSTEM';$('brandLogo').alt=business;if(company.logo_principal)$('brandLogo').src=company.logo_principal;$('tagline').textContent='COMPUTADORAS · LAPTOPS · ACCESORIOS';const cs=await fetch(API+'?action=categorias').then(r=>r.json());$('cats').innerHTML='<button class="on" data-id="0">Todo</button>'+cs.map(c=>`<button data-id="${c.id}">${esc(c.nombre)} · ${c.cantidad}</button>`).join('');$('cats').onclick=e=>{if(!e.target.dataset.id)return;category=+e.target.dataset.id;[...$('cats').children].forEach(b=>b.classList.toggle('on',b===e.target));loadProducts()};await loadProducts();renderCart()}catch(e){$('grid').innerHTML='<div class="empty">No fue posible cargar el catálogo.</div>'}}
async function loadProducts(){const current=++requestNumber;$('grid').innerHTML='<div class="empty">Buscando productos...</div>';const u=new URL(API,location.origin);u.searchParams.set('q',$('search').value.trim());u.searchParams.set('categoria',category);try{const data=await fetch(u).then(r=>r.json());if(current!==requestNumber)return;products=Array.isArray(data)?data:[];products.forEach(p=>catalog.set(String(p.id),p));$('resultLabel').textContent=products.length+' resultado'+(products.length===1?'':'s');$('grid').innerHTML=products.length?products.map(cardTemplate).join(''):'<div class="empty">No encontramos productos con esos datos.</div>';renderCart()}catch(e){if(current===requestNumber)$('grid').innerHTML='<div class="empty">No fue posible completar la búsqueda.</div>'}}
function storePriceHtml(p){const old=Number(p.precio_anterior||0);return (old>Number(p.precio)?'<del class="web-price-before">'+money(old)+'</del>':'')+'<span>'+money(p.precio)+'</span>'}
function cardTemplate(p){const imgs=productImages(p),cover=imgs[0]||FALLBACK,summary=p.descripcion||p.descripcion_detallada||'Producto disponible en nuestra tienda.',selected=Number(cart[String(p.id)]||0);return `<article class="card"><div class="photo" onclick="openProduct(${p.id})"><img loading="lazy" src="${esc(cover)}" alt="${esc(p.nombre)}" onerror="safeImage(this)"><button class="zoom" aria-label="Ver producto"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.5" cy="10.5" r="5.5"></circle><path d="m15 15 4.5 4.5M10.5 8v5M8 10.5h5"></path></svg></button>${imgs.length>1?`<span class="photo-count">${imgs.length} fotos</span>`:''}</div><div class="info"><div class="meta">${esc(p.categoria)} · ${esc(p.marca)}</div><h3>${esc(p.nombre)}</h3><div class="summary">${esc(summary)}</div><div class="details">${esc([p.modelo,p.talla&&'Talla '+p.talla,p.color].filter(Boolean).join(' · '))}<br>Stock: ${p.stock}</div><div class="bottom"><span class="price">${storePriceHtml(p)}</span><button class="add" data-product-id="${p.id}" onclick="add(${p.id},1,this)" aria-label="Agregar ${esc(p.nombre)} al carrito"><span class="add-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3h2l2.1 10.1a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 7H6"/><circle cx="9" cy="19" r="1.4"/><circle cx="18" cy="19" r="1.4"/><path d="M13 8v4M11 10h4"/></svg></span><span class="add-copy"><strong>${selected?'Agregar otra':'Agregar'}</strong><small>${selected?'En canasta':'al carrito'}</small></span><span class="add-count" ${selected?'':'hidden'}>${selected}</span></button></div></div></article>`}
function openProduct(id){activeProduct=products.find(p=>p.id==id)||catalog.get(String(id));if(!activeProduct)return;galleryImages=productImages(activeProduct);if(!galleryImages.length)galleryImages=[FALLBACK];galleryIndex=0;detailQuantity=1;$('detailQty').textContent=1;$('detailKicker').textContent=[activeProduct.categoria,activeProduct.marca].filter(Boolean).join(' · ');$('detailTitle').textContent=activeProduct.nombre;$('detailPrice').innerHTML=storePriceHtml(activeProduct);$('detailDescription').textContent=activeProduct.descripcion_detallada||activeProduct.descripcion||'Conoce este producto y consulta su disponibilidad.';$('detailStock').textContent='Stock disponible: '+activeProduct.stock;const attrs=[['Código',activeProduct.codigo],['Modelo',activeProduct.modelo],['Talla',activeProduct.talla],['Color',activeProduct.color],['Género',activeProduct.genero],['Detalle',activeProduct.botapie]];$('detailAttributes').innerHTML=attrs.filter(x=>x[1]).map(x=>`<div class="attribute"><small>${esc(x[0])}</small><b>${esc(x[1])}</b></div>`).join('');renderGallery();$('productModal').classList.add('open');document.body.style.overflow='hidden'}
function renderGallery(){$('detailImage').src=galleryImages[galleryIndex];safeImage($('detailImage'));$('thumbs').innerHTML=galleryImages.map((src,i)=>`<button class="${i===galleryIndex?'on':''}" onclick="galleryIndex=${i};renderGallery()"><img src="${esc(src)}" onerror="safeImage(this)" alt="Vista ${i+1}"></button>`).join('');document.querySelectorAll('.nav-arrow').forEach(b=>b.style.display=galleryImages.length>1?'block':'none')}
function stepGallery(n){galleryIndex=(galleryIndex+n+galleryImages.length)%galleryImages.length;renderGallery()}function closeProduct(){$('productModal').classList.remove('open');document.body.style.overflow=''}function changeDetailQty(n){detailQuantity=Math.max(1,Math.min(99,detailQuantity+n));$('detailQty').textContent=detailQuantity}function addFromDetail(){if(activeProduct){add(activeProduct.id,detailQuantity);closeProduct();toggleCart(true)}}
function add(id,quantity=1,button=null){cart[id]=(cart[id]||0)+quantity;save();document.querySelectorAll('.cart-icon,.floating-icon').forEach(icon=>{icon.classList.remove('cart-bump');void icon.offsetWidth;icon.classList.add('cart-bump');setTimeout(()=>icon.classList.remove('cart-bump'),600)});if(button){button.classList.add('just-added');setTimeout(()=>button.classList.remove('just-added'),650)}document.body.animate([{filter:'brightness(1)'},{filter:'brightness(1.04)'},{filter:'brightness(1)'}],{duration:250})}function save(){renderCart()}
function renderCart(){const rows=Object.entries(cart).map(([id,q])=>[catalog.get(String(id)),Number(q)]).filter(x=>x[0]),amount=rows.reduce((sum,[p,q])=>sum+q,0),totalValue=rows.reduce((sum,[p,q])=>sum+Number(p.precio||0)*q,0);$('count').textContent=amount;$('floatingCount').textContent=amount;$('headerCartTotal').textContent=money(totalValue);$('floatingCartTotal').textContent=money(totalValue);document.body.classList.toggle('cart-has-items',amount>0);$('items').innerHTML=rows.length?rows.map(([p,q])=>`<div class="item"><img src="${esc(productImages(p)[0]||FALLBACK)}" onerror="safeImage(this)"><div><b>${esc(p.nombre)}</b><br><small>${esc(p.codigo)} · ${money(p.precio)}</small></div><div class="qty"><button onclick="change(${p.id},-1)">&minus;</button> ${q} <button onclick="change(${p.id},1)">+</button></div></div>`).join(''):'<div class="empty">Tu carrito está vacío.</div>';$('total').textContent=money(totalValue);document.querySelectorAll('.add[data-product-id]').forEach(button=>{const qty=Number(cart[String(button.dataset.productId)]||0),copy=button.querySelector('.add-copy'),badge=button.querySelector('.add-count');if(copy)copy.innerHTML=`<strong>${qty?'Agregar otra':'Agregar'}</strong><small>${qty?'En canasta':'al carrito'}</small>`;if(badge){badge.textContent=qty;badge.hidden=qty<=0}})}
function change(id,n){cart[id]=(cart[id]||0)+n;if(cart[id]<=0)delete cart[id];save()}function toggleCart(force){const open=typeof force==='boolean'?force:!$('drawer').classList.contains('open');$('drawer').classList.toggle('open',open);document.body.style.overflow=open?'hidden':'';renderCart()}
let checkoutBusy=false;
async function checkout(){
 const rows=Object.entries(cart).map(([id,q])=>[catalog.get(String(id)),q]).filter(x=>x[0]);
 if(!rows.length)return alert('Agrega un producto al carrito.');
 if(!currentCustomer){toggleCart(false);openAuth(true);$('authMessage').textContent='Ingrese, regístrese o continúe por WhatsApp sin registrarse.';return;}
 if(checkoutBusy)return;
 const btn=$('checkoutBtn'),label=$('checkoutLabel');checkoutBusy=true;btn.disabled=true;label.textContent='Generando proforma...';
 try{
  const api='../api/index.php';
   const client=currentCustomer;
  if(!client||!client.IdCliente)throw new Error('No se encontró el cliente VARIOS.');
  const payload={
   cliente:{IdCliente:client.IdCliente},vendedor:{Usuario:'TIENDA'},EsCreditoProforma:0,
    EsDescripcion:1,DescripcionProforma:`Cliente web: ${client.nombre} | Teléfono: ${client.telefono} | Correo: ${client.correo}`,DescripcionProforma2:client.direccion||'',
   diasValidosProf:7,EsOrganizacion:0,nombreOrganizacion:'',
   productos:rows.map(([p,q])=>({IdProducto:p.id||p.IdProducto,cantidad:q,precio:Number(p.precio)||0,descuento:0}))
  };
  const createResponse=await fetch(api+'/proformas',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});
  const created=await createResponse.json();
  if(!createResponse.ok||!created.insertId)throw new Error(created.error||'No se pudo crear la proforma.');
  label.textContent='Preparando PDF...';
  const pdfResponse=await fetch(api+'/venta/proforma/generar_pdf?id='+encodeURIComponent(created.insertId));
  const generated=await pdfResponse.json();
  if(!pdfResponse.ok||!generated.url)throw new Error(generated.error||'La proforma fue creada, pero no se pudo generar el PDF.');
  const detailResponse=await fetch(api+'/proformas/id/'+encodeURIComponent(created.insertId));
  const detail=detailResponse.ok?await detailResponse.json():{};
  const number=detail.Numero||created.insertId;
   const msg='Hola, le compartimos la proforma N.° '+number+'.\n\nCLIENTE: '+client.nombre+'\nTELÉFONO: '+client.telefono+'\nCORREO: '+client.correo+'\nDIRECCIÓN: '+client.direccion+'\n\n'+rows.map(([p,q])=>`• ${q} x ${p.nombre} (${p.codigo}) - ${money(p.precio*q)}`).join('\n')+'\n\nTotal: '+$('total').textContent+'\nPDF: '+generated.url;
  const phone=String(company.CELULAR||company.TELEFONO||'').replace(/\D/g,'');
  label.textContent='Abriendo WhatsApp...';
  let shared=false;
  try{
   const fileResponse=await fetch(generated.url);if(!fileResponse.ok)throw new Error();
   const file=new File([await fileResponse.blob()],'Proforma-'+number+'.pdf',{type:'application/pdf'});
   if(navigator.canShare&&navigator.canShare({files:[file]})){await navigator.share({title:'Proforma '+number,text:'Le compartimos su proforma en PDF.',files:[file]});shared=true;}
  }catch(e){if(e&&e.name==='AbortError')shared=true;}
  if(!shared){
   const download=document.createElement('a');download.href=generated.url;download.download='Proforma-'+number+'.pdf';document.body.appendChild(download);download.click();download.remove();
   open('https://wa.me/'+(phone.startsWith('51')?phone:'51'+phone)+'?text='+encodeURIComponent(msg),'_blank');
  }
 }catch(error){alert(error.message||'No se pudo preparar la proforma.');}
 finally{checkoutBusy=false;btn.disabled=false;label.textContent='Enviar pedido';}
}
 let voiceRecognition=null;
 function scrollCategories(direction){const list=$('cats');if(list)list.scrollBy({left:direction*Math.max(240,list.clientWidth*.7),behavior:'smooth'})}
 function voiceNotice(message,isError=false){const notice=$('storeToast');if(!notice)return;notice.textContent=message;notice.className=`store-toast${isError?' error':''} show`;clearTimeout(voiceNotice.timer);voiceNotice.timer=setTimeout(()=>notice.classList.remove('show'),2800)}
 function toggleVoiceSearch(){
  const SpeechRecognition=window.SpeechRecognition||window.webkitSpeechRecognition,button=$('voiceSearch');
  if(!SpeechRecognition){voiceNotice('La búsqueda por voz no está disponible en este navegador.',true);return;}
  if(voiceRecognition){voiceRecognition.stop();return;}
  voiceRecognition=new SpeechRecognition();voiceRecognition.lang='es-PE';voiceRecognition.interimResults=false;voiceRecognition.maxAlternatives=1;
  voiceRecognition.onstart=()=>{button.classList.add('listening');button.setAttribute('aria-pressed','true');button.title='Escuchando...';voiceNotice('Escuchando... diga el producto que busca.');};
  voiceRecognition.onresult=event=>{const text=event.results?.[0]?.[0]?.transcript?.trim()||'';if(text){$('search').value=text;clearTimeout(searchTimer);loadProducts();}};
  voiceRecognition.onerror=event=>{if(event.error!=='aborted'&&event.error!=='no-speech')voiceNotice(event.error==='not-allowed'?'Permita el acceso al micrófono para buscar por voz.':'No se pudo reconocer la voz. Intente nuevamente.',true);};
  voiceRecognition.onend=()=>{button.classList.remove('listening');button.setAttribute('aria-pressed','false');button.title='Buscar por voz';voiceRecognition=null;};
  try{voiceRecognition.start()}catch(error){voiceRecognition=null;voiceNotice('No se pudo iniciar el micrófono.',true);}
 }
 $('search').addEventListener('input',()=>{clearTimeout(searchTimer);searchTimer=setTimeout(loadProducts,280)});$('search').addEventListener('keydown',e=>{if(e.key==='Enter'){clearTimeout(searchTimer);loadProducts()}});$('clearSearch').onclick=()=>{$('search').value='';$('search').focus();loadProducts()};document.addEventListener('keydown',e=>{if(e.key==='Escape'){closeProduct();toggleCart(false)}if($('productModal').classList.contains('open')&&e.key==='ArrowLeft')stepGallery(-1);if($('productModal').classList.contains('open')&&e.key==='ArrowRight')stepGallery(1)});init();
  </script>
<section id="webOffers" class="web-offers" hidden aria-labelledby="webOffersTitle">
 <div class="web-offers-heading"><h2 id="webOffersTitle">OFERTAS</h2><a href="#catalogo">VER MÁS</a></div>
 <div class="web-offers-carousel"><button class="offers-arrow offers-prev" type="button" aria-label="Ofertas anteriores">&#8249;</button><div id="webOffersList" class="web-offers-list" tabindex="0" aria-label="Carrusel de ofertas"></div><button class="offers-arrow offers-next" type="button" aria-label="Siguientes ofertas">&#8250;</button></div>
</section>
<script>
function offerCard(p){
 const previous=Number(p.precio_anterior||0),price=Number(p.precio||0);
 const discount=previous>price?Math.round((previous-price)/previous*100):0;
 return `<article class="offer-item"><button class="offer-photo" onclick="openProduct(${Number(p.id)})" aria-label="Ver ${esc(p.nombre)}"><img loading="lazy" src="${esc(productImages(p)[0]||FALLBACK)}" alt="${esc(p.nombre)}" onerror="safeImage(this)">${discount>0?`<span class="offer-discount">−${discount}%<small>DESCUENTO</small></span>`:'<span class="offer-tag">OFERTA</span>'}</button><button class="offer-name" onclick="openProduct(${Number(p.id)})" title="${esc(p.nombre)}">${esc(p.nombre)}</button><div class="offer-price">${storePriceHtml(p)}</div><button class="offer-add" data-product-id="${Number(p.id)}" onclick="add(${Number(p.id)},1,this)">＋ Agregar <span class="add-count" hidden>0</span></button></article>`;
}
(async function(){
 try {
  const response=await fetch(API+'?ofertas=1',{cache:'no-store'});
  if(!response.ok)throw new Error('offers');
  const offers=await response.json();
  if(!Array.isArray(offers)||!offers.length)return;
  offers.forEach(p=>catalog.set(String(p.id),p));
  const section=document.getElementById('webOffers'),list=document.getElementById('webOffersList');
  list.innerHTML=offers.map(offerCard).join('');
  document.querySelector('.tools').insertAdjacentElement('beforebegin',section);
  section.hidden=false;
  let interacting=false;
  function overflow(){return list.scrollWidth>list.clientWidth+5;}
  function move(direction){const max=list.scrollWidth-list.clientWidth;if(max<=5)return;const step=list.firstElementChild.getBoundingClientRect().width+16;const end=direction>0&&list.scrollLeft>=max-5;list.scrollTo({left:end?0:Math.max(0,list.scrollLeft+direction*step),behavior:'smooth'});}
  function controls(){section.querySelectorAll('.offers-arrow').forEach(button=>button.hidden=!overflow());}
  section.querySelector('.offers-prev').onclick=()=>move(-1);
  section.querySelector('.offers-next').onclick=()=>move(1);
  section.addEventListener('mouseenter',()=>interacting=true);section.addEventListener('mouseleave',()=>interacting=false);
  section.addEventListener('focusin',()=>interacting=true);section.addEventListener('focusout',event=>{if(!section.contains(event.relatedTarget))interacting=false;});
  list.addEventListener('keydown',event=>{if(event.key==='ArrowRight'||event.key==='ArrowLeft'){event.preventDefault();move(event.key==='ArrowRight'?1:-1);}});
  window.addEventListener('resize',controls);controls();
  if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches)setInterval(()=>{if(!interacting&&!document.hidden&&!document.getElementById('productModal').classList.contains('open'))move(1);},4000);
 } catch(error){console.warn('No se pudieron cargar las ofertas de la tienda.');}
})();
</script>
<style>
#webOffers.web-offers{max-width:1650px;margin:24px auto 60px;padding:12px 20px 20px;background:#fff!important;border:0;border-radius:0;box-shadow:none;color:#24242a}
#webOffers[hidden]{display:none!important}.web-offers-heading{display:flex;align-items:center;justify-content:space-between;gap:15px;border-bottom:1px solid #e6e6e6;margin-bottom:20px}
#webOffers .web-offers-heading h2{font-size:23px;font-weight:700;margin:0;padding:12px 0;border-bottom:3px solid #e4132f;color:#28282d;letter-spacing:.4px}
.web-offers-heading a{font-size:14px;font-weight:800;color:#e4132f;text-decoration:none}.web-offers-carousel{position:relative}
#webOffersList{display:flex;gap:16px;overflow-x:auto;padding:4px 2px 12px;scroll-snap-type:x mandatory;scrollbar-width:none;touch-action:pan-x pan-y}
#webOffersList::-webkit-scrollbar{display:none}.offer-item{flex:0 0 calc((100% - 80px)/6);min-width:0;scroll-snap-align:start;text-align:center;background:#fff;padding:6px 8px 12px;border:1px solid transparent;border-radius:12px}
.offer-item:hover{border-color:#f5d7dc}.offer-photo{position:relative;display:block;width:100%;height:205px;padding:22px 8px 8px;border:0;background:#fff;cursor:pointer}.offer-photo img{width:100%;height:100%;object-fit:contain}.offer-discount,.offer-tag{position:absolute;right:0;top:0;background:#e4132f;color:#fff;border-radius:8px;padding:7px;font-size:20px;font-weight:900}.offer-discount small{display:block;font-size:8px;letter-spacing:.4px}.offer-tag{font-size:11px}
.offer-name{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;width:100%;height:42px;padding:0;border:0;background:#fff;color:#29292f;font-size:13px;line-height:21px;font-weight:600;cursor:pointer;margin:12px 0 6px}
.offer-price{color:#e4132f;font-weight:800;font-size:21px;min-height:51px}.offer-price .web-price-before{display:block;color:#919197;font-size:13px;font-weight:400;line-height:18px;text-decoration:line-through}
.offer-add{background:#fff;color:#da1230;border:1px solid #f0ccd3;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:700;margin-top:5px}.offer-add:hover{background:#e4132f;color:#fff}
#webOffers .offers-arrow{position:absolute;top:95px;width:42px;height:42px;border:1px solid #eee;border-radius:50%;background:#fff;color:#d8132e;box-shadow:0 2px 8px #0002;font-size:29px;line-height:1;z-index:2}.offers-prev{left:-16px}.offers-next{right:-16px}.offers-arrow[hidden]{display:none}
@media(max-width:1100px){.offer-item{flex-basis:calc((100% - 48px)/4)}}
@media(max-width:700px){#webOffers.web-offers{margin:16px 8px 50px;padding:8px 12px}.offer-item{flex-basis:calc((100% - 16px)/2)}.offer-photo{height:160px}#webOffers .web-offers-heading h2{font-size:20px}.offer-price{font-size:19px}#webOffers .offers-arrow{top:75px;width:34px;height:34px}.offers-prev{left:-10px}.offers-next{right:-10px}}
</style>
  <template data-disabled="stock-layout-duplicates">
  <script>
    (() => {
      // Bloque posterior desactivado: duplicaba el render y reemplazaba los campos reales.
      return;
    function stockOf(product){
      return Math.max(0,Math.floor(Number(product&&product.stock)||0));
    }
    function notifyStore(message){
      const toast=$('storeToast');
      if(!toast)return;
      toast.textContent=message;
      toast.classList.add('show');
      clearTimeout(notifyStore.timer);
      notifyStore.timer=setTimeout(()=>toast.classList.remove('show'),2800);
    }
    function productLink(product){
      const url=new URL(location.origin+location.pathname);
      url.searchParams.set('q',product.codigo||product.nombre||'');
      return url.href;
    }
    function cardTemplate(p){
      const images=productImages(p),image=images[0]||FALLBACK,stock=stockOf(p),soldOut=stock<=0;
      return `<article class="card">
        <div class="pic"><img src="${esc(image)}" alt="${esc(p.nombre)}" onerror="safeImage(this)"><button class="zoom" onclick="openProduct(${p.id})" aria-label="Ver detalle">&#128269;</button>${images.length>1?`<span class="photos">&#128247; ${images.length} fotos</span>`:''}</div>
        <div class="body"><div class="meta">${esc(p.categoria||'MODA')} · ${esc(p.marca||'')}</div><h3>${esc(p.nombre)}</h3><div class="desc">${esc(p.descripcion||'Producto disponible en nuestra tienda.')}</div><div class="details">${esc(p.modelo||'')} · Talla ${esc(p.talla||'-')} · ${esc(p.color||'')}</div><span class="store-stock ${soldOut?'out':''}">${soldOut?'Agotado':'Stock disponible: '+stock}</span><div class="buy"><strong>${money(p.precio)}</strong><button class="add" ${soldOut?'disabled':''} onclick="add(${p.id})">&#128722; ${soldOut?'Sin stock':'Agregar al carrito'}</button></div></div>
      </article>`;
    }
    function openProduct(id){
      const p=products.find(x=>Number(x.id)===Number(id))||catalog.get(Number(id));
      if(!p)return;
      catalog.set(Number(p.id),p);activeProduct=p;galleryImages=productImages(p);galleryIndex=0;
      const stock=stockOf(p);detailQuantity=stock>0?1:0;
      $('detailTitle').textContent=[p.nombre,p.modelo,p.color].filter(Boolean).join(' · ');
      $('detailCategory').textContent=[p.categoria,p.marca].filter(Boolean).join(' · ');
      $('detailName').textContent=p.nombre||'Producto';$('detailDescription').textContent=p.descripcion||'Producto disponible en nuestra tienda.';
      $('detailExtra').textContent=p.descripcion_detallada||'Consulte disponibilidad, colores y tallas con nuestro equipo.';
      $('detailModel').textContent=p.modelo||'-';$('detailSize').textContent=p.talla||'-';$('detailColor').textContent=p.color||'-';$('detailStock').textContent=stock;
      $('detailPrice').textContent=money(p.precio);$('detailQty').textContent=detailQuantity;renderDetailImage();syncDetailStock();openModal('productModal');
    }
    function syncDetailStock(){
      if(!activeProduct)return;
      const stock=stockOf(activeProduct),buttons=document.querySelectorAll('.quantity button'),button=document.querySelector('.detail-add');
      $('detailQty').textContent=detailQuantity;
      if(buttons[0])buttons[0].disabled=stock<=0||detailQuantity<=1;
      if(buttons[1])buttons[1].disabled=stock<=0||detailQuantity>=stock;
      if(button){button.disabled=stock<=0;const label=button.querySelector('span');if(label)label.textContent=stock<=0?'Producto agotado':'Agregar al carrito';}
    }
    function changeDetailQty(delta){
      if(!activeProduct)return;
      const stock=stockOf(activeProduct);
      if(stock<=0){notifyStore('Este producto no tiene stock disponible.');return;}
      detailQuantity=Math.max(1,Math.min(stock,detailQuantity+Number(delta||0)));syncDetailStock();
    }
    function addFromDetail(){
      if(activeProduct&&add(activeProduct.id,detailQuantity))closeModal('productModal');
    }
    function add(id,quantity=1){
      id=Number(id);quantity=Math.max(1,Number(quantity)||1);
      const p=products.find(x=>Number(x.id)===id)||catalog.get(id);
      if(!p){notifyStore('No se pudo identificar el producto.');return false;}
      catalog.set(id,p);
      const stock=stockOf(p),current=Number(cart[id]||0);
      if(stock<=0){notifyStore('Producto agotado. No se puede agregar al pedido.');return false;}
      if(current+quantity>stock){notifyStore('Solo hay '+stock+' unidad'+(stock===1?'':'es')+' disponible'+(stock===1?'':'s')+'.');return false;}
      cart[id]=current+quantity;renderCart();pulseCart();return true;
    }
    function renderCart(){
      const rows=Object.entries(cart).map(([id,q])=>[catalog.get(Number(id))||products.find(p=>Number(p.id)===Number(id)),Number(q)]).filter(x=>x[0]);
      $('count').textContent=rows.reduce((s,x)=>s+x[1],0);$('floatCount').textContent=$('count').textContent;
      let invalid=false;
      $('items').innerHTML=rows.length?rows.map(([p,q])=>{
        const stock=stockOf(p),bad=stock<=0||q>stock;invalid=invalid||bad;
        return `<div class="item ${bad?'invalid':''}"><img src="${esc(productImages(p)[0]||FALLBACK)}" onerror="safeImage(this)"><div><b>${esc(p.nombre)}</b><small>Código: ${esc(p.codigo)} · ${money(p.precio)} c/u</small><span class="item-stock ${bad?'out':''}">${stock<=0?'Agotado':'Stock disponible: '+stock}</span></div><div class="qty"><button onclick="change(${p.id},-1)">−</button><b>${q}</b><button ${q>=stock?'disabled':''} onclick="change(${p.id},1)">+</button></div></div>`;
      }).join(''):'<div class="empty">Tu carrito está vacío.</div>';
      $('total').textContent=money(rows.reduce((s,[p,q])=>s+Number(p.precio)*q,0));
      const checkoutButton=document.querySelector('.checkout');
      if(checkoutButton){checkoutButton.disabled=!rows.length||invalid;checkoutButton.title=invalid?'Revise los productos sin stock':'';}
      saveCart();
    }
    function change(id,delta){
      id=Number(id);const p=catalog.get(id)||products.find(x=>Number(x.id)===id);
      if(!p)return;
      const stock=stockOf(p),next=Number(cart[id]||0)+Number(delta||0);
      if(next<=0)delete cart[id];
      else if(stock<=0){notifyStore('Producto agotado. Se retiró del carrito.');delete cart[id];}
      else if(next>stock)notifyStore('Ha alcanzado el stock máximo disponible: '+stock+'.');
      else cart[id]=next;
      renderCart();
    }
    function checkout(){
      const rows=Object.entries(cart).map(([id,q])=>[catalog.get(Number(id))||products.find(p=>Number(p.id)===Number(id)),Number(q)]).filter(x=>x[0]);
      if(!rows.length){notifyStore('Agregue al menos un producto al pedido.');return;}
      const invalid=rows.find(([p,q])=>stockOf(p)<=0||q>stockOf(p));
      if(invalid){notifyStore('Revise el stock de '+invalid[0].nombre+' antes de continuar.');renderCart();return;}
      const total=rows.reduce((sum,[p,q])=>sum+Number(p.precio)*q,0);
      const date=new Date(),order='WEB-'+date.getFullYear()+String(date.getMonth()+1).padStart(2,'0')+String(date.getDate()).padStart(2,'0')+'-'+String(date.getHours()).padStart(2,'0')+String(date.getMinutes()).padStart(2,'0');
      const detail=rows.map(([p,q],index)=>[
        (index+1)+'. *'+p.nombre+'*',
        '   Código: '+p.codigo,
        '   Cantidad: '+q,
        '   Precio unitario: '+money(p.precio),
        '   Subtotal: '+money(Number(p.precio)*q),
        '   Producto: '+productLink(p)
      ].join('\n')).join('\n\n');
      const message=[
        '🛍️ *PEDIDO WEB - '+(company.nombre||'TIENDA')+'*',
        'Pedido: *'+order+'*',
        'Fecha: '+date.toLocaleString('es-PE'),
        '',
        '*DETALLE DEL PEDIDO*',
        detail,
        '',
        '*TOTAL DEL PEDIDO: '+money(total)+'*',
        '',
        'Por favor, confirmar disponibilidad, forma de pago y entrega.'
      ].join('\n');
      const phone=String(company.whatsapp||'').replace(/\D/g,'');
      window.open('https://wa.me/'+phone+'?text='+encodeURIComponent(message),'_blank','noopener');
    }
    const requestedSearch=new URLSearchParams(location.search).get('q');
    if(requestedSearch&&$('search'))$('search').value=requestedSearch;
  </script>

  <style>
    .store-stock{display:inline-flex;align-items:center;gap:6px;margin-top:8px;padding:5px 9px;border-radius:999px;background:#edf9f3;color:#08724f;font-size:12px;font-weight:800}
    .store-stock.low{background:#fff3d6;color:#9a5a00}.store-stock.out{background:#ffe7eb;color:#b01838}
    .item-stock{margin-top:5px;font-size:12px;font-weight:800;color:#08724f}.item-stock.out{color:#b01838}
    .cart-code{display:block;margin-top:3px;color:#718096;font-size:12px}
    .cart-item-invalid{border-color:#ef9aab!important;background:#fff7f8!important}
    .add:disabled,.detail-add:disabled,.checkout:disabled,.qty button:disabled{cursor:not-allowed!important;filter:grayscale(.25);opacity:.55!important;transform:none!important;box-shadow:none!important}
  </style>

  <script>
    function stockOf(product){
      const value=Number(product&&product.stock);
      return Number.isFinite(value)?Math.max(0,Math.floor(value)):0;
    }

    function productById(id){
      return catalog.get(String(id))||products.find(p=>String(p.id)===String(id))||null;
    }

    function notifyStore(message,type='warning'){
      let notice=document.getElementById('storeNotice');
      if(!notice){
        notice=document.createElement('div');
        notice.id='storeNotice';
        Object.assign(notice.style,{position:'fixed',left:'50%',bottom:'28px',transform:'translateX(-50%)',zIndex:'4000',maxWidth:'min(92vw,520px)',padding:'13px 18px',borderRadius:'14px',color:'#fff',fontWeight:'800',textAlign:'center',boxShadow:'0 14px 35px rgba(31,12,39,.28)',transition:'opacity .25s'});
        document.body.appendChild(notice);
      }
      notice.style.background=type==='error'?'#b01838':'#55205f';
      notice.style.opacity='1';
      notice.textContent=message;
      clearTimeout(notice._timer);
      notice._timer=setTimeout(()=>notice.style.opacity='0',3000);
    }

    function productLink(product){
      const url=new URL(window.location.origin+window.location.pathname);
      url.searchParams.set('q',product.codigo||product.nombre||'');
      return url.toString();
    }

    function cardTemplate(p){
      const stock=stockOf(p), available=stock>0;
      const image=p.imagen||placeholder;
      const description=p.descripcion_corta||p.descripcion||'Producto disponible en nuestra tienda.';
      const details=[p.modelo,p.talla?`Talla ${p.talla}`:'',p.color].filter(Boolean).join(' · ');
      const stockClass=!available?'out':stock<=3?'low':'';
      return `<article class="card">
        <div class="photo" onclick="openProduct('${esc(p.id)}')">
          <img src="${esc(image)}" alt="${esc(p.nombre)}" onerror="this.onerror=null;this.src='${placeholder}'">
          <button class="zoom" type="button" aria-label="Ver detalle">⌕</button>
        </div>
        <div class="info">
          <div class="meta">${esc([p.categoria,p.marca].filter(Boolean).join(' · '))}</div>
          <h3>${esc(p.nombre)}</h3>
          <div class="summary">${esc(description)}</div>
          <div class="details">${esc(details)}</div>
          <span class="store-stock ${stockClass}">${available?`Stock disponible: ${stock}`:'Sin stock disponible'}</span>
          <div class="bottom">
            <span class="price">${money(p.precio)}</span>
            <button class="add" type="button" ${available?'': 'disabled'} onclick="add('${esc(p.id)}',1,this)">${available?'Agregar al carrito':'Agotado'}</button>
          </div>
        </div>
      </article>`;
    }

    function syncDetailStock(){
      const p=productById(galleryProductId); if(!p)return;
      const stock=stockOf(p), available=stock>0;
      detailQty=Math.max(1,Math.min(detailQty,Math.max(1,stock)));
      if($('detailQty'))$('detailQty').textContent=detailQty;
      if($('detailStock'))$('detailStock').textContent=available?`Stock disponible: ${stock}`:'Producto agotado';
      const minus=document.querySelector('.product-modal .quantity button:first-child');
      const plus=document.querySelector('.product-modal .quantity button:last-child');
      const action=document.querySelector('.product-modal .detail-add');
      if(minus)minus.disabled=!available||detailQty<=1;
      if(plus)plus.disabled=!available||detailQty>=stock;
      if(action){action.disabled=!available;action.textContent=available?'Agregar al carrito':'Producto agotado';}
    }

    function openProduct(id){
      const p=productById(id); if(!p)return;
      galleryProductId=String(id); detailQty=1;
      galleryImages=(Array.isArray(p.imagenes)&&p.imagenes.length?p.imagenes:[p.imagen||placeholder]).filter(Boolean);
      if(!galleryImages.length)galleryImages=[placeholder];
      galleryIndex=0;
      $('detailKicker').textContent=[p.categoria,p.marca].filter(Boolean).join(' · ');
      $('detailTitle').textContent=p.nombre;
      $('detailPrice').textContent=money(p.precio);
      $('detailDescription').textContent=p.descripcion_detallada||p.descripcion_corta||p.descripcion||'Producto disponible en nuestra tienda.';
      $('detailAttributes').textContent=[p.codigo?`Código: ${p.codigo}`:'',p.modelo?`Modelo: ${p.modelo}`:'',p.talla?`Talla: ${p.talla}`:'',p.color?`Color: ${p.color}`:''].filter(Boolean).join(' · ');
      renderGallery(); syncDetailStock();
      $('productModal').classList.add('open'); document.body.classList.add('modal-open');
    }

    function changeDetailQty(delta){detailQty+=Number(delta)||0;syncDetailStock()}
    function addFromDetail(){if(galleryProductId)add(galleryProductId,detailQty)}

    function add(id,quantity=1,button=null){
      const p=productById(id); if(!p)return;
      const stock=stockOf(p), current=Number(cart.get(String(id))||0), requested=Math.max(1,Number(quantity)||1);
      if(stock<=0){notifyStore('Este producto no tiene stock disponible.','error');return}
      if(current+requested>stock){notifyStore(`Solo hay ${stock} unidad${stock===1?'':'es'} disponible${stock===1?'':'s'} de este producto.`,'error');return}
      cart.set(String(id),current+requested); renderCart();
      if(button){button.classList.add('added');setTimeout(()=>button.classList.remove('added'),450)}
    }

    function change(id,delta){
      const p=productById(id); if(!p)return;
      const stock=stockOf(p), next=Number(cart.get(String(id))||0)+(Number(delta)||0);
      if(next<=0)cart.delete(String(id));
      else if(next>stock)notifyStore(`Cantidad máxima disponible: ${stock}.`,'error');
      else cart.set(String(id),next);
      renderCart();
    }

    function renderCart(){
      let count=0,total=0,invalid=false,html='';
      cart.forEach((qty,id)=>{
        const p=productById(id); if(!p)return;
        const stock=stockOf(p), bad=stock<=0||qty>stock;
        count+=qty; total+=Number(p.precio||0)*qty; invalid=invalid||bad;
        html+=`<div class="item ${bad?'cart-item-invalid':''}">
          <img src="${esc(p.imagen||placeholder)}" alt="${esc(p.nombre)}" onerror="this.onerror=null;this.src='${placeholder}'">
          <div><strong>${esc(p.nombre)}</strong><span class="cart-code">Código: ${esc(p.codigo||'S/C')} · ${money(p.precio)}</span><div class="item-stock ${bad?'out':''}">${bad?'Stock insuficiente':`Stock disponible: ${stock}`}</div></div>
          <div class="qty"><button type="button" onclick="change('${esc(id)}',-1)">−</button><span>${qty}</span><button type="button" ${qty>=stock?'disabled':''} onclick="change('${esc(id)}',1)">+</button></div>
        </div>`;
      });
      $('cartItems').innerHTML=html||'<div class="empty">Tu carrito está vacío.</div>';
      $('cartTotal').textContent=money(total);
      ['count','floatingCount'].forEach(id=>{if($(id))$(id).textContent=count});
      document.querySelectorAll('.checkout').forEach(btn=>{btn.disabled=!count||invalid;btn.textContent=invalid?'Revisar stock del pedido':'Enviar pedido por WhatsApp'});
      const cartButton=document.querySelector('.cart-button'); if(cartButton)cartButton.classList.toggle('has-items',count>0);
    }

    function checkout(){
      if(!cart.size){notifyStore('Agrega productos antes de generar el pedido.','error');return}
      const rows=[]; let total=0,index=1;
      for(const [id,qty] of cart.entries()){
        const p=productById(id); if(!p)continue;
        const stock=stockOf(p);
        if(stock<=0||qty>stock){notifyStore(`No se puede generar el pedido: revisa el stock de ${p.nombre}.`,'error');renderCart();return}
        const subtotal=Number(p.precio||0)*qty; total+=subtotal;
        rows.push(`${index}. *${p.nombre}*\n   Código: ${p.codigo||'S/C'}\n   Cantidad: ${qty}\n   Precio unitario: ${money(p.precio)}\n   Subtotal: ${money(subtotal)}\n   Ver producto: ${productLink(p)}`); index++;
      }
      const now=new Date(), pad=n=>String(n).padStart(2,'0');
      const orderCode=`PED-${now.getFullYear()}${pad(now.getMonth()+1)}${pad(now.getDate())}-${pad(now.getHours())}${pad(now.getMinutes())}`;
      const business=company.RAZON_SOCIAL||company.NOMBRE||company.nombre||'Tienda virtual';
      const message=`*PEDIDO WEB - ${business}*\nCódigo de pedido: ${orderCode}\nFecha: ${now.toLocaleString('es-PE')}\n\n*DETALLE DEL PEDIDO*\n\n${rows.join('\n\n')}\n\n*TOTAL DEL PEDIDO: ${money(total)}*\n\nSolicito confirmar disponibilidad, forma de entrega y pago.`;
      let phone=String(company.CELULAR||company.TELEFONO||company.whatsapp||'').replace(/\D/g,'');
      if(phone.length===9)phone='51'+phone;
      window.open(`https://wa.me/${phone}?text=${encodeURIComponent(message)}`,'_blank','noopener');
    }

    setTimeout(()=>{renderProducts();renderCart()},0);
    })();
  </script>
  <style>
    .stock-pill{display:inline-flex;align-items:center;gap:6px;margin-top:8px;padding:6px 10px;border-radius:999px;background:#e9f8f1;color:#087a58;font-size:12px;font-weight:800}
    .stock-pill.out{background:#fff0f2;color:#b41639}
    .add[disabled],.detail-add[disabled],.checkout.disabled{cursor:not-allowed!important;opacity:.55!important;filter:grayscale(.2);transform:none!important;box-shadow:none!important}
    .cart-stock{display:block;margin-top:5px;color:#65778b;font-size:12px;font-weight:700}
    .cart-stock.invalid{color:#bc1638}
    .store-toast{position:fixed;left:50%;bottom:28px;z-index:10050;max-width:min(430px,calc(100vw - 32px));transform:translate(-50%,20px);padding:13px 18px;border-radius:14px;background:#32133b;color:#fff;font-weight:800;box-shadow:0 16px 40px rgba(33,8,42,.3);opacity:0;transition:.25s ease;pointer-events:none;text-align:center}
    .store-toast.show{opacity:1;transform:translate(-50%,0)}
    .store-toast.error{background:#b41639}
  </style>
  <script>
    /* Implementación final del carrito: mantiene la estructura original de la tienda. */
    function stockOf(product){
      return Math.max(0,Number(product?.stock ?? product?.STOCK ?? 0)||0);
    }

    function webPriceHtml(product) {
      const current = priceOf(product), previous = Number(product.precio_anterior || 0);
      return (previous > current ? '<del class="web-price-before">' + money(previous) + '</del>' : '') + '<span>' + money(current) + '</span>';
    }
    function priceOf(product){
      return Math.max(0,Number(product?.precio ?? product?.price ?? product?.PrecioContado ?? 0)||0);
    }

    function escapeHtml(value){
      return String(value ?? '').replace(/[&<>"']/g,function(char){
        return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char];
      });
    }

    function productById(id){
      const key=String(id);
      return catalog.get(key)||products.find(product=>String(product.id)===key)||null;
    }

    function storeNotice(message,type='error'){
      let toast=document.getElementById('storeToast');
      if(!toast){
        toast=document.createElement('div');
        toast.id='storeToast';
        toast.className='store-toast';
        document.body.appendChild(toast);
      }
      toast.textContent=message;
      toast.className=`store-toast ${type}`;
      requestAnimationFrame(()=>toast.classList.add('show'));
      clearTimeout(storeNotice.timer);
      storeNotice.timer=setTimeout(()=>toast.classList.remove('show'),2800);
    }

    function productPublicLink(product){
      const url=new URL(window.location.href);
      url.search='';
      url.hash='';
      url.searchParams.set('q',product.code||product.name||product.id);
      return url.toString();
    }

    function cardTemplate(product){
      catalog.set(String(product.id),product);
      const images=productImages(product);
      const stock=stockOf(product);
      const unavailable=stock<=0;
      return `<article class="product-card${unavailable?' product-out':''}">
        <div class="photo">
          <img src="${escapeHtml(images[0]||FALLBACK)}" alt="${escapeHtml(product.name||'Producto')}" loading="lazy" onerror="this.onerror=null;this.src='${FALLBACK}'">
          <button class="zoom" type="button" onclick="openProduct('${escapeHtml(String(product.id))}')" aria-label="Ver detalles">⌕</button>
        </div>
        <div class="info">
          <div class="summary">${escapeHtml([product.category,product.brand].filter(Boolean).join(' · '))}</div>
          <h3>${escapeHtml(product.name||'Producto')}</h3>
          <p>${escapeHtml(product.description||'Producto disponible en nuestra tienda.')}</p>
          <div class="details">${escapeHtml([product.model,product.size?`Talla ${product.size}`:'',product.color].filter(Boolean).join(' · '))}</div>
          <span class="stock-pill${unavailable?' out':''}">${unavailable?'Sin stock':`Stock disponible: ${stock}`}</span>
          <div class="bottom"><strong class="price">${webPriceHtml(product)}</strong><button class="add" data-product-id="${escapeHtml(String(product.id))}" type="button" ${unavailable?'disabled':''} onclick="add('${escapeHtml(String(product.id))}')"><span class="add-label">${unavailable?'Agotado':'Agregar al carrito'}</span><span class="add-count" ${Number(cart[String(product.id)]||0)>0?'':'hidden'}>${Number(cart[String(product.id)]||0)}</span></button></div>
        </div>
      </article>`;
    }

    function openProduct(id){
      const product=productById(id);
      if(!product)return;
      activeProduct=product;
      galleryImages=productImages(product);
      galleryIndex=0;
      detailQuantity=stockOf(product)>0?1:0;
      $('detailKicker').textContent=[product.category,product.brand].filter(Boolean).join(' · ');
      $('detailTitle').textContent=product.name||'Producto';
      $('detailPrice').innerHTML=webPriceHtml(product);
      $('detailDescription').textContent=product.description||'Producto disponible en nuestra tienda.';
      $('detailAttributes').innerHTML=[product.model&&`<span>Modelo: ${escapeHtml(product.model)}</span>`,product.size&&`<span>Talla: ${escapeHtml(product.size)}</span>`,product.color&&`<span>Color: ${escapeHtml(product.color)}</span>`,product.code&&`<span>Código: ${escapeHtml(product.code)}</span>`].filter(Boolean).join('');
      renderGallery();
      syncDetailStock();
      $('productModal').classList.add('open');
      document.body.style.overflow='hidden';
    }

    function syncDetailStock(){
      if(!activeProduct)return;
      const stock=stockOf(activeProduct);
      $('detailQty').textContent=detailQuantity;
      $('detailStock').textContent=stock>0?`Stock disponible: ${stock}`:'Producto agotado';
      $('detailStock').classList.toggle('out',stock<=0);
      const addButton=document.querySelector('#productModal .detail-add');
      if(addButton){
        addButton.disabled=stock<=0;
        addButton.textContent=stock<=0?'Producto agotado':'Agregar al carrito';
      }
      document.querySelectorAll('#productModal .quantity button').forEach(button=>button.disabled=stock<=0);
    }

    function changeDetailQty(delta){
      if(!activeProduct)return;
      const stock=stockOf(activeProduct);
      detailQuantity=Math.min(stock,Math.max(stock>0?1:0,detailQuantity+delta));
      $('detailQty').textContent=detailQuantity;
      if(delta>0&&detailQuantity>=stock&&stock>0)storeNotice(`Solo hay ${stock} unidad${stock===1?'':'es'} disponible${stock===1?'':'s'}.`);
    }

    function addFromDetail(){
      if(!activeProduct)return;
      if(add(activeProduct.id,detailQuantity)){
        closeProduct();
        openCart();
      }
    }

    function add(id,quantity=1){
      const product=productById(id);
      if(!product)return false;
      const stock=stockOf(product);
      const key=String(product.id);
      const current=Number(cart[key]||0);
      const requested=current+Math.max(1,Number(quantity)||1);
      if(stock<=0){
        storeNotice(`${product.name||'El producto'} no tiene stock disponible.`);
        return false;
      }
      if(requested>stock){
        storeNotice(`Stock insuficiente para ${product.name}. Disponible: ${stock}.`);
        return false;
      }
      cart[key]=requested;
      renderCart();
      return true;
    }

    function change(id,delta){
      const product=productById(id);
      const key=String(id);
      if(!product||!cart[key])return;
      const next=Number(cart[key])+Number(delta);
      if(next<=0)delete cart[key];
      else if(next>stockOf(product))storeNotice(`No puede superar el stock disponible (${stockOf(product)}).`);
      else cart[key]=next;
      renderCart();
    }

    function renderCart(){
      let html='',totalValue=0,totalItems=0,invalid=false;
      Object.entries(cart).forEach(([id,quantity])=>{
        const product=productById(id);
        if(!product)return;
        const stock=stockOf(product);
        const qty=Number(quantity)||0;
        const rowInvalid=stock<=0||qty>stock;
        invalid=invalid||rowInvalid;
        totalItems+=qty;
        totalValue+=priceOf(product)*qty;
        const image=productImages(product)[0]||FALLBACK;
        html+=`<div class="cart-item">
          <img src="${escapeHtml(image)}" alt="${escapeHtml(product.name||'Producto')}" onerror="this.onerror=null;this.src='${FALLBACK}'">
          <div class="cart-copy"><b>${escapeHtml(product.name||product.nombre||'Producto')}</b><small>Código: ${escapeHtml(product.code||product.codigo||String(product.id))} · ${money(priceOf(product))} c/u</small><span class="cart-stock${rowInvalid?' invalid':''}">${stock>0?`Stock disponible: ${stock}`:'Sin stock disponible'}</span></div>
          <div class="cart-actions"><button type="button" onclick="change('${escapeHtml(id)}',-1)">−</button><span>${qty}</span><button type="button" onclick="change('${escapeHtml(id)}',1)" ${qty>=stock?'disabled':''}>+</button></div>
        </div>`;
      });
      $('items').innerHTML=html||'<div class="empty-cart">Tu carrito está vacío.</div>';
      $('total').textContent=money(totalValue);
      $('headerCartTotal').textContent=money(totalValue);
      $('floatingCartTotal').textContent=money(totalValue);
      $('count').textContent=totalItems;
      $('floatingCount').textContent=totalItems;
      document.querySelectorAll('.add[data-product-id]').forEach(button=>{const qty=Number(cart[String(button.dataset.productId)]||0),label=button.querySelector('.add-label'),badge=button.querySelector('.add-count');if(label&&!button.disabled)label.textContent=qty>0?'Agregar otra':'Agregar al carrito';if(badge){badge.textContent=qty;badge.hidden=qty<=0}});
      document.body.classList.toggle('cart-has-items',totalItems>0);
      const checkoutButton=document.querySelector('.checkout');
      if(checkoutButton){
        checkoutButton.disabled=totalItems===0||invalid;
        checkoutButton.classList.toggle('disabled',totalItems===0||invalid);
        checkoutButton.textContent=invalid?'Revisa el stock del pedido':'Enviar pedido por WhatsApp';
      }
    }

    function checkout(){
      const rows=[];
      let totalValue=0;
      for(const [id,quantity] of Object.entries(cart)){
        const product=productById(id);
        if(!product)continue;
        const stock=stockOf(product),qty=Number(quantity)||0;
        if(stock<=0||qty>stock){
          storeNotice(`Revise el stock de ${product.name}. Disponible: ${stock}.`);
          return;
        }
        const subtotal=priceOf(product)*qty;
        totalValue+=subtotal;
        rows.push({product,qty,subtotal});
      }
      if(!rows.length){storeNotice('Agregue productos antes de generar el pedido.');return;}
      const order=`WEB-${new Date().toISOString().replace(/\D/g,'').slice(0,14)}`;
      const business=company.NOMBRE_EMPRESA||company.RAZON_SOCIAL||company.nombre||'JOBI FASHION CHIC';
      const lines=[
        '🛍️ *SOLICITUD DE PEDIDO*',
        `*${business}*`,
        `*N.º:* ${order}`,
        `*Fecha:* ${new Date().toLocaleString('es-PE')}`,
        '',
        '*DETALLE DEL PEDIDO*'
      ];
      rows.forEach(({product,qty,subtotal},index)=>{
        lines.push('',`${index+1}. *${product.name||product.nombre||'Producto'}*`,`   Código: ${product.code||product.codigo||product.id}`,`   Cantidad: ${qty}`,`   Precio unitario: ${money(priceOf(product))}`,`   Subtotal: ${money(subtotal)}`,`   Enlace: ${productPublicLink(product)}`);
      });
      lines.push('','━━━━━━━━━━━━━━━━━━',`*TOTAL DEL PEDIDO: ${money(totalValue)}*`,'','Por favor, confirmar disponibilidad y datos de entrega.');
      const phone=String(company.CELULAR||company.TELEFONO||company.whatsapp||company.phone||'').replace(/\D/g,'');
      window.open(`https://wa.me/${phone}?text=${encodeURIComponent(lines.join('\n'))}`,'_blank','noopener');
    }

    const requestedProduct=new URLSearchParams(window.location.search).get('q');
    if(requestedProduct&&$('search')){
      $('search').value=requestedProduct;
      setTimeout(()=>loadProducts(),0);
    }
    setTimeout(()=>{renderProducts();renderCart()},0);
  </script>
  </template>
  <script src="/resources/js/neurollantas-store.js?v=20261010-tech-20261002-3"></script>
</body></html>

<style>.web-price-before{display:block;font-size:14px;line-height:1.4;color:#89919b;font-weight:500;text-decoration:line-through;text-decoration-thickness:2px}.price>span{white-space:nowrap}</style>
