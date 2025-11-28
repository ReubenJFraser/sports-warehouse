// /js/image-lazy.js

document.addEventListener("DOMContentLoaded", () => {
  const imgs = document.querySelectorAll("img[data-src]");

  const io = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      const img = entry.target;
      img.src = img.dataset.src;
      img.removeAttribute("data-src");
      obs.unobserve(img);
    });
  }, {
    rootMargin: "200px 0px" // start loading a bit before they scroll into view
  });

  imgs.forEach(img => io.observe(img));
});

