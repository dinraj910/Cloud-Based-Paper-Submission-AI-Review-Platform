# 🤖 AI PDF Chat - Quick Start

## What's New?

Your research portal now has an **AI-powered chatbot** that can analyze and discuss PDF papers!

### Features
- 💬 **Chat with PDFs**: Ask questions about any research paper
- 🆓 **100% Free**: Uses Groq's free Llama 3.3 model
- ⚡ **Fast**: Get answers in seconds
- 🎨 **Beautiful UI**: Modern popup chat interface
- 🔒 **Private**: Conversations are not stored

---

## Setup (2 Minutes)

### Option 1: Automated Setup (Recommended)

```bash
cd /var/www/html/research-portal
./setup_ai_chat.sh
```

### Option 2: Manual Setup

1. **Get Free API Key** (30 seconds)
   - Visit: https://console.groq.com/keys
   - Sign up (no credit card needed)
   - Create an API key

2. **Configure** (10 seconds)
   ```bash
   nano config/ai.php
   # Replace YOUR_GROQ_API_KEY_HERE with your key
   ```

3. **Install PDF Tool** (optional, 1 minute)
   ```bash
   sudo apt-get install -y poppler-utils
   ```

---

## How to Use

1. **Upload a PDF** to the portal
2. **Click the "AI Chat" button** (purple/pink, next to Download)
3. **Wait 2-3 seconds** for PDF analysis
4. **Ask questions!**

### Example Questions
- "What is this paper about?"
- "Summarize the key findings"
- "What methodology was used?"
- "What are the limitations?"

---

## Files Added

```
api/
├── chat_pdf.php           ← AI chat API
└── extract_pdf_text.php   ← PDF text extraction

config/
├── ai.php                 ← Your API key (configure this!)
└── ai.php.example         ← Example template

index.php                  ← Updated with AI chat UI

AI_CHAT_SETUP.md          ← Detailed documentation
QUICK_START_AI.md         ← This file
setup_ai_chat.sh          ← Automated setup script
```

---

## Troubleshooting

**"AI API key not configured"**
→ Set your Groq API key in `config/ai.php`

**"Could not extract text from PDF"**
→ Install pdftotext: `sudo apt-get install poppler-utils`

**Modal not opening**
→ Check browser console for errors

---

## More Info

- **Full Documentation**: See [AI_CHAT_SETUP.md](AI_CHAT_SETUP.md)
- **Groq Console**: https://console.groq.com
- **Rate Limits**: 14,400 requests/day (free tier)

---

**That's it! You're ready to chat with research papers! 🎉**
