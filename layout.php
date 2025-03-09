<!-- layout.php -->
<div class="pane-container">
  <!-- List Pane: Serves as navigation -->
  <div class="list-pane">
    <ul class="contact-options">
      <li data-detail="contact-form" class="list-item active">Contact Us</li>
      <li data-detail="visit-us" class="list-item">Visit Us</li>
      <li data-detail="faq" class="list-item">FAQ</li>
    </ul>
  </div>

  <!-- Detail Pane: Displays selected content -->
  <div class="detail-pane">
    <!-- Video Section (Always Visible) -->
    <div class="detail-content">
      <video id="promoVideo" width="100%" height="auto" controls autoplay muted>
        <source src="images/videos/coming-soon-neon.mp4" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    </div>

    <!-- Contact Form (Dynamically Loaded) -->
    <div id="contact-form-detail">
      <?php include 'contact-form.php'; ?>
    </div>
  </div>
</div>


