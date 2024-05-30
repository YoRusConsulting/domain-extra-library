# YoRus Consulting domain extra library

This package provides additional features to the domain layer of a Symfony application:
- Validation constraints
- Faker providers
    - Uuid
- Doctrine types for specific data types
  - jsonb
- Repository interfaces to manage the data access layer
- Console commands to generate DDD/CQRS classes


## Installation

```
composer require yorus/domain-extra-library
```

Add a configuration file to your project to define the services and parameters needed by the library.

```yaml
# config/packages/yorus_domain_extra_library.yaml

yorus_domain_extra_library:
  namespace: YourNamespace\
```

This configuration will be used to generate the classes (DDD/CQRS generator) with the correct namespace.

## Usage

### Validation constraints

The validation constraints are used to validate the data received by the controllers.
The constraints are configurable which allows them to be used in different domains.
We distinguish 2 types of constraints:

* property constraint
* class constraint

#### Property constraint
It is used to validate a single property of the request.

#### Contrainte de classe
It needs several properties of the request to do its validation work.
This type of constraint will - most of the time - be placed within a validation group, 
and this group will be played first.
Indeed, if the global validation fails, it is not useful to validate each of the properties.

Some constraints require the implementation of a specific interface at the level of the _repository_ used.
Configuration is to be placed in the `config\services.yaml` file of the project, or any other configuration file of the application.

For example:
```yaml
app.constraint.validator.category:
        class: \YoRus\DomainExtraLibrary\Infra\Validator\Constraint\ResourceMustExistConstraintValidator
        arguments:
            - '@App\Infra\Repository\DoctrineORM\CategoryReader'
        tags:
            - { name: validator.constraint_validator, alias: app.constraint.category }

app.constraint.validator.formation:
  class: \YoRus\DomainExtraLibrary\Infra\Validator\Constraint\ResourceMustExistConstraintValidator
  arguments:
    - '@App\Infra\Repository\DoctrineORM\FormationReader'
  tags:
    - { name: validator.constraint_validator, alias: app.constraint.formation }
```
The code above is used to make available the `ResourceMustExistConstraint` constraint for the `Category` resource.
It will be used in the validation file of the command / query.
It uses the `CategoryReader` service to check if the category exists in the database.

Constraints are used in the yaml file of the command / query validator.
For example:
```yaml
App\Command\Formation\UpdateFormationCommand:
  group_sequence:
    - UpdateFormationCommand
    - resource
  properties:
    id:
      - NotNull: ~
      - Uuid: ~
      - \YoRus\DomainExtraLibrary\Infra\Validator\Constraint\ResourceMustExistConstraint:
          reader: app.constraint.formation
          resource: Formation
          groups: [resource]
    payload:
      - Collection:
          fields:
            label:
              - NotNull: ~
              - NotBlank: ~
              - Length:
                  min: 3
            description:
              - NotNull: ~
              - NotBlank: ~
              - Length:
                  min: 10
            category:
              - NotNull: ~
              - Uuid: ~
              - \YoRus\DomainExtraLibrary\Infra\Validator\Constraint\ResourceMustExistConstraint:
                  reader: app.constraint.category
                  resource: Category
                  groups: [resource]
          allowExtraFields: false

```

See the official documentation for more details: https://symfony.com/doc/current/validation/custom_constraint.html

#### Available constraints
##### ResourceMustExistConstraint
Check that the resource represented by the property on which the constraint is applied actually exists in the database.

* Constraint type: property
* Request type: create, retrieve, update, delete

Parameters:

* _reader_: the id of the service representing the validator linked to the constraint
* _resource_: the name of the current resource
* _code_: the HTTP code returned in case the constraint is not validated

Configuration example:

```yaml
app.constraint.validator.stock:
        class: \YoRus\DomainExtraLibrary\Infra\Validator\Constraint\ResourceMustExistConstraintValidator
        arguments:
            - '@App\Stock\Infra\Repository\DoctrineORM\StockReader'
        tags:
            - { name: validator.constraint_validator, alias: app.constraint.stock }
```

Usage example:

```
- YoRus\DomainExtraLibrary\Infra\Validator\Constraint\ResourceMustExistConstraint:
	reader: app.constraint.stock
	resource: Stock
	code: 400
	groups: [Resource]
```

### Faker providers

The library provides a Faker provider to generate UUIDs, useful for generating test data, 
especially when creating fixtures.

```yaml
App\Domain\Entity\User:
    user.john_doe:
        __construct: [ <uuidObject('69e4234a-b3c8-4322-aa7f-24164913269a')>, 'John', 'Doe' ]
```

### Doctrine types

The library provides a Doctrine type for the `jsonb` data type.

### Repository interfaces

The library provides interfaces to manage the data access layer.
These interfaces are used to define the methods that the repository must implement.

#### RequiredFinderRepositoryInterface

This interface propose a `findRequired` method to find an entity by its identifier.
Unlike the `find` method, it suggests to throws an `EntityNotFoundException` exception if the entity is not found.

### Console commands

The library provides console commands to generate DDD/CQRS classes.
The generated classes are the following:
- Controller
- Entity
- Repository
- Query
- QueryHandler

To use the generator, run the following command:

```bash
php bin/console yorus:ddd-cqrs:generate
```

To obtain help on the command, run:

```bash
php bin/console yorus:ddd-cqrs:generate --help
```

The command accepts the following options:
- `--context`: the context in which classes must be generated. Usefull to separate classes by domain 
or to manage some bounded contexts.
- --dry-run: to simulate the generation of classes without writing them to the disk. 
It will display the generated files in the console.

The command will ask you to enter the following information:
- The name of the domain to manage
- The list of parameters to manage in the entity

The parameters are the following syntax:
```
name:type
name*:type
name:?type
```

The `name` is the name of the parameter.
The `type` is the type of the parameter.
The `*` indicates that the parameter will be managed as a primary key in the entity.
The `?` indicates that the parameter is optional

For example:
```
id*:uuid
name:string
description:string
```

If no type is provided, the parameter will be managed as a string.
