// /js/orientation-utils.js

/**
 * Add .portrait-mode or .landscape-mode to <body>
 * so you can hook those in your CSS (and elsewhere in JS).
 */
function updateOrientationClass() {
  const isPortrait = window.matchMedia("(orientation: portrait)").matches;
  document.body.classList.toggle("portrait-mode", isPortrait);
  document.body.classList.toggle("landscape-mode", !isPortrait);
}

/**
 * Send a GA4 event whenever orientation flips.
 * (Requires gtag.js already loaded.)
 */
function trackOrientationChange() {
  const newOrientation = document.body.classList.contains("portrait-mode")
    ? "portrait"
    : "landscape";

  if (typeof gtag === "function") {
    gtag("event", "screen_orientation_change", {
      screen_orientation: newOrientation,
      event_category:     "engagement",
      event_label:        location.pathname,
      value:              Date.now()
    });
  }
}

// On initial load, set class *and* fire first analytics hit
window.addEventListener("DOMContentLoaded", () => {
  updateOrientationClass();
  trackOrientationChange();
});

// On every rotate, update class *and* fire another hit
window.addEventListener("orientationchange", () => {
  updateOrientationClass();
  trackOrientationChange();
});

export { updateOrientationClass, trackOrientationChange };


