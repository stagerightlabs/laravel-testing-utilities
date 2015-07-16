<?php namespace Phylos\Repositories;

use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Create a new model instance and store it in the database
     *
     * @param array $data
     * @return static
     */
    public function store(array $data);

    /**
     * Update a Model Object Instance
     *
     * @param int|string $id
     * @param array $data
     * @return \Illuminate\Support\Collection|null|static
     */
    public function update($id, array $data);

    /**
     * Delete a model object
     *
     * @param string|Model
     *
     * @return boolean
     */
    public function delete($model);

    /**
     * Retrieve a single model object, using its id
     *
     * @param integer $id
     * @return null|Model
     */
    public function byId($id);

    /**
     * Retrieve a single model, using its hash value
     *
     * @param $hash
     * @return null|Model
     */
    public function byHash($hash);

    /**
     * Retrieve a model object by its reference value, if it has one
     *
     * @param $reference
     * @return null
     */
    public function byReference($reference);

    /**
     * Flush the cache for this Model Object instance
     *
     * @param Model $model
     * @return void
     */
    public function flush(Model $model);

    /**
     * Return the Repository Model instance
     * @return Model
     */
    public function getModel();

    /**
     * Determine if there is already an instance of a model with the given attributes
     *
     * @param array $attributes
     * @return bool
     */
    public function exists(array $attributes);

    /**
     * Each repository will be responsible for implementing its own reference generator.
     *
     * @return string
     */
    function generateReferenceId();
}