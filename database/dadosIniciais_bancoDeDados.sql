-- ARQUIVO: 02_dados_iniciais.sql
-- FINALIDADE: Limpar dados existentes e popular o banco com dados de exemplo.

-- Apaga os dados existentes na ordem inversa de dependência para evitar erros
DELETE FROM visualizacao_perfil;
DELETE FROM formacao_academica;
DELETE FROM experiencia_profissional;
DELETE FROM candidatura;
DELETE FROM vaga;
DELETE FROM empresa;
DELETE FROM aluno;
DELETE FROM usuario;
DELETE FROM curso;
DELETE FROM departamento;
DELETE FROM campus;

-- Insere os dados de exemplo
INSERT INTO campus (nome, endereco, estado, uf) VALUES
('IFBA Campus Feira de Santana', 'Rodovia BR-324, Km 521', 'Bahia', 'BA');

INSERT INTO departamento (nome, id_campus) VALUES
('Departamento de Tecnologia', 1);

INSERT INTO curso (nome_curso, id_departam) VALUES
('Bacharelado em Sistemas de Informação', 1);

INSERT INTO usuario (nome, login, senha, tipo) VALUES
('Ana Silva', 'ana.silva', 'senha123', 1),
('Bruno Costa', 'bruno.costa', 'senha123', 1),
('TechCorp Soluções', 'techcorp', 'empresa123', 2),
('InovaTech', 'inovatech', 'empresa123', 2),
('Admin IFBA', 'admin.fsa', 'admin123', 3);

INSERT INTO aluno (id_aluno, matricula, sobrenome, cpf, data_nasc, email, status, id_curso, habilidades) VALUES
(1, '2021BSI001', 'Silva', '111.222.333-44', '2002-03-15', 'ana.silva@ifba.edu.br', 'Regular', 1, '[{"habilidade": "Python", "nivel": "Avançado"}]'),
(2, '2022BSI002', 'Costa', '555.666.777-88', '2003-07-20', 'bruno.costa@ifba.edu.br', 'Regular', 1, '[{"habilidade": "JavaScript", "nivel": "Avançado"}]');

INSERT INTO empresa (id_empresa, nome_fant, cnpj, tel, email) VALUES
(3, 'TechCorp', '11.222.333/0001-44', '75999998888', 'contato@techcorp.com'),
(4, 'InovaTech', '44.555.666/0001-77', '75988887777', 'rh@inovatech.com');

INSERT INTO vaga (id_usuario_admin, titulo, descricao, faixa_salarial, pre_requi, modalidade) VALUES
(5, 'Estágio em Desenvolvimento Backend', 'Atuar no desenvolvimento de sistemas web.', 1200.00, 'Conhecimento em Python e Django.', 'Híbrido'),
(5, 'Estágio em Desenvolvimento Frontend', 'Oportunidade para criar interfaces com React.', 1300.00, 'Conhecimento em JavaScript e React.', 'Remoto');

INSERT INTO candidatura (id_aluno, id_vaga) VALUES
(1, 1),
(2, 2);