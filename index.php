<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Coming Soon</title>

  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="images/favicon.ico" />

  <!-- Google Fonts and Icons -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />

  <!-- Compiled SCSS -->
  <link rel="stylesheet" href="src/assets/sass/main.css" />

  <!-- Import Material Web Components -->
  <script type="module">
    import 'https://cdn.jsdelivr.net/npm/@material/web@2.2.0/button/filled-button.js/+esm';
    import 'https://cdn.jsdelivr.net/npm/@material/web@2.2.0/textfield/outlined-text-field.js/+esm';
    import { styles as typescaleStyles } from 'https://cdn.jsdelivr.net/npm/@material/web@2.2.0/typography/md-typescale-styles.js/+esm';
    document.adoptedStyleSheets.push(typescaleStyles.styleSheet);
  </script>

  <!-- Inline Styles for Under Construction Image (if needed) -->
  <style>
    .under-construction-img {
      max-width: 320px;
      width: 100%;
      height: auto;
      display: block;
      margin: 1rem auto;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="site-logo">
      <img src="images/sports-warehouse-logo.svg" alt="Sports Warehouse Logo" />
    </div>
    <p class="promo-message md-typescale-body-large">
      Sports Warehouse is coming soon. If you have any questions, please fill out the contact form below.
    </p>

    <!-- Include the layout structure (which contains the video and form) -->
    <?php include 'layout.php'; ?>
  </div>

  <!-- Theme Toggle Button -->
  <button id="themeToggle" class="theme-toggle">Toggle Theme</button>

  <!-- Floating Action Button (FAB) -->
  <button class="fab" aria-label="Contact Us" id="openFormButton">
    <span class="material-icons" aria-hidden="true">mail</span>
  </button>

  <!-- External JavaScript for Sliding Pane & Theme Toggle -->
  <script src="pane-controller.js" defer></script>

  <!-- JavaScript for Handling Video Sequence -->
  <script type="module">
    document.addEventListener('DOMContentLoaded', () => {
      const promoVideo = document.getElementById('promoVideo');
      // Adjust the container selection if your video is inside a specific element in layout.php
      const leftContent = document.querySelector('.detail-content');
      let videoCount = 0;

      promoVideo.addEventListener('ended', () => {
        videoCount++;

        if (videoCount === 1) {
          // Switch from "Coming Soon Neon" to "Quick High-Five"
          promoVideo.src = 'images/videos/quick-high-five.mov.mp4';
          promoVideo.load();
          promoVideo.play();
        } else if (videoCount === 2) {
          // Remove the video element and insert the "Under Construction" image
          promoVideo.remove();
          const underConstructionImg = document.createElement('img');
          underConstructionImg.src = 'images/website-under-construction.png';
          underConstructionImg.alt = 'Website Under Construction';
          underConstructionImg.classList.add('under-construction-img');
          leftContent.appendChild(underConstructionImg);
        }
      });
    });
  </script>

  <!-- Include Full-Screen Dialog Partial for Mobile Form -->
  <?php include 'dialog.php'; ?>

  <!-- External JavaScript for Dialog Controller -->
  <script src="dialog-controller.js" defer></script>

  <!-- Inline Script for Theme Toggle Functionality -->
  <script type="module">
    document.addEventListener('DOMContentLoaded', () => {
      const toggleBtn = document.getElementById('themeToggle');
      const bodyEl = document.body;
      // Set initial theme to light
      bodyEl.classList.add('sport-warehouse-light-theme', 'sport-warehouse-fixed-theme');

      toggleBtn.addEventListener('click', () => {
        if (bodyEl.classList.contains('sport-warehouse-light-theme')) {
          bodyEl.classList.remove('sport-warehouse-light-theme');
          bodyEl.classList.add('sport-warehouse-dark-theme');
        } else {
          bodyEl.classList.remove('sport-warehouse-dark-theme');
          bodyEl.classList.add('sport-warehouse-light-theme');
        }
      });
    });
  </script>

  <!-- Footer with Video Reference Tooltips -->
  <footer class="video-references" style="text-align: center; padding: 1rem;">
    <p>
      Video 1: 
      <span class="tooltip" data-tooltip="AdobeStock_316253953.mov – Es Sarawuth: COMING SOON neon sign on dark background">
        Coming Soon Neon
      </span>
    </p>
    <p>
      Video 2: 
      <span class="tooltip" data-tooltip="AdobeStock_439110123.mov – Kawee: Sport friend gives high five, crosses arms and looks at camera in gym">
        Quick High-Five
      </span>
    </p>
  </footer>
</body>
</html>

















