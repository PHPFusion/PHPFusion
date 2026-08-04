<?php

namespace PHPFusion\Apps\UserFields\Profile\RealName;

use PHPFusion\ProfileGlobal\ProfileContext;
use PHPFusion\ProfileGlobal\ProfileFieldValidator;
use PHPFusion\ProfileGlobal\ProfileModuleInterface;
use PHPFusion\ProfileGlobal\ProfileRepository;

final class RealNameModule implements ProfileModuleInterface
{
    private array $definition;
    private ProfileRepository $repository;
    private ProfileFieldValidator $validator;

    public function __construct(array $definition, ProfileRepository $repository)
    {
        $this->definition = $definition;
        $this->repository = $repository;
        $this->validator = new ProfileFieldValidator();
    }

    public function definition(): array
    {
        return $this->definition;
    }

    public function schema(ProfileContext $context): array
    {
        $locale = fusion_get_locale();
        $values = $this->values($context);

        return [
            [
                'name'             => 'first_name',
                'label'            => $locale['urn_102'],
                'type'             => 'text',
                'required'         => TRUE,
                'required_message' => $locale['urn_106'],
                'max_length'       => 100,
                'placeholder'      => $locale['urn_104'],
                'autocomplete'     => 'given-name',
                'layout'           => 'half',
                'value'            => $values['first_name'],
            ],
            [
                'name'             => 'last_name',
                'label'            => $locale['urn_103'],
                'type'             => 'text',
                'required'         => TRUE,
                'required_message' => $locale['urn_107'],
                'max_length'       => 100,
                'placeholder'      => $locale['urn_105'],
                'autocomplete'     => 'family-name',
                'layout'           => 'half',
                'value'            => $values['last_name'],
            ],
        ];
    }

    public function values(ProfileContext $context): array
    {
        $realName = $this->normalizePart((string)$context->userValue('user_realname'));
        [$firstName, $lastName] = $this->splitName($realName);

        return [
            'first_name'    => $firstName,
            'last_name'     => $lastName,
            'user_realname' => $realName,
        ];
    }

    public function update(ProfileContext $context, array $input): array
    {
        $locale = fusion_get_locale();

        if (!$context->canEdit()) {
            return $this->error($locale['urn_108'], [], 403);
        }

        $schema = $this->schema($context);
        $validated = [];
        $errors = [];

        foreach ($schema as $field) {
            $name = (string)$field['name'];
            $validation = $this->validator->validate(
                $field,
                $this->normalizePart((string)($input[$name] ?? '')),
                $context,
                $this->definition
            );
            if ($validation['errors']) {
                $errors[$name] = $validation['errors'];
            }
            $validated[$name] = (string)$validation['value'];
        }

        $realName = $validated['first_name'] . ' ' . $validated['last_name'];
        if (!$errors && mb_strlen($realName) > 100) {
            $errors['last_name'] = [$locale['urn_109']];
        }

        if ($errors) {
            return $this->error($locale['urn_110'], $errors, 422);
        }

        $this->repository->updateUserColumn($context->subjectId(), 'user_realname', $realName);

        return [
            'success' => TRUE,
            'status'  => 200,
            'message' => $locale['urn_111'],
            'errors'  => [],
            'values'  => [
                'first_name'    => $validated['first_name'],
                'last_name'     => $validated['last_name'],
                'user_realname' => $realName,
            ],
        ];
    }

    private function normalizePart(string $value): string
    {
        $value = trim(strip_tags($value));

        return (string)preg_replace('/\s+/u', ' ', $value);
    }

    private function splitName(string $realName): array
    {
        if ($realName === '') {
            return ['', ''];
        }

        if (!preg_match('/^(.+)\s+(\S+)$/u', $realName, $matches)) {
            return [$realName, ''];
        }

        return [$matches[1], $matches[2]];
    }

    private function error(string $message, array $errors, int $status): array
    {
        return [
            'success' => FALSE,
            'status'  => $status,
            'message' => $message,
            'errors'  => $errors,
            'values'  => [],
        ];
    }
}
