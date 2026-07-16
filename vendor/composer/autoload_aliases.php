<?php

// Functions and constants

namespace {

}


namespace WooCommerceSerialNumbers {

    class AliasAutoloader
    {
        private string $includeFilePath;

        private array $autoloadAliases = array (
  'B8\\Models\\Model' => 
  array (
    'type' => 'class',
    'classname' => 'Model',
    'isabstract' => true,
    'namespace' => 'B8\\Models',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Model',
    'implements' => 
    array (
      0 => 'ArrayAccess',
    ),
  ),
  'B8\\Models\\Page' => 
  array (
    'type' => 'class',
    'classname' => 'Page',
    'isabstract' => false,
    'namespace' => 'B8\\Models',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Page',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Post' => 
  array (
    'type' => 'class',
    'classname' => 'Post',
    'isabstract' => false,
    'namespace' => 'B8\\Models',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Post',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Query' => 
  array (
    'type' => 'class',
    'classname' => 'Query',
    'isabstract' => false,
    'namespace' => 'B8\\Models',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Query',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Relations\\BelongsTo' => 
  array (
    'type' => 'class',
    'classname' => 'BelongsTo',
    'isabstract' => false,
    'namespace' => 'B8\\Models\\Relations',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Relations\\BelongsTo',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Relations\\BelongsToMany' => 
  array (
    'type' => 'class',
    'classname' => 'BelongsToMany',
    'isabstract' => false,
    'namespace' => 'B8\\Models\\Relations',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Relations\\BelongsToMany',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Relations\\HasMany' => 
  array (
    'type' => 'class',
    'classname' => 'HasMany',
    'isabstract' => false,
    'namespace' => 'B8\\Models\\Relations',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Relations\\HasMany',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Relations\\HasOne' => 
  array (
    'type' => 'class',
    'classname' => 'HasOne',
    'isabstract' => false,
    'namespace' => 'B8\\Models\\Relations',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Relations\\HasOne',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Relations\\HasOneThrough' => 
  array (
    'type' => 'class',
    'classname' => 'HasOneThrough',
    'isabstract' => false,
    'namespace' => 'B8\\Models\\Relations',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Relations\\HasOneThrough',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Relations\\MorphMany' => 
  array (
    'type' => 'class',
    'classname' => 'MorphMany',
    'isabstract' => false,
    'namespace' => 'B8\\Models\\Relations',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Relations\\MorphMany',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Relations\\MorphTo' => 
  array (
    'type' => 'class',
    'classname' => 'MorphTo',
    'isabstract' => false,
    'namespace' => 'B8\\Models\\Relations',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Relations\\MorphTo',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Relations\\MorphToMany' => 
  array (
    'type' => 'class',
    'classname' => 'MorphToMany',
    'isabstract' => false,
    'namespace' => 'B8\\Models\\Relations',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Relations\\MorphToMany',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Relations\\Relation' => 
  array (
    'type' => 'class',
    'classname' => 'Relation',
    'isabstract' => true,
    'namespace' => 'B8\\Models\\Relations',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Relations\\Relation',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Term' => 
  array (
    'type' => 'class',
    'classname' => 'Term',
    'isabstract' => false,
    'namespace' => 'B8\\Models',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Term',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\User' => 
  array (
    'type' => 'class',
    'classname' => 'User',
    'isabstract' => false,
    'namespace' => 'B8\\Models',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\User',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Utilities\\DateTime' => 
  array (
    'type' => 'class',
    'classname' => 'DateTime',
    'isabstract' => false,
    'namespace' => 'B8\\Models\\Utilities',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Utilities\\DateTime',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Utilities\\DateUtil' => 
  array (
    'type' => 'class',
    'classname' => 'DateUtil',
    'isabstract' => false,
    'namespace' => 'B8\\Models\\Utilities',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Utilities\\DateUtil',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Utilities\\StringUtil' => 
  array (
    'type' => 'class',
    'classname' => 'StringUtil',
    'isabstract' => false,
    'namespace' => 'B8\\Models\\Utilities',
    'extends' => 'WooCommerceSerialNumbers\\B8\\Models\\Utilities\\StringUtil',
    'implements' => 
    array (
    ),
  ),
  'Lib\\Container' => 
  array (
    'type' => 'class',
    'classname' => 'Container',
    'isabstract' => false,
    'namespace' => 'Lib',
    'extends' => 'WooCommerceSerialNumbers\\Lib\\Container',
    'implements' => 
    array (
      0 => 'ArrayAccess',
    ),
  ),
  'Lib\\Plugin' => 
  array (
    'type' => 'class',
    'classname' => 'Plugin',
    'isabstract' => true,
    'namespace' => 'Lib',
    'extends' => 'WooCommerceSerialNumbers\\Lib\\Plugin',
    'implements' => 
    array (
      0 => 'Lib\\PluginInterface',
    ),
  ),
  'Lib\\Settings' => 
  array (
    'type' => 'class',
    'classname' => 'Settings',
    'isabstract' => true,
    'namespace' => 'Lib',
    'extends' => 'WooCommerceSerialNumbers\\Lib\\Settings',
    'implements' => 
    array (
    ),
  ),
  'B8\\Models\\Traits\\AttributesTrait' => 
  array (
    'type' => 'trait',
    'traitname' => 'AttributesTrait',
    'namespace' => 'B8\\Models\\Traits',
    'use' => 
    array (
      0 => 'WooCommerceSerialNumbers\\B8\\Models\\Traits\\AttributesTrait',
    ),
  ),
  'B8\\Models\\Traits\\CacheableTrait' => 
  array (
    'type' => 'trait',
    'traitname' => 'CacheableTrait',
    'namespace' => 'B8\\Models\\Traits',
    'use' => 
    array (
      0 => 'WooCommerceSerialNumbers\\B8\\Models\\Traits\\CacheableTrait',
    ),
  ),
  'B8\\Models\\Traits\\HookableTrait' => 
  array (
    'type' => 'trait',
    'traitname' => 'HookableTrait',
    'namespace' => 'B8\\Models\\Traits',
    'use' => 
    array (
      0 => 'WooCommerceSerialNumbers\\B8\\Models\\Traits\\HookableTrait',
    ),
  ),
  'B8\\Models\\Traits\\RelationsTrait' => 
  array (
    'type' => 'trait',
    'traitname' => 'RelationsTrait',
    'namespace' => 'B8\\Models\\Traits',
    'use' => 
    array (
      0 => 'WooCommerceSerialNumbers\\B8\\Models\\Traits\\RelationsTrait',
    ),
  ),
  'Lib\\PluginInterface' => 
  array (
    'type' => 'interface',
    'interfacename' => 'PluginInterface',
    'namespace' => 'Lib',
    'extends' => 
    array (
      0 => 'WooCommerceSerialNumbers\\Lib\\PluginInterface',
    ),
  ),
);

        public function __construct()
        {
            $this->includeFilePath = __DIR__ . '/autoload_alias.php';
        }

        public function autoload($class)
        {
            if (!isset($this->autoloadAliases[$class])) {
                return;
            }
            switch ($this->autoloadAliases[$class]['type']) {
                case 'class':
                        $this->load(
                            $this->classTemplate(
                                $this->autoloadAliases[$class]
                            )
                        );
                    break;
                case 'interface':
                    $this->load(
                        $this->interfaceTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                case 'trait':
                    $this->load(
                        $this->traitTemplate(
                            $this->autoloadAliases[$class]
                        )
                    );
                    break;
                default:
                    // Never.
                    break;
            }
        }

        private function load(string $includeFile)
        {
            file_put_contents($this->includeFilePath, $includeFile);
            include $this->includeFilePath;
            file_exists($this->includeFilePath) && unlink($this->includeFilePath);
        }

        private function classTemplate(array $class): string
        {
            $abstract = $class['isabstract'] ? 'abstract ' : '';
            $classname = $class['classname'];
            if (isset($class['namespace'])) {
                $namespace = "namespace {$class['namespace']};";
                $extends = '\\' . $class['extends'];
                $implements = empty($class['implements']) ? ''
                : ' implements \\' . implode(', \\', $class['implements']);
            } else {
                $namespace = '';
                $extends = $class['extends'];
                $implements = !empty($class['implements']) ? ''
                : ' implements ' . implode(', ', $class['implements']);
            }
            return <<<EOD
                <?php
                $namespace
                $abstract class $classname extends $extends $implements {}
                EOD;
        }

        private function interfaceTemplate(array $interface): string
        {
            $interfacename = $interface['interfacename'];
            $namespace = isset($interface['namespace'])
            ? "namespace {$interface['namespace']};" : '';
            $extends = isset($interface['namespace'])
            ? '\\' . implode('\\ ,', $interface['extends'])
            : implode(', ', $interface['extends']);
            return <<<EOD
                <?php
                $namespace
                interface $interfacename extends $extends {}
                EOD;
        }
        private function traitTemplate(array $trait): string
        {
            $traitname = $trait['traitname'];
            $namespace = isset($trait['namespace'])
            ? "namespace {$trait['namespace']};" : '';
            $uses = isset($trait['namespace'])
            ? '\\' . implode(';' . PHP_EOL . '    use \\', $trait['use'])
            : implode(';' . PHP_EOL . '    use ', $trait['use']);
            return <<<EOD
                <?php
                $namespace
                trait $traitname { 
                    use $uses; 
                }
                EOD;
        }
    }

    spl_autoload_register([ new AliasAutoloader(), 'autoload' ]);
}
