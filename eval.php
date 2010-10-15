<?
/*
ob_start();
phpinfo(INFO_VARIABLES);
$phpinfo = ob_get_contents();
ob_end_clean();
$f = fopen("test.html", "a");
fwrite($f, $phpinfo);
fclose($f);
*/

function logUsage()
{
    global $globalvars;

    $remote = apache_getenv("REMOTE_ADDR");

    //
    // don't log myself
    //
    if ($remote == "128.130.205.13")
        return;

    if ($remote == "128.131.225.87")
        return;

    $f = fopen("accesslog", "a");

    $info = "Called from ".$remote." (".gethostbyaddr($remote).")";
    $info .= "  time: ".date("F j, Y, g:i a")."\n";

    fwrite($f, $info);
    fclose($f);
    
}

//
// php init stuff
//
ini_set("memory_limit", "60M");
ini_set("max_execution_time", "300");

assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 1);
assert_options(ASSERT_BAIL, 0);
assert_options(ASSERT_QUIET_EVAL, 0);

//logUsage();

//
// system-wide installation:
//
putenv("LD_LIBRARY_PATH=/usr/local/lib/dlvhex");
putenv("PATH=\$PATH:/usr/local/bin");
$DLVHEX_PLUGINDIR = "/usr/local/lib/dlvhex/plugins";


//putenv("MALLOC_CHECK_=0");
putenv("GLIBCXX_FORCE_NEW=1");


$uniqueID = substr(md5(uniqid(rand(),1)),0,10);

//
// correct post-strings
//
if (get_magic_quotes_gpc())
{
    function stripslashes_deep($value)
    {
        $value = is_array($value) ?
            array_map('stripslashes_deep', $value) :
            stripslashes($value);

        return $value;
    }

    $_POST = array_map('stripslashes_deep', $_POST);
}

if (isset($_POST["lp"]))
{
        $lpfile = "tempfiles/_user".$uniqueID.".lp";

        $f = fopen($lpfile, "w");
        fwrite($f, $_POST["lp"]);
        fclose($f);
}


//
// create filter string
//
$fs = "";

if (isset($_POST["filter"]) && ($_POST["filter"] != ""))
{
    //$fs = str_replace(" ", "", $_POST["filter"]);
    $fs = str_replace("\"", "\\\"", $_POST["filter"]);
}


$output = "";

//
// build the right solver command
//
switch($_POST["solver"])
{
case "dlv":

    $DLV="dlv -silent";

    if ($fs != "")
        $DLV .= " -filter=".$fs;

    $SOLVEREXEC = $DLV." ".$lpfile." 2>&1";

    break;

case "dlt":

    $DLT="dlt -silent";

    if ($fs != "")
        $DLT .= " -filter=".$fs;

    $SOLVEREXEC = $DLT." ".$lpfile." 2>&1";

    break;

case "dlvhex":

    $HEXSOLVER="dlvhex --silent --plugindir=$DLVHEX_PLUGINDIR";

    if ($fs != "")
        $HEXSOLVER .= " --filter=".$fs;

    $SOLVEREXEC = $HEXSOLVER." ".$lpfile." 2>&1";

    break;

case "dlvhex+dlt":

    $HEXSOLVER="dlvhex --silent --dlt --plugindir=$DLVHEX_PLUGINDIR";

    if ($fs != "")
        $HEXSOLVER .= " --filter=".$fs;

    $SOLVEREXEC = $HEXSOLVER." ".$lpfile." 2>&1";

    break;

case "dlp":

    $ontology = $_POST["dlpont"];

    if (trim($ontology) == "")
    {
        $output[] = "Error: no ontology specified for dl-program!";
        break;
    }

    $HEXSOLVER="dlvhex --silent --ontology=$ontology --plugindir=$DLVHEX_PLUGINDIR";

    if ($fs != "")
        $HEXSOLVER .= " --filter=".$fs;

    $SOLVEREXEC = $HEXSOLVER." ".$lpfile." 2>&1";

    break;

default:
    $output[] = "invalid solver specified: ".$_POST["solver"];
    break;
}

//
// execute it (if we have no error-msg already in $output)
//
if (isset($lpfile) && ($output == ""))
{
    //$SOLVEREXEC = "time ".$SOLVEREXEC;
    exec($SOLVEREXEC, $output, $retcode);
//    echo $lpfile;
    unlink($lpfile);
}


//
// display output
//
if ($output != "")
{
    // always modified
    //header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

    echo "<h3>Result:</h3>\n";

    if ($fs != "")
        echo "<p>filter: ".stripslashes($fs)."</p>\n";

    //@header("Content-Type: text/xml");
    @header("Cache-Control: no-cache, must-revalidate, max-age=0");
    // Date in the past
    @header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    @header("Pragma: no-cache");

    $i = 0;

    foreach ($output as $l)
    {
        if (empty($l))
            continue;

        // replace dlv best model string
        $l = str_replace("Best model: ", "", $l);

        if ($l[0] == '{')
        {
            $i++;
            echo "<p><b>Answer Set $i</b>:<br/>";
        }

        // replace tempfile (if we have an error msg)
        $l = str_replace(" in ".$lpfile, "", $l);

        // turn web-addresses into links:
        $l = ereg_replace('http://[^"]*', "<a href=\"\\0\">\\0</a>", $l);

        // one fact per line
        if ($_POST["ofpl"] === "true")
        {
            $l = ereg_replace(', ', ",<br/>", $l);
        }


        // put space after comma to break long facts
        $l = str_replace("\",\"", "\", \"", $l);

        echo $l."<p/>\n";
    }

}

?>
