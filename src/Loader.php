<?php

namespace MoceanAPI_WC;

use MoceanAPI_WC\Forms\Handlers\ContactForm7;
use MoceanAPI_WC\Migrations\MigrateSendSMSPlugin;
use MoceanAPI_WC\Migrations\MigrateWoocommercePlugin;

class Loader {

    public static function load()
    {
        new ContactForm7();

        // load Migrations
        MigrateWoocommercePlugin::migrate();
        MigrateSendSMSPlugin::migrate();
    }
}
