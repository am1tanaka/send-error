<?php

namespace Am1\Utils;

/** 不正アクセステーブル*/
class InvalidAccessTable extends \Illuminate\Database\Eloquent\Model
{
    protected $table = TABLE_INVALID_ACCESS;
    protected $guarded = array('id');
}
