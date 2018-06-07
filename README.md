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

```yaml
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
                    - { label: 'Action 1.1', name: 'test', menu_dropdown: { name: 'Group 1' } # [Group 1 ↓]
                    - { label: 'Action 1.2', name: 'test', menu_dropdown: { name: 'Group 1' } # [Group 1 ↓]
                    - { label: 'Action 2.1', name: 'test', menu_dropdown: { name: 'Group 2' } # [Group 2 ↓]
                    - { label: 'Action 2.2', name: 'test', menu_dropdown: { name: 'Group 2' } # [Group 2 ↓]
```

## EasyAdmin custom menu groups

All menu children from different files with the same group will be merge into one.

```yaml
easy_admin:
    design:
        menu:
            -
                label: 'System'
                group: 'System'
```

## EasyAdmin custom menu priority

The priority value is optional and defaults to 0. The higher the priority, the sooner it gets executed.

```yaml
easy_admin:
    design:
        menu:
            - { label: 'Top item', priority: 9999 }
            - { label: 'Bottom item', priority: 0 }
```


## EasyAdmin custom list translated item

```yaml
easy_admin:
    entities:
        Entity:
            list:
                fields:
                    -
                        label: 'Example'
                        property: 'hello'
                        badge: true # Optional
                        domain: 'translation_domain'
                        template: '@KRGEasyAdminExtension/easy_admin/field_string.html.twig'
```

The value is translated with the domain, the label class is formated like (value ~ '_label')|trans

```yaml
hello: Hello
hello_label: label-default
```

## EasyAdmin user label list

```yaml
easy_admin:
    entities:
        Entity:
            class: 'AppBundle\Entity\User'
            list:
                fields:
                    -
                        property: 'user'
                        type: object
                        fullname: false
                        template: '@KRGEasyAdminExtension/easy_admin/field_user.html.twig'
````
