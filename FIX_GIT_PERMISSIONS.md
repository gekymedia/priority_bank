# Fix Git Pull Permission Issues on Server

## Problem
When running `git pull` in `/home/gekymedia/web/bank.prioritysolutionsagency.com/public_html`, it changes file permissions and breaks the web app.

## Solution Steps

### 1. SSH into the server
```bash
ssh root@gekymedia.com
```

### 2. Check current ownership and permissions
```bash
cd /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html
ls -la
whoami
```

### 3. Check Git configuration
```bash
cd /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html
git config --list | grep -i filemode
git config --list | grep -i user
```

### 4. Fix Git configuration to preserve permissions
```bash
cd /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html
# Disable file mode changes
git config core.fileMode false

# Set proper user (should match web server user, usually gekymedia or www-data)
git config user.name "gekymedia"
git config user.email "gekymedia@gmail.com"
```

### 5. Fix current file permissions
```bash
cd /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html

# Set proper ownership (adjust user:group as needed)
chown -R gekymedia:gekymedia .

# Set proper permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Make specific files executable if needed
chmod +x artisan
chmod -R 775 storage bootstrap/cache
```

### 6. Create a post-checkout Git hook to maintain permissions
```bash
cd /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html
cat > .git/hooks/post-checkout << 'EOF'
#!/bin/bash
# Fix permissions after git checkout/pull
chown -R gekymedia:gekymedia .
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod +x artisan
chmod -R 775 storage bootstrap/cache
EOF

chmod +x .git/hooks/post-checkout
```

### 7. Create a post-merge Git hook (runs after git pull)
```bash
cd /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html
cat > .git/hooks/post-merge << 'EOF'
#!/bin/bash
# Fix permissions after git pull
chown -R gekymedia:gekymedia .
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod +x artisan
chmod -R 775 storage bootstrap/cache
EOF

chmod +x .git/hooks/post-merge
```

### 8. Alternative: Use a deployment script
Create a safe deployment script:
```bash
cd /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html
cat > deploy.sh << 'EOF'
#!/bin/bash
# Safe deployment script
cd /home/gekymedia/web/bank.prioritysolutionsagency.com/public_html

# Pull changes
git pull

# Fix ownership
chown -R gekymedia:gekymedia .

# Fix permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod +x artisan
chmod -R 775 storage bootstrap/cache

# Clear caches (Laravel)
php artisan cache:clear
php artisan config:clear
php artisan view:clear
EOF

chmod +x deploy.sh
```

## Usage
Instead of `git pull`, use:
```bash
./deploy.sh
```

## Comparison with Working Directory
Check how chat.gekychat.com is configured:
```bash
cd /home/gekymedia/web/chat.gekychat.com/public_html
git config --list | grep -i filemode
ls -la | head -20
```

## Notes
- Always run git commands as the web server user (gekymedia), not root
- The `core.fileMode false` setting prevents Git from tracking permission changes
- Git hooks automatically fix permissions after pull operations
- Consider using a deployment script for safer updates
