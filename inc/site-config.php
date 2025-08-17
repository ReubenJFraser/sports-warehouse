
<?php
return [
  
  // — Intro slide (non‐brand, non‐demographic) —
  'homepage' => [
    'slides' => [
      [
      'banner'   => 'images/AdobeStock_414079274-soccer_ball_and_field.jpeg',
      'alt'      => 'Soccer ball on a stadium pitch',
      'ariaText' => 'View our brand new range of sports balls. Shop now.',
      'videoKey' => null, // no video for this slide
      ],
    ],
    'videos' => [], // leave empty
  ],

  // Global plus-size page (aggregate across all brands)
  'plus_size' => [
    // 1) define each hero‐slide: banner + alt + aria text + the key of its video
    'slides' => [
      [
      'banner'    => 'images/plus_size/banners/body-positive-sportswomen-hugging-smiling.jpeg',
      'alt'       => 'Body positive sportswomen hugging and smiling',
      'ariaText'  => 'Celebrate body positivity with our plus-size activewear. Shop now.',
      'videoKey'  => 'mom_daughter',
      ],
      [
      'banner'    => 'images/plus_size/banners/curvy-woman-boxing-personal-trainer.jpeg',
      'alt'       => 'Curvy woman boxing with personal trainer',
      'ariaText'  => 'Feel empowered in our plus-size activewear. Shop now.',
      'videoKey'  => 'mom_daughter',
      ],
      [
      'banner'    => 'images/plus_size/banners/gym.jpeg',
      'alt'       => 'Plus-size woman working out at the gym',
      'ariaText'  => 'Feel strong and confident in our plus-size gym wear. Shop now.',
      'videoKey'  => 'mom_daughter',
      ],
    ],

    // 2) define your actual video asset, keyed by videoKey
    'videos' => [
    'mom_daughter' => [
      'src'       => 'images/plus_size/videos/mom-daughter.mp4',
      'ariaLabel' => 'Plus-size mother shopping online with the help of her daughter for activewear',
        ],
    ],
],

  // Adidas brand configuration
  'adidas' => [ 
    'slides' => [
      [
      'banner'   => 'images/adidas/banners/01-urban.png',
      'alt'      => 'Adidas Urban Collection',
      'ariaText' => 'Discover the Adidas Urban Collection. Shop the latest street styles.',
      'videoKey' => 'soccer_world_cup',
      ],
      [
      'banner'   => 'images/adidas/banners/men/01-urban.jpg',
      'alt'      => 'Adidas Men’s Urban Collection – Style 1',
      'ariaText' => 'Explore the men’s urban line from Adidas. Shop now.',
      'videoKey' => 'soccer_world_cup',
      ],
      [
      'banner'   => 'images/adidas/banners/men/02-urban.png',
      'alt'      => 'Adidas Men’s Urban Collection – Style 2',
      'ariaText' => 'Discover the latest men’s streetwear from Adidas. Shop now.',
      'videoKey' => 'soccer_world_cup',
      ],
      [
      'banner'   => 'images/adidas/banners/women/01.png',
      'alt'      => 'Adidas Women’s Collection',
      'ariaText' => 'Step into the Adidas women’s collection. Shop now.',
      'videoKey' => 'soccer_world_cup',
      ],
      [
      'banner'   => 'images/adidas/banners/kids/01.png',
      'alt'      => 'Adidas Kids’ Collection',
      'ariaText' => 'Gear up the little ones in Adidas Kids’ Collection. Shop now.',
      'videoKey' => 'soccer_world_cup',
      ],
    ],

    'videos' => [
    'soccer_world_cup' => [
      'src'       => 'images/adidas/video/soccer-world_cup.mp4',
      'ariaLabel' => 'Adidas Soccer World Cup video teaser',
      ],
    ],
  ],

  // Asics brand configuration
  'asics' => [
    'slides' => [
      [
      'banner'   => 'images/asics/banners/men/01-cross-country-running.jpg',
      'alt'      => 'Asics Cross-Country Running Collection',
      'ariaText' => 'Discover the Asics cross-country running collection. Shop now.',
      'videoKey' => 'paris_olympics',
      ],
    ],

    'videos' => [
    'paris_olympics' => [
      'src'       => 'images/asics/video/paris_olympics.mp4',
      'ariaLabel' => 'Asics Paris Olympics teaser featuring Australian athletes',
      ],
    ],
  ],

  // Intus brand configuration
  'intus' => [
    'slides' => [
      [
      'banner'   => 'images/intus/men/banner.png',
      'alt'      => 'Intus Men’s Collection',
      'ariaText' => 'Discover Intus men’s collection. Shop now.',
      'videoKey' => 'run_club',
      ],
    ],
    
    'videos' => [
    'run_club' => [
      'src'       => 'images/intus/men/video/run-club-launch.mp4',
      'ariaLabel' => 'Intus Run Club launch video',
      ],
    ],
  ],

  // Nike brand configuration
  'nike' => [
    'slides' => [
      [
      'banner'   => 'images/nike/banner-joyride.png',
      'alt'      => 'Nike Joyride Banner',
      'ariaText' => 'Experience Nike Joyride cushioning. Shop now.',
      'videoKey' => 'movement',
      ],
    ],

    'videos' => [
    'movement' => [
      'src'       => 'images/nike/video/movement.mp4',
      'ariaLabel' => 'Nike Movement video teaser',
      ],
    ],
  ],

  // Puma brand configuration
  'puma' => [
    'slides' => [
      [
      'banner'   => 'images/puma/banner-1.png',
      'alt'      => 'Puma Collection Banner',
      'ariaText' => 'Discover the latest from Puma. Shop now.',
      'videoKey' => 'dua_lipa',
      ],
    ],

    'videos' => [
    'dua_lipa' => [
      'src'       => 'images/puma/dua_lipa.mp4',
      'ariaLabel' => 'Puma x Dua Lipa campaign video',
      ],
    ],
  ],

  // Reebok brand configuration
  'reebok' => [
    'slides' => [
      [
      'banner'   => 'images/reebok/banner-1.png',
      'alt'      => 'Reebok Collection Banner',
      'ariaText' => 'Step into Reebok’s latest collection. Shop now.',
      'videoKey' => 'impact',
      ],
    ],

    'videos' => [
    'impact' => [
      'src'       => 'images/reebok/impact.mp4',
      'ariaLabel' => 'Reebok Impact video teaser',
      ],
    ],
  ],

  // Stax brand configuration  
  'stax' => [ 
    'slides' => [
      [
      'banner'    => 'images/Stax/banners/women/01-hero-images-collage.png',
      'alt'       => "Stax Women’s Collection",
      'ariaText'  => "Explore Stax women’s collection. Shop now.",
      'videoKey'  => 'tropical',  // reuse same video
      ],
      [
      'banner'    => 'images/Stax/banners/women/02-seamless-tones.png',
      'alt'       => 'Stax Tones Collection',
      'ariaText'  => 'Discover tonal perfection in Stax’s Tones collection. Shop now.',
      'videoKey'  => 'tropical',
      ],
      [
      'banner'    => 'images/stax/banners/women/plus-size/01-shorts.png',
      'alt'       => 'Stax Plus-Size Shorts',
      'ariaText'  => 'Find your perfect fit in our plus-size shorts. Feel confident and comfortable.',
      'videoKey'  => 'tropical',
      ],
      [
      'banner'    => 'images/stax/banners/women/plus-size/02-tights.png',
      'alt'       => 'Stax Plus-Size Tights',
      'ariaText'  => 'Embrace every curve with our plus-size tights. Designed to move with you.',
      'videoKey'  => 'tropical',
      ],
      [
      'banner'    => 'images/stax/banners/women/plus-size/03-long-sleeve.png',
      'alt'       => 'Stax Plus-Size Long Sleeve',
      'ariaText'  => 'Stay cozy and stylish in our plus-size long sleeve. Love the way you look.',
      'videoKey'  => 'tropical',
      ],
    ],

    'videos' => [
    'tropical' => [
      'src'       => 'images/stax/video/tropical.mp4',
      'ariaLabel' => 'Stax Tropical activewear teaser',
      ],
    'biker_shorts' => [
      'src'       => 'images/stax/video/biker_shorts-video.mp4',
      'ariaLabel' => 'Stax Biker Shorts in motion',
      ],
    'scoop_long_sleeve' => [
      'src'       => 'images/stax/video/scoop_long_sleeve-video.mp4',
      'ariaLabel' => 'Stax Scoop Long Sleeve in motion',
      ],
    'nandex_v_front_crop' => [
      'src'       => 'images/stax/video/nandex_v_front_crop.mp4',
      'ariaLabel' => 'Stax Nandex V-Front Crop in motion',
      ],
    ],
  ],

  // Under Armour brand configuration
  'underarmour' => [
    'slides' => [
      [
      'banner'   => 'images/underarmour/men/poly_tracksuit/01.png',
      'alt'      => 'Under Armour Poly Tracksuit',
      'ariaText' => 'Discover Under Armour Poly Tracksuit. Shop now.',
      'videoKey' => 'basketball',
      ],
      [
      'banner'   => 'images/underarmour/men/ua_storm_vanish_track_pants/01.png',
      'alt'      => 'Under Armour Storm Vanish Track Pants',
      'ariaText' => 'Explore Under Armour Storm Vanish Track Pants. Shop now.',
      'videoKey' => 'basketball',
      ],
    ],

    'videos' => [
    'basketball' => [
     'src'       => 'images/underarmour/video/basketball.mp4',
     'ariaLabel' => 'Under Armour basketball campaign video teaser',
      ],
    ],
  ],

  // Wilson brand configuration
'wilson' => [
  'slides' => [
      [
      'banner'   => 'images/wilson/men/parkside_crew/01.png',
      'alt'      => 'Wilson Parkside Crew Collection',
      'ariaText' => 'Discover the Wilson Parkside Crew collection. Shop now.',
      'videoKey' => 'feel_in_flow',
      ],
      [
      'banner'   => 'images/wilson/men/tournament_shorts/01.png',
      'alt'      => 'Wilson Tournament Shorts Collection',
      'ariaText' => 'Gear up in Wilson Tournament Shorts. Shop now.',
      'videoKey' => 'feel_in_flow',
      ],
      [
      'banner'   => 'images/wilson/unisex/01.png',
      'alt'      => 'Wilson Unisex Collection',
      'ariaText' => 'Explore the Wilson Unisex collection. Shop now.',
      'videoKey' => 'feel_in_flow',
      ],
    ],

    'videos' => [
    'feel_in_flow' => [
     'src'       => 'images/wilson/unisex/feel_in_flow.mp4',
     'ariaLabel' => 'Wilson “Feel in Flow” campaign video teaser',
      ],
    'french_clay' => [
     'src'       => 'images/wilson/unisex/french_clay.mp4',
     'ariaLabel' => 'Wilson French Clay court campaign video teaser',
      ],
    ],
  ],
 
];

// Build demographics automatically:
$demographics = ['men','women','kids'];
foreach ($demographics as $demo) {
  $slides = [];
  $videos = [];
  foreach ($raw as $brandKey => $conf) {
    if (!isset($conf['slides'])) continue;
    foreach ($conf['slides'] as $slide) {
      if (stripos($slide['banner'], "/{$demo}/") !== false) {
        $slides[] = $slide;
        // if there's a teaser video:
        if ($slide['videoKey'] && isset($conf['videos'][$slide['videoKey']])) {
          $videos[$slide['videoKey']] = $conf['videos'][$slide['videoKey']];
        }
      }
    }
  }
  $raw[$demo] = [
    'slides'=> $slides,
    'videos'=> array_values($videos),
  ];
}

return $raw;



