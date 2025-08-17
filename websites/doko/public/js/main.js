// Main global JS helpers (initial minimal scaffold)
// Ensures addToCartWithQuantity etc. exist on pages that did not explicitly include product-actions earlier.
(function(){
  if (typeof window.addToCartWithQuantity !== 'function' && window.ProductManager) {
    window.addToCartWithQuantity = function(productId, productName){
      var qtyInput = document.getElementById('qty-'+productId) || document.getElementById('quantity');
      var qty = 1;
      if (qtyInput) {
        var parsed = parseInt(qtyInput.value,10); if (!isNaN(parsed) && parsed>0) qty = parsed;
      }
      window.ProductManager.addToCartWithQuantity(productId, qty, productName || 'Product');
    };
  }
  if (typeof window.toggleWishlist !== 'function' && window.ProductManager) {
    window.toggleWishlist = function(productId, ev){ window.ProductManager.toggleWishlist(productId, ev); };
  }
})();

// Hero Background Slider (continuous scrolling, no fade, local images only)
window.HeroBackgroundSlider = window.HeroBackgroundSlider || (function(){
  // Use the project's local hero images (public/images). Include all six slider images.
  const imagePaths = [
    '/images/Slider1.jpg',
    '/images/Slider2.jpg',
    '/images/Slider5.jpg',
    '/images/Slider6.jpg',
    '/images/Silder3.jpg',
    '/images/Silder4.jpg'
  ];

  let trackEl = null; let animationDuration = 60; // seconds for a full cycle (adjust for speed)

  function buildSlides(){
    trackEl = document.getElementById('hero-slide-track');
    if(!trackEl) return;
    trackEl.innerHTML='';
    trackEl.classList.add('scroll-mode');
    // Create one sequence
    imagePaths.forEach(src => {
      const slide = document.createElement('div');
      slide.className = 'hero-slide';
      slide.style.backgroundImage = `url(${src})`;
      trackEl.appendChild(slide);
    });
    // Duplicate sequence for seamless loop
    imagePaths.forEach(src => {
      const slide = document.createElement('div');
      slide.className = 'hero-slide';
      slide.style.backgroundImage = `url(${src})`;
      trackEl.appendChild(slide);
    });

    applyDynamicSizing();
  }

  function preloadAll(){
    imagePaths.forEach(src => { const img = new Image(); img.src = src; });
  }

  function applyDynamicSizing(){
    if(!trackEl) return;
    // Each slide should take full viewport width for big, bold images
    const slides = trackEl.querySelectorAll('.hero-slide');
    const vw = trackEl.clientWidth || window.innerWidth;
    slides.forEach(slide => { slide.style.width = vw + 'px'; });
    // Adjust animation duration relative to number of slides so speed remains pleasant
    const totalWidth = vw * slides.length; // px to travel
    const pxPerSecondTarget = 40; // lower = slower scroll
    animationDuration = Math.max(30, Math.round(totalWidth / pxPerSecondTarget));
    injectKeyframes(totalWidth / 2); // we translate one sequence width (half of total)
  }

  function injectKeyframes(distance){
    let styleTag = document.getElementById('hero-scroll-dynamic');
    if(!styleTag){ styleTag = document.createElement('style'); styleTag.id='hero-scroll-dynamic'; document.head.appendChild(styleTag); }
    styleTag.textContent = `@keyframes heroScrollDynamic {0%{transform:translateX(0);}100%{transform:translateX(-${distance}px);}}\n`+
      `.hero-with-slider .hero-background-slider .hero-slide-track.scroll-mode{display:flex;height:100%;animation:heroScrollDynamic ${animationDuration}s linear infinite;}`+
      `.hero-with-slider .hero-background-slider .hero-slide-track.scroll-mode .hero-slide{flex:0 0 auto;position:relative;inset:auto;opacity:1;transition:none;background-size:cover;background-position:center;}`;
  }

  function onResize(){ applyDynamicSizing(); }

  function init(){
    buildSlides();
    preloadAll();
    window.addEventListener('resize', debounce(onResize, 250));
  }

  // Small debounce helper
  function debounce(fn, delay){ let t; return function(){ clearTimeout(t); t=setTimeout(()=>fn.apply(this, arguments), delay); }; }

  return { init };
})();
