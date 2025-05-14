FROM node:18 as build
WORKDIR /app
COPY package*.json ./
RUN npm install --legacy-peer-deps
COPY . .
RUN npm run build
RUN ls -la /app/dist

FROM nginx:alpine
RUN apk add --no-cache bash gettext

RUN rm -rf /usr/share/nginx/html/*

RUN rm -rf /etc/nginx/conf.d/*

COPY --from=build /app/dist/sentiment-analysis-app/ /usr/share/nginx/html/
#debug
RUN ls -la /usr/share/nginx/html

COPY nginx.conf /etc/nginx/conf.d/default.conf.template

# run nginx
CMD ["sh", "-c", "envsubst '$${BACKEND_URL} $${BACKEND_PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf && nginx -g 'daemon off;'"]