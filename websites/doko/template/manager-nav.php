<?php
/**
 * Manager Navigation Partial
 * Renders a slim navigation bar below the global storefront header so manager pages
 * share the same look & footer. Keeps logic minimal; path links are absolute.
 */
$script = $_SERVER['SCRIPT_NAME'] ?? '';
function doko_manager_active(string $section, string $script): string {
    return (strpos($script, "/manager/$section/") !== false) ? 'active' : '';
}
?>
<nav class="admin-subnav">
    <div class="container">
        <div class="admin-brand"><a href="/manager/dashboard/" class="admin-logo-link">DOKO <span>Manager</span></a></div>
        <ul class="admin-subnav-list">
            <li><a class="<?php echo doko_manager_active('dashboard', $script); ?>" href="/manager/dashboard/">Dashboard</a></li>
            <li><a class="<?php echo doko_manager_active('orders', $script); ?>" href="/manager/orders/">Orders <span id="manager-pending-count" class="badge-pill" style="display:none;">0</span></a></li>
            <li><a class="<?php echo doko_manager_active('products', $script); ?>" href="/manager/products/">Products <span id="manager-products-count" class="badge-pill" style="display:none;">0</span></a></li>
            <li class="admin-subnav-spacer"></li>
            <li class="admin-subnav-right"><a href="/" class="return-site">← Storefront</a></li>
            <li class="admin-subnav-right"><a href="/logout.php" class="logout-link" onclick="return confirm('Logout from manager panel?');">Logout</a></li>
        </ul>
    </div>
</nav>
<script>
// Fallback: replace Font Awesome <i> icons with inline SVG if font fails (avoids ▯ boxes)
(function(){
    const faMap={
        'fa-users':'<svg aria-hidden="true" viewBox="0 0 640 512" class="fa-svg" width="16" height="16" fill="currentColor"><path d="M96 224c35.3 0 64-28.7 64-64s-28.7-64-64-64S32 124.7 32 160s28.7 64 64 64zm384 0c35.3 0 64-28.7 64-64s-28.7-64-64-64s-64 28.7-64 64s28.7 64 64 64zm32 32h-64c-35.3 0-64 28.7-64 64v32h192v-32c0-35.3-28.7-64-64-64zM208 256h224c-26.5 0-48 21.5-48 48v48H256v-48c0-26.5-21.5-48-48-48zm-80 0H64c-35.3 0-64 28.7-64 64v32h128v-32c0-23.6 12.9-44.2 32-55.4V304c0-17.7 7.2-33.7 18.7-45.3C130.4 257.1 113.8 256 96 256zm352 0c-17.8 0-34.4 1.1-50.7 2.7C409.8 270.3 417 286.3 417 304v-7.4c19.1 11.2 32 31.8 32 55.4v32h128v-32c0-35.3-28.7-64-64-64h-64z"/></svg>',
        'fa-user-plus':'<svg aria-hidden="true" viewBox="0 0 640 512" width="16" height="16" fill="currentColor"><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm51.2 48h-6.3c-22.5 10.3-47.7 16-74.9 16s-52.4-5.7-74.9-16h-6.3C51.2 304 0 355.2 0 419.2v28.8C0 472.9 39.1 512 87.1 512H361c5.1 0 10.1-.5 14.9-1.4c-1.3-7.1-1.9-14.4-1.9-21.8c0-76.5 55.4-140.1 128-150.9V352c0-53-43-96-96-96H275.2zM616 128h-56V72c0-8.8-7.2-16-16-16s-16 7.2-16 16v56H472c-8.8 0-16 7.2-16 16s7.2 16 16 16h56v56c0 8.8 7.2 16 16 16s16-7.2 16-16V160h56c8.8 0 16-7.2 16-16s-7.2-16-16-16z"/></svg>',
        'fa-exclamation-triangle':'<svg aria-hidden="true" viewBox="0 0 576 512" width="16" height="16" fill="currentColor"><path d="M569.5 440L327.4 40c-11.7-19.5-40.1-19.5-51.8 0L6.5 440C-5.2 459.5 8.9 484 31 484h514c22.1 0 36.2-24.5 24.5-44zM288 176c13.3 0 24 10.7 24 24v96c0 13.3-10.7 24-24 24s-24-10.7-24-24v-96c0-13.3 10.7-24 24-24zm0 224c-17.7 0-32-14.3-32-32 0-17.6 14.3-32 32-32s32 14.4 32 32c0 17.7-14.3 32-32 32z"/></svg>',
        'fa-user-edit':'<svg aria-hidden="true" viewBox="0 0 640 512" width="16" height="16" fill="currentColor"><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm89.6 32H265.2c-22.5 10.3-47.7 16-74.9 16s-52.4-5.7-74.9-16H89.6C40.1 288 0 328.1 0 377.6V416c0 17.7 14.3 32 32 32h290.9c-2.2-10.2-2.9-20.9-.5-31.6l12.8-57.3c2.4-10.6 7.4-20.4 14.4-28.6L313.8 320zM631.6 292.3l-45.3-45.3c-12.5-12.5-32.8-12.5-45.3 0L392.3 395.7c-7 7-12 15.8-14.4 25.3l-12.8 57.3c-2.9 13.2 8.9 24.9 22.1 22.1l57.3-12.8c9.5-2.1 18.3-7.1 25.3-14.1L631.6 337.7c12.5-12.5 12.5-32.8 0-45.3z"/></svg>',
        'fa-eye':'<svg aria-hidden="true" viewBox="0 0 576 512" width="16" height="16" fill="currentColor"><path d="M572.5 241.4C518.1 135.5 407.8 64 288 64S57.9 135.5 3.5 241.4a48.07 48.07 0 0 0 0 45.2C57.9 376.5 168.2 448 288 448s230.1-71.5 284.5-161.4c9.6-18.1 9.6-39.5 0-57.2zM288 400c-88.2 0-168.5-51.2-211.8-128C119.5 195.2 199.8 144 288 144s168.5 51.2 211.8 128C456.5 348.8 376.2 400 288 400zm0-208a80 80 0 1 0 0 160 80 80 0 1 0 0-160z"/></svg>',
        'fa-user-slash':'<svg aria-hidden="true" viewBox="0 0 640 512" width="16" height="16" fill="currentColor"><path d="M633.8 458.1L23 6.1C15.2 .7 4.8 2.2-.6 10S-2.2 27.2 5.6 32.6l84.5 61.6C78.3 114.5 64 142.7 64 176c0 70.7 57.3 128 128 128c12.2 0 23.9-1.7 35-4.9l24.4 17.8C240.8 322.8 224 349.2 224 379.2V408c0 5.6 .9 11 2.6 16H32c-17.7 0-32 14.3-32 32s14.3 32 32 32H486.4l106 77.3c7.8 5.4 18.2 3.9 23.6-3.9s3.9-18.2-3.9-23.6L520.2 425.4c2.6-2.6 5.2-5.1 7.6-7.9C552.5 390.1 576 348.6 576 304c0-70.7-57.3-128-128-128c-12.2 0-23.9 1.7-35 4.9l-42.3-30.9c8.2-18 12.3-37.6 12.3-58c0-61.9-33.7-115.8-84.5-144.1c-9.3-5.2-21-1.7-26.2 7.7c-5.2 9.3-1.7 21 7.7 26.2C322 0 352 48.4 352 112c0 20.7-4.3 40.3-12.3 58c-6.7 14.7-15.8 28.1-27 39.7l-27.3-20c8.8-8.6 16.4-18.6 22.1-29.9c11.3-23.2 17.5-49.2 17.5-76.7c0-79.5-45-150.3-117.6-186.1c-9.8-4.9-21.9-.9-26.8 8.9s-.9 21.9 8.9 26.8C224.1 8.6 272 73 272 144c0 21.6-4 42.3-11.3 61.3c-2.8 7.5-6.2 14.6-10 21.4l-33.7-24.6c3-5.5 5.7-11.3 8-17.3c8.7-26.7 13.3-54.1 13.3-82.7c0-33.2-8.2-64.4-22.6-92H526.9c17.7 0 32-14.3 32-32S544.6 0 526.9 0H142.3c-5.4 0-10.5 1.4-15 3.8L181 38.4c28.8 26.2 51 59.8 64.3 97c5.3 14.8 8.2 30.4 8.2 46.5c0 21.6-4 42.3-11.3 61.3c-2.8 7.5-6.2 14.6-10 21.4l-33.7-24.6c3-5.5 5.7-11.3 8-17.3c8.7-26.7 13.3-54.1 13.3-82.7c0-54-21-104.3-57.9-142.8L121.1 20c-7.8-5.4-18.2-3.9-23.6 3.9S93.6 42.1 101.4 47.5L580.2 400.7c30.8-24.3 51.8-62.4 51.8-104.7c0-70.7-57.3-128-128-128c-12.2 0-23.9 1.7-35 4.9l-27.3-19.9c8.8-8.6 16.4-18.6 22.1-29.9c11.3-23.2 17.5-49.2 17.5-76.7c0-44.6-15.7-84.2-41.9-115.6c-7.6-9.1-21.1-10.3-30.3-2.6s-10.3 21.1-2.6 30.3C503.7 68.1 512 88.4 512 112c0 21.6-4 42.3-11.3 61.3c-2.8 7.5-6.2 14.6-10 21.4l-27.3-20c8.8-8.6 16.4-18.6 22.1-29.9c11.3-23.2 17.5-49.2 17.5-76.7c0-88.4-50.6-164.5-124.1-196.7c-10.3-4.4-22.2 .4-26.6 10.7c-4.4 10.3 .4 22.2 10.7 26.6C448.9 43.5 496 112.7 496 192c0 21.6-4 42.3-11.3 61.3c-2.8 7.5-6.2 14.6-10 21.4l-27.3-20c8.8-8.6 16.4-18.6 22.1-29.9c11.3-23.2 17.5-49.2 17.5-76.7c0-115.2-93.5-208.7-208.7-208.7H272c-8.8 0-16 7.2-16 16s7.2 16 16 16c115.2 0 208.7 93.5 208.7 208.7c0 28.6-5.9 56-16.4 81.4l-34-24.8c2.3-5.7 4.8-11.4 6.6-17.5c8.7-26.7 13.3-54.1 13.3-82.7c0-61.9-33.7-115.8-84.5-144.1c-9.3-5.2-21-1.7-26.2 7.7c-5.2 9.3-1.7 21 7.7 26.2C370 0 400 48.4 400 112c0 20.7-4.3 40.3-12.3 58c-6.7 14.7-15.8 28.1-27 39.7l-27.3-20c8.8-8.6 16.4-18.6 22.1-29.9c11.3-23.2 17.5-49.2 17.5-76.7c0-79.5-45-150.3-117.6-186.1c-9.8-4.9-21.9-.9-26.8 8.9s-.9 21.9 8.9 26.8C224.1 8.6 272 73 272 144c0 21.6-4 42.3-11.3 61.3c-2.8 7.5-6.2 14.6-10 21.4l-33.7-24.6c3-5.5 5.7-11.3 8-17.3c8.7-26.7 13.3-54.1 13.3-82.7c0-33.2-8.2-64.4-22.6-92H497.1"/></svg>',
        'fa-user-check':'<svg aria-hidden="true" viewBox="0 0 640 512" width="16" height="16" fill="currentColor"><path d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm89.6 32H265.2c-22.5 10.3-47.7 16-74.9 16s-52.4-5.7-74.9-16H89.6C40.1 288 0 328.1 0 377.6V416c0 17.7 14.3 32 32 32H352c0-70.7 57.3-128 128-128h32c17.7 0 32-14.3 32-32s-14.3-32-32-32h-32c-23.6 0-44.2 12.9-55.4 32H313.6zM500.7 75.1L448 127.8 419.3 99.1c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l57.4 57.4c12.5 12.5 32.8 12.5 45.3 0L546 165.7c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0z"/></svg>',
        'fa-archive':'<svg aria-hidden="true" viewBox="0 0 512 512" width="16" height="16" fill="currentColor"><path d="M32 32C14.3 32 0 46.3 0 64v64c0 17.7 14.3 32 32 32V464c0 26.5 21.5 48 48 48H432c26.5 0 48-21.5 48-48V160c17.7 0 32-14.3 32-32V64c0-17.7-14.3-32-32-32H32zm96 128H384c8.8 0 16 7.2 16 16v32c0 8.8-7.2 16-16 16H112c-8.8 0-16-7.2-16-16V176c0-8.8 7.2-16 16-16z"/></svg>'
    };
    function fontAwesomeLikelyMissing(){
        try{const test=document.createElement('i');test.className='fas fa-users';test.style.position='absolute';test.style.left='-9999px';document.body.appendChild(test);const fam=getComputedStyle(test).fontFamily;document.body.removeChild(test);return !/Font Awesome/i.test(fam);}catch(e){return true;}
    }
    function replace(){
        document.querySelectorAll('i').forEach(function(el){
            for(const cls of el.classList){
                if(faMap[cls]){ el.outerHTML=faMap[cls]; break; }
            }
        });
    }
    if(document.readyState==='complete' || document.readyState==='interactive'){
        setTimeout(function(){ if(fontAwesomeLikelyMissing()) replace(); },600);
    } else {
        document.addEventListener('DOMContentLoaded', function(){ setTimeout(function(){ if(fontAwesomeLikelyMissing()) replace(); },600); });
    }
})();
</script>
<style>
/* Manager nav badge */
.badge-pill{background:#2563eb;color:#fff;border-radius:999px;padding:2px 6px;font-size:11px;font-weight:600;vertical-align:middle;margin-left:4px;line-height:1;}
</style>
<script>
// Fetch pending orders count for badge
(function(){
  async function fetchPendingCount(){
    try {
      const res = await fetch('/api/orders/stats.php', {credentials:'same-origin'});
      const data = await res.json();
      if(data && typeof data.pending_orders === 'number' && data.pending_orders > 0){
        const el = document.getElementById('manager-pending-count');
        if(el){ el.textContent = data.pending_orders; el.style.display='inline-block'; }
      }
    } catch(e) { /* silent */ }
  }
  if(document.readyState==='complete' || document.readyState==='interactive') setTimeout(fetchPendingCount, 300); else document.addEventListener('DOMContentLoaded',()=>setTimeout(fetchPendingCount,300));
})();

// Fetch product count (lightweight) and display in badge
(function(){
  async function fetchCount(){
    try {
      const res = await fetch('/api/products/list.php?limit=1', {credentials:'same-origin'});
      const data = await res.json();
      if(data && typeof data.total === 'number'){
        const el = document.getElementById('manager-products-count');
        if(el){ el.textContent = data.total; el.style.display='inline-block'; }
      }
    } catch(e) { /* silent */ }
  }
  if(document.readyState==='complete' || document.readyState==='interactive') setTimeout(fetchCount, 300); else document.addEventListener('DOMContentLoaded',()=>setTimeout(fetchCount,300));
})();
</script>
