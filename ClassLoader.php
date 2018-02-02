<?php
namespace Dorans\Competition;

/**
 * TODO: use glob instead of hardcoding the folders
 *
 * Class ClassLoader
 * @package Dorans\Competition
 */
abstract class ClassLoader
{
    /**
     * Load all classes
     */
    public static function loadClasses()
    {
        self::loadEntities();
        self::loadRepositories();
        self::loadServices();
        self::loadUtils();
    }

    /**
     * Load the classes from the Entity folder
     */
    protected static function loadEntities()
    {
        $dir = 'Entity';
        self::checkIsDir($dir);

        $baseDir = $dir . '/Base';
        self::checkIsDir($baseDir);

        // first load base classes
        self::loadFromDir($baseDir);

        // next load 'normal' classes
        self::loadFromDir($dir);
    }

    /**
     * Load the classes from the Repository folder
     */
    protected static function loadRepositories()
    {
        $dir = 'Repository';
        self::checkIsDir($dir);

        // next load 'normal' classes
        self::loadFromDir($dir);
    }

    /**
     * Load the classes from the Service folder
     */
    protected static function loadServices()
    {
        $dir = 'Service';
        self::checkIsDir($dir);

        // next load 'normal' classes
        self::loadFromDir($dir);
    }

    /**
     * Load the classes from the Service folder
     */
    protected static function loadUtils()
    {
        $dir = 'Util';
        self::checkIsDir($dir);

        $helperDir = $dir . '/Helper';
        self::checkIsDir($helperDir);

        // first load base classes
        self::loadFromDir($helperDir);

        // next load 'normal' classes
        self::loadFromDir($dir);
    }

    /**
     * @param $dir
     * @throws \Exception
     */
    protected static function checkIsDir($dir)
    {
        if (!is_dir(__DIR__ . '/' . $dir)) {
            throw new \Exception('Invalid directory ' . $dir);
        }
    }

    /**
     * @param $dir
     */
    protected static function loadFromDir($dir)
    {
        foreach (glob(__DIR__ . '/' . $dir . '/*.php') as $file) {
            include_once($file);
        }
    }
}