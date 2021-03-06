## OAuth Consumer
This gives an overview how you can use the oauth-php library when you want to connect to a server.

If you only need 2-legged OAuth (which is probably the most common case), see the section ConsumerHowTo#Two-legged_OAuth. There's a complete example there on how to use it.

Here are some real-life examples:

For 3-legged OAuth, the steps you need to do to get access to a server are:

Get an instance of the OAuth store
Add the server consumer_key and secret to the OAuth library
Obtain an access token to the server
Authorize the access token (exchanging it for a request token)
And now you can make signed requests to the server.

### Two-legged OAuth
There's a separate store in the code for 2-legged OAuth, called OAuthStore2Leg. It does not depend on a database, and thus it's much easier to use. Here's a sample example:

    include_once "oauth-php/library/OAuthStore.php";
    include_once "oauth-php/library/OAuthRequester.php";
    
    // this is your consumer key
    $key = '???????';
    
    // this is your secret key
    $secret = '????????';
    
    $options = array(
      'consumer_key' => $key,
      'consumer_secret' => $secret
    );
    
    OAuthStore::instance("2Leg", $options );
    
    // this is the URL of the request 
    $url = "?????????"; 
    
    // you can also use POST instead
    $method = "GET";
    
    $params = null;
    
    try { 
      // Obtain a request object for the request we want to make
      $request = new OAuthRequester($url, $method, $params);
    
      // Sign the request, perform a curl request and return the results, 
      // throws OAuthException2 exception on an error
      // $result is an array of the form: array ('code'=>int, 'headers'=>array(), 'body'=>string)
      $result = $request->doRequest();
    
      $response = $result['body'];
    } catch(OAuthException2 $e)
    {
      // log the error
    }

There are some real life examples in the package, including a Twitter example.

### Three-legged OAuth
#### Step 1: Instantiate an OAuth store
Before doing any calls with the OAuth library, you need to have a store instance. oauth-php uses a singleton instance of the store for each request, so you'll have to instantiate it once yourself for the OAuth store to know which store singleton to use.

Per default oauth-php will instantiate an OAuthStoreMySQL store with default arguments for username, password and host.

For use with MySQL, you can instantiate the store like this:

    $options = array('server' => 'localhost', 'username' => 'john', 'password' => 'secret', 'database' => 'johns_db');
    $store = OAuthStore::instance('MySQL', $options);

#### Step 2: Add Server To OAuth
##### Add the OAuth server to which you want to connect
Now, add the server to the store, this will return the consumer_key from the store, the user_id is the id of the currently logged on user.

You need to obtain the consumer key and consumer secret from the server. Every web site has a place where it is explained how you can obtain a key and secret for your application.

    // Get the id of the current user (must be an int)
    $user_id = 1;
    
    // The server description
    $server = array( 
      'consumer_key' => 'some-server-supplied-key',
      'consumer_secret' => 'some-server-supplied-secret',
      'server_uri' => 'http://www.example.com/api/',
      'signature_methods' => array('HMAC-SHA1', 'PLAINTEXT'),
      'request_token_uri' => 'http://www.example.com/request_token',
      'authorize_uri' => 'http://www.example.com/authorize',
      'access_token_uri' => 'http://www.example.com/access_token'
    );
    
    // Save the server in the the OAuthStore
     $consumer_key = $store->updateServer($server, $user_id);

##### List all OAuth servers
You can easily obtain a list of all registered OAuth servers. This will return an array with the full description of all servers registered for a certain user.

    $servers = $store->listServers($optional_filter_text, $user_id);

##### Delete an OAuth server from the registry
You can simply delete an OAuth server from the registry. You need to supply the user id for which you want to delete the server.

    $store->deleteServer($consumer_key, $user_id);

#### Step 3: Obtain Access To A Server
Before you can make OAuth signed calls to an OAuth enabled server, you have to obtain authorization from an user on that server. This can be done with the OAuthRequester class.

First we need to obtain a request token from the OAuth server. The OAuthRequester will request an instance of the OAuthStore for finding the needed uris. When you don't use the default MySQL store you will need to request an instance of the store (supplying the correct initialisation parameters) before calling the method below.

    // Fetch the id of the current user
    $user_id = 1;
     
    // Obtain a request token from the server
    $token = OAuthRequester::requestRequestToken($consumer_key, $user_id);
    
    // Callback to our (consumer) site, will be called when the user finished the authorization at the server
    $callback_uri = 'http://www.mysite.com/callback?consumer_key='.rawurlencode($consumer_key).'&usr_id='.intval($user_id);
    
    // Now redirect to the autorization uri and get us authorized
    if (!empty($token['authorize_uri']))
    {
      // Redirect to the server, add a callback to our server
      if (strpos($token['authorize_uri'], '?'))
      {
        $uri = $token['authorize_uri'] . '&';
      } else {
        $uri = $token['authorize_uri'] . '?';
      } 
      
      $uri .= 'oauth_token='.rawurlencode($token['token']).'&oauth_callback='.rawurlencode($callback_uri);
    } else {
      // No authorization uri, assume we are authorized, exchange request token for access token
      $uri = $callback_uri . '&oauth_token='.rawurlencode($token['token']);
    }
    
    header('Location: '.$uri);
    exit();

#### Step 4: Exchange Request Token For Access Token
You also have to implement a callback http handler. This is called by the OAuth server whenever the user authorized our request token. When we are authorized, then we can exchange our request token for an access token.

As an example I add an exception handler, all OAuth methods can throw OAuthException exceptions.

    // Request parameters are oauth_token, consumer_key and usr_id. 
    $consumer_key = $_GET['consumer_key'];
    $oauth_token = $_GET['oauth_token'];
    $user_id = $_GET['usr_id'];

    try { 
      OAuthRequester::requestAccessToken($consumer_key, $oauth_token, $user_id);
    } catch (OAuthException $e) {
      // Something wrong with the oauth_token. 
      // Could be: 
      // 1. Was already ok 
      // 2. We were not authorized
    }

#### Step 5: Make A Signed Request
When you make a request to an OAuth enabled server you need to sign it with the access token and secrets we obtained when we obtained access to the server.

The OAuthStore and the OAuth objects work closely together. The OAuth requester will find the correct credentials by examining the uri of the request being made. This makes signing requests very easy.

    // The request uri being called. 
    $request_uri = 'http://www.example.com/api';
    
    // Parameters, appended to the request depending on the request method. 
    // Will become the POST body or the GET query string.
    $params = array( 'method' => 'ping' );
    
    // Obtain a request object for the request we want to make
    $req = new OAuthRequester($request_uri, 'GET', $params);
    
    // Sign the request, perform a curl request and return the results, throws OAuthException exception on an error
    // $result is an array of the form:
    // array ('code'=>int, 'headers'=>array(), 'body'=>string)
    
    $result = $req->doRequest($user_id);
