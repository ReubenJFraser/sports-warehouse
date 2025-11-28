// dialog-controller.js

document.addEventListener('DOMContentLoaded', () => {
    const formDialog = document.getElementById('formDialog');
    const openFormButton = document.getElementById('openFormButton'); // Ensure this button exists in your UI
    const closeDialog = document.getElementById('closeDialog');
  
    if (openFormButton && formDialog && closeDialog) {
      openFormButton.addEventListener('click', () => {
        formDialog.classList.add('active');
      });
  
      closeDialog.addEventListener('click', () => {
        formDialog.classList.remove('active');
      });
    }
  });
  