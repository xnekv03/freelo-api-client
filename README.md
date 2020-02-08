# Unofficial client for Freelo

This library makes it easy to send requests towards [Freelo API](https://freelo.docs.apiary.io)


## Installation

The recommended way to install package is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of package:

```bash
composer require xnekv03/freelo-api-client
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```

You can then later update package using composer:

 ```bash
composer update
 ```

## Usage
### Before you start
#### Create a Freelo account
[Registration](https://app.freelo.cz/registration)
#### Get your API key

Login to your [Dashboard](https://app.freelo.cz/dashboard), go to your Settings and obtain your API key which will be something like
`9lDZU35Lb0wmnq4tWvmmUkugLja4dXwPDcOMP1CBdIa`


### Initialize API Client 

```php
# load autoload file
require_once  'vendor/autoload.php';

# Import Freelo library
use Freelo\Client;

$freeloApiToken = '9lDZU35Lb0wmnq4tWvmmUkugLja4dXwPDcOMP1CBdIa';
$loginEmail = 'john@doe.com';

# Initialize a client
$freeloClient = new Client($freeloApiToken,$loginEmail);
```

### Create project

```php
$projectName = "Project Alice";
$currencyIso = "EUR";  // currently EUR, USD or CZK is supported

$projectId = $freeloClient->createProject($projectName, $currencyIso);
echo $projectId;        // 74201 - project ID is returned
```

### Collection of all own projects including active To-Do lists

```php
$projects = $freeloClient->getAllOwnProjectIncludinglToDo();
var_dump($projects);        // array with all projects including their names, IDs and task lists
```

### Paginated collection of all invited projects

```php
$projects = $freeloClient->getAllInvitedProjects();
var_dump($projects);        // array with all invited projects
```

### Paginated collection of all archived projects
```php
$projects = $freeloClient->getAllArchivededProjects();
var_dump($projects);        // array with all archived projects
```
### Paginated collection of all templated projects
```php
$projects = $freeloClient->getAllTemplateProjects();
var_dump($projects);        // array with all templated projects
```
### Project workers collection
```php
$projectId = 73335;
$projectsWorkers = $freeloClient->allProjectWorkers($projectId);
var_dump($projectsWorkers);        // array with all workers assigned to given project
```
### Create to-do list
```php
$projectId = 73335;     // ID of an existing project
$budget = 10205; // 2 decimal places with no decimal separator, ie. 1.05 = '105'
$listName = 'Pre-launch test';

$projectDetails = $freeloClient->createToDoList($projectId, $budget, $listName);
var_dump($projectDetails);        // array with task details

```
### Find all assignable workers for To-Do list
```php
$projectId = 73335;     // ID of an existing project
$taskId = 179444;     // ID of existing task

$workers = $freeloClient->assignableWorkersCollection($projectId, $taskId);
var_dump($workers);        // array with available workers and their IDs
```

