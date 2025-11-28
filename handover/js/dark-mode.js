// /js/dark-mode.js
function applyMode() {
  const dark = window.matchMedia("(prefers-color-scheme: dark)").matches;
  document.body.classList.toggle("dark-mode", dark);
}

window.matchMedia("(prefers-color-scheme: dark)")
      .addEventListener("change", applyMode);

window.addEventListener("DOMContentLoaded", applyMode);

