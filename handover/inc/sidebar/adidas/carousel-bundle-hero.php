<!-- Global Wrapper -->
<section aria-label="Bundle Rewards Carousel">
  <h2 class="carousel-header">Bundle Rewards</h2>
  <div class="bundle-carousel swiper-container"> …slides… </div>
  <p class="carousel-footnote">Hyperglam counts as two items</p>
  <button class="carousel-cta">Shop Bundles</button>
</section>

<!-- Slide Markup (repeat for each slide -->
<div class="swiper-slide bundle-slide">
  <img src="images/brands/adidas/banners/women/athleisure-collage.png" alt="Athleisure set">
  <div class="bundle-badge">Buy 2 → 25% OFF</div>
  <p class="slide-description">Mix & Match Athleisure pieces and save.</p>
</div>
<!-- Next slides: Buy 3 → 50% OFF 3rd; Buy 4 → 50% OFF 3rd & 4th -->



import React from 'react';
import { Swiper, SwiperSlide } from 'swiper/react';
import { Navigation, Autoplay } from 'swiper';
import 'swiper/css';
import 'swiper/css/navigation';

interface Slide {
  imageSrc: string;
  imageAlt: string;
  badgeText: string;
  description: string;
}

const slides: Slide[] = [
  {
    imageSrc: 'images/brands/adidas/banners/women/athleisure-collage.png',
    imageAlt: 'Athleisure set: tube top, shorts, leggings',
    badgeText: 'Buy 2 → 25% OFF',
    description: 'Mix & match Athleisure pieces and save.',
  },
  {
    imageSrc: 'images/brands/adidas/banners/women/powerreact-collage.png',
    imageAlt: 'Powerreact set: bra and tights',
    badgeText: 'Buy 3 → 50% OFF 3rd',
    description: 'Build your training kit and earn deeper discounts.',
  },
  {
    imageSrc: 'images/brands/adidas/banners/women/hyperglam-collage.png',
    imageAlt: 'Hyperglam set: long sleeve crop top and leggings',
    badgeText: 'Buy 4 → 50% OFF 3rd & 4th',
    description: 'Go all in on style—unlock the biggest savings.',
  },
];

export function BundleRewardsCarousel() {
  return (
    <section
      aria-label="Bundle Rewards Carousel"
      className="relative bg-white py-12"
    >
      <div className="container mx-auto px-4 text-center">
        <h2 className="text-3xl font-bold mb-6">Bundle Rewards</h2>

        <Swiper
          modules={[Navigation, Autoplay]}
          navigation
          autoplay={{ delay: 8000, disableOnInteraction: false }}
          loop
          className="bundle-swiper"
        >
          {slides.map((slide, idx) => (
            <SwiperSlide key={idx}>
              <div className="relative">
                <img
                  src={slide.imageSrc}
                  alt={slide.imageAlt}
                  className="w-full h-64 md:h-96 object-cover rounded-lg"
                />
                <div className="absolute top-4 left-4 bg-black bg-opacity-75 text-white px-3 py-1 rounded-full text-sm font-semibold">
                  {slide.badgeText}
                </div>
                <p className="mt-4 text-lg font-medium">{slide.description}</p>
              </div>
            </SwiperSlide>
          ))}
        </Swiper>

        <p className="mt-4 italic text-sm text-gray-500">
          Hyperglam counts as two items
        </p>

        <button
          className="mt-6 inline-block bg-sidebar-primary text-sidebar-primary-foreground hover:bg-sidebar-primary/90 uppercase py-3 px-8 rounded-md font-semibold"
        >
          Shop Bundles
        </button>
      </div>
    </section>
  );
}




