<?php
namespace StephenHarris\WordPressBehatExtension\Context\Plugins;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

class WordPressPluginContext implements Context
{

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
            $plugin = $row["plugin"];
            if ($row["status"] == "enabled") {
                $this->iActivateThePlugin($plugin);
                $this->thePluginIsActivated($plugin);
            } else {
                $this->iDeactivateThePlugin($row["plugin"]);
                $this->thePluginIsDeactivated($plugin);
            }
        }
    }

    /**
     * Example:
     * The plugin "my-plugin/my-plugin.php" is uninstallable
     *
     * @Given The plugin :plugin is uninstallable
     */
    public function thePluginIsUninstallable($plugin)
    {
        if (!is_uninstallable_plugin($plugin)) {
            throw new \Exception(sprintf('The plugin "%s" is not uninstallable', $plugin));
        }
    }

    /**
     * Example:
     * The plugin "my-plugin/my-plugin.php" is not uninstallable
     *
     * @Given The plugin :plugin is not uninstallable
     */
    public function thePluginIsNotUninstallable($plugin)
    {
        if (is_uninstallable_plugin($plugin)) {
            throw new \Exception(sprintf('The plugin "%s" is uninstallable', $plugin));
        }
    }

    /**
     * Example:
     * I uninstall the plugin "my-plugin/my-plugin.php"
     *
     * @Given I uninstall the plugin :plugin
     */
    public function iUninstallThePlugin($plugin)
    {
        $this->thePluginIsUninstallable($plugin);
        uninstall_plugin($plugin);
    }

    /**
     * Example:
     * I activate the plugin "my-plugin/my-plugin.php"
     *
     * @Given I activate the plugin :plugin
     */
    public function iActivateThePlugin($plugin)
    {
        activate_plugin($plugin);
    }

    /**
     * Example:
     * The plugin "my-plugin/plugin" is activated
     *
     * @Given The plugin :plugin is activated
     */
    public function thePluginIsActivated($plugin)
    {
        if (!is_plugin_active($plugin)) {
            throw new \Exception(sprintf('The plugin "%s" is not activated.', $plugin));
        }
    }

    /**
     * Example:
     * I deactivate the plugin "my-plugin/my-plugin.php"
     *
     * @Given I deactivate the plugin :plugin
     */
    public function iDeactivateThePlugin($plugin)
    {
        deactivate_plugins($plugin);
    }

    /**
     * Example:
     * The plugin "my-plugin/plugin" is deactivated
     *
     * @Given The plugin :plugin is deactivated
     */
    public function thePluginIsDeactivated($plugin)
    {
        if (is_plugin_active($plugin)) {
            throw new \Exception(sprintf('The plugin "%s" is not deactivated.', $plugin));
        }
    }
}
