#!/bin/sh

# trust the repo
# fixes:
# - fatal: detected dubious ownership in repository at '/workspaces/bot-zero'.
git config --global --add safe.directory "$PWD"

# config local GPG for signing
# fixes:
# - error: cannot run C:\Program Files (x86)\Gpg4win..\GnuPG\bin\gpg.exe: No such file or directory.
git config --global gpg.program gpg 

