<div class="page-content">
  <main class="site-container">
    <section class="hero-section split-hero">
      <?php
        // Honor the global switch from index.php; default false if missing.
        $videosEnabled = isset($VIDEOS_ENABLED) ? (bool)$VIDEOS_ENABLED : false;
      ?>
      <div class="hero-main">
        <div class="swiper-container">
          <div class="swiper-wrapper">

            <!-- 1) Static homepage slide: exact SVG call-out -->
            <?php if ($page === 'homepage'): ?>
              <div class="swiper-slide">
                <div class="hero-img-wrapper">
                  <img
                    src="images/AdobeStock_414079274-soccer_ball_and_field.jpeg"
                    alt="Soccer ball on a stadium pitch"
                    class="hero-img hero-bg"
                  />
                  <div class="hero-overlay" aria-labelledby="hero-home-0-caption">
                    <h2 id="hero-home-0-caption" class="visually-hidden">
                      View our brand new range of sports balls. Shop now.
                    </h2>
                    <div class="hero-callout">
                      <svg
                        class="hero-callout__svg"
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 200 240"
                        overflow="visible"
                        preserveAspectRatio="xMaxYMid meet"
                        role="img"
                        aria-label="View our brand new range of sports balls. Shop now."
                      >
                        <g id="callout-content">
                          <text x="205" y="15" text-anchor="end" fill="var(--color-secondary)" font-family="Myriad Pro, sans-serif" font-size="32" letter-spacing="-0.02em" dominant-baseline="hanging" stroke="rgba(0,0,0,0.4)" stroke-width="1px">
                            View our brand
                          </text>
                          <text x="205" y="60" text-anchor="end" fill="var(--color-secondary)" font-family="Myriad Pro, sans-serif" font-size="32" dominant-baseline="hanging" stroke="rgba(0,0,0,0.4)" stroke-width="1px">
                            new range of
                          </text>
                          <text x="205" y="130" text-anchor="end" fill="var(--color-secondary)" font-family="Myriad Pro, sans-serif" font-size="48" letter-spacing="0.02em" dominant-baseline="middle" stroke="rgba(0,0,0,0.4)" stroke-width="1px">
                            Sports balls
                          </text>
                          <a href="/shop" aria-label="Shop now" style="cursor:pointer; text-decoration:none;">
                            <rect x="65" y="190" width="140" height="60" rx="30" ry="30" fill="var(--color-dark-blue)"/>
                            <text x="135" y="222" fill="#ffffff" font-family="Myriad Pro, sans-serif" font-size="24" text-anchor="middle" dominant-baseline="middle">
                              Shop Now
                            </text>
                          </a>
                        </g>
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
            <?php endif; ?>

            <!-- 2) Dynamic slides for this page -->
            <?php
              if ($page === 'stax') {
                // Pull all STAX slides
                $slides = $db->query(
                  "SELECT * FROM slides WHERE page_key = 'stax' ORDER BY sort_order"
                )->fetchAll(PDO::FETCH_ASSOC);

                // Pull all STAX videos with full metadata
                $videosRaw = $db->query(
                  "SELECT video_key, src_path, aria_label FROM videos WHERE page_key = 'stax'"
                )->fetchAll(PDO::FETCH_ASSOC);

                $config[$page] = [
                  'slides' => array_map(function ($slide) {
                    return [
                      'banner'   => $slide['banner_path'],
                      'alt'      => $slide['alt_text'],
                      'ariaText' => $slide['aria_text'],
                      'videoKey' => $slide['video_key'] ?: null,
                    ];
                  }, $slides),
                  // keep DB column names; we’ll use fallbacks when reading
                  'videos' => array_column($videosRaw, null, 'video_key'),
                ];
              }
            ?>

            <?php foreach ($config[$page]['slides'] ?? [] as $i => $slide): ?>
              <div class="swiper-slide">
                <div class="hero-img-wrapper">
                  <?php
                    $vk = $slide['videoKey'] ?? null;
                    $hasVideo = $videosEnabled && $vk && isset($config[$page]['videos'][$vk]);
                  ?>
                  <?php if ($hasVideo): ?>
                    <?php $v = $config[$page]['videos'][$vk]; ?>
                    <video
                      class="hero-img hero-bg"
                      src="<?= htmlspecialchars($v['src'] ?? $v['src_path'] ?? '') ?>"
                      autoplay muted loop playsinline preload="auto"
                      aria-label="<?= htmlspecialchars($v['ariaLabel'] ?? $v['aria_label'] ?? '') ?>"
                    ></video>
                  <?php else: ?>
                    <img
                      class="hero-img hero-bg"
                      src="<?= htmlspecialchars($slide['banner']) ?>"
                      alt="<?= htmlspecialchars($slide['alt']) ?>"
                    />
                  <?php endif; ?>

                  <div
                    class="hero-overlay"
                    aria-labelledby="hero-<?= htmlspecialchars($page) ?>-<?= $i ?>-caption"
                  >
                    <h2
                      id="hero-<?= htmlspecialchars($page) ?>-<?= $i ?>-caption"
                      class="visually-hidden"
                    >
                      <?= htmlspecialchars($slide['ariaText']) ?>
                    </h2>
                    <div class="hero-callout">
                      <!-- optional per-slide SVG callout -->
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>

          </div><!-- /.swiper-wrapper -->
          <div class="swiper-pagination"></div>
          <div class="swiper-button-prev" aria-label="Previous slide"></div>
          <div class="swiper-button-next" aria-label="Next slide"></div>
        </div><!-- /.swiper-container -->
      </div><!-- /.hero-main -->

      <?php if ($videosEnabled && !empty($config[$page]['videos'])): ?>
        <aside class="hero-sidebar">
          <?php foreach ($config[$page]['videos'] ?? [] as $video): ?>
            <video
              src="<?= htmlspecialchars($video['src'] ?? $video['src_path'] ?? '') ?>"
              autoplay muted loop playsinline preload="auto"
              class="sidebar-video"
              aria-label="<?= htmlspecialchars($video['ariaLabel'] ?? $video['aria_label'] ?? '') ?>"
            ></video>
            <div class="sidebar-caption">
              <p><strong><?= htmlspecialchars($video['ariaLabel'] ?? $video['aria_label'] ?? '') ?></strong></p>
            </div>
          <?php endforeach; ?>
        </aside>
      <?php endif; ?>
    </section>
  </main>
</div>

<script src="https://unpkg.com/swiper@10/swiper-bundle.min.js"></script>
<script>
  // grab the Swiper container inside your split-hero
  const container = document.querySelector('.split-hero .swiper-container');
  if (container) {
    // count only the real slides (not the clones)
    const realSlides = container.querySelectorAll('.swiper-slide:not(.swiper-slide-duplicate)').length;
    const shouldLoop  = realSlides > 1;

    new Swiper(container, {
      effect: 'fade',
      fadeEffect: { crossFade: true },

      // only loop/autoplay when you have multiple slides
      loop: shouldLoop,
      autoplay: shouldLoop
        ? {
            delay: 5000,
            disableOnInteraction: false,
            pauseOnMouseEnter: true,
          }
        : false,

      speed: 800,

      pagination: {
        el: '.swiper-pagination',
        clickable: shouldLoop,
      },

      navigation: shouldLoop
        ? {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
          }
        : {},

      // preloadImages: false,
      // lazy: true,
    });
  }
</script>

