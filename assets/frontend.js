document.addEventListener("DOMContentLoaded", function () {
    const select = document.getElementById("s3-folder-select");
    const gallery = document.getElementById("s3-gallery");
  
    if (!select || !gallery) return;
  
    select.addEventListener("change", () => {
      const folder = select.value;
      gallery.innerHTML = "Lade Bilder...";
  
      fetch("/wp-admin/admin-ajax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `action=get_s3_images&folder=${encodeURIComponent(folder)}`
      })
        .then(res => res.json())
        .then(images => {
          gallery.innerHTML = "";
          images.forEach(url => {
            const img = document.createElement("img");
            img.src = url;
            img.style.width = "200px";
            img.style.margin = "10px";
            img.loading = "lazy";
            gallery.appendChild(img);
          });
        });
    });
  });
  