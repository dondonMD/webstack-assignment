# WordPress Setup Notes

## Required Plugins

- Elementor
- WPGraphQL

## Local Services

The local stack is defined in [wordpress/docker-compose.yml](/C:/Users/modis/Desktop/take_home_assignment_Modisa/wordpress/docker-compose.yml).

Services:

- `db`: MySQL 8
- `wordpress`: Apache + WordPress
- `wpcli`: long-running WP-CLI container for setup and seed operations

## Automated Setup

Run:

```powershell
.\wordpress\scripts\setup-wordpress.ps1
```

The script handles:

- WordPress core install
- permalink updates
- plugin installation and activation
- page creation
- menu scaffolding
- seeded posts and featured images

## Seed Content

Seeded post data lives in:

- [wordpress/seed/posts.json](/C:/Users/modis/Desktop/take_home_assignment_Modisa/wordpress/seed/posts.json)
- [wordpress/seed/images](/C:/Users/modis/Desktop/take_home_assignment_Modisa/wordpress/seed/images)

## Manual Admin Checks

After setup:

1. Log into `http://localhost:8090/wp-admin`.
2. Confirm `Settings > Permalinks` uses `Post name`.
3. Confirm `Plugins` shows Elementor and WPGraphQL as active.
4. Confirm `Posts` contains the six seeded demo posts.
5. Confirm each post has a featured image, excerpt, author, and date.
6. Confirm `Pages` contains Home, About, and Contact.

## GraphQL Endpoint

Expected local endpoint:

```text
http://localhost:8090/graphql
```
