<?php

namespace Momenoor\FormBuilder\Concerns;

use Doctrine\DBAL\Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait HasIdentifier
{
    /**
     * @var string
     */
    private string $identifier;


    /**
     * @throws Exception
     */
    public function getIdentifierColumnName(): string
    {
        $identifiers = [];
        if (!empty($this->identifier)) {
            return $this->identifier;
        }

        $defaultIdentifier = $this->getDefaultIdentifier();
        $columns = $this->getForm()->getColumns();
        $columnNames = $columns;

        foreach ($defaultIdentifier as $column) {
            if (in_array($column, $columnNames)) {
                return $column;
                break;
            }
        }

        $indexes = $this->getForm()->getIndexes();
        foreach ($indexes as $index) {
            foreach ($columnNames as $key => $column) {
                if (Str::contains($column, '_id')) {
                    continue;
                }
                Arr::add($identifiers, $key, $column);
            }
        }
        return Arr::first($identifiers);

    }

    /**
     * @return string
     * @throws Exception
     */
    public function getIdentifier(): string
    {

        $this->setIdentifier($this->getIdentifierColumnName());

        return $this->identifier;
    }

    /**
     * @param string $identifier
     *
     * @return $this
     */
    public function setIdentifier(string $identifier): static
    {

        $this->identifier = $identifier;

        return $this;
    }

    private function getDefaultIdentifier(): array
    {
        return [
            'name',
            'title',
            'label',
            'subject',
            'text',
            'description',
            'email',
        ];
    }

}
