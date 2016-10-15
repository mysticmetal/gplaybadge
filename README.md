# gplaybadge ![](https://img.shields.io/codeship/4b5860b0-7521-0134-3653-1eaf12e437c5.svg) [![Code Climate](https://img.shields.io/codeclimate/github/maxcanna/gplaybadge.svg)](https://codeclimate.com/github/maxcanna/gplaybadge) [![](https://img.shields.io/github/license/maxcanna/gplaybadge.svg)](https://github.com/maxcanna/gplaybadge/blob/master/LICENSE)

Easily create a badge to promote your own android application in a single step. [Demo](http://gplay.ws).

## How do I get set up?

You can easily create an Heroku application:

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

Or manually:

* Install composer via ``` curl -sS https://getcomposer.org/installer | php ```
* Install project dependencies via ```php composer.phar install```
* Test locally via ``` php -S 0.0.0.0:3000 -t web/ ```
* Deploy wherever you like

In either case remember to set these two env vars:
* `MASHAPE_KEY` to your [GPlay API key](https://api.gplay.ws/)
* `UA` to your [Google Analytics Tracking ID](https://api.gplay.ws/)
