const photos = [
{ category: 'chitradurga', src: 'images/Chitradurga11.webp', alt: 'images' },
{ category: 'kali', src: 'images/kale1.webp', alt: 'images' },
{ category: 'academics', src: 'images/academics/image_10.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_11.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_12.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_13.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_14.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_15.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_16.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_17.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_20.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_21.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_22.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_23.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_3.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_4.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_5.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_6.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_8.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/image_9.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/img.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/img2.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/img3.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/IMG_20200507_120703 (1).webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/WhatsApp Image 2021-05-20 at 23.11.39.jp.webp', alt: 'academics' },
{ category: 'academics', src: 'images/academics/WhatsApp Image 2021-05-20 at 23.12.11.jp.webp', alt: 'academics' },
{ category: 'adcl', src: 'images/adcl/aca.webp', alt: 'adcl' },
{ category: 'adcl', src: 'images/adcl/adcl1.webp', alt: 'adcl' },
{ category: 'adcl', src: 'images/adcl/adcl10.webp', alt: 'adcl' },
{ category: 'adcl', src: 'images/adcl/adcl11.webp', alt: 'adcl' },
{ category: 'ayush-photos', src: 'images/ayush-photos/ayush-2026-07-02.jpeg', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/0165160e-83e1-4859-9654-6eb243b989a1.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/12e81100-0fcd-4f5b-adba-c5fcfb1e537c.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/62690832-d4bc-4c1f-81f6-39d533a9b1d8.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/ANP_6190.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/ANP_6194.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/ANP_6221.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/ANP_6230.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/ANP_6401.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/ANP_6418.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/b6593dd4-feb5-4974-b1d9-46e2a3cdf6cc.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/PHOTO-2024-03-27-07-02-15 (1).webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/Screenshot 2024-01-11 124146.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/WhatsApp Image 2024-01-21 at 10.44.59.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/WhatsApp Image 2024-01-21 at 10.45.41.webp', alt: 'ayush-photos' },
{ category: 'ayush-photos', src: 'images/ayush-photos/ayush4u.jpeg', alt: 'ayush-photos' },
{ category: 'family', src: 'images/family/image_10.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_11.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_12.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_13.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_14.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_15.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_16.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_17.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_18.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_19.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_20.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_21.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_22.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_23.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_24.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_25.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_26.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_27.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_28.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_29.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_30.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_31.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_32.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_33.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_34.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_35.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_36.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_37.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_38.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_39.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_4.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_40.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_41.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_42.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_43.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_44.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_45.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_46.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_47.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_48.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_49.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_5.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_50.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_51.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_52.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_53.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_54.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_55.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_56.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_57.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_58.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_59.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_6.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_60.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_61.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_62.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_63.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_64.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_65.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_66.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_67.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_68.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_69.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_7.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_70.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_8.webp', alt: 'family' },
{ category: 'family', src: 'images/family/image_9.webp', alt: 'family' },
{ category: 'forestdeptphotos', src: 'images/forestDeptPhotos/ktr.webp', alt: 'forestdeptphotos' },
{ category: 'forestdeptphotos', src: 'images/forestDeptPhotos/ktr2.webp', alt: 'forestdeptphotos' },
{ category: 'forestdeptphotos', src: 'images/forestDeptPhotos/ktr3.webp', alt: 'forestdeptphotos' },
{ category: 'honours', src: 'images/honours/A C 1.webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/A C 2.webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/A C 3.webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/A C 4.webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/AMZ_0019.webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/AMZ_0175.webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/Asia Book of Records.webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/IMG_0850.HEIC.webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/IMG_2176.HEIC.webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/IMG_5774.webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/IMG_5777.webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/Launched rapid response vehicles to strengthen regulatory responsibility of board..webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/mobileapp.webp', alt: 'honours' },
{ category: 'honours', src: 'images/honours/WhatsApp Image 2021-03-12 at 11.38.05.jp.webp', alt: 'honours' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/2-min.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/3-min.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/4-min.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/5 (1)-min.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/5-min.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/A C 1.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/A C 2.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/A C 3.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/A C 4.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/AMZ_0019.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/AMZ_0175.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/Asia Book of Records.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/BV9A8047.jfif.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/BV9A8110.jfif.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/BV9A8116.jfif.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/IMG-0144.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/IMG-7578.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/IMG-7603.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/IMG_0850.HEIC.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/IMG_2106-min.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/IMG_2133-min.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/IMG_2170-min.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/IMG_2176.HEIC.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/IMG_2364-min.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/IMG_2813-min.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/IMG_5774.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/IMG_5777.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/Launched rapid response vehicles to strengthen regulatory responsibility of board..webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/mobileapp.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/photos 1704-min.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/WhatsApp Image 2020-11-05 at 09.14.57.jp.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/WhatsApp Image 2021-03-12 at 11.36.32.jp.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/WhatsApp Image 2021-03-12 at 11.38.05.jp.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/WhatsApp Image 2021-04-06 at 10.42.23 (1.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/WhatsApp Image 2021-04-06 at 10.42.23 (2.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/WhatsApp Image 2021-04-06 at 10.42.23 (3.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/WhatsApp Image 2021-04-06 at 10.42.23.jp.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/WhatsApp Image 2021-04-06 at 10.50.42.jp (1).webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/WhatsApp Image 2021-04-06 at 10.50.42.jp.webp', alt: 'kspcb-photos' },
{ category: 'kspcb-photos', src: 'images/kspcb-Photos/WhatsApp Image 2021-07-01 at 13.53.31.webp', alt: 'kspcb-photos' },
{ category: 'shimoga', src: 'images/shimoga/shimoga.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga10.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga12.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga13.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga14.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga15.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga16.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga18.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga2.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga3.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga4.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga5.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga6.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga7.webp', alt: 'shimoga' },
{ category: 'shimoga', src: 'images/shimoga/shimoga9.webp', alt: 'shimoga' },
];
const photoGrid = document.getElementById('photoGrid');
const tabs = document.querySelectorAll('.filter-tab');
let currentCategory = 'all';
let visibleCount = 12;
const loadStep = 12;

/* Render Photos */
function renderPhotos(reset = false) {

  if (reset) {
    photoGrid.innerHTML = '';
  }
  const filtered =
    currentCategory === 'all'
      ? photos
      : photos.filter(
          photo => photo.category === currentCategory
        );
  const photosToShow =
    filtered.slice(0, visibleCount);
  photoGrid.innerHTML = '';
  photosToShow.forEach(photo => {
    const img = document.createElement('img');
    img.src = photo.src;
    img.alt = photo.alt;
    img.loading = 'lazy';
    photoGrid.appendChild(img);
  });

  updateLoadMore(filtered.length);

}

/* Load More Button */
function updateLoadMore(total) {

  let btn =
    document.getElementById('loadMoreBtn');
  if (visibleCount >= total) {
    if (btn) btn.style.display = 'none';
    return;
  }

  if (!btn) {

    btn = document.createElement('button');
    btn.id = 'loadMoreBtn';
    btn.className = 'load-more-btn';
    btn.textContent = 'Load More Photos';
    photoGrid.after(btn);
    btn.addEventListener('click', () => {
      visibleCount += loadStep;
      renderPhotos();
    });
  }
  btn.style.display = 'inline-flex';
}

/* Category Tabs */
tabs.forEach(tab => {
  tab.addEventListener('click', e => {
    e.preventDefault();
    tabs.forEach(t =>
      t.classList.remove('active')
    );
    tab.classList.add('active');
    currentCategory =
      tab.id.replace('tab-', '');
    visibleCount = loadStep;
    renderPhotos(true);
  })
});

/* Initial Load */
renderPhotos(true);