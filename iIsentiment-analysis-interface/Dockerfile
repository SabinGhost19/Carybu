FROM node:18 as build
WORKDIR /app
COPY package*.json ./
RUN npm install --legacy-peer-deps
COPY . .
RUN npm run build
# Debug: show what's in the build directory
RUN ls -la /app/dist

FROM nginx:alpine
RUN apk add --no-cache bash gettext

# Remove ALL default content from nginx html directory
RUN rm -rf /usr/share/nginx/html/*

# Remove default configs
RUN rm -rf /etc/nginx/conf.d/*

# Copy application from the correct Angular output directory (browser folder)
COPY --from=build /app/dist/sentiment-analysis-app/ /usr/share/nginx/html/

# Debug: show what's been copied
RUN ls -la /usr/share/nginx/html

# Copy nginx config
COPY nginx.conf /etc/nginx/conf.d/default.conf.template

# Run nginx
CMD ["sh", "-c", "envsubst '$${BACKEND_URL} $${BACKEND_PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf && nginx -g 'daemon off;'"]