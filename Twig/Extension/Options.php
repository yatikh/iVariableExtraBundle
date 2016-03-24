<?php

namespace iVariable\ExtraBundle\Twig\Extension;

class Options extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getGlobals()
    {
        return array(
            'options' => $this,
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('truncate', array($this, 'modTruncate')),
            new \Twig_SimpleFilter('number', array($this, 'modNumber')),
            new \Twig_SimpleFilter('uri', array($this, 'modUri')),
            new \Twig_SimpleFilter('startsWith', array($this, 'modStartsWith')),
            new \Twig_SimpleFilter('endsWith', array($this, 'modEndsWith')),
            new \Twig_SimpleFilter('contains', array($this, 'modContains')),
            new \Twig_SimpleFilter('pad', 'str_pad'),
            new \Twig_SimpleFilter('md5', 'md5'),
            new \Twig_SimpleFilter('jsonEncode', 'json_encode'),
            new \Twig_SimpleFilter('jsonDecode', 'json_decode'),
            new \Twig_SimpleFilter('ltrim', 'ltrim'),
            new \Twig_SimpleFilter('rtrim', 'rtrim'),
            new \Twig_SimpleFilter('substring', 'substr'),
            new \Twig_SimpleFilter('dump', 'var_dump'),
            new \Twig_SimpleFilter('shift', 'array_shift'),
            new \Twig_SimpleFilter('pop', 'array_pop'),
            new \Twig_SimpleFilter('count', 'count'),
        );
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'options';
    }

    public function getServer($key)
    {
        return $_SERVER[$key];
    }

    public function container()
    {
        return $this->container;
    }

    public function __call($name, $args)
    {
        return $this->container->getParameter($name);
    }

    public function modTruncate($str, $len, $suffix = null)
    {
        if (mb_strlen($str) > $len) {
            $str = mb_substr($str, 0, $len);
            if (!is_null($suffix)) {
                $str .= $suffix;
            }
        }

        return $str;
    }

    public function modNumber($number, $decimals = 2, $decPoint = ',', $thousandsSep = '.', $collapseIntegers = false)
    {
        if (is_null($decPoint)) {
            $decPoint = ',';
        }

        if (is_null($thousandsSep)) {
            $thousandsSep = '.';
        }

        if ($collapseIntegers && ((int) $number == $number)) {
            $decimals = 0;
        }

        return number_format($number, $decimals, $decPoint, $thousandsSep);
    }

    /**
     * Creates a clean uri. Basic usage:
     * 'foo'|uri() => /foo/
     * 'foo'|uri('pref', {'foo' : 'bar'}, 'top') => /pref/foo/?foo=bar#top.
     *
     * Both key and params accept arrays:
     * ['foo', 'bar']|uri() => /foo/bar/
     * 'foo'|uri(['pref', 'foobar']) => /pref/foobar/foo/
     
     * @param string|array $key
     * @param string|array $prefix
     * @param array        $params
     * @param string       $hash
     */
    public function modUri($key, $prefix = '', $params = array(), $hash = null)
    {
        $isLink = false;

        // concatenate if array is supplied
        if (is_array($key)) {
            $key = implode('/', $key);
        }
        if (is_array($prefix)) {
            $prefix = implode('/', $prefix);
        }

        if ($isLink || $this->isAbsoluteUrl($key)) {
            $uri = $key;
        } else {

            // use the prefix
            $uri = $prefix.'/'.$key;

            if (substr($uri, -1 != '/') && (strpos($key, '.') === false)) {
                $uri .= '/';
            }

            // make sure uri starts with a slash
            if (substr($uri, 0, 1) != '/') {
                $uri = '/'.$uri;
            }

            // remove 2 or more consecutive slashes
            $uri = preg_replace('/\/{2,}/', '/', $uri);
        }

        $uri = trim($uri);

        if (!empty($params)) {
            $uri .= ((strpos($uri, '?') === false) ? '?' : '&').http_build_query($params);
        }

        if (!is_null($hash)) {
            $uri .= '#'.$hash;
        }

        return $uri;
    }

    public function isAbsoluteUrl($url)
    {
        return preg_match('/^[a-zA-Z]+\:.+/', $url);
    }

    public function modStartsWith($str, $cmp)
    {
        return substr($str, 0, mb_strlen($cmp)) == $cmp;
    }

    public function modEndsWith($str, $cmp)
    {
        return substr($str, -mb_strlen($cmp)) == $cmp;
    }

    public function modContains($str, $cmp)
    {
        return stristr($str, $cmp) !== false;
    }
}
