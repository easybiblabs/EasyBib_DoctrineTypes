# [WIP] Support for PostgreSQL's hstore type for Doctrine

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
