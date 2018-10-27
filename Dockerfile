FROM alpine:3.7

# Install the dependencies.
RUN apk add --no-cache \
    php7 \
    php7-curl \
    php7-dom \
    php7-tokenizer \
    php7-xdebug

# Enable xdebug for code coverage.
RUN echo zend_extension=xdebug.so >> /etc/php7/conf.d/xdebug.ini

# Create and set the working directory to /package.
RUN mkdir /package
WORKDIR /package