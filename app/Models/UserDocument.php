<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class UserDocument extends Model
{

    protected $fillable = ['student_visibility'];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id')->withTrashed();
    }

    public function uploadedBy()
    {
        return $this->belongsTo('App\User', 'uploaded_by', 'id')->withTrashed();
    }

    public static function getDocumentTypeEnumValues()
    {
        $table = 'user_documents';
        $field = 'type';
        $typeResult = DB::select(DB::raw( "SHOW COLUMNS FROM {$table} WHERE Field = '{$field}'" ));
        $enum = array();
        if (!empty($typeResult)) {
            $type = $typeResult[0]->Type;
            preg_match("/^enum\(\'(.*)\'\)$/", $type, $matches);
            if (!empty($matches)) {
                $enumValues = explode("','", $matches[1]);
                foreach($enumValues as $enumValue) {
                    $enum[$enumValue] = str_replace("-", " ", $enumValue);
                }
            }
        } else {
            $enumValues = config('students.document_types');
            if (!empty($enumValues)) {
                foreach($enumValues as $enumValue) {
                    $enum[str_replace(" ", "-", $enumValue)] = $enumValue;
                }
            } else {
                $enum = [];
            }
        }
        return $enum;
    }

}
