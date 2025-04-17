# Bazat pe imaginea Ubuntu
FROM ubuntu:22.04

# Evitarea întrebărilor interactive în timpul instalării
ENV DEBIAN_FRONTEND=noninteractive

# Setare valori implicite pentru variabilele de configurare
ENV NODE_HOST=127.0.0.1
ENV NODE_PORT=3000
ENV PORT=3000
ENV NODE_ENV=production

# Actualizare și instalare Apache, Node.js, envsubst și alte utilități
RUN apt-get update && \
    apt-get install -y apache2 curl gnupg gettext && \
    # Adăugare repository Node.js
    mkdir -p /etc/apt/keyrings && \
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list && \
    apt-get update && \
    apt-get install -y nodejs && \
    # Verificarea instalării
    node --version && \
    npm --version && \
    # Instalare unelte necesare
    apt-get install -y git nano vim && \
    # Activare module Apache necesare
    a2enmod proxy proxy_http headers rewrite && \
    # Curățare cache apt
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

# Rezolvare problemă ServerName
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Creare director pentru aplicația Node.js
WORKDIR /var/www/app

# Copiere fișiere aplicație în container
COPY ./app /var/www/app/

# Instalare dependințe Node.js
RUN npm install

# Copiere configurație Apache template
COPY ./apache-conf/node-app.conf.template /etc/apache2/sites-available/

# Dezactivare site-ul implicit (acest pas poate fi făcut acum)
RUN a2dissite 000-default.conf

# Expune porturile pentru Apache (80) și Node.js (3000)
EXPOSE 80 3000

# Creare script de pornire modificat pentru a gestiona erorile
COPY ./start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh

# Comanda de pornire
CMD ["/usr/local/bin/start.sh"]