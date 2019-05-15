<?php
namespace App\Services;

class PhoneNormalizer
{
    public function normalize($number, $minLength = 10)
    {
        $minLength = intval($minLength);
        if ($minLength <= 0 || strlen($number) < $minLength)
        {
            return false;
        }

        if (strlen($number) >= 10 && substr($number, 0, 2) == '+8')
        {
            $number = '00'.substr($number, 1);
        }

        $number = preg_replace("/[^0-9\#\*,;]/i", "", $number);
        if (strlen($number) >= 10)
        {
            if (substr($number, 0, 2) == '80' || substr($number, 0, 2) == '81' || substr($number, 0, 2) == '82')
            {
            }
            else if (substr($number, 0, 2) == '00')
            {
                $number = substr($number, 2);
            }
            else if (substr($number, 0, 3) == '011')
            {
                $number = substr($number, 3);
            }
            else if (substr($number, 0, 1) == '8')
            {
                $number = '7'.substr($number, 1);
            }
            else if (substr($number, 0, 1) == '0')
            {
                $number = substr($number, 1);
            }
        }

        return $number;
    }
}