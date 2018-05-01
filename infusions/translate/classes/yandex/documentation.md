Usage:
Get your API key, place it in yandex_key.inc
````$xslt
use Yandex\Translate\Translator;
use Yandex\Translate\Exception;

try {
  $translator = new Translator($key);
  $translation = $translator->translate('Hello world', 'en-ru');

  echo $translation; // Привет мир

  echo $translation->getSource(); // Hello world;

  echo $translation->getSourceLanguage(); // en
  echo $translation->getResultLanguage(); // ru
} catch (Exception $e) {
  // handle exception
}
````