<?php
/**
 * Copyright 2016 Sony Corporation
 */

namespace CdnPurge\Common\Enum;

/**
 * Implementation of a basic enumeration class
 */
abstract class BasicEnum {
    /**
     * Construct won't be called inside this class and is uncallable from
     * the outside. This prevents instantiating this class.
     * This is by purpose, because we want a static class.
     */
    private function __construct() {}

    /** @var array Cached class constants */
    private static $constCacheArray = NULL;

    /**
     * Fetch all the enum names
     *
     * @return array All the names of the enum class as an array
     */
    public static function names()
    {
        return array_keys(self::getConstants());
    }

    /**
     * Fetch all the enum values
     *
     * @return array All the values of the enum class as an array
     */
    public static function values()
    {
        return array_values(self::getConstants());
    }

    /**
     * Get name for a given const value
     *
     * @param string $value Value to find name for
     * @return FALSE|string Corresponding name if found. FALSE if no such value is present.
     */
    public static function getName($value)
    {
        $constants = self::getConstants();

        foreach ($constants as $name => $givenVal) {
            if ($givenVal == $value) {
                return $name;
            }
        }

        return FALSE;
    }

    /**
     * Checks if the enum name is valid
     *
     * @param string  $name   The enum name to validate
     * @param boolean $strict If set true, validation is case-sensitive
     *
     * @return boolean  true if name is valid
     */
    public static function isValidName($name, $strict = FALSE)
    {
        $constants = self::getConstants();

        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    /**
     * Checks if the enum value is valid
     *
     * @param string  $value   The enum value to validate
     *
     * @return boolean  true if value is valid
     */
    public static function isValidValue($value)
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, TRUE);
    }

    /**
     * Get all the constants of the calling enum class
     *
     * @return array All the class constants as an array
     */
    private static function getConstants()
    {
        if (self::$constCacheArray == NULL) {
            self::$constCacheArray = [];
        }
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new \ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }
}
