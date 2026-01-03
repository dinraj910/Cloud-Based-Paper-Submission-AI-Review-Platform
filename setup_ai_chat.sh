#!/bin/bash

# AI Chat Feature - Quick Setup Script
# This script helps you set up the AI PDF chat feature

echo "╔════════════════════════════════════════════════════════╗"
echo "║     AI PDF Chat Feature - Quick Setup                 ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if running from correct directory
if [ ! -f "config/ai.php" ]; then
    echo -e "${RED}Error: Please run this script from the research-portal directory${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Checking PDF text extraction tool...${NC}"
if command -v pdftotext &> /dev/null; then
    echo -e "${GREEN}✓ pdftotext is installed${NC}"
else
    echo -e "${YELLOW}⚠ pdftotext is not installed${NC}"
    echo "Installing pdftotext (poppler-utils)..."
    
    if command -v apt-get &> /dev/null; then
        sudo apt-get update
        sudo apt-get install -y poppler-utils
        echo -e "${GREEN}✓ pdftotext installed successfully${NC}"
    elif command -v yum &> /dev/null; then
        sudo yum install -y poppler-utils
        echo -e "${GREEN}✓ pdftotext installed successfully${NC}"
    else
        echo -e "${RED}⚠ Could not install pdftotext automatically. Please install manually.${NC}"
        echo "   The system will use a fallback method for PDF extraction."
    fi
fi
echo ""

echo -e "${YELLOW}Step 2: Checking configuration file...${NC}"
if grep -q "YOUR_GROQ_API_KEY_HERE" config/ai.php; then
    echo -e "${RED}⚠ API key not configured${NC}"
    echo ""
    echo "To complete setup:"
    echo "1. Visit https://console.groq.com/keys"
    echo "2. Sign up for a free account (no credit card required)"
    echo "3. Create an API key"
    echo "4. Edit config/ai.php and replace YOUR_GROQ_API_KEY_HERE with your key"
    echo ""
    
    read -p "Do you have your Groq API key ready? (y/n) " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo "Please enter your Groq API key:"
        read -r API_KEY
        
        # Update config file
        sed -i "s/YOUR_GROQ_API_KEY_HERE/$API_KEY/" config/ai.php
        echo -e "${GREEN}✓ API key configured successfully${NC}"
    else
        echo -e "${YELLOW}Please configure your API key manually in config/ai.php${NC}"
    fi
else
    echo -e "${GREEN}✓ API key is configured${NC}"
fi
echo ""

echo -e "${YELLOW}Step 3: Checking file permissions...${NC}"
chmod 755 api/chat_pdf.php
chmod 755 api/extract_pdf_text.php
chmod 644 config/ai.php
echo -e "${GREEN}✓ File permissions set${NC}"
echo ""

echo -e "${YELLOW}Step 4: Testing API endpoint...${NC}"
# Create a simple test
TEST_RESULT=$(php -r "include 'config/ai.php'; echo empty(\$GROQ_API_KEY) || \$GROQ_API_KEY === 'YOUR_GROQ_API_KEY_HERE' ? 'NOT_CONFIGURED' : 'CONFIGURED';")

if [ "$TEST_RESULT" == "CONFIGURED" ]; then
    echo -e "${GREEN}✓ Configuration test passed${NC}"
else
    echo -e "${RED}⚠ API key still needs to be configured${NC}"
fi
echo ""

echo "╔════════════════════════════════════════════════════════╗"
echo "║                  Setup Summary                         ║"
echo "╚════════════════════════════════════════════════════════╝"
echo ""
echo "Files created:"
echo "  ✓ api/chat_pdf.php           - AI chat endpoint"
echo "  ✓ api/extract_pdf_text.php   - PDF text extraction"
echo "  ✓ config/ai.php              - API configuration"
echo "  ✓ config/ai.php.example      - Example config"
echo "  ✓ AI_CHAT_SETUP.md           - Complete documentation"
echo ""
echo "Next steps:"
echo "  1. Make sure your Groq API key is configured in config/ai.php"
echo "  2. Upload a PDF through the portal"
echo "  3. Click the 'AI Chat' button on any PDF"
echo "  4. Start asking questions!"
echo ""
echo "For detailed documentation, see: AI_CHAT_SETUP.md"
echo ""
echo -e "${GREEN}Setup complete! 🚀${NC}"
