# Dockerfile
FROM node:24-slim

# Create app directory
WORKDIR /app

# Copy package files first to take advantage of layer caching
COPY package*.json ./

# Install dependencies deterministically
# Use --only=production in prod images if you don't need devDependencies
RUN npm ci --no-audit --no-fund

# Copy app source (after installing deps)
COPY . .

# Use non-root user for better security
# Node image includes 'node' user, ensure ownership
RUN chown -R node:node /app
USER node

# Environment defaults (override at runtime)
ENV NODE_ENV=production
ENV PORT=8080
# DB envs should be passed via docker run / compose, example: DB_HOST, DB_USER, DB_PASS, DB_NAME

EXPOSE 8080

# Optional: enable source maps for better stack traces in production if you generate them
CMD ["node", "--enable-source-maps", "index.js"]
