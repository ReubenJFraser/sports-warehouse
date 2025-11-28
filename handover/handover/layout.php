<!-- layout.php -->
<div class="pane-container">
  <!-- List Pane: For future navigation (currently hidden) -->
  <div class="list-pane" style="display: none;">
    <!-- Developer Note: This list is reserved for future navigation. Do not display in production until needed. -->
    <ul class="contact-options">
      <li data-detail="contact-form" class="list-item active">Contact Us</li>
      <li data-detail="visit-us" class="list-item">Visit Us</li>
      <li data-detail="faq" class="list-item">FAQ</li>
    </ul>
  </div>

  <!-- Detail Pane: Displays Video & Form -->
  <div class="detail-pane">
    <!-- Video Section (Always Visible) -->
    <div class="detail-content">
      <video id="promoVideo" width="100%" height="auto" controls autoplay muted>
        <source src="images/videos/coming-soon-neon.mp4" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    </div>

    <!-- Contact Form (Visible on Desktop Only) -->
    <div id="desktop-contact-form" class="desktop-form">
      <?php include 'contact-form.php'; ?>
    </div>
  </div>
</div>
