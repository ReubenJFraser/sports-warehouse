import { ImageWithFallback } from './figma/ImageWithFallback';
import { Button } from './ui/button';
import { Card, CardContent } from './ui/card';
import { Carousel, CarouselContent, CarouselItem, CarouselNext, CarouselPrevious } from './ui/carousel';
import blackHyperglamFull from 'figma:asset/cee670bb544c3ad4229f31fc674c8054ef30c465.png';
import blackHyperglamLeggings from 'figma:asset/e51cc6b192a00be0c7a114a891b5626dcf21eaab.png';
import mauveHyperglamSet from 'figma:asset/e15b796ce8e6355830153097033456394690eddb.png';

export function SidebarWireframe() {
  // Black variant images - using both unique black images
  const blackVariantImages = [
    {
      src: blackHyperglamFull,
      alt: "Woman in black Adidas Hyperglam long sleeve crop top and full-length leggings with white 3-Stripes detailing in dynamic pose with hand on head"
    },
    {
      src: blackHyperglamLeggings,
      alt: "Close-up detail of black Adidas Hyperglam high-waisted leggings with signature white 3-Stripes and trefoil logo, showing athletic sneakers"
    }
  ];

  // Mauve variant images - using the mauve activewear image
  const mauveVariantImages = [
    {
      src: mauveHyperglamSet,
      alt: "Woman in mauve Adidas Hyperglam long sleeve crop top and full-length leggings with white 3-Stripes detailing in athletic pose"
    },
    {
      src: mauveHyperglamSet,
      alt: "Detail view of mauve Hyperglam crop top showing ribbed texture and athletic fit with signature branding"
    }
  ];

  return (
    <aside className="w-80 bg-sidebar border-r border-sidebar-border h-screen overflow-y-auto">
      <div className="p-6 space-y-6">
        {/* Header Section */}
        <div className="space-y-4">
          <h2 className="text-sidebar-foreground tracking-wide">
            Adidas Hyperglam Collection
          </h2>
        </div>

        {/* Black Variant Carousel */}
        <div className="space-y-3">
          <h3 className="text-sidebar-foreground tracking-wide">Black Variant</h3>
          <Card className="ring-1 ring-sidebar-border rounded-xl overflow-hidden">
            <CardContent className="p-3">
              <Carousel className="w-full">
                <CarouselContent>
                  {blackVariantImages.map((image, index) => (
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
            </CardContent>
          </Card>
        </div>

        {/* Mauve Variant Carousel */}
        <div className="space-y-3">
          <h3 className="text-sidebar-foreground tracking-wide">Mauve Variant</h3>
          <Card className="ring-1 ring-sidebar-border rounded-xl overflow-hidden">
            <CardContent className="p-3">
              <Carousel className="w-full">
                <CarouselContent>
                  {mauveVariantImages.map((image, index) => (
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
            </CardContent>
          </Card>
        </div>

        {/* Product Description */}
        <div className="space-y-4">
          <div className="space-y-3">
            <p className="text-sidebar-foreground leading-relaxed">
              🖤Hyperglam Long Sleeve Crop Top + Full-Length Leggings
            </p>
            <p className="text-sidebar-foreground leading-relaxed">
              Strike a balance between fierce and feminine in the Adidas Hyperglam set. The long sleeve crop top and high-rise leggings move with you—from studio to street—thanks to sleek performance fabric, sculpting seams, and a bold Hyperglam hue, available in black or mauve. Signature 3-Stripes details flash along the arms and hips, giving this look an edge that's equal parts athletic and stylish
            </p>
          </div>

          {/* Notes Section */}
          <div className="bg-sidebar-accent rounded-lg p-4 space-y-3">
            <p className="text-sidebar-foreground leading-relaxed">
              📌 Notes:
            </p>
            <ul className="text-sidebar-foreground leading-relaxed space-y-1">
              <li>• Best for: Yoga, pilates, dance, or high-energy movement</li>
              <li>• Sold as a set only</li>
              <li>• Midriff-baring top and curve-sculpted leggings for a coordinated silhouette</li>
            </ul>
          </div>

          <Button className="w-full bg-sidebar-primary text-sidebar-primary-foreground hover:bg-sidebar-primary/90 uppercase py-2 rounded-md">
            Shop Hyperglam Set
          </Button>
        </div>
      </div>
    </aside>
  );
}
