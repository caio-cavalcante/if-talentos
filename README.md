# IF Talentos - Banco de Talentos BSI

![Imagem do ecrã principal da aplicação IF Talentos](https://github.com/caio-cavalcante/if-talentos/blob/main/assets/images/tela-inicial.png)

Uma plataforma web desenvolvida para ser a ponte entre os estudantes do curso de Bacharelado em Sistemas de Informação (BSI) do IFBA - Campus Feira de Santana e as oportunidades de estágio no mercado de trabalho.

O IF Talentos nasceu como um projeto interdisciplinar para as matérias de Processo de Desenvolvimento de Software, Programação Web e Banco de Dados 2, com a missão de resolver um desafio real: a dificuldade na obtenção de estágios obrigatórios. A plataforma centraliza perfis de alunos, vagas de empresas e um painel de administração para garantir a qualidade e a segurança do ecossistema.

## ✨ Funcionalidades Principais

O projeto na sua versão atual já conta com um ciclo completo e funcional, incluindo:

- **Plataforma Multi-perfil**: Interfaces distintas e seguras para Alunos, Empresas e Administradores.

- **Sistema de Autenticação**: Cadastro e login seguros com senhas criptografadas e gestão de sessões.

- **Dashboard de Admin**: Painel com gráficos e estatísticas sobre a saúde da plataforma (total de usuários, vagas, habilidades mais comuns).

- **Gerenciamento Completo (CRUDs)**:
    - Administradores podem gerenciar Vagas, Usuários e Cursos.
    - Empresas podem criar e gerenciar as suas próprias vagas.

- **Sistema de Aprovação de Vagas**: Vagas criadas por empresas passam por uma curadoria do administrador antes de serem publicadas para os alunos.

- **Busca de Talentos**: Empresas com perfil completo podem visualizar e buscar perfis de alunos qualificados.

- **Perfil Progressivo**: Os usuários (alunos e empresas) criam uma conta simplificada e são incentivados a completar o perfil para desbloquear as funcionalidades principais.

## 🚀 Próximos Passos e Melhorias Planeadas

O projeto continua em evolução. As próximas atualizações serão focadas em aprimorar a experiência do utilizador e adicionar novas funcionalidades estratégicas.

### Funcionalidades
- [ ] Implementar filtros avançados na busca de vagas e talentos.
- [ ] Finalizar a funcionalidade de upload de ficheiros (logos de empresas, currículos de alunos).
- [ ] Desenvolver funções de validação e limpeza de dados (CPF, CNPJ).

### Melhorias de Interface (UX)
- [ ] Adicionar máscaras de input para campos como telefone, CPF e CNPJ.
- [ ] Criar um sistema de notificações para empresas (vagas aprovadas/rejeitadas).
- [ ] Refinar as mensagens de erro e feedback em toda a aplicação.

### Concluído Recentemente
- [x] Criação de CRUD completo para Cursos, Vagas, Usuários (Admin).
- [x] Implementação do CRUD de Vagas para Empresas.
- [x] Desenvolvimento do sistema de aprovação de vagas.
- [x] Criação de dashboards funcionais para todos os perfis.
- [x] Implementação de perfis progressivos (cadastro simplificado + completar perfil).

## 💻 Tecnologias Utilizadas

- **Back-end**: PHP 8
- **Front-end**: HTML5, CSS3, JavaScript (ES6)
- **Banco de Dados**: PostgreSQL
- **Bibliotecas JavaScript**:
    - Typed.js: Para a animação na página inicial.
- **Ícones**: Font Awesome

## 🔧 Pré-requisitos para o Ambiente de Desenvolvimento

Antes de começar, garanta que possui os seguintes pré-requisitos instalados na sua máquina:

- Um ambiente de servidor local (ex: XAMPP, WAMP, MAMP).
- PHP 8 ou superior, com a extensão `pdo_pgsql` ativada no seu `php.ini`.
- PostgreSQL 12 ou superior.
- Um gestor de banco de dados como DBeaver ou PgAdmin.

## 🚀 Como Executar o Projeto Localmente

### 1. Clone o repositório:
```bash
git clone https://github.com/seu-usuario/if-talentos.git
```

### 2. Configure o Banco de Dados:
- Crie um novo banco de dados no seu PostgreSQL (ex: `if_talentos_db`).
- Importe e execute os scripts `criacaoBancoDeDados.sql` e `estrutura_bancoDeDados.sql` para criar toda a estrutura de tabelas.

### 3. Configure a Conexão:
- Navegue até a pasta `includes/`.
- Abra o `db_connect.php` e insira as suas credenciais locais do PostgreSQL (host, nome do banco, utilizador e senha).

### 4. Inicie o Servidor:
- Mova a pasta do projeto para o diretório do seu servidor local (ex: `htdocs` no XAMPP).
- Inicie o seu servidor Apache.
- Acesse `http://localhost/if-talentos` no seu navegador.

## 🤝 Colaboradores

| | | |
|:---:|:---:|:---:|
| <img src="https://github.com/caio-cavalcante/if-talentos/blob/main/assets/images/Breno.jpeg" width="100px;" alt="Foto do Breno"/><br/><sub><b>Breno Santana de Souza</b></sub> | <img src="https://github.com/caio-cavalcante/if-talentos/blob/main/assets/images/Caio.jpeg" width="100px;" alt="Foto do Caio"/><br/><sub><b>Caio Cavalcante Araújo</b></sub> | <img src="https://github.com/caio-cavalcante/if-talentos/blob/main/assets/images/Daniel.jpeg" width="100px;" alt="Foto do Daniel"/><br/><sub><b>Daniel de Souza Pereira</b></sub> |

## 📝 Licença

Este projeto é um trabalho acadêmico e de portfólio, todos os direitos são reservados aos seus desenvolvedores.
