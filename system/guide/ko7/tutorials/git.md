# Creating a New Application using Git

[!!] The following examples assume that your web server is already set up.

Using your console, change to the configured document root of your websever and run `git init`. 
This will create the bare structure for a new git repository.

## Using Koseven inside your main repository

Now we will download the Koseven Framework by cloning it's contents via git:

    git clone git://github.com/koseven/koseven.git .

Next you can commit those files to your repository:

    git commit -m 'Initial commit'

That's all there is to it. You now have an application that is using Git for versioning.

## Using Koseven as a sub-repository

If you want an option to make it easier to upgrade to current koseven versions by keeping it as a submodule in
your repository here is how you can do it.

First we will add koseven as submodule into our repository via:

    git submodule add -b master https://github.com/koseven/koseven system
    git submodule init 
    
*Note: You can also use other branches for example "devel" (not recommended in production environments)*

Now let's copy the `public` and `application` folder from `system/application` and `system/public` into our repository
root:

    cp -R system/application . 
    cp -R system/public .
    
Alright. Since we are done with the basic folder structure we now need to edit a few lines in our `public/index.php`
by changing the `$modules` and `$system` variables:

    $modules = 'system'.DIRECTORY_SEPARATOR.'modules';
    $system = 'system'.DIRECTORY_SEPARATOR.'system';
    
Now let's copy the `.gitignore` file from koseven and an initialization commit
    
    cp system/.gitignore .
    git add -A
    git commit -m 'Initial Commit'
    
That's it! Whenever you want to update koseven simply run:

    git submodule update --recursive --remote
    
without making any further changes or need to merge something.

*Note: If you update your koseven version make sure application/bootstrap.php and index.php are not changing, if they do so please keep in mind, that you also have to change them.*

    


