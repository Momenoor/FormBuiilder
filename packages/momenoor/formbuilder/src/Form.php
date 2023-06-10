<?php

namespace Momenoor\FormBuilder;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Momenoor\FormBuilder\Concerns\HasDBConnection;
use Momenoor\FormBuilder\Concerns\HasOptions;
use Spatie\Html\Html;

class Form
{

    use HasOptions, HasDBConnection;

    public array $fields = [];
    public string|null|object $model = null;
    public array $rules = [];
    public array $messages = [];
    public array $errors = [];
    public array $data = [];
    public string|null $name = null;
    public string|View|null $template = null;

    public Html $htmlBuilder;
    protected array $allowedAttributes = [
        'method',
        'action',
        'enctype',
        'accept-charset',
        'accept',
        'multipart',
    ];
    public string $identifier = '';


    public function buildForm()
    {
    }

    /**
     * @throws BindingResolutionException
     * @throws \Exception
     */
    public function render($options = [])
    {
        $this->initDependencies();
        $attributes = $this->prepareAttributes();
        $options = $this->prepareOptions($options);

        $script = $this->loadJs(['jquery.slim.min','bootstrap.bundle.min', 'select2.full.min', 'formBuilder.min']);
        $style = $this->loadCss(['bootstrap.min', 'select2.min','select2-bootstrap-5-theme.min']);


        return view()->make($this->getTemplate())->with(
            [
                'data' => $this->getData(),
                'model' => $this->getModel(),
                'options' => $options,
                'script' => $script,
                'style' => $style,
                'attributes' => $attributes,
                'fields' => $this->getFields(),
                'rules' => $this->getRules(),
                'messages' => $this->getMessages(),
                'errors' => $this->getErrors(),
                'form' => $this,
                'showInCard' => $this->getOption('show_in_card'),
                'rowClass' => $this->getOption('row_class'),
            ]
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function add($name, $type = null, $attributes = null,): Field
    {
        if (is_array($name)) {
            $attributes = $name;
            $name = Arr::get($attributes, 'name', null);
            $type = Arr::get($attributes, 'type', null);
        }

        if (empty($name)) {
            abort(500, 'Field name can\'t be empty');
        }

        if (is_array($type)) {
            $attributes = $type;
            $type = null;
        }

        $attributes['form'] = $this;
        $attributes['model'] = $this->getModel();

        $field = Field::make($name, $type, $attributes);

        $this->addField($field);

        return $field;
    }


    /**
     * @param Field $field
     * @return $this
     */
    protected function addField(Field $field): static
    {
        $this->fields[$field->getName()] = $field;
        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getConfig($key, $default = null)
    {
        if (!str_starts_with($key, 'FormBuilder.')) {
            $key = 'FormBuilder.' . $key;
        }
        return config($key, $default);
    }

    public function setTemplate($template)
    {
        return $this->template = $template;
    }

    public function getTemplate(): View|string
    {
        return $this->template ?: $this->getConfig('form.template_prefix') . $this->getConfig('form.template');
    }


    public function setModel($model): static
    {
        if (is_string($model)) {
            $model = new $model;
        }
        $this->model = $model;
        return $this;
    }

    public function getModel(): object
    {
        if (empty($this->model)) {
            $this->guessFormModelName();
        }
        return $this->model;
    }


    public function setRules(array $rules): static
    {
        $this->rules = $rules;
        return $this;
    }

    public function setMessages(array $messages): static
    {
        $this->messages = $messages;
        return $this;
    }

    public function setErrors(array $errors): static
    {
        $this->errors = $errors;
        return $this;
    }

    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function setName($name): static
    {
        $this->name = $name;
        return $this;
    }

    public function setUrl($url): static
    {
        return $this->setAction(url($url));
    }

    public function setRoute($route): static
    {
        return $this->setAction(route($route));
    }

    public function setAction($action): static
    {
        $this->options['action'] = $action;
        return $this;
    }

    public function setMethod($method): static
    {
        $this->options['method'] = $method;
        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getRules(): array
    {
        return $this->rules;
    }

    public function getName(): ?string
    {
        if (empty($this->name)) {
            $this->name = Str::snake(class_basename($this));
        }
        return $this->name;
    }

    public function getAction()
    {
        return $this->options['action'];
    }

    public function getMethod()
    {
        return $this->options['method'];
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    public function hasMessages(): bool
    {
        return !empty($this->messages);
    }

    public function hasFields(): bool
    {
        return !empty($this->fields);
    }

    public function hasModel(): bool
    {
        return !empty($this->model);
    }

    public function hasRules(): bool
    {
        return !empty($this->rules);
    }

    /**
     * @throws \Exception
     */
    private function guessFormModelName(): void
    {
        $name = Str::of($this->getName())->lower()->ucfirst();
        if (Str::contains($name, 'form')) {
            $name = Str::remove(['form', '_'], $name);
        }

        $namespace = $this->getConfig('form.model_namespace', 'App\\Models\\');
        $modelName = $namespace . $name;

        if (!class_exists($modelName)) {
            abort(500, 'Form ' . $modelName . ' not found');
        }
        $this->setModel($modelName);

    }

    private function initDependencies(): void
    {
        $hasRowClass = $this->getConfig('row_class') ?: false;
        if ($hasRowClass) {
            $this->addOption('row_class', $hasRowClass);
        }

        $showInCard = $this->getConfig('show_in_card') ?: false;
        if ($showInCard) {
            $this->addOption('show_in_card', $showInCard);
        }
    }

    /**
     * @param string $fileName
     * @param bool $isLink
     * @return string
     * @throws \Exception
     */
    private function loadJs(mixed $fileName, bool $isLink = true): string
    {
        $scripts = '';

        if (is_array($fileName)) {
            foreach ($fileName as $key => $value) {
                $scripts .= $this->loadJs($value);
            }
            return $scripts;
        } else {
            $file = $this->loadResource('js', $fileName, $isLink);
            if ($isLink) {
                return '<script src="' . asset($file) . '"></script>';
            }
            return '<script>' . $file . '</script>';
        }

    }

    /**
     * @param string $fileName
     * @param bool $isLink
     * @return string
     * @throws \Exception
     */
    private function loadCss(mixed $fileName, bool $isLink = true): string
    {
        $scripts = '';

        if (is_array($fileName)) {
            foreach ($fileName as $key => $value) {
                $scripts .= $this->loadCss($value);
            }
            return $scripts;
        } else {
            $file = $this->loadResource('css', $fileName, $isLink);
            if ($isLink) {
                return '<link rel="stylesheet" href="' . asset($file) . '">';
            }
            return '<style>' . $file . '</style>';
        }

    }

    /**
     * @throws \Exception
     */
    private function loadResource(string $type, string $fileName, bool $isLink = true): string
    {
        $ext = match ($type) {
            'js' => '.js',
            'css' => '.css',
            default => '.blade.php',
        };
        $file = $type . '/' . $fileName . $ext;
        $filePath = __DIR__ . '/resources/' . $file;

        if ($isLink) {
            return $file;
        }
        if (file_exists($filePath)) {
            return file_get_contents($filePath);
        }
        throw new \Exception('File not found: ' . $filePath);
    }
}
