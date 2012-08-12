<?php
namespace EasyBib\Doctrine\Types\Test;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\PersistentObject;
use Doctrine\Common\Util\Debug;

use EasyBib\Doctrine\Types\Test\Entity\Note;

class HstoreTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    public function setUp()
    {
        // register our custom type
        Type::addType('hstore', 'EasyBib\Doctrine\Types\Hstore');

        $isDevMode      = true;
        $doctrineConfig = Setup::createAnnotationMetadataConfiguration(
            array(__DIR__ . '/Entity'),
            $isDevMode
        );

        // database configuration parameters
        $rootTestsFolder = dirname(dirname(dirname(dirname(__DIR__))));
        if (file_exists($rootTestsFolder . '/db-config.php')) {
            $dbConfig = include $rootTestsFolder . '/db-config.php';
        } else {
            throw new \RuntimeException("No database configuration found!");
        }

        // create the entity manager
        $this->em = EntityManager::create($dbConfig, $doctrineConfig);

        // make the PersistentObject happy
        PersistentObject::setObjectManager($this->em);
    }

    /**
     * Basic operations against the entity.
     */
    public function testEntity()
    {
        $note = new Note();

        $note->setTitle(sprintf("This is the note's title (created: %s)", date("Y-m-d")));
        $note->setAttributes(array(
            'schemaless' => 'data',
            'in'         => 'postgres',
            'mind'       => 'blown',
            'nosql'      => 'goes rdbms',
            'foo'        => 1.1,
        ));

        $this->em->persist($note);
        $this->em->flush();

        $id = $note->getId();

        $this->assertInternalType('int', $id);
    }
}
