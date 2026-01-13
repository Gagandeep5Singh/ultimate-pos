# PowerShell Script to Push Changes to GitHub
# Usage: Right-click and "Run with PowerShell" or run: .\push-to-github.ps1

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Push Changes to GitHub" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Change to project directory
Set-Location "c:\xampp\htdocs\UltimatePOS\public"

# Check if Git is initialized
if (-not (Test-Path .git)) {
    Write-Host "ERROR: Git is not initialized in this directory!" -ForegroundColor Red
    Write-Host "Please run: git init" -ForegroundColor Yellow
    pause
    exit
}

# Show current status
Write-Host "Checking Git status..." -ForegroundColor Yellow
git status --short

Write-Host ""
$hasChanges = git diff --quiet --exit-code; $hasStaged = git diff --cached --quiet --exit-code

if ($LASTEXITCODE -ne 0 -or -not $hasStaged) {
    Write-Host "Changes detected!" -ForegroundColor Green
    Write-Host ""
    
    # Ask user for commit message
    $commitMessage = Read-Host "Enter commit message (or press Enter for default)"
    
    if ([string]::IsNullOrWhiteSpace($commitMessage)) {
        $timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
        $commitMessage = "Update files - $timestamp"
    }
    
    Write-Host ""
    Write-Host "Staging all changes..." -ForegroundColor Yellow
    git add .
    
    Write-Host "Committing changes..." -ForegroundColor Yellow
    git commit -m $commitMessage
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "Pushing to GitHub..." -ForegroundColor Yellow
        git push origin main
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host ""
            Write-Host "========================================" -ForegroundColor Green
            Write-Host "  SUCCESS! Changes pushed to GitHub" -ForegroundColor Green
            Write-Host "========================================" -ForegroundColor Green
            Write-Host ""
            Write-Host "Next step: Go to Hostinger Control Panel" -ForegroundColor Cyan
            Write-Host "  → Advanced → GIT → Click 'Deploy' button" -ForegroundColor Cyan
            Write-Host ""
        } else {
            Write-Host ""
            Write-Host "ERROR: Failed to push to GitHub!" -ForegroundColor Red
            Write-Host "Check your GitHub credentials or network connection." -ForegroundColor Yellow
        }
    } else {
        Write-Host ""
        Write-Host "ERROR: Failed to commit changes!" -ForegroundColor Red
    }
} else {
    Write-Host "No changes to commit. Working tree is clean." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "To check what files are tracked:" -ForegroundColor Cyan
    Write-Host "  git status" -ForegroundColor White
}

Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
