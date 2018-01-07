<?php
$version = 0.1;
echo "Api Generator v.$version by Mte90 - www.mte90.net \n";
if ($argv[1] != "-help"){
$contents = file_get_contents(dirname(__FILE__) . "/php.api");
$array = explode("<&$>\n", $contents); 
$compr = "\n";
$space = " ";
if ($argv[1] == "-compressor"){
$textg .= "\n";
$compr = "";
$space = "";
$textc = " with the compressor option";
}
$textg = "<?php
/*This Api file was generated$textc the ".date('l jS \of F Y h:i:s A')."
With the Api Generator v.$version by Mte90 - www.mte90.net*/ \n";
echo "Generation Start!\n";
foreach ($array as $key => $value) {
$arrayb = explode("<&%>", $value); 
$basefl = explode("(",$arrayb[0]);
$textg .= "$"."funclist[".$key."]$space=$space\"".$basefl[0]."\";$compr";
$textg .= "$"."funcdest[".$key."]$space=$space\"".str_replace("<&$>","",str_replace("\"","'",$value))."\";$compr";
}
$textg .= "\n?>";
file_put_contents(dirname(__FILE__) . "/phpapi.php", $textg);
echo number_format((filesize(dirname(__FILE__) . "/php.api")/1024),1)." kb -> ".number_format((filesize(dirname(__FILE__) . "/phpapi.php")/1024),1)." kb\n";
echo "Generation finished!\n";
} else {
echo "Api Generator v.$version by Mte90 - www.mte90.net \n";
echo "\nThis script convert a pre-formatted list of function and description in a simple file to readable by the program. \n";
echo "\nThe command list:\n";
echo "-help                   to get this help\n";
echo "-compressor             to compress the api file generated\n";
echo "\nThanks to use this script\n";
}
?>