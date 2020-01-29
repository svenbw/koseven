# Cookie Security

In this Document we are talking about Cookie Security. If you need general Information or guides with Cookies please 
visit the [Cookies](cookies) section of this Documentation.

## General Security

Pleas keep in mind, that you should only store non-private Information in Cookies. 
[RFC 6265](http://www.faqs.org/rfcs/rfc6265.html) clearly states that cookies are not intended to provide security.

## Builtin Security

Koseven uses "signed" cookies. Every cookie that is stored is combined with a secure hash to prevent modification of the 
cookie. For example if you set a cookie:

    Cookie::set('user_id', 10);

The Value of the Cookie will automatically be hashed through the `hash_hmac` function. Let's look a bit closer into it:

    hash_hmac('sha1', $name.$value.Cookie::$salt, Cookie::$salt);
    
As you can see the value gets hashed with the `sha1` algorithm. As 'data' variable we pass a string consisting of: 
The cookie name, the cookie value and the cookie salt - which is also used as hashing key.

## Additional Hints

To prevent Cookies from being accessable via JavaScript you can make your Cookie 'http only'. You can do this by 
changing the [Cookie::$httponly](../api/Cookie#property-httponly) setting to `TRUE`.
    
    Cookie::$httponly = TRUE;
    
You can harden this by only allowing them to be accessed over HTTPS protocol.
Do this by changing the [Cookie::$secure](../api/Cookie#property-secure) setting to `TRUE`.

    Cookie::$secure = TRUE;
