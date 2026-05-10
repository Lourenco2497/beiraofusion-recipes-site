<?php
$tipo = $_GET['tipo'] ?? 'cocktail';
$titulo = $tipo === 'gastronomia' ? 'DESCOBRE GASTRONOMIA' : 'DESCOBRE COCKTAILS';
?>


<div class="container">
<h1 class="mt-4 pb-1"><?= htmlspecialchars($titulo) ?></h1>

<!-- Container onde vão aparecer as receitas -->
<div id="receitas-container" class="mt-3"></div>

<div id="loader" class="text-center py-3">A carregar...</div>
</div>

<!-- Scripts -->
<script src="../js/like.js"></script>
<script>
    let pagina = 0;
    let carregando = false;
    let tipo = '<?= $tipo ?>';

    function carregarMais() {
        if (carregando) return;
        carregando = true;
        document.getElementById('loader').style.display = 'block';

        fetch(`../api/carregar_receitas.php?tipo=${tipo}&pagina=${pagina}`)
            .then(res => res.text())
            .then(html => {
                if (html.trim() === '') {
                    document.getElementById('loader').innerText = 'Sem mais receitas.';
                } else {
                    document.getElementById('receitas-container').insertAdjacentHTML('beforeend', html);
                    pagina++;
                    carregando = false;
                }
            });
    }

    // scroll infinito
    window.addEventListener('scroll', () => {
        if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 200) {
            carregarMais();
        }
    });


    carregarMais();
</script>

<script src="../js/menu.js"></script>

