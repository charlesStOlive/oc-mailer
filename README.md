mailer


POur regler le probleme de certificat. 
download and extract cacert.pem following the instructions at https://curl.se/docs/caextract.htm
save it on your filesystem somewhere (for example, XAMPP users might use)
POur laragon le mettre dans C:\laragon\etc\ssl
Si mis dans ce dossier inutile de modifier php.ini sinon 
[curl]
curl.cainfo = "C:\xampp\php\extras\ssl\cacert.pem"
