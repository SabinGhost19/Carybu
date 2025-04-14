FROM wordpress:latest

# Copierea plugin-urilor, temelor și configurațiilor personalizate
COPY wp-content/ /var/www/html/wp-content/

# Setarea permisiunilor corecte
RUN chown -R www-data:www-data /var/www/html/wp-content

# Instrucțiuni de configurare suplimentară dacă este necesar
# Puteți adăuga aici comenzi pentru a instala extensii PHP sau alte configurări

# Exemplu pentru a instala extensii PHP suplimentare:
# RUN docker-php-ext-install opcache

# Puteți copia și fișiere de configurare specifice dacă aveți nevoie
# COPY wp-config-custom.php /var/www/html/wp-config-custom.php