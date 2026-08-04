# fusionFileinput

`form_fileinput()` renders a native multipart file input with a project-owned
drop zone, image thumbnails, inline errors, and an optional read-only core
preflight. It has no jQuery or Kartik dependency.

The browser preflight never stores a file. Final validation and storage still
belong to PHPFusion Defender when the enclosing PHP form is submitted.

## PHP usage

Single image:

```php
echo openform('avatarFrm', options: ['enctype' => TRUE]);
echo form_fileinput('avatar', 'Photo', '', [
    'upload_path' => IMAGES.'avatars/',
    'type' => 'image',
    'valid_ext' => '.jpg,.jpeg,.png,.webp',
    'max_byte' => 2 * 1024 * 1024,
    'thumbnail' => TRUE,
]);
echo form_button('save', 'Upload', 'save');
echo closeform();
```

Multiple documents:

```php
echo openform('documentsFrm', options: ['enctype' => TRUE]);
echo form_fileinput('documents', 'Documents', '', [
    'type' => 'object',
    'valid_ext' => '.pdf,.doc,.docx',
    'multiple' => TRUE,
    'max_count' => 5,
    'max_byte' => 10 * 1024 * 1024,
]);
echo closeform();

if (check_file_uploaded('documents')) {
    $uploads = file_sanitizer('documents', '', 'documents');
}
```

For a multiple input, `form_fileinput()` adds the `[]` HTML name suffix while
keeping the PHP `$_FILES` and Defender key as `documents`.

## Browser API and events

Components initialize automatically, including markup inserted later. The
instance remains available through:

```js
const uploader = fusionFileinput('#documents');
uploader.clear();
uploader.cancel(0);
```

Callbacks can be attached through the same API:

```js
fusionFileinput('#documents', {
    onDrop(detail) {},
    onBlur(detail) {},
    onCancel(detail) {},
    onSubmit(detail) {}
});
```

Every callback also has a bubbling, cancelable DOM event:

- `fusionFileinput:drop`
- `fusionFileinput:blur`
- `fusionFileinput:cancel`
- `fusionFileinput:submit`
- `fusionFileinput:change`
- `fusionFileinput:validate`
- `fusionFileinput:error`

Preventing `fusionFileinput:submit`, or returning `false` from `onSubmit`,
prevents the enclosing form submission.

Set `remote_check` to `FALSE` when a form intentionally relies on submit-time
validation only. `remote_required` controls whether a network failure blocks
submission. The default core endpoint is `file-upload-check` / route
`/api/v1/files/preflight`.
