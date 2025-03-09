
const paneContainer = document.querySelector('.pane-container');
const listPane = document.querySelector('.list-pane');
const detailPane = document.querySelector('.detail-pane');
const backBtn = document.querySelector('.back-btn');
const listItems = document.querySelectorAll('.list-pane .list-item'); // Matches actual class name

/* Attach a click event to the back button (in the detail pane */
backBtn.addEventListener('click', () => {
    paneContainer.classList.remove('open'); // slide detail pane out, showing the list
  });


/* the detail pane already contains the form/video. Just ensure it’s visible. */

listItems.forEach(item => {
    item.addEventListener('click', () => {
      // Optionally, load specific content based on item (if multiple details)
      paneContainer.classList.add('open'); // slide in the detail pane on mobile
    });
  });
  

  




