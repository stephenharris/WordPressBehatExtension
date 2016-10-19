## WordPress Extension for Behat 3

This is a Behat 3.0 Extension for WordPress plugin and theme development. 

The Extension allows you to use WordPress functions in your context class if you include `StephenHarris\WordPressBehatExtension\Context\WordPressContext` (or create and include a child class of it, i.e. make your `FeatureContext` ).

It also provides [other contexts](docs/Contexts.md).

**Version:** 0.3.0  


## History

This repository started off as a fork of:

 - <https://github.com/JohnBillion/WordPressBehatExtension>
 - itself a fork of <https://github.com/tmf/WordPressExtension>
 - itself a fork of <https://github.com/wdalmut/WordPressExtension>


## Installation

1. Add a composer development requirement for your WordPress theme or plugin:

    ```json
    {
        "repositories": [
            {
                "type": "vcs",
                "url": "https://github.com/stephenharris/WordPressBehatExtension.git"
            }
        ],
        "require-dev" : {
            "StephenHarris/wordpress-behat-extension": "~0.1",
            "johnpbloch/wordpress": "~4.5.2"
        }
    }
    ```
    You don't *have* to install WordPress via composer. But you shall need a path to a WordPress install below.

2. Add the following Behat configuration file below. You should need:

 - path to your WordPress install (here assumed `vendor/wordpress`, relative to your project's root directory.
 - The database, and database username and password of your WordPress install (here assumed `wordress_test`, `root`, `''`)
 - The URL of your WordPress install (In this example we'll be using php's build in server)
 - A temporary directory to store e-mails that are 'sent'


    ```yml
    default:
      suites:
        default:
          contexts:
            - FeatureContext
            - WordPressContext
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
    
3. Install the vendors and initialize behat test suites

    ```bash
    composer update
    # You will need to ensure a WordPress install is available, with database credentials that
    # mach the configuration file above
    vendor/bin/behat --init
    ```

4. Write some Behat features and test them

    ```
    Feature: Manage plugins
        In order to manage plugins
        As an admin
        I need to enable and disable plugins
    
        Background:
            Given I have a vanilla wordpress installation
                | name          | email                   | username | password |
                | BDD WordPress | your@email.com          | admin    | test     |
            And I am logged in as "admin" with password "test"
    
        Scenario: Enable the dolly plugin
            Given there are plugins
                | plugin    | status  |
                | hello.php | enabled |
            When I go to "/wp-admin/"
            Then I should see a "#dolly" element
    
        Scenario: Disable the dolly plugin
            Given there are plugins
                | plugin    | status   |
                | hello.php | disabled |
            When I go to "/wp-admin/"
            Then I should not see a "#dolly" element
    
    ```

5. Run the tests


 > In our example, since we using PHP's built-in web sever, this will need to be started so that  Behat can access our site. 

 > ```bash
    php -S localhost:8000 -t vendor/wordpress -d disable_functions=mail
    ```

    ```bash
    vendor/bin/behat
    ```

## Aim

The aim of this project is to provide a collection of context classes that allow for easy testing of WordPress' core functionality. Those contexts can then be built upon to test your site/plugin/theme-specific functionality. 


## Health Warning

This is not to be used on a live site. Your WordPress tables **will** be cleared of all data. 

Currently this extension also over-rides your `wp-config.php` but this implementation may change in the future.


## Changelog

A changelog can be found at [CHANGELOG.md](./CHANGELOG.md).


## How to help

This project needs a lot of love. The contexts could do we some improved structuring and refactoring to keep things DRYer. It also lacks a consistant coding standard. 

You can help by

 - Opening an issue to request a context
 - Submitting a PR to add a context
 - Just using this extension in your development / testing workflow
