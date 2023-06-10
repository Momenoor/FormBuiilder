<?php

namespace Momenoor\FormBuilder\Concerns;

use Doctrine\DBAL\Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;

trait HasDBConnection
{

    private function getConnection(): \Illuminate\Database\Connection
    {
        return Schema::getConnection();
    }

    private function driverIsMongoDb(): bool
    {
        return $this->getConnection()->getConfig()['driver'] === 'mongodb';
    }

    /**
     * Check if the database connection is any sql driver.
     *
     * @return bool
     */
    private function driverIsSql(): bool
    {
        return $this->getConnection()->getConfig('driver') === 'mysql';

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
        $tableName = $this->model->getTable();
        if (Arr::get($this->getColumns(), $name)) {
            return Schema::getColumnType($tableName, $name);
        }
        return 'string';
    }

    public function getColumns($tableName = null): array
    {
        if (empty($tableName)) {
            $tableName = $this->model->getTable();
        }
        return Schema::getColumnListing($tableName);
    }

    /**
     * @throws Exception
     */
    public function getIndexes(): array
    {
        return $this->getConnection()->getDoctrineSchemaManager()->listTableIndexes($this->model->getTable());
    }

    public function getColumn($name): \Doctrine\DBAL\Schema\Column
    {
        return $this->getConnection()->getDoctrineColumn($this->model->getTable(), $name);
    }

}
