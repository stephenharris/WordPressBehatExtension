<?php
namespace Johnbillion\WordPressExtension\Context;

use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode,
	Behat\Behat\Hook\Scope\BeforeScenarioScope;

use Behat\MinkExtension\Context\MinkContext;

/**
 * Class WordPressContext
 *
 * @package Johnbillion\WordPressExtension\Context
 */
class WordPressContext extends MinkContext
{
	protected $current_user;

	protected $wordpressParams = null;

	protected $blogs;

	function setWordPressParameters( $args ) {
		$this->wordpressParams = $args;
	}

	function getWordPressParameters( $args ) {
		return $this->wordpressParams;
	}

	/**
	 * @BeforeScenario
	 */
	public function flushDatabase(BeforeScenarioScope $scope)
	{
		$connection = $this->wordpressParams['connection'];
		$mysqli = new \Mysqli(
			'localhost',//$connection['host'],
			$connection['username'],
			$connection['password'],
			$connection['db']
		);

		$mysqli->multi_query("DROP DATABASE IF EXISTS ${connection['db']}; CREATE DATABASE ${connection['db']};");

		$mysqli->multi_query(implode("\n", array(
			"DROP DATABASE IF EXISTS " . $connection['db'] . ";",
			"CREATE DATABASE " . $connection['db'] . ";",
		)));

		if ( $this->wordpressParams['multisite']['dbdump'] ) {
			$command = "mysql -u{$connection['username']} -p{$connection['password']} "
				. "-h localhost -D {$connection['db']} < {$this->wordpressParams['multisite']['dbdump']}";
			$out = shell_exec($command);
		}

		wp_cache_flush();
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
     * Add these posts to this wordpress installation
     *
     * @see wp_insert_post
     *
     * @Given /^there are posts$/
     */
    public function thereArePosts(TableNode $table)
    {
        foreach ($table->getHash() as $postData) {
            if (!is_int(wp_insert_post($postData))) {
                throw new \InvalidArgumentException("Invalid post information schema.");
            }
        }
    }


    /**
     * @Given there are :taxonomy terms
     */
	public function thereAreTerms($taxonomy, TableNode $terms)
	{
		foreach ($terms->getHash() as $termData) {
			$return = wp_insert_term($termData['name'],$taxonomy,$termData);
			if (is_wp_error($return)) {
				throw new \InvalidArgumentException(sprintf("Invalid taxonomy term information schema: %s", $return->get_error_message()));
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
		//require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        foreach ($table->getHash() as $row) {
            if ($row["status"] == "enabled") {
                activate_plugin( $row["plugin"] );
            } else {
                deactivate_plugins( $row["plugin"] );
            }
        }
    }

    /**
     * @Given I set :option option to :value
     */
    public function iSetOptionTo($option, $value)
    {
    	update_option( $option, $value );
    }

    /**
     * Login into the reserved area of this wordpress
     *
     * @Given /^I am logged in as "([^"]*)" with password "([^"]*)"$/
     */
    public function login($username, $password)
    {

    	$this->getSession()->reset();
        $this->visit("wp-login.php");
        $currentPage = $this->getSession()->getPage();

		$this->spin(function($context) use ($currentPage, $username, $password) {
			$currentPage->fillField('user_login', $username);
        	$currentPage->fillField('user_pass', $password);
			$context->checkOption('rememberme');
        	$currentPage->findButton('wp-submit')->click();
        	return true;
        });

        // Assert that we are on the dashboard
        assertTrue(
            $this->spin(function($context) use ( $username ) {
    			$context->getSession()->getPage()->hasContent('Dashboard');
				$this->current_user = get_user_by( 'login', $username );
    			return true;
	    	})
        );

    }


	/**
	 * @Given /^the ([a-zA-z_-]+) "([^"]*)" has ([a-zA-z_-]+) terms ((?:[^,]+)(?:,\s*([^,]+))*)$/i
	 */
	public function thePostTypeHasTerms( $post_type, $title, $taxonomy, $terms )
	{
		$post = get_page_by_title( $title, OBJECT, $post_type );
		if ( ! $post ) {
			throw new \Exception( sprintf( 'Post "%s" of post type %s not found', $title, $post_type ) );
		}

		$names = array_map( 'trim', explode( ',', $terms ) );
		$terms = array();
		foreach( $names as $name ){
			$term = get_term_by( 'name', htmlspecialchars( $name ), $taxonomy );
			if ( ! $term ) {
				throw new \Exception( sprintf( 'Could not find "%s" term %s', $taxonomy, $name ) );
			}
			$terms[] = $term->slug;
		}
		$term_ids = wp_set_object_terms( $post->ID, $terms, $taxonomy, false );

		if ( ! $term_ids ) {
			throw new \Exception( sprintf( 'Could not set the %s terms of post "%s"', $taxonomy, $title ) );
		} else if ( is_wp_error( $term_ids ) ) {
			throw new \Exception( sprintf( 'Could not set the %s terms of post "%s": %s', $taxonomy, $title, $terms->get_error_message() ) );
		}
	}


    /**
     * @Then /^the ([a-z0-9_\-]*) "([^"]*)" should have ([a-z0-9_\-]*) terms "([^"]*)"$/
     */
    public function thePostTypeShouldHaveTerms( $post_type, $title, $taxonomy, $terms )
    {
    	$post = get_page_by_title( $title, OBJECT, $post_type );
    	if ( ! $post ) {
    		throw new \InvalidArgumentException( sprintf( 'Post "%s" of post type %s not found', $title, $post_type ) );
    	}
    	clean_post_cache( $post->ID );
    	$actual_terms = get_the_terms( $post->ID, $taxonomy );

    	if ( ! $actual_terms ) {
    		throw new \InvalidArgumentException( sprintf( 'Could not get the %s terms of post "%s"', $taxonomy, $title ) );
    	} else if ( is_wp_error( $terms ) ) {
    		throw new \InvalidArgumentException( sprintf( 'Could not get the %s terms of post "%s": %s', $taxonomy, $title, $terms->get_error_message() ) );
    	}

    	$actual_slugs   = wp_list_pluck( $actual_terms, 'slug' );
    	$expected_slugs = array_map( 'trim', explode( ',', $terms ) );

    	$does_not_have   = array_diff( $expected_slugs, $actual_slugs );
    	$should_not_have = array_diff( $actual_slugs, $expected_slugs );

    	if ( $does_not_have || $should_not_have ) {
    		throw new \Exception(
    			sprintf(
    				'Failed asserting "%s" has the %s terms: "%s"' . "\n" . "Actual terms: %s",
    				$title,
    				$taxonomy,
    				implode( ',', $expected_slugs ),
    				implode( ',', $actual_slugs )
    			)
    		);
    	}
    }

    /**
     * @Then /^the ([a-z0-9_\-]*) "([^"]*)" should have status "([^"]*)"$/
     */
    public function thePostTypeShouldHaveStatus( $post_type, $title, $status )
    {
    	$post = get_page_by_title( $title, OBJECT, $post_type );
    	if ( ! $post ) {
    		throw new \Exception( sprintf( 'Post "%s" of post type %s not found', $title, $post_type ) );
    	}

    	clean_post_cache( $post->ID );
    	$actual_status = get_post_status( $post->ID );

		assertEquals( $status, $actual_status );
    }

    /**
     * Fills in form field with specified id|name|label|value.
     *
     * @overide When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with "(?P<value>(?:[^"]|\\")*)"$/
     * @overide When /^(?:|I )fill in "(?P<field>(?:[^"]|\\")*)" with:$/
     * @overide When /^(?:|I )fill in "(?P<value>(?:[^"]|\\")*)" for "(?P<field>(?:[^"]|\\")*)"$/
     */
    public function fillField($field, $value)
    {
    	$field = $this->fixStepArgument($field);
    	$value = $this->fixStepArgument($value);

    	$this->spin(function($context) use ($field, $value) {
    		$context->getSession()->getPage()->fillField($field, $value);
    		return true;
    	});
    }

    public function spin ($lambda, $wait = 60)
    {
    	for ($i = 0; $i < $wait; $i++)
    	{
    		try {
    			if ($lambda($this)) {
    				return true;
    			}
    		} catch (Exception $e) {
		    	// do nothing
    		}

    		sleep(1);
    	}

    	$backtrace = debug_backtrace();

    	throw new Exception(
	    	"Timeout thrown by " . $backtrace[1]['class'] . "::" . $backtrace[1]['function'] . "()\n"
    	);
    }

}
