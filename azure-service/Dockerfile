# Bazat pe imaginea Ubuntu
FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

ENV NODE_HOST=127.0.0.1
ENV NODE_PORT=3000
ENV PORT=3000
ENV NODE_ENV=production

RUN apt-get update && \
    apt-get install -y apache2 curl gnupg gettext && \
    
    mkdir -p /etc/apt/keyrings && \
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_20.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list && \
    apt-get update && \
    apt-get install -y nodejs && \

    node --version && \
    npm --version && \
    apt-get install -y git nano vim && \
    a2enmod proxy proxy_http headers rewrite && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/app

COPY ./app /var/www/app/

RUN npm install

COPY ./apache-conf/node-app.conf.template /etc/apache2/sites-available/

RUN a2dissite 000-default.conf

EXPOSE 80 3000

COPY ./start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]