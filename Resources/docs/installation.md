# Installing HerokuizeMeBundle

## Step 1: Download HerokuizeMeBundle with composer

Require the bundle as a dependency with composer: 

`$ composer require zsturgess/herokuizemebundle`

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

The bundle provides a single command, `herokuize:me` that can be used to complete the rest of the setup:

`$ php app/console herokuize:me`

The command will install the deploy hook that will auto-detect when the project is being pushed to heroku and make some configuration changes in order to make it run smoothly on production, as well as run an initial sense-check for implicit, undeclared dependencies on PHP extensions.

## Step 4: Commit any changes and push to Heroku

The `herokuize:me` command may prompt you to commit changes to your project's `composer.json` and `composer.lock` files. If so, commit these before making any other changes.

Finally, your project should be ready to push to Heroku. When Heroku builds your app, HeokuizeMeBundle will make extra changes to the Symfony configuration of your project that only make sense on Heroku (for example: redirecting Symfony logs to event streams)