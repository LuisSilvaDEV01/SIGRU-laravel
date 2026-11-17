-- =====================================================================
-- SIGRU - Recursos avançados (Banco de Dados II / Seminário II)
-- Views | Índices de desempenho | Triggers | Stored Procedures
--
-- Ordem de importação:
--   1) sigru_create.sql
--   2) sigru_seed.sql
--   3) sigru_avancado.sql   <-- este arquivo
--
-- Observações:
--   - UNIQUE de negócio (matricula, cardápio data+turno, etc.) já estão
--     no sigru_create.sql — aqui ficam só índices de DESEMPENHO.
--   - Triggers automatizam estoque e débito de carteira.
--   - Se o seed for reexecutado depois deste arquivo, ele remove os
--     triggers; rode este script novamente após o seed.
--   - Enquanto o PHP antigo ainda fizer UPDATE manual de estoque/carteira,
--     haverá dupla atualização nesses fluxos da aplicação.
-- =====================================================================
USE sigru;
SET NAMES utf8mb4;

-- =====================================================================
-- 1) ÍNDICES DE DESEMPENHO
-- (não recria UNIQUE já existentes no DDL base)
-- =====================================================================

-- Helper: drop index se existir
SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'refeicao'
              AND index_name = 'idx_refeicao_horario'
        ),
        'DROP INDEX idx_refeicao_horario ON refeicao',
        'SELECT 1'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'itens_fila'
              AND index_name = 'idx_itens_fila_situacao'
        ),
        'DROP INDEX idx_itens_fila_situacao ON itens_fila',
        'SELECT 1'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'movimento_estoque'
              AND index_name = 'idx_movimento_data'
        ),
        'DROP INDEX idx_movimento_data ON movimento_estoque',
        'SELECT 1'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'conta_pagar'
              AND index_name = 'idx_conta_pagar_status_venc'
        ),
        'DROP INDEX idx_conta_pagar_status_venc ON conta_pagar',
        'SELECT 1'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (
    SELECT IF(
        EXISTS(
            SELECT 1 FROM information_schema.statistics
            WHERE table_schema = DATABASE()
              AND table_name = 'recarga_historico'
              AND index_name = 'idx_recarga_data'
        ),
        'DROP INDEX idx_recarga_data ON recarga_historico',
        'SELECT 1'
    )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Consultas por data de refeição / relatórios
CREATE INDEX idx_refeicao_horario
    ON refeicao (horario_entrada);

-- Fila filtrada por situação e ordenada por inscrição
CREATE INDEX idx_itens_fila_situacao
    ON itens_fila (situacao, horario_inscricao);

-- Histórico de movimentos por período
CREATE INDEX idx_movimento_data
    ON movimento_estoque (data_movimento);

-- Contas a pagar por status e vencimento
CREATE INDEX idx_conta_pagar_status_venc
    ON conta_pagar (status, data_vencimento);

-- Histórico de recargas por data
-- Obs.: colunas cobertas por FK/UNIQUE no DDL não são reindexadas aqui
CREATE INDEX idx_recarga_data
    ON recarga_historico (data);

-- =====================================================================
-- 2) VIEWS
-- =====================================================================

DROP VIEW IF EXISTS vw_dashboard_dia;
DROP VIEW IF EXISTS vw_estoque_status;
DROP VIEW IF EXISTS vw_ultimas_refeicoes;
DROP VIEW IF EXISTS vw_cardapio_hoje;
DROP VIEW IF EXISTS vw_fila_atual;
DROP VIEW IF EXISTS vw_refeicoes_por_categoria;
DROP VIEW IF EXISTS vw_contas_pagar_pendentes;
DROP VIEW IF EXISTS vw_carteiras_saldo;

-- Indicadores do dia (dashboard)
CREATE VIEW vw_dashboard_dia AS
SELECT
    (SELECT COUNT(*) FROM usuario) AS total_usuarios,
    (SELECT COUNT(*) FROM refeicao WHERE DATE(horario_entrada) = CURDATE()) AS refeicoes_hoje,
    (SELECT COUNT(*) FROM itens_fila WHERE situacao = 'em espera') AS fila_espera,
    (SELECT COALESCE(SUM(valor), 0) FROM refeicao WHERE DATE(horario_entrada) = CURDATE()) AS caixa_hoje;

-- Estoque com status derivado da quantidade
CREATE VIEW vw_estoque_status AS
SELECT
    p.id_produto,
    p.nome_produto,
    p.unidade_medida,
    tp.descricao_tipo,
    COALESCE(e.quantidade, 0) AS quantidade,
    e.data_atualizacao,
    CASE
        WHEN COALESCE(e.quantidade, 0) = 0 THEN 'Zerado'
        WHEN COALESCE(e.quantidade, 0) < 80 THEN 'Baixo'
        ELSE 'OK'
    END AS status_estoque
FROM produto p
JOIN tipo_produto tp ON tp.id_tipo = p.id_tipo_produto
LEFT JOIN estoque e ON e.id_produto = p.id_produto;

-- Refeições com usuário e turno do cardápio
CREATE VIEW vw_ultimas_refeicoes AS
SELECT
    r.id_refeicao,
    u.nome_completo,
    ca.turno,
    ca.data_servico,
    r.valor,
    r.horario_entrada,
    r.horario_saida
FROM refeicao r
JOIN usuario u ON u.id_usuario = r.id_usuario
JOIN itens_cardapio ic ON ic.id_itens_cardapio = r.id_itens_cardapio
JOIN cardapio ca ON ca.id_cardapio = ic.id_cardapio;

-- Cardápio do dia (data_servico já é DATE no DDL)
CREATE VIEW vw_cardapio_hoje AS
SELECT
    ca.id_cardapio,
    ca.turno,
    ict.descricao AS tipo_item,
    ic.descricao AS detalhe,
    ic.id_itens_cardapio
FROM cardapio ca
JOIN itens_cardapio ic ON ic.id_cardapio = ca.id_cardapio
JOIN itens_cardapio_tipo ict ON ict.id_tipo = ic.id_tipo
WHERE ca.data_servico = CURDATE();

-- Situação atual da fila virtual
CREATE VIEW vw_fila_atual AS
SELECT
    i.id_itens_fila,
    i.id_usuario,
    u.nome_completo,
    u.matricula,
    cat.descricao AS categoria,
    f.tipo_fila,
    i.horario_inscricao,
    i.situacao
FROM itens_fila i
JOIN usuario u ON u.id_usuario = i.id_usuario
JOIN categoria_usuario cat ON cat.id_categoria = u.id_categoria
JOIN fila f ON f.id_fila = i.id_fila;

-- Agregado de refeições por categoria e dia (base de relatórios)
CREATE VIEW vw_refeicoes_por_categoria AS
SELECT
    c.descricao AS categoria,
    DATE(r.horario_entrada) AS data_refeicao,
    COUNT(*) AS qtd,
    SUM(r.valor) AS total
FROM refeicao r
JOIN usuario u ON u.id_usuario = r.id_usuario
JOIN categoria_usuario c ON c.id_categoria = u.id_categoria
GROUP BY c.descricao, DATE(r.horario_entrada);

-- Contas a pagar ainda pendentes
CREATE VIEW vw_contas_pagar_pendentes AS
SELECT
    cp.id_conta_pagar,
    cp.valor,
    cp.data_vencimento,
    cp.status,
    cp.origem,
    f.razao_social
FROM conta_pagar cp
LEFT JOIN fornecedores f ON f.cnpj = cp.cnpj_fornecedor
WHERE cp.status = 'PENDENTE';

-- Saldo das carteiras digitais
CREATE VIEW vw_carteiras_saldo AS
SELECT
    cd.id_carteira,
    u.matricula,
    u.nome_completo,
    c.descricao AS categoria,
    cd.saldo
FROM carteira_digital cd
JOIN usuario u ON u.id_usuario = cd.id_usuario
JOIN categoria_usuario c ON c.id_categoria = u.id_categoria;

-- =====================================================================
-- 3) TRIGGERS (automatização)
-- =====================================================================

DROP TRIGGER IF EXISTS trg_movimento_atualiza_estoque;
DROP TRIGGER IF EXISTS trg_refeicao_debita_carteira;

DELIMITER $$

-- Movimento de estoque → atualiza saldo automaticamente
CREATE TRIGGER trg_movimento_atualiza_estoque
AFTER INSERT ON movimento_estoque
FOR EACH ROW
BEGIN
    DECLARE v_id_estoque INT UNSIGNED DEFAULT NULL;
    DECLARE v_qtd INT UNSIGNED DEFAULT NULL;

    SELECT id_estoque, quantidade
      INTO v_id_estoque, v_qtd
      FROM estoque
     WHERE id_produto = NEW.id_produto
     LIMIT 1;

    IF NEW.tipo_movimento = 'ENTRADA' THEN
        IF v_id_estoque IS NULL THEN
            INSERT INTO estoque (quantidade, data_atualizacao, id_produto)
            VALUES (NEW.quantidade_mov, NOW(), NEW.id_produto);
        ELSE
            UPDATE estoque
               SET quantidade = quantidade + NEW.quantidade_mov,
                   data_atualizacao = NOW()
             WHERE id_estoque = v_id_estoque;
        END IF;

    ELSEIF NEW.tipo_movimento = 'SAIDA' THEN
        IF v_id_estoque IS NULL OR v_qtd < NEW.quantidade_mov THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Quantidade insuficiente em estoque para a saida.';
        END IF;

        UPDATE estoque
           SET quantidade = quantidade - NEW.quantidade_mov,
               data_atualizacao = NOW()
         WHERE id_estoque = v_id_estoque;
    ELSE
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'tipo_movimento invalido. Use ENTRADA ou SAIDA.';
    END IF;
END$$

-- Refeição → debita carteira automaticamente (antes do INSERT)
CREATE TRIGGER trg_refeicao_debita_carteira
BEFORE INSERT ON refeicao
FOR EACH ROW
BEGIN
    DECLARE v_saldo DECIMAL(8,2) DEFAULT NULL;

    SELECT saldo
      INTO v_saldo
      FROM carteira_digital
     WHERE id_usuario = NEW.id_usuario
     LIMIT 1;

    IF v_saldo IS NULL THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Usuario sem carteira digital cadastrada.';
    END IF;

    IF v_saldo < NEW.valor THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Saldo insuficiente na carteira digital.';
    END IF;

    UPDATE carteira_digital
       SET saldo = saldo - NEW.valor
     WHERE id_usuario = NEW.id_usuario;
END$$

DELIMITER ;

-- =====================================================================
-- 4) STORED PROCEDURES
-- =====================================================================

DROP PROCEDURE IF EXISTS sp_movimentar_estoque;
DROP PROCEDURE IF EXISTS sp_recarregar_carteira;
DROP PROCEDURE IF EXISTS sp_registrar_refeicao;

DELIMITER $$

-- Registra movimento; o trigger atualiza o estoque
CREATE PROCEDURE sp_movimentar_estoque(
    IN p_tipo_movimento VARCHAR(10),
    IN p_quantidade INT UNSIGNED,
    IN p_id_produto INT UNSIGNED,
    IN p_cnpj_fornecedor VARCHAR(14)
)
BEGIN
    IF p_tipo_movimento NOT IN ('ENTRADA', 'SAIDA') THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Tipo de movimento invalido. Use ENTRADA ou SAIDA.';
    END IF;

    IF p_quantidade IS NULL OR p_quantidade = 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Quantidade deve ser maior que zero.';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM produto WHERE id_produto = p_id_produto) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Produto informado nao existe.';
    END IF;

    INSERT INTO movimento_estoque (
        tipo_movimento, quantidade_mov, data_movimento, id_produto, cnpj_fornecedor
    ) VALUES (
        p_tipo_movimento, p_quantidade, NOW(), p_id_produto, p_cnpj_fornecedor
    );
END$$

-- Recarga de carteira + histórico (transação)
CREATE PROCEDURE sp_recarregar_carteira(
    IN p_id_usuario INT UNSIGNED,
    IN p_valor DECIMAL(8,2)
)
BEGIN
    DECLARE v_id_carteira INT UNSIGNED DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF p_valor IS NULL OR p_valor <= 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Valor de recarga deve ser maior que zero.';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM usuario WHERE id_usuario = p_id_usuario) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Usuario informado nao existe.';
    END IF;

    START TRANSACTION;

    SELECT id_carteira
      INTO v_id_carteira
      FROM carteira_digital
     WHERE id_usuario = p_id_usuario
     LIMIT 1;

    IF v_id_carteira IS NULL THEN
        INSERT INTO carteira_digital (id_usuario, saldo)
        VALUES (p_id_usuario, 0.00);
        SET v_id_carteira = LAST_INSERT_ID();
    END IF;

    UPDATE carteira_digital
       SET saldo = saldo + p_valor
     WHERE id_carteira = v_id_carteira;

    INSERT INTO recarga_historico (id_carteira, valor, data)
    VALUES (v_id_carteira, p_valor, NOW());

    COMMIT;
END$$

-- Registra refeição (trigger debita carteira) e finaliza a fila
CREATE PROCEDURE sp_registrar_refeicao(
    IN p_id_usuario INT UNSIGNED,
    IN p_id_itens_cardapio INT UNSIGNED,
    IN p_id_categoria_valores INT UNSIGNED,
    IN p_valor DECIMAL(8,2),
    IN p_id_itens_fila INT UNSIGNED
)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF p_valor IS NULL OR p_valor <= 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Valor da refeicao invalido.';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM usuario WHERE id_usuario = p_id_usuario) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Usuario informado nao existe.';
    END IF;

    START TRANSACTION;

    INSERT INTO refeicao (
        id_usuario, id_itens_cardapio, id_categoria_valores, valor, horario_entrada
    ) VALUES (
        p_id_usuario, p_id_itens_cardapio, p_id_categoria_valores, p_valor, NOW()
    );

    IF p_id_itens_fila IS NOT NULL AND p_id_itens_fila > 0 THEN
        UPDATE itens_fila
           SET situacao = 'finalizado'
         WHERE id_itens_fila = p_id_itens_fila;
    END IF;

    COMMIT;
END$$

DELIMITER ;

-- =====================================================================
-- 5) VERIFICAÇÃO RÁPIDA (não altera dados de negócio)
-- =====================================================================

SELECT 'views' AS recurso, COUNT(*) AS qtd
FROM information_schema.views
WHERE table_schema = DATABASE() AND table_name LIKE 'vw_%'
UNION ALL
SELECT 'triggers', COUNT(*)
FROM information_schema.triggers
WHERE trigger_schema = DATABASE()
UNION ALL
SELECT 'procedures', COUNT(*)
FROM information_schema.routines
WHERE routine_schema = DATABASE() AND routine_type = 'PROCEDURE'
UNION ALL
SELECT 'indexes_custom', COUNT(DISTINCT index_name)
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND index_name IN (
      'idx_refeicao_horario',
      'idx_itens_fila_situacao',
      'idx_movimento_data',
      'idx_conta_pagar_status_venc',
      'idx_recarga_data'
  );

SELECT * FROM vw_dashboard_dia;
SELECT * FROM vw_estoque_status ORDER BY quantidade ASC LIMIT 5;
SELECT * FROM vw_cardapio_hoje;
SELECT * FROM vw_fila_atual ORDER BY FIELD(situacao,'atendendo','em espera','finalizado'), horario_inscricao;

-- Exemplos para demonstração manual (descomente se quiser testar):
-- CALL sp_recarregar_carteira(2, 10.00);
-- CALL sp_movimentar_estoque('ENTRADA', 5, 1, '12345678000190');
-- CALL sp_movimentar_estoque('SAIDA', 2, 1, NULL);
-- CALL sp_registrar_refeicao(2, 1, 1, 8.50, 4);

-- =====================================================================
-- FIM — sigru_avancado.sql
-- =====================================================================
