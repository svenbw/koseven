# Deploying (Setting up a production environment)

There are a few things you'll want to do with your application before moving into production.

## Bootstrap

The `bootstrap.php` file covers most of the global settings that would change between environments.

Generally you should make the following 3 changes:

1. Setting the [KO7::$environment](../api/KO7#property-environment) variable to `PRODUCTION`
2. Enable caching and disable profiling in the [KO7::init](../api/KO7#init) call
3. Change the [Cookie::$salt](../api/Cookie#property-salt)

## Configuration

Change the `url.php` configuration and edit the `trusted_hosts` section.
Add your production environment domain(s) into the list and remove the developing environment domain(s).

Also check your other configuration files (for example if you use the Database Module check your `database.php` 
configuration file).
