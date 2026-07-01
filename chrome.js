/* Shared chrome (utility bar, nav, footer) — injected on every page.
   Keeps the 8 pages in lockstep without a build step. */
(function(){
  // Alphabetized dropdowns
  const DEPTS_ALPHA = [
    { slug:'academics', name:'Academics' },
    { slug:'adcl', name:'ADCL' },
    { slug:'ayush', name:'AYUSH' },
    { slug:'chitradurga', name:'Chitradurga' },
    { slug:'ecology-environment', name:'Ecology & Environment' },
    { slug:'kalaburagi', name:'Kalaburagi' },
    { slug:'kali-tiger-reserve', name:'Kali Tiger Reserve' },
    { slug:'karnataka-forest-department',name:'Karnataka Forest Department' },
    { slug:'kfcsc', name:'KFCSC' },
    { slug:'kspcb', name:'KSPCB' },
    { slug:'nagarhole-national-park', name:'Nagarhole National Park' },
    { slug:'shimoga', name:'Shimoga' }
  ];
  window.SR_DEPTS_ALPHA = DEPTS_ALPHA;

  // Canonical career order — matches the CV Experience timeline (cv.html#career),
  // most-recent posting first. This is the single source of truth for the
  // department card order on index.html, accomplishments.html, etc.
  // Academics has no posting in the CV timeline, so it sits by its own date.
  window.SR_DEPTS_CV = [
    { slug:'ecology-environment', name:'Ecology & Environment', code:'F-12', period:'2022-Present', kind:'Principal Secretary' },
    { slug:'karnataka-forest-department', name:'Karnataka Forest Department', code:'F-10', period:'2017-2020', kind:'Wildlife Wing' },
    { slug:'ayush', name:'AYUSH', code:'F-11', period:'2020-2022', kind:'Traditional Medicine' },
    { slug:'kfcsc', name:'KFCSC', code:'F-06', period:'2012-2014', kind:'Cooperative Body' },
    { slug:'kspcb', name:'KSPCB', code:'F-07', period:'2014-2017', kind:'Pollution Board' },
    { slug:'shimoga', name:'Shimoga Division', code:'F-02', period:'2002-2005', kind:'District Forest' },
    { slug:'adcl', name:'ADCL', code:'F-05', period:'2010-2012', kind:'Welfare Corporation' },
    { slug:'kali-tiger-reserve', name:'Kali Tiger Reserve', code:'F-08', period:'2013-2015', kind:'Tiger Reserve' },
    { slug:'chitradurga', name:'Chitradurga Division', code:'F-01', period:'1999-2002', kind:'District Forest' },
    { slug:'academics', name:'Academics', code:'F-04', period:'2008-2010', kind:'Research & Teaching' },
    { slug:'kalaburagi', name:'Kalaburagi Division', code:'F-03', period:'2005-2008', kind:'District Forest' },
    { slug:'nagarhole-national-park', name:'Nagarhole National Park', code:'F-09', period:'2015-2017', kind:'National Park' },
  ];

  // Logo URLs
  window.SR_LOGOS = {
    'kspcb': 'images/kspcb_logo.png',
    'ayush': 'images/ayush_logo.png',
    'kalaburagi': 'images/kalaburagi_logo.png',
    'shimoga': 'images/shimoga_logo.png',
    'academics': 'images/academics_logo.png',
    'chitradurga': 'images/chitradurga_logo.png',
    'kfcsc': 'images/kfcsc_logo.png',
    'ecology-environment': 'images/gok_logo.png',
    'karnataka-forest-department': 'images/karnataka_forest_department_logo.png',
    'adcl': 'images/adcl_logo.png',
    'nagarhole-national-park': 'images/nagarhole-national-park_logo.png',
    'kali-tiger-reserve': 'images/kali-tiger-reserve_logo.png',
    'family': 'images/family/banner_logo.webp'
  };

  function ddItems(page){
    return DEPTS_ALPHA.map(d => `<li><a href="${ROOT}dept.html?dept=${d.slug}&page=${page}">${d.name}</a></li>`).join('')
      + `<li><a href="${ROOT}${page}.html" class="dd-all">View all departments →</a></li>`;
  }

  function ddItemsPhotos(){
    return DEPTS_ALPHA.map(d => `<li><a href="${ROOT}dept.html?dept=${d.slug}&page=photos">${d.name}</a></li>`).join('')
      + `<li><a href="${ROOT}dept.html?dept=family&page=photos">Family</a></li>`
      + `<li><a href="${ROOT}photos.html" class="dd-all">View all departments →</a></li>`;
  }

  // active page
  const path = (location.pathname.split('/').pop() || 'index.html').toLowerCase();
  // Path prefix back to project root (handles pages inside subfolders like /posts/)
  const ROOT = '../'.repeat(Math.max(0, location.pathname.split('/').filter(Boolean).length - 1));
  function active(name){ return path === name ? ' class="is-current"' : ''; }
  function mActive(name){ return path === name ? ' class="is-current"' : ''; }

  const navbar = `
    <header class="nav-bar" role="navigation" aria-label="Primary">
      <div class="container">
        <a href="${ROOT}index.html" class="brand">
          <span class="brand-mark">S</span>
          <span>
            <span class="brand-name">Srinivasulu IFS</span>
            <span class="brand-sub">Indian Forest Service</span>
          </span>
        </a>
        <ul class="nav-primary">
          <li${active('index.html')}><a href="${ROOT}index.html" title="Home"><i data-lucide="home"></i></a></li>
          <li${active('cv.html')}><a href="${ROOT}cv.html" title="CV"><i data-lucide="user"></i></a></li>
          <li${active('honours.html')}><a href="${ROOT}honours.html" title="Honours"><i data-lucide="award"></i></a></li>
          <li${active('accomplishments.html')}><a href="${ROOT}accomplishments.html" title="Accomplishments"><i data-lucide="briefcase"></i></a></li>
          <li${active('publications.html')}><a href="${ROOT}publications.html" title="Publications"><i data-lucide="book-open"></i></a></li>
          <li${active('photos.html')}><a href="${ROOT}photos.html" title="Photos"><i data-lucide="image"></i></a></li>
          <li${active('videos.html')}><a href="${ROOT}videos.html" title="Videos"><i data-lucide="play-circle"></i></a></li>
          <li${active('audios.html')}><a href="${ROOT}audios.html" title="Audios"><i data-lucide="headphones"></i></a></li>
          <li${active('blog.html')}><a href="${ROOT}blog.html" title="Blog"><i data-lucide="newspaper"></i></a></li>
        </ul>
        <div class="nav-tools">
          <a href="${ROOT}contact.html" class="btn btn-primary btn-sm ctxt">Contact</a>
        </div>
        <button class="hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false" aria-controls="mobileDrawer">
          <span></span><span></span><span></span>
        </button>
      </div>
    </header>`;

  const mobileDrawer = `
    <div class="scrim" id="mobileScrim" aria-hidden="true"></div>
    <aside class="mobile-drawer" id="mobileDrawer" role="dialog" aria-modal="true" aria-label="Menu">
      <div class="mobile-drawer-head">
        <a href="${ROOT}index.html" class="brand">
          <span class="brand-mark">S</span>
          <span><span class="brand-name">Srinivasulu IFS</span></span>
        </a>
        <button class="mobile-drawer-close" id="mobileClose" aria-label="Close menu"><i data-lucide="x"></i></button>
      </div>
      <nav class="mobile-nav">
        <span class="mobile-nav-section">Browse</span>
        <a href="${ROOT}index.html"${mActive('index.html')}><i data-lucide="home"></i>Home</a>
        <a href="${ROOT}cv.html"${mActive('cv.html')}><i data-lucide="user"></i>CV</a>
        <a href="${ROOT}honours.html"${mActive('honours.html')}><i data-lucide="award"></i>Honours and awards</a>
        <a href="${ROOT}accomplishments.html"${mActive('accomplishments.html')}><i data-lucide="briefcase"></i>Accomplishments</a>
        <a href="${ROOT}publications.html"${mActive('publications.html')}><i data-lucide="book-open"></i>Publications</a>
        <span class="mobile-nav-section">Media</span>
        <a href="${ROOT}photos.html"${mActive('photos.html')}><i data-lucide="image"></i>Photos</a>
        <a href="${ROOT}videos.html"${mActive('videos.html')}><i data-lucide="play-circle"></i>Videos</a>
        <a href="${ROOT}audios.html"${mActive('audios.html')}><i data-lucide="headphones"></i>Audios</a>
        <span class="mobile-nav-section">More</span>
        <a href="${ROOT}blog.html"${mActive('blog.html')}><i data-lucide="newspaper"></i>Blog</a>
        <a href="${ROOT}contact.html"${mActive('contact.html')}><i data-lucide="mail"></i>Contact</a>
      </nav>
    </aside>`;

  const footer = `
    <footer>
      <div class="container">
        <div class="footer-grid">
          <div class="footer-col">
            <div class="footer-brand">
              <span class="brand-mark">S</span>
              <span>
                <span class="brand-name">Srinivasulu IFS</span>
                <span class="brand-sub">Indian Forest Service · 1997 Batch</span>
              </span>
            </div>
            <p class="footer-tag">Principal Secretary, Ecology &amp; Environment, Government of Karnataka.</p>
            <div class="footer-social" aria-label="Social channels">
              <a href="https://www.youtube.com/@srinivasifs4051" aria-label="YouTube" target="_blank" rel="noopener"><svg class="sr-social-svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.58 6.186a2.51 2.51 0 0 0-1.768-1.768C18.254 4 12 4 12 4s-6.254 0-7.814.418A2.51 2.51 0 0 0 2.42 6.186C2 7.746 2 12 2 12s0 4.254.42 5.814a2.51 2.51 0 0 0 1.766 1.768C5.746 20 12 20 12 20s6.254 0 7.812-.418a2.51 2.51 0 0 0 1.768-1.768C22 16.254 22 12 22 12s0-4.254-.42-5.814zM10 15.5v-7l6 3.5-6 3.5z"/></svg></a>
              <a href="https://www.facebook.com/srinivasulu.krishnamurthy" aria-label="Facebook" target="_blank" rel="noopener"><svg class="sr-social-svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987H7.898V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.99 22 12z"/></svg></a>
              <a href="https://www.instagram.com/sriniforest/" aria-label="Instagram" target="_blank" rel="noopener"><svg class="sr-social-svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".9" fill="currentColor" stroke="none"/></svg></a>
              <a href="#" aria-label="LinkedIn"><svg class="sr-social-svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14zM8.34 17v-7H6.07v7h2.27zM7.2 8.5a1.25 1.25 0 1 0 0-2.5 1.25 1.25 0 0 0 0 2.5zM18 17v-4.04c0-2.04-1.1-3.06-2.6-3.06-1.18 0-1.78.63-2.13 1.07V10H11v7h2.27v-3.7c0-.62.14-1.24.94-1.24.79 0 .82.74.82 1.28V17H18z"/></svg></a>
            </div>
          </div>
          <div class="footer-col">
            <h4>Portfolio</h4>
            <ul>
              <li><a href="${ROOT}cv.html">CV</a></li>
              <li><a href="${ROOT}honours.html">Honours and awards</a></li>
              <li><a href="${ROOT}accomplishments.html">Accomplishments</a></li>
              <li><a href="${ROOT}publications.html">Publications</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h4>Media</h4>
            <ul>
              <li><a href="${ROOT}photos.html">Photos</a></li>
              <li><a href="${ROOT}videos.html">Videos</a></li>
              <li><a href="${ROOT}audios.html">Audios</a></li>
              <li><a href="${ROOT}blog.html">Blog</a></li>
              <li><a href="${ROOT}contact.html">Contact</a></li>
            </ul>
          </div>
          <div class="footer-col">
            <h4>Government</h4>
            <ul>
              <li><a href="${ROOT}contact.html#map">Sitemap</a></li>
              <li style="display:none;"><a href="#">Accessibility</a></li>
              <li style="display:none;"><a href="#">Privacy &amp; Disclaimer</a></li>
              <li style="display:none;"><a href="#">Help</a></li>
            </ul>
          </div>
        </div>
        <div class="footer-bottom">
          <span>© 2025 Srinivasulu IFS · Maintained by Vanalok</span>
          <span>Last updated: <time datetime="2025-11-15">15 November 2025</time></span>
        </div>
      </div>
    </footer>`;

  // Inject
  function inject(){
    const host = document.getElementById('sr-chrome-top') || document.body;
    if (document.getElementById('sr-chrome-top')){
      host.innerHTML = navbar + mobileDrawer;
    } else {
      // Fallback: prepend at body start
      const wrap = document.createElement('div');
      wrap.innerHTML = navbar + mobileDrawer;
      document.body.insertBefore(wrap, document.body.firstChild);
    }
    const fHost = document.getElementById('sr-chrome-footer');
    if (fHost) fHost.innerHTML = footer; else {
      const f = document.createElement('div'); f.innerHTML = footer; document.body.appendChild(f);
    }
    if (window.SrIcons) window.SrIcons();
    if (window.bindMobileDrawer) {
      window.bindMobileDrawer();
    }
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', inject);
  else inject();
})();
