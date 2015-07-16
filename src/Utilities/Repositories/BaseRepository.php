<?php namespace SRLabs\Utilities\Repositories;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Logging\Log;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{

    /**
     * @var Model
     */
    protected $model;
    /**
     * @var string
     */
    protected $resourceName;
    /**
     * @var Repository
     */
    protected $cache;
    /**
     * @var Dispatcher
     */
    protected $dispatcher;
    /**
     * @var Log
     */
    protected $logger;

    /**
     * @param Model $model
     * @param Repository $cache
     * @param Dispatcher $dispatcher
     * @param Log $logger
     */
    public function __construct(
        Model $model,
        Repository $cache,
        Dispatcher $dispatcher,
        Log $logger
    ) {
        // DI Member Assignment
        $this->model      = $model;
        $this->cache      = $cache;
        $this->dispatcher = $dispatcher;
        $this->logger     = $logger;

        // Set the Resource Name
        $this->resourceName = $this->model->getTable();
        $this->className = get_class($model);
    }

    /**
     * Create a new model instance and store it in the database
     *
     * @param array $data
     * @return static
     */
    public function store(array $data)
    {
        // Do we need to create a reference id?
        if ($this->model->isFillable('ref') && !isset($data['ref'])) {
            $data['ref'] = $this->generateReferenceId();
        }

        // Create the new model object
        $model = $this->model->create($data);

        // Do we need to set a hash?
        if ($this->model->isFillable('hash')) {
            $model->hash = \Hashids::encode($model->id);
            $model->save();
        }

        // Return the new model object
        return $model;
    }

    /**
     * Update a Model Object Instance
     *
     * @param int|string $id
     * @param array $data
     * @return \Illuminate\Support\Collection|null|static
     */
    public function update($id, array $data)
    {
        // If the first parameter is a string, it is a Hashids hash
        if (is_string($id)) {
            $id = $this->decodeHash($id);
        }

        // Fetch the Model Object
        $model = $this->byId($id);

        // Set the new values on the Model Object
        foreach ($data as $key => $value) {
            if ($this->model->isFillable($key)) {
                $model->$key = $value;
            }
        }

        // Save the changes to the database
        $model->save();

        // Flush the cache
        $this->flush($model);

        // Return the updated model
        return $model;
    }

    /**
     * Delete a model object
     *
     * @param string|Model
     *
     * @return boolean
     */
    public function delete($model)
    {
        // Resolve the hash string if necessary
        $model = $this->resolveHash($model);

        //Flush the cache
        $this->flush($model);

        // Delete the order
        return $model->delete();
    }

    /**
     * Retrieve a single model object, using its id
     *
     * @param integer $id
     * @return null|Model
     */
    public function byId($id)
    {
        $key = $this->resourceName . '.id.' . (string)$id;

        return $this->cache->remember($key, 10, function () use ($id) {
            return $this->model->find($id);
        });
    }

    /**
     * Retrieve a single model, using its hash value
     *
     * @param $hash
     * @return null|Model
     */
    public function byHash($hash)
    {
        $id = $this->decodeHash($hash);

        if ($id) {
            $key = $this->resourceName . '.hash.' . $hash;

            return $this->cache->remember($key, 10, function() use ($id) {
                return $this->model->find($id);
            });
        }

        return null;
    }

    /**
     * Retrieve a model object by its reference value, if it has one
     *
     * @param $reference
     * @return null
     */
    public function byReference($reference)
    {
        if ($this->model->isFillable('ref')) {
            $key = $this->resourceName . '.ref.' . $reference;

            return $this->cache->remember($key, 10, function () use ($reference) {
                return $this->model->where('ref', $reference)->first();
            });
        } else {
            return null;
        }
    }

    /**
     * Determine if there is already an instance of a model with the given attributes
     *
     * @param array $attributes
     * @return bool
     */
    public function exists(array $attributes)
    {
        return $this->model->where($attributes)->exists();
    }

    /**
     * Flush the cache for this Model Object instance
     *
     * @param Model $model
     * @return void
     */
    public function flush(Model $model)
    {
        // Assemble Cache Keys
        $keys[] = $this->resourceName . '.hash.' . $model->hash;
        $keys[] = $this->resourceName . '.id.' . $model->id;

        // Some keys will not be available on all models
        if ($this->model->isFillable('ref')) {
            $keys[] = $this->resourceName . '.ref.' . $model->ref;
        }

        // Clear the cache for the given keys
        foreach ($keys as $key) {
            $this->cache->forget($key);
        }
    }

    /**
     * Return the Repository Model instance
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * A helper function for decoding Hashids
     *
     * @param $hash
     * @return null
     */
    protected function decodeHash($hash)
    {
        $decoded = \Hashids::decode($hash);

        if (is_array($decoded)) {
            return $decoded[0];
        } else {
            return null;
        }
    }

    /**
     * Convert hash string to model object, if necessary
     *
     * @param $model
     * @return Model|null
     */
    protected function resolveHash($model)
    {
        if (!($model instanceof $this->className)) {
            return $this->byHash($model);
        }

        return $model;
    }

    /**
     * Each repository will be responsible for implementing its own reference generator.
     *
     * @return string
     */
    abstract function generateReferenceId();
}