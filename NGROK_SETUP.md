# 🌐 Expose Your Research Portal with Ngrok

## Quick Setup (2 minutes)

### Step 1: Get Free Ngrok Auth Token

1. Go to: https://dashboard.ngrok.com/signup
2. Sign up (free, no credit card needed)
3. Go to: https://dashboard.ngrok.com/get-started/your-authtoken
4. Copy your authtoken

### Step 2: Configure Ngrok

```bash
ngrok config add-authtoken YOUR_AUTH_TOKEN_HERE
```

### Step 3: Start Ngrok

```bash
ngrok http 80
```

You'll see output like:
```
Forwarding    https://abc-123-456.ngrok-free.app -> http://localhost:80
```

### Step 4: Access Your Portal

Share the `https://` URL with anyone! They can access your portal from anywhere.

**Example**: `https://abc-123-456.ngrok-free.app/research-portal/`

## 🎯 Quick Commands

**Start ngrok:**
```bash
cd /var/www/html/research-portal
ngrok http 80
```

**Run in background:**
```bash
nohup ngrok http 80 > ngrok.log 2>&1 &
```

**Get current URL:**
```bash
curl -s localhost:4040/api/tunnels | grep -o '"public_url":"https://[^"]*'
```

**Stop ngrok:**
```bash
pkill ngrok
```

## ⚙️ Important Notes

1. **Free tier limits**: 40 connections/min, random URL each time
2. **URL changes**: Each time you restart ngrok, you get a new URL
3. **Apache config**: No changes needed! Ngrok works as-is
4. **Persistent domain**: Upgrade to paid ($8/mo) for fixed domain

## 🔒 Security Tips

1. This exposes your portal to the internet - anyone with the URL can access it
2. Make sure your database credentials are secure
3. Consider adding rate limiting for production use
4. The free tier shows an ngrok warning page on first visit

## 📊 Monitor Traffic

Open in browser: `http://localhost:4040`

This shows:
- Live requests
- Response times
- Request/response details

## 🚀 Your Portal is Ready!

Just run:
```bash
ngrok config add-authtoken YOUR_TOKEN
ngrok http 80
```

Then share the URL! 🎉
