document.addEventListener("DOMContentLoaded", () => {
  const calendarEl = document.getElementById("calendar");

  // Sécurité si le conteneur ou la lib ne sont pas chargés
  if (!calendarEl || !window.FullCalendar) {
    console.warn("Calendrier introuvable ou FullCalendar non chargé.");
    return;
  }

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "timeGridWeek",
    locale: "fr",
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,timeGridDay",
    },
    events: [
      {
        title: "Réunion projet",
        start: "2025-12-16T10:00:00",
        end: "2025-12-16T11:00:00",
      },
      {
        title: "Session focus",
        start: "2025-12-17T14:00:00",
        end: "2025-12-17T15:30:00",
      },
    ],
  });

  calendar.render();
});
