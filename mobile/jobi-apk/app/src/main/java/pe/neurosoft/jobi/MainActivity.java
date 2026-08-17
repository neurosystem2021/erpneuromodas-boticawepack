package pe.neurosoft.jobi;

import android.Manifest;
import android.app.Activity;
import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.content.pm.PackageManager;
import android.graphics.Color;
import android.net.Uri;
import android.os.Bundle;
import android.provider.MediaStore;
import android.view.View;
import android.view.Window;
import android.webkit.CookieManager;
import android.webkit.PermissionRequest;
import android.webkit.ValueCallback;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ProgressBar;
import android.widget.Toast;

public class MainActivity extends Activity {
    private static final String HOME_URL = "https://jobi.factura.vip/views/VentasApp.php";
    private static final int FILE_CHOOSER = 4102;
    private WebView webView;
    private ProgressBar progress;
    private ValueCallback<Uri[]> fileCallback;
    private PermissionRequest pendingWebPermission;

    @Override protected void onCreate(Bundle state) {
        super.onCreate(state);
        if (android.os.Build.VERSION.SDK_INT >= 30) {
            getWindow().setStatusBarColor(Color.WHITE);
            getWindow().setNavigationBarColor(Color.rgb(42, 33, 38));
            getWindow().getDecorView().getWindowInsetsController().setSystemBarsAppearance(
                android.view.WindowInsetsController.APPEARANCE_LIGHT_STATUS_BARS,
                android.view.WindowInsetsController.APPEARANCE_LIGHT_STATUS_BARS);
        }
        setContentView(R.layout.activity_main);
        webView = findViewById(R.id.webView);
        progress = findViewById(R.id.progress);
        configureWebView();
        if (state == null) webView.loadUrl(HOME_URL); else webView.restoreState(state);
    }

    private void configureWebView() {
        WebSettings s = webView.getSettings();
        s.setJavaScriptEnabled(true);
        s.setDomStorageEnabled(true);
        s.setDatabaseEnabled(true);
        s.setAllowFileAccess(true);
        s.setMediaPlaybackRequiresUserGesture(false);
        s.setLoadWithOverviewMode(true);
        s.setUseWideViewPort(true);
        s.setBuiltInZoomControls(false);
        s.setDisplayZoomControls(false);
        s.setMixedContentMode(WebSettings.MIXED_CONTENT_COMPATIBILITY_MODE);
        s.setUserAgentString(s.getUserAgentString() + " JOBI-NeuroApp/1.0 Android");
        CookieManager.getInstance().setAcceptCookie(true);
        CookieManager.getInstance().setAcceptThirdPartyCookies(webView, true);
        webView.setBackgroundColor(Color.rgb(255, 247, 248));
        webView.setWebViewClient(new WebViewClient() {
            @Override public void onPageFinished(WebView view, String url) {
                super.onPageFinished(view, url);
                installNativeAppEnhancements();
            }
            @Override public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
                return route(request.getUrl());
            }
            @SuppressWarnings("deprecation") @Override public boolean shouldOverrideUrlLoading(WebView view, String url) {
                return route(Uri.parse(url));
            }
        });
        webView.setWebChromeClient(new WebChromeClient() {
            @Override public void onProgressChanged(WebView view, int value) {
                progress.setProgress(value);
                progress.setVisibility(value < 100 ? View.VISIBLE : View.GONE);
            }
            @Override public void onPermissionRequest(PermissionRequest request) {
                runOnUiThread(() -> {
                    if (android.os.Build.VERSION.SDK_INT >= 23 &&
                        checkSelfPermission(Manifest.permission.CAMERA) != PackageManager.PERMISSION_GRANTED) {
                        pendingWebPermission = request;
                        requestPermissions(new String[]{Manifest.permission.CAMERA}, 4103);
                        return;
                    }
                    request.grant(request.getResources());
                });
            }
            @Override public boolean onShowFileChooser(WebView view, ValueCallback<Uri[]> callback, FileChooserParams params) {
                if (fileCallback != null) fileCallback.onReceiveValue(null);
                fileCallback = callback;
                try { startActivityForResult(params.createIntent(), FILE_CHOOSER); }
                catch (ActivityNotFoundException ex) {
                    fileCallback = null;
                    Toast.makeText(MainActivity.this, "No hay un selector de archivos disponible", Toast.LENGTH_LONG).show();
                }
                return true;
            }
        });
    }

    private void installNativeAppEnhancements() {
        String script = "(function(){" +
            "if(location.pathname.toLowerCase().indexOf('/views/ventasapp.php')<0)return;" +
            "document.documentElement.classList.add('jobi-native');" +
            "if(!document.getElementById('jobi-apk-native-style')){" +
              "var s=document.createElement('style');s.id='jobi-apk-native-style';" +
              "s.textContent='#navBar .navbar-toggle,#navBar #myNavbar{display:none!important}#navBar.navbar-style{min-height:64px!important;padding:5px 16px!important;border-radius:0 0 20px 20px!important}#navBar .container-fluid,#navBar .navbar-header{display:flex!important;width:100%!important;min-height:52px!important;align-items:center!important;justify-content:center!important}#navBar .navbar-brand{position:static!important;float:none!important;height:52px!important;margin:0!important;padding:3px 0!important}#navBar .navbar-brand img{width:104px!important;max-height:47px!important}.va-doc-menu{max-height:min(390px,65vh)!important;overflow-y:auto!important;overscroll-behavior:contain}';" +
              "document.head.appendChild(s);}" +
            "function ensureCredit(){var c=window.__jobiCreditType;if(!c)return;" +
              "try{if(typeof state!=='undefined'&&state.types&&!state.types.some(function(x){return String(x.CodSunat)==='07'}))state.types.push(c);}catch(e){}" +
              "var id=String(c.IdTipoDoc||c.id||11),sel=document.getElementById('saleType'),menu=document.getElementById('saleTypeMenu');" +
              "if(sel&&!Array.from(sel.options).some(function(o){return o.value===id})){var op=document.createElement('option');op.value=id;op.textContent=c.TipoDoc||'NOTA DE CREDITO ELEC.';sel.appendChild(op);}" +
              "if(menu&&!menu.querySelector('[data-jobi-credit-note]')){var b=document.createElement('button');b.type='button';b.className='va-doc-option';b.setAttribute('data-jobi-credit-note','1');b.innerHTML='<i class=\"fa fa-file-text-o\"></i><span><b>NOTA DE CREDITO ELEC.</b><small>Requiere documento de referencia y motivo</small></span><i class=\"fa fa-check-circle\"></i>';b.onclick=function(){if(typeof chooseDocType==='function')chooseDocType(id);};menu.appendChild(b);}" +
            "}" +
            "fetch('/api/index.php/ventas/tipos?q=').then(function(r){return r.json()}).then(function(rows){window.__jobiCreditType=(rows||[]).find(function(x){return String(x.CodSunat)==='07'});ensureCredit();var n=0,t=setInterval(function(){ensureCredit();if(++n>20)clearInterval(t)},500)}).catch(function(){});" +
          "})()";
        webView.evaluateJavascript(script, null);
    }

    private boolean route(Uri uri) {
        String scheme = uri.getScheme() == null ? "" : uri.getScheme().toLowerCase();
        String host = uri.getHost() == null ? "" : uri.getHost().toLowerCase();
        if ((scheme.equals("http") || scheme.equals("https")) &&
            (host.equals("jobi.factura.vip") || host.endsWith(".factura.vip"))) {
            String path = uri.getPath() == null ? "/" : uri.getPath().toLowerCase();
            boolean login = path.equals("/") || path.endsWith("/index.php");
            boolean ventasApp = path.endsWith("/views/ventasapp.php");
            boolean support = path.startsWith("/api/") || path.startsWith("/controllers/") ||
                path.startsWith("/resources/") || path.startsWith("/imprimir/") ||
                path.startsWith("/storage/") || path.startsWith("/whatsapp-gateway/");
            if (login || ventasApp || support) return false;
            webView.loadUrl(HOME_URL);
            Toast.makeText(this, "Este acceso está disponible únicamente en Ventas App", Toast.LENGTH_SHORT).show();
            return true;
        }
        try { startActivity(new Intent(Intent.ACTION_VIEW, uri)); return true; }
        catch (Exception ex) { return false; }
    }

    @Override protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == FILE_CHOOSER && fileCallback != null) {
            fileCallback.onReceiveValue(WebChromeClient.FileChooserParams.parseResult(resultCode, data));
            fileCallback = null;
        }
    }

    @Override protected void onSaveInstanceState(Bundle state) { webView.saveState(state); super.onSaveInstanceState(state); }
    @Override public void onRequestPermissionsResult(int requestCode, String[] permissions, int[] results) {
        super.onRequestPermissionsResult(requestCode, permissions, results);
        if (requestCode != 4103 || pendingWebPermission == null) return;
        PermissionRequest request = pendingWebPermission;
        pendingWebPermission = null;
        if (results.length > 0 && results[0] == PackageManager.PERMISSION_GRANTED) {
            request.grant(request.getResources());
            Toast.makeText(this, "Cámara habilitada para escanear", Toast.LENGTH_SHORT).show();
        } else {
            request.deny();
            Toast.makeText(this, "Active el permiso de cámara para usar el escáner", Toast.LENGTH_LONG).show();
        }
    }
    @Override public void onBackPressed() { if (webView.canGoBack()) webView.goBack(); else super.onBackPressed(); }
}
