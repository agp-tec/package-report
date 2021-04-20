<?php


namespace Agp\Report;


class ReportFilter
{
    /** Tipo do campo (int, string, date, etc)
     * @var string
     */
    public $tipo;
    /** Metodo de filtro (=, between, like) ou options do select para choice
     * @var string|array
     */
    public $metodo;
    /** Direção do orderby atual
     * @var string
     */
    public $order;
    /**
     * @var ReportColumn
     */
    private $column;
    /** Atributos html do input de filtro
     * @var string[]
     */
    private $attrs;
    /** Valor do filtro
     * @var string
     */
    public $data;

    /**
     * ReportFilter constructor.
     * @param $column
     */
    public function __construct($column)
    {
        $this->column = $column;
    }

    /** Renderiza input de filtro
     * @return string
     */
    public function renderInput()
    {
        $inputName = 'query[' . $this->column->alias . ']';
        $inputValues = request()->input();
        $inputValue = null;
        if ($inputValues && array_key_exists('query', $inputValues) && is_array($inputValues['query']) && array_key_exists($this->column->alias, $inputValues['query']))
            $inputValue = $inputValues['query'][$this->column->alias];
        switch ($this->tipo) {
            case 'int':
            case 'number':
            case 'integer':
                if (!array_key_exists('type', $this->attrs))
                    $this->attrs['type'] = 'number';
                return '<input ' . $this->getAttrs() . ' name="' . $inputName . '" value="' . request()->input('query.' . $this->column->alias) . '">';
            case 'bool':
            case 'checkbox':
            case 'switch':
                $view = config('report.input_checkbox_view');
            if (!$view)
                $view = 'Report::input.checkbox';
            if (!array_key_exists('type', $this->attrs))
                $this->attrs['type'] = 'checkbox';
            $attrs = $this->getAttrs();
            $inputTitle = $this->column->header->title;
            return view($view, compact('inputTitle', 'inputName', 'inputValue', 'attrs'));
            case 'choice':
            case 'select':
                $view = config('report.input_choice_view');
                if (!$view)
                    $view = 'Report::input.choice';
                $options = $this->metodo;
                return view($view, compact('inputName', 'inputValue', 'options'));
            case 'date':
            case 'datetime':
                if ($this->metodo == 'between') {
                    $view = config('report.input_datetime_view');
                    if (!$view)
                        $view = 'Report::input.datetime';
                    if (!$inputValue) {
                        $inputValue = array();
                        $inputValue['start'] = null;
                        $inputValue['end'] = null;
                    }
                    return view($view, compact('inputName', 'inputValue'));
                }
                if (!array_key_exists('type', $this->attrs))
                    $this->attrs['type'] = 'text';
                return '<input ' . $this->getAttrs() . ' name="' . $inputName . '" value="' . request()->input('query.' . $this->column->alias) . '">';
            default:
                if (!array_key_exists('type', $this->attrs))
                    $this->attrs['type'] = 'text';
                return '<input ' . $this->getAttrs() . ' name="' . $inputName . '" value="' . request()->input('query.' . $this->column->alias) . '">';
        }
    }

    /**
     * @param string $tipo Tipo de dado (int, string, datetime, etc)
     * @param string|array $metodo Metodo de filtro (=,>=,<=,like,between, etc) ou opcoes do choice
     * @param string[] $attrs Atributos html do input de filtro
     */
    public function set(string $tipo, $metodo, $attrs = ['class' => 'form-control'])
    {
        $this->tipo = $tipo;
        $this->metodo = $metodo;
        $this->attrs = $attrs;
    }

    public function getOrderByUrl()
    {
        $query = $this->column->parent->getRequestKey(null);
        $query['order'][$this->column->alias] = $this->order == 'desc' ? 'asc' : 'desc';
        return http_build_query([
                $this->column->parent->getName() => $query,
            ]) . '#' . $this->column->parent->getName();
    }

    /** Retorna os atributos html
     * @return string
     */
    private function getAttrs()
    {
        $res = '';
        foreach ($this->attrs as $key => $value)
            $res .= $key . '="' . $value . '"';
        return $res;
    }
}
