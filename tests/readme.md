#Prepare 
* Verify the composer.json file contains the below packages within the require-rev block and scripts

```json
{
  "require-dev": {
    "codeception/lib-innerbrowser": "^1.0",
    "codeception/module-asserts": "^1.1",
    "codeception/module-cli": "^1.0",
    "codeception/module-db": "^1.0",
    "codeception/module-filesystem": "^1.0",
    "codeception/module-phpbrowser": "^1.0",
    "codeception/module-rest": "^1.2",
    "codeception/module-webdriver": "^1.0",
    "codeception/util-universalframework": "^1.0"
  },
  "scripts": {
    "test:acceptance": "@php ./vendor/bin/codecept run acceptance",
    "test:clean": "@php ./vendor/bin/codecept clean",
    "test:coverage": "@php ./vendor/bin/codecept run wpunit --coverage --coverage-xml --coverage-html",
    "test:functional": "@php ./vendor/bin/codecept run functional",
    "test:generate-scenarios": "@php vendor/bin/codecept generate:scenarios",
    "test:integration": "@php ./vendor/bin/codecept run wpunit"
  }
}
```
https://www.oracle.com/java/technologies/downloads/
As Global NPM Package
npm install selenium-standalone -g
selenium-standalone install && selenium-standalone start
As a Local NPM Package
npm install selenium-standalone --save-dev
npx selenium-standalone install && npx selenium-standalone start
* Install `selenium-server` using brew `brew install selenium-server-standalone`
* Download chrome driver from here `https://sites.google.com/chromium.org/driver/`
* Then move the package in bin folder so its become available in your path `mv chromedriver /usr/local/bin`
* Verify everything looks nice `chromedriver --version` & `selenium-server --version`
* run this `./vendor/bin/codecept init wpbrowser` and follow the
  on-screen guideline.



#Setup
* Copy `.env` file to `.env.testing` and modify file as per your setup. 
* Create a database file as you chose in the `.end.testing` file
* Run `./vendor/bin/codecept build`
* Run testing `./vendor/bin/codecept run acceptance`
* Run testing `./vendor/bin/codecept run functional`
* Run testing `./vendor/bin/codecept run unit`
* Run testing `./vendor/bin/codecept run wpuni`

# Levels of testing

**Acceptance tests**
Acceptance and functional tests are very similar, with a distinction that acceptance tests are testing the functionality of the project from the viewpoint of the business user. They are very similar to end to end (E2E) tests, but those are performed from the viewpoint of the QA engineer.

**Functional tests**
The functional tests test the functionality from the perspective of a developer.

Here you could test some custom validation rules, Ajax requests, and similar.

**Integration tests**
These tests will test code modules in the context of a WordPress app.

**Unit tests**
Unit tests always test single classes or functions (units) in isolation.

Say we have a validator class that validates email. We would want to make sure that class works as expected, regardless if it's in the WordPress context or not. Unit tests are where stubbing/mocking/spying of dependencies is used to gain total control over the input and context the class is using.
