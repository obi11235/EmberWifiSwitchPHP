<?php
/**
 * Description of Hue_Light
 *
 * @author paul
 */
class WifiSwitch_Switch
{

	const LONG = 2;
	const ON = 1;
	const OFF = 0;

	private $ip;

	private $id;
	private $name;

	private $state;
	private $channel;

	private $ch = NULL;


	/**
	 *
	 * @param string $ip
	 */
	function __construct($ip, $channel)
	{
		$this->ip = $ip;
		$this->channel = $channel;

		$this->getState();
	}

	function getState()
	{
		$this->_setupCurl();

		curl_setopt($this->ch, CURLOPT_URL,'http://'.$this->ip.'/status');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$response = curl_exec($this->ch);

		$this->_parseState($response);

		return $this->state;
	}

	private function _setupCurl()
	{
		if(!is_null($this->ch))
			return;

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HTTPHEADER, array( 
			'Content-type: text/xml; charset="utf-8"'));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$this->ch = $ch;
	}

	public function closeConnection()
	{
		if(is_null($this->ch));
			return;
		curl_close($this->ch);
		$this->ch = null;
	}

	private function _parseState($response)
	{
		if(!isset($response) || $response == '')
			throw new Exception('Invalid Response, empty.');

		$matches = preg_match('/BUTTON'.$this->channel.'=(\d+)/s', $response, $status);

		Debug::print_r($response, 'Response');
		Debug::print_r($status, 'Status');
		Debug::print_r($matches, 'Matches');

		switch($matches)
		{
			case 0:
				throw new Exception('Missing States');
				break;
			case FALSE:
				throw new Exception('Error Parsing Response');
				break;
			case 1:
			default:
				break;
		}

		if($status[1] == 'Error')
			Throw new Exception('Wemo Error');

		$this->state = self::_validateStatus($status[1]);
	}

	private static function _validateStatus($state)
	{
		switch($state)
		{
			case self::OFF:
			case self::ON:
				return $state;
				break;
			case self::LONG:
				return self::ON;
				break;
			default:
				throw new Exception('Invalid Status');
		}
	}

	function setState($state)
	{
		$this->getState();

		if($this->state == $state)
			return true;

		$this->_setupCurl();

		curl_setopt($this->ch, CURLOPT_URL,'http://'.$this->ip.'/button/'.$this->channel);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS,'BUTTON='.$this->state);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		$response = curl_exec($this->ch);

		//$this->_parseState($response);
/*
		if($state == $this->state)
			return true;
		else
		{
			Debug::print_r($state, 'state');
			Debug::print_r($this->state, 'This->State');
			throw new Exception('Error changing state');
		}
*/
	}

	function flipSwitch()
	{
		$current = $this->getState();
		switch($current)
		{
			case self::ON: $this->setState(self::OFF); break;
			case self::OFF: $this->setState(self::ON); break;
		}
	}

	function switchChanged()
	{
		$old_state = $this->state;
		if($old_state == $this->getState())
			return false;
		else
			return true;
	}
}
