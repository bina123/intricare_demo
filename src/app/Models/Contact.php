<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'profile_image',
        'additional_file',
        'notes',
        'merged_into'
    ];
    protected $with = ['customFieldValues']; // auto-load on fetch if needed

    public function customFieldValues()
    {
        return $this->hasMany(ContactCustomFieldValue::class);
    }

    public function mergedInto()
    {
        return $this->belongsTo(Contact::class, 'merged_into');
    }

    public function mergedContacts()
    {
        return $this->hasMany(Contact::class, 'merged_into');
    }
}
