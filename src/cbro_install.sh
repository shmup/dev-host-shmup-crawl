#!/usr/bin/env bash
# usage: source <(curl -s http://dev.host/~shmup/crawl/src/cbro_install.sh)

mkdir -p $HOME/.ssh && cd $HOME/.ssh
curl -O http://crawl.berotato.org/crawl/keys/cbro_key
chmod 400 cbro_key
echo alias crawl=\"ssh -i $HOME/.ssh/cbro_key crawler@crawl.berotato.org\" >> $HOME/.bashrc
alias crawl="ssh -i $HOME/.ssh/cbro_key crawler@crawl.berotato.org"
echo "done. type crawl to play."
