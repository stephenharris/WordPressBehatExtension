<?php
namespace StephenHarris\WordPressBehatExtension\Context;

use Behat\Gherkin\Node\TableNode;

class WordPressPluginsContext implements Context
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
            if ($row["status"] == "enabled") {
                activate_plugin($row["plugin"]);
            } else {
                deactivate_plugins($row["plugin"]);
            }
        }
    }
}
