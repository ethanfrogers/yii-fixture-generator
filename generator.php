<?php
$OUTFILE = 'output.php';

if(isset($argv[1]) && $argv[1] != ""){
    $OUTFILE = $argv[1];
}
if(!preg_match("/^.*\.php$/", $OUTFILE)){
    $OUTFILE.=".php";
}

echo "Generate new Yii Fixture?[yes|no]:";

$stdin = fopen('php://stdin', 'r');
$continue = fgets($stdin);

if(trim($continue) != 'yes'){
    die("Exiting Yii Fixture Generator\n");
}

//define table name
echo "Enter Table Name:";
$table_name = trim(fgets($stdin));
echo "Table Name: $table_name\n";

//define columns to be used
$columns = array();
echo "Enter Column Names(Enter to quit)\n";
do{
    echo "Column: ";
    $column = fgets($stdin);
    if(trim($column) != ''){
        $columns[] = trim($column);
    }
}while(trim($column) != "");

//make sure there are no problems with the
//column names
do{
    echo "Edit Column Names? (Enter To Quit)\n";
    foreach($columns as $index => $name){
        echo "$index: $name\n";
    }
    $edit = trim(fgets($stdin));
    if($edit != "" && isset($columns[$edit])){
        $previous_name = $columns[$edit];
        echo "New Name (prev: $previous_name):";
        $columns[$edit] = trim(fgets($stdin));
    }
}while($edit != "");


$to_generate = array();
do{
    echo "Generate New Fixture\n";
    $temp = array();
    foreach($columns as $index=>$column_name){
        echo "Enter Value for $column_name: ";
        $tmp[$column_name] = trim(fgets($stdin));
    }
    $to_generate[] = $tmp;
    echo "Generate Another Fixtue? [yes|no]:";
    $continue = trim(fgets($stdin));
}while($continue != 'no');


echo "Writing fixtures to file: $OUTFILE\n";
file_put_contents($OUTFILE, fixtureTemplate($to_generate));


function fixtureTemplate($fixtures){
    $tpl = "<?php\n";
    $tpl .= "return array(\n";
    foreach($fixtures as $index => $fixture){
        $tpl .= "array(";
        foreach($fixture as $name => $value){
            $tpl .="'$name' => '$value',";
        }
        $tpl .= "),\n";
    }
    $tpl .= ");\n";
    $tpl .= "?>";


    return $tpl;
}




?>
