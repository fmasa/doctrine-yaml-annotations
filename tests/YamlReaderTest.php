<?php

namespace Fmasa\DoctrineYamlAnnotations;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/TestAnnotation.php';
require_once __DIR__ . '/TestEntity.php';

class YamlReaderTest extends TestCase
{

    /**
     * @dataProvider configurationProvider
     */
    public function testGetPropertyAnnotation(Configuration $configuration)
    {
        $reader = new YamlReader($configuration, ['ann' => TestAnnotation::class]);

        $annotation = $reader->getPropertyAnnotation(
            $this->getClass()->getProperty('foo'),
            TestAnnotation::class
        );

        $this->assertInstanceOf(TestAnnotation::class, $annotation);
        $this->assertSame(10, $annotation->value);
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testGetAliasedPropertyAnnotation(Configuration $configuration)
    {
        $reader = new YamlReader($configuration, ['ann' => TestAnnotation::class]);

        $annotation = $reader->getPropertyAnnotation(
            $this->getClass()->getProperty('bar'),
            TestAnnotation::class
        );

        $this->assertInstanceOf(TestAnnotation::class, $annotation);
        $this->assertSame(20, $annotation->value);
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testGetAllPropertyAnnotations(Configuration $configuration)
    {
        $reader = new YamlReader($configuration, ['ann' => TestAnnotation::class]);

        $annotations = $reader->getPropertyAnnotations(
            $this->getClass()->getProperty('bar')
        );

        $this->assertCount(1, $annotations);

        $annotation = $annotations[TestAnnotation::class];

        $this->assertInstanceOf(TestAnnotation::class, $annotation);
        $this->assertSame(20, $annotation->value);
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testGetClassAnnotation(Configuration $configuration)
    {
        $reader = new YamlReader($configuration, ['ann' => TestAnnotation::class]);

        $annotation = $reader->getClassAnnotation(
            $this->getClass(),
            TestAnnotation::class
        );

        $this->assertInstanceOf(TestAnnotation::class, $annotation);
        $this->assertSame(30, $annotation->value);
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testGetEmbeddableAnnotation(Configuration $configuration)
    {
        $reader = new YamlReader($configuration, ['ann' => TestAnnotation::class]);

        $annotation = $reader->getPropertyAnnotation(
            $this->getClass()->getProperty('embeddable'),
            TestAnnotation::class
        );

        $this->assertInstanceOf(TestAnnotation::class, $annotation);
        $this->assertSame(40, $annotation->value);
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testGetAllClassAnnotations(Configuration $configuration)
    {
        $reader = new YamlReader($configuration, ['ann' => TestAnnotation::class]);

        $annotations = $reader->getClassAnnotations(
            $this->getClass()
        );

        $this->assertCount(1, $annotations);

        $annotation = $annotations[TestAnnotation::class];

        $this->assertInstanceOf(TestAnnotation::class, $annotation);
        $this->assertSame(30, $annotation->value);
    }

    /**
     * @dataProvider configurationProvider
     */
    public function testMethodRelatedMethodsReturnsNothing(Configuration $configuration)
    {
        $reader = new YamlReader($configuration, ['ann' => TestAnnotation::class]);

        $annotations = $reader->getClassAnnotations(
            $this->getClass()
        );

        $this->assertCount(1, $annotations);

        $method = $this->getClass()->getMethod('fooBar');

        $annotation = $reader->getMethodAnnotation($method, TestAnnotation::class);
        $this->assertNull($annotation);

        $annotations = $reader->getMethodAnnotations($method);

        $this->assertSame([], $annotations);
    }

    public function configurationProvider(): array
    {
        $arguments = [];
        $configuration1 = new Configuration();
        $configuration1->setMetadataDriverImpl(new YamlDriver(__DIR__));

        $arguments[] = [$configuration1];

        $configuration2 = new Configuration();
        $chain = new MappingDriverChain();
        $chain->addDriver(new YamlDriver(__DIR__), '');
        $configuration2->setMetadataDriverImpl($chain);

        $arguments[] = [$configuration2];

        return $arguments;
    }

    private function getClass(): \ReflectionClass
    {
        return new \ReflectionClass(TestEntity::class);
    }

}
