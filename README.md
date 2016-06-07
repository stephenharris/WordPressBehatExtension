## WordPress Extension for Behat 3

This is a Behat 3.0 Extension for WordPress plugin and theme development. 
You can use it to test your WordPress installation, or just test your plugin/theme without installing them in a normal WordPress installation (i.e. stand-alone).

The Extension allows you to use WordPress functions in your context class (if you extend your `FeatureContext` from `StephenHarris\WordPressBehat\Context\WordPressContext`).

It also provides other contexts:

 - `StephenHarris\WordPressBehat\Context\WordPressAdminContext.php` - navigating the WordPress admin
 - `StephenHarris\WordPressBehat\Context\WordPressPostListContext.php` - default WordPress admin post type page 

**Version:** 0.1.0  


## History

This repository started off as a fork of:

 - <https://github.com/StephenHarris/WordPressBehatExtension>
 - itself a fork of <https://github.com/tmf/WordPressExtension>
 - itself a fork of <https://github.com/wdalmut/WordPressExtension>


## Installation

1. Add a composer development requirement for your WordPress theme or plugin:

    ```json
    {
        "require-dev" : {
            "StephenHarris/wordpress-behat-extension": "~0.1",
            "johnpbloch/wordpress": "~4.5.2"
        }
    }
    ```
    You don't *have* to install WordPress via composer. But you shall need a path to a WordPress install below.

2. Add the following Behat configuration file:

    ```yml
    default:
      suites:
        default:
          contexts:
            - FeatureContext:
                screenshot_dir: '%paths.base%/failed-scenerios/'
            - WordPressAdminContext
            - WordPressPostListContext
      extensions:
        StephenHarris\WordPressBehat:
          path: '%paths.base%/vendor/wordpress'
          connection:
            db: 'wordpress_test'
            username: 'root'
            password: ''
        Behat\MinkExtension:
          base_url:    'http://localhost:8000'
          goutte: ~
          selenium2: ~
    ```
    changing the directories as appropriate. The **screenshot_dir** will store screenshots of any failed tests. It will also include the mark-up. This helps you review failed tests and debug the issue. 

3. Install the vendors and initialize behat test suites

    ```bash
    composer update
    # You will need to ensure a WordPress install is available, with database credentials that
    # mach the configuratin file above
    vendor/bin/behat --init
    ```

4. Start your development web server and point its document root to the wordpress directory in vendors (without mail function)

    ```bash
    php -S localhost:8000 -t vendor/wordpress -d disable_functions=mail
    ```

5. Write some Behat features and test them

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

6. Run the tests

    ```bash
    vendor/bin/behat
    ```

## Aim

The aim of this project is to provide a collection of context classes that allow for easy testing of WordPress' core functionality. Those contexts can then be built upon to test your site/plugin/theme-specific functionality. 

## Changelog

A changelog can be found at [CHANGELOG.md](./CHANGELOG.md).


## How to help

This project needs a lot of love. The contexts could do we some improved structuring and refactoring to keep things DRYer. It also lacks a consistant coding standard. 

You can help by

 - Opening an issue to request a context
 - Submitting a PR to add a context
 - Just using this extension in your development / testing workflow
