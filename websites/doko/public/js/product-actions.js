/**
 * Product Actions JavaScript
 * product-actions.js - idempotent implementation
 */
(function(){
    if (!window.ProductManager) {
        window.ProductManager = {
            addToCartWithQuantity: function(productId, quantity = 1, productName = 'Product') {
                quantity = parseInt(quantity) || 1;
                if (typeof window.addToCart === 'function') { window.addToCart(productId, quantity, productName); return; }
                fetch('/api/cart/add.php', { method:'POST', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin', body: JSON.stringify({ product_id: productId, quantity }) })
                .then(r=>r.json()).then(data=>{ if (data && data.success) { window.ProductManager.showNotification('Product added to cart!', 'success'); if (typeof updateCartCount==='function') updateCartCount(); } else if (data && data.message && /auth|login/i.test(data.message)) { window.ProductManager.showNotification('Please login to add items to cart','warning'); setTimeout(()=>{ window.location.href='/login.php?redirect='+encodeURIComponent(location.pathname); },1200); } else { window.ProductManager.showNotification((data && data.message) || 'Failed to add to cart','error'); } }).catch(e=>{ console.error('AddToCart error',e); window.ProductManager.showNotification('Network error adding to cart','error'); });
            },

            toggleWishlist: function(productId, ev){
                const eventRef = ev || window.event;
                fetch('/api/wishlist/wishlist.php', { method:'POST', headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'}, credentials:'same-origin', body: JSON.stringify({ action:'toggle', product_id: productId }) })
                .then(resp=>{ const status = resp.status; return resp.json().then(data=>({status,data})); })
                .then(({status,data})=>{
                    const updateLocal = (id, add)=>{ try{ let wl=JSON.parse(localStorage.getItem('doko_wishlist')||'[]'); wl=Array.isArray(wl)?wl:[]; const exists = wl.some(e=>parseInt(e.product_id)===parseInt(id)); if(add && !exists) wl.push({product_id:parseInt(id)}); if(!add && exists) wl = wl.filter(e=>parseInt(e.product_id)!==parseInt(id)); localStorage.setItem('doko_wishlist', JSON.stringify(wl)); const badge=document.getElementById('wishlist-count'); if (badge) badge.textContent = wl.length.toString(); }catch(e){}
                    };
                    if (data && data.success){ window.ProductManager.showNotification(data.message,'success'); let btn = eventRef && eventRef.target ? eventRef.target.closest('button') : document.querySelector(`[data-wishlist-btn="${productId}"]`); if (btn){ const icon = btn.querySelector('i'); if (icon){ if (data.in_wishlist){ icon.classList.remove('far'); icon.classList.add('fas'); } else { icon.classList.remove('fas'); icon.classList.add('far'); } } } if (typeof updateWishlistCount === 'function') updateWishlistCount(); updateLocal(productId, !!data.in_wishlist); }
                    else {
                        if (status === 401 || (data && data.message && /auth|login|authentication/i.test(data.message))) {
                            try{ let wl=JSON.parse(localStorage.getItem('doko_wishlist')||'[]'); wl=Array.isArray(wl)?wl:[]; const exists = wl.some(e=>parseInt(e.product_id)===parseInt(productId)); if (exists){ wl = wl.filter(e=>parseInt(e.product_id)!==parseInt(productId)); window.ProductManager.showNotification('Removed from local wishlist (login to sync)','warning'); } else { wl.push({product_id:parseInt(productId)}); window.ProductManager.showNotification('Saved to wishlist locally (login to sync)','success'); } localStorage.setItem('doko_wishlist', JSON.stringify(wl)); const badge=document.getElementById('wishlist-count'); if (badge) badge.textContent = wl.length.toString(); }catch(e){ window.ProductManager.showNotification('Please login to use wishlist','warning'); setTimeout(()=>{ window.location.href='/login.php?redirect='+encodeURIComponent(window.location.pathname); },1200); }
                        } else { window.ProductManager.showNotification((data && data.message) || 'Failed to update wishlist','error'); }
                    }
                }).catch(err=>{ console.error('Error updating wishlist',err); window.ProductManager.showNotification('An error occurred. Please try again.','error'); });
            },

            showNotification: function(message, type='info'){ document.querySelectorAll('.product-notification').forEach(n=>n.remove()); const n = document.createElement('div'); n.className = 'product-notification notification-'+type; let icon='info-circle'; if (type==='success') icon='check-circle'; if (type==='error') icon='exclamation-circle'; if (type==='warning') icon='exclamation-triangle'; n.innerHTML = `<i class="fas fa-${icon}"></i><span>${message}</span>`; n.style.cssText = 'position: fixed; top: 80px; right: 20px; padding: 15px 20px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.15); z-index:9999; display:flex; align-items:center; gap:10px; font-weight:500; animation:slideIn 0.3s ease; max-width:400px;'; if (type==='success'){ n.style.background='#10b981'; n.style.color='white'; } else if (type==='error'){ n.style.background='#ef4444'; n.style.color='white'; } else if (type==='warning'){ n.style.background='#f59e0b'; n.style.color='white'; } else { n.style.background='#3b82f6'; n.style.color='white'; } document.body.appendChild(n); setTimeout(()=>{ n.style.animation='slideOut 0.3s ease'; setTimeout(()=>n.remove(),300); },5000); }
        };

        const style = document.createElement('style'); style.textContent = '@keyframes slideIn{from{transform:translateX(100%);opacity:0;}to{transform:translateX(0);opacity:1;}}@keyframes slideOut{from{transform:translateX(0);opacity:1;}to{transform:translateX(100%);opacity:0;}}.product-notification{transition:all 0.3s ease}.product-notification:hover{transform:translateX(-5px);}'; document.head.appendChild(style);
    }

    function updateWishlistCount(){ const badge = document.getElementById('wishlist-count'); if (!badge) return; fetch('/api/wishlist/wishlist.php', { credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest'} }).then(resp=>{ if (resp.status===200) return resp.json().catch(()=>null); if (resp.status===401) throw new Error('unauth'); return null; }).then(data=>{ if (data && data.success && typeof data.count!=='undefined') badge.textContent = String(data.count); else { let wl=[]; try{ wl=JSON.parse(localStorage.getItem('doko_wishlist')||'[]'); }catch(e){wl=[];} badge.textContent = String(Array.isArray(wl)?wl.length:0); } }).catch(()=>{ let wl=[]; try{ wl=JSON.parse(localStorage.getItem('doko_wishlist')||'[]'); }catch(e){wl=[];} badge.textContent = String(Array.isArray(wl)?wl.length:0); }); }

    window.updateWishlistCount = updateWishlistCount;

    if (typeof window.addToCartWithQuantity !== 'function'){ window.addToCartWithQuantity = function(productId, productName){ let qtyInput = document.getElementById('qty-'+productId) || document.getElementById('quantity'); let qty=1; if (qtyInput){ let p=parseInt(qtyInput.value,10); if (!isNaN(p) && p>0) qty=p; } window.ProductManager.addToCartWithQuantity(productId, qty, productName||'Product'); }; }

    if (typeof window.toggleWishlist !== 'function'){ window.toggleWishlist = function(productId, ev){ window.ProductManager.toggleWishlist(productId, ev); }; }

    if (typeof window.removeFromWishlist !== 'function'){ window.removeFromWishlist = async function(productId, ev){ try{ await fetch(`/api/wishlist/wishlist.php?product_id=${productId}`, { method:'DELETE', credentials:'same-origin', headers:{'X-Requested-With':'XMLHttpRequest'} }); }catch(e){} if (typeof updateWishlistCount==='function') try{ updateWishlistCount(); }catch(e){} const item=document.querySelector(`[data-wishlist-item='${productId}']`) || document.querySelector(`.wishlist-item[data-id='${productId}']`); if (item) item.remove(); if (window.ProductManager && typeof window.ProductManager.showNotification==='function') window.ProductManager.showNotification('Item removed from wishlist','success'); }; }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', updateWishlistCount); else updateWishlistCount();

})();