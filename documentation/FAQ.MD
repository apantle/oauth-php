## Frequently added questions

#### I'm getting problems with SSL.
If you are getting *"Exception CURL error: SSL certificate problem, verify that the CA cert is OK"* or similar, that indicates problem with the remote SSL certificate.

To fix this, you should just pass `array(CURLOPT_SSL_VERIFYPEER => false)` as the $curl_options parameter in OAuthRequester::requestRequestToken.

#### I don't have CURL, what do I do?
You need CURL installed to use this library, sorry. There's no workaround.