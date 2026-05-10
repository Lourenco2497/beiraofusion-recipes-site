<body class="body-adicionar">
<div class="container form-container mt-5 pt-5">
    <h1 class="mb-4 mt-5 pt-5">Criar conta</h1>

    <form action="../scripts/sc_registo.php" method="post">
        <div class="mb-3">
            <h4 for="email" class="form-label">E-mail</h4>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <div class="mb-3">
            <h4 for="username" class="form-label">Nome de utilizador</h4>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>

        <div class="mb-3">
            <h4 for="password" class="form-label">Password</h4>
            <div class="password-container">
                <input type="password" class="form-control" id="password" name="password" required>
                <span class="password-toggle" onclick="togglePassword()">
                <i class="fas fa-eye"></i>
            </span>
            </div>
        </div>

        <button type="submit" class="btn btn-criar-conta">Criar conta</button>
    </form>


    <div class="login-link">
        <p>Já tens conta? <a href="login.php">Iniciar sessão</a>.</p>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const icon = document.querySelector('.password-toggle i');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
</body>