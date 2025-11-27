document.getElementById('favoriBtn').addEventListener('click', async function() {
    const btn = this;
    const recetteId = btn.dataset.recetteId;
    const icon = document.getElementById('favoriIcon');
    const text = document.getElementById('favoriText');
    
    btn.disabled = true;
    
    try {
        const response = await fetch(`/api/favori/toggle/${recetteId}`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'}
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (data.isFavorite) {
                icon.classList.add('bi-heart-fill');
                icon.classList.remove('bi-heart');
                text.textContent = 'Retirer des favoris';
            } else {
                icon.classList.add('bi-heart');
                icon.classList.remove('bi-heart-fill');
                text.textContent = 'Ajouter aux Favoris';
            }
            icon.style.transform = 'scale(1.3)';
            setTimeout(() => icon.style.transform = 'scale(1)', 200);
        }
    } catch (error) {
        console.error('Erreur:', error);
    } finally {
        btn.disabled = false;
    }
});

document.querySelectorAll('.form-check-input').forEach(checkbox => {
    const key = 'recette_{{ recette.id }}_' + checkbox.id;
    if (localStorage.getItem(key) === 'true') {
        checkbox.checked = true;
        checkbox.nextElementSibling.style.textDecoration = 'line-through';
        checkbox.nextElementSibling.style.color = '#999';
    }
    checkbox.addEventListener('change', function() {
        localStorage.setItem(key, this.checked);
        if (this.checked) {
            this.nextElementSibling.style.textDecoration = 'line-through';
            this.nextElementSibling.style.color = '#999';
        } else {
            this.nextElementSibling.style.textDecoration = 'none';
            this.nextElementSibling.style.color = '#333';
        }
    });
});
