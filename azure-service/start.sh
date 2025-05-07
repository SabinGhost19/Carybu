#!/bin/bash

export NODE_HOST=${NODE_HOST:-127.0.0.1}
export NODE_PORT=${NODE_PORT:-3000}
export PORT=${PORT:-3000}
export NODE_ENV=${NODE_ENV:-production}

if [ -z "$AZURE_TEXT_ANALYTICS_API_KEY" ]; then
  echo "EROARE: Variabila de mediu AZURE_TEXT_ANALYTICS_API_KEY nu este setată!"
  exit 1
fi

if [ -z "$AZURE_TEXT_ANALYTICS_ENDPOINT" ]; then
  echo "EROARE: Variabila de mediu AZURE_TEXT_ANALYTICS_ENDPOINT nu este setată!"
  exit 1
fi

if [ -z "$AZURE_STORAGE_CONNECTION_STRING" ]; then
  echo "EROARE: Variabila de mediu AZURE_STORAGE_CONNECTION_STRING nu este setată!"
  exit 1
fi

echo "Pornire serviciu cu următoarele configurări:"
echo "NODE_ENV: $NODE_ENV"
echo "PORT: $PORT"
echo "NODE_HOST: $NODE_HOST"
echo "NODE_PORT: $NODE_PORT"
echo "AZURE_TEXT_ANALYTICS_ENDPOINT: $AZURE_TEXT_ANALYTICS_ENDPOINT"
echo "AZURE_STORAGE_CONTAINER_NAME: $AZURE_STORAGE_CONTAINER_NAME"
echo "AZURE_SQL_SERVER: $AZURE_SQL_SERVER"
echo "AZURE_SQL_DATABASE: $AZURE_SQL_DATABASE"

envsubst '$NODE_HOST $NODE_PORT' < /etc/apache2/sites-available/node-app.conf.template > /etc/apache2/sites-available/node-app.conf

a2ensite node-app.conf

cd /var/www/app
npm start &

/usr/sbin/apache2ctl -DNO_DETACH -k start