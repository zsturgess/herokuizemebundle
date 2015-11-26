# HerokuizeMeBundle

HerokuizeMeBundle is a quick and easy way to get a Symfony project up and running on Heroku.

HerokuizeMeBundle is a creation of Zac Sturgess. See also the [list of contributors](https://github.com/zsturgess/herokuizemebundle/graphs/contributors).

**Warning:** HerokuizeMeBundle is still in the process of being developed, and is unstable. You have been warned.

## Installation

Installation is as easy as installing a bundle and running a single command.

### Step 1: Download HerokuizeMeBundle with composer

Require the bundle as a dev dependency with composer: 

`$ composer require zsturgess/herokuizemebundle --dev`

### Step 2: Enable the bundle

Enable the bundle in the dev section of the Symfony kernel:

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
            // ...
            $bundles[] = new ZacSturgess\HerokuizeMeBundle\ZacSturgessHerokuizeMeBundle();
            // ...
    }

### Step 3: Run the herokuize:me command

The bundle provides a single command, `herokuize:me` that can check a Symfony project for problems that might stop it from running on heroku:

`$ php app/console herokuize:me`

You can pass `--auto-fix` to have the bundle attempt to fix these problems automatically for you:

`$ php app/console herokuize:me --auto-fix`

**Warning:** The `--auto-fix` mode of the command can be destructive. It is highly recommended that you commit the state of your working copy before running the command in this mode so that, should the auto fixers cause more problems than they solve, the changes they made can be easily reset.

### Next Steps

TODO: Explain about pushing to Heroku, env var configuration, getting logs via adddons like papertrail, etc.

## License

This project is under the MIT license. See the [complete license](LICENSE):

    LICENSE


## Reporting an issue or a feature request

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/zsturgess/herokuizemebundle/issues).