# Trial Task Solution
This project automates the process of extracting data from an XML file and storing it in a MySQL database.

## Description
The script processes an XML file, extracts data from it, and inserts it into a MySQL database. Here we are using xaaamp Apache. The configuration for the database,  type of database, XML file location, and logging path is set up through a `config.json` file, which must be created and placed in the same directory as the script.

## Example Json file
{
    "database": {
        "type": "mysql", 
        "host": "hostname",
        "username": "username",
        "password": "password",
        "db_name": "Database name"
    },
    "xml": {
        "file_path": "path of xml file"
    },
    "logging": {
        "log_file": "path of error log file"
    }
}

## Working for other databases
To adapt the code for example PostgreSQL,the following changes are made:

1. Database Class Modifications:
   Replace MySQL-specific functions (mysqli_*) with PostgreSQL functions (pg_*). Modify the MySQLdb class to PostgreSQLdb.
2. XmlData Class:
   Modified the selectDbType() method to support dynamic selection of the database class (PostgreSQLdb, MSSQLdb, or MySQLdb) based on the configuration file.
3. Configuration (config.json):
   Add type of database which we are using in json file.
4. Database Connection & Query Adjustments:
   Used appropriate database connection functions and query syntax for PostgreSQL (pg_connect, pg_query)
