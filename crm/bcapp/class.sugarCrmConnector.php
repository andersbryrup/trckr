<?php
/**
 * @author Henrik Farre <hf@bellcom.dk>
 *
 */
class sugarCrmConnector
{
  private static $instance = null;
  private static $options = null;
  private $userID = null;
  private $sessionID = null;
  private $client = null;
  
  /**
   * @return void
   */
  private function __construct() 
  {
    $this->client = new SoapClient(NULL, self::$options);
  }

  /**
   * Connects to the SOAP host
   *
   * @return void
   * @author Henrik Farre <hf@bellcom.dk>
   **/
  public function connect($userName = null, $passWord = null )
  {
    if ( is_null($this->sessionID) && !isset($_COOKIE['sessionID']) )
    {
      $this->sessionID = $this->login( $userName, $passWord );
      setcookie('sessionID',$this->sessionID,time()+3600);
    }
    elseif( is_null($this->sessionID) && isset($_COOKIE['sessionID']) )
    {
      $this->sessionID = $_COOKIE['sessionID'];
    }

    $this->userID = $this->getUserIDFromServer();
    
    return $this->sessionID;
  }
  
  /**
   * destroy
   * @return void
   * @author Henrik Farre <hf@bellcom.dk>
   **/
  public static function destroy()
  {
    self::$instance = null;
  }

  /**
   * Gets userID from SugarCRM
   * @return string 
   */
  private function getUserIDFromServer()
  {
    return $this->client->get_user_id($this->sessionID);
  }

  /**
   * @param string $userName
   * @param string $passWord
   * @return string The sessionID
   */
  private function login( $userName, $passWord )
  {
    $userAuth = array(
      'user_name' => $userName,
      'password' => md5($passWord),
      'version' => '.01'
    );
    $response = $this->client->login($userAuth,'SOME_KEY');

    if ( $response->id < 0 && isset($response->error) )
    {
      throw new Exception( $response->error->description );
    }

     return $response->id;
  }
  
  /**
   * @param string $location Location of the SOAP host (uri)
   * @return sugarCrmConnector
   */
  public static function getInstance( $location = null )
  {
    /*if ( defined('SOAP_HOST') )
    {
      $location = SOAP_HOST;
    }

    if ( is_null($location) )
    {
      throw new Exception('No location given');
    }*/

    if ( is_null($location) )
    {
      $location = 'http://crm.bellcom.dk/soap.php';
    }

    self::$options = array(
      'location' => $location,
      'uri' => 'http://www.sugarcrm.com/sugarcrm',
      'trace' => 1
    );

    if ( self::$instance === null )
    {
      self::$instance = new self();
    }
  
    return self::$instance;
  }

  /**
   * @return string
   */
  public function getUserID()
  {
    if ( is_null($this->userID) )
    {
      $this->userID = $this->getUserIDFromServer();
    }
    return $this->userID;
  }

  /**
   * @return string
   */
  public function getSessionID()
  {
    if ( is_null($this->sessionID) )
    {
      throw new Exception('Not connected');
    }
    return $this->sessionID;
  }

  /**
   * @param string $sessionID
   */
  public function setSessionID( $sessionID )
  {
    $this->sessionID = $sessionID;
  }

  /**
   * @param string $module Which module to pull data from
   * @param string $query "Where" part of SQL statement (without the "where")
   * @param string $orderBy What field to order result by
   * @param int $offset Result offset
   * @param array $fields Which fields to fetch
   * @return array
   */
  public function getEntryList( $module, $query, $orderBy = '', $offset = 0, array $fields )
  {
    $response = $this->client->get_entry_list( $this->sessionID, $module, $query, $orderBy, $offset, $fields, 10000, false);

    if ( isset($response->error) && $response->error->number < 0)
    {
      throw new Exception( $response->error->description );
    }

    return $response;
  }

  /**
   * @param string $module Which module to add data to
   * @param array $data Data to be inserted
   * @return stdClass object
   */
  public function setEntry( $module, array $data )
  {
    $response = $this->client->set_entry($this->sessionID,$module,$data);
    if ( $response->id < 0 && isset($response->error) )
    {
      throw new Exception( $response->error->description );
    }
    return $response;
  }

  /**
   * setRelation
   * @param array $relation, see function set_relationship in soap/SoapSugarUsers.php
   * @return stdClass object
   * @author Henrik Farre <hf@bellcom.dk>
   **/
  public function setRelation( $relation )
  {
    $response = $this->client->set_relationship($this->sessionID,$relation);
    if ( $response->id < 0 && isset($response->error) )
    {
      throw new Exception( $response->error->description );
    }
    return $response;
  }
}
