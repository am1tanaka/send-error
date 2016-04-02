<?php

namespace Am1\Utils;

/** 不正アクセステーブル*/
class InvalidAccessTable extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'invalid_access';
    protected $guarded = array('id');
}
