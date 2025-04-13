FROM node:18 as build

WORKDIR /app

COPY package*.json ./

RUN npm install

COPY . .

RUN npm run build

FROM nginx:alpine

RUN rm -rf /etc/nginx/conf.d/*

COPY --from=build /app/dist/sentiment-analysis-app /usr/share/nginx/html

COPY nginx.conf /etc/nginx/conf.d/

CMD ["sh", "-c", "sed -i 's|${BACKEND_URL}|'"$BACKEND_URL"'|g; s|${BACKEND_PORT}|'"$BACKEND_PORT"'|g /etc/nginx/conf.d/nginx.conf && nginx -g 'daemon off;'"]