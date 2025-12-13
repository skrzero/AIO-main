document.addEventListener("DOMContentLoaded", () => {
  const menuBtn = document.getElementById("menu-btn");
  const sidebar = document.getElementById("sidebar");
  const closeBtn = document.getElementById("close-btn");
  const overlay = document.getElementById("overlay");

  function openMenu() {
    sidebar.classList.add("active");
    overlay.classList.add("active");
  }

  function closeMenu() {
    sidebar.classList.remove("active");
    overlay.classList.remove("active");
  }

  menuBtn.addEventListener("click", openMenu);
  closeBtn.addEventListener("click", closeMenu);
  overlay.addEventListener("click", closeMenu);
});

// Configuration API météo
const apiKey = "f87082ca227e4c50a5325643eb07cc4a";
const defaultCity = "Dax"; // Ville par défaut si géolocalisation échoue

/**
 * Récupère la météo par coordonnées GPS
 */
async function getWeatherByCoords(lat, lon) {
  const url = `https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&units=metric&lang=fr&appid=${apiKey}`;
  const response = await fetch(url);
  if (!response.ok) throw new Error("Erreur API météo");
  return await response.json();
}

/**
 * Récupère la météo par nom de ville
 */
async function getWeatherByCity(city) {
  const url = `https://api.openweathermap.org/data/2.5/weather?q=${city}&units=metric&lang=fr&appid=${apiKey}`;
  const response = await fetch(url);
  if (!response.ok) throw new Error("Erreur API météo");
  return await response.json();
}

/**
 * Formate la description météo avec première lettre en majuscule
 */
function capitalizeFirst(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

/**
 * Affiche les données météo dans le DOM
 */
function displayWeather(data) {
  const weatherDiv = document.getElementById("weather");
  
  const temp = Math.round(data.main.temp);
  const feelsLike = Math.round(data.main.feels_like);
  const desc = capitalizeFirst(data.weather[0].description);
  const icon = `https://openweathermap.org/img/wn/${data.weather[0].icon}@2x.png`;
  const humidity = data.main.humidity;
  const windSpeed = Math.round(data.wind.speed * 3.6); // Conversion m/s en km/h
  const pressure = data.main.pressure;
  const cityName = data.name;
  const country = data.sys.country || "";

  // Détermine le fond selon le temps
  const weatherId = data.weather[0].id;
  let weatherClass = "weather-card";
  if (weatherId >= 200 && weatherId < 300) {
    weatherClass += " weather-storm";
  } else if (weatherId >= 300 && weatherId < 600) {
    weatherClass += " weather-rain";
  } else if (weatherId >= 600 && weatherId < 700) {
    weatherClass += " weather-snow";
  } else if (weatherId >= 700 && weatherId < 800) {
    weatherClass += " weather-fog";
  } else if (weatherId === 800) {
    weatherClass += " weather-clear";
  } else if (weatherId > 800) {
    weatherClass += " weather-clouds";
  }

  weatherDiv.className = weatherClass;
  weatherDiv.innerHTML = `
    <div class="weather-header">
      <div class="weather-location">
        <h3 class="weather-city">${cityName}${country ? `, ${country}` : ""}</h3>
        <p class="weather-description">${desc}</p>
      </div>
      <div class="weather-icon-container">
        <img src="${icon}" alt="${desc}" class="weather-icon" />
      </div>
    </div>
    
    <div class="weather-main">
      <div class="weather-temp">
        <span class="temp-value">${temp}</span>
        <span class="temp-unit">°C</span>
      </div>
      <p class="weather-feels-like">Ressenti ${feelsLike}°C</p>
    </div>
    
    <div class="weather-details">
      <div class="weather-detail-item">
        <span class="detail-icon">💧</span>
        <span class="detail-label">Humidité</span>
        <span class="detail-value">${humidity}%</span>
      </div>
      <div class="weather-detail-item">
        <span class="detail-icon">💨</span>
        <span class="detail-label">Vent</span>
        <span class="detail-value">${windSpeed} km/h</span>
      </div>
      <div class="weather-detail-item">
        <span class="detail-icon">📊</span>
        <span class="detail-label">Pression</span>
        <span class="detail-value">${pressure} hPa</span>
      </div>
    </div>
  `;
  
  // Animation d'apparition
  weatherDiv.style.opacity = "0";
  setTimeout(() => {
    weatherDiv.style.transition = "opacity 0.5s ease-in";
    weatherDiv.style.opacity = "1";
  }, 100);
}

/**
 * Affiche un message d'erreur
 */
function displayError(message) {
  const weatherDiv = document.getElementById("weather");
  weatherDiv.className = "weather-card weather-error";
  weatherDiv.innerHTML = `
    <div class="weather-error-content">
      <span class="error-icon">⚠️</span>
      <p class="error-message">${message}</p>
      <button class="error-retry" onclick="loadWeather()">Réessayer</button>
    </div>
  `;
}

/**
 * Affiche le loader
 */
function displayLoader() {
  const weatherDiv = document.getElementById("weather");
  weatherDiv.className = "weather-card weather-loading";
  weatherDiv.innerHTML = `
    <div class="weather-loader">
      <div class="loader-spinner"></div>
      <p>Chargement de la météo...</p>
    </div>
  `;
}

/**
 * Fonction principale de chargement de la météo
 */
async function loadWeather() {
  displayLoader();
  
  try {
    // Essaie d'abord la géolocalisation
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        async (position) => {
          try {
            const data = await getWeatherByCoords(
              position.coords.latitude,
              position.coords.longitude
            );
            displayWeather(data);
          } catch (error) {
            console.error("Erreur météo par coordonnées:", error);
            // Fallback sur la ville par défaut
            try {
              const data = await getWeatherByCity(defaultCity);
              displayWeather(data);
            } catch (fallbackError) {
              displayError("Impossible de charger la météo");
            }
          }
        },
        async (error) => {
          console.warn("Géolocalisation refusée ou échouée:", error);
          // Fallback sur la ville par défaut
          try {
            const data = await getWeatherByCity(defaultCity);
            displayWeather(data);
          } catch (fallbackError) {
            displayError("Impossible de charger la météo");
          }
        },
        { timeout: 5000 }
      );
    } else {
      // Pas de support géolocalisation, utilise la ville par défaut
      const data = await getWeatherByCity(defaultCity);
      displayWeather(data);
    }
  } catch (error) {
    console.error("Erreur générale météo:", error);
    displayError("Erreur de connexion à l'API météo");
  }
}

// Expose la fonction globalement pour le bouton de retry
window.loadWeather = loadWeather;

// Charge la météo au chargement de la page
loadWeather();
