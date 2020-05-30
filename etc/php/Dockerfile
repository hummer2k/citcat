FROM chialab/php:7.2-fpm

# Install additional php extensions and tools
RUN apt-get update && apt-get install --no-install-recommends -y gnupg2 libxslt-dev vim  iproute2 && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install xsl

# Install mailhog
RUN curl -L https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64 --output /usr/local/bin/mhsendmail \
    && chmod +x /usr/local/bin/mhsendmail

# Install PHP tools
RUN curl -L https://files.magerun.net/n98-magerun2.phar -o /usr/local/bin/n98-magerun2.phar \
    && chmod +x /usr/local/bin/n98-magerun2.phar

# User creation
RUN useradd -U -m -r -o -u 1003 phpdev

# Install fixuid
RUN USER=phpdev && \
    GROUP=phpdev && \
    curl -SsL https://github.com/boxboat/fixuid/releases/download/v0.4/fixuid-0.4-linux-amd64.tar.gz | tar -C /usr/local/bin -xzf - && \
    chown root:root /usr/local/bin/fixuid && \
    chmod 4755 /usr/local/bin/fixuid && \
    mkdir -p /etc/fixuid && \
    printf "user: $USER\ngroup: $GROUP\n" > /etc/fixuid/config.yml

ENTRYPOINT ["fixuid", "-q"]

CMD ["php-fpm"]
USER phpdev:phpdev
