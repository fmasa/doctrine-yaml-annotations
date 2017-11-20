<?php

namespace Fmasa\DoctrineYamlAnnotations;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\YamlDriver;

class YamlReader implements Reader
{

    /** @var Configuration */
    private $configuration;

    /** @var YamlDriver[] */
    private $drivers;

    /** @var array */
    private $aliases;

    /** @var array */
    private $classAnnotations = [];

    /** @var array */
    private $propertyAnnotations = [];

    /** @var string[] */
    private $classes = [];

    public function __construct(Configuration $configuration, array $aliases = [])
    {
        $this->configuration = $configuration;
        $this->aliases = $aliases;
    }

    public function getClassAnnotations(\ReflectionClass $class)
    {
        $class = $class->getName();
        $this->loadClass($class);

        return $this->classAnnotations[$class] ?? [];
    }

    public function getClassAnnotation(\ReflectionClass $class, $annotationName)
    {
        return $this->getClassAnnotations($class)[$annotationName] ?? NULL;
    }

    public function getMethodAnnotations(\ReflectionMethod $method)
    {
        return []; // No support for methods yet
    }

    public function getMethodAnnotation(\ReflectionMethod $method, $annotationName)
    {
        return NULL; // No support for methods yet
    }

    public function getPropertyAnnotations(\ReflectionProperty $property)
    {
        $class = $property->getDeclaringClass()->getName();
        $this->loadClass($class);

        return $this->propertyAnnotations[$class][$property->getName()] ?? [];
    }

    public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName)
    {
        return $this->getPropertyAnnotations($property)[$annotationName] ?? NULL;
    }

    private function getAnnotations(array $element): array
    {
        if (!isset($element['annotations']) || !is_array($element['annotations'])) {
            return [];
        }

        $annotations = [];
        foreach ($element['annotations'] as $annotationName => $parameters) {
            $annotationName = $this->aliases[$annotationName] ?? $annotationName;

            $annotation = new $annotationName();
            foreach ($parameters as $property => $value) {
                $annotation->$property = $value;
            }

            $annotations[$annotationName] = $annotation;
        }

        return $annotations;
    }

    /**
     * @return YamlDriver[]
     */
    private function getDrivers(): array
    {
        if ($this->drivers !== NULL) {
            return $this->drivers;
        }

        $chain = $this->configuration->getMetadataDriverImpl();
        $drivers = $chain instanceof MappingDriverChain ? $chain->getDrivers() : [$chain];

        $this->drivers = [];

        foreach ($drivers as $driver) {
            if ($driver instanceof YamlDriver) {
                $this->drivers[] = $driver;
            }
        }

        return $this->drivers;
    }

    private function loadClass(string $class)
    {
        if (isset($this->classes[$class])) {
            return;
        }

        $this->propertyAnnotations[$class] = [];

        foreach ($this->getDrivers() as $driver) {
            if (!in_array($class, $driver->getAllClassNames(), TRUE)) {
                continue;
            }
            $classElement = $driver->getElement($class);

            $this->classAnnotations[$class] = $this->getAnnotations($classElement);

            $fields = $classElement['fields'] ?? [];

            if(isset($classElement['embedded'])) {
                $fields = array_merge($fields, $classElement['embedded']);
            }

            foreach ($fields as $propertyName => $field) {
                $this->propertyAnnotations[$class][$propertyName] = $this->getAnnotations($field);
            }
        }

        $this->classes[$class] = TRUE;
    }
}
