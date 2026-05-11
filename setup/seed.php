<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));

require_once ROOT . '/connections/connections.php';

echo "Waiting for database...\n";
$conn    = null;
$attempt = 0;
while ($attempt < 20) {
    try {
        $conn = new_db_connection();
        break;
    } catch (Exception $e) {
        $attempt++;
        echo "Not ready (attempt $attempt/20): {$e->getMessage()}\n";
        sleep(3);
    }
}
if (!$conn) {
    echo "Could not connect after 20 attempts. Skipping seed.\n";
    exit(0);
}

// ── Base schema (Railway: import if tables don't exist yet) ──────────────────

$r = $conn->query("SHOW TABLES LIKE 'user_types'");
if ($r->num_rows === 0) {
    echo "No tables found — importing schema...\n";
    $sql = file_get_contents(ROOT . '/setup/railway-schema.sql');
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        if ($stmt === '' || preg_match('/^--/', $stmt)) continue;
        $conn->query($stmt);
    }
    echo "Schema imported.\n";
}

// ── Schema patches (run every boot, idempotent) ───────────────────────────────

foreach (['description TEXT NULL', "image_url VARCHAR(255) NULL DEFAULT 'cupao.png'", 'valid_until DATE NULL'] as $colDef) {
    $col = explode(' ', $colDef)[0];
    $r = $conn->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='vouchers' AND COLUMN_NAME='$col'");
    if ((int)$r->fetch_row()[0] === 0) {
        $conn->query("ALTER TABLE vouchers ADD COLUMN $colDef");
    }
}

foreach (['show_saved_recipes TINYINT(1) NOT NULL DEFAULT 1', 'show_liked_recipes TINYINT(1) NOT NULL DEFAULT 1'] as $colDef) {
    $col = explode(' ', $colDef)[0];
    $r = $conn->query("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='$col'");
    if ((int)$r->fetch_row()[0] === 0) {
        $conn->query("ALTER TABLE users ADD COLUMN $colDef");
    }
}

$conn->query("CREATE TABLE IF NOT EXISTS comment_likes (
    ref_user_id INT NOT NULL,
    ref_comment_id INT NOT NULL,
    PRIMARY KEY (ref_user_id, ref_comment_id),
    FOREIGN KEY (ref_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (ref_comment_id) REFERENCES comments(comment_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── Seed guard ────────────────────────────────────────────────────────────────

$r = $conn->query("SELECT COUNT(*) AS c FROM user_types");
if ((int)$r->fetch_assoc()['c'] > 0) {
    echo "Already seeded.\n";
    exit(0);
}

echo "Seeding database...\n";

$conn->query("CREATE TABLE IF NOT EXISTS challenges (
    challenge_id INT NOT NULL AUTO_INCREMENT,
    challenge_key VARCHAR(50) NOT NULL,
    description VARCHAR(255) NOT NULL,
    PRIMARY KEY (challenge_id),
    UNIQUE KEY uk_key (challenge_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS coupon_challenges (
    ref_voucher_id INT NOT NULL,
    ref_challenge_id INT NOT NULL,
    PRIMARY KEY (ref_voucher_id, ref_challenge_id),
    FOREIGN KEY (ref_voucher_id) REFERENCES vouchers(vouchers_id) ON DELETE CASCADE,
    FOREIGN KEY (ref_challenge_id) REFERENCES challenges(challenge_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS reports (
    report_id INT NOT NULL AUTO_INCREMENT,
    ref_reporter_id INT NOT NULL,
    ref_reported_id INT NOT NULL,
    reason TEXT NOT NULL,
    ref_status_id INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (report_id),
    FOREIGN KEY (ref_reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (ref_reported_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ── Copy branded capa images into imgs/receitas so recipes have nice photos ───

$capas    = ROOT . '/imgs/capas/';
$receitas = ROOT . '/imgs/receitas/';

$copies = [
    'caipirao.jpg'                                           => 'caipirao.jpg',
    'mentirao.png'                                           => 'mentirao.png',
    'maracujao.jpg'                                          => 'maracujao.jpg',
    'on-the-rocks.jpg'                                       => 'on-the-rocks.jpg',
    'foto-_0000s_0005_morangao.jpg'                          => 'morangao.jpg',
    'foto-_0000s_0010_cafezada.jpg'                          => 'cafezada.jpg',
    'foto-_0000s_0000_sangria_tinta.jpg'                     => 'sangria_tinta.jpg',
    '20240611_licorbeirao_cocktails_beiraosour_1x1_bj.png'   => 'beirao_sour.png',
    '20240611_licorbeirao_cocktails_beiraotonico_1x1_bj.png' => 'beirao_tonico.png',
    '20240611_licorbeirao_cocktails_beiraospice_1x1_bj.png'  => 'beirao_spice.png',
    'pudim_licor_beirao.webp'                                => 'pudim_beirao.webp',
    'rabanadas-de-licor-beirao-750x536.jpg'                  => 'rabanadas_beirao.jpg',
    'leitecreme.JPG'                                         => 'leitecreme.jpg',
    'crepes_de_beirao.jpg'                                   => 'crepes_beirao.jpg',
];

foreach ($copies as $src => $dst) {
    $s = $capas . $src;
    $d = $receitas . $dst;
    if (file_exists($s) && !file_exists($d)) {
        copy($s, $d) ? print("Copied $dst\n") : print("Failed to copy $dst\n");
    }
}

// ── Lookup tables ─────────────────────────────────────────────────────────────

$conn->query("INSERT INTO user_types (type_id, type_name) VALUES (1,'utilizador'),(2,'admin')");
$conn->query("INSERT INTO difficulty (difficulty_id, name) VALUES (1,'Fácil'),(2,'Médio'),(3,'Difícil')");
$conn->query("INSERT INTO status (status_id, name) VALUES (1,'Pendente'),(2,'Publicado'),(3,'Rejeitado')");
$conn->query("INSERT INTO category_recipe (category_recipe_id, name) VALUES (1,'Pratos'),(2,'Cocktails'),(3,'Sobremesas')");
$conn->query("INSERT INTO category_ingredients (category_ingredients_id, name) VALUES
    (1,'Licores'),(2,'Frutas'),(3,'Laticínios'),(4,'Especiarias'),(5,'Cereais'),(6,'Outros')");
$conn->query("INSERT INTO vouchers_type (vouchers_type_id, name) VALUES (1,'Desconto'),(2,'Oferta')");

// ── Users ─────────────────────────────────────────────────────────────────────

$adminHash = $conn->real_escape_string(password_hash('admin123', PASSWORD_DEFAULT));
$userHash  = $conn->real_escape_string(password_hash('user123',  PASSWORD_DEFAULT));

$conn->query("INSERT INTO users (user_id, username, email, password_hash, profile_image, ref_type_id) VALUES
    (1,'Admin','admin@beiraofusion.pt','$adminHash','default.jpg',2),
    (2,'joana_silva','joana@example.com','$userHash','joana.png',1),
    (3,'carlos_santos','carlos@example.com','$userHash','carlos.png',1),
    (4,'filipa_lima','filipa@example.com','$userHash','filipa.png',1),
    (5,'ines_rodrigues','ines@example.com','$userHash','ines.png',1),
    (6,'jose_ferreira','jose@example.com','$userHash','jose.png',1)");

// ── Ingredients ───────────────────────────────────────────────────────────────

$conn->query("INSERT INTO ingredients (ingredient_id, name, ref_category_ingredients_id) VALUES
    (1,'Licor Beirão',1),
    (2,'Sumo de limão',2),
    (3,'Sumo de laranja',2),
    (4,'Açúcar',6),
    (5,'Gelo',6),
    (6,'Água tónica',6),
    (7,'Morango',2),
    (8,'Maracujá',2),
    (9,'Hortelã',2),
    (10,'Leite',3),
    (11,'Natas',3),
    (12,'Ovos',3),
    (13,'Farinha',5),
    (14,'Canela',4),
    (15,'Café',6),
    (16,'Mel',6),
    (17,'Vinho tinto',6),
    (18,'Limão',2),
    (19,'Manteiga',3),
    (20,'Água com gás',6)");

// ── Recipes ───────────────────────────────────────────────────────────────────

$conn->query("INSERT INTO recipes
    (recipe_id, ref_user_id, title, description, prep_time, image_url, ref_difficulty_id, ref_status_id, ref_category_recipe_id)
    VALUES
    (1, 2,'Caipirão','O clássico Caipirão com Licor Beirão, lima e açúcar. Uma bebida fresca e cheia de sabor.',5,'caipirao.jpg',1,2,2),
    (2, 3,'Mentirão','Cocktail refrescante com Licor Beirão, sumo de limão e água com gás. Leve e saboroso.',5,'mentirao.png',1,2,2),
    (3, 4,'Maracujão','A combinação perfeita entre Licor Beirão e maracujá fresco para os dias quentes.',8,'maracujao.jpg',1,2,2),
    (4, 5,'Beirão Tónico','O clássico Beirão com água tónica e uma fatia de laranja. Simples e elegante.',3,'beirao_tonico.png',1,2,2),
    (5, 6,'Morangão','Cocktail aromático com Licor Beirão, morangos frescos esmagados e hortelã.',10,'morangao.jpg',2,2,2),
    (6, 2,'Cafezada','Bebida quente com Licor Beirão e café espresso, perfeita para o inverno.',5,'cafezada.jpg',1,2,2),
    (7, 3,'Sangria com Licor Beirão','Sangria tradicional portuguesa enriquecida com Licor Beirão e frutos da época.',15,'sangria_tinta.jpg',2,2,2),
    (8, 4,'Beirão Sour','Cocktail azedo com Licor Beirão, sumo de limão fresco e açúcar. Agitado, não mexido.',8,'beirao_sour.png',2,2,2),
    (9, 5,'Beirão Spice','Versão especial com especiarias e Licor Beirão servido sobre gelo. Surpreendente.',10,'beirao_spice.png',2,2,2),
    (10,6,'Pudim com Licor Beirão','Pudim flan cremoso com toque especial de Licor Beirão. A sobremesa perfeita.',60,'pudim_beirao.webp',3,2,3),
    (11,2,'Rabanadas de Licor Beirão','As tradicionais rabanadas natalícias com um delicioso toque de Licor Beirão.',45,'rabanadas_beirao.jpg',2,2,3),
    (12,3,'Leite Creme com Beirão','Leite creme com caramelo tostado e toque aromático de Licor Beirão.',30,'leitecreme.jpg',2,2,3),
    (13,4,'Crepes de Beirão','Crepes finos com creme de Licor Beirão, mel e frutos vermelhos frescos.',25,'crepes_beirao.jpg',2,2,3)");

// ── Recipe steps ──────────────────────────────────────────────────────────────

$steps = [
    [1, 1, 'Cortar o limão em pequenos cubos e colocar no fundo do copo.'],
    [1, 2, 'Adicionar 2 colheres de açúcar e esmagar levemente com um pilão.'],
    [1, 3, 'Adicionar 5cl de Licor Beirão e encher o copo com gelo picado.'],
    [1, 4, 'Agitar bem e servir com palhinhas.'],

    [2, 1, 'Encher um copo alto com gelo picado.'],
    [2, 2, 'Adicionar 4cl de Licor Beirão e espremer meio limão por cima.'],
    [2, 3, 'Completar com água com gás fria.'],
    [2, 4, 'Decorar com ramo de hortelã e fatia de limão.'],

    [3, 1, 'Cortar o maracujá ao meio e retirar a polpa para um copo.'],
    [3, 2, 'Adicionar gelo e 5cl de Licor Beirão.'],
    [3, 3, 'Mexer bem com uma colher longa.'],
    [3, 4, 'Decorar com casca de limão e servir imediatamente.'],

    [4, 1, 'Encher um copo largo com gelo.'],
    [4, 2, 'Adicionar 4cl de Licor Beirão sobre o gelo.'],
    [4, 3, 'Completar com água tónica gelada sem mexer demasiado.'],
    [4, 4, 'Decorar com uma fatia de laranja na borda do copo.'],

    [5, 1, 'Lavar os morangos, cortar em pedaços e colocar no fundo do copo.'],
    [5, 2, 'Esmagar ligeiramente com um pilão para libertar o suco.'],
    [5, 3, 'Adicionar 5cl de Licor Beirão e gelo picado.'],
    [5, 4, 'Completar com água com gás e decorar com hortelã fresca.'],

    [6, 1, 'Preparar um espresso quente e forte.'],
    [6, 2, 'Adicionar 3cl de Licor Beirão ao café quente.'],
    [6, 3, 'Adoçar a gosto com mel ou açúcar.'],
    [6, 4, 'Servir numa chávena pré-aquecida e saborear quente.'],

    [7, 1, 'Cortar as frutas da época em pedaços e colocar numa jarra grande.'],
    [7, 2, 'Adicionar 1 garrafa de vinho tinto, 10cl de Licor Beirão e sumo de laranja.'],
    [7, 3, 'Mexer bem, cobrir e refrigerar por pelo menos 2 horas.'],
    [7, 4, 'Servir sobre gelo com fruta fresca e decorar com hortelã.'],

    [8, 1, 'Num shaker, juntar 5cl de Licor Beirão e 3cl de sumo de limão fresco.'],
    [8, 2, 'Adicionar 1 colher de açúcar e bastante gelo.'],
    [8, 3, 'Agitar vigorosamente por 15 segundos.'],
    [8, 4, 'Coar para um copo de coquetel e decorar com casca de limão.'],

    [9, 1, 'Preparar um shaker com gelo abundante.'],
    [9, 2, 'Adicionar 5cl de Licor Beirão e pitada de canela.'],
    [9, 3, 'Agitar bem e coar para um copo com gelo fresco.'],
    [9, 4, 'Decorar com pau de canela e servir de imediato.'],

    [10, 1, 'Bater os ovos com o açúcar até obter um creme homogéneo e claro.'],
    [10, 2, 'Adicionar o leite morno aos poucos e 3cl de Licor Beirão. Misturar bem.'],
    [10, 3, 'Caramelizar o fundo das forminhas e verter o creme preparado.'],
    [10, 4, 'Cozer em banho-maria no forno a 160°C por 45 minutos. Deixar arrefecer antes de servir.'],

    [11, 1, 'Preparar uma mistura de ovos batidos, leite, canela e 5cl de Licor Beirão.'],
    [11, 2, 'Mergulhar as fatias de pão na mistura e deixar absorver bem.'],
    [11, 3, 'Fritar em azeite ou manteiga quente até dourar de ambos os lados.'],
    [11, 4, 'Escorrer em papel absorvente, polvilhar com açúcar e canela. Servir quente.'],

    [12, 1, 'Misturar as gemas com o açúcar, adicionar a farinha e o leite frio aos poucos.'],
    [12, 2, 'Levar ao lume médio, mexendo sempre em forma de oito, até engrossar.'],
    [12, 3, 'Retirar do lume, adicionar 3cl de Licor Beirão e misturar bem.'],
    [12, 4, 'Verter em taças, deixar arrefecer e queimar o açúcar por cima com maçarico.'],

    [13, 1, 'Preparar a massa dos crepes misturando farinha, ovos, leite e manteiga derretida.'],
    [13, 2, 'Deixar repousar 30 min e cozinhar crepes finos numa frigideira antiaderente.'],
    [13, 3, 'Preparar o recheio aquecendo natas com mel e 4cl de Licor Beirão até cremoso.'],
    [13, 4, 'Rechear os crepes, dobrar em triângulos e servir com frutos vermelhos frescos.'],
];

$stepStmt = $conn->prepare(
    "INSERT INTO recipe_steps (ref_recipe_id, step_number, description) VALUES (?, ?, ?)"
);
foreach ($steps as [$rid, $num, $desc]) {
    $stepStmt->bind_param("iis", $rid, $num, $desc);
    $stepStmt->execute();
}
$stepStmt->close();

// ── Recipe ingredients ────────────────────────────────────────────────────────

$conn->query("INSERT INTO recipe_ingredients (ref_recipe_id, ref_ingredient_id, quantity, unit) VALUES
    (1,1,5,'cl'),(1,4,2,'cs'),(1,5,1,'cx'),(1,18,1,'un'),
    (2,1,4,'cl'),(2,18,0.5,'un'),(2,5,1,'cx'),(2,9,3,'un'),(2,20,1,'cx'),
    (3,1,5,'cl'),(3,8,1,'un'),(3,5,1,'cx'),(3,18,0.5,'un'),
    (4,1,4,'cl'),(4,6,1,'cx'),(4,5,1,'cx'),(4,3,1,'un'),
    (5,1,5,'cl'),(5,7,5,'un'),(5,9,3,'un'),(5,5,1,'cx'),(5,20,1,'cx'),
    (6,1,3,'cl'),(6,15,1,'cx'),(6,16,1,'cs'),
    (7,1,10,'cl'),(7,17,75,'cl'),(7,3,20,'cl'),(7,5,1,'cx'),(7,18,1,'un'),
    (8,1,5,'cl'),(8,2,3,'cl'),(8,4,1,'cs'),(8,5,1,'cx'),
    (9,1,5,'cl'),(9,14,1,'un'),(9,5,1,'cx'),
    (10,1,3,'cl'),(10,12,4,'un'),(10,10,5,'dl'),(10,4,150,'g'),
    (11,1,5,'cl'),(11,12,2,'un'),(11,10,2,'dl'),(11,14,1,'cs'),(11,4,50,'g'),
    (12,1,3,'cl'),(12,12,4,'un'),(12,10,5,'dl'),(12,13,2,'cs'),(12,4,150,'g'),
    (13,1,4,'cl'),(13,13,125,'g'),(13,12,2,'un'),(13,10,3,'dl'),(13,19,30,'g'),(13,16,2,'cs'),(13,11,2,'dl')");

// ── Social interactions ───────────────────────────────────────────────────────

$conn->query("INSERT INTO recipe_likes (ref_user_id, ref_recipe_id) VALUES
    (2,1),(2,4),(2,7),(2,10),
    (3,2),(3,5),(3,8),(3,11),
    (4,3),(4,6),(4,9),(4,12),
    (5,1),(5,3),(5,5),(5,7),(5,13),
    (6,2),(6,4),(6,6),(6,8),(6,10)");

$conn->query("INSERT INTO recipe_saves (ref_user_id, ref_recipe_id) VALUES
    (2,7),(2,10),(2,12),
    (3,1),(3,8),(3,13),
    (4,2),(4,11),
    (5,4),(5,9),
    (6,3),(6,5),(6,12)");

$conn->query("INSERT INTO follows (follower_id, following_id) VALUES
    (2,3),(2,4),(2,5),
    (3,2),(3,6),
    (4,2),(4,3),
    (5,2),(5,4),(5,6),
    (6,3),(6,5)");

// ── Vouchers & challenges ─────────────────────────────────────────────────────

$conn->query("INSERT INTO challenges (challenge_id, challenge_key, description) VALUES
    (1,'PUBLISH_RECIPE','Publica a tua primeira receita para desbloquear'),
    (2,'UPDATE_AVATAR','Atualiza a tua foto de perfil para desbloquear'),
    (3,'FAVORITE_RECIPE','Dá like a uma receita para desbloquear')");

$conn->query("INSERT INTO vouchers (vouchers_id, name, description, image_url, valid_until, ref_vouchers_type_id) VALUES
    (1,'10% Desconto Online','10% de desconto na loja online Beirão. Código válido na próxima compra.','cupao.png','2026-12-31',1),
    (2,'Licor Beirão 20cl Grátis','Oferta de 20cl de Licor Beirão em compras superiores a 50€.','cupao.png','2026-12-31',2),
    (3,'Copo Exclusivo Beirão','Recebe um copo exclusivo Beirão com a tua próxima encomenda.','logo_cupoes_coracao.png','2026-12-31',2)");

$conn->query("INSERT INTO vouchers_users (ref_vouchers_id, ref_user_id) VALUES (1,2),(1,5),(2,3)");

$conn->query("INSERT INTO coupon_challenges (ref_voucher_id, ref_challenge_id) VALUES
    (2,1),(3,2)");

$conn->close();

echo "Done!\n";
echo "Admin:  admin@beiraofusion.pt / admin123\n";
echo "Users:  joana@example.com / user123  (and carlos, filipa, ines, jose)\n";
