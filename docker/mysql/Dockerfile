FROM mysql:8.4

# Importação automática na 1ª subida do volume (ordem alfabética)
COPY database/sql/sigru_create.sql /docker-entrypoint-initdb.d/01_sigru_create.sql
COPY database/sql/sigru_seed.sql /docker-entrypoint-initdb.d/02_sigru_seed.sql
COPY database/sql/sigru_avancado.sql /docker-entrypoint-initdb.d/03_sigru_avancado.sql
