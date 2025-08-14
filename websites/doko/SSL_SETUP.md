# SSL Certificate Setup for Local Development
# This guide helps you set up HTTPS for local development to use Facebook OAuth

## Option 1: Use mkcert (Recommended)

### Install mkcert:
1. Download mkcert from: https://github.com/FiloSottile/mkcert/releases
2. Run: `mkcert -install` (install local CA)
3. Generate certificates: `mkcert localhost 127.0.0.1 ::1`

### Update nginx.conf:
Add this server block for HTTPS:

```nginx
server {
    listen 443 ssl http2;
    server_name localhost;
    
    ssl_certificate /etc/ssl/certs/localhost.pem;
    ssl_certificate_key /etc/ssl/private/localhost-key.pem;
    
    root /websites/doko/public;
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass php:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### Update docker-compose.yml:
Add SSL certificate volumes and port 443:

```yaml
web:
  image: nginx:latest
  ports:
    - "80:80"
    - "443:443"  # Add HTTPS port
  volumes:
    - ./nginx.conf:/etc/nginx/conf.d/default.conf
    - ./websites:/websites
    - ./localhost.pem:/etc/ssl/certs/localhost.pem      # SSL certificate
    - ./localhost-key.pem:/etc/ssl/private/localhost-key.pem  # SSL key
```

## Option 2: Facebook App Development Mode

### Alternative: Configure Facebook for localhost HTTP

1. Go to Facebook Developers: https://developers.facebook.com/apps/
2. Select your app
3. Go to Settings > Basic
4. Add "localhost" to App Domains
5. Go to Facebook Login > Settings
6. Add these Valid OAuth Redirect URIs:
   - http://localhost/login.php
   - http://localhost/api/oauth/facebook.php
   - http://localhost/

### Update Facebook App for Development:
- Set App Mode to "Development"
- Add test users if needed
- Ensure app is not in "Live" mode for localhost testing

## Option 3: Use ngrok (Quick Solution)

1. Install ngrok: https://ngrok.com/
2. Run: `ngrok http 80`
3. Use the HTTPS URL provided by ngrok
4. Update Facebook app settings with the ngrok HTTPS URL

## Current Facebook App Settings Needed:

App ID: 1505709500870981
Valid OAuth Redirect URIs should include:
- https://your-ngrok-url.ngrok.io/login.php
- http://localhost/login.php (if app is in development mode)

## Test the Setup:

1. Visit https://localhost/login.php (with HTTPS)
2. Click "Continue with Facebook"
3. Complete OAuth flow
4. Check if user is created/logged in successfully
