# Git Deployment Guide - Local to Hostinger via GitHub

## Current Setup ✅
- **Local Repository:** `c:\xampp\htdocs\UltimatePOS\public`
- **GitHub Repository:** `https://github.com/Gagandeep5Singh/ultimate-pos.git`
- **Branch:** `main`
- **Hostinger Git Integration:** Already configured at `alphatec.click`

## Step-by-Step Workflow

### 1. Make Changes Locally
Edit your files in `c:\xampp\htdocs\UltimatePOS\public` as usual.

### 2. Check What Changed
```powershell
cd c:\xampp\htdocs\UltimatePOS\public
git status
```

### 3. Stage Your Changes
Add all modified files:
```powershell
git add .
```

Or add specific files:
```powershell
git add resources/views/home/index.blade.php
git add Modules/Repair/Resources/views/repair/create.blade.php
```

### 4. Commit Your Changes
```powershell
git commit -m "Description of your changes"
```

Example:
```powershell
git commit -m "Update payment cards background and button styling"
```

### 5. Push to GitHub
```powershell
git push origin main
```

### 6. Deploy to Hostinger

**Option A: Manual Deployment (Recommended for testing)**
1. Go to Hostinger Control Panel
2. Navigate to: **Advanced → GIT**
3. Find your repository: `https://github.com/Gagandeep5Singh/ultimate-pos.git`
4. Click the **"Deploy"** button
5. Wait for deployment to complete
6. Check "View latest build output" if there are any errors

**Option B: Auto Deployment (For production)**
1. In Hostinger GIT section, enable **"Auto Deployment"**
2. Now every time you push to GitHub, Hostinger will automatically deploy
3. ⚠️ **Warning:** Only enable this if you're confident your code is ready

## Important Notes

### ⚠️ Database and Configuration Files
**DO NOT commit these files:**
- `.env` (contains sensitive database credentials)
- `storage/` (cache and logs)
- `vendor/` (composer dependencies)
- `node_modules/` (npm dependencies)

These should be in `.gitignore` and managed separately on Hostinger.

### 📁 File Structure
Your Hostinger setup shows the repository is deployed to `/` (root), which typically means `public_html/`.

**Important:** Make sure your `.gitignore` excludes:
- `.env`
- `storage/logs/*`
- `storage/framework/cache/*`
- `storage/framework/sessions/*`
- `storage/framework/views/*`
- `vendor/`
- `node_modules/`

### 🔄 Typical Workflow Example

```powershell
# 1. Make your changes in your code editor

# 2. Check what changed
cd c:\xampp\htdocs\UltimatePOS\public
git status

# 3. Stage changes
git add .

# 4. Commit
git commit -m "Fix payment cards background styling"

# 5. Push to GitHub
git push origin main

# 6. Go to Hostinger and click "Deploy"
```

## Troubleshooting

### Problem: "Nothing to commit"
- Make sure you've saved your files
- Check `git status` to see if files are tracked
- You may need to `git add` the files first

### Problem: "Permission denied" when pushing
- Check your GitHub credentials
- You may need to set up SSH keys or use a Personal Access Token

### Problem: Hostinger deployment fails
- Check "View latest build output" in Hostinger
- Verify file permissions on Hostinger
- Make sure `.env` file exists on Hostinger (not in Git)

### Problem: Changes not showing on website
- Clear cache: `php artisan cache:clear` (run on Hostinger via SSH or terminal)
- Clear view cache: `php artisan view:clear`
- Check file permissions on Hostinger

## Quick Commands Reference

```powershell
# Check status
git status

# See what changed
git diff

# Add all changes
git add .

# Commit
git commit -m "Your message"

# Push to GitHub
git push origin main

# Pull latest from GitHub (if working on multiple machines)
git pull origin main

# View commit history
git log --oneline -10
```

## Need Help?

If deployment fails:
1. Check Hostinger "View latest build output"
2. Verify your `.gitignore` is correct
3. Make sure database credentials in Hostinger `.env` are correct
4. Check file permissions on Hostinger
