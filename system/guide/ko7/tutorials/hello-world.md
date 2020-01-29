# Hello, World

Just about every framework ever written has some kind of hello world example included, so it'd be pretty rude of us to 
break this tradition!

We'll start out by looking into the very very basic hello world controller that comes shipped, and will dive bit into
it.
 
## Bare bones

First of all open the file `application/classes/Controller/Hello.php` it should look like this:

    <?php

    class Controller_Welcome extends Controller {
    
        public function action_index()
        {
            $this->response->body('hello, world!');
        }
    
    } // End Welcome

Lets see what's going on here:

`<?php`
:	You should recognize the first tag as an opening php tag (if you don't you should probably [learn php](http://php.net)).  

`class Controller_Welcome extends Controller`
:	This line declares our controller, each controller class has to be prefixed with `Controller_` and an underscore 
    delimited path to the folder the controller is in (see [Conventions and styles](../conventions) for more info).  
    Each controller should also extend the base `Controller` class which provides a standard structure for controllers.

`public function action_index()`
:	This defines the "index" action of our controller. Koseven will attempt to call this action if the user hasn't 
    specified an action.

`$this->response->body('hello, world!');`
:	And this is the line which outputs the customary phrase!

Now if you open your browser and go to `http://localhost/index.php/hello` (or `http://localhost/hello` if you are using
[clean urls](clean-urls)) you should see something like:

![Hello, World!](hello_world_1.png "Hello, World!")
