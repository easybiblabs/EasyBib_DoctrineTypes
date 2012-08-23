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
     * @var array
     */
    protected $classes;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var Doctrine\ORM\Tools\SchemaTool
     */
    protected $tool;

    /**
     * @var bool
     */
    protected $isTravis;

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
        $this->isTravis  = getenv("TRAVIS");
        if (file_exists($rootTestsFolder . '/db-config.php')) {
            $dbConfig = include $rootTestsFolder . '/db-config.php';
        } elseif (false !== $this->isTravis) {
            $dbConfig = include $rootTestsFolder . '/db-config-travisci.php';
        } else {
            throw new \RuntimeException("No database configuration found.");
        }

        // create the entity manager
        $this->em = EntityManager::create($dbConfig, $doctrineConfig);

        // enable 'hstore'
        $this->em->getConnection()->exec("CREATE EXTENSION IF NOT EXISTS hstore");

        // register type with DBAL
        $this->em->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('hstore', 'hstore');

        // make the PersistentObject happy
        PersistentObject::setObjectManager($this->em);

        // create table
        $this->setUpSchema($this->em);
    }

    /**
     * Create the schema!
     *
     * @return void
     */
    public function setUpSchema(EntityManager $em)
    {
        $this->classes = array(
            $em->getClassMetadata('EasyBib\Doctrine\Types\Test\Entity\Note'),
        );

        $this->tool = new SchemaTool($em);
        if (false === $this->isTravis) {
            return;
        }
        $this->tool->createSchema($this->classes);
    }

    public function tearDown()
    {
        /**
         * @desc Despite throwing an exception in {@link self::setUp()}, PHPUnit
         *       will still end up in this function.
         */
        if ($this->em !== null) {
            if (false === $this->isTravis) {
                $this->em->getConnection()->close();
                unset($this->em);
                return;
            }
            $this->tool->dropSchema($this->classes);
            $this->em->getConnection()->close();
            unset($this->em);
        }
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
        if (false !== $this->isTravis) {
            $this->markTestSkipped("This won't run on travis-ci.");
            return;
        }

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
        $sql = $this->tool->getCreateSchemaSql($this->classes);
        $this->assertEquals("CREATE TABLE test (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, attributes hstore NOT NULL, PRIMARY KEY(id))", $sql[0]);
    }

    /**
     * There seems to be a bug when I try to drop a table.
     */
    public function testDropTable()
    {
        $statements = $this->tool->getDropSchemaSQL($this->classes);
        $this->assertInternalType('array', $statements);
        $this->assertSame('DROP SEQUENCE test_id_seq', $statements[0]);
        $this->assertSame('DROP TABLE test', $statements[1]);
    }
}
