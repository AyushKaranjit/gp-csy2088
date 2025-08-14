@echo off
echo Setting up HTTPS tunnel for Facebook OAuth...
echo.

echo Step 1: Download and install ngrok
echo Visit: https://ngrok.com/download
echo.

echo Step 2: After installing ngrok, run this command:
echo ngrok http 80
echo.

echo Step 3: Copy the HTTPS URL (e.g., https://abc123.ngrok.io)
echo.

echo Step 4: Update Facebook App Settings:
echo - Go to https://developers.facebook.com/apps/1505709500870981/
echo - Add the ngrok HTTPS URL to Valid OAuth Redirect URIs
echo - Example: https://abc123.ngrok.io/login.php
echo.

echo Step 5: Access your app via the HTTPS ngrok URL
echo Example: https://abc123.ngrok.io/login.php
echo.

echo Facebook OAuth will work with the HTTPS ngrok URL!
pause
