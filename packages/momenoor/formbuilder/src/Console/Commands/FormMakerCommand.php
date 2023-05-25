<?php

namespace Momenoor\FormBuilder\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class FormMakerCommand extends GeneratorCommand
{
    protected $name = 'form:make {name?}';


    protected $description = 'Create a form builder class';

    public function handle(): void
    {

        if (!$this->argument('name')) {
            $name = $this->ask('Please provide a class name');
            if (empty($name)) {
                $this->error('You should provide a class name.');
            }
        }

        parent::handle();
    }

    protected function getStub(): string
    {
        $stubPath = __DIR__ . '/../stubs/form-builder-template.stub';

        if (!file_exists($stubPath)) {
            $this->error('Stub file not found.');
            return false;
        }
        return $stubPath;
    }

    protected function getArguments(): array
    {
        return array(
            array('name', InputArgument::REQUIRED, 'Full class name of the desired form class.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [
            ['fields', null, InputOption::VALUE_OPTIONAL, 'Fields for the form'],
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'Class namespace'],
            ['path', null, InputOption::VALUE_OPTIONAL, 'File path','App\Forms'],
        ];
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string $stub
     * @param  string $name
     * @return string
     */
    protected function replaceClass($stub, $name): string
    {

        $stub = str_replace(
            '{{class}}',
            $this->getClassInfo($name)->className,
            $stub
        );

        return str_replace(
            '{{fields}}',
            $this->getFieldsVariable($this->option('fields')),
            $stub
        );
    }

    /**
     * Replace the namespace for the given stub.
     *
     * @param  string $stub
     * @param  string $name
     * @return $this
     */
    protected function replaceNamespace(&$stub, $name): static
    {

        $path = $this->option('path');

        $namespace = $this->option('namespace');

        if (!$namespace) {
            $namespace = $this->getClassInfo($name)->namespace;

            if ($path) {
                $namespace = str_replace('/', '\\', trim($path, '/'));
                foreach ($this->getAutoload() as $autoloadNamespace => $autoloadPath) {
                    if (preg_match('|' . $autoloadPath . '|', $path)) {
                        $namespace = str_replace([$autoloadPath, '/'], [$autoloadNamespace, '\\'], trim($path, '/'));
                    }
                }
            }
        }

        $stub = str_replace('{{namespace}}', $namespace, $stub);

        return $this;
    }


    /**
     * Get psr-4 namespace.
     *
     * @return array
     */
    protected function getAutoload(): array
    {
        $composerPath = base_path('/composer.json');
        if (!file_exists($composerPath)) {
            return [];
        }
        $composer = json_decode(file_get_contents(
            $composerPath
        ), true);

        return Arr::get($composer, 'autoload.psr-4', []);
    }

    /**
     * Get the desired class name from the input.
     *
     * @return string
     */
    protected function getNameInput(): string
    {

        return str_replace('/', '\\', $this->argument('name'));

    }

    /**
     * @inheritdoc
     */
    protected function getPath($name): string
    {

        $optionsPath = $this->option('path');

        if (!empty($optionsPath)) {
            return join('/', [
                $this->laravel->basePath(),
                trim($optionsPath, '/'),
                $this->getNameInput() . '.php'
            ]);
        }

        return parent::getPath($name);
    }

    /**
     * Get fields from options and create add methods from it.
     *
     * @param string|null $fields
     * @return string
     */
    public function getFieldsVariable(string $fields = null): string
    {
        if ($fields) {
            return $this->parseFields($fields);
        }

        return '// Add fields here...';
    }

    /**
     * @param string $name
     * @return object
     */
    public function getClassInfo(string $name): object
    {
        $explodedClassNamespace = explode('\\', $name);
        $className = array_pop($explodedClassNamespace);
        $fullNamespace = join('\\', $explodedClassNamespace);

        return (object)[
            'namespace' => $fullNamespace,
            'className' => $className
        ];
    }

    /**
     * Parse fields from string.
     *
     * @param string $fields
     * @return string
     */
    protected function parseFields(string $fields): string
    {
        $fieldsArray = explode(',', $fields);
        $text = '$this' . "\n";

        foreach ($fieldsArray as $field) {
            $text .= $this->prepareAdd($field, end($fieldsArray) == $field);
        }

        return $text . ';';
    }

    /**
     * Prepare template for single add field.
     *
     * @param string $field
     * @param bool $isLast
     * @return string
     */
    protected function prepareAdd(string $field, bool $isLast = false): string
    {
        $field = trim($field);
        list($name, $type) = explode(':', $field);
        $textArr = [
            "            ->add('",
            $name,
            "', '",
            $type,
            "')",
            ($isLast) ? "" : "\n"
        ];

        return join('', $textArr);
    }
}
