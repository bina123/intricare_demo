<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMergeLog extends Model
{
    protected $fillable = [
        'master_contact_id',
        'secondary_contact_id',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function master()
    {
        return $this->belongsTo(Contact::class, 'master_contact_id');
    }

    public function secondary()
    {
        return $this->belongsTo(Contact::class, 'secondary_contact_id');
    }
}
