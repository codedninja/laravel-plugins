<?php namespace Plugins\HelloWorld;

use Illuminate\Support\Facades\Facade;

class HelloWorld extends Facade {
	
	public function __construct()
	{

	}

	public static function up ()
	{
		echo "Up";
	}

	public static function down ()
	{
		echo "Down";
	}
}