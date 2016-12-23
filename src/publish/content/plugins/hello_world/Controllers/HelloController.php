<?php namespace Plugins\HelloWorld\Controllers;

use App\Http\Controllers\Controller;

class HelloController extends Controller
{
	public function index()
	{
		return view('HelloWorld::index');
	}
}