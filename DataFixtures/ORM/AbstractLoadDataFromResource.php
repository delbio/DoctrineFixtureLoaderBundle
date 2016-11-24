<?php
namespace Delbio\Bundle\DoctrineFixtureLoaderBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractLoadDataFromResource extends AbstractDataFixture
{

    protected $kernel;

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->prepare($this->container, $manager);
        $data = $this->getData();
        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($data as $key => $definition) {
            $this->writeConsole("adding " . $key);
            $entity = $this->createEntity();
            $this->populateEntity($accessor, $entity, $definition);
            $this->setReference($key, $entity);
            $this->persist($entity);
        }
        $this->flush();


        $this->writeConsole("End Loading");
    }

    abstract protected function prepare(ContainerInterface $container, ObjectManager $manager);

    /**
     * @return mixed
     */
    protected function getData()
    {
        $this->kernel = $this->container->get('kernel');
        return Yaml::parse(
            file_get_contents(
                $this->locateResource($this->getResoursePath($this->kernel))
            ),
            Yaml::PARSE_DATETIME
        );
    }

    /**
     * @example '@UtilityBundle/Resources/fixtures/users.yml'
     * @param $kernel
     * @return string
     */
    abstract protected function getResoursePath($kernel);

    protected function locateResource(string $configPath)
    {
        return $this->kernel->locateResource($configPath);
    }

    abstract protected function createEntity();

    protected function populateEntity(PropertyAccessor $accessor, $entity, $definition)
    {
        if ( isset($definition['references']) ){
            foreach ($definition['references'] as $property) {
                $this->writeConsole("> setting " . $property);
                $value = $this->getReference($definition['properties'][$property]);
                $accessor->setValue($entity, $property, $value);
                unset($definition['properties'][$property]);
            }
        }

        foreach ($definition['properties'] as $property => $value) {
            $this->writeConsole("> setting " . $property);
            $accessor->setValue($entity, $property, $value);
        }
    }

    abstract protected function persist($entity);
    abstract protected function flush();
}
