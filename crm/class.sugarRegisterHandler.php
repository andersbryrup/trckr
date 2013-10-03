<?php

require 'bcapp/class.sugarCrmConnector.php';

class registerHandler {
  private $sugar;

  static $categories = array(
    17 => 'Udvikling',
    10 => 'Møde',
    15 => 'Aften support'
  );

  public function __construct($user, $pass) {
    $this->sugar = sugarCrmConnector::getInstance();
    $this->sugar->connect($user, $pass);
  }

  public static function getCategories(){
    return self::$categories;
  }

  public function getCases(){
    try{
      $casesData = $this->sugar->getEntryList( 'Cases', "cases.status != 'Closed'", 'name', 0, array('name', 'account_id'));

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
    catch (Exception $e) {
      $return = array( 
        'error'            => true, 
        'msg'              => $e->getMessage(), 
      );
    }
    file_put_contents('cases', serialize($return['casesToAccounts']));
    return $return;
  }

  /**
   * $date
   *  yyyy-mm-dd
   */
  public function register($timestamp, $time, $case_id, $description, $type){
    $userID = $this->sugar->getUserID();

    $cases = unserialize(file_get_contents('cases'));

    $account_id = $cases[$case_id];

    if(!$account_id){
      return array(
        'error' => true,
        'msg' => 'Err. on account id, try syncing again'
      );
    }
    
    $comment = "";

    $date = date('Y-m-d', $timestamp);

    // time stuff
    // we do a litte rounding on the minutes, may this should
    // be changed
    $hours = gmdate('H:i', $time);

    list($h,$m) = explode(':',$hours);
    if ( $m <= 20 )
    {
      $hours = $h.':15';
    }
    elseif ( $m <= 35 )
    {
      $hours = $h.':30';
    }
    elseif ( $m <= 50 )
    {
      $hours = $h.':45';
    }
    else 
    {
      $hours = ($h+1).':00';
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
        'value' => $type,
      ),
    );

    error_log(print_r($data, 1));

    try
    {
      $this->sugar->setEntry('JCRMTime',$data);
      $return = array(
        'error' => false,
        'msg' => 'Added '. $hours.' hours on '. date('D \t\h\e j \o\f F Y', $timestamp)
      );
    }
    catch (Exception $e)
    {
      $return = array(
        'error' => true,
        'msg' => $e->getMessage() );
    }
    return $return;
  }
}

