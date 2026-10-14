<?php
require_once __DIR__ . '/Nodo.php';

class ListaDoblementeEnlazada
{
    private ?Nodo $cabeza = null;
    private ?Nodo $cola = null;
    private int $tamano = 0;

    public int $comparaciones = 0;
    public int $intercambios = 0;
    public int $pasadas = 0;

    public function tamano(): int
    {
        return $this->tamano;
    }

    public function insertarAlFinal(array $datos): void
    {
        $nuevo = new Nodo($datos);
        if ($this->cabeza === null) {
            $this->cabeza = $nuevo;
            $this->cola = $nuevo;
        } else {
            $nuevo->anterior = $this->cola;
            $this->cola->siguiente = $nuevo;
            $this->cola = $nuevo;
        }
        $this->tamano++;
    }

    public function recorrer(): Generator
    {
        for ($actual = $this->cabeza; $actual !== null; $actual = $actual->siguiente) {
            yield $actual;
        }
    }

    public function recorrerInverso(): Generator
    {
        for ($actual = $this->cola; $actual !== null; $actual = $actual->anterior) {
            yield $actual;
        }
    }

    private function comparar($a, $b, bool $numerico): int
    {
        return $numerico ? ((float)$a <=> (float)$b) : strcasecmp((string)$a, (string)$b);
    }

    public function ordenarBurbuja(string $campo, bool $numerico, bool $ascendente = true): void
    {
        $this->comparaciones = $this->intercambios = $this->pasadas = 0;
        if ($this->tamano < 2) {
            return;
        }
        $limite = null;
        do {
            $huboIntercambio = false;
            $this->pasadas++;
            $actual = $this->cabeza;
            while ($actual->siguiente !== $limite) {
                $this->comparaciones++;
                $cmp = $this->comparar($actual->datos[$campo], $actual->siguiente->datos[$campo], $numerico);
                if ($ascendente ? $cmp > 0 : $cmp < 0) {
                    $temporal = $actual->datos;
                    $actual->datos = $actual->siguiente->datos;
                    $actual->siguiente->datos = $temporal;
                    $this->intercambios++;
                    $huboIntercambio = true;
                }
                $actual = $actual->siguiente;
            }
            $limite = $actual; 
        } while ($huboIntercambio && $limite !== $this->cabeza);
    }
}
