# Yii2 SSH Console

Controller with ssh commands for the yii2 console.

## Example

	<?php namespace app\commands;

	use \thelfensdrfer\yii2sshconsole\Controller;

	class DeployController extends Controller
	{
		public function actionExec()
		{
			$this->auth('example.com', [
				'username' => 'myusername',
				'password' => 'mypassword', // optional
			]);

			// Or via private key
			/*
			$this->auth('example.com', [
				'username' => 'myusername',
				'key' => '/path/to/private.key',
				'password' => 'mykeypassword', // optional
			]);
			*/

			$output = $this->run('echo "test"');
			echo 'Output: ' . $output; // Output: test

			$output = $this->run([
				'cd /path/to/install',
				'./put_offline.sh',
				'git pull -f',
				'composer install',
				'./yii migrate',
				'./build.sh',
				'./put_online.sh',
			]);
		}
	}


	./yii deploy/exec