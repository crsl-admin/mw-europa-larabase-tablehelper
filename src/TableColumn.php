<?php

namespace Plokko\LaravelTableHelper;

abstract class TableColumn
{
    protected array $props = [];

    public function __construct(
        public readonly string $name,
        public ?string $label,
        public bool $visible = true,
    ) {}

    public function __call($name, $arguments): self
    {
        $this->props[$name] = $arguments[0];

        return $this;
    }

    /**
     * Parse TableData adding informations to the query.
     */
    abstract public function parse(TableData &$data): void;
}
