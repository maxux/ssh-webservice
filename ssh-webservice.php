<?php
header('Content-Type: text/plain');

$user = 'maxux';
$expr = 'NR==1';

if($_SERVER['REQUEST_URI'] != '/') {
    $paths = explode("/", $_SERVER['REQUEST_URI']);
    $user = $paths[1];

    if(count($paths) > 2) {
        if(!is_numeric($paths[2])) {
            http_response_code(400);
            exit();
        }

        $expr = 'NR==' . $paths[2];
    }
}

?>
#!/bin/bash
set -e

hm=$HOME
target="<?php echo $user; ?>"

red="\033[1;31m"
green="\033[1;32m"
yellow="\033[1;33m"
blue="\033[1;34m"
reset="\033[0m"

echo -e "[+][${target}] fetching user keys"

if which curl > /dev/null; then
	sk=$(curl -s https://github.com/${target}.keys | awk '<?php echo $expr; ?> { print }')
else
	sk=$(wget -q https://github.com/${target}.keys -O - | awk '<?php echo $expr; ?> { print }')
fi

if [ "$sk" == "Not Found" ]; then
	echo -e "[+][${target}] authorization failed: ${red}user not found.${reset}"
	exit 1
fi

if [ "$(whoami)" == "root" ]; then
	hm="/root"
fi

echo -e "[+][${target}] authorization target: ${blue}${hm}/.ssh${reset}"
mkdir -p ${hm}/.ssh

echo -en "[+][${target}] authorizing key ... "

if ! grep "${sk}" ${hm}/.ssh/authorized_keys > /dev/null 2>&1; then
	echo "${sk} ${target}@github" >> ${hm}/.ssh/authorized_keys
	echo -e "${green}authorized.${reset}"
else
	echo -e "${blue}already authorized.${reset}"
fi

perms=$(stat -L -c "%a" ${hm}/.ssh)
if [ "${perms}" != "700" ]; then
	echo -e "[-][${target}] ${yellow}warning${reset}: wrong permissions on ${blue}${hm}/.ssh${reset} (should be 700)"
fi

perms=$(stat -L -c "%a" ${hm}/.ssh/authorized_keys)
if [ "${perms}" != "600" ]; then
	echo -e "[-][${target}] ${yellow}warning${reset}: wrong permissions on ${blue}${hm}/.ssh/authorized_keys${reset} (should be 600)"
fi
