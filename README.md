# Twitter Search PHP Proxy

## About

This app acts as a server proxy for a client-side
(JavaScript) application to make AJAX GET requests and receive results in JSONP
formatted Twitter Search results without the client-side app handling the oAuth
authentication. The oAuth authentication is handled on the server using PHP.
Credentials are stored client side in an encrypted cookie. When a user returns
to the application and the cookie is found, the app can use the cookieÕs stored
credentials to make authenticated requests to the Twitter Search API.

## Requirements

PHP Server

Twitter Account

Twitter Application (consumer keys)

## Credits

This twitter oAuth PHP proxy uses the [twitteroAuth library from
Abraham](https://github.com/abraham/twitteroauth).

## Before starting

Make sure you have a twitter account, have created a Twitter
application and have your consumer keys handy.

 

Place the proxy folder somewhere on the same domain as your application
that will be searching Twitter and make note of the URL to this folder. We will
refer to this URL as **$OAUTH_URL** in the rest of the documentation.

## Setup Instructions

Configure the config.php file

Set the URL to the callback file contained in the same folder as
this file.

Set your twitter applicationÕs key and secret key.

Set the name of the cookie youÕd like to use to store the userÕs
app credentials.

Set the domain you are setting the cookie on.

Create your own encryption key and initialization vector and set
them

Set the time zone to match your serverÕs location.

## How to use

### Logging in

To sign in on behalf of a
user, your web application should call the **$OAUTH_URL**/sign_in.php file
with a URL parameter named Òredirect_uriÓ and set it the URL that you wish to
return to after successfully or unsuccessfully logging in. Make sure that the
URL is encoded as a URL parameter.

 

On successful login, a user
will be redirected to the **$OAUTH_URL**/callback.php file, which will set
the encrypted cookie and send them to the Òredirect_uriÓ that you supplied as a
parameter to the sign-in page.

 

On unsuccessful login, a user
will just be redirected back to the Òredirect_uriÓ URL specified with error URL
parameters set by Twitter.

 

If a user cancels sign in,
they will be redirected to the Òredirect_uriÓ URL.

 

### Making Search Requests

Once the cookie has been set, the user can successfully make
authenticated requests using the $**OAUTH_URL**/index.php file. This file
will look for, unencrypt and read the cookie and try to authenticate with its
credentials.

 

Using the [same URL
parameters as the Twitter Search API](https://dev.twitter.com/docs/api/1.1/get/search/tweets) your app will make calls to your proxy
**$OAUTH_URL**/index.php file instead of the Twitter API URL.

 

Replace:
https://api.twitter.com/1.1/search/tweets.json

New: [http://myapp.com/oauth/index.php](http://myapp.com/oauth/index.php)

 

Example request:

**$OAUTH_URL**/index.php?q=%23wildfire&amp;count=100&amp;result_type=recent&amp;include_entities=false&amp;geocode=38.34380200088966%2C-115.99417955611926%2C500mi

 

### Checking if authenticated

If you make a call to the **$OAUTH_URL**/index.php file
without proper authentication it will return a JSON object with the property ÒloggedInÓ
set to false and an array of error objects.

 

myCallbackFunction({

            loggedIn:
false

            error:[{É}]

});

 

 

Your application can see if a user is signed-in by making a
request to the **$OAUTH_URL**/index.php file and seeing if the loggedIn
property is set to false. If it is, then you can prompt or direct them to
sign-in. If not, then you can freely make requests to the Twitter search API on
their behalf.

 

### Changing credentials/deleting cookie

If you would like to force someone to log in while deleting
their cookie you can call the **$OAUTH_URL**/sign_in.php file with the param
&amp;force_login=true

This will force them to re-login if they are already logged
in allowing them to switch user accounts if necessary.

 

[http://myapp.com/oauth/sign_in.php?redirect_uri=http://myapp.com/app/&amp;force_login=true](http://myapp.com/oauth/sign_in.php?redirect_uri=http://myapp.com/app/&amp;force_login=true)

 

You can also call the **$OAUTH_URL**/index.php file with
the URL parameter ÒdÓ set to true to delete the cookie without signing in.

[http://myapp.com/oauth/index.php?d=true](http://myapp.com/oauth/index.php?d=true)