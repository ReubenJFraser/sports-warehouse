# Site Structure (3 Levels)

```
Folder PATH listing for volume OS
Volume serial number is 8ACC-9713
C:.
|   .env.example
|   .gitignore
|   category.php
|   currentstate.zip
|   db.php
|   demo-cards.html
|   example.php
|   hello.html
|   image-list.txt
|   index.php
|   item_orientations.csv
|   item_orientations_adidas.csv
|   product.php
|   README.md
|   search.php
|   site-structure.txt
|   SportsWarehouse_DeliberableB.zip
|   test_db.php
|   
+---.vscode
|       extensions.json
|       settings.json
|       tasks.json
|       
+---css
|   |   base.css
|   |   header.css
|   |   main.css
|   |   
|   \---components
|           button.css
|           card-layout.css
|           card-responsiveness.css
|           card.css
|           md3-overrides.css
|           pill.css
|           themes.css
|           
+---db
|       sportswh_dump.sql
|       sportswh_dump_sanitized.sql
|       
+---docs
|       Category-Sync-Runbook.md
|       
+---images
|   |   adobestock_414079274-soccer_ball_and_field.jpeg
|   |   adobestock_536942588-ellipse.png
|   |   
|   +---brands
|   |   +---adidas
|   |   |   +---banners
|   |   |   |   |   01-urban.png
|   |   |   |   |   
|   |   |   |   +---kids
|   |   |   |   |       01.png
|   |   |   |   |       
|   |   |   |   +---men
|   |   |   |   |   \---urban
|   |   |   |   |           01.png
|   |   |   |   |           02.png
|   |   |   |   |           03.png
|   |   |   |   |           
|   |   |   |   \---women
|   |   |   |       |   streetwear.png
|   |   |   |       |   
|   |   |   |       \---blackpink
|   |   |   |           \---jennie
|   |   |   |               \---superstar-the_original
|   |   |   |                       01.png
|   |   |   |                       02.png
|   |   |   |                       03.png
|   |   |   |                       04.png
|   |   |   |                       05.png
|   |   |   |                       06.png
|   |   |   |                       
|   |   |   +---kids
|   |   |   |   \---marvel-spider_man
|   |   |   |       +---backpack
|   |   |   |       |       01.png
|   |   |   |       |       02.png
|   |   |   |       |       03.png
|   |   |   |       |       04.png
|   |   |   |       |       05.png
|   |   |   |       |       
|   |   |   |       +---banner
|   |   |   |       |   \---global_disney_marvel_superheroes-launch
|   |   |   |       |           01.png
|   |   |   |       |           02.png
|   |   |   |       |           
|   |   |   |       +---t-shirt
|   |   |   |       |       01.png
|   |   |   |       |       02.png
|   |   |   |       |       
|   |   |   |       +---tracksuit
|   |   |   |       |       01.png
|   |   |   |       |       02.png
|   |   |   |       |       03.png
|   |   |   |       |       04.png
|   |   |   |       |       
|   |   |   |       \---trainers-light_up
|   |   |   |               01.png
|   |   |   |               
|   |   |   +---men
|   |   |   |   +---3-stripes
|   |   |   |   |   \---running_tee
|   |   |   |   |           01.png
|   |   |   |   |           
|   |   |   |   +---campas_sneakers
|   |   |   |   |       01.png
|   |   |   |   |       02.png
|   |   |   |   |       03.png
|   |   |   |   |       04.png
|   |   |   |   |       05.png
|   |   |   |   |       06.png
|   |   |   |   |       
|   |   |   |   \---poly_linear_full_zip_hoodie_and_pants
|   |   |   |           01.png
|   |   |   |           02.png
|   |   |   |           03.png
|   |   |   |           04.png
|   |   |   |           05.png
|   |   |   |           06.png
|   |   |   |           07.png
|   |   |   |           08.png
|   |   |   |           09.png
|   |   |   |           
|   |   |   +---other
|   |   |   |       UEFA_Euro16-Top_Glider_Ball.png
|   |   |   |       
|   |   |   +---videos
|   |   |   |       jennie-blackpink.mp4
|   |   |   |       tubetop.mp4
|   |   |   |       world_cup.mp4
|   |   |   |       
|   |   |   \---women
|   |   |       \---3-stripes
|   |   |           +---booty_shorts
|   |   |           |       01.png
|   |   |           |       02.png
|   |   |           |       03.png
|   |   |           |       04.png
|   |   |           |       05.png
|   |   |           |       
|   |   |           +---flared_leggings
|   |   |           |       01.png
|   |   |           |       02.png
|   |   |           |       
|   |   |           +---hyperglam_long_sleeve_crop_top_and_full-length_leggings
|   |   |           |       01.png
|   |   |           |       02.png
|   |   |           |       03.png
|   |   |           |       04.png
|   |   |           |       05.png
|   |   |           |       
|   |   |           +---powerreact_training-medium_support-sports_bra
|   |   |           |       01.png
|   |   |           |       02.png
|   |   |           |       03.png
|   |   |           |       04.png
|   |   |           |       
|   |   |           +---tights
|   |   |           |       01.png
|   |   |           |       02.png
|   |   |           |       
|   |   |           \---tube_top
|   |   |                   01.png
|   |   |                   02.png
|   |   |                   03.png
|   |   |                   04.png
|   |   |                   
|   |   +---asics
|   |   |   +---banners
|   |   |   |   \---men
|   |   |   |           01-cross-country-running.jpg
|   |   |   |           
|   |   |   +---men
|   |   |   |   \---gel_training
|   |   |   |       +---shorts
|   |   |   |       |       01.png
|   |   |   |       |       02.png
|   |   |   |       |       03.png
|   |   |   |       |       04.png
|   |   |   |       |       05.png
|   |   |   |       |       
|   |   |   |       \---Zip-Track_Top
|   |   |   |               01.png
|   |   |   |               02.png
|   |   |   |               03.png
|   |   |   |               04.png
|   |   |   |               05.png
|   |   |   |               
|   |   |   +---unisex
|   |   |   |   +---running_shoes
|   |   |   |   |       kayano_26.png
|   |   |   |   |       
|   |   |   |   \---soccer_boots
|   |   |   |           gel_lethal_tigreor_8_IT.png
|   |   |   |           
|   |   |   \---video
|   |   |           paris_olympics.mp4
|   |   |           
|   |   +---asos
|   |   |   \---4504-curve
|   |   |       \---sports_bra
|   |   |           \---high_support
|   |   |               \---zip-front-adjustable-straps
|   |   |                       01.png
|   |   |                       02.png
|   |   |                       03.png
|   |   |                       
|   |   +---aybl
|   |   |   \---adapt_seamless
|   |   |       +---shorts
|   |   |       |       01.png
|   |   |       |       02.png
|   |   |       |       03.png
|   |   |       |       04.png
|   |   |       |       05.png
|   |   |       |       06.png
|   |   |       |       
|   |   |       \---sports_bra
|   |   |               01.png
|   |   |               02.png
|   |   |               03.png
|   |   |               04.png
|   |   |               05.png
|   |   |               06.png
|   |   |               
|   |   +---designer
|   |   |   +---kate_galliano
|   |   |   |   \---bodysuit
|   |   |   |       \---bubblegum
|   |   |   |               01.png
|   |   |   |               02.png
|   |   |   |               
|   |   |   \---lisa_trujillo
|   |   |       \---kids
|   |   |           |   01-activewear.jpg
|   |   |           |   
|   |   |           \---banner
|   |   |                   01.png
|   |   |                   02.png
|   |   |                   03.png
|   |   |                   
|   |   +---nike
|   |   |   |   banner-joyride.png
|   |   |   |   
|   |   |   +---kids
|   |   |   |   \---girls
|   |   |   |       \---futura
|   |   |   |           \---air
|   |   |   |               \---pink_and_white
|   |   |   |                       01.png
|   |   |   |                       02.png
|   |   |   |                       
|   |   |   +---other
|   |   |   |       600ml_waterbottle.png
|   |   |   |       
|   |   |   \---women
|   |   |       +---running
|   |   |       |   \---swift_uv-12_zip_top
|   |   |       |           01.png
|   |   |       |           02.png
|   |   |       |           03.png
|   |   |       |           04.png
|   |   |       |           05.png
|   |   |       |           06.png
|   |   |       |           07.png
|   |   |       |           
|   |   |       +---swoosh
|   |   |       |   \---sports_bra
|   |   |       |       \---medium-support-padded
|   |   |       |               01.png
|   |   |       |               02.png
|   |   |       |               03.png
|   |   |       |               04.png
|   |   |       |               05.png
|   |   |       |               
|   |   |       +---training
|   |   |       |   +---full-zip_top Tracksuit
|   |   |       |   |       01.png
|   |   |       |   |       02.png
|   |   |       |   |       03.png
|   |   |       |   |       04.png
|   |   |       |   |       05.png
|   |   |       |   |       06.png
|   |   |       |   |       
|   |   |       |   \---pro_mesh-3inch_shorts
|   |   |       |           01.png
|   |   |       |           02.png
|   |   |       |           03.png
|   |   |       |           04.png
|   |   |       |           05.png
|   |   |       |           
|   |   |       +---video
|   |   |       |       movement.mp4
|   |   |       |       
|   |   |       \---zenvy
|   |   |           +---leggings-high-waisted-flared
|   |   |           |   |   01.png
|   |   |           |   |   02.png
|   |   |           |   |   03.png
|   |   |           |   |   04.png
|   |   |           |   |   
|   |   |           |   \---green
|   |   |           |           05.mp4
|   |   |           |           
|   |   |           \---leggings-high_waisted-full_length
|   |   |               |   01.png
|   |   |               |   02.png
|   |   |               |   
|   |   |               \---purple
|   |   +---other
|   |   |       protec-skate_helmet.png
|   |   |       sting-armaplus-boxing_gloves-T3.png
|   |   |       
|   |   +---pangaia
|   |   |   \---stretch-compressive-volcanic_grey
|   |   |       +---leggings
|   |   |       |       01.png
|   |   |       |       02.png
|   |   |       |       03.png
|   |   |       |       04.png
|   |   |       |       
|   |   |       \---sports_bra
|   |   |               01.png
|   |   |               02.png
|   |   |               03.png
|   |   |               04.png
|   |   |               
|   |   +---puma
|   |   |   |   banner-1.png
|   |   |   |   dua_lipa.mp4
|   |   |   |   
|   |   |   \---unisex
|   |   |       \---training_shoes
|   |   |           \---rs-x³_spectra
|   |   |                   01.png
|   |   |                   02.png
|   |   |                   
|   |   +---reebok
|   |   |   |   banner-1.png
|   |   |   |   impact.mp4
|   |   |   |   
|   |   |   \---unisex
|   |   |       \---training_shoes
|   |   |           \---nano_x3
|   |   |                   black_court_brown.png
|   |   |                   core_black-neon_cherry.png
|   |   |                   pure_white.png
|   |   |                   
|   |   +---stax
|   |   |   +---banners
|   |   |   |   \---women
|   |   |   |           01-hero-images-collage.png
|   |   |   |           02-seamless-tones.png
|   |   |   |           
|   |   |   +---video
|   |   |   |   |   tropical.mp4
|   |   |   |   |   
|   |   |   |   \---melbourne
|   |   |   |           large.mp4
|   |   |   |           podolyano-kyah_richards.png
|   |   |   |           run-club-launch.mp4
|   |   |   |           small.mp4
|   |   |   |           
|   |   |   \---women
|   |   |       +---airlyte
|   |   |       |   +---active_zip_Jacket
|   |   |       |   |       01.png
|   |   |       |   |       02.png
|   |   |       |   |       03.png
|   |   |       |   |       04.png
|   |   |       |   |       05.png
|   |   |       |   |       06.png
|   |   |       |   |       07.png
|   |   |       |   |       
|   |   |       |   +---backless_playsuit
|   |   |       |   |       01.png
|   |   |       |   |       02.png
|   |   |       |   |       03.png
|   |   |       |   |       04.png
|   |   |       |   |       05.png
|   |   |       |   |       06.png
|   |   |       |   |       07.png
|   |   |       |   |       
|   |   |       |   +---full-length_tights
|   |   |       |   |       01.png
|   |   |       |   |       02.png
|   |   |       |   |       
|   |   |       |   +---mini_biker_shorts
|   |   |       |   |       01.png
|   |   |       |   |       02.png
|   |   |       |   |       
|   |   |       |   +---ruched_tank
|   |   |       |   |       01.png
|   |   |       |   |       02.png
|   |   |       |   |       03.png
|   |   |       |   |       04.png
|   |   |       |   |       05.png
|   |   |       |   |       06.png
|   |   |       |   |       07.png
|   |   |       |   |       08.png
|   |   |       |   |       09.png
|   |   |       |   |       10.png
|   |   |       |   |       11.png
|   |   |       |   |       12.png
|   |   |       |   |       
|   |   |       |   \---wrap-longsleeve
|   |   |       |           01.png
|   |   |       |           02.png
|   |   |       |           03.png
|   |   |       |           
|   |   |       +---nandex
|   |   |       |   +---adira_crop
|   |   |       |   |       01.png
|   |   |       |   |       02.png
|   |   |       |   |       03.png
|   |   |       |   |       04.png
|   |   |       |   |       
|   |   |       |   +---flex_crop
|   |   |       |   |       01.png
|   |   |       |   |       02.png
|   |   |       |   |       03.png
|   |   |       |   |       04.png
|   |   |       |   |       
|   |   |       |   +---strappy_crop
|   |   |       |   |       01.png
|   |   |       |   |       02.png
|   |   |       |   |       03.png
|   |   |       |   |       04.png
|   |   |       |   |       
|   |   |       |   +---venus_skirt
|   |   |       |   |       01.mp4
|   |   |       |   |       02.png
|   |   |       |   |       03.png
|   |   |       |   |       04.png
|   |   |       |   |       05.png
|   |   |       |   |       06.png
|   |   |       |   |       07.png
|   |   |       |   |       08.png
|   |   |       |   |       
|   |   |       |   \---v_front-crop
|   |   |       |           01.png
|   |   |       |           02.png
|   |   |       |           03.png
|   |   |       |           04.png
|   |   |       |           05.png
|   |   |       |           06.png
|   |   |       |           
|   |   |       \---second_left_seamless_campaign
|   |   |           +---biker_shorts
|   |   |           |       01.png
|   |   |           |       02.png
|   |   |           |       03.png
|   |   |           |       04.png
|   |   |           |       05.png
|   |   |           |       06.mp4
|   |   |           |       
|   |   |           +---bralette
|   |   |           |       01.png
|   |   |           |       02.png
|   |   |           |       03.png
|   |   |           |       04.png
|   |   |           |       
|   |   |           \---scoop_long_sleeve
|   |   |                   01.png
|   |   |                   02.png
|   |   |                   03.png
|   |   |                   04.png
|   |   |                   05.png
|   |   |                   06.mp4
|   |   |                   
|   |   +---underarmour
|   |   |   +---kids
|   |   |   |   \---girls
|   |   |   |       \---sports_bra
|   |   |   |           \---ua_crossback
|   |   |   |                   01.png
|   |   |   |                   02.png
|   |   |   |                   03.png
|   |   |   |                   04.png
|   |   |   |                   05.png
|   |   |   |                   
|   |   |   +---men
|   |   |   |   +---poly_tracksuit
|   |   |   |   |       01.png
|   |   |   |   |       02.png
|   |   |   |   |       03.png
|   |   |   |   |       04.png
|   |   |   |   |       05.png
|   |   |   |   |       06.png
|   |   |   |   |       07.png
|   |   |   |   |       
|   |   |   |   \---ua_storm_vanish_track_pants
|   |   |   |           1.png
|   |   |   |           2.png
|   |   |   |           3.png
|   |   |   |           4.png
|   |   |   |           5.png
|   |   |   |           
|   |   |   +---video
|   |   |   |       basketball.mp4
|   |   |   |       
|   |   |   \---women
|   |   |       \---wordmark_strappy_sports_bra
|   |   |               01.png
|   |   |               02.png
|   |   |               03.png
|   |   |               04.png
|   |   |               05.png
|   |   |               
|   |   \---wilson
|   |       +---men
|   |       |   |   chico_lachowski.mp4
|   |       |   |   
|   |       |   \---active_set
|   |       |       +---parkside_crew
|   |       |       |       01.png
|   |       |       |       02.png
|   |       |       |       03.png
|   |       |       |       04.png
|   |       |       |       
|   |       |       +---tournament_shirt
|   |       |       |       01.png
|   |       |       |       02.png
|   |       |       |       03.png
|   |       |       |       04.png
|   |       |       |       05.png
|   |       |       |       
|   |       |       \---tournament_shorts
|   |       |               01.png
|   |       |               02.png
|   |       |               03.png
|   |       |               04.png
|   |       |               05.png
|   |       |               
|   |       +---unisex
|   |       |   |   01.png
|   |       |   |   02.png
|   |       |   |   03.png
|   |       |   |   04.png
|   |       |   |   05.png
|   |       |   |   feel_in_flow.mp4
|   |       |   |   french_clay.mp4
|   |       |   |   
|   |       |   \---rf_collection
|   |       |       \---pro_classic
|   |       |           \---01
|   |       |                   a.png
|   |       |                   b.png
|   |       |                   c.png
|   |       |                   d.png
|   |       |                   youtube.mp4
|   |       |                   
|   |       \---video
|   |               rf_01-pro_classic-youtube.mp4
|   |               rf_collection_by_wilson-06_secs-youtube.mp4
|   |               
|   +---icons
|   |   |   call_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.svg
|   |   |   close_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.svg
|   |   |   login_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.svg
|   |   |   menu_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.svg
|   |   |   production_quantity_limits_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.svg
|   |   |   search_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.svg
|   |   |   shopping_cart_24dp_FFFFFF_FILL0_wght400_GRAD0_opsz24.svg
|   |   |   
|   |   \---social_apps
|   |           Facebook_Logo_Secondary.svg
|   |           Instagram_Glyph_White.svg
|   |           Messenger_Icon_Secondary_White.svg
|   |           WhatsApp-Digital_Glyph_White.svg
|   |           
|   +---logos
|   |       01.png
|   |       02.png
|   |       adidas-currentColor.svg
|   |       AdobeStock_583381948 [Converted].svg
|   |       ally_fashion.html
|   |       asics-currentColor.svg
|   |       ChatGPT Image Jun 14, 2025, 08_03_34 PM.png
|   |       new_balance-currentColor.svg
|   |       nike-currentColor.svg
|   |       puma-currentColor.svg
|   |       reebok-currentColor.svg
|   |       self_portrait-ring_fadeout.png
|   |       shopping-bag-with-sw - powerpoint.svg
|   |       shopping-bag-with-sw.svg
|   |       sports-warehouse-favicon.ico
|   |       sports-warehouse-logo-sw-recommended_apple_size.png
|   |       sports-warehouse-logo-sw-stroke.svg
|   |       sports-warehouse-logo-sw.ico
|   |       sports-warehouse-logo-sw.png
|   |       sports-warehouse-logo.svg
|   |       sports_warehouse_circle_sw_white_ring_highlight.png
|   |       sports_warehouse_circle_with_sw.png
|   |       under_armour-currentColor.svg
|   |       wilson-currentColor.svg
|   |       
|   \---plus_size
|       +---banners
|       |       curvy-woman-boxing-personal-trainer.jpeg
|       |       gym.jpeg
|       |       women-body_positive-activewear-hugging-smiling.png
|       |       
|       \---video
|               mom-daughter.mp4
|               
+---inc
|   |   card-utils.php
|   |   footer.php
|   |   header.php
|   |   hero.php
|   |   product-grid.php
|   |   site-config.php
|   |   
|   \---sidebar
|       |   layout.php
|       |   
|       \---adidas
|               carousel-bundle-hero.php
|               hyperglam.php
|               jennie.php
|               kids.php
|               mens.php
|               
+---js
|       dark-mode.js
|       image-lazy.js
|       orientation-utils.js
|       site-ui.js
|       
+---scripts
|       run-category-sync.ps1
|       run-category-sync.sh
|       update-orientations.php
|       
\---sql
    \---category-sync
            apply.sql
            audit.sql
            dry-run.sql
```