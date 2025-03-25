<?php
ini_set('max_execution_time', 300);

interface DataStorage 
{
    public function connect();
    public function createTable($columns);
    public function insertData($columns, $data);
}
class XmlData 
{
    private $dataStorage;
    private $config;
    private $logFile;
    private $xmlFile;
    private $columns;
    private $xml;

    public function __construct($config) 
    {
        $this->config = $config;
        $this->logFile = $this->config['logging']['log_file'];
        $this->xmlFile = $this->config['xml']['file_path'];
        $this->selectDbType();
        $this->processXml();
        $this->insertData();
    }

    //configuring the storage type based on our requirements
    private function selectDbType() 
    {
        $storageType = $this->config['database']['type'];

        if ($storageType == 'mysql') 
        {
            $this->dataStorage = new MySQLDb($this->config);
        } else 
        {
            $this->logError("Unsupported storage type: $storageType");
            exit;
        }

        $this->dataStorage->connect();
    }

    //reading xml file and processing data
    private function processXml() 
    {
        $this->xml = simplexml_load_file($this->xmlFile);
        if (!$this->xml) 
        {
            $this->logError("Error: Cannot read XML file");
            exit;
        }

        if (!isset($this->xml->item[0])) 
        {
            $this->logError("Error: XML file is empty or invalid format");
            exit;
        }

        $f_column = (array) $this->xml->item[0];
        $this->columns = array_keys($f_column);
        

        if (empty($this->columns)) 
        {
            $this->logError("Error: No columns found in XML");
            exit;
        }

        $this->dataStorage->createTable($this->columns);
        echo "Table created successfully!\n"; // Echo after table creation
    }

    private function insertData() 
    {
        $this->dataStorage->insertData($this->columns, $this->xml->item);
        echo "Data inserted successfully!\n"; // Echo after data insertion
    }

    private function logError($message) 
    {
        file_put_contents($this->logFile, date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
    }
}


class MySQLdb implements DataStorage 
{
    private $conn;
    private $config;

    public function __construct($config) 
    {
        $this->config = $config;
    }
    //connection to database
    public function connect()
    {
        $db = $this->config['database'];
        $this->conn = new mysqli($db['host'], $db['username'], $db['password'], $db['db_name']);

        if ($this->conn->connect_error) 
        {
            $this->logError("Database Connection Error: " . $this->conn->connect_error);
            exit;
        }

        echo "Connected successfully to DB\n";
    }
     //creating a table in database based on columns fetched from xml file
    public function createTable($columns) 
    {
        $sql_1 = "CREATE TABLE IF NOT EXISTS feed_data (`id` INT AUTO_INCREMENT PRIMARY KEY, ";
        foreach ($columns as $column) 
        {
            $sql_1 .= "`$column` VARCHAR(255), ";
        }
        $sql_1 = rtrim($sql_1, ", ") . ")";

        if ($this->conn->query($sql_1) !== TRUE) 
        {
            $this->logError("Error creating table: " . $this->conn->error);
            exit;
        }
    }
     //insert data to created table based on columns and its corresponding data
    public function insertData($columns, $data) 
    {
        $value_fill = implode(", ", array_fill(0, count($columns), "?"));
        $sql_2 = "INSERT INTO feed_data (" . implode(", ", $columns) . ") VALUES ($value_fill)";
        $stmt = $this->conn->prepare($sql_2);

        if (!$stmt) 
        {
            $this->logError("Error preparing statement: " . $this->conn->error);
            exit;
        }

        foreach ($data as $item) 
        {
            $values = array_values((array) $item);
            $types = str_repeat("s", count($values));
            $stmt->bind_param($types, ...$values);

            if (!$stmt->execute()) 
            {
                $this->logError("Error inserting data: " . $stmt->error);
            }
        }

        $stmt->close();
        $this->conn->close();
    }
     //creating error log file 
    private function logError($message) 
    {
        file_put_contents($this->config['logging']['log_file'], date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
    }
}


$config = json_decode(file_get_contents('config.json'), true);
new XmlData($config);
?>
