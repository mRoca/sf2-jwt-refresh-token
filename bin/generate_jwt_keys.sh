#!/bin/bash
set -e

KEYS_DIR=var/jwt
PRIVATE_KEY=$KEYS_DIR/private.pem
PUBLIC_KEY=$KEYS_DIR/public.pem
PASSWORD=keypasswd

if [ ! -f $PRIVATE_KEY ]; then
    mkdir -p $KEYS_DIR

    # Create the private certificate with a default password
    openssl genrsa -passout pass:$PASSWORD -out $PRIVATE_KEY -aes256 1024 -config openssl.cnf
    # Remove passphrase from the key. Comment the line out to keep the passphrase
    openssl rsa -in $PRIVATE_KEY -passin pass:$PASSWORD -out $PRIVATE_KEY
    # Create the public certificate
    openssl rsa -pubout -in $PRIVATE_KEY -out $PUBLIC_KEY
    chmod 440 $PRIVATE_KEY $PUBLIC_KEY
fi
