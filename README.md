# WebDev_Prescription
Group space for prescription web app of group 6


vhost config file
//this assumes the project is saved in the wamp project folder "www"

<VirtualHost *:80>
  ServerName webdev-prescription.bytebusters
  DocumentRoot "${INSTALL_DIR}/www/webdev_prescription"
  <Directory "${INSTALL_DIR}/www/webdev_prescription">
    Options +Indexes +Includes +FollowSymLinks +MultiViews
    AllowOverride All
    Require all granted
  </Directory>
</VirtualHost>

in "C:\Windows\System32\drivers\etc\hosts" add this line to both server and clients
[ip address of host * not literally this should be changed] webdev-prescription.bytebusters

notes:
make sure firewall is off

DOCKER INSTRUCTIONS
inside the root folder run:

docker compose up --build
sudo docker compose up --build *if running on ubuntu server