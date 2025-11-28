<?php // inc/photoswipe-init.php ?>
<script type="module">
  import PhotoSwipeLightbox from 'https://unpkg.com/photoswipe@5/dist/photoswipe-lightbox.esm.js';

  // Only init if at least one PSWP trigger exists
  const cardSelector = '.product-card[data-pswp-gallery]';
  const hasTriggers = document.querySelector(`${cardSelector} a[data-pswp]`);

  if (hasTriggers) {
    const isMobile = () => window.matchMedia('(max-width: 767.98px)').matches;

    const lightbox = new PhotoSwipeLightbox({
      // one gallery per product card
      gallery: cardSelector,
      // anchors inside each card
      children: 'a[data-pswp]',
      // lazy-load core
      pswpModule: () => import('https://unpkg.com/photoswipe@5/dist/photoswipe.esm.js'),

      // Comfortable padding: minimal on phones, roomier on larger screens
      paddingFn: (vp) => {
        const pad = vp.x < 768 ? 8 : (vp.x < 1200 ? 32 : 64);
        return { top: pad, right: pad, bottom: pad, left: pad };
      },

      // Sensible defaults
      initialZoomLevel: 'fit',                // fit image into viewport
      maxZoomLevel: isMobile() ? 1.5 : 2.0,   // don't let users zoom to silly levels
      wheelToZoom: true,                      // allow mouse wheel zoom on desktop
      imageClickAction: 'zoom-or-close',
      tapAction: 'zoom-or-close',
      doubleTapAction: 'zoom'
    });

    // Fine-tune zoom levels per slide after PSWP is created
    lightbox.on('open', () => {
      const pswp = lightbox.pswp;

      // Keep secondary & max zoom reasonable and avoid hard upscaling small assets
      pswp.on('calcZoomLevels', (e) => {
        const maxByViewport = isMobile() ? 1.5 : 2.0;
        // cap the maximum zoom to ~2x the "fit" level (or 1.5x on mobile)
        e.zoomLevels.max = Math.min(e.zoomLevels.max, e.zoomLevels.fit * maxByViewport);
        // a friendly second step for double-tap
        e.zoomLevels.secondary = Math.min(e.zoomLevels.max, e.zoomLevels.fit * (isMobile() ? 1.15 : 1.25));
      });
    });

    lightbox.init();
  }
</script>


