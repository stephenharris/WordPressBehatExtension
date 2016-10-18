<?php

namespace StephenHarris\WordPressBehatExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

use StephenHarris\WordPressBehatExtension\Context\WordPressContext;

/**
 * Class FeatureListener
 *
 * @package StephenHarris\WordPressBehatExtension\Listener
 */
class WordPressContextInitializer implements ContextInitializer
{
    private $wordpressParams;
    private $minkParams;
    private $basePath;

    /**
     * inject the wordpress extension parameters and the mink parameters
     *
     * @param array  $wordpressParams
     * @param array  $minkParams
     * @param string $basePath
     */
    public function __construct($wordpressParams, $minkParams, $basePath)
    {
        $this->wordpressParams = $wordpressParams;
        $this->minkParams = $minkParams;
        $this->basePath = $basePath;
    }

    /**
     * setup the wordpress environment / stack if the context is a wordpress context
     *
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof WordPressContext) {
            return;
        }
        $this->prepareEnvironment();
        $this->installFileFixtures();
        $this->flushDatabase();
        $this->loadStack();
    }

    /**
     * prepare environment variables
     */
    private function prepareEnvironment()
    {
        // wordpress uses these superglobal fields everywhere...
        $urlParts = parse_url($this->minkParams['base_url']);
        $_SERVER['HTTP_HOST']       = $urlParts['host'] . (isset($urlParts['port']) ? ':' . $urlParts['port'] : '');
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

        // we don't have a request uri in headless scenarios:
        // wordpress will try to "fix" php_self variable based on the request uri, if not present
        $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

        //Fake mail directory
        if (! defined('WORDPRESS_FAKE_MAIL_DIR')) {
            define('WORDPRESS_FAKE_MAIL_DIR', $this->wordpressParams['mail']['directory']);
        }
    }

    /**
     * actually load the wordpress stack
     */
    private function loadStack()
    {
        // prevent wordpress from calling home to api.wordpress.org
        if (!defined('WP_INSTALLING') || !WP_INSTALLING) {
            define('WP_INSTALLING', true);
        }

        $this->installMuPlugins();

        //TODO: Find a better way: read the entire string
        $mu_plugin = $this->getMuPluginDir();
        $str = file_get_contents($mu_plugin . DIRECTORY_SEPARATOR . 'wp-mail.php');
        $str = str_replace('WORDPRESS_FAKE_MAIL_DIR', "'" . WORDPRESS_FAKE_MAIL_DIR . "'", $str);
        file_put_contents($mu_plugin . DIRECTORY_SEPARATOR . 'wp-mail.php', $str);

        $this->loadWordPress();
    }

    protected function installMuPlugins()
    {
        $finder = new Finder();
        $finder->files()->in(__DIR__.'/mu-plugins')->depth('== 0');

        foreach ($finder as $muPluginFile) {
            $this->installMuPlugin($muPluginFile);
        }
    }

    protected function installMuPlugin($path)
    {
        $mu_plugin = $this->getMuPluginDir();
        $this->copyIfNotExists($path, $mu_plugin . DIRECTORY_SEPARATOR . basename($path));
    }

    protected function getMuPluginDir()
    {
        // load our wp_mail mu-plugin
        $mu_plugin = implode(DIRECTORY_SEPARATOR, array(
            rtrim($this->wordpressParams['path'], DIRECTORY_SEPARATOR),
            'wp-content',
            'mu-plugins'
        ));

        if (!is_dir($mu_plugin)) {
            mkdir($mu_plugin, 0777, true);
        }
        return $mu_plugin;
    }

    protected function copyIfNotExists($source, $dest)
    {
        if (!file_exists($dest)) {
            copy($source, $dest);
        }
    }

    protected function loadWordPress()
    {
        // load the wordpress "stack"
        $finder = new Finder();
        $finder->files()->in($this->wordpressParams['path'])->depth('== 0')->name('wp-load.php');

        foreach ($finder as $bootstrapFile) {
            require_once $bootstrapFile->getRealpath();
        }
    }

    /**
     * create a wp-config.php and link plugins / themes
     */
    public function installFileFixtures()
    {
        $finder = new Finder();
        $fs = new Filesystem();
        $finder->files()->in($this->wordpressParams['path'])->depth('== 0')->name('wp-config-sample.php');
        foreach ($finder as $file) {
            $configContent =
                str_replace(array(
                    "'DB_NAME', 'database_name_here'",
                    "'DB_USER', 'username_here'",
                    "'DB_PASSWORD', 'password_here'"
                ), array(
                    sprintf("'DB_NAME', '%s'", $this->wordpressParams['connection']['db']),
                    sprintf("'DB_USER', '%s'", $this->wordpressParams['connection']['username']),
                    sprintf("'DB_PASSWORD', '%s'", $this->wordpressParams['connection']['password']),
                ), $file->getContents());
            $fs->dumpFile($file->getPath() . '/wp-config.php', $configContent);
        }
    }

    /**
     * flush the database if specified by flush_database parameter
     */
    public function flushDatabase()
    {
        if ($this->wordpressParams['flush_database']) {
            $connection = $this->wordpressParams['connection'];
            $database   = $connection['db'];
            $mysqli = new \Mysqli(
                'localhost',
                $connection['username'],
                $connection['password'],
                $database
            );

            $mysqli->multi_query("DROP DATABASE IF EXISTS $database; CREATE DATABASE $database;");
        }
    }
}
