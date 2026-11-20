<?php
require_once __DIR__ . '/includes/auth.php';
exigir_admin();

$sucesso = "";
$erro = "";
$eu = usuario_logado()["id_user"];


if (isset($_GET["alternar"])) {
    $id = (int) $_GET["alternar"];
    if ($id === $eu) {
        $erro = "Você não pode desativar o seu próprio usuário.";
    } else {
        $st = $pdo->prepare("UPDATE `user` SET ativo = 1 - ativo WHERE id_user = ?");
        $st->execute([$id]);
        $sucesso = $st->rowCount() ? "Situação do usuário alterada!" : "";
        if (!$sucesso) $erro = "Não foi possível encontrar o usuário!";
    }
}
if (isset($_GET["salvo"])) {
    $sucesso = "Usuário salvo com sucesso!";
}

$usuarios = $pdo->query("SELECT * FROM `user` ORDER BY nome")->fetchAll();

require_once __DIR__ . '/includes/menu.php';
?>
    <main class="flex-1 flex flex-col">

        <div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-xl font-extrabold text-gray-800">Gerenciar Usuários</h1>
                <p class="text-sm text-gray-500">Cadastre usuários e defina o perfil de acesso</p>
            </div>
            <a href="usuario_form.php" class="bg-senai-green text-white font-bold px-4 py-2.5 rounded-lg text-sm hover:bg-green-600 transition">+ Novo Usuário</a>
        </div>

        <div class="p-6 flex-1">

            <?php if ($sucesso): ?>
                <div class="bg-green-50 border border-green-300 text-green-700 rounded-lg p-3 mb-5 text-sm">✓ <?= htmlspecialchars($sucesso) ?></div>
            <?php endif; ?>
            <?php if ($erro): ?>
                <div class="bg-red-50 border border-red-300 text-red-700 rounded-lg p-3 mb-5 text-sm"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-senai-blue text-white">
                        <tr>
                            <th class="px-4 py-3 text-left">Nome</th>
                            <th class="px-4 py-3 text-center">Login</th>
                            <th class="px-4 py-3 text-center">Perfil</th>
                            <th class="px-4 py-3 text-center">Situação</th>
                            <th class="px-4 py-3 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <?php foreach ($usuarios as $u): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-gray-700 font-semibold"><?= htmlspecialchars($u["nome"]) ?></td>
                            <td class="px-4 py-3 text-center text-gray-600"><?= htmlspecialchars($u["login"]) ?></td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($u["perfil"] === PERFIL_ADMIN): ?>
                                    <span class="bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-1 rounded">Administrador</span>
                                <?php else: ?>
                                    <span class="bg-orange-100 text-orange-700 text-xs font-semibold px-2 py-1 rounded">Operador de almoxarifado</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($u["ativo"]): ?>
                                    <span class="bg-green-100 text-green-700 text-xs font-semibold px-2 py-1 rounded">Ativo</span>
                                <?php else: ?>
                                    <span class="bg-gray-200 text-gray-600 text-xs font-semibold px-2 py-1 rounded">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="usuario_form.php?id=<?= $u["id_user"] ?>" class="bg-senai-blue text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-senai-blue-dark transition">✏️ Editar</a>
                                    <?php if ((int) $u["id_user"] !== $eu): ?>
                                        <a onclick="return confirm('Tem certeza disso?')" href="?alternar=<?= $u["id_user"] ?>" class="bg-senai-red text-white text-xs px-2.5 py-1.5 rounded-md hover:bg-red-700 transition"><?= $u["ativo"] ? "Desativar" : "Ativar" ?></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
