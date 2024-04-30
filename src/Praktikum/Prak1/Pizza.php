<?php declare(strict_types=1);

class Pizza
{
    public string $pizza_name;
    public string $pizza_short;
    public string $pizza_image;
    public float $pizza_price;


    public function __construct($pizza_name, $pizza_short ,$pizza_image, $pizza_price)
    {
        $this->pizza_name = $pizza_name;
        $this->pizza_short = $pizza_short;
        $this->pizza_image = $pizza_image;
        $this->pizza_price = $pizza_price;
    }
}
