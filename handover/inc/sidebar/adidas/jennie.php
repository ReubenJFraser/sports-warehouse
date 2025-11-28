
import { ImageWithFallback } from './figma/ImageWithFallback';
import { Button } from './ui/button';
import { Card, CardContent } from './ui/card';
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from './ui/carousel';
import jennieVideoImage from 'figma:asset/1512f217872833391b2d811001a5752cf6c88f26.png';
import jenniePortraitImage from 'figma:asset/2271c9bf869ce98d7820e283b9a1e97e5645e8eb.png';
import jenniePromoImage from 'figma:asset/a5f09636f0b6f7bc4d1c00d30ae464f7b5f0ae2d.png';
import jennieCollectionImage from 'figma:asset/8e8b1ab7f96341046ffc7a3b9e68131673e2cd90.png';

export function SidebarWireframe() {
  // Create an array of images for the slider with the four provided images
  const sliderImages = [
    {
      src: jenniePortraitImage,
      alt: "Jennie from Blackpink in black and white portrait photo with intense gaze"
    },
    {
      src: jenniePromoImage, 
      alt: "Jennie from Blackpink in black and white photo wearing Adidas Originals zip jacket with 3-Stripes"
    },
    {
      src: jennieCollectionImage,
      alt: "Jennie modeling Adidas Originals tube top and zip jacket with classic 3-Stripes detailing"
    },
    {
      src: jennieVideoImage,
      alt: "Jennie from Blackpink in black Adidas Originals tracksuit with 3-Stripes detailing in casual pose"
    }
  ];

  return (
    <aside className="w-80 bg-sidebar border-r border-sidebar-border h-screen overflow-y-auto">
      <div className="p-6 space-y-8">
        {/* Promotional Image Section */}
        <div className="space-y-4">
          <h2
            className="text-sidebar-foreground tracking-wide"
            aria-label="Jennie from Blackpink featuring in Adidas Originals campaign"
          >
            Jennie x Adidas Originals
          </h2>

          <div className="relative rounded-2xl overflow-hidden ring-1 ring-sidebar-border">
            <div className="w-full aspect-[4/3]">
              <img
                src={jennieVideoImage}
                alt="Jennie from Blackpink in black Adidas Originals tracksuit with distinctive 3-Stripes detailing sitting in casual pose"
                className="w-full h-full object-cover"
              />
            </div>
          </div>

          <p className="text-sidebar-foreground leading-relaxed">
            Jennie from South Korean girl group, Blackpink, styled in classic Adidas Originals: tube top, zip jacket, and flared pants with 3-Stripes detailing.
          </p>
        </div>

        {/* Featured Collection Slider Section */}
        <div className="space-y-4">
          <h2 className="text-sidebar-foreground tracking-wide">
            Jennie x Adidas Collection
          </h2>

          <Card className="ring-1 ring-sidebar-border rounded-xl overflow-hidden">
            <CardContent className="p-4 space-y-4">
              <Carousel className="w-full">
                <CarouselContent>
                  {sliderImages.map((image, index) => (
                    <CarouselItem key={index}>
                      <div className="w-full aspect-[3/4] rounded-xl overflow-hidden bg-muted">
                        <img
                          src={image.src}
                          alt={image.alt}
                          className="w-full h-full object-cover"
                        />
                      </div>
                    </CarouselItem>
                  ))}
                </CarouselContent>
                <CarouselPrevious className="left-2" />
                <CarouselNext className="right-2" />
              </Carousel>

              <div className="space-y-3">
                <p className="text-sidebar-foreground leading-relaxed">
                  Discover Jennie's signature style with this exclusive Adidas Originals collection. Featuring classic sportswear silhouettes with modern street fashion influence, perfect for expressing individual style and confidence.
                </p>

                <Button className="w-full bg-sidebar-primary text-sidebar-primary-foreground hover:bg-sidebar-primary/90 uppercase py-2 rounded-md">
                  Shop Collection
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </aside>
  );
}

import { Button } from './ui/button';

export function JennieOverlay() {
  return (
    <div
      className="absolute inset-y-0 left-0 w-1/2 flex flex-col justify-center px-8 bg-black bg-opacity-50"
      aria-labelledby="jennie-headline"
      role="region"
    >
      <h1
        id="jennie-headline"
        className="text-5xl md:text-6xl font-bold text-white uppercase leading-tight"
      >
        Athleisure Originals
      </h1>
      <h2 className="mt-2 text-2xl md:text-3xl font-semibold text-white uppercase">
        Jennie × Adidas
      </h2>
      <p className="mt-4 text-base md:text-lg text-white">
        Inspired by Jennie. Mix-and-Match Athleisure.
      </p>
      <Button className="mt-6 w-max bg-sidebar-primary text-sidebar-primary-foreground hover:bg-sidebar-primary/90 uppercase py-2 px-6 rounded-md">
        Shop the Collection
      </Button>
    </div>
  );
}



