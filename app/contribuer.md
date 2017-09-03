# Comment contribuer au projet :

## 1 - Préambule

Le projet TodoList est basé sur le framework Symfony dans sa version 3.1. Il est donc fortement recomandé de suivre les 
conventions en vigueur dans le projet Symfony lui même, afin d'assurer la meilleure compatibilité et la meilleure
évolutivité possibles.

Le respect de ces quelques règles permettra en plus, une meilleure compréhension du code par les différents développeurs
qui seront ammenés à contribuer au projet.

## 2 - Respect des standards de codage : Les PSR

Les PSR pour "Php Standard Recomandations" sont une base de travail sur laquelle tous les développeurs PHP devraient se 
baser lors de leurs travaux. Ceci afin d'assurer des standards qui permttrons un meilleure compréhention du code par les
autres développeurs, ainsi qu'une meilleure compatibilté avec les librairies externes.

Vous pouvez consulter la liste des des 17 PSR exitantes sur le site [www.php-fig.org/psr](http://www.php-fig.org/psr/)

Les projets Symfony s'appuient tout particulièrement sur les :
* [PSR-0](http://www.php-fig.org/psr/psr-0/) sur l'autoloading (aujourd'hui Depricated, mais reprisent dans la PSR-4)
* [PSR-1](http://www.php-fig.org/psr/psr-1/) sur les bases des standards de code
* [PSR-2](http://www.php-fig.org/psr/psr-2/) sur le style de code
* [PSR-4](http://www.php-fig.org/psr/psr-4/) sur l'autoloading (reprenant et remplaçant la PSR-0)

Pour en savoir sur les ["coding standards de symfony"](https://symfony.com/doc/3.1/contributing/code/standards.html)

Afin de vérifier facilement si les standards sont respectés, il est conseillé de faire une revue de code grâce à l'outil
gratuit mis à disposition par sensiolab : [sensiolabInsight](https://insight.sensiolabs.com/)

## 3 - Les conventions de code

Il convient aussi de respecter aussi quelques conventions de code supplémentaires mises en place par l'équipe de sensiolab.
Elles portent essentiellement sur le nommage des méthodes et les "déprecation".

Vous pouvez les consulter sur le site se Symfony, à l'adresse suivante : [https://symfony.com/doc/3.1/contributing/code/conventions.html](https://symfony.com/doc/3.1/contributing/code/conventions.html)

L'outil sensiolabInsight cité dans la section 2 du présent document vérifie aussi ces conventions de code.

## 4 - Les tests

Les tests s'opèrent via phpunit.

La façon de tester l'application est détaillée dans la [documentation technique](https://docs.google.com/document/d/1RsZuYsPEiKfg-j880f90ta8r3toFOlF4wvMTPSYsGeY/edit?usp=sharing).

Pour en savoir plus sur phpunit : [https://phpunit.de/](https://phpunit.de/)

Pour en savoir plus phpunit au sein de Symfony [https://symfony.com/doc/3.1/testing.html](https://symfony.com/doc/3.1/testing.html)

## 5 - Les revues de code

1. Clonez le projet présent sur github : [https://github.com/Aeltus/todolist](https://github.com/Aeltus/todolist) branche **master**
2. Créez votre banche de travail, puis apportez les modifications nécessaires au projet sur votre version clonnée.
3. N'oubliez pas de rédiger les tests, ainsi que de les lancer pour vérifier la compatibilité des vos modifications avec le projet.
4. Vérifiez que les standards de code aient été respectés grâce à l'outil sensioLabInsight
5. Créez une pull request sur le dépot github [https://github.com/Aeltus/todolist](https://github.com/Aeltus/todolist)
6. Après verification, la branche sera mergée avec la branche master.
