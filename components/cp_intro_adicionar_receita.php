<body class="body-adicionar">
<div class="container-fluid bg-white">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4 py-7 px-6 position-relative mt-3">


            <?php if (isset($status) && $status === 'success'): ?>
                <div class="alert alert-success" role="alert">
                    Receita adicionada com sucesso! Pode adicionar outra.
                </div>
            <?php elseif (isset($status) && $status === 'error'): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($message ?? 'Ocorreu um erro.') ?>
                </div>
            <?php endif; ?>

            <form id="recipe-form" action="../components/cp_adicionar_receita.php" method="post" enctype="multipart/form-data">
                <a href="../site/index.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                </a>

                <!-- upload img -->
                <div class="card-upload mt-5 text-center d-flex justify-content-center align-items-center">
                    <div class="d-flex flex-column align-items-center justify-content-center text-center">
                        <div id="image-preview-container" class="image-preview d-flex flex-column align-items-center justify-content-center">
                            <label for="imagem" class="btn btn-outline-secondary d-flex flex-column align-items-center justify-content-center">
                                <i class="fa-solid fa-image"></i>
                            </label>
                            <h5 class="mt-3 text-secondary small">Adiciona uma imagem!</h5>
                        </div>
                        <input type="file" id="imagem" name="imagem" class="d-none" accept="image/*">
                    </div>
                </div>

                <!-- título -->
                <div class="mt-4">
                    <h3 class="form-label fw-medium">Título</h3>
                    <input type="text" name="titulo" class="form-control bg-warning-subtle border-0" placeholder="Título da receita" required>
                </div>

                <!-- categoria -->
                <div class="mt-4">
                    <h3 class="form-label fw-medium">Categoria</h3>
                    <div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ref_category_recipe_id" id="comida" value="1" checked>
                            <label class="form-check-label" for="prato-principal">Prato principal</label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="ref_category_recipe_id" id="sobremesa" value="3">
                            <label class="form-check-label" for="sobremesa">Sobremesa</label>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" name="ref_category_recipe_id" id="cocktail" value="2">
                            <label class="form-check-label" for="cocktail">Cocktail</label>
                        </div>
                    </div>
                </div>

                <!-- tempo de prep -->
                <div class="mt-4">
                    <h3 class="form-label fw-medium">Tempo de preparação (minutos)</h3>
                    <input type="number" name="prep_time" class="form-control bg-warning-subtle border-0" placeholder="Ex: 90" required>
                </div>

                <!-- dificuldade -->
                <div class="mt-4">
                    <h3 class="form-label fw-medium">Dificuldade</h3>
                    <div class="d-flex justify-content-between">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ref_difficulty_id" id="facil" value="1" checked>
                            <label class="form-check-label" for="facil">Fácil</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ref_difficulty_id" id="medio" value="2">
                            <label class="form-check-label" for="medio">Médio</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="ref_difficulty_id" id="dificil" value="3">
                            <label class="form-check-label" for="dificil">Difícil</label>
                        </div>
                    </div>
                </div>

                <!-- ingredientes -->
                <div class="mt-4">
                    <h3 class="form-label fw-medium">Ingredientes</h3>
                    <div class="ingredient-list">
                        <div class="d-flex align-items-center gap-2 mb-2 ingredient-row">
                            <input type="text" name="ingrediente_qt[]" class="form-control bg-warning-subtle border-0" placeholder="Qt." style="flex: 0 1 70px;">
                            <input type="text" name="ingrediente_unit[]" class="form-control bg-warning-subtle border-0" placeholder="Uni." style="flex: 0 1 100px;">
                            <select name="ingrediente_id[]" class="form-select bg-warning-subtle border-0 ingredient-select flex-grow-1">
                                <option value="" selected>Selecione um ingrediente</option>
                                <?php if (isset($ingredients_options)): ?>
                                    <?php foreach ($ingredients_options as $ingredient): ?>
                                        <option value="<?= htmlspecialchars($ingredient['ingredient_id']) ?>"><?= htmlspecialchars($ingredient['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <option value="new">Adicionar novo...</option>
                            </select>
                            <input type="text" name="ingrediente_nome_novo[]" class="bg-warning-subtle border-0 new-ingredient-name flex-grow-1" placeholder="Nome do novo ingrediente" style="display: none;">
                            <button type="button" class="btn bg-warning-subtle border-0 remove-ingredient d-flex align-items-center">
                                <i class="bi bi-trash text-danger"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn bg-warning border-0 d-block mx-auto mt-3" id="add-ingredient">+ Adicionar ingrediente</button>
                    </div>
                </div>


                <!-- instruções -->
                <div class="mt-4">
                    <h3 class="form-label fw-medium">Instruções</h3>
                    <div class="instruction-list">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input type="text" name="instrucao[]" class="form-control bg-warning-subtle border-0" placeholder="Passo 1" required>
                            <button type="button" class="btn bg-warning-subtle border-0 remove-instruction">
                                <i class="bi bi-trash text-danger"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="button" class="btn bg-warning border-0 d-block mx-auto mt-3" id="add-instruction">+ Adicionar instrução</button>
                    </div>
                </div>

                <!-- submit -->
                <div class="text-center">
                    <button type="submit" class="btn btn-danger d-block mx-auto mt-4 fw-medium" id="btn-publicar">Publicar</button>
                </div>


<script>
    const ingredientsData = <?= isset($ingredients_options) ? json_encode($ingredients_options) : '[]' ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../js/adicionar-receita.js"></script>
</body>
</html>