# SIGRU — Roteiro de sentenças SQL para teste / demo

Banco: `sigru`  
Pré-requisito: importar na ordem `sigru_create.sql` → `sigru_seed.sql` → `sigru_avancado.sql`.

```sql
USE sigru;
```

---

## 0. Checagem rápida (objetos avançados existem?)

```sql
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
```

Esperado aproximado: views **8**, triggers **2**, procedures **3**, indexes_custom **5**.

---

## 1. Índices — listar e inspecionar

```sql
SHOW INDEX FROM refeicao WHERE Key_name = 'idx_refeicao_horario';
SHOW INDEX FROM itens_fila WHERE Key_name = 'idx_itens_fila_situacao';
SHOW INDEX FROM movimento_estoque WHERE Key_name = 'idx_movimento_data';
SHOW INDEX FROM conta_pagar WHERE Key_name = 'idx_conta_pagar_status_venc';
SHOW INDEX FROM recarga_historico WHERE Key_name = 'idx_recarga_data';
```

```sql
SELECT table_name, index_name, column_name, seq_in_index
FROM information_schema.statistics
WHERE table_schema = DATABASE()
  AND index_name LIKE 'idx_%'
ORDER BY table_name, index_name, seq_in_index;
```

---

## 2. Views — testar cada uma

### 2.1 `vw_dashboard_dia`

```sql
SELECT * FROM vw_dashboard_dia;
```

### 2.2 `vw_estoque_status`

```sql
SELECT nome_produto, quantidade, unidade_medida, status_estoque
FROM vw_estoque_status
ORDER BY quantidade ASC;
```

### 2.3 `vw_ultimas_refeicoes`

```sql
SELECT nome_completo, turno, data_servico, valor, horario_entrada
FROM vw_ultimas_refeicoes
ORDER BY horario_entrada DESC
LIMIT 10;
```

### 2.4 `vw_cardapio_hoje`

```sql
SELECT * FROM vw_cardapio_hoje
ORDER BY turno, tipo_item;
```

### 2.5 `vw_fila_atual`

```sql
SELECT nome_completo, tipo_fila, situacao, horario_inscricao
FROM vw_fila_atual
ORDER BY FIELD(situacao, 'atendendo', 'em espera', 'finalizado'),
         horario_inscricao;
```

### 2.6 `vw_refeicoes_por_categoria`

```sql
SELECT * FROM vw_refeicoes_por_categoria
ORDER BY data_refeicao DESC, categoria;
```

### 2.7 `vw_contas_pagar_pendentes`

```sql
SELECT * FROM vw_contas_pagar_pendentes
ORDER BY data_vencimento;
```

### 2.8 `vw_carteiras_saldo`

```sql
SELECT matricula, nome_completo, categoria, saldo
FROM vw_carteiras_saldo
ORDER BY saldo DESC;
```

---

## 3. CRUD — cadastro, consulta, alteração, exclusão

### 3.1 INSERT (cadastro)

```sql
-- Usuário
INSERT INTO usuario (matricula, nome_completo, id_categoria, id_bairro, endereco)
VALUES (202410099, 'Pedro Henrique Tavares', 1, 1, 'Rua Nova, 99');

-- Fornecedor
INSERT INTO fornecedores (cnpj, razao_social, telefone, id_bairro, endereco)
VALUES ('11223344000199', 'Padaria Pão Quente Ltda', '38966665555', 1, 'Rua do Pão, 33');

-- Movimento de estoque (dispara trigger de estoque)
INSERT INTO movimento_estoque (
    tipo_movimento, quantidade_mov, data_movimento, id_produto, cnpj_fornecedor
) VALUES ('ENTRADA', 50, NOW(), 2, '12345678000190');
```

### 3.2 SELECT + JOIN (consultas)

```sql
-- Usuários × categoria
SELECT u.id_usuario, u.matricula, u.nome_completo, c.descricao AS categoria
FROM usuario u
JOIN categoria_usuario c ON c.id_categoria = u.id_categoria;

-- Produtos × estoque × tipo
SELECT p.id_produto, p.nome_produto, t.descricao_tipo,
       e.quantidade, e.data_atualizacao
FROM produto p
JOIN tipo_produto t ON t.id_tipo = p.id_tipo_produto
LEFT JOIN estoque e ON e.id_produto = p.id_produto
ORDER BY e.quantidade ASC;

-- Fila em espera
SELECT f.tipo_fila, u.nome_completo, i.horario_inscricao, i.situacao
FROM itens_fila i
JOIN fila f ON f.id_fila = i.id_fila
JOIN usuario u ON u.id_usuario = i.id_usuario
WHERE i.situacao = 'em espera'
ORDER BY i.horario_inscricao;

-- Refeições do dia × cardápio
SELECT r.id_refeicao, u.nome_completo, ca.data_servico, ca.turno,
       ic.descricao AS item_cardapio, r.valor, r.horario_entrada
FROM refeicao r
JOIN usuario u ON u.id_usuario = r.id_usuario
JOIN itens_cardapio ic ON ic.id_itens_cardapio = r.id_itens_cardapio
JOIN cardapio ca ON ca.id_cardapio = ic.id_cardapio
WHERE ca.data_servico = CURDATE();

-- Contas a pagar pendentes × fornecedor
SELECT cp.id_conta_pagar, f.razao_social, cp.valor, cp.data_vencimento, cp.status
FROM conta_pagar cp
LEFT JOIN fornecedores f ON f.cnpj = cp.cnpj_fornecedor
WHERE cp.status = 'PENDENTE'
ORDER BY cp.data_vencimento;

-- Carteiras × usuário
SELECT u.nome_completo, cd.saldo
FROM carteira_digital cd
JOIN usuario u ON u.id_usuario = cd.id_usuario
ORDER BY cd.saldo DESC;
```

### 3.3 UPDATE (alteração)

```sql
UPDATE fornecedores
SET telefone = '38900001234'
WHERE cnpj = '12345678000190';

UPDATE refeicao
SET horario_saida = NOW()
WHERE id_refeicao = 2;

UPDATE conta_pagar
SET status = 'PAGO'
WHERE id_conta_pagar = 1;

UPDATE itens_fila
SET situacao = 'atendendo'
WHERE id_itens_fila = 4;
```

> Não use `UPDATE` manual em `estoque` / `carteira_digital` para “simular” o sistema: isso fura o fluxo das triggers/procedures.

### 3.4 DELETE (exclusão)

```sql
DELETE FROM itens_fila
WHERE id_itens_fila = 1
  AND situacao = 'finalizado';

-- Só se não houver FK impedindo:
DELETE FROM fornecedores
WHERE cnpj = '11223344000199';
```

> `DELETE` em `movimento_estoque` **não** desfaz o saldo automaticamente (a trigger só age no `INSERT`). Para demo de estoque, prefira `CALL sp_movimentar_estoque`.

---

## 4. Triggers — provar o efeito automático

### 4.1 `trg_movimento_atualiza_estoque`

Antes / depois da entrada:

```sql
-- ANTES
SELECT id_produto, quantidade, data_atualizacao
FROM estoque
WHERE id_produto = 1;

-- Dispara a trigger (via INSERT direto ou via procedure)
CALL sp_movimentar_estoque('ENTRADA', 5, 1, '12345678000190');

-- DEPOIS (quantidade deve subir +5)
SELECT id_produto, quantidade, data_atualizacao
FROM estoque
WHERE id_produto = 1;
```

Saída (deve baixar estoque; se faltar saldo, erro):

```sql
CALL sp_movimentar_estoque('SAIDA', 2, 1, NULL);

SELECT id_produto, quantidade
FROM estoque
WHERE id_produto = 1;
```

Teste negativo (estoque insuficiente — deve falhar):

```sql
-- Ajuste o valor para algo maior que o saldo atual
CALL sp_movimentar_estoque('SAIDA', 999999, 1, NULL);
```

### 4.2 `trg_refeicao_debita_carteira`

```sql
-- ANTES
SELECT id_usuario, saldo FROM carteira_digital WHERE id_usuario = 2;

-- Preferencial: procedure (também finaliza fila se passar id_itens_fila)
CALL sp_registrar_refeicao(2, 1, 1, 2.00, NULL);

-- DEPOIS (saldo deve cair 2.00)
SELECT id_usuario, saldo FROM carteira_digital WHERE id_usuario = 2;
```

Teste negativo (saldo insuficiente — deve falhar):

```sql
CALL sp_registrar_refeicao(2, 1, 1, 999999.00, NULL);
```

---

## 5. Procedures — testar cada uma

### 5.1 `sp_recarregar_carteira`

```sql
SELECT saldo FROM carteira_digital WHERE id_usuario = 2;

CALL sp_recarregar_carteira(2, 10.00);

SELECT saldo FROM carteira_digital WHERE id_usuario = 2;

SELECT * FROM recarga_historico
WHERE id_carteira = (SELECT id_carteira FROM carteira_digital WHERE id_usuario = 2)
ORDER BY data DESC
LIMIT 5;
```

### 5.2 `sp_movimentar_estoque`

```sql
CALL sp_movimentar_estoque('ENTRADA', 5, 1, '12345678000190');
CALL sp_movimentar_estoque('SAIDA', 2, 1, NULL);

SELECT * FROM movimento_estoque
WHERE id_produto = 1
ORDER BY data_movimento DESC
LIMIT 5;

SELECT * FROM vw_estoque_status WHERE id_produto = 1;
```

### 5.3 `sp_registrar_refeicao`

```sql
-- Veja um item de fila em espera (se houver) para passar no último parâmetro
SELECT id_itens_fila, id_usuario, situacao
FROM itens_fila
WHERE situacao IN ('em espera', 'atendendo')
ORDER BY horario_inscricao
LIMIT 5;

-- Exemplo: usuário 2, item cardápio 1, preço 1, valor 2.00, fila 4 (ajuste IDs do seu seed)
CALL sp_registrar_refeicao(2, 1, 1, 2.00, 4);

SELECT * FROM vw_ultimas_refeicoes ORDER BY horario_entrada DESC LIMIT 5;
SELECT situacao FROM itens_fila WHERE id_itens_fila = 4;
SELECT saldo FROM carteira_digital WHERE id_usuario = 2;
```

---

## 6. Roteiro curto para a banca (ordem sugerida)

1. Checagem da seção **0** (contagens).  
2. `SHOW INDEX` / listagem da seção **1**.  
3. `SELECT *` nas 8 views (seção **2**).  
4. Um INSERT + um SELECT com JOIN + um UPDATE (seção **3**).  
5. Demo trigger estoque: antes → `CALL` → depois (seção **4.1**).  
6. Demo trigger carteira + `sp_registrar_refeicao` (seções **4.2** e **5.3**).  
7. `CALL sp_recarregar_carteira` e mostrar `recarga_historico` (seção **5.1**).

---

## 7. Comandos úteis de inspeção

```sql
SHOW FULL TABLES WHERE Table_type = 'VIEW';
SHOW TRIGGERS;
SHOW PROCEDURE STATUS WHERE Db = 'sigru';

SHOW CREATE VIEW vw_dashboard_dia\G
SHOW CREATE TRIGGER trg_movimento_atualiza_estoque\G
SHOW CREATE PROCEDURE sp_recarregar_carteira\G
```
