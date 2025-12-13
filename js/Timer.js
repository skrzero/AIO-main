const timeDisplay = document.querySelector(".minuteur");
const startStopBtn = document.querySelector(".btn-start");

if (timeDisplay && startStopBtn) {
  let heures = 0;
  let minutes = 0;
  let secondes = 0;
  let enCours = false;
  let intervalId = null;

  function demarrerMinuteur() {
    intervalId = setInterval(() => {
      secondes++;
      if (secondes === 60) {
        secondes = 0;
        minutes++;
      }
      if (minutes === 60) {
        minutes = 0;
        heures++;
      }
      majTimer();
    }, 1000);
  }

  function format(val) {
    if (val < 10) {
      return "0" + val;
    } else {
      return val;
    }
  }

  function majTimer() {
    timeDisplay.textContent = `${format(heures)}:${format(minutes)}:${format(secondes)}`;
  }

  function pauseMinuteur() {
    clearInterval(intervalId);
  }

  startStopBtn.onclick = () => {
    if (!enCours) {
      startStopBtn.textContent = "Pause";
      demarrerMinuteur();
      enCours = true;
    } else {
      startStopBtn.textContent = "Démarrer";
      pauseMinuteur();
      enCours = false;
    }
  };
}