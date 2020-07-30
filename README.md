# Data Storage PHP

Простое хранилище данных в виде ключ-значение в JSON-файлах 
с разделяемой блокировкой на чтение и эксклюзивной блокировкой на запись.

## Содержание

<!-- MarkdownTOC levels="1,2,3,4,5,6" autoanchor="true" autolink="true" -->

- [Требования](#%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F)
- [Установка](#%D0%A3%D1%81%D1%82%D0%B0%D0%BD%D0%BE%D0%B2%D0%BA%D0%B0)
- [Класс `FileStorage`](#%D0%9A%D0%BB%D0%B0%D1%81%D1%81-filestorage)
- [Примеры](#%D0%9F%D1%80%D0%B8%D0%BC%D0%B5%D1%80%D1%8B)
- [Автор](#%D0%90%D0%B2%D1%82%D0%BE%D1%80)
- [Лицензия](#%D0%9B%D0%B8%D1%86%D0%B5%D0%BD%D0%B7%D0%B8%D1%8F)

<!-- /MarkdownTOC -->

<a id="%D0%A2%D1%80%D0%B5%D0%B1%D0%BE%D0%B2%D0%B0%D0%BD%D0%B8%D1%8F"></a>
## Требования

- PHP >= 7.0.
- Трейт [`\App\Utils\JsonUtils`](https://github.com/andrey-tech/utils-php), содержащий методы для работы c данными в формате JSON.
- Произвольный автозагрузчик классов, реализующий стандарт [PSR-4](https://www.php-fig.org/psr/psr-4/).

<a id="%D0%A3%D1%81%D1%82%D0%B0%D0%BD%D0%BE%D0%B2%D0%BA%D0%B0"></a>
## Установка

Установка через composer:
```
$ composer require andrey-tech/data-storage-php
```

или добавить

```
"andrey-tech/data-storage-php"
```

в секцию require файла composer.json.

<a id="%D0%9A%D0%BB%D0%B0%D1%81%D1%81-filestorage"></a>
## Класс `FileStorage`

Работа с хранилищами производится с помощью класса `\App\DataStorage\FileStorage`.  
При возникновении ошибок выбрасывается исключение с объектом класса `\App\DataStorage\FileStorageException`. 

Класс `\App\DataStorage\FileStorage` имеет следующие публичные методы:

- `__construct(string $storageName = 'storage', string $storageDir = 'storage/')` Конструктор класса-хранилища.
    * `$storageName` - имя хранилища. Должно удовлетворять регулярному выражению `'/^[\w\.-]+$/i`;
    * `$storageDir` - каталог, в котором будут располагаться JSON-файлы хранилища.
- `set(array $set) :void` Устанавливает в хранилище значения по ключам.
    * `$set` - ассоциативный массив ключей и значений: `[ 'key1' => 'value1', 'key2' => 'value2',... ]`.
- `get(array|string $keys)` Возвращает из хранилища значение по ключу или значения по ключам.
    * `$keys` - ключ или массив ключей.
- `delete(array|string $keys) :void` Удаляет из хранилища пару(ы) ключ-значение по ключу(ам).
    * `$keys` - ключ или массив ключей.
- `load() :array` Загружает и возвращает все данные из хранилища в виде массива.
- `update(array $set = [], array $delete = []) :void` Обновляет и/или удаляет значения по ключам в хранилище.
    * `$set` - ассоциативный массив ключей и значений: `[ 'key1' => 'value1', 'key2' => 'value2',... ]`;
    * `$delete` - массив удаляемых ключей.
- `hasKey(string $key) :bool` Проверяет наличие пары ключ-значение в хранилище.
    * `$key` - имя ключа.
- `getStorageFileName() :string` Возвращает абсолютное имя JSON-файла хранилища.

<a id="%D0%9F%D1%80%D0%B8%D0%BC%D0%B5%D1%80%D1%8B"></a>
## Примеры

```php
use \App\DataStorage\FileStorage;
use \App\DataStorage\FileStorageException;
use \App\AppException;

try {

    $storage = new FileStorage('storage-1');

    $storage->set([
        'manager_id' => 2369305,
        'numbers'    => [ 4, 8, 15, 16, 23, 42 ],
        'error_time' => null,
        'user_ids'   => [ 'alex' => 23, 'bob' => 2 ],
        'months'     => [ '0' => [ 1, 4 ], '1' => [  1, 2, 5  ] ]
    ]);
    $storage->set([ 'group_id' => 94824 ]);

    var_dump($storage->hasKey('numbers'));

    print_r($storage->get('numbers'));
    print_r($storage->get([ 'manager_id', 'user_ids' ]));

    $storage->delete('group_id');

    $storage->update(
        $set = [ 'error_time' => 1596124230 ],
        $delete = [ 'manager_id' ]
    );

    print_r($storage->load());

} catch (FileStorageException $e) {
    printf('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
} catch (AppException $e) {
    printf('Ошибка (%d): %s' . PHP_EOL, $e->getCode(), $e->getMessage());
}
```

<a id="%D0%90%D0%B2%D1%82%D0%BE%D1%80"></a>
## Автор

© 2020 andrey-tech

<a id="%D0%9B%D0%B8%D1%86%D0%B5%D0%BD%D0%B7%D0%B8%D1%8F"></a>
## Лицензия

Данный код распространяется на условиях лицензии [MIT](./LICENSE).
