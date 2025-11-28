import { ImageWithFallback } from './figma/ImageWithFallback';
import { Button } from './ui/button';
import { Card, CardContent } from './ui/card';
import spiderManPromoImage from 'figma:asset/ed7b73a3d7b72f1035768996be9c701ecd5ff016.png';
import trackSuitImage from 'figma:asset/74639d55cc9d63a73f05369a2c7ab3639b15bbe4.png';

export function SidebarWireframe() {
  return (
    <aside className="w-80 bg-sidebar border-r border-sidebar-border h-screen overflow-y-auto">
      <div className="p-6 space-y-8">
        {/* Promotional Image Section */}
        <div className="space-y-4">
          <h2
            className="text-sidebar-foreground tracking-wide"
            aria-label="Adidas x Marvel Spider-Man promotional campaign featuring young boy in superhero outfit"
          >
            Adidas x Marvel Spider-Man
          </h2>

          <div className="relative rounded-2xl overflow-hidden ring-1 ring-sidebar-border">
            <div className="w-full aspect-[4/3]">
              <img
                src={spiderManPromoImage}
                alt="Young boy modeling Adidas x Marvel Spider-Man outfit with red comic t-shirt and black tracksuit pants against blue sky background"
                className="w-full h-full object-cover"
              />
            </div>
          </div>
        </div>

        {/* Featured Product Section */}
        <div className="space-y-4">
          <h2 className="text-sidebar-foreground tracking-wide">
            Adidas x Marvel Spider-Man Collection
          </h2>

          <Card className="ring-1 ring-sidebar-border rounded-xl overflow-hidden">
            <CardContent className="p-4 space-y-4">
              <div className="w-full aspect-[711/1000] rounded-xl overflow-hidden bg-muted">
                <img
                  src={trackSuitImage}
                  alt="Boy and girl wearing Adidas x Marvel Spider-Man tracksuit collection with black jackets and matching pants"
                  className="w-full h-full object-cover"
                />
              </div>

              <div className="space-y-4">
                <p className="text-sidebar-foreground leading-relaxed">
                  Swing into action with the ultimate superhero outfit from adidas x Marvel. This 4-piece set includes the Spider-Man tracksuit, a bold Spider-Man t-shirt, matching trainers with flashing lights, and a backpack.
                </p>

                <div className="bg-sidebar-accent rounded-lg p-4 space-y-2">
                  <h3 className="text-sidebar-foreground tracking-wide">Special Offer:</h3>
                  <p className="text-sidebar-foreground leading-relaxed">
                    Buy both the Tracksuit and T-Shirt and get 50% OFF the Light-Up Trainers and/or Backpack. Complete the full heroic look at half the price—gear up and save big!
                  </p>
                </div>

                <Button className="w-full bg-sidebar-primary text-sidebar-primary-foreground hover:bg-sidebar-primary/90 uppercase py-2 rounded-md">
                  Shop
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </aside>
  );
}







