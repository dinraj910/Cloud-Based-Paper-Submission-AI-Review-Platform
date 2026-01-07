# 🤖 AI PDF Chat Feature - Setup Guide

## Overview

This feature adds an AI-powered chatbot to analyze and discuss research papers. Users can click an AI button on any PDF to open a chat interface and ask questions about the paper's content.

## ✨ Features

- **🤖 Free AI Model**: Uses Groq's free Llama 3.3 70B model (no credit card required)
- **💬 Interactive Chat**: Real-time conversation interface with chat history
- **📄 PDF Analysis**: Automatically extracts and analyzes PDF text
- **🎨 Beautiful UI**: Modern modal interface with gradient design
- **⚡ Fast Responses**: Groq provides some of the fastest LLM inference speeds
- **🔒 Privacy**: Conversations are not stored (ephemeral)

## 🚀 Quick Start

### Step 1: Get a Free Groq API Key

1. Visit [https://console.groq.com](https://console.groq.com)
2. Sign up for a free account (no credit card required)
3. Navigate to API Keys section
4. Click "Create API Key"
5. Copy your API key

### Step 2: Configure the API Key

1. Open `/var/www/html/research-portal/config/ai.php`
2. Replace `YOUR_GROQ_API_KEY_HERE` with your actual API key:

```php
$GROQ_API_KEY = 'gsk_your_actual_key_here';
```

### Step 3: Install PDF Text Extraction Tool (Optional but Recommended)

For better PDF text extraction, install `pdftotext`:

```bash
# Ubuntu/Debian
sudo apt-get update
sudo apt-get install -y poppler-utils

# Verify installation
which pdftotext
```

If `pdftotext` is not available, the system will use a fallback PHP-based extraction method.

### Step 4: Test the Feature

1. Upload a PDF research paper through the portal
2. Click the **"AI Chat"** button (purple/pink gradient) on any PDF card
3. Wait for the PDF to be analyzed
4. Start asking questions!

## 💡 Example Questions to Ask

- "What is the main conclusion of this paper?"
- "Summarize the methodology used"
- "What are the key findings?"
- "Explain the results in simple terms"
- "What datasets were used in this research?"
- "What are the limitations mentioned?"

## 🏗️ Technical Architecture

### Components

1. **Frontend (index.php)**
   - AI Chat button for PDF files
   - Modal popup with chat interface
   - JavaScript for real-time messaging

2. **Backend APIs**
   - `api/extract_pdf_text.php` - Extracts text from PDF URLs
   - `api/chat_pdf.php` - Handles AI chat requests

3. **Configuration**
   - `config/ai.php` - Stores Groq API key

### How It Works

```
┌─────────────┐
│   User      │
│  Clicks AI  │
│   Button    │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────┐
│  1. Extract PDF Text            │
│     GET /api/extract_pdf_text   │
│     - Downloads PDF             │
│     - Extracts text content     │
│     - Returns ~15K characters   │
└──────┬──────────────────────────┘
       │
       ▼
┌─────────────────────────────────┐
│  2. User Asks Question          │
│     POST /api/chat_pdf.php      │
│     - Sends question + PDF text │
│     - Calls Groq API            │
│     - Returns AI response       │
└──────┬──────────────────────────┘
       │
       ▼
┌─────────────────────────────────┐
│  3. Display Response            │
│     - Shows in chat interface   │
│     - Maintains conversation    │
│     - User can ask follow-ups   │
└─────────────────────────────────┘
```

## 🔧 Configuration Options

### Change AI Model

Edit `api/chat_pdf.php` line 68:

```php
'model' => 'llama-3.3-70b-versatile', // Fast, versatile model
// Alternatives:
// 'model' => 'llama-3.1-8b-instant',  // Faster, lighter
// 'model' => 'mixtral-8x7b-32768',    // Larger context window
```

### Adjust Text Extraction Limit

Edit `api/extract_pdf_text.php` line 48:

```php
if (strlen($text) > 15000) {
    $text = substr($text, 0, 15000) . '...';
}
```

### Modify Response Length

Edit `api/chat_pdf.php` line 71:

```php
'max_tokens' => 1024,  // Increase for longer responses
```

## 🎨 UI Customization

### Change AI Button Color

In `index.php`, find the AI Chat button (around line 93):

```php
class="... from-purple-500 to-pink-500 ..."
```

Change to:
- Blue: `from-blue-500 to-cyan-500`
- Green: `from-green-500 to-emerald-500`
- Orange: `from-orange-500 to-red-500`

### Customize Modal Size

In `index.php`, find the modal container (around line 234):

```html
<div class="... max-w-2xl ...">
```

Change to:
- Larger: `max-w-4xl`
- Smaller: `max-w-lg`

## 🐛 Troubleshooting

### "AI API key not configured" Error

**Solution**: Make sure you've set your Groq API key in `config/ai.php`

### "Could not extract text from PDF" Error

**Causes**:
- PDF is image-based (scanned document without OCR)
- PDF is encrypted/password protected
- PDF URL is not accessible

**Solutions**:
- Ensure PDFs are text-based (not scanned images)
- Install `pdftotext` for better extraction
- Verify PDF URL is publicly accessible

### "Failed to get AI response" Error

**Causes**:
- Invalid API key
- Rate limit exceeded (Groq free tier: ~14,000 requests/day)
- Network connectivity issues

**Solutions**:
- Verify API key is correct
- Check Groq console for usage limits
- Wait a few minutes and try again

### Modal Not Opening

**Solution**: Check browser console for JavaScript errors. Ensure jQuery is not conflicting.

## 📊 Rate Limits (Groq Free Tier)

- **Requests per day**: ~14,400
- **Requests per minute**: 30
- **Tokens per minute**: 14,400

More than enough for a research portal!

## 🔐 Security Best Practices

1. **Never commit API key to Git**:
   ```bash
   echo "config/ai.php" >> .gitignore
   ```

2. **Use environment variables** (production):
   ```php
   $GROQ_API_KEY = getenv('GROQ_API_KEY') ?: '';
   ```

3. **Add rate limiting** to prevent abuse:
   ```php
   // In chat_pdf.php, add session-based rate limiting
   session_start();
   if (($_SESSION['chat_requests'] ?? 0) > 10) {
       // Limit to 10 requests per session
   }
   ```

## 🌟 Future Enhancements

Possible improvements:
- [ ] Support for DOC/DOCX files
- [ ] Image-based PDF support (OCR)
- [ ] Save chat history to database (optional)
- [ ] Multi-language support
- [ ] Voice input/output
- [ ] PDF highlighting based on AI responses
- [ ] Export chat conversations
- [ ] Compare multiple papers

## 📝 File Structure

```
research-portal/
├── api/
│   ├── chat_pdf.php           # AI chat endpoint
│   └── extract_pdf_text.php   # PDF text extraction
├── config/
│   ├── ai.php                 # API key configuration
│   └── ai.php.example         # Example config
├── index.php                  # Main page with AI chat UI
└── AI_CHAT_SETUP.md          # This file
```

## 🆘 Support

- **Groq Documentation**: https://console.groq.com/docs
- **Groq Community**: https://discord.gg/groq
- **Rate Limits**: https://console.groq.com/docs/rate-limits

## 📄 License

This AI chat feature is part of the Research Portal project and follows the same license.

---

**Enjoy chatting with your research papers! 🚀📚**
