// frontend.js – Lightbox lädt Bilder aus Cache Storage (Blob) statt direkt über <img src>

document.addEventListener('DOMContentLoaded', () => {
  const thumbs = document.querySelectorAll('.s3-gallery-thumb');

  // Lightbox-Overlay erstellen
  const overlay = document.createElement('div');
  overlay.className = 's3-lightbox-overlay';
  overlay.innerHTML = `
    <span class="s3-lightbox-close">&times;</span>
    <span class="s3-lightbox-prev">&#10094;</span>
    <img id="lightbox-img" style="max-width:90%; max-height:90%; opacity:0; transition: opacity 0.3s ease;">
    <span class="s3-lightbox-next">&#10095;</span>
  `;
  document.body.appendChild(overlay);

  const lightboxImg = overlay.querySelector('#lightbox-img');
  let currentIndex = 0;

  // Optional preload in Cache Storage (nicht zwingend nötig, wenn beim Anzeigen geladen wird)
  thumbs.forEach(el => {
    const url = el.dataset.src;
    caches.open('s3-gallery-cache-v1').then(cache => {
      cache.match(url).then(match => {
        if (!match) {
          fetch(url).then(res => {
            if (res.ok) cache.put(url, res.clone());
          }).catch(console.error);
        }
      });
    });
  });

  // Bild aus Cache (oder Netzwerk) laden und anzeigen
  async function loadImageFromCacheOrNetwork(url) {
    const cache = await caches.open('s3-gallery-cache-v1');
    let response = await cache.match(url);
    if (!response) {
      response = await fetch(url);
      if (response.ok) cache.put(url, response.clone());
    }
    const blob = await response.blob();
    return URL.createObjectURL(blob);
  }

  const show = (index) => {
    if (index < 0 || index >= thumbs.length) return;
    currentIndex = index;
    const src = thumbs[currentIndex].dataset.src;

    lightboxImg.style.opacity = 0;

    loadImageFromCacheOrNetwork(src).then(blobUrl => {
      lightboxImg.src = blobUrl;
      lightboxImg.onload = () => {
        lightboxImg.style.opacity = 1;
      };
    });
  };

  // Klicks auf Thumbnails
  thumbs.forEach((el, i) => {
    el.addEventListener('click', e => {
      e.preventDefault();
      overlay.classList.add('active');
      show(i);
    });
  });

  overlay.querySelector('.s3-lightbox-close').addEventListener('click', () => {
    overlay.classList.remove('active');
    lightboxImg.src = '';
  });

  overlay.querySelector('.s3-lightbox-prev').addEventListener('click', () => show(currentIndex - 1));
  overlay.querySelector('.s3-lightbox-next').addEventListener('click', () => show(currentIndex + 1));

  // Spinner entfernen, sobald Vorschaubilder geladen sind
  document.querySelectorAll('img.s3-img-loading').forEach((img) => {
    const wrapper = img.closest('.s3-image-wrapper');
    const placeholder = wrapper?.querySelector('.s3-img-placeholder');

    const showLoaded = () => {
      img.classList.add('s3-img-loaded');
      if (placeholder) placeholder.remove();
    };

    if (img.complete) {
      showLoaded();
    } else {
      img.addEventListener('load', showLoaded);
    }
  });
});