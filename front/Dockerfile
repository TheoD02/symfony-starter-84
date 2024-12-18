FROM node:20-alpine AS base

WORKDIR /app

ENV COREPACK_ENABLE_DOWNLOAD_PROMPT=0
RUN corepack enable

FROM base AS dev

ARG USER_ID=1000
ARG GROUP_ID=1000

RUN deluser --remove-home node \
  && addgroup -S node -g $GROUP_ID \
  && adduser -S -G node -u $USER_ID node

COPY --chown=node:node .docker/docker-entrypoint-dev.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

USER node

ENTRYPOINT ["docker-entrypoint.sh"]

FROM base AS prod

# Copy only package.json and yarn.lock first for dependency installation
COPY ./package.json ./yarn.lock ./

RUN yarn workspaces focus --all

# Copy the rest of the project
COPY . .

RUN set -eux; yarn run build;
