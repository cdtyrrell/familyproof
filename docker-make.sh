cd Documents/Sandbox/Genealogy/db

docker run -i -t -p "83:80" -v ${PWD}/app:/app -v ${PWD}/mysql:/var/lib/mysql mattrayner/lamp:latest-2004-php7
#docker run -i -t -p "81:80" -v ${PWD}/app:/app -v ${PWD}/mysql:/var/lib/mysql mattrayner/lamp:latest-1804-php7