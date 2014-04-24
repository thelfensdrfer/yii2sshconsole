<?php

if (defined('YII_ENV'))
	define('NET_SSH2_LOG_SIMPLE', NET_SSH2_LOG_COMPLEX);

namespace thelfensdrfer\yii2sshconsole;

use \Yii;

class LoginFailedException extends \Exception {}
class LoginUnknownException extends \Exception {}
class NotConnectedException extends \Exception {}

class Controller extends \yii\console\Controller
{
	private $ssh = null;

	private $callback = null;

	/**
	 * Connect to the ssh server.
	 *
	 * @param string $host
	 * @param array $auth
	 * Login via username/password
	 *     [
	 *         'username' => 'myname',
	 *         'password' => 'mypassword', // can be empty
	 *      ]
	 * or via private key
	 * 		[
	 * 		    'key' => '/path/to/private.key',
	 * 		    'password' => 'mykeypassword', // can be empty
	 * 		]
	 * @param integer $port Default 22
	 * @param integer $timeout Default 10 seconds
	 *
	 * @throws \thelfensdrfer\yii2sshconsole\LoginFailedException If the login failes
	 * @throws \thelfensdrfer\yii2sshconsole\LoginUnknownException If no username is set
	 *
	 * @return bool
	 */
	public function connect($host, $auth, $callback = null, $port = 22, $timeout = 10)
	{
		$this->ssh = new \Net_SSH2($host, $port, $timeout);

		if (!isset($auth['key']) && isset($auth['username'])) {
			// Login via username/password

			$username = $auth['username'];
			$password = isset($auth['password']) ? $auth['password'] : '';

			if (!$this->ssh->login($username, $password))
				throw new LoginFailedException(Yii::t(
					'Yii2SshConsole',
					'Login failed for user {username} using password {answer}!',
					[
						'username' => $username,
						'answer' => !empty($password) ? 1 : 0
					]
				));
			else
				return true;
		} elseif (isset($auth['key']) and isset($auth['username'])) {
			// Login via private key

			$username = $auth['username'];
			$password = isset($auth['key_password']) ? $auth['key_password'] : '';

			$key = new Crypt_RSA();
			if (!empty($password)) {
				$key->setPassword($password);
			}
			$key->loadKey(file_get_contents($auth['key']));

			if (!$this->ssh->login($username, $key))
				throw new LoginFailedException(Yii::t(
					'Yii2SshConsole',
					'Login failed for user {username} using key with password {answer}!',
					[
						'username' => $username,
						'answer' => !empty($password) ? 1 : 0
					]
				));
			else
				return true;
		} else {
			// No username given

			throw new LoginUnknownException(Yii::t(
					'Yii2SshConsole',
					'No username given!'
			));
		}

		return false;
	}

	/**
	 * Run a ssh command for the current connection.
	 *
	 * @param string $command
	 * @param callable $callback
	 *
	 * @throws NotConnectedException If the client is not connected to the server
	 *
	 * @return string
	 */
	public function run($command, $callback = null)
	{
		if (!$this->ssh->isConnected())
			throw new NotConnectedException();

		return $this->ssh->write($command);
	}

	/**
	 * Returns the log messages of the connection.
	 *
	 * @return array
	 */
	public function getLog()
	{
		return $this->ssh->getLog();
	}
}