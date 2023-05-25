<?php

namespace Momenoor\FormBuilder\Concerns;

use Illuminate\Support\Arr;

trait HasDBConnection
{
    private function getSchema()
    {
        return $this->getModel()->getConnection()->getSchemaBuilder();
    }

    private function driverIsMongoDb(): bool
    {
        return $this->getSchema()->getConnection()->getConfig()['driver'] === 'mongodb';
    }

    /**
     * Check if the database connection is any sql driver.
     *
     * @return bool
     */
    private function driverIsSql(): bool
    {
        $driver = $this->getSchema()->getConnection()->getConfig('driver');

        return in_array($driver, $this->getSqlDriverList());
    }

    /**
     * Get SQL driver list.
     *
     * @return array
     */
    private function getSqlDriverList(): array
    {
        return ['mysql', 'sqlsrv', 'sqlite', 'pgsql'];
    }

    public function getDbColumnType($name): string
    {
        $schema = $this->getSchema();
        $tableName = $this->getModel()->getTable();
        if (Arr::get($schema->getColumnListing($tableName), $name)) {
            return $schema->getColumnType($tableName, $name);
        }
        return 'string';
    }

}
