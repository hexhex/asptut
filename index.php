<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
    <title>Answer Set Programming for the Semantic Web</title>
    <link rel="stylesheet" type="text/css" href="style.css" />
    <script type="text/javascript" src="code/prototype.lite.js"></script>
    <script type="text/javascript" src="code/moo.fx.js"></script>
    <script type="text/javascript" src="code/moo.fx.pack.js"></script>
    <script type="text/javascript" src="code/moo.ajax.js"></script>
    <script type="text/javascript" src="code/common.js"></script>
</head>

<!-- body onload="initPosition(document.getElementById('lp'))" -->
<body>

    <div id="header">
    <h2>Answer Set Programming for the Semantic Web</h2>
        <!-- Slides -->
        <p style='margin-bottom: 0px;'>Slides:&nbsp;
        <span><a href="slides/unit1.pdf" target="_blank">Unit1</a></span>&nbsp;
        <span><a href="slides/unit2.pdf" target="_blank">Unit2</a></span>&nbsp;
        <span><a href="slides/unit3.pdf" target="_blank">Unit3</a></span>&nbsp;
        <span><a href="slides/unit4.pdf" target="_blank">Unit4</a></span>&nbsp;
        <span><a href="slides/unit5.pdf" target="_blank">Unit5</a></span>&nbsp;
        <span><a href="slides/unit6.pdf" target="_blank">Unit6</a></span>&nbsp;
        <span><a href="slides/unit7.pdf" target="_blank">Unit7</a></span>
        </p>
    </div>
<div id="Wrapper">

    <div class="examplecol">
        <p>Examples:</p>
    <?
        $path = "./examples/";
        $directory = dir($path); 
        $directories_array = array();

        while ($file = $directory->read())
            if (is_dir($path.$file) && ($file != ".") && ($file != "..") && ($file != "CVS"))
                $directories_array[] = $file;

        sort($directories_array);

        foreach ($directories_array as $file)
        {
            // read content of this example directory
            echo "<div class=\"stretchtoggle\"><a href=\"#$file\">$file</a></div>\n";

            $exdirpath = $path.$file."/";
            $exdir = dir($exdirpath); 
            $exfiles_array = array();

            while ($exfile = $exdir->read())
                if (is_file($exdirpath.$exfile) && 
                    ($exfile[0] != ".") && 
                    ($exfile != "CVS"))
                    $exfiles_array[] = $exfile;

            sort($exfiles_array);

            echo "<div class=\"stretcher\">\n";
            foreach($exfiles_array as $value)
            echo "<span class=\"examplelink\">&nbsp;<a href=\"#\" onClick=\"javascript:loadfile('$exdirpath$value'); return false;\">$value</a></span><br/>\n";

            echo "</div>\n";
        }
        $directory->close();

    ?>

    <script type="text/javascript">
    /*
     * examples-accordion:
     */
    var myDivs = document.getElementsByClassName('stretcher');
    var myLinks = document.getElementsByClassName('stretchtoggle');
    var myAccordion = new fx.Accordion(myLinks, myDivs, {opacity: true});
    </script>

    </div> <!-- example column -->

    <div class="maincol">

        <!-- next two divs are for ie bug! -->
        <div style='width: 95%;'><div>
        <!-- set top margin to 5px for cropped buttons bug on ie! -->
        <p style='margin-top: 5px;'>Choose the reasoner:&nbsp;
        <span class="solverbutton" 
              id="dlv"
              onClick="javascript:togglesolver(this.id);"><a href="#">dlv</a></span>
        <span class="solverbutton"
              id="dlt"
              onClick="javascript:togglesolver(this.id);"><a href="#">dlt</a></span>
        <span class="solverbutton"
              id="dlvhex"
              onClick="javascript:togglesolver(this.id);"><a href="#">dlvhex</a></span>
        <span class="solverbutton"
              id="dlvhex+dlt"
              onClick="javascript:togglesolver(this.id);"><a href="#">dlvhex+dlt</a></span>
        <span class="solverbutton"
              id="dlp"
              onClick="javascript:togglesolver(this.id);"><a href="#">dlp</a></span>
        &nbsp;<span id="ont">Ontology:&nbsp;
        <input id="ontology" type="text" style="width: 250px;" /></span>
        </p>

        <div style="text-align: right;">[<a href="#" onClick="javascript:clearlp(); return false;">clear</a>]</div>
        <textarea id="lp"
                  wrap="off" style="margin-bottom: 10px;">%%% enter your program here %%%</textarea>
                  <!-- name="lptextarea"
                  onmouseup="updatePosition(this)"
                  onmousedown="updatePosition(this)"
                  onkeyup="updatePosition(this)"
                  onkeydown="updatePosition(this)"
                  onfocus="updatePosition(this)" -->
        </div>
        </div>
        <!--
        Line:&nbsp;<span id='txtline'>0</span>&nbsp;
        Column:&nbsp;<span id='txtcol'>0</span><br/>
        -->

        Result Filter:<br/>
        <input id="filter" type="text" style="width: 95%;" /><br/>
        (comma-separated list of predicate names)
        <br/>
        <br/>

        <div style="text-align: left;">
            <input type="checkbox" id="onefactperline">One fact per line
            &nbsp;&nbsp;
            <span id="evalbutton"><b><a href="#" onClick="javascript:evallp(); return false;">[evaluate]</a></b></span>
        </div>

        <div id="result"></div>

    <!--
    <div style="clear: both;"></div>
    -->

    </div> <!-- maincol -->

</div> <!-- Wrapper -->

</body>

<script type="text/javascript">
    solverToSet = "dlv";
    togglesolver(solverToSet);
</script>

</html>
