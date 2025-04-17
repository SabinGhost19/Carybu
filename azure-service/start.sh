#!/bin/bash

# Setare valori implicite pentru variabilele de mediu
export NODE_HOST=${NODE_HOST:-127.0.0.1}
export NODE_PORT=${NODE_PORT:-3000}
export PORT=${PORT:-3000}
export NODE_ENV=${NODE_ENV:-production}

# Verificare variabile de mediu critica pentru funcționarea aplicației
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

# Afișează informații despre mediul de execuție (fără informații sensibile)
echo "Pornire serviciu cu următoarele configurări:"
echo "NODE_ENV: $NODE_ENV"
echo "PORT: $PORT"
echo "NODE_HOST: $NODE_HOST"
echo "NODE_PORT: $NODE_PORT"
echo "AZURE_TEXT_ANALYTICS_ENDPOINT: $AZURE_TEXT_ANALYTICS_ENDPOINT"
echo "AZURE_STORAGE_CONTAINER_NAME: $AZURE_STORAGE_CONTAINER_NAME"
echo "AZURE_SQL_SERVER: $AZURE_SQL_SERVER"
echo "AZURE_SQL_DATABASE: $AZURE_SQL_DATABASE"

# Înlocuiește variabilele în fișierul de configurare Apache
envsubst '$NODE_HOST $NODE_PORT' < /etc/apache2/sites-available/node-app.conf.template > /etc/apache2/sites-available/node-app.conf

# Activează site-ul Apache după ce a fost generat din template
a2ensite node-app.conf

# Pornește aplicația Node.js în fundal
cd /var/www/app
npm start &

# Pornește Apache fără a încerca să seteze ulimit
/usr/sbin/apache2ctl -DNO_DETACH -k start