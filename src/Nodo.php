<?php
class Nodo
{
    public array $datos;
    public ?Nodo $anterior = null;
    public ?Nodo $siguiente = null;

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }
}
