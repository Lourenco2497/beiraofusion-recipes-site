document.addEventListener('DOMContentLoaded', function () {
    const recipeForm = document.getElementById('recipe-form');
    if (!recipeForm) return;

    const imageInput = document.getElementById('imagem');
    const imagePreviewContainer = document.getElementById('image-preview-container');
    const cardUpload = document.querySelector('.card-upload');
    const initialImageContent = imagePreviewContainer.innerHTML;

    if (cardUpload) {
        cardUpload.addEventListener('click', function() {
            imageInput.click();
        });
    }

    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                imagePreviewContainer.innerHTML = `
                    <img src="${e.target.result}" alt="Pré-visualização da imagem" class="img-preview">
                `;
                cardUpload.classList.add('preview-active');
                cardUpload.classList.remove('d-flex', 'justify-content-center', 'align-items-center');
            };
            reader.readAsDataURL(file);
        } else {
            imagePreviewContainer.innerHTML = initialImageContent;
            cardUpload.classList.remove('preview-active');
            cardUpload.classList.add('d-flex', 'justify-content-center', 'align-items-center');
        }
    });

    const addIngredientBtn = document.getElementById('add-ingredient');
    const ingredientList = document.querySelector('.ingredient-list');

    function createIngredientRowHtml() {
        let optionsHtml = '<option value="" selected>Selecione um ingrediente</option>';
        ingredientsData.forEach(ingredient => {
            optionsHtml += `<option value="${htmlspecialchars(ingredient.ingredient_id)}">${htmlspecialchars(ingredient.name)}</option>`;
        });
        optionsHtml += '<option value="new">Adicionar novo...</option>';

        return `
        <input type="text" name="ingrediente_qt[]" class="form-control bg-warning-subtle border-0" placeholder="Qt." style="flex: 0 1 70px;">
        <input type="text" name="ingrediente_unit[]" class="form-control bg-warning-subtle border-0" placeholder="Uni." style="flex: 0 1 100px;">
        <select name="ingrediente_id[]" class="form-select bg-warning-subtle border-0 ingredient-select flex-grow-1">
            ${optionsHtml}
        </select>
        <input type="text" name="ingrediente_nome_novo[]" class="form-control bg-warning-subtle border-0 new-ingredient-name flex-grow-1" placeholder="Nome do novo ingrediente" style="display: none;">
        <button type="button" class="btn bg-warning-subtle border-0 remove-ingredient d-flex align-items-center">
            <i class="bi bi-trash text-danger"></i>
        </button>`;
    }


    function htmlspecialchars(str) {
        if (typeof str !== 'string') return '';
        return str.replace(/[&<>"']/g, function (match) {
            const S = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return S[match];
        });
    }


    addIngredientBtn.addEventListener('click', function () {
        const newIngredientRow = document.createElement('div');
        newIngredientRow.className = 'd-flex gap-2 mb-2 ingredient-row';
        newIngredientRow.innerHTML = createIngredientRowHtml();
        ingredientList.appendChild(newIngredientRow);
    });

    ingredientList.addEventListener('click', function (e) {
        if (e.target.closest('.remove-ingredient')) {
            e.target.closest('.ingredient-row').remove();
        }
    });

    ingredientList.addEventListener('change', function(e) {
        if (e.target.classList.contains('ingredient-select')) {
            const row = e.target.closest('.ingredient-row');
            const newIngredientInput = row.querySelector('.new-ingredient-name');
            const selectElement = e.target;

            if (selectElement.value === 'new') {
                newIngredientInput.style.display = 'block';
                newIngredientInput.required = true;
                selectElement.style.display = 'none'; // Hide select
            } else {
                newIngredientInput.style.display = 'none';
                newIngredientInput.required = false;
                newIngredientInput.value = '';
                selectElement.style.display = 'block'; // Show select
            }
        }
    });


    const addInstructionBtn = document.getElementById('add-instruction');
    const instructionList = document.querySelector('.instruction-list');

    function updateInstructionPlaceholders() {
        const allInstructions = instructionList.querySelectorAll('input[name="instrucao[]"]');
        allInstructions.forEach((input, index) => {
            input.placeholder = `Passo ${index + 1}`;
        });
    }

    addInstructionBtn.addEventListener('click', function () {
        const newStepNumber = instructionList.children.length + 1;
        const newInstructionRow = document.createElement('div');
        newInstructionRow.className = 'd-flex align-items-center gap-2 mb-2';
        newInstructionRow.innerHTML = `
            <input type="text" name="instrucao[]" class="form-control bg-warning-subtle border-0" placeholder="Passo ${newStepNumber}" required>
            <button type="button" class="btn bg-warning-subtle border-0 remove-instruction">
                <i class="bi bi-trash text-danger"></i>
            </button>`;
        instructionList.appendChild(newInstructionRow);
    });

    instructionList.addEventListener('click', function (e) {
        if (e.target.closest('.remove-instruction')) {
            e.target.closest('.d-flex').remove();
            updateInstructionPlaceholders();
        }
    });

    updateInstructionPlaceholders();


    recipeForm.addEventListener('submit', function (e) {
        let ingredientValid = false;
        const ingredientRows = ingredientList.querySelectorAll('.ingredient-row');

        if (ingredientRows.length === 0) {
            e.preventDefault();
            alert('Por favor, adicione pelo menos um ingrediente.');
            return;
        }

        let hasAtLeastOneIngredientName = false;
        for (const row of ingredientRows) {
            const select = row.querySelector('.ingredient-select');
            const newNameInput = row.querySelector('.new-ingredient-name');
            if ((select.value && select.value !== 'new' && select.value !== '') || (newNameInput.value.trim() !== '' && select.value === 'new')) {
                hasAtLeastOneIngredientName = true;
                break;
            }
        }

        if (!hasAtLeastOneIngredientName && ingredientRows.length > 0) {
            e.preventDefault();
            alert('Por favor, selecione ou adicione um nome para cada ingrediente com quantidade.');
            return;
        }


        const instructions = instructionList.querySelectorAll('input[name="instrucao[]"]');
        if (Array.from(instructions).every(input => input.value.trim() === '')) {
            e.preventDefault();
            alert('Por favor, adicione pelo menos uma instrução.');
        }
    });
});