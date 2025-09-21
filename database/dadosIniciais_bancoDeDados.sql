-- ARQUIVO: 02_dados_iniciais.sql
-- FINALIDADE: Limpar dados existentes e popular o banco com dados de exemplo (VERSÃO REVISADA).

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

-- Inserindo usuários (com os novos campos tel e email)
INSERT INTO usuario (nome, tel, email, login, senha, tipo) VALUES
('Ana Silva', '75999111111', 'ana.silva@ifba.edu.br', 'ana.silva', 'senha123', 1),
('Bruno Costa', '75999222222', 'bruno.costa@ifba.edu.br', 'bruno.costa', 'senha123', 1),
('TechCorp Soluções', '7532210011', 'contato@techcorp.com', 'techcorp', 'empresa123', 2),
('InovaTech', '7532210022', 'rh@inovatech.com', 'inovatech', 'empresa123', 2),
('Admin IFBA', '7536160000', 'admin@ifba.edu.br', 'admin.fsa', 'admin123', 3);

-- Inserindo dados de alunos (agora sem tel e email)
INSERT INTO aluno (id_aluno, matricula, sobrenome, cpf, data_nasc, status, id_curso, habilidades) VALUES
(1, '2021BSI001', 'Silva', '111.222.333-44', '2002-03-15', 'Regular', 1, '[{"habilidade": "Python", "nivel": "Avançado"}]'),
(2, '2022BSI002', 'Costa', '555.666.777-88', '2003-07-20', 'Regular', 1, '[{"habilidade": "JavaScript", "nivel": "Avançado"}]');

-- Inserindo dados de empresas (agora sem tel e email)
INSERT INTO empresa (id_empresa, nome_fant, cnpj) VALUES
(3, 'TechCorp', '11.222.333/0001-44'),
(4, 'InovaTech', '44.555.666/0001-77');

-- Inserindo vagas publicadas pelo usuário admin (id_usuario = 5)
INSERT INTO vaga (id_usuario_admin, titulo, descricao, faixa_salarial, pre_requi, modalidade) VALUES
(5, 'Estágio em Desenvolvimento Backend', 'Atuar no desenvolvimento de sistemas web.', 1200.00, 'Conhecimento em Python e Django.', 'Híbrido'),
(5, 'Estágio em Desenvolvimento Frontend', 'Oportunidade para criar interfaces com React.', 1300.00, 'Conhecimento em JavaScript e React.', 'Remoto');

-- Inserindo candidaturas e outras informações
INSERT INTO candidatura (id_aluno, id_vaga) VALUES
(1, 1),
(2, 2);

INSERT INTO experiencia_profissional (id_aluno, data_inicio, data_saida, cargo, nome_empresa, descricao_ativ) VALUES
(1, '2023-01-10', NULL, 'Desenvolvedor Web Jr (Estágio)', 'WebSystems', 'Desenvolvimento de APIs REST com Django Rest Framework.');

INSERT INTO formacao_academica (id_aluno, curso_da_form, local_formacao, tipo) VALUES
(2, 'Técnico em Informática', 'IFBA Campus Feira de Santana', 'Técnico');

INSERT INTO visualizacao_perfil (id_aluno, id_empresa) VALUES
(1, 3);