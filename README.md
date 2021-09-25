mailer


en local pour enlever l'erreur SSL ajouter en ligne 78 dans le vendor \juanmiguelbesada\mjml-php\src\Client.php 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
