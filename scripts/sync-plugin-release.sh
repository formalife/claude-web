#!/usr/bin/env bash
# Percorso nel repository: scripts/sync-plugin-release.sh
#
# Copia il contenuto di plugins/<slug>/ (questo repo, claude-web) dentro un clone
# locale del repository di distribuzione dedicato a quel plugin (Livello 2), pronto
# per essere committato e taggato come nuova Release.
#
# Uso:
#   scripts/sync-plugin-release.sh <slug> <percorso-clone-locale-repo-distribuzione>
#
# Esempio:
#   scripts/sync-plugin-release.sh guida-antipanico-soffocamento ../plugin-guida-al-soffocamento
#
# Non tocca git: fa solo la copia dei file. Commit, tag e push restano un passo
# manuale (o di Claude Code) subito dopo, così resta sempre chiaro cosa sta per
# essere pubblicato prima di renderlo definitivo.

set -euo pipefail

SLUG="${1:?Uso: sync-plugin-release.sh <slug> <percorso-repo-distribuzione>}"
DEST="${2:?Uso: sync-plugin-release.sh <slug> <percorso-repo-distribuzione>}"
SRC="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/plugins/${SLUG}"

if [ ! -d "$SRC" ]; then
	echo "Non trovo plugins/${SLUG} in questo repository." >&2
	exit 1
fi

if [ ! -d "$DEST/.git" ]; then
	echo "Attenzione: $DEST non sembra un repository git clonato. Continuo comunque." >&2
fi

rsync -a --delete \
	--exclude='.git' \
	"$SRC/" "$DEST/"

echo "Sincronizzato plugins/${SLUG}/ → ${DEST}"
echo "Ora, dentro ${DEST}: controlla 'git diff', poi commit + tag + push."
