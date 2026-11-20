DROP DATABASE IF EXISTS empresa;
CREATE DATABASE empresa CHARACTER SET utf8 COLLATE utf8_danish_ci;
USE empresa;


DROP TABLE IF EXISTS movimentacao CASCADE;
DROP TABLE IF EXISTS produto CASCADE;
DROP TABLE IF EXISTS `user` CASCADE;

CREATE TABLE `user` (
    id_user INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    login VARCHAR(50) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    perfil VARCHAR(10) NOT NULL DEFAULT 'OPERADOR' CHECK (perfil IN ('ADMIN', 'OPERADOR')),
    ativo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE produto (
    id_produto INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    quantidade_estoque INTEGER NOT NULL DEFAULT 0 CHECK (quantidade_estoque >= 0),
    valor_unitario NUMERIC(10,2) NOT NULL,
    data_cadastro DATE NOT NULL DEFAULT (CURRENT_DATE)
) ENGINE=InnoDB;

CREATE TABLE movimentacao (
    id_movimentacao INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    tipo VARCHAR(10) NOT NULL CHECK (tipo IN ('ENTRADA', 'SAIDA')),
    quantidade INTEGER NOT NULL CHECK (quantidade > 0),
    id_produto INTEGER NOT NULL,
    datetime_movimentacao DATETIME NOT NULL,
    id_user INTEGER NOT NULL,
    FOREIGN KEY (id_produto) REFERENCES produto(id_produto),
    FOREIGN KEY (id_user) REFERENCES `user`(id_user)
) ENGINE=InnoDB;

CREATE TRIGGER movimentacao_atualiza_estoque AFTER INSERT ON movimentacao FOR EACH ROW
    UPDATE produto
       SET quantidade_estoque = quantidade_estoque + IF(NEW.tipo = 'ENTRADA', NEW.quantidade, -NEW.quantidade)
     WHERE id_produto = NEW.id_produto;

CREATE TRIGGER movimentacao_sem_update BEFORE UPDATE ON movimentacao FOR EACH ROW
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Movimentações não podem ser alteradas.';
CREATE TRIGGER movimentacao_sem_delete BEFORE DELETE ON movimentacao FOR EACH ROW
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Movimentações não podem ser excluídas.';


INSERT INTO `user` (nome, login, senha_hash, perfil) VALUES
('Administrador', 'admin', '$2y$10$TbWPV1H5qC4rvwl6FzqW7OiGKOUBk2tD540m7y5w.fz2MH5fZ0VPW', 'ADMIN'),
('Operador de Almoxarifado', 'operador', '$2y$10$qKIBIR4tbb3nbwX9k5ua/usn0Y1PzgXeAe4HhcGvzfSAJv.GRnd/i', 'OPERADOR');

INSERT INTO produto (nome, quantidade_estoque, valor_unitario) VALUES
('Amaciante de roupas', 0, 0.50),
('Detergente', 0, 18.90),
('Sabão em pó', 0, 22.50);

INSERT INTO movimentacao (tipo, quantidade, id_produto, datetime_movimentacao, id_user) VALUES
('ENTRADA', 110, 1, NOW(), 1),
('ENTRADA', 30, 2, NOW(), 1),
('ENTRADA', 40, 3, NOW(), 1),
('SAIDA', 10, 1, NOW(), 1),
('SAIDA', 5, 2, NOW(), 1);

select * from produto;

