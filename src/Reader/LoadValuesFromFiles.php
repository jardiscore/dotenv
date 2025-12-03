<?php

declare(strict_types=1);

namespace JardisCore\DotEnv\Reader;

use Exception;
use JardisCore\DotEnv\Casting\CastTypeHandler;

/**
 * Reads and return all values from given files
 */
class LoadValuesFromFiles
{
    private CastTypeHandler $castTypeHandler;

    public function __construct(CastTypeHandler $castTypeHandler)
    {
        $this->castTypeHandler = $castTypeHandler;
    }

    /**
     * @param array<string> $files
     * @param bool|null $public
     * @return array<string, mixed>
     * @throws Exception
     */
    public function __invoke(array $files, ?bool $public = true): array
    {
        $envValues = [];
        $public = $public ?? true;

        foreach ($files as $file) {
            if (file_exists($file)) {
                $rows = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if ($rows !== false) {
                    $envValues = array_merge($envValues, $this->loadFileValues($rows, $public));
                }
            }
        }

        return $envValues;
    }

    /**
     * @param array<string> $rows
     * @return array<string, mixed>
     * @throws Exception
     */
    protected function loadFileValues(array $rows, ?bool $public = true): array
    {
        $result = [];

        foreach ($rows as $row) {
            if (strpos(trim($row), '#') === 0) {
                continue;
            }

            list($key, $value) = explode('=', $row, 2);
            $key = trim($key);
            $value = $value ? trim($value) : $value;

            $typeCastValue = ($this->castTypeHandler)($value);

            if ($public) {
                $this->publish($key, $value, $typeCastValue);
            } else {
                $result[$key] = $typeCastValue;
            }
        }

        return $result;
    }

    /**
     * @param string $key
     * @param string $value
     * @param mixed $castValue
     */
    protected function publish(string $key, string $value, mixed $castValue): void
    {
        $value = is_array($castValue) ? $value : $castValue;
        putenv("$key=$value");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
