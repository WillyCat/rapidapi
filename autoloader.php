<?php
function
myautoloader (string $class_name)
{
        $fn =  $class_name . '.class.php';

	$paths = array_merge ([ '.' ], explode (PATH_SEPARATOR, get_include_path() ));

	foreach ($paths as $path)
		if (file_exists ($path . DIRECTORY_SEPARATOR . $fn))
		{
			include $path . DIRECTORY_SEPARATOR . $fn;
			return;
		}
//else
//echo 'non existent: ' . $path . DIRECTORY_SEPARATOR . $fn . "\n";

	throw new Exception ('file not found: ' . $fn);
}
?>
