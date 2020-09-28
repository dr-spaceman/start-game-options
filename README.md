# Videogamesite

A place where videogame fans can follow, review, collect, and show off their favorite games.

A site where serious gamers can discuss gaming topics in an unserious manner.

A website about videogames.

## Built With

* PHP 7
* Javascript and React 16
* Webpack
* CSS 3 and Sass

## Launch

Dependencies listed in [composer.json](composer.json) and [package.json](package.json); Set up your environment: `composer i && npm install`

## Tests

Unit tests are located in the `tests` folder and can be run with [PHPUnit](https://phpunit.de/): `composer test`

## API

REST API endpoint is located at [/api](/api).

### Documentation

API documentation is compiled from doctrine annotation within the API controller files. To compile: `composer compile-apidoc`

## Authors

* **Matt Berti** - *Programmer, Designer* - [Dr Spaceman](https://github.com/dr-spaceman)
* **Alex Williams** - *Cofounder, Content creator*
* **Rahul Choudhury** - *Cofunder, Development advisor* - [Primigenus](https://github.com/Primigenus)
* Your name here

### Contributing

Contributions welcome! Contact Matt.

## Todo

* Major business
    * [ ] Game collection app
        * [ ] Refactor and simplify publications system to only include basic information and physical box art
        * [ ] Build a temporary or alternate shelves for rating/image capturing and sharing
    * [ ] Reporpose and reengineer SBLOG as games journal/guide/tips
    * [ ] Message forums
* Back end business
    * [x] Convert mysqli to pdo
    * [ ] Classes
        * [x] Mapper
        * [x] Identity Map
        * [x] Storage/Registry for persistent sharing objects, eg. PDO connection
        * [x] Badge
        * [ ] Game Shelf
        * [x] Image
        * [ ] Page construction/layout
        * [ ] Sblog
        * [x] Upload
        * [x] User
* Front end business
    * [x] Responsive CSS
    * [x] Incorporate React and package UI components with Webpack
    * [x] Rebrand site as "Start Game Options"
