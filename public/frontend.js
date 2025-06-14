document.addEventListener('DOMContentLoaded', () => {
  // Lightbox-Element vorbereiten
  const thumbs = document.querySelectorAll('.s3-gallery-thumb');
  const overlay = document.createElement('div');
  overlay.className = 's3-lightbox-overlay';
  overlay.innerHTML = `
    <span class="s3-lightbox-close">&times;</span>
    <span class="s3-lightbox-prev">&#10094;</span>
    <img>
    <span class="s3-lightbox-next">&#10095;</span>
  `;
  document.body.appendChild(overlay);

  const imgEl = overlay.querySelector('img');
  let current = 0;

  const show = (index) => {
    const el = thumbs[index];
    if (!el) return;
    imgEl.src = el.dataset.src;
    current = index;
    overlay.classList.add('active');
  };

  thumbs.forEach((el, i) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      show(i);
    });
  });

  overlay.querySelector('.s3-lightbox-close').onclick = () =>
      overlay.classList.remove('active');
  overlay.querySelector('.s3-lightbox-prev').onclick = () =>
      show((current - 1 + thumbs.length) % thumbs.length);
  overlay.querySelector('.s3-lightbox-next').onclick = () =>
      show((current + 1) % thumbs.length);

  // Bilder sichtbar machen, sobald sie geladen sind
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
