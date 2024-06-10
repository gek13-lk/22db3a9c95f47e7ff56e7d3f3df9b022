# python official image
FROM python:3.12.0-alpine3.18

RUN apk add --no-cache build-base gfortran openblas-dev linux-headers

RUN pip install appdirs

RUN pip install --no-cache-dir --upgrade pip setuptools

# install dependencies
RUN \
  apk update && \
  apk add postgresql-dev gcc python3-dev musl-dev zlib-dev jpeg-dev

# set work directory
WORKDIR /usr/src/app

# set environment variables
ENV PYTHONDONTWRITEBYTECODE 1
ENV PYTHONUNBUFFERED 1

# install dependencies
COPY ./requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# copy project
COPY . .