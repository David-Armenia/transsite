/* =================================
   Search filter (cards)
================================= */
document.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.querySelector('.search-input');
  const cards = document.querySelectorAll('.card');

  if (!searchInput || !cards.length) return;

  searchInput.addEventListener('input', () => {
    const query = searchInput.value.toLowerCase().trim();

    cards.forEach(card => {
      const text = card.innerText.toLowerCase();
      card.style.display = text.includes(query) ? '' : 'none';
    });
  });
});


/* =================================
   Request form (AJAX) — SINGLE handler
================================= */
document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.request-form');
  const resultBox = document.querySelector('.request-result');

  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (resultBox) {
      resultBox.style.display = 'none';
      resultBox.textContent = '';
    }

    const fd = new FormData(form);
    fd.append('action', 'transescort_create_request');
    fd.append('nonce', (window.TRANS && TRANS.nonce) ? TRANS.nonce : '');
      const pid = form.dataset.profile ? String(form.dataset.profile) : "";
      if (pid) fd.append("profile_id", pid);


    try {
      const res = await fetch((window.TRANS && TRANS.ajaxurl) ? TRANS.ajaxurl : "/wp-admin/admin-ajax.php", {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      });

      const data = await res.json();

      if (resultBox) {
        resultBox.classList.remove("is-success", "is-error");

        if (data.success) {
          resultBox.classList.add("is-success");
          resultBox.textContent = (data.data?.message || "Заявка отправлена ✅");
        } else {
          resultBox.classList.add("is-error");
          resultBox.textContent = (data.data?.message || "Ошибка. Попробуйте ещё раз.");
        }

        resultBox.style.display = "block";
        resultBox.scrollIntoView({ behavior: "smooth", block: "center" });
      }

      if (data.success) {
        form.reset();
      }
    } catch (err) {
      if (resultBox) {
        resultBox.textContent = 'Server error. Try again.';
        resultBox.style.display = 'block';
      }
    }
  });
});


/* =================================
   Video modal
================================= */
(function () {
  const modal = document.getElementById('videoModal');
  const player = document.getElementById('videoModalPlayer');
  if (!modal || !player) return;

  function openModal(url) {
    if (!url) return;

    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');

    player.pause();
    player.removeAttribute('src');
    player.load();

    player.src = url;
    player.load();
    player.play().catch(() => {});
  }

  function closeModal() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');

    player.pause();
    player.removeAttribute('src');
    player.load();
  }

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.video-open');
    if (btn) {
      openModal(btn.getAttribute('data-video'));
      return;
    }

    if (
      e.target?.getAttribute?.('data-close') === '1'
    ) {
      closeModal();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
})();


/* =================================
   Gallery lightbox (profiles)
================================= */
(function(){
  const lb = document.getElementById('galleryLightbox');
  const img = document.getElementById('galleryLightboxImg');
  if (!lb || !img) return;

  document.addEventListener('click', (e) => {
    const item = e.target.closest('.profile-gallery-item');
    if (item) {
      const full = item.dataset.full;
      if (!full) return;

      img.src = full;
      lb.classList.add('is-open');
      lb.setAttribute('aria-hidden','false');
      return;
    }

    if (
      e.target.classList.contains('gallery-lightbox__backdrop') ||
      e.target.classList.contains('gallery-lightbox__close')
    ) {
      lb.classList.remove('is-open');
      lb.setAttribute('aria-hidden','true');
      img.src = '';
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && lb.classList.contains('is-open')) {
      lb.classList.remove('is-open');
      lb.setAttribute('aria-hidden','true');
      img.src = '';
    }
  });
})();


// === Reveal on scroll (IntersectionObserver) ===
(function(){
  try{
    var items = document.querySelectorAll(".profile-card, .home-section");
    items.forEach(function(el){ el.classList.add("reveal"); });
    if (!("IntersectionObserver" in window)) {
      items.forEach(function(el){ el.classList.add("is-visible"); });
      return;
    }
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if (e.isIntersecting){
          e.target.classList.add("is-visible");
          io.unobserve(e.target);
        }
      });
    }, { rootMargin: "100px 0px" });
    items.forEach(function(el){ io.observe(el); });
  }catch(e){}
})();


// === Skeleton for images ===
(function(){
  try{
    var media = document.querySelectorAll(".profile-card__media");
    media.forEach(function(m){
      var img = m.querySelector("img");
      if (!img) return;
      m.classList.add("skeleton");
      if (img.complete){ m.classList.remove("skeleton"); return; }
      img.addEventListener("load", function(){ m.classList.remove("skeleton"); }, { once:true });
      img.addEventListener("error", function(){ m.classList.remove("skeleton"); }, { once:true });
    });
  }catch(e){}
})();


/* Favorites (AJAX) */
document.addEventListener("click", async function(e){
  const btn = e.target.closest(".js-fav-toggle");
  if (!btn) return;

  // if button is inside a link, don't navigate
  e.preventDefault();
  e.stopPropagation();

  const profileId = btn.getAttribute("data-profile");
  const nonce = btn.getAttribute("data-nonce");
  if (!profileId || !nonce) return;

  btn.disabled = true;
  try {
    const body = new URLSearchParams();
    body.append("action", "te_toggle_favorite_profile");
    body.append("profile_id", profileId);
    body.append("nonce", nonce);

    const ajaxurl = (window.TRANS && TRANS.ajaxurl) ? TRANS.ajaxurl : "/wp-admin/admin-ajax.php";
    const res = await fetch(ajaxurl, {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8" },
      body: body.toString()
    });

    const json = await res.json();
    if (!json || !json.success) {
      if (res.status === 401) alert("Нужно войти, чтобы добавлять в избранное.");
      return;
    }

    const active = !!json.data.active;

    // Update favorites counter in header (if exists)
    const c = document.getElementById("favCount");
    if (c && json.data && typeof json.data.count !== "undefined") {
      const n = parseInt(json.data.count, 10) || 0;
      c.textContent = String(n);
      c.style.display = n > 0 ? "flex" : "none";
    }

    btn.classList.toggle("is-active", active);
    btn.textContent = active ? "♥" : "♡";

    // If it's the big button on single page
    if (btn.classList.contains("sp-like")) {
      btn.textContent = active ? "♥ В избранном" : "♡ Добавить в избранное";
    }
  } catch (err) {
    console.error(err);
  } finally {
    btn.disabled = false;
  }
});

/* Favorites (AJAX) */
document.addEventListener("click", async function(e){
  const btn = e.target && (e.target.closest ? e.target.closest(".js-fav-toggle") : null);
  if(!btn) return;

  e.preventDefault();

  const profileId = parseInt(btn.getAttribute("data-profile") || "0", 10);
  if(!profileId) return;

  if(!(window.TRANS && TRANS.isLoggedIn)){
    window.location.href = (window.TRANS && TRANS.loginUrl) ? TRANS.loginUrl : "/login/";
    return;
  }

  try{
    btn.disabled = true;

    const fd = new FormData();
    fd.append("action", "te_toggle_favorite_profile");
    fd.append("nonce", (window.TRANS && TRANS.favNonce) ? TRANS.favNonce : "");
    fd.append("profile_id", String(profileId));

    const res = await fetch((window.TRANS && TRANS.ajaxurl) ? TRANS.ajaxurl : "/wp-admin/admin-ajax.php", {
      method: "POST",
      credentials: "same-origin",
      body: fd
    });

    const data = await res.json();
    if(!data || !data.success){
      btn.disabled = false;
      return;
    }

    const active = !!(data.data && data.data.active);

    // обновим все кнопки для этого profileId на странице
    document.querySelectorAll('.js-fav-toggle[data-profile="'+profileId+'"]').forEach(function(b){
      b.classList.toggle("is-active", active);
      const t = b.querySelector(".sp-fav-text");
      if(t) t.textContent = active ? "В избранном" : "В избранное";
    });

  }catch(err){
    // молча, чтобы не ломать UI
  }finally{
    btn.disabled = false;
  }
});

/* ============ TE Lightbox (single-profile gallery) ============ */
(function(){
  function qs(sel, root){ return (root||document).querySelector(sel); }
  function qsa(sel, root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }

  // collect gallery items from PHP (same order as $gallery_ids)
  function collectGalleryUrls(){
    // Берем из кнопок .sp-gitem background-image (fast, no extra data attrs required)
    var items = qsa('.single-profile .sp-gitem');
    if (!items.length) return [];

    return items.map(function(el){
      var bg = (el.style && el.style.backgroundImage) ? el.style.backgroundImage : '';
      // url("...")
      var m = bg.match(/url\(["']?(.*?)["']?\)/i);
      return m ? m[1] : '';
    }).filter(Boolean);
  }

  var urls = [];
  var lb, img, countEl, btnPrev, btnNext, btnClose;
  var index = 0;

  function build(){
    lb = document.createElement('div');
    lb.className = 'te-lb';
    lb.innerHTML = ''
      + '<div class="te-lb__panel" role="dialog" aria-modal="true">'
      +   '<img class="te-lb__img" alt="">'
      +   '<button type="button" class="te-lb__close" aria-label="Close">✕</button>'
      +   '<button type="button" class="te-lb__prev" aria-label="Prev">‹</button>'
      +   '<button type="button" class="te-lb__next" aria-label="Next">›</button>'
      +   '<div class="te-lb__count"></div>'
      + '</div>';

    document.body.appendChild(lb);

    img = qs('.te-lb__img', lb);
    countEl = qs('.te-lb__count', lb);
    btnPrev = qs('.te-lb__prev', lb);
    btnNext = qs('.te-lb__next', lb);
    btnClose = qs('.te-lb__close', lb);

    lb.addEventListener('click', function(e){
      if (e.target === lb) close();
    });
    btnClose.addEventListener('click', close);
    btnPrev.addEventListener('click', function(){ go(index - 1); });
    btnNext.addEventListener('click', function(){ go(index + 1); });

    document.addEventListener('keydown', function(e){
      if (!lb.classList.contains('is-open')) return;
      if (e.key === 'Escape') close();
      if (e.key === 'ArrowLeft') go(index - 1);
      if (e.key === 'ArrowRight') go(index + 1);
    });
  }

  function open(i){
    if (!urls.length) urls = collectGalleryUrls();
    if (!urls.length) return;

    if (!lb) build();

    index = Math.max(0, Math.min(i, urls.length - 1));
    render();
    lb.classList.add('is-open');
    document.documentElement.style.overflow = 'hidden';
  }

  function close(){
    if (!lb) return;
    lb.classList.remove('is-open');
    document.documentElement.style.overflow = '';
  }

  function go(i){
    if (!urls.length) return;
    if (i < 0) i = urls.length - 1;
    if (i >= urls.length) i = 0;
    index = i;
    render();
  }

  function render(){
    var src = urls[index] || '';
    img.src = src;
    countEl.textContent = (index + 1) + ' / ' + urls.length;
  }

  // Click handler: any element with data-te-lightbox opens by index
  document.addEventListener('click', function(e){
    var t = e.target;

    // if user clicks inside hero image
    var hero = t.closest ? t.closest('.single-profile .sp-hero[data-te-lightbox="1"]') : null;
    if (hero){
      e.preventDefault();
      open(0);
      return;
    }

    var item = t.closest ? t.closest('.single-profile [data-te-lightbox="1"][data-index]') : null;
    if (item){
      e.preventDefault();
      var i = parseInt(item.getAttribute('data-index') || '0', 10);
      open(isNaN(i) ? 0 : i);
      return;
    }

    // side items (если клик по самому div)
    var side = t.closest ? t.closest('.single-profile .sp-side-item[data-te-lightbox="1"]') : null;
    if (side){
      e.preventDefault();
      var si = parseInt(side.getAttribute('data-index') || '0', 10);
      // side thumbs correspond to first 2 of gallery; open 1 or 2 (best effort)
      open(Math.max(1, Math.min(2, si + 1)));
      return;
    }
  });

})();

/* ============ SINGLE PROFILE — SWIPE GALLERY (TOUCH/DRAG) ============ */
document.addEventListener("DOMContentLoaded", function () {
  var gallery = document.querySelector(".single-profile .sp-gallery");
  if (!gallery) return;

  var items = Array.prototype.slice.call(gallery.querySelectorAll(".sp-gitem"));
  if (!items.length) return;

  // mark in-view (simple observer)
  if ("IntersectionObserver" in window) {
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if (e.isIntersecting) e.target.classList.add("is-inview");
        else e.target.classList.remove("is-inview");
      });
    }, { root: gallery, threshold: 0.6 });

    items.forEach(function(el){ io.observe(el); });
  } else {
    items[0].classList.add("is-inview");
  }

  // drag/swipe
  var isDown = false;
  var startX = 0;
  var startScroll = 0;
  var moved = false;

  function pageX(e){
    return (e.touches && e.touches[0]) ? e.touches[0].pageX : e.pageX;
  }

  gallery.addEventListener("pointerdown", function(e){
    isDown = true;
    moved = false;
    startX = pageX(e);
    startScroll = gallery.scrollLeft;
    gallery.classList.add("is-dragging");
    gallery.setPointerCapture && gallery.setPointerCapture(e.pointerId);
  }, {passive:true});

  gallery.addEventListener("pointermove", function(e){
    if (!isDown) return;
    var x = pageX(e);
    var walk = (x - startX);
    if (Math.abs(walk) > 3) moved = true;
    gallery.scrollLeft = startScroll - walk;
  }, {passive:true});

  function endDrag(){
    if (!isDown) return;
    isDown = false;
    gallery.classList.remove("is-dragging");

    // snap to nearest item (smooth)
    var gRect = gallery.getBoundingClientRect();
    var best = null;
    var bestDist = Infinity;

    items.forEach(function(el){
      var r = el.getBoundingClientRect();
      var dist = Math.abs((r.left - gRect.left));
      if (dist < bestDist) { bestDist = dist; best = el; }
    });

    if (best) {
      var left = best.offsetLeft;
      gallery.scrollTo({ left: left, behavior: "smooth" });
    }
  }

  gallery.addEventListener("pointerup", endDrag, {passive:true});
  gallery.addEventListener("pointercancel", endDrag, {passive:true});
  gallery.addEventListener("mouseleave", endDrag, {passive:true});

  // avoid opening links when user swiped
  gallery.addEventListener("click", function(e){
    if (!moved) return;
    e.preventDefault();
    e.stopPropagation();
  }, true);
});

