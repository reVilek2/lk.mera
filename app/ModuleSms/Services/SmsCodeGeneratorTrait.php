<?php
namespace App\ModuleSms\Services;

/**
 * Trait SmsCodeGeneratorTrait
 * @package App\ModuleSms\Services
 */
trait SmsCodeGeneratorTrait
{
    /**
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public function codeGenerate($length = 10)
    {
        // Текущее время
        $time = new \DateTime(date('Y-m-d H:i:s'));
        // день час минута секунда
        $stringTime = $time->format('dHis');

        // разбиваем на массив и перемешиваем его
        $array = str_split($stringTime);
        shuffle($array);

        // количество элементов в массиве
        $arrayCount = count($array);

        if ($length < $arrayCount) {
            // оставляем в массиве первые $length элементы
            $array = array_slice($array, 0, $length);
        } elseif ($length > $arrayCount) {
            // добавляем недостающие
            $length = $length - $arrayCount;
            // определяем набор символов
            $chars="1234567890";
            // определяем количество символов в $chars
            $size = strlen($chars)-1;
            // количество символов в наборе
            $rand = null;
            // создаем псевдослучайную последовательность
            while($length--) $rand .= $chars[rand(0,$size)];

            // добавляем сгенерированную последовательность в массив $array
            array_push($array, $rand);
        }

        // объединяем массив в строку
        $code = implode("", $array);

        // если кода нет или количество символов не совпадает с требуемым, выбрасываем исключение
        if(!$code) {

            throw new \Exception("Code generation failed");
        }

        return $code;
    }
}