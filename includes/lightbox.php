<!-- Lightbox — shared include for image full-screen view -->
<div id="lightbox"
     onclick="this.style.display='none'"
     class="lightbox-overlay"
     role="dialog"
     aria-modal="true"
     aria-label="Image viewer">
  <img id="lightboxImg" class="lightbox-img" alt="Full-size view">
  <button onclick="event.stopPropagation(); document.getElementById('lightbox').style.display='none';"
          class="lightbox-close"
          aria-label="Close image viewer">
    &#215;
  </button>
</div>
