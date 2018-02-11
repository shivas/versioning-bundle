versioning-bundle
=================

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/d6d73376-b826-46d0-85f5-fd9f77c45c06/mini.png)](https://insight.sensiolabs.com/projects/d6d73376-b826-46d0-85f5-fd9f77c45c06)
[![Total Downloads](https://img.shields.io/packagist/dt/shivas/versioning-bundle.svg?style=flat)](https://packagist.org/packages/shivas/versioning-bundle)
[![Build Status](https://travis-ci.org/shivas/versioning-bundle.svg?branch=2.0.0-alpha)](https://travis-ci.org/shivas/versioning-bundle)

Simple way to version your Symfony Flex application.

What it is:
-

- Adds an additional environment variable and keeps it inline with your current application version
- Adds a global Twig variable for easy access
- Basic Version providers implemented for manual and *git tag* versioning
- Easy to extend with new providers for different SCM's or needs
- Uses Semantic Versioning 2.0.0 recommendations using https://github.com/nikolaposa/version library
- Uses Symfony console command to bump the version on every deployment

Purpose:
-

To have an environment variable in your Symfony application with the current version of the application for various needs:
- Display in frontend
- Display in backend
- Anything you can come up with

Providers implemented:
-

- GitRepositoryProvider (git tag describe provider to automatically update the version by looking at git tags)
- RevisionProvider (read the version from a REVISION file)
- InitialVersionProvider (just returns the default initial version 0.1.0)

Installation
-

Symfony Flex automates the installation, just require the bundle in your application!
```
composer require shivas/versioning-bundle
```

The version is automatically updated with Composer and available in your application.
```
# PHP
getenv('SHIVAS_APP_VERSION')

# Twig
{{ shivas_app_version }}
```

Alternatively, if you want to display the version automatically without having to bump it first, set the versioning manager as a Twig global.
However this is not recommended.
```yaml
twig:
    globals:
        shivas_manager: '@Shivas\VersioningBundle\Service\VersionManager'
```

And then, in your Twig layout:
```
# Twig
{{ shivas_manager.version }}
```

Console commands
-

There are two available console commands. The app:version:bump command is automatically called by Composer on every install and update.
```
# This will display all available version providers
bin/console app:version:list-providers

# Display a dry run of a version bup
bin/console app:version:bump -d
```

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

Add the provider to the container using your services file:
```xml
<service id="App\Provider\MyCustomProvider">
    <tag name="shivas_versioning.provider" alias="my_provider" priority="25" />
</service>
```

Please take a look at the priority attribute, it should be more than 0 if you want to override the default GitRepositoryProvider as it's default value is 0.

Ensure your provider is loaded correctly:
```
bin/console app:version:list-providers

 ============= ========== ================================== ===========
  Alias         Priority   Name                               Supported
 ============= ========== ================================== ===========
  my_provider   25         My custom provider                 Yes
  git           0          Git tag describe provider          Yes
  revision      -25        REVISION file provider             No
  init          -50        Initial version (0.1.0) provider   Yes
 ============= ========== ================================== ===========
```

The next time you bump the version, your custom git provider will provide the version string.

Version formatters
-

Version formatters are used to modify the version string to make it more readable. The default GitDescribeFormatter works in the following fashion:

- if the commit sha matches the last tag sha then the tag is converted to the version as is
- if the commit sha differs from the last tag sha then the following happens:
  - the tag is parsed as the version
  - the prerelease part is added with following data: "dev.abcdefa"
  - where the prerelease part "dev" means that the version is not tagged and is "dev" stable, and the last part is the commit sha

If you want to use the version string as given by the provider, alias the FormatterInterface with the VersionFormatter class.
```yaml
# app/config/services.yaml
Shivas\VersioningBundle\Formatter\FormatterInterface: '@Shivas\VersioningBundle\Formatter\VersionFormatter'
```

Creating your own version formatter
-

To customize the version format, write a class that implements the FormatterInterface:
```php

namespace App\Formatter;

use Shivas\VersioningBundle\Formatter\FormatterInterface;

class MyCustomFormatter implements FormatterInterface
{

}
```

Then alias the FormatterInterface with your own:
```yaml
# app/config/services.yaml
Shivas\VersioningBundle\Formatter\FormatterInterface: '@App\Formatter\MyCustomFormatter'
```

Capistrano v3 task for creating a REVISION file
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
```

Good luck versioning your project.

Contributions for different SCM's and etc are welcome, just submit a pull request.
