<?php

namespace Cygnis\Data\Repositories;

use Illuminate\Support\Facades\Cache;

use Cygnis\Data\Contracts\RepositoryContract;

use Carbon\Carbon;
use DB;

/**
 * @author Usaama Effendi <usaamaeffendi@gmail.com>
 */
abstract class AbstractRepository implements RepositoryContract {

    protected $builder;
    /**
     *
     * This method will create a new model
     * and will return output back to client as json
     *
     * @access public
     * @return mixed
     *
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     *
     **/

    public function create(array $data = []) {

        $newInstance = $this->model->newInstance();
        foreach ($data as $column => $value) {
            $newInstance->{$column} = $value;
        }
        $newInstance->created_at = Carbon::now();
        $newInstance->updated_at = Carbon::now();
        if ($newInstance->save()) {
            Cache::forget($this->_cacheTotalKey);
            return $this->findById($newInstance->id, true);
        } else {
            return false;
        }
    }

    /**
     *
     * This method will fetch single model
     * and will return output back to client as json
     *
     * @access public
     * @return mixed
     *
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     *
     **/
    public function findById($id, $refresh = false, $details = false, $encode = true) {
        $data = Cache::get($this->_cacheKey.$id);

        if ($data == NULL || $refresh == true) {
            $query = $this->model->find($id);
            if ($query != NULL) {

                $data = new \stdClass;
                foreach ($query->getAttributes() as $column => $value) {
                    $data->{$column} = $query->{$column};
                }
                Cache::forever($this->_cacheKey.$id, $data);
            } else {
                return null;
            }
        }
        return $data;
    }

    /**
     *
     * This method will fetch single model by attribute
     * and will return output back to client as json
     *
     * @access public
     * @return mixed
     *
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     *
     **/
    public function findByAttribute($attribute, $value, $refresh = false, $details = false, $encode = true) {
        $model = $this->model->newInstance()
                        ->where($attribute, '=', $value)->first(['id']);

        if ($model != NULL) {
            $model = $this->findById($model->id, $refresh, $details, $encode);
        }
        return $model;
    }

    /**
     *
     * This method will fetch random model
     * and will return output back to client as json
     *
     * @access public
     * @return mixed
     *
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     *
     **/
    public function findRandom($refresh = false, $details = false, $encode = true) {
        $model = $this->model->newInstance()
                        ->inRandomOrder()->first();

        if ($model != NULL) {
            $model = $this->findById($model->id, $refresh, $details, $encode);
        }
        return $model;
    }

    /**
     *
     * This method will fetch all exsiting models
     * and will return output back to client as json
     *
     * @access public
     * @return mixed
     *
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     *
     **/
    public function findByAll($pagination = false, $perPage = 10, array $input = [] ) {
        $ids = $this->builder;

        if ($pagination == true) {

            $ids = $ids->paginate($perPage);
            $models = $ids->items();

        } else {
            $sql = $ids->toSql();
            $binds = $ids->getBindings();
            $models = DB::select($sql, $binds);
        }

        $data = ['data'=>[]];
        if ($models) {
            foreach ($models as &$model) {
                $model = $this->findById($model->id);
                if ($model) {
                    $data['data'][] = $model;
                }
            }
        }

        if ($pagination == true) {
            $data['pagination'] = [];
            $data['pagination']['total'] = $ids->total();
            $data['pagination']['current'] = $ids->currentPage();
            $data['pagination']['first'] = 1;
            $data['pagination']['last'] = $ids->lastPage();
            $data['pagination']['from'] = $ids->firstItem();
            $data['pagination']['to'] = $ids->lastItem();
            if($ids->hasMorePages()) {
                if ( $ids->currentPage() == 1) {
                    $data['pagination']['previous'] = -1;
                } else {
                    $data['pagination']['previous'] = $ids->currentPage()-1;
                }
                $data['pagination']['next'] = $ids->currentPage()+1;
            } else {
                $data['pagination']['previous'] = $ids->currentPage()-1;
                $data['pagination']['next'] =  $ids->lastPage();
            }
            if ($ids->lastPage() > 1) {
                $data['pagination']['pages'] = range(1,$ids->lastPage());
            } else {
                $data['pagination']['pages'] = [1];
            }
        }
        return $data;
    }

    /**
     *
     * This method will update an existing model
     * and will return output back to client as json
     *
     * @access public
     * @return mixed
     *
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     *
     **/
    public function update(array $data = []) {

        $model = $this->model->find($data['id']);
        if ($model != NULL) {
            foreach ($data as $column => $value) {
                $model->{$column} = $value;
            }
            $model->updated_at = Carbon::now();

            if ($model->save()) {

                return $this->findById($data['id'], true);
            }
            return false;
        }
        return NULL;
    }

    /**
     *
     * This method will remove model
     * and will return output back to client as json
     *
     * @access public
     * @return bool
     *
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     *
     **/
    public function deleteById($id) {
        $model = $this->model->find($id);
        if($model != NULL) {
            Cache::forget($this->_cacheKey.$id);
            Cache::forget($this->_cacheTotalKey);
            return $model->delete();
        }
        return false;
    }

    /**
     *
     * This method will fetch total models
     * and will return output back to client as json
     *
     * @access public
     * @return integer
     *
     * @author Usaama Effendi <usaamaeffendi@gmail.com>
     *
     **/
    public function findTotal($refresh = false) {

        $total = Cache::get($this->_cacheTotalKey);
        if ($total == NULL || $refresh == true) {
            $total = $this->model->count();
            Cache::forever($this->_cacheTotalKey, $total);
        }
        return $total;
    }

    public function getTranslationJson($data) {
        $language = app('language');
        $data = json_decode($data) ?: new \stdClass;

        if (isset($data->{$language})) {
            return $data->{$language};
        }
        return $data;
    }
}
