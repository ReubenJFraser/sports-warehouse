<!-- HEADER -->
<?php
require_once __DIR__ . '/url.php';
?>

<header class="site-header">

<!-- ==================== MOBILE APP BAR (≤767px) ==================== -->
<div class="mobile-header">
  <!-- 1) Hamburger on the left -->
  <button
    class="icon-button hamburger-menu"
    aria-label="Open menu"
    aria-controls="mobileNavDrawer"
    aria-expanded="false"
  >
    <span class="hamburger-icon icon" aria-hidden="true"></span>
    <span class="menu-label">Menu</span>
  </button>

  <!-- 2) Spacer under the camera notch -->
  <div class="camera-notch-spacer" aria-hidden="true"></div>

  <!-- 3) Actions on the right: Login + Cart -->
  <div class="mobile-header-actions">
    <a
      href="#"
      class="icon-button login-button"
      aria-label="Login to your account"
    >
      <div class="login-icon icon"></div>
      <span class="login-label">Login</span>
    </a>
    <a
      href="#"
      class="icon-button cart-button"
      aria-label="View shopping cart, 0 items"
    >
      <div class="cart-with-badge">
        <div class="cart-icon icon"></div>
        <span class="cart-badge" aria-label="0 items in cart">0</span>
      </div>
    </a>
  </div>
</div>

<!-- ==================== LOGO ROW (≤767px) ==================== -->
<div class="logo-row mobile-only">
  <div class="brand-logo aligned-content">
    <img
      src="images/logos/sports-warehouse-logo.svg"
      alt="Sports Warehouse Logo"
      class="sports-warehouse-logo">
  </div>
</div>

<!-- ==================== SEARCH ROW (≤767px) ==================== -->
<div class="search-row mobile-only">
  <form action="search.php" method="get" class="search-container">
    <div class="search-container-inner">
      <input
        type="text"
        name="q"
        id="search-input-mobile"
        placeholder="Search products"
        aria-label="Search Products"
        value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
      >
      <button type="submit" class="search-icon" aria-label="Submit Search">
        <span class="magnifying-glass icon" aria-hidden="true"></span>
      </button>
    </div>
  </form>
</div>

      <!-- ==================== MOBILE NAV DRAWER ==================== -->
      <nav
  id="mobileNavDrawer"
  class="mobile-nav-drawer mobile-only"
  aria-label="Mobile Navigation"
  aria-expanded="false"
>

  <div class="drawer-inner"> 
  <!-- Site Navigation -->
  <div class="mobile-nav-section">
    <h4>Site Navigation</h4>
    <ul>
      <li><a href="#">Home</a></li>
      <li><a href="#">About SW</a></li>
      <li><a href="#">View Products</a></li>
    </ul>
  </div>

  <!-- Product Categories -->
  <div class="mobile-nav-section">
    <h4>Product Categories</h4>
    <ul>
      <li><a href="category.php?category=Shoes">Shoes</a></li>
      <li><a href="category.php?category=Helmets">Helmets</a></li>
      <li><a href="category.php?category=Pants">Pants</a></li>
      <li><a href="category.php?category=Tops">Tops</a></li>
      <li><a href="category.php?category=Balls">Balls</a></li>
      <li><a href="category.php?category=Equipment">Equipment</a></li>
      <li><a href="category.php?category=Training Gear">Training Gear</a></li>
    </ul>
  </div>

  <!-- Contact & Social Section -->
<div class="mobile-nav-section contact-section">
<h4>Contact &amp; Social</h4>

<!-- 1) Phone row -->
<div class="contact-line">
  <a href="tel:1-800-123-4567" aria-label="Call us" class="social-icon">
<svg
viewBox="0 -960 960 960"
xmlns="http://www.w3.org/2000/svg"
role="img"
aria-labelledby="call-icon-title"
>
<title id="call-icon-title">Phone</title>
<path
  fill="currentColor"
  d="M798-120q-125 0-247-54.5T329-329Q229-429 174.5-551T120-798q0-18
     12-30t30-12h162q14 0 25 9.5t13 22.5l26 140q2 16-1 27t-11 19l-97 98q20
     37 47.5 71.5T387-386q31 31 65 57.5t72 48.5l94-94q9-9 23.5-13.5T670-390l138
     28q14 4 23 14.5t9 23.5v162q0 18-12 30t-30 12ZM241-600l66-66-17-94h-89q5
     41 14 81t26 79Zm358 358q39 17 79.5 27t81.5 13v-88l-94-19-67 67ZM241-600Zm358
     358Z"
/>
</svg>
</a>
  <span>1-800-123-4567</span>
</div>

<!-- 2) Email row -->
<div class="contact-line">
  <a href="mailto:info@sportswarehouse.com" aria-label="Email us" class="social-icon">
  <svg
    viewBox="0 0 20 20"
    xmlns="http://www.w3.org/2000/svg"
    role="img"
    aria-labelledby="email-icon-title"
  >
    <title id="email-icon-title">Email</title>
    <path
      fill="currentColor"
      d="M2.003 5.884l8 4.8 8-4.8A2 2 0 0 0 16 4H4a2 2 0 0 0-1.997 1.884z"
    />
    <path
      fill="currentColor"
      d="M18 8.118l-8 4.8-8-4.8V14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8.118z"
    />
  </svg>
  </a>
  <span>info@sportswarehouse.com</span>
</div>

<!-- 3) Follow row -->
<div class="contact-line follow-line">
  <span>Follow:</span>
  <span class="divider">|</span>

  <a
    href="https://facebook.com/YourPage"
    aria-label="Follow us on Facebook"
    class="social-icon"
  >
            <svg
              viewBox="0 0 500 500"
              xmlns="http://www.w3.org/2000/svg"
              role="img"
              aria-labelledby="fc-icon-title"
            >
              <title id="fc-icon-title">Facebook</title>
              <path
                fill="currentColor"
                d="M500,250C500,111.93,388.07,0,250,0S0,111.93,0,250
                  c0,117.24,80.72,215.62,189.61,242.64v-166.24h-51.55v-76.4h51.55
                  v-32.92c0-85.09,38.51-124.53,122.05-124.53,15.84,0,43.17,3.11,54.35,6.21
                  v69.25c-5.9-.62-16.15-.93-28.88-.93-40.99,0-56.83,15.53-56.83,55.9
                  v27.02h81.66l-14.03,76.4h-67.63v171.77c123.79-14.95,219.71-120.35,219.71-248.17Z"
              />
            </svg>
            </a>
  <span class="divider">|</span>

  <a
    href="https://m.me/YourPage"
    aria-label="Chat with us on Messenger"
    class="social-icon"
  >
            <svg
              viewBox="0 0 502 502"
              xmlns="http://www.w3.org/2000/svg"
              role="img"
              aria-labelledby="ms-icon-title"
            >
              <title id="ms-icon-title">Messenger</title>
              <path
                fill="currentColor"
                d="M251,1C110.17,1,1,104.16,1,243.5c0,72.89,29.87,135.86,78.51,179.37,
                  4.09,3.65,6.55,8.78,6.72,14.25l1.36,44.48c.43,14.18,15.09,23.41,28.06,17.68
                  l49.62-21.91c4.21-1.85,8.92-2.2,13.35-.97,22.81,6.27,47.07,9.61,72.37,9.61,
                  140.83,0,250-103.16,250-242.5S391.83,1,251,1ZM405.92,178.79l-87.04,134.52
                  c-4.42,6.83-13.53,8.78-20.36,4.36l-80.63-52.17c-3.12-2.02-7.16-1.96-10.22.15
                  l-90.88,62.68c-13.26,9.14-29.47-6.59-20.72-20.11l87.05-134.52
                  c4.42-6.83,13.53-8.78,20.35-4.36l80.65,52.18c3.12,2.02,7.16,1.96,10.22-.15
                  l90.86-62.67c13.26-9.15,29.47,6.59,20.72,20.11Z"
              />
            </svg>
            </a>
  <span class="divider">|</span>

  <a
    href="https://instagram.com/YourProfile"
    aria-label="Follow us on Instagram"
    class="social-icon"
  >
            <svg
              viewBox="0 0 1000 1000"
              xmlns="http://www.w3.org/2000/svg"
              role="img"
              aria-labelledby="ig-icon-title"
            >
              <title id="ig-icon-title">Instagram</title>
              <path
                fill="currentColor"
                transform="translate(-2.5 -2.5)"
                d="M295.42,6c-53.2,2.51-89.53,11-121.29,23.48-32.87,12.81-60.73,30-88.45,57.82
                S40.89,143,28.17,175.92c-12.31,31.83-20.65,68.19-23,121.42S2.3,367.68,2.56,503.46
                3.42,656.26,6,709.6c2.54,53.19,11,89.51,23.48,121.28,12.83,32.87,30,60.72,57.83,88.45
                S143,964.09,176,976.83c31.8,12.29,68.17,20.67,121.39,23s70.35,2.87,206.09,2.61
                152.83-.86,206.16-3.39S799.1,988,830.88,975.58c32.87-12.86,60.74-30,88.45-57.84
                S964.1,862,976.81,829.06c12.32-31.8,20.69-68.17,23-121.35,2.33-53.37,2.88-70.41,2.62-206.17
                s-.87-152.78-3.4-206.1-11-89.53-23.47-121.32c-12.85-32.87-30-60.7-57.82-88.45
                S862,40.87,829.07,28.19c-31.82-12.31-68.17-20.7-121.39-23S637.33,2.3,501.54,2.56
                348.75,3.4,295.42,6m5.84,903.88c-48.75-2.12-75.22-10.22-92.86-17-23.36-9-40-19.88-57.58-37.29
                s-28.38-34.11-37.5-57.42c-6.85-17.64-15.1-44.08-17.38-92.83-2.48-52.69-3-68.51-3.29-202
                s.22-149.29,2.53-202c2.08-48.71,10.23-75.21,17-92.84,9-23.39,19.84-40,37.29-57.57
                s34.1-28.39,57.43-37.51c17.62-6.88,44.06-15.06,92.79-17.38,52.73-2.5,68.53-3,202-3.29
                s149.31.21,202.06,2.53c48.71,2.12,75.22,10.19,92.83,17,23.37,9,40,19.81,57.57,37.29
                s28.4,34.07,37.52,57.45c6.89,17.57,15.07,44,17.37,92.76,2.51,52.73,3.08,68.54,3.32,202
                s-.23,149.31-2.54,202c-2.13,48.75-10.21,75.23-17,92.89-9,23.35-19.85,40-37.31,57.56
                s-34.09,28.38-57.43,37.5c-17.6,6.87-44.07,15.07-92.76,17.39-52.73,2.48-68.53,3-202.05,3.29
                s-149.27-.25-202-2.53m407.6-674.61a60,60,0,1,0,59.88-60.1,60,60,0,0,0-59.88,60.1
                M245.77,503c.28,141.8,115.44,256.49,257.21,256.22S759.52,643.8,759.25,502
                643.79,245.48,502,245.76,245.5,361.22,245.77,503m90.06-.18a166.67,166.67,0,1,1,167,166.34
                166.65,166.65,0,0,1-167-166.34"
              />
            </svg>
            </a>
  <span class="divider">|</span>

  <a
    href="https://wa.me/15551234567"
    aria-label="Chat with us on WhatsApp"
    class="social-icon"
  >
            <svg
              viewBox="0 0 360 362"
              xmlns="http://www.w3.org/2000/svg"
              role="img"
              aria-labelledby="wa-icon-title"
            >
              <title id="wa-icon-title">WhatsApp</title>
              <path
                fill="currentColor"
                fill-rule="evenodd"
                clip-rule="evenodd"
                d="M307.546 52.5655C273.709 18.685 228.706 0.0171895 180.756 0C81.951 0
                  1.53846 80.404 1.50408 179.235C1.48689 210.829 9.74646 241.667 25.4319 268.844
                  L0 361.736L95.0236 336.811C121.203 351.096 150.683 358.616 180.679 358.625H180.756
                  C279.544 358.625 359.966 278.212 360 179.381C360.017 131.483 341.392 86.4547
                  307.546 52.5741V52.5655ZM180.756 328.354H180.696C153.966 328.346 127.744 321.16
                  104.865 307.589L99.4242 304.358L43.034 319.149L58.0834 264.168L54.5423 258.53
                  C39.6304 234.809 31.749 207.391 31.7662 179.244C31.8006 97.1036 98.6334 30.2707
                  180.817 30.2707C220.61 30.2879 258.015 45.8015 286.145 73.9665C314.276 102.123
                  329.755 139.562 329.738 179.364C329.703 261.513 262.871 328.346 180.756 328.346
                  V328.354ZM262.475 216.777C257.997 214.534 235.978 203.704 231.869 202.209
                  C227.761 200.713 224.779 199.966 221.796 204.452C218.814 208.939 210.228 219.029
                  207.615 222.011C205.002 225.002 202.389 225.372 197.911 223.128C193.434 220.885
                  179.003 216.158 161.891 200.902C148.578 189.024 139.587 174.362 136.975 169.875
                  C134.362 165.389 136.7 162.965 138.934 160.739C140.945 158.728 143.412 155.505
                  145.655 152.892C147.899 150.279 148.638 148.406 150.133 145.423C151.629 142.432
                  150.881 139.82 149.764 137.576C148.646 135.333 139.691 113.287 135.952 104.323
                  C132.316 95.5909 128.621 96.777 125.879 96.6309C123.266 96.5019 120.284 96.4762
                  117.293 96.4762C114.302 96.4762 109.454 97.5935 105.346 102.08C101.238 106.566
                  89.6691 117.404 89.6691 139.441C89.6691 161.478 105.716 182.785 107.959 185.776
                  C110.202 188.767 139.544 234.001 184.469 253.408C195.153 258.023 203.498 260.782
                  210.004 262.845C220.731 266.257 230.494 265.776 238.212 264.624C246.816 263.335
                  264.71 253.786 268.44 243.326C272.17 232.866 272.17 223.893 271.053 222.028
                  C269.936 220.163 266.945 219.037 262.467 216.794L262.475 216.777Z"
              />
            </svg>
            </a>
</div>
</div><!-- /.drawer-inner -->
</nav>


<!-- ==================== DESKTOP LAYOUT (Shown at ≥769px) ==================== -->

<!-- Row 1: Full-Width Blue Navigation Bar -->
<nav class="header-row desktop-only" aria-label="Site Navigation and Login/Cart">
  <div class="site-container">
    <!-- Left Side: Main Site Links -->
    <div class="nav-left nav-group" aria-label="Site Links">
      <a href="#">Home</a>
      <a href="#">About SW</a>
      <a href="#">Contact Us</a>
      <a href="#">View Products</a>
    </div>
    <!-- Right Side: Login, View Cart, Item Count -->
    <div class="nav-right nav-group">
      <!-- Login Group -->
      <div class="login-group">
        <a href="#" aria-label="Login to your account">
          <div class="login-icon icon"></div>
          <span class="login-label">Login</span>
        </a>
      </div>
      <div class="view-cart-group">
        <a href="#" class="cart-with-badge" aria-label="Cart, 0 items">
          <div class="cart-icon icon"></div>
          <span class="cart-badge">0</span>
        </a>
      </div>
    </div>
  </div> <!-- end .site-container -->
</nav> <!-- end .header-row -->

<!-- Rows 2 & 3: contained in .site-container -->
<div class="site-container desktop-only">

  <!-- Row 2: Logo (left) and Search Bar (right) with white background -->
  <div class="logo-search-row">
    <div class="logo-wrapper">
      <img src="images/logos/sports-warehouse-logo.svg"
        alt="Sports Warehouse Logo"
        class="sports-warehouse-logo">
    </div>
    <div class="search-wrapper">
  <form action="search.php" method="get" class="search-form">
    <input
      type="text"
      name="q"
      id="search-input-desktop"
      placeholder="Search products"
      aria-label="Search Products"
      value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
    >
    <button type="submit" class="search-icon" aria-label="Search">
      <span class="magnifying-glass icon" aria-hidden="true"></span>
    </button>
  </form>
</div>
  </div> <!-- End of Row 2 -->

  <!-- Row 3: Category Navigation (dark bar with Shoes, Helmets, etc.) -->
  <nav class="desktop-category-nav desktop-only" aria-label="Product Categories">
    <a href="category.php?category=Shoes"        class="m3-btn">Shoes</a>
    <a href="category.php?category=Helmets"      class="m3-btn">Helmets</a>
    <a href="category.php?category=Pants"        class="m3-btn">Pants</a>
    <a href="category.php?category=Tops"         class="m3-btn">Tops</a>
    <a href="category.php?category=Balls"        class="m3-btn">Balls</a>
    <a href="category.php?category=Equipment"    class="m3-btn">Equipment</a>
    <a href="category.php?category=Training Gear" class="m3-btn">Training Gear</a>
</nav>


</div> <!-- end .site-container -->

</header>



