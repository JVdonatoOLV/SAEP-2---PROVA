<?php
require_once __DIR__ . '/includes/auth.php';

if (usuario_logado()) {
    header('Location: index.php');
    exit;
}

$erro  = "";
$login = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = trim($_POST["login"] ?? "");
    $senha = $_POST["senha"] ?? "";

    $st = $pdo->prepare("SELECT id_user, nome, login, perfil, senha_hash FROM `user` WHERE login = ? AND ativo = 1");
    $st->execute([$login]);
    $u = $st->fetch();

    if ($u && password_verify($senha, $u["senha_hash"])) {
        session_regenerate_id(true);
        unset($u["senha_hash"]);
        $_SESSION["usuario"] = $u;
        header("Location: index.php");
        exit;
    }
    $erro = "Login ou senha inválidos.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar | Estoque SAEP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { senai: { blue:'#34679A', 'blue-dark':'#2C5A85' } } } } }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .form-input { width:100%; border:1px solid #d1d5db; border-radius:8px; padding:10px 14px; font-size:14px; outline:none; }
        .form-input:focus { border-color:#34679A; box-shadow:0 0 0 3px rgba(52,103,154,.15); }
        .form-label { display:block; font-size:12px; font-weight:600; color:#6b7280; margin-bottom:6px; text-transform:uppercase; letter-spacing:.05em; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-sm p-8 w-full max-w-sm">
        <p class="text-xl font-extrabold text-gray-800">📦 Estoque SAEP</p>
        <p class="text-sm text-gray-500 mb-6">Entre com seu login e senha</p>

        <?php if ($erro): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 text-sm"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-4">
                <label class="form-label">Login</label>
                <input type="text" name="login" class="form-input" required autofocus value="<?= htmlspecialchars($login) ?>">
            </div>
            <div class="mb-6">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-input" required>
            </div>
            <button type="submit" class="w-full bg-senai-blue text-white font-bold px-5 py-2.5 rounded-lg text-sm hover:bg-senai-blue-dark transition">Entrar</button>
        </form>
    </div>
</body>
</html>
