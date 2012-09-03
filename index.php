<!doctype html>
<meta charset="utf-8">
<title>Simple CMS - is simple</title>
<link href='http://fonts.googleapis.com/css?family=Open+Sans:400italic,300,400,700&subset=latin-ext' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=EB+Garamond&subset=latin-ext' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Marck+Script|Shadows+Into+Light+Two&subset=latin-ext' rel='stylesheet' type='text/css'>

<link rel="stylesheet" href="style.css">
<link rel="shortcut icon" href="favicon.ico">
<script src="jquery.min.js"></script>
<script src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>

<script>
$(function() {
  $('nav .expand').click(function() {
    $(this).next().slideToggle();
  });
  //$('nav .expand + ul').hide();
});
</script>
<hgroup>
  <h1><a href="https://plus.google.com/117847088376801430983/posts" style="color:red">+</a> <a href="/">Simple CMS</a></h1>
  <h2><a href="/">is simple</a></h2>
</hgroup>

<?php

setlocale(LC_ALL, '');

function path_clean($path) {
  $path = str_replace("pages/", "", $path);
  $path = str_replace(".html", "", $path);
  return $path;
}

function path_unclean($path) {
  return "pages/".$path.".html";
}

function path_to_title($path) {
  return path_clean(basename($path));
}

function startsWith($haystack, $needle) {
  $length = strlen($needle);
  return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle) {
  $length = strlen($needle);
  if ($length == 0) {
    return true;
  }

  return (substr($haystack, -$length) === $needle);
}

function rscandir($base='./', &$data=array()) {

  $array = scandir($base);
   
  foreach($array as $value) {

    if (startsWith($value, '.')) continue;
  
    if (is_dir($base.$value)) {
      $data = rscandir($base.$value.'/', $data);
      
    } else if (is_file($base.$value)) {
      $data[] = $base.$value;
      
    }
    
  }
  
  return $data;
  
}

function is_bad_page($path, $check_index = true) {
  if($check_index && endsWith($path, "index.html")) return true;
  if(endsWith($path, "~")) return true;
  return !endsWith($path, ".html");
}

?>

<article>

<?php

$clean_path = isset($_GET["p"]) ? $_GET["p"] : '';
if(strpos($clean_path,'..') !== false) $clean_path = "";
$path = path_unclean($clean_path);

if( file_exists($path) ) {
  echo "<h1>".path_to_title($path)."</h1>";
  echo "<time>".strftime ("%e %B %Y", filemtime($path))."</time>";
  readfile($path);
} else if("" == $clean_path ) {
  ?>

  <h1>Last Changes</h1>
  <ul>

  <?php
    $files = rscandir('pages/');
    usort($files, function($a, $b) {
      return filemtime($a) < filemtime($b);
    });

    $i = 0;
    foreach($files as $file) {
      if($i++ >= 20) break;
      if(is_bad_page($file, false)) continue;
      $clean_path = path_clean($file);
      $title = path_to_title($file);
      echo '<li><a href="?p='.urlencode($clean_path).'">'.$title.'</a> <time>'.strftime ("%e %B %Y", filemtime($file)).'</time></li>';      
    }
  ?>

  </ul>

  <?php
} else {
  echo "<h1>404 - this page does not exist</h1>";
}

?>
</article>

<nav>
  <ul>
    <li>
      <a href="."><em>Last Changes</em></a>
    </li>
    <?php

       function browse_dir($path) {
         if ($handle = opendir( $path )) {
           while (false !== ($entry = readdir($handle))) {
             if ($entry != "." && $entry != "..") {
               visit($path . "/" . $entry);
             }
           }
           closedir($handle);
         }
       }

       function visit($path)
       {

         $name = basename( $path );

         if (is_dir($path)) {

           if(file_exists($path . '/index.html')) {

             $safepath = path_clean($path . '/index.html');
             $name = '<a href="?p='.urlencode($safepath).'">'.$name.'</a>';

           }

           echo "<li>$name";
           echo ' <a class="expand open">±</a>';
           echo "<ul>";
           browse_dir( $path );
           echo "</ul></li>";

         } else if (is_file($path)) {

           if(is_bad_page($path)) return;

           $name = path_to_title($path);
           $safepath = path_clean($path);

           echo "<li><a href=\"?p=".urlencode($safepath)."\">$name</a></li>\n";

         }

       }

       browse_dir('pages');
       
       ?>
  </ul>
</nav>
