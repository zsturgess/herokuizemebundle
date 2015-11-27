# Installing HerokuizeMeBundle

## Step 1: Download HerokuizeMeBundle with composer

Require the bundle as a dev dependency with composer: 

`$ composer require zsturgess/herokuizemebundle --dev`

## Step 2: Enable the bundle

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

## Step 3: Run the herokuize:me command

The bundle provides a single command, `herokuize:me` that can check a Symfony project for problems that might stop it from running on heroku:

`$ php app/console herokuize:me`

You can pass `--auto-fix` to have the bundle attempt to fix these problems automatically for you:

`$ php app/console herokuize:me --auto-fix`

**Warning:** The `--auto-fix` mode of the command can be destructive. It is highly recommended that you commit the state of your working copy before running the command in this mode so that, should the auto fixers cause more problems than they solve, the changes they made can be easily reset.

## Next Steps

If you're not sure what to do once you've run through the installation, take a look at our [next steps](nextsteps.md) for help on getting up and running on Heroku.