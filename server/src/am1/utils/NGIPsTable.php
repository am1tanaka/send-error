<?php

namespace Am1\Utils;

/** NGテーブル*/
class NGIPsTable extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'ng_ips';
    protected $guarded = array('id');
}
