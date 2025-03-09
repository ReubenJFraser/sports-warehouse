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
</head>
<body>
  <div class="container">
    <div class="site-logo">
      <img src="images/sports-warehouse-logo.svg" alt="Sports Warehouse Logo" />
    </div>
    <p class="promo-message md-typescale-body-large">
      Sports Warehouse is coming soon. If you have any questions, please fill out the contact form below.
    </p>

    <!-- Include the layout structure (which contains the form & video) -->
    <?php include 'layout.php'; ?>
  </div>

  <!-- Theme Toggle Button -->
  <button id="themeToggle" class="theme-toggle">Toggle Theme</button>

  <!-- Floating Action Button (FAB) -->
  <button class="fab" aria-label="Contact Us">
    <span class="material-icons" aria-hidden="true">mail</span>
  </button>

  <!-- External JavaScript for Sliding Pane & Theme Toggle -->
  <script src="pane-controller.js" defer></script>

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













