# Elementor Build Notes

This assignment specifically requires the WordPress version to be built in Elementor. The repo seeds the content and pages, but the final homepage assembly is still completed in the WordPress admin UI.

## Homepage Structure To Recreate

### Header

- White background
- Logo aligned left
- Menu links: Home, About, Contact
- Rounded blue `Get Started` button aligned right

### Hero

- Wide banner section
- Soft background image feel
- Left copy block:
  - small intro line
  - large headline
  - primary button `Read More`
- Right visual block with laptop / editorial tech theme

### Posts Section

- Light-gray section background
- `Latest Posts` title
- 3-column desktop grid
- 2-column tablet grid
- 1-column mobile grid

### Post Card Anatomy

- featured image
- post title
- short excerpt
- footer row with author, date, and `Read More`
- subtle radius and soft shadow

## Practical Elementor Build Sequence

1. Set `Home` as the static front page.
2. Open `Home` in Elementor.
3. Create the header using a top section with three columns.
4. Build the hero as a two-column section with strong vertical padding.
5. Add a posts section below on a light-gray background.
6. Use Elementor Posts widget or Loop Grid, limited to `6` posts.
7. Match spacing and proportions to the mockup rather than over-decorating.
8. Verify responsive breakpoints manually.

## Reviewer Quality Checks

- The Elementor page should read as the same design language as the Next.js page.
- The posts grid must be limited to the latest 6 posts.
- Button hierarchy and spacing should stay close to the mockup.
- Card gutters should remain consistent across breakpoints.
