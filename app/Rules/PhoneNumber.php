<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class PhoneNumber implements Rule
{
    private $message = 'The :attribute must be phone number.';

    public function __construct($message = null)
    {
        if ($message) {
            $this->message = $message;
        }
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
        return $this->is_phone($value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * проверка - телефон ли
     * @param $_val
     * @return bool
     */
    private function is_phone($_val)
    {
        if (empty($_val)) {
            return false;
        }

        if (!preg_match('/^\+?\d{10,15}$/', $_val)) {
            return false;
        }

        if (
            (mb_substr($_val, 0, 2) == '+7' and mb_strlen($_val) != 12) ||
            (mb_substr($_val, 0, 1) == '7'  and mb_strlen($_val) != 11) ||
            (mb_substr($_val, 0, 1) == '8'  and mb_strlen($_val) == 11) ||
            (mb_substr($_val, 0, 1) == '9'  and mb_strlen($_val) == 11)
        ) {
            return false;
        }
        return true;
    }
}
