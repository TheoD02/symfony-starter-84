FROM node:20-alpine AS base

WORKDIR /app

RUN corepack enable

FROM base AS dev

# Expose the development server port
EXPOSE 5173

COPY .docker/entrypoint-dev.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

ENTRYPOINT ["entrypoint"]

FROM base AS prod

# Copy only package.json and yarn.lock first for dependency installation
COPY ./package.json ./yarn.lock ./

RUN yarn workspaces focus --all

# Copy the rest of the project
COPY . .

RUN set -eux; yarn run build;
