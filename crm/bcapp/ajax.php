<?php
require 'config.php';
require 'class.sugarCrmConnector.php';

switch ($_GET['action']) 
{
  case 'save':
    $return = save();
    break;

  case 'jsInit':
    $return = jsInit();
    break;

  case 'completeAccount':
    $return = completeAccount();
    break;

  case 'saveContact':
    $return = saveContact();
    break;
  
  default:
    $return = array( 'error' => true, 'msg' => 'Unknown action' );
    break;
}

die( json_encode( $return ) );

function jsInit()
{
  try
  {
    $sugar = sugarCrmConnector::getInstance();
    $sugar->connect(); // we should have a cookie here, no need for username/password
    $casesData = $sugar->getEntryList( 'Cases', "cases.status != 'Closed'", 'name', 0, array('name','account_id') );

    $cases = array();
    $casesToAccounts = array();

    foreach ( $casesData->entry_list as $case )
    {
      $cases[] = array( 'id' => $case->id, 'name' => trim($case->name_value_list[0]->value) );
      $casesToAccounts[$case->id] = $case->name_value_list[1]->value;
    }

    $return = array( 
      'error'            => false, 
      'msg'              => 'Ok', 
      'cases'            => $cases,
      'casesToAccounts'  => $casesToAccounts,
    );
  }
  catch (Exception $e)
  {
    $return = array( 
      'error'            => true, 
      'msg'              => $e->getMessage(), 
    );
  }

  return $return;
}

function save()
{
  extract($_GET);
  $sugar = sugarCrmConnector::getInstance();
  $sugar->connect(); // we should have a cookie here, no need for username/password
  $userID = $sugar->getUserID();

  $date = $year.'-'.$month.'-'.$day;

  $explodeChar = null;

  if ( strpos($hours,'.') )
  {
    $explodeChar = '.';
  }

  if ( strpos($hours,',') )
  {
    $explodeChar = ',';
  }

  if ( !is_null($explodeChar) )
  {
    list($h,$m) = explode($explodeChar,$hours);
    $m = sprintf("%-'02s",$m); // pad with 0 to the right

    if ( $m <= 25 )
    {
      $hours = $h.':15';
    }
    elseif ( $m <= 50 )
    {
      $hours = $h.':30';
    }
    elseif ( $m <= 75 )
    {
      $hours = $h.':45';
    }
    else 
    {
      $hours = ($h+1).':00';
    }
  }

  // Add missing :00
  if ( strpos($hours,':') === false )
  {
    $hours = $hours.':00';
  }

  $data = array(
    array( 
      'name' => "date_entered",
      'value' => date('Y-m-d H:i:s'),
    ),
    array( 
      'name' => "date_modified",
      'value' => date('Y-m-d H:i:s'),
    ),
    array( 
      'name' => "case_id",
      'value' => $case_id,
    ),
    array( 
      'name' => "account_id",
      'value' => $account_id,
    ),
    array( 
      'name' => "description",
      'value' => $description,
    ),
    array( 
      'name' => "inv_comment",
      'value' => $comment,
    ),
    array( 
      'name' => "assigned_user_id",
      'value' => $userID,
    ),
    array( 
      'name' => "modified_user_id",
      'value' => $userID,
    ),
    array( 
      'name' => "created_by",
      'value' => $userID,
    ),
    array( 
      'name' => "date",
      'value' => $date,
    ),
    array( 
      'name' => "time_length",
      'value' => $hours,
    ),
    array( 
      'name' => "category",
      'value' => $category,
    ),
  );

  try
  {
    $sugar->setEntry('JCRMTime',$data);
    $return = array( 'error' => false, 'msg' => 'Added '. $hours.' hours to '. $caseName .' on '. date('D \t\h\e j \o\f F Y',mktime(0,0,0,$month,$day,$year)) );
  }
  catch (Exception $e)
  {
    $return = array( 'error' => true, 'msg' => $e->getMessage() );
  }

  return $return;
}

function completeAccount()
{
  $accounts = array();

  $sugar = sugarCrmConnector::getInstance();
  $sugar->connect( 'henrik', '@2le&ouC' );

  $query = $_GET['term'];

  $rawAccounts = $sugar->getEntryList( 'Accounts', "accounts.deleted = 0 AND accounts.name LIKE '%". $query ."%'", 'name', 0, array('name','id') );

  foreach ( $rawAccounts->entry_list as $account )
  {
    $name = trim($account->name_value_list[1]->value);
    $accounts[] = array( 'id' => $account->id, 'name' => $name, 'label' => $name);
  }

  return $accounts;
}

function saveContact()
{
  $contactsData = array(
    array(
      'name' => 'first_name',
      'value' => $_POST['firstname'],
      ),
    array(
      'name' => 'last_name',
      'value' => $_POST['lastname'],
      ),
    array(
      'name' => 'phone_work',
      'value' => $_POST['phonenum'],
      ),
    array(
      'name' => 'account_id',
      'value' => $_POST['account_id'],
      ),
    array(
      'name' => 'email1',
      'value' => $_POST['email'],
      ),
    );

  try
  {
    $sugar = sugarCrmConnector::getInstance();
    $sugar->connect( 'henrik', '@2le&ouC' );
    $result = $sugar->setEntry('Contacts',$contactsData);

    $contactID = $result->id;

    /*if ( !empty($_POST['email']) )
    {
      $emailAddressData = array(
        array(
          'name' => 'email_address',
          'value' => $_POST['email'],
        ),
      );

      $result = $sugar->setEntry('EmailAddresses',$emailAddressData);
      $emailAddressID = $result->id;

      $relation = array(
        'module1'    => 'Contacts',
        'module1_id' => $contactID,
        'module2'    => 'EmailAddresses',
        'module2_id' => $emailAddressID,
        );

      error_log(__LINE__.':'.__FILE__.' '.print_r($relation,1)); // hf@bellcom.dk debugging
      //$result = $sugar->setRelation( $relation );
      //error_log(__LINE__.':'.__FILE__.' '.print_r($result,1)); // hf@bellcom.dk debugging
    }
    */

    $return = array( 'error' => false, 'msg' => 'Saved' );
  }
  catch (Exception $e)
  {
    $return = array( 'error' => true, 'msg' => $e->getMessage() );
  }

  return $return;
}
