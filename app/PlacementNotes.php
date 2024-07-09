<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlacementNotes extends Model
{
    protected $table = 'placement_notes';
    protected $primaryKey = 'id';

    protected $hidden = [
        
    ];
    protected $guarded = [
        'id'
    ];
    
    static $placementType = [
        'fullTime'  =>  'Full Time',
        'partTime'  =>  'Part Time',
        'casual'    =>  'Casual',
        'other'     =>  'Other'
    ];
}
