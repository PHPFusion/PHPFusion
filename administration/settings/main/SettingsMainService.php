<?php

namespace PHPFusion\Administration\Settings\Main;

final class SettingsMainService
{
    private SettingsMainRepository $repository;
    private array $schema;

    public function __construct(?SettingsMainRepository $repository = NULL)
    {
        $this->repository = $repository ?? new SettingsMainRepository();
        $this->schema = MainSettingsSchema::storage();
    }

    public function all(): array
    {
        $keys = [];
        foreach ($this->schema as $fields) {
            $keys = array_merge($keys, array_keys($fields));
        }
        return $this->repository->read($keys);
    }

    public function read(string $section): array
    {
        return $this->repository->read(array_keys($this->schema[$section] ?? []));
    }

    public function validate(string $section, array $input, string $onlyField = ''): array
    {
        $errors = [];
        foreach ($this->schema[$section] ?? [] as $name => $rules) {
            if ($onlyField !== '' && $name !== $onlyField) {
                continue;
            }
            $value = trim((string)($input[$name] ?? ''));
            if (!empty($rules['required']) && $value === '') {
                $errors[$name][] = 'This field is required.';
                continue;
            }
            if (!empty($rules['max']) && mb_strlen($value) > (int)$rules['max']) {
                $errors[$name][] = 'This value is too long.';
            }
            switch ($rules['type']) {
                case 'email':
                    if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$name][] = 'Enter a valid email address.';
                    }
                    break;
                case 'enum':
                    if (!in_array($value, (array)$rules['values'], TRUE)) {
                        $errors[$name][] = 'Choose a valid option.';
                    }
                    break;
                case 'host':
                    if ($value !== '' && !filter_var($value, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                        $errors[$name][] = 'Enter a valid host name without a protocol or path.';
                    }
                    break;
                case 'path':
                    if ($value !== '' && (!str_starts_with($value, '/') || str_contains($value, '..'))) {
                        $errors[$name][] = 'Enter a valid absolute site path.';
                    }
                    break;
                case 'port':
                    if ($value !== '' && filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]) === FALSE) {
                        $errors[$name][] = 'Enter a port between 1 and 65535.';
                    }
                    break;
                case 'domains':
                    foreach (preg_split('/[\r\n|]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $domain) {
                        if (!filter_var(trim($domain), FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
                            $errors[$name][] = 'One or more domain names are invalid.';
                            break;
                        }
                    }
                    break;
                case 'search_module':
                    if (!array_key_exists($value, self::searchOptions())) {
                        $errors[$name][] = 'Choose an available search module.';
                    }
                    break;
            }
        }

        return $errors;
    }

    public function update(string $section, array $input): array
    {
        $fields = $this->schema[$section] ?? [];
        $values = [];
        foreach ($fields as $name => $rules) {
            $value = trim((string)($input[$name] ?? ''));
            if ($rules['type'] === 'domains') {
                $value = implode('|', array_map('trim', preg_split('/[\r\n|]+/', $value, -1, PREG_SPLIT_NO_EMPTY) ?: []));
            }
            $values[$name] = stripinput($value);
        }

        if ($section === 'url') {
            $host = $values['site_host'];
            if (str_contains($host, '/')) {
                [$host, $path] = array_pad(explode('/', $host, 2), 2, '');
                $values['site_host'] = $host;
                if ($path !== '') $values['site_path'] = '/'.$path;
            }
            $values['siteurl'] = $values['site_protocol'].'://'.$values['site_host'].
                ($values['site_port'] !== '' ? ':'.$values['site_port'] : '').$values['site_path'];
        }

        $this->repository->update($values);

        return $values;
    }

    public static function searchOptions(): array
    {
        $locale = fusion_get_locale();
        $options = ['all' => $locale['admins_419a'] ?? 'All'];
        if ($handle = @opendir(INCLUDES.'search/')) {
            while (FALSE !== ($file = readdir($handle))) {
                if (!preg_match('/^(.*?)_include\.php$/i', $file, $matches)) continue;
                $name = $matches[1];
                $enabled = !defined('DB_INFUSIONS') || dbcount(
                    '(inf_id)',
                    DB_INFUSIONS,
                    'inf_folder=:folder',
                    [':folder' => $name]
                ) > 0;
                if (!$enabled) continue;
                $searchLocale = fusion_get_locale('', LOCALE.LOCALESET.'search/'.$name.'.php');
                $options[$name] = $searchLocale[$name.'_title'] ?? ucfirst(str_replace('_', ' ', $name));
            }
            closedir($handle);
        }

        return $options;
    }
}
