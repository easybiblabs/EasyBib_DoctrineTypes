<?php
namespace EasyBib\Doctrine\Types\Test;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\PersistentObject;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Common\Util\Debug; // for debugging: Debug::dump

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
        if (!Type::hasType('hstore')) {
            Type::addType('hstore', 'EasyBib\Doctrine\Types\Hstore');
        }

        $isDevMode      = true;
        $doctrineConfig = Setup::createAnnotationMetadataConfiguration(
            array(__DIR__ . '/Entity'),
            $isDevMode
        );

        // database configuration parameters
        $rootTestsFolder = dirname(dirname(dirname(dirname(__DIR__))));
        if (file_exists($rootTestsFolder . '/db-config.php')) {
            $dbConfig = include $rootTestsFolder . '/db-config.php';
        } elseif (isset($_ENV['TRAVIS'])) {
            $dbConfig = include $rootTestsFolder . '/db-config-travisci.php';
        } else {
            throw new \RuntimeException("No database configuration found!");
        }

        // create the entity manager
        $this->em = EntityManager::create($dbConfig, $doctrineConfig);

        // enable 'hstore'
        $this->em->getConnection()->exec("CREATE EXTENSION IF NOT EXISTS hstore");

        // make the PersistentObject happy
        PersistentObject::setObjectManager($this->em);
    }

    public function tearDown()
    {
        //$this->em->getConnection()->close();
        //unset($this->em);
    }

    /**
     * Basic operations against the entity.
     */
    public function testEntity()
    {
        $note = new Note();

        $note->setTitle(sprintf("This is the note's title (created: %s)", date("Y-m-d")));

        $attributes = array(
            'schemaless' => 'data',
            'in'         => 'postgres',
            'mind'       => 'blown',
            'nosql'      => 'goes rdbms',
            'foo'        => 1.1,
        );

        $note->setAttributes($attributes);

        $this->em->persist($note);
        $this->em->flush();

        $id = $note->getId();

        $this->assertInternalType('int', $id);
        unset($note);

        $noteDatabase = $this->em->getRepository('EasyBib\Doctrine\Types\Test\Entity\Note')->find($id);
        $this->assertEquals($attributes, $noteDatabase->getAttributes());
    }

    /**
     * @todo This test is totally arbitrary because I am retrieving something that
     * exists in my database. I need to figure out how to make Doctrine forget what
     * it previously knew/created.
     */
    public function testFromDatabase()
    {
        $note       = $this->em->getRepository('EasyBib\Doctrine\Types\Test\Entity\Note')->find(38);
        $attributes = $note->getAttributes();

        $this->assertInternalType('array', $attributes);
        $this->assertArrayHasKey('schemaless', $attributes);
        $this->assertArrayHasKey('in', $attributes);
        $this->assertArrayHasKey('mind', $attributes);
        $this->assertArrayHasKey('nosql', $attributes);
        $this->assertArrayHasKey('foo', $attributes);

        $this->assertInternalType('float', $attributes['foo']);
    }

    /**
     * Ensure type hstore is added when the table is created!
     */
    public function testSchema()
    {
        $tool = new SchemaTool($this->em);
        $classes = array(
            $this->em->getClassMetadata('EasyBib\Doctrine\Types\Test\Entity\Note'),
        );
        $sql = $tool->getCreateSchemaSql($classes);

        $this->assertEquals("CREATE TABLE test (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, attributes hstore NOT NULL, PRIMARY KEY(id))", $sql[0]);
    }
}
