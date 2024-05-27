# YoRus Consulting domain extra library

## Installation

```
composer require yorus/domain-extra-library
```

## Contraintes de validation

Présentes dans _YoRus\DomainExtraLibrary\Infra\Validator\Constraint_, 
les contraites de validation sont utilisées dans les différents domaines 
et permettent de valider les données reçues par les contrôleurs. 

Les contraintes sont paramétrables ce qui permet de les utiliser dans différents domaines.
Certains paramètres possèdent des valeurs par défaut, ce qui permet d'alléger leur configuration.

On distingue 2 types de contraintes : 

* contrainte de propriété
* contrainte de classe

### Contrainte de propriété

Elle est utilisée pour valider une seule et unique propriété de la requête.

### Contrainte de classe

Elle a besoin de plusieurs propriétés de la requête afin de faire son travail de validation. 
Ce type de contrainte sera - la plupart du temps - placée au sein d'un groupe de validation, et ce groupe sera joué en premier.
En effet, si la validation globale échoue, il n'est pas utile de valider chacune des propriétés.

Certaines contraintes nécessitent l'implémentation d'interface spécifique au niveau des _repository_ utilisés.

Les configuration sont à placer dans le fichier `config\services.yaml` du projet, ou tout autre fichier de configuration de l'application.

Les contraintes sont utilisées dans le fichier yaml de validateur des command / query.

Voir la doc officielle pour plus de détails : https://symfony.com/doc/current/validation/custom_constraint.html

### Contraintes disponibles
#### ResourceMustExistConstraint
Vérifie que la ressource représentée par la propriété sur laquelle 
on applique la contrainte existe bien dans la base de données.

* Type de contrainte : propriété
* Type de requête : create, retrieve, update, delete

Paramètres : 

* _reader_ : l'id du service représentant le validateur lié à la contrainte
* _resource_ : le nom de la ressource courtante
* _code_ : le code HTTP renvoyé en cas de non validation de la contrainte

Exemple de configuration : 

```
yorus.constraint.validator.stock:
        class: \YoRus\DomainExtraLibrary\Infra\Validator\Constraint\ResourceMustExistConstraintValidator
        arguments:
            - '@YoRus\Stock\Infra\Repository\DoctrineORM\StockReader'
        tags:
            - { name: validator.constraint_validator, alias: yorus.constraint.stock }
```

Exemple d'utilisation : 

```
- YoRus\DomainExtraLibrary\Infra\Validator\Constraint\ResourceMustExistConstraint:
	reader: yorus.constraint.stock
	resource: Stock
	code: 400
	groups: [Resource]
```