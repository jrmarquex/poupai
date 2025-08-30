# AssistenteFin - WhatsApp Financial Management

## Overview

AssistenteFin is a WhatsApp-based financial management service that allows users to track expenses and income through natural conversation. Users can send text messages, voice notes, or photos of receipts, and an AI agent automatically categorizes and records the transactions. The service provides automated dashboards showing monthly spending patterns, categorized expenses, and income vs. expenditure analysis. The product targets young professionals and freelancers with a subscription model of R$ 9.99/month and offers a 7-day free trial.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- **Single-page static site**: Built as a landing page using vanilla HTML with Tailwind CSS for styling
- **No-build approach**: Uses CDN-delivered dependencies (Tailwind CSS, Alpine.js) for simplicity and fast deployment
- **Mobile-first responsive design**: Optimized primarily for smartphone viewing since the target users interact via WhatsApp
- **Performance-optimized**: Designed for Lighthouse optimization with focus on loading speed and accessibility

### Design System
- **Dark theme fintech aesthetic**: Uses dark background (#0B0F14) with vibrant accent colors
- **Color palette**: 
  - Primary: Teal green (#20C997)
  - Secondary: Purple (#A78BFA) 
  - Accent: Cyan (#22D3EE)
  - Glass effects: Semi-transparent overlays with blur effects
- **Modern visual elements**: Glassmorphism, subtle gradients, and microanimations for engaging UX
- **Typography**: Inter font family for clean, professional readability

### Interaction Framework
- **Alpine.js**: Lightweight JavaScript framework for reactive components and state management
- **Progressive enhancement**: Core functionality works without JavaScript, enhanced with Alpine.js
- **Minimal JavaScript footprint**: Focus on essential interactions only

### SEO and Accessibility
- **Semantic HTML structure**: Proper heading hierarchy and meta tags
- **Open Graph and Twitter Cards**: Social media optimization
- **Performance considerations**: Inline SVGs, optimized loading, no external image dependencies

## External Dependencies

### CDN Services
- **Tailwind CSS**: Styling framework delivered via CDN for rapid development
- **Alpine.js**: Client-side reactivity framework (referenced but not yet implemented)
- **Google Fonts**: Inter font family for typography

### WhatsApp Integration
- **WhatsApp Business API**: Core service integration for user interaction and transaction processing
- **AI Processing Service**: Automated categorization and transaction parsing from text, audio, and images

### Payment Processing
- **Subscription billing system**: Monthly R$ 9.99 recurring payments with 7-day free trial
- **Brazilian payment methods**: Integration with local payment processors for the Brazilian market

### Analytics and Monitoring
- **Web analytics**: Performance tracking and user behavior analysis
- **Conversion tracking**: Landing page effectiveness and trial-to-paid conversion metrics