param(
    [Parameter(Mandatory = $true)]
    [string]$BaseUrl
)

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

Write-Host "Updating WordPress URLs to $BaseUrl ..."
Invoke-CheckedCommand -Command @(
    "docker", "compose", "-f", "wordpress/docker-compose.yml",
    "exec", "-T", "wpcli", "wp", "option", "update", "home", $BaseUrl
)
Invoke-CheckedCommand -Command @(
    "docker", "compose", "-f", "wordpress/docker-compose.yml",
    "exec", "-T", "wpcli", "wp", "option", "update", "siteurl", $BaseUrl
)
Invoke-CheckedCommand -Command @(
    "docker", "compose", "-f", "wordpress/docker-compose.yml",
    "exec", "-T", "wpcli", "wp", "rewrite", "flush", "--hard"
)

Write-Host "WordPress URL update complete."
