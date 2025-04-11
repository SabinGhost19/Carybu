# Stage 1: Build the Angular application
FROM node:18-alpine as build

# Set working directory
WORKDIR /app

# Copy package files
COPY package.json package-lock.json ./

# Install dependencies
RUN npm ci

# Copy source code
COPY . .

# Build the application
RUN npm run build -- --configuration production

# Verifică structura directorului dist
RUN ls -la dist/chat_interface/

# Stage 2: Serve the application using Nginx
FROM nginx:alpine

# Remove default Nginx files
RUN rm -rf /etc/nginx/conf.d/* && rm -rf /usr/share/nginx/html/*

# Copy custom nginx configuration
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Copy the built application
COPY --from=build /app/dist/chat_interface/ /usr/share/nginx/html/

# Verifică structura în containerul final
RUN ls -la /usr/share/nginx/html/

# Expose port 80
EXPOSE 80

# Start Nginx
CMD ["nginx", "-g", "daemon off;"]