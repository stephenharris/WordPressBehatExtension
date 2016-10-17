<?php
namespace StephenHarris\WordPressBehatExtension\Context;

use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use StephenHarris\WordPressBehatExtension\WordPress\InboxFactory;

/**
 * Class WordPressContext
 *
 * @package StephenHarris\WordPressBehatExtension\Context
 */
class WordPressContext extends MinkContext
{
    /**
     * Create a new WordPress website from scratch
     *
     * @Given /^\w+ have a vanilla wordpress installation$/
     */
    public function installWordPress(TableNode $table = null)
    {
        global $wp_rewrite;

        $name = "admin";
        $email = "an@example.com";
        $password = "test";
        $username = "admin";

        if ($table) {
            $hash = $table->getHash();
            $row = $hash[0];
            $name = $row["name"];
            $username = $row["username"];
            $email = $row["email"];
            $password = $row["password"];
        }

        $mysqli = new \Mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        $value = $mysqli->multi_query(implode("\n", array(
            "DROP DATABASE IF EXISTS " . DB_NAME . ";",
            "CREATE DATABASE " . DB_NAME . ";",
        )));
        \PHPUnit_Framework_Assert::assertTrue($value);
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        wp_install($name, $username, $email, true, '', $password);

        //This is a bit of a hack, we don't care about the notification e-mails here so clear the inbox
        //we run the risk of deleting stuff we want!
        $factory = InboxFactory::getInstance();
        $inbox   = $factory->getInbox($email);
        $inbox->clearInbox();

        $wp_rewrite->init();
        $wp_rewrite->set_permalink_structure('/%year%/%monthnum%/%day%/%postname%/');
    }

    /**
     * Add these users to this wordpress installation
     *
     * @see wp_insert_user
     *
     * @Given /^there are users$/
     */
    public function thereAreUsers(TableNode $table)
    {
        foreach ($table->getHash() as $userData) {
            if (!is_int(wp_insert_user($userData))) {
                throw new \InvalidArgumentException("Invalid user information schema.");
            }
        }
    }


    /**
     * @Given there are :taxonomy terms
     */
    public function thereAreTerms($taxonomy, TableNode $terms)
    {
        foreach ($terms->getHash() as $termData) {
            $return = wp_insert_term($termData['name'], $taxonomy, $termData);
            if (is_wp_error($return)) {
                throw new \InvalidArgumentException(
                    sprintf("Invalid taxonomy term information schema: %s", $return->get_error_message())
                );
            }
        }
    }

    /**
     * Activate/Deactivate plugins
     * | plugin          | status  |
     * | plugin/name.php | enabled |
     *
     * @Given /^there are plugins$/
     */
    public function thereArePlugins(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            if ($row["status"] == "enabled") {
                activate_plugin($row["plugin"]);
            } else {
                deactivate_plugins($row["plugin"]);
            }
        }
    }

    /**
     * @Given I set :option option to :value
     */
    public function iSetOptionTo($option, $value)
    {
        update_option($option, $value);
    }
}
