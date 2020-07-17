<?php
namespace Vgsite;

use OutOfRangeException;

/**
 * Implements methods to manage object properties.
 */
trait PropsTrait
{
    /** @var array Keys for loaded from DB table via mapper; Vals set in constructor. */
    // public const PROPS_KEYS = [];

    /**
     * @param int $id Id proprty; Set to -1 for prototype object.
     * @param array $props Multidimentional array with keys and values corresponding to a database table.
     */
    public function __construct(int $id=-1, array $props) {
        // DomainObject::__construct
        parent::__construct($id);
        foreach (static::PROPS_KEYS as $key) {
            $this->setProp($key, $props[$key]);
        }
    }

    public function getProp(string $key)
    {
        $this->assertPropKeyExists($key);

        return $this->{$key} ?? null;
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
        $this->assertPropKeyExists($key);
        $this->{$key} = $val;

        return $this;
    }

    public function assertPropKeyExists(string $key): void
    {
        if (!in_array($key, static::PROPS_KEYS)) {
            throw new OutOfRangeException(sprintf('%s does not have a property key `%s`.', static::class, $key));
        }
    }
}
