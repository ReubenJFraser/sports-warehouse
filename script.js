// script.js

document.addEventListener('DOMContentLoaded', () => {
  // Form validation setup
  const form = document.getElementById('contactForm');
  const firstNameField = document.getElementById('firstNameField');
  const lastNameField  = document.getElementById('lastNameField');
  const emailField     = document.getElementById('emailField');

  // Error message elements
  const firstNameError = document.getElementById('firstNameError');
  const lastNameError  = document.getElementById('lastNameError');
  const emailError     = document.getElementById('emailError');

  // A simple email regex for demonstration
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  form.addEventListener('submit', function(event) {
    let formIsValid = true;

    // Validate First Name
    if (!firstNameField.value.trim()) {
      formIsValid = false;
      firstNameError.textContent = "First Name is required.";
    } else {
      firstNameError.textContent = "";
    }

    // Validate Last Name
    if (!lastNameField.value.trim()) {
      formIsValid = false;
      lastNameError.textContent = "Last Name is required.";
    } else {
      lastNameError.textContent = "";
    }

    // Validate Email (required and must match pattern)
    const emailVal = emailField.value.trim();
    if (!emailVal) {
      formIsValid = false;
      emailError.textContent = "Email is required.";
    } else if (!emailPattern.test(emailVal)) {
      formIsValid = false;
      emailError.textContent = "Please enter a valid email address.";
    } else {
      emailError.textContent = "";
    }

    // If form is invalid, prevent submission
    if (!formIsValid) {
      event.preventDefault();
    }
  });

  // Theme toggle setup
  const toggleBtn = document.getElementById('themeToggle');
  const bodyEl = document.body;
  
  // Start in light theme
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

