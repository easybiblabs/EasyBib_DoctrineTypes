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
        if (empty($value)) {
            return array();
        }

        $attributes = explode(', ', $value);
        $array      = array();
        foreach ($attributes as $attribute) {
            list($key, $value) = explode('=>', $attribute);

            $value = substr($value, 1, -1);
            if (is_numeric($value)) {
                if (false === strpos($value, '.')) {
                    $value = (int) $value;
                } else {
                    $value = (float) $value;
                }
            } elseif (in_array($value, array('true', 'false'))) {
                $value = ($value == 'true')?true:false;
            }

            $array[substr($key, 1, -1)] = $value;
        }
        return $array;
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

        return $hstoreString;
    }

    public function getName()
    {
        return self::HSTORE;
    }
}
