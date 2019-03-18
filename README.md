# my_query
PHP class to make queries to database

To make queries to database my_query php class is used.

To use it include two files:
include '/var/www/lfmg/api.livingformusicgroup.com/functions/cons.php';
include '/var/www/lfmg/api.livingformusicgroup.com/functions/query.php';
Cons.php file stores variables to connect to different databases. Open it to understand it.

Query.php includes a php class my_query that allows to make easy-to-understand and easy in code requests to database.

Most popular functions of this php class are: select, insert, update.

Let's look at them in detail:

//This is Select function structure: function select($table, $function, $where_columns = null, $where_values = null, $select_column = null, $query_end = null)

//Function example
$con = new my_query($db);
$info = $con->select('table_name','one', 'id', '5', 'select_column','limit 1'); // This means to get a 'select_column' (which is a column name example) where 'id' column = '5' from table 'table_name'; additional information to this function is 'limit 1' which adds this text to the end of the query;  parameter 'one' which is the second parameter in the function means that the function will return only one row from the table. Other option is 'many'
echo $info; //  it will return the value of the selected column, e.g. '15' etc (depending on the column type - text, integer etc)
//Example with 'many' 2nd parameter of the function:
$info = $con->select('my_table','many', ['type','age'], ['contacts','30-40'], 'phone', 'order by id desc'); //It means that we search for table rows where type = 'contacts' and age = '30-40' order by id 'desc'; parameter 'many' means that the function will return an array of values;
print_r($info);
//Here you will get an array with such a structure: $info[0]['phone'], $info[1]['phone'] etc

//Update function example:
$con->update('table_name','id','5',['type','age'],['contacts','20-30']); //It means to update a column 'type' to be 'contacts' and 'age' to be '20-30'where id = '5' in the table 'table_name'

//Insert function example
$con->insert('table_name',['type','age'],['contacts','10-20']); // This means that a row with column type = 'contacts' and age = '10-20' will be added to the 'table_name' table
For more examples see the structure of the file.
