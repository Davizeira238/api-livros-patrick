<?php   

//conexao com MySQL

class Database

{

public static function getConexao() : PDO
{

// conexao com o banco do projeto java
// getenv deixa trocar sem alterar o codigo (DB_DSN USADO SO EM TESTES)

$dsn = getenv('DB_DSN') ?:
'mysql:host=localhost;port=3306;dbname=intercambio_livros;charset=utf8mb4';

$usuario = getenv('DB_USER') ?: 'root';
$senha = getenv('DB_PASS') ?: 'root';

return new PDO($dsn, $usuario, $senha,  [

PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,       // MANDA ERRO PRO CONTROLLER

PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  // ORGANIZA AS LINHAS TRAZENDO ELAS COMO "ID, AUTOR, TITULO"
]



);
}
}








}
