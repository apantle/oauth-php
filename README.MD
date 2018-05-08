## OAuth Consumer And Server Library For PHP
A PHP library for OAuth 1.0a consumers and servers. Complete with an extensible OAuth store, including a full working implementation of MySQL/MySQLi, Postgresql and PDO stores.

Forked and updated from [oauth-php](https://code.google.com/archive/p/oauth-php/) by [Marc Worrell](mailto:marcw@pobox.com)  

The library implements methods to:

 - verify incoming requests against the library
 - sign outgoing requests, with curl support for actually doing the request
 - sign requests with a body
 - administrate consumer keys and tokens for multiple users (server and consumer side)
 - log incoming and outgoing requests handled by the library (optionally in the database)

### Guides, examples and help

 - [FAQ](documentation/FAQ.MD)
 - [OAuth Consumer (Client)](documentation/CONSUMER.MD)
 - [OAuth Server (Service Provider)](documentation/SERVER.MD) 

