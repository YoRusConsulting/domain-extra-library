# Alten domain extra library

## Contraintes de validation

Présentes dans _Alten\DomainExtraLibrary\Infra\Validator\Constraint_, les contraites de validation sont utilisées dans les différents domaines et permettent de valider les données reçues par les contrôleurs. 

Les contraintes sont paramètrables ce qui permet de les utiliser dans différents domaines.
Certains paramètres possèdent des valeurs par défaut, ce qui permet d'alléger leur configuration.

On distingue 2 types de contraintes : 

* contrainte de propriété

Valide une seule propriété de la requête

* contrainte de classe


A besoin de plusieurs propriétés de la requête afin de faire son travail de validation. Ce type de contrainte sera - la plupart du temps - placée au sein d'un groupe de validation, et ce groupe sera joué en premier.
En effet, si la validation globale échoue, il n'est pas utile de valider chacune des propriétés.

Certaines contraintes nécessitent l'implémentation d'interface spécifique au niveau des _repository_ utilisés.

Les config sont à placer dans le fichier `\config\services.yaml` du projet, ou tout autre fichier de configuration de l'appli.

Les contraintes sont utilisées dans le fichier yaml de validateur de command / query.

Voir la doc officielle pour plus de détails : https://symfony.com/doc/current/validation/custom_constraint.html

###ResourceMustExistConstraint
Vérifie que la ressource représentée par la propriété sur laquelle on applique la contrainte existe bien dans la base de données.

Type de contrainte : propriété

Type de requête : create, retrieve, update, delete

Paramètres : 

* reader : 
l'id du service représentant le validateur lié à la contrainte

* resource : 
le nom de la ressource courtante

* code : 
le code HTTP renvoyé en cas de non validation de la contrainte

Exemple de config : 

```
logit.constraint.validator.stock_definition:
        class: \Logit\DomainExtraLibrary\Infra\Validator\Constraint\ResourceMustExistConstraintValidator
        arguments:
            - '@Logit\Stock\Infra\Repository\DoctrineORM\StockDefinitionReader'
        tags:
            - { name: validator.constraint_validator, alias: logit.constraint.stock_definition }
```

Exemple d'utilisation : 

```
- Logit\DomainExtraLibrary\Infra\Validator\Constraint\ResourceMustExistConstraint:
	reader: logit.constraint.stock_definition
	resource: StockDefinition
	code: 400
	groups: [Resource]
```