# [WIP] Support for PostgreSQL's hstore type for Doctrine

[![Build Status](https://secure.travis-ci.org/easybib/EasyBib_DoctrineTypes.png?branch=master)](http://travis-ci.org/easybib/EasyBib_DoctrineTypes)

Add the following to your composer.json:

    {
        "repositories": [
            {
                "type": "vcs",
                "url": "http://github.com/easybib/EasyBib_DoctrineTypes"
            }
        ]
    }

Then install/update:

    ./composer.phar install


Finally (assuming the above worked):

    use Doctrine\DBAL\Types\Type;
    Type::addType('hstore', 'EasyBib\Doctrine\Type\Hstore');

    /* ... more setup here ... */
    $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('hstore', 'hstore');
