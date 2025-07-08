FROM mysql:8.0

COPY docker-healthcheck /usr/local/bin/

RUN chmod +x /usr/local/bin/docker-healthcheck

HEALTHCHECK CMD ["docker-healthcheck"]