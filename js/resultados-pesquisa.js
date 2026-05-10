$(document).ready(function() {
    $('#pesquisa').on('input', function() {
        let query = $(this).val().trim();

        if (query.length >= 2) {
            // esconde o conteúdo principal
            $('#main-content').addClass('hidden-during-search');

            $.ajax({
                url: '../api/get_receitas.php',
                method: 'GET',
                data: { query: query },
                success: function(data) {
                    let html = '';
                    if (data.length > 0) {
                        data.forEach(function(item) {
                            const isUser = query.trim().startsWith('@');
                            const href = isUser
                                ? `perfil_utilizador.php?id=${item.id}`
                                : `receita_detail.php?id=${item.id}`;
                            const imagePath = isUser
                                ? `../${item.image_url || 'imgs/perfis/default.jpg'}`
                                : `../imgs/receitas/${item.image_url}`;

                            if (isUser) {
                                html += `
            <div class="card mb-3 p-3 text-center shadow-sm">
                <a href="${href}" class="text-decoration-none text-dark">
                    <img src="${imagePath}" alt="${item.title}" class="rounded-circle mx-auto d-block" style="width: 80px; height: 80px; object-fit: cover;">
                    <h4 class="mt-2">${item.title}</h4>
                </a>
            </div>`;
                            } else {
                                html += `
            <div class="card recipe-card mb-3 shadow-sm" style="text-decoration: none;">
                <a href="${href}" style="text-decoration: none; color: inherit;">
                    <img src="${imagePath}" class="card-img-top receita-imagem-fixa" alt="Imagem da receita">
                    <div class="card-body">
                        <h5 class="card-title">${item.title}</h5>
                    </div>
                </a>
            </div>`;
                            }
                        });

                    } else {
                        html = "<p>Nenhuma receita encontrada.</p>";
                    }
                    $('#search-results').html(html);
                }
            });
        } else {
            // se a pesquisa for apagada, volta a mostrar o conteúdo principal
            $('#search-results').empty();
            $('#main-content').removeClass('hidden-during-search');
        }
    });
});
