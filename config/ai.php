<?php
/**
 * AI Configuration
 * Get your free API key from: https://console.groq.com/keys
 * Groq provides free access to Llama models with generous rate limits
 */

// IMPORTANT: Get your free Groq API key from https://console.groq.com/keys
// Sign up is free and takes less than a minute

// Option 1: Use environment variable (RECOMMENDED for security)
$GROQ_API_KEY = getenv('GROQ_API_KEY') ?: '';

// Option 2: Or set directly (NOT RECOMMENDED - keep this file in .gitignore)
// $GROQ_API_KEY = 'your_key_here';

// If environment variable not set, you can load it from set_api_key.sh
// Run: source set_api_key.sh
