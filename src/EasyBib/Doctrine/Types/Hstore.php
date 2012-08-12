<?php
namespace EasyBib\Doctrine\Types;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class Hstore extends Type
{
    const HSTORE = 'hstore';

    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        // return the SQL used to create your column type. To create a portable column type, use the $platform.
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // This is executed when the value is read from the database. Make your conversions here, optionally using the $platform.
        var_dump($value, '2php'); exit;
    }

    /**
     * @param mixed            $value
     * @param AbstractPlatform $platform
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return '';
        }
        if ($value instanceof \stdClass) {
            $value = get_object_vars($value);
        }
        if (!is_array($value)) {
            throw new \InvalidArgumentException("Hstore value must be off array or \stdClass.");
        }

        $hstoreString = '';

        foreach ($value as $k => $v) {
            if (!is_string($v) && !is_numeric($v) && !is_bool($v)) {
                throw new \InvalidArgumentException("Cannot save 'nested arrays' into hstore.");
            }
            $v = trim($v);
            if (!is_numeric($v) && false !== strpos($v, ' ')) {
                $v = sprintf('"%s"', $v);
            }
            $hstoreString .= "$k => $v," . "\n";
        }
        $hstoreString = substr(trim($hstoreString), 0, -1) . "\n";

var_dump($hstoreString); exit;
        return $hstoreString;
    }

    public function getName()
    {
        return self::HSTORE;
    }
}
