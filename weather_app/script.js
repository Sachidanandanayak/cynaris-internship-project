/**
 * Cynaris Solutions - Atmospheric Telemetry & Weather Dashboard
 * Week 2 Day 5: Frontend Mini Project (Responsive Weather App)
 *
 * Concepts Demonstrated:
 * 1. Asynchronous JavaScript: fetch() API with async/await
 * 2. Defensive programming: try / catch / finally error handling
 * 3. DOM element caching and dynamic DOM rendering
 * 4. Zero API key leakage architecture (Open-Meteo keyless primary provider)
 * 5. State management: Unit conversion (°C <-> °F) without re-fetching
 * 6. Browser Storage: LocalStorage persistence for user preferences
 * 7. HTML5 Geolocation API integration with permission fallbacks
 */

'use strict';

/* ==========================================================================
   1. Configuration & WMO Meteorological Code Dictionaries
   ========================================================================== */

/**
 * Global application state
 */
const appState = {
    currentCity: 'London',
    currentWeather: null, // Cached raw weather payload from API
    unit: 'celsius',      // 'celsius' | 'fahrenheit'
    provider: 'open-meteo', // 'open-meteo' | 'openweathermap'
    apiKey: '',
    isLoading: false
};

/**
 * LocalStorage keys for preferences and API settings
 */
const STORAGE_KEYS = {
    PREFERENCES: 'cynaris_weather_prefs_v1',
    API_KEY: 'cynaris_owm_api_key_v1'
};

/**
 * WMO (World Meteorological Organization) Code Translation Table
 * Maps Open-Meteo integer codes to human-readable text, emoji icons, and theme classes.
 */
const WMO_WEATHER_CODES = {
    0:  { description: 'Clear Sky', iconDay: '☀️', iconNight: '🌙', theme: 'clear' },
    1:  { description: 'Mainly Clear', iconDay: '🌤️', iconNight: '🌤️', theme: 'clear' },
    2:  { description: 'Partly Cloudy', iconDay: '⛅', iconNight: '⛅', theme: 'clouds' },
    3:  { description: 'Overcast', iconDay: '☁️', iconNight: '☁️', theme: 'clouds' },
    45: { description: 'Foggy', iconDay: '🌫️', iconNight: '🌫️', theme: 'clouds' },
    48: { description: 'Depositing Rime Fog', iconDay: '🌫️', iconNight: '🌫️', theme: 'clouds' },
    51: { description: 'Light Drizzle', iconDay: '🌦️', iconNight: '🌦️', theme: 'rain' },
    53: { description: 'Moderate Drizzle', iconDay: '🌦️', iconNight: '🌦️', theme: 'rain' },
    55: { description: 'Dense Drizzle', iconDay: '🌧️', iconNight: '🌧️', theme: 'rain' },
    56: { description: 'Light Freezing Drizzle', iconDay: '🌧️', iconNight: '🌧️', theme: 'snow' },
    57: { description: 'Dense Freezing Drizzle', iconDay: '🌧️', iconNight: '🌧️', theme: 'snow' },
    61: { description: 'Slight Rain', iconDay: '🌦️', iconNight: '🌦️', theme: 'rain' },
    63: { description: 'Moderate Rain', iconDay: '🌧️', iconNight: '🌧️', theme: 'rain' },
    65: { description: 'Heavy Rain', iconDay: '🌧️', iconNight: '🌧️', theme: 'rain' },
    66: { description: 'Light Freezing Rain', iconDay: '🌨️', iconNight: '🌨️', theme: 'snow' },
    67: { description: 'Heavy Freezing Rain', iconDay: '🌨️', iconNight: '🌨️', theme: 'snow' },
    71: { description: 'Slight Snow Fall', iconDay: '🌨️', iconNight: '🌨️', theme: 'snow' },
    73: { description: 'Moderate Snow Fall', iconDay: '❄️', iconNight: '❄️', theme: 'snow' },
    75: { description: 'Heavy Snow Fall', iconDay: '❄️', iconNight: '❄️', theme: 'snow' },
    77: { description: 'Snow Grains', iconDay: '❄️', iconNight: '❄️', theme: 'snow' },
    80: { description: 'Slight Rain Showers', iconDay: '🌦️', iconNight: '🌦️', theme: 'rain' },
    81: { description: 'Moderate Rain Showers', iconDay: '🌧️', iconNight: '🌧️', theme: 'rain' },
    82: { description: 'Violent Rain Showers', iconDay: '⛈️', iconNight: '⛈️', theme: 'rain' },
    85: { description: 'Slight Snow Showers', iconDay: '🌨️', iconNight: '🌨️', theme: 'snow' },
    86: { description: 'Heavy Snow Showers', iconDay: '❄️', iconNight: '❄️', theme: 'snow' },
    95: { description: 'Thunderstorm', iconDay: '⛈️', iconNight: '⛈️', theme: 'thunderstorm' },
    96: { description: 'Thunderstorm with Slight Hail', iconDay: '⛈️', iconNight: '⛈️', theme: 'thunderstorm' },
    99: { description: 'Thunderstorm with Heavy Hail', iconDay: '⛈️', iconNight: '⛈️', theme: 'thunderstorm' }
};

/* ==========================================================================
   2. DOM Element Cache
   ========================================================================== */
const DOM = {
    // Search & inputs
    searchForm: document.getElementById('weather-search-form'),
    cityInput: document.getElementById('city-input'),
    btnClearInput: document.getElementById('btn-clear-input'),
    btnSearch: document.getElementById('btn-search'),
    btnGeolocation: document.getElementById('btn-geolocation'),
    unitCelsius: document.getElementById('unit-celsius'),
    unitFahrenheit: document.getElementById('unit-fahrenheit'),
    quickChips: document.querySelectorAll('.city-chip'),

    // Loading & Error States
    errorBanner: document.getElementById('error-banner'),
    errorTitle: document.getElementById('error-title'),
    errorMessage: document.getElementById('error-message'),
    btnDismissError: document.getElementById('btn-dismiss-error'),
    loadingState: document.getElementById('loading-state'),

    // Weather Display Containers
    weatherDisplay: document.getElementById('weather-display'),
    telemetrySource: document.getElementById('telemetry-source'),
    currentCity: document.getElementById('current-city'),
    currentCountry: document.getElementById('current-country'),
    currentTime: document.getElementById('current-time'),
    weatherIcon: document.getElementById('weather-icon'),
    weatherCondition: document.getElementById('weather-condition'),
    currentTemp: document.getElementById('current-temp'),
    currentUnit: document.getElementById('current-unit'),
    feelsLikeTemp: document.getElementById('feels-like-temp'),
    highLowTemp: document.getElementById('high-low-temp'),

    // Metrics
    metricHumidity: document.getElementById('metric-humidity'),
    humidityBar: document.getElementById('humidity-bar'),
    humidityCaption: document.getElementById('humidity-caption'),
    metricWindSpeed: document.getElementById('metric-wind-speed'),
    metricWindUnit: document.getElementById('metric-wind-unit'),
    metricWindDirection: document.getElementById('metric-wind-direction'),
    windCompassArrow: document.getElementById('wind-compass-arrow'),
    metricPressure: document.getElementById('metric-pressure'),
    pressureCaption: document.getElementById('pressure-caption'),
    metricUv: document.getElementById('metric-uv'),
    metricUvBadge: document.getElementById('metric-uv-badge'),
    metricPrecip: document.getElementById('metric-precip'),
    precipCaption: document.getElementById('precip-caption'),
    metricSunrise: document.getElementById('metric-sunrise'),
    metricSunset: document.getElementById('metric-sunset'),

    // Forecast container
    forecastContainer: document.getElementById('forecast-cards-container'),

    // Settings Modal
    btnOpenSettings: document.getElementById('btn-open-settings'),
    btnCloseSettings: document.getElementById('btn-close-settings'),
    settingsModal: document.getElementById('settings-modal'),
    providerSelect: document.getElementById('provider-select'),
    owmKeyGroup: document.getElementById('owm-key-group'),
    owmApiKeyInput: document.getElementById('owm-api-key'),
    btnSaveSettings: document.getElementById('btn-save-settings'),
    btnResetSettings: document.getElementById('btn-reset-settings')
};

/* ==========================================================================
   3. Utility Functions & Unit Conversion
   ========================================================================== */

/**
 * Converts Celsius to Fahrenheit
 * Formula: (°C * 9/5) + 32
 * @param {number} celsius
 * @returns {number}
 */
function toFahrenheit(celsius) {
    if (typeof celsius !== 'number' || isNaN(celsius)) return 0;
    return Math.round((celsius * 9) / 5 + 32);
}

/**
 * Formats temperature based on the active unit
 * @param {number} tempInCelsius - Base temperature in Celsius
 * @param {boolean} includeSymbol - Whether to include °C or °F
 * @returns {string}
 */
function formatTemperature(tempInCelsius, includeSymbol = false) {
    if (typeof tempInCelsius !== 'number' || isNaN(tempInCelsius)) return '--';
    const value = appState.unit === 'fahrenheit' ? toFahrenheit(tempInCelsius) : Math.round(tempInCelsius);
    if (!includeSymbol) return `${value}`;
    return `${value}°${appState.unit === 'fahrenheit' ? 'F' : 'C'}`;
}

/**
 * Converts Wind Speed between km/h and mph
 * @param {number} kmh
 * @returns {{ value: string, unit: string }}
 */
function formatWindSpeed(kmh) {
    if (typeof kmh !== 'number' || isNaN(kmh)) return { value: '--', unit: 'km/h' };
    if (appState.unit === 'fahrenheit') {
        const mph = (kmh * 0.621371).toFixed(1);
        return { value: mph, unit: 'mph' };
    }
    return { value: kmh.toFixed(1), unit: 'km/h' };
}

/**
 * Converts wind degree to 16-point compass heading string
 * @param {number} degrees - 0 to 360
 * @returns {string} e.g. "WSW", "NE"
 */
function getCompassDirection(degrees) {
    if (typeof degrees !== 'number' || isNaN(degrees)) return 'N';
    const directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
    const index = Math.round((degrees % 360) / 22.5) % 16;
    return directions[index];
}

/**
 * Maps WMO code to human-readable metadata, icon, and CSS theme
 * @param {number} code - WMO code
 * @param {boolean} isDay - 1 for day, 0 for night
 * @returns {{ description: string, icon: string, theme: string }}
 */
function interpretWeatherCode(code, isDay = 1) {
    const defaultEntry = { description: 'Variable Weather', iconDay: '🌤️', iconNight: '🌙', theme: 'clear' };
    const entry = WMO_WEATHER_CODES[code] || defaultEntry;
    const isDaytime = Boolean(isDay);

    let themeClass = `theme-${entry.theme}`;
    if (entry.theme === 'clear') {
        themeClass = isDaytime ? 'theme-clear-day' : 'theme-clear-night';
    }

    return {
        description: entry.description,
        icon: isDaytime ? entry.iconDay : entry.iconNight,
        themeClass
    };
}

/**
 * Formats ISO timestamp string (e.g. "2026-09-12T06:30") to 12-hour or 24-hour time "06:30"
 * @param {string} isoString
 * @returns {string}
 */
function formatIsoTime(isoString) {
    if (!isoString) return '--:--';
    try {
        const parts = isoString.split('T');
        if (parts.length > 1) {
            return parts[1].substring(0, 5);
        }
        return isoString;
    } catch {
        return '--:--';
    }
}

/**
 * Converts ISO date string ("2026-09-12") to abbreviated day name and date ("Sat, Sep 12")
 * @param {string} dateString
 * @returns {{ dayName: string, shortDate: string }}
 */
function formatForecastDate(dateString) {
    try {
        const date = new Date(dateString + 'T00:00:00');
        const dayName = date.toLocaleDateString('en-US', { weekday: 'short' });
        const shortDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        return { dayName, shortDate };
    } catch {
        return { dayName: 'Day', shortDate: dateString };
    }
}

/* ==========================================================================
   4. UI State Management: Loading & Error Notifications
   ========================================================================== */

/**
 * Displays or hides the loading skeleton and disables search controls
 * @param {boolean} show - True to display loading state
 */
function setLoading(show) {
    appState.isLoading = show;
    if (show) {
        DOM.loadingState.hidden = false;
        DOM.weatherDisplay.hidden = true;
        DOM.btnSearch.disabled = true;
        DOM.btnGeolocation.disabled = true;
        hideError();
    } else {
        DOM.loadingState.hidden = true;
        DOM.weatherDisplay.hidden = false;
        DOM.btnSearch.disabled = false;
        DOM.btnGeolocation.disabled = false;
    }
}

/**
 * Displays an accessible error banner with title and explanation
 * @param {string} title
 * @param {string} message
 */
function showError(title, message) {
    DOM.errorTitle.textContent = title;
    DOM.errorMessage.textContent = message;
    DOM.errorBanner.hidden = false;
    DOM.errorBanner.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Dismisses and hides the error banner
 */
function hideError() {
    DOM.errorBanner.hidden = true;
}

/* ==========================================================================
   5. Asynchronous Meteorological Data Fetching (Fetch API & Async/Await)
   ========================================================================== */

/**
 * Step 1: Geocodes city name into latitude & longitude coordinates using Open-Meteo Geocoding API.
 * Free, non-commercial, zero API key required.
 *
 * @param {string} cityName
 * @returns {Promise<{ name: string, country: string, admin1: string, latitude: number, longitude: number }>}
 */
async function geocodeCity(cityName) {
    const sanitizedQuery = encodeURIComponent(cityName.trim());
    const geocodeUrl = `https://geocoding-api.open-meteo.com/v1/search?name=${sanitizedQuery}&count=1&language=en&format=json`;

    // Perform asynchronous HTTP request via fetch()
    const response = await fetch(geocodeUrl);

    // Defensive check: Verify HTTP response status (200-299)
    if (!response.ok) {
        throw new Error(`Geocoding server responded with HTTP status ${response.status} (${response.statusText})`);
    }

    const data = await response.json();

    // Verify search matches returned
    if (!data.results || data.results.length === 0) {
        throw new Error(`Location "${cityName}" was not found. Please check spelling and try again.`);
    }

    const bestMatch = data.results[0];
    return {
        name: bestMatch.name,
        country: bestMatch.country || '',
        admin1: bestMatch.admin1 || '',
        latitude: bestMatch.latitude,
        longitude: bestMatch.longitude
    };
}

/**
 * Step 2: Fetches comprehensive meteorological telemetry and 5-day daily forecasts
 * using Open-Meteo Forecast API.
 *
 * @param {number} latitude
 * @param {number} longitude
 * @returns {Promise<Object>}
 */
async function fetchForecastByCoordinates(latitude, longitude) {
    const forecastUrl = `https://api.open-meteo.com/v1/forecast?latitude=${latitude}&longitude=${longitude}&current=temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,weather_code,surface_pressure,wind_speed_10m,wind_direction_10m&daily=weather_code,temperature_2m_max,temperature_2m_min,sunrise,sunset,uv_index_max&timezone=auto&forecast_days=6`;

    const response = await fetch(forecastUrl);

    if (!response.ok) {
        throw new Error(`Meteorological API responded with HTTP status ${response.status} (${response.statusText})`);
    }

    const weatherData = await response.json();
    return weatherData;
}

/**
 * Master Controller: Fetches weather data for a given city query
 * Orchestrates geocoding + telemetry fetch wrapped in defensive try/catch/finally.
 *
 * @param {string} cityName
 */
async function getWeatherDataForCity(cityName) {
    const trimmedCity = cityName.trim();

    // Defensive input validation
    if (!trimmedCity) {
        showError('Invalid Input', 'Please enter a valid city or region name before searching.');
        return;
    }

    setLoading(true);

    try {
        // Step 1: Geocode location
        const location = await geocodeCity(trimmedCity);

        // Step 2: Fetch current and daily forecast telemetry
        const telemetry = await fetchForecastByCoordinates(location.latitude, location.longitude);

        // Cache into normalized application state
        appState.currentCity = location.name;
        appState.currentWeather = {
            location,
            telemetry
        };

        // Render to DOM
        renderWeatherToDOM();

        // Save preference to LocalStorage
        savePreferences();

        // Update search input to match resolved location name
        DOM.cityInput.value = location.name;
        DOM.btnClearInput.hidden = false;

    } catch (error) {
        console.error('[Weather Fetch Error]:', error);
        showError(
            'Weather Fetch Failed',
            error.message || 'An unexpected error occurred while contacting meteorological servers.'
        );
    } finally {
        // Guarantee loading indicator is removed even if errors occur
        setLoading(false);
    }
}

/**
 * Geolocation Handler: Fetches telemetry using browser navigator.geolocation
 */
async function getWeatherByCurrentLocation() {
    if (!navigator.geolocation) {
        showError('Geolocation Unavailable', 'Geolocation services are not supported by your current browser.');
        return;
    }

    setLoading(true);

    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;
            try {
                // Reverse geocoding or direct coordinates fetch
                const telemetry = await fetchForecastByCoordinates(latitude, longitude);

                appState.currentCity = 'Current Location';
                appState.currentWeather = {
                    location: {
                        name: 'Current Coordinates',
                        country: `${latitude.toFixed(2)}°, ${longitude.toFixed(2)}°`,
                        admin1: 'GPS Telemetry',
                        latitude,
                        longitude
                    },
                    telemetry
                };

                renderWeatherToDOM();
                savePreferences();
                DOM.cityInput.value = '';
                DOM.btnClearInput.hidden = true;
            } catch (error) {
                console.error('[Geolocation Weather Error]:', error);
                showError('GPS Fetch Failed', error.message);
            } finally {
                setLoading(false);
            }
        },
        (error) => {
            setLoading(false);
            let message = 'Unable to retrieve your location.';
            if (error.code === error.PERMISSION_DENIED) {
                message = 'Location access was denied. Please search for your city manually or allow location access in your browser.';
            } else if (error.code === error.POSITION_UNAVAILABLE) {
                message = 'Location information is currently unavailable.';
            } else if (error.code === error.TIMEOUT) {
                message = 'The request to acquire user location timed out.';
            }
            showError('Location Error', message);
        },
        { timeout: 10000, enableHighAccuracy: true }
    );
}

/* ==========================================================================
   6. DOM Rendering & Visual State Updates
   ========================================================================== */

/**
 * Re-renders all weather elements to the DOM using cached state
 * Supports instant unit toggling (°C / °F) without network requests.
 */
function renderWeatherToDOM() {
    if (!appState.currentWeather) return;

    const { location, telemetry } = appState.currentWeather;
    const current = telemetry.current;
    const daily = telemetry.daily;

    // 1. Location Header
    DOM.currentCity.textContent = location.name;
    const locationSubtitle = [location.admin1, location.country].filter(Boolean).join(', ');
    DOM.currentCountry.textContent = locationSubtitle || 'Global Station';

    // Timestamp & Timezone
    const now = new Date();
    const timeFormatted = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    DOM.currentTime.textContent = `Local Station Time: ${timeFormatted} • Timezone: ${telemetry.timezone || 'Auto'}`;

    // 2. Weather Code & Condition
    const weatherInfo = interpretWeatherCode(current.weather_code, current.is_day);
    DOM.weatherCondition.textContent = weatherInfo.description;
    DOM.weatherIcon.textContent = weatherInfo.icon;
    DOM.weatherIcon.setAttribute('aria-label', weatherInfo.description);

    // Apply dynamic ambient theme to document body
    document.body.className = weatherInfo.themeClass;

    // 3. Current Temperature & High/Low
    DOM.currentTemp.textContent = formatTemperature(current.temperature_2m);
    DOM.currentUnit.innerHTML = appState.unit === 'fahrenheit' ? '&deg;F' : '&deg;C';

    DOM.feelsLikeTemp.textContent = formatTemperature(current.apparent_temperature, true);

    const todayHigh = daily && daily.temperature_2m_max ? daily.temperature_2m_max[0] : current.temperature_2m;
    const todayLow = daily && daily.temperature_2m_min ? daily.temperature_2m_min[0] : current.temperature_2m;
    DOM.highLowTemp.textContent = `${formatTemperature(todayHigh, true)} / ${formatTemperature(todayLow, true)}`;

    // 4. Metrics Grid
    // Metric 1: Humidity
    DOM.metricHumidity.textContent = Math.round(current.relative_humidity_2m);
    DOM.humidityBar.style.width = `${Math.min(100, Math.max(0, current.relative_humidity_2m))}%`;
    if (current.relative_humidity_2m > 70) {
        DOM.humidityCaption.textContent = 'High humidity • Muggy atmosphere';
    } else if (current.relative_humidity_2m < 30) {
        DOM.humidityCaption.textContent = 'Low humidity • Dry air';
    } else {
        DOM.humidityCaption.textContent = 'Comfortable ambient moisture';
    }

    // Metric 2: Wind Speed & Direction
    const wind = formatWindSpeed(current.wind_speed_10m);
    DOM.metricWindSpeed.textContent = wind.value;
    DOM.metricWindUnit.textContent = wind.unit;
    const windDirection = getCompassDirection(current.wind_direction_10m);
    DOM.metricWindDirection.textContent = `${windDirection} (${Math.round(current.wind_direction_10m)}°)`;
    DOM.windCompassArrow.style.transform = `rotate(${current.wind_direction_10m}deg)`;

    // Metric 3: Atmospheric Pressure
    DOM.metricPressure.textContent = current.surface_pressure ? current.surface_pressure.toFixed(1) : '--';

    // Metric 4: UV Index
    const uvIndex = daily && daily.uv_index_max ? daily.uv_index_max[0] : 0;
    DOM.metricUv.textContent = uvIndex !== undefined ? uvIndex.toFixed(1) : '--';
    updateUvBadge(uvIndex);

    // Metric 5: Precipitation
    DOM.metricPrecip.textContent = current.precipitation !== undefined ? current.precipitation.toFixed(1) : '0.0';
    DOM.precipCaption.textContent = current.precipitation > 0 ? 'Active precipitation observed' : 'No active rainfall detected';

    // Metric 6: Sun Schedule
    if (daily && daily.sunrise && daily.sunset) {
        DOM.metricSunrise.textContent = formatIsoTime(daily.sunrise[0]);
        DOM.metricSunset.textContent = formatIsoTime(daily.sunset[0]);
    }

    // 5. Render 5-Day Extended Forecast Strip
    renderForecastStrip(daily);
}

/**
 * Updates UV index rating badge color and label
 * @param {number} uv
 */
function updateUvBadge(uv) {
    DOM.metricUvBadge.className = 'metric-badge';
    if (uv <= 2.9) {
        DOM.metricUvBadge.classList.add('low');
        DOM.metricUvBadge.textContent = 'Low';
    } else if (uv <= 5.9) {
        DOM.metricUvBadge.classList.add('moderate');
        DOM.metricUvBadge.textContent = 'Moderate';
    } else {
        DOM.metricUvBadge.classList.add('high');
        DOM.metricUvBadge.textContent = 'High';
    }
}

/**
 * Renders the 5-day daily forecast strip cards
 * @param {Object} daily - Daily forecast object from Open-Meteo
 */
function renderForecastStrip(daily) {
    if (!daily || !daily.time || daily.time.length === 0) return;

    // Clear previous forecast cards
    DOM.forecastContainer.innerHTML = '';

    // Render up to 5 days (starting from day 1, or day 0 if desired; day 1-5 represents future 5 days)
    const count = Math.min(6, daily.time.length);

    for (let i = 1; i < count; i++) {
        const dateStr = daily.time[i];
        const { dayName, shortDate } = formatForecastDate(dateStr);
        const code = daily.weather_code ? daily.weather_code[i] : 0;
        const weatherInfo = interpretWeatherCode(code, 1);
        const maxTemp = formatTemperature(daily.temperature_2m_max[i]);
        const minTemp = formatTemperature(daily.temperature_2m_min[i]);

        const card = document.createElement('div');
        card.className = 'forecast-day-card';
        card.setAttribute('role', 'listitem');
        card.setAttribute('tabindex', '0');
        card.setAttribute('aria-label', `${dayName}, ${weatherInfo.description}, High ${maxTemp}°, Low ${minTemp}°`);

        card.innerHTML = `
            <span class="forecast-day-name">${dayName}</span>
            <span class="forecast-date">${shortDate}</span>
            <div class="forecast-icon" role="img" aria-hidden="true">${weatherInfo.icon}</div>
            <span class="forecast-condition-desc">${weatherInfo.description}</span>
            <div class="forecast-temps-row">
                <span class="forecast-high">${maxTemp}&deg;</span>
                <span class="forecast-low">${minTemp}&deg;</span>
            </div>
        `;

        DOM.forecastContainer.appendChild(card);
    }
}

/* ==========================================================================
   7. Preferences & LocalStorage Persistence
   ========================================================================== */

/**
 * Saves user preferences to browser LocalStorage
 */
function savePreferences() {
    try {
        const prefs = {
            city: appState.currentCity,
            unit: appState.unit,
            provider: appState.provider
        };
        localStorage.setItem(STORAGE_KEYS.PREFERENCES, JSON.stringify(prefs));
    } catch (e) {
        console.warn('LocalStorage save failed:', e);
    }
}

/**
 * Loads user preferences from browser LocalStorage
 */
function loadPreferences() {
    try {
        const saved = localStorage.getItem(STORAGE_KEYS.PREFERENCES);
        if (saved) {
            const prefs = JSON.parse(saved);
            if (prefs.city) appState.currentCity = prefs.city;
            if (prefs.unit) setTemperatureUnit(prefs.unit, false);
            if (prefs.provider) appState.provider = prefs.provider;
        }

        const savedApiKey = localStorage.getItem(STORAGE_KEYS.API_KEY);
        if (savedApiKey) {
            appState.apiKey = savedApiKey;
            DOM.owmApiKeyInput.value = savedApiKey;
        }
    } catch (e) {
        console.warn('LocalStorage load failed:', e);
    }
}

/**
 * Sets active temperature unit and re-renders
 * @param {'celsius' | 'fahrenheit'} unit
 * @param {boolean} shouldSave
 */
function setTemperatureUnit(unit, shouldSave = true) {
    appState.unit = unit;

    if (unit === 'celsius') {
        DOM.unitCelsius.classList.add('active');
        DOM.unitCelsius.setAttribute('aria-checked', 'true');
        DOM.unitFahrenheit.classList.remove('active');
        DOM.unitFahrenheit.setAttribute('aria-checked', 'false');
    } else {
        DOM.unitFahrenheit.classList.add('active');
        DOM.unitFahrenheit.setAttribute('aria-checked', 'true');
        DOM.unitCelsius.classList.remove('active');
        DOM.unitCelsius.setAttribute('aria-checked', 'false');
    }

    if (appState.currentWeather) {
        renderWeatherToDOM();
    }

    if (shouldSave) {
        savePreferences();
    }
}

/* ==========================================================================
   8. Event Listeners & Interactive Handlers
   ========================================================================== */

function setupEventListeners() {
    // 1. Search Form Submission
    DOM.searchForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const city = DOM.cityInput.value.trim();
        if (city) {
            getWeatherDataForCity(city);
        } else {
            showError('Empty Search', 'Please enter a city name to search.');
        }
    });

    // 2. City Input text changes & clear button
    DOM.cityInput.addEventListener('input', () => {
        DOM.btnClearInput.hidden = DOM.cityInput.value.length === 0;
        hideError();
    });

    DOM.btnClearInput.addEventListener('click', () => {
        DOM.cityInput.value = '';
        DOM.btnClearInput.hidden = true;
        DOM.cityInput.focus();
    });

    // 3. Geolocation button
    DOM.btnGeolocation.addEventListener('click', () => {
        getWeatherByCurrentLocation();
    });

    // 4. Unit Toggles (°C / °F)
    DOM.unitCelsius.addEventListener('click', () => setTemperatureUnit('celsius'));
    DOM.unitFahrenheit.addEventListener('click', () => setTemperatureUnit('fahrenheit'));

    // 5. Quick City Chips
    DOM.quickChips.forEach((chip) => {
        chip.addEventListener('click', () => {
            const cityName = chip.dataset.city;
            if (cityName) {
                DOM.cityInput.value = cityName;
                DOM.btnClearInput.hidden = false;
                getWeatherDataForCity(cityName);
            }
        });
    });

    // 6. Dismiss Error Banner
    DOM.btnDismissError.addEventListener('click', hideError);

    // 7. Settings Modal
    DOM.btnOpenSettings.addEventListener('click', () => {
        DOM.settingsModal.hidden = false;
        DOM.providerSelect.value = appState.provider;
        DOM.owmKeyGroup.hidden = appState.provider !== 'openweathermap';
        const unitRadio = document.querySelector(`input[name="modal-temp-unit"][value="${appState.unit}"]`);
        if (unitRadio) unitRadio.checked = true;
    });

    DOM.btnCloseSettings.addEventListener('click', () => {
        DOM.settingsModal.hidden = true;
    });

    DOM.settingsModal.addEventListener('click', (e) => {
        if (e.target === DOM.settingsModal) {
            DOM.settingsModal.hidden = true;
        }
    });

    DOM.providerSelect.addEventListener('change', (e) => {
        DOM.owmKeyGroup.hidden = e.target.value !== 'openweathermap';
    });

    DOM.btnSaveSettings.addEventListener('click', () => {
        appState.provider = DOM.providerSelect.value;
        const keyVal = DOM.owmApiKeyInput.value.trim();
        if (keyVal) {
            appState.apiKey = keyVal;
            localStorage.setItem(STORAGE_KEYS.API_KEY, keyVal);
        } else {
            localStorage.removeItem(STORAGE_KEYS.API_KEY);
        }

        const selectedUnit = document.querySelector('input[name="modal-temp-unit"]:checked');
        if (selectedUnit) {
            setTemperatureUnit(selectedUnit.value);
        }

        DOM.settingsModal.hidden = true;
        savePreferences();
    });

    DOM.btnResetSettings.addEventListener('click', () => {
        DOM.providerSelect.value = 'open-meteo';
        DOM.owmKeyGroup.hidden = true;
        DOM.owmApiKeyInput.value = '';
        localStorage.removeItem(STORAGE_KEYS.API_KEY);
        appState.provider = 'open-meteo';
        appState.apiKey = '';
        setTemperatureUnit('celsius');
        DOM.settingsModal.hidden = true;
        savePreferences();
    });

    // Escape key closes modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !DOM.settingsModal.hidden) {
            DOM.settingsModal.hidden = true;
        }
    });
}

/* ==========================================================================
   9. Initialization
   ========================================================================== */

function initializeWeatherApp() {
    loadPreferences();
    setupEventListeners();

    // Fetch initial weather for remembered city or default London
    const initialCity = appState.currentCity || 'London';
    DOM.cityInput.value = initialCity;
    DOM.btnClearInput.hidden = false;
    getWeatherDataForCity(initialCity);
}

// Bootstrap application once DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeWeatherApp);
} else {
    initializeWeatherApp();
}
