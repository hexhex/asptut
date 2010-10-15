<?

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 1);
assert_options(ASSERT_BAIL, 0);
assert_options(ASSERT_QUIET_EVAL, 0);

$lines = file($_POST["filename"]);

$prologue = true;

$solver="";
$ontology="";
$filter="";

foreach ($lines as $line_num => $line)
{
    if ($prologue)
    {
        if (substr($line, 0, 8) == "%solver=")
            $solver = substr($line, 8, strlen($line) - 9);
        else if (substr($line, 0, 10) == "%ontology=")
            $ontology = substr($line, 10, strlen($line) - 11);
        else if (substr($line, 0, 8) == "%filter=")
            $filter = substr($line, 8, strlen($line) - 9);
        else
        {
            $prologue = false;
            echo $solver."|".$ontology."|".$filter."\n";
        }
    }
    
    if (!$prologue)
        echo $line;
}

?>
