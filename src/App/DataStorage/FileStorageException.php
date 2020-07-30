<?php
/**
 * Класс FileStorageException. Обрабатывает исключения в классе \App\DataStorage\FileStorage
 *
 * @author    andrey-tech
 * @copyright 2019-2020 andrey-tech
 * @see https://github.com/andrey-tech/data-storage-php
 * @license   MIT
 *
 * @version 1.0.0
 *
 * v1.0.0 (16.07.2019) Начальный релиз
 *
 */

declare(strict_types = 1);

namespace App\DataStorage;

use Exception;

class FileStorageException extends Exception
{
    /**
     * Конструктор
     * @param string $message Сообщение об исключении
     * @param int $code Код исключения
     * @param Exception|null $previous Предыдущее исключение
     */
    public function __construct(string $message = '', $code = 0, Exception $previous = null)
    {
        parent::__construct("App FileStorage: " . $message, $code, $previous);
    }
}
