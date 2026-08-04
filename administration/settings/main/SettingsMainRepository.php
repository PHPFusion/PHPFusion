<?php

namespace PHPFusion\Administration\Settings\Main;

use RuntimeException;

final class SettingsMainRepository
{
    public function read(array $keys): array
    {
        $keys = $this->allowedKeys($keys);
        if ($keys === []) {
            return [];
        }

        $placeholders = [];
        $parameters = [];
        foreach ($keys as $index => $key) {
            $placeholder = ':setting_'.$index;
            $placeholders[] = $placeholder;
            $parameters[$placeholder] = $key;
        }

        $result = dbquery(
            'SELECT settings_name, settings_value FROM '.DB_SETTINGS.
            ' WHERE settings_name IN ('.implode(',', $placeholders).')',
            $parameters
        );
        $values = array_fill_keys($keys, '');
        while ($row = dbarray($result)) {
            $values[(string)$row['settings_name']] = (string)$row['settings_value'];
        }

        return $values;
    }

    public function update(array $values): void
    {
        if ($values === []) {
            return;
        }

        $allowed = array_flip($this->allowedKeys(array_keys($values), TRUE));
        foreach (array_keys($values) as $name) {
            if (!isset($allowed[$name])) {
                throw new RuntimeException('An unsupported settings key was rejected.');
            }
        }

        dbquery('BEGIN');
        try {
            foreach ($values as $name => $value) {
                $result = dbquery(
                    'UPDATE '.DB_SETTINGS.' SET settings_value=:value WHERE settings_name=:name',
                    [':value' => (string)$value, ':name' => (string)$name]
                );
                if ($result === FALSE) {
                    throw new RuntimeException('A setting could not be updated.');
                }
            }
            dbquery('COMMIT');
        } catch (\Throwable $exception) {
            dbquery('ROLLBACK');
            throw $exception;
        }
    }

    private function allowedKeys(array $keys, bool $allowDerived = FALSE): array
    {
        $allowed = [];
        foreach (MainSettingsSchema::storage() as $fields) {
            $allowed = array_merge($allowed, array_keys($fields));
        }
        if ($allowDerived) {
            $allowed[] = 'siteurl';
        }

        return array_values(array_intersect(
            array_unique(array_filter(array_map('strval', $keys))),
            array_unique($allowed)
        ));
    }
}
