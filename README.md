[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/lafourchette/FixturesBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/lafourchette/FixturesBundle/?branch=master) [![Code Coverage](https://scrutinizer-ci.com/g/lafourchette/FixturesBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/lafourchette/FixturesBundle/?branch=master) [![Build Status](https://scrutinizer-ci.com/g/lafourchette/FixturesBundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/lafourchette/FixturesBundle/build-status/master) [![Build Status](https://travis-ci.org/lafourchette/FixturesBundle.svg?branch=fix%2Fphpcs)](https://travis-ci.org/lafourchette/FixturesBundle)
#FixturesBundle

Simplify usage of fixtures into your code / tests / dev.

It's a symfony extension for nelmio/alice library.

Sample, load some fixtures written in yml ( and it's **dependencies** ) : 
```php
    $fixturesLoaderRegistry = $this->container->get('fixtures.loader.registry');
    $fixturesLoader = $fixturesLoaderRegistry->getLoader('default');
	
    $fixturesLoader->load(['@MyBundle/Resources/fixtures/myfixture']);
```

## Require 

* symfony
* doctrine ( dbal, orm, fixtures-bundle)
* nelmio/alice 

**All your fixtures must be written with nelmio/alice format (yml format)**


## Installation 

Add ```"lafourchette/fixtures-bundle"```to your composer file.

```json
"require-dev": {
    "lafourchette/fixtures-bundle": "dev-master"
}
```

And add ```FixturesBundle```to your ```AppKernel.php``` file.
```php
if ($this->getEnvironment() != 'prod') {
    $bundles[] = new LaFourchette\FixturesBundle\FixturesBundle();
}
```

## Configuration

Every ```EntityManager``` has to be setup to load corresponding fixtures.

Sample ```fixtures.yml``` :
```yml
fixtures:
    entityManagerName: #use same name than the entity manager in doctrine
        groups:
	        # you can declare a named group of fixtures
            groupName:
                - @MyBundle/Resources/fixtures/myfixture
                - @MyBundle/Resources/fixtures/myfixture2

        dependencies:
            # specify fixture dependencies, for every fixture
            @MyBundle/Resources/fixtures/fixture1
                - @MyBundle/Resources/fixtures/fixtureRequiredByFixture1
            
        providerClasses:
	        # You can specify which providers you want to use with the loader
            - MyBundle\Fixture\DefaultValuesProvider

        fixturesDataProcessor: la_fourchette.fixtures.data_processor
        # data-processor must be a service declared in the app
```


### Provider classes 

Not required, but you can specify the provider to be used for every loader, in the config file.
See configuration format and nelmio/alice documentation section about providers : https://github.com/nelmio/alice/blob/master/doc/customizing-data-generation.md#add-a-custom-faker-provider-class

### Data Processor

You can use a data processor for your fixtures.
See configuration format and documentation of data processors here : 
https://github.com/nelmio/alice/blob/master/doc/processors.md

## Usage

### Get the loader of entityManager "default" 

To load fixtures, you have to get the corresponding "**loader**" of your *entityManager*.

```php
	$fixturesLoaderRegistry = $this->container->get('fixtures.loader.registry');
	$fixturesLoaderDefault = $fixturesLoaderRegistry->getLoader('default'););
```

#### Warning

Loader by default automaticaly purges all you database entities ( a TRUNCATE is executed ! )
You can avoid these by passing the second argument to `load` method : 

```php
    public function load(array $fixtures, $purge = true) {}
```


### Load a simple fixture file : src/MyBundle/Resources/fixtures/fixture.yml

```php
    $fixturesLoaderDefault->load(['@MyBundle/Resources/fixtures/fixture']);
```

### Load all required fixture of fixture file : src/MyBundle/Resources/fixtures/fixture.yml

For example, you file : **fixture.yml** required an other fixture file : **fixtureRequired.yml**. 
You can load **fixtureRequired.yml** and after **fixture.yml** obviously.

But loader system include a dependency system. 
You can declare a dependency in your config file like this : 
```yml
dependencies:
    # indicate fixture dependencies, for each fixture
    @MyBundle/Resources/fixtures/fixture
        - @MyBundle/Resources/fixtures/fixtureRequired
```

And now if you do : 

```php
    $fixturesLoaderDefault->load(['@MyBundle/Resources/fixtures/fixture']);
```

Then the loader load **fixtureRequired.yml** and after **fixture.yml**. 

### Load a group of fixtures

In addition of declarating fixtures dependencies, you can also define a naming group of fixtures in your config file.

```yml
 groups:
  # you can declare a named group of fixtures
     groupName:
         - @MyBundle/Resources/fixtures/myfixture
         - @MyBundle/Resources/fixtures/myfixture2
```
And to use it in the code, simply do : 
```php
    $fixturesLoaderDefault->load(['@Group:groupName']);
```

**Note** : the **dependencies** are still in function in groups, so if dependencies are declared for *myfixture2* or *myfixture*, they will be loaded in the correct order. And you can also **required a group in a dependency**.

### Advanced : use different SETs of fixtures 

In some cases groups are not enough, you may want to load fixtures according to a *variable*. 
We call it a **SET** of fixtures. 

A **Set** is simply a group of fixtures created for working together, but with the same dependencies and same groups than all your fixtures. 
A set is not declared in the configuration, you just have to organise your fixtures files into **the same directory**. 

Example, defining two sets : mySet1 and mySet2
```
src/MyBundle/Resources/fixtures/mySet1/fixture.yml
src/MyBundle/Resources/fixtures/mySet1/fixture1.yml
src/MyBundle/Resources/fixtures/mySet1/fixtureRequiredBy1.yml

src/MyBundle/Resources/fixtures/mySet2/fixture.yml
src/MyBundle/Resources/fixtures/mySet2/fixture1.yml
src/MyBundle/Resources/fixtures/mySet2/fixtureRequiredBy1.yml
```

Now if you want to load src/MyBundle/Resources/fixtures/mySet1/fixture.yml, you can do : 
```php
    $fixturesLoaderDefault->load(['@MyBundle/Resources/fixtures/{set}/fixture:mySet1']);
```

The fixture path syntax (if using set) is: 
*path* **:** *setName*

The **{set}** is automatically replaced by the required set. 

The power of set system is that those syntaxes also work in the config file so you can have : 

```yml
dependencies:
    # indicate fixture dependencies, for each fixture
    @MyBundle/Resources/fixtures/{set}/fixture
        - @MyBundle/Resources/fixtures/{set}/fixtureRequired
        - @MyBundle/Resources/fixtures/common/fixtureCommonOfAllSet
```

And now, you have declared dependencies for all your sets, and you can have common fixtures for all sets.

## Tools

### CommandLine 

**Note** : not emplemented yet

You can use our command line tools to load fixtures.

```bash
app/console lafourchette:fixtures:load defaultEm @MyBundle/Resources/fixtures/{set}/fixture:default 
```

### Behat context tool

**Note** : not emplemented yet

The bundle provides a behat context to use fixtures loader system.

Add it to your behat config file : behat.yml
```yml
default:
  suites:
    default:
      contexts:
        - LaFourchette\Behat\Context\FixturesContext:
            - "@fixtures.loader.registry"
```

Now you have new sentences available and it's really easy to load required fixtures for every scenario. (to avoid scenario dependencies ...)

```gherkin
Feature: My feature

    Background:
        Given I have data in "default" database with:
            |@Group:login:default|
            |@MyBundle/Resources/fixtures/{set}/fixture:default|
        ...

    Scenario: I do something with my fixture
        ...

	Scenario: I re-do something and all fixtures are re-initialized
        ...
```

Available sentences :

```gherkin

# Load fixture sentence
Given I have data in "default" database with:
"""
   |@Group:login:default|
   |@MyBundle/Resources/fixtures/{set}/fixture:default|
"""
...

# Purge sentence
Given I purge data in "default" database
...

```
