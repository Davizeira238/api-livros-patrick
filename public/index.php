<?php
// Ponto de entrada: toda requisição passa por aqui e é encaminhada ao Controller (as "rotas").
require_once __DIR__ . '/../src/Config/Database.php';
require_once __DIR__ . '/../src/Model/Livro.php';
require_once __DIR__ . '/../src/DAO/LivroDAO.php';
require_once __DIR__ . '/../src/Service/LivroService.php';
require_once __DIR__ . '/../src/Controller/LivroController.php';

header('Content-Type: application/json; charset=utf-8');

$metodo = $_SERVER['REQUEST_METHOD'];
$caminho = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$partes = explode('/', $caminho);   // ex.: "livros/3" -> ["livros", "3"]

if ($partes[0] !== 'livros' || count($partes) > 2) {
    http_response_code(404);
    echo json_encode(['erro' => 'Rota não encontrada.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = null;
if (isset($partes[1])) {
    if (!ctype_digit($partes[1]) || (int) $partes[1] <= 0) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID inválido.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $id = (int) $partes[1];
}

(new LivroController())->tratar($metodo, $id);
