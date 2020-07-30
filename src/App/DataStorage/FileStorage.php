<?php

/**
 * Класс FileStorage. Реализует простое хранилище данных в виде ключ-значение в JSON-файлах
 *
 * @author    andrey-tech
 * @copyright 2019-2020 andrey-tech
 * @see https://github.com/andrey-tech/data-storage-php
 * @license   MIT
 *
 * @version 1.1.0
 *
 * v1.0.0 (16.07.2020) Начальный релиз
 * v1.0.1 (19.07.2000) Исправления для трейта \App\Utils\JsonUtils
 * v1.1.0 (30.07.2000) Исправлена проверка пустых аргументов в методе update().
 *                     Добавлен метод hasKey().
 *                     Добавлена проверка имени хранилища.
 *
 */

declare(strict_types = 1);

namespace App\DataStorage;

use App\Utils\JsonUtils;

class FileStorage
{
    use JsonUtils;

    /**
     * Каталог для хранения файлов в хранилище
     * @var string
     */
    public $storageDir;

    /**
     * Имя хранилища
     * @var string
     */
    protected $storageName;

    /**
     * Регулярное выражение для проверки имени хранилища
     * @var string
     */
    protected $storageNameRegex = '/^[\w-]+$/i';

    /**
     * Конструктор
     * @param string $storageName Имя хранилища
     * @param string $storageDir Каталог для хранения файлов хранилища
     */
    public function __construct(string $storageName = 'storage', string $storageDir = 'storage/')
    {
        if (! preg_match($this->storageNameRegex, $storageName)) {
            throw new FileStorageException("Некорректное имя хранилища '{$storageName}': {$this->storageNameRegex}");
        }

        $this->storageName = $storageName;
        $this->storageDir = $storageDir;
    }

    /**
     * Возвращает значения по ключам
     * @param string|array $keys Имя ключа или массив имен ключей
     * @return mixed
     * @throws FileStorageException
     */
    public function get($keys)
    {
        $data = $this->load();

        if (is_array($keys)) {
            $result = [];
            foreach ($keys as $key ) {
                $result[ $key ] = $data[ $key ] ?? null;
            }
            return $result;
        }

        return $data[ $keys ] ?? null;
    }

    /**
     * Проверяет наличие пары ключ-значение в хранилище
     * @param string $key Имя ключа
     * @return bool
     * @throws FileStorageException
     */
    public function hasKey(string $key) :bool
    {
        $data = $this->load();
        return array_key_exists($key, $data);
    }

    /**
     * Устанавливает значения по ключам
     * @param array $set Набор данных для обновления [ 'key1' => 'value1', 'key2' => 'value2' ]
     * @return void
     * @throws FileStorageException
     */
    public function set(array $set)
    {
        $this->update($set);
    }

    /**
     * Удаляет пары ключ-значение по ключам
     * @param string|array $keys Имя ключа или массив имен ключей
     * @return void
     * @throws FileStorageException
     */
    public function delete($keys)
    {
        if (! is_array($keys)) {
            $keys = [ $keys ];
        }
        $this->update($set = [], $keys);
    }

    /**
     * Загружает и возвращает все все данные из хранилища
     * @return array|null
     * @throws FileStorageException
     */
    public function load()
    {
        $storageFile = $this->getStorageFileName();
        if (! is_file($storageFile)) {
            return null;
        }

        $fh = @fopen($storageFile, 'r');
        if ($fh === false) {
            throw new FileStorageException("Не удалось открыть файл '{$storageFile}'");
        }

        if (! flock($fh, LOCK_SH)) {
            throw new FileStorageException("Не удалось получить разделяемую блокировку файла '{$storageFile}'");
        }

        $fileSize = @filesize($storageFile);
        if ($fileSize === false) {
            throw new FileStorageException("Не удалось получить размер файла '{$storageFile}'");
        }

        if ($fileSize > 0) {
            $content = fread($fh, $fileSize);
            if ($content === false) {
                throw new FileStorageException("Не удалось прочитать файл '{$storageFile}'");
            }
            $data = $this->fromJson($content);
        } else {
            $data = [];
        }

        if (! flock($fh, LOCK_UN)) {
            throw new FileStorageException("Не удалось разблокировать файл '{$storageFile}'");
        }

        if (! fclose($fh)) {
            throw new FileStorageException("Не удалось закрыть файл '{$storageFile}'");
        }

        return $data;
    }

    /**
     * Обновляет или удаляет значения по ключам в хранилище
     * @param array $set Набор данных для обновления [ 'key1' => 'value1', 'key2' => 'value2' ]
     * @param array $delete Массив ключей для удаления данных [ 'key1', 'key2' ]
     * @return void
     * @throws FileStorageException
     */
    public function update(array $set = [], array $delete = [])
    {
        if (empty($set) && empty($delete)) {
            return;
        }

        $storageFile = $this->getStorageFileName();

        $fh = @fopen($storageFile, 'c+');
        if ($fh === false) {
            throw new FileStorageException("Не удалось открыть файл '{$storageFile}'");
        }

        if (! flock($fh, LOCK_EX)) {
            throw new FileStorageException("Не удалось получить эксклюзивную блокировку файла '{$storageFile}'");
        }

        $fileSize = @filesize($storageFile);
        if ($fileSize === false) {
            throw new FileStorageException("Не удалось получить размер файла '{$storageFile}'");
        }

        if ($fileSize > 0) {
            if (! copy($storageFile, $storageFile . '.bak')) {
                throw new FileStorageException("Не удалось создать резервную копию файла '{$storageFile}'");
            }

            $content = fread($fh, $fileSize);
            if ($content === false) {
                throw new FileStorageException("Не удалось прочитать файл '{$storageFile}'");
            }
            $data = $this->fromJson($content);
        } else {
            $data = [];
        }

        // Обновляем значения
        $data = array_replace($data, $set);

        // Удаляем значения
        $data = array_filter($data,
            function ($key) use ($delete) {
                return ! in_array($key, $delete);
            },
            ARRAY_FILTER_USE_KEY
        );

        $content = $this->toJson($data, [ JSON_PRETTY_PRINT ]);

        if (! ftruncate($fh, 0)) {
            throw new FileStorageException("Не удалось урезать файл '{$storageFile}'");
        }

        if (! rewind($fh)) {
            throw new FileStorageException("Не удалось сбросить курсор файлового указателя файла '{$storageFile}'");
        }

        if (! fwrite($fh, $content)) {
            throw new FileStorageException("Не удалось записать в файл '{$storageFile}'");
        }

        if (! flock($fh, LOCK_UN)) {
            throw new FileStorageException("Не удалось разблокировать файл '{$storageFile}'");
        }

        if (! fclose($fh)) {
            throw new FileStorageException("Не удалось закрыть файл '{$storageFile}'");
        }
    }

    /**
     * Возвращает абсолютное имя JSON-файла хранилища
     * @return string
     * @throws FileStorageException
     */
    public function getStorageFileName() :string
    {
        $storageDir = __DIR__ . DIRECTORY_SEPARATOR . $this->storageDir;

        // Проверяем наличие каталога хранилища (is_dir кешируется)
        if (! is_dir($storageDir)) {
               if (! mkdir($storageDir, $mode = 0755, $recursive = true)) {
                throw new FileStorageException("Не удалось рекурсивно создать каталог '{$storageDir}'");
            }
        }

        $storageDir = realpath($storageDir);
        $storageFile = $storageDir . DIRECTORY_SEPARATOR . $this->storageName . '.json';

        return $storageFile;
    }
}
