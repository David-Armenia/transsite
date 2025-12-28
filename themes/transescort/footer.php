<footer class="site-footer">
  <div class="container">
    <p>© <?php echo date('Y'); ?> TransEscort</p>
  </div>
</footer>

<!-- VIDEO MODAL -->
<div class="video-modal" id="videoModal" aria-hidden="true">
  <div class="video-modal__backdrop" data-close="1"></div>

  <div class="video-modal__dialog" role="dialog" aria-modal="true" aria-label="Video player">
    <button class="video-modal__close" type="button" data-close="1" aria-label="Close">✕</button>

    <div class="video-modal__player">
      <video id="videoModalPlayer" playsinline controls preload="metadata"></video>
    </div>
  </div>
</div>

<!-- GALLERY LIGHTBOX -->
<div class="gallery-lightbox" id="galleryLightbox" aria-hidden="true">
  <div class="gallery-lightbox__backdrop"></div>
  <button class="gallery-lightbox__close" type="button" aria-label="Close gallery">✕</button>
  <img id="galleryLightboxImg" alt="">
</div>

<?php wp_footer(); ?>
</body>
</html>

