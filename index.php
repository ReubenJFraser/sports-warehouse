<?php
// ???
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sports Warehouse</title>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,600" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <!-- Standard Favicon -->
  <link rel="icon" type="image/x-icon" href="/images/logos/sports-warehouse-icon-SW.ico">
  <!-- Alternative PNG Favicon (optional) -->
  <link rel="icon" type="image/png" sizes="32x32" href="/images/logos/sports-warehouse-icon-SW-alternative_favicon.png">
  <!-- Apple Touch Icon for mobile devices -->
  <link rel="apple-touch-icon" sizes="180x180" href="/images/logos/sports-warehouse-icon-SW-recommended_apple_size.png">
  <!-- Optional: Additional meta tags for theme color on supported devices -->
  <meta name="theme-color" content="#ff690c">
</head>
<body>

  <!-- HEADER -->
  <header class="site-header">

    <!-- ==================== MOBILE LAYOUT (Default) ==================== -->

    <!-- 1) App Bar (light blue row): hamburger + cart -->
    <div class="header mobile-header" aria-label="Mobile App Bar">
      <button class="icon-button hamburger-menu" aria-label="Toggle Menu">
        <div class="hamburger-icon-container">
          <!-- Icon set via CSS (.hamburger-icon) -->
          <div class="hamburger-icon icon"></div>
        </div>
        <span class="menu-label">Menu</span>
      </button>
      <div class="shopping-cart">
        <a href="#" aria-label="View Cart">
          <!-- Cart icon defined via CSS (.cart-icon) -->
          <div class="cart-icon icon"></div>
          <div class="cart-text">
            <span class="view-cart">View Cart</span>
            <span class="item-count">0 items</span>
          </div>
        </a>
      </div>
    </div>

    <!-- Mobile Nav Drawer (Hidden by default; toggled via .open or JS) -->
    <nav id="mobileNavDrawer" 
         class="mobile-nav-drawer mobile-only" 
         aria-label="Mobile Navigation" 
         role="navigation">
      <ul>
        <li>
          <a href="#" aria-label="Login">Login</a>
        </li>
        <li>
          <a href="#">Home</a>
        </li>
        <li>
          <a href="#">About SW</a>
        </li>
        <li>
          <a href="#">Contact Us</a>
        </li>
        <li>
          <a href="#">View Products</a>
        </li>
      </ul>
    </nav>

    <!-- 2) Logo Row (mobile) -->
    <div class="logo-row mobile-only">
      <div class="brand-logo aligned-content">
        <img src="/images/sports-warehouse-logo.svg" alt="Sports Warehouse Logo" class="sports-warehouse-logo">
      </div>
    </div>

    <!-- 3) Search Row (mobile) -->
    <div class="search-row mobile-only">
      <div class="search-container">
        <div class="search-container-inner">
          <input type="text" id="search-input-mobile"
                 placeholder="Search products" aria-label="Search Products"/>
          <button class="search-icon" aria-label="Submit Search">
            <div class="search-icon-circle">
              <!-- Magnifying glass icon set via CSS -->
              <div class="magnifying-glass icon"></div>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- ==================== DESKTOP LAYOUT (Shown at ≥600px) ==================== -->

    <!-- Row 1: Full-Width Blue Navigation Bar -->
    <div class="header-row desktop-only" aria-label="Site Navigation and Login/Cart">
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
          <!-- View Cart Group -->
          <div class="view-cart-group">
            <a href="#" aria-label="View Cart">
              <div class="cart-icon icon"></div>
              <span class="view-cart">View Cart</span>
            </a>
          </div>
          <!-- Item Count Group -->
          <div class="item-count-group">
            <span class="item-count">0 items</span>
          </div>
        </div>
      </div> <!-- end .site-container -->
    </div> <!-- end .header-row -->

    <!-- Rows 2 & 3: contained in .site-container -->
    <div class="site-container desktop-only">

      <!-- Row 2: Logo (left) and Search Bar (right) with white background -->
      <div class="logo-search-row">
        <div class="logo-wrapper">
          <img src="/images/sports-warehouse-logo.svg"
            alt="Sports Warehouse Logo"
            width="300"
            height="41"
            class="sports-warehouse-logo">
        </div>
        <div class="search-wrapper">
          <input type="text"
            id="search-input-desktop"
            placeholder="Search products"
            aria-label="Search Products">
          <button class="search-icon" aria-label="Search Button">
            <div class="search-icon-circle">
              <div class="magnifying-glass icon"></div>
            </div>
          </button>
        </div>
      </div> <!-- End of Row 2 -->

      <!-- Row 3: Category Navigation (dark bar with Shoes, Helmets, etc.) -->
      <nav class="desktop-category-nav desktop-only" aria-label="Product Categories">
        <a href="#">Shoes</a>
        <a href="#" class="active">Helmets</a>
        <a href="#">Pants</a>
        <a href="#">Tops</a>
        <a href="#">Balls</a>
        <a href="#">Equipment</a>
        <a href="#">Training Gear</a>
      </nav>

    </div> <!-- end .site-container -->

  </header>

  <main>
  <!-- Featured Products Section -->
  <section class="featured-products">
    <!-- Orange title bar (reusable .section-featured style) -->
    <div class="section-featured">
      <h2>Featured Products</h2>
    </div>

    <!-- Products grid container -->
    <div class="featured-products-grid">
      
      <!-- Example product card #1 -->
      <div class="product-card">
        <img src="images\products\Adidas-Soccer-EURO16™-Top_Glider_Ball.jpg" alt="adidas EURO16™ Top Glider Soccer Ball">
        <div class="product-pricing">
          <span class="price-original">$46.00</span>
          <span class="price-current">$34.95</span>          
        </div>
        <div class="product-name">adidas EURO16™ Top Glider Soccer Ball</div>
      </div>

      <!-- Example product card #2 -->
      <div class="product-card">
        <img src="images\products\Pro-Tec-Skate-Helmet-Classic_Certified-Black_Metal_Flake.jpg" alt="Pro-Tec Classic Certified Skate Helmet Black Metal Flake">
        <div class="product-pricing">          
          <span class="price-original">$110</span>
          <span class="price-current">$98.99</span>
        </div>
        <div class="product-name">Pro-Tec Classic Certified Skate Helmet Black Metal Flake</div>
      </div>

      <!-- Example product card #3 -->
      <div class="product-card">
        <img src="images\products\Nike-Hypersport-600ml_Drink_Bottle.jpg" alt="Nike Hypersport 600ml Water Bottle">
        <div class="product-pricing">          
          <span class="price-original">$17.50</span>
          <span class="price-current">$15.00</span>
        </div>
        <div class="product-name">Nike Hypersport 600ml Water Bottle</div>
      </div>

      <!-- Example product card #4 -->
      <div class="product-card">
        <img src="images\products\Sting-Armaplus-Boxing_Gloves-T3.jpg" alt="Sting ArmaPlus Boxing Gloves">
        <div class="product-pricing">
          <span class="price-current">$79.95</span>
          <!-- No original price for this one -->
        </div>
        <div class="product-name">Sting ArmaPlus Boxing Gloves</div>
      </div>

      <!-- Example product card #5 -->
      <div class="product-card">
        <img src="images\products\Asics-Mens-Soccer_Boots-Gel_Lethal_Tigreor_8_IT.jpg" alt="Asics Gel Lethal Tigreor 8 IT Men's">
        <div class="product-pricing">          
          <span class="price-original">$260</span>
          <span class="price-current">$239.99</span>
        </div>
        <div class="product-name">Asics Gel Lethal Tigreor 8 IT Men's</div>
      </div>
      <!-- Example product card #6 -->
      <div class="product-card">
        <img src="images\products\Asics-Mens-Running_Shoes-Kayano_26.jpg" alt="Asics Gel Kayano 26 Running Shoes Men's">
        <div class="product-pricing">          
          <span class="price-original">$230</span>
          <span class="price-current">$179.95</span>
        </div>
        <div class="product-name">Asics Gel Lethal Tigreor 8 IT Men's</div>
      </div>
      <!-- Example product card #7 -->
      <div class="product-card">
        <img src="images\products\Adidas-Mens-Essentials-3_Stripes-Training_Tee.png" alt="Adidas Mens Essentials 3 Stripes Training Tee">
        <div class="product-pricing">          
          <span class="price-original">$40</span>
          <span class="price-current">$27.99</span>
        </div>
        <div class="product-name">Adidas Men's Essentials 3 Stripes Training Tee</div>
      </div>
      <!-- Example product card #8 -->
      <div class="product-card">
        <img src="images\products\Adidas-Womens-Essentials-3_Stripes-Flared_Leggings.png" alt="Adidas Womens Essentials 3 Stripes Flared Leggings">
        <div class="product-pricing">          
          <span class="price-original">$75</span>
          <span class="price-current">$59.95</span>
        </div>
        <div class="product-name">Adidas Women's Essentials 3 Stripes Flared Leggings</div>
      </div>
      <!-- Example product card #9 -->
      <div class="product-card">
        <img src="images\products\Nike-Kids-Girls-Futura-Air-Pink_and_White.png" alt="Nike Kids Futura Air Tee">
        <div class="product-pricing">          
          <span class="price-original">$29.99</span>
          <span class="price-current">$19.99</span>
        </div>
        <div class="product-name">Nike Futura Air Tee Kids</div>
      </div>
    </div> <!-- /.featured-products-grid -->
  </section>

<!-- Brands & Partnerships Section -->
<section class="brands">
  <!-- Orange section header reused from .section-featured style -->
  <div class="section-featured">
    <h2>Our brands and partnerships</h2>
  </div>

  <!-- Optional intro text, describing this section -->
  <p class="brand-intro">
    These are some of our top brands and partnerships. 
    <span class="highlight">The best of the best is here.</span>
  </p>

  <!-- Logo row (flex container) -->
  <div class="brand-logos">
    <!-- For each brand, wrap the logo in an <a> if you want it clickable -->
    <a href="#" aria-label="Adidas"><img src="images/logos/Adidas-currentColor.svg" alt="Adidas Logo"></a>
    <a href="#" aria-label="Asics"><img src="images/logos/Asics-currentColor.svg" alt="Asics Logo"></a>
    <a href="#" aria-label="New Balance"><img src="images/logos/New_Balance-currentColor.svg" alt="New Balance Logo"></a>
    <a href="#" aria-label="Nike"><img src="images/logos/NIKE-currentColor.svg" alt="Nike Logo"></a>
    <a href="#" aria-label="Puma"><img src="images/logos/Puma-currentColor.svg" alt="Puma Logo"></a>
    <a href="#" aria-label="Reebok"><img src="images/logos/Reebok-currentColor.svg" alt="Reebok Logo"></a>
    <a href="#" aria-label="Under Armour"><img src="images/logos/Under_Armour-currentColor.svg" alt="Under Armour Logo"></a>
    <a href="#" aria-label="Wilson"><img src="images/logos/Wilson-currentColor.svg" alt="Wilson Logo"></a>
  </div>
</section>

<!-- Footer Section -->
<footer class="footer-bar">
  <!-- Top Area: Three Colored Columns -->
  <div class="footer-columns">
    <!-- Left Column (Dark Blue) -->
    <div class="footer-column footer-left-col">
      <h4>Site Navigation</h4>
      <ul>
        <li><a href="#">Home</a></li>
        <li><a href="#">About SW</a></li>       
        <li><a href="#">View Products</a></li>
      </ul>
      <!-- Separate "Contact Us" trigger button placed beneath the list -->
      <div class="contact-button-wrapper">
        <button id="openContactModal" class="contact-button">
          Contact Us
        </button>
      </div>  
    </div>

    <!-- Middle Column (Dark Orange) -->
    <div class="footer-column footer-mid-col">
      <h4>Product Categories</h4>
      <ul>
        <li><a href="#">Shoes</a></li>
        <li><a href="#">Helmets</a></li>
        <li><a href="#">Parts</a></li>
        <li><a href="#">Tops</a></li>
        <li><a href="#">Balls</a></li>
        <li><a href="#">Equipment</a></li>
        <li><a href="#">Training Gear</a></li>
      </ul>
    </div>

    <!-- Right Column (Dark Blue) -->
    <div class="footer-column footer-right-col">
      <h4>Contact &amp; Social</h4>
      <p>
        Call: 1-800-123-4567<br>
        Email: info@sportswarehouse.com
      </p>
      <p>Follow: 
        <a href="#">FB</a> | 
        <a href="#">TW</a> | 
        <a href="#">IG</a>
      </p>
    </div>
  </div>

  <!-- Bottom White Bar -->
  <div class="footer-bottom">
    <p>&copy; 2025 Sports Warehouse. All rights reserved.<br>
    Website made by Awesomesauce Design and Kyle Leong.</p>
  </div>
</footer>

<!-- Contact Form Modal (Hidden by Default) -->
<div class="contact-modal" id="contactModal">
  <div class="contact-modal-content">
  <button type="button" class="close-button" id="closeForm" aria-label="Close Form">
    <img src="images/close_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.svg" alt="Close">
  </button>

    <h2 class="visually-hidden">Contact Us Form</h2>

        <!-- PHP Feedback -->
        <?php if (!empty($errors)): ?>
          <div class="error-messages">
            <ul>
              <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php elseif ($successMessage): ?>
          <div class="success-message">
            <?php echo htmlspecialchars($successMessage); ?>
          </div>
        <?php endif; ?>

        <form method="post" action="index.php#contactForm">
          <div class="input-group">
            <input type="text" name="first-name" id="first-name" placeholder=" " required
                   value="<?php echo htmlspecialchars($firstName); ?>">
            <label for="first-name">First Name</label>
          </div>
          <div class="input-group">
            <input type="text" name="last-name" id="last-name" placeholder=" " required
                   value="<?php echo htmlspecialchars($lastName); ?>">
            <label for="last-name">Last Name</label>
          </div>
          <div class="input-group">
            <input type="text" name="contact-number" id="contact-number" placeholder=" " required
                   value="<?php echo htmlspecialchars($contactNumber); ?>">
            <label for="contact-number">Contact Number</label>
          </div>
          <div class="input-group">
            <input type="email" name="email-address" id="email-address" placeholder=" " required
                   value="<?php echo htmlspecialchars($emailAddress); ?>">
            <label for="email-address">Email Address</label>
          </div>
          <div class="input-group">
          <textarea name="question-comment" id="question-comment" placeholder=" " required><?php
              echo htmlspecialchars($questionComment);
            ?></textarea>
            <label for="question-comment">Question or Comment</label>
          </div>
          <button type="submit">Submit</button>
        </form>
      </section>
    </aside>
  </main>

  <script src="script.js"></script>
</body>
</html>

















