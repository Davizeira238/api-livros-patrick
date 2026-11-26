-- Use este arquivo SOMENTE se não estiver usando o MySQL/Flyway do projeto Java.
-- (Com o projeto Java, `docker compose up -d` já cria tudo.)
CREATE DATABASE IF NOT EXISTS intercambio_livros CHARACTER SET utf8mb4;
USE intercambio_livros;

-- Donos dos livros
CREATE TABLE IF NOT EXISTS Usuarios (
    id BIGINT NOT NULL AUTO_INCREMENT,        -- o banco cria o id sozinho (1, 2, 3...)
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(254) NOT NULL,
    PRIMARY KEY (id),
    CONSTRAINT uk_usuarios_email UNIQUE (email)  -- não pode repetir e-mail
) ENGINE=InnoDB;

-- Livros cadastrados
CREATE TABLE IF NOT EXISTS Livros (
    id BIGINT NOT NULL AUTO_INCREMENT,        -- por isso o INSERT do DAO não manda id
    titulo VARCHAR(255) NOT NULL,             -- obrigatório, até 255 letras
    autor VARCHAR(150) NOT NULL,              -- obrigatório, até 150 letras
    isbn VARCHAR(17),                         -- opcional (pode ser NULL)
    disponivel BOOLEAN NOT NULL DEFAULT TRUE, -- se não informar, fica disponível
    usuario_id BIGINT NOT NULL,
    PRIMARY KEY (id),
    -- Chave estrangeira: o dono precisa existir em Usuarios (senão: erro 23000 -> API responde 400)
    CONSTRAINT fk_livros_usuario FOREIGN KEY (usuario_id)
        REFERENCES Usuarios (id) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB;

-- Usuário de teste (ignorado se já existir)
INSERT IGNORE INTO Usuarios (nome, email) VALUES ('Aluno de teste', 'aluno.teste@example.com');
