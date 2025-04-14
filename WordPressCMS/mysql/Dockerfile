FROM mysql:8.0

# Copierea dump-ului SQL pentru baza de date inițială
COPY dump.sql /docker-entrypoint-initdb.d/

# Opțional: Copierea configurărilor personalizate pentru MySQL
# COPY my-custom.cnf /etc/mysql/conf.d/

# Opțional: Alte comenzi de configurare specifice pentru MySQL 8.0 dacă sunt necesare
# RUN chmod 644 /etc/mysql/conf.d/my-custom.cnf

# Setarea parametrilor MySQL 8.0 specifici dacă este necesar
# De exemplu, pentru a configura autentificarea pentru compatibilitate:
# RUN echo "[mysqld]" >> /etc/mysql/conf.d/mysql.cnf
# RUN echo "default_authentication_plugin=mysql_native_password" >> /etc/mysql/conf.d/mysql.cnf