<?php
namespace Delbio\Bundle\DoctrineFixtureLoaderBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractUploadableDoctrineEntityLoadDataFromResource extends AbstractUploadableLoadDataFromResource
{
    /**
     * @var ObjectManager
     */
    protected $em;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    protected function prepare(ContainerInterface $container, ObjectManager $manager)
    {
        $this->em = $manager;
        $this->validator = $container->get('validator');
    }

    protected function persist($entity)
    {
        $errors = $this->validator->validate($entity);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new \RuntimeException($errorsString);
        }
        $this->em->persist($entity);
    }

    protected function flush()
    {
        $this->em->flush();
    }
}