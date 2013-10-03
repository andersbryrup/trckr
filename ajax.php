<?php
require 'crm/class.sugarRegisterHandler.php';
header('Content-type: application/json');

$input = json_decode(file_get_contents('php://input'));
if(is_object($input)){
  foreach($input as $key => $value){
    $_POST[$key] = $value;
  }
}

define('CRMUSERNAME', 'hell');
define('CRMPASSWORD', 'no!');

$pdo = new PDO('sqlite:time.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$tracker = new Tracker($pdo);

if(!empty($_POST)){
  if($_POST['action'] == 'saveTracker'){

    if(isset($_POST['id'])){
      $tracker->updateTracker($_POST);

    }
    else {
      $current = $tracker->getActiveTracker();

      $tracker->stopTracker((int)$current['id']);

      $time = NULL;
      $input = $_POST['input'];

      if($input[0] == '-'){
        $exp_array = explode(' ', $input);
        $time = -$exp_array[0] * 60;

        unset($exp_array[0]);

        $input = implode($exp_array, ' ');

      }
      $exp_array = explode('@', $input);
      
      $task = $exp_array[0]; 
      $client = $exp_array[1]; 

      $id = $tracker->addTracker($task, $time, $client );

      if(is_numeric($id)){
        print json_encode(array('id' => $id));
      }
    }
  }

  if($_POST['action'] == 'stopTracker'){
    $tracker->stopTracker($_POST['id']);
  }

  if($_POST['action'] == 'startTracker'){
    $current = $tracker->getActiveTracker();

    $tracker->stopTracker((int)$current['id']);

    $tracker->startTracker($_POST['id']);
  }

  if($_POST['action'] == 'deleteTracker'){
    $tracker->deleteTracker($_POST['id']);
  }

  if($_POST['action'] == 'registerTracker'){
    $registerHandler = new registerHandler(CRMUSERNAME, CRMPASSWORD);

    $item = $tracker->getTrackerInfo($_POST['id']);

    $timestamp = $item['timestamp'];
    $time = $item['time'];
    $case_id = $_POST['client_id'];
    $category = $_POST['category'];
    $description = $item['name'];

    $status = $registerHandler->register($timestamp, $time , $case_id, $description, $category);

    if($status['error'] !== TRUE){
      $tracker->registerTracker($_POST['id'], $status['msg'], 'success');
    }
    else {
      $tracker->registerTracker($_POST['id'], $status['msg'], 'failure');
    }

  }
} else {
  if(!empty($_GET)){
    if($_GET['q'] == 'getActiveTracker'){

      $tracker = $tracker->getActiveTracker();

      print json_encode($tracker);
      return;
    }

    if($_GET['q'] == 'getTrackerInfo'){

      $info = $tracker->getTrackerInfo($_GET['id']);

      print json_encode($info);
      return;
    }

    if($_GET['q'] == 'getOverview'){
      $from = $_GET['from'];
      $to = $_GET['to'];

      $default_category = 17;

      $cases = $tracker->getClients();
      $cases_arr = array();

      foreach($cases as $case){
        $cases_arr[$case['name']] = $case['id'];
      }

      $overview = $tracker->getOverview($from, $to);

      foreach($overview as $key => $row){
        $overview[$key]['category'] = $default_category;
        $overview[$key]['client_id'] = isset($cases_arr[$row['client']]) ? $cases_arr[$row['client']] : '';
      }

      print json_encode($overview);
      return;
    }

    if($_GET['q'] == 'getClients'){
      $cases = $tracker->getClients(); 

      print json_encode($cases);
      return;
    }

    if($_GET['q'] == 'syncClients'){
      $registerHandler = new registerHandler(CRMUSERNAME, CRMPASSWORD);

      $cases = $registerHandler->getCases();

      $tracker->deleteClients();

      foreach($cases['cases'] as $key => $value){
        $tracker->saveClient($value['name'], $value['id']);
      }
      return;
    }

    if($_GET['q'] == 'getSubmitForm'){
      $categories = registerHandler::getCategories();

      foreach ($categories as $id => $name){
        $categoryOptions[] = array('key' => $name , 'value' => $id);
      }

      $cases = $tracker->getClients(); 
      
      $clientOptions = '';

      foreach($cases as $case){
        $clientOptions[] = array('key' => $case['name'], 'value' => $case['id']);
      }

      $return = array(
        'categories' => $categoryOptions,
        'clients' => $clientOptions
      );

      print json_encode($return);
      return;
    }
  }

  $trackers = $tracker->getTrackers();
  print json_encode($trackers);
}

class Tracker {
  protected $dbh;

  public function __construct($pdo){
    $this->dbh = $pdo;
  }

  public  function addTracker($task, $time = NULL, $client= NULL){
    $start = time();

    $sql = 'INSERT INTO `trackers` (
      name,
      start,
      timestamp,
      time,
      client
    ) VALUES (
      :name,
      :start,
      :timestamp,
      :time,
      :client
    )';
    $stmt = $this->dbh->prepare($sql);
    $stmt->execute(array(
      ':name' => $task,
      ':start' => time(),
      ':timestamp' => time(),
      ':time' => $time,
      ':client' => $client)
    );
   
    return $this->dbh->LastInsertId();
  }

  public function deleteClients(){
    $sql = 'DELETE FROM `clients` WHERE 1';
    $stmt = $this->dbh->query($sql);
  }


  public function saveClient($name, $id){
    $start = time();

    $sql = 'INSERT INTO `clients` (
      name,
      id
    ) VALUES (
      :name,
      :id
    )';
    $stmt = $this->dbh->prepare($sql);
    $stmt->execute(array(
      ':name' => $name,
      ':id' => $id)
    );
   
    return $this->dbh->LastInsertId();
  }

  public function getClients(){
    $sql = 'SELECT * FROM `clients` WHERE 1';
    $stmt = $this->dbh->query($sql);

    return $stmt->fetchAll();
  }

  public function getTrackers(){
    $time = time() - 30000;

    $sql = 'SELECT * FROM `trackers` WHERE timestamp >= :timestamp ORDER BY timestamp DESC';
    $stmt = $this->dbh->prepare($sql);
    $stmt->execute(array(':timestamp' => $time));

    $rows = array();

    while($row = $stmt->fetch()){
      if($row['start']){
        $row['time'] = $row['time'] + (time() - $row['start']);
      }
      $row['time'] = gmdate('H:i', $row['time']);
      $rows[] = $row;
    }
    return $rows;
  }

  public function stopTracker($id){
    $sql = 'SELECT * FROM `trackers` WHERE id = :id';
    $stmt = $this->dbh->prepare($sql);
    $stmt->execute(array(':id' => $id));
    $row = $stmt->fetch();

    $time = (time() - $row['start']);

    $time = $row['time'] + $time;
   
    $sql = 'UPDATE 
      `trackers`
      SET 
      start = NULL,
      time = :time
      WHERE
      id = :id';

    $stmt = $this->dbh->prepare($sql);
    $stmt->execute(array(':id' => $id, ':time' => $time));
  }

  public function startTracker($id){
    $sql = 'UPDATE 
      `trackers`
      SET 
      start = :start
      WHERE
      id = :id';

    $stmt = $this->dbh->prepare($sql);
    $stmt->execute(array(':id' => $id, ':start' => time()));
  }

  public function deleteTracker($id){
    $sql = 'DELETE FROM 
      `trackers`
      WHERE
      id = :id';

    $stmt = $this->dbh->prepare($sql);
    $stmt->execute(array(':id' => $id));
  }

  public function updateTracker($post){
  
    $time_exp = explode(':', $post['time']);

    $start = $post['start'] ? time() : NULL;

    $time = ($time_exp[0] * 3600) + ($time_exp[1] * 60);
    $sql = 'UPDATE 
      `trackers`
      SET 
      name = :name,
      client = :client,
      time = :time,
      start = :start
      WHERE
      id = :id';

    $stmt = $this->dbh->prepare($sql);

    $err = $stmt->execute(array(
      ':id' => $post['id'],
      ':name' => $post['name'],
      ':client' => $post['client'],
      ':time' => $time,
      ':start' => $start,
    ));
  }

  public function registerTracker($id, $message, $status){
    $sql = 'UPDATE 
      `trackers`
      SET 
      registered = :registered,
      register_message = :register_message,
      register_status = :register_status
      WHERE
      id = :id';

    $stmt = $this->dbh->prepare($sql);
    $stmt->execute(array(':id' => $id, ':registered' => time(), ':register_message' => $message, ':register_status' => $status));
  }

  public function getTrackerInfo($id){
    $sql = 'SELECT * FROM `trackers` WHERE id = :id';

    $stmt = $this->dbh->prepare($sql);
    $stmt->execute(array(':id' => $id));
    $row = $stmt->fetch();

    if($row['start']){
      $row['time'] = $row['time'] + (time() - $row['start']);
    }
    return $row;
  }


  public function getActiveTracker(){
    $sql = 'SELECT * FROM `trackers` WHERE start <> \'\'';

    $stmt = $this->dbh->query($sql);

    $row = $stmt->fetch();

    if($row){
      $row['time'] = gmdate('H:i', $row['time'] + (time() - $row['start']));
    }
    else {
      $row['name'] = 'No tracker';
    }
    return $row;
  }

  public function getOverview($from, $to){
    $sql = 'SELECT * FROM `trackers` WHERE timestamp > :from AND timestamp < :to ORDER BY timestamp DESC';

    $stmt = $this->dbh->prepare($sql);
    $stmt->execute(array(':from' => $from, ':to' => $to));

    $rows = array();

    while($row = $stmt->fetch()){
      $row['time'] = gmdate('H:i', $row['time']);
      $row['timestamp'] = $row['timestamp'] * 1000;
      $rows[] = $row;
    }
    return $rows;
  }
}
