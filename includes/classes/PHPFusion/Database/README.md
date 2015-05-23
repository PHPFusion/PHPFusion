# Object-oriented database layer

## What is it?

The very old database functions are used by many people in their infusions. 
We cannot easily implement some new features without breaking compatibility, however, we need them. 

The old functions still work, but we have classes behind them.
The object-oriented version support more features:

- Multiple database connection at the same time
- Connecting to the database automatically
- Close the connection
- Error handling using exceptions

There are some common new features:

- Parameterized query to escape the unsafe characters automatically
- New debug mode to show all queries
- You can set the character set of custom connections
- Advanced users can write custom database drivers

## Basic examples

### Connecting to the default database

#### Object-oriented style

	use PHPFusion\Database\DatabaseFactory; // At the top of the PHP file
	
	$conn = DatabaseFactory::getConnection(); 

#### Procedural style

	dbconnect($db_host, $db_user, $db_pass, $db_name);
    
### Connecting to multiple databases

#### Object-oriented style
 
You need to create a new file in the PHP-Fusion's root directory named <strong>config.db.php</strong>.
The file must return an array:

	<?php
	return array(
		'custom1' => array(
			'host' => 'host1',
			'database' => 'database1',
			'user' => 'user1',
			'password' => 'password1',
			'driver' => 'pdo_mysql', // Optional. 'pdo_mysql' or 'mysql'. pdo_mysql by default.
			'charset' => 'utf8', // Optional. utf8 by default
			'debug' => FALSE // Optional. FALSE by default
		),
		'custom2' => array(
			'host' => 'host2',
			'database' => 'database2',
			'user' => 'user2',
			'password' => 'password2',
			'driver' => 'pdo_mysql', // Optional. 'pdo_mysql' or 'mysql'. pdo_mysql by default.
			'charset' => 'utf8', // Optional. utf8 by default
			'debug' => FALSE // Optional. FALSE by default
		)
	);

Where you want to connect:

	use PHPFusion\Database\DatabaseFactory; // At the top of the PHP file
	
	$conn = DatabaseFactory::getConnection(); // Configured in config.php
	$custom_conn1 = DatabaseFactory::getConnection('custom1'); 
	$custom_conn2 = DatabaseFactory::getConnection('custom2'); 

#### Procedural style

Not implemented

### Close the connection

It is useful only 

- when you want to start a very long process after getting everything from the database. (You can reconnect anytime)
- when you have many connections and want to close some of them, but it is unlikely.

#### Object-oriented style

	$conn->close()
	$custom_conn1->close();

#### Procedural style

Not implemented

### Check if the connection is alive

#### Object-oriented style

Probably you will not need it since you can call $conn->close()
even if the connection is closed

	if ($conn->isConnected()) {
		// The connection is alive
	}
	
or

	if ($conn->isClosed()) {
		// The connection is closed
	}

#### Procedural style

Not implemented

### Send a query without parameters

#### Object-oriented style

	$conn->query("SQL code");
	$custom_conn1->query("SQL code");
	$custom_conn2->query("SQL code");

#### Procedural style

	dbquery("SQL code"); // Only with default connection
    
### Send a query with parameters

Parameters make the SQL more secure. 
All unsafe characters will be escaped based on the connection. 
You do not need to use apostrophes in the SQL.
 
- All string parameters will be enclosed by apostrophes 
- boolean and NULL values will be converted to string (TRUE, FALSE and NULL) without apostrophes
- All integers and floats will be used in the SQL without apostrophes

#### Object-oriented style

	$result1 = $conn->query('SELECT * FROM '.DB_USERS.' WHERE user_name = :name', array(
		':name' => "It's my name", // It can contain unsafe characters like apostrophe
	));
	
	$result2 = $conn->query('SELECT * FROM '.DB_USERS.' limit :offset, :limit', array(
		':offset' => 0, // Must be an integer! '0' does not work
		':limit' => 10 // Must be an integer! '10' does not work
	));

#### Procedural style

	$result1 = dbquery('SELECT * FROM '.DB_USERS.' WHERE user_name = :name', array(
		':name' => "It's my name", // It can contain unsafe characters like apostrophe
	));
	
	$result2 = dbquery('SELECT * FROM '.DB_USERS.' limit :offset, :limit', array(
		':offset' => 0, // Must be an integer! '0' does not work
		':limit' => 10 // Must be an integer! '10' does not work
	));


### Fetch all records as numeric arrays

#### Object-oriented style

***Long version***

	$users = array();
	while ($user = $conn->fetchRow($result1)) {
		$users[] = $user;
	}

***Short version***

    $users = $conn->fetchAllRows($result1);
    
#### Procedural style
	
***Long version***
	
	$users = array();
	while ($user = dbarraynum($result1)) {
		$users[] = $user;
	}
	
***Short version***

Not implemented
	
### Fetch all records as associative arrays

#### Object-oriented style

***Long version***

	$users = array();
	while ($user = $conn->fetchAssoc($result1)) {
		$users[] = $user;
	}

***Short version***

    $users = $conn->fetchAllAssoc($result1);
    
#### Procedural style
	
***Long version***
	
	$users = array();
	while ($user = dbarray($result1)) {
		$users[] = $user;
	}
	
***Short version***

Not implemented

### Fetch the first column

It is useful when you select only one column

#### Object-oriented style

    $result = $conn->query('SELECT max(user_id) FROM '.DB_USERS);
    $max = $conn->fetchFirstColumn($result);
        
#### Procedural style
 
    $result = dbquery('SELECT max(user_id) FROM '.DB_USERS);
    $max = dbresult($result);
    
### Count the selected rows

#### Object-oriented style

    $count  = $conn->countRows($result);
    
#### Procedural style

    $count = dbrows($result);
    
### Count the matched rows in a table

#### Object-oriented style

	$countAll = $conn->count('(*)', DB_USERS);

or

	$count = $conn->count('(*)', DB_USERS, 'user_status = :status', array(
    	':status' => 0    
	));

#### Procedural style

	$countAll = dbcount('(*)', DB_USERS);

or

	$count = dbcount('(*)', DB_USERS, 'user_status = :status', array(
    	':status' => 0    
	));

### Get the last inserted auto_increment id

#### Object-oriented style

	$lastId = $conn->getLastId();
	
#### Procedural style

	$lastId = dblastid();
	
### Get the database server's version

#### Object-oriented style

	$version = $conn->getServerVersion();
	
#### Procedural style

Not implemented

## Statistics

Only the object-oriented version is implemented

### Get the number of all SQL queries
    use PHPFusion\Database\AbstractDatabaseDriver; // At the top of the PHP file
    
	$count = AbstractDatabaseDriver::getGlobalQueryCount();
	
or

	$count = $conn->getGlobalQueryCount();
	
### Get the number of SQL queries of a connection

	$count = $conn->getQueryCount();

### Get the summary of query time of all queries
    use PHPFusion\Database\AbstractDatabaseDriver; // At the top of the PHP file
    
	$time = AbstractDatabaseDriver::getGlobalQueryTimeSum();
	
or

	$time = $conn->getGlobalQueryTimeSum();

### Get the summarized query time of all queries of a connection

	$time = $conn->getQueryTimeSum();

## Debugging 

You can turn on the debug mode per connection. When you do it, all 
queries, parameters and query times will be listed at the bottom of the page
under everything else.

### Debug the default connection

Insert this line into the config.php:

	PHPFusion\Database\DatabaseFactory::setDebug();

### Debug custom connections

In config.db.php set debug to TRUE

	<?php
	return array(
		'custom1' => array(
			'host' => 'host1',
			'database' => 'database1',
			'user' => 'user1',
			'password' => 'password1',
			'driver' => 'pdo_mysql', // Optional. 'pdo_mysql' or 'mysql'. pdo_mysql by default.
			'charset' => 'utf8', // Optional. utf8 by default
			'debug' => TRUE // Optional. FALSE by default
		)
	);
	
### Get the queries anywhere

It works only if the script does not stop earlier:

    use PHPFusion\Database\AbstractDatabaseDriver; // At the top of the PHP file
    
	$queries = AbstractDatabaseDriver::getGlobalQueryLog();
	print_p($queries);

Queries only one connection:

	$queries = $conn->getQueryLog();
	print_p($queries);

## Error handling

### Connection errors

### Object-oriented style

	try {
		$conn2 = DatabaseFactory::getConnection('test');
	} catch (ConnectionException $e) {
		exit('Oops! The database connection cannot be established!');
	} catch (SelectionException $e) {
		exit('Oops! I could not select the database for the connection: "test"');
	}
	
#### Procedural style

You need to set the fifth parameter of the dbconnect() to FALSE. 
Otherwise, the script will be halted on any failure.

	$info = dbconnect($db_host, $db_user, $db_pass, $db_name, FALSE);
	if (!$info['connection_success']) {
		exit('Oops! The database connection cannot be established!');
	} elseif (!$info['dbselection_success']) {
		exit('Oops! I could not select the database for the default connection');
	}
	
## More about DatabaseFactory and customization

Coming soon...
