<?php
$OUTFILE = 'output.php';

//process file arguments
if(count($argv) > 1){
    foreach ($argv as $index => $pot_option) {
        $option_array = processOption($pot_option);
        if(isset($option_array['option'])){
            switch($option_array['option']){
                case 'append':
                    $APPEND_FILE = $option_array['params'];
                    break;
            }
        }

    }
} 

// if(isset($argv[1]) && $argv[1] != ""){
//     $OUTFILE = $argv[1];
// }
// if(!preg_match("/^.*\.php$/", $OUTFILE)){
//     $OUTFILE.=".php";
// }

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
//should have done this from the beginning
$OUTFILE = $table_name . ".php";


$columns = array();
if(isset($APPEND_FILE)){
    echo "Importing $APPEND_FILE\n";
    $APPEND_FIXTURE = importAndProcessFixtureFile($APPEND_FILE); 
    $columns = array_keys($APPEND_FIXTURE[0]);
} else {
    echo "Enter Column Names(Enter to quit)\n";
    do{
        echo "Column: ";
        $column = trim(fgets($stdin));
        if($column != ''){
            $columns[] = $column;
        }
    }while($column != "");
}

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
if(isset($APPEND_FIXTURE)){
    $to_generate = array_merge_recursive($APPEND_FIXTURE, $to_generate);
}

do{
    echo "Generate New Fixture\n";
    echo "Alias (Enter for no alias):";
    $alias = trim(fgets($stdin));

    $temp = array();
    foreach($columns as $index=>$column_name){
        echo "Enter Value for $column_name: ";
        $tmp[$column_name] = trim(fgets($stdin));
    }

    if($alias != ''){
        $to_generate[$alias] = $tmp;
    }
    else {
        $to_generate[] = $tmp;
    }

    echo "Generate Another Fixtue? [yes|no]:";
    $continue = trim(fgets($stdin));
}while($continue != 'no');


echo "Fixtures to generate\n";
print_r($to_generate);

echo "Writing fixtures to file: $OUTFILE\n";
file_put_contents($OUTFILE, fixtureTemplate($to_generate));


function fixtureTemplate($fixtures){
    $tpl = "<?php\n";
    $tpl .= "return array(\n";
    foreach($fixtures as $index => $fixture){
        if(!is_numeric($index)){
            $tpl .= "'$index' => ";
        }
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


function processOption($potential_option){
    $retval = array();

    if(preg_match("/^--(.*)=(.*)$/", $potential_option, $matches)){
        if(isset($matches[1]) && $matches[1] != ''){
                $retval['option'] = $matches[1];
        }
        if(isset($matches[2]) && $matches[2] != ''){
                $retval['params'] = $matches[2];
        }
    }

    return count($retval) > 0 ? $retval : false;
}

function importAndProcessFixtureFile($file){
    try{
        $old_fixture = require($file);
    } catch(Exception $e){
        echo "Could not find target file\n";
    }

    return $old_fixture;
}

?>
