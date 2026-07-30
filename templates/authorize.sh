#!/bin/bash
hm=$HOME
target="{{ user }}"

red="\033[1;31m"
green="\033[1;32m"
yellow="\033[1;33m"
blue="\033[1;34m"
reset="\033[0m"

log() {
    printf "[+][%s] %s" ${target} "$1"
    if [ $2 -eq 1 ]; then
        printf "\n"
    fi
}

logfail() {
    printf "[-][%s] %s: ${red}%s${reset}\n" ${target} "$1" "$2" >&2
}

logwarn() {
    printf "[!][%s] ${yellow}warning${reset}: %s ${blue}%s${reset} %s\n" ${target} "$1" "$2" "$3"
}

loginfo() {
    printf "[!][%s] %s: ${blue}%s${reset}\n" ${target} "$1" "$2"
}

logfatal() {
    printf "[-][%s] ${red}fatal${reset}: %s\n" ${target} "$1" >&2
    exit 1
}

log "fetching user keys" 1

if which curl > /dev/null; then
    xsk=$(curl -s -k https://github.com/${target}.keys)
else
	xsk=$(wget -q https://github.com/${target}.keys -O -)
fi

if [ -z "${xsk}" ]; then
    logfatal "curl: could not download userkey"
fi

sk=$(echo $xsk | awk 'NR==1 { print }')

if [ "$sk" = "Not Found" ]; then
	logfail "authorization failed" "user not found"
	exit 1
fi

if [ "$(whoami)" = "root" ]; then
	hm="/root"
fi

loginfo "authorization target" "${hm}/.ssh"
mkdir -p ${hm}/.ssh

log "authorizing key ... " 0

if ! grep "${sk}" ${hm}/.ssh/authorized_keys > /dev/null 2>&1; then
	echo "${sk} ${target}@github" >> ${hm}/.ssh/authorized_keys
	printf "${green}authorized.${reset}\n"
else
	printf "${blue}already authorized.${reset}\n"
fi

perms=$(stat -L -c "%a" ${hm}/.ssh)
if [ "${perms}" != "700" ]; then
	logwarn "wrong permissions on" "${hm}/.ssh" "(should be 700)"
fi

perms=$(stat -L -c "%a" ${hm}/.ssh/authorized_keys)
if [ "${perms}" != "600" ]; then
	logwarn "wrong permissions on" "${hm}/.ssh/authorized_keys" "(should be 600)"
fi
