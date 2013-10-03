<?php

if ( $_SERVER['REMOTE_ADDR'] != '90.185.206.100' )
{
  die();
}

require '../timereg/class.sugarCrmConnector.php';

if (empty($_POST))
{

$sugar = sugarCrmConnector::getInstance('http://crm.bellcom.dk/soap.php');
$sugar->connect( 'henrik', '@2le&ouC' );

$cases = $sugar->getEntryList( 'Cases', "cases.deleted != 1 AND cases.status != 'Closed'", 'name', 0, array('name','id') );

$options = '';
foreach ($cases->entry_list as $case ) 
{
   $options .= sprintf('<option value="%s">%s</option>', $case->name_value_list[0]->value, $case->name_value_list[1]->value);
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title></title>
</head>
<body>
  <h1>Case dump</h1>
  <form action="/bcapp/case-dump.php" method="POST" accept-charset="utf-8">
    <p>
      <select multiple="multiple" size="20" name="cases[]">
      <?php
      echo $options;
      ?>
      </select>
    </p>
    <p><input type="submit" value="Continue &rarr;" /></p>
  </form>
</body>
</html>
<?php
}
else
{
  header("Content-type: text/csv");  
  header("Cache-Control: no-store, no-cache");  
  header('Content-Disposition: attachment; filename="filename.csv"');
  $sugar = sugarCrmConnector::getInstance('http://crm.bellcom.dk/soap.php');
  $sugar->connect( 'henrik', '@2le&ouC' );

  $cases = array();
  foreach ($_POST['cases'] as $caseID) 
  {
    $cases[] = $sugar->getEntryList( 'JCRMTime', sprintf( 'jcrmtime.case_id = "%s"', $caseID ), 'date', 0, array() );
  }

  $fields = array( 'created_by_name','description', 'date_entered', 'case_name', 'account_name', 'time_length', 'category' );
  $headers = array();
  $lines = array();

  foreach ($cases as $case) 
  {
    foreach ( $case->entry_list as $entry ) 
    {
      $data = array();

      foreach ($entry->name_value_list as $values) 
      {
        if ( in_array( $values->name,$fields ) )
        {
          $headers[$values->name] = $values->name;

          switch ($values->name) 
          {
            case 'category':
              switch ($values->value) 
              {
                case '1':
                  $category = 'Account Management';
                  break;
                case '2':
                  $category = 'Admin';
                  break;
                case '3':
                  $category = 'Analyse og design';
                  break;
                case '4':
                  $category = 'Consulting';
                  break;
                case '5':
                  $category = 'Design og layout';
                  break;
                case '6':
                  $category = 'Dokumentation / vejledninger';
                  break;
                case '7':
                  $category = 'Drift';
                  break;
                case '8':
                  $category = 'HTML';
                  break;
                case '9':
                  $category = 'Installation';
                  break;
                case '10':
                  $category = 'Møde';
                  break;
                case '11':
                  $category = 'OS Bidrag max 2 timer per uge';
                  break;
                case '12':
                  $category = 'Opgradering af Postnuke';
                  break;
                case '13':
                  $category = 'Pre-sales';
                  break;
                case '14':
                  $category = 'Projektledelse';
                  break;
                case '15':
                  $category = 'Support';
                  break;
                case '16':
                  $category = 'Test';
                  break;
                case '17':
                  $category = 'Udvikling';
                  break;
                case '18':
                  $category = 'Undervisning';
                  break;
              }
              $data[] = '"'.$category.'"';
              break;
            case 'time_length':
              $data[] = $values->value;
              break;
            default:
              $data[] = '"'. $values->value .'"';
              break;
          }


        }
      }
      $lines[] = implode( ';', $data );
    }
  }

  echo '"'. implode( '";"', $headers ).'"'. PHP_EOL;
  echo implode( PHP_EOL, $lines );
}
?>
