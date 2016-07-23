# gplaybadge ![](https://codeship.com/projects/aae89ac0-af0d-0133-5e4c-6624307c89c5/status?branch=master)

Easily create a badge to promote your own android application in a single step. [Demo](http://gplay.ws)

## How do I get set up?

You can easily create an Heroku application:

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

Or manually:

* Install composer via ``` curl -sS https://getcomposer.org/installer | php ```
* Install project dependencies via ```php composer.phar install```
* Test locally via ``` php -S 0.0.0.0:3000 -t web/ ```
* Deploy wherever you like

In either case remember to set `MASHAPE_KEY` env var to your [GPlay API key](https://api.gplay.ws/)