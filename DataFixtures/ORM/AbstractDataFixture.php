<?php
namespace Delbio\Bundle\DoctrineFixtureLoaderBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


abstract class AbstractDataFixture extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface {

    /**
     * @var null|ConsoleOutput
     */
    protected $consoleOutput = null;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * AbstractDataFixture constructor.
     */
    public function __construct() {
        $this->consoleOutput = new ConsoleOutput();
    }

    /**
     * @param ContainerInterface|null $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return null|ConsoleOutput
     */
    private function getConsoleOutputInstance() {
        return null !== $this->consoleOutput ? $this->consoleOutput : new ConsoleOutput();
    }

    /**
     * {@inheritDoc}
     */
    public abstract function load(ObjectManager $manager);

    /**
     * {@inheritDoc}
     */
    public abstract function getOrder();

    /**
     * @param string $text
     * Write text in console
     */
    public function writeConsole($text){
        $this->getConsoleOutputInstance()->writeln("<comment>".$text."</comment>");
    }

}