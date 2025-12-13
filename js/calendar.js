/**
 * Script pour le calendrier interactif
 */

let currentDate = new Date(currentYearMonth + '-01');
let selectedDate = null;

// Initialise le calendrier
document.addEventListener('DOMContentLoaded', () => {
    renderCalendar();
    setupEventListeners();
});

/**
 * Rend le calendrier pour le mois actuel
 */
function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    
    // Premier jour du mois
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    
    // Jour de la semaine du premier jour (0 = dimanche, ajusté pour lundi = 0)
    let startDay = firstDay.getDay() - 1;
    if (startDay < 0) startDay = 6; // Dimanche devient 6
    
    const daysInMonth = lastDay.getDate();
    const daysContainer = document.getElementById('calendar-days');
    daysContainer.innerHTML = '';
    
    // Jours vides avant le premier jour
    for (let i = 0; i < startDay; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'col day empty';
        daysContainer.appendChild(emptyDay);
    }
    
    // Jours du mois
    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        dayElement.className = 'col day';
        dayElement.dataset.date = dateStr;
        
        // Vérifie si c'est aujourd'hui
        const today = new Date();
        if (year === today.getFullYear() && month === today.getMonth() && day === today.getDate()) {
            dayElement.classList.add('today');
        }
        
        // Ajoute les événements du jour
        const dayEvents = events.filter(e => {
            const eventDate = new Date(e.start_datetime);
            return eventDate.getFullYear() === year && 
                   eventDate.getMonth() === month && 
                   eventDate.getDate() === day;
        });
        
        dayElement.innerHTML = `
            <span class="day-number">${day}</span>
            ${dayEvents.length > 0 ? `<div class="event-indicator">${dayEvents.length}</div>` : ''}
        `;
        
        // Ajoute les événements visuels
        dayEvents.forEach(event => {
            const eventEl = document.createElement('div');
            eventEl.className = `event bg-${getCategoryColor(event.category)} text-white p-1 rounded mb-1`;
            eventEl.textContent = event.title;
            eventEl.title = `${event.title} - ${formatTime(event.start_datetime)}`;
            dayElement.appendChild(eventEl);
        });
        
        // Clic sur le jour
        dayElement.addEventListener('click', () => {
            selectedDate = dateStr;
            showDayEvents(dateStr);
        });
        
        daysContainer.appendChild(dayElement);
    }
    
    // Met à jour le titre du mois
    const monthNames = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 
                        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
    document.getElementById('month-year').textContent = 
        `${monthNames[month]} ${year}`;
}

/**
 * Configure les écouteurs d'événements
 */
function setupEventListeners() {
    // Boutons précédent/suivant
    document.getElementById('prev-month').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        loadMonthEvents();
    });
    
    document.getElementById('next-month').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        loadMonthEvents();
    });
    
    // Formulaire d'événement
    document.getElementById('eventForm').addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const action = document.getElementById('event_id').value ? '/calendar/event/update' : '/calendar/event/create';
        
        fetch(action, {
            method: 'POST',
            body: formData
        }).then(() => {
            location.reload();
        });
    });
    
    // Bouton ajouter événement
    document.getElementById('add-event-btn').addEventListener('click', () => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('dayEventsModal'));
        modal.hide();
        
        const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));
        document.getElementById('eventModalTitle').textContent = 'Nouvel événement';
        document.getElementById('event_id').value = '';
        document.getElementById('eventForm').action = '/calendar/event/create';
        
        // Pré-remplit la date sélectionnée
        if (selectedDate) {
            const dateTime = selectedDate + 'T09:00';
            document.getElementById('event-start').value = dateTime;
            document.getElementById('event-end').value = selectedDate + 'T10:00';
        }
        
        eventModal.show();
    });
}

/**
 * Charge les événements du mois depuis le serveur
 */
function loadMonthEvents() {
    const yearMonth = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}`;
    
    window.location.href = `/calendar?month=${yearMonth}`;
}

/**
 * Affiche les événements d'un jour
 */
function showDayEvents(dateStr) {
    fetch(`/calendar/day-events?date=${dateStr}`)
        .then(response => response.json())
        .then(data => {
            const modal = new bootstrap.Modal(document.getElementById('dayEventsModal'));
            const title = document.getElementById('dayEventsTitle');
            const content = document.getElementById('dayEventsContent');
            
            const date = new Date(dateStr);
            title.textContent = `Événements du ${date.toLocaleDateString('fr-FR', { 
                weekday: 'long', 
                day: 'numeric', 
                month: 'long' 
            })}`;
            
            if (data.events && data.events.length > 0) {
                content.innerHTML = data.events.map(event => `
                    <div class="card mb-2">
                        <div class="card-body">
                            <h6 class="card-title">${event.title}</h6>
                            ${event.description ? `<p class="card-text">${event.description}</p>` : ''}
                            <small class="text-muted">
                                ${formatDateTime(event.start_datetime)} - ${formatDateTime(event.end_datetime)}
                            </small>
                            <span class="badge bg-${getCategoryColor(event.category)} ms-2">${event.category}</span>
                        </div>
                    </div>
                `).join('');
            } else {
                content.innerHTML = '<p class="text-muted">Aucun événement ce jour.</p>';
            }
            
            modal.show();
        })
        .catch(error => {
            console.error('Erreur:', error);
        });
}

/**
 * Retourne la couleur Bootstrap selon la catégorie
 */
function getCategoryColor(category) {
    const colors = {
        'travail': 'primary',
        'personnel': 'success',
        'autre': 'secondary'
    };
    return colors[category] || 'secondary';
}

/**
 * Formate une date/heure
 */
function formatDateTime(dateTimeStr) {
    const date = new Date(dateTimeStr);
    return date.toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Formate une heure
 */
function formatTime(dateTimeStr) {
    const date = new Date(dateTimeStr);
    return date.toLocaleTimeString('fr-FR', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

