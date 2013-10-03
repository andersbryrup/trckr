<?php

if ( $_SERVER['REMOTE_ADDR'] != '90.185.206.100' )
{
  die();
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title></title>
</head>
<body>
  <h1>Import</h1>
<?php

require '../timereg/class.sugarCrmConnector.php';

if (empty($_POST))
{

$sugar = sugarCrmConnector::getInstance('http://crm1.bellcom.dk/soap.php');
$sugar->connect( 'henrik', '#wi6piUq' );

$accounts = $sugar->getEntryList( 'Accounts', 'accounts.deleted != 1', 'name', 0, array('name','id') );

$options = '';
foreach ($accounts->entry_list as $account) 
{
   $options .= sprintf('<option value="%s">%s</option>', $account->name_value_list[0]->value, $account->name_value_list[1]->value);
}
?>

  <form action="/bcapp/import.php" method="POST" accept-charset="utf-8">
    <p>
      <select multiple="multiple" size="20" name="accounts[]">
      <?php
      echo $options;
      ?>
      </select>
    </p>
    <p><input type="submit" value="Continue &rarr;" /></p>
  </form>
<?php
}
else
{
  $sugar = sugarCrmConnector::getInstance('http://crm1.bellcom.dk/soap.php');
  $sugar->connect( 'henrik', '#wi6piUq' );

  $accounts = array();
  foreach ($_POST['accounts'] as $accountID) 
  {
    $accounts[] = $sugar->getEntryList( 'Accounts', sprintf( 'accounts.id = "%s"', $accountID ), 'name', 0, array('name','description','account_type','industry','phone_fax','billing_address_street','billing_address_city','billing_address_state','billing_address_postalcode','billing_address_country','phone_office','phone_alternate','website','shipping_address_street','shipping_address_city','shipping_address_state','shipping_address_country','parent_id','campaign_id') );
  }

  sugarCrmConnector::destroy();
  $sugarNew = sugarCrmConnector::getInstance('http://crm.bellcom.dk/soap.php');
  $sugarNew->connect( 'henrik', '@2le&ouC' );

  foreach ($accounts as $account) 
  {
    $data = array();

    foreach ( $account->entry_list as $entry ) 
    {
      foreach ($entry->name_value_list as $values) 
      {
        $data[] = array(
          'name'  => $values->name,
          'value' => $values->value,
          );
      }
    }
    $sugarNew->setEntry( 'Accounts', $data );
  }
}
?>

</body>
</html>
