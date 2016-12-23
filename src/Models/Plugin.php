<?php namespace Tehcodedninja\Plugins\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Plugin extends Model
{
	protected $fillable = [
		'name',
		'slug',
		'description',
		'version',
		'active',
		'path'
	];

	public function activate()
	{
		if($this->active)
			return $this;

		require_once $this->path.'/'.$this->file;
		$class = $this->namespace.'\\'.$this->pluginClass();
		resolve($class);

		$class::up();

		$this->active = 1;
		$this->save();
		return $this;
	}

	public function deactivate()
	{
		if (!$this->active)
			return $this;

		require_once $this->path.'/'.$this->file;
		$class = $this->namespace.'\\'.$this->pluginClass();
		resolve($class);

		$class::down();

		$this->active = 0;
		$this->save();
		return $this;
	}

	public function scopeActive($query)
	{
		return $query->where('active', '1');
	}

	public static function getFolderName($namespace)
	{
		$exploded_namespace = explode('\\', $namespace);
		$plugin_name = lcfirst($exploded_namespace[count($exploded_namespace)-2]);
		return Str::snake($plugin_name);
	}

	public static function getPlugins()
	{
		$plugins_path = glob($_SERVER['DOCUMENT_ROOT'].'/content/plugins/*', GLOB_ONLYDIR);
		$plugins = collect([]);

		foreach ($plugins_path as $plugin_path)
		{
			$info = json_decode(file_get_contents($plugin_path.'/info.json'));
			$slug = Str::slug($info->name);

			$plugin = Plugin::where('slug', $slug)->first();
			if (!$plugin)
			{
				$plugin = Plugin::create([
					'name'				=> $info->name,
					'slug'				=> $slug,
					'description'	=> $info->description,
					'version'			=> $info->version,
					'active'			=> false,
					'path'				=> $plugin_path,
				]);
			}
			
			$plugins->push($plugin);
		}

		return $plugins;
	}

	public function getFileAttribute()
	{
		return $this->__plugin_class($this->path).'.php';
	}

	public function getNamespaceAttribute()
	{
		return "Plugins\\".$this->__plugin_class($this->path);
	}

	public function pluginClass()
	{
		return $this->__plugin_class($this->path);
	}

	private function __plugin_class($path)
	{
		$plugin_folder_name = pathinfo($this->path)['filename'];
		$class_name = Str::ucfirst(Str::camel($plugin_folder_name));
		return $class_name;
	}
}