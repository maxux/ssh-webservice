<?php
header('Content-Type: text/plain');

$user = 'maxux';
$expr = 'NR==1';
if($_SERVER['REQUEST_URI'] != '/') {
    $paths = explode("/", $_SERVER['REQUEST_URI']);
    $user = $paths[1];
    if (count($paths) > 2) {
        if (is_numeric($paths[2])) {
            $expr = 'NR==' . $paths[2];
        } else {
            http_response_code(400);
            exit();
        }
    }
}
?>
#!/bin/bash
set -e

HM=$HOME
TARGET="<?php echo $user; ?>"

red="\033[1;31m"
green="\033[1;32m"
yellow="\033[1;33m"
blue="\033[1;34m"
reset="\033[0m"

echo -en "[+] authorizing ${yellow}${TARGET}${reset}: "

if which curl > /dev/null; then
	SK=$(curl -s https://github.com/${TARGET}.keys | awk '<?php echo $expr; ?> {print}')
else
	SK=$(wget -q https://github.com/${TARGET}.keys -O - | awk '<?php echo $expr; ?> {print}')
fi

if [ "$SK" == "Not Found" ]; then
	echo -e "${red}user not found.${reset}"
	exit 1
fi

if [ "$(whoami)" == "root" ]; then
	HM="/root"
fi

mkdir -p $HM/.ssh

if ! grep "$SK" $HM/.ssh/authorized_keys > /dev/null 2>&1; then
	echo "$SK ${TARGET}@github" >> $HM/.ssh/authorized_keys
	echo -e "${green}authorized.${reset}"
else
	echo -e "${blue}already authorized.${reset}"
fi
