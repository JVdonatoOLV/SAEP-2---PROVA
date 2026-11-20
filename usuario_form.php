<?php
require_once __DIR__ . '/includes/auth.php';
exigir_admin();

$erro = "";
$id = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);
$nome = $login = "";
$perfil = PERFIL_OPERADOR;

if ($id) {
    $st = $pdo->prepare("SELECT * FROM `user` WHERE id_user = ?");
    $st->execute([$id]);
    $existente = $st->fetch();
    if (!$existente) {
        header("Location: usuarios.php");
        exit;
    }
    $nome = $existente["nome"];
    $login = $existente["login"];
    $perfil = $existente["perfil"];
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome   = trim($_POST["nome"] ?? "");
    $login  = trim($_POST["login"] ?? "");
    $perfil = $_POST["perfil"] ?? "";
    $senha  = $_POST["senha"] ?? "";

    if ($nome === "" || $login === "" || ($id === 0 && $senha === "")) {
        $erro = "Preencha todos os campos obrigatórios!";
    } elseif (!isset(PERFIS[$perfil])) {
        $erro = "Perfil inválido!";
    } elseif ($senha !== "" && strlen($senha) < 6) {
        $erro = "A senha deve ter pelo menos 6 caracteres.";
    } elseif ($id === usuario_logado()["id_user"] && $perfil !== PERFIL_ADMIN) {
        $erro = "Você não pode remover o seu próprio perfil de administrador.";
    } else {
        $st = $pdo->prepare("SELECT id_user FROM `user` WHERE login = ? AND id_user <> ?");
        $st->execute([$login, $id]);
        if ($st->fetch()) {
            $erro = "Este login já está em uso!";
        }
    }

    if ($erro === "") {
        if ($id) {
            $st = $pdo->prepare("UPDATE `user` SET nome = ?, login = ?, perfil = ? WHERE id_user = ?");
            $st->execute([$nome, $login, $perfil, $id]);
            if ($senha !== "") {
                $pdo->prepare("UPDATE `user` SET senha_hash = ? WHERE id_user = ?")
                    ->execute([password_hash($senha, PASSWORD_DEFAULT), $id]);
            }
        } else {
            $st = $pdo->prepare("INSERT INTO `user` (nome, login, senha_hash, perfil) VALUES (?, ?, ?, ?)");
            $st->execute([$nome, $login, password_hash($senha, PASSWORD_DEFAULT), $perfil]);
        }
        header("Location: usuarios.php?salvo=1");
        exit;
    }
}

require_once __DIR__ . '/includes/menu.php';
?>
    <main class="flex-1 flex flex-col">
        <div class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-1">
                <a href="usuarios.php" class="hover:text-senai-blue">Usuários</a> ›
                <span class="text-gray-700 font-semibold"><?= $id ? "Editar" : "Novo" ?> Usuário</span>
            </div>
            <h1 class="text-xl font-extrabold text-gray-800"><?= $id ? "Editar" : "Novo" ?> Usuário</h1>
        </div>
        <div class="p-6 flex-1 max-w-xl">

            <?php if ($erro): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm p-6">
                <form method="post">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <div class="mb-4">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="nome" class="form-input" required maxlength="100" value="<?= htmlspecialchars($nome) ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Login *</label>
                        <input type="text" name="login" class="form-input" required maxlength="50" value="<?= htmlspecialchars($login) ?>">
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Perfil *</label>
                        <select name="perfil" class="form-input" required>
                            <?php foreach (PERFIS as $valor => $rotulo): ?>
                                <option value="<?= $valor ?>" <?= $perfil === $valor ? "selected" : "" ?>><?= $rotulo ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-6">
                        <label class="form-label">Senha <?= $id ? "(deixe em branco para manter)" : "*" ?></label>
                        <input type="password" name="senha" class="form-input" autocomplete="new-password" <?= $id ? "" : "required" ?> minlength="6">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-senai-blue text-white font-bold px-5 py-2.5 rounded-lg text-sm hover:bg-senai-blue-dark transition">💾 Salvar</button>
                        <a href="usuarios.php" class="bg-gray-100 text-gray-600 font-semibold px-5 py-2.5 rounded-lg text-sm hover:bg-gray-200 transition">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
