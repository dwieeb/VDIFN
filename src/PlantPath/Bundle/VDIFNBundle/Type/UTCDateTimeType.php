<?php

namespace PlantPath\Bundle\VDIFNBundle\Type;

use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;

/**
 * http://doctrine-orm.readthedocs.org/en/latest/cookbook/working-with-datetime.html#handling-different-timezones-with-the-datetime-type
 */
class UTCDateTimeType extends DateTimeType
{
    protected static $utc;

    public static function getUTC()
    {
        if (null === self::$utc) {
            self::$utc = new \DateTimeZone('UTC');
        }

        return self::$utc;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $value->setTimezone(self::getUTC());

        return $value->format($platform->getDateTimeFormatString());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        $val = \DateTime::createFromFormat($platform->getDateTimeFormatString(), $value, self::getUTC());

        if (!$val) {
            throw ConversionException::conversionFailed($value, $this->getName());
        }

        return $val;
    }
}
