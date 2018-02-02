This is an ORM for Wordpress plugins, 
it works by using WP's own DB connection.
This ORM is inspired by Doctrine 
and makes it easier/fun to develop small plugins.

## Why would you make an ORM for wordpress plugins?
Because I can. 
And I don't like to type my own query's, 
so I made an ORM to do that for me.

## Would you recommend using this?
Only for experimental uses. 
It lacks any form of Unit testing and has only be used by me.

#### Usage:
- Entity's should extend \Dorans\Competition\Entity\Base\AbstractEntity
- Repo's should extend \Dorans\Competition\Repository\BaseEntityRepository
- Services should extend \Dorans\Competition\Service\BaseEntityService
- Want to add your own annotations? Take a look at: \Dorans\Competition\Util\Helper\RelationMetaDataHelper

You can load the classes easily via the ClassLoader.

```php
<?php
error_reporting(E_ALL);

require('library/ClassLoader.php');
try {
    \Dorans\Competition\ClassLoader::loadClasses();
} catch (\Exception $e) {
    echo '<pre>' . $e . '</pre>';
}
```

Repo's expect the WPDB global connection and the entity class that will be managed, as constructor args.
```php
<?php
global $wpdb;

$repository = new BaseEntityRepository($wpdb, Competition::class);
```

Services are therefore the recommended way to use this ORM, because these initiate the repo's for you.
```php
<?php
$entityService = new TeamEntityService();
```

#### TODO's:
Are located in the source code as comments.  
disclaimer: I probably won't do any of these.