# API de Livros (PHP)

Uma API para **cadastrar, listar, buscar, atualizar e excluir livros**.
Ela não tem tela: em vez de mostrar uma página, ela responde com **dados em JSON**,
para que qualquer programa (um app de celular, um site, o Postman) possa usar.

Este é o **Trabalho 2** da disciplina **Aplicações para Internet**. Ele continua o nosso
projeto da Parte 1, o [Sistema de Intercâmbio de Livros em Java](https://github.com/Davizeira238/projetolivro),
e **usa o mesmo banco de dados**. Por isso, um livro cadastrado pela API aparece no site
Java, e vice-versa.

| | Projeto Java (Parte 1) | Esta API (Parte 2) |
| --- | --- | --- |
| Entrega | páginas HTML | dados em JSON |
| Para quem | pessoas, no navegador | outros programas |
| Banco | MySQL `intercambio_livros` | o **mesmo** MySQL |

---

## Como funciona por dentro

O projeto segue o padrão **MVC em camadas**. Cada pedido passa por estas etapas:

```text
Pedido → public/index.php → LivroController → LivroService → LivroDAO → MySQL
```

Pense num restaurante:

| Arquivo | Papel | No restaurante |
| --- | --- | --- |
| `public/index.php` | recebe todo pedido e descobre o que foi pedido (as rotas) | a porta de entrada |
| `src/Controller/LivroController.php` | chama o Service e responde em JSON com o código HTTP certo | o garçom |
| `src/Service/LivroService.php` | confere as regras (título obrigatório, tamanhos, dono válido) | o gerente |
| `src/DAO/LivroDAO.php` | o **único** lugar com SQL: lê e grava no banco | a cozinha |
| `src/Model/Livro.php` | só guarda os dados de um livro | a ficha do prato |
| `src/Config/Database.php` | abre a conexão com o MySQL | a chave da despensa |
| `database/schema.sql` | cria o banco e as tabelas do zero (se não usar o projeto Java) | a planta da despensa |

Cada camada faz **uma coisa só**. Assim fica fácil entender, mudar e testar.

---

## O que precisa ter instalado

- **PHP 8.1 ou mais novo**, com as extensões `pdo_mysql` e `mbstring` ligadas no `php.ini`
  (confira com `php -m`).
- **Docker Desktop**, para rodar o MySQL do projeto Java.
- **Postman** (opcional, mas é o jeito mais fácil de testar).

---

## Como rodar

**1. Ligar o banco** (na pasta do projeto Java):

```powershell
git clone https://github.com/Davizeira238/projetolivro.git
cd projetolivro
docker compose up -d
```

Confira com `docker compose ps -a`: o `mysql` deve estar **healthy** e o `migrations`, **Exited (0)**.

> Sem o projeto Java? Rode o arquivo `database/schema.sql` em qualquer MySQL.

**2. Criar um usuário** (todo livro precisa de um dono). Só na primeira vez:

```powershell
docker compose exec mysql mysql -u root -proot intercambio_livros -e "INSERT IGNORE INTO Usuarios (nome, email) VALUES ('Aluno de teste','aluno.teste@example.com'); SELECT id, nome FROM Usuarios;"
```

**3. Ligar a API** (na pasta deste projeto):

```powershell
php -S localhost:8000 -t public
```

Pronto! A API está em **http://localhost:8000/livros**. Deixe o terminal aberto enquanto usa.

A conexão com o banco usa `localhost:3306`, banco `intercambio_livros`, usuário `root` e
senha `root` (os mesmos do projeto Java). Esses valores são só para uso local; dá para
trocar sem mexer no código, criando as variáveis de ambiente `DB_DSN`, `DB_USER` e `DB_PASS`.

---

## O que a API faz (endpoints)

| O que fazer | Método | Endereço | Se der certo |
| --- | --- | --- | --- |
| Listar todos os livros | `GET` | `/livros` | **200** + lista |
| Ver um livro | `GET` | `/livros/{id}` | **200** + o livro |
| Cadastrar um livro | `POST` | `/livros` | **201** + o livro criado (com o id novo) |
| Atualizar um livro | `PUT` | `/livros/{id}` | **200** + o livro atualizado |
| Excluir um livro | `DELETE` | `/livros/{id}` | **204** (sem conteúdo) |

### O que mandar no cadastro e na atualização

```json
{
  "titulo": "Dom Casmurro",
  "autor": "Machado de Assis",
  "isbn": "9788535910663",
  "disponivel": true,
  "usuario_id": 1
}
```

| Campo | Obrigatório? | Regra |
| --- | --- | --- |
| `titulo` | sim | texto de até 255 letras |
| `autor` | sim | texto de até 150 letras |
| `usuario_id` | sim | número de um usuário que existe |
| `isbn` | não | texto de até 17 caracteres |
| `disponivel` | não | `true` ou `false` (se não mandar, fica `true`) |

> No `PUT`, o livro é **substituído** pelos dados enviados. Se você não mandar o `isbn`, ele fica vazio.

### Quando algo dá errado

A API sempre responde com uma mensagem clara, no formato `{"erro": "mensagem"}`:

| Código | Quando acontece | Exemplo de mensagem |
| --- | --- | --- |
| **400** | faltou um dado, dado inválido ou o dono não existe | `Título é obrigatório.` |
| **404** | o livro (ou o endereço) não existe | `Livro não encontrado.` |
| **405** | método que não existe naquele endereço (ex.: `PATCH`) | `Método não permitido.` |
| **503** | o banco está fora do ar | `Não foi possível acessar o banco de dados.` |

---

## Testando

### Com o Postman (recomendado)

1. No Postman, aperte **Ctrl + O** e importe o arquivo `postman/API-Livros.postman_collection.json`.
2. Abra a coleção **API Livros - Apresentação** e clique nos testes **de 1 a 9**, apertando **Send** em cada um.

Os testes seguem a ordem: cadastrar → listar → buscar → atualizar → três erros → excluir →
buscar o livro excluído (404). O primeiro guarda o id do livro criado, e os outros usam
esse id sozinhos.

### Pelo PowerShell

```powershell
Invoke-RestMethod http://localhost:8000/livros
Invoke-RestMethod -Method Post http://localhost:8000/livros -ContentType "application/json; charset=utf-8" -Body '{"titulo":"Dom Casmurro","autor":"Machado de Assis","usuario_id":1}'
Invoke-RestMethod http://localhost:8000/livros/1
Invoke-RestMethod -Method Put http://localhost:8000/livros/1 -ContentType "application/json; charset=utf-8" -Body '{"titulo":"Dom Casmurro","autor":"Machado","disponivel":false,"usuario_id":1}'
Invoke-RestMethod -Method Delete http://localhost:8000/livros/1
```

### O que já foi testado (com o MySQL de verdade)

- Cadastrar, listar, buscar, atualizar e excluir.
- Título vazio ou só com espaços, título com mais de 255 letras, corpo que não é JSON,
  `usuario_id` como texto, `disponivel` inválido e dono inexistente → **400**.
- Id inválido (`abc`, `0`) → **400**; livro inexistente (no GET, PUT e DELETE) → **404**.
- Método não permitido → **405**; endereço inexistente → **404**.

---

## Pastas

```text
api-livros-patrick/
├── public/index.php                    ← porta de entrada (rotas)
├── src/
│   ├── Config/Database.php             ← conexão com o MySQL
│   ├── Model/Livro.php                 ← dados do livro
│   ├── DAO/LivroDAO.php                ← SQL (CRUD)
│   ├── Service/LivroService.php        ← regras e validações
│   └── Controller/LivroController.php  ← respostas JSON
├── database/schema.sql                 ← cria o banco do zero
├── postman/API-Livros.postman_collection.json
└── README.md
```

---

## Quem fez o quê

| Quem | Parte | Arquivos |
| --- | --- | --- |
| Davi ([@Davizeira238](https://github.com/Davizeira238)) | camada de dados | `Database.php`, `Livro.php`, `LivroDAO.php`, `schema.sql` |
| [@andreluiscostada](https://github.com/andreluiscostada) | camada HTTP | `index.php`, `LivroController.php`, `LivroService.php` |
| Os dois | | testes, coleção do Postman, README e apresentação |

Cada um trabalhou na sua própria branch, e as partes foram juntadas por Pull Request.
