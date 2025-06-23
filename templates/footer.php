<?php $c = include(__DIR__.'/../config.php'); ?>
</main>
<footer>
  <p>
    <?=htmlspecialchars($c['footer_text'])?>
    &nbsp; | &nbsp;
    <a href="/privacy/" style="color:var(--primary-color); text-decoration:underline;">Privacy Policy</a>
  </p>
</footer>
<!-- Cookie Notice BEGIN -->
<div id="cookie-notice" style="display:none;">
  <div class="cookie-box">
    <div>
      This website uses essential cookies to function.<br><br>
      We also use optional tracking cookies (Google Analytics) to help us measure traffic and improve performance.<br>
      <br>
      You can disable tracking cookies below if you do not want to be tracked.<br>
      <a href="/privacy/" style="color:var(--primary-color);text-decoration:underline;font-size:0.97em;display:inline-block;margin:7px 0 3px 0;" tabindex="0">Read our Privacy Policy</a>
      <label style="font-weight:500;display:flex;align-items:center;gap:7px;margin-top:14px;">
        Allow Google Analytics:
        <input type="checkbox" id="ga-opt" checked>
        <span class="slider"></span>
        <span style="font-size:0.95em;font-weight:400;">Tracking cookies</span>
      </label>
    </div>
    <div class="cookie-actions">
      <button id="cookie-accept" class="cookie-btn">OK</button>
    </div>
  </div>
</div>
<style>
#cookie-notice {
  position: fixed;
  bottom: 22px;
  right: 22px;
  z-index: 3500;
}
.cookie-box {
  background: #fffbea;
  border: 1.3px solid #f4de99;
  color: #775720;
  border-radius: 13px;
  padding: 20px 22px 14px 19px;
  box-shadow: 0 4px 24px #8a5f0012;
  min-width: 228px;
  max-width: 340px;
  font-size: 15px;
  display: flex;
  flex-direction: column;
  gap: 0.4em;
}
.cookie-actions {
  display: flex;
  justify-content: flex-end;
  margin-top: 8px;
}
.cookie-btn {
  background: var(--primary-color, #ffa927);
  color: #fff;
  font-weight: bold;
  border: none;
  padding: 7px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 1em;
}
.slider {
  display:inline-block;
  width:36px; height:20px;
  border-radius:20px;
  background:#d6cca0;
  position:relative;
  vertical-align:middle;
  margin:0 7px;
  transition: background .18s;
}
#ga-opt {
  position:absolute; opacity:0; width:0; height:0;
}
.slider:before {
  content: '';
  position: absolute;
  left: 2.5px; top: 2.5px;
  width: 15px; height: 15px;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 1px 4px #66700218;
  transition: .15s;
}
#ga-opt:checked + .slider {
  background: var(--primary-color, #ffa927);
}
#ga-opt:checked + .slider:before {
  transform: translateX(16px);
}
@media (max-width: 700px) {
  #cookie-notice {left:5vw; right:5vw; max-width:95vw;}
}
</style>
<script>
window.GA_MEASUREMENT_ID = <?=json_encode($c['google_analytics_id'] ?? '')?>;
(function() {
  function getCookie(n) {
    let m = document.cookie.match('(^|;)\\s*'+n+'\\s*=\\s*([^;]+)');
    return m ? m.pop() : null;
  }
  function setCookie(n, v, d) {
    let date = new Date();
    date.setTime(date.getTime() + (d*24*60*60*1000));
    document.cookie = n + "=" + v + "; expires=" + date.toUTCString() + "; path=/";
  }
  function showCookieNotice() {
    document.getElementById('cookie-notice').style.display = '';
  }
  function hideCookieNotice() {
    document.getElementById('cookie-notice').style.display = 'none';
  }
  document.addEventListener('DOMContentLoaded', function() {
    if(getCookie('cb_cookie_notice') !== '1') {
      showCookieNotice();
      var gaOpt = document.getElementById('ga-opt');
      gaOpt.checked = true;
      document.getElementById('cookie-accept').onclick = function() {
        setCookie('cb_cookie_notice', '1', 365);
        setCookie('cb_ga', gaOpt.checked ? '1' : '0', 365);
        hideCookieNotice();
        if(window.GA_MEASUREMENT_ID && !gaOpt.checked) {
          window['ga-disable-' + window.GA_MEASUREMENT_ID] = true;
        }
      };
      gaOpt.onchange = function() {};
    }
    if(window.GA_MEASUREMENT_ID && getCookie('cb_ga') === '0') {
      window['ga-disable-' + window.GA_MEASUREMENT_ID] = true;
    }
  });
})();
</script>
<?php if (!empty($c['google_analytics_id']) && (empty($_COOKIE['cb_ga']) || $_COOKIE['cb_ga'] === '1')): ?>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?=htmlspecialchars($c['google_analytics_id'])?>"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '<?=$c['google_analytics_id']?>');
</script>
<?php endif; ?>
</body>
</html>
