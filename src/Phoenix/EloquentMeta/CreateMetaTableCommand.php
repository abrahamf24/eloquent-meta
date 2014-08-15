<?php namespace Phoenix\EloquentMeta;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Filesystem\Filesystem;

class CreateMetaTableCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'generate:metatable';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Creates a migration for new metadata tables.';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct(FileSystem $filesystem)
	{
		$this->fs = $filesystem;

		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$table_name = strtolower($this->argument('name'));
		$migration = "create_{$table_name}_table";

		// The template file is the migration that ships with the package
		// It is located @ src/migrations/2013_07_01_180252_create_meta_table.php
		$template_dir = __DIR__.'/../../migrations/';
		$template_file = '2013_07_01_180252_create_meta_table.php';
		$template_path = $template_dir . $template_file;

		// Make sure the template path exists
		if ( ! $this->fs->exists($template_path))
		{
			return $this->error('Unable to find template: ' . $template_path);
		}
		
		// Set the Destination Directory
		$dest_dir = app_path() . '/database/migrations/';
		$dest_file = date("Y_m_d_His").'_'.$migration.'.php';
		$dest_path = $dest_dir . $dest_file;

		// Make Sure the Destination Directory exists
		if ( ! $this->fs->isDirectory($dest_dir))
		{
			dd('invalid destination directory');
		}

		// Read Template File
		$template = $this->fs->get($template_path);

		// Replace what is necessary
		$classname = 'Create'.studly_case(ucfirst($table_name)).'Table';

		$contents = str_replace('CreateMetaTable', $classname, $template);
		$contents = str_replace("'meta'", "'".$table_name."'", $contents);

		// Write new Migration to destination
		$this->fs->put($dest_path, $contents);

		// Dump-Autoload
		$this->call('dump-autoload');

		$this->info($table_name . ' migration created. run "php artisan migrate" to create the table');
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('name', InputArgument::REQUIRED, 'The name of the metatable to be built.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array();
	}

}