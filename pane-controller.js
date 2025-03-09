document.addEventListener('DOMContentLoaded', () => {
    const paneContainer = document.querySelector('.pane-container');
    const listPane = document.querySelector('.list-pane');
    const detailPane = document.querySelector('.detail-pane');
    const backBtn = document.querySelector('.back-btn');
    const listItems = document.querySelectorAll('.list-pane .list-item'); // Matches actual class name
  
    // Attach a click event to the back button if it exists
    if (backBtn) {
      backBtn.addEventListener('click', () => {
        paneContainer.classList.remove('open'); // Slide detail pane out, showing the list
      });
    } else {
      console.warn('Warning: No element with class ".back-btn" found.');
    }
  
    // Attach click events to each list item in the list pane
    listItems.forEach(item => {
      item.addEventListener('click', () => {
        paneContainer.classList.add('open'); // Slide in the detail pane on mobile
      });
    });
  });
  
  

  




