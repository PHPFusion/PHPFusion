<?php

namespace PHPFusion\Core\Profile;

use RuntimeException;

final class ProfileModel
{
    public function findUser(int $userId): array
    {
        if ($userId <= 0) {
            return [];
        }

        $result = dbquery(
            'SELECT * FROM ' . DB_USERS . ' WHERE user_id=:user_id LIMIT 1',
            [':user_id' => $userId]
        );

        return dbrows($result) ? dbarray($result) : [];
    }

    public function updateUserColumns(int $userId, array $values, array $allowedColumns): void
    {
        $allowed = array_fill_keys($allowedColumns, TRUE);
        foreach ($values as $column => $value) {
            if (
                !isset($allowed[$column])
                || !$this->validIdentifier((string)$column)
                || !column_exists(DB_USERS, (string)$column, FALSE)
            ) {
                throw new RuntimeException('Public profile data must use an allowed DB_USERS column.');
            }

            dbquery(
                'UPDATE ' . DB_USERS . " SET {$column}=:field_value WHERE user_id=:user_id",
                [':field_value' => $value, ':user_id' => $userId]
            );
        }
    }

    private function validIdentifier(string $identifier): bool
    {
        return (bool)preg_match('/^[a-z][a-z0-9_]{1,63}$/i', $identifier);
    }
}
