<?php

namespace App;

use ArrayAccess;
use Illuminate\Support\Arr;

class DataContainer implements ArrayAccess
{
    /**
     * The DataContainer attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Create a new DataContainer instance.
     *
     * @param array|object $attributes
     *
     * @return void
     */
    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }
    }

    /**
     * Get all of the DataContainer data.
     *
     * @return array
     */
    public function all()
    {
        return $this->attributes;
    }

    /**
     * Checks if a key exists.
     *
     * @param string|array $key
     *
     * @return bool
     */
    public function exists($key)
    {
        return ! collect(is_array($key) ? $key : func_get_args())->contains(function ($key) {
            return ! Arr::exists($this->attributes, $key);
        });
    }

    /**
     * Checks if a key is present and not null.
     *
     * @param string|array $key
     *
     * @return bool
     */
    public function has($key)
    {
        return ! collect(is_array($key) ? $key : func_get_args())->contains(function ($key) {
            return is_null($this->get($key));
        });
    }

    /**
     * Get an item from the DataContainer.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return Arr::get($this->attributes, $key, $default);
    }

    /**
     * Get the value of a given key and then forget it.
     *
     * @param string $key
     * @param string $default
     *
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return Arr::pull($this->attributes, $key, $default);
    }

    /**
     * Replace the given DataContainer attributes entirely.
     *
     * @param array $attributes
     *
     * @return void
     */
    public function replace(array $attributes)
    {
        $this->put($attributes);
    }

    /**
     * Put a key / value pair or array of key / value pairs in the DataContainer.
     *
     * @param string|array $key
     * @param mixed        $value
     *
     * @return void
     */
    public function put($key, $value = null)
    {
        if (! is_array($key)) {
            $key = [$key => $value];
        }

        foreach ($key as $arrayKey => $arrayValue) {
            Arr::set($this->attributes, $arrayKey, $arrayValue);
        }
    }

    /**
     * Push a value onto a DataContainer array.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function push($key, $value)
    {
        $array = $this->get($key, []);

        $array[] = $value;

        $this->put($key, $array);
    }

    /**
     * Increment the value of an item in the DataContainer.
     *
     * @param string $key
     * @param int    $amount
     *
     * @return mixed
     */
    public function increment($key, $amount = 1)
    {
        $this->put($key, $value = $this->get($key, 0) + $amount);

        return $value;
    }

    /**
     * Decrement the value of an item in the DataContainer.
     *
     * @param string $key
     * @param int    $amount
     *
     * @return int
     */
    public function decrement($key, $amount = 1)
    {
        return $this->increment($key, $amount * -1);
    }

    /**
     * Remove one or many items from the DataContainer.
     *
     * @param string|array $keys
     *
     * @return void
     */
    public function forget($keys)
    {
        Arr::forget($this->attributes, $keys);
    }

    /**
     * Remove all of the items from the DataContainer.
     *
     * @return void
     */
    public function flush()
    {
        $this->attributes = [];
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->{$offset});
    }

    /**
     * Get the value for a given offset.
     *
     * @param string $offset
     *
     * @return mixed
     */
    public function &offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * Set the value at the given offset.
     *
     * @param string $offset
     * @param mixed  $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    /**
     * Unset the value at the given offset.
     *
     * @param string $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }

    /**
     * Dynamically retrieve the value of an attribute.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function &__get($key)
    {
        return $this->attributes[$key];
    }

    /**
     * Dynamically set the value of an attribute.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    /**
     * Dynamically check if an attribute is set.
     *
     * @param string $key
     *
     * @return bool
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Dynamically unset an attribute.
     *
     * @param string $key
     *
     * @return void
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }
}
