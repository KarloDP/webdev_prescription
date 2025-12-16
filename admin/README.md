CREATING THE IMAGE:
docker compose up -d

*use sudo if running in ubuntu

add the following line to hosts file on client:
Windows: C:\Windows\System32\drivers\etc\hosts
Linux: /etc/hosts

<hostip> webdev-prescription.bytebusters

admin end (when docker container is running) is accessible from:
ipaddress:8080