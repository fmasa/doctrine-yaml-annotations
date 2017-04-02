# Doctrine YAML annotations
[![Build Status](https://travis-ci.org/fmasa/doctrine-yaml-annotations.svg?branch=master)](https://travis-ci.org/fmasa/doctrine-yaml-annotations)
[![Coverage Status](https://coveralls.io/repos/github/fmasa/doctrine-yaml-annotations/badge.svg?branch=master)](https://coveralls.io/github/fmasa/doctrine-yaml-annotations?branch=master)

One of the great features of Doctrine 2 is extensibility.
Doctrine offers multiple ways to specify mapping information,
but the most of the extensions only supports Annotations configuration.

This package adds custom annotations to your YAML mapping files.

What is currently supported:
- property annotations (fields and embeddables)
- class annotations

## Installation
The best way to install fmasa/doctrine-yaml-annotations is using [Composer](https://getcomposer.org/):

    $ composer require fmasa/doctrine-yaml-annotations

For example let's configure the [Consistence](https://github.com/consistence/consistence-doctrine) extension for Doctrine.

First we have to create annotation reader:
```php
use Fmasa\DoctrineYamlAnnotations\YamlReader;

$configuration = $entityManager->getConfiguration();
$reader = new YamlReader($configuration, [
    'enum' => EnumAnnotation::class
]);
    
```
Second argument for AnnotationReader is optional map with entity aliases.

Add annotations to your mapping files:
```yaml
Some\Entity:
    
    ...
    
    fields:
        state:
            type: enum_string
            annotations:
                Consistence\Doctrine\Enum\EnumAnnotation: # or just enum
                    class: StateEnum
```

Now you can read annotations just using `Doctrine\Common\Annotations\Reader` API:
```php
$reader->getPropertyAnnotation(
    (new \ReflectionClass(Some\Entity::class))->getProperty('state'),
    EnumAnnotation::class
); // returns instance of EnumAnnotation { class => "StateEnum" }
```
