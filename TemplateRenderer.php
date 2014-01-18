<?php


/**
 * Класс, реализующий шаблонизацию а-ля юишный рендерер.
 *
 * Class  TemplateRenderer
 */
class TemplateRenderer
{
    /**
     * @var  String  Папка-корень шаблонов
     */
    protected $_root = __DIR__;

    /**
     * @var  String  Папка с главными фреймами относительно корня
     */
    protected $_layout_root = 'layouts/';

    /**
     * @var  String  Главный фрейм
     */
    protected $_layout = false;

    /**
     * @var  \BaseClass_  Вызывающий или связанный контроллер.
     *                    Можно, конечно, было просто унаследовать или впихнуть в \BaseClass_, но что-то не захотелось.
     */
    protected $_context = null;


    /**
     * @var  String  Расширение файлов с шаблонами.
     */
    protected $_template_extension = '.php';


    /**
     * @param  Bool|String  $layout       Фрейм.
     * @param  Bool|String  $root         Папка с шаблонами.
     * @param  Bool|String  $layout_root  Папка с фреймами относительно папки с шаблонами.
     * @param  Mixed  $context            Связанные данные — например, контроллер, вызвавший свой метод render.
     *                                    Можно было просто запихнуть всё содержимое этого класса в BaseClass_,
     *                                    но тогда всем пользователям класса пришлось бы инстанцировать BaseClass_
     *                                    на малейший чих.
     */
    public function __construct($layout = false, $root = false, $layout_root = false, $context = null)
    {
        if ($layout) {
            $this->_layout = $layout;
        }
        if ($root) {
            $this->_root = $root;
        }
        if ($layout_root) {
            $this->_layout_root = $layout_root;
        }
        if ($context) {
            $this->_context = $context;
        }
    }


    /**
     * Рендерит шаблон с подстановкой в фрейм.
     *
     * @param  String  $template  Путь к шаблону относительно папки с шаблонами.
     * @param  Array   $data      Данные, пробрасываемые в шаблон.
     * @param  Bool    $return    Возвращать результаты как строку или сразу выводить.
     * @return  String  Строка-результат либо ничего, если return = false.
     */
    public function render($template, Array $data, $return = false)
    {
        $content = $this->renderPartial($template, $data, true);
        $layout = $this->getLayoutFile();
        $rendered = $this->renderFile($layout, array(
            'content' => $content,
        ));
        if ($return) {
            return $rendered;
        }
        echo $rendered;
    }


    /**
     * Рендерит шаблон без подстановки в фрейм.
     *
     * @param  String  $template  Путь к шаблону относительно папки с шаблонами.
     * @param  Array   $data      Данные, пробрасываемые в шаблон.
     * @param  Bool    $return    Возвращать результаты как строку или сразу выводить.
     * @return  String  Строка-результат либо ничего, если return = false.
     */
    public function renderPartial($template, Array $data, $return = false)
    {
        $template = $this->getTemplateFile($template);
        $content = $this->renderFile($template, $data);
        if ($return) {
            return $content;
        }
        echo $content;
    }


    /**
     * Рендерит шаблон по абсолютному пути без подстановки в фрейм, возвращает отрендеренную строку.
     *
     * @param  String  $template  Путь к шаблону относительно папки с шаблонами.
     * @param  Array   $data      Данные, пробрасываемые в шаблон.
     * @return  String  Строка-результат.
     */
    protected function renderFile($template, Array $data)
    {
        $data = array_merge_recursive(array(
            'context' => $this->_context,
        ), $data);
        if (is_array($data)) {
            extract($data);
        }
        ob_start();
        include($template);
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }


    /**
     * @param  String  $template  Путь к шаблону относительно папки с шаблонами.
     * @return  String  Абсолютный путь к шаблону.
     */
    protected function getTemplateFile($template)
    {
        return realpath($this->getRoot() . DIRECTORY_SEPARATOR . $template . $this->_template_extension);
    }


    /**
     * @return  String  Абсолютный путь к фрейму.
     */
    public function getLayoutFile()
    {
        if ($this->_layout !== false) {
            return realpath($this->getLayoutRoot() . DIRECTORY_SEPARATOR . $this->_layout . $this->_template_extension);
        }
        return $this->_layout;
    }


    /**
     * @return  String  Абсолютный путь к папке с фреймами.
     */
    public function getLayoutRoot()
    {
        return realpath($this->root . DIRECTORY_SEPARATOR . $this->_layout_root);
    }


    /**
     * @return  String  Абсолютный путь к папке с шаблонами.
     */
    public function getRoot()
    {
        return realpath($this->_root);
    }


    /**
     * Устанавливает значение свойства по следующему алгоритму:
     *   * если существует метод setИмяСвойстваВCamelCase, то возвращает результат его вызова;
     *   * если существует свойство _имя_свойства, то сохраняет туда значение;
     *   * если ничего из вышеперечисленного сделать не удалось, то выдаёт E_NOTICE.
     *
     * @param  $name  Имя свойства.
     * @param  $value  Значение.
     * @return  mixed
     */
    public function __set($name, $value)
    {
        $method_name = $this->_resolveMethodName($name, 'set');
        if (method_exists($this, $method_name)) {
            return $this->{$method_name}($value);
        }
        $property_name = $this->_resolvePropertyName($name);
        if (property_exists($this, $property_name)) {
            return $this->{$property_name} = $value;
        }
        trigger_error(__CLASS__ . ": Unresolved property \"{$name}\"");
        return false;
    }


    /**
     * Устанавливает значение свойства по следующему алгоритму:
     *   * если существует метод getИмяСвойстваВCamelCase, то возвращает результат его вызова;
     *   * если существует свойство _имя_свойства, то извлекает оттуда значение;
     *   * если ничего из вышеперечисленного сделать не удалось, то выдаёт E_NOTICE.
     *
     * @param  $name  Имя свойства.
     * @return  mixed
     */
    public function __get($name)
    {
        $method_name = $this->_resolveMethodName($name);
        if (method_exists($this, $method_name)) {
            return $this->{$method_name}();
        }
        $property_name = $this->_resolvePropertyName($name);
        if (property_exists($this, $property_name)) {
            return $this->{$property_name};
        }
        trigger_error(__CLASS__ . ": Unresolved property \"{$name}\"");
        return false;
    }


    /**
     * Форматирует имя искомого метода.
     *
     * @param  String  $name  Имя искомого свойства.
     * @param  String  $type  Тип метода (геттер/сеттер).
     * @return  String
     */
    protected function _resolveMethodName($name, $type = 'get')
    {
        $name = implode('-', array_map('ucfirst', explode('_', $name)));
        return "{$type}{$name}";
    }


    /**
     * Форматирует имя искомого внутреннего свойства.
     *
     * @param  String  $name  Имя искомого свойства.
     * @return  String
     */
    protected function _resolvePropertyName($name)
    {
        return "_{$name}";
    }

}
