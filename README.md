# EasyAdminExtensionBundle
EasyAdmin Extension Bundle

## Add admin toolbar block

1. Create class
```php
<?php

use KRG\EasyAdminExtensionBundle\Toolbar\ToolbarInterface;
use Symfony\Component\Templating\EngineInterface;

class SampleToolbar implements ToolbarInterface
{
    /** @var EngineInterface */
    protected $templating;
    
    public function render() 
    {
        return $this->templating->render('@KRGEasyAdminExtension/twig/sample.html.twig');
    }
}
```

2. Create twig
```twig
<a href="#">Sample action</a>   
```

3. Create tagged service 
```yaml
KRG\EasyAdminExtensionBundle\Toolbar\:
    resource: '../../Toolbar'
    tags: ['krg.easyadmin.toolbar']
    public: true
```

4. Use twig extension
```twig
<body>
    {{ krg_admin_toolbar() }}
    ...
</body>
```


## Configuration

``yaml
krg_easy_admin_extension:
    debug: true
```
