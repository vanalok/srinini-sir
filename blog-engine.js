/**
 * Blog Engine - Handles fetching, filtering and pagination for blog posts.
 */
const API_BASE = 'https://www.srinivasifs.com/_functions';

const BlogEngine = (function () {
  let state = {
    mode: 'all',
    currentCategory: 'All',
    currentPage: 1,
    limit: 9,
    searchQuery: '',
    totalPages: 1,
    totalPosts: 0,
    posts: [],
    categories: [] // Available categories to show in filter bar
  };

  let elements = {
    grid: null,
    filterBar: null,
    pagination: null,
    counter: null,
    search: null
  };

  // Human-readable mapping for categories
  const categoryNames = {
    'kspcb': 'KSPCB',
    'ayush': 'Ayush',
    'karnataka-forest-department': 'Karnataka Forest Department',
    'kali-tiger-reserve': 'Kali Tiger Reserve',
    'nagarhole-national-park': 'Nagarhole National Park',
    'kalaburagi': 'Kalaburagi',
    'chitradurga': 'Chitradurga',
    'shimoga': 'Shivamogga',
    'kfcsc': 'KFCSC',
    'academics': 'Academics',
    'adcl': 'ADCL',
    'ecology-environment': 'Ecology & Environment',
    'All': 'All'
  };

  const DEPT_MAP = {
    'kspcb': ['5f685d9f798880001769b5d8', '5f55f4804fa8d200183c946f', '5fd31d3ffd9730001751de07'],
    'ayush': ['65b5289accb9cf28f47214d3', '65b526619dd3756237b9b7ee'],
    'karnataka-forest-department': ['5f97fa8fdff7a40017edb91d', '5f55f5abc8940c001755f2ee', '5f685e54e559920017e23d71'],
    'kali-tiger-reserve': ['5f685e7ce559920017e23da4', '5f55f55ba82e030017925d50'],
    'nagarhole-national-park': ['5f71c99d8ba8ed001735f4ae'],
    'kalaburagi': ['5f71b70fef645c0017546304'],
    'chitradurga': ['5f55f5d45ab2290017dc1d1b'],
    'shimoga': ['5f71c2b5a1adb500178ffd39'],
    'academics': ['5f7188fa84bb8700189bea03'],
    'adcl': ['5fb383bc5cc48c001707e767', '5f55f543ac304100173210eb'],
    'ecology-environment': ['6894a872361afb873d30f5d9']
  };

  const CATEGORY_TO_DEPT = {};
  for (const dept in DEPT_MAP) {
    DEPT_MAP[dept].forEach(id => {
      CATEGORY_TO_DEPT[id] = dept;
    });
  }

  function getWixImageUrl(wixUrl) {
    if (!wixUrl) return 'https://images.unsplash.com/photo-1448375240586-882707db888b?w=600&q=80';
    if (wixUrl.startsWith('wix:image://v1/')) {
      const parts = wixUrl.replace('wix:image://v1/', '').split('/');
      if (parts.length > 0) {
        return 'https://static.wixstatic.com/media/' + parts[0];
      }
    }
    if (wixUrl.startsWith('http://') || wixUrl.startsWith('https://')) {
      return wixUrl;
    }
    return 'https://static.wixstatic.com/media/' + wixUrl;
  }

  function getCategoryName(catIdOrSlug) {
    if (CATEGORY_TO_DEPT[catIdOrSlug]) {
      const deptSlug = CATEGORY_TO_DEPT[catIdOrSlug];
      return categoryNames[deptSlug] || deptSlug;
    }
    if (categoryNames[catIdOrSlug]) return categoryNames[catIdOrSlug];
    if (!catIdOrSlug) return '';
    return catIdOrSlug.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
  }

  async function init(options) {
    elements.grid = document.getElementById(options.gridId);
    elements.filterBar = document.getElementById(options.filterBarId);
    elements.pagination = document.getElementById(options.paginationId);
    elements.counter = document.getElementById(options.counterId);
    elements.search = document.getElementById(options.searchId);
    
    state.mode = options.mode || 'all';

    // Parse URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const categoryParam = urlParams.get('category');
    if (categoryParam) {
      state.currentCategory = categoryParam;
    }

    if (elements.search) {
      elements.search.addEventListener('input', debounce((e) => {
        state.searchQuery = e.target.value.trim().toLowerCase();
        state.currentPage = 1; // Reset to page 1 on search
        fetchAndRender();
      }, 300));
    }

    // Since the API might not have an endpoint to get all categories, 
    // we'll pre-populate the filter bar based on the mode or just fetch posts.
    if (state.mode === 'all') {
       // In 'all' mode (e.g., blog.html), we might want to fetch a few posts to extract categories, 
       // or just rely on the API to give us posts and we don't show a full category filter bar, 
       // or we use a predefined list. For now, let's use a predefined list for the main blog too.
       state.categories = Object.keys(categoryNames).filter(c => c !== 'All');
    } else {
       // In 'section' mode, we might just show the current category as the only filter or hide it.
       // The user clicked a specific department, so we focus on that.
       state.categories = [state.currentCategory];
    }

    renderFilterBar();
    await fetchAndRender();
  }

  async function fetchAndRender() {
    renderSkeletons();

    try {
      let postsToRender = [];
      let totalPosts = 0;
      let totalPages = 1;

      if (state.currentCategory === 'All') {
        let url = `${API_BASE}/posts?page=${state.currentPage}&limit=${state.limit}`;
        if (state.searchQuery) {
          url += `&search=${encodeURIComponent(state.searchQuery)}`;
        }
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch posts');
        const data = await response.json();
        postsToRender = data.posts || [];
        totalPosts = data.totalPosts || data.totalItems || postsToRender.length;
        totalPages = data.totalPages || 1;
      } else {
        // Fetch posts from all category IDs mapped to this department
        const categoryIds = DEPT_MAP[state.currentCategory] || [];
        if (categoryIds.length === 0) {
          postsToRender = [];
          totalPosts = 0;
          totalPages = 1;
        } else {
          const fetchPromises = categoryIds.map(id => {
            let url = `${API_BASE}/posts?category=${id}&limit=100`;
            if (state.searchQuery) {
              url += `&search=${encodeURIComponent(state.searchQuery)}`;
            }
            return fetch(url).then(res => res.ok ? res.json() : { posts: [] });
          });
          const results = await Promise.all(fetchPromises);
          
          let combinedPosts = [];
          results.forEach(res => {
            if (res.posts) {
              combinedPosts = combinedPosts.concat(res.posts);
            }
          });

          // Deduplicate by ID
          const seen = new Set();
          combinedPosts = combinedPosts.filter(post => {
            const duplicate = seen.has(post.id);
            seen.add(post.id);
            return !duplicate;
          });

          // Sort by publishedDate descending
          combinedPosts.sort((a, b) => new Date(b.publishedDate || 0) - new Date(a.publishedDate || 0));

          totalPosts = combinedPosts.length;
          totalPages = Math.max(1, Math.ceil(totalPosts / state.limit));

          // Slice for current page pagination
          const startIndex = (state.currentPage - 1) * state.limit;
          const endIndex = startIndex + state.limit;
          postsToRender = combinedPosts.slice(startIndex, endIndex);
        }
      }

      state.posts = postsToRender;
      state.totalPosts = totalPosts;
      state.totalPages = totalPages;

      renderGrid();
      renderPagination();
      updateCounter();

    } catch (error) {
      console.error('Error fetching posts:', error);
      elements.grid.innerHTML = `
        <div class="blog-empty">
          <h3>Oops! Something went wrong.</h3>
          <p>We couldn't load the posts at this time. Please try again later.</p>
        </div>
      `;
      if (elements.pagination) elements.pagination.innerHTML = '';
      if (elements.counter) elements.counter.textContent = '';
    }
  }

  function renderFilterBar() {
    if (!elements.filterBar) return;
    elements.filterBar.innerHTML = '';

    // "All" button for blog.html
    if (state.mode === 'all') {
      const allBtn = document.createElement('button');
      allBtn.className = `filter-btn ${state.currentCategory === 'All' ? 'active' : ''}`;
      allBtn.textContent = 'All Updates';
      allBtn.onclick = () => setCategory('All');
      elements.filterBar.appendChild(allBtn);
    }

    state.categories.forEach(cat => {
      const btn = document.createElement('button');
      btn.className = `filter-btn ${state.currentCategory === cat ? 'active' : ''}`;
      btn.textContent = getCategoryName(cat);
      btn.onclick = () => setCategory(cat);
      elements.filterBar.appendChild(btn);
    });
  }

  function setCategory(category) {
    if (state.currentCategory === category) return;
    state.currentCategory = category;
    state.currentPage = 1;
    
    // Update URL without reloading to allow sharing links
    const url = new URL(window.location);
    if (category === 'All') {
      url.searchParams.delete('category');
    } else {
      url.searchParams.set('category', category);
    }
    window.history.pushState({}, '', url);

    renderFilterBar();
    fetchAndRender();
  }

  function renderSkeletons() {
    if (!elements.grid) return;
    elements.grid.innerHTML = '';
    for (let i = 0; i < state.limit; i++) {
      elements.grid.innerHTML += `
        <div class="b-skeleton">
          <div class="skeleton-img"></div>
          <div class="skeleton-body">
            <div class="skeleton-line short"></div>
            <div class="skeleton-line medium" style="height: 20px; margin-top: 10px; margin-bottom: 15px;"></div>
            <div class="skeleton-line long"></div>
            <div class="skeleton-line medium"></div>
            <div class="skeleton-line long" style="margin-top: 20px;"></div>
          </div>
        </div>
      `;
    }
  }

  function renderGrid() {
    if (!elements.grid) return;
    elements.grid.innerHTML = '';

    if (state.posts.length === 0) {
      elements.grid.innerHTML = `
        <div class="blog-empty">
          <h3>No posts found</h3>
          <p>Try adjusting your search or category filter.</p>
        </div>
      `;
      return;
    }

    state.posts.forEach(post => {
      // Determine image to show
      const imgUrl = getWixImageUrl(post.coverImage);
      const dateStr = formatDate(post.publishedDate);
      const categoryTag = post.categories && post.categories.length > 0 ? getCategoryName(post.categories[0]) : 'Update';
      const excerpt = post.excerpt || '';
      
      const card = document.createElement('a');
      card.href = `post.html?slug=${post.slug}`;
      card.className = 'b-card';
      
      card.innerHTML = `
        <div class="b-card-img">
          <img src="${imgUrl}" alt="${post.title}" loading="lazy" onerror="this.src='https://images.unsplash.com/photo-1448375240586-882707db888b?w=600&q=80'">
        </div>
        <div class="b-card-body">
          <span class="b-tag">${categoryTag}</span>
          <h3 class="b-card-title">${post.title}</h3>
          <p class="b-excerpt">${excerpt}</p>
          <div class="b-card-foot">
            <div class="b-date">
              <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              ${dateStr}
            </div>
            <span class="b-read-more">Read <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
          </div>
        </div>
      `;
      elements.grid.appendChild(card);
    });
  }

  function renderPagination() {
    if (!elements.pagination) return;
    elements.pagination.innerHTML = '';

    if (state.totalPages <= 1) return;

    // Previous Button
    const prevBtn = document.createElement('button');
    prevBtn.className = 'page-btn';
    prevBtn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>';
    prevBtn.disabled = state.currentPage === 1;
    prevBtn.onclick = () => {
      if (state.currentPage > 1) {
        state.currentPage--;
        fetchAndRender();
        scrollToTop();
      }
    };
    elements.pagination.appendChild(prevBtn);

    // Page Numbers
    for (let i = 1; i <= state.totalPages; i++) {
      // Simple logic: show first, last, and pages around current
      if (
        i === 1 || 
        i === state.totalPages || 
        (i >= state.currentPage - 1 && i <= state.currentPage + 1)
      ) {
        const pageBtn = document.createElement('button');
        pageBtn.className = `page-btn ${i === state.currentPage ? 'active' : ''}`;
        pageBtn.textContent = i;
        pageBtn.onclick = () => {
          if (state.currentPage !== i) {
            state.currentPage = i;
            fetchAndRender();
            scrollToTop();
          }
        };
        elements.pagination.appendChild(pageBtn);
      } else if (
        i === state.currentPage - 2 || 
        i === state.currentPage + 2
      ) {
        const ellipsis = document.createElement('span');
        ellipsis.className = 'page-ellipsis';
        ellipsis.textContent = '...';
        elements.pagination.appendChild(ellipsis);
      }
    }

    // Next Button
    const nextBtn = document.createElement('button');
    nextBtn.className = 'page-btn';
    nextBtn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>';
    nextBtn.disabled = state.currentPage === state.totalPages;
    nextBtn.onclick = () => {
      if (state.currentPage < state.totalPages) {
        state.currentPage++;
        fetchAndRender();
        scrollToTop();
      }
    };
    elements.pagination.appendChild(nextBtn);
  }

  function updateCounter() {
    if (!elements.counter) return;
    elements.counter.textContent = `Showing ${state.posts.length} of ${state.totalPosts} posts`;
  }

  function scrollToTop() {
    if (elements.grid) {
      const yOffset = -100; 
      const y = elements.grid.getBoundingClientRect().top + window.pageYOffset + yOffset;
      window.scrollTo({top: y, behavior: 'smooth'});
    }
  }

  function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  }

  function debounce(func, timeout = 300) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => { func.apply(this, args); }, timeout);
    };
  }

  async function loadPost(slug, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;

    renderPostSkeleton(container);

    try {
      const response = await fetch(`${API_BASE}/post?slug=${slug}`);
      if (!response.ok) throw new Error('Post not found');
      const data = await response.json();
      const post = data.post || []      
      renderPostDetail(container, post);
      loadRelatedPosts();

    } catch (error) {
      console.error('Error loading post:', error);
      container.innerHTML = `
        <div class="post-not-found">
          <h2>Post Not Found</h2>
          <p>The post you are looking for does not exist or has been moved.</p>
          <a href="blog.html" class="post-back-btn">← Back to Blog</a>
        </div>
      `;
    }
  }

  async function loadRelatedPosts() {
    const relatedGrid = document.getElementById('relatedGrid');
    if (!relatedGrid) return;

    try {
      const response = await fetch(`${API_BASE}/randomPosts`);
      if (!response.ok) throw new Error('Failed to fetch related posts');
      const data = await response.json();
      const posts = data.posts || [];
      
      relatedGrid.innerHTML = '';
      posts.forEach(post => {
        const imgUrl = getWixImageUrl(post.coverImage);
        const card = document.createElement('a');
        card.href = `post.html?slug=${post.slug}`;
        card.className = 'b-card';
        card.innerHTML = `
          <div class="b-card-img"><img src="${imgUrl}" alt="${post.title}"></div>
          <div class="b-card-body">
            <h3 class="b-card-title" style="font-size: 0.95rem;">${post.title}</h3>
            <div class="b-card-foot">
              <span class="b-read-more">Read More</span>
            </div>
          </div>
        `;
        relatedGrid.appendChild(card);
      });
    } catch (error) {
      console.error('Related posts error:', error);
    }
  }

  function renderPostSkeleton(container) {
    container.innerHTML = `
      <div class="post-skeleton">
        <div class="sk-title"></div>
        <div class="sk-meta"></div>
        <div class="sk-img"></div>
        <div class="sk-body"></div>
        <div class="sk-body w80"></div>
        <div class="sk-body w70"></div>
      </div>
    `;
  }

  function renderPostDetail(container, post) {
    // Determine the cover media
    let coverMediaHtml = '';
    const media = post.media;
    if (media && media.displayed) {
      if (media.wixMedia && media.wixMedia.image) {
        coverMediaHtml = `<img src="${media.wixMedia.image.url}" class="post-hero-img" alt="${post.title}" onerror="this.style.display='none'">`;
      } else if (media.embedMedia && media.embedMedia.thumbnail) {
        coverMediaHtml = `<img src="${media.embedMedia.thumbnail.url}" class="post-hero-img" alt="${post.title}" onerror="this.style.display='none'">`;
      }
    }

    const firstPublished = formatDate(post.firstPublishedDate || post.date);
    const lastPublished = post.lastPublishedDate ? formatDate(post.lastPublishedDate) : null;
    
    const categoriesHtml = (post.categories || [])
      .map(cat => `<span class="post-cat-tag">${cat.label || getCategoryName(cat)}</span>`)
      .join('');

    const metricsHtml = post.metrics ? `
      <div class="post-metrics">
        <span><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> ${post.minutesToRead || 1} min read</span>
        <span><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg> ${post.metrics.views || 0} views</span>
      </div>
    ` : '';

    container.innerHTML = `
      <a href="javascript:history.back()" class="post-back-btn">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Back
      </a>
      <div class="post-cats">${categoriesHtml}</div>
      <h1 class="post-title">${post.title}</h1>
      <div class="post-meta">
        <div class="post-meta-main">
          ${post.owner ? `<img src="${post.owner.image ? post.owner.image.url : ''}" class="author-img" onerror="this.style.display='none'">` : ''}
          <div class="post-meta-text">
            <span class="author-name">${post.owner ? post.owner.name : 'Srinivasulu IFS'}</span>
            <div class="post-dates">
              <span class="post-date">${firstPublished}</span>
              ${lastPublished && lastPublished !== firstPublished ? `<span class="post-meta-dot"></span> <span class="post-updated">Updated: ${lastPublished}</span>` : ''}
            </div>
          </div>
        </div>
        ${metricsHtml}
      </div>
      
      ${coverMediaHtml}

      <div class="post-body">
        ${post.richContent ? renderRichContent(post.richContent.nodes) : (post.content || post.excerpt || 'No content available.')}
      </div>

      <div class="post-share-bar">
        <a href="https://twitter.com/intent/tweet?text=${encodeURIComponent(post.title)}&url=${encodeURIComponent(window.location.href)}" target="_blank" class="share-icon" title="Share on X">𝕏</a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}" target="_blank" class="share-icon" title="Share on Facebook">f</a>
        <a href="https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(window.location.href)}" target="_blank" class="share-icon" title="Share on LinkedIn">in</a>
        <a href="javascript:void(0)" onclick="navigator.clipboard.writeText(window.location.href).then(()=>alert('Link copied!'))" class="share-icon" title="Copy Link">🔗</a>
      </div>

      ${post.externalUrl ? `
        <a href="${post.externalUrl}" target="_blank" class="post-external-btn">
          View Original Publication
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
      ` : ''}
    `;
  }

  function renderRichContent(nodes) {
    if (!nodes || !Array.isArray(nodes)) return '';
    return nodes.map(node => renderNode(node)).join('');
  }

  function renderNode(node) {
    switch (node.type) {
      case 'PARAGRAPH':
        return `<p class="rich-p ${node.paragraphData?.textStyle?.textAlignment?.toLowerCase() || ''}">${renderInlineNodes(node.nodes)}</p>`;
      
      case 'HEADING':
        const level = node.headingData?.level || 2;
        return `<h${level} class="rich-h${level}">${renderInlineNodes(node.nodes)}</h${level}>`;
      
      case 'IMAGE':
        const img = node.imageData?.image?.src;
        if (!img) return '';
        return `
          <figure class="rich-img">
            <img src="${img.url || `https://static.wixstatic.com/media/${img.id}`}" alt="${node.imageData?.caption || ''}">
            ${node.imageData?.caption ? `<figcaption>${node.imageData.caption}</figcaption>` : ''}
          </figure>
        `;
      
      case 'VIDEO':
        const video = node.videoData?.video?.src;
        if (!video) return '';
        if (video.url.includes('youtube.com') || video.url.includes('youtu.be')) {
          const vidId = video.url.split('v=')[1] || video.url.split('/').pop();
          return `<div class="rich-video"><iframe src="https://www.youtube.com/embed/${vidId}" frameborder="0" allowfullscreen></iframe></div>`;
        }
        return `<div class="rich-video"><video src="${video.url}" controls></video></div>`;

      case 'BULLETED_LIST':
        return `<ul class="rich-ul">${node.bulletedListData?.items?.map(item => `<li>${renderRichContent(item.nodes)}</li>`).join('') || ''}</ul>`;
      
      case 'ORDERED_LIST':
        return `<ol class="rich-ol">${node.orderedListData?.items?.map(item => `<li>${renderRichContent(item.nodes)}</li>`).join('') || ''}</ol>`;

      case 'FILE':
        const file = node.fileData;
        if (!file) return '';
        const sizeKb = Math.round(file.size / 1024);
        return `
          <div class="rich-file-card">
            <div class="file-icon">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="file-info">
              <span class="file-name">${file.name}</span>
              <span class="file-meta">Download ${file.type.toUpperCase()} • ${sizeKb}KB</span>
            </div>
            <a href="https://static.wixstatic.com/media/${file.src.id}" download="${file.name}" class="file-download" target="_blank">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </a>
          </div>
        `;

      default:
        return '';
    }
  }

  function renderInlineNodes(nodes) {
    if (!nodes || !Array.isArray(nodes)) return '';
    return nodes.map(node => {
      if (node.type === 'TEXT') {
        let text = node.textData.text.replace(/\n/g, '<br>');
        if (node.textData.decorations) {
          node.textData.decorations.forEach(deco => {
            if (deco.type === 'BOLD') text = `<strong>${text}</strong>`;
            if (deco.type === 'ITALIC') text = `<em>${text}</em>`;
            if (deco.type === 'LINK') text = `<a href="${deco.linkData.link.url}" target="${deco.linkData.link.target || '_self'}">${text}</a>`;
            if (deco.type === 'COLOR') text = `<span style="color: ${deco.colorData.foreground}">${text}</span>`;
          });
        }
        return text;
      }
      return '';
    }).join('');
  }

  return {
    init,
    loadPost
  };
})();
