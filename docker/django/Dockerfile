# python official image
FROM python:3.10.0-alpine

# install dependencies
RUN \
  apk update && \
  apk add --no-cache postgresql-dev gcc python3-dev musl-dev zlib-dev jpeg-dev build-base gfortran openblas-dev linux-headers

# set work directory
WORKDIR /usr/src/app

# set environment variables
ENV PYTHONDONTWRITEBYTECODE 1
ENV PYTHONUNBUFFERED 1

# install dependencies
RUN pip install --no-cache-dir --upgrade pip
COPY ./requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# copy project
COPY . .