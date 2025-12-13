/**
 * Script pour les mémos avec drag-and-drop
 */

document.addEventListener('DOMContentLoaded', () => {
    initSortable();
    initEditButtons();
});

/**
 * Initialise le drag-and-drop avec SortableJS
 */
function initSortable() {
    const container = document.getElementById('memo-container');
    
    if (!container) return;
    
    new Sortable(container, {
        animation: 150,
        handle: '.memo-card',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        dragClass: 'sortable-drag',
        onEnd: function(evt) {
            // Récupère les nouvelles positions
            const positions = {};
            const memoCards = container.querySelectorAll('.memo-card');
            
            memoCards.forEach((card, index) => {
                positions[card.dataset.id] = index + 1;
            });
            
            // Envoie les nouvelles positions au serveur
            fetch('/memos/update-positions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    positions: positions
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Erreur:', data.error);
                    // Recharge la page en cas d'erreur
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                location.reload();
            });
        }
    });
}

/**
 * Initialise les boutons d'édition
 */
function initEditButtons() {
    document.querySelectorAll('.edit-memo').forEach(btn => {
        btn.addEventListener('click', () => {
            const memoId = btn.dataset.id;
            const memoTitle = btn.dataset.title;
            const memoContent = btn.dataset.content;
            
            // Remplit le formulaire d'édition
            document.getElementById('edit-memo-id').value = memoId;
            document.getElementById('edit-memo-title').value = memoTitle;
            document.getElementById('edit-memo-content').value = memoContent;
            
            // Ouvre le modal
            const modal = new bootstrap.Modal(document.getElementById('editMemoModal'));
            modal.show();
        });
    });
}

// Styles CSS pour le drag-and-drop (ajoutés dynamiquement)
const style = document.createElement('style');
style.textContent = `
    .sortable-ghost {
        opacity: 0.4;
        background: #f0f0f0;
    }
    
    .sortable-chosen {
        cursor: grabbing;
    }
    
    .sortable-drag {
        opacity: 0.8;
    }
    
    .memo-card {
        cursor: grab;
        transition: transform 0.2s ease;
    }
    
    .memo-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15) !important;
    }
    
    .memo-card:active {
        cursor: grabbing;
    }
    
    .event-indicator {
        position: absolute;
        top: 5px;
        right: 5px;
        background: #007bff;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: bold;
    }
    
    .day {
        min-height: 100px;
        border: 1px solid #dee2e6;
        padding: 5px;
        position: relative;
        cursor: pointer;
        transition: background-color 0.2s ease;
    }
    
    .day:hover {
        background-color: #f8f9fa;
    }
    
    .day.today {
        background-color: #e7f3ff;
        font-weight: bold;
    }
    
    .day.empty {
        background-color: #f8f9fa;
        cursor: default;
    }
    
    .day-number {
        display: block;
        font-size: 0.9rem;
        margin-bottom: 5px;
    }
    
    .event {
        font-size: 0.75rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
`;
document.head.appendChild(style);

