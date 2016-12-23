versioning-bundle
=================

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d6d73376-b826-46d0-85f5-fd9f77c45c06/mini.png)](https://insight.sensiolabs.com/projects/d6d73376-b826-46d0-85f5-fd9f77c45c06)
[![Total Downloads](https://img.shields.io/packagist/dt/shivas/versioning-bundle.svg?style=flat)](https://packagist.org/packages/shivas/versioning-bundle)

Simple way to version your Symfony2 application.

What it is:
-

- Adds additional parameter to your parameters.yml file and keeps it inline with your current application version.
- Basic Version handlers implemented for manual and *git tag* versioning
- Easy to extend with new handlers for different SCM's or needs
- Uses Semantic Versioning 2.0.0 recommendations using https://github.com/herrera-io/php-version library
- Uses Symfony console component to create command, can be easily integrated with Capifony to update version on every deployment

Purpose:
-

To have parameter in your Symfony2 application with current version of application for various needs:
- Display in frontend
- Display in backend
- Anything you can come up with

Handlers implemented:
-

- ParameterHandler (to manage version manually using app:version:bump command)
- GitRepositoryHandler (git tag describe hangler to automaticly update version looking at git tags)
- InitialVersionHandler (just returns default initial version 0.1.0)

Install
-

run composer.phar update
```
php composer.phar require shivas/versioning-bundle
```

Add bundle to your AppKernel
```
new Shivas\VersioningBundle\ShivasVersioningBundle()
```

run in console:
```
# This will display of available handlers for version bumping
./app/console app:version:bump -l
```

```
# to see dry-run
./app/console app:version:bump -d
```

Default configuration
-

Default configuration of bundle looks like this:
```
./app/console config:dump ShivasVersioningBundle
Default configuration for "ShivasVersioningBundle"
shivas_versioning:
    version_parameter:    application_version
    version_file:         parameters.yml

```

That means in the parameters file the `application_version` variable will be created and updated on every bump of version, you can change the name to anything you want by writing that in your config.yml file.
You may also specify a file other than `parameters.yml` if you would like to use a custom file.  If so, make sure to import it in your config.yml file - you may want to use `ignore_errors` on the import
to avoid issues if the file does not yet exist.

```yaml
    # app/config/config.yml
    imports:
        - { resource: sem_var.yml, ignore_errors: true }

    shivas_versioning:
        version_file:  sem_var.yml
```

Git Handler
-

Git handler works only when you have atleast one TAG in your repository, and all TAGS used for versioning should follow SemVer 2.0.0 notation
with exception, that git handler allows letter "v" or "V" to be used in your tags, e.g. v1.0.0

Application version from git tag are extracted in following fashion:

- v or V is stripped away
- if commit sha matches last tag sha then tag is converted to version as is
- if commit sha differs from last tag sha then following happens:
  - tag is parsed as version
  - prerelease part of SemVer is added with following data: "-dev.abcdefa"
  - where prerelease part "dev" means that version is not tagged and is "dev" stable, and last part is commit sha

This is default behavior and can be easily changed anyway you want (later on that)

Displaying version
-

To display version for example in page title you can add following to your config.yml:
```yaml
twig:
    globals:
        app_version: v%application_version%
```

And then, in your Twig layout display it as global variable:
```html
<title>{{ app_version }}</title>
```

Alternatively, if you want to display version automatically without having to bump it first, set `config.yml` to :
```yaml
twig:
    globals:
        shivas_manager: '@shivas_versioning.manager'
```

And then, in your Twig layout:
```html
<title>{{ shivas_manager.version }}</title>
```

The downside is the app version will be computed every time a twig layout is loaded, even if the variable is not used in the template. However, it could be useful if you have rapid succession of new versions or if you fear to forget a version bump.

Adding own handlers
-

It's easy, write a class that implements HandlerInterface:
```php
namespace Acme\AcmeBundle\Handler;

use Shivas\VersioningBundle\Handler\HandlerInterface;

class MyCustomHandler implements HandlerInterface
{

}
```

For example, lets modify how git handler adds prerelease data to version if tag is outdated, instead just add "build" part with git commit sha. We gonna reuse big part of git handler and just override method for metadata handling:
```php
<?php
namespace Acme\AcmeBundle\Handler;

use Herrera\Version\Builder;
use Shivas\VersioningBundle\Handler\GitRepositoryHandler;

class MyCustomHandler extends GitRepositoryHandler
{
    protected function handleMetaData(Builder $builder, $matches)
    {
        if (intval($matches[7]) != 0) {
            // just add commit sha to build part
            $build = array_merge($builder->getBuild(), array($matches[8]));
            $builder->setBuild($build);
        }
    }
}
```

Add handler to container using your services file (xml in my case):
```xml
        <service id="mycustom_git_handler" class="Acme\AcmeBundle\Handler\MyCustomHandler">
            <argument>%kernel.root_dir%</argument>
            <tag name="shivas_versioning.handler" alias="my_own_git" priority="20" />
        </service>
```

Take a note on priority attribute, it should be more than 0 if you want to override default git handler as it's default value is 0.

Run in console
```
./app/console app:version:bump -l
```

And notice your new handler is above old one:
```
Registered Version handlers
 ============ ========== ===================================== ===========
  Alias        Priority   Name                                  Supported
 ============ ========== ===================================== ===========
  my_own_git   20         Git tag describe handler              Yes
  git          0          Git tag describe handler              Yes
  parameter    -50        parameters.yml file version handler   Yes
  init         -100       Initial version (0.1.0) handler       Yes
 ============ ========== ===================================== ===========
```

So, next time you execute version bump, your custom git handler will take care for your version building.


Make Composer bump your version on install
-

Add script handler 

```
Shivas\\VersioningBundle\\Composer\\ScriptHandler::bumpVersion
```

to your composer.json file to invoke it on post-install-cmd. Make sure it is above clearCache, it may look like this:

```json
"scripts": {
    "post-install-cmd": [
        "Incenteev\\ParameterHandler\\ScriptHandler::buildParameters",
        "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap",
        "Shivas\\VersioningBundle\\Composer\\ScriptHandler::bumpVersion",
        "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache",
        "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
        "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installRequirementsFile",
        "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::prepareDeploymentTarget"
    ],
    "post-update-cmd": [
        "Incenteev\\ParameterHandler\\ScriptHandler::buildParameters",
        "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::buildBootstrap",
        "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::clearCache",
        "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installAssets",
        "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::installRequirementsFile",
        "Sensio\\Bundle\\DistributionBundle\\Composer\\ScriptHandler::prepareDeploymentTarget"
    ]
},
```


Capifony task for version bump
-

Add following to your recipe
```ruby
  namespace :version do
    desc "Updates version using app:version:bump symfony command"
    task :bump, :roles => :app, :except => { :no_release => true } do
      capifony_pretty_print "--> Bumping version"
      run "#{try_sudo} sh -c 'cd #{latest_release} && #{php_bin} #{symfony_console} app:version:bump #{console_options}'"
      capifony_puts_ok
    end
  end

# bump version before cache is created
before "symfony:assets:install", "version:bump"
after "version:bump", "symfony:cache:clear"
```

Good luck versioning your project.

Contributions for different SCM's and etc are welcome, use pull request.
