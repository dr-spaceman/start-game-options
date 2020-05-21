# Videogamesite

A place where videogame fans can [read](http://videogamin.squarehaven.com/content/Special:featured "featured Videogam.incyclopedia content") and [contribute](http://videogamin.squarehaven.com/content/Special:new "create a new Videogam.incyclopedia article") information about [games](http://videogamin.squarehaven.com/games) and the [people](http://videogamin.squarehaven.com/people) who make them. It's a place to follow, review, collect, and show off your favorite (and least favorite) games.

A site where serious gamers can [discuss](http://videogamin.squarehaven.com/forums "Videogam.in Message Forums of Death!!!"), [peruse](http://videogamin.squarehaven.com/posts/ "Videogam.in Sblog: videogame news & blogs"), and [write](http://videogamin.squarehaven.com/posts/manage.php?action=newpost "Create a new Sblog post") about [gaming topics](http://videogamin.squarehaven.com/posts/topics/ "Videogame topics") in an unserious manner. Its edge lies in its unique ability to speak vapidly about otherwise serious issues, take videogames only as earnestly as its own college drunkenness and inability to graduate within four years, and ultimately: the vapid community of vulgar buffoonery that lurk within its dank confines.

## Built With

* PHP 7
* Javascript and jQuery

## Launch

Dependencies listed in [composer.json](composer.json); Set up your environment: `composer i`

## Tests

Tests can be run with [PHPUnit](https://phpunit.de/) within the `tests` folder. A database facsimile on your local machine is required for most tests.

## Contributing

Contributions welcome!

## Authors

* **Matt Berti** - *Programmer, Designer* - [Dr Spaceman](https://github.com/dr-spaceman)
* **Alex Williams** - *Cofounder, Content creator*
* **Rahul Choudhury** - *Cofunder, Development advisor* - [Primigenus](https://github.com/Primigenus)
* Your name here

## Acknowledgments

* Dictionary data provided by [CC-CEDICT](http://cc-cedict.org/)

## Todo

* Back end
    * [ ] Convert mysqli to pdo
    * [ ] Classes to manage db tables and app functions
        * [x] Mapper
        * [x] Identity Map
        * [x] Storage/Registry for persistent sharing objects, eg. PDO connection
        * [x] Badge
        * [ ] Game Shelf
        * [ ] Image
        * [ ] Page construction/layout
        * [ ] Sblog
        * [x] Upload
        * [x] User
    * [ ] Refactor and simplify publications system to only include basic information and physical box art
    * [ ] Build a temporary or alternate shelves for rating/image capturing and sharing
    * [ ] Sblog reporposed/reengineered as games journal/guide/tips 
* Front end
    * [ ] Responsive CSS
