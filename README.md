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

## EasyAdmin menu action dropdown

```yaml
easy_admin:
    entities:
        example:
            list:
                actions:
                    - { label: 'Action', name: 'action', menu_dropdown: true } # [↓]
                    - { label: 'Action 1.1', name: 'test', menu_dropdown: 'Group 1' } # [Group 1 ↓]
                    - { label: 'Action 1.2', name: 'test', menu_dropdown: 'Group 1' } # [Group 1 ↓]
                    - { label: 'Action 2.1', name: 'test', menu_dropdown: 'Group 2' } # [Group 2 ↓]
                    - { label: 'Action 2.2', name: 'test', menu_dropdown: 'Group 2' } # [Group 2 ↓]
```
