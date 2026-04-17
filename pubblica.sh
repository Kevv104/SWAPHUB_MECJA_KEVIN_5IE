#!/bin/bash

# Controllo argomenti
if [ "$#" -ne 2 ]; then
  echo "Uso: $0 <nome_link> <cartella_riferimento>"
  exit 1
fi

LINK_NAME="$1"
TARGET_DIR="$2"
WWW_ROOT="/var/www/html"

# Risolve la cartella sorgente da pubblicare
if [ -d "$TARGET_DIR" ]; then
  SOURCE_DIR="$(realpath "$TARGET_DIR")"
elif [ -d "/workspaces/$TARGET_DIR" ]; then
  SOURCE_DIR="/workspaces/$TARGET_DIR"
else
  echo "Errore: cartella '$TARGET_DIR' non trovata"
  exit 1
fi

# Crea o aggiorna il link simbolico in /var/www/html
sudo ln -sfn "$SOURCE_DIR" "$WWW_ROOT/$LINK_NAME"
echo "Link creato/aggiornato: $WWW_ROOT/$LINK_NAME -> $SOURCE_DIR"
