## What is this?

This is a Behat 3.0 Extension for WordPress plugin and theme development. It provides [step definitions](Contexts.md) and allows you to use WordPress functions in your own context class.

## History

This repository started off as a fork of:

 - <https://github.com/JohnBillion/WordPressBehatExtension>
 - itself a fork of <https://github.com/tmf/WordPressExtension>
 - itself a fork of <https://github.com/wdalmut/WordPressExtension>


## Installation

*(For 'quick start' guides, please see the [Recipes](Recipes.md)).*

1. Add a composer development requirement for your WordPress theme or plugin:

    ```json
    {
        "require-dev" : {
            "stephenharris/wordpress-behat-extension": "~0.3",
            "behat/mink-goutte-driver": "~1.1",
            "behat/mink-selenium2-driver": "~1.3.1",
            "johnpbloch/wordpress": "~4.6.1"
        }
    }
    ```
    You don't *have* to install WordPress via composer. But you shall need a path to a WordPress install below. Additionally you don't *have* to use the Goutte and Selenium2 drivers, but these are the most common.

2. Add the following Behat configuration file below. You will need:

 - The path to your WordPress install (here assumed `vendor/wordpress`, relative to your project's root directory).
 - The database, and database username and password of your WordPress install (here assumed `wordress_test`, `root`, `''`)
 - The URL of your WordPress install (In this example we'll be using php's build in server)
 - A temporary directory to store e-mails that are 'sent'


    ```yaml
    default:
      suites:
        default:
          contexts:
            - FeatureContext
            - \StephenHarris\WordPressBehatExtension\Context\WordPressContext
            - \StephenHarris\WordPressBehatExtension\Context\Plugins\WordPressPluginContext
            # and any other contexts you need, please see the documentation
      extensions:
        StephenHarris\WordPressBehatExtension:
          path: '%paths.base%/vendor/wordpress'
          connection:
            db: 'wordpress_test'
            username: 'root'
            password: ''
          mail:
            directory: '/tmp/mail'
        Behat\MinkExtension:
          base_url:    'http://localhost:8000'
          goutte: ~
          selenium2: ~
    ```
    
    *Note the `StephenHarris\WordPressBehatExtension\Context\WordPressContext` context included. This will cause WordPress to be loaded, and all its functions available in your context classes.*. You can also include [other contexts](Contexts.md).
    
    
3. Install the vendors and initialize behat test suites

    ```bash
    $ composer update
    # You will need to ensure a WordPress install is available, with database credentials that
    # mach the configuration file above
    $ vendor/bin/behat --init
    ```

4. Write some Behat features in your project's `features` directory and define any steps. The `WordPressContext` context will make all WordPress functions available in your context classes (but there is a better way).

       ```gherkin
       Feature: You can read blog posts
        In order to read blogs
        As a user
        I need to go to the blog

        Background:
            Given I have a vanilla wordpress installation
                | name          | email             | username | password |
                | BDD WordPress | admin@example.com | admin    | password |
            And there are posts
                | post_title      | post_content              | post_status | post_author |
                | Just my article | The content of my article | publish     | admin       |
                | My draft        | This is just a draft      | draft       | admin       |

        Scenario: List my blog posts
            Given I am on the homepage
            Then I should see "Just my article"
            And I should not see "My draft"

        Scenario: Read a blog post
            Given I am on the homepage
            When I follow "Just my article"
            Then I should see "Just my article"
            And I should see "The content of my article"    
        ```

5. Run the tests


 > In our example, since we using PHP's built-in web sever, this will need to be started so that  Behat can access our site. 

 > ```bash
    $  php -S localhost:8000 -t vendor/wordpress -d disable_functions=mail
    ```

    ```bash
    $ vendor/bin/behat
    ```

## Aim

The aim of this project is to provide a collection of context classes that allow for easy testing of WordPress' core functionality. Those contexts can then be built upon to test your site/plugin/theme-specific functionality. 

## License

WordPressBehatExtension is open source and released under MIT license. See [LICENSE](https://github.com/stephenharris/WordPressBehatExtension/blob/develop/LICENSE) file for more info.

## Health Warning

This **is not to be used on a live site**. Your database **will** be cleared of all data. 

Currently this extension also over-rides your `wp-config.php` but this implementation may change in the future.

The extension installs three `mu-plugins` into your install (which it assumes is at `{site-path}/wp-content/mu-plugins`). These plug-ins do the following:
 
 - `wp-mail.php` - over-rides `wp_mail()` function to store the e-mails locally
 - `wp-install.php` - over-rides `wp_install_defaults()` to prevent any default content being created, with the exception of the 'Uncategorised' category.
 - `move-admin-bar-to-back.php` - a workaround for [#1](https://github.com/stephenharris/WordPressBehatExtension/issues/1) which prevent elements from being hidden from Selenium behind the admin menu bar.


## Changelog

A changelog can be found at [CHANGELOG.md](https://github.com/stephenharris/WordPressBehatExtension/blob/develop/CHANGELOG%2Emd).