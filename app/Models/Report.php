<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DateScope;
use LaracraftTech\LaravelDateScopes\DateScopes;

class Report extends Model
{
    use HasFactory;
    use DateScope;
    use DateScopes;

    protected $fillable = [
        'employee_id',
        'user_id',
        'order_id',
        'reply_by_id',
        'content',
        'reply',
        'rate',
        'type',
        'inventory_id',
        'status',
        'sender_role',
        'duration',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function employee(){
        return $this->belongsTo(Employee::class);
    }
    
    public function replyEmployee(){
        return $this->belongsTo(Employee::class, 'reply_by_id');    
    }

    public function order(){
        return $this->belongsTo(Order::class);
    }

}
