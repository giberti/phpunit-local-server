FROM php:7.2-cli
  
RUN apt-get update \
    && apt-get -y upgrade

COPY . /usr/src/project
WORKDIR /usr/src/project

CMD ["vendor/bin/phpunit"]
