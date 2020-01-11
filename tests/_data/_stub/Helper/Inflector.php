<?php
/*
 * This source file is subject to the MIT License.
 *
 * (c) Dominic Beck <dominic@headcrumbs.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this package.
 */
declare(strict_types=1);

namespace Stub\Helper;

use function array_keys;
use function array_search;
use function array_values;
use function current;
use function html_entity_decode;
use function htmlspecialchars;
use function htmlspecialchars_decode;
use function in_array;
use function implode;
use function mb_substr;
use function mb_strlen;
use function mb_strtoupper;
use function next;
use function preg_match;
use function preg_replace;
use function preg_split;
use function rawurldecode;
use function rawurlencode;
use function reset;
use function strtolower;
use function strtoupper;
use function str_replace;
use function substr;
use function transliterator_transliterate;
use function trim;
use function ucfirst;
use function ucwords;
use function utf8_encode;

/**
 * Some basic methods for handling string conversions,
 * language related processing and translation.
 */
class Inflector
{
    /**
     * Shortcut for `Any-Latin; NFKD` transliteration rule. The rule is strict, letters will be transliterated with
     * the closest sound-representation chars. The result may contain any UTF-8 chars. For example:
     * `获取到 どちら Українська: ґ,є, Српска: ђ, њ, џ! ¿Español?` will be transliterated to
     * `huò qǔ dào dochira Ukraí̈nsʹka: g̀,ê, Srpska: đ, n̂, d̂! ¿Español?`
     *
     * @see http://unicode.org/reports/tr15/#Normalization_Forms_Table
     * @see transliterate()
     */
    const TRANSLITERATE_STRICT = 'Any-Latin; NFKD';

    /**
     * Shortcut for `Any-Latin; Latin-ASCII` transliteration rule. The rule is medium, letters will be
     * transliterated to characters of Latin-1 (ISO 8859-1) ASCII table. For example:
     * `获取到 どちら Українська: ґ,є, Српска: ђ, њ, џ! ¿Español?` will be transliterated to
     * `huo qu dao dochira Ukrainsʹka: g,e, Srpska: d, n, d! ¿Espanol?`
     *
     * @see http://unicode.org/reports/tr15/#Normalization_Forms_Table
     * @see transliterate()
     */
    const TRANSLITERATE_MEDIUM = 'Any-Latin; Latin-ASCII';

    /**
     * Shortcut for `Any-Latin; Latin-ASCII; [\u0080-\uffff] remove` transliteration rule. The rule is loose,
     * letters will be transliterated with the characters of Basic Latin Unicode Block.
     * For example:
     * `获取到 どちら Українська: ґ,є, Српска: ђ, њ, џ! ¿Español?` will be transliterated to
     * `huo qu dao dochira Ukrainska: g,e, Srpska: d, n, d! Espanol?`
     *
     * @see http://unicode.org/reports/tr15/#Normalization_Forms_Table
     * @see transliterate()
     */
    const TRANSLITERATE_LOOSE = 'Any-Latin; Latin-ASCII; [\u0080-\uffff] remove';

    /**
     * @var Mixed Either a [[\Transliterator]], or a string from which a [[\Transliterator]] can be built
     * for transliteration. Used by [[transliterate()]] when intl is available. Defaults to [[TRANSLITERATE_LOOSE]]
     * @see http://php.net/manual/en/transliterator.transliterate.php
     */
    public static $transliterator = self::TRANSLITERATE_LOOSE;

    /**
     * Transliteration map for special character replacements.
     *
     * @var Array
     */
    public static $transliteration_map = ['Ä' => 'Ae', 'ä' => 'ae', 'Ö' => 'Oe', 'ö' => 'oe', 'Ü' => 'Ue', 'ü' => 'ue'];

    /**
     * Array of regular expressions and corresponding replacement handling.
     *
     * @authors:
     *       Antonio Ramirez <amigo.cobos@gmail.com>
     *       Alexander Makarov <sam@rmcreative.ru>
     * @license http://www.yiiframework.com/license/
     *
     * @var Array
     */
    public static $plurals = [
        '/([nrlm]ese|deer|fish|sheep|measles|ois|pox|media)$/i' => '\1',
        '/^(sea[- ]bass)$/i' => '\1',
        '/(m)ove$/i' => '\1oves',
        '/(f)oot$/i' => '\1eet',
        '/(h)uman$/i' => '\1umans',
        '/(s)tatus$/i' => '\1tatuses',
        '/(s)taff$/i' => '\1taff',
        '/(t)ooth$/i' => '\1eeth',
        '/(quiz)$/i' => '\1zes',
        '/^(ox)$/i' => '\1\2en',
        '/([m|l])ouse$/i' => '\1ice',
        '/(matr|vert|ind)(ix|ex)$/i' => '\1ices',
        '/(x|ch|ss|sh)$/i' => '\1es',
        '/([^aeiouy]|qu)y$/i' => '\1ies',
        '/(hive)$/i' => '\1s',
        '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
        '/sis$/i' => 'ses',
        '/([ti])um$/i' => '\1a',
        '/(p)erson$/i' => '\1eople',
        '/(m)an$/i' => '\1en',
        '/(c)hild$/i' => '\1hildren',
        '/(buffal|tomat|potat|ech|her|vet)o$/i' => '\1oes',
        '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
        '/us$/i' => 'uses',
        '/(alias)$/i' => '\1es',
        '/(ax|cris|test)is$/i' => '\1es',
        '/s$/' => 's',
        '/^$/' => '',
        '/$/' => 's'
        ];

    /**
     * Array of regular expressions and corresponding replacement handling.
     *
     * @authors:
     *       Antonio Ramirez <amigo.cobos@gmail.com>
     *       Alexander Makarov <sam@rmcreative.ru>
     * @license http://www.yiiframework.com/license/
     *
     * @var Array
     */
    public static $singulars = [
        '/([nrlm]ese|deer|fish|sheep|measles|ois|pox|media|ss)$/i' => '\1',
        '/^(sea[- ]bass)$/i' => '\1',
        '/(s)tatuses$/i' => '\1tatus',
        '/(f)eet$/i' => '\1oot',
        '/(t)eeth$/i' => '\1ooth',
        '/^(.*)(menu)s$/i' => '\1\2',
        '/(quiz)zes$/i' => '\\1',
        '/(matr)ices$/i' => '\1ix',
        '/(vert|ind)ices$/i' => '\1ex',
        '/^(ox)en/i' => '\1',
        '/(alias)(es)*$/i' => '\1',
        '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
        '/([ftw]ax)es/i' => '\1',
        '/(cris|ax|test)es$/i' => '\1is',
        '/(shoe|slave)s$/i' => '\1',
        '/(o)es$/i' => '\1',
        '/ouses$/' => 'ouse',
        '/([^a])uses$/' => '\1us',
        '/([m|l])ice$/i' => '\1ouse',
        '/(x|ch|ss|sh)es$/i' => '\1',
        '/(m)ovies$/i' => '\1\2ovie',
        '/(s)eries$/i' => '\1\2eries',
        '/([^aeiouy]|qu)ies$/i' => '\1y',
        '/([lr])ves$/i' => '\1f',
        '/(tive)s$/i' => '\1',
        '/(hive)s$/i' => '\1',
        '/(drive)s$/i' => '\1',
        '/([^fo])ves$/i' => '\1fe',
        '/(^analy)ses$/i' => '\1sis',
        '/(analy|diagno|^ba|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
        '/([ti])a$/i' => '\1um',
        '/(p)eople$/i' => '\1\2erson',
        '/(m)en$/i' => '\1an',
        '/(c)hildren$/i' => '\1\2hild',
        '/(n)ews$/i' => '\1\2ews',
        '/eaus$/' => 'eau',
        '/^(.*us)$/' => '\\1',
        '/s$/i' => ''
        ];

    /**
     * Array of rules for handling special english language conversions
     *
     * @authors:
     *       Antonio Ramirez <amigo.cobos@gmail.com>
     *       Alexander Makarov <sam@rmcreative.ru>
     * @license http://www.yiiframework.com/license/
     *
     * @var Array
     */
    public static $specials = [
        'atlas' => 'atlases',
        'beef' => 'beefs',
        'brother' => 'brothers',
        'cafe' => 'cafes',
        'child' => 'children',
        'cookie' => 'cookies',
        'corpus' => 'corpuses',
        'cow' => 'cows',
        'curve' => 'curves',
        'foe' => 'foes',
        'ganglion' => 'ganglions',
        'genie' => 'genies',
        'genus' => 'genera',
        'graffito' => 'graffiti',
        'hoof' => 'hoofs',
        'loaf' => 'loaves',
        'man' => 'men',
        'money' => 'monies',
        'mongoose' => 'mongooses',
        'move' => 'moves',
        'mythos' => 'mythoi',
        'niche' => 'niches',
        'numen' => 'numina',
        'occiput' => 'occiputs',
        'octopus' => 'octopuses',
        'opus' => 'opuses',
        'ox' => 'oxen',
        'penis' => 'penises',
        'sex' => 'sexes',
        'soliloquy' => 'soliloquies',
        'testis' => 'testes',
        'trilby' => 'trilbys',
        'turf' => 'turfs',
        'wave' => 'waves',
        'Amoyese' => 'Amoyese',
        'bison' => 'bison',
        'Borghese' => 'Borghese',
        'bream' => 'bream',
        'breeches' => 'breeches',
        'britches' => 'britches',
        'buffalo' => 'buffalo',
        'cantus' => 'cantus',
        'carp' => 'carp',
        'chassis' => 'chassis',
        'clippers' => 'clippers',
        'cod' => 'cod',
        'coitus' => 'coitus',
        'Congoese' => 'Congoese',
        'contretemps' => 'contretemps',
        'corps' => 'corps',
        'debris' => 'debris',
        'diabetes' => 'diabetes',
        'djinn' => 'djinn',
        'eland' => 'eland',
        'elk' => 'elk',
        'equipment' => 'equipment',
        'Faroese' => 'Faroese',
        'flounder' => 'flounder',
        'Foochowese' => 'Foochowese',
        'gallows' => 'gallows',
        'Genevese' => 'Genevese',
        'Genoese' => 'Genoese',
        'Gilbertese' => 'Gilbertese',
        'graffiti' => 'graffiti',
        'headquarters' => 'headquarters',
        'herpes' => 'herpes',
        'hijinks' => 'hijinks',
        'Hottentotese' => 'Hottentotese',
        'information' => 'information',
        'innings' => 'innings',
        'jackanapes' => 'jackanapes',
        'Kiplingese' => 'Kiplingese',
        'Kongoese' => 'Kongoese',
        'Lucchese' => 'Lucchese',
        'mackerel' => 'mackerel',
        'Maltese' => 'Maltese',
        'mews' => 'mews',
        'moose' => 'moose',
        'mumps' => 'mumps',
        'Nankingese' => 'Nankingese',
        'news' => 'news',
        'nexus' => 'nexus',
        'Niasese' => 'Niasese',
        'Pekingese' => 'Pekingese',
        'Piedmontese' => 'Piedmontese',
        'pincers' => 'pincers',
        'Pistoiese' => 'Pistoiese',
        'pliers' => 'pliers',
        'Portuguese' => 'Portuguese',
        'proceedings' => 'proceedings',
        'rabies' => 'rabies',
        'rice' => 'rice',
        'rhinoceros' => 'rhinoceros',
        'salmon' => 'salmon',
        'Sarawakese' => 'Sarawakese',
        'scissors' => 'scissors',
        'series' => 'series',
        'Shavese' => 'Shavese',
        'shears' => 'shears',
        'siemens' => 'siemens',
        'species' => 'species',
        'swine' => 'swine',
        'testes' => 'testes',
        'trousers' => 'trousers',
        'trout' => 'trout',
        'tuna' => 'tuna',
        'Vermontese' => 'Vermontese',
        'Wenchowese' => 'Wenchowese',
        'whiting' => 'whiting',
        'wildebeest' => 'wildebeest',
        'Yengeese' => 'Yengeese'
        ];

    /**
     * Array of uri component characters considered
     * "reserved" in section 2.2 RFC3986.
     *
     * @var Array
     */
    public static $uri_reserved_chars = ['!', '*', "'", '(', ')', ';', ':', '@', '&', '=', '+', '$', ',', '/', '?', '%', '#', '[', ']'];

    /**
     * Array of percent-encoded "reserved" uri component characters
     * defined in RFC3986, section 2.2.
     *
     * @var Array
     */
    public static $uri_reserved_chars_encoded = ['%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D'];

    /**
     * Return a camelized syntax string.
     */
    public static function camelize(string $str) : string
    {
        return str_replace(' ', '', ucwords(str_replace(['_', '-'], ' ', $str)));
    }

    /**
     * Return a dashed-syntax string.
     */
    public static function dashize(string $str) : string
    {
        return preg_replace('/(?<=\\w)([A-Z])/', '-\\1', $str);
    }

    /**
     *  Convert special HTML entities back to their respective characters.
     */
    public static function decode(string $content, int $flags = \ENT_QUOTES) : string
    {
        return htmlspecialchars_decode($content, $flags);
    }

    /**
     * Return a decoded uri segment, which has been percent-encoded
     * according to RFC 3986, section 2.2.
     */
    public static function decodeUriSegment(string $str) : string
    {
        $str = (string) rawurldecode($str);
        return str_replace(static::$uri_reserved_chars_encoded, static::$uri_reserved_chars, $str);
    }

    /**
     * Return a dotized-syntax string.
     */
    public static function dotize(string $str) : string
    {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '.\\1', $str));
    }

    /**
     * Return a percent-encoded url segment according
     * to RFC 3986, section 2.2.
     */
    public static function encodeUriSegment(string $str) : string
    {
        $str = (string) rawurlencode(strtolower($str));
        return str_replace(static::$uri_reserved_chars, static::$uri_reserved_chars_encoded, $str);
    }

    /**
     * Convert special characters into html entities.
     */
    public static function escape(string $str, int $flag = \ENT_QUOTES, string $e = 'utf-8', bool $double_encode = false) : string
    {
        return htmlspecialchars($str, $flag, $e, $double_encode);
    }

    /**
     * Generate a humanized syntax string.
     */
    public static function humanize(string $str, bool $ucAll = false) : string
    {
        $str = trim(str_replace(['_id', '-', '_', '.'], ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $str)));
        return $ucAll ? ucwords($str) : ucfirst($str);
    }

    /**
     * Return a multibyte-aware lcfirst string.
     */
    public static function lcfirst(string $str, string $e = 'utf-8') : string
    {
        $mbAware = mb_strtolower(mb_substr($str, 0, 1, $e), $e);
        return $mbAware . mb_substr($str, 1, mb_strlen($str, $e), $e);
    }

    /**
     * Return a number to its ordinal english form.
     */
    public static function ordinalize(int $number) : string
    {
        if (in_array(($number % 100), range(11, 13))) {
            return $number . 'th';
        }

        switch ($number % 10) {
            case 1:
                return $number . 'st';
            case 2:
                return $number . 'nd';
            case 3:
                return $number . 'rd';
            default:
                return $number . 'th';
        }
    }


    /**
     * Normalize preferred language string format anomalies 
     * returned by the browser/client request.
     */
    public static function normalizeAcceptLanguage(string $locale) : string
    {
        $keywords = preg_split('/[-_]+/', $locale, 2, \PREG_SPLIT_NO_EMPTY);
        reset($keywords);

        return implode('_', [current($keywords), strtoupper(next($keywords))]);
    }

    /**
     * Normalize locale to ISO 639-1 (alpha-2 format).
     */
    public static function normalizeLocale(string $locale) : string
    {
        $keywords = preg_split('/[-_]+/', $locale, 2, \PREG_SPLIT_NO_EMPTY);
        reset($keywords);

        return current($keywords);
    }

    /**
     * Convert an english language word to its plural form.
     */
    public static function pluralize(string $str) : string
    {
        if (isset(static::$specials[$str])) {
            return static::$specials[$str];
        }

        foreach (static::$plurals as $rule => $replacement) {
            if (preg_match($rule, $str)) {
                return preg_replace($rule, $replacement, $str);
            }
        }

        return $word;
    }

    /**
     * Replace special diacritic characters to their
     * corresponding replacements defined in map $transliteration_map.
     */
    public static function replaceDiacritics(string $str) : string
    {
        return str_replace(
            array_keys(static::$transliteration_map),
            array_values(static::$transliteration_map),
            (string) $str
        );
    }

    /**
     * Replace non breaking spaces.
     */
    public static function replaceNBSP(string $str) : string
    {
        return str_replace(utf8_encode(html_entity_decode('&#160;')), ' ', $str);
    }

    /**
     * Sanitize a string
     */
    public static function sanitize(string $str, string $replacement = '_', string $transliterator = null) : string
    {
        $str = static::replaceDiacritics($str);
        $str = static::transliterate($str, $transliterator);
        $str = preg_replace('/[^a-zA-Z0-9=\s—–-]+/u', '', $str);
        $str = preg_replace('/[=\s—–-]+/u', $replacement, $str);
        return trim($str, $replacement);
    }

    /**
     * Convert an english plural to singular.
     */
    public static function singularize(string $str) : string
    {
        $result = array_search($str, static::$specials, true);

        if (false !== $result) {
            return $result;
        }

        foreach (static::$singulars as $rule => $replacement) {
            if (preg_match($rule, $str)) {
                return preg_replace($rule, $replacement, $str);
            }
        }

        return $str;
    }

    /**
     * Return a string with all spaces converted to given replacement.
     * All non-word characters are removed and the rest of characters
     * transliterated based on the given $transliterator.
     */
    public static function slugify(string $str, string $replacement = '-', string $transliterator = null) : string
    {
        $str = static::replaceDiacritics($str);
        $str = static::transliterate($str, $transliterator);
        $str = preg_replace('/[^a-zA-Z0-9=\s—–-]+/u', '', $str);
        $str = preg_replace('/[=\s—–-]+/u', $replacement, $str);
        $str = trim($str, $replacement);
        return strtolower($str);
    }

    /**
     * Return a transliterated version of a string.
     *
     * @author Antonio Ramirez <amigo.cobos@gmail.com>
     * @author Alexander Makarov <sam@rmcreative.ru>
     */
    public static function transliterate(string $str, string $transliterator = null) : string
    {
        if (null === $transliterator) {
            $transliterator = static::$transliterator;
        }

        return transliterator_transliterate($transliterator, $str);
    }

    /**
     * Return a multibyte-aware ucfirst string.
     */
    public static function ucfirst(string $str, string $e = 'utf-8') : string
    {
        $mbAware = mb_strtoupper(mb_substr($str, 0, 1, $e), $e);
        return $mbAware . mb_substr($str, 1, mb_strlen($str, $e), $e);
    }

    /**
     * Return an underscore-syntax string.
     */
    public static function underscore(string $str) : string
    {
        return strtolower(preg_replace(['/([A-Z]+)([A-Z][a-z])/', '/([a-z])([A-Z\d])/'], ['\\1_\\2', '\\1_\\2'], $str));
    }

    /**
     * Similiar to `humanize`, but return the first letter in lowercase.
     */
    public static function variablize(string $str) : string
    {
        $str = static::camelize($str);
        return strtolower($str[0]) . substr($str, 1);
    }
}
