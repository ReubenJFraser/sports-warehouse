# Mixed-Aspect-Ratio Product Cards and Detail View

Mixed-Aspect-Ratio Product Cards and Detail
View
We propose that each product card image be a near-square portrait (4:5)  thumbnail. Using a 4:5 aspect
ratio (width:height) maximizes vertical detail while keeping cards uniform. Large, detailed thumbnails
help users distinguish products . All cards should use the same 4:5 container and styling so the grid
looks consistent . This avoids the “chaotic” misalignment that occurs when mixing arbitrary aspect
ratios. In practice, a CSS Grid or Flexbox layout with fixed card dimensions (or the CSS  aspect-
ratio property) can enforce the 4:5 size. If any images vary slightly, use object-fit: contain  so
each image fills its 4:5 box without cropping . 
Grid layout:  Because all cards share 4:5, a simple responsive grid works (e.g. repeat(auto-
fit, minmax(..)) ).  Optionally,  a  justified-image  grid  algorithm  (as  used  by  Flickr/Gallery
plugins) can be applied: it groups images into rows of equal height that fill the container .
This JavaScript approach calculates a target row height and adjusts each image’s width so every
row exactly spans the viewport, preserving each photo’s aspect ratio . The result is clean rows
with no gaps – portrait and landscape cards get balanced space . However , since we’re fixing
the card aspect to 4:5, the justified script is not strictly required for the listing. 
Consistency:  All product thumbnails should share style (background, padding, camera angle) to
aid comparison. For example, if one product’s image is portrait-oriented and another’s is
landscape-oriented, the 4:5 container and consistent padding will keep the grid aligned. (By
contrast, mixing 1:1, 4:3, 16:9 images on a page “creates a chaotic user experience” .) In short,
we set the product-card image box to 4:5 and fill it with the image, scaling as needed. This
ensures a uniform, easy-to-scan listing .
Image  loading:  To  optimize  performance,  load  images  lazily.  For  example,  use  an
IntersectionObserver  so off-screen product images only download when scrolled into view
. Assign each image placeholder the correct 4:5 space up front (using CSS width/height) so
the layout doesn’t shift when images load. This approach – allocate space based on known
aspect ratio, then swap in the real image – prevents “jank”.
Detail View & Carousel
When the user clicks a product card image, open a  gallery overlay  (modal or expanded detail view)
containing all of that product’s images (portrait, square, landscape). This “image gallery overlay” pattern
is well‑established . In the overlay, the first slide should show the selected image at large size, with
navigation arrows/dots to move to other images. The user can click or swipe to progress through the
carousel. 
Expanded gallery:  As Baymard Institute notes, sites commonly show detailed product images in
a modal “image gallery overlay” that contains all available images and videos. We will follow
this pattern. The overlay should allow fullscreen or zoomed viewing. Avoid any UI that hides
images – e.g. do not truncate thumbnail lists without clear indication. Provide visible next/
prev arrows and (on mobile) swipe gestures so users can navigate images.


Carousel controls: Always include manual controls. Users expect arrow buttons or dots to move
between slides. For example, show left/right arrows on desktop, and enable swipe/touch on
mobile. Ensure any indicator dots or thumbnails are fully visible (don’t crop or hide them) to
avoid users overlooking images.
Image grouping by orientation: Within the carousel, group images by orientation  to keep
transitions  smooth.  Automatically  detect  each  image’s  orientation  (by  comparing  its  natural
width  vs  height).  Classify  images  as  “portrait”  (height > width),  “square”  (roughly  equal),  or
“landscape”  (width > height).  Then  order  the  carousel  so  that  all  similar-orientation  images
appear consecutively. For example, show all portrait images first, then any square images, then
landscape images. This way, sliding from one image to the next won’t suddenly jump from a tall
portrait to a wide landscape (which can be jarring).
Automatic detection:  Implement orientation grouping in JavaScript. After images load (or from
known metadata), do something like:
letportraits =[],squares =[],landscapes =[];
images.forEach(img=>{
if(img.naturalWidth ===0)return;// not loaded yet
if(img.naturalWidth >img.naturalHeight )landscapes .push(img);
elseif(img.naturalWidth <img.naturalHeight )portraits .push(img);
elsesquares.push(img);
});
letordered =portraits .concat(squares,landscapes );
buildCarousel (ordered);
This ensures the carousel index progresses from vertical to horizontal images in one direction. (If
desired, we could sort differently, but grouping by aspect is key.) Once grouped, initialize the
carousel library or custom slider with the sorted list. The effect is a balanced viewing experience:
the user can explore, say, all tall product shots in a row, then see square/product-detail shots,
then wide scene or lifestyle shots.
Hero vs Sidebar images:  We’ll define distinct roles for each aspect category. For the hero banner
(the main product image on the detail page), a wide landscape image is usually ideal. In many
design systems, hero banners use a 16:9 ratio . If we choose a single hero image, we should
use a landscape image scaled to 16:9 (or similar) to fill the section nicely . Alternatively, if the
hero area will show multiple images side-by-side, use square or 4:5 images in that row so they
align neatly. For example, two 4:5 portrait images can sit side-by-side like tiles. 
In contrast, a sidebar thumbnail  (or mini-slider) can use the same 4:5 standard as the product cards. This
way, any image shown as a secondary preview or cart thumbnail appears the same shape as on the
listing.  Keeping  sidebar/thumbnail  images  at  4:5  maintains  consistency:  the  listing  page,  the  cart
sidebar , and the product card all use the same aspect ratio.
Implementation Details
Prepare images:  Store each image’s width/height (or aspect ratio) in metadata if possible. If not,
read img.naturalWidth /naturalHeight  after loading to compute orientation. It’s best to
know each image’s ratio before rendering, so you can allocate correct space.• 

Triggering the overlay:  Attach a click handler on the product card image. On click, launch the
gallery overlay and pause page scrolling. Insert the carousel slides (ordered by orientation) into
the overlay container . 
Resizing & responsiveness:  The carousel should adapt if the viewport size changes. If a user
rotates a device or resizes the window, you may need to recalc image sizes or reposition slides.
Some carousel libraries handle this automatically; otherwise, listen to a resize event and refresh
the slider . 
Styling  images:  Use  max-width: 100%  and  max-height: 100%  on  carousel  images.
Combined  with  the  grouping  strategy,  this  ensures  each  image  fills  the  slide  area  without
overflow. (For example, a portrait image in a wide slide will be vertically tall but centered; a
landscape in a tall container will fit horizontally.)
Performance optimizations:  -  Lazy-loading:  If a product has many images, don’t load all at
once. Instead, load the first few (e.g. current and adjacent slides), and defer others until needed.
IntersectionObserver can help here: only fetch images when their slide is about to be displayed
. -  Placeholder sizing:  Allocate each slide’s space based on aspect ratio before the image
loads, to avoid layout shifts. For instance, if a portrait image is 800×1000 (4:5), set its container to
that 4:5 ratio. - Smooth transitions:  Apply CSS transitions or use a carousel library to animate
between slides, avoiding sudden jumps if the layout recalculates.
Fallback layout:  If JavaScript is disabled or fails, ensure at least the clicked image opens in a
larger view. For example, the link could point to a lightbox URL of that image. But our primary
plan assumes a JS-powered overlay.
Summary
Product cards  will use 4:5 (width:height)  images for consistency. Use CSS or a small layout
script (like a justified grid algorithm) to make all rows tidy . Large, consistent thumbnails
improve scanning .
Detail view  will open a carousel overlay  showing all images. This follows common UX patterns
. Users navigate with arrows or swipes .
Images are grouped by orientation  automatically. Detect each image’s dimensions, then sort
portrait vs square vs landscape. Show grouped images sequentially to avoid jarring aspect
jumps.
Hero and sidebar images  have their own roles: use landscape (16:9) for wide banners , and
use 4:5 for any smaller product thumbnails or sidebars.
Implementation  involves reading image sizes, initializing a JS slider with the ordered images,
and optimizing load with lazy-loading .
This plan ensures that both portrait and landscape product photos are presented effectively. Users will
see consistent, full-size product images on cards, and be able to explore every image in a smooth
carousel experience . The solution leverages known techniques (justified grids, object-fit, modal
galleries) and groups images by aspect to deliver a polished, user-friendly gallery .
Sources:  Nielsen Norman Group on product images ; Baymard e‑commerce UX research on
carousels  and  image  galleries ;  technical  guides  on  responsive  image  grids ;  and
e‑commerce best practices for aspect ratios. 
