<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Auth;

class ManagerClientsIds implements Rule
{
    public function __construct($message = null)
    {

    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $manager = Auth::user();
        if(!$manager){
            return false;
        }

        if (!$manager->hasRole('manager')) {
            return false;
        }

        $clients = $manager->clients;
        $clients = $clients->filter(function ($client, $key) use($value) {
            if(is_array($value)){
                return in_array($client->id, $value);
            } else {
                return $client->id == $value;
            }
        });

        if(!$clients->count()){
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.manager_clients_ids');
    }

}
