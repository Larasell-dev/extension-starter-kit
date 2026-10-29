<?php

/*
 * Renames this template to your extension, then deletes itself:
 *
 *     php configure.php --vendor=acme --name=wishlists
 *     php configure.php --vendor=acme --name=wishlists --namespace="Acme\Wishlists"
 *
 * Without options it asks for each value.
 */

$options = getopt('', ['vendor:', 'name:', 'namespace:']);

$ask = function (string $question, ?string $default = null): string {
    $answer = trim((string) readline($question.($default !== null ? " [{$default}]" : '').': '));

    return $answer !== '' ? $answer : (string) $default;
};

$studly = fn (string $value): string => str_replace(' ', '', ucwords(preg_replace('/[^a-z0-9]+/i', ' ', $value)));

$vendor = strtolower($options['vendor'] ?? $ask('Composer vendor, e.g. acme'));
$name = strtolower($options['name'] ?? $ask('Extension name, e.g. wishlists'));

if (! preg_match('/^[a-z0-9]([_.-]?[a-z0-9]+)*$/', $vendor) || ! preg_match('/^[a-z0-9]([_.-]?[a-z0-9]+)*$/', $name)) {
    fwrite(STDERR, "The vendor and name must be valid Composer package name parts, e.g. acme/wishlists.\n");
    exit(1);
}

$namespace = trim($options['namespace'] ?? $ask('Namespace', $studly($vendor).'\\'.$studly($name)), '\\');
$class = $studly($name);
$slug = str_replace(['_', '.'], '-', $name);

$replacements = [
    'larasell-dev/extension-starter-kit' => "{$vendor}/{$name}",
    'Larasell\\\\Extension' => str_replace('\\', '\\\\', $namespace),
    'Larasell\\Extension' => $namespace,
    'ExtensionServiceProvider' => "{$class}ServiceProvider",
    'larasell.extension' => "larasell.{$slug}",
    'larasell/extension.php' => "larasell/{$slug}.php",
    'larasell-extension-config' => "larasell-{$slug}-config",
    'Larasell Extension Template' => ucwords(str_replace('-', ' ', $slug)),
];

$root = __DIR__;
$skip = ['vendor', 'node_modules', '.git'];
$files = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    fn (SplFileInfo $file): bool => ! in_array($file->getFilename(), $skip, true),
));

foreach ($files as $file) {
    $path = $file->getPathname();

    if ($path === __FILE__ || ! $file->isFile()) {
        continue;
    }

    $contents = file_get_contents($path);
    $updated = strtr($contents, $replacements);

    // Drops the README's instructions for using the template.
    $updated = preg_replace('/<!-- template:start -->.*?<!-- template:end -->\n*/s', '', $updated);

    if ($updated !== $contents) {
        file_put_contents($path, $updated);
    }
}

rename("{$root}/src/ExtensionServiceProvider.php", "{$root}/src/{$class}ServiceProvider.php");
rename("{$root}/config/larasell/extension.php", "{$root}/config/larasell/{$slug}.php");
unlink(__FILE__);

// composer create-project writes a lock file for the old name.
@unlink("{$root}/composer.lock");

echo "Configured {$vendor}/{$name} in {$namespace}.\n";
// Renaming can change the order of use statements, which Pint fixes.
echo "Next: composer install && composer lint && composer test\n";
