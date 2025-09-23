-- ARQUIVO: 01_estrutura.sql
-- FINALIDADE: Criar toda a estrutura (tabelas e lógica) do banco de dados.

-- Tabela de Campus
CREATE TABLE campus (
    id_campus SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    endereco TEXT,
    estado VARCHAR(50),
    uf CHAR(2)
);

-- Tabela de Departamentos
CREATE TABLE departamento (
    id_departam SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    id_campus INT REFERENCES campus(id_campus)
);

-- Tabela de Cursos
CREATE TABLE curso (
    id_curso SERIAL PRIMARY KEY,
    nome_curso VARCHAR(150) NOT NULL,
    id_departam INT REFERENCES departamento(id_departam)
);

-- Tabela de Usuários (Generalização)
CREATE TABLE usuario (
    id_usuario SERIAL PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    tel VARCHAR(20),
    email VARCHAR(255) UNIQUE NOT NULL,
    login VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo INT NOT NULL CHECK (tipo IN (1, 2, 3)) -- 1: aluno, 2: empresa, 3: admin
);

-- Tabela de Alunos (Especialização)
CREATE TABLE aluno (
    id_aluno INT PRIMARY KEY REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    matricula VARCHAR(20) UNIQUE NOT NULL,
    sobrenome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    data_nasc DATE NOT NULL,
    status VARCHAR(50),
    id_curso INT REFERENCES curso(id_curso),
    habilidades JSONB
);

-- Tabela de Empresas (Especialização)
CREATE TABLE empresa (
    id_empresa INT PRIMARY KEY REFERENCES usuario(id_usuario) ON DELETE CASCADE,
    nome_fant VARCHAR(255),
    cnpj VARCHAR(18) UNIQUE NOT NULL
);

-- Tabela de Vagas
CREATE TABLE vaga (
    id_vaga SERIAL PRIMARY KEY,
    id_usuario_admin INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    faixa_salarial NUMERIC(10, 2),
    pre_requi TEXT,
    modalidade VARCHAR(50),
    status VARCHAR(50) DEFAULT 'Aberta',
    data_publicacao DATE DEFAULT CURRENT_DATE,
    data_expirar DATE,
    CONSTRAINT fk_admin_vaga FOREIGN KEY (id_usuario_admin) REFERENCES usuario(id_usuario)
);

-- Tabela de Candidaturas
CREATE TABLE candidatura (
    id_candidatura SERIAL PRIMARY KEY,
    id_aluno INT NOT NULL REFERENCES aluno(id_aluno) ON DELETE CASCADE,
    id_vaga INT NOT NULL REFERENCES vaga(id_vaga) ON DELETE CASCADE,
    data_candidatura TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_candidatura VARCHAR(50) DEFAULT 'Enviada',
    UNIQUE (id_aluno, id_vaga)
);

-- Tabela de Experiência Profissional
CREATE TABLE experiencia_profissional (
    id_exppro SERIAL PRIMARY KEY,
    id_aluno INT NOT NULL REFERENCES aluno(id_aluno) ON DELETE CASCADE,
    data_inicio DATE NOT NULL,
    data_saida DATE,
    cargo VARCHAR(100) NOT NULL,
    nome_empresa VARCHAR(150),
    descricao_ativ TEXT
);

-- Tabela de Formação Acadêmica
CREATE TABLE formacao_academica (
    id_formacad SERIAL PRIMARY KEY,
    id_aluno INT NOT NULL REFERENCES aluno(id_aluno) ON DELETE CASCADE,
    curso_da_form VARCHAR(150) NOT NULL,
    local_formacao VARCHAR(150),
    data_inicio DATE,
    data_conclusao DATE,
    tipo VARCHAR(50)
);

-- Tabela de Visualização de Perfil
CREATE TABLE visualizacao_perfil (
    id_visualizacao SERIAL PRIMARY KEY,
    id_aluno INT NOT NULL REFERENCES aluno(id_aluno),
    id_empresa INT NOT NULL REFERENCES empresa(id_empresa),
    data_visualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Auditoria para Vagas
CREATE TABLE auditoria_vaga (
    id_auditoria SERIAL PRIMARY KEY,
    id_vaga_afetada INT,
    operacao VARCHAR(10) NOT NULL,
    dados_antigos JSONB, -- Armazena como era o registro antes
    dados_novos JSONB, -- Armazena como ficou o registro depois
    modificado_por VARCHAR(100) NOT NULL,
    data_modificacao TIMESTAMP NOT NULL
);

-- View para Relatório Gerencial
CREATE OR REPLACE VIEW vw_gerenciamento_vagas AS
SELECT
    v.id_vaga,
    v.titulo,
    v.status,
    u.nome AS nome_admin, -- Traz o nome do admin que postou a vaga
    v.data_publicacao,
    v.data_expirar,
    -- Subconsulta que conta o número de candidatos para cada vaga
    (SELECT COUNT(*) FROM candidatura c WHERE c.id_vaga = v.id_vaga) AS total_candidatos
FROM
    vaga v
JOIN
    usuario u ON v.id_usuario_admin = u.id_usuario -- Junta com a tabela de usuários para pegar o nome
ORDER BY
    v.data_publicacao DESC;

-- Stored Procedure para arquivar vagas expiradas
CREATE OR REPLACE PROCEDURE sp_arquivar_vagas_expiradas(OUT vagas_arquivadas INT)
LANGUAGE plpgsql
AS $$
BEGIN
    WITH vagas_a_mudar AS (
        UPDATE vaga
        SET status = 'Expirada'
        WHERE data_expirar < CURRENT_DATE AND status = 'Aberta'
        RETURNING id_vaga -- Retorna os IDs das linhas afetadas
    )
    SELECT count(*) INTO vagas_arquivadas FROM vagas_a_mudar; -- Conta quantas linhas foram afetadas

END;
$$;

-- Função da Trigger de Auditoria
CREATE OR REPLACE FUNCTION fn_auditoria_vaga()
RETURNS TRIGGER AS $$
BEGIN
    IF (TG_OP = 'INSERT') THEN
        INSERT INTO auditoria_vaga (id_vaga_afetada, operacao, dados_novos, modificado_por, data_modificacao)
        VALUES (NEW.id_vaga, 'INSERT', row_to_json(NEW), current_user, now());
    ELSIF (TG_OP = 'UPDATE') THEN
        INSERT INTO auditoria_vaga (id_vaga_afetada, operacao, dados_antigos, dados_novos, modificado_por, data_modificacao)
        VALUES (NEW.id_vaga, 'UPDATE', row_to_json(OLD), row_to_json(NEW), current_user, now());
    ELSIF (TG_OP = 'DELETE') THEN
        INSERT INTO auditoria_vaga (id_vaga_afetada, operacao, dados_antigos, modificado_por, data_modificacao)
        VALUES (OLD.id_vaga, 'DELETE', row_to_json(OLD), current_user, now());
    END IF;
    RETURN NULL; -- O resultado é ignorado para triggers AFTER
END;
$$ LANGUAGE plpgsql;

-- Criação da Trigger
DROP TRIGGER IF EXISTS trg_auditoria_vaga ON vaga;
CREATE TRIGGER trg_auditoria_vaga
AFTER INSERT OR UPDATE OR DELETE ON vaga
FOR EACH ROW EXECUTE FUNCTION fn_auditoria_vaga();