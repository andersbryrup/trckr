<?php
if ( isset($_POST['action']) && $_POST['action'] == 'login' )
{
  try
  {
    require 'config.php';
    require 'class.sugarCrmConnector.php';
    $sugar = sugarCrmConnector::getInstance(); 
    $sugar->connect($_POST['username'], $_POST['password']);

    header('Location: http://'.$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']);
    die();
  }
  catch(Exception $e)
  {
    $error = $e->getMessage();
  }
}
?>
<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--[if lt IE 7 ]> <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="da" lang="da" class="ie ie6 ielt8"> <![endif]-->
<!--[if IE 7 ]>    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="da" lang="da" class="ie ie7 ielt8">  <![endif]-->
<!--[if IE 8 ]>    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="da" lang="da" class="ie ie8"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="da" lang="da"> <!--<![endif]-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Timereg</title>
    <meta name="MSSmartTagsPreventParsing" content="TRUE" />
    <link href="normalize.css" rel="stylesheet" type="text/css" />
    <link href="style.css" rel="stylesheet" type="text/css" />
  </head>
  <body>
<a href="import.php">Import (BETA)</a> | <a href="asterisk-to-contact.php">Quick contact create (Beta)</a> | <a href="case-dump.php">Case udtræk (Alpha)</a>
<?php
if ( !isset($_COOKIE['sessionID']) )
{
  if (isset($error))
  {
    echo $error;
  }
?>
<form action="SugarCrmSoap.php" method="post" accept-charset="utf-8">
  <p>
    <label>Username:</label>
    <input type="text" name="username" id="username" value="" />
  </p>
  <p>
    <label>Password:</label>
    <input type="password" name="password" value="" />
  </p>
  <p><input type="submit" value="Login" /></p>
  <input type="hidden" name="action" value="login" />
</form>
<script type="text/javascript">
document.getElementById('username').focus();
</script>
<?php
}
else
{
?>


<form action="#" method="post" accept-charset="utf-8" id="time_form">

<p>
  <label for="filter">Filter</label>
  <input type="text" name="filter" value="" id="filter" autocomplete="off" tabindex="1" />
  <span id="smartcase" style="display:none">Smartcase enabled</span>
</p>

<p>
  <label for="cases">Case:</label>
  <input type="button" name="refresh_cases" id="refresh_cases" value="Genindlæs" />
  <select name="case_id" id="cases" size="20" tabindex="2">
  </select>
</p>

<p>
<label>Tid:</label>
<input type="text" name="hours" value="" id="hours" tabindex="3" />
</p>

<p>
<label>Kategori:</label>
<select name="category" id="categories" tabindex="4">
  <option value="10">Møde</option>
  <option value="15">Aften support</option>
  <option value="17" selected="">Udvikling</option>
</select>
</p>

<p>
<label for="description">Beskrivelse:</label>
<input type="text" name="description" value="" id="description" tabindex="5" />
</p>

<p>
<label for="comment">Kommentar:</label>
<input type="text" name="comment" value="" id="comment" tabindex="6" />
</p>

<p>
<label for="day">Dag:</label>
<input type="text" name="day" value="<?php echo date('d')?>" tabindex="7" />
</p>

<p>
<label for="month">M&aring;ned:</label>
<select name="month" id="month" tabindex="8">
<?php

$months = array(
'januar'    => '01',
'februar'   => '02',
'marts'     => '03',
'april'     => '04',
'maj'       => '05',
'juni'      => '06',
'juli'      => '07',
'august'    => '08',
'september' => '09',
'oktober'   => '10',
'november'  => '11',
'december'  => '12',
  );

foreach ($months as $key => $value) 
{
  echo '<option value="'.$value.'" '. (date('m') == $value ? 'selected=""' : '' ) .'>'.$key.'</option>'; 
}
?>
</select>
</p>

<p>
<label for="year">&Aring;r:</label>
<input type="text" name="year" value="<?php echo date('Y')?>" tabindex="9" />
</p>

<p><input type="submit" value="Gem" tabindex="10" /></p>
</form>

<div id="log"></div>

  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script>
  <script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.validate/1.7/jquery.validate.min.js"></script>
  <script type="text/javascript" src="timereg.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
      timeRegForm.init();
    });
  </script>
<?php
}
?>
  </body>
</html>
