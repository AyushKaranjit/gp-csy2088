# Google OAuth Setup Guide for DOKO

This guide will help you set up Google OAuth login for your DOKO application.

## Prerequisites

1. **Google Cloud Account**: [Google Cloud Console](https://console.cloud.google.com/)

## Quick Setup

### 1. Environment Configuration

Copy `.env.example` to `.env` and update the following variables:

```bash
# Google OAuth Configuration
# Get these from: https://console.developers.google.com/
GOOGLE_CLIENT_ID=your-google-client-id-here
GOOGLE_CLIENT_SECRET=your-google-client-secret-here
```

### 2. Google OAuth Setup

#### Step 1: Create Google Cloud Project
1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing one
3. Enable the Google+ API

#### Step 2: Create OAuth Credentials
1. Go to **APIs & Services** > **Credentials**
2. Click **Create Credentials** > **OAuth client ID**
3. Configure consent screen if prompted
4. Choose **Web application**
5. Add **Authorized JavaScript origins**:
   - `http://localhost` (for local development)
   - `https://yourdomain.com` (for production)
6. Add **Authorized redirect URIs**:
   - `http://localhost/api/oauth/google.php` (for local development)
   - `https://yourdomain.com/api/oauth/google.php` (for production)

#### Step 3: Configure OAuth Settings
1. Copy the **Client ID** and **Client Secret**
2. Update your `.env` file with these values

## Database Setup

The OAuth system requires database columns for storing social login information. Run the setup script:

```bash
php database/add_oauth_columns.php
```

## Testing

1. Start your local server
2. Navigate to the login page
3. Click "Continue with Google"
4. Verify the OAuth flow works correctly

## Production Deployment

1. Update redirect URIs in Google Cloud Console
2. Use HTTPS for production
3. Update environment variables with production values

## Security Notes

- Keep your client secret secure and never expose it publicly
- Use HTTPS in production
- Regularly rotate your OAuth credentials
- Monitor OAuth usage in Google Cloud Console

## Troubleshooting

### Common Issues

1. **Invalid redirect URI**: Ensure the redirect URI in Google Cloud Console matches your application URL
2. **OAuth consent screen**: Make sure your consent screen is properly configured
3. **HTTPS requirement**: Some OAuth features require HTTPS in production
4. **NetworkError: Error retrieving a token**: 
   - Check that **Authorized JavaScript origins** includes your domain (e.g., `http://localhost`)
   - Verify the Google Client ID is correct in your `.env` file
   - Ensure the API endpoint `api/oauth/google.php` exists and is working
5. **Failed to open popup window**: 
   - Modern browsers block popups by default
   - The updated implementation uses Google's One Tap flow instead of popups
   - Make sure you're not using an ad blocker that blocks Google OAuth
6. **FedCM errors**: 
   - The implementation disables FedCM to avoid compatibility issues
   - This should resolve most NetworkError issues with token retrieval

### Support

For additional help:
- Check Google Cloud Console logs
- Review OAuth documentation: [Google OAuth 2.0](https://developers.google.com/identity/protocols/oauth2)
- Contact support if needed
