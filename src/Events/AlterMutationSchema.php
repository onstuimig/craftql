<?php

namespace markhuot\CraftQL\Events;

use markhuot\CraftQL\Builders\Schema;
use yii\base\Event;

class AlterMutationSchema extends Event {

    const EVENT = 'craftQlAlterMutationSchema';

    /**
     * The schema to build
     *
     * @var Schema
     */
    public $mutation;

}
