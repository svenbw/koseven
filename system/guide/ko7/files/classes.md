# Classes

Classes are blueprints for objects.
Koseven makes use of the PHP autoloading capability, it uses the `spl_autoload()` magic function along with the 
[Cascading Filesystem](files) to create an easy means for loading classes. You can find more about this in
the [Autoloading](autoloading) section of this Documentation.


[Models](mvc/models) and [Controllers](mvc/controllers) are classes as well, but are treated slightly differently by Koseven.  Read their respective pages to learn more.

## Helper or Library?

Koseven does not differentiate between "helper" and "library" classes like in previous versions.  
They are all placed in the `classes/` folder and follow the same [conventions](conventions#class-names-and-file-location). 
 
The distinction is that in general, a "helper" class is used statically, (for examples see the 
[helpers included in Koseven](helpers)), and library classes are typically instantiated and used as objects 
(like the [Database query builders](../database/query/builder)). The distinction is not black and white, and is
irrelevant anyways, since they are treated the same by Koseven.

## Creating a class

To create a new class, simply place a file in the `classes/` directory at any point in the [Cascading Filesystem](files), that follows the [Class naming conventions](conventions#class-names-and-file-location).  For example, lets create a `Foobar` class.

	// classes/Foobar.php
	
	class Foobar {
		public static function magic()
		{
			// Does something
		}
	}
	
We can now call `Foobar::magic()` any where and Koseven will [autoload](autoloading) the file for us.

We can also put classes in subdirectories.

	// classes/Professor/Baxter.php
	
	class Professor_Baxter {
		public static function teach()
		{
			// Does something
		}
	}
	
We could now call `Professor_Baxter::teach()` any where we want.

For examples of how to create and use classes, simply look at the `classes` folder in `system` or any module.

## Namespacing your classes

Koseven Core files are currently not namespaced, but it has integrated support for namespaced classes.
They follow the same Convention as none-namespaced classes with the addition that the namespace also
is considered as a folder.

Examples:

| Namespace | Class Name  | Path                            |
|-----------|-------------|---------------------------------|
| Tools     | Screwdriver | classes/Tools/Screwdriver.php   |
| Tools     | Wood_Saw    | classes/Tools/Wood/Saw.php      |

Please keep in mind, that controllers are treated a bit different then standard classes and therefore can
not be namespaced at this moment. For namespaced Controllers you can have a look at [this module](https://github.com/errotan/koseven-controller-namespace)
which is providing that functionality.

Here is an example of a Namespaced class

    // classes/Helpers/Arrays
    
    namespace Helpers;
    
    class Arrays extends \Arr {
        public static function combine()
	{
                // Does something
        }
    }

It does not matter if you use it directly via `\Helpers\Arrays::combine` or do it via the use statement `use Helpers\Arrays`
and call the class like this `Arrays::combine`. Both of those ways work and Koseven will automatically load your 
namespaced class.
