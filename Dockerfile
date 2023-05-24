FROM alpine:3.17.3

# Setup the environment
WORKDIR /app

# Required system packages
RUN apk update \ 
    && apk upgrade \
    && apk add --no-cache -U \
    curl bash ca-certificates \
    openssl ncurses coreutils \
    python3 make gcc g++ \
    libgcc linux-headers grep \
    util-linux binutils findutils

# PHP
RUN apk add --no-cache --update php \
    php81-phar php81-mbstring php81-openssl php81-json php81-curl php81-intl \
    php81-xml php81-zip php81-dom php81-gd php81-pdo php81-pdo_mysql php81-pdo_sqlite \
    php81-fileinfo php81-tokenizer php81-session php81-simplexml php81-ctype \
    php81-mysqli \
    && php -v

# Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === '55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8dba01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer \
    && composer --version

# PNPM
RUN apk add --no-cache --update nodejs npm \
    && npm install -g pnpm \
    && pnpm --version

# Copy the application
COPY . /app

# Build
RUN composer install \
    && pnpm install \
    && pnpm run build

# Expose the port
EXPOSE 80

HEALTHCHECK CMD wget -q --no-cache --spider localhost

ENTRYPOINT ["php", "-S", "0.0.0.0:80", "public/index.php"]

# Build the image
# docker build -t lms .

# Run the container
# docker run -d -p 3031:80 --name lms lms

# Rebuild the container
# docker build -t lms . && docker stop lms && docker rm lms && docker run -d -p 3031:80 --name lms lms