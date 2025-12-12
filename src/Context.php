<?php

namespace Exowpee\LaravelFlow;

use Exowpee\LaravelFlow\Exceptions\IsolationException;

final class Context
{
    private array $moduleData = [];

    private array $moduleStack = [];

    public function __construct(private array $coreData = []) {}

    // ═══════════════════════════════════════════════════════════
    // Magic Methods : Accès aux données CORE uniquement
    // ═══════════════════════════════════════════════════════════

    public function __get(string $key): mixed
    {
        return $this->coreData[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        if ($this->currentModule() !== null) {
            throw new IsolationException(
                "Module '{$this->currentModule()}' cannot modify core data '{$key}'. ".
                "Use metadata('{$key}', \$value) instead."
            );
        }

        $this->coreData[$key] = $value;
    }

    public function __isset(string $key): bool
    {
        return isset($this->coreData[$key]);
    }

    public function __unset(string $key): void
    {
        if ($this->currentModule() !== null) {
            throw new IsolationException(
                "Module '{$this->currentModule()}' cannot unset core data."
            );
        }

        unset($this->coreData[$key]);
    }

    // ═══════════════════════════════════════════════════════════
    // Metadata : Zone isolée pour modules avec auto-prefix
    // ═══════════════════════════════════════════════════════════

    public function metadata(string $key, mixed $value = null): mixed
    {
        if (func_num_args() === 2) {
            return $this->setMetadata($key, $value);
        }

        return $this->getMetadata($key);
    }

    private function setMetadata(string $key, mixed $value): self
    {
        $currentModule = $this->currentModule();

        if ($currentModule === null) {
            throw new IsolationException(
                'Core context cannot write to module metadata. '.
                'Use $context->property = $value for core data.'
            );
        }

        if (str_contains($key, '.')) {
            throw new IsolationException(
                "Module '{$currentModule}' cannot use '.' in metadata keys. ".
                "Use simple keys like 'logged' (auto-prefixed to '{$currentModule}.logged')."
            );
        }

        $prefixedKey = "{$currentModule}.{$key}";
        $this->moduleData[$prefixedKey] = $value;

        return $this;
    }

    private function getMetadata(string $key): mixed
    {
        $currentModule = $this->currentModule();

        if ($currentModule === null) {
            throw_unless(str_contains($key, '.'), "Invalid key: '.' is missing");

            return $this->moduleData[$key] ?? null;
        }

        if (str_contains($key, '.')) {
            throw new IsolationException(
                "Cannot use '.' in metadata keys. ".
                "Module '{$currentModule}': use simple keys like 'logged' (auto-prefixed to '{$currentModule}.logged')."
            );
        }

        $prefixedKey = "{$currentModule}.{$key}";

        return $this->moduleData[$prefixedKey] ?? null;
    }

    // ═══════════════════════════════════════════════════════════
    // Internal : Mode Switching avec pile (usage par FlowManager)
    // ═══════════════════════════════════════════════════════════

    /**
     * @internal
     */
    public function _enterModuleContext(string $moduleName): void
    {
        $this->moduleStack[] = $moduleName;
    }

    /**
     * @internal
     */
    public function _exitModuleContext(): void
    {
        if ($this->moduleStack === []) {
            throw new IsolationException(
                'Cannot exit module context: stack is empty.'
            );
        }

        array_pop($this->moduleStack);
    }

    /**
     * Retourne le module actuellement actif (sommet de la pile)
     */
    private function currentModule(): ?string
    {
        return $this->moduleStack === []
            ? null
            : end($this->moduleStack);
    }

    // ═══════════════════════════════════════════════════════════
    // Utilitaires
    // ═══════════════════════════════════════════════════════════

    public function core(): array
    {
        return $this->coreData;
    }

    public function modules(): array
    {
        $groupedModules = [];
        foreach ($this->moduleData as $key => $value) {
            if (str_contains((string) $key, '.')) {
                [$module, $field] = explode('.', (string) $key, 2);
                $groupedModules[$module][$field] = $value;
            } else {
                $groupedModules['_ungrouped'][$key] = $value;
            }
        }

        return $this->currentModule()
                ? ($groupedModules[$this->currentModule()] ?? [])
                : $groupedModules;
    }

    public function all(): array
    {
        return [
            'core'    => $this->core(),
            'modules' => $this->modules(),
        ];
    }

    public function getFromModules(string $key): array
    {
        $datas = [];
        $modules = $this->modules();
        foreach ($modules as $module) {
            if (isset($module[$key]) && is_array($module[$key])) {
                $datas = array_merge($datas, $module[$key]);
            }
        }

        return $datas;
    }

}