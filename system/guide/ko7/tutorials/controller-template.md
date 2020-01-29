# Create a Controller which automatically renders a View Template

Koseven comes shipped with the abstract [Controller_Template](../api/Controller_Template) class which allows you
to set a default template for your controller which is then rendered automatically.

Here is a little controller example:

    abstract class Controller_Base extends Controller_Template {
        
        // Here we set our template name (we will create this one later)
        public $template = 'layout';
        
        public function action_index()
        {
            $template = new View('world');
            $this->template->bind('content', $template);
        }

    }
    
Now let's create the main layout (`views/layout.php`):
    
    <!DOCTYPE html>
    <html>
        <head>
          ...
        </head>
        <body>
          <span style="color: red;">Hello, <?php $content->render() ?></span>
        </body>
    </html>
    
And here is our content view (`views/world.php`)

    World!
    
The above example will output `Hello, World!` in a red font.
