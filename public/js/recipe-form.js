// JavaScript pour formulaires recettes (new/edit)
// Gestion dynamique des ingrédients et étapes

document.addEventListener("DOMContentLoaded", function () {
    console.log("Recipe form script loaded");

    // ============================================
    // GESTION DES INGRÉDIENTS (Symfony Collection)
    // ============================================

    const ingredientsContainer = document.getElementById(
        "ingredients-collection"
    );
    const addIngredientBtn = document.getElementById("add-ingredient");

    if (ingredientsContainer) {
        // Chercher le prototype - 2 emplacements possibles
        let prototype = ingredientsContainer.dataset.prototype;

        // Si pas trouvé, chercher sur le widget Symfony caché
        if (!prototype) {
            const symfonyWidget = document.getElementById(
                "recette_recetteIngredients"
            );
            if (symfonyWidget) {
                prototype = symfonyWidget.dataset.prototype;
            }
        }

        // Index pour nouveaux ingrédients
        let ingredientIndex =
            document.querySelectorAll(".ingredient-row").length || 0;

        // Supprimer ingrédient (délégation)
        ingredientsContainer.addEventListener("click", function (e) {
            if (e.target.closest(".remove-ingredient")) {
                e.target.closest(".ingredient-row").remove();
                console.log("Ingredient removed");
            }
        });

        // Ajouter ingrédient via prototype Symfony
        if (addIngredientBtn && prototype) {
            addIngredientBtn.addEventListener("click", function () {
                console.log("Adding ingredient, index:", ingredientIndex);

                // Remplacer __name__ par l'index
                let newForm = prototype.replace(/__name__/g, ingredientIndex);

                // Créer un élément temporaire pour parser le HTML
                const temp = document.createElement("div");
                temp.innerHTML = newForm;

                // Extraire les champs individuels
                const ingredientSelect = temp.querySelector(
                    'select[id*="ingredient"]'
                );
                const quantiteInput = temp.querySelector(
                    'input[id*="quantite"]'
                );
                const uniteSelect = temp.querySelector('select[id*="unite"]');

                // Appliquer les classes Bootstrap
                if (ingredientSelect)
                    ingredientSelect.className = "form-select";
                if (quantiteInput) {
                    quantiteInput.className = "form-control";
                    quantiteInput.placeholder = "Qté";
                }
                if (uniteSelect) uniteSelect.className = "form-select";

                // Créer le wrapper avec structure Bootstrap
                const newRow = document.createElement("div");
                newRow.className = "ingredient-row mb-2";
                newRow.innerHTML = `
                <div class="row g-2 align-items-end">
                    <div class="col-md-5"></div>
                    <div class="col-md-2"></div>
                    <div class="col-md-3"></div>
                    <div class="col-md-2">
                        <button type="button" 
                                class="btn btn-outline-danger w-100 remove-ingredient"
                                aria-label="Supprimer cet ingrédient">
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            `;

                // Insérer les champs dans la structure
                const cols = newRow.querySelectorAll('[class^="col-md"]');
                if (ingredientSelect && cols[0])
                    cols[0].appendChild(ingredientSelect);
                if (quantiteInput && cols[1])
                    cols[1].appendChild(quantiteInput);
                if (uniteSelect && cols[2]) cols[2].appendChild(uniteSelect);

                ingredientsContainer.appendChild(newRow);
                ingredientIndex++;

                console.log("Ingredient added");
            });
        } else if (!prototype) {
            console.error("No prototype found in either location!");
        }
    }

    // ============================================
    // GESTION DES ÉTAPES
    // ============================================

    const etapesContainer = document.getElementById("etapes-collection");
    const addEtapeBtn = document.getElementById("add-etape");
    const etapesHidden = document.getElementById("recette_etapes");

    let etapeIndex = 1;

    // Charger les étapes existantes (mode édition)
    if (etapesHidden && etapesHidden.value && etapesContainer) {
        const etapes = etapesHidden.value.split("\n").filter((e) => e.trim());

        etapes.forEach(function (etape) {
            const cleanEtape = etape.replace(/^\d+\.\s*/, "").trim();
            if (cleanEtape) {
                addEtapeRow(cleanEtape);
            }
        });
    }

    // Ajouter 2 étapes par défaut si vide (mode création)
    if (etapesContainer && etapesContainer.children.length === 0) {
        addEtapeRow("");
        addEtapeRow("");
    }

    // Fonction pour ajouter une étape
    function addEtapeRow(value = "") {
        if (!etapesContainer) return;

        const newEtape = document.createElement("div");
        newEtape.className = "etape-row mb-2";
        newEtape.innerHTML = `
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <span class="badge bg-success" aria-hidden="true">${etapeIndex}</span>
                </div>
                <div class="col">
                    <input type="text" 
                           class="form-control etape-input" 
                           placeholder="Décrivez l'étape de préparation"
                           aria-label="Étape ${etapeIndex}"
                           value="${value}"
                           data-index="${etapeIndex}">
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-etape" aria-label="Supprimer cette étape">
                        <i class="bi bi-x-lg" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        `;
        etapesContainer.appendChild(newEtape);
        etapeIndex++;
        updateEtapesHidden();
    }

    // Bouton ajouter étape
    if (addEtapeBtn) {
        addEtapeBtn.addEventListener("click", function () {
            addEtapeRow("");
            console.log("Step added");
        });
    }

    // Supprimer étape
    if (etapesContainer) {
        etapesContainer.addEventListener("click", function (e) {
            if (e.target.closest(".remove-etape")) {
                e.target.closest(".etape-row").remove();
                renumberEtapes();
                updateEtapesHidden();
                console.log("Step removed");
            }
        });

        // Mettre à jour à chaque saisie
        etapesContainer.addEventListener("input", function (e) {
            if (e.target.classList.contains("etape-input")) {
                updateEtapesHidden();
            }
        });
    }

    // Renuméroter les étapes
    function renumberEtapes() {
        if (!etapesContainer) return;

        const etapes = etapesContainer.querySelectorAll(".etape-row");
        etapes.forEach((etape, index) => {
            etape.querySelector(".badge").textContent = index + 1;
            const input = etape.querySelector(".etape-input");
            input.dataset.index = index + 1;
            input.setAttribute("aria-label", `Étape ${index + 1}`);
        });
        etapeIndex = etapes.length + 1;
    }

    // Mettre à jour le champ caché
     // Mettre à jour le champ caché
function updateEtapesHidden() {
    if (!etapesContainer || !etapesHidden) return;

    const etapes = [];
    etapesContainer
        .querySelectorAll(".etape-input")
        .forEach((input, index) => {
            if (input.value.trim()) {
                etapes.push(`${index + 1}. ${input.value.trim()}`);
            }
        });
    
    // Si aucune étape, mettre une valeur par défaut ou laisser vide
    etapesHidden.value = etapes.length > 0 ? etapes.join("\n") : "";
    
    console.log("Steps updated:", etapesHidden.value);
}

    console.log("Recipe form ready");
});
