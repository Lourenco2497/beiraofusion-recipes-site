<body class="body-adicionar">
<div class="login-container mt-5 pt-5">
    <h1 class="mt-5 pt-5 mb-4">Olá, bem-vindo de volta!</h1>

    <?php
    session_start();
    if (isset($_SESSION['mensagem_erro'])) {
        echo '<div class="alert alert-danger">' . $_SESSION['mensagem_erro'] . '</div>';
        unset($_SESSION['mensagem_erro']);
    }
    ?>

    <form action="../scripts/sc_login.php" method="post">
        <div class="mb-4">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <div class="password-container">
                <input type="password" class="form-control" id="password" name="password" required>
                <button type="button" class="password-toggle" onclick="togglePassword()">
                    <i class="fa-regular fa-eye"></i>
                </button>
            </div>
            <div class="forgot-password">
                <a href="#">Não sabes a tua palavra-passe?</a>
            </div>
        </div>

        <button type="submit" class="login-btn">Iniciar sessão</button>

        <div class="signup-text">
            <span>Ainda não tens conta? </span>
            <a href="../site/registo.php">Criar conta.</a>
        </div>
    </form>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const passwordToggle = document.querySelector('.password-toggle i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordToggle.classList.remove('fa-eye-slash');
            passwordToggle.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            passwordToggle.classList.remove('fa-eye');
            passwordToggle.classList.add('fa-eye-slash');
        }
    }
</script>
</body>