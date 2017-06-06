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
        $params = ['gte' => $min, 'lte' => $max];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->query->addFilter('range', [$this->field => $params]);
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
        $params = ['gte' => $value];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->query->addFilter('range', [$this->field => $params]);
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
        $params = ['gt' => $value];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->query->addFilter('range', [$this->field => $params]);
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
        $params = ['lte' => $value];
        if ($dateFormat) {
            $params['format'] = $dateFormat;
        }
        $this->query->addFilter('range', [$this->field => $params]);
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
        $params = ['lt' => $value];
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

}