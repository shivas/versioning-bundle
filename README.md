versioning-bundle
=================

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d6d73376-b826-46d0-85f5-fd9f77c45c06/mini.png)](https://insight.sensiolabs.com/projects/d6d73376-b826-46d0-85f5-fd9f77c45c06)
[![Total Downloads](https://img.shields.io/packagist/dt/shivas/versioning-bundle.svg?style=flat)](https://packagist.org/packages/shivas/versioning-bundle)
[![Build Status](https://travis-ci.org/shivas/versioning-bundle.svg?branch=2.0.0-alpha)](https://travis-ci.org/shivas/versioning-bundle)

Simple way to version your Symfony application.

What it is:
-

- Adds additional parameter to your parameters.yaml file and keeps it inline with your current application version.
- Basic Version providers implemented for manual and *git tag* versioning
- Easy to extend with new providers for different SCM's or needs
- Uses Semantic Versioning 2.0.0 recommendations using https://github.com/nikolaposa/version library
- Uses Symfony console component to bump the version, can be easily integrated with Capifony to update on every deployment

Purpose:
-

To have a parameter in your Symfony application with the current version of the application for various needs:
- Display in frontend
- Display in backend
- Anything you can come up with

Providers implemented:
-

- GitRepositoryProvider (git tag describe provider to automatically update the version by looking at git tags)
- RevisionProvider (get the version from a REVISION file)
- ParameterProvider (to manage the version manually using the app:version:bump command)
- InitialVersionProvider (just returns the default initial version 0.1.0)

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
# This will display all available version providers
./bin/console app:version:bump -l
```

```
# to see dry-run
./bin/console app:version:bump -d
```

Default configuration
-

Default configuration of bundle looks like this:
```
./bin/console config:dump ShivasVersioningBundle
# Default configuration for "ShivasVersioningBundle"
shivas_versioning:
    version_parameter:    application_version
    version_file:         parameters.yaml
    version_formatter:    shivas_versioning.formatters.git
```

That means in the parameters file the `application_version` variable will be created and updated on every version bump, you can change the name to anything you want by writing that in your config.yaml file.
You may also specify a file other than `parameters.yaml` if you would like to use a custom file.  If so, make sure to import it in your config.yaml file - you may want to use `ignore_errors` on the import
to avoid issues if the file does not yet exist.

```yaml
    # app/config/config.yaml
    imports:
        - { resource: sem_var.yaml, ignore_errors: true }

    shivas_versioning:
        version_file:  sem_var.yaml
```

The default version formatter is `shivas_versioning.formatters.git`. This formatter shows the version from the Git tag and adds dev.commithash as a prerelease when not on the tag commit. If you want you can disable this formatter or create your own.

```yaml
    # app/config/config.yaml
    shivas_versioning:
        version_formatter: ~
```

Displaying version
-

To display the version in the page title for example, you can add the following to your config.yaml:
```yaml
twig:
    globals:
        app_version: v%application_version%
```

And then, in your Twig layout display it with the global variable:
```html
    <title>{{ app_version }}</title>
```

Alternatively, if you want to display the version automatically without having to bump it first, set `config.yaml` to :
```yaml
twig:
    globals:
        shivas_manager: '@shivas_versioning.manager'
```

And then, in your Twig layout:
```html
    <title>{{ shivas_manager.version }}</title>
```

The downside is that the version will be computed every time a twig layout is loaded, even if the variable is not used in the template. However, it could be useful if you have rapid succession of new versions or if you are afraid to forget to bump the version.

Version providers
-

Providers are used to get a version string for your application. All versions should follow the SemVer 2.0.0 notation, with the exception that letter "v" or "V" may be prefixed, e.g. v1.0.0.
The default provider is the GitRepositoryProvider which only works when you have atleast one TAG in your repository. Be sure that all of your TAGS are valid version numbers.

Adding own provider
-

It's easy, write a class that implements the ProviderInterface:
```php

namespace Acme\AcmeBundle\Provider;

use Shivas\VersioningBundle\Provider\ProviderInterface;

class MyCustomProvider implements ProviderInterface
{

}
```

Add the provider to the container using your services file (xml in my case):
```xml
    <service id="mycustom_git_provider" class="Acme\AcmeBundle\Provider\MyCustomProvider">
        <argument>%kernel.root_dir%</argument>
        <tag name="shivas_versioning.provider" alias="my_own_git" priority="20" />
    </service>
```

Take a note on the priority attribute, it should be more than 0 if you want to override the default GitRepositoryProvider as it's default value is 0.

Run in console
```
./bin/console app:version:bump -l
```

And notice your new provider is above old one:
```
Registered Version providers
 ============ ========== ====================================== ===========
  Alias        Priority   Name                                  Supported
 ============ ========== ====================================== ===========
  my_own_git   20         Git tag describe provider              Yes
  git          0          Git tag describe provider              Yes
  revision     -25        REVISION file provider                 Yes
  parameter    -50        parameters.yaml file version provider  Yes
  init         -100       Initial version (0.1.0) provider       Yes
 ============ ========== ====================================== ===========
```

So, the next time you execute a version bump, your custom git provider will provide the version string.

Version formatters
-

Version formatters are used to modify the version string to make it more readable. The default GitDescribeFormatter works in the following fashion:

- if the commit sha matches the last tag sha then the tag is converted to the version as is
- if the commit sha differs from the last tag sha then the following happens:
  - the tag is parsed as the version
  - the prerelease part is added with following data: "dev.abcdefa"
  - where the prerelease part "dev" means that the version is not tagged and is "dev" stable, and the last part is the commit sha

Creating your own version formatter
-

To customize the version format, write a class that implements the FormatterInterface:
```php

namespace Acme\AcmeBundle\Formatter;

use Shivas\VersioningBundle\Formatter\FormatterInterface;

class MyCustomFormatter implements FormatterInterface
{

}
```

Add the formatter to the container using your services file (xml in my case):
```xml
    <service id="mycustom_git_formatter" class="Acme\AcmeBundle\Formatter\MyCustomFormatter" />
```

Finally register your own formatter in the configuration.
```yaml
    # app/config/config.yaml
    shivas_versioning:
        version_formatter: mycustom_git_formatter
```

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

Capistrano v3 task for version bump
-

Add following to your recipe
``` ruby
namespace :deploy do
    task :add_revision_file do
        on roles(:app) do
            within repo_path do
                execute(:git, :'describe', :"--tags --long",
                :"#{fetch(:branch)}", ">#{release_path}/REVISION")
            end
        end
    end
end

# We get git describe --tags just after deploy:updating
after 'deploy:updating', 'deploy:add_revision_file'

namespace :version do
    desc "Updates version using app:version:bump symfony command"
    task :bump do
        invoke 'symfony:console', 'app:version:bump'
    end
end

# After deploy bump version
after 'deploy:finishing', 'version:bump'
```

Good luck versioning your project.

Contributions for different SCM's and etc are welcome, use pull request.
