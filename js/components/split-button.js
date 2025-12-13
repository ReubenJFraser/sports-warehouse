// ---------------------------------------------------------
// JS COMPONENT: Split Button (Admin Panel)
// File: js/components/split-button.js
// ---------------------------------------------------------

document.addEventListener('DOMContentLoaded', () => {
  // Toggle dropdown menu when the caret is clicked
  document.querySelectorAll('.split-button .menu').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation(); // Prevent outside click handler from firing immediately
      const splitButton = btn.closest('.split-button');
      const dropdown = splitButton.querySelector('.dropdown');

      const isOpen = dropdown.classList.contains('active');
      dropdown.classList.toggle('active');
      btn.setAttribute('aria-expanded', String(!isOpen));
    });
  });

  // Close all dropdowns when clicking outside
  document.addEventListener('click', (e) => {
    document.querySelectorAll('.split-button .dropdown.active').forEach(dropdown => {
      const wrapper = dropdown.closest('.split-button');
      if (!wrapper.contains(e.target)) {
        dropdown.classList.remove('active');
        const menuBtn = wrapper.querySelector('.menu');
        if (menuBtn) menuBtn.setAttribute('aria-expanded', 'false');
      }
    });
  });
});

// ---------------------------------------------------------
// Action: Default Deploy (no prompt)
// ---------------------------------------------------------
function runDeployDefault() {
  fetch("run_function.php", {
    method: "POST",
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: "function=deploy_cloudways"
  })
  .then(response => response.text())
  .then(data => alert("Deployment triggered: " + data))
  .catch(err => alert("Error triggering deploy: " + err));
}

// ---------------------------------------------------------
// Action: Prompt for commit message and submit form
// ---------------------------------------------------------
function promptCommit(button) {
  const message = prompt("Enter commit message:");
  if (message && button.closest('form')) {
    const form = button.closest('form');
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'message';
    input.value = message;
    form.appendChild(input);
    form.submit();
  }
}








