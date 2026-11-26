<?php
// CONTROLLER = o "GARÇOM".
// Recebe o pedido (vindo do index.php), chama o Service (gerente)
// e RESPONDE ao cliente em JSON, com o código HTTP certo (200, 201, 404...).
// Também é ele que "pega os alarmes" (erros) e transforma em resposta. (Igual ao LivroController.java)
class LivroController
{
    // Guarda o Service, para poder pedir as coisas a ele.
    private LivroService $service;

    // Quando o Controller é criado, ele cria um Service para usar.
    public function __construct(?LivroService $service = null)
    {
        $this->service = $service ?? new LivroService();
    }

    // O "CARDÁPIO DE DECISÕES".
    // $metodo = o verbo do pedido (GET, POST, PUT, DELETE)
    // $id     = o número da URL (/livros/3 -> 3) ou null (/livros)
    public function tratar(string $metodo, ?int $id): void
    {
        try {
            // ----- SEM id na URL: /livros -----
            if ($id === null) {
                if ($metodo === 'GET') {
                    // LISTAR: pega a lista de livros e transforma cada um em array (toArray) -> 200 OK
                    $this->responder(200, array_map(fn($l) => $l->toArray(), $this->service->listar()));
                } elseif ($metodo === 'POST') {
                    // CADASTRAR: lê o JSON, o Service valida e manda gravar -> 201 Criado
                    $livro = $this->service->criar($this->lerJson());
                    $this->responder(201, $livro->toArray());
                } else {
                    // Outro verbo em /livros (ex.: PATCH) -> 405 Método não permitido
                    $this->responder(405, ['erro' => 'Método não permitido.']);
                }
                return;
            }

            // ----- COM id na URL: /livros/3 -----
            if ($metodo === 'GET') {
                // BUSCAR UM: achou -> 200 com o livro | não achou (null) -> 404
                $livro = $this->service->buscar($id);
                $livro ? $this->responder(200, $livro->toArray()) : $this->naoEncontrado();
            } elseif ($metodo === 'PUT') {
                // ATUALIZAR: lê o JSON, o Service valida e atualiza -> 200 | livro não existe -> 404
                $livro = $this->service->atualizar($id, $this->lerJson());
                $livro ? $this->responder(200, $livro->toArray()) : $this->naoEncontrado();
            } elseif ($metodo === 'DELETE') {
                // EXCLUIR: apagou -> 204 (sem conteúdo) | livro não existe -> 404
                $this->service->excluir($id) ? $this->responder(204, null) : $this->naoEncontrado();
            } else {
                // Outro verbo em /livros/3 (ex.: POST) -> 405
                $this->responder(405, ['erro' => 'Método não permitido.']);
            }

        // ----- AQUI OS "ALARMES" (erros) SÃO PEGOS -----
        } catch (InvalidArgumentException $e) {
            // Alarme do SERVICE: dado inválido (ex.: título vazio) -> 400 com a mensagem do Service
            $this->responder(400, ['erro' => $e->getMessage()]);
        } catch (PDOException $e) {
            // Alarme do BANCO (vem do DAO):
            // código 23000 = o usuario_id não existe (chave estrangeira) -> 400
            // qualquer outro = problema no banco (ex.: Docker desligado) -> 503
            if ($e->getCode() === '23000') {
                $this->responder(400, ['erro' => 'O usuario_id informado não existe.']);
            } else {
                $this->responder(503, ['erro' => 'Não foi possível acessar o banco de dados.']);
            }
        }
    }

    // LÊ O JSON que o cliente mandou no corpo do pedido e transforma em array.
    // Se não for um JSON válido -> alarme -> 400.
    private function lerJson(): array
    {
        $dados = json_decode(file_get_contents('php://input'), true);
        if (!is_array($dados)) {
            throw new InvalidArgumentException('Envie um JSON válido no corpo da requisição.');
        }
        return $dados;
    }

    // Atalho para a resposta 404 "Livro não encontrado".
    private function naoEncontrado(): void
    {
        $this->responder(404, ['erro' => 'Livro não encontrado.']);
    }

    // RESPONDE AO CLIENTE: coloca o código HTTP e transforma os dados em JSON.
    // É AQUI que o array vira JSON (json_encode).
    // JSON_UNESCAPED_UNICODE = mantém os acentos ("não" em vez de "não").
    // No 204 os dados são null, então não escreve nada.
    private function responder(int $status, $dados): void
    {
        http_response_code($status);
        if ($dados !== null) {
            echo json_encode($dados, JSON_UNESCAPED_UNICODE);
        }
    }
}
