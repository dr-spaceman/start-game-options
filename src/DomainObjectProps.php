<?php

namespace Vgsite;

use InvalidArgumentException;
use OutOfRangeException;

/**
 * Implements methods to manage object properties.
 */
abstract class DomainObjectProps extends DomainObject
{
    /** @var array Keys for loaded from DB table via mapper; The first key should by Promary Key; Vals set in constructor. */
    public const PROPS_KEYS = [];

    /** @var array List of props required to have scalar values for object construction; Throws exception if not given. */
    public const PROPS_REQUIRED = [];

    /**
     * @param array $props Multidimentional array with keys and values corresponding to a database table.
     */
    public function __construct(array $props)
    {
        if (empty(static::PROPS_KEYS)) {
            throw new \Exception(get_called_class() . ' requires const PROPS_KEYS list.');
        }

        $this->assertRequiredProps($props);

        foreach ($props as $key => $val) {
            if ($this->hasPropKey($key)) {
                $this->setProp($key, $val);
            }
        }

        $id = $this->parseIdProp();

        // DomainObject::__construct
        parent::__construct($id);
    }

    public function getProp(string $key)
    {
        $this->assertHasPropKey($key);

        return $this->{$key};
    }

    public function getProps(): array
    {
        $props = array();
        foreach (static::PROPS_KEYS as $key) {
            $props[$key] = $this->{$key};
        }

        return $props;
    }

    public function setProp(string $key, $val): self
    {
        $this->assertHasPropKey($key);
        $this->{$key} = $val;

        return $this;
    }

    public function assertHasPropKey(string $key): void
    {
        if (!$this->hasPropKey($key)) {
            throw new OutOfRangeException(sprintf('%s does not have a property key `%s`.', static::class, $key));
        }
    }

    public function hasPropKey(string $key): bool
    {
        return in_array($key, static::PROPS_KEYS);
    }

    public function assertRequiredProps(array $props): void
    {
        if (null !== static::PROPS_REQUIRED) {
            foreach (static::PROPS_REQUIRED as $prop) {
                if (null === $props[$prop]) {
                    throw new InvalidArgumentException(
                        sprintf('%s object requires prop `%s` when constructing.', get_called_class(), $prop)
                    );
                }
            }
        }
    }

    public function parseIdProp(): int
    {
        $id_key_field = static::PROPS_KEYS[0];
        $id = (int) $this->getProp($id_key_field);
        if (empty($id)) {
            throw new InvalidArgumentException(
                sprintf('%s object requires ID prop `%s` when constructing.', get_called_class(), $id_key_field)
            );
        }

        return $id;
    }
}
