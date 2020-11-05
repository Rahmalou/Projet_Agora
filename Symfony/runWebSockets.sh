#!/usr/bin/env bash

echo "Lancement des sockets dans 3...2...1...GO"

php bin/console sockets:start-chat

echo "Fermeture des web sockets"