# syntax=docker/dockerfile:1

# ========== STAGE 1: BUILD (Node.js) ==========
FROM node:22.23.4-alpine AS build

WORKDIR /app

# Manifest disalin lebih dulu agar cache npm tetap dipakai selama
# package.json dan package-lock.json tidak berubah.
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .

# Alamat API diatur saat build lewat build arg / variabel lingkungan.
# Default "/api" berarti permintaan diteruskan oleh Nginx ke service backend.
ARG VITE_API_URL=/api
ENV VITE_API_URL=$VITE_API_URL

RUN npm run build

# ========== STAGE 2: RUNTIME (Nginx) ==========
FROM nginx:1.30.6-alpine3.24 AS runtime

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD wget -q -O /dev/null http://127.0.0.1/ || exit 1
