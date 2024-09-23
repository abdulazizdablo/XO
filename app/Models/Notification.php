<?php

namespace App\Models;

use App\Traits\TranslateFields;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Notification extends Model
{
    use HasFactory, SoftDeletes;
	use HasTranslations, TranslateFields;


	public $translatable = ['title','body'];

	
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body'
    ];
	
	    

    public function user(){
        return $this->belongsTo(User::class);
    } 
	
    public function employee(){
        return $this->belongsTo(Employee::class);
    } 
}
