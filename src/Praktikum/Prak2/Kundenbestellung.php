<?php

class Kundenbestellung
{
    public array $pizza_status = [];
    public string $address;
    public int $telephone;
    public string $username;
    public int $order_id;

    public float $order_price = 0;

    public function __construct(int $order_id, mysqli_result $recordset)
    {
        $record = $recordset->fetch_assoc();
        $this->order_id = $order_id;
        if ($record && isset($record['address'])){
            $full_address = $record['address'];
            $address_split = explode(',', $full_address);
            if (count($address_split) == 4) {
                $this->address = $address_split[0].' ' . $address_split[1];
                $this->telephone = intval($address_split[2]);
                $this->username = $address_split[3];
            }else {
                $this->address = $address_split[0];
                if ($address_split[1] != " ") {
                    $this->telephone = intval($address_split[1]);
                }
                $this->username = $address_split[2];
            }
        }
        do {
                $this->pizza_status[] = [
                    'pizza_name' => $record['name'],
                    'status' => $record['status'],
                ];
                if (isset($record['price'])){
                    $this->order_price += $record['price'];
                }
            $record = $recordset->fetch_assoc();
        } while ($record);
    }

    public function getOrderStatus():int{
        $min_status = $this->pizza_status[0]['status'];
        foreach ($this->pizza_status as $pizza){
            if ($min_status > $pizza['status']){
                $min_status = $pizza['status'];
            }
        }
        return intval($min_status);
    }

    public function get_all_pizza_names():string{
        $allPizzas = "";
        foreach ($this->pizza_status as $pizza){
            $allPizzas .= $pizza['pizza_name'].', ';
        }
        return $allPizzas;
    }
}

