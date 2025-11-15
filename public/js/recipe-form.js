// JavaScript pour formulaires recettes (new/edit)
// Gestion dynamique des ingrédients et étapes

document.addEventListener('DOMContentLoaded', function() {
    console.log('Recipe form script loaded');
    
    // Configuration
    const ingredientsContainer = document.getElementById('ingredients-collection');
    const addIngredientBtn = document.getElementById('add-ingredient');
    const etapesContainer = document.getElementById('etapes-collection');
    const addEtapeBtn = document.getElementById('add-etape');
    const etapesHidden = document.getElementById('etapes-hidden');
    
    let ingredientIndex = document.querySelectorAll('.ingredient-row').length;
    let etapeIndex = 1;
    
    // 
    // GESTION DES INGRÉDIENTS
    // 
    
    // Supprimer ingrédient
    if (ingredientsContainer) {
        ingredientsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-ingredient')) {
                e.target.closest('.ingredient-row').remove();
                console.log('Ingredient removed');
            }
        });
    }
    
    // Ajouter ingrédient
    if (addIngredientBtn) {
        addIngredientBtn.addEventListener('click', function() {
            console.log('Adding ingredient, index:', ingredientIndex);
            
            const newRow = document.createElement('div');
            newRow.className = 'ingredient-row mb-2';
            newRow.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-5">
                        <div class="ingredient-autocomplete-wrapper">
                            <input type="hidden" name="recette[recetteIngredients][${ingredientIndex}][ingredient_id]" value="">
                            <input type="text" 
                                   name="recette[recetteIngredients][${ingredientIndex}][ingredient_nom]" 
                                   class="form-control ingredient-autocomplete" 
                                   placeholder="Nom de l'ingredient"
                                   autocomplete="off">
                            <div class="autocomplete-results" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <input type="number" 
                               name="recette[recetteIngredients][${ingredientIndex}][quantite]" 
                               class="form-control" 
                               placeholder="Qte"
                               step="0.01">
                    </div>
                    <div class="col-md-3">
                        <select name="recette[recetteIngredients][${ingredientIndex}][unite]" class="form-select">
                            <option value="">Unite</option>
                            <option value="g">g</option>
                            <option value="kg">kg</option>
                            <option value="ml">ml</option>
                            <option value="cl">cl</option>
                            <option value="L">L</option>
                            <option value="piece">piece</option>
                            <option value="c. a soupe">c. a soupe</option>
                            <option value="c. a cafe">c. a cafe</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger w-100 remove-ingredient">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            `;
            ingredientsContainer.appendChild(newRow);
            setupAutocomplete(newRow.querySelector('.ingredient-autocomplete'));
            ingredientIndex++;
            console.log('Ingredient added');
        });
    }
    
    // Autocomplete
    function setupAutocomplete(input) {
        const wrapper = input.closest('.ingredient-autocomplete-wrapper');
        const hiddenInput = wrapper.querySelector('input[type="hidden"]');
        const resultsDiv = wrapper.querySelector('.autocomplete-results');
        let timeoutId;
        
        input.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const query = this.value.trim();
            
            if (query.length < 2) {
                resultsDiv.style.display = 'none';
                return;
            }
            
            timeoutId = setTimeout(() => {
                fetch(`/api/ingredients/search?q=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(ingredients => {
                        resultsDiv.innerHTML = '';
                        
                        if (ingredients.length === 0) {
                            resultsDiv.style.display = 'none';
                            return;
                        }
                        
                        ingredients.forEach(ingredient => {
                            const item = document.createElement('div');
                            item.className = 'autocomplete-item';
                            item.textContent = ingredient.nom;
                            
                            item.addEventListener('click', () => {
                                input.value = ingredient.nom;
                                hiddenInput.value = ingredient.id;
                                resultsDiv.style.display = 'none';
                            });
                            
                            resultsDiv.appendChild(item);
                        });
                        
                        resultsDiv.style.display = 'block';
                    })
                    .catch(error => console.error('Autocomplete error:', error));
            }, 300);
        });
        
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                resultsDiv.style.display = 'none';
            }
        });
    }
    
    // Init autocomplete sur champs existants
    document.querySelectorAll('.ingredient-autocomplete').forEach(input => {
        setupAutocomplete(input);
    });
    
    // 
    // GESTION DES ÉTAPES
    // 
    
    // Ajouter étape
    if (addEtapeBtn) {
        addEtapeBtn.addEventListener('click', function() {
            console.log('Adding step, index:', etapeIndex);
            
            const newEtape = document.createElement('div');
            newEtape.className = 'etape-row mb-2';
            newEtape.innerHTML = `
                <div class="row g-2 align-items-center">
                    <div class="col-auto">
                        <span class="badge bg-success">${etapeIndex}</span>
                    </div>
                    <div class="col">
                        <input type="text" 
                               class="form-control etape-input" 
                               placeholder="Decrivez l'etape de preparation"
                               data-index="${etapeIndex}">
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-etape">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            `;
            etapesContainer.appendChild(newEtape);
            etapeIndex++;
            updateEtapesHidden();
            console.log('Step added');
        });
    }
    
    // Supprimer étape
    if (etapesContainer) {
        etapesContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-etape')) {
                e.target.closest('.etape-row').remove();
                renumberEtapes();
                updateEtapesHidden();
                console.log('Step removed');
            }
        });
        
        // Mettre à jour à chaque saisie
        etapesContainer.addEventListener('input', function(e) {
            if (e.target.classList.contains('etape-input')) {
                updateEtapesHidden();
            }
        });
    }
    
    // Renuméroter les étapes
    function renumberEtapes() {
        const etapes = etapesContainer.querySelectorAll('.etape-row');
        etapes.forEach((etape, index) => {
            etape.querySelector('.badge').textContent = index + 1;
            etape.querySelector('.etape-input').dataset.index = index + 1;
        });
        etapeIndex = etapes.length + 1;
    }
    
    // Mettre à jour le champ caché
    function updateEtapesHidden() {
        const etapes = [];
        etapesContainer.querySelectorAll('.etape-input').forEach((input, index) => {
            if (input.value.trim()) {
                etapes.push(`${index + 1}. ${input.value.trim()}`);
            }
        });
        if (etapesHidden) {
            etapesHidden.value = etapes.join('\n');
            console.log('Steps updated:', etapesHidden.value);
        }
    }
    
    // Ajouter 2 étapes par défaut
    if (addEtapeBtn && etapesContainer && etapesContainer.children.length === 0) {
        addEtapeBtn.click();
        addEtapeBtn.click();
        console.log('Default steps added');
    }
    
    console.log('Recipe form ready');
});