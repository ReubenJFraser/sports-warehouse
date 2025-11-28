export function SidebarWireframe() {
  return {
    <aside
      className="w-80 bg-sidebar border-r border-sidebar-border p-6 space-y-8"
      aria-label="Sidebar — promotional video and featured product"
    >
      {/* Video Section */}
      <div className="space-y-4">
        <h2
          className="text-sidebar-foreground tracking-wide font-semibold"
          aria-label={`Adidas "You Got This" World Cup campaign video, featuring Beckham, Messi, and Bellingham`}
        >
          Adidas "You Got This" World Cup Campaign
        </h2>

        <div className="relative rounded-2xl overflow-hidden ring-1 ring-sidebar-border">
          <video
            id="campaign-video"
            className="w-full h-48 md:h-64 object-cover"
            poster="https://images.unsplash.com/photo-1611532736597-de2d4265fba3?w=400&h=240&fit=crop"
            controls
          >
            <source src="https://cdn.yoursite.com/videos/you-got-this.mp4" type="video/mp4" />
            <source src="https://cdn.yoursite.com/videos/you-got-this.webm" type="video/webm" />
            Your browser does not support the video tag.
          </video>
        </div>
      </div>

      {/* Featured Product Section */}
      <div className="space-y-4">
        <h2
          className="text-sidebar-foreground tracking-wide font-semibold"
          aria-label="Featured product: Adidas Originals Campus 00s"
        >
          Adidas Originals: Campus 00s
        </h2>

        <Card className="ring-1 ring-sidebar-border rounded-xl overflow-hidden">
          <CardContent className="p-4 space-y-4">
            <div className="w-full h-52 md:h-64 rounded-xl overflow-hidden bg-muted">
              <img
                src={campusImage}
                alt="Adidas Originals Campus 00s sneakers in dark gray suede with white 3-Stripes"
                className="w-full h-full object-cover"
              />
            </div>

            <div className="space-y-3">
              <p className="text-sidebar-foreground leading-relaxed">
                Soft suede meets street-ready swagger in these Campus 00s from adidas Originals—chunky gum soles and laces, supersized 3-Stripes, and ’80s-inspired padding for off-duty cool.
              </p>

              <Button className="w-full bg-sidebar-primary text-sidebar-primary-foreground hover:bg-sidebar-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sidebar-primary py-2 rounded-md">
                Shop
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>
    </aside>
    };
