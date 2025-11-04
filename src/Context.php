<?php

namespace Exowpee\LaravelFlow;

use Exowpee\LaravelFlow\Exceptions\IsolationException;

final class Context
{
    private array $coreData = [];
    private array $pluginData = [];
    private ?string $currentPlugin = null;

    public function __construct(array $initial = [])
    {
        $this->coreData = $initial;
    }

    // ═══════════════════════════════════════════════════════════
    // Magic Methods : Accès aux données CORE uniquement
    // ═══════════════════════════════════════════════════════════

    public function __get(string $key): mixed
    {
        return $this->coreData[$key] ?? null;
    }

    public function __set(string $key, mixed $value): void
    {
        if ($this->currentPlugin !== null) {
            throw new IsolationException(
                "Plugin '{$this->currentPlugin}' cannot modify core data '{$key}'. " .
                "Use _metadata('{$key}', \$value) instead."
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
        if ($this->currentPlugin !== null) {
            throw new IsolationException(
                "Plugin '{$this->currentPlugin}' cannot unset core data."
            );
        }

        unset($this->coreData[$key]);
    }

    // ═══════════════════════════════════════════════════════════
    // Metadata : Zone isolée pour plugins avec auto-prefix
    // ═══════════════════════════════════════════════════════════

    /**
     * Accès aux données plugin
     * 
     * Usage:
     *   $context->_metadata('logged')   → Get from plugin
     * 
     *   $context->_metadata('test.logged')   → Get from core
     * 
     *   $context->_metadata('logged', true) → Set from plugin
     */
    public function _metadata(string $key, mixed $value = null): mixed
    {
        if (func_num_args() === 2) {
            return $this->setMetadata($key, $value);
        }

        return $this->getMetadata($key);
    }

    /**
     * Set metadata avec isolation stricte
     */
    private function setMetadata(string $key, mixed $value): self
    {
        // Core ne peut pas écrire dans pluginData
        if ($this->currentPlugin === null) {
            throw new IsolationException(
                "Core context cannot write to plugin metadata. " .
                "Use \$context->property = \$value for core data."
            );
        }

        // Interdire les points dans les clés pour garantir l'isolation
        if (str_contains($key, '.')) {
            throw new IsolationException(
                "Plugin '{$this->currentPlugin}' cannot use '.' in metadata keys. " .
                "Use simple keys like 'logged' (auto-prefixed to '{$this->currentPlugin}.logged')."
            );
        }

        // Auto-prefix et stockage
        $prefixedKey = "{$this->currentPlugin}.{$key}";
        $this->pluginData[$prefixedKey] = $value;

        return $this;
    }

    /**
     * Get metadata avec auto-prefix intelligent
     */
    private function getMetadata(string $key): mixed
    {
        if ($this->currentPlugin === null) {
            throw_unless(str_contains($key, '.'), "Invalid key: '.' is missing");
            return $this->pluginData[$key] ?? null;
        }

        if (str_contains($key, '.')) {
            throw new IsolationException(
                "Cannot use '.' in metadata keys. " .
                "Plugin '{$this->currentPlugin}': use simple keys like 'logged' (auto-prefixed to '{$this->currentPlugin}.logged')."
            );
        }

        $prefixedKey = "{$this->currentPlugin}.{$key}";
        return $this->pluginData[$prefixedKey] ?? null;
    }

    // ═══════════════════════════════════════════════════════════
    // Internal : Mode Switching (usage par FlowManager uniquement)
    // ═══════════════════════════════════════════════════════════

    /**
     * @internal
     */
    public function _enterPluginContext(string $pluginName): void
    {
        $this->currentPlugin = $pluginName;
    }

    /**
     * @internal
     */
    public function _exitPluginContext(): void
    {
        $this->currentPlugin = null;
    }

    // ═══════════════════════════════════════════════════════════
    // Utilitaires
    // ═══════════════════════════════════════════════════════════

    /**
     * Retourne toutes les données (core + plugins groupés)
     */
    public function all(): array
    {
        $groupedPlugins = [];
        foreach ($this->pluginData as $key => $value) {
            if (str_contains($key, '.')) {
                [$plugin, $field] = explode('.', $key, 2);
                $groupedPlugins[$plugin][$field] = $value;
            } else {
                // Clé sans namespace (ne devrait pas arriver)
                $groupedPlugins['_ungrouped'][$key] = $value;
            }
        }

        return [
            'core' => $this->coreData,
            'plugins' => $this->currentPlugin
                ? ($groupedPlugins[$this->currentPlugin] ?? [])
                : $groupedPlugins
        ];
    }

    /**
     * Retourne uniquement les données core
     */
    public function core(): array
    {
        return $this->coreData;
    }
}