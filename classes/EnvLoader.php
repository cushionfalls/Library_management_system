<?php
/**
 * EnvLoader — Minimal .env file parser.
 *
 * Reads a .env file and loads its KEY=VALUE pairs into putenv() and $_ENV.
 * Supports:
 *   - Lines in the format KEY=VALUE
 *   - Quoted values (single or double quotes are stripped)
 *   - Comments (lines starting with #)
 *   - Blank lines (ignored)
 *
 * Usage:
 *   EnvLoader::load(__DIR__ . '/../.env');
 *   $key = EnvLoader::get('GROQ_API_KEY');
 */
class EnvLoader
{
    /** @var bool Whether the .env file has already been loaded. */
    private static $loaded = false;

    /**
     * Load a .env file into the environment.
     *
     * @param string $filePath Absolute path to the .env file.
     * @return void
     */
    public static function load(string $filePath): void
    {
        // Prevent double-loading in the same request
        if (self::$loaded) {
            return;
        }

        if (!file_exists($filePath) || !is_readable($filePath)) {
            // Silently skip if no .env file — production may use real env vars
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            // Split on the first '=' only
            $eqPos = strpos($line, '=');
            if ($eqPos === false) {
                continue;
            }

            $key   = trim(substr($line, 0, $eqPos));
            $value = trim(substr($line, $eqPos + 1));

            // Strip surrounding quotes (single or double)
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last  = $value[strlen($value) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $value = substr($value, 1, -1);
                }
            }

            // Set into the environment
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }

        self::$loaded = true;
    }

    /**
     * Retrieve an environment variable value.
     *
     * @param string      $key     The variable name.
     * @param string|null $default Fallback value if not found.
     * @return string|null
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }
        return $_ENV[$key] ?? $default;
    }
}
?>
