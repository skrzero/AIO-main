const addNoteBtn = document.getElementById("addNoteBtn");
    const noteInput = document.getElementById("noteInput");
    const notesList = document.getElementById("notesList");

    addNoteBtn.addEventListener("click", () => {
      const text = noteInput.value.trim();
      if (text !== "") {
        // Création d'une carte Bootstrap
        const col = document.createElement("div");
        col.className = "col-12 col-md-6 col-lg-4";
        col.innerHTML = `
          <div class="card shadow-sm">
            <div class="card-body">
              <p class="card-text">${text}</p>
              <button class="btn btn-sm btn-danger deleteBtn">Supprimer</button>
            </div>
          </div>
        `;
        // Ajout dans la liste
        notesList.prepend(col);
        noteInput.value = "";

        // Gestion du bouton supprimer
        col.querySelector(".deleteBtn").addEventListener("click", () => {
          col.remove();
        });
      }
    });