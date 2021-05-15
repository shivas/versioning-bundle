versioning-bundle
=================

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d6d73376-b826-46d0-85f5-fd9f77c45c06/mini.png)](https://insight.sensiolabs.com/projects/d6d73376-b826-46d0-85f5-fd9f77c45c06)
[![Total Downloads](https://img.shields.io/packagist/dt/shivas/versioning-bundle.svg?style=flat)](https://packagist.org/packages/shivas/versioning-bundle)
[![Build Status](https://travis-ci.org/shivas/versioning-bundle.svg?branch=master)](https://travis-ci.org/shivas/versioning-bundle)

Simple way to version your Symfony Flex application.

What it is:
-

- Automatically keep track of your application version using Git tags or a Capistrano REVISION file
- Adds a global Twig variable for easy access
- Easy to extend with new version providers and formatters for different SCM's or needs
- Uses Semantic Versioning 2.0.0 recommendations using https://github.com/nikolaposa/version library
- Support for manual version management

Purpose:
-

To have an environment variable in your Symfony application with the current version of the application for various needs:
- Display in frontend
- Display in backend
- Anything you can come up with

Providers implemented:
-

- `VersionProvider` (read the version from a VERSION file)
- `GitRepositoryProvider` (git tag describe provider to automatically update the version by looking at git tags)
- `RevisionProvider` (read the version from a REVISION file)
- `InitialVersionProvider` (just returns the default initial version 0.1.0)

Installation
-

Symfony Flex automates the installation process, just require the bundle in your application!
```console
composer require shivas/versioning-bundle
```

The version is automatically available in your application.
```
# Twig template
{{ shivas_app_version }}

# Or get the version from the service
public function indexAction(VersionManagerInterface $manager)
{
    $version = $manager->getVersion();
}
```

Console commands
-

There are three available console commands. You only need to run the app:version:bump command when manually managing your version number.
```console
# Display the application version status
bin/console app:version:status

# Display all available version providers
bin/console app:version:list-providers

# Manually bump the application version
bin/console app:version:bump
```

Version providers
-

Providers are used to get a version string for your application. All versions should follow the SemVer 2.0.0 notation, with the exception that letter "v" or "V" may be prefixed, e.g. v1.0.0.
The recommended version provider is the `GitRepositoryProvider` which only works when you have at least one TAG in your repository. Be sure that all of your TAGS are valid version numbers.

Adding own provider
-

It's easy, write a class that implements the `ProviderInterface`:
```php
namespace App\Provider;

use Shivas\VersioningBundle\Provider\ProviderInterface;

class MyCustomProvider implements ProviderInterface
{

}
```

Add the provider to the container using your services file:
```yaml
App\Provider\MyCustomProvider:
    tags:
        - { name: shivas_versioning.provider, alias: my_provider, priority: 0 }
```

```xml
<service id="App\Provider\MyCustomProvider">
    <tag name="shivas_versioning.provider" alias="my_provider" priority="0" />
</service>
```

Please take a look at the priority attribute, it should be between 0 and 99 to keep the providers in the right order.

Ensure your provider is loaded correctly and supported:
```console
bin/console app:version:list-providers

Registered version providers
 ============= ========================================================= ========== ===========
  Alias         Class                                                     Priority   Supported
 ============= ========================================================= ========== ===========
  version       Shivas\VersioningBundle\Provider\VersionProvider          100        No
  my_provider   App\Provider\MyCustomProvider                             0          Yes
  git           Shivas\VersioningBundle\Provider\GitRepositoryProvider    -25        Yes
  revision      Shivas\VersioningBundle\Provider\RevisionProvider         -50        No
  init          Shivas\VersioningBundle\Provider\InitialVersionProvider   -75        Yes
 ============= ========================================================= ========== ===========
```

Version formatters
-

Version formatters are used to modify the version string to make it more readable. The default `GitDescribeFormatter` works in the following fashion:

- if the commit sha matches the last tag sha then the tag is converted to the version as is
- if the commit sha differs from the last tag sha then the following happens:
  - the tag is parsed as the version
  - the prerelease part is added with following data: "dev.abcdefa"
  - where the prerelease part "dev" means that the version is not tagged and is "dev" stable, and the last part is the commit sha

If you want to disable the default formatter, use the `NullFormatter`:
```yaml
# app/config/services.yaml
Shivas\VersioningBundle\Formatter\NullFormatter: ~
Shivas\VersioningBundle\Formatter\FormatterInterface: '@Shivas\VersioningBundle\Formatter\NullFormatter'
```

Creating your own version formatter
-

To customize the version format, write a class that implements the `FormatterInterface`:
```php
namespace App\Formatter;

use Shivas\VersioningBundle\Formatter\FormatterInterface;

class MyCustomFormatter implements FormatterInterface
{

}
```

Then alias the `FormatterInterface` with your own:
```yaml
# app/config/services.yaml
Shivas\VersioningBundle\Formatter\FormatterInterface: '@App\Formatter\MyCustomFormatter'
```

Capistrano v3 task for creating a REVISION file
-

Add following to your recipe
```ruby
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
```

Good luck versioning your project.

Contributions for different SCM's and etc are welcome, just submit a pull request.
