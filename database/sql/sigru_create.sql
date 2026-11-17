-- =====================================================================
-- SIGRU - Sistema Integrado de Gestão do Restaurante Universitário
-- Script de Criação do Banco de Dados (MySQL)
-- Tamanhos de atributos conforme Dicionário de Dados do Seminário I
--
-- Inclui:
--   - Charset utf8mb4
--   - Constraints UNIQUE de regras de negócio
--   - DEFAULTS em campos críticos
--   - Tipos alinhados entre PK/FK
-- =====================================================================

DROP DATABASE IF EXISTS sigru;
CREATE DATABASE sigru
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE sigru;

-- =====================================================================
-- TABELAS BASE (sem dependências)
-- =====================================================================

-- CIDADE
CREATE TABLE cidade (
    id_cidade     INT(6) UNSIGNED AUTO_INCREMENT,
    descricao     VARCHAR(100) NOT NULL,
    uf            VARCHAR(2) NOT NULL,
    codigo_ibge   VARCHAR(7) NOT NULL,
    PRIMARY KEY (id_cidade),
    UNIQUE KEY uk_cidade_codigo_ibge (codigo_ibge)
);

-- BAIRRO (FK -> CIDADE)
CREATE TABLE bairro (
    id_bairro     INT(6) UNSIGNED AUTO_INCREMENT,
    id_cidade     INT(6) UNSIGNED NOT NULL,
    descricao     VARCHAR(30) NOT NULL,
    PRIMARY KEY (id_bairro),
    CONSTRAINT fk_bairro_cidade FOREIGN KEY (id_cidade)
        REFERENCES cidade (id_cidade)
);

-- CATEGORIA_USUARIO
CREATE TABLE categoria_usuario (
    id_categoria  INT(3) UNSIGNED AUTO_INCREMENT,
    descricao     VARCHAR(50) NOT NULL,
    PRIMARY KEY (id_categoria),
    UNIQUE KEY uk_categoria_descricao (descricao)
);

-- TIPO_PRODUTO
CREATE TABLE tipo_produto (
    id_tipo         INT(3) UNSIGNED AUTO_INCREMENT,
    descricao_tipo  VARCHAR(100) NOT NULL,
    PRIMARY KEY (id_tipo),
    UNIQUE KEY uk_tipo_produto_descricao (descricao_tipo)
);

-- ITENS_CARDAPIO_TIPO
CREATE TABLE itens_cardapio_tipo (
    id_tipo       INT(6) UNSIGNED AUTO_INCREMENT,
    descricao     VARCHAR(50) NOT NULL,
    PRIMARY KEY (id_tipo),
    UNIQUE KEY uk_itens_cardapio_tipo_descricao (descricao)
);

-- CARDAPIO
-- UNIQUE (data_servico, turno): um cardápio por dia/turno
CREATE TABLE cardapio (
    id_cardapio   INT(6) UNSIGNED AUTO_INCREMENT,
    data_servico  DATE NOT NULL,
    turno         VARCHAR(20) NOT NULL,
    PRIMARY KEY (id_cardapio),
    UNIQUE KEY uk_cardapio_data_turno (data_servico, turno)
);

-- FILA
CREATE TABLE fila (
    id_fila    INT(6) UNSIGNED AUTO_INCREMENT,
    tipo_fila  VARCHAR(20) NOT NULL,
    PRIMARY KEY (id_fila),
    UNIQUE KEY uk_fila_tipo (tipo_fila)
);

-- =====================================================================
-- TABELAS COM CHAVES NATURAIS (CPF / CNPJ)
-- =====================================================================

-- FUNCIONARIO (PK = CPF; FK -> BAIRRO)
CREATE TABLE funcionario (
    cpf              VARCHAR(11) NOT NULL,
    nome_funcionario VARCHAR(100) NOT NULL,
    cargo            VARCHAR(50) NOT NULL,
    privilegios      VARCHAR(200) NOT NULL,
    id_bairro        INT(6) UNSIGNED NOT NULL,
    endereco         VARCHAR(100) NOT NULL,
    PRIMARY KEY (cpf),
    CONSTRAINT fk_funcionario_bairro FOREIGN KEY (id_bairro)
        REFERENCES bairro (id_bairro)
);

-- FORNECEDORES (PK = CNPJ; FK -> BAIRRO)
CREATE TABLE fornecedores (
    cnpj          VARCHAR(14) NOT NULL,
    razao_social  VARCHAR(100) NOT NULL,
    telefone      VARCHAR(15) NULL,
    id_bairro     INT(6) UNSIGNED NOT NULL,
    endereco      VARCHAR(100) NOT NULL,
    PRIMARY KEY (cnpj),
    CONSTRAINT fk_fornecedores_bairro FOREIGN KEY (id_bairro)
        REFERENCES bairro (id_bairro)
);

-- =====================================================================
-- TABELAS DEPENDENTES DE NÍVEL 2
-- =====================================================================

-- USUARIO (FK -> CATEGORIA_USUARIO, BAIRRO)
CREATE TABLE usuario (
    id_usuario    INT(6) UNSIGNED AUTO_INCREMENT,
    matricula     INT(11) UNSIGNED NOT NULL,
    nome_completo VARCHAR(100) NOT NULL,
    id_categoria  INT(3) UNSIGNED NOT NULL,
    id_bairro     INT(6) UNSIGNED NOT NULL,
    endereco      VARCHAR(100) NOT NULL,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uk_usuario_matricula (matricula),
    CONSTRAINT fk_usuario_categoria FOREIGN KEY (id_categoria)
        REFERENCES categoria_usuario (id_categoria),
    CONSTRAINT fk_usuario_bairro FOREIGN KEY (id_bairro)
        REFERENCES bairro (id_bairro)
);

-- PRODUTO (FK -> TIPO_PRODUTO)
CREATE TABLE produto (
    id_produto      INT(6) UNSIGNED AUTO_INCREMENT,
    nome_produto    VARCHAR(100) NOT NULL,
    unidade_medida  VARCHAR(2) NOT NULL,
    id_tipo_produto INT(3) UNSIGNED NOT NULL,
    PRIMARY KEY (id_produto),
    CONSTRAINT fk_produto_tipo FOREIGN KEY (id_tipo_produto)
        REFERENCES tipo_produto (id_tipo)
);

-- ITENS_CARDAPIO (FK -> CARDAPIO, ITENS_CARDAPIO_TIPO)
CREATE TABLE itens_cardapio (
    id_itens_cardapio INT(6) UNSIGNED AUTO_INCREMENT,
    id_cardapio       INT(6) UNSIGNED NOT NULL,
    id_tipo           INT(6) UNSIGNED NOT NULL,
    descricao         VARCHAR(200) NULL,
    PRIMARY KEY (id_itens_cardapio),
    CONSTRAINT fk_itenscardapio_cardapio FOREIGN KEY (id_cardapio)
        REFERENCES cardapio (id_cardapio),
    CONSTRAINT fk_itenscardapio_tipo FOREIGN KEY (id_tipo)
        REFERENCES itens_cardapio_tipo (id_tipo)
);

-- ITENS_CARDAPIO_TIPO_CATEGORIAS_VALORES
-- Preço único por combinação tipo de item × categoria de usuário
CREATE TABLE itens_cardapio_tipo_categorias_valores (
    id_categoria_valores INT(6) UNSIGNED AUTO_INCREMENT,
    id_tipo              INT(6) UNSIGNED NOT NULL,
    id_categoria         INT(3) UNSIGNED NOT NULL,
    valor                DECIMAL(8,2) NOT NULL,
    PRIMARY KEY (id_categoria_valores),
    UNIQUE KEY uk_preco_tipo_categoria (id_tipo, id_categoria),
    CONSTRAINT fk_ictcv_tipo FOREIGN KEY (id_tipo)
        REFERENCES itens_cardapio_tipo (id_tipo),
    CONSTRAINT fk_ictcv_categoria FOREIGN KEY (id_categoria)
        REFERENCES categoria_usuario (id_categoria)
);

-- CARTEIRA_DIGITAL (FK -> USUARIO) — relação 1:1 com usuário
CREATE TABLE carteira_digital (
    id_carteira INT(6) UNSIGNED AUTO_INCREMENT,
    id_usuario  INT(6) UNSIGNED NOT NULL,
    saldo       DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (id_carteira),
    UNIQUE KEY uk_carteira_usuario (id_usuario),
    CONSTRAINT fk_carteira_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario)
);

-- =====================================================================
-- TABELAS DEPENDENTES DE NÍVEL 3
-- =====================================================================

-- ESTOQUE (FK -> PRODUTO) — um registro de saldo por produto
CREATE TABLE estoque (
    id_estoque       INT(6) UNSIGNED AUTO_INCREMENT,
    quantidade       INT(8) UNSIGNED NOT NULL DEFAULT 0,
    data_atualizacao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                     ON UPDATE CURRENT_TIMESTAMP,
    id_produto       INT(6) UNSIGNED NOT NULL,
    PRIMARY KEY (id_estoque),
    UNIQUE KEY uk_estoque_produto (id_produto),
    CONSTRAINT fk_estoque_produto FOREIGN KEY (id_produto)
        REFERENCES produto (id_produto)
);

-- MOVIMENTO_ESTOQUE (FK -> PRODUTO, FORNECEDORES)
CREATE TABLE movimento_estoque (
    id_movimento    INT(8) UNSIGNED AUTO_INCREMENT,
    tipo_movimento  VARCHAR(10) NOT NULL,
    quantidade_mov  INT(8) UNSIGNED NOT NULL,
    data_movimento  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_produto      INT(6) UNSIGNED NOT NULL,
    cnpj_fornecedor VARCHAR(14) NULL,
    PRIMARY KEY (id_movimento),
    CONSTRAINT fk_movimento_produto FOREIGN KEY (id_produto)
        REFERENCES produto (id_produto),
    CONSTRAINT fk_movimento_fornecedor FOREIGN KEY (cnpj_fornecedor)
        REFERENCES fornecedores (cnpj)
);

-- RECEITA
CREATE TABLE receita (
    id_receita  INT(6) UNSIGNED AUTO_INCREMENT,
    nome        VARCHAR(50) NOT NULL,
    rendimento  DECIMAL(8,2) NOT NULL,
    preparo     VARCHAR(2000) NOT NULL,
    PRIMARY KEY (id_receita)
);

-- CONTA_RECEBER
CREATE TABLE conta_receber (
    id_conta_receber  INT(6) UNSIGNED AUTO_INCREMENT,
    valor             DECIMAL(8,2) NOT NULL,
    data_prevista     DATE NOT NULL,
    origem            VARCHAR(50) NOT NULL,
    PRIMARY KEY (id_conta_receber)
);

-- =====================================================================
-- TABELAS DEPENDENTES DE NÍVEL 4 (associativas)
-- =====================================================================

-- ITENS_CARDAPIO_RECEITA (FK -> ITENS_CARDAPIO, RECEITA)
CREATE TABLE itens_cardapio_receita (
    id_itens_cardapio_receita INT(6) UNSIGNED AUTO_INCREMENT,
    qtd_necessaria            DECIMAL(8,2) NOT NULL,
    id_itens_cardapio         INT(6) UNSIGNED NOT NULL,
    id_receita                INT(6) UNSIGNED NOT NULL,
    PRIMARY KEY (id_itens_cardapio_receita),
    CONSTRAINT fk_icr_itenscardapio FOREIGN KEY (id_itens_cardapio)
        REFERENCES itens_cardapio (id_itens_cardapio),
    CONSTRAINT fk_icr_receita FOREIGN KEY (id_receita)
        REFERENCES receita (id_receita)
);

-- ITENS_RECEITA_PRODUTO (FK -> RECEITA, PRODUTO)
CREATE TABLE itens_receita_produto (
    id_receita_produto INT(6) UNSIGNED AUTO_INCREMENT,
    qtd_produto        DECIMAL(8,2) NOT NULL,
    id_receita         INT(6) UNSIGNED NOT NULL,
    id_produto         INT(6) UNSIGNED NOT NULL,
    PRIMARY KEY (id_receita_produto),
    CONSTRAINT fk_irp_receita FOREIGN KEY (id_receita)
        REFERENCES receita (id_receita),
    CONSTRAINT fk_irp_produto FOREIGN KEY (id_produto)
        REFERENCES produto (id_produto)
);

-- ITENS_FILA (FK -> FILA, USUARIO)
CREATE TABLE itens_fila (
    id_itens_fila     INT(6) UNSIGNED AUTO_INCREMENT,
    id_usuario        INT(6) UNSIGNED NOT NULL,
    id_fila           INT(6) UNSIGNED NOT NULL,
    horario_inscricao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    situacao          VARCHAR(20) NOT NULL DEFAULT 'em espera',
    PRIMARY KEY (id_itens_fila),
    CONSTRAINT fk_itensfila_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario),
    CONSTRAINT fk_itensfila_fila FOREIGN KEY (id_fila)
        REFERENCES fila (id_fila)
);

-- REFEICAO (FK -> USUARIO, ITENS_CARDAPIO, ITENS_CARDAPIO_TIPO_CATEGORIAS_VALORES)
CREATE TABLE refeicao (
    id_refeicao          INT(8) UNSIGNED AUTO_INCREMENT,
    id_usuario           INT(6) UNSIGNED NOT NULL,
    id_itens_cardapio    INT(6) UNSIGNED NOT NULL,
    id_categoria_valores INT(6) UNSIGNED NOT NULL,
    valor                DECIMAL(8,2) NOT NULL,
    horario_entrada      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    horario_saida        DATETIME NULL,
    PRIMARY KEY (id_refeicao),
    CONSTRAINT fk_refeicao_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuario (id_usuario),
    CONSTRAINT fk_refeicao_itenscardapio FOREIGN KEY (id_itens_cardapio)
        REFERENCES itens_cardapio (id_itens_cardapio),
    CONSTRAINT fk_refeicao_categoriavalores FOREIGN KEY (id_categoria_valores)
        REFERENCES itens_cardapio_tipo_categorias_valores (id_categoria_valores)
);

-- RECARGA_HISTORICO (FK -> CARTEIRA_DIGITAL)
CREATE TABLE recarga_historico (
    id_recarga  INT(6) UNSIGNED AUTO_INCREMENT,
    id_carteira INT(6) UNSIGNED NOT NULL,
    valor       DECIMAL(8,2) NOT NULL,
    data        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_recarga),
    CONSTRAINT fk_recarga_carteira FOREIGN KEY (id_carteira)
        REFERENCES carteira_digital (id_carteira)
);

-- CONTA_PAGAR (FK -> FORNECEDORES)
CREATE TABLE conta_pagar (
    id_conta_pagar  INT(6) UNSIGNED AUTO_INCREMENT,
    valor           DECIMAL(10,2) NOT NULL,
    data_vencimento DATE NOT NULL,
    status          VARCHAR(20) NOT NULL DEFAULT 'PENDENTE',
    origem          VARCHAR(20) NOT NULL,
    cnpj_fornecedor VARCHAR(14) NULL,
    PRIMARY KEY (id_conta_pagar),
    CONSTRAINT fk_contapagar_fornecedor FOREIGN KEY (cnpj_fornecedor)
        REFERENCES fornecedores (cnpj)
);

-- =====================================================================
-- FIM DA CRIAÇÃO DAS TABELAS
-- =====================================================================
