mailer


en local pour enlever l'erreur SSL 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); dans le vendor MJML 