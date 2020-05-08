<?php

namespace Am1\Utils;

/** NGテーブル*/
class NGIPsTable extends \Illuminate\Database\Eloquent\Model
{
    protected $table = TABLE_NG_IPS;
    protected $guarded = array('id');
}
