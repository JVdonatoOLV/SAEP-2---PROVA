<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../conexao.php';

const PERFIL_ADMIN    = 'ADMIN';
const PERFIL_OPERADOR = 'OPERADOR';

const PERFIS = [
    PERFIL_ADMIN    => 'Administrador do sistema',
    PERFIL_OPERADOR => 'Operador de almoxarifado',
];

function usuario_logado() {
    return $_SESSION['usuario'] ?? null;
}

function eh_admin() {
    $u = usuario_logado();
    return $u && $u['perfil'] === PERFIL_ADMIN;
}

function exigir_login() {
    global $pdo;
    $u = usuario_logado();
    if ($u) {
        $st = $pdo->prepare("SELECT id_user, nome, login, perfil FROM `user` WHERE id_user = ? AND ativo = 1");
        $st->execute([$u['id_user']]);
        $atual = $st->fetch();
        if ($atual) {
            $_SESSION['usuario'] = $atual;
            return;
        }
        unset($_SESSION['usuario']);
    }
    header('Location: login.php');
    exit;
}

function exigir_admin() {
    exigir_login();
    if (!eh_admin()) {
        http_response_code(403);
        require __DIR__ . '/menu.php';
        echo '<main class="flex-1 p-6"><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded max-w-xl">'
           . '<strong>Acesso negado.</strong> Esta área é exclusiva do administrador do sistema. '
           . '<a href="index.php" class="underline">Voltar ao dashboard</a></div></main></body></html>';
        exit;
    }
}
