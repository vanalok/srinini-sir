/* ==========================================================================
   SRINIVASULU IFS · app.js
   Shared behaviors used across all pages.
   ========================================================================== */

(function(){
  'use strict';

  /* ---- Lucide icons ---- */
  function renderIcons(){ if (window.lucide && lucide.createIcons) lucide.createIcons(); }
  window.SrIcons = renderIcons;

  /* ---- Theme + a11y toggles ---- */
  function persist(k, v){ try{ localStorage.setItem(k,v); }catch(e){} }
  function read(k){ try{ return localStorage.getItem(k); }catch(e){ return null; } }

  function applyStoredPrefs(){
    const html = document.documentElement;
    const t = read('sr-theme'); if (t) html.setAttribute('data-theme', t);
    const c = read('sr-contrast'); if (c) html.setAttribute('data-contrast', c);
    const f = read('sr-fs'); if (f) html.setAttribute('data-fs', f);
    const l = read('sr-lang') || 'en'; html.setAttribute('lang', l);
  }
  applyStoredPrefs();

  function bindToggles(){
    const html = document.documentElement;
    document.querySelectorAll('[data-toggle="theme"]').forEach(b => {
      b.addEventListener('click', () => {
        const cur = html.getAttribute('data-theme');
        const next = cur === 'dark' ? '' : 'dark';
        if (next) html.setAttribute('data-theme', next); else html.removeAttribute('data-theme');
        persist('sr-theme', next);
        b.setAttribute('aria-pressed', String(next === 'dark'));
      });
      b.setAttribute('aria-pressed', String(html.getAttribute('data-theme') === 'dark'));
    });
    document.querySelectorAll('[data-toggle="contrast"]').forEach(b => {
      b.addEventListener('click', () => {
        const cur = html.getAttribute('data-contrast');
        const next = cur === 'high' ? '' : 'high';
        if (next) html.setAttribute('data-contrast', next); else html.removeAttribute('data-contrast');
        persist('sr-contrast', next);
        b.setAttribute('aria-pressed', String(next === 'high'));
      });
      b.setAttribute('aria-pressed', String(html.getAttribute('data-contrast') === 'high'));
    });
    document.querySelectorAll('[data-toggle="fs"]').forEach(b => {
      b.addEventListener('click', () => {
        const cur = html.getAttribute('data-fs') || 'a';
        const next = cur === 'a' ? 'a+' : cur === 'a+' ? 'a++' : '';
        if (next) html.setAttribute('data-fs', next); else html.removeAttribute('data-fs');
        persist('sr-fs', next || '');
        b.textContent = next ? (next === 'a+' ? 'A+' : 'A++') : 'A';
      });
      const cur = html.getAttribute('data-fs');
      b.textContent = cur === 'a+' ? 'A+' : cur === 'a++' ? 'A++' : 'A';
    });
    document.querySelectorAll('[data-toggle="lang"]').forEach(sel => {
      sel.value = html.getAttribute('lang') || 'en';
      sel.addEventListener('change', () => {
        html.setAttribute('lang', sel.value);
        persist('sr-lang', sel.value);
      });
    });
  }

  /* ---- Mobile drawer ---- */
  function bindMobileDrawer(){
    const drawer = document.getElementById('mobileDrawer');
    const scrim = document.getElementById('mobileScrim');
    const open = document.getElementById('hamburger');
    const close = document.getElementById('mobileClose');
    if (!drawer || !open) return;
    const openIt = () => {
      drawer.classList.add('is-open'); scrim.classList.add('is-open');
      open.setAttribute('aria-expanded','true'); document.body.style.overflow='hidden';
    };
    const closeIt = () => {
      drawer.classList.remove('is-open'); scrim.classList.remove('is-open');
      open.setAttribute('aria-expanded','false'); document.body.style.overflow='';
    };
    open.addEventListener('click', openIt);
    close && close.addEventListener('click', closeIt);
    scrim && scrim.addEventListener('click', closeIt);
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && drawer.classList.contains('is-open')) closeIt(); });
  }

  /* ---- Sticky nav shrink ---- */
  function bindNavShrink(){
    const nav = document.querySelector('.nav-bar');
    if (!nav) return;
    let last = 0;
    const onScroll = () => {
      const y = window.scrollY;
      nav.classList.toggle('is-scrolled', y > 24);
      last = y;
    };
    window.addEventListener('scroll', onScroll, {passive:true});
    onScroll();
  }

  /* ---- Reveal on scroll ---- */
  function bindReveal(){
    const items = document.querySelectorAll('.reveal');
    if (!items.length || !('IntersectionObserver' in window)){
      items.forEach(i => i.classList.add('is-in')); return;
    }
    const io = new IntersectionObserver((entries) => {
      entries.forEach(e => { if (e.isIntersecting){ e.target.classList.add('is-in'); io.unobserve(e.target); } });
    }, {threshold:.12, rootMargin:'0px 0px -40px 0px'});
    items.forEach(i => io.observe(i));
  }

  /* ---- Department drawer ---- */
  const DeptDrawer = (function(){
    let drawer, scrim, lastFocus;
    function ensure(){
      if (drawer) return;
      drawer = document.createElement('aside');
      drawer.className = 'drawer';
      drawer.setAttribute('role','dialog');
      drawer.setAttribute('aria-modal','true');
      drawer.setAttribute('aria-label','Department details');
      document.body.appendChild(drawer);

      scrim = document.createElement('div');
      scrim.className = 'scrim';
      scrim.addEventListener('click', close);
      document.body.appendChild(scrim);
      document.addEventListener('keydown', e => { if (e.key === 'Escape' && drawer.classList.contains('is-open')) close(); });
    }
    function open(html){
      ensure();
      drawer.innerHTML = html;
      drawer.classList.add('is-open');
      scrim.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      lastFocus = document.activeElement;
      const c = drawer.querySelector('.drawer-close');
      c && c.addEventListener('click', close);
      renderIcons();
      // Tab switching inside drawer
      drawer.querySelectorAll('.drawer-tabs .tab').forEach(t => {
        t.addEventListener('click', () => {
          drawer.querySelectorAll('.drawer-tabs .tab').forEach(x => x.classList.remove('is-active'));
          drawer.querySelectorAll('.tab-panel').forEach(x => x.classList.remove('is-active'));
          t.classList.add('is-active');
          const id = t.dataset.target;
          const panel = drawer.querySelector('#' + id);
          panel && panel.classList.add('is-active');
        });
      });
      setTimeout(() => {
        const f = drawer.querySelector('button, a, [tabindex="0"]');
        f && f.focus();
      }, 100);
    }
    function close(){
      if (!drawer) return;
      drawer.classList.remove('is-open');
      scrim.classList.remove('is-open');
      document.body.style.overflow = '';
      // Clear URL param
      const url = new URL(location.href);
      url.searchParams.delete('dept');
      history.replaceState(null,'',url);
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    return { open, close };
  })();
  window.DeptDrawer = DeptDrawer;

  /* ---- Photo viewer ---- */
  const PhotoViewer = (function(){
    let items = [], idx = 0, root;
    function ensure(){
      if (root) return;
      root = document.createElement('div');
      root.className = 'viewer';
      root.setAttribute('role','dialog'); root.setAttribute('aria-modal','true'); root.setAttribute('aria-label','Photo viewer');
      root.innerHTML = `
        <div class="viewer-top">
          <span class="viewer-counter" id="vCount"></span>
          <span class="viewer-title" id="vTitle"></span>
          <div class="viewer-actions">
            <button class="v-btn" id="vInfo" aria-label="Toggle info"><i data-lucide="info"></i></button>
            <button class="v-btn" id="vShare" aria-label="Share"><i data-lucide="share-2"></i></button>
            <button class="v-btn" id="vDownload" aria-label="Download"><i data-lucide="download"></i></button>
            <button class="v-btn" id="vClose" aria-label="Close"><i data-lucide="x"></i></button>
          </div>
        </div>
        <div class="viewer-stage">
          <button class="viewer-nav prev" id="vPrev" aria-label="Previous"><i data-lucide="chevron-left"></i></button>
          <img id="vImg" alt="">
          <button class="viewer-nav next" id="vNext" aria-label="Next"><i data-lucide="chevron-right"></i></button>
        </div>
        <aside class="viewer-info" id="vInfoPanel" aria-label="Image information">
          <h3 id="vInfoTitle">Image details</h3>
          <dl id="vInfoList"></dl>
        </aside>`;
      document.body.appendChild(root);
      root.querySelector('#vClose').addEventListener('click', close);
      root.querySelector('#vPrev').addEventListener('click', () => show(idx - 1));
      root.querySelector('#vNext').addEventListener('click', () => show(idx + 1));
      root.querySelector('#vInfo').addEventListener('click', () => root.querySelector('#vInfoPanel').classList.toggle('is-open'));
      root.querySelector('#vDownload').addEventListener('click', () => {
        const a = document.createElement('a'); a.href = items[idx].src; a.download = items[idx].src.split('/').pop(); a.click();
      });
      root.querySelector('#vShare').addEventListener('click', async () => {
        const item = items[idx];
        const url = location.origin + '/' + (item.src || '');
        if (navigator.share){
          try{ await navigator.share({title: item.alt || 'Photo', url}); }catch(e){}
        } else {
          await navigator.clipboard.writeText(url);
        }
      });
      document.addEventListener('keydown', e => {
        if (!root.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowLeft') show(idx - 1);
        if (e.key === 'ArrowRight') show(idx + 1);
        if (e.key === 'i' || e.key === 'I') root.querySelector('#vInfoPanel').classList.toggle('is-open');
      });
      renderIcons();
    }
    function open(list, startIdx){ ensure(); items = list; idx = startIdx || 0; root.classList.add('is-open'); document.body.style.overflow='hidden'; show(idx); }
    function show(i){
      if (!items.length) return;
      idx = (i + items.length) % items.length;
      const it = items[idx];
      root.querySelector('#vImg').src = it.src;
      root.querySelector('#vImg').alt = it.alt || '';
      root.querySelector('#vCount').textContent = (idx + 1) + ' / ' + items.length;
      root.querySelector('#vTitle').textContent = it.title || it.alt || '';
      const list = root.querySelector('#vInfoList');
      list.innerHTML = '';
      const meta = [
        ['Category', it.category],
        ['File', (it.src || '').split('/').pop()],
        ['Caption', it.alt || '—']
      ];
      meta.forEach(([k,v]) => {
        if (!v) return;
        list.innerHTML += `<dt>${k}</dt><dd>${v}</dd>`;
      });
    }
    function close(){ root.classList.remove('is-open'); document.body.style.overflow=''; }
    return { open, close };
  })();
  window.PhotoViewer = PhotoViewer;

  /* ---- Video viewer ---- */
  const VideoViewer = (function(){
    let items = [], idx = 0, root;
    function ensure(){
      if (root) return;
      root = document.createElement('div');
      root.className = 'viewer';
      root.setAttribute('role','dialog'); root.setAttribute('aria-modal','true'); root.setAttribute('aria-label','Video player');
      root.innerHTML = `
        <div class="viewer-top">
          <span class="viewer-counter" id="vvCount"></span>
          <span class="viewer-title" id="vvTitle"></span>
          <div class="viewer-actions">
            <button class="v-btn" id="vvShare" aria-label="Share"><i data-lucide="share-2"></i></button>
            <button class="v-btn" id="vvYoutube" aria-label="Open on YouTube"><i data-lucide="external-link"></i></button>
            <button class="v-btn" id="vvClose" aria-label="Close"><i data-lucide="x"></i></button>
          </div>
        </div>
        <div class="viewer-stage">
          <button class="viewer-nav prev" id="vvPrev" aria-label="Previous"><i data-lucide="chevron-left"></i></button>
          <iframe id="vvFrame" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
          <button class="viewer-nav next" id="vvNext" aria-label="Next"><i data-lucide="chevron-right"></i></button>
        </div>`;
      document.body.appendChild(root);
      root.querySelector('#vvClose').addEventListener('click', close);
      root.querySelector('#vvPrev').addEventListener('click', () => show(idx - 1));
      root.querySelector('#vvNext').addEventListener('click', () => show(idx + 1));
      root.querySelector('#vvYoutube').addEventListener('click', () => window.open(items[idx].url, '_blank'));
      root.querySelector('#vvShare').addEventListener('click', async () => {
        const it = items[idx];
        if (navigator.share){ try{ await navigator.share({title: it.title, url: it.url}); }catch(e){} }
        else { await navigator.clipboard.writeText(it.url); }
      });
      document.addEventListener('keydown', e => {
        if (!root.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'ArrowLeft') show(idx - 1);
        if (e.key === 'ArrowRight') show(idx + 1);
      });
      renderIcons();
    }
    function open(list, startIdx){
      ensure(); items = list; idx = startIdx || 0;
      root.classList.add('is-open'); document.body.style.overflow='hidden'; show(idx);
    }
    function show(i){
      idx = (i + items.length) % items.length;
      const it = items[idx];
      root.querySelector('#vvFrame').src = 'https://www.youtube-nocookie.com/embed/' + it.videoId + '?autoplay=1&rel=0';
      root.querySelector('#vvCount').textContent = (idx + 1) + ' / ' + items.length;
      root.querySelector('#vvTitle').textContent = it.title;
    }
    function close(){
      root.classList.remove('is-open');
      document.body.style.overflow='';
      root.querySelector('#vvFrame').src = '';
    }
    return { open, close };
  })();
  window.VideoViewer = VideoViewer;

  /* ---- Share helper ---- */
  window.SrShare = async function(title, url){
    if (navigator.share){
      try{ await navigator.share({title, url}); return; }catch(e){}
    }
    try{ await navigator.clipboard.writeText(url); alert('Link copied'); }catch(e){}
  };

  /* ---- Search overlay ---- */
  function bindSearch(){
    const trigger = document.getElementById('searchTrigger');
    const overlay = document.getElementById('searchOverlay');
    const closeBtn = document.getElementById('searchClose');
    const input = document.getElementById('searchInput');
    if (!trigger || !overlay) return;
    const open = () => { overlay.classList.add('is-open'); document.body.style.overflow='hidden'; setTimeout(()=>input && input.focus(), 50); };
    const close = () => { overlay.classList.remove('is-open'); document.body.style.overflow=''; };
    trigger.addEventListener('click', open);
    closeBtn && closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', e => { if (e.target === overlay) close(); });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && overlay.classList.contains('is-open')) close();
      if ((e.ctrlKey || e.metaKey) && e.key === 'k'){ e.preventDefault(); open(); }
    });
  }

  /* ---- Boot ---- */
  document.addEventListener('DOMContentLoaded', () => {
    renderIcons();
    bindToggles();
    bindMobileDrawer();
    bindNavShrink();
    bindReveal();
    bindSearch();
  });
})();
