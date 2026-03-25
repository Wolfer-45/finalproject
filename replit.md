# WanderWise

A PHP 8.2 travel planning web application with MySQL database backend.

## Overview

WanderWise is an AI-powered travel companion for India. It helps users plan trips, find travel buddies, track budgets, and keep travel memories — with a full blue-tone UI (primary #0070BB, dark #003262, white).

## Features

- Trip planning with AI itinerary generation (Google Gemini API)
- Weather forecasts (OpenWeatherMap API)
- Travel buddy matching (public browseable for guests)
- Budget tracking and expense management (₹ INR throughout)
- Storybook as interactive book UI with page-turning navigation
- Festival calendar with month-by-month Indian festivals
- Safety tips
- AI chatbot (Wandi) — guest preview available
- Dashboard with Chart.js doughnut charts for trip status + mood breakdown

## Architecture

- **Language**: PHP 8.2
- **Database**: MySQL 8.0 (local, socket-based connection)
- **Server**: PHP built-in development server on port 5000
- **No build system** — traditional PHP web app

## Color Palette (Redesigned)

- Primary: `#0070BB` (main blue)
- Dark: `#003262` (navy)
- Light: `#4A9FD4` (lighter blue)
- Background: `#F0F7FF` (very light blue-white)
- All CSS variables: `--primary`, `--primary-dark`, `--primary-light`
- Legacy aliases: `--saffron`, `--teal`, `--gold` all map to blue values

## Guest Access Model

- Pages accessible without login: Home, Plan Trip, AI Chat, Find Buddy, Festivals, Weather, Safety, Stories
- Pages requiring login: Dashboard, Profile, Settings, Budget, Storybook, Itinerary, Map, Packing
- Guest action intercept: `showLoginModal()` is shown when guests try to submit forms or take actions
- Navbar (guest): Home, Plan Trip, AI Chat, Find Buddy, Festivals, Login, Get Started Free
- Navbar (logged in): Dashboard, Plan Trip, AI Chat, Find Buddy, Stories, Festivals, Profile, Logout

## Project Structure

```
/
├── config.php              # Main configuration (DB, API keys, SITE_URL)
├── database.sql            # MySQL schema
├── start.sh                # Startup script (MySQL + PHP server)
├── includes/
│   ├── db.php              # PDO database connection (supports socket)
│   ├── auth.php            # Authentication helpers
│   ├── functions.php       # Utility functions
│   ├── header.php          # HTML header/nav (consistent guest+auth navbar + login modal)
│   ├── footer.php          # HTML footer
│   ├── gemini.php          # Gemini AI API integration
│   └── weather-api.php     # OpenWeatherMap integration
├── assets/
│   ├── css/
│   │   ├── style.css       # Main design system (blue palette, components)
│   │   ├── travel-theme.css # Page-specific styles, book UI, chatbot
│   │   └── chatbot.css     # Chatbot-specific styles
│   ├── js/                 # JavaScript
│   └── images/             # Static images
├── uploads/
│   └── storybook/          # User-uploaded photos
└── data/
    └── travel-knowledge.txt
```

## Database

- MySQL 8.0 runs locally via socket at `/home/runner/mysql-run/mysql.sock`
- Data stored at `/home/runner/mysql-data`
- Database name: `wanderwise_db`
- User: `root` (no password)
- Schema auto-imported on first start

## Environment Variables / Secrets

The following API keys should be set in `config.private.php` (not committed):
- `GEMINI_API_KEY` — Google Gemini AI API key
- `WEATHER_API_KEY` — OpenWeatherMap API key

## Running

The `start.sh` script:
1. Initializes MySQL data directory (if first run)
2. Starts MySQL server
3. Imports schema (if DB doesn't exist)
4. Starts PHP server on `0.0.0.0:5000`

## Configuration Notes

- `SITE_URL` is dynamically resolved from `HTTP_HOST` header (supports Replit proxy)
- MySQL connects via Unix socket for reliability
- HTTPS redirect in `.htaccess` is disabled (Replit handles TLS at proxy level)
- X-Frame-Options header removed to allow Replit iframe preview
- Currency: all INR (₹) — no dollar signs in UI
- Chart.js 4.4.0 loaded via CDN on dashboard for activity charts
