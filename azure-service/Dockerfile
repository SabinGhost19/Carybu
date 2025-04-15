# Bazat pe imaginea Ubuntu
FROM ubuntu:22.04

# Evitarea întrebărilor interactive în timpul instalării
ENV DEBIAN_FRONTEND=noninteractive

# Setare variabile de mediu pentru Azure
ENV PORT=3000
ENV NODE_ENV=development

# Azure Text Analytics
ENV AZURE_TEXT_ANALYTICS_ENDPOINT="https://sentimental-analy.cognitiveservices.azure.com/"
ENV AZURE_TEXT_ANALYTICS_API_KEY="2Pi7bUAYZZpTDKpvYjWm9eH22ehxPKx7YUmmzrT0JyNlnK8eDh6jJQQJ99BDAC5RqLJXJ3w3AAAaACOGQagg"

# Azure Blob Storage
ENV AZURE_STORAGE_CONNECTION_STRING="DefaultEndpointsProtocol=https;AccountName=caribytestblob19;AccountKey=8sn6aZ4g7cEDg9qbHgjVHHn9B+llVBSjJfsjQVwrLmPrbusJh2+5SA8zoSX9nQKhqkafq4ZqJH5W+AStrm7lGA==;EndpointSuffix=core.windows.net"
ENV AZURE_STORAGE_CONTAINER_NAME="caribu-container"

# Azure SQL Database
ENV AZURE_SQL_SERVER="caribu-test-sql-server.database.windows.net"
ENV AZURE_SQL_DATABASE="SentimentAnalysisDB" 
ENV AZURE_SQL_USER="sabinghost19"
ENV AZURE_SQL_PASSWORD="123sabinstan$"

# Actualizare și instalare Apache, Node.js și npm
RUN apt-get update && \
    apt-get install -y apache2 curl gnupg && \
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

# Configurare Apache pentru proxy invers către aplicația Node.js
COPY ./apache-conf/node-app.conf /etc/apache2/sites-available/
RUN a2ensite node-app.conf && a2dissite 000-default.conf

# Expune porturile pentru Apache (80) și Node.js (3000)
EXPOSE 80 3000

# Creare script de pornire modificat pentru a gestiona erorile
COPY ./start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh

# Comanda de pornire
CMD ["/usr/local/bin/start.sh"]