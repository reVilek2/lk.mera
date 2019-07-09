<?php
namespace App\ModulePayment\Drivers;

use Illuminate\Contracts\Support\Arrayable;

class YandexReceiptItem implements Arrayable
{
    /**
     * без НДС
     */
    const TAX_NO_VAT = 1;

    /**
     * НДС по ставке 0%
     */
    const TAX_VAT_0 = 2;

    /**
     * НДС чека по ставке 10%
     */
    const TAX_VAT_10 = 3;

    /**
     * НДС чека по ставке 18%
     */
    const TAX_VAT_18 = 4;

    /**
     * НДС чека по расчетной ставке 10/110
     */
    const TAX_VAT_110 = 5;

    /**
     * НДС чека по расчетной ставке 18/118
     */
    const TAX_VAT_118 = 6;

    //////// Признак предмета расчета ///////
    /**
     * Товар
     */
    const PAYMENT_SUBJECT_COMMODITY = 'commodity';

    /**
     * Подакцизный товар
     */
    const PAYMENT_SUBJECT_EXCISE = 'excise';

    /**
     * Работа
     */
    const PAYMENT_SUBJECT_JOB = 'job';

    /**
     * Услуга
     */
    const PAYMENT_SUBJECT_SERVICE = 'service';

    /**
     * Ставка в азартной игре
     */
    const PAYMENT_SUBJECT_GAMBLING_BET = 'gambling_bet';

    /**
     * Выигрыш в азартной игре
     */
    const PAYMENT_SUBJECT_GAMBLING_PRIZE = 'gambling_prize';

    /**
     * Лотерейный билет
     */
    const PAYMENT_SUBJECT_LOTTERY = 'lottery';

    /**
     * Выигрыш в лотерею
     */
    const PAYMENT_SUBJECT_LOTTERY_PRIZE = 'lottery_prize';

    /**
     * Результаты интеллектуальной деятельности
     */
    const PAYMENT_SUBJECT_INTELLECTUAL_ACTIVITY = 'intellectual_activity';

    /**
     * Платеж
     */
    const PAYMENT_SUBJECT_PAYMENT = 'payment';

    /**
     * Агентское вознаграждение
     */
    const PAYMENT_SUBJECT_AGENT_COMMISSION = 'agent_commission';

    /**
     * 	Имущественные права
     */
    const PAYMENT_SUBJECT_PROPERTY_RIGHT = 'property_right';

    /**
     * 	Внереализационный доход
     */
    const PAYMENT_SUBJECT_NON_OPERATING_GAIN = 'non_operating_gain';

    /**
     * 	Страховой сбор
     */
    const PAYMENT_SUBJECT_INSURANCE_PREMIUM = 'insurance_premium';

    /**
     * 	Торговый сбор
     */
    const PAYMENT_SUBJECT_SALES_TAX = 'sales_tax';

    /**
     * 	Курортный сбор
     */
    const PAYMENT_SUBJECT_RESORT_FEE = 'resort_fee';

    /**
     * 	Несколько вариантов
     */
    const PAYMENT_SUBJECT_COMPOSITE = 'composite';

    /**
     * 	Другое
     */
    const PAYMENT_SUBJECT_ANOTHER = 'another';

    ////// Признак способа расчета //////
    /**
     * Полная предоплата
     */
    const PAYMENT_MODE_FULL_PREPAYMENT = 'full_prepayment';

    /**
     * Частичная предоплата
     */
    const PAYMENT_MODE_PARTIAL_PREPAYMENT = 'partial_prepayment';

    /**
     * Аванс
     */
    const PAYMENT_MODE_PARTIAL_ADVANCE = 'advance';

    /**
     * Полный расчет
     */
    const PAYMENT_MODE_FULL_PAYMENT = 'full_payment';

    /**
     * Частичный расчет и кредит
     */
    const PAYMENT_MODE_PARTIAL_PAYMENT = 'partial_payment';

    /**
     * Кредит
     */
    const PAYMENT_MODE_CREDIT = 'credit';

    /**
     * Выплата по кредиту
     */
    const PAYMENT_MODE_CREDIT_PAYMENT = 'credit_payment';

    /**
     * Quantity
     *
     * @var int
     */
    private $qty;

    /**
     * Item price
     *
     * @var float
     */
    private $price;

    /**
     * Currency
     *
     * @var string
     */
    private $currency;

    /**
     * Tax
     * Ставка НДС
     *
     * @var int
     */
    private $tax;

    /**
     * Item name
     *
     * @var string
     */
    private $name;

    /**
     * Признак предмета расчета
     *
     * @var string
     */
    private $paymentSubject;

    /**
     * Признак способа расчета
     *
     * @var string
     */
    private $paymentMode;

    /**
     * ReceiptItem constructor.
     *
     * @param string $name
     * @param int $qty
     * @param float $price
     * @param int $tax
     * @param string $currency
     * @param string $paymentSubject
     * @param string $paymentMode
     */
    public function __construct($name = null,
                                $qty = null,
                                $price = null,
                                $tax = null,
                                $currency = 'RUR',
                                $paymentSubject = self::PAYMENT_SUBJECT_PAYMENT,
                                $paymentMode = self::PAYMENT_MODE_FULL_PAYMENT)
    {
        $this->setName($name)
            ->setQty($qty)
            ->setPrice($price)
            ->setTax($tax)
            ->setCurrency($currency)
            ->setPaymentSubject($paymentSubject)
            ->setPaymentMode($paymentMode);
    }

    /**
     * Get PaymentSubject
     *
     * @return string
     */
    public function getPaymentSubject()
    {
        return $this->paymentSubject;
    }

    /**
     * Set PaymentSubject
     *
     * @param string $paymentSubject
     *
     * @return $this
     */
    public function setPaymentSubject($paymentSubject)
    {
        $this->paymentSubject = $paymentSubject;

        return $this;
    }

    /**
     * Get PaymentMode
     *
     * @return string
     */
    public function getPaymentMode()
    {
        return $this->paymentMode;
    }

    /**
     * Set PaymentMode
     *
     * @param string $paymentMode
     *
     * @return $this
     */
    public function setPaymentMode($paymentMode)
    {
        $this->paymentMode = $paymentMode;

        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * Set quantity
     *
     * @param int $qty
     *
     * @return $this
     */
    public function setQty($qty)
    {
        $this->qty = $qty;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param float $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get tax
     *
     * @return int
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set tax
     *
     * @param int $tax
     *
     * @return $this
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get item name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set item name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'quantity'    => (string)$this->getQty(),
            'amount'      => [
                'value'    => $this->getPrice(),
                'currency' => $this->getCurrency(),
            ],
            'vat_code'    => $this->getTax(),
            'description' => mb_substr($this->getName(), 0, 128),
            'payment_mode' => $this->getPaymentMode(),
            'payment_subject' => $this->getPaymentSubject(),
        ];
    }
}