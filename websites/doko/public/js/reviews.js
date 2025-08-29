(function(){
    function $(sel, ctx) { return (ctx || document).querySelector(sel); }
    function $all(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

    function api(path, opts){
        const headers = {'Content-Type':'application/json'};
        const token = window._csrf_token || document.querySelector('meta[name="csrf-token"]')?.content;
        if(token) headers['X-CSRF-Token'] = token;
        return fetch(path, Object.assign({credentials:'include', headers}, opts)).then(r=>r.json());
    }

    let latestReviews = [];
    function setStarWidgetValue(val){
        const starWidgetEl = document.getElementById('review-star-widget');
        const starSelectEl = document.getElementById('review-rating');
        if(starSelectEl) starSelectEl.value = val;
        if(!starWidgetEl) return;
        const stars = Array.from(starWidgetEl.querySelectorAll('.star-input'));
        stars.forEach(s => {
            const v = Number(s.dataset.value);
            if (v <= val) { s.classList.remove('far'); s.classList.add('fas'); s.style.color='#f6c34d'; }
            else { s.classList.remove('fas'); s.classList.add('far'); s.style.color='#ccc'; }
        });
    }

    function refreshReviews(productId){
        api(`/api/products/product-reviews-list.php?product_id=${productId}`, {method:'GET'})
        .then(json=>{
            if(!json.success) return;
            const list = $('#reviews-list');
            if(!list) return;
            list.innerHTML = '';
            latestReviews = json.reviews || [];
            // Render Google-like reviews: header with average + count, then cards with truncated text and Read more
            const reviews = json.reviews || [];
            const header = document.createElement('div');
            header.className = 'reviews-header google-reviews-header';
            const avg = reviews.length ? (reviews.reduce((s,r)=>s + Number(r.rating||0),0) / reviews.length) : 0;
            header.innerHTML = `
                <div class="reviews-summary">
                    <div class="avg-rating">${avg ? (Math.round(avg * 10) / 10).toFixed(1) : '0.0'}</div>
                    <div class="avg-stars">${renderStars(Math.round(avg))}</div>
                    <div class="count">${reviews.length} review${reviews.length!==1?'s':''}</div>
                </div>
                <div class="write-action">
                    <button id="open-write-review" class="btn btn-outline">Write a review</button>
                </div>
            `;
            list.appendChild(header);

            if(reviews.length){
                reviews.forEach(r=>{
                    const div = document.createElement('div');
                    div.className = 'review google-review-card';
                    div.dataset.reviewId = r.review_id;

                    const statusClass = r.status === 'approved' ? 'status-approved' : r.status === 'rejected' ? 'status-rejected' : 'status-pending';
                    const statusBadge = r.status && r.status !== 'approved' ? `<span class="review-badge ${statusClass}">${escapeHtml(r.status)}</span>` : '';

                    // truncate review text for compact view
                    const rawText = r.review || '';
                    const maxLen = 320;
                    const shortText = rawText.length > maxLen ? escapeHtml(rawText.slice(0, maxLen)) + '...' : escapeHtml(rawText);
                    const needsMore = rawText.length > maxLen;

                    div.innerHTML = `
                        <div class="gr-left">
                            <img class="gr-avatar" src="${escapeHtml(r.profile_image||'/public/images/default-avatar.png')}" alt="">
                        </div>
                        <div class="gr-right">
                            <div class="gr-head">
                                <strong class="gr-name">${escapeHtml(r.username||'Guest')}</strong>
                                <span class="gr-rating">${renderStars(r.rating)}</span>
                                <span class="gr-date">${new Date(r.created_at).toLocaleDateString()}</span>
                                ${statusBadge}
                            </div>
                            <div class="gr-body">
                                ${r.title?`<div class="gr-title">${escapeHtml(r.title)}</div>`:''}
                                <div class="gr-text" data-full="${escapeHtml(rawText)}">${shortText}${needsMore?` <a href="#" class="read-more">Read more</a>`:''}</div>
                            </div>
                            <div class="gr-actions">
                                ${r.can_edit?`<button class="btn btn-outline btn-sm review-edit" data-id="${r.review_id}">Edit</button>`:''}
                                ${r.can_delete?`<button class="btn btn-danger btn-sm review-delete" data-id="${r.review_id}">Delete</button>`:''}
                            </div>
                        </div>
                    `;
                    list.appendChild(div);
                });
            } else {
                const p = document.createElement('p');
                p.textContent = 'No reviews yet. Be the first to review this product.';
                list.appendChild(p);
            }
            // wire up write button to focus the review form
            const writeBtn = document.getElementById('open-write-review');
            if(writeBtn){ writeBtn.addEventListener('click', ()=>{ document.querySelector('.review-form')?.scrollIntoView({behavior:'smooth', block:'center'}); document.getElementById('review-title')?.focus(); }); }
        }).catch(()=>{});
    }

    function renderStars(n){
        let out='';
        for(let i=1;i<=5;i++) out += i<=n ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
        return out;
    }

    function escapeHtml(s){ if(!s) return ''; return s.replace(/[&<>\"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c];}); }

    // Wire up form
    document.addEventListener('DOMContentLoaded', ()=>{
        const productIdMeta = document.querySelector('meta[name="product-id"]');
        const productId = productIdMeta?productIdMeta.content: (window.PRODUCT_ID||null);
        if(!productId) return;
        refreshReviews(productId);

        const submit = document.getElementById('submit-review');
        if(submit){
            submit.addEventListener('click', async (ev)=>{
                // prevent double actions
                if (submit.disabled) return;
                const id = document.getElementById('review-id').value || null;
                const titleEl = document.getElementById('review-title');
                const textEl = document.getElementById('review-text');
                const ratingEl = document.getElementById('review-rating');
                const rating = Number(ratingEl ? ratingEl.value : 5) || 5;

                // Basic validation
                if (!rating || rating < 1 || rating > 5) { alert('Please select a rating'); return; }
                // Optionally require text
                if (!textEl.value || textEl.value.trim().length < 5) { if(!confirm('Your review is short. Submit anyway?')) return; }

                const payload = {product_id: Number(productId), rating: rating, title: (titleEl.value||'').trim(), review: (textEl.value||'').trim() };
                const method = id ? 'PUT' : 'POST';
                if(id) payload.review_id = Number(id);

                // UI loading state
                const origText = submit.textContent;
                submit.disabled = true;
                submit.textContent = id ? 'Saving...' : 'Submitting...';

                try {
                    const resp = await api('/api/products/product-reviews-' + (id? 'update.php' : 'create.php'), {method, body: JSON.stringify(payload)});
                    if(resp && resp.success){
                        // Reset form
                        document.getElementById('review-id').value = '';
                        titleEl.value = '';
                        textEl.value = '';
                        // reset select and star UI
                        if (ratingEl) ratingEl.value = 5;
                        if (typeof setStarWidgetValue === 'function') setStarWidgetValue(5);
                        // Refresh list to show pending/approved as per server logic
                        refreshReviews(productId);
                        alert(resp.message || 'Saved');
                    } else {
                        alert((resp && resp.message) || 'Failed to save review');
                    }
                } catch (e) {
                    console.error('Review submit error', e);
                    alert('Network error submitting review');
                } finally {
                    submit.disabled = false;
                    submit.textContent = origText;
                }
            });
        }

        const cancel = document.getElementById('cancel-edit');
        if(cancel){ cancel.addEventListener('click', ()=>{
            document.getElementById('review-id').value = '';
            document.getElementById('review-title').value = '';
            document.getElementById('review-text').value = '';
            const ratingEl = document.getElementById('review-rating'); if (ratingEl) ratingEl.value = 5;
            if (typeof setStarWidgetValue === 'function') setStarWidgetValue(5);
        }); }

        // Delegate delete/edit buttons
        document.body.addEventListener('click', (e)=>{
            const del = e.target.closest('.review-delete');
            if(del){
                const id = del.dataset.id;
                if(!confirm('Delete this review?')) return;
                api('/api/products/product-reviews-delete.php', {method:'DELETE', body: JSON.stringify({review_id: Number(id)})})
                .then(resp=>{ if(resp.success){ refreshReviews(productId); alert(resp.message); } else alert(resp.message||'Failed'); });
                return;
            }
            const ed = e.target.closest('.review-edit');
            if(ed){
                const id = ed.dataset.id;
                const found = latestReviews.find(r => String(r.review_id) === String(id));
                if(!found) {
                    // fallback to API fetch
                    api(`/api/products/product-reviews-list.php?product_id=${productId}`, {method:'GET'}).then(json=>{
                        if(!json.success) return;
                        latestReviews = json.reviews || [];
                        const f2 = latestReviews.find(r => String(r.review_id) === String(id));
                        if(!f2) return alert('Review data not available');
                        document.getElementById('review-id').value = f2.review_id;
                        document.getElementById('review-title').value = f2.title || '';
                        document.getElementById('review-text').value = f2.review || '';
                        setStarWidgetValue(f2.rating || 5);
                        window.scrollTo({top: document.querySelector('.review-form').offsetTop - 20, behavior:'smooth'});
                    });
                    return;
                }
                document.getElementById('review-id').value = found.review_id;
                document.getElementById('review-title').value = found.title || '';
                document.getElementById('review-text').value = found.review || '';
                setStarWidgetValue(found.rating || 5);
                window.scrollTo({top: document.querySelector('.review-form').offsetTop - 20, behavior:'smooth'});
            }
        });

        // Star input widget: sync clicks to hidden select
        const starWidget = document.getElementById('review-star-widget');
        const starSelect = document.getElementById('review-rating');
        if (starWidget && starSelect) {
            starWidget.addEventListener('click', (ev)=>{
                const star = ev.target.closest('.star-input');
                if (!star) return;
                const val = Number(star.dataset.value) || 1;
                // set select value
                if (starSelect.querySelector(`option[value="${val}"]`)) starSelect.value = val;
                // update UI
                const stars = Array.from(starWidget.querySelectorAll('.star-input'));
                stars.forEach(s => {
                    const v = Number(s.dataset.value);
                    if (v <= val) { s.classList.remove('far'); s.classList.add('fas'); s.style.color='#f6c34d'; }
                    else { s.classList.remove('fas'); s.classList.add('far'); s.style.color='#ccc'; }
                });
            });
            // initialize default value
            setStarWidgetValue(5);
        }
    });

})();
