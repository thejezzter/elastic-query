<?php

namespace Bardex\Elastic;


class Where
{
    protected $field;

    protected $query;


    public function __construct(SearchQuery $query)
    {
        $this->query = $query;
    }

    /**
     * @param mixed $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @param $value
     * @return SearchQuery
     */
    public function equal($value)
    {
        $this->query->addFilter('term', [$this->field => $value]);
        return $this->query;
    }

    /**
     * Добавить фильтр совпадения хотя бы одного значения из набора, этот фильтр не влияет на поле релевантности _score.
     *
     * @param $values - массив допустимых значений
     * @example $query->where('channel')->in([1,2,3])->where('page.categoryId')->in([10,11]);
     * @return SearchQuery;
     */
    public function in(array $values)
    {
        // потому что ES не понимает дырки в ключах
        $values = array_values($values);
        $this->query->addFilter('terms', [$this->field => $values]);
        return $this->query;
    }

    /**
     * Добавить фильтр вхождения значение в диапазон (обе границы включительно).
     * Можно искать по диапазону дат.
     * Этот фильтр не влияет на поле релевантности _score.
     *
     * @param $min - нижняя граница диапазона (включительно)
     * @param $max - верхняя граница диапазона (включительно)
     * @param $dateFormat - необязательное поле описание формата даты
     * @link https://www.elastic.co/guide/en/elasticsearch/reference/5.0/query-dsl-range-query.html
     * @return SearchQuery;
     */
    public function between($min, $max, $dateFormat = null)
    {
        $this->range(['gte' => $min, 'lte' => $max], $dateFormat);
        return $this->query;
    }

    /**
     * Добавить фильтр "больше или равно"
     * @param $value - значение
     * @param null $dateFormat - необязательный формат даты
     * @return SearchQuery
     */
    public function greaterOrEqual($value, $dateFormat = null)
    {
        $this->range(['gte' => $value], $dateFormat);
        return $this->query;
    }

    /**
     * Добавить фильтр "больше чем"
     * @param $value - значение
     * @param null $dateFormat - необязательный формат даты
     * @return SearchQuery
     */
    public function greater($value, $dateFormat = null)
    {
        $this->range(['gt' => $value], $dateFormat);
        return $this->query;
    }

    /**
     * Добавить фильтр "меньше или равно"
     * @param $value - значение
     * @param null $dateFormat - необязательный формат даты
     * @return SearchQuery
     */
    public function lessOrEqual($value, $dateFormat = null)
    {
        $this->range(['lte' => $value], $dateFormat);
        return $this->query;
    }


    /**
     * Добавить фильтр "меньше чем"
     * @param $value - значение
     * @param null $dateFormat - - необязательный формат даты
     * @return SearchQuery
     */
    public function less($value, $dateFormat = null)
    {
        $this->range(['lt' => $value], $dateFormat);
        return $this->query;
    }


    protected function range($params, $dateFormat=null)
    {
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->query->addFilter('range', [$this->field => $params]);
        return $this->query;
    }


    /**
     * Добавить фильтр полнотекстового поиска, этот фильтр влияет на поле релевантности _score.
     *
     * @param $text - поисковая фраза
     * @return SearchQuery;
     */
    public function match($text)
    {
        if (is_array($this->field)) {
            $this->query->addFilter('multi_match', [
                'query'  => $text,
                'fields' => $this->field
            ]);
        } else {
            $this->query->addFilter('match', [$this->field => $text]);
        }
        return $this->query;
    }

    /**
     * Поле существует и имеет не null значение
     * @return SearchQuery
     */
    public function exists()
    {
        $this->query->addFilter('exists', ["field" => $this->field]);
        return $this->query;
    }

    /**
     * @param $value
     * @return SearchQuery
     */
    public function not($value)
    {
        $this->query->addNotFilter('term', [$this->field => $value]);
        return $this->query;
    }


    /**
     * @param $values - массив допустимых значений
     * @example $query->where('channel')->notIn([1,2,3]);
     * @return SearchQuery;
     */
    public function notIn(array $values)
    {
        // потому что ES не понимает дырки в ключах
        $values = array_values($values);
        $this->query->addNotFilter('terms', [$this->field => $values]);
        return $this->query;
    }


    /**
     * @param $min
     * @param $max
     * @param null $dateFormat
     * @return SearchQuery
     */
    public function notBetween($min, $max, $dateFormat = null)
    {
        $params = ['gte' => $min, 'lte' => $max];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->query->addNotFilter('range', [$this->field => $params]);
        return $this->query;
    }

    /**
     * @param $text
     * @return SearchQuery
     */
    public function notMatch($text)
    {
        if (is_array($this->field)) {
            $this->query->addNotFilter('multi_match', [
                'query'  => $text,
                'fields' => $this->field
            ]);
        } else {
            $this->query->addNotFilter('match', [$this->field => $text]);
        }
        return $this->query;
    }

    /**
     * @return SearchQuery
     */
    public function notExists()
    {
        $this->query->addNotFilter('exists', ["field" => $this->field]);
        return $this->query;
    }
}