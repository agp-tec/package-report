<?php


namespace Agp\Report;

/**
 * Class ReportTotalizador
 * Contem as colunas para realizar o totalizador
 * @package App\Helper
 */
class ReportTotalizador
{
    /** Metodo de totalização = 'count','sum','avg'
     * @var string
     */
    public $metodo;
    /** Resultado da totalização
     * @var float|int
     */
    private $valor;
    /** Variavel auxiliar contadoa para realizar avg
     * @var int
     */
    private $count;

    /**
     * ReportTotalizador constructor.
     * @param string $metodo Metodo de totalização = 'count','sum','avg'
     * @param float|int $valor
     */
    public function __construct($metodo, $valor = 0)
    {
        $this->metodo = $metodo;
        $this->valor = $valor;
        $this->count = 0;
    }

    /**
     * @return float|int
     */
    public function getValor()
    {
        if ($this->metodo == 'avg')
            return $this->valor / $this->count;
        return $this->valor;
    }

    /**
     * Limpa valores do totalizador
     */
    public function clear()
    {
        $this->valor = 0;
        $this->count = 0;
    }

    /**
     * @param float|int $value
     */
    public function append($value)
    {
        $this->count++;
        switch ($this->metodo) {
            case 'count':
                $this->valor += 1;
                break;
            case 'avg':
            case 'sum':
                $this->valor += $value;
                break;
        }
    }

}
