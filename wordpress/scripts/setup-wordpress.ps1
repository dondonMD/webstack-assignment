$ErrorActionPreference = "Stop"

function Invoke-CheckedCommand {
    param(
        [Parameter(Mandatory = $true)]
        [string[]]$Command
    )

    & $Command[0] $Command[1..($Command.Length - 1)]

    if ($LASTEXITCODE -ne 0) {
        throw "Command failed: $($Command -join ' ')"
    }
}

Invoke-CheckedCommand -Command @("docker", "info")

Write-Host "Starting WordPress services..."
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "up", "-d")

Write-Host "Waiting for WordPress container to accept CLI commands..."
Start-Sleep -Seconds 20

Write-Host "Installing WordPress core if needed..."
& docker compose -f wordpress/docker-compose.yml exec -T wpcli wp core is-installed
if ($LASTEXITCODE -ne 0) {
    Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "wp", "core", "install", "--url=http://localhost:8090", "--title=MyBlog Assignment", "--admin_user=admin", "--admin_password=admin", "--admin_email=admin@example.com", "--skip-email")
} else {
    Write-Host "WordPress core already installed."
}

Write-Host "Enabling SVG uploads for local seed media..."
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "bash", "-lc", "mkdir -p /var/www/html/wp-content/mu-plugins && cp /scripts/allow-svg-uploads.php /var/www/html/wp-content/mu-plugins/allow-svg-uploads.php")

Write-Host "Updating WordPress URLs..."
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "wp", "option", "update", "home", "http://localhost:8090")
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "wp", "option", "update", "siteurl", "http://localhost:8090")

Write-Host "Setting permalink structure..."
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "wp", "rewrite", "structure", "/%postname%/", "--hard")
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "wp", "rewrite", "flush", "--hard")

Write-Host "Installing required plugins..."
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "wp", "plugin", "install", "elementor", "--activate")
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "wp", "plugin", "install", "wp-graphql", "--activate")

Write-Host "Creating core pages..."
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "wp", "post", "create", "--post_type=page", "--post_title=Home", "--post_name=home", "--post_status=publish")
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "wp", "post", "create", "--post_type=page", "--post_title=About", "--post_name=about", "--post_status=publish")
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "wp", "post", "create", "--post_type=page", "--post_title=Contact", "--post_name=contact", "--post_status=publish")

Write-Host "Setting homepage..."
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "wp", "option", "update", "show_on_front", "page")
Invoke-CheckedCommand -Command @("docker", "compose", "-f", "wordpress/docker-compose.yml", "exec", "-T", "wpcli", "bash", "/scripts/seed-content.sh")

Write-Host "WordPress setup complete."
