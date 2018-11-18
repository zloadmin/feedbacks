<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    static function addID($id)
    {
        $teacher = Teacher::find($id);
        if(!$teacher) {
            $teacher = new self();
            $teacher->id = $id;
            $teacher->save();
        }
    }
}
