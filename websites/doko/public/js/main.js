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

// Hero Background Slider (lightweight)
window.HeroBackgroundSlider = window.HeroBackgroundSlider || (function(){
  // Strict grocery category bundle images (no lifestyle people, clear food focus)
  // Each item: src (landscape), label displayed as overlay
  const images = [
    { src: '/images/hero/hero-vegetables.jpg', fallback: 'https://images.unsplash.com/photo-1542834369-f10ebf06d3cb?auto=format&fit=crop&w=1600&h=900&q=70', label: 'Fresh Vegetables' },
    { src: '/images/hero/hero-fruits.jpg',     fallback: 'https://images.unsplash.com/photo-1576402187878-974f70c890a5?auto=format&fit=crop&w=1600&h=900&q=70', label: 'Seasonal Fruits' },
    { src: '/images/hero/hero-grains.jpg',     fallback: 'https://images.unsplash.com/photo-1601050690597-df0568f70950?auto=format&fit=crop&w=1600&h=900&q=70', label: 'Grains & Pulses' },
    { src: '/images/hero/hero-snacks.jpg',     fallback: 'https://images.unsplash.com/photo-1585238341974-3be318f9c8f0?auto=format&fit=crop&w=1600&h=900&q=70', label: 'Snacks & Packaged' },
    { src: '/images/hero/hero-dairy.jpg',      fallback: 'https://images.unsplash.com/photo-1580910051074-7c8141d4b0d5?auto=format&fit=crop&w=1600&h=900&q=70', label: 'Dairy & Eggs' },
    { src: '/images/hero/hero-spices.jpg',     fallback: 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?auto=format&fit=crop&w=1600&h=900&q=70', label: 'Spices & Herbs' },
    { src: '/images/hero/hero-bakery.jpg',     fallback: 'https://images.unsplash.com/photo-1608198093002-ad4e005484b2?auto=format&fit=crop&w=1600&h=900&q=70', label: 'Bakery & Breads' }
  ];

  const interval = 6500; // ms per slide
  let trackEl = null; let idx = 0; let timer = null; let built = false;

  function ensureOverlayStyles(){
    if(document.getElementById('hero-slide-label-style')) return;
    const st=document.createElement('style');
    st.id='hero-slide-label-style';
    st.textContent=`.hero-slide-label{position:absolute;left:1.25rem;bottom:1.25rem;padding:.55rem 1rem;font-size:.9rem;letter-spacing:.5px;background:rgba(0,0,0,.45);color:#fff;border-radius:6px;font-weight:600;backdrop-filter:blur(4px);box-shadow:0 4px 12px rgba(0,0,0,.25);max-width:70%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:inherit}.hero-slide.active .hero-slide-label{animation:fadeInLabel .8s ease both}@keyframes fadeInLabel{0%{opacity:0;transform:translateY(8px)}100%{opacity:1;transform:translateY(0)}}`;
    document.head.appendChild(st);
  }

  function createSlide(imageObj, makeActive=false){
    const d=document.createElement('div');
    d.className='hero-slide';
    if(makeActive) d.classList.add('active');
    d.style.backgroundImage = `url(${imageObj.src})`;
    // Label overlay
    ensureOverlayStyles();
    if(imageObj.label){
      const label=document.createElement('div');
      label.className='hero-slide-label';
      label.textContent=imageObj.label;
      d.appendChild(label);
    }
    return d;
  }

  function preload(src, fallback){
    return new Promise((resolve,reject)=>{
      const img=new Image();
      img.onload=()=>resolve(src);
      img.onerror=()=>{
        if(fallback){
          const f = new Image();
          f.onload=()=>resolve(fallback);
          f.onerror=()=>reject(src);
          f.src=fallback;
        } else reject(src);
      };
      img.src=src;
    });
  }

  function build(){
    trackEl = document.getElementById('hero-slide-track');
    if(!trackEl) return;
    trackEl.classList.remove('scroll-mode');
    trackEl.innerHTML='';
    // initial slide
  trackEl.appendChild(createSlide(images[0], true));
    built = true;
    // Preload second
  if(images[1]) preload(images[1].src, images[1].fallback).then(res=>{ if(res!==images[1].src) images[1].src=res; }).catch(()=>{});
  }

  function goNext(){
    if(!built || !trackEl) return;
    const nextIndex = (idx + 1) % images.length;
    const currentSlide = trackEl.querySelector('.hero-slide.active');
    const nextObj = images[nextIndex];
    preload(nextObj.src, nextObj.fallback).then(resolved=>{
      if(resolved !== nextObj.src){ nextObj.src = resolved; }
      const nextSlide = createSlide(nextObj, false);
      trackEl.appendChild(nextSlide);
      requestAnimationFrame(()=>{
        nextSlide.classList.add('active');
        if(currentSlide) currentSlide.classList.remove('active');
        // remove old slides (keep only last 2 to limit DOM growth)
        const slides = trackEl.querySelectorAll('.hero-slide');
        if(slides.length>2) trackEl.removeChild(slides[0]);
        idx = nextIndex;
        // Preload following image
  const follow = images[(idx+1)%images.length];
  preload(follow.src, follow.fallback).then(res=>{ if(res!==follow.src) follow.src=res; }).catch(()=>{});
      });
  }).catch(()=>{ // skip corrupted image
      idx = nextIndex; goNext();
    });
  }

  function start(){ stop(); timer = setInterval(goNext, interval); }
  function stop(){ if(timer){ clearInterval(timer); timer=null; } }
  function init(){ build(); start(); }
  return { init, start, stop };
})();
