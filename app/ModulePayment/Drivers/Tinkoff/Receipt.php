<?php
namespace App\ModulePayment\Drivers\Tinkoff;

use Illuminate\Contracts\Support\Arrayable;

class Receipt extends Arrayable
{
    /**
     * общая СН
     */
    const TAX_SYSTEM_COMMON = 'osn';

    /**
     * упрощенная СН (доходы)
     */
    const TAX_SYSTEM_SIMPLE_INCOME = 'usn_income';

    /**
     * упрощенная СН (доходы минус расходы)
     */
    const TAX_SYSTEM_SIMPLE_NO_OUTCOME = 'usn_income_outcome';

    /**
     * единый налог на вмененный доход
     */
    const TAX_SYSTEM_SIMPLE_UNIFIED = 'envd';

    /**
     * единый сельскохозяйственный налог
     */
    const TAX_SYSTEM_SIMPLE_AGRO = 'esn';

    /**
     * патентная СН
     */
    const TAX_SYSTEM_SIMPLE_PATENT = 'patent';

    /**
     * phone number
     *
     * @var string
     */
    private $phone;

    /**
     * email
     *
     * @var string
     */
    private $email;
	
	/**
     * Phone number or e-mail
     *
     * @var string
     */
    private $contact;
    /**
     * Tax system
     * Система налогообложения магазина (СНО). Параметр необходим, только если у вас несколько систем налогообложения. В остальных случаях не передается.
     *
     * @var int
     */
    private $taxSystem;
    /**
     * Items
     *
     * @var ReceiptItem[]
     */
    private $items = [];
	
    /**
     * Receipt constructor.
     *
     * @param string $phone
     * @param string $email
     * @param array|null $items
     * @param int $taxSystem
     */
    public function __construct($phone = null, $email = null, array $items = [], $taxSystem = null)
    {
		$this->setItems($items)->setTaxSystem($taxSystem);
        $this->setPhone($phone);
        $this->setEmail($email);
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }
	
	/**
     * Get contact
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }
    /**
     * Set contact
     *
     * @param string $contact
     *
     * @return $this
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
        return $this;
    }
    /**
     * Get tax system
     *
     * @return int
     */
    public function getTaxSystem()
    {
        return $this->taxSystem;
    }
    /**
     * Set tax system
     *
     * @param int $taxSystem
     *
     * @return $this
     */
    public function setTaxSystem($taxSystem)
    {
        $this->taxSystem = $taxSystem;
        return $this;
    }
    /**
     * Get all items in receipt
     *
     * @return ReceiptItem[]
     */
    public function getItems()
    {
        return $this->items;
    }
    /**
     * Set items in receipt
     *
     * @param ReceiptItem[] $items
     *
     * @return $this
     */
    public function setItems(array $items)
    {
        $this->items = $items;
        return $this;
    }
    /**
     * Add item
     *
     * @param ReceiptItem $item
     *
     * @return $this
     */
    public function addItem(ReceiptItem $item)
    {
        $this->items[] = $item;
        return $this;
    }
	
    /**
     * Receipt to array
     *
     * @return array
     */
    public function toArray()
    {
        $items = array_map(function ($item) {
            /** @var ReceiptItem $item */
            return $item->toArray();
        }, $this->getItems());

        $result = [
            'Phone' => $this->getPhone(),
            'Email' => $this->getEmail(),
            'Items' => $items,
        ];
        if (($taxSystem = $this->getTaxSystem()) !== null) {
            $result['Taxation'] = $taxSystem;
        }

        return $result;
    }
	
    /**
     * Receipt to json
     *
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
