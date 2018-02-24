# Phar Maker

Ark provides a simple tool to make phar package.
It packages one directory into phar and make boot stub for running.

## Code Sample

The following code would build `ArkTestSuit.phar` in the output directory,
and make `runner.php` inside as handler for phar file.

```php
$pm = new \sinri\ark\phar\PharMaker();
$pm->setPharName("ArkTestSuit");
$pm->setDirectory('~/projects/sample_cli_program');
$pm->setOutputDirectory('~/projects/output_phar');
$pm->setBootstrapStubAsCLIEntrance("runner.php");// it is a relative path inside the package
//$pm->addExtension("php");// this is not necessary for php but an example, but you might use this for other file types
//$pm->addExcludeEntrance("composer.phar");
$pm->archive();
```