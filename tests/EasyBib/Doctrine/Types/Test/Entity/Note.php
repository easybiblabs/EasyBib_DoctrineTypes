<?php
namespace EasyBib\Doctrine\Types\Test\Entity;

/**
 * @desc The best kept secret!
 */
use Doctrine\Common\Persistence\PersistentObject;

/**
 * The following entity is for testing purposes and represent a note object in the
 * database. The following 'columns' are provided: id, title and attributes.
 *
 * This is not a real project - more or less an integration test to confirm my type
 * implementation for Hstore.
 *
 * @Entity
 * @Table("test")
 */
class Note extends PersistentObject
{
    /**
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     * @Id
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $title;

    /**
     * @Column(type="hstore")
     */
    protected $attributes;
}
