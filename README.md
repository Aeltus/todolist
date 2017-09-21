ToDoList
========

Base du projet #8 : Améliorez un projet existant

https://openclassrooms.com/projects/ameliorer-un-projet-existant-1

##Installing

1. clone this repository (master branch)
2. put it into your server root folder
3. into command line install vendors by using composer (https://getcomposer.org/download/)
- use this command in prompt : composer install
4. set de configuration into :
- /app/config/parameters.yml.dist and rename it in /app/config/parameters.yml

5. also in command line set the database by using this commands
- php bin/console doctrine:database:create
- php bin/console doctrine:schema:update --force
- php bin/console doctrine:fixtures:load

6. put assets in rights folders by using (in prompt)
- php bin/console assets/install
