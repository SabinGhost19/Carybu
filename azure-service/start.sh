#!/bin/bash

# Verifică dacă variabilele de mediu necesare sunt setate
if [ -z "$AZURE_TEXT_ANALYTICS_API_KEY" ]; then
  echo "AVERTISMENT: Variabila de mediu AZURE_TEXT_ANALYTICS_API_KEY nu este setată!"
fi

if [ -z "$AZURE_TEXT_ANALYTICS_ENDPOINT" ]; then
  echo "AVERTISMENT: Variabila de mediu AZURE_TEXT_ANALYTICS_ENDPOINT nu este setată!"
fi

if [ -z "$AZURE_STORAGE_CONNECTION_STRING" ]; then
  echo "AVERTISMENT: Variabila de mediu AZURE_STORAGE_CONNECTION_STRING nu este setată!"
fi

# Afișează variabilele de mediu (fără chei sensibile) pentru debugging
echo "Utilizez următoarele configurații:"
echo "PORT: $PORT"
echo "NODE_ENV: $NODE_ENV"
echo "AZURE_TEXT_ANALYTICS_ENDPOINT: $AZURE_TEXT_ANALYTICS_ENDPOINT"
echo "AZURE_STORAGE_CONTAINER_NAME: $AZURE_STORAGE_CONTAINER_NAME"
echo "AZURE_SQL_SERVER: $AZURE_SQL_SERVER"
echo "AZURE_SQL_DATABASE: $AZURE_SQL_DATABASE"

# Pornește aplicația Node.js în fundal
cd /var/www/app
npm start &

# Modificare pentru a rezolva problema cu ulimit
# Pornește Apache fără a încerca să seteze ulimit
/usr/sbin/apache2ctl -DNO_DETACH -k start