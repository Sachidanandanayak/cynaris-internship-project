/**
 * Cynaris Solutions - Weather App Configuration Template
 * Week 2 Day 5: Frontend Mini Project
 *
 * SECURITY NOTICE:
 * 1. NEVER commit API keys or secret credentials directly into version control.
 * 2. This file serves as an example template (config.example.js).
 * 3. If using an external service that requires an API key (e.g. OpenWeatherMap):
 *    - Copy this file to `config.js` in the same directory (`weather_app/config.js`).
 *    - Replace the placeholder key below with your private API key.
 *    - `weather_app/config.js` is included in `.gitignore` and will NOT be tracked by git.
 *
 * NOTE ON DEFAULT PROVIDER:
 * By default, this application utilizes the Open-Meteo API (https://open-meteo.com/),
 * which provides high-precision global meteorological forecasts with ZERO API key required.
 * This guarantees 100% out-of-the-box functionality for local development and GitHub Pages.
 */

const WEATHER_CONFIG = {
    // Selected provider: 'open-meteo' (default, keyless) or 'openweathermap'
    provider: 'open-meteo',

    // Optional OpenWeatherMap API configuration (only used if provider is 'openweathermap')
    openWeatherMap: {
        apiKey: 'YOUR_OPENWEATHERMAP_API_KEY_HERE', // DO NOT COMMIT REAL KEYS
        baseUrl: 'https://api.openweathermap.org/data/2.5'
    },

    // Default application preferences
    defaults: {
        city: 'London',
        temperatureUnit: 'celsius', // 'celsius' or 'fahrenheit'
        autoLocateOnLoad: false
    }
};

// Export configuration for browser environments or module bundlers
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WEATHER_CONFIG;
}
